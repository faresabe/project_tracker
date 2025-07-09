<?php
$host = 'localhost';
$dbname = 'project_manager';
$username = 'root';  // Change this to your MySQL username
$password = '';      // Change this to your MySQL password if you have one

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed. Please check your database configuration.']);
    error_log('Database connection error: ' . $e->getMessage());
    exit;
}
?>
