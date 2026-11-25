<?php
/**
 * Trang chủ / danh sách sản phẩm. Biến từ IndexController::index():
 * @var array      $products
 * @var array      $categories
 * @var array      $categoryCounts
 * @var array      $selectedCategories
 * @var array|null $selectedPriceRange
 * @var string     $keyword
 * @var int        $totalProducts
 * @var int        $currentPage
 * @var int        $totalPages
 */
$currentPage = $currentPage ?? 1;
$totalPages  = $totalPages ?? 1;
$keyword     = $keyword ?? '';
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float) $v, 0, ',', '.') . 'đ';

// Khoảng giá theo giá hoa thực tế của cửa hàng
$priceRanges = [
    '0-399999'         => 'Dưới 400.000đ',
    '400000-599999'    => '400.000 – 600.000đ',
    '600000-799999'    => '600.000 – 800.000đ',
    '800000-999999999' => 'Trên 800.000đ',
];
$currentPrice = $_GET['price_range'] ?? '';

// URL giữ nguyên các bộ lọc khác, bỏ 1 bộ lọc (dùng cho nhãn ✕)
$urlWithout = function ($key, $value = null) {
    $q = $_GET;
    unset($q['page']);
    if ($key === 'categories') {
        $q['categories'] = array_values(array_diff((array) ($q['categories'] ?? []), [(string) $value]));
        if (!$q['categories']) unset($q['categories']);
    } else {
        unset($q[$key]);
    }
    $q['router'] = 'customers';
    return 'index.php?' . http_build_query($q);
};

