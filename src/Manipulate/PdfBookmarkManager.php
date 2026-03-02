<?php

/**
 * PdfBookmarkManager.php
 *
 * PDF Bookmark (Outlines) management functionality.
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
 * PDF Bookmark Manager
 *
 * Manages PDF document outlines (bookmarks/table of contents).
 *
 * @category  Library
 * @package   Pdf
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type Bookmark array{
 *     'title': string,
 *     'page': int,
 *     'level': int,
 *     'y'?: float,
 *     'children'?: array<Bookmark>
 * }
 */
class PdfBookmarkManager
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
     * Bookmarks
     *
     * @var array<array{title: string, page: int, level: int, y?: float}>
     */
    protected array $bookmarks = [];

    /**
     * Parsed objects
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
     * Resources dictionary content (ColorSpace, XObject, etc.)
     */
    protected string $resourcesContent = '';

    /**
     * Load a PDF file
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
        $this->parseExistingBookmarks();

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
        $this->parseExistingBookmarks();

        return $this;
    }

    /**
     * Parse existing bookmarks from PDF
     */
    protected function parseExistingBookmarks(): void
    {
        // Extract PDF version
        if (preg_match('/%PDF-(\d+\.\d+)/', $this->pdfContent, $match)) {
            $this->pdfVersion = $match[1];
        }

        // Extract all objects
        $this->objects = $this->extractObjects($this->pdfContent);

        // Extract shared Resources dictionary
        $this->extractResources();

        // Find and parse /Outlines
        $this->parseOutlines();
    }

    /**
     * Extract shared Resources dictionary from PDF
     */
    protected function extractResources(): void
    {
        foreach ($this->objects as $objNum => $objContent) {
            // Check if this is a Page object
            if ($this->isPageObject($objContent)) {
                // Try reference pattern first: /Resources N 0 R
                if (preg_match('/\/Resources\s+(\d+)\s+0\s+R/', $objContent, $match)) {
                    $resObjNum = (int)$match[1];
                    if (isset($this->objects[$resObjNum])) {
                        $this->resourcesContent = $this->objects[$resObjNum];
                        return;
                    }
                }
                // Try inline pattern: /Resources << ... >> (with nested dicts)
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
     *
     * @param string $content PDF content
     * @param string $key Dictionary key (e.g., '/Resources')
     * @return string Extracted dictionary or empty string
     */
    protected function extractNestedDict(string $content, string $key): string
    {
        $pos = strpos($content, $key);
        if ($pos === false) {
            return '';
        }

        // Find << after the key
        $start = strpos($content, '<<', $pos);
        if ($start === false) {
            return '';
        }

        // Count nested << and >> to find matching close
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
     * Parse outline objects
     */
    protected function parseOutlines(): void
    {
        // Find /Outlines reference in catalog
        if (!preg_match('/\/Outlines\s+(\d+)\s+0\s+R/', $this->pdfContent, $match)) {
            return;
        }

        $outlinesObjNum = (int)$match[1];
        if (!isset($this->objects[$outlinesObjNum])) {
            return;
        }

        // Parse outline items
        $this->parseOutlineItem($this->objects[$outlinesObjNum], 0);
    }

    /**
     * Parse an outline item
     *
     * @param string $content Outline object content
     * @param int $level Current nesting level
     */
    protected function parseOutlineItem(string $content, int $level): void
    {
        // Find first child
        if (preg_match('/\/First\s+(\d+)\s+0\s+R/', $content, $match)) {
            $firstObjNum = (int)$match[1];
            if (isset($this->objects[$firstObjNum])) {
                $this->parseOutlineEntry($this->objects[$firstObjNum], $level);
            }
        }
    }

    /**
     * Parse an outline entry and its siblings
     *
     * @param string $content Outline entry content
     * @param int $level Current level
     */
    protected function parseOutlineEntry(string $content, int $level): void
    {
        // Extract title
        $title = '';
        if (preg_match('/\/Title\s*\(([^)]*)\)/', $content, $match)) {
            $title = $this->decodePdfString($match[1]);
        } elseif (preg_match('/\/Title\s*<([^>]*)>/', $content, $match)) {
            $title = $this->decodeHexString($match[1]);
        }

        // Extract destination page
        $page = 1;
        if (preg_match('/\/Dest\s*\[\s*(\d+)\s+0\s+R/', $content, $match)) {
            // Find page number from page object reference
            $pageObjNum = (int)$match[1];
            $page = $this->getPageNumber($pageObjNum);
        }

        if (!empty($title)) {
            $this->bookmarks[] = [
                'title' => $title,
                'page' => $page,
                'level' => $level,
            ];
        }

        // Parse children
        if (preg_match('/\/First\s+(\d+)\s+0\s+R/', $content, $match)) {
            $childObjNum = (int)$match[1];
            if (isset($this->objects[$childObjNum])) {
                $this->parseOutlineEntry($this->objects[$childObjNum], $level + 1);
            }
        }

        // Parse next sibling
        if (preg_match('/\/Next\s+(\d+)\s+0\s+R/', $content, $match)) {
            $nextObjNum = (int)$match[1];
            if (isset($this->objects[$nextObjNum])) {
                $this->parseOutlineEntry($this->objects[$nextObjNum], $level);
            }
        }
    }

    /**
     * Get page number from page object reference
     *
     * @param int $pageObjNum Page object number
     * @return int Page number (1-indexed)
     */
    protected function getPageNumber(int $pageObjNum): int
    {
        $pageNum = 1;
        foreach ($this->objects as $objNum => $content) {
            if ($this->isPageObject($content)) {
                if ($objNum === $pageObjNum) {
                    return $pageNum;
                }
                $pageNum++;
            }
        }
        return 1;
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
     * Decode PDF string
     *
     * @param string $str PDF string
     * @return string Decoded string
     */
    protected function decodePdfString(string $str): string
    {
        return str_replace(['\\n', '\\r', '\\t', '\\(', '\\)', '\\\\'], ["\n", "\r", "\t", '(', ')', '\\'], $str);
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
     * Get all bookmarks
     *
     * @return array<array{title: string, page: int, level: int}>
     */
    public function getBookmarks(): array
    {
        return $this->bookmarks;
    }

    /**
     * Get bookmark count
     *
     * @return int Number of bookmarks
     */
    public function getBookmarkCount(): int
    {
        return count($this->bookmarks);
    }

    /**
     * Add a bookmark
     *
     * @param string $title Bookmark title
     * @param int $page Target page number (1-indexed)
     * @param int $level Nesting level (0 = top level)
     * @param float|null $y Y position on page (null = top)
     * @return static
     */
    public function addBookmark(string $title, int $page, int $level = 0, ?float $y = null): static
    {
        $bookmark = [
            'title' => $title,
            'page' => max(1, $page),
            'level' => max(0, $level),
        ];

        if ($y !== null) {
            $bookmark['y'] = $y;
        }

        $this->bookmarks[] = $bookmark;

        return $this;
    }

    /**
     * Add multiple bookmarks at once
     *
     * @param array<array{title: string, page: int, level?: int, y?: float}> $bookmarks
     * @return static
     */
    public function addBookmarks(array $bookmarks): static
    {
        foreach ($bookmarks as $bookmark) {
            $this->addBookmark(
                $bookmark['title'],
                $bookmark['page'],
                $bookmark['level'] ?? 0,
                $bookmark['y'] ?? null
            );
        }

        return $this;
    }

    /**
     * Remove all bookmarks
     *
     * @return static
     */
    public function removeAllBookmarks(): static
    {
        $this->bookmarks = [];
        return $this;
    }

    /**
     * Remove bookmarks by page
     *
     * @param int $page Page number
     * @return static
     */
    public function removeBookmarksByPage(int $page): static
    {
        $this->bookmarks = array_filter(
            $this->bookmarks,
            fn($b) => $b['page'] !== $page
        );
        $this->bookmarks = array_values($this->bookmarks);

        return $this;
    }

    /**
     * Remove bookmarks by level
     *
     * @param int $level Level to remove
     * @return static
     */
    public function removeBookmarksByLevel(int $level): static
    {
        $this->bookmarks = array_filter(
            $this->bookmarks,
            fn($b) => $b['level'] !== $level
        );
        $this->bookmarks = array_values($this->bookmarks);

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
     * Rebuild PDF with updated bookmarks
     *
     * @return string Modified PDF content
     */
    protected function rebuildPdf(): string
    {
        $pages = $this->parsePages();

        if (empty($pages)) {
            throw new PdfException('No pages found in PDF');
        }

        $this->objectNumber = 1;

        $pdf = "%PDF-{$this->pdfVersion}\n";
        $pdf .= "%\xe2\xe3\xcf\xd3\n";

        $offsets = [];

        // Allocate object numbers
        $catalogObjNum = $this->objectNumber++;
        $pagesObjNum = $this->objectNumber++;
        $outlinesObjNum = empty($this->bookmarks) ? null : $this->objectNumber++;

        // Font object
        $fontObjNum = $this->objectNumber++;

        // ColorSpace object (if present in original)
        $colorSpaceObjNum = null;
        $colorSpaceContent = '';
        if (!empty($this->resourcesContent)) {
            if (preg_match('/\/ColorSpace\s*<<\s*\/CS1\s+(\d+)\s+0\s+R/', $this->resourcesContent, $csMatch)) {
                $origCsObjNum = (int)$csMatch[1];
                if (isset($this->objects[$origCsObjNum])) {
                    $colorSpaceObjNum = $this->objectNumber++;
                    $colorSpaceContent = $this->objects[$origCsObjNum];
                }
            }
        }

        // Allocate bookmark objects if any
        $bookmarkObjNums = [];
        foreach ($this->bookmarks as $index => $bookmark) {
            $bookmarkObjNums[$index] = $this->objectNumber++;
        }

        // Page objects
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
        $pdf .= "<<\n/Type /Catalog\n/Pages {$pagesObjNum} 0 R\n";
        if ($outlinesObjNum !== null) {
            $pdf .= "/Outlines {$outlinesObjNum} 0 R\n";
            $pdf .= "/PageMode /UseOutlines\n";
        }
        $pdf .= ">>\n";
        $pdf .= "endobj\n";

        // Write Pages
        $kidsStr = implode(' ', array_map(fn($n) => "{$n} 0 R", $pageObjNums));
        $offsets[$pagesObjNum] = strlen($pdf);
        $pdf .= "{$pagesObjNum} 0 obj\n";
        $pdf .= "<<\n/Type /Pages\n/Kids [{$kidsStr}]\n/Count " . count($pageObjNums) . "\n>>\n";
        $pdf .= "endobj\n";

        // Write Outlines (if any)
        if ($outlinesObjNum !== null && !empty($bookmarkObjNums)) {
            $currentOffset = strlen($pdf);
            $pdf .= $this->buildOutlinesObject($outlinesObjNum, $bookmarkObjNums, $pageObjNums, $offsets, $currentOffset);
        }

        // Write font
        $offsets[$fontObjNum] = strlen($pdf);
        $pdf .= "{$fontObjNum} 0 obj\n";
        $pdf .= "<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n/Encoding /WinAnsiEncoding\n>>\n";
        $pdf .= "endobj\n";

        // Write ColorSpace if present
        if ($colorSpaceObjNum !== null && !empty($colorSpaceContent)) {
            $offsets[$colorSpaceObjNum] = strlen($pdf);
            $pdf .= "{$colorSpaceObjNum} 0 obj\n";
            $pdf .= $colorSpaceContent . "\n";
            $pdf .= "endobj\n";
        }

        // Write pages and content
        foreach ($pages as $index => $page) {
            $pageObjNum = $pageObjNums[$index];
            $contentObjNum = $contentObjNums[$pageObjNum];

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
            if ($colorSpaceObjNum !== null) {
                $pdf .= "  /ColorSpace << /CS1 {$colorSpaceObjNum} 0 R >>\n";
            }
            $pdf .= ">>\n";
            $pdf .= "/Contents {$contentObjNum} 0 R\n";
            $pdf .= ">>\n";
            $pdf .= "endobj\n";

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

        // Write xref
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
     * Build outlines object and its entries
     *
     * @param int $outlinesObjNum Outlines root object number
     * @param array<int, int> $bookmarkObjNums Bookmark object numbers
     * @param array<int> $pageObjNums Page object numbers
     * @param array<int, int> &$offsets Offsets array (modified)
     * @param int $currentOffset Current offset in the main PDF
     * @return string Outlines objects
     */
    protected function buildOutlinesObject(
        int $outlinesObjNum,
        array $bookmarkObjNums,
        array $pageObjNums,
        array &$offsets,
        int $currentOffset
    ): string {
        $pdf = '';

        // Build tree structure based on levels
        $tree = $this->buildBookmarkTree();
        $firstObjNum = $bookmarkObjNums[0] ?? null;
        $lastObjNum = end($bookmarkObjNums) ?: null;

        // Write Outlines root - set offset BEFORE adding content
        $offsets[$outlinesObjNum] = $currentOffset + strlen($pdf);
        $outlinesContent = "{$outlinesObjNum} 0 obj\n";
        $outlinesContent .= "<<\n/Type /Outlines\n";
        if ($firstObjNum !== null) {
            $outlinesContent .= "/First {$firstObjNum} 0 R\n";
            $outlinesContent .= "/Last {$lastObjNum} 0 R\n";
        }
        $outlinesContent .= "/Count " . count($this->bookmarks) . "\n";
        $outlinesContent .= ">>\n";
        $outlinesContent .= "endobj\n";

        $pdf .= $outlinesContent;

        // Write bookmark entries
        foreach ($this->bookmarks as $index => $bookmark) {
            $objNum = $bookmarkObjNums[$index];
            $pageIdx = min($bookmark['page'] - 1, count($pageObjNums) - 1);
            $pageIdx = max(0, $pageIdx);
            $pageRef = $pageObjNums[$pageIdx];

            // Find prev/next at same level
            $prevObjNum = $this->findPrevSibling($index, $bookmarkObjNums);
            $nextObjNum = $this->findNextSibling($index, $bookmarkObjNums);

            // Set offset BEFORE adding content
            $offsets[$objNum] = $currentOffset + strlen($pdf);
            $pdf .= "{$objNum} 0 obj\n";
            $pdf .= "<<\n";
            $pdf .= "/Title " . $this->encodePdfString($bookmark['title']) . "\n";
            $pdf .= "/Parent {$outlinesObjNum} 0 R\n";

            if ($prevObjNum !== null) {
                $pdf .= "/Prev {$prevObjNum} 0 R\n";
            }
            if ($nextObjNum !== null) {
                $pdf .= "/Next {$nextObjNum} 0 R\n";
            }

            // Destination
            $y = $bookmark['y'] ?? 800;
            $pdf .= "/Dest [{$pageRef} 0 R /XYZ 0 {$y} 0]\n";

            $pdf .= ">>\n";
            $pdf .= "endobj\n";
        }

        return $pdf;
    }

    /**
     * Find previous sibling bookmark
     *
     * @param int $index Current bookmark index
     * @param array<int, int> $bookmarkObjNums Object numbers
     * @return int|null Previous object number
     */
    protected function findPrevSibling(int $index, array $bookmarkObjNums): ?int
    {
        if ($index === 0) {
            return null;
        }

        $currentLevel = $this->bookmarks[$index]['level'];

        for ($i = $index - 1; $i >= 0; $i--) {
            if ($this->bookmarks[$i]['level'] === $currentLevel) {
                return $bookmarkObjNums[$i];
            }
            if ($this->bookmarks[$i]['level'] < $currentLevel) {
                break;
            }
        }

        return null;
    }

    /**
     * Find next sibling bookmark
     *
     * @param int $index Current bookmark index
     * @param array<int, int> $bookmarkObjNums Object numbers
     * @return int|null Next object number
     */
    protected function findNextSibling(int $index, array $bookmarkObjNums): ?int
    {
        $count = count($this->bookmarks);
        if ($index >= $count - 1) {
            return null;
        }

        $currentLevel = $this->bookmarks[$index]['level'];

        for ($i = $index + 1; $i < $count; $i++) {
            if ($this->bookmarks[$i]['level'] === $currentLevel) {
                return $bookmarkObjNums[$i];
            }
            if ($this->bookmarks[$i]['level'] < $currentLevel) {
                break;
            }
        }

        return null;
    }

    /**
     * Build bookmark tree structure
     *
     * @return array<array{index: int, children: array<int>}>
     */
    protected function buildBookmarkTree(): array
    {
        $tree = [];
        foreach ($this->bookmarks as $index => $bookmark) {
            $tree[$index] = ['index' => $index, 'children' => []];
        }
        return $tree;
    }

    /**
     * Encode string for PDF
     *
     * @param string $str String to encode
     * @return string PDF string literal
     */
    protected function encodePdfString(string $str): string
    {
        $str = str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '\\r', '\\n'], $str);
        return '(' . $str . ')';
    }

    /**
     * Parse pages from PDF
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
     * Extract page data
     *
     * @param string $pageContent Page object content
     * @param int $pageObjNum Page object number
     * @return array{mediaBox: array<float>, contentStream: string}|null
     */
    protected function extractPageData(string $pageContent, int $pageObjNum): ?array
    {
        $mediaBox = [0, 0, 612, 792];
        if (preg_match('/\/MediaBox\s*\[\s*([\d.-]+)\s+([\d.-]+)\s+([\d.-]+)\s+([\d.-]+)\s*\]/', $pageContent, $mbMatch)) {
            $mediaBox = [
                (float)$mbMatch[1],
                (float)$mbMatch[2],
                (float)$mbMatch[3],
                (float)$mbMatch[4],
            ];
        }

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
     * Extract stream content
     *
     * @param string $objContent Object content
     * @param int $objNum Object number
     * @return string Decoded stream
     */
    protected function extractStreamContent(string $objContent, int $objNum): string
    {
        if (preg_match('/stream\s*(.*?)\s*endstream/s', $objContent, $match)) {
            return $this->decodeStream($objContent, $match[1]);
        }

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
