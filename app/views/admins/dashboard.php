<?php
// ================================================================
// LAYOUT ADMIN — Góc Nhỏ Có Hoa
// Controller gán $viewFile rồi include file này.
// ================================================================
$gnBrand = 'Góc Nhỏ Có Hoa';

// Thông báo thành công / lỗi hiện thành toast (assets/js/popup.js)
require_once __DIR__ . '/../../../helpers/message_helper.php';
MessageHelper::$useToast = true;
// Hàm hiển thị dùng chung cho các trang con (badge, tiền, phân trang...)
require_once __DIR__ . '/../../../helpers/admin_ui_helper.php';

// ---------- Chế độ popup: chỉ trả về nội dung form (không có sidebar / top bar) ----------
// admin.js gọi URL form kèm &modal=1 rồi đặt nội dung vào popup form.
$gnInModal = !empty($_GET['modal']);
if ($gnInModal) {
    if (isset($viewFile) && file_exists($viewFile)) {
        include $viewFile;
    }
    MessageHelper::logMessage();
    return;
}

$navCtrl   = $_GET['controller'] ?? 'dashboard';
$navAction = $_GET['action'] ?? 'index';
$navStatus = $_GET['status'] ?? null;

function gn_url($ctrl, $action = null, $status = null)
{
    $url = "index.php?controller=$ctrl";
    if ($action) $url .= "&action=$action";
    if ($status) $url .= "&status=$status";
    return $url;
}

// Số đơn chờ xác nhận -> hiện badge trên menu Đơn hàng
$gnPending = (int) pdo_connect()->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();

// Cấu trúc menu dùng chung cho sidebar (desktop) và bảng "Thêm" (mobile)
//  - 'ctrl'    : controller làm mục này sáng lên
//  - 'actions' : (tùy chọn) chỉ sáng khi đúng các action này — dùng khi 2 mục chung 1 controller
//  - item con  : [nhãn, icon, controller, action, status, badge?]
$gnPromoCodeActions = ['listPromotion', 'addPromotion', 'updatePromotion', 'deletePromotion'];
$gnPromoProductActions = [
    'listAllProductPromotion', 'listApplyProductPromotion', 'listInactiveProductPromotion',
    'listExpiredProductPromotion', 'applyProductPromotion', 'updateProductPromotion', 'removeProductPromotion',
];

$gnMenu = [
    ['type' => 'link', 'label' => 'Tổng quan', 'icon' => 'fa-chart-pie', 'ctrl' => ['dashboard'], 'href' => gn_url('dashboard')],

    ['type' => 'section', 'label' => 'Cửa hàng'],
    ['type' => 'link', 'label' => 'Sản phẩm', 'icon' => 'fa-cube', 'ctrl' => ['product'], 'href' => gn_url('product', 'listProduct')],
    ['type' => 'link', 'label' => 'Danh mục', 'icon' => 'fa-layer-group', 'ctrl' => ['category'], 'href' => gn_url('category', 'listCategory')],

    ['type' => 'section', 'label' => 'Bán hàng'],
    ['type' => 'group', 'label' => 'Đơn hàng', 'icon' => 'fa-receipt', 'ctrl' => ['order'], 'badge' => $gnPending, 'items' => [
        ['Tất cả', 'fa-list', 'order', 'listOrder', null],
        ['Chờ xác nhận', 'fa-clock', 'order', 'listOrder', 'pending', $gnPending],
        ['Đã xác nhận', 'fa-circle-check', 'order', 'listOrder', 'confirmed'],
        ['Đang giao', 'fa-truck-fast', 'order', 'listOrder', 'shipping'],
        ['Hoàn thành', 'fa-flag-checkered', 'order', 'listOrder', 'completed'],
        ['Đã hủy', 'fa-ban', 'order', 'listOrder', 'cancelled'],
    ]],

    ['type' => 'section', 'label' => 'Khuyến mãi'],
    ['type' => 'link', 'label' => 'Mã khuyến mãi', 'icon' => 'fa-ticket', 'ctrl' => ['promotion'], 'actions' => $gnPromoCodeActions, 'href' => gn_url('promotion', 'listPromotion')],
    ['type' => 'group', 'label' => 'Khuyến mãi sản phẩm', 'icon' => 'fa-tags', 'ctrl' => ['promotion'], 'actions' => $gnPromoProductActions, 'items' => [
        ['Tất cả', 'fa-list', 'promotion', 'listAllProductPromotion', null],
        ['Đang áp dụng', 'fa-fire', 'promotion', 'listApplyProductPromotion', null],
        ['Tạm ngưng', 'fa-circle-pause', 'promotion', 'listInactiveProductPromotion', null],
        ['Hết hạn', 'fa-hourglass-end', 'promotion', 'listExpiredProductPromotion', null],
    ]],

    ['type' => 'section', 'label' => 'Hệ thống'],
    ['type' => 'group', 'label' => 'Tài khoản', 'icon' => 'fa-user-shield', 'ctrl' => ['accountCustomer', 'accountAdmin'], 'items' => [
        ['Khách hàng', 'fa-users', 'accountCustomer', 'listAccountCustomer', null],
        ['Quản trị viên', 'fa-crown', 'accountAdmin', 'listAccountAdmin', null],
    ]],
    ['type' => 'link', 'label' => 'Cài đặt', 'icon' => 'fa-sliders', 'ctrl' => [], 'href' => '#', 'soon' => true],
];

