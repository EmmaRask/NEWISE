// --- Hämta element ---
const roomDays = document.querySelectorAll('.calendar[data-type="room"] .day');
const activityDays = document.querySelectorAll('.calendar[data-type="activity"] .day');
const roomSelect = document.getElementById('room-select');
const activitySelect = document.getElementById('feature-select');
const form = document.querySelector('form');
const hiddenDaysInput = document.getElementById('selected-days');

// --- Lagra val ---
let selectedRoomDays = [];
let selectedActivities = {};

// --- Uppdatera rumskalender ---
function updateRoomCalendar() {
    roomDays.forEach(day => {
        const dayNum = parseInt(day.textContent);
        day.classList.remove('selected-room');
        if (selectedRoomDays.includes(dayNum)) {
            day.classList.add('selected-room');
        }
    });
}

// --- Uppdatera aktivitetskalender ---
function updateActivityCalendar() {
    activityDays.forEach(day => {
        const dayNum = parseInt(day.textContent);
        day.classList.remove('active', 'selected-activity');

        // Markera endast dagar med aktivitet
        if (selectedActivities[dayNum]) {
            day.classList.add('active', 'selected-activity');
        }
    });
}

// --- Klick i rumskalender ---
roomDays.forEach(day => {
    day.addEventListener('click', () => {
        if (day.classList.contains('is-booked')) return;
        const dayNum = parseInt(day.textContent);

        if (selectedRoomDays.includes(dayNum)) {
            selectedRoomDays = selectedRoomDays.filter(d => d !== dayNum);
            delete selectedActivities[dayNum];
        } else {
            selectedRoomDays.push(dayNum);
        }

        updateRoomCalendar();
        updateActivityCalendar(); // Behöver endast uppdatera markerade aktiviteter, inte göra alla dagar aktiva
    });
});

// --- Klick i aktivitetskalender ---
activityDays.forEach(day => {
    day.addEventListener('click', () => {
        const dayNum = parseInt(day.textContent);
        const selectedOption = activitySelect.selectedOptions[0];

        if (!selectedOption) return; // inget valt
        if (!selectedRoomDays.includes(dayNum)) return; // endast för rum-valda dagar

        // Toggle aktivitet
        if (selectedActivities[dayNum] === selectedOption.value) {
            delete selectedActivities[dayNum];
        } else {
            selectedActivities[dayNum] = selectedOption.value;
        }

        updateActivityCalendar();
    });
});

// --- Ändringar i activity select ---
activitySelect.addEventListener('change', () => {
    // Ta bort aktiviteter som inte längre finns i select
    Object.keys(selectedActivities).forEach(dayNum => {
        const currentValue = selectedActivities[dayNum];
        const found = Array.from(activitySelect.selectedOptions).find(opt => opt.value === currentValue);
        if (!found) delete selectedActivities[dayNum];
    });

    updateActivityCalendar();
});

// --- Formulär submit ---
form.addEventListener('submit', event => {
    if (selectedRoomDays.length === 0) {
        alert('Please select at least one day');
        event.preventDefault();
        return;
    }

    selectedRoomDays.sort((a, b) => a - b);
    hiddenDaysInput.value = selectedRoomDays.join(',');
});
