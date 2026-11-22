<?php
/**
 * Biến từ AccountAdminController::listAccountAdmin():
 * @var array $listAccAdmin
 */
$currentAdminId = (int) ($_SESSION['admin']['admin_id'] ?? 0);
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title">Quản trị viên</h2>
        <p class="ad-head-sub"><?= count($listAccAdmin) ?> tài khoản có quyền vào trang quản trị</p>
    </div>
    <div class="ad-head-actions">
        <a href="index.php?controller=accountAdmin&action=addAccountAdmin" class="btn ad-btn-primary">
            <i class="fas fa-user-plus"></i> Thêm quản trị viên
        </a>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<div class="ad-card">
    <?php if (empty($listAccAdmin)): ?>
        <?= ad_empty('fa-user-shield', 'Chưa có quản trị viên', '') ?>
    <?php else: ?>
        <div class="ad-table-wrap">
            <table class="ad-table ad-stack-mobile">
                <thead>
                    <tr>
                        <th>Quản trị viên</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Ngày tạo</th>
                        <th class="ad-num">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listAccAdmin as $acc): ?>
                        <?php
                        $isMe = (int) $acc['admin_id'] === $currentAdminId;
                        $initial = mb_strtoupper(mb_substr(trim($acc['full_name']) ?: $acc['username'], 0, 1, 'UTF-8'), 'UTF-8');
                        ?>
                        <tr>
                            <td class="ad-cell-main">
                                <div class="ad-item">
                                    <span class="gn-avatar" aria-hidden="true"><?= ad_e($initial) ?></span>
                                    <div>
                                        <div class="ad-item-name">
                                            <?= ad_e($acc['full_name']) ?>
                                            <?php if ($isMe): ?><span class="ad-badge is-accent no-dot ms-1">Bạn</span><?php endif; ?>
                                        </div>
                                        <div class="ad-item-sub">@<?= ad_e($acc['username']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Email"><?= ad_e($acc['email']) ?></td>
                            <td data-label="Điện thoại" class="ad-nowrap"><?= ad_e($acc['phone'] ?: '—') ?></td>
                            <td data-label="Ngày tạo" class="ad-nowrap"><?= ad_date($acc['created_at']) ?></td>
                            <td class="ad-cell-actions">
                                <div class="ad-actions">
                                    <?php if ($isMe): ?>
                                        <a href="index.php?controller=profile" class="ad-icon-btn is-view" title="Hồ sơ của bạn" aria-label="Hồ sơ của bạn">
                                            <i class="fas fa-id-card"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?controller=accountAdmin&action=deleteAccountAdmin&admin_id=<?= (int) $acc['admin_id'] ?>"
                                            class="ad-icon-btn is-danger" title="Xóa tài khoản" aria-label="Xóa tài khoản <?= ad_e($acc['username']) ?>"
                                            data-confirm="Tài khoản @<?= ad_e($acc['username']) ?> sẽ không thể đăng nhập trang quản trị nữa."
                                            data-confirm-title="Xóa quản trị viên?" data-confirm-variant="danger" data-confirm-ok="Xóa">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
