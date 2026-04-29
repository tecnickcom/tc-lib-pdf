<?php
/**
 * E020_barcodes.php
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

// main TCPDF object
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
$pdf->setSubject('tc-lib-pdf example: 020');
$pdf->setTitle('All Barcode Types Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example barcodes 1D 2D');
$pdf->setPDFFilename('020_barcodes.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);
$pdf->enableDefaultPageContent();

$titlefont = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 12);
$textfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 9);
$smallfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 8);

$style = [
    'lineWidth' => 0,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => 'black',
    'fillColor' => 'black',
];

$linear = [
    [
        'type' => 'C128A',
        'code' => '0123456789',
        'name' => 'Code 128 Set A',
        'standard' => 'ISO/IEC 15417',
        'use' => 'High-density linear code for control characters, logistics, and internal labeling.',
    ],
    [
        'type' => 'C128B',
        'code' => '0123456789',
        'name' => 'Code 128 Set B',
        'standard' => 'ISO/IEC 15417',
        'use' => 'High-density linear code for mixed upper/lower-case text in shipping and warehousing.',
    ],
    [
        'type' => 'C128C',
        'code' => '0123456789',
        'name' => 'Code 128 Set C',
        'standard' => 'ISO/IEC 15417',
        'use' => 'Numeric-optimized Code 128 variant for compact encoded numbers and IDs.',
    ],
    [
        'type' => 'C128',
        'code' => '0123456789',
        'name' => 'Code 128 (Auto)',
        'standard' => 'ISO/IEC 15417',
        'use' => 'General-purpose high-density linear symbology with automatic charset selection.',
    ],
    [
        'type' => 'C39E+',
        'code' => '0123456789',
        'name' => 'Code 39 Extended + checksum',
        'standard' => 'ANSI MH10.8M / USS Code 39',
        'use' => 'Extended Code 39 character support with checksum for asset tags and internal IDs.',
    ],
    [
        'type' => 'C39E',
        'code' => '0123456789',
        'name' => 'Code 39 Extended',
        'standard' => 'ANSI MH10.8M / USS Code 39',
        'use' => 'Expanded Code 39 set used for industrial and inventory labels.',
    ],
    [
        'type' => 'C39+',
        'code' => '0123456789',
        'name' => 'Code 39 + checksum',
        'standard' => 'ANSI MH10.8M / USS Code 39',
        'use' => 'Code 39 with checksum to improve scan reliability in production environments.',
    ],
    [
        'type' => 'C39',
        'code' => '0123456789',
        'name' => 'Code 39',
        'standard' => 'ANSI MH10.8M-1983 (USD-3 / 3 of 9)',
        'use' => 'Classic alphanumeric barcode used for parts, equipment, and non-retail logistics.',
    ],
    [
        'type' => 'C93',
        'code' => '0123456789',
        'name' => 'Code 93',
        'standard' => 'USS-93',
        'use' => 'Compact linear code often used where Code 39 would be too wide.',
    ],
    [
        'type' => 'CODABAR',
        'code' => '0123456789',
        'name' => 'Codabar',
        'standard' => 'NW-7 (legacy)',
        'use' => 'Legacy symbology used in libraries, blood banks, and some healthcare systems.',
    ],
    [
        'type' => 'CODE11',
        'code' => '0123456789',
        'name' => 'Code 11',
        'standard' => 'USD-8 (legacy)',
        'use' => 'Numeric code widely used in telecommunications and labeling of equipment.',
    ],
    [
        'type' => 'EAN13',
        'code' => '0123456789',
        'name' => 'EAN-13',
        'standard' => 'GS1 EAN/UPC',
        'use' => 'Global retail product identification barcode used on consumer goods.',
    ],
    [
        'type' => 'EAN2',
        'code' => '12',
        'name' => 'EAN 2-digit add-on',
        'standard' => 'GS1 EAN/UPC add-on',
        'use' => 'Supplementary add-on commonly used to encode issue numbers for periodicals.',
    ],
    [
        'type' => 'EAN5',
        'code' => '12345',
        'name' => 'EAN 5-digit add-on',
        'standard' => 'GS1 EAN/UPC add-on',
        'use' => 'Supplementary add-on often used for suggested retail pricing on books/magazines.',
    ],
    [
        'type' => 'EAN8',
        'code' => '1234567',
        'name' => 'EAN-8',
        'standard' => 'GS1 EAN/UPC',
        'use' => 'Compact retail barcode for very small consumer packages.',
    ],
    [
        'type' => 'I25+',
        'code' => '0123456789',
        'name' => 'Interleaved 2 of 5 + checksum',
        'standard' => 'ITF / Interleaved 2 of 5',
        'use' => 'Numeric shipping and carton labels with checksum for improved integrity.',
    ],
    [
        'type' => 'I25',
        'code' => '0123456789',
        'name' => 'Interleaved 2 of 5',
        'standard' => 'ITF / Interleaved 2 of 5',
        'use' => 'Numeric-only barcode frequently used on corrugated packaging.',
    ],
    [
        'type' => 'IMB',
        'code' => '01234567094987654321-01234567891',
        'name' => 'Intelligent Mail Barcode',
        'standard' => 'USPS-B-3200',
        'use' => 'USPS mail sorting and tracking barcode for letters and flats.',
    ],
    [
        'type' => 'IMBPRE',
        'code' => 'AADTFFDFTDADTAADAATFDTDDAAADDTDTTDAFADADDDTFFFDDTTTADFAAADFTDAADA',
        'name' => 'IMB pre-processed',
        'standard' => 'USPS Intelligent Mail (pre-encoded pattern)',
        'use' => 'Feeds a precomputed IMB state pattern when encoding is prepared upstream.',
    ],
    [
        'type' => 'KIX',
        'code' => '0123456789',
        'name' => 'KIX',
        'standard' => 'PostNL KIX (4-state)',
        'use' => 'Dutch postal customer indexing and routing barcode.',
    ],
    [
        'type' => 'MSI+',
        'code' => '0123456789',
        'name' => 'MSI + checksum',
        'standard' => 'MSI Plessey (variant)',
        'use' => 'Inventory-oriented numeric coding with checksum for warehouse workflows.',
    ],
    [
        'type' => 'MSI',
        'code' => '0123456789',
        'name' => 'MSI',
        'standard' => 'MSI Plessey (variant)',
        'use' => 'Numeric barcode used in stock control and shelf labeling.',
    ],
    [
        'type' => 'PHARMA2T',
        'code' => '0123456789',
        'name' => 'Pharmacode two-track',
        'standard' => 'Pharmacode (2-track variant)',
        'use' => 'Pharmaceutical package line verification using two-track bar patterns.',
    ],
    [
        'type' => 'PHARMA',
        'code' => '0123456789',
        'name' => 'Pharmacode',
        'standard' => 'Laetus Pharmacode',
        'use' => 'Pharmaceutical packaging control on production lines.',
    ],
    [
        'type' => 'PLANET',
        'code' => '0123456789',
        'name' => 'PLANET',
        'standard' => 'USPS PLANET (legacy)',
        'use' => 'Legacy USPS mail tracking and routing barcode.',
    ],
    [
        'type' => 'POSTNET',
        'code' => '0123456789',
        'name' => 'POSTNET',
        'standard' => 'USPS POSTNET (legacy)',
        'use' => 'Legacy USPS ZIP and delivery-point encoding for mail sorting.',
    ],
    [
        'type' => 'RMS4CC',
        'code' => '0123456789',
        'name' => 'RMS4CC',
        'standard' => 'Royal Mail 4-State Customer Code',
        'use' => 'UK postal addressing and sorting barcode.',
    ],
    [
        'type' => 'S25+',
        'code' => '0123456789',
        'name' => 'Standard 2 of 5 + checksum',
        'standard' => 'Standard 2 of 5 (industrial)',
        'use' => 'Numeric industrial barcode with checksum for improved reliability.',
    ],
    [
        'type' => 'S25',
        'code' => '0123456789',
        'name' => 'Standard 2 of 5',
        'standard' => 'Standard 2 of 5 (industrial)',
        'use' => 'Legacy numeric industrial labeling barcode.',
    ],
    [
        'type' => 'UPCA',
        'code' => '72527273070',
        'name' => 'UPC-A',
        'standard' => 'GS1 EAN/UPC',
        'use' => 'Retail barcode primarily used in North America.',
    ],
    [
        'type' => 'UPCE',
        'code' => '725277',
        'name' => 'UPC-E',
        'standard' => 'GS1 EAN/UPC',
        'use' => 'Compressed UPC format for small retail packages.',
    ],
];

$square = [
    [
        'type' => 'LRAW',
        'code' => '0101010101',
        'name' => '1D raw mode',
        'standard' => 'Library raw mode (non-standard)',
        'use' => 'Debug/test mode that directly renders provided 1D bit patterns.',
    ],
    [
        'type' => 'SRAW',
        'code' => '0101,1010',
        'name' => '2D raw mode',
        'standard' => 'Library raw mode (non-standard)',
        'use' => 'Debug/test mode that directly renders provided 2D bit grids.',
    ],
    [
        'type' => 'AZTEC',
        'code' => 'ABCDabcd01234',
        'name' => 'Aztec Code',
        'standard' => 'ISO/IEC 24778:2008',
        'use' => 'Compact 2D code used for tickets, transport passes, and mobile scans.',
    ],
    [
        'type' => 'AZTEC,50,A,A',
        'code' => 'ABCDabcd01234',
        'name' => 'Aztec Code (with parameters)',
        'standard' => 'ISO/IEC 24778:2008',
        'use' => 'Aztec variant with explicit encoder parameters for size/error tuning.',
    ],
    [
        'type' => 'PDF417',
        'code' => '0123456789',
        'name' => 'PDF417',
        'standard' => 'ISO/IEC 15438:2006',
        'use' => 'Stacked 2D barcode used in transport, identity, and archival workflows.',
    ],
    [
        'type' => 'QRCODE',
        'code' => '0123456789',
        'name' => 'QR Code',
        'standard' => 'ISO/IEC 18004',
        'use' => 'Widely used 2D code for URLs, mobile interactions, and consumer scanning.',
    ],
    [
        'type' => 'QRCODE,H,ST,0,0',
        'code' => 'abcdefghijklmnopqrstuvwxy0123456789',
        'name' => 'QR Code (with parameters)',
        'standard' => 'ISO/IEC 18004',
        'use' => 'QR variant with explicit error correction and encoding parameters.',
    ],
    [
        'type' => 'DATAMATRIX',
        'code' => '0123456789',
        'name' => 'Data Matrix (square)',
        'standard' => 'ISO/IEC 16022',
        'use' => 'Compact 2D marking used in electronics, medical devices, and manufacturing.',
    ],
    [
        'type' => 'DATAMATRIX,R',
        'code' => '0123456789012345678901234567890123456789',
        'name' => 'Data Matrix (rectangular)',
        'standard' => 'ISO/IEC 16022',
        'use' => 'Rectangular Data Matrix for narrow labels and constrained print areas.',
    ],
    [
        'type' => 'DATAMATRIX,S,GS1',
        'code' => \chr(232) . '01095011010209171719050810ABCD1234' . \chr(232) . '2110',
        'name' => 'GS1 Data Matrix (square)',
        'standard' => 'ISO/IEC 16022 with GS1 AIs',
        'use' => 'GS1-compliant 2D code for regulated product IDs and traceability.',
    ],
    [
        'type' => 'DATAMATRIX,R,GS1',
        'code' => \chr(232) . '01095011010209171719050810ABCD1234' . \chr(232) . '2110',
        'name' => 'GS1 Data Matrix (rectangular)',
        'standard' => 'ISO/IEC 16022 with GS1 AIs',
        'use' => 'Rectangular GS1 Data Matrix for compact traceability labels.',
    ],
];

function formatSampleDataForText(string $sample): string
{
    $out = '';
    $bytes = \unpack('C*', $sample);
    if ($bytes === false) {
        return '';
    }

    foreach ($bytes as $byte) {
        if ($byte === 232) {
            $out .= '<FNC1>';
            continue;
        }

        if (($byte >= 32) && ($byte <= 126)) {
            $out .= \chr($byte);
            continue;
        }

        $out .= '\\x' . \strtoupper(\str_pad((string) \dechex($byte), 2, '0', STR_PAD_LEFT));
    }

    return $out;
}

function renderBarcodeSection(
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    array $items,
    string $title,
    int $sectionIndex,
    array $titlefont,
    array $textfont,
    array $smallfont,
    array $style,
    bool $square = false
): void {
    $marginLeft = 12.0;
    $marginRight = 12.0;
    $marginTop = 15.0;
    $marginBottom = 12.0;

    $page = $pdf->addPage([
        'margin' => [
            'PL' => $marginLeft,
            'PR' => $marginRight,
            'CT' => $marginTop,
            'CB' => $marginBottom,
        ],
    ]);

    $pageWidth = (float) $page['width'];
    $pageHeight = (float) $page['height'];
    $contentWidth = $pageWidth - $marginLeft - $marginRight;
    $maxY = $pageHeight - $marginBottom;

    $drawHeader = static function () use ($pdf, $titlefont, $smallfont, $title, $sectionIndex, $marginLeft, $contentWidth): float {
        $pdf->page->addContent($titlefont['out']);
        $pdf->page->addContent($pdf->getTextCell(
            'Barcode Types Catalog (' . $sectionIndex . '/2): ' . $title,
            $marginLeft,
            10,
            $contentWidth,
            0,
            0,
            1,
            'T',
            'L'
        ));

        $pdf->page->addContent($smallfont['out']);
        $pdf->page->addContent($pdf->getTextCell(
            'Source type list: tc-lib-barcode example/index.php',
            $marginLeft,
            16,
            $contentWidth,
            0,
            0,
            1,
            'T',
            'L'
        ));

        return 22.0;
    };

    $cursorY = $drawHeader();
    foreach ($items as $item) {
        $sampleText = formatSampleDataForText($item['code']);
        $desc = 'Standard: ' . $item['standard'] . "\n"
            . 'Use: ' . $item['use'] . "\n"
            . 'Sample data: ' . $sampleText;

        $descTopOffset = 4.5;
        $descLineSpace = 1.0;
        $barcodeGap = 4.0;
        $barcodeBlockGap = 8.0;

        while (true) {
            $descTopY = $cursorY + $descTopOffset;
            $descOut = $pdf->getTextCell($desc, $marginLeft, $descTopY, $contentWidth, 0, 0, $descLineSpace, 'T', 'L');
            $descBBox = $pdf->getLastBBox();
            $barcodePosY = ((float) $descBBox['y'] + (float) $descBBox['h'] + $barcodeGap);
            $barcodeWidth = -1;
            $barcodeHeight = $square ? -1 : 10;

            if (! $square) {
                $ncols = (int) $pdf->barcode->getBarcodeObj($item['type'], $item['code'])->getArray()['ncols'];
                $targetMinBarWidth = 0.65;
                $barcodeWidth = (int) \max(1, \round($ncols * $targetMinBarWidth));
            }

            $barcodeModel = $pdf->barcode->getBarcodeObj(
                $item['type'],
                $item['code'],
                $barcodeWidth,
                $barcodeHeight,
                'black',
                [0, 0, 0, 0]
            );
            $barcodeData = $barcodeModel->getArray();
            $itemBottomY = $barcodePosY + (float) $barcodeData['full_height'];
            $nextCursorY = $itemBottomY + $barcodeBlockGap;

            if ($nextCursorY <= $maxY) {
                break;
            }

            $page = $pdf->addPage([
                'margin' => [
                    'PL' => $marginLeft,
                    'PR' => $marginRight,
                    'CT' => $marginTop,
                    'CB' => $marginBottom,
                ],
            ]);

            $cursorY = $drawHeader();
        }

        $pdf->page->addContent($textfont['out']);
        $head = '[' . $item['type'] . '] ' . $item['name'];
        $pdf->page->addContent($pdf->getTextCell($head, $marginLeft, $cursorY, $contentWidth, 0, 0, 1, 'T', 'L'));

        $pdf->page->addContent($smallfont['out']);
        $pdf->page->addContent($descOut);

        $pdf->page->addContent($pdf->getBarcode(
            $item['type'],
            $item['code'],
            $marginLeft,
            $barcodePosY,
            $barcodeWidth,
            $barcodeHeight,
            [0, 0, 0, 0],
            $style
        ));

        $cursorY = $nextCursorY;
    }
}

renderBarcodeSection($pdf, $linear, 'Linear', 1, $titlefont, $textfont, $smallfont, $style, false);
renderBarcodeSection($pdf, $square, 'Square / 2D', 2, $titlefont, $textfont, $smallfont, $style, true);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
