<?php
// Настройки базы данных
define('DB_HOST', 'localhost'); // Это обычно localhost, если не так - уточните у хостинга
define('DB_USER', 'yachtwor_Rihards'); // Имя пользователя, которое вы создали
define('DB_PASSWORD', 'Rihards2023@'); // Пароль для пользователя
define('DB_NAME', 'yachtwor_berga'); // Имя базы данных

// Пытаемся начать сессию только если она еще не активна
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Установка языка по умолчанию, если он еще не установлен в сессии
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'lv';  // Установим язык по умолчанию
}

// Если передан параметр языка, меняем его
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Функция для подключения к базе данных
function getDBConnection() {
    try {
        // Устанавливаем кодировку UTF-8 при подключении к базе данных
        return new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASSWORD);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>
