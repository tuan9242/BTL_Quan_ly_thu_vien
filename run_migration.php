<?php
/**
 * Script chạy migration database
 * Chạy file này một lần để cập nhật database
 */

require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "Bắt đầu migration...\n\n";

try {
    // 1. Thêm cột quantity vào bảng borrows (nếu chưa có)
    echo "1. Kiểm tra cột quantity trong bảng borrows...\n";
    $checkColumn = $conn->query("SHOW COLUMNS FROM `borrows` LIKE 'quantity'");
    if ($checkColumn->rowCount() == 0) {
        $conn->exec("ALTER TABLE `borrows` ADD COLUMN `quantity` INT DEFAULT 1 AFTER `book_id`");
        echo "   ✓ Đã thêm cột quantity\n";
    } else {
        echo "   ✓ Cột quantity đã tồn tại\n";
    }
    
    // 2. Tạo bảng cart (nếu chưa có)
    echo "2. Kiểm tra bảng cart...\n";
    $checkTable = $conn->query("SHOW TABLES LIKE 'cart'");
    if ($checkTable->rowCount() == 0) {
        $sql = "CREATE TABLE `cart` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL,
          `book_id` INT NOT NULL,
          `quantity` INT DEFAULT 1,
          `duration_days` INT DEFAULT 30,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
          FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE,
          UNIQUE KEY `unique_cart_item` (`user_id`, `book_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->exec($sql);
        echo "   ✓ Đã tạo bảng cart\n";
    } else {
        echo "   ✓ Bảng cart đã tồn tại\n";
    }
    
    // 3. Tạo bảng notifications (nếu chưa có)
    echo "3. Kiểm tra bảng notifications...\n";
    $checkTable = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($checkTable->rowCount() == 0) {
        $sql = "CREATE TABLE `notifications` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL,
          `title` VARCHAR(255) NOT NULL,
          `message` TEXT NOT NULL,
          `type` VARCHAR(50) DEFAULT 'info',
          `is_read` TINYINT(1) DEFAULT 0,
          `related_id` INT NULL,
          `related_type` VARCHAR(50) NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
          INDEX `idx_user_read` (`user_id`, `is_read`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->exec($sql);
        echo "   ✓ Đã tạo bảng notifications\n";
    } else {
        echo "   ✓ Bảng notifications đã tồn tại\n";
    }
    
    echo "\n✓ Migration hoàn tất thành công!\n";
    
} catch (PDOException $e) {
    echo "\n✗ Lỗi: " . $e->getMessage() . "\n";
    exit(1);
}

