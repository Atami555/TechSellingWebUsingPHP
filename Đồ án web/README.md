# TechStore - Website Bán Đồ Điện Tử

Website bán đồ điện tử được xây dựng bằng PHP với giao diện hiện đại và responsive.

## Tính năng chính

### 🛍️ E-commerce
- **Trang chủ**: Hiển thị sản phẩm nổi bật, danh mục và thông tin cửa hàng
- **Danh mục sản phẩm**: Phân loại sản phẩm theo danh mục với bộ lọc và sắp xếp
- **Chi tiết sản phẩm**: Thông tin đầy đủ, hình ảnh, đánh giá và sản phẩm liên quan
- **Giỏ hàng**: Thêm/xóa/cập nhật sản phẩm với AJAX
- **Tìm kiếm**: Tìm kiếm sản phẩm theo tên và mô tả

### 👤 Quản lý người dùng
- **Đăng ký/Đăng nhập**: Hệ thống xác thực an toàn
- **Hồ sơ người dùng**: Quản lý thông tin cá nhân
- **Lịch sử đơn hàng**: Theo dõi trạng thái đơn hàng

### 🎨 Giao diện
- **Responsive Design**: Tương thích với mọi thiết bị
- **Modern UI**: Sử dụng Bootstrap 5 và Font Awesome
- **Animations**: Hiệu ứng mượt mà và chuyên nghiệp
- **Dark/Light Theme**: Giao diện tối và sáng

## Công nghệ sử dụng

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.3
- **Icons**: Font Awesome 6.0
- **AJAX**: Fetch API

## Cài đặt

### Yêu cầu hệ thống
- PHP 7.4 hoặc cao hơn
- MySQL 5.7 hoặc cao hơn
- Web server (Apache/Nginx)
- Composer (tùy chọn)

### Bước 1: Clone repository
```bash
git clone https://github.com/your-username/techstore.git
cd techstore
```

### Bước 2: Cấu hình database
1. Tạo database mới:
```sql
CREATE DATABASE techstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import schema:
```bash
mysql -u username -p techstore < database/schema.sql
```

3. Cập nhật cấu hình database trong `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'techstore');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Bước 3: Cấu hình web server

#### Apache
Tạo file `.htaccess` trong thư mục gốc:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/techstore;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Bước 4: Cấu hình quyền file
```bash
chmod 755 assets/
chmod 644 assets/css/*
chmod 644 assets/js/*
chmod 644 assets/images/*
```

## Cấu trúc thư mục

```
techstore/
├── assets/                 # Tài nguyên tĩnh
│   ├── css/               # Stylesheet
│   ├── js/                # JavaScript
│   └── images/            # Hình ảnh
├── ajax/                  # API endpoints
├── config/                # Cấu hình
├── database/              # Schema và migration
├── includes/              # Functions và utilities
├── index.php              # Trang chủ
├── products.php           # Danh sách sản phẩm
├── product.php            # Chi tiết sản phẩm
├── cart.php               # Giỏ hàng
├── login.php              # Đăng nhập
├── register.php           # Đăng ký
├── search.php             # Tìm kiếm
└── README.md              # Tài liệu
```

## Sử dụng

### Quản lý sản phẩm
1. Truy cập admin panel (cần đăng nhập với tài khoản admin)
2. Thêm/sửa/xóa sản phẩm
3. Quản lý danh mục
4. Xem báo cáo bán hàng

### Quản lý đơn hàng
1. Xem danh sách đơn hàng
2. Cập nhật trạng thái đơn hàng
3. In hóa đơn
4. Quản lý kho

### Tùy chỉnh giao diện
1. Chỉnh sửa CSS trong `assets/css/style.css`
2. Thêm JavaScript trong `assets/js/main.js`
3. Thay đổi hình ảnh trong `assets/images/`

## API Endpoints

### Giỏ hàng
- `POST /ajax/add_to_cart.php` - Thêm sản phẩm vào giỏ hàng
- `POST /ajax/update_cart.php` - Cập nhật số lượng
- `POST /ajax/remove_from_cart.php` - Xóa sản phẩm
- `GET /ajax/get_cart_count.php` - Lấy số lượng sản phẩm
- `GET /ajax/get_cart_total.php` - Lấy tổng tiền

### Tìm kiếm
- `GET /ajax/search_suggestions.php` - Gợi ý tìm kiếm

## Bảo mật

- **SQL Injection**: Sử dụng prepared statements
- **XSS Protection**: Escape output và validate input
- **CSRF Protection**: Token validation
- **Password Security**: Hash với bcrypt
- **Session Security**: Secure session configuration

## Tối ưu hóa

### Performance
- **Caching**: Redis/Memcached cho session
- **CDN**: Sử dụng CDN cho static assets
- **Compression**: Gzip compression
- **Image Optimization**: WebP format và lazy loading

### SEO
- **Meta Tags**: Dynamic meta tags
- **URL Structure**: Clean URLs
- **Sitemap**: XML sitemap
- **Schema Markup**: Structured data

## Troubleshooting

### Lỗi thường gặp

1. **Database connection failed**
   - Kiểm tra thông tin kết nối trong `config/database.php`
   - Đảm bảo MySQL service đang chạy

2. **Permission denied**
   - Kiểm tra quyền file và thư mục
   - Đảm bảo web server có quyền đọc file

3. **AJAX không hoạt động**
   - Kiểm tra console browser
   - Đảm bảo đường dẫn AJAX đúng

4. **Images không hiển thị**
   - Kiểm tra đường dẫn hình ảnh
   - Đảm bảo file tồn tại trong thư mục `assets/images/`

## Đóng góp

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Mở Pull Request

## License

Distributed under the MIT License. See `LICENSE` for more information.

## Liên hệ

- **Email**: info@techstore.com
- **Phone**: 0123 456 789
- **Address**: 123 Đường ABC, Quận 1, TP.HCM

## Changelog

### v1.0.0 (2024-01-01)
- Initial release
- Basic e-commerce functionality
- User authentication
- Shopping cart
- Product management
- Responsive design

---

**TechStore** - Cửa hàng điện tử hàng đầu Việt Nam 🚀
