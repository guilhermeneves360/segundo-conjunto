<?php

require_once __DIR__ . '/../utils/Response.php';

class ExportService
{
    public static function csv(array $rows, array $headers, string $filename)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    public static function pdf(array $trips)
    {
        $lines = [];
        $lines[] = '%PDF-1.3';
        $content = "TravelPlan Pro - Relatório de Viagens\n\n";
        foreach ($trips as $trip) {
            $content .= sprintf("Destino: %s\nUsuário: %s\nIda: %s\nVolta: %s\nOrçamento: R$ %s\n\n",
                $trip['destination'],
                $trip['owner_name'] ?? $trip['name'] ?? 'N/A',
                $trip['start_date'],
                $trip['end_date'],
                number_format($trip['budget'], 2, ',', '.')
            );
        }
        $stream = <<<'EOT'
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>
endobj
4 0 obj
<< /Length %d >>
stream
BT /F1 12 Tf 50 800 Td (%s) Tj ET
endstream
endobj
5 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>
endobj
xref
0 6
0000000000 65535 f 
0000000010 00000 n 
0000000060 00000 n 
0000000120 00000 n 
0000000250 00000 n 
0000000350 00000 n 
trailer
<< /Root 1 0 R /Size 6 >>
startxref
%d
%%%%EOF
EOT;
        $contentText = addslashes(str_replace('\n', '\n', $content));
        $contentText = preg_replace('/([\\()])/', '\\$1', $content);
        $raw = sprintf($stream, strlen($content), str_replace('\n', ') Tj\n(', $contentText), 0);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="relatorio_viagens.pdf"');
        echo $raw;
        exit;
    }
}
