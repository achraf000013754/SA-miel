<?php
/**
 * API for Products
 * Endpoints:
 * GET  /api_products.php?action=get_all         - Get all products
 * GET  /api_products.php?action=get_one&id=1    - Get single product
 * GET  /api_products.php?action=search&q=honey  - Search products
 * GET  /api_products.php?action=get_category&cat=rare - Get by category
 */

require 'config.php';

$action = $_GET['action'] ?? '';

// Handle CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

switch ($action) {
    case 'get_all':
        getAll();
        break;
    
    case 'get_one':
        getOne();
        break;
    
    case 'search':
        search();
        break;
    
    case 'get_category':
        getCategory();
        break;
    
    default:
        sendError('Invalid action', 400);
}

function getAll() {
    global $conn;
    
    $sql = "SELECT id, name_ar, name_en, description_ar, description_en, price, category, 
            badge_ar, badge_en, stars, emoji, stock FROM products ORDER BY id";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        sendResponse(['success' => true, 'data' => $products]);
    } else {
        sendResponse(['success' => true, 'data' => []]);
    }
}

function getOne() {
    global $conn;
    
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        sendError('Invalid product ID', 400);
    }
    
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        sendResponse(['success' => true, 'data' => $result->fetch_assoc()]);
    } else {
        sendError('Product not found', 404);
    }
}

function search() {
    global $conn;
    
    $query = $_GET['q'] ?? '';
    
    if (strlen($query) < 2) {
        sendError('Search query too short', 400);
    }
    
    $searchTerm = '%' . $conn->real_escape_string($query) . '%';
    
    $sql = "SELECT id, name_ar, name_en, price, category, emoji, stars FROM products 
            WHERE name_ar LIKE ? OR name_en LIKE ? OR description_ar LIKE ? OR description_en LIKE ?
            LIMIT 20";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    sendResponse(['success' => true, 'data' => $products, 'count' => count($products)]);
}

function getCategory() {
    global $conn;
    
    $category = $_GET['cat'] ?? '';
    
    if (empty($category)) {
        sendError('Category not specified', 400);
    }
    
    $category = $conn->real_escape_string($category);
    
    $sql = "SELECT id, name_ar, name_en, price, category, emoji, stars FROM products 
            WHERE category = ? ORDER BY id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    sendResponse(['success' => true, 'data' => $products]);
}

$conn->close();
?>
