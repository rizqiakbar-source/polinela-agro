<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

class ImageUploader
{
    /**
     * Upload dan simpan berkas gambar dengan penamaan unik
     */
    public static function upload(UploadedFile $file, string $targetFolder = 'products', int $maxSizeKb = 5120): array
    {
        if (!$file->isValid()) {
            return [
                'success' => false,
                'message' => $file->getErrorString() . ' (' . $file->getError() . ')',
                'filename'=> null,
            ];
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        if (!in_array($file->getClientMimeType(), $allowedTypes)) {
            return [
                'success' => false,
                'message' => 'Format berkas tidak didukung. Harap unggah gambar JPG, PNG, atau WEBP.',
                'filename'=> null,
            ];
        }

        if ($file->getSizeByUnit('kb') > $maxSizeKb) {
            return [
                'success' => false,
                'message' => 'Ukuran berkas melebihi batas ' . ($maxSizeKb / 1024) . ' MB.',
                'filename'=> null,
            ];
        }

        $destination = FCPATH . 'assets/img/' . trim($targetFolder, '/') . '/';
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $newName = $file->getRandomName();
        $file->move($destination, $newName);

        return [
            'success'  => true,
            'message'  => 'Gambar berhasil diunggah.',
            'filename' => $newName,
            'path'     => 'assets/img/' . trim($targetFolder, '/') . '/' . $newName,
        ];
    }
}
