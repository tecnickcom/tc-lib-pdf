<?php
/**
 * E058_embedded_files_portfolio.php
 *
 * @since       2026-05-01
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
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
 * Demonstrate PDF as a document container (portfolio / package).
 *
 * Multiple file types are embedded inside the PDF using:
 *   addEmbeddedFile()           – embed a file from disk
 *   addContentAsEmbeddedFile()  – embed content generated in-memory (CSV, XML, JSON)
 *
 * Each embedded file is then linked to an annotation on the document page so
 * that clicking the annotation in a viewer opens or exports the attachment.
 *
 * Embedded files:
 *   invoice.csv   – tabular invoice data
 *   report.xml    – XML report
 *   config.json   – JSON configuration
 */

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 058');
$pdf->setTitle('Embedded Files Portfolio');
$pdf->setKeywords('TCPDF tc-lib-pdf example embedded files portfolio attachment csv xml json');
$pdf->setPDFFilename('E058_embedded_files_portfolio.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

$bfont  = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);
$bfontB = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 12);

// -----------------------------------------------------------------------
// Build the in-memory attachment content
// -----------------------------------------------------------------------

$csvContent = <<<CSV
item_no,description,qty,unit_price,total
1,"Widget A",10,9.99,99.90
2,"Widget B",5,24.50,122.50
3,"Service Fee",1,150.00,150.00
,,,,
,,"Subtotal",,372.40
,,"Tax (10%)",,37.24
,,"Total",,409.64
CSV;

$xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<report generated="2026-05-01" version="1.0">
  <summary>
    <title>Monthly Sales Report</title>
    <period>April 2026</period>
    <currency>USD</currency>
  </summary>
  <items>
    <item id="1"><name>Widget A</name><qty>10</qty><revenue>99.90</revenue></item>
    <item id="2"><name>Widget B</name><qty>5</qty><revenue>122.50</revenue></item>
    <item id="3"><name>Service Fee</name><qty>1</qty><revenue>150.00</revenue></item>
  </items>
  <totals>
    <subtotal>372.40</subtotal>
    <tax>37.24</tax>
    <total>409.64</total>
  </totals>
</report>
XML;

$jsonContent = \json_encode([
    'application' => 'tc-lib-pdf demo',
    'version'     => '1.0',
    'settings'    => [
        'compress'      => true,
        'unicode'       => true,
        'subset_fonts'  => false,
        'pdf_version'   => '1.7',
    ],
    'output' => [
        'mode'     => 'render',
        'filename' => 'E058_embedded_files_portfolio.pdf',
    ],
], \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);

if ($jsonContent === false) {
    throw new \RuntimeException('Failed to encode JSON content');
}

// Embed content generated in-memory
$pdf->addContentAsEmbeddedFile('invoice.csv',  $csvContent,  'text/csv',             'Data',   'Invoice line items');
$pdf->addContentAsEmbeddedFile('report.xml',   $xmlContent,  'application/xml',      'Data',   'Monthly sales report');
$pdf->addContentAsEmbeddedFile('config.json',  $jsonContent, 'application/json',     'Source', 'Library configuration');

// -----------------------------------------------------------------------
// Page 1 – Portfolio overview
// -----------------------------------------------------------------------
$page1 = $pdf->addPage();
$pdf->page->addContent($bfontB['out']);
$pdf->page->addContent($pdf->getTextCell('Embedded Files Portfolio', 15, 15, 180, 0, 0, 1, 'T', 'C'));
$pdf->page->addContent($bfont['out']);
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$html = <<<HTML
<p>This PDF acts as a self-contained package by embedding three data files in different formats.
Click the annotation icons on this page (or use your PDF viewer's attachment panel) to
access the files.</p>

<p><b>Embedded attachments</b></p>
<table border="1" cellpadding="4" cellspacing="0">
  <tr><th>Filename</th><th>MIME type</th><th>AFRelationship</th><th>Description</th></tr>
  <tr><td>invoice.csv</td><td>text/csv</td><td>Data</td><td>Invoice line items</td></tr>
  <tr><td>report.xml</td><td>application/xml</td><td>Data</td><td>Monthly sales report</td></tr>
  <tr><td>config.json</td><td>application/json</td><td>Source</td><td>Library configuration</td></tr>
</table>

<p><b>API used</b></p>
<table border="1" cellpadding="4" cellspacing="0">
  <tr><th>Method</th><th>Use case</th></tr>
  <tr><td>addEmbeddedFile()</td><td>Embed a file read from disk.</td></tr>
  <tr><td>addContentAsEmbeddedFile()</td><td>Embed content generated in memory.</td></tr>
  <tr><td>setAnnotation() with Fileattachment subtype</td><td>Visible annotation linking to an embedded file.</td></tr>
</table>

<p>The <em>AFRelationship</em> value categorises each attachment for PDF processors:
<em>Source</em> = the source that produced this PDF; <em>Data</em> = data associated with the PDF.</p>
HTML;

$pdf->addHTMLCell($html, 15, 30, 180);

// -----------------------------------------------------------------------
// Attachment annotations – link visible icons to each embedded file
// Annotation type 'FileAttachment', opt 'fs' = file key, 'name' = icon name
// -----------------------------------------------------------------------
$annotations = [
    ['file' => 'invoice.csv',  'label' => 'invoice.csv',  'x' => 15,  'y' => 175],
    ['file' => 'report.xml',   'label' => 'report.xml',   'x' => 60,  'y' => 175],
    ['file' => 'config.json',  'label' => 'config.json',  'x' => 105, 'y' => 175],
];

foreach ($annotations as $ann) {
    $annotId = $pdf->setAnnotation(
        (float) $ann['x'],
        (float) $ann['y'],
        40.0,
        10.0,
        (string) $ann['label'],
        [
            'subtype' => 'FileAttachment',
            'fs'      => $ann['file'],
            'name'    => 'PushPin',
            'f'       => 4,
        ]
    );
    $pdf->page->addAnnotRef($annotId, $page1['pid']);
}

// -----------------------------------------------------------------------
// Page 2 – Content preview
// -----------------------------------------------------------------------
$page2 = $pdf->addPage();
$pdf->page->addContent($bfontB['out']);
$pdf->page->addContent($pdf->getTextCell('Attachment Content Preview', 15, 15, 180, 0, 0, 1, 'T', 'C'));
$pdf->page->addContent($bfont['out']);
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$previewHtml = <<<HTML
<p><b>invoice.csv (first few lines)</b></p>
<pre>item_no,description,qty,unit_price,total
1,"Widget A",10,9.99,99.90
2,"Widget B",5,24.50,122.50
3,"Service Fee",1,150.00,150.00</pre>

<p><b>report.xml (excerpt)</b></p>
<pre>&lt;report generated="2026-05-01" version="1.0"&gt;
  &lt;summary&gt;&lt;title&gt;Monthly Sales Report&lt;/title&gt;&lt;/summary&gt;
  &lt;totals&gt;
    &lt;subtotal&gt;372.40&lt;/subtotal&gt;
    &lt;total&gt;409.64&lt;/total&gt;
  &lt;/totals&gt;
&lt;/report&gt;</pre>

<p><b>config.json (excerpt)</b></p>
<pre>{
  "application": "tc-lib-pdf demo",
  "version": "1.0",
  "settings": {
    "compress": true,
    "unicode": true
  }
}</pre>
HTML;

$pdf->addHTMLCell($previewHtml, 15, 30, 180);

// -----------------------------------------------------------------------
// Output
// -----------------------------------------------------------------------
$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
