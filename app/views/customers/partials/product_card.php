<?php
/**
 * Thẻ sản phẩm dùng chung (trang chủ, gợi ý ở trang chi tiết). CSS: assets/css/customers/shop.css
 * @var array $product  hàng từ ProductModel (đã qua applyPromotions)
 */
$pcE = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$pcMoney = fn($v) => number_format((float) $v, 0, ',', '.') . 'đ';
$pcUrl = 'index.php?router=customers&controller=detail&action=index&id=' . (int) $product['product_id'];
$pcInStock = $product['stock_quantity'] > 0;
$pcSale = isset($product['discounted_price']);
$pcPercent = $pcSale && $product['price'] > 0 ? round(100 - $product['discounted_price'] / $product['price'] * 100) : 0;
?>
<div class="product-card">
    <div class="product-img">
        <a href="<?= $pcUrl ?>" tabindex="-1">
            <img src="<?= $pcE(productImageUrl($product['image_url'])) ?>" alt="<?= $pcE($product['product_name']) ?>" loading="lazy"
                onerror="this.src='https://placehold.co/400x500?text=Flower'">
        </a>
        <?php if ($pcSale): ?>
            <div class="product-badge">-<?= $pcPercent ?>%</div>
        <?php endif; ?>
    </div>
    <div class="product-body">
        <div class="product-category"><?= $pcE($product['category_name']) ?></div>
        <a href="<?= $pcUrl ?>" class="product-name"><?= $pcE($product['product_name']) ?></a>

        <div class="product-price">
            <span class="current-price"><?= $pcMoney($pcSale ? $product['discounted_price'] : $product['price']) ?></span>
            <?php if ($pcSale): ?><span class="old-price"><?= $pcMoney($product['price']) ?></span><?php endif; ?>
        </div>
        <!-- Luôn có dòng này (có thể trống) để nút ở các thẻ thẳng hàng -->
        <div class="stock-note">
            <?php if ($pcInStock && $product['stock_quantity'] <= 5): ?>
                <i class="bi bi-fire"></i> Chỉ còn <?= (int) $product['stock_quantity'] ?> sản phẩm
            <?php endif; ?>
        </div>

        <div class="product-footer">
            <form action="index.php?router=customers&controller=cart&action=addCart" method="POST">
                <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn-add-cart" <?= $pcInStock ? '' : 'disabled' ?>>
                    <i class="bi bi-bag-plus"></i> <?= $pcInStock ? 'Thêm vào giỏ' : 'Hết hàng' ?>
                </button>
            </form>
        </div>
    </div>
</div>
