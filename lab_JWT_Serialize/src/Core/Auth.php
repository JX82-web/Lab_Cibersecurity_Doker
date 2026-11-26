<?php
declare(strict_types=1);

namespace Lab\Core;

use mysqli_sql_exception;
use RuntimeException;

final class Auth
{
    public const USERNAME_PATTERN = '/^[A-Za-z0-9_.-]{3,32}$/';
    public const PASSWORD_MIN = 8;
    public const PASSWORD_MAX = 128;

    public static function check(string $username, string $password): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT password FROM users WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            return false;
        }

        $stored = (string) $row['password'];
        // Account registrati: hash bcrypt. Account seed di init.sql: valore in chiaro (compatibilità).
        if (str_starts_with($stored, '$2y$')) {
            return password_verify($password, $stored);
        }
        return hash_equals($stored, $password);
    }

    /**
     * Valida e crea un nuovo account. Lancia RuntimeException con messaggio leggibile in caso di errore.
     */
    public static function register(string $username, string $password): void
    {
        if (!preg_match(self::USERNAME_PATTERN, $username)) {
            throw new RuntimeException('Username must be 3–32 characters: letters, digits, _ . -');
        }
        $len = strlen($password);
        if ($len < self::PASSWORD_MIN || $len > self::PASSWORD_MAX) {
            throw new RuntimeException('Password must be 8–128 characters.');
        }

        $db = Database::connect();
        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $db->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
            $stmt->bind_param('ss', $username, $hash);
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) { // duplicate key
                throw new RuntimeException('This username is already taken.');
            }
            throw new RuntimeException('Registration is temporarily unavailable.');
        }
    }
}
