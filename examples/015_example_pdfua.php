<?php
/**
 * 015_example_pdfua.php
 */

require(__DIR__ . '/../vendor/autoload.php');

\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfua');
$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$pdf->addPage();
$pdf->page->addContent($font['out']);

$html = '<h1>PDF/UA</h1>'
	. '<p>Mode: pdfua</p>'
	. '<p>This generic alias enables the library\'s accessible tagged-PDF workflow.</p>'
	. '<p>Highlights: tagged structure, document language and MarkInfo output, accessible link content, '
	. 'and ActualText support for ligatures and special glyphs.</p>';

$pdf->addHTMLCell($html, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
