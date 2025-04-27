<?php
require_once 'admin/config.php';

header('Content-Type: application/json');

// 🛡️ Проверка параметра сектора
if (!isset($_GET['sector'])) {
    echo json_encode([]);
    exit;
}

$sector = (int) $_GET['sector'];
$pdo = getDBConnection();

// 📦 Получаем все подтверждённые бронирования по сектору
$stmt = $pdo->prepare("
    SELECT date, time_slot, custom_time
    FROM booking
    WHERE sector = :sector AND status = 'confirmed'
");
$stmt->execute(['sector' => $sector]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [];

// 🔄 Обработка каждого бронирования
foreach ($results as $row) {
    $date = $row['date'];
    $slot = $row['time_slot'];

    // 📆 Инициализация массива по дате
    if (!isset($response[$date])) {
        $response[$date] = [];
    }

    // ⏰ Обработка стандартных слотов
    if (in_array($slot, ['full', 'half_am', 'half_pm'])) {
        $response[$date][] = $slot;
    }

    // ⏱️ Обработка кастомных интервалов
    if ($slot === 'custom' && !empty($row['custom_time'])) {
        $response[$date][] = [
            'slot' => 'custom',
            'time' => $row['custom_time']
        ];
    }
}

// 📤 Отдаём собранные данные
echo json_encode($response);
