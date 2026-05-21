<?php

/**
 * E039_text_methods.php
 *
 * @since       2017-05-08
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

\define('OUTPUT_FILE', \realpath(__DIR__ . '/../target') . '/example.pdf');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    isunicode: true,
    subsetfont: false,
    compress: true,
    mode: '',
    objEncrypt: null,
);

// ----------

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 039');
$pdf->setTitle('Text Methods');
$pdf->setKeywords('TCPDF tc-lib-pdf text methods alignment spacing writing');
$pdf->setPDFFilename('039_text_methods.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont1 = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// test images directory
$imgdir = \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-image/test/images/');

// ----------

$page01 = $pdf->addPage();
$pdf->setBookmark(name: 'Text', link: '', level: 0, page: -1, posx: 0, posy: 0, fstyle: 'B', color: '');

// Add an internal link to this page
$page01_link = $pdf->addInternalLink();

$styletxt = [
    'lineWidth' => 0.25,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => 'red',
    'fillColor' => 'black',
];

$pdf->graph->add($styletxt);

$bfont2 = $pdf->font->insert($pdf->pon, 'times', 'BI', 24);

$pdf->page->addContent($bfont2['out']);
// alternative to set the current font (last entry in the font stack):
// $pdf->page->addContent($pdf->font->getOutCurrentFont());

// Add text
$txt = $pdf->getTextLine(
    txt: 'Test PDF text with justification (stretching) % %% %%%',
    posx: 0,
    posy: $pdf->toUnit($bfont2['ascent']),
    width: $page01['width'],
);

$pdf->page->addContent($txt);

$bbox = $pdf->getLastBBox();

// Add text
$txt2 = $pdf->getTextLine(
    txt: 'Link to https://tcpdf.org',
    posx: 15,
    posy: $bbox['y'] + $bbox['h'] + $pdf->toUnit($bfont2['ascent']),
    width: 0,
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    forcedir: '',
    txtanchor: 'S',
    shadow: [
        'xoffset' => 0.5,
        'yoffset' => 0.5,
        'opacity' => 0.5,
        'mode' => 'Normal',
        'color' => 'red',
    ],
);
$pdf->page->addContent($txt2);

// get the coordinates of the box containing the last added text string.
$bbox = $pdf->getLastBBox();

$aoid1 = $pdf->setLink(
    posx: $bbox['x'],
    posy: $bbox['y'],
    width: $bbox['w'],
    height: $bbox['h'],
    link: 'https://tcpdf.org',
);
$pdf->page->addAnnotRef($aoid1);

// -----------------------------------------------

// add a text column with automatic line breaking

$bfont3 = $pdf->font->insert($pdf->pon, 'courier', '', 14);
$pdf->page->addContent($bfont3['out']);

$txt3 =
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
    . "\n"
    . 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';

$col = $pdf->color->getPdfColor('blue');
$pdf->page->addContent($col);

// single block of text
$txtbox = $pdf->getTextCell(
    txt: $txt3,
    posx: 20,
    posy: 30,
    width: 150,
    height: 0,
    offset: 15,
    linespace: 1,
    valign: 'T',
    halign: 'J',
    cell: null,
    styles: [],
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: false,
    forcedir: '',
    shadow: null,
);
$pdf->page->addContent($txtbox);

$col = $pdf->color->getPdfColor('black');
$pdf->page->addContent($col);

$bfont4 = $pdf->font->insert($pdf->pon, 'freeserif', 'I', 12);
$pdf->page->addContent($bfont4['out']);

$pdf->setDefaultCellPadding(top: 2, right: 2, bottom: 2, left: 2);

// Text cell
$style_cell = [
    'all' => [
        'lineWidth' => 1,
        'lineCap' => 'round',
        'lineJoin' => 'round',
        'miterLimit' => 1,
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => 'green',
        'fillColor' => 'yellow',
    ],
];

$pdf->setDefaultCellBorderPos(borderpos: $pdf::BORDERPOS_DEFAULT);
$txtcell1 = $pdf->getTextCell(
    txt: 'DEFAULT',
    posx: 20,
    posy: 100,
    width: 0,
    height: 0,
    offset: 0,
    linespace: 0,
    valign: 'C',
    halign: 'C',
    cell: null,
    styles: $style_cell,
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: true,
    forcedir: '',
    shadow: null,
);
$pdf->page->addContent($txtcell1);

$pdf->setDefaultCellBorderPos(borderpos: $pdf::BORDERPOS_EXTERNAL);
$txtcell2 = $pdf->getTextCell(
    txt: 'EXTERNAL',
    posx: 49,
    posy: 100,
    width: 0,
    height: 0,
    offset: 0,
    linespace: 0,
    valign: 'C',
    halign: 'C',
    cell: null,
    styles: $style_cell,
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: true,
    forcedir: '',
    shadow: null,
);
$pdf->page->addContent($txtcell2);

$pdf->setDefaultCellBorderPos(borderpos: $pdf::BORDERPOS_INTERNAL);
$txtcell2 = $pdf->getTextCell(
    txt: 'INTERNAL',
    posx: 80,
    posy: 100,
    width: 0,
    height: 0,
    offset: 0,
    linespace: 0,
    valign: 'C',
    halign: 'C',
    cell: null,
    styles: $style_cell,
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: true,
    forcedir: '',
    shadow: null,
);
$pdf->page->addContent($txtcell2);

$defbstyle = [
    'lineWidth' => $pdf->toUnit(1),
    'lineCap' => 'square',
    'lineJoin' => 'miter',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => '#333333',
    'fillColor' => '#cccccc',
];
$bstyle = [
    'all' => $defbstyle,
    0 => $defbstyle, // TOP
    1 => $defbstyle, // RIGHT
    2 => $defbstyle, // BOTTOM
    3 => $defbstyle, // LEFT
];
$bstyle[0]['lineColor'] = $bstyle[3]['lineColor'] = '#e7e7e7';

$pdf->setDefaultCellBorderPos(borderpos: $pdf::BORDERPOS_DEFAULT);
$txtcell3 = $pdf->getTextCell(
    txt: 'BUTTON',
    posx: 120,
    posy: 100,
    width: 0,
    height: 0,
    offset: 0,
    linespace: 0,
    valign: 'C',
    halign: 'C',
    cell: null,
    styles: $bstyle,
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: true,
    forcedir: '',
    shadow: null,
);
$pdf->page->addContent($txtcell3);

$pdf->setDefaultCellBorderPos(borderpos: $pdf::BORDERPOS_DEFAULT);

$txtcell2 = $pdf->getTextCell(
    txt: $txt3,
    posx: 20,
    posy: 120,
    width: 150,
    height: 0,
    offset: 0,
    linespace: 0,
    valign: 'C',
    halign: 'J',
    cell: null,
    styles: $style_cell,
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: true,
    forcedir: '',
    shadow: null,
);
$pdf->page->addContent($txtcell2);

$bfont4 = $pdf->font->insert($pdf->pon, 'dejavusans', '', 14);
$pdf->page->addContent($bfont4['out']);

$pdf->setDefaultCellPadding(top: 2, right: 2, bottom: 2, left: 2);

$style_cell_b = [
    'all' => [
        'lineWidth' => 0.5,
        'lineCap' => 'round',
        'lineJoin' => 'round',
        'miterLimit' => 0.5,
        'dashArray' => [0, 1],
        'dashPhase' => 2,
        'lineColor' => 'red',
    ],
];

// block of text between two page regions
$pdf->addTextCell(
    txt: "\u{27A0}" . $txt3,
    pid: -1,
    posx: 20,
    posy: 165,
    width: 150,
    height: 0,
    offset: 15,
    linespace: 1,
    valign: 'T',
    halign: 'J',
    cell: null,
    styles: $style_cell_b,
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: true,
    forcedir: '',
    shadow: null,
);

$txt4 = 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?';

$txt5 = 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.';

$pdf->enableZeroWidthBreakPoints(true);
$pdf->addTextCell(
    txt: 'TEST-TEXT-ENABLE-AUTO-BREAK-POINTS',
    pid: -1,
    posx: 20,
    posy: 233,
    width: 85,
    height: 0,
    offset: 0,
    linespace: 0,
    valign: 'C',
    halign: 'L',
    cell: null,
    styles: $style_cell,
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: true,
    forcedir: '',
    shadow: null,
);

$pdf->enableZeroWidthBreakPoints(false);
$pdf->addTextCell(
    txt: 'TEST-TEXT-DISABLE-AUTO-BREAK-POINTS',
    pid: -1,
    posx: 20,
    posy: 252,
    width: 85,
    height: 0,
    offset: 0,
    linespace: 0,
    valign: 'C',
    halign: 'L',
    cell: null,
    styles: $style_cell,
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: true,
    forcedir: '',
    shadow: null,
);

// Hyphenation example
// TEX hyphenation patterns can be downloaded from:
// https://www.ctan.org/tex-archive/language/hyph-utf8/tex/generic/hyph-utf8/patterns/tex
//
//$hyphen_patterns = $pdf->loadTexHyphenPatterns('../../RESOURCES/hyph-la-x-classic.tex');
//$pdf->setTexHyphenPatterns($hyphen_patterns);

// block of text between two page regions
$pdf->addTextCell(
    txt: $txt3 . "\n" . $txt4 . "\n" . $txt5,
    pid: -1,
    posx: 20,
    posy: 265,
    width: 120,
    height: 0,
    offset: 15,
    linespace: 1,
    valign: 'T',
    halign: 'J',
    cell: null,
    styles: $style_cell,
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: true,
    forcedir: '',
    shadow: null,
);

$pdf->addTextCell(
    txt: 'overline, linethrough and underline',
    pid: -1,
    posx: 15,
    posy: 50,
    width: 180,
    height: 0,
    offset: 0,
    linespace: 1,
    valign: 'T',
    halign: 'L',
    cell: null,
    styles: [],
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: true,
    linethrough: true,
    overline: true,
    clip: false,
    drawcell: false,
    forcedir: '',
    shadow: null,
);

$pdf->addTextCell(
    txt: 'addTextCell()' . PHP_EOL . 'First line.' . PHP_EOL . 'Second Line.',
    posx: 15,
    posy: 70,
    valign: 'T',
    halign: 'L',
    drawcell: false,
);

$pdf->page->addContent($pdf->getTextCell(
    txt: 'getTextCell()' . PHP_EOL . 'First line.' . PHP_EOL . 'Second Line.',
    posx: 15,
    posy: 90,
    valign: 'T',
    halign: 'L',
));

// =============================================================

// ----------
// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// ----------

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF(rawpdf: $rawpdf);

//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
