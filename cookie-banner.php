<!-- ✅ Cookie Consent Banner -->

<div id="cookie-banner">
  <div class="cookie-banner-inner">
    <div class="cookie-banner-text">
      <?= gdpr_text('cookie_banner_text', $gdpr_texts) ?>
      <a href="#" id="cookie-settings-link"><?= gdpr_text('cookie_more', $gdpr_texts) ?? 'Uzzināt vairāk' ?></a>
    </div>
    <div class="cookie-banner-actions">
      <button id="accept-cookies"><?= gdpr_text('cookie_accept', $gdpr_texts) ?></button>
    </div>
  </div>
</div>

<!-- ⚙️ Cookie Settings Modal -->
<div id="cookie-settings-modal">
  <div class="cookie-modal-content">
    <h2><?= gdpr_text('cookie_modal_title', $gdpr_texts) ?></h2>
    <p><?= gdpr_text('cookie_modal_intro', $gdpr_texts) ?></p>

    <h3><?= gdpr_text('cookie_functional', $gdpr_texts) ?></h3>
    <p><?= gdpr_text('cookie_functional_desc', $gdpr_texts) ?></p>

    <h3><?= gdpr_text('cookie_analytics', $gdpr_texts) ?></h3>
    <label>
      <input type="checkbox" id="analytics-cookies" checked />
      <?= gdpr_text('cookie_analytics', $gdpr_texts) ?>
    </label>

    <div class="cookie-modal-buttons">
      <button id="save-cookie-settings"><?= gdpr_text('cookie_save', $gdpr_texts) ?></button>
      <button id="close-cookie-modal">✖</button>
    </div>
  </div>
</div>
