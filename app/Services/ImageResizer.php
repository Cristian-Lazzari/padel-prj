<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * Ridimensionamento immagini con GD (estensione già presente su hosting
 * condiviso): nessuna dipendenza aggiuntiva nel progetto.
 *
 * Estratto da AvatarService perché serve anche alle copertine dei tornei,
 * che hanno un lato massimo diverso.
 */
class ImageResizer
{
    /**
     * Ridimensiona mantenendo le proporzioni: il lato più lungo diventa
     * al massimo $maxSize. Restituisce il binario JPEG.
     */
    public function resizeToJpeg(UploadedFile $file, int $maxSize, int $quality = 85): string
    {
        $source = $this->createImage($file);

        $width = imagesx($source);
        $height = imagesy($source);
        $longest = max($width, $height);

        if ($longest > $maxSize) {
            $ratio = $maxSize / $longest;
            $newWidth = max(1, (int) round($width * $ratio));
            $newHeight = max(1, (int) round($height * $ratio));
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        // Sfondo bianco: il JPEG non supporta la trasparenza di png/webp.
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $white);

        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($source);

        ob_start();
        imagejpeg($canvas, null, $quality);
        $binary = ob_get_clean();

        imagedestroy($canvas);

        return $binary;
    }

    /**
     * Crea la risorsa GD dal file caricato, raddrizzando le foto scattate
     * da smartphone in base all'orientamento EXIF.
     */
    private function createImage(UploadedFile $file)
    {
        $path = $file->getRealPath();

        switch ($file->getMimeType()) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($path);
                $image = $this->applyExifOrientation($image, $path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($path);
                break;
            case 'image/webp':
                $image = function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false;
                break;
            default:
                $image = false;
        }

        if (! $image) {
            throw new \RuntimeException('Formato immagine non supportato');
        }

        return $image;
    }

    private function applyExifOrientation($image, string $path)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);

        if (! $exif || empty($exif['Orientation'])) {
            return $image;
        }

        switch ($exif['Orientation']) {
            case 3:
                return imagerotate($image, 180, 0);
            case 6:
                return imagerotate($image, -90, 0);
            case 8:
                return imagerotate($image, 90, 0);
        }

        return $image;
    }
}
