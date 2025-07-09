<?php
session_start();

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connection.php';

function json_response($data = null, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_auth() {
    if (!is_logged_in()) {
        json_response(['error' => 'Authentication required'], 401);
    }
}

function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}
?>
