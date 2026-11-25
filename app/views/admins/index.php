<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';

// Chặn truy cập khi chưa đăng nhập admin
if (empty($_SESSION['admin'])) {
    header("Location: ../../../index.php?router=login");
    exit;
}

if (isset($_GET['controller'])) {
    $controllerURL = $_GET['controller'];
} else {
    $controllerURL = 'dashboard';
}

if (isset($_GET['action'])) {
    $actionURL = $_GET['action'];
} else {
    $actionURL = 'index';
}

$controllerPath = '';
switch ($controllerURL) {
    case 'dashboard':
        $controllerPath = "../../controllers/admins/dashboard_ctrl.php";
        break;
    case 'product':
        $controllerPath = "../../controllers/admins/product_ctrl.php";
        break;
    case 'category':
        $controllerPath = "../../controllers/admins/category_ctrl.php";
        break;
    case 'accountAdmin':
        $controllerPath = "../../controllers/admins/account_admin_ctrl.php";
        break;
    case 'accountCustomer':
        $controllerPath = "../../controllers/admins/account_customer_ctrl.php";
        break;
    case 'order':
        $controllerPath = "../../controllers/admins/order_ctrl.php";
        break;
    case 'promotion':
        $controllerPath = "../../controllers/admins/promotion_ctrl.php";
        break;
    case 'profile':
        $controllerPath = "../../controllers/admins/profile_ctrl.php";
        break;
    default:
        break;
}
if (!file_exists($controllerPath)) {
    die("<h2><i> Không tìm thấy file controller:</i> $controllerPath </h2>");
}
require_once $controllerPath;

$className = ucfirst($controllerURL) . 'Controller';
if (!class_exists($className)) {
    die("<h2><i> Không tìm thấy class:</i> $className </h2>");
}

$controller = new $className();
// Chỉ gọi được action public (is_callable), không gọi hàm "magic"/nội bộ (__construct, _xxx).
// Tên action lấy từ URL -> phải escape khi in ra (tránh chèn mã HTML/JS).
if (!is_string($actionURL) || $actionURL === '' || $actionURL[0] === '_' || !is_callable([$controller, $actionURL])) {
    http_response_code(404);
    die("<h2><i> Không tìm thấy action:</i> " . htmlspecialchars(is_string($actionURL) ? $actionURL : '', ENT_QUOTES, 'UTF-8') . " trong controller: $className</h2>");
}
$controller->$actionURL();
