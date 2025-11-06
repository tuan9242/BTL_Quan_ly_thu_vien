<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Book.php';

$pageTitle = 'Trang chủ - Thư viện Đại học';
$currentPage = 'home';

$bookModel = new Book();
$popularBooks = $bookModel->getPopular(6);
$recentBooks = $bookModel->getAll(8);

include __DIR__ . '/../layout/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Chào mừng đến Thư viện Đại học</h1>
            <p class="hero-subtitle">Khám phá hàng ngàn đầu sách phong phú từ mọi lĩnh vực</p>
            
            <?php if (!isLoggedIn()): ?>
                <div class="hero-actions">
                    <a href="index.php?page=login" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập ngay
                    </a>
                    <a href="index.php?page=register" class="btn btn-outline btn-lg">
                        <i class="fas fa-user-plus"></i> Đăng ký tài khoản
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>


<!-- Popular Books Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <i class="fas fa-fire"></i> Sách phổ biến
                </h2>
                <p class="section-subtitle">Những cuốn sách được mượn nhiều nhất</p>
            </div>
            <a href="index.php?page=search" class="btn btn-outline">
                Xem tất cả <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="books-grid">
            <?php foreach ($popularBooks as $book): ?>
                <div class="book-card">
                    <div class="book-cover">
                        <?php if (!empty($book['cover_image'])): ?>
                            <img src="<?php echo $book['cover_image']; ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                        <?php else: ?>
                            <img src="uploads/defaults/default-cover.svg" alt="<?php echo htmlspecialchars($book['title']); ?>">
                        <?php endif; ?>
                        
                        <?php if ($book['available_quantity'] > 0): ?>
                            <div class="book-status available">
                                <i class="fas fa-check-circle"></i> Có sẵn
                            </div>
                        <?php else: ?>
                            <div class="book-status unavailable">
                                <i class="fas fa-times-circle"></i> Hết sách
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="book-info">
                        <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="book-author">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($book['author']); ?>
                        </p>
                        
                        <div class="book-meta">
                            <div class="meta-item">
                                <i class="fas fa-tag"></i>
                                <span><?php echo htmlspecialchars($book['category_name']); ?></span>
                            </div>
                            <?php if (isset($book['published_year'])): ?>
                            <div class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo $book['published_year']; ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($book['location'])): ?>
                            <div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($book['location']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($book['description']): ?>
                            <p class="book-description">
                                <?php echo htmlspecialchars(substr($book['description'], 0, 100)) . '...'; ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (isLoggedIn()): ?>
                            <div class="book-actions">
                                <a href="index.php?page=book-detail&id=<?php echo $book['id']; ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-info-circle"></i> Chi tiết
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Recent Books Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <i class="fas fa-clock"></i> Sách mới cập nhật
                </h2>
                <p class="section-subtitle">Những cuốn sách mới được thêm vào thư viện</p>
            </div>
        </div>
        
        <div class="books-grid">
            <?php foreach ($recentBooks as $book): ?>
                <div class="book-card">
                    <div class="book-cover">
                        <?php if (!empty($book['cover_image'])): ?>
                            <img src="<?php echo $book['cover_image']; ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                        <?php else: ?>
                            <img src="uploads/defaults/default-cover.svg" alt="<?php echo htmlspecialchars($book['title']); ?>">
                        <?php endif; ?>
                        
                        <?php if ($book['available_quantity'] > 0): ?>
                            <div class="book-status available">
                                <i class="fas fa-check-circle"></i> Có sẵn
                            </div>
                        <?php else: ?>
                            <div class="book-status unavailable">
                                <i class="fas fa-times-circle"></i> Hết sách
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="book-info">
                        <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="book-author">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($book['author']); ?>
                        </p>
                        
                        <div class="book-meta">
                            <div class="meta-item">
                                <i class="fas fa-tag"></i>
                                <span><?php echo htmlspecialchars($book['category_name'] ?? 'N/A'); ?></span>
                            </div>
                            <?php if (isset($book['published_year'])): ?>
                            <div class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo $book['published_year']; ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($book['location'])): ?>
                            <div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($book['location']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($book['description']): ?>
                            <p class="book-description">
                                <?php echo htmlspecialchars(substr($book['description'], 0, 100)) . '...'; ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (isLoggedIn()): ?>
                            <div class="book-actions">
                                <a href="index.php?page=book-detail&id=<?php echo $book['id']; ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-info-circle"></i> Chi tiết
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section features-section">
    <div class="container">
        <h2 class="section-title text-center mb-2">Tính năng nổi bật</h2>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Tìm kiếm thông minh</h3>
                <p>Tìm kiếm sách nhanh chóng theo tên, tác giả, ISBN hoặc danh mục</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-book-reader"></i>
                </div>
                <h3>Mượn sách dễ dàng</h3>
                <p>Đặt mượn sách trực tuyến và theo dõi lịch sử mượn trả</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3>Thông báo tự động</h3>
                <p>Nhận thông báo về hạn trả sách và sách mới</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Truy cập mọi lúc</h3>
                <p>Sử dụng trên mọi thiết bị: máy tính, tablet, điện thoại</p>
            </div>
        </div>
    </div>
</section>

<style>
.hero {
    background: linear-gradient(135deg, var(--primary-blue-dark) 0%, var(--primary-blue) 100%);
    padding: 4rem 0;
    color: white;
    margin-bottom: 0;
    position: relative;
    overflow: hidden;
}

.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') bottom/cover no-repeat;
    opacity: 0.3;
}

