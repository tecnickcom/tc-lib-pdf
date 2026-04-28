# tc-lib-pdf

> **The next generation of [TCPDF](https://tcpdf.org)** - a modern, modular PHP library for programmatically generating PDF documents.

[![Latest Stable Version](https://poser.pugx.org/tecnickcom/tc-lib-pdf/version)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
[![Build](https://github.com/tecnickcom/tc-lib-pdf/actions/workflows/check.yml/badge.svg)](https://github.com/tecnickcom/tc-lib-pdf/actions/workflows/check.yml)
[![Coverage](https://codecov.io/gh/tecnickcom/tc-lib-pdf/graph/badge.svg?token=rmAqNKVG1c)](https://codecov.io/gh/tecnickcom/tc-lib-pdf)
[![License](https://poser.pugx.org/tecnickcom/tc-lib-pdf/license)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
[![Downloads](https://poser.pugx.org/tecnickcom/tc-lib-pdf/downloads)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)

[![Donate via PayPal](https://img.shields.io/badge/donate-paypal-87ceeb.svg)](https://www.paypal.com/donate/?hosted_button_id=NZUEC5XS8MFBJ)

If this library saves you time, please consider [supporting its development via PayPal](https://www.paypal.com/donate/?hosted_button_id=NZUEC5XS8MFBJ).

---

## Overview

`tc-lib-pdf` is a pure-PHP library for dynamically generating PDF documents.  
It is the modern evolution of the widely used TCPDF library, redesigned around a modular package architecture, Composer-first workflow, and strict PHP type safety.

It coordinates specialized companion packages for fonts, images, graphics, pages, filtering, and encryption into a cohesive document-authoring API. The result is a production-ready toolkit for invoices, reports, labels, and other generated PDFs where predictable output and long-term maintainability matter.

| | |
|---|---|
| **Namespace** | `\Com\Tecnick\Pdf` |
| **Author** | Nicola Asuni \<info@tecnick.com\> |
| **License** | [GNU LGPL v3](https://www.gnu.org/copyleft/lesser.html) — see [LICENSE](LICENSE) |
| **Website** | <https://tcpdf.org> |
| **API docs** | <https://tcpdf.org/docs/srcdoc/tc-lib-pdf> |
| **Packagist** | <https://packagist.org/packages/tecnickcom/tc-lib-pdf> |

Releases follow [Semantic Versioning](https://semver.org):

- **PATCH** — backwards-compatible bug fixes
- **MINOR** — backwards-compatible new features
- **MAJOR** — breaking changes

---

## Description

`tc-lib-pdf` is a modern PDF generation library for PHP applications that need deterministic document output without delegating rendering to an external service. It builds on the long-standing ideas behind TCPDF, but reshapes them into a cleaner, Composer-native package designed for contemporary PHP development.

For teams already familiar with TCPDF, this project offers a more modular foundation with stronger typing, clearer package boundaries, and an API surface intended to be easier to maintain in large codebases. For teams adopting it fresh, it provides a practical way to generate production PDFs directly from PHP while retaining fine-grained control over layout, typography, graphics, metadata, and standards-oriented output.

The library is particularly well suited to backend-driven document workflows such as invoices, shipping labels, statements, certificates, reports, and archived business records. Instead of treating PDF generation as an opaque export step, `tc-lib-pdf` exposes the document model in a way that lets developers compose pages programmatically, integrate with existing application data, and keep output logic versioned alongside the rest of the codebase.

Because it is part of the broader `tc-lib-*` ecosystem, `tc-lib-pdf` can coordinate fonts, images, page geometry, graphics primitives, and optional features such as barcodes and encryption through dedicated companion packages. That modular design makes the project easier to evolve over time while still delivering the all-in-one capabilities PHP developers expect from a serious PDF engine.

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
- **Digital signatures** — detached CMS (PKCS#7) signatures with configurable appearance fields
- **RFC 3161 TSA timestamps** — embed a trusted timestamp token from any RFC 3161-compliant Time Stamping Authority (TSA) into the CMS signature; configurable digest algorithm (`sha256`, `sha384`, `sha512`), policy OID, nonce, timeout, and TLS peer verification
- **LTV (Long-Term Validation)** — embed revocation evidence in the same PDF revision as the signature:
  - collects the signing certificate chain and fetches OCSP responses and/or CRL payloads from AIA and CDP URLs
  - deduplicates binary payloads by fingerprint
  - emits `/DSS`, `/VRI`, `/OCSPs`, `/CRLs`, and `/Certs` objects in the catalog
  - each feature (OCSP, CRL, cert embedding, DSS, VRI) can be enabled independently via `setSignature()` LTV options
- **PDF annotations**: links, text notes, file attachments, markup, shapes, media, and widgets
- **JavaScript** embedding
- **PDF/A** (1/2/3, including a/b/u conformance levels) — see [Factur-X / ZUGFeRD](#other) below
- **PDF/X** (generic alias, PDF/X-1a, PDF/X-3, PDF/X-4, PDF/X-5) — print-exchange conformance: per-variant OutputIntent identifiers, GTS_PDFXVersion in Info dict and XMP, PDF version enforcement, CMYK color forcing for restrictive profiles (X-1a, X-3), transparency restrictions, and suppression of encryption and JavaScript
- **PDF/UA** (generic alias, PDF/UA-1, PDF/UA-2) — accessibility conformance: tagged structure tree (`StructTreeRoot` / `ParentTree`), `MarkInfo /Marked true`, document language (`/Lang`), `DisplayDocTitle true`, `ActualText` for ligatures and special glyphs, figure alt-text tagging, and heading-level clamping to prevent skipped levels; PDF/UA-2 targets PDF 2.0

### Other
- **1D and 2D barcodes** via [`tc-lib-barcode`](https://github.com/tecnickcom/tc-lib-barcode)
- Interactive AcroForm fields (buttons, checkboxes, radio buttons, text, combo boxes, list boxes)
- XObject templates and layers with object visibility controls
- Multiple output targets: inline display, forced download, file save, and MIME attachment
- Factur-X / ZUGFeRD workflows via embedded XML in PDF/A-3 documents
- Page compression via the `zlib` PHP extension

---

## Requirements

- **PHP 8.1** or later
- Composer

Optional PHP extensions for extended functionality: `gd`, `zlib`.

---

## Installation

```bash
composer require tecnickcom/tc-lib-pdf
```

Or add to your `composer.json`:

```json
{
    "require": {
        "tecnickcom/tc-lib-pdf": "^8.6"
    }
}
```

---

## Font Setup

When you install `tc-lib-pdf` as a dependency in your project (via `composer require` or `composer install`), the fonts from the companion package [`tc-lib-pdf-font`](https://github.com/tecnickcom/tc-lib-pdf-font) must be generated before they can be used.

Since Composer does not execute dependency scripts during installation, you need to add the font generation step to your **consuming project's** `composer.json` file:

```json
{
  "scripts": {
    "tc-lib-pdf-fonts": [
      "[ -d vendor/tecnickcom/tc-lib-pdf-font ] && make -C vendor/tecnickcom/tc-lib-pdf-font deps fonts || true"
    ],
    "post-install-cmd": [
      "@tc-lib-pdf-fonts"
    ],
    "post-update-cmd": [
      "@tc-lib-pdf-fonts"
    ]
  }
}
```

This ensures fonts are generated automatically when you run:

```bash
composer install
composer update
composer require ...
```

If you prefer to generate fonts manually, run the build in the `tc-lib-pdf-font` package:

```bash
cd vendor/tecnickcom/tc-lib-pdf-font
make deps fonts
```

Equivalent one-liner from your project root:

```bash
make -C vendor/tecnickcom/tc-lib-pdf-font deps fonts
```

Once fonts are generated, they are cached in `vendor/tecnickcom/tc-lib-pdf-font/target/fonts/` and will not be regenerated unless explicitly rebuilt.

You can also add your own fonts and generate their PHP font data with `tc-lib-pdf-font`. For shared or immutable environments, generate them once into a persistent directory you control (outside `vendor/`) and point `K_PATH_FONTS` to that location.

```php
\define('K_PATH_FONTS', '/opt/app/fonts/tc-lib-pdf');
```

This avoids regenerating fonts on every dependency reinstall and lets multiple deployments reuse the same prepared font set.

---

## Quick Start

```php
<?php

require(__DIR__ . '/../vendor/autoload.php');

\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);

$page = $pdf->addPage();

$pdf->page->addContent($bfont['out']);

$html = '<h1>Hello, PDF!</h1><p>Generated with tc-lib-pdf.</p>';

$pdf->addHTMLCell($html, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF($rawpdf);
```

For more complete examples — including invoices, images, barcodes, HTML tables, dedicated HTML selector/form/table showcases, PDF/X, and PDF/UA — see the [examples](examples) directory.
Annotation-focused runnable example: [examples/027_example_annotations.php](examples/027_example_annotations.php).

To run the bundled examples locally:

```bash
make x       # build example assets
make server  # start a local PHP server
```

Then open <http://localhost:8971/index.php>.

---

## Digital Signatures

`tc-lib-pdf` supports detached CMS (PKCS#7) signatures with optional RFC 3161 timestamps and LTV (Long-Term Validation) material, all embedded in a single PDF revision.

Signature-focused runnable examples:

- [examples/007_example_signature_basic.php](examples/007_example_signature_basic.php) : basic detached CMS signature with visible signature fields.
- [examples/008_example_signature_timestamp.php](examples/008_example_signature_timestamp.php) : detached CMS signature with RFC 3161 TSA timestamp configuration.
- [examples/009_example_signature_ltv.php](examples/009_example_signature_ltv.php) : detached CMS signature with LTV validation material embedding.

### Basic signature

```php
$pdf->setSignature([
    'signcert'  => 'file:///path/to/cert.pem',
    'privkey'   => 'file:///path/to/key.pem',
    'password'  => '',
    'cert_type' => 2,
    'info'      => [
        'Name'        => 'Jane Smith',
        'Location'    => 'London',
        'Reason'      => 'Document approval',
        'ContactInfo' => 'jane@example.com',
    ],
]);
```

### Adding a TSA timestamp (RFC 3161)

Call `setSignTimeStamp()` after `setSignature()` to request a timestamp token from a trusted TSA and embed it in the CMS unsigned attributes:

```php
$pdf->setSignTimeStamp([
    'enabled'        => true,
    'host'           => 'https://freetsa.org/tsr',
    'username'       => '',
    'password'       => '',
    'cert'           => '',
    'hash_algorithm' => 'sha256',   // sha256 | sha384 | sha512
    'policy_oid'     => '',         // optional OID string
    'nonce_enabled'  => true,
    'timeout'        => 30,
    'verify_peer'    => true,
]);
```

### LTV (Long-Term Validation)

Enable LTV via the `ltv` key inside `setSignature()`. The library fetches OCSP responses and CRL payloads from the certificate's AIA and CDP extensions and writes them into the same PDF revision as the signature:

```php
$pdf->setSignature([
    'signcert' => 'file:///path/to/cert.pem',
    'privkey'  => 'file:///path/to/key.pem',
    'password' => '',
    'ltv'      => [
        'enabled'     => true,
        'embed_ocsp'  => true,   // fetch OCSP responses
        'embed_crl'   => true,   // fetch CRL payloads (fallback)
        'embed_certs' => true,   // include certificate DER bytes
        'include_dss' => true,   // emit /DSS in catalog
        'include_vri' => true,   // emit /VRI map keyed by cert SHA-1
    ],
]);
```

When LTV is enabled the output PDF contains `/DSS`, `/VRI`, `/OCSPs`, `/CRLs`, and `/Certs` objects, making Adobe-style long-term signature validation feasible without any external retrieval at verification time.

### Generating a self-signed test certificate

```bash
openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
    -keyout tcpdf.key -out tcpdf.crt
# convert to PKCS#12 if needed
openssl pkcs12 -export -in tcpdf.crt -inkey tcpdf.key -out tcpdf.p12
```

---

## PDF/X Conformance

`tc-lib-pdf` supports multiple PDF/X profiles for print-exchange workflows. Pass the mode string as the fifth argument to the `Tcpdf` constructor:

```php
// Generic PDF/X alias (maps to the library's baseline print-exchange workflow)
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx');

// Specific variants
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx1a'); // PDF/X-1a:2003
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx3');  // PDF/X-3:2003
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx4');  // PDF/X-4:2010
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx5');  // PDF/X-5g:2010
```

Each variant automatically applies the appropriate conformance constraints:

| Mode | Min PDF version | Transparency | Process colors | GTS_PDFXVersion |
|------|-----------------|--------------|----------------|-----------------|
| `pdfx` / `pdfx3` | 1.3 | blocked | CMYK forced | PDF/X-3:2003 |
| `pdfx1a` | 1.3 | blocked | CMYK forced | PDF/X-1a:2003 |
| `pdfx4` | 1.6 | allowed | unrestricted | PDF/X-4:2010 |
| `pdfx5` | 1.6 | allowed | unrestricted | PDF/X-5g:2010 |

All PDF/X modes suppress encryption and JavaScript (not permitted by the ISO 15930 standard).

Runnable examples: [examples/010_example_pdfx.php](examples/010_example_pdfx.php) through [examples/014_example_pdfx5.php](examples/014_example_pdfx5.php).

---

## PDF/UA Accessibility

`tc-lib-pdf` supports tagged PDF output conforming to PDF/UA (ISO 14289). Pass the mode string as the fifth argument to the `Tcpdf` constructor:

```php
// Generic PDF/UA alias
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfua');

// Specific parts
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfua1'); // PDF/UA-1 (PDF 1.7)
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfua2'); // PDF/UA-2 (PDF 2.0)
```

When a PDF/UA mode is active the library automatically:

- Writes a `StructTreeRoot` with a `ParentTree` that maps every page to its tagged content blocks
- Emits `MarkInfo << /Marked true >>` in the document catalog
- Sets `/Lang` (defaults to `en-US` when not explicitly provided)
- Forces `ViewerPreferences /DisplayDocTitle true`
- Maps HTML heading elements (`h1`–`h6`) to PDF structure roles `H1`–`H6` with level-clamping to prevent skipped heading levels
- Tags text content with MCIDs and wraps each run in the appropriate structure element (`P`, `H1`–`H6`, `Link`, etc.)
- Tags `<img>` elements as `Figure` with their `alt` attribute written as `/Alt` in the structure element
- Emits `ActualText` entries for ligatures and special glyphs so text extraction and screen readers work correctly

To provide the document language explicitly:

```php
$pdf->setDocInfo(['a_meta_language' => 'de-DE']);
```

Runnable examples: [examples/015_example_pdfua.php](examples/015_example_pdfua.php) through [examples/017_example_pdfua2.php](examples/017_example_pdfua2.php).

---

## Development

```bash
# Install all development dependencies
make deps

# List all available Make targets
make help

# Run the full quality pipeline (lint, static analysis, tests, coverage)
make qa

# Generate PDF/X + PDF/UA sample matrix and run external validators (if installed)
make preflight
```

Build artifacts and reports are written to the `target/` directory.

---

## Packaging

The primary distribution channel is Composer. For system-level deployments, RPM and DEB packages are also provided.

```bash
make rpm   # build RPM package  → target/RPM/
make deb   # build DEB package  → target/DEB/
```

When using the RPM or DEB package, bootstrap the library with its system autoloader:

```php
require_once '/usr/share/php/Com/Tecnick/Pdf/autoload.php';
```

---

## Contributing

Contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) and [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) before submitting a pull request.

1. Fork the repository and create a feature branch.
2. Write or update tests for your change.
3. Run `make qa` to ensure the full pipeline passes.
4. Open a pull request with a clear description of the change.

Security vulnerabilities should be reported according to [SECURITY.md](SECURITY.md).

---

## Third-Party Fonts

PHP font metadata files under the fonts directory are covered by the project license (GNU LGPL v3). They can be regenerated with the built-in font utilities.

Original source files are renamed for compatibility and compressed with PHP `gzcompress` (`.z` extension) where applicable.

| Prefix | Source | License |
|--------|--------|---------|
| `freefont` | [GNU FreeFont](https://ftp.gnu.org/gnu/freefont/freefont-ttf-20120503.zip) | GNU GPL v3 |
| `pdfa` | [tc-font-pdfa](https://github.com/tecnickcom/tc-font-pdfa) (derived from GNU FreeFont) | GNU GPL v3 |
| `dejavu` | [DejaVu Fonts 2.35](https://sourceforge.net/projects/dejavu/files/dejavu/2.35/dejavu-fonts-ttf-2.35.zip) | Bitstream Vera (with DejaVu public-domain changes) |
| `unifont` | [GNU Unifont 15.1.03](https://www.unifoundry.com/pub/unifont/unifont-15.1.03/unifont-15.1.03.tar.gz) | GPL v2+ with font embedding exception (also distributed under SIL OFL 1.1) |
| `cid0` | [GNU Unifont](http://unifoundry.com/unifont.html) (CID mappings) | GPL v2+ with font embedding exception |
| `core` | [Adobe Core14 AFM](https://partners.adobe.com/public/developer/en/pdf/Core14_AFMs.zip) | Adobe copyright terms (see AFM notices) |

---

## ICC Profile

The bundled `sRGB.icc` profile is sourced from the Debian [`icc-profiles-free`](https://packages.debian.org/source/stable/icc-profiles-free) package.

---

## Contact

Nicola Asuni — <info@tecnick.com>
