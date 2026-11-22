<?php
class DashboardModel
{
    private $pdo;
    public function __construct()
    {
        $this->pdo = pdo_connect();
    }

    // Khoảng ngày [from 00:00:00, to + 1 ngày) để lấy trọn ngày cuối
    private function rangeParams($from, $to)
    {
        return [
            ':from' => $from . ' 00:00:00',
            ':to'   => date('Y-m-d', strtotime($to . ' +1 day')) . ' 00:00:00',
        ];
    }

    // Số tổng trong khoảng ngày. Doanh thu = mọi đơn trừ đơn đã hủy
    public function getSummary($from, $to)
    {
        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN order_status <> 'cancelled' THEN total_amount END), 0) AS revenue,
                    COUNT(*) AS total_orders,
                    COALESCE(SUM(order_status = 'cancelled'), 0) AS cancelled_orders
                FROM orders
                WHERE order_date >= :from AND order_date < :to";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->rangeParams($from, $to));
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT COUNT(*) FROM customers WHERE created_at >= :from AND created_at < :to";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->rangeParams($from, $to));
        $summary['new_customers'] = (int) $stmt->fetchColumn();

        // Đơn chờ xác nhận: lấy tất cả (không lọc ngày) vì đây là việc cần xử lý ngay
        $summary['pending_orders'] = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")
            ->fetchColumn();

        return $summary;
    }

    public function getLatestOrders($from, $to, $limit = 10)
    {
        $limit = (int) $limit;
        $sql = "SELECT o.order_id, o.order_code, o.total_amount, o.order_status, o.order_date,
                       cus.full_name AS customer_name
                FROM orders o
                JOIN customers cus ON o.customer_id = cus.customer_id
                WHERE o.order_date >= :from AND o.order_date < :to
                ORDER BY o.order_date DESC, o.order_id DESC
                LIMIT $limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->rangeParams($from, $to));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Bán chạy: tính trên các đơn không bị hủy trong khoảng ngày
    public function getTopProducts($from, $to, $limit = 5)
    {
        $limit = (int) $limit;
        $sql = "SELECT p.product_id, p.product_name, p.image_url,
                       SUM(od.quantity) AS sold_quantity,
                       SUM(od.subtotal) AS revenue
                FROM order_details od
                JOIN orders o ON od.order_id = o.order_id
                JOIN products p ON od.product_id = p.product_id
                WHERE o.order_status <> 'cancelled'
                  AND o.order_date >= :from AND o.order_date < :to
                GROUP BY p.product_id, p.product_name, p.image_url
                ORDER BY sold_quantity DESC, revenue DESC
                LIMIT $limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->rangeParams($from, $to));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Sắp hết hàng: tồn kho <= ngưỡng, bỏ qua sản phẩm đã ngừng kinh doanh
    public function getLowStockProducts($threshold = 5)
    {
        $sql = "SELECT p.product_id, p.product_name, p.image_url, p.stock_quantity, p.status,
                       c.category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.stock_quantity <= ? AND p.status <> 'discontinued'
                ORDER BY p.stock_quantity ASC, p.product_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int) $threshold]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