// Thanh tab dưới đáy (mobile): 4 mục chính + "Thêm" — cùng thứ tự với sidebar
$gnTabs = [
    ['Tổng quan', 'fa-chart-pie', 'dashboard', gn_url('dashboard'), 0],
    ['Sản phẩm', 'fa-cube', 'product', gn_url('product', 'listProduct'), 0],
    ['Đơn hàng', 'fa-receipt', 'order', gn_url('order', 'listOrder'), $gnPending],
    ['Khuyến mãi', 'fa-tags', 'promotion', gn_url('promotion', 'listPromotion'), 0],
];
$gnTabCtrls = array_column($gnTabs, 2);

$gnIsCtrl = fn($ctrls) => in_array($navCtrl, (array) $ctrls, true);
// Mục menu cấp 1 có đang active không (xét thêm 'actions' nếu có)
$gnIsOn = fn($m) => !empty($m['ctrl']) && $gnIsCtrl($m['ctrl'])
    && (empty($m['actions']) || in_array($navAction, $m['actions'], true));
$gnIsItem = fn($it) => $navCtrl === $it[2] && $navAction === $it[3] && $navStatus === $it[4];
$gnBadge = fn($n) => $n > 0 ? '<span class="gn-badge" title="' . $n . ' đơn chờ xác nhận">' . ($n > 99 ? '99+' : $n) . '</span>' : '';

// Tên trang hiện tại (hiện trên top bar và tab trình duyệt)
$gnPageLabel = $navCtrl === 'profile' ? 'Hồ sơ của tôi' : 'Tổng quan';
foreach ($gnMenu as $m) {
    if ($gnIsOn($m)) {
        $gnPageLabel = $m['label'];
        foreach ($m['items'] ?? [] as $it) {
            if ($gnIsItem($it)) $gnPageLabel = $m['label'] . ' · ' . $it[0];
        }
        break;
    }
}

$gnAdmin     = $_SESSION['admin'] ?? [];
$gnAdminName = $gnAdmin['full_name'] ?? ($gnAdmin['username'] ?? 'Quản trị viên');
$gnAdminUser = $gnAdmin['username'] ?? '';
$gnInitial   = mb_strtoupper(mb_substr(trim($gnAdminName), 0, 1, 'UTF-8'), 'UTF-8');
$gnLogout    = '../../../index.php?router=logout';
// Hộp xác nhận trước khi đăng xuất (popup.js) — dùng cho mọi nút Đăng xuất trong trang quản trị
$gnLogoutConfirm = 'data-confirm="Bạn sẽ cần đăng nhập lại để vào trang quản trị." data-confirm-title="Đăng xuất?"'
    . ' data-confirm-variant="warning" data-confirm-ok="Đăng xuất" data-confirm-cancel="Ở lại"';

// ---------- Nút quay lại: về trang cha của trang hiện tại ----------
// "controller.action" => [controller cha, action cha, tên trang cha]; trang không có trong bảng -> về Tổng quan
$gnBackMap = [
    'product.addProduct'               => ['product', 'listProduct', 'Sản phẩm'],
    'product.updateProduct'            => ['product', 'listProduct', 'Sản phẩm'],
    'category.addCategory'             => ['category', 'listCategory', 'Danh mục'],
    'category.updateCategory'          => ['category', 'listCategory', 'Danh mục'],
    'order.listOrderDetail'            => ['order', 'listOrder', 'Đơn hàng'],
    'promotion.addPromotion'           => ['promotion', 'listPromotion', 'Mã khuyến mãi'],
    'promotion.updatePromotion'        => ['promotion', 'listPromotion', 'Mã khuyến mãi'],
    'promotion.applyProductPromotion'  => ['promotion', 'listAllProductPromotion', 'Khuyến mãi sản phẩm'],
    'promotion.updateProductPromotion' => ['promotion', 'listAllProductPromotion', 'Khuyến mãi sản phẩm'],
    'accountAdmin.addAccountAdmin'     => ['accountAdmin', 'listAccountAdmin', 'Quản trị viên'],
];
// ---------- Form thêm / sửa mở dạng popup ----------
// "controller.action" => cỡ popup ('' | 'lg' | 'xl'). Các trang này khi mở thẳng (GET)
// sẽ chuyển về danh sách cha rồi tự bật popup (&open=...).
$gnFormModals = [
    'product.addProduct'               => 'xl',
    'product.updateProduct'            => 'xl',
    'category.addCategory'             => '',
    'category.updateCategory'          => '',
    'promotion.addPromotion'           => 'lg',
    'promotion.updatePromotion'        => 'lg',
    'promotion.applyProductPromotion'  => 'lg',
    'promotion.updateProductPromotion' => 'lg',
    'accountAdmin.addAccountAdmin'     => 'lg',
];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($gnFormModals["$navCtrl.$navAction"])) {
    [$pCtrl, $pAction] = $gnBackMap["$navCtrl.$navAction"];
    $openUrl = 'index.php?' . http_build_query($_GET);
    header('Location: ' . gn_url($pCtrl, $pAction) . '&open=' . rawurlencode($openUrl));
    exit;
}

