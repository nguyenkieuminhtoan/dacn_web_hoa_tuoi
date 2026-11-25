<?php
/**
 * Đơn hàng của tôi. Biến từ OrderController::listOrders():
 * @var array  $orders         đơn theo tab đang chọn
 * @var array  $statusCounts   [order_status => số đơn]
 * @var string $currentStatus  '' = tất cả
 * @var int    $totalOrders
 */
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float) $v, 0, ',', '.') . 'đ';
$listUrl = 'index.php?router=customers&controller=order&action=listOrders';

$tabs = ['' => 'Tất cả'] + OrderHelper::STATUS_LABELS;
?>
<style>
    .ol-page {
        max-width: 960px;
        padding-top: 2rem;
    }

    .ol-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .ol-title {
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -.5px;
        color: #3a2c23;
        margin: 0;
    }

    .ol-title small {
        font-size: .9rem;
        font-weight: 600;
        color: var(--text-muted);
        letter-spacing: 0;
        margin-left: 8px;
    }

    .ol-shop {
        font-size: .9rem;
        font-weight: 700;
        color: var(--primary-color);
        text-decoration: none;
    }

    .ol-shop:hover {
        text-decoration: underline;
    }

    /* ---------- Tab trạng thái (cuộn ngang trên mobile) ---------- */
    .ol-tabs {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding: 2px 2px 10px;
        margin: 0 -2px 1rem;
        scrollbar-width: none;
    }

    .ol-tabs::-webkit-scrollbar {
        display: none;
    }

    .ol-tab {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #f0e1e6;
        color: var(--text-main);
        font-size: .88rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .ol-tab:hover {
        border-color: var(--accent-color);
        color: var(--primary-color);
    }

    .ol-tab.active {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: #fff;
    }

    .ol-tab .n {
        font-size: .72rem;
        font-weight: 700;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: #f8eef1;
        color: var(--text-muted);
        display: grid;
        place-items: center;
    }

    .ol-tab.active .n {
        background: rgba(255, 255, 255, .25);
        color: #fff;
    }

    /* ---------- Thẻ đơn (cả thẻ bấm được) ---------- */
    .ol-card {
        display: block;
        background: var(--card-bg);
        border: 1px solid #f3e9e2;
        border-radius: 20px;
        box-shadow: var(--shadow-sm);
        margin-bottom: .85rem;
        color: var(--text-main);
        text-decoration: none;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .ol-card:hover {
        border-color: #f6c2d3;
        box-shadow: 0 12px 28px rgba(224, 90, 126, .1);
        transform: translateY(-2px);
        color: var(--text-main);
    }

    .ol-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: .8rem 1.25rem;
        border-bottom: 1px solid #f6eee9;
        font-size: .85rem;
    }

    .ol-code {
        font-weight: 800;
        color: var(--text-main);
    }

    .ol-date {
        color: var(--text-muted);
        margin-left: 8px;
    }

    .ol-card-body {
        display: grid;
        grid-template-columns: 64px minmax(0, 1fr) auto 18px;
        align-items: center;
        gap: 14px;
        padding: 1rem 1.25rem;
    }

    .ol-thumb {
        position: relative;
        width: 64px;
        height: 64px;
    }

    .ol-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 12px;
        background: #faf5f0;
        display: block;
    }

    .ol-more {
        position: absolute;
        right: -6px;
        bottom: -6px;
        background: #6b5a4c;
        color: #fff;
        font-size: .65rem;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 999px;
        box-shadow: 0 0 0 2px #fff;
    }

    .ol-name {
        font-weight: 700;
        font-size: .95rem;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .ol-sub {
        font-size: .8rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .ol-total {
        text-align: right;
        white-space: nowrap;
    }

    .ol-total .lbl {
        display: block;
        font-size: .72rem;
        color: var(--text-muted);
    }

    .ol-total .val {
        font-weight: 800;
        font-size: 1.1rem;
        color: var(--primary-color);
    }

    .ol-chevron {
        color: #c9b6aa;
    }

    /* Nhãn trạng thái (dùng chung với trang chi tiết) */
    .os-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 11px;
        border-radius: 999px;
        font-size: .75rem;
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

    .ol-empty {
        background: #fff;
        border: 1px dashed #f0d9e1;
        border-radius: 20px;
        padding: 3rem 1.5rem;
        text-align: center;
    }

    .ol-empty i {
        font-size: 2.8rem;
        color: #f4b8cb;
    }

    .ol-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--primary-gradient);
        color: #fff;
        border-radius: 999px;
        padding: 10px 24px;
        font-weight: 700;
        text-decoration: none;
    }

    .ol-btn:hover {
        color: #fff;
        box-shadow: 0 8px 18px rgba(233, 30, 99, .25);
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: none; }
    }

    .animate-fade-up {
        animation: fadeUp .3s cubic-bezier(.2, 0, 0, 1) both;
    }

    @media (max-width: 767.98px) {
        .ol-page {
            padding-top: 1.25rem;
        }

        .ol-title {
            font-size: 1.45rem;
        }

        .ol-card-top {
            padding: .7rem 1rem;
        }

        .ol-date {
            display: block;
            margin: 2px 0 0;
            font-size: .78rem;
        }

        /* Ảnh | tên + tổng tiền (xếp dọc) */
        .ol-card-body {
            grid-template-columns: 56px minmax(0, 1fr) 14px;
            gap: 12px;
            padding: .85rem 1rem;
        }

        .ol-thumb {
            width: 56px;
            height: 56px;
        }

        .ol-name {
            font-size: .9rem;
        }

        .ol-total {
            grid-column: 2;
            grid-row: 2;
            text-align: left;
            margin-top: -6px;
        }

        .ol-total .lbl {
            display: inline;
            margin-right: 6px;
        }

        .ol-total .val {
            font-size: 1rem;
        }

        .ol-chevron {
            grid-column: 3;
            grid-row: 1 / span 2;
        }

        .ol-card:hover {
            transform: none;
        }
    }
