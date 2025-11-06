# Hướng dẫn sử dụng Hệ thống Quản lý Thư viện Đại học

## 🚀 Cài đặt và Khởi chạy

### 1. Yêu cầu hệ thống
- **PHP**: 7.4 trở lên
- **MySQL**: 5.7 trở lên
- **Web Server**: Apache/Nginx với mod_rewrite
- **XAMPP/WAMP/LAMP**: Để phát triển local

### 2. Cài đặt
```bash
# 1. Clone hoặc copy project vào thư mục web server
# 2. Import database
mysql -u root -p < database.sql

# 3. Cấu hình database trong config/database.php
# 4. Đảm bảo mod_rewrite được bật
# 5. Truy cập: http://localhost/library-management/public/
```

### 3. Tài khoản demo
- **Admin**: admin / admin123
- **Thủ thư**: librarian / admin123  
- **Sinh viên**: student1 / admin123

## 📚 Chức năng chính

### 👤 Dành cho Sinh viên

#### 1. Đăng ký tài khoản
- Truy cập: `index.php?page=register`
- Điền đầy đủ thông tin: username, email, mật khẩu, họ tên, mã sinh viên
- Hệ thống sẽ tự động tạo tài khoản với quyền sinh viên

#### 2. Tìm kiếm sách
- Truy cập: `index.php?page=search`
- Tìm kiếm theo: tên sách, tác giả, ISBN
- Lọc theo danh mục
- Xem kết quả với phân trang

#### 3. Xem chi tiết sách
- Click vào "Chi tiết" trên bất kỳ cuốn sách nào
- Xem thông tin đầy đủ: mô tả, vị trí, số lượng
- Xem sách liên quan cùng danh mục

#### 4. Mượn sách
- Đăng nhập vào hệ thống
- Vào trang chi tiết sách
- Click "Mượn sách" (nếu có sẵn)
- Hệ thống tự động tính hạn trả (30 ngày)

#### 5. Quản lý sách đã mượn
- Truy cập: `index.php?page=my-borrows`
- Xem sách đang mượn, quá hạn, đã trả
- Trả sách trực tuyến
- Xem phí phạt (nếu có)

### 👨‍💼 Dành cho Admin/Thủ thư

#### 1. Bảng điều khiển
- Truy cập: `index.php?page=admin-dashboard`
- Xem thống kê tổng quan: số sách, người dùng, lượt mượn
- Xem sách sắp hết hạn
- Xem hoạt động gần đây

#### 2. Quản lý sách
- Truy cập: `index.php?page=admin-books`
- Thêm/sửa/xóa sách
- Upload ảnh bìa
- Quản lý số lượng, vị trí

#### 3. Quản lý người dùng
- Truy cập: `index.php?page=admin-users`
- Xem danh sách người dùng
- Thay đổi trạng thái tài khoản
- Xem lịch sử hoạt động

#### 4. Quản lý mượn sách
- Truy cập: `index.php?page=admin-borrows`
- Xem tất cả lượt mượn
- Xử lý trả sách
- Quản lý phí phạt

## 🔧 Tính năng kỹ thuật

### 1. Bảo mật
- Mã hóa mật khẩu với `password_hash()`
- XSS protection với `htmlspecialchars()`
- SQL injection protection với PDO prepared statements
- Session management an toàn

### 2. Responsive Design
- Tương thích mobile, tablet, desktop
- CSS Grid và Flexbox
- Mobile-first approach

### 3. AJAX Features
- Mượn sách không reload trang
- Tìm kiếm real-time
- Thông báo động

### 4. Database
- Cấu trúc chuẩn hóa
- Indexes tối ưu
- Foreign key constraints
- Triggers cho tự động cập nhật

## 📱 Sử dụng trên Mobile

### 1. Navigation
- Menu hamburger trên mobile
- Touch-friendly buttons
- Swipe gestures

### 2. Search
- Search bar responsive
- Auto-complete suggestions
- Voice search support (nếu có)

### 3. Book Management
- Card layout tối ưu cho mobile
- Quick actions
- Pull-to-refresh

## 🎨 Customization

### 1. Themes
- CSS Variables trong `:root`
- Dễ dàng thay đổi màu sắc
- Dark mode support (có thể thêm)

### 2. Layout
- Modular structure
- Reusable components
- Flexible grid system

### 3. Content
- Multi-language support ready
- Configurable settings
- Dynamic content loading

## 🐛 Troubleshooting

### 1. CSS không load
```bash
# Kiểm tra đường dẫn
ls -la public/css/style.css

# Kiểm tra permissions
chmod 644 public/css/style.css
```

### 2. JavaScript không hoạt động
```bash
# Kiểm tra console errors
# Kiểm tra file path
# Kiểm tra syntax errors
```

### 3. Database connection
```php
# Kiểm tra config/database.php
# Test connection
$database = new Database();
$conn = $database->getConnection();
if ($conn) echo "Connected!";
```

### 4. URL Rewriting
```apache
# Kiểm tra .htaccess
# Đảm bảo mod_rewrite enabled
# Kiểm tra AllowOverride All
```

## 📊 Performance Tips

### 1. Database
- Sử dụng indexes
- Optimize queries
- Connection pooling

### 2. Frontend
- Minify CSS/JS
- Compress images
- Use CDN

### 3. Caching
- Browser caching
- Server-side caching
- Database query caching

## 🔄 Updates và Maintenance

### 1. Regular Tasks
- Backup database
- Update dependencies
- Monitor logs

### 2. Security Updates
- Update PHP version
- Update libraries
- Security patches

### 3. Feature Updates
- Version control
- Testing
- Rollback plan

## 📞 Support

### 1. Documentation
- Code comments
- API documentation
- User manuals

### 2. Community
- GitHub issues
- Discussion forums
- User groups

### 3. Professional Support
- Custom development
- Training
- Consulting

---

**Lưu ý**: Hệ thống được thiết kế để dễ sử dụng và bảo trì. Nếu gặp vấn đề, hãy kiểm tra logs và documentation trước khi liên hệ support.
