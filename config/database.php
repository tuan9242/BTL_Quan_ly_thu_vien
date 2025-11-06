<?php
class Database {
    private $host = "localhost";
    private $db_name = "library_management";
    private $username = "root";
    private $password = "tuan9242";
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}

// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hàm helper
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isLibrarian() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'librarian']);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function calculateFine($dueDate, $returnDate = null) {
    $return = $returnDate ? new DateTime($returnDate) : new DateTime();
    $due = new DateTime($dueDate);
    
    if ($return <= $due) {
        return 0;
    }
    
    $daysLate = $return->diff($due)->days;
    $finePerDay = 5000; // 5,000 VNĐ per day
    
    return $daysLate * $finePerDay;
}

function alert($message, $type = 'info') {
    $icons = [
        'success' => '✓',
        'error' => '✗',
        'warning' => '⚠',
        'info' => 'ℹ'
    ];
    
    return "<div class='alert alert-{$type}'>
                <span class='alert-icon'>{$icons[$type]}</span>
                <span class='alert-message'>{$message}</span>
            </div>";
}
?>