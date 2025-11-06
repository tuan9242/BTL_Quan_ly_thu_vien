<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $username;
    public $password;
    public $email;
    public $full_name;
    public $phone;
    public $address;
    public $student_id;
    public $role;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Lấy tất cả người dùng
    public function getAll($limit = null, $offset = 0) {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";

        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($query);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy người dùng theo ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password = $row['password'];
            $this->email = $row['email'];
            $this->full_name = $row['full_name'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->student_id = $row['student_id'];
            $this->role = $row['role'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return $row;
        }

        return false;
    }

    // Lấy người dùng theo username
    public function getByUsername($username) {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy người dùng theo email
    public function getByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Xác thực đăng nhập
    public function authenticate($username, $password) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE (username = :username OR email = :username) 
                  AND status = 'active' LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    // Tạo người dùng mới
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  (username, password, email, full_name, phone, address, student_id, role, status)
                  VALUES
                  (:username, :password, :email, :full_name, :phone, :address, :student_id, :role, :status)";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->student_id = htmlspecialchars(strip_tags($this->student_id));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->status = htmlspecialchars(strip_tags($this->status));

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':student_id', $this->student_id);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':status', $this->status);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Cập nhật người dùng
    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET username = :username,
                      email = :email,
                      full_name = :full_name,
                      phone = :phone,
                      address = :address,
                      student_id = :student_id,
                      role = :role,
                      status = :status
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->student_id = htmlspecialchars(strip_tags($this->student_id));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':student_id', $this->student_id);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Đổi mật khẩu
    public function changePassword($userId, $newPassword) {
        $query = "UPDATE " . $this->table . " SET password = :password WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $userId);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Xóa người dùng
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Kiểm tra username đã tồn tại
    public function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        return $stmt->fetch() !== false;
    }

    // Kiểm tra email đã tồn tại
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch() !== false;
    }

    // Kiểm tra student_id đã tồn tại
    public function studentIdExists($studentId) {
        $query = "SELECT id FROM " . $this->table . " WHERE student_id = :student_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        
        return $stmt->fetch() !== false;
    }

    // Lấy thống kê người dùng
    public function getStatistics() {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as students,
                    SUM(CASE WHEN role = 'librarian' THEN 1 ELSE 0 END) as librarians,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
                  FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Tìm kiếm người dùng
    public function search($keyword, $role = null, $limit = null, $offset = 0) {
        $query = "SELECT * FROM " . $this->table . "
                  WHERE (username LIKE :keyword
                         OR full_name LIKE :keyword
                         OR email LIKE :keyword
                         OR student_id LIKE :keyword)";

        if ($role) {
            $query .= " AND role = :role";
        }

        $query .= " ORDER BY created_at DESC";

        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($query);
        $searchTerm = "%{$keyword}%";
        $stmt->bindParam(':keyword', $searchTerm);

        if ($role) {
            $stmt->bindParam(':role', $role);
        }

        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
