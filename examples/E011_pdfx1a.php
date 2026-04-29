<?php
/**
 * E011_pdfx1a.php
 */

require(__DIR__ . '/../vendor/autoload.php');

\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx1a');
$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$pdf->addPage();
$pdf->page->addContent($font['out']);

$html = '<h1>PDF/X-1a</h1>'
	. '<p>Mode: pdfx1a</p>'
	. '<p>PDF/X-1a targets an older, press-safe workflow centered on CMYK and spot colors.</p>'
	. '<p>Highlights: minimum PDF 1.3 output, transparency disabled, encryption disabled, '
	. 'and interactive annotations/actions suppressed for a classic print handoff.</p>';

$pdf->addHTMLCell($html, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
