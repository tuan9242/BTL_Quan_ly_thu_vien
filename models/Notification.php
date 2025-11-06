<?php
require_once __DIR__ . '/../config/database.php';

class Notification {
    private $conn;
    private $table = 'notifications';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Tạo thông báo
    public function create($userId, $title, $message, $type = 'info', $relatedId = null, $relatedType = null) {
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, title, message, type, related_id, related_type) 
                  VALUES (:user_id, :title, :message, :type, :related_id, :related_type)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':related_id', $relatedId);
        $stmt->bindParam(':related_type', $relatedType);
        
        return $stmt->execute();
    }

    // Lấy thông báo của user
    public function getByUser($userId, $limit = 20, $unreadOnly = false) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id";
        
        if ($unreadOnly) {
            $query .= " AND is_read = 0";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Đánh dấu đã đọc
    public function markAsRead($id, $userId) {
        $query = "UPDATE " . $this->table . " 
                  SET is_read = 1 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    // Đánh dấu tất cả đã đọc
    public function markAllAsRead($userId) {
        $query = "UPDATE " . $this->table . " 
                  SET is_read = 1 
                  WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    // Đếm số thông báo chưa đọc
    public function getUnreadCount($userId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                  WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ? (int)$result['count'] : 0;
    }

    // Xóa thông báo
    public function delete($id, $userId) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }
}
?>

