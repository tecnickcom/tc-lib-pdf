<?php

/**
 * PdfLinearizer.php
 *
 * PDF Linearization class for fast web view optimization.
 *
 * @category    Library
 * @package     PdfManipulate
 * @subpackage  Linearizer
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2024-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */

namespace Com\Tecnick\Pdf\Manipulate;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * PDF Linearization class
 *
 * Provides functionality to linearize PDFs for "fast web view".
 * Linearized PDFs can display the first page before the entire
 * document has been downloaded.
 *
 * Example usage:
 * ```php
 * $linearizer = new PdfLinearizer();
 * $linearizer->loadFile('document.pdf');
 * if (!$linearizer->isLinearized()) {
 *     $linearizer->linearizeToFile('document_linearized.pdf');
 * }
 * ```
 *
 * @category    Library
 * @package     PdfManipulate
 */
class PdfLinearizer
{
    /**
     * Original PDF content
     *
     * @var string
     */
    protected string $content = '';

    /**
     * PDF file path
     *
     * @var string
     */
    protected string $filePath = '';

    /**
     * PDF version
     *
     * @var string
     */
    protected string $pdfVersion = '1.7';

    /**
     * Parsed objects from original PDF
     *
     * @var array<int, array{offset: int, gen: int, content: string}>
     */
    protected array $objects = [];

    /**
     * Root object number
     *
     * @var int
     */
    protected int $rootObjNum = 0;

    /**
     * Info object number
     *
     * @var int
     */
    protected int $infoObjNum = 0;

    /**
     * Page objects
     *
     * @var array<int, int>
     */
    protected array $pageObjects = [];

    /**
     * Page count
     *
     * @var int
     */
    protected int $pageCount = 0;

    /**
     * Linearization statistics
     *
     * @var array{originalSize: int, linearizedSize: int, firstPageOffset: int}
     */
    protected array $stats = [
        'originalSize' => 0,
        'linearizedSize' => 0,
        'firstPageOffset' => 0,
    ];

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

        $this->filePath = $filePath;
        $this->content = $content;
        $this->stats['originalSize'] = strlen($content);

        $this->parseVersion();
        $this->parseObjects();
        $this->parseTrailer();
        $this->parsePages();

