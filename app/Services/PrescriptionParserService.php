<?php

declare(strict_types=1);

namespace App\Services;

final class PrescriptionParserService
{
    public function analyze(?string $absolutePath, ?string $mimeType, string $recordType, string $manualText = ''): array
    {
        $manualText = trim($manualText);
        if ($manualText !== '') {
            return [
                'extracted_text' => $manualText,
                'medications' => $this->extractMedications($manualText),
                'ocr_status' => 'manual',
                'ocr_provider' => 'manual-entry',
                'ocr_error' => null,
            ];
        }

        if ($recordType !== 'prescription' || $absolutePath === null || !is_file($absolutePath)) {
            return [
                'extracted_text' => null,
                'medications' => [],
                'ocr_status' => 'not_requested',
                'ocr_provider' => null,
                'ocr_error' => null,
            ];
        }

        $localResult = $this->runLocalTesseract($absolutePath, $mimeType ?? '');
        if ($localResult['success']) {
            $text = trim((string) $localResult['text']);

            return [
                'extracted_text' => $text !== '' ? $text : null,
                'medications' => $this->extractMedications($text),
                'ocr_status' => $text !== '' ? 'completed' : 'failed',
                'ocr_provider' => 'tesseract',
                'ocr_error' => $text !== '' ? null : 'Local OCR returned no readable prescription text.',
            ];
        }

        $remoteResult = $this->runRemoteOcr($absolutePath, $mimeType ?? '');
        if ($remoteResult['attempted']) {
            $text = trim((string) ($remoteResult['text'] ?? ''));

            return [
                'extracted_text' => $text !== '' ? $text : null,
                'medications' => $this->extractMedications($text),
                'ocr_status' => $text !== '' ? 'completed' : 'failed',
                'ocr_provider' => $remoteResult['provider'] ?? 'ocr-api',
                'ocr_error' => $text !== '' ? null : ($remoteResult['error'] ?? 'OCR could not read the prescription clearly.'),
            ];
        }

        return [
            'extracted_text' => null,
            'medications' => [],
            'ocr_status' => 'not_requested',
            'ocr_provider' => null,
            'ocr_error' => $localResult['error'] ?? null,
        ];
    }

    private function runLocalTesseract(string $absolutePath, string $mimeType): array
    {
        if (!$this->isImageMimeType($mimeType) || !function_exists('shell_exec')) {
            return ['success' => false, 'error' => null];
        }

        $binary = @shell_exec('command -v tesseract 2>/dev/null');
        $binary = is_string($binary) ? trim($binary) : '';
        if ($binary === '') {
            return ['success' => false, 'error' => null];
        }

        $command = escapeshellarg($binary) . ' ' . escapeshellarg($absolutePath) . ' stdout --psm 6 2>/dev/null';
        $text = @shell_exec($command);
        if (!is_string($text)) {
            return ['success' => false, 'error' => 'Local OCR is unavailable on this server.'];
        }

        return [
            'success' => true,
            'text' => $text,
        ];
    }

    private function runRemoteOcr(string $absolutePath, string $mimeType): array
    {
        $apiKey = trim((string) config('services.prescription_ocr.api_key', ''));
        $enabled = (bool) config('services.prescription_ocr.enabled', false);
        $endpoint = trim((string) config('services.prescription_ocr.endpoint', ''));

        if (!$enabled || $apiKey === '' || $endpoint === '' || !function_exists('curl_init') || !function_exists('curl_file_create')) {
            return ['attempted' => false];
        }

        $ch = curl_init($endpoint);
        if ($ch === false) {
            return ['attempted' => true, 'error' => 'Unable to initialize the OCR connection.', 'provider' => 'ocr-api'];
        }

        $postFields = [
            'apikey' => $apiKey,
            'language' => (string) config('services.prescription_ocr.language', 'eng'),
            'OCREngine' => (string) config('services.prescription_ocr.engine', '2'),
            'file' => curl_file_create($absolutePath, $mimeType !== '' ? $mimeType : 'application/octet-stream', basename($absolutePath)),
        ];

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if (!is_string($raw) || $raw === '') {
            return [
                'attempted' => true,
                'error' => $error !== '' ? $error : 'The OCR API did not return a response.',
                'provider' => 'ocr-api',
            ];
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return [
                'attempted' => true,
                'error' => 'The OCR API returned an unreadable response.',
                'provider' => 'ocr-api',
            ];
        }

        $provider = 'ocr-api';
        if ($status < 200 || $status >= 300) {
            $message = is_array($payload['ErrorMessage'] ?? null)
                ? implode(' ', array_map('strval', $payload['ErrorMessage']))
                : (string) ($payload['ErrorMessage'] ?? ('OCR API returned HTTP ' . $status . '.'));

            return [
                'attempted' => true,
                'error' => trim($message) !== '' ? trim($message) : 'OCR API request failed.',
                'provider' => $provider,
            ];
        }

        $parsedResults = $payload['ParsedResults'] ?? [];
        $chunks = [];
        if (is_array($parsedResults)) {
            foreach ($parsedResults as $result) {
                $chunk = trim((string) ($result['ParsedText'] ?? ''));
                if ($chunk !== '') {
                    $chunks[] = $chunk;
                }
            }
        }

        $text = trim(implode("\n", $chunks));
        if ($text === '') {
            $message = is_array($payload['ErrorMessage'] ?? null)
                ? implode(' ', array_map('strval', $payload['ErrorMessage']))
                : (string) ($payload['ErrorMessage'] ?? 'The OCR API could not detect text in the image.');

            return [
                'attempted' => true,
                'error' => trim($message) !== '' ? trim($message) : 'The OCR API could not detect text in the image.',
                'provider' => $provider,
            ];
        }

        return [
            'attempted' => true,
            'text' => $text,
            'provider' => $provider,
        ];
    }

