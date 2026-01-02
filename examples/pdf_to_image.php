<?php
/**
 * pdf_to_image.php
 *
 * Example demonstrating PDF to image conversion.
 *
 * @category    Library
 * @package     Pdf
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.
// NOTE: This feature requires either Imagick extension or Ghostscript installed.

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');

use Com\Tecnick\Pdf\Manipulate\PdfToImage;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "PDF to Image Example\n";
echo "====================\n\n";

// =========================================================================
// Step 1: Create a sample PDF document
// =========================================================================

echo "1. Creating a sample PDF document...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf->setCreator('tc-lib-pdf');
$pdf->setTitle('Image Conversion Demo');
$pdf->enableDefaultPageContent();
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

for ($i = 1; $i <= 3; $i++) {
    $pdf->addPage();
    $pdf->page->addContent(
        "BT\n/F1 24 Tf\n1 0 0 1 200 750 Tm\n(Page {$i}) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 14 Tf\n1 0 0 1 150 700 Tm\n(PDF to Image Conversion Demo) Tj\nET\n"
    );
    // Add some graphics
    $pdf->page->addContent("q 0.5 0 0 rg 100 500 200 100 re f Q\n");  // Red box
    $pdf->page->addContent("q 0 0.5 0 rg 150 450 200 100 re f Q\n");  // Green box
    $pdf->page->addContent("q 0 0 0.5 rg 200 400 200 100 re f Q\n");  // Blue box
    $pdf->page->addContent(
        "BT\n/F1 12 Tf\n1 0 0 1 100 350 Tm\n(This page contains text and graphics.) Tj\nET\n"
    );
}

$sourcePath = __DIR__ . '/../target/image_source.pdf';
@mkdir(dirname($sourcePath), 0755, true);
file_put_contents($sourcePath, $pdf->getOutPDFString());
echo "   Created: $sourcePath (3 pages)\n\n";

// =========================================================================
// Step 2: Demonstrate PdfToImage API
// =========================================================================

echo "2. PdfToImage Class API...\n\n";

echo "   Creating a converter:\n";
echo "   ---------------------\n";
echo "   \$converter = new PdfToImage();\n";
echo "   // or via Tcpdf: \$converter = \$pdf->createImageConverter();\n\n";

echo "   Loading PDF:\n";
echo "   ------------\n";
echo "   \$converter->loadFile('document.pdf');\n";
echo "   \$converter->loadContent(\$pdfContent);\n\n";

echo "   Image settings:\n";
echo "   ---------------\n";
echo "   \$converter->setFormat('png');        // png, jpeg, webp, gif\n";
echo "   \$converter->setResolution(150);      // DPI (72-600)\n";
echo "   \$converter->setQuality(90);          // For JPEG/WebP (1-100)\n";
echo "   \$converter->setBackgroundColor('white');\n\n";

echo "   Converting:\n";
echo "   -----------\n";
echo "   \$imageData = \$converter->convertPage(1);\n";
echo "   \$allImages = \$converter->convertAllPages();\n";
echo "   \$converter->savePageToFile(1, 'page1.png');\n";
echo "   \$converter->saveAllPagesToDirectory('./images');\n\n";

// =========================================================================
// Step 3: Check available backends
// =========================================================================

echo "3. Checking available backends...\n";

$converter = new PdfToImage();

echo "   Imagick available: " . ($converter->hasImagick() ? 'Yes' : 'No') . "\n";
echo "   Ghostscript available: " . ($converter->hasGhostscript() ? 'Yes' : 'No') . "\n";

$backend = $converter->getAvailableBackend();
if ($backend === null) {
    echo "\n   WARNING: No conversion backend available!\n";
    echo "   Please install either:\n";
    echo "   - PHP Imagick extension (recommended)\n";
    echo "   - Ghostscript (gs command)\n\n";

    echo "Features Demonstrated:\n";
    echo "======================\n";
    echo "- PdfToImage class for PDF to image conversion\n";
    echo "- Backend detection (Imagick/Ghostscript)\n";
    echo "- Note: Actual conversion requires Imagick or Ghostscript\n\n";

    echo "To install Imagick:\n";
    echo "-------------------\n";
    echo "- Ubuntu/Debian: apt install php-imagick\n";
    echo "- macOS: brew install imagemagick && pecl install imagick\n";
    echo "- Windows: Download from PECL\n\n";

    echo "To install Ghostscript:\n";
    echo "-----------------------\n";
    echo "- Ubuntu/Debian: apt install ghostscript\n";
    echo "- macOS: brew install ghostscript\n";
    echo "- Windows: Download from https://ghostscript.com\n";

    exit(0);
}

echo "   Using backend: {$backend}\n";
echo "   Supported formats: " . implode(', ', $converter->getSupportedFormats()) . "\n\n";

// =========================================================================
// Step 4: Load PDF and get info
// =========================================================================

echo "4. Loading PDF and getting info...\n";

$converter->loadFile($sourcePath);
echo "   Page count: " . $converter->getPageCount() . "\n";

$dims = $converter->getPageDimensions(1);
if ($dims) {
    echo "   Page 1 dimensions at 150 DPI: {$dims['width']}x{$dims['height']} pixels\n";
}
echo "\n";

// =========================================================================
// Step 5: Convert single page to PNG
// =========================================================================

echo "5. Converting single page to PNG...\n";

$converter2 = new PdfToImage();
$converter2->loadFile($sourcePath)
           ->setFormat('png')
           ->setResolution(150);

$pngPath = __DIR__ . '/../target/page1.png';
$converter2->savePageToFile(1, $pngPath);
echo "   Created: $pngPath\n";
echo "   Size: " . number_format(filesize($pngPath)) . " bytes\n\n";

// =========================================================================
// Step 6: Convert to JPEG with quality setting
// =========================================================================

echo "6. Converting to JPEG with quality setting...\n";

$converter3 = new PdfToImage();
$converter3->loadFile($sourcePath)
           ->setFormat('jpeg')
           ->setResolution(150)
           ->setQuality(85);

$jpegPath = __DIR__ . '/../target/page1.jpg';
$converter3->savePageToFile(1, $jpegPath);
echo "   Created: $jpegPath (quality: 85)\n";
echo "   Size: " . number_format(filesize($jpegPath)) . " bytes\n\n";

// =========================================================================
// Step 7: Convert with different resolutions
// =========================================================================

echo "7. Converting with different resolutions...\n";

$resolutions = [72, 150, 300];
foreach ($resolutions as $dpi) {
    $conv = new PdfToImage();
    $conv->loadFile($sourcePath)
         ->setFormat('png')
         ->setResolution($dpi);

    $path = __DIR__ . "/../target/page1_{$dpi}dpi.png";
    $conv->savePageToFile(1, $path);

    $dims = $conv->getPageDimensions(1);
    $dimStr = $dims ? "{$dims['width']}x{$dims['height']}" : 'unknown';
    echo "   {$dpi} DPI: {$dimStr} pixels, " . number_format(filesize($path)) . " bytes\n";
}
echo "\n";

// =========================================================================
// Step 8: Convert all pages to directory
// =========================================================================

echo "8. Converting all pages to directory...\n";

$converter4 = new PdfToImage();
$converter4->loadFile($sourcePath)
           ->setFormat('png')
           ->setResolution(150);

$imageDir = __DIR__ . '/../target/pdf_images';
$files = $converter4->saveAllPagesToDirectory($imageDir, 'document');

echo "   Created " . count($files) . " images in: $imageDir\n";
foreach ($files as $pageNum => $filepath) {
    echo "   - Page {$pageNum}: " . basename($filepath) . "\n";
}
echo "\n";

// =========================================================================
// Step 9: Get image data without saving
// =========================================================================

echo "9. Getting image data as binary...\n";

$converter5 = new PdfToImage();
$converter5->loadFile($sourcePath)
           ->setFormat('png');

$imageData = $converter5->convertPage(1);
echo "   Page 1 image data: " . number_format(strlen($imageData)) . " bytes\n";

// Verify it's a valid PNG
$isPng = str_starts_with($imageData, "\x89PNG");
echo "   Valid PNG: " . ($isPng ? 'Yes' : 'No') . "\n\n";

// =========================================================================
// Step 10: Using convenience method from Tcpdf
// =========================================================================

echo "10. Using convenience method...\n";

$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$conveniencePath = __DIR__ . '/../target/convenience_page.png';
$pdf2->pdfPageToImage($sourcePath, 1, $conveniencePath, 'png', 150);
echo "    Created: $conveniencePath\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- PdfToImage class for PDF to image conversion\n";
echo "- Multiple format support (PNG, JPEG, WebP, GIF)\n";
echo "- Configurable resolution (DPI)\n";
echo "- Quality settings for JPEG/WebP\n";
echo "- Convert single page or all pages\n";
echo "- Get image data or save to files\n";
echo "- Backend detection (Imagick/Ghostscript)\n\n";

echo "PdfToImage Methods:\n";
echo "-------------------\n";
echo "- loadFile(\$path): Load PDF from file\n";
echo "- loadContent(\$content): Load PDF from string\n";
echo "- setFormat(\$format): Set output format\n";
echo "- setResolution(\$dpi): Set DPI (72-600)\n";
echo "- setQuality(\$quality): Set quality (1-100)\n";
echo "- setBackgroundColor(\$color): Set background\n";
echo "- setBackend(\$backend): Force specific backend\n";
echo "- getPageCount(): Get number of pages\n";
echo "- getSupportedFormats(): Get format list\n";
echo "- hasImagick(): Check Imagick availability\n";
echo "- hasGhostscript(): Check GS availability\n";
echo "- getAvailableBackend(): Get active backend\n";
echo "- convertPage(\$num): Get page as image data\n";
echo "- convertAllPages(): Get all pages as data\n";
echo "- savePageToFile(\$num, \$path): Save page to file\n";
echo "- saveAllPagesToDirectory(\$dir): Save all pages\n";
echo "- getPageDimensions(\$num): Get image dimensions\n\n";

echo "Supported Formats:\n";
echo "------------------\n";
echo "- png: Lossless, good for text/graphics\n";
echo "- jpeg: Lossy, smaller files for photos\n";
echo "- webp: Modern format, good compression\n";
echo "- gif: Limited colors, animations\n";
