<?php

/**
 * E009_signature_ltv.php
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
$pdf->setSubject('tc-lib-pdf example: 009');
$pdf->setTitle('PAdES B-LT Long-Term Validation Signature Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example signature ltv dss vri pades pades-b-lt');
$pdf->setPDFFilename('009_signature_ltv.pdf');

// Insert font before adding the first page.
$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);

$page = $pdf->addPage();
$pdf->page->addContent($bfont['out']);

$text = 'This document is a PAdES-BASELINE-LT signature: B-T plus a Document Security Store (DSS).';
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
    'The DSS/VRI embed the certificate and, when its CA exposes reachable OCSP/CRL responders, '
    . 'the revocation data needed to verify offline long after expiry.';
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

// PAdES-BASELINE-LT: a B-T signature (so a TSA timestamp is required) plus the
// LTV material (DSS/VRI). The signing process and this demo's limitations are
// rendered into the document itself (see the explanatory block added below).
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
            'Reason' => 'PAdES-BASELINE-LT (DSS/VRI) signature example',
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
    ]);

$pdf->signature()->appearance()->place(posx: 15, posy: 35, width: 90, height: 20, page: -1, name: 'LTVSignature');

// Attach a reusable Form XObject as signature appearance using the official API.
// Any imported page can be used as a visual signature stamp.
$stampSource = __DIR__ . '/data/pdf/E006_example_minimal.pdf';
if (\is_readable($stampSource)) {
    $sourceId = $pdf->setImportSourceFile($stampSource);
    $tpl = $pdf->importPage(sourceId: $sourceId, pageNum: 1);
    $pdf->signature()->appearance()->xobject($tpl->getXobjId());
} else {
    // Fallback appearance stream when the optional stamp source is unavailable.
    $sigW = 90.0;
    $sigH = 20.0;
    $sigTopY = $page['height'] - $sigH;

    $sigAppearance = $bfont['out'];
    $sigAppearance .= $pdf->color->getPdfColor('rgb(15%,15%,15%)');
    $sigAppearance .= $pdf->getTextCell(
        txt: 'PAdES B-LT signature (DSS/VRI)',
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
    $pdf->signature()->appearance()->stream(stream: $sigAppearance);
}

$widgetObjId = $pdf->signature()->widgetObjectId();
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

// Render the signing process and the demo's limitations into the document so the
// output PDF is self-documenting (this text is page content, not part of the
// signed appearance stream).
$explainHtml = <<<HTML
    <h2 style="font-size:11pt;color:#1f2a33;">How this PAdES-BASELINE-LT signature is produced</h2>
    <p style="font-size:9pt;color:#1f2a33;">A B-LT signature is a B-T signature (a TSA timestamp is required)
    plus long-term validation material. The signing process writes two incremental revisions: first the
    CAdES signature with its embedded RFC 3161 signature timestamp (B-T), then a Document Security Store
    (<code>/DSS</code> with a <code>/VRI</code> map keyed by the uppercase SHA-1 of the final signature
    <code>/Contents</code>) carrying the validation material. OCSP/CRL retrieval follows the certificate's
    AIA and CRL-DP extensions so a validator can check the chain offline long after the certificate
    expires.</p>
    <h2 style="font-size:11pt;color:#7a1f1f;">Limitations of this demo</h2>
    <ul style="font-size:9pt;color:#1f2a33;">
      <li>A reachable TSA is required (live HTTPS request); if it is unreachable or rejects the request the
      signing step throws a signing exception and no PDF is produced.</li>
      <li>The bundled self-signed demo certificate exposes neither an OCSP responder nor a CRL distribution
      point, so the DSS embeds only the certificate bytes (no revocation data) and a validator reports B-T
      with a DSS present rather than a full B-LT. Missing responders are tolerated and do not abort signing.
      Sign with a CA-issued certificate whose OCSP/CRL responders are reachable at signing time to obtain a
      full B-LT verdict.</li>
    </ul>
    HTML;
$pdf->addHTMLCell(html: $explainHtml, posx: 15, posy: 70, width: 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
