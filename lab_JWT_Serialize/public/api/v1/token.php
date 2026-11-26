<?php
declare(strict_types=1);
require __DIR__ . '/../../bootstrap.php';

use Lab\Core\Token;
use Lab\Models\LegacySession;
use Lab\Plugins\AuditPlugin;

header('Content-Type: application/json');

$t = new LegacySession();
$t->username = 'guest';
$t->plugin = new AuditPlugin();

echo json_encode(['token' => Token::issue($t)]);
