<?php
// ================================================================
// LAYOUT CỬA HÀNG — Góc Nhỏ Có Hoa
// Controller gán $viewFile (+ $pageTitle) rồi include file này.
// ================================================================
require_once __DIR__ . '/../../../helpers/message_helper.php';
require_once __DIR__ . '/../../../helpers/image_helper.php'; // productImageUrl() cho các view
require_once __DIR__ . '/../../models/cart_model.php';
require_once __DIR__ . '/../../models/category_model.php';
MessageHelper::$useToast = true; // thông báo thành công / lỗi hiện dạng toast (popup.js)

$shBrand    = 'Góc Nhỏ Có Hoa';
$shCustomer = $_SESSION['customer'] ?? null;
$shKeyword  = trim((string) ($_GET['q'] ?? ''));

// Số sản phẩm trong giỏ (hiện badge)
$shCartCount = 0;
if ($shCustomer) {
    $shCartCount = (int) (new CartModel())->getCartCount($shCustomer['customer_id']);
}

// Mục đang active trên menu: home | cart | orders
$shCtrl   = $_GET['controller'] ?? 'index';
$shAction = $_GET['action'] ?? 'index';
$shActive = 'home';
if ($shCtrl === 'cart' || ($shCtrl === 'order' && in_array($shAction, ['index', 'placeOrder'], true))) {
    $shActive = 'cart';
} elseif ($shCtrl === 'order') {
    $shActive = 'orders';
}

// Tên gọi (chữ cuối của họ tên, vd: "Nguyễn Văn An" -> "An") + chữ cái đầu cho avatar
$shFirstName = $shCustomer ? trim(strrchr(' ' . trim($shCustomer['full_name']), ' ')) : '';
$shInitial = $shCustomer
    ? mb_strtoupper(mb_substr($shFirstName ?: $shCustomer['username'], 0, 1, 'UTF-8'), 'UTF-8')
    : '';

// Vài danh mục cho footer (link lọc thật)
$shFooterCats = array_slice((new CategoryModel())->getAllCategory(), 0, 4);

$shUrl = [
    'home'   => 'index.php?router=customers',
    'cart'   => 'index.php?router=customers&controller=cart&action=listCart',
    'orders' => 'index.php?router=customers&controller=order&action=listOrders',
    'login'  => 'index.php?router=login',
    'reg'    => 'index.php?router=register',
    'logout' => 'index.php?router=logout',
];
$shLogoutConfirm = 'data-confirm="Giỏ hàng vẫn được giữ, bạn đăng nhập lại để tiếp tục mua sắm." data-confirm-title="Đăng xuất?"'
    . ' data-confirm-variant="warning" data-confirm-ok="Đăng xuất" data-confirm-cancel="Ở lại"';
$shE = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= isset($pageTitle) && $pageTitle ? $shE($pageTitle) . ' - ' . $shBrand : $shBrand . ' - Hoa tươi giao tận nơi' ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/page-transition.css">
    <link rel="expect" href="#mainContent" blocking="render">
    <link rel="stylesheet" href="assets/css/customers/shop.css">

    <!-- Hiệu ứng chuyển trang (trước lần vẽ đầu) · popup xác nhận + toast · kiểm tra form -->
    <script src="assets/js/page-transition.js"></script>
    <script src="assets/js/popup.js"></script>
    <script src="assets/js/admin.js" defer></script>
</head>

