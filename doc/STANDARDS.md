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

Every PDF/A mode applies the restrictions of ISO 19005:

- Encryption is not permitted: an encryption object passed to the constructor is ignored, and no `/Encrypt` entry is written.
- JavaScript is not permitted and is omitted from the output.
- PDF/A-1 does not allow live transparency, so soft masks, blend modes and transparency groups are suppressed. PDF/A-2 and PDF/A-3 allow them.
- Embedded files are only allowed in PDF/A-3.
- Imported streams that use the `LZWDecode` filter are re-encoded with `FlateDecode` (see [PDF_IMPORT.md](PDF_IMPORT.md)).
- Every annotation except links and popups carries a normal appearance stream: one is generated from the annotation rectangle when the caller supplies none.
- Annotation flags carry `Print` and never carry `Hidden` or `NoView`.
- Annotation subtypes outside the permitted set are dropped by `setAnnotation()`, which returns 0: `3d`, `movie`, `screen` and `sound` in every part, plus `caret`, `fileattachment`, `polygon`, `polyline`, `redact` and `watermark` in PDF/A-1 (ISO 19005-1 clause 6.5.2).

PDF/A-3 supports embedding arbitrary file attachments (for example XML invoice payloads), which is what **Factur-X / ZUGFeRD** workflows use: embed the structured XML in a PDF/A-3 document and register the relationship via XMP metadata. `setFacturX()` does both:

```php
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfa3');
// ... build document ...
$pdf->setFacturX(
    xml: $invoiceXML,
    profile: \Com\Tecnick\Pdf\HybridProfile::FacturX,
    level: \Com\Tecnick\Pdf\HybridConformance::En16931,
);
```

The XML is embedded as an associated file with the name, MIME type and `/AFRelationship` required by the profile, and the PDF/A extension schema plus the `DocumentType`, `DocumentFileName`, `Version` and `ConformanceLevel` properties are written to the XMP metadata. `HybridProfile` covers `FacturX` (equivalent to ZUGFeRD 2.1 and later), `ZugferdV1`, `ZugferdV2` and `OrderX`; the file name, XMP namespace, prefix, version and document type follow from it. Each of them, the description and the conformance level can be overridden with the remaining arguments.

The document must be in PDF/A-3 mode: a warning is raised at output time otherwise.

Building the CII XML payload itself is out of scope: pass the XML produced by a dedicated e-invoicing library.

Runnable example (invoice with embedded Factur-X XML): [examples/E001_invoice.php](../examples/E001_invoice.php).

## PDF/X Conformance

`tc-lib-pdf` supports multiple PDF/X profiles for print-exchange workflows. Pass the mode string as the `mode` argument to the `Tcpdf` constructor:

```php
// Generic PDF/X alias (same constraints as pdfx3)
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfx');

// Specific variants
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfx1a'); // PDF/X-1a:2003
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfx3');  // PDF/X-3:2003
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfx4');  // PDF/X-4:2010
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfx5');  // PDF/X-5g:2010
```

Each variant applies its own conformance constraints:

| Mode | Min PDF version | Transparency | Process colors | GTS_PDFXVersion |
|------|-----------------|--------------|----------------|-----------------|
| `pdfx` / `pdfx3` | 1.3 | blocked | CMYK forced | PDF/X-3:2003 |
| `pdfx1a` | 1.3 | blocked | CMYK forced | PDF/X-1a:2003 |
| `pdfx4` | 1.6 | allowed | unrestricted | PDF/X-4:2010 |
| `pdfx5` | 1.6 | allowed | unrestricted | PDF/X-5g:2010 |

All PDF/X modes suppress encryption and JavaScript (not permitted by the ISO 15930 standard). A PDF/X page carries a `TrimBox` and no `ArtBox`, the interactive annotation subtypes (`widget`, `screen`, `movie`, `sound`, `fileattachment` and `3d`) are dropped, and the remaining annotations are given an appearance stream and the same flag treatment as in PDF/A.

