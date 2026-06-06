<?php

/**
 * E076_absolute_position.php
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
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 076');
$pdf->setTitle('Minimal Example');
$pdf->setKeywords('TCPDF tc-lib-pdf absolute positon');
$pdf->setPDFFilename('076_absolute_position.pdf');

// Line style
$style1 = [
    'lineWidth' => 0.5,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => 'green',
    'fillColor' => 'blue',
];

// Insert font
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

// ----------

// Add first page in Portrait mode (P)
$pageP = $pdf->addPage(['format' => 'A4', 'orientation' => 'P']);

// Add font to the page
$pdf->page->addContent($bfont['out']);

// Draw page diagonals
$pdf->page->addContent($pdf->graph->getLine(0, 0, $pageP['width'], $pageP['height'], $style1));
$pdf->page->addContent($pdf->graph->getLine(0, $pageP['height'], $pageP['width'], 0, $style1));

// ----------

// Add first page in Landscape mode (L)
$pageL = $pdf->addPage(['format' => 'A4', 'orientation' => 'L']);

// Draw page diagonals
$pdf->page->addContent($pdf->graph->getLine(0, 0, $pageL['width'], $pageL['height'], $style1));
$pdf->page->addContent($pdf->graph->getLine(0, $pageL['height'], $pageL['width'], 0, $style1));

// /\/\/\/\/\

// Add content to previous pages

// ----------

// Select the page to change
$pgP = $pdf->setCurrentPage($pageP['pid']);

$cxP = $pgP['width'] / 2;
$cyP = $pgP['height'] / 2;

$pdf->page->addContent(
    $pdf->getTextCell('THIS TEXT TOP-LEFT STARTS AT THE PAGE CENTER POINT', $cxP, $cyP),
    $pgP['pid'],
);

// ----------

$pgL = $pdf->setCurrentPage($pageL['pid']);

$cxL = $pgL['width'] / 2;
$cyL = $pgL['height'] / 2;

$pdf->page->addContent(
    $pdf->getTextCell('THIS TEXT TOP-LEFT STARTS AT THE PAGE CENTER POINT', $cxL, $cyL),
    $pgL['pid'],
);

// ----------

// Get the PDF content
$rawpdf = $pdf->getOutPDFString();

// Render the PDF content
$pdf->renderPDF(rawpdf: $rawpdf);
