<?php
/**
 * Biến từ CategoryController::listCategory():
 * @var array $listCategory
 */
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title">Danh mục</h2>
        <p class="ad-head-sub"><?= count($listCategory) ?> danh mục sản phẩm</p>
    </div>
    <div class="ad-head-actions">
        <a href="index.php?controller=category&action=addCategory" class="btn ad-btn-primary">
            <i class="fas fa-plus"></i> Thêm danh mục
        </a>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<div class="ad-card" data-filter>
    <div class="ad-toolbar">
        <div class="ad-search">
            <i class="fas fa-search"></i>
            <input type="search" class="form-control" placeholder="Tìm theo tên hoặc mã..." data-filter-search aria-label="Tìm danh mục">
        </div>
        <span class="ad-toolbar-count" data-filter-count></span>
    </div>

    <?php if (empty($listCategory)): ?>
        <?= ad_empty('fa-folder-open', 'Chưa có danh mục nào', 'Tạo danh mục để phân loại sản phẩm.',
            '<a href="index.php?controller=category&action=addCategory" class="btn ad-btn-primary"><i class="fas fa-plus"></i> Thêm danh mục</a>') ?>
    <?php else: ?>
        <div class="ad-table-wrap">
            <table class="ad-table ad-stack-mobile">
                <thead>
                    <tr>
                        <th>Danh mục</th>
                        <th>Mô tả</th>
                        <th>Ngày tạo</th>
                        <th>Cập nhật</th>
                        <th class="ad-num">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listCategory as $c): ?>
                        <tr data-search="<?= ad_e($c['category_name'] . ' ' . $c['category_id']) ?>">
                            <td class="ad-cell-main">
                                <div class="ad-item">
                                    <span class="ad-thumb"><i class="fas fa-layer-group"></i></span>
                                    <div>
                                        <div class="ad-item-name"><?= ad_e($c['category_name']) ?></div>
                                        <div class="ad-item-sub">Mã #<?= (int) $c['category_id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Mô tả">
                                <div class="ad-clamp ad-muted" title="<?= ad_e($c['description']) ?>"><?= ad_e($c['description'] ?: '—') ?></div>
                            </td>
                            <td data-label="Ngày tạo" class="ad-nowrap"><?= ad_date($c['created_at']) ?></td>
                            <td data-label="Cập nhật" class="ad-nowrap"><?= ad_date($c['updated_at']) ?></td>
                            <td class="ad-cell-actions">
                                <div class="ad-actions">
                                    <a href="index.php?controller=category&action=updateCategory&category_id=<?= (int) $c['category_id'] ?>"
                                        class="ad-icon-btn is-edit" title="Sửa" aria-label="Sửa <?= ad_e($c['category_name']) ?>">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="index.php?controller=category&action=deleteCategory&category_id=<?= (int) $c['category_id'] ?>"
                                        class="ad-icon-btn is-danger" title="Xóa" aria-label="Xóa <?= ad_e($c['category_name']) ?>"
                                        data-confirm="Các sản phẩm thuộc danh mục “<?= ad_e($c['category_name']) ?>” sẽ bị ảnh hưởng."
                                        data-confirm-title="Xóa danh mục?" data-confirm-variant="danger" data-confirm-ok="Xóa">
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
            <?= ad_empty('fa-magnifying-glass', 'Không tìm thấy danh mục phù hợp', 'Thử từ khóa khác.') ?>
        </div>
    <?php endif; ?>
</div>
