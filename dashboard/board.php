<?php
require_once '../authentication/db_connection.php';
requireLogin();

$user = getCurrentUser();
$database = new Database();
$db = $database->getConnection();

// Handle AJAX requests first
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    $response = ['success' => false, 'message' => ''];
    
    // Handle status update
    if (isset($_POST['update_status'])) {
        $projectId = (int)$_POST['project_id'];
        $newStatus = $_POST['new_status'];
        
        // Verify project belongs to current user
        $query = "SELECT id FROM projects WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$projectId, $user['id']]);
        
        if ($stmt->fetch()) {
            $query = "UPDATE projects SET status = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$newStatus, $projectId])) {
                $response['success'] = true;
                $response['message'] = 'Status updated successfully';
                $response['new_status'] = $newStatus;
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
                
                $message = 'Project status updated successfully!';
                $messageType = 'success';
            } else {
                $response['message'] = 'Failed to update status';
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
                
                $message = 'Failed to update project status.';
                $messageType = 'error';
            }
        } else {
            $response['message'] = 'Project not found';
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            
            $message = 'Project not found.';
            $messageType = 'error';
        }
    }
    
    // Handle project deletion
    if (isset($_POST['delete_project'])) {
        $projectId = (int)$_POST['project_id'];
        
        // Verify project belongs to current user
        $query = "SELECT id FROM projects WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$projectId, $user['id']]);
        
        if ($stmt->fetch()) {
            $query = "DELETE FROM projects WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$projectId])) {
                $response['success'] = true;
                $response['message'] = 'Project deleted successfully';
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
                
                $message = 'Project deleted successfully!';
                $messageType = 'success';
            } else {
                $response['message'] = 'Failed to delete project';
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
                
                $message = 'Failed to delete project.';
                $messageType = 'error';
            }
        } else {
            $response['message'] = 'Project not found';
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            
            $message = 'Project not found.';
            $messageType = 'error';
        }
    }
    
    // Handle project creation
    if (isset($_POST['create_project'])) {
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'pending';
        $priority = $_POST['priority'] ?? 'medium';
        $deadline = $_POST['deadline'] ?? null;
        
        if (empty($title) || empty($description)) {
            $response['message'] = 'Please fill in all required fields';
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            
            $message = 'Please fill in all required fields';
            $messageType = 'error';
        } else {
            $query = "INSERT INTO projects (user_id, title, description, status, priority, deadline) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$user['id'], $title, $description, $status, $priority, $deadline ?: null])) {
                $response['success'] = true;
                $response['message'] = 'Project created successfully';
                $response['redirect'] = '?page=projects';
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
                
                $message = 'Project created successfully!';
                $messageType = 'success';
                $_POST = []; // Clear form data
            } else {
                $response['message'] = 'Failed to create project';
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
                
                $message = 'Failed to create project. Please try again.';
                $messageType = 'error';
            }
        }
    }
    
    // Handle settings update
    if (isset($_POST['update_settings'])) {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $theme = $_POST['theme'] ?? 'light';
        $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
        
        if (empty($name) || empty($email)) {
            $response['message'] = 'Name and email are required';
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            
            $message = 'Name and email are required';
            $messageType = 'error';
        } elseif (!validateEmail($email)) {
            $response['message'] = 'Please enter a valid email address';
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            
            $message = 'Please enter a valid email address';
            $messageType = 'error';
        } else {
            // Check if email is already taken by another user
            $query = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$email, $user['id']]);
            
            if ($stmt->fetch()) {
                $response['message'] = 'Email address is already taken';
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
                
                $message = 'Email address is already taken';
                $messageType = 'error';
            } else {
                // If password change is requested
                if (!empty($currentPassword) || !empty($newPassword)) {
                    if (empty($currentPassword) || empty($newPassword)) {
                        $response['message'] = 'Both current and new password are required to change password';
                        
                        if ($isAjax) {
                            header('Content-Type: application/json');
                            echo json_encode($response);
                            exit;
                        }
                        
                        $message = 'Both current and new password are required to change password';
                        $messageType = 'error';
                    } elseif (strlen($newPassword) < 6) {
                        $response['message'] = 'New password must be at least 6 characters long';
                        
                        if ($isAjax) {
                            header('Content-Type: application/json');
                            echo json_encode($response);
                            exit;
                        }
                        
                        $message = 'New password must be at least 6 characters long';
                        $messageType = 'error';
                    } else {
                        // Verify current password
                        $query = "SELECT password FROM users WHERE id = ?";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$user['id']]);
                        $userData = $stmt->fetch();
                        
                        if (!verifyPassword($currentPassword, $userData['password'])) {
                            $response['message'] = 'Current password is incorrect';
                            
                            if ($isAjax) {
                                header('Content-Type: application/json');
                                echo json_encode($response);
                                exit;
                            }
                            
                            $message = 'Current password is incorrect';
                            $messageType = 'error';
                        } else {
                            // Update with new password
                            $hashedPassword = hashPassword($newPassword);
                            $query = "UPDATE users SET name = ?, email = ?, password = ?, theme = ?, email_notifications = ? WHERE id = ?";
                            $stmt = $db->prepare($query);
                            
                            if ($stmt->execute([$name, $email, $hashedPassword, $theme, $emailNotifications, $user['id']])) {
                                $response['success'] = true;
                                $response['message'] = 'Settings updated successfully!';
                                
                                if ($isAjax) {
                                    header('Content-Type: application/json');
                                    echo json_encode($response);
                                    exit;
                                }
                                
                                $message = 'Settings updated successfully!';
                                $messageType = 'success';
                                // Update session
                                $_SESSION['user_name'] = $name;
                                $_SESSION['user_email'] = $email;
                                // Refresh user data
                                $user = getCurrentUser();
                            } else {
                                $response['message'] = 'Failed to update settings';
                                
                                if ($isAjax) {
                                    header('Content-Type: application/json');
                                    echo json_encode($response);
                                    exit;
                                }
                                
                                $message = 'Failed to update settings';
                                $messageType = 'error';
                            }
                        }
                    }
                } else {
                    // Update without password change
                    $query = "UPDATE users SET name = ?, email = ?, theme = ?, email_notifications = ? WHERE id = ?";
                    $stmt = $db->prepare($query);
                    
                    if ($stmt->execute([$name, $email, $theme, $emailNotifications, $user['id']])) {
                        $response['success'] = true;
                        $response['message'] = 'Settings updated successfully!';
                        
                        if ($isAjax) {
                            header('Content-Type: application/json');
                            echo json_encode($response);
                            exit;
                        }
                        
                        $message = 'Settings updated successfully!';
                        $messageType = 'success';
                        // Update session
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email;
                        // Refresh user data
                        $user = getCurrentUser();
                    } else {
                        $response['message'] = 'Failed to update settings';
                        
                        if ($isAjax) {
                            header('Content-Type: application/json');
                            echo json_encode($response);
                            exit;
                        }
                        
                        $message = 'Failed to update settings';
                        $messageType = 'error';
                    }
                }
            }
        }
    }
}

