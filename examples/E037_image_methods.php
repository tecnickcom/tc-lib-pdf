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

// NOTE: run make fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require __DIR__ . '/../vendor/autoload.php';

\define('OUTPUT_FILE', \realpath(__DIR__ . '/../target') . '/example.pdf');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    isunicode: true,
    subsetfont: false,
    compress: true,
    mode: '',
    objEncrypt: null,
);

// ----------

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 037');
$pdf->setTitle('Image Methods');
$pdf->setKeywords('TCPDF tc-lib-pdf image methods jpeg png svg placement scaling');
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

$pdf->addHTMLCell(html: $html, posx: 15, posy: 15, width: 180);

$posx = 5;
$posy = 40;

// Cell grid geometry: 5 columns, each sample rendered $cellw units wide.
$cellw = 40;
$rowh = 20;
$cols = 5;

// Sample images exercising different color models and transparency.
$samples = [
    '200x100_CMYK.jpg', // CMYK JPEG sample.
    '200x100_GRAY.jpg', // Grayscale JPEG sample.
    '200x100_GRAY.png', // Grayscale PNG sample.
    '200x100_INDEX16.png', // Indexed-color PNG with a 16-color palette.
    '200x100_INDEX256.png', // Indexed-color PNG with a 256-color palette.
    '200x100_INDEXALPHA.png', // Indexed-color PNG with alpha transparency.
    '200x100_RGB.jpg', // RGB JPEG sample.
    '200x100_RGB.png', // RGB PNG sample.
    '200x100_RGBALPHA.png', // RGB PNG with alpha transparency.
    '200x100_RGBICC.jpg', // RGB JPEG carrying an ICC color profile.
    '200x100_RGBICC.png', // RGB PNG carrying an ICC color profile.
    '200x100_RGBINT.png', // RGB PNG variant (alternate RGB image path).
];

// Add Images
//
// Each sample is rendered preserving its original aspect ratio: only the target
// width is provided and the matching height is computed directly from the
// intrinsic image size by image->getImageDimensionsByKey().
foreach ($samples as $index => $name) {
    $file = $imgdir . '/' . $name;

    // Import the image; add() returns the image instance ID used for placement.
    $iid = $pdf->image->add($file);

    // Resolve the cache key for the imported image.
    $key = $pdf->image->getKey($file);

    // Derive the rendered dimensions from the intrinsic image size: a single
    // target side (width) is given and the other (height) is computed to
    // preserve the original aspect ratio.
    $dim = $pdf->image->getImageDimensionsByKey($key, $cellw);

    $col = $index % $cols;
    $row = \intdiv($index, $cols);
    $imgx = $posx + ($col * $cellw);
    $imgy = $posy + ($row * $rowh);

    $out = $pdf->image->getSetImage($iid, $imgx, $imgy, $dim['width'], $dim['height'], $page01['height']);
    $pdf->page->addContent($out);
}

// getImageDimensionsByKey() supports additional resolution modes, for example:
//   $pdf->image->getImageDimensionsByKey($key);               // original pixel size
//   $pdf->image->getImageDimensionsByKey($key, 0, 30);        // derive width from height
//   $pdf->image->getImageDimensionsByKey($key, 40, 40, true); // scale to fit a 40x40 box

// =============================================================

// ----------
// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// ----------

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF(rawpdf: $rawpdf);

//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
