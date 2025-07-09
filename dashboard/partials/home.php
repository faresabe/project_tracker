<div style="padding: 20px;">
    <!-- Dashboard Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_projects'] ?? 0; ?></div>
            <div class="stat-label">Total Projects</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['completed_projects'] ?? 0; ?></div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['ongoing_projects'] ?? 0; ?></div>
            <div class="stat-label">Ongoing</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['pending_projects'] ?? 0; ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="margin-bottom: 30px;">
        <h3>Quick Actions</h3>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="?page=add_project" class="btn btn-primary">
                ➕ Create New Project
            </a>
            <a href="?page=projects" class="btn">
                📋 View All Projects
            </a>
            <a href="?page=settings" class="btn">
                ⚙️ Settings
            </a>
        </div>
    </div>

    <!-- Recent Projects -->
    <div>
        <h3>Recent Projects</h3>
        <?php if (!empty($recentProjects)): ?>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <?php foreach ($recentProjects as $project): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
                        <div>
                            <h4 style="margin: 0 0 5px 0; color: #2c3e50;">
                                <a href="project_detail.php?id=<?php echo $project['id']; ?>" style="text-decoration: none; color: inherit;">
                                    <?php echo htmlspecialchars($project['title']); ?>
                                </a>
                            </h4>
                            <p style="margin: 0 0 5px 0; color: #666; font-size: 14px;">
                                <?php echo htmlspecialchars(substr($project['description'], 0, 100)) . (strlen($project['description']) > 100 ? '...' : ''); ?>
                            </p>
                            <small style="color: #888; font-size: 12px;">
                                Created: <?php echo date('M j, Y', strtotime($project['created_at'])); ?>
                            </small>
                        </div>
                        <div>
                            <span class="status-badge status-<?php echo $project['status']; ?>">
                                <?php echo ucfirst($project['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <p>No projects yet. <a href="?page=add_project">Create your first project</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .btn {
        padding: 12px 20px;
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
</style>