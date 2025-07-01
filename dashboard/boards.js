document.addEventListener("DOMContentLoaded", function () {
  const navLinks = document.querySelectorAll(".sidebar nav ul li");
  const tasks = []; // Store tasks temporarily

  // Sidebar nav click handler
  navLinks.forEach(link => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      const page = this.getAttribute("data-page");

      fetch(`partials/${page}`)
        .then(response => response.text())
        .then(html => {
          document.getElementById("page-content").innerHTML = html;

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

  // Load home page on start
  fetch("partials/home.html")
    .then(response => response.text())
    .then(html => {
      document.getElementById("page-content").innerHTML = html;
      loadProjects();
    });

  // Main logic to setup Add Project form
  function setupAddProjectForm() {
    const addProjectForm = document.getElementById('add-project-form');
    if (!addProjectForm) return;

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
            tasks.length = 0;
            addProjectForm.reset();
            document.getElementById('tasks-container').innerHTML = '';
            document.getElementById('nav-home').click();
          }
        })
        .catch(err => console.error('Error:', err));
    });

    // Show Add Task popup
    const addTaskBtn = document.querySelector('.add-task');
    if (addTaskBtn) {
      addTaskBtn.addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('task-form-popup').style.display = 'flex';
      });
    }

    // Close popup
    const closeTaskBtn = document.querySelector('.close-task-form');
    if (closeTaskBtn) {
      closeTaskBtn.addEventListener('click', function () {
        document.getElementById('task-form-popup').style.display = 'none';
      });
    }

    // Add Task form logic
    const addTaskForm = document.getElementById('add-task-form');
    if (addTaskForm) {
      addTaskForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const task = {
          name: document.getElementById('task-name').value,
          type: document.getElementById('task-type').value,
          description: document.getElementById('task-desc').value,
          start_date: document.getElementById('task-start-date').value,
          end_date: document.getElementById('task-end-date').value
        };

        tasks.push(task);

        const taskEl = document.createElement('div');
        taskEl.classList.add('task-item');
        taskEl.innerHTML = `
          ${task.name}
          <button class="edit-task">Edit</button>
          <button class="delete-task">Delete</button>
        `;

        // Delete Task
        taskEl.querySelector('.delete-task').addEventListener('click', function () {
          const index = tasks.indexOf(task);
          if (index > -1) tasks.splice(index, 1);
          taskEl.remove();
        });

        // Edit Task
        taskEl.querySelector('.edit-task').addEventListener('click', function () {
          document.getElementById('task-name').value = task.name;
          document.getElementById('task-type').value = task.type;
          document.getElementById('task-desc').value = task.description;

          const index = tasks.indexOf(task);
          if (index > -1) tasks.splice(index, 1);
          taskEl.remove();

          document.getElementById('task-form-popup').style.display = 'flex';
        });

        document.getElementById('tasks-container').appendChild(taskEl);

        this.reset();
        document.getElementById('task-form-popup').style.display = 'none';
      });
    }
  }

  // Load project cards on Home
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

  // Build simple project card
  function createProjectCard(project) {
    const card = document.createElement('div');
    card.className = 'project-card';
    
    card.innerHTML = `
      <h4>${project.title}</h4>
      <p>From: ${project.start_date} To: ${project.end_date}</p>
      <button class="see-more-btn">See More</button>`;

    // Handle See More: fetch project details dynamically
    const seeMoreBtn = card.querySelector('.see-more-btn');
    seeMoreBtn.addEventListener('click', function () {
      fetch(`php_api/get_project_details.php?id=${project.id}`)
        .then(response => response.json())
        .then(fullProject => {
          renderProjectDetails(fullProject);
        })
        .catch(err => console.error('Error loading project details:', err));
    });

    return card;
  }

  // Render full project details with tasks dynamically
  function renderProjectDetails(project) {
    const container = document.getElementById('page-content');
    container.innerHTML = `
      <div class="project-title">${project.title}</div>
      <div class="tasks-container"></div>
    `;

    const tasksContainer = container.querySelector('.tasks-container');

    if (project.tasks && project.tasks.length > 0) {
      project.tasks.forEach(task => {
        const taskCard = document.createElement('div');
        taskCard.className = 'task-card';
        taskCard.innerHTML = `
          <div class="task-header">
            ${task.name}
            <span class="edit-icon">&#9998;</span>
            <div class="status">
              <input type="checkbox" ${task.done ? 'checked' : ''}>
            </div>
          </div>
          <div class="task-desc">${task.description}</div>
          <div class="task-footer">
            <span style="font-size:1.2em;">&#9200;</span>
            <span class="date">${task.start_date} to ${task.end_date}</span>
          </div>
        `;
        tasksContainer.appendChild(taskCard);
      });
    } else {
      tasksContainer.innerHTML = '<p>No tasks found for this project.</p>';
    }
  }

});
