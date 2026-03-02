<?php

/**
 * PdfEncryptor.php
 *
 * PDF encryption class for adding password protection to PDFs.
 *
 * @category    Library
 * @package     PdfManipulate
 * @subpackage  Encryptor
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2024-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */

namespace Com\Tecnick\Pdf\Manipulate;

use Com\Tecnick\Pdf\Exception as PdfException;
use Com\Tecnick\Pdf\Encrypt\Encrypt;

/**
 * PDF Encryptor class
 *
 * Provides functionality to add password protection and encryption to PDF files.
 *
 * Example usage:
 * ```php
 * $encryptor = new PdfEncryptor();
 * $encryptor->loadFile('document.pdf')
 *           ->setUserPassword('user123')
 *           ->setOwnerPassword('owner456')
 *           ->setEncryptionMode(PdfEncryptor::AES_256)
 *           ->setPermissions(['print', 'copy']);
 * $encryptor->encryptToFile('protected.pdf');
 * ```
 *
 * @category    Library
 * @package     PdfManipulate
 */
class PdfEncryptor
{
    /**
     * Encryption mode: RC4 40-bit
     */
    public const RC4_40 = 0;

    /**
     * Encryption mode: RC4 128-bit
     */
    public const RC4_128 = 1;

    /**
     * Encryption mode: AES 128-bit
     */
    public const AES_128 = 2;

    /**
     * Encryption mode: AES 256-bit
     */
    public const AES_256 = 3;

    /**
     * Original PDF content
     *
     * @var string
     */
    protected string $pdfContent = '';

    /**
     * PDF version
     *
     * @var string
     */
    protected string $pdfVersion = '1.7';

    /**
     * User password (for opening document)
     *
     * @var string
     */
    protected string $userPassword = '';

    /**
     * Owner password (for full access)
     *
     * @var string
     */
    protected string $ownerPassword = '';

    /**
     * Encryption mode
     *
     * @var int
     */
    protected int $encryptionMode = self::AES_128;

    /**
     * Permissions to allow
     *
     * @var array<string>
     */
    protected array $permissions = [
        'print',
        'modify',
        'copy',
        'annot-forms',
        'fill-forms',
        'extract',
        'assemble',
        'print-high',
    ];

    /**
     * Resources dictionary content (ColorSpace, XObject, etc.)
     *
     * @var string
     */
    protected string $resourcesContent = '';

    /**
     * All available permissions
     *
     * @var array<string>
     */
    protected const ALL_PERMISSIONS = [
        'print',
        'modify',
        'copy',
        'annot-forms',
        'fill-forms',
        'extract',
        'assemble',
        'print-high',
    ];

    /**
     * Object number counter
     *
     * @var int
     */
    protected int $objectNumber = 1;

    /**
     * Parsed PDF objects
     *
     * @var array<int, array{offset: int, content: string, stream?: string}>
     */
    protected array $objects = [];

    /**
     * Encrypt object for key derivation
     *
     * @var ?Encrypt
     */
    protected ?Encrypt $encryptObj = null;

    /**
     * AES-256 encryption data (custom implementation)
     *
     * @var array<string, mixed>
     */
    protected array $aes256Data = [];

    /**
     * Load PDF from file
     *
     * @param string $filePath Path to PDF file
     * @return static
     * @throws PdfException If file cannot be read
     */
    public function loadFile(string $filePath): static
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new PdfException("Cannot read file: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new PdfException("Failed to read file: {$filePath}");
        }

