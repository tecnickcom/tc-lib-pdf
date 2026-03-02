<?php
/**
 * pdf_linearize.php
 *
 * Example demonstrating PDF linearization for fast web view.
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

use Com\Tecnick\Pdf\Manipulate\PdfLinearizer;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "PDF Linearization Example\n";
echo "=========================\n\n";

// =========================================================================
// Step 1: Create a sample PDF document
// =========================================================================

echo "1. Creating a sample multi-page PDF document...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf->setCreator('tc-lib-pdf');
$pdf->setTitle('Linearization Demo');
$pdf->enableDefaultPageContent();
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Create 10 pages with content
for ($i = 1; $i <= 10; $i++) {
    $pdf->addPage();
    $pdf->page->addContent(
        "BT\n/F1 24 Tf\n1 0 0 1 200 750 Tm\n(Page {$i}) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 14 Tf\n1 0 0 1 100 700 Tm\n(PDF Linearization Demo Document) Tj\nET\n"
    );

    // Add content blocks
    for ($j = 0; $j < 5; $j++) {
        $y = 650 - ($j * 40);
        $pdf->page->addContent(
            "BT\n/F1 11 Tf\n1 0 0 1 50 {$y} Tm\n(Content block {$j} on page {$i}.) Tj\nET\n"
        );
    }

    // Add some graphics
    $pdf->page->addContent("q 0.8 0 0 rg 50 350 200 100 re f Q\n");
    $pdf->page->addContent("q 0 0.8 0 rg 100 300 200 100 re f Q\n");
    $pdf->page->addContent("q 0 0 0.8 rg 150 250 200 100 re f Q\n");
}

$sourcePath = __DIR__ . '/../target/linearize_source.pdf';
@mkdir(dirname($sourcePath), 0755, true);
file_put_contents($sourcePath, $pdf->getOutPDFString());
$sourceSize = filesize($sourcePath);
echo "   Created: $sourcePath\n";
echo "   Size: " . number_format($sourceSize) . " bytes (10 pages)\n\n";

// =========================================================================
// Step 2: Demonstrate PdfLinearizer API
// =========================================================================

echo "2. PdfLinearizer Class API...\n\n";

echo "   Creating a linearizer:\n";
echo "   ----------------------\n";
echo "   \$linearizer = new PdfLinearizer();\n";
echo "   // or via Tcpdf: \$linearizer = \$pdf->createLinearizer();\n\n";

echo "   Loading PDF:\n";
echo "   ------------\n";
echo "   \$linearizer->loadFile('document.pdf');\n";
echo "   \$linearizer->loadContent(\$pdfContent);\n\n";

echo "   Checking linearization:\n";
echo "   -----------------------\n";
echo "   \$isLinearized = \$linearizer->isLinearized();\n\n";

echo "   Getting info:\n";
echo "   -------------\n";
echo "   \$pageCount = \$linearizer->getPageCount();\n";
echo "   \$version = \$linearizer->getVersion();\n";
echo "   \$objectCount = \$linearizer->getObjectCount();\n\n";

echo "   Linearizing:\n";
echo "   ------------\n";
echo "   \$linearizedPdf = \$linearizer->linearize();\n";
echo "   \$linearizer->linearizeToFile('output.pdf');\n\n";

// =========================================================================
// Step 3: Check if source is linearized
// =========================================================================

echo "3. Checking if source PDF is linearized...\n";

$linearizer = new PdfLinearizer();
$linearizer->loadFile($sourcePath);

echo "   Source is linearized: " . ($linearizer->isLinearized() ? 'Yes' : 'No') . "\n";
echo "   Page count: " . $linearizer->getPageCount() . "\n";
echo "   PDF version: " . $linearizer->getVersion() . "\n";
echo "   Object count: " . $linearizer->getObjectCount() . "\n\n";

// =========================================================================
// Step 4: Linearize the PDF
// =========================================================================

echo "4. Linearizing the PDF...\n";

$linearizer2 = new PdfLinearizer();
$linearizer2->loadFile($sourcePath);

$linearPath = __DIR__ . '/../target/linearized_output.pdf';
$linearizer2->linearizeToFile($linearPath);
$linearSize = filesize($linearPath);

echo "   Created: $linearPath\n";
echo "   Original size: " . number_format($linearizer2->getOriginalSize()) . " bytes\n";
echo "   Linearized size: " . number_format($linearizer2->getLinearizedSize()) . " bytes\n";

$stats = $linearizer2->getStatistics();
echo "   First page end offset: " . number_format($stats['firstPageOffset']) . " bytes\n\n";

// =========================================================================
// Step 5: Verify linearization
// =========================================================================

echo "5. Verifying linearized PDF...\n";

$verifier = new PdfLinearizer();
$verifier->loadFile($linearPath);

echo "   Output is linearized: " . ($verifier->isLinearized() ? 'Yes' : 'No') . "\n";
echo "   Page count preserved: " . ($verifier->getPageCount() === 10 ? 'Yes' : 'No') . "\n\n";

// =========================================================================
// Step 6: Using convenience method from Tcpdf
// =========================================================================

echo "6. Using convenience method...\n";

$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$conveniencePath = __DIR__ . '/../target/linearize_convenience.pdf';
$pdf2->linearizePdf($sourcePath, $conveniencePath);
echo "   Created: $conveniencePath\n";
echo "   Size: " . number_format(filesize($conveniencePath)) . " bytes\n\n";

// =========================================================================
// Step 7: Check already linearized file
// =========================================================================

echo "7. Checking convenience output (should be linearized)...\n";

echo "   Is linearized: " . ($pdf2->isPdfLinearized($conveniencePath) ? 'Yes' : 'No') . "\n\n";

// =========================================================================
// Step 8: Linearize PDF from content
// =========================================================================

echo "8. Linearizing from PDF content...\n";

$pdfContent = file_get_contents($sourcePath);
$linearizer3 = new PdfLinearizer();
$linearizer3->loadContent($pdfContent);

$fromContentPath = __DIR__ . '/../target/linearized_from_content.pdf';
$linearizer3->linearizeToFile($fromContentPath);
echo "   Created: $fromContentPath\n";
echo "   Size: " . number_format(filesize($fromContentPath)) . " bytes\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- PdfLinearizer class for fast web view optimization\n";
echo "- Check if PDF is already linearized\n";
echo "- Linearize existing PDF files\n";
echo "- Linearize PDF content strings\n";
echo "- Get PDF structure information (pages, objects, version)\n\n";

echo "What is PDF Linearization?\n";
echo "--------------------------\n";
echo "Linearization (also called 'Fast Web View') restructures a PDF so that:\n";
echo "- The first page can display before the entire file is downloaded\n";
echo "- The linearization dictionary appears first in the file\n";
echo "- First page objects are positioned early in the file\n";
echo "- Hint streams help viewers locate page content quickly\n\n";

echo "PdfLinearizer Methods:\n";
echo "----------------------\n";
echo "- loadFile(\$path): Load PDF from file\n";
echo "- loadContent(\$content): Load PDF from string\n";
echo "- isLinearized(): Check if PDF has linearization dictionary\n";
echo "- getPageCount(): Get number of pages\n";
echo "- getVersion(): Get PDF version\n";
echo "- getObjectCount(): Get number of PDF objects\n";
echo "- getOriginalSize(): Get original file size\n";
echo "- getLinearizedSize(): Get output size after linearization\n";
echo "- getStatistics(): Get detailed linearization stats\n";
echo "- linearize(): Get linearized PDF content as string\n";
echo "- linearizeToFile(\$path): Save linearized PDF to file\n\n";

echo "Use Cases:\n";
echo "----------\n";
echo "- Web-served PDFs (faster initial display)\n";
echo "- Large documents where progressive loading matters\n";
echo "- PDF viewers that support byte-range requests\n";
echo "- Improving user experience for slow connections\n";
