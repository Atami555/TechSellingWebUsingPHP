-- Tạo database
CREATE DATABASE IF NOT EXISTS techstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techstore;

-- Bảng danh mục
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    image VARCHAR(255),
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng sản phẩm
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NULL,
    sku VARCHAR(100) UNIQUE,
    stock_quantity INT DEFAULT 0,
    category_id INT,
    brand VARCHAR(100),
    model VARCHAR(100),
    specifications JSON,
    features TEXT,
    image VARCHAR(255),
    gallery JSON,
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    meta_title VARCHAR(255),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bảng người dùng
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    avatar VARCHAR(255),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    status ENUM('active', 'inactive') DEFAULT 'active',
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng đơn hàng
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_name VARCHAR(100) NOT NULL,
    shipping_phone VARCHAR(20) NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_fee DECIMAL(10,2) DEFAULT 0,
    payment_method ENUM('cod', 'bank_transfer', 'credit_card') DEFAULT 'cod',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    status ENUM('pending', 'confirmed', 'shipping', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Bảng chi tiết đơn hàng
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng đánh giá sản phẩm
CREATE TABLE product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(255),
    comment TEXT,
    images JSON,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);

-- Bảng tin tức
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt VARCHAR(500),
    image VARCHAR(255),
    author_id INT,
    status ENUM('draft', 'published') DEFAULT 'draft',
    featured BOOLEAN DEFAULT FALSE,
    meta_title VARCHAR(255),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Bảng liên hệ
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng cấu hình
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Chèn dữ liệu mẫu cho danh mục
INSERT INTO categories (name, slug, description, icon, sort_order) VALUES
('Điện thoại', 'dien-thoai', 'Điện thoại thông minh và phụ kiện', 'fas fa-mobile-alt', 1),
('Laptop', 'laptop', 'Máy tính xách tay và phụ kiện', 'fas fa-laptop', 2),
('Máy tính bảng', 'may-tinh-bang', 'iPad, Android tablet và phụ kiện', 'fas fa-tablet-alt', 3),
('Phụ kiện', 'phu-kien', 'Tai nghe, sạc, ốp lưng và phụ kiện khác', 'fas fa-headphones', 4),
('Gaming', 'gaming', 'Thiết bị chơi game chuyên nghiệp', 'fas fa-gamepad', 5),
('Đồng hồ thông minh', 'dong-ho-thong-minh', 'Smartwatch và đồng hồ thông minh', 'fas fa-clock', 6),
('Camera', 'camera', 'Máy ảnh và thiết bị quay phim', 'fas fa-camera', 7),
('Âm thanh', 'am-thanh', 'Loa, tai nghe và thiết bị âm thanh', 'fas fa-volume-up', 8);

