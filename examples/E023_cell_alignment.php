<?php

/**
 * E023_cell_alignment.php
 *
 * @since       2026-04-26
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

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: \Com\Tecnick\Pdf\Page\Unit::Millimeter,
    isunicode: true,
    subsetfont: false,
    compress: true,
    mode: \Com\Tecnick\Pdf\PdfConformance::None,
    objEncrypt: null,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 023');
$pdf->setTitle('Cell alignment');
$pdf->setKeywords('TCPDF tc-lib-pdf example cell alignment');
$pdf->setPDFFilename('023_cell_alignment.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);
$pdf->enableDefaultPageContent();

$setFont = static function (\Com\Tecnick\Pdf\Tcpdf $pdf, string $family, string $style, int $size): array {
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
$pdf->page->addContent($pdf->getTextCell(
    'Cell Alignment Example',
    15,
    15,
    180,
    8,
    valign: \Com\Tecnick\Pdf\TextVAlign::Center,
    halign: \Com\Tecnick\Pdf\TextHAlign::Left,
    drawcell: false,
));

$bodyFont = $setFont($pdf, 'helvetica', '', 11);

$borderStyle = ['all' => $getLineStyle(0.7, '#0080ff')];
$lineStyle = $getLineStyle(0.1, '#ff0000');
$cellDef = [
    'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
    'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
    'borderpos' => 0.0,
];

$cellW = 30.0;
$cellH = $pdf->toUnit($bodyFont['size']) * 3.0;
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
        'C' => $lineY - ($cellH / 2.0),
        'B' => $lineY - $cellH,
        'A' => match ($textAlign) {
            'T' => $lineY,
            'B' => $lineY - ($cellH - $fontHeight),
            default => $lineY - (($cellH - $fontHeight) / 2.0),
        },
        'L' => match ($textAlign) {
            'T' => $lineY - $fontAscent,
            'B' => $lineY - ($cellH - ($fontHeight - $fontAscent)),
            default => $lineY - ((($cellH - $fontHeight) / 2.0) + $fontAscent),
        },
        'D' => match ($textAlign) {
            'T' => $lineY - $fontHeight,
            'B' => $lineY - $cellH,
            default => $lineY - (($cellH + $fontHeight) / 2.0),
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
    return (
        $pdf->graph->getLine($x, $y, $x + $w, $y, $style)
        . $pdf->graph->getLine($x + $w, $y, $x + $w, $y + $h, $style)
        . $pdf->graph->getLine($x + $w, $y + $h, $x, $y + $h, $style)
        . $pdf->graph->getLine($x, $y + $h, $x, $y, $style)
    );
};

foreach ($rows as $row) {
    $pdf->page->addContent($pdf->graph->getLine(15, $row['lineY'], 195, $row['lineY'], $lineStyle));
    foreach ($columns as $idx => $col) {
        $x = $startX + ($idx * $cellW);
        $y = $getCellTopY($row['lineY'], $cellH, $col['cellAlign'], $row['textAlign'], $fontHeight, $fontAscent);
        $label = $col['prefix'] . '-' . $row['suffix'];
        $pdf->page->addContent($pdf->color->getPdfColor('black'));
        $pdf->page->addContent($pdf->getTextCell(
            $label,
            $x,
            $y,
            $cellW,
            $cellH,
            valign: $row['textAlign'],
            halign: \Com\Tecnick\Pdf\TextHAlign::Center,
            cell: $cellDef,
            drawcell: false,
        ));
        $pdf->page->addContent($drawCellBorder($pdf, $x, $y, $cellW, $cellH, $borderStyle['all']));
    }
}

$imagePath = __DIR__ . '/images/tcpdf_cell.png';
if (\is_file($imagePath)) {
    $imgId = $pdf->image->add($imagePath);
    $page = $pdf->page->getPage($pdf->page->getPageId());
    $pdf->page->addContent($pdf->image->getSetImage($imgId, 15, 160, 100, 100, $page['height']));
}

$legend =
    "LEGEND:\n\n"
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
    . 'FD: font descent';

$setFont($pdf, 'helvetica', '', 10);
$pdf->page->addContent($pdf->getTextCell($legend, 125, 160, 70, 100, 0, 1.25, 'T', 'L', drawcell: false));

// ---------------------------------------------------------------------------
// Second page: alignment combined with asymmetric paddings.
// ---------------------------------------------------------------------------

$pdf->addPage();

$setFont($pdf, 'helvetica', 'B', 20);
$pdf->page->addContent($pdf->getTextCell(
    'Cell Padding and Alignment',
    15,
    15,
    180,
    8,
    valign: \Com\Tecnick\Pdf\TextVAlign::Center,
    halign: \Com\Tecnick\Pdf\TextHAlign::Left,
    drawcell: false,
));

$setFont($pdf, 'helvetica', '', 9);
$pdf->page->addContent($pdf->getTextCell(
    'The text is aligned inside the padding box (dashed grey) and not inside the whole cell (solid blue).'
    . ' The margin and padding values of the "cell" parameter are expressed in points,'
    . ' so they are converted here with toPoints(); the padding sizes below are in millimeters.',
    15,
    25,
    180,
    12,
    0,
    0.5,
    valign: \Com\Tecnick\Pdf\TextVAlign::Top,
    halign: \Com\Tecnick\Pdf\TextHAlign::Left,
    drawcell: false,
));

/**
 * Returns a cell definition with the given padding expressed in user units.
 */
