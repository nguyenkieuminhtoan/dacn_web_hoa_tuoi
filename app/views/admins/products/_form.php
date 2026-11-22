<?php
/**
 * Form sản phẩm dùng chung cho Thêm (add_product.php) và Sửa (update_product.php).
 * @var string     $formMode       'add' | 'update'
 * @var array|null $oneProduct     Sản phẩm đang sửa (null khi thêm)
 * @var array      $listCategory   Danh mục
 * @var array      $listEnumStatus Các giá trị trạng thái (enum)
 */
$isEdit = $formMode === 'update';
$p = $oneProduct ?? [
    'product_id' => null, 'product_name' => '', 'price' => '', 'stock_quantity' => '',
    'category_id' => '', 'status' => 'available', 'description' => '', 'image_url' => '',
];
$action = $isEdit
    ? 'index.php?controller=product&action=updateProduct&product_id=' . (int) $p['product_id']
    : 'index.php?controller=product&action=addProduct';
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title"><?= $isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></h2>
        <p class="ad-head-sub"><?= $isEdit ? ad_e($p['product_name']) : 'Điền thông tin để đưa sản phẩm mới lên cửa hàng' ?></p>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<form class="ad-form-layout" action="<?= $action ?>" method="post" enctype="multipart/form-data" data-validate>
    <?php if ($isEdit): ?>
        <input type="hidden" name="product_id" value="<?= (int) $p['product_id'] ?>">
    <?php endif; ?>

    <!-- Cột trái: thông tin chính -->
    <div class="ad-form-main">
        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-circle-info"></i> Thông tin sản phẩm</h3>
            </div>
            <div class="ad-card-body">
                <div class="ad-field">
                    <label class="ad-label" for="product_name">Tên sản phẩm <span class="ad-req">*</span></label>
                    <input type="text" class="form-control" id="product_name" name="product_name"
                        value="<?= ad_e($p['product_name']) ?>" placeholder="VD: Bó hoa hồng đỏ tình yêu"
                        data-label="tên sản phẩm" data-rules="required|min:3|max:100">
                    <div class="ad-error" data-error-for="product_name"></div>
                </div>

                <div class="ad-grid-2 ad-field">
                    <div class="ad-field">
                        <label class="ad-label" for="price">Giá bán <span class="ad-req">*</span></label>
                        <div class="ad-input-group">
                            <input type="number" class="form-control" id="price" name="price" min="0" step="any"
                                value="<?= ad_e($p['price'] === '' ? '' : (float) $p['price']) ?>" placeholder="0" inputmode="numeric"
                                data-label="giá bán" data-rules="required|gte:1000|lte:999999999">
                            <span class="ad-suffix">₫</span>
                        </div>
                        <div class="ad-error" data-error-for="price"></div>
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="stock_quantity">Số lượng tồn kho <span class="ad-req">*</span></label>
                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" step="1"
                            value="<?= ad_e($p['stock_quantity']) ?>" placeholder="0" inputmode="numeric"
                            data-label="số lượng tồn kho" data-rules="required|int|gte:0">
                        <div class="ad-error" data-error-for="stock_quantity"></div>
                    </div>
                </div>

                <div class="ad-field">
                    <label class="ad-label" for="category_id">Danh mục <span class="ad-req">*</span></label>
                    <select class="form-select" id="category_id" name="category_id" data-label="danh mục" data-rules="required">
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($listCategory as $c): ?>
                            <option value="<?= (int) $c['category_id'] ?>" <?= (string) $c['category_id'] === (string) $p['category_id'] ? 'selected' : '' ?>>
                                <?= ad_e($c['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="ad-error" data-error-for="category_id"></div>
                </div>

                <div class="ad-field">
                    <label class="ad-label" for="description">Mô tả</label>
                    <textarea class="form-control" id="description" name="description" rows="6"
                        placeholder="Loại hoa, màu sắc, ý nghĩa, dịp tặng phù hợp..."><?= ad_e($p['description']) ?></textarea>
                </div>
            </div>
        </section>
    </div>

    <!-- Cột phải: ảnh + trạng thái -->
    <div class="ad-form-side">
        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-image"></i> Ảnh sản phẩm</h3>
            </div>
            <div class="ad-card-body">
                <label class="ad-upload" id="productUpload" data-upload>
                    <input type="file" name="image_url" accept="image/jpeg,image/png,image/gif,image/webp" aria-label="Chọn ảnh sản phẩm">
                    <img src="<?= $p['image_url'] ? ad_img_url($p['image_url']) : '' ?>" alt="Ảnh sản phẩm">
                    <div class="ad-upload-empty">
                        <i class="fas fa-cloud-arrow-up"></i>
                        Bấm hoặc kéo thả ảnh vào đây
                        <small>JPG, PNG, GIF, WEBP · tối đa 5MB</small>
                    </div>
                </label>
                <?php if ($isEdit && $p['image_url']): ?>
                    <div class="ad-hint">Chọn ảnh mới để thay ảnh hiện tại.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-toggle-on"></i> Trạng thái</h3>
            </div>
            <div class="ad-card-body">
                <select class="form-select" id="status" name="status" aria-label="Trạng thái sản phẩm">
                    <?php foreach ($listEnumStatus as $st): ?>
                        <option value="<?= ad_e($st) ?>" <?= $st === $p['status'] ? 'selected' : '' ?>><?= ad_e(ad_product_status_label($st)) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isEdit): ?>
                    <div class="mt-3">
                        <div class="ad-meta"><span>Mã sản phẩm</span><span>#<?= (int) $p['product_id'] ?></span></div>
                        <div class="ad-meta"><span>Ngày tạo</span><span><?= ad_date($p['created_at'] ?? null, true) ?></span></div>
                        <div class="ad-meta"><span>Cập nhật</span><span><?= ad_date($p['updated_at'] ?? null, true) ?></span></div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Nút lưu (mobile: dính đáy màn hình) -->
    <section class="ad-card ad-form-actions-card">
        <div class="ad-card-body">
            <div class="ad-form-actions">
                <a href="index.php?controller=product&action=listProduct" class="btn ad-btn-light">Hủy</a>
                <button type="submit" name="<?= $isEdit ? 'btnUpdateProduct' : 'btnAddProduct' ?>" value="1" class="btn ad-btn-primary">
                    <i class="fas fa-floppy-disk"></i> <?= $isEdit ? 'Lưu thay đổi' : 'Thêm sản phẩm' ?>
                </button>
            </div>
        </div>
    </section>
</form>
