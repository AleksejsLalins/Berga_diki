// cancel_booking.js – Обработка отмены резервации

document.addEventListener('DOMContentLoaded', () => {
    const cancelForm = document.getElementById('cancel-form');
    if (!cancelForm) return;

    let savedData = {}; // 🔐 будет содержать копию данных, не FormData

    cancelForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // 💾 Собираем значения вручную
        savedData = {
            phone: cancelForm.phone.value.trim(),
            email: cancelForm.email.value.trim(),
            code: cancelForm.code.value.trim()
        };

        const msgBox = document.getElementById('cancel-message');
        msgBox.textContent = '';
        msgBox.style.color = '';

        // 🔍 Проверка через check_cancel.php
        fetch('check_cancel.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(savedData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const htmlMessage = data.bookings.map(b =>
                    `🗓 <strong>${b.date}</strong> – ${b.time_slot === 'custom' ? b.custom_time : b.time_slot} (Sektors ${b.sector})`
                ).join('<br>');

                // 🧾 Показываем вторую модалку
                showCancelModal(htmlMessage, () => {
                    // ✅ Подтверждение отмены – теперь создаём новый FormData
                    const realFormData = new URLSearchParams(savedData);

                    fetch('cancel_booking.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: realFormData
                    })
                    .then(res => res.json())
                    .then(result => {
                        msgBox.textContent = result.message;
                        msgBox.style.color = result.status === 'success' ? 'green' : 'red';
                        cancelForm.reset();
                    })
                    .catch(() => {
                        msgBox.textContent = "❌ Neizdevās atcelt rezervāciju.";
                        msgBox.style.color = 'red';
                    });
                });

            } else {
                msgBox.textContent = data.message;
                msgBox.style.color = 'red';
            }
        })
        .catch(() => {
            const msgBox = document.getElementById('cancel-message');
            msgBox.textContent = "❌ Neizdevās pārbaudīt rezervāciju.";
            msgBox.style.color = 'red';
        });
    });
});
