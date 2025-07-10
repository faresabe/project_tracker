<?php
// Get all projects for the current user
$query = "SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$allProjects = $stmt->fetchAll();
?>

<div style="padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>All Projects</h2>
        <a href="?page=add_project" class="btn btn-primary">Add New Project</a>
    </div>
    
    <?php if (!empty($allProjects)): ?>
        <div class="projects-grid">
            <?php foreach ($allProjects as $project): ?>
                <div class="project-card">
                    <div class="project-header">
                        <h3 class="project-title">
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
                        <a href="project_detail.php?id=<?php echo $project['id']; ?>" class="btn btn-sm">View Details</a>
                        
                        <!-- Status Update Form -->
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                            <select name="new_status" class="status-select btn btn-sm" onchange="this.form.submit()">
                                <option value="">Change Status</option>
                                <?php if ($project['status'] !== 'pending'): ?>
                                    <option value="pending">Pending</option>
                                <?php endif; ?>
                                <?php if ($project['status'] !== 'ongoing'): ?>
                                    <option value="ongoing">Ongoing</option>
                                <?php endif; ?>
                                <?php if ($project['status'] !== 'completed'): ?>
                                    <option value="completed">Completed</option>
                                <?php endif; ?>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                        
                        <!-- Delete Form -->
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this project?')">
                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                            <button type="submit" name="delete_project" class="btn btn-sm btn-danger">
                                Delete
                            </button>
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
    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .project-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .project-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
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

    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        transition: all 0.3s;
        background: #6c757d;
        color: white;
    }

    .btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-primary:hover {
        background: #0056b3;
    }

    .btn:hover {
        background: #545b62;
        color: white;
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

    .status-select {
        background: #6c757d;
        color: white;
        border: 1px solid #6c757d;
    }

    @media (max-width: 768px) {
        .projects-grid {
            grid-template-columns: 1fr;
        }
        
        .project-actions {
            flex-direction: column;
            gap: 10px;
        }
        
        .btn {
            width: 100%;
        }
    }
</style>
