const roomSelect = document.getElementById('room-select');

roomSelect.addEventListener('change', () => {
  const roomType = roomSelect.value;

  document
    .querySelectorAll('.calendar[data-type="room"] .day')
    .forEach(day => {
      day.classList.remove('is-booked');

      if (roomType && day.dataset[roomType] === 'booked') {
        day.classList.add('is-booked');
      }
    });
});


const activitySelect = document.getElementById('feature-select');

activitySelect.addEventListener('change', () => {
  const selectedActivities = Array.from(activitySelect.selectedOptions)
                                  .map(option => option.value);

  document
    .querySelectorAll('.calendar[data-type="activity"] .day')
    .forEach(day => {
      day.classList.remove('is-booked');

      selectedActivities.forEach(activity => {
        if (day.dataset[activity] === 'booked') {
          day.classList.add('is-booked');
        }
      });
    });
});



//*document.querySelectorAll('.calendar').forEach(calendar => {
// const type = calendar.dataset.type;
  // logik baserad på typ
//});
