<?php
require_once __DIR__ . '/../config/database.php';

class Category {
    private $conn;
    private $table = 'categories';

    public $id;
    public $name;
    public $description;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Lấy tất cả danh mục
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy danh mục theo ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Lấy tên danh mục theo ID
    public function getNameById($id) {
        $query = "SELECT name FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['name'] : 'Không xác định';
    }

    // Tạo danh mục mới
    public function create() {
        $query = "INSERT INTO " . $this->table . " (name, description) VALUES (:name, :description)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        return $stmt->execute();
    }

    // Cập nhật danh mục
    public function update() {
        $query = "UPDATE " . $this->table . " SET name = :name, description = :description WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        return $stmt->execute();
    }

    // Xóa danh mục
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Đếm số sách trong danh mục
    public function getBookCount($id) {
        $query = "SELECT COUNT(*) as count FROM books WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
}
?>
