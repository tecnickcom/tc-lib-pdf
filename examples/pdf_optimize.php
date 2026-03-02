<?php
/**
 * pdf_optimize.php
 *
 * Example demonstrating PDF optimization and compression.
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

use Com\Tecnick\Pdf\Manipulate\PdfOptimizer;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "PDF Optimizer Example\n";
echo "=====================\n\n";

// =========================================================================
// Step 1: Create a sample PDF document (with redundant content)
// =========================================================================

echo "1. Creating a sample PDF document with redundant content...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf->setCreator('tc-lib-pdf');
$pdf->setTitle('Optimization Demo');
$pdf->enableDefaultPageContent();
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Create multiple pages with similar content (for demonstrating optimization)
for ($i = 1; $i <= 10; $i++) {
    $pdf->addPage();
    $pdf->page->addContent(
        "BT\n/F1 18 Tf\n1 0 0 1 28.35 800 Tm\n(Page {$i}) Tj\nET\n"
    );

    // Add repeated content blocks
    for ($j = 0; $j < 10; $j++) {
        $y = 750 - ($j * 30);
        $pdf->page->addContent(
            "BT\n/F1 11 Tf\n1 0 0 1 28.35 {$y} Tm\n(This is line {$j} of repeated content.) Tj\nET\n"
        );
    }

    // Add some graphics
    $pdf->page->addContent("q 1 0 0 1 50 400 cm 0 0 200 100 re S Q\n");
    $pdf->page->addContent("q 1 0 0 1 100 450 cm 0 0 100 50 re S Q\n");
}

$sourcePath = __DIR__ . '/../target/optimize_source.pdf';
@mkdir(dirname($sourcePath), 0755, true);
file_put_contents($sourcePath, $pdf->getOutPDFString());
$sourceSize = filesize($sourcePath);
echo "   Created: $sourcePath\n";
echo "   Size: " . PdfOptimizer::formatFileSize($sourceSize) . " ({$sourceSize} bytes)\n\n";

// =========================================================================
// Step 2: Demonstrate PdfOptimizer API
// =========================================================================

echo "2. PdfOptimizer Class API...\n\n";

echo "   Creating an optimizer:\n";
echo "   ----------------------\n";
echo "   \$optimizer = new PdfOptimizer();\n";
echo "   // or via Tcpdf: \$optimizer = \$pdf->createOptimizer();\n\n";

echo "   Loading PDF:\n";
echo "   ------------\n";
echo "   \$optimizer->loadFile('large.pdf');\n";
echo "   \$optimizer->loadContent(\$pdfContent);\n\n";

echo "   Optimization settings:\n";
echo "   ----------------------\n";
echo "   \$optimizer->setOptimizationLevel(PdfOptimizer::LEVEL_MAXIMUM);\n";
echo "   \$optimizer->compressStreams(true);\n";
echo "   \$optimizer->removeUnusedObjects(true);\n";
echo "   \$optimizer->removeDuplicateObjects(true);\n";
echo "   \$optimizer->setCompressionLevel(9);\n\n";

echo "   Getting statistics:\n";
echo "   -------------------\n";
echo "   \$originalSize = \$optimizer->getOriginalSize();\n";
echo "   \$optimizedSize = \$optimizer->getOptimizedSize();\n";
echo "   \$ratio = \$optimizer->getCompressionRatio();\n\n";

echo "   Optimizing:\n";
echo "   -----------\n";
echo "   \$optimizedPdf = \$optimizer->optimize();\n";
echo "   \$optimizer->optimizeToFile('small.pdf');\n\n";

// =========================================================================
// Step 3: Basic optimization
// =========================================================================

echo "3. Basic optimization...\n";

$optimizer = new PdfOptimizer();
$optimizer->loadFile($sourcePath);

echo "   Original size: " . PdfOptimizer::formatFileSize($optimizer->getOriginalSize()) . "\n";

$basicPath = __DIR__ . '/../target/optimize_basic.pdf';
$optimizer->optimizeToFile($basicPath);

echo "   Optimized size: " . PdfOptimizer::formatFileSize($optimizer->getOptimizedSize()) . "\n";
echo "   Compression ratio: " . $optimizer->getCompressionRatio() . "%\n";
echo "   Created: $basicPath\n\n";

// =========================================================================
// Step 4: Minimal optimization
// =========================================================================

echo "4. Minimal optimization (stream compression only)...\n";

$optimizer2 = new PdfOptimizer();
$optimizer2->loadFile($sourcePath)
           ->setOptimizationLevel(PdfOptimizer::LEVEL_MINIMAL);

$minimalPath = __DIR__ . '/../target/optimize_minimal.pdf';
$optimizer2->optimizeToFile($minimalPath);

echo "   Original size: " . PdfOptimizer::formatFileSize($optimizer2->getOriginalSize()) . "\n";
echo "   Optimized size: " . PdfOptimizer::formatFileSize($optimizer2->getOptimizedSize()) . "\n";
echo "   Compression ratio: " . $optimizer2->getCompressionRatio() . "%\n";
echo "   Created: $minimalPath\n\n";

// =========================================================================
// Step 5: Standard optimization
// =========================================================================

echo "5. Standard optimization...\n";

$optimizer3 = new PdfOptimizer();
$optimizer3->loadFile($sourcePath)
           ->setOptimizationLevel(PdfOptimizer::LEVEL_STANDARD);

$standardPath = __DIR__ . '/../target/optimize_standard.pdf';
$optimizer3->optimizeToFile($standardPath);

echo "   Original size: " . PdfOptimizer::formatFileSize($optimizer3->getOriginalSize()) . "\n";
echo "   Optimized size: " . PdfOptimizer::formatFileSize($optimizer3->getOptimizedSize()) . "\n";
echo "   Compression ratio: " . $optimizer3->getCompressionRatio() . "%\n";
echo "   Created: $standardPath\n\n";

// =========================================================================
// Step 6: Maximum optimization
// =========================================================================

echo "6. Maximum optimization...\n";

$optimizer4 = new PdfOptimizer();
$optimizer4->loadFile($sourcePath)
           ->setOptimizationLevel(PdfOptimizer::LEVEL_MAXIMUM);

$maxPath = __DIR__ . '/../target/optimize_maximum.pdf';
$optimizer4->optimizeToFile($maxPath);

echo "   Original size: " . PdfOptimizer::formatFileSize($optimizer4->getOriginalSize()) . "\n";
echo "   Optimized size: " . PdfOptimizer::formatFileSize($optimizer4->getOptimizedSize()) . "\n";
echo "   Compression ratio: " . $optimizer4->getCompressionRatio() . "%\n";
echo "   Created: $maxPath\n\n";

// =========================================================================
// Step 7: Custom optimization settings
// =========================================================================

echo "7. Custom optimization settings...\n";

$optimizer5 = new PdfOptimizer();
$optimizer5->loadFile($sourcePath)
           ->compressStreams(true)
           ->removeUnusedObjects(true)
           ->removeDuplicateObjects(false)
           ->setCompressionLevel(6);

$customPath = __DIR__ . '/../target/optimize_custom.pdf';
$optimizer5->optimizeToFile($customPath);

echo "   Settings: compress=true, removeUnused=true, removeDuplicates=false, level=6\n";
echo "   Original size: " . PdfOptimizer::formatFileSize($optimizer5->getOriginalSize()) . "\n";
echo "   Optimized size: " . PdfOptimizer::formatFileSize($optimizer5->getOptimizedSize()) . "\n";
echo "   Compression ratio: " . $optimizer5->getCompressionRatio() . "%\n";
echo "   Created: $customPath\n\n";

// =========================================================================
// Step 8: Get detailed statistics
// =========================================================================

echo "8. Detailed optimization statistics...\n";

$optimizer6 = new PdfOptimizer();
$optimizer6->loadFile($sourcePath)
           ->setOptimizationLevel(PdfOptimizer::LEVEL_MAXIMUM);
$optimizer6->optimize();

$stats = $optimizer6->getStatistics();
echo "   Original size: " . PdfOptimizer::formatFileSize($stats['originalSize']) . "\n";
echo "   Optimized size: " . PdfOptimizer::formatFileSize($stats['optimizedSize']) . "\n";
echo "   Compression ratio: " . $stats['compressionRatio'] . "%\n";
echo "   Objects removed: " . $stats['objectsRemoved'] . "\n";
echo "   Duplicates removed: " . $stats['duplicatesRemoved'] . "\n\n";

// =========================================================================
// Step 9: Using convenience method from Tcpdf
// =========================================================================

echo "9. Using convenience method...\n";

$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$conveniencePath = __DIR__ . '/../target/optimize_convenience.pdf';
$pdf2->optimizePdf($sourcePath, $conveniencePath, PdfOptimizer::LEVEL_STANDARD);
$convenienceSize = filesize($conveniencePath);
echo "   Created: $conveniencePath\n";
echo "   Size: " . PdfOptimizer::formatFileSize($convenienceSize) . "\n\n";

// =========================================================================
// Step 10: Compression level comparison
// =========================================================================

echo "10. Compression level comparison...\n";

for ($level = 1; $level <= 9; $level++) {
    $opt = new PdfOptimizer();
    $opt->loadFile($sourcePath)
        ->setCompressionLevel($level);
    $opt->optimize();
    echo "    Level $level: " . PdfOptimizer::formatFileSize($opt->getOptimizedSize()) .
         " (" . $opt->getCompressionRatio() . "% saved)\n";
}
echo "\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- PdfOptimizer class for PDF file size reduction\n";
echo "- Multiple optimization levels (minimal, standard, maximum)\n";
echo "- Stream compression with configurable levels\n";
echo "- Removal of unused objects\n";
echo "- Detection and removal of duplicate objects\n";
echo "- Detailed optimization statistics\n\n";

echo "PdfOptimizer Methods:\n";
echo "---------------------\n";
echo "- loadFile(\$path): Load PDF from file\n";
echo "- loadContent(\$content): Load PDF from string\n";
echo "- setOptimizationLevel(\$level): Set preset level\n";
echo "- compressStreams(\$bool): Enable stream compression\n";
echo "- removeUnusedObjects(\$bool): Remove unused objects\n";
echo "- removeDuplicateObjects(\$bool): Remove duplicates\n";
echo "- setCompressionLevel(\$level): Set zlib level (1-9)\n";
echo "- getOriginalSize(): Get original size in bytes\n";
echo "- getOptimizedSize(): Get optimized size in bytes\n";
echo "- getCompressionRatio(): Get percentage saved\n";
echo "- getStatistics(): Get detailed statistics\n";
echo "- formatFileSize(\$bytes): Format bytes as human-readable\n";
echo "- optimize(): Get optimized PDF content\n";
echo "- optimizeToFile(\$path): Save optimized PDF\n\n";

echo "Optimization Levels:\n";
echo "--------------------\n";
echo "- LEVEL_MINIMAL: Stream compression only\n";
echo "- LEVEL_STANDARD: Compression + remove unused\n";
echo "- LEVEL_MAXIMUM: All optimizations enabled\n";
