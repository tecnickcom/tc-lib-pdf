<?php
/**
 * 012_example_pdfx3.php
 */

require(__DIR__ . '/../vendor/autoload.php');

\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx3');
$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$pdf->addPage();
$pdf->page->addContent($font['out']);

$html = '<h1>PDF/X-3</h1>'
	. '<p>Mode: pdfx3</p>'
	. '<p>PDF/X-3 keeps the print-exchange restrictions of early PDF/X while allowing '
	. 'color-managed workflows beyond device CMYK.</p>'
	. '<p>Highlights: minimum PDF 1.3 output, output-intent identification, no transparency, '
	. 'and no interactive actions that would conflict with print-only delivery.</p>';

$pdf->addHTMLCell($html, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
