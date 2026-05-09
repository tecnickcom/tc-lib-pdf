<?php
/**
 * E033_geometric_transformations.php
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
$pdf->setSubject('tc-lib-pdf example: 033');
$pdf->setTitle('Geometric Transformations');
$pdf->setKeywords('TCPDF tc-lib-pdf transformations rotate scale translate skew matrix');
$pdf->setPDFFilename('033_geometric_transformations.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont1 = $pdf->font->insert($pdf->pon, 'helvetica', '', 8);

// test images directory
$imgdir = \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-image/test/images/');

$page01 = $pdf->addPage();
$pdf->setBookmark('Transformations', '', 1);

// Document title and explanation
$titleHtml = <<<HTML
<h1 style="font-size:16pt; color:#003366;">Geometric Transformations</h1>
<p style="font-size:9pt; color:#333333;">
This example demonstrates the geometric transformation operations available in tc-lib-pdf:
<b>scaling</b>, <b>translation</b>, <b>rotation</b>, <b>skewing</b>, <b>mirroring</b>,
and combined <b>affine transformations.</b>
Each operation is shown with a reference rectangle (black) and the transformed result (red).
</p>
HTML;

$pdf->addHTMLCell(
    $titleHtml,
    15, // float $posx
    15, // float $posy
    180, // float $width
);

// Geometric Transformations

$rect_style = [
    'lineWidth' => 0.3,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => 'black',
    'fillColor' => 'black',
];

$transform_style = [
    'lineWidth' => 0.3,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => 'red',
    'fillColor' => 'red',
];

$pdf->page->addContent($pdf->getTextCell('Scaling: 150% around (50, 80)', 50, 60, 65, 6, valign: 'T', halign: 'L', drawcell: false));

// Scaling
$t1 = $pdf->graph->getBasicRect(50, 70, 40, 10, 'D', $rect_style);
$t1 .= $pdf->graph->getStartTransform();
// Scale by 150% centered by (50,80) which is the lower left corner of the rectangle
$t1 .= $pdf->graph->getScaling(1.5, 1.5, 50, 80);
$t1 .= $pdf->graph->getBasicRect(50, 70, 40, 10, 'D', $transform_style);
$t1 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t1);


$pdf->page->addContent($pdf->getTextCell('Translation: +7 x, +5 y', 125, 66, 65, 6, valign: 'T', halign: 'L', drawcell: false));


//  Translation
$t2 = $pdf->graph->getBasicRect(125, 70, 40, 10, 'D');
$t2 .= $pdf->graph->getStartTransform();
// Translate 7 to the right, 5 to the bottom
$t2 .= $pdf->graph->getTranslation(7, 5);
$t2 .= $pdf->graph->getBasicRect(125, 70, 40, 10, 'D', $transform_style);
$t2 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t2);


$pdf->page->addContent($pdf->getTextCell('Rotation: 20 degrees around (70, 110)', 50, 112, 70, 6, valign: 'T', halign: 'L', drawcell: false));


// Rotation
$t3 = $pdf->graph->getBasicRect(70, 100, 40, 10, 'D');
$t3 .= $pdf->graph->getStartTransform();
// Rotate 20 degrees counter-clockwise centered by (70,110) which is the lower left corner of the rectangle
$t3 .= $pdf->graph->getRotation(20, 70, 110);
$t3 .= $pdf->graph->getBasicRect(70, 100, 40, 10, 'D', $transform_style);
$t3 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t3);


$pdf->page->addContent($pdf->getTextCell('Skewing: 30 degrees on x-axis', 125, 96, 65, 6, valign: 'T', halign: 'L', drawcell: false));


// Skewing
$t4 = $pdf->graph->getBasicRect(125, 100, 40, 10, 'D');
$t4 .= $pdf->graph->getStartTransform();
// skew 30 degrees along the x-axis centered by (125,110) which is the lower left corner of the rectangle
$t4 .= $pdf->graph->getSkewing(30, 0, 125, 110);
$t4 .= $pdf->graph->getBasicRect(125, 100, 40, 10, 'D', $transform_style);
$t4 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t4);


$pdf->page->addContent($pdf->getTextCell('Horizontal mirror at x = 70', 50, 126, 65, 6, valign: 'T', halign: 'L', drawcell: false));


//  Mirroring Horizontally
$t5 = $pdf->graph->getBasicRect(70, 130, 40, 10, 'D');
$t5 .= $pdf->graph->getStartTransform();
// mirror horizontally with axis of reflection at x-position 70 (left side of the rectangle)
$t5 .= $pdf->graph->getHorizMirroring(70);
$t5 .= $pdf->graph->getBasicRect(70, 130, 40, 10, 'D', $transform_style);
$t5 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t5);


$pdf->page->addContent($pdf->getTextCell('Vertical mirror at y = 140', 125, 126, 65, 6, valign: 'T', halign: 'L', drawcell: false));


//  Mirroring Vertically
$t6 = $pdf->graph->getBasicRect(125, 130, 40, 10, 'D');
$t6 .= $pdf->graph->getStartTransform();
// mirror vertically with axis of reflection at y-position 140 (bottom side of the rectangle)
$t6 .= $pdf->graph->getVertMirroring(140);
$t6 .= $pdf->graph->getBasicRect(125, 130, 40, 10, 'D', $transform_style);
$t6 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t6);


$pdf->page->addContent($pdf->getTextCell('Point reflection at (70, 170)', 50, 156, 65, 6, valign: 'T', halign: 'L', drawcell: false));


//  Point Reflection
$t7 = $pdf->graph->getBasicRect(70, 160, 40, 10, 'D');
$t7 .= $pdf->graph->getStartTransform();
// point reflection at the lower left point of rectangle
$t7 .= $pdf->graph->getPointMirroring(70, 170);
$t7 .= $pdf->graph->getBasicRect(70, 160, 40, 10, 'D', $transform_style);
$t7 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t7);

$pdf->page->addContent($pdf->getTextCell('Line reflection through (120, 170), angle -20 degrees', 125, 156, 70, 6, valign: 'T', halign: 'L', drawcell: false));

//  Mirroring against a straigth line described by a point (120, 120) and an angle -20°
$angle = -20;
$px = 120;
$py = 170;

// just for visualisation: the straight line to mirror against
$t8 = $pdf->graph->getLine($px - 1, $py - 1, $px + 1, $py + 1, [
    'lineColor' => 'green',
]);
$t8 .= $pdf->graph->getLine($px - 1, $py + 1, $px + 1, $py - 1, [
    'lineColor' => 'green',
]);
$t8 .= $pdf->graph->getStartTransform();
$t8 .= $pdf->graph->getRotation($angle, $px, $py);
$t8 .= $pdf->graph->getLine($px - 5, $py, $px + 60, $py, [
    'lineColor' => 'green',
]);
$t8 .= $pdf->graph->getStopTransform();
$t8 .= $pdf->graph->getBasicRect(125, 160, 40, 10, 'D', $rect_style);
$t8 .= $pdf->graph->getStartTransform();
// mirror against the straight line
$t8 .= $pdf->graph->getReflection($angle, $px, $py);
$t8 .= $pdf->graph->getBasicRect(125, 160, 40, 10, 'D', $transform_style);
$t8 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t8);

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
