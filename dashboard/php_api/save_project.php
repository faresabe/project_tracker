<?php
require_once '../../authentication/config.php';

require_auth();
$user_id = get_current_user_id();

$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate
if (empty($data['title'])) {
    json_response(['error' => 'Project title is required'], 400);
}

try {
    // Insert project with user_id
    $stmt = $pdo->prepare("INSERT INTO projects (title, type, description, start_date, end_date, status, user_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");

    $success = $stmt->execute([
        $data['title'],
        $data['type'] ?? 'web',
        $data['description'] ?? '',
        $data['start_date'] ?? null,
        $data['end_date'] ?? null,
        $data['status'] ?? 'pending',
        $user_id
    ]);

    if ($success) {
        $projectId = $pdo->lastInsertId();

        // Save tasks if provided
        if (!empty($data['tasks']) && is_array($data['tasks'])) {
            $taskStmt = $pdo->prepare("INSERT INTO tasks (project_id, name, type, description, start_date, end_date, status)
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");

            foreach ($data['tasks'] as $task) {
                $taskStmt->execute([
                    $projectId,
                    $task['name'] ?? '',
                    $task['type'] ?? 'feature',
                    $task['description'] ?? '',
                    $task['start_date'] ?? null,
                    $task['end_date'] ?? null,
                    $task['status'] ?? 'pending'
                ]);
            }
        }

        json_response(['success' => true, 'projectId' => $projectId]);
    } else {
        json_response(['error' => 'Failed to save project'], 500);
    }

} catch (PDOException $e) {
    json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
}
?>