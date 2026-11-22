<?php
// Biến có sẵn từ DashboardController::index():
// $from, $to, $summary, $latestOrders, $topProducts, $lowStock, $lowStockLimit
require_once __DIR__ . '/../../../helpers/message_helper.php';

function ov_money($v)
{
    return number_format((float) $v, 0, ',', '.') . ' ₫';
}

function ov_status_badge($status)
{
    $map = [
        'pending'   => ['warning', 'Chờ xác nhận'],
        'confirmed' => ['primary', 'Đã xác nhận'],
        'shipping'  => ['info', 'Đang giao'],
        'completed' => ['success', 'Hoàn thành'],
        'cancelled' => ['secondary', 'Đã hủy'],
    ];
    [$color, $text] = $map[$status] ?? ['danger', 'Lỗi trạng thái'];
    return "<span class=\"badge bg-$color\">$text</span>";
}

$uploadDir = '../../../uploads/';
?>
<style>
    .ov-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .05);
    }

    .ov-stat {
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        height: 100%;
    }

    .ov-stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .ov-stat-label {
        font-size: .8rem;
        color: #64748b;
        margin-bottom: 2px;
    }

    .ov-stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
        font-variant-numeric: tabular-nums;
    }

    .ov-stat-sub {
        font-size: .75rem;
        color: #94a3b8;
    }

    .ov-card-header {
        padding: 14px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ov-card-header h6 {
        margin: 0;
        font-weight: 700;
        color: #0f172a;
    }

    .ov-table {
        margin: 0;
        font-size: .875rem;
    }

    .ov-table th {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #64748b;
        font-weight: 600;
        background: #f8fafc !important;
        white-space: nowrap;
    }

    .ov-table td,
    .ov-table th {
        padding: 10px 16px;
        vertical-align: middle;
    }

    .ov-num {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .ov-thumb {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 8px;
        background: #f1f5f9;
        flex-shrink: 0;
    }

    .ov-empty {
        padding: 28px 16px;
        text-align: center;
        color: #94a3b8;
        font-size: .875rem;
    }

    /* Điện thoại: 2 cột gọn, bỏ icon để số không bị tràn */
    @media (max-width: 575.98px) {
        .ov-stat {
            padding: 14px;
        }

        .ov-stat-icon {
            display: none;
        }

        .ov-stat-value {
            font-size: 1.1rem;
        }

        .ov-table td,
        .ov-table th {
            padding: 9px 12px;
            white-space: nowrap;
        }
    }
</style>

<div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Tổng quan</h3>
        <div class="text-muted small">
            Số liệu từ <strong><?= date('d/m/Y', strtotime($from)) ?></strong>
            đến <strong><?= date('d/m/Y', strtotime($to)) ?></strong>
        </div>
    </div>

    <form method="get" action="index.php" class="d-flex flex-wrap align-items-end gap-2">
        <input type="hidden" name="controller" value="dashboard">
        <div>
            <label for="ovFrom" class="form-label small text-muted mb-1">Từ ngày</label>
            <input type="date" id="ovFrom" name="from" class="form-control form-control-sm"
                value="<?= htmlspecialchars($from) ?>" max="<?= htmlspecialchars($to) ?>">
        </div>
        <div>
            <label for="ovTo" class="form-label small text-muted mb-1">Đến ngày</label>
            <input type="date" id="ovTo" name="to" class="form-control form-control-sm"
                value="<?= htmlspecialchars($to) ?>" min="<?= htmlspecialchars($from) ?>">
        </div>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Lọc</button>
        <a href="index.php?controller=dashboard" class="btn btn-sm btn-outline-secondary">30 ngày gần nhất</a>
    </form>
</div>

<?php MessageHelper::logMessage(); ?>

<!-- Số tổng -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="ov-card ov-stat">
            <div class="ov-stat-icon bg-success-subtle text-success"><i class="fas fa-sack-dollar"></i></div>
            <div>
                <div class="ov-stat-label">Doanh thu</div>
                <div class="ov-stat-value"><?= ov_money($summary['revenue']) ?></div>
                <div class="ov-stat-sub">Không tính đơn đã hủy</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="ov-card ov-stat">
            <div class="ov-stat-icon bg-primary-subtle text-primary"><i class="fas fa-receipt"></i></div>
            <div>
                <div class="ov-stat-label">Số đơn hàng</div>
                <div class="ov-stat-value"><?= (int) $summary['total_orders'] ?></div>
                <div class="ov-stat-sub"><?= (int) $summary['cancelled_orders'] ?> đơn đã hủy</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <a href="index.php?controller=order&action=listOrder&status=pending" class="text-decoration-none">
            <div class="ov-card ov-stat">
                <div class="ov-stat-icon bg-warning-subtle text-warning"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="ov-stat-label">Chờ xác nhận</div>
                    <div class="ov-stat-value"><?= (int) $summary['pending_orders'] ?></div>
                    <div class="ov-stat-sub">Tất cả thời gian · bấm để xử lý</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <div class="ov-card ov-stat">
            <div class="ov-stat-icon bg-info-subtle text-info"><i class="fas fa-user-plus"></i></div>
            <div>
                <div class="ov-stat-label">Khách hàng mới</div>
                <div class="ov-stat-value"><?= (int) $summary['new_customers'] ?></div>
                <div class="ov-stat-sub">Đăng ký trong khoảng ngày</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Đơn hàng mới nhất -->
    <div class="col-12">
        <div class="ov-card">
            <div class="ov-card-header">
                <h6><i class="fas fa-truck-fast text-primary me-2"></i>Đơn hàng mới nhất</h6>
                <a href="index.php?controller=order&action=listOrder" class="small text-decoration-none">Xem tất cả →</a>
            </div>
            <?php if (empty($latestOrders)): ?>
                <div class="ov-empty">Không có đơn hàng nào trong khoảng ngày này.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table ov-table table-hover">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Ngày đặt</th>
                                <th class="ov-num">Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($latestOrders as $o): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($o['order_code']) ?></td>
                                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                                    <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($o['order_date'])) ?></td>
                                    <td class="ov-num"><?= ov_money($o['total_amount']) ?></td>
                                    <td><?= ov_status_badge($o['order_status']) ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary"
                                            href="index.php?controller=order&action=listOrderDetail&order_id=<?= (int) $o['order_id'] ?>">
                                            Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top sản phẩm bán chạy -->
    <div class="col-12 col-lg-6">
        <div class="ov-card h-100">
            <div class="ov-card-header">
                <h6><i class="fas fa-fire text-danger me-2"></i>Top sản phẩm bán chạy</h6>
                <span class="small text-muted">Không tính đơn đã hủy</span>
            </div>
            <?php if (empty($topProducts)): ?>
                <div class="ov-empty">Chưa bán được sản phẩm nào trong khoảng ngày này.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table ov-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sản phẩm</th>
                                <th class="ov-num">Đã bán</th>
                                <th class="ov-num">Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProducts as $i => $p): ?>
                                <tr>
                                    <td class="text-muted"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($p['image_url'])): ?>
                                                <img class="ov-thumb" src="<?= $uploadDir . htmlspecialchars($p['image_url']) ?>" alt="">
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($p['product_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="ov-num fw-semibold"><?= (int) $p['sold_quantity'] ?></td>
                                    <td class="ov-num"><?= ov_money($p['revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sản phẩm sắp hết hàng -->
    <div class="col-12 col-lg-6">
        <div class="ov-card h-100">
            <div class="ov-card-header">
                <h6><i class="fas fa-box-open text-warning me-2"></i>Sắp hết hàng</h6>
                <span class="small text-muted">Tồn kho ≤ <?= (int) $lowStockLimit ?> · hiện tại</span>
            </div>
            <?php if (empty($lowStock)): ?>
                <div class="ov-empty">Tất cả sản phẩm đều còn đủ hàng.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table ov-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Danh mục</th>
                                <th class="ov-num">Tồn kho</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStock as $p): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($p['image_url'])): ?>
                                                <img class="ov-thumb" src="<?= $uploadDir . htmlspecialchars($p['image_url']) ?>" alt="">
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($p['product_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                                    <td class="ov-num">
                                        <span class="badge <?= (int) $p['stock_quantity'] === 0 ? 'bg-danger' : 'bg-warning text-dark' ?>">
                                            <?= (int) $p['stock_quantity'] === 0 ? 'Hết hàng' : (int) $p['stock_quantity'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-secondary"
                                            href="index.php?controller=product&action=updateProduct&product_id=<?= (int) $p['product_id'] ?>">
                                            Nhập thêm
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
