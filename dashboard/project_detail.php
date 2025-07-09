<?php
require_once '../authentication/db_connection.php';
requireLogin();

$user = getCurrentUser();
$database = new Database();
$db = $database->getConnection();

$projectId = (int)($_GET['id'] ?? 0);

// Get project details
$query = "SELECT * FROM projects WHERE id = ? AND user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$projectId, $user['id']]);
$project = $stmt->fetch();

if (!$project) {
    header("Location: board.php?page=projects");
    exit();
}

// Get project tasks
$query = "SELECT * FROM tasks WHERE project_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$projectId]);
$tasks = $stmt->fetchAll();

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $taskTitle = sanitizeInput($_POST['task_title'] ?? '');
    $taskDescription = sanitizeInput($_POST['task_description'] ?? '');
    $taskStatus = $_POST['task_status'] ?? 'pending';
    $taskPriority = $_POST['task_priority'] ?? 'medium';
    $dueDate = $_POST['due_date'] ?? null;
    
    if (!empty($taskTitle)) {
        $query = "INSERT INTO tasks (project_id, title, description, status, priority, due_date) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$projectId, $taskTitle, $taskDescription, $taskStatus, $taskPriority, $dueDate ?: null])) {
            header("Location: project_detail.php?id=$projectId&task_added=1");
            exit();
        }
    }
}

// Handle task status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task_status'])) {
    $taskId = (int)$_POST['task_id'];
    $newStatus = $_POST['new_status'];
    
    $query = "UPDATE tasks SET status = ? WHERE id = ? AND project_id = ?";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$newStatus, $taskId, $projectId])) {
        header("Location: project_detail.php?id=$projectId&task_updated=1");
        exit();
    }
}

