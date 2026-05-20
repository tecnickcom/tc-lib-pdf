<?php

/**
 * E014_pdfx5.php
 */

require __DIR__ . '/../vendor/autoload.php';

\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf(unit: 'mm', isunicode: true, subsetfont: false, compress: true, mode: 'pdfx5');

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 014');
$pdf->setTitle('PDF/X-5 Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example pdfx5');
$pdf->setPDFFilename('014_pdfx5.pdf');

$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$pdf->addPage();
$pdf->page->addContent($font['out']);

$html =
    '<h1>PDF/X-5</h1>'
    . '<p>Mode: pdfx5</p>'
    . '<p>PDF/X-5 builds on the PDF/X-4 family for more advanced print workflows and external references.</p>'
    . '<p>Highlights: minimum PDF 1.6 output, transparency retained, modern PDF/X identification, '
    . 'and the same print-safe suppression of unsupported interactive actions.</p>';

$pdf->addHTMLCell(html: $html, posx: 15, posy: 20, width: 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