</style>

<div class="container ol-page mb-5">
    <div class="ol-head">
        <h1 class="ol-title">Đơn hàng của tôi<?php if ($totalOrders): ?><small><?= (int) $totalOrders ?> đơn</small><?php endif; ?></h1>
        <a href="index.php?router=customers" class="ol-shop"><i class="bi bi-bag"></i> Tiếp tục mua sắm</a>
    </div>

    <?php if ($totalOrders === 0): ?>
        <div class="ol-empty animate-fade-up">
            <i class="bi bi-receipt-cutoff"></i>
            <h4 class="fw-bold mt-3">Bạn chưa có đơn hàng nào</h4>
            <p class="text-muted mb-4">Chọn một bó hoa thật xinh để gửi tặng người thương nhé!</p>
            <a href="index.php?router=customers" class="ol-btn">Mua sắm ngay <i class="bi bi-arrow-right"></i></a>
        </div>
    <?php else: ?>
        <nav class="ol-tabs" aria-label="Lọc theo trạng thái">
            <?php foreach ($tabs as $key => $label):
                $count = $key === '' ? $totalOrders : (int) ($statusCounts[$key] ?? 0);
                $url = $key === '' ? $listUrl : $listUrl . '&status=' . $key;
            ?>
                <a href="<?= $url ?>" class="ol-tab<?= $currentStatus === $key ? ' active' : '' ?>" <?= $currentStatus === $key ? 'aria-current="page"' : '' ?>>
                    <?= $e($label) ?> <span class="n"><?= $count ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php if (!$orders): ?>
            <div class="ol-empty">
                <i class="bi bi-inbox"></i>
                <h5 class="fw-bold mt-3">Không có đơn nào “<?= $e(OrderHelper::statusLabel($currentStatus)) ?>”</h5>
                <a href="<?= $listUrl ?>" class="ol-shop">Xem tất cả đơn hàng</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $i => $order):
                $more = (int) $order['total_products'] - 1;
                $detailUrl = 'index.php?router=customers&controller=order&action=viewDetail&id=' . (int) $order['order_id'];
            ?>
                <a href="<?= $detailUrl ?>" class="ol-card animate-fade-up" style="animation-delay: <?= min($i * 20, 100) ?>ms">
                    <div class="ol-card-top">
                        <div>
                            <span class="ol-code"><?= $e($order['order_code']) ?></span>
                            <span class="ol-date"><?= date('d/m/Y · H:i', strtotime($order['order_date'])) ?></span>
                        </div>
                        <span class="os-badge os-<?= $e($order['order_status']) ?>"><?= $e(OrderHelper::statusLabel($order['order_status'])) ?></span>
                    </div>
                    <div class="ol-card-body">
                        <div class="ol-thumb">
                            <img src="<?= $e(productImageUrl($order['first_product_image'], '200x200')) ?>" alt="" loading="lazy"
                                onerror="this.src='https://placehold.co/100x100?text=Flower'">
                            <?php if ($more > 0): ?><span class="ol-more">+<?= $more ?></span><?php endif; ?>
                        </div>
                        <div style="min-width:0">
                            <div class="ol-name"><?= $e($order['first_product_name'] ?? 'Đơn hàng') ?></div>
                            <div class="ol-sub">
                                <?= (int) $order['total_qty'] ?> sản phẩm<?= $more > 0 ? ' · và ' . $more . ' mẫu hoa khác' : '' ?>
                            </div>
                        </div>
                        <div class="ol-total">
                            <span class="lbl">Tổng tiền</span>
                            <span class="val"><?= $money($order['total_amount']) ?></span>
                        </div>
                        <i class="bi bi-chevron-right ol-chevron" aria-hidden="true"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
