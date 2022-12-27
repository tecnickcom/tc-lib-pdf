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

use \Com\Tecnick\Color\Model\Cmyk as ColorCMYK;
use \Com\Tecnick\Color\Model\Gray as ColorGray;
use \Com\Tecnick\Color\Model\Hsl as ColorHSL;
use \Com\Tecnick\Color\Model\Rgb as ColorRGB;

define('OUTPUT_FILE', '../target/test.pdf');

// define fonts directory
define('K_PATH_FONTS', '../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/');

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false, '');

// ----------
// Set Metadata

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('John Doe');
$pdf->setSubject('tc-lib-pdf example');
$pdf->setTitle('Example');
$pdf->setKeywords('TCPDF','tc-lib-pdf','example');


$page05 = $pdf->page->add();

$pdf->graph->setPageWidth($page05['width']);
$pdf->graph->setPageHeight($page05['height']);


$barcode_style =  array(
    'lineWidth' => 0,
    'lineCap'   => 'butt',
    'lineJoin'  => 'miter',
    'dashArray' => array(),
    'dashPhase' => 0,
    'lineColor' => 'black',
    'fillColor' => 'red',
);

$barcode2 = $pdf->getBarcode(
    'IMB',
    '01234567094987654321-01234567891',
    10,
    80,
    -1, 
    -2,
    array(0,0,0,0),
    $barcode_style
);
$pdf->page->addContent($barcode2);

// ----------


// PDF document as string
$doc = $pdf->getOutPDFString();

// Debug document output:
//var_export($doc);

// Save the PDF document as a file
file_put_contents(OUTPUT_FILE, $doc);

echo 'OK: '.OUTPUT_FILE;
