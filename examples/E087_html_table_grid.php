<?php

declare(strict_types=1);

/**
 * E087_html_table_grid.php
 *
 * Visual inspection sheet for HTML table layout: keep-together tables, rows with
 * different cell counts, row height estimates of spanning cells near the page
 * bottom, and explicit cell heights. Each case states the expected result.
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

$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 087');
$pdf->setTitle('HTML Table Grid');
$pdf->setKeywords('TCPDF tc-lib-pdf example html table colspan rowspan height page-break-inside');
$pdf->setPDFFilename('087_html_table_grid.pdf');

$selectFont = function (string $family = 'helvetica', string $style = '', float $size = 10) use ($pdf): void {
    $font = $pdf->font->insert($pdf->pon, $family, $style, $size);
    $pdf->page->addContent($font['out']);
};

// Reference geometry drawn over the cases.
$guideStyle = [
    'lineWidth' => 0.3,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [1, 1],
    'dashPhase' => 0,
    'lineColor' => '#cc2244',
];

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
 * Adds a page with a title and returns the ordinate of the first case.
 */
$drawTitle = function (string $title, string $expected) use ($pdf, $note): float {
    $pdf->addPage();
    $note($title, 10, 8, 190, 'B', 12);
    $note('Expected: ' . $expected, 10, 15, 190, '', 8);

    return 26.0;
};

/**
 * Draws a dashed line at the bottom of the current page region.
 */
$markRegionBottom = function () use ($pdf, $guideStyle, $note): void {
    $region = $pdf->page->getRegion();
    $bottom = $region['RY'] + $region['RH'];
    $pdf->page->addContent($pdf->graph->getLine(5, $bottom, 205, $bottom, $guideStyle));
    $note('region bottom', 170, $bottom - 4, 35);
};

// ----------
// Part 1: a table kept together is moved only when it does not fit.
// ----------

$keepTable =
    '<tr><td colspan="3"><b>HEADING</b></td></tr>'
    . '<tr><td colspan="3">Subheading</td></tr>'
    . \str_repeat('<tr><td>Description<br>sub description text</td><td>middle cell</td><td>right cell</td></tr>', 5)
    . '<tr><td colspan="2">summary</td><td>sum</td></tr></table>';

foreach ([
    'page-break-inside: avoid' => 'style="page-break-inside: avoid;"',
    'nobr="true"' => 'nobr="true"',
] as $label => $attr) {
    $posy = $drawTitle(
        'Keep-together tables (' . $label . ')',
        'the first five tables fit on this page; the sixth does not fit and starts on the next page.',
    );
    $markRegionBottom();
    $selectFont();
    $pdf->addHTMLCell(
        html: \str_repeat('<table ' . $attr . ' border="1">' . $keepTable, 6),
        posx: 10,
        posy: $posy,
        width: 190,
    );
    $note('The sixth table, moved to this page as a whole.', 10, 60, 190);
}

// ----------
// Part 2: rows with different cell counts share one column grid.
// ----------

$posy = $drawTitle(
    'Rows with different cell counts',
    'every cell stays inside the dashed frame (the declared table width); the columns line up across rows.',
);

$gridCases = [
    'later row with more cells' => '<tr><td>A1</td><td>B1</td></tr><tr><td>A2</td><td>B2</td><td>C2</td></tr>',
    'rowspan in the first row' =>
        '<tr><td rowspan="2">A (rowspan 2)</td><td>B1</td></tr>'
            . '<tr><td>B2</td><td>C2</td></tr><tr><td>A3</td><td>B3</td><td>C3</td></tr>',
    'widths on a later row' =>
        '<tr><td>A1</td><td>B1</td></tr>'
            . '<tr><td width="20%">A2 20%</td><td width="30%">B2 30%</td><td width="50%">C2 50%</td></tr>',
    'width on a new column' => '<tr><td>A1</td><td>B1</td></tr><tr><td>A2</td><td>B2</td><td width="50%">C2 50%</td></tr>',
    'colspan wider than the first row' =>
        '<tr><td>A1</td><td>B1</td></tr>'
            . '<tr><td colspan="4">spans four columns</td></tr><tr><td>A3</td><td>B3</td><td>C3</td><td>D3</td></tr>',
];

