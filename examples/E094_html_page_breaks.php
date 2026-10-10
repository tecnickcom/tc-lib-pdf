<?php

declare(strict_types=1);

/**
 * E094_html_page_breaks.php
 *
 * Visual inspection sheet for page breaks requested by the HTML content: CSS
 * page-break-before inside a styled table, and the tcpdf pageBreak tag. Each case
 * states the expected result.
 *
 * @since       2026-10-10
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

$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 094');
$pdf->setTitle('HTML Page Breaks');
$pdf->setKeywords('TCPDF tc-lib-pdf example html page-break-before tcpdf pagebreak table');
$pdf->setPDFFilename('094_html_page_breaks.pdf');

$selectFont = function (string $family = 'helvetica', string $style = '', float $size = 10) use ($pdf): void {
    $font = $pdf->font->insert($pdf->pon, $family, $style, $size);
    $pdf->page->addContent($font['out']);
};

/**
 * Writes a note in absolute page coordinates.
 */
$note = function (string $txt, float $posx, float $posy, float $width, string $style = '', float $size = 8) use (
    $pdf,
    $selectFont,
): void {
    $selectFont('helvetica', $style, $size);
    $pdf->page->addContent($pdf->getTextCell(
        txt: $txt,
        posx: $posx,
        posy: $posy,
        width: $width,
        valign: \Com\Tecnick\Pdf\TextVAlign::Top,
        halign: \Com\Tecnick\Pdf\TextHAlign::Left,
    ));
};

/**
 * Adds a page with 15 mm margins and a title, and returns the ordinate of the case.
 */
$drawTitle = function (string $title, string $expected) use ($pdf, $note): float {
    // The pages added by the page breaks keep these margins.
    $pdf->addPage(['margin' => ['PT' => 15, 'PB' => 15, 'CT' => 15, 'CB' => 15]]);
    $note($title, 15, 15, 180, 'B', 12);
    $note('Expected: ' . $expected, 15, 22, 180, '', 8);

    return 35.0;
};

// ----------
// A page break before a row of a table with a background and a border.
// ----------

$posy = $drawTitle(
    'page-break-before on a table row',
    'rows 1 to 3 on this page and rows 4 to 6 at the top margin of the next page, '
    . 'each part with the table background and frame.',
);
$rows = '';
for ($idx = 1; $idx <= 6; ++$idx) {
    $rows .=
        '<tr'
        . ($idx === 4 ? ' style="page-break-before:always"' : '')
        . '><td>row '
        . $idx
        . '</td><td>second column</td></tr>';
}

$selectFont();
$pdf->addHTMLCell(
    '<table border="1" cellpadding="3" style="background-color:#ddeeff">' . $rows . '</table>',
    15,
    $posy,
    180,
);

// ----------
// The tcpdf page break tag.
// ----------

$posy = $drawTitle('tcpdf pageBreak tag', 'the paragraph after the tag starts at the top margin of the next page.');
$selectFont();
$pdf->addHTMLCell(
    '<p>Paragraph before the tag.</p><tcpdf method="pagebreak" />'
    . '<p>Paragraph after the tag, at the top of the page.</p>',
    15,
    $posy + 60,
    180,
);

$posy = $drawTitle(
    'tcpdf pageBreak tag with a pagebreak attribute',
    'one page break only: the paragraph after the tag is on the next page, which is not preceded by an empty page.',
);
$selectFont();
$pdf->addHTMLCell(
    '<p>Paragraph before the tag.</p><tcpdf method="pagebreak" pagebreak="true" />'
    . '<p>Paragraph after the tag, on the page right after this one.</p>',
    15,
    $posy,
    180,
);

// ----------
// A forced page break continues at the top margin.
// ----------

$posy = $drawTitle(
    'page-break-before on a paragraph of a block that starts low on the page',
    'the second paragraph is at the top margin of the next page, not 100 mm down as on this page.',
);
$selectFont();
$pdf->addHTMLCell(
    '<p>First paragraph, 100 mm from the top of the page.</p>'
    . '<p style="page-break-before:always">Second paragraph, at the top margin.</p>',
    15,
    100,
    180,
);

// =============================================================

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF(rawpdf: $rawpdf);
