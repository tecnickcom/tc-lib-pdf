<?php

declare(strict_types=1);

/**
 * E013_pdfx4.php
 *
 * PDF/X-4 conformance example with transparency-capable print output.
 *
 * Also shows the colour space the SVG paints are emitted in: ISO 15930-7 admits
 * a device colour space only when the output intent defines the same space, so
 * the operators follow the printing condition passed to setOutputIntent().
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
    mode: \Com\Tecnick\Pdf\PdfConformance::Pdfx4,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 013');
$pdf->setTitle('PDF/X-4 Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example pdfx4');
$pdf->setPDFFilename('013_pdfx4.pdf');

// ISO 15930-7 and ISO 15930-8 require the destination profile to be embedded.
// A real print job supplies its own condition (for example 'FOGRA39' with the
// matching CMYK profile); the bundled sRGB profile keeps this example self-contained.
$pdf->setSRGB(true);
$pdf->setOutputIntent(identifier: 'sRGB IEC61966-2.1', info: 'sRGB IEC61966-2.1', condition: 'sRGB display condition');

$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$pdf->addPage();
$pdf->page->addContent($font['out']);

$html =
    '<h1>PDF/X-4</h1>'
    . '<p>Mode: pdfx4</p>'
    . '<p>PDF/X-4 modernizes print exchange by allowing live transparency in a color-managed workflow.</p>'
    . '<p>Highlights: minimum PDF 1.6 output, PDF/X-4 identification metadata, transparency retained, '
    . 'encryption disabled, and interactive actions still suppressed for print conformance.</p>';

$pdf->addHTMLCell(html: $html, posx: 15, posy: 20, width: 180);

// The output intent above is the 3-component sRGB profile, an RGB printing
// condition, so the DeviceRGB paints of this SVG are conformant and emitted as
// 'rg' operators. Passing a 4-component profile to setOutputIntent() instead
// makes the same SVG emit DeviceCMYK, and the explicit cmyk() paint is emitted
// as 'k' either way.
$svg =
    '@<svg xmlns="http://www.w3.org/2000/svg" width="100" height="40" viewBox="0 0 100 40">'
    . '<rect x="0" y="0" width="30" height="40" fill="cmyk(0%,0%,0%,100%)"/>'
    . '<rect x="35" y="0" width="30" height="40" fill="#d6457b"/>'
    . '<path d="M 70 0 H 100 V 40 H 70 Z"/>'
    . '</svg>';

$page = $pdf->page->getPage();
$soid = $pdf->addSVG(img: $svg, posx: 15, posy: 70, width: 90, height: 36, pageheight: $page['height']);
$pdf->page->addContent($pdf->getSetSVG(soid: $soid));

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
