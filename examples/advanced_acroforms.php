<?php
/**
 * advanced_acroforms.php
 *
 * Example demonstrating advanced AcroForm features:
 * - Calculated fields (sum, product, average, custom)
 * - Field validation (required, email, number, range)
 * - Conditional visibility (show/hide based on field values)
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

// Import form classes
use Com\Tecnick\Pdf\Forms\FieldCalculation;
use Com\Tecnick\Pdf\Forms\FieldValidator;
use Com\Tecnick\Pdf\Forms\ConditionalVisibility;

echo "Advanced AcroForms Example\n";
echo "==========================\n\n";

// =========================================================================
// Example 1: Field Calculations
// =========================================================================

echo "1. Field Calculations\n";
echo "---------------------\n";

// Sum calculation
$sumCalc = FieldCalculation::sum('total', ['item1', 'item2', 'item3']);
echo "SUM calculation JavaScript:\n";
echo $sumCalc->toJavaScript() . "\n\n";

// Product calculation (e.g., quantity * price)
$productCalc = FieldCalculation::product('lineTotal', ['quantity', 'price']);
echo "PRODUCT calculation JavaScript:\n";
echo $productCalc->toJavaScript() . "\n\n";

// Average calculation
$avgCalc = FieldCalculation::average('avgScore', ['score1', 'score2', 'score3']);
echo "AVERAGE calculation JavaScript:\n";
echo $avgCalc->toJavaScript() . "\n\n";

// Min/Max calculations
$minCalc = FieldCalculation::min('lowestPrice', ['price1', 'price2', 'price3']);
$maxCalc = FieldCalculation::max('highestPrice', ['price1', 'price2', 'price3']);
echo "MIN calculation target: " . $minCalc->getTargetField() . "\n";
echo "MAX calculation target: " . $maxCalc->getTargetField() . "\n\n";

// Custom calculation with JavaScript expression
$customCalc = FieldCalculation::custom(
    'taxAmount',
    'subtotal * taxRate / 100',
    ['subtotal', 'taxRate']
);
echo "CUSTOM calculation JavaScript:\n";
echo $customCalc->toJavaScript() . "\n\n";

// =========================================================================
// Example 2: Field Validation
// =========================================================================

echo "2. Field Validation\n";
echo "-------------------\n";

// Required email validation
$emailValidator = FieldValidator::forField('email')
    ->required('Email address is required.')
    ->email('Please enter a valid email address.');
echo "EMAIL validation JavaScript:\n";
echo $emailValidator->toJavaScript() . "\n\n";

// Numeric range validation
$ageValidator = FieldValidator::forField('age')
    ->required('Age is required.')
    ->integer('Please enter a whole number.')
    ->range(18, 120, 'Age must be between 18 and 120.');
echo "AGE validation JavaScript:\n";
echo $ageValidator->toJavaScript() . "\n\n";

// Length validation
$usernameValidator = FieldValidator::forField('username')
    ->required()
    ->length(3, 20, 'Username must be 3-20 characters.')
    ->regex('^[a-zA-Z0-9_]+$', 'Only letters, numbers, and underscores allowed.');
echo "USERNAME validation rules: " . count($usernameValidator->getRules()) . " rules\n";
echo "Keystroke validation:\n" . $usernameValidator->toKeystrokeJavaScript() . "\n\n";

// Phone validation
$phoneValidator = FieldValidator::forField('phone')
    ->phone('Please enter a valid phone number.');
echo "PHONE validator field: " . $phoneValidator->getFieldName() . "\n\n";

// =========================================================================
// Example 3: Conditional Visibility
// =========================================================================

echo "3. Conditional Visibility\n";
echo "-------------------------\n";

// Show field when checkbox is checked
$checkboxVisibility = ConditionalVisibility::forFields('otherDetails')
    ->showWhenChecked('hasOther');
echo "CHECKBOX visibility JavaScript:\n";
echo $checkboxVisibility->toJavaScript() . "\n\n";

// Show multiple fields based on dropdown value
$dropdownVisibility = ConditionalVisibility::forFields(['address', 'city', 'zipcode'])
    ->showWhenEquals('contactMethod', 'mail');
echo "DROPDOWN visibility targets: " . implode(', ', $dropdownVisibility->getTargetFields()) . "\n";
echo "Source fields: " . implode(', ', $dropdownVisibility->getSourceFields()) . "\n\n";

// Complex condition with AND
$complexVisibility = ConditionalVisibility::forFields('premiumDiscount')
    ->showWhenGreaterThan('orderTotal', 100)
    ->andWhen('memberType', ConditionalVisibility::OP_EQUALS, 'premium');
echo "COMPLEX visibility JavaScript:\n";
echo $complexVisibility->toJavaScript() . "\n\n";

// Hide instead of show (inverse logic)
$inverseVisibility = ConditionalVisibility::forFields('guestMessage')
    ->showWhenNotEmpty('username')
    ->hideInsteadOfShow();
echo "INVERSE visibility (hide when username not empty)\n\n";

// =========================================================================
// Example 4: Integration with TCPDF
// =========================================================================

echo "4. Integration with TCPDF\n";
echo "-------------------------\n";

echo "Creating PDF with advanced form fields...\n\n";

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('Advanced AcroForms Example');
$pdf->setTitle('Advanced AcroForms');
$pdf->setKeywords('TCPDF, PDF, forms, calculations, validation, example');

// Enable default page content
$pdf->enableDefaultPageContent();

// Insert font
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Add a page
$pdf->addPage();

// Add title using PDF operators
$pdf->page->addContent(
    "BT\n" .
    "/F1 14 Tf\n" .
    "1 0 0 1 28.35 800 Tm\n" .
    "(Advanced AcroForms Example) Tj\n" .
    "ET\n"
);

// Section 1: Calculated Fields
$pdf->page->addContent(
    "BT\n/F1 12 Tf\n1 0 0 1 28.35 770 Tm\n(1. Calculated Fields) Tj\nET\n"
);

$pdf->page->addContent(
    "BT\n/F1 10 Tf\n1 0 0 1 42.52 750 Tm\n(Item 1:) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 10 Tf\n1 0 0 1 42.52 735 Tm\n(Item 2:) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 10 Tf\n1 0 0 1 42.52 720 Tm\n(Item 3:) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 10 Tf\n1 0 0 1 42.52 705 Tm\n(Total \\(auto-calculated\\):) Tj\nET\n"
);

// Section 2: Validated Fields
$pdf->page->addContent(
    "BT\n/F1 12 Tf\n1 0 0 1 28.35 675 Tm\n(2. Validated Fields) Tj\nET\n"
);

$pdf->page->addContent(
    "BT\n/F1 10 Tf\n1 0 0 1 42.52 655 Tm\n(Email \\(required, email format\\):) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 10 Tf\n1 0 0 1 42.52 640 Tm\n(Age \\(18-120, integer only\\):) Tj\nET\n"
);

// Section 3: Conditional Visibility
$pdf->page->addContent(
    "BT\n/F1 12 Tf\n1 0 0 1 28.35 610 Tm\n(3. Conditional Visibility) Tj\nET\n"
);

$pdf->page->addContent(
    "BT\n/F1 10 Tf\n1 0 0 1 42.52 590 Tm\n(Enable details checkbox:) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 10 Tf\n1 0 0 1 42.52 575 Tm\n(Details field \\(shows when checked\\):) Tj\nET\n"
);

// Usage instructions
$pdf->page->addContent(
    "BT\n/F1 12 Tf\n1 0 0 1 28.35 545 Tm\n(Instructions:) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 10 Tf\n1 0 0 1 42.52 525 Tm\n(- Open in Adobe Acrobat for full JavaScript support) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 10 Tf\n1 0 0 1 42.52 510 Tm\n(- Enter values in Item 1-3 to see total calculation) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 10 Tf\n1 0 0 1 42.52 495 Tm\n(- Try invalid email/age to see validation) Tj\nET\n"
);
$pdf->page->addContent(
    "BT\n/F1 10 Tf\n1 0 0 1 42.52 480 Tm\n(- Check the checkbox to show the details field) Tj\nET\n"
);

// Output PDF
$pdfData = $pdf->getOutPDFString();

// Save to file
$outputPath = __DIR__ . '/../target/advanced_acroforms.pdf';
file_put_contents($outputPath, $pdfData);

echo "PDF saved to: $outputPath\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "\n";
echo "FieldCalculation class:\n";
echo "  - FieldCalculation::sum() - Add values from multiple fields\n";
echo "  - FieldCalculation::product() - Multiply values (qty * price)\n";
echo "  - FieldCalculation::average() - Calculate mean of values\n";
echo "  - FieldCalculation::min() / max() - Find min/max values\n";
echo "  - FieldCalculation::custom() - Custom JavaScript expression\n";
echo "\n";
echo "FieldValidator class:\n";
echo "  - ->required() - Field must not be empty\n";
echo "  - ->email() - Valid email format\n";
echo "  - ->number() / ->integer() - Numeric validation\n";
echo "  - ->range(\$min, \$max) - Value within range\n";
echo "  - ->length(\$min, \$max) - String length validation\n";
echo "  - ->regex(\$pattern) - Custom pattern matching\n";
echo "  - ->custom(\$expression) - Custom JavaScript validation\n";
echo "\n";
echo "ConditionalVisibility class:\n";
echo "  - ->showWhenChecked() - Show when checkbox is checked\n";
echo "  - ->showWhenEquals() - Show when field equals value\n";
echo "  - ->showWhenNotEmpty() - Show when field has value\n";
echo "  - ->showWhenGreaterThan() - Show when value > threshold\n";
echo "  - ->andWhen() / ->orWhen() - Combine conditions\n";
echo "  - ->hideInsteadOfShow() - Inverse the logic\n";
echo "\n";
echo "TCPDF Integration:\n";
echo "  - \$pdf->createFieldCalculation() - Create calculation builder\n";
echo "  - \$pdf->createFieldValidator() - Create validator builder\n";
echo "  - \$pdf->createConditionalVisibility() - Create visibility rules\n";
echo "  - \$pdf->addCalculatedField() - Add field with calculation\n";
echo "  - \$pdf->addValidatedField() - Add field with validation\n";
echo "  - \$pdf->applyConditionalVisibility() - Apply visibility rules\n";
