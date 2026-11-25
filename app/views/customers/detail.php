<?php
/**
 * Chi tiết sản phẩm. Biến từ DetailController::index():
 * @var array $product   (đã áp khuyến mãi: discounted_price nếu có)
 * @var array $category
 * @var array $related   sản phẩm gợi ý (ưu tiên cùng danh mục)
 */
$related = $related ?? [];
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float) $v, 0, ',', '.') . 'đ';

$stock    = (int) $product['stock_quantity'];
$inStock  = $stock > 0 && $product['status'] === 'available';
$lowStock = $inStock && $stock <= 5;
$hasSale  = isset($product['discounted_price']);
$percent  = $hasSale && $product['price'] > 0 ? round(100 - $product['discounted_price'] / $product['price'] * 100) : 0;
$finalPrice   = $hasSale ? $product['discounted_price'] : $product['price'];
$categoryName = $category['category_name'] ?? 'Danh mục';
$categoryUrl  = 'index.php?router=customers&categories%5B%5D=' . (int) $product['category_id'];
$description  = trim((string) ($product['description'] ?? ''));
?>
<style>
    .pd-page {
        max-width: 1140px;
        padding-top: 1.5rem;
    }

    /* ---------- Đường dẫn ---------- */
    .pd-crumb {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 6px;
        list-style: none;
        padding: 0;
        margin: 0 0 1.25rem;
        font-size: .85rem;
        color: var(--text-muted);
        min-width: 0;
    }

    .pd-crumb li {
        white-space: nowrap;
    }

    .pd-crumb li:last-child {
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--text-main);
        font-weight: 600;
    }

    .pd-crumb a {
        color: var(--text-muted);
        text-decoration: none;
    }

    .pd-crumb a:hover {
        color: var(--primary-color);
    }

    .pd-crumb .sep {
        font-size: .65rem;
        color: #c9b6aa;
    }

    /* ---------- Bố cục 2 cột ---------- */
    .pd-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 48px;
        align-items: start;
    }

    /* Ảnh: khung dọc 4:5, đứng yên khi cuộn phần thông tin */
    .pd-media {
        position: sticky;
        top: calc(var(--sh-header-h) + 20px);
    }

    .pd-photo {
        position: relative;
        aspect-ratio: 4 / 5;
        border-radius: 28px;
        overflow: hidden;
        background: #faf5f0;
        box-shadow: var(--shadow-md);
    }

    .pd-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .pd-flag {
        position: absolute;
        top: 16px;
        left: 16px;
        background: var(--primary-gradient);
        color: #fff;
        font-weight: 800;
        font-size: .85rem;
        padding: 6px 14px;
        border-radius: 999px;
        box-shadow: 0 6px 16px rgba(233, 30, 99, .3);
    }

    /* ---------- Thông tin ---------- */
    .pd-info {
        padding-top: .25rem;
    }

    .pd-cat {
        display: inline-block;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--accent-color);
        text-decoration: none;
        margin-bottom: .5rem;
    }

    .pd-cat:hover {
        color: var(--primary-color);
    }

    .pd-title {
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -.5px;
        line-height: 1.2;
        color: #3a2c23;
        margin: 0 0 1rem;
    }

    .pd-price {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 4px 12px;
        margin-bottom: .75rem;
    }

    .pd-price .now {
        font-size: 1.9rem;
        font-weight: 800;
        color: var(--primary-color);
        line-height: 1.1;
    }

    .pd-price .was {
        font-size: 1.05rem;
        color: #b6a088;
        text-decoration: line-through;
    }

    .pd-price .save {
        font-size: .8rem;
        font-weight: 700;
        color: #1e7e34;
        background: #e6f4ea;
        padding: 3px 10px;
        border-radius: 999px;
        align-self: center;
    }

    .pd-stock {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .88rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .pd-stock::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 0 4px color-mix(in srgb, currentColor 18%, transparent);
    }

    .pd-stock.ok {
        color: #1e7e34;
    }

    .pd-stock.low {
        color: #c2410c;
    }

    .pd-stock.out {
        color: #d93025;
    }

    /* ---------- Chọn số lượng + thêm vào giỏ (1 hàng) ---------- */
    .pd-buy {
        display: flex;
        align-items: stretch;
        gap: 12px;
    }

    .pd-qty {
        display: flex;
        align-items: center;
        background: #f8f1ea;
        border-radius: 14px;
        padding: 4px;
        flex-shrink: 0;
    }

    .pd-qty button {
        width: 40px;
        height: 100%;
        min-height: 44px;
        border-radius: 10px;
        border: 0;
        background: #fff;
        color: var(--text-main);
        font-size: 1.1rem;
        font-weight: 700;
        transition: background .15s ease, color .15s ease;
    }

    .pd-qty button:hover:not(:disabled) {
        background: var(--accent-color);
        color: #fff;
    }

    .pd-qty button:disabled {
        opacity: .4;
    }

    .pd-qty input {
        width: 48px;
        border: 0;
        background: transparent;
        text-align: center;
        font-weight: 700;
        font-size: 1.05rem;
        -moz-appearance: textfield;
        appearance: textfield;
    }

    .pd-qty input:focus {
        outline: none;
    }

    .pd-qty input::-webkit-inner-spin-button,
    .pd-qty input::-webkit-outer-spin-button {
        -webkit-appearance: none;
    }

    .pd-add {
        flex: 1;
        min-height: 52px;
        border: 0;
        border-radius: 14px;
        background: var(--primary-gradient);
        color: #fff;
        font-weight: 800;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .pd-add:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(233, 30, 99, .28);
    }

    .pd-add:disabled {
        background: #ece7e3;
        color: #a8a29e;
        cursor: not-allowed;
    }

    /* ---------- Cam kết ---------- */
    .pd-perks {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        list-style: none;
        padding: 0;
        margin: 1.75rem 0 0;
    }

    .pd-perks li {
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 12px;
        border-radius: 16px;
        background: #fff;
        border: 1px solid #f3e9e2;
        font-size: .8rem;
        line-height: 1.35;
        color: var(--text-muted);
    }

    .pd-perks i {
        font-size: 1.25rem;
        color: var(--primary-color);
    }

    .pd-perks b {
        color: var(--text-main);
        font-size: .85rem;
    }

    /* ---------- Mô tả ---------- */
    .pd-desc {
        margin-top: 1.75rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f3e9e2;
    }

    .pd-desc h2 {
        font-size: 1rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: .6rem;
    }

    .pd-desc-body {
        font-size: .97rem;
        line-height: 1.8;
        color: var(--text-muted);
    }

    /* ---------- Gợi ý ---------- */
    .pd-related {
        margin-top: 4rem;
    }

    .pd-related-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 1.25rem;
    }

    .pd-related-head h2 {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -.3px;
        margin: 0;
    }

    .pd-related-head a {
        font-size: .88rem;
        font-weight: 700;
        color: var(--primary-color);
        text-decoration: none;
        white-space: nowrap;
    }

    .pd-related-head a:hover {
        text-decoration: underline;
    }

    @keyframes pdFade {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    .pd-anim {
        animation: pdFade .3s cubic-bezier(.2, 0, 0, 1) both;
    }

    /* ---------- Tablet ---------- */
    @media (max-width: 991.98px) {
        .pd-grid {
            gap: 32px;
        }

        .pd-title {
            font-size: 1.65rem;
        }

        .pd-perks {
            grid-template-columns: 1fr;
        }

        .pd-perks li {
            flex-direction: row;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
        }
    }

    /* ---------- Điện thoại: 1 cột, thanh mua dính đáy ---------- */
    @media (max-width: 767.98px) {
        .pd-page {
            padding-top: 1rem;
        }

        .pd-crumb {
            margin-bottom: .85rem;
            font-size: .8rem;
        }

        .pd-grid {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .pd-media {
            position: static;
        }

        .pd-photo {
            aspect-ratio: 1 / 1;
            border-radius: 22px;
        }

        .pd-title {
            font-size: 1.4rem;
            margin-bottom: .6rem;
        }

        .pd-price .now {
            font-size: 1.55rem;
        }

        .pd-stock {
            margin-bottom: 1rem;
        }

        /* Nút mua luôn trong tầm tay: dính ngay trên thanh tab đáy (chỉ khi còn hàng) */
        .pd-buy.is-sticky {
            position: sticky;
            bottom: calc(var(--sh-tab-h) + env(safe-area-inset-bottom) + 10px);
            z-index: 5;
            background: #fff;
            border: 1px solid #f3e1e8;
            border-radius: 18px;
            padding: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .1);
            gap: 8px;
        }

        .pd-qty button {
            width: 36px;
        }

        .pd-qty input {
            width: 36px;
        }

        .pd-add {
            min-height: 48px;
            font-size: .92rem;
        }

        .pd-perks {
            margin-top: 1.25rem;
            gap: 8px;
        }

        .pd-desc {
            margin-top: 1.25rem;
            padding-top: 1.25rem;
        }

        .pd-related {
            margin-top: 2.5rem;
        }

        .pd-related-head h2 {
            font-size: 1.2rem;
        }
    }
</style>

<div class="container pd-page">
    <nav aria-label="Đường dẫn">
        <ol class="pd-crumb">
            <li><a href="index.php?router=customers" data-nav-back>Cửa hàng</a></li>
            <li class="sep" aria-hidden="true"><i class="bi bi-chevron-right"></i></li>
            <li><a href="<?= $categoryUrl ?>"><?= $e($categoryName) ?></a></li>
            <li class="sep d-none d-sm-block" aria-hidden="true"><i class="bi bi-chevron-right"></i></li>
            <li class="d-none d-sm-block" aria-current="page"><?= $e($product['product_name']) ?></li>
        </ol>
    </nav>

    <div class="pd-grid">
        <!-- Ảnh -->
        <div class="pd-media pd-anim">
            <div class="pd-photo">
                <img src="<?= $e(productImageUrl($product['image_url'], '800x1000')) ?>" alt="<?= $e($product['product_name']) ?>"
                    onerror="this.src='https://placehold.co/800x1000?text=Flower'">
                <?php if ($hasSale): ?><span class="pd-flag">-<?= $percent ?>%</span><?php endif; ?>
            </div>
        </div>

        <!-- Thông tin -->
        <div class="pd-info pd-anim" style="animation-delay: 40ms">
            <a class="pd-cat" href="<?= $categoryUrl ?>"><?= $e($categoryName) ?></a>
            <h1 class="pd-title"><?= $e($product['product_name']) ?></h1>

            <div class="pd-price">
                <span class="now"><?= $money($finalPrice) ?></span>
                <?php if ($hasSale): ?>
                    <span class="was"><?= $money($product['price']) ?></span>
                    <span class="save">Tiết kiệm <?= $money($product['price'] - $finalPrice) ?></span>
                <?php endif; ?>
            </div>

            <?php if (!$inStock): ?>
                <div class="pd-stock out">Tạm hết hàng</div>
            <?php elseif ($lowStock): ?>
                <div class="pd-stock low">Chỉ còn <?= $stock ?> sản phẩm — đặt sớm nhé!</div>
            <?php else: ?>
                <div class="pd-stock ok">Còn hàng</div>
            <?php endif; ?>

            <form action="index.php?router=customers&controller=cart&action=addCart" method="POST">
                <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
                <div class="pd-buy<?= $inStock ? ' is-sticky' : '' ?>">
                    <?php if ($inStock): ?>
                        <div class="pd-qty">
                            <button type="button" data-qty="-1" aria-label="Giảm số lượng">−</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $stock ?>"
                                inputmode="numeric" aria-label="Số lượng">
                            <button type="button" data-qty="1" aria-label="Tăng số lượng">+</button>
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="pd-add" <?= $inStock ? '' : 'disabled' ?>>
                        <i class="bi bi-bag-plus-fill"></i> <?= $inStock ? 'Thêm vào giỏ hàng' : 'Tạm hết hàng' ?>
                    </button>
                </div>
            </form>

            <ul class="pd-perks">
                <li><i class="bi bi-truck"></i><span><b>Giao tận nơi</b><br class="d-none d-lg-inline"> Gói cẩn thận, giữ hoa tươi</span></li>
                <li><i class="bi bi-flower1"></i><span><b>Hoa tươi mỗi ngày</b><br class="d-none d-lg-inline"> Cắm khi có đơn</span></li>
                <li><i class="bi bi-cash-coin"></i><span><b>Thanh toán COD</b><br class="d-none d-lg-inline"> Trả tiền khi nhận hoa</span></li>
            </ul>

            <?php if ($description !== ''): ?>
                <section class="pd-desc">
                    <h2>Mô tả sản phẩm</h2>
                    <div class="pd-desc-body"><?= nl2br($e($description)) ?></div>
                </section>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($related): ?>
        <section class="pd-related" aria-labelledby="relatedTitle">
            <div class="pd-related-head">
                <h2 id="relatedTitle">Có thể bạn cũng thích</h2>
                <a href="<?= $categoryUrl ?>">Xem thêm<span class="d-none d-sm-inline"> <?= $e($categoryName) ?></span> <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="row g-3 g-md-4">
                <?php foreach ($related as $product): ?>
                    <div class="col-6 col-md-3">
                        <?php include __DIR__ . '/partials/product_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
    // Nút +/− số lượng, giới hạn trong khoảng 1..tồn kho
    (function() {
        const input = document.getElementById('quantity');
        if (!input) return;
        const min = parseInt(input.min) || 1;
        const max = parseInt(input.max) || 1;
        const [minus, plus] = document.querySelectorAll('.pd-qty button');

        function clamp(v) {
            v = parseInt(v);
            if (isNaN(v)) v = min;
            return Math.min(max, Math.max(min, v));
        }

        function sync() {
            input.value = clamp(input.value);
            minus.disabled = +input.value <= min;
            plus.disabled = +input.value >= max;
        }
        [minus, plus].forEach(btn => btn.addEventListener('click', () => {
            input.value = clamp(+input.value + +btn.dataset.qty);
            sync();
        }));
        input.addEventListener('change', sync);
        sync();
    })();
</script>