        return $this;
    }

    /**
     * Load PDF from content
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

        $this->content = $content;
        $this->filePath = '';
        $this->stats['originalSize'] = strlen($content);

        $this->parseVersion();
        $this->parseObjects();
        $this->parseTrailer();
        $this->parsePages();

        return $this;
    }

    /**
     * Parse PDF version
     *
     * @return void
     */
    protected function parseVersion(): void
    {
        if (preg_match('/%PDF-(\d+\.\d+)/', $this->content, $matches)) {
            $this->pdfVersion = $matches[1];
        }
    }

    /**
     * Parse PDF objects
     *
     * @return void
     */
    protected function parseObjects(): void
    {
        $this->objects = [];

        // Find all object definitions
        if (preg_match_all('/(\d+)\s+(\d+)\s+obj\b/s', $this->content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $idx => $match) {
                $objNum = (int) $matches[1][$idx][0];
                $genNum = (int) $matches[2][$idx][0];
                $offset = (int) $match[1];

                // Find the endobj
                $start = $offset + strlen($match[0]);
                $endPos = strpos($this->content, 'endobj', $start);

                if ($endPos !== false) {
                    $objContent = substr($this->content, $offset, $endPos - $offset + 6);
                    $this->objects[$objNum] = [
                        'offset' => $offset,
                        'gen' => $genNum,
                        'content' => $objContent,
                    ];
                }
            }
        }
    }

    /**
     * Parse trailer to find root and info
     *
     * @return void
     */
    protected function parseTrailer(): void
    {
        // Find Root reference
        if (preg_match('/\/Root\s+(\d+)\s+\d+\s+R/', $this->content, $matches)) {
            $this->rootObjNum = (int) $matches[1];
        }

        // Find Info reference
        if (preg_match('/\/Info\s+(\d+)\s+\d+\s+R/', $this->content, $matches)) {
            $this->infoObjNum = (int) $matches[1];
        }
    }

    /**
     * Parse page structure
     *
     * @return void
     */
    protected function parsePages(): void
    {
        $this->pageObjects = [];
        $this->pageCount = 0;

        // Find Pages object from Root
        if ($this->rootObjNum > 0 && isset($this->objects[$this->rootObjNum])) {
            $rootContent = $this->objects[$this->rootObjNum]['content'];
            if (preg_match('/\/Pages\s+(\d+)\s+\d+\s+R/', $rootContent, $matches)) {
                $pagesObjNum = (int) $matches[1];
                $this->extractPageObjects($pagesObjNum);
            }
        }
    }

    /**
     * Extract page objects recursively
     *
     * @param int $objNum Object number to process
     * @return void
     */
    protected function extractPageObjects(int $objNum): void
    {
        if (!isset($this->objects[$objNum])) {
            return;
        }

        $content = $this->objects[$objNum]['content'];

        // Check if this is a Pages node (has /Kids)
        if (preg_match('/\/Kids\s*\[(.*?)\]/s', $content, $kidsMatch)) {
            // Extract child references
            if (preg_match_all('/(\d+)\s+\d+\s+R/', $kidsMatch[1], $childMatches)) {
                foreach ($childMatches[1] as $childNum) {
                    $this->extractPageObjects((int) $childNum);
                }
            }
        }

        // Check if this is a Page (has /Type /Page)
        if (preg_match('/\/Type\s*\/Page\b/', $content)) {
            $this->pageCount++;
            $this->pageObjects[$this->pageCount] = $objNum;
        }
    }

    /**
     * Check if PDF is already linearized
     *
     * @return bool True if linearized
     */
    public function isLinearized(): bool
    {
        // Check for linearization dictionary at the start
        // A linearized PDF has a linearization dictionary as the first object
        // containing /Linearized key

        // Look for linearization dictionary in first 1024 bytes
        $header = substr($this->content, 0, 1024);

        return str_contains($header, '/Linearized');
    }

    /**
     * Get page count
     *
     * @return int Number of pages
     */
    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    /**
     * Get original file size
     *
     * @return int Size in bytes
     */
    public function getOriginalSize(): int
    {
        return $this->stats['originalSize'];
    }

    /**
     * Get linearized file size
     *
     * @return int Size in bytes
     */
    public function getLinearizedSize(): int
    {
        return $this->stats['linearizedSize'];
    }

    /**
     * Get linearization statistics
     *
     * @return array{originalSize: int, linearizedSize: int, firstPageOffset: int}
     */
    public function getStatistics(): array
    {
        return $this->stats;
    }

    /**
     * Linearize the PDF
     *
     * @return string Linearized PDF content
     * @throws PdfException If PDF is not loaded
     */
    public function linearize(): string
    {
        if (empty($this->content)) {
            throw new PdfException('No PDF loaded');
        }

        if ($this->pageCount === 0) {
            throw new PdfException('PDF has no pages');
        }

        // Build linearized PDF structure
        $output = $this->buildLinearizedPdf();
        $this->stats['linearizedSize'] = strlen($output);

        return $output;
    }

    /**
     * Build linearized PDF structure
     *
     * Linearized PDF structure:
     * 1. Header
     * 2. Linearization dictionary (object 1)
     * 3. First page xref and trailer
     * 4. Document catalog, page tree (shared objects)
     * 5. First page content
     * 6. Hint stream
     * 7. Remaining pages
     * 8. Main xref and trailer
     *
     * @return string Linearized PDF
     */
    protected function buildLinearizedPdf(): string
    {
        $output = '';
        $xref = [];
        $offset = 0;

        // 1. PDF Header
        $header = "%PDF-{$this->pdfVersion}\n%\xE2\xE3\xCF\xD3\n";
        $output .= $header;
        $offset = strlen($header);

        // Collect all objects that need to be written
        $allObjects = [];
        $newObjNum = 1;
        $objMapping = []; // old obj num => new obj num

        // Build object order for linearization:
        // - First: linearization dict, catalog, page tree, first page
        // - Then: remaining pages and their resources
        // - Finally: remaining shared objects

        // Get first page object and its dependencies
        $firstPageObjNum = $this->pageObjects[1] ?? 0;
        $firstPageDeps = $this->getObjectDependencies($firstPageObjNum);

        // Catalog and page tree
        $catalogDeps = $this->getObjectDependencies($this->rootObjNum);

        // Priority objects (first page related)
        $priorityObjects = array_unique(array_merge(
            [$this->rootObjNum],
            $catalogDeps,
            [$firstPageObjNum],
            $firstPageDeps
        ));

        // All other objects
        $otherObjects = array_diff(array_keys($this->objects), $priorityObjects);

        // Build linearization dictionary placeholder (will update later)
        $linearizeDictObjNum = $newObjNum++;
        $objMapping[0] = $linearizeDictObjNum; // Special marker

        // Map old to new object numbers
        foreach ($priorityObjects as $oldNum) {
            if ($oldNum > 0 && isset($this->objects[$oldNum])) {
                $objMapping[$oldNum] = $newObjNum++;
            }
        }
        foreach ($otherObjects as $oldNum) {
            if (isset($this->objects[$oldNum])) {
                $objMapping[$oldNum] = $newObjNum++;
            }
        }

        // Track first page end offset for linearization dict
        $firstPageEndOffset = 0;

        // 2. Write linearization dictionary
        $xref[$linearizeDictObjNum] = $offset;
        $linearDict = "{$linearizeDictObjNum} 0 obj\n<<\n";
        $linearDict .= "/Linearized 1\n";
        $linearDict .= "/L %FILE_LENGTH%\n"; // Placeholder for file length
        $linearDict .= "/H [%HINT_OFFSET% %HINT_LENGTH%]\n"; // Hint stream location
        $linearDict .= "/O " . ($objMapping[$firstPageObjNum] ?? 2) . "\n"; // First page object
        $linearDict .= "/E %FIRST_PAGE_END%\n"; // End of first page
        $linearDict .= "/N {$this->pageCount}\n"; // Page count
        $linearDict .= "/T %XREF_OFFSET%\n"; // Main xref offset
        $linearDict .= ">>\nendobj\n";
        $output .= $linearDict;
        $offset += strlen($linearDict);

        // 3. Write priority objects (catalog, pages, first page)
        foreach ($priorityObjects as $oldNum) {
            if ($oldNum <= 0 || !isset($this->objects[$oldNum])) {
                continue;
            }

            $newNum = $objMapping[$oldNum];
            $xref[$newNum] = $offset;

            $objContent = $this->remapObjectReferences(
                $this->objects[$oldNum]['content'],
                $objMapping,
                $oldNum,
                $newNum
            );

            $output .= $objContent . "\n";
            $offset += strlen($objContent) + 1;

            // Mark end of first page section
            if ($oldNum === $firstPageObjNum) {
                $firstPageEndOffset = $offset;
            }
        }

        // If first page end not set, use current offset
        if ($firstPageEndOffset === 0) {
            $firstPageEndOffset = $offset;
        }

        // 4. Write remaining objects
        foreach ($otherObjects as $oldNum) {
            if (!isset($this->objects[$oldNum])) {
                continue;
            }

            $newNum = $objMapping[$oldNum];
            $xref[$newNum] = $offset;

            $objContent = $this->remapObjectReferences(
                $this->objects[$oldNum]['content'],
                $objMapping,
                $oldNum,
                $newNum
            );

            $output .= $objContent . "\n";
            $offset += strlen($objContent) + 1;
        }

        // 5. Write hint stream (simplified)
        $hintObjNum = $newObjNum++;
        $hintOffset = $offset;
        $xref[$hintObjNum] = $offset;

        // Simplified hint stream (page offset hints)
        $hintData = $this->buildHintStream($objMapping);
        $hintStream = "{$hintObjNum} 0 obj\n<<\n";
        $hintStream .= "/Filter /FlateDecode\n";
        $hintStream .= "/S " . strlen($hintData) . "\n";
        $hintStream .= "/Length " . strlen($hintData) . "\n";
        $hintStream .= ">>\nstream\n";
        $hintStream .= $hintData;
        $hintStream .= "\nendstream\nendobj\n";
        $output .= $hintStream;
        $offset += strlen($hintStream);
        $hintLength = strlen($hintStream);

        // 6. Write xref table
        $xrefOffset = $offset;
        $xrefTable = "xref\n";
        $xrefTable .= "0 " . ($hintObjNum + 1) . "\n";
        $xrefTable .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $hintObjNum; $i++) {
            $objOffset = $xref[$i] ?? 0;
            $xrefTable .= sprintf("%010d 00000 n \n", $objOffset);
        }

        $output .= $xrefTable;
        $offset += strlen($xrefTable);

        // 7. Write trailer
        $trailer = "trailer\n<<\n";
        $trailer .= "/Size " . ($hintObjNum + 1) . "\n";
        $trailer .= "/Root " . ($objMapping[$this->rootObjNum] ?? 1) . " 0 R\n";
        if ($this->infoObjNum > 0 && isset($objMapping[$this->infoObjNum])) {
            $trailer .= "/Info " . $objMapping[$this->infoObjNum] . " 0 R\n";
        }
        $trailer .= ">>\nstartxref\n{$xrefOffset}\n%%EOF\n";
        $output .= $trailer;

        // Update placeholders in linearization dictionary
        $fileLength = strlen($output);
        $output = str_replace('%FILE_LENGTH%', (string) $fileLength, $output);
        $output = str_replace('%HINT_OFFSET%', (string) $hintOffset, $output);
        $output = str_replace('%HINT_LENGTH%', (string) $hintLength, $output);
        $output = str_replace('%FIRST_PAGE_END%', (string) $firstPageEndOffset, $output);
        $output = str_replace('%XREF_OFFSET%', (string) $xrefOffset, $output);

        $this->stats['firstPageOffset'] = $firstPageEndOffset;

        return $output;
    }

    /**
     * Get object dependencies (objects referenced by this object)
     *
     * @param int $objNum Object number
     * @return array<int> Referenced object numbers
     */
    protected function getObjectDependencies(int $objNum): array
    {
        if (!isset($this->objects[$objNum])) {
            return [];
        }

        $deps = [];
        $content = $this->objects[$objNum]['content'];

        // Find all object references
        if (preg_match_all('/(\d+)\s+\d+\s+R/', $content, $matches)) {
            foreach ($matches[1] as $refNum) {
                $refNum = (int) $refNum;
                if ($refNum !== $objNum && isset($this->objects[$refNum])) {
                    $deps[] = $refNum;
                }
            }
        }

        return array_unique($deps);
    }

    /**
     * Remap object references to new numbers
     *
     * @param string $content Object content
     * @param array<int, int> $mapping Old to new object mapping
     * @param int $oldNum Original object number
     * @param int $newNum New object number
     * @return string Updated content
     */
    protected function remapObjectReferences(string $content, array $mapping, int $oldNum, int $newNum): string
    {
        // Update object header
        $content = preg_replace(
            '/^' . $oldNum . '\s+\d+\s+obj/',
            $newNum . ' 0 obj',
            $content
        );

        // Update all references
        $content = preg_replace_callback(
            '/(\d+)\s+(\d+)\s+R/',
            function ($matches) use ($mapping) {
                $refNum = (int) $matches[1];
                $newRef = $mapping[$refNum] ?? $refNum;
                return $newRef . ' 0 R';
            },
            $content
        );

        return $content;
    }

    /**
     * Build hint stream data
     *
     * @param array<int, int> $mapping Object mapping
     * @return string Hint stream data (compressed)
     */
    protected function buildHintStream(array $mapping): string
    {
        // Simplified hint stream
        // In a full implementation, this would contain:
        // - Page offset hint table
        // - Shared object hint table

        $hints = '';

        // Page offset hints (simplified)
        for ($i = 1; $i <= $this->pageCount; $i++) {
            $pageObjNum = $this->pageObjects[$i] ?? 0;
            $newNum = $mapping[$pageObjNum] ?? 0;
            // Pack as simple offset hints
            $hints .= pack('N', $newNum);
        }

        // Compress the hints
        $compressed = @gzcompress($hints, 9);
        return $compressed !== false ? $compressed : $hints;
    }

    /**
     * Linearize PDF and save to file
     *
     * @param string $filePath Output file path
     * @return bool True on success
     * @throws PdfException If linearization fails
     */
    public function linearizeToFile(string $filePath): bool
    {
        $content = $this->linearize();
        $result = file_put_contents($filePath, $content);
        return $result !== false;
    }

    /**
     * Get PDF version
     *
     * @return string PDF version (e.g., "1.7")
     */
    public function getVersion(): string
    {
        return $this->pdfVersion;
    }

    /**
     * Get object count
     *
     * @return int Number of objects
     */
    public function getObjectCount(): int
    {
        return count($this->objects);
    }
}
