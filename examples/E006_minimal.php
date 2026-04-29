<?php
/**
 * E006_minimal.php
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

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf();

// Insert font
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Add first page
$page = $pdf->addPage();

// Add font to the page
$pdf->page->addContent($bfont['out']);

// some HTML content
$html = '<h1>Hello, PDF!</h1><p>Generated with tc-lib-pdf.</p>';

// render the HTML content
$pdf->addHTMLCell($html, 15, 20, 180);

// Get the PDF content
$rawpdf = $pdf->getOutPDFString();

// Render the PDF content
$pdf->renderPDF($rawpdf);
