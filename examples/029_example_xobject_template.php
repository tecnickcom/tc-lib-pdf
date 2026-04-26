<?php
/**
 * 029_example_xobject_template.php
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
$pdf->setSubject('tc-lib-pdf example: 029');
$pdf->setTitle('XObject template');
$pdf->setKeywords('TCPDF tc-lib-pdf example xobject template transparency clipping');
$pdf->setPDFFilename('029_example_xobject_template.pdf');
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

// Insert one neutral font before addPage() so page context has a valid current font.
$pdf->font->insert($pdf->pon, 'helvetica', '', 10, 0.0, 1.0);

$pdf->addPage();

$setFont($pdf, 'helvetica', 'B', 20);
$pdf->page->addContent(
    $pdf->getTextCell(
        'XObject Templates',
        15.0,
        22.0,
        180.0,
        0.0,
        drawcell: false,
        valign: 'T',
        halign: 'C',
    )
);

$setFont($pdf, 'helvetica', '', 10);
$pdf->page->addContent(
    $pdf->getTextCell(
        'The same template object is rendered multiple times with different sizes and alpha values.',
        15.0,
        34.0,
        180.0,
        0.0,
        drawcell: false,
        valign: 'T',
        halign: 'L',
    )
);

$templateWidth = 60.0;
$templateHeight = 60.0;
$templateGroup = ['CS' => 'DeviceRGB', 'I' => true, 'K' => false];
$templateId = $pdf->newXObjectTemplate($templateWidth, $templateHeight, $templateGroup);

$imageId = 0;
$imagePath = __DIR__ . '/images/tcpdf_logo.jpg';
if (\is_file($imagePath)) {
    $imageId = $pdf->image->add($imagePath);
    $pdf->addXObjectImageID($templateId, $imageId);
}

$templateContent = $pdf->graph->getStartTransform();
$templateContent .= $pdf->graph->getStarPolygon(30.0, 30.0, 29.0, 10, 3, 0.0, 'CNZ');
if ($imageId > 0) {
    $templateContent .= $pdf->image->getSetImage(
        $imageId,
        0.0,
        0.0,
        $templateWidth,
        $templateHeight,
        $templateHeight,
    );
}
$templateContent .= $pdf->graph->getStopTransform();

$templateFont = $pdf->font->insert($pdf->pon, 'times', 'B', 15, 0.0, 1.0);
$pdf->addXObjectFontID($templateId, $templateFont['key']);
$templateContent .= $templateFont['out'];
$templateContent .= $pdf->color->getPdfColor('rgb(255,0,0)', true);
$templateContent .= $pdf->getTextCell(
    'Template',
    0.0,
    22.0,
    $templateWidth,
    0.0,
    drawcell: false,
    valign: 'T',
    halign: 'C',
);

$pdf->addXObjectContent($templateId, $templateContent);
$pdf->exitXObjectTemplate();

$instances = [
    [0.4, 15.0, 50.0, 20.0],
    [0.6, 27.0, 62.0, 40.0],
    [0.8, 55.0, 85.0, 60.0],
    [1.0, 95.0, 125.0, 80.0],
];

foreach ($instances as [$alpha, $x, $y, $size]) {
    $pdf->page->addContent($pdf->graph->getAlpha((float) $alpha));
    $pdf->page->addContent($pdf->getXObjectTemplate($templateId, (float) $x, (float) $y, (float) $size, (float) $size));
}

$pdf->page->addContent($pdf->graph->getAlpha(1.0));

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
