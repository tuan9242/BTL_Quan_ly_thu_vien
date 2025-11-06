<?php
require_once __DIR__ . '/../../config/database.php';

if (!isLibrarian()) {
    redirect('index.php');
}

$pageTitle = 'Bảng điều khiển - Admin';
$currentPage = 'admin';

$database = new Database();
$conn = $database->getConnection();

// Thống kê tổng quan
$stats = [
    'total_books' => 0,
    'total_users' => 0,
    'total_borrows' => 0,
    'overdue_borrows' => 0
];

$query = "SELECT COUNT(*) as total FROM books";
$stats['total_books'] = $conn->query($query)->fetch()['total'];

$query = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
$stats['total_users'] = $conn->query($query)->fetch()['total'];

$query = "SELECT COUNT(*) as total FROM borrows WHERE status = 'borrowed'";
$stats['total_borrows'] = $conn->query($query)->fetch()['total'];

$query = "SELECT COUNT(*) as total FROM borrows WHERE status = 'overdue'";
$stats['overdue_borrows'] = $conn->query($query)->fetch()['total'];

// Sách mượn gần đây
$query = "SELECT b.*, u.full_name, bk.title, bk.isbn
          FROM borrows b
          JOIN users u ON b.user_id = u.id
          JOIN books bk ON b.book_id = bk.id
          ORDER BY b.created_at DESC
          LIMIT 10";
$recentBorrows = $conn->query($query)->fetchAll();

// Sách sắp hết hạn
$query = "SELECT b.*, u.full_name, u.phone, bk.title, bk.isbn
          FROM borrows b
          JOIN users u ON b.user_id = u.id
          JOIN books bk ON b.book_id = bk.id
          WHERE b.status = 'borrowed' AND b.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
          ORDER BY b.due_date ASC
          LIMIT 10";
$upcomingDue = $conn->query($query)->fetchAll();

include __DIR__ . '/../layout/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-main">
        <div class="container">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Bảng điều khiển</h1>
                    <p class="page-subtitle">Tổng quan hệ thống thư viện</p>
                </div>
                <div class="page-actions">
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Làm mới
                    </button>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['total_books']); ?></div>
                        <div class="stat-label">Tổng số sách</div>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                        <div class="stat-label">Người dùng</div>
                    </div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['total_borrows']); ?></div>
                        <div class="stat-label">Đang mượn</div>
                    </div>
                </div>
                
                <div class="stat-card danger">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['overdue_borrows']); ?></div>
                        <div class="stat-label">Quá hạn</div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Borrows -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-clock"></i> Mượn sách gần đây
                    </h2>
                    <a href="index.php?page=admin-borrows" class="btn btn-outline btn-sm">
                        Xem tất cả
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Người mượn</th>
                                    <th>Sách</th>
                                    <th>ISBN</th>
                                    <th>Ngày mượn</th>
                                    <th>Hạn trả</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBorrows as $borrow): ?>
                                <tr>
                                    <td>#<?php echo $borrow['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($borrow['full_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($borrow['title']); ?></td>
                                    <td><code><?php echo $borrow['isbn']; ?></code></td>
                                    <td><?php echo formatDate($borrow['borrow_date']); ?></td>
                                    <td><?php echo formatDate($borrow['due_date']); ?></td>
                                    <td>
                                        <?php if ($borrow['status'] === 'borrowed'): ?>
                                            <span class="badge badge-warning">Đang mượn</span>
                                        <?php elseif ($borrow['status'] === 'returned'): ?>
                                            <span class="badge badge-success">Đã trả</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Quá hạn</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Upcoming Due Books -->
            <?php if (!empty($upcomingDue)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-bell"></i> Sách sắp đến hạn (7 ngày tới)
                    </h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Người mượn</th>
                                    <th>Liên hệ</th>
                                    <th>Sách</th>
                                    <th>Hạn trả</th>
                                    <th>Còn lại</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingDue as $borrow): 
                                    $daysLeft = (new DateTime($borrow['due_date']))->diff(new DateTime())->days;
                                ?>
                                <tr>
                                    <td>#<?php echo $borrow['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($borrow['full_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($borrow['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($borrow['title']); ?></td>
                                    <td><?php echo formatDate($borrow['due_date']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $daysLeft <= 2 ? 'badge-danger' : 'badge-warning'; ?>">
                                            <?php echo $daysLeft; ?> ngày
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">
                                            <i class="fas fa-bell"></i> Nhắc nhở
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

 

<?php include __DIR__ . '/../layout/footer.php'; ?>
