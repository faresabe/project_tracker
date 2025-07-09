<?php
require_once '../../authentication/config.php';

require_auth();
$user_id = get_current_user_id();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../board.php');
    exit;
}

$project_id = intval($_POST['project_id'] ?? 0);
$status = $_POST['status'] ?? '';

$valid_statuses = ['pending', 'ongoing', 'completed'];
if (!in_array($status, $valid_statuses)) {
    header('Location: ../board.php?page=home&error=Invalid status');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE projects SET status = ? WHERE id = ? AND user_id = ?");
    $success = $stmt->execute([$status, $project_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        header('Location: ../board.php?page=home&success=Project status updated');
    } else {
        header('Location: ../board.php?page=home&error=Project not found or access denied');
    }
} catch (PDOException $e) {
    header('Location: ../board.php?page=home&error=Database error occurred');
}
exit;
?>