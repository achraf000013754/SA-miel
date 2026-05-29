<?php
/**
 * Database Configuration File
 * WAMPSERVER Configuration
 */

// Database Connection Parameters
define('DB_HOST', 'localhost');      // WAMPSERVER default host
define('DB_USER', 'root');            // WAMPSERVER default user (no password by default)
define('DB_PASS', '');                // WAMPSERVER default password (empty)
define('DB_NAME', 'sa_miel_store');  // Database name

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS Headers for API calls
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Database Connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection error: ' . $e->getMessage()]);
    exit;
}

// Helper function for JSON response
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Helper function for error response
function sendError($message, $statusCode = 400) {
    sendResponse(['error' => $message], $statusCode);
}
?>
