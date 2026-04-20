# tc-lib-pdf

> **The next generation of [TCPDF](https://tcpdf.org)** — a modern, modular PHP library for programmatically generating PDF documents.

[![Latest Stable Version](https://poser.pugx.org/tecnickcom/tc-lib-pdf/version)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
[![Build](https://github.com/tecnickcom/tc-lib-pdf/actions/workflows/check.yml/badge.svg)](https://github.com/tecnickcom/tc-lib-pdf/actions/workflows/check.yml)
[![Coverage](https://codecov.io/gh/tecnickcom/tc-lib-pdf/graph/badge.svg?token=rmAqNKVG1c)](https://codecov.io/gh/tecnickcom/tc-lib-pdf)
[![License](https://poser.pugx.org/tecnickcom/tc-lib-pdf/license)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
[![Downloads](https://poser.pugx.org/tecnickcom/tc-lib-pdf/downloads)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)

[![Donate via PayPal](https://img.shields.io/badge/donate-paypal-87ceeb.svg)](https://www.paypal.com/donate/?hosted_button_id=NZUEC5XS8MFBJ)

If this library saves you time, please consider [supporting its development via PayPal](https://www.paypal.com/donate/?hosted_button_id=NZUEC5XS8MFBJ).

---

## Overview

`tc-lib-pdf` is a pure-PHP library for dynamically generating PDF documents. It is the modern evolution of the widely used TCPDF library, redesigned around a modular package architecture, Composer-first workflow, and strict PHP type safety.

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

## Features

### Text & Fonts
- Full **UTF-8 Unicode** and **right-to-left** (RTL) language support
- **TrueTypeUnicode**, **OpenTypeUnicode v1**, TrueType, OpenType v1, Type1, and CID-0 fonts
- Font subsetting to keep file sizes small
- Text hyphenation, stretching, and letter-spacing (tracking)
- Text rendering modes: fill, stroke, and clipping
- Automatic line breaks, page breaks, and justification

### Layout & Content
- All standard page sizes, custom formats, custom margins, and configurable units of measure
- **HTML** and **CSS** rendering
- **SVG** rendering
- Multi-column layouts and no-write page regions
- Headers, footers, and common page content
- Bookmarks, named destinations, and table of contents
- Automatic page numbering and page groups; move and delete pages

### Images & Graphics
- Native **JPEG**, **PNG**, and **SVG** support
- Extended image support via GD (`GD`, `GD2`, `GD2PART`, `GIF`, `JPEG`, `PNG`, `BMP`, `XBM`, `XPM`)
- Extended image support via [ImageMagick](http://www.imagemagick.org/script/formats.php)
- Geometric graphics and 2D transformations
- **JPEG and PNG ICC profiles**, Grayscale, RGB, CMYK, spot colors, and transparencies

### Security & Standards
- Document **encryption** up to 256-bit AES and **digital signature** certification
- **PDF annotations**: links, text notes, and file attachments
- **JavaScript** embedding
- **PDF/A-1b** conformance support

### Other
- **1D and 2D barcodes** via [`tc-lib-barcode`](https://github.com/tecnickcom/tc-lib-barcode)
- XObject templates and layers with object visibility controls
- Page compression via the `zlib` PHP extension

---

## Requirements

- **PHP 8.1** or later
- Composer

Optional PHP extensions for extended functionality: `gd`, `imagick`, `zlib`.

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

require_once __DIR__ . '/vendor/autoload.php';

$pdf = new \Com\Tecnick\Pdf\Tcpdf();

// Add a page
$pdf->addPage();

// Write some text
$pdf->writeHTML('<h1>Hello, PDF!</h1><p>Generated with tc-lib-pdf.</p>');

// Output to browser
$pdf->getPage();
echo $pdf->getOutPDFString();
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

PHP font metadata files under the fonts directory are covered by the project license (GNU LGPL v3). They contain no binary font payload and can be regenerated with the built-in font utilities.

Original TTF files are renamed for compatibility and compressed with PHP `gzcompress` (`.z` extension).

| Prefix | Source | License |
|--------|--------|---------|
| `free` | [GNU FreeFont](https://www.gnu.org/software/freefont/) | GNU GPL v3 |
| `pdfa` | GNU FreeFont (derived) | GNU GPL v3 |
| `dejavu` | [DejaVu Fonts 2.33](http://dejavu-fonts.org) | Bitstream Vera |
| `ae` | [Arabeyes.org](http://projects.arabeyes.org/) | GNU GPL v2 |

---

## ICC Profile

The bundled `sRGB.icc` profile is sourced from the Debian [`icc-profiles-free`](https://packages.debian.org/source/stable/icc-profiles-free) package.

---

## Contact

Nicola Asuni — <info@tecnick.com>
