<?php
$project = null;
$tasks = [];

// Check if editing existing project
if (isset($_GET['edit'])) {
    $project_id = intval($_GET['edit']);
    
    // Get project details
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($project) {
        // Get project tasks
        $taskStmt = $pdo->prepare("SELECT * FROM tasks WHERE project_id = ? ORDER BY created_at DESC");
        $taskStmt->execute([$project_id]);
        $tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['project_title'] ?? '';
    $type = $_POST['project_type'] ?? 'web';
    $description = $_POST['project_description'] ?? '';
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    
    if (empty($title)) {
        $error = 'Project title is required';
    } else {
        try {
            if ($project) {
                // Update existing project
                $stmt = $pdo->prepare("UPDATE projects SET title = ?, type = ?, description = ?, start_date = ?, end_date = ? WHERE id = ? AND user_id = ?");
                $success = $stmt->execute([$title, $type, $description, $start_date, $end_date, $project['id'], $user_id]);
                $message = 'Project updated successfully!';
            } else {
                // Create new project
                $stmt = $pdo->prepare("INSERT INTO projects (title, type, description, start_date, end_date, status, user_id) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
                $success = $stmt->execute([$title, $type, $description, $start_date, $end_date, $user_id]);
                $message = 'Project created successfully!';
            }
            
            if ($success) {
                header('Location: ?page=home&success=' . urlencode($message));
                exit;
            } else {
                $error = 'Failed to save project';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<section class="form-section">
  <h2><?= $project ? 'Edit Project' : 'Add New Project' ?></h2>
  
  <?php if (isset($error)): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>
  
  <form method="POST">
    <div class="form-group">
      <label for="project-title">Project Title</label>
      <input type="text" id="project-title" name="project_title" 
             value="<?= htmlspecialchars($project['title'] ?? '') ?>" required>
    </div>
    
    <div class="form-group">
      <label for="project-type">Project Type</label>
      <select id="project-type" name="project_type">
        <option value="web" <?= ($project['type'] ?? '') === 'web' ? 'selected' : '' ?>>Web Development</option>
        <option value="mobile" <?= ($project['type'] ?? '') === 'mobile' ? 'selected' : '' ?>>Mobile App</option>
        <option value="design" <?= ($project['type'] ?? '') === 'design' ? 'selected' : '' ?>>UI/UX Design</option>
        <option value="other" <?= ($project['type'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
      </select>
    </div>
    
    <div class="form-group">
      <label for="project-description">Description</label>
      <textarea id="project-description" name="project_description" rows="4"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
    </div>
    
    <?php if ($project): ?>
    <div class="form-group">
      <label>Tasks</label>
      <div class="tasks-box">
        <div id="tasks-container">
          <?php foreach ($tasks as $task): ?>
            <div class="task-item">
              <div class="task-content">
                <strong><?= htmlspecialchars($task['name']) ?></strong>
                <span class="task-type <?= $task['type'] ?>"><?= ucfirst($task['type']) ?></span>
                <p><?= htmlspecialchars($task['description']) ?></p>
                <small>From: <?= $task['start_date'] ?> To: <?= $task['end_date'] ?></small>
              </div>
              <div class="task-actions">
                <button type="button" onclick="openTaskForm(<?= $project['id'] ?>, <?= htmlspecialchars(json_encode($task)) ?>)" class="edit-task">Edit</button>
                <button type="button" onclick="deleteTask(<?= $task['id'] ?>, <?= $project['id'] ?>)" class="delete-task">Delete</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <a href="#" onclick="openTaskForm(<?= $project['id'] ?? 'null' ?>)" class="add-task">+ Add Task</a>
      </div>
    </div>
    <?php endif; ?>
    
    <div class="form-group">
      <label>Timeline</label>
      <div class="date-box">
        <div>
          <label for="start-date">Start Date</label>
          <input type="date" id="start-date" name="start_date" 
                 value="<?= $project['start_date'] ?? '' ?>">
          <span class="calendar-icon"></span>
        </div>
        <div>
          <label for="end-date">End Date</label>
          <input type="date" id="end-date" name="end_date" 
                 value="<?= $project['end_date'] ?? '' ?>">
          <span class="calendar-icon"></span>
        </div>
      </div>
    </div>
    
    <button type="submit" class="create-project">
      <?= $project ? 'Update Project' : 'Create Project' ?>
    </button>
  </form>
</section>