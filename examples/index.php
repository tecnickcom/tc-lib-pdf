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

// define fonts directory
define('K_PATH_FONTS', '../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/');

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('John Doe');
$pdf->setSubject('tc-lib-pdf example');
$pdf->setTitle('Example');
$pdf->setKeywords('TCPDF','tc-lib-pdf','example');

// Add a page
$pdf->page->add();

// Insert fonts
$bfont = $pdf->font->insert($pdf->pon, 'helvetica');
$bfont = $pdf->font->insert($pdf->pon, 'times', 'BI');

// $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_CMYK.jpg');
// $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_GRAY.jpg');
// $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_GRAY.png');
// $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_INDEX16.png');
// $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_INDEX256.png');
// $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_INDEXALPHA.png');
// $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_RGB.jpg');
// $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_RGB.png');
// $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_RGBALPHA.png');
// $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_RGBICC.jpg');
// $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_RGBICC.png');
// $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_RGBINT.png');


// PDF document as string
$doc = $pdf->getOutPDFString();

// Debug document output:
var_export($doc);

// Save the PDF document as a file
file_put_contents('../target/example.pdf', $doc);
