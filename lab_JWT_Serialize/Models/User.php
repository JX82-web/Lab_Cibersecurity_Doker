<?php
declare(strict_types=1);

namespace Lab\Models;

final class User
{
    public function __construct(
        public string $username,
        public string $role
    ) {}
}
