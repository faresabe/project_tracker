<?php
require_once '../../authentication/config.php';

require_auth();
$user_id = get_current_user_id();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../board.php');
    exit;
}

$task_id = $_POST['task_id'] ?? null;
$project_id = intval($_POST['project_id'] ?? 0);
$task_name = trim($_POST['task_name'] ?? '');
$task_type = $_POST['task_type'] ?? 'feature';
$task_description = trim($_POST['task_description'] ?? '');
$task_status = $_POST['task_status'] ?? 'pending';
$task_start_date = $_POST['task_start_date'] ?? null;
$task_end_date = $_POST['task_end_date'] ?? null;

// Validate
if (empty($task_name) || empty($project_id)) {
    header('Location: ../board.php?page=project_details&project_id=' . $project_id . '&error=Task name is required');
    exit;
}

try {
    // Verify project belongs to current user
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    if (!$stmt->fetch()) {
        header('Location: ../board.php?page=home&error=Project not found');
        exit;
    }

    if ($task_id) {
        // Update existing task
        $stmt = $pdo->prepare("UPDATE tasks SET name = ?, type = ?, description = ?, status = ?, start_date = ?, end_date = ? WHERE id = ? AND project_id = ?");
        $success = $stmt->execute([$task_name, $task_type, $task_description, $task_status, $task_start_date, $task_end_date, $task_id, $project_id]);
        $message = 'Task updated successfully!';
    } else {
        // Create new task
        $stmt = $pdo->prepare("INSERT INTO tasks (project_id, name, type, description, status, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([$project_id, $task_name, $task_type, $task_description, $task_status, $task_start_date, $task_end_date]);
        $message = 'Task created successfully!';
    }

    if ($success) {
        header('Location: ../board.php?page=project_details&project_id=' . $project_id . '&success=' . urlencode($message));
    } else {
        header('Location: ../board.php?page=project_details&project_id=' . $project_id . '&error=Failed to save task');
    }
} catch (PDOException $e) {
    header('Location: ../board.php?page=project_details&project_id=' . $project_id . '&error=Database error occurred');
}
exit;
?>