<?php

/**
 * PdfSplitter.php
 *
 * PDF Split functionality - extracts pages from a PDF document.
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
 * PDF Splitter
 *
 * Splits a PDF document into multiple parts or extracts specific pages.
 *
 * @category  Library
 * @package   Pdf
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type PageData array{
 *     'mediaBox': array<float>,
 *     'contentStream': string,
 *     'fonts': array<string, array<string, mixed>>,
 *     'objNum': int,
 * }
 */
class PdfSplitter
{
    /**
     * Source PDF content
     */
    protected string $pdfContent = '';

    /**
     * Parsed pages from the source PDF
     *
     * @var array<PageData>
     */
    protected array $pages = [];

    /**
     * All extracted objects from source
     *
     * @var array<int, string>
     */
    protected array $objects = [];

    /**
     * PDF version from source
     */
    protected string $pdfVersion = '1.7';

    /**
     * Resources dictionary content (ColorSpace, XObject, etc.)
     */
    protected string $resourcesContent = '';

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
            throw new PdfException("Cannot read PDF file: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new PdfException("Failed to read PDF file: {$filePath}");
        }

        return $this->loadContent($content);
    }

    /**
     * Load PDF from content
     *
     * @param string $pdfContent Raw PDF content
     * @return static
     * @throws PdfException If content is not valid PDF
     */
    public function loadContent(string $pdfContent): static
    {
        if (strpos($pdfContent, '%PDF-') !== 0) {
            throw new PdfException('Invalid PDF content');
        }

        $this->pdfContent = $pdfContent;
        $this->parseVersion();
        $this->extractObjects();
        $this->extractResources();
        $this->parsePages();

        return $this;
    }

    /**
     * Get the total number of pages in the source PDF
     *
     * @return int Number of pages
     */
    public function getPageCount(): int
    {
        return count($this->pages);
    }

    /**
     * Get the PDF version
     *
     * @return string PDF version
     */
    public function getVersion(): string
    {
        return $this->pdfVersion;
    }

    /**
     * Extract specific pages to a new PDF
     *
     * @param array<int>|string $pages Page numbers (1-indexed) or range string
     * @return string New PDF content with extracted pages
     * @throws PdfException If no valid pages specified
     */
    public function extractPages(array|string $pages): string
    {
        $pageNumbers = $this->resolvePageNumbers($pages);

        if (empty($pageNumbers)) {
            throw new PdfException('No valid pages specified for extraction');
        }

        return $this->buildPdfWithPages($pageNumbers);
    }

    /**
     * Extract a single page to a new PDF
     *
     * @param int $pageNumber Page number (1-indexed)
     * @return string New PDF content with the single page
     * @throws PdfException If page number is invalid
     */
    public function extractPage(int $pageNumber): string
    {
        if ($pageNumber < 1 || $pageNumber > count($this->pages)) {
            throw new PdfException("Invalid page number: {$pageNumber}");
        }

        return $this->extractPages([$pageNumber]);
    }

    /**
     * Split PDF into individual page files
     *
     * @param string $outputDir Output directory
     * @param string $prefix Filename prefix (default: 'page_')
     * @return array<string> Array of created file paths
     * @throws PdfException If output directory is not writable
     */
    public function splitToFiles(string $outputDir, string $prefix = 'page_'): array
    {
        if (!is_dir($outputDir) || !is_writable($outputDir)) {
            throw new PdfException("Output directory is not writable: {$outputDir}");
        }

        $files = [];
        $totalPages = count($this->pages);

        for ($i = 1; $i <= $totalPages; $i++) {
            $pageContent = $this->extractPage($i);
            $fullPath = rtrim($outputDir, '/') . '/' . $prefix . sprintf('%03d.pdf', $i);

            if (file_put_contents($fullPath, $pageContent) !== false) {
                $files[] = $fullPath;
            }
        }

        return $files;
    }

    /**
     * Split PDF into chunks of N pages each
     *
     * @param int $pagesPerChunk Number of pages per chunk
     * @return array<string> Array of PDF content strings
     * @throws PdfException If invalid chunk size
     */
    public function splitByPageCount(int $pagesPerChunk): array
    {
        if ($pagesPerChunk < 1) {
            throw new PdfException('Pages per chunk must be at least 1');
        }

        $chunks = [];
        $totalPages = count($this->pages);

        for ($start = 1; $start <= $totalPages; $start += $pagesPerChunk) {
            $end = min($start + $pagesPerChunk - 1, $totalPages);
            $chunks[] = $this->extractPages(range($start, $end));
        }

        return $chunks;
    }

    /**
     * Split PDF at specific page numbers
     *
     * @param array<int> $splitPoints Page numbers where to split (split before these pages)
     * @return array<string> Array of PDF content strings
     */
    public function splitAtPages(array $splitPoints): array
    {
        $totalPages = count($this->pages);
        $splitPoints = array_unique(array_filter($splitPoints, fn($p) => $p > 1 && $p <= $totalPages));
        sort($splitPoints);

        $chunks = [];
        $currentStart = 1;

        foreach ($splitPoints as $splitPoint) {
            if ($splitPoint > $currentStart) {
                $chunks[] = $this->extractPages(range($currentStart, $splitPoint - 1));
            }
            $currentStart = $splitPoint;
        }

        // Add remaining pages
        if ($currentStart <= $totalPages) {
            $chunks[] = $this->extractPages(range($currentStart, $totalPages));
        }

        return $chunks;
    }

    /**
     * Extract odd pages only
     *
     * @return string PDF content with odd pages
     */
    public function extractOddPages(): string
    {
        $oddPages = [];
        for ($i = 1; $i <= count($this->pages); $i += 2) {
            $oddPages[] = $i;
        }
        return $this->extractPages($oddPages);
    }

    /**
     * Extract even pages only
     *
     * @return string PDF content with even pages
     */
    public function extractEvenPages(): string
    {
        $evenPages = [];
        for ($i = 2; $i <= count($this->pages); $i += 2) {
            $evenPages[] = $i;
        }
        return $this->extractPages($evenPages);
    }

    /**
     * Extract pages in reverse order
     *
     * @return string PDF content with pages reversed
     */
    public function extractReversed(): string
    {
        return $this->extractPages(range(count($this->pages), 1, -1));
    }

    /**
     * Parse PDF version from header
     */
    protected function parseVersion(): void
    {
        if (preg_match('/^%PDF-(\d+\.\d+)/', $this->pdfContent, $matches)) {
            $this->pdfVersion = $matches[1];
        }
    }

    /**
     * Extract all objects from PDF
     */
    protected function extractObjects(): void
    {
        $this->objects = [];

        // Match objects: N 0 obj ... endobj
        $pattern = '/(\d+)\s+0\s+obj\s*(.*?)\s*endobj/s';
        if (preg_match_all($pattern, $this->pdfContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $objNum = (int)$match[1];
                $this->objects[$objNum] = $match[2];
            }
        }
    }

    /**
     * Extract shared Resources dictionary from PDF
     */
    protected function extractResources(): void
    {
        foreach ($this->objects as $objNum => $objContent) {
            if (str_contains($objContent, '/Type /Page') && !str_contains($objContent, '/Type /Pages')) {
                // Try reference pattern: /Resources N 0 R
                if (preg_match('/\/Resources\s+(\d+)\s+\d+\s+R/', $objContent, $match)) {
                    $resObjNum = (int)$match[1];
                    if (isset($this->objects[$resObjNum])) {
                        $this->resourcesContent = $this->objects[$resObjNum];
                        return;
                    }
                }
                // Try inline pattern: /Resources << ... >>
                if (preg_match('/\/Resources\s*<</', $objContent)) {
                    $this->resourcesContent = $this->extractNestedDict($objContent, '/Resources');
                    if (!empty($this->resourcesContent)) {
                        return;
                    }
                }
            }
        }
    }

    /**
     * Extract a nested dictionary from PDF content
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
     * Parse all pages from the PDF
     */
    protected function parsePages(): void
    {
        $this->pages = [];

        foreach ($this->objects as $objNum => $objContent) {
            // Check if this is a Page object (not Pages)
            if (preg_match('/\/Type\s*\/Page\b(?!s)/s', $objContent)) {
                $page = $this->extractPageData($objContent, $objNum);
                if ($page !== null) {
                    $this->pages[] = $page;
                }
            }
        }
    }

    /**
     * Extract page data including content stream
     *
     * @param string $pageContent Page object content
     * @param int $pageObjNum Page object number
     * @return PageData|null
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
            // Array of content streams
            if (preg_match_all('/(\d+)\s+0\s+R/', $cMatch[1], $refs)) {
                foreach ($refs[1] as $ref) {
                    $refNum = (int)$ref;
                    if (isset($this->objects[$refNum])) {
                        $contentStream .= $this->extractStreamContent($this->objects[$refNum], $refNum);
                    }
                }
            }
        }

        // Extract fonts from resources
        $fonts = $this->extractFonts($pageContent);

        return [
            'mediaBox' => $mediaBox,
            'contentStream' => $contentStream,
            'fonts' => $fonts,
            'objNum' => $pageObjNum,
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
     * @return array<string, array<string, mixed>>
     */
    protected function extractFonts(string $pageContent): array
    {
        $fonts = [];

        // Look for font references in Resources
        if (preg_match('/\/Font\s*<<([^>]*)>>/s', $pageContent, $fontMatch)) {
            // Inline font dictionary
            if (preg_match_all('/\/(\w+)\s+(\d+)\s+0\s+R/', $fontMatch[1], $refs, PREG_SET_ORDER)) {
                foreach ($refs as $ref) {
                    $fontName = $ref[1];
                    $fontObjNum = (int)$ref[2];
                    if (isset($this->objects[$fontObjNum])) {
                        $fonts[$fontName] = $this->parseFontObject($this->objects[$fontObjNum]);
                    }
                }
            }
        } elseif (preg_match('/\/Resources\s+(\d+)\s+0\s+R/', $pageContent, $resMatch)) {
            // Resources in separate object
            $resObjNum = (int)$resMatch[1];
            if (isset($this->objects[$resObjNum])) {
                $resContent = $this->objects[$resObjNum];
                if (preg_match('/\/Font\s*<<([^>]*)>>/s', $resContent, $fontMatch)) {
                    if (preg_match_all('/\/(\w+)\s+(\d+)\s+0\s+R/', $fontMatch[1], $refs, PREG_SET_ORDER)) {
                        foreach ($refs as $ref) {
                            $fontName = $ref[1];
                            $fontObjNum = (int)$ref[2];
                            if (isset($this->objects[$fontObjNum])) {
                                $fonts[$fontName] = $this->parseFontObject($this->objects[$fontObjNum]);
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
     * @return array<int> Array of 1-indexed page numbers
     */
    protected function resolvePageNumbers(array|string $pages): array
    {
        $totalPages = count($this->pages);

        if ($pages === 'all') {
            return range(1, max(1, $totalPages));
        }

        if (is_string($pages)) {
            return $this->parsePageRange($pages, $totalPages);
        }

        // Filter valid page numbers
        return array_values(array_filter($pages, fn($p) => $p >= 1 && $p <= $totalPages));
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

        return array_values(array_unique($pages));
    }

    /**
     * Build a new PDF with selected pages
     *
     * @param array<int> $pageNumbers Page numbers to include (1-indexed)
     * @return string PDF content
     */
    protected function buildPdfWithPages(array $pageNumbers): string
    {
        $objectNumber = 1;

        // Collect all unique fonts needed from selected pages
        $allFonts = [];
        foreach ($pageNumbers as $pageNum) {
            if (isset($this->pages[$pageNum - 1])) {
                $page = $this->pages[$pageNum - 1];
                foreach ($page['fonts'] as $name => $font) {
                    $key = $font['baseFont'] ?? 'Helvetica';
                    if (!isset($allFonts[$key])) {
                        $allFonts[$key] = $font;
                        $allFonts[$key]['name'] = $name;
                    }
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
        $catalogObjNum = $objectNumber++;
        $pagesObjNum = $objectNumber++;

        // Create font objects
        $fontObjects = [];
        $fontObjNums = [];
        foreach ($allFonts as $key => $font) {
            $fontObjNum = $objectNumber++;
            $fontObjNums[$key] = $fontObjNum;
            $fontObjects[$fontObjNum] = $this->buildFontObject($fontObjNum, $font);
        }

        // ColorSpace object (if present in original)
        $colorSpaceObjNum = null;
        $colorSpaceContent = '';
        if (!empty($this->resourcesContent)) {
            if (preg_match('/\/ColorSpace\s*<<\s*\/CS1\s+(\d+)\s+\d+\s+R/', $this->resourcesContent, $csMatch)) {
                $origCsObjNum = (int)$csMatch[1];
                if (isset($this->objects[$origCsObjNum])) {
                    $colorSpaceObjNum = $objectNumber++;
                    $colorSpaceContent = $this->objects[$origCsObjNum];
                }
            }
        }

        // Create page objects with content streams
        $pageObjNums = [];
        $contentObjNums = [];

        foreach ($pageNumbers as $pageNum) {
            if (isset($this->pages[$pageNum - 1])) {
                $pageObjNum = $objectNumber++;
                $contentObjNum = $objectNumber++;
                $pageObjNums[] = $pageObjNum;
                $contentObjNums[$pageObjNum] = $contentObjNum;
            }
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

        // Write ColorSpace object (if present)
        if ($colorSpaceObjNum !== null && !empty($colorSpaceContent)) {
            $offsets[$colorSpaceObjNum] = strlen($pdf);
            $pdf .= "{$colorSpaceObjNum} 0 obj\n";
            $pdf .= $colorSpaceContent . "\n";
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
        $pageIndex = 0;
        foreach ($pageNumbers as $pageNum) {
            if (!isset($this->pages[$pageNum - 1])) {
                continue;
            }

            $page = $this->pages[$pageNum - 1];
            $pageObjNum = $pageObjNums[$pageIndex];
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

            $pageIndex++;
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
