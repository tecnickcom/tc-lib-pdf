<?php
/**
 * E002_font_dump.php
 *
 * @since       2026-04-19
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
require(__DIR__ . '/../vendor/autoload.php');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm', // string $unit = 'mm',
    true, // bool $isunicode = true,
    false, // bool $subsetfont = false,
    true, // bool $compress = true,
    '', // string $mode = '',
    null, // ?ObjEncrypt $objEncrypt = null,
);

// ----------

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 002');
$pdf->setTitle('Font Dump and Glyph Coverage');
$pdf->setKeywords('TCPDF tc-lib-pdf fonts glyphs unicode dump metrics');
$pdf->setPDFFilename('002_font_dump.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$titlefont = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 16);

// core font families and styles
$corefonts = [
    ['family' => 'courier', 'style' => ''],
    ['family' => 'courier', 'style' => 'B'],
    ['family' => 'courier', 'style' => 'I'],
    ['family' => 'courier', 'style' => 'BI'],
    ['family' => 'helvetica', 'style' => ''],
    ['family' => 'helvetica', 'style' => 'B'],
    ['family' => 'helvetica', 'style' => 'I'],
    ['family' => 'helvetica', 'style' => 'BI'],
    ['family' => 'times', 'style' => ''],
    ['family' => 'times', 'style' => 'B'],
    ['family' => 'times', 'style' => 'I'],
    ['family' => 'times', 'style' => 'BI'],
    ['family' => 'symbol', 'style' => ''],
    ['family' => 'zapfdingbats', 'style' => ''],
];

foreach ($corefonts as $fontcfg) {
    $family = $fontcfg['family'];
    $style = $fontcfg['style'];
    $fontname = $family . ($style !== '' ? ' ' . $style : '');

    $page = $pdf->addPage();
    $pdf->setBookmark('Font: ' . $fontname, '', 0, -1, 0, 0, 'B', '');

    // title
    $pdf->page->addContent($titlefont['out']);
    $title = $pdf->getTextCell('FONT: ' . $fontname, 15, 15, 180, 10, valign: 'C', halign: 'C');
    $pdf->page->addContent($title);

    // glyph table font
    $charfont = $pdf->font->insert($pdf->pon, $family, $style, 16);
    $pdf->page->addContent($charfont['out']);

    // render character table (0-255) as a 16x16 grid
    for ($i = 0; $i < 256; ++$i) {
        $col = $i % 16;
        $row = (int) ($i / 16);
        $posx = 15 + ($col * 11.25);
        $posy = 30 + ($row * 11.25);

        $txt = $pdf->uniconv->chr($i);
        $cell = $pdf->getTextCell($txt, $posx, $posy, 11.25, 11.25, valign: 'C', halign: 'C');
        $pdf->page->addContent($cell);
    }

    $pangram = $pdf->getTextCell(
        'The quick brown fox jumps over the lazy dog',
        15,
        220,
        180,
        8,
        valign: 'C',
        halign: 'C',
    );
    $pdf->page->addContent($pangram);
}

// =============================================================

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF($rawpdf);
