# tc-lib-pdf
*PHP PDF Library*

[![Latest Stable Version](https://poser.pugx.org/tecnickcom/tc-lib-pdf/version)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
![Build](https://github.com/tecnickcom/tc-lib-pdf/actions/workflows/check.yml/badge.svg)
[![Coverage](https://codecov.io/gh/tecnickcom/tc-lib-pdf/graph/badge.svg?token=rmAqNKVG1c)](https://codecov.io/gh/tecnickcom/tc-lib-pdf)
[![License](https://poser.pugx.org/tecnickcom/tc-lib-pdf/license)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
[![Downloads](https://poser.pugx.org/tecnickcom/tc-lib-pdf/downloads)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)

[![Donate via PayPal](https://img.shields.io/badge/donate-paypal-87ceeb.svg)](https://www.paypal.com/donate/?hosted_button_id=NZUEC5XS8MFBJ)
*Please consider supporting this project by making a donation via [PayPal](https://www.paypal.com/donate/?hosted_button_id=NZUEC5XS8MFBJ)*

* **category**    Library
* **package**     \Com\Tecnick\Pdf
* **author**      Nicola Asuni <info@tecnick.com>
* **copyright**   2002-2025 Nicola Asuni - Tecnick.com LTD
* **license**     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
* **link**        https://tcpdf.org
* **source**      https://github.com/tecnickcom/tc-lib-pdf
* **SRC DOC**     https://tcpdf.org/docs/srcdoc/tc-lib-pdf

## Description

PHP library for generating PDF documents on-the-fly.
This is the new version of the TCPDF library that will be deprecated once all the existing features are ported.

NOTE: The first complete (stable) version will be 8.1.0. As this is currently unstable, only the patch number in the version will be updated, even if breaking changes are introduced.

### Main Features:

***(the features striked through are in progress)***

* all standard page formats, custom page formats, custom margins and units of measure;
* UTF-8 Unicode and Right-To-Left languages;
* TrueTypeUnicode, OpenTypeUnicode v1, TrueType, OpenType v1, Type1 and CID-0 fonts;
* font subsetting;
* [] ~SVG~
* [] ~CSS~
* [] ~HTML~
* [] ~JavaScript~
* images, graphic (geometric figures) and transformation methods;
* supports JPEG, PNG and SVG images natively, all images supported by GD (GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM) and all images supported via ImagMagick (http://www.imagemagick.org/script/formats.php)
* 1D and 2D barcodes via tc-lib-barcode.
* JPEG and PNG ICC profiles, Grayscale, RGB, CMYK, Spot Pdfs and Transparencies;
* page common content support (header/footer);
* document encryption up to 256 bit and digital signature certifications;
* PDF annotations, including links, text and file attachments;
* text rendering modes (fill, stroke and clipping);
* multiple columns mode;
* no-write page regions;
* bookmarks, named destinations and table of content;
* text hyphenation;
* text stretching and spacing (tracking);
* automatic page break, line break and text alignments including justification;
* automatic page numbering and page groups;
* move and delete pages;
* page compression (requires php-zlib extension);
* XOBject Templates;
* Layers and object visibility.
* PDF/A-1b support.

### Third party fonts:

This library may include third party font files released with different licenses.

All the PHP files on the fonts directory are subject to the general TCPDF license (GNU-LGPLv3),
they do not contain any binary data but just a description of the general properties of a particular font.
These files can be also generated on the fly using the font utilities and TCPDF methods.

All the original binary TTF font files have been renamed for compatibility with TCPDF and compressed using the gzcompress PHP function that uses the ZLIB data format (.z files).

The binary files (.z) that begins with the prefix "free" have been extracted from the GNU FreeFont collection (GNU-GPLv3).
The binary files (.z) that begins with the prefix "pdfa" have been derived from the GNU FreeFont, so they are subject to the same license.
For the details of Copyright, License and other information, please check the files inside the directory fonts/freefont-20120503
Link : http://www.gnu.org/software/freefont/

The binary files (.z) that begins with the prefix "dejavu" have been extracted from the DejaVu fonts 2.33 (Bitstream) collection.
For the details of Copyright, License and other information, please check the files inside the directory fonts/dejavu-fonts-ttf-2.33
Link : http://dejavu-fonts.org

The binary files (.z) that begins with the prefix "ae" have been extracted from the Arabeyes.org collection (GNU-GPLv2).
Link : http://projects.arabeyes.org/

### ICC profile:

TCPDF includes the sRGB.icc profile from the icc-profiles-free Debian package:
https://packages.debian.org/source/stable/icc-profiles-free

## Getting started

First, you need to install all development dependencies using [Composer](https://getcomposer.org/):

```bash
$ curl -sS https://getcomposer.org/installer | php
$ mv composer.phar /usr/local/bin/composer
```

You can install the library via composer:

```bash
composer require tecnickcom/tc-lib-pdf
```

This project include a Makefile that allows you to test and build the project with simple commands.
To see all available options:

```bash
make help
```

To install all the development dependencies:

```bash
make deps
```

## Running all tests

Before committing the code, please check if it passes all tests using

```bash
make qa
```

All artifacts are generated in the target directory.


## Example

Examples are located in the `example` directory.

Start a development server (requires PHP 8.0+) using the command:

```
make server
```

and point your browser to <http://localhost:8971/index.php>


## Installation

Create a composer.json in your projects root-directory:

```json
{
    "require": {
        "tecnickcom/tc-lib-pdf": "dev-main"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:tecnickcom/tc-lib-pdf.git"
        }
    ]
}
```


## Packaging

This library is mainly intended to be used and included in other PHP projects using Composer.
However, since some production environments dictates the installation of any application as RPM or DEB packages,
this library includes make targets for building these packages (`make rpm` and `make deb`).
The packages are generated under the `target` directory.

When this library is installed using an RPM or DEB package, you can use it your code by including the autoloader:
```
require_once ('/usr/share/php/Com/Tecnick/Barcode/autoload.php');
```


## Developer(s) Contact

* Nicola Asuni <info@tecnick.com>
