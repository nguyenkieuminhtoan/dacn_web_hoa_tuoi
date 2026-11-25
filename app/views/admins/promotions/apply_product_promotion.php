<?php
/**
 * Biến từ PromotionController::applyProductPromotion():
 * @var array $listProduct          Tất cả sản phẩm
 * @var array $listPromotionActive  Mã khuyến mãi đang hoạt động
 */
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title">Áp dụng khuyến mãi</h2>
        <p class="ad-head-sub">Chọn sản phẩm và mã khuyến mãi muốn gắn cho sản phẩm đó</p>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<form class="ad-form-layout" method="post" action="index.php?controller=promotion&action=applyProductPromotion" data-validate>
    <div class="ad-form-main">
        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><span class="ad-badge is-accent no-dot">1</span> Chọn sản phẩm</h3>
            </div>
            <div class="ad-card-body">
                <label class="ad-label" for="productSelect">Sản phẩm <span class="ad-req">*</span></label>
                <select class="form-select" id="productSelect" name="product_id" data-label="sản phẩm" data-rules="required">
                    <option value="">-- Chọn sản phẩm --</option>
                    <?php foreach ($listProduct as $p): ?>
                        <option value="<?= (int) $p['product_id'] ?>"
                            data-img="<?= $p['image_url'] ? ad_img_url($p['image_url']) : '' ?>"
                            data-price="<?= ad_e(ad_money($p['price'])) ?>"
                            data-busy="<?= isset($p['discounted_price']) ? '1' : '' ?>">
                            <?= ad_e($p['product_name']) ?> — <?= ad_money($p['price']) ?><?= isset($p['discounted_price']) ? ' (đang có khuyến mãi)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="ad-error" data-error-for="product_id"></div>
                <div class="ad-hint">Mỗi sản phẩm chỉ có một khuyến mãi đang hoạt động tại một thời điểm.</div>
            </div>
        </section>

        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><span class="ad-badge is-accent no-dot">2</span> Chọn mã khuyến mãi</h3>
                <a class="ad-small" href="index.php?controller=promotion&action=addPromotion">+ Tạo mã mới</a>
            </div>
            <div class="ad-card-body">
                <?php if (empty($listPromotionActive)): ?>
                    <?= ad_empty('fa-ticket', 'Chưa có mã nào đang hoạt động', 'Tạo hoặc bật một mã khuyến mãi trước khi áp dụng.',
                        '<a class="btn ad-btn-primary" href="index.php?controller=promotion&action=addPromotion"><i class="fas fa-plus"></i> Tạo mã khuyến mãi</a>') ?>
                <?php else: ?>
                    <div class="ad-choices" role="radiogroup" aria-label="Mã khuyến mãi">
                        <?php foreach ($listPromotionActive as $i => $pr): ?>
                            <label class="ad-choice">
                                <input type="radio" name="promotion_id" value="<?= (int) $pr['promotion_id'] ?>"
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
                <h3 class="ad-card-title"><i class="fas fa-eye"></i> Sản phẩm đã chọn</h3>
            </div>
            <div class="ad-card-body">
                <div id="applyPreview" class="ad-muted ad-small">Chưa chọn sản phẩm.</div>
            </div>
        </section>
    </div>

    <section class="ad-card ad-form-actions-card">
        <div class="ad-card-body">
            <div class="ad-form-actions">
                <a href="index.php?controller=promotion&action=listAllProductPromotion" class="btn ad-btn-light">Hủy</a>
                <button type="submit" name="btnApplyProductPromotion" value="1" class="btn ad-btn-primary" <?= empty($listPromotionActive) ? 'disabled' : '' ?>>
                    <i class="fas fa-check"></i> Áp dụng
                </button>
            </div>
        </div>
    </section>
</form>

<script>
    // Xem trước sản phẩm đã chọn
    (function() {
        const select = document.getElementById('productSelect');
        const box = document.getElementById('applyPreview');
        select.addEventListener('change', function() {
            const opt = select.options[select.selectedIndex];
            if (!select.value) {
                box.textContent = 'Chưa chọn sản phẩm.';
                return;
            }
            const name = opt.textContent.split(' — ')[0].trim();
            box.innerHTML = '';
            const wrap = document.createElement('div');
            wrap.className = 'ad-item';
            if (opt.dataset.img) {
                const img = document.createElement('img');
                img.className = 'ad-thumb ad-thumb-lg';
                img.src = opt.dataset.img;
                img.alt = '';
                wrap.appendChild(img);
            }
            const info = document.createElement('div');
            info.innerHTML = '<div class="ad-item-name"></div><div class="ad-item-sub"></div>';
            info.children[0].textContent = name;
            info.children[1].textContent = 'Giá gốc ' + opt.dataset.price;
            wrap.appendChild(info);
            box.appendChild(wrap);
            if (opt.dataset.busy) {
                const warn = document.createElement('div');
                warn.className = 'ad-badge is-warning mt-3';
                warn.textContent = 'Sản phẩm đang có khuyến mãi khác';
                box.appendChild(warn);
            }
        });
    })();
</script>
