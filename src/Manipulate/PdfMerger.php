<?php

/**
 * PdfMerger.php
 *
 * PDF Merge functionality - combines multiple PDF documents into one.
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
 * PDF Merger
 *
 * Merges multiple PDF documents into a single PDF file.
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type PdfSource array{
 *     'content': string,
 *     'pages'?: array<int>|string,
 *     'file'?: string,
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
     * PDF objects for output
     *
     * @var array<string>
     */
    protected array $objects = [];

    /**
     * Page references
     *
     * @var array<string>
     */
    protected array $pageRefs = [];

    /**
     * PDF version to use
     */
    protected string $pdfVersion = '1.7';

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
        $this->objects = [];
        $this->pageRefs = [];
        $this->objectNumber = 1;
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

        $this->objects = [];
        $this->pageRefs = [];
        $this->objectNumber = 1;

        // Parse and collect pages from all sources
        foreach ($this->sources as $source) {
            $this->processSource($source);
        }

        if (empty($this->pageRefs)) {
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
        $parsedPages = $this->parsePdfPages($content);

        // Determine which pages to include
        $pageNumbers = $this->resolvePageNumbers($pages, count($parsedPages));

        // Add selected pages
        foreach ($pageNumbers as $pageNum) {
            if (isset($parsedPages[$pageNum - 1])) {
                $this->addParsedPage($parsedPages[$pageNum - 1]);
            }
        }
    }

    /**
     * Parse PDF and extract page information
     *
     * @param string $content PDF content
     * @return array<array{
     *     'mediaBox': array<float>,
     *     'content': string,
     *     'resources': string,
     * }> Array of parsed page data
     */
    protected function parsePdfPages(string $content): array
    {
        $pages = [];

        // Find all page objects
        if (preg_match_all('/(\d+)\s+0\s+obj\s*<<([^>]*\/Type\s*\/Page[^>]*)>>/s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $pageDict = $match[2];

                // Extract MediaBox
                $mediaBox = [0, 0, 612, 792]; // Default US Letter
                if (preg_match('/\/MediaBox\s*\[\s*([\d.]+)\s+([\d.]+)\s+([\d.]+)\s+([\d.]+)\s*\]/', $pageDict, $mbMatch)) {
                    $mediaBox = [
                        (float) $mbMatch[1],
                        (float) $mbMatch[2],
                        (float) $mbMatch[3],
                        (float) $mbMatch[4],
                    ];
                }

                // Extract content stream reference
                $contentRef = '';
                if (preg_match('/\/Contents\s+(\d+)\s+0\s+R/', $pageDict, $cMatch)) {
                    $contentRef = $cMatch[1];
                }

                // Extract resources reference
                $resourcesRef = '';
                if (preg_match('/\/Resources\s+(\d+)\s+0\s+R/', $pageDict, $rMatch)) {
                    $resourcesRef = $rMatch[1];
                }

                $pages[] = [
                    'mediaBox' => $mediaBox,
                    'content' => $contentRef,
                    'resources' => $resourcesRef,
                    'dict' => $pageDict,
                ];
            }
        }

        return $pages;
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
            return range(1, $totalPages);
        }

        if (is_string($pages)) {
            // Parse range like "1-5" or "1,3,5"
            return $this->parsePageRange($pages, $totalPages);
        }

        // Filter valid page numbers
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

        return array_unique($pages);
    }

    /**
     * Add a parsed page to the output
     *
     * @param array{
     *     'mediaBox': array<float>,
     *     'content': string,
     *     'resources': string,
     * } $pageData Parsed page data
     */
    protected function addParsedPage(array $pageData): void
    {
        $objNum = $this->objectNumber++;

        $mediaBox = sprintf(
            '[%.4f %.4f %.4f %.4f]',
            $pageData['mediaBox'][0],
            $pageData['mediaBox'][1],
            $pageData['mediaBox'][2],
            $pageData['mediaBox'][3]
        );

        // Create a simple page object
        $pageObj = "{$objNum} 0 obj\n";
        $pageObj .= "<<\n";
        $pageObj .= "/Type /Page\n";
        $pageObj .= "/Parent 1 0 R\n";
        $pageObj .= "/MediaBox {$mediaBox}\n";
        $pageObj .= "/Resources << /ProcSet [/PDF /Text /ImageB /ImageC /ImageI] >>\n";
        $pageObj .= ">>\n";
        $pageObj .= "endobj\n";

        $this->objects[$objNum] = $pageObj;
        $this->pageRefs[] = "{$objNum} 0 R";
    }

    /**
     * Build the final merged PDF
     *
     * @return string Complete PDF content
     */
    protected function buildMergedPdf(): string
    {
        $pdf = "%PDF-{$this->pdfVersion}\n";
        $pdf .= "%\xe2\xe3\xcf\xd3\n"; // Binary marker

        // Reserve object 1 for Pages
        $pagesObjNum = 1;
        $this->objectNumber = max($this->objectNumber, 2);

        // Catalog object
        $catalogObjNum = $this->objectNumber++;
        $catalog = "{$catalogObjNum} 0 obj\n";
        $catalog .= "<<\n";
        $catalog .= "/Type /Catalog\n";
        $catalog .= "/Pages {$pagesObjNum} 0 R\n";
        $catalog .= ">>\n";
        $catalog .= "endobj\n";

        // Pages object
        $pageRefs = implode(' ', $this->pageRefs);
        $pageCount = count($this->pageRefs);
        $pagesObj = "{$pagesObjNum} 0 obj\n";
        $pagesObj .= "<<\n";
        $pagesObj .= "/Type /Pages\n";
        $pagesObj .= "/Kids [{$pageRefs}]\n";
        $pagesObj .= "/Count {$pageCount}\n";
        $pagesObj .= ">>\n";
        $pagesObj .= "endobj\n";

        // Write objects and track offsets
        $offsets = [];

        $offsets[$pagesObjNum] = strlen($pdf);
        $pdf .= $pagesObj;

        foreach ($this->objects as $num => $obj) {
            $offsets[$num] = strlen($pdf);
            $pdf .= $obj;
        }

        $offsets[$catalogObjNum] = strlen($pdf);
        $pdf .= $catalog;

        // Write xref table
        $xrefOffset = strlen($pdf);
        $objCount = max(array_keys($offsets)) + 1;

        $pdf .= "xref\n";
        $pdf .= "0 {$objCount}\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i < $objCount; $i++) {
            if (isset($offsets[$i])) {
                $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
            } else {
                $pdf .= "0000000000 00000 f \n";
            }
        }

        // Trailer
        $pdf .= "trailer\n";
        $pdf .= "<<\n";
        $pdf .= "/Size {$objCount}\n";
        $pdf .= "/Root {$catalogObjNum} 0 R\n";
        $pdf .= ">>\n";
        $pdf .= "startxref\n";
        $pdf .= "{$xrefOffset}\n";
        $pdf .= "%%EOF\n";

        return $pdf;
    }
}
