<?php

/**
 * E007_signature_basic.php
 *
 * @since       2026-04-21
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
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 007');
$pdf->setTitle('Basic Digital Signature Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example signature');
$pdf->setPDFFilename('007_signature_basic.pdf');

// Insert font before adding the first page.
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);

// Add a page and a simple signed-content block.
$page = $pdf->addPage();
$pdf->page->addContent($bfont['out']);

$text = 'This document is signed using a detached CMS (PKCS#7) signature.';
$textCell = $pdf->getTextCell(
    txt: $text,
    posx: 15,
    posy: 20,
    width: 180,
    height: 0,
    offset: 0,
    linespace: 1,
    valign: 'T',
    halign: 'L',
);
$pdf->page->addContent($textCell);

$text2 = 'The widget below uses a custom /AP appearance stream so the field is visibly rendered in viewers.';
$textCell2 = $pdf->getTextCell(
    txt: $text2,
    posx: 15,
    posy: 27,
    width: 180,
    height: 0,
    offset: 0,
    linespace: 1,
    valign: 'T',
    halign: 'L',
);
$pdf->page->addContent($textCell2);

$certPath = \realpath(__DIR__ . '/data/cert/tcpdf.crt');
if ($certPath === false) {
    throw new \RuntimeException('Missing signing certificate: examples/data/cert/tcpdf.crt');
}

$cert = 'file://' . $certPath;

$pdf->setSignature([
    'cert_type' => 2,
    'info' => [
        'ContactInfo' => 'https://github.com/tecnickcom/tc-lib-pdf',
        'Location' => 'Demo Office',
        'Name' => 'tc-lib-pdf',
        'Reason' => 'Basic detached signature example',
    ],
    'password' => '',
    'privkey' => $cert,
    'signcert' => $cert,
]);

// Visible signature field plus one extra empty approval signature field.
$pdf->setSignatureAppearance(posx: 15, posy: 35, width: 75, heigth: 20, page: -1, name: 'PrimarySignature');

// Optional custom appearance stream for the signature widget (/AP /N).
// Coordinate system starts from the lower-left corner of the widget rectangle.
$sigW = 75.0;
$sigH = 20.0;
$sigTopY = $page['height'] - $sigH;

// styles controls cell fill/border; set font and text color explicitly for AP text.
$sigAppearance = $bfont['out'];
$sigAppearance .= $pdf->color->getPdfColor('rgb(15%,15%,15%)');
$sigAppearance .= $pdf->getTextCell(
    txt: 'Digitally signed by tc-lib-pdf',
    posx: 0,
    posy: $sigTopY,
    width: $sigW,
    height: $sigH,
    offset: 2.5,
    linespace: 0,
    valign: 'C',
    halign: 'L',
    cell: null,
    styles: [
        'all' => [
            'fillColor' => 'rgb(92%,96%,100%)',
            'lineColor' => 'rgb(20%,32%,60%)',
            'lineWidth' => 1.2,
        ],
    ],
    strokewidth: 0,
    wordspacing: 0,
    leading: 0,
    rise: 0,
    jlast: true,
    fill: true,
    stroke: false,
    underline: false,
    linethrough: false,
    overline: false,
    clip: false,
    drawcell: true,
);

$pdf->setSignatureAppearanceStream(stream: $sigAppearance);

// Exposed object ID of the signature widget annotation.
// This can be useful for low-level workflows or custom QA checks.
$signatureWidgetObjId = $pdf->getSignatureObjectID();
$text3 = 'Primary signature widget object ID: ' . $signatureWidgetObjId;
$textCell3 = $pdf->getTextCell(
    txt: $text3,
    posx: 95,
    posy: 35,
    width: 100,
    height: 0,
    offset: 0,
    linespace: 1,
    valign: 'T',
    halign: 'L',
);
$pdf->page->addContent($textCell3);

$pdf->addEmptySignatureAppearance(posx: 15, posy: 60, width: 75, heigth: 20, page: -1, name: 'ApprovalSignature');

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
