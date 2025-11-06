</main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-book-open"></i>
                        <span>Thư viện ĐH</span>
                    </div>
                    <p class="footer-desc">
                        Hệ thống quản lý thư viện đại học hiện đại, 
                        giúp sinh viên dễ dàng tìm kiếm và mượn sách trực tuyến.
                    </p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Liên kết nhanh</h4>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-home"></i> Trang chủ</a></li>
                        <li><a href="index.php?page=search"><i class="fas fa-search"></i> Tìm kiếm sách</a></li>
                        <li><a href="index.php?page=my-borrows"><i class="fas fa-book-reader"></i> Sách đã mượn</a></li>
                        <li><a href="#"><i class="fas fa-info-circle"></i> Giới thiệu</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Thông tin</h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-book"></i> Quy định mượn trả</a></li>
                        <li><a href="#"><i class="fas fa-clock"></i> Giờ mở cửa</a></li>
                        <li><a href="#"><i class="fas fa-question-circle"></i> Câu hỏi thường gặp</a></li>
                        <li><a href="#"><i class="fas fa-envelope"></i> Liên hệ</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Liên hệ</h4>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Đại học ABC, Hà Nội, Việt Nam</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>(024) 1234 5678</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>library@university.edu.vn</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>T2-T6: 7:00 - 21:00<br>T7-CN: 8:00 - 17:00</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Thư viện Đại học. All rights reserved.</p>
                <p>Được phát triển với <i class="fas fa-heart"></i> bởi Team Developer</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>

<style>
.footer {
    background: linear-gradient(135deg, var(--primary-blue-dark) 0%, var(--primary-blue) 100%);
    color: white;
    padding: 3rem 0 1rem;
    margin-top: 4rem;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 3rem;
    margin-bottom: 2rem;
}

.footer-section h4 {
    margin-bottom: 1rem;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.footer-logo i {
    font-size: 2rem;
    color: var(--accent-blue-light);
}

.footer-desc {
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.footer-social {
    display: flex;
    gap: 1rem;
}

.footer-social a {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: var(--transition);
}

.footer-social a:hover {
    background: var(--accent-blue);
    transform: translateY(-3px);
    box-shadow: var(--shadow-blue);
}

.footer-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: white;
}

.footer-links {
    list-style: none;
}

.footer-links li {
    margin-bottom: 0.75rem;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
}

.footer-links a:hover {
    color: var(--accent-blue-light);
    padding-left: 0.5rem;
}

.footer-contact {
    list-style: none;
}

.footer-contact li {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    color: rgba(255, 255, 255, 0.7);
}

.footer-contact i {
    color: var(--accent-blue-light);
    width: 20px;
    margin-top: 0.25rem;
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 2rem;
    text-align: center;
    color: rgba(255, 255, 255, 0.5);
}

.footer-bottom p {
    margin-bottom: 0.5rem;
}

.footer-bottom .fa-heart {
    color: #ef4444;
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}
</style>
