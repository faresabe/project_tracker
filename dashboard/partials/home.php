<div style="padding: 20px;">
    <h2 style="margin-bottom: 30px;">Dashboard Overview</h2>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_projects'] ?? 0; ?></div>
            <div class="stat-label">Total Projects</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="stat-number"><?php echo $stats['pending_projects'] ?? 0; ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="stat-number"><?php echo $stats['ongoing_projects'] ?? 0; ?></div>
            <div class="stat-label">Ongoing</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="stat-number"><?php echo $stats['completed_projects'] ?? 0; ?></div>
            <div class="stat-label">Completed</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
            <a href="?page=add_project" class="action-card">
                <div class="action-icon">➕</div>
                <div class="action-title">Add New Project</div>
                <div class="action-desc">Create a new project to get started</div>
            </a>
            <a href="?page=projects" class="action-card">
                <div class="action-icon">📋</div>
                <div class="action-title">View All Projects</div>
                <div class="action-desc">Manage and track your projects</div>
            </a>
            <a href="?page=settings" class="action-card">
                <div class="action-icon">⚙️</div>
                <div class="action-title">Account Settings</div>
                <div class="action-desc">Update your profile and preferences</div>
            </a>
        </div>
    </div>
    
    <!-- Recent Projects -->
    <?php if (!empty($recentProjects)): ?>
    <div class="recent-projects">
        <h3>Recent Projects</h3>
        <div class="projects-list">
            <?php foreach ($recentProjects as $project): ?>
                <div class="project-item" 
                     data-project-id="<?php echo $project['id']; ?>" 
                     data-status="<?php echo $project['status']; ?>" 
                     data-priority="<?php echo $project['priority']; ?>">
                    <div class="project-info">
                        <h4>
                            <a href="project_detail.php?id=<?php echo $project['id']; ?>" style="text-decoration: none; color: #2c3e50;">
                                <?php echo htmlspecialchars($project['title']); ?>
                            </a>
                        </h4>
                        <p><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . (strlen($project['description']) > 100 ? '...' : ''); ?></p>
                        <small>Created: <?php echo date('M j, Y', strtotime($project['created_at'])); ?></small>
                    </div>
                    <div class="project-status">
                        <span class="status-badge status-<?php echo $project['status']; ?>">
                            <?php echo ucfirst($project['status']); ?>
                        </span>
                        <div class="project-actions-mini">
                            <select class="status-select-mini" data-project-id="<?php echo $project['id']; ?>">
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
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="?page=projects" class="btn btn-primary">View All Projects</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    .quick-actions {
        margin-bottom: 40px;
    }

    .quick-actions h3 {
        margin-bottom: 20px;
        color: #2c3e50;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .action-card {
        background: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-decoration: none;
        color: inherit;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }

    .action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        text-decoration: none;
        color: inherit;
    }

    .action-icon {
        font-size: 2em;
        margin-bottom: 15px;
    }

    .action-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #2c3e50;
    }

    .action-desc {
        color: #666;
        font-size: 14px;
    }

    .recent-projects {
        margin-top: 40px;
    }

    .recent-projects h3 {
        margin-bottom: 20px;
        color: #2c3e50;
    }

    .projects-list {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .project-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        transition: background 0.3s ease;
    }

    .project-item:last-child {
        border-bottom: none;
    }

    .project-item:hover {
        background: #f8f9fa;
    }

    .project-item.loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .project-info {
        flex: 1;
    }

    .project-info h4 {
        margin: 0 0 8px 0;
        font-size: 16px;
    }

    .project-info p {
        margin: 0 0 5px 0;
        color: #666;
        font-size: 14px;
    }

    .project-info small {
        color: #888;
        font-size: 12px;
    }

    .project-status {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .project-actions-mini {
        display: flex;
        gap: 10px;
    }

    .status-select-mini {
        padding: 5px 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 12px;
        background: white;
        cursor: pointer;
    }

    .btn {
        padding: 12px 24px;
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
        text-decoration: none;
        color: white;
    }

    @media (max-width: 768px) {
        .actions-grid {
            grid-template-columns: 1fr;
        }
        
        .project-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .project-status {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
