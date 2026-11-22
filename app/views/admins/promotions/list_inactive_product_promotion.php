<?php
// Biến từ PromotionController::listInactiveProductPromotion(): $listInactive, $page, $totalPages, $total
$ppRows = $listInactive;
$ppAction = 'listInactiveProductPromotion';
$ppTitle = 'Tạm ngưng';
$ppEmpty = 'Không có sản phẩm nào gắn mã khuyến mãi đang tạm ngưng.';
$ppTotal = $total;
include __DIR__ . '/_product_promotion_list.php';
