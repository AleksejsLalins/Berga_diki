// cancel_modal.js – логика второй модалки подтверждения отмены бронирования

/**
 * Показывает модальное окно подтверждения отмены резервации
 *
 * @param {string} detailsHtml - HTML с деталями резервации
 * @param {Function} onConfirmCallback - вызывается при подтверждении
 */
window.showCancelModal = function(detailsHtml, onConfirmCallback) {
    const modal = document.getElementById('cancel-confirm-modal');
    const detailsEl = document.getElementById('cancel-details');
    const confirmBtn = document.getElementById('cancel-confirm');
    const closeBtn = document.getElementById('cancel-close');

    if (!modal || !detailsEl || !confirmBtn || !closeBtn) {
        console.warn("❗ Cancel modal elements missing");
        return;
    }

    // Вставляем детали
    detailsEl.innerHTML = detailsHtml;

    // Показываем
    modal.classList.remove('hidden');

    // Удалим старый обработчик, если он есть
    const newHandler = () => {
        onConfirmCallback();
        modal.classList.add('hidden');
        confirmBtn.removeEventListener('click', newHandler);
    };

    confirmBtn.addEventListener('click', newHandler);
    closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
};
