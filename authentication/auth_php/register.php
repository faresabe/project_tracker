<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';

if (!$pdo) {
    json_response(['error' => 'Database not connected'], 500);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    json_response(['error' => 'Invalid JSON input'], 400);
}

if (
    empty($data['first_name']) ||
    empty($data['last_name']) ||
    empty($data['email']) ||
    empty($data['password'])
) {
    json_response(['error' => 'All fields are required'], 400);
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        json_response(['error' => 'Email already exists'], 400);
    }
} catch (PDOException $e) {
    json_response(['error' => 'Error checking email: ' . $e->getMessage()], 500);
}

$passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $passwordHash
    ]);

    if ($result) {
        json_response(['success' => true, 'message' => 'Registration successful']);
    } else {
        json_response(['error' => 'Registration failed'], 500);
    }
} catch (PDOException $e) {
    json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
}
?>
