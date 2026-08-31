<?php

declare(strict_types=1);

/**
 * E014_pdfx5.php
 *
 * PDF/X-5 conformance example.
 *
 * @since       2026-04-25
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

require __DIR__ . '/../vendor/autoload.php';

\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: \Com\Tecnick\Pdf\Page\Unit::Millimeter,
    isunicode: true,
    subsetfont: false,
    compress: true,
    mode: \Com\Tecnick\Pdf\PdfConformance::Pdfx5,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 014');
$pdf->setTitle('PDF/X-5 Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example pdfx5');
$pdf->setPDFFilename('014_pdfx5.pdf');

// ISO 15930-7 and ISO 15930-8 require the destination profile to be embedded.
// A real print job supplies its own condition (for example 'FOGRA39' with the
// matching CMYK profile); the bundled sRGB profile keeps this example self-contained.
$pdf->setSRGB(true);
$pdf->setOutputIntent(identifier: 'sRGB IEC61966-2.1', info: 'sRGB IEC61966-2.1', condition: 'sRGB display condition');

$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$pdf->addPage();
$pdf->page->addContent($font['out']);

$html =
    '<h1>PDF/X-5</h1>'
    . '<p>Mode: pdfx5</p>'
    . '<p>PDF/X-5 builds on the PDF/X-4 family for more advanced print workflows and external references.</p>'
    . '<p>Highlights: minimum PDF 1.6 output, transparency retained, modern PDF/X identification, '
    . 'and the same print-safe suppression of unsupported interactive actions.</p>';

$pdf->addHTMLCell(html: $html, posx: 15, posy: 20, width: 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
