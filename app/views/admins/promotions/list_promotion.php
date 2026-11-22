<?php
/**
 * Biến từ PromotionController::listPromotion():
 * @var array $listPromotion
 * @var int   $page
 * @var int   $totalPages
 * @var int   $totalPromotions
 */
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title">Mã khuyến mãi</h2>
        <p class="ad-head-sub"><?= (int) $totalPromotions ?> mã khuyến mãi</p>
    </div>
    <div class="ad-head-actions">
        <a class="btn ad-btn-primary" href="index.php?controller=promotion&action=addPromotion">
            <i class="fas fa-plus"></i> Tạo mã khuyến mãi
        </a>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<div class="ad-card" data-filter>
    <div class="ad-toolbar">
        <div class="ad-search">
            <i class="fas fa-search"></i>
            <input type="search" class="form-control" placeholder="Tìm mã hoặc tên chương trình..." data-filter-search aria-label="Tìm mã khuyến mãi">
        </div>
        <select class="form-select" data-filter-status aria-label="Lọc theo trạng thái">
            <option value="">Tất cả trạng thái</option>
            <option value="active">Đang hoạt động</option>
            <option value="inactive">Tạm ngưng</option>
            <option value="expired">Hết hạn</option>
        </select>
        <span class="ad-toolbar-count" data-filter-count></span>
    </div>

    <?php if (empty($listPromotion)): ?>
        <?= ad_empty('fa-ticket', 'Chưa có mã khuyến mãi nào', 'Tạo mã đầu tiên để bắt đầu giảm giá cho sản phẩm.',
            '<a class="btn ad-btn-primary" href="index.php?controller=promotion&action=addPromotion"><i class="fas fa-plus"></i> Tạo mã khuyến mãi</a>') ?>
    <?php else: ?>
        <div class="ad-table-wrap">
            <table class="ad-table ad-stack-mobile">
                <thead>
                    <tr>
                        <th>Chương trình</th>
                        <th>Mức giảm</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th class="ad-num">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listPromotion as $p): ?>
                        <tr data-search="<?= ad_e($p['promotion_code'] . ' ' . $p['promotion_name']) ?>" data-status="<?= ad_e($p['status']) ?>">
                            <td class="ad-cell-main">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="ad-code"><?= ad_e($p['promotion_code']) ?></span>
                                    <span class="ad-item-name"><?= ad_e($p['promotion_name']) ?></span>
                                </div>
                                <?php if (!empty($p['description'])): ?>
                                    <div class="ad-item-sub ad-clamp mt-1"><?= ad_e($p['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td data-label="Mức giảm" class="ad-nowrap ad-strong"><?= ad_e(ad_discount($p['discount_type'], $p['discount_value'])) ?></td>
                            <td data-label="Thời gian" class="ad-nowrap ad-small">
                                <?= ad_date($p['start_date']) ?> <span class="ad-muted">→</span> <?= ad_date($p['end_date']) ?>
                            </td>
                            <td data-label="Trạng thái"><?= ad_promo_status($p['status']) ?></td>
                            <td class="ad-cell-actions">
                                <div class="ad-actions">
                                    <a href="index.php?controller=promotion&action=updatePromotion&promotion_id=<?= (int) $p['promotion_id'] ?>"
                                        class="ad-icon-btn is-edit" title="Sửa" aria-label="Sửa mã <?= ad_e($p['promotion_code']) ?>">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="index.php?controller=promotion&action=deletePromotion&promotion_id=<?= (int) $p['promotion_id'] ?>"
                                        class="ad-icon-btn is-danger" title="Xóa" aria-label="Xóa mã <?= ad_e($p['promotion_code']) ?>"
                                        data-confirm="Mã <?= ad_e($p['promotion_code']) ?> sẽ bị xóa và gỡ khỏi mọi sản phẩm đang áp dụng."
                                        data-confirm-title="Xóa mã khuyến mãi?" data-confirm-variant="danger" data-confirm-ok="Xóa">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div data-filter-empty class="ad-no-result">
            <?= ad_empty('fa-magnifying-glass', 'Không tìm thấy mã phù hợp', 'Thử từ khóa khác hoặc bỏ bộ lọc.') ?>
        </div>
        <?= ad_pagination($page, $totalPages, 'index.php?controller=promotion&action=listPromotion', $totalPromotions) ?>
    <?php endif; ?>
</div>
