<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$timeout = 3600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
$_SESSION['last_activity'] = time();

$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'lv');
$_SESSION['lang'] = $lang;

$pdo = getDBConnection();

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['labels'])) {
    foreach ($_POST['labels'] as $key => $value) {
        $stmt = $pdo->prepare("UPDATE menu_labels SET label_text = :text WHERE key_name = :key AND language = :lang");
        $stmt->execute([
            'text' => $value,
            'key' => $key,
            'lang' => $lang
        ]);
    }
    $message = "Saglabāts!";
}

// Получаем все надписи
$stmt = $pdo->prepare("SELECT key_name, label_text FROM menu_labels WHERE language = :lang ORDER BY key_name");
$stmt->execute(['lang' => $lang]);
$labels = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <title>Rediģēt tulkojumus – <?= strtoupper($lang) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background: #f4f4f4;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .lang-switch {
            text-align: center;
            margin-bottom: 20px;
        }

        .lang-switch a {
            display: inline-block;
            margin: 0 5px;
            padding: 8px 16px;
            background-color: #ccc;
            border-radius: 6px;
            text-decoration: none;
            color: #000;
            font-weight: bold;
        }

        .lang-switch a.active {
            background-color: #2d89ef;
            color: #fff;
        }

        form {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-top: 20px;
            font-weight: bold;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .save-button {
            display: block;
            padding: 12px 24px;
            background-color: #2d89ef;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        .message {
            text-align: center;
            color: green;
            font-weight: bold;
        }
        
        .form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
}

.back-button {
    padding: 12px 24px;
    background-color: #ccc;
    color: #333;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: background 0.3s;
}

.back-button:hover {
    background-color: #bbb;
}

    </style>
</head>
<body>

<h1>Rediģēt valodas atslēgas (<?= strtoupper($lang) ?>)</h1>

<div class="lang-switch">
    <a href="?lang=lv" class="<?= $lang === 'lv' ? 'active' : '' ?>">LV</a>
    <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
    <a href="?lang=ru" class="<?= $lang === 'ru' ? 'active' : '' ?>">RU</a>
</div>

<?php if (isset($message)): ?>
    <p class="message"><?= $message ?></p>
<?php endif; ?>

<form method="POST">
    <?php foreach ($labels as $key => $label): ?>
        <label for="label_<?= $key ?>"><?= $key ?></label>
        <input type="text" name="labels[<?= htmlspecialchars($key) ?>]" value="<?= htmlspecialchars($label) ?>">
    <?php endforeach; ?>

    <div class="form-actions">
    <button type="submit" class="save-button">💾 Saglabāt izmaiņas</button>
    <a href="edit.php" class="back-button">← Atpakaļ uz admin paneli</a>
</div>

    
</form>

</body>
</html>
