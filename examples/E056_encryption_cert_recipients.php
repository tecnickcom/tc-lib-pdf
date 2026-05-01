<?php
/**
 * E056_encryption_cert_recipients.php
 *
 * @since       2026-05-01
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

/**
 * Demonstrate certificate-based (public-key) PDF encryption with a permission matrix.
 *
 * Certificate-based encryption lets you define a list of recipients; each recipient's
 * public-key certificate is used to wrap the document key.  Only holders of the
 * corresponding private key can decrypt the document.  No shared password is required.
 *
 * The example creates two logical recipients from the same demo certificate to show
 * how different permission sets can be defined per-recipient:
 *   Recipient A – read-only  (print only)
 *   Recipient B – reviewer   (print + annotate)
 *
 * The demo certificate shipped with the examples is a self-signed X.509 cert.
 * To create your own certificate:
 *   openssl req -x509 -nodes -days 365000 -newkey rsa:2048 \
 *               -keyout cert.pem -out cert.pem
 */

$certPath = \realpath(__DIR__ . '/data/cert/tcpdf.crt');
if ($certPath === false) {
    throw new \RuntimeException('Missing demo certificate: examples/data/cert/tcpdf.crt');
}

$certUri = 'file://' . $certPath;

$mode = (PHP_SAPI === 'cli') ? 'encrypted' : 'preview';
if (PHP_SAPI === 'cli') {
  $mode = (string) ($argv[1] ?? 'encrypted');
} elseif (isset($_GET['mode']) && \is_string($_GET['mode'])) {
  $mode = $_GET['mode'];
}

$mode = \strtolower(\trim($mode));
if (! \in_array($mode, ['encrypted', 'preview'], true)) {
  $mode = (PHP_SAPI === 'cli') ? 'encrypted' : 'preview';
}

$useEncryption = ($mode === 'encrypted');

// Build recipient list:
//   Each entry has 'c' (certificate path/URI) and 'p' (allowed permissions array).
$pubkeys = [
    [
        'c' => $certUri,
        'p' => ['print'],           // Recipient A: print-only
    ],
    [
        'c' => $certUri,
        'p' => ['print', 'annot-forms'],  // Recipient B: print + annotate
    ],
];

$fileId = \md5('E056_encryption_cert_recipients');

$encrypt = null;
if ($useEncryption) {
  // AES-128 (mode 2) with certificate recipients – no shared password needed.
  $encrypt = new \Com\Tecnick\Pdf\Encrypt\Encrypt(
    true,          // enabled
    $fileId,
    2,             // mode: AES-128
    [],            // global permissions (empty – controlled per-recipient)
    '',            // no user password
    '',            // no owner password
    $pubkeys,      // certificate recipients
    true,          // encryptMetadata
    true           // encryptEmbeddedFiles
  );
}

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm',
    true,
    false,
    true,
    '',
    $encrypt
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 056');
$pdf->setTitle('Certificate-Based Recipient Encryption');
$pdf->setKeywords('TCPDF tc-lib-pdf example encryption certificate recipients public-key');
$pdf->setPDFFilename('E056_encryption_cert_recipients.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);
$bfontB = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 12);

// -----------------------------------------------------------------------
// Page 1 – Overview
// -----------------------------------------------------------------------
$page1 = $pdf->addPage();
$pdf->page->addContent($bfontB['out']);
$pdf->page->addContent($pdf->getTextCell('Certificate-Based Recipient Encryption', 15, 15, 180, 0, 0, 1, 'T', 'L'));
$pdf->page->addContent($bfont['out']);
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$html = <<<HTML
<p><b>How it works</b></p>
<p>Certificate-based encryption (also called <em>public-key encryption</em>) wraps the
document encryption key using each recipient's X.509 public-key certificate.
No shared password is distributed; only the holder of the matching private key can
open the document.</p>

