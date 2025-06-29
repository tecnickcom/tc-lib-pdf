<?php
/**
 * index.php
 *
 * @since       2017-05-08
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');


define('OUTPUT_FILE', realpath(__DIR__ . '/../target') . '/example.pdf');

// define fonts directory
define('K_PATH_FONTS', realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

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
$pdf->setAuthor('John Doe');
$pdf->setSubject('tc-lib-pdf generic example');
$pdf->setTitle('Example');
$pdf->setKeywords('TCPDF tc-lib-pdf generic example');
$pdf->setPDFFilename('test_index.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont1 = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);


// test images directory
$imgdir = realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-image/test/images/');


// ----------
// Add first page

$page01 = $pdf->addPage();
$pdf->setBookmark('Images', '', 0, -1, 0, 0, 'B', 'blue');

// Add Images

$iid01 = $pdf->image->add($imgdir . '/200x100_CMYK.jpg');
$iid01_out = $pdf->image->getSetImage($iid01, 0, 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid01_out);

$iid02 = $pdf->image->add($imgdir . '/200x100_GRAY.jpg');
$iid02_out = $pdf->image->getSetImage($iid02, 40, 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid02_out);

$iid03 = $pdf->image->add($imgdir . '/200x100_GRAY.png');
$iid03_out = $pdf->image->getSetImage($iid03, 80, 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid03_out);

$iid04 = $pdf->image->add($imgdir . '/200x100_INDEX16.png');
$iid04_out = $pdf->image->getSetImage($iid04, 120, 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid04_out);

$iid05 = $pdf->image->add($imgdir . '/200x100_INDEX256.png');
$iid05_out = $pdf->image->getSetImage($iid05, 160, 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid05_out);

$iid06 = $pdf->image->add($imgdir . '/200x100_INDEXALPHA.png');
$iid06_out = $pdf->image->getSetImage($iid06, 0, 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid06_out);

$iid07 = $pdf->image->add($imgdir . '/200x100_RGB.jpg');
$iid07_out = $pdf->image->getSetImage($iid07, 40, 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid07_out);

$iid08 = $pdf->image->add($imgdir . '/200x100_RGB.png');
$iid08_out = $pdf->image->getSetImage($iid08, 80, 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid08_out);

$iid09 = $pdf->image->add($imgdir . '/200x100_RGBALPHA.png');
$iid09_out = $pdf->image->getSetImage($iid09, 120, 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid09_out);

$iid10 = $pdf->image->add($imgdir . '/200x100_RGBICC.jpg');
$iid10_out = $pdf->image->getSetImage($iid10, 160, 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid10_out);

$iid11 = $pdf->image->add($imgdir . '/200x100_RGBICC.png');
$iid11_out = $pdf->image->getSetImage($iid11, 0, 40, 40, 20, $page01['height']);
$pdf->page->addContent($iid11_out);

$iid12 = $pdf->image->add($imgdir . '/200x100_RGBINT.png');
$iid12_out = $pdf->image->getSetImage($iid12, 40, 40, 40, 20, $page01['height']);
$pdf->page->addContent($iid12_out);

// ----------
// Add second page

$page02 = $pdf->addPage();
$pdf->setBookmark('Graphics', '', 0, -1, 0, 0, 'B', 'green');

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

$pdf->graph->setPageWidth($page02['width']);
$pdf->graph->setPageHeight($page02['height']);

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
// Add page 2

$page03 = $pdf->addPage();
$pdf->setBookmark('Ellipse', '', 1);

$pdf->graph->setPageWidth($page03['width']);
$pdf->graph->setPageHeight($page03['height']);

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
// Add page 4

$page04 = $pdf->addPage();
$pdf->setBookmark('Pie Chart', '', 1);

$pdf->graph->setPageWidth($page04['width']);
$pdf->graph->setPageHeight($page04['height']);

$xc = 105;
$yc = 100;
$r = 50;

$pie1 = $pdf->graph->getPieSector($xc, $yc, $r, 20, 120, 'FD', $style5, 2);
$pdf->page->addContent($pie1);

$pie2 = $pdf->graph->getPieSector($xc, $yc, $r, 120, 250, 'FD', $style6, 2);
$pdf->page->addContent($pie2);

$pie3 = $pdf->graph->getPieSector($xc, $yc, $r, 250, 20, 'FD', $style7, 2);
$pdf->page->addContent($pie3);


// ----------
// Add page 5

$page05 = $pdf->addPage();
$pdf->setBookmark('Crop Marks and Color Maps', '', 1);

$pdf->graph->setPageWidth($page05['width']);
$pdf->graph->setPageHeight($page05['height']);



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


// ----------
// Add page 6

$page06 = $pdf->addPage();
$pdf->setBookmark('Color Gradients', '', 1);

$pdf->graph->setPageWidth($page06['width']);
$pdf->graph->setPageHeight($page06['height']);


// Linear gradient
$lingrad = $pdf->graph->getLinearGradient(20, 45, 80, 80, 'red', 'blue', [0, 0, 1, 0]);
$pdf->page->addContent($lingrad);

// Radial Gradient
$radgrad = $pdf->graph->getRadialGradient(110, 45, 80, 80, 'white', 'black', [0.5, 0.5, 1, 1, 1.2]);
$pdf->page->addContent($radgrad);


// CoonsPatchMesh
$coonspatchmesh1 = $pdf->graph->getCoonsPatchMeshWithCoords(20, 155, 80, 80, 'yellow', 'blue', 'green', 'red');
$pdf->page->addContent($coonspatchmesh1);


// set the coordinates for the cubic Bézier points x1,y1 ... x12, y12 of the patch
$coords = [
    0.00,
    0.00,
    0.33,
    0.20,
    //lower left
    0.67,
    0.00,
    1.00,
    0.00,
    0.80,
    0.33,
    //lower right
    0.80,
    0.67,
    1.00,
    1.00,
    0.67,
    0.80,
    //upper right
    0.33,
    1.00,
    0.00,
    1.00,
    0.20,
    0.67,
    //upper left
    0.00,
    0.33,
];                       //lower left

// paint a coons patch gradient with the above coordinates
$coonspatchmesh2 = $pdf->graph->getCoonsPatchMeshWithCoords(110, 155, 80, 80, 'yellow', 'blue', 'green', 'red', $coords, 0, 1);
$pdf->page->addContent($coonspatchmesh2);


// ----------
// Add page 7

$page07 = $pdf->addPage();
$pdf->setBookmark('Color gradient mesh', '', 1);

$pdf->graph->setPageWidth($page07['width']);
$pdf->graph->setPageHeight($page07['height']);

// first patch: f = 0
$patch_array[0]['f'] = 0;
$patch_array[0]['points'] = [0.00, 0.00, 0.33, 0.00, 0.67, 0.00, 1.00, 0.00, 1.00, 0.33, 0.8, 0.67, 1.00, 1.00, 0.67, 0.8, 0.33, 1.80, 0.00, 1.00, 0.00, 0.67, 0.00, 0.33];
$patch_array[0]['colors'][0] = [
    'red' => 1,
    'green' => 1,
    'blue' => 0,
    'alpha' => 1,
];
$patch_array[0]['colors'][1] = [
    'red' => 1,
    'green' => 1,
    'blue' => 1,
    'alpha' => 1,
];
$patch_array[0]['colors'][2] = [
    'red' => 0,
    'green' => 1,
    'blue' => 0,
    'alpha' => 1,
];
$patch_array[0]['colors'][3] = [
    'red' => 1,
    'green' => 0,
    'blue' => 0,
    'alpha' => 1,
];

// second patch - above the other: f = 2
$patch_array[1]['f'] = 2;
$patch_array[1]['points'] = [0.00, 1.33, 0.00, 1.67, 0.00, 2.00, 0.33, 2.00, 0.67, 2.00, 1.00, 2.00, 1.00, 1.67, 1.5, 1.33];
$patch_array[1]['colors'][0] = [
    'red' => 0,
    'green' => 0,
    'blue' => 0,
    'alpha' => 1,
];
$patch_array[1]['colors'][1] = [
    'red' => 1,
    'green' => 0,
    'blue' => 1,
    'alpha' => 1,
];

// third patch - right of the above: f = 3
$patch_array[2]['f'] = 3;
$patch_array[2]['points'] = [1.33, 0.80, 1.67, 1.50, 2.00, 1.00, 2.00, 1.33, 2.00, 1.67, 2.00, 2.00, 1.67, 2.00, 1.33, 2.00];
$patch_array[2]['colors'][0] = [
    'red' => 0,
    'green' => 1,
    'blue' => 1,
    'alpha' => 1,
];
$patch_array[2]['colors'][1] = [
    'red' => 0,
    'green' => 0,
    'blue' => 0,
    'alpha' => 1,
];

// fourth patch - below the above, which means left(?) of the above: f = 1
$patch_array[3]['f'] = 1;
$patch_array[3]['points'] = [2.00, 0.67, 2.00, 0.33, 2.00, 0.00, 1.67, 0.00, 1.33, 0.00, 1.00, 0.00, 1.00, 0.33, 0.8, 0.67];
$patch_array[3]['colors'][0] = [
    'red' => 0,
    'green' => 0,
    'blue' => 0,
    'alpha' => 1,
];
$patch_array[3]['colors'][1] = [
    'red' => 0,
    'green' => 0,
    'blue' => 1,
    'alpha' => 1,
];

$coonspatchmesh3 = $pdf->graph->getCoonsPatchMesh(0, 0, 210, 297, $patch_array, 0, 2);
$pdf->page->addContent($coonspatchmesh3);

// ----------
// Add page 8

$page08 = $pdf->addPage();
$pdf->setBookmark('Transformations', '', 1);

$pdf->graph->setPageWidth($page08['width']);
$pdf->graph->setPageHeight($page08['height']);

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


// ----------
// Add page 9

$page09 = $pdf->addPage();
$pdf->setBookmark('Barcodes', '', 0, -1, 0, 0, 'B', '');

$dest_barcode_page = $pdf->setNamedDestination('barcode');

$pdf->graph->setPageWidth($page09['width']);
$pdf->graph->setPageHeight($page09['height']);

// Barcode

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
    'https://tecnick.com',
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


// ----------
// Add page 10

$page10 = $pdf->addPage();
$pdf->setBookmark('Image Clipping', '', 0, -1, 0, 0, 'B', '');

$pdf->graph->setPageWidth($page10['width']);
$pdf->graph->setPageHeight($page10['height']);

// Clipping Mask

$cnz = $pdf->graph->getStartTransform();
$cnz .= $pdf->graph->getStarPolygon(50, 50, 40, 10, 3, 0, 'CNZ');
$clipimg = $pdf->image->add($imgdir . '/200x100_CMYK.jpg');
$cnz .= $pdf->image->getSetImage($clipimg, 10, 10, 80, 80, $page10['height']);
$cnz .= $pdf->graph->getStopTransform();

$pdf->page->addContent($cnz);


// ----------
// Add page 11

$page11 = $pdf->addPage();
$pdf->setBookmark('Text', '', 0, -1, 0, 0, 'B', '');

// Add an internal link to this page
$page11_link = $pdf->addInternalLink();

$pdf->graph->setPageWidth($page11['width']);
$pdf->graph->setPageHeight($page11['height']);

$styletxt = [
    'lineWidth' => 0.25,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => 'red',
    'fillColor' => 'black',
];

$pdf->graph->add($styletxt);


$bfont2 = $pdf->font->insert($pdf->pon, 'times', 'BI', 24);

$pdf->page->addContent($bfont2['out']);
// alternative to set the current font (last entry in the font stack):
// $pdf->page->addContent($pdf->font->getOutCurrentFont());

// Add text
$txt = $pdf->getTextLine(
    'Test PDF text with justification (stretching) % %% %%%',
    0,
    $pdf->toUnit($bfont2['ascent']),
    $page11['width']
);

$pdf->page->addContent($txt);

$bbox = $pdf->getLastBBox();

// Add text
$txt2 = $pdf->getTextLine(
    'Link to https://tcpdf.org',
    15,
    ($bbox['y'] + $bbox['h'] + $pdf->toUnit($bfont2['ascent'])),
    0,
    0,
    0,
    0,
    0,
    true,
    false,
    false,
    false,
    false,
    false,
    '',
    'S',
    [
        'xoffset' => 0.5,
        'yoffset' => 0.5,
        'opacity' => 0.5,
        'mode' => 'Normal',
        'color' => 'red',
    ],
);
$pdf->page->addContent($txt2);

// get the coordinates of the box containing the last added text string.
$bbox = $pdf->getLastBBox();

$aoid1 = $pdf->setLink(
    $bbox['x'],
    $bbox['y'],
    $bbox['w'],
    $bbox['h'],
    'https://tcpdf.org',
);
$pdf->page->addAnnotRef($aoid1);

// -----------------------------------------------

// add a text column with automatic line breaking

$bfont3 = $pdf->font->insert($pdf->pon, 'courier', '', 14);
$pdf->page->addContent($bfont3['out']);

$txt3 = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'."\n".'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';

$col = $pdf->color->getPdfColor('blue');
$pdf->page->addContent($col);

// single block of text 
$txtbox = $pdf->getTextCell(
    $txt3, // string $txt,
    20, // float $posx = 0,
    30, // float $posy = 0,
    150, // float $width = 0,
    0, // float $height = 0,
    15, // float $offset = 0,
    1, // float $linespace = 0,
    'T', // string $valign = 'C',
    'J', // string $halign = 'C',
    null, // ?array $cell = null,
    [], // array $styles = [],
    0, // float $strokewidth = 0,
    0, // float $wordspacing = 0,
    0, // float $leading = 0,
    0, // float $rise = 0,
    true, // bool $jlast = true,
    true, // bool $fill = true,
    false, // bool $stroke = false,
    false, //bool $underline = false,
    false, //bool $linethrough = false,
    false, //bool $overline = false,
    false, // bool $clip = false,
    false, // bool $drawcell = true,
    '', // string $forcedir = '',
    null // ?array $shadow = null,
);
$pdf->page->addContent($txtbox);

$col = $pdf->color->getPdfColor('black');
$pdf->page->addContent($col);

$bfont4 = $pdf->font->insert($pdf->pon, 'freeserif', 'I', 14);
$pdf->page->addContent($bfont4['out']);

$pdf->setDefaultCellPadding(2,2,2,2);

// Text cell
$style_cell = [
    'all' => [
        'lineWidth' => 1,
        'lineCap' => 'round',
        'lineJoin' => 'round',
        'miterLimit' => 1,
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => 'green',
        'fillColor' => 'yellow',
    ],
];

$txtcell1 = $pdf->getTextCell(
    'DEFAULT', // string $txt,
    20, // float $posx = 0,
    100, // float $posy = 0,
    0, // float $width = 0,
    0, // float $height = 0,
    0, // float $offset = 0,
    0, // float $linespace = 0,
    'C', // string $valign = 'C',
    'C', // string $halign = 'C',
    null, // ?array $cell = null,
    $style_cell, // array $styles = [],
    0, // float $strokewidth = 0,
    0, // float $wordspacing = 0,
    0, // float $leading = 0,
    0, // float $rise = 0,
    true, // bool $jlast = true,
    true, // bool $fill = true,
    false, // bool $stroke = false,
    false, //bool $underline = false,
    false, //bool $linethrough = false,
    false, //bool $overline = false,
    false, // bool $clip = false,
    true, // bool $drawcell = true,
    '', // string $forcedir = '',
    null // ?array $shadow = null,
);
$pdf->page->addContent($txtcell1);

$pdf->setDefaultCellBorderPos($pdf::BORDERPOS_EXTERNAL);
$txtcell2 = $pdf->getTextCell(
    'EXTERNAL', // string $txt,
    49, // float $posx = 0,
    100, // float $posy = 0,
    0, // float $width = 0,
    0, // float $height = 0,
    0, // float $offset = 0,
    0, // float $linespace = 0,
    'C', // string $valign = 'C',
    'C', // string $halign = 'C',
    null, // ?array $cell = null,
    $style_cell, // array $styles = [],
    0, // float $strokewidth = 0,
    0, // float $wordspacing = 0,
    0, // float $leading = 0,
    0, // float $rise = 0,
    true, // bool $jlast = true,
    true, // bool $fill = true,
    false, // bool $stroke = false,
    false, //bool $underline = false,
    false, //bool $linethrough = false,
    false, //bool $overline = false,
    false, // bool $clip = false,
    true, // bool $drawcell = true,
    '', // string $forcedir = '',
    null // ?array $shadow = null,
);
$pdf->page->addContent($txtcell2);

$pdf->setDefaultCellBorderPos($pdf::BORDERPOS_INTERNAL);
$txtcell2 = $pdf->getTextCell(
    'INTERNAL', // string $txt,
    80, // float $posx = 0,
    100, // float $posy = 0,
    0, // float $width = 0,
    0, // float $height = 0,
    0, // float $offset = 0,
    0, // float $linespace = 0,
    'C', // string $valign = 'C',
    'C', // string $halign = 'C',
    null, // ?array $cell = null,
    $style_cell, // array $styles = [],
    0, // float $strokewidth = 0,
    0, // float $wordspacing = 0,
    0, // float $leading = 0,
    0, // float $rise = 0,
    true, // bool $jlast = true,
    true, // bool $fill = true,
    false, // bool $stroke = false,
    false, //bool $underline = false,
    false, //bool $linethrough = false,
    false, //bool $overline = false,
    false, // bool $clip = false,
    true, // bool $drawcell = true,
    '', // string $forcedir = '',
    null // ?array $shadow = null,
);
$pdf->page->addContent($txtcell2);


$pdf->setDefaultCellBorderPos($pdf::BORDERPOS_DEFAULT);

$txtcell2 = $pdf->getTextCell(
    $txt3, // string $txt,
    20, // float $posx = 0,
    120, // float $posy = 0,
    150, // float $width = 0,
    0, // float $height = 0,
    0, // float $offset = 0,
    0, // float $linespace = 0,
    'C', // string $valign = 'C',
    'J', // string $halign = 'C',
    null, // ?array $cell = null,
    $style_cell, // array $styles = [],
    0, // float $strokewidth = 0,
    0, // float $wordspacing = 0,
    0, // float $leading = 0,
    0, // float $rise = 0,
    true, // bool $jlast = true,
    true, // bool $fill = true,
    false, // bool $stroke = false,
    false, //bool $underline = false,
    false, //bool $linethrough = false,
    false, //bool $overline = false,
    false, // bool $clip = false,
    true, // bool $drawcell = true,
    '', // string $forcedir = '',
    null // ?array $shadow = null,
);
$pdf->page->addContent($txtcell2);


$bfont4 = $pdf->font->insert($pdf->pon, 'dejavusans', '', 14);
$pdf->page->addContent($bfont4['out']);

$pdf->setDefaultCellPadding(2,2,2,2);

$style_cell_b = [
    'all' => [
        'lineWidth' => 0.5,
        'lineCap' => 'round',
        'lineJoin' => 'round',
        'miterLimit' => 0.5,
        'dashArray' => [0,1],
        'dashPhase' => 2,
        'lineColor' => 'red',
    ],
];

// block of text between two page regions
$pdf->addTextCell(
    $txt3, // string $txt,
    -1, // int $pid = -1,
    20, // float $posx = 0,
    165, // float $posy = 0,
    150, // float $width = 0,
    0, // float $height = 0,
    15, // float $offset = 0,
    1, // float $linespace = 0,
    'T', // string $valign = 'T',
    'J', // string $halign = '',
    null, // ?array $cell = null,
    $style_cell_b, // array $styles = [],
    0, // float $strokewidth = 0,
    0, // float $wordspacing = 0,
    0, // float $leading = 0,
    0, // float $rise = 0,
    true, // bool $jlast = true,
    true, // bool $fill = true,
    false, // bool $stroke = false,
    false, //bool $underline = false,
    false, //bool $linethrough = false,
    false, //bool $overline = false,
    false, // bool $clip = false,
    true, // bool $drawcell = true,
    '', // string $forcedir = '',
    null, // ?array $shadow = null,
);

$txt4 = 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?';

$txt5 = 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.';

$pdf->enableZeroWidthBreakPoints(true);
$pdf->addTextCell(
    "TEST-TEXT-ENABLE-AUTO-BREAK-POINTS", // string $txt,
    -1, // int $pid = -1,
    20, // float $posx = 0,
    233, // float $posy = 0,
    85, // float $width = 0,
    0, // float $height = 0,
    0, // float $offset = 0,
    0, // float $linespace = 0,
    'C', // string $valign = 'T',
    'L', // string $halign = '',
    null, // ?array $cell = null,
    $style_cell, // array $styles = [],
    0, // float $strokewidth = 0,
    0, // float $wordspacing = 0,
    0, // float $leading = 0,
    0, // float $rise = 0,
    true, // bool $jlast = true,
    true, // bool $fill = true,
    false, // bool $stroke = false,
    false, //bool $underline = false,
    false, //bool $linethrough = false,
    false, //bool $overline = false,
    false, // bool $clip = false,
    true, // bool $drawcell = true,
    '', // string $forcedir = '',
    null, // ?array $shadow = null,
);

$pdf->enableZeroWidthBreakPoints(false);
$pdf->addTextCell(
    "TEST-TEXT-DISABLE-AUTO-BREAK-POINTS", // string $txt,
    -1, // int $pid = -1,
    20, // float $posx = 0,
    252, // float $posy = 0,
    85, // float $width = 0,
    0, // float $height = 0,
    0, // float $offset = 0,
    0, // float $linespace = 0,
    'C', // string $valign = 'T',
    'L', // string $halign = '',
    null, // ?array $cell = null,
    $style_cell, // array $styles = [],
    0, // float $strokewidth = 0,
    0, // float $wordspacing = 0,
    0, // float $leading = 0,
    0, // float $rise = 0,
    true, // bool $jlast = true,
    true, // bool $fill = true,
    false, // bool $stroke = false,
    false, //bool $underline = false,
    false, //bool $linethrough = false,
    false, //bool $overline = false,
    false, // bool $clip = false,
    true, // bool $drawcell = true,
    '', // string $forcedir = '',
    null, // ?array $shadow = null,
);

// Hyphenation example
// TEX hyphenation patterns can be downloaded from:
// https://www.ctan.org/tex-archive/language/hyph-utf8/tex/generic/hyph-utf8/patterns/tex
//
//$hyphen_patterns = $pdf->loadTexHyphenPatterns('../../RESOURCES/hyph-la-x-classic.tex');
//$pdf->setTexHyphenPatterns($hyphen_patterns);

// block of text between two page regions
$pdf->addTextCell(
    $txt3 . "\n" . $txt4 . "\n" . $txt5, // string $txt,
    -1, // int $pid = -1,
    20, // float $posx = 0,
    265, // float $posy = 0,
    120, // float $width = 0,
    0, // float $height = 0,
    15, // float $offset = 0,
    1, // float $linespace = 0,
    'T', // string $valign = 'T',
    'J', // string $halign = '',
    null, // ?array $cell = null,
    $style_cell, // array $styles = [],
    0, // float $strokewidth = 0,
    0, // float $wordspacing = 0,
    0, // float $leading = 0,
    0, // float $rise = 0,
    true, // bool $jlast = true,
    true, // bool $fill = true,
    false, // bool $stroke = false,
    false, //bool $underline = false,
    false, //bool $linethrough = false,
    false, //bool $overline = false,
    false, // bool $clip = false,
    true, // bool $drawcell = true,
    '', // string $forcedir = '',
    null, // ?array $shadow = null,
);

$pdf->addTextCell(
    'overline, linethrough and underline', // string $txt,
    -1, // int $pid = -1,
    15, // float $posx = 0,
    50, // float $posy = 0,
    180, // float $width = 0,
    0, // float $height = 0,
    0, // float $offset = 0,
    1, // float $linespace = 0,
    'T', // string $valign = 'C',
    'L', // string $halign = 'C',
    null, // ?array $cell = null,
    [], // array $styles = [],
    0, // float $strokewidth = 0,
    0, // float $wordspacing = 0,
    0, // float $leading = 0,
    0, // float $rise = 0,
    true, // bool $jlast = true,
    true, // bool $fill = true,
    false, // bool $stroke = false,
    true, //bool $underline = false,
    true, //bool $linethrough = false,
    true, //bool $overline = false,
    false, // bool $clip = false,
    false, // bool $drawcell = true,
    '', // string $forcedir = '',
    null // ?array $shadow = null,
);

// ----------

// Page signature

$pageC01 = $pdf->addPage();
$pdf->setBookmark('Signature', '', 0, -1, 0, 0, 'B', 'red');

/*
NOTES:
 - To create self-signed signature:
   openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
 - To export crt to p12:
   openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
 - To convert pfx certificate to pem:
   openssl pkcs12 -in tcpdf.pfx -out tcpdf.crt -nodes
*/

