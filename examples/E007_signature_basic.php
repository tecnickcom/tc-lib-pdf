<?php

/**
 * E007_signature_basic.php
 *
 * @since       2026-04-21
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 007');
$pdf->setTitle('PAdES B-B Digital Signature Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example signature pades pades-b-b');
$pdf->setPDFFilename('007_signature_basic.pdf');

// Insert font before adding the first page.
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);

// Add a page and a simple signed-content block.
$page = $pdf->addPage();
$pdf->page->addContent($bfont['out']);

$text = 'This document is signed with a PAdES-BASELINE-B signature (/ETSI.CAdES.detached).';
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

$text2 = 'The CMS carries the ESS signing-certificate-v2 attribute required by ETSI EN 319 142-1.';
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

// Preferred fluent API: $pdf->signature()->configure(...). The 'profile' option
// selects the PAdES baseline level (legacy | pades-b-b | pades-b-t | pades-b-lt |
// pades-b-lta); the pre-facade $pdf->setSignature([...]) method still works and
// takes the same array. The signing process and this demo's limitations are
// rendered into the document itself (see the explanatory block added below).
$pdf->signature()->configure([
    'profile' => 'pades-b-b',
    'digest_algorithm' => 'sha256', // sha256 | sha384 | sha512
    'cert_type' => 2,
    'info' => [
        'ContactInfo' => 'https://github.com/tecnickcom/tc-lib-pdf',
        'Location' => 'Demo Office',
        'Name' => 'tc-lib-pdf',
        'Reason' => 'PAdES-BASELINE-B signature example',
    ],
    'password' => '',
    'privkey' => $cert,
    'signcert' => $cert,
]);

// Visible signature field plus one extra empty approval signature field.
$pdf->signature()->appearance()->place(posx: 15, posy: 35, width: 75, height: 20, page: -1, name: 'PrimarySignature');

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

$pdf->signature()->appearance()->stream(stream: $sigAppearance);

// Exposed object ID of the signature widget annotation.
// This can be useful for low-level workflows or custom QA checks.
$signatureWidgetObjId = $pdf->signature()->widgetObjectId();
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

// A second, empty signature field. It is a valid unsigned placeholder (no CMS is
// written to it) that a later party can sign in an approval workflow. Only the
// PrimarySignature field above carries a signature in this example's output.
$pdf->signature()->emptyField(posx: 15, posy: 60, width: 75, height: 20, page: -1, name: 'ApprovalSignature');

// Render the signing process and the demo's limitations into the document so the
// output PDF is self-documenting (this text is page content, not part of the
// signed appearance stream).
$explainHtml = <<<HTML
    <h2 style="font-size:11pt;color:#1f2a33;">How this PAdES-BASELINE-B signature is produced</h2>
    <p style="font-size:9pt;color:#1f2a33;">The library reserves a <code>/Contents</code> placeholder inside a
    <code>/SubFilter /ETSI.CAdES.detached</code> signature field, hashes the document over the two
    <code>/ByteRange</code> spans that surround the placeholder, and embeds a detached CMS (PKCS#7). The
    CAdES SignerInfo carries the mandatory content-type and message-digest attributes plus the ESS
    signing-certificate-v2 attribute required by ETSI EN 319 142-1. Per the baseline the CMS signing-time
    attribute is intentionally omitted; the claimed time is carried by the <code>/M</code> dictionary entry
    instead. The produced CMS verifies cryptographically against the <code>/ByteRange</code> bytes.</p>
    <h2 style="font-size:11pt;color:#7a1f1f;">Limitations of this demo</h2>
    <ul style="font-size:9pt;color:#1f2a33;">
      <li>B-B has no timestamp, so the <code>/M</code> signing time is a self-asserted claim and is not
      cryptographically protected. Use <code>pades-b-t</code> (example E008) for a trusted signing time
      backed by an RFC 3161 TSA.</li>
      <li>The bundled certificate <code>examples/data/cert/tcpdf.crt</code> is self-signed. The CMS is
      cryptographically valid, but the signer identity does not chain to a trusted root, so viewers report
      the signature as valid with an unknown or untrusted signer. Sign with a CA-issued certificate for a
      trusted identity verdict.</li>
    </ul>
    HTML;
$pdf->addHTMLCell(html: $explainHtml, posx: 15, posy: 85, width: 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
