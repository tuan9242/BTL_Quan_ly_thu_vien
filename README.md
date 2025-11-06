# Hệ thống Quản lý Thư viện Đại học

## Vấn đề đã được sửa

### 1. CSS và JavaScript không load được
**Nguyên nhân:** Đường dẫn CSS và JavaScript không đúng
**Giải pháp:** 
- Sửa đường dẫn từ `/public/css/style.css` thành `css/style.css`
- Sửa đường dẫn từ `/public/js/script.js` thành `js/script.js`
- Sửa tất cả đường dẫn `/public/index.php` thành `index.php`

### 2. File footer.php bị duplicate content
**Nguyên nhân:** Nội dung bị lặp lại trong file
**Giải pháp:** Tạo lại file footer.php với nội dung đúng

### 3. Cấu hình URL rewriting
**Giải pháp:** Tạo file `.htaccess` để cấu hình URL rewriting và cache

## Cách sử dụng

### 1. Truy cập trang web
- Mở trình duyệt và truy cập: `http://localhost/library-management/public/`
- Hoặc: `http://localhost/library-management/public/index.php`

### 2. Test CSS và JavaScript
- Truy cập: `http://localhost/library-management/public/test.html`
- Kiểm tra xem CSS có load đúng không
- Click vào nút "Test JavaScript" để kiểm tra JavaScript

### 3. Cấu trúc thư mục
```
library-management/
├── public/                 # Thư mục chứa file chính
│   ├── index.php          # File chính
│   ├── css/               # CSS files
│   ├── js/                # JavaScript files
│   └── test.html          # File test
├── views/                 # Templates
├── controllers/           # Controllers
├── models/               # Models
├── config/               # Configuration
└── .htaccess             # URL rewriting
```

## Tính năng chính

- ✅ **Đăng nhập/Đăng ký** - Hệ thống xác thực an toàn
- ✅ **Tìm kiếm sách** - Tìm kiếm thông minh với bộ lọc
- ✅ **Chi tiết sách** - Thông tin đầy đủ và sách liên quan
- ✅ **Mượn/Trả sách** - Quản lý mượn trả tự động
- ✅ **Quản lý sách** - CRUD operations cho admin
- ✅ **Quản lý người dùng** - Quản lý tài khoản và quyền
- ✅ **Dashboard** - Thống kê và báo cáo
- ✅ **Responsive design** - Tương thích mọi thiết bị
- ✅ **Mobile-friendly** - Tối ưu cho mobile
- ✅ **AJAX** - Tương tác mượt mà
- ✅ **Security** - Bảo mật cao với PDO và password hashing

## Yêu cầu hệ thống

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx với mod_rewrite
- XAMPP/WAMP/LAMP

## Cài đặt

1. **Copy project vào thư mục web server**
   ```bash
   cp -r library-management /var/www/html/
   ```

2. **Import database**
   ```bash
   mysql -u root -p < database.sql
   ```

3. **Cấu hình database** trong `config/database.php`
   ```php
   private $host = "localhost";
   private $db_name = "library_management";
   private $username = "root";
   private $password = "your_password";
   ```

4. **Cấu hình web server**
   - Đảm bảo mod_rewrite được bật
   - Cấu hình DocumentRoot trỏ đến thư mục `public/`

5. **Truy cập trang web**
   ```
   http://localhost/library-management/public/
   ```

## Tài khoản demo

- **Admin**: admin / admin123
- **Thủ thư**: librarian / admin123
- **Sinh viên**: student1 / admin123

## Cấu trúc dự án

```
library-management/
├── public/                 # Thư mục chứa file chính
│   ├── index.php          # File chính (entry point)
│   ├── css/               # CSS files
│   ├── js/                # JavaScript files
│   └── test.html          # File test
├── views/                 # Templates
│   ├── auth/              # Đăng nhập/đăng ký
│   ├── user/              # Giao diện người dùng
│   ├── admin/             # Giao diện admin
│   └── layout/            # Layout chung
├── controllers/           # Controllers
├── models/               # Models (Business Logic)
├── config/               # Configuration
├── assets/               # Static assets
├── database.sql          # Database schema
├── .htaccess             # URL rewriting
├── README.md             # Hướng dẫn này
└── USER_GUIDE.md         # Hướng dẫn chi tiết
```

## API Endpoints

### User Routes
- `GET /?page=home` - Trang chủ
- `GET /?page=search` - Tìm kiếm sách
- `GET /?page=book-detail&id={id}` - Chi tiết sách
- `GET /?page=my-borrows` - Sách đã mượn
- `POST /?page=borrow-book` - Mượn sách (AJAX)

### Admin Routes
- `GET /?page=admin-dashboard` - Dashboard
- `GET /?page=admin-books` - Quản lý sách
- `GET /?page=admin-users` - Quản lý người dùng
- `GET /?page=admin-borrows` - Quản lý mượn sách

### Auth Routes
- `GET /?page=login` - Đăng nhập
- `GET /?page=register` - Đăng ký
- `GET /?action=logout` - Đăng xuất

## Lưu ý

- **Bảo mật**: Đảm bảo mod_rewrite được bật và cấu hình đúng
- **Database**: Kiểm tra kết nối và quyền truy cập
- **Permissions**: Cấu hình quyền đọc/ghi cho thư mục
- **PHP**: Phiên bản 7.4+ với các extension cần thiết
- **MySQL**: Phiên bản 5.7+ với InnoDB engine

## Hỗ trợ

- 📖 **Tài liệu**: Xem `USER_GUIDE.md` để biết thêm chi tiết
- 🐛 **Báo lỗi**: Tạo issue trên GitHub
- 💬 **Thảo luận**: Sử dụng discussion forum
- 📧 **Liên hệ**: Email support cho các vấn đề nghiêm trọng
