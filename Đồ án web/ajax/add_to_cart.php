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

if (!isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$product_id = (int)$input['product_id'];
$quantity = (int)$input['quantity'];

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Kiểm tra sản phẩm có tồn tại và còn hàng không
$product = getProductById($product_id);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit;
}

if ($product['stock_quantity'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm đã hết hàng']);
    exit;
}

// Kiểm tra số lượng có vượt quá tồn kho không
$current_cart_quantity = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] : 0;
$total_quantity = $current_cart_quantity + $quantity;

if ($total_quantity > $product['stock_quantity']) {
    echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá tồn kho']);
    exit;
}

// Thêm vào giỏ hàng
addToCart($product_id, $quantity);

echo json_encode([
    'success' => true, 
    'message' => 'Đã thêm sản phẩm vào giỏ hàng',
    'cart_count' => array_sum($_SESSION['cart'] ?? [])
]);
?>
