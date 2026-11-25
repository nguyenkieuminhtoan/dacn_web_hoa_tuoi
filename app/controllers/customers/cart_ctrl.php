<?php
include_once __DIR__ . '/../../models/cart_model.php';
include_once __DIR__ . '/../../../helpers/message_helper.php';

class CartController
{
    private $model;

    public function __construct()
    {
        $this->model = new CartModel();
    }

    public function listCart()
    {
        if (!isset($_SESSION['customer'])) {
            MessageHelper::error("Vui lòng đăng nhập để xem giỏ hàng");
            header("Location: index.php?router=login");
            exit();
        }

        $customer_id = $_SESSION['customer']['customer_id'];
        $listCart = $this->model->getCartByCustomer($customer_id);

        if (empty($listCart)) {
            $viewFile = __DIR__ . '/../../views/customers/carts/empty_cart.php';
            $pageTitle = 'Giỏ hàng';
            require __DIR__ . '/../../views/customers/layout.php';
        } else {
            $viewFile = __DIR__ . '/../../views/customers/carts/list_cart.php';
            $pageTitle = 'Giỏ hàng';
            require __DIR__ . '/../../views/customers/layout.php';
        }
    }

    public function addCart()
    {
        if (!isset($_SESSION['customer'])) {
            MessageHelper::error("Vui lòng đăng nhập để thêm vào giỏ hàng");
            header("Location: index.php?router=login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?router=customers&controller=cart&action=listCart");
            exit();
        }

        $customer_id = $_SESSION['customer']['customer_id'];
        $product_id  = (int) ($_POST['product_id'] ?? 0);
        // Số lượng phải là số nguyên >= 1 (chặn số âm / chữ gửi lên từ form)
        $quantity    = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $detailUrl   = "index.php?router=customers&controller=detail&action=index&id=" . $product_id;

        $product = $this->model->getProductById($product_id);

        if (!$product || $product['status'] === 'discontinued') {
            MessageHelper::error("Sản phẩm không tồn tại hoặc đã ngừng kinh doanh");
            header("Location: index.php?router=customers");
            exit();
        }

        if ($quantity === false) {
            MessageHelper::error("Số lượng không hợp lệ");
            header("Location: " . $detailUrl);
            exit();
        }

        if ($product['status'] !== 'available' || $product['stock_quantity'] <= 0) {
            MessageHelper::error("Sản phẩm này đang tạm hết hàng");
            header("Location: " . $detailUrl);
            exit();
        }

        if ($product['stock_quantity'] < $quantity) {
            MessageHelper::error("Số lượng vượt quá tồn kho (còn {$product['stock_quantity']} sản phẩm)");
            header("Location: " . $detailUrl);
            exit();
        }

        $cartItem = $this->model->findCartItem($customer_id, $product_id);

        if ($cartItem) {
            $newQuantity = $cartItem['quantity'] + $quantity;

            if ($newQuantity > $product['stock_quantity']) {
                MessageHelper::error("Giỏ hàng đã có {$cartItem['quantity']} sản phẩm này, tồn kho chỉ còn {$product['stock_quantity']}");
                header("Location: " . $detailUrl);
                exit();
            }

            $this->model->updateCartQuantity($cartItem['cart_id'], $customer_id, $newQuantity);
        } else {
            $this->model->addCart($customer_id, $product_id, $quantity);
        }

        MessageHelper::success("Đã thêm sản phẩm vào giỏ hàng");
        header("Location: index.php?router=customers&controller=cart&action=listCart");
        exit();
    }

    public function deleteCart()
    {
        if (!isset($_SESSION['customer'])) {
            header("Location: index.php?router=login");
            exit();
        }

        // Chỉ nhận POST (form có hộp xác nhận) — tránh bị xóa qua 1 đường link GET bất kỳ
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cart_id = (int) ($_POST['cart_id'] ?? 0);
            $customer_id = $_SESSION['customer']['customer_id'];

            $this->model->deleteCart($cart_id, $customer_id);
            MessageHelper::success("Đã xóa sản phẩm khỏi giỏ hàng");
        }

        header("Location: index.php?router=customers&controller=cart&action=listCart");
        exit();
    }

    public function updateCart()
    {
        if (!isset($_SESSION['customer'])) {
            header("Location: index.php?router=login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customer_id = $_SESSION['customer']['customer_id'];
            $cart_id = (int) ($_POST['cart_id'] ?? 0);
            $quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            // Lấy dòng giỏ theo cả customer_id -> không sửa được giỏ của người khác;
            // tồn kho lấy từ DB, không tin product_id gửi lên
            $item = $this->model->getCartItem($cart_id, $customer_id);
            if (!$item) {
                MessageHelper::error("Không tìm thấy sản phẩm trong giỏ hàng");
            } elseif ($quantity === false) {
                MessageHelper::error("Số lượng không hợp lệ");
            } elseif ($quantity > $item['stock_quantity']) {
                MessageHelper::error("Số lượng vượt quá tồn kho (còn {$item['stock_quantity']} sản phẩm)");
            } else {
                $this->model->updateCartQuantity($cart_id, $customer_id, $quantity);
                MessageHelper::success("Đã cập nhật số lượng");
            }
        }

        header("Location: index.php?router=customers&controller=cart&action=listCart");
        exit();
    }
}
