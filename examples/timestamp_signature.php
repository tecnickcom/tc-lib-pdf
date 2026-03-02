<?php
/**
 * timestamp_signature.php
 *
 * Example demonstrating RFC 3161 timestamped signatures.
 *
 * @category    Library
 * @package     Pdf
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');

use Com\Tecnick\Pdf\Signature\SignatureManager;
use Com\Tecnick\Pdf\Signature\TimestampClient;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "RFC 3161 Timestamp Signature Example\n";
echo "=====================================\n\n";

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

// =========================================================================
// Step 1: Create a simple PDF to sign
// =========================================================================

echo "1. Creating a simple PDF document...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('RFC 3161 Timestamp Example');
$pdf->setTitle('Timestamped Document');
$pdf->setKeywords('TCPDF, PDF, timestamp, RFC3161, signature');

// Enable default page content
$pdf->enableDefaultPageContent();

// Insert font
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Add a page
$pdf->addPage();

// Add content using basic drawing
$pdf->page->addContent(
    "BT\n" .
    "/F1 14 Tf\n" .
    "1 0 0 1 28.35 800 Tm\n" .
    "(RFC 3161 Timestamped Document) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 28.35 770 Tm\n" .
    "(This document demonstrates RFC 3161 timestamp support.) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 28.35 750 Tm\n" .
    "(When signed with a timestamp, the signature includes:) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 42.52 730 Tm\n" .
    "(- Proof of when the document was signed) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 42.52 710 Tm\n" .
    "(- A timestamp token from a trusted TSA) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 42.52 690 Tm\n" .
    "(- Long-term validation capability) Tj\n" .
    "ET\n"
);

// Generate PDF
$pdfData = $pdf->getOutPDFString();

// Save the unsigned PDF
$unsignedPath = __DIR__ . '/../target/timestamp_unsigned.pdf';
@mkdir(dirname($unsignedPath), 0755, true);
file_put_contents($unsignedPath, $pdfData);
echo "   Created: $unsignedPath\n\n";

// =========================================================================
// Step 2: Demonstrate TimestampClient API
// =========================================================================

echo "2. TimestampClient API demonstration...\n";

// Create timestamp client
$tsClient = new TimestampClient('http://timestamp.digicert.com');

echo "   TSA URL: http://timestamp.digicert.com\n";
echo "   Hash Algorithm: SHA-256\n\n";

// Show available methods
echo "   Available TimestampClient methods:\n";
echo "   - setCredentials(\$username, \$password) - Set basic auth\n";
echo "   - setTimeout(\$seconds) - Set request timeout\n";
echo "   - getTimestampToken(\$data) - Get RFC 3161 token\n";
echo "   - buildTimestampRequest(\$hash) - Build TSA request\n";
echo "   - parseTimestampResponse(\$response) - Parse TSA response\n\n";

// =========================================================================
// Step 3: Show signature configuration with timestamp
// =========================================================================

echo "3. Signature configuration with timestamp...\n";

$signatureConfig = [
    'certificate' => 'file:///path/to/certificate.pem',
    'privateKey' => 'file:///path/to/private-key.pem',
    'password' => 'certificate-password',
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

echo "   Configuration structure:\n";
echo "   [\n";
echo "       'timestamp' => [\n";
echo "           'url' => 'http://timestamp.digicert.com',\n";
echo "           'username' => 'optional-user',\n";
echo "           'password' => 'optional-pass',\n";
echo "           'timeout' => 30,\n";
echo "       ],\n";
echo "   ]\n\n";

// =========================================================================
// Step 4: Show signing workflow
// =========================================================================

echo "4. Signing workflow with timestamp...\n";

echo "   Code example:\n";
echo "   \$sigManager = new SignatureManager('mm');\n";
echo "   \$sigManager->loadPdf(\$pdfContent);\n";
echo "   \n";
echo "   // Configure with timestamp\n";
echo "   \$config = [\n";
echo "       'certificate' => 'file://cert.pem',\n";
echo "       'privateKey' => 'file://key.pem',\n";
echo "       'password' => 'secret',\n";
echo "       'timestamp' => [\n";
echo "           'url' => 'http://timestamp.digicert.com',\n";
echo "       ],\n";
echo "   ];\n";
echo "   \n";
echo "   // Sign with timestamp\n";
echo "   \$signedPdf = \$sigManager->sign(\$config);\n\n";

// =========================================================================
// Step 5: Show TSA providers
// =========================================================================

echo "5. Popular TSA providers...\n\n";

$tsaProviders = [
    [
        'name' => 'DigiCert',
        'url' => 'http://timestamp.digicert.com',
        'auth' => 'None required',
        'free' => 'Yes',
    ],
    [
        'name' => 'Sectigo',
        'url' => 'http://timestamp.sectigo.com',
        'auth' => 'None required',
        'free' => 'Yes',
    ],
    [
        'name' => 'GlobalSign',
        'url' => 'http://timestamp.globalsign.com/tsa/r6advanced1',
        'auth' => 'None required',
        'free' => 'Limited',
    ],
    [
        'name' => 'FreeTSA',
        'url' => 'https://freetsa.org/tsr',
        'auth' => 'None required',
        'free' => 'Yes',
    ],
];

echo "   Provider         URL                                          Auth            Free\n";
echo "   ---------------  -------------------------------------------  --------------  ----\n";
foreach ($tsaProviders as $provider) {
    printf(
        "   %-15s  %-43s  %-14s  %s\n",
        $provider['name'],
        $provider['url'],
        $provider['auth'],
        $provider['free']
    );
}

echo "\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- TimestampClient class for RFC 3161 timestamp requests\n";
echo "- Integration with SignatureManager for timestamped signatures\n";
echo "- Configuration options for TSA authentication\n";
echo "- Popular free TSA providers list\n\n";

echo "To verify a timestamped signature:\n";
echo "1. Open the signed PDF in Adobe Acrobat Reader\n";
echo "2. Click on the signature\n";
echo "3. Check 'Signature Properties' -> 'Date/Time'\n";
echo "4. It should show 'Signature is timestamped' with TSA details\n\n";

echo "Note: Actual signing requires valid certificates.\n";
echo "The unsigned PDF was saved to: $unsignedPath\n";
