<?php
require_once __DIR__ . '/../config/database.php';

class AuthController {
    public function logout() {
        session_destroy();
        redirect('index.php?page=login');
    }
}
?>

