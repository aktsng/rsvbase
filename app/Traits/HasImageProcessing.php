<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasImageProcessing
{
    /**
     * 画像をWebPに変換し、200KB以下に圧縮して保存する
     */
    protected function processAndStoreImage(UploadedFile $file, string $directory): string
    {
        $extension = $file->getClientOriginalExtension();
        $image = null;

        // GDを使用して画像を読み込む
        if (\preg_match('/jpe?g/i', $extension)) {
            $image = \imagecreatefromjpeg($file->getRealPath());
        } elseif (\preg_match('/png/i', $extension)) {
            $image = \imagecreatefrompng($file->getRealPath());
            if ($image) {
                \imagepalettetotruecolor($image);
                \imagealphablending($image, true);
                \imagesavealpha($image, true);
            }
        } elseif (\preg_match('/webp/i', $extension) && \function_exists('imagecreatefromwebp')) {
            $image = \imagecreatefromwebp($file->getRealPath());
        }

        if (!$image) {
            // 読み込めない場合はそのまま保存（フォールバック）
            return $file->store($directory, 'public');
        }

        // 自然なサイズにリサイズ（大きすぎる場合のみ）
        $width = \imagesx($image);
        $height = \imagesy($image);
        $maxSize = 1200;
        if ($width > $maxSize || $height > $maxSize) {
            if ($width > $height) {
                $newWidth = $maxSize;
                $newHeight = (int) ($height * ($maxSize / $width));
            } else {
                $newHeight = $maxSize;
                $newWidth = (int) ($width * ($maxSize / $height));
            }
            $resizedImage = \imagecreatetruecolor($newWidth, $newHeight);
            \imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            \imagedestroy($image);
            $image = $resizedImage;
        }

        // 一時ファイルに保存し、200KB以下になるまでクオリティを調整
        $tempPath = \tempnam(\sys_get_temp_dir(), 'img_proc_');
        $quality = 80;
        $useWebp = \function_exists('imagewebp');

        do {
            if ($useWebp) {
                \imagewebp($image, $tempPath, $quality);
            } else {
                \imagejpeg($image, $tempPath, $quality);
            }
            $size = \filesize($tempPath);
            $quality -= 10;
        } while ($size > 200 * 1024 && $quality > 10);

        // Storageに保存
        $ext = $useWebp ? 'webp' : 'jpg';
        $fileName = $directory . '/' . Str::random(40) . '.' . $ext;
        Storage::disk('public')->put($fileName, \fopen($tempPath, 'r'));

        // 後始末
        \imagedestroy($image);
        \unlink($tempPath);

        return $fileName;
    }
}
