<?php
require_once 'admin/config.php';
header('Content-Type: application/json');

$pdo = getDBConnection();

$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$code  = $_POST['code']  ?? '';

if (empty($phone) || empty($email) || empty($code)) {
    echo json_encode(['status' => 'error', 'message' => 'Nepieciešami visi lauki']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT b.id, b.date, b.time_slot, b.custom_time, b.sector
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

echo json_encode(['status' => 'success', 'bookings' => $bookings]);
