<?php
require_once '../authentication/db_connection.php';
requireLogin();

$user = getCurrentUser();
$database = new Database();
$db = $database->getConnection();

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
        
        /* Projects Grid */
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .project-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #e9ecef;
        }
        
        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .project-header h3 {
            margin: 0;
            color: #2c3e50;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-ongoing { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        
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
        
        // Project management functions
        function deleteProject(projectId) {
            if (confirm('Are you sure you want to delete this project?')) {
                fetch('php_api/delete_project.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ project_id: projectId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting project: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting project');
                });
            }
        }
        
        function updateProjectStatus(projectId, newStatus) {
            fetch('php_api/update_project.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    project_id: projectId, 
                    status: newStatus 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating project: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating project');
            });
        }
    </script>
</body>
</html>