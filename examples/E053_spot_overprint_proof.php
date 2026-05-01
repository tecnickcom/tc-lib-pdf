<?php
/**
 * E053_spot_overprint_proof.php
 *
 * Demonstrates spot color registration, tint ramp generation, and
 * overprint simulation using tc-lib-color and tc-lib-pdf-graph.
 *
 * What this demonstrates:
 * - color->addSpotColor(): register named spot colors with CMYK equivalents.
 * - color->getPdfColor($name, $stroke, $tint): apply spot ink at any tint level.
 * - Tint ramps: a sequence of filled rectangles stepping from 0% to 100% tint.
 * - Layered spot rectangles: simulate overprint by stacking semi-transparent shapes.
 * - Companion lib: tc-lib-color (spot/Lab color model), tc-lib-pdf-graph (rects).
 *
 * Note: tc-lib-pdf does not expose a native /OP (overprint) ExtGState toggle.
 * Overprint simulation here uses stacked shapes with varying tints to give a
 * visual impression of ink mixing, which is representative for screen proofing.
 *
 * @since       2026-05-01
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

require(__DIR__ . '/../vendor/autoload.php');

define('K_PATH_FONTS', (string) realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 053');
$pdf->setTitle('Spot Color & Overprint Proof');
$pdf->setKeywords('TCPDF tc-lib-pdf spot color CMYK tint ramp overprint proof');
$pdf->setPDFFilename('053_spot_overprint_proof.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);
$pdf->enableDefaultPageContent();

// ----------
// Register spot colors with CMYK fallback values.
// These simulate industry-standard Pantone-like named inks. In a real press
// workflow the names would match the RIP spot-color library exactly.

$pdf->color->addSpotColor(
    'PROOF Cyan',
    new \Com\Tecnick\Color\Model\Cmyk(['cyan' => 1.0, 'magenta' => 0.0, 'yellow' => 0.0, 'key' => 0.0, 'alpha' => 0.0])
);
$pdf->color->addSpotColor(
    'PROOF Magenta',
    new \Com\Tecnick\Color\Model\Cmyk(['cyan' => 0.0, 'magenta' => 1.0, 'yellow' => 0.0, 'key' => 0.0, 'alpha' => 0.0])
);
$pdf->color->addSpotColor(
    'PROOF Yellow',
    new \Com\Tecnick\Color\Model\Cmyk(['cyan' => 0.0, 'magenta' => 0.0, 'yellow' => 1.0, 'key' => 0.0, 'alpha' => 0.0])
);
$pdf->color->addSpotColor(
    'PROOF Black',
    new \Com\Tecnick\Color\Model\Cmyk(['cyan' => 0.0, 'magenta' => 0.0, 'yellow' => 0.0, 'key' => 1.0, 'alpha' => 0.0])
);
$pdf->color->addSpotColor(
    'PROOF Warm Red',
    new \Com\Tecnick\Color\Model\Cmyk(['cyan' => 0.0, 'magenta' => 0.85, 'yellow' => 0.9, 'key' => 0.02, 'alpha' => 0.0])
);
$pdf->color->addSpotColor(
    'PROOF Reflex Blue',
    new \Com\Tecnick\Color\Model\Cmyk(['cyan' => 0.93, 'magenta' => 0.75, 'yellow' => 0.0, 'key' => 0.02, 'alpha' => 0.0])
);

// ----------

$labelFont = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 8);
$noteFont  = $pdf->font->insert($pdf->pon, 'helvetica', '', 8);

// ===| Page 1 – Tint Ramps |=================================================

$pdf->addPage(['format' => 'A4']);
$pdf->page->addContent($labelFont['out']);

$introHtml = '<h1 style="font-family: helvetica;">Spot Color Proof — Tint Ramps</h1>
<p style="font-family: helvetica; font-size: 9pt;">
    Each row shows a named spot color at tints from 10% to 100%.
    Spot colors are registered via <code>color-&gt;addSpotColor()</code> with a CMYK equivalent
    and applied using <code>color-&gt;getPdfColor($name, $stroke, $tint)</code>.
    The label column shows the spot name as it would appear in a PDF color dictionary.
</p>';
$pdf->addHTMLCell($introHtml, 15, 20, 175);

// List of spots to render and their display label color
/** @var array<int, array{name: string, label: string}> $spotRows */
$spotRows = [
    ['name' => 'PROOF Cyan',        'label' => '#007799'],
    ['name' => 'PROOF Magenta',     'label' => '#990077'],
    ['name' => 'PROOF Yellow',      'label' => '#887700'],
    ['name' => 'PROOF Black',       'label' => '#222222'],
    ['name' => 'PROOF Warm Red',    'label' => '#993300'],
    ['name' => 'PROOF Reflex Blue', 'label' => '#001177'],
];

$tintSteps  = 10;
$swatchW    = 14.0;
$swatchH    = 10.0;
$labelW     = 42.0;
$rowGap     = 2.0;
$startX     = 15.0;
$startY     = 55.0;

