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
use Com\Tecnick\Pdf\Signature\DssBuilder;
use Com\Tecnick\Pdf\Signature\OcspClient;
use Com\Tecnick\Pdf\Signature\CrlFetcher;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "Long-Term Validation (LTV) Signature Example\n";
echo "=============================================\n\n";

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

// =========================================================================
// Step 1: Create a simple PDF to demonstrate LTV
// =========================================================================

echo "1. Creating a simple PDF document...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('LTV Signature Example');
$pdf->setTitle('Long-Term Validation Document');
$pdf->setKeywords('TCPDF, PDF, LTV, signature, DSS');

// Enable default page content
$pdf->enableDefaultPageContent();

// Insert font
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// Add a page
$pdf->addPage();

// Add content
$pdf->page->addContent(
    "BT\n" .
    "/F1 14 Tf\n" .
    "1 0 0 1 28.35 800 Tm\n" .
    "(Long-Term Validation \\(LTV\\) Document) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 28.35 770 Tm\n" .
    "(This document demonstrates LTV signature support.) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 28.35 750 Tm\n" .
    "(LTV-enabled signatures include a Document Security Store \\(DSS\\):) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 42.52 730 Tm\n" .
    "(- Certificate chain for verification) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 42.52 710 Tm\n" .
    "(- OCSP responses for certificate validity) Tj\n" .
    "ET\n"
);

$pdf->page->addContent(
    "BT\n" .
    "/F1 11 Tf\n" .
    "1 0 0 1 42.52 690 Tm\n" .
    "(- CRLs for revocation checking) Tj\n" .
    "ET\n"
);

// Generate PDF
$pdfData = $pdf->getOutPDFString();

// Save the unsigned PDF
$unsignedPath = __DIR__ . '/../target/ltv_unsigned.pdf';
@mkdir(dirname($unsignedPath), 0755, true);
file_put_contents($unsignedPath, $pdfData);
echo "   Created: $unsignedPath\n\n";

// =========================================================================
// Step 2: Demonstrate LTV Components
// =========================================================================

echo "2. LTV Components Overview...\n\n";

echo "   Document Security Store (DSS) Structure:\n";
echo "   ----------------------------------------\n";
echo "   The DSS is a dictionary in the PDF catalog containing:\n\n";
echo "   /DSS <<\n";
echo "       /Certs [array of certificate streams]\n";
echo "       /OCSPs [array of OCSP response streams]\n";
echo "       /CRLs [array of CRL streams]\n";
echo "       /VRI <<\n";
echo "           /[signature hash] <<\n";
echo "               /Cert [refs to certs for this signature]\n";
echo "               /OCSP [refs to OCSP responses]\n";
echo "               /CRL [refs to CRLs]\n";
echo "           >>\n";
echo "       >>\n";
echo "   >>\n\n";

// =========================================================================
// Step 3: Demonstrate OCSP Client
// =========================================================================

echo "3. OCSP Client API...\n\n";

$ocspClient = new OcspClient(30);

echo "   OcspClient Methods:\n";
echo "   -------------------\n";
echo "   \$client = new OcspClient(timeout: 30);\n";
echo "   \$response = \$client->getOcspResponse(\$certificate, \$issuerCert);\n\n";

echo "   OCSP provides real-time certificate status:\n";
echo "   - Good: Certificate is valid\n";
echo "   - Revoked: Certificate has been revoked\n";
echo "   - Unknown: Status cannot be determined\n\n";

// =========================================================================
// Step 4: Demonstrate CRL Fetcher
// =========================================================================

echo "4. CRL Fetcher API...\n\n";

$crlFetcher = new CrlFetcher(30);

echo "   CrlFetcher Methods:\n";
echo "   -------------------\n";
echo "   \$fetcher = new CrlFetcher(timeout: 30);\n";
echo "   \$crl = \$fetcher->getCrl(\$certificate);\n\n";

echo "   CRL (Certificate Revocation List) contains:\n";
echo "   - List of revoked certificate serial numbers\n";
echo "   - Revocation dates and reasons\n";
echo "   - Next update time\n\n";

// =========================================================================
// Step 5: Demonstrate DSS Builder
// =========================================================================

echo "5. DSS Builder API...\n\n";

$dssBuilder = new DssBuilder(30);

echo "   DssBuilder Methods:\n";
echo "   -------------------\n";
echo "   \$builder = new DssBuilder(timeout: 30);\n";
echo "   \$builder->addCertificate(\$derCert);\n";
echo "   \$builder->addOcspResponse(\$response);\n";
echo "   \$builder->addCrl(\$crl);\n";
echo "   \n";
echo "   if (\$builder->hasValidationData()) {\n";
echo "       \$result = \$builder->buildDssObjects(\$startObjNum);\n";
echo "       // \$result contains 'objects', 'dssObjNum', 'nextObjNum'\n";
echo "   }\n\n";

// =========================================================================
// Step 6: Full LTV Workflow
// =========================================================================

echo "6. Full LTV Signing Workflow...\n\n";

echo "   // Step 1: Sign the PDF\n";
echo "   \$sigManager = new SignatureManager('mm');\n";
echo "   \$sigManager->loadPdf(\$pdfContent);\n";
echo "   \$signedPdf = \$sigManager->sign(\$signatureConfig);\n";
echo "   \n";
echo "   // Step 2: Extract certificate chain\n";
echo "   \$certificates = \$sigManager->extractCertificateChain();\n";
echo "   \n";
echo "   // Step 3: Enable LTV\n";
echo "   \$sigManager->loadPdf(\$signedPdf);\n";
echo "   \$ltvConfig = [\n";
echo "       'timeout' => 30,\n";
echo "       'fetchOcsp' => true,\n";
echo "       'fetchCrl' => true,\n";
echo "   ];\n";
echo "   \$ltvPdf = \$sigManager->enableLtv(\$certificates, \$ltvConfig);\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- OcspClient for fetching OCSP responses\n";
echo "- CrlFetcher for downloading CRLs\n";
echo "- DssBuilder for constructing Document Security Store\n";
echo "- SignatureManager.enableLtv() for adding LTV data\n\n";

echo "What is LTV (Long-Term Validation)?\n";
echo "-----------------------------------\n";
echo "LTV embeds the following data in the PDF:\n";
echo "  - Document Security Store (DSS) in the catalog\n";
echo "  - Certificate chain for signature verification\n";
echo "  - OCSP responses (certificate validity proof)\n";
echo "  - CRLs (Certificate Revocation Lists)\n\n";

echo "Benefits:\n";
echo "---------\n";
echo "  - Signatures remain valid after certificate expiration\n";
echo "  - No need for online verification services\n";
echo "  - Compliant with PAdES-LTV (PDF Advanced Electronic Signatures)\n";
echo "  - Required for long-term archival (PDF/A-3)\n\n";

echo "To verify an LTV signature:\n";
echo "1. Open the PDF in Adobe Acrobat Reader\n";
echo "2. Click on the signature\n";
echo "3. Check 'Signature Properties'\n";
echo "4. Look for 'LTV enabled' status\n\n";

echo "Note: Actual LTV requires valid certificates with OCSP/CRL distribution points.\n";
echo "The unsigned PDF was saved to: $unsignedPath\n";
