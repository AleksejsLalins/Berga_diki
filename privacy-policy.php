<?php
require_once __DIR__ . '/admin/config.php';

$lang = $_GET['lang'] ?? 'lv';
$pdo = getDBConnection();

// Получаем политику конфиденциальности
$stmt = $pdo->prepare("SELECT text FROM gdpr_texts WHERE key_name = 'privacy_policy' AND language = :lang");
$stmt->execute(['lang' => $lang]);
$policy = $stmt->fetchColumn();

if (!$policy) {
    $policy = "Privātuma politika nav pieejama šajā valodā.";
}

function lang_switch_link($code, $label) {
    return '<a href="?lang=' . $code . '">' . $label . '</a>';
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <title>Privātuma politika</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/globals.css">
    <link rel="stylesheet" href="/css/privacy-policy.css">
    
        <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-3CJBNJG9F1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-3CJBNJG9F1');
    </script>
    
</head>
<body>

    <nav>
        <?= lang_switch_link('lv', 'Latviešu') ?>
        <?= lang_switch_link('en', 'English') ?>
        <?= lang_switch_link('ru', 'Русский') ?>
        <a href="booking.php?lang=<?= $lang ?>" style="float: right;">← Atpakaļ</a>
    </nav>

    <div class="policy-content">
        <?= $policy ?>
    </div>

</body>
</html>
