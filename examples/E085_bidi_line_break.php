<?php

declare(strict_types=1);

/**
 * E085_bidi_line_break.php
 *
 * Visual inspection grid for the reading order of mixed-direction text that
 * wraps over several lines: RTL runs in LTR paragraphs, LTR runs in RTL
 * paragraphs, truncation, page regions and HTML page splits.
 *
 * @since       2026-10-03
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
$pdf->setSubject('tc-lib-pdf example: 085');
$pdf->setTitle('Bidi Line Break Reading Order');
$pdf->setKeywords('TCPDF tc-lib-pdf example bidi rtl ltr hebrew arabic line break reading order');
$pdf->setPDFFilename('085_bidi_line_break.pdf');

// Every word of a run carries its number, so the reading order can be checked
// without reading Hebrew or Arabic: the numbers of an RTL run grow from right to
// left on each line, those of an LTR run from left to right, and the numbers
// continue from one line to the next.

/**
 * Returns the numbered Hebrew words from $first to $last.
 */
$hebrew = static function (int $first, int $last): string {
    $words = [];
    for ($num = $first; $num <= $last; ++$num) {
        $words[] = "\u{05DE}\u{05D9}\u{05DC}\u{05D4}" . $num;
    }

    return \implode(' ', $words);
};

/**
 * Returns the numbered Arabic words from $first to $last.
 */
$arabic = static function (int $first, int $last): string {
    $words = [];
    for ($num = $first; $num <= $last; ++$num) {
        $words[] = "\u{0643}\u{0644}\u{0645}\u{0629}" . $num;
    }

    return \implode(' ', $words);
};

/**
 * Returns the numbered Latin words from $first to $last.
 */
$latin = static function (int $first, int $last): string {
    $words = [];
    for ($num = $first; $num <= $last; ++$num) {
        $words[] = 'w' . $num;
    }

    return \implode(' ', $words);
};

// The metrics used to break the lines come from the current font of the stack,
// so each block selects its own font right before it is written.
$selectFont = function (string $family = 'dejavusans', string $style = '', float $size = 11) use ($pdf): void {
    $font = $pdf->font->insert($pdf->pon, $family, $style, $size);
    $pdf->page->addContent($font['out']);
};

// The cell border shows the width the lines are broken to.
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

$labelx = 8.0;
$labelw = 32.0;
$posx = 44.0;
$width = 80.0;
$captionx = $posx + $width + 4.0;
$captionw = 210.0 - $captionx - 8.0;

/**
 * Writes a small text block (label or caption) at absolute page coordinates.
 */
$note = function (string $txt, float $posx, float $posy, float $width, string $halign) use ($pdf, $selectFont): void {
    $selectFont('helvetica', '', 7);
    $pdf->page->addContent($pdf->getTextCell(
        txt: $txt,
        posx: $posx,
        posy: $posy,
        width: $width,
        valign: \Com\Tecnick\Pdf\TextVAlign::Top,
        halign: $halign,
        drawcell: false,
    ));
};

/**
 * Draws one labelled case with its expected result and returns the ordinate of
 * the next one.
 *
 * @param array<string, mixed> $args Named arguments for addTextCell.
 */
$drawCase = function (string $label, string $expected, float $posy, array $args) use (
    $pdf,
    $selectFont,
    $note,
    $cellStyle,
    $labelx,
    $labelw,
    $posx,
    $width,
    $captionx,
    $captionw,
): float {
    $note($label, $labelx, $posy, $labelw, 'R');
    $note($expected, $captionx, $posy, $captionw, 'L');

    $selectFont();
    $pdf->addTextCell(...\array_merge([
        'posx' => $posx,
        'posy' => $posy,
        'width' => $width,
        'valign' => \Com\Tecnick\Pdf\TextVAlign::Top,
        'halign' => \Com\Tecnick\Pdf\TextHAlign::Left,
        'styles' => $cellStyle,
    ], $args));
    $bbox = $pdf->getLastCellBBox();

    return \max($posy + 18.0, $bbox['y'] + $bbox['h'] + 6.0);
};

/**
 * Writes a page title and returns the ordinate of the first case.
 */
$drawTitle = function (string $title, ?array $page = null) use ($pdf, $selectFont, $labelx): float {
    $pdf->addPage($page ?? []);
    $selectFont('helvetica', 'B', 12);
    $pdf->page->addContent($pdf->getTextCell(
        txt: $title,
        posx: $labelx,
        posy: 12,
        width: 190,
        valign: \Com\Tecnick\Pdf\TextVAlign::Top,
        halign: \Com\Tecnick\Pdf\TextHAlign::Left,
        drawcell: false,
    ));

    return 24.0;
};

