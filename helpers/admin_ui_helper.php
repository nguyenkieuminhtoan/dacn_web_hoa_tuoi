<?php
// ==================================================================
// Hàm hiển thị dùng chung cho các trang quản trị (đi kèm assets/css/admins/admin.css)
// Layout admin đã require file này, các view dùng trực tiếp.
// ==================================================================

if (!function_exists('ad_e')) {

    // Escape HTML ngắn gọn
    function ad_e($value)
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    function ad_money($value)
    {
        return number_format((float) $value, 0, ',', '.') . '₫';
    }

    function ad_date($value, $withTime = false)
    {
        if (empty($value)) return '—';
        return date($withTime ? 'd/m/Y H:i' : 'd/m/Y', strtotime($value));
    }

    // Đường dẫn ảnh trong uploads/ tính từ app/views/admins/index.php
    function ad_img_url($file)
    {
        return '../../../uploads/' . rawurlencode((string) $file);
    }

    // Ảnh thu nhỏ (hoặc ô trống có icon khi không có ảnh)
    function ad_thumb($file, $alt = '', $class = '')
    {
        if (empty($file)) {
            return '<span class="ad-thumb ' . $class . '" aria-hidden="true"><i class="fas fa-image"></i></span>';
        }
        return '<img class="ad-thumb ' . $class . '" src="' . ad_img_url($file) . '" alt="' . ad_e($alt) . '" loading="lazy">';
    }

    function ad_badge($text, $tone = 'muted')
    {
        return '<span class="ad-badge is-' . $tone . '">' . ad_e($text) . '</span>';
    }

    // ---------- Nhãn trạng thái ----------
    function ad_order_status_label($status)
    {
        return [
            'pending'   => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'shipping'  => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ][$status] ?? 'Không rõ';
    }

    function ad_order_status($status)
    {
        $tone = [
            'pending' => 'warning', 'confirmed' => 'primary', 'shipping' => 'info',
            'completed' => 'success', 'cancelled' => 'muted',
        ][$status] ?? 'danger';
        return ad_badge(ad_order_status_label($status), $tone);
    }

    function ad_payment_status($status)
    {
        [$text, $tone] = [
            'unpaid'   => ['Chưa thanh toán', 'warning'],
            'paid'     => ['Đã thanh toán', 'success'],
            'refunded' => ['Đã hoàn tiền', 'muted'],
        ][$status] ?? ['Không rõ', 'danger'];
        return ad_badge($text, $tone);
    }

    function ad_payment_method($method)
    {
        return [
            'cod'           => 'Thanh toán khi nhận (COD)',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'e_wallet'      => 'Ví điện tử',
        ][$method] ?? 'Không rõ';
    }

    function ad_product_status_label($status)
    {
        return [
            'available'    => 'Còn hàng',
            'out_of_stock' => 'Tạm hết hàng',
            'discontinued' => 'Ngừng kinh doanh',
        ][$status] ?? $status;
    }

    function ad_product_status($status)
    {
        $tone = ['available' => 'success', 'out_of_stock' => 'warning', 'discontinued' => 'muted'][$status] ?? 'danger';
        return ad_badge(ad_product_status_label($status), $tone);
    }

    function ad_promo_status_label($status)
    {
        return ['active' => 'Đang hoạt động', 'inactive' => 'Tạm ngưng', 'expired' => 'Hết hạn'][$status] ?? $status;
    }

    function ad_promo_status($status)
    {
        $tone = ['active' => 'success', 'inactive' => 'warning', 'expired' => 'muted'][$status] ?? 'danger';
        return ad_badge(ad_promo_status_label($status), $tone);
    }

    function ad_discount_type_label($type)
    {
        return ['percentage' => 'Giảm theo phần trăm (%)', 'fixed_amount' => 'Giảm số tiền cố định (₫)'][$type] ?? $type;
    }

    function ad_discount($type, $value)
    {
        return $type === 'percentage'
            ? 'Giảm ' . rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',') . '%'
            : 'Giảm ' . ad_money($value);
    }

    // ---------- Phân trang ----------
    // $baseUrl: URL không có &page=  (vd: index.php?controller=product&action=listProduct)
    function ad_pagination($page, $totalPages, $baseUrl, $totalItems = null)
    {
        $page = max(1, (int) $page);
        $totalPages = (int) $totalPages;
        if ($totalPages <= 1) return '';

        $link = function ($p, $label, $aria = '') use ($baseUrl) {
            return '<a href="' . ad_e($baseUrl . '&page=' . $p) . '"' . ($aria ? ' aria-label="' . $aria . '"' : '') . '>' . $label . '</a>';
        };

        // Hiện tối đa 5 số quanh trang hiện tại
        $from = max(1, $page - 2);
        $to = min($totalPages, $from + 4);
        $from = max(1, $to - 4);

        $html = '<nav class="ad-pagination" aria-label="Phân trang">';
        $html .= '<span class="ad-pagination-info">Trang ' . $page . ' / ' . $totalPages
            . ($totalItems !== null ? ' · ' . (int) $totalItems . ' mục' : '') . '</span>';
        $html .= '<ul class="ad-pages">';
        $html .= $page > 1
            ? '<li>' . $link($page - 1, '<i class="fas fa-chevron-left"></i>', 'Trang trước') . '</li>'
            : '<li class="disabled"><span><i class="fas fa-chevron-left"></i></span></li>';
        for ($i = $from; $i <= $to; $i++) {
            $html .= $i === $page
                ? '<li class="active"><span aria-current="page">' . $i . '</span></li>'
                : '<li>' . $link($i, $i) . '</li>';
        }
        $html .= $page < $totalPages
            ? '<li>' . $link($page + 1, '<i class="fas fa-chevron-right"></i>', 'Trang sau') . '</li>'
            : '<li class="disabled"><span><i class="fas fa-chevron-right"></i></span></li>';
        $html .= '</ul></nav>';
        return $html;
    }

    // ---------- Khối trống ----------
    function ad_empty($icon, $title, $text = '', $actionHtml = '')
    {
        return '<div class="ad-empty"><i class="fas ' . $icon . '"></i>'
            . '<div class="ad-empty-title">' . ad_e($title) . '</div>'
            . ($text ? '<p>' . ad_e($text) . '</p>' : '')
            . $actionHtml . '</div>';
    }
}
