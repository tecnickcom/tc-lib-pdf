<?php
/**
 * 021_example_font_stretch_spacing.php
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
$pdf->setSubject('tc-lib-pdf example: 021');
$pdf->setTitle('Font stretching, scaling and spacing');
$pdf->setKeywords('TCPDF tc-lib-pdf example font stretching scaling spacing');
$pdf->setPDFFilename('021_example_font_stretch_spacing.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);
$pdf->enableDefaultPageContent();

$style = [
    'all' => [
        'lineWidth' => 0.2,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => 'black',
        'fillColor' => '',
    ],
];

$applyFont = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    float $spacing,
    float $stretching,
    int $size = 11
): array {
    $font = $pdf->font->insert($pdf->pon, 'times', '', $size, $spacing, $stretching);
    $pdf->page->addContent($font['out']);
    return $font;
};

$measureText = static function (\Com\Tecnick\Pdf\Tcpdf $pdf, string $text): array {
    $ordarr = $pdf->uniconv->strToOrdArr($text);
    $width = $pdf->font->getOrdArrWidth($ordarr);
    return [$ordarr, $width];
};

$drawBoxedLine = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    array $style,
    string $text,
    float $x,
    float $y,
    float $w,
    float $h,
    float $spacing,
    float $stretching
) use ($applyFont): void {
    $font = $applyFont($pdf, $spacing, $stretching, 11);
    $pdf->page->addContent($pdf->graph->getRect($x, $y, $w, $h, 'D', $style));
    $baseline = $y + 1.5 + $pdf->toUnit($font['ascent']);
    $pdf->page->addContent($pdf->getTextLine($text, $x + 1.5, $baseline));
};

$setNeutralTitleFont = static function (\Com\Tecnick\Pdf\Tcpdf $pdf): void {
    $font = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 13, 0.0, 1.0);
    $pdf->page->addContent($font['out']);
};

$setNeutralTextFont = static function (\Com\Tecnick\Pdf\Tcpdf $pdf): void {
    $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 9, 0.0, 1.0);
    $pdf->page->addContent($font['out']);
};

// Insert one neutral font before addPage() so page context has a valid current font.
$pdf->font->insert($pdf->pon, 'helvetica', '', 10, 0.0, 1.0);

$pdf->addPage();

$setNeutralTitleFont($pdf);
$pdf->page->addContent(
    $pdf->getTextCell('Example 021 - Font stretching, scaling and spacing', 15, 15, 180, 0, 0, 1, 'T', 'L')
);

$setNeutralTextFont($pdf);
$pdf->page->addContent(
    $pdf->getTextCell(
        'This mirrors the legacy cell stretch sample using tc-lib-pdf font spacing/stretching controls.',
        15,
        23,
        180,
        0,
        0,
        1,
        'T',
        'L'
    )
);

$sample = 'TEST CELL STRETCH: scaling and spacing';
$left = 15;
$top = 32;
$cellw = 85;
$cellh = 8;
$labelw = 95;

$applyFont($pdf, 0.0, 1.0, 11);
[$ordarr, $baseWidthPoints] = $measureText($pdf, $sample);
$textChars = \count($ordarr);
$targetWidthPoints = $pdf->toPoints($cellw - 3);

$scaleFitRatio = ($baseWidthPoints > $targetWidthPoints) ? ($targetWidthPoints / $baseWidthPoints) : 1.0;
$scaleForceRatio = $targetWidthPoints / $baseWidthPoints;
$scaleFit = (100.0 * $scaleFitRatio);
$scaleForce = (100.0 * $scaleForceRatio);

$spacingFit = 0.0;
if ($baseWidthPoints > $targetWidthPoints && $textChars > 1) {
    $spacingFit = ($targetWidthPoints - $baseWidthPoints) / ($textChars - 1);
}

$spacingForce = 0.0;
if ($textChars > 1) {
    $spacingForce = ($targetWidthPoints - $baseWidthPoints) / ($textChars - 1);
}

$modes = [
    ['no stretch', 0.0, 100.0],
    ['scaling (fit)', 0.0, $scaleFit],
    ['force scaling', 0.0, $scaleForce],
    ['spacing (fit)', $spacingFit, 100.0],
    ['force spacing', $spacingForce, 100.0],
];

$firstBlockEndY = $top;
$row = 0;
foreach ($modes as $mode) {
    $y = $top + ($row * ($cellh + 1.5));
    $label = \sprintf('%s  [stretch=%.1f%%, spacing=%.3fpt]', $mode[0], $mode[2], $mode[1]);
    $setNeutralTextFont($pdf);
    $pdf->page->addContent($pdf->getTextLine($label, $left, $y + 5.5));
    $drawBoxedLine($pdf, $style, $sample, $left + $labelw, $y, $cellw, $cellh, $mode[1], $mode[2]);
    $firstBlockEndY = \max($firstBlockEndY, $y + $cellh, $y + 6.5);
    ++$row;
}

$secondIntroY = $firstBlockEndY + 10.0;
$secondTitleY = $secondIntroY + 8.0;
$secondRowsStartY = $secondTitleY + 9.0;

$setNeutralTextFont($pdf);
$pdf->page->addContent(
    $pdf->getTextCell(
        'Second section: global font stretching and spacing combinations (legacy example style).',
        15,
        $secondIntroY,
        180,
        0,
        0,
        1,
        'T',
        'L'
    )
);

$setNeutralTitleFont($pdf);
$pdf->page->addContent($pdf->getTextCell('Global stretching/spacing matrix', 15, $secondTitleY, 180, 0, 0, 1, 'T', 'L'));

$stretchVals = [90.0, 100.0, 110.0];
$spacingVals = [-0.72, 0.0, 0.72]; // ~ -0.254, 0, +0.254 mm expressed in points.
$sample2 = 'Stretching and spacing test text';

$y = $secondRowsStartY;
foreach ($stretchVals as $stretching) {
    foreach ($spacingVals as $spacing) {
        $line = \sprintf(
            'Stretching %3.0f%%, Spacing %+.3fpt',
            $stretching,
            $spacing
        );
        $setNeutralTextFont($pdf);
        $pdf->page->addContent($pdf->getTextLine($line, 15, $y + 5.5));
        $drawBoxedLine($pdf, $style, $sample2, 95, $y, 100, 8, $spacing, $stretching);
        $y += 9.5;
    }
    $y += 2;
}

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