// Get dashboard statistics
$stats = [];
$query = "SELECT 
    COUNT(*) as total_projects,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_projects,
    SUM(CASE WHEN status = 'ongoing' THEN 1 ELSE 0 END) as ongoing_projects,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_projects
    FROM projects WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$stats = $stmt->fetch();

// Get recent projects
$query = "SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$recentProjects = $stmt->fetchAll();

// Handle page navigation
$page = $_GET['page'] ?? 'home';
$validPages = ['home', 'add_project', 'projects', 'settings'];
if (!in_array($page, $validPages)) {
    $page = 'home';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PROJECT</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #34495e;
            margin-bottom: 20px;
        }
        
        .sidebar-header h2 {
            margin: 0;
            font-size: 24px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin: 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 15px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #34495e;
        }
        
        .sidebar-menu a span {
            margin-right: 10px;
            width: 20px;
            display: inline-block;
        }
        
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .content-area {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            min-height: 600px;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        /* Message styles */
        .message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .message-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
                z-index: 1000;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
                background: #2c3e50;
                color: white;
                border: none;
                padding: 10px;
                border-radius: 4px;
                cursor: pointer;
            }
        }
        
        .mobile-menu-btn {
            display: none;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>PROJECT</h2>
                <p>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="?page=home" class="<?php echo $page === 'home' ? 'active' : ''; ?>">
                    <span>🏠</span> Dashboard
                </a></li>
                <li><a href="?page=add_project" class="<?php echo $page === 'add_project' ? 'active' : ''; ?>">
                    <span>➕</span> Add Project
                </a></li>
                <li><a href="?page=projects" class="<?php echo $page === 'projects' ? 'active' : ''; ?>">
                    <span>📋</span> All Projects
                </a></li>
                <li><a href="?page=settings" class="<?php echo $page === 'settings' ? 'active' : ''; ?>">
                    <span>⚙️</span> Settings
                </a></li>
                <li><a href="logout.php">
                    <span>🚪</span> Logout
                </a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div>
                    <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
                    <h1 id="pageTitle">
                        <?php 
                        switch($page) {
                            case 'home': echo 'Dashboard'; break;
                            case 'add_project': echo 'Add New Project'; break;
                            case 'projects': echo 'All Projects'; break;
                            case 'settings': echo 'Settings'; break;
                            default: echo 'Dashboard';
                        }
                        ?>
                    </h1>
                </div>
                <div class="user-info">
                    <span>Hello, <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Display messages -->
            <?php if (isset($message)): ?>
                <div class="message message-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="content-area">
                <?php
                // Include the appropriate page content
                switch($page) {
                    case 'home':
                        include 'partials/home.php';
                        break;
                    case 'add_project':
                        include 'partials/add_project.php';
                        break;
                    case 'projects':
                        include 'partials/projects.php';
                        break;
                    case 'settings':
                        include 'partials/settings.php';
                        break;
                    default:
                        include 'partials/home.php';
                }
                ?>
            </div>
        </main>
    </div>

    <!-- Include the JavaScript file -->
    <script src="../js/app.js"></script>
    <script>
        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !menuBtn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>
