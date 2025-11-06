<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Book.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Borrow.php';

if (!isLibrarian()) {
    redirect('index.php');
}

$pageTitle = 'Báo cáo thống kê - Admin';
$currentPage = 'admin';
$page = 'admin-reports';

$bookModel = new Book();
$userModel = new User();
$borrowModel = new Borrow();

// Lấy thống kê tổng quan
$bookStats = $bookModel->getTotalCount();
$userStats = $userModel->getStatistics();
$borrowStats = $borrowModel->getStatistics();

// Lấy sách phổ biến
$popularBooks = $bookModel->getPopular(10);

// Lấy sách quá hạn
$overdueBooks = $borrowModel->getOverdueBooks();

// Lấy thống kê theo tháng
$database = new Database();
$conn = $database->getConnection();

$monthlyStats = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i month"));
    $query = "SELECT COUNT(*) as count FROM borrows WHERE DATE_FORMAT(created_at, '%Y-%m') = :month";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':month', $month);
    $stmt->execute();
    $monthlyStats[] = [
        'month' => $month,
        'count' => $stmt->fetch()['count']
    ];
}

include __DIR__ . '/../layout/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-main">
        <div class="container">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Báo cáo thống kê</h1>
                    <p class="page-subtitle">Thống kê và báo cáo hệ thống thư viện</p>
                </div>
                <div class="page-actions">
                    <button class="btn btn-primary" onclick="printReport()">
                        <i class="fas fa-print"></i> In báo cáo
                    </button>
                    <button class="btn btn-outline" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> Xuất PDF
                    </button>
                </div>
            </div>
            
            <!-- Summary Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($bookStats); ?></div>
                        <div class="stat-label">Tổng số sách</div>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($userStats['total']); ?></div>
                        <div class="stat-label">Tổng người dùng</div>
                    </div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($borrowStats['total']); ?></div>
                        <div class="stat-label">Tổng lượt mượn</div>
                    </div>
                </div>
                
                <div class="stat-card danger">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($borrowStats['overdue']); ?></div>
                        <div class="stat-label">Sách quá hạn</div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="charts-section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chart-line"></i> Thống kê mượn sách theo tháng
                        </h2>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" width="400" height="200"></canvas>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chart-pie"></i> Phân bố trạng thái mượn sách
                        </h2>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Popular Books -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-fire"></i> Sách được mượn nhiều nhất
                    </h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên sách</th>
                                    <th>Tác giả</th>
                                    <th>Danh mục</th>
                                    <th>Số lượt mượn</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($popularBooks as $index => $book): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($book['title']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td><?php echo htmlspecialchars($book['category_name']); ?></td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?php echo $book['borrow_count']; ?> lượt
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($book['available_quantity'] > 0): ?>
                                            <span class="badge badge-success">Có sẵn</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Hết sách</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Overdue Books -->
            <?php if (!empty($overdueBooks)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i> Sách quá hạn trả
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
                                    <th>Ngày mượn</th>
                                    <th>Hạn trả</th>
                                    <th>Quá hạn</th>
                                    <th>Phí phạt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overdueBooks as $borrow): 
                                    $daysOverdue = (time() - strtotime($borrow['due_date'])) / (60 * 60 * 24);
                                    $fineAmount = calculateFine($borrow['due_date']);
                                ?>
                                <tr>
                                    <td>#<?php echo $borrow['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($borrow['full_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($borrow['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($borrow['title']); ?></td>
                                    <td><?php echo formatDate($borrow['borrow_date']); ?></td>
                                    <td><?php echo formatDate($borrow['due_date']); ?></td>
                                    <td>
                                        <span class="badge badge-danger">
                                            <?php echo floor($daysOverdue); ?> ngày
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fine-amount">
                                            <?php echo number_format($fineAmount); ?> VNĐ
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- User Statistics -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-users"></i> Thống kê người dùng
                    </h2>
                </div>
                <div class="card-body">
                    <div class="user-stats-grid">
                        <div class="user-stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo $userStats['students']; ?></div>
                                <div class="stat-label">Sinh viên</div>
                            </div>
                        </div>
                        
                        <div class="user-stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo $userStats['librarians']; ?></div>
                                <div class="stat-label">Thủ thư</div>
                            </div>
                        </div>
                        
                        <div class="user-stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo $userStats['admins']; ?></div>
                                <div class="stat-label">Quản trị viên</div>
                            </div>
                        </div>
                        
                        <div class="user-stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo $userStats['active']; ?></div>
                                <div class="stat-label">Tài khoản hoạt động</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
 

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlyStats, 'month')); ?>,
        datasets: [{
            label: 'Số lượt mượn',
            data: <?php echo json_encode(array_column($monthlyStats, 'count')); ?>,
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Thống kê mượn sách theo tháng'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Đang mượn', 'Đã trả', 'Quá hạn'],
        datasets: [{
            data: [
                <?php echo $borrowStats['borrowed']; ?>,
                <?php echo $borrowStats['returned']; ?>,
                <?php echo $borrowStats['overdue']; ?>
            ],
            backgroundColor: [
                'rgb(245, 158, 11)',
                'rgb(16, 185, 129)',
                'rgb(239, 68, 68)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Phân bố trạng thái mượn sách'
            },
            legend: {
                position: 'bottom'
            }
        }
    }
});

function printReport() {
    window.print();
}

function exportToPDF() {
    // Sử dụng thư viện jsPDF để xuất PDF
    alert('Tính năng xuất PDF sẽ được phát triển trong phiên bản tiếp theo!');
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
