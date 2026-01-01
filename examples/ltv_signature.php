<?php
/**
 * ltv_signature.php
 *
 * Example demonstrating Long-Term Validation (LTV) signatures.
 *
 * LTV signatures embed revocation information (OCSP responses and CRLs)
 * directly in the PDF, allowing signatures to be validated even after:
 * - The signing certificate expires
 * - OCSP responders become unavailable
 * - CRL distribution points are no longer accessible
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
use Com\Tecnick\Pdf\Signature\DssBuilder;
use Com\Tecnick\Pdf\Signature\OcspClient;
use Com\Tecnick\Pdf\Signature\CrlFetcher;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

/**
 * Long-Term Validation (LTV) Signatures
 *
 * LTV-enabled signatures include a Document Security Store (DSS) containing:
 * - Certs: All certificates in the signing chain
 * - OCSPs: OCSP responses proving certificate validity
 * - CRLs: Certificate Revocation Lists
 * - VRI: Validation-Related Information per signature
 *
 * This allows signatures to remain verifiable indefinitely, even when
 * online revocation checking is no longer possible.
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

echo "Long-Term Validation (LTV) Signature Example\n";
echo "=============================================\n\n";

// Create signature manager
$sigManager = new SignatureManager('mm');

// Load the PDF
$sigManager->loadPdf($inputPdf);

// Configure signature
$signatureConfig = [
    'certificate' => 'file://' . $certPath,
    'privateKey' => 'file://' . $certPath,
    'password' => 'tcpdfdemo',
    'hashAlgorithm' => 'sha256',
    'info' => [
        'name' => 'John Doe',
        'reason' => 'Document approval with LTV',
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
];

try {
    // Step 1: Sign the PDF
    echo "Step 1: Signing PDF...\n";
    $signedPdf = $sigManager->sign($signatureConfig);
    echo "  - PDF signed successfully\n";

    // Step 2: Extract certificate chain (for production, you'd get this from your PKI)
    echo "\nStep 2: Extracting certificate chain...\n";

    // For this example, we'll load the certificate manually
    $certContent = file_get_contents($certPath);
    if ($certContent === false) {
        throw new \Exception('Unable to read certificate');
    }

    // Convert PEM to DER
    $certificates = [];
    if (preg_match('/-----BEGIN CERTIFICATE-----(.+?)-----END CERTIFICATE-----/s', $certContent, $m)) {
        $derCert = base64_decode(preg_replace('/\s+/', '', $m[1]));
        if ($derCert !== false) {
            $certificates[] = $derCert;
            echo "  - Extracted signing certificate\n";
        }
    }

    // Step 3: Enable LTV (add DSS with validation data)
    echo "\nStep 3: Enabling Long-Term Validation...\n";

    // Reload the signed PDF
    $sigManager->loadPdf($signedPdf);

    // Configure LTV options
    $ltvConfig = [
        'timeout' => 30,
        'fetchOcsp' => true,  // Try to fetch OCSP responses
        'fetchCrl' => true,   // Try to fetch CRLs
    ];

    // Enable LTV
    $ltvPdf = $sigManager->enableLtv($certificates, $ltvConfig);

    // Check if LTV data was added
    if ($ltvPdf === $signedPdf) {
        echo "  - Note: No validation data was added (this is normal for test certificates)\n";
        echo "  - In production with real certificates, OCSP/CRL data would be embedded\n";
    } else {
        echo "  - LTV validation data added to PDF\n";
    }

    // Save the LTV-enabled signed PDF
    $outputPath = __DIR__ . '/../target/signed_with_ltv.pdf';
    file_put_contents($outputPath, $ltvPdf);

    echo "\nLTV-enabled signed PDF saved to: $outputPath\n";

    echo "\n";
    echo "What is LTV (Long-Term Validation)?\n";
    echo "-----------------------------------\n";
    echo "LTV embeds the following data in the PDF:\n";
    echo "  - Document Security Store (DSS) in the catalog\n";
    echo "  - Certificate chain for signature verification\n";
    echo "  - OCSP responses (certificate validity proof)\n";
    echo "  - CRLs (Certificate Revocation Lists)\n";
    echo "\n";
    echo "Benefits:\n";
    echo "  - Signatures remain valid even after certificate expiration\n";
    echo "  - No need for online verification services\n";
    echo "  - Compliant with PAdES-LTV (PDF Advanced Electronic Signatures)\n";
    echo "\n";
    echo "To verify the LTV signature:\n";
    echo "  1. Open the PDF in Adobe Acrobat Reader\n";
    echo "  2. Click on the signature\n";
    echo "  3. Check 'Signature Properties'\n";
    echo "  4. Look for 'LTV enabled' status\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";

    // If signing fails, show the error but don't try fallback
    echo "\nNote: LTV requires valid certificates with OCSP/CRL distribution points.\n";
    echo "For testing, you can still sign without LTV using the regular signing methods.\n";
}

// Demonstrate individual LTV components
echo "\n";
echo "Additional: Using LTV components individually\n";
echo "----------------------------------------------\n";

// OCSP Client example
echo "\n1. OCSP Client Usage:\n";
echo "   \$ocspClient = new OcspClient(timeout: 30);\n";
echo "   \$response = \$ocspClient->getOcspResponse(\$cert, \$issuerCert);\n";

// CRL Fetcher example
echo "\n2. CRL Fetcher Usage:\n";
echo "   \$crlFetcher = new CrlFetcher(timeout: 30);\n";
echo "   \$crl = \$crlFetcher->getCrl(\$certificate);\n";

// DSS Builder example
echo "\n3. DSS Builder Usage:\n";
echo "   \$dssBuilder = new DssBuilder(timeout: 30);\n";
echo "   \$dssBuilder->addCertificate(\$cert);\n";
echo "   \$dssBuilder->addOcspResponse(\$ocspResponse);\n";
echo "   \$dssBuilder->addCrl(\$crl);\n";
echo "   \$dssObjects = \$dssBuilder->buildDssObjects(\$startObjNum);\n";
