<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing product ID']);
    exit;
}

$product_id = (int)$input['product_id'];

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Xóa sản phẩm khỏi giỏ hàng
removeFromCart($product_id);

echo json_encode([
    'success' => true, 
    'message' => 'Đã xóa sản phẩm khỏi giỏ hàng',
    'cart_count' => array_sum($_SESSION['cart'] ?? [])
]);
?>