    private function extractMedications(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $results = [];
        $seen = [];

        foreach ($lines as $line) {
            $original = trim(preg_replace('/\s+/', ' ', $line) ?? '');
            if ($original === '' || mb_strlen($original) < 3) {
                continue;
            }

            $clean = preg_replace('/^(rx|tab|cap|tablet|capsule)\.?\s*/i', '', $original) ?? $original;
            if (!preg_match('/[A-Za-z]/', $clean)) {
                continue;
            }

            $lower = strtolower($clean);
            if ($this->looksAdministrative($lower)) {
                continue;
            }

            $dosage = null;
            if (preg_match('/\b\d+(?:\.\d+)?\s?(?:mg|mcg|g|ml|units?)\b/i', $clean, $match) === 1) {
                $dosage = trim($match[0]);
            }

            $isMedicationLike = $dosage !== null
                || preg_match('/\b(?:tablet|tab|capsule|cap|syrup|drops|cream|ointment|gel|spray|inj|injection|sos|od|bd|tid|hs)\b/i', $clean) === 1
                || preg_match('/^\d+[\).\-\s]+[A-Za-z]/', $clean) === 1;

            if (!$isMedicationLike) {
                continue;
            }

            $name = preg_replace('/^\d+[\).\-\s]+/', '', $clean) ?? $clean;
            $name = preg_replace('/\b\d+(?:\.\d+)?\s?(?:mg|mcg|g|ml|units?)\b.*$/i', '', $name) ?? $name;
            $name = preg_replace('/\b(?:tablet|tab|capsule|cap|syrup|drops|cream|ointment|gel|spray|inj|injection)\b.*$/i', '', $name) ?? $name;
            $name = trim((string) preg_replace('/[^A-Za-z0-9+\/\-\s]/', '', $name));
            $name = trim((string) preg_replace('/\s+/', ' ', $name));

            if ($name === '' || mb_strlen($name) < 2) {
                continue;
            }

            $instructions = null;
            if ($dosage !== null) {
                $position = stripos($clean, $dosage);
                if ($position !== false) {
                    $instructions = trim(substr($clean, $position + strlen($dosage)));
                }
            } else {
                $parts = preg_split('/\s{2,}| - /', $clean, 2) ?: [];
                $instructions = isset($parts[1]) ? trim((string) $parts[1]) : null;
            }

            $key = strtolower($name . '|' . ($dosage ?? ''));
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $results[] = [
                'name' => $name,
                'dosage' => $dosage,
                'instructions' => $instructions !== '' ? $instructions : null,
                'raw_line' => $original,
            ];
        }

        return $results;
    }

    private function looksAdministrative(string $line): bool
    {
        foreach ([
            'patient',
            'doctor',
            'clinic',
            'hospital',
            'date',
            'age',
            'gender',
            'address',
            'phone',
            'mobile',
            'diagnosis',
            'advice',
            'follow up',
            'follow-up',
        ] as $needle) {
            if (str_contains($line, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function isImageMimeType(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }
}
