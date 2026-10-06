<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;
use DomainException;

class MediaStorage
{
    public function saveImage(UploadedFile $file, string $directory): string
    {
        if (!$file->isValid() || $file->getSize() > 2 * 1024 * 1024) throw new DomainException('Foto harus JPG/PNG dan berukuran maksimal 2 MB.');
        $size = @getimagesize($file->getTempName());
        if (!$size || !in_array($size[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true) || $size[0] * $size[1] > 16000000) {
            throw new DomainException('Foto tidak valid atau resolusinya terlalu besar (maksimal 16 megapiksel).');
        }
        $source = @imagecreatefromstring(file_get_contents($file->getTempName()));
        if (!$source) throw new DomainException('Foto tidak dapat dibaca.');
        $ratio = min(1, 1920 / max($size[0], $size[1]));
        $width = max(1, (int) round($size[0] * $ratio)); $height = max(1, (int) round($size[1] * $ratio));
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
        imagecopyresampled($image, $source, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
        try {
            $folder = $this->directory($directory, true);
            $filename = bin2hex(random_bytes(16)) . '.jpg';
            if (!imagejpeg($image, $folder . '/' . $filename, 85)) throw new \RuntimeException('Foto belum berhasil disimpan.');
            return $filename;
        } finally {
            imagedestroy($source); imagedestroy($image);
        }
    }

    public function deleteImage(string $directory, string $filename): bool
    {
        if (!preg_match('/\A[a-zA-Z0-9_-][a-zA-Z0-9_.-]*\.(?:jpg|jpeg|png|webp)\z/i', $filename) || str_contains($filename, '..')) return false;
        $folder = $this->directory($directory, false);
        $path = realpath($folder . '/' . $filename);
        return $path !== false && dirname($path) === $folder && is_file($path) && unlink($path);
    }

    private function directory(string $directory, bool $create): string
    {
        if (!preg_match('~\A(?:wisata/gallery/[1-9][0-9]*|berita)\z~', $directory)) throw new \InvalidArgumentException('Direktori media tidak valid.');
        $base = realpath(FCPATH . 'uploads');
        if ($base === false) throw new \RuntimeException('Folder uploads belum tersedia.');
        $folder = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $directory);
        if ($create && !is_dir($folder) && !mkdir($folder, 0755, true)) throw new \RuntimeException('Folder media tidak dapat dibuat.');
        $resolved = realpath($folder);
        if ($resolved === false) return $folder;
        if (!str_starts_with($resolved, $base . DIRECTORY_SEPARATOR)) throw new \RuntimeException('Folder media berada di luar uploads.');
        return $resolved;
    }
}
