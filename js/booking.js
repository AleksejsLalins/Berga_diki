// booking.js – Обновлённый скрипт с поддержкой локализации и стилей

let openMenu = null;
let allBookings = {};

// Функция для переводов
function t(key) {
    return window.bookingLang?.[key] || key;
}

document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('calendar-body');
    const sectorButtons = document.querySelectorAll('.sector-btn');
    let selectedSector = null;
    let selectedDates = [];

    // 🌍 КАРТА: скрываем блок карты при выборе сектора
    const pondMapSection = document.getElementById('pond-map-section');
    const pondMapImage = document.getElementById('pond-map-image');
    const toggleBtn = document.getElementById('toggle-map');

    let mapVisible = true;
    if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        mapVisible = !mapVisible;
        pondMapImage.style.display = mapVisible ? 'block' : 'none';
        toggleBtn.innerText = mapVisible ? '📍 ' + t('hide_map') : '📍 ' + t('show_map');

        if (mapVisible) {
            const offset = 100; // отступ сверху, чтобы карта не была прижата к самому верху
            const topPosition = pondMapImage.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({ top: topPosition, behavior: 'smooth' });
        }
    });
}



    document.addEventListener('click', function (e) {
        if (openMenu && !openMenu.contains(e.target)) {
            openMenu.remove();
            openMenu = null;
        }
    });

    sectorButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            if (pondMapImage) pondMapImage.style.display = 'none';
mapVisible = false;
toggleBtn.innerText = '📍 ' + t('show_map');

            const scrollTarget = document.getElementById('calendar-container') || document.getElementById('booking-form');
