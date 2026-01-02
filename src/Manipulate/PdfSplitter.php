<?php

/**
 * PdfSplitter.php
 *
 * PDF Split functionality - extracts pages from a PDF document.
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
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
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type PageData array{
 *     'mediaBox': array<float>,
 *     'dict': string,
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
     * PDF version from source
     */
    protected string $pdfVersion = '1.7';

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
            $filename = sprintf('%s%s%03d.pdf', rtrim($outputDir, '/'), '/', (int) $i);
            $filename = str_replace('//', '/', $prefix . sprintf('%03d.pdf', $i));
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
     * Parse all pages from the PDF
     */
    protected function parsePages(): void
    {
        $this->pages = [];

        // Find all page objects (not Pages, just Page)
        if (preg_match_all('/(\d+)\s+0\s+obj\s*<<([^>]*\/Type\s*\/Page\b[^>]*)>>/s', $this->pdfContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // Skip if this is actually a /Pages object
                if (strpos($match[2], '/Type /Pages') !== false) {
                    continue;
                }

                $pageDict = $match[2];

                // Extract MediaBox
                $mediaBox = [0, 0, 612, 792]; // Default US Letter
                if (preg_match('/\/MediaBox\s*\[\s*([\d.-]+)\s+([\d.-]+)\s+([\d.-]+)\s+([\d.-]+)\s*\]/', $pageDict, $mbMatch)) {
                    $mediaBox = [
                        (float) $mbMatch[1],
                        (float) $mbMatch[2],
                        (float) $mbMatch[3],
                        (float) $mbMatch[4],
                    ];
                }

                $this->pages[] = [
                    'mediaBox' => $mediaBox,
                    'dict' => $pageDict,
                    'objNum' => (int) $match[1],
                ];
            }
        }
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
            return range(1, $totalPages);
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
                $start = max(1, (int) $start);
                $end = min($totalPages, (int) $end);
                for ($i = $start; $i <= $end; $i++) {
                    $pages[] = $i;
                }
            } else {
                $pageNum = (int) $part;
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
        $pdf = "%PDF-{$this->pdfVersion}\n";
        $pdf .= "%\xe2\xe3\xcf\xd3\n"; // Binary marker

        $objNum = 1;
        $offsets = [];
        $pageRefs = [];

        // Create page objects
        foreach ($pageNumbers as $pageNum) {
            if (!isset($this->pages[$pageNum - 1])) {
                continue;
            }

            $page = $this->pages[$pageNum - 1];
            $pageObjNum = ++$objNum;

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
            $pdf .= "/Parent 1 0 R\n";
            $pdf .= "/MediaBox {$mediaBox}\n";
            $pdf .= "/Resources << /ProcSet [/PDF /Text /ImageB /ImageC /ImageI] >>\n";
            $pdf .= ">>\n";
            $pdf .= "endobj\n";

            $pageRefs[] = "{$pageObjNum} 0 R";
        }

        // Pages object (object 1)
        $pagesObj = "1 0 obj\n";
        $pagesObj .= "<<\n";
        $pagesObj .= "/Type /Pages\n";
        $pagesObj .= "/Kids [" . implode(' ', $pageRefs) . "]\n";
        $pagesObj .= "/Count " . count($pageRefs) . "\n";
        $pagesObj .= ">>\n";
        $pagesObj .= "endobj\n";

        // Catalog object
        $catalogObjNum = ++$objNum;

        // Insert Pages object at the beginning (after header)
        $headerLen = strpos($pdf, '%', 5) + 5; // After binary marker line
        $headerLen = strlen("%PDF-{$this->pdfVersion}\n%\xe2\xe3\xcf\xd3\n");

        // Rebuild PDF with Pages object first
        $newPdf = "%PDF-{$this->pdfVersion}\n";
        $newPdf .= "%\xe2\xe3\xcf\xd3\n";

        // Add Pages object
        $offsets[1] = strlen($newPdf);
        $newPdf .= $pagesObj;

        // Re-add page objects with correct offsets
        $pageRefs = [];
        $pageObjNum = 1;
        foreach ($pageNumbers as $pageNum) {
            if (!isset($this->pages[$pageNum - 1])) {
                continue;
            }

            $page = $this->pages[$pageNum - 1];
            $pageObjNum = count($pageRefs) + 2;

            $mediaBox = sprintf(
                '[%.4f %.4f %.4f %.4f]',
                $page['mediaBox'][0],
                $page['mediaBox'][1],
                $page['mediaBox'][2],
                $page['mediaBox'][3]
            );

            $offsets[$pageObjNum] = strlen($newPdf);
            $newPdf .= "{$pageObjNum} 0 obj\n";
            $newPdf .= "<<\n";
            $newPdf .= "/Type /Page\n";
            $newPdf .= "/Parent 1 0 R\n";
            $newPdf .= "/MediaBox {$mediaBox}\n";
            $newPdf .= "/Resources << /ProcSet [/PDF /Text /ImageB /ImageC /ImageI] >>\n";
            $newPdf .= ">>\n";
            $newPdf .= "endobj\n";

            $pageRefs[] = "{$pageObjNum} 0 R";
        }

        // Update Pages object with correct refs
        $pagesContent = "1 0 obj\n";
        $pagesContent .= "<<\n";
        $pagesContent .= "/Type /Pages\n";
        $pagesContent .= "/Kids [" . implode(' ', $pageRefs) . "]\n";
        $pagesContent .= "/Count " . count($pageRefs) . "\n";
        $pagesContent .= ">>\n";
        $pagesContent .= "endobj\n";

        // Rebuild again with correct structure
        $finalPdf = "%PDF-{$this->pdfVersion}\n";
        $finalPdf .= "%\xe2\xe3\xcf\xd3\n";

        $offsets = [];
        $offsets[1] = strlen($finalPdf);
        $finalPdf .= $pagesContent;

        // Add page objects
        $nextObj = 2;
        foreach ($pageNumbers as $pageNum) {
            if (!isset($this->pages[$pageNum - 1])) {
                continue;
            }

            $page = $this->pages[$pageNum - 1];

            $mediaBox = sprintf(
                '[%.4f %.4f %.4f %.4f]',
                $page['mediaBox'][0],
                $page['mediaBox'][1],
                $page['mediaBox'][2],
                $page['mediaBox'][3]
            );

            $offsets[$nextObj] = strlen($finalPdf);
            $finalPdf .= "{$nextObj} 0 obj\n";
            $finalPdf .= "<<\n";
            $finalPdf .= "/Type /Page\n";
            $finalPdf .= "/Parent 1 0 R\n";
            $finalPdf .= "/MediaBox {$mediaBox}\n";
            $finalPdf .= "/Resources << /ProcSet [/PDF /Text /ImageB /ImageC /ImageI] >>\n";
            $finalPdf .= ">>\n";
            $finalPdf .= "endobj\n";

            $nextObj++;
        }

        // Add Catalog
        $catalogNum = $nextObj;
        $offsets[$catalogNum] = strlen($finalPdf);
        $finalPdf .= "{$catalogNum} 0 obj\n";
        $finalPdf .= "<<\n";
        $finalPdf .= "/Type /Catalog\n";
        $finalPdf .= "/Pages 1 0 R\n";
        $finalPdf .= ">>\n";
        $finalPdf .= "endobj\n";

        // Write xref
        $xrefOffset = strlen($finalPdf);
        $objCount = $catalogNum + 1;

        $finalPdf .= "xref\n";
        $finalPdf .= "0 {$objCount}\n";
        $finalPdf .= "0000000000 65535 f \n";

        for ($i = 1; $i < $objCount; $i++) {
            if (isset($offsets[$i])) {
                $finalPdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
            } else {
                $finalPdf .= "0000000000 00000 f \n";
            }
        }

        // Trailer
        $finalPdf .= "trailer\n";
        $finalPdf .= "<<\n";
        $finalPdf .= "/Size {$objCount}\n";
        $finalPdf .= "/Root {$catalogNum} 0 R\n";
        $finalPdf .= ">>\n";
        $finalPdf .= "startxref\n";
        $finalPdf .= "{$xrefOffset}\n";
        $finalPdf .= "%%EOF\n";

        return $finalPdf;
    }
}
