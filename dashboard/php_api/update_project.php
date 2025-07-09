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

$projectId = (int)($input['project_id'] ?? 0);
$status = $input['status'] ?? '';

if (!$projectId || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Project ID and status are required']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    // Verify project belongs to current user
    $query = "SELECT id FROM projects WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$projectId, $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit();
    }
    
    // Update project status
    $query = "UPDATE projects SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$status, $projectId])) {
        echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update project']);
    }
} catch (Exception $e) {
    error_log("Error updating project: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>