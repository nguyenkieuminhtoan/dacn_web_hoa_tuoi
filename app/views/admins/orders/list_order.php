<?php
/**
 * Biến từ OrderController::listOrder():
 * @var array       $listOrder
 * @var string|null $status    Trạng thái đang lọc (null = tất cả)
 */
$orderTabs = ['' => 'Tất cả', 'pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang giao', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];

// Bước tiếp theo theo quy trình: [action, nhãn, icon, tiêu đề xác nhận, nội dung, variant]
$nextStep = [
    'pending'   => ['confirm', 'Xác nhận', 'fa-check', 'Xác nhận đơn hàng?', 'Đơn sẽ chuyển sang trạng thái “Đã xác nhận”.', 'primary'],
    'confirmed' => ['shipping', 'Giao hàng', 'fa-truck-fast', 'Bắt đầu giao hàng?', 'Đơn sẽ chuyển sang trạng thái “Đang giao”.', 'primary'],
    'shipping'  => ['completed', 'Hoàn thành', 'fa-flag-checkered', 'Hoàn thành đơn hàng?', 'Xác nhận khách đã nhận hàng thành công.', 'success'],
];
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title">Đơn hàng</h2>
        <p class="ad-head-sub"><?= count($listOrder) ?> đơn <?= $status ? '“' . ad_e(ad_order_status_label($status)) . '”' : 'trong hệ thống' ?></p>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<div class="ad-card" data-filter>
    <nav class="ad-tabs" aria-label="Lọc theo trạng thái">
        <?php foreach ($orderTabs as $key => $label): ?>
            <?php $key = $key === '' ? null : $key; // '' = tab Tất cả ?>
            <a class="ad-tab<?= ($status ?: null) === $key ? ' active' : '' ?>"
                href="index.php?controller=order&action=listOrder<?= $key ? '&status=' . $key : '' ?>"
                <?= ($status ?: null) === $key ? 'aria-current="page"' : '' ?>><?= $label ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="ad-toolbar">
        <div class="ad-search">
            <i class="fas fa-search"></i>
            <input type="search" class="form-control" placeholder="Tìm mã đơn, tên khách..." data-filter-search aria-label="Tìm đơn hàng">
        </div>
        <span class="ad-toolbar-count" data-filter-count></span>
    </div>

    <?php if (empty($listOrder)): ?>
        <?= ad_empty('fa-receipt', 'Không có đơn hàng nào', $status ? 'Chưa có đơn ở trạng thái này.' : 'Đơn hàng của khách sẽ hiện ở đây.') ?>
    <?php else: ?>
        <div class="ad-table-wrap">
            <table class="ad-table ad-stack-mobile">
                <thead>
                    <tr>
                        <th>Đơn hàng</th>
                        <th>Ngày đặt</th>
                        <th>Thanh toán</th>
                        <th class="ad-num">Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th class="ad-num">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listOrder as $o): ?>
                        <?php
                        $id = (int) $o['order_id'];
                        $st = $o['order_status'];
                        $detailUrl = "index.php?controller=order&action=listOrderDetail&order_id=$id";
                        ?>
                        <tr data-search="<?= ad_e($o['order_code'] . ' ' . $o['customer_name']) ?>">
                            <td class="ad-cell-main">
                                <a href="<?= $detailUrl ?>" class="text-decoration-none">
                                    <div class="ad-item-name"><?= ad_e($o['order_code']) ?></div>
                                </a>
                                <div class="ad-item-sub"><i class="far fa-user me-1"></i><?= ad_e($o['customer_name']) ?></div>
                            </td>
                            <td data-label="Ngày đặt" class="ad-nowrap"><?= ad_date($o['order_date'], true) ?></td>
                            <td data-label="Thanh toán">
                                <div>
                                    <div class="ad-small"><?= ad_e(ad_payment_method($o['payment_method'])) ?></div>
                                    <div class="mt-1"><?= ad_payment_status($o['payment_status']) ?></div>
                                </div>
                            </td>
                            <td data-label="Tổng tiền" class="ad-num ad-strong"><?= ad_money($o['total_amount']) ?></td>
                            <td data-label="Trạng thái"><?= ad_order_status($st) ?></td>
                            <td class="ad-cell-actions">
                                <div class="ad-actions">
                                    <?php if (isset($nextStep[$st])): [$act, $label, $icon, $cTitle, $cMsg, $variant] = $nextStep[$st]; ?>
                                        <a href="index.php?controller=order&action=<?= $act ?>&order_id=<?= $id ?>"
                                            class="btn btn-sm ad-btn-primary" title="<?= $label ?>"
                                            data-confirm="<?= ad_e($cMsg) ?>" data-confirm-title="<?= ad_e($cTitle) ?>"
                                            data-confirm-variant="<?= $variant ?>" data-confirm-ok="<?= $label ?>">
                                            <i class="fas <?= $icon ?>"></i> <?= $label ?>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= $detailUrl ?>" class="ad-icon-btn is-view" title="Xem chi tiết" aria-label="Xem chi tiết <?= ad_e($o['order_code']) ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (in_array($st, ['pending', 'confirmed', 'shipping'], true)): ?>
                                        <a href="index.php?controller=order&action=cancelled&order_id=<?= $id ?>"
                                            class="ad-icon-btn is-danger" title="Hủy đơn" aria-label="Hủy đơn <?= ad_e($o['order_code']) ?>"
                                            data-confirm="Đơn <?= ad_e($o['order_code']) ?> sẽ chuyển sang trạng thái “Đã hủy”."
                                            data-confirm-title="Hủy đơn hàng?" data-confirm-variant="danger" data-confirm-ok="Hủy đơn" data-confirm-cancel="Không">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?controller=order&action=deleted&order_id=<?= $id ?>"
                                            class="ad-icon-btn is-danger" title="Xóa vĩnh viễn" aria-label="Xóa đơn <?= ad_e($o['order_code']) ?>"
                                            data-confirm="Đơn <?= ad_e($o['order_code']) ?> sẽ bị xóa khỏi hệ thống và không thể khôi phục."
                                            data-confirm-title="Xóa vĩnh viễn đơn hàng?" data-confirm-variant="danger" data-confirm-ok="Xóa vĩnh viễn">
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
        <div data-filter-empty class="ad-no-result">
            <?= ad_empty('fa-magnifying-glass', 'Không tìm thấy đơn hàng phù hợp', 'Thử mã đơn hoặc tên khách khác.') ?>
        </div>
    <?php endif; ?>
</div>
