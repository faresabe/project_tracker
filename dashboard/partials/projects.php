<?php
// Get all projects for the current user
$query = "SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$allProjects = $stmt->fetchAll();

// Handle project deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project'])) {
    $projectId = (int)$_POST['project_id'];
    
    // Verify project belongs to current user
    $query = "SELECT id FROM projects WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$projectId, $user['id']]);
    
    if ($stmt->fetch()) {
        $query = "DELETE FROM projects WHERE id = ?";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$projectId])) {
            header("Location: ?page=projects&deleted=1");
            exit();
        }
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
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
            header("Location: ?page=projects&updated=1");
            exit();
        }
    }
}
?>

<div style="padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>All Projects</h2>
        <a href="?page=add_project" class="btn btn-primary">Add New Project</a>
    </div>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            Project deleted successfully!
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['updated'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            Project updated successfully!
        </div>
    <?php endif; ?>
    
    <?php if (!empty($allProjects)): ?>
        <div class="projects-grid">
            <?php foreach ($allProjects as $project): ?>
                <div class="project-card">
                    <div class="project-header">
                        <h3>
                            <a href="project_detail.php?id=<?php echo $project['id']; ?>" style="text-decoration: none; color: #2c3e50;">
                                <?php echo htmlspecialchars($project['title']); ?>
                            </a>
                        </h3>
                        <span class="status-badge status-<?php echo $project['status']; ?>">
                            <?php echo ucfirst($project['status']); ?>
                        </span>
                    </div>
                    <p class="project-description">
                        <?php echo htmlspecialchars($project['description']); ?>
                    </p>
                    <div class="project-meta">
                        <small>
                            <strong>Priority:</strong> <?php echo ucfirst($project['priority']); ?><br>
                            <strong>Created:</strong> <?php echo date('M j, Y', strtotime($project['created_at'])); ?>
                            <?php if ($project['deadline']): ?>
                                <br><strong>Deadline:</strong> <?php echo date('M j, Y', strtotime($project['deadline'])); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div class="project-actions">
                        <a href="project_detail.php?id=<?php echo $project['id']; ?>" class="btn btn-sm">View</a>
                        
                        <!-- Status Update Form -->
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                            <select name="new_status" onchange="this.form.submit()" class="btn btn-sm" style="padding: 5px;">
                                <option value="">Change Status</option>
                                <option value="pending" <?php echo $project['status'] === 'pending' ? 'disabled' : ''; ?>>Pending</option>
                                <option value="ongoing" <?php echo $project['status'] === 'ongoing' ? 'disabled' : ''; ?>>Ongoing</option>
                                <option value="completed" <?php echo $project['status'] === 'completed' ? 'disabled' : ''; ?>>Completed</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                        
                        <!-- Delete Form -->
                        <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this project?');">
                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                            <button type="submit" name="delete_project" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <p>No projects found. <a href="?page=add_project">Create your first project</a></p>
        </div>
    <?php endif; ?>
</div>

<style>
    .project-description {
        color: #666;
        margin-bottom: 15px;
        line-height: 1.5;
    }
    
    .project-meta {
        margin-bottom: 15px;
        color: #888;
    }
    
    .project-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }
    
    .btn-danger {
        background: #dc3545;
        color: white;
    }
    
    .btn-danger:hover {
        background: #c82333;
    }
</style>