// set certificate file
$cert = 'file://data/cert/tcpdf.crt';

$sigdata = [
    // 'appearance' => [
    //     'empty' => [],
    //     'name' => '',
    //     'page' => 0,
    //     'rect' => '',
    // ],
    // 'approval' => '',
    'cert_type' => 2,
    // 'extracerts' => null,
    'info' => [
        'ContactInfo' => 'http://www.tcpdf.org',
        'Location' => 'Office',
        'Name' => 'tc-lib-pdf',
        'Reason' => 'PDF signature test',
    ],
    'password' => 'tcpdfdemo',
    'privkey' => $cert,
    'signcert' => $cert,
];

$pdf->setSignature($sigdata);

$sigimg = $pdf->image->add('./images/tcpdf_signature.png');
$sigimg_out = $pdf->image->getSetImage($sigimg, 30, 30, 20, 20, $pageC01['height']);
$pdf->page->addContent($sigimg_out);

$pdf->setSignatureAppearance(30, 30, 20, 20, -1, 'test');

$pdf->addEmptySignatureAppearance(30, 60, 20, 20, -1, 'test');


// ----------

// XOBject template

$pageC02 = $pdf->addPage();
$pdf->setBookmark('XOBject Template', '', 0, -1, 0, 0, 'B', '');

