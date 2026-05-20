<?php

/**
 * E011_pdfx1a.php
 */

require __DIR__ . '/../vendor/autoload.php';

\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf(unit: 'mm', isunicode: true, subsetfont: false, compress: true, mode: 'pdfx1a');

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 011');
$pdf->setTitle('PDF/X-1a Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example pdfx1a');
$pdf->setPDFFilename('011_pdfx1a.pdf');

$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$pdf->addPage();
$pdf->page->addContent($font['out']);

$html =
    '<h1>PDF/X-1a</h1>'
    . '<p>Mode: pdfx1a</p>'
    . '<p>PDF/X-1a targets an older, press-safe workflow centered on CMYK and spot colors.</p>'
    . '<p>Highlights: minimum PDF 1.3 output, transparency disabled, encryption disabled, '
    . 'and interactive annotations/actions suppressed for a classic print handoff.</p>';

$pdf->addHTMLCell(html: $html, posx: 15, posy: 20, width: 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
