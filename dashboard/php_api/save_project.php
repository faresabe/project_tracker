<?php
require_once '../../authentication/db_connection.php';

header('Content-Type: application/json');
startSession();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$title = sanitizeInput($input['title'] ?? '');
$description = sanitizeInput($input['description'] ?? '');
$status = $input['status'] ?? 'pending';
$priority = $input['priority'] ?? 'medium';
$deadline = $input['deadline'] ?? null;

if (empty($title) || empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Title and description are required']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    $query = "INSERT INTO projects (user_id, title, description, status, priority, deadline) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$_SESSION['user_id'], $title, $description, $status, $priority, $deadline ?: null])) {
        $projectId = $db->lastInsertId();
        echo json_encode([
            'success' => true, 
            'message' => 'Project created successfully',
            'project_id' => $projectId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create project']);
    }
} catch (Exception $e) {
    error_log("Error creating project: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>