$tid = $pdf->newXObjectTemplate(80, 80, []);


$timg = $pdf->image->add($imgdir . '/200x100_RGB.png');
$pdf->addXObjectImageID($tid, $timg);

$xcnz = $pdf->image->getSetImage($timg, 10, 10, 80, 80, 80);
$pdf->addXObjectContent($tid, $xcnz);

$pdf->exitXObjectTemplate();

$tmpl = $pdf->graph->getAlpha(0.33);
$tmpl .= $pdf->getXObjectTemplate($tid, 10, 10, 30, 30, 'T', 'L');
$pdf->page->addContent($tmpl);

$tmpl = $pdf->graph->getAlpha(0.66);
$tmpl .= $pdf->getXObjectTemplate($tid, 20, 20, 40, 40, 'T', 'L');
$pdf->page->addContent($tmpl);

$tmpl = $pdf->graph->getAlpha(1);
$tmpl .= $pdf->getXObjectTemplate($tid, 40, 40, 60, 60, 'T', 'L');
$pdf->page->addContent($tmpl);

// ----------

// Layers

$pageV01 = $pdf->addPage();
$pdf->setBookmark('Layers', '', 0, -1, 0, 0, 'B', '');

$pdf->page->addContent($bfont4['out']);

$txtV1 = 'LAYERS: You can limit the visibility of PDF objects to screen or printer by using the newLayer() method.
Check the print preview of this document to display the alternative text.';
$txtboxV1 = $pdf->getTextCell($txtV1, 15, 15, 150, valign: 'T', halign: 'L');
$pdf->page->addContent($txtboxV1);

