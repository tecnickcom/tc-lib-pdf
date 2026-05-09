<?php
/**
 * E010_pdfx.php
 *
 * @since       2026-04-25
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

require(__DIR__ . '/../vendor/autoload.php');

\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx');

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 010');
$pdf->setTitle('PDF/X Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example pdfx');
$pdf->setPDFFilename('010_pdfx.pdf');

$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$page = $pdf->addPage();
$pdf->page->addContent($font['out']);

$html = '<h1>PDF/X</h1>'
	. '<p>Mode: pdfx</p>'
	. '<p>This generic alias currently maps to the library\'s baseline PDF/X print workflow.</p>'
	. '<p>Highlights: PDF/X identification metadata, print-oriented output intent handling, '
	. 'CMYK-oriented process colors, and suppression of interactive actions that are not allowed '
	. 'in a print-exchange profile.</p>';

$pdf->addHTMLCell($html, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
