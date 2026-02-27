<?php
/**
 * pdf_split.php
 *
 * Example demonstrating PDF split functionality.
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

use Com\Tecnick\Pdf\Manipulate\PdfSplitter;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "PDF Split Example\n";
echo "=================\n\n";

// =========================================================================
// Step 1: Create a sample multi-page PDF to split
// =========================================================================

echo "1. Creating a multi-page PDF document...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf->setCreator('tc-lib-pdf');
$pdf->setTitle('Multi-Page Document for Splitting');
$pdf->enableDefaultPageContent();
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Create 5 pages with different content
for ($i = 1; $i <= 5; $i++) {
    $pdf->addPage();
    $pdf->page->addContent(
        "BT\n/F1 18 Tf\n1 0 0 1 28.35 800 Tm\n(Page {$i} of 5) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 11 Tf\n1 0 0 1 28.35 770 Tm\n(This is page {$i} of the multi-page document.) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 11 Tf\n1 0 0 1 28.35 750 Tm\n(Each page can be extracted individually or in groups.) Tj\nET\n"
    );
}

$sourcePath = __DIR__ . '/../target/split_source.pdf';
@mkdir(dirname($sourcePath), 0755, true);
file_put_contents($sourcePath, $pdf->getOutPDFString());
echo "   Created: $sourcePath (5 pages)\n\n";

// =========================================================================
// Step 2: Demonstrate PdfSplitter API
// =========================================================================

echo "2. PdfSplitter Class API...\n\n";

echo "   Creating a splitter:\n";
echo "   --------------------\n";
echo "   \$splitter = new PdfSplitter();\n";
echo "   // or via Tcpdf: \$splitter = \$pdf->createSplitter();\n\n";

echo "   Loading PDF:\n";
echo "   ------------\n";
echo "   \$splitter->loadFile('document.pdf');\n";
echo "   \$splitter->loadContent(\$pdfContent);\n\n";

echo "   Extracting pages:\n";
echo "   -----------------\n";
echo "   \$page1 = \$splitter->extractPage(1);        // Single page\n";
echo "   \$pages = \$splitter->extractPages([1,3,5]); // Multiple pages\n";
echo "   \$pages = \$splitter->extractPages('1-3');   // Page range\n\n";

echo "   Splitting to files:\n";
echo "   -------------------\n";
echo "   \$splitter->splitToFiles('output/');          // One file per page\n";
echo "   \$chunks = \$splitter->splitByPageCount(2);   // Split into chunks (returns content)\n\n";

// =========================================================================
// Step 3: Extract a single page
// =========================================================================

echo "3. Extracting a single page...\n";

$splitter = new PdfSplitter();
$splitter->loadFile($sourcePath);

echo "   Total pages in source: " . $splitter->getPageCount() . "\n";

$page3Content = $splitter->extractPage(3);
$page3Path = __DIR__ . '/../target/split_page3.pdf';
file_put_contents($page3Path, $page3Content);
echo "   Extracted page 3 to: $page3Path\n\n";

// =========================================================================
// Step 4: Extract multiple specific pages
// =========================================================================

echo "4. Extracting specific pages (1, 3, 5)...\n";

$specificPages = $splitter->extractPages([1, 3, 5]);
$specificPath = __DIR__ . '/../target/split_pages_1_3_5.pdf';
file_put_contents($specificPath, $specificPages);
echo "   Extracted pages 1, 3, 5 to: $specificPath\n\n";

// =========================================================================
// Step 5: Extract a page range
// =========================================================================

echo "5. Extracting page range (2-4)...\n";

$rangePages = $splitter->extractPages('2-4');
$rangePath = __DIR__ . '/../target/split_pages_2_to_4.pdf';
file_put_contents($rangePath, $rangePages);
echo "   Extracted pages 2-4 to: $rangePath\n\n";

// =========================================================================
// Step 6: Split into individual files
// =========================================================================

echo "6. Splitting into individual files...\n";

$outputDir = __DIR__ . '/../target/split_individual/';
@mkdir($outputDir, 0755, true);
$files = $splitter->splitToFiles($outputDir, 'page_');

echo "   Created " . count($files) . " individual files:\n";
foreach ($files as $file) {
    echo "   - $file\n";
}
echo "\n";

// =========================================================================
// Step 7: Split into chunks
// =========================================================================

echo "7. Splitting into chunks (2 pages each)...\n";

$chunkDir = __DIR__ . '/../target/split_chunks/';
@mkdir($chunkDir, 0755, true);
$chunks = $splitter->splitByPageCount(2);

echo "   Created " . count($chunks) . " chunks:\n";
$chunkNum = 1;
foreach ($chunks as $chunkContent) {
    $chunkPath = $chunkDir . 'chunk_' . sprintf('%03d', $chunkNum) . '.pdf';
    file_put_contents($chunkPath, $chunkContent);
    echo "   - $chunkPath\n";
    $chunkNum++;
}
echo "\n";

// =========================================================================
// Step 8: Extract odd/even pages
// =========================================================================

echo "8. Extracting odd and even pages...\n";

$oddPages = $splitter->extractOddPages();
$oddPath = __DIR__ . '/../target/split_odd_pages.pdf';
file_put_contents($oddPath, $oddPages);
echo "   Extracted odd pages (1, 3, 5) to: $oddPath\n";

$evenPages = $splitter->extractEvenPages();
$evenPath = __DIR__ . '/../target/split_even_pages.pdf';
file_put_contents($evenPath, $evenPages);
echo "   Extracted even pages (2, 4) to: $evenPath\n\n";

// =========================================================================
// Step 9: Extract pages in reverse order
// =========================================================================

echo "9. Extracting pages in reverse order...\n";

$reversedPages = $splitter->extractReversed();
$reversedPath = __DIR__ . '/../target/split_reversed.pdf';
file_put_contents($reversedPath, $reversedPages);
echo "   Extracted reversed pages (5,4,3,2,1) to: $reversedPath\n\n";

// =========================================================================
// Step 10: Using convenience method
// =========================================================================

echo "10. Using convenience method...\n";

$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$convenienceDir = __DIR__ . '/../target/split_convenience/';
@mkdir($convenienceDir, 0755, true);
$convenienceFiles = $pdf2->splitPdfFile($sourcePath, $convenienceDir);

echo "    Split via convenience method:\n";
foreach ($convenienceFiles as $file) {
    echo "    - $file\n";
}
echo "\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- PdfSplitter class for splitting PDF documents\n";
echo "- Extracting single pages\n";
echo "- Extracting multiple specific pages\n";
echo "- Extracting page ranges (e.g., '2-4')\n";
echo "- Splitting into individual files\n";
echo "- Splitting into chunks of N pages\n";
echo "- Extracting odd/even pages\n";
echo "- Extracting pages in reverse order\n\n";

echo "PdfSplitter Methods:\n";
echo "--------------------\n";
echo "- loadFile(\$path): Load PDF from file\n";
echo "- loadContent(\$content): Load PDF from string\n";
echo "- getPageCount(): Get total page count\n";
echo "- extractPage(\$num): Extract single page\n";
echo "- extractPages(\$pages): Extract multiple pages\n";
echo "- splitToFiles(\$dir, \$prefix): Split into files\n";
echo "- splitByPageCount(\$count): Split into chunks (returns content)\n";
echo "- extractOddPages(): Extract odd pages\n";
echo "- extractEvenPages(): Extract even pages\n";
echo "- extractReversed(): Extract in reverse order\n";
