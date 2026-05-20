<?php

/**
 * E030_svg.php
 *
 * Dedicated SVG rendering example.
 *
 * Exercises all SVG image files shipped in the examples/images/ directory and
 * serves as a visual regression baseline for SVG feature development.
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
require __DIR__ . '/../vendor/autoload.php';

\define('OUTPUT_FILE', \realpath(__DIR__ . '/../target') . '/example_svg.pdf');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// ---------------------------------------------------------------------------
// SVG directory
$svgdir = __DIR__ . '/images';

// ---------------------------------------------------------------------------
// PDF object

$pdf = new \Com\Tecnick\Pdf\Tcpdf(unit: 'mm', isunicode: true, subsetfont: false, compress: true);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 030 — SVG');
$pdf->setTitle('SVG Rendering Example');
$pdf->setKeywords('TCPDF tc-lib-pdf SVG example');
$pdf->setPDFFilename('030_svg.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// Insert a font for page labels
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

// ---------------------------------------------------------------------------
// Page 1 — tcpdf_box.svg
//
// A multi-path box illustration that exercises fill, stroke, fill-rule and
// transform handling.
// ---------------------------------------------------------------------------

$page01 = $pdf->addPage();
$pdf->setBookmark(name: 'tcpdf_box.svg', link: '', level: 0, page: -1, posx: 0, posy: 0, fstyle: 'B', color: 'blue');

$pdf->page->addContent($bfont['out']);

// Label
$pdf->addTextCell(
    txt: 'tcpdf_box.svg — paths, fill, stroke, fill-rule',
    pid: -1,
    posx: 10,
    posy: 10,
    width: 190,
    height: 6,
    offset: 0,
    linespace: 0,
    valign: 'T',
    halign: 'L',
    cell: null,
    styles: [],
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: false,
);

// Render at two different sizes to check scaling
$svg01a = $pdf->addSVG(
    img: $svgdir . '/tcpdf_box.svg',
    posx: 10,
    posy: 20,
    width: 180,
    height: 120,
    pageheight: $page01['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg01a));

$svg01b = $pdf->addSVG(
    img: $svgdir . '/tcpdf_box.svg',
    posx: 10,
    posy: 148,
    width: 90,
    height: 60,
    pageheight: $page01['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg01b));

$svg01c = $pdf->addSVG(
    img: $svgdir . '/tcpdf_box.svg',
    posx: 105,
    posy: 148,
    width: 45,
    height: 30,
    pageheight: $page01['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg01c));

// ---------------------------------------------------------------------------
// Page 2 — testsvg.svg
//
// Exercises linear and radial gradients, clip-paths, complex paths, text and
// image embedding inside an SVG document.
// ---------------------------------------------------------------------------

$page02 = $pdf->addPage();
$pdf->setBookmark(name: 'testsvg.svg', link: '', level: 0, page: -1, posx: 0, posy: 0, fstyle: 'B', color: 'green');

$pdf->page->addContent($bfont['out']);

$pdf->addTextCell(
    txt: 'testsvg.svg — gradients, clip-paths, text, embedded image',
    pid: -1,
    posx: 10,
    posy: 10,
    width: 190,
    height: 6,
    offset: 0,
    linespace: 0,
    valign: 'T',
    halign: 'L',
    cell: null,
    styles: [],
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: false,
);

// Full-width render
$svg02a = $pdf->addSVG(
    img: $svgdir . '/testsvg.svg',
    posx: 10,
    posy: 20,
    width: 190,
    height: 95,
    pageheight: $page02['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg02a));

// Smaller render to verify scaling
$svg02b = $pdf->addSVG(
    img: $svgdir . '/testsvg.svg',
    posx: 10,
    posy: 120,
    width: 95,
    height: 47,
    pageheight: $page02['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg02b));

$svg02c = $pdf->addSVG(
    img: $svgdir . '/testsvg.svg',
    posx: 110,
    posy: 120,
    width: 47,
    height: 23,
    pageheight: $page02['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg02c));

// ---------------------------------------------------------------------------
// Page 3 — testsvgblend.svg
//
// Exercises mix-blend-mode (multiply, screen, darken …) and opacity.
// ---------------------------------------------------------------------------

$page03 = $pdf->addPage();
$pdf->setBookmark(name: 'testsvgblend.svg', link: '', level: 0, page: -1, posx: 0, posy: 0, fstyle: 'B', color: 'red');

$pdf->page->addContent($bfont['out']);

$pdf->addTextCell(
    txt: 'testsvgblend.svg — blend modes, opacity',
    pid: -1,
    posx: 10,
    posy: 10,
    width: 190,
    height: 6,
    offset: 0,
    linespace: 0,
    valign: 'T',
    halign: 'L',
    cell: null,
    styles: [],
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: false,
);

$svg03a = $pdf->addSVG(
    img: $svgdir . '/testsvgblend.svg',
    posx: 10,
    posy: 20,
    width: 190,
    height: 127,
    pageheight: $page03['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg03a));

// Side-by-side scaled copies
$svg03b = $pdf->addSVG(
    img: $svgdir . '/testsvgblend.svg',
    posx: 10,
    posy: 155,
    width: 90,
    height: 60,
    pageheight: $page03['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg03b));

$svg03c = $pdf->addSVG(
    img: $svgdir . '/testsvgblend.svg',
    posx: 105,
    posy: 155,
    width: 90,
    height: 60,
    pageheight: $page03['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg03c));

// ---------------------------------------------------------------------------
// Page 4 — tux.svg
//
// The Tux penguin — a complex multi-path SVG with fine detail and many style
// attributes (fill, stroke, stroke-width, opacity).
//
// Copyright: Larry Ewing — permits any use with proper attribution.
// ---------------------------------------------------------------------------

$page04 = $pdf->addPage();
$pdf->setBookmark(name: 'tux.svg', link: '', level: 0, page: -1, posx: 0, posy: 0, fstyle: 'B', color: 'black');

$pdf->page->addContent($bfont['out']);

$pdf->addTextCell(
    txt: 'tux.svg — complex multi-path illustration (The copyright holder of the Tux image is Larry Ewing)',
    pid: -1,
    posx: 10,
    posy: 10,
    width: 190,
    height: 6,
    offset: 0,
    linespace: 0,
    valign: 'T',
    halign: 'L',
    cell: null,
    styles: [],
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: false,
);

// Large centred render
$svg04a = $pdf->addSVG(
    img: $svgdir . '/tux.svg',
    posx: 55,
    posy: 20,
    width: 100,
    height: 121,
    pageheight: $page04['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg04a));

// Three scaled thumbnails
$svg04b = $pdf->addSVG(
    img: $svgdir . '/tux.svg',
    posx: 10,
    posy: 148,
    width: 55,
    height: 66,
    pageheight: $page04['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg04b));

$svg04c = $pdf->addSVG(
    img: $svgdir . '/tux.svg',
    posx: 70,
    posy: 148,
    width: 40,
    height: 48,
    pageheight: $page04['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg04c));

$svg04d = $pdf->addSVG(
    img: $svgdir . '/tux.svg',
    posx: 115,
    posy: 148,
    width: 25,
    height: 30,
    pageheight: $page04['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg04d));

// ---------------------------------------------------------------------------
// Page 5 — all four images together (overview grid)
// ---------------------------------------------------------------------------

$page05 = $pdf->addPage();
$pdf->setBookmark(
    name: 'SVG overview grid',
    link: '',
    level: 0,
    page: -1,
    posx: 0,
    posy: 0,
    fstyle: 'B',
    color: 'purple',
);

$pdf->page->addContent($bfont['out']);

$pdf->addTextCell(
    txt: 'SVG overview grid — all four images at equal size',
    pid: -1,
    posx: 10,
    posy: 10,
    width: 190,
    height: 6,
    offset: 0,
    linespace: 0,
    valign: 'T',
    halign: 'L',
    cell: null,
    styles: [],
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: false,
);

// Row 1
$s1 = $pdf->addSVG(
    img: $svgdir . '/tcpdf_box.svg',
    posx: 10,
    posy: 20,
    width: 90,
    height: 60,
    pageheight: $page05['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $s1));

$s2 = $pdf->addSVG(
    img: $svgdir . '/testsvg.svg',
    posx: 105,
    posy: 20,
    width: 90,
    height: 60,
    pageheight: $page05['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $s2));

// Row 2
$s3 = $pdf->addSVG(
    img: $svgdir . '/testsvgblend.svg',
    posx: 10,
    posy: 88,
    width: 90,
    height: 60,
    pageheight: $page05['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $s3));

$s4 = $pdf->addSVG(
    img: $svgdir . '/tux.svg',
    posx: 105,
    posy: 88,
    width: 90,
    height: 109,
    pageheight: $page05['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $s4));

// ---------------------------------------------------------------------------
// Page 6 — abstract_a4_features.svg (full-page portrait)
//
// Dedicated A4 portrait abstract composition that concentrates most SVG
// features currently supported by this library in one single visual sample.
// ---------------------------------------------------------------------------

$page06 = $pdf->addPage();
$pdf->setBookmark(
    name: 'abstract_a4_features.svg',
    link: '',
    level: 0,
    page: -1,
    posx: 0,
    posy: 0,
    fstyle: 'B',
    color: 'orange',
);

$svg06a = $pdf->addSVG(
    img: $svgdir . '/abstract_a4_features.svg',
    posx: 0,
    posy: 0,
    width: $page06['width'],
    height: $page06['height'],
    pageheight: $page06['height'],
);
$pdf->page->addContent($pdf->getSetSVG(soid: $svg06a));

// ---------------------------------------------------------------------------
// Output
// ---------------------------------------------------------------------------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