// Handle task deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task'])) {
    $taskId = (int)$_POST['task_id'];
    
    $query = "DELETE FROM tasks WHERE id = ? AND project_id = ?";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$taskId, $projectId])) {
        header("Location: project_detail.php?id=$projectId&task_deleted=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['title']); ?> - PROJECT</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .project-title {
            margin: 0;
            color: #2c3e50;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-ongoing { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        
        .project-meta {
            color: #666;
            font-size: 14px;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .main-content, .sidebar {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .section-title {
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
        }
        
        .task-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .task-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
        }
        
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .task-title {
            margin: 0;
            color: #2c3e50;
            font-size: 16px;
        }
        
        .task-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 11px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .project-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .task-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .task-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="margin-bottom: 15px;">
                <a href="board.php?page=projects" class="btn btn-secondary">← Back to Projects</a>
            </div>
            
            <div class="project-header">
                <h1 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h1>
                <span class="status-badge status-<?php echo $project['status']; ?>">
                    <?php echo ucfirst($project['status']); ?>
                </span>
            </div>
            
            <div class="project-meta">
                <p><strong>Description:</strong> <?php echo htmlspecialchars($project['description']); ?></p>
                <p>
                    <strong>Priority:</strong> <?php echo ucfirst($project['priority']); ?> | 
                    <strong>Created:</strong> <?php echo date('M j, Y', strtotime($project['created_at'])); ?>
                    <?php if ($project['deadline']): ?>
                        | <strong>Deadline:</strong> <?php echo date('M j, Y', strtotime($project['deadline'])); ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <?php if (isset($_GET['task_added'])): ?>
            <div class="alert alert-success">Task added successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['task_updated'])): ?>
            <div class="alert alert-success">Task updated successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['task_deleted'])): ?>
            <div class="alert alert-success">Task deleted successfully!</div>
        <?php endif; ?>
        
        <div class="content-grid">
            <div class="main-content">
                <h2 class="section-title">Tasks (<?php echo count($tasks); ?>)</h2>
                
                <?php if (!empty($tasks)): ?>
                    <div class="task-list">
                        <?php foreach ($tasks as $task): ?>
                            <div class="task-item">
                                <div class="task-header">
                                    <h3 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                                    <div class="task-actions">
                                        <span class="status-badge status-<?php echo $task['status']; ?>">
                                            <?php echo ucfirst($task['status']); ?>
                                        </span>
                                        
                                        <!-- Status Update Form -->
                                        <form method="POST" style="display: inline-block;">
                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                            <select name="new_status" onchange="this.form.submit()" class="btn btn-sm">
                                                <option value="">Change Status</option>
                                                <option value="pending" <?php echo $task['status'] === 'pending' ? 'disabled' : ''; ?>>Pending</option>
                                                <option value="ongoing" <?php echo $task['status'] === 'ongoing' ? 'disabled' : ''; ?>>Ongoing</option>
                                                <option value="completed" <?php echo $task['status'] === 'completed' ? 'disabled' : ''; ?>>Completed</option>
                                            </select>
                                            <input type="hidden" name="update_task_status" value="1">
                                        </form>
                                        
                                        <!-- Delete Form -->
                                        <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                            <button type="submit" name="delete_task" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                
                                <?php if ($task['description']): ?>
                                    <p style="color: #666; margin-bottom: 10px;"><?php echo htmlspecialchars($task['description']); ?></p>
                                <?php endif; ?>
                                
                                <div style="font-size: 12px; color: #888;">
                                    <strong>Priority:</strong> <?php echo ucfirst($task['priority']); ?> | 
                                    <strong>Created:</strong> <?php echo date('M j, Y', strtotime($task['created_at'])); ?>
                                    <?php if ($task['due_date']): ?>
                                        | <strong>Due:</strong> <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <p>No tasks yet. Add your first task using the form on the right.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="sidebar">
                <h3 class="section-title">Add New Task</h3>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="taskTitle">Task Title *</label>
                        <input type="text" id="taskTitle" name="task_title" class="form-control" required 
                               placeholder="Enter task title">
                    </div>
                    
                    <div class="form-group">
                        <label for="taskDescription">Description</label>
                        <textarea id="taskDescription" name="task_description" class="form-control" 
                                  rows="3" placeholder="Task description..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="taskStatus">Status</label>
                        <select id="taskStatus" name="task_status" class="form-control">
                            <option value="pending">Pending</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="taskPriority">Priority</label>
                        <select id="taskPriority" name="task_priority" class="form-control">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="dueDate">Due Date</label>
                        <input type="date" id="dueDate" name="due_date" class="form-control">
                    </div>
                    
                    <button type="submit" name="add_task" class="btn btn-primary" style="width: 100%;">
                        Add Task
                    </button>
                </form>
                
                <hr style="margin: 30px 0;">
                
                <h3 class="section-title">Project Stats</h3>
                <div style="font-size: 14px; color: #666;">
                    <?php
                    $totalTasks = count($tasks);
                    $completedTasks = count(array_filter($tasks, function($task) { return $task['status'] === 'completed'; }));
                    $ongoingTasks = count(array_filter($tasks, function($task) { return $task['status'] === 'ongoing'; }));
                    $pendingTasks = count(array_filter($tasks, function($task) { return $task['status'] === 'pending'; }));
                    $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                    ?>
                    <p><strong>Total Tasks:</strong> <?php echo $totalTasks; ?></p>
                    <p><strong>Completed:</strong> <?php echo $completedTasks; ?></p>
                    <p><strong>Ongoing:</strong> <?php echo $ongoingTasks; ?></p>
                    <p><strong>Pending:</strong> <?php echo $pendingTasks; ?></p>
                    <p><strong>Progress:</strong> <?php echo $progress; ?>%</p>
                    
                    <div style="background: #e9ecef; height: 10px; border-radius: 5px; margin-top: 10px; overflow: hidden;">
                        <div style="background: #28a745; height: 100%; width: <?php echo $progress; ?>%; transition: width 0.3s;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>