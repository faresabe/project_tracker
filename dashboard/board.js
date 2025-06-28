// dashboard.js

document.addEventListener('DOMContentLoaded', () => {
  const navItems = document.querySelectorAll('.sidebar nav ul li');
  const mainContent = document.getElementById('main-content');

  // ✅ Load the Home page partial by default
  loadPage('home.html');

  // ✅ Add click events to all sidebar buttons
  navItems.forEach(item => {
    item.addEventListener('click', () => {
      // Remove .active from all
      navItems.forEach(nav => nav.classList.remove('active'));
      // Add .active to clicked one
      item.classList.add('active');

      const page = item.getAttribute('data-page');
      loadPage(page);
    });
  });

  // ✅ Function to load a page into <main>
  function loadPage(page) {
    fetch(`partials/${page}`)
      .then(response => {
        if (!response.ok) throw new Error('Page not found');
        return response.text();
      })
      .then(data => {
        mainContent.innerHTML = data;
      })
      .catch(err => {
        mainContent.innerHTML = `<p>Sorry, could not load page.</p>`;
        console.error(err);
      });
  }
});
