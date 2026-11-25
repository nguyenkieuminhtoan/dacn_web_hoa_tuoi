<?php
if (isset($_GET['controller'])) {
    $controllerURL = $_GET['controller'];
} else {
    $controllerURL = 'index';
}

if (isset($_GET['action'])) {
    $actionURL = $_GET['action'];
} else {
    $actionURL = 'index';
}

$controllerPath = '';
switch ($controllerURL) {
    case 'index':
        $controllerPath = __DIR__ . '/../../controllers/customers/index_ctrl.php';
        break;
    case 'detail':
        $controllerPath = __DIR__ . '/../../controllers/customers/detail_ctrl.php';
        break;
    case 'cart':
        $controllerPath = __DIR__ . '/../../controllers/customers/cart_ctrl.php';
        break;
    case 'order':
        $controllerPath = __DIR__ . '/../../controllers/customers/order_ctrl.php';
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
