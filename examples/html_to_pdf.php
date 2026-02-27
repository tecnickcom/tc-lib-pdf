<?php
/**
 * html_to_pdf.php
 *
 * Example demonstrating HTML to PDF conversion.
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

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "HTML to PDF Example\n";
echo "===================\n\n";

// =========================================================================
// Step 1: Basic HTML rendering
// =========================================================================

echo "1. Basic HTML rendering...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf->setCreator('tc-lib-pdf');
$pdf->setTitle('HTML to PDF Demo');
$pdf->enableDefaultPageContent();
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

$pdf->addPage();

$html = '
<h1>Welcome to HTML to PDF</h1>
<p>This is a demonstration of the HTML to PDF conversion feature.</p>
<p>The library supports various HTML elements and CSS styling.</p>
';

$pdf->writeHTML($html);

$basicPath = __DIR__ . '/../target/html_basic.pdf';
@mkdir(dirname($basicPath), 0755, true);
file_put_contents($basicPath, $pdf->getOutPDFString());
echo "   Created: $basicPath\n\n";

// =========================================================================
// Step 2: Headings and text formatting
// =========================================================================

echo "2. Headings and text formatting...\n";

$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf2->setCreator('tc-lib-pdf');
$pdf2->setTitle('HTML Formatting Demo');
$pdf2->enableDefaultPageContent();
$pdf2->font->insert($pdf2->pon, 'helvetica', '', 12);

$pdf2->addPage();

$html2 = '
<h1>Heading Level 1</h1>
<h2>Heading Level 2</h2>
<h3>Heading Level 3</h3>
<h4>Heading Level 4</h4>
<h5>Heading Level 5</h5>
<h6>Heading Level 6</h6>

<p>This is a paragraph with <b>bold text</b> and <i>italic text</i>.</p>
<p>You can also combine <b><i>bold and italic</i></b> together.</p>
<p>Links appear in <a href="#">blue color</a> by default.</p>
';

$pdf2->writeHTML($html2);

$headingsPath = __DIR__ . '/../target/html_headings.pdf';
file_put_contents($headingsPath, $pdf2->getOutPDFString());
echo "   Created: $headingsPath\n\n";

// =========================================================================
// Step 3: Lists
// =========================================================================

echo "3. Lists (ordered and unordered)...\n";

$pdf3 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf3->setCreator('tc-lib-pdf');
$pdf3->setTitle('HTML Lists Demo');
$pdf3->enableDefaultPageContent();
$pdf3->font->insert($pdf3->pon, 'helvetica', '', 12);

$pdf3->addPage();

$html3 = '
<h2>Unordered List</h2>
<ul>
    <li>First item</li>
    <li>Second item</li>
    <li>Third item</li>
</ul>

<h2>Ordered List</h2>
<ol>
    <li>First step</li>
    <li>Second step</li>
    <li>Third step</li>
</ol>

<h2>Nested Lists</h2>
<ul>
    <li>Parent item 1
        <ul>
            <li>Child item 1.1</li>
            <li>Child item 1.2</li>
        </ul>
    </li>
    <li>Parent item 2</li>
</ul>
';

$pdf3->writeHTML($html3);

$listsPath = __DIR__ . '/../target/html_lists.pdf';
file_put_contents($listsPath, $pdf3->getOutPDFString());
echo "   Created: $listsPath\n\n";

// =========================================================================
// Step 4: Tables
// =========================================================================

echo "4. Tables...\n";

$pdf4 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf4->setCreator('tc-lib-pdf');
$pdf4->setTitle('HTML Tables Demo');
$pdf4->enableDefaultPageContent();
$pdf4->font->insert($pdf4->pon, 'helvetica', '', 12);

$pdf4->addPage();

$html4 = '
<h2>Simple Table</h2>
<table>
    <tr>
        <th>Name</th>
        <th>Age</th>
        <th>City</th>
    </tr>
    <tr>
        <td>John Doe</td>
        <td>30</td>
        <td>New York</td>
    </tr>
    <tr>
        <td>Jane Smith</td>
        <td>25</td>
        <td>Los Angeles</td>
    </tr>
    <tr>
        <td>Bob Johnson</td>
        <td>35</td>
        <td>Chicago</td>
    </tr>
</table>
';

$pdf4->writeHTML($html4);

$tablesPath = __DIR__ . '/../target/html_tables.pdf';
file_put_contents($tablesPath, $pdf4->getOutPDFString());
echo "   Created: $tablesPath\n\n";

// =========================================================================
// Step 5: CSS styling
// =========================================================================

echo "5. CSS inline styling...\n";

$pdf5 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf5->setCreator('tc-lib-pdf');
$pdf5->setTitle('CSS Styling Demo');
$pdf5->enableDefaultPageContent();
$pdf5->font->insert($pdf5->pon, 'helvetica', '', 12);

$pdf5->addPage();

$html5 = '
<h2 style="color: #0066cc;">Styled Heading</h2>

<p style="color: red;">This paragraph is in red color.</p>

<p style="color: #00aa00; font-size: 14pt;">This paragraph is green and larger.</p>

<p style="text-align: center;">This paragraph is centered.</p>

<p style="text-align: right;">This paragraph is right-aligned.</p>

<div style="color: navy; font-style: italic;">
    This is a styled div with navy italic text.
</div>
';

$pdf5->writeHTML($html5);

$cssPath = __DIR__ . '/../target/html_css_styling.pdf';
file_put_contents($cssPath, $pdf5->getOutPDFString());
echo "   Created: $cssPath\n\n";

// =========================================================================
// Step 6: Blockquotes and preformatted text
// =========================================================================

echo "6. Blockquotes and preformatted text...\n";

$pdf6 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf6->setCreator('tc-lib-pdf');
$pdf6->setTitle('Blockquotes Demo');
$pdf6->enableDefaultPageContent();
$pdf6->font->insert($pdf6->pon, 'helvetica', '', 12);

$pdf6->addPage();

$html6 = '
<h2>Blockquote</h2>
<blockquote>
    This is a blockquote. It is indented from both sides
    and typically styled differently from regular paragraphs.
</blockquote>

<h2>Preformatted Code</h2>
<pre>
function hello() {
    echo "Hello, World!";
}
</pre>

<p>Inline code: <code>$variable = "value";</code></p>
';

$pdf6->writeHTML($html6);

$blockquotePath = __DIR__ . '/../target/html_blockquote.pdf';
file_put_contents($blockquotePath, $pdf6->getOutPDFString());
echo "   Created: $blockquotePath\n\n";

// =========================================================================
// Step 7: Complete document example
// =========================================================================

echo "7. Complete document example...\n";

$pdf7 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf7->setCreator('tc-lib-pdf');
$pdf7->setTitle('Complete HTML Document');
$pdf7->enableDefaultPageContent();
$pdf7->font->insert($pdf7->pon, 'helvetica', '', 12);

$pdf7->addPage();

$html7 = '
<h1 style="color: #333366; text-align: center;">Product Catalog</h1>

<h2>Introduction</h2>
<p>Welcome to our product catalog. Below you will find a list of our
featured products with detailed information.</p>

<hr/>

<h2>Featured Products</h2>
<table>
    <tr>
        <th>Product</th>
        <th>Category</th>
        <th>Price</th>
        <th>In Stock</th>
    </tr>
    <tr>
        <td>Widget Pro</td>
        <td>Electronics</td>
        <td>$99.99</td>
        <td>Yes</td>
    </tr>
    <tr>
        <td>Gadget X</td>
        <td>Electronics</td>
        <td>$149.99</td>
        <td>Yes</td>
    </tr>
    <tr>
        <td>Tool Master</td>
        <td>Tools</td>
        <td>$49.99</td>
        <td>No</td>
    </tr>
</table>

<h2>Product Features</h2>
<ul>
    <li><b>Quality</b> - All products are made with premium materials</li>
    <li><b>Warranty</b> - 1-year warranty included</li>
    <li><b>Support</b> - 24/7 customer support available</li>
</ul>

<h2>Order Process</h2>
<ol>
    <li>Select your products</li>
    <li>Add to cart</li>
    <li>Proceed to checkout</li>
    <li>Enter shipping information</li>
    <li>Complete payment</li>
</ol>

<blockquote>
    "Outstanding quality and fast delivery. Highly recommended!"
    - Happy Customer
</blockquote>

<hr/>

<p style="text-align: center; color: gray;">
    Thank you for choosing our products!
</p>
';

$pdf7->writeHTML($html7);

$completePath = __DIR__ . '/../target/html_complete.pdf';
file_put_contents($completePath, $pdf7->getOutPDFString());
echo "   Created: $completePath\n\n";

// =========================================================================
// Step 8: Using HtmlRenderer directly
// =========================================================================

echo "8. Using HtmlRenderer directly...\n";

$pdf8 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf8->setCreator('tc-lib-pdf');
$pdf8->setTitle('HtmlRenderer Demo');
$pdf8->enableDefaultPageContent();
$pdf8->font->insert($pdf8->pon, 'helvetica', '', 12);

$pdf8->addPage();

$renderer = $pdf8->createHtmlRenderer();
$renderer->setMargins(20, 20, 20);
$renderer->setFont('helvetica', '', 11);

$html8 = '
<h1>Custom Renderer Settings</h1>
<p>This document uses custom margins and font settings.</p>
<p>The HtmlRenderer class provides more control over the rendering process.</p>
';

$renderer->render($html8);

$rendererPath = __DIR__ . '/../target/html_renderer.pdf';
file_put_contents($rendererPath, $pdf8->getOutPDFString());
echo "   Created: $rendererPath\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- Basic HTML to PDF conversion\n";
echo "- Headings (h1-h6)\n";
echo "- Text formatting (bold, italic, underline)\n";
echo "- Ordered and unordered lists\n";
echo "- Nested lists\n";
echo "- Tables with headers\n";
echo "- CSS inline styling (color, font-size, text-align)\n";
echo "- Blockquotes and preformatted text\n";
echo "- Line breaks and horizontal rules\n";
echo "- Custom margins and fonts via HtmlRenderer\n\n";

echo "Supported HTML Elements:\n";
echo "------------------------\n";
echo "- Block: p, div, h1-h6, blockquote, pre, section, article, header, footer\n";
echo "- Inline: span, b, strong, i, em, u, a, code\n";
echo "- Lists: ul, ol, li\n";
echo "- Tables: table, thead, tbody, tfoot, tr, th, td\n";
echo "- Other: br, hr, img\n\n";

echo "Supported CSS Properties:\n";
echo "-------------------------\n";
echo "- font-family, font-size, font-weight, font-style\n";
echo "- color, background-color\n";
echo "- text-align, line-height\n\n";

echo "Methods:\n";
echo "--------\n";
echo "- \$pdf->writeHTML(\$html): Simple HTML rendering\n";
echo "- \$pdf->writeHTMLWithMargins(\$html, \$left, \$top, \$right): With custom margins\n";
echo "- \$pdf->createHtmlRenderer(): Get HtmlRenderer for advanced control\n";
