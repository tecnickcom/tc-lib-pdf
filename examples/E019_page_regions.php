<?php
/**
 * E019_page_regions.php
 *
 * @since       2026-04-26
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
$pdf->setSubject('tc-lib-pdf example: 019');
$pdf->setTitle('Page regions example');
$pdf->setKeywords('TCPDF tc-lib-pdf example page regions columns');
$pdf->setPDFFilename('019_page_regions.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$titlefont = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 12);
$textfont = $pdf->font->insert($pdf->pon, 'times', '', 11);

// ----------
// Two text regions (columns) generated from the content area.

$leftMargin = 15.0;
$rightMargin = 15.0;
$topMargin = 20.0;
$bottomMargin = 20.0;
$columnGap = 8.0;

$contentWidth = 210.0 - $leftMargin - $rightMargin;
$contentHeight = 297.0 - $topMargin - $bottomMargin;
$columnWidth = ($contentWidth - $columnGap) / 2.0;

$page = $pdf->addPage([
    'margin' => [
        'PL' => $leftMargin,
        'PR' => $rightMargin,
        'CT' => $topMargin,
        'CB' => $bottomMargin,
    ],
    'region' => [
        [
            'RX' => $leftMargin,
            'RY' => $topMargin,
            'RW' => $columnWidth,
            'RH' => $contentHeight,
        ],
        [
            'RX' => $leftMargin + $columnWidth + $columnGap,
            'RY' => $topMargin,
            'RW' => $columnWidth,
            'RH' => $contentHeight,
        ],
    ],
]);

$pdf->page->addContent($titlefont['out']);
$pdf->addTextCell(
    'Page Regions: two flowing columns across pages',
    $page['pid'],
    0,
    0,
    0,
    0,
    0,
    0,
    'T',
    'L',
    null,
    [],
    0,
    0,
    0,
    0,
    true,
    true,
    false,
    false,
    false,
    false,
    false,
    false,
);

$pdf->page->addContent($textfont['out']);

$leftPrefix = 'LEFT/RIGHT REGION FLOW. This paragraph is rendered using page regions. '
    . 'When the first region is full, writing continues in the next region, '
    . 'then on the next page with the same two-region layout. ';

$baseParagraph = 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque '
    . 'laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto '
    . 'beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut '
    . 'odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. '
    . 'Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit.';

$chunks = [];
for ($idx = 1; $idx <= 20; ++$idx) {
    $chunks[] = 'Section ' . $idx . '. ' . $leftPrefix . $baseParagraph;
}

$content = "Two columns with automatic region/page breaks\n\n"
    . \implode("\n\n", $chunks);

$region = $pdf->page->getRegion($page['pid']);
$regionWidth = (float) $region['RW'];

$pdf->addTextCell(
    $content,
    $page['pid'],
    0, // float $posx = 0,
    12, // float $posy = 0,
    $regionWidth, // float $width = 0,
    0, // float $height = 0,
    0, // float $offset = 0,
    1, // float $linespace = 0,
    'T', // string $valign = 'T',
    'J', // string $halign = '',
    null, // ?array $cell = null,
    [], // array $styles = [],
    0, // float $strokewidth = 0,
    0, // float $wordspacing = 0,
    0, // float $leading = 0,
    0, // float $rise = 0,
    true, // bool $jlast = true,
    true, // bool $fill = true,
    false, // bool $stroke = false,
    false, // bool $underline = false,
    false, // bool $linethrough = false,
    false, // bool $overline = false,
    false, // bool $clip = false,
    false, // bool $drawcell = true,
);

// --- Same example using HTML ---

$pageH = $pdf->addPage([
    'margin' => [
        'PL' => $leftMargin,
        'PR' => $rightMargin,
        'CT' => $topMargin,
        'CB' => $bottomMargin,
    ],
    'region' => [
        [
            'RX' => $leftMargin,
            'RY' => $topMargin,
            'RW' => $columnWidth,
            'RH' => $contentHeight,
        ],
        [
            'RX' => $leftMargin + $columnWidth + $columnGap,
            'RY' => $topMargin,
            'RW' => $columnWidth,
            'RH' => $contentHeight,
        ],
    ],
]);

$contentHtml = '<p>'
    . \str_replace(
        "\n\n",
        '</p><p>',
        \htmlspecialchars($content, \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8')
    )
    . '</p>';

$pdf->addHTMLCell(
    $contentHtml,
    12,
    0,
    $regionWidth,
);

// =============================================================

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF($rawpdf);
