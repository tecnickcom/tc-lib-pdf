<?php
/**
 * E027_annotations.php
 *
 * Demonstrates all currently supported annotation subtypes.
 *
 * @since       2026-04-27
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
$pdf->setTitle('All Supported Annotation Types');
$pdf->setKeywords('TCPDF tc-lib-pdf example annotation all subtypes');
$pdf->setPDFFilename('027_annotations.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);
$pdf->enableDefaultPageContent();

$setFont = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    string $family,
    string $style,
    int $size,
): void {
    $font = $pdf->font->insert($pdf->pon, $family, $style, $size, 0.0, 1.0);
    $pdf->page->addContent($font['out']);
};

$pdf->font->insert($pdf->pon, 'helvetica', '', 12, 0.0, 1.0);

$leftMargin = 20.0;
$annX = 108.0;
$annW = 40.0;
$rowH = 10.0;

$convertTopToPdfCoordinates = static function (array $coords, float $pageHeight): array {
    $out = [];
    foreach ($coords as $idx => $val) {
        if (! is_numeric($val)) {
            continue;
        }

        $num = (float) $val;
        if (($idx % 2) === 1) {
            $num = $pageHeight - $num;
        }

        $out[] = $num;
    }

    return $out;
};

$normalizeAnnotationOptionCoordinates = static function (
    array $opt,
    float $pageHeight,
) use ($convertTopToPdfCoordinates): array {
    $subtype = strtolower((string) ($opt['subtype'] ?? ''));

    if (($subtype === 'line') && isset($opt['l']) && is_array($opt['l'])) {
        $opt['l'] = $convertTopToPdfCoordinates($opt['l'], $pageHeight);
    }

    if (in_array($subtype, ['polygon', 'polyline'], true) && isset($opt['vertices']) && is_array($opt['vertices'])) {
        $opt['vertices'] = $convertTopToPdfCoordinates($opt['vertices'], $pageHeight);
    }

    if (in_array($subtype, ['highlight', 'underline', 'squiggly', 'strikeout', 'redact'], true)) {
        if (isset($opt['quadpoints']) && is_array($opt['quadpoints'])) {
            $quadOut = [];
            foreach ($opt['quadpoints'] as $quad) {
                if (! is_array($quad)) {
                    continue;
                }

                $quadOut[] = $convertTopToPdfCoordinates($quad, $pageHeight);
            }

            $opt['quadpoints'] = $quadOut;
        }
    }

    if (($subtype === 'ink') && isset($opt['inklist']) && is_array($opt['inklist'])) {
        $inkOut = [];
        foreach ($opt['inklist'] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $inkOut[] = $convertTopToPdfCoordinates($line, $pageHeight);
        }

        $opt['inklist'] = $inkOut;
    }

    return $opt;
};

$addRow = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    string $label,
    float $y,
    string $txt,
    array $opt,
    float $pageHeight,
    string $preview = '',
) use ($leftMargin, $annX, $annW, $rowH, $normalizeAnnotationOptionCoordinates): int {
    $x = $leftMargin;
    $labelW = ($annX - $x - 1.0);

    $pdf->page->addContent(
        $pdf->getTextCell(
            $label,
            $x,
            $y,
            $labelW,
            $rowH,
            drawcell: true,
            valign: 'M',
            halign: 'L',
        )
    );

    $pdf->page->addContent(
        $pdf->getTextCell(
            '',
            $annX,
            $y,
            $annW,
            $rowH,
            drawcell: true,
            valign: 'M',
            halign: 'L',
        )
    );

    if ($preview !== '') {
        $pdf->page->addContent(
            $pdf->getTextCell(
                $preview,
                $annX + 0.4,
                $y + 0.3,
                $annW - 0.8,
                $rowH - 0.6,
                drawcell: false,
                valign: 'M',
                halign: 'C',
            )
        );
    }

    $aid = $pdf->setAnnotation(
        $annX,
        $y,
        $annW,
        $rowH,
        $txt,
        $normalizeAnnotationOptionCoordinates($opt, $pageHeight),
    );
    $pdf->page->addAnnotRef($aid);

    return $aid;
};

$mkQuadPoints = static function (float $x, float $y, float $w, float $h): array {
    return [[
        $x,
        $y + $h,
        $x + $w,
        $y + $h,
        $x,
        $y,
        $x + $w,
        $y,
    ]];
};

$attachmentPath = __DIR__ . '/data/utf8test.txt';
$soundPath = __DIR__ . '/data/utf8test.txt';

$page1 = $pdf->addPage();
$setFont($pdf, 'helvetica', 'B', 16);
$pdf->page->addContent(
    $pdf->getTextCell('All Supported Annotation Types (page 1/2)', $leftMargin, 10, 190, 9, drawcell: false, valign: 'T', halign: 'L')
);
$setFont($pdf, 'helvetica', '', 10);

$y = 22.0;
$textAnnotId = $addRow(
    $pdf,
    'Text',
    $y,
    "Text note body\nwith two lines",
    ['subtype' => 'Text', 'name' => 'Comment', 't' => 'Text note'],
    $page1['height'],
);
$y += 10.0;

$addRow(
    $pdf,
    'Link (URI)',
    $y,
    'https://github.com/tecnickcom/tc-lib-pdf',
    ['subtype' => 'Link', 'h' => 'I'],
    $page1['height'],
    'Open URL',
);
$y += 10.0;

$addRow(
    $pdf,
    'FreeText',
    $y,
    'FreeText content',
    ['subtype' => 'FreeText', 'da' => '/F1 10 Tf', 'q' => 1, 'it' => 'FreeTextTypeWriter'],
    $page1['height'],
);
$y += 10.0;

$addRow(
    $pdf,
    'Line',
    $y,
    'Line annotation',
    [
        'subtype' => 'Line',
        'l' => [$annX + 1.0, $y + ($rowH - 1.0), $annX + $annW - 1.0, $y + 1.0],
        'le' => ['OpenArrow', 'ClosedArrow'],
        'cap' => true,
        'it' => 'LineDimension',
    ],
    $page1['height'],
);
    $y += 10.0;

    $addRow($pdf, 'Square', $y, 'Square annotation', ['subtype' => 'Square', 'ic' => [0.9, 0.9, 0.5]], $page1['height']);
    $y += 10.0;

    $addRow($pdf, 'Circle', $y, 'Circle annotation', ['subtype' => 'Circle', 'ic' => [0.7, 0.9, 1.0]], $page1['height']);
    $y += 10.0;

$addRow(
    $pdf,
    'Polygon',
    $y,
    'Polygon annotation',
    [
        'subtype' => 'Polygon',
        'vertices' => [
            $annX,
            $y + ($rowH - 2.0),
            $annX + 14.0,
            $y + 1.5,
            $annX + 28.0,
            $y + ($rowH - 2.0),
            $annX + $annW,
            $y + 1.5,
        ],
    ],
    $page1['height'],
);
$y += 10.0;

$addRow(
    $pdf,
    'Polyline',
    $y,
    'Polyline annotation',
    [
        'subtype' => 'Polyline',
        'vertices' => [
            $annX,
            $y + 1.5,
            $annX + 12.0,
            $y + ($rowH - 2.0),
            $annX + 24.0,
            $y + 1.5,
            $annX + $annW,
            $y + ($rowH - 2.0),
        ],
        'le' => ['Circle', 'Slash'],
    ],
    $page1['height'],
);
    $y += 10.0;

$qp1 = $mkQuadPoints($annX, $y, $annW, $rowH);
$addRow(
    $pdf,
    'Highlight',
    $y,
    'Highlight annotation',
    ['subtype' => 'Highlight', 'quadpoints' => $qp1],
    $page1['height'],
    'Sample text',
);
    $y += 10.0;

$qp2 = $mkQuadPoints($annX, $y, $annW, $rowH);
$addRow(
    $pdf,
    'Underline',
    $y,
    'Underline annotation',
    ['subtype' => 'Underline', 'quadpoints' => $qp2],
    $page1['height'],
    'Sample text',
);
    $y += 10.0;

$qp3 = $mkQuadPoints($annX, $y, $annW, $rowH);
$addRow(
    $pdf,
    'Squiggly',
    $y,
    'Squiggly annotation',
    ['subtype' => 'Squiggly', 'quadpoints' => $qp3],
    $page1['height'],
    'Sample text',
);
    $y += 10.0;

$qp4 = $mkQuadPoints($annX, $y, $annW, $rowH);
$addRow(
    $pdf,
    'StrikeOut',
    $y,
    'StrikeOut annotation',
    ['subtype' => 'StrikeOut', 'quadpoints' => $qp4],
    $page1['height'],
    'Sample text',
);
    $y += 10.0;

$addRow($pdf, 'Stamp', $y, 'Stamp annotation', ['subtype' => 'Stamp', 'name' => 'Approved'], $page1['height']);
    $y += 10.0;

$addRow($pdf, 'Caret', $y, 'Caret annotation', ['subtype' => 'Caret', 'sy' => 'P'], $page1['height']);
    $y += 10.0;

$addRow(
    $pdf,
    'Ink',
    $y,
    'Ink annotation',
    [
        'subtype' => 'Ink',
        'inklist' => [[
            $annX,
            $y + ($rowH - 2.0),
            $annX + 14.0,
            $y + 2.0,
            $annX + 28.0,
            $y + ($rowH - 2.0),
            $annX + $annW,
            $y + 2.5,
        ]],
    ],
    $page1['height'],
);
$y += 10.0;

$addRow(
    $pdf,
    'Popup (parent: Text)',
    $y,
    'Popup annotation',
    ['subtype' => 'Popup', 'parent' => ['n' => $textAnnotId], 'open' => false],
    $page1['height'],
    'Popup target',
);
$y += 10.0;

if (is_file($attachmentPath)) {
    $addRow(
        $pdf,
        'FileAttachment',
        $y,
        'Attached file',
        ['subtype' => 'FileAttachment', 'name' => 'PushPin', 'fs' => $attachmentPath],
        $page1['height'],
    );
} else {
    $pdf->page->addContent($pdf->getTextCell('FileAttachment skipped: missing examples/data/utf8test.txt', $leftMargin, $y, 190, 7));
}

$page2 = $pdf->addPage();
$setFont($pdf, 'helvetica', 'B', 16);
$pdf->page->addContent(
    $pdf->getTextCell('All Supported Annotation Types (page 2/2)', $leftMargin, 10, 190, 9, drawcell: false, valign: 'T', halign: 'L')
);
$setFont($pdf, 'helvetica', '', 10);

$y = 22.0;

if (is_file($soundPath)) {
    $addRow(
        $pdf,
        'Sound',
        $y,
        'Sound payload',
        ['subtype' => 'Sound', 'name' => 'Speaker', 'fs' => $soundPath],
        $page2['height'],
        'Sound icon',
    );
} else {
    $pdf->page->addContent($pdf->getTextCell('Sound skipped: missing examples/data/utf8test.txt', $leftMargin, $y, 190, 7));
}
$y += 10.0;

$addRow(
    $pdf,
    'Movie',
    $y,
    'Movie payload',
    [
        'subtype' => 'Movie',
        't' => 'Movie title',
        'movie' => ['f' => 'sample.mov', 'aspect' => [16.0, 9.0], 'poster' => true],
        'a' => ['showcontrols' => true, 'mode' => 'Repeat'],
    ],
    $page2['height'],
    'Movie action',
);
$y += 10.0;

$addRow(
    $pdf,
    'Screen',
    $y,
    'Screen payload',
    [
        'subtype' => 'Screen',
        't' => 'Screen title',
        'mk' => ['r' => 90, 'bc' => [0.1, 0.2, 0.3], 'tp' => 2],
        'a' => '/S /Named /N /NextPage',
    ],
    $page2['height'],
    'Screen action',
);
$y += 10.0;

$addRow(
    $pdf,
    'Widget',
    $y,
    'field-demo',
    [
        'subtype' => 'Widget',
        'h' => 'N',
        't' => 'demo_widget',
        'v' => 'abc',
        'da' => '/F1 10 Tf',
        'q' => 0,
    ],
    $page2['height'],
    'Widget field',
);
$y += 10.0;

$redactQuad = $mkQuadPoints($annX, $y, $annW, $rowH);
$addRow(
    $pdf,
    'Redact',
    $y,
    'Redact payload',
    [
        'subtype' => 'Redact',
        'quadpoints' => $redactQuad,
        'overlaytext' => 'REDACTED',
        'repeat' => true,
        'da' => '/F1 10 Tf',
        'q' => 1,
    ],
    $page2['height'],
    'Sample text',
);
$y += 10.0;

$addRow(
    $pdf,
    'Watermark',
    $y,
    'Watermark payload',
    [
        'subtype' => 'Watermark',
        'fixedprint' => [
            'type' => 'FixedPrint',
            'matrix' => [1.0, 0.0, 0.0, 1.0, 5.0, 5.0],
            'h' => 0.5,
            'v' => 0.5,
        ],
    ],
    $page2['height'],
);
$y += 12.0;

$pdf->page->addContent(
    $pdf->getTextCell(
        'Notes: PrinterMark, TrapNet, and 3D are currently intentionally unsupported and are not included in this demo.',
        $leftMargin,
        $y,
        190,
        0,
        drawcell: false,
        valign: 'T',
        halign: 'L',
    )
);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
