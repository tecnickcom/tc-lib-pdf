<?php
/**
 * table.php
 *
 * Example demonstrating native table support.
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

use Com\Tecnick\Pdf\Table\Table;
use Com\Tecnick\Pdf\Table\TableCell;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "Native Table Example\n";
echo "====================\n\n";

// =========================================================================
// Example 1: Table Class API
// =========================================================================

echo "1. Table Class API...\n\n";

echo "   Creating a table:\n";
echo "   -----------------\n";
echo "   \$table = \$pdf->createTable([\n";
echo "       'borderWidth' => 0.3,\n";
echo "       'borderColor' => '#333333',\n";
echo "       'cellPadding' => 2,\n";
echo "       'headerBackgroundColor' => '#4a90d9',\n";
echo "       'headerTextColor' => '#ffffff',\n";
echo "   ]);\n\n";

echo "   Setting position and width:\n";
echo "   ---------------------------\n";
echo "   \$table->setPosition(10, 30);  // x, y in mm\n";
echo "   \$table->setWidth(190);        // width in mm\n\n";

echo "   Adding rows:\n";
echo "   ------------\n";
echo "   \$table->addHeaderRow(['Product', 'Quantity', 'Price']);\n";
echo "   \$table->addRow(['Widget A', '10', '\$5.00']);\n";
echo "   \$table->addRow(['Widget B', '25', '\$3.50']);\n\n";

echo "   Rendering:\n";
echo "   ----------\n";
echo "   \$table->render();\n\n";

// =========================================================================
// Example 2: TableCell Class API
// =========================================================================

echo "2. TableCell Class API...\n\n";

echo "   Creating a cell:\n";
echo "   -----------------\n";
echo "   \$cell = \$pdf->createTableCell(\n";
echo "       'Cell content',\n";
echo "       colspan: 2,       // span 2 columns\n";
echo "       rowspan: 1,       // span 1 row\n";
echo "       style: ['backgroundColor' => '#f0f0f0']\n";
echo "   );\n\n";

echo "   Cell styling:\n";
echo "   -------------\n";
echo "   \$cell->setHAlign('C');                    // L, C, R\n";
echo "   \$cell->setVAlign('M');                    // T, M, B\n";
echo "   \$cell->setBackgroundColor('#e8f5e9');    // hex color\n";
echo "   \$cell->setTextColor('#000000');          // hex color\n";
echo "   \$cell->setBorderWidth(0.5);              // in mm\n";
echo "   \$cell->setBorderColor('#666666');        // hex color\n\n";

// =========================================================================
// Example 3: Spanning Example
// =========================================================================

echo "3. Cell Spanning Example...\n\n";

echo "   Header spanning 4 columns:\n";
echo "   --------------------------\n";
echo "   \$headerCell = \$pdf->createTableCell('Sales Report', 4, 1);\n";
echo "   \$headerCell->setHAlign('C');\n";
echo "   \$table->addRow([\$headerCell], [], true);\n\n";

echo "   Creating footer with totals:\n";
echo "   ----------------------------\n";
echo "   \$totalCell = \$pdf->createTableCell('Total', 1, 1, [\n";
echo "       'backgroundColor' => '#e8f5e9'\n";
echo "   ]);\n";
echo "   \$sumCell = \$pdf->createTableCell('\$1,234.00', 3, 1, [\n";
echo "       'backgroundColor' => '#e8f5e9'\n";
echo "   ]);\n";
echo "   \$table->addRow([\$totalCell, \$sumCell]);\n\n";

// =========================================================================
// Example 4: Style Options
// =========================================================================

echo "4. Table Style Options...\n\n";

echo "   Available style options:\n";
echo "   ------------------------\n";
$styleOptions = [
    'borderWidth' => 'Border line width in mm (default: 0.2)',
    'borderColor' => 'Border color as hex string (default: #000000)',
    'cellPadding' => 'Cell padding in mm (default: 1)',
    'backgroundColor' => 'Cell background color (default: none)',
    'textColor' => 'Text color as hex string (default: #000000)',
    'headerBackgroundColor' => 'Header row background (default: #cccccc)',
    'headerTextColor' => 'Header text color (default: #000000)',
    'fontSize' => 'Font size in points (default: 10)',
    'fontFamily' => 'Font family name (default: helvetica)',
];

foreach ($styleOptions as $option => $desc) {
    printf("   %-25s  %s\n", $option, $desc);
}
echo "\n";

// =========================================================================
// Example 5: Alternating Row Colors
// =========================================================================

echo "5. Alternating Row Colors...\n\n";

echo "   \$data = [\n";
echo "       ['John', 'Engineering'],\n";
echo "       ['Jane', 'Marketing'],\n";
echo "       ['Bob', 'Sales'],\n";
echo "   ];\n";
echo "   \n";
echo "   foreach (\$data as \$i => \$row) {\n";
echo "       \$bgColor = (\$i % 2 === 0) ? '#ffffff' : '#f5f5f5';\n";
echo "       \$table->addRow(\$row, ['backgroundColor' => \$bgColor]);\n";
echo "   }\n\n";

// =========================================================================
// Example 6: Generate PDF with Tables
// =========================================================================

echo "6. Generating PDF with tables...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('Table Example');
$pdf->setTitle('Native Table Support');
$pdf->setKeywords('TCPDF, PDF, table, example');

// Enable default page content
$pdf->enableDefaultPageContent();

// Insert font
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);

// Add a page
$pdf->addPage();

// Add title
$pdf->page->addContent(
    "BT\n" .
    "/F1 14 Tf\n" .
    "1 0 0 1 28.35 800 Tm\n" .
    "(Native Table Support Example) Tj\n" .
    "ET\n"
);

// Create a simple table
$table = $pdf->createTable([
    'borderWidth' => 0.3,
    'borderColor' => '#333333',
    'cellPadding' => 2,
    'headerBackgroundColor' => '#4a90d9',
    'headerTextColor' => '#ffffff',
]);

$table->setPosition(10, 30);
$table->setWidth(190);

// Add header row
$table->addHeaderRow(['Product', 'Quantity', 'Unit Price', 'Total']);

// Add data rows
$table->addRow(['Widget A', '10', '$5.00', '$50.00']);
$table->addRow(['Widget B', '25', '$3.50', '$87.50']);
$table->addRow(['Widget C', '15', '$7.25', '$108.75']);
$table->addRow(['Widget D', '8', '$12.00', '$96.00']);

$table->render();

// Add second table with styled cells
$pdf->page->addContent(
    "BT\n" .
    "/F1 12 Tf\n" .
    "1 0 0 1 28.35 600 Tm\n" .
    "(Table with Cell Spanning) Tj\n" .
    "ET\n"
);

$table2 = $pdf->createTable([
    'borderWidth' => 0.3,
    'borderColor' => '#666666',
    'cellPadding' => 3,
]);

$table2->setPosition(10, 75);
$table2->setWidth(190);

// Header spanning all columns
$headerCell = $pdf->createTableCell('Sales Report - Q4 2024', 4, 1, [
    'backgroundColor' => '#2e7d32',
    'textColor' => '#ffffff',
]);
$headerCell->setHAlign('C');
$table2->addRow([$headerCell], [], true);

// Sub-header
$table2->addHeaderRow(['Region', 'October', 'November', 'December']);

// Data
$table2->addRow(['North', '$12,500', '$14,200', '$18,900']);
$table2->addRow(['South', '$9,800', '$11,500', '$15,200']);
$table2->addRow(['East', '$15,300', '$16,800', '$21,400']);
$table2->addRow(['West', '$11,200', '$13,100', '$17,600']);

// Footer row
$totalCell = $pdf->createTableCell('Total', 1, 1, ['backgroundColor' => '#e8f5e9']);
$sum1 = $pdf->createTableCell('$48,800', 1, 1, ['backgroundColor' => '#e8f5e9']);
$sum2 = $pdf->createTableCell('$55,600', 1, 1, ['backgroundColor' => '#e8f5e9']);
$sum3 = $pdf->createTableCell('$73,100', 1, 1, ['backgroundColor' => '#e8f5e9']);
$table2->addRow([$totalCell, $sum1, $sum2, $sum3]);

$table2->render();

// Output PDF
$pdfData = $pdf->getOutPDFString();

// Save to file
$outputPath = __DIR__ . '/../target/table_example.pdf';
@mkdir(dirname($outputPath), 0755, true);
file_put_contents($outputPath, $pdfData);

echo "   PDF saved to: $outputPath\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- Table class with configurable styles\n";
echo "- TableCell class for custom cell styling\n";
echo "- Header rows with distinct formatting\n";
echo "- Cell spanning (colspan)\n";
echo "- Custom colors and alignment\n";
echo "- Alternating row colors\n";
echo "- Auto column width calculation\n\n";

echo "Table Methods:\n";
echo "--------------\n";
echo "- createTable(\$style): Create new table\n";
echo "- setPosition(\$x, \$y): Set table position\n";
echo "- setWidth(\$w): Set table width\n";
echo "- addHeaderRow(\$cells): Add header row\n";
echo "- addRow(\$cells, \$style, \$isHeader): Add data row\n";
echo "- render(): Draw table on page\n";