// Draw tint scale header
$pdf->page->addContent($noteFont['out']);
for ($t = 1; $t <= $tintSteps; $t++) {
    $tx = $startX + $labelW + ($t - 1) * $swatchW;
    $pdf->page->addContent(
        $pdf->getTextCell((string) ($t * 10) . '%', $tx, $startY - 5, $swatchW, 0, 0, 1, 'T', 'C')
    );
}

$rowY = $startY;
foreach ($spotRows as $row) {
    $spotName = $row['name'];
    $labelColor = $row['label'];

    // Spot name label
    $pdf->page->addContent($labelFont['out']);
    $pdf->page->addContent($pdf->color->getPdfColor($labelColor, true, 1.0));
    $pdf->page->addContent($pdf->color->getPdfColor($labelColor, false, 1.0));
    $pdf->page->addContent(
        $pdf->getTextCell($spotName, $startX, $rowY + 2, $labelW, 0, 0, 1, 'T', 'L')
    );

    // Draw tint swatches 10%–100%
    for ($t = 1; $t <= $tintSteps; $t++) {
        $tint = $t / $tintSteps;
        $sx   = $startX + $labelW + ($t - 1) * $swatchW;

        // Set fill and stroke to spot color at this tint level
        $pdf->page->addContent($pdf->color->getPdfColor($spotName, true, $tint));
        $pdf->page->addContent($pdf->color->getPdfColor($spotName, false, $tint));
        $pdf->page->addContent($pdf->graph->getRect($sx, $rowY, $swatchW, $swatchH, 'DF'));
    }

    $rowY += $swatchH + $rowGap;
}

// Reset to black for subsequent text
$pdf->page->addContent($pdf->color->getPdfColor('black', true, 1.0));
$pdf->page->addContent($pdf->color->getPdfColor('black', false, 1.0));

$pdf->page->addContent($noteFont['out']);
$footNote = '<p style="font-family: helvetica; font-size: 8pt; color: #555555; margin-top: 4mm;">
    Spot colors use the PDF <em>Separation</em> color space with a CMYK alternate.
    Screen rendering uses the CMYK alternate; print output uses the named ink.
</p>';
$pdf->addHTMLCell($footNote, 15, 170, 175);

// ===| Page 2 – Stacked Spot Layers (Overprint Simulation) |=================

$pdf->addPage(['format' => 'A4']);
$pdf->page->addContent($labelFont['out']);

$overHtml = '<h1 style="font-family: helvetica;">Overprint Simulation — Stacked Spot Layers</h1>
<p style="font-family: helvetica; font-size: 9pt;">
    Two spot inks are drawn in overlapping rectangles at varying tints to simulate
    the visual effect of overprinting. In a real press workflow the RIP controls overprint
    behaviour via the <em>/OP</em> and <em>/op</em> ExtGState flags.
    This page shows the CMYK-alternate screen rendering of the overlap.
</p>';
$pdf->addHTMLCell($overHtml, 15, 20, 175);

// Overprint grid: every combination of Cyan × Magenta at 25/50/75/100% tints
$ox = 25.0;
$oy = 55.0;
$cellSize = 22.0;
$tints = [0.25, 0.5, 0.75, 1.0];
$labelNames = ['25%', '50%', '75%', '100%'];

// Column headers (Cyan tint)
$pdf->page->addContent($labelFont['out']);
foreach ($labelNames as $ci => $clab) {
    $pdf->page->addContent(
        $pdf->getTextCell('C ' . $clab, $ox + 12 + $ci * $cellSize, $oy - 7, $cellSize, 0, 0, 1, 'T', 'C')
    );
}

foreach ($tints as $mi => $mTint) {
    // Row header (Magenta tint)
    $pdf->page->addContent(
        $pdf->getTextCell('M ' . $labelNames[$mi], $ox, $oy + $mi * $cellSize + 5, 12, 0, 0, 1, 'T', 'L')
    );

    foreach ($tints as $ci => $cTint) {
        $cx = $ox + 12 + $ci * $cellSize;
        $cy = $oy + $mi * $cellSize;

        // Draw Cyan layer first, then Magenta layer on top
        $pdf->page->addContent($pdf->color->getPdfColor('PROOF Cyan', false, $cTint));
        $pdf->page->addContent($pdf->graph->getRect($cx, $cy, $cellSize, $cellSize, 'F'));

        $pdf->page->addContent($pdf->color->getPdfColor('PROOF Magenta', false, $mTint));
        $pdf->page->addContent($pdf->graph->getAlpha(0.6)); // partial alpha to show both layers
        $pdf->page->addContent($pdf->graph->getRect($cx, $cy, $cellSize, $cellSize, 'F'));
        $pdf->page->addContent($pdf->graph->getAlpha(1.0)); // restore
    }
}

// Reset colors
$pdf->page->addContent($pdf->color->getPdfColor('black', true, 1.0));
$pdf->page->addContent($pdf->color->getPdfColor('black', false, 1.0));

$overNote = '<p style="font-family: helvetica; font-size: 8pt; color: #555555; margin-top: 4mm;">
    Each cell renders Cyan at the column tint, then Magenta at the row tint with 60% opacity —
    approximating a soft-proof of two overprinted inks on a white substrate.
</p>';
$pdf->addHTMLCell($overNote, 15, 165, 175);

// ----------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