$gnBack = null; // [url, tên trang cha]
if ($navCtrl !== 'dashboard') {
    [$bCtrl, $bAction, $bLabel] = $gnBackMap["$navCtrl.$navAction"] ?? ['dashboard', null, 'Tổng quan'];
    $bUrl = gn_url($bCtrl, $bAction);

    // Vừa đi từ chính trang cha (vd: danh sách đang lọc / ở trang 2) -> quay về đúng trạng thái đó
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if ($ref && parse_url($ref, PHP_URL_PATH) === parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) {
        parse_str((string) parse_url($ref, PHP_URL_QUERY), $refQuery);
        if (($refQuery['controller'] ?? 'dashboard') === $bCtrl && ($refQuery['action'] ?? null) === $bAction) {
            $bUrl = 'index.php?' . http_build_query($refQuery);
        }
    }
    $gnBack = [$bUrl, $bLabel];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($gnPageLabel) ?> | Quản trị <?= $gnBrand ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Giao diện dùng chung cho các trang con -->
    <link rel="stylesheet" href="../../../assets/css/admins/admin.css">
    <!-- Popup dùng chung: hộp xác nhận (data-confirm) + toast thông báo -->
    <script src="../../../assets/js/popup.js"></script>
    <!-- Kiểm tra form, chọn ảnh, lọc danh sách, popup form (data-validate / data-upload / data-filter) -->
    <script>
        window.GNFormModals = <?= json_encode($gnFormModals) ?>;
    </script>
    <script src="../../../assets/js/admin.js" defer></script>

    <!-- Hiệu ứng chuyển trang + thanh tiến trình (phải chạy trước lần vẽ đầu tiên) -->
    <script>
        (function() {
            var root = document.documentElement;
            // Trình duyệt chưa hỗ trợ View Transitions giữa các trang -> dùng hiệu ứng hiện dần dự phòng
            if (!('onpagereveal' in window)) root.classList.add('gn-anim-fallback');

            // Chiều chuyển trang: bấm "Quay lại" hoặc Back/Forward của trình duyệt -> trượt ngược
            window.addEventListener('pagereveal', function(e) {
                if (!e.viewTransition) return;
                var dir = null;
                try {
                    dir = sessionStorage.getItem('gn-nav-dir');
                    sessionStorage.removeItem('gn-nav-dir');
                } catch (err) {}
                var nav = window.navigation && navigation.activation;
                if (dir === 'back' || (nav && nav.navigationType === 'traverse')) {
                    e.viewTransition.types.add('back');
                }
            });
            document.addEventListener('click', function(e) {
                if (e.target.closest && e.target.closest('.gn-back-btn')) {
                    try { sessionStorage.setItem('gn-nav-dir', 'back'); } catch (err) {}
                }
            }, true);

            // ---------- Thanh tiến trình khi đang chuyển trang ----------
            function startProgress() {
                var bar = document.getElementById('gnProgress');
                if (bar) bar.classList.add('is-loading');
            }
            function stopProgress() {
                var bar = document.getElementById('gnProgress');
                if (bar) bar.classList.remove('is-loading');
            }
            if (window.navigation) {
                // Navigation API: bắt mọi kiểu chuyển trang (link, form, location.href sau khi xác nhận...)
                navigation.addEventListener('navigate', function(e) {
                    if (e.destination.sameDocument || e.downloadRequest !== null && e.downloadRequest !== undefined) return;
                    startProgress();
                });
                // Chuyển trang bị hủy / lỗi mạng -> tắt thanh tiến trình
                navigation.addEventListener('navigateerror', stopProgress);
            } else {
                // Dự phòng: link thường và form (bỏ qua khi bị chặn, vd: mở popup form)
                document.addEventListener('click', function(e) {
                    var a = e.target.closest && e.target.closest('a[href]');
                    if (!a || a.target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
                    var href = a.getAttribute('href');
                    if (!href || href.charAt(0) === '#' || /^(mailto|tel|javascript):/i.test(href)) return;
                    setTimeout(function() { if (!e.defaultPrevented) startProgress(); }, 0);
                });
                document.addEventListener('submit', function(e) {
                    setTimeout(function() { if (!e.defaultPrevented) startProgress(); }, 0);
                });
            }
            // Quay lại bằng bộ nhớ đệm (bfcache) -> tắt thanh tiến trình còn treo
            window.addEventListener('pageshow', stopProgress);
        })();
    </script>

    <style>
        :root {
            --gn-side-w: 264px;
            --gn-rail-w: 76px;
            --gn-top-h: 64px;
            --gn-tab-h: 64px;

            --gn-accent: #e11d48;
            --gn-accent-strong: #be123c;
            --gn-accent-soft: #fff1f2;
            --gn-accent-ring: #fecdd3;

            --gn-bg: #f6f4f5;
            --gn-surface: #ffffff;
            --gn-border: #eee6e9;
            --gn-text: #1f2937;
            --gn-text-2: #475569;
            --gn-muted: #94a3b8;
        }

        /* html body: thắng reboot của các trang con có nạp lại bootstrap */
        html body.gn-admin {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--gn-bg);
            color: var(--gn-text);
            overflow-x: hidden;
        }

        /* ================= SIDEBAR ================= */
        .gn-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: var(--gn-side-w);
            background: var(--gn-surface);
            border-right: 1px solid var(--gn-border);
            display: flex;
            flex-direction: column;
            z-index: 1040;
            transition: width .25s ease;
        }

        .gn-brand {
            height: var(--gn-top-h);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 18px;
            border-bottom: 1px solid var(--gn-border);
            text-decoration: none;
            flex-shrink: 0;
            overflow: hidden;
        }

        .gn-brand-mark {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(135deg, #fb7185, var(--gn-accent));
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 1.05rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(225, 29, 72, .25);
        }

        .gn-brand-text {
            line-height: 1.15;
            white-space: nowrap;
        }

        .gn-brand-name {
            font-weight: 800;
            font-size: 1rem;
            color: var(--gn-text);
            letter-spacing: -.01em;
        }

        .gn-brand-sub {
            font-size: .72rem;
            color: var(--gn-muted);
            font-weight: 500;
        }

        .gn-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 12px 12px 16px;
            scrollbar-width: thin;
        }

        .gn-section {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--gn-muted);
            padding: 18px 12px 6px;
            white-space: nowrap;
        }

        .gn-link {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 10px 12px;
            margin: 2px 0;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: var(--gn-text-2);
            font-size: .9rem;
            font-weight: 500;
            text-decoration: none;
            text-align: left;
            white-space: nowrap;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }

        .gn-link>i:first-child {
            width: 20px;
            text-align: center;
            font-size: 1rem;
            color: var(--gn-muted);
            flex-shrink: 0;
            transition: color .15s ease;
        }

        .gn-link:hover {
            background: #f8f5f6;
            color: var(--gn-text);
        }

        .gn-link:hover>i:first-child {
            color: var(--gn-text-2);
        }

        .gn-link:focus-visible {
            outline: 2px solid var(--gn-accent-ring);
            outline-offset: 1px;
        }

        .gn-link.active {
            background: var(--gn-accent-soft);
            color: var(--gn-accent-strong);
            font-weight: 600;
        }

        .gn-link.active>i:first-child {
            color: var(--gn-accent);
        }

        .gn-label {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gn-caret {
            font-size: .7rem;
            color: var(--gn-muted);
            transition: transform .2s ease;
        }

        .gn-group.open>.gn-link .gn-caret {
            transform: rotate(180deg);
        }

        .gn-soon {
            font-size: .62rem;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 999px;
            background: #f1f5f9;
            color: var(--gn-muted);
        }

        .gn-link.is-soon {
            cursor: default;
            opacity: .75;
        }

        /* Badge số đơn chờ xác nhận */
        .gn-badge {
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            background: var(--gn-accent);
            color: #fff;
            font-size: .7rem;
            font-weight: 700;
            line-height: 20px;
            text-align: center;
            font-variant-numeric: tabular-nums;
            flex-shrink: 0;
        }

        body.gn-collapsed .gn-group>.gn-link {
            position: relative;
        }

        body.gn-collapsed .gn-group>.gn-link .gn-badge {
            position: absolute;
            top: 4px;
            right: 12px;
            min-width: 16px;
            height: 16px;
            line-height: 16px;
            padding: 0 4px;
            font-size: .6rem;
            box-shadow: 0 0 0 2px var(--gn-surface);
        }

        /* Menu con (sidebar mở rộng) */
        .gn-sub {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows .25s ease;
        }

        .gn-sub-inner {
            overflow: hidden;
            margin-left: 21px;
            padding-left: 12px;
            border-left: 1px solid var(--gn-border);
        }

        .gn-group.open>.gn-sub {
            grid-template-rows: 1fr;
        }

        .gn-sub .gn-link {
            padding: 8px 10px;
            font-size: .85rem;
        }

        .gn-sub .gn-link>i:first-child {
            font-size: .85rem;
        }

        .gn-flyout-title {
            display: none;
        }

        /* Thẻ admin cuối sidebar */
        .gn-user {
            border-top: 1px solid var(--gn-border);
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            overflow: hidden;
        }

        .gn-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--gn-accent-soft);
            color: var(--gn-accent-strong);
            font-weight: 700;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            text-decoration: none;
        }

        .gn-user-info {
            flex: 1;
            min-width: 0;
            line-height: 1.2;
        }

        .gn-user-name {
            font-weight: 600;
            font-size: .85rem;
            color: var(--gn-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gn-user-role {
            font-size: .72rem;
            color: var(--gn-muted);
        }

        .gn-icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1px solid var(--gn-border);
            background: var(--gn-surface);
            color: var(--gn-text-2);
            display: grid;
            place-items: center;
            flex-shrink: 0;
            text-decoration: none;
            transition: all .15s ease;
        }

        .gn-icon-btn:hover {
            border-color: var(--gn-accent-ring);
            color: var(--gn-accent);
            background: var(--gn-accent-soft);
        }

        /* ================= SIDEBAR THU GỌN (rail) ================= */
        body.gn-collapsed .gn-sidebar {
            width: var(--gn-rail-w);
        }

        body.gn-collapsed .gn-brand {
            padding: 0;
            justify-content: center;
        }

        body.gn-collapsed .gn-brand-text,
        body.gn-collapsed .gn-label,
        body.gn-collapsed .gn-caret,
        body.gn-collapsed .gn-soon,
        body.gn-collapsed .gn-user-info,
        body.gn-collapsed .gn-user .gn-icon-btn {
            display: none;
        }

        body.gn-collapsed .gn-section {
            font-size: 0;
            padding: 10px 0;
        }

        body.gn-collapsed .gn-section::after {
            content: "";
            display: block;
            height: 1px;
            background: var(--gn-border);
            margin: 0 14px;
        }

        body.gn-collapsed .gn-nav>.gn-link,
        body.gn-collapsed .gn-group>.gn-link {
            justify-content: center;
            padding: 12px 0;
        }

        body.gn-collapsed .gn-nav>.gn-link>i:first-child,
        body.gn-collapsed .gn-group>.gn-link>i:first-child {
            font-size: 1.1rem;
        }

        body.gn-collapsed .gn-user {
            justify-content: center;
        }

        /* Menu con dạng flyout khi thu gọn */
        body.gn-collapsed .gn-sub {
            position: fixed;
            left: calc(var(--gn-rail-w) + 8px);
            top: 0;
            min-width: 230px;
            display: block;
            background: var(--gn-surface);
            border: 1px solid var(--gn-border);
            border-radius: 14px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, .14);
            padding: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateX(-6px);
            transition: opacity .15s ease, transform .15s ease, visibility .15s;
            z-index: 1060;
        }

        body.gn-collapsed .gn-group.flyout>.gn-sub {
            opacity: 1;
            visibility: visible;
            transform: none;
        }

        body.gn-collapsed .gn-sub-inner {
            margin: 0;
            padding: 0;
            border: 0;
            overflow: visible;
        }

        body.gn-collapsed .gn-sub .gn-label {
            display: inline;
        }

        body.gn-collapsed .gn-flyout-title {
            display: block;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--gn-muted);
            padding: 6px 10px 4px;
        }

        /* ================= TOP BAR ================= */
        .gn-topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--gn-side-w);
            height: var(--gn-top-h);
            background: rgba(255, 255, 255, .92);
            border-bottom: 1px solid var(--gn-border);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 24px;
            z-index: 1030;
            transition: left .25s ease;
        }

        @supports (backdrop-filter: blur(8px)) {
            .gn-topbar {
                background: rgba(255, 255, 255, .8);
                backdrop-filter: blur(10px);
            }
        }

        body.gn-collapsed .gn-topbar {
            left: var(--gn-rail-w);
        }

        .gn-page-title {
            font-weight: 700;
            font-size: 1.05rem;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }

        .gn-top-brand {
            display: none;
        }

        /* Nút quay lại trên top bar */
        .gn-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 36px;
            padding: 0 14px 0 12px;
            border-radius: 10px;
            border: 1px solid var(--gn-border);
            background: var(--gn-surface);
            color: var(--gn-text-2);
            font-size: .85rem;
            font-weight: 600;
            text-decoration: none;
            flex-shrink: 0;
            transition: all .15s ease;
        }

        .gn-back-btn:hover,
        .gn-back-btn:focus-visible {
            border-color: var(--gn-accent-ring);
            background: var(--gn-accent-soft);
            color: var(--gn-accent-strong);
        }

        .gn-back-btn i {
            transition: transform .15s ease;
        }

        .gn-back-btn:hover i {
            transform: translateX(-2px);
        }

        .gn-top-user {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--gn-text);
            border: 0;
            background: transparent;
            padding: 4px;
            border-radius: 999px;
        }

        .gn-top-user:hover {
            background: #f8f5f6;
        }

        .gn-top-user .gn-top-name {
            font-weight: 600;
            font-size: .875rem;
        }

        /* ================= NỘI DUNG ================= */
        .gn-main {
            margin-left: var(--gn-side-w);
            padding: calc(var(--gn-top-h) + 24px) 28px 40px;
            min-height: 100vh;
            transition: margin-left .25s ease;
        }

        body.gn-collapsed .gn-main {
            margin-left: var(--gn-rail-w);
        }

        /* ================= TAB DƯỚI ĐÁY (mobile) ================= */
        .gn-tabbar {
            display: none;
        }

        .gn-sheet .gn-sheet-group {
            margin-bottom: 18px;
        }

        .gn-sheet-title {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--gn-muted);
            margin: 0 4px 8px;
        }

        .gn-sheet-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .gn-sheet-grid .gn-link {
            margin: 0;
            background: #faf8f9;
            border: 1px solid var(--gn-border);
            padding: 11px 12px;
            font-size: .85rem;
            white-space: normal;
            line-height: 1.25;
        }

        .gn-sheet-grid .gn-link.active {
            background: var(--gn-accent-soft);
            border-color: var(--gn-accent-ring);
        }

        /* Tablet: sidebar mặc định thu gọn (xử lý trong JS), nội dung hẹp lại */
        @media (max-width: 991.98px) {
            .gn-main {
                padding-left: 20px;
                padding-right: 20px;
            }
        }

        /* Mobile: ẩn sidebar, dùng tab dưới đáy */
        @media (max-width: 767.98px) {

            .gn-sidebar,
            .gn-desktop-only {
                display: none !important;
            }

            .gn-topbar,
            body.gn-collapsed .gn-topbar {
                left: 0;
                padding: 0 16px;
                padding-top: env(safe-area-inset-top);
                height: calc(var(--gn-top-h) + env(safe-area-inset-top));
            }

            .gn-top-brand {
                display: grid;
            }

            /* Mobile: nút quay lại thay chỗ logo, chỉ còn icon */
            .gn-topbar.has-back .gn-top-brand {
                display: none;
            }

            .gn-back-btn {
                width: 36px;
                padding: 0;
                justify-content: center;
            }

            .gn-back-btn span {
                display: none;
            }

            .gn-top-brand .gn-brand-mark {
                width: 34px;
                height: 34px;
                font-size: .95rem;
            }

            .gn-page-title {
                font-size: 1rem;
            }

            .gn-top-user .gn-top-name {
                display: none;
            }

            .gn-main,
            body.gn-collapsed .gn-main {
                margin-left: 0;
                padding: calc(var(--gn-top-h) + env(safe-area-inset-top) + 16px) 16px calc(var(--gn-tab-h) + env(safe-area-inset-bottom) + 24px);
            }

            .gn-tabbar {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                height: calc(var(--gn-tab-h) + env(safe-area-inset-bottom));
                padding-bottom: env(safe-area-inset-bottom);
                background: var(--gn-surface);
                border-top: 1px solid var(--gn-border);
                box-shadow: 0 -6px 20px rgba(15, 23, 42, .06);
                z-index: 1035;
            }

            .gn-tab {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 4px;
                border: 0;
                background: transparent;
                color: var(--gn-muted);
                font-size: .68rem;
                font-weight: 600;
                text-decoration: none;
                position: relative;
                -webkit-tap-highlight-color: transparent;
            }

            .gn-tab i {
                font-size: 1.15rem;
            }

            .gn-tab-icon {
                position: relative;
                line-height: 1;
            }

            .gn-tab-icon .gn-badge {
                position: absolute;
                top: -7px;
                left: 12px;
                min-width: 17px;
                height: 17px;
                line-height: 17px;
                padding: 0 4px;
                font-size: .6rem;
                box-shadow: 0 0 0 2px var(--gn-surface);
            }

            .gn-tab.active {
                color: var(--gn-accent);
            }

            .gn-tab.active::before {
                content: "";
                position: absolute;
                top: 0;
                width: 28px;
                height: 3px;
                border-radius: 0 0 4px 4px;
                background: var(--gn-accent);
            }

            .gn-sheet.offcanvas-bottom {
                height: auto;
                max-height: 82vh;
                border-radius: 20px 20px 0 0;
                padding-bottom: env(safe-area-inset-bottom);
            }

            .gn-sheet-handle {
                width: 40px;
                height: 4px;
                border-radius: 4px;
                background: #e2e8f0;
                margin: 10px auto 0;
            }
        }

        @media (min-width: 768px) {
            .gn-mobile-only {
                display: none !important;
            }
        }

        /* ================= HIỆU ỨNG CHUYỂN TRANG ================= */
        /* View Transitions giữa các trang (Chrome/Edge 126+, Safari 18.2+) */
        @view-transition {
            navigation: auto;
        }

        /* Sidebar, top bar, tab đáy đứng yên — chỉ phần nội dung chuyển động */
        .gn-sidebar {
            view-transition-name: gn-sidebar;
        }

        .gn-topbar {
            view-transition-name: gn-topbar;
        }

        .gn-tabbar {
            view-transition-name: gn-tabbar;
        }

        .gn-main {
            view-transition-name: gn-main;
        }

        ::view-transition-group(gn-sidebar),
        ::view-transition-group(gn-tabbar),
        ::view-transition-group(gn-main),
        ::view-transition-old(gn-sidebar),
        ::view-transition-new(gn-sidebar),
        ::view-transition-old(gn-tabbar),
        ::view-transition-new(gn-tabbar) {
            animation: none;
        }

        ::view-transition-old(gn-sidebar),
        ::view-transition-old(gn-tabbar) {
            display: none;
        }

        /* Top bar: chỉ tên trang đổi -> mờ chéo thật nhanh */
        ::view-transition-group(gn-topbar) {
            animation-duration: 160ms;
        }

        /* Nội dung: trang cũ trượt sang trái + mờ đi, trang mới trượt vào từ phải */
        ::view-transition-old(gn-main) {
            animation: 160ms cubic-bezier(.4, 0, 1, 1) both gn-page-out;
        }

        ::view-transition-new(gn-main) {
            animation: 280ms cubic-bezier(0, 0, .2, 1) 60ms both gn-page-in;
        }

        /* Quay lại: đảo chiều */
        html:active-view-transition-type(back)::view-transition-old(gn-main) {
            animation-name: gn-page-out-back;
        }

        html:active-view-transition-type(back)::view-transition-new(gn-main) {
            animation-name: gn-page-in-back;
        }

        @keyframes gn-page-out {
            to {
                opacity: 0;
                transform: translateX(-24px);
            }
        }

        @keyframes gn-page-in {
            from {
                opacity: 0;
                transform: translateX(24px);
            }
        }

        @keyframes gn-page-out-back {
            to {
                opacity: 0;
                transform: translateX(24px);
            }
        }

        @keyframes gn-page-in-back {
            from {
                opacity: 0;
                transform: translateX(-24px);
            }
        }

        /* Dự phòng (Firefox...): nội dung trang mới hiện dần lên */
        .gn-anim-fallback .gn-main {
            animation: gn-page-rise 320ms cubic-bezier(0, 0, .2, 1) both;
        }

        @keyframes gn-page-rise {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
        }

        /* Thanh tiến trình khi đang chuyển trang */
        .gn-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0;
            z-index: 2200;
            background: linear-gradient(90deg, #fb7185, var(--gn-accent));
            box-shadow: 0 0 8px rgba(225, 29, 72, .45);
            border-radius: 0 3px 3px 0;
            opacity: 0;
            pointer-events: none;
        }

        .gn-progress.is-loading {
            opacity: 1;
            animation: gn-progress-grow 8s cubic-bezier(.08, .8, .2, 1) forwards;
        }

        @keyframes gn-progress-grow {
            0% {
                width: 0;
            }

            10% {
                width: 35%;
            }

            100% {
                width: 92%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
            }

            ::view-transition-group(*),
            ::view-transition-old(*),
            ::view-transition-new(*) {
                animation: none !important;
            }

            .gn-anim-fallback .gn-main {
                animation: none;
            }

            .gn-progress.is-loading {
                animation: none;
                width: 60%;
            }
        }
    </style>
