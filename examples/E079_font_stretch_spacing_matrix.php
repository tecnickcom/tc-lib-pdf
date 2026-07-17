<?php

/**
 * E079_font_stretch_spacing_matrix.php
 *
 * @since       2026-06-15
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// This example exercises horizontal font stretching (Tz) and
// character spacing (Tc) through both the direct text API (Section 1) and the
// HTML/CSS engine (Section 2), across every combination of alignment,
// stretching and spacing for two fonts (times and dejavuserif).
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
$pdf->setSubject('tc-lib-pdf example: 079');
$pdf->setTitle('Font stretching and spacing matrix');
$pdf->setKeywords('TCPDF tc-lib-pdf example font stretch Tz spacing Tc letter-spacing font-stretch html css');
$pdf->setPDFFilename('079_font_stretch_spacing_matrix.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

// ---------- shared layout constants ----------

$marginLeft = 15.0;
$contentWidth = 180.0;
$topY = 15.0;
$bottomLimit = 285.0;
$rowHeight = 8.0;

$boxStyle = [
    'all' => [
        'lineWidth' => 0.2,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => 'gray',
        'fillColor' => '',
    ],
];

// Matrix dimensions (mirror example_063: 90/100/110% and -0.254/0/+0.254mm).
$alignments = ['L' => 'LEFT', 'C' => 'CENTER', 'R' => 'RIGHT', 'J' => 'JUSTIFY'];
$cssAlign = ['L' => 'left', 'C' => 'center', 'R' => 'right', 'J' => 'justify'];
$stretchRatios = [0.90, 1.00, 1.10]; // font-stack stretching ratios (1.0 = 100%)
$spacingPoints = [-0.72, 0.0, 0.72]; // direct API character spacing (points)
$spacingMillim = [-0.254, 0.0, 0.254]; // CSS letter-spacing (mm); ~ the spacing above
$fonts = ['times', 'dejavuserif'];

// ---------- helpers ----------

$setFont = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    string $family,
    string $style,
    float $size,
    float $spacing = 0.0,
    float $stretching = 1.0,
): void {
    $font = $pdf->font->insert($pdf->pon, $family, $style, $size, $spacing, $stretching);
    $pdf->page->addContent($font['out']);
};

// Register a neutral current font without writing to any page; used before
// addPage() so the new page is created with a valid current font.
$ensureFont = static function (\Com\Tecnick\Pdf\Tcpdf $pdf): void {
    $pdf->font->insert($pdf->pon, 'helvetica', '', 10, 0.0, 1.0);
};

$heading = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    string $title,
    string $subtitle,
    float $marginLeft,
    float $contentWidth,
    float $y,
) use ($setFont): float {
    $setFont($pdf, 'helvetica', 'B', 13);
    $pdf->page->addContent($pdf->getTextCell(
        txt: $title,
        posx: $marginLeft,
        posy: $y,
        width: $contentWidth,
        height: 0,
        valign: \Com\Tecnick\Pdf\TextVAlign::Top,
        halign: \Com\Tecnick\Pdf\TextHAlign::Left,
    ));
    $setFont($pdf, 'helvetica', '', 9);
    $pdf->page->addContent($pdf->getTextCell(
        txt: $subtitle,
        posx: $marginLeft,
        posy: $y + 6.5,
        width: $contentWidth,
        height: 0,
        valign: \Com\Tecnick\Pdf\TextVAlign::Top,
        halign: \Com\Tecnick\Pdf\TextHAlign::Left,
    ));

    return $y + 15.0;
};

// ========== SECTION 1 — DIRECT TEXT API ==========

foreach ($fonts as $family) {
    $ensureFont($pdf); // ensure a current font exists before addPage
    $pdf->addPage();
    $y = $heading(
        $pdf,
        'Example 079 - Section 1: direct text API (font: ' . $family . ')',
        'Each row inserts the font with a stretching ratio and character spacing, then renders the '
        . 'label via getTextCell() using the L/C/R/J alignment shown.',
        $marginLeft,
        $contentWidth,
        $topY,
    );

    foreach ($alignments as $code => $name) {
        foreach ($stretchRatios as $ratio) {
            foreach ($spacingPoints as $spacing) {
                if (($y + $rowHeight) > $bottomLimit) {
                    $ensureFont($pdf);
                    $pdf->addPage();
                    $y = $topY;
                }

                $setFont($pdf, $family, '', 14, $spacing, $ratio);
                $pdf->page->addContent($pdf->graph->getRect(
                    $marginLeft,
                    $y,
                    $contentWidth,
                    $rowHeight,
                    'D',
                    $boxStyle,
                ));
                $label = \sprintf(
                    '%s | Stretching = %d%% | Spacing = %+.3fpt',
                    $name,
                    (int) \round($ratio * 100.0),
                    $spacing,
                );
                $pdf->page->addContent($pdf->getTextCell(
                    txt: $label,
                    posx: $marginLeft,
                    posy: $y,
                    width: $contentWidth,
                    height: $rowHeight,
                    valign: \Com\Tecnick\Pdf\TextVAlign::Center,
                    halign: $code,
                    drawcell: false,
                ));
                $y += $rowHeight + 1.0;
            }
        }

        $y += 1.5; // gap between alignment groups
    }
}

// ========== SECTION 2 — HTML / CSS API ==========

foreach ($fonts as $family) {
    $ensureFont($pdf); // ensure a current font exists before addPage
    $pdf->addPage();
    $y = $heading(
        $pdf,
        'Example 079 - Section 2: HTML/CSS API (font: ' . $family . ')',
        'Each bordered block is an HTML span carrying CSS font-stretch and letter-spacing; the '
        . 'wrapping paragraph must stay inside its cell with the stretch/spacing applied.',
        $marginLeft,
        $contentWidth,
        $topY,
    );

    // Base font for the HTML engine (neutral stretch/spacing root).
    $setFont($pdf, $family, '', 11);

    $html = '';
    foreach ($alignments as $code => $name) {
        foreach ($stretchRatios as $ratio) {
            $pct = (int) \round($ratio * 100.0);
            foreach ($spacingMillim as $spacing) {
                $html .=
                    '<table border="1" cellpadding="3" cellspacing="0" style="width:100%;"><tr><td>'
                    . '<div style="text-align:'
                    . $cssAlign[$code]
                    . ';">'
                    . '<span style="font-stretch:'
                    . $pct
                    . '%;letter-spacing:'
                    . $spacing
                    . 'mm;">'
                    . '<span style="color:red;">'
                    . $name
                    . '</span> | '
                    . '<span style="color:green;">Stretching = '
                    . $pct
                    . '%</span> | '
                    . '<span style="color:blue;">Spacing = '
                    . \sprintf('%+.3F', $spacing)
                    . 'mm</span><br />'
                    . 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In sed imperdiet lectus. '
                    . 'Phasellus quis velit velit, non condimentum quam. Sed neque urna, ultrices ac '
                    . 'volutpat vel, laoreet vitae augue. Sed vel velit erat. Class aptent taciti sociosqu '
                    . 'ad litora torquent per conubia nostra, per inceptos himenaeos.'
                    . '</span></div></td></tr></table>';
            }
        }
    }

    $pdf->addHTMLCell(html: $html, posx: $marginLeft, posy: $y, width: $contentWidth, height: 0);
}

// ----------

// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__) . '/target', $rawpdf);
$pdf->renderPDF(rawpdf: $rawpdf);

//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
