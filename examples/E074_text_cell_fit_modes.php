<?php

/**
 * E074_text_cell_fit_modes.php
 *
 * Demonstrates getTextCell/addTextCell fit modes:
 * T, S, F and disabled behavior.
 *
 * @since       2026-05-19
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
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 074');
$pdf->setTitle('Text Cell Fit Modes');
$pdf->setKeywords('TCPDF tc-lib-pdf example text cell fit T S F');
$pdf->setPDFFilename('074_text_cell_fit_modes.pdf');

$titleFont = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 14);

// ----------

$pdf->addPage();
$pdf->page->addContent($titleFont['out']);
$pdf->page->addContent($pdf->getTextCell(txt: 'getTextCell() fit mode', posx: 15, posy: 15, valign: 'T', halign: 'L'));

$baseFont = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);

$cellStyle = [
    'all' => [
        'lineWidth' => 0.3,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => '#4a5b70',
        'fillColor' => '#ffffff',
    ],
];

$pdf->page->addContent($baseFont['out']);

$pdf->page->addContent($pdf->getTextCell(
    txt: 'Long text scenario (overflow):',
    posx: 15,
    posy: 30,
    valign: 'T',
    halign: 'L',
));

$longSample = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
$modesL = ['T', 'S', 'F', ''];
$modeY = 40;

foreach ($modesL as $mode) {
    $label = $mode === '' ? 'fit=(disabled)' : 'fit=' . $mode;

    $pdf->page->addContent($pdf->getTextCell(
        txt: $label,
        posx: 15,
        posy: $modeY,
        width: 24,
        height: 6,
        valign: 'T',
        halign: 'R',
    ));

    // one line
    $pdf->page->addContent($pdf->getTextCell(
        txt: $longSample,
        posx: 41,
        posy: $modeY,
        width: 160,
        height: 5,
        valign: 'T',
        halign: 'L',
        styles: $cellStyle,
        fit: $mode,
    ));

    // multiple lines
    $pdf->page->addContent($pdf->getTextCell(
        txt: $longSample,
        posx: 41,
        posy: $modeY + 8,
        width: 80,
        height: 10,
        valign: 'T',
        halign: 'L',
        styles: $cellStyle,
        fit: $mode,
    ));

    $modeY += 25;
}

// ----------

$page = $pdf->addPage();
$pid = $page['pid'];

$pdf->page->addContent($titleFont['out']);
$pdf->page->addContent($pdf->getTextCell(txt: 'addTextCell() fit mode', posx: 15, posy: 15, valign: 'T', halign: 'L'));

$pdf->page->addContent($baseFont['out']);

$pdf->page->addContent($pdf->getTextCell(
    txt: 'Long text scenario (overflow):',
    posx: 15,
    posy: 30,
    valign: 'T',
    halign: 'L',
));

$modeY = 40;

foreach ($modesL as $mode) {
    $label = $mode === '' ? 'fit=(disabled)' : 'fit=' . $mode;

    $pdf->page->addContent($pdf->getTextCell(
        txt: $label,
        posx: 15,
        posy: $modeY,
        width: 24,
        height: 6,
        valign: 'T',
        halign: 'R',
    ));

    // one line
    $pdf->addTextCell(
        txt: $longSample,
        pid: $pid,
        posx: 41,
        posy: $modeY,
        width: 160,
        height: 5,
        valign: 'T',
        halign: 'L',
        styles: $cellStyle,
        fit: $mode,
    );

    // multiple lines
    $pdf->addTextCell(
        txt: $longSample,
        pid: $pid,
        posx: 41,
        posy: $modeY + 8,
        width: 80,
        height: 10,
        valign: 'T',
        halign: 'L',
        styles: $cellStyle,
        fit: $mode,
    );

    $modeY += 25;
}

// ----------
// /\/\/\/\/\
// ----------

$pdf->addPage();
$pdf->page->addContent($titleFont['out']);
$pdf->page->addContent($pdf->getTextCell(
    txt: 'getTextCell() fit mode long word (hyphenated)',
    posx: 15,
    posy: 15,
    width: 180,
    valign: 'T',
    halign: 'L',
));

$baseFont = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);

$cellStyle = [
    'all' => [
        'lineWidth' => 0.3,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => '#4a5b70',
        'fillColor' => '#ffffff',
    ],
];

$pdf->page->addContent($baseFont['out']);

$pdf->page->addContent($pdf->getTextCell(
    txt: 'Long word scenario (overflow):',
    posx: 15,
    posy: 30,
    valign: 'T',
    halign: 'L',
));

// Define a custom hyphenation pattern that splits a word after each character.
// See loadTexHyphenPatterns() for language-specific hyphenation patterns.
$singleCharPattern = [];
foreach (range('a', 'z') as $letter) {
    $singleCharPattern[$letter] = $letter . '1';
}
$pdf->setTexHyphenPatterns(patterns: $singleCharPattern);

$longWord = 'Loremipsumdolorsitametconsecteturadipiscingelitseddoeiusmodtemporincididuntutlaboreetdoloremagnaaliqua.';
$modesL = ['T', 'S', 'F', ''];
$modeY = 40;

foreach ($modesL as $mode) {
    $label = $mode === '' ? 'fit=(disabled)' : 'fit=' . $mode;

    $pdf->page->addContent($pdf->getTextCell(
        txt: $label,
        posx: 15,
        posy: $modeY,
        width: 24,
        height: 6,
        valign: 'T',
        halign: 'R',
    ));

    // one line
    $pdf->page->addContent($pdf->getTextCell(
        txt: $longWord,
        posx: 41,
        posy: $modeY,
        width: 160,
        height: 5,
        valign: 'T',
        halign: 'L',
        styles: $cellStyle,
        fit: $mode,
    ));

    // multiple lines
    $pdf->page->addContent($pdf->getTextCell(
        txt: $longWord,
        posx: 41,
        posy: $modeY + 8,
        width: 80,
        height: 10,
        valign: 'T',
        halign: 'L',
        styles: $cellStyle,
        fit: $mode,
    ));

    $modeY += 25;
}

// ----------

$page = $pdf->addPage();
$pid = $page['pid'];

$pdf->page->addContent($titleFont['out']);
$pdf->page->addContent($pdf->getTextCell(
    txt: 'addTextCell() fit mode long word (hyphenated)',
    posx: 15,
    posy: 15,
    width: 180,
    valign: 'T',
    halign: 'L',
));

$pdf->page->addContent($baseFont['out']);

$pdf->page->addContent($pdf->getTextCell(
    txt: 'Long word scenario (overflow):',
    posx: 15,
    posy: 30,
    valign: 'T',
    halign: 'L',
));

$modeY = 40;

foreach ($modesL as $mode) {
    $label = $mode === '' ? 'fit=(disabled)' : 'fit=' . $mode;

    $pdf->page->addContent($pdf->getTextCell(
        txt: $label,
        posx: 15,
        posy: $modeY,
        width: 24,
        height: 6,
        valign: 'T',
        halign: 'R',
    ));

    // one line
    $pdf->addTextCell(
        txt: $longWord,
        pid: $pid,
        posx: 41,
        posy: $modeY,
        width: 160,
        height: 5,
        valign: 'T',
        halign: 'L',
        styles: $cellStyle,
        fit: $mode,
    );

    // multiple lines
    $pdf->addTextCell(
        txt: $longWord,
        pid: $pid,
        posx: 41,
        posy: $modeY + 8,
        width: 80,
        height: 10,
        valign: 'T',
        halign: 'L',
        styles: $cellStyle,
        fit: $mode,
    );

    $modeY += 25;
}

// ----------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
