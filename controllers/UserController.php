<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class UserController {
    public function delete() {
        header('Content-Type: application/json');
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền']);
            return;
        }
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
            return;
        }
        $user = new User();
        $ok = $user->delete($id);
        echo json_encode(['success' => (bool)$ok]);
    }
}
?>

