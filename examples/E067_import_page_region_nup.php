<?php
/**
 * E067_import_page_region_nup.php
 *
 * @since       2026-05-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate dependencies and example fonts.

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// ---- Step 1: build a 4-page source document ----

$src = new \Com\Tecnick\Pdf\Tcpdf();
$srcFont = $src->font->insert($src->pon, 'helvetica', '', 14);

$cards = [
    ['title' => 'Card A', 'color' => '#ffd7b5', 'text' => 'Quarterly sales summary'],
    ['title' => 'Card B', 'color' => '#b8f2e6', 'text' => 'Regional performance map'],
    ['title' => 'Card C', 'color' => '#cde7ff', 'text' => 'Customer retention trends'],
    ['title' => 'Card D', 'color' => '#f3d9fa', 'text' => 'Fulfillment and logistics KPIs'],
];

foreach ($cards as $idx => $card) {
    $src->addPage();

    // Colored background fill.
    $bgStyle = [
        'all' => [
            'lineWidth' => 0,
            'lineCap'   => 'butt',
            'lineJoin'  => 'miter',
            'dashArray' => [],
            'dashPhase' => 0,
            'lineColor' => $card['color'],
            'fillColor' => $card['color'],
        ],
    ];
    $src->page->addContent($src->graph->getRect(0, 0, 210, 297, 'F', $bgStyle));

    // Render page content.
    $src->page->addContent($srcFont['out']);
    $src->addHTMLCell(
        '<h2>' . \htmlspecialchars($card['title']) . '</h2>'
        . '<p>' . \htmlspecialchars($card['text']) . '</p>'
        . '<p>Page ' . ($idx + 1) . ' of ' . \count($cards) . '</p>',
        20,
        40,
        170
    );
}

$sourcePdfData = $src->getOutPDFString();

// ---- Step 2: import and compose a 2x2 N-up destination page ----

$pdf = new \Com\Tecnick\Pdf\Tcpdf();
$labelFont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$sourceId = $pdf->setImportSourceData($sourcePdfData);

$pdf->addPage();
$pdf->page->addContent($labelFont['out']);

$margin = 12.0;
$gap = 6.0;
$gridWidth = 210.0 - (2 * $margin) - $gap;
$gridHeight = 297.0 - (2 * $margin) - $gap;
$cellWidth = $gridWidth / 2.0;
$cellHeight = $gridHeight / 2.0;

$positions = [
    [$margin, $margin],
    [$margin + $cellWidth + $gap, $margin],
    [$margin, $margin + $cellHeight + $gap],
    [$margin + $cellWidth + $gap, $margin + $cellHeight + $gap],
];

$borderStyle = [
    'all' => [
        'lineWidth' => 0.3,
        'lineCap'   => 'butt',
        'lineJoin'  => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => '#404040',
        'fillColor' => '',
    ],
];

foreach ($positions as $idx => [$x, $y]) {
    $tpl = $pdf->importPage($sourceId, $idx + 1, ['box' => 'CropBox', 'cache' => true]);

    $pdf->useImportedPage($tpl, $x, $y, $cellWidth, $cellHeight, [
        'keepAspectRatio' => true,
        'align' => 'CC',
        'clip' => true,
    ]);

    // Draw outer cell frame over the imported content.
    $pdf->page->addContent($pdf->graph->getRect($x, $y, $cellWidth, $cellHeight, 'D', $borderStyle));

    $pdf->page->addContent($labelFont['out']);
    $pdf->addHTMLCell(
        '<span style="font-size:9px">Imported page ' . ($idx + 1) . '</span>',
        $x + 2,
        $y + 2,
        40
    );
}

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
