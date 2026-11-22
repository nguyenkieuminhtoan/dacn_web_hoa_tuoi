<?php
/**
 * Form danh mục dùng chung cho Thêm / Sửa.
 * @var string     $formMode    'add' | 'update'
 * @var array|null $oneCategory Danh mục đang sửa
 */
$isEdit = $formMode === 'update';
$c = $oneCategory ?? ['category_id' => null, 'category_name' => '', 'description' => ''];
$action = $isEdit
    ? 'index.php?controller=category&action=updateCategory&category_id=' . (int) $c['category_id']
    : 'index.php?controller=category&action=addCategory';
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title"><?= $isEdit ? 'Sửa danh mục' : 'Thêm danh mục' ?></h2>
        <p class="ad-head-sub"><?= $isEdit ? ad_e($c['category_name']) : 'Tạo nhóm mới để phân loại sản phẩm' ?></p>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<form class="ad-form-layout" action="<?= $action ?>" method="post" data-validate>
    <div class="ad-form-main">
        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-circle-info"></i> Thông tin danh mục</h3>
            </div>
            <div class="ad-card-body">
                <div class="ad-field">
                    <label class="ad-label" for="category_name">Tên danh mục <span class="ad-req">*</span></label>
                    <input type="text" class="form-control" id="category_name" name="category_name"
                        value="<?= ad_e($c['category_name']) ?>" placeholder="VD: Hoa sinh nhật, Hoa cưới..."
                        data-label="tên danh mục" data-rules="required|min:3|max:100">
                    <div class="ad-error" data-error-for="category_name"></div>
                    <div class="ad-hint">Từ 3 đến 100 ký tự.</div>
                </div>
                <div class="ad-field">
                    <label class="ad-label" for="description">Mô tả</label>
                    <textarea class="form-control" id="description" name="description" rows="6"
                        placeholder="Danh mục này gồm những loại hoa nào, dùng cho dịp gì..."><?= ad_e($c['description']) ?></textarea>
                    <div class="ad-hint">Không bắt buộc.</div>
                </div>
            </div>
        </section>
    </div>

    <div class="ad-form-side">
        <?php if ($isEdit): ?>
            <section class="ad-card">
                <div class="ad-card-head">
                    <h3 class="ad-card-title"><i class="fas fa-clock-rotate-left"></i> Thông tin khác</h3>
                </div>
                <div class="ad-card-body">
                    <div class="ad-meta"><span>Mã danh mục</span><span>#<?= (int) $c['category_id'] ?></span></div>
                    <div class="ad-meta"><span>Ngày tạo</span><span><?= ad_date($c['created_at'] ?? null, true) ?></span></div>
                    <div class="ad-meta"><span>Cập nhật</span><span><?= ad_date($c['updated_at'] ?? null, true) ?></span></div>
                </div>
            </section>
        <?php else: ?>
            <section class="ad-card">
                <div class="ad-card-body ad-small ad-muted">
                    <i class="fas fa-lightbulb me-1"></i> Đặt tên ngắn gọn, dễ hiểu với khách — tên danh mục hiển thị trên trang cửa hàng.
                </div>
            </section>
        <?php endif; ?>
    </div>

    <section class="ad-card ad-form-actions-card">
        <div class="ad-card-body">
            <div class="ad-form-actions">
                <a href="index.php?controller=category&action=listCategory" class="btn ad-btn-light">Hủy</a>
                <button type="submit" name="<?= $isEdit ? 'btnUpdateCategory' : 'btnAddCategory' ?>" value="1" class="btn ad-btn-primary">
                    <i class="fas fa-floppy-disk"></i> <?= $isEdit ? 'Lưu thay đổi' : 'Thêm danh mục' ?>
                </button>
            </div>
        </div>
    </section>
</form>
