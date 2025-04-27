<?php
// 🔗 Подключение к конфигурации базы данных
require_once 'config.php';
// session_start();

// ⏱ Sēdes pārbaude (termiņš: 1 stunda)
$timeout = 3600;
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$_SESSION['last_activity'] = time(); // Atjaunojam taimautu

// 🔤 Iegūstam izvēlēto valodu un sekcijas nosaukumu
$lang = $_SESSION['lang'];
$section_name = $_GET['section'] ?? '';

// 🔗 Ielādējam attiecīgās sekcijas saturu no datubāzes
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM sections WHERE section_name = :section_name AND language = :lang");
$stmt->bindParam(':section_name', $section_name);
$stmt->bindParam(':lang', $lang);
$stmt->execute();
$section = $stmt->fetch();

// 📥 Apstrādājam iesniegto formu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'];

    // 💾 Atjaunojam saturu datubāzē
    $stmt = $pdo->prepare("UPDATE sections SET content = :content WHERE section_name = :section_name AND language = :lang");
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':section_name', $section_name);
    $stmt->bindParam(':lang', $lang);
    $stmt->execute();

    // 🔄 Pāradresācija pēc saglabāšanas
    header("Location: edit_section.php?section=$section_name&lang=$lang&success=true");
    exit();
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Rediģēt sekciju "<?= htmlspecialchars($section_name) ?>"</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- 📦 Pievienojam TinyMCE redaktoru -->
    <script src="https://cdn.tiny.cloud/1/iudcz4s7unr45c9f3m6tw6o4oee50w3samvqbkrm79ro2aau/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

    <style>
        /* 🌐 Pamata stils */
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            padding: 2em;
            box-sizing: border-box;
        }

        h2 {
            color: #114b5f;
            text-align: center;
        }

        form {
            background-color: #fff;
            padding: 20px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
            box-sizing: border-box;
        }

        textarea {
            width: 100%;
            height: 400px;
        }

        button, .back-link {
            background-color: #114b5f;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 1em;
            display: inline-block;
        }

        .back-link {
            background-color: #888;
            margin-right: 10px;
        }

        .success-msg {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-bottom: 1em;
        }
    </style>

    <script>
        // 🧠 Inicializējam TinyMCE
        tinymce.init({
    selector: '#content',
    menubar: false,
    plugins: 'lists link image preview code',
    toolbar: 'undo redo | bold italic | bullist numlist | link image | code | preview',
    extended_valid_elements: 'iframe[src|frameborder|style|scrolling|class|width|height|name|align|allowfullscreen]',
    valid_children: '+body[style|iframe]',
    content_style: "body { font-family:Helvetica,Arial,sans-serif; font-size:14px }",

    // 🔧 Убираем автоматическое преобразование символов в HTML-сущности
    entity_encoding: 'raw',
    encoding: 'utf-8',
    forced_root_block: '', // чтобы не добавлял <p> сам по себе
});

    </script>
</head>
<body>

<div class="container">
    <!-- 🧾 Virsraksts -->
    <h2>Rediģēt sekciju "<?= htmlspecialchars($section_name) ?>"</h2>

    <!-- ✅ Ziņojums par veiksmīgu saglabāšanu -->
    <?php if (isset($_GET['success'])): ?>
        <p class="success-msg">Saturs veiksmīgi saglabāts!</p>
    <?php endif; ?>

    <!-- 🔙 Atgriešanās poga -->
    <a href="edit.php" class="back-link">← Atpakaļ uz sekciju sarakstu</a>

    <!-- 📝 Rediģēšanas forma -->
    <form method="POST" action="edit_section.php?section=<?= urlencode($section_name) ?>&lang=<?= $lang ?>">
        <label for="content"><strong>Saturs:</strong></label>
        <textarea id="content" name="content"><?= htmlspecialchars($section['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
        <button type="submit">💾 Saglabāt</button>
    </form>
</div>

</body>
</html>
