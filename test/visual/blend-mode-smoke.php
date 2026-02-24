<?php

/**
 * blend-mode-smoke.php
 *
 * Visual smoke test for SVG mix-blend-mode support.
 *
 * Renders BlendModeTest.svg — a reference design with five labelled columns
 * (darken, multiply, color-burn, lighten, color-dodge), each a blue rectangle
 * blending over the large orange "TCPDF" letters underneath.
 *
 * If blend modes are working the output should look like the reference PNG.
 * If not, the rectangles will be solid blue and the letters will be hidden.
 *
 * Run:  php test/visual/blend-mode-smoke.php
 * Open: target/blend-mode-smoke.pdf
 */

require_once __DIR__ . '/../../vendor/autoload.php';

if (!defined('K_PATH_FONTS')) {
    define('K_PATH_FONTS', __DIR__ . '/../../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/');
}

// SVG is 600×435 — render at a comfortable A4-ish size (190mm wide)
$svgW = 190.0;
$svgH = round($svgW * 435 / 600, 2);  // ~137.85 mm — keep aspect ratio

$pdf = new \Com\Tecnick\Pdf\Tcpdf();
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);
$pdf->addPage([
    'width'  => $svgW + 20,
    'height' => $svgH + 20,
]);

$svgPath = __DIR__ . '/BlendModeTest.svg';
$soid = $pdf->addSVG($svgPath, 10.0, 10.0, $svgW, $svgH);
$pdf->page->addContent($pdf->getSetSVG($soid));

$outPath = __DIR__ . '/../../target/blend-mode-smoke.pdf';
file_put_contents($outPath, $pdf->getOutPDFString());
echo 'Written: ' . realpath($outPath) . PHP_EOL;
echo PHP_EOL;
echo 'Compare with: test/visual/BlendModeTest.svg (open in a browser)' . PHP_EOL;
echo PHP_EOL;
echo 'PASS if: blue rectangles blend with the orange TCPDF letters' . PHP_EOL;
echo '         (darken=dark green, multiply=near-black, color-burn=dark red, lighten=lavender, color-dodge=white)' . PHP_EOL;
echo 'FAIL if: blue rectangles are solid blue and completely hide the letters' . PHP_EOL;
