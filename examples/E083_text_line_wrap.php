<?php

/**
 * E083_text_line_wrap.php
 *
 * Visual inspection grid for the line wrapping corner cases of
 * addTextCell/getTextCell: first line offset, break opportunities
 * and text direction.
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

// autoloader when using Composer
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 083');
$pdf->setTitle('Text Line Wrap Corner Cases');
$pdf->setKeywords('TCPDF tc-lib-pdf example text line wrap offset indent hyphen');
$pdf->setPDFFilename('083_text_line_wrap.pdf');

// The metrics used to break the lines come from the current font of the stack,
// so each block selects its own font right before it is written.
$selectFont = function (string $family = 'helvetica', string $style = '', float $size = 11) use ($pdf): void {
    $font = $pdf->font->insert($pdf->pon, $family, $style, $size);
    $pdf->page->addContent($font['out']);
};

// The cell border shows whether the wrapped lines stay inside the given width.
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

// The marker shows where the first line is asked to start.
$markStyle = [
    'lineWidth' => 0.3,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [1, 1],
    'dashPhase' => 0,
    'lineColor' => '#cc2244',
];

$labelx = 8.0;
$labelw = 32.0;
$posx = 44.0;
$width = 150.0;
$height = 22.0;
$step = 27.0;

$word = 'AAAAAAAAAA-BBBBBBBBBB-CCCCCCCCCC';
$text = 'Alpha beta gamma delta epsilon zeta eta theta iota kappa lambda.';

/**
 * Draws one labelled case and returns the ordinate of the next one.
 *
 * @param array<string, mixed> $args Named arguments for addTextCell.
 */
$drawCase = function (string $label, float $posy, array $args) use (
    $pdf,
    $selectFont,
    $cellStyle,
    $markStyle,
    $labelx,
    $labelw,
    $posx,
    $width,
    $height,
    $step,
): float {
    $selectFont('helvetica', '', 7);
    $pdf->addTextCell(
        txt: $label,
        posx: $labelx,
        posy: $posy,
        width: $labelw,
        valign: \Com\Tecnick\Pdf\TextVAlign::Top,
        halign: \Com\Tecnick\Pdf\TextHAlign::Right,
        drawcell: false,
    );

    $family = isset($args['family']) && \is_string($args['family']) ? $args['family'] : 'helvetica';
    unset($args['family']);
    $selectFont($family);
    $pdf->addTextCell(...\array_merge([
        'posx' => $posx,
        'posy' => $posy,
        'width' => $width,
        'height' => $height,
        'valign' => \Com\Tecnick\Pdf\TextVAlign::Top,
        'styles' => $cellStyle,
    ], $args));
    $bbox = $pdf->getLastTextBBox();

    $offset = isset($args['offset']) && \is_float($args['offset']) ? $args['offset'] : 0.0;
    if ($offset > 0.0 && $offset < $width) {
        // A right aligned first line starts from the right, so the offset is taken there.
        $fromright = ($args['halign'] ?? null) === \Com\Tecnick\Pdf\TextHAlign::Right;
        $markx = $fromright ? $posx + $width - $offset : $posx + $offset;
        $pdf->page->addContent($pdf->graph->getLine($markx, $posy, $markx, $posy + $height, $markStyle));
    }

    $selectFont('helvetica', '', 7);
    $pdf->addTextCell(
        txt: \sprintf('%.1f .. %.1f mm', $bbox['x'], $bbox['x'] + $bbox['w']),
        posx: $labelx,
        posy: $posy + 8,
        width: $labelw,
        valign: \Com\Tecnick\Pdf\TextVAlign::Top,
        halign: \Com\Tecnick\Pdf\TextHAlign::Right,
        drawcell: false,
    );

    return $posy + $step;
};

/**
 * Draws one labelled HTML block twice, wide and narrow, and returns the ordinate
 * of the next case. The CSS text-indent maps to the first line offset.
 */
