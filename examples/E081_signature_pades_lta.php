<?php

/**
 * E081_signature_pades_lta.php
 *
 * @since       2026-07-15
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
$pdf->setSubject('tc-lib-pdf example: 081');
$pdf->setTitle('PAdES B-LTA Archive Timestamp Signature Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example signature pades pades-b-lta archive timestamp doctimestamp dss');
$pdf->setPDFFilename('081_signature_pades_lta.pdf');

// Insert font before adding the first page.
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);

$page = $pdf->addPage();
$pdf->page->addContent($bfont['out']);

$text = 'This document is a PAdES-BASELINE-LTA signature: B-LT plus a document archive timestamp.';
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

$text2 =
    'The archive timestamp is a /Type /DocTimeStamp field (/SubFilter /ETSI.RFC3161) covering the '
    . 'whole document, added in a further incremental revision to protect the signature over the long term.';
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

// PAdES-BASELINE-LTA is produced by configuring a B-LT signature (with a TSA) and
// then calling upgradeToLta(), which selects the pades-b-lta profile and forces the
// LTV material on. The signing process and this demo's limitations are rendered
// into the document itself (see the explanatory block added below).
$pdf
    ->signature()
    ->configure([
        'profile' => 'pades-b-lt',
        'digest_algorithm' => 'sha256',
        'cert_type' => 2,
        'info' => [
            'ContactInfo' => 'https://github.com/tecnickcom/tc-lib-pdf',
            'Location' => 'Demo Office',
            'Name' => 'tc-lib-pdf',
            'Reason' => 'PAdES-BASELINE-LTA archive-timestamped signature example',
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
    ])
    ->upgradeToLta();

$pdf->signature()->appearance()->place(posx: 15, posy: 40, width: 90, height: 20, page: -1, name: 'ArchiveSignature');

// Appearance stream marking the long-term archival intent of the signature.
$sigW = 90.0;
$sigH = 20.0;
$sigTopY = $page['height'] - $sigH;

$sigAppearance = $bfont['out'];
$sigAppearance .= $pdf->color->getPdfColor('rgb(15%,15%,15%)');
$sigAppearance .= $pdf->getTextCell(
    txt: 'PAdES B-LTA Archive Signature',
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
            'fillColor' => 'rgb(93%,93%,98%)',
            'lineColor' => 'rgb(30%,30%,55%)',
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
    <h2 style="font-size:11pt;color:#1f2a33;">How this PAdES-BASELINE-LTA signature is produced</h2>
    <p style="font-size:9pt;color:#1f2a33;">Configuring a B-LT signature (with a TSA) and then calling
    <code>upgradeToLta()</code> selects the pades-b-lta profile and forces the LTV material on, so the output
    carries three incremental revisions: the CAdES signature with its RFC 3161 signature timestamp (B-T),
    the DSS/VRI validation material (B-LT), and a <code>/Type /DocTimeStamp</code> archive timestamp
    (<code>/SubFilter /ETSI.RFC3161</code>) whose messageImprint covers the whole document up to that
    revision (B-LTA). The archive timestamp protects the signature and its validation material over the long
    term, even after the signing certificate or its algorithms weaken.</p>
    <h2 style="font-size:11pt;color:#7a1f1f;">Limitations of this demo</h2>
    <ul style="font-size:9pt;color:#1f2a33;">
      <li>Two live RFC 3161 requests are made: one for the signature timestamp and one for the archive
      timestamp. A configured, reachable TSA is required for both; if the TSA is unreachable or rejects a
      request the signing step throws a signing exception and no PDF is produced.</li>
      <li>As in E009, the bundled self-signed demo certificate carries no OCSP/CRL responders, so the DSS
      embeds only certificates and a validator reports B-T with a DSS and an archive timestamp rather than a
      full B-LTA. A CA-issued certificate with reachable revocation responders is needed for a full B-LTA
      verdict.</li>
    </ul>
    HTML;
$pdf->addHTMLCell(html: $explainHtml, posx: 15, posy: 66, width: 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
