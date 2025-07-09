<?php
if (!$project_id) {
    header('Location: ?page=home');
    exit;
}

// Get project details
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
$stmt->execute([$project_id, $user_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header('Location: ?page=home&error=Project not found');
    exit;
}

// Get project tasks
$taskStmt = $pdo->prepare("SELECT * FROM tasks WHERE project_id = ? ORDER BY created_at DESC");
$taskStmt->execute([$project_id]);
$tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="project-details">
  <div class="project-details-header">
    <h2><?= htmlspecialchars($project['title']) ?></h2>
    <div class="project-details-actions">
      <a href="?page=add_project&edit=<?= $project['id'] ?>" class="edit-project-btn">Edit Project</a>
      <a href="?page=home" class="back-btn">Back to Projects</a>
    </div>
  </div>
  
  <div class="project-info">
    <p><strong>Type:</strong> <?= ucfirst($project['type']) ?></p>
    <p><strong>Description:</strong> <?= htmlspecialchars($project['description'] ?: 'No description') ?></p>
    <p><strong>Status:</strong> 
      <span class="project-status <?= $project['status'] ?>">
        <?= ucfirst($project['status']) ?>
      </span>
    </p>
    <p><strong>Timeline:</strong> 
      <?= $project['start_date'] ?: 'Not set' ?> to <?= $project['end_date'] ?: 'Not set' ?>
    </p>
    <p><strong>Created:</strong> <?= date('M j, Y', strtotime($project['created_at'])) ?></p>
  </div>
  
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h3>Tasks (<?= count($tasks) ?>)</h3>
    <button onclick="openTaskForm(<?= $project['id'] ?>)" class="create-project" style="margin: 0;">
      Add New Task
    </button>
  </div>
  
  <div class="task-cards-container">
    <?php if (empty($tasks)): ?>
      <div style="text-align: center; padding: 40px; color: #666;">
        <p>No tasks for this project yet.</p>
        <button onclick="openTaskForm(<?= $project['id'] ?>)" class="create-project">
          Add First Task
        </button>
      </div>
    <?php else: ?>
      <?php foreach ($tasks as $task): ?>
        <div class="task-card">
          <div class="task-card-header">
            <div class="task-header-left">
              <span class="task-title"><?= htmlspecialchars($task['name']) ?></span>
              <span class="task-type <?= $task['type'] ?>"><?= ucfirst($task['type']) ?></span>
            </div>
            <div class="task-header-right">
              <span class="task-priority <?= $task['status'] ?>"><?= ucfirst($task['status']) ?></span>
              <button onclick="deleteTask(<?= $task['id'] ?>, <?= $project['id'] ?>)" class="delete-btn">Delete</button>
            </div>
          </div>
          
          <div class="task-desc">
            <?= htmlspecialchars($task['description'] ?: 'No description') ?>
          </div>
          
          <div class="task-footer">
            <span class="due-icon">📅</span>
            <span class="due-date">
              <?php if ($task['start_date'] && $task['end_date']): ?>
                <?= date('M j', strtotime($task['start_date'])) ?> - <?= date('M j, Y', strtotime($task['end_date'])) ?>
              <?php elseif ($task['end_date']): ?>
                Due: <?= date('M j, Y', strtotime($task['end_date'])) ?>
              <?php else: ?>
                No due date set
              <?php endif; ?>
            </span>
          </div>
          
          <div style="margin-top: 10px;">
            <button onclick="openTaskForm(<?= $project['id'] ?>, <?= htmlspecialchars(json_encode($task)) ?>)" 
                    class="edit-task" style="font-size: 0.85rem; padding: 6px 12px;">
              Edit Task
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>