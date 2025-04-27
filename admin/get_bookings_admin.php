<?php
require_once 'config.php';
require_once 'db.php';

$pdo = getDBConnection();
$sector = $_GET['sector'] ?? null;

if (!$sector) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT b.date, b.time_slot, b.custom_time, c.full_name, c.phone, c.email
    FROM booking b
    JOIN customers c ON b.customer_id = c.id
    WHERE b.sector = :sector AND b.status = 'confirmed'
");

$stmt->execute(['sector' => $sector]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];

foreach ($bookings as $row) {
    $date = $row['date'];

    if (!isset($data[$date])) {
        $data[$date] = [];
    }

    $data[$date][] = [
        'slot' => $row['time_slot'],
        'time' => $row['custom_time'],
        'name' => $row['full_name'],
        'phone' => $row['phone'],
        'email' => $row['email']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
