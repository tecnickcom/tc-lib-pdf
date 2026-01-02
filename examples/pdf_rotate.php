<?php
/**
 * pdf_rotate.php
 *
 * Example demonstrating PDF page rotation.
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

use Com\Tecnick\Pdf\Manipulate\PdfPageRotator;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "PDF Page Rotator Example\n";
echo "========================\n\n";

// =========================================================================
// Step 1: Create a sample multi-page PDF
// =========================================================================

echo "1. Creating a multi-page PDF document...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf->setCreator('tc-lib-pdf');
$pdf->setTitle('Page Rotation Demo');
$pdf->enableDefaultPageContent();
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Create 5 pages with orientation indicators
for ($i = 1; $i <= 5; $i++) {
    $pdf->addPage();
    $pdf->page->addContent(
        "BT\n/F1 24 Tf\n1 0 0 1 28.35 800 Tm\n(Page {$i}) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 14 Tf\n1 0 0 1 28.35 760 Tm\n(TOP) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 14 Tf\n1 0 0 1 28.35 50 Tm\n(BOTTOM) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 14 Tf\n1 0 0 1 500 400 Tm\n(RIGHT) Tj\nET\n"
    );
}

$sourcePath = __DIR__ . '/../target/rotate_source.pdf';
@mkdir(dirname($sourcePath), 0755, true);
file_put_contents($sourcePath, $pdf->getOutPDFString());
echo "   Created: $sourcePath (5 pages)\n\n";

// =========================================================================
// Step 2: Demonstrate PdfPageRotator API
// =========================================================================

echo "2. PdfPageRotator Class API...\n\n";

echo "   Creating a rotator:\n";
echo "   -------------------\n";
echo "   \$rotator = new PdfPageRotator();\n";
echo "   // or via Tcpdf: \$rotator = \$pdf->createPageRotator();\n\n";

echo "   Loading PDF:\n";
echo "   ------------\n";
echo "   \$rotator->loadFile('document.pdf');\n";
echo "   \$rotator->loadContent(\$pdfContent);\n\n";

echo "   Rotating pages:\n";
echo "   ---------------\n";
echo "   \$rotator->rotatePage(\$pageNum, \$degrees);\n";
echo "   \$rotator->rotatePages(\$degrees, \$pages);\n";
echo "   \$rotator->rotateClockwise(\$pages);\n";
echo "   \$rotator->rotateCounterClockwise(\$pages);\n";
echo "   \$rotator->rotateUpsideDown(\$pages);\n\n";

echo "   Applying changes:\n";
echo "   -----------------\n";
echo "   \$modifiedPdf = \$rotator->apply();\n";
echo "   \$rotator->applyToFile('output.pdf');\n\n";

// =========================================================================
// Step 3: Rotate a single page
// =========================================================================

echo "3. Rotating a single page...\n";

$rotator = new PdfPageRotator();
$rotator->loadFile($sourcePath);

echo "   Total pages: " . $rotator->getPageCount() . "\n";

// Rotate page 2 by 90 degrees
$rotator->rotatePage(2, 90);

$singlePath = __DIR__ . '/../target/rotate_single.pdf';
$rotator->applyToFile($singlePath);
echo "   Rotated page 2 by 90 degrees\n";
echo "   Created: $singlePath\n\n";

// =========================================================================
// Step 4: Rotate all pages
// =========================================================================

echo "4. Rotating all pages...\n";

$rotator2 = new PdfPageRotator();
$rotator2->loadFile($sourcePath);
$rotator2->rotatePages(90, 'all');

$allPath = __DIR__ . '/../target/rotate_all_90.pdf';
$rotator2->applyToFile($allPath);
echo "   Rotated all pages by 90 degrees\n";
echo "   Created: $allPath\n\n";

// =========================================================================
// Step 5: Rotate specific pages
// =========================================================================

echo "5. Rotating specific pages...\n";

$rotator3 = new PdfPageRotator();
$rotator3->loadFile($sourcePath);
$rotator3->rotatePages(180, [1, 3, 5]);

$specificPath = __DIR__ . '/../target/rotate_specific.pdf';
$rotator3->applyToFile($specificPath);
echo "   Rotated pages 1, 3, 5 by 180 degrees\n";
echo "   Created: $specificPath\n\n";

// =========================================================================
// Step 6: Clockwise rotation
// =========================================================================

echo "6. Clockwise rotation (90 degrees)...\n";

$rotator4 = new PdfPageRotator();
$rotator4->loadFile($sourcePath);
$rotator4->rotateClockwise();

$clockwisePath = __DIR__ . '/../target/rotate_clockwise.pdf';
$rotator4->applyToFile($clockwisePath);
echo "   Created: $clockwisePath\n\n";

// =========================================================================
// Step 7: Counter-clockwise rotation
// =========================================================================

echo "7. Counter-clockwise rotation (270 degrees)...\n";

$rotator5 = new PdfPageRotator();
$rotator5->loadFile($sourcePath);
$rotator5->rotateCounterClockwise();

$counterPath = __DIR__ . '/../target/rotate_counter_clockwise.pdf';
$rotator5->applyToFile($counterPath);
echo "   Created: $counterPath\n\n";

// =========================================================================
// Step 8: Upside down rotation
// =========================================================================

echo "8. Upside down rotation (180 degrees)...\n";

$rotator6 = new PdfPageRotator();
$rotator6->loadFile($sourcePath);
$rotator6->rotateUpsideDown();

$upsideDownPath = __DIR__ . '/../target/rotate_upside_down.pdf';
$rotator6->applyToFile($upsideDownPath);
echo "   Created: $upsideDownPath\n\n";

// =========================================================================
// Step 9: Rotate odd and even pages differently
// =========================================================================

echo "9. Rotating odd/even pages differently...\n";

$rotator7 = new PdfPageRotator();
$rotator7->loadFile($sourcePath);
$rotator7->rotateOddPages(90);
$rotator7->rotateEvenPages(270);

$oddEvenPath = __DIR__ . '/../target/rotate_odd_even.pdf';
$rotator7->applyToFile($oddEvenPath);
echo "   Odd pages: 90 degrees, Even pages: 270 degrees\n";
echo "   Created: $oddEvenPath\n\n";

// =========================================================================
// Step 10: Multiple rotations on same document
// =========================================================================

echo "10. Multiple rotations on same document...\n";

$rotator8 = new PdfPageRotator();
$rotator8->loadFile($sourcePath);
$rotator8->rotatePage(1, 0);    // No rotation
$rotator8->rotatePage(2, 90);   // 90 degrees
$rotator8->rotatePage(3, 180);  // 180 degrees
$rotator8->rotatePage(4, 270);  // 270 degrees
$rotator8->rotatePage(5, 90);   // 90 degrees

$multiplePath = __DIR__ . '/../target/rotate_multiple.pdf';
$rotator8->applyToFile($multiplePath);
echo "   Page 1: 0, Page 2: 90, Page 3: 180, Page 4: 270, Page 5: 90\n";
echo "   Created: $multiplePath\n\n";

// =========================================================================
// Step 11: Using convenience method
// =========================================================================

echo "11. Using convenience method...\n";

$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$conveniencePath = __DIR__ . '/../target/rotate_convenience.pdf';

$pdf2->rotatePdfPages($sourcePath, 90, $conveniencePath);
echo "    Created: $conveniencePath\n\n";

// =========================================================================
// Step 12: Check and reset rotations
// =========================================================================

echo "12. Checking and resetting rotations...\n";

$rotator9 = new PdfPageRotator();
$rotator9->loadFile($sourcePath);
$rotator9->rotatePages(90, 'all');

echo "   Rotations after initial set:\n";
$rotations = $rotator9->getAllRotations();
foreach ($rotations as $page => $degrees) {
    echo "   - Page {$page}: {$degrees} degrees\n";
}

$rotator9->resetPageRotation(3);
echo "\n   After resetting page 3:\n";
echo "   - Page 3: " . $rotator9->getPageRotation(3) . " degrees\n";

$rotator9->resetAllRotations();
echo "\n   After resetting all: " . count($rotator9->getAllRotations()) . " rotations\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- PdfPageRotator class for rotating PDF pages\n";
echo "- Rotating single pages\n";
echo "- Rotating all pages\n";
echo "- Rotating specific pages\n";
echo "- Clockwise and counter-clockwise shortcuts\n";
echo "- Upside down (180 degree) rotation\n";
echo "- Rotating odd/even pages separately\n";
echo "- Multiple different rotations\n";
echo "- Checking and resetting rotations\n\n";

echo "PdfPageRotator Methods:\n";
echo "-----------------------\n";
echo "- loadFile(\$path): Load PDF from file\n";
echo "- loadContent(\$content): Load PDF from string\n";
echo "- getPageCount(): Get total pages\n";
echo "- rotatePage(\$page, \$degrees): Rotate single page\n";
echo "- rotatePages(\$degrees, \$pages): Rotate multiple pages\n";
echo "- rotateClockwise(\$pages): Rotate 90 degrees\n";
echo "- rotateCounterClockwise(\$pages): Rotate 270 degrees\n";
echo "- rotateUpsideDown(\$pages): Rotate 180 degrees\n";
echo "- rotateOddPages(\$degrees): Rotate odd pages\n";
echo "- rotateEvenPages(\$degrees): Rotate even pages\n";
echo "- getPageRotation(\$page): Get rotation for page\n";
echo "- getAllRotations(): Get all rotations\n";
echo "- resetPageRotation(\$page): Reset page to 0\n";
echo "- resetAllRotations(): Reset all rotations\n";
echo "- apply(): Get modified PDF content\n";
echo "- applyToFile(\$path): Save modified PDF\n";
