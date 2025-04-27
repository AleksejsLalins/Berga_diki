<?php
require_once __DIR__ . '/admin/config.php';

$lang = $_GET['lang'] ?? 'lv';

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT section_name, content FROM sections WHERE language = :lang");
$stmt->execute(['lang' => $lang]);
$sections = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$gdprStmt = $pdo->prepare("SELECT key_name, text FROM gdpr_texts WHERE language = :lang");
$gdprStmt->execute(['lang' => $lang]);
$gdpr_texts = $gdprStmt->fetchAll(PDO::FETCH_KEY_PAIR);

function gdpr_text($key, $texts) {
    return isset($texts[$key]) ? $texts[$key] : '';
}


//  Получаем подписи для меню
$menuStmt = $pdo->prepare("SELECT key_name, label_text FROM menu_labels WHERE language = :lang");
$menuStmt->execute(['lang' => $lang]);
$menu_labels = $menuStmt->fetchAll(PDO::FETCH_KEY_PAIR);

function section($name, $sections) {
    return isset($sections[$name]) ? $sections[$name] : '';
}

function menu_label($key, $menu_labels) {
    return isset($menu_labels[$key]) ? $menu_labels[$key] : ucfirst($key);
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">

<head>
    <meta name="description" content="Karpu dīķi Jelgavas rajonā , kuros iekārtotas makšķerēšanas vietas lieliskai karpu copei. Karpas, karūsas, līņi, līdakas, C&amp;R dīķis jeb ķer un atlaid sporta dīķis.  7 dažādi dīķi katrai gaumei.">
    <meta charset="UTF-8" />
    <!-- Основной фавикон -->
<link rel="icon" href="/favicon.ico" type="image/x-icon">

<!-- PNG иконки -->
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">

<!-- Apple -->
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

<!-- Android / Chrome -->
<link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png">
<link rel="icon" type="image/png" sizes="512x512" href="/android-chrome-512x512.png">

<!-- Manifest (для PWA) -->
<link rel="manifest" href="/site.webmanifest">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>Berga dīķi - Makšķerēšanas dīķi</title>
    <link rel="stylesheet" href="css/globals.css?v=<?= time() ?>">
    <link rel="stylesheet" href="css/cookie-banner.css?v=<?= time() ?>">
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Подключаем gallery.js -->
    <script src="js/gallery.js" defer></script>
    <!--  <script src="js/viewport.js" defer></script> -->
    
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
    <header>
        <div class="top-bar">
            <div class="social-icons">
                <a href="https://www.facebook.com/Bergadiki"><img src="icons/facebook.png" alt="Berga dīķi Facebook" /></a>
                <a href="https://www.instagram.com/bergadiki/"><img src="icons/instagram.png" alt="Berga dīķi Instagram" /></a>
                <a href="https://www.youtube.com/channel/UCn1umA33vCACqiRyq-2E3eQ"><img src="icons/youtube.png" alt="Berga dīķi YouTube" /></a>
                <a href="https://wa.me/37126265170"><img src="icons/whatsapp.png" alt="Berga dīķi WhatsApp" /></a>
            </div>
            <div class="language-switch">
                <a href="?lang=lv"><img src="icons/lv.png" alt="LV" /></a>
                <a href="?lang=en"><img src="icons/en.png" alt="EN" /></a>
                <a href="?lang=ru"><img src="icons/ru.png" alt="RU" /></a>
            </div>
        </div>

        <!-- Логотип в шапке, он виден только на ПК -->
        <div class="logo-bar">
            <img class="logo" src="images/logo.png" alt="Berga Dīķi" />
        </div>
    </header>

    <!-- Навигация -->
    <nav class="main-nav">
        <!-- Контейнер для гамбургера и логотипа (для мобильных) -->
        <div class="mobile-header">
            <!-- Гамбургер -->
            <div class="hamburger-menu" id="hamburger-menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>

            <!-- Логотип для мобильной версии, показывается только на мобильных -->
            <img src="images/logo_m.png" alt="Berga dīķi" class="mobile-logo" />
        </div>

        <!-- Меню -->
        <div class="nav-links" id="nav-links">
    <a href="#about"><?= menu_label('about', $menu_labels) ?></a>
    <a href="#ponds"><?= menu_label('ponds', $menu_labels) ?></a>
    <a href="#rules"><?= menu_label('rules', $menu_labels) ?></a>
    <a href="#pricing"><?= menu_label('pricing', $menu_labels) ?></a>
    <a href="#booking"><?= menu_label('booking', $menu_labels) ?></a>
    <a href="#gallery"><?= menu_label('gallery', $menu_labels) ?></a>
    <a href="#contacts"><?= menu_label('contacts', $menu_labels) ?></a>
</div>

    </nav>

    <!-- Картинка с кнопками -->
    <section class="main-bg">
        <img src="images/mainbg.png" alt="Berga dīķi" />
        <h1 class="visually-hidden">Berga Dīķi - Makšķerēšanas dīķi</h1>
        <div class="buttons">
            <a href="tel:+37126265170" class="button"><?= menu_label('button_contact', $menu_labels) ?></a>
<a href="#booking" class="button"><?= menu_label('button_reserve', $menu_labels) ?></a>

        </div>
    </section>

    <main>
        
        <section id="about">
            <h2><?= menu_label('about', $menu_labels) ?></h2>
            <br>
            <?= section('about', $sections) ?>
        </section>

        <section id="ponds">
            
            <h2><?= menu_label('ponds', $menu_labels) ?></h2>
            <br>
            <div class="pond-map-wrapper">
  <img id="pondMap" src="images/map12res1.png" alt="Dīķu karte">
</div>

<div id="pondMapModal" class="pond-modal">
  <img src="images/map12res1.png" alt="Dīķu karte pilnā izmērā">
</div>


            <?= section('ponds', $sections) ?>
        </section>

        <section id="rules">
            <h2><?= menu_label('rules', $menu_labels) ?></h2>
            <br>
            <?= section('rules', $sections) ?>
        </section>

        <section id="pricing">
            <h2><?= menu_label('pricing', $menu_labels) ?></h2>
            <br>
            <?= section('pricing', $sections) ?>
        </section>
        
        <section id="booking">
            <h2><?= menu_label('booking', $menu_labels) ?></h2>
            <br>
            <?= section('booking', $sections) ?>
            <!--<a href="booking.php" class="button">Rezervēt sektoru</a>-->
            <a href="index.php" class="button">Rezervēt sektoru</a>
        </section>


        <!-- Галерея -->
        <section id="gallery" class="gallery">
            <div class="slider-container">
                <div class="slider">
                    <div class="slide"><img src="images/pic1.jpg" alt="Karpas 1" loading="lazy"></div>
                    <div class="slide"><img src="images/pic2.jpg" alt="Karpas 2" loading="lazy"></div>
                    <div class="slide"><img src="images/pic3.jpg" alt="Karpas 3" loading="lazy"></div>
                    <div class="slide"><img src="images/pic4.jpg" alt="Karpas 4" loading="lazy"></div>
                    <div class="slide"><img src="images/pic5.jpg" alt="Karpas 5" loading="lazy"></div>
                    <div class="slide"><img src="images/pic6.jpg" alt="Karpas 6" loading="lazy"></div>
                    <div class="slide"><img src="images/pic7.jpg" alt="Karpas 7" loading="lazy"></div>
                    <div class="slide"><img src="images/pic8.jpg" alt="Karpas 8" loading="lazy"></div>
                    <div class="slide"><img src="images/pic9.jpg" alt="Karpas 9" loading="lazy"></div>
                    <div class="slide"><img src="images/pic10.jpg" alt="Karpas 10" loading="lazy"></div>
                    <div class="slide"><img src="images/pic11.jpg" alt="Karpas 11" loading="lazy"></div>
                    <div class="slide"><img src="images/pic12.jpg" alt="Karpas 12" loading="lazy"></div>
                    <div class="slide"><img src="images/pic13.jpg" alt="Karpas 13" loading="lazy"></div>
                    <div class="slide"><img src="images/pic14.jpg" alt="Karpas 14" loading="lazy"></div>
                </div>
                <button class="prev">&#10094;</button>
                <button class="next">&#10095;</button>
            </div>

            <!-- Модальное окно -->
            <div class="popup-overlay">
                <div class="popup-content">
                    <div class="img-wrapper">
                        <img src="" alt="">
                    </div>
                </div>
            </div>
        </section>

        <section id="contacts">
  <div class="contacts-wrapper">
    <div class="contacts-info">
      <p><strong>📍 <?= menu_label('contacts_address', $menu_labels) ?></strong><br />Jaunlejzemnieki, Salgales pagasts, Ozolnieku novads, Latvija, LV-3045</p>
      <p><strong>📞 <?= menu_label('contacts_phone', $menu_labels) ?></strong><br />+371 26265170</p>
      <p><strong>✉️ <?= menu_label('contacts_email', $menu_labels) ?></strong><br />info@bergadiki.lv</p>
      <p><strong>🕒 <?= menu_label('contacts_hours', $menu_labels) ?></strong><br />Katru dienu no 6:00 līdz 21:00<br />
        <em><?= menu_label('contacts_note', $menu_labels) ?></em></p>

      <div class="contacts-buttons">
        <a href="tel:+37126265170" class="contacts button">📞 <?= menu_label('contacts_button_call', $menu_labels) ?></a>
        <a href="mailto:info@bergadiki.lv" class="contacts button">✉️ <?= menu_label('contacts_button_mail', $menu_labels) ?></a>
        <a href="https://waze.com/ul?ll=56.64570065214936,23.895847968470548&navigate=yes" class="contacts button" target="_blank" rel="noopener">🧭 Waze</a>
        <a href="https://maps.google.com?q=Berga+dīķi" class="contacts button" target="_blank" rel="noopener">🌍 Google Maps</a>
      </div>
    </div>
    <div class="contacts-map">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2193.667154194016!2d23.89328377683476!3d56.645588573427716!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46ef2e7b774735e1%3A0x9c526b1fa8c5eea5!2zQmVyZ2EgZMSrxLdp!5e0!3m2!1slv!2slv!4v1744839039984!5m2!1slv!2slv"
        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>
</section>




    <footer>
  <div class="footer-left">
    <ul>
      <li><a href="/privacy-policy.php"><?= menu_label('footer_privacy', $menu_labels) ?></a></li>
      <li><a href="/diki.php"><?= menu_label('footer_management', $menu_labels) ?></a></li>
    </ul>
  </div>
  <div class="footer-right">
    <p>Berga Dīķi – info@bergadiki.lv – +371 26265170</p>
  </div>
</footer>


    <!-- Скрипты, размещённые внизу страницы (перед </body>), не блокируют загрузку контента -->
    <script src="js/menu.js"></script>
    <script src="js/scrollspy.js"></script>
    <script src="js/mapwrapper.js?v=<?= time() ?>" defer></script>

    <?php if (false): ?>
  <script src="js/content-loader.js"></script>
<?php endif; ?>



<!-- Cookie Banner -->
    <?php include 'cookie-banner.php'; ?>
<script src="js/cookie-banner.js?v=<?= time() ?>"></script>
</body>
</html>