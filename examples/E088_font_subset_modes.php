<?php

declare(strict_types=1);

/**
 * E088_font_subset_modes.php
 *
 * Visual inspection sheet for the subsetting mode of fonts used through the
 * text, HTML and SVG renderers in a document that does not subset by default.
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

// The document does not subset fonts by default.
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: \Com\Tecnick\Pdf\Page\Unit::Millimeter,
    isunicode: true,
    subsetfont: false,
    compress: true,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 088');
$pdf->setTitle('Font Subset Modes');
$pdf->setKeywords('TCPDF tc-lib-pdf example font subset mode html svg');
$pdf->setPDFFilename('088_font_subset_modes.pdf');

// FreeSerif is asked to be subset, FreeMono to be embedded whole.
$serif = $pdf->font->insert($pdf->pon, 'freeserif', '', 11, null, null, '', true);
$mono = $pdf->font->insert($pdf->pon, 'freemono', '', 9, null, null, '', false);

$pdf->addPage();

/**
 * Selects a font of the stack for the following text.
 */
$selectFont = function (string $family, string $style = '', float $size = 11) use ($pdf): void {
    $font = $pdf->font->insert($pdf->pon, $family, $style, $size);
    $pdf->page->addContent($font['out']);
};

/** @var array<int, array{0: string, 1: bool}> $rows */
$rows = [];

/**
 * Records the subsetting mode of FreeSerif after a rendering step.
 */
$record = function (string $step) use ($pdf, $serif, &$rows): void {
    $rows[] = [$step, $pdf->font->getFont($serif['key'])['subset']];
};

$record('after insert(subset: true)');

$selectFont('helvetica', 'B', 14);
$pdf->addTextCell(txt: 'Font subset modes', posx: 15, posy: 12, width: 180, drawcell: false);

$selectFont('freeserif');
$pdf->addTextCell(
    txt: 'Text cell in FreeSerif: the quick brown fox jumps over the lazy dog.',
    posx: 15,
    posy: 24,
    width: 180,
    drawcell: false,
);
$record('after addTextCell()');

// The HTML renderer inserts the fonts it uses without stating a subset mode.
$pdf->addHTMLCell(
    html: '<p style="font-family:freeserif;font-size:11pt;">HTML cell in FreeSerif: '
    . 'regular and <span style="font-size:14pt;">larger</span> text.</p>',
    posx: 15,
    posy: 34,
    width: 180,
);
$record('after addHTMLCell()');

// The SVG renderer also inserts its fonts without stating a subset mode.
$svg =
    '@<svg xmlns="http://www.w3.org/2000/svg" width="180" height="12" viewBox="0 0 180 12">'
    . '<text x="0" y="9" font-family="freeserif" font-size="9">SVG text in FreeSerif.</text></svg>';
$soid = $pdf->addSVG(img: $svg, posx: 15, posy: 48, width: 180, height: 12);
$pdf->page->addContent($pdf->getSetSVG(soid: $soid));
$record('after addSVG()');

// Summary: FreeSerif must stay subset after every step.
$html =
    '<table border="1" cellpadding="3" style="font-family:helvetica;font-size:9pt;">'
    . '<tr><th width="50%">FreeSerif step</th><th width="25%">subset</th><th width="25%">expected</th></tr>';
foreach ($rows as [$step, $subset]) {
    $html .=
        '<tr><td>'
        . $step
        . '</td>'
        . '<td style="color:'
        . ($subset ? '#007700' : '#cc0000')
        . ';">'
        . ($subset ? 'true' : 'false')
        . '</td>'
        . '<td>true</td></tr>';
}

$monosubset = $pdf->font->getFont($mono['key'])['subset'];
$html .=
    '<tr><td>FreeMono, insert(subset: false)</td>'
    . '<td>'
    . ($monosubset ? 'true' : 'false')
    . '</td><td>false</td></tr></table>';

$html .=
    '<p style="font-family:helvetica;font-size:9pt;">Check the embedded fonts with '
    . '<code>pdffonts</code>: FreeSerif is listed as a subset (the name has a six-letter prefix '
    . 'and the "sub" column is "yes"), FreeMono is embedded whole.</p>';

$pdf->addHTMLCell(html: $html, posx: 15, posy: 64, width: 180);

$selectFont('freemono', '', 9);
$pdf->addTextCell(txt: 'FreeMono text, embedded whole.', posx: 15, posy: 130, width: 180, drawcell: false);

// ----------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
