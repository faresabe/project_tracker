<?php
require_once '../../authentication/db_connection.php';

header('Content-Type: application/json');
startSession();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    $query = "SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $projects = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'projects' => $projects
    ]);
} catch (Exception $e) {
    error_log("Error fetching projects: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>