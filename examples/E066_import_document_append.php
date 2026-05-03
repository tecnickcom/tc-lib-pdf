<?php
/**
 * E066_import_document_append.php
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

// ---- Step 1: build a multi-page source PDF ----

$src = new \Com\Tecnick\Pdf\Tcpdf();
$bfont = $src->font->insert($src->pon, 'helvetica', '', 14);

$pages = ['First page', 'Second page', 'Third page'];
foreach ($pages as $idx => $label) {
    $srcPage = $src->addPage();
    $src->page->addContent($bfont['out']);
    $src->addHTMLCell(
        '<h2>Source: ' . \htmlspecialchars($label) . '</h2>'
        . '<p>Page ' . ($idx + 1) . ' of ' . \count($pages) . ' in the source document.</p>',
        15,
        20,
        160
    );
}

$sourcePdfData = $src->getOutPDFString();

// ---- Step 2: create destination document with an intro page ----

$pdf = new \Com\Tecnick\Pdf\Tcpdf();
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);

$pdf->addPage();
$pdf->page->addContent($bfont['out']);
$pdf->addHTMLCell(
    '<h1>Document append demo</h1>'
    . '<p>The following pages are imported from a separate source document.</p>',
    15,
    20,
    160
);

// ---- Step 3: register source and append all its pages ----

$sourceId = $pdf->setImportSourceData($sourcePdfData);
$pageCount = $pdf->getSourcePageCount($sourceId);

// appendDocument adds one new destination page per source page and places the template.
$templates = $pdf->appendDocument($sourceId);

// ---- Step 4: add a closing page with a summary ----

$pdf->addPage();
$pdf->page->addContent($bfont['out']);
$pdf->addHTMLCell(
    '<p>Appended ' . \count($templates) . ' page(s) from source ('
    . $pageCount . ' available).</p>',
    15,
    20,
    160
);

// Render
$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
