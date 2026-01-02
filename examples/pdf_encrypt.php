<?php
/**
 * pdf_encrypt.php
 *
 * Example demonstrating PDF encryption and password protection.
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

use Com\Tecnick\Pdf\Manipulate\PdfEncryptor;

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

echo "PDF Encryptor Example\n";
echo "=====================\n\n";

// =========================================================================
// Step 1: Create a sample PDF document
// =========================================================================

echo "1. Creating a sample PDF document...\n";

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
$pdf->setCreator('tc-lib-pdf');
$pdf->setTitle('Encryption Demo');
$pdf->enableDefaultPageContent();
$pdf->font->insert($pdf->pon, 'helvetica', '', 12);

for ($i = 1; $i <= 3; $i++) {
    $pdf->addPage();
    $pdf->page->addContent(
        "BT\n/F1 18 Tf\n1 0 0 1 28.35 800 Tm\n(Page {$i} - Confidential Document) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 11 Tf\n1 0 0 1 28.35 770 Tm\n(This document contains sensitive information.) Tj\nET\n"
    );
    $pdf->page->addContent(
        "BT\n/F1 11 Tf\n1 0 0 1 28.35 750 Tm\n(It should be protected with encryption.) Tj\nET\n"
    );
}

$sourcePath = __DIR__ . '/../target/encrypt_source.pdf';
@mkdir(dirname($sourcePath), 0755, true);
file_put_contents($sourcePath, $pdf->getOutPDFString());
echo "   Created: $sourcePath (3 pages, unencrypted)\n\n";

// =========================================================================
// Step 2: Demonstrate PdfEncryptor API
// =========================================================================

echo "2. PdfEncryptor Class API...\n\n";

echo "   Creating an encryptor:\n";
echo "   ----------------------\n";
echo "   \$encryptor = new PdfEncryptor();\n";
echo "   // or via Tcpdf: \$encryptor = \$pdf->createEncryptor();\n\n";

echo "   Loading PDF:\n";
echo "   ------------\n";
echo "   \$encryptor->loadFile('document.pdf');\n";
echo "   \$encryptor->loadContent(\$pdfContent);\n\n";

echo "   Setting passwords:\n";
echo "   ------------------\n";
echo "   \$encryptor->setUserPassword('user123');    // Password to open\n";
echo "   \$encryptor->setOwnerPassword('owner456');  // Full access password\n";
echo "   \$encryptor->setPasswords('user', 'owner'); // Set both\n\n";

echo "   Encryption modes:\n";
echo "   -----------------\n";
echo "   PdfEncryptor::RC4_40   - RC4 40-bit (legacy)\n";
echo "   PdfEncryptor::RC4_128  - RC4 128-bit\n";
echo "   PdfEncryptor::AES_128  - AES 128-bit (recommended)\n";
echo "   PdfEncryptor::AES_256  - AES 256-bit (strongest)\n\n";

echo "   Setting permissions:\n";
echo "   --------------------\n";
echo "   \$encryptor->setPermissions(['print', 'copy']);\n";
echo "   \$encryptor->allowPrinting();   // Allow print\n";
echo "   \$encryptor->allowCopying();    // Allow copy\n";
echo "   \$encryptor->allowModifying();  // Allow modify\n";
echo "   \$encryptor->allowAllPermissions();  // Allow all\n";
echo "   \$encryptor->denyAllPermissions();   // Deny all\n\n";

echo "   Encrypting:\n";
echo "   -----------\n";
echo "   \$encryptedPdf = \$encryptor->encrypt();\n";
echo "   \$encryptor->encryptToFile('protected.pdf');\n\n";

// =========================================================================
// Step 3: Basic encryption with user password
// =========================================================================

echo "3. Basic encryption with user password...\n";

$encryptor = new PdfEncryptor();
$encryptor->loadFile($sourcePath);

echo "   Is already encrypted: " . ($encryptor->isEncrypted() ? 'Yes' : 'No') . "\n";
echo "   Available permissions: " . implode(', ', $encryptor->getAvailablePermissions()) . "\n";

$encryptor->setUserPassword('secret123')
          ->setEncryptionMode(PdfEncryptor::AES_128)
          ->allowAllPermissions();

$basicPath = __DIR__ . '/../target/encrypt_basic.pdf';
$encryptor->encryptToFile($basicPath);
echo "   Created: $basicPath\n";
echo "   User password: secret123\n";
echo "   Encryption: " . $encryptor->getEncryptionModeName(PdfEncryptor::AES_128) . "\n\n";

// =========================================================================
// Step 4: Encryption with both passwords
// =========================================================================

echo "4. Encryption with user and owner passwords...\n";

$encryptor2 = new PdfEncryptor();
$encryptor2->loadFile($sourcePath);
$encryptor2->setPasswords('user_pass', 'owner_pass')
           ->setEncryptionMode(PdfEncryptor::AES_256);

$dualPath = __DIR__ . '/../target/encrypt_dual_password.pdf';
$encryptor2->encryptToFile($dualPath);
echo "   Created: $dualPath\n";
echo "   User password: user_pass\n";
echo "   Owner password: owner_pass\n";
echo "   Encryption: " . $encryptor2->getEncryptionModeName(PdfEncryptor::AES_256) . "\n\n";

// =========================================================================
// Step 5: Restrict permissions - no copying
// =========================================================================

echo "5. Restricting permissions (no copying allowed)...\n";

$encryptor3 = new PdfEncryptor();
$encryptor3->loadFile($sourcePath);
$encryptor3->setUserPassword('nocopy')
           ->setPermissions(['print', 'modify', 'annot-forms', 'fill-forms', 'assemble', 'print-high']);
           // Note: 'copy' and 'extract' are NOT included

$noCopyPath = __DIR__ . '/../target/encrypt_no_copy.pdf';
$encryptor3->encryptToFile($noCopyPath);
echo "   Created: $noCopyPath\n";
echo "   User password: nocopy\n";
echo "   Permissions: " . implode(', ', $encryptor3->getPermissions()) . "\n\n";

// =========================================================================
// Step 6: Restrict permissions - print only
// =========================================================================

echo "6. Print-only permissions...\n";

$encryptor4 = new PdfEncryptor();
$encryptor4->loadFile($sourcePath);
$encryptor4->setUserPassword('printonly')
           ->denyAllPermissions()
           ->allowPrinting(true);  // true = high quality

$printOnlyPath = __DIR__ . '/../target/encrypt_print_only.pdf';
$encryptor4->encryptToFile($printOnlyPath);
echo "   Created: $printOnlyPath\n";
echo "   User password: printonly\n";
echo "   Permissions: " . implode(', ', $encryptor4->getPermissions()) . "\n\n";

// =========================================================================
// Step 7: Maximum restriction
// =========================================================================

echo "7. Maximum restriction (no permissions)...\n";

$encryptor5 = new PdfEncryptor();
$encryptor5->loadFile($sourcePath);
$encryptor5->setUserPassword('locked')
           ->setOwnerPassword('masterkey')
           ->denyAllPermissions();

$lockedPath = __DIR__ . '/../target/encrypt_locked.pdf';
$encryptor5->encryptToFile($lockedPath);
echo "   Created: $lockedPath\n";
echo "   User password: locked (view only)\n";
echo "   Owner password: masterkey (full access)\n";
echo "   Permissions: none (user can only view)\n\n";

// =========================================================================
// Step 8: Different encryption modes
// =========================================================================

echo "8. Different encryption modes...\n";

$modes = [
    PdfEncryptor::RC4_40 => 'rc4_40',
    PdfEncryptor::RC4_128 => 'rc4_128',
    PdfEncryptor::AES_128 => 'aes_128',
    PdfEncryptor::AES_256 => 'aes_256',
];

foreach ($modes as $mode => $name) {
    $enc = new PdfEncryptor();
    $enc->loadFile($sourcePath);
    $enc->setUserPassword('mode_test')
        ->setEncryptionMode($mode);

    $modePath = __DIR__ . "/../target/encrypt_{$name}.pdf";
    $enc->encryptToFile($modePath);
    echo "   Created: $modePath\n";
    echo "   Mode: " . $enc->getEncryptionModeName($mode) . "\n";
}
echo "\n";

// =========================================================================
// Step 9: Form filling allowed
// =========================================================================

echo "9. Form filling permissions...\n";

$encryptor9 = new PdfEncryptor();
$encryptor9->loadFile($sourcePath);
$encryptor9->setUserPassword('formfill')
           ->denyAllPermissions()
           ->allowFormFilling();

$formPath = __DIR__ . '/../target/encrypt_form_fill.pdf';
$encryptor9->encryptToFile($formPath);
echo "   Created: $formPath\n";
echo "   User password: formfill\n";
echo "   Permissions: " . implode(', ', $encryptor9->getPermissions()) . "\n\n";

// =========================================================================
// Step 10: Using convenience method from Tcpdf
// =========================================================================

echo "10. Using convenience method...\n";

$pdf2 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);

$conveniencePath = __DIR__ . '/../target/encrypt_convenience.pdf';
$pdf2->encryptPdf(
    $sourcePath,
    'easypass',
    $conveniencePath,
    'ownerpass',
    PdfEncryptor::AES_128,
    ['print', 'copy']
);
echo "   Created: $conveniencePath\n";
echo "   User password: easypass\n";
echo "   Owner password: ownerpass\n\n";

// =========================================================================
// Summary
// =========================================================================

echo "Features Demonstrated:\n";
echo "======================\n";
echo "- PdfEncryptor class for PDF encryption\n";
echo "- User password protection (to open document)\n";
echo "- Owner password (for full access)\n";
echo "- Multiple encryption modes (RC4, AES)\n";
echo "- Permission control (print, copy, modify, etc.)\n";
echo "- Convenience methods for common restrictions\n\n";

echo "PdfEncryptor Methods:\n";
echo "---------------------\n";
echo "- loadFile(\$path): Load PDF from file\n";
echo "- loadContent(\$content): Load PDF from string\n";
echo "- setUserPassword(\$pwd): Set user password\n";
echo "- setOwnerPassword(\$pwd): Set owner password\n";
echo "- setPasswords(\$user, \$owner): Set both passwords\n";
echo "- setEncryptionMode(\$mode): Set encryption strength\n";
echo "- setPermissions(\$perms): Set allowed permissions\n";
echo "- allowAllPermissions(): Allow all operations\n";
echo "- denyAllPermissions(): Deny all operations\n";
echo "- allowPrinting(\$highQuality): Allow printing\n";
echo "- allowCopying(): Allow copy/extract\n";
echo "- allowModifying(): Allow modifications\n";
echo "- allowFormFilling(): Allow form filling\n";
echo "- allowAssembly(): Allow document assembly\n";
echo "- getPermissions(): Get current permissions\n";
echo "- getAvailablePermissions(): Get all permission names\n";
echo "- isEncrypted(): Check if already encrypted\n";
echo "- encrypt(): Get encrypted PDF content\n";
echo "- encryptToFile(\$path): Save encrypted PDF\n\n";

echo "Encryption Modes:\n";
echo "-----------------\n";
echo "- RC4_40: RC4 40-bit (legacy, weak)\n";
echo "- RC4_128: RC4 128-bit (legacy)\n";
echo "- AES_128: AES 128-bit (recommended)\n";
echo "- AES_256: AES 256-bit (strongest)\n\n";

echo "Available Permissions:\n";
echo "----------------------\n";
echo "- print: Print the document\n";
echo "- print-high: High-quality printing\n";
echo "- modify: Modify document contents\n";
echo "- copy: Copy text and graphics\n";
echo "- extract: Extract text for accessibility\n";
echo "- annot-forms: Add/modify annotations\n";
echo "- fill-forms: Fill interactive forms\n";
echo "- assemble: Insert/rotate/delete pages\n";
