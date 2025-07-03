<?php
session_start();
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
?>
