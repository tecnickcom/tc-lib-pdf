<?php
/**
 * pdfa3_embedded.php
 *
 * Example demonstrating PDF/A-3 with embedded files (e.g., ZUGFeRD/Factur-X invoices).
 *
 * PDF/A-3 is the only PDF/A version that allows embedding arbitrary files.
 * This is commonly used for:
 * - ZUGFeRD invoices (Germany)
 * - Factur-X invoices (France/Germany)
 * - XRechnung (Germany)
 * - Embedded source data files
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

/**
 * AFRelationship values for PDF/A-3 embedded files:
 *
 * - 'Source'      : The embedded file is the source for the PDF (e.g., original document)
 * - 'Data'        : The embedded file contains data (e.g., XML invoice data)
 * - 'Alternative' : An alternative representation of the PDF content
 * - 'Supplement'  : Additional information supplementing the PDF content
 * - 'Unspecified' : No specific relationship defined
 */

// Output file
$outputFile = \realpath(__DIR__ . '/../target') . '/example_pdfa3_embedded.pdf';

// Create TCPDF instance with PDF/A-3b mode
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm',                    // unit
    true,                    // unicode
    false,                   // subset font
    false,                   // compress (disabled for PDF/A-3)
    'pdfa3b',                // PDF/A-3b mode
    null,                    // encryption (not allowed in PDF/A)
);

// Set document metadata (required for PDF/A compliance)
$pdf->setCreator('tc-lib-pdf PDF/A-3 Example');
$pdf->setAuthor('TCPDF Library');
$pdf->setSubject('PDF/A-3 with Embedded Files Demonstration');
$pdf->setTitle('PDF/A-3b Invoice with ZUGFeRD Data');
$pdf->setKeywords('TCPDF, PDF/A-3, ZUGFeRD, Factur-X, embedded files, invoice');
$pdf->setPDFFilename('example_pdfa3_embedded.pdf');

// Viewer preferences
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

// Enable default page content
$pdf->enableDefaultPageContent();

// Insert fonts
$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$fontBold = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 14);

// Add a page
$page = $pdf->addPage();
$pdf->setBookmark('Invoice', '', 0, -1, 0, 0, 'B', 'blue');

// Set up graph object
$pdf->graph->setPageWidth($page['width']);
$pdf->graph->setPageHeight($page['height']);

// Add content - Title
$pdf->page->addContent($fontBold['out']);
$title = $pdf->getTextLine('INVOICE #2025-001', 20, 20, $page['width'] - 40);
$pdf->page->addContent($title);

// Add invoice details
$pdf->page->addContent($font['out']);

$invoiceContent = 'Date: January 2, 2025
Invoice Number: 2025-001

Bill To:
  Example Customer
  123 Main Street
  Berlin, Germany 10115

Items:
  1. Software License         EUR 500.00
  2. Support (12 months)      EUR 200.00
  3. Training Session         EUR 150.00

Subtotal:                     EUR 850.00
VAT (19%):                    EUR 161.50
-----------------------------------------
Total:                        EUR 1,011.50

Payment Terms: Net 30 days
Bank: Example Bank AG
IBAN: DE89 3704 0044 0532 0130 00

This invoice contains embedded ZUGFeRD/Factur-X XML data
for automated processing. The XML data can be extracted
using PDF/A-3 compliant software.';

$pdf->setDefaultCellPadding(2, 2, 2, 2);

$style = [
    'all' => [
        'lineWidth' => 0.5,
        'lineCap' => 'round',
        'lineJoin' => 'round',
        'lineColor' => '#336699',
        'fillColor' => '#f0f8ff',
    ],
];

$textbox = $pdf->getTextCell(
    $invoiceContent,
    20,
    35,
    $page['width'] - 40,
    0,
    0,
    1.2,
    'T',
    'L',
    null,
    $style,
);
$pdf->page->addContent($textbox);

// Create sample ZUGFeRD/Factur-X XML content
$zugferdXml = '<?xml version="1.0" encoding="UTF-8"?>
<rsm:CrossIndustryInvoice xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100"
    xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100"
    xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100">
  <rsm:ExchangedDocumentContext>
    <ram:GuidelineSpecifiedDocumentContextParameter>
      <ram:ID>urn:factur-x.eu:1p0:basic</ram:ID>
    </ram:GuidelineSpecifiedDocumentContextParameter>
  </rsm:ExchangedDocumentContext>
  <rsm:ExchangedDocument>
    <ram:ID>2025-001</ram:ID>
    <ram:TypeCode>380</ram:TypeCode>
    <ram:IssueDateTime>
      <udt:DateTimeString format="102">20250102</udt:DateTimeString>
    </ram:IssueDateTime>
  </rsm:ExchangedDocument>
  <rsm:SupplyChainTradeTransaction>
    <ram:ApplicableHeaderTradeSettlement>
      <ram:InvoiceCurrencyCode>EUR</ram:InvoiceCurrencyCode>
      <ram:SpecifiedTradeSettlementHeaderMonetarySummation>
        <ram:LineTotalAmount>850.00</ram:LineTotalAmount>
        <ram:TaxTotalAmount currencyID="EUR">161.50</ram:TaxTotalAmount>
        <ram:GrandTotalAmount>1011.50</ram:GrandTotalAmount>
        <ram:DuePayableAmount>1011.50</ram:DuePayableAmount>
      </ram:SpecifiedTradeSettlementHeaderMonetarySummation>
    </ram:ApplicableHeaderTradeSettlement>
  </rsm:SupplyChainTradeTransaction>
</rsm:CrossIndustryInvoice>';

// Embed the ZUGFeRD XML file
// Using 'Data' relationship as this is the invoice data
$pdf->addXmlEmbeddedFile(
    'factur-x.xml',
    $zugferdXml,
    'Data',  // AFRelationship - Data for invoice XML
    'Factur-X invoice data in XML format'
);

// You can also embed additional files with different relationships
$additionalData = '{"invoiceId": "2025-001", "amount": 1011.50, "currency": "EUR"}';
$pdf->addContentAsEmbeddedFile(
    'invoice-data.json',
    $additionalData,
    'application/json',
    'Supplement',  // Additional supplementary data
    'Invoice data in JSON format for API integration'
);

// Generate PDF output
$rawpdf = $pdf->getOutPDFString();

// Save to file
$pdf->savePDF(\dirname(__DIR__) . '/target', $rawpdf);

echo "PDF/A-3 document with embedded files generated: " . $outputFile . "\n";
echo "\nEmbedded files:\n";
echo "  1. factur-x.xml (AFRelationship: Data) - ZUGFeRD/Factur-X invoice XML\n";
echo "  2. invoice-data.json (AFRelationship: Supplement) - JSON data\n";
echo "\nTo verify PDF/A-3 compliance and extract embedded files:\n";
echo "  - Use veraPDF (https://verapdf.org/)\n";
echo "  - Use Adobe Acrobat Pro\n";
echo "  - Use pdfdetach from poppler-utils: pdfdetach -saveall example_pdfa3_embedded.pdf\n";
