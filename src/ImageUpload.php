<?php

declare(strict_types=1);

final class ImageUpload
{
    public const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_PIXELS = 25_000_000;
    private const MIN_EDGE = 200;
    private const MAX_EDGE = 2000;
    private const QUALITY = 82;

    public static function files(string $field): array
    {
        $raw = $_FILES[$field] ?? null;
        if (!is_array($raw) || !isset($raw['error'])) {
            return [];
        }
        if (!is_array($raw['error'])) {
            $raw = array_map(static fn ($v) => [$v], $raw);
        }
        $files = [];
        foreach ($raw['error'] as $i => $error) {
            if ((int) $error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $files[] = [
                'name' => (string) ($raw['name'][$i] ?? ''),
                'tmp_name' => (string) ($raw['tmp_name'][$i] ?? ''),
                'error' => (int) $error,
                'size' => (int) ($raw['size'][$i] ?? 0),
            ];
        }
        return $files;
    }

    public static function process(array $file, int $maxBytes): array
    {
        $label = self::label($file['name']);

        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            throw new UploadException("$label is te groot.");
        }
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new UploadException("$label kon niet worden geüpload. Probeer het opnieuw.");
        }

        $size = filesize($file['tmp_name']);
        if ($size === false || $size === 0) {
            throw new UploadException("$label is leeg.");
        }
        if ($size > $maxBytes) {
            throw new UploadException("$label is groter dan " . (int) ($maxBytes / 1048576) . ' MB.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            throw new UploadException("$label is geen JPG, PNG of WebP-foto.");
        }

        $info = @getimagesize($file['tmp_name']);
        if ($info === false || ($info['mime'] ?? '') !== $mime) {
            throw new UploadException("$label is geen geldige afbeelding.");
        }
        [$width, $height] = $info;
        if ($width < self::MIN_EDGE || $height < self::MIN_EDGE) {
            throw new UploadException("$label is te klein (minimaal " . self::MIN_EDGE . ' pixels breed en hoog).');
        }
        if ($width * $height > self::MAX_PIXELS) {
            throw new UploadException("$label heeft te veel pixels.");
        }

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
            'image/png' => @imagecreatefrompng($file['tmp_name']),
            'image/webp' => @imagecreatefromwebp($file['tmp_name']),
        };
        if (!$image instanceof GdImage) {
            throw new UploadException("$label kon niet worden gelezen.");
        }

        imagepalettetotruecolor($image);

        if ($mime === 'image/jpeg') {
            $image = self::applyExifOrientation($image, $file['tmp_name']);
        }

        $image = self::limitSize($image);

        $target = BASE_PATH . '/storage/uploads/tmp/' . bin2hex(random_bytes(16)) . '.webp';
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $ok = imagewebp($image, $target, self::QUALITY);
        $result = [
            'path' => $target,
            'width' => imagesx($image),
            'height' => imagesy($image),
        ];
        imagedestroy($image);

        if (!$ok || !is_file($target)) {
            @unlink($target);
            throw new UploadException("$label kon niet worden opgeslagen.");
        }
        $result['bytes'] = (int) filesize($target);
        return $result;
    }

    public static function store(array $processed, string $directory): string
    {
        $name = bin2hex(random_bytes(16)) . '.webp';
        $dir = rtrim($directory, '/');
        if (!is_dir($dir) || !rename($processed['path'], $dir . '/' . $name)) {
            @unlink($processed['path']);
            throw new UploadException('Een foto kon niet worden opgeslagen.');
        }
        @chmod($dir . '/' . $name, 0644);
        return $name;
    }

    public static function discard(array $processed): void
    {
        if (isset($processed['path']) && is_file($processed['path'])) {
            @unlink($processed['path']);
        }
    }

    public static function cleanupTmp(int $maxAgeSeconds = 3600): void
    {
        foreach (glob(BASE_PATH . '/storage/uploads/tmp/*.webp') ?: [] as $file) {
            if (filemtime($file) < time() - $maxAgeSeconds) {
                @unlink($file);
            }
        }
    }

    public static function isSafeName(string $name): bool
    {
        return (bool) preg_match('/^(?:[a-f0-9]{32}|demo-[a-z0-9-]{1,60})\.webp$/', $name);
    }

    private static function applyExifOrientation(GdImage $image, string $path): GdImage
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }
        $exif = @exif_read_data($path);
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
        if ($rotated instanceof GdImage && $rotated !== $image) {
            imagedestroy($image);
            return $rotated;
        }
        return $image;
    }

    private static function limitSize(GdImage $image): GdImage
    {
        $w = imagesx($image);
        $h = imagesy($image);
        $longest = max($w, $h);
        if ($longest <= self::MAX_EDGE) {
            return $image;
        }
        $scale = self::MAX_EDGE / $longest;
        $scaled = imagescale($image, (int) round($w * $scale), (int) round($h * $scale), IMG_BICUBIC);
        if ($scaled instanceof GdImage) {
            imagedestroy($image);
            return $scaled;
        }
        return $image;
    }

    private static function label(string $name): string
    {
        $clean = mb_substr(preg_replace('/[^\p{L}\p{N} ._()-]/u', '', basename($name)) ?? '', 0, 60);
        return $clean !== '' ? "Bestand \"$clean\"" : 'Een bestand';
    }
}
