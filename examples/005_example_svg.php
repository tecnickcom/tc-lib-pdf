<?php
/**
 * 005_example_svg.php
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
$pdf->setSubject('tc-lib-pdf example: 005');
$pdf->setTitle('Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example');
$pdf->setPDFFilename('005_example_svg.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent(false);

$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$page = $pdf->addPage(['format' => 'A4']);

$pdf->graph->setPageWidth($page['width']);
$pdf->graph->setPageHeight($page['height']);

$pdf->page->addContent($bfont['out']);

$svgid02 = $pdf->addSVG('./images/testsvg.svg', 10, 10, 120, 60, $page['height']);
$svgid02_out = $pdf->getSetSVG($svgid02);
$pdf->page->addContent($svgid02_out);

$svgid03 = $pdf->addSVG('./images/testsvgblend.svg', 10, 70, 90, 60, $page['height']);
$svgid03_out = $pdf->getSetSVG($svgid03);
$pdf->page->addContent($svgid03_out);

$svgid01 = $pdf->addSVG('./images/tcpdf_box.svg', 10, 90, 120, 180, $page['height']);
$svgid01_out = $pdf->getSetSVG($svgid01);
$pdf->page->addContent($svgid01_out);

// The copyright holder of the tux.svg image is Larry Ewing,
// allows anyone to use it for any purpose, provided that the copyright holder is properly attributed.
// Redistribution, derivative work, commercial use, and all other use is permitted.
$svgid03 = $pdf->addSVG('./images/tux.svg', 130, 65, 62, 75, $page['height']);
$svgid03_out = $pdf->getSetSVG($svgid03);
$pdf->page->addContent($svgid03_out);

// =============================================================

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF($rawpdf);
