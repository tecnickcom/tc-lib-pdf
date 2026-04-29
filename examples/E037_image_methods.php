<?php
/**
 * E037_image_methods.php
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
$pdf->setSubject('tc-lib-pdf example: 037');
$pdf->setTitle('Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example');
$pdf->setPDFFilename('037_image_methods.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont1 = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);


// test images directory
$imgdir = \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-image/test/images/');

// ----------
// Add first page

$page01 = $pdf->addPage();

$html = <<<HTML
<h1 style="font-size:16pt; color:#003366;">Image Methods</h1>
<p style="font-size:9pt; color:#333333;">
This example shows how to place JPEG and PNG images with different color models and transparency.
</p>
HTML;

$pdf->addHTMLCell(
    $html,
    15,
    15,
    180,
);

$posx = 5;
$posy = 100;

// Add Images

// CMYK JPEG sample.
$iid01 = $pdf->image->add($imgdir . '/200x100_CMYK.jpg');
$iid01_out = $pdf->image->getSetImage($iid01, $posx + 0, $posy + 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid01_out);

// Grayscale JPEG sample.
$iid02 = $pdf->image->add($imgdir . '/200x100_GRAY.jpg');
$iid02_out = $pdf->image->getSetImage($iid02, $posx + 40, $posy + 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid02_out);

// Grayscale PNG sample.
$iid03 = $pdf->image->add($imgdir . '/200x100_GRAY.png');
$iid03_out = $pdf->image->getSetImage($iid03, $posx + 80, $posy + 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid03_out);

// Indexed-color PNG with a 16-color palette.
$iid04 = $pdf->image->add($imgdir . '/200x100_INDEX16.png');
$iid04_out = $pdf->image->getSetImage($iid04, $posx + 120, $posy + 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid04_out);

// Indexed-color PNG with a 256-color palette.
$iid05 = $pdf->image->add($imgdir . '/200x100_INDEX256.png');
$iid05_out = $pdf->image->getSetImage($iid05, $posx + 160, $posy + 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid05_out);

// Indexed-color PNG sample with alpha transparency.
$iid06 = $pdf->image->add($imgdir . '/200x100_INDEXALPHA.png');
$iid06_out = $pdf->image->getSetImage($iid06, $posx + 0, $posy + 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid06_out);

// RGB JPEG sample.
$iid07 = $pdf->image->add($imgdir . '/200x100_RGB.jpg');
$iid07_out = $pdf->image->getSetImage($iid07, $posx + 40, $posy + 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid07_out);

// RGB PNG sample.
$iid08 = $pdf->image->add($imgdir . '/200x100_RGB.png');
$iid08_out = $pdf->image->getSetImage($iid08, $posx + 80, $posy + 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid08_out);

// RGB PNG sample with alpha transparency.
$iid09 = $pdf->image->add($imgdir . '/200x100_RGBALPHA.png');
$iid09_out = $pdf->image->getSetImage($iid09, $posx + 120, $posy + 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid09_out);

// RGB JPEG sample carrying an ICC color profile.
$iid10 = $pdf->image->add($imgdir . '/200x100_RGBICC.jpg');
$iid10_out = $pdf->image->getSetImage($iid10, $posx + 160, $posy + 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid10_out);

// RGB PNG sample carrying an ICC color profile.
$iid11 = $pdf->image->add($imgdir . '/200x100_RGBICC.png');
$iid11_out = $pdf->image->getSetImage($iid11, $posx + 0, $posy + 40, 40, 20, $page01['height']);
$pdf->page->addContent($iid11_out);

// RGB PNG variant used to exercise the alternate RGB image path.
$iid12 = $pdf->image->add($imgdir . '/200x100_RGBINT.png');
$iid12_out = $pdf->image->getSetImage($iid12, $posx + 40, $posy + 40, 40, 20, $page01['height']);
$pdf->page->addContent($iid12_out);

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
