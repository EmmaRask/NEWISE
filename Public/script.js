
const roomButtons = document.querySelectorAll('[data-room]');

roomButtons.forEach(button => {
  button.addEventListener('click', () => {
    const roomType = button.dataset.room;

    document
      .querySelectorAll('.calendar[data-type="room"] .day')
      .forEach(day => {
        day.classList.remove('is-booked');

        if (day.dataset[roomType] === 'booked') {
          day.classList.add('is-booked');
        }
      });
  });
});



//*document.querySelectorAll('.calendar').forEach(calendar => {
// const type = calendar.dataset.type;
  // logik baserad på typ
//});
