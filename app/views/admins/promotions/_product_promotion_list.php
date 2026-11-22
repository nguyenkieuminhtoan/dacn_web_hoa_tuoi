<?php
/**
 * Danh sách "khuyến mãi sản phẩm" dùng chung cho 4 trang: Tất cả / Đang áp dụng / Tạm ngưng / Hết hạn.
 * @var array  $ppRows     Các dòng sản phẩm-khuyến mãi
 * @var string $ppAction   Action hiện tại (để tạo link phân trang + tab)
 * @var string $ppTitle    Tiêu đề trang
 * @var string $ppEmpty    Câu hiển thị khi trống
 * @var int    $page
 * @var int    $totalPages
 * @var int    $ppTotal    Tổng số dòng
 */
$ppTabs = [
    'listAllProductPromotion'      => 'Tất cả',
    'listApplyProductPromotion'    => 'Đang áp dụng',
    'listInactiveProductPromotion' => 'Tạm ngưng',
    'listExpiredProductPromotion'  => 'Hết hạn',
];
$ppCalc = new PromotionModel();
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title">Khuyến mãi sản phẩm</h2>
        <p class="ad-head-sub"><?= ad_e($ppTitle) ?> · <?= (int) $ppTotal ?> sản phẩm</p>
    </div>
    <div class="ad-head-actions">
        <a class="btn ad-btn-primary" href="index.php?controller=promotion&action=applyProductPromotion">
            <i class="fas fa-plus"></i> Áp dụng khuyến mãi
        </a>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<div class="ad-card" data-filter>
    <nav class="ad-tabs" aria-label="Lọc theo trạng thái khuyến mãi">
        <?php foreach ($ppTabs as $act => $label): ?>
            <a class="ad-tab<?= $act === $ppAction ? ' active' : '' ?>" href="index.php?controller=promotion&action=<?= $act ?>"
                <?= $act === $ppAction ? 'aria-current="page"' : '' ?>><?= $label ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="ad-toolbar">
        <div class="ad-search">
            <i class="fas fa-search"></i>
            <input type="search" class="form-control" placeholder="Tìm tên sản phẩm, mã khuyến mãi..." data-filter-search aria-label="Tìm">
        </div>
        <span class="ad-toolbar-count" data-filter-count></span>
    </div>

    <?php if (empty($ppRows)): ?>
        <?= ad_empty('fa-tags', 'Không có sản phẩm nào', $ppEmpty,
            '<a class="btn ad-btn-primary" href="index.php?controller=promotion&action=applyProductPromotion"><i class="fas fa-plus"></i> Áp dụng khuyến mãi</a>') ?>
    <?php else: ?>
        <div class="ad-table-wrap">
            <table class="ad-table ad-stack-mobile">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th class="ad-num">Giá sau giảm</th>
                        <th>Mã khuyến mãi</th>
                        <th>Mức giảm</th>
                        <th>Trạng thái</th>
                        <th>Ngày áp dụng</th>
                        <th class="ad-num">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ppRows as $r): ?>
                        <?php
                        $calc = $ppCalc->calculatePromotionPrice($r['price'], $r['discount_type'], $r['discount_value']);
                        $ppId = (int) $r['product_promotion_id'];
                        ?>
                        <tr data-search="<?= ad_e($r['product_name'] . ' ' . $r['promotion_code']) ?>">
                            <td class="ad-cell-main">
                                <div class="ad-item">
                                    <?= ad_thumb($r['image_url'], $r['product_name']) ?>
                                    <div>
                                        <div class="ad-item-name"><?= ad_e($r['product_name']) ?></div>
                                        <div class="ad-item-sub">Giá gốc <?= ad_money($r['price']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Giá sau giảm" class="ad-num">
                                <div>
                                    <div class="ad-price-new"><?= ad_money($calc['final_price']) ?></div>
                                    <div class="ad-save">Tiết kiệm <?= ad_money($calc['discount_amount']) ?></div>
                                </div>
                            </td>
                            <td data-label="Mã khuyến mãi"><span class="ad-code"><?= ad_e($r['promotion_code']) ?></span></td>
                            <td data-label="Mức giảm" class="ad-nowrap"><?= ad_e(ad_discount($r['discount_type'], $r['discount_value'])) ?></td>
                            <td data-label="Trạng thái"><?= ad_promo_status($r['status']) ?></td>
                            <td data-label="Ngày áp dụng" class="ad-nowrap"><?= ad_date($r['created_at']) ?></td>
                            <td class="ad-cell-actions">
                                <div class="ad-actions">
                                    <a href="index.php?controller=promotion&action=updateProductPromotion&product_promotion_id=<?= $ppId ?>"
                                        class="ad-icon-btn is-edit" title="Đổi mã khuyến mãi" aria-label="Đổi mã khuyến mãi cho <?= ad_e($r['product_name']) ?>">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="index.php?controller=promotion&action=removeProductPromotion&product_promotion_id=<?= $ppId ?>"
                                        class="ad-icon-btn is-danger" title="Gỡ khuyến mãi" aria-label="Gỡ khuyến mãi khỏi <?= ad_e($r['product_name']) ?>"
                                        data-confirm="“<?= ad_e($r['product_name']) ?>” sẽ không còn được áp dụng mã <?= ad_e($r['promotion_code']) ?>."
                                        data-confirm-title="Gỡ khuyến mãi?" data-confirm-variant="danger" data-confirm-ok="Gỡ khuyến mãi">
                                        <i class="fas fa-link-slash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div data-filter-empty class="ad-no-result">
            <?= ad_empty('fa-magnifying-glass', 'Không tìm thấy kết quả phù hợp', 'Thử tên sản phẩm hoặc mã khác.') ?>
        </div>
        <?= ad_pagination($page, $totalPages, 'index.php?controller=promotion&action=' . $ppAction, $ppTotal) ?>
    <?php endif; ?>
</div>
