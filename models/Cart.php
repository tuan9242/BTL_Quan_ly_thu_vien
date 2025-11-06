<?php
require_once __DIR__ . '/../config/database.php';

class Cart {
    private $conn;
    private $table = 'cart';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Thêm sách vào giỏ hàng
    public function add($userId, $bookId, $quantity = 1, $durationDays = 30) {
        // Kiểm tra sách đã có trong giỏ chưa
        $checkQuery = "SELECT id, quantity FROM " . $this->table . " 
                       WHERE user_id = :user_id AND book_id = :book_id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':user_id', $userId);
        $checkStmt->bindParam(':book_id', $bookId);
        $checkStmt->execute();
        $existing = $checkStmt->fetch();

        if ($existing) {
            // Cập nhật số lượng
            $newQuantity = $existing['quantity'] + $quantity;
            $updateQuery = "UPDATE " . $this->table . " 
                           SET quantity = :quantity, duration_days = :duration_days 
                           WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':quantity', $newQuantity);
            $updateStmt->bindParam(':duration_days', $durationDays);
            $updateStmt->bindParam(':id', $existing['id']);
            
            if ($updateStmt->execute()) {
                return ['success' => true, 'message' => 'Đã cập nhật giỏ hàng!'];
            }
        } else {
            // Thêm mới
            $insertQuery = "INSERT INTO " . $this->table . " 
                          (user_id, book_id, quantity, duration_days) 
                          VALUES (:user_id, :book_id, :quantity, :duration_days)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bindParam(':user_id', $userId);
            $insertStmt->bindParam(':book_id', $bookId);
            $insertStmt->bindParam(':quantity', $quantity);
            $insertStmt->bindParam(':duration_days', $durationDays);
            
            if ($insertStmt->execute()) {
                return ['success' => true, 'message' => 'Đã thêm vào giỏ hàng!'];
            }
        }

        return ['success' => false, 'message' => 'Có lỗi xảy ra!'];
    }

    // Lấy giỏ hàng của user
    public function getByUser($userId) {
        $query = "SELECT c.*, b.title, b.author, b.isbn, b.cover_image, 
                         b.available_quantity, b.location,
                         cat.name AS category_name
                  FROM " . $this->table . " c
                  JOIN books b ON c.book_id = b.id
                  LEFT JOIN categories cat ON b.category_id = cat.id
                  WHERE c.user_id = :user_id
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Cập nhật số lượng
    public function updateQuantity($id, $userId, $quantity) {
        $query = "UPDATE " . $this->table . " 
                  SET quantity = :quantity 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    // Xóa item khỏi giỏ hàng
    public function remove($id, $userId) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    // Xóa tất cả giỏ hàng
    public function clear($userId) {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    // Đếm số item trong giỏ
    public function getCount($userId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ? (int)$result['count'] : 0;
    }
}
?>

