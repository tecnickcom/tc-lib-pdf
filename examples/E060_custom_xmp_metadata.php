<?php
/**
 * E060_custom_xmp_metadata.php
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
 * Demonstrate custom XMP namespace injection via setCustomXMP().
 *
 * PDF files carry an XMP metadata stream alongside the classic Info dictionary.
 * The tc-lib-pdf library always emits standard namespaces (dc:, xmp:, pdf:,
 * xmpMM:, pdfaid:, etc.).  setCustomXMP() lets you inject arbitrary additional
 * XMP fragments into specific positions within the XMP envelope.
 *
 * Valid keys and their injection points
 * ──────────────────────────────────────
 *   'x:xmpmeta'
 *       Inserted just before </x:xmpmeta> — top-level envelope extension.
 *
 *   'x:xmpmeta.rdf:RDF'
 *       Inserted just before </rdf:RDF> — adds a new rdf:Description block.
 *
 *   'x:xmpmeta.rdf:RDF.rdf:Description'
 *       Inserted into the main rdf:Description — adds properties to the
 *       existing shared description block (dc, xmp, pdf properties).
 *
 *   'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas'
 *   'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag'
 *       PDF/A extension schema declarations (only meaningful in PDF/A mode).
 *
 * IMPORTANT: the injected XML is NOT validated by the library.  You are
 * responsible for well-formed, namespace-correct XMP.  Malformed XMP will
 * not prevent PDF generation but may confuse metadata readers.
 *
 * Use cases illustrated here:
 *   1. IPTC Core rights metadata   (photojournalism / stock-photography DAM)
 *   2. XMP-MM version history      (document management / DAM systems)
 *   3. Custom application metadata (arbitrary key-value pairs for integration)
 */

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 060');
$pdf->setTitle('Custom XMP Metadata Injection');
$pdf->setKeywords('TCPDF tc-lib-pdf example xmp metadata iptc dublin core dam archiving');
$pdf->setPDFFilename('E060_custom_xmp_metadata.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// -----------------------------------------------------------------------
// 1.  IPTC Core rights and creator metadata
//     Injected as a new rdf:Description block inside rdf:RDF.
// -----------------------------------------------------------------------
$iptcXmp = <<<'XMP'
    <rdf:Description rdf:about=""
        xmlns:Iptc4xmpCore="http://iptc.org/std/Iptc4xmpCore/1.0/xmlns/">
      <Iptc4xmpCore:CopyrightNotice>Copyright © 2026 Tecnick.com LTD. All rights reserved.</Iptc4xmpCore:CopyrightNotice>
      <Iptc4xmpCore:CreatorContactInfo>
        <rdf:Description>
          <Iptc4xmpCore:CiEmailWork>info@tecnick.com</Iptc4xmpCore:CiEmailWork>
          <Iptc4xmpCore:CiUrlWork>https://tecnick.com</Iptc4xmpCore:CiUrlWork>
        </rdf:Description>
      </Iptc4xmpCore:CreatorContactInfo>
      <Iptc4xmpCore:UsageTerms>
        <rdf:Alt>
          <rdf:li xml:lang="en">Permission is granted to reproduce for personal and commercial use,
            citing the source.  Modification is not allowed.</rdf:li>
        </rdf:Alt>
      </Iptc4xmpCore:UsageTerms>
    </rdf:Description>

XMP;

// Key: inject before </rdf:RDF>
$pdf->setCustomXMP('x:xmpmeta.rdf:RDF', $iptcXmp);

// -----------------------------------------------------------------------
// 2.  XMP Media Management — version / revision history
//     Also injected as a new rdf:Description block.
// -----------------------------------------------------------------------
$xmpMmXmp = <<<'XMP'
    <rdf:Description rdf:about=""
        xmlns:xmpMM="http://ns.adobe.com/xap/1.0/mm/"
        xmlns:stEvt="http://ns.adobe.com/xap/1.0/sType/ResourceEvent#"
        xmlns:stRef="http://ns.adobe.com/xap/1.0/sType/ResourceRef#">
      <xmpMM:DocumentID>urn:uuid:tc-lib-pdf-example-060-2026</xmpMM:DocumentID>
      <xmpMM:InstanceID>urn:uuid:tc-lib-pdf-example-060-instance-001</xmpMM:InstanceID>
      <xmpMM:VersionID>1.0</xmpMM:VersionID>
      <xmpMM:History>
        <rdf:Seq>
          <rdf:li rdf:parseType="Resource">
            <stEvt:action>created</stEvt:action>
            <stEvt:when>2026-05-01T00:00:00+00:00</stEvt:when>
            <stEvt:softwareAgent>tc-lib-pdf</stEvt:softwareAgent>
          </rdf:li>
        </rdf:Seq>
      </xmpMM:History>
      <xmpMM:DerivedFrom rdf:parseType="Resource">
        <stRef:documentID>urn:uuid:tc-lib-pdf-example-059-2026</stRef:documentID>
        <stRef:versionID>1.0</stRef:versionID>
      </xmpMM:DerivedFrom>
    </rdf:Description>

XMP;

$pdf->setCustomXMP('x:xmpmeta.rdf:RDF', $xmpMmXmp);

// -----------------------------------------------------------------------
// 3.  Custom application metadata (key-value pairs for system integration)
//     Injected as extra properties in the existing shared rdf:Description.
// -----------------------------------------------------------------------
$appXmp = <<<'XMP'
      <tcpdf:exampleId>E060</tcpdf:exampleId>
      <tcpdf:generatedBy>tc-lib-pdf</tcpdf:generatedBy>
      <tcpdf:generationDate>2026-05-01</tcpdf:generationDate>

XMP;

// Note: this key injects into the existing shared rdf:Description block.
// The tcpdf: prefix must be declared in the same Description or a parent.
// For self-contained safety we wrap it in its own Description with the ns decl.
$appXmpBlock = <<<'XMP'
    <rdf:Description rdf:about=""
        xmlns:tcpdf="https://github.com/tecnickcom/tc-lib-pdf/ns/xmp/1.0/">
      <tcpdf:exampleId>E060</tcpdf:exampleId>
      <tcpdf:generatedBy>tc-lib-pdf</tcpdf:generatedBy>
      <tcpdf:generationDate>2026-05-01</tcpdf:generationDate>
      <tcpdf:featureTag>custom-xmp-metadata</tcpdf:featureTag>
    </rdf:Description>

XMP;

$pdf->setCustomXMP('x:xmpmeta.rdf:RDF', $appXmpBlock);

// -----------------------------------------------------------------------
// Page — explanation and key summary
// -----------------------------------------------------------------------
$bfont  = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);
$bfontB = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 14);

