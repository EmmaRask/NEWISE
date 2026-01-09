document.addEventListener('DOMContentLoaded', () => {

    // ------------------------------
    // 1️⃣ Fade-karuseller
    // ------------------------------
    function initCarousel(carouselSelector, imageArray, interval = 3000) {
        const carousel = document.querySelector(carouselSelector);
        if (!carousel) return;

        // Rensa befintliga bilder
        carousel.innerHTML = '';

        // Skapa img-element för varje bild
        imageArray.forEach((src, i) => {
            const img = document.createElement('img');
            img.src = src;
            img.style.opacity = i === 0 ? '1' : '0';
            img.style.position = 'absolute';
            img.style.top = 0;
            img.style.left = 0;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.transition = 'opacity 0.8s ease-in-out';
            carousel.appendChild(img);
        });

        const slides = carousel.querySelectorAll('img');
        let currentIndex = 0;

        setInterval(() => {
            const prevIndex = currentIndex;
            currentIndex = (currentIndex + 1) % slides.length;

            slides[prevIndex].style.opacity = '0';
            slides[currentIndex].style.opacity = '1';
        }, interval);
    }

    // Initiera karuseller
    initCarousel('.hero-carousel', heroImages, 4000);
    initCarousel('.activityCarousel', activityImages, 5000);

    // ------------------------------
    // 2️⃣ Kalenderfunktioner
    // ------------------------------
    const roomDays = document.querySelectorAll('.calendar[data-type="room"] .day');
    const activityDays = document.querySelectorAll('.calendar[data-type="activity"] .day');
    const roomSelect = document.getElementById('room-select');
    const activitySelect = document.getElementById('feature-select');
    const form = document.querySelector('form');
    const hiddenDaysInput = document.getElementById('selected-days');

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
            updateActivityCalendar();
        });
    });

    // --- Klick i aktivitetskalender ---
    activityDays.forEach(day => {
        day.addEventListener('click', () => {
            const dayNum = parseInt(day.textContent);
            const selectedOption = activitySelect.selectedOptions[0];

            if (!selectedOption) return; // inget valt
            if (!selectedRoomDays.includes(dayNum)) return; // endast rum-valda dagar

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

});

