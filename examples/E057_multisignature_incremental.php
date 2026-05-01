<?php
/**
 * E057_multisignature_incremental.php
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
 * Demonstrate a multi-signature approval flow.
 *
 * In a real approval workflow a document typically accumulates one signature
 * per approver in sequence (incremental signatures).  tc-lib-pdf supports this
 * pattern by:
 *
 *  1. setSignature()               – primary signing certificate (author / certifier)
 *  2. setSignatureAppearance()     – visible widget for the primary signature
 *  3. addEmptySignatureAppearance() – empty widget placeholders for each subsequent approver
 *
 * The primary signature field (cert_type 2 = MDP/Author) locks the document
 * against further modifications while still permitting subsequent approvals to
 * fill in the reserved empty fields.
 *
 * This example creates a 3-page document:
 *   Page 1 – contract / document body
 *   Page 2 – approval form with 3 signature fields:
 *              · Author     (primary – pre-signed by the library)
 *              · Reviewer   (empty approval field)
 *              · Manager    (empty approval field)
 *   Page 3 – signing guide / workflow description
 */

$certPath = \realpath(__DIR__ . '/data/cert/tcpdf.crt');
if ($certPath === false) {
    throw new \RuntimeException('Missing signing certificate: examples/data/cert/tcpdf.crt');
}

$cert = 'file://' . $certPath;

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 057');
$pdf->setTitle('Multi-Signature Approval Flow');
$pdf->setKeywords('TCPDF tc-lib-pdf example signature approval multi-signature incremental');
$pdf->setPDFFilename('E057_multisignature_incremental.pdf');

$pdf->enableDefaultPageContent();

$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);
$bfontB = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 12);

// -----------------------------------------------------------------------
// Page 1 – Document body (contract)
// -----------------------------------------------------------------------
$page1 = $pdf->addPage();
$pdf->page->addContent($bfontB['out']);
$pdf->page->addContent($pdf->getTextCell('SERVICE AGREEMENT EXAMPLE', 15, 15, 180, 0, 0, 1, 'T', 'C'));
$pdf->page->addContent($bfont['out']);
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$html1 = <<<HTML
<p>This Service Agreement ("Agreement") is entered into as of the date of the last signature below
between <strong>Client</strong> and <strong>Service Provider</strong>.</p>

<p><b>1. Services.</b> Service Provider agrees to deliver software development services as described
in Schedule A attached hereto.</p>

<p><b>2. Payment.</b> Client agrees to pay the fees set forth in Schedule B within 30 days of invoice.</p>

<p><b>3. Term.</b> This Agreement commences on the Effective Date and continues for 12 months unless
earlier terminated by either party upon 30 days written notice.</p>

<p><b>4. Confidentiality.</b> Each party agrees to keep the other party's confidential information
strictly confidential and not to disclose it to any third party without prior written consent.</p>

<p><b>5. Governing Law.</b> This Agreement shall be governed by the laws of the applicable jurisdiction.</p>

<p>The parties have executed this Agreement as of the dates indicated in the signature fields on
the following page.</p>
HTML;

$pdf->addHTMLCell($html1, 15, 30, 180);

// -----------------------------------------------------------------------
// Page 2 – Approval / signature form
// -----------------------------------------------------------------------
$page2 = $pdf->addPage();
$pdf->page->addContent($bfontB['out']);
$pdf->page->addContent($pdf->getTextCell('SIGNATURE PAGE EXAMPLE', 15, 15, 180, 0, 0, 1, 'T', 'C'));
$pdf->page->addContent($bfont['out']);
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$pdf->page->addContent(
    $pdf->getTextCell('The following parties have reviewed and approved this Agreement:', 15, 30, 180, 0, 0, 1, 'T', 'L')
);

// Signature box helper labels (rendered as PDF text cells)
$labels = [
    ['role' => 'Author / Certifier',  'name' => 'John Doe',         'y' => 45],
    ['role' => 'Reviewer',            'name' => '(awaiting sig.)',  'y' => 90],
    ['role' => 'Manager',             'name' => '(awaiting sig.)',  'y' => 135],
];

