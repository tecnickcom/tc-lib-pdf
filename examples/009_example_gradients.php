<?php
/**
 * 009_example_gradients.php
 *
 * @since       2026-04-19
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm', // string $unit = 'mm',
    true, // bool $isunicode = true,
    false, // bool $subsetfont = false,
    false, // bool $compress = true,
    '', // string $mode = '',
    null, // ?ObjEncrypt $objEncrypt = null,
);

// ----------

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 006');
$pdf->setTitle('Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example');
$pdf->setPDFFilename('009_example_gradients.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent(false);

$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$page01 = $pdf->addPage(['format' => 'A4']);

$pdf->graph->setPageWidth($page01['width']);
$pdf->graph->setPageHeight($page01['height']);

$pdf->page->addContent($bfont['out']);

// =============================================================


// Linear gradient
$lingrad = $pdf->graph->getLinearGradient(20, 45, 80, 80, 'red', 'blue', [0, 0, 1, 0]);
$pdf->page->addContent($lingrad);

// Radial Gradient
$radgrad = $pdf->graph->getRadialGradient(110, 45, 80, 80, 'white', 'black', [0.5, 0.5, 1, 1, 1.2]);
$pdf->page->addContent($radgrad);


// CoonsPatchMesh
$coonspatchmesh1 = $pdf->graph->getCoonsPatchMeshWithCoords(20, 155, 80, 80, 'yellow', 'blue', 'green', 'red');
$pdf->page->addContent($coonspatchmesh1);


// set the coordinates for the cubic Bézier points x1,y1 ... x12, y12 of the patch
$coords = [
    0.00,
    0.00,
    0.33,
    0.20,
    //lower left
    0.67,
    0.00,
    1.00,
    0.00,
    0.80,
    0.33,
    //lower right
    0.80,
    0.67,
    1.00,
    1.00,
    0.67,
    0.80,
    //upper right
    0.33,
    1.00,
    0.00,
    1.00,
    0.20,
    0.67,
    //upper left
    0.00,
    0.33,
];                       //lower left

// paint a coons patch gradient with the above coordinates
$coonspatchmesh2 = $pdf->graph->getCoonsPatchMeshWithCoords(110, 155, 80, 80, 'yellow', 'blue', 'green', 'red', $coords, 0, 1);
$pdf->page->addContent($coonspatchmesh2);


// ----------
// Add page 7

$page02 = $pdf->addPage();
$pdf->setBookmark('Color gradient mesh', '', 1);

$pdf->graph->setPageWidth($page02['width']);
$pdf->graph->setPageHeight($page02['height']);

// first patch: f = 0
$patch_array[0]['f'] = 0;
$patch_array[0]['points'] = [0.00, 0.00, 0.33, 0.00, 0.67, 0.00, 1.00, 0.00, 1.00, 0.33, 0.8, 0.67, 1.00, 1.00, 0.67, 0.8, 0.33, 1.80, 0.00, 1.00, 0.00, 0.67, 0.00, 0.33];
$patch_array[0]['colors'][0] = [
    'red' => 1,
    'green' => 1,
    'blue' => 0,
    'alpha' => 1,
];
$patch_array[0]['colors'][1] = [
    'red' => 1,
    'green' => 1,
    'blue' => 1,
    'alpha' => 1,
];
$patch_array[0]['colors'][2] = [
    'red' => 0,
    'green' => 1,
    'blue' => 0,
    'alpha' => 1,
];
$patch_array[0]['colors'][3] = [
    'red' => 1,
    'green' => 0,
    'blue' => 0,
    'alpha' => 1,
];

// second patch - above the other: f = 2
$patch_array[1]['f'] = 2;
$patch_array[1]['points'] = [0.00, 1.33, 0.00, 1.67, 0.00, 2.00, 0.33, 2.00, 0.67, 2.00, 1.00, 2.00, 1.00, 1.67, 1.5, 1.33];
$patch_array[1]['colors'][0] = [
    'red' => 0,
    'green' => 0,
    'blue' => 0,
    'alpha' => 1,
];
$patch_array[1]['colors'][1] = [
    'red' => 1,
    'green' => 0,
    'blue' => 1,
    'alpha' => 1,
];

// third patch - right of the above: f = 3
$patch_array[2]['f'] = 3;
$patch_array[2]['points'] = [1.33, 0.80, 1.67, 1.50, 2.00, 1.00, 2.00, 1.33, 2.00, 1.67, 2.00, 2.00, 1.67, 2.00, 1.33, 2.00];
$patch_array[2]['colors'][0] = [
    'red' => 0,
    'green' => 1,
    'blue' => 1,
    'alpha' => 1,
];
$patch_array[2]['colors'][1] = [
    'red' => 0,
    'green' => 0,
    'blue' => 0,
    'alpha' => 1,
];

// fourth patch - below the above, which means left(?) of the above: f = 1
$patch_array[3]['f'] = 1;
$patch_array[3]['points'] = [2.00, 0.67, 2.00, 0.33, 2.00, 0.00, 1.67, 0.00, 1.33, 0.00, 1.00, 0.00, 1.00, 0.33, 0.8, 0.67];
$patch_array[3]['colors'][0] = [
    'red' => 0,
    'green' => 0,
    'blue' => 0,
    'alpha' => 1,
];
$patch_array[3]['colors'][1] = [
    'red' => 0,
    'green' => 0,
    'blue' => 1,
    'alpha' => 1,
];

$coonspatchmesh3 = $pdf->graph->getCoonsPatchMesh(0, 0, 210, 297, $patch_array, 0, 2);
$pdf->page->addContent($coonspatchmesh3);

// =============================================================

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF($rawpdf);
