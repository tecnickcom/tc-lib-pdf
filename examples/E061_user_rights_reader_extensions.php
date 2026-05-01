<?php
/**
 * E061_user_rights_reader_extensions.php
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
// NOTE: This example requires a signing certificate.  Run the following to create a self-signed one:
//   cd examples/data/cert
//   openssl req -x509 -nodes -days 365000 -newkey rsa:2048 -keyout tcpdf.crt -out tcpdf.crt \
//     -subj "/CN=tc-lib-pdf demo"

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

/**
 * Demonstrate Adobe Reader extension rights via setUserRights().
 *
 * Background
 * ──────────
 * Standard PDF encryption (setEncrypt / E045) controls which operations a
 * user is *prevented* from performing.  That is a restriction model.
 *
 * Reader Extensions (UR — Usage Rights) are a complementary *enablement*
 * model: they grant additional privileges to free Adobe Reader users that
 * Reader would otherwise reserve for Acrobat Pro.  Common examples:
 *   • Filling in forms and saving the filled data
 *   • Creating, modifying, and deleting annotations (comments, stamps, etc.)
 *   • Signing existing signature fields
 *   • Exporting embedded files
 *
 * Implementation
 * ──────────────
 * UR rights are embedded via a special /UR3 signature object (SubFilter:
 * adbe.pkcs7.detached with cert_type = 0, i.e. no DocMDP constraint).
 * This is distinct from a CMS approval or certification signature:
 *   • cert_type > 0  → DocMDP  (document modification detection / certification)
 *   • cert_type = 0  → UR      (usage-rights extension)
 *
 * setUserRights() accepts an array of right categories:
 *   enabled    (bool)   must be true to activate UR output
 *   document   (string) document-level rights  e.g. '/FullSave'
 *   form       (string) form-filling rights    e.g. '/FillIn /Export'
 *   formex     (string) form extras            e.g. '/BarcodePlaintext'
 *   annots     (string) annotation rights      e.g. '/Create /Delete /Modify /Copy /Import /Export'
 *   signature  (string) signing rights         e.g. '/Modify'
 *   ef         (string) embedded-file rights   e.g. '/Create /Delete /Modify /Import'
 *
 * The companion setSignature() call wires the UR3 signature using the
 * supplied certificate.  cert_type = 0 selects the UR branch in the output
 * layer (see Output::getOutSignatureUserRights).
 *
 * IMPORTANT: UR rights are only honoured by Adobe Reader / Acrobat.  Most
 * open-source readers do not enforce or display UR distinctions.
 */

$certPath = \realpath(__DIR__ . '/data/cert/tcpdf.crt');
if ($certPath === false) {
    throw new \RuntimeException(
        'Missing signing certificate: examples/data/cert/tcpdf.crt' . "\n"
        . 'Create it with:' . "\n"
        . '  openssl req -x509 -nodes -days 365000 -newkey rsa:2048 \\' . "\n"
        . '    -keyout examples/data/cert/tcpdf.crt -out examples/data/cert/tcpdf.crt \\' . "\n"
        . '    -subj "/CN=tc-lib-pdf demo"'
    );
}

$cert = 'file://' . $certPath;

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 061');
$pdf->setTitle('Reader Extension Rights (UR Signature)');
$pdf->setKeywords('TCPDF tc-lib-pdf example user rights reader extensions UR signature form annotation');
$pdf->setPDFFilename('E061_user_rights_reader_extensions.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// -----------------------------------------------------------------------
// Enable Reader Extension Rights
// -----------------------------------------------------------------------

// Grant form-filling, commenting, and signing rights to free Adobe Reader.
$pdf->setUserRights([
    'enabled'   => true,
    'document'  => '/FullSave',
    'form'      => '/FillIn /Export',
    'annots'    => '/Create /Delete /Modify /Copy /Import /Export',
    'signature' => '/Modify',
    'ef'        => '/Create /Delete /Modify /Import',
]);

// Wire the UR3 signature (cert_type = 0 → UR path, not DocMDP).
$pdf->setSignature([
    'cert_type' => 0,     // 0 = UR, not DocMDP certification
    'password'  => '',
    'privkey'   => $cert,
    'signcert'  => $cert,
    'info'      => [
        'Name'        => 'tc-lib-pdf',
        'Location'    => 'Demo',
        'Reason'      => 'Enabling Reader Extension Rights',
        'ContactInfo' => 'https://github.com/tecnickcom/tc-lib-pdf',
    ],
]);

// -----------------------------------------------------------------------
// Add a simple interactive text field to exercise form-filling rights
// -----------------------------------------------------------------------
$bfont  = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);
$bfontB = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 14);

