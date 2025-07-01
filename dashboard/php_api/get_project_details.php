<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "project_manager";

if (!isset($_GET['id'])) {
  echo json_encode(['success' => false, 'error' => 'Project ID is required']);
  exit;
}

$projectId = intval($_GET['id']);

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Get project
  $stmt = $conn->prepare("SELECT * FROM projects WHERE id = :id");
  $stmt->execute([':id' => $projectId]);
  $project = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$project) {
    echo json_encode(['success' => false, 'error' => 'Project not found']);
    exit;
  }

  // Get tasks for this project
  $taskStmt = $conn->prepare("SELECT * FROM tasks WHERE project_id = :id");
  $taskStmt->execute([':id' => $projectId]);
  $tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);

  // Combine
  $project['tasks'] = $tasks;

  echo json_encode($project);

} catch (PDOException $e) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
