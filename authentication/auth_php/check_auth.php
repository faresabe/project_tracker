<?php
require_once '../config.php';

if (is_logged_in()) {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    json_response(['authenticated' => true, 'user' => [
        'name' => $user['first_name'] . ' ' . $user['last_name']
    ]]);
} else {
    json_response(['authenticated' => false]);
}
?>
