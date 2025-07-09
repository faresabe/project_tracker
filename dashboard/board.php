<?php
require_once '../authentication/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ../authentication/sign_in.php');
    exit;
}

// Get current user info
$user_id = get_current_user_id();
$stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user data was found
if ($user) {
    $user_name = $user['first_name'] . ' ' . $user['last_name'];
} else {
    // Fallback if user not found
    $user_name = 'Unknown User';
    // Optionally redirect to login
    // header('Location: ../authentication/sign_in.php');
    // exit;
}

// Get current page
$page = $_GET['page'] ?? 'home';
$valid_pages = ['home', 'add_project', 'settings', 'project_details'];

if (!in_array($page, $valid_pages)) {
    $page = 'home';
}

// Handle project details
$project_id = $_GET['project_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Pulse</title>
  <link rel="stylesheet" href="board.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="dashboard-container">
    <!-- Mobile menu button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
    
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header"></div>
      <nav>
        <ul>
          <li class="<?= $page === 'home' ? 'active' : '' ?>">
            <a href="?page=home">
              <span class="icon">🏠</span> <span class="menu-text">Home</span>
            </a>
          </li>
          <li class="<?= $page === 'add_project' ? 'active' : '' ?>">
            <a href="?page=add_project">
              <span class="icon">➕</span> <span class="menu-text">Add Project</span>
            </a>
          </li>
          <li class="<?= $page === 'settings' ? 'active' : '' ?>">
            <a href="?page=settings">
              <span class="icon">⚙️</span> <span class="menu-text">Settings</span>
            </a>
          </li>
        </ul>
      </nav>
      
      <div style="padding: 0 24px;">
        <a href="php_api/logout.php" class="logout-btn">
          <span>🚪</span> Logout
        </a>
      </div>
    </aside>
   
    <main class="main-content">
      <header class="dashboard-header">
        <form method="GET" style="display: flex; align-items: center; gap: 20px;">
          <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
          <input type="text" name="search" class="search-bar" placeholder="Search projects..." 
                 value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
          <button type="submit" style="display: none;"></button>
        </form>
        <div class="user-info">
          <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User" class="user-avatar">
          <div>
            <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
            <div class="user-role">Project Manager</div>
          </div>
        </div>
      </header>
      
      <div id="page-content">
        <?php
        switch ($page) {
            case 'home':
                include 'partials/home.php';
                break;
            case 'add_project':
                include 'partials/add_project.php';
                break;
            case 'settings':
                include 'partials/settings.php';
                break;
            case 'project_details':
                include 'partials/project_details.php';
                break;
            default:
                include 'partials/home.php';
        }
        ?>
      </div>
    </main>
  </div>

  <!-- Task Form Popup -->
  <div id="task-form-popup" style="display: none;">
    <div class="task-form-container">
      <div class="task-form-header">
        <h3 id="task-form-title">Add New Task</h3>
        <button class="close-task-form" onclick="closeTaskForm()">&times;</button>
      </div>
      <form id="add-task-form" method="POST" action="php_api/save_task.php">
        <input type="hidden" id="task-id" name="task_id" value="">
        <input type="hidden" id="project-id-hidden" name="project_id" value="">
        
        <div class="form-group">
          <label for="task-name">Name</label>
          <input type="text" id="task-name" name="task_name" placeholder="Enter task name" required />
        </div>
        
        <div class="form-group">
          <label for="task-type">Type</label>
          <select id="task-type" name="task_type" required>
            <option value="">Select type</option>
            <option value="feature">Feature</option>
            <option value="bug">Bug</option>
            <option value="improvement">Improvement</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="task-desc">Description</label>
          <textarea id="task-desc" name="task_description" rows="3" placeholder="Enter task description"></textarea>
        </div>
        
        <div class="form-group">
          <label for="task-status">Task Status</label>
          <select id="task-status" name="task_status" required>
            <option value="pending">Pending</option>
            <option value="ongoing">Ongoing</option>
            <option value="done">Done</option>
          </select>
        </div>
        
        <div class="form-group">
          <label>Date</label>
          <div class="date-box">
            <div>
              <label for="task-start-date">Start Date</label>
              <input type="date" id="task-start-date" name="task_start_date" required />
            </div>
            <div>
              <label for="task-end-date">End Date</label>
              <input type="date" id="task-end-date" name="task_end_date" required />
            </div>
          </div>
        </div>
       
        <button type="submit" class="create-task-btn">Create Task</button>
      </form>
    </div>
  </div>

  <script>
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('active');
    }

    function openTaskForm(projectId = null, taskData = null) {
      const popup = document.getElementById('task-form-popup');
      const form = document.getElementById('add-task-form');
      const title = document.getElementById('task-form-title');
      
      if (taskData) {
        title.textContent = 'Edit Task';
        document.getElementById('task-id').value = taskData.id;
        document.getElementById('task-name').value = taskData.name;
        document.getElementById('task-type').value = taskData.type;
        document.getElementById('task-desc').value = taskData.description;
        document.getElementById('task-status').value = taskData.status;
        document.getElementById('task-start-date').value = taskData.start_date;
        document.getElementById('task-end-date').value = taskData.end_date;
      } else {
        title.textContent = 'Add New Task';
        form.reset();
        document.getElementById('task-id').value = '';
      }
      
      if (projectId) {
        document.getElementById('project-id-hidden').value = projectId;
      }
      
      popup.style.display = 'flex';
    }

    function closeTaskForm() {
      document.getElementById('task-form-popup').style.display = 'none';
      document.getElementById('add-task-form').reset();
    }

    function deleteProject(projectId, projectTitle) {
      if (confirm(`Are you sure you want to delete "${projectTitle}"?`)) {
        window.location.href = `php_api/delete_project.php?id=${projectId}&redirect=1`;
      }
    }

    function deleteTask(taskId, projectId) {
      if (confirm('Are you sure you want to delete this task?')) {
        window.location.href = `php_api/delete_task.php?id=${taskId}&project_id=${projectId}`;
      }
    }

    // Close popup when clicking outside
    document.getElementById('task-form-popup').addEventListener('click', function(e) {
      if (e.target === this) {
        closeTaskForm();
      }
    });
  </script>
</body>
</html>