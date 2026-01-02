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

        // Create encryption object
        $encrypt = new Encrypt(
            true, // enabled
            $fileId,
            $this->encryptionMode,
            $blockedPermissions,
            $this->userPassword,
            $this->ownerPassword ?: $this->generateRandomPassword()
        );

        // Get encryption data
        $encData = $encrypt->getEncryptionData();

        // Adjust PDF version based on encryption mode
        $pdfVersion = $this->pdfVersion;
        if ($this->encryptionMode >= self::AES_128) {
            $pdfVersion = max('1.5', $pdfVersion);
        }
        if ($this->encryptionMode >= self::AES_256) {
            $pdfVersion = max('1.7', $pdfVersion);
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
        $pdf .= "<<\n/Type /Catalog\n/Pages {$pagesObjNum} 0 R\n>>\n";
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
            $content = $page['content'];
            if (!empty($content)) {
                // Decompress if needed
                $decompressed = @gzuncompress($content);
                if ($decompressed !== false) {
                    $content = $decompressed;
                }
            }

            // Encrypt content
            $encryptedContent = $encrypt->encryptString($content, $contentObjNum);
            $encryptedContent = gzcompress($encryptedContent, 9);
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
            $dict .= "/V " . ($this->encryptionMode === self::RC4_40 ? '1' : '2') . "\n";
            $dict .= "/R " . ($this->encryptionMode === self::RC4_40 ? '2' : '3') . "\n";
            $dict .= "/Length " . ($this->encryptionMode === self::RC4_40 ? '40' : '128') . "\n";
        } else {
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
        }

        // Add O, U, and P values
        if (isset($encData['O'])) {
            $dict .= "/O <" . bin2hex($encData['O']) . ">\n";
        }
        if (isset($encData['U'])) {
            $dict .= "/U <" . bin2hex($encData['U']) . ">\n";
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
}
