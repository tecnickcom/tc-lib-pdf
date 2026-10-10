<?php

declare(strict_types=1);

/**
 * E086_cjk_line_break.php
 *
 * Visual inspection sheet for the line breaking of Chinese, Japanese and Korean
 * text, which has no spaces between words, and of the line break controls
 * (soft hyphen, zero width space and <wbr>). Each case is drawn in a frame and
 * states the expected result.
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
$pdf->setSubject('tc-lib-pdf example: 086');
$pdf->setTitle('CJK Line Break');
$pdf->setKeywords('TCPDF tc-lib-pdf example cjk chinese japanese korean line break kinsoku wbr');
$pdf->setPDFFilename('086_cjk_line_break.pdf');

$selectFont = function (string $family = 'helvetica', string $style = '', float $size = 10) use ($pdf): void {
    $font = $pdf->font->insert($pdf->pon, $family, $style, $size);
    $pdf->page->addContent($font['out']);
};

// The frame shows whether the wrapped lines stay inside the given width.
$frameStyle = [
    'lineWidth' => 0.3,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [1, 1],
    'dashPhase' => 0,
    'lineColor' => '#cc2244',
];

$labelx = 10.0;
$labelw = 55.0;
$posx = 70.0;
$width = 90.0;

/**
 * Writes a note in absolute page coordinates.
 */
$note = function (string $txt, float $posy, float $size = 8, string $style = '') use (
    $pdf,
    $selectFont,
    $labelx,
    $labelw,
): void {
    $selectFont('helvetica', $style, $size);
    $pdf->page->addContent($pdf->getTextCell(
        txt: $txt,
        posx: $labelx,
        posy: $posy,
        width: $labelw,
        valign: \Com\Tecnick\Pdf\TextVAlign::Top,
        halign: \Com\Tecnick\Pdf\TextHAlign::Left,
    ));
};

/**
 * Adds a page with a title and returns the ordinate of the first case.
 */
$drawTitle = function (string $title) use ($pdf, $selectFont): float {
    $pdf->addPage();
    $selectFont('helvetica', 'B', 12);
    $pdf->page->addContent($pdf->getTextCell(txt: $title, posx: 10, posy: 8, width: 190));

    return 20.0;
};

/**
 * Draws one HTML case inside a dashed frame and returns the ordinate of the next one.
 */
$drawCase = function (string $label, float $posy, string $html, string $font = 'unifont_jp', float $minh = 8.0) use (
    $pdf,
    $selectFont,
    $note,
    $frameStyle,
    $posx,
    $width,
): float {
    $note($label, $posy);
    $selectFont($font);
    $pdf->addHTMLCell(html: $html, posx: $posx, posy: $posy, width: $width);
    $bbox = $pdf->getLastBBox();
    $height = \max($minh, $bbox['y'] + $bbox['h'] - $posy);
    $pdf->page->addContent($pdf->graph->getRect($posx, $posy, $width, $height, 'D', ['all' => $frameStyle]));

    return $posy + $height + 8.0;
};

$japanese =
    '吾輩は猫である。名前はまだ無い。どこで生れたかとんと見当がつかぬ。'
    . '何でも薄暗いじめじめした所でニャーニャー泣いていた事だけは記憶している。';
$chinese = '天下皆知美之為美，斯惡已；皆知善之為善，斯不善已。故有無相生，難易相成，長短相形，高下相傾，音聲相和，前後相隨。';
$korean = '모든 인간은 태어날 때부터 자유로우며 그 존엄과 권리에 있어 동등하다. 인간은 천부적으로 이성과 양심을 부여받았으며 서로 형제애의 정신으로 행동하여야 한다.';

// ----------
// Text without spaces between words wraps inside the frame.
// ----------

$posy = $drawTitle('CJK text: every line stays inside the dashed frame');

$posy = $drawCase('Japanese (expected: wrapped inside the frame)', $posy, '<p>' . $japanese . '</p>');
$posy = $drawCase('Chinese (expected: wrapped inside the frame)', $posy, '<p>' . $chinese . '</p>', 'unifont');
$posy = $drawCase(
    'Korean (expected: wrapped inside the frame, between syllables)',
    $posy,
    '<p>' . $korean . '</p>',
    'unifont',
);
$posy = $drawCase(
    'Latin and Japanese (expected: breaks between the scripts, the bracketed word kept together)',
    $posy,
    '<p>Unicode対応のテキストを「正しく」改行する。PDF文書のレイアウトでUnicodeの改行規則を使う。</p>',
);
$posy = $drawCase(
    'Line start and end rules (expected: no line starts with a comma, a full stop, a prolonged sound mark '
    . 'or a small kana, no line ends with an opening bracket)',
    $posy,
    '<p>あいうえおかきくけこさしすせ、そたちつてと「なにぬねの」はひふへほまみむめもゃゅょ。</p>',
);
$posy = $drawCase(
    'Japanese in a 50% table cell (expected: wrapped inside the cell)',
    $posy,
    '<table border="1" cellpadding="2"><tr><td width="50%">'
    . $japanese
    . '</td><td width="50%">'
    . 'The quick brown fox jumps over the lazy dog.</td></tr></table>',
    'unifont_jp',
    30.0,
);

