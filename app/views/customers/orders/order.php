<?php
/**
 * Đặt hàng (thông tin giao hàng). Biến từ OrderController::index():
 * @var array $listCart
 * @var array $customer
 * @var array $old          dữ liệu đã nhập ở lần gửi trước (khi server báo lỗi)
 * @var array $fieldErrors  lỗi từng ô do server trả về
 */
$old = $old ?? [];
$fieldErrors = $fieldErrors ?? [];
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float) $v, 0, ',', '.') . 'đ';

$total = 0;
$saved = 0;
$totalQty = 0;
foreach ($listCart as $item) {
    $total += ($item['discounted_price'] ?? $item['price']) * $item['quantity'];
    $totalQty += $item['quantity'];
    if (isset($item['discounted_price'])) $saved += ($item['price'] - $item['discounted_price']) * $item['quantity'];
}

// Giá trị ô: ưu tiên dữ liệu vừa nhập, sau đó tới thông tin tài khoản
$val = fn($key, $fallback = '') => $old[$key] ?? $fallback;
$err = fn($key) => $fieldErrors[$key] ?? '';
$invalid = fn($key) => isset($fieldErrors[$key]) ? ' is-invalid' : '';
$cartUrl = 'index.php?router=customers&controller=cart&action=listCart';
?>
<style>
    .checkout-container {
        margin-top: 2rem;
    }

    .co-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }

    .co-title {
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -.5px;
        color: #3a2c23;
        margin: 0;
    }

    .co-back {
        font-size: .9rem;
        font-weight: 700;
        color: var(--primary-color);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .co-back:hover {
        text-decoration: underline;
    }

    .checkout-card {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid #f3e9e2;
    }

    .co-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.1rem;
        font-weight: 800;
        color: #3a2c23;
        margin: 0 0 1.1rem;
    }

    .co-step {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #fde8ef;
        color: var(--primary-color);
        font-size: .8rem;
        font-weight: 800;
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }

    .checkout-card .form-label {
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: .4rem;
        font-size: .88rem;
    }

    .checkout-card .form-label .opt {
        font-weight: 500;
        color: var(--text-muted);
    }

    .checkout-card .form-control {
        border-radius: 12px;
        padding: .7rem .95rem;
        border: 1px solid #ecdcd2;
        background-color: #fff;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .checkout-card .form-control:focus {
        box-shadow: 0 0 0 4px rgba(224, 90, 126, .12);
        border-color: var(--accent-color);
    }

    .co-divider {
        border: 0;
        border-top: 1px solid #f3e9e2;
        margin: 1.75rem 0;
        opacity: 1;
    }

    /* ---------- Thanh toán ---------- */
    .payment-method-card {
        border: 1.5px solid #eee3dc;
        border-radius: 14px;
        padding: .85rem 1rem;
        margin-bottom: .7rem;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: border-color .2s ease, background .2s ease;
    }

    .payment-method-card .pm-icon {
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .payment-method-card.is-selectable {
        cursor: pointer;
    }

    .payment-method-card.active {
        border-color: var(--primary-color);
        background: #fff7f9;
    }

    .payment-method-card.is-disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    .payment-check {
        margin-left: auto;
        color: var(--primary-color);
        font-size: 1.25rem;
        visibility: hidden;
    }

    .payment-method-card.active .payment-check {
        visibility: visible;
    }

    .pm-soon {
        font-size: .62rem;
        font-weight: 700;
        background: #ece7e3;
        color: #7c6f66;
        padding: 2px 7px;
        border-radius: 999px;
        margin-left: 6px;
        vertical-align: middle;
    }

    /* ---------- Tóm tắt ---------- */
    .summary-sticky {
        position: sticky;
        top: calc(var(--sh-header-h) + 20px);
    }

    .order-items {
        max-height: 320px;
        overflow-y: auto;
        margin: 0 -4px 1rem 0;
        padding-right: 4px;
        scrollbar-width: thin;
        scrollbar-color: #f3d3de transparent;
    }

    .order-summary-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: .75rem 0;
        border-bottom: 1px solid #f3e9e2;
    }

    .order-summary-item:last-child {
        border-bottom: none;
    }

    .order-summary-img-wrap {
        position: relative;
        flex-shrink: 0;
    }

    .order-summary-img {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border-radius: 12px;
        display: block;
        background: #faf5f0;
    }

    .order-qty {
        position: absolute;
        top: -6px;
        right: -6px;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        border-radius: 999px;
        background: #6b5a4c;
        color: #fff;
        font-size: .68rem;
        font-weight: 800;
        line-height: 20px;
        text-align: center;
        box-shadow: 0 0 0 2px #fff;
    }

    .order-item-name {
        font-weight: 600;
        font-size: .88rem;
        line-height: 1.35;
        color: var(--text-main);
    }

    .order-item-unit {
        font-size: .78rem;
        color: var(--text-muted);
    }

    .order-item-sub {
        font-weight: 700;
        font-size: .88rem;
        white-space: nowrap;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: .65rem;
        font-size: .92rem;
        color: var(--text-muted);
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px dashed #ead8cf;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-main);
    }

    .btn-place-order {
        background: var(--primary-gradient);
        color: #fff;
        border: none;
        padding: .95rem;
        border-radius: 14px;
        font-weight: 800;
        font-size: 1rem;
        width: 100%;
        margin-top: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease;
    }

    .btn-place-order:hover:not([aria-busy="true"]) {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(224, 90, 126, .25);
        color: #fff;
    }

    /* Đang gửi (admin.js đặt aria-busy) -> không bấm lại được */
    .btn-place-order[aria-busy="true"] {
        opacity: .75;
        pointer-events: none;
    }

    .co-terms {
        font-size: .78rem;
        color: var(--text-muted);
        text-align: center;
        margin: .7rem 0 0;
    }

    .co-bar {
        display: none;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-up {
        animation: fadeUp .3s cubic-bezier(.2, 0, 0, 1) both;
    }

    @media (max-width: 991.98px) {
        .summary-sticky {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        .checkout-container {
            margin-top: 1.25rem;
        }

        .co-title {
            font-size: 1.45rem;
        }

        .checkout-card {
            padding: 1.25rem;
            border-radius: 20px;
        }

        .order-items {
            max-height: none;
        }

        /* Nút đặt hàng đã có ở thanh dính đáy */
        .summary-sticky .btn-place-order,
        .summary-sticky .co-terms {
            display: none;
        }

        .co-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: sticky;
            bottom: calc(var(--sh-tab-h) + env(safe-area-inset-bottom) + 10px);
            z-index: 5;
            margin-top: 1rem;
            background: #fff;
            border: 1px solid #f3e1e8;
            border-radius: 18px;
            padding: 10px 10px 10px 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .1);
        }

        .co-bar .label {
            font-size: .75rem;
            color: var(--text-muted);
        }

        .co-bar .amount {
            font-weight: 800;
            font-size: 1.15rem;
            color: var(--primary-color);
            line-height: 1.2;
        }

        .co-bar .btn-place-order {
            width: auto;
            margin: 0;
            padding: .8rem 1.2rem;
            font-size: .9rem;
        }
    }