.hero-content {
    text-align: center;
    max-width: 900px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    color: white;
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 1.5rem;
    margin-bottom: 2.5rem;
    opacity: 0.95;
    font-weight: 400;
}

.hero-search {
    max-width: 700px;
    margin: 0 auto;
}

.search-wrapper {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 50px;
    padding: 0.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.search-icon {
    color: var(--gray);
    margin-left: 1rem;
    font-size: 1.25rem;
}

.search-input-hero {
    flex: 1;
    border: none;
    padding: 1rem;
    font-size: 1rem;
    background: transparent;
}

.search-input-hero:focus {
    outline: none;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

.section {
    margin: 3rem 0;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.section {
    padding: 4rem 0;
    background: var(--bg-primary);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 3rem;
    gap: var(--spacing-lg);
}

.section-title {
    font-size: 2.25rem;
    font-weight: 700;
    color: var(--primary-blue-dark);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.section-subtitle {
    font-size: 1rem;
    color: var(--text-secondary);
    font-weight: 400;
}


.features-section {
    background: var(--bg-blue-light);
    padding: 4rem 0;
    border-radius: 0;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.feature-card {
    background: var(--bg-primary);
    padding: 2.5rem;
    border-radius: var(--radius-lg);
    text-align: center;
    box-shadow: var(--shadow-md);
    transition: var(--transition);
    border: 1px solid var(--border-color);
}

.feature-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
    border-color: var(--primary-blue);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: white;
    box-shadow: var(--shadow-blue);
}

.feature-card h3 {
    font-size: 1.35rem;
    margin-bottom: 1rem;
    color: var(--primary-blue-dark);
    font-weight: 600;
}

.feature-card p {
    color: var(--text-secondary);
    line-height: 1.6;
}

.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.book-card {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.book-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

.book-cover {
    position: relative;
    width: 100%;
    height: 250px;
    background: linear-gradient(135deg, var(--primary-light), var(--primary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: var(--white);
    overflow: hidden;
}

.book-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-status {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.book-status.available {
    background: rgba(16, 185, 129, 0.9);
    color: white;
}

.book-status.unavailable {
    background: rgba(239, 68, 68, 0.9);
    color: white;
}

.book-info {
    padding: 1.5rem;
}

.book-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--dark);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-wrap: break-word;
    word-break: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
}

.book-author {
    color: var(--gray);
    font-size: 0.9rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    word-wrap: break-word;
    word-break: break-word;
    overflow-wrap: break-word;
    flex-wrap: wrap;
}

.book-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: var(--gray);
    word-wrap: break-word;
    word-break: break-word;
    overflow-wrap: break-word;
    flex-wrap: wrap;
}

.meta-item i {
    width: 16px;
    color: var(--primary);
}

.book-description {
    font-size: 0.9rem;
    color: var(--gray);
    line-height: 1.5;
    margin-bottom: 1rem;
    word-wrap: break-word;
    word-break: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
}

.book-actions {
    display: flex;
    gap: 0.5rem;
}

.book-actions .btn {
    flex: 1;
}

@media (max-width: 992px) {
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.25rem;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .section-title {
        font-size: 1.75rem;
    }
    
    .books-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>
