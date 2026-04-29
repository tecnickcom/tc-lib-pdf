<?php
/**
 * E000_overview.php
 *
 * @since       2017-05-08
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


\define('OUTPUT_FILE', \realpath(__DIR__ . '/../target') . '/example_overview.pdf');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm', // string $unit = 'mm',
    true, // bool $isunicode = true,
    false, // bool $subsetfont = false,
    true, // bool $compress = true,
    '', // string $mode = '',
    null, // ?ObjEncrypt $objEncrypt = null,
);

// ----------


$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 000');
$pdf->setTitle('tc-lib-pdf Visual Showcase');
$pdf->setKeywords('TCPDF tc-lib-pdf showcase barcode qr svg image transparency gradients');
$pdf->setPDFFilename('000_overview.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$titleFont = $pdf->font->insert($pdf->pon, 'dejavusans', 'B', 30);
$subtitleFont = $pdf->font->insert($pdf->pon, 'dejavusans', '', 13);
$bodyFont = $pdf->font->insert($pdf->pon, 'dejavusans', '', 10);
$smallFont = $pdf->font->insert($pdf->pon, 'dejavusans', '', 8);


$imgdir = __DIR__ . '/images';


// ----------
// Add first page

$page01 = $pdf->addPage();
$pdf->setBookmark('Visual showcase', '', 0, -1, 0, 0, 'B', 'blue');

$pageW = (float) $page01['width'];
$pageH = (float) $page01['height'];

$cardStyle = [
    'all' => [
        'lineWidth' => 0.35,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => 'rgb(193,216,242)',
        'fillColor' => 'rgb(255,255,255)',
    ],
];

$lineStyle = [
    'all' => [
        'lineWidth' => 0.25,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => 'rgb(147,196,227)',
        'fillColor' => 'rgb(147,196,227)',
    ],
];

$barcodeStyle = [
    'lineWidth' => 0,
    'lineCap' => 'butt',
    'lineJoin' => 'miter',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => 'black',
    'fillColor' => 'black',
];

$textOnDark = $pdf->color->getPdfColor('rgb(244,250,255)');
$textOnLight = $pdf->color->getPdfColor('rgb(22,37,61)');

// Background atmosphere.
$pdf->page->addContent(
    $pdf->graph->getLinearGradient(0, 0, $pageW, $pageH, 'rgb(10,27,60)', 'rgb(0,171,193)', [0, 0, 1, 1])
);

$pdf->page->addContent($pdf->graph->getAlpha(0.12));
for ($lineY = -20; $lineY <= 330; $lineY += 12) {
    $pdf->page->addContent($pdf->graph->getLine(0, (float) $lineY, $pageW, (float) ($lineY + 95), $lineStyle));
}

$pdf->page->addContent($pdf->graph->getAlpha(0.28));
$pdf->page->addContent($pdf->graph->getCircle(167, 46, 40, 0, 360, 'F', [
    'all' => [
        'lineWidth' => 0,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => 'rgb(255,255,255)',
        'fillColor' => 'rgb(255,255,255)',
    ],
]));
$pdf->page->addContent($pdf->graph->getAlpha(1.0));

// Main title block.
$pdf->page->addContent($textOnDark);
$pdf->page->addContent($titleFont['out']);
$pdf->addHTMLCell(
    '<div style="font-size:30pt; font-weight:bold; color:rgb(244,250,255);">tc-lib-pdf</div>',
    15,
    18,
    120
);

$pdf->page->addContent($subtitleFont['out']);
$pdf->addHTMLCell(
    '<div style="font-size:13pt; line-height:1.3; color:rgb(244,250,255);">Modern PDF generation in pure PHP: vectors, images, barcodes, and rich page composition.</div>',
    15,
    34,
    125
);

// Left feature card.
$pdf->page->addContent($pdf->graph->getAlpha(0.90));
$pdf->page->addContent($pdf->graph->getRect(12, 56, 89, 137, 'DF', $cardStyle));
$pdf->page->addContent($pdf->graph->getAlpha(1.0));

$pdf->page->addContent($textOnLight);
$pdf->page->addContent($subtitleFont['out']);
$pdf->addHTMLCell(
    '<div style="font-size:13pt; color:rgb(22,37,61);">Cool Features</div>',
    18,
    64,
    72
);

$pdf->page->addContent($bodyFont['out']);
$features = '<ul style="font-size:10pt; line-height:1.35; color:rgb(22,37,61); margin:0; padding-left:12pt;">'
    . '<li>Native SVG rendering</li>'
    . '<li>JPEG/PNG with transparency</li>'
    . '<li>1D/2D barcodes (QR, DataMatrix)</li>'
    . '<li>Gradients and vector primitives</li>'
    . '<li>HTML/CSS text layout</li>'
    . '<li>Digital signatures and PDF/X, PDF/UA support</li>'
    . '</ul>';
$pdf->addHTMLCell($features, 18, 74, 79);

$logoFile = $imgdir . '/tcpdf_logo.jpg';
if (\is_file($logoFile)) {
    $logoId = $pdf->image->add($logoFile);
    $pdf->page->addContent($pdf->image->getSetImage($logoId, 153, 10, 40, 13, $pageH));
}

// SVG art panel.
$pdf->page->addContent($pdf->graph->getAlpha(0.92));
$pdf->page->addContent($pdf->graph->getRect(106, 70, 92, 121, 'DF', $cardStyle));
$pdf->page->addContent($pdf->graph->getAlpha(1.0));

$svgFile = $imgdir . '/testsvgblend.svg';
if (\is_file($svgFile)) {
    $svgObj = $pdf->addSVG($svgFile, 111, 76, 82, 108, $pageH);
    $pdf->page->addContent($pdf->getSetSVG($svgObj));
}

$pdf->page->addContent($textOnLight);
$pdf->page->addContent($smallFont['out']);
$pdf->addHTMLCell(
    '<div style="font-size:8pt; text-align:center; color:rgb(22,37,61);">SVG graphic rendered directly into PDF vectors</div>',
    111,
    182,
    82
);

// QR code panel.
$pdf->page->addContent($pdf->graph->getAlpha(0.92));
$pdf->page->addContent($pdf->graph->getRect(12, 198, 186, 76, 'DF', $cardStyle));
$pdf->page->addContent($pdf->graph->getAlpha(1.0));

$pdf->page->addContent($textOnLight);
$pdf->page->addContent($subtitleFont['out']);
$pdf->addHTMLCell(
    '<div style="font-size:13pt; color:rgb(22,37,61);">Scan to Explore</div>',
    18,
    206,
    92
);

$pdf->page->addContent($bodyFont['out']);
$pdf->addHTMLCell(
    '<div style="font-size:10pt; line-height:1.25; color:rgb(22,37,61);">This QR code points to the official TCPDF project page:</div>',
    18,
    216,
    118
);
$pdf->addHTMLCell(
    '<div style="font-size:10pt; color:rgb(22,37,61);"><a href="https://tcpdf.org">https://tcpdf.org</a></div>',
    18,
    228,
    118
);

$pdf->page->addContent(
    $pdf->getBarcode(
        'QRCODE,H',
        'https://tcpdf.org',
        145,
        206,
        45,
        45,
        [0, 0, 0, 0],
        $barcodeStyle
    )
);

// =============================================================

// ----------
// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// ----------

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF($rawpdf);
//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
