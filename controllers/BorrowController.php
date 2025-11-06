<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Borrow.php';

class BorrowController {
    public function create() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            return;
        }
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }
        $bookId = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
        if ($bookId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu mã sách']);
            return;
        }
        $borrowModel = new Borrow();
        $result = $borrowModel->create($_SESSION['user_id'], $bookId);
        echo json_encode($result);
    }

    public function return() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            return;
        }
        if (!isLibrarian()) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền']);
            return;
        }
        $borrowId = isset($_POST['borrow_id']) ? intval($_POST['borrow_id']) : 0;
        if ($borrowId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu mã mượn']);
            return;
        }
        $borrowModel = new Borrow();
        $result = $borrowModel->returnBook($borrowId);
        echo json_encode($result);
    }
}
?>

