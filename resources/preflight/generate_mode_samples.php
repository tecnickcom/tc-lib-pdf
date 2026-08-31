<?php
/**
 * Generate one minimal PDF sample per supported PDF/A, PDF/X and PDF/UA mode.
 *
 * The samples carry the metadata every conformance mode requires, so that a
 * validator failure points at the library and not at the sample. Any warning
 * raised while building a sample fails the script.
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
    'pdfa1',
    'pdfa1a',
    'pdfa1b',
    'pdfa2',
    'pdfa2a',
    'pdfa2b',
    'pdfa2u',
    'pdfa3',
    'pdfa3a',
    'pdfa3b',
    'pdfa3u',
    'pdfx',
    'pdfx1a',
    'pdfx3',
    'pdfx4',
    'pdfx5',
    'pdfua',
    'pdfua1',
    'pdfua2',
];

$warnings = [];
\set_error_handler(static function (int $errno, string $errstr) use (&$warnings): bool {
    $warnings[] = '[' . $errno . '] ' . $errstr;
    return true;
}, E_USER_WARNING | E_USER_NOTICE | E_WARNING | E_NOTICE);

try {
    foreach ($modes as $mode) {
        $pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, $mode);

        $title = \strtoupper($mode);
        $pdf->setTitle('Conformance sample ' . $title);
        $pdf->setCreator('tc-lib-pdf preflight');
        $pdf->setAuthor('Tecnick.com LTD');
        $pdf->setSubject('Conformance sample for mode ' . $mode);
        $pdf->setLanguage('en-US');

        if (\in_array($mode, ['pdfx4', 'pdfx5'], true)) {
            // ISO 15930-7 and ISO 15930-8 require an embedded destination profile.
            $pdf->setSRGB(true);
            $pdf->setOutputIntent(
                identifier: 'sRGB IEC61966-2.1',
                info: 'sRGB IEC61966-2.1',
                condition: 'sRGB display condition',
            );
        }

        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);

        // The unordered list draws a vector bullet, which a tagged document has to
        // mark as an Artifact; the table exercises the tagged table roles.
        $html =
            '<h1>Conformance sample</h1>'
            . '<p>Mode: ' . $title . '</p>'
            . '<ul><li>first item</li><li>second item</li></ul>'
            . '<table border="1"><tr><th>Header</th></tr><tr><td>Cell</td></tr></table>';

        $pdf->addHTMLCell($html, 15, 20, 180);

        $rawPdf = $pdf->getOutPDFString();
        $outFile = $outDir . '/mode-' . $mode . '.pdf';
        if (\file_put_contents($outFile, $rawPdf) === false) {
            \fwrite(STDERR, "Unable to write sample file: {$outFile}\n");
            exit(1);
        }

        \fwrite(STDOUT, $mode . "\t" . $outFile . "\n");
    }
} finally {
    \restore_error_handler();
}

if ($warnings !== []) {
    \fwrite(STDERR, "Sample generation raised " . \count($warnings) . " warning(s):\n");
    foreach ($warnings as $warning) {
        \fwrite(STDERR, '  ' . $warning . "\n");
    }

    exit(1);
}
