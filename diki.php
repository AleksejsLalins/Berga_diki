<?php
require_once __DIR__ . '/admin/config.php';

$lang = $_GET['lang'] ?? 'lv';

$pdo = getDBConnection();

// Загружаем статьи про dīķu apsaimniekošana
$stmt = $pdo->prepare("SELECT title, content FROM diki_posts WHERE language = :lang ORDER BY created_at DESC");
$stmt->execute(['lang' => $lang]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Тексты из перевода
$menuStmt = $pdo->prepare("SELECT key_name, label_text FROM menu_labels WHERE language = :lang");
$menuStmt->execute(['lang' => $lang]);
$menu_labels = $menuStmt->fetchAll(PDO::FETCH_KEY_PAIR);

function menu_label($key, $labels) {
  return isset($labels[$key]) ? $labels[$key] : ucfirst($key);
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
  <meta charset="UTF-8">
  <title><?= menu_label('footer_management', $menu_labels) ?> | Berga dīķi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/globals.css?v=<?= time() ?>">
  <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>
<body>

  <header>
    <div class="top-bar">
      <div class="language-switch">
        <a href="?lang=lv" class="<?= $lang === 'lv' ? 'active' : '' ?>"><img src="icons/lv.png" alt="LV" /></a>
        <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>"><img src="icons/en.png" alt="EN" /></a>
        <a href="?lang=ru" class="<?= $lang === 'ru' ? 'active' : '' ?>"><img src="icons/ru.png" alt="RU" /></a>
      </div>
    </div>
    <div class="logo-bar">
      <a href="/"><img class="logo" src="images/logo.png" alt="Berga Dīķi" /></a>
    </div>
    
        <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-3CJBNJG9F1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-3CJBNJG9F1');
    </script>
  </header>

  <main>
    <section class="post-section">
      <h2><?= menu_label('footer_management', $menu_labels) ?></h2>

      <?php foreach ($posts as $post): ?>
        <article class="post-block">
          <h3><?= htmlspecialchars($post['title']) ?></h3>
          <div class="post-content">
            <?= $post['content'] ?>
          </div>
        </article>
      <?php endforeach; ?>

      
      <div class="back-button-wrapper">
  <a href="/" class="button back-button"><?= menu_label('button_back_home', $menu_labels) ?></a>
</div>

    </section>
  </main>

  <footer>
    <p>SIA "Karpu Dīķi" – Reģ. nr. 12345678 – info@karpudiki.lv – +371 20000000</p>
  </footer>

</body>
</html>
