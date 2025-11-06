<?php
require_once __DIR__ . '/../config/database.php';

class Borrow {
    private $conn;
    private $table = 'borrows';

    public $id;
    public $user_id;
    public $book_id;
    public $borrow_date;
    public $due_date;
    public $return_date;
    public $status;
    public $notes;
    public $fine_amount;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Lấy tất cả bản ghi mượn
    public function getAll($userId = null) {
        $query = "SELECT b.*, u.full_name, u.phone, bk.title, bk.author, bk.isbn
                  FROM " . $this->table . " b
                  JOIN users u ON b.user_id = u.id
                  JOIN books bk ON b.book_id = bk.id";
        
        if ($userId) {
            $query .= " WHERE b.user_id = :user_id";
        }
        
        $query .= " ORDER BY b.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($userId) {
            $stmt->bindParam(':user_id', $userId);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Tạo bản ghi mượn sách (trạng thái pending - chờ duyệt)
    public function create($userId, $bookId, $quantity = 1, $durationDays = 30, $pricePerDay = 0) {
        $quantity = max(1, (int)$quantity);
        
        // Kiểm tra xem người dùng đã có yêu cầu pending hoặc đang mượn sách này chưa
        $checkQuery = "SELECT id FROM " . $this->table . " 
                       WHERE user_id = :user_id AND book_id = :book_id 
                       AND (status = 'borrowed' OR status = 'pending')";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':user_id', $userId);
        $checkStmt->bindParam(':book_id', $bookId);
        $checkStmt->execute();
        
        if ($checkStmt->fetch()) {
            return ['success' => false, 'message' => 'Bạn đã có yêu cầu mượn sách này rồi!'];
        }
        
        // Kiểm tra số sách đang mượn (không tính pending)
        $countQuery = "SELECT COUNT(*) as count FROM " . $this->table . " 
                       WHERE user_id = :user_id AND status = 'borrowed'";
        $countStmt = $this->conn->prepare($countQuery);
        $countStmt->bindParam(':user_id', $userId);
        $countStmt->execute();
        $count = $countStmt->fetch()['count'];
        
        if ($count >= 5) { // Giới hạn 5 sách
            return ['success' => false, 'message' => 'Bạn đã mượn tối đa 5 cuốn sách!'];
        }
        
        // Kiểm tra sách có sẵn không
        $bookQuery = "SELECT available_quantity FROM books WHERE id = :book_id";
        $bookStmt = $this->conn->prepare($bookQuery);
        $bookStmt->bindParam(':book_id', $bookId);
        $bookStmt->execute();
        $book = $bookStmt->fetch();
        
        if (!$book || $book['available_quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Sách này không đủ số lượng để mượn!'];
        }
        
        // Tạo bản ghi mượn với hạn trả động và lưu ghi chú chi phí
        $durationDays = max(1, (int)$durationDays);
        $pricePerDay = max(0, (int)$pricePerDay);
        if ($pricePerDay <= 0) { $pricePerDay = 2000; }
        $totalPrice = $durationDays * $pricePerDay * $quantity;
        $notes = $totalPrice > 0
            ? ("So luong: {$quantity} cuon; Thoi gian muon: {$durationDays} ngay; Gia/ngay: " . number_format($pricePerDay) . "; Tong: " . number_format($totalPrice) . " VND")
            : ("So luong: {$quantity} cuon; Thoi gian muon: {$durationDays} ngay");

        $query = "INSERT INTO " . $this->table . "
                  (user_id, book_id, quantity, borrow_date, due_date, status, notes)
                  VALUES 
                  (:user_id, :book_id, :quantity, CURDATE(), DATE_ADD(CURDATE(), INTERVAL :duration DAY), 'pending', :notes)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':book_id', $bookId);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindValue(':duration', $durationDays, PDO::PARAM_INT);
        $stmt->bindParam(':notes', $notes);
        
        if ($stmt->execute()) {
            // KHÔNG cập nhật số lượng sách có sẵn ngay (chờ duyệt)
            // Tạo thông báo cho thủ thư
            require_once __DIR__ . '/Notification.php';
            $notificationModel = new Notification();
            $notificationModel->create(
                $userId,
                'Yêu cầu mượn sách đã được gửi',
                "Yêu cầu mượn sách của bạn đang chờ thủ thư duyệt.",
                'info',
                $this->conn->lastInsertId(),
                'borrow'
            );
            
            return ['success' => true, 'message' => 'Yêu cầu mượn sách đã được gửi! Vui lòng chờ thủ thư duyệt.', 'borrow_id' => $this->conn->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Có lỗi xảy ra khi gửi yêu cầu mượn sách!'];
        }
    }

    // Duyệt yêu cầu mượn sách (thủ thư)
    public function approve($id) {
        // Lấy thông tin yêu cầu
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id AND status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $borrow = $stmt->fetch();
        
        if (!$borrow) {
            return ['success' => false, 'message' => 'Không tìm thấy yêu cầu mượn sách!'];
        }
        
        // Kiểm tra lại số lượng sách có sẵn
        $bookQuery = "SELECT available_quantity FROM books WHERE id = :book_id";
        $bookStmt = $this->conn->prepare($bookQuery);
        $bookStmt->bindParam(':book_id', $borrow['book_id']);
        $bookStmt->execute();
        $book = $bookStmt->fetch();
        
        $quantity = isset($borrow['quantity']) ? (int)$borrow['quantity'] : 1;
        
        if (!$book || $book['available_quantity'] < $quantity) {
            // Từ chối nếu không đủ số lượng
            $this->reject($id, 'Không đủ số lượng sách có sẵn');
            return ['success' => false, 'message' => 'Không đủ số lượng sách có sẵn!'];
        }
        
        // Tính số ngày mượn từ due_date ban đầu
        $originalBorrowDate = new DateTime($borrow['borrow_date']);
        $originalDueDate = new DateTime($borrow['due_date']);
        $durationDays = $originalDueDate->diff($originalBorrowDate)->days;
        if ($durationDays <= 0) $durationDays = 30; // Default 30 days
        
        // Cập nhật trạng thái thành borrowed và cập nhật số lượng
        $updateQuery = "UPDATE " . $this->table . "
                        SET status = 'borrowed',
                            borrow_date = CURDATE(),
                            due_date = DATE_ADD(CURDATE(), INTERVAL :duration DAY)
                        WHERE id = :id";
        
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bindParam(':id', $id);
        $updateStmt->bindValue(':duration', $durationDays, PDO::PARAM_INT);
        
        if ($updateStmt->execute()) {
            // Cập nhật số lượng sách có sẵn
            $updateBookQuery = "UPDATE books SET available_quantity = available_quantity - :quantity WHERE id = :book_id";
            $updateBookStmt = $this->conn->prepare($updateBookQuery);
            $updateBookStmt->bindParam(':quantity', $quantity);
            $updateBookStmt->bindParam(':book_id', $borrow['book_id']);
            $updateBookStmt->execute();
            
            // Tạo thông báo cho người dùng
            require_once __DIR__ . '/Notification.php';
            $notificationModel = new Notification();
            $notificationModel->create(
                $borrow['user_id'],
                'Yêu cầu mượn sách đã được duyệt',
                "Yêu cầu mượn sách của bạn đã được thủ thư duyệt. Vui lòng đến thư viện để nhận sách.",
                'success',
                $id,
                'borrow'
            );
            
            return ['success' => true, 'message' => 'Đã duyệt yêu cầu mượn sách!'];
        } else {
            return ['success' => false, 'message' => 'Có lỗi xảy ra khi duyệt yêu cầu!'];
        }
    }

    // Từ chối yêu cầu mượn sách
    public function reject($id, $reason = '') {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id AND status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $borrow = $stmt->fetch();
        
        if (!$borrow) {
            return ['success' => false, 'message' => 'Không tìm thấy yêu cầu mượn sách!'];
        }
        
        // Cập nhật trạng thái thành rejected
        $updateQuery = "UPDATE " . $this->table . " SET status = 'rejected' WHERE id = :id";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bindParam(':id', $id);
        
        if ($updateStmt->execute()) {
            // Tạo thông báo cho người dùng
            require_once __DIR__ . '/Notification.php';
            $notificationModel = new Notification();
            $message = !empty($reason) 
                ? "Yêu cầu mượn sách của bạn đã bị từ chối. Lý do: {$reason}"
                : "Yêu cầu mượn sách của bạn đã bị từ chối.";
            $notificationModel->create(
                $borrow['user_id'],
                'Yêu cầu mượn sách bị từ chối',
                $message,
                'error',
                $id,
                'borrow'
            );
            
            return ['success' => true, 'message' => 'Đã từ chối yêu cầu mượn sách!'];
        } else {
            return ['success' => false, 'message' => 'Có lỗi xảy ra khi từ chối yêu cầu!'];
        }
    }

    // Trả sách
    public function returnBook($id) {
        // Lấy thông tin mượn sách
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $borrow = $stmt->fetch();
        
        if (!$borrow) {
            return ['success' => false, 'message' => 'Không tìm thấy bản ghi mượn sách!'];
        }
        
        if ($borrow['status'] === 'returned') {
            return ['success' => false, 'message' => 'Sách này đã được trả rồi!'];
        }
        
        // Tính phí phạt
        $fineAmount = calculateFine($borrow['due_date']);
        
        // Cập nhật trạng thái trả sách
        $quantity = isset($borrow['quantity']) ? (int)$borrow['quantity'] : 1;
        
        $updateQuery = "UPDATE " . $this->table . "
                        SET status = 'returned',
                            return_date = CURDATE(),
                            fine_amount = :fine_amount
                        WHERE id = :id";
        
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bindParam(':id', $id);
        $updateStmt->bindParam(':fine_amount', $fineAmount);
        
        if ($updateStmt->execute()) {
            // Cập nhật số lượng sách có sẵn (trả lại đúng số lượng đã mượn)
            $bookQuery = "UPDATE books SET available_quantity = available_quantity + :quantity WHERE id = :book_id";
            $bookStmt = $this->conn->prepare($bookQuery);
            $bookStmt->bindParam(':quantity', $quantity);
            $bookStmt->bindParam(':book_id', $borrow['book_id']);
            $bookStmt->execute();
            
            return ['success' => true, 'message' => 'Trả sách thành công!'];
        } else {
            return ['success' => false, 'message' => 'Có lỗi xảy ra khi trả sách!'];
        }
    }
    
    // Lấy sách mượn theo người dùng
    public function getByUser($userId) {
        $query = "SELECT b.*, bk.title, bk.author, bk.isbn, bk.cover_image, bk.location
                  FROM " . $this->table . " b
                  JOIN books bk ON b.book_id = bk.id
                  WHERE b.user_id = :user_id
                  ORDER BY b.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Kiểm tra sách quá hạn
    public function checkOverdue() {
        $query = "UPDATE " . $this->table . "
                  SET status = 'overdue'
                  WHERE due_date < CURDATE() AND status = 'borrowed'";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    // Lấy thống kê mượn sách
    public function getStatistics() {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'borrowed' THEN 1 ELSE 0 END) as borrowed,
                    SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned,
                    SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue,
                    SUM(fine_amount) as total_fine
                  FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Lấy danh sách yêu cầu pending (chờ duyệt)
    public function getPendingRequests() {
        $query = "SELECT b.*, u.full_name, u.phone, u.email, 
                         bk.title, bk.author, bk.isbn, bk.available_quantity
                  FROM " . $this->table . " b
                  JOIN users u ON b.user_id = u.id
                  JOIN books bk ON b.book_id = bk.id
                  WHERE b.status = 'pending'
                  ORDER BY b.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy sách quá hạn
    public function getOverdueBooks() {
        $query = "SELECT b.*, u.full_name, u.phone, bk.title
                  FROM " . $this->table . " b
                  JOIN users u ON b.user_id = u.id
                  JOIN books bk ON b.book_id = bk.id
                  WHERE b.status = 'overdue' OR (b.status = 'borrowed' AND b.due_date < CURDATE())
                  ORDER BY b.due_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>