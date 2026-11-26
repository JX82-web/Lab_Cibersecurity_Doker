<?php
declare(strict_types=1);

namespace Lab\Core;

final class Token
{
    public static function issue(object $token): string
    {
        return base64_encode(serialize($token));
    }

    public static function decode(string $value): mixed
    {
        // INTENTIONAL LAB VULNERABILITY:
        // user-controlled serialized data is deserialized without a MAC/signature.
        return unserialize(base64_decode($value));
    }
}
