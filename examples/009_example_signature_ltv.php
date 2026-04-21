<?php
/**
 * 009_example_signature_ltv.php
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
require(__DIR__ . '/../vendor/autoload.php');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 009');
$pdf->setTitle('LTV Signature Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example signature ltv dss vri');
$pdf->setPDFFilename('009_example_signature_ltv.pdf');

$page = $pdf->addPage();
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);
$pdf->page->addContent($bfont['out']);

$text = 'This document enables LTV collection to emit DSS/VRI validation structures.';
$pdf->addTextCell($text, -1, 15, 20, 180, 0, 0, 1, 'T', 'L');

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

$pdf->setSignatureAppearance(15, 35, 90, 20, -1, 'LTVSignature');

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
