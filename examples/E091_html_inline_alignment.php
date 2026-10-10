<?php

declare(strict_types=1);

/**
 * E091_html_inline_alignment.php
 *
 * Visual inspection sheet for the horizontal alignment of HTML lines made of
 * several inline fragments, left-to-right and right-to-left. Each case states the
 * expected result.
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
$pdf->setSubject('tc-lib-pdf example: 091');
$pdf->setTitle('HTML Inline Alignment');
$pdf->setKeywords('TCPDF tc-lib-pdf example html text-align center right justify rtl inline');
$pdf->setPDFFilename('091_html_inline_alignment.pdf');

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
 * Draws a case: a label, the HTML in a 60 mm box at x = 80 mm, the box frame and a
 * dashed guide on its center. Returns the ordinate of the next case.
 */
$drawCase = function (string $label, string $html, float $posy, string $family = 'helvetica') use (
    $pdf,
    $note,
    $selectFont,
    $guideStyle,
): float {
    $note($label, 10, $posy, 65);
    $selectFont($family);
    $pdf->addHTMLCell($html, 80, $posy, 60);
    $height = \max(8.0, $pdf->getLastHTMLCellEnd()['y'] - $posy);
    $pdf->page->addContent($pdf->graph->getRect(80, $posy, 60, $height, 'D', ['all' => $guideStyle]));
    $pdf->page->addContent($pdf->graph->getLine(110, $posy, 110, $posy + $height, $guideStyle));

    return $posy + $height + 6;
};

$lorem = 'Lorem ipsum dolor sit amet, <b>consectetur</b> adipiscing elit, sed do eiusmod';

// ----------
// Part 1: left-to-right lines with inline elements.
// ----------

$posy = $drawTitle(
    'Alignment of lines with inline elements',
    'centered lines are symmetric about the dashed center guide; right-aligned lines end at the right edge.',
);

$posy = $drawCase(
    'center: a line ending before an inline element',
    '<div style="text-align:center">' . $lorem . '</div>',
    $posy,
);
$posy = $drawCase(
    'right: a line ending before an inline element',
    '<div style="text-align:right">' . $lorem . '</div>',
    $posy,
);
$posy = $drawCase(
    'center: table cell with align="center"',
    '<table><tr><td align="center">Lorem ipsum dolor sit amet, <b>consectetur</b> adipiscing elit</td></tr></table>',
    $posy,
);
$posy = $drawCase(
    'center: the last line of a wrapped fragment is followed by "do" in bold',
    '<div style="text-align:center">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed '
    . '<b>do</b> eiusmod tempor</div>',
    $posy,
);
$posy = $drawCase(
    'center: italic start, then a wrapped fragment followed by "elit" in bold',
    '<div style="text-align:center"><i>Lorem ipsum</i> dolor sit amet, consectetur adipiscing '
    . '<b>elit</b> sed do</div>',
    $posy,
);
$posy = $drawCase(
    'center: a wrapped fragment followed by a line break, then an inline element',
    '<div style="text-align:center">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do'
    . '<br />eiusmod <b>tempor</b></div>',
    $posy,
);
$drawCase(
    'justify (reference): every line but the last fills the box',
    '<div style="text-align:justify">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed '
    . '<b>do</b> eiusmod tempor incididunt ut labore et dolore magna aliqua</div>',
    $posy,
);

// ----------
// Part 2: right-to-left runs with inline elements.
// ----------

$posy = $drawTitle(
    'Alignment of right-to-left runs with inline elements',
    'each line follows text-align as the reference without inline elements; fragments are one space apart.',
);

$hebrew =
    "\u{05D0}\u{05D1}\u{05D2}\u{05D3} \u{05D4}\u{05D5}\u{05D6}\u{05D7} \u{05D8}\u{05D9}\u{05DA}\u{05DB} "
    . "\u{05DC}\u{05DD}\u{05DE}\u{05DF} \u{05E0}\u{05E1}\u{05E2}\u{05E3}";
$mixed = $hebrew . ' <b>' . $hebrew . '</b> ' . $hebrew;
$rtl = '<div dir="rtl" style="font-family:dejavusans;text-align:';

foreach (['center', 'left', 'right', 'justify'] as $align) {
    $posy = $drawCase(
        'Hebrew, ' . $align . ', without inline elements (reference)',
        $rtl . $align . '">' . $hebrew . ' ' . $hebrew . ' ' . $hebrew . '</div>',
        $posy,
        'dejavusans',
    );
    $posy = $drawCase(
        'Hebrew, ' . $align . ', with a bold fragment',
        $rtl . $align . '">' . $mixed . '</div>',
        $posy,
        'dejavusans',
    );
}

$drawCase(
    'Latin text in a right-to-left block, center',
    '<div dir="rtl" style="text-align:center">' . $lorem . '</div>',
    $posy,
);

// =============================================================

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF(rawpdf: $rawpdf);
