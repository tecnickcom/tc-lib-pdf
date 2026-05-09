<?php
/**
 * E038_image_clipping.php
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
$pdf->setSubject('tc-lib-pdf example: 038');
$pdf->setTitle('Image Clipping');
$pdf->setKeywords('TCPDF tc-lib-pdf image clipping masks clipping paths');
$pdf->setPDFFilename('038_image_clipping.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont1 = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);


// test images directory
$imgdir = \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-image/test/images/');


// ----------

$page10 = $pdf->addPage();


$html = <<<HTML
<h1 style="font-size:16pt; color:#003366;">Image Clipping</h1>
<p style="font-size:9pt; color:#333333;">
Demonstrates how to apply a clipping mask to an image using a star polygon path.
</p>
HTML;

$pdf->addHTMLCell(
    $html,
    15,
    15,
    180,
);

// Clipping Mask

$posx = 80;
$posy = 100;

$cnz = $pdf->graph->getStartTransform();
$cnz .= $pdf->graph->getStarPolygon($posx, $posy, 40, 10, 3, 0, 'CNZ');
$clipimg = $pdf->image->add($imgdir . '/200x100_CMYK.jpg');
$cnz .= $pdf->image->getSetImage($clipimg, $posx - 40, $posy - 40, 80, 80, $page10['height']);
$cnz .= $pdf->graph->getStopTransform();

$pdf->page->addContent($cnz);


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
