<?php
/**
 * E017_pdfua2.php
 */

require(__DIR__ . '/../vendor/autoload.php');

\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfua2');
$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$pdf->addPage();
$pdf->page->addContent($font['out']);

$html = '<h1>PDF/UA-2</h1>'
	. '<p>Mode: pdfua2</p>'
	. '<p>PDF/UA-2 moves accessible output onto the PDF 2.0 generation of the standard.</p>'
	. '<p>Highlights: PDF/UA-2 identification metadata, tagged structure output, accessible text semantics, '
	. 'and the same library accessibility protections applied to links, figures, and document metadata.</p>';

$pdf->addHTMLCell($html, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
