<?php
/**
 * E016_pdfua1.php
 */

require(__DIR__ . '/../vendor/autoload.php');

\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfua1');
$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$pdf->addPage();
$pdf->page->addContent($font['out']);

$html = '<h1>PDF/UA-1</h1>'
	. '<p>Mode: pdfua1</p>'
	. '<p>PDF/UA-1 is the original accessible-PDF profile built on PDF 1.7 semantics.</p>'
	. '<p>Highlights: forced accessible viewer preferences, structure-tree output, tagged text content, '
	. 'language metadata, and semantic handling for headings, figures, and links.</p>';

$pdf->addHTMLCell($html, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
