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
    
    <!-- Search and Filter Section -->
    <div class="filters-container">
        <div class="filters-row">
            <div class="filter-group">
                <label>Search Projects:</label>
                <input type="text" data-search="projects" class="search-input" placeholder="Search by title or description...">
            </div>
            <div class="filter-group">
                <label>Filter by Status:</label>
                <select data-filter="status" class="filter-select">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Filter by Priority:</label>
                <select data-filter="priority" class="filter-select">
                    <option value="all">All Priorities</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
        </div>
        <div class="search-results-info"></div>
    </div>
    
    <?php if (!empty($allProjects)): ?>
        <div class="projects-grid">
            <?php foreach ($allProjects as $project): ?>
                <div class="project-card" 
                     data-project-id="<?php echo $project['id']; ?>" 
                     data-status="<?php echo $project['status']; ?>" 
                     data-priority="<?php echo $project['priority']; ?>">
                    
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
                        
                        <!-- Status Update - AJAX enabled -->
                        <select class="status-select btn btn-sm" data-project-id="<?php echo $project['id']; ?>">
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
                        
                        <!-- Delete Button - AJAX enabled -->
                        <button class="btn btn-sm btn-danger delete-btn" data-project-id="<?php echo $project['id']; ?>">
                            Delete
                        </button>
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

    /* Loading state */
    .loading {
        opacity: 0.6;
        pointer-events: none;
        position: relative;
    }

    .loading::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Filters */
    .filters-container {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .filters-row {
        display: flex;
        gap: 20px;
        align-items: end;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filter-group label {
        font-size: 12px;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
    }

    .filter-select, .search-input {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        min-width: 150px;
        transition: border-color 0.3s ease;
    }

    .filter-select:focus, .search-input:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
    }

    .search-results-info {
        margin-top: 10px;
        font-size: 12px;
        color: #666;
    }

    /* Notifications */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 4px;
        color: white;
        font-weight: 600;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification-success {
        background: #28a745;
    }

    .notification-error {
        background: #dc3545;
    }

    .notification-close {
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        margin-left: 10px;
    }

    /* Search highlighting */
    mark {
        background: #fff3cd;
        padding: 2px 4px;
        border-radius: 2px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .projects-grid {
            grid-template-columns: 1fr;
        }
        
        .filters-row {
            flex-direction: column;
            gap: 15px;
        }
        
        .filter-group {
            width: 100%;
        }
        
        .filter-select, .search-input {
            width: 100%;
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
