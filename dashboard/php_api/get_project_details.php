<?php
require_once '../../authentication/config.php';

require_auth();
$user_id = get_current_user_id();

if (!isset($_GET['id'])) {
    json_response(['error' => 'Project ID is required'], 400);
}

$projectId = intval($_GET['id']);

try {
    // Get project (ensure it belongs to current user)
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$projectId, $user_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        json_response(['error' => 'Project not found'], 404);
    }

    // Get tasks for this project
    $taskStmt = $pdo->prepare("SELECT * FROM tasks WHERE project_id = ? ORDER BY created_at DESC");
    $taskStmt->execute([$projectId]);
    $tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine
    $project['tasks'] = $tasks;

    json_response($project);

} catch (PDOException $e) {
    json_response(['error' => $e->getMessage()], 500);
}
?>