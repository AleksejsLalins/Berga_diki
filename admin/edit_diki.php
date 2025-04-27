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

// Получаем текущую статью
$stmt = $pdo->prepare("SELECT title, content FROM diki_posts WHERE language = :lang LIMIT 1");
$stmt->execute(['lang' => $lang]);
$current = $stmt->fetch(PDO::FETCH_ASSOC);

// Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    $exists = $pdo->prepare("SELECT COUNT(*) FROM diki_posts WHERE language = :lang");
    $exists->execute(['lang' => $lang]);
    $count = $exists->fetchColumn();

    if ($count > 0) {
        $update = $pdo->prepare("UPDATE diki_posts SET title = :title, content = :content WHERE language = :lang");
    } else {
        $update = $pdo->prepare("INSERT INTO diki_posts (title, content, language) VALUES (:title, :content, :lang)");
    }

    $update->execute([
        'title' => $title,
        'content' => $content,
        'lang' => $lang
    ]);

    header("Location: edit_diki.php?lang=$lang&success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title>Dīķu apsaimniekošana – <?= strtoupper($lang) ?></title>
    <script src="https://cdn.tiny.cloud/1/iudcz4s7unr45c9f3m6tw6o4oee50w3samvqbkrm79ro2aau/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
            background: #f9f9f9;
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

        form {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        textarea {
            width: 100%;
            height: 400px;
        }

        .success {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            background: #888;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .save-button {
            background-color: #2d89ef;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
    </style>

    <script>
        tinymce.init({
            selector: '#content',
            menubar: false,
            plugins: 'lists link image preview code',
            toolbar: 'undo redo | bold italic | bullist numlist | link image | code | preview',
            content_style: "body { font-family:Helvetica,Arial,sans-serif; font-size:14px }",
            entity_encoding: 'raw',
            encoding: 'utf-8',
            forced_root_block: ''
        });
    </script>
</head>
<body>

<h1>Dīķu apsaimniekošana – <?= strtoupper($lang) ?></h1>

<div class="lang-switch">
    <a href="?lang=lv" class="<?= $lang === 'lv' ? 'active' : '' ?>">LV</a>
    <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
    <a href="?lang=ru" class="<?= $lang === 'ru' ? 'active' : '' ?>">RU</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="success">Saturs veiksmīgi saglabāts!</div>
<?php endif; ?>

<form method="POST">
    <label for="title"><strong>Virsraksts:</strong></label>
    <input type="text" name="title" id="title" value="<?= htmlspecialchars($current['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <label for="content"><strong>Saturs:</strong></label>
    <textarea id="content" name="content"><?= htmlspecialchars($current['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

    <button class="save-button">💾 Saglabāt</button>
    <a href="edit.php" class="back-link">← Atpakaļ uz paneli</a>
</form>

</body>
</html>
