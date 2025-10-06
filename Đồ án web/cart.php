<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Xử lý cập nhật giỏ hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $product_id = (int)$_POST['product_id'];
        
        switch ($_POST['action']) {
            case 'update':
                $quantity = (int)$_POST['quantity'];
                updateCartQuantity($product_id, $quantity);
                break;
            case 'remove':
                removeFromCart($product_id);
                break;
            case 'clear':
                clearCart();
                break;
        }
        
        header('Location: cart.php');
        exit;
    }
}

$cart = getCart();
$cart_total = getCartTotal();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-laptop-code me-2"></i>TechStore
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Danh mục
                        </a>
                        <ul class="dropdown-menu">
                            <?php 
                            $categories = getCategories();
                            foreach($categories as $category): 
                            ?>
                            <li><a class="dropdown-item" href="products.php?category=<?= $category['id'] ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Tất cả sản phẩm</a>
                    </li>
                </ul>
                
                <form class="d-flex me-3" method="GET" action="search.php">
                    <input class="form-control me-2" type="search" name="q" placeholder="Tìm kiếm sản phẩm...">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Giỏ hàng
                            <span class="badge bg-danger" id="cart-count"><?= count($cart) ?></span>
                        </a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Tài khoản</a></li>
                            <li><a class="dropdown-item" href="orders.php">Đơn hàng</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Đăng nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Đăng ký</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="container mt-5 pt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item active">Giỏ hàng</li>
            </ol>
        </nav>
    </div>

    <!-- Cart Content -->
    <div class="container mb-5">
        <div class="row">
            <div class="col-12">
                <h1 class="h2 mb-4">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Giỏ hàng của bạn
                </h1>
            </div>
        </div>

        <?php if (empty($cart)): ?>
        <!-- Empty Cart -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                    <h3 class="text-muted">Giỏ hàng trống</h3>
                    <p class="text-muted mb-4">Bạn chưa có sản phẩm nào trong giỏ hàng. Hãy bắt đầu mua sắm!</p>
                    <a href="products.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Cart Items -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Sản phẩm trong giỏ hàng</h5>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tất cả sản phẩm khỏi giỏ hàng?')">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-trash me-1"></i>Xóa tất cả
                            </button>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach($cart as $item): ?>
                        <div class="cart-item" data-product-id="<?= $item['product']['id'] ?>">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="<?= htmlspecialchars($item['product']['image']) ?>" 
                                         alt="<?= htmlspecialchars($item['product']['name']) ?>" 
                                         class="cart-item-image">
                                </div>
                                <div class="col-md-4">
                                    <h6 class="mb-1">
                                        <a href="product.php?id=<?= $item['product']['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($item['product']['name']) ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">SKU: <?= htmlspecialchars($item['product']['sku']) ?></small>
                                </div>
                                <div class="col-md-2">
                                    <div class="quantity-controls">
                                        <button type="button" class="quantity-btn" 
                                                data-action="decrease" data-id="<?= $item['product']['id'] ?>">-</button>
                                        <input type="number" class="form-control quantity-input" 
                                               value="<?= $item['quantity'] ?>" min="1" max="<?= $item['product']['stock_quantity'] ?>"
                                               data-product-id="<?= $item['product']['id'] ?>">
                                        <button type="button" class="quantity-btn" 
                                                data-action="increase" data-id="<?= $item['product']['id'] ?>">+</button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <span class="h6 text-primary mb-0">
                                            <?= formatPrice($item['product']['price']) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            Tổng: <?= formatPrice($item['product']['price'] * $item['quantity']) ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?= $item['product']['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm remove-from-cart" 
                                                    data-id="<?= $item['product']['id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Continue Shopping -->
                <div class="mt-4">
                    <a href="products.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm
                    </a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="order-summary">
                    <h5 class="mb-3">Tóm tắt đơn hàng</h5>
                    
                    <div class="summary-item">
                        <span>Tạm tính (<?= count($cart) ?> sản phẩm):</span>
                        <span id="cart-subtotal"><?= formatPrice($cart_total) ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Phí vận chuyển:</span>
                        <span>
                            <?php if ($cart_total >= 500000): ?>
                            <span class="text-success">Miễn phí</span>
                            <?php else: ?>
                            <span>30.000₫</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Thuế VAT:</span>
                        <span>Đã bao gồm</span>
                    </div>
                    
                    <hr>
                    
                    <div class="summary-total">
                        <span>Tổng cộng:</span>
                        <span id="cart-total">
                            <?= formatPrice($cart_total + ($cart_total >= 500000 ? 0 : 30000)) ?>
                        </span>
                    </div>
                    
                    <div class="mt-4">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="checkout.php" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-credit-card me-2"></i>Thanh toán
                        </a>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Vui lòng đăng nhập để thanh toán
                        </div>
                        <a href="login.php" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập để thanh toán
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Security Badges -->
                    <div class="mt-4 text-center">
                        <div class="row">
                            <div class="col-4">
                                <div class="text-center">
                                    <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                                    <small class="d-block">Bảo mật</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <i class="fas fa-truck fa-2x text-primary mb-2"></i>
                                    <small class="d-block">Giao hàng</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <i class="fas fa-undo fa-2x text-warning mb-2"></i>
                                    <small class="d-block">Đổi trả</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Promo Code -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-tag me-2"></i>Mã giảm giá</h6>
                    </div>
                    <div class="card-body">
                        <form id="promoForm">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Nhập mã giảm giá" id="promoCode">
                                <button class="btn btn-outline-primary" type="submit">Áp dụng</button>
                            </div>
                        </form>
                        <div id="promoMessage" class="mt-2"></div>
                    </div>
                </div>

                <!-- Recommended Products -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-star me-2"></i>Sản phẩm gợi ý</h6>
                    </div>
                    <div class="card-body">
                        <?php 
                        $featured_products = getFeaturedProducts(3);
                        foreach($featured_products as $product): 
                        ?>
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="me-3" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <a href="product.php?id=<?= $product['id'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </a>
                                </h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-primary fw-bold"><?= formatPrice($product['price']) ?></span>
                                    <button class="btn btn-outline-primary btn-sm add-to-cart" data-id="<?= $product['id'] ?>">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><i class="fas fa-laptop-code me-2"></i>TechStore</h5>
                    <p class="text-white-50">
                        Cửa hàng điện tử uy tín với hơn 10 năm kinh nghiệm, 
                        cung cấp những sản phẩm công nghệ chất lượng cao.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-youtube fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-tiktok fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Danh mục</h6>
                    <ul class="list-unstyled">
                        <li><a href="products.php?category=1" class="text-white-50">Điện thoại</a></li>
                        <li><a href="products.php?category=2" class="text-white-50">Laptop</a></li>
                        <li><a href="products.php?category=4" class="text-white-50">Phụ kiện</a></li>
                        <li><a href="products.php?category=5" class="text-white-50">Gaming</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Hỗ trợ</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50">Liên hệ</a></li>
                        <li><a href="#" class="text-white-50">Bảo hành</a></li>
                        <li><a href="#" class="text-white-50">Đổi trả</a></li>
                        <li><a href="#" class="text-white-50">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h6>Liên hệ</h6>
                    <ul class="list-unstyled text-white-50">
                        <li><i class="fas fa-map-marker-alt me-2"></i>123 Đường ABC, Quận 1, TP.HCM</li>
                        <li><i class="fas fa-phone me-2"></i>0123 456 789</li>
                        <li><i class="fas fa-envelope me-2"></i>info@techstore.com</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-white-50">&copy; 2024 TechStore. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <img src="assets/images/payment-methods.png" alt="Payment Methods" class="img-fluid" style="max-height: 30px;">
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Promo code form
        document.getElementById('promoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const promoCode = document.getElementById('promoCode').value;
            const messageDiv = document.getElementById('promoMessage');
            
            if (promoCode.trim() === '') {
                messageDiv.innerHTML = '<div class="alert alert-warning">Vui lòng nhập mã giảm giá</div>';
                return;
            }
            
            // Simulate promo code validation
            const validCodes = ['WELCOME10', 'SAVE20', 'TECHSTORE15'];
            if (validCodes.includes(promoCode.toUpperCase())) {
                messageDiv.innerHTML = '<div class="alert alert-success">Mã giảm giá đã được áp dụng!</div>';
            } else {
                messageDiv.innerHTML = '<div class="alert alert-danger">Mã giảm giá không hợp lệ</div>';
            }
        });
    </script>
</body>
</html>
