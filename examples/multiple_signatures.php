<?php

/**
 * multiple_signatures.php
 *
 * Example demonstrating multiple digital signatures in a PDF document.
 *
 * @since       2025-01-01
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run `composer install` and `make fonts` in the project root first.

require __DIR__ . '/../vendor/autoload.php';

use Com\Tecnick\Pdf\Tcpdf;
use Com\Tecnick\Pdf\Signature\SignatureManager;

// Certificate paths
$certDir = __DIR__ . '/data/cert/';

// Output directory
$outputDir = realpath(__DIR__ . '/../target');
if ($outputDir === false) {
    mkdir(__DIR__ . '/../target', 0755, true);
    $outputDir = realpath(__DIR__ . '/../target');
}

// ============================================================================
// STEP 1: Create a PDF document
// ============================================================================

echo "Creating PDF document...\n";

$pdf = new Tcpdf(
    'mm',    // unit
    true,    // unicode
    false,   // subsetfont
    true,    // compress
    '',      // mode
    null     // encryption
);

$pdf->setCreator('tc-lib-pdf Multiple Signature Example');
$pdf->setAuthor('Example Author');
$pdf->setSubject('Multiple Digital Signatures Demo');
$pdf->setTitle('Multi-Signature PDF');

// Add a page with some content
$page = $pdf->addPage();

// Add some text (you would normally add more content here)
$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 14);
$pdf->page->addContent($font['out']);

// Simple text positioning
$textOut = "BT 50 750 Td (Multiple Digital Signatures Example) Tj ET\n";
$textOut .= "BT 50 720 Td (This document will be signed by multiple parties.) Tj ET\n";
$textOut .= "BT 50 690 Td (Each signature uses incremental PDF updates.) Tj ET\n";
$textOut .= "BT 50 650 Td (Signature 1: ____________________) Tj ET\n";
$textOut .= "BT 50 620 Td (Signature 2: ____________________) Tj ET\n";
$textOut .= "BT 50 590 Td (Signature 3: ____________________) Tj ET\n";
$pdf->page->addContent($textOut);

// Get the unsigned PDF
$unsignedPdf = $pdf->getOutPDFString();

// Save unsigned version for reference
$unsignedFile = $outputDir . '/unsigned_document.pdf';
file_put_contents($unsignedFile, $unsignedPdf);
echo "Unsigned PDF saved to: {$unsignedFile}\n";

// ============================================================================
// STEP 2: Apply First Signature
// ============================================================================

echo "\nApplying first signature...\n";

$signatureManager = new SignatureManager('mm');
$signatureManager->loadPdf($unsignedPdf);

$signedPdf = $signatureManager->sign([
    'certificate' => 'file://' . $certDir . 'signer1.crt',
    'privateKey' => 'file://' . $certDir . 'signer1.key',
    'password' => '',
    'hashAlgorithm' => 'sha256',
    'certType' => 2, // Permit form filling and signing
    'info' => [
        'name' => 'Signer One',
        'reason' => 'Initial Document Review',
        'location' => 'San Francisco, CA',
        'contact' => 'signer1@example.com',
    ],
    'appearance' => [
        'page' => 1,
        'x' => 120,
        'y' => 135,  // Near "Signature 1" line
        'width' => 60,
        'height' => 15,
    ],
]);

// Save after first signature
$signedOnceFile = $outputDir . '/signed_once.pdf';
file_put_contents($signedOnceFile, $signedPdf);
echo "PDF with 1 signature saved to: {$signedOnceFile}\n";

// ============================================================================
// STEP 3: Apply Second Signature (Incremental Update)
// ============================================================================

echo "\nApplying second signature...\n";

$signatureManager = new SignatureManager('mm');
$signatureManager->loadPdf($signedPdf);

$signedPdf = $signatureManager->sign([
    'certificate' => 'file://' . $certDir . 'signer2.crt',
    'privateKey' => 'file://' . $certDir . 'signer2.key',
    'password' => '',
    'hashAlgorithm' => 'sha256',
    'certType' => 2,
    'info' => [
        'name' => 'Signer Two',
        'reason' => 'Legal Department Approval',
        'location' => 'New York, NY',
        'contact' => 'signer2@example.com',
    ],
    'appearance' => [
        'page' => 1,
        'x' => 120,
        'y' => 125,  // Near "Signature 2" line
        'width' => 60,
        'height' => 15,
    ],
]);

// Save after second signature
$signedTwiceFile = $outputDir . '/signed_twice.pdf';
file_put_contents($signedTwiceFile, $signedPdf);
echo "PDF with 2 signatures saved to: {$signedTwiceFile}\n";

// ============================================================================
// STEP 4: Apply Third Signature (Another Incremental Update)
// ============================================================================

echo "\nApplying third signature...\n";

$signatureManager = new SignatureManager('mm');
$signatureManager->loadPdf($signedPdf);

$signedPdf = $signatureManager->sign([
    'certificate' => 'file://' . $certDir . 'signer3.crt',
    'privateKey' => 'file://' . $certDir . 'signer3.key',
    'password' => '',
    'hashAlgorithm' => 'sha256',
    'certType' => 2,
    'info' => [
        'name' => 'Signer Three',
        'reason' => 'Final Executive Approval',
        'location' => 'London, UK',
        'contact' => 'signer3@example.com',
    ],
    'appearance' => [
        'page' => 1,
        'x' => 120,
        'y' => 115,  // Near "Signature 3" line
        'width' => 60,
        'height' => 15,
    ],
]);

// Save final document with all signatures
$finalFile = $outputDir . '/signed_final.pdf';
file_put_contents($finalFile, $signedPdf);
echo "PDF with 3 signatures saved to: {$finalFile}\n";

// ============================================================================
// Summary
// ============================================================================

echo "\n" . str_repeat('=', 60) . "\n";
echo "MULTIPLE SIGNATURE EXAMPLE COMPLETED\n";
echo str_repeat('=', 60) . "\n";
echo "\nGenerated files:\n";
echo "  1. {$unsignedFile}\n";
echo "  2. {$signedOnceFile}\n";
echo "  3. {$signedTwiceFile}\n";
echo "  4. {$finalFile}\n";
echo "\nOpen these files in Adobe Acrobat Reader to verify the signatures.\n";
echo "Each signature should be valid and independent.\n";
