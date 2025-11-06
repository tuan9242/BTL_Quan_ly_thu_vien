<?php
require_once __DIR__ . '/../config/database.php';

// Simple MVC-style dispatching for controller/action endpoints
if (isset($_GET['controller']) && isset($_GET['action'])) {
    $controller = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['controller']);
    $action = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['action']);

    $controllerFile = __DIR__ . '/../controllers/' . ucfirst($controller) . 'Controller.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $className = ucfirst($controller) . 'Controller';
        if (class_exists($className)) {
            $instance = new $className();
            if (method_exists($instance, $action)) {
                // JSON endpoints shouldn't output BOM/HTML
                call_user_func([$instance, $action]);
                exit;
            }
        }
    }
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    exit;
}

// Xử lý logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    redirect('index.php?page=login');
}

// Routing
$page = $_GET['page'] ?? 'home';

// Kiểm tra quyền truy cập các trang admin theo role
if (strpos($page, 'admin-') === 0) {
    // Các trang chỉ dành cho admin
    $adminOnlyPages = ['admin-users', 'admin-reports'];
    if (in_array($page, $adminOnlyPages)) {
        if (!isAdmin()) {
            redirect('index.php');
        }
    } else {
        // Các trang admin còn lại yêu cầu tối thiểu thủ thư (librarian)
        if (!isLibrarian()) {
            redirect('index.php');
        }
    }
}

// Kiểm tra đăng nhập cho các trang yêu cầu
$authRequiredPages = ['search', 'my-borrows', 'book-detail', 'profile', 'change-password', 'cart'];
if (in_array($page, $authRequiredPages) && !isLoggedIn()) {
    $_SESSION['alert'] = alert('Vui lòng đăng nhập để tiếp tục!', 'warning');
    redirect('index.php?page=login');
}

// Load trang tương ứng
switch ($page) {
    case 'login':
        include __DIR__ . '/../views/auth/login.php';
        break;
        
    case 'register':
        include __DIR__ . '/../views/auth/register.php';
        break;
        
    case 'forgot-password':
        include __DIR__ . '/../views/auth/forgot-password.php';
        break;
        
    case 'search':
        include __DIR__ . '/../views/user/search.php';
        break;
        
    case 'my-borrows':
        include __DIR__ . '/../views/user/my-borrows.php';
        break;
        
    case 'book-detail':
        include __DIR__ . '/../views/user/book-detail.php';
        break;
        
    case 'profile':
        include __DIR__ . '/../views/user/profile.php';
        break;
        
    case 'change-password':
        include __DIR__ . '/../views/user/change-password.php';
        break;
        
    case 'admin-dashboard':
        include __DIR__ . '/../views/admin/dashboard.php';
        break;
        
    case 'admin-books':
        include __DIR__ . '/../views/admin/books.php';
        break;
        
    case 'admin-borrows':
        include __DIR__ . '/../views/admin/borrows.php';
        break;
        
    case 'admin-users':
        include __DIR__ . '/../views/admin/users.php';
        break;
        
    case 'admin-categories':
        include __DIR__ . '/../views/admin/categories.php';
        break;
        
    case 'admin-reports':
        include __DIR__ . '/../views/admin/reports.php';
        break;
        
    case 'cart':
        include __DIR__ . '/../views/user/cart.php';
        break;
        
    case 'api-notifications':
        // API thông báo
        if (!isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }
        
        require_once __DIR__ . '/../models/Notification.php';
        $notificationModel = new Notification();
        $action = $_GET['action'] ?? '';
        
        if ($action === 'get') {
            $notifications = $notificationModel->getByUser($_SESSION['user_id'], 10, false);
            // Format time ago
            foreach ($notifications as &$notif) {
                $created = new DateTime($notif['created_at']);
                $now = new DateTime();
                $diff = $now->diff($created);
                
                if ($diff->days > 0) {
                    $notif['time_ago'] = $diff->days . ' ngày trước';
                } elseif ($diff->h > 0) {
                    $notif['time_ago'] = $diff->h . ' giờ trước';
                } elseif ($diff->i > 0) {
                    $notif['time_ago'] = $diff->i . ' phút trước';
                } else {
                    $notif['time_ago'] = 'Vừa xong';
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'notifications' => $notifications]);
            exit;
        } elseif ($action === 'mark-read') {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $result = $notificationModel->markAsRead($id, $_SESSION['user_id']);
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit;
        } elseif ($action === 'mark-all-read') {
            $result = $notificationModel->markAllAsRead($_SESSION['user_id']);
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit;
        }
        break;
        
    case 'api-cart':
        // API giỏ hàng
        if (!isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }
        
        require_once __DIR__ . '/../models/Cart.php';
        $cartModel = new Cart();
        $action = $_GET['action'] ?? '';
        
        if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $bookId = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
            $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
            $durationDays = isset($_POST['duration_days']) ? max(1, (int)$_POST['duration_days']) : 30;
            $result = $cartModel->add($_SESSION['user_id'], $bookId, $quantity, $durationDays);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
        break;
        
    case 'borrow-book':
        // Xử lý mượn sách qua AJAX
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
            require_once __DIR__ . '/../models/Borrow.php';
            $borrowModel = new Borrow();
            $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
            $durationDays = isset($_POST['duration_days']) ? max(1, (int)$_POST['duration_days']) : 30;
            $pricePerDay = isset($_POST['price_per_day']) ? max(0, (int)$_POST['price_per_day']) : 0; // optional
            $result = $borrowModel->create($_SESSION['user_id'], (int)$_POST['book_id'], $quantity, $durationDays, $pricePerDay);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
        break;
        
    case 'approve-borrow':
        // Duyệt yêu cầu mượn sách (thủ thư)
        if (!isLibrarian()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không có quyền']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_id'])) {
            require_once __DIR__ . '/../models/Borrow.php';
            $borrowModel = new Borrow();
            $result = $borrowModel->approve((int)$_POST['borrow_id']);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
        break;
        
    case 'reject-borrow':
        // Từ chối yêu cầu mượn sách (thủ thư)
        if (!isLibrarian()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không có quyền']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_id'])) {
            require_once __DIR__ . '/../models/Borrow.php';
            $borrowModel = new Borrow();
            $reason = isset($_POST['reason']) ? sanitize($_POST['reason']) : '';
            $result = $borrowModel->reject((int)$_POST['borrow_id'], $reason);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
        break;
        
    case 'home':
    default:
        include __DIR__ . '/../views/user/home.php';
        break;
}
?>