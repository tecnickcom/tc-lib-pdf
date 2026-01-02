<?php

/**
 * PdfMerger.php
 *
 * PDF Merge functionality - combines multiple PDF documents into one.
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
 * PDF Merger
 *
 * Merges multiple PDF documents into a single PDF file.
 *
 * @category  Library
 * @package   Pdf
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type PdfSource array{
 *     'content': string,
 *     'pages'?: array<int>|string,
 *     'file'?: string,
 * }
 *
 * @phpstan-type ParsedPage array{
 *     'mediaBox': array<float>,
 *     'contentStream': string,
 *     'resources': array<string, mixed>,
 *     'fonts': array<string, array<string, mixed>>,
 * }
 */
class PdfMerger
{
    /**
     * List of PDF sources to merge
     *
     * @var array<PdfSource>
     */
    protected array $sources = [];

    /**
     * Current object number
     */
    protected int $objectNumber = 1;

    /**
     * PDF version to use
     */
    protected string $pdfVersion = '1.7';

    /**
     * Resources dictionary content (ColorSpace, XObject, etc.)
     */
    protected string $resourcesContent = '';

    /**
     * Collected pages with their content
     *
     * @var array<ParsedPage>
     */
    protected array $collectedPages = [];

    /**
     * Add a PDF file to merge
     *
     * @param string $filePath Path to PDF file
     * @param array<int>|string $pages Page numbers to include ('all' or array of page numbers, 1-indexed)
     * @return static
     * @throws PdfException If file cannot be read
     */
    public function addFile(string $filePath, array|string $pages = 'all'): static
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new PdfException("Cannot read PDF file: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new PdfException("Failed to read PDF file: {$filePath}");
        }

        $this->sources[] = [
            'content' => $content,
            'pages' => $pages,
            'file' => $filePath,
        ];

