<?php
/**
 * 026_example_text_rendering_modes.php
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
$pdf->setSubject('tc-lib-pdf example: 026');
$pdf->setTitle('Text rendering modes and clipping');
$pdf->setKeywords('TCPDF tc-lib-pdf example text rendering mode clipping stroke fill');
$pdf->setPDFFilename('026_example_text_rendering_modes.pdf');
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

$drawTextModeLine = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    string $text,
    float $y,
    float $strokeWidth,
    bool $fill,
    bool $stroke,
    bool $clip,
    int $imageId,
    float $pageHeight,
): void {
    if ($clip) {
        $pdf->page->addContent($pdf->graph->getStartTransform());
    }

    $pdf->page->addContent(
        $pdf->getTextCell(
            $text,
            15.0,
            $y,
            180.0,
            0.0,
            strokewidth: $strokeWidth,
            fill: $fill,
            stroke: $stroke,
            clip: $clip,
            drawcell: false,
            valign: 'T',
            halign: 'L',
        )
    );

    if ($clip && ($imageId > 0)) {
        $pdf->page->addContent($pdf->image->getSetImage($imageId, 15.0, $y + 2.0, 170.0, 10.0, $pageHeight));
        $pdf->page->addContent($pdf->graph->getStopTransform());
    }
};

// Insert one neutral font before addPage() so page context has a valid current font.
$pdf->font->insert($pdf->pon, 'helvetica', '', 10, 0.0, 1.0);

$page = $pdf->addPage();

$setFont($pdf, 'helvetica', '', 11);
$pdf->page->addContent(
    $pdf->getTextCell(
        'Text rendering modes: fill, stroke, fill+stroke, invisible, and clipping.',
        15.0,
        20.0,
        180.0,
        0.0,
        drawcell: false,
        valign: 'T',
        halign: 'L',
    )
);

$setFont($pdf, 'helvetica', '', 22);

$pdf->page->addContent($pdf->color->getPdfColor('black', false));
$pdf->page->addContent($pdf->color->getPdfColor('rgb(255,0,0)', true));

$drawTextModeLine($pdf, 'Fill text', 35.0, 0.0, true, false, false, 0, $page['height']);
$drawTextModeLine($pdf, 'Stroke text', 47.0, 0.2, false, true, false, 0, $page['height']);
$drawTextModeLine($pdf, 'Fill, then stroke text', 59.0, 0.2, true, true, false, 0, $page['height']);
$drawTextModeLine($pdf, 'Neither fill nor stroke text (invisible)', 71.0, 0.0, false, false, false, 0, $page['height']);

$imagePath = __DIR__ . '/images/tcpdf_logo.jpg';
$imageId = 0;
if (\is_file($imagePath)) {
    $imageId = $pdf->image->add($imagePath);
}

$drawTextModeLine($pdf, 'Fill text and add to path for clipping', 95.0, 0.0, true, false, true, $imageId, $page['height']);
$drawTextModeLine($pdf, 'Stroke text and add to path for clipping', 107.0, 0.3, false, true, true, $imageId, $page['height']);
$drawTextModeLine($pdf, 'Fill, then stroke text and add to path for clipping', 119.0, 0.3, true, true, true, $imageId, $page['height']);
$drawTextModeLine($pdf, 'Add text to path for clipping', 131.0, 0.0, false, false, true, $imageId, $page['height']);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
