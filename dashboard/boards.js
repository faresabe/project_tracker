document.addEventListener("DOMContentLoaded", function () {
  const navLinks = document.querySelectorAll(".sidebar nav ul li");
  let tasks = [];
  let currentUser = null;

  // Check authentication on load
  checkAuth();

  function checkAuth() {
    fetch('../authentication/auth_php/check_auth.php')
      .then(response => response.json())
      .then(data => {
        if (data.authenticated) {
          currentUser = data.user;
          updateUserInfo();
        } else {
          window.location.href = '../authentication/sign_in.html';
        }
      })
      .catch(() => {
        window.location.href = '../authentication/sign_in.html';
      });
  }

  function updateUserInfo() {
    const userNameEl = document.querySelector('.user-name');
    const userRoleEl = document.querySelector('.user-role');
    
    if (userNameEl && currentUser) {
      userNameEl.textContent = currentUser.name;
    }
    if (userRoleEl) {
      userRoleEl.textContent = 'Project Manager';
    }
  }

  // Navigation handling
  navLinks.forEach(link => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const page = this.getAttribute("data-page");
      loadPage(page);
      
      navLinks.forEach(item => item.classList.remove("active"));
      this.classList.add("active");
    });
  });

  // Mobile menu toggle
  const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
  const sidebar = document.querySelector('.sidebar');
  
  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', () => {
      sidebar.classList.toggle('active');
    });
  }

  // Load initial page
  loadPage('home.html');

  function loadPage(page) {
    fetch(`partials/${page}`)
      .then(response => response.text())
      .then(html => {
        document.getElementById("page-content").innerHTML = html;

        if (page === 'add_project.html') {
          setupAddProjectForm();
        } else if (page === 'home.html') {
          loadProjects();
        } else if (page === 'setting.html') {
          setupSettingsForm();
        }
      })
      .catch(error => {
        console.error('Error loading page:', error);
        document.getElementById("page-content").innerHTML = '<p>Error loading page</p>';
      });
  }

  function setupAddProjectForm(project = null) {
    const addProjectForm = document.getElementById('add-project-form');
    if (!addProjectForm) return;

    const submitBtn = addProjectForm.querySelector('button[type="submit"]');
    tasks = [];
    
    const tasksContainer = document.getElementById('tasks-container');
    if (tasksContainer) {
      tasksContainer.innerHTML = '';
    }

    if (project) {
      document.getElementById('project-title').value = project.title || '';
      document.getElementById('project-type').value = project.type || 'web';
      document.getElementById('project-description').value = project.description || '';
      document.getElementById('start-date').value = project.start_date || '';
      document.getElementById('end-date').value = project.end_date || '';

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
        status: project ? project.status : 'pending',
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
            alert(project ? 'Project updated successfully!' : 'Project created successfully!');
            addProjectForm.reset();
            tasks = [];
            loadPage('home.html');
            document.getElementById('nav-home').click();
          } else {
            alert(data.error || 'Something went wrong.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Network error occurred.');
        });
    };

    setupTaskForm();
  }

  function setupTaskForm() {
    const addTaskBtn = document.querySelector('.add-task');
    const closeTaskBtn = document.querySelector('.close-task-form');
    const taskFormPopup = document.getElementById('task-form-popup');
    const addTaskForm = document.getElementById('add-task-form');

    if (addTaskBtn) {
      addTaskBtn.onclick = (e) => {
        e.preventDefault();
        taskFormPopup.style.display = 'flex';
      };
    }

    if (closeTaskBtn) {
      closeTaskBtn.onclick = () => {
        taskFormPopup.style.display = 'none';
        addTaskForm.reset();
      };
    }

    // Close popup when clicking outside
    if (taskFormPopup) {
      taskFormPopup.onclick = (e) => {
        if (e.target === taskFormPopup) {
          taskFormPopup.style.display = 'none';
          addTaskForm.reset();
        }
      };
    }

    if (addTaskForm) {
      addTaskForm.onsubmit = function (e) {
        e.preventDefault();

        const task = {
          name: document.getElementById('task-name').value,
          type: document.getElementById('task-type').value,
          description: document.getElementById('task-desc').value,
          start_date: document.getElementById('task-start-date').value,
          end_date: document.getElementById('task-end-date').value,
          status: document.getElementById('task-status').value
        };

        addTaskToList(task);
        this.reset();
        taskFormPopup.style.display = 'none';
      };
    }
  }

  function addTaskToList(task) {
    tasks.push(task);

    const tasksContainer = document.getElementById('tasks-container');
    if (!tasksContainer) return;

    const taskEl = document.createElement('div');
    taskEl.classList.add('task-item');
    taskEl.innerHTML = `
      <div class="task-content">
        <strong>${task.name}</strong>
        <span class="task-type ${task.type}">${task.type}</span>
        <p>${task.description}</p>
        <small>From: ${task.start_date} To: ${task.end_date}</small>
      </div>
      <div class="task-actions">
        <button class="edit-task" type="button">Edit</button>
        <button class="delete-task" type="button">Delete</button>
      </div>
    `;

    taskEl.querySelector('.delete-task').onclick = function () {
      if (confirm('Delete this task?')) {
        tasks = tasks.filter(t => t !== task);
        taskEl.remove();
      }
    };

    taskEl.querySelector('.edit-task').onclick = function () {
      // Fill form with task data
      document.getElementById('task-name').value = task.name;
      document.getElementById('task-type').value = task.type;
      document.getElementById('task-desc').value = task.description;
      document.getElementById('task-start-date').value = task.start_date;
      document.getElementById('task-end-date').value = task.end_date;
      document.getElementById('task-status').value = task.status;

      // Remove task from list
      tasks = tasks.filter(t => t !== task);
      taskEl.remove();

      // Show form
      document.getElementById('task-form-popup').style.display = 'flex';
    };

    tasksContainer.appendChild(taskEl);
  }

  function loadProjects() {
    fetch('php_api/get_projects.php')
      .then(response => response.json())
      .then(data => {
        if (data.error) {
          console.error('Error:', data.error);
          return;
        }

        updateStatusCards(data.stats);
        renderProjects(data.projects);
      })
      .catch(error => {
        console.error('Error loading projects:', error);
      });
  }

  function updateStatusCards(stats) {
    const completedCard = document.querySelector('.status-card.completed .status-percent');
    const pendingCard = document.querySelector('.status-card.pending .status-percent');
    const ongoingCard = document.querySelector('.status-card.ongoing .status-percent');

    if (completedCard) completedCard.textContent = `${stats.completed}%`;
    if (pendingCard) pendingCard.textContent = `${stats.pending}%`;
    if (ongoingCard) ongoingCard.textContent = `${stats.ongoing}%`;

    // Add click handlers for status filtering
    document.querySelectorAll('.status-card').forEach(card => {
      card.addEventListener('click', () => {
        const filter = card.getAttribute('data-filter');
        filterProjectsByStatus(filter);
      });
    });
  }

  function filterProjectsByStatus(status) {
    const projectCards = document.querySelectorAll('.project-card');
    projectCards.forEach(card => {
      const projectStatus = card.getAttribute('data-status');
      if (status === 'all' || projectStatus === status) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  }

  function renderProjects(projects) {
    const container = document.getElementById('projects-list');
    if (!container) return;

    container.innerHTML = '';
    
    if (projects.length === 0) {
      container.innerHTML = '<p>No projects found. <a href="#" onclick="document.getElementById(\'nav-add\').click()">Create your first project</a></p>';
      return;
    }

    projects.forEach(project => {
      const card = createProjectCard(project);
      container.appendChild(card);
    });
  }

  function createProjectCard(project) {
    const card = document.createElement('div');
    card.className = 'project-card';
    card.setAttribute('data-status', project.status);

    const statusClass = project.status || 'pending';
    const statusText = project.status ? project.status.charAt(0).toUpperCase() + project.status.slice(1) : 'Pending';

    card.innerHTML = `
      <div class="project-header">
        <h4>${project.title}</h4>
        <span class="project-status ${statusClass}">${statusText}</span>
      </div>
      <p class="project-description">${project.description || 'No description'}</p>
      <p class="project-dates">From: ${project.start_date || 'Not set'} To: ${project.end_date || 'Not set'}</p>
      <div class="project-actions">
        <button class="see-more-btn">View Tasks</button>
        <button class="edit-project-btn">Edit</button>
        <button class="delete-project-btn">Delete</button>
        <select class="status-select">
          <option value="pending" ${project.status === 'pending' ? 'selected' : ''}>Pending</option>
          <option value="ongoing" ${project.status === 'ongoing' ? 'selected' : ''}>Ongoing</option>
          <option value="completed" ${project.status === 'completed' ? 'selected' : ''}>Completed</option>
        </select>
      </div>
    `;

    // Status change handler
    const statusSelect = card.querySelector('.status-select');
    statusSelect.addEventListener('change', (e) => {
      updateProjectStatus(project.id, e.target.value);
    });

    // View tasks handler
    card.querySelector('.see-more-btn').onclick = () => {
      fetch(`php_api/get_project_details.php?id=${project.id}`)
        .then(res => res.json())
        .then(fullProject => {
          if (fullProject.error) {
            alert(fullProject.error);
            return;
          }
          renderProjectDetails(fullProject);
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error loading project details');
        });
    };

    // Edit handler
    card.querySelector('.edit-project-btn').onclick = () => {
      fetch(`php_api/get_project_details.php?id=${project.id}`)
        .then(res => res.json())
        .then(fullProject => {
          if (fullProject.error) {
            alert(fullProject.error);
            return;
          }
          loadPage('add_project.html');
          setTimeout(() => setupAddProjectForm(fullProject), 100);
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error loading project for editing');
        });
    };

    // Delete handler
    card.querySelector('.delete-project-btn').onclick = () => {
      if (confirm(`Are you sure you want to delete "${project.title}"?`)) {
        fetch(`php_api/delete_project.php?id=${project.id}`)
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              alert('Project deleted successfully');
              loadProjects(); // Reload projects
            } else {
              alert(data.error || 'Error deleting project');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Network error occurred');
          });
      }
    };

    return card;
  }

  function updateProjectStatus(projectId, newStatus) {
    fetch('php_api/update_project_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: projectId, status: newStatus })
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          loadProjects(); // Reload to update stats
        } else {
          alert(data.error || 'Error updating status');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred');
      });
  }

  function renderProjectDetails(project) {
    const container = document.getElementById('page-content');

    let tasksHtml = '';
    if (project.tasks && project.tasks.length > 0) {
      project.tasks.forEach(task => {
        tasksHtml += `
          <div class="task-card">
            <div class="task-card-header">
              <div class="task-header-left">
                <span class="task-title">${task.name}</span>
                <span class="task-type ${task.type}">${task.type}</span>
              </div>
              <div class="task-header-right">
                <span class="task-priority ${task.status}">${task.status}</span>
                <button class="delete-btn" onclick="deleteTask(${task.id}, ${project.id})">Delete</button>
              </div>
            </div>
            <div class="task-desc">${task.description || 'No description'}</div>
            <div class="task-footer">
              <span class="due-icon">📅</span>
              <span class="due-date">Due: ${task.end_date || 'Not set'}</span>
            </div>
          </div>
        `;
      });
    } else {
      tasksHtml = '<p>No tasks for this project. <button onclick="editProject(' + project.id + ')">Add tasks</button></p>';
    }

    container.innerHTML = `
      <div class="project-details">
        <div class="project-details-header">
          <h2>${project.title}</h2>
          <div class="project-details-actions">
            <button onclick="editProject(${project.id})" class="edit-project-btn">Edit Project</button>
            <button onclick="loadPage('home.html')" class="back-btn">Back to Projects</button>
          </div>
        </div>
        <div class="project-info">
          <p><strong>Type:</strong> ${project.type}</p>
          <p><strong>Description:</strong> ${project.description || 'No description'}</p>
          <p><strong>Status:</strong> ${project.status}</p>
          <p><strong>Timeline:</strong> ${project.start_date || 'Not set'} to ${project.end_date || 'Not set'}</p>
        </div>
        <h3>Tasks</h3>
        <div class="task-cards-container">
          ${tasksHtml}
        </div>
      </div>
    `;
  }

  // Global functions for inline handlers
  window.editProject = function(projectId) {
    fetch(`php_api/get_project_details.php?id=${projectId}`)
      .then(res => res.json())
      .then(fullProject => {
        if (fullProject.error) {
          alert(fullProject.error);
          return;
        }
        loadPage('add_project.html');
        setTimeout(() => setupAddProjectForm(fullProject), 100);
      });
  };

  window.deleteTask = function(taskId, projectId) {
    if (confirm('Delete this task?')) {
      // For now, we'll reload the project and remove the task
      // In a full implementation, you'd have a separate delete task endpoint
      alert('Task deletion would be implemented with a separate API endpoint');
    }
  };

  function setupSettingsForm() {
    const settingsForm = document.querySelector('#page-content form');
    if (!settingsForm) return;

    // Pre-fill with current user data
    if (currentUser) {
      const nameField = document.getElementById('user-name');
      if (nameField) {
        nameField.value = currentUser.name;
      }
    }

    settingsForm.onsubmit = function(e) {
      e.preventDefault();
      alert('Settings functionality would be implemented here');
    };
  }

  // Logout handler
  document.getElementById('logout-btn').addEventListener('click', function(e) {
    e.preventDefault();
    
    if (confirm('Are you sure you want to logout?')) {
      fetch('../authentication/auth_php/logout.php')
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            window.location.href = '../index.html';
          }
        })
        .catch(() => {
          // Even if there's an error, redirect to login
          window.location.href = '../index.html';
        });
    }
  });

  // Search functionality
  const searchBar = document.querySelector('.search-bar');
  if (searchBar) {
    searchBar.addEventListener('input', function(e) {
      const searchTerm = e.target.value.toLowerCase();
      const projectCards = document.querySelectorAll('.project-card');
      
      projectCards.forEach(card => {
        const title = card.querySelector('h4').textContent.toLowerCase();
        const description = card.querySelector('.project-description').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    });
  }
});