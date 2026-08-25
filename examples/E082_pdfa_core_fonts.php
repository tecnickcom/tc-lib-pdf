<?php

/**
 * E082_pdfa_core_fonts.php
 *
 * Visual check of the PDF/A core font substitutes.
 *
 * In a PDF/A mode the standard-14 families are replaced by the embeddable PDFA*
 * Type1 programs, which are emitted with /Encoding /WinAnsiEncoding and select
 * their glyphs by name. This example prints the codes whose glyph names differ
 * between the substitutes and WinAnsiEncoding, so a name that the embedded
 * program does not define shows up as a blank rather than a character:
 *
 *   223  ß  germandbls
 *   181  µ  mu
 *   183  ·  periodcentered
 *
 * The Symbol face is emitted with a /Differences array instead, and is printed
 * with the Adobe Symbol codes that carry the same risk, followed by the
 * extensible delimiter pieces used to build tall brackets.
 *
 * Text extraction is independent of the glyph names, so copying from the
 * rendered page returns the correct characters even when a glyph is missing.
 *
 * @since       2026-08-21
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

require __DIR__ . '/../vendor/autoload.php';

\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: \Com\Tecnick\Pdf\Page\Unit::Millimeter,
    isunicode: true,
    mode: \Com\Tecnick\Pdf\PdfConformance::Pdfa3b,
);
$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setTitle('PDF/A Core Font Substitutes');
$pdf->setSubject('tc-lib-pdf example: 082 - PDF/A core font glyph coverage');
$pdf->setKeywords('TCPDF tc-lib-pdf pdfa core fonts glyph names WinAnsiEncoding Symbol');
$pdf->setPDFFilename('E082_pdfa_core_fonts.pdf');
$pdf->setLanguage(code: 'en-US');

$pdf->addPage();

/**
 * Render a run of single-byte codes with the font currently selected.
 *
 * The codes are written straight into the content stream, so that each one
 * selects its glyph through the encoding the font dictionary declares.
 *
 * @param array<int, int> $codes Character codes to print.
 */
$putCodes = static function (array $codes, float $posx, float $posy) use ($pdf): void {
    $esc = '';
    foreach ($codes as $code) {
        $esc .= \sprintf('\%03o', $code);
    }

    // getTextCell() takes the top of the cell, this takes the baseline: shift it down by
    // the ascent of the 14pt face so both sit on the same line
    $pdf->page->addContent(\sprintf(
        'BT %F %F Td (%s) Tj ET',
        $pdf->toPoints($posx),
        $pdf->toYPoints($posy + 3.7),
        $esc,
    ));
};

$title = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 13);
$pdf->page->addContent($title['out']);
$pdf->page->addContent($pdf->getTextCell(txt: 'PDF/A core font substitutes', posx: 15, posy: 14));

$note = $pdf->font->insert($pdf->pon, 'helvetica', '', 8);
$pdf->page->addContent($note['out']);
$pdf->page->addContent($pdf->getTextCell(
    txt: 'Every cell below must show a glyph. A blank marks a name the embedded font program does not define.',
    posx: 15,
    posy: 21,
));

// The three WinAnsi codes, shown in every substituted family and style.
$posy = 32;
foreach (['helvetica', 'times', 'courier'] as $family) {
    foreach (['', 'B', 'I', 'BI'] as $style) {
        $font = $pdf->font->insert($pdf->pon, $family, $style, 11);
        $pdf->page->addContent($font['out']);
        $pdf->page->addContent($pdf->getTextCell(
            txt: \str_pad($family . ($style === '' ? '' : '-' . $style), 14)
                . ' Straße  µm  a·b  ÄÖÜäöü  fi fl  «»  †‡',
            posx: 15,
            posy: $posy,
        ));
        $posy += 8;
    }

    $posy += 3;
}

// Symbol declares its own encoding through a /Differences array.
$symbol = $pdf->font->insert($pdf->pon, 'symbol', '', 14);

$pdf->page->addContent($note['out']);
$pdf->page->addContent($pdf->getTextCell(txt: 'symbol: named glyphs', posx: 15, posy: $posy));
$pdf->page->addContent($symbol['out']);
$putCodes([39, 74, 118, 161, 192, 210, 211, 212, 224], 60, $posy);
$posy += 9;

$pdf->page->addContent($note['out']);
$pdf->page->addContent($pdf->getTextCell(txt: 'symbol: delimiter pieces', posx: 15, posy: $posy));
$pdf->page->addContent($symbol['out']);
$putCodes([230, 231, 232, 246, 247, 248, 233, 234, 235, 236, 237, 238, 239, 244, 160], 60, $posy);
$posy += 9;

// ZapfDingbats carries no /Encoding entry: its built-in vector selects the glyphs.
$pdf->page->addContent($note['out']);
$pdf->page->addContent($pdf->getTextCell(txt: 'zapfdingbats', posx: 15, posy: $posy));
$dingbats = $pdf->font->insert($pdf->pon, 'zapfdingbats', '', 14);
$pdf->page->addContent($dingbats['out']);
$putCodes([33, 34, 35, 36, 37, 38, 110, 111, 112, 113], 60, $posy);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
