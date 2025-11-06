<?php
require_once __DIR__ . '/../../config/database.php';

$pageTitle = 'Sách đã mượn - Thư viện Đại học';
$currentPage = 'my-borrows';

$database = new Database();
$conn = $database->getConnection();

// Lấy danh sách sách đã mượn
$query = "SELECT b.*, bk.title, bk.author, bk.isbn, bk.cover_image, bk.location
          FROM borrows b
          JOIN books bk ON b.book_id = bk.id
          WHERE b.user_id = :user_id
          ORDER BY b.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$borrows = $stmt->fetchAll();

// Thống kê
$stats = [
    'borrowing' => 0,
    'returned' => 0,
    'overdue' => 0,
    'total_fine' => 0
];

foreach ($borrows as $borrow) {
    if ($borrow['status'] === 'borrowed') $stats['borrowing']++;
    if ($borrow['status'] === 'returned') $stats['returned']++;
    if ($borrow['status'] === 'overdue') $stats['overdue']++;
    $stats['total_fine'] += $borrow['fine_amount'];
}

include __DIR__ . '/../layout/header.php';
?>

<div class="container">
    <div class="page-header-user">
        <h1 class="page-title-user">
            <i class="fas fa-book-reader"></i> Sách đã mượn
        </h1>
        <p class="page-subtitle-user">Quản lý và theo dõi sách bạn đã mượn</p>
    </div>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['borrowing']; ?></div>
                <div class="stat-label">Đang mượn</div>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['returned']; ?></div>
                <div class="stat-label">Đã trả</div>
            </div>
        </div>
        
        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['overdue']; ?></div>
                <div class="stat-label">Quá hạn</div>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['total_fine']); ?>đ</div>
                <div class="stat-label">Tổng phí phạt</div>
            </div>
        </div>
    </div>
    
    <?php if (empty($borrows)): ?>
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h3>Bạn chưa mượn sách nào</h3>
            <p>Hãy tìm kiếm và mượn sách yêu thích của bạn</p>
            <a href="index.php?page=search" class="btn btn-primary">
                <i class="fas fa-search"></i> Tìm kiếm sách
            </a>
        </div>
    <?php else: ?>
        <!-- Borrowing Books -->
        <?php 
        $borrowingBooks = array_filter($borrows, function($b) { return $b['status'] === 'borrowed' || $b['status'] === 'overdue'; });
        if (!empty($borrowingBooks)): 
        ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-hand-holding"></i> Đang mượn
                    <span class="badge badge-warning"><?php echo count($borrowingBooks); ?></span>
                </h2>
            </div>
            <div class="card-body">
                <div class="borrow-list">
                    <?php foreach ($borrowingBooks as $borrow): 
                        $dueDate = new DateTime($borrow['due_date']);
                        $today = new DateTime();
                        $daysLeft = $today->diff($dueDate)->days;
                        $isOverdue = $today > $dueDate;
                    ?>
                    <div class="borrow-item <?php echo $isOverdue ? 'overdue' : ''; ?>">
                        <div class="borrow-cover">
                            <?php if ($borrow['cover_image']): ?>
                                <img src="<?php echo $borrow['cover_image']; ?>" alt="<?php echo htmlspecialchars($borrow['title']); ?>">
                            <?php else: ?>
                                <i class="fas fa-book"></i>
                            <?php endif; ?>
                        </div>
                        
                        <div class="borrow-info">
                            <h3 class="borrow-title"><?php echo htmlspecialchars($borrow['title']); ?></h3>
                            <p class="borrow-author">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($borrow['author']); ?>
                            </p>
                            <p class="borrow-isbn">
                                <i class="fas fa-barcode"></i> ISBN: <?php echo $borrow['isbn']; ?>
                            </p>
                            <p class="borrow-location">
                                <i class="fas fa-map-marker-alt"></i> Vị trí: <?php echo htmlspecialchars($borrow['location']); ?>
                            </p>
                        </div>
                        
                        <div class="borrow-dates">
                            <div class="date-item">
                                <i class="fas fa-calendar-check"></i>
                                <div>
                                    <strong>Ngày mượn:</strong>
                                    <span><?php echo formatDate($borrow['borrow_date']); ?></span>
                                </div>
                            </div>
                            <div class="date-item">
                                <i class="fas fa-calendar-times"></i>
                                <div>
                                    <strong>Hạn trả:</strong>
                                    <span><?php echo formatDate($borrow['due_date']); ?></span>
                                </div>
                            </div>
                            <?php if ($isOverdue): ?>
                                <div class="date-item overdue-info">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <div>
                                        <strong>Quá hạn:</strong>
                                        <span><?php echo $daysLeft; ?> ngày</span>
                                    </div>
                                </div>
                                <div class="date-item fine-info">
                                    <i class="fas fa-money-bill"></i>
                                    <div>
                                        <strong>Phí phạt:</strong>
                                        <span><?php echo number_format(calculateFine($borrow['due_date'])); ?>đ</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="date-item countdown">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <strong>Còn lại:</strong>
                                        <span class="countdown-text"><?php echo $daysLeft; ?> ngày</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="borrow-actions">
                            <?php if ($isOverdue): ?>
                                <span class="badge badge-danger">
                                    <i class="fas fa-exclamation-triangle"></i> QUÁ HẠN
                                </span>
                            <?php else: ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> Đang mượn
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Returned Books -->
        <?php 
        $returnedBooks = array_filter($borrows, function($b) { return $b['status'] === 'returned'; });
        if (!empty($returnedBooks)): 
        ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-history"></i> Lịch sử mượn trả
                    <span class="badge badge-success"><?php echo count($returnedBooks); ?></span>
                </h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Sách</th>
                                <th>Tác giả</th>
                                <th>Ngày mượn</th>
                                <th>Hạn trả</th>
                                <th>Ngày trả</th>
                                <th>Phí phạt</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($returnedBooks as $borrow): ?>
                            <tr>
                                <td>#<?php echo $borrow['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($borrow['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($borrow['author']); ?></td>
                                <td><?php echo formatDate($borrow['borrow_date']); ?></td>
                                <td><?php echo formatDate($borrow['due_date']); ?></td>
                                <td><?php echo formatDate($borrow['return_date']); ?></td>
                                <td>
                                    <?php if ($borrow['fine_amount'] > 0): ?>
                                        <span class="text-danger"><?php echo number_format($borrow['fine_amount']); ?>đ</span>
                                    <?php else: ?>
                                        <span class="text-success">0đ</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Đã trả
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
    <?php endif; ?>