$getCellDef = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    float $top,
    float $right,
    float $bottom,
    float $left,
): array {
    return [
        'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        // the cell definition stores the padding in points
        'padding' => [
            'T' => $pdf->toPoints($top),
            'R' => $pdf->toPoints($right),
            'B' => $pdf->toPoints($bottom),
            'L' => $pdf->toPoints($left),
        ],
        'borderpos' => \Com\Tecnick\Pdf\Base::BORDERPOS_DEFAULT,
    ];
};

$padStyle = $getLineStyle(0.1, '#808080');
$padStyle['dashArray'] = [1, 1];

$drawPaddingBox = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    float $x,
    float $y,
    float $w,
    float $h,
    array $pad,
    array $style,
) use ($drawCellBorder): string {
    return $drawCellBorder(
        $pdf,
        $x + $pdf->toUnit($pad['L']),
        $y + $pdf->toUnit($pad['T']),
        $w - $pdf->toUnit($pad['L'] + $pad['R']),
        $h - $pdf->toUnit($pad['T'] + $pad['B']),
        $style,
    );
};

$vcolumns = [
    ['label' => 'Top', 'valign' => \Com\Tecnick\Pdf\TextVAlign::Top],
    ['label' => 'Center', 'valign' => \Com\Tecnick\Pdf\TextVAlign::Center],
    ['label' => 'Bottom', 'valign' => \Com\Tecnick\Pdf\TextVAlign::Bottom],
    ['label' => 'Ascent', 'valign' => \Com\Tecnick\Pdf\TextVAlign::Ascent],
    ['label' => 'Baseline', 'valign' => \Com\Tecnick\Pdf\TextVAlign::Baseline],
    ['label' => 'Descent', 'valign' => \Com\Tecnick\Pdf\TextVAlign::Descent],
];

$vpaddings = [
    ['label' => 'vertical padding: T=0 B=0', 'T' => 0.0, 'B' => 0.0],
    ['label' => 'vertical padding: T=10 B=0', 'T' => 10.0, 'B' => 0.0],
    ['label' => 'vertical padding: T=0 B=10', 'T' => 0.0, 'B' => 10.0],
    ['label' => 'vertical padding: T=12 B=4', 'T' => 12.0, 'B' => 4.0],
];

$vcellW = 180.0 / \count($vcolumns);
$vcellH = 24.0;
$vrowPitch = 32.0;
$vtop = 48.0;

$setFont($pdf, 'helvetica', 'B', 11);
$pdf->page->addContent($pdf->getTextCell(
    'Vertical alignment (valign)',
    15,
    40,
    180,
    5,
    valign: \Com\Tecnick\Pdf\TextVAlign::Top,
    halign: \Com\Tecnick\Pdf\TextHAlign::Left,
    drawcell: false,
));

