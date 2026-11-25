<?php
require_once __DIR__ . '/../../models/product_model.php';
require_once __DIR__ . '/../../models/category_model.php';

class DetailController
{
    public function index()
    {
        $productModel = new ProductModel();
        $categoryModel = new CategoryModel();

        $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$productId) {
            header("Location: index.php?router=customers");
            exit;
        }

        // getByIdProduct() đã áp khuyến mãi (cùng cách tính với trang chủ)
        $product = $productModel->getByIdProduct($productId);

        // Không có / đã ngừng kinh doanh -> về cửa hàng
        if (!$product || $product['status'] === 'discontinued') {
            header("Location: index.php?router=customers");
            exit;
        }

        $category = $categoryModel->getByIdCategory($product['category_id']);
        $related = $productModel->getRelatedProducts($product['category_id'], $productId, 4);

        $viewFile = __DIR__ . '/../../views/customers/detail.php';
        $pageTitle = $product['product_name'];
        require __DIR__ . '/../../views/customers/layout.php';
    }
}
