<?php

function handleImageUpload(array $file, int $maxWidth = 1200, int $maxHeight = 750, int $maxSize = 5242880): array
{
    if (!isset($file['error'], $file['tmp_name'], $file['name'], $file['size'])) {
        return ['success' => false, 'message' => 'Invalid upload payload'];
    }

    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload failed'];
    }

    if ((int) $file['size'] <= 0 || (int) $file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds the maximum allowed limit'];
    }

    $tmpPath = (string) $file['tmp_name'];
    if (!is_uploaded_file($tmpPath)) {
        return ['success' => false, 'message' => 'Invalid uploaded file'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpPath);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mimeType])) {
        return ['success' => false, 'message' => 'Only JPG, PNG, and WEBP images are allowed'];
    }

    $imageInfo = getimagesize($tmpPath);
    if ($imageInfo === false) {
        return ['success' => false, 'message' => 'Invalid image file'];
    }

    $originalWidth = (int) $imageInfo[0];
    $originalHeight = (int) $imageInfo[1];

    if ($originalWidth <= 0 || $originalHeight <= 0) {
        return ['success' => false, 'message' => 'Invalid image dimensions'];
    }

    $ext = $allowed[$mimeType];
    $filename = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

    $projectRoot = dirname(__DIR__);
    $uploadDir = $projectRoot . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        return ['success' => false, 'message' => 'Unable to prepare upload directory'];
    }

    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    $needsResize = $originalWidth > $maxWidth || $originalHeight > $maxHeight;

    if (!$needsResize || !extension_loaded('gd')) {
        if (!move_uploaded_file($tmpPath, $targetPath)) {
            return ['success' => false, 'message' => 'Failed to store uploaded image'];
        }

        return ['success' => true, 'path' => 'assets/images/uploads/' . $filename];
    }

    switch ($mimeType) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($tmpPath);
            break;
        case 'image/png':
            $source = imagecreatefrompng($tmpPath);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($tmpPath);
            break;
        default:
            $source = false;
            break;
    }

    if ($source === false) {
        return ['success' => false, 'message' => 'Failed to process image'];
    }

    $scale = min($maxWidth / $originalWidth, $maxHeight / $originalHeight, 1);
    $newWidth = (int) floor($originalWidth * $scale);
    $newHeight = (int) floor($originalHeight * $scale);

    $destination = imagecreatetruecolor($newWidth, $newHeight);
    if ($destination === false) {
        imagedestroy($source);
        return ['success' => false, 'message' => 'Failed to resize image'];
    }

    if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 0, 0, 0, 127);
        imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
    }

    $resampled = imagecopyresampled(
        $destination,
        $source,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $originalWidth,
        $originalHeight
    );

    if (!$resampled) {
        imagedestroy($destination);
        imagedestroy($source);
        return ['success' => false, 'message' => 'Failed to resize image'];
    }

    $saved = false;
    if ($mimeType === 'image/jpeg') {
        $saved = imagejpeg($destination, $targetPath, 85);
    } elseif ($mimeType === 'image/png') {
        $saved = imagepng($destination, $targetPath, 6);
    } elseif ($mimeType === 'image/webp') {
        $saved = imagewebp($destination, $targetPath, 85);
    }

    imagedestroy($destination);
    imagedestroy($source);

    if (!$saved) {
        return ['success' => false, 'message' => 'Failed to save resized image'];
    }

    return ['success' => true, 'path' => 'assets/images/uploads/' . $filename];
}
