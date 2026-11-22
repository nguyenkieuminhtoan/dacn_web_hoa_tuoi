<?php
/**
 * Biến từ ProfileController::index():
 * @var array $admin
 */
$initial = mb_strtoupper(mb_substr(trim($admin['full_name']) ?: $admin['username'], 0, 1, 'UTF-8'), 'UTF-8');
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title">Hồ sơ của tôi</h2>
        <p class="ad-head-sub">Thông tin tài khoản quản trị bạn đang đăng nhập</p>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<div class="ad-layout-detail">
    <section class="ad-card">
        <div class="ad-card-head">
            <h3 class="ad-card-title"><i class="fas fa-id-card"></i> Thông tin tài khoản</h3>
        </div>
        <div class="ad-card-body">
            <dl class="ad-dl">
                <div><dt>Họ và tên</dt><dd><?= ad_e($admin['full_name']) ?></dd></div>
                <div><dt>Tên đăng nhập</dt><dd>@<?= ad_e($admin['username']) ?></dd></div>
                <div><dt>Email</dt><dd><?= ad_e($admin['email']) ?></dd></div>
                <div><dt>Số điện thoại</dt><dd><?= ad_e($admin['phone'] ?: '—') ?></dd></div>
                <div><dt>Ngày tạo</dt><dd><?= ad_date($admin['created_at'], true) ?></dd></div>
                <div><dt>Cập nhật lần cuối</dt><dd><?= ad_date($admin['updated_at'], true) ?></dd></div>
            </dl>
        </div>
    </section>

    <section class="ad-card">
        <div class="ad-card-body text-center">
            <span class="gn-avatar mx-auto mb-3" style="width:72px;height:72px;font-size:1.6rem"><?= ad_e($initial) ?></span>
            <div class="ad-item-name" style="font-size:1.05rem"><?= ad_e($admin['full_name']) ?></div>
            <div class="ad-item-sub mb-3">Quản trị viên</div>
            <a href="<?= $gnLogout ?>" class="btn ad-btn-light w-100 text-danger" <?= $gnLogoutConfirm ?>>
                <i class="fas fa-arrow-right-from-bracket"></i> Đăng xuất
            </a>
        </div>
    </section>
</div>
