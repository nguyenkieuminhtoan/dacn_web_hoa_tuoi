<?php
class OrderModel
{
    private $pdo;
    public function __construct()
    {
        $this->pdo = pdo_connect();
    }

    public function getAllOrder()
    {
        $sql = "SELECT o.*, cus.full_name AS customer_name
        FROM orders o JOIN customers cus ON o.customer_id= cus.customer_id 
        ORDER BY o.order_date DESC, o.order_id DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderPagination($limit, $offset)
    {
        $sql = "SELECT o.*, cus.full_name AS customer_name
        FROM orders o JOIN customers cus ON o.customer_id= cus.customer_id 
        ORDER BY o.customer_id DESC 
        LIMIT $limit OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByIdOrder($order_id)
    {
        $sql = "SELECT o.*, cus.full_name AS customer_name , cus.email, cus.phone, cus.address
        FROM orders o JOIN customers cus ON o.customer_id= cus.customer_id WHERE o.order_id=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$order_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByIdOrderDetail($order_id)
    {
        $sql = "SELECT od.*, p.product_name, p.image_url, p.status AS product_status
        FROM order_details od JOIN products p ON od.product_id= p.product_id WHERE od.order_id=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateOrderStatus($order_id, $order_status)
    {
        $sql = "UPDATE orders SET order_status=? WHERE order_id=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$order_status, $order_id]);
        return $stmt;
    }

    // Các trạng thái mà hàng đã bị trừ kho nhưng chưa giao xong -> huỷ / xoá thì hoàn kho
    const STOCK_HELD_STATUSES = ['pending', 'confirmed', 'shipping'];

    // Cộng lại tồn kho theo chi tiết đơn (gọi bên trong transaction)
    private function restoreStock($order_id)
    {
        $sql = "UPDATE products p
                JOIN (SELECT product_id, SUM(quantity) qty FROM order_details WHERE order_id = ? GROUP BY product_id) od
                  ON od.product_id = p.product_id
                SET p.stock_quantity = p.stock_quantity + od.qty";
        $this->pdo->prepare($sql)->execute([$order_id]);
    }

    /**
     * Huỷ đơn + hoàn tồn kho (1 transaction). Chỉ huỷ được khi đơn đang ở $allowedStatuses.
     * @return bool true nếu đã huỷ
     */
    public function cancelOrder($order_id, array $allowedStatuses = self::STOCK_HELD_STATUSES)
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("SELECT order_status FROM orders WHERE order_id = ? FOR UPDATE");
            $stmt->execute([$order_id]);
            $status = $stmt->fetchColumn();

            if ($status === false || !in_array($status, $allowedStatuses, true)) {
                $this->pdo->rollBack();
                return false;
            }

            $this->pdo->prepare("UPDATE orders SET order_status = 'cancelled' WHERE order_id = ?")->execute([$order_id]);
            if (in_array($status, self::STOCK_HELD_STATUSES, true)) {
                $this->restoreStock($order_id);
            }
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return false;
        }
    }

    public function deleteOrder($order_id)
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("SELECT order_status FROM orders WHERE order_id = ? FOR UPDATE");
            $stmt->execute([$order_id]);
            $status = $stmt->fetchColumn();
            if ($status === false) {
                $this->pdo->rollBack();
                return false;
            }

            // Xoá đơn chưa giao xong (chưa huỷ) -> trả hàng về kho trước khi xoá chi tiết
            if (in_array($status, self::STOCK_HELD_STATUSES, true)) {
                $this->restoreStock($order_id);
            }

            $this->pdo->prepare("DELETE FROM order_details WHERE order_id=?")->execute([$order_id]);
            $stmt = $this->pdo->prepare("DELETE FROM orders WHERE order_id=?");
            $stmt->execute([$order_id]);
            $this->pdo->commit();
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Đặt hàng từ giỏ (1 transaction):
     *  khoá các dòng sản phẩm -> kiểm tra còn bán + đủ hàng -> trừ kho -> tạo đơn + chi tiết -> xoá giỏ.
     * Giá lấy từ $cartItems (đã áp khuyến mãi). Lỗi giữa chừng thì rollback, không trừ kho sai.
     * @return array ['ok' => true, 'order_id' => int] | ['ok' => false, 'reason' => 'stock'|'empty'|'error', 'error' => string]
     */
    public function placeOrderFromCart($customer_id, array $cartItems, array $info)
    {
        if (!$cartItems) return ['ok' => false, 'reason' => 'empty', 'error' => 'Giỏ hàng trống!'];

        try {
            $this->pdo->beginTransaction();

            // Khoá các sản phẩm trong giỏ để 2 khách đặt cùng lúc không cùng trừ 1 lượng hàng
            $ids = array_map('intval', array_column($cartItems, 'product_id'));
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->pdo->prepare("SELECT product_id, product_name, stock_quantity, status FROM products WHERE product_id IN ($in) FOR UPDATE");
            $stmt->execute($ids);
            $locked = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $locked[$row['product_id']] = $row;

            $total = 0;
            foreach ($cartItems as $item) {
                $p = $locked[$item['product_id']] ?? null;
                if (!$p || $p['status'] !== 'available' || $p['stock_quantity'] < $item['quantity']) {
                    $this->pdo->rollBack();
                    $name = $p['product_name'] ?? $item['product_name'];
                    $left = $p ? max(0, (int) $p['stock_quantity']) : 0;
                    return ['ok' => false, 'reason' => 'stock', 'error' => "“{$name}” không đủ hàng (còn {$left}). Vui lòng cập nhật lại giỏ hàng."];
                }
                $total += ($item['discounted_price'] ?? $item['price']) * $item['quantity'];
            }

            $order_code = 'ORD-' . strtoupper(substr(uniqid(), 7));
            $stmt = $this->pdo->prepare("INSERT INTO orders (order_code, customer_id, total_amount, receiver_name, receiver_phone, delivery_address, note, payment_method, order_status, payment_status)
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid')");
            $stmt->execute([$order_code, $customer_id, $total, $info['receiver_name'], $info['receiver_phone'], $info['delivery_address'], $info['note'], $info['payment_method']]);
            $order_id = (int) $this->pdo->lastInsertId();

            $detail = $this->pdo->prepare("INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $minus = $this->pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            foreach ($cartItems as $item) {
                $price = $item['discounted_price'] ?? $item['price'];
                $detail->execute([$order_id, $item['product_id'], $item['quantity'], $price, $price * $item['quantity']]);
                $minus->execute([$item['quantity'], $item['product_id']]);
            }

            $this->pdo->prepare("DELETE FROM cart WHERE customer_id = ?")->execute([$customer_id]);
            $this->pdo->commit();
            return ['ok' => true, 'order_id' => $order_id];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['ok' => false, 'reason' => 'error', 'error' => 'Có lỗi xảy ra khi đặt hàng, vui lòng thử lại!'];
        }
    }

    public function getByOrderStatus($order_status)
    {
        $sql = "SELECT o.*, cus.full_name AS customer_name
            FROM orders o
            JOIN customers cus ON o.customer_id = cus.customer_id
            WHERE o.order_status = ?
            ORDER BY o.order_date DESC, o.order_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$order_status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createOrder($customer_id, $total_amount, $receiver_name, $receiver_phone, $delivery_address, $note, $payment_method)
    {
        $order_code = 'ORD-' . strtoupper(substr(uniqid(), 7));
        $sql = "INSERT INTO orders (order_code, customer_id, total_amount, receiver_name, receiver_phone, delivery_address, note, payment_method, order_status, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid')";
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute([$order_code, $customer_id, $total_amount, $receiver_name, $receiver_phone, $delivery_address, $note, $payment_method])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    public function createOrderDetail($order_id, $product_id, $quantity, $unit_price, $subtotal)
    {
        $sql = "INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$order_id, $product_id, $quantity, $unit_price, $subtotal]);
    }

    public function getOrdersByCustomer($customer_id)
    {
        $sql = "SELECT o.*, 
                (SELECT p.image_url FROM order_details od JOIN products p ON od.product_id = p.product_id WHERE od.order_id = o.order_id LIMIT 1) as first_product_image,
                (SELECT p.product_name FROM order_details od JOIN products p ON od.product_id = p.product_id WHERE od.order_id = o.order_id LIMIT 1) as first_product_name,
                (SELECT COUNT(*) FROM order_details WHERE order_id = o.order_id) as total_products,
                (SELECT COALESCE(SUM(quantity), 0) FROM order_details WHERE order_id = o.order_id) as total_qty
                FROM orders o
                WHERE o.customer_id = ?
                ORDER BY o.order_date DESC, o.order_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
