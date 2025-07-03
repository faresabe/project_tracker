<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "project_manager";

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (empty($data['id'])) {
  echo json_encode(['success' => false, 'error' => 'Project ID required']);
  exit;
}

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Update the project
  $taskStmt = $conn->prepare("
  INSERT INTO tasks (project_id, name, type, description, start_date, end_date, status)
  VALUES (:project_id, :name, :type, :description, :start_date, :end_date, :status)
");


$taskStmt->execute([
    ':project_id' => $data['id'],
    ':name' => $task['name'] ?? '',
    ':type' => $task['type'] ?? '',
    ':description' => $task['description'] ?? '',
    ':start_date' => $task['start_date'] ?? null,
    ':end_date' => $task['end_date'] ?? null,
    ':status' => $task['status'] ?? 'pending' 
  ]);
  

 
  $deleteStmt = $conn->prepare("DELETE FROM tasks WHERE project_id = :project_id");
  $deleteStmt->execute([':project_id' => $data['id']]);

  
  if (!empty($data['tasks']) && is_array($data['tasks'])) {
    $taskStmt = $conn->prepare("
      INSERT INTO tasks (project_id, name, type, description, start_date, end_date) 
      VALUES (:project_id, :name, :type, :description, :start_date, :end_date)
    ");

    foreach ($data['tasks'] as $task) {
      $taskStmt->execute([
        ':project_id' => $data['id'],
        ':name' => $task['name'] ?? '',
        ':type' => $task['type'] ?? '',
        ':description' => $task['description'] ?? '',
        ':start_date' => $task['start_date'] ?? null,
        ':end_date' => $task['end_date'] ?? null
      ]);
    }
  }

  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
