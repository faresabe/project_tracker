<?php
// Get user's projects with search functionality
$search = $_GET['search'] ?? '';
$filter_status = $_GET['filter'] ?? '';

$query = "SELECT * FROM projects WHERE user_id = ?";
$params = [$user_id];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($filter_status)) {
    $query .= " AND status = ?";
    $params[] = $filter_status;
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_projects = count($projects);
$completed = array_filter($projects, fn($p) => $p['status'] === 'completed');
$pending = array_filter($projects, fn($p) => $p['status'] === 'pending');
$ongoing = array_filter($projects, fn($p) => $p['status'] === 'ongoing');

$completed_percent = $total_projects > 0 ? round((count($completed) / $total_projects) * 100) : 0;
$pending_percent = $total_projects > 0 ? round((count($pending) / $total_projects) * 100) : 0;
$ongoing_percent = $total_projects > 0 ? round((count($ongoing) / $total_projects) * 100) : 0;
?>

<section class="status-section">
    <h2 class="status-title">PROJECT STATUS</h2>
    <p class="status-label">Click on status cards to filter projects</p>
    
    <div class="status-cards">
      <a href="?page=home&filter=completed" class="status-card completed" style="text-decoration: none; color: inherit;">
        <div class="status-percent"><?= $completed_percent ?>%</div>
        <div class="status-text">Completed</div>
      </a>
      
      <a href="?page=home&filter=pending" class="status-card pending" style="text-decoration: none; color: inherit;">
        <div class="status-percent"><?= $pending_percent ?>%</div>
        <div class="status-text">Pending</div>
      </a>
      
      <a href="?page=home&filter=ongoing" class="status-card ongoing" style="text-decoration: none; color: inherit;">
        <div class="status-percent"><?= $ongoing_percent ?>%</div>
        <div class="status-text">Ongoing</div>
      </a>
    </div>
</section>

<section class="projects-section">
    <div class="projects-header">
      <h3>
        <?php if (!empty($search)): ?>
          Search Results for "<?= htmlspecialchars($search) ?>"
        <?php elseif (!empty($filter_status)): ?>
          <?= ucfirst($filter_status) ?> Projects
        <?php else: ?>
          Recent Projects
        <?php endif; ?>
      </h3>
      <div>
        <?php if (!empty($search) || !empty($filter_status)): ?>
          <a href="?page=home" class="view-all">Show All</a>
        <?php endif; ?>
      </div>
    </div>
    
    <div class="projects-list">
      <?php if (empty($projects)): ?>
        <div style="text-align: center; padding: 40px; color: #666;">
          <?php if (!empty($search)): ?>
            <p>No projects found matching "<?= htmlspecialchars($search) ?>"</p>
            <a href="?page=home">View all projects</a>
          <?php else: ?>
            <p>No projects found.</p>
            <a href="?page=add_project" class="create-project">Create your first project</a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="project-row">
          <?php foreach ($projects as $project): ?>
            <div class="project-card" data-status="<?= $project['status'] ?>">
              <div class="project-header">
                <h4><?= htmlspecialchars($project['title']) ?></h4>
                <span class="project-status <?= $project['status'] ?>">
                  <?= ucfirst($project['status']) ?>
                </span>
              </div>
              
              <p class="project-description">
                <?= htmlspecialchars($project['description'] ?: 'No description') ?>
              </p>
              
              <p class="project-dates">
                From: <?= $project['start_date'] ?: 'Not set' ?> 
                To: <?= $project['end_date'] ?: 'Not set' ?>
              </p>
              
              <div class="project-actions">
                <a href="?page=project_details&project_id=<?= $project['id'] ?>" class="see-more-btn">
                  View Tasks
                </a>
                <a href="?page=add_project&edit=<?= $project['id'] ?>" class="edit-project-btn">
                  Edit
                </a>
                <button onclick="deleteProject(<?= $project['id'] ?>, '<?= htmlspecialchars($project['title']) ?>')" 
                        class="delete-project-btn">
                  Delete
                </button>
                
                <form method="POST" action="php_api/update_project_status.php" style="display: inline;">
                  <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                  <select name="status" class="status-select" onchange="this.form.submit()">
                    <option value="pending" <?= $project['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="ongoing" <?= $project['status'] === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                    <option value="completed" <?= $project['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                  </select>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
</section>