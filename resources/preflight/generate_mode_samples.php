<?php
/**
 * Generate one minimal PDF sample per supported PDF/X and PDF/UA mode.
 *
 * Usage:
 *   php resources/preflight/generate_mode_samples.php [output-directory]
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

define('K_PATH_FONTS', (string) \realpath(__DIR__ . '/../../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$outDir = $argv[1] ?? (__DIR__ . '/../../target/preflight');
if (!\is_dir($outDir) && !\mkdir($outDir, 0775, true) && !\is_dir($outDir)) {
    \fwrite(STDERR, "Unable to create output directory: {$outDir}\n");
    exit(1);
}

$modes = [
    'pdfx',
    'pdfx1a',
    'pdfx3',
    'pdfx4',
    'pdfx5',
    'pdfua',
    'pdfua1',
    'pdfua2',
];

foreach ($modes as $mode) {
    $pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, $mode);
    $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
    $page = $pdf->addPage();
    $pdf->page->addContent($font['out']);

    $title = \strtoupper($mode);
    $pdf->addHTMLCell('<h1>Conformance sample</h1><p>Mode: ' . $title . '</p>', 15, 20, 180);

    $rawPdf = $pdf->getOutPDFString();
    $outFile = $outDir . '/mode-' . $mode . '.pdf';
    if (\file_put_contents($outFile, $rawPdf) === false) {
        \fwrite(STDERR, "Unable to write sample file: {$outFile}\n");
        exit(1);
    }

    \fwrite(STDOUT, $mode . "\t" . $outFile . "\n");
}
