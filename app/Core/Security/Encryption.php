<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Support\Env;
use RuntimeException;

final class Encryption
{
    private const CIPHER = 'aes-256-gcm';

    public static function encrypt(string $plaintext): string
    {
        $key = self::key();
        $iv = random_bytes(openssl_cipher_iv_length(self::CIPHER));
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Encryption failed.');
        }

        return base64_encode(json_encode([
            'v' => 1,
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
            'data' => base64_encode($ciphertext),
        ], JSON_THROW_ON_ERROR));
    }

    public static function decrypt(string $payload): string
    {
        $decoded = json_decode(base64_decode($payload, true) ?: '', true);

        if (!is_array($decoded) || ($decoded['v'] ?? null) !== 1) {
            throw new RuntimeException('Invalid encrypted payload.');
        }

        $plaintext = openssl_decrypt(
            base64_decode((string) $decoded['data'], true),
            self::CIPHER,
            self::key(),
            OPENSSL_RAW_DATA,
            base64_decode((string) $decoded['iv'], true),
            base64_decode((string) $decoded['tag'], true),
        );

        if ($plaintext === false) {
            throw new RuntimeException('Decryption failed.');
        }

        return $plaintext;
    }

    private static function key(): string
    {
        $encoded = (string) Env::get('APP_KEY', '');

        if ($encoded === '') {
            throw new RuntimeException('APP_KEY is not configured.');
        }

        $key = str_starts_with($encoded, 'base64:')
            ? base64_decode(substr($encoded, 7), true)
            : hash('sha256', $encoded, true);

        if (!is_string($key) || strlen($key) !== 32) {
            throw new RuntimeException('APP_KEY must resolve to a 32-byte key.');
        }

        return $key;
    }
}
