<?php
/**
 * E055_page_reorder_booklet.php
 *
 * @since       2026-05-01
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

/**
 * Demonstrate page reordering using tc-lib-pdf-page's Page::move() method.
 *
 * Scenario:
 *   - Add pages in "draft" logical order: Introduction, Appendix, Chapter 1, Chapter 2.
 *   - After authoring, reorder so the final reading order is:
 *       1. Introduction, 2. Chapter 1, 3. Chapter 2, 4. Appendix
 *   - Each page shows its original draft index vs. its final booklet position.
 *   - A booklet-spread grid is drawn on a landscape summary page to show how pages
 *     pair up for saddle-stitch imposition (spread 1: pages 4 & 1; spread 2: pages 2 & 3).
 */

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 055');
$pdf->setTitle('Page Reorder and Booklet Imposition');
$pdf->setKeywords('TCPDF tc-lib-pdf example page reorder booklet imposition spread');
$pdf->setPDFFilename('E055_page_reorder_booklet.pdf');

$pdf->enableDefaultPageContent();

// Insert default font before first page.
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);
$bfontB = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 14);

// -----------------------------------------------------------------------
// Phase 1 – Author pages in draft order (Introduction, Appendix, Ch1, Ch2)
// -----------------------------------------------------------------------

// Page index 0 – Introduction
$page0 = $pdf->addPage(['format' => 'A5']);
$pdf->page->addContent($bfontB['out']);
$pdf->page->addContent($pdf->getTextCell('Introduction', 15, 20, 120, 0, 0, 1, 'T', 'L'));
$pdf->page->addContent($bfont['out']);
$pdf->page->addContent($pdf->getTextCell('(Draft index 0)', 15, 36, 120, 0, 0, 1, 'T', 'L'));
$pdf->page->addContent($pdf->getTextCell(
    'Welcome to the tc-lib-pdf page-reorder demo. This page was authored first'
    . ' and will remain in position 0 after reordering.',
    15,
    46,
    120,
    0,
    0,
    1,
    'T',
    'L'
));

// Page index 1 – Appendix (will be moved to the end later)
$page1 = $pdf->addPage(['format' => 'A5']);
$pdf->page->addContent($bfontB['out']);
$pdf->page->addContent($pdf->getTextCell('Appendix', 15, 20, 120, 0, 0, 1, 'T', 'L'));
$pdf->page->addContent($bfont['out']);
$pdf->page->addContent($pdf->getTextCell('(Draft index 1 – will move to index 3)', 15, 36, 120, 0, 0, 1, 'T', 'L'));
$pdf->page->addContent($pdf->getTextCell(
    'This Appendix page was authored second but belongs at the end.'
    . ' Page::move() will relocate it after Chapter 2.',
    15,
    46,
    120,
    0,
    0,
    1,
    'T',
    'L'
));

// Page index 2 – Chapter 1
$page2 = $pdf->addPage(['format' => 'A5']);
$pdf->page->addContent($bfontB['out']);
$pdf->page->addContent($pdf->getTextCell('Chapter 1', 15, 20, 120, 0, 0, 1, 'T', 'L'));
$pdf->page->addContent($bfont['out']);
$pdf->page->addContent($pdf->getTextCell('(Draft index 2 – will move to index 1)', 15, 36, 120, 0, 0, 1, 'T', 'L'));
$pdf->page->addContent($pdf->getTextCell(
    'Chapter 1 content. After reordering this becomes the second page (index 1),'
    . ' immediately after the Introduction.',
    15,
    46,
    120,
    0,
    0,
    1,
    'T',
    'L'
));

// Page index 3 – Chapter 2
$page3 = $pdf->addPage(['format' => 'A5']);
$pdf->page->addContent($bfontB['out']);
$pdf->page->addContent($pdf->getTextCell('Chapter 2', 15, 20, 120, 0, 0, 1, 'T', 'L'));
$pdf->page->addContent($bfont['out']);
$pdf->page->addContent($pdf->getTextCell('(Draft index 3 – will move to index 2)', 15, 36, 120, 0, 0, 1, 'T', 'L'));
$pdf->page->addContent($pdf->getTextCell(
    'Chapter 2 content. After reordering this becomes the third page (index 2),'
    . ' between Chapter 1 and the Appendix.',
    15,
    46,
    120,
    0,
    0,
    1,
    'T',
    'L'
));

// -----------------------------------------------------------------------
// Phase 2 – Reorder using Page::move()
//
// Current order:  [0:Intro] [1:Appendix] [2:Ch1] [3:Ch2]
// Target order:   [0:Intro] [1:Ch1] [2:Ch2] [3:Appendix]
//
// Step A: move Ch1 (index 2) to index 1 → [Intro, Ch1, Appendix, Ch2]
// Step B: move Ch2 (now index 3) to index 2 → [Intro, Ch1, Ch2, Appendix]
//
// Note: Page::move() only moves a page to a LOWER (earlier) index.
// -----------------------------------------------------------------------

