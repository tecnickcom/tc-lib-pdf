<?php

/**
 * PdfOptimizer.php
 *
 * PDF optimization class for compressing and reducing PDF file size.
 *
 * @category    Library
 * @package     PdfManipulate
 * @subpackage  Optimizer
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2024-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */

namespace Com\Tecnick\Pdf\Manipulate;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * PDF Optimizer class
 *
 * Provides functionality to optimize and compress PDF files.
 *
 * Example usage:
 * ```php
 * $optimizer = new PdfOptimizer();
 * $optimizer->loadFile('large.pdf')
 *           ->compressStreams(true)
 *           ->removeUnusedObjects(true);
 * echo "Original: " . $optimizer->getOriginalSize() . " bytes\n";
 * $optimizer->optimizeToFile('small.pdf');
 * echo "Optimized: " . $optimizer->getOptimizedSize() . " bytes\n";
 * ```
 *
 * @category    Library
 * @package     PdfManipulate
 */
class PdfOptimizer
{
    /**
     * Optimization level: Minimal
     */
    public const LEVEL_MINIMAL = 1;

    /**
     * Optimization level: Standard
     */
    public const LEVEL_STANDARD = 2;

    /**
     * Optimization level: Maximum
     */
    public const LEVEL_MAXIMUM = 3;

    /**
     * Original PDF content
     *
     * @var string
     */
    protected string $pdfContent = '';

    /**
     * Optimized PDF content
     *
     * @var string
     */
    protected string $optimizedContent = '';

    /**
     * PDF version
     *
     * @var string
     */
    protected string $pdfVersion = '1.7';

    /**
     * Object number counter
     *
     * @var int
     */
    protected int $objectNumber = 1;

    /**
     * Whether to compress streams
     *
     * @var bool
     */
    protected bool $shouldCompressStreams = true;

    /**
     * Whether to remove unused objects
     *
     * @var bool
     */
    protected bool $shouldRemoveUnused = true;

    /**
     * Whether to remove duplicate objects
     *
     * @var bool
     */
    protected bool $shouldRemoveDuplicates = true;

    /**
     * Compression level (1-9)
     *
     * @var int
     */
    protected int $compressionLevel = 9;

    /**
     * Parsed PDF objects
     *
     * @var array<int, array{content: string, stream?: string, used: bool, hash?: string}>
     */
    protected array $objects = [];

    /**
     * Object reference mapping (old -> new)
     *
     * @var array<int, int>
     */
    protected array $objectMapping = [];

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
        $this->optimizedContent = '';
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
     * Set optimization level
     *
     * @param int $level Optimization level constant
     * @return static
     */
    public function setOptimizationLevel(int $level): static
    {
        switch ($level) {
            case self::LEVEL_MINIMAL:
                $this->shouldCompressStreams = true;
                $this->shouldRemoveUnused = false;
                $this->shouldRemoveDuplicates = false;
                $this->compressionLevel = 6;
                break;

            case self::LEVEL_STANDARD:
                $this->shouldCompressStreams = true;
                $this->shouldRemoveUnused = true;
                $this->shouldRemoveDuplicates = false;
                $this->compressionLevel = 9;
                break;

            case self::LEVEL_MAXIMUM:
            default:
                $this->shouldCompressStreams = true;
                $this->shouldRemoveUnused = true;
                $this->shouldRemoveDuplicates = true;
                $this->compressionLevel = 9;
                break;
        }

        return $this;
    }

    /**
     * Enable or disable stream compression
     *
     * @param bool $compress Whether to compress
     * @return static
     */
    public function compressStreams(bool $compress = true): static
    {
        $this->shouldCompressStreams = $compress;
        return $this;
    }

    /**
     * Enable or disable removal of unused objects
     *
     * @param bool $remove Whether to remove
     * @return static
     */
    public function removeUnusedObjects(bool $remove = true): static
    {
        $this->shouldRemoveUnused = $remove;
        return $this;
    }

    /**
     * Enable or disable removal of duplicate objects
     *
     * @param bool $remove Whether to remove
     * @return static
     */
    public function removeDuplicateObjects(bool $remove = true): static
    {
        $this->shouldRemoveDuplicates = $remove;
        return $this;
    }

    /**
     * Set compression level (1-9)
     *
     * @param int $level Compression level
     * @return static
     */
    public function setCompressionLevel(int $level): static
    {
        $this->compressionLevel = max(1, min(9, $level));
        return $this;
    }

    /**
     * Get original file size in bytes
     *
     * @return int Size in bytes
     */
    public function getOriginalSize(): int
    {
        return strlen($this->pdfContent);
    }

