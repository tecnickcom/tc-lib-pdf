<?php
/**
 * pdf_barcode.php
 *
 * Example demonstrating PDF barcode and QR code stamping.
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

use Com\Tecnick\Pdf\Manipulate\PdfBarcodeStamper;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "PDF Barcode Stamper Example\n";
echo "===========================\n\n";

// =========================================================================
// Step 1: Create a sample PDF
// =========================================================================

echo "1. Creating a sample PDF document...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf->setCreator('tc-lib-pdf');
$pdf->setTitle('Document with Barcodes');
$pdf->enableDefaultPageContent();
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Create 3 pages
for ($i = 1; $i <= 3; $i++) {
    $pdf->addPage();
    $pdf->page->addContent(
        "BT\n/F1 18 Tf\n1 0 0 1 28.35 800 Tm\n(Page {$i}) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 11 Tf\n1 0 0 1 28.35 770 Tm\n(This page will have barcodes added to it.) Tj\nET\n"
    );
}

$sourcePath = __DIR__ . '/../target/barcode_source.pdf';
@mkdir(dirname($sourcePath), 0755, true);
file_put_contents($sourcePath, $pdf->getOutPDFString());
echo "   Created: $sourcePath (3 pages)\n\n";

// =========================================================================
// Step 2: Demonstrate PdfBarcodeStamper API
// =========================================================================

echo "2. PdfBarcodeStamper Class API...\n\n";

echo "   Creating a stamper:\n";
echo "   -------------------\n";
echo "   \$stamper = new PdfBarcodeStamper();\n";
echo "   // or via Tcpdf: \$stamper = \$pdf->createBarcodeStamper();\n\n";

echo "   Loading PDF:\n";
echo "   ------------\n";
echo "   \$stamper->loadFile('document.pdf');\n";
echo "   \$stamper->loadContent(\$pdfContent);\n\n";

echo "   Adding barcodes:\n";
echo "   ----------------\n";
echo "   \$stamper->addQRCode(\$content, \$x, \$y, \$size, \$pages, \$color);\n";
echo "   \$stamper->addBarcode(\$type, \$content, \$x, \$y, \$w, \$h, \$pages);\n";
echo "   \$stamper->addCode128(\$content, \$x, \$y, \$w, \$h, \$pages);\n";
echo "   \$stamper->addDataMatrix(\$content, \$x, \$y, \$size, \$pages);\n\n";

echo "   Applying changes:\n";
echo "   -----------------\n";
echo "   \$modifiedPdf = \$stamper->apply();\n";
echo "   \$stamper->applyToFile('output.pdf');\n\n";

// =========================================================================
// Step 3: Add QR code to all pages
// =========================================================================

echo "3. Adding QR code to all pages...\n";

$stamper = new PdfBarcodeStamper();
$stamper->loadFile($sourcePath);

// Add QR code at bottom-right of all pages
$stamper->addQRCode('https://github.com/tecnickcom/tc-lib-pdf', 480, 100, 80);

$qrPath = __DIR__ . '/../target/barcode_qr.pdf';
$stamper->applyToFile($qrPath);
echo "   Created: $qrPath (QR code on all pages)\n\n";

// =========================================================================
// Step 4: Add QR code to specific pages
// =========================================================================

echo "4. Adding QR code to specific pages only...\n";

$stamper2 = new PdfBarcodeStamper();
$stamper2->loadFile($sourcePath);

// Add QR code only to pages 1 and 3
$stamper2->addQRCode('https://example.com', 50, 100, 100, [1, 3]);

$specificPath = __DIR__ . '/../target/barcode_specific_pages.pdf';
$stamper2->applyToFile($specificPath);
echo "   Created: $specificPath (QR code on pages 1 and 3 only)\n\n";

// =========================================================================
// Step 5: Add Code 128 barcode
// =========================================================================

echo "5. Adding Code 128 barcode...\n";

$stamper3 = new PdfBarcodeStamper();
$stamper3->loadFile($sourcePath);

// Add Code 128 barcode
$stamper3->addCode128('ABC-123456', 50, 650, 200, 50);

$code128Path = __DIR__ . '/../target/barcode_code128.pdf';
$stamper3->applyToFile($code128Path);
echo "   Created: $code128Path\n\n";

// =========================================================================
// Step 6: Add multiple barcodes
// =========================================================================

echo "6. Adding multiple barcodes to a document...\n";

$stamper4 = new PdfBarcodeStamper();
$stamper4->loadFile($sourcePath);

// QR code with URL
$stamper4->addQRCode('https://tecnick.com', 450, 700, 100);

// Code 128 with product code
$stamper4->addCode128('PROD-2024-001', 50, 650, 200, 40);

// Another QR code for tracking
$stamper4->addQRCode('TRACK:12345678', 50, 100, 80, 'all', '#0066cc');

$multiplePath = __DIR__ . '/../target/barcode_multiple.pdf';
$stamper4->applyToFile($multiplePath);
echo "   Created: $multiplePath\n\n";

// =========================================================================
// Step 7: Colored barcodes
// =========================================================================

echo "7. Adding colored barcodes...\n";

$stamper5 = new PdfBarcodeStamper();
$stamper5->loadFile($sourcePath);

// Blue QR code
$stamper5->addQRCode('Blue QR', 50, 600, 80, 'all', '#0000ff');

// Red QR code
$stamper5->addQRCode('Red QR', 150, 600, 80, 'all', '#ff0000');

// Green QR code
$stamper5->addQRCode('Green QR', 250, 600, 80, 'all', '#00aa00');

$coloredPath = __DIR__ . '/../target/barcode_colored.pdf';
$stamper5->applyToFile($coloredPath);
echo "   Created: $coloredPath\n\n";

// =========================================================================
// Step 8: Different barcode types
// =========================================================================

echo "8. Different barcode types...\n";

$stamper6 = new PdfBarcodeStamper();
$stamper6->loadFile($sourcePath);

// QR Code
$stamper6->addBarcode('QRCODE', 'QR Code Example', 50, 700, 80, 80);

// Code 128
$stamper6->addBarcode('C128', '1234567890', 150, 720, 150, 40);

// Code 39
$stamper6->addBarcode('C39', 'CODE39', 320, 720, 150, 40);

$typesPath = __DIR__ . '/../target/barcode_types.pdf';
$stamper6->applyToFile($typesPath);
echo "   Created: $typesPath\n\n";

// =========================================================================
// Step 9: Supported barcode types
// =========================================================================

echo "9. Supported barcode types:\n";

$stamper7 = new PdfBarcodeStamper();
$types = $stamper7->getSupportedTypes();
echo "   ";
echo implode(', ', $types);
echo "\n\n";

// =========================================================================
// Step 10: Using convenience method
// =========================================================================

echo "10. Using convenience method...\n";

$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$conveniencePath = __DIR__ . '/../target/barcode_convenience.pdf';
$pdf2->addQRCodeToPdf(
    $sourcePath,
    'Added via convenience method',
    400,
    50,
    150,
    $conveniencePath
);
echo "    Created: $conveniencePath\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- PdfBarcodeStamper class for adding barcodes to PDFs\n";
echo "- Adding QR codes to documents\n";
echo "- Adding Code 128 barcodes\n";
echo "- Adding barcodes to all or specific pages\n";
echo "- Multiple barcodes on same document\n";
echo "- Colored barcodes\n";
echo "- Different barcode types (QR, C128, C39, etc.)\n\n";

echo "PdfBarcodeStamper Methods:\n";
echo "--------------------------\n";
echo "- loadFile(\$path): Load PDF from file\n";
echo "- loadContent(\$content): Load PDF from string\n";
echo "- addQRCode(\$content, \$x, \$y, \$size, \$pages, \$color): Add QR code\n";
echo "- addBarcode(\$type, \$content, \$x, \$y, \$w, \$h, \$pages, \$color): Add barcode\n";
echo "- addCode128(\$content, \$x, \$y, \$w, \$h, \$pages, \$color): Add Code 128\n";
echo "- addDataMatrix(\$content, \$x, \$y, \$size, \$pages, \$color): Add DataMatrix\n";
echo "- getSupportedTypes(): Get list of supported barcode types\n";
echo "- clearBarcodes(): Remove all pending barcodes\n";
echo "- apply(): Get modified PDF content\n";
echo "- applyToFile(\$path): Save modified PDF\n";
