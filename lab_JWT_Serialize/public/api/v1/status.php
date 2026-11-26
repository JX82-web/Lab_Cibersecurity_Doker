<?php
declare(strict_types=1);
require __DIR__ . '/../../bootstrap.php';

use Lab\Core\Token;

header('Content-Type: application/json');

$raw = $_COOKIE['CYBER_TOKEN'] ?? '';
if ($raw === '') {
    http_response_code(401);
    echo json_encode(['error' => 'token required']);
    exit;
}

try {
    $decoded = Token::decode($raw);
    $username = is_object($decoded) && isset($decoded->username) ? $decoded->username : 'unknown';
    echo json_encode([
        'status' => 'ok',
        'user' => $username,
        'service' => 'cyber_site',
        'api' => 'v1'
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid legacy token']);
}
