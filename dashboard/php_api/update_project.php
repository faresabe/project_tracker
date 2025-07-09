<?php
require_once '../../authentication/config.php';

require_auth();
$user_id = get_current_user_id();

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (empty($data['id'])) {
    json_response(['error' => 'Project ID required'], 400);
}

try {
    // Verify project belongs to current user
    $checkStmt = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$data['id'], $user_id]);
    
    if (!$checkStmt->fetch()) {
        json_response(['error' => 'Project not found or access denied'], 404);
    }

    // Update the project
    $stmt = $pdo->prepare("UPDATE projects SET title = ?, type = ?, description = ?, start_date = ?, end_date = ?, status = ? WHERE id = ? AND user_id = ?");
    
    $success = $stmt->execute([
        $data['title'] ?? '',
        $data['type'] ?? 'web',
        $data['description'] ?? '',
        $data['start_date'] ?? null,
        $data['end_date'] ?? null,
        $data['status'] ?? 'pending',
        $data['id'],
        $user_id
    ]);

    if ($success) {
        // Delete existing tasks
        $deleteStmt = $pdo->prepare("DELETE FROM tasks WHERE project_id = ?");
        $deleteStmt->execute([$data['id']]);

        // Insert new tasks
        if (!empty($data['tasks']) && is_array($data['tasks'])) {
            $taskStmt = $pdo->prepare("INSERT INTO tasks (project_id, name, type, description, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");

            foreach ($data['tasks'] as $task) {
                $taskStmt->execute([
                    $data['id'],
                    $task['name'] ?? '',
                    $task['type'] ?? 'feature',
                    $task['description'] ?? '',
                    $task['start_date'] ?? null,
                    $task['end_date'] ?? null,
                    $task['status'] ?? 'pending'
                ]);
            }
        }

        json_response(['success' => true]);
    } else {
        json_response(['error' => 'Failed to update project'], 500);
    }

} catch (PDOException $e) {
    json_response(['error' => $e->getMessage()], 500);
}
?>