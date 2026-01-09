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

    updateActivityCalendar();
}

// --- Uppdatera aktivitetskalender ---
function updateActivityCalendar() {
    activityDays.forEach(day => {
        const dayNum = parseInt(day.textContent);

        day.classList.remove('active', 'selected-activity');

        if (selectedRoomDays.includes(dayNum)) {
            day.classList.add('active');
            if (selectedActivities[dayNum]) {
                day.classList.add('selected-activity');
            }
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
    });
});

// --- Klick i aktivitetskalender ---
activityDays.forEach(day => {
    day.addEventListener('click', () => {
        const dayNum = parseInt(day.textContent);

        if (!day.classList.contains('active')) return;

        if (selectedActivities[dayNum]) {
            delete selectedActivities[dayNum];
        } else {
            const firstActivity = Array.from(activitySelect.selectedOptions)[0];
            if (firstActivity) selectedActivities[dayNum] = firstActivity.value;
        }

        updateActivityCalendar();
    });
});

// --- Ändringar i aktivitet-select ---
activitySelect.addEventListener('change', () => {
    Object.keys(selectedActivities).forEach(dayNum => {
        const currentValue = selectedActivities[dayNum];
        const found = Array.from(activitySelect.selectedOptions).find(opt => opt.value === currentValue);
        if (!found) delete selectedActivities[dayNum];
    });

    updateActivityCalendar();
});

// --- Formulär submission ---
form.addEventListener('submit', event => {
    if (selectedRoomDays.length === 0) {
        alert('Please select at least one day');
        event.preventDefault();
        return;
    }

    selectedRoomDays.sort((a, b) => a - b);
    hiddenDaysInput.value = selectedRoomDays.join(',');
});
