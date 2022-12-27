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


// Clipping Mask

$cnz = $pdf->graph->getStartTransform();
$cnz .= $pdf->graph->getStarPolygon(50, 50, 40, 10, 3, 0, 'CNZ');
$clipimg = $pdf->image->add('../vendor/tecnickcom/tc-lib-pdf-image/test/images/200x100_CMYK.jpg');
$cnz .= $pdf->image->getSetImage($clipimg, 10, 10, 80, 80, $page05['height']);
$cnz .= $pdf->graph->getStopTransform();
$pdf->page->addContent($cnz);


// ----------


// PDF document as string
$doc = $pdf->getOutPDFString();

// Debug document output:
//var_export($doc);

// Save the PDF document as a file
file_put_contents(OUTPUT_FILE, $doc);

echo 'OK: '.OUTPUT_FILE;