</div>

<style>
.page-header-user {
    text-align: center;
    margin-bottom: 2rem;
    color: white;
}

.page-title-user {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.page-subtitle-user {
    font-size: 1.1rem;
    opacity: 0.9;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.empty-state i {
    font-size: 5rem;
    color: var(--primary);
    margin-bottom: 1.5rem;
}

.empty-state h3 {
    font-size: 1.75rem;
    margin-bottom: 0.75rem;
    color: var(--dark);
}

.empty-state p {
    color: var(--gray);
    margin-bottom: 2rem;
}

.borrow-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.borrow-item {
    display: grid;
    grid-template-columns: 120px 1fr auto auto;
    gap: 1.5rem;
    padding: 1.5rem;
    background: var(--light-gray);
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.borrow-item.overdue {
    background: #fee2e2;
    border-left: 4px solid var(--danger);
}

.borrow-item:hover {
    transform: translateX(8px);
    box-shadow: var(--shadow);
}

.borrow-cover {
    width: 120px;
    height: 160px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.borrow-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.borrow-cover i {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-light), var(--primary));
    color: white;
    font-size: 3rem;
}

.borrow-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--dark);
}

.borrow-author,
.borrow-isbn,
.borrow-location {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--gray);
    margin-bottom: 0.25rem;
}

.borrow-author i,
.borrow-isbn i,
.borrow-location i {
    color: var(--primary);
    width: 16px;
}

.borrow-dates {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.date-item {
    display: flex;
    gap: 0.75rem;
    align-items: start;
}

.date-item i {
    color: var(--primary);
    margin-top: 0.25rem;
}

.date-item strong {
    display: block;
    font-size: 0.85rem;
    color: var(--dark);
}

.date-item span {
    display: block;
    font-size: 0.9rem;
    color: var(--gray);
}

.overdue-info i {
    color: var(--danger);
}

.overdue-info span {
    color: var(--danger);
    font-weight: 600;
}

.fine-info i {
    color: var(--warning);
}

.fine-info span {
    color: var(--danger);
    font-weight: 600;
}

.countdown i {
    color: var(--success);
}

.countdown-text {
    color: var(--success);
    font-weight: 600;
}

.borrow-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    justify-content: center;
}

@media (max-width: 968px) {
    .borrow-item {
        grid-template-columns: 1fr;
    }
    
    .borrow-cover {
        width: 100%;
        height: 250px;
    }
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>
