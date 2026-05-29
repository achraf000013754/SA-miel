<?php
/**
 * API for User Authentication
 * Endpoints:
 * POST /api_users.php?action=register  - Register new user
 * POST /api_users.php?action=login     - Login user
 * GET  /api_users.php?action=check_email&email=test@example.com - Check if email exists
 */

require 'config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        register();
        break;
    
    case 'login':
        login();
        break;
    
    case 'check_email':
        checkEmail();
        break;
    
    default:
        sendError('Invalid action', 400);
}

function register() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $name = trim($data['name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    
    // Validation
    if (empty($email) || empty($password) || empty($name)) {
        sendError('Email, password, and name are required', 400);
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendError('Invalid email format', 400);
    }
    
    if (strlen($password) < 6) {
        sendError('Password must be at least 6 characters', 400);
    }
    
    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        sendError('Email already registered', 409);
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $sql = "INSERT INTO users (email, password, name, phone) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $email, $hashedPassword, $name, $phone);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        sendResponse([
            'success' => true,
            'message' => 'Registration successful',
            'user' => [
                'id' => $user_id,
                'email' => $email,
                'name' => $name
            ]
        ], 201);
    } else {
        sendError('Error registering user: ' . $stmt->error, 500);
    }
}

function login() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        sendError('Email and password are required', 400);
    }
    
    // Get user
    $sql = "SELECT id, name, email, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendError('Invalid email or password', 401);
    }
    
    $user = $result->fetch_assoc();
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        sendError('Invalid email or password', 401);
    }
    
    // Generate token (simple JWT-like token)
    $token = bin2hex(random_bytes(32));
    
    // In production, store this token in a session or JWT
    // For now, we'll return it to the client
    
    sendResponse([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ],
        'token' => $token
    ]);
}

function checkEmail() {
    global $conn;
    
    $email = trim($_GET['email'] ?? '');
    
    if (empty($email)) {
        sendError('Email not provided', 400);
    }
    
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $exists = $result->num_rows > 0;
    
    sendResponse([
        'success' => true,
        'exists' => $exists,
        'email' => $email
    ]);
}

$conn->close();
?>
