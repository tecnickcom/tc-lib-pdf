<?php
/**
 * 007_example_crop_marks.php
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
$pdf->setPDFFilename('007_example_crop_marks.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent(false);

$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$page = $pdf->addPage(['format' => 'A4']);

$pdf->graph->setPageWidth($page['width']);
$pdf->graph->setPageHeight($page['height']);

$pdf->page->addContent($bfont['out']);

$style7 = [
    'lineWidth' => 0.5,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => 'darkorange',
    'fillColor' => 'palegreen',
];


$pdf->graph->add($style7);

$style8 = $pdf->graph->getCurrentStyleArray();

// Crop Marks

$crpmrk1 = $pdf->graph->getCropMark(50, 70, 10, 10, 'TL', $style8);
$pdf->page->addContent($crpmrk1);

$crpmrk2 = $pdf->graph->getCropMark(140, 70, 10, 10, 'TR', $style8);
$pdf->page->addContent($crpmrk2);

$crpmrk3 = $pdf->graph->getCropMark(50, 120, 10, 10, 'BL', $style8);
$pdf->page->addContent($crpmrk3);

$crpmrk4 = $pdf->graph->getCropMark(140, 120, 10, 10, 'BR', $style8);
$pdf->page->addContent($crpmrk4);

$crpmrk5 = $pdf->graph->getCropMark(95, 65, 5, 5, 'LTR');
$pdf->page->addContent($crpmrk5);

$crpmrk6 = $pdf->graph->getCropMark(95, 125, 5, 5, 'LBR');
$pdf->page->addContent($crpmrk6);

$crpmrk7 = $pdf->graph->getCropMark(45, 95, 5, 5, 'TLB');
$pdf->page->addContent($crpmrk7);

$crpmrk8 = $pdf->graph->getCropMark(145, 95, 5, 5, 'TRB');
$pdf->page->addContent($crpmrk8);

$crpmrk9 = $pdf->graph->getCropMark(95, 140, 5, 5, 'TLBR', [
    'lineColor' => 'lime',
]);
$pdf->page->addContent($crpmrk9);


// Registration Marks


$regmrk1 = $pdf->graph->getRegistrationMark(40, 60, 5, false);
$pdf->page->addContent($regmrk1);

$regmrk2 = $pdf->graph->getRegistrationMark(150, 60, 5, true);
$pdf->page->addContent($regmrk2);

$regmrk3 = $pdf->graph->getRegistrationMark(40, 130, 5, true);
$pdf->page->addContent($regmrk3);

$regmrk4 = $pdf->graph->getRegistrationMark(150, 130, 5, false, 'blue');
$pdf->page->addContent($regmrk4);

// CYMK Registration Mark
$regmrk5 = $pdf->graph->getCmykRegistrationMark(150, 155, 8);
$pdf->page->addContent($regmrk5);

// Add Spot Colors

$color_custom_dark_green = new \Com\Tecnick\Color\Model\Cmyk(
    [
        'cyan' => 1,
        'magenta' => 0.5,
        'yellow' => 0.8,
        'key' => 0.45,
        'alpha' => 0,
    ]
);
$pdf->color->addSpotColor('Custom Dark Green', $color_custom_dark_green);

$color_custom_light_yellow = new \Com\Tecnick\Color\Model\Cmyk(
    [
        'cyan' => 0,
        'magenta' => 0,
        'yellow' => 0.55,
        'key' => 0,
        'alpha' => 0,
    ]
);
$pdf->color->addSpotColor('Custom Light Yellow', $color_custom_light_yellow);

$color_custom_black = new \Com\Tecnick\Color\Model\Cmyk(
    [
        'cyan' => 0,
        'magenta' => 0,
        'yellow' => 0,
        'key' => 1,
        'alpha' => 0,
    ]
);
$pdf->color->addSpotColor('Custom Black', $color_custom_black);

$color_custom_red = new \Com\Tecnick\Color\Model\Cmyk(
    [
        'cyan' => 0.3,
        'magenta' => 1,
        'yellow' => 0.9,
        'key' => 0.1,
        'alpha' => 0,
    ]
);
$pdf->color->addSpotColor('Custom Red', $color_custom_red);

$color_custom_green = new \Com\Tecnick\Color\Model\Cmyk(
    [
        'cyan' => 1,
        'magenta' => 0.3,
        'yellow' => 1,
        'key' => 0,
        'alpha' => 0,
    ]
);
$pdf->color->addSpotColor('Custom Green', $color_custom_green);

$color_custom_blue = new \Com\Tecnick\Color\Model\Cmyk(
    [
        'cyan' => 1,
        'magenta' => 0.6,
        'yellow' => 0.1,
        'key' => 0.05,
        'alpha' => 0,
    ]
);
$pdf->color->addSpotColor('Custom Blue', $color_custom_blue);

$color_custom_yellow = new \Com\Tecnick\Color\Model\Cmyk(
    [
        'cyan' => 0,
        'magenta' => 0.2,
        'yellow' => 1,
        'key' => 0,
        'alpha' => 0,
    ]
);
$pdf->color->addSpotColor('Custom Yellow', $color_custom_yellow);



$style8 = [
    'lineWidth' => 0,
    'lineCap' => 0,
    'lineJoin' => 0,
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => 'white',
    'fillColor' => 'white',
];


$pdf->page->addContent($pdf->graph->getStyleCmd($style8));


// Color Registration Bars with spot colors

$colregspot = $pdf->graph->getColorRegistrationBar(
    30,
    150,
    100,
    10,
    true,
    [
        ['black'],
        ['red'],
        ['green'],
        ['blue'],
        ['Custom Dark Green'],
        ['Custom Light Yellow'],
        ['Custom Black'],
        ['Custom Red'],
        ['Custom Green'],
        ['Custom Blue'],
        ['Custom Yellow'],
    ]
);
$pdf->page->addContent($colregspot);


$colreg1 = $pdf->graph->getColorRegistrationBar(50, 70, 40, 40, false);
$pdf->page->addContent($colreg1);

$colreg2 = $pdf->graph->getColorRegistrationBar(90, 70, 40, 40, true);
$pdf->page->addContent($colreg2);

$barcols = [
    ['black'],
    ['white'],
    ['red'],
    ['green'],
    ['blue'],
    ['cyan'],
    ['magenta'],
    ['yellow'],
    ['gray'],
    ['black'],
];

$colreg3 = $pdf->graph->getColorRegistrationBar(50, 115, 80, 5, true, $barcols);
$pdf->page->addContent($colreg3);

$colreg4 = $pdf->graph->getColorRegistrationBar(135, 70, 5, 50, false, $barcols);
$pdf->page->addContent($colreg4);

// =============================================================

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF($rawpdf);
