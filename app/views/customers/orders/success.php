<?php
/**
 * Đặt hàng thành công. Biến từ OrderController::success():
 * @var array $order
 * @var array $orderDetails
 */
$orderDetails = $orderDetails ?? [];
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float) $v, 0, ',', '.') . 'đ';

$itemsTotal = array_sum(array_column($orderDetails, 'subtotal'));
$itemsQty = array_sum(array_column($orderDetails, 'quantity'));
$paymentStatus = $order['payment_status'] ?? 'unpaid';
$payClass = ['paid' => 'os-completed', 'refunded' => 'os-confirmed'][$paymentStatus] ?? 'os-pending';
$ordersUrl = 'index.php?router=customers&controller=order&action=listOrders';
?>
<style>
    .sc-page {
        max-width: 640px;
        padding-top: 2.5rem;
    }

    .sc-top {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .sc-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: #e8f5e9;
        color: #2e7d32;
        display: grid;
        place-items: center;
        font-size: 2.6rem;
        animation: sc-pop .5s cubic-bezier(.175, .885, .32, 1.275) both;
    }

    @keyframes sc-pop {
        from { transform: scale(0); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .sc-title {
        font-size: 1.75rem;
        font-weight: 800;
        letter-spacing: -.5px;
        color: #3a2c23;
        margin: 0 0 .5rem;
    }

    .sc-sub {
        color: var(--text-muted);
        font-size: .95rem;
        margin: 0 auto;
        max-width: 420px;
        line-height: 1.6;
    }

    .sc-code {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 1rem;
        padding: 8px 18px;
        border-radius: 999px;
        background: #fff;
        border: 1px dashed var(--primary-color);
        color: var(--primary-color);
        font-weight: 800;
        font-size: .95rem;
    }

    .sc-card {
        background: var(--card-bg);
        border: 1px solid #f3e9e2;
        border-radius: 20px;
        box-shadow: var(--shadow-sm);
        padding: 1.4rem;
        margin-bottom: 1.25rem;
    }

    .sc-card-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
        font-weight: 800;
        color: #3a2c23;
        margin: 0 0 1rem;
    }

    .sc-card-title i {
        color: var(--primary-color);
    }

    /* ---------- Sản phẩm ---------- */
    .sc-item {
        display: grid;
        grid-template-columns: 56px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        padding: .7rem 0;
        border-bottom: 1px solid #f6eee9;
    }

    .sc-item:first-child { padding-top: 0; }
    .sc-item:last-child { border-bottom: 0; }

    .sc-item img {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border-radius: 10px;
        background: #faf5f0;
        display: block;
    }

    .sc-item-name {
        font-weight: 700;
        font-size: .9rem;
        line-height: 1.35;
        color: var(--text-main);
    }

    .sc-item-unit {
        font-size: .8rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .sc-item-sub {
        font-weight: 700;
        font-size: .9rem;
        white-space: nowrap;
    }

    .sc-sum {
        border-top: 1px dashed #ead8cf;
        margin-top: .6rem;
        padding-top: .9rem;
    }

    .sc-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: .92rem;
        color: var(--text-muted);
        margin-bottom: .5rem;
    }

    .sc-total {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--text-main);
        margin-top: .6rem;
    }

    .sc-total span:last-child {
        color: var(--primary-color);
    }

    /* ---------- Giao đến / Thanh toán ---------- */
    .sc-info dt {
        font-size: .75rem;
        font-weight: 700;
        color: #a08b7d;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 2px;
    }

    .sc-info dd {
        font-size: .93rem;
        color: var(--text-main);
        margin-bottom: .9rem;
    }

    .sc-info dd:last-child { margin-bottom: 0; }

    .os-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 11px;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
    }

    .os-badge::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .os-pending { background: #fff8e1; color: #b45309; }
    .os-confirmed { background: #e3f2fd; color: #1565c0; }
    .os-completed { background: #e8f5e9; color: #2e7d32; }

    /* ---------- Nút ---------- */
    .sc-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .sc-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: .85rem;
        border-radius: 14px;
        font-weight: 700;
        font-size: .95rem;
        text-decoration: none;
        border: 1.5px solid transparent;
        transition: transform .2s ease, box-shadow .2s ease, background .15s ease, color .15s ease;
    }

    .sc-btn-primary {
        background: var(--primary-gradient);
        color: #fff;
    }

    .sc-btn-primary:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(233, 30, 99, .25);
    }

    .sc-btn-outline {
        background: #fff;
        color: var(--text-main);
        border-color: #ecdcd2;
    }

    .sc-btn-outline:hover {
        border-color: var(--accent-color);
        color: var(--primary-color);
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: none; }
    }

    .animate-fade-up {
        animation: fadeUp .5s cubic-bezier(.2, 0, 0, 1) both;
    }

    @media (max-width: 767.98px) {
        .sc-page { padding-top: 1.25rem; }
        .sc-title { font-size: 1.4rem; }
        .sc-card { padding: 1.1rem; border-radius: 18px; }
    }
