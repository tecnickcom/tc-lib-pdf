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
require(__DIR__ . '/../vendor/autoload.php');

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

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm',   // string $unit
    true,   // bool $isunicode
    false,  // bool $subsetfont
    true,   // bool $compress
);

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
$pdf->setBookmark('tcpdf_box.svg', '', 0, -1, 0, 0, 'B', 'blue');

$pdf->page->addContent($bfont['out']);

// Label
$pdf->addTextCell('tcpdf_box.svg — paths, fill, stroke, fill-rule', -1, 10, 10, 190, 6, 0, 0, 'T', 'L', null, [], 0, 0, 0, 0, true, true, false, false, false, false, false, false);

// Render at two different sizes to check scaling
$svg01a = $pdf->addSVG($svgdir . '/tcpdf_box.svg', 10, 20, 180, 120, $page01['height']);
$pdf->page->addContent($pdf->getSetSVG($svg01a));

$svg01b = $pdf->addSVG($svgdir . '/tcpdf_box.svg', 10, 148, 90, 60, $page01['height']);
$pdf->page->addContent($pdf->getSetSVG($svg01b));

$svg01c = $pdf->addSVG($svgdir . '/tcpdf_box.svg', 105, 148, 45, 30, $page01['height']);
$pdf->page->addContent($pdf->getSetSVG($svg01c));

// ---------------------------------------------------------------------------
// Page 2 — testsvg.svg
//
// Exercises linear and radial gradients, clip-paths, complex paths, text and
// image embedding inside an SVG document.
// ---------------------------------------------------------------------------

$page02 = $pdf->addPage();
$pdf->setBookmark('testsvg.svg', '', 0, -1, 0, 0, 'B', 'green');

$pdf->page->addContent($bfont['out']);

$pdf->addTextCell('testsvg.svg — gradients, clip-paths, text, embedded image', -1, 10, 10, 190, 6, 0, 0, 'T', 'L', null, [], 0, 0, 0, 0, true, true, false, false, false, false, false, false);

// Full-width render
$svg02a = $pdf->addSVG($svgdir . '/testsvg.svg', 10, 20, 190, 95, $page02['height']);
$pdf->page->addContent($pdf->getSetSVG($svg02a));

// Smaller render to verify scaling
$svg02b = $pdf->addSVG($svgdir . '/testsvg.svg', 10, 120, 95, 47, $page02['height']);
$pdf->page->addContent($pdf->getSetSVG($svg02b));

$svg02c = $pdf->addSVG($svgdir . '/testsvg.svg', 110, 120, 47, 23, $page02['height']);
$pdf->page->addContent($pdf->getSetSVG($svg02c));

// ---------------------------------------------------------------------------
// Page 3 — testsvgblend.svg
//
// Exercises mix-blend-mode (multiply, screen, darken …) and opacity.
// ---------------------------------------------------------------------------

$page03 = $pdf->addPage();
$pdf->setBookmark('testsvgblend.svg', '', 0, -1, 0, 0, 'B', 'red');

$pdf->page->addContent($bfont['out']);

$pdf->addTextCell('testsvgblend.svg — blend modes, opacity', -1, 10, 10, 190, 6, 0, 0, 'T', 'L', null, [], 0, 0, 0, 0, true, true, false, false, false, false, false, false);

$svg03a = $pdf->addSVG($svgdir . '/testsvgblend.svg', 10, 20, 190, 127, $page03['height']);
$pdf->page->addContent($pdf->getSetSVG($svg03a));

// Side-by-side scaled copies
$svg03b = $pdf->addSVG($svgdir . '/testsvgblend.svg', 10, 155, 90, 60, $page03['height']);
$pdf->page->addContent($pdf->getSetSVG($svg03b));

$svg03c = $pdf->addSVG($svgdir . '/testsvgblend.svg', 105, 155, 90, 60, $page03['height']);
$pdf->page->addContent($pdf->getSetSVG($svg03c));

// ---------------------------------------------------------------------------
// Page 4 — tux.svg
//
// The Tux penguin — a complex multi-path SVG with fine detail and many style
// attributes (fill, stroke, stroke-width, opacity).
//
// Copyright: Larry Ewing — permits any use with proper attribution.
// ---------------------------------------------------------------------------

$page04 = $pdf->addPage();
$pdf->setBookmark('tux.svg', '', 0, -1, 0, 0, 'B', 'black');

$pdf->page->addContent($bfont['out']);

$pdf->addTextCell('tux.svg — complex multi-path illustration (Larry Ewing)', -1, 10, 10, 190, 6, 0, 0, 'T', 'L', null, [], 0, 0, 0, 0, true, true, false, false, false, false, false, false);

// Large centred render
$svg04a = $pdf->addSVG($svgdir . '/tux.svg', 55, 20, 100, 121, $page04['height']);
$pdf->page->addContent($pdf->getSetSVG($svg04a));

// Three scaled thumbnails
$svg04b = $pdf->addSVG($svgdir . '/tux.svg', 10, 148, 55, 66, $page04['height']);
$pdf->page->addContent($pdf->getSetSVG($svg04b));

$svg04c = $pdf->addSVG($svgdir . '/tux.svg', 70, 148, 40, 48, $page04['height']);
$pdf->page->addContent($pdf->getSetSVG($svg04c));

$svg04d = $pdf->addSVG($svgdir . '/tux.svg', 115, 148, 25, 30, $page04['height']);
$pdf->page->addContent($pdf->getSetSVG($svg04d));

// ---------------------------------------------------------------------------
// Page 5 — all four images together (overview grid)
// ---------------------------------------------------------------------------

$page05 = $pdf->addPage();
$pdf->setBookmark('SVG overview grid', '', 0, -1, 0, 0, 'B', 'purple');

$pdf->page->addContent($bfont['out']);

$pdf->addTextCell('SVG overview grid — all four images at equal size', -1, 10, 10, 190, 6, 0, 0, 'T', 'L', null, [], 0, 0, 0, 0, true, true, false, false, false, false, false, false);

// Row 1
$s1 = $pdf->addSVG($svgdir . '/tcpdf_box.svg', 10, 20, 90, 60, $page05['height']);
$pdf->page->addContent($pdf->getSetSVG($s1));

$s2 = $pdf->addSVG($svgdir . '/testsvg.svg', 105, 20, 90, 60, $page05['height']);
$pdf->page->addContent($pdf->getSetSVG($s2));

// Row 2
$s3 = $pdf->addSVG($svgdir . '/testsvgblend.svg', 10, 88, 90, 60, $page05['height']);
$pdf->page->addContent($pdf->getSetSVG($s3));

$s4 = $pdf->addSVG($svgdir . '/tux.svg', 105, 88, 90, 109, $page05['height']);
$pdf->page->addContent($pdf->getSetSVG($s4));

// ---------------------------------------------------------------------------
// Page 6 — abstract_a4_features.svg (full-page portrait)
//
// Dedicated A4 portrait abstract composition that concentrates most SVG
// features currently supported by this library in one single visual sample.
// ---------------------------------------------------------------------------

$page06 = $pdf->addPage();
$pdf->setBookmark('abstract_a4_features.svg', '', 0, -1, 0, 0, 'B', 'orange');

$svg06a = $pdf->addSVG(
    $svgdir . '/abstract_a4_features.svg',
    0,
    0,
    $page06['width'],
    $page06['height'],
    $page06['height'],
);
$pdf->page->addContent($pdf->getSetSVG($svg06a));

// ---------------------------------------------------------------------------
// Output
// ---------------------------------------------------------------------------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
