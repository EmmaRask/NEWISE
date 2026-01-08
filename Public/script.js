// Hämta element
const roomDays = document.querySelectorAll('.calendar[data-type="room"] .day');
const activityDays = document.querySelectorAll('.calendar[data-type="activity"] .day');
const roomSelect = document.getElementById('room-select');
const activitySelect = document.getElementById('feature-select');

// Lagra användarens val
let selectedRoomDays = [];
let selectedActivities = {};

// Funktion för att uppdatera rumskalendern visuellt
function updateRoomCalendar() {
    roomDays.forEach(day => {
        const dayNum = parseInt(day.textContent);

        // Ta bort tidigare markerade användarval
        day.classList.remove('selected-room');

        // Markera dagar användaren valt (om ej databokad)
        if (selectedRoomDays.includes(dayNum)) {
            day.classList.add('selected-room');
        }
    });

  roomDays.forEach(day => {
  day.addEventListener('click', () => {
    console.log('Klickade dag:', day.textContent);
    day.classList.toggle('selected-room');
  });
});


    // Efter att rum valts, uppdatera aktivitetskalendern
    updateActivityCalendar();
}

// Funktion för att uppdatera aktivitetskalendern
function updateActivityCalendar() {
    activityDays.forEach(day => {
        const dayNum = parseInt(day.textContent);

        // Rensa alla markeringar
        day.classList.remove('active', 'selected-activity');

        // Endast dagar användaren valt i rumskalendern kan bli "aktiva"
        if (selectedRoomDays.includes(dayNum)) {
            day.classList.add('active');

            // Markera aktivitet om den valts
            if (selectedActivities[dayNum]) {
                day.classList.add('selected-activity');
            }
        }
    });
}

// Lyssna på klick i rumskalendern
roomDays.forEach(day => {
    day.addEventListener('click', () => {
        // Om databokad dag, gör inget
        if (day.classList.contains('is-booked')) return;

        const dayNum = parseInt(day.textContent);

        if (selectedRoomDays.includes(dayNum)) {
            selectedRoomDays = selectedRoomDays.filter(d => d !== dayNum);
            delete selectedActivities[dayNum]; // ta bort eventuell aktivitet på dagen
        } else {
            selectedRoomDays.push(dayNum);
        }

        updateRoomCalendar();
    });
});

// Lyssna på klick i aktivitetskalendern
activityDays.forEach(day => {
    day.addEventListener('click', () => {
        const dayNum = parseInt(day.textContent);

        // Endast aktiva dagar kan få aktivitet
        if (!day.classList.contains('active')) return;

        if (selectedActivities[dayNum]) {
            delete selectedActivities[dayNum];
        } else {
            // Ta första valda aktivitet (för demonstration, kan göras multi senare)
            const firstActivity = Array.from(activitySelect.selectedOptions)[0];
            if (firstActivity) {
                selectedActivities[dayNum] = firstActivity.value;
            }
        }

        updateActivityCalendar();
    });
});

// Lyssna på select-ändringar för aktiviteter
activitySelect.addEventListener('change', () => {
    // Vi uppdaterar markerade aktiviteter på redan valda dagar
    Object.keys(selectedActivities).forEach(dayNum => {
        const currentValue = selectedActivities[dayNum];
        const found = Array.from(activitySelect.selectedOptions).find(opt => opt.value === currentValue);
        if (!found) delete selectedActivities[dayNum]; // ta bort om ej längre valt
    });

    updateActivityCalendar();
});