    /**
     * Get optimized file size in bytes
     *
     * @return int Size in bytes
     */
    public function getOptimizedSize(): int
    {
        if (empty($this->optimizedContent)) {
            $this->optimizedContent = $this->optimize();
        }
        return strlen($this->optimizedContent);
    }

    /**
     * Get compression ratio (percentage saved)
     *
     * @return float Percentage saved (0-100)
     */
    public function getCompressionRatio(): float
    {
        $original = $this->getOriginalSize();
        $optimized = $this->getOptimizedSize();

        if ($original === 0) {
            return 0.0;
        }

        return round((1 - ($optimized / $original)) * 100, 2);
    }

    /**
     * Optimize the PDF and return content
     *
     * @return string Optimized PDF content
     * @throws PdfException If no PDF loaded
     */
    public function optimize(): string
    {
        if (empty($this->pdfContent)) {
            throw new PdfException('No PDF loaded');
        }

        $this->parseAllObjects();

        if ($this->shouldRemoveUnused) {
            $this->markUsedObjects();
        }

        if ($this->shouldRemoveDuplicates) {
            $this->findDuplicateObjects();
        }

        $this->optimizedContent = $this->rebuildOptimizedPdf();

        return $this->optimizedContent;
    }

    /**
     * Optimize the PDF and save to file
     *
     * @param string $outputPath Output file path
     * @return bool True on success
     * @throws PdfException If operation fails
     */
    public function optimizeToFile(string $outputPath): bool
    {
        $content = $this->optimize();
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
        $this->objectMapping = [];

        // Match objects with potential streams
        preg_match_all(
            '/(\d+)\s+\d+\s+obj\s*(.*?)endobj/s',
            $this->pdfContent,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $objNum = (int) $match[1];
            $objContent = $match[2];

            $stream = null;
            $dictContent = $objContent;

            if (preg_match('/<<(.*)>>\s*stream\s*(.*?)\s*endstream/s', $objContent, $streamMatch)) {
                $stream = $streamMatch[2];
                $dictContent = '<<' . $streamMatch[1] . '>>';
            }

            $this->objects[$objNum] = [
                'content' => trim($dictContent),
                'stream' => $stream,
                'used' => true, // Assume used until proven otherwise
            ];
        }
    }

    /**
     * Mark objects that are actually used
     *
     * @return void
     */
    protected function markUsedObjects(): void
    {
        // First, mark all as unused
        foreach ($this->objects as $num => &$obj) {
            $obj['used'] = false;
        }

        // Find root object
        $rootNum = null;
        if (preg_match('/\/Root\s+(\d+)\s+\d+\s+R/', $this->pdfContent, $matches)) {
            $rootNum = (int) $matches[1];
        }

        // Find info object
        $infoNum = null;
        if (preg_match('/\/Info\s+(\d+)\s+\d+\s+R/', $this->pdfContent, $matches)) {
            $infoNum = (int) $matches[1];
        }

        // Mark root and info as used
        if ($rootNum !== null) {
            $this->markObjectUsed($rootNum);
        }
        if ($infoNum !== null) {
            $this->markObjectUsed($infoNum);
        }
    }

    /**
     * Recursively mark an object and its references as used
     *
     * @param int $objNum Object number
     * @return void
     */
    protected function markObjectUsed(int $objNum): void
    {
        if (!isset($this->objects[$objNum]) || $this->objects[$objNum]['used']) {
            return;
        }

        $this->objects[$objNum]['used'] = true;

        // Find all references in this object
        $content = $this->objects[$objNum]['content'];
        preg_match_all('/(\d+)\s+\d+\s+R/', $content, $matches);

        foreach ($matches[1] as $refNum) {
            $this->markObjectUsed((int) $refNum);
        }
    }

    /**
     * Find and mark duplicate objects
     *
     * @return void
     */
    protected function findDuplicateObjects(): void
    {
        $hashes = [];

        foreach ($this->objects as $num => &$obj) {
            if (!$obj['used']) {
                continue;
            }

            // Create hash of object content
            $hashContent = $obj['content'];
            if (isset($obj['stream'])) {
                $hashContent .= $obj['stream'];
            }
            $hash = md5($hashContent);
            $obj['hash'] = $hash;

            if (isset($hashes[$hash])) {
                // This is a duplicate - map it to the original
                $this->objectMapping[$num] = $hashes[$hash];
                $obj['used'] = false; // Mark as unused (will use the original)
            } else {
                $hashes[$hash] = $num;
            }
        }
    }

