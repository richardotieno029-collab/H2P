<?php

// Utilities for image naming, resizing, and compression.
// Requires PHP GD extension.

function sanitizeFileName(string $filename): string {
    // Remove path information and any illegal characters
    $name = basename($filename);
    $name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $name);
    return $name;
}

function generateImageName(string $prefix, string $originalName): string {
    $sanitized = sanitizeFileName($originalName);
    $ext = pathinfo($sanitized, PATHINFO_EXTENSION);
    $ext = strtolower($ext);
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
        $ext = 'jpg';
    }
    return sprintf('%s_%s_%s.%s', $prefix, time(), bin2hex(random_bytes(4)), $ext);
}

function optimizeImageFile(string $sourcePath, string $destinationPath, int $maxWidth = 1200, int $quality = 70): bool {
    if (!function_exists('getimagesize')) {
        return false;
    }

    $info = @getimagesize($sourcePath);
    if (!$info || !isset($info['mime'])) {
        return false;
    }

    $mime = $info['mime'];
    $width = $info[0];
    $height = $info[1];

    $ratio = $width > 0 ? $height / $width : 1;
    $newWidth = $width;
    $newHeight = $height;

    if ($maxWidth > 0 && $width > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = (int)round($maxWidth * $ratio);
    }

    switch ($mime) {
        case 'image/jpeg':
        case 'image/pjpeg':
            if (!function_exists('imagecreatefromjpeg')) {
                return false;
            }
            $src = @imagecreatefromjpeg($sourcePath);
            break;

        case 'image/png':
            if (!function_exists('imagecreatefrompng')) {
                return false;
            }
            $src = @imagecreatefrompng($sourcePath);
            break;

        case 'image/gif':
            if (!function_exists('imagecreatefromgif')) {
                return false;
            }
            $src = @imagecreatefromgif($sourcePath);
            break;

        default:
            return false;
    }

    if (!$src) {
        return false;
    }

    $dst = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG/GIF
    if (in_array($mime, ['image/png', 'image/gif'], true)) {
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Write to temp file first, then move over destination
    $tmpPath = $destinationPath . '.tmp';

    $saved = false;
    switch ($mime) {
        case 'image/jpeg':
        case 'image/pjpeg':
            $saved = imagejpeg($dst, $tmpPath, $quality);
            break;
        case 'image/png':
            // PNG quality is 0 (no compression) to 9
            $pngQuality = (int)round((100 - $quality) / 10);
            $pngQuality = max(0, min(9, $pngQuality));
            $saved = imagepng($dst, $tmpPath, $pngQuality);
            break;
        case 'image/gif':
            $saved = imagegif($dst, $tmpPath);
            break;
    }

    imagedestroy($src);
    imagedestroy($dst);

    if (!$saved) {
        @unlink($tmpPath);
        return false;
    }

    if (!@rename($tmpPath, $destinationPath)) {
        // fallback to copy if rename fails (e.g., cross-filesystem)
        if (!@copy($tmpPath, $destinationPath)) {
            @unlink($tmpPath);
            return false;
        }
        @unlink($tmpPath);
    }

    return true;
}