foreach ($labels as $lbl) {
    $pdf->page->addContent($bfontB['out']);
    $pdf->page->addContent(
        $pdf->getTextCell($lbl['role'], 15, (float) $lbl['y'], 85, 0, 0, 1, 'T', 'L')
    );
    $pdf->page->addContent($bfont['out']);
    $pdf->page->addContent(
        $pdf->getTextCell('Name: ' . $lbl['name'], 15, (float) ($lbl['y'] + 7), 85, 0, 0, 1, 'T', 'L')
    );
    $pdf->page->addContent(
        $pdf->getTextCell('Date: ___________________', 15, (float) ($lbl['y'] + 14), 85, 0, 0, 1, 'T', 'L')
    );
}

// -----------------------------------------------------------------------
// Register the primary (author/certifier) digital signature.
// cert_type = 2  →  MDP / author signature
// -----------------------------------------------------------------------
$pdf->setSignature([
    'cert_type' => 2,
    'info' => [
        'ContactInfo' => 'https://github.com/tecnickcom/tc-lib-pdf',
        'Location'    => 'Demo Office',
        'Name'        => 'John Doe',
        'Reason'      => 'Author / certifier signature – approval workflow example',
    ],
    'password'  => '',
    'privkey'   => $cert,
    'signcert'  => $cert,
]);

// Primary signature appearance – placed on page 2 at the first slot.
$pdf->setSignatureAppearance(100, 45, 90, 35, $page2['pid'], 'Author');

// Empty approval fields for the remaining approvers.
$pdf->addEmptySignatureAppearance(100, 90,  90, 35, $page2['pid'], 'Reviewer');
$pdf->addEmptySignatureAppearance(100, 135, 90, 35, $page2['pid'], 'Manager');

// -----------------------------------------------------------------------
// Page 3 – Workflow guide
// -----------------------------------------------------------------------
$page3 = $pdf->addPage();
$pdf->page->addContent($bfontB['out']);
$pdf->page->addContent($pdf->getTextCell('Multi-Signature Workflow Guide', 15, 15, 180, 0, 0, 1, 'T', 'L'));
$pdf->page->addContent($bfont['out']);
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$html3 = <<<HTML
<p><b>How incremental multi-signature works in tc-lib-pdf</b></p>

<p>A PDF approval workflow typically progresses through these steps:</p>
<ol>
  <li><b>Author creates and certifies the document.</b>
  The primary signature (Author field) is applied by <code>setSignature()</code> with
  <em>cert_type 2</em> (MDP). This locks the document against structural changes
  while reserving the empty approval fields for subsequent signers.</li>

  <li><b>Reviewer opens the document, validates the Author signature, and applies their
  signature to the Reviewer field.</b> In a PDF viewer (e.g. Acrobat) the reviewer
  uses the reserved empty widget.  Each subsequent signature is appended to the file
  as an incremental update, preserving all prior signature data.</li>

  <li><b>Manager repeats the same process for the Manager field.</b></li>
</ol>

<p><b>tc-lib-pdf API summary</b></p>
<table border="1" cellpadding="4" cellspacing="0">
  <tr><th>Method</th><th>Purpose</th></tr>
  <tr><td>setSignature()</td><td>Register the primary signing certificate and options.</td></tr>
  <tr><td>setSignatureAppearance()</td><td>Visible widget for the primary signature.</td></tr>
  <tr><td>addEmptySignatureAppearance()</td><td>Reserve an empty widget for a future approver.</td></tr>
  <tr><td>setSignTimeStamp()</td><td>Optionally attach an RFC 3161 TSA timestamp (see E008).</td></tr>
</table>

<p><b>Certification levels (cert_type)</b></p>
<table border="1" cellpadding="4" cellspacing="0">
  <tr><th>cert_type</th><th>Meaning</th></tr>
  <tr><td>1</td><td>No changes permitted after signing.</td></tr>
  <tr><td>2</td><td>Form fills and approval signatures permitted (MDP).</td></tr>
  <tr><td>3</td><td>Annotations and form fills permitted.</td></tr>
</table>
HTML;

$pdf->addHTMLCell($html3, 15, 30, 180);

// -----------------------------------------------------------------------
// Output
// -----------------------------------------------------------------------
$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