        return $this->loadContent($content);
    }

    /**
     * Load PDF from string content
     *
     * @param string $content PDF content
     * @return static
     * @throws PdfException If content is not valid PDF
     */
    public function loadContent(string $content): static
    {
        if (!str_starts_with($content, '%PDF-')) {
            throw new PdfException('Invalid PDF content');
        }

        $this->pdfContent = $content;
        $this->extractVersion();

        return $this;
    }

    /**
     * Extract PDF version
     *
     * @return void
     */
    protected function extractVersion(): void
    {
        if (preg_match('/%PDF-(\d+\.\d+)/', $this->pdfContent, $matches)) {
            $this->pdfVersion = $matches[1];
        }
    }

    /**
     * Set user password (required to open document)
     *
     * @param string $password User password
     * @return static
     */
    public function setUserPassword(string $password): static
    {
        $this->userPassword = $password;
        return $this;
    }

    /**
     * Set owner password (for full access)
     *
     * @param string $password Owner password
     * @return static
     */
    public function setOwnerPassword(string $password): static
    {
        $this->ownerPassword = $password;
        return $this;
    }

    /**
     * Set both user and owner passwords
     *
     * @param string $userPassword User password
     * @param string $ownerPassword Owner password
     * @return static
     */
    public function setPasswords(string $userPassword, string $ownerPassword): static
    {
        $this->userPassword = $userPassword;
        $this->ownerPassword = $ownerPassword;
        return $this;
    }

    /**
     * Set encryption mode
     *
     * @param int $mode Encryption mode constant
     * @return static
     * @throws PdfException If invalid mode
     */
    public function setEncryptionMode(int $mode): static
    {
        if ($mode < self::RC4_40 || $mode > self::AES_256) {
            throw new PdfException("Invalid encryption mode: {$mode}");
        }

        $this->encryptionMode = $mode;
        return $this;
    }

    /**
     * Set permissions to ALLOW
     *
     * @param array<string> $permissions Permissions to allow
     * @return static
     */
    public function setPermissions(array $permissions): static
    {
        $this->permissions = array_intersect($permissions, self::ALL_PERMISSIONS);
        return $this;
    }

    /**
     * Allow all permissions
     *
     * @return static
     */
    public function allowAllPermissions(): static
    {
        $this->permissions = self::ALL_PERMISSIONS;
        return $this;
    }

    /**
     * Deny all permissions
     *
     * @return static
     */
    public function denyAllPermissions(): static
    {
        $this->permissions = [];
        return $this;
    }

    /**
     * Allow printing
     *
     * @param bool $highQuality Allow high-quality printing
     * @return static
     */
    public function allowPrinting(bool $highQuality = true): static
    {
        if (!in_array('print', $this->permissions, true)) {
            $this->permissions[] = 'print';
        }
        if ($highQuality && !in_array('print-high', $this->permissions, true)) {
            $this->permissions[] = 'print-high';
        }
        return $this;
    }

    /**
     * Allow copying content
     *
     * @return static
     */
    public function allowCopying(): static
    {
        if (!in_array('copy', $this->permissions, true)) {
            $this->permissions[] = 'copy';
        }
        if (!in_array('extract', $this->permissions, true)) {
            $this->permissions[] = 'extract';
        }
        return $this;
    }

    /**
     * Allow modifying content
     *
     * @return static
     */
    public function allowModifying(): static
    {
        if (!in_array('modify', $this->permissions, true)) {
            $this->permissions[] = 'modify';
        }
        return $this;
    }

    /**
     * Allow form filling
     *
     * @return static
     */
    public function allowFormFilling(): static
    {
        if (!in_array('fill-forms', $this->permissions, true)) {
            $this->permissions[] = 'fill-forms';
        }
        if (!in_array('annot-forms', $this->permissions, true)) {
            $this->permissions[] = 'annot-forms';
        }
        return $this;
    }

    /**
     * Allow document assembly
     *
     * @return static
     */
    public function allowAssembly(): static
    {
        if (!in_array('assemble', $this->permissions, true)) {
            $this->permissions[] = 'assemble';
        }
        return $this;
    }

    /**
     * Get current permissions
     *
     * @return array<string>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Get available permission names
     *
     * @return array<string>
     */
    public function getAvailablePermissions(): array
    {
        return self::ALL_PERMISSIONS;
    }

    /**
     * Get encryption mode name
     *
     * @param int $mode Mode constant
     * @return string Mode name
     */
    public function getEncryptionModeName(int $mode): string
    {
        return match ($mode) {
            self::RC4_40 => 'RC4 40-bit',
            self::RC4_128 => 'RC4 128-bit',
            self::AES_128 => 'AES 128-bit',
            self::AES_256 => 'AES 256-bit',
            default => 'Unknown',
        };
    }

    /**
     * Check if PDF is already encrypted
     *
     * @return bool
     */
    public function isEncrypted(): bool
    {
        return str_contains($this->pdfContent, '/Encrypt');
    }

    /**
     * Encrypt the PDF and return content
     *
     * @return string Encrypted PDF content
     * @throws PdfException If no PDF loaded or encryption fails
     */
    public function encrypt(): string
    {
        if (empty($this->pdfContent)) {
            throw new PdfException('No PDF loaded');
        }

        if ($this->isEncrypted()) {
            throw new PdfException('PDF is already encrypted. Please decrypt first.');
        }

        return $this->rebuildEncryptedPdf();
    }

    /**
     * Encrypt the PDF and save to file
     *
     * @param string $outputPath Output file path
     * @return bool True on success
     * @throws PdfException If operation fails
     */
    public function encryptToFile(string $outputPath): bool
    {
        $content = $this->encrypt();
        $result = file_put_contents($outputPath, $content);
        return $result !== false;
    }

    /**
     * Parse all objects from PDF
     *
     * @return void
     */
    protected function parseAllObjects(): void
    {
        $this->objects = [];

        // Match objects with potential streams
        preg_match_all(
            '/(\d+)\s+\d+\s+obj\s*(.*?)endobj/s',
            $this->pdfContent,
            $matches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        );

        foreach ($matches as $match) {
            $objNum = (int) $match[1][0];
            $objContent = $match[2][0];

            $stream = null;
            if (preg_match('/<<(.*)>>\s*stream\s*(.*?)\s*endstream/s', $objContent, $streamMatch)) {
                $stream = $streamMatch[2];
                $objContent = '<<' . $streamMatch[1] . '>>';
            }

            $this->objects[$objNum] = [
                'offset' => $match[1][1],
                'content' => trim($objContent),
                'stream' => $stream,
            ];
        }
    }

    /**
     * Parse pages from PDF
     *
     * @return array<array{mediaBox: array{0: float, 1: float, 2: float, 3: float}, content: string}>
     */
    protected function parsePages(): array
    {
        $pages = [];
        $this->parseAllObjects();

        // Find pages reference
        $pagesObjNum = null;
        foreach ($this->objects as $objNum => $obj) {
            if (str_contains($obj['content'], '/Type /Pages')) {
                $pagesObjNum = $objNum;
                break;
            }
        }

        if ($pagesObjNum === null) {
            return $pages;
        }

        // Get kids
        $pagesContent = $this->objects[$pagesObjNum]['content'];
        if (preg_match('/\/Kids\s*\[(.*?)\]/s', $pagesContent, $matches)) {
            preg_match_all('/(\d+)\s+\d+\s+R/', $matches[1], $kidMatches);

            foreach ($kidMatches[1] as $pageObjNum) {
                $pageObjNum = (int) $pageObjNum;
                if (!isset($this->objects[$pageObjNum])) {
                    continue;
                }

                $pageContent = $this->objects[$pageObjNum]['content'];

                // Get MediaBox
                $mediaBox = [0, 0, 612, 792]; // Default to Letter
                if (preg_match('/\/MediaBox\s*\[\s*([\d.\s-]+)\s*\]/', $pageContent, $boxMatch)) {
                    $coords = preg_split('/\s+/', trim($boxMatch[1]));
                    if (count($coords) >= 4) {
                        $mediaBox = array_map('floatval', array_slice($coords, 0, 4));
                    }
                }

                // Get content stream reference
                $contentStream = '';
                if (preg_match('/\/Contents\s+(\d+)\s+\d+\s+R/', $pageContent, $contMatch)) {
                    $contObjNum = (int) $contMatch[1];
                    if (isset($this->objects[$contObjNum]['stream'])) {
                        $contentStream = $this->objects[$contObjNum]['stream'];
                    }
                }

                $pages[] = [
                    'mediaBox' => $mediaBox,
                    'content' => $contentStream,
                ];
            }
        }

        return $pages;
    }

    /**
     * Extract shared Resources dictionary from PDF
     */
    protected function extractResources(): void
    {
        foreach ($this->objects as $objNum => $obj) {
            if (str_contains($obj['content'], '/Type /Page') && !str_contains($obj['content'], '/Type /Pages')) {
                // Try reference pattern: /Resources N 0 R
                if (preg_match('/\/Resources\s+(\d+)\s+\d+\s+R/', $obj['content'], $match)) {
                    $resObjNum = (int)$match[1];
                    if (isset($this->objects[$resObjNum])) {
                        $this->resourcesContent = $this->objects[$resObjNum]['content'];
                        return;
                    }
                }
                // Try inline pattern: /Resources << ... >>
                if (preg_match('/\/Resources\s*<</', $obj['content'])) {
                    $this->resourcesContent = $this->extractNestedDict($obj['content'], '/Resources');
                    if (!empty($this->resourcesContent)) {
                        return;
                    }
                }
            }
        }
    }

    /**
     * Extract a nested dictionary from PDF content
     *
     * @param string $content PDF content
     * @param string $key Dictionary key
     * @return string Extracted dictionary
     */
    protected function extractNestedDict(string $content, string $key): string
    {
        $pos = strpos($content, $key);
        if ($pos === false) {
            return '';
        }

        $start = strpos($content, '<<', $pos);
        if ($start === false) {
            return '';
        }

        $depth = 0;
        $len = strlen($content);
        $end = $start;

        for ($i = $start; $i < $len - 1; $i++) {
            if ($content[$i] === '<' && $content[$i + 1] === '<') {
                $depth++;
                $i++;
            } elseif ($content[$i] === '>' && $content[$i + 1] === '>') {
                $depth--;
                $i++;
                if ($depth === 0) {
                    $end = $i + 1;
                    break;
                }
            }
        }

        return substr($content, $start, $end - $start);
    }

    /**
     * Rebuild PDF with encryption
     *
     * @return string Encrypted PDF content
     */
    protected function rebuildEncryptedPdf(): string
    {
        $pages = $this->parsePages();
        $this->extractResources();

        if (empty($pages)) {
            throw new PdfException('No pages found in PDF');
        }

        // Generate file ID
        $fileId = md5($this->pdfContent . microtime());

        // Determine permissions to BLOCK (inverse of what we allow)
        $blockedPermissions = array_diff(self::ALL_PERMISSIONS, $this->permissions);

        // For AES-256, use custom implementation to work around library bug
        if ($this->encryptionMode === self::AES_256) {
            $encData = $this->generateAes256EncryptionData($fileId, $blockedPermissions);
            $this->encryptObj = null; // Not used for AES-256
        } else {
            // Create encryption object and store for key derivation
            $this->encryptObj = new Encrypt(
                true, // enabled
                $fileId,
                $this->encryptionMode,
                $blockedPermissions,
                $this->userPassword,
                $this->ownerPassword ?: $this->generateRandomPassword()
            );

            // Get encryption data
            $encData = $this->encryptObj->getEncryptionData();
        }

        // Adjust PDF version based on encryption mode
        $pdfVersion = $this->pdfVersion;
        $useAdobeExtension = false;
        if ($this->encryptionMode >= self::AES_128) {
            $pdfVersion = max('1.5', $pdfVersion);
        }
        if ($this->encryptionMode >= self::AES_256) {
            // AES-256 with R=6 uses PDF 1.7 with Adobe Extension Level 8
            // (better compatibility than PDF 2.0 with older readers like Adobe Acrobat)
            $pdfVersion = '1.7';
            $useAdobeExtension = true;
        }

        $this->objectNumber = 1;

        // Build new PDF
        $pdf = "%PDF-{$pdfVersion}\n";
        $pdf .= "%\xe2\xe3\xcf\xd3\n";

        $offsets = [];

        // Reserve object numbers
        $catalogObjNum = $this->objectNumber++;
        $pagesObjNum = $this->objectNumber++;
        $encryptObjNum = $this->objectNumber++;
        $fontObjNum = $this->objectNumber++;

        // ColorSpace object (if present in original)
        $colorSpaceObjNum = null;
        $colorSpaceContent = '';
        if (!empty($this->resourcesContent)) {
            if (preg_match('/\/ColorSpace\s*<<\s*\/CS1\s+(\d+)\s+\d+\s+R/', $this->resourcesContent, $csMatch)) {
                $origCsObjNum = (int)$csMatch[1];
                if (isset($this->objects[$origCsObjNum])) {
                    $colorSpaceObjNum = $this->objectNumber++;
                    $colorSpaceContent = $this->objects[$origCsObjNum]['content'];
                }
            }
        }

        $pageObjNums = [];
        $contentObjNums = [];

        foreach ($pages as $index => $page) {
            $pageObjNum = $this->objectNumber++;
            $contentObjNum = $this->objectNumber++;
            $pageObjNums[] = $pageObjNum;
            $contentObjNums[$pageObjNum] = $contentObjNum;
        }

        // Catalog
        $offsets[$catalogObjNum] = strlen($pdf);
        $pdf .= "{$catalogObjNum} 0 obj\n";
        $pdf .= "<<\n/Type /Catalog\n/Pages {$pagesObjNum} 0 R\n";
        if ($useAdobeExtension) {
            // Add Adobe Extension Level 8 for AES-256 compatibility
            $pdf .= "/Extensions << /ADBE << /BaseVersion /1.7 /ExtensionLevel 8 >> >>\n";
        }
        $pdf .= ">>\n";
        $pdf .= "endobj\n";

        // Pages
        $kidsStr = implode(' ', array_map(fn($n) => "{$n} 0 R", $pageObjNums));
        $offsets[$pagesObjNum] = strlen($pdf);
        $pdf .= "{$pagesObjNum} 0 obj\n";
        $pdf .= "<<\n/Type /Pages\n/Kids [{$kidsStr}]\n/Count " . count($pageObjNums) . "\n>>\n";
        $pdf .= "endobj\n";

        // Encrypt dictionary
        $offsets[$encryptObjNum] = strlen($pdf);
        $pdf .= "{$encryptObjNum} 0 obj\n";
        $pdf .= $this->buildEncryptDictionary($encData);
        $pdf .= "\nendobj\n";

        // Font
        $offsets[$fontObjNum] = strlen($pdf);
        $pdf .= "{$fontObjNum} 0 obj\n";
        $pdf .= "<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n/Encoding /WinAnsiEncoding\n>>\n";
        $pdf .= "endobj\n";

        // ColorSpace (if present)
        if ($colorSpaceObjNum !== null && !empty($colorSpaceContent)) {
            $offsets[$colorSpaceObjNum] = strlen($pdf);
            $pdf .= "{$colorSpaceObjNum} 0 obj\n";
            $pdf .= $colorSpaceContent . "\n";
            $pdf .= "endobj\n";
        }

        // Pages and content streams
        foreach ($pages as $index => $page) {
            $pageObjNum = $pageObjNums[$index];
            $contentObjNum = $contentObjNums[$pageObjNum];
            $box = $page['mediaBox'];

            // Page object
            $offsets[$pageObjNum] = strlen($pdf);
            $pdf .= "{$pageObjNum} 0 obj\n";
            $pdf .= "<<\n/Type /Page\n/Parent {$pagesObjNum} 0 R\n";
            $pdf .= "/MediaBox [{$box[0]} {$box[1]} {$box[2]} {$box[3]}]\n";
            $resourcesStr = "/Font << /F1 {$fontObjNum} 0 R >>";
            if ($colorSpaceObjNum !== null) {
                $resourcesStr .= " /ColorSpace << /CS1 {$colorSpaceObjNum} 0 R >>";
            }
            $pdf .= "/Resources << {$resourcesStr} >>\n";
            $pdf .= "/Contents {$contentObjNum} 0 R\n>>\n";
            $pdf .= "endobj\n";

            // Content stream (encrypted)
            // PDF encryption order: compress first, then encrypt
            $content = $page['content'];
            if (!empty($content)) {
                // Decompress if original was compressed (to get plaintext)
                $decompressed = @gzuncompress($content);
                if ($decompressed !== false) {
                    $content = $decompressed;
                }
            }

            // Step 1: Compress the plaintext content
            $compressedContent = gzcompress($content, 9);

            // Step 2: Encrypt the compressed content
            $encryptedContent = $this->encryptContentStream(
                $compressedContent,
                $contentObjNum,
                $this->encryptionMode
            );

            $streamLen = strlen($encryptedContent);

            $offsets[$contentObjNum] = strlen($pdf);
            $pdf .= "{$contentObjNum} 0 obj\n";
            $pdf .= "<<\n/Length {$streamLen}\n/Filter /FlateDecode\n>>\n";
            $pdf .= "stream\n{$encryptedContent}\nendstream\n";
            $pdf .= "endobj\n";
        }

        // Cross-reference table
        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= "0 " . ($this->objectNumber) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i < $this->objectNumber; $i++) {
            $offset = $offsets[$i] ?? 0;
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        // Trailer
        $pdf .= "trailer\n";
        $pdf .= "<<\n";
        $pdf .= "/Size {$this->objectNumber}\n";
        $pdf .= "/Root {$catalogObjNum} 0 R\n";
        $pdf .= "/Encrypt {$encryptObjNum} 0 R\n";
        $pdf .= "/ID [<{$fileId}><{$fileId}>]\n";
        $pdf .= ">>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    /**
     * Build encryption dictionary
     *
     * @param array<string, mixed> $encData Encryption data
     * @return string PDF dictionary string
     */
    protected function buildEncryptDictionary(array $encData): string
    {
        $dict = "<<\n";
        $dict .= "/Filter /Standard\n";

        if ($this->encryptionMode <= self::RC4_128) {
            // RC4 40-bit or 128-bit
            $dict .= "/V " . ($this->encryptionMode === self::RC4_40 ? '1' : '2') . "\n";
            $dict .= "/R " . ($this->encryptionMode === self::RC4_40 ? '2' : '3') . "\n";
            $dict .= "/Length " . ($this->encryptionMode === self::RC4_40 ? '40' : '128') . "\n";
        } elseif ($this->encryptionMode === self::AES_128) {
            // AES 128-bit
            $dict .= "/V 4\n";
            $dict .= "/R 4\n";
            $dict .= "/Length 128\n";
            $dict .= "/CF <<\n";
            $dict .= "  /StdCF <<\n";
            $dict .= "    /CFM /AESV2\n";
            $dict .= "    /Length 16\n";
            $dict .= "  >>\n";
            $dict .= ">>\n";
            $dict .= "/StmF /StdCF\n";
            $dict .= "/StrF /StdCF\n";
        } else {
            // AES 256-bit (PDF 2.0 / ISO 32000-2)
            $dict .= "/V 5\n";
            $dict .= "/R 6\n";
            $dict .= "/Length 256\n";
            $dict .= "/CF << /StdCF << /AuthEvent /DocOpen /CFM /AESV3 /Length 32 >> >>\n";
            $dict .= "/StmF /StdCF\n";
            $dict .= "/StrF /StdCF\n";
        }

        // Add O, U, and P values
        if (isset($encData['O'])) {
            $dict .= "/O <" . bin2hex($encData['O']) . ">\n";
        }
        if (isset($encData['U'])) {
            $dict .= "/U <" . bin2hex($encData['U']) . ">\n";
        }
        // Add OE and UE for AES-256
        if ($this->encryptionMode === self::AES_256) {
            if (isset($encData['OE'])) {
                $dict .= "/OE <" . bin2hex($encData['OE']) . ">\n";
            }
            if (isset($encData['UE'])) {
                $dict .= "/UE <" . bin2hex($encData['UE']) . ">\n";
            }
            if (isset($encData['perms'])) {
                $dict .= "/Perms <" . bin2hex($encData['perms']) . ">\n";
            }
        }
        if (isset($encData['P'])) {
            $dict .= "/P " . $encData['P'] . "\n";
        }

        $dict .= ">>";

        return $dict;
    }

    /**
     * Generate random password
     *
     * @return string Random password
     */
    protected function generateRandomPassword(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Encrypt content stream using proper AES/RC4 encryption
     *
     * This method properly handles encryption of arbitrary length content,
     * working around a bug in tc-lib-pdf-encrypt that truncates data.
     *
     * @param string $content Content to encrypt
     * @param int $objNum Object number for key derivation
     * @param int $mode Encryption mode
     * @return string Encrypted content
     */
    protected function encryptContentStream(
        string $content,
        int $objNum,
        int $mode
    ): string {
        if (empty($content)) {
            return $content;
        }

        // Get object-specific key
        if ($mode === self::AES_256) {
            // AES-256 uses the file encryption key directly (no per-object derivation)
            $objKey = $this->aes256Data['key'] ?? '';
        } elseif ($this->encryptObj !== null) {
            // Use library's getObjectKey for proper derivation
            $objKey = $this->encryptObj->getObjectKey($objNum);
        } else {
            return $content;
        }

        if ($mode <= self::RC4_128) {
            // RC4 encryption
            return $this->rc4Encrypt($content, $objKey);
        }

        // AES encryption - use our own to work around library's padding bug
        return $this->aesEncrypt($content, $objKey, $mode);
    }

    /**
     * RC4 encryption
     *
     * @param string $data Data to encrypt
     * @param string $key Encryption key
     * @return string Encrypted data
     */
    protected function rc4Encrypt(string $data, string $key): string
    {
        // Initialize S-box
        $s = range(0, 255);
        $j = 0;
        $keyLen = strlen($key);

        for ($i = 0; $i < 256; $i++) {
            $j = ($j + $s[$i] + ord($key[$i % $keyLen])) % 256;
            [$s[$i], $s[$j]] = [$s[$j], $s[$i]];
        }

        // Encrypt
        $result = '';
        $i = $j = 0;
        $dataLen = strlen($data);

        for ($k = 0; $k < $dataLen; $k++) {
            $i = ($i + 1) % 256;
            $j = ($j + $s[$i]) % 256;
            [$s[$i], $s[$j]] = [$s[$j], $s[$i]];
            $result .= chr(ord($data[$k]) ^ $s[($s[$i] + $s[$j]) % 256]);
        }

        return $result;
    }

    /**
     * AES encryption with proper padding
     *
     * @param string $data Data to encrypt
     * @param string $key Encryption key
     * @param int $mode Encryption mode (AES_128 or AES_256)
     * @return string Encrypted data with IV prepended
     */
    protected function aesEncrypt(string $data, string $key, int $mode): string
    {
        $cipher = ($mode == self::AES_256) ? 'aes-256-cbc' : 'aes-128-cbc';

        // Generate random IV
        $ivLen = openssl_cipher_iv_length($cipher);
        if ($ivLen === false) {
            $ivLen = 16;
        }
        $iv = openssl_random_pseudo_bytes($ivLen);

        // Pad key to required length
        $keyLen = ($mode == self::AES_256) ? 32 : 16;
        $key = str_pad(substr($key, 0, $keyLen), $keyLen, "\x00");

        // Encrypt with PKCS7 padding (OpenSSL default)
        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            return $data; // Fallback to unencrypted on error
        }

        // PDF spec requires IV prepended to encrypted data
        return $iv . $encrypted;
    }

    /**
     * Get encryption mode constant from name
     *
     * @param string $name Mode name
     * @return int Mode constant
     * @throws PdfException If unknown mode
     */
    public static function getModeFromName(string $name): int
    {
        $name = strtoupper(str_replace(['-', '_', ' '], '', $name));
        return match ($name) {
            'RC440', 'RC4_40', 'RC440BIT' => self::RC4_40,
            'RC4128', 'RC4_128', 'RC4128BIT' => self::RC4_128,
            'AES128', 'AES_128', 'AES128BIT' => self::AES_128,
            'AES256', 'AES_256', 'AES256BIT' => self::AES_256,
            default => throw new PdfException("Unknown encryption mode: {$name}"),
        };
    }

    /**
     * Generate AES-256 encryption data (custom implementation)
     *
     * This bypasses the tc-lib-pdf-encrypt library bug that truncates
     * the encryption key to 16 bytes in AESnopad.pad().
     *
     * Implements PDF 2.0 / ISO 32000-2 encryption algorithm.
     *
     * @param string $fileId File identifier (hex string)
     * @param array<string> $blockedPermissions Permissions to block
     * @return array<string, mixed> Encryption data
     */
    protected function generateAes256EncryptionData(string $fileId, array $blockedPermissions): array
    {
        // Generate random 32-byte file encryption key
        $fileKey = random_bytes(32);

        // Truncate passwords to 127 bytes (PDF 2.0 spec)
        $userPwd = substr($this->userPassword, 0, 127);
        $ownerPwd = substr($this->ownerPassword ?: $this->generateRandomPassword(), 0, 127);

        // Calculate permission value
        $protection = $this->calculatePermissionValue($blockedPermissions);

        // Generate random salts (8 bytes each)
        $userValidationSalt = random_bytes(8);
        $userKeySalt = random_bytes(8);
        $ownerValidationSalt = random_bytes(8);
        $ownerKeySalt = random_bytes(8);

        // Compute U value using Algorithm 2.B (R=6 iterative hash)
        // U = hash_r6(password, validation_salt, "") || validation_salt || key_salt
        $uHash = $this->computeHashR6($userPwd, $userValidationSalt, '');
        $U = $uHash . $userValidationSalt . $userKeySalt;

        // Compute UE value: AES-256-CBC encrypt file key with hash_r6(password, key_salt, "")
        $ueKeyHash = $this->computeHashR6($userPwd, $userKeySalt, '');
        $UE = $this->aes256EncryptNoPadding($fileKey, $ueKeyHash);

        // Compute O value using Algorithm 2.B (R=6 iterative hash)
        // O = hash_r6(password, validation_salt, U) || validation_salt || key_salt
        $oHash = $this->computeHashR6($ownerPwd, $ownerValidationSalt, $U);
        $O = $oHash . $ownerValidationSalt . $ownerKeySalt;

        // Compute OE value: AES-256-CBC encrypt file key with hash_r6(password, key_salt, U)
        $oeKeyHash = $this->computeHashR6($ownerPwd, $ownerKeySalt, $U);
        $OE = $this->aes256EncryptNoPadding($fileKey, $oeKeyHash);

        // Compute Perms value (16 bytes)
        $perms = $this->computePermsValue($protection, $fileKey);

        // Store data for content encryption
        $this->aes256Data = [
            'key' => $fileKey,
            'U' => $U,
            'UE' => $UE,
            'O' => $O,
            'OE' => $OE,
            'P' => $protection,
            'perms' => $perms,
        ];

        return $this->aes256Data;
    }

    /**
     * Compute hash using Algorithm 2.B (R=6 iterative hash)
     *
     * This implements the PDF 2.0 / ISO 32000-2 hash algorithm for R=6 encryption.
     * It uses iterative SHA-256/384/512 hashing with AES encryption.
     *
     * @param string $password Password (max 127 bytes UTF-8)
     * @param string $salt 8-byte salt
     * @param string $udata User data (empty for user password, U value for owner password)
     * @return string 32-byte hash
     */
    protected function computeHashR6(string $password, string $salt, string $udata): string
    {
        // Initial hash: SHA-256(password || salt || udata)
        $k = hash('sha256', $password . $salt . $udata, true);

        // Iterative processing for R=6 (Algorithm 2.B from ISO 32000-2)
        $roundNumber = 0;
        $done = false;

        while (!$done) {
            $roundNumber++;

            // K1 = repeat(password || K || udata, 64)
            $k1Block = $password . $k . $udata;
            $k1 = str_repeat($k1Block, 64);

            // Use first 16 bytes of K as AES-128-CBC key
            $aesKey = substr($k, 0, 16);
            // Use bytes 16-31 of K as IV
            $aesIv = substr($k, 16, 16);

            // Encrypt K1 with AES-128-CBC
            $e = openssl_encrypt($k1, 'aes-128-cbc', $aesKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $aesIv);

            // Hash selection: SUM of first 16 bytes of E, mod 3
            $eMod3 = 0;
            for ($i = 0; $i < 16; $i++) {
                $eMod3 += ord($e[$i]);
            }
            $eMod3 = $eMod3 % 3;

            switch ($eMod3) {
                case 0:
                    $k = hash('sha256', $e, true);
                    break;
                case 1:
                    $k = hash('sha384', $e, true);
                    break;
                case 2:
                    $k = hash('sha512', $e, true);
                    break;
            }

            // Termination condition: roundNumber >= 64 AND last byte of E <= (roundNumber - 32)
            if ($roundNumber >= 64) {
                $lastByte = ord($e[strlen($e) - 1]);
                if ($lastByte <= ($roundNumber - 32)) {
                    $done = true;
                }
            }
        }

        // Return first 32 bytes
        return substr($k, 0, 32);
    }

    /**
     * AES-256-CBC encryption without padding (for UE/OE values)
     *
     * @param string $data Data to encrypt (must be multiple of 16 bytes)
     * @param string $key 32-byte encryption key
     * @return string Encrypted data
     */
    protected function aes256EncryptNoPadding(string $data, string $key): string
    {
        // Zero IV as per PDF spec for UE/OE
        $iv = str_repeat("\x00", 16);

        // Ensure key is 32 bytes
        $key = str_pad(substr($key, 0, 32), 32, "\x00");

        // Pad data to multiple of 16 bytes if needed
        $padLen = 16 - (strlen($data) % 16);
        if ($padLen < 16) {
            $data .= str_repeat("\x00", $padLen);
        }

        $encrypted = openssl_encrypt(
            $data,
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $iv
        );

        return $encrypted !== false ? $encrypted : '';
    }

    /**
     * Calculate permission value from blocked permissions
     *
     * @param array<string> $blockedPermissions Permissions to block
     * @return int Permission value (P)
     */
    protected function calculatePermissionValue(array $blockedPermissions): int
    {
        // Permission bits mapping (bit positions, 1-indexed as per PDF spec)
        $permBits = [
            'print' => 3,       // Bit 3
            'modify' => 4,      // Bit 4
            'copy' => 5,        // Bit 5
            'annot-forms' => 6, // Bit 6
            'fill-forms' => 9,  // Bit 9
            'extract' => 10,    // Bit 10
            'assemble' => 11,   // Bit 11
            'print-high' => 12, // Bit 12
        ];

        // Start with all permissions allowed: 0xFFFFFFFC
        // This is -4 as signed 32-bit int (bits 1-2 are 0, all others are 1)
        $protection = -4;

        // Clear bits for blocked permissions
        foreach ($blockedPermissions as $perm) {
            if (isset($permBits[$perm])) {
                // Clear the bit (bit positions are 1-indexed, so subtract 1 for shift)
                $protection &= ~(1 << ($permBits[$perm] - 1));
            }
        }

        return $protection;
    }

    /**
     * Compute Perms value for AES-256
     *
     * @param int $protection Permission value
     * @param string $fileKey 32-byte file encryption key
     * @return string 16-byte Perms value
     */
    protected function computePermsValue(int $protection, string $fileKey): string
    {
        // Build 16-byte perms input
        // Bytes 0-3: permission value (little-endian)
        $perms = pack('V', $protection);

        // Bytes 4-7: 0xFFFFFFFF
        $perms .= "\xFF\xFF\xFF\xFF";

        // Byte 8: 'T' if encrypting metadata, 'F' otherwise (we always encrypt)
        $perms .= 'T';

        // Bytes 9-11: 'adb'
        $perms .= 'adb';

        // Bytes 12-15: random
        $perms .= random_bytes(4);

        // Encrypt with file key using AES-256-ECB (no IV needed for ECB mode)
        $key = str_pad(substr($fileKey, 0, 32), 32, "\x00");

        $encrypted = openssl_encrypt(
            $perms,
            'aes-256-ecb',
            $key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
        );

        return $encrypted !== false ? $encrypted : '';
    }
}
