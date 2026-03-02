<?php
/**
 * pdfa.php
 *
 * Example demonstrating PDF/A-1, PDF/A-2, and PDF/A-3 compliance modes.
 *
 * @since       2025-01-02
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

/**
 * Create a PDF/A compliant document.
 *
 * PDF/A modes available:
 *   - 'pdfa1'  or 'pdfa1b' : PDF/A-1b (PDF 1.4, Basic conformance)
 *   - 'pdfa1a'             : PDF/A-1a (PDF 1.4, Accessible conformance)
 *   - 'pdfa2'  or 'pdfa2b' : PDF/A-2b (PDF 1.7, Basic conformance)
 *   - 'pdfa2a'             : PDF/A-2a (PDF 1.7, Accessible conformance)
 *   - 'pdfa2u'             : PDF/A-2u (PDF 1.7, Unicode conformance)
 *   - 'pdfa3'  or 'pdfa3b' : PDF/A-3b (PDF 1.7, Basic conformance)
 *   - 'pdfa3a'             : PDF/A-3a (PDF 1.7, Accessible conformance)
 *   - 'pdfa3u'             : PDF/A-3u (PDF 1.7, Unicode conformance)
 *
 * Conformance levels:
 *   - 'A' (Accessible): Full compliance including tagged PDF and Unicode mapping
 *   - 'B' (Basic): Visual appearance preservation only
 *   - 'U' (Unicode): Basic + Unicode character mapping (PDF/A-2 and PDF/A-3 only)
 */

// Select the PDF/A mode to generate
$pdfaMode = 'pdfa2b'; // Change to 'pdfa1b', 'pdfa2a', 'pdfa2u', 'pdfa3b', etc.

// Determine output filename based on mode
$outputFile = \realpath(__DIR__ . '/../target') . '/example_' . $pdfaMode . '.pdf';

// Create TCPDF instance with PDF/A mode
// Note: Compression is disabled for PDF/A-1, but allowed for PDF/A-2 and PDF/A-3
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm',                    // unit
    true,                    // unicode
    false,                   // subset font
    true,                    // compress (will be overridden for PDF/A-1)
    $pdfaMode,               // PDF/A mode
    null,                    // encryption (not allowed in PDF/A)
);

// Set document metadata (required for PDF/A compliance)
$pdf->setCreator('tc-lib-pdf PDF/A Example');
$pdf->setAuthor('TCPDF Library');
$pdf->setSubject('PDF/A Compliance Demonstration');
$pdf->setTitle('PDF/A-' . substr($pdfaMode, 4) . ' Example Document');
$pdf->setKeywords('TCPDF, PDF/A, archival, long-term preservation');
$pdf->setPDFFilename('example_' . $pdfaMode . '.pdf');

// Viewer preferences
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

// Enable default page content
$pdf->enableDefaultPageContent();

// Insert a font (use embedded fonts for PDF/A compliance)
$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Add a page
$page = $pdf->addPage();

// Add bookmark
$pdf->setBookmark('PDF/A Example', '', 0, -1, 0, 0, 'B', 'blue');

// Get graph object dimensions
$pdf->graph->setPageWidth($page['width']);
$pdf->graph->setPageHeight($page['height']);

// Add content to the page
$pdf->page->addContent($font['out']);

// Title
$titleFont = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 18);
$pdf->page->addContent($titleFont['out']);

$titleText = 'PDF/A-' . strtoupper(substr($pdfaMode, 4)) . ' Compliant Document';
$title = $pdf->getTextLine($titleText, 20, 20, $page['width'] - 40);
$pdf->page->addContent($title);

// Body text
$pdf->page->addContent($font['out']);

$bodyText = 'This document demonstrates PDF/A compliance using tc-lib-pdf.

PDF/A is an ISO-standardized version of PDF specialized for digital preservation
of electronic documents. It is a subset of the PDF format that eliminates features
unsuitable for long-term archiving.

Key PDF/A Requirements:
- All fonts must be embedded
- No encryption is allowed
- No external content references
- XMP metadata is required
- Color spaces must be device-independent (ICC profiles)

