<?php
class CartModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = pdo_connect();
    }

    public function getCartByCustomer($customer_id)
    {
        $sql = "SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.price, p.image_url, p.stock_quantity, p.status
                FROM cart c
                JOIN products p ON c.product_id = p.product_id
                WHERE c.customer_id = ?
                ORDER BY c.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customer_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($items)) {
            require_once __DIR__ . '/product_model.php';
            $productModel = new ProductModel();
            $productModel->applyPromotions($items);
        }

        return $items;
    }

    public function findCartItem($customer_id, $product_id)
    {
        $sql = "SELECT * FROM cart WHERE customer_id = ? AND product_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customer_id, $product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addCart($customer_id, $product_id, $quantity)
    {
        $sql = "INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$customer_id, $product_id, $quantity]);
    }

    // Luôn kèm customer_id: chỉ sửa được giỏ của chính mình
    public function updateCartQuantity($cart_id, $customer_id, $quantity)
    {
        $sql = "UPDATE cart SET quantity = ? WHERE cart_id = ? AND customer_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$quantity, $cart_id, $customer_id]);
    }

    // 1 dòng giỏ hàng của khách + tồn kho / trạng thái sản phẩm (null nếu không thuộc khách này)
    public function getCartItem($cart_id, $customer_id)
    {
        $sql = "SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.stock_quantity, p.status
                FROM cart c JOIN products p ON c.product_id = p.product_id
                WHERE c.cart_id = ? AND c.customer_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cart_id, $customer_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Kiểm tra giỏ trước khi đặt: trả về danh sách lỗi theo product_id (rỗng = đặt được).
     * Dùng chung cho trang giỏ hàng, trang đặt hàng và lúc đặt.
     */
    public static function cartProblems(array $items)
    {
        $problems = [];
        foreach ($items as $item) {
            $stock = (int) $item['stock_quantity'];
            if (($item['status'] ?? 'available') === 'discontinued') {
                $problems[$item['product_id']] = 'Sản phẩm đã ngừng kinh doanh';
            } elseif (($item['status'] ?? 'available') !== 'available' || $stock <= 0) {
                $problems[$item['product_id']] = 'Tạm hết hàng';
            } elseif ($item['quantity'] > $stock) {
                $problems[$item['product_id']] = 'Chỉ còn ' . $stock . ' sản phẩm';
            }
        }
        return $problems;
    }

    public function deleteCart($cart_id, $customer_id)
    {
        $sql = "DELETE FROM cart WHERE cart_id = ? AND customer_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$cart_id, $customer_id]);
    }

    public function clearCart($customer_id)
    {
        $sql = "DELETE FROM cart WHERE customer_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$customer_id]);
    }

    public function getProductById($product_id)
    {
        $sql = "SELECT * FROM products WHERE product_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCartCount($customer_id)
    {
        $sql = "SELECT SUM(quantity) as total_items FROM cart WHERE customer_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customer_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_items'] ? (int)$result['total_items'] : 0;
    }
}
