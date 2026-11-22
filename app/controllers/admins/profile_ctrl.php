<?php
require_once __DIR__ . '/../../../helpers/message_helper.php';

// Hồ sơ của quản trị viên đang đăng nhập
class ProfileController
{
    public function index()
    {
        $pdo = pdo_connect();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_id = ?");
        $stmt->execute([$_SESSION['admin']['admin_id']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            MessageHelper::error("Không tìm thấy tài khoản của bạn");
            header("Location: index.php?controller=dashboard");
            exit;
        }

        $viewFile = "../../views/admins/profile_view.php";
        include __DIR__ . '/../../views/admins/dashboard.php';
    }
}
