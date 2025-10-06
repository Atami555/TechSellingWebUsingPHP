<?php
require_once 'config/database.php';

// Lấy sản phẩm nổi bật
function getFeaturedProducts($limit = 8) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE featured = 1 AND status = 'active' ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Lấy danh mục
function getCategories() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Lấy sản phẩm theo danh mục
function getProductsByCategory($category_id, $limit = 12) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$category_id, $limit]);
    return $stmt->fetchAll();
}

// Lấy chi tiết sản phẩm
function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = ? AND p.status = 'active'");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Tìm kiếm sản phẩm
function searchProducts($keyword, $limit = 20) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE (name LIKE ? OR description LIKE ?) AND status = 'active' ORDER BY created_at DESC LIMIT ?");
    $searchTerm = "%$keyword%";
    $stmt->execute([$searchTerm, $searchTerm, $limit]);
    return $stmt->fetchAll();
}

// Lấy tất cả sản phẩm
function getAllProducts($limit = 20, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    return $stmt->fetchAll();
}

// Đếm tổng số sản phẩm
function countProducts() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
    $stmt->execute();
    return $stmt->fetch()['total'];
}

// Thêm sản phẩm vào giỏ hàng
function addToCart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

// Lấy giỏ hàng
function getCart() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    global $pdo;
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll();
    
    $cart = [];
    foreach ($products as $product) {
        $cart[] = [
            'product' => $product,
            'quantity' => $_SESSION['cart'][$product['id']]
        ];
    }
    
    return $cart;
}

// Tính tổng giỏ hàng
function getCartTotal() {
    $cart = getCart();
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['product']['price'] * $item['quantity'];
    }
    return $total;
}

// Xóa sản phẩm khỏi giỏ hàng
function removeFromCart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

// Cập nhật số lượng sản phẩm trong giỏ hàng
function updateCartQuantity($product_id, $quantity) {
    if ($quantity <= 0) {
        removeFromCart($product_id);
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

// Xóa toàn bộ giỏ hàng
function clearCart() {
    $_SESSION['cart'] = [];
}

// Đăng nhập người dùng
function loginUser($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        return true;
    }
    return false;
}

// Đăng ký người dùng
function registerUser($name, $email, $password, $phone = '') {
    global $pdo;
    
    // Kiểm tra email đã tồn tại
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false; // Email đã tồn tại
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, created_at) VALUES (?, ?, ?, ?, NOW())");
    return $stmt->execute([$name, $email, $hashed_password, $phone]);
}

// Lấy thông tin người dùng
function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Tạo đơn hàng
function createOrder($user_id, $cart, $shipping_info) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Tạo đơn hàng
        $total = getCartTotal();
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_name, shipping_phone, shipping_address, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->execute([$user_id, $total, $shipping_info['name'], $shipping_info['phone'], $shipping_info['address']]);
        $order_id = $pdo->lastInsertId();
        
        // Thêm chi tiết đơn hàng
        foreach ($cart as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product']['id'], $item['quantity'], $item['product']['price']]);
        }
        
        $pdo->commit();
        clearCart();
        return $order_id;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// Lấy đơn hàng của người dùng
function getUserOrders($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Lấy chi tiết đơn hàng
function getOrderDetails($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT o.*, oi.*, p.name as product_name, p.image as product_image 
                          FROM orders o 
                          LEFT JOIN order_items oi ON o.id = oi.order_id 
                          LEFT JOIN products p ON oi.product_id = p.id 
                          WHERE o.id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}

// Format giá tiền
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . '₫';
}

// Tạo slug từ tên
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}
?>