</style>

<div class="container checkout-container mb-5">
    <div class="co-head animate-fade-up">
        <h1 class="co-title">Thông tin đặt hàng</h1>
        <a href="<?= $cartUrl ?>" class="co-back" data-nav-back><i class="bi bi-arrow-left"></i> Quay lại giỏ hàng</a>
    </div>

    <form action="index.php?router=customers&controller=order&action=placeOrder" method="POST" data-validate id="checkoutForm">
        <div class="row g-4">
            <div class="col-lg-7 animate-fade-up">
                <div class="checkout-card">
                    <h2 class="co-section-title"><span class="co-step">1</span> Thông tin người nhận</h2>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="receiver_name">Họ và tên</label>
                            <input type="text" id="receiver_name" name="receiver_name" class="form-control<?= $invalid('receiver_name') ?>" autocomplete="name"
                                value="<?= $e($val('receiver_name', $customer['full_name'] ?? '')) ?>" data-label="họ tên người nhận" data-rules="required|max:100">
                            <div class="sh-error" data-error-for="receiver_name"><?= $e($err('receiver_name')) ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="receiver_phone">Số điện thoại</label>
                            <input type="tel" id="receiver_phone" name="receiver_phone" class="form-control<?= $invalid('receiver_phone') ?>" autocomplete="tel" inputmode="numeric"
                                value="<?= $e($val('receiver_phone', $customer['phone'] ?? '')) ?>" placeholder="VD: 0912345678" data-label="số điện thoại" data-rules="required|phone">
                            <div class="sh-error" data-error-for="receiver_phone"><?= $e($err('receiver_phone')) ?></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="delivery_address">Địa chỉ giao hàng</label>
                            <textarea id="delivery_address" name="delivery_address" class="form-control<?= $invalid('delivery_address') ?>" rows="2" autocomplete="street-address"
                                placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành" data-label="địa chỉ giao hàng"
                                data-rules="required|min:10|max:255"><?= $e($val('delivery_address', $customer['address'] ?? '')) ?></textarea>
                            <div class="sh-error" data-error-for="delivery_address"><?= $e($err('delivery_address')) ?></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="note">Ghi chú <span class="opt">(tùy chọn)</span></label>
                            <textarea id="note" name="note" class="form-control<?= $invalid('note') ?>" rows="2" data-label="ghi chú" data-rules="max:500"
                                placeholder="VD: Giao giờ hành chính, lời nhắn ghi trên thiệp..."><?= $e($val('note')) ?></textarea>
                            <div class="sh-error" data-error-for="note"><?= $e($err('note')) ?></div>
                        </div>
                    </div>

                    <hr class="co-divider">

                    <h2 class="co-section-title"><span class="co-step">2</span> Phương thức thanh toán</h2>
                    <div class="payment-methods" role="radiogroup" aria-label="Phương thức thanh toán">
                        <label class="payment-method-card is-selectable active">
                            <input type="radio" name="payment_method" value="cod" checked class="visually-hidden">
                            <i class="bi bi-cash-stack pm-icon text-success"></i>
                            <div>
                                <div class="fw-bold">Thanh toán khi nhận hàng (COD)</div>
                                <div class="small text-muted">Nhận hoa và thanh toán tiền mặt cho shipper</div>
                            </div>
                            <i class="bi bi-check-circle-fill payment-check"></i>
                        </label>
                        <div class="payment-method-card is-disabled" aria-disabled="true">
                            <i class="bi bi-bank pm-icon text-primary"></i>
                            <div>
                                <div class="fw-bold">Chuyển khoản ngân hàng <span class="pm-soon">Sắp ra mắt</span></div>
                                <div class="small text-muted">Thanh toán qua số tài khoản ngân hàng</div>
                            </div>
                        </div>
                        <div class="payment-method-card is-disabled" aria-disabled="true">
                            <i class="bi bi-wallet2 pm-icon text-warning"></i>
                            <div>
                                <div class="fw-bold">Ví điện tử <span class="pm-soon">Sắp ra mắt</span></div>
                                <div class="small text-muted">Momo, ZaloPay, Viettel Money</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 animate-fade-up" style="animation-delay: 40ms">
                <div class="checkout-card summary-sticky">
                    <h2 class="co-section-title">Đơn hàng <span class="text-muted fw-semibold" style="font-size:.85rem">(<?= (int) $totalQty ?> sản phẩm)</span></h2>

                    <div class="order-items">
                        <?php foreach ($listCart as $item): ?>
                            <?php
                            $price = $item['discounted_price'] ?? $item['price'];
                            $subtotal = $price * $item['quantity'];
                            ?>
                            <div class="order-summary-item">
                                <div class="order-summary-img-wrap">
                                    <img src="<?= $e(productImageUrl($item['image_url'], '200x200')) ?>" class="order-summary-img" alt=""
                                        onerror="this.src='https://placehold.co/100x100?text=Flower'">
                                    <span class="order-qty" aria-label="Số lượng"><?= (int) $item['quantity'] ?></span>
                                </div>
                                <div class="flex-grow-1" style="min-width:0">
                                    <div class="order-item-name"><?= $e($item['product_name']) ?></div>
                                    <div class="order-item-unit"><?= (int) $item['quantity'] ?> × <?= $money($price) ?></div>
                                </div>
                                <div class="order-item-sub"><?= $money($subtotal) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-row">
                        <span>Tạm tính</span>
                        <span><?= $money($total + $saved) ?></span>
                    </div>
                    <?php if ($saved > 0): ?>
                        <div class="summary-row">
                            <span>Khuyến mãi</span>
                            <span class="text-success">−<?= $money($saved) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="summary-row">
                        <span>Phí vận chuyển</span>
                        <span class="text-success">Miễn phí</span>
                    </div>
                    <div class="total-row">
                        <span>Tổng cộng</span>
                        <span style="color: var(--primary-color)"><?= $money($total) ?></span>
                    </div>

                    <button type="submit" class="btn-place-order" data-busy-text="Đang đặt hàng...">
                        <i class="bi bi-bag-check"></i> Xác nhận đặt hàng
                    </button>
                    <p class="co-terms">Bạn sẽ thanh toán <?= $money($total) ?> khi nhận hoa.</p>
                </div>
            </div>
        </div>

        <!-- Mobile: tổng tiền + nút đặt hàng luôn trong tầm tay -->
        <div class="co-bar">
            <div>
                <div class="label">Tổng cộng</div>
                <div class="amount"><?= $money($total) ?></div>
            </div>
            <button type="submit" class="btn-place-order" data-busy-text="Đang đặt...">Đặt hàng <i class="bi bi-arrow-right"></i></button>
        </div>
    </form>
</div>
