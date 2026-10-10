<?php

declare(strict_types=1);

/**
 * E089_import_empty_resources.php
 *
 * Imports the pages of a small source PDF that is built inline: a page with an
 * empty resource dictionary, a page without a resource dictionary and a page
 * with a font resource. Each imported page is a Form XObject with a valid
 * /Resources dictionary.
 *
 * @since       2026-10-09
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: local file reads (images, fonts, attachments) are restricted to an allowlist of
// trusted paths that covers this package tree, so run the examples in place. To read assets
// from other locations, list them in the 'allowedPaths' entry of the fileOptions constructor
// parameter (see E047_remote_resources_security.php).

// NOTE: run make fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// ---- Step 1: build the source PDF ----

/**
 * Returns the body of an unfiltered stream object.
 */
$stream = static fn(string $data): string => '<< /Length ' . \strlen($data) . " >>\nstream\n" . $data . "\nendstream";

// A5 pages. The first two contents only paint paths, so they need no resources.
$objects = [
    1 => '<< /Type /Catalog /Pages 2 0 R >>',
    2 => '<< /Type /Pages /Kids [3 0 R 5 0 R 7 0 R] /Count 3 /MediaBox [0 0 420 595] >>',
    3 => '<< /Type /Page /Parent 2 0 R /Resources << >> /Contents 4 0 R >>',
    4 => $stream("0.85 0.90 1 rg 40 40 340 515 re f\n0 0 0.6 RG 4 w 40 40 340 515 re S"),
    5 => '<< /Type /Page /Parent 2 0 R /Contents 6 0 R >>',
    6 => $stream("1 0.92 0.80 rg 40 40 340 515 re f\n0.6 0.3 0 RG 4 w 40 40 340 515 re S"),
    7 => '<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 9 0 R >> >> /Contents 8 0 R >>',
    8 => $stream("0.85 1 0.85 rg 40 40 340 515 re f\nBT /F1 18 Tf 60 500 Td (Source page 3: font resource) Tj ET"),
    9 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
];

$sourcePdfData = "%PDF-1.7\n";
$xref = '';
foreach ($objects as $num => $body) {
    $xref .= \sprintf("%010d 00000 n \n", \strlen($sourcePdfData));
    $sourcePdfData .= $num . " 0 obj\n" . $body . "\nendobj\n";
}

$xrefStart = \strlen($sourcePdfData);
$size = \count($objects) + 1;
$sourcePdfData .=
    "xref\n0 "
    . $size
    . "\n0000000000 65535 f \n"
    . $xref
    . "trailer\n<< /Size "
    . $size
    . " /Root 1 0 R >>\nstartxref\n"
    . $xrefStart
    . "\n%%EOF\n";

// ---- Step 2: import every page into a new document ----

$pdf = new \Com\Tecnick\Pdf\Tcpdf();
$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 089');
$pdf->setTitle('Import Pages With Empty Resources');
$pdf->setKeywords('TCPDF tc-lib-pdf example import empty resources form xobject');
$pdf->setPDFFilename('089_import_empty_resources.pdf');

$sourceId = $pdf->setImportSourceData($sourcePdfData);

$labels = [
    1 => 'Source page 1: /Resources << >>. Expected: blue frame.',
    2 => 'Source page 2: no /Resources key. Expected: orange frame.',
    3 => 'Source page 3: font resource. Expected: green frame and the source text.',
];

foreach ($labels as $pageNum => $label) {
    $pdf->addPageFromImport($sourceId, $pageNum);
    $font = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 11);
    $pdf->page->addContent($font['out']);
    $pdf->addTextCell(txt: $label, posx: 20, posy: 30, width: 108, drawcell: false);
}

$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 9);
$pdf->page->addContent($font['out']);
$pdf->addTextCell(
    txt: 'Every imported page is a Form XObject with a /Resources dictionary: '
    . 'the file opens without errors and "qpdf --check" reports no warnings.',
    posx: 20,
    posy: 150,
    width: 108,
    drawcell: false,
);

// ----------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
