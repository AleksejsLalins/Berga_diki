document.addEventListener('DOMContentLoaded', function () {
  const banner = document.getElementById('cookie-banner');
  const modal = document.getElementById('cookie-settings-modal');
  const acceptBtn = document.getElementById('accept-cookies');
  const settingsLink = document.getElementById('cookie-settings-link');
  const closeBtn = document.getElementById('close-cookie-modal');
  const saveBtn = document.getElementById('save-cookie-settings');
  const analyticsCheckbox = document.getElementById('analytics-cookies');

  // 🧼 Скрываем модальное окно, даже если CSS сбойный
  modal.style.display = 'none';

  // ✅ Показываем баннер, если пользователь не дал согласие
  if (!localStorage.getItem('cookieConsent')) {
    banner.style.display = 'block';
  }

  // ✅ Принять куки (всё)
  acceptBtn.addEventListener('click', () => {
    localStorage.setItem('cookieConsent', 'accepted');
    banner.style.display = 'none';
  });

  // ⚙️ Показать модальное окно настроек
  settingsLink.addEventListener('click', (e) => {
    e.preventDefault();
    modal.style.display = 'flex';
  });

  // ❌ Закрыть модалку без сохранения
  closeBtn.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  // 💾 Сохранить выбор пользователя
  saveBtn.addEventListener('click', () => {
    const allowAnalytics = analyticsCheckbox.checked;
    localStorage.setItem('cookieConsent', allowAnalytics ? 'analytics' : 'essential');
    banner.style.display = 'none';
    modal.style.display = 'none';
  });
});
