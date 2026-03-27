const filterButton = document.querySelector('button#display_filters');
const filterForm = document.querySelector('form');

if (filterButton && filterForm) {
  filterButton.addEventListener('click', () => {
    if (filterForm.style.display === 'none') {
      filterForm.style.display = 'block';
      filterButton.innerText = 'Hide Filters';
    } else {
      filterForm.style.display = 'none';
      filterButton.innerText = 'Show Filters';
    }
  });
}
