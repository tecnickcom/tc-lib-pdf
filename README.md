# tc-lib-pdf (TCPDF)

<img src="resources/img/tcpdf_logo.svg" alt="TCPDF logo" width="150" />

> **The next generation of [TCPDF](https://tcpdf.org)** - a modern, modular PHP library for programmatically generating PDF documents.

[![Latest Stable Version](https://poser.pugx.org/tecnickcom/tc-lib-pdf/version)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
[![Build](https://github.com/tecnickcom/tc-lib-pdf/actions/workflows/check.yml/badge.svg)](https://github.com/tecnickcom/tc-lib-pdf/actions/workflows/check.yml)
[![Coverage](https://codecov.io/gh/tecnickcom/tc-lib-pdf/graph/badge.svg?token=rmAqNKVG1c)](https://codecov.io/gh/tecnickcom/tc-lib-pdf)
[![License](https://poser.pugx.org/tecnickcom/tc-lib-pdf/license)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
[![Downloads](https://poser.pugx.org/tecnickcom/tc-lib-pdf/downloads)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)

[![Sponsor on GitHub](https://img.shields.io/badge/sponsor-github-EA4AAA.svg?logo=githubsponsors&logoColor=white)](https://github.com/sponsors/tecnickcom)

If this project is useful to you, please consider [supporting development via GitHub Sponsors](https://github.com/sponsors/tecnickcom).

---

## Contents

- [Overview](#overview)
- [For TCPDF Users](#for-tcpdf-users)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Examples](#examples)
- [In-Depth Documentation](#in-depth-documentation)
- [Contributing](#contributing)

---

## Overview

`tc-lib-pdf` is a pure-PHP library for dynamically generating PDF documents.  
It is the modern evolution of the widely used TCPDF library, redesigned around a modular package architecture, Composer-first workflow, and strict PHP type safety.

It coordinates specialized companion packages for fonts, images, graphics, pages, filtering, and encryption into a cohesive document-authoring API. The result is a production-ready toolkit for invoices, reports, labels, and other generated PDFs where predictable output and long-term maintainability matter.

| | |
|---|---|
| **Namespace** | `\Com\Tecnick\Pdf` |
| **Author** | Nicola Asuni \<info@tecnick.com\> |
| **License** | [GNU LGPL v3](https://www.gnu.org/copyleft/lesser.html) - see [LICENSE](LICENSE) |
| **Website** | <https://tcpdf.org> |
| **API docs** | <https://tcpdf.org/docs/srcdoc/tc-lib-pdf> |
| **Packagist** | <https://packagist.org/packages/tecnickcom/tc-lib-pdf> |

Releases follow [Semantic Versioning](https://semver.org):

- **PATCH** — backwards-compatible bug fixes
- **MINOR** — backwards-compatible new features
- **MAJOR** — breaking changes

---

## For TCPDF Users

If you already know TCPDF, `tc-lib-pdf` will feel familiar in purpose but it is not positioned as a drop-in replacement.

- The codebase is split across focused Composer packages instead of a single monolithic distribution.
- The API surface is more strongly typed and organized around companion services such as fonts, pages, graphics, and images.
- Setup is Composer-first, which means asset preparation such as font generation is part of project bootstrap rather than an implicit bundled step.

The fastest way to evaluate the library is to follow the installation and quick-start steps below, then compare the runnable examples in [examples/index.md](examples/index.md) with the equivalent workflows you already maintain in TCPDF.

## Features

### Text & Fonts
- Full **UTF-8 Unicode** and **right-to-left** (RTL) language support
- **TrueTypeUnicode**, **OpenTypeUnicode v1**, TrueType, OpenType v1, Type1, and CID-0 fonts
- Font subsetting to keep file sizes small
- Text hyphenation, stretching, and letter-spacing (tracking)
- Language-aware TeX hyphenation patterns and optional zero-width breakpoints
- Text rendering modes: fill, stroke, and clipping
- Automatic line breaks, page breaks, and justification

### Layout & Content
- All standard page sizes, custom formats, custom margins, and configurable units of measure
- **HTML** and **CSS** rendering
- **SVG** rendering
- Multi-column layouts and no-write page regions
- Headers, footers, and common page content
- Bookmarks, named destinations, and table of contents
- Automatic page numbering and page groups
- Full page box control (Media/Crop/Bleed/Trim/Art), page reordering, and viewer preferences

### Images & Graphics
- Native **JPEG**, **PNG**, and **SVG** support
- Extended image format handling via GD (`GD`, `GD2`, `GD2PART`, `GIF`, `JPEG`, `PNG`, `BMP`, `XBM`, `XPM`, `WBMP`, `TIFF`, `ICO`, `PSD`, `IFF`, `SWC`)
- Geometric graphics and 2D transformations
- Linear and radial gradients, Coons patch mesh gradients, crop marks, and registration bars
- **JPEG and PNG ICC profiles**, grayscale/RGB/CMYK/spot colors, transparencies, and overprint control

### Security & Standards
- Password and certificate-based document encryption (RC4 and AES, up to 256-bit)
- Remote resource controls via `fileOptions` with host allowlists plus separate internal and markup local-path allowlists for external assets
- **Digital signatures** — detached CMS (PKCS#7) signatures with configurable appearance fields
- **RFC 3161 TSA timestamps** — embed a trusted timestamp token from any RFC 3161-compliant Time Stamping Authority (TSA) into the CMS signature; configurable digest algorithm (`sha256`, `sha384`, `sha512`), policy OID, nonce, timeout, and TLS peer verification
- **LTV (Long-Term Validation)** — embed revocation evidence in the same PDF revision as the signature:
  - collects the signing certificate chain and fetches OCSP responses and/or CRL payloads from AIA and CDP URLs
  - deduplicates binary payloads by fingerprint
  - emits `/DSS`, `/VRI`, `/OCSPs`, `/CRLs`, and `/Certs` objects in the catalog
  - each feature (OCSP, CRL, cert embedding, DSS, VRI) can be enabled independently via `setSignature()` LTV options
- **PDF annotations**: links, text notes, file attachments, markup, shapes, media, and widgets
- **JavaScript** embedding
- **PDF/A** (1/2/3, including a/b/u conformance levels) — see [doc/STANDARDS.md](doc/STANDARDS.md) and [E001_invoice.php](examples/E001_invoice.php) for a Factur-X / ZUGFeRD example
- **PDF/X** (generic alias, PDF/X-1a, PDF/X-3, PDF/X-4, PDF/X-5) — print-exchange conformance: per-variant OutputIntent identifiers, GTS_PDFXVersion in Info dict and XMP, PDF version enforcement, CMYK color forcing for restrictive profiles (X-1a, X-3), transparency restrictions, and suppression of encryption and JavaScript
- **PDF/UA** (generic alias, PDF/UA-1, PDF/UA-2) — accessibility conformance: tagged structure tree (`StructTreeRoot` / `ParentTree`), `MarkInfo /Marked true`, document language (`/Lang`), `DisplayDocTitle true`, `ActualText` for ligatures and special glyphs, figure alt-text tagging, and heading-level clamping to prevent skipped levels; PDF/UA-2 targets PDF 2.0

### PDF Import
- Import pages from existing PDFs as **Form XObjects** and place them on any destination page
- Import a single page at a user-defined position and scale (`importPage` / `useImportedPage`)
- Append full documents page-by-page, auto-sized to the source page dimensions (`addPageFromImport`)
- Load source PDFs from a file path or raw byte string (`setImportSourceFile` / `setImportSourceData`)

### Other
- **1D and 2D barcodes** via [`tc-lib-barcode`](https://github.com/tecnickcom/tc-lib-barcode)
- Interactive AcroForm fields (buttons, checkboxes, radio buttons, text, combo boxes, list boxes)
- XObject templates and layers with object visibility controls
- Multiple output targets: inline display, forced download, file save, and MIME attachment
- Factur-X / ZUGFeRD workflows via embedded XML in PDF/A-3 documents
- Page compression via the `zlib` PHP extension

---

## Requirements

- **PHP 8.2** or later
- Required PHP extensions: `date`, `pcre` (enforced by Composer)
- Composer

Optional PHP extensions for extended functionality: `gd`, `zlib`.

Feature-specific prerequisites:

- Digital signatures, timestamps, and LTV workflows require signing certificates/keys and any external TSA or revocation endpoints your configuration references.
- `make preflight` depends on external validation tools when you want standards validation beyond the built-in sample generation.

---

## Installation

For a clean first run:

1. Install the package with Composer.
2. Generate companion font files (see [doc/FONTS.md](doc/FONTS.md)).
3. Run the minimal script using the generated `K_PATH_FONTS` path.

```bash
composer require tecnickcom/tc-lib-pdf
```

Or add to your `composer.json`:

```json
{
    "require": {
        "tecnickcom/tc-lib-pdf": "^8"
    }
}
```

---

## Quick Start

The following example assumes the script lives in your project root. If you place it elsewhere, adjust the `autoload.php` and `K_PATH_FONTS` paths accordingly.

```php
<?php

require(__DIR__ . '/vendor/autoload.php');

\define('K_PATH_FONTS', \realpath(__DIR__ . '/vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);

$page = $pdf->addPage();

$pdf->page->addContent($bfont['out']);

$html = '<h1>Hello, PDF!</h1><p>Generated with tc-lib-pdf.</p>';

$pdf->addHTMLCell(
    html:   $html,
    posx:   15,   // mm from left page edge
    posy:   20,   // mm from top page edge
    width:  180,  // mm wide (0 = to right margin)
);

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF($rawpdf);
```

`getOutPDFString()` returns the raw PDF bytes. `renderPDF()` streams those bytes to the browser; if you need file storage or an email attachment, keep the returned string and write or hand it off yourself.

> **Note:** `realpath()` returns `false` when the fonts directory does not yet exist. If you see `K_PATH_FONTS` errors on first run, verify that fonts were generated after `composer install` (see [doc/FONTS.md](doc/FONTS.md)).

For more complete examples — including invoices, images, barcodes, HTML tables, dedicated HTML selector/form/table showcases, PDF/X, and PDF/UA — see the [examples](examples) directory.
Annotation-focused runnable example: [examples/E027_annotations.php](examples/E027_annotations.php).

To run the bundled examples locally:

```bash
make x       # build example assets
make server  # start a local PHP server
```

Then open <http://localhost:8971/index.php>.

If the minimal example fails on first run, verify these two points first:

- `K_PATH_FONTS` resolves to an existing generated font directory.

- The companion fonts were generated after `composer install` or `composer update`.

---

## Examples

The [examples](examples) directory is the fastest way to understand supported features and integration patterns in runnable form.

Useful starting points:

- [examples/index.md](examples/index.md): index of available examples.
- [examples/E000_overview.php](examples/E000_overview.php): broad feature overview.
- [examples/E006_minimal.php](examples/E006_minimal.php): minimal PDF generation flow.
- [examples/E043_html_tables.php](examples/E043_html_tables.php): HTML table rendering.
- [examples/E065_import_single_page.php](examples/E065_import_single_page.php): PDF page import.

Selected topic groups in the examples set:

- Document basics (layout, headers/footers, cells, colors, images, text rendering)
- Standards and compliance (PDF/X, PDF/UA, PDF/A workflows)
- Security and signing (encryption, signatures, timestamps, LTV)
- Advanced composition (annotations, templates, layers, page import/reorder)

To preview examples locally:

```bash
make x
make server
```

Then open <http://localhost:8971/index.php>.

---

## In-Depth Documentation

For implementation details, compliance guidance, operational workflows, and advanced usage patterns, see the focused guides in the `doc/` directory:

- Font setup, custom fonts, and third-party font licenses: [doc/FONTS.md](doc/FONTS.md)
- ICC profile details: [doc/ICC_PROFILE.md](doc/ICC_PROFILE.md)
- PDF import API, examples, and fidelity notes: [doc/PDF_IMPORT.md](doc/PDF_IMPORT.md)
- Remote resources and `fileOptions` (`allowedHosts`, `allowedPaths`, `markupAllowedPaths`, cURL policy): [doc/REMOTE_RESOURCES.md](doc/REMOTE_RESOURCES.md)
- Digital signatures, TSA timestamps, and LTV: [doc/DIGITAL_SIGNATURES.md](doc/DIGITAL_SIGNATURES.md)
- PDF/A, PDF/X, and PDF/UA conformance modes: [doc/STANDARDS.md](doc/STANDARDS.md)
- Development, QA, preflight, and packaging workflows: [doc/DEVELOPMENT.md](doc/DEVELOPMENT.md)

---

## Contributing

Contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) and [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) before submitting a pull request.

1. Fork the repository and create a feature branch.
2. Write or update tests for your change.
3. Run `make qa` to ensure the full pipeline passes.
4. Open a pull request with a clear description of the change.

Security vulnerabilities should be reported according to [SECURITY.md](SECURITY.md).

