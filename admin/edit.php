<?php
// Подключаем конфиг
require_once 'config.php';
// session_start();


// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Обновляем активность
$timeout = 3600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
$_SESSION['last_activity'] = time();

// Устанавливаем язык (по умолчанию латышский)
$lang = $_SESSION['lang'] ?? 'lv';

// Получаем список секций
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT section_name FROM sections WHERE language = :lang");
$stmt->execute(['lang' => $lang]);
$sections = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Administrēšana – Saturs</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f4f4;
            padding: 40px;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .lang-switch {
            text-align: center;
            margin-bottom: 20px;
        }

        .lang-switch a {
            display: inline-block;
            margin: 0 8px;
            padding: 8px 16px;
            background-color: #e2e2e2;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .lang-switch a.active {
            background-color: #2d89ef;
            color: #fff;
        }

        .section-list {
            max-width: 600px;
            margin: 0 auto 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        }

        .section-list a {
            display: block;
            padding: 12px;
            margin-bottom: 10px;
            background-color: #f7f7f7;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-decoration: none;
            color: #2d89ef;
            font-weight: bold;
        }

        .section-list a:hover {
            background-color: #e0ecff;
        }

        .bottom-actions {
            text-align: center;
            margin-top: 40px;
        }

        .bottom-actions a {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 20px;
            background-color: #2d89ef;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .bottom-actions a:hover {
            background-color: #1b65c1;
        }
    </style>
</head>
<body>

<h1>Admin Panel – Sadaļu rediģēšana</h1>

<!-- Переключатель языков -->
<div class="lang-switch">
    <a href="?lang=lv" class="<?= $lang === 'lv' ? 'active' : '' ?>">LV</a>
    <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
    <a href="?lang=ru" class="<?= $lang === 'ru' ? 'active' : '' ?>">RU</a>
</div>

<div class="section-list">
    <?php foreach ($sections as $section): ?>
        <a href="edit_section.php?section=<?= urlencode($section) ?>&lang=<?= $lang ?>">
            Rediģēt sadaļu – <?= ucfirst($section) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Кнопка управления клиентами -->
<div class="bottom-actions">
    <a href="edit_diki.php">📝 Rediģēt rakstus</a>
    <a href="edit_labels.php">📝 Rediģēt LABEL tulkojumus</a>
    <a href="clients.php">👥 Klientu pārvaldība</a>
    <a href="edit_booking.php">📅 Rezervāciju pārvaldība</a>
    <a href="edit_css.php" class="admin-button">Rediģēt CSS</a>
    <a href="logout.php">Iziet</a>
</div>

</body>
</html>
