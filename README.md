# tc-lib-pdf

PHP PDF library.

[![Latest Stable Version](https://poser.pugx.org/tecnickcom/tc-lib-pdf/version)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
![Build](https://github.com/tecnickcom/tc-lib-pdf/actions/workflows/check.yml/badge.svg)
[![Coverage](https://codecov.io/gh/tecnickcom/tc-lib-pdf/graph/badge.svg?token=rmAqNKVG1c)](https://codecov.io/gh/tecnickcom/tc-lib-pdf)
[![License](https://poser.pugx.org/tecnickcom/tc-lib-pdf/license)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
[![Downloads](https://poser.pugx.org/tecnickcom/tc-lib-pdf/downloads)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)

[![Donate via PayPal](https://img.shields.io/badge/donate-paypal-87ceeb.svg)](https://www.paypal.com/donate/?hosted_button_id=NZUEC5XS8MFBJ)
Please consider supporting this project by making a donation via [PayPal](https://www.paypal.com/donate/?hosted_button_id=NZUEC5XS8MFBJ).

This package is the current evolution of TCPDF, focused on modern PHP workflows and modular library design.

## At a glance

- Category: Library
- Package namespace: `\Com\Tecnick\Pdf`
- Author: Nicola Asuni <info@tecnick.com>
- Copyright: 2002-2026 Nicola Asuni - Tecnick.com LTD
- License: [GNU LGPL v3](https://www.gnu.org/copyleft/lesser.html) (see [LICENSE](LICENSE))
- Project website: <https://tcpdf.org>
- Source: <https://github.com/tecnickcom/tc-lib-pdf>
- API docs: <https://tcpdf.org/docs/srcdoc/tc-lib-pdf>

## Description

`tc-lib-pdf` is a PHP library for dynamically generating PDF documents.

The first fully stable release was `8.1.0`. Releases follow semantic versioning:

- PATCH: backwards-compatible bug fixes
- MINOR: backwards-compatible feature additions
- MAJOR: breaking changes

For details, see <https://semver.org>.

## Main features

- All standard page formats, custom page formats, custom margins, and units of measure
- UTF-8 Unicode and right-to-left language support
- TrueTypeUnicode, OpenTypeUnicode v1, TrueType, OpenType v1, Type1, and CID-0 fonts
- Font subsetting
- SVG support
- CSS support
- HTML support [WIP] - almost there! needs some extra testing and fixes
- JavaScript support
- Images, geometric graphics, and transformation methods
- Native JPEG, PNG, and SVG support
- Image support via GD (`GD`, `GD2`, `GD2PART`, `GIF`, `JPEG`, `PNG`, `BMP`, `XBM`, `XPM`)
- Image support via ImageMagick (<http://www.imagemagick.org/script/formats.php>)
- 1D and 2D barcodes via `tc-lib-barcode`
- JPEG and PNG ICC profiles, Grayscale, RGB, CMYK, spot PDFs, and transparencies
- Page common content support (header/footer)
- Document encryption up to 256-bit and digital signature certifications
- PDF annotations, including links, text, and file attachments
- Text rendering modes (fill, stroke, and clipping)
- Multiple-column mode
- No-write page regions
- Bookmarks, named destinations, and table of contents
- Text hyphenation
- Text stretching and spacing (tracking)
- Automatic page breaks, line breaks, and text alignment including justification
- Automatic page numbering and page groups
- Move and delete pages
- Page compression (requires the `zlib` PHP extension)
- XObject templates
- Layers and object visibility
- PDF/A-1b support

## Installation

Install from Packagist:

```bash
composer require tecnickcom/tc-lib-pdf
```

Or in `composer.json`:

```json
{
    "require": {
        "tecnickcom/tc-lib-pdf": "^8.5"
    }
}
```

## Quick start

Working example scripts are available in the [examples](examples) directory.

To run examples locally (requires PHP 8.1+):

```bash
make x
make server
```

Then open <http://localhost:8971/index.php>.

## Development

Install development dependencies:

```bash
make deps
```

Show all Make targets:

```bash
make help
```

Run the full quality pipeline before committing:

```bash
make qa
```

Generated artifacts are written to the `target` directory.

## Packaging

The primary distribution channel is Composer, but RPM and DEB packages are also supported for system-level deployment workflows.

Build packages:

```bash
make rpm
make deb
```

Packages are generated under `target`.

When installed from RPM/DEB, include the library autoloader:

```php
require_once '/usr/share/php/Com/Tecnick/Pdf/autoload.php';
```

## Third-party fonts and licenses

This library may include third-party font files under different licenses.

All PHP files under the fonts directory are covered by the project license (GNU LGPL v3). They contain font metadata only and no binary font payload. These files can also be generated dynamically with the font utilities and TCPDF methods.

Original binary TTF files are renamed for compatibility and compressed with PHP `gzcompress` into `.z` files.

License sources:

- Prefix `free`: extracted from GNU FreeFont (GNU GPL v3)
- Prefix `pdfa`: derived from GNU FreeFont (GNU GPL v3)
- Prefix `dejavu`: extracted from DejaVu fonts 2.33 (Bitstream)
- Prefix `ae`: extracted from Arabeyes.org collection (GNU GPL v2)

References:

- GNU FreeFont: <https://www.gnu.org/software/freefont/>
- DejaVu: <http://dejavu-fonts.org>
- Arabeyes: <http://projects.arabeyes.org/>

## ICC profile

This project includes the `sRGB.icc` profile from the Debian `icc-profiles-free` package:
<https://packages.debian.org/source/stable/icc-profiles-free>

## Contact

- Nicola Asuni <info@tecnick.com>
