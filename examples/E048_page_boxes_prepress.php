<?php
/**
 * E048_page_boxes_prepress.php
 *
 * Demonstrates Media/Crop/Bleed/Trim/Art page boxes for print-oriented
 * workflows, plus visible guides for quick prepress validation.
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
$pdf->setSubject('tc-lib-pdf example: 048');
$pdf->setTitle('Page Boxes and Prepress Guides');
$pdf->setKeywords('TCPDF tc-lib-pdf example page boxes MediaBox CropBox BleedBox TrimBox ArtBox');
$pdf->setPDFFilename('048_page_boxes_prepress.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);
$pdf->enableDefaultPageContent();

$probe = false;
if (PHP_SAPI === 'cli') {
    $probe = (((string) getenv('TC_EXAMPLE_PROBE')) === '1');
} else {
    $probe = isset($_GET['probe']) && ($_GET['probe'] === '1');
}

$titlefont = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 13);
$textfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 9);

$pageBoxesMm = [
    'CropBox' => ['llx' => 5.0, 'lly' => 5.0, 'urx' => 205.0, 'ury' => 292.0],
    'BleedBox' => ['llx' => 3.0, 'lly' => 3.0, 'urx' => 207.0, 'ury' => 294.0],
    'TrimBox' => ['llx' => 10.0, 'lly' => 10.0, 'urx' => 200.0, 'ury' => 287.0],
    'ArtBox' => ['llx' => 20.0, 'lly' => 20.0, 'urx' => 190.0, 'ury' => 277.0],
];

$pageBoxesPt = [];
foreach ($pageBoxesMm as $name => $coords) {
    $pageBoxesPt[$name] = [
        'llx' => $pdf->toPoints((float) $coords['llx']),
        'lly' => $pdf->toPoints((float) $coords['lly']),
        'urx' => $pdf->toPoints((float) $coords['urx']),
        'ury' => $pdf->toPoints((float) $coords['ury']),
    ];
}

$page = $pdf->addPage([
    'format' => 'A4',
    'box' => $pageBoxesPt,
]);

$pdf->page->addContent($titlefont['out']);
$pdf->page->addContent($pdf->getTextCell(
    'Page Boxes: Media/Crop/Bleed/Trim/Art',
    25,
    14,
    180,
    0,
    0,
    1,
    'T',
    'L'
));

$pdf->page->addContent($textfont['out']);
$pdf->page->addContent($pdf->getTextCell(
    "This sheet overlays the configured page boxes to verify prepress bounds.\n"
    . "Coordinates are defined in page-box metadata and rendered here as guides.",
    25,
    24,
    180,
    0,
    0,
    1.2,
    'T',
    'L'
));

$styles = [
    'MediaBox' => ['all' => ['lineWidth' => 0.3, 'lineColor' => '#000000', 'dashArray' => []]],
    'CropBox' => ['all' => ['lineWidth' => 0.3, 'lineColor' => '#1f77b4', 'dashArray' => [2, 1]]],
    'BleedBox' => ['all' => ['lineWidth' => 0.3, 'lineColor' => '#d62728', 'dashArray' => [1, 1]]],
    'TrimBox' => ['all' => ['lineWidth' => 0.35, 'lineColor' => '#2ca02c', 'dashArray' => []]],
    'ArtBox' => ['all' => ['lineWidth' => 0.3, 'lineColor' => '#9467bd', 'dashArray' => [3, 1]]],
];

$boxes = [
    'MediaBox' => [
        'llx' => 0.0,
        'lly' => 0.0,
        'urx' => (float) $page['width'],
        'ury' => (float) $page['height'],
    ],
] + $pageBoxesMm;
$pageHeight = (float) $page['height'];

$labelY = 46.0;
foreach (['MediaBox', 'CropBox', 'BleedBox', 'TrimBox', 'ArtBox'] as $name) {
    if (!isset($boxes[$name])) {
        continue;
    }

    $box = $boxes[$name];
    $x = (float) ($box['llx'] ?? 0.0);
    $y = $pageHeight - (float) ($box['ury'] ?? 0.0);
    $w = (float) ($box['urx'] ?? 0.0) - (float) ($box['llx'] ?? 0.0);
    $h = (float) ($box['ury'] ?? 0.0) - (float) ($box['lly'] ?? 0.0);

    $pdf->page->addContent($pdf->graph->getRect($x, $y, $w, $h, 'D', $styles[$name]));

    $pdf->page->addContent($pdf->getTextCell(
        $name . sprintf(' [%.1f, %.1f, %.1f, %.1f]', (float) ($box['llx'] ?? 0.0), (float) ($box['lly'] ?? 0.0), (float) ($box['urx'] ?? 0.0), (float) ($box['ury'] ?? 0.0)),
        25,
        $labelY,
        180,
        0,
        0,
        1,
        'T',
        'L'
    ));

    $labelY += 5.0;
}

$pdf->page->addContent($pdf->getTextCell(
    "Tip: use TrimBox as the final cut size and keep critical artwork inside ArtBox.\n"
    . "BleedBox extends beyond trim to avoid white edges after cutting.",
    25,
    72,
    180,
    0,
    0,
    1.2,
    'T',
    'L'
));

$rawpdf = $pdf->getOutPDFString();

if ($probe) {
    $matches = [];
    $probeRows = [];
    $pointsPerUnit = $pdf->toPoints(1.0);
    $pattern = '/\\/(MediaBox|CropBox|BleedBox|TrimBox|ArtBox)\\s*\\[\\s*([0-9.+-]+)\\s+([0-9.+-]+)\\s+([0-9.+-]+)\\s+([0-9.+-]+)\\s*\\]/';
    if (\preg_match_all($pattern, $rawpdf, $matches, PREG_SET_ORDER) === false) {
        $probeRows[] = 'Page-box probe: unable to parse page-box dictionaries.';
    } else {
        foreach ($matches as $vals) {
            $name = (string) $vals[1];
            $llx = (float) $vals[2];
            $lly = (float) $vals[3];
            $urx = (float) $vals[4];
            $ury = (float) $vals[5];
            $wPt = $urx - $llx;
            $hPt = $ury - $lly;
            $probeRows[] = sprintf(
                '%s PDF=[%.3f %.3f %.3f %.3f] sizePt=%.3fx%.3f sizeUnit=%.3fx%.3f',
                $name,
                $llx,
                $lly,
                $urx,
                $ury,
                $wPt,
                $hPt,
                ($pointsPerUnit > 0 ? ($wPt / $pointsPerUnit) : 0.0),
                ($pointsPerUnit > 0 ? ($hPt / $pointsPerUnit) : 0.0),
            );
        }
    }

    $output = "\n[E048 page-box probe]\n" . implode("\n", $probeRows) . "\n";
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $output);
    } else {
        error_log($output);
    }
}

$pdf->renderPDF($rawpdf);
