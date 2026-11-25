<?php
function uploadImage($fileInput, $tagetDir = __DIR__ . "/../uploads/")
{
    if (isset($fileInput) && $fileInput['error'] == 0) {
        if (!is_dir($tagetDir)) {
            mkdir($tagetDir, 0777, true);
        }
        $fileName = time() . '_' . basename($fileInput['name']);
        $targetFile = $tagetDir . $fileName;

        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        // Khớp với accept của form sản phẩm (jpeg, png, gif, webp)
        $fileAlow = ['jpg', 'png', 'jpeg', 'gif', 'webp'];

        if (in_array($fileType, $fileAlow)) {
            if (move_uploaded_file($fileInput['tmp_name'], $targetFile)) {
                return $fileName;
            }
        }
    }
    return null;
}

// URL ảnh sản phẩm cho trang khách hàng; chưa có ảnh (image_url NULL/rỗng) -> ảnh tạm
function productImageUrl($fileName, $placeholderSize = '400x500')
{
    if (empty($fileName)) {
        return 'https://placehold.co/' . $placeholderSize . '/fdf0f4/c2185b?text=Flower';
    }
    return 'uploads/' . $fileName;
}

function deleteImage($fileName, $tagetDir = __DIR__ . '/../uploads/')
{
    if (!empty($fileName)) {
        $filePath = $tagetDir . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
            return true;
        }
    }
    return false;
}
