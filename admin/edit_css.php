<?php
// edit_css.php — Rediģēt globālās CSS mainīgās vērtības
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$cssFile = '../css/globals.css';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newVars = $_POST['css'] ?? [];
    $contents = file_get_contents($cssFile);

    foreach ($newVars as $key => $value) {
        $escapedValue = addslashes($value);
        $contents = preg_replace("/--$key:\s*[^;]+;/", "--$key: $escapedValue;", $contents);
    }

    file_put_contents($cssFile, $contents);
    $success = true;
}

// === Загружаем текущие переменные
$cssVars = [];
if (file_exists($cssFile)) {
    $css = file_get_contents($cssFile);
    if (preg_match('/:root\s*{([^}]*)}/', $css, $matches)) {
        $lines = explode(";", $matches[1]);
        foreach ($lines as $line) {
            if (preg_match('/--(.*?):\s*(.*)/', trim($line), $varMatch)) {
                $cssVars[trim($varMatch[1])] = trim($varMatch[2]);
            }
        }
    }
}

// === Названия переменных для отображения
function displayName($key) {
    $map = [
        // 🎨 Krāsas
        'color-primary' => 'Galvenā krāsa',
        'color-background' => 'Fona krāsa',
        'color-text' => 'Teksta krāsa',
        'color-white' => 'Balta krāsa',
        'color-hover' => 'Hover krāsa',

        // 📐 Izmēri
        'header-height' => 'Header augstums',
        'nav-height' => 'Izvēlnes augstums',
        'footer-height' => 'Footer augstums',
        'logo-height' => 'Logo augstums',
        'section-max-width' => 'Sekcijas maksimālais platums',
        'section-padding' => 'Sekcijas iekšējā atstarpe',

        // 🔤 Fonts
        'font-inter' => 'Inter fonts',
        'font-fjalla-one' => 'Fjalla One fonts',
        'font-weight-inter-regular' => 'Inter Regular biezums',
        'font-weight-inter-medium' => 'Inter Medium biezums',
        'font-weight-inter-semibold' => 'Inter Semibold biezums',
        'font-weight-fjalla-one' => 'Fjalla One biezums',

        // 📏 Fonta izmēri
        'font-size-header' => 'Virsraksta izmērs',
        'font-size-nav' => 'Navigācijas fonta izmērs',
        'font-size-footer' => 'Kājenes fonta izmērs',
        'font-size-content' => 'Satura fonta izmērs',
    ];
    return $map[$key] ?? $key;
}

// === Grupēšana pa kategorijām
$groups = [
    'Krāsas' => ['color-primary', 'color-background', 'color-text', 'color-white', 'color-hover'],
    'Izmēri' => ['header-height', 'nav-height', 'footer-height', 'logo-height', 'section-max-width', 'section-padding'],
    'Fonts' => ['font-inter', 'font-fjalla-one', 'font-weight-inter-regular', 'font-weight-inter-medium', 'font-weight-inter-semibold', 'font-weight-fjalla-one'],
    'Fonta izmēri' => ['font-size-header', 'font-size-nav', 'font-size-footer', 'font-size-content']
];
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Rediģēt CSS</title>
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fa;
        }
        .container {
            max-width: 850px;
            margin: auto;
            background: white;
            padding: 24px 32px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 40px;
        }
        h2 {
            margin-bottom: 20px;
            color: #114b5f;
        }
        h3 {
            margin-top: 30px;
            font-size: 20px;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }
        .row {
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        label {
            flex: 1;
        }
        input[type="text"] {
            flex: 1;
            padding: 8px 10px;
            font-size: 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-left: 12px;
        }
        .success {
            color: green;
            margin-bottom: 20px;
        }
        .buttons-bottom {
            display: flex;
            justify-content: space-between;
            margin-top: 32px;
        }
        .back-link, button[type="submit"] {
            background: #114b5f;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .preview-box {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            margin-left: 12px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Rediģēt globālās CSS mainīgās vērtības</h2>
    <?php if ($success): ?><div class="success">✅ Izmaiņas saglabātas veiksmīgi!</div><?php endif; ?>
    <form method="POST">
        <?php foreach ($groups as $groupTitle => $vars): ?>
            <h3><?= htmlspecialchars($groupTitle) ?></h3>
            <?php foreach ($vars as $key): ?>
                <?php if (!isset($cssVars[$key])) continue; ?>
                <div class="row">
                    <label for="css_<?= $key ?>"><?= displayName($key) ?></label>
                    <input type="text" name="css[<?= $key ?>]" id="css_<?= $key ?>" value="<?= htmlspecialchars($cssVars[$key]) ?>">
                    <?php if (strpos($key, 'color-') === 0): ?>
                        <div class="preview-box" style="background: <?= htmlspecialchars($cssVars[$key]) ?>"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <div class="buttons-bottom">
            <a href="edit.php" class="back-link">&larr; Atpakaļ uz admin paneli</a>
            <button type="submit">💾 Saglabāt</button>
        </div>
    </form>
</div>
</body>
</html>
