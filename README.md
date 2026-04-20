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
- **Digital signatures**, signature appearance fields
- **PDF annotations**: links, text notes, file attachments, markup, shapes, media, and widgets
- **JavaScript** embedding
- **PDF/A** (1/2/3, including a/b/u conformance levels) and **PDF/X** support

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

For more complete examples — including invoices, images, barcodes, and HTML tables — see the [examples](examples) directory.

To run the bundled examples locally:

```bash
make x       # build example assets
make server  # start a local PHP server
```

Then open <http://localhost:8971/index.php>.

---

## Development

```bash
# Install all development dependencies
make deps

# List all available Make targets
make help

# Run the full quality pipeline (lint, static analysis, tests, coverage)
make qa
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
