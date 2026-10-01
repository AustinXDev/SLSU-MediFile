<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/init.php';

$signaturePath = $_GET['path'] ?? '';

if ($signaturePath === '') {
    http_response_code(400);
    exit('Signature path is required.');
}

$signaturePath = ltrim($signaturePath, '/');

/*
|--------------------------------------------------------------------------
| Only allow signature files
|--------------------------------------------------------------------------
*/

if (
    str_contains($signaturePath, '..') ||
    !str_starts_with($signaturePath, 'storage/private/signatures/')
) {
    http_response_code(403);
    exit('Invalid signature path.');
}

/*
|--------------------------------------------------------------------------
| Resolve the actual filesystem path
|--------------------------------------------------------------------------
*/

$filePath = ROOT_PATH . '/App/' . $signaturePath;

if (!is_file($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    exit('Signature not found.');
}

/*
|--------------------------------------------------------------------------
| Verify PNG
|--------------------------------------------------------------------------
*/

$imageInfo = @getimagesize($filePath);

if (
    $imageInfo === false ||
    ($imageInfo['mime'] ?? '') !== 'image/png'
) {
    http_response_code(404);
    exit('Invalid signature image.');
}

/*
|--------------------------------------------------------------------------
| Output image
|--------------------------------------------------------------------------
*/

header('Content-Type: image/png');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');

readfile($filePath);
exit;
