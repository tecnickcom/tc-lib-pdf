<?php
/**
 * pdf_form_fill.php
 *
 * Example demonstrating PDF form field filling.
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

use Com\Tecnick\Pdf\Manipulate\PdfFormFiller;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "PDF Form Filler Example\n";
echo "=======================\n\n";

// =========================================================================
// Step 1: Create a sample PDF with form fields
// =========================================================================

echo "1. Creating a sample PDF with form fields...\n";

// We'll create a simple PDF with AcroForm fields manually
// In real usage, you'd typically load an existing form PDF

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf->setCreator('tc-lib-pdf');
$pdf->setTitle('Form Example');
$pdf->enableDefaultPageContent();
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

$pdf->addPage();
$pdf->page->addContent(
    "BT\n/F1 18 Tf\n1 0 0 1 28.35 800 Tm\n(Sample Form) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 11 Tf\n1 0 0 1 28.35 760 Tm\n(This PDF demonstrates form field handling.) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 11 Tf\n1 0 0 1 28.35 730 Tm\n(Name: ________________________) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 11 Tf\n1 0 0 1 28.35 700 Tm\n(Email: ________________________) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 11 Tf\n1 0 0 1 28.35 670 Tm\n(Notes: ________________________) Tj\nET\n"
);

$sourcePath = __DIR__ . '/../target/form_source.pdf';
@mkdir(dirname($sourcePath), 0755, true);
file_put_contents($sourcePath, $pdf->getOutPDFString());
echo "   Created: $sourcePath\n\n";

// =========================================================================
// Step 2: Demonstrate PdfFormFiller API
// =========================================================================

echo "2. PdfFormFiller Class API...\n\n";

echo "   Creating a form filler:\n";
echo "   -----------------------\n";
echo "   \$filler = new PdfFormFiller();\n";
echo "   // or via Tcpdf: \$filler = \$pdf->createFormFiller();\n\n";

echo "   Loading PDF:\n";
echo "   ------------\n";
echo "   \$filler->loadFile('form.pdf');\n";
echo "   \$filler->loadContent(\$pdfContent);\n\n";

echo "   Inspecting fields:\n";
echo "   ------------------\n";
echo "   \$fields = \$filler->getFormFields();   // Get all fields with details\n";
echo "   \$names = \$filler->getFieldNames();    // Get field names only\n";
echo "   \$count = \$filler->getFieldCount();    // Get number of fields\n";
echo "   \$exists = \$filler->hasField('name');  // Check if field exists\n";
echo "   \$value = \$filler->getFieldValue('name'); // Get current value\n";
echo "   \$type = \$filler->getFieldType('name');   // Get field type\n\n";

echo "   Setting field values:\n";
echo "   ---------------------\n";
echo "   \$filler->setFieldValue('name', 'John Doe');\n";
echo "   \$filler->setFieldValues(['name' => 'John', 'email' => 'john@test.com']);\n\n";

echo "   Flattening fields:\n";
echo "   ------------------\n";
echo "   \$filler->flattenFields(true);  // Make fields read-only\n\n";

echo "   Applying changes:\n";
echo "   -----------------\n";
echo "   \$modifiedPdf = \$filler->apply();\n";
echo "   \$filler->applyToFile('filled_form.pdf');\n\n";

// =========================================================================
// Step 3: Create a PDF with AcroForm for demonstration
// =========================================================================

echo "3. Creating a PDF with actual form fields...\n";

// Create a minimal PDF with form fields
$formPdf = createFormPdf();
$formSourcePath = __DIR__ . '/../target/form_with_fields.pdf';
file_put_contents($formSourcePath, $formPdf);
echo "   Created: $formSourcePath\n\n";

// =========================================================================
// Step 4: Load and inspect form fields
// =========================================================================

echo "4. Loading and inspecting form fields...\n";

$filler = new PdfFormFiller();
$filler->loadFile($formSourcePath);

echo "   Field count: " . $filler->getFieldCount() . "\n";
echo "   Field names: " . implode(', ', $filler->getFieldNames()) . "\n\n";

$fields = $filler->getFormFields();
echo "   Field details:\n";
foreach ($fields as $name => $field) {
    echo "   - {$name}:\n";
    echo "     Type: {$field['type']}\n";
    echo "     Value: '{$field['value']}'\n";
    $rectStr = '[' . implode(', ', array_map(fn($v) => round($v, 2), $field['rect'])) . ']';
    echo "     Rect: {$rectStr}\n";
}
echo "\n";

// =========================================================================
// Step 5: Fill form fields
// =========================================================================

echo "5. Filling form fields...\n";

$filler2 = new PdfFormFiller();
$filler2->loadFile($formSourcePath);

// Set individual field values
$filler2->setFieldValue('full_name', 'John Doe');
$filler2->setFieldValue('email_address', 'john.doe@example.com');
$filler2->setFieldValue('comments', 'This is a test comment.');

$filledPath = __DIR__ . '/../target/form_filled.pdf';
$filler2->applyToFile($filledPath);
echo "   Filled fields and saved to: $filledPath\n\n";

// =========================================================================
// Step 6: Fill using array of values
// =========================================================================

echo "6. Filling using array of values...\n";

$filler3 = new PdfFormFiller();
$filler3->loadFile($formSourcePath);

$filler3->setFieldValues([
    'full_name' => 'Jane Smith',
    'email_address' => 'jane.smith@example.com',
    'comments' => 'Filled using setFieldValues()',
]);

$arrayFilledPath = __DIR__ . '/../target/form_array_filled.pdf';
$filler3->applyToFile($arrayFilledPath);
echo "   Created: $arrayFilledPath\n\n";

// =========================================================================
// Step 7: Fill and flatten (make read-only)
// =========================================================================

echo "7. Filling and flattening fields...\n";

$filler4 = new PdfFormFiller();
$filler4->loadFile($formSourcePath);

$filler4->setFieldValues([
    'full_name' => 'Flattened Form',
    'email_address' => 'flattened@example.com',
    'comments' => 'This form is now read-only',
]);
$filler4->flattenFields(true);

$flattenedPath = __DIR__ . '/../target/form_flattened.pdf';
$filler4->applyToFile($flattenedPath);
echo "   Created: $flattenedPath (fields are read-only)\n\n";

// =========================================================================
// Step 8: Check field properties
// =========================================================================

echo "8. Checking field properties...\n";

$filler5 = new PdfFormFiller();
$filler5->loadFile($formSourcePath);

foreach ($filler5->getFieldNames() as $name) {
    echo "   Field: {$name}\n";
    echo "   - Type: " . $filler5->getFieldType($name) . "\n";
    echo "   - Required: " . ($filler5->isFieldRequired($name) ? 'Yes' : 'No') . "\n";
    echo "   - Read-only: " . ($filler5->isFieldReadOnly($name) ? 'Yes' : 'No') . "\n";
    $rect = $filler5->getFieldRect($name);
    if ($rect) {
        echo "   - Position: [" . implode(', ', array_map(fn($v) => round($v, 2), $rect)) . "]\n";
    }
}
echo "\n";

// =========================================================================
// Step 9: Using convenience method from Tcpdf
// =========================================================================

echo "9. Using convenience method...\n";

$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$conveniencePath = __DIR__ . '/../target/form_convenience.pdf';
$pdf2->fillPdfForm(
    $formSourcePath,
    [
        'full_name' => 'Convenience Method',
        'email_address' => 'convenience@example.com',
        'comments' => 'Filled via convenience method',
    ],
    $conveniencePath
);
echo "   Created: $conveniencePath\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- PdfFormFiller class for form field manipulation\n";
echo "- Loading PDFs with form fields\n";
echo "- Inspecting form field properties\n";
echo "- Setting individual field values\n";
echo "- Setting multiple values at once\n";
echo "- Flattening fields (making read-only)\n";
echo "- Checking field types and properties\n\n";

echo "PdfFormFiller Methods:\n";
echo "----------------------\n";
echo "- loadFile(\$path): Load PDF from file\n";
echo "- loadContent(\$content): Load PDF from string\n";
echo "- getFormFields(): Get all field details\n";
echo "- getFieldNames(): Get field names array\n";
echo "- getFieldCount(): Get number of fields\n";
echo "- hasField(\$name): Check if field exists\n";
echo "- getFieldValue(\$name): Get current field value\n";
echo "- getFieldType(\$name): Get field type\n";
echo "- getFieldOptions(\$name): Get choice field options\n";
echo "- isFieldRequired(\$name): Check if field is required\n";
echo "- isFieldReadOnly(\$name): Check if field is read-only\n";
echo "- getFieldRect(\$name): Get field position\n";
echo "- setFieldValue(\$name, \$value): Set single field\n";
echo "- setFieldValues(\$array): Set multiple fields\n";
echo "- flattenFields(\$bool): Enable/disable flattening\n";
echo "- clearFieldValues(): Clear pending changes\n";
echo "- apply(): Get modified PDF content\n";
echo "- applyToFile(\$path): Save modified PDF\n\n";

echo "Field Types:\n";
echo "------------\n";
echo "- text: Text input fields\n";
echo "- button: Buttons, checkboxes, radio buttons\n";
echo "- choice: Dropdowns, list boxes\n";
echo "- signature: Signature fields\n";

/**
 * Create a sample PDF with AcroForm fields
 *
 * @return string PDF content
 */
