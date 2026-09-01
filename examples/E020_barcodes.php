<?php

declare(strict_types=1);

/**
 * E020_barcodes.php
 *
 * @since       2026-04-26
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: local file reads (images, fonts, attachments) are restricted to an allowlist of
// trusted paths that covers this package tree, so run the examples in place. To read assets
// from other locations, list them in the 'allowedPaths' entry of the fileOptions constructor
// parameter (see E047_remote_resources_security.php).

// NOTE: run make fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// main TCPDF object
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
        'type' => 'AUSPOST',
        'code' => '6254516251ABC123',
        'name' => 'Australia Post 4-State Customer Barcode',
        'standard' => 'Australia Post 4-State Customer Code',
        'use' => 'Australian postal sorting and customer routing barcode.',
    ],
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
        'type' => 'C16K',
        'code' => 'ab0123456789',
        'name' => 'Code 16K',
        'standard' => 'Uniform Symbology Specification Code 16K',
        'use' => 'Stacked Code 128 symbology for small items requiring multi-row data.',
    ],
    [
        'type' => 'C32',
        'code' => '012345676',
        'name' => 'Code 32',
        'standard' => 'Italian Pharmacode (IMH, radix 32)',
        'use' => 'Italian pharmaceutical product coding derived from Code 39.',
    ],
    [
        'type' => 'C49',
        'code' => 'MULTIPLE ROWS IN CODE 49',
        'name' => 'Code 49',
        'standard' => 'ANSI/AIM BC6',
        'use' => 'Multi-row stacked symbology for compact alphanumeric data.',
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
        'type' => 'DATABAR',
        'code' => '09501101530010',
        'name' => 'GS1 DataBar Omnidirectional',
        'standard' => 'ISO/IEC 24724',
        'use' => 'GS1 item identification scannable from any orientation at retail point of sale.',
    ],
    [
        'type' => 'DATABAREXP',
        'code' => '(01)90614141000015(3202)000150',
        'name' => 'GS1 DataBar Expanded',
        'standard' => 'ISO/IEC 24724',
        'use' => 'Encodes GS1 Application Identifiers such as weight, price, and dates.',
    ],
    [
        'type' => 'DATABAREXPSTACK',
        'code' => '(01)90614141000015(3202)000150',
        'name' => 'GS1 DataBar Expanded Stacked',
        'standard' => 'ISO/IEC 24724',
        'use' => 'Stacked form of DataBar Expanded for narrow label areas.',
    ],
    [
        'type' => 'DATABARLIMITED',
        'code' => '15012345678907',
        'name' => 'GS1 DataBar Limited',
        'standard' => 'ISO/IEC 24724',
        'use' => 'Item identification for small retail packages not scanned omnidirectionally.',
    ],
    [
        'type' => 'DATABARSTACK',
        'code' => '00012345678905',
        'name' => 'GS1 DataBar Stacked',
        'standard' => 'ISO/IEC 24724',
        'use' => 'Two-row DataBar for very small items such as loose produce.',
    ],
    [
        'type' => 'DATABARSTACKOMNI',
        'code' => '00034567890125',
        'name' => 'GS1 DataBar Stacked Omnidirectional',
        'standard' => 'ISO/IEC 24724',
        'use' => 'Two-row omnidirectional DataBar for point-of-sale scanning of small items.',
    ],
    [
        'type' => 'DATABARTRUNC',
        'code' => '00012345678905',
        'name' => 'GS1 DataBar Truncated',
        'standard' => 'ISO/IEC 24724',
        'use' => 'Reduced-height DataBar for non-retail item marking.',
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
        'type' => 'GS114',
        'code' => '9501101020917',
        'name' => 'GS1-14 / EAN-14 / SCC-14',
        'standard' => 'GS1 General Specifications (GS1-128 with AI 01)',
        'use' => 'Trade item grouping identifier (GTIN-14) for cases and pallets.',
    ],
    [
        'type' => 'GS1128',
        'code' => '(01)09501101020917(10)AB-123',
        'name' => 'GS1-128',
        'standard' => 'ISO/IEC 15417 with GS1 Application Identifiers',
        'use' => 'Supply-chain data carrier for batch, expiry, and logistics attributes.',
    ],
    [
        'type' => 'HIBC128',
        'code' => '+A123BJC5D6E71',
        'name' => 'HIBC in Code 128',
        'standard' => 'ANSI/HIBC 2.6 and ANSI/HIBC 1.3',
        'use' => 'Health industry supplier labeling of medical products in Code 128.',
    ],
    [
        'type' => 'HIBC39',
        'code' => '+A123BJC5D6E71',
        'name' => 'HIBC in Code 39',
        'standard' => 'ANSI/HIBC 2.6 and ANSI/HIBC 1.3',
        'use' => 'Health industry supplier labeling of medical products in Code 39.',
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
        'type' => 'IDENTCODE',
        'code' => '563102430313',
        'name' => 'Deutsche Post Identcode',
        'standard' => 'Deutsche Post Identcode (Interleaved 2 of 5 based)',
        'use' => 'German parcel identification for Deutsche Post shipments.',
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
        'type' => 'ITF14',
        'code' => '09312345678907',
        'name' => 'ITF-14',
        'standard' => 'GS1 General Specifications (ITF-14)',
        'use' => 'GTIN-14 marking on corrugated cartons and outer packaging, with bearer bars.',
    ],
    [
        'type' => 'JPPOST',
        'code' => '910-00673-80-25J1-2B',
        'name' => 'Japan Post Customer Barcode',
        'standard' => 'Japan Post Customer Barcode (4-state)',
        'use' => 'Japanese postal address and routing barcode.',
    ],
    [
        'type' => 'KIX',
        'code' => '0123456789',
        'name' => 'KIX',
        'standard' => 'PostNL KIX (4-state)',
        'use' => 'Dutch postal customer indexing and routing barcode.',
    ],
    [
        'type' => 'LEITCODE',
        'code' => '21348075016401',
        'name' => 'Deutsche Post Leitcode',
        'standard' => 'Deutsche Post Leitcode (Interleaved 2 of 5 based)',
        'use' => 'German postal routing code for destination sorting.',
    ],
    [
        'type' => 'LOGMARS',
        'code' => '12345/ABCDE',
        'name' => 'LOGMARS',
        'standard' => 'MIL-STD-1189B (Code 39 profile)',
        'use' => 'US military logistics marking and asset labeling.',
    ],
    [
        'type' => 'MAILMARK',
        'code' => '41038422416563762EF61AH8T ',
        'name' => 'Royal Mail Mailmark',
        'standard' => 'Royal Mail Mailmark 4-state (types C and L)',
        'use' => 'UK mail tracking and reporting barcode.',
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
        // the two-track Pharmacode encodes an integer between 4 and 64570080
        'code' => '12345678',
        'name' => 'Pharmacode two-track',
        'standard' => 'Pharmacode (2-track variant)',
        'use' => 'Pharmaceutical package line verification using two-track bar patterns.',
    ],
    [
        'type' => 'PHARMA',
        // the Pharmacode encodes an integer between 3 and 131070
        'code' => '123456',
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
        'type' => 'PLESSEY',
        'code' => '0123456789ABCDEF',
        'name' => 'Plessey Code',
        'standard' => 'Plessey (legacy)',
        'use' => 'Legacy hexadecimal symbology used for retail shelf labeling.',
    ],
    [
        'type' => 'POSTNET',
        'code' => '0123456789',
        'name' => 'POSTNET',
        'standard' => 'USPS POSTNET (legacy)',
        'use' => 'Legacy USPS ZIP and delivery-point encoding for mail sorting.',
    ],
    [
        'type' => 'PZN',
        'code' => '2758089',
        'name' => 'PZN',
        'standard' => 'IFA Pharmazentralnummer (Code 39 based)',
        'use' => 'German pharmaceutical central product number for medicines.',
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
        'type' => 'S25DATALOGIC',
        'code' => '0123456789',
        'name' => '2 of 5 Datalogic',
        'standard' => '2 of 5 Datalogic (China Post Code)',
        'use' => 'Numeric variant used by China Post and legacy Datalogic systems.',
    ],
    [
        'type' => 'S25IATA',
        'code' => '0123456789',
        'name' => '2 of 5 IATA',
        'standard' => '2 of 5 IATA (Computer Identics 2 of 5)',
        'use' => 'Air cargo and baggage handling numeric barcode.',
    ],
    [
        'type' => 'S25MATRIX',
        'code' => '0123456789',
        'name' => '2 of 5 Matrix',
        'standard' => '2 of 5 Matrix',
        'use' => 'Numeric symbology denser than Standard 2 of 5 for industrial labels.',
    ],
    [
        'type' => 'SSCC18',
        'code' => '39501101020917171',
        'name' => 'SSCC-18',
        'standard' => 'GS1 General Specifications (GS1-128 with AI 00)',
        'use' => 'Serial Shipping Container Code identifying logistic units.',
    ],
    [
        'type' => 'TELEPEN',
        'code' => 'ABC123',
        'name' => 'Telepen',
        'standard' => 'Telepen (full ASCII)',
        'use' => 'Library and academic sector symbology encoding the full ASCII set.',
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
        'type' => 'AZTECRUNE',
        'code' => '125',
        'name' => 'Aztec Rune',
        'standard' => 'ISO/IEC 24778:2008 Annex A',
        'use' => 'Minimal Aztec symbol encoding a single value from 0 to 255.',
    ],
    [
        'type' => 'PDF417',
        'code' => '0123456789',
        'name' => 'PDF417',
        'standard' => 'ISO/IEC 15438:2006',
        'use' => 'Stacked 2D barcode used in transport, identity, and archival workflows.',
    ],
    [
        'type' => 'PDF417C',
        'code' => '0123456789',
        'name' => 'Compact PDF417',
        'standard' => 'ISO/IEC 15438:2006 (compact/truncated)',
        'use' => 'Truncated PDF417 without right row indicators for narrow print areas.',
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
        'type' => 'MICROQR',
        'code' => '0123456789',
        'name' => 'Micro QR Code',
        'standard' => 'ISO/IEC 18004',
        'use' => 'Reduced-size QR variant for small marking areas and short payloads.',
    ],
    [
        'type' => 'MICROQR,M,4,AN',
        'code' => 'ABCDEFGHIJKLMNOPQR',
        'name' => 'Micro QR Code (with parameters)',
        'standard' => 'ISO/IEC 18004',
        'use' => 'Micro QR variant with explicit error correction, version, and encoding mode.',
    ],
    [
        'type' => 'HIBCAZ',
        'code' => '+A123BJC5D6E71',
        'name' => 'HIBC in Aztec Code',
        'standard' => 'ANSI/HIBC 2.6 and ANSI/HIBC 1.3',
        'use' => 'Health industry supplier labeling of medical products in Aztec Code.',
    ],
    [
        'type' => 'HIBCDM',
        'code' => '+A123BJC5D6E71',
        'name' => 'HIBC in Data Matrix',
        'standard' => 'ANSI/HIBC 2.6 and ANSI/HIBC 1.3',
        'use' => 'Health industry supplier labeling of medical products in Data Matrix.',
    ],
    [
        'type' => 'HIBCQR',
        'code' => '+A123BJC5D6E71',
        'name' => 'HIBC in QR Code',
        'standard' => 'ANSI/HIBC 2.6 and ANSI/HIBC 1.3',
        'use' => 'Health industry supplier labeling of medical products in QR Code.',
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
    [
        'type' => 'DMRE',
        'code' => 'A1B2C3D4E5F6G7H8I9J0K1L2',
        'name' => 'Data Matrix Rectangular Extension',
        'standard' => 'ISO/IEC 21471',
        'use' => 'Extra rectangular symbol sizes for marking thin parts and narrow labels.',
    ],
    [
        'type' => 'DMRE,GS1',
        'code' => \chr(232) . '01095011010209171719050810ABCD1234' . \chr(232) . '2110',
        'name' => 'GS1 Data Matrix Rectangular Extension',
        'standard' => 'ISO/IEC 21471 with GS1 AIs',
        'use' => 'GS1-compliant rectangular extension for traceability on narrow labels.',
    ],
    [
        'type' => 'DMRE,N,ASCII,8x144',
        'code' => 'A1B2C3D4E5F6G7H8I9J0K1L2',
        'name' => 'Data Matrix Rectangular Extension 8x144',
        'standard' => 'ISO/IEC 21471',
        'use' => 'Rectangular extension with an explicit symbol size and encoding mode.',
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

        if ($byte >= 32 && $byte <= 126) {
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
    bool $square = false,
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

    $drawHeader = static function () use (
        $pdf,
        $titlefont,
        $smallfont,
        $title,
        $sectionIndex,
        $marginLeft,
        $contentWidth,
    ): float {
        $pdf->page->addContent($titlefont['out']);
        $pdf->page->addContent($pdf->getTextCell(
            txt: 'Barcode Types Catalog (' . $sectionIndex . '/2): ' . $title,
            posx: $marginLeft,
            posy: 10,
            width: $contentWidth,
            height: 0,
            offset: 0,
            linespace: 1,
            valign: \Com\Tecnick\Pdf\TextVAlign::Top,
            halign: \Com\Tecnick\Pdf\TextHAlign::Left,
        ));

        $pdf->page->addContent($smallfont['out']);
        $pdf->page->addContent($pdf->getTextCell(
            txt: 'Source type list: tc-lib-barcode example/index.php',
            posx: $marginLeft,
            posy: 16,
            width: $contentWidth,
            height: 0,
            offset: 0,
            linespace: 1,
            valign: \Com\Tecnick\Pdf\TextVAlign::Top,
            halign: \Com\Tecnick\Pdf\TextHAlign::Left,
        ));

        return 22.0;
    };

    $cursorY = $drawHeader();
    foreach ($items as $item) {
        $sampleText = formatSampleDataForText($item['code']);
        $desc = 'Standard: ' . $item['standard'] . "\n" . 'Use: ' . $item['use'] . "\n" . 'Sample data: ' . $sampleText;

        $descTopOffset = 4.5;
        $descLineSpace = 1.0;
        $barcodeGap = 4.0;
        $barcodeBlockGap = 8.0;

        while (true) {
            $descTopY = $cursorY + $descTopOffset;
            $descOut = $pdf->getTextCell(
                txt: $desc,
                posx: $marginLeft,
                posy: $descTopY,
                width: $contentWidth,
                height: 0,
                offset: 0,
                linespace: $descLineSpace,
                valign: \Com\Tecnick\Pdf\TextVAlign::Top,
                halign: \Com\Tecnick\Pdf\TextHAlign::Left,
            );
            $descBBox = $pdf->getLastBBox();
            $barcodePosY = (float) $descBBox['y'] + (float) $descBBox['h'] + $barcodeGap;
            $barcodeWidth = -1;
            $barcodeHeight = -1;

            if (!$square) {
                $metrics = $pdf->barcode->getBarcodeObj($item['type'], $item['code'])->getArray();
                $ncols = (int) $metrics['ncols'];
                $nrows = (int) $metrics['nrows'];
                $targetMinBarWidth = 0.65;
                $barcodeWidth = (int) \max(1, \min($contentWidth, \round($ncols * $targetMinBarWidth)));
                // stacked symbologies keep the module aspect ratio, the other ones use a fixed bar height
                $barcodeHeight = $nrows > 4 ? (int) \max(1, \round(($barcodeWidth * $nrows) / $ncols)) : 10;
            }

            $barcodeModel = $pdf->barcode->getBarcodeObj(
                $item['type'],
                $item['code'],
                $barcodeWidth,
                $barcodeHeight,
                'black',
                [0, 0, 0, 0],
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
        $pdf->page->addContent($pdf->getTextCell(
            txt: $head,
            posx: $marginLeft,
            posy: $cursorY,
            width: $contentWidth,
            height: 0,
            offset: 0,
            linespace: 1,
            valign: \Com\Tecnick\Pdf\TextVAlign::Top,
            halign: \Com\Tecnick\Pdf\TextHAlign::Left,
        ));

        $pdf->page->addContent($smallfont['out']);
        $pdf->page->addContent($descOut);

        $pdf->page->addContent($pdf->getBarcode(
            type: $item['type'],
            code: $item['code'],
            posx: $marginLeft,
            posy: $barcodePosY,
            width: $barcodeWidth,
            height: $barcodeHeight,
            padding: [0, 0, 0, 0],
            style: $style,
        ));

        $cursorY = $nextCursorY;
    }
}

renderBarcodeSection($pdf, $linear, 'Linear', 1, $titlefont, $textfont, $smallfont, $style, false);
renderBarcodeSection($pdf, $square, 'Square / 2D', 2, $titlefont, $textfont, $smallfont, $style, true);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
