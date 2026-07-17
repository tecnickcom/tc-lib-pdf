<?php

/**
 * E008_signature_timestamp.php
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
$pdf->setSubject('tc-lib-pdf example: 008');
$pdf->setTitle('PAdES B-T Signature Timestamp Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example signature timestamp tsa pades pades-b-t');
$pdf->setPDFFilename('008_signature_timestamp.pdf');

// Insert font before adding the first page.
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);

$page = $pdf->addPage();
$pdf->page->addContent($bfont['out']);

$text = 'This document is a PAdES-BASELINE-T signature: B-B plus an RFC 3161 signature timestamp.';
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

// PAdES-BASELINE-T: configure() selects the profile, timestamp() adds the TSA;
// both calls are chainable. The signing process and this demo's limitations are
// rendered into the document itself (see the explanatory block added below).
$pdf
    ->signature()
    ->configure([
        'profile' => 'pades-b-t',
        'digest_algorithm' => 'sha256',
        'cert_type' => 2,
        'info' => [
            'ContactInfo' => 'https://github.com/tecnickcom/tc-lib-pdf',
            'Location' => 'Demo Office',
            'Name' => 'tc-lib-pdf',
            'Reason' => 'PAdES-BASELINE-T timestamped signature example',
        ],
        'password' => '',
        'privkey' => $cert,
        'signcert' => $cert,
    ])
    ->timestamp([
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

$pdf
    ->signature()
    ->appearance()
    ->place(posx: 15, posy: 35, width: 90, height: 20, page: -1, name: 'TimestampedSignature');

// Optional appearance stream to clearly indicate timestamped signature intent.
$sigW = 90.0;
$sigH = 20.0;
$sigTopY = $page['height'] - $sigH;

$sigAppearance = $bfont['out'];
$sigAppearance .= $pdf->color->getPdfColor('rgb(20%,20%,20%)');
$sigAppearance .= $pdf->getTextCell(
    txt: 'PAdES B-T Timestamped Signature',
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

$pdf->signature()->appearance()->stream(stream: $sigAppearance);

// Render the signing process and the demo's limitations into the document so the
// output PDF is self-documenting (this text is page content, not part of the
// signed appearance stream).
$explainHtml = <<<HTML
    <h2 style="font-size:11pt;color:#1f2a33;">How this PAdES-BASELINE-T signature is produced</h2>
    <p style="font-size:9pt;color:#1f2a33;">A B-T signature is a B-B signature (example E007) plus a signature
    timestamp. The library first builds the CAdES CMS, then sends the SignerInfo signature bytes to the TSA
    and embeds the returned RFC 3161 token in the CMS as the <code>id-aa-signatureTimeStampToken</code>
    unsigned attribute. The output CMS verifies over the <code>/ByteRange</code> bytes and carries a genuine
    TSA token that binds a trusted time to the signature.</p>
    <h2 style="font-size:11pt;color:#7a1f1f;">Limitations of this demo</h2>
    <ul style="font-size:9pt;color:#1f2a33;">
      <li>Timestamping performs a live RFC 3161 request to the TSA over HTTPS. The endpoint used here is the
      public freetsa.org demo service; use your own trusted TSA in production. The timestamp is mandatory
      for <code>pades-b-t</code> and above, so if the TSA is unreachable or rejects the request the signing
      step throws a signing exception and no PDF is produced.</li>
      <li>The bundled certificate is self-signed, so (as in E007) the signature is cryptographically valid
      but the signer identity is not trusted by viewers. The embedded timestamp itself is a real
      freetsa.org token and validates.</li>
    </ul>
    HTML;
$pdf->addHTMLCell(html: $explainHtml, posx: 15, posy: 65, width: 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
