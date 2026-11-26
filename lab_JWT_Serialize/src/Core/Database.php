<?php
declare(strict_types=1);

namespace Lab\Core;

use mysqli;

final class Database
{
    public static function connect(): mysqli
    {
        $cfg = require __DIR__ . '/../../config/config.php';
        $db = new mysqli(
            $cfg['db']['host'],
            $cfg['db']['user'],
            $cfg['db']['pass'],
            $cfg['db']['name']
        );
        if ($db->connect_errno) {
            throw new \RuntimeException('Database unavailable');
        }
        $db->set_charset('utf8mb4');
        return $db;
    }
}