foreach ($vpaddings as $vrow => $vpad) {
    $rowY = $vtop + ($vrow * $vrowPitch);
    $cellY = $rowY + 6.0;
    $cellDef = $getCellDef($pdf, $vpad['T'], 0.0, $vpad['B'], 0.0);

    $setFont($pdf, 'helvetica', 'I', 8);
    $pdf->page->addContent($pdf->getTextCell(
        $vpad['label'],
        15,
        $rowY,
        180,
        5,
        valign: \Com\Tecnick\Pdf\TextVAlign::Top,
        halign: \Com\Tecnick\Pdf\TextHAlign::Left,
        drawcell: false,
    ));

    foreach ($vcolumns as $idx => $col) {
        $x = $startX + ($idx * $vcellW);
        $setFont($pdf, 'helvetica', '', 9);
        $pdf->page->addContent($pdf->getTextCell(
            $col['label'],
            $x,
            $cellY,
            $vcellW,
            $vcellH,
            valign: $col['valign'],
            halign: \Com\Tecnick\Pdf\TextHAlign::Center,
            cell: $cellDef,
            drawcell: false,
        ));
        $pdf->page->addContent($drawCellBorder($pdf, $x, $cellY, $vcellW, $vcellH, $borderStyle['all']));
        $pdf->page->addContent($drawPaddingBox($pdf, $x, $cellY, $vcellW, $vcellH, $cellDef['padding'], $padStyle));
    }
}

$hcolumns = [
    ['label' => 'Left', 'halign' => \Com\Tecnick\Pdf\TextHAlign::Left],
    ['label' => 'Center', 'halign' => \Com\Tecnick\Pdf\TextHAlign::Center],
    ['label' => 'Right', 'halign' => \Com\Tecnick\Pdf\TextHAlign::Right],
];

$hpaddings = [
    ['label' => 'horizontal padding: L=0 R=0', 'L' => 0.0, 'R' => 0.0],
    ['label' => 'horizontal padding: L=15 R=0', 'L' => 15.0, 'R' => 0.0],
    ['label' => 'horizontal padding: L=0 R=15', 'L' => 0.0, 'R' => 15.0],
];

$hcellW = 180.0 / \count($hcolumns);
$hcellH = 12.0;
$hrowPitch = 20.0;
$htop = 190.0;

$setFont($pdf, 'helvetica', 'B', 11);
$pdf->page->addContent($pdf->getTextCell(
    'Horizontal alignment (halign)',
    15,
    182,
    180,
    5,
    valign: \Com\Tecnick\Pdf\TextVAlign::Top,
    halign: \Com\Tecnick\Pdf\TextHAlign::Left,
    drawcell: false,
));

foreach ($hpaddings as $hrow => $hpad) {
    $rowY = $htop + ($hrow * $hrowPitch);
    $cellY = $rowY + 6.0;
    $cellDef = $getCellDef($pdf, 0.0, $hpad['R'], 0.0, $hpad['L']);

    $setFont($pdf, 'helvetica', 'I', 8);
    $pdf->page->addContent($pdf->getTextCell(
        $hpad['label'],
        15,
        $rowY,
        180,
        5,
        valign: \Com\Tecnick\Pdf\TextVAlign::Top,
        halign: \Com\Tecnick\Pdf\TextHAlign::Left,
        drawcell: false,
    ));

    foreach ($hcolumns as $idx => $col) {
        $x = $startX + ($idx * $hcellW);
        $setFont($pdf, 'helvetica', '', 9);
        $pdf->page->addContent($pdf->getTextCell(
            $col['label'],
            $x,
            $cellY,
            $hcellW,
            $hcellH,
            valign: \Com\Tecnick\Pdf\TextVAlign::Center,
            halign: $col['halign'],
            cell: $cellDef,
            drawcell: false,
        ));
        $pdf->page->addContent($drawCellBorder($pdf, $x, $cellY, $hcellW, $hcellH, $borderStyle['all']));
        $pdf->page->addContent($drawPaddingBox($pdf, $x, $cellY, $hcellW, $hcellH, $cellDef['padding'], $padStyle));
    }
}

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
