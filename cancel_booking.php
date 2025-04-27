<?php
require_once 'admin/config.php';

header('Content-Type: application/json');

$pdo = getDBConnection();

// 🧾 Валидация входящих данных
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$code = $_POST['code'] ?? '';

if (empty($phone) || empty($email) || empty($code)) {
    echo json_encode(['status' => 'error', 'message' => 'Nepieciešami visi lauki']);
    exit;
}

// 🔍 Поиск всех бронирований с указанным cancel_code и клиентом
$stmt = $pdo->prepare("
    SELECT b.id, b.date, b.time_slot, b.custom_time, b.sector, c.full_name, c.id as customer_id
    FROM booking b
    INNER JOIN customers c ON b.customer_id = c.id
    WHERE c.phone = :phone AND c.email = :email AND b.cancel_code = :code AND b.status = 'confirmed'
");
$stmt->execute([
    'phone' => $phone,
    'email' => $email,
    'code'  => $code
]);

$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$bookings) {
    echo json_encode(['status' => 'error', 'message' => 'Rezervācija netika atrasta vai kods nav pareizs.']);
    exit;
}

// 🚫 Отменяем все подходящие бронирования
$bookingIds = array_column($bookings, 'id');
$in = implode(',', array_fill(0, count($bookingIds), '?'));
$cancel = $pdo->prepare("UPDATE booking SET status = 'cancelled' WHERE id IN ($in)");
$cancel->execute($bookingIds);

// 📧 Уведомление админу о отмене
notify_admin_cancel($bookings);

// 📧 Уведомление клиенту о отмене
notify_client_cancel($bookings);

// ✅ Ответ клиенту
echo json_encode(['status' => 'success', 'message' => 'Rezervācija veiksmīgi atcelta.']);

// Функция уведомления админу при отмене
function notify_admin_cancel($bookings) {
    $adminTo = 'info@bergadiki.lv';
    $subject = '❌ Rezervācija tika atcelta';

    $booking = $bookings[0];

    $message = "❌ Klients atcēla savu rezervāciju:\n\n";
    $message .= "👤 Vārds: {$booking['full_name']}\n";
    $message .= "📞 Telefons: {$_POST['phone']}\n";
    $message .= "📧 E-pasts: {$_POST['email']}\n\n";
    $message .= "📅 Atceltie datumi:\n";

    foreach ($bookings as $b) {
        $slot = $b['time_slot'] === 'custom' ? ($b['custom_time'] ?? 'custom') : $b['time_slot'];
        $message .= "- {$b['date']} ({$slot})\n";
    }

    $message .= "\n📍 Sektors: {$booking['sector']}\n";
    $message .= "\n➡️ https://bergadiki.lv/admin/edit_client.php?id={$booking['customer_id']}\n";

    $headers = "From: noreply@bergadiki.lv\r\n" .
               "Reply-To: noreply@bergadiki.lv\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail($adminTo, $subject, $message, $headers);
}

// Функция уведомления клиента при отмене
function notify_client_cancel($bookings) {
    $clientTo = $_POST['email'];
    $subject = '❌ Jūsu rezervācija tika atcelta';

    $booking = $bookings[0];

    $message = "Sveiki, {$booking['full_name']}!\n\n";
    $message .= "Jūs atcēlāt savu rezervāciju. Šeit ir detaļas:\n\n";
    $message .= "📍 Sektors: {$booking['sector']}\n";
    $message .= "📅 Atceltie datumi:\n";

    foreach ($bookings as $b) {
        $slot = $b['time_slot'] === 'custom' ? ($b['custom_time'] ?? 'custom') : $b['time_slot'];
        $message .= "- {$b['date']} ({$slot})\n";
    }

    $message .= "\nPaldies, ka izmantojāt mūsu pakalpojumus! Ar cieņu, Berga Dīķi. https://www.bergadiki.lv\n";

    $headers = "From: info@bergadiki.lv\r\n" .
               "Reply-To: info@bergadiki.lv\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail($clientTo, $subject, $message, $headers);
}
