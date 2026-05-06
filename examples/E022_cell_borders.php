<?php
/**
 * E022_cell_borders.php
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
$pdf->setSubject('tc-lib-pdf example: 022');
$pdf->setTitle('Cell borders');
$pdf->setKeywords('TCPDF tc-lib-pdf example cell borders');
$pdf->setPDFFilename('022_cell_borders.pdf');
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

$buildCellStyles = static function (array $sideStyles, string $fillColor): array {
    $fillStyle = [
        'lineWidth' => 0,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => '#000000',//$fillColor,
        'fillColor' => $fillColor,
    ];

    $styles = [
        'all' => $fillStyle,
        0 => $fillStyle,
        1 => $fillStyle,
        2 => $fillStyle,
        3 => $fillStyle,
    ];

    foreach ($sideStyles as $side => $style) {
        $styles[$side] = \array_merge($fillStyle, $style, ['fillColor' => $fillColor]);
    }

    return $styles;
};

$drawStyledCell = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    string $label,
    float $x,
    float $y,
    float $w,
    float $h,
    array $styles,
    int $borderPos
): void {
    $pdf->setDefaultCellBorderPos($borderPos);

    $pdf->page->addContent($pdf->getTextCell(' ', $x, $y, $w, $h, styles: $styles, drawcell: true));

    $pdf->page->addContent($pdf->color->getPdfColor('black'));
    $pdf->page->addContent($pdf->getTextCell($label, $x, $y, $w, $h, valign: 'C', halign: 'C', drawcell: false));
};

$pdf->font->insert($pdf->pon, 'helvetica', '', 10, 0.0, 1.0);

$pdf->addPage();

$setFont($pdf, 'helvetica', 'B', 20);
$pdf->page->addContent($pdf->getTextCell('Cell Borders', 15, 15, 180, 8, valign: 'C', halign: 'L', drawcell: false));

$setFont($pdf, 'helvetica', '', 11);

$basicLineStyle = $getLineStyle(0.508, '#0080ff');
$fillColor = '#ffff80';
$maskToSide = ['T' => 0, 'R' => 1, 'B' => 2, 'L' => 3];

$masks = ['1', 'LTRB', 'LTR', 'TRB', 'LRB', 'LTB', 'LT', 'TR', 'RB', 'LB', 'LR', 'TB', 'L', 'T', 'R', 'B'];
$x = 15.0;
$y = 32.0;
$w = 30.0;
$h = 8.0;

foreach ($masks as $mask) {
    $active = ($mask === '1') ? 'LTRB' : $mask;
    $sideStyles = [];
    foreach ($maskToSide as $side => $idx) {
        if (\strpos($active, $side) !== false) {
            $sideStyles[$idx] = $basicLineStyle;
        }
    }
    $drawStyledCell(
        $pdf,
        $mask,
        $x,
        $y,
        $w,
        $h,
        $buildCellStyles($sideStyles, $fillColor),
        $pdf::BORDERPOS_DEFAULT
    );
    $y += 10.0;
}

$normalBorders = [
    0 => \array_merge($getLineStyle(2.0, '#ff0000'), ['lineCap' => 'square']),
    1 => \array_merge($getLineStyle(2.0, '#ff0000'), ['lineCap' => 'square']),
    2 => \array_merge($getLineStyle(2.0, '#ff0000'), ['lineCap' => 'square']),
    3 => \array_merge($getLineStyle(2.0, '#ff0000'), ['lineCap' => 'square']),
];

$multiBorders = [
    0 => \array_merge($getLineStyle(2.0, '#00ff00'), ['lineCap' => 'square']),
    1 => \array_merge($getLineStyle(2.0, '#ff00ff'), ['lineCap' => 'square']),
    2 => \array_merge($getLineStyle(2.0, '#0000ff'), ['lineCap' => 'square']),
    3 => \array_merge($getLineStyle(2.0, '#ff0000'), ['lineCap' => 'square']),
];

$advX = $x;
$advY = $y + 5.0;
$drawStyledCell($pdf, 'LTRB', $advX, $advY, 30, 10, $buildCellStyles($normalBorders, $fillColor), $pdf::BORDERPOS_DEFAULT);
$advY += 15.0;

$drawStyledCell($pdf, 'LTRB', $advX, $advY, 30, 10, $buildCellStyles($multiBorders, $fillColor), $pdf::BORDERPOS_DEFAULT);
$advY += 15.0;

$drawStyledCell($pdf, 'LTRB EXT', $advX, $advY, 30, 10, $buildCellStyles($normalBorders, $fillColor), $pdf::BORDERPOS_EXTERNAL);
$advY += 15.0;

$drawStyledCell($pdf, 'LTRB INT', $advX, $advY, 30, 10, $buildCellStyles($normalBorders, $fillColor), $pdf::BORDERPOS_INTERNAL);

$pdf->setDefaultCellBorderPos($pdf::BORDERPOS_DEFAULT);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
