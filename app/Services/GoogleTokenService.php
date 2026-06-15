<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class GoogleTokenService
{
    public function verify(string $credential): array
    {
        $clientId = trim((string) config('services.google.client_id', ''));
        if ($clientId === '') {
            throw new RuntimeException('Google sign-in is not configured yet.');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = array_pad(explode('.', $credential), 3, '');
        if ($encodedHeader === '' || $encodedPayload === '' || $encodedSignature === '') {
            throw new RuntimeException('Invalid Google credential payload.');
        }

        $header = json_decode((string) $this->base64UrlDecode($encodedHeader), true);
        $payload = json_decode((string) $this->base64UrlDecode($encodedPayload), true);
        $signature = $this->base64UrlDecode($encodedSignature);

        if (!is_array($header) || !is_array($payload) || !is_string($signature)) {
            throw new RuntimeException('Unable to decode Google credential.');
        }

        if (($header['alg'] ?? '') !== 'RS256' || empty($header['kid'])) {
            throw new RuntimeException('Unsupported Google credential algorithm.');
        }

        $audience = (string) ($payload['aud'] ?? '');
        $issuer = (string) ($payload['iss'] ?? '');
        $expiresAt = (int) ($payload['exp'] ?? 0);
        $issuedAt = (int) ($payload['iat'] ?? 0);

        if ($audience !== $clientId) {
            throw new RuntimeException('Google credential audience mismatch.');
        }

        if (!in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw new RuntimeException('Google credential issuer is invalid.');
        }

        if ($expiresAt <= time() || ($issuedAt > 0 && $issuedAt > time() + 60)) {
            throw new RuntimeException('Google credential has expired.');
        }

        $certificates = $this->loadCertificates();
        $certificate = $certificates[(string) $header['kid']] ?? null;
        if (!is_string($certificate) || $certificate === '') {
            throw new RuntimeException('Google certificate for this sign-in token is unavailable.');
        }

        $signedData = $encodedHeader . '.' . $encodedPayload;
        $publicKey = openssl_pkey_get_public($certificate);
        if ($publicKey === false) {
            throw new RuntimeException('Unable to read Google public certificate.');
        }

        $verified = openssl_verify($signedData, $signature, $publicKey, OPENSSL_ALGO_SHA256);
        if ($verified !== 1) {
            throw new RuntimeException('Google credential signature verification failed.');
        }

        if (($payload['email_verified'] ?? false) !== true && ($payload['email_verified'] ?? '') !== 'true') {
            throw new RuntimeException('Google account email is not verified.');
        }

        return $payload;
    }

    private function loadCertificates(): array
    {
        $cacheDirectory = storage_path('cache');
        if (!is_dir($cacheDirectory)) {
            mkdir($cacheDirectory, 0775, true);
        }

        $cacheFile = $cacheDirectory . DIRECTORY_SEPARATOR . 'google-certs.json';
        if (is_file($cacheFile)) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($cached) && (int) ($cached['expires_at'] ?? 0) > time() && is_array($cached['certificates'] ?? null)) {
                return $cached['certificates'];
            }
        }

        $response = $this->fetchCertificates();
        if ($response['success']) {
            $payload = [
                'expires_at' => time() + max(300, (int) $response['ttl']),
                'certificates' => $response['certificates'],
            ];
            file_put_contents($cacheFile, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return $response['certificates'];
        }

        if (is_file($cacheFile)) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($cached) && is_array($cached['certificates'] ?? null)) {
                return $cached['certificates'];
            }
        }

        throw new RuntimeException('Unable to verify Google sign-in right now.');
    }

    private function fetchCertificates(): array
    {
        $url = 'https://www.googleapis.com/oauth2/v1/certs';

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return ['success' => false];
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
            curl_setopt($ch, CURLOPT_HEADER, true);

            $raw = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            curl_close($ch);

            if (!is_string($raw) || $status < 200 || $status >= 300) {
                return ['success' => false];
            }

            $headerBlock = substr($raw, 0, $headerSize);
            $body = substr($raw, $headerSize);
            $certificates = json_decode($body, true);

            return [
                'success' => is_array($certificates),
                'certificates' => is_array($certificates) ? $certificates : [],
                'ttl' => $this->cacheTtlFromHeaders($headerBlock),
            ];
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\n",
                'timeout' => 10,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        $certificates = is_string($body) ? json_decode($body, true) : null;

        return [
            'success' => is_array($certificates),
            'certificates' => is_array($certificates) ? $certificates : [],
            'ttl' => $this->cacheTtlFromHeaders(implode("\n", $http_response_header ?? [])),
        ];
    }

    private function cacheTtlFromHeaders(string $headers): int
    {
        if (preg_match('/max-age=(\d+)/i', $headers, $matches) === 1) {
            return (int) $matches[1];
        }

        return 3600;
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        if ($decoded === false) {
            throw new RuntimeException('Unable to decode token segment.');
        }

        return $decoded;
    }
}
