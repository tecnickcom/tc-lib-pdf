<?php

declare(strict_types=1);

/**
 * E093_html_large_table.php
 *
 * A large HTML table written in one addHTMLCell() call, then the same rows written in
 * chunks, each one continuing below the previous one with getLastHTMLCellEnd(). The
 * last page lists the row and page counts and the peak memory of each method.
 *
 * @since       2026-10-10
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: local file reads (images, fonts, attachments) are restricted to an allowlist of
// trusted paths that covers this package tree, so run the examples in place. To read assets
// from other locations, list them in the 'allowedPaths' entry of the fileOptions constructor
// parameter (see E047_remote_resources_security.php).

// NOTE: run make fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 093');
$pdf->setTitle('HTML Large Table');
$pdf->setKeywords('TCPDF tc-lib-pdf example html table large memory chunk getLastHTMLCellEnd');
$pdf->setPDFFilename('093_html_large_table.pdf');

$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$rows = 1000;
$chunk = 100;
$margins = ['margin' => ['PT' => 10, 'PB' => 10, 'CT' => 10, 'CB' => 10]];

/**
 * Returns the table rows from $first to $last.
 */
$getRows = function (int $first, int $last): string {
    $html = '';
    for ($idx = $first; $idx <= $last; ++$idx) {
        $html .= '<tr><td>Row ' . $idx . '</td><td>Lorem ipsum</td><td>dolor sit amet</td><td>1,00</td></tr>';
    }

    return $html;
};

$head = '<tr style="background-color:#cccccc"><th>Row</th><th>Text</th><th>Text</th><th>Value</th></tr>';

// ----------
// One call for the whole table.
// ----------

$pdf->addPage($margins);
$pdf->page->addContent($font['out']);
$firstPage = $pdf->page->getPageId();
\gc_collect_cycles();
\memory_reset_peak_usage();
$start = \memory_get_usage();
$pdf->addHTMLCell(
    '<h2>One addHTMLCell() call</h2><table border="1" cellpadding="2"><thead>'
    . $head
    . '</thead>'
    . $getRows(1, $rows)
    . '</table>',
    15,
    10,
    180,
);
$singlePeak = \memory_get_peak_usage() - $start;
$singlePages = $pdf->page->getPageId() - $firstPage + 1;

// ----------
// The same rows in chunks: each call continues below the previous one.
// ----------

$pdf->addPage($margins);
$pdf->page->addContent($font['out']);
$firstPage = $pdf->page->getPageId();
\gc_collect_cycles();
\memory_reset_peak_usage();
$start = \memory_get_usage();
$pdf->addHTMLCell('<h2>addHTMLCell() calls of ' . $chunk . ' rows</h2>', 15, 10, 180);
for ($first = 1; $first <= $rows; $first += $chunk) {
    $end = $pdf->getLastHTMLCellEnd();
    $pdf->setCurrentPage($end['page']);
    $pdf->addHTMLCell(
        '<table border="1" cellpadding="2"><thead>'
        . $head
        . '</thead>'
        . $getRows($first, \min($rows, $first + $chunk - 1))
        . '</table>',
        15,
        $end['y'],
        180,
    );
}

$chunkPeak = \memory_get_peak_usage() - $start;
$chunkPages = $pdf->page->getPageId() - $firstPage + 1;

// ----------
// Summary.
// ----------

$pdf->addPage($margins);
$pdf->page->addContent($font['out']);
$summary = \sprintf(
    '<h2>Summary</h2><table border="1" cellpadding="3">'
    . '<tr><th>Method</th><th>Rows</th><th>Pages</th><th>Peak memory</th></tr>'
    . '<tr><td>one call</td><td>%d</td><td>%d</td><td>%.1f MB</td></tr>'
    . '<tr><td>calls of %d rows</td><td>%d</td><td>%d</td><td>%.1f MB</td></tr>'
    . '</table><p>Expected: the rows of both tables are numbered without gaps or overlaps; the chunked '
    . 'table continues right below each previous chunk. The peak memory depends on the PHP version.</p>',
    $rows,
    $singlePages,
    $singlePeak / 1_048_576,
    $chunk,
    $rows,
    $chunkPages,
    $chunkPeak / 1_048_576,
);
$pdf->addHTMLCell($summary, 15, 10, 180);

// =============================================================

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF(rawpdf: $rawpdf);
