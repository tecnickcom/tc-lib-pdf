<?php
/**
 * E013_pdfx4.php
 */

require(__DIR__ . '/../vendor/autoload.php');

\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx4');

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 013');
$pdf->setTitle('PDF/X-4 Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example pdfx4');
$pdf->setPDFFilename('013_pdfx4.pdf');

$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$pdf->addPage();
$pdf->page->addContent($font['out']);

$html = '<h1>PDF/X-4</h1>'
	. '<p>Mode: pdfx4</p>'
	. '<p>PDF/X-4 modernizes print exchange by allowing live transparency in a color-managed workflow.</p>'
	. '<p>Highlights: minimum PDF 1.6 output, PDF/X-4 identification metadata, transparency retained, '
	. 'encryption disabled, and interactive actions still suppressed for print conformance.</p>';

$pdf->addHTMLCell($html, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