-- Chèn dữ liệu mẫu cho sản phẩm
INSERT INTO products (name, slug, description, short_description, price, sale_price, sku, stock_quantity, category_id, brand, model, image, featured) VALUES
('iPhone 15 Pro Max', 'iphone-15-pro-max', 'iPhone 15 Pro Max với chip A17 Pro mạnh mẽ, camera 48MP và màn hình Super Retina XDR 6.7 inch. Thiết kế titan cao cấp với khả năng chống nước IP68.', 'iPhone 15 Pro Max - Công nghệ tiên tiến nhất', 29990000, 27990000, 'IP15PM-256', 50, 1, 'Apple', 'iPhone 15 Pro Max', 'assets/images/products/iphone-15-pro-max.jpg', 1),
('Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24-ultra', 'Galaxy S24 Ultra với S Pen, camera 200MP, màn hình Dynamic AMOLED 2X 6.8 inch và chip Snapdragon 8 Gen 3. Thiết kế titan bền bỉ.', 'Galaxy S24 Ultra - S Pen và camera 200MP', 24990000, 22990000, 'SGS24U-512', 30, 1, 'Samsung', 'Galaxy S24 Ultra', 'assets/images/products/galaxy-s24-ultra.jpg', 1),
('MacBook Pro M3', 'macbook-pro-m3', 'MacBook Pro 14 inch với chip M3 mạnh mẽ, màn hình Liquid Retina XDR và thời lượng pin lên đến 18 giờ. Hoàn hảo cho công việc chuyên nghiệp.', 'MacBook Pro M3 - Hiệu suất vượt trội', 45990000, 42990000, 'MBP14-M3-512', 25, 2, 'Apple', 'MacBook Pro M3', 'assets/images/products/macbook-pro-m3.jpg', 1),
('Dell XPS 13', 'dell-xps-13', 'Dell XPS 13 với thiết kế siêu mỏng, màn hình InfinityEdge 13.4 inch và hiệu suất mạnh mẽ. Lựa chọn hoàn hảo cho doanh nhân.', 'Dell XPS 13 - Thiết kế cao cấp', 28990000, 26990000, 'DXPS13-512', 20, 2, 'Dell', 'XPS 13', 'assets/images/products/dell-xps-13.jpg', 1),
('iPad Pro 12.9', 'ipad-pro-12-9', 'iPad Pro 12.9 inch với chip M2, màn hình Liquid Retina XDR và hỗ trợ Apple Pencil. Công cụ sáng tạo mạnh mẽ nhất.', 'iPad Pro 12.9 - Sáng tạo không giới hạn', 21990000, 19990000, 'IPADP12-256', 35, 3, 'Apple', 'iPad Pro 12.9', 'assets/images/products/ipad-pro-12-9.jpg', 1),
('AirPods Pro 2', 'airpods-pro-2', 'AirPods Pro thế hệ 2 với chip H2, chống ồn chủ động và âm thanh không gian. Trải nghiệm âm thanh tuyệt vời.', 'AirPods Pro 2 - Âm thanh không gian', 5990000, 5490000, 'APP2-USB-C', 100, 4, 'Apple', 'AirPods Pro 2', 'assets/images/products/airpods-pro-2.jpg', 1),
('Sony WH-1000XM5', 'sony-wh-1000xm5', 'Tai nghe chống ồn hàng đầu với chip V1, âm thanh chất lượng cao và thời lượng pin 30 giờ. Trải nghiệm âm thanh tuyệt đỉnh.', 'Sony WH-1000XM5 - Chống ồn hàng đầu', 8990000, 7990000, 'SWH1000XM5', 40, 4, 'Sony', 'WH-1000XM5', 'assets/images/products/sony-wh-1000xm5.jpg', 1),
('PlayStation 5', 'playstation-5', 'Console game thế hệ tiếp theo với SSD siêu nhanh, ray tracing và 4K gaming. Trải nghiệm game đỉnh cao.', 'PlayStation 5 - Thế hệ game mới', 12990000, 11990000, 'PS5-STD', 15, 5, 'Sony', 'PlayStation 5', 'assets/images/products/playstation-5.jpg', 1);

-- Chèn dữ liệu mẫu cho người dùng admin
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@techstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Chèn dữ liệu cấu hình
INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'TechStore', 'Tên website'),
('site_description', 'Cửa hàng điện tử hàng đầu Việt Nam', 'Mô tả website'),
('contact_email', 'info@techstore.com', 'Email liên hệ'),
('contact_phone', '0123 456 789', 'Số điện thoại liên hệ'),
('shipping_fee', '30000', 'Phí vận chuyển'),
('free_shipping_threshold', '500000', 'Ngưỡng miễn phí vận chuyển');

-- Tạo trigger để tự động tạo order_number
DELIMITER $$
CREATE TRIGGER generate_order_number
BEFORE INSERT ON orders
FOR EACH ROW
BEGIN
    IF NEW.order_number IS NULL OR NEW.order_number = '' THEN
        SET NEW.order_number = CONCAT('ORD', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(LAST_INSERT_ID() + 1, 4, '0'));
    END IF;
END$$
DELIMITER ;