// ----------
// Text cells: runs of the opposite direction wrapping over several lines.
// ----------

$posy = $drawTitle('Mixed direction text wrapping over several lines');

$posy = $drawCase(
    'RTL run in LTR text, 2 lines',
    'Line 1: "Start:", then Hebrew words 1, 2, 3, ... read from right to left. '
    . 'Line 2: the next Hebrew words, again growing from right to left, then ":end".',
    $posy,
    ['txt' => 'Start: ' . $hebrew(1, 10) . ' :end'],
);
$posy = $drawCase(
    'RTL run in LTR text, 4 lines',
    'Each line holds the next Hebrew numbers, growing from right to left; the first line holds word 1.',
    $posy,
    ['txt' => 'Start: ' . $hebrew(1, 24) . ' :end'],
);
$posy = $drawCase(
    'LTR run in RTL text',
    'RTL paragraph, right aligned. Line 1: Hebrew word 1 at the right edge, then w1, w2, ... '
    . 'read from left to right. The w numbers continue on the next lines.',
    $posy,
    [
        'txt' => $hebrew(1, 1) . ' ' . $latin(1, 30) . ' ' . $hebrew(2, 2),
        'forcedir' => \Com\Tecnick\Unicode\TextDirection::Rtl,
        'halign' => \Com\Tecnick\Pdf\TextHAlign::Right,
    ],
);
$posy = $drawCase(
    'Arabic run in LTR text',
    'Joined Arabic words 1, 2, 3, ... growing from right to left on each line, '
    . 'continuing on the next line. The lam-alef ligature is shaped.',
    $posy,
    ['txt' => 'Start: ' . $arabic(1, 12) . " \u{0644}\u{0627} :end"],
);
// Every second word of the RTL run is in brackets. A bracket pair that directly
// follows the LTR text would resolve to LTR and split the run (UAX #9 rule N0).
$bracketed = [];
foreach (\explode(' ', $hebrew(1, 14)) as $idx => $word) {
    $bracketed[] = ($idx % 2) === 1 ? '(' . $word . ')' : $word;
}

$posy = $drawCase(
    'Brackets in an RTL run',
    'Every second Hebrew word is in brackets. The numbers grow from right to left on each '
    . 'line and continue on the next one; every bracket pair encloses its word.',
    $posy,
    ['txt' => 'Start: ' . \implode(' ', $bracketed) . ' :end'],
);
$drawCase(
    'Two paragraphs',
    'Paragraph 1 (LTR): w1, w2, ... Paragraph 2 (RTL, starts with Hebrew): '
    . 'Hebrew word 1 at the right end of its first line, then 2, 3, ...',
    $posy,
    ['txt' => $latin(1, 16) . "\n" . $hebrew(1, 14)],
);

// ----------
// Break opportunities and truncation.
// ----------

$posy = $drawTitle('Break opportunities and truncation in mixed direction text');

$pdf->setTexHyphenPatterns(['hyphen' => 'hy3phen', 'phena' => 'phen1a', 'ation' => 'a1tion']);
$pdf->enableZeroWidthBreakPoints(true);
$posy = $drawCase(
    'Hyphens and zero width breaks',
    'Comma separated Hebrew words break after a comma, keeping the right to left '
    . 'numbering across lines; the comma ending a line is drawn at its left end. '
    . '"hyphenation" breaks with a visible hyphen at the end of its line.',
    $posy,
    [
        'txt' =>
            'Start: '
                . \str_replace(' ', ',', $hebrew(1, 18))
                . ' hyphenation hyphenation hyphenation hyphenation hyphenation',
        'width' => 50.0,
    ],
);
$pdf->setTexHyphenPatterns([]);
$pdf->enableZeroWidthBreakPoints(false);

