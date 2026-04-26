<?php
/**
 * 027_example_annotations.php
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
$pdf->setSubject('tc-lib-pdf example: 027');
$pdf->setTitle('Text and file attachment annotations');
$pdf->setKeywords('TCPDF tc-lib-pdf example annotation text fileattachment pushpin');
$pdf->setPDFFilename('027_example_annotations.pdf');
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
$setFont($pdf, 'times', '', 16);

$pdf->page->addContent(
    $pdf->getTextCell(
        "Example of Text Annotation.\nMove your mouse over the yellow icon or double click on it to display the annotation text.",
        15.0,
        22.0,
        180.0,
        0.0,
        drawcell: false,
        valign: 'T',
        halign: 'L',
    )
);

$textBox = $pdf->getLastBBox();
$textIconSize = 10.0;
$textIconX = $textBox['x'] + $textBox['w'] + 1.5;
$textIconY = $textBox['y'] + (($textBox['h'] - $textIconSize) / 2.0);

$textAnnotId = $pdf->setAnnotation(
    $textIconX,
    $textIconY,
    $textIconSize,
    $textIconSize,
    "Text annotation example\naccented letters test: àèéìòù",
    [
        'subtype' => 'Text',
        'name' => 'Comment',
        't' => 'title example',
        'subj' => 'example',
        'c' => 'rgb(255,255,0)',
    ],
);
$pdf->page->addAnnotRef($textAnnotId);

$pdf->page->addContent(
    $pdf->getTextCell(
        'Example of File Attachment. Double click on the push-pin icon to open the attached file.',
        15.0,
        75.0,
        180.0,
        0.0,
        drawcell: false,
        valign: 'T',
        halign: 'L',
    )
);

$fileBox = $pdf->getLastBBox();
$fileIconSize = 5.0;
$fileIconX = $fileBox['x'] + $fileBox['w'] + 1.5;
$fileIconY = $fileBox['y'] + (($fileBox['h'] - $fileIconSize) / 2.0);

$attachmentPath = __DIR__ . '/data/utf8test.txt';
if (\is_file($attachmentPath)) {
    $fileAnnotId = $pdf->setAnnotation(
        $fileIconX,
        $fileIconY,
        $fileIconSize,
        $fileIconSize,
        'text file',
        [
            'subtype' => 'FileAttachment',
            'name' => 'PushPin',
            'fs' => $attachmentPath,
        ],
    );
    $pdf->page->addAnnotRef($fileAnnotId);
}

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
