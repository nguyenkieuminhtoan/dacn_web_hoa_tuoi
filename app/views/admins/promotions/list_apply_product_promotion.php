<?php
// Biến từ PromotionController::listApplyProductPromotion(): $listApplyProductPromotion, $page, $totalPages, $totalPromotions
$ppRows = $listApplyProductPromotion;
$ppAction = 'listApplyProductPromotion';
$ppTitle = 'Đang áp dụng';
$ppEmpty = 'Hiện không có sản phẩm nào đang được giảm giá.';
$ppTotal = $totalPromotions;
include __DIR__ . '/_product_promotion_list.php';
