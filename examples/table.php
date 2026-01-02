<?php
/**
 * table.php
 *
 * Example demonstrating native table support.
 *
 * @since       2025-01-02
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('Table Example');
$pdf->setTitle('Native Table Support');
$pdf->setKeywords('TCPDF, PDF, table, example');

// Add a page
$pdf->page->add();
$pdf->setDefaultCellPadding(0, 0, 0, 0);
$pdf->setDefaultCellMargin(0, 0, 0, 0);

// Set font
$pdf->font->add($pdf->pon, 'helvetica', 'BI');
$pdf->page->addContent($pdf->font->getOutCurrentFont());

// Title
$pdf->addTextCell('Native Table Example', -1, 10, 10, 0, 0, 0, 0, 'T', 'L');

// Set regular font
$pdf->font->add($pdf->pon, 'helvetica', '');
$pdf->page->addContent($pdf->font->getOutCurrentFont());

// =========================================================================
// Example 1: Simple table with header
// =========================================================================

$pdf->addTextCell('Example 1: Simple Table with Header', -1, 10, 25, 0, 0, 0, 0, 'T', 'L');

$table = $pdf->createTable([
    'borderWidth' => 0.3,
    'borderColor' => '#333333',
    'cellPadding' => 2,
    'headerBackgroundColor' => '#4a90d9',
    'headerTextColor' => '#ffffff',
]);

$table->setPosition(10, 32);
$table->setWidth(190);

// Add header row
$table->addHeaderRow(['Product', 'Quantity', 'Unit Price', 'Total']);

// Add data rows
$table->addRow(['Widget A', '10', '$5.00', '$50.00']);
$table->addRow(['Widget B', '25', '$3.50', '$87.50']);
$table->addRow(['Widget C', '15', '$7.25', '$108.75']);
$table->addRow(['Widget D', '8', '$12.00', '$96.00']);

$table->render();

// =========================================================================
// Example 2: Table with cell spanning
// =========================================================================

$pdf->addTextCell('Example 2: Table with Cell Spanning', -1, 10, 75, 0, 0, 0, 0, 'T', 'L');

$table2 = $pdf->createTable([
    'borderWidth' => 0.3,
    'borderColor' => '#666666',
    'cellPadding' => 3,
    'headerBackgroundColor' => '#2e7d32',
    'headerTextColor' => '#ffffff',
]);

$table2->setPosition(10, 82);
$table2->setWidth(190);

// Header spanning 4 columns
$headerCell = $pdf->createTableCell('Sales Report - Q4 2024', 4, 1, [
    'backgroundColor' => '#2e7d32',
    'textColor' => '#ffffff',
]);
$headerCell->setHAlign('C');
$table2->addRow([$headerCell], [], true);

// Subheader
$table2->addHeaderRow(['Region', 'October', 'November', 'December']);

// Data rows
$table2->addRow(['North', '$12,500', '$14,200', '$18,900']);
$table2->addRow(['South', '$9,800', '$11,500', '$15,200']);
$table2->addRow(['East', '$15,300', '$16,800', '$21,400']);
$table2->addRow(['West', '$11,200', '$13,100', '$17,600']);

// Footer row with colspan
$totalCell = $pdf->createTableCell('Total', 1, 1, [
    'backgroundColor' => '#e8f5e9',
]);
$sumCell = $pdf->createTableCell('$48,800', 1, 1, [
    'backgroundColor' => '#e8f5e9',
]);
$sumCell2 = $pdf->createTableCell('$55,600', 1, 1, [
    'backgroundColor' => '#e8f5e9',
]);
$sumCell3 = $pdf->createTableCell('$73,100', 1, 1, [
    'backgroundColor' => '#e8f5e9',
]);
$table2->addRow([$totalCell, $sumCell, $sumCell2, $sumCell3]);

$table2->render();

// =========================================================================
// Example 3: Styled table with alternating colors
// =========================================================================

$pdf->addTextCell('Example 3: Styled Table with Custom Colors', -1, 10, 140, 0, 0, 0, 0, 'T', 'L');

$table3 = $pdf->createTable([
    'borderWidth' => 0.2,
    'borderColor' => '#cccccc',
    'cellPadding' => 2.5,
    'headerBackgroundColor' => '#1565c0',
    'headerTextColor' => '#ffffff',
]);

$table3->setPosition(10, 147);
$table3->setWidth(190);

$table3->addHeaderRow(['ID', 'Name', 'Department', 'Status']);

// Alternating row colors
$data = [
    ['001', 'John Smith', 'Engineering', 'Active'],
    ['002', 'Jane Doe', 'Marketing', 'Active'],
    ['003', 'Bob Johnson', 'Sales', 'On Leave'],
    ['004', 'Alice Williams', 'HR', 'Active'],
    ['005', 'Charlie Brown', 'Finance', 'Active'],
];

$rowIndex = 0;
foreach ($data as $row) {
    $bgColor = ($rowIndex % 2 === 0) ? '#ffffff' : '#f5f5f5';
    $style = ['backgroundColor' => $bgColor];
    $table3->addRow($row, $style);
    $rowIndex++;
}

$table3->render();

// =========================================================================
// Example 4: Table with different alignments
// =========================================================================

$pdf->addTextCell('Example 4: Table with Different Alignments', -1, 10, 195, 0, 0, 0, 0, 'T', 'L');

$table4 = $pdf->createTable([
    'borderWidth' => 0.3,
    'borderColor' => '#000000',
    'cellPadding' => 3,
    'headerBackgroundColor' => '#ff5722',
    'headerTextColor' => '#ffffff',
]);

$table4->setPosition(10, 202);
$table4->setWidth(190);

$table4->addHeaderRow(['Left Aligned', 'Center Aligned', 'Right Aligned']);

// Create cells with different alignments
$leftCell = $pdf->createTableCell('Text on left');
$leftCell->setHAlign('L');

$centerCell = $pdf->createTableCell('Text centered');
$centerCell->setHAlign('C');

$rightCell = $pdf->createTableCell('Text on right');
$rightCell->setHAlign('R');

$table4->addRow([$leftCell, $centerCell, $rightCell]);

$leftCell2 = $pdf->createTableCell('Another left');
$leftCell2->setHAlign('L');

$centerCell2 = $pdf->createTableCell('Another center');
$centerCell2->setHAlign('C');

$rightCell2 = $pdf->createTableCell('Another right');
$rightCell2->setHAlign('R');

$table4->addRow([$leftCell2, $centerCell2, $rightCell2]);

$table4->render();

// =========================================================================
// Example 5: Table on new page with auto page break
// =========================================================================

$pdf->page->add();
$pdf->page->addContent($pdf->font->getOutCurrentFont());

$pdf->addTextCell('Example 5: Large Table with Auto Page Break', -1, 10, 10, 0, 0, 0, 0, 'T', 'L');

$table5 = $pdf->createTable([
    'borderWidth' => 0.2,
    'borderColor' => '#888888',
    'cellPadding' => 2,
    'headerBackgroundColor' => '#673ab7',
    'headerTextColor' => '#ffffff',
]);

$table5->setPosition(10, 17);
$table5->setWidth(190);

$table5->addHeaderRow(['#', 'Item Description', 'Category', 'Price']);

// Generate many rows to trigger page break
for ($i = 1; $i <= 50; $i++) {
    $category = ['Electronics', 'Clothing', 'Food', 'Books', 'Home'][$i % 5];
    $price = number_format(rand(1000, 50000) / 100, 2);
    $table5->addRow([(string)$i, "Sample Item #$i with description", $category, "\$$price"]);
}

$table5->render();

// Output PDF
$pdfData = $pdf->getOutPDFString();

// Save to file
$outputPath = __DIR__ . '/../target/table_example.pdf';
file_put_contents($outputPath, $pdfData);

echo "Table example PDF saved to: $outputPath\n";
echo "\nFeatures demonstrated:\n";
echo "1. Simple table with header row\n";
echo "2. Cell spanning (colspan)\n";
echo "3. Custom cell colors and alternating rows\n";
echo "4. Text alignment (left, center, right)\n";
echo "5. Large table with automatic page breaks\n";
