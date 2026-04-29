<?php
/**
 * E025_transparency.php
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
$pdf->setSubject('tc-lib-pdf example: 025');
$pdf->setTitle('Object transparency');
$pdf->setKeywords('TCPDF tc-lib-pdf example object transparency alpha extgstate');
$pdf->setPDFFilename('025_transparency.pdf');
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

$getRectStyle = static function (string $strokeColor, string $fillColor): array {
    return [
        'all' => [
            'lineWidth' => 2.0,
            'lineCap' => 'square',
            'lineJoin' => 'miter',
            'dashArray' => [],
            'dashPhase' => 0,
            'lineColor' => $strokeColor,
            'fillColor' => $fillColor,
        ],
    ];
};

// Insert one neutral font before addPage() so page context has a valid current font.
$pdf->font->insert($pdf->pon, 'helvetica', '', 10, 0.0, 1.0);

$page = $pdf->addPage();

$setFont($pdf, 'helvetica', '', 12);
$pdf->page->addContent(
    $pdf->getTextCell(
        'You can set the transparency of PDF objects using graph->getAlpha().',
        15,
        20,
        180,
        0,
        valign: 'T',
        halign: 'L',
        drawcell: false
    )
);

// Opaque red square.
$pdf->page->addContent(
    $pdf->graph->getRect(30.0, 40.0, 60.0, 60.0, 'DF', $getRectStyle('rgb(127,0,0)', 'rgb(255,0,0)'))
);

// Semi-transparent objects.
$pdf->page->addContent($pdf->graph->getAlpha(0.5));

$pdf->page->addContent(
    $pdf->graph->getRect(50.0, 60.0, 60.0, 60.0, 'DF', $getRectStyle('rgb(0,127,0)', 'rgb(0,255,0)'))
);

$pdf->page->addContent(
    $pdf->graph->getRect(70.0, 80.0, 60.0, 60.0, 'DF', $getRectStyle('rgb(0,0,127)', 'rgb(0,0,255)'))
);

$imagePath = __DIR__ . '/images/tcpdf_logo.jpg';
if (\is_file($imagePath)) {
    $imageId = $pdf->image->add($imagePath);
    $pdf->page->addContent($pdf->image->getSetImage($imageId, 90.0, 100.0, 60.0, 60.0, $page['height']));
}

// Restore full opacity.
$pdf->page->addContent($pdf->graph->getAlpha(1.0));

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
