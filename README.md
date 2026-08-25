# tc-lib-pdf (TCPDF)

<img src="resources/img/tcpdf_logo.svg" alt="TCPDF logo" width="150" />

> **The next generation of [TCPDF](https://tcpdf.org)** - a modern, modular PHP library for programmatically generating PDF documents.

[![Latest Stable Version](https://poser.pugx.org/tecnickcom/tc-lib-pdf/version)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
[![Build](https://github.com/tecnickcom/tc-lib-pdf/actions/workflows/check.yml/badge.svg)](https://github.com/tecnickcom/tc-lib-pdf/actions/workflows/check.yml)
[![Coverage](https://codecov.io/gh/tecnickcom/tc-lib-pdf/graph/badge.svg?token=rmAqNKVG1c)](https://codecov.io/gh/tecnickcom/tc-lib-pdf)
[![License](https://poser.pugx.org/tecnickcom/tc-lib-pdf/license)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
[![Downloads](https://poser.pugx.org/tecnickcom/tc-lib-pdf/downloads)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)

[![Sponsor on GitHub](https://img.shields.io/badge/sponsor-github-EA4AAA.svg?logo=githubsponsors&logoColor=white)](https://github.com/sponsors/tecnickcom)

> 💖 **Keep TCPDF maintained.** `tc-lib-pdf` is the actively-developed successor to **TCPDF**, which is installed 100M+ times across 500+ PHP packages and is now maintenance-only. If your company depends on it, [become a sponsor](https://github.com/sponsors/tecnickcom) to keep this shared infrastructure secure and maintained. See [Sponsors](#sponsors) for tiers.

---

## Contents

- [Overview](#overview)
- [Sponsors](#sponsors)
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

`tc-lib-pdf` is a pure-PHP library for generating PDF documents.  
It is the modern evolution of TCPDF, built around a modular package architecture, a Composer-first workflow, and strict PHP types.

It coordinates companion packages for fonts, images, graphics, pages, filtering, encryption, and digital signatures into a single document-authoring API.

| | |
|---|---|
| **Namespace** | `\Com\Tecnick\Pdf` |
| **Author** | Nicola Asuni \<info@tecnick.com\> |
| **License** | [GNU LGPL v3](https://www.gnu.org/copyleft/lesser.html) - see [LICENSE](LICENSE) |
| **Website** | <https://tcpdf.org> |
| **API docs** | <https://tcpdf.org/docs/srcdoc/tc-lib-pdf> |
| **Packagist** | <https://packagist.org/packages/tecnickcom/tc-lib-pdf> |

Releases follow [Semantic Versioning](https://semver.org):

- **PATCH**: backwards-compatible bug fixes
- **MINOR**: backwards-compatible new features
- **MAJOR**: breaking changes

---

## Sponsors

`tc-lib-pdf` is the actively-developed successor to **TCPDF**, which is installed **100M+ times across 500+ PHP packages** and is now maintenance-only. Sponsoring funds the ongoing security and maintenance work on both.

[![Sponsor on GitHub](https://img.shields.io/badge/sponsor-github-EA4AAA.svg?logo=githubsponsors&logoColor=white)](https://github.com/sponsors/tecnickcom)

<!-- sponsors -->
**Your logo here.** Be the first company to back the project: [become a sponsor →](https://github.com/sponsors/tecnickcom)
<!-- sponsors -->

See **[SPONSORS.md](https://github.com/tecnickcom/.github/blob/main/SPONSORS.md)** for sponsorship tiers, how to add your logo, and the logo/content policy. Individual backers are listed in **[BACKERS.md](https://github.com/tecnickcom/.github/blob/main/BACKERS.md)**.

---

## For TCPDF Users

`tc-lib-pdf` is not a drop-in replacement for TCPDF:

- The codebase is split across separate Composer packages instead of a single distribution.
- The API is strongly typed and organized around companion services such as fonts, pages, graphics, and images.
- Setup is Composer-first: asset preparation such as font generation is part of project bootstrap, not a bundled step.

The runnable examples in [examples/index.md](examples/index.md) cover the equivalent of most TCPDF workflows.

## Features

### Text & Fonts
- Full **UTF-8 Unicode** and **right-to-left** (RTL) language support
- **TrueTypeUnicode**, **OpenTypeUnicode v1**, TrueType, OpenType v1, Type1, and CID-0 fonts
- Supplementary-plane characters above U+FFFF, including mathematical alphanumeric symbols and emoji ([doc/FONTS.md](doc/FONTS.md#unicode-text-encoding))
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
- **Per-page transparency group control** via `setPageTransparencyGroup()`: `'auto'` (default) emits the page transparency `/Group` only on pages that use transparency, `'always'` emits it on every page, `'never'` omits it

### Images & Graphics
- Native **JPEG**, **PNG**, and **SVG** support
- Extended image format handling via GD (`GD`, `GD2`, `GD2PART`, `GIF`, `JPEG`, `PNG`, `BMP`, `XBM`, `XPM`, `WBMP`, `TIFF`, `ICO`, `PSD`, `IFF`, `SWC`)
- Geometric graphics and 2D transformations
- Linear and radial gradients, Coons patch mesh gradients, crop marks, and registration bars
- **JPEG and PNG ICC profiles**, grayscale/RGB/CMYK/spot colors, transparencies, and overprint control

### Security & Standards
- Password and certificate-based document encryption (RC4 and AES, up to 256-bit)
- Remote resource controls via `fileOptions` with host allowlists plus separate internal and markup local-path allowlists for external assets
- **Digital signatures**: detached CMS (PKCS#7) and **PAdES baseline** signatures (ETSI EN 319 142-1) through the `signature()` facade, with configurable appearance fields. Profiles: `legacy` (ISO 32000-1 `adbe.pkcs7.detached`), `pades-b-b`, `pades-b-t`, `pades-b-lt`, `pades-b-lta` (`ETSI.CAdES.detached`), with RSA or ECDSA keys and `sha256`/`sha384`/`sha512` digests. Local (private-key) and external/remote (HSM) signing are both supported. The cryptography lives in [`tc-lib-pdf-sign`](https://github.com/tecnickcom/tc-lib-pdf-sign); see [doc/DIGITAL_SIGNATURES.md](doc/DIGITAL_SIGNATURES.md)
- **RFC 3161 TSA timestamps** (PAdES B-T): a timestamp token is embedded in the CMS as the `id-aa-signatureTimeStampToken` attribute, with configurable digest algorithm, policy OID, nonce, timeout, and TLS peer verification. The token is verified and matched against the request before it is embedded
- **LTV (Long-Term Validation)** (PAdES B-LT): revocation evidence in a post-signing incremental revision:
  - collects the signing certificate chain and fetches OCSP responses and CRL payloads from AIA and CDP URLs
  - verifies every response before embedding it, including the certificates carried by the signature timestamp token
  - deduplicates binary payloads by fingerprint
  - emits a Document Security Store (`/DSS`) carrying `/VRI`, `/Certs`, `/OCSPs`, and `/CRLs`, referenced from the re-emitted document catalog
  - OCSP, CRL, cert embedding, DSS, and VRI are each enabled independently through the `signature()` LTV options
- **Archive timestamps** (PAdES B-LTA): `signature()->upgradeToLta()` adds a `/Type /DocTimeStamp` archive timestamp over the whole document in a further incremental revision
- **PDF annotations**: links, text notes, file attachments, markup, shapes, media, and widgets
- **JavaScript** embedding
- **PDF/A** (1/2/3, including a/b/u conformance levels): see [doc/STANDARDS.md](doc/STANDARDS.md) and [E001_invoice.php](examples/E001_invoice.php) for a Factur-X / ZUGFeRD example
- **PDF/X** (generic alias, PDF/X-1a, PDF/X-3, PDF/X-4, PDF/X-5): print-exchange conformance covering per-variant OutputIntent identifiers, GTS_PDFXVersion in Info dict and XMP, PDF version enforcement, CMYK color forcing for restrictive profiles (X-1a, X-3), transparency restrictions, and suppression of encryption and JavaScript
- **PDF/UA** (generic alias, PDF/UA-1, PDF/UA-2): accessibility conformance covering tagged structure tree (`StructTreeRoot` / `ParentTree`), `MarkInfo /Marked true`, document language (`/Lang`), `DisplayDocTitle true`, `ActualText` for ligatures and special glyphs, figure alt-text tagging, and heading-level clamping to prevent skipped levels; PDF/UA-2 targets PDF 2.0

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
- Stream compression via the `zlib` PHP extension, available in every conformance mode

---

## Requirements

- **PHP 8.2** or later
- Composer

Optional PHP extensions: `gd`, `zlib`.

Feature-specific prerequisites:

- Digital signatures, timestamps, and LTV require signing certificates and keys, plus any TSA or revocation endpoint the configuration references.
- `make preflight` runs external validation tools when they are installed.

---

## Installation

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

This example assumes the script lives in the project root; adjust the `autoload.php` and `K_PATH_FONTS` paths otherwise.

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

`getOutPDFString()` returns the raw PDF bytes. `renderPDF()` streams those bytes to the browser; to store them in a file or send them as an attachment, keep the returned string.

> **Note:** `realpath()` returns `false` when the fonts directory does not exist. A `K_PATH_FONTS` error on first run means the fonts have not been generated (see [doc/FONTS.md](doc/FONTS.md)).

---

## Examples

The [examples](examples) directory holds runnable scripts for the supported features.

Starting points:

- [examples/index.md](examples/index.md): index of available examples.
- [examples/E000_overview.php](examples/E000_overview.php): broad feature overview.
- [examples/E006_minimal.php](examples/E006_minimal.php): minimal PDF generation flow.
- [examples/E043_html_tables.php](examples/E043_html_tables.php): HTML table rendering.
- [examples/E027_annotations.php](examples/E027_annotations.php): annotation subtypes.
- [examples/E065_import_single_page.php](examples/E065_import_single_page.php): PDF page import.

Topic groups:

- Document basics (layout, headers/footers, cells, colors, images, text rendering)
- Standards and compliance (PDF/X, PDF/UA, PDF/A workflows)
- Security and signing (encryption, PAdES/PKCS#7 signatures, timestamps, LTV)
- Advanced composition (annotations, templates, layers, page import/reorder)

To run them locally:

```bash
make fonts   # generate the companion fonts used by the examples
make server  # start a local PHP server
```

Then open <http://localhost:8971/E000_overview.php>.

---

## In-Depth Documentation

Focused guides in the `doc/` directory:

- Font setup, custom fonts, and third-party font licenses: [doc/FONTS.md](doc/FONTS.md)
- ICC profile details: [doc/ICC_PROFILE.md](doc/ICC_PROFILE.md)
- PDF import API, examples, and fidelity notes: [doc/PDF_IMPORT.md](doc/PDF_IMPORT.md)
- Remote resources and `fileOptions` (`allowedHosts`, `allowedPaths`, `markupAllowedPaths`, cURL policy): [doc/REMOTE_RESOURCES.md](doc/REMOTE_RESOURCES.md)
- External cache for font subsets and images (`CacheInterface`, selective caching): [doc/CACHE.md](doc/CACHE.md)
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

