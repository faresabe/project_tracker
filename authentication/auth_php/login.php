<?php
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['email']) || empty($data['password'])) {
    json_response(['error' => 'Email and password required'], 400);
}

try {
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, password_hash FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data['password'], $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        json_response(['success' => true, 'user' => [
            'id' => $user['id'],
            'name' => $user['first_name'] . ' ' . $user['last_name']
        ]]);
    } else {
        json_response(['error' => 'Invalid email or password'], 401);
    }
} catch (PDOException $e) {
    json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
}
?>