$drawHtmlCase = function (string $label, float $posy, string $html) use (
    $pdf,
    $selectFont,
    $cellStyle,
    $labelx,
    $labelw,
    $posx,
    $height,
    $step,
): float {
    $selectFont('helvetica', '', 7);
    $pdf->addTextCell(
        txt: $label,
        posx: $labelx,
        posy: $posy,
        width: $labelw,
        valign: \Com\Tecnick\Pdf\TextVAlign::Top,
        halign: \Com\Tecnick\Pdf\TextHAlign::Right,
        drawcell: false,
    );

    $selectFont('dejavusans');
    $pdf->addHTMLCell($html, $posx, $posy, 95.0, $height, null, $cellStyle);
    $selectFont('dejavusans');
    $pdf->addHTMLCell($html, $posx + 100.0, $posy, 50.0, $height, null, $cellStyle);

    return $posy + $step;
};

/**
 * Writes a page title and returns the ordinate of the first case.
 */
$drawTitle = function (string $title) use ($pdf, $selectFont, $labelx): float {
    $pdf->addPage();
    $selectFont('helvetica', 'B', 12);
    $pdf->addTextCell(
        txt: $title,
        posx: $labelx,
        posy: 12,
        width: 190,
        valign: \Com\Tecnick\Pdf\TextVAlign::Top,
        halign: \Com\Tecnick\Pdf\TextHAlign::Left,
        drawcell: false,
    );

    return 22.0;
};

// ----------
// First line offset: the offset shortens the first line only.
// ----------

$posy = $drawTitle('First line offset (the dashed mark is the requested start of the first line)');

// Measure the test word to leave it 2mm less room than it needs on the first line.
// getTextCell() returns the PDF code without writing it, and records the metrics.
$selectFont();
$pdf->getTextCell(txt: $word, posx: $posx, posy: $posy, width: $width);
$tight = $width - $pdf->getLastTextBBox()['w'] + 2.0;

$posy = $drawCase('no offset', $posy, [
    'txt' => $text,
]);
$posy = $drawCase('first word fits', $posy, [
    'txt' => $word . ' ' . $text,
    'offset' => $tight - 12.0,
]);
$posy = $drawCase('first word too wide', $posy, [
    'txt' => $word . ' ' . $text,
    'offset' => $tight,
]);
$posy = $drawCase('word wider than the cell', $posy, [
    'txt' => $word . $word . ' ' . $text,
    'offset' => $tight,
]);
$posy = $drawCase('offset over the width', $posy, [
    'txt' => $text,
    'offset' => $width + 20.0,
]);
$posy = $drawCase('negative offset (hanging)', $posy, [
    'txt' => $text,
    'offset' => -4.0,
]);
$posy = $drawCase('offset and line break', $posy, [
    'txt' => "Alpha beta\ngamma delta epsilon zeta eta theta iota kappa lambda mu nu xi.",
    'offset' => $tight,
]);
$posy = $drawCase('offset and justified', $posy, [
    'txt' => $word . ' ' . $text,
    'offset' => $tight,
    'halign' => \Com\Tecnick\Pdf\TextHAlign::Justify,
]);
$drawCase('offset and centered', $posy, [
    'txt' => $word . ' ' . $text,
    'offset' => $tight,
    'halign' => \Com\Tecnick\Pdf\TextHAlign::Center,
]);

// ----------
// Break opportunities: where a line is allowed to end.
// ----------

$posy = $drawTitle('Break opportunities');

$longword = 'Loremipsumdolorsitametconsecteturadipiscingelitseddoeiusmodtemporincididuntutlaboreetdolore';
$breakable = \str_replace(
    '-',
    "\u{200B}",
    'Lorem-ipsum-dolor-sitamet-consectetur-adipiscing-elit-seddo-eiusmod-tempor-incididunt-utlabore',
);

$posy = $drawCase('spaces only', $posy, [
    'txt' => $text . ' ' . $text,
]);
$posy = $drawCase('no break point', $posy, [
    'txt' => $longword,
]);

// Break after every character to place a hyphenated break next to the cell edge.
$charPatterns = [];
foreach (\range('a', 'z') as $letter) {
    $charPatterns[$letter] = $letter . '1';
}

$pdf->setTexHyphenPatterns(patterns: $charPatterns);
$posy = $drawCase('hyphenated', $posy, [
    'txt' => $longword,
]);
$posy = $drawCase('hyphenated with offset', $posy, [
    'txt' => $longword,
    'offset' => 60.0,
]);
$pdf->setTexHyphenPatterns(patterns: []);

$pdf->enableZeroWidthBreakPoints(true);
$posy = $drawCase('automatic break points', $posy, [
    'txt' => 'https://www.example.com/a/very/long/path/that/has/no/space/in/it/at/all/and/ends/here/index.html',
]);
$pdf->enableZeroWidthBreakPoints(false);

