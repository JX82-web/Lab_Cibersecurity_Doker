<?php
declare(strict_types=1);

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'cyber_db',
        'name' => getenv('DB_NAME') ?: 'cyber_site',
        'user' => getenv('DB_USER') ?: 'cyber',
        'pass' => getenv('DB_PASS') ?: 'cyber_lab_password',
    ],
];
