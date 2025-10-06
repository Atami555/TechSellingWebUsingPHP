<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$products = [];
$total_products = 0;

if (!empty($search_query)) {
    $products = searchProducts($search_query, $limit);
    $total_products = count(searchProducts($search_query, 1000)); // Lấy tất cả để đếm
}

$total_pages = ceil($total_products / $limit);
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm: <?= htmlspecialchars($search_query) ?> - TechStore</title>
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
                            <?php foreach($categories as $category): ?>
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
                    <input class="form-control me-2" type="search" name="q" placeholder="Tìm kiếm sản phẩm..." value="<?= htmlspecialchars($search_query) ?>">
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
                <li class="breadcrumb-item active">Tìm kiếm</li>
            </ol>
        </nav>
    </div>

    <!-- Search Results -->
    <div class="container mb-5">
        <div class="row">
            <div class="col-12">
                <h1 class="h2 mb-4">
                    <i class="fas fa-search me-2"></i>
                    Kết quả tìm kiếm
                </h1>
                
                <?php if (!empty($search_query)): ?>
                <div class="search-info mb-4">
                    <p class="text-muted">
                        Tìm kiếm cho: <strong>"<?= htmlspecialchars($search_query) ?>"</strong>
                        <?php if ($total_products > 0): ?>
                        - Tìm thấy <?= $total_products ?> kết quả
                        <?php endif; ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($search_query)): ?>
        <!-- No Search Query -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h3 class="text-muted">Nhập từ khóa để tìm kiếm</h3>
                    <p class="text-muted">Sử dụng ô tìm kiếm ở trên để tìm sản phẩm bạn muốn</p>
                </div>
            </div>
        </div>
        <?php elseif (empty($products)): ?>
        <!-- No Results -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-search-minus fa-4x text-muted mb-3"></i>
                    <h3 class="text-muted">Không tìm thấy kết quả</h3>
                    <p class="text-muted mb-4">
                        Không tìm thấy sản phẩm nào cho từ khóa "<?= htmlspecialchars($search_query) ?>". 
                        Hãy thử với từ khóa khác.
                    </p>
                    <div class="search-suggestions">
                        <h6 class="mb-3">Gợi ý tìm kiếm:</h6>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="search.php?q=iphone" class="btn btn-outline-primary btn-sm">iPhone</a>
                            <a href="search.php?q=samsung" class="btn btn-outline-primary btn-sm">Samsung</a>
                            <a href="search.php?q=laptop" class="btn btn-outline-primary btn-sm">Laptop</a>
                            <a href="search.php?q=tai+nghe" class="btn btn-outline-primary btn-sm">Tai nghe</a>
                            <a href="search.php?q=gaming" class="btn btn-outline-primary btn-sm">Gaming</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Search Results -->
        <div class="row">
            <?php foreach($products as $product): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card product-card h-100 shadow-sm">
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="product-overlay">
                            <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="card-text text-muted small"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                    <span class="h5 text-primary mb-0"><?= formatPrice($product['sale_price']) ?></span>
                                    <small class="text-muted text-decoration-line-through ms-2"><?= formatPrice($product['price']) ?></small>
                                    <?php else: ?>
                                    <span class="h5 text-primary mb-0"><?= formatPrice($product['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-outline-primary btn-sm add-to-cart" data-id="<?= $product['id'] ?>">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                            <?php if ($product['stock_quantity'] <= 0): ?>
                            <div class="mt-2">
                                <span class="badge bg-danger">Hết hàng</span>
                            </div>
                            <?php elseif ($product['stock_quantity'] <= 10): ?>
                            <div class="mt-2">
                                <span class="badge bg-warning">Sắp hết hàng</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Search results pagination" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?q=<?= urlencode($search_query) ?>&page=<?= $page - 1 ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?q=<?= urlencode($search_query) ?>&page=<?= $i ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?q=<?= urlencode($search_query) ?>&page=<?= $page + 1 ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
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
</body>
</html>
