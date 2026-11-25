<?php
class OrderHelper
{
    public static function randomOrderCode()
    {
        return $code = 'ORD' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    // Nhãn hiển thị cho khách (trang Đơn hàng của tôi / Chi tiết đơn hàng)
    const STATUS_LABELS = [
        'pending'   => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'shipping'  => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    const PAYMENT_METHOD_LABELS = [
        'cod'           => 'Thanh toán khi nhận hàng (COD)',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'e_wallet'      => 'Ví điện tử',
    ];

    const PAYMENT_STATUS_LABELS = [
        'unpaid'   => 'Chưa thanh toán',
        'paid'     => 'Đã thanh toán',
        'refunded' => 'Đã hoàn tiền',
    ];

    // Khách chỉ tự huỷ được khi shop chưa xác nhận đơn
    const CUSTOMER_CANCELLABLE = ['pending'];

    public static function statusLabel($status)
    {
        return self::STATUS_LABELS[$status] ?? (string) $status;
    }
}
