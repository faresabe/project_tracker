<?php
require_once '../../authentication/config.php';

require_auth();
$user_id = get_current_user_id();

if (!isset($_GET['id'])) {
    json_response(['error' => 'Project ID required'], 400);
}

$id = intval($_GET['id']);

try {
    // Verify project belongs to current user and delete
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
    $deleted = $stmt->execute([$id, $user_id]);

    if ($stmt->rowCount() > 0) {
        json_response(['success' => true]);
    } else {
        json_response(['error' => 'Project not found or access denied'], 404);
    }
} catch (PDOException $e) {
    json_response(['error' => $e->getMessage()], 500);
}
?>