$posy = $drawCase(
    'Truncated RTL text',
    'Only the first lines fit: Hebrew words 1, 2, 3, ... from the right, '
    . 'and the ellipsis at the left end of the last line. The text is cut by character, '
    . 'so the last kept word can be shortened (word 12 cut to "1").',
    $posy,
    [
        'txt' => $hebrew(1, 40),
        'height' => 12.0,
        'forcedir' => \Com\Tecnick\Unicode\TextDirection::Rtl,
        'halign' => \Com\Tecnick\Pdf\TextHAlign::Right,
        'fit' => \Com\Tecnick\Pdf\TextFitMode::Truncate,
    ],
);
$drawCase(
    'Truncated LTR text with an RTL run',
    'Only the first lines fit: "Start:", then Hebrew words 1, 2, 3, ... from the right. '
    . 'The ellipsis ends the LTR paragraph, so it follows the RTL run at the right end of the '
    . 'last line.',
    $posy,
    [
        'txt' => 'Start: ' . $hebrew(1, 40),
        'height' => 12.0,
        'fit' => \Com\Tecnick\Pdf\TextFitMode::Truncate,
    ],
);

// ----------
// HTML: single text fragments, justification and a page split.
// ----------

$posy = $drawTitle('HTML paragraphs with mixed direction runs');

/**
 * Draws one labelled HTML block with its expected result and returns the
 * ordinate of the next one.
 */
$drawHtmlCase = function (string $label, string $expected, float $posy, string $html) use (
    $pdf,
    $selectFont,
    $note,
    $cellStyle,
    $labelx,
    $labelw,
    $posx,
    $width,
    $captionx,
    $captionw,
): float {
    $note($label, $labelx, $posy, $labelw, 'R');
    $note($expected, $captionx, $posy, $captionw, 'L');

    $selectFont();
    $pdf->addHTMLCell($html, $posx, $posy, $width, 0, null, $cellStyle);
    $bbox = $pdf->getLastBBox();

    return \max($posy + 18.0, $bbox['y'] + $bbox['h'] + 10.0);
};

$posy = $drawHtmlCase(
    'LTR paragraph',
    'Line 1: "Start:", then Hebrew words 1, 2, 3, ... from the right; the next lines continue the numbering.',
    $posy,
    '<p>Start: ' . $hebrew(1, 16) . ' :end</p>',
);
$posy = $drawHtmlCase(
    'Justified, with a following fragment',
    'Same order as above, justified line by line; the last line ends with ":end" and a bold "x".',
    $posy,
    '<p style="text-align:justify">Start: ' . $hebrew(1, 16) . ' :end <b>x</b></p>',
);
$posy = $drawHtmlCase(
    'RTL block with an LTR run',
    'Right aligned. Hebrew word 1 at the right edge of line 1, then w1, w2, ... from left '
    . 'to right, continuing on the next lines.',
    $posy,
    '<div dir="rtl">' . $hebrew(1, 1) . ' ' . $latin(1, 30) . ' ' . $hebrew(2, 2) . '</div>',
);

$note(
    'Page split: the paragraph at the bottom of this page continues on the next page. Line by line the '
    . 'Hebrew numbers keep growing from right to left, and the last line on the next page ends with ":end".',
    $labelx,
    255.0,
    190.0,
    'L',
);
$selectFont();
$pdf->addHTMLCell('<p>Start: ' . $hebrew(1, 40) . ' :end</p>', $posx, 268.0, $width, 0, null, $cellStyle);

// ----------
// Page regions: a paragraph flowing from one column to the next.
// ----------

$regiony = 40.0;
$regionh = 40.0;
$regionw = 80.0;
$regiontop = $drawTitle('Mixed direction paragraph flowing across two page regions', [
    'region' => [
        ['RX' => 15.0, 'RY' => $regiony, 'RW' => $regionw, 'RH' => $regionh],
        ['RX' => 115.0, 'RY' => $regiony, 'RW' => $regionw, 'RH' => $regionh],
    ],
]);
$note(
    'RTL paragraph: the left column is filled first, then the text continues in the right column. '
    . 'The Hebrew numbers and the w numbers continue across the column boundary: none is repeated '
    . 'or missing.',
    $labelx,
    $regiontop,
    190.0,
    'L',
);

$selectFont();
$pdf->addTextCell(
    // NOTE: addTextCell() coordinates are relative to the current page region
    txt: $hebrew(1, 24) . ' ' . $latin(1, 40) . ' ' . $hebrew(25, 54),
    posx: 0,
    posy: 0,
    width: $regionw,
    valign: \Com\Tecnick\Pdf\TextVAlign::Top,
    halign: \Com\Tecnick\Pdf\TextHAlign::Right,
    drawcell: false,
    forcedir: \Com\Tecnick\Unicode\TextDirection::Rtl,
);

// ----------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
