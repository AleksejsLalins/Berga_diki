// admin_booking.js
// Versija bāzēta uz booking.js ar izmaiņām admin panelim

let openMenu = null;           // Aktīvās izvēlnes references
let allBookings = {};          // Visas rezervācijas pa datumiem (no servera)
let selectedSector = null;     // Pašreiz izvēlētais sektors
let selectedDates = [];        // Izvēlētie datumi rezervācijai

function t(key) {
    // Tulkotājs - meklē tulkojumu bookingLang objektā (ja pieejams)
    return window.bookingLang?.[key] || key;
}

document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('calendar-body');
    const sectorButtons = document.querySelectorAll('.sector-btn');

    // Noklikšķināšana jebkur citur aizver izvēlni
    document.addEventListener('click', function (e) {
        if (openMenu && !openMenu.contains(e.target)) {
            openMenu.remove();
            openMenu = null;
        }
    });

    // Sektora pogu noklikšķināšana
    sectorButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            sectorButtons.forEach(b => b.classList.remove('selected-sector'));
            this.classList.add('selected-sector');
            selectedSector = this.dataset.sector;
            selectedDates = [];
            renderCalendar();
        });
    });

    // Kalendāra zīmēšana
    function renderCalendar() {
        const today = new Date();
        const endDate = new Date(today);
        endDate.setDate(today.getDate() + 35);
        tbody.innerHTML = '';

        if (!selectedSector) return;

        // Datu iegūšana no servera
        fetch(`/admin/get_bookings_admin.php?sector=${selectedSector}`)
            .then(res => res.json())
            .then(bookedData => {
                allBookings = bookedData;
                let current = new Date(today);

                while (current <= endDate) {
                    const weekRow = document.createElement('tr');

                    for (let i = 0; i < 7; i++) {
                        const cellDate = new Date(current);
                        const dateStr = cellDate.toISOString().split('T')[0];
                        const td = document.createElement('td');
                        const day = String(cellDate.getDate()).padStart(2, '0');
                        const month = String(cellDate.getMonth() + 1).padStart(2, '0');

                        td.textContent = `${day}.${month}`;
                        td.setAttribute('data-date', dateStr);

                        const dayBookings = bookedData[dateStr];
                        let statusClass = 'available';

                        if (Array.isArray(dayBookings)) {
                            let tooltipContent = '';
                            let hasFull = false;
                            let hasHalfAM = false;
                            let hasHalfPM = false;
                            let hasCustom = false;

                            dayBookings.forEach(entry => {
                                const slot = entry.slot;

                                if (slot === 'full') hasFull = true;
                                else if (slot === 'half_am') hasHalfAM = true;
                                else if (slot === 'half_pm') hasHalfPM = true;
                                else if (slot === 'custom') hasCustom = true;

                                const slotLabel = {
                                    full: 'Pilna diena',
                                    half_am: 'Līdz 12:00',
                                    half_pm: 'Pēc 12:00',
                                    custom: entry.time || 'Custom laiks'
                                }[slot] || slot;

                                tooltipContent += `${entry.name} (${entry.phone}, ${entry.email}) – ${slotLabel}\n`;
                            });

                            if (tooltipContent) {
                                td.title = tooltipContent.trim();
                            }

                            // ✅ Ja ir full vai abi half, tad 'booked'
                            if (hasFull || (hasHalfAM && hasHalfPM)) {
                                statusClass = 'booked';
                            } else if (hasHalfAM) {
                                statusClass = 'half-am';
                            } else if (hasHalfPM) {
                                statusClass = 'half-pm';
                            } else if (hasCustom) {
                                statusClass = 'custom-booked';
                            }
                        }

                        td.classList.add(statusClass);

                        if (statusClass !== 'booked') {
                            td.style.cursor = 'pointer';
                            td.addEventListener('click', () => showSlotSelector(td, dateStr));
                        }

                        weekRow.appendChild(td);
                        current.setDate(current.getDate() + 1);
                    }

                    tbody.appendChild(weekRow);
                }
            });
    }

    // Izvēlēties laika slotu dienai
    function showSlotSelector(cell, date) {
        if (openMenu) openMenu.remove();

        const menu = document.createElement('div');
        menu.className = 'slot-selector';

        const booked = allBookings[date] || [];
        const hasCustom = booked.some(b => b.slot === 'custom');

        const bookedSlots = booked.map(b => b.slot);
        const isHalfAM = bookedSlots.includes('half_am');
        const isHalfPM = bookedSlots.includes('half_pm');

        let availableSlots = [];

        if (!hasCustom) {
            if (isHalfAM && !isHalfPM) {
                availableSlots = ['half_pm'];
            } else if (isHalfPM && !isHalfAM) {
                availableSlots = ['half_am'];
            } else if (!isHalfAM && !isHalfPM) {
                availableSlots = ['full', 'half_am', 'half_pm'];
            }
        }

        availableSlots.push('custom');

        menu.innerHTML = availableSlots.map(slot => `
            <button data-slot="${slot}">${t('slot_' + slot)}</button>
        `).join('');

        document.body.appendChild(menu);
        openMenu = menu;

        const rect = cell.getBoundingClientRect();
        menu.style.position = 'absolute';
        menu.style.top = `${rect.bottom + window.scrollY + 5}px`;
        menu.style.left = `${rect.left + window.scrollX}px`;
        menu.style.zIndex = 9999;

        menu.querySelectorAll('button[data-slot]').forEach(btn => {
            btn.addEventListener('click', () => {
                const slot = btn.dataset.slot;
                selectedDates = selectedDates.filter(d => d.date !== date);
                selectedDates.push({ date, slot });
                menu.remove();
                openMenu = null;
                updateCalendarHighlights();
                updateBookingJSON();
            });
        });
    }

    // Iezīmē kalendārā izvēlētos datumus
    function updateCalendarHighlights() {
        document.querySelectorAll('#calendar-body td').forEach(td => {
            td.classList.remove('selected-date');
        });

        selectedDates.forEach(item => {
            const td = document.querySelector(`#calendar-body td[data-date="${item.date}"]`);
            if (td) td.classList.add('selected-date');
        });
    }

    // JSON dati paslēptajā laukā (ja nepieciešams)
    function updateBookingJSON() {
        const input = document.getElementById('admin-booking-data');
        if (input) {
            input.value = JSON.stringify(selectedDates);
        }
    }

    // Global: Tīrīt izvēli
    window.clearAdminSelection = function () {
        selectedDates = [];
        updateCalendarHighlights();
        updateBookingJSON();
    };
});
