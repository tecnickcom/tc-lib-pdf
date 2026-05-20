<?php

/**
 * E008_signature_timestamp.php
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
$pdf->setSubject('tc-lib-pdf example: 008');
$pdf->setTitle('Signature Timestamp Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example signature timestamp tsa');
$pdf->setPDFFilename('008_signature_timestamp.pdf');

// Insert font before adding the first page.
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);

$page = $pdf->addPage();
$pdf->page->addContent($bfont['out']);

$text = 'This document requests an RFC 3161 TSA timestamp for the CMS signature.';
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

$text2 = 'Timestamping requires outbound HTTPS connectivity to the configured TSA endpoint.';
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
        'Reason' => 'RFC 3161 timestamped signature example',
    ],
    'password' => '',
    'privkey' => $cert,
    'signcert' => $cert,
]);

$pdf->setSignTimeStamp([
    'enabled' => true,
    // Public demo endpoint. For production use your trusted TSA service.
    'host' => 'https://freetsa.org/tsr',
    'username' => '',
    'password' => '',
    'cert' => '',
    'hash_algorithm' => 'sha256',
    'policy_oid' => '',
    'nonce_enabled' => true,
    'timeout' => 30,
    'verify_peer' => true,
]);

$pdf->setSignatureAppearance(posx: 15, posy: 35, width: 90, heigth: 20, page: -1, name: 'TimestampedSignature');

// Optional appearance stream to clearly indicate timestamped signature intent.
$sigW = 90.0;
$sigH = 20.0;
$sigTopY = $page['height'] - $sigH;

$sigAppearance = $bfont['out'];
$sigAppearance .= $pdf->color->getPdfColor('rgb(20%,20%,20%)');
$sigAppearance .= $pdf->getTextCell(
    txt: 'RFC 3161 Timestamped Signature',
    posx: 0,
    posy: $sigTopY,
    width: $sigW,
    height: $sigH,
    offset: 3.0,
    linespace: 0,
    valign: 'C',
    halign: 'L',
    cell: null,
    styles: [
        'all' => [
            'fillColor' => 'rgb(96%,96%,90%)',
            'lineColor' => 'rgb(35%,35%,10%)',
            'lineWidth' => 1.0,
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

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
