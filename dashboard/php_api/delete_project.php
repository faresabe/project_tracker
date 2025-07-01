<?php
header("Content-Type: application/json");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Example: adjust for your DB connection
    $pdo = new PDO('mysql:host=localhost;dbname=project_manager', 'root', '');

    // Delete project & tasks (if separate table)
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $deleted = $stmt->execute([$id]);

    if ($deleted) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
} else {
    echo json_encode(["success" => false, "error" => "No ID."]);
}
?>
