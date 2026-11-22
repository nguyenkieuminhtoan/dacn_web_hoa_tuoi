<?php
/**
 * Biến từ AccountCustomerController:
 * @var array $listAccCustomer
 */
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title">Khách hàng</h2>
        <p class="ad-head-sub"><?= count($listAccCustomer) ?> tài khoản khách hàng</p>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<div class="ad-card" data-filter>
    <div class="ad-toolbar">
        <div class="ad-search">
            <i class="fas fa-search"></i>
            <input type="search" class="form-control" placeholder="Tìm tên, tài khoản, email, số điện thoại..." data-filter-search aria-label="Tìm khách hàng">
        </div>
        <span class="ad-toolbar-count" data-filter-count></span>
    </div>

    <?php if (empty($listAccCustomer)): ?>
        <?= ad_empty('fa-users', 'Chưa có khách hàng nào', 'Khách đăng ký tài khoản trên cửa hàng sẽ hiện ở đây.') ?>
    <?php else: ?>
        <div class="ad-table-wrap">
            <table class="ad-table ad-stack-mobile">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Địa chỉ</th>
                        <th>Ngày tham gia</th>
                        <th class="ad-num">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listAccCustomer as $acc): ?>
                        <?php $initial = mb_strtoupper(mb_substr(trim($acc['full_name']) ?: $acc['username'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                        <tr data-search="<?= ad_e($acc['full_name'] . ' ' . $acc['username'] . ' ' . $acc['email'] . ' ' . $acc['phone']) ?>">
                            <td class="ad-cell-main">
                                <div class="ad-item">
                                    <span class="gn-avatar" aria-hidden="true"><?= ad_e($initial) ?></span>
                                    <div>
                                        <div class="ad-item-name"><?= ad_e($acc['full_name']) ?></div>
                                        <div class="ad-item-sub">@<?= ad_e($acc['username']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Email"><a href="mailto:<?= ad_e($acc['email']) ?>" class="text-reset"><?= ad_e($acc['email']) ?></a></td>
                            <td data-label="Điện thoại" class="ad-nowrap"><?= ad_e($acc['phone'] ?: '—') ?></td>
                            <td data-label="Địa chỉ"><div class="ad-clamp ad-small"><?= ad_e($acc['address'] ?: '—') ?></div></td>
                            <td data-label="Ngày tham gia" class="ad-nowrap"><?= ad_date($acc['created_at']) ?></td>
                            <td class="ad-cell-actions">
                                <div class="ad-actions">
                                    <a href="index.php?controller=accountCustomer&action=deleteAccountCustomer&customer_id=<?= (int) $acc['customer_id'] ?>"
                                        class="ad-icon-btn is-danger" title="Xóa tài khoản" aria-label="Xóa tài khoản <?= ad_e($acc['username']) ?>"
                                        data-confirm="Tài khoản @<?= ad_e($acc['username']) ?> cùng giỏ hàng, đơn hàng và bình luận của khách sẽ bị xóa vĩnh viễn."
                                        data-confirm-title="Xóa tài khoản khách hàng?" data-confirm-variant="danger" data-confirm-ok="Xóa">
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
            <?= ad_empty('fa-magnifying-glass', 'Không tìm thấy khách hàng phù hợp', 'Thử tên, email hoặc số điện thoại khác.') ?>
        </div>
    <?php endif; ?>
</div>