</head>

<body class="gn-admin">
    <div class="gn-progress" id="gnProgress" role="progressbar" aria-hidden="true"></div>
    <script>
        // Áp trạng thái thu gọn trước khi vẽ để tránh nháy layout
        (function() {
            var saved = null;
            try {
                saved = localStorage.getItem('gnSidebarCollapsed');
            } catch (e) {}
            var w = window.innerWidth;
            var collapsed = w >= 768 && (saved === null ? w < 992 : saved === '1');
            if (collapsed) document.body.classList.add('gn-collapsed');
        })();
    </script>

    <!-- ================= SIDEBAR (tablet / desktop) ================= -->
    <aside class="gn-sidebar" id="gnSidebar" aria-label="Menu quản trị">
        <a class="gn-brand" href="<?= gn_url('dashboard') ?>" title="<?= $gnBrand ?>">
            <span class="gn-brand-mark"><i class="fas fa-spa"></i></span>
            <span class="gn-brand-text">
                <span class="gn-brand-name d-block"><?= $gnBrand ?></span>
                <span class="gn-brand-sub">Trang quản trị</span>
            </span>
        </a>

        <nav class="gn-nav" id="gnNav">
            <?php foreach ($gnMenu as $gi => $m): ?>
                <?php if ($m['type'] === 'section'): ?>
                    <div class="gn-section"><?= $m['label'] ?></div>

                <?php elseif ($m['type'] === 'link'): ?>
                    <?php $soon = !empty($m['soon']); $on = $gnIsOn($m); ?>
                    <a class="gn-link<?= $on ? ' active' : '' ?><?= $soon ? ' is-soon' : '' ?>"
                        href="<?= $m['href'] ?>" title="<?= $m['label'] ?>"
                        <?= $soon ? 'aria-disabled="true" onclick="return false"' : '' ?>
                        <?= $on ? 'aria-current="page"' : '' ?>>
                        <i class="fas <?= $m['icon'] ?>"></i>
                        <span class="gn-label"><?= $m['label'] ?></span>
                        <?php if ($soon): ?><span class="gn-soon">Sắp có</span><?php endif; ?>
                    </a>

                <?php else: ?>
                    <?php $groupOn = $gnIsOn($m); ?>
                    <div class="gn-group<?= $groupOn ? ' open' : '' ?>">
                        <button type="button" class="gn-link gn-group-toggle<?= $groupOn ? ' active' : '' ?>"
                            title="<?= $m['label'] ?>" aria-expanded="<?= $groupOn ? 'true' : 'false' ?>"
                            aria-controls="gnSub<?= $gi ?>">
                            <i class="fas <?= $m['icon'] ?>"></i>
                            <span class="gn-label"><?= $m['label'] ?></span>
                            <?= $gnBadge($m['badge'] ?? 0) ?>
                            <i class="fas fa-chevron-down gn-caret"></i>
                        </button>
                        <div class="gn-sub" id="gnSub<?= $gi ?>">
                            <div class="gn-sub-inner">
                                <div class="gn-flyout-title"><?= $m['label'] ?></div>
                                <?php foreach ($m['items'] as $it): ?>
                                    <a class="gn-link<?= $gnIsItem($it) ? ' active' : '' ?>"
                                        href="<?= gn_url($it[2], $it[3], $it[4]) ?>"
                                        <?= $gnIsItem($it) ? 'aria-current="page"' : '' ?>>
                                        <i class="fas <?= $it[1] ?>"></i>
                                        <span class="gn-label"><?= $it[0] ?></span>
                                        <?= $gnBadge($it[5] ?? 0) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <div class="gn-user">
            <a class="gn-avatar" href="index.php?controller=profile" title="Hồ sơ của tôi"><?= htmlspecialchars($gnInitial) ?></a>
            <div class="gn-user-info">
                <div class="gn-user-name"><?= htmlspecialchars($gnAdminName) ?></div>
                <div class="gn-user-role">@<?= htmlspecialchars($gnAdminUser) ?></div>
            </div>
            <a class="gn-icon-btn" href="<?= $gnLogout ?>" title="Đăng xuất" aria-label="Đăng xuất" <?= $gnLogoutConfirm ?>><i class="fas fa-arrow-right-from-bracket"></i></a>
        </div>
    </aside>

    <!-- ================= TOP BAR ================= -->
    <header class="gn-topbar<?= $gnBack ? ' has-back' : '' ?>">
        <button class="gn-icon-btn gn-desktop-only" type="button" id="gnToggle" title="Thu gọn / mở rộng menu"
            aria-controls="gnSidebar">
            <i class="fas fa-bars"></i>
        </button>
        <a class="gn-top-brand" href="<?= gn_url('dashboard') ?>" aria-label="<?= $gnBrand ?>">
            <span class="gn-brand-mark"><i class="fas fa-spa"></i></span>
        </a>
        <?php if ($gnBack): ?>
            <a class="gn-back-btn" href="<?= htmlspecialchars($gnBack[0]) ?>"
                title="Quay lại <?= $gnBack[1] ?>" aria-label="Quay lại <?= $gnBack[1] ?>">
                <i class="fas fa-arrow-left"></i><span>Quay lại</span>
            </a>
        <?php endif; ?>
        <h1 class="gn-page-title"><?= htmlspecialchars($gnPageLabel) ?></h1>

        <div class="dropdown ms-auto">
            <button class="gn-top-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="gn-avatar"><?= htmlspecialchars($gnInitial) ?></span>
                <span class="gn-top-name"><?= htmlspecialchars($gnAdminName) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2 p-2">
                <li class="px-3 py-2 small text-muted">Đăng nhập: <strong>@<?= htmlspecialchars($gnAdminUser) ?></strong></li>
                <li><a class="dropdown-item rounded-3" href="index.php?controller=profile"><i class="fas fa-id-card me-2"></i>Hồ sơ</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger rounded-3" href="<?= $gnLogout ?>" <?= $gnLogoutConfirm ?>><i class="fas fa-arrow-right-from-bracket me-2"></i>Đăng xuất</a></li>
            </ul>
        </div>
    </header>

    <!-- ================= NỘI DUNG TRANG ================= -->
    <main class="gn-main" id="mainContent">
        <?php
        if (isset($viewFile) && file_exists($viewFile)) {
            include $viewFile;
        }
        // Hiện nốt thông báo nếu trang con không tự gọi logMessage()
        MessageHelper::logMessage();
        ?>
    </main>

    <!-- ================= TAB DƯỚI ĐÁY (mobile) ================= -->
    <nav class="gn-tabbar gn-mobile-only" aria-label="Menu chính">
        <?php foreach ($gnTabs as $t): ?>
            <a class="gn-tab<?= $navCtrl === $t[2] ? ' active' : '' ?>" href="<?= $t[3] ?>"
                <?= $navCtrl === $t[2] ? 'aria-current="page"' : '' ?>>
                <span class="gn-tab-icon"><i class="fas <?= $t[1] ?>"></i><?= $gnBadge($t[4]) ?></span><span><?= $t[0] ?></span>
            </a>
        <?php endforeach; ?>
        <button class="gn-tab<?= in_array($navCtrl, $gnTabCtrls, true) ? '' : ' active' ?>" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#gnSheet" aria-controls="gnSheet">
            <i class="fas fa-grip"></i><span>Thêm</span>
        </button>
    </nav>

    <!-- Bảng "Thêm" trượt từ dưới lên -->
    <div class="offcanvas offcanvas-bottom gn-sheet gn-mobile-only" tabindex="-1" id="gnSheet" aria-labelledby="gnSheetLabel">
        <div class="gn-sheet-handle"></div>
        <div class="offcanvas-header pb-2">
            <h5 class="offcanvas-title fw-bold" id="gnSheetLabel">Tất cả chức năng</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
        </div>
        <div class="offcanvas-body pt-0">
            <?php
            // Gom menu thành các nhóm cho bảng mobile
            $sheet = [];
            $current = null;
            foreach ($gnMenu as $m) {
                if ($m['type'] === 'section') {
                    $current = $m['label'];
                } elseif ($m['type'] === 'group') {
                    $sheet[$m['label']] = array_map(fn($it) => [
                        'label' => $it[0], 'icon' => $it[1], 'href' => gn_url($it[2], $it[3], $it[4]),
                        'active' => $gnIsItem($it), 'soon' => false, 'badge' => $it[5] ?? 0,
                    ], $m['items']);
                } elseif ($current) {
                    $sheet[$current][] = [
                        'label' => $m['label'], 'icon' => $m['icon'], 'href' => $m['href'],
                        'active' => $gnIsOn($m), 'soon' => !empty($m['soon']), 'badge' => 0,
                    ];
                }
            }
            ?>
            <?php foreach ($sheet as $title => $links): ?>
                <div class="gn-sheet-group">
                    <div class="gn-sheet-title"><?= $title ?></div>
                    <div class="gn-sheet-grid">
                        <?php foreach ($links as $l): ?>
                            <a class="gn-link<?= $l['active'] ? ' active' : '' ?><?= $l['soon'] ? ' is-soon' : '' ?>"
                                href="<?= $l['href'] ?>" <?= $l['soon'] ? 'aria-disabled="true" onclick="return false"' : '' ?>>
                                <i class="fas <?= $l['icon'] ?>"></i>
                                <span class="gn-label"><?= $l['label'] ?></span>
                                <?= $gnBadge($l['badge']) ?>
                                <?php if ($l['soon']): ?><span class="gn-soon">Sắp có</span><?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="gn-sheet-group mb-2">
                <div class="gn-sheet-title">Tài khoản của tôi</div>
                <div class="gn-sheet-grid">
                    <a class="gn-link" href="index.php?controller=profile"><i class="fas fa-id-card"></i><span class="gn-label">Hồ sơ</span></a>
                    <a class="gn-link text-danger" href="<?= $gnLogout ?>" <?= $gnLogoutConfirm ?>><i class="fas fa-arrow-right-from-bracket text-danger"></i><span class="gn-label">Đăng xuất</span></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup form dùng chung cho mọi form thêm / sửa (nội dung nạp bằng admin.js) -->
    <div class="modal fade gn-form-modal" id="gnFormModal" tabindex="-1" aria-labelledby="gnFormModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="gn-form-modal-heading">
                        <h2 class="modal-title" id="gnFormModalTitle">Đang tải...</h2>
                        <p class="gn-form-modal-sub" id="gnFormModalSub"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body" id="gnFormModalBody"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const body = document.body;
            const nav = document.getElementById('gnNav');
            const toggle = document.getElementById('gnToggle');
            const isCollapsed = () => body.classList.contains('gn-collapsed');

            function closeFlyouts(except) {
                nav.querySelectorAll('.gn-group.flyout').forEach(g => {
                    if (g !== except) {
                        g.classList.remove('flyout');
                        g.querySelector('.gn-group-toggle').setAttribute('aria-expanded', 'false');
                    }
                });
            }

            // Thu gọn / mở rộng sidebar (desktop + tablet)
            if (toggle) {
                toggle.addEventListener('click', function() {
                    body.classList.toggle('gn-collapsed');
                    closeFlyouts();
                    try {
                        localStorage.setItem('gnSidebarCollapsed', isCollapsed() ? '1' : '0');
                    } catch (e) {}
                });
            }

            // Nhóm menu: mở rộng -> accordion; thu gọn -> flyout bên phải
            nav.querySelectorAll('.gn-group-toggle').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const group = btn.closest('.gn-group');

                    if (isCollapsed()) {
                        const willOpen = !group.classList.contains('flyout');
                        closeFlyouts(group);
                        group.classList.toggle('flyout', willOpen);
                        btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                        if (willOpen) {
                            const sub = group.querySelector('.gn-sub');
                            const r = btn.getBoundingClientRect();
                            const maxTop = window.innerHeight - sub.offsetHeight - 12;
                            sub.style.top = Math.max(12, Math.min(r.top - 8, maxTop)) + 'px';
                        }
                        return;
                    }

                    const open = group.classList.toggle('open');
                    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            });

            document.addEventListener('click', e => {
                if (!e.target.closest('.gn-group')) closeFlyouts();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeFlyouts();
            });
            // Chỉ đóng flyout khi thật sự cuộn dọc (đổi độ rộng sidebar cũng phát sự kiện scroll)
            let lastScrollTop = nav.scrollTop;
            nav.addEventListener('scroll', () => {
                if (nav.scrollTop !== lastScrollTop) {
                    lastScrollTop = nav.scrollTop;
                    closeFlyouts();
                }
            });
            window.addEventListener('resize', () => closeFlyouts());
        })();
    </script>
</body>

</html>
