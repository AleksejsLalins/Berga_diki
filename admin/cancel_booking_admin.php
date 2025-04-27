<?php
// cancel_booking_admin.php – Отмена резервации администратором
require_once 'config.php';

$pdo = getDBConnection();

// 🛡️ Получаем ID резервации из GET
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo "Nepareizs rezervācijas ID.";
    exit;
}

try {
    // ✅ Меняем статус на "cancelled"
    $stmt = $pdo->prepare("UPDATE booking SET status = 'cancelled' WHERE id = :id");
    $stmt->execute(['id' => $id]);

    // ✅ Успешно, возвращаемся на страницу управления
    header("Location: edit_booking.php");
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo "Kļūda datubāzē: " . htmlspecialchars($e->getMessage());
    exit;
}
