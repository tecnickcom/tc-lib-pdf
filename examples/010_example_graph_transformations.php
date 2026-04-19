<?php
/**
 * 010_example_graph_transformations.php
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
$pdf->setPDFFilename('010_example_graph_transformations.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent(false);

$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$page01 = $pdf->addPage(['format' => 'A4']);

$pdf->graph->setPageWidth($page01['width']);
$pdf->graph->setPageHeight($page01['height']);

$pdf->page->addContent($bfont['out']);

// =============================================================


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

// Scaling
$t1 = $pdf->graph->getBasicRect(50, 70, 40, 10, 'D', $rect_style);
$t1 .= $pdf->graph->getStartTransform();
// Scale by 150% centered by (50,80) which is the lower left corner of the rectangle
$t1 .= $pdf->graph->getScaling(1.5, 1.5, 50, 80);
$t1 .= $pdf->graph->getBasicRect(50, 70, 40, 10, 'D', $transform_style);
$t1 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t1);


//  Translation
$t2 = $pdf->graph->getBasicRect(125, 70, 40, 10, 'D');
$t2 .= $pdf->graph->getStartTransform();
// Translate 7 to the right, 5 to the bottom
$t2 .= $pdf->graph->getTranslation(7, 5);
$t2 .= $pdf->graph->getBasicRect(125, 70, 40, 10, 'D', $transform_style);
$t2 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t2);


// Rotation
$t3 = $pdf->graph->getBasicRect(70, 100, 40, 10, 'D');
$t3 .= $pdf->graph->getStartTransform();
// Rotate 20 degrees counter-clockwise centered by (70,110) which is the lower left corner of the rectangle
$t3 .= $pdf->graph->getRotation(20, 70, 110);
$t3 .= $pdf->graph->getBasicRect(70, 100, 40, 10, 'D', $transform_style);
$t3 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t3);


// Skewing
$t4 = $pdf->graph->getBasicRect(125, 100, 40, 10, 'D');
$t4 .= $pdf->graph->getStartTransform();
// skew 30 degrees along the x-axis centered by (125,110) which is the lower left corner of the rectangle
$t4 .= $pdf->graph->getSkewing(30, 0, 125, 110);
$t4 .= $pdf->graph->getBasicRect(125, 100, 40, 10, 'D', $transform_style);
$t4 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t4);


//  Mirroring Horizontally
$t5 = $pdf->graph->getBasicRect(70, 130, 40, 10, 'D');
$t5 .= $pdf->graph->getStartTransform();
// mirror horizontally with axis of reflection at x-position 70 (left side of the rectangle)
$t5 .= $pdf->graph->getHorizMirroring(70);
$t5 .= $pdf->graph->getBasicRect(70, 130, 40, 10, 'D', $transform_style);
$t5 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t5);


//  Mirroring Vertically
$t6 = $pdf->graph->getBasicRect(125, 130, 40, 10, 'D');
$t6 .= $pdf->graph->getStartTransform();
// mirror vertically with axis of reflection at y-position 140 (bottom side of the rectangle)
$t6 .= $pdf->graph->getVertMirroring(140);
$t6 .= $pdf->graph->getBasicRect(125, 130, 40, 10, 'D', $transform_style);
$t6 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t6);


//  Point Reflection
$t7 = $pdf->graph->getBasicRect(70, 160, 40, 10, 'D');
$t7 .= $pdf->graph->getStartTransform();
// point reflection at the lower left point of rectangle
$t7 .= $pdf->graph->getPointMirroring(70, 170);
$t7 .= $pdf->graph->getBasicRect(70, 160, 40, 10, 'D', $transform_style);
$t7 .= $pdf->graph->getStopTransform();
$pdf->page->addContent($t7);

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

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF($rawpdf);