$lyr01 = $pdf->newLayer('screen', [], false, true, false);
$pdf->page->addContent($lyr01);
$txtV2 = 'This line is for display on screen only.';
$txtboxV2 = $pdf->getTextCell($txtV2, 15, 45, 150, valign: 'T', halign: 'L');
$pdf->page->addContent($txtboxV2);
$pdf->page->addContent($pdf->closeLayer());


$lyr02 = $pdf->newLayer('print', [], true, false, false);
$pdf->page->addContent($lyr02);
$txtV3 = 'This line is for print only.';
$txtboxV3 = $pdf->getTextCell($txtV3, 15, 55, 150, valign: 'T', halign: 'L');
$pdf->page->addContent($txtboxV3);
$pdf->page->addContent($pdf->closeLayer());

// Links

$txtlnk1 = $pdf->getTextCell("Link to page 11", 15, 70, 150, valign: 'T', halign: 'L');
$pdf->page->addContent($txtlnk1);
$bbox = $pdf->getLastBBox();
$lnk1 = $pdf->setLink(
    $bbox['x'],
    $bbox['y'],
    $bbox['w'],
    $bbox['h'],
    $page11_link,
);
$pdf->page->addAnnotRef($lnk1);

$txtlnk2 = $pdf->getTextCell("Link dest to barcode page", 15, 80, 150, valign: 'T', halign: 'L');
$pdf->page->addContent($txtlnk2);
$bbox = $pdf->getLastBBox();
$lnk2 = $pdf->setLink(
    $bbox['x'],
    $bbox['y'],
    $bbox['w'],
    $bbox['h'],
    $dest_barcode_page,
);
$pdf->page->addAnnotRef($lnk2);

// ----------

$pageTOC = $pdf->addPage();
$pdf->setBookmark('TOC');

$pdf->page->addContent($bfont4['out']);

$pdf->setDefaultCellMargin(0,0,0,0);
$pdf->setDefaultCellPadding(1,1,1,1);

$style_cell_toc = [
    'all' => [
        'lineWidth' => 0,
        'lineCap' => 'round',
        'lineJoin' => 'round',
        'miterLimit' => 0,
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => '',
    ],
];

$pdf->graph->add($style_cell_toc);

$pdf->addTOC(-1, 15, 30, 170, false);


// =============================================================

// ----------
// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// ----------

// Various output modes:

//$pdf->savePDF(dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF($rawpdf);
//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
