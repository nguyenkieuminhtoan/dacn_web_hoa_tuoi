<?php
class MessageHelper
{
    // true: logMessage() in ra toast (assets/js/popup.js) thay vì khung alert Bootstrap.
    // Layout admin bật cờ này; trang khách hàng / login vẫn dùng alert như cũ.
    public static $useToast = false;

    public static function success($message)
    {
        $_SESSION['success'] = $message;
    }

    public static function error($message)
    {
        $_SESSION['error'] = $message;
    }

    public static function logMessage()
    {
        if (self::$useToast) {
            self::printToast();
            return;
        }

        if (isset($_SESSION['success'])) {
            echo "<div class='alert alert-success alert-dismissible fade show mt-2' role='alert'>
                {$_SESSION['success']}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger alert-dismissible fade show mt-2' role='alert'>
                {$_SESSION['error']}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
            unset($_SESSION['error']);
        }
    }

    // Đẩy thông báo vào hàng đợi GNPopupQueue, popup.js sẽ hiện thành toast
    private static function printToast()
    {
        $items = [];
        foreach (['success' => 'success', 'error' => 'error'] as $key => $type) {
            if (isset($_SESSION[$key])) {
                $items[] = ['type' => $type, 'message' => strip_tags((string) $_SESSION[$key])];
                unset($_SESSION[$key]);
            }
        }
        if (!$items) return;

        $json = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        // GNFlashError: báo cho admin.js biết vừa có lỗi từ server -> popup form điền lại dữ liệu đã nhập
        $flag = in_array('error', array_column($items, 'type'), true) ? 'window.GNFlashError = true;' : '';
        echo "<script>$flag(window.GNPopupQueue = window.GNPopupQueue || []).push.apply(window.GNPopupQueue, $json);</script>";
    }
}
