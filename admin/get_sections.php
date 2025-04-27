<?php
// 👇 Включаем ошибки для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

$lang = $_GET['lang'] ?? 'lv';

$query = "SELECT * FROM sections WHERE language = :lang";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':lang', $lang, PDO::PARAM_STR);
$stmt->execute();
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($sections);