    /**
     * Rebuild PDF with optimizations applied
     *
     * @return string Optimized PDF content
     */
    protected function rebuildOptimizedPdf(): string
    {
        $this->objectNumber = 1;
        $newObjects = [];
        $newObjNums = [];

        // Build new object numbers for used objects
        foreach ($this->objects as $oldNum => $obj) {
            if (!$obj['used']) {
                continue;
            }

            $newObjNums[$oldNum] = $this->objectNumber++;
        }

        // Add mappings for duplicates
        foreach ($this->objectMapping as $oldNum => $targetNum) {
            if (isset($newObjNums[$targetNum])) {
                $newObjNums[$oldNum] = $newObjNums[$targetNum];
            }
        }

        // Build PDF
        $pdf = "%PDF-{$this->pdfVersion}\n";
        $pdf .= "%\xe2\xe3\xcf\xd3\n";

        $offsets = [];

        foreach ($this->objects as $oldNum => $obj) {
            if (!$obj['used']) {
                continue;
            }

            $newNum = $newObjNums[$oldNum];
            $content = $this->updateReferences($obj['content'], $newObjNums);

            $offsets[$newNum] = strlen($pdf);
            $pdf .= "{$newNum} 0 obj\n";

            if (isset($obj['stream'])) {
                $stream = $obj['stream'];

                // Compress stream if needed
                if ($this->shouldCompressStreams) {
                    $stream = $this->optimizeStream($content, $stream);
                    // Update length in content
                    $streamLen = strlen($stream);
                    $content = preg_replace('/\/Length\s+\d+/', '/Length ' . $streamLen, $content);
                    // Ensure FlateDecode filter
                    if (!str_contains($content, '/Filter')) {
                        $content = str_replace('>>', '/Filter /FlateDecode >>', $content);
                    }
                }

                $pdf .= $content . "\nstream\n" . $stream . "\nendstream\n";
            } else {
                $pdf .= $content . "\n";
            }

            $pdf .= "endobj\n";
        }

        // Build cross-reference table
        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= "0 {$this->objectNumber}\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i < $this->objectNumber; $i++) {
            $offset = $offsets[$i] ?? 0;
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        // Find root object number
        $rootNum = 1;
        foreach ($this->objects as $oldNum => $obj) {
            if ($obj['used'] && str_contains($obj['content'], '/Type /Catalog')) {
                $rootNum = $newObjNums[$oldNum];
                break;
            }
        }

        // Trailer
        $pdf .= "trailer\n";
        $pdf .= "<<\n";
        $pdf .= "/Size {$this->objectNumber}\n";
        $pdf .= "/Root {$rootNum} 0 R\n";
        $pdf .= ">>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    /**
     * Update object references in content
     *
     * @param string $content Content with references
     * @param array<int, int> $mapping Old to new number mapping
     * @return string Updated content
     */
    protected function updateReferences(string $content, array $mapping): string
    {
        return preg_replace_callback(
            '/(\d+)\s+(\d+)\s+R/',
            function ($matches) use ($mapping) {
                $oldNum = (int) $matches[1];
                $gen = $matches[2];
                $newNum = $mapping[$oldNum] ?? $oldNum;
                return "{$newNum} {$gen} R";
            },
            $content
        );
    }

    /**
     * Optimize a stream (compress if not already)
     *
     * @param string $dictContent Dictionary content
     * @param string $stream Stream content
     * @return string Optimized stream
     */
    protected function optimizeStream(string $dictContent, string $stream): string
    {
        // Check if already compressed
        if (str_contains($dictContent, '/FlateDecode')) {
            // Already compressed, try to recompress with higher level
            $decompressed = @gzuncompress($stream);
            if ($decompressed !== false) {
                $recompressed = gzcompress($decompressed, $this->compressionLevel);
                if (strlen($recompressed) < strlen($stream)) {
                    return $recompressed;
                }
            }
            return $stream;
        }

        // Not compressed - compress it
        $compressed = gzcompress($stream, $this->compressionLevel);
        if ($compressed !== false && strlen($compressed) < strlen($stream)) {
            return $compressed;
        }

        return $stream;
    }

    /**
     * Get optimization statistics
     *
     * @return array{
     *     originalSize: int,
     *     optimizedSize: int,
     *     compressionRatio: float,
     *     objectsRemoved: int,
     *     duplicatesRemoved: int
     * }
     */
    public function getStatistics(): array
    {
        $unusedCount = 0;
        $duplicateCount = count($this->objectMapping);

        foreach ($this->objects as $obj) {
            if (!$obj['used']) {
                $unusedCount++;
            }
        }

        return [
            'originalSize' => $this->getOriginalSize(),
            'optimizedSize' => $this->getOptimizedSize(),
            'compressionRatio' => $this->getCompressionRatio(),
            'objectsRemoved' => $unusedCount - $duplicateCount,
            'duplicatesRemoved' => $duplicateCount,
        ];
    }

    /**
     * Get human-readable file size
     *
     * @param int $bytes Size in bytes
     * @return string Formatted size
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $size = (float) $bytes;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
}