// getTextCell() follows the same rules.
$note('getTextCell() (expected: wrapped inside the frame)', $posy);
$selectFont('unifont_jp');
$pdf->page->addContent($pdf->getTextCell(
    txt: $japanese,
    posx: $posx,
    posy: $posy,
    width: $width,
    valign: \Com\Tecnick\Pdf\TextVAlign::Top,
    halign: \Com\Tecnick\Pdf\TextHAlign::Left,
));
$bbox = $pdf->getLastTextBBox();
$pdf->page->addContent($pdf->graph->getRect($posx, $posy, $width, $bbox['h'], 'D', ['all' => $frameStyle]));

// ----------
// Rules that keep the text together or break it anywhere.
// ----------

$posy = $drawTitle('word-break and the ideographic line breaks');

$posy = $drawCase(
    'word-break: keep-all (expected: one line that runs out of the frame)',
    $posy,
    '<p style="word-break:keep-all">' . $japanese . '</p>',
);
$posy = $drawCase(
    'white-space: nowrap (expected: one line that runs out of the frame)',
    $posy,
    '<p style="white-space:nowrap">' . $japanese . '</p>',
);
$posy = $drawCase(
    'word joiners between the first four characters (expected: no break between them)',
    $posy,
    "<p>吾\u{2060}輩\u{2060}は\u{2060}猫である。" . $japanese . '</p>',
);
$posy = $drawCase(
    'word-break: break-all (expected: the long word broken at the frame edge)',
    $posy,
    '<p style="word-break:break-all">Pneumonoultramicroscopicsilicovolcanoconiosis'
    . 'Pneumonoultramicroscopicsilicovolcanoconiosis</p>',
    'dejavusans',
);

$selectFont('unifont_jp');
$pdf->enableIdeographicBreaks(false);
$pdf->enableWordBreakFallback(false);
$note(
    'enableIdeographicBreaks(false) and enableWordBreakFallback(false) (expected: one line that runs out of the frame)',
    $posy,
);
$selectFont('unifont_jp');
$pdf->addHTMLCell(html: '<p>' . $japanese . '</p>', posx: $posx, posy: $posy, width: $width);
$pdf->page->addContent($pdf->graph->getRect($posx, $posy, $width, 8.0, 'D', ['all' => $frameStyle]));
$pdf->enableIdeographicBreaks(true);
$pdf->enableWordBreakFallback(true);

// ----------
// Line break controls in Latin text: the same result with a core and a Unicode font.
// ----------

$posy = $drawTitle('Line break controls: a core font and a Unicode font both break at the controls');

$controls = [
    'soft hyphen &amp;shy; (expected: breaks with a hyphen)' => 'aaaaaaaaaa&shy;bbbbbbbbbb&shy;cccccccccc&shy;dddddddddd',
    'zero width space &amp;#8203; (expected: breaks without a hyphen)' => 'aaaaaaaaaa&#8203;bbbbbbbbbb&#8203;cccccccccc&#8203;dddddddddd',
    '&lt;wbr&gt; (expected: breaks without a hyphen)' => 'aaaaaaaaaa<wbr>bbbbbbbbbb<wbr>cccccccccc<wbr>dddddddddd',
];

foreach ($controls as $label => $text) {
    foreach (['helvetica', 'dejavusans'] as $font) {
        $note($font . ': ' . \html_entity_decode($label), $posy);
        $selectFont($font);
        $pdf->addHTMLCell(html: '<p>' . $text . '</p>', posx: $posx, posy: $posy, width: 40.0);
        $bbox = $pdf->getLastBBox();
        $height = \max(8.0, $bbox['y'] + $bbox['h'] - $posy);
        $pdf->page->addContent($pdf->graph->getRect($posx, $posy, 40.0, $height, 'D', ['all' => $frameStyle]));
        $posy += $height + 6.0;
    }
}

// ----------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
