<?php
/**
 * timestamp_signature.php
 *
 * Example demonstrating RFC 3161 timestamped signatures.
 *
 * @since       2025-01-02
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');

use Com\Tecnick\Pdf\Signature\SignatureManager;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

/**
 * This example demonstrates how to add RFC 3161 timestamps to PDF signatures.
 *
 * Timestamps provide proof that a document was signed at a specific time,
 * which is essential for long-term validation (LTV) of digital signatures.
 *
 * Free TSA servers for testing:
 * - http://timestamp.digicert.com
 * - http://timestamp.sectigo.com
 * - http://tsa.starfieldtech.com
 * - http://freetsa.org/tsr (requires specific format)
 *
 * Note: For production use, you should use a TSA from a trusted provider.
 */

// Path to the PDF to sign
$inputPdf = __DIR__ . '/../target/example.pdf';

// Check if input PDF exists
if (!file_exists($inputPdf)) {
    echo "Input PDF not found. Please run index.php first to generate example.pdf\n";
    exit(1);
}

// Path to certificate (use existing test certificate)
$certPath = __DIR__ . '/data/cert/tcpdf.crt';

// Check if certificate exists
if (!file_exists($certPath)) {
    echo "Certificate not found at: $certPath\n";
    echo "Please ensure the test certificate exists.\n";
    exit(1);
}

// Create signature manager
$sigManager = new SignatureManager('mm');

// Load the PDF
$sigManager->loadPdf($inputPdf);

// Configure signature with timestamp
$signatureConfig = [
    'certificate' => 'file://' . $certPath,
    'privateKey' => 'file://' . $certPath,
    'password' => 'tcpdfdemo',
    'hashAlgorithm' => 'sha256',
    'info' => [
        'name' => 'John Doe',
        'reason' => 'Document approval with timestamp',
        'location' => 'Office',
        'contact' => 'john@example.com',
    ],
    'appearance' => [
        'page' => 1,
        'x' => 10,
        'y' => 10,
        'width' => 50,
        'height' => 20,
    ],
    // RFC 3161 Timestamp configuration
    'timestamp' => [
        'url' => 'http://timestamp.digicert.com',
        // Optional authentication (not required for DigiCert)
        // 'username' => 'user',
        // 'password' => 'pass',
        'timeout' => 30,
    ],
];

try {
    // Sign the PDF with timestamp
    echo "Signing PDF with RFC 3161 timestamp...\n";
    $signedPdf = $sigManager->sign($signatureConfig);

    // Save the signed PDF
    $outputPath = __DIR__ . '/../target/signed_with_timestamp.pdf';
    file_put_contents($outputPath, $signedPdf);

    echo "Signed PDF saved to: $outputPath\n";
    echo "The signature includes an RFC 3161 timestamp from DigiCert TSA.\n";
    echo "\nTo verify the timestamp:\n";
    echo "1. Open the PDF in Adobe Acrobat Reader\n";
    echo "2. Click on the signature\n";
    echo "3. Check 'Signature Properties' -> 'Date/Time'\n";
    echo "   It should show 'Signature is timestamped' with TSA details.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";

    // If timestamp fails, try signing without it
    echo "\nAttempting to sign without timestamp...\n";
    unset($signatureConfig['timestamp']);

    try {
        $signedPdf = $sigManager->sign($signatureConfig);
        $outputPath = __DIR__ . '/../target/signed_no_timestamp.pdf';
        file_put_contents($outputPath, $signedPdf);
        echo "Signed PDF (without timestamp) saved to: $outputPath\n";
    } catch (\Exception $e2) {
        echo "Error signing without timestamp: " . $e2->getMessage() . "\n";
    }
}