PDF/A Versions:
- PDF/A-1 (ISO 19005-1:2005): Based on PDF 1.4
- PDF/A-2 (ISO 19005-2:2011): Based on PDF 1.7, adds JPEG2000, transparency
- PDF/A-3 (ISO 19005-3:2012): Same as PDF/A-2, allows embedded files

Conformance Levels:
- Level A (Accessible): Tagged PDF with Unicode mapping
- Level B (Basic): Visual appearance preservation
- Level U (Unicode): Level B + Unicode mapping (PDF/A-2 and PDF/A-3 only)';

$pdf->setDefaultCellPadding(2, 2, 2, 2);

$style = [
    'all' => [
        'lineWidth' => 0.5,
        'lineCap' => 'round',
        'lineJoin' => 'round',
        'miterLimit' => 0.5,
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => '#336699',
        'fillColor' => '#f0f8ff',
    ],
];

$textbox = $pdf->getTextCell(
    $bodyText,
    20,
    35,
    $page['width'] - 40,
    0,
    0,
    1.2,
    'T',
    'L',
    null,
    $style,
);
$pdf->page->addContent($textbox);

// Add a second page with graphics to demonstrate PDF/A compatibility
$page2 = $pdf->addPage();
$pdf->setBookmark('Graphics', '', 0, -1, 0, 0, 'B', 'green');

$pdf->graph->setPageWidth($page2['width']);
$pdf->graph->setPageHeight($page2['height']);

$pdf->page->addContent($titleFont['out']);
$title2 = $pdf->getTextLine('PDF/A Graphics Support', 20, 20, $page2['width'] - 40);
$pdf->page->addContent($title2);

$pdf->page->addContent($font['out']);

$graphicsInfo = 'PDF/A supports vector graphics and embedded images with proper color profiles.
All graphics operations below are PDF/A compliant.';

$infoBox = $pdf->getTextCell($graphicsInfo, 20, 35, $page2['width'] - 40, 0, 0, 1.2, 'T', 'L');
$pdf->page->addContent($infoBox);

// Draw some shapes
$rectStyle = [
    'lineWidth' => 1,
    'lineCap' => 'round',
    'lineJoin' => 'round',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => '#003366',
    'fillColor' => '#6699cc',
];

// Rectangle
$rect = $pdf->graph->getRect(20, 60, 50, 30, 'DF', ['all' => $rectStyle]);
$pdf->page->addContent($rect);

// Circle
$circleStyle = [
    'lineWidth' => 1,
    'lineCap' => 'round',
    'lineJoin' => 'round',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => '#336600',
    'fillColor' => '#99cc66',
];
$circle = $pdf->graph->getCircle(115, 75, 15, 0, 360, 'DF', $circleStyle);
$pdf->page->addContent($circle);

// Ellipse
$ellipseStyle = [
    'lineWidth' => 1,
    'lineCap' => 'round',
    'lineJoin' => 'round',
    'dashArray' => [],
    'dashPhase' => 0,
    'lineColor' => '#663300',
    'fillColor' => '#cc9966',
];
$ellipse = $pdf->graph->getEllipse(170, 75, 25, 15, 0, 0, 360, 'DF', $ellipseStyle);
$pdf->page->addContent($ellipse);

// Add mode information at the bottom
$pdf->page->addContent($font['out']);
$modeInfo = 'Generated in ' . strtoupper($pdfaMode) . ' mode using tc-lib-pdf';
$modeBox = $pdf->getTextCell($modeInfo, 20, 120, $page2['width'] - 40, 0, 0, 1, 'T', 'C');
$pdf->page->addContent($modeBox);

// Generate PDF output
$rawpdf = $pdf->getOutPDFString();

// Save to file
$pdf->savePDF(\dirname(__DIR__) . '/target', $rawpdf);

echo "PDF/A document generated: " . $outputFile . "\n";
echo "Mode: " . strtoupper($pdfaMode) . "\n";

// To verify PDF/A compliance, you can use tools like:
// - veraPDF (https://verapdf.org/)
// - Adobe Acrobat Pro
// - PDF-Tools (https://www.pdf-tools.com/)