<p><b>Recipient permission matrix</b></p>
<table border="1" cellpadding="4" cellspacing="0">
  <tr>
    <th>Recipient</th>
    <th>Certificate</th>
    <th>Allowed permissions</th>
  </tr>
  <tr>
    <td>Recipient A (read-only)</td>
    <td>tcpdf.crt (demo)</td>
    <td>print</td>
  </tr>
  <tr>
    <td>Recipient B (reviewer)</td>
    <td>tcpdf.crt (demo)</td>
    <td>print, annot-forms</td>
  </tr>
</table>

<p><b>Encryption parameters</b></p>
<ul>
  <li>Output mode: MODE_PLACEHOLDER</li>
  <li>Algorithm: AES-128 (mode 2) when mode is <em>encrypted</em></li>
  <li>Metadata encrypted: yes (encrypted mode)</li>
  <li>Embedded files encrypted: yes (encrypted mode)</li>
  <li>User/owner password: none (certificate recipients only, encrypted mode)</li>
</ul>

<p><b>Creating your own certificate</b></p>
<p>Use the following OpenSSL command to generate a self-signed certificate suitable for
recipient encryption:</p>
<p><em>openssl req -x509 -nodes -days 365000 -newkey rsa:2048 -keyout cert.pem -out cert.pem</em></p>
HTML;

$modeLabel = $useEncryption
  ? 'encrypted (certificate recipients)'
  : 'preview (unencrypted for browser compatibility)';

$html = \str_replace('MODE_PLACEHOLDER', $modeLabel, $html);

$pdf->addHTMLCell($html, 15, 30, 180);

// -----------------------------------------------------------------------
// Page 2 – Permissions reference
// -----------------------------------------------------------------------
$page2 = $pdf->addPage();
$pdf->page->addContent($bfontB['out']);
$pdf->page->addContent($pdf->getTextCell('PDF Permission Flags Reference', 15, 15, 180, 0, 0, 1, 'T', 'L'));
$pdf->page->addContent($bfont['out']);
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$html2 = <<<HTML
<p><b>Available permission identifiers</b></p>
<table border="1" cellpadding="4" cellspacing="0">
  <tr><th>Identifier</th><th>Description</th></tr>
  <tr><td>print</td><td>Print the document (low-quality).</td></tr>
  <tr><td>modify</td><td>Modify document content (not covered by more specific flags).</td></tr>
  <tr><td>copy</td><td>Copy or extract text and graphics.</td></tr>
  <tr><td>annot-forms</td><td>Add/modify annotations and fill interactive forms.</td></tr>
  <tr><td>fill-forms</td><td>Fill in existing interactive form fields.</td></tr>
  <tr><td>extract</td><td>Extract content for accessibility.</td></tr>
  <tr><td>assemble</td><td>Insert/rotate/delete pages, create bookmarks and thumbnails.</td></tr>
  <tr><td>print-high</td><td>Print at high quality (faithful digital reproduction).</td></tr>
</table>

<p><b>Notes</b></p>
<ul>
  <li>With certificate recipients, permission flags are embedded inside the CMS enveloped-data
  structure for each recipient separately.</li>
  <li>Viewer enforcement of permissions depends on the PDF reader implementation.</li>
  <li>Combining certificate encryption with a digital signature (E057) provides both
  access control and document integrity assurance.</li>
</ul>
HTML;

$pdf->addHTMLCell($html2, 15, 30, 180);

// -----------------------------------------------------------------------
// Output
// -----------------------------------------------------------------------
$rawpdf = $pdf->getOutPDFString();

if (PHP_SAPI !== 'cli') {
    if ($useEncryption) {
        // Most browser-native PDF viewers do not support certificate-based decryption.
        // Force a download so the file can be opened with a compatible PDF reader.
        $pdf->downloadPDF($rawpdf);
        exit;
    }

    $pdf->renderPDF($rawpdf);
    exit;
}

echo $rawpdf;
