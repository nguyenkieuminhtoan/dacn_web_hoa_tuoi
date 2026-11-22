<?php
// Biến từ PromotionController::listAllProductPromotion(): $listAll, $page, $totalPages, $total
$ppRows = $listAll;
$ppAction = 'listAllProductPromotion';
$ppTitle = 'Tất cả';
$ppEmpty = 'Chưa có sản phẩm nào được áp dụng khuyến mãi.';
$ppTotal = $total;
include __DIR__ . '/_product_promotion_list.php';
