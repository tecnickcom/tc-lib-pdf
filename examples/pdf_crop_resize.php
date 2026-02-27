<?php
/**
 * pdf_crop_resize.php
 *
 * Example demonstrating PDF page crop and resize operations.
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

use Com\Tecnick\Pdf\Manipulate\PdfPageBoxEditor;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "PDF Page Crop/Resize Example\n";
echo "============================\n\n";

// =========================================================================
// Step 1: Create a sample multi-page PDF with different page sizes
// =========================================================================

echo "1. Creating a sample PDF document (A4 size)...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf->setCreator('tc-lib-pdf');
$pdf->setTitle('Page Crop/Resize Demo');
$pdf->enableDefaultPageContent();
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Create 4 A4 pages with content
for ($i = 1; $i <= 4; $i++) {
    $pdf->addPage();
    // Page number at top
    $pdf->page->addContent(
        "BT\n/F1 24 Tf\n1 0 0 1 28.35 800 Tm\n(Page {$i}) Tj\nET\n"
    );
    // Corner markers to show page boundaries
    $pdf->page->addContent(
        "BT\n/F1 10 Tf\n1 0 0 1 10 830 Tm\n(TOP-LEFT) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 10 Tf\n1 0 0 1 500 830 Tm\n(TOP-RIGHT) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 10 Tf\n1 0 0 1 10 10 Tm\n(BOTTOM-LEFT) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 10 Tf\n1 0 0 1 480 10 Tm\n(BOTTOM-RIGHT) Tj\nET\n"
    );
    // Center content
    $pdf->page->addContent(
        "BT\n/F1 14 Tf\n1 0 0 1 200 400 Tm\n(CENTER CONTENT) Tj\nET\n"
    );
}

$sourcePath = __DIR__ . '/../target/crop_source.pdf';
@mkdir(dirname($sourcePath), 0755, true);
file_put_contents($sourcePath, $pdf->getOutPDFString());
echo "   Created: $sourcePath (4 pages, A4 size)\n\n";

// =========================================================================
// Step 2: Demonstrate PdfPageBoxEditor API
// =========================================================================

echo "2. PdfPageBoxEditor Class API...\n\n";

echo "   Creating an editor:\n";
echo "   -------------------\n";
echo "   \$editor = new PdfPageBoxEditor();\n";
echo "   // or via Tcpdf: \$editor = \$pdf->createPageBoxEditor();\n\n";

echo "   Loading PDF:\n";
echo "   ------------\n";
echo "   \$editor->loadFile('document.pdf');\n";
echo "   \$editor->loadContent(\$pdfContent);\n\n";

echo "   Setting page boxes:\n";
echo "   -------------------\n";
echo "   \$editor->setMediaBox(\$llx, \$lly, \$urx, \$ury, \$pages);\n";
echo "   \$editor->setCropBox(\$llx, \$lly, \$urx, \$ury, \$pages);\n";
echo "   \$editor->setBleedBox(\$llx, \$lly, \$urx, \$ury, \$pages);\n";
echo "   \$editor->setTrimBox(\$llx, \$lly, \$urx, \$ury, \$pages);\n";
echo "   \$editor->setArtBox(\$llx, \$lly, \$urx, \$ury, \$pages);\n\n";

echo "   Convenience methods:\n";
echo "   --------------------\n";
echo "   \$editor->cropToSize(\$size, \$pages);      // Crop to standard size\n";
echo "   \$editor->cropTo(\$w, \$h, \$pages);         // Crop to custom dimensions\n";
echo "   \$editor->resizeTo(\$size, \$pages);        // Resize to standard size\n";
echo "   \$editor->resizeToCustom(\$w, \$h, \$pages); // Resize to custom dimensions\n";
echo "   \$editor->addMargin(\$margin, \$pages);     // Add uniform margin\n";
echo "   \$editor->addMargins(\$t, \$r, \$b, \$l, \$pages); // Add individual margins\n\n";

echo "   Applying changes:\n";
echo "   -----------------\n";
echo "   \$modifiedPdf = \$editor->apply();\n";
echo "   \$editor->applyToFile('output.pdf');\n\n";

// =========================================================================
// Step 3: Crop to A5 size
// =========================================================================

echo "3. Cropping all pages to A5 size...\n";

$editor = new PdfPageBoxEditor();
$editor->loadFile($sourcePath);

echo "   Original page count: " . $editor->getPageCount() . "\n";
echo "   Supported sizes: " . implode(', ', $editor->getSupportedSizes()) . "\n";

$editor->cropToSize('A5', 'all');

$a5Path = __DIR__ . '/../target/crop_to_a5.pdf';
$editor->applyToFile($a5Path);
echo "   Created: $a5Path (cropped to A5)\n\n";

// =========================================================================
// Step 4: Crop to Letter size
// =========================================================================

echo "4. Cropping to Letter size...\n";

$editor2 = new PdfPageBoxEditor();
$editor2->loadFile($sourcePath);
$editor2->cropToSize('LETTER', 'all');

$letterPath = __DIR__ . '/../target/crop_to_letter.pdf';
$editor2->applyToFile($letterPath);
echo "   Created: $letterPath (cropped to Letter)\n\n";

// =========================================================================
// Step 5: Crop specific pages
// =========================================================================

echo "5. Cropping specific pages only...\n";

$editor3 = new PdfPageBoxEditor();
$editor3->loadFile($sourcePath);
$editor3->cropToSize('A5', [1, 3]);  // Only crop pages 1 and 3

$specificPath = __DIR__ . '/../target/crop_specific_pages.pdf';
$editor3->applyToFile($specificPath);
echo "   Created: $specificPath (pages 1 and 3 cropped to A5)\n\n";

// =========================================================================
// Step 6: Custom crop dimensions
// =========================================================================

echo "6. Cropping to custom dimensions...\n";

$editor4 = new PdfPageBoxEditor();
$editor4->loadFile($sourcePath);

// Crop to 400x300 points from lower-left corner
$editor4->setMediaBox(0, 0, 400, 300, 'all');

$customPath = __DIR__ . '/../target/crop_custom_size.pdf';
$editor4->applyToFile($customPath);
echo "   Created: $customPath (400x300 points)\n\n";

// =========================================================================
// Step 7: Resize pages
// =========================================================================

echo "7. Resizing pages to A5...\n";

$editor5 = new PdfPageBoxEditor();
$editor5->loadFile($sourcePath);
$editor5->resizeTo('A5', 'all');

$resizePath = __DIR__ . '/../target/resize_to_a5.pdf';
$editor5->applyToFile($resizePath);
echo "   Created: $resizePath\n\n";

// =========================================================================
// Step 8: Resize to custom dimensions
// =========================================================================

echo "8. Resizing to custom dimensions (500x700 points)...\n";

$editor6 = new PdfPageBoxEditor();
$editor6->loadFile($sourcePath);
$editor6->resizeToCustom(500, 700, 'all');

$customResizePath = __DIR__ . '/../target/resize_custom.pdf';
$editor6->applyToFile($customResizePath);
echo "   Created: $customResizePath\n\n";

// =========================================================================
// Step 9: Add margins
// =========================================================================

echo "9. Adding uniform margin (50 points)...\n";

$editor7 = new PdfPageBoxEditor();
$editor7->loadFile($sourcePath);
$editor7->addMargin(50, 'all');

$marginPath = __DIR__ . '/../target/crop_with_margin.pdf';
$editor7->applyToFile($marginPath);
echo "   Created: $marginPath\n\n";

// =========================================================================
// Step 10: Add individual margins
// =========================================================================

echo "10. Adding individual margins (top:100, right:50, bottom:100, left:50)...\n";

$editor8 = new PdfPageBoxEditor();
$editor8->loadFile($sourcePath);
$editor8->addMargins(100, 50, 100, 50, 'all');

$marginsPath = __DIR__ . '/../target/crop_with_margins.pdf';
$editor8->applyToFile($marginsPath);
echo "   Created: $marginsPath\n\n";

// =========================================================================
// Step 11: Set CropBox (different from MediaBox)
// =========================================================================

echo "11. Setting CropBox (visible area) separately from MediaBox...\n";

$editor9 = new PdfPageBoxEditor();
$editor9->loadFile($sourcePath);

// Set a CropBox smaller than MediaBox - this defines the visible area
$editor9->setCropBox(50, 50, 500, 750, 'all');

$cropBoxPath = __DIR__ . '/../target/crop_box_set.pdf';
$editor9->applyToFile($cropBoxPath);
echo "   Created: $cropBoxPath (CropBox set to 450x700 with 50pt offset)\n\n";

// =========================================================================
// Step 12: Set all page boxes
// =========================================================================

echo "12. Setting multiple page boxes...\n";

$editor10 = new PdfPageBoxEditor();
$editor10->loadFile($sourcePath);

// MediaBox - full page area
$editor10->setMediaBox(0, 0, 612, 792, 'all');  // US Letter

// CropBox - visible printing area
$editor10->setCropBox(36, 36, 576, 756, 'all');

// BleedBox - area for printing bleeds
$editor10->setBleedBox(27, 27, 585, 765, 'all');

// TrimBox - final trimmed size
$editor10->setTrimBox(36, 36, 576, 756, 'all');

$allBoxesPath = __DIR__ . '/../target/crop_all_boxes.pdf';
$editor10->applyToFile($allBoxesPath);
echo "   Created: $allBoxesPath (all boxes configured)\n\n";

// =========================================================================
// Step 13: Different sizes for different pages
// =========================================================================

echo "13. Different sizes for different pages...\n";

$editor11 = new PdfPageBoxEditor();
$editor11->loadFile($sourcePath);

$editor11->resizeTo('A4', [1]);      // Page 1: A4
$editor11->resizeTo('A5', [2]);      // Page 2: A5
$editor11->resizeTo('LETTER', [3]);  // Page 3: Letter
$editor11->resizeTo('A6', [4]);      // Page 4: A6

$mixedPath = __DIR__ . '/../target/crop_mixed_sizes.pdf';
$editor11->applyToFile($mixedPath);
echo "   Page 1: A4, Page 2: A5, Page 3: Letter, Page 4: A6\n";
echo "   Created: $mixedPath\n\n";

// =========================================================================
// Step 14: Get current page boxes
// =========================================================================

echo "14. Getting current page boxes...\n";

$editor12 = new PdfPageBoxEditor();
$editor12->loadFile($sourcePath);

$mediaBox = $editor12->getMediaBox(1);
echo "   Page 1 MediaBox: [" . implode(', ', $mediaBox) . "]\n";

$boxes = $editor12->getAllBoxes(1);
echo "   Available boxes for page 1:\n";
foreach ($boxes as $name => $box) {
    if ($box !== null) {
        echo "   - $name: [" . implode(', ', $box) . "]\n";
    }
}
echo "\n";

// =========================================================================
// Step 15: Reset modifications
// =========================================================================

echo "15. Reset and modify again...\n";

$editor13 = new PdfPageBoxEditor();
$editor13->loadFile($sourcePath);

// Make some changes
$editor13->resizeTo('A5', 'all');

// Check pending changes
$pending = $editor13->getAllModifications();
echo "   Pending modifications: " . count($pending) . " pages\n";

// Reset all changes
$editor13->resetAllModifications();
$pending = $editor13->getAllModifications();
echo "   After reset: " . count($pending) . " pages\n\n";

// =========================================================================
// Step 16: Using convenience method from Tcpdf
// =========================================================================

echo "16. Using convenience method...\n";

$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$conveniencePath = __DIR__ . '/../target/crop_convenience.pdf';
$pdf2->cropPdfPages($sourcePath, 'A5', $conveniencePath);
echo "    Created: $conveniencePath\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- PdfPageBoxEditor class for page crop/resize operations\n";
echo "- Cropping to standard sizes (A4, A5, Letter, etc.)\n";
echo "- Cropping to custom dimensions\n";
echo "- Cropping specific pages only\n";
echo "- Resizing pages\n";
echo "- Adding uniform and individual margins\n";
echo "- Setting individual page boxes (MediaBox, CropBox, etc.)\n";
echo "- Different sizes for different pages\n";
echo "- Getting current page box values\n";
echo "- Resetting modifications\n\n";

echo "PdfPageBoxEditor Methods:\n";
echo "-------------------------\n";
echo "- loadFile(\$path): Load PDF from file\n";
echo "- loadContent(\$content): Load PDF from string\n";
echo "- getPageCount(): Get total pages\n";
echo "- getSupportedSizes(): Get list of standard sizes\n";
echo "- setMediaBox(\$llx, \$lly, \$urx, \$ury, \$pages): Set MediaBox\n";
echo "- setCropBox(\$llx, \$lly, \$urx, \$ury, \$pages): Set CropBox\n";
echo "- setBleedBox(\$llx, \$lly, \$urx, \$ury, \$pages): Set BleedBox\n";
echo "- setTrimBox(\$llx, \$lly, \$urx, \$ury, \$pages): Set TrimBox\n";
echo "- setArtBox(\$llx, \$lly, \$urx, \$ury, \$pages): Set ArtBox\n";
echo "- cropToSize(\$size, \$pages): Crop to standard size\n";
echo "- cropTo(\$width, \$height, \$pages, \$position): Crop to custom\n";
echo "- resizeTo(\$size, \$pages): Resize to standard size\n";
echo "- resizeToCustom(\$width, \$height, \$pages): Resize to custom\n";
echo "- addMargin(\$margin, \$pages): Add uniform margin\n";
echo "- addMargins(\$top, \$right, \$bottom, \$left, \$pages): Add margins\n";
echo "- getMediaBox(\$page): Get MediaBox for page\n";
echo "- getAllBoxes(\$page): Get all boxes for page\n";
echo "- getAllModifications(): Get pending modifications\n";
echo "- resetPageModifications(\$page): Reset page modifications\n";
echo "- resetAllModifications(): Reset all modifications\n";
echo "- apply(): Get modified PDF content\n";
echo "- applyToFile(\$path): Save modified PDF\n";
