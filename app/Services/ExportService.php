<?php

declare(strict_types=1);

namespace App\Services;

final class ExportService
{
    public function csv(array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $contents = (string) stream_get_contents($handle);
        fclose($handle);

        return $contents;
    }

    public function excel(array $rows, string $title): string
    {
        if ($rows === []) {
            return '<table><tr><td>No data</td></tr></table>';
        }

        $headers = array_keys($rows[0]);
        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<table border="1"><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . e((string) $header) . '</th>';
        }
        $html .= '</tr>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($headers as $header) {
                $html .= '<td>' . e((string) ($row[$header] ?? '')) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table></body></html>';

        return $html;
    }

    public function pdf(array $rows, string $title): string
    {
        $lines = [$title, str_repeat('=', mb_strlen($title)), ''];
        if ($rows === []) {
            $lines[] = 'No data available.';
        } else {
            foreach ($rows as $index => $row) {
                $lines[] = 'Row ' . ($index + 1);
                foreach ($row as $key => $value) {
                    $lines[] = sprintf('%s: %s', $key, (string) $value);
                }
                $lines[] = '';
            }
        }

        return $this->simplePdf($lines);
    }

    private function simplePdf(array $lines): string
    {
        $content = "BT\n/F1 11 Tf\n50 780 Td\n14 TL\n";
        foreach ($lines as $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $content .= '(' . $escaped . ") Tj\nT*\n";
        }
        $content .= "ET";

        $objects = [];
        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $objects[] = '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj';
        $objects[] = '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj';
        $objects[] = '4 0 obj << /Length ' . strlen($content) . " >> stream\n" . $content . "\nendstream endobj";
        $objects[] = '5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= 'xref' . "\n";
        $pdf .= '0 ' . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= 'trailer << /Size ' . (count($objects) + 1) . ' /Root 1 0 R >>' . "\n";
        $pdf .= 'startxref' . "\n";
        $pdf .= $xrefOffset . "\n%%EOF";

        return $pdf;
    }
}