        return $this;
    }

    /**
     * Add PDF content directly
     *
     * @param string $pdfContent Raw PDF content
     * @param array<int>|string $pages Page numbers to include ('all' or array of page numbers, 1-indexed)
     * @return static
     */
    public function addContent(string $pdfContent, array|string $pages = 'all'): static
    {
        $this->sources[] = [
            'content' => $pdfContent,
            'pages' => $pages,
        ];

        return $this;
    }

    /**
     * Set the PDF version for the output
     *
     * @param string $version PDF version (e.g., '1.4', '1.7', '2.0')
     * @return static
     */
    public function setVersion(string $version): static
    {
        $this->pdfVersion = $version;
        return $this;
    }

    /**
     * Get the number of sources added
     *
     * @return int Number of PDF sources
     */
    public function getSourceCount(): int
    {
        return count($this->sources);
    }

    /**
     * Clear all sources
     *
     * @return static
     */
    public function clear(): static
    {
        $this->sources = [];
        $this->collectedPages = [];
        $this->objectNumber = 1;
        $this->resourcesContent = '';
        return $this;
    }

    /**
     * Merge all added PDFs and return the result
     *
     * @return string Merged PDF content
     * @throws PdfException If no sources added or merge fails
     */
    public function merge(): string
    {
        if (empty($this->sources)) {
            throw new PdfException('No PDF sources added for merging');
        }

        $this->collectedPages = [];
        $this->objectNumber = 1;

        // Parse and collect pages from all sources
        foreach ($this->sources as $source) {
            $this->processSource($source);
        }

        if (empty($this->collectedPages)) {
            throw new PdfException('No pages found in PDF sources');
        }

        return $this->buildMergedPdf();
    }

    /**
     * Merge and save to file
     *
     * @param string $outputPath Path to save merged PDF
     * @return bool True on success
     * @throws PdfException If merge or save fails
     */
    public function mergeToFile(string $outputPath): bool
    {
        $merged = $this->merge();
        $result = file_put_contents($outputPath, $merged);
        return $result !== false;
    }

    /**
     * Process a single PDF source
     *
     * @param PdfSource $source PDF source data
     */
    protected function processSource(array $source): void
    {
        $content = $source['content'];
        $pages = $source['pages'] ?? 'all';

        // Parse the PDF structure
        $parsedPages = $this->parsePdfContent($content);

        // Determine which pages to include
        $pageNumbers = $this->resolvePageNumbers($pages, count($parsedPages));

        // Add selected pages
        foreach ($pageNumbers as $pageNum) {
            if (isset($parsedPages[$pageNum - 1])) {
                $this->collectedPages[] = $parsedPages[$pageNum - 1];
            }
        }
    }

    /**
     * Parse PDF content and extract pages with their content streams
     *
     * @param string $content PDF content
     * @return array<ParsedPage>
     */
    protected function parsePdfContent(string $content): array
    {
        $pages = [];

        // Extract all objects from the PDF
        $objects = $this->extractObjects($content);

        // Extract resources (for ColorSpace preservation)
        $this->extractResourcesFromObjects($objects);

        // Find page objects and their content
        foreach ($objects as $objNum => $objContent) {
            if ($this->isPageObject($objContent)) {
                $page = $this->extractPageData($objContent, $objects, $content);
                if ($page !== null) {
                    $pages[] = $page;
                }
            }
        }

        return $pages;
    }

    /**
     * Extract all objects from PDF content
     *
     * @param string $content PDF content
     * @return array<int, string> Object number => object content
     */
    protected function extractObjects(string $content): array
    {
        $objects = [];

        // Match objects: N 0 obj ... endobj
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
     * Extract resources dictionary from objects
     *
     * @param array<int, string> $objects All objects
     */
    protected function extractResourcesFromObjects(array $objects): void
    {
        // Only extract if we don't already have resources content
        if (!empty($this->resourcesContent) && str_contains($this->resourcesContent, '/ColorSpace')) {
            return;
        }

        foreach ($objects as $objContent) {
            // Look for Resources dictionary with ColorSpace
            if (preg_match('/\/Resources\s+(\d+)\s+0\s+R/', $objContent, $match)) {
                $resourcesObjNum = (int)$match[1];
                if (isset($objects[$resourcesObjNum])) {
                    $content = $objects[$resourcesObjNum];
                    if (str_contains($content, '/ColorSpace')) {
                        $this->resourcesContent = $content;
                        return;
                    }
                }
            }
            // Also check for inline Resources dictionary
            if (preg_match('/\/Resources\s*<</', $objContent)) {
                $resourcesDict = $this->extractNestedDict($objContent, '/Resources');
                if (!empty($resourcesDict) && str_contains($resourcesDict, '/ColorSpace')) {
                    $this->resourcesContent = $resourcesDict;
                    return;
                }
            }
        }
    }

    /**
     * Extract a nested dictionary from content
     *
     * @param string $content Content to search
     * @param string $key Dictionary key to extract
     * @return string Extracted dictionary content
     */
    protected function extractNestedDict(string $content, string $key): string
    {
        $pattern = '/' . preg_quote($key, '/') . '\s*<</';
        if (!preg_match($pattern, $content, $match, PREG_OFFSET_CAPTURE)) {
            return '';
        }

        $startPos = $match[0][1] + strlen($match[0][0]) - 2;
        $depth = 0;
        $endPos = $startPos;

        for ($i = $startPos; $i < strlen($content); $i++) {
            if ($i < strlen($content) - 1 && $content[$i] === '<' && $content[$i + 1] === '<') {
                $depth++;
                $i++;
            } elseif ($i < strlen($content) - 1 && $content[$i] === '>' && $content[$i + 1] === '>') {
                $depth--;
                $i++;
                if ($depth === 0) {
                    $endPos = $i + 1;
                    break;
                }
            }
        }

        return substr($content, $startPos, $endPos - $startPos);
    }

    /**
     * Check if object content is a Page object
     *
     * @param string $content Object content
     * @return bool
     */
    protected function isPageObject(string $content): bool
    {
        // Must have /Type /Page but NOT /Type /Pages
        return preg_match('/\/Type\s*\/Page\b(?!s)/s', $content) === 1;
    }

    /**
     * Extract page data including content stream
     *
     * @param string $pageContent Page object content
     * @param array<int, string> $objects All objects
     * @param string $fullContent Full PDF content
     * @return ParsedPage|null
     */
    protected function extractPageData(string $pageContent, array $objects, string $fullContent): ?array
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
            if (isset($objects[$contentObjNum])) {
                $contentStream = $this->extractStreamContent($objects[$contentObjNum], $fullContent, $contentObjNum);
            }
        } elseif (preg_match('/\/Contents\s*\[([\d\s0R]+)\]/s', $pageContent, $cMatch)) {
            // Array of content streams
            if (preg_match_all('/(\d+)\s+0\s+R/', $cMatch[1], $refs)) {
                foreach ($refs[1] as $ref) {
                    $refNum = (int)$ref;
                    if (isset($objects[$refNum])) {
                        $contentStream .= $this->extractStreamContent($objects[$refNum], $fullContent, $refNum);
                    }
                }
            }
        }

        // Extract fonts from resources
        $fonts = $this->extractFonts($pageContent, $objects);

        return [
            'mediaBox' => $mediaBox,
            'contentStream' => $contentStream,
            'resources' => [],
            'fonts' => $fonts,
        ];
    }

    /**
     * Extract stream content from an object
     *
     * @param string $objContent Object content
     * @param string $fullContent Full PDF for stream lookup
     * @param int $objNum Object number
     * @return string Decoded stream content
     */
    protected function extractStreamContent(string $objContent, string $fullContent, int $objNum): string
    {
        // Check if stream is in the object content
        if (preg_match('/stream\s*(.*?)\s*endstream/s', $objContent, $match)) {
            return $this->decodeStream($objContent, $match[1]);
        }

        // Look for stream in full content
        $pattern = '/' . $objNum . '\s+0\s+obj\s*<<[^>]*>>\s*stream\s*(.*?)\s*endstream/s';
        if (preg_match($pattern, $fullContent, $match)) {
            return $this->decodeStream($objContent, $match[1]);
        }

        return '';
    }

    /**
     * Decode stream content (handle FlateDecode)
     *
     * @param string $objContent Object dictionary for filter info
     * @param string $streamData Raw stream data
     * @return string Decoded stream
     */
    protected function decodeStream(string $objContent, string $streamData): string
    {
        // Check for FlateDecode filter
        if (preg_match('/\/Filter\s*\/FlateDecode/', $objContent)) {
            $decoded = @gzuncompress($streamData);
            if ($decoded !== false) {
                return $decoded;
            }
            // Try gzinflate for raw deflate data
            $decoded = @gzinflate($streamData);
            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $streamData;
    }

    /**
     * Extract font information from page resources
     *
     * @param string $pageContent Page object content
     * @param array<int, string> $objects All objects
     * @return array<string, array<string, mixed>>
     */
    protected function extractFonts(string $pageContent, array $objects): array
    {
        $fonts = [];

        // Look for font references in Resources
        if (preg_match('/\/Font\s*<<([^>]*)>>/s', $pageContent, $fontMatch)) {
            // Inline font dictionary
            if (preg_match_all('/\/(\w+)\s+(\d+)\s+0\s+R/', $fontMatch[1], $refs, PREG_SET_ORDER)) {
                foreach ($refs as $ref) {
                    $fontName = $ref[1];
                    $fontObjNum = (int)$ref[2];
                    if (isset($objects[$fontObjNum])) {
                        $fonts[$fontName] = $this->parseFontObject($objects[$fontObjNum]);
                    }
                }
            }
        } elseif (preg_match('/\/Resources\s+(\d+)\s+0\s+R/', $pageContent, $resMatch)) {
            // Resources in separate object
            $resObjNum = (int)$resMatch[1];
            if (isset($objects[$resObjNum])) {
                $resContent = $objects[$resObjNum];
                if (preg_match('/\/Font\s*<<([^>]*)>>/s', $resContent, $fontMatch)) {
                    if (preg_match_all('/\/(\w+)\s+(\d+)\s+0\s+R/', $fontMatch[1], $refs, PREG_SET_ORDER)) {
                        foreach ($refs as $ref) {
                            $fontName = $ref[1];
                            $fontObjNum = (int)$ref[2];
                            if (isset($objects[$fontObjNum])) {
                                $fonts[$fontName] = $this->parseFontObject($objects[$fontObjNum]);
                            }
                        }
                    }
                }
            }
        }

        return $fonts;
    }

    /**
     * Parse font object to extract font details
     *
     * @param string $fontContent Font object content
     * @return array<string, mixed>
     */
    protected function parseFontObject(string $fontContent): array
    {
        $font = [
            'type' => 'Type1',
            'baseFont' => 'Helvetica',
            'encoding' => 'WinAnsiEncoding',
        ];

        if (preg_match('/\/Subtype\s*\/(\w+)/', $fontContent, $match)) {
            $font['type'] = $match[1];
        }

        if (preg_match('/\/BaseFont\s*\/(\S+)/', $fontContent, $match)) {
            $font['baseFont'] = $match[1];
        }

        if (preg_match('/\/Encoding\s*\/(\w+)/', $fontContent, $match)) {
            $font['encoding'] = $match[1];
        }

        return $font;
    }

    /**
     * Resolve page numbers from specification
     *
     * @param array<int>|string $pages Page specification
     * @param int $totalPages Total pages in document
     * @return array<int> Array of 1-indexed page numbers
     */
    protected function resolvePageNumbers(array|string $pages, int $totalPages): array
    {
        if ($pages === 'all') {
            return range(1, max(1, $totalPages));
        }

        if (is_string($pages)) {
            return $this->parsePageRange($pages, $totalPages);
        }

        return array_filter($pages, fn($p) => $p >= 1 && $p <= $totalPages);
    }

    /**
     * Parse page range string
     *
     * @param string $range Range string (e.g., "1-5", "1,3,5", "1-3,5,7-9")
     * @param int $totalPages Total pages available
     * @return array<int> Array of page numbers
     */
    protected function parsePageRange(string $range, int $totalPages): array
    {
        $pages = [];
        $parts = explode(',', $range);

        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, '-') !== false) {
                [$start, $end] = explode('-', $part, 2);
                $start = max(1, (int)$start);
                $end = min($totalPages, (int)$end);
                for ($i = $start; $i <= $end; $i++) {
                    $pages[] = $i;
                }
            } else {
                $pageNum = (int)$part;
                if ($pageNum >= 1 && $pageNum <= $totalPages) {
                    $pages[] = $pageNum;
                }
            }
        }

        return array_unique($pages);
    }

    /**
     * Build the final merged PDF
     *
     * @return string Complete PDF content
     */
    protected function buildMergedPdf(): string
    {
        $this->objectNumber = 1;

        // Collect all unique fonts needed
        $allFonts = [];
        foreach ($this->collectedPages as $page) {
            foreach ($page['fonts'] as $name => $font) {
                $key = $font['baseFont'] ?? 'Helvetica';
                if (!isset($allFonts[$key])) {
                    $allFonts[$key] = $font;
                    $allFonts[$key]['name'] = $name;
                }
            }
        }

        // Ensure we have at least one font
        if (empty($allFonts)) {
            $allFonts['Helvetica'] = [
                'type' => 'Type1',
                'baseFont' => 'Helvetica',
                'encoding' => 'WinAnsiEncoding',
                'name' => 'F1',
            ];
        }

        // Start building PDF
        $pdf = "%PDF-{$this->pdfVersion}\n";
        $pdf .= "%\xe2\xe3\xcf\xd3\n";

        $offsets = [];

        // Object 1: Catalog
        $catalogObjNum = $this->objectNumber++;
        $pagesObjNum = $this->objectNumber++;

        // Create font objects
        $fontObjects = [];
        $fontObjNums = [];
        foreach ($allFonts as $key => $font) {
            $fontObjNum = $this->objectNumber++;
            $fontObjNums[$key] = $fontObjNum;
            $fontObjects[$fontObjNum] = $this->buildFontObject($fontObjNum, $font);
        }

        // Allocate ColorSpace object if we have ColorSpace in resources
        $colorSpaceObjNum = null;
        if (!empty($this->resourcesContent) && str_contains($this->resourcesContent, '/ColorSpace')) {
            $colorSpaceObjNum = $this->objectNumber++;
        }

        // Create page objects with content streams
        $pageObjNums = [];
        $contentObjNums = [];

        foreach ($this->collectedPages as $index => $page) {
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

        // Write font objects
        foreach ($fontObjects as $objNum => $fontObj) {
            $offsets[$objNum] = strlen($pdf);
            $pdf .= $fontObj;
        }

        // ColorSpace (if needed)
        if ($colorSpaceObjNum !== null) {
            $offsets[$colorSpaceObjNum] = strlen($pdf);
            $pdf .= "{$colorSpaceObjNum} 0 obj\n";
            $pdf .= "[/Separation /Black /DeviceCMYK << /FunctionType 2 /Domain [0 1] /C0 [0 0 0 0] /C1 [0 0 0 1] /N 1 >>]\n";
            $pdf .= "endobj\n";
        }

        // Build font resources string
        $fontResources = '';
        $fontIndex = 1;
        foreach ($fontObjNums as $key => $objNum) {
            $fontResources .= "/F{$fontIndex} {$objNum} 0 R ";
            $fontIndex++;
        }

        // Write page and content objects
        foreach ($this->collectedPages as $index => $page) {
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
            $pdf .= "  /Font << {$fontResources}>>\n";
            if ($colorSpaceObjNum !== null) {
                $pdf .= "  /ColorSpace << /CS1 {$colorSpaceObjNum} 0 R >>\n";
            }
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
        $pdf .= ">>\n";
        $pdf .= "startxref\n";
        $pdf .= "{$xrefOffset}\n";
        $pdf .= "%%EOF\n";

        return $pdf;
    }

    /**
     * Build a font object
     *
     * @param int $objNum Object number
     * @param array<string, mixed> $font Font data
     * @return string Font object
     */
    protected function buildFontObject(int $objNum, array $font): string
    {
        $type = $font['type'] ?? 'Type1';
        $baseFont = $font['baseFont'] ?? 'Helvetica';
        $encoding = $font['encoding'] ?? 'WinAnsiEncoding';

        $obj = "{$objNum} 0 obj\n";
        $obj .= "<<\n";
        $obj .= "/Type /Font\n";
        $obj .= "/Subtype /{$type}\n";
        $obj .= "/BaseFont /{$baseFont}\n";
        if ($encoding) {
            $obj .= "/Encoding /{$encoding}\n";
        }
        $obj .= ">>\n";
        $obj .= "endobj\n";

        return $obj;
    }
}
