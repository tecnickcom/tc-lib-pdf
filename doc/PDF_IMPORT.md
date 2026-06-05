# PDF Import

Back to root overview: [README.md](../README.md#in-depth-documentation)

`tc-lib-pdf` can import pages from existing PDFs as Form XObjects and place them on destination pages.

## Source Registration and Page Count

```php
$sourceId = $pdf->setImportSourceFile('/path/to/source.pdf');
// or: $sourceId = $pdf->setImportSourceData($rawPdfBytes);

$count = $pdf->getSourcePageCount($sourceId);
```

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
- Encrypted source PDFs are currently not importable with the bundled parser backend. Password-like options are accepted by the import API, but encrypted inputs fail with an explicit actionable exception.
- For multi-stream page contents, import normalizes by decoding and concatenating stream bytes; this can change low-level byte representation while preserving rendered appearance in typical cases.
- Transparency-group behavior is conformance-aware: when transparency is disallowed by the active PDF mode (for example PDF/X-1a or PDF/X-3), import suppresses transparency groups to remain compliant.
- Setting `groupXObject` to `false` can reduce output size, but may change compositing on source pages that rely on transparency blending.
