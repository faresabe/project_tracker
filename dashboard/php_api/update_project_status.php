<?php
require_once '../../authentication/config.php';

require_auth();
$user_id = get_current_user_id();

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (empty($data['id']) || empty($data['status'])) {
    json_response(['error' => 'Project ID and status required'], 400);
}

$validStatuses = ['pending', 'ongoing', 'completed'];
if (!in_array($data['status'], $validStatuses)) {
    json_response(['error' => 'Invalid status'], 400);
}

try {
    $stmt = $pdo->prepare("UPDATE projects SET status = ? WHERE id = ? AND user_id = ?");
    $success = $stmt->execute([$data['status'], $data['id'], $user_id]);

    if ($stmt->rowCount() > 0) {
        json_response(['success' => true]);
    } else {
        json_response(['error' => 'Project not found or access denied'], 404);
    }
} catch (PDOException $e) {
    json_response(['error' => $e->getMessage()], 500);
}
?>