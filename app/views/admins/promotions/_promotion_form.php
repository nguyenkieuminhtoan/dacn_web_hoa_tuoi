<?php
/**
 * Form mã khuyến mãi dùng chung cho Thêm / Sửa.
 * @var string     $formMode             'add' | 'update'
 * @var array|null $onePromotion         Mã đang sửa
 * @var array      $listEnumDiscountType percentage | fixed_amount
 * @var array      $listEnumStatus       active | inactive | expired
 */
$isEdit = $formMode === 'update';
$pr = $onePromotion ?? [
    'promotion_id' => null, 'promotion_code' => '', 'promotion_name' => '', 'description' => '',
    'discount_type' => 'percentage', 'discount_value' => '', 'start_date' => date('Y-m-d'),
    'end_date' => '', 'status' => 'active',
];
$action = $isEdit
    ? 'index.php?controller=promotion&action=updatePromotion&promotion_id=' . (int) $pr['promotion_id']
    : 'index.php?controller=promotion&action=addPromotion';
// "15.00" -> "15" (chỉ cắt số 0 sau dấu chấm thập phân)
$discountValue = (string) $pr['discount_value'];
if (strpos($discountValue, '.') !== false) $discountValue = rtrim(rtrim($discountValue, '0'), '.');
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title"><?= $isEdit ? 'Sửa mã khuyến mãi' : 'Tạo mã khuyến mãi' ?></h2>
        <p class="ad-head-sub"><?= $isEdit ? ad_e($pr['promotion_code'] . ' · ' . $pr['promotion_name']) : 'Tạo chương trình giảm giá để áp dụng cho sản phẩm' ?></p>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<form class="ad-form-layout" action="<?= $action ?>" method="post" data-validate>
    <div class="ad-form-main">
        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-ticket"></i> Thông tin chương trình</h3>
            </div>
            <div class="ad-card-body">
                <div class="ad-grid-2">
                    <div class="ad-field">
                        <label class="ad-label" for="promotion_code">Mã khuyến mãi <?= $isEdit ? '' : '<span class="ad-req">*</span>' ?></label>
                        <?php if ($isEdit): ?>
                            <input type="text" class="form-control" id="promotion_code" value="<?= ad_e($pr['promotion_code']) ?>" disabled>
                            <div class="ad-hint">Mã đã tạo thì không đổi được.</div>
                        <?php else: ?>
                            <input type="text" class="form-control text-uppercase" id="promotion_code" name="promotion_code"
                                placeholder="VD: NOEL2026" autocomplete="off"
                                data-label="mã khuyến mãi" data-rules="required|min:3|max:50">
                            <div class="ad-error" data-error-for="promotion_code"></div>
                            <div class="ad-hint">Viết liền, không dấu. Mỗi mã chỉ dùng một lần.</div>
                        <?php endif; ?>
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="promotion_name">Tên chương trình <span class="ad-req">*</span></label>
                        <input type="text" class="form-control" id="promotion_name" name="promotion_name"
                            value="<?= ad_e($pr['promotion_name']) ?>" placeholder="VD: Giáng Sinh an lành"
                            data-label="tên chương trình" data-rules="required|max:200">
                        <div class="ad-error" data-error-for="promotion_name"></div>
                    </div>
                    <div class="ad-field ad-span-2">
                        <label class="ad-label" for="description">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                            placeholder="Áp dụng cho những sản phẩm nào, điều kiện gì..."><?= ad_e($pr['description']) ?></textarea>
                    </div>
                </div>
            </div>
        </section>

        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-percent"></i> Mức giảm & thời gian</h3>
            </div>
            <div class="ad-card-body">
                <div class="ad-grid-2">
                    <div class="ad-field">
                        <label class="ad-label" for="discount_type">Kiểu giảm <span class="ad-req">*</span></label>
                        <select class="form-select" id="discount_type" name="discount_type" data-label="kiểu giảm" data-rules="required">
                            <?php foreach ($listEnumDiscountType as $type): ?>
                                <option value="<?= ad_e($type) ?>" <?= $type === $pr['discount_type'] ? 'selected' : '' ?>><?= ad_e(ad_discount_type_label($type)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="ad-error" data-error-for="discount_type"></div>
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="discount_value">Giá trị giảm <span class="ad-req">*</span></label>
                        <div class="ad-input-group">
                            <input type="number" class="form-control" id="discount_value" name="discount_value" min="0" step="any"
                                value="<?= ad_e($discountValue) ?>" placeholder="0" inputmode="decimal"
                                data-label="giá trị giảm" data-rules="required|gte:1">
                            <span class="ad-suffix" id="discountSuffix">%</span>
                        </div>
                        <div class="ad-error" data-error-for="discount_value"></div>
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="start_date">Ngày bắt đầu <span class="ad-req">*</span></label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= ad_e($pr['start_date']) ?>"
                            data-label="ngày bắt đầu" data-rules="required">
                        <div class="ad-error" data-error-for="start_date"></div>
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="end_date">Ngày kết thúc <span class="ad-req">*</span></label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= ad_e($pr['end_date']) ?>"
                            data-label="ngày kết thúc" data-rules="required|after:start_date">
                        <div class="ad-error" data-error-for="end_date"></div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="ad-form-side">
        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-toggle-on"></i> Trạng thái</h3>
            </div>
            <div class="ad-card-body">
                <select class="form-select" id="status" name="status" aria-label="Trạng thái mã khuyến mãi">
                    <?php foreach ($listEnumStatus as $st): ?>
                        <option value="<?= ad_e($st) ?>" <?= $st === $pr['status'] ? 'selected' : '' ?>><?= ad_e(ad_promo_status_label($st)) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="ad-hint">Chỉ mã “Đang hoạt động” mới chọn được khi áp dụng cho sản phẩm.</div>
                <?php if ($isEdit): ?>
                    <div class="mt-3">
                        <div class="ad-meta"><span>Mã</span><span class="ad-code"><?= ad_e($pr['promotion_code']) ?></span></div>
                        <div class="ad-meta"><span>Ngày tạo</span><span><?= ad_date($pr['created_at'] ?? null, true) ?></span></div>
                        <div class="ad-meta"><span>Cập nhật</span><span><?= ad_date($pr['updated_at'] ?? null, true) ?></span></div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <section class="ad-card ad-form-actions-card">
        <div class="ad-card-body">
            <div class="ad-form-actions">
                <a href="index.php?controller=promotion&action=listPromotion" class="btn ad-btn-light">Hủy</a>
                <button type="submit" name="<?= $isEdit ? 'btnUpdatePromotion' : 'btnAddPromotion' ?>" value="1" class="btn ad-btn-primary">
                    <i class="fas fa-floppy-disk"></i> <?= $isEdit ? 'Lưu thay đổi' : 'Tạo mã' ?>
                </button>
            </div>
        </div>
    </section>
</form>

<script>
    // Đổi đơn vị (% / ₫) và giới hạn 100% theo kiểu giảm
    (function() {
        const type = document.getElementById('discount_type');
        const value = document.getElementById('discount_value');
        const suffix = document.getElementById('discountSuffix');
        function sync() {
            const pct = type.value === 'percentage';
            suffix.textContent = pct ? '%' : '₫';
            value.dataset.rules = pct ? 'required|gte:1|lte:100' : 'required|gte:1000';
        }
        type.addEventListener('change', sync);
        sync();
    })();
</script>
