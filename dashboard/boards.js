document.addEventListener("DOMContentLoaded", function () {
  const navLinks = document.querySelectorAll(".sidebar nav ul li");
  let tasks = [];

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
        });

      navLinks.forEach(item => item.classList.remove("active"));
      this.classList.add("active");
    });
  });

  fetch("partials/home.html")
    .then(response => response.text())
    .then(html => {
      document.getElementById("page-content").innerHTML = html;
      loadProjects();
    });

  function setupAddProjectForm(project = null) {
    const addProjectForm = document.getElementById('add-project-form');
    if (!addProjectForm) return;

    const submitBtn = addProjectForm.querySelector('button[type="submit"]');

    tasks = [];
    document.getElementById('tasks-container').innerHTML = '';

    if (project) {
      document.getElementById('project-title').value = project.title;
      document.getElementById('project-type').value = project.type;
      document.getElementById('project-description').value = project.description;
      document.getElementById('start-date').value = project.start_date;
      document.getElementById('end-date').value = project.end_date;

      submitBtn.textContent = 'Update Project';

      if (project.tasks && project.tasks.length > 0) {
        project.tasks.forEach(task => addTaskToList(task));
      }
    } else {
      submitBtn.textContent = 'Create Project';
    }

    addProjectForm.onsubmit = function (e) {
      e.preventDefault();

      const projectData = {
        id: project ? project.id : null,
        title: document.getElementById('project-title').value,
        type: document.getElementById('project-type').value,
        description: document.getElementById('project-description').value,
        start_date: document.getElementById('start-date').value,
        end_date: document.getElementById('end-date').value,
        status: 'pending',
        tasks: tasks
      };

      const apiURL = project ? 'php_api/update_project.php' : 'php_api/save_project.php';

      fetch(apiURL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(projectData)
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(project ? 'Project updated!' : 'Project created!');
            addProjectForm.reset();
            document.getElementById('nav-home').click();
          } else {
            alert(data.error || 'Something went wrong.');
          }
        });
    };

    const addTaskBtn = document.querySelector('.add-task');
    if (addTaskBtn) {
      addTaskBtn.onclick = () => {
        document.getElementById('task-form-popup').style.display = 'flex';
      };
    }

    const closeTaskBtn = document.querySelector('.close-task-form');
    if (closeTaskBtn) {
      closeTaskBtn.onclick = () => {
        document.getElementById('task-form-popup').style.display = 'none';
      };
    }

    const addTaskForm = document.getElementById('add-task-form');
    if (addTaskForm) {
      addTaskForm.onsubmit = function (e) {
        e.preventDefault();

        const task = {
          name: document.getElementById('task-name').value,
          type: document.getElementById('task-type').value,
          description: document.getElementById('task-desc').value,
          start_date: document.getElementById('task-start-date').value,
          end_date: document.getElementById('task-end-date').value
        };

        addTaskToList(task);

        this.reset();
        document.getElementById('task-form-popup').style.display = 'none';
      };
    }
  }

  function addTaskToList(task) {
    tasks.push(task);

    const taskEl = document.createElement('div');
    taskEl.classList.add('task-item');
    taskEl.innerHTML = `
      ${task.name}
      <button class="edit-task">Edit</button>
      <button class="delete-task">Delete</button>
    `;

    taskEl.querySelector('.delete-task').onclick = function () {
      tasks = tasks.filter(t => t !== task);
      taskEl.remove();
    };

    taskEl.querySelector('.edit-task').onclick = function () {
      document.getElementById('task-name').value = task.name;
      document.getElementById('task-type').value = task.type;
      document.getElementById('task-desc').value = task.description;
      document.getElementById('task-start-date').value = task.start_date;
      document.getElementById('task-end-date').value = task.end_date;

      tasks = tasks.filter(t => t !== task);
      taskEl.remove();

      document.getElementById('task-form-popup').style.display = 'flex';
    };

    document.getElementById('tasks-container').appendChild(taskEl);
  }

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
      });
  }

  function createProjectCard(project) {
    const card = document.createElement('div');
    card.className = 'project-card';

    card.innerHTML = `
      <h4>${project.title}</h4>
      <p>From: ${project.start_date} To: ${project.end_date}</p>
      <button class="see-more-btn">See More</button>
      <button class="edit-project-btn">Edit</button>
      <button class="delete-project-btn">Delete</button>
    `;

    card.querySelector('.see-more-btn').onclick = () => {
      fetch(`php_api/get_project_details.php?id=${project.id}`)
        .then(res => res.json())
        .then(fullProject => renderProjectDetails(fullProject));
    };

    card.querySelector('.edit-project-btn').onclick = () => {
      fetch(`php_api/get_project_details.php?id=${project.id}`)
        .then(res => res.json())
        .then(fullProject => {
          fetch('partials/add_project.html')
            .then(r => r.text())
            .then(html => {
              document.getElementById("page-content").innerHTML = html;
              setupAddProjectForm(fullProject);
            });
        });
    };

    card.querySelector('.delete-project-btn').onclick = () => {
      if (confirm(`Delete project "${project.title}"?`)) {
        fetch(`php_api/delete_project.php?id=${project.id}`, {
          method: 'GET'
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              alert('Project deleted.');
              card.remove();
            } else {
              alert('Error deleting.');
            }
          });
      }
    };

    return card;
  }

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
      tasksContainer.innerHTML = '<p>No tasks for this project.</p>';
    }
  }

});
