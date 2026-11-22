<?php
// Biến từ PromotionController::listExpiredProductPromotion(): $listExpired, $page, $totalPages, $total
$ppRows = $listExpired;
$ppAction = 'listExpiredProductPromotion';
$ppTitle = 'Hết hạn';
$ppEmpty = 'Không có sản phẩm nào gắn mã khuyến mãi đã hết hạn.';
$ppTotal = $total;
include __DIR__ . '/_product_promotion_list.php';
