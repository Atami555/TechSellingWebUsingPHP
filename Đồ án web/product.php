<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

$product = getProductById($product_id);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Lấy sản phẩm liên quan
$related_products = getProductsByCategory($product['category_id'], 4);

// Lấy đánh giá sản phẩm (nếu có)
$reviews = [];
if (isset($pdo)) {
    $stmt = $pdo->prepare("SELECT pr.*, u.name as user_name FROM product_reviews pr 
                          LEFT JOIN users u ON pr.user_id = u.id 
                          WHERE pr.product_id = ? AND pr.status = 'approved' 
                          ORDER BY pr.created_at DESC LIMIT 10");
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll();
}

// Tính điểm đánh giá trung bình
$avg_rating = 0;
if (!empty($reviews)) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $avg_rating = $total_rating / count($reviews);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - TechStore</title>
    <meta name="description" content="<?= htmlspecialchars($product['meta_description'] ?: substr($product['description'], 0, 160)) ?>">
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
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Giỏ hàng
                            <span class="badge bg-danger" id="cart-count">0</span>
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
                <li class="breadcrumb-item"><a href="products.php">Sản phẩm</a></li>
                <?php if ($product['category_name']): ?>
                <li class="breadcrumb-item"><a href="products.php?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>
    </div>

    <!-- Product Details -->
    <div class="container mb-5">
        <div class="row">
            <!-- Product Images -->
            <div class="col-lg-6 mb-4">
                <div class="product-image-container">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="img-fluid rounded main-product-image" id="mainImage">
                    
                    <?php if ($product['gallery']): ?>
                    <?php $gallery = json_decode($product['gallery'], true); ?>
                    <?php if (is_array($gallery) && !empty($gallery)): ?>
                    <div class="product-gallery mt-3">
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="gallery-thumb active" onclick="changeMainImage(this.src)">
                        <?php foreach($gallery as $image): ?>
                        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="gallery-thumb" onclick="changeMainImage(this.src)">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="product-info">
                    <h1 class="h2 mb-3"><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <!-- Rating -->
                    <?php if ($avg_rating > 0): ?>
                    <div class="rating mb-3">
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= $avg_rating ? 'text-warning' : 'text-muted' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="ms-2 text-muted">(<?= count($reviews) ?> đánh giá)</span>
                    </div>
                    <?php endif; ?>

                    <!-- Price -->
                    <div class="price-section mb-4">
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                        <div class="d-flex align-items-center">
                            <span class="h3 text-primary me-3"><?= formatPrice($product['sale_price']) ?></span>
                            <span class="h5 text-muted text-decoration-line-through"><?= formatPrice($product['price']) ?></span>
                            <span class="badge bg-danger ms-2">-<?= round((($product['price'] - $product['sale_price']) / $product['price']) * 100) ?>%</span>
                        </div>
                        <?php else: ?>
                        <span class="h3 text-primary"><?= formatPrice($product['price']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Short Description -->
                    <?php if ($product['short_description']): ?>
                    <div class="short-description mb-4">
                        <p class="text-muted"><?= htmlspecialchars($product['short_description']) ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Stock Status -->
                    <div class="stock-status mb-4">
                        <?php if ($product['stock_quantity'] <= 0): ?>
                        <span class="badge bg-danger fs-6">Hết hàng</span>
                        <?php elseif ($product['stock_quantity'] <= 10): ?>
                        <span class="badge bg-warning fs-6">Chỉ còn <?= $product['stock_quantity'] ?> sản phẩm</span>
                        <?php else: ?>
                        <span class="badge bg-success fs-6">Còn hàng</span>
                        <?php endif; ?>
                    </div>

                    <!-- Add to Cart -->
                    <div class="add-to-cart-section mb-4">
                        <?php if ($product['stock_quantity'] > 0): ?>
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <label class="form-label">Số lượng:</label>
                            </div>
                            <div class="col-auto">
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn" onclick="decreaseQuantity()">-</button>
                                    <input type="number" class="form-control quantity-input" id="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
                                    <button type="button" class="quantity-btn" onclick="increaseQuantity()">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-lg me-3 add-to-cart" data-id="<?= $product['id'] ?>">
                                <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ hàng
                            </button>
                            <button class="btn btn-outline-primary btn-lg" onclick="buyNow()">
                                <i class="fas fa-bolt me-2"></i>Mua ngay
                            </button>
                        </div>
                        <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>
                            <i class="fas fa-times me-2"></i>Sản phẩm hết hàng
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Product Features -->
                    <div class="product-features">
                        <h6 class="fw-bold mb-3">Tính năng nổi bật:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Bảo hành chính hãng</li>
                            <li><i class="fas fa-check text-success me-2"></i>Giao hàng miễn phí toàn quốc</li>
                            <li><i class="fas fa-check text-success me-2"></i>Đổi trả trong 30 ngày</li>
                            <li><i class="fas fa-check text-success me-2"></i>Hỗ trợ 24/7</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Details Tabs -->
        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">
                            <i class="fas fa-info-circle me-2"></i>Mô tả sản phẩm
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab">
                            <i class="fas fa-cogs me-2"></i>Thông số kỹ thuật
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                            <i class="fas fa-star me-2"></i>Đánh giá (<?= count($reviews) ?>)
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="productTabsContent">
                    <!-- Description Tab -->
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <div class="product-description">
                                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                                </div>
                                
                                <?php if ($product['features']): ?>
                                <div class="mt-4">
                                    <h6 class="fw-bold">Tính năng chi tiết:</h6>
                                    <div class="features-content">
                                        <?= nl2br(htmlspecialchars($product['features'])) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Specifications Tab -->
                    <div class="tab-pane fade" id="specifications" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <?php if ($product['specifications']): ?>
                                <?php $specs = json_decode($product['specifications'], true); ?>
                                <?php if (is_array($specs) && !empty($specs)): ?>
                                <div class="specifications-table">
                                    <table class="table table-striped">
                                        <tbody>
                                            <?php foreach($specs as $key => $value): ?>
                                            <tr>
                                                <td class="fw-bold" style="width: 30%;"><?= htmlspecialchars($key) ?></td>
                                                <td><?= htmlspecialchars($value) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-muted">Thông số kỹ thuật đang được cập nhật.</p>
                                <?php endif; ?>
                                <?php else: ?>
                                <p class="text-muted">Thông số kỹ thuật đang được cập nhật.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews Tab -->
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <?php if (!empty($reviews)): ?>
                                <div class="reviews-section">
                                    <?php foreach($reviews as $review): ?>
                                    <div class="review-item border-bottom pb-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($review['user_name']) ?></h6>
                                                <div class="stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($review['created_at'])) ?></small>
                                        </div>
                                        <?php if ($review['title']): ?>
                                        <h6 class="fw-bold"><?= htmlspecialchars($review['title']) ?></h6>
                                        <?php endif; ?>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Chưa có đánh giá nào</h5>
                                    <p class="text-muted">Hãy là người đầu tiên đánh giá sản phẩm này!</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">
                    <i class="fas fa-th-large me-2"></i>
                    Sản phẩm liên quan
                </h3>
                <div class="row">
                    <?php foreach($related_products as $related): ?>
                    <?php if ($related['id'] != $product['id']): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card product-card h-100 shadow-sm">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($related['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($related['name']) ?>">
                                <div class="product-overlay">
                                    <a href="product.php?id=<?= $related['id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> Xem chi tiết
                                    </a>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($related['name']) ?></h5>
                                <p class="card-text text-muted small"><?= htmlspecialchars(substr($related['description'], 0, 100)) ?>...</p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h6 text-primary mb-0"><?= formatPrice($related['price']) ?></span>
                                        <button class="btn btn-outline-primary btn-sm add-to-cart" data-id="<?= $related['id'] ?>">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
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
        // Quantity controls
        function increaseQuantity() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.getAttribute('max'));
            const current = parseInt(input.value);
            if (current < max) {
                input.value = current + 1;
            }
        }

        function decreaseQuantity() {
            const input = document.getElementById('quantity');
            const current = parseInt(input.value);
            if (current > 1) {
                input.value = current - 1;
            }
        }

        // Change main image
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.gallery-thumb').forEach(thumb => {
                thumb.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // Buy now function
        function buyNow() {
            const quantity = document.getElementById('quantity').value;
            const productId = <?= $product['id'] ?>;
            
            // Add to cart first
            fetch('ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to checkout
                    window.location.href = 'checkout.php';
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra', 'error');
            });
        }
    </script>
</body>
</html>
