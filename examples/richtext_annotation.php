<?php
/**
 * richtext_annotation.php
 *
 * Example demonstrating rich text support in annotations and form fields.
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

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('Rich Text Example');
$pdf->setTitle('Rich Text in Annotations');
$pdf->setKeywords('TCPDF, PDF, rich text, annotations, example');

// Enable default page content
$pdf->enableDefaultPageContent();

// Insert font
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Add a page
$pdf->addPage();

// Add title
$pdf->page->addContent(
    "BT\n" .
    "/F1 14 Tf\n" .
    "1 0 0 1 28.35 800 Tm\n" .
    "(Rich Text in Annotations Example) Tj\n" .
    "ET\n"
);

echo "Rich Text in Annotations Example\n";
echo "=================================\n\n";

// =========================================================================
// Example 1: RichText Builder
// =========================================================================

echo "1. Creating rich text content using the RichText builder...\n";

$rt = $pdf->createRichText('Helvetica', 12, '#000000');

// Build formatted content
$rt->startParagraph('left')
   ->addBold('Important Notice: ')
   ->addText('This document contains ')
   ->addColored('highlighted information', '#ff0000')
   ->addText(' that requires your attention.')
   ->endParagraph();

$rt->addLineBreak();

$rt->startParagraph('left')
   ->addText('Please review the following: ')
   ->addBoldItalic('terms and conditions')
   ->addText(' before proceeding.')
   ->endParagraph();

$richContent = $rt->build();

echo "   Rich text content created.\n";
echo "   Content preview:\n";
echo "   " . substr($richContent, 0, 100) . "...\n\n";

// =========================================================================
// Example 2: Adding styled text segments
// =========================================================================

echo "2. Creating styled text with custom formatting...\n";

$rt2 = $pdf->createRichText('Arial', 14, '#333333');

$rt2->addStyled('Custom styled text: ', [
    'font-weight' => 'bold',
    'font-size' => '16pt',
    'color' => '#2196F3',
]);

$rt2->addSized('Large text ', 18);
$rt2->addSized('and small text', 10);

$rt2->addLineBreak();

$rt2->addUnderline('This text is underlined');
$rt2->addText(' and ');
$rt2->addItalic('this is italic');

$styledContent = $rt2->build();

echo "   Styled content created.\n\n";

// =========================================================================
// Example 3: Simple markup conversion
// =========================================================================

echo "3. Converting simple HTML-like markup to rich text...\n";

$rt3 = $pdf->createRichText();

$markup = '<p>This is a <b>simple</b> paragraph with <i>basic</i> formatting.</p>'
        . '<p>Second paragraph with <b><i>bold italic</i></b> text.</p>';

$markupContent = $rt3->fromMarkup($markup);

echo "   Markup: $markup\n";
echo "   Converted to rich text XHTML.\n\n";

// =========================================================================
// Example 4: Default Appearance (DA) strings
// =========================================================================

echo "4. Creating Default Appearance strings for PDF...\n";

$da1 = \Com\Tecnick\Pdf\RichText::createDA('Helv', 12, '#000000');
echo "   DA (black, 12pt): $da1\n";

$da2 = \Com\Tecnick\Pdf\RichText::createDA('Helv', 14, '#ff0000');
echo "   DA (red, 14pt): $da2\n";

$ds = \Com\Tecnick\Pdf\RichText::createDS('Helvetica', 12, '#0000ff', 'center');
echo "   DS (blue, centered): $ds\n\n";

// =========================================================================
// Example 5: Full workflow with rich text content
// =========================================================================

echo "5. Complete rich text workflow example...\n";

// Create rich text for an annotation
$annotation_rt = $pdf->createRichText('Helvetica', 11, '#333333');

$annotation_rt->addBold('Review Required')
              ->addLineBreak()
              ->addText('Please check the following items:')
              ->addLineBreak()
              ->addColored('1. ', '#2196F3')->addText('Verify calculations')
              ->addLineBreak()
              ->addColored('2. ', '#2196F3')->addText('Check formatting')
              ->addLineBreak()
              ->addColored('3. ', '#2196F3')->addText('Approve changes');

$annotation_content = $annotation_rt->build();

// Plain text fallback
$plain_text = "Review Required\nPlease check the following items:\n1. Verify calculations\n2. Check formatting\n3. Approve changes";

echo "   Rich text annotation content created.\n";
echo "   Content length: " . strlen($annotation_content) . " bytes\n\n";

// =========================================================================
// Output
// =========================================================================

echo "Features demonstrated:\n";
echo "- RichText builder with fluent interface\n";
echo "- Bold, italic, underline formatting\n";
echo "- Custom colors and font sizes\n";
echo "- Paragraph alignment\n";
echo "- Simple HTML markup conversion\n";
echo "- Default Appearance (DA) string generation\n";
echo "- Default Style (DS) string generation\n";
echo "\n";

echo "Note: To see rich text annotations in a PDF:\n";
echo "1. Create the PDF with addRichTextAnnotation()\n";
echo "2. Open in Adobe Acrobat Reader\n";
echo "3. Rich text formatting will be visible in annotations\n";

// Add descriptive text to the PDF using PDF operators
$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 28.35 770 Tm\n" .
    "(This PDF demonstrates the RichText class for creating) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 28.35 755 Tm\n" .
    "(formatted text content in annotations and form fields.) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 28.35 730 Tm\n" .
    "(RichText Builder Features:) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 10 Tf\n" .
    "1 0 0 1 42.52 715 Tm\n" .
    "(- addBold\\(\\), addItalic\\(\\), addBoldItalic\\(\\)) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 10 Tf\n" .
    "1 0 0 1 42.52 700 Tm\n" .
    "(- addColored\\(\\) for colored text) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 10 Tf\n" .
    "1 0 0 1 42.52 685 Tm\n" .
    "(- addSized\\(\\) for custom font sizes) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 10 Tf\n" .
    "1 0 0 1 42.52 670 Tm\n" .
    "(- addStyled\\(\\) for custom CSS styles) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 10 Tf\n" .
    "1 0 0 1 42.52 655 Tm\n" .
    "(- addParagraph\\(\\) with alignment) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 10 Tf\n" .
    "1 0 0 1 42.52 640 Tm\n" .
    "(- fromMarkup\\(\\) for HTML conversion) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 28.35 615 Tm\n" .
    "(PDF Rich Text uses XHTML subset:) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 10 Tf\n" .
    "1 0 0 1 42.52 600 Tm\n" .
    "(Allowed tags: <p>, <span>, <b>, <i>, <br>, <font>) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 10 Tf\n" .
    "1 0 0 1 42.52 585 Tm\n" .
    "(Supported CSS: color, font-size, font-family, text-align) Tj\n" .
    "ET\n"
);

// Output PDF
$pdfData = $pdf->getOutPDFString();

// Save to file
$outputPath = __DIR__ . '/../target/richtext_example.pdf';
file_put_contents($outputPath, $pdfData);

echo "\nPDF saved to: $outputPath\n";
