<?php
require_once __DIR__ . '/../../models/order_model.php';
require_once __DIR__ . '/../../models/cart_model.php';
require_once __DIR__ . '/../../../helpers/order_helper.php';

class OrderController
{
    private $orderModel;
    private $cartModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->cartModel = new CartModel();
    }

    public function index()
    {
        if (!isset($_SESSION['customer'])) {
            header('Location: index.php?router=login');
            exit();
        }

        $customer_id = $_SESSION['customer']['customer_id'];
        $listCart = $this->cartModel->getCartByCustomer($customer_id);

        if (empty($listCart)) {
            $_SESSION['error'] = "Giỏ hàng của bạn đang trống!";
            header('Location: index.php?router=customers&controller=cart&action=listCart');
            exit();
        }

        // Có món hết hàng / vượt tồn kho -> quay lại giỏ để khách sửa trước
        if (CartModel::cartProblems($listCart)) {
            $_SESSION['error'] = "Một số sản phẩm trong giỏ không đủ hàng. Vui lòng cập nhật giỏ hàng trước khi đặt.";
            header('Location: index.php?router=customers&controller=cart&action=listCart');
            exit();
        }

        // Pre-fill receiver info from customer profile if available
        $customer = $_SESSION['customer'];

        // Lần gửi trước bị server báo lỗi -> giữ lại dữ liệu đã nhập + lỗi từng ô (dùng 1 lần)
        $old = $_SESSION['checkout_old'] ?? [];
        $fieldErrors = $_SESSION['checkout_errors'] ?? [];
        unset($_SESSION['checkout_old'], $_SESSION['checkout_errors']);

        $viewFile = __DIR__ . '/../../views/customers/orders/order.php';
        $pageTitle = 'Đặt hàng';
        require __DIR__ . '/../../views/customers/layout.php';
    }

    /**
     * Luật kiểm tra thông tin giao hàng (khớp data-rules trong orders/order.php + cột trong DB).
     * @return array [tên ô => thông báo lỗi]; rỗng = hợp lệ
     */
    private static function validateCheckout(array $info)
    {
        $errors = [];
        $len = fn($s) => mb_strlen($s, 'UTF-8');

        if ($info['receiver_name'] === '') $errors['receiver_name'] = 'Vui lòng nhập họ tên người nhận';
        elseif ($len($info['receiver_name']) > 100) $errors['receiver_name'] = 'Họ tên người nhận không được quá 100 ký tự';

        if ($info['receiver_phone'] === '') $errors['receiver_phone'] = 'Vui lòng nhập số điện thoại';
        elseif (!preg_match('/^[0-9]{10,15}$/', $info['receiver_phone'])) $errors['receiver_phone'] = 'Số điện thoại phải gồm 10–15 chữ số';

        if ($info['delivery_address'] === '') $errors['delivery_address'] = 'Vui lòng nhập địa chỉ giao hàng';
        elseif ($len($info['delivery_address']) < 10) $errors['delivery_address'] = 'Địa chỉ giao hàng phải có ít nhất 10 ký tự';
        elseif ($len($info['delivery_address']) > 255) $errors['delivery_address'] = 'Địa chỉ giao hàng không được quá 255 ký tự';

        if ($len($info['note']) > 500) $errors['note'] = 'Ghi chú không được quá 500 ký tự';

        return $errors;
    }

    public function placeOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['customer'])) {
                header('Location: index.php?router=login');
                exit();
            }

            $customer_id = $_SESSION['customer']['customer_id'];
            $info = [
                'receiver_name'    => trim((string) ($_POST['receiver_name'] ?? '')),
                'receiver_phone'   => trim((string) ($_POST['receiver_phone'] ?? '')),
                'delivery_address' => trim((string) ($_POST['delivery_address'] ?? '')),
                'note'             => trim((string) ($_POST['note'] ?? '')),
                'payment_method'   => $_POST['payment_method'] ?? 'cod',
            ];
            // Chỉ nhận các phương thức có trong DB (enum), sai thì về COD
            if (!in_array($info['payment_method'], ['cod', 'bank_transfer', 'e_wallet'], true)) {
                $info['payment_method'] = 'cod';
            }

            // Kiểm tra lại ở server (cùng luật với form: admin.js data-rules) — không tin dữ liệu từ trình duyệt
            $fieldErrors = self::validateCheckout($info);
            if ($fieldErrors) {
                $_SESSION['checkout_old'] = $info;
                $_SESSION['checkout_errors'] = $fieldErrors;
                $_SESSION['error'] = "Vui lòng kiểm tra lại thông tin giao hàng.";
                header('Location: index.php?router=customers&controller=order&action=index');
                exit();
            }

            $listCart = $this->cartModel->getCartByCustomer($customer_id);
            if (empty($listCart)) {
                $_SESSION['error'] = "Giỏ hàng trống!";
                header('Location: index.php?router=customers&controller=cart&action=listCart');
                exit();
            }

            // Kiểm tra tồn kho + trừ kho + tạo đơn + xoá giỏ trong 1 transaction
            $result = $this->orderModel->placeOrderFromCart($customer_id, $listCart, $info);

            if ($result['ok']) {
                $_SESSION['success'] = "Đặt hàng thành công!";
                header('Location: index.php?router=customers&controller=order&action=success&id=' . $result['order_id']);
            } else {
                // Thiếu hàng -> về giỏ để sửa số lượng; lỗi khác -> ở lại trang đặt hàng (giữ dữ liệu đã nhập)
                $_SESSION['error'] = $result['error'];
                $_SESSION['checkout_old'] = $info;
                $back = $result['reason'] === 'error' ? 'controller=order&action=index' : 'controller=cart&action=listCart';
                header('Location: index.php?router=customers&' . $back);
            }
            exit();
        }

        header('Location: index.php?router=customers&controller=order&action=index');
        exit();
    }

    public function success()
    {
        if (!isset($_SESSION['customer'])) {
            header('Location: index.php?router=login');
            exit();
        }
        $order_id = (int) ($_GET['id'] ?? 0);
        $order = $this->orderModel->getByIdOrder($order_id);
        if (!$order || $order['customer_id'] != $_SESSION['customer']['customer_id']) {
            header('Location: index.php?router=customers');
            exit();
        }
        $orderDetails = $this->orderModel->getByIdOrderDetail($order_id);
        $viewFile = __DIR__ . '/../../views/customers/orders/success.php';
        $pageTitle = 'Đặt hàng thành công';
        require __DIR__ . '/../../views/customers/layout.php';
    }

    public function listOrders()
    {
        if (!isset($_SESSION['customer'])) {
            header('Location: index.php?router=login');
            exit();
        }

        $customer_id = $_SESSION['customer']['customer_id'];
        $allOrders = $this->orderModel->getOrdersByCustomer($customer_id);

        // Lọc theo tab trạng thái (?status=...) + đếm số đơn mỗi tab
        $statusCounts = array_count_values(array_column($allOrders, 'order_status'));
        $currentStatus = $_GET['status'] ?? '';
        if (!isset(OrderHelper::STATUS_LABELS[$currentStatus])) $currentStatus = '';
        $orders = $currentStatus === ''
            ? $allOrders
            : array_values(array_filter($allOrders, fn($o) => $o['order_status'] === $currentStatus));
        $totalOrders = count($allOrders);

        $viewFile = __DIR__ . '/../../views/customers/orders/list_orders.php';
        $pageTitle = 'Đơn hàng của tôi';
        require __DIR__ . '/../../views/customers/layout.php';
    }

    public function viewDetail()
    {
        if (!isset($_SESSION['customer'])) {
            header('Location: index.php?router=login');
            exit();
        }

        $order_id = (int) ($_GET['id'] ?? 0);
        $order = $this->orderModel->getByIdOrder($order_id);

        if (!$order || $order['customer_id'] != $_SESSION['customer']['customer_id']) {
            header('Location: index.php?router=customers&controller=order&action=listOrders');
            exit();
        }

        $orderDetails = $this->orderModel->getByIdOrderDetail($order_id);

        $viewFile = __DIR__ . '/../../views/customers/orders/order_detail.php';
        $pageTitle = 'Đơn hàng ' . $order['order_code'];
        require __DIR__ . '/../../views/customers/layout.php';
    }

    // Khách tự huỷ đơn (chỉ khi còn "Chờ xử lý") — POST + hộp xác nhận; hoàn tồn kho trong cùng transaction
    public function cancelOrder()
    {
        if (!isset($_SESSION['customer'])) {
            header('Location: index.php?router=login');
            exit();
        }

        $order_id = (int) ($_POST['order_id'] ?? 0);
        $detailUrl = 'index.php?router=customers&controller=order&action=viewDetail&id=' . $order_id;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $detailUrl);
            exit();
        }

        $order = $this->orderModel->getByIdOrder($order_id);
        if (!$order || $order['customer_id'] != $_SESSION['customer']['customer_id']) {
            header('Location: index.php?router=customers&controller=order&action=listOrders');
            exit();
        }

        // cancelOrder() tự kiểm tra lại trạng thái (khoá dòng) -> admin vừa xác nhận thì không huỷ được nữa
        if ($order['order_status'] === 'cancelled') {
            $_SESSION['error'] = "Đơn hàng {$order['order_code']} đã được hủy trước đó.";
        } elseif ($this->orderModel->cancelOrder($order_id, OrderHelper::CUSTOMER_CANCELLABLE)) {
            $_SESSION['success'] = "Đã hủy đơn hàng {$order['order_code']}.";
        } else {
            $_SESSION['error'] = "Đơn hàng đã được cửa hàng xác nhận nên không thể tự hủy. Vui lòng liên hệ cửa hàng để được hỗ trợ.";
        }
        header('Location: ' . $detailUrl);
        exit();
    }
}
