<?php
/**
 * Chi tiết đơn hàng. Biến từ OrderController::viewDetail():
 * @var array $order
 * @var array $orderDetails
 */
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float) $v, 0, ',', '.') . 'đ';

$status = $order['order_status'];
$isCancelled = $status === 'cancelled';
$canCancel = in_array($status, OrderHelper::CUSTOMER_CANCELLABLE, true);

// Thanh tiến trình: vị trí bước hiện tại
$steps = [
    'pending'   => ['Đặt hàng', 'bi-receipt'],
    'confirmed' => ['Xác nhận', 'bi-check2-circle'],
    'shipping'  => ['Đang giao', 'bi-truck'],
    'completed' => ['Hoàn thành', 'bi-flower1'],
];
$stepKeys = array_keys($steps);
$currentStep = array_search($status, $stepKeys, true);

$itemsTotal = array_sum(array_column($orderDetails, 'subtotal'));
$itemsQty = array_sum(array_column($orderDetails, 'quantity'));
$paymentStatus = $order['payment_status'] ?? 'unpaid';
$payClass = ['paid' => 'os-completed', 'refunded' => 'os-confirmed'][$paymentStatus] ?? 'os-pending';
$listUrl = 'index.php?router=customers&controller=order&action=listOrders';
?>
<style>
    .od-page {
        max-width: 1100px;
        padding-top: 1.5rem;
    }

    .od-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .88rem;
        font-weight: 600;
        color: var(--text-muted);
        text-decoration: none;
        margin-bottom: .75rem;
    }

    .od-back:hover {
        color: var(--primary-color);
    }

    .od-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }

    .od-title {
        font-size: 1.75rem;
        font-weight: 800;
        letter-spacing: -.5px;
        color: #3a2c23;
        margin: 0;
    }

    .od-date {
        font-size: .88rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .od-card {
        background: var(--card-bg);
        border: 1px solid #f3e9e2;
        border-radius: 20px;
        box-shadow: var(--shadow-sm);
        padding: 1.4rem;
        margin-bottom: 1.25rem;
    }

    /* Thẻ cuối mỗi cột: để khoảng cách của .row lo (tránh hở gấp đôi khi xếp dọc) */
    .od-col > .od-card:last-child {
        margin-bottom: 0;
    }

    .od-card-title {
        font-size: 1rem;
        font-weight: 800;
        color: #3a2c23;
        margin: 0 0 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .od-card-title i {
        color: var(--primary-color);
    }

    /* ---------- Tiến trình ---------- */
    .od-steps {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .od-step {
        position: relative;
        text-align: center;
        font-size: .8rem;
        font-weight: 600;
        color: #b3a196;
    }

    /* đường nối giữa các bước */
    .od-step:not(:first-child)::before {
        content: "";
        position: absolute;
        top: 19px;
        right: 50%;
        width: 100%;
        height: 3px;
        background: #f0e4dd;
        z-index: 0;
    }

    .od-step.done:not(:first-child)::before,
    .od-step.current:not(:first-child)::before {
        background: var(--primary-color);
    }

    .od-dot {
        position: relative;
        z-index: 1;
        width: 40px;
        height: 40px;
        margin: 0 auto 6px;
        border-radius: 50%;
        background: #f6eee9;
        color: #b3a196;
        display: grid;
        place-items: center;
        font-size: 1.1rem;
        border: 3px solid #fff;
    }

    .od-step.done .od-dot {
        background: #fde8ef;
        color: var(--primary-color);
    }

    .od-step.done,
    .od-step.current {
        color: var(--text-main);
    }

    .od-step.current .od-dot {
        background: var(--primary-color);
        color: #fff;
        box-shadow: 0 6px 16px rgba(233, 30, 99, .3);
    }

    .od-cancelled {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fdecec;
        color: #b42318;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: .9rem;
    }

    .od-cancelled i {
        font-size: 1.4rem;
    }

    /* ---------- Sản phẩm ---------- */
    .od-item {
        display: grid;
        grid-template-columns: 64px minmax(0, 1fr) auto;
        gap: 14px;
        align-items: center;
        padding: .85rem 0;
        border-bottom: 1px solid #f6eee9;
    }

    .od-item:first-child {
        padding-top: 0;
    }

    .od-item:last-child {
        border-bottom: 0;
    }

    .od-item img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 12px;
        background: #faf5f0;
        display: block;
    }

    .od-item-name {
        display: block;
        font-weight: 700;
        font-size: .95rem;
        color: var(--text-main);
        text-decoration: none;
        line-height: 1.35;
    }

    a.od-item-name:hover {
        color: var(--primary-color);
    }

    .od-item-unit {
        font-size: .8rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .od-item-sub {
        font-weight: 700;
        white-space: nowrap;
    }

    .od-sum {
        border-top: 1px dashed #ead8cf;
        margin-top: .5rem;
        padding-top: .9rem;
    }

    .od-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: .92rem;
        color: var(--text-muted);
        margin-bottom: .5rem;
    }

    .od-total {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--text-main);
        margin-top: .6rem;
    }

    .od-total span:last-child {
        color: var(--primary-color);
    }

    /* ---------- Thông tin bên phải ---------- */
    .od-info dt {
        font-size: .75rem;
        font-weight: 700;
        color: #a08b7d;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 2px;
    }

    .od-info dd {
        font-size: .93rem;
        color: var(--text-main);
        margin-bottom: .9rem;
    }

    .od-info dd:last-child {
        margin-bottom: 0;
    }

    .od-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .od-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: .8rem;
        border-radius: 14px;
        font-weight: 700;
        font-size: .92rem;
        text-decoration: none;
        border: 1.5px solid transparent;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }

    .od-btn-primary {
        background: var(--primary-gradient);
        color: #fff;
    }

    .od-btn-primary:hover {
        color: #fff;
        box-shadow: 0 8px 18px rgba(233, 30, 99, .25);
    }

    .od-btn-danger {
        background: #fff;
        color: #c62828;
        border-color: #f5c2c2;
    }

    .od-btn-danger:hover {
        background: #c62828;
        color: #fff;
        border-color: #c62828;
    }

    .od-hint {
        font-size: .78rem;
        color: var(--text-muted);
        text-align: center;
        margin: 0;
    }

    /* Nhãn trạng thái */
    .os-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 700;
        white-space: nowrap;
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
    .os-shipping { background: #f3e5f5; color: #7b1fa2; }
    .os-completed { background: #e8f5e9; color: #2e7d32; }
    .os-cancelled { background: #fdecec; color: #c62828; }

    .sticky-lg {
        position: sticky;
        top: calc(var(--sh-header-h) + 20px);
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: none; }
    }

    .animate-fade-up {
        animation: fadeUp .3s cubic-bezier(.2, 0, 0, 1) both;
    }

    @media (max-width: 991.98px) {
        .sticky-lg {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        .od-page {
            padding-top: 1rem;
        }

        .od-title {
            font-size: 1.3rem;
        }

        .od-card {
            padding: 1.1rem;
            border-radius: 18px;
            margin-bottom: 1rem;
        }

        .od-dot {
            width: 34px;
            height: 34px;
            font-size: .95rem;
        }

        .od-step:not(:first-child)::before {
            top: 16px;
        }

        .od-step {
            font-size: .7rem;
        }

        .od-item {
            grid-template-columns: 56px minmax(0, 1fr) auto;
            gap: 12px;
        }

        .od-item img {
            width: 56px;
            height: 56px;
        }

        .od-item-name {
            font-size: .88rem;
        }
    }
</style>

<div class="container od-page mb-5">
    <a href="<?= $listUrl ?>" class="od-back" data-nav-back><i class="bi bi-arrow-left"></i> Đơn hàng của tôi</a>

    <div class="od-head animate-fade-up">
        <div>
            <h1 class="od-title">Đơn hàng <?= $e($order['order_code']) ?></h1>
            <div class="od-date">Đặt lúc <?= date('H:i · d/m/Y', strtotime($order['order_date'])) ?></div>
        </div>
        <span class="os-badge os-<?= $e($status) ?>"><?= $e(OrderHelper::statusLabel($status)) ?></span>
    </div>

    <div class="row g-4">
        <div class="col-lg-8 od-col">
            <!-- Tiến trình đơn -->
            <div class="od-card animate-fade-up">
                <?php if ($isCancelled): ?>
                    <div class="od-cancelled" role="status">
                        <i class="bi bi-x-circle-fill"></i>
                        <div><strong>Đơn hàng đã bị hủy.</strong> Nếu cần hỗ trợ, vui lòng liên hệ cửa hàng.</div>
                    </div>
                <?php else: ?>
                    <ol class="od-steps" aria-label="Tiến trình đơn hàng">
                        <?php foreach ($stepKeys as $i => $key):
                            $cls = $currentStep === false ? '' : ($i < $currentStep ? 'done' : ($i === $currentStep ? 'current' : ''));
                            [$label, $icon] = $steps[$key];
                        ?>
                            <li class="od-step <?= $cls ?>" <?= $cls === 'current' ? 'aria-current="step"' : '' ?>>
                                <span class="od-dot"><i class="bi <?= $icon ?>"></i></span>
                                <?= $e($label) ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>

            <!-- Sản phẩm -->
            <div class="od-card animate-fade-up" style="animation-delay: 20ms">
                <h2 class="od-card-title"><i class="bi bi-bag"></i> Sản phẩm (<?= (int) $itemsQty ?>)</h2>
                <?php foreach ($orderDetails as $item):
                    // Sản phẩm còn bán -> tên bấm được để xem lại / mua lại
                    $linkable = ($item['product_status'] ?? '') !== 'discontinued';
                    $productUrl = 'index.php?router=customers&controller=detail&action=index&id=' . (int) $item['product_id'];
                ?>
                    <div class="od-item">
                        <img src="<?= $e(productImageUrl($item['image_url'], '200x200')) ?>" alt="" loading="lazy"
                            onerror="this.src='https://placehold.co/100x100?text=Flower'">
                        <div style="min-width:0">
                            <?php if ($linkable): ?>
                                <a href="<?= $productUrl ?>" class="od-item-name"><?= $e($item['product_name']) ?></a>
                            <?php else: ?>
                                <span class="od-item-name"><?= $e($item['product_name']) ?></span>
                            <?php endif; ?>
                            <div class="od-item-unit"><?= (int) $item['quantity'] ?> × <?= $money($item['unit_price']) ?></div>
                        </div>
                        <div class="od-item-sub"><?= $money($item['subtotal']) ?></div>
                    </div>
                <?php endforeach; ?>

                <div class="od-sum">
                    <div class="od-row"><span>Tiền hàng</span><span><?= $money($itemsTotal) ?></span></div>
                    <div class="od-row"><span>Phí vận chuyển</span><span class="text-success">Miễn phí</span></div>
                    <div class="od-total"><span>Tổng cộng</span><span><?= $money($order['total_amount']) ?></span></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sticky-lg">
                <div class="od-card animate-fade-up" style="animation-delay: 40ms">
                    <h2 class="od-card-title"><i class="bi bi-geo-alt"></i> Giao đến</h2>
                    <dl class="od-info mb-0">
                        <dt>Người nhận</dt>
                        <dd><?= $e($order['receiver_name']) ?> · <?= $e($order['receiver_phone']) ?></dd>
                        <dt>Địa chỉ</dt>
                        <dd><?= $e($order['delivery_address']) ?></dd>
                        <?php if (trim((string) $order['note']) !== ''): ?>
                            <dt>Ghi chú</dt>
                            <dd class="fst-italic"><?= nl2br($e($order['note'])) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>

                <div class="od-card animate-fade-up" style="animation-delay: 60ms">
                    <h2 class="od-card-title"><i class="bi bi-wallet2"></i> Thanh toán</h2>
                    <dl class="od-info mb-0">
                        <dt>Phương thức</dt>
                        <dd><?= $e(OrderHelper::PAYMENT_METHOD_LABELS[$order['payment_method']] ?? $order['payment_method']) ?></dd>
                        <dt>Trạng thái</dt>
                        <dd><span class="os-badge <?= $payClass ?>"><?= $e(OrderHelper::PAYMENT_STATUS_LABELS[$paymentStatus] ?? $paymentStatus) ?></span></dd>
                    </dl>
                </div>

                <div class="od-actions animate-fade-up" style="animation-delay: 80ms">
                    <?php if ($canCancel): ?>
                        <!-- Huỷ đơn: POST + hộp xác nhận (popup.js); server kiểm tra lại trạng thái -->
                        <form action="index.php?router=customers&controller=order&action=cancelOrder" method="POST" class="m-0">
                            <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                            <button type="submit" class="od-btn od-btn-danger"
                                data-confirm="Đơn <?= $e($order['order_code']) ?> sẽ bị hủy và không thể khôi phục."
                                data-confirm-title="Hủy đơn hàng?" data-confirm-variant="danger"
                                data-confirm-ok="Hủy đơn" data-confirm-cancel="Không, giữ lại">
                                <i class="bi bi-x-circle"></i> Hủy đơn hàng
                            </button>
                        </form>
                        <p class="od-hint">Bạn có thể hủy khi cửa hàng chưa xác nhận đơn.</p>
                    <?php endif; ?>
                    <a href="index.php?router=customers" class="od-btn od-btn-primary"><i class="bi bi-bag-heart"></i> Tiếp tục mua sắm</a>
                </div>
            </div>
        </div>
    </div>
</div>
