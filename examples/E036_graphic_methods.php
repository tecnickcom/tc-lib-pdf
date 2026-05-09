<?php
/**
 * E036_graphic_methods.php
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
$pdf->setSubject('tc-lib-pdf example: 036');
$pdf->setTitle('Graphic Methods');
$pdf->setKeywords('TCPDF tc-lib-pdf graphic methods lines curves polygons shapes');
$pdf->setPDFFilename('036_graphic_methods.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont1 = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);


// test images directory
$imgdir = \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-image/test/images/');

// ----------

$style1 = [
    'lineWidth' => 0.5,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [5, 2, 1, 2],
    'dashPhase' => 0,
    'lineColor' => 'red',
    'fillColor' => 'powderblue',
];

$style2 = [
    'lineWidth' => 0.5,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => 'green',
    'fillColor' => 'powderblue',
];

$style3 = [
    'lineWidth' => 1,
    'lineCap' => 'round',
    'lineJoin' => 'round',
    'dashArray' => [6, 2],
    'dashPhase' => 0,
    'lineColor' => 'blue',
    'fillColor' => 'powderblue',
];

$style4 = [
    'all' => [
        'lineWidth' => 0.5,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'miterLimit' => 0.5,
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => 'black',
        'fillColor' => 'aliceblue',
    ],
    // TOP
    0 => [
        'lineWidth' => 0.25,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => 'red',
        'fillColor' => 'powderblue',
    ],
    // RIGHT
    1 => [
        'lineWidth' => 0.25,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => 'green',
        'fillColor' => 'powderblue',
    ],
    // BOTTOM
    2 => [
        'lineWidth' => 0.50,
        'lineCap' => 'round',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => 'blue',
        'fillColor' => 'powderblue',
    ],
    // LEFT
    3 => [
        'lineWidth' => 0.75,
        'lineCap' => 'square',
        'lineJoin' => 'miter',
        'dashArray' => [6, 3, 2, 3],
        'dashPhase' => 0,
        'lineColor' => 'yellow',
        'fillColor' => 'powderblue',
    ],
];

$style5 = [
    'lineWidth' => 0.25,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => 'darkblue',
    'fillColor' => 'mistyrose',
];

$style6 = [
    'lineWidth' => 0.5,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [3, 3],
    'dashPhase' => 0,
    'lineColor' => 'green',
    'fillColor' => 'powderblue',
];

$style7 = [
    'lineWidth' => 0.5,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => 'darkorange',
    'fillColor' => 'palegreen',
];

// ----------

$page01 = $pdf->addPage();

$titleHtml = <<<HTML
<h1 style="font-size:16pt; color:#003366;">Graphic Methods</h1>
<p style="font-size:9pt; color:#333333;">
This example presents a compact visual overview of the drawing primitives available in tc-lib-pdf,
including pie sectors, ellipses, lines, rectangles, curves, polygons, regular polygons, star polygons,
rounded rectangles, and arrow styles.
</p>
HTML;

$pdf->addHTMLCell(
    $titleHtml,
    15,
    15,
    180,
);



$xc = 105;
$yc = 150;
$r = 50;

$pie1 = $pdf->graph->getPieSector($xc, $yc, $r, 20, 120, 'FD', $style5, 2);
$pdf->page->addContent($pie1);

$pie2 = $pdf->graph->getPieSector($xc, $yc, $r, 120, 250, 'FD', $style6, 2);
$pdf->page->addContent($pie2);

$pie3 = $pdf->graph->getPieSector($xc, $yc, $r, 250, 20, 'FD', $style7, 2);
$pdf->page->addContent($pie3);

// ----------

$page02 = $pdf->addPage();
$pdf->setBookmark('Ellipse', '', 1);

// center of ellipse
$xc = 100;
$yc = 100;
// X Y axis
$arc1 = $pdf->graph->getLine($xc - 50, $yc, $xc + 50, $yc, $style1);
$pdf->page->addContent($arc1);

$arc2 = $pdf->graph->getLine($xc, $yc - 50, $xc, $yc + 50, $style2);
$pdf->page->addContent($arc2);

// ellipse axis
$arc3 = $pdf->graph->getLine($xc - 50, $yc - 50, $xc + 50, $yc + 50, $style3);
$pdf->page->addContent($arc3);

$arc4 = $pdf->graph->getLine($xc - 50, $yc + 50, $xc + 50, $yc - 50, $style5);
$pdf->page->addContent($arc4);

// ellipse
$arc5 = $pdf->graph->getEllipse($xc, $yc, 30, 15, 45, 0, 360, 'D', $style6, 2);
$pdf->page->addContent($arc5);

// ellipse arc
$arc6 = $pdf->graph->getEllipse($xc, $yc, 30, 15, 45, 45, 90, 'D', $style7, 2);
$pdf->page->addContent($arc6);

// ----------

$page03 = $pdf->addPage();

// Line

$line1 = $pdf->graph->getLine(5, 10, 80, 30, $style1);
$pdf->page->addContent($line1);

$line2 = $pdf->graph->getLine(5, 10, 5, 30, $style2);
$pdf->page->addContent($line2);

$line3 = $pdf->graph->getLine(5, 10, 80, 10, $style3);
$pdf->page->addContent($line3);

// Rectangle

$rect1 = $pdf->graph->getRect(100, 10, 40, 20, 'DF', $style4);
$pdf->page->addContent($rect1);

$rect2 = $pdf->graph->getRect(145, 10, 40, 20, 'D', [
    'all' => $style3,
]);
$pdf->page->addContent($rect2);

// Curve

$curve1 = $pdf->graph->getCurve(5, 40, 30, 55, 70, 45, 60, 75, '', $style6);
$pdf->page->addContent($curve1);

$curve2 = $pdf->graph->getCurve(80, 40, 70, 75, 150, 45, 100, 75, 'F', $style6);
$pdf->page->addContent($curve2);

$curve3 = $pdf->graph->getCurve(140, 40, 150, 55, 180, 45, 200, 75, 'DF', $style6);
$pdf->page->addContent($curve3);

// Circle

$circle1 = $pdf->graph->getCircle(25, 105, 20);
$pdf->page->addContent($circle1);

$circle2 = $pdf->graph->getCircle(25, 105, 10, 90, 180, '', $style7);
$pdf->page->addContent($circle2);

$circle3 = $pdf->graph->getCircle(25, 105, 10, 270, 360, 'F');
$pdf->page->addContent($circle3);

$circle4 = $pdf->graph->getCircle(25, 105, 10, 270, 360, 'C', $style7);
$pdf->page->addContent($circle4);

// Ellipse

$ellipse1 = $pdf->graph->getEllipse(100, 103, 40, 20);
$pdf->page->addContent($ellipse1);

$ellipse2 = $pdf->graph->getEllipse(100, 105, 20, 10, 0, 90, 180, '', $style7);
$pdf->page->addContent($ellipse2);

$ellipse3 = $pdf->graph->getEllipse(100, 105, 20, 10, 0, 270, 360, 'DF', $style7);
$pdf->page->addContent($ellipse3);

$ellipse4 = $pdf->graph->getEllipse(175, 103, 30, 15, 45);
$pdf->page->addContent($ellipse4);

$ellipse5 = $pdf->graph->getEllipse(175, 105, 15, 7.50, 45, 90, 180, '', $style7);
$pdf->page->addContent($ellipse5);

$ellipse6 = $pdf->graph->getEllipse(175, 105, 15, 7.50, 45, 270, 360, 'F', $style7, 4);
$pdf->page->addContent($ellipse6);

// Polygon

$polygon1 = $pdf->graph->getPolygon([5, 135, 45, 135, 15, 165], 's');
$pdf->page->addContent($polygon1);

$polygon2 = $pdf->graph->getPolygon([60, 135, 80, 135, 80, 155, 70, 165, 50, 155], 'DF', [$style6, $style7, $style7, $style6, $style6]);
$pdf->page->addContent($polygon2);

$polygon3 = $pdf->graph->getPolygon([120, 135, 140, 135, 150, 155, 110, 155], 'D', [$style5, $style6, $style7, $style6]);
$pdf->page->addContent($polygon3);

$polygon4 = $pdf->graph->getPolygon([160, 135, 190, 155, 170, 155, 200, 160, 160, 165], 'DF', [
    'all' => $style6,
]);
$pdf->page->addContent($polygon4);

$polygon5 = $pdf->graph->getPolygon([80, 165, 90, 160, 100, 165, 110, 160, 120, 165, 130, 160, 140, 165], 'D', [
    'all' => $style1,
]);
$pdf->page->addContent($polygon5);

// Regular Polygon

$regpoly1 = $pdf->graph->getRegularPolygon(20, 190, 15, 6, 0, 'b', [
    'all' => $style6,
], 's', $style5);
$pdf->page->addContent($regpoly1);

$regpoly2 = $pdf->graph->getRegularPolygon(55, 190, 15, 6, 0, 's');
$pdf->page->addContent($regpoly2);

$regpoly3 = $pdf->graph->getRegularPolygon(55, 190, 10, 6, 45, 'DF', [$style6, $style5, $style7, $style5, $style7, $style7]);
$pdf->page->addContent($regpoly3);

$regpoly4 = $pdf->graph->getRegularPolygon(90, 190, 15, 3, 0, 'b', [
    'all' => $style5,
], 'F', $style6);
$pdf->page->addContent($regpoly4);

$regpoly5 = $pdf->graph->getRegularPolygon(125, 190, 15, 4, 30, 'b', [
    'all' => $style5,
], 's', $style1);
$pdf->page->addContent($regpoly5);

$regpoly6 = $pdf->graph->getRegularPolygon(160, 190, 15, 10, 0, 's');
$pdf->page->addContent($regpoly6);


// Star Polygon

$startpoly1 = $pdf->graph->getStarPolygon(20, 230, 15, 20, 3, 0, 's', [
    'all' => $style2,
], 'b', $style5);
$pdf->page->addContent($startpoly1);

$startpoly2 = $pdf->graph->getStarPolygon(55, 230, 15, 12, 5, 15, 's');
$pdf->page->addContent($startpoly2);

$startpoly3 = $pdf->graph->getStarPolygon(55, 230, 7, 12, 5, 45, 'b', [
    'all' => $style7,
], 'F', $style6);
$pdf->page->addContent($startpoly3);

$startpoly4 = $pdf->graph->getStarPolygon(90, 230, 15, 20, 6, 0, 's', [
    'all' => $style5,
], 'F', $style6);
$pdf->page->addContent($startpoly4);

$startpoly5 = $pdf->graph->getStarPolygon(125, 230, 15, 5, 2, 30, 's', [
    'all' => $style5,
], 's', $style6);
$pdf->page->addContent($startpoly5);

$startpoly6 = $pdf->graph->getStarPolygon(160, 230, 15, 10, 3, 0, 's');
$pdf->page->addContent($startpoly6);

$startpoly7 = $pdf->graph->getStarPolygon(160, 230, 7, 50, 26, 10, 's');
$pdf->page->addContent($startpoly7);

// Rounded Rectangle

$roundrect1 = $pdf->graph->getRoundedRect(5, 255, 40, 30, 3.50, 3.50, '1111', 'DF');
$pdf->page->addContent($roundrect1);

$roundrect2 = $pdf->graph->getRoundedRect(50, 255, 40, 30, 6.50, 6.50, '1000', 'b');
$pdf->page->addContent($roundrect2);

$roundrect3 = $pdf->graph->getRoundedRect(95, 255, 40, 30, 10.0, 5.0, '1111', 's', $style6);
$pdf->page->addContent($roundrect3);

$roundrect4 = $pdf->graph->getRoundedRect(140, 255, 40, 30, 8.0, 8.0, '0101', 'DF', $style6);
$pdf->page->addContent($roundrect4);

// Arrows

$arrow1 = $pdf->graph->getArrow(200, 280, 185, 266, 0, 5, 15);
$pdf->page->addContent($arrow1);

$arrow2 = $pdf->graph->getArrow(200, 280, 190, 263, 1, 5, 15);
$pdf->page->addContent($arrow2);

$arrow3 = $pdf->graph->getArrow(200, 280, 195, 261, 2, 5, 15);
$pdf->page->addContent($arrow3);

$arrow4 = $pdf->graph->getArrow(200, 280, 200, 260, 3, 5, 15);
$pdf->page->addContent($arrow4);

// ----------

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
