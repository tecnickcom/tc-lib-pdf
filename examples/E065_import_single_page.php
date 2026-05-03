<?php
/**
 * E065_import_single_page.php
 *
 * @since       2026-05-03
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

// ---- Step 1: create a source PDF to import from ----

$src = new \Com\Tecnick\Pdf\Tcpdf();
$bfont = $src->font->insert($src->pon, 'helvetica', '', 14);

$srcPage = $src->addPage();
$src->page->addContent($bfont['out']);
$src->addHTMLCell('<h1>Source document</h1><p>This page will be imported.</p>', 15, 20, 160);

$sourcePdfData = $src->getOutPDFString();

// ---- Step 2: create a new document and import the source page ----

$pdf = new \Com\Tecnick\Pdf\Tcpdf();
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Register the source document from its raw bytes.
$sourceId = $pdf->setImportSourceData($sourcePdfData);

// Count pages available in the source.
$pageCount = $pdf->getSourcePageCount($sourceId);

// Import page 1 as a reusable Form XObject template.
$tpl = $pdf->importPage($sourceId, 1);

// Add a new page to the destination document.
$page = $pdf->addPage();
$pdf->page->addContent($bfont['out']);

// Place the imported page as a thumbnail (100 mm wide) at (20, 20).
// The height is computed automatically to preserve the aspect ratio.
$placed = $pdf->useImportedPage($tpl, 20, 20, 140);

// Add an annotation below the imported page.
$pdf->addHTMLCell(
    '<p>Imported ' . $pageCount . ' page(s). Placed page 1 above (scaled).</p>',
    15,
    (float) $placed['y'] + (float) $placed['height'] + 5,
    160
);

// Get and render the PDF content.
$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