if (scrollTarget) {
    scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

            sectorButtons.forEach(b => b.classList.remove('selected-sector'));
            this.classList.add('selected-sector');
            selectedSector = this.dataset.sector;
            selectedDates = [];
            renderCalendar();
        });
    });

    document.getElementById('cancel-reservation-btn').addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('cancel-modal').style.display = 'flex';
    });

    document.querySelector('.modal-close').addEventListener('click', function () {
        document.getElementById('cancel-modal').style.display = 'none';
    });

    function renderCalendar() {
        const today = new Date();
        const endDate = new Date(today);
        endDate.setDate(today.getDate() + 35);
        tbody.innerHTML = '';

        if (!selectedSector) return;

        fetch(`get_bookings.php?sector=${selectedSector}`)
            .then(res => res.json())
            .then(bookedData => {
                allBookings = bookedData;

                let current = new Date(today);
                while (current <= endDate) {
                    const weekRow = document.createElement('tr');

                    for (let i = 0; i < 7; i++) {
                        const cellDate = new Date(current);
                        const dateStr = cellDate.toISOString().split('T')[0];
                        const day = String(cellDate.getDate()).padStart(2, '0');
                        const month = String(cellDate.getMonth() + 1).padStart(2, '0');

                        const td = document.createElement('td');
                        td.textContent = `${day}.${month}`;
                        td.setAttribute('data-date', dateStr);

                        const dayBookings = bookedData[dateStr];
                        let tooltip = [];
                        let statusClass = 'available';

                        if (Array.isArray(dayBookings)) {
                            dayBookings.forEach(entry => {
                                if (typeof entry === 'string') {
                                    if (entry === 'full') statusClass = 'booked';
                                    else if (entry === 'half_am') statusClass = 'half-am';
                                    else if (entry === 'half_pm') statusClass = 'half-pm';
                                } else if (entry.slot === 'custom') {
                                    td.classList.add('custom-booked');
                                    tooltip.push(entry.time);
                                }
                            });
                        }

                        if (tooltip.length > 0) {
                            const tooltipText = `${t('busy')}: ` + tooltip.join(', ');
                            td.addEventListener('mouseenter', () => {
                                const tooltipEl = document.createElement('div');
                                tooltipEl.className = 'cell-tooltip';
                                tooltipEl.textContent = tooltipText;
                                document.body.appendChild(tooltipEl);

                                const rect = td.getBoundingClientRect();
                                tooltipEl.style.top = (rect.top + window.scrollY - 30) + 'px';
                                tooltipEl.style.left = (rect.left + window.scrollX + rect.width / 2 - tooltipEl.offsetWidth / 2) + 'px';

                                requestAnimationFrame(() => tooltipEl.classList.add('show'));
                                td._tooltip = tooltipEl;
                            });

                            td.addEventListener('mouseleave', () => {
                                if (td._tooltip) {
                                    td._tooltip.remove();
                                    td._tooltip = null;
                                }
                            });
                        }

                        td.classList.add(statusClass);

                        if (statusClass === 'available' || statusClass.startsWith('half') || tooltip.length > 0) {
                            td.style.cursor = 'pointer';
                            td.addEventListener('click', (e) => {
                                e.stopPropagation();
                                setTimeout(() => {
                                    document.querySelectorAll('#calendar-body td').forEach(cell => cell.classList.remove('highlighted-date-temp'));
                                    td.classList.add('highlighted-date-temp');
                                }, 0);

                                if (openMenu) {
                                    openMenu.remove();
                                    openMenu = null;
                                    return;
                                }

                                showSlotSelector(td, dateStr);
                            });
                        }

                        weekRow.appendChild(td);
                        current.setDate(current.getDate() + 1);
                    }

                    tbody.appendChild(weekRow);
                }
            });
    }

    function showSlotSelector(cell, date) {
        const menu = document.createElement('div');
        menu.innerHTML = '';
        menu.className = 'slot-selector floating fade-in';
        menu.id = 'slot-selector';

        const booked = allBookings[date] || [];
        const hasCustom = booked.some(b => b.slot === 'custom');

        let slotButtons = '';

const bookedSlots = booked
    .filter(b => typeof b === 'string');

const isHalfAM = bookedSlots.includes('half_am');
const isHalfPM = bookedSlots.includes('half_pm');

if (!hasCustom) {
    let availableSlots = [];

    if (isHalfAM && !isHalfPM) {
        availableSlots = ['half_pm'];
    } else if (isHalfPM && !isHalfAM) {
        availableSlots = ['half_am'];
    } else if (!isHalfAM && !isHalfPM) {
        availableSlots = ['full', 'half_am', 'half_pm'];
    }

    slotButtons = availableSlots.map(slot =>
        `<button data-slot="${slot}">${t('slot_' + slot)}</button>`
    ).join('');
}

// всегда добавляем кастомную опцию
slotButtons += `
    <button data-slot="custom">${t('slot_custom')}</button>
    <div class="custom-time-picker" style="display:none;">
        <label>${t('from')} <select class="time-from"></select></label>
        <label>${t('to')} <select class="time-to"></select></label>
        <button class="confirm-custom">${t('ok_button')}</button>
    </div>
`;

menu.innerHTML = slotButtons;
        document.body.appendChild(menu);
openMenu = menu;

requestAnimationFrame(() => {
    const rect = cell.getBoundingClientRect();
    const scrollY = window.scrollY;
    const scrollX = window.scrollX;
    const menuRect = menu.getBoundingClientRect();

    let top = rect.bottom + scrollY + 5;
    let left = rect.left + scrollX;
    if (left + menuRect.width > window.innerWidth) {
        left = window.innerWidth - menuRect.width - 10;
    }
    if (top + menuRect.height > scrollY + window.innerHeight) {
        top = rect.top + scrollY - menuRect.height - 5;
    }

    menu.style.position = 'absolute';
    menu.style.top = `${top}px`;
    menu.style.left = `${left}px`;
    menu.style.zIndex = '9999';
});


        const fromSelect = menu.querySelector('.time-from');
        const toSelect = menu.querySelector('.time-to');

        let disabledTimes = [];
        booked.forEach(b => {
            if (b.slot === 'custom') {
                const [start, end] = b.time.split('-');
                for (let h = +start; h < +end; h++) {
                    disabledTimes.push(h.toString().padStart(2, '0') + ':00');
                }
            } else if (b === 'full') {
                disabledTimes = Array.from({ length: 24 }, (_, h) => h.toString().padStart(2, '0') + ':00');
            } else if (b === 'half_am') {
                for (let h = 0; h < 12; h++) disabledTimes.push(h.toString().padStart(2, '0') + ':00');
            } else if (b === 'half_pm') {
                for (let h = 12; h < 24; h++) disabledTimes.push(h.toString().padStart(2, '0') + ':00');
            }
        });

        for (let h = 0; h < 24; h++) {
            const t = h.toString().padStart(2, '0') + ':00';
            if (!disabledTimes.includes(t)) {
                fromSelect.innerHTML += `<option value="${t}">${t}</option>`;
                toSelect.innerHTML += `<option value="${t}">${t}</option>`;
            }
        }

        const busyList = booked.filter(b => b.slot === 'custom').map(b => `<div class="busy-time">⛔ ${b.time}</div>`).join('');
        if (busyList) {
            const infoDiv = document.createElement('div');
            infoDiv.className = 'busy-times-info';
            infoDiv.innerHTML = busyList;
            infoDiv.style.marginBottom = '8px';
            infoDiv.style.background = '#fff0f0';
            infoDiv.style.border = '1px dashed #dc3545';
            infoDiv.style.padding = '6px 8px';
            infoDiv.style.borderRadius = '4px';
            infoDiv.style.color = '#c0392b';
            infoDiv.style.fontSize = '13px';

            const picker = menu.querySelector('.custom-time-picker');
            picker.prepend(infoDiv);
        }

        menu.querySelector('button[data-slot="custom"]').addEventListener('click', (e) => {
            e.stopPropagation();
            const picker = menu.querySelector('.custom-time-picker');
            picker.style.display = picker.style.display === 'block' ? 'none' : 'block';
        });

        menu.querySelectorAll('button[data-slot]:not([data-slot="custom"])').forEach(btn => {
            btn.addEventListener('click', () => {
                const slot = btn.dataset.slot;
                selectedDates = selectedDates.filter(d => d.date !== date);
                selectedDates.push({ date: date, slot: slot });
                menu.remove();
                openMenu = null;
                updateCalendarHighlights();
            });
        });
        
        function isTimeOverlap(newStart, newEnd, existingIntervals) {
    const newStartNum = +newStart.replace(':', '');
    const newEndNum = +newEnd.replace(':', '');

    return existingIntervals.some(interval => {
        const [start, end] = interval.split('-').map(t => +t.replace(':', ''));
        return newStartNum < end && newEndNum > start;
    });
}
        
        menu.querySelector('.confirm-custom').addEventListener('click', () => {
    const from = fromSelect.value;
    const to = toSelect.value;
    if (from >= to) return alert(t('invalid_range'));

    const existingCustomTimes = booked
        .filter(b => b.slot === 'custom')
        .map(b => b.time);

    if (isTimeOverlap(from, to, existingCustomTimes)) {
        return alert(t('custom_time_conflict') || '⛔ Laiks pārklājas ar esošu rezervāciju!');
    }

    selectedDates = selectedDates.filter(d => d.date !== date);
    selectedDates.push({ date: date, slot: 'custom', time: `${from}-${to}` });
    menu.remove();
    openMenu = null;
    updateCalendarHighlights();
});

        // 🫼 Закрытие по клику вне
        setTimeout(() => document.addEventListener('click', handleOutsideClick));
        function handleOutsideClick(e) {
            if (!e.target.closest('#slot-selector')) {
                if (openMenu) openMenu.remove();
                openMenu = null;
                document.removeEventListener('click', handleOutsideClick);
            }
        }
    }

    function updateCalendarHighlights() {
        document.querySelectorAll('#calendar-body td').forEach(td => {
            td.classList.remove('selected-date', 'selected-full', 'selected-half-am', 'selected-half-pm', 'selected-custom');
            td.removeAttribute('title');
        });

        selectedDates.forEach(item => {
            const td = document.querySelector(`#calendar-body td[data-date="${item.date}"]`);
            if (!td) return;

            td.classList.add('selected-date');
            if (item.slot === 'full') td.classList.add('selected-full');
            if (item.slot === 'half_am') td.classList.add('selected-half-am');
            if (item.slot === 'half_pm') td.classList.add('selected-half-pm');
            if (item.slot === 'custom') {
                td.classList.add('selected-custom');
                td.title = `${t('busy')}: ${item.time}`;
            }
        });
    
}

document.getElementById('booking-form').addEventListener('submit', function (e) {
    e.preventDefault();

    const sector = document.querySelector('.selected-sector')?.dataset?.sector;
    if (!sector || selectedDates.length === 0) {
        alert(t('select_sector_date'));
        return;
    }

    const payload = {
        name: this.full_name.value,
        email: this.email.value,
        phone: this.phone.value,
        sector: parseInt(sector),
        dates: selectedDates,
        lang: new URLSearchParams(window.location.search).get('lang') || 'lv'  // 👈 язык из URL
    };

    fetch('save_booking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        const messageBox = document.getElementById('booking-message');
        messageBox.style.color = data.status === 'success' ? 'green' : 'red';
        messageBox.textContent = data.message || t('error_booking');
        if (data.status === 'success') {
            document.getElementById('booking-form').reset();
            selectedDates = [];
            updateCalendarHighlights();
        }
    })
    .catch(() => {
        const messageBox = document.getElementById('booking-message');
        messageBox.style.color = 'red';
        messageBox.textContent = t('server_error');
    });
});
});