$page1 = $pdf->addPage();

$html = <<<'HTML'
<h1 style="font-size:16pt; color:#003366;">Custom XMP Metadata Injection</h1>
<p style="font-size:10pt; color:#333333;">
This PDF embeds three custom XMP metadata blocks beyond the standard dc:/xmp:
namespaces that tc-lib-pdf emits automatically.  Inspect the XMP stream with
a PDF inspector or <code>exiftool -XMP -b output.pdf</code> to verify.
</p>

<h2 style="font-size:13pt; color:#005599;">Block 1 — IPTC Core rights</h2>
<p style="font-size:9pt; color:#333333;">
Namespace: <b>Iptc4xmpCore</b> (http://iptc.org/std/Iptc4xmpCore/1.0/xmlns/)<br />
Injection key: <code>x:xmpmeta.rdf:RDF</code><br />
Contains: CopyrightNotice, CreatorContactInfo (email, URL), UsageTerms.<br />
Typical use: photojournalism DAM, stock-photography catalogues.
</p>

<h2 style="font-size:13pt; color:#005599;">Block 2 — XMP-MM version history</h2>
<p style="font-size:9pt; color:#333333;">
Namespace: <b>xmpMM</b> (http://ns.adobe.com/xap/1.0/mm/)<br />
Injection key: <code>x:xmpmeta.rdf:RDF</code><br />
Contains: DocumentID, InstanceID, VersionID, History (sequence of events), DerivedFrom.<br />
Typical use: document management systems, digital asset lineage tracking.
</p>

<h2 style="font-size:13pt; color:#005599;">Block 3 — Custom application metadata</h2>
<p style="font-size:9pt; color:#333333;">
Namespace: <b>tcpdf</b> (https://github.com/tecnickcom/tc-lib-pdf/ns/xmp/1.0/)<br />
Injection key: <code>x:xmpmeta.rdf:RDF</code><br />
Contains: exampleId, generatedBy, generationDate, featureTag.<br />
Typical use: integration metadata for build pipelines, print-MIS systems, or
any workflow that needs to carry arbitrary key-value pairs inside the PDF
without modifying the Info dictionary.
</p>

<h2 style="font-size:13pt; color:#005599;">Important notes</h2>
<p style="font-size:9pt; color:#333333;">
• The injected XML is embedded as-is; the library does NOT validate XMP well-formedness.<br />
• Multiple calls with the same key are concatenated in insertion order.<br />
• Injection into <code>x:xmpmeta.rdf:RDF.rdf:Description</code> adds properties to the
  shared Description block; prefer a standalone Description block (as done here) to
  keep namespace declarations self-contained.<br />
• All standard metadata (title, author, subject, keywords, dates, producer) is still
  emitted automatically and is not affected by setCustomXMP().
</p>
HTML;

$pdf->addHTMLCell($html, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