foreach ($gridCases as $label => $rows) {
    $note($label, 10, $posy, 45);
    $selectFont();
    $pdf->addHTMLCell('<table border="1" cellpadding="2">' . $rows . '</table>', 60, $posy, 140);
    $height = $pdf->getLastBBox()['y'] + $pdf->getLastBBox()['h'] - $posy;
    $frame = $pdf->graph->getRect(59, $posy - 1, 142, \max(10.0, $height + 2), 'D', ['all' => $guideStyle]);
    $pdf->page->addContent($frame);
    $posy += \max(14.0, $height + 8);
}

// ----------
// Part 3: the row estimate of a spanning cell uses the width of all its columns.
// ----------

$spanText = \str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 3);
foreach ([
    'colspan="2" row near the page bottom' =>
        '<tr><td width="50%">A</td><td width="50%">B</td></tr><tr><td colspan="2">' . $spanText . '</td></tr>',
    'single column reference' => '<tr><td>A</td></tr><tr><td>' . $spanText . '</td></tr>',
] as $label => $rows) {
    $drawTitle(
        'Spanning cell near the page bottom: ' . $label,
        'the table starts 15mm above the region bottom and both of its rows stay on this page.',
    );
    $markRegionBottom();
    $region = $pdf->page->getRegion();
    $selectFont();
    $pdf->addHTMLCell(
        '<table border="1" cellpadding="2">' . $rows . '</table>',
        15,
        $region['RY'] + $region['RH'] - 15,
        180,
    );
}

// ----------
// Part 4: explicit cell heights set the minimum row height.
// ----------

$posy = $drawTitle(
    'Cell height: the declared height is the minimum height of the row',
    'each table row has the height in its label; the dashed line marks that height from the table top.',
);

$heightCases = [
    ['<td>',                       'no height: content height',                     0.0],
    ['<td height="100">',          'height="100" (100px = 26.46mm)',                26.46],
    ['<td style="height:100px;">', 'style="height:100px" (26.46mm)',                26.46],
    ['<td style="height:30mm;">',  'style="height:30mm"',                           30.0],
    ['<td style="height:1mm;">',   'height lower than the content: content height', 0.0],
    ['<th style="height:20mm;">',  'th with style="height:20mm"',                   20.0],
];

foreach ($heightCases as [$cell, $label, $expected]) {
    $note($label, 10, $posy, 50);
    $tag = \str_starts_with($cell, '<th') ? 'th' : 'td';
    $selectFont();
    $pdf->addHTMLCell(
        '<table border="1" cellpadding="2"><tr>' . $cell . 'cell</' . $tag . '><td>second cell</td></tr></table>',
        65,
        $posy,
        120,
    );
    if ($expected > 0.0) {
        $pdf->page->addContent($pdf->graph->getLine(62, $posy + $expected, 188, $posy + $expected, $guideStyle));
    }

    $posy += \max(12.0, $expected + 6.0);
}

$note('rowspan="2" with style="height:30mm": the two rows share the height', 10, $posy, 50);
$selectFont();
$pdf->addHTMLCell(
    '<table border="1" cellpadding="2">'
    . '<tr><td rowspan="2" style="height:30mm;">spanning cell</td><td>row 1</td></tr>'
    . '<tr><td>row 2</td></tr></table>',
    65,
    $posy,
    120,
);
$pdf->page->addContent($pdf->graph->getLine(62, $posy + 30.0, 188, $posy + 30.0, $guideStyle));

// ----------
// Part 5: narrow columns keep their text.
// ----------

$posy = $drawTitle(
    'Narrow columns: words wider than a column',
    'every text stays inside its column frame, except in the white-space: nowrap case, which never wraps.',
);

