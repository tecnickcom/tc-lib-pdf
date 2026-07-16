<?php

/**
 * E078_htmlcell_page_margins.php
 *
 * @since       2026-06-14
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
//
// A single A4 page is created with 100mm top and bottom margins, leaving a
// narrow central content band. getHTMLCell() returns the PDF code for an HTML
// block at absolute page coordinates, so HTML text can be placed in the top
// margin (border), in the central content area, and in the bottom margin
// (border) of the same page.
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    isunicode: true,
    subsetfont: false,
    compress: true,
    mode: '',
    objEncrypt: null,
);

// ----------

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 078');
$pdf->setTitle('addHTMLCell in Page Margins');
$pdf->setKeywords('TCPDF tc-lib-pdf html addHTMLCell margin border top bottom center absolute');
$pdf->setPDFFilename('078_htmlcell_page_margins.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

// ----------

// Top and bottom margins (mm).
$topMargin = 100.0;
$bottomMargin = 100.0;

// Insert font.
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Add a single A4 page with 100mm top and bottom margins.
// 'autobreak' is disabled so placing content in the bottom margin does not
// trigger an automatic page break and everything stays on one page.
$page = $pdf->addPage([
    'format' => 'A4',
    'orientation' => 'P',
    'autobreak' => false,
    'margin' => [
        'PT' => $topMargin,
        'PB' => $bottomMargin,
        'CT' => $topMargin,
        'CB' => $bottomMargin,
    ],
]);

// Add font to the page.
$pdf->page->addContent($bfont['out']);

$pageWidth = (float) $page['width'];
$pageHeight = (float) $page['height'];
$contentTop = $topMargin;
$contentBottom = $pageHeight - $bottomMargin;

// ----------

// Draw the two margin boundary lines so the top/bottom borders are visible.
$lineStyle = [
    'lineWidth' => 0.3,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [2, 2],
    'dashPhase' => 0,
    'lineColor' => 'red',
];

$pdf->page->addContent($pdf->graph->getLine(0, $contentTop, $pageWidth, $contentTop, $lineStyle));
$pdf->page->addContent($pdf->graph->getLine(0, $contentBottom, $pageWidth, $contentBottom, $lineStyle));

// ----------

// Horizontal placement of the HTML cells (centered with 20mm side gaps).
$cellX = 20.0;
$cellW = $pageWidth - (2 * $cellX);

// An explicit cell height is passed so each block is treated as an
// absolutely-positioned bounded box. Without it, content placed below the
// content region (e.g. the bottom margin) is treated as overflow and the
// HTML cursor is reset to the top of the content area.
$cellH = 20.0;

// 1) Text in the TOP border (inside the top margin area).
$pdf->page->addContent($pdf->getHTMLCell(
    html: '<div style="text-align:center; color:#008000;">'
    . '<p><b>TOP BORDER</b><br />This text is placed inside the 100mm top margin.</p>'
    . '<table border="1"><tr><td>Table Cell TOP</td></tr></table>'
    . '</div>',
    posx: $cellX,
    posy: ($topMargin / 2) - 10,
    width: $cellW,
    height: $cellH,
));

// 2) Text in the CENTRAL content area.
$pdf->page->addContent($pdf->getHTMLCell(
    html: '<div style="text-align:center; color:#000080;">'
    . '<p><b>CENTRAL PAGE</b><br />This text is placed in the central content area '
    . 'between the top and bottom margins.</p>'
    . '<table border="1"><tr><td>Table Cell CENTRAL</td></tr></table>'
    . '</div>',
    posx: $cellX,
    posy: (($contentTop + $contentBottom) / 2) - 10,
    width: $cellW,
    height: $cellH,
));

// 3) Text in the BOTTOM border (inside the bottom margin area).
$pdf->page->addContent($pdf->getHTMLCell(
    html: '<div style="text-align:center; color:#800000;">'
    . '<p><b>BOTTOM BORDER</b><br />This text is placed inside the 100mm bottom margin.</p>'
    . '<table border="1"><tr><td>Table Cell BOTTOM</td></tr></table>'
    . '</div>',
    posx: $cellX,
    posy: $contentBottom + ($bottomMargin / 2) - 10,
    width: $cellW,
    height: $cellH,
));

// ----------

// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF(rawpdf: $rawpdf);

//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