$posy = $drawCase('zero width spaces', $posy, [
    'txt' => $breakable,
]);

$hebrew =
    "\u{05D0}\u{05D1}\u{05D2}\u{05D3} \u{05D4}\u{05D5}\u{05D6}\u{05D7} \u{05D8}\u{05D9}\u{05DA}\u{05DB} "
    . "\u{05DC}\u{05DD}\u{05DE}\u{05DF} \u{05E0}\u{05E1}\u{05E2}\u{05E3} \u{05E4}\u{05E5}\u{05E6}\u{05E7}";

$posy = $drawCase('RTL', $posy, [
    'txt' => $hebrew . ' ' . $hebrew,
    'family' => 'dejavusans',
    'halign' => \Com\Tecnick\Pdf\TextHAlign::Right,
    'forcedir' => \Com\Tecnick\Unicode\TextDirection::Rtl,
]);
$drawCase('RTL with offset', $posy, [
    'txt' => $hebrew . ' ' . $hebrew,
    'family' => 'dejavusans',
    'offset' => 60.0,
    'halign' => \Com\Tecnick\Pdf\TextHAlign::Right,
    'forcedir' => \Com\Tecnick\Unicode\TextDirection::Rtl,
]);

// ----------
// CSS text-indent: the first line offset of the HTML engine, taken from the side
// the block starts from. Every case is drawn on a wide and on a narrow block.
// ----------

$posy = $drawTitle('CSS text-indent: indented from the start side of the block');

$ltrbody = 'Alpha beta gamma delta epsilon zeta eta theta iota kappa.';
$rtlbody = $hebrew . ' ' . $hebrew;

$posy = $drawHtmlCase('LTR', $posy, '<div>' . $ltrbody . '</div>');
$posy = $drawHtmlCase('LTR indent', $posy, '<div style="text-indent:25mm">' . $ltrbody . '</div>');
$posy = $drawHtmlCase(
    'LTR centered indent',
    $posy,
    '<div style="text-align:center;text-indent:25mm">' . $ltrbody . '</div>',
);
$posy = $drawHtmlCase('LTR hanging', $posy, '<div style="text-indent:-4mm">' . $ltrbody . '</div>');
$posy = $drawHtmlCase('RTL', $posy, '<div dir="rtl">' . $rtlbody . '</div>');
$posy = $drawHtmlCase('RTL indent', $posy, '<div dir="rtl" style="text-indent:25mm">' . $rtlbody . '</div>');
$drawHtmlCase(
    'RTL centered indent',
    $posy,
    '<div dir="rtl" style="text-align:center;text-indent:25mm">' . $rtlbody . '</div>',
);

// ----------
// Continuation flow: successive calls that carry on the same line.
// ----------

$flowy = $drawTitle('Continuation flow: each run starts where the previous one ended');
$selectFont();

$runs = [
    'Alpha beta gamma delta epsilon zeta.',
    'Eta theta iota kappa.',
    $word,
    'Lambda mu nu xi omicron pi rho sigma tau.',
    $word,
    'Upsilon phi chi psi omega.',
];

$flowh = $pdf->toUnit($pdf->font->getCurrentFont()['height']);
$spacew = $pdf->toUnit($pdf->font->getCharWidth(0x20));
$posy = $flowy;
$offset = 0.0;

foreach ($runs as $run) {
    $pdf->addTextCell(
        txt: $run,
        posx: $posx,
        posy: $posy,
        width: $width,
        offset: $offset,
        valign: \Com\Tecnick\Pdf\TextVAlign::Top,
        halign: \Com\Tecnick\Pdf\TextHAlign::Left,
        drawcell: false,
    );

    // The next run continues after the last rendered line of this one.
    // getLastBBox() is that line, while getLastTextBBox() would be the box around
    // every line of the run, which reaches further right whenever the run wrapped.
    $bbox = $pdf->getLastBBox();
    $offset = $bbox['x'] + $bbox['w'] + $spacew - $posx;
    $posy = $bbox['y'] + $bbox['h'] - $flowh;
}

$pdf->page->addContent($pdf->graph->getRect($posx, $flowy, $width, $posy + $flowh - $flowy, 'D', [
    'all' => $markStyle,
]));

// ----------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
