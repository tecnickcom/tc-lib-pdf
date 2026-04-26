<?php
/**
 * 023_example_cell_alignment.php
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

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm',
    true,
    false,
    true,
    '',
    null,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 023');
$pdf->setTitle('Cell alignment');
$pdf->setKeywords('TCPDF tc-lib-pdf example cell alignment');
$pdf->setPDFFilename('023_example_cell_alignment.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);
$pdf->enableDefaultPageContent();

$setFont = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    string $family,
    string $style,
    int $size,
): array {
    $font = $pdf->font->insert($pdf->pon, $family, $style, $size, 0.0, 1.0);
    $pdf->page->addContent($font['out']);
    return $font;
};

$getLineStyle = static function (float $width, string $color): array {
    return [
        'lineWidth' => $width,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => $color,
    ];
};

$pdf->font->insert($pdf->pon, 'helvetica', '', 10, 0.0, 1.0);

$pdf->addPage();

$setFont($pdf, 'helvetica', 'B', 20);
$pdf->page->addContent($pdf->getTextCell('Cell Alignment Example', 15, 15, 180, 8, valign: 'C', halign: 'L', drawcell: false));

$bodyFont = $setFont($pdf, 'helvetica', '', 11);

$borderStyle = ['all' => $getLineStyle(0.7, '#0080ff')];
$lineStyle = $getLineStyle(0.1, '#ff0000');
$cellDef = [
    'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
    'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
    'borderpos' => 0.0,
];

$cellW = 30.0;
$cellH = ($pdf->toUnit($bodyFont['size']) * 3.0);
$startX = 15.0;
$fontHeight = $pdf->toUnit($bodyFont['height']);
$fontAscent = $pdf->toUnit($bodyFont['ascent']);
$columns = [
    ['prefix' => 'Top', 'cellAlign' => 'T'],
    ['prefix' => 'Center', 'cellAlign' => 'C'],
    ['prefix' => 'Bottom', 'cellAlign' => 'B'],
    ['prefix' => 'Ascent', 'cellAlign' => 'A'],
    ['prefix' => 'Baseline', 'cellAlign' => 'L'],
    ['prefix' => 'Descent', 'cellAlign' => 'D'],
];

$rows = [
    ['lineY' => 60.0, 'suffix' => 'Center', 'textAlign' => 'C'],
    ['lineY' => 90.0, 'suffix' => 'Top', 'textAlign' => 'T'],
    ['lineY' => 120.0, 'suffix' => 'Bottom', 'textAlign' => 'B'],
];

$getCellTopY = static function (
    float $lineY,
    float $cellH,
    string $cellAlign,
    string $textAlign,
    float $fontHeight,
    float $fontAscent,
): float {
    return match ($cellAlign) {
        'C' => ($lineY - ($cellH / 2.0)),
        'B' => ($lineY - $cellH),
        'A' => match ($textAlign) {
            'T' => $lineY,
            'B' => ($lineY - ($cellH - $fontHeight)),
            default => ($lineY - (($cellH - $fontHeight) / 2.0)),
        },
        'L' => match ($textAlign) {
            'T' => ($lineY - $fontAscent),
            'B' => ($lineY - ($cellH - ($fontHeight - $fontAscent))),
            default => ($lineY - ((($cellH - $fontHeight) / 2.0) + $fontAscent)),
        },
        'D' => match ($textAlign) {
            'T' => ($lineY - $fontHeight),
            'B' => ($lineY - $cellH),
            default => ($lineY - (($cellH + $fontHeight) / 2.0)),
        },
        default => $lineY,
    };
};

$drawCellBorder = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    float $x,
    float $y,
    float $w,
    float $h,
    array $style,
): string {
    return $pdf->graph->getLine($x, $y, $x + $w, $y, $style)
        . $pdf->graph->getLine($x + $w, $y, $x + $w, $y + $h, $style)
        . $pdf->graph->getLine($x + $w, $y + $h, $x, $y + $h, $style)
        . $pdf->graph->getLine($x, $y + $h, $x, $y, $style);
};

foreach ($rows as $row) {
    $pdf->page->addContent($pdf->graph->getLine(15, $row['lineY'], 195, $row['lineY'], $lineStyle));
    foreach ($columns as $idx => $col) {
        $x = $startX + ($idx * $cellW);
        $y = $getCellTopY(
            $row['lineY'],
            $cellH,
            $col['cellAlign'],
            $row['textAlign'],
            $fontHeight,
            $fontAscent,
        );
        $label = $col['prefix'] . '-' . $row['suffix'];
        $pdf->page->addContent($pdf->color->getPdfColor('black'));
        $pdf->page->addContent(
            $pdf->getTextCell(
                $label,
                $x,
                $y,
                $cellW,
                $cellH,
                valign: $row['textAlign'],
                halign: 'C',
                cell: $cellDef,
                drawcell: false
            )
        );
        $pdf->page->addContent($drawCellBorder($pdf, $x, $y, $cellW, $cellH, $borderStyle['all']));
    }
}

$imagePath = __DIR__ . '/images/tcpdf_cell.png';
if (\is_file($imagePath)) {
    $imgId = $pdf->image->add($imagePath);
    $page = $pdf->page->getPage($pdf->page->getPageId());
    $pdf->page->addContent($pdf->image->getSetImage($imgId, 15, 160, 100, 100, $page['height']));
}

$legend = "LEGEND:\n\n"
    . "X: cell x top-left origin (top-right for RTL)\n"
    . "Y: cell y top-left origin (top-right for RTL)\n"
    . "CW: cell width\n"
    . "CH: cell height\n"
    . "LW: line width\n"
    . "NRL: normal line position\n"
    . "EXT: external line position\n"
    . "INT: internal line position\n"
    . "ML: margin left\n"
    . "MR: margin right\n"
    . "MT: margin top\n"
    . "MB: margin bottom\n"
    . "PL: padding left\n"
    . "PR: padding right\n"
    . "PT: padding top\n"
    . "PB: padding bottom\n"
    . "TW: text width\n"
    . "FA: font ascent\n"
    . "FB: font baseline\n"
    . "FD: font descent";

$setFont($pdf, 'helvetica', '', 10);
$pdf->page->addContent($pdf->getTextCell($legend, 125, 160, 70, 100, 0, 1.25, 'T', 'L', drawcell: false));

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