// Các bộ lọc đang bật -> nhãn
$activeChips = [];
if ($keyword !== '') $activeChips[] = ['Từ khóa: “' . $keyword . '”', $urlWithout('q')];
foreach ($categories as $cat) {
    if (in_array((string) $cat['category_id'], array_map('strval', $selectedCategories), true)) {
        $activeChips[] = [$cat['category_name'], $urlWithout('categories', $cat['category_id'])];
    }
}
if ($currentPrice !== '') {
    $activeChips[] = [$priceRanges[$currentPrice] ?? 'Khoảng giá', $urlWithout('price_range')];
}
$filterCount = count($selectedCategories) + ($currentPrice !== '' ? 1 : 0);
$isBrowsing = $keyword !== '' || $filterCount > 0; // đang tìm / lọc -> ẩn banner
// "Bỏ lọc": xóa danh mục + khoảng giá, giữ từ khóa tìm kiếm
$resetUrl = 'index.php?router=customers' . ($keyword !== '' ? '&amp;q=' . rawurlencode($keyword) : '');
?>
<style>
    /* ---------- Banner ---------- */
    .hero {
        background: linear-gradient(135deg, #f6ede5 0%, #ede0d4 100%);
        padding: 1.75rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 32px 32px;
        position: relative;
        overflow: hidden;
    }

    /* Ảnh banner: khung ngang thấp, không chiếm nhiều chiều cao */
    .hero-img {
        width: 100%;
        height: 240px;
        object-fit: cover;
        border-radius: 20px;
        box-shadow: var(--shadow-md);
    }

    .hero::before {
        content: "";
        position: absolute;
        top: -50px;
        right: -50px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255, 255, 255, .4) 0%, transparent 70%);
        border-radius: 50%;
    }

    .hero h1 {
        font-size: 2.1rem;
        font-weight: 800;
        letter-spacing: -1px;
        color: #3a2c23;
        line-height: 1.15;
        margin-bottom: .5rem;
    }

    .hero p {
        font-size: 1.05rem;
        color: var(--text-muted);
        max-width: 480px;
        margin-bottom: 0;
    }

    .hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #fff;
        color: var(--primary-color);
        padding: 6px 14px;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 700;
        box-shadow: var(--shadow-sm);
    }

    .btn-hero {
        background: var(--primary-gradient);
        color: #fff;
        border: 0;
        border-radius: 999px;
        padding: 11px 28px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-hero:hover {
        color: #fff;
        box-shadow: 0 10px 22px rgba(233, 30, 99, .28);
        transform: translateY(-2px);
    }

    .shop-top {
        padding-top: 2rem;
    }

    /* ---------- Bố cục: cột lọc hẹp cố định + lưới sản phẩm ---------- */
    @media (min-width: 992px) {
        .shop-layout {
            display: grid;
            grid-template-columns: 236px minmax(0, 1fr);
            gap: 28px;
            align-items: start;
        }
    }

    /* ---------- Bộ lọc ---------- */
    .filter-card {
        background: var(--card-bg);
        border-radius: 20px;
        padding: 1.1rem 1rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid #f0e6db;
        position: sticky;
        top: calc(var(--sh-header-h) + 16px);
        /* Không cao quá màn hình -> luôn "dính" được khi cuộn, dài thì cuộn bên trong */
        max-height: calc(100vh - var(--sh-header-h) - 32px);
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #f3d3de transparent;
    }

    .filter-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 0 .4rem;
        margin-bottom: .75rem;
    }

    .filter-title {
        font-weight: 800;
        font-size: 1rem;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-reset {
        font-size: .78rem;
        font-weight: 700;
        color: #d63031;
        text-decoration: none;
    }

    .filter-reset:hover {
        text-decoration: underline;
        color: #d63031;
    }

    .filter-group+.filter-group {
        border-top: 1px solid #f3e9e2;
        margin-top: .75rem;
        padding-top: .85rem;
    }

    .filter-group h6 {
        font-weight: 700;
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin: 0 .4rem .4rem;
        color: #a08b7d;
    }

    /* Mỗi lựa chọn là 1 dòng gọn, cả dòng bấm được */
    .filter-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px .4rem;
        border-radius: 10px;
        font-size: .88rem;
        line-height: 1.35;
        color: var(--text-main);
        cursor: pointer;
        transition: background .15s ease, color .15s ease;
    }

    .filter-option:hover {
        background: #fdf3f6;
    }

    .filter-option:has(input:checked) {
        background: #fde8ef;
        color: var(--primary-color);
        font-weight: 600;
    }

    .filter-option input {
        accent-color: var(--primary-color);
        width: 16px;
        height: 16px;
        margin: 0;
        flex-shrink: 0;
    }

    .filter-option .name {
        flex: 1;
        min-width: 0;
    }

    .filter-option .num {
        font-size: .72rem;
        font-weight: 600;
        color: #b3a196;
    }

    .filter-option:has(input:checked) .num {
        color: var(--primary-color);
    }

    /* ---------- Danh mục dài: thu gọn / tìm nhanh ---------- */
    .cat-list.collapsed .filter-option.is-extra {
        display: none;
    }

    /* Mở rộng: danh sách cuộn bên trong, khung lọc không bị kéo dài */
    .cat-list:not(.collapsed) {
        max-height: 264px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #f3d3de transparent;
        margin-right: -4px;
        padding-right: 4px;
    }

    .cat-list .filter-option[hidden] {
        display: none;
    }

    .cat-empty {
        font-size: .82rem;
        color: #a08b7d;
        padding: 6px .4rem;
    }

    .cat-search {
        position: relative;
        margin: 0 0 .4rem;
    }

    .cat-search i {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        font-size: .8rem;
        color: #b3a196;
        pointer-events: none;
    }

    .cat-search input {
        width: 100%;
        height: 34px;
        border-radius: 10px;
        border: 1px solid #efe2da;
        background: #fffafb;
        padding: 0 10px 0 30px;
        font-size: .84rem;
        color: var(--text-main);
    }

    .cat-search input:focus {
        outline: none;
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(240, 98, 146, .12);
        background: #fff;
    }

    .cat-toggle {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: .3rem 0 0 .4rem;
        padding: 0;
        border: 0;
        background: none;
        font-size: .82rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .cat-toggle:hover span {
        text-decoration: underline;
    }

    .cat-toggle i {
        font-size: .72rem;
        transition: transform .2s ease;
    }

    .cat-toggle[aria-expanded="true"] i {
        transform: rotate(180deg);
    }

    .btn-filter {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 42px;
        padding: 0 16px;
        border-radius: 999px;
        border: 1px solid #ecdcd2;
        background: #fff;
        font-weight: 600;
        font-size: .9rem;
        color: var(--text-main);
    }

    .btn-filter .count {
        background: var(--primary-color);
        color: #fff;
        border-radius: 999px;
        min-width: 20px;
        height: 20px;
        font-size: .7rem;
        display: grid;
        place-items: center;
        padding: 0 6px;
    }

    /* ---------- Tiêu đề danh sách + nhãn bộ lọc ---------- */
    .section-header {
        margin-bottom: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .section-header h2 {
        font-weight: 800;
        font-size: 2rem;
        color: var(--text-main);
        letter-spacing: -.5px;
        margin: 0;
    }

    .filter-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 1.5rem;
    }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 8px 6px 14px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #f1dbe3;
        color: var(--text-main);
        font-size: .85rem;
        font-weight: 500;
        text-decoration: none;
    }

    .filter-chip i {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #fde8ef;
        color: var(--primary-color);
        display: grid;
        place-items: center;
        font-size: .7rem;
    }

    .filter-chip:hover {
        border-color: var(--accent-color);
        color: var(--primary-color);
    }

    .filter-chip.clear {
        background: transparent;
        border-color: transparent;
        color: #d63031;
        font-weight: 700;
        padding-right: 14px;
    }

    /* ---------- Trống / phân trang ---------- */
    .shop-empty {
        background: #fff;
        border-radius: var(--radius-lg);
        border: 1px dashed #f0d9e1;
        padding: 3.5rem 1.5rem;
        text-align: center;
    }

    .shop-empty i {
        font-size: 3rem;
        color: #f4b8cb;
    }

    .pagination-container {
        margin-top: 3rem;
        display: flex;
        justify-content: center;
    }

    .pagination-modern {
        display: flex;
        gap: 10px;
    }

    .page-item-custom {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: white;
        border: 1px solid #f0e6db;
        color: var(--text-main);
        font-weight: 600;
        text-decoration: none;
        transition: var(--transition);
    }

    .page-item-custom:hover {
        background: var(--bg-color);
        border-color: var(--accent-color);
        color: var(--accent-color);
    }

    .page-item-custom.active {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    .page-item-custom.disabled {
        opacity: .4;
        pointer-events: none;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-up {
        animation: fadeUp .3s cubic-bezier(.2, 0, 0, 1) both;
    }

    /* ---------- Bộ lọc dạng bảng trượt (dưới 992px) ---------- */
    @media (max-width: 991.98px) {
        #shopFilter.offcanvas-lg {
            height: auto;
            max-height: 85vh;
            border-radius: 22px 22px 0 0;
            padding-bottom: env(safe-area-inset-bottom);
        }

        #shopFilter .filter-card {
            position: static;
            max-height: none;
            overflow: visible;
            border: 0;
            box-shadow: none;
            padding: 0;
        }

        /* Bảng trượt đã có tiêu đề + nút "Bỏ lọc" riêng ở thanh trên */
        #shopFilter .filter-head {
            display: none;
        }

        .filter-option {
            padding: 10px .5rem;
            font-size: .95rem;
        }

        /* Trong bảng trượt: cả bảng đã cuộn được, không cần cuộn lồng */
        #shopFilter .cat-list:not(.collapsed) {
            max-height: none;
            overflow: visible;
        }

        .cat-search input {
            height: 40px;
            font-size: 16px; /* >= 16px để iOS không tự phóng to khi gõ */
        }
    }

    @media (max-width: 767.98px) {
        .hero {
            padding: 1.25rem 0 1.4rem;
            margin-bottom: 1.25rem;
            border-radius: 0 0 24px 24px;
        }

        .hero h1 {
            font-size: 1.45rem;
            letter-spacing: -.5px;
        }

        .hero p {
            font-size: .9rem;
        }

        .hero-tag {
            font-size: .72rem;
            padding: 4px 11px;
        }

        .btn-hero {
            padding: 9px 22px;
            font-size: .88rem;
        }

        .section-header h2 {
            font-size: 1.4rem;
        }

        .pagination-container {
            margin-top: 2rem;
        }
    }