$page1 = $pdf->addPage();
$pdf->page->addContent($bfont['out']);
// Re-insert non-bold base font so the font stack is in a clean state
// before addHTMLCell starts its height-estimation pass.
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);

// Info block
$html = <<<'HTML'
<h1 style="font-size:16pt; color:#003366;">Reader Extension Rights (UR Signature)</h1>
<p style="font-size:10pt; color:#333333;">
This PDF uses a /UR3 usage-rights signature to grant additional privileges to
free Adobe Reader users beyond the default read-only experience.
</p>

<h2 style="font-size:13pt; color:#005599;">Rights enabled in this document</h2>
<table border="0" cellpadding="3">
  <tr><td style="font-size:9pt; color:#003366; font-weight:bold; width:90mm;">Category</td>
      <td style="font-size:9pt; color:#003366; font-weight:bold;">Granted rights</td></tr>
  <tr><td style="font-size:9pt;">Document</td>
      <td style="font-size:9pt;">/FullSave — Reader may save a filled-in copy</td></tr>
  <tr><td style="font-size:9pt;">Form</td>
      <td style="font-size:9pt;">/FillIn /Export — fill fields and export data</td></tr>
  <tr><td style="font-size:9pt;">Annotations</td>
      <td style="font-size:9pt;">/Create /Delete /Modify /Copy /Import /Export</td></tr>
  <tr><td style="font-size:9pt;">Signature</td>
      <td style="font-size:9pt;">/Modify — apply or clear a digital signature</td></tr>
  <tr><td style="font-size:9pt;">Embedded files</td>
      <td style="font-size:9pt;">/Create /Delete /Modify /Import</td></tr>
</table>

<h2 style="font-size:13pt; color:#005599;">Interactive field (test form-fill right)</h2>
<p style="font-size:9pt; color:#333333;">
The text field below can be filled and <b>saved</b> in free Adobe Reader because
the FullSave document right has been enabled.  Without UR rights, Reader would
only allow printing a filled-in copy, not saving it.
</p>
HTML;

$pdf->addHTMLCell($html, 15, 20, 180);

// Add a text field that exercises the form-filling right.
$ffid = $pdf->addFFText('ur_test_field', 15, 140, 120, 8, ['value' => 'Type here and save...']);
$pdf->page->addAnnotRef($ffid);

// Label for the field.
// Re-insert non-bold font before getTextCell to keep the font stack clean.
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);
$pdf->page->addContent(
    $pdf->getTextCell(
        'Saveable text field:',
        15.0,
        133.0,
        60.0,
        6.0,
        drawcell: false,
        valign: 'M',
        halign: 'L',
    )
);

// -----------------------------------------------------------------------
// Page 2 — Contrast with DocMDP certification (cert_type > 0)
// -----------------------------------------------------------------------
$page2 = $pdf->addPage();
// Re-insert non-bold base font before addHTMLCell.
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$html2 = <<<'HTML'
<h1 style="font-size:16pt; color:#003366;">UR vs DocMDP — Key Differences</h1>

<h2 style="font-size:13pt; color:#005599;">cert_type = 0 → Usage Rights (UR3) — this document</h2>
<p style="font-size:9pt; color:#333333;">
Purpose: <b>grant</b> extra privileges to Reader.<br />
Signature type: /UR3 — not a DocMDP certification.<br />
Does not constrain future modifications.<br />
Reader shows a "Reader Extensions" badge rather than a certification badge.<br />
Created via: <code>setUserRights([...]) + setSignature(['cert_type' =&gt; 0, ...])</code>
</p>

<h2 style="font-size:13pt; color:#005599;">cert_type = 1/2/3 → DocMDP Certification</h2>
<p style="font-size:9pt; color:#333333;">
Purpose: <b>certify</b> authorship and <b>restrict</b> allowed changes.<br />
cert_type 1 = no changes allowed; 2 = form-filling + signing; 3 = + annotations.<br />
Reader shows a certification badge; further changes break the signature seal.<br />
Created via: <code>setSignature(['cert_type' =&gt; 2, ...])</code> (see E007–E009)
</p>

<h2 style="font-size:13pt; color:#005599;">Standard encryption (E045) vs UR rights</h2>
<p style="font-size:9pt; color:#333333;">
Standard PDF encryption sets a <b>permission bitmap</b> that <b>prevents</b> operations
(printing, copying, modifying) unless the owner password is supplied.<br />
UR rights are orthogonal: they <b>enable</b> specific Reader features that are
otherwise locked regardless of password protection.  Both mechanisms can be
combined in the same document.
</p>
HTML;

$pdf->addHTMLCell($html2, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