ISO 15930 also requires an annotation to sit entirely outside the bleed box. The library does not move or drop the annotations that break this rule, since a hyperlink over body text is usually intended: it records the overlap instead, and `getWarnings()` returns the list after the document has been rendered.

```php
$pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfx1a');
// ... build document ...
$out = $pdf->getOutPDFString();

foreach ($pdf->getWarnings() as $warning) {
    // PDF/X: the /Link annotation on page 1 overlaps the BleedBox; ...
}
```

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

When a PDF/UA mode is active the library:

- Writes a `StructTreeRoot` with a `ParentTree` that maps every page to its tagged content blocks
- Emits `MarkInfo << /Marked true >>` in the document catalog
- Sets `/Lang` (defaults to `en-US` when not explicitly provided)
- Forces `ViewerPreferences /DisplayDocTitle true`
- Maps HTML heading elements (`h1`-`h6`) to PDF structure roles `H1`-`H6` with level-clamping to prevent skipped heading levels
- Tags text content with MCIDs and wraps each run in the appropriate structure element (`P`, `H1`-`H6`, `Link`, etc.)
- Tags `<img>` elements as `Figure` with their `alt` attribute written as `/Alt` in the structure element
- Emits `ActualText` entries for ligatures and special glyphs
- Provides Artifact marked-content helpers for non-semantic content (`beginArtifact()`, `endArtifact()`, `addArtifactContent()`)
- Nests every annotation in a structure element with an `OBJR` reference and a `/StructParent`: `Form` for a widget, `Link` for a link, `Annot` for the rest. `PrinterMark` and `Popup` annotations take none
- Gives a form field a `/TU` description, falling back to the field name when the `tu` option is not set

To provide the document language explicitly:

```php
$pdf->setLanguageArray(['a_meta_language' => 'de-DE']);
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

## Reproducible Output

Two runs of the same code produce different bytes by default: the creation and modification dates follow the clock, and the file identifier is drawn at random. Pin all three to obtain byte-for-byte reproducible documents, which archival and invoicing workflows often require:

```php
$pdf->setDocCreationDate(1600000000);
$pdf->setDocModificationDate(1600000000);
$pdf->setFileId('any string, or 32 hexadecimal digits');
```

`setFileId()` drives the trailer `/ID` array and the XMP `xmpMM:InstanceID` property. A value that is not 32 hexadecimal digits is hashed to that form. It cannot be called on an encrypted document, because the encryption key is derived from the identifier chosen at construction time.

XMP defines `xmpMM:DocumentID` as stable across the renditions of a document and `xmpMM:InstanceID` as unique to one saved instance, so the two never carry the same value. The document identifier is derived from the file identifier unless it is set explicitly:

```php
$pdf->setDocumentId('invoice-2026-0042');
```

The XMP packet ends with about 2 KB of padding, which is what allows a reader to rewrite the metadata in place. Documents that are never edited after generation can drop it, which also declares the packet read-only:

```php
$pdf->setXMPPaddingLines(0);
```

## Stream Compression

Compressed streams always use the `FlateDecode` filter, which is permitted by ISO 19005, ISO 15930 and ISO 14289. The library never emits `LZWDecode`, the only general-purpose filter that ISO 19005 forbids.

Compression is controlled by the `compress` argument of the `Tcpdf` constructor and is independent of the conformance mode:

```php
// compressed output (default), in any mode
$pdf = new \Com\Tecnick\Pdf\Tcpdf(compress: true, mode: 'pdfa3b');

// uncompressed output
$pdf = new \Com\Tecnick\Pdf\Tcpdf(compress: false, mode: 'pdfa3b');
```

The argument applies to page content streams, appearance and form XObjects, patterns, shaders, imported objects, image ICC profiles and palettes. Some streams do not depend on it:

- Embedded font programs and the sRGB output-intent ICC profile are always `FlateDecode` encoded.
- XMP metadata is always stored uncompressed.
- PDF/A-3 file attachments are always stored uncompressed and carry the `/Subtype` MIME entry required by ISO 19005-3.
