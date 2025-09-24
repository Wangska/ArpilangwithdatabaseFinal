<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user information
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, nickname, email, username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Login user
function loginUser($email, $password) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}

// Register user
function registerUser($first_name, $last_name, $nickname, $email, $username, $password) {
    $pdo = getDBConnection();
    
    // Check if email or username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        return false; // User already exists
    }
    
    // Hash password and insert user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, nickname, email, username, password) VALUES (?, ?, ?, ?, ?, ?)");
    
    try {
        $stmt->execute([$first_name, $last_name, $nickname, $email, $username, $hashed_password]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Logout user
function logoutUser() {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Redirect to login if not authenticated
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Generate random invitation code
function generateInvitationCode() {
    return strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 9));
}
?>
