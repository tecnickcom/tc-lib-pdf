<?php
/**
 * pdf_stamp.php
 *
 * Example demonstrating PDF stamping and watermark functionality.
 *
 * @category    Library
 * @package     Pdf
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');

use Com\Tecnick\Pdf\Manipulate\PdfStamper;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "PDF Stamp/Watermark Example\n";
echo "===========================\n\n";

// =========================================================================
// Step 1: Create a sample PDF to stamp
// =========================================================================

echo "1. Creating a sample PDF document...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf->setCreator('tc-lib-pdf');
$pdf->setTitle('Document for Stamping');
$pdf->enableDefaultPageContent();
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Create 3 pages with content
for ($i = 1; $i <= 3; $i++) {
    $pdf->addPage();
    $pdf->page->addContent(
        "BT\n/F1 16 Tf\n1 0 0 1 28.35 750 Tm\n(Document Page {$i}) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 11 Tf\n1 0 0 1 28.35 720 Tm\n(This is a sample document page that will receive stamps and watermarks.) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 11 Tf\n1 0 0 1 28.35 700 Tm\n(The stamper can add various overlays to PDF documents.) Tj\nET\n"
    );
}

$sourcePath = __DIR__ . '/../target/stamp_source.pdf';
@mkdir(dirname($sourcePath), 0755, true);
file_put_contents($sourcePath, $pdf->getOutPDFString());
echo "   Created: $sourcePath (3 pages)\n\n";

// =========================================================================
// Step 2: Demonstrate PdfStamper API
// =========================================================================

echo "2. PdfStamper Class API...\n\n";

echo "   Creating a stamper:\n";
echo "   --------------------\n";
echo "   \$stamper = new PdfStamper();\n";
echo "   // or via Tcpdf: \$stamper = \$pdf->createStamper();\n\n";

echo "   Loading PDF:\n";
echo "   ------------\n";
echo "   \$stamper->loadFile('document.pdf');\n";
echo "   \$stamper->loadContent(\$pdfContent);\n\n";

echo "   Adding stamps:\n";
echo "   --------------\n";
echo "   \$stamper->addTextStamp('APPROVED', ['position' => PdfStamper::POSITION_TOP_RIGHT]);\n";
echo "   \$stamper->addWatermark('CONFIDENTIAL', ['rotation' => 45]);\n";
echo "   \$stamper->addPageNumbers();\n";
echo "   \$stamper->addHeader('Company Name');\n";
echo "   \$stamper->addFooter('Legal Notice');\n";
echo "   \$stamper->addDateStamp();\n\n";

echo "   Applying and saving:\n";
echo "   --------------------\n";
echo "   \$content = \$stamper->apply();          // Get stamped content\n";
echo "   \$stamper->applyToFile('output.pdf');   // Save to file\n\n";

// =========================================================================
// Step 3: Add a simple text stamp
// =========================================================================

echo "3. Adding a text stamp (APPROVED)...\n";

$stamper = new PdfStamper();
$stamper->loadFile($sourcePath);

$stamper->addTextStamp('APPROVED', [
    'position' => PdfStamper::POSITION_TOP_RIGHT,
    'fontSize' => 24,
    'color' => [0, 128, 0], // Green
    'opacity' => 0.8,
]);

$approvedPath = __DIR__ . '/../target/stamp_approved.pdf';
$stamper->applyToFile($approvedPath);
echo "   Stamped with 'APPROVED' to: $approvedPath\n\n";

// =========================================================================
// Step 4: Add a diagonal watermark
// =========================================================================

echo "4. Adding a diagonal watermark (CONFIDENTIAL)...\n";

$stamper2 = new PdfStamper();
$stamper2->loadFile($sourcePath);

$stamper2->addWatermark('CONFIDENTIAL', [
    'rotation' => 45,
    'fontSize' => 60,
    'color' => [128, 128, 128], // Gray
    'opacity' => 0.3,
]);

$watermarkPath = __DIR__ . '/../target/stamp_watermark.pdf';
$stamper2->applyToFile($watermarkPath);
echo "   Added watermark to: $watermarkPath\n\n";

// =========================================================================
// Step 5: Add draft watermark using convenience method
// =========================================================================

echo "5. Adding DRAFT watermark...\n";

$stamper3 = new PdfStamper();
$stamper3->loadFile($sourcePath);
$stamper3->addDraftWatermark();

$draftPath = __DIR__ . '/../target/stamp_draft.pdf';
$stamper3->applyToFile($draftPath);
echo "   Added DRAFT watermark to: $draftPath\n\n";

// =========================================================================
// Step 6: Add page numbers
// =========================================================================

echo "6. Adding page numbers...\n";

$stamper4 = new PdfStamper();
$stamper4->loadFile($sourcePath);
$stamper4->addPageNumbers(
    'Page {page} of {total}',
    PdfStamper::POSITION_BOTTOM_CENTER,
    ['fontSize' => 10]
);

$numberedPath = __DIR__ . '/../target/stamp_page_numbers.pdf';
$stamper4->applyToFile($numberedPath);
echo "   Added page numbers to: $numberedPath\n\n";

// =========================================================================
// Step 7: Add header and footer
// =========================================================================

echo "7. Adding header and footer...\n";

$stamper5 = new PdfStamper();
$stamper5->loadFile($sourcePath);
$stamper5->addHeader('Tecnick.com - Confidential Document', [
    'fontSize' => 10,
    'color' => [0, 0, 128], // Navy
]);
$stamper5->addFooter('Copyright 2025 - All Rights Reserved', [
    'fontSize' => 8,
    'color' => [128, 128, 128], // Gray
]);

$headerFooterPath = __DIR__ . '/../target/stamp_header_footer.pdf';
$stamper5->applyToFile($headerFooterPath);
echo "   Added header and footer to: $headerFooterPath\n\n";

// =========================================================================
// Step 8: Add date stamp
// =========================================================================

echo "8. Adding date stamp...\n";

$stamper6 = new PdfStamper();
$stamper6->loadFile($sourcePath);
$stamper6->addDateStamp(
    'Y-m-d H:i',
    PdfStamper::POSITION_BOTTOM_LEFT,
    ['fontSize' => 8]
);

$datePath = __DIR__ . '/../target/stamp_date.pdf';
$stamper6->applyToFile($datePath);
echo "   Added date stamp to: $datePath\n\n";

// =========================================================================
// Step 9: Multiple stamps on one document
// =========================================================================

echo "9. Adding multiple stamps to one document...\n";

$stamper7 = new PdfStamper();
$stamper7->loadFile($sourcePath);

// Add all stamps
$stamper7->addConfidentialWatermark();
$stamper7->addTextStamp('REVIEWED', [
    'position' => PdfStamper::POSITION_TOP_LEFT,
    'fontSize' => 18,
    'color' => [0, 0, 200],
]);
$stamper7->addPageNumbers('Page {page} of {total}', PdfStamper::POSITION_BOTTOM_RIGHT);
$stamper7->addDateStamp('Y-m-d', PdfStamper::POSITION_BOTTOM_LEFT);

$multiPath = __DIR__ . '/../target/stamp_multiple.pdf';
$stamper7->applyToFile($multiPath);
echo "   Added multiple stamps to: $multiPath\n\n";

// =========================================================================
// Step 10: Using convenience method
// =========================================================================

echo "10. Using convenience method...\n";

$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$conveniencePath = __DIR__ . '/../target/stamp_convenience.pdf';
$pdf2->addWatermarkToFile($sourcePath, 'SAMPLE', $conveniencePath);

echo "    Added watermark via convenience method to: $conveniencePath\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- PdfStamper class for adding stamps and watermarks\n";
echo "- Text stamps at various positions\n";
echo "- Diagonal watermarks with rotation\n";
echo "- DRAFT and CONFIDENTIAL preset watermarks\n";
echo "- Page numbering with customizable format\n";
echo "- Headers and footers\n";
echo "- Date stamps\n";
echo "- Multiple stamps on single document\n\n";

echo "PdfStamper Methods:\n";
echo "-------------------\n";
echo "- loadFile(\$path): Load PDF from file\n";
echo "- loadContent(\$content): Load PDF from string\n";
echo "- addTextStamp(\$text, \$config): Add text stamp\n";
echo "- addWatermark(\$text, \$config): Add watermark\n";
echo "- addDraftWatermark(): Add DRAFT watermark\n";
echo "- addConfidentialWatermark(): Add CONFIDENTIAL watermark\n";
echo "- addPageNumbers(\$format, \$position, \$config): Add page numbers\n";
echo "- addHeader(\$text, \$config): Add header\n";
echo "- addFooter(\$text, \$config): Add footer\n";
echo "- addDateStamp(\$format, \$position, \$config): Add date stamp\n";
echo "- apply(): Get stamped PDF content\n";
echo "- applyToFile(\$path): Save stamped PDF to file\n";
echo "- getStampCount(): Get number of stamps added\n";
echo "- clearStamps(): Clear all stamps\n\n";

echo "Position Constants:\n";
echo "-------------------\n";
echo "- PdfStamper::POSITION_TOP_LEFT\n";
echo "- PdfStamper::POSITION_TOP_CENTER\n";
echo "- PdfStamper::POSITION_TOP_RIGHT\n";
echo "- PdfStamper::POSITION_CENTER_LEFT\n";
echo "- PdfStamper::POSITION_CENTER\n";
echo "- PdfStamper::POSITION_CENTER_RIGHT\n";
echo "- PdfStamper::POSITION_BOTTOM_LEFT\n";
echo "- PdfStamper::POSITION_BOTTOM_CENTER\n";
echo "- PdfStamper::POSITION_BOTTOM_RIGHT\n";
