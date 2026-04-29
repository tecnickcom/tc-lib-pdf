<?php
/**
 * E041_layers_visibility.php
 *
 * @since       2017-05-08
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


\define('OUTPUT_FILE', \realpath(__DIR__ . '/../target') . '/example.pdf');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm', // string $unit = 'mm',
    true, // bool $isunicode = true,
    false, // bool $subsetfont = false,
    true, // bool $compress = true,
    '', // string $mode = '',
    null, // ?ObjEncrypt $objEncrypt = null,
);

// ----------


$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 041');
$pdf->setTitle('Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example');
$pdf->setPDFFilename('041_layers_visibility.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont1 = $pdf->font->insert($pdf->pon, 'helvetica', '', 14);


// test images directory
$imgdir = \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-image/test/images/');

// ----------

// Layers

$page1 = $pdf->addPage();

$pdf->page->addContent($pdf->getTextCell('Layers and Optional Content Groups', 15, 15, 180, 8));

$pdf->page->addContent($pdf->getTextCell('This page contains objects assigned to different visibility layers.', 15, 28, 180, 6));

$pdf->page->addContent($pdf->getTextCell('Always visible text', 15, 40, 120, 7, 0, 0, 'T', 'L', drawcell: true));

$lyrScreen = $pdf->newLayer('screen_only', ['view' => true], false, true, false);
$pdf->page->addContent($lyrScreen);
$pdf->page->addContent($pdf->getTextCell('Screen-only layer content', 15, 54, 120, 7, 0, 0, 'T', 'L', drawcell: true));
$pdf->page->addContent($pdf->closeLayer());

$lyrPrint = $pdf->newLayer('print_only', ['design' => true], true, false, false);
$pdf->page->addContent($lyrPrint);
$pdf->page->addContent($pdf->getTextCell('Print-only layer content', 15, 66, 120, 7, 0, 0, 'T', 'L', drawcell: true));
$pdf->page->addContent($pdf->closeLayer());

$pdf->page->addContent($pdf->getTextCell('Viewer support for layer toggles varies by PDF reader.', 15, 82, 180, 6));

// =============================================================

// ----------
// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// ----------

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF($rawpdf);
//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
