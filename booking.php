<?php
// booking.php – Lapa rezervācijai
require_once 'admin/config.php';

$lang = $_GET['lang'] ?? 'lv';
$pdo = getDBConnection();

$gdprStmt = $pdo->prepare("SELECT key_name, text FROM gdpr_texts WHERE language = :lang");
$gdprStmt->execute(['lang' => $lang]);
$gdpr_texts = $gdprStmt->fetchAll(PDO::FETCH_KEY_PAIR);

function gdpr_text($key, $texts) {
    return isset($texts[$key]) ? $texts[$key] : '';
}


$stmt = $pdo->prepare("SELECT key_name, content FROM booking_translations WHERE language = :lang");
$stmt->execute(['lang' => $lang]);
$booking_texts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

function booking_text($key, $texts) {
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
    <meta charset="UTF-8">
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

    <title><?= booking_text('title', $booking_texts) ?></title>
    <meta name="description" content="Karpu dīķi Jelgavas rajonā , kuros iekārtotas makšķerēšanas vietas lieliskai karpu copei. Karpas, karūsas, līņi, līdakas, C&amp;R dīķis jeb ķer un atlaid sporta dīķis.  7 dažādi dīķi katrai gaumei.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="css/globals.css?v=<?= time() ?>">
    <link rel="stylesheet" href="css/cookie-banner.css?v=<?= time() ?>">
    <link rel="stylesheet" href="css/booking.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.23.0/dist/date-fns.min.js"></script>
    
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
<!-- 🔼 Language Switch -->
<div class="b-top-bar">
    <div class="b-language-switch">
        <a href="?lang=lv">LV</a>
        <a href="?lang=en">EN</a>
        <a href="?lang=ru">RU</a>
    </div>
</div>

<!-- 🎣 Header -->
<header class="b-header">
    <div class="b-logo-bar">
        <img class="b-logo" src="images/logo.png" alt="Logo" />
        <h1 class="b-title"><?= booking_text('title', $booking_texts) ?></h1>
    </div>
</header>

<!-- 🧹 Content -->
<main class="b-main">

    <!-- 🔙 Buttons -->
    <div class="b-top-buttons">
        <a href="index.php?lang=<?= $lang ?>" class="b-nav-button"><?= booking_text('back_button', $booking_texts) ?></a>
        <a href="tel:+37126265170" class="b-nav-button"><?= booking_text('contact_button', $booking_texts) ?></a>
        <a href="#" class="b-nav-button" id="cancel-reservation-btn"><?= booking_text('cancel_button', $booking_texts) ?></a>

    </div>
    <!-- 🔁 Первая модалка — НЕ форма -->
<div class="modal-overlay" id="cancel-modal" style="display: none;">
  <div class="modal-window">
    <h3><?= booking_text('cancel_heading', $booking_texts) ?></h3>

    <input type="tel" id="cancel-phone" placeholder="<?= booking_text('phone_placeholder', $booking_texts) ?>" required>
    <input type="email" id="cancel-email" placeholder="<?= booking_text('email_placeholder', $booking_texts) ?>" required>
    <input type="text" id="cancel-code" placeholder="<?= booking_text('cancel_code_placeholder', $booking_texts) ?>" required>

    <button id="cancel-check"><?= booking_text('cancel_check_button', $booking_texts) ?></button>
    <button type="button" class="modal-close"><?= booking_text('cancel_close_button', $booking_texts) ?></button>

    <p id="cancel-message"></p>
  </div>
</div>

    
    <!-- 💥 Cancel Modal -->
<div class="cancel-modal hidden" id="cancel-confirm-modal">
  <div class="cancel-modal-content">
    <h3><?= booking_text('cancel_confirm_title', $booking_texts) ?></h3>
    <div id="cancel-details" class="cancel-details-text"></div>
    <div class="cancel-modal-buttons">
     <button type="button" id="cancel-confirm" class="b-nav-button danger">
  <?= booking_text('cancel_confirm_button_final', $booking_texts) ?>
</button>

      <button id="cancel-close" class="b-nav-button"><?= booking_text('cancel_close_button', $booking_texts) ?></button>
    </div>
  </div>
</div>



    <!-- 📘 Rules Block -->
    <div class="reservation-rules-block">
        <?= booking_text('reservation_rules', $booking_texts) ?>
    </div>
    
    <!-- 🗺️ Map of Ponds Section (collapsible) -->
<div class="pond-map-wrapper" id="pond-map-section">
  <button id="toggle-map" class="b-nav-button" style="margin-bottom: 10px;">
    📍 <?= menu_label('hide_map', $menu_labels) ?>

  </button>
  <img src="images/1dikisres.png" alt="Sektoru karte" id="pond-map-image">
</div>

    <!-- 📍 Sectors -->
    <div class="sectors-container">
        <?php $sectorLabel = booking_text('sector_label', $booking_texts); ?>
<?php for ($i = 1; $i <= 11; $i++): ?>
    <button class="sector-btn" data-sector="<?= $i ?>"><?= htmlspecialchars($sectorLabel) ?> <?= $i ?></button>
<?php endfor; ?>

    </div>
    

    <!-- 🗕️ Calendar -->
    <div class="calendar-container">
        <table class="calendar-table">
            <thead>
                <tr>
                    <th>P</th><th>O</th><th>T</th><th>C</th><th>Pk</th><th>S</th><th>Sv</th>
                </tr>
            </thead>
            <tbody id="calendar-body"></tbody>
        </table>

        <!-- 🛍️ Legend -->
        <div class="calendar-legend">
            <span class="legend-item available"><?= booking_text('legend_available', $booking_texts) ?></span>
            <span class="legend-item half-am"><?= booking_text('legend_half_am', $booking_texts) ?></span>
            <span class="legend-item half-pm"><?= booking_text('legend_half_pm', $booking_texts) ?></span>
            <span class="legend-item booked"><?= booking_text('legend_full', $booking_texts) ?></span>
            <span class="legend-item custom-booked"><?= booking_text('legend_custom', $booking_texts) ?></span>
        </div>
    </div>

    <!-- 📝 Booking form -->
    <div class="booking-form-container">
        <h3><?= booking_text('form_heading', $booking_texts) ?></h3>
        <form id="booking-form" method="POST">
            <input type="hidden" name="selected_sector" id="selected-sector">
            <input type="hidden" name="selected_dates" id="selected-dates">

            <input type="text" name="full_name" id="full_name" placeholder="<?= booking_text('name_placeholder', $booking_texts) ?>" required>
            <input type="tel" name="phone" id="phone" placeholder="<?= booking_text('phone_placeholder', $booking_texts) ?>" required>
            <input type="email" name="email" id="email" placeholder="<?= booking_text('email_placeholder', $booking_texts) ?>" required>
            
            <!-- GDPR checkbox -->
<div class="gdpr-checkbox-wrapper">
  <label class="gdpr-checkbox-label">
    <input type="checkbox" name="privacy_agreement" required>
    <span><?= gdpr_text('gdpr_checkbox', $gdpr_texts) ?></span>
  </label>
</div>


            <button type="submit"><?= booking_text('submit_button', $booking_texts) ?></button>
        </form>
        <p id="booking-message"></p>
    </div>
</main>

<!-- 🗃️ Footer -->
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

<div id="map-modal" class="map-modal">
  <div class="map-modal-content">
    <img src="images/1dikisres.png" alt="Karte" id="map-fullscreen-img">
  </div>
</div>
<!-- JS -->
<script>
    window.bookingLang = <?= json_encode($booking_texts, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="js/booking.js?v=<?= time() ?>" defer></script>
<script src="js/cancel_booking.js?v=<?= time() ?>" defer></script>
<script src="js/cancel_modal.js?v=<?= time() ?>" defer></script>
<script src="js/sectormapmodal.js?v=<?= time() ?>" defer></script>

<!-- Cookie Banner -->
    <?php include 'cookie-banner.php'; ?>
<script src="js/cookie-banner.js?v=<?= time() ?>"></script>
</body>
</html>

