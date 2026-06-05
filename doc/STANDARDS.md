# Standards and Conformance

Back to root overview: [README.md](../README.md#in-depth-documentation)

## PDF/A Archival

`tc-lib-pdf` supports PDF/A output for long-term archival workflows (ISO 19005). Pass the mode string as the `mode` argument to the `Tcpdf` constructor:

```php
// PDF/A-1b (default conformance level when suffix is omitted)
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfa1');

// Explicit conformance levels
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfa1a');  // PDF/A-1a
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfa1b');  // PDF/A-1b
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfa2a');  // PDF/A-2a
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfa2b');  // PDF/A-2b
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfa2u');  // PDF/A-2u
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfa3a');  // PDF/A-3a
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfa3b');  // PDF/A-3b
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfa3u');  // PDF/A-3u
```

| Mode suffix | Conformance | Unicode ToUnicode | Tagged structure |
|-------------|-------------|-------------------|------------------|
| `a` | Level A | required | required |
| `b` | Level B | required | not required |
| `u` | Level U (parts 2/3 only) | required | not required |

PDF/A-3 supports embedding arbitrary file attachments (for example XML invoice payloads). This is the basis for **Factur-X / ZUGFeRD** workflows - embed the structured XML in a PDF/A-3 document and register the relationship via XMP metadata:

```php
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfa3');
// ... build document ...
$pdf->Annotation(/* file attachment annotation pointing to the XML */);
$pdf->setCustomXMP('x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag', $xmpBag);
```

Runnable example (invoice with embedded Factur-X XML): [examples/E001_invoice.php](../examples/E001_invoice.php).

## PDF/X Conformance

`tc-lib-pdf` supports multiple PDF/X profiles for print-exchange workflows. Pass the mode string as the `mode` argument to the `Tcpdf` constructor:

```php
// Generic PDF/X alias (maps to the library's baseline print-exchange workflow)
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfx');

// Specific variants
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfx1a'); // PDF/X-1a:2003
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfx3');  // PDF/X-3:2003
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfx4');  // PDF/X-4:2010
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfx5');  // PDF/X-5g:2010
```

Each variant automatically applies the appropriate conformance constraints:

| Mode | Min PDF version | Transparency | Process colors | GTS_PDFXVersion |
|------|-----------------|--------------|----------------|-----------------|
| `pdfx` / `pdfx3` | 1.3 | blocked | CMYK forced | PDF/X-3:2003 |
| `pdfx1a` | 1.3 | blocked | CMYK forced | PDF/X-1a:2003 |
| `pdfx4` | 1.6 | allowed | unrestricted | PDF/X-4:2010 |
| `pdfx5` | 1.6 | allowed | unrestricted | PDF/X-5g:2010 |

All PDF/X modes suppress encryption and JavaScript (not permitted by the ISO 15930 standard).

Runnable examples: [examples/E010_pdfx.php](../examples/E010_pdfx.php) through [examples/E014_pdfx5.php](../examples/E014_pdfx5.php).

## PDF/UA Accessibility

`tc-lib-pdf` supports tagged PDF output conforming to PDF/UA (ISO 14289). Pass the mode string as the `mode` argument to the `Tcpdf` constructor:

```php
// Generic PDF/UA alias
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfua');

// Specific parts
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfua1'); // PDF/UA-1 (PDF 1.7)
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfua2'); // PDF/UA-2 (PDF 2.0)
```

When a PDF/UA mode is active the library automatically:

- Writes a `StructTreeRoot` with a `ParentTree` that maps every page to its tagged content blocks
- Emits `MarkInfo << /Marked true >>` in the document catalog
- Sets `/Lang` (defaults to `en-US` when not explicitly provided)
- Forces `ViewerPreferences /DisplayDocTitle true`
- Maps HTML heading elements (`h1`-`h6`) to PDF structure roles `H1`-`H6` with level-clamping to prevent skipped heading levels
- Tags text content with MCIDs and wraps each run in the appropriate structure element (`P`, `H1`-`H6`, `Link`, etc.)
- Tags `<img>` elements as `Figure` with their `alt` attribute written as `/Alt` in the structure element
- Emits `ActualText` entries for ligatures and special glyphs so text extraction and screen readers work correctly
- Provides Artifact marked-content helpers for non-semantic content (`beginArtifact()`, `endArtifact()`, `addArtifactContent()`)

To provide the document language explicitly:

```php
$pdf->setDocInfo(['a_meta_language' => 'de-DE']);
```

To tag decorative or repeated content as Artifact (for example headers, footers, and page numbers):

```php
$pid = $pdf->addPage()['pid'];

$headerOperators = $pdf->graph->getLine(10, 10, 200, 10);
$pdf->addArtifactContent($headerOperators, $pid, 'Pagination', 'Header');

$footerText = $pdf->getTextCell('Page 1', 180, 280, 20, 5);
$pdf->addArtifactContent($footerText, $pid, 'Pagination', 'Footer');
```

In PDF/UA mode, the built-in `defaultPageContent()` page-number footer is emitted as `Artifact` with
`/Type /Pagination /Subtype /Footer`.

Runnable examples: [examples/E015_pdfua.php](../examples/E015_pdfua.php) through [examples/E017_pdfua2.php](../examples/E017_pdfua2.php).
