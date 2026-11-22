<?php
/**
 * Biến từ OrderController::listOrderDetail():
 * @var array $order            Đơn hàng (kèm thông tin khách)
 * @var array $listOrderDetail  Các sản phẩm trong đơn
 */
$id = (int) $order['order_id'];
$st = $order['order_status'];
$totalQty = array_sum(array_column($listOrderDetail, 'quantity'));
?>
<div class="ad-head">
    <div>
        <h2 class="ad-head-title">Đơn <?= ad_e($order['order_code']) ?></h2>
        <p class="ad-head-sub">Đặt lúc <?= ad_date($order['order_date'], true) ?></p>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<div class="ad-stats">
    <div class="ad-stat">
        <div class="ad-stat-label">Trạng thái đơn</div>
        <div class="ad-stat-value"><?= ad_order_status($st) ?></div>
    </div>
    <div class="ad-stat">
        <div class="ad-stat-label">Thanh toán</div>
        <div class="ad-stat-value"><?= ad_payment_status($order['payment_status']) ?></div>
    </div>
    <div class="ad-stat">
        <div class="ad-stat-label">Phương thức</div>
        <div class="ad-stat-value ad-small"><?= ad_e(ad_payment_method($order['payment_method'])) ?></div>
    </div>
    <div class="ad-stat">
        <div class="ad-stat-label">Tổng tiền</div>
        <div class="ad-stat-value" style="color:var(--gn-accent-strong)"><?= ad_money($order['total_amount']) ?></div>
    </div>
</div>

<div class="ad-layout-detail">
    <div>
        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-box"></i> Sản phẩm trong đơn</h3>
                <span class="ad-small ad-muted"><?= (int) $totalQty ?> sản phẩm</span>
            </div>
            <div class="ad-table-wrap">
                <table class="ad-table ad-stack-mobile">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="ad-num">Số lượng</th>
                            <th class="ad-num">Đơn giá</th>
                            <th class="ad-num">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listOrderDetail as $item): ?>
                            <tr>
                                <td class="ad-cell-main">
                                    <div class="ad-item">
                                        <?= ad_thumb($item['image_url'], $item['product_name']) ?>
                                        <div class="ad-item-name"><?= ad_e($item['product_name']) ?></div>
                                    </div>
                                </td>
                                <td data-label="Số lượng" class="ad-num"><?= (int) $item['quantity'] ?></td>
                                <td data-label="Đơn giá" class="ad-num"><?= ad_money($item['unit_price']) ?></td>
                                <td data-label="Thành tiền" class="ad-num ad-strong"><?= ad_money($item['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="ad-card-foot d-flex justify-content-between align-items-center">
                <span class="ad-muted">Tổng cộng</span>
                <span class="ad-strong" style="font-size:1.1rem;color:var(--gn-accent-strong)"><?= ad_money($order['total_amount']) ?></span>
            </div>
        </section>

        <?php if (!empty($order['note'])): ?>
            <section class="ad-card">
                <div class="ad-card-head">
                    <h3 class="ad-card-title"><i class="fas fa-note-sticky"></i> Ghi chú của khách</h3>
                </div>
                <div class="ad-card-body"><?= nl2br(ad_e($order['note'])) ?></div>
            </section>
        <?php endif; ?>
    </div>

    <div>
        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-bolt"></i> Xử lý đơn</h3>
            </div>
            <div class="ad-card-body ad-stack">
                <?php if ($st === 'pending'): ?>
                    <a href="index.php?controller=order&action=confirm&order_id=<?= $id ?>" class="btn ad-btn-primary"
                        data-confirm="Đơn sẽ chuyển sang trạng thái “Đã xác nhận”." data-confirm-title="Xác nhận đơn hàng?"
                        data-confirm-variant="primary" data-confirm-ok="Xác nhận">
                        <i class="fas fa-check"></i> Xác nhận đơn
                    </a>
                <?php elseif ($st === 'confirmed'): ?>
                    <a href="index.php?controller=order&action=shipping&order_id=<?= $id ?>" class="btn ad-btn-primary"
                        data-confirm="Đơn sẽ chuyển sang trạng thái “Đang giao”." data-confirm-title="Bắt đầu giao hàng?"
                        data-confirm-variant="primary" data-confirm-ok="Giao hàng">
                        <i class="fas fa-truck-fast"></i> Bắt đầu giao hàng
                    </a>
                <?php elseif ($st === 'shipping'): ?>
                    <a href="index.php?controller=order&action=completed&order_id=<?= $id ?>" class="btn btn-success"
                        data-confirm="Xác nhận khách đã nhận hàng thành công." data-confirm-title="Hoàn thành đơn hàng?"
                        data-confirm-variant="success" data-confirm-ok="Hoàn thành">
                        <i class="fas fa-flag-checkered"></i> Đã giao thành công
                    </a>
                <?php endif; ?>

                <?php if (in_array($st, ['pending', 'confirmed', 'shipping'], true)): ?>
                    <a href="index.php?controller=order&action=cancelled&order_id=<?= $id ?>" class="btn ad-btn-light text-danger"
                        data-confirm="Đơn sẽ chuyển sang trạng thái “Đã hủy”." data-confirm-title="Hủy đơn hàng?"
                        data-confirm-variant="danger" data-confirm-ok="Hủy đơn" data-confirm-cancel="Không">
                        <i class="fas fa-ban"></i> Hủy đơn
                    </a>
                <?php else: ?>
                    <p class="ad-small ad-muted mb-1">Đơn đã <?= $st === 'completed' ? 'hoàn thành' : 'bị hủy' ?>, không còn bước xử lý.</p>
                    <a href="index.php?controller=order&action=deleted&order_id=<?= $id ?>" class="btn ad-btn-light text-danger"
                        data-confirm="Đơn hàng sẽ bị xóa khỏi hệ thống và không thể khôi phục." data-confirm-title="Xóa vĩnh viễn đơn hàng?"
                        data-confirm-variant="danger" data-confirm-ok="Xóa vĩnh viễn">
                        <i class="fas fa-trash-alt"></i> Xóa đơn hàng
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-truck"></i> Giao hàng</h3>
            </div>
            <div class="ad-card-body">
                <dl class="ad-dl">
                    <div><dt>Người nhận</dt><dd><?= ad_e($order['receiver_name']) ?></dd></div>
                    <div><dt>Điện thoại</dt><dd><a href="tel:<?= ad_e($order['receiver_phone']) ?>"><?= ad_e($order['receiver_phone']) ?></a></dd></div>
                    <div><dt>Địa chỉ</dt><dd><?= ad_e($order['delivery_address']) ?></dd></div>
                </dl>
            </div>
        </section>

        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-user"></i> Khách đặt hàng</h3>
            </div>
            <div class="ad-card-body">
                <dl class="ad-dl">
                    <div><dt>Họ tên</dt><dd><?= ad_e($order['customer_name']) ?></dd></div>
                    <div><dt>Email</dt><dd><?= ad_e($order['email']) ?></dd></div>
                    <div><dt>Điện thoại</dt><dd><?= ad_e($order['phone'] ?: '—') ?></dd></div>
                </dl>
            </div>
        </section>
    </div>
</div>
