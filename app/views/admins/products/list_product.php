<?php
/**
 * Biến từ ProductController::listProduct():
 * @var array $listProduct   Sản phẩm của trang hiện tại
 * @var int   $page          Trang hiện tại
 * @var int   $totalPages    Tổng số trang
 * @var int   $totalProducts Tổng số sản phẩm
 */
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title">Sản phẩm</h2>
        <p class="ad-head-sub"><?= (int) $totalProducts ?> sản phẩm trong cửa hàng</p>
    </div>
    <div class="ad-head-actions">
        <a href="index.php?controller=product&action=addProduct" class="btn ad-btn-primary">
            <i class="fas fa-plus"></i> Thêm sản phẩm
        </a>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<div class="ad-card" data-filter>
    <div class="ad-toolbar">
        <div class="ad-search">
            <i class="fas fa-search"></i>
            <input type="search" class="form-control" placeholder="Tìm theo tên hoặc mã..." data-filter-search
                aria-label="Tìm sản phẩm">
        </div>
        <select class="form-select" data-filter-status aria-label="Lọc theo trạng thái">
            <option value="">Tất cả trạng thái</option>
            <option value="available">Còn hàng</option>
            <option value="out_of_stock">Tạm hết hàng</option>
            <option value="discontinued">Ngừng kinh doanh</option>
        </select>
        <span class="ad-toolbar-count" data-filter-count></span>
    </div>

    <?php if (empty($listProduct)): ?>
        <?= ad_empty('fa-box-open', 'Chưa có sản phẩm nào', 'Hãy thêm sản phẩm đầu tiên cho cửa hàng.',
            '<a href="index.php?controller=product&action=addProduct" class="btn ad-btn-primary"><i class="fas fa-plus"></i> Thêm sản phẩm</a>') ?>
    <?php else: ?>
        <div class="ad-table-wrap">
            <table class="ad-table ad-stack-mobile">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Danh mục</th>
                        <th class="ad-num">Giá bán</th>
                        <th class="ad-num">Tồn kho</th>
                        <th>Trạng thái</th>
                        <th class="ad-num">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listProduct as $p): ?>
                        <tr data-search="<?= ad_e($p['product_name'] . ' ' . $p['product_id']) ?>" data-status="<?= ad_e($p['status']) ?>">
                            <td class="ad-cell-main">
                                <div class="ad-item">
                                    <?= ad_thumb($p['image_url'], $p['product_name']) ?>
                                    <div>
                                        <div class="ad-item-name"><?= ad_e($p['product_name']) ?></div>
                                        <div class="ad-item-sub">Mã #<?= (int) $p['product_id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Danh mục"><?= ad_e($p['category_name'] ?? '—') ?></td>
                            <td data-label="Giá bán" class="ad-num">
                                <?php if (isset($p['discounted_price'])): ?>
                                    <div>
                                        <div class="ad-price-new"><?= ad_money($p['discounted_price']) ?></div>
                                        <div class="ad-price-old"><?= ad_money($p['price']) ?></div>
                                    </div>
                                <?php else: ?>
                                    <span class="ad-strong"><?= ad_money($p['price']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Tồn kho" class="ad-num">
                                <span class="<?= (int) $p['stock_quantity'] <= 5 ? 'text-danger ad-strong' : '' ?>">
                                    <?= number_format((int) $p['stock_quantity'], 0, ',', '.') ?>
                                </span>
                            </td>
                            <td data-label="Trạng thái"><?= ad_product_status($p['status']) ?></td>
                            <td class="ad-cell-actions">
                                <div class="ad-actions">
                                    <a href="index.php?controller=product&action=updateProduct&product_id=<?= (int) $p['product_id'] ?>"
                                        class="ad-icon-btn is-edit" title="Sửa" aria-label="Sửa <?= ad_e($p['product_name']) ?>">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="index.php?controller=product&action=deleteProduct&product_id=<?= (int) $p['product_id'] ?>"
                                        class="ad-icon-btn is-danger" title="Xóa" aria-label="Xóa <?= ad_e($p['product_name']) ?>"
                                        data-confirm="Sản phẩm “<?= ad_e($p['product_name']) ?>” sẽ bị xóa khỏi cửa hàng và không thể khôi phục."
                                        data-confirm-title="Xóa sản phẩm?" data-confirm-variant="danger" data-confirm-ok="Xóa">
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
            <?= ad_empty('fa-magnifying-glass', 'Không tìm thấy sản phẩm phù hợp', 'Thử từ khóa khác hoặc bỏ bộ lọc trạng thái.') ?>
        </div>
        <?= ad_pagination($page, $totalPages, 'index.php?controller=product&action=listProduct', $totalProducts) ?>
    <?php endif; ?>
</div>
