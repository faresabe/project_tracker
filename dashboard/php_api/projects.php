<?php
header("Access-Control-Allow-Origin: http://127.0.0.1:5501");
header("Access-Control-Allow-Methods: GET, POST, PUT");
header("Access-Control-Allow-Headers: Content-Type");
$dataFile = 'projects.json';

// Create file if it doesn't exist
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

function getProjects() {
    global $dataFile;
    return json_decode(file_get_contents($dataFile), true);
}

function saveProjects($projects) {
    global $dataFile;
    file_put_contents($dataFile, json_encode($projects));
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        echo file_get_contents($dataFile);
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $projects = getProjects();
        $newProject = [
            'id' => uniqid(),
            'title' => $input['title'],
            'type' => $input['type'],
            'description' => $input['description'],
            'startDate' => $input['startDate'],
            'endDate' => $input['endDate'],
            'status' => 'pending',
            'tasks' => []
        ];
        $projects[] = $newProject;
        saveProjects($projects);
        echo json_encode($newProject);
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $projects = getProjects();
        $updated = false;
        foreach ($projects as &$project) {
            if ($project['id'] === $input['id']) {
                $project = array_merge($project, $input);
                $updated = true;
                break;
            }
        }
        saveProjects($projects);
        echo json_encode(['success' => $updated]);
        break;
        
    case 'DELETE':
        $id = $_GET['id'];
        $projects = getProjects();
        $projects = array_filter($projects, function($project) use ($id) {
            return $project['id'] !== $id;
        });
        saveProjects(array_values($projects));
        echo json_encode(['success' => true]);
        break;
}
?>