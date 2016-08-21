# tc-lib-pdf
*PHP PDF Library*

# UNDER DEVELOPMENT

[![Latest Stable Version](https://poser.pugx.org/tecnickcom/tc-lib-pdf/version)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
[![Master Build Status](https://secure.travis-ci.org/tecnickcom/tc-lib-pdf.png?branch=master)](https://travis-ci.org/tecnickcom/tc-lib-pdf?branch=master)
[![Master Coverage Status](https://coveralls.io/repos/tecnickcom/tc-lib-pdf/badge.svg?branch=master&service=github)](https://coveralls.io/github/tecnickcom/tc-lib-pdf?branch=master)
[![License](https://poser.pugx.org/tecnickcom/tc-lib-pdf/license)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)
[![Total Downloads](https://poser.pugx.org/tecnickcom/tc-lib-pdf/downloads)](https://packagist.org/packages/tecnickcom/tc-lib-pdf)

[![Develop Branch](https://img.shields.io/badge/-develop:-gray.svg)](https://github.com/tecnickcom/tc-lib-pdf/tree/develop)
[![Develop Build Status](https://secure.travis-ci.org/tecnickcom/tc-lib-pdf.png?branch=develop)](https://travis-ci.org/tecnickcom/tc-lib-pdf?branch=develop)
[![Develop Coverage Status](https://coveralls.io/repos/tecnickcom/tc-lib-pdf/badge.svg?branch=develop&service=github)](https://coveralls.io/github/tecnickcom/tc-lib-pdf?branch=develop)

[![Donate via PayPal](https://img.shields.io/badge/donate-paypal-87ceeb.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&currency_code=GBP&business=paypal@tecnick.com&item_name=donation%20for%20tc-lib-pdf%20project)
*Please consider supporting this project by making a donation via [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&currency_code=GBP&business=paypal@tecnick.com&item_name=donation%20for%20tc-lib-pdf%20project)*

* **category**    Library
* **package**     \Com\Tecnick\Pdf
* **author**      Nicola Asuni <info@tecnick.com>
* **copyright**   2002-2016 Nicola Asuni - Tecnick.com LTD
* **license**     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
* **link**        https://tcpdf.org
* **source**      https://github.com/tecnickcom/tc-lib-pdf
* **SRC DOC**     https://tcpdf.org/docs/srcdoc/tc-lib-pdf
* **RPM**         https://bintray.com/tecnickcom/rpm/tc-lib-pdf
* **DEB**         https://bintray.com/tecnickcom/deb/tc-lib-pdf

## Description

PHP library for generating PDF documents on-the-fly.

### Main Features:
* no external libraries are required for the basic functions;
* all standard page formats, custom page formats, custom margins and units of measure;
* UTF-8 Unicode and Right-To-Left languages;
* TrueTypeUnicode, OpenTypeUnicode v1, TrueType, OpenType v1, Type1 and CID-0 fonts;
* font subsetting;
* methods to publish some XHTML + CSS code, Javascript and Forms;
* images, graphic (geometric figures) and transformation methods;
* supports JPEG, PNG and SVG images natively, all images supported by GD (GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM) and all images supported via ImagMagick (http://www.imagemagick.org/script/formats.php)
* 1D and 2D barcodes: CODE 39, ANSI MH10.8M-1983, USD-3, 3 of 9, CODE 93, USS-93, Standard 2 of 5, Interleaved 2 of 5, CODE 128 A/B/C, 2 and 5 Digits UPC-Based Extension, EAN 8, EAN 13, UPC-A, UPC-E, MSI, POSTNET, PLANET, RMS4CC (Royal Mail 4-state Customer Code), CBC (Customer Bar Code), KIX (Klant index - Customer index), Intelligent Mail Barcode, Onecode, USPS-B-3200, CODABAR, CODE 11, PHARMACODE, PHARMACODE TWO-TRACKS, Datamatrix, QR-Code, PDF417;
* JPEG and PNG ICC profiles, Grayscale, RGB, CMYK, Spot Pdfs and Transparencies;
* automatic page header and footer management;
* document encryption up to 256 bit and digital signature certifications;
* transactions to UNDO commands;
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

This project include a Makefile that allows you to test and build the project with simple commands.
To see all available options:

```bash
make help
```

To install all the development dependencies:

```bash
make build_dev
```

## Running all tests

Before committing the code, please check if it passes all tests using

```bash
make qa_all
```
this generates the phpunit coverage report in target/coverage.
Please check if the tests are covering all code.

Generate the documentation:

```bash
make docs
```

Generate static analysis reports in target/report:

```bash
make reports
```

Other make options allows you to install this library globally and build an RPM package.
Please check all the available options using `make help`.


## Example

Examples are located in the `example` directory.

Start a development server (requires PHP 5.4) using the command:

```
make server
```

and point your browser to <http://localhost:8000/index.php>


## Installation

Create a composer.json in your projects root-directory:

```json
{
    "require": {
        "tecnickcom/tc-lib-pdf": "dev-master"
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

**NOTE:** Updated RPM and Debian packages of this library can be downloaded from: https://bintray.com/tecnickcom


## Developer(s) Contact

* Nicola Asuni <info@tecnick.com>
