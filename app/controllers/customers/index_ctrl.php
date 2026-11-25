<?php
require_once __DIR__ . '/../../models/product_model.php';
require_once __DIR__ . '/../../models/category_model.php';
require_once __DIR__ . '/../../models/promotion_model.php';

class IndexController
{
    public function index()
    {
        $productModel = new ProductModel();
        $categoryModel = new CategoryModel();
        $promotionModel = new PromotionModel();

        // Lọc
        $selectedCategories = isset($_GET['categories']) && is_array($_GET['categories'])
            ? $_GET['categories']
            : [];

        $selectedPriceRange = isset($_GET['price_range']) && !empty($_GET['price_range'])
            ? explode('-', $_GET['price_range'])
            : null;

        // Tìm kiếm theo tên sản phẩm / danh mục (ô tìm kiếm trên header)
        $keyword = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);

        // Phân trang
        $itemsPerPage = 8; // 8 sản phẩm / trang (2 hàng × 4 cột trên máy tính)
        $currentPage = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($currentPage - 1) * $itemsPerPage;

        // Lấy dữ liệu
        $products = $productModel->getProductsForCustomerPagination(
            $itemsPerPage,
            $offset,
            $selectedCategories,
            $selectedPriceRange,
            $keyword
        );

        $totalProducts = (int) $productModel->countProductsForCustomer(
            $selectedCategories,
            $selectedPriceRange,
            $keyword
        );

        $totalPages = max(1, (int) ceil($totalProducts / $itemsPerPage));

        // Lấy danh mục chỉ những danh mục có sản phẩm
        $allCategories = $categoryModel->getAllCategory();
        $activeProducts = $productModel->getAllProductForCustomer();

        $activeCategories = array_unique(array_column($activeProducts, 'category_id'));

        $categories = array_filter($allCategories, function ($cat) use ($activeCategories) {
            return in_array($cat['category_id'], $activeCategories);
        });

        // Tính số lượng sản phẩm cho mỗi danh mục
        $categoryCounts = [];
        foreach ($activeProducts as $product) {
            $catId = $product['category_id'];
            $categoryCounts[$catId] = ($categoryCounts[$catId] ?? 0) + 1;
        }

        // Lấy min/max giá từ sản phẩm
        $prices = array_column($activeProducts, 'price');
        $minPrice = !empty($prices) ? min($prices) : 0;
        $maxPrice = !empty($prices) ? max($prices) : 0;

        // Giữ lại filter khi chuyển trang
        $queryParams = $_GET;
        unset($queryParams['page']);
        $baseQuery = http_build_query($queryParams);
        $baseUrl = $baseQuery ? '?' . $baseQuery . '&' : '?';

        $viewFile = __DIR__ . '/../../views/customers/dashboard.php';
        $pageTitle = $keyword !== '' ? 'Tìm kiếm: ' . $keyword : '';
        require __DIR__ . '/../../views/customers/layout.php';
    }
}