$pdf->page->move(2, 1);
// Order is now: [0:Intro] [1:Ch1] [2:Appendix] [3:Ch2]

$pdf->page->move(3, 2);
// Order is now: [0:Intro] [1:Ch1] [2:Ch2] [3:Appendix]

// -----------------------------------------------------------------------
// Phase 3 – Booklet imposition summary page (A4 landscape)
//
// For a 4-page saddle-stitch booklet the imposition pairs are:
//   Physical sheet front: page 4 (right) | page 1 (left)  → spread 1
//   Physical sheet back:  page 2 (left)  | page 3 (right) → spread 2
// -----------------------------------------------------------------------

$summaryPage = $pdf->addPage(['format' => 'A4', 'orientation' => 'L']);
$pdf->page->addContent($bfontB['out']);
$pdf->page->addContent($pdf->getTextCell(
    'Booklet Imposition Summary (saddle-stitch, 4 pages)',
    15,
    12,
    260,
    0,
    0,
    1,
    'T',
    'C'
));
$pdf->page->addContent($bfont['out']);

// Draw spread grid
$graph = $pdf->graph;

// Spread 1 – outer sheet (pages 4 & 1)
$spread1X = 15.0;
$spread1Y = 30.0;
$spreadW = 118.0;
$spreadH = 75.0;
$halfW = $spreadW / 2;

// outer spread box
$pdf->page->addContent(
    $graph->getRect($spread1X, $spread1Y, $spreadW, $spreadH, 'D', [], [])
);
// centre fold line
$pdf->page->addContent(
    $graph->getLine($spread1X + $halfW, $spread1Y, $spread1X + $halfW, $spread1Y + $spreadH)
);
// labels
$pdf->page->addContent($pdf->getTextCell(
    'Spread 1 – Outer sheet',
    $spread1X,
    $spread1Y - 6,
    $spreadW,
    0,
    0,
    1,
    'T',
    'L'
));
$pdf->page->addContent($pdf->getTextCell(
    'Page 4  (Appendix)',
    $spread1X + 2,
    $spread1Y + 4,
    $halfW - 4,
    0,
    0,
    1,
    'T',
    'L'
));
$pdf->page->addContent($pdf->getTextCell('Right side of sheet', $spread1X + 2, $spread1Y + 12, $halfW - 4, 0, 0, 1, 'T', 'L'));
$pdf->page->addContent($pdf->getTextCell(
    'Page 1  (Introduction)',
    $spread1X + $halfW + 2,
    $spread1Y + 4,
    $halfW - 4,
    0,
    0,
    1,
    'T',
    'L'
));
$pdf->page->addContent($pdf->getTextCell('Left side of sheet', $spread1X + $halfW + 2, $spread1Y + 12, $halfW - 4, 0, 0, 1, 'T', 'L'));

// Spread 2 – inner sheet (pages 2 & 3)
$spread2X = $spread1X + $spreadW + 20.0;

$pdf->page->addContent(
    $graph->getRect($spread2X, $spread1Y, $spreadW, $spreadH, 'D', [], [])
);
$pdf->page->addContent(
    $graph->getLine($spread2X + $halfW, $spread1Y, $spread2X + $halfW, $spread1Y + $spreadH)
);
$pdf->page->addContent($pdf->getTextCell(
    'Spread 2 – Inner sheet',
    $spread2X,
    $spread1Y - 6,
    $spreadW,
    0,
    0,
    1,
    'T',
    'L'
));
$pdf->page->addContent($pdf->getTextCell(
    'Page 2  (Chapter 1)',
    $spread2X + 2,
    $spread1Y + 4,
    $halfW - 4,
    0,
    0,
    1,
    'T',
    'L'
));
$pdf->page->addContent($pdf->getTextCell('Left side of sheet', $spread2X + 2, $spread1Y + 12, $halfW - 4, 0, 0, 1, 'T', 'L'));
$pdf->page->addContent($pdf->getTextCell(
    'Page 3  (Chapter 2)',
    $spread2X + $halfW + 2,
    $spread1Y + 4,
    $halfW - 4,
    0,
    0,
    1,
    'T',
    'L'
));
$pdf->page->addContent($pdf->getTextCell('Right side of sheet', $spread2X + $halfW + 2, $spread1Y + 12, $halfW - 4, 0, 0, 1, 'T', 'L'));

// Fold / cut annotation
$pdf->page->addContent($bfont['out']);
$pdf->page->addContent($pdf->getTextCell(
    'Fold along centre fold lines, then saddle-stitch through the fold.'
    . ' The final reading order after binding: Introduction → Chapter 1 → Chapter 2 → Appendix.',
    15,
    120,
    260,
    0,
    0,
    1,
    'T',
    'L'
));

// -----------------------------------------------------------------------
// Output
// -----------------------------------------------------------------------
$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