</style>

<div class="container sc-page mb-5">
    <div class="sc-top animate-fade-up">
        <div class="sc-icon"><i class="bi bi-check2"></i></div>
        <h1 class="sc-title">Đặt hàng thành công!</h1>
        <p class="sc-sub">Cảm ơn bạn đã tin tưởng Góc Nhỏ Có Hoa. Chúng tôi sẽ sớm chuẩn bị những đóa hoa tươi đẹp nhất để gửi đến bạn.</p>
        <div class="sc-code"><i class="bi bi-receipt"></i> <?= $e($order['order_code']) ?></div>
    </div>

    <!-- Sản phẩm -->
    <div class="sc-card animate-fade-up" style="animation-delay: 60ms">
        <h2 class="sc-card-title"><i class="bi bi-bag-heart"></i> Đơn hàng của bạn (<?= (int) $itemsQty ?> sản phẩm)</h2>
        <?php foreach ($orderDetails as $item): ?>
            <div class="sc-item">
                <img src="<?= $e(productImageUrl($item['image_url'], '200x200')) ?>" alt="" loading="lazy"
                    onerror="this.src='https://placehold.co/100x100?text=Flower'">
                <div style="min-width:0">
                    <div class="sc-item-name"><?= $e($item['product_name']) ?></div>
                    <div class="sc-item-unit"><?= (int) $item['quantity'] ?> × <?= $money($item['unit_price']) ?></div>
                </div>
                <div class="sc-item-sub"><?= $money($item['subtotal']) ?></div>
            </div>
        <?php endforeach; ?>

        <div class="sc-sum">
            <div class="sc-row"><span>Tiền hàng</span><span><?= $money($itemsTotal) ?></span></div>
            <div class="sc-row"><span>Phí vận chuyển</span><span class="text-success">Miễn phí</span></div>
            <div class="sc-total"><span>Tổng cộng</span><span><?= $money($order['total_amount']) ?></span></div>
        </div>
    </div>

    <!-- Giao đến -->
    <div class="sc-card animate-fade-up" style="animation-delay: 100ms">
        <h2 class="sc-card-title"><i class="bi bi-geo-alt"></i> Giao đến</h2>
        <dl class="sc-info mb-0">
            <dt>Người nhận</dt>
            <dd><?= $e($order['receiver_name']) ?> · <?= $e($order['receiver_phone']) ?></dd>
            <dt>Địa chỉ</dt>
            <dd><?= $e($order['delivery_address']) ?></dd>
            <?php if (trim((string) ($order['note'] ?? '')) !== ''): ?>
                <dt>Ghi chú</dt>
                <dd class="fst-italic"><?= nl2br($e($order['note'])) ?></dd>
            <?php endif; ?>
            <dt>Thanh toán</dt>
            <dd>
                <?= $e(OrderHelper::PAYMENT_METHOD_LABELS[$order['payment_method']] ?? $order['payment_method']) ?>
                <span class="os-badge <?= $payClass ?> ms-1"><?= $e(OrderHelper::PAYMENT_STATUS_LABELS[$paymentStatus] ?? $paymentStatus) ?></span>
            </dd>
        </dl>
    </div>

    <div class="sc-actions animate-fade-up" style="animation-delay: 140ms">
        <a href="<?= $ordersUrl ?>" class="sc-btn sc-btn-primary"><i class="bi bi-receipt"></i> Xem đơn hàng của tôi</a>
        <a href="index.php?router=customers" class="sc-btn sc-btn-outline"><i class="bi bi-bag"></i> Tiếp tục mua sắm</a>
    </div>
</div>
