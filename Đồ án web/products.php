<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Lấy tham số từ URL
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$limit = 12;
$offset = ($page - 1) * $limit;

// Lấy sản phẩm
if ($search) {
    $products = searchProducts($search, $limit);
    $total_products = count(searchProducts($search, 1000)); // Lấy tất cả để đếm
} else {
    $products = getAllProducts($limit, $offset);
    $total_products = countProducts();
}

// Lấy danh mục
$categories = getCategories();

// Lấy thông tin danh mục hiện tại
$current_category = null;
if ($category_id) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $category_id) {
            $current_category = $cat;
            break;
        }
    }
}

$total_pages = ceil($total_products / $limit);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $current_category ? htmlspecialchars($current_category['name']) . ' - ' : '' ?>Sản phẩm - TechStore</title>
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
                        <a class="nav-link active" href="products.php">Tất cả sản phẩm</a>
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
                <?php if ($current_category): ?>
                <li class="breadcrumb-item"><a href="products.php">Sản phẩm</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($current_category['name']) ?></li>
                <?php else: ?>
                <li class="breadcrumb-item active">Sản phẩm</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>

    <!-- Page Header -->
    <div class="container mb-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h2 mb-3">
                    <?php if ($current_category): ?>
                        <i class="<?= htmlspecialchars($current_category['icon']) ?> me-2"></i>
                        <?= htmlspecialchars($current_category['name']) ?>
                    <?php else: ?>
                        <i class="fas fa-box me-2"></i>
                        Tất cả sản phẩm
                    <?php endif; ?>
                </h1>
                <?php if ($current_category && $current_category['description']): ?>
                <p class="text-muted"><?= htmlspecialchars($current_category['description']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Bộ lọc</h5>
                    </div>
                    <div class="card-body">
                        <!-- Categories Filter -->
                        <div class="mb-4">
                            <h6 class="fw-bold">Danh mục</h6>
                            <div class="list-group list-group-flush">
                                <a href="products.php" class="list-group-item list-group-item-action <?= !$category_id ? 'active' : '' ?>">
                                    Tất cả danh mục
                                </a>
                                <?php foreach($categories as $category): ?>
                                <a href="products.php?category=<?= $category['id'] ?>" 
                                   class="list-group-item list-group-item-action <?= $category_id == $category['id'] ? 'active' : '' ?>">
                                    <i class="<?= htmlspecialchars($category['icon']) ?> me-2"></i>
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Price Filter -->
                        <div class="mb-4">
                            <h6 class="fw-bold">Khoảng giá</h6>
                            <form method="GET" id="priceFilter">
                                <?php if ($category_id): ?>
                                <input type="hidden" name="category" value="<?= $category_id ?>">
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="min_price" 
                                               placeholder="Từ" value="<?= $min_price ?>" min="0">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="max_price" 
                                               placeholder="Đến" value="<?= $max_price ?>" min="0">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm mt-2 w-100">Áp dụng</button>
                            </form>
                        </div>

                        <!-- Sort Options -->
                        <div class="mb-4">
                            <h6 class="fw-bold">Sắp xếp</h6>
                            <form method="GET" id="sortFilter">
                                <?php if ($category_id): ?>
                                <input type="hidden" name="category" value="<?= $category_id ?>">
                                <?php endif; ?>
                                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                                    <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                                    <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Giá thấp đến cao</option>
                                    <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Giá cao đến thấp</option>
                                    <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Tên A-Z</option>
                                    <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>Tên Z-A</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Results Info -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <span class="text-muted">
                            Hiển thị <?= count($products) ?> trong <?= $total_products ?> sản phẩm
                        </span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="me-2">Hiển thị:</span>
                        <select class="form-select form-select-sm" style="width: auto;">
                            <option value="12">12 sản phẩm</option>
                            <option value="24">24 sản phẩm</option>
                            <option value="48">48 sản phẩm</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($products)): ?>
                <!-- No Products -->
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Không tìm thấy sản phẩm</h4>
                    <p class="text-muted">Hãy thử tìm kiếm với từ khóa khác hoặc duyệt các danh mục khác.</p>
                    <a href="products.php" class="btn btn-primary">Xem tất cả sản phẩm</a>
                </div>
                <?php else: ?>
                <!-- Products Grid -->
                <div class="row">
                    <?php foreach($products as $product): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
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
                <nav aria-label="Products pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $category_id ? '&category=' . $category_id : '' ?><?= $sort ? '&sort=' . $sort : '' ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= $category_id ? '&category=' . $category_id : '' ?><?= $sort ? '&sort=' . $sort : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $category_id ? '&category=' . $category_id : '' ?><?= $sort ? '&sort=' . $sort : '' ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
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
