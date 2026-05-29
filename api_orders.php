<?php
/**
 * API for Orders
 * Endpoints:
 * POST /api_orders.php?action=create  - Create order
 * GET  /api_orders.php?action=get_user_orders&user_id=1 - Get user orders
 * GET  /api_orders.php?action=get_order&order_id=1 - Get order details
 */

require 'config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        createOrder();
        break;
    
    case 'get_user_orders':
        getUserOrders();
        break;
    
    case 'get_order':
        getOrder();
        break;
    
    default:
        sendError('Invalid action', 400);
}

function createOrder() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $user_id = intval($data['user_id'] ?? 0);
    $items = $data['items'] ?? [];
    $total_amount = floatval($data['total_amount'] ?? 0);
    $shipping_cost = floatval($data['shipping_cost'] ?? 0);
    $notes = trim($data['notes'] ?? '');
    
    // Validation
    if ($user_id <= 0) {
        sendError('Valid user_id required', 400);
    }
    
    if (count($items) === 0) {
        sendError('Order must have at least one item', 400);
    }
    
    if ($total_amount <= 0) {
        sendError('Invalid total amount', 400);
    }
    
    // Check user exists
    $sql = "SELECT id FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        sendError('User not found', 404);
    }
    
    // Insert order
    $sql = "INSERT INTO orders (user_id, total_amount, shipping_cost, notes, status) 
            VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idds", $user_id, $total_amount, $shipping_cost, $notes);
    
    if (!$stmt->execute()) {
        sendError('Error creating order: ' . $stmt->error, 500);
    }
    
    $order_id = $conn->insert_id;
    
    // Insert order items
    $items_inserted = 0;
    foreach ($items as $item) {
        $product_id = intval($item['product_id'] ?? 0);
        $quantity = intval($item['quantity'] ?? 1);
        $price = floatval($item['price'] ?? 0);
        
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $order_id, $product_id, $quantity, $price);
        
        if ($stmt->execute()) {
            $items_inserted++;
        }
    }
    
    if ($items_inserted !== count($items)) {
        sendError('Error inserting some order items', 500);
    }
    
    sendResponse([
        'success' => true,
        'message' => 'Order created successfully',
        'order_id' => $order_id,
        'total_amount' => $total_amount
    ], 201);
}

function getUserOrders() {
    global $conn;
    
    $user_id = intval($_GET['user_id'] ?? 0);
    
    if ($user_id <= 0) {
        sendError('Valid user_id required', 400);
    }
    
    $sql = "SELECT id, total_amount, shipping_cost, status, created_at 
            FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $orders = [];
    
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    sendResponse([
        'success' => true,
        'data' => $orders,
        'count' => count($orders)
    ]);
}

function getOrder() {
    global $conn;
    
    $order_id = intval($_GET['order_id'] ?? 0);
    
    if ($order_id <= 0) {
        sendError('Valid order_id required', 400);
    }
    
    // Get order details
    $sql = "SELECT o.id, o.user_id, o.total_amount, o.shipping_cost, o.status, o.notes, o.created_at,
                   u.name, u.email, u.phone, u.address
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendError('Order not found', 404);
    }
    
    $order = $result->fetch_assoc();
    
    // Get order items
    $sql = "SELECT oi.id, oi.product_id, oi.quantity, oi.price_at_purchase,
                   p.name_ar, p.name_en, p.emoji
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    $items_result = $stmt->get_result();
    $items = [];
    
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    $order['items'] = $items;
    
    sendResponse([
        'success' => true,
        'data' => $order
    ]);
}

$conn->close();
?>
