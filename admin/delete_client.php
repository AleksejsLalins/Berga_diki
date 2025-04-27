<?php
require_once 'config.php';
// session_start();

// ⏱ Sesijas pārbaude
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pdo = getDBConnection();

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

header("Location: clients.php");
exit();
