<?php

/**
 * E009_signature_ltv.php
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
$pdf->setSubject('tc-lib-pdf example: 009');
$pdf->setTitle('LTV Signature Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example signature ltv dss vri');
$pdf->setPDFFilename('009_signature_ltv.pdf');

// Insert font before adding the first page.
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);

$page = $pdf->addPage();
$pdf->page->addContent($bfont['out']);

$text = 'This document enables LTV collection to emit DSS/VRI validation structures.';
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

$text2 = 'When OCSP/CRL/cert retrieval succeeds, validators can verify long after certificate expiry.';
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
        'Reason' => 'LTV (DSS/VRI) signature example',
    ],
    'password' => '',
    'privkey' => $cert,
    'signcert' => $cert,
    'ltv' => [
        'enabled' => true,
        'embed_ocsp' => true,
        'embed_crl' => true,
        'embed_certs' => true,
        'include_dss' => true,
        'include_vri' => true,
    ],
]);

$pdf->setSignatureAppearance(posx: 15, posy: 35, width: 90, heigth: 20, page: -1, name: 'LTVSignature');

// Attach a reusable Form XObject as signature appearance using the official API.
// Any imported page can be used as a visual signature stamp.
$stampSource = __DIR__ . '/data/pdf/E006_example_minimal.pdf';
if (\is_readable($stampSource)) {
    $sourceId = $pdf->setImportSourceFile($stampSource);
    $tpl = $pdf->importPage(sourceId: $sourceId, pageNum: 1);
    $pdf->setSignatureAppearanceXObject($tpl->getXobjId());
} else {
    // Fallback appearance stream when the optional stamp source is unavailable.
    $sigW = 90.0;
    $sigH = 20.0;
    $sigTopY = $page['height'] - $sigH;

    $sigAppearance = $bfont['out'];
    $sigAppearance .= $pdf->color->getPdfColor('rgb(15%,15%,15%)');
    $sigAppearance .= $pdf->getTextCell(
        txt: 'LTV-enabled signature (DSS/VRI)',
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
                'fillColor' => 'rgb(94%,97%,92%)',
                'lineColor' => 'rgb(20%,40%,20%)',
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
}

$widgetObjId = $pdf->getSignatureObjectID();
$text3 = 'LTV signature widget object ID: ' . $widgetObjId;
$textCell3 = $pdf->getTextCell(
    txt: $text3,
    posx: 15,
    posy: 60,
    width: 180,
    height: 0,
    offset: 0,
    linespace: 1,
    valign: 'T',
    halign: 'L',
);
$pdf->page->addContent($textCell3);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