<body class="shop">
    <div class="gn-progress" id="gnProgress" role="progressbar" aria-hidden="true"></div>

    <!-- ================= HEADER ================= -->
    <header class="sh-header" data-vt="header">
        <div class="container sh-header-inner">
            <a href="<?= $shUrl['home'] ?>" class="sh-logo" aria-label="<?= $shBrand ?> - Trang chủ">
                <i class="bi bi-flower2"></i> <?= $shBrand ?>
            </a>

            <form class="sh-search" action="index.php" method="get" role="search">
                <input type="hidden" name="router" value="customers">
                <i class="bi bi-search"></i>
                <input type="search" name="q" value="<?= $shE($shKeyword) ?>" placeholder="Tìm hoa hồng, tulip, hướng dương..."
                    aria-label="Tìm kiếm sản phẩm" autocomplete="off">
                <?php if ($shKeyword !== ''): ?>
                    <a class="sh-search-clear" href="<?= $shUrl['home'] ?>" title="Xóa tìm kiếm" aria-label="Xóa tìm kiếm"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </form>

            <nav class="sh-actions" aria-label="Tài khoản và giỏ hàng">
                <a href="<?= $shUrl['orders'] ?>" class="sh-action<?= $shActive === 'orders' ? ' active' : '' ?>" title="Đơn hàng của tôi">
                    <i class="bi bi-receipt"></i><span class="sh-action-label">Đơn hàng</span>
                </a>
                <a href="<?= $shUrl['cart'] ?>" class="sh-action<?= $shActive === 'cart' ? ' active' : '' ?>" title="Giỏ hàng"
                    aria-label="Giỏ hàng<?= $shCartCount ? " ($shCartCount sản phẩm)" : '' ?>">
                    <span class="sh-icon"><i class="bi bi-bag"></i>
                        <?php if ($shCartCount > 0): ?><span class="sh-badge"><?= $shCartCount > 99 ? '99+' : $shCartCount ?></span><?php endif; ?>
                    </span><span class="sh-action-label">Giỏ hàng</span>
                </a>

                <?php if ($shCustomer): ?>
                    <div class="dropdown">
                        <button class="sh-action dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="sh-avatar"><?= $shE($shInitial) ?></span>
                            <span class="sh-action-label">Chào, <?= $shE($shFirstName) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end mt-2">
                            <li class="px-3 py-2 small text-muted">Đăng nhập: <strong>@<?= $shE($shCustomer['username']) ?></strong></li>
                            <li><a class="dropdown-item" href="<?= $shUrl['orders'] ?>"><i class="bi bi-receipt"></i> Đơn hàng của tôi</a></li>
                            <li><a class="dropdown-item" href="<?= $shUrl['cart'] ?>"><i class="bi bi-bag"></i> Giỏ hàng</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $shUrl['logout'] ?>" <?= $shLogoutConfirm ?>><i class="bi bi-box-arrow-right text-danger"></i> Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= $shUrl['login'] ?>" class="sh-btn-login"><i class="bi bi-person"></i> Đăng nhập</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- ================= NỘI DUNG TRANG ================= -->
    <main class="sh-main" id="mainContent" data-vt="main">
        <?php
        if (isset($viewFile) && file_exists($viewFile)) {
            include $viewFile;
        }
        MessageHelper::logMessage(); // thông báo flash -> toast
        ?>
    </main>

    <!-- ================= FOOTER ================= -->
    <footer class="sh-footer">
        <div class="container">
            <div class="row g-3 g-lg-4">
                <div class="col-lg-5">
                    <div class="sh-footer-logo"><i class="bi bi-flower2"></i> <?= $shBrand ?></div>
                    <p class="sh-footer-tag">Mỗi đóa hoa mang một câu chuyện riêng — chọn từng bông hoa tươi để gửi trọn yêu thương đến người bạn trân quý.</p>
                </div>
                <div class="col-12 col-sm-6 col-lg-4 sh-footer-shop">
                    <h6>Mua sắm</h6>
                    <ul>
                        <li><a href="<?= $shUrl['home'] ?>">Tất cả sản phẩm</a></li>
                        <?php foreach ($shFooterCats as $c): ?>
                            <li><a href="index.php?router=customers&categories%5B%5D=<?= (int) $c['category_id'] ?>"><?= $shE($c['category_name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <!-- Mobile: ẩn (đã có ở thanh tab đáy) -->
                <div class="col-6 col-lg-3 sh-footer-acc">
                    <h6>Tài khoản</h6>
                    <ul>
                        <li><a href="<?= $shUrl['cart'] ?>">Giỏ hàng</a></li>
                        <li><a href="<?= $shUrl['orders'] ?>">Đơn hàng của tôi</a></li>
                        <?php if ($shCustomer): ?>
                            <li><a href="<?= $shUrl['logout'] ?>" <?= $shLogoutConfirm ?>>Đăng xuất</a></li>
                        <?php else: ?>
                            <li><a href="<?= $shUrl['login'] ?>">Đăng nhập</a></li>
                            <li><a href="<?= $shUrl['reg'] ?>">Tạo tài khoản</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="sh-footer-bottom">© <?= date('Y') ?> <?= $shBrand ?><span class="d-none d-md-inline">. Kiến tạo yêu thương từ những đóa hoa.</span></div>
        </div>
    </footer>

    <!-- ================= TAB ĐÁY (mobile) ================= -->
    <nav class="sh-tabbar sh-mobile-only" aria-label="Menu chính" data-vt="tabbar">
        <a class="sh-tab<?= $shActive === 'home' && $shKeyword === '' ? ' active' : '' ?>" href="<?= $shUrl['home'] ?>">
            <i class="bi bi-house<?= $shActive === 'home' && $shKeyword === '' ? '-fill' : '' ?>"></i><span>Trang chủ</span>
        </a>
        <button class="sh-tab<?= $shKeyword !== '' ? ' active' : '' ?>" type="button" data-bs-toggle="offcanvas" data-bs-target="#shSearchSheet" aria-controls="shSearchSheet">
            <i class="bi bi-search"></i><span>Tìm kiếm</span>
        </button>
        <a class="sh-tab<?= $shActive === 'cart' ? ' active' : '' ?>" href="<?= $shUrl['cart'] ?>">
            <span class="sh-tab-icon"><i class="bi bi-bag<?= $shActive === 'cart' ? '-fill' : '' ?>"></i>
                <?php if ($shCartCount > 0): ?><span class="sh-badge"><?= $shCartCount > 99 ? '99+' : $shCartCount ?></span><?php endif; ?>
            </span><span>Giỏ hàng</span>
        </a>
        <a class="sh-tab<?= $shActive === 'orders' ? ' active' : '' ?>" href="<?= $shUrl['orders'] ?>">
            <i class="bi bi-receipt<?= $shActive === 'orders' ? '-cutoff' : '' ?>"></i><span>Đơn hàng</span>
        </a>
        <?php if ($shCustomer): ?>
            <button class="sh-tab" type="button" data-bs-toggle="offcanvas" data-bs-target="#shAccountSheet" aria-controls="shAccountSheet">
                <i class="bi bi-person-circle"></i><span>Tài khoản</span>
            </button>
        <?php else: ?>
            <a class="sh-tab" href="<?= $shUrl['login'] ?>"><i class="bi bi-person"></i><span>Đăng nhập</span></a>
        <?php endif; ?>
    </nav>

    <!-- Bảng tìm kiếm (mobile) -->
    <div class="offcanvas offcanvas-top sh-sheet sh-mobile-only" tabindex="-1" id="shSearchSheet" aria-label="Tìm kiếm">
        <div class="offcanvas-body">
            <form class="sh-search mw-100" action="index.php" method="get" role="search">
                <input type="hidden" name="router" value="customers">
                <i class="bi bi-search"></i>
                <input type="search" name="q" value="<?= $shE($shKeyword) ?>" placeholder="Tìm hoa hồng, tulip, hướng dương..."
                    aria-label="Tìm kiếm sản phẩm" autocomplete="off" enterkeyhint="search">
                <button type="button" class="sh-search-clear" data-bs-dismiss="offcanvas" aria-label="Đóng"><i class="bi bi-x-lg"></i></button>
            </form>
        </div>
    </div>

    <?php if ($shCustomer): ?>
        <!-- Bảng tài khoản (mobile) -->
        <div class="offcanvas offcanvas-bottom sh-sheet sh-mobile-only" tabindex="-1" id="shAccountSheet" aria-labelledby="shAccountTitle">
            <div class="offcanvas-header pb-0">
                <div class="d-flex align-items-center gap-3">
                    <span class="sh-avatar" style="width:44px;height:44px;font-size:1.1rem"><?= $shE($shInitial) ?></span>
                    <div>
                        <div class="fw-bold" id="shAccountTitle"><?= $shE($shCustomer['full_name']) ?></div>
                        <div class="small text-muted">@<?= $shE($shCustomer['username']) ?></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
            </div>
            <div class="offcanvas-body">
                <a class="sh-sheet-link" href="<?= $shUrl['orders'] ?>"><i class="bi bi-receipt"></i> Đơn hàng của tôi</a>
                <a class="sh-sheet-link" href="<?= $shUrl['cart'] ?>"><i class="bi bi-bag"></i> Giỏ hàng</a>
                <a class="sh-sheet-link text-danger" href="<?= $shUrl['logout'] ?>" <?= $shLogoutConfirm ?>><i class="bi bi-box-arrow-right text-danger"></i> Đăng xuất</a>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mở bảng tìm kiếm (mobile) -> đặt con trỏ vào ô nhập
        document.getElementById('shSearchSheet')?.addEventListener('shown.bs.offcanvas', function() {
            this.querySelector('input[name=q]').focus();
        });
    </script>
</body>

</html>
