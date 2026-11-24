<?php

require dirname(__DIR__, 2) . '/src/admin.php';

$stmt = Database::pdo()->prepare('SELECT file FROM quote_request_photos WHERE id = ?');
$stmt->execute([get_int('id')]);
$file = $stmt->fetchColumn();

$dir = realpath(BASE_PATH . '/storage/uploads/requests');
$path = is_string($file) && ImageUpload::isSafeName($file) ? realpath($dir . '/' . $file) : false;

if ($dir === false || $path === false || !str_starts_with($path, $dir . DIRECTORY_SEPARATOR) || !is_file($path)) {
    http_response_code(404);
    exit('Foto niet gevonden.');
}

header('Content-Type: image/webp');
header('Content-Length: ' . filesize($path));
header('Content-Disposition: inline; filename="aanvraag-foto.webp"');
header('Cache-Control: private, max-age=600');
header("Content-Security-Policy: default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox");
readfile($path);