function createFormPdf(): string
{
    // Build a minimal PDF with form fields
    $objects = [];
    $objNum = 1;

    // Object 1: Catalog
    $catalogNum = $objNum++;
    $pagesNum = $objNum++;
    $acroFormNum = $objNum++;
    $pageNum = $objNum++;
    $contentsNum = $objNum++;
    $fontNum = $objNum++;
    $field1Num = $objNum++;
    $field2Num = $objNum++;
    $field3Num = $objNum++;

    $objects[$catalogNum] = "<<\n/Type /Catalog\n/Pages {$pagesNum} 0 R\n/AcroForm {$acroFormNum} 0 R\n>>";

    // Object 2: Pages
    $objects[$pagesNum] = "<<\n/Type /Pages\n/Kids [{$pageNum} 0 R]\n/Count 1\n>>";

    // Object 3: AcroForm
    $objects[$acroFormNum] = "<<\n/Fields [{$field1Num} 0 R {$field2Num} 0 R {$field3Num} 0 R]\n/NeedAppearances true\n/DR << /Font << /Helv {$fontNum} 0 R >> >>\n/DA (/Helv 10 Tf 0 g)\n>>";

    // Object 4: Page
    $objects[$pageNum] = "<<\n/Type /Page\n/Parent {$pagesNum} 0 R\n/MediaBox [0 0 612 792]\n/Contents {$contentsNum} 0 R\n/Resources << /Font << /F1 {$fontNum} 0 R >> >>\n/Annots [{$field1Num} 0 R {$field2Num} 0 R {$field3Num} 0 R]\n>>";

    // Object 5: Page contents
    $content = "BT\n/F1 18 Tf\n1 0 0 1 50 750 Tm\n(Sample Form) Tj\nET\n";
    $content .= "BT\n/F1 12 Tf\n1 0 0 1 50 700 Tm\n(Full Name:) Tj\nET\n";
    $content .= "BT\n/F1 12 Tf\n1 0 0 1 50 650 Tm\n(Email Address:) Tj\nET\n";
    $content .= "BT\n/F1 12 Tf\n1 0 0 1 50 600 Tm\n(Comments:) Tj\nET\n";
    $stream = gzcompress($content, 9);
    $streamLen = strlen($stream);
    $objects[$contentsNum] = "<<\n/Length {$streamLen}\n/Filter /FlateDecode\n>>\nstream\n{$stream}\nendstream";

    // Object 6: Font
    $objects[$fontNum] = "<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n/Encoding /WinAnsiEncoding\n>>";

    // Object 7: Field 1 - Full Name
    $objects[$field1Num] = "<<\n/Type /Annot\n/Subtype /Widget\n/FT /Tx\n/T (full_name)\n/V ()\n/Rect [150 695 400 720]\n/F 4\n/P {$pageNum} 0 R\n/DA (/Helv 10 Tf 0 g)\n>>";

    // Object 8: Field 2 - Email
    $objects[$field2Num] = "<<\n/Type /Annot\n/Subtype /Widget\n/FT /Tx\n/T (email_address)\n/V ()\n/Rect [150 645 400 670]\n/F 4\n/P {$pageNum} 0 R\n/DA (/Helv 10 Tf 0 g)\n>>";

    // Object 9: Field 3 - Comments
    $objects[$field3Num] = "<<\n/Type /Annot\n/Subtype /Widget\n/FT /Tx\n/T (comments)\n/V ()\n/Rect [150 570 400 620]\n/F 4\n/P {$pageNum} 0 R\n/DA (/Helv 10 Tf 0 g)\n/Ff 4096\n>>";  // Multiline

    // Build PDF
    $pdf = "%PDF-1.7\n";
    $pdf .= "%\xe2\xe3\xcf\xd3\n";

    $offsets = [];
    foreach ($objects as $num => $content) {
        $offsets[$num] = strlen($pdf);
        $pdf .= "{$num} 0 obj\n{$content}\nendobj\n";
    }

    // Cross-reference table
    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n";
    $pdf .= "0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }

    // Trailer
    $pdf .= "trailer\n";
    $pdf .= "<<\n/Size " . (count($objects) + 1) . "\n/Root {$catalogNum} 0 R\n>>\n";
    $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

    return $pdf;
}
