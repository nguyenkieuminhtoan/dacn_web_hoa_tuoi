<?php
/**
 * Biến từ PromotionController::updateProductPromotion():
 * @var array $apply                Dòng sản phẩm-khuyến mãi đang sửa
 * @var array $listPromotionActive  Mã khuyến mãi đang hoạt động
 */
$ppId = (int) $apply['product_promotion_id'];
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title">Đổi mã khuyến mãi</h2>
        <p class="ad-head-sub"><?= ad_e($apply['product_name']) ?></p>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<form class="ad-form-layout" method="post" data-validate
    action="index.php?controller=promotion&action=updateProductPromotion&product_promotion_id=<?= $ppId ?>">
    <input type="hidden" name="product_promotion_id" value="<?= $ppId ?>">

    <div class="ad-form-main">
        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-ticket"></i> Chọn mã khuyến mãi mới</h3>
                <a class="ad-small" href="index.php?controller=promotion&action=addPromotion">+ Tạo mã mới</a>
            </div>
            <div class="ad-card-body">
                <?php if (empty($listPromotionActive)): ?>
                    <?= ad_empty('fa-ticket', 'Chưa có mã nào đang hoạt động', 'Tạo hoặc bật một mã khuyến mãi khác trước.') ?>
                <?php else: ?>
                    <div class="ad-choices" role="radiogroup" aria-label="Mã khuyến mãi">
                        <?php foreach ($listPromotionActive as $i => $pr): ?>
                            <?php $isCurrent = (int) $pr['promotion_id'] === (int) $apply['promotion_id']; ?>
                            <label class="ad-choice<?= $isCurrent ? ' is-current' : '' ?>">
                                <input type="radio" name="promotion_id" value="<?= (int) $pr['promotion_id'] ?>" <?= $isCurrent ? 'checked' : '' ?>
                                    <?= $i === 0 ? 'data-label="mã khuyến mãi" data-rules="required" data-msg-required="Vui lòng chọn một mã khuyến mãi"' : '' ?>>
                                <span class="ad-choice-card">
                                    <span class="ad-choice-title d-block"><?= ad_e($pr['promotion_name']) ?></span>
                                    <span class="ad-code mt-1 d-inline-block"><?= ad_e($pr['promotion_code']) ?></span>
                                    <?php if (!empty($pr['description'])): ?>
                                        <span class="ad-choice-sub ad-pre-line d-block"><?= ad_e($pr['description']) ?></span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="ad-error mt-2" data-error-for="promotion_id"></div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="ad-form-side">
        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-box"></i> Sản phẩm</h3>
            </div>
            <div class="ad-card-body">
                <div class="ad-item">
                    <?= ad_thumb($apply['image_url'], $apply['product_name'], 'ad-thumb-lg') ?>
                    <div>
                        <div class="ad-item-name"><?= ad_e($apply['product_name']) ?></div>
                        <div class="ad-item-sub">Mã SP #<?= (int) $apply['product_id'] ?></div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="ad-meta"><span>Mã hiện tại</span><span class="ad-code"><?= ad_e($apply['promotion_code']) ?></span></div>
                    <div class="ad-meta"><span>Chương trình</span><span><?= ad_e($apply['promotion_name']) ?></span></div>
                    <div class="ad-meta"><span>Mức giảm</span><span><?= ad_e(ad_discount($apply['discount_type'], $apply['discount_value'])) ?></span></div>
                </div>
            </div>
        </section>
    </div>

    <section class="ad-card ad-form-actions-card">
        <div class="ad-card-body">
            <div class="ad-form-actions">
                <a href="index.php?controller=promotion&action=listAllProductPromotion" class="btn ad-btn-light">Hủy</a>
                <button type="submit" name="btnUpdateProductPromotion" value="1" class="btn ad-btn-primary" <?= empty($listPromotionActive) ? 'disabled' : '' ?>>
                    <i class="fas fa-floppy-disk"></i> Lưu thay đổi
                </button>
            </div>
        </div>
    </section>
</form>
