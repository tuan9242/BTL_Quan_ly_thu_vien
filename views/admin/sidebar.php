<aside class="admin-sidebar">
    <div class="sidebar-header">
        <i class="fas fa-tasks"></i>
        <h3>Quản lý</h3>
    </div>
    
    <nav class="sidebar-nav">
        <a href="index.php?page=admin-dashboard" 
           class="sidebar-link <?php echo ($page ?? '') === 'admin-dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>
            <span>Tổng quan</span>
        </a>
        
        <?php if (isLibrarian()): ?>
        <a href="index.php?page=admin-books" 
           class="sidebar-link <?php echo ($page ?? '') === 'admin-books' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i>
            <span>Quản lý sách</span>
        </a>
        
        <a href="index.php?page=admin-borrows" 
           class="sidebar-link <?php echo ($page ?? '') === 'admin-borrows' ? 'active' : ''; ?>">
            <i class="fas fa-hand-holding"></i>
            <span>Quản lý mượn trả</span>
        </a>
        <?php endif; ?>
        
        <?php if (isAdmin()): ?>
        <a href="index.php?page=admin-users" 
           class="sidebar-link <?php echo ($page ?? '') === 'admin-users' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Quản lý người dùng</span>
        </a>
        <?php endif; ?>

        <?php if (isLibrarian()): ?>
        <a href="index.php?page=admin-categories" 
           class="sidebar-link <?php echo ($page ?? '') === 'admin-categories' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i>
            <span>Danh mục sách</span>
        </a>
        <?php endif; ?>

        <?php if (isAdmin()): ?>
        <a href="index.php?page=admin-reports" 
           class="sidebar-link <?php echo ($page ?? '') === 'admin-reports' ? 'active' : ''; ?>">
            <i class="fas fa-file-alt"></i>
            <span>Báo cáo thống kê</span>
        </a>
        <?php endif; ?>
    </nav>
    
    
</aside>
