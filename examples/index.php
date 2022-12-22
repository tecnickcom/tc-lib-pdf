<?php
/**
 * index.php
 *
 * @since       2017-05-08
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2017 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require ('../vendor/autoload.php');

define('OUTPUT_FILE', '../target/example.pdf');

// define fonts directory
define('K_PATH_FONTS', '../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/');

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, '');

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('John Doe');
$pdf->setSubject('tc-lib-pdf example');
$pdf->setTitle('Example');
$pdf->setKeywords('TCPDF','tc-lib-pdf','example');

// Insert fonts
$bfont = $pdf->font->insert($pdf->pon, 'helvetica');
$bfont = $pdf->font->insert($pdf->pon, 'times', 'BI');

// Add a page
$page01 = $pdf->page->add();

// Add Images

$iid01 = $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_CMYK.jpg');
$iid01_out = $pdf->image->getSetImage($iid01, 0, 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid01_out);

$iid02 = $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_GRAY.jpg');
$iid02_out = $pdf->image->getSetImage($iid02, 40, 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid02_out);

$iid03 = $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_GRAY.png');
$iid03_out = $pdf->image->getSetImage($iid03, 80, 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid03_out);

$iid04 = $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_INDEX16.png');
$iid04_out = $pdf->image->getSetImage($iid04, 120, 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid04_out);

$iid05 = $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_INDEX256.png');
$iid05_out = $pdf->image->getSetImage($iid05, 160, 0, 40, 20, $page01['height']);
$pdf->page->addContent($iid05_out);

$iid06 = $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_INDEXALPHA.png');
$iid06_out = $pdf->image->getSetImage($iid06, 0, 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid06_out);

$iid07 = $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_RGB.jpg');
$iid07_out = $pdf->image->getSetImage($iid07, 40, 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid07_out);

$iid08 = $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_RGB.png');
$iid08_out = $pdf->image->getSetImage($iid08, 80, 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid08_out);

$iid09 = $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_RGBALPHA.png');
$iid09_out = $pdf->image->getSetImage($iid09, 120, 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid09_out);

$iid10 = $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_RGBICC.jpg');
$iid10_out = $pdf->image->getSetImage($iid10, 160, 20, 40, 20, $page01['height']);
$pdf->page->addContent($iid10_out);

$iid11 = $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_RGBICC.png');
$iid11_out = $pdf->image->getSetImage($iid11, 0, 40, 40, 20, $page01['height']);
$pdf->page->addContent($iid11_out);

$iid12 = $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_RGBINT.png');
$iid12_out = $pdf->image->getSetImage($iid12, 40, 40, 40, 20, $page01['height']);
$pdf->page->addContent($iid12_out);

// PDF document as string
$doc = $pdf->getOutPDFString();

// Debug document output:
//var_export($doc);

// Save the PDF document as a file
file_put_contents(OUTPUT_FILE, $doc);

echo 'OK: '.OUTPUT_FILE;
