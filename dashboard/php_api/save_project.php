<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "project_manager";

$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate
if (empty($data['title'])) {
    echo json_encode(['success' => false, 'error' => 'Project title is required']);
    exit;
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert the project
    $stmt = $conn->prepare("INSERT INTO projects (title, type, description, start_date, end_date, status)
                            VALUES (:title, :type, :description, :start_date, :end_date, :status)");
    $success = $stmt->execute([
        ':title' => $data['title'],
        ':type' => $data['type'] ?? 'web',
        ':description' => $data['description'] ?? '',
        ':start_date' => $data['start_date'] ?? null,
        ':end_date' => $data['end_date'] ?? null,
        ':status' => $data['status'] ?? 'pending'
    ]);

    if ($success) {
        $projectId = $conn->lastInsertId();

        if (!empty($data['tasks']) && is_array($data['tasks'])) {
            $taskStmt = $conn->prepare("INSERT INTO tasks (project_id, name, type, description, start_date, end_date)
                            VALUES (:project_id, :name, :type, :description, :start_date, :end_date)");

            foreach ($data['tasks'] as $task) {
                $taskStmt->execute([
                    ':project_id' => $projectId,
                    ':name' => $task['name'] ?? '',
                    ':type' => $task['type'] ?? '',
                    ':description' => $task['description'] ?? '',
                    ':start_date' => $task['start_date'] ?? null,
                    ':end_date' => $task['end_date'] ?? null,
                    
                ]);
            }
        }

        echo json_encode(['success' => true, 'projectId' => $projectId]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save project']);
    }

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
