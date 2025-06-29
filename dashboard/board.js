document.addEventListener('DOMContentLoaded', () => {
  // Project data store (will be populated from backend)
  let projects = [];

  // DOM Elements
  const navItems = document.querySelectorAll('.sidebar nav ul li');
  const pageContent = document.getElementById('page-content');
  const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
  const sidebar = document.querySelector('.sidebar');

  // Initialize the dashboard
  initDashboard();

  // Event Listeners
  mobileMenuBtn.addEventListener('click', toggleMobileMenu);

  navItems.forEach(item => {
    item.addEventListener('click', () => {
      navItems.forEach(nav => nav.classList.remove('active'));
      item.classList.add('active');
      const page = item.getAttribute('data-page');
      if (page) loadPage(page);
      if (sidebar.classList.contains('active')) toggleMobileMenu();
    });
  });

  // Functions
  async function initDashboard() {
    try {
      projects = await fetchProjects();
      loadPage('home.html');
      document.getElementById('nav-home').classList.add('active');
      setupTaskForm();
      renderProjects();
      updateStatusSection();
    } catch (error) {
      console.error('Failed to initialize dashboard:', error);
      pageContent.innerHTML = `<div class="error-message">Failed to load data. Please refresh the page.</div>`;
    }
  }

  function toggleMobileMenu() {
    sidebar.classList.toggle('active');
    mobileMenuBtn.textContent = sidebar.classList.contains('active') ? '✕' : '☰';
  }

  function loadPage(page) {
    fetch(`partials/${page}`)
      .then(response => {
        if (!response.ok) throw new Error(`Could not load ${page}`);
        return response.text();
      })
      .then(html => {
        pageContent.innerHTML = html;
        
        if (page === 'add_project.html') {
          setupAddProjectForm();
        }
        
        if (page === 'home.html') {
          renderProjects();
          updateStatusSection();
        }
        
        setupTaskForm();
      })
      .catch(error => {
        console.error('Page load error:', error);
        pageContent.innerHTML = `<div class="error-message">Error loading page. Please try again.</div>`;
      });
  }

  async function setupAddProjectForm() {
    const form = document.getElementById('add-project-form');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const title = form.querySelector('#project-title').value.trim();
      const type = form.querySelector('#project-type').value;
      const description = form.querySelector('#project-description').value.trim();
      const startDate = form.querySelector('#start-date').value;
      const endDate = form.querySelector('#end-date').value;
      
      if (!title) {
        alert('Please enter a project title');
        return;
      }
      
      const newProject = {
        title,
        type,
        description,
        startDate,
        endDate
      };
      
      try {
        const savedProject = await saveProject(newProject);
        projects.push(savedProject);
        
        renderProjects();
        updateStatusSection();
        
        // Reset form and navigate back to home view
        form.reset();
        document.getElementById('nav-home').click();
      } catch (error) {
        console.error('Failed to save project:', error);
        alert('Failed to save project. Please try again.');
      }
    });
  }

  function renderProjects() {
    const projectsList = document.getElementById('projects-list');
    if (!projectsList) return;
    
    projectsList.innerHTML = '';
    
    projects.forEach(project => {
      const card = document.createElement('div');
      card.classList.add('project-card');
      card.setAttribute('data-status', project.status);
      card.setAttribute('data-project-id', project.id);
      
      card.innerHTML = `
        <img src="https://via.placeholder.com/54" alt="Project">
        <div>
          <div class="project-title">${project.title}</div>
          <div class="project-type">${project.type}</div>
          <div class="project-status">Status: ${project.status}</div>
          <a href="#" class="see-more">See more</a>
        </div>
      `;
      
      // Add click event to update status
      card.addEventListener('click', function(e) {
        if (!e.target.classList.contains('see-more')) {
          e.preventDefault();
          const projectId = this.getAttribute('data-project-id');
          cycleProjectStatus(projectId);
        }
      });
      
      projectsList.appendChild(card);
    });
  }

  async function cycleProjectStatus(projectId) {
    const project = projects.find(p => p.id === projectId);
    if (!project) return;
    
    // Cycle through statuses
    const statusOrder = ['pending', 'ongoing', 'completed'];
    const currentIndex = statusOrder.indexOf(project.status);
    const nextIndex = (currentIndex + 1) % statusOrder.length;
    const newStatus = statusOrder[nextIndex];
    
    try {
      await updateProject({
        id: projectId,
        status: newStatus
      });
      
      // Update local project data
      project.status = newStatus;
      
      // Update UI
      renderProjects();
      updateStatusSection();
    } catch (error) {
      console.error('Failed to update project status:', error);
      alert('Failed to update project status. Please try again.');
    }
  }

  function updateStatusSection() {
    const statusCards = {
      completed: document.querySelector('.status-card.completed .status-percent'),
      pending: document.querySelector('.status-card.pending .status-percent'),
      ongoing: document.querySelector('.status-card.ongoing .status-percent')
    };
    
    if (!statusCards.completed) return;
    
    const totalProjects = projects.length;
    const counts = {
      completed: projects.filter(p => p.status === 'completed').length,
      pending: projects.filter(p => p.status === 'pending').length,
      ongoing: projects.filter(p => p.status === 'ongoing').length
    };
    
    statusCards.completed.textContent = totalProjects > 0 
      ? `${Math.round((counts.completed / totalProjects) * 100)}%` 
      : '0%';
    statusCards.pending.textContent = totalProjects > 0 
      ? `${Math.round((counts.pending / totalProjects) * 100)}%` 
      : '0%';
    statusCards.ongoing.textContent = totalProjects > 0 
      ? `${Math.round((counts.ongoing / totalProjects) * 100)}%` 
      : '0%';
  }

  // API Functions
  async function fetchProjects() {
    try {
      const response = await fetch('http://localhost/pro_management_frontend/dashboard/php_api/projects.php');
      if (!response.ok) throw new Error('Network response was not ok');
      return await response.json();
    } catch (error) {
      console.error('Error fetching projects:', error);
      throw error;
    }
  }

  async function saveProject(project) {
    try {
      const response = await fetch('http://localhost/pro_management_frontend/dashboard/php_api/projects.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(project)
      });
      if (!response.ok) throw new Error('Network response was not ok');
      return await response.json();
    } catch (error) {
      console.error('Error saving project:', error);
      throw error;
    }
  }

  async function updateProject(updates) {
    try {
      const response = await fetch('http://localhost/pro_management_frontend/dashboard/php_api/projects.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updates)
      });
      if (!response.ok) throw new Error('Network response was not ok');
      return await response.json();
    } catch (error) {
      console.error('Error updating project:', error);
      throw error;
    }
  }

  // Task Form Functionality
  function setupTaskForm() {
    const addTaskLink = document.querySelector('.add-task');
    const taskFormPopup = document.getElementById('task-form-popup');
    const closeTaskForm = document.querySelector('.close-task-form');
    const taskForm = document.getElementById('add-task-form');
    const priorityOptions = document.querySelectorAll('.priority-option');
    
    if (!addTaskLink || !taskFormPopup || !taskForm) return;
    
    let currentPriority = 'high';
    
    // Set initial priority selection
    priorityOptions.forEach(option => {
      if (option.getAttribute('data-priority') === currentPriority) {
        option.classList.add('selected');
      }
    });
    
    addTaskLink.addEventListener('click', (e) => {
      e.preventDefault();
      taskFormPopup.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    });
    
    if (closeTaskForm) {
      closeTaskForm.addEventListener('click', () => {
        taskFormPopup.style.display = 'none';
        document.body.style.overflow = '';
      });
    }
    
    taskFormPopup.addEventListener('click', (e) => {
      if (e.target === taskFormPopup) {
        taskFormPopup.style.display = 'none';
        document.body.style.overflow = '';
      }
    });
    
    priorityOptions.forEach(option => {
      option.addEventListener('click', () => {
        priorityOptions.forEach(opt => opt.classList.remove('selected'));
        option.classList.add('selected');
        currentPriority = option.getAttribute('data-priority');
      });
    });
    
    taskForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const taskName = document.getElementById('task-name').value.trim();
      const taskType = document.getElementById('task-type').value;
      const taskDesc = document.getElementById('task-desc').value.trim();
      const startDate = document.getElementById('start-date').value;
      const endDate = document.getElementById('end-date').value;
      
      if (!taskName || !taskType || !startDate || !endDate) {
        alert('Please fill in all required fields');
        return;
      }
      
      const newTask = {
        name: taskName,
        type: taskType,
        description: taskDesc,
        startDate,
        endDate,
        priority: currentPriority,
        status: 'pending'
      };
      
      try {
        // In a real app, you would save the task to your backend here
        console.log('New task created:', newTask);

        // Reset form
        taskForm.reset();
        currentPriority = 'high';
        priorityOptions.forEach(opt => {
          opt.classList.remove('selected');
          if (opt.getAttribute('data-priority') === 'high') {
            opt.classList.add('selected');
          }
        });
        
        // Close the form
        taskFormPopup.style.display = 'none';
        document.body.style.overflow = '';
        
        alert('Task created successfully!');
      } catch (error) {
        console.error('Failed to create task:', error);
        alert('Failed to create task. Please try again.');
      }
    });
  }
});