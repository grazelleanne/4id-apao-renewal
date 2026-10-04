<?php
declare(strict_types=1);

final class PdfReport
{
    public static function download(string $filename, string $title, array $fields): never
    {
        $lines = [$title, 'APAO Renewal System', 'Generated: ' . date('d F Y'), ''];
        foreach ($fields as $label => $value) {
            $text = self::ansi((string) $label . ': ' . (string) ($value ?: '—'));
            foreach (explode("\n", wordwrap($text, 82, "\n", true)) as $line) {
                $lines[] = $line;
            }
        }
        $pdf = self::build($lines);
        $name = preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($filename)) ?: 'report.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . strlen($pdf));
        header('X-Content-Type-Options: nosniff');
        echo $pdf;
        exit;
    }

    private static function ansi(string $text): string
    {
        $converted = function_exists('iconv') ? iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text) : false;
        return $converted === false ? preg_replace('/[^\x20-\x7E]/', '?', $text) : $converted;
    }

    private static function build(array $lines): string
    {
        $groups = array_chunk($lines, 48);
        $font = 3 + count($groups) * 2;
        $objects = [1 => '<< /Type /Catalog /Pages 2 0 R >>'];
        $kids = [];
        foreach ($groups as $i => $group) {
            $page = 3 + $i * 2;
            $contentId = $page + 1;
            $kids[] = "{$page} 0 R";
            $stream = "BT\n/F1 10 Tf\n50 790 Td\n14 TL\n";
            foreach ($group as $lineNo => $line) {
                $line = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], (string) $line);
                $stream .= ($lineNo === 0 && $i === 0 ? '/F1 16 Tf ' : '/F1 10 Tf ') . "({$line}) Tj\nT*\n";
            }
            $stream .= "ET";
            $objects[$page] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 {$font} 0 R >> >> /Contents {$contentId} 0 R >>";
            $objects[$contentId] = '<< /Length ' . strlen($stream) . " >>\nstream\n{$stream}\nendstream";
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($groups) . ' >>';
        $objects[$font] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        ksort($objects);
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $size = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        for ($id = 1; $id < $size; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }
        return $pdf . "trailer << /Size {$size} /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }
}
