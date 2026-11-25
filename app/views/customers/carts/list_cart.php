<?php
/**
 * Giỏ hàng. Biến từ CartController::listCart():
 * @var array $listCart
 */
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float) $v, 0, ',', '.') . 'đ';
$total = 0;
$totalQty = 0;
$saved = 0;
foreach ($listCart as $item) {
    $total += ($item['discounted_price'] ?? $item['price']) * $item['quantity'];
    $totalQty += $item['quantity'];
    if (isset($item['discounted_price'])) $saved += ($item['price'] - $item['discounted_price']) * $item['quantity'];
}
// Món hết hàng / vượt tồn kho -> đánh dấu + khoá nút đặt hàng
$problems = CartModel::cartProblems($listCart);
$canCheckout = !$problems;
$checkoutUrl = 'index.php?router=customers&controller=order&action=index';
?>
<style>
    .cart-container {
        margin-top: 2rem;
    }

    /* ---------- Tiêu đề ---------- */
    .cart-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }

    .cart-title {
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -.5px;
        color: #3a2c23;
        margin: 0;
        display: flex;
        align-items: baseline;
        gap: 12px;
    }

    .cart-count {
        font-size: .9rem;
        font-weight: 600;
        color: var(--text-muted);
        letter-spacing: 0;
    }

    .cart-continue {
        font-size: .9rem;
        font-weight: 700;
        color: var(--primary-color);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .cart-continue:hover {
        text-decoration: underline;
    }

    .cart-alert {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        border-radius: 16px;
        padding: 14px 16px;
        margin-bottom: 1.25rem;
        font-size: .92rem;
    }

    .cart-alert i {
        font-size: 1.15rem;
        margin-top: 1px;
    }

    /* ---------- Danh sách ---------- */
    .cart-card {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        padding: .25rem 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid #f3e9e2;
    }

    .cart-item {
        border-bottom: 1px solid #f3e9e2;
        padding: 1.25rem 0;
        display: grid;
        grid-template-columns: 96px minmax(0, 1fr) auto;
        gap: 1rem 1.25rem;
        align-items: start;
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    .cart-img {
        width: 96px;
        height: 96px;
        object-fit: cover;
        border-radius: var(--radius-md);
        display: block;
        background: #faf5f0;
    }

    .cart-item.has-problem .cart-img {
        opacity: .55;
    }

    .item-info {
        display: flex;
        flex-direction: column;
        gap: .35rem;
        min-width: 0;
    }

    .cart-name {
        font-weight: 700;
        font-size: 1rem;
        color: #3a2c23;
        text-decoration: none;
        line-height: 1.35;
    }

    .cart-name:hover {
        color: var(--primary-color);
    }

    .item-price {
        font-size: .9rem;
    }

    .unit-price {
        font-weight: 700;
        color: var(--primary-color);
    }

    .old-price {
        font-size: .8rem;
        text-decoration: line-through;
        color: #b6a088;
        margin-left: 6px;
    }

    .item-problem {
        font-size: .8rem;
        font-weight: 700;
        color: #c2410c;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .qty-form {
        margin: .35rem 0 0;
    }

    .qty-control {
        display: inline-flex;
        align-items: center;
        background: #f8f1ea;
        border-radius: 12px;
        padding: 3px;
    }

    .qty-btn {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        border: none;
        background: #fff;
        color: var(--text-main);
        font-weight: 700;
        display: grid;
        place-items: center;
        transition: background .15s ease, color .15s ease;
    }

    .qty-btn:hover:not(:disabled) {
        background: var(--accent-color);
        color: #fff;
    }

    .qty-btn:disabled {
        opacity: .4;
    }

    .qty-input {
        width: 42px;
        border: none;
        background: transparent;
        text-align: center;
        font-weight: 700;
        -moz-appearance: textfield;
        appearance: textfield;
    }

    .qty-input:focus {
        outline: none;
    }

    .qty-input::-webkit-inner-spin-button,
    .qty-input::-webkit-outer-spin-button {
        -webkit-appearance: none;
    }

    .item-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: space-between;
        gap: .75rem;
        align-self: stretch;
    }

    .item-subtotal {
        font-weight: 800;
        font-size: 1.05rem;
        white-space: nowrap;
    }

    .btn-remove {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        background: #fff0f0;
        color: #d63031;
        display: grid;
        place-items: center;
        transition: background .15s ease, color .15s ease;
    }

    .btn-remove:hover {
        background: #d63031;
        color: #fff;
    }

    /* ---------- Tóm tắt ---------- */
    .summary-card {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
        border: 1px solid #f3e9e2;
        position: sticky;
        top: calc(var(--sh-header-h) + 20px);
    }

    .summary-title {
        font-size: 1.1rem;
        font-weight: 800;
        margin-bottom: 1.1rem;
        color: #3a2c23;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: .7rem;
        font-size: .93rem;
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

    .btn-checkout {
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
        text-decoration: none;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .btn-checkout:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(224, 90, 126, .25);
        color: #fff;
    }

    .btn-checkout:disabled {
        background: #ece7e3;
        color: #a8a29e;
        cursor: not-allowed;
    }

    .checkout-note {
        font-size: .8rem;
        color: #c2410c;
        text-align: center;
        margin: .6rem 0 0;
    }

    /* Thanh tổng tiền dính đáy (mobile) */
    .cart-bar {
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

    @media (max-width: 767.98px) {
        .cart-container {
            margin-top: 1.25rem;
        }

        .cart-title {
            font-size: 1.45rem;
        }

        .cart-card {
            padding: .25rem 1rem;
            border-radius: 20px;
        }

        /* Ảnh | thông tin + số lượng ; thành tiền + nút xóa xếp dọc bên phải */
        .cart-item {
            grid-template-columns: 72px minmax(0, 1fr) auto;
            gap: .75rem;
            padding: 1rem 0;
        }

        .cart-img {
            width: 72px;
            height: 72px;
            border-radius: 12px;
        }

        .cart-name {
            font-size: .92rem;
        }

        .item-subtotal {
            font-size: .92rem;
        }

        .btn-remove {
            width: 32px;
            height: 32px;
        }

        .summary-card {
            position: static;
            border-radius: 20px;
        }

        /* Nút đặt hàng đã có ở thanh dính đáy */
        .summary-card .btn-checkout,
        .summary-card .checkout-note {
            display: none;
        }

        .cart-bar {
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

        .cart-bar .label {
            font-size: .75rem;
            color: var(--text-muted);
        }

        .cart-bar .amount {
            font-weight: 800;
            font-size: 1.15rem;
            color: var(--primary-color);
            line-height: 1.2;
        }

        .cart-bar .btn-checkout {
            width: auto;
            margin: 0;
            padding: .8rem 1.2rem;
            font-size: .9rem;
            border-radius: 14px;
        }
    }
</style>

<div class="container cart-container mb-5">
    <div class="cart-head animate-fade-up">
        <h1 class="cart-title">Giỏ hàng <span class="cart-count"><?= (int) $totalQty ?> sản phẩm</span></h1>
        <a href="index.php?router=customers" class="cart-continue" data-nav-back><i class="bi bi-arrow-left"></i> Tiếp tục mua sắm</a>
    </div>

    <?php if ($problems): ?>
        <div class="cart-alert animate-fade-up" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Một số sản phẩm không đủ hàng.</strong>
                Giảm số lượng hoặc xóa các sản phẩm được đánh dấu để tiếp tục đặt hàng.
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8 animate-fade-up">
            <div class="cart-card">
                <?php foreach ($listCart as $item): ?>
                    <?php
                    $unitPrice = $item['discounted_price'] ?? $item['price'];
                    $subtotal = $unitPrice * $item['quantity'];
                    $detailUrl = 'index.php?router=customers&controller=detail&action=index&id=' . (int) $item['product_id'];
                    $problem = $problems[$item['product_id']] ?? null;
                    $stock = (int) $item['stock_quantity'];
                    $sellable = $item['status'] === 'available' && $stock > 0;
                    ?>
                    <div class="cart-item<?= $problem ? ' has-problem' : '' ?>">
                        <a href="<?= $detailUrl ?>" tabindex="-1" class="cart-img-link">
                            <img src="<?= $e(productImageUrl($item['image_url'], '200x200')) ?>" alt="<?= $e($item['product_name']) ?>" class="cart-img"
                                onerror="this.src='https://placehold.co/200x200?text=Flower'">
                        </a>

                        <div class="item-info">
                            <a href="<?= $detailUrl ?>" class="cart-name"><?= $e($item['product_name']) ?></a>
                            <div class="item-price">
                                <span class="unit-price"><?= $money($unitPrice) ?></span>
                                <?php if (isset($item['discounted_price'])): ?><span class="old-price"><?= $money($item['price']) ?></span><?php endif; ?>
                            </div>
                            <?php if ($problem): ?>
                                <div class="item-problem"><i class="bi bi-exclamation-circle"></i> <?= $e($problem) ?></div>
                            <?php endif; ?>

                            <?php if ($sellable): ?>
                                <form action="index.php?router=customers&controller=cart&action=updateCart" method="POST" class="qty-form">
                                    <input type="hidden" name="cart_id" value="<?= (int) $item['cart_id'] ?>">
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn" data-qty="-1" aria-label="Giảm số lượng" <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>−</button>
                                        <!-- max = tồn kho; đang vượt kho thì nút − đưa về đúng mức còn lại -->
                                        <input type="number" name="quantity" class="qty-input" value="<?= (int) $item['quantity'] ?>" min="1"
                                            max="<?= max(1, $stock) ?>" aria-label="Số lượng <?= $e($item['product_name']) ?>" inputmode="numeric">
                                        <button type="button" class="qty-btn" data-qty="1" aria-label="Tăng số lượng" <?= $item['quantity'] >= $stock ? 'disabled' : '' ?>>+</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>

                        <div class="item-right">
                            <span class="item-subtotal"><?= $money($subtotal) ?></span>
                            <!-- Xóa = POST + hộp xác nhận (popup.js) -->
                            <form action="index.php?router=customers&controller=cart&action=deleteCart" method="POST" class="m-0">
                                <input type="hidden" name="cart_id" value="<?= (int) $item['cart_id'] ?>">
                                <button type="submit" class="btn-remove" title="Xóa khỏi giỏ" aria-label="Xóa <?= $e($item['product_name']) ?> khỏi giỏ"
                                    data-confirm="“<?= $e($item['product_name']) ?>” sẽ được bỏ khỏi giỏ hàng của bạn."
                                    data-confirm-title="Xóa khỏi giỏ hàng?" data-confirm-variant="danger" data-confirm-ok="Xóa" data-confirm-cancel="Giữ lại">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-4 animate-fade-up" style="animation-delay: 40ms">
            <div class="summary-card">
                <h2 class="summary-title">Tóm tắt đơn hàng</h2>
                <div class="summary-row">
                    <span>Tạm tính (<?= (int) $totalQty ?> sản phẩm)</span>
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

                <?php if ($canCheckout): ?>
                    <a href="<?= $checkoutUrl ?>" class="btn-checkout">Tiến hành đặt hàng <i class="bi bi-arrow-right"></i></a>
                <?php else: ?>
                    <button type="button" class="btn-checkout" disabled>Tiến hành đặt hàng <i class="bi bi-arrow-right"></i></button>
                    <p class="checkout-note">Cập nhật các sản phẩm không đủ hàng để đặt hàng.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mobile: tổng tiền + nút đặt hàng luôn trong tầm tay -->
    <div class="cart-bar">
        <div>
            <div class="label">Tổng cộng</div>
            <div class="amount"><?= $money($total) ?></div>
        </div>
        <?php if ($canCheckout): ?>
            <a href="<?= $checkoutUrl ?>" class="btn-checkout">Đặt hàng <i class="bi bi-arrow-right"></i></a>
        <?php else: ?>
            <button type="button" class="btn-checkout" disabled>Đặt hàng</button>
        <?php endif; ?>
    </div>
</div>

<script>
    // Nút +/− đổi số lượng rồi gửi form cập nhật ngay
    document.querySelectorAll('.qty-form').forEach(function(form) {
        const input = form.querySelector('.qty-input');
        const min = parseInt(input.min) || 1;
        const max = parseInt(input.max) || 1;
        const submit = v => {
            v = Math.min(max, Math.max(min, parseInt(v) || min));
            input.value = v;
            if (v !== parseInt(input.defaultValue)) form.submit();
        };
        form.querySelectorAll('.qty-btn').forEach(btn => btn.addEventListener('click', () => submit(+input.value + +btn.dataset.qty)));
        input.addEventListener('change', () => submit(input.value));
    });
</script>
