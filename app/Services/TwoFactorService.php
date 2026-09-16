<?php

namespace App\Services;

/**
 * Minimal RFC 6238 TOTP implementation (compatible with Google Authenticator,
 * Authy, etc.) — no external package required.
 */
class TwoFactorService
{
    private const DIGITS = 6;
    private const PERIOD = 30;

    public static function generateSecret(int $length = 20): string
    {
        return self::base32Encode(random_bytes($length));
    }

    public static function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4))))
            ->all();
    }

    public static function qrCodeUrl(string $secret, string $email, string $issuer = 'ISP Tickets'): string
    {
        $label = rawurlencode("{$issuer}:{$email}");
        $otpauth = "otpauth://totp/{$label}?secret={$secret}&issuer=" . rawurlencode($issuer) . "&digits=" . self::DIGITS . "&period=" . self::PERIOD;

        // Rendered client-side via a QR library; this URL is what gets encoded.
        return $otpauth;
    }

    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $timestamp = floor(time() / self::PERIOD);

        for ($i = -$window; $i <= $window; $i++) {
            if (self::generateCode($secret, $timestamp + $i) === $code) {
                return true;
            }
        }

        return false;
    }

    private static function generateCode(string $secret, int $timeStep): string
    {
        $key = self::base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeStep);

        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;

        $truncated = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        $code = $truncated % (10 ** self::DIGITS);
        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    private static function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binaryString = '';
        foreach (str_split($data) as $char) {
            $binaryString .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $output = '';
        foreach (str_split($binaryString, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $output .= $alphabet[bindec($chunk)];
        }

        return $output;
    }

    private static function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper($secret);
        $binaryString = '';

        foreach (str_split($secret) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) continue;
            $binaryString .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($binaryString, 8) as $chunk) {
            if (strlen($chunk) < 8) continue;
            $bytes .= chr(bindec($chunk));
        }

        return $bytes;
    }
}