$longword = 'Supercalifragilisticexpialidociousandevenlonger';
$narrowCases = [
    'a word wider than the column is broken between two characters' =>
        '<td width="40">' . $longword . ' text</td><td>next column</td>',
    'hyphenated words break after a hyphen' => '<td width="90">Long-distance well-known state-of-the-art</td><td>next</td>',
    'overflow-wrap: break-word, a 4-character word in a 25px column' =>
        '<td width="25" style="overflow-wrap: break-word">OW25 ' . $longword . '</td><td>next</td>',
    'word-break: break-all' => '<td width="60" style="word-break: break-all">' . $longword . ' text</td><td>next</td>',
    'white-space: nowrap (overflows, as specified by CSS)' =>
        '<td width="40" style="white-space: nowrap">nowrap ' . $longword . '</td><td>next</td>',
];

foreach ($narrowCases as $label => $cells) {
    $note($label, 10, $posy, 50);
    $selectFont();
    $pdf->addHTMLCell('<table border="1" cellpadding="2"><tr>' . $cells . '</tr></table>', 65, $posy, 120);
    $posy = \max($posy + 12.0, $pdf->getLastHTMLCellEnd()['y'] + 6.0);
}

$note('automatic column widths: the bold header "Amount" is not broken', 10, $posy, 50);
$rows = '';
for ($idx = 1; $idx <= 3; ++$idx) {
    $rows .=
        '<tr><td>'
        . $idx
        . '</td><td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td><td style="text-align:right">'
        . \number_format($idx * 12.34, 2)
        . '</td></tr>';
}

$selectFont('dejavusans');
$pdf->addHTMLCell(
    '<table border="1" cellpadding="3" cellspacing="0"><thead><tr style="background-color:#cccccc"><th>#</th>'
    . '<th>Description</th><th>Amount</th></tr></thead>'
    . $rows
    . '</table>',
    65,
    $posy,
    120,
);

// ----------
// Part 6: cell borders are drawn over the cell backgrounds.
// ----------

$posy = $drawTitle(
    'Cell borders over cell backgrounds',
    'zoomed in (800%), every border keeps its full width over the backgrounds of the next rows.',
);

$borderCases = [
    'a 3px red bottom border above a grey row' =>
        '<table cellpadding="2"><tr><td style="border-bottom: 3px solid #ff0000;">row 1 with a red bottom border</td></tr>'
            . '<tr><td style="background-color: #cccccc;">row 2 with a grey background</td></tr></table>',
    'the same with border-collapse: collapse' =>
        '<table cellpadding="2" style="border-collapse: collapse"><tr><td style="border-bottom: 3px solid #ff0000;">'
            . 'row 1 with a red bottom border</td></tr><tr><td style="background-color: #cccccc;">'
            . 'row 2 with a grey background</td></tr></table>',
    'alternating backgrounds with 2px borders' =>
        '<table cellpadding="2">'
            . \str_repeat(
                '<tr><td style="background-color:#ddeeff;border:2px solid #0044aa">blue row</td></tr>'
                . '<tr><td style="background-color:#ffeedd;border:2px solid #aa4400">orange row</td></tr>',
                2,
            )
            . '</table>',
    'a cell spanning rows with a background' =>
        '<table cellpadding="2" border="2"><tr><td rowspan="2" style="background-color:#ffcc00">spanning</td>'
            . '<td style="background-color:#cccccc">row 1</td></tr>'
            . '<tr><td style="background-color:#cccccc">row 2</td></tr></table>',
    'a bordered table inside a cell with a background' =>
        '<table cellpadding="4"><tr><td style="background-color:#ddffdd">'
            . '<table border="2" cellpadding="2"><tr><td style="background-color:#ffffff">inner 1</td></tr>'
            . '<tr><td style="background-color:#eeeeee">inner 2</td></tr></table></td></tr></table>',
];

foreach ($borderCases as $label => $html) {
    $note($label, 10, $posy, 50);
    $selectFont();
    $pdf->addHTMLCell($html, 65, $posy, 120);
    $posy = \max($posy + 12.0, $pdf->getLastHTMLCellEnd()['y'] + 6.0);
}

// ----------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
