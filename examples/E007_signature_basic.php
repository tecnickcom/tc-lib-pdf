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
require(__DIR__ . '/../vendor/autoload.php');

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
$textCell = $pdf->getTextCell($text, 15, 20, 180, 0, 0, 1, 'T', 'L');
$pdf->page->addContent($textCell);

$text2 = 'The widget below uses a custom /AP appearance stream so the field is visibly rendered in viewers.';
$textCell2 = $pdf->getTextCell($text2, 15, 27, 180, 0, 0, 1, 'T', 'L');
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
$pdf->setSignatureAppearance(15, 35, 75, 20, -1, 'PrimarySignature');

// Optional custom appearance stream for the signature widget (/AP /N).
// Coordinate system starts from the lower-left corner of the widget rectangle.
$sigW = 75.0;
$sigH = 20.0;
$sigTopY = $page['height'] - $sigH;

// styles controls cell fill/border; set font and text color explicitly for AP text.
$sigAppearance = $bfont['out'];
$sigAppearance .= $pdf->color->getPdfColor('rgb(15%,15%,15%)');
$sigAppearance .= $pdf->getTextCell(
    'Digitally signed by tc-lib-pdf',
    0,
    $sigTopY,
    $sigW,
    $sigH,
    2.5,
    0,
    'C',
    'L',
    null,
    [
        'all' => [
            'fillColor' => 'rgb(92%,96%,100%)',
            'lineColor' => 'rgb(20%,32%,60%)',
            'lineWidth' => 1.2,
        ],
    ],
    0,
    0,
    0,
    0,
    true,
    true,
    false,
    false,
    false,
    false,
    false,
    true
);

$pdf->setSignatureAppearanceStream($sigAppearance);

// Exposed object ID of the signature widget annotation.
// This can be useful for low-level workflows or custom QA checks.
$signatureWidgetObjId = $pdf->getSignatureObjectID();
$text3 = 'Primary signature widget object ID: ' . $signatureWidgetObjId;
$textCell3 = $pdf->getTextCell($text3, 95, 35, 100, 0, 0, 1, 'T', 'L');
$pdf->page->addContent($textCell3);

$pdf->addEmptySignatureAppearance(15, 60, 75, 20, -1, 'ApprovalSignature');

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
