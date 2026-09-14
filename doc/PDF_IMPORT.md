# PDF Import

Back to root overview: [README.md](../README.md#in-depth-documentation)

`tc-lib-pdf` can import pages from existing PDFs as Form XObjects and place them on destination pages.

## Source Registration and Page Count

```php
$sourceId = $pdf->setImportSourceFile('/path/to/source.pdf');
// or: $sourceId = $pdf->setImportSourceData($rawPdfBytes);

$count = $pdf->getSourcePageCount($sourceId);
```

The page count is derived from the page tree actually reachable through `/Kids`; the declared `/Count` entry of the `/Pages` dictionary is ignored, so a forged or wrong `/Count` cannot influence how many pages are counted or imported. Structurally broken page trees (missing `/Kids`, unexpected node types, duplicate or cyclic references) raise `ImportCorruptedSourceException`.

The reachable-page walk runs once per registered source and produces a flattened page index (one effective page dictionary per page, with inherited attributes resolved) that is cached and reused by `getSourcePageCount()`, `importPage()`, and `importPages()`.

## Parser Limits and Diagnostics

`setImportSourceFile()` and `setImportSourceData()` accept an optional configuration array that is forwarded to the parser. Parsing happens once per source, at registration time, so these settings belong here rather than in the per-page `importPage()` options.

| Key | Type | Default | Meaning |
| --- | --- | --- | --- |
| `ignore_filter_errors` | `bool` | `false` | Keep a stream that fails to decode as raw data instead of failing |
| `decode_streams` | `bool` | `false` | Decode stream payloads while parsing indirect objects |
| `max_stream_size` | `int` | `33554432` | Maximum size in bytes of a single decoded stream; `0` means unlimited |
| `max_resolution_depth` | `int` | `64` | Maximum number of indirect object resolutions in flight at once; values below `1` are clamped to `1` |
| `max_nesting_depth` | `int` | `256` | Maximum nesting depth of array and dictionary objects; values below `1` are clamped to `1` |
| `strict_limits` | `bool` | `false` | Fail with `ImportResourceLimitException` as soon as a limit leaves an object unresolved |

Exceeding `max_nesting_depth` always raises `ImportResourceLimitException`: a dictionary or array that cannot be tokenized has no usable value to fall back to. Reaching `max_resolution_depth`, or meeting a reference cycle, leaves that reference unresolved and lets the rest of the document import; the affected pages may lose content whose object could not be reached.

Those non-fatal cases are recorded as document warnings, one per kind of event, with the number of occurrences and the first object affected:

```php
$sourceId = $pdf->setImportSourceFile('/path/to/source.pdf');
$pdf->importPages($sourceId);
$pdf->getOutPDFString();

foreach ($pdf->getWarnings() as $warning) {
    // "The source document 3fa8c1d2 was not fully resolved while parsing: the indirect
    //  object resolution depth limit (64) left a reference unresolved 7 times, first
    //  at object 128_0"
}
```

The warning list is complete only after `getOutPDFString()` has been called, like every other document warning.

To fail instead of degrading, set `strict_limits`:

```php
try {
    $sourceId = $pdf->setImportSourceFile('/path/to/source.pdf', ['strict_limits' => true]);
} catch (\Com\Tecnick\Pdf\Import\ImportResourceLimitException $exc) {
    // the source needs higher limits, or cannot be imported faithfully
}
```

To import a document that legitimately nests deeper than the defaults, raise the limits:

```php
$sourceId = $pdf->setImportSourceFile('/path/to/source.pdf', [
    'max_resolution_depth' => 512,
    'max_nesting_depth'    => 2048,
]);
```

`ImportResourceLimitException` extends `ImportCorruptedSourceException`, so a handler for the latter around source registration also catches limit failures.

## Page Content Streams

A page `/Contents` entry is accepted in every form the specification allows: a stream, an array of streams, or an indirect reference to either. Several streams are decoded and concatenated into the single stream of the resulting Form XObject.

A page whose `/Contents` entry cannot be resolved to any stream is imported as an empty Form XObject and reported as a document warning, so a blank imported page is never silent:

```php
$pdf->importPage($sourceId, 1);
$pdf->getOutPDFString();

foreach ($pdf->getWarnings() as $warning) {
    // "The imported page 1 has a /Contents entry but no content stream could be
    //  extracted: the page will be blank"
}
```

A `/Contents` stream that exists but is empty, and an empty `/Contents` array, are legal empty contents and are not reported.

## Import One Page and Place It

```php
$tpl = $pdf->importPage($sourceId, 1, [
    'box' => 'CropBox',          // MediaBox|CropBox|BleedBox|TrimBox|ArtBox
    'groupXObject' => true,
    'cache' => true,
    'respectRotation' => true,
]);

$pdf->addPage();
$placed = $pdf->useImportedPage($tpl, 20, 20, 120, 80, [
    'keepAspectRatio' => true,
    'align' => 'CC',             // TL|TC|TR|CL|CC|CR|BL|BC|BR
    'clip' => true,
]);
```

## Append Pages from a Source Document

```php
// Append all pages.
$templates = $pdf->appendDocument($sourceId);

// Append only selected pages.
$templates = $pdf->appendDocument($sourceId, [1, 3, 5]);

// Add one imported page sized to the source page.
$tpl = $pdf->addPageFromImport($sourceId, 2);
```

## Import Examples

- Single page import: [examples/E065_import_single_page.php](../examples/E065_import_single_page.php)
- Full document append: [examples/E066_import_document_append.php](../examples/E066_import_document_append.php)
- Advanced N-up composition from imported pages: [examples/E067_import_page_region_nup.php](../examples/E067_import_page_region_nup.php)

## Import Limitations and Fidelity Notes

- Form and annotation semantics are not merged into editable destination structures; pages are imported as Form XObjects.
- Digital signatures in source files are not preserved as valid signatures in the destination output.
- Encrypted source PDFs are not importable with the bundled parser backend. Password-like options are accepted by the import API, but encrypted inputs fail with an explicit exception.
- Multi-stream page contents are normalized by decoding and concatenating the stream bytes; this changes the low-level byte representation while preserving the rendered appearance.
- Transparency-group behavior is conformance-aware: when transparency is disallowed by the active PDF mode (PDF/A-1, PDF/X-1a or PDF/X-3), import suppresses transparency groups to remain compliant.
- Stream filters are conformance-aware: a source stream compressed with `LZWDecode`, which ISO 19005 forbids, is decoded and re-encoded with `FlateDecode`. When the stream cannot be re-encoded (an undecodable filter chain), a PDF/A destination raises `ImportUnsupportedFeatureException` and any other mode keeps the source stream unchanged. Importing a `JPXDecode` stream into a PDF/A-1 document raises the same exception, since ISO 19005-1 does not allow that filter.
- Setting `groupXObject` to `false` can reduce output size, but may change compositing on source pages that rely on transparency blending.
