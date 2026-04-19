<?php
/**
 * 011_example_barcodes.php
 *
 * @since       2026-04-19
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
require(__DIR__ . '/../vendor/autoload.php');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm', // string $unit = 'mm',
    true, // bool $isunicode = true,
    false, // bool $subsetfont = false,
    false, // bool $compress = true,
    '', // string $mode = '',
    null, // ?ObjEncrypt $objEncrypt = null,
);

// ----------

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 006');
$pdf->setTitle('Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example');
$pdf->setPDFFilename('011_example_barcodes.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent(false);

$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$page01 = $pdf->addPage(['format' => 'A4']);

$pdf->graph->setPageWidth($page01['width']);
$pdf->graph->setPageHeight($page01['height']);

$pdf->page->addContent($bfont['out']);

// =============================================================

$barcode_style = [
    'lineWidth' => 0,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => 'black',
    'fillColor' => 'black',
];

$barcode1 = $pdf->getBarcode(
    'QRCODE,H',
    'https://tcpdf.org',
    10,
    10,
    -1,
    -1,
    [0, 0, 0, 0],
    $barcode_style
);
$pdf->page->addContent($barcode1);

$barcode2 = $pdf->getBarcode(
    'IMB',
    '01234567094987654321-01234567891',
    10,
    80,
    -1,
    -2,
    [0, 0, 0, 0],
    $barcode_style
);
$pdf->page->addContent($barcode2);

// =============================================================

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF($rawpdf);
