<?php
/**
 * pdf_metadata.php
 *
 * Example demonstrating PDF metadata editing functionality.
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

use Com\Tecnick\Pdf\Manipulate\PdfMetadataEditor;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "PDF Metadata Editor Example\n";
echo "===========================\n\n";

// =========================================================================
// Step 1: Create a sample PDF to edit
// =========================================================================

echo "1. Creating a sample PDF document...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf->setCreator('tc-lib-pdf');
$pdf->setTitle('Original Title');
$pdf->setAuthor('Original Author');
$pdf->setSubject('Original Subject');
$pdf->setKeywords('original, keywords');
$pdf->enableDefaultPageContent();
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

$pdf->addPage();
$pdf->page->addContent(
    "BT\n/F1 18 Tf\n1 0 0 1 28.35 800 Tm\n(Sample PDF Document) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 11 Tf\n1 0 0 1 28.35 770 Tm\n(This document will have its metadata edited.) Tj\nET\n"
);

$sourcePath = __DIR__ . '/../target/metadata_source.pdf';
@mkdir(dirname($sourcePath), 0755, true);
file_put_contents($sourcePath, $pdf->getOutPDFString());
echo "   Created: $sourcePath\n\n";

// =========================================================================
// Step 2: Demonstrate PdfMetadataEditor API
// =========================================================================

echo "2. PdfMetadataEditor Class API...\n\n";

echo "   Creating an editor:\n";
echo "   -------------------\n";
echo "   \$editor = new PdfMetadataEditor();\n";
echo "   // or via Tcpdf: \$editor = \$pdf->createMetadataEditor();\n\n";

echo "   Loading PDF:\n";
echo "   ------------\n";
echo "   \$editor->loadFile('document.pdf');\n";
echo "   \$editor->loadContent(\$pdfContent);\n\n";

echo "   Setting metadata:\n";
echo "   -----------------\n";
echo "   \$editor->setTitle('New Title');\n";
echo "   \$editor->setAuthor('New Author');\n";
echo "   \$editor->setSubject('New Subject');\n";
echo "   \$editor->setKeywords('new, keywords');\n";
echo "   \$editor->setCreator('My Application');\n";
echo "   \$editor->setProducer('tc-lib-pdf');\n\n";

echo "   Getting metadata:\n";
echo "   -----------------\n";
echo "   \$metadata = \$editor->getMetadata();\n";
echo "   \$title = \$editor->getField('Title');\n\n";

echo "   Applying changes:\n";
echo "   -----------------\n";
echo "   \$modifiedPdf = \$editor->apply();\n";
echo "   \$editor->applyToFile('output.pdf');\n\n";

// =========================================================================
// Step 3: Read existing metadata
// =========================================================================

echo "3. Reading existing metadata...\n";

$editor = new PdfMetadataEditor();
$editor->loadFile($sourcePath);

echo "   Original metadata:\n";
$originalMetadata = $editor->getMetadata();
foreach ($originalMetadata as $field => $value) {
    echo "   - {$field}: {$value}\n";
}
echo "\n";

// =========================================================================
// Step 4: Edit metadata
// =========================================================================

echo "4. Editing metadata...\n";

$editor->setTitle('Edited PDF Document')
       ->setAuthor('John Doe')
       ->setSubject('This PDF has been modified')
       ->setKeywords('edited, modified, metadata, tc-lib-pdf')
       ->setCreator('PDF Metadata Editor Example')
       ->setProducer('tc-lib-pdf Library')
       ->setModDate();

echo "   New metadata:\n";
$newMetadata = $editor->getMetadata();
foreach ($newMetadata as $field => $value) {
    echo "   - {$field}: {$value}\n";
}
echo "\n";

// =========================================================================
// Step 5: Save modified PDF
// =========================================================================

echo "5. Saving modified PDF...\n";

$editedPath = __DIR__ . '/../target/metadata_edited.pdf';
$editor->applyToFile($editedPath);
echo "   Created: $editedPath\n\n";

// =========================================================================
// Step 6: Verify edited metadata
// =========================================================================

echo "6. Verifying edited metadata...\n";

$verifyEditor = new PdfMetadataEditor();
$verifyEditor->loadFile($editedPath);

echo "   Metadata in edited file:\n";
$verifiedMetadata = $verifyEditor->getMetadata();
foreach ($verifiedMetadata as $field => $value) {
    echo "   - {$field}: {$value}\n";
}
echo "\n";

// =========================================================================
// Step 7: Using setMetadata for bulk update
// =========================================================================

echo "7. Bulk metadata update...\n";

$bulkEditor = new PdfMetadataEditor();
$bulkEditor->loadFile($sourcePath);
$bulkEditor->setMetadata([
    'Title' => 'Bulk Updated Title',
    'Author' => 'Bulk Author',
    'Subject' => 'Bulk Subject Update',
    'Keywords' => 'bulk, update, metadata',
]);

$bulkPath = __DIR__ . '/../target/metadata_bulk.pdf';
$bulkEditor->applyToFile($bulkPath);
echo "   Created: $bulkPath\n\n";

// =========================================================================
// Step 8: Using convenience method
// =========================================================================

echo "8. Using convenience method...\n";

$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$conveniencePath = __DIR__ . '/../target/metadata_convenience.pdf';

$pdf2->editPdfMetadata($sourcePath, [
    'Title' => 'Convenience Method Title',
    'Author' => 'Convenience Author',
    'Subject' => 'Created via convenience method',
], $conveniencePath);

echo "   Created: $conveniencePath\n\n";

// =========================================================================
// Step 9: Remove and clear metadata
// =========================================================================

echo "9. Removing metadata fields...\n";

$removeEditor = new PdfMetadataEditor();
$removeEditor->loadFile($sourcePath);

echo "   Before removal: " . count($removeEditor->getMetadata()) . " fields\n";

$removeEditor->removeField('Keywords');
echo "   After removing Keywords: " . count($removeEditor->getMetadata()) . " fields\n";

$removeEditor->clearMetadata();
$removeEditor->setTitle('Minimal Metadata Document');

$minimalPath = __DIR__ . '/../target/metadata_minimal.pdf';
$removeEditor->applyToFile($minimalPath);
echo "   Created: $minimalPath (with minimal metadata)\n\n";

// =========================================================================
// Step 10: Setting dates
// =========================================================================

echo "10. Setting creation and modification dates...\n";

$dateEditor = new PdfMetadataEditor();
$dateEditor->loadFile($sourcePath);

// Set creation date to a specific date
$creationDate = new DateTime('2024-01-15 10:30:00');
$dateEditor->setCreationDate($creationDate);

// Set modification date to now
$dateEditor->setModDate();

$dateEditor->setTitle('Document with Custom Dates');

$datePath = __DIR__ . '/../target/metadata_dates.pdf';
$dateEditor->applyToFile($datePath);
echo "   Created: $datePath\n";
echo "   CreationDate: " . $dateEditor->getField('CreationDate') . "\n";
echo "   ModDate: " . $dateEditor->getField('ModDate') . "\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- PdfMetadataEditor class for editing PDF document info\n";
echo "- Loading PDF from file or content\n";
echo "- Reading existing metadata\n";
echo "- Setting individual metadata fields\n";
echo "- Bulk metadata updates\n";
echo "- Removing specific fields\n";
echo "- Clearing all metadata\n";
echo "- Setting creation and modification dates\n\n";

echo "PdfMetadataEditor Methods:\n";
echo "--------------------------\n";
echo "- loadFile(\$path): Load PDF from file\n";
echo "- loadContent(\$content): Load PDF from string\n";
echo "- setTitle(\$title): Set document title\n";
echo "- setAuthor(\$author): Set document author\n";
echo "- setSubject(\$subject): Set document subject\n";
echo "- setKeywords(\$keywords): Set document keywords\n";
echo "- setCreator(\$creator): Set creator application\n";
echo "- setProducer(\$producer): Set producer application\n";
echo "- setCreationDate(\$date): Set creation date\n";
echo "- setModDate(\$date): Set modification date\n";
echo "- setMetadata(\$array): Bulk set metadata\n";
echo "- getMetadata(): Get all metadata\n";
echo "- getField(\$name): Get specific field\n";
echo "- removeField(\$name): Remove a field\n";
echo "- clearMetadata(): Clear all metadata\n";
echo "- apply(): Get modified PDF content\n";
echo "- applyToFile(\$path): Save modified PDF\n";
