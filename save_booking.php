<?php
// ==============================
// 🔧 Настройки и подключение БД
// ==============================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'admin/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['email'], $data['phone'], $data['name'], $data['sector'], $data['dates'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nepilnīga forma']);
    exit;
}

$lang = $data['lang'] ?? 'lv';
$pdo = getDBConnection();

// ====================================
// 🌐 Загрузка переводов из БД
// ====================================
$stmt = $pdo->prepare("SELECT key_name, content FROM booking_translations WHERE language = :lang");
$stmt->execute(['lang' => $lang]);
$texts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

function t($key) {
    global $texts;
    return $texts[$key] ?? $key;
}

// ====================================
// 🧑‍💼 Проверка клиента в базе
// ====================================
$stmt = $pdo->prepare("SELECT id, email, verified FROM customers WHERE phone = :phone LIMIT 1");
$stmt->execute([
    'phone' => $data['phone']
]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// 👉 Если клиент найден
if ($customer) {
    $customerId = $customer['id'];
    $verified = $customer['verified'];

    // ✉️ Обновим email, если он другой
    if ($verified && $data['email'] !== $customer['email']) {
        $update = $pdo->prepare("UPDATE customers SET email = :email WHERE id = :id");
        $update->execute(['email' => $data['email'], 'id' => $customerId]);
    }

    // ❌ Если клиент не верифицирован
    if (!$verified) {
        notify_admin($data, $customerId, false);
        echo json_encode(['status' => 'error', 'message' => t('not_verified_message')]);
        exit;
    }
} else {
    // 🆕 Новый клиент → добавляем в базу, НЕ разрешаем резервацию
    $insert = $pdo->prepare("
        INSERT INTO customers (full_name, phone, email, verified, created_at)
        VALUES (:name, :phone, :email, 0, NOW())
    ");
    $insert->execute([
        'name' => $data['name'],
        'phone' => $data['phone'],
        'email' => $data['email']
    ]);
    $customerId = $pdo->lastInsertId();
    notify_admin($data, $customerId, true);

    echo json_encode(['status' => 'error', 'message' => t('first_time_contact_admin')]);
    exit;
}

// ====================================
// 🔐 Уникальный код отмены
// ====================================
$cancelCode = bin2hex(random_bytes(8));

// ====================================
// 💾 Сохраняем бронирование
// ====================================
$insertBooking = $pdo->prepare("
    INSERT INTO booking (customer_id, sector, date, time_slot, custom_time, status, created_at, cancel_code)
    VALUES (:customer_id, :sector, :date, :slot, :custom_time, 'confirmed', NOW(), :cancel_code)
");

foreach ($data['dates'] as $d) {
    if (!isset($d['date'], $d['slot'])) continue;
    $customTime = ($d['slot'] === 'custom' && isset($d['time'])) ? $d['time'] : null;

    $insertBooking->execute([
        'customer_id' => $customerId,
        'sector' => $data['sector'],
        'date' => $d['date'],
        'slot' => $d['slot'],
        'custom_time' => $customTime,
        'cancel_code' => $cancelCode
    ]);
}

require_once 'google_calendar_sync.php';

$bookingForCalendar = [
    'name' => $data['name'],
    'phone' => $data['phone'],
    'email' => $data['email'],
    'sector' => $data['sector'],
    'dates' => $data['dates']
];

try {
    addBookingToGoogleCalendar($bookingForCalendar);
} catch (Exception $e) {
    // Логируем ошибку в файл, чтобы потом разобраться
    error_log('Google Calendar error: ' . $e->getMessage());

    // Но клиенту ничего не показываем — бронирование всё равно идёт дальше
}


// ====================================
// 📧 E-mail клиенту
// ====================================
$to = $data['email'];
$subject = t('email_subject');
$bookingDetails = "";
$cancelInstructions = t('cancel_instructions'); // piemēram: "Lai atceltu rezervāciju, izmantojiet šo saiti"
$cancelCodeLabel = t('cancel_code'); // piemēram: "Atcelšanas kods"
$cancelLink = "https://bergadiki.lv/booking.php?lang=$lang";
foreach ($data['dates'] as $d) {
    $type = $d['slot'] === 'custom' ? ($d['time'] ?? 'custom') : $d['slot'];
    $bookingDetails .= "- {$d['date']} ({$type})\n";
}
$message = t('email_greeting') . " " . $data['name'] . ",\n\n" .
           t('email_confirmation_text') . "\n\n" .
           "📍 Sektors: {$data['sector']}\n" .
           "📅 Datumi:\n$bookingDetails\n\n" .
           "$cancelInstructions:\n$cancelLink\n\n" .
           "$cancelCodeLabel: $cancelCode\n\n" .
           t('email_signature');

$headers = "From: info@bergadiki.lv\r\n" .
           "Reply-To: info@bergadiki.lv\r\n" .
           "Content-Type: text/plain; charset=UTF-8\r\n";

@mail($to, $subject, $message, $headers);

// ====================================
// 📧 Уведомление админу
// ====================================
notify_admin($data, $customerId, false, true);

// ✅ Ответ клиенту
echo json_encode(['status' => 'success', 'message' => t('booking_success')]);


// ====================================
// 📬 Функция отправки email админу
// ====================================
function notify_admin($data, $customerId = null, $isNew = false, $isConfirmed = false) {
    $adminTo = 'info@bergadiki.lv';

    if ($isNew) {
        $subject = '🆕 Jauns klients mēģina rezervēt';
        $message = "🧾 Jauns klients mēģina veikt rezervāciju:\n\n";
    } elseif (!$isConfirmed) {
        $subject = '🚫 Noverificēts klients mēģina rezervēt';
        $message = "⚠️ Noverificēts klients mēģina veikt rezervāciju:\n\n";
    } else {
        $subject = '✅ Jauna rezervācija';
        $message = "🔔 Saņemta jauna rezervācija:\n\n";
    }

    $message .= "👤 Vārds: {$data['name']}\n📞 Telefons: {$data['phone']}\n📧 E-pasts: {$data['email']}\n\n📅 Datumi:\n";

    foreach ($data['dates'] as $d) {
        $slot = $d['slot'] === 'custom' ? ($d['time'] ?? 'custom') : $d['slot'];
        $message .= "- {$d['date']} ({$slot})\n";
    }

    $message .= "\n📍 Sektors: {$data['sector']}\n";

    // Безопасная генерация ссылки (проверка customerId)
    if (!empty($customerId)) {
        $message .= "\n➡️ https://bergadiki.lv/admin/edit_client.php?id={$customerId}\n";
    }

    $headers = "From: noreply@bergadiki.lv\r\n" .
               "Reply-To: noreply@bergadiki.lv\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail($adminTo, $subject, $message, $headers);
}

