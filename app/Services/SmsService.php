<?php

declare(strict_types=1);

namespace App\Services;

final class SmsService
{
    public function isConfigured(): bool
    {
        return trim((string) config('services.sms.gateway_url', '')) !== '';
    }

    public function sendOtp(string $phone, string $otp): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SMS gateway is not configured yet. Please use email OTP or Google sign-in for now.',
            ];
        }

        $message = sprintf(
            '%s login code is %s. It expires in %d minutes.',
            (string) config('app.name', 'ClinicFlow'),
            $otp,
            (int) config('services.otp.ttl_minutes', 10)
        );

        $body = strtr((string) config('services.sms.body_template', ''), [
            '{{phone}}' => $phone,
            '{{otp}}' => $otp,
            '{{message}}' => $message,
            '{{app_name}}' => (string) config('app.name', 'ClinicFlow'),
        ]);

        $response = $this->sendRequest(
            (string) config('services.sms.gateway_url', ''),
            (string) config('services.sms.gateway_method', 'POST'),
            (array) config('services.sms.gateway_headers', []),
            $body
        );

        if ($response['success']) {
            return [
                'success' => true,
                'message' => 'OTP sent to mobile.',
            ];
        }

        return [
            'success' => false,
            'message' => $response['message'],
        ];
    }

    private function sendRequest(string $url, string $method, array $headers, string $body): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return ['success' => false, 'message' => 'Unable to initialize SMS gateway connection.'];
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);

            if ($method !== 'GET') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $raw = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($raw !== false && $status >= 200 && $status < 300) {
                return ['success' => true, 'message' => 'SMS gateway request completed.'];
            }

            return ['success' => false, 'message' => $error !== '' ? $error : 'SMS gateway returned HTTP ' . $status . '.'];
        }

        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $method === 'GET' ? null : $body,
                'timeout' => 15,
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        $statusLine = $http_response_header[0] ?? '';
        if ($raw !== false && preg_match('#\s(2\d\d)\s#', $statusLine) === 1) {
            return ['success' => true, 'message' => 'SMS gateway request completed.'];
        }

        return ['success' => false, 'message' => 'SMS gateway request failed.'];
    }
}
