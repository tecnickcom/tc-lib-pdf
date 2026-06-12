<?php

/**
 * E077_html_table_header_px_unit.php
 *
 * @since       2026-06-12
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require __DIR__ . '/../vendor/autoload.php';

\define('OUTPUT_FILE', \realpath(__DIR__ . '/../target') . '/example.pdf');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
//
// The 'px' document unit maps one user unit to one PDF point, while CSS
// pixel lengths use the standard 96dpi ratio (1px = 0.75pt). This example
// verifies that replayed table headers on continuation pages keep the same
// column geometry as the table body regardless of the document unit
// (see https://github.com/tecnickcom/tc-lib-pdf/issues/224).
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'px',
    isunicode: true,
    subsetfont: false,
    compress: true,
    mode: '',
    objEncrypt: null,
);

// ----------

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 077');
$pdf->setTitle('HTML Table Header with PX Unit');
$pdf->setKeywords('TCPDF tc-lib-pdf html table thead header pixel px unit pagination');
$pdf->setPDFFilename('077_html_table_header_px_unit.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

// A small custom page so the table spans several pages.
$pageV01 = $pdf->addPage(['height' => 300, 'width' => 400]);

$bfont6 = $pdf->font->insert($pdf->pon, 'dejavusans', '', 6);

$pdf->page->addContent($bfont6['out']);

// =============================================================
// Table spanning multiple pages with header row replay.
// The replayed header rows on each continuation page must keep the
// exact per-column widths computed for the original table.

$tableRows = '';
for ($i = 1; $i <= 100; ++$i) {
    $tableRows .=
        '<tr>'
        . '<td>'
        . $i
        . '</td>'
        . '<td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>'
        . '<td style="text-align:right">'
        . \number_format($i * 12.34, 2)
        . '</td>'
        . '</tr>';
}

$tablehtml =
    '<h2>Table across pages (px unit)</h2>'
    . '<table border="1" cellpadding="3" cellspacing="0">'
    . '<thead>'
    . '<tr style="background-color:#cccccc">'
    . '<th>#</th><th>Description</th><th>Amount</th>'
    . '</tr>'
    . '</thead>'
    . $tableRows
    . '</table>';

$pdf->addHTMLCell(html: $tablehtml, posx: 20, posy: 20, width: 360);

// =============================================================

// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF(rawpdf: $rawpdf);

//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
