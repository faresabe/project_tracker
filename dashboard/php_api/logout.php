<?php
require_once '../../authentication/config.php';

session_destroy();

// Check if it's an AJAX request
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    json_response(['success' => true]);
} else {
    // Regular redirect
    header('Location: ../../index.html');
    exit;
}
?>