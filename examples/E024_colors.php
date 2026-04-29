<?php
/**
 * E024_colors.php
 *
 * @since       2026-04-26
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

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm',
    true,
    false,
    true,
    '',
    null,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 024');
$pdf->setTitle('CMYK, RGB and Grayscale colors');
$pdf->setKeywords('TCPDF tc-lib-pdf example cmyk rgb grayscale colors');
$pdf->setPDFFilename('024_colors.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);
$pdf->enableDefaultPageContent();

$fontFamily = 'helvetica';
$fontStyle = '';
$fontSize = 10;

$drawColorBlock = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    float $x,
    float $y,
    float $w,
    float $h,
    string $label,
    string $strokeColor,
    string $fillColor,
    string $textColor,
): void {
    $style = [
        'all' => [
            'lineWidth' => 2.0,
            'lineCap' => 'square',
            'lineJoin' => 'miter',
            'dashArray' => [],
            'dashPhase' => 0,
            'lineColor' => $strokeColor,
            'fillColor' => $fillColor,
        ],
    ];

    $pdf->page->addContent($pdf->graph->getRect($x, $y, $w, $h, 'DF', $style));
    $pdf->page->addContent($pdf->color->getPdfColor($textColor));
    $pdf->page->addContent($pdf->getTextCell($label, $x, $y + $h + 2.0, $w, 6.0, valign: 'T', halign: 'L', drawcell: false));
};

// Insert the font once and reuse it across all pages.
$pdf->font->insert($pdf->pon, $fontFamily, $fontStyle, $fontSize, 0.0, 1.0);

$pdf->addPage();

$colorIntroHtml = '<h1>Color Example</h1>This page demonstrates core color support powered by <span style="color:#008800">tc-lib-color</span>. The library provides unified parsing and conversion for grayscale, RGB, CMYK, spot, and Lab color models so the same API can target both screen output and print-ready workflows. In this sample, color definitions are applied consistently to fills, strokes, and text to highlight how each model can be mixed in a single PDF document.';
$pdf->addHTMLCell($colorIntroHtml, 15, 28, 180);

$posy = 100;

$drawColorBlock($pdf, 30.0, $posy, 30.0, 30.0, 'Cyan', 'cmyk(50,0,0,0)', 'cmyk(100,0,0,0)', 'cmyk(100,0,0,0)');
$drawColorBlock($pdf, 70.0, $posy, 30.0, 30.0, 'Magenta', 'cmyk(0,50,0,0)', 'cmyk(0,100,0,0)', 'cmyk(0,100,0,0)');
$drawColorBlock($pdf, 110.0, $posy, 30.0, 30.0, 'Yellow', 'cmyk(0,0,50,0)', 'cmyk(0,0,100,0)', 'cmyk(0,0,100,0)');
$drawColorBlock($pdf, 150.0, $posy, 30.0, 30.0, 'Black', 'cmyk(0,0,0,50)', 'cmyk(0,0,0,100)', 'cmyk(0,0,0,100)');

$posy += 50;
$drawColorBlock($pdf, 30.0, $posy, 30.0, 30.0, 'Red', 'rgb(255,127,127)', 'rgb(255,0,0)', 'rgb(255,0,0)');
$drawColorBlock($pdf, 70.0, $posy, 30.0, 30.0, 'Green', 'rgb(127,255,127)', 'rgb(0,255,0)', 'rgb(0,255,0)');
$drawColorBlock($pdf, 110.0, $posy, 30.0, 30.0, 'Blue', 'rgb(127,127,255)', 'rgb(0,0,255)', 'rgb(0,0,255)');

$posy += 50;
$drawColorBlock($pdf, 30.0, $posy, 30.0, 30.0, 'Gray', '#bfbfbf', '#7f7f7f', '#7f7f7f');

// ----------

$pdf->addPage();
$spotIntroHtml = '<h1>Example of Spot Colors</h1>Spot colors are single, pre-mixed inks, rather than colors produced by four (CMYK), six (CMYKOG), or more process inks during printing. They are usually supplied by specialized vendors, although many printers also use in-house formulations to match target shades.<br /><br />Because there is no universal open standard for spot colors, users typically rely on vendor color books and manually register both the spot color names and their CMYK equivalents.<br /><br />Common industry spot color systems include:<br /><span style="color:#008800">ANPA-COLOR, DIC, FOCOLTONE, GCMI, HKS, PANTONE, TOYO, TRUMATCH</span>.';
$pdf->addHTMLCell($spotIntroHtml, 15, 28, 180);

$pdf->color->addSpotColor(
    'My TCPDF Dark Green',
    new \Com\Tecnick\Color\Model\Cmyk(['cyan' => 1.0, 'magenta' => 0.5, 'yellow' => 0.8, 'key' => 0.45, 'alpha' => 0.0])
);
$pdf->color->addSpotColor(
    'My TCPDF Light Yellow',
    new \Com\Tecnick\Color\Model\Cmyk(['cyan' => 0.0, 'magenta' => 0.0, 'yellow' => 0.55, 'key' => 0.0, 'alpha' => 0.0])
);
$pdf->color->addSpotColor(
    'My TCPDF Black',
    new \Com\Tecnick\Color\Model\Cmyk(['cyan' => 0.0, 'magenta' => 0.0, 'yellow' => 0.0, 'key' => 1.0, 'alpha' => 0.0])
);
$pdf->color->addSpotColor(
    'My TCPDF Red',
    new \Com\Tecnick\Color\Model\Cmyk(['cyan' => 0.3, 'magenta' => 1.0, 'yellow' => 0.9, 'key' => 0.1, 'alpha' => 0.0])
);
$pdf->color->addSpotColor(
    'My TCPDF Green',
    new \Com\Tecnick\Color\Model\Cmyk(['cyan' => 1.0, 'magenta' => 0.3, 'yellow' => 1.0, 'key' => 0.0, 'alpha' => 0.0])
);
$pdf->color->addSpotColor(
    'My TCPDF Blue',
    new \Com\Tecnick\Color\Model\Cmyk(['cyan' => 1.0, 'magenta' => 0.6, 'yellow' => 0.1, 'key' => 0.05, 'alpha' => 0.0])
);
$pdf->color->addSpotColor(
    'My TCPDF Yellow',
    new \Com\Tecnick\Color\Model\Cmyk(['cyan' => 0.0, 'magenta' => 0.2, 'yellow' => 1.0, 'key' => 0.0, 'alpha' => 0.0])
);

$drawSpotColorBlock = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    float $x,
    float $y,
    float $w,
    float $h,
    string $spotName,
    float $tint,
): void {
    $pdf->page->addContent($pdf->color->getPdfColor($spotName, true, $tint));
    $pdf->page->addContent($pdf->color->getPdfColor($spotName, false, $tint));
    $pdf->page->addContent($pdf->graph->getRect($x, $y, $w, $h, 'DF'));

    $pdf->page->addContent($pdf->color->getPdfColor('My TCPDF Black', false, 1.0));
    $pdf->page->addContent($pdf->getTextCell($spotName, $x + $w + 3.0, $y + 6.0, 110.0, 8.0, valign: 'T', halign: 'L', drawcell: false));
};

$spots = [
    'My TCPDF Dark Green',
    'My TCPDF Light Yellow',
    'My TCPDF Red',
    'My TCPDF Green',
    'My TCPDF Blue',
    'My TCPDF Yellow',
];

$startY = 90.0;
foreach ($spots as $spot) {
    $drawSpotColorBlock($pdf, 30.0, $startY, 40.0, 16.0, $spot, 1.0);
    $startY += 24.0;
}

// ----------

$pdf->addPage();
$labIntroHtml = '<h1>Example of Lab Spot Colors</h1>Lab-based spot colors define color values using CIE L*a*b* coordinates, which are device-independent and better suited for consistent cross-device color reproduction.<br /><br />The following sample spot colors are registered using <span style="color:#008800">addSpotLabColor()</span> and then rendered with standard spot-color drawing commands.';
$pdf->addHTMLCell($labIntroHtml, 15, 28, 180);

$pdf->color->addSpotLabColor('My TCPDF Lab Red', 53.0, 80.0, 67.0);
$pdf->color->addSpotLabColor('My TCPDF Lab Green', 87.0, -86.0, 83.0);
$pdf->color->addSpotLabColor('My TCPDF Lab Blue', 32.0, 79.0, -108.0);
$pdf->color->addSpotLabColor('My TCPDF Lab Orange', 74.0, 24.0, 78.0);
$pdf->color->addSpotLabColor('My TCPDF Lab Violet', 40.0, 60.0, -55.0);
$pdf->color->addSpotLabColor('My TCPDF Lab Gray', 55.0, 0.0, 0.0);

// Advanced Lab spot example: custom whitepoint/blackpoint (D50 with non-zero blackpoint).
$pdf->color->addSpotLabColor(
    'My TCPDF Lab D50 Orange',
    64.25,
    58.5,
    71.2,
    [0.9642, 1.0000, 0.8251],
    [0.0120, 0.0125, 0.0110]
);

// Advanced Lab spot example: constrained a*/b* range and explicit Tint=0 Lab endpoint (col0).
$pdf->color->addSpotLabColor(
    'My TCPDF Lab Narrow Blue',
    36.0,
    62.0,
    -76.0,
    [0.9505, 1.0000, 1.0890],
    [0.0, 0.0, 0.0],
    [-96.0, 96.0, -96.0, 96.0],
    [92.0, -4.0, -8.0]
);

$labSpots = [
    ['My TCPDF Lab Red', 1.0],
    ['My TCPDF Lab Green', 1.0],
    ['My TCPDF Lab Blue', 1.0],
    ['My TCPDF Lab Orange', 0.9],
    ['My TCPDF Lab Violet', 0.85],
    ['My TCPDF Lab Gray', 1.0],
    ['My TCPDF Lab D50 Orange', 1.0],
    ['My TCPDF Lab Narrow Blue', 1.0],
];

$startY = 70.0;
foreach ($labSpots as [$spot, $tint]) {
    $drawSpotColorBlock($pdf, 30.0, $startY, 40.0, 16.0, $spot, $tint);
    $startY += 24.0;
}

// ----------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);