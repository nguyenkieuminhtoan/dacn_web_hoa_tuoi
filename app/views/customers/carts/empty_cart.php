<?php
?>
<style>
.empty-cart-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: 5rem 2rem;
            box-shadow: var(--shadow-md);
            text-align: center;
            max-width: 600px;
            margin: auto;
        }

.empty-icon {
            font-size: 6rem;
            color: #f1e4d8;
            margin-bottom: 2rem;
            display: block;
        }

.btn-shop {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 1.2rem 3rem;
            border-radius: 50px;
            font-weight: 800;
            font-size: 1.1rem;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 2rem;
        }

.btn-shop:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(43, 94, 59, 0.2);
            color: white;
        }

@keyframes fadeUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

.animate-fade-up {
            animation: fadeUp .3s cubic-bezier(0.2, 0, 0, 1) forwards;
        }
</style>


    <div class="container py-5">
        <div class="empty-cart-card animate-fade-up">
            <i class="bi bi-bag-heart empty-icon"></i>
            <h1 class="heading-font fw-bold mb-3 fs-1">Giỏ hàng đang chờ...</h1>
            <p class="text-muted fs-5">Những đóa hoa đẹp nhất vẫn đang chờ bạn khám phá. Hãy lấp đầy giỏ hàng bằng yêu thương nhé!</p>
            
            <a href="index.php?router=customers" class="btn-shop">
                <i class="bi bi-flower2"></i> KHÁM PHÁ NGAY
            </a>
        </div>
    </div>
