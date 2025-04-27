document.addEventListener('DOMContentLoaded', () => {
    const checkBtn = document.getElementById('cancel-check');
    const msgBox = document.getElementById('cancel-message');

    checkBtn.addEventListener('click', () => {
        const phone = document.getElementById('cancel-phone').value.trim();
        const email = document.getElementById('cancel-email').value.trim();
        const code = document.getElementById('cancel-code').value.trim();

        const formData = new FormData();
        formData.append('phone', phone);
        formData.append('email', email);
        formData.append('code', code);

        fetch('check_cancel.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const html = data.bookings.map(b =>
                    `🗓 <strong>${b.date}</strong> – ${b.time_slot === 'custom' ? b.custom_time : b.time_slot} (Sektors ${b.sector})`
                ).join('<br>');

                showCancelModal(html, () => {
                    // подтверждение – отменить
                    fetch('cancel_booking.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(result => {
                        msgBox.textContent = result.message;
                        msgBox.style.color = result.status === 'success' ? 'green' : 'red';
                    });
                });

            } else {
                msgBox.textContent = data.message;
                msgBox.style.color = 'red';
            }
        });
    });
});
