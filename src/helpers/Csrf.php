<?php

class Csrf
{
    private const SESSION_KEY = 'csrf_tokens';
    private const TOKEN_LIFETIME = 3600; // 1 hour

    /**
     * Get a CSRF token for the current session, generating a new one if needed.
     */
    public static function token(): string
    {
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        $now = time();
        $generatedAt = $_SESSION[self::SESSION_KEY]['default_generated'] ?? 0;
        $token = $_SESSION[self::SESSION_KEY]['default_token'] ?? null;

        if (!$token || ($now - (int)$generatedAt) >= self::TOKEN_LIFETIME) {
            $token = bin2hex(random_bytes(32));
            $_SESSION[self::SESSION_KEY]['default_token'] = $token;
            $_SESSION[self::SESSION_KEY]['default_generated'] = $now;
        }

        return $token;
    }

    /**
     * Validate the submitted token.
     */
    public static function validate(?string $token): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY]['default_token'])) {
            return false;
        }

        $storedToken = $_SESSION[self::SESSION_KEY]['default_token'];

        if (!is_string($token) || !hash_equals($storedToken, $token)) {
            return false;
        }

        // Jangan hapus token setelah validasi untuk memungkinkan penggunaan berulang
        // Token akan kadaluarsa berdasarkan waktu (1 jam) bukan berdasarkan penggunaan
        return true;
    }

    /**
     * Helper to render hidden input.
     */
    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}