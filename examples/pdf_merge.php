do <?php
/**
 * pdf_merge.php
 *
 * Example demonstrating PDF merge functionality.
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

use Com\Tecnick\Pdf\Manipulate\PdfMerger;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "PDF Merge Example\n";
echo "=================\n\n";

// =========================================================================
// Step 1: Create sample PDF files to merge
// =========================================================================

echo "1. Creating sample PDF files...\n";

// Create first PDF
$pdf1 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf1->setCreator('tc-lib-pdf');
$pdf1->setTitle('Document 1');
$pdf1->enableDefaultPageContent();
$pdf1->font->insert($pdf1->pon, 'helvetica', '', 12);
$pdf1->addPage();
$pdf1->page->addContent(
    "BT\n/F1 14 Tf\n1 0 0 1 28.35 800 Tm\n(Document 1 - Page 1) Tj\nET\n"
);
$pdf1->page->addContent(
    "BT\n/F1 11 Tf\n1 0 0 1 28.35 770 Tm\n(This is the first page of the first document.) Tj\nET\n"
);
$pdf1->addPage();
$pdf1->page->addContent(
    "BT\n/F1 14 Tf\n1 0 0 1 28.35 800 Tm\n(Document 1 - Page 2) Tj\nET\n"
);
$pdf1->page->addContent(
    "BT\n/F1 11 Tf\n1 0 0 1 28.35 770 Tm\n(This is the second page of the first document.) Tj\nET\n"
);

$doc1Path = __DIR__ . '/../target/merge_doc1.pdf';
@mkdir(dirname($doc1Path), 0755, true);
file_put_contents($doc1Path, $pdf1->getOutPDFString());
echo "   Created: $doc1Path\n";

// Create second PDF
$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf2->setCreator('tc-lib-pdf');
$pdf2->setTitle('Document 2');
$pdf2->enableDefaultPageContent();
$pdf2->font->insert($pdf2->pon, 'helvetica', '', 12);
$pdf2->addPage();
$pdf2->page->addContent(
    "BT\n/F1 14 Tf\n1 0 0 1 28.35 800 Tm\n(Document 2 - Page 1) Tj\nET\n"
);
$pdf2->page->addContent(
    "BT\n/F1 11 Tf\n1 0 0 1 28.35 770 Tm\n(This is the first page of the second document.) Tj\nET\n"
);

$doc2Path = __DIR__ . '/../target/merge_doc2.pdf';
file_put_contents($doc2Path, $pdf2->getOutPDFString());
echo "   Created: $doc2Path\n";

// Create third PDF
$pdf3 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf3->setCreator('tc-lib-pdf');
$pdf3->setTitle('Document 3');
$pdf3->enableDefaultPageContent();
$pdf3->font->insert($pdf3->pon, 'helvetica', '', 12);
$pdf3->addPage();
$pdf3->page->addContent(
    "BT\n/F1 14 Tf\n1 0 0 1 28.35 800 Tm\n(Document 3 - Page 1) Tj\nET\n"
);
$pdf3->page->addContent(
    "BT\n/F1 11 Tf\n1 0 0 1 28.35 770 Tm\n(This is the first page of the third document.) Tj\nET\n"
);
$pdf3->addPage();
$pdf3->page->addContent(
    "BT\n/F1 14 Tf\n1 0 0 1 28.35 800 Tm\n(Document 3 - Page 2) Tj\nET\n"
);
$pdf3->addPage();
$pdf3->page->addContent(
    "BT\n/F1 14 Tf\n1 0 0 1 28.35 800 Tm\n(Document 3 - Page 3) Tj\nET\n"
);

$doc3Path = __DIR__ . '/../target/merge_doc3.pdf';
file_put_contents($doc3Path, $pdf3->getOutPDFString());
echo "   Created: $doc3Path\n\n";

// =========================================================================
// Step 2: Demonstrate PdfMerger API
// =========================================================================

echo "2. PdfMerger Class API...\n\n";

echo "   Creating a merger:\n";
echo "   ------------------\n";
echo "   \$merger = new PdfMerger();\n";
echo "   // or via Tcpdf: \$merger = \$pdf->createMerger();\n\n";

echo "   Adding files:\n";
echo "   -------------\n";
echo "   \$merger->addFile('document1.pdf');           // All pages\n";
echo "   \$merger->addFile('document2.pdf', [1, 3]);   // Specific pages\n";
echo "   \$merger->addFile('document3.pdf', '1-5');    // Page range\n";
echo "   \$merger->addFile('document4.pdf', 'all');    // All pages (explicit)\n\n";

echo "   Adding content directly:\n";
echo "   ------------------------\n";
echo "   \$merger->addContent(\$pdfContent);            // From string\n\n";

echo "   Merging:\n";
echo "   --------\n";
echo "   \$merged = \$merger->merge();                  // Get content\n";
echo "   \$merger->mergeToFile('output.pdf');          // Save to file\n\n";

// =========================================================================
// Step 3: Merge all documents
// =========================================================================

echo "3. Merging all documents...\n";

$merger = new PdfMerger();
$merger->addFile($doc1Path)      // 2 pages
       ->addFile($doc2Path)      // 1 page
       ->addFile($doc3Path);     // 3 pages

echo "   Added " . $merger->getSourceCount() . " PDF sources\n";

$mergedPath = __DIR__ . '/../target/merged_all.pdf';
$merger->mergeToFile($mergedPath);
echo "   Merged to: $mergedPath\n\n";

// =========================================================================
// Step 4: Merge specific pages
// =========================================================================

echo "4. Merging specific pages...\n";

$merger2 = new PdfMerger();
$merger2->addFile($doc1Path, [1])     // Only page 1 from doc1
        ->addFile($doc3Path, [1, 3]); // Pages 1 and 3 from doc3

$mergedSpecificPath = __DIR__ . '/../target/merged_specific.pdf';
$merger2->mergeToFile($mergedSpecificPath);
echo "   Merged specific pages to: $mergedSpecificPath\n\n";

// =========================================================================
// Step 5: Using convenience method
// =========================================================================

echo "5. Using convenience method...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$mergedContent = $pdf->mergePdfFiles([$doc1Path, $doc2Path]);
$conveniencePath = __DIR__ . '/../target/merged_convenience.pdf';
file_put_contents($conveniencePath, $mergedContent);
echo "   Merged via convenience method to: $conveniencePath\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- PdfMerger class for combining PDF documents\n";
echo "- Adding PDF files with all or specific pages\n";
echo "- Page range syntax (e.g., '1-5', '1,3,5')\n";
echo "- Direct content merging\n";
echo "- Convenience method via Tcpdf::mergePdfFiles()\n\n";

echo "PdfMerger Methods:\n";
echo "------------------\n";
echo "- addFile(\$path, \$pages): Add PDF file\n";
echo "- addContent(\$content, \$pages): Add PDF content\n";
echo "- setVersion(\$version): Set output PDF version\n";
echo "- getSourceCount(): Get number of sources\n";
echo "- clear(): Clear all sources\n";
echo "- merge(): Get merged PDF content\n";
echo "- mergeToFile(\$path): Save merged PDF to file\n";
