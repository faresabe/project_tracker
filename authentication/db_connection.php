<?php
class Database {
    private $host = "localhost";
    private $db_name = "project_management";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            die("Database connection failed");
        }
        return $this->conn;
    }
}

// Session management functions
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function requireLogin() {
    startSession();
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../authentication/sign_in.php");
        exit();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    startSession();
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, name, email, theme, email_notifications FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    
    return $stmt->fetch();
}

function setUserSession($user) {
    startSession();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
}

function destroyUserSession() {
    startSession();
    session_destroy();
}

// Utility functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>