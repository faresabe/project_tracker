document.addEventListener("DOMContentLoaded", function () {
  const navLinks = document.querySelectorAll(".sidebar nav ul li");
  const tasks = []; // Temporary store for tasks

  // === Sidebar navigation ===
  navLinks.forEach(link => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      const page = this.getAttribute("data-page");

      fetch(`partials/${page}`)
        .then(response => response.text())
        .then(html => {
          document.getElementById("page-content").innerHTML = html;

          // Setup forms if needed
          if (page === 'add_project.html') {
            setupAddProjectForm();
          } else if (page === 'home.html') {
            loadProjects();
          }
        })
        .catch(err => console.error("Error loading page:", err));

      navLinks.forEach(item => item.classList.remove("active"));
      this.classList.add("active");
    });
  });

  // === Load home page by default ===
  fetch("partials/home.html")
    .then(response => response.text())
    .then(html => {
      document.getElementById("page-content").innerHTML = html;
      loadProjects();
    });

  // === Function to setup Add Project form ===
  function setupAddProjectForm() {
    const addProjectForm = document.getElementById('add-project-form');
    if (!addProjectForm) return;

    // Handle form submit
    addProjectForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const projectData = {
        title: document.getElementById('project-title').value,
        type: document.getElementById('project-type').value,
        description: document.getElementById('project-description').value,
        start_date: document.getElementById('start-date').value,
        end_date: document.getElementById('end-date').value,
        status: 'pending',
        tasks: tasks
      };

      fetch('php_api/save_project.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(projectData)
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Project saved successfully!');
            tasks.length = 0; // Clear tasks
            addProjectForm.reset();
            document.getElementById('tasks-container').innerHTML = '';
            document.getElementById('nav-home').click(); // Triggers loadProjects() via nav link
          }
        })
        .catch(err => console.error('Error:', err));
    });

    // Show Add Task popup
    const addTaskBtn = addProjectForm.querySelector('.add-task');
    addTaskBtn.addEventListener('click', function (e) {
      e.preventDefault();
      document.getElementById('task-form-popup').style.display = 'flex';
    });

    // Close popup
    const closeTaskBtn = document.querySelector('.close-task-form');
    closeTaskBtn.addEventListener('click', function () {
      document.getElementById('task-form-popup').style.display = 'none';
    });

    // Handle Add Task form submit
    const addTaskForm = document.getElementById('add-task-form');
    addTaskForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const task = {
        name: document.getElementById('task-name').value,
        type: document.getElementById('task-type').value,
        description: document.getElementById('task-desc').value,
        start_date: document.getElementById('start-date').value,
        end_date: document.getElementById('end-date').value,
        priority: document.querySelector('.priority-option.selected').dataset.priority
      };

      tasks.push(task);

      const taskEl = document.createElement('div');
      taskEl.innerText = `${task.name} (${task.type}) - ${task.priority}`;
      document.getElementById('tasks-container').appendChild(taskEl);

      this.reset();
      document.getElementById('task-form-popup').style.display = 'none';
    });

    // Priority option toggle
    document.querySelectorAll('.priority-option').forEach(option => {
      option.addEventListener('click', function () {
        document.querySelectorAll('.priority-option').forEach(opt => opt.classList.remove('selected'));
        this.classList.add('selected');
      });
    });
  }

  // === Load projects on Home page ===
  function loadProjects() {
  
    fetch('php_api/get_projects.php')
      .then(response => response.json())
      .then(projects => {
     
        const container = document.getElementById('projects-list');
        if (!container) return;

        container.innerHTML = '';

        projects.forEach(project => {
          const card = createProjectCard(project);
          container.appendChild(card);
        });
      })
      .catch(err => console.error('Error loading projects:', err));
  }

  // === Build project card HTML ===
  function createProjectCard(project) {
    const card = document.createElement('div');
    card.className = 'project-card';
    card.innerHTML = `
      <h4>${project.title}</h4>
      <p>${project.description}</p>
      <p>Type: ${project.type}</p>
      <p>From: ${project.start_date} To: ${project.end_date}</p>
      <p>Status: ${project.status}</p>
    `;
    return card;
  }
});
