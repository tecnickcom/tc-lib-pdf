<?php

/**
 * E084_signature_two_phase_cms.php
 *
 * Two-phase CMS signing: the document side computes the ByteRange digest and the
 * CMS signed attributes, a key service signs those attributes without seeing the
 * document, and the detached CAdES is assembled and injected here. The signing
 * request crosses the boundary as an array, so the two phases can run in separate
 * processes or requests.
 *
 * @since       2026-08-25
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: local file reads (images, fonts, attachments) are restricted to an allowlist of
// trusted paths that covers this package tree, so run the examples in place. To read assets
// from other locations, list them in the 'allowedPaths' entry of the fileOptions constructor
// parameter (see E047_remote_resources_security.php).

// NOTE: run make fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$certPath = \realpath(__DIR__ . '/data/cert/tcpdf.crt');
if ($certPath === false) {
    throw new \RuntimeException('Missing signing certificate: examples/data/cert/tcpdf.crt');
}

// The document side needs the signing certificate, not the key: the certificate is
// public and a key service hands it out before the first signature.
$certPem = (string) \file_get_contents($certPath);
$x509 = \openssl_x509_read($certPem);
if ($x509 === false) {
    throw new \RuntimeException('Unreadable signing certificate');
}

$signerCertPem = '';
\openssl_x509_export($x509, $signerCertPem);
$signerCertDer = \Com\Tecnick\Pdf\Sign\Cms\Certificate::toDer($signerCertPem);

// The profile and the digest have to be the same on both sides: the PDF /SubFilter
// comes from the profile, and the CMS message digest from the digest algorithm.
$signConfig = new \Com\Tecnick\Pdf\Sign\Config(
    profile: \Com\Tecnick\Pdf\Sign\Config::PROFILE_PADES_B_B,
    digestAlgorithm: \Com\Tecnick\Pdf\Sign\DigestAlgorithm::Sha256,
    certType: 2,
);

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: \Com\Tecnick\Pdf\Page\Unit::Millimeter,
    isunicode: true,
    subsetfont: false,
    compress: true,
    mode: \Com\Tecnick\Pdf\PdfConformance::None,
    objEncrypt: null,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 084');
$pdf->setTitle('Two-Phase CMS Signing');
$pdf->setKeywords('TCPDF tc-lib-pdf example signature two-phase cms cades hsm remote key');
$pdf->setPDFFilename('E084_signed_two_phase_cms.pdf');
$pdf->enableDefaultPageContent();

$page = $pdf->addPage(['format' => 'A4']);

$basefont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);
$pdf->page->addContent($basefont['out']);

// Placeholder only: signcert and privkey stay empty, no key is read here.
$pdf
    ->signature()
    ->external()
    ->configure([
        'profile' => $signConfig->profile,
        'digest_algorithm' => $signConfig->digestAlgorithm,
        'cert_type' => 2,
        'info' => [
            'ContactInfo' => 'https://github.com/tecnickcom/tc-lib-pdf',
            'Location' => 'Remote key service',
            'Name' => 'Two-Phase Signer',
            'Reason' => 'Two-phase CAdES signature over the ByteRange digest',
        ],
        'password' => '',
        'privkey' => '',
        'signcert' => '',
    ]);

$sigPosX = 110.0;
$sigPosY = 250.0;
$sigWidth = 80.0;
$sigHeight = 20.0;

$pdf
    ->signature()
    ->appearance()
    ->place(
        posx: $sigPosX,
        posy: $sigPosY,
        width: $sigWidth,
        height: $sigHeight,
        page: $page['pid'],
        name: 'TwoPhaseSignature',
    );

$sigStamp = \gmdate('Y-m-d H:i:s') . ' UTC';
$sigTopY = $page['height'] - $sigHeight;

$sigTableHtml = <<<HTML
    <table border="1" cellpadding="1" cellspacing="0" style="width:80mm; border-color:#183a66; color:#1f2a33; font-size:9pt;">
      <tr>
        <td style="width:40mm;background-color:#d4e8ff;"><b>TWO-PHASE SIGNATURE</b></td>
        <td style="width:40mm;background-color:#a9d0ff;text-align:center;"><b>CAdES</b></td>
      </tr>
      <tr>
        <td colspan="2" style="background-color:#ffffcc;">Signed attributes signed remotely</td>
      </tr>
      <tr style="background-color:#ccffcc;">
        <td style="width:40mm;">Prepared:</td>
        <td style="width:40mm;text-align:center;"><b>{$sigStamp}</b></td>
      </tr>
    </table>
    HTML;

$sigAppearance = $basefont['out'];
$sigAppearance .= $pdf->getHTMLCell(html: $sigTableHtml, posx: 0, posy: $sigTopY, width: $sigWidth, height: $sigHeight);
$pdf->signature()->appearance()->stream(stream: $sigAppearance);

$instructionsHtml = <<<HTML
    <h1>Two-Phase CMS Signing (E084)</h1>
    <p>The private key signs the CMS signed attributes only. It never receives the document, and the
    document side never loads a key. The field uses the PAdES-BASELINE-B sub-filter
    <code>/ETSI.CAdES.detached</code>.</p>

    <h2>Workflow</h2>
    <ol>
      <li><b>Reserve the placeholder</b><br />
          <code>signature()-&gt;external()-&gt;configure()</code> with empty <code>signcert</code> and
          <code>privkey</code>, plus <code>appearance()-&gt;place()</code> for the visible widget.</li>
      <li><b>Hash the ByteRange</b><br />
          <code>signature()-&gt;external()-&gt;prepare('sha256')</code> returns the prepared PDF bytes, the
          <code>byte_range</code> tuple, and the digest of the covered bytes.</li>
      <li><b>Build the signing request</b><br />
          <code>Signer::prepare()</code> turns that digest and the signing certificate into a
          <code>SigningRequest</code>, and <code>Signer::signaturePayload()</code> renders it as the DER
          SET OF signed attributes (RFC 5652 section 5.4). <code>toArray()</code> and
          <code>fromArray()</code> carry the request across a queue or a second HTTP request, with an
          optional HMAC key.</li>
      <li><b>Sign remotely</b><br />
          The key service signs the payload bytes and returns the raw signature. Nothing else about the
          document crosses that boundary.</li>
      <li><b>Assemble and inject</b><br />
          <code>Signer::buildFromSignature()</code> rebuilds the same attributes, verifies the signature
          against them, and emits the detached CAdES.
          <code>signature()-&gt;external()-&gt;apply()</code> writes it into the reserved
          <code>/Contents</code>.</li>
    </ol>

    <h2>Difference from E075</h2>
    <p>Example E075 covers the case where a provider returns a
    complete CMS and the library only injects it, with a simulated payload that does not verify. Here the
    CMS is built locally around a signature made elsewhere, so the output is a real signature that a
    validator checks. The remote signer is simulated in-process with
    <code>openssl_sign()</code> over the bundled demo key, standing in for an HSM, a KMS, or a signing
    service.</p>

    <h2>Notes</h2>
    <ul>
      <li>The bundled certificate <code>examples/data/cert/tcpdf.crt</code> is self-signed, so a viewer
      reports a valid signature from an untrusted signer. Sign with a CA-issued certificate for a trusted
      chain.</li>
      <li>PAdES-BASELINE carries the signing time in the <code>/M</code> entry, so the CMS signing-time
      attribute is omitted; the profile decides this through the <code>Config</code> passed to both
      halves.</li>
      <li>A B-T or higher profile requires a timestamp client and transport in
      <code>buildFromSignature()</code>, and a larger reserved placeholder.</li>
    </ul>

    <h2>Run Modes</h2>
    <ul>
      <li><b>CLI default:</b> <code>save</code> mode writes both prepared and signed files under <code>target/</code>.</li>
      <li><b>CLI explicit:</b> <code>php examples/E084_signature_two_phase_cms.php save</code> or <code>render</code>.</li>
      <li><b>Web:</b> append <code>?mode=save</code> or <code>?mode=render</code>.</li>
    </ul>
    HTML;

$pdf->addHTMLCell(html: $instructionsHtml, posx: 15, posy: 20, width: 180);

$mode = PHP_SAPI === 'cli' ? 'save' : 'render';
if (PHP_SAPI === 'cli') {
    $mode = (string) ($argv[1] ?? 'save');
} elseif (isset($_GET['mode']) && \is_string($_GET['mode'])) {
    $mode = $_GET['mode'];
}

$mode = \strtolower(\trim($mode));
if (!\in_array($mode, ['render', 'save'], true)) {
    $mode = PHP_SAPI === 'cli' ? 'save' : 'render';
}

// Phase 1, document side: the ByteRange bytes and their digest.
$prepared = $pdf->signature()->external()->prepare($signConfig->digestAlgorithm);

$signer = new \Com\Tecnick\Pdf\Sign\Signer();
$signingTime = \time();
$request = $signer->prepare(
    messageDigest: $prepared['hash_raw'],
    signerCertDer: $signerCertDer,
    config: $signConfig,
    signingTime: $signingTime,
);

// The request is the only state the two phases share. Exported with a key it carries
// an HMAC, so a payload edited into another valid request is rejected on the way back.
$transportKey = 'demo-shared-secret';
$requestState = $request->toArray($transportKey);
$request = \Com\Tecnick\Pdf\Sign\Cms\SigningRequest::fromArray($requestState, $transportKey);

$payload = $signer->signaturePayload($request);

// Phase 2, key service: the bytes to sign are all it receives. Simulated here with the
// bundled demo key; a real deployment calls an HSM, a KMS, or a signing API.
$remoteSigner = static function (string $bytes) use ($certPath): string {
    $privateKey = \openssl_pkey_get_private('file://' . $certPath, '');
    if ($privateKey === false) {
        throw new \RuntimeException('Unreadable signing key');
    }

    $signature = '';
    if (!\openssl_sign($bytes, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
        throw new \RuntimeException('The remote signer failed to sign the payload');
    }

    return $signature;
};

$rawSignature = $remoteSigner($payload);

// Phase 1 again: the same attributes are derived from the request, the signature is
// checked against them, and the detached CAdES comes out.
$cms = $signer->buildFromSignature(request: $request, signature: $rawSignature, chainCertsDer: [], config: $signConfig);

// The CMS covers the ByteRange bytes, which are the prepared document without the
// /Contents gap. Verified before injection, so a mismatch surfaces here.
$verifiedCertDer = (new \Com\Tecnick\Pdf\Sign\Cms\SignedDataVerifier())->verify($cms, $prepared['prepared_pdf']);

$signedPdf = $pdf
    ->signature()
    ->external()
    ->apply(
        preparedPdf: $prepared['prepared_pdf'],
        byteRange: $prepared['byte_range'],
        signature: $cms,
        encoding: \Com\Tecnick\Pdf\Signature\ExternalSignatureEncoding::Binary,
    );

if ($mode === 'save') {
    $targetDir = \dirname(__DIR__) . '/target';
    if (!\is_dir($targetDir)) {
        \mkdir($targetDir, 0777, true);
    }

    $preparedPath = $targetDir . '/E084_prepared_unsigned_two_phase_cms.pdf';
    $signedPath = $targetDir . '/E084_signed_two_phase_cms.pdf';

    \file_put_contents($preparedPath, $prepared['prepared_pdf']);
    \file_put_contents($signedPath, $signedPdf);

    if (PHP_SAPI !== 'cli') {
        \header('Content-Type: text/plain; charset=utf-8');
    }

    echo "Prepared PDF: {$preparedPath}\n";
    echo "Signed PDF:   {$signedPath}\n";
    echo 'Digest (' . $prepared['algorithm'] . ', base64): ' . $prepared['hash_base64'] . "\n";
    echo 'Signed attributes: ' . \strlen($payload) . " bytes\n";
    echo 'Signature: ' . \strlen($rawSignature) . " bytes\n";
    echo 'CMS: ' . \strlen($cms) . " bytes\n";
    $verifiedCert = \openssl_x509_parse(\Com\Tecnick\Pdf\Sign\Cms\Certificate::derToPem($verifiedCertDer));
    echo 'Verified against: ' . ($verifiedCert === false ? '' : (string) $verifiedCert['name']) . "\n";
    exit();
}

$pdf->renderPDF(rawpdf: $signedPdf);
