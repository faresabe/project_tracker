document.addEventListener('DOMContentLoaded', () => {
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
      // Update active navigation item
      navItems.forEach(nav => nav.classList.remove('active'));
      item.classList.add('active');
      
      // Load the selected page
      const page = item.getAttribute('data-page');
      if (page) {
        loadPage(page);
      }
      
      // Close mobile menu if open
      if (sidebar.classList.contains('active')) {
        toggleMobileMenu();
      }
    });
  });
  
  // Functions
  function initDashboard() {
    // Load the home page by default
    loadPage('home.html');
    
    // Set home nav item as active
    document.getElementById('nav-home').classList.add('active');
    
    // Initialize task form functionality
    setupTaskForm();
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
        
        // Initialize page-specific functionality
        if (page === 'add_project.html') {
          setupAddProjectForm();
        }
        
        // Re-setup task form after page load in case it was in the partial
        setupTaskForm();
      })
      .catch(error => {
        pageContent.innerHTML = `<div class="error-message">Error loading page. Please try again.</div>`;
        console.error(error);
      });
  }
  
  function setupAddProjectForm() {
    const form = document.getElementById('add-project-form');
    if (!form) return;
    
    const createBtn = form.querySelector('.create-project');
    
    createBtn.addEventListener('click', (e) => {
      e.preventDefault();
      
      // Get form values
      const title = form.querySelector('#project-title').value.trim();
      const type = form.querySelector('#project-type').value;
      const description = form.querySelector('#project-description').value.trim();
      const startDate = form.querySelector('#start-date').value.trim();
      const endDate = form.querySelector('#end-date').value.trim();
      
      // Validate
      if (!title) {
        alert('Please enter a project title');
        return;
      }
      
      // Create project object
      const newProject = {
        title,
        type,
        description,
        startDate,
        endDate,
        status: 'pending' // Default status
      };
      
      // In a real app, you would save to a database here
      console.log('New project:', newProject);
      
      // Add project card to UI
      addProjectCard(newProject);
      
      // Return to home page
      loadPage('home.html');
      document.getElementById('nav-home').classList.add('active');
      document.getElementById('nav-add').classList.remove('active');
    });
  }
  
  function addProjectCard(project) {
    const projectsList = document.getElementById('projects-list');
    if (!projectsList) return;
    
    const card = document.createElement('div');
    card.classList.add('project-card');
    card.setAttribute('data-status', project.status);
    
    card.innerHTML = `
      <img src="https://via.placeholder.com/54" alt="Project">
      <div>
        <div class="project-title">${project.title}</div>
        <div class="project-type">${project.type}</div>
        <div class="project-description">${project.description}</div>
        <a href="#" class="see-more">See more</a>
      </div>
    `;
    
    projectsList.appendChild(card);
  }

  function setupTaskForm() {
    // Task Form Elements
    const addTaskLink = document.querySelector('.add-task');
    const taskFormPopup = document.getElementById('task-form-popup');
    const closeTaskForm = document.querySelector('.close-task-form');
    const taskForm = document.getElementById('add-task-form');
    const priorityOptions = document.querySelectorAll('.priority-option');
    
    // Current task data
    let currentPriority = 'high';
    
    // Only proceed if we have the task form elements
    if (!addTaskLink || !taskFormPopup || !taskForm) return;
    
    // Open task form
    addTaskLink.addEventListener('click', (e) => {
      e.preventDefault();
      taskFormPopup.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    });
    
    // Close task form
    if (closeTaskForm) {
      closeTaskForm.addEventListener('click', () => {
        taskFormPopup.style.display = 'none';
        document.body.style.overflow = '';
      });
    }
    
    // Close when clicking outside form
    taskFormPopup.addEventListener('click', (e) => {
      if (e.target === taskFormPopup) {
        taskFormPopup.style.display = 'none';
        document.body.style.overflow = '';
      }
    });
    
    // Priority selection
    priorityOptions.forEach(option => {
      option.addEventListener('click', () => {
        // Remove selected class from all options
        priorityOptions.forEach(opt => opt.classList.remove('selected'));
        
        // Add selected class to clicked option
        option.classList.add('selected');
        
        // Update current priority
        currentPriority = option.getAttribute('data-priority');
      });
    });
    
    // Form submission
    taskForm.addEventListener('submit', (e) => {
      e.preventDefault();
      
      // Get form values
      const taskName = document.getElementById('task-name').value.trim();
      const taskType = document.getElementById('task-type').value;
      const taskDesc = document.getElementById('task-desc').value.trim();
      const startDate = document.getElementById('start-date').value;
      const endDate = document.getElementById('end-date').value;
      
      // Validate form
      if (!taskName || !taskType || !startDate || !endDate) {
        alert('Please fill in all required fields');
        return;
      }
      
      // Create task object
      const newTask = {
        id: Date.now().toString(),
        name: taskName,
        type: taskType,
        description: taskDesc,
        startDate,
        endDate,
        priority: currentPriority,
        status: 'pending',
        createdAt: new Date().toISOString()
      };
      
      console.log('New task created:', newTask);
      
      // Add the task to your UI
      addTaskToUI(newTask);
      
      // Reset form
      taskForm.reset();
      currentPriority = 'high';
      
      // Reset priority selection
      priorityOptions.forEach(opt => {
        opt.classList.remove('selected');
        if (opt.getAttribute('data-priority') === 'high') {
          opt.classList.add('selected');
        }
      });
      
      // Close the form
      taskFormPopup.style.display = 'none';
      document.body.style.overflow = '';
    });
  }
  
  function addTaskToUI(task) {
    // Find or create a tasks container
    let tasksContainer = document.getElementById('tasks-container');
    
    if (!tasksContainer) {
      tasksContainer = document.createElement('div');
      tasksContainer.id = 'tasks-container';
      tasksContainer.className = 'tasks-list';
      document.getElementById('page-content').appendChild(tasksContainer);
    }
    
    const taskElement = document.createElement('div');
    taskElement.className = 'task-item';
    taskElement.innerHTML = `
      <div class="task-info">
        <h4>${task.name}</h4>
        <span class="task-type ${task.type}">${task.type}</span>
        <span class="task-priority ${task.priority}">${task.priority}</span>
      </div>
      <div class="task-dates">
        <span>Start: ${formatDate(task.startDate)}</span>
        <span>End: ${formatDate(task.endDate)}</span>
      </div>
      <div class="task-description">${task.description || 'No description'}</div>
    `;
    tasksContainer.appendChild(taskElement);
  }
  
  function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
  }
});