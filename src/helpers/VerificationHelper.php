<?php

class VerificationHelper
{
    private const DEFAULT_SECRET = 'kombi-signature';

    private static function getSecret(): string
    {
        $envSecret = getenv('APP_SIGNATURE_KEY') ?: getenv('APP_KEY');
        return $envSecret ?: self::DEFAULT_SECRET;
    }

    private static function getBaseUrl(): string
    {
        $envUrl = getenv('APP_URL');
        if ($envUrl) {
            return rtrim($envUrl, '/');
        }

        if (!empty($_SERVER['HTTP_HOST'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            return $scheme . '://' . $_SERVER['HTTP_HOST'];
        }

        return '';
    }

    private static function buildSignature(int $evaluationId, ?string $updatedAt): string
    {
        $timestamp = $updatedAt ?: '0';
        $payload = $evaluationId . '|' . $timestamp;
        return hash_hmac('sha256', $payload, self::getSecret());
    }

    public static function generateEvaluationToken(int $evaluationId, ?string $updatedAt): string
    {
        return self::buildSignature($evaluationId, $updatedAt);
    }

    public static function verifyEvaluationToken(int $evaluationId, ?string $updatedAt, string $token): bool
    {
        if ($token === '') {
            return false;
        }
        $expected = self::buildSignature($evaluationId, $updatedAt);
        return hash_equals($expected, $token);
    }

    public static function generateEvaluationLink(int $evaluationId, ?string $updatedAt): string
    {
        $signature = self::generateEvaluationToken($evaluationId, $updatedAt);
        $base = self::getBaseUrl();
        $path = '/verification/evaluation?id=' . $evaluationId . '&token=' . urlencode($signature);
        return $base ? $base . $path : $path;
    }

    public static function generateFinalScoreLink(int $studentId, ?string $updatedAt): string
    {
        $signature = self::generateEvaluationToken($studentId, $updatedAt);
        $base = self::getBaseUrl();
        // Use shorter URL format to avoid QR code length issues
        // Just use the path without full URL - QR code scanner will append base URL
        $path = '/verification/final?id=' . $studentId . '&token=' . urlencode($signature);
        return $base ? $base . $path : $path;
    }
}
