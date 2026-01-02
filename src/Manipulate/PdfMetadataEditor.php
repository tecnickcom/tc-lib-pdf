<?php

/**
 * PdfMetadataEditor.php
 *
 * PDF Metadata editing functionality - modify document info properties.
 *
 * @category  Library
 * @package   Pdf
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Manipulate;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * PDF Metadata Editor
 *
 * Edits PDF document information (metadata) such as title, author, subject, keywords.
 *
 * @category  Library
 * @package   Pdf
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class PdfMetadataEditor
{
    /**
     * PDF content
     */
    protected string $pdfContent = '';

    /**
     * Original file path
     */
    protected string $filePath = '';

    /**
     * Metadata fields
     *
     * @var array<string, string>
     */
    protected array $metadata = [];

    /**
     * Parsed objects from PDF
     *
     * @var array<int, string>
     */
    protected array $objects = [];

    /**
     * Current object number
     */
    protected int $objectNumber = 1;

    /**
     * PDF version
     */
    protected string $pdfVersion = '1.7';

    /**
     * Load a PDF file for editing
     *
     * @param string $filePath Path to PDF file
     * @return static
     * @throws PdfException If file cannot be read
     */
    public function loadFile(string $filePath): static
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new PdfException("Cannot read PDF file: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new PdfException("Failed to read PDF file: {$filePath}");
        }

        $this->pdfContent = $content;
        $this->filePath = $filePath;
        $this->parseExistingMetadata();

        return $this;
    }

    /**
     * Load PDF from content string
     *
     * @param string $pdfContent Raw PDF content
     * @return static
     */
    public function loadContent(string $pdfContent): static
    {
        $this->pdfContent = $pdfContent;
        $this->filePath = '';
        $this->parseExistingMetadata();

        return $this;
    }

    /**
     * Parse existing metadata from the PDF
     */
    protected function parseExistingMetadata(): void
    {
        // Extract PDF version
        if (preg_match('/%PDF-(\d+\.\d+)/', $this->pdfContent, $match)) {
            $this->pdfVersion = $match[1];
        }

        // Extract all objects
        $this->objects = $this->extractObjects($this->pdfContent);

        // Find and parse /Info dictionary
        $infoObjNum = $this->findInfoObject();
        if ($infoObjNum !== null && isset($this->objects[$infoObjNum])) {
            $this->parseInfoDictionary($this->objects[$infoObjNum]);
        }
    }

    /**
     * Find the /Info object number from trailer
     *
     * @return int|null Info object number or null if not found
     */
    protected function findInfoObject(): ?int
    {
        // Look for /Info reference in trailer
        if (preg_match('/\/Info\s+(\d+)\s+0\s+R/', $this->pdfContent, $match)) {
            return (int)$match[1];
        }

        return null;
    }

    /**
     * Parse the /Info dictionary content
     *
     * @param string $content Info object content
     */
    protected function parseInfoDictionary(string $content): void
    {
        $fields = ['Title', 'Author', 'Subject', 'Keywords', 'Creator', 'Producer', 'CreationDate', 'ModDate'];

        foreach ($fields as $field) {
            if (preg_match('/\/' . $field . '\s*\(([^)]*)\)/', $content, $match)) {
                $this->metadata[$field] = $this->decodePdfString($match[1]);
            } elseif (preg_match('/\/' . $field . '\s*<([^>]*)>/', $content, $match)) {
                // Hex string
                $this->metadata[$field] = $this->decodeHexString($match[1]);
            }
        }
    }

    /**
     * Decode PDF string (handle escape sequences)
     *
     * @param string $str PDF string
     * @return string Decoded string
     */
    protected function decodePdfString(string $str): string
    {
        $str = str_replace(['\\n', '\\r', '\\t', '\\(', '\\)', '\\\\'], ["\n", "\r", "\t", '(', ')', '\\'], $str);
        return $str;
    }

    /**
     * Decode hex string
     *
     * @param string $hex Hex string
     * @return string Decoded string
     */
    protected function decodeHexString(string $hex): string
    {
        $hex = preg_replace('/\s+/', '', $hex);
        return hex2bin($hex) ?: '';
    }

    /**
     * Extract all objects from PDF content
     *
     * @param string $content PDF content
     * @return array<int, string>
     */
    protected function extractObjects(string $content): array
    {
        $objects = [];

        $pattern = '/(\d+)\s+0\s+obj\s*(.*?)\s*endobj/s';
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $objNum = (int)$match[1];
                $objects[$objNum] = $match[2];
            }
        }

        return $objects;
    }

    /**
     * Set document title
     *
     * @param string $title Document title
     * @return static
     */
    public function setTitle(string $title): static
    {
        $this->metadata['Title'] = $title;
        return $this;
    }

    /**
     * Set document author
     *
     * @param string $author Document author
     * @return static
     */
    public function setAuthor(string $author): static
    {
        $this->metadata['Author'] = $author;
        return $this;
    }

    /**
     * Set document subject
     *
     * @param string $subject Document subject
     * @return static
     */
    public function setSubject(string $subject): static
    {
        $this->metadata['Subject'] = $subject;
        return $this;
    }

    /**
     * Set document keywords
     *
     * @param string $keywords Keywords (comma-separated)
     * @return static
     */
    public function setKeywords(string $keywords): static
    {
        $this->metadata['Keywords'] = $keywords;
        return $this;
    }

    /**
     * Set document creator application
     *
     * @param string $creator Creator application name
     * @return static
     */
    public function setCreator(string $creator): static
    {
        $this->metadata['Creator'] = $creator;
        return $this;
    }

    /**
     * Set document producer application
     *
     * @param string $producer Producer application name
     * @return static
     */
    public function setProducer(string $producer): static
    {
        $this->metadata['Producer'] = $producer;
        return $this;
    }

    /**
     * Set creation date
     *
     * @param \DateTimeInterface|null $date Date/time or null for current time
     * @return static
     */
    public function setCreationDate(?\DateTimeInterface $date = null): static
    {
        $date = $date ?? new \DateTime();
        $this->metadata['CreationDate'] = $this->formatPdfDate($date);
        return $this;
    }

    /**
     * Set modification date
     *
     * @param \DateTimeInterface|null $date Date/time or null for current time
     * @return static
     */
    public function setModDate(?\DateTimeInterface $date = null): static
    {
        $date = $date ?? new \DateTime();
        $this->metadata['ModDate'] = $this->formatPdfDate($date);
        return $this;
    }

    /**
     * Format date for PDF
     *
     * @param \DateTimeInterface $date Date to format
     * @return string PDF date string
     */
    protected function formatPdfDate(\DateTimeInterface $date): string
    {
        // PDF date format: D:YYYYMMDDHHmmSSOHH'mm'
        return 'D:' . $date->format('YmdHis') . $date->format('O');
    }

    /**
     * Set multiple metadata fields at once
     *
     * @param array<string, string> $metadata Associative array of field => value
     * @return static
     */
    public function setMetadata(array $metadata): static
    {
        foreach ($metadata as $field => $value) {
            $method = 'set' . ucfirst($field);
            if (method_exists($this, $method)) {
                $this->$method($value);
            } else {
                $this->metadata[$field] = $value;
            }
        }
        return $this;
    }

    /**
     * Get all current metadata
     *
     * @return array<string, string>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get a specific metadata field
     *
     * @param string $field Field name
     * @return string|null Field value or null if not set
     */
    public function getField(string $field): ?string
    {
        return $this->metadata[$field] ?? null;
    }

    /**
     * Remove a metadata field
     *
     * @param string $field Field name to remove
     * @return static
     */
    public function removeField(string $field): static
    {
        unset($this->metadata[$field]);
        return $this;
    }

    /**
     * Clear all metadata
     *
     * @return static
     */
    public function clearMetadata(): static
    {
        $this->metadata = [];
        return $this;
    }

    /**
     * Apply changes and return modified PDF
     *
     * @return string Modified PDF content
     * @throws PdfException If no PDF loaded
     */
    public function apply(): string
    {
        if (empty($this->pdfContent)) {
            throw new PdfException('No PDF loaded');
        }

        return $this->rebuildPdf();
    }

    /**
     * Apply changes and save to file
     *
     * @param string $outputPath Output file path
     * @return bool True on success
     * @throws PdfException If operation fails
     */
    public function applyToFile(string $outputPath): bool
    {
        $content = $this->apply();
        $result = file_put_contents($outputPath, $content);
        return $result !== false;
    }

    /**
     * Rebuild PDF with updated metadata
     *
     * @return string Modified PDF content
     */
    protected function rebuildPdf(): string
    {
        // Parse the PDF structure
        $pages = $this->parsePages();

        if (empty($pages)) {
            throw new PdfException('No pages found in PDF');
        }

        $this->objectNumber = 1;

        // Start building PDF
        $pdf = "%PDF-{$this->pdfVersion}\n";
        $pdf .= "%\xe2\xe3\xcf\xd3\n";

        $offsets = [];

        // Object 1: Catalog
        $catalogObjNum = $this->objectNumber++;
        $pagesObjNum = $this->objectNumber++;
        $infoObjNum = $this->objectNumber++;

        // Reserve object numbers for fonts
        $fontObjNum = $this->objectNumber++;

        // Create page objects
        $pageObjNums = [];
        $contentObjNums = [];

        foreach ($pages as $index => $page) {
            $pageObjNum = $this->objectNumber++;
            $contentObjNum = $this->objectNumber++;
            $pageObjNums[] = $pageObjNum;
            $contentObjNums[$pageObjNum] = $contentObjNum;
        }

        // Write Catalog
        $offsets[$catalogObjNum] = strlen($pdf);
        $pdf .= "{$catalogObjNum} 0 obj\n";
        $pdf .= "<<\n/Type /Catalog\n/Pages {$pagesObjNum} 0 R\n>>\n";
        $pdf .= "endobj\n";

        // Write Pages
        $kidsStr = implode(' ', array_map(fn($n) => "{$n} 0 R", $pageObjNums));
        $offsets[$pagesObjNum] = strlen($pdf);
        $pdf .= "{$pagesObjNum} 0 obj\n";
        $pdf .= "<<\n/Type /Pages\n/Kids [{$kidsStr}]\n/Count " . count($pageObjNums) . "\n>>\n";
        $pdf .= "endobj\n";

        // Write Info dictionary (with updated metadata)
        $offsets[$infoObjNum] = strlen($pdf);
        $pdf .= $this->buildInfoObject($infoObjNum);

        // Write font object
        $offsets[$fontObjNum] = strlen($pdf);
        $pdf .= "{$fontObjNum} 0 obj\n";
        $pdf .= "<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n/Encoding /WinAnsiEncoding\n>>\n";
        $pdf .= "endobj\n";

        // Write page and content objects
        foreach ($pages as $index => $page) {
            $pageObjNum = $pageObjNums[$index];
            $contentObjNum = $contentObjNums[$pageObjNum];

            // Write page object
            $mediaBox = sprintf(
                '[%.4f %.4f %.4f %.4f]',
                $page['mediaBox'][0],
                $page['mediaBox'][1],
                $page['mediaBox'][2],
                $page['mediaBox'][3]
            );

            $offsets[$pageObjNum] = strlen($pdf);
            $pdf .= "{$pageObjNum} 0 obj\n";
            $pdf .= "<<\n";
            $pdf .= "/Type /Page\n";
            $pdf .= "/Parent {$pagesObjNum} 0 R\n";
            $pdf .= "/MediaBox {$mediaBox}\n";
            $pdf .= "/Resources <<\n";
            $pdf .= "  /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]\n";
            $pdf .= "  /Font << /F1 {$fontObjNum} 0 R >>\n";
            $pdf .= ">>\n";
            $pdf .= "/Contents {$contentObjNum} 0 R\n";
            $pdf .= ">>\n";
            $pdf .= "endobj\n";

            // Write content stream
            $stream = $page['contentStream'];
            $streamLength = strlen($stream);

            $offsets[$contentObjNum] = strlen($pdf);
            $pdf .= "{$contentObjNum} 0 obj\n";
            $pdf .= "<<\n/Length {$streamLength}\n>>\n";
            $pdf .= "stream\n";
            $pdf .= $stream;
            $pdf .= "\nendstream\n";
            $pdf .= "endobj\n";
        }

        // Write xref table
        $xrefOffset = strlen($pdf);
        $maxObjNum = max(array_keys($offsets));

        $pdf .= "xref\n";
        $pdf .= "0 " . ($maxObjNum + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $maxObjNum; $i++) {
            if (isset($offsets[$i])) {
                $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
            } else {
                $pdf .= "0000000000 00000 f \n";
            }
        }

        // Trailer
        $pdf .= "trailer\n";
        $pdf .= "<<\n";
        $pdf .= "/Size " . ($maxObjNum + 1) . "\n";
        $pdf .= "/Root {$catalogObjNum} 0 R\n";
        $pdf .= "/Info {$infoObjNum} 0 R\n";
        $pdf .= ">>\n";
        $pdf .= "startxref\n";
        $pdf .= "{$xrefOffset}\n";
        $pdf .= "%%EOF\n";

        return $pdf;
    }

    /**
     * Build the /Info dictionary object
     *
     * @param int $objNum Object number
     * @return string Info object content
     */
    protected function buildInfoObject(int $objNum): string
    {
        $obj = "{$objNum} 0 obj\n";
        $obj .= "<<\n";

        foreach ($this->metadata as $key => $value) {
            $obj .= "/{$key} " . $this->encodePdfString($value) . "\n";
        }

        $obj .= ">>\n";
        $obj .= "endobj\n";

        return $obj;
    }

    /**
     * Encode string for PDF
     *
     * @param string $str String to encode
     * @return string PDF string literal
     */
    protected function encodePdfString(string $str): string
    {
        // Escape special characters
        $str = str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '\\r', '\\n'], $str);
        return '(' . $str . ')';
    }

    /**
     * Parse pages from PDF content
     *
     * @return array<array{mediaBox: array<float>, contentStream: string}>
     */
    protected function parsePages(): array
    {
        $pages = [];

        foreach ($this->objects as $objNum => $objContent) {
            if ($this->isPageObject($objContent)) {
                $page = $this->extractPageData($objContent, $objNum);
                if ($page !== null) {
                    $pages[] = $page;
                }
            }
        }

        return $pages;
    }

    /**
     * Check if object is a Page object
     *
     * @param string $content Object content
     * @return bool
     */
    protected function isPageObject(string $content): bool
    {
        return preg_match('/\/Type\s*\/Page\b(?!s)/s', $content) === 1;
    }

    /**
     * Extract page data
     *
     * @param string $pageContent Page object content
     * @param int $pageObjNum Page object number
     * @return array{mediaBox: array<float>, contentStream: string}|null
     */
    protected function extractPageData(string $pageContent, int $pageObjNum): ?array
    {
        // Extract MediaBox
        $mediaBox = [0, 0, 612, 792]; // Default US Letter
        if (preg_match('/\/MediaBox\s*\[\s*([\d.-]+)\s+([\d.-]+)\s+([\d.-]+)\s+([\d.-]+)\s*\]/', $pageContent, $mbMatch)) {
            $mediaBox = [
                (float)$mbMatch[1],
                (float)$mbMatch[2],
                (float)$mbMatch[3],
                (float)$mbMatch[4],
            ];
        }

        // Extract content stream
        $contentStream = '';
        if (preg_match('/\/Contents\s+(\d+)\s+0\s+R/', $pageContent, $cMatch)) {
            $contentObjNum = (int)$cMatch[1];
            if (isset($this->objects[$contentObjNum])) {
                $contentStream = $this->extractStreamContent($this->objects[$contentObjNum], $contentObjNum);
            }
        } elseif (preg_match('/\/Contents\s*\[([\d\s0R]+)\]/s', $pageContent, $cMatch)) {
            if (preg_match_all('/(\d+)\s+0\s+R/', $cMatch[1], $refs)) {
                foreach ($refs[1] as $ref) {
                    $refNum = (int)$ref;
                    if (isset($this->objects[$refNum])) {
                        $contentStream .= $this->extractStreamContent($this->objects[$refNum], $refNum);
                    }
                }
            }
        }

        return [
            'mediaBox' => $mediaBox,
            'contentStream' => $contentStream,
        ];
    }

    /**
     * Extract stream content from an object
     *
     * @param string $objContent Object content
     * @param int $objNum Object number
     * @return string Decoded stream content
     */
    protected function extractStreamContent(string $objContent, int $objNum): string
    {
        // Check if stream is in the object content
        if (preg_match('/stream\s*(.*?)\s*endstream/s', $objContent, $match)) {
            return $this->decodeStream($objContent, $match[1]);
        }

        // Look for stream in full content
        $pattern = '/' . $objNum . '\s+0\s+obj\s*<<[^>]*>>\s*stream\s*(.*?)\s*endstream/s';
        if (preg_match($pattern, $this->pdfContent, $match)) {
            return $this->decodeStream($objContent, $match[1]);
        }

        return '';
    }

    /**
     * Decode stream content
     *
     * @param string $objContent Object dictionary
     * @param string $streamData Raw stream data
     * @return string Decoded stream
     */
    protected function decodeStream(string $objContent, string $streamData): string
    {
        if (preg_match('/\/Filter\s*\/FlateDecode/', $objContent)) {
            $decoded = @gzuncompress($streamData);
            if ($decoded !== false) {
                return $decoded;
            }
            $decoded = @gzinflate($streamData);
            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $streamData;
    }
}
