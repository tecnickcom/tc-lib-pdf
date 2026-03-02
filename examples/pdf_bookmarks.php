<?php
/**
 * pdf_bookmarks.php
 *
 * Example demonstrating PDF bookmark (outline) management.
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

use Com\Tecnick\Pdf\Manipulate\PdfBookmarkManager;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "PDF Bookmark Manager Example\n";
echo "============================\n\n";

// =========================================================================
// Step 1: Create a multi-page PDF to add bookmarks to
// =========================================================================

echo "1. Creating a multi-page PDF document...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf->setCreator('tc-lib-pdf');
$pdf->setTitle('Document with Bookmarks');
$pdf->enableDefaultPageContent();
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Create pages with chapter content
$chapters = [
    'Introduction',
    'Getting Started',
    'Basic Concepts',
    'Advanced Topics',
    'Best Practices',
    'Troubleshooting',
    'Conclusion',
];

foreach ($chapters as $index => $chapter) {
    $pdf->addPage();
    $pageNum = $index + 1;
    $pdf->page->addContent(
        "BT\n/F1 24 Tf\n1 0 0 1 28.35 800 Tm\n(Chapter {$pageNum}: {$chapter}) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 11 Tf\n1 0 0 1 28.35 760 Tm\n(This is the content for chapter {$pageNum}.) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 11 Tf\n1 0 0 1 28.35 740 Tm\n(Each chapter covers different aspects of the topic.) Tj\nET\n"
    );
}

$sourcePath = __DIR__ . '/../target/bookmarks_source.pdf';
@mkdir(dirname($sourcePath), 0755, true);
file_put_contents($sourcePath, $pdf->getOutPDFString());
echo "   Created: $sourcePath (" . count($chapters) . " pages)\n\n";

// =========================================================================
// Step 2: Demonstrate PdfBookmarkManager API
// =========================================================================

echo "2. PdfBookmarkManager Class API...\n\n";

echo "   Creating a manager:\n";
echo "   -------------------\n";
echo "   \$manager = new PdfBookmarkManager();\n";
echo "   // or via Tcpdf: \$manager = \$pdf->createBookmarkManager();\n\n";

echo "   Loading PDF:\n";
echo "   ------------\n";
echo "   \$manager->loadFile('document.pdf');\n";
echo "   \$manager->loadContent(\$pdfContent);\n\n";

echo "   Managing bookmarks:\n";
echo "   -------------------\n";
echo "   \$manager->addBookmark('Title', \$page, \$level);\n";
echo "   \$manager->addBookmarks([\$bookmark1, \$bookmark2]);\n";
echo "   \$manager->getBookmarks();\n";
echo "   \$manager->removeAllBookmarks();\n\n";

echo "   Applying changes:\n";
echo "   -----------------\n";
echo "   \$modifiedPdf = \$manager->apply();\n";
echo "   \$manager->applyToFile('output.pdf');\n\n";

// =========================================================================
// Step 3: Add simple bookmarks
// =========================================================================

echo "3. Adding simple bookmarks...\n";

$manager = new PdfBookmarkManager();
$manager->loadFile($sourcePath);

// Add bookmarks for each chapter
foreach ($chapters as $index => $chapter) {
    $manager->addBookmark("Chapter " . ($index + 1) . ": " . $chapter, $index + 1);
}

echo "   Added " . $manager->getBookmarkCount() . " bookmarks\n";

$simplePath = __DIR__ . '/../target/bookmarks_simple.pdf';
$manager->applyToFile($simplePath);
echo "   Created: $simplePath\n\n";

// =========================================================================
// Step 4: Add hierarchical bookmarks
// =========================================================================

echo "4. Adding hierarchical bookmarks...\n";

$manager2 = new PdfBookmarkManager();
$manager2->loadFile($sourcePath);

// Top-level sections with sub-sections
$manager2->addBookmark('Part I: Basics', 1, 0);
$manager2->addBookmark('Introduction', 1, 1);
$manager2->addBookmark('Getting Started', 2, 1);
$manager2->addBookmark('Basic Concepts', 3, 1);

$manager2->addBookmark('Part II: Advanced', 4, 0);
$manager2->addBookmark('Advanced Topics', 4, 1);
$manager2->addBookmark('Best Practices', 5, 1);

$manager2->addBookmark('Part III: Reference', 6, 0);
$manager2->addBookmark('Troubleshooting', 6, 1);
$manager2->addBookmark('Conclusion', 7, 1);

echo "   Added " . $manager2->getBookmarkCount() . " hierarchical bookmarks\n";

$hierarchyPath = __DIR__ . '/../target/bookmarks_hierarchy.pdf';
$manager2->applyToFile($hierarchyPath);
echo "   Created: $hierarchyPath\n\n";

// =========================================================================
// Step 5: Read existing bookmarks
// =========================================================================

echo "5. Reading existing bookmarks...\n";

$reader = new PdfBookmarkManager();
$reader->loadFile($simplePath);

$bookmarks = $reader->getBookmarks();
echo "   Found " . count($bookmarks) . " bookmarks:\n";
foreach ($bookmarks as $bookmark) {
    $indent = str_repeat('  ', $bookmark['level']);
    echo "   {$indent}- {$bookmark['title']} (page {$bookmark['page']})\n";
}
echo "\n";

// =========================================================================
// Step 6: Add bookmarks in bulk
// =========================================================================

echo "6. Adding bookmarks in bulk...\n";

$manager3 = new PdfBookmarkManager();
$manager3->loadFile($sourcePath);

$bulkBookmarks = [
    ['title' => 'Overview', 'page' => 1, 'level' => 0],
    ['title' => 'What is this?', 'page' => 1, 'level' => 1],
    ['title' => 'Why use it?', 'page' => 1, 'level' => 1],
    ['title' => 'Main Content', 'page' => 2, 'level' => 0],
    ['title' => 'Chapter A', 'page' => 2, 'level' => 1],
    ['title' => 'Chapter B', 'page' => 3, 'level' => 1],
    ['title' => 'Chapter C', 'page' => 4, 'level' => 1],
    ['title' => 'Appendix', 'page' => 5, 'level' => 0],
];

$manager3->addBookmarks($bulkBookmarks);

$bulkPath = __DIR__ . '/../target/bookmarks_bulk.pdf';
$manager3->applyToFile($bulkPath);
echo "   Added " . count($bulkBookmarks) . " bookmarks in bulk\n";
echo "   Created: $bulkPath\n\n";

// =========================================================================
// Step 7: Remove bookmarks
// =========================================================================

echo "7. Removing bookmarks...\n";

$manager4 = new PdfBookmarkManager();
$manager4->loadFile($simplePath);

echo "   Initial bookmarks: " . $manager4->getBookmarkCount() . "\n";

// Remove bookmarks for page 3
$manager4->removeBookmarksByPage(3);
echo "   After removing page 3 bookmarks: " . $manager4->getBookmarkCount() . "\n";

$removedPath = __DIR__ . '/../target/bookmarks_removed.pdf';
$manager4->applyToFile($removedPath);
echo "   Created: $removedPath\n\n";

// =========================================================================
// Step 8: Clear all bookmarks
// =========================================================================

echo "8. Clearing all bookmarks...\n";

$manager5 = new PdfBookmarkManager();
$manager5->loadFile($simplePath);

echo "   Bookmarks before clear: " . $manager5->getBookmarkCount() . "\n";
$manager5->removeAllBookmarks();
echo "   Bookmarks after clear: " . $manager5->getBookmarkCount() . "\n";

$clearedPath = __DIR__ . '/../target/bookmarks_cleared.pdf';
$manager5->applyToFile($clearedPath);
echo "   Created: $clearedPath (no bookmarks)\n\n";

// =========================================================================
// Step 9: Using convenience method
// =========================================================================

echo "9. Using convenience method...\n";

$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$convenienceBookmarks = [
    ['title' => 'Home', 'page' => 1],
    ['title' => 'About', 'page' => 2],
    ['title' => 'Contact', 'page' => 7],
];

$conveniencePath = __DIR__ . '/../target/bookmarks_convenience.pdf';
$pdf2->addPdfBookmarks($sourcePath, $convenienceBookmarks, $conveniencePath);
echo "   Created: $conveniencePath\n\n";

// =========================================================================
// Step 10: Bookmarks with Y position
// =========================================================================

echo "10. Bookmarks with specific Y positions...\n";

$manager6 = new PdfBookmarkManager();
$manager6->loadFile($sourcePath);

// Add bookmarks pointing to specific locations on pages
$manager6->addBookmark('Page 1 - Top', 1, 0, 800);
$manager6->addBookmark('Page 1 - Middle', 1, 0, 400);
$manager6->addBookmark('Page 2 - Top', 2, 0, 800);
$manager6->addBookmark('Page 2 - Bottom', 2, 0, 100);

$positionPath = __DIR__ . '/../target/bookmarks_positions.pdf';
$manager6->applyToFile($positionPath);
echo "   Created: $positionPath (with Y positions)\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- PdfBookmarkManager class for managing PDF outlines\n";
echo "- Adding simple bookmarks to pages\n";
echo "- Creating hierarchical bookmark structures\n";
echo "- Bulk bookmark addition\n";
echo "- Reading existing bookmarks\n";
echo "- Removing bookmarks by page\n";
echo "- Clearing all bookmarks\n";
echo "- Specifying Y position for bookmark targets\n\n";

echo "PdfBookmarkManager Methods:\n";
echo "---------------------------\n";
echo "- loadFile(\$path): Load PDF from file\n";
echo "- loadContent(\$content): Load PDF from string\n";
echo "- getBookmarks(): Get all bookmarks\n";
echo "- getBookmarkCount(): Get bookmark count\n";
echo "- addBookmark(\$title, \$page, \$level, \$y): Add single bookmark\n";
echo "- addBookmarks(\$array): Add multiple bookmarks\n";
echo "- removeBookmarksByPage(\$page): Remove bookmarks for page\n";
echo "- removeBookmarksByLevel(\$level): Remove bookmarks at level\n";
echo "- removeAllBookmarks(): Clear all bookmarks\n";
echo "- apply(): Get modified PDF content\n";
echo "- applyToFile(\$path): Save modified PDF\n";
