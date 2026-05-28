<?php

/**
 * E075_external_signature_injection.php
 *
 * Demonstrates a remote/external signing workflow using a signature placeholder,
 * ByteRange hash export, and later CMS/PKCS#7 injection.
 *
 * @since       2026-05-28
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

require __DIR__ . '/../vendor/autoload.php';

define('K_PATH_FONTS', (string) realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    isunicode: true,
    subsetfont: false,
    compress: true,
    mode: '',
    objEncrypt: null,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 075');
$pdf->setTitle('External Signature Injection Workflow');
$pdf->setKeywords('TCPDF tc-lib-pdf example external signature remote pkcs7 byterange');
$pdf->setPDFFilename('E075_signed_external_signature_injection.pdf');
$pdf->enableDefaultPageContent();

$page = $pdf->addPage(['format' => 'A4']);

$basefont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);
$pdf->page->addContent($basefont['out']);

$pdf->setSignatureForExternalSigning([
    'cert_type' => 2,
    'info' => [
        'ContactInfo' => 'https://github.com/tecnickcom/tc-lib-pdf',
        'Location' => 'Remote signing service',
        'Name' => 'External Signer',
        'Reason' => 'Remote detached CMS signature injection demo',
    ],
    'password' => '',
    'privkey' => '',
    'signcert' => '',
]);

$sigPosX = 100.0;
$sigPosY = 220.0;
$sigWidth = 80.0;
$sigHeight = 20.0;

$pdf->setSignatureAppearance(
    posx: $sigPosX,
    posy: $sigPosY,
    width: $sigWidth,
    heigth: $sigHeight,
    page: $page['pid'],
    name: 'Remote Approval',
);

// Custom /AP stream to make the signature widget visually obvious.
$sigStamp = \gmdate('Y-m-d H:i:s') . ' UTC';
$sigTopY = $page['height'] - $sigHeight;

// Build the signature appearance stream directly from HTML table markup.
$sigAppearance = $basefont['out'];

$sigTableHtml = <<<HTML
    <table border="1" cellpadding="1" cellspacing="0" style="width:80mm; border-color:#183a66; color:#1f2a33; font-size:9pt;">
      <tr>
        <td style="width:40mm;background-color:#d4e8ff;"><b>REMOTE SIGNATURE</b></td>
        <td style="width:40mm;background-color:#a9d0ff;text-align:center;"><b>CMS/PKCS#7</b></td>
      </tr>
      <tr>
        <td colspan="2" style="background-color:#ffffcc;">External signature provider</td>
      </tr>
      <tr style="background-color:#ccffcc;">
        <td style="width:40mm;">Prepared:</td>
        <td style="width:40mm;text-align:center;"><b>{$sigStamp}</b></td>
      </tr>
    </table>
    HTML;

$sigAppearance .= $pdf->getHTMLCell(html: $sigTableHtml, posx: 0, posy: $sigTopY, width: $sigWidth, height: $sigHeight);
$pdf->setSignatureAppearanceStream(stream: $sigAppearance);

$instructionsHtml = <<<HTML
    <h1>External Signature Injection (E075)</h1>
    <p>This example demonstrates a full remote-signing flow where the private key never lives in your application.</p>

    <h2>Workflow</h2>
    <ol>
      <li><b>Create placeholder</b><br />
          Configure a signature field and reserve <code>/Contents</code> bytes using
          <code>setSignatureForExternalSigning()</code> and <code>setSignatureAppearance()</code>.</li>
      <li><b>Prepare and hash</b><br />
          Call <code>getExternalSignaturePreparation('sha256')</code> to receive:
          <ul>
            <li>prepared PDF bytes with final <code>/ByteRange</code>,</li>
            <li>the exact <code>byte_range</code> tuple,</li>
            <li>digest in raw, hex, and base64 formats.</li>
          </ul>
      </li>
      <li><b>Remote sign</b><br />
          Send the digest (typically <code>hash_base64</code>) to your external provider,
          such as HSM/KMS or gov.br signing API, and receive CMS/PKCS#7 detached signature bytes.</li>
      <li><b>Inject signature</b><br />
          Call <code>applyExternalSignature(preparedPdf, byteRange, cmsSignature, encoding)</code>
          with <code>encoding = binary|base64|hex</code> to produce the final signed PDF bytes.</li>
    </ol>

    <h2>This Demo Uses a Fake External Response</h2>
    <p>To keep the example self-contained, it injects a simulated CMS payload.
    The resulting PDF proves the integration mechanics, but the signature is expected to be cryptographically invalid.</p>

    <h2>Run Modes</h2>
    <ul>
      <li><b>CLI default:</b> <code>save</code> mode writes both prepared and signed files under <code>target/</code>.</li>
      <li><b>CLI explicit:</b> <code>php examples/E075_external_signature_injection.php save</code> or <code>render</code>.</li>
      <li><b>Web:</b> append <code>?mode=save</code> or <code>?mode=render</code>.</li>
    </ul>
    HTML;

$pdf->addHTMLCell(html: $instructionsHtml, posx: 15, posy: 20, width: 180);

$mode = PHP_SAPI === 'cli' ? 'save' : 'render';
if (PHP_SAPI === 'cli') {
    $mode = (string) ($argv[1] ?? 'save');
} elseif (isset($_GET['mode']) && is_string($_GET['mode'])) {
    $mode = $_GET['mode'];
}

$mode = strtolower(trim($mode));
if (!in_array($mode, ['render', 'save'], true)) {
    $mode = PHP_SAPI === 'cli' ? 'save' : 'render';
}

$prepared = $pdf->getExternalSignaturePreparation('sha256');

// Simulated remote CMS response for demonstration only.
$fakeRemoteCmsSignature = 'DEMO-REMOTE-CMS:' . $prepared['hash_raw'];
$signedPdf = $pdf->applyExternalSignature(
    preparedPdf: $prepared['prepared_pdf'],
    byteRange: $prepared['byte_range'],
    signature: $fakeRemoteCmsSignature,
    encoding: 'binary',
);

if ($mode === 'save') {
    $targetDir = \dirname(__DIR__) . '/target';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $preparedPath = $targetDir . '/E075_prepared_unsigned_external_signature.pdf';
    $signedPath = $targetDir . '/E075_signed_demo_external_signature_injection.pdf';

    file_put_contents($preparedPath, $prepared['prepared_pdf']);
    file_put_contents($signedPath, $signedPdf);

    if (PHP_SAPI !== 'cli') {
        header('Content-Type: text/plain; charset=utf-8');
    }

    echo "Prepared PDF: {$preparedPath}\n";
    echo "Signed PDF:   {$signedPath}\n";
    echo 'Digest (sha256, base64): ' . $prepared['hash_base64'] . "\n";
    echo "\n";
    echo "Note: Signed output uses a simulated external CMS payload for demo purposes.\n";
    exit();
}

$pdf->renderPDF(rawpdf: $signedPdf);