</style>

<?php if (!$isBrowsing): ?>
    <!-- Banner -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-7 animate-fade-up">
                    <span class="hero-tag mb-2"><i class="bi bi-flower1"></i> Hoa tươi mỗi ngày</span>
                    <h1>Trao gửi yêu thương qua từng cánh hoa</h1>
                    <p>Bộ sưu tập hoa tươi được thiết kế tỉ mỉ, gửi trọn yêu thương đến người bạn trân quý.</p>
                    <div class="mt-3">
                        <a href="#products" class="btn-hero">Mua ngay <i class="bi bi-arrow-down"></i></a>
                    </div>
                </div>
                <div class="col-md-5 d-none d-md-block animate-fade-up" style="animation-delay: 60ms">
                    <img src="https://images.unsplash.com/photo-1526047932273-341f2a7631f9?q=80&w=1000&auto=format&fit=crop"
                        class="hero-img" alt="Bó hoa tươi">
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<div class="container<?= $isBrowsing ? ' shop-top' : '' ?>" id="products">
    <!-- Tiêu đề nằm trên cả 2 cột để cột lọc và lưới sản phẩm bắt đầu thẳng hàng -->
    <div class="section-header">
        <h2><?= $keyword !== '' ? 'Kết quả cho “' . $e($keyword) . '”' : 'Bộ sưu tập hoa' ?></h2>
        <button class="btn-filter d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#shopFilter" aria-controls="shopFilter">
            <i class="bi bi-sliders"></i> Bộ lọc
            <?php if ($filterCount > 0): ?><span class="count"><?= $filterCount ?></span><?php endif; ?>
        </button>
    </div>

    <div class="shop-layout">
        <!-- Bộ lọc: cột trái trên máy tính, bảng trượt trên điện thoại / tablet -->
        <aside>
            <div class="offcanvas-lg offcanvas-bottom" tabindex="-1" id="shopFilter" aria-labelledby="shopFilterTitle">
                <div class="offcanvas-header d-lg-none">
                    <h5 class="offcanvas-title fw-bold" id="shopFilterTitle"><i class="bi bi-sliders me-2" style="color:var(--primary-color)"></i>Bộ lọc</h5>
                    <?php if ($filterCount > 0): ?>
                        <a class="filter-reset ms-auto me-3" href="<?= $resetUrl ?>">Bỏ lọc</a>
                    <?php endif; ?>
                    <button type="button" class="btn-close<?= $filterCount > 0 ? ' m-0' : '' ?>" data-bs-dismiss="offcanvas" data-bs-target="#shopFilter" aria-label="Đóng"></button>
                </div>
                <div class="offcanvas-body d-block">
                    <div class="filter-card">
                        <div class="filter-head">
                            <div class="filter-title"><i class="bi bi-sliders"></i> Bộ lọc</div>
                            <?php if ($filterCount > 0): ?>
                                <a class="filter-reset" href="<?= $resetUrl ?>">Bỏ lọc</a>
                            <?php endif; ?>
                        </div>

                        <form id="filterForm" method="GET" action="index.php">
                            <input type="hidden" name="router" value="customers">
                            <?php if ($keyword !== ''): ?>
                                <input type="hidden" name="q" value="<?= $e($keyword) ?>">
                            <?php endif; ?>

                            <?php
                            // Nhiều danh mục: chỉ hiện $catLimit mục đầu (+ các mục đang chọn), còn lại bấm "Xem thêm".
                            // Từ $catSearchFrom mục trở lên thì có thêm ô tìm danh mục.
                            $catLimit      = 6;
                            $catSearchFrom = 10;
                            $catTotal      = count($categories);
                            $selectedIds   = array_map('strval', $selectedCategories);
                            $catExtra      = 0;
                            ?>
                            <div class="filter-group" data-cat-filter>
                                <h6>Danh mục</h6>

                                <?php if ($catTotal >= $catSearchFrom): ?>
                                    <div class="cat-search">
                                        <i class="bi bi-search"></i>
                                        <!-- Không có name -> không gửi lên server, không kích hoạt lọc -->
                                        <input type="search" placeholder="Tìm danh mục..." aria-label="Tìm danh mục" autocomplete="off" data-cat-search>
                                    </div>
                                <?php endif; ?>

                                <div class="cat-list<?= $catTotal > $catLimit ? ' collapsed' : '' ?>" data-cat-list>
                                    <?php $i = 0;
                                    foreach ($categories as $category):
                                        $checked = in_array((string) $category['category_id'], $selectedIds, true);
                                        $isExtra = $i++ >= $catLimit && !$checked; // mục đang chọn luôn hiện
                                        if ($isExtra) $catExtra++;
                                    ?>
                                        <label class="filter-option<?= $isExtra ? ' is-extra' : '' ?>">
                                            <input type="checkbox" name="categories[]" value="<?= (int) $category['category_id'] ?>" <?= $checked ? 'checked' : '' ?>>
                                            <span class="name"><?= $e($category['category_name']) ?></span>
                                            <span class="num"><?= (int) ($categoryCounts[$category['category_id']] ?? 0) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                    <div class="cat-empty" hidden>Không có danh mục phù hợp</div>
                                </div>

                                <?php if ($catExtra > 0): ?>
                                    <button type="button" class="cat-toggle" data-cat-toggle data-more="Xem thêm <?= $catExtra ?> danh mục" aria-expanded="false">
                                        <span>Xem thêm <?= $catExtra ?> danh mục</span> <i class="bi bi-chevron-down"></i>
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="filter-group">
                                <h6>Khoảng giá</h6>
                                <label class="filter-option">
                                    <input type="radio" name="price_range" value="" <?= $currentPrice === '' ? 'checked' : '' ?>>
                                    <span class="name">Tất cả mức giá</span>
                                </label>
                                <?php foreach ($priceRanges as $value => $label): ?>
                                    <label class="filter-option">
                                        <input type="radio" name="price_range" value="<?= $value ?>" <?= $currentPrice === $value ? 'checked' : '' ?>>
                                        <span class="name"><?= $label ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Danh sách sản phẩm -->
        <div class="shop-list">
            <?php if ($activeChips): ?>
                <div class="filter-chips" aria-label="Bộ lọc đang áp dụng">
                    <?php foreach ($activeChips as [$label, $url]): ?>
                        <a class="filter-chip" href="<?= $e($url) ?>" title="Bỏ bộ lọc này"><?= $e($label) ?> <i class="bi bi-x-lg"></i></a>
                    <?php endforeach; ?>
                    <?php if (count($activeChips) > 1): ?>
                        <a class="filter-chip clear" href="index.php?router=customers">Xóa tất cả</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($products)): ?>
                <div class="shop-empty">
                    <i class="bi bi-flower2"></i>
                    <h4 class="mt-3 fw-bold">Không tìm thấy sản phẩm phù hợp</h4>
                    <p class="text-muted mb-4">
                        <?= $keyword !== '' ? 'Không có hoa nào khớp với “' . $e($keyword) . '”. Thử từ khóa ngắn hơn hoặc bỏ bớt bộ lọc nhé!' : 'Thử bỏ bớt bộ lọc để xem thêm mẫu hoa nhé!' ?>
                    </p>
                    <a href="index.php?router=customers" class="btn-hero">Xem tất cả hoa</a>
                </div>
            <?php else: ?>
                <div class="row g-3 g-md-4">
                    <?php $delay = 0; ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-6 col-md-4 col-xl-3 animate-fade-up" style="animation-delay: <?= $delay ?>ms">
                            <?php include __DIR__ . '/partials/product_card.php'; ?>
                        </div>
                        <?php $delay = min($delay + 25, 150); ?>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1):
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryParams['router'] = 'customers';
                    $pageBase = 'index.php?' . http_build_query($queryParams) . '&page=';
                ?>
                    <nav class="pagination-container" aria-label="Phân trang">
                        <div class="pagination-modern">
                            <a class="page-item-custom <?= $currentPage <= 1 ? 'disabled' : '' ?>" aria-label="Trang trước"
                                href="<?= $currentPage > 1 ? $e($pageBase . ($currentPage - 1)) : '#' ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                            <?php
                            $startPage = max(1, $currentPage - 1);
                            $endPage = min($totalPages, $startPage + 2);
                            if ($endPage - $startPage < 2) $startPage = max(1, $endPage - 2);
                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a class="page-item-custom <?= $i == $currentPage ? 'active' : '' ?>" href="<?= $e($pageBase . $i) ?>"
                                    <?= $i == $currentPage ? 'aria-current="page"' : '' ?>><?= $i ?></a>
                            <?php endfor; ?>
                            <a class="page-item-custom <?= $currentPage >= $totalPages ? 'disabled' : '' ?>" aria-label="Trang sau"
                                href="<?= $currentPage < $totalPages ? $e($pageBase . ($currentPage + 1)) : '#' ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Bộ lọc: chọn là lọc ngay (về trang 1). Chỉ các ô có name (bỏ qua ô tìm danh mục).
    document.querySelectorAll('#filterForm input[name]').forEach(function(input) {
        input.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });

    // Danh mục dài: Xem thêm / Thu gọn + tìm nhanh (không phân biệt dấu)
    (function() {
        var group = document.querySelector('[data-cat-filter]');
        if (!group) return;
        var list = group.querySelector('[data-cat-list]');
        var toggle = group.querySelector('[data-cat-toggle]');
        var search = group.querySelector('[data-cat-search]');
        var empty = list.querySelector('.cat-empty');
        var items = Array.prototype.slice.call(list.querySelectorAll('.filter-option'));
        var fold = function(s) {
            return s.normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'd').toLowerCase().trim();
        };

        function setExpanded(open) {
            list.classList.toggle('collapsed', !open);
            if (!toggle) return;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.querySelector('span').textContent = open ? 'Thu gọn' : toggle.dataset.more;
            if (!open) list.scrollTop = 0;
        }

        if (toggle) {
            toggle.addEventListener('click', function() {
                setExpanded(list.classList.contains('collapsed'));
            });
        }

        if (search) {
            search.addEventListener('keydown', function(ev) {
                if (ev.key === 'Enter') ev.preventDefault(); // không gửi form
            });
            search.addEventListener('input', function() {
                var q = fold(search.value);
                var shown = 0;
                items.forEach(function(item) {
                    var ok = !q || fold(item.querySelector('.name').textContent).indexOf(q) !== -1;
                    item.hidden = !ok;
                    if (ok) shown++;
                });
                empty.hidden = shown > 0;
                // Đang tìm: hiện mọi kết quả khớp, ẩn nút Xem thêm
                if (q) list.classList.remove('collapsed');
                else setExpanded(toggle ? toggle.getAttribute('aria-expanded') === 'true' : true);
                if (toggle) toggle.hidden = !!q;
            });
        }
    })();
</script>
