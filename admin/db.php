<?php
$host = 'localhost';
$dbname = 'yachtwor_berga';
$username = 'yachtwor_Rihards';
$password = 'Rihards2023@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Ошибка подключения: ' . $e->getMessage();
    exit;
}
