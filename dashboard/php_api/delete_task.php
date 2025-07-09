<?php
require_once '../../authentication/config.php';

require_auth();
$user_id = get_current_user_id();

if (!isset($_GET['id']) || !isset($_GET['project_id'])) {
    header('Location: ../board.php?page=home&error=Invalid request');
    exit;
}

$task_id = intval($_GET['id']);
$project_id = intval($_GET['project_id']);

try {
    // Verify project belongs to current user and delete task
    $stmt = $pdo->prepare("DELETE t FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.id = ? AND p.id = ? AND p.user_id = ?");
    $success = $stmt->execute([$task_id, $project_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        header('Location: ../board.php?page=project_details&project_id=' . $project_id . '&success=Task deleted successfully');
    } else {
        header('Location: ../board.php?page=project_details&project_id=' . $project_id . '&error=Task not found or access denied');
    }
} catch (PDOException $e) {
    header('Location: ../board.php?page=project_details&project_id=' . $project_id . '&error=Database error occurred');
}
exit;
?>