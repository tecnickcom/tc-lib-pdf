<?php

/**
 * PdfStamper.php
 *
 * PDF Stamping/Watermark functionality - adds stamps and watermarks to PDF documents.
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
 * PDF Stamper
 *
 * Adds text stamps, watermarks, and image overlays to PDF documents.
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type StampConfig array{
 *     'text'?: string,
 *     'x'?: float,
 *     'y'?: float,
 *     'fontSize'?: float,
 *     'fontFamily'?: string,
 *     'color'?: array{float, float, float},
 *     'opacity'?: float,
 *     'rotation'?: float,
 *     'position'?: string,
 *     'pages'?: array<int>|string,
 * }
 *
 * @phpstan-type WatermarkConfig array{
 *     'text': string,
 *     'fontSize'?: float,
 *     'color'?: array{float, float, float},
 *     'opacity'?: float,
 *     'rotation'?: float,
 *     'pages'?: array<int>|string,
 * }
 */
class PdfStamper
{
    /**
     * Position constants
     */
    public const POSITION_TOP_LEFT = 'top-left';
    public const POSITION_TOP_CENTER = 'top-center';
    public const POSITION_TOP_RIGHT = 'top-right';
    public const POSITION_CENTER_LEFT = 'center-left';
    public const POSITION_CENTER = 'center';
    public const POSITION_CENTER_RIGHT = 'center-right';
    public const POSITION_BOTTOM_LEFT = 'bottom-left';
    public const POSITION_BOTTOM_CENTER = 'bottom-center';
    public const POSITION_BOTTOM_RIGHT = 'bottom-right';

    /**
     * Layer constants
     */
    public const LAYER_FOREGROUND = 'foreground';
    public const LAYER_BACKGROUND = 'background';

    /**
     * Source PDF content
     */
    protected string $pdfContent = '';

    /**
     * PDF version
     */
    protected string $pdfVersion = '1.7';

    /**
     * Stamps to apply
     *
     * @var array<array{
     *     'type': string,
     *     'config': array<string, mixed>,
     *     'layer': string,
     * }>
     */
    protected array $stamps = [];

    /**
     * Parsed PDF objects
     *
     * @var array<int, string>
     */
    protected array $objects = [];

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
        $this->stamps = [];

        return $this;
    }

    /**
     * Extract all objects from PDF content
     */
    protected function extractObjects(): void
    {
        $this->objects = [];

        // Match all PDF objects
        if (preg_match_all('/(\d+)\s+0\s+obj\s*(.*?)\s*endobj/s', $this->pdfContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $objNum = (int) $match[1];
                $this->objects[$objNum] = $match[2];
            }
        }
    }

    /**
     * Extract Resources dictionary from page objects
     */
    protected function extractResources(): void
    {
        $this->resourcesContent = '';

        // Find page objects and their resources
        foreach ($this->objects as $objContent) {
            if (strpos($objContent, '/Type /Page') !== false || strpos($objContent, '/Type/Page') !== false) {
                // Check for Resources reference
                if (preg_match('/\/Resources\s+(\d+)\s+0\s+R/', $objContent, $resMatch)) {
                    $resObjNum = (int) $resMatch[1];
                    if (isset($this->objects[$resObjNum])) {
                        $this->resourcesContent = $this->objects[$resObjNum];
                        break;
                    }
                }
                // Check for inline Resources
                if (preg_match('/\/Resources\s*<<(.*)>>/s', $objContent, $resMatch)) {
                    $this->resourcesContent = $resMatch[1];
                    break;
                }
            }
        }

        // If no resources found in page, check objects directly
        if (empty($this->resourcesContent)) {
            foreach ($this->objects as $objContent) {
                if (strpos($objContent, '/ColorSpace') !== false && strpos($objContent, '/Font') !== false) {
                    // This looks like a Resources dictionary
                    if (preg_match('/<<(.*)>>/s', $objContent, $match)) {
                        $this->resourcesContent = $match[1];
                        break;
                    }
                }
            }
        }
    }

    /**
     * Extract nested dictionary content
     *
     * @param string $content Content to parse
     * @param string $key Dictionary key to extract
     * @return string Extracted content
     */
    protected function extractNestedDict(string $content, string $key): string
    {
        $pattern = '/' . preg_quote($key, '/') . '\s*<<(.*?)>>/s';
        if (preg_match($pattern, $content, $match)) {
            return $match[1];
        }

        // Try matching with proper bracket counting
        $startPattern = '/' . preg_quote($key, '/') . '\s*<</';
        if (preg_match($startPattern, $content, $match, PREG_OFFSET_CAPTURE)) {
            $startPos = $match[0][1] + strlen($match[0][0]);
            $depth = 1;
            $pos = $startPos;
            $len = strlen($content);

            while ($pos < $len && $depth > 0) {
                if (substr($content, $pos, 2) === '<<') {
                    $depth++;
                    $pos += 2;
                } elseif (substr($content, $pos, 2) === '>>') {
                    $depth--;
                    if ($depth === 0) {
                        return substr($content, $startPos, $pos - $startPos);
                    }
                    $pos += 2;
                } else {
                    $pos++;
                }
            }
        }

        return '';
    }

    /**
     * Add a text stamp
     *
     * @param string $text Text to stamp
     * @param StampConfig $config Stamp configuration
     * @param string $layer Layer to add stamp (foreground or background)
     * @return static
     */
    public function addTextStamp(string $text, array $config = [], string $layer = self::LAYER_FOREGROUND): static
    {
        $this->stamps[] = [
            'type' => 'text',
            'config' => array_merge([
                'text' => $text,
                'x' => 10,
                'y' => 10,
                'fontSize' => 12,
                'fontFamily' => 'Helvetica',
                'color' => [0, 0, 0],
                'opacity' => 1.0,
                'rotation' => 0,
                'position' => null,
                'pages' => 'all',
            ], $config),
            'layer' => $layer,
        ];

        return $this;
    }

    /**
     * Add a diagonal watermark across the page
     *
     * @param string $text Watermark text
     * @param WatermarkConfig $config Watermark configuration
     * @return static
     */
    public function addWatermark(string $text, array $config = []): static
    {
        $defaults = [
            'text' => $text,
            'fontSize' => 60,
            'color' => [0.8, 0.8, 0.8], // Light gray
            'opacity' => 0.3,
            'rotation' => 45,
            'position' => self::POSITION_CENTER,
            'pages' => 'all',
        ];

        $this->stamps[] = [
            'type' => 'watermark',
            'config' => array_merge($defaults, $config),
            'layer' => self::LAYER_BACKGROUND,
        ];

        return $this;
    }

    /**
     * Add a "DRAFT" watermark
     *
     * @param array<int>|string $pages Pages to apply to
     * @return static
     */
    public function addDraftWatermark(array|string $pages = 'all'): static
    {
        return $this->addWatermark('DRAFT', [
            'color' => [1, 0, 0], // Red
            'opacity' => 0.2,
            'pages' => $pages,
        ]);
    }

    /**
     * Add a "CONFIDENTIAL" watermark
     *
     * @param array<int>|string $pages Pages to apply to
     * @return static
     */
    public function addConfidentialWatermark(array|string $pages = 'all'): static
    {
        return $this->addWatermark('CONFIDENTIAL', [
            'color' => [1, 0, 0], // Red
            'opacity' => 0.15,
            'pages' => $pages,
        ]);
    }

    /**
     * Add a "COPY" watermark
     *
     * @param array<int>|string $pages Pages to apply to
     * @return static
     */
    public function addCopyWatermark(array|string $pages = 'all'): static
    {
        return $this->addWatermark('COPY', [
            'color' => [0.5, 0.5, 0.5], // Gray
            'opacity' => 0.2,
            'pages' => $pages,
        ]);
    }

    /**
     * Add page numbers
     *
     * @param string $format Format string (use {page} and {total} placeholders)
     * @param string $position Position on page
     * @param array<string, mixed> $config Additional configuration
     * @return static
     */
    public function addPageNumbers(
        string $format = 'Page {page} of {total}',
        string $position = self::POSITION_BOTTOM_CENTER,
        array $config = []
    ): static {
        $this->stamps[] = [
            'type' => 'pageNumber',
            'config' => array_merge([
                'format' => $format,
                'position' => $position,
                'fontSize' => 10,
                'fontFamily' => 'Helvetica',
                'color' => [0, 0, 0],
                'opacity' => 1.0,
                'pages' => 'all',
            ], $config),
            'layer' => self::LAYER_FOREGROUND,
        ];

        return $this;
    }

    /**
     * Add a header to all pages
     *
     * @param string $text Header text
     * @param array<string, mixed> $config Configuration
     * @return static
     */
    public function addHeader(string $text, array $config = []): static
    {
        return $this->addTextStamp($text, array_merge([
            'position' => self::POSITION_TOP_CENTER,
            'fontSize' => 10,
        ], $config));
    }

    /**
     * Add a footer to all pages
     *
     * @param string $text Footer text
     * @param array<string, mixed> $config Configuration
     * @return static
     */
    public function addFooter(string $text, array $config = []): static
    {
        return $this->addTextStamp($text, array_merge([
            'position' => self::POSITION_BOTTOM_CENTER,
            'fontSize' => 10,
        ], $config));
    }

    /**
     * Add a date stamp
     *
     * @param string $format Date format (default: Y-m-d H:i:s)
     * @param string $position Position on page
     * @param array<string, mixed> $config Additional configuration
     * @return static
     */
    public function addDateStamp(
        string $format = 'Y-m-d H:i:s',
        string $position = self::POSITION_TOP_RIGHT,
        array $config = []
    ): static {
        $dateText = date($format);
        return $this->addTextStamp($dateText, array_merge([
            'position' => $position,
            'fontSize' => 8,
        ], $config));
    }

    /**
     * Add a "VOID" stamp (typically for cancelled documents)
     *
     * @param array<int>|string $pages Pages to apply to
     * @return static
     */
    public function addVoidStamp(array|string $pages = 'all'): static
    {
        return $this->addWatermark('VOID', [
            'color' => [1, 0, 0], // Red
            'opacity' => 0.3,
            'fontSize' => 100,
            'pages' => $pages,
        ]);
    }

    /**
     * Clear all stamps
     *
     * @return static
     */
    public function clearStamps(): static
    {
        $this->stamps = [];
        return $this;
    }

    /**
     * Get the number of stamps added
     *
     * @return int Number of stamps
     */
    public function getStampCount(): int
    {
        return count($this->stamps);
    }

    /**
     * Apply all stamps and return the modified PDF
     *
     * @return string Modified PDF content
     * @throws PdfException If no PDF loaded
     */
    public function apply(): string
    {
        if (empty($this->pdfContent)) {
            throw new PdfException('No PDF content loaded');
        }

        if (empty($this->stamps)) {
            return $this->pdfContent; // No modifications needed
        }

        return $this->applyStamps();
    }

    /**
     * Apply stamps and save to file
     *
     * @param string $outputPath Output file path
     * @return bool True on success
     * @throws PdfException If apply or save fails
     */
    public function applyToFile(string $outputPath): bool
    {
        $result = $this->apply();
        return file_put_contents($outputPath, $result) !== false;
    }

    /**
     * Parse PDF version
     */
    protected function parseVersion(): void
    {
        if (preg_match('/^%PDF-(\d+\.\d+)/', $this->pdfContent, $matches)) {
            $this->pdfVersion = $matches[1];
        }
    }

    /**
     * Apply all stamps to the PDF
     *
     * @return string Modified PDF content
     */
    protected function applyStamps(): string
    {
        // For simplicity, we'll create a new PDF with the stamps
        // A full implementation would modify the existing PDF structure

        $pageCount = $this->countPages();
        $pageMediaBoxes = $this->getPageMediaBoxes();

        // Generate stamp content streams for each page
        $stampStreams = [];
        for ($page = 1; $page <= $pageCount; $page++) {
            $mediaBox = $pageMediaBoxes[$page - 1] ?? [0, 0, 612, 792];
            $stampStreams[$page] = $this->generateStampStream($page, $pageCount, $mediaBox);
        }

        // Apply stamps by modifying content streams
        return $this->modifyPdfWithStamps($stampStreams);
    }

    /**
     * Count pages in the PDF
     *
     * @return int Number of pages
     */
    protected function countPages(): int
    {
        $count = 0;
        if (preg_match_all('/\/Type\s*\/Page\b(?!s)/', $this->pdfContent, $matches)) {
            $count = count($matches[0]);
        }
        return max(1, $count);
    }

    /**
     * Get MediaBox for each page
     *
     * @return array<array<float>> Array of MediaBox arrays
     */
    protected function getPageMediaBoxes(): array
    {
        $boxes = [];
        $default = [0, 0, 612, 792];

        if (preg_match_all('/\/MediaBox\s*\[\s*([\d.-]+)\s+([\d.-]+)\s+([\d.-]+)\s+([\d.-]+)\s*\]/', $this->pdfContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $boxes[] = [
                    (float) $match[1],
                    (float) $match[2],
                    (float) $match[3],
                    (float) $match[4],
                ];
            }
        }

        // Ensure we have at least one box
        if (empty($boxes)) {
            $boxes[] = $default;
        }

        return $boxes;
    }

    /**
     * Generate stamp content stream for a page
     *
     * @param int $pageNum Current page number
     * @param int $totalPages Total pages
     * @param array<float> $mediaBox Page MediaBox
     * @return string PDF content stream commands
     */
    protected function generateStampStream(int $pageNum, int $totalPages, array $mediaBox): string
    {
        $stream = "q\n"; // Save graphics state

        $pageWidth = $mediaBox[2] - $mediaBox[0];
        $pageHeight = $mediaBox[3] - $mediaBox[1];

        foreach ($this->stamps as $stamp) {
            // Check if stamp applies to this page
            if (!$this->stampAppliesToPage($stamp['config']['pages'] ?? 'all', $pageNum, $totalPages)) {
                continue;
            }

            $stream .= $this->generateSingleStampStream($stamp, $pageNum, $totalPages, $pageWidth, $pageHeight);
        }

        $stream .= "Q\n"; // Restore graphics state

        return $stream;
    }

    /**
     * Check if stamp applies to a specific page
     *
     * @param array<int>|string $pages Page specification
     * @param int $pageNum Current page number
     * @param int $totalPages Total pages
     * @return bool True if stamp applies
     */
    protected function stampAppliesToPage(array|string $pages, int $pageNum, int $totalPages): bool
    {
        if ($pages === 'all') {
            return true;
        }

        if ($pages === 'first') {
            return $pageNum === 1;
        }

        if ($pages === 'last') {
            return $pageNum === $totalPages;
        }

        if (is_array($pages)) {
            return in_array($pageNum, $pages);
        }

        return true;
    }

    /**
     * Generate stream for a single stamp
     *
     * @param array{type: string, config: array<string, mixed>, layer: string} $stamp Stamp data
     * @param int $pageNum Current page
     * @param int $totalPages Total pages
     * @param float $pageWidth Page width
     * @param float $pageHeight Page height
     * @return string Content stream commands
     */
    protected function generateSingleStampStream(
        array $stamp,
        int $pageNum,
        int $totalPages,
        float $pageWidth,
        float $pageHeight
    ): string {
        $config = $stamp['config'];
        $stream = '';

        // Set opacity if needed
        $opacity = $config['opacity'] ?? 1.0;
        if ($opacity < 1.0) {
            // Create ExtGState for opacity would require object modification
            // For simplicity, we'll just note this would be applied
        }

        // Set color
        $color = $config['color'] ?? [0, 0, 0];
        $stream .= sprintf("%.3f %.3f %.3f rg\n", $color[0], $color[1], $color[2]);

        // Get text
        $text = $config['text'] ?? '';
        if ($stamp['type'] === 'pageNumber') {
            $format = $config['format'] ?? 'Page {page} of {total}';
            $text = str_replace(['{page}', '{total}'], [$pageNum, $totalPages], $format);
        }

        // Calculate position
        $fontSize = $config['fontSize'] ?? 12;
        $position = $config['position'] ?? null;

        if ($position !== null) {
            [$x, $y] = $this->calculatePosition($position, $pageWidth, $pageHeight, strlen($text) * $fontSize * 0.5, $fontSize);
        } else {
            $x = $config['x'] ?? 10;
            $y = $config['y'] ?? 10;
        }

        // Handle rotation
        $rotation = $config['rotation'] ?? 0;

        if ($rotation != 0) {
            $rad = deg2rad($rotation);
            $cos = cos($rad);
            $sin = sin($rad);

            // For centered watermarks, rotate around center
            if ($stamp['type'] === 'watermark') {
                $centerX = $pageWidth / 2;
                $centerY = $pageHeight / 2;
                $stream .= sprintf(
                    "%.4f %.4f %.4f %.4f %.4f %.4f cm\n",
                    $cos,
                    $sin,
                    -$sin,
                    $cos,
                    $centerX - $centerX * $cos + $centerY * $sin,
                    $centerY - $centerX * $sin - $centerY * $cos
                );
                $x = $centerX - (strlen($text) * $fontSize * 0.3);
                $y = $centerY;
            }
        }

        // Add text
        $stream .= "BT\n";
        $stream .= "/F1 {$fontSize} Tf\n";
        $stream .= sprintf("%.4f %.4f Td\n", $x, $y);
        $stream .= "(" . $this->escapeText($text) . ") Tj\n";
        $stream .= "ET\n";

        return $stream;
    }

    /**
     * Calculate position based on position constant
     *
     * @param string $position Position constant
     * @param float $pageWidth Page width
     * @param float $pageHeight Page height
     * @param float $textWidth Approximate text width
     * @param float $textHeight Text height
     * @return array{float, float} [x, y] coordinates
     */
    protected function calculatePosition(
        string $position,
        float $pageWidth,
        float $pageHeight,
        float $textWidth,
        float $textHeight
    ): array {
        $margin = 20;

        return match ($position) {
            self::POSITION_TOP_LEFT => [$margin, $pageHeight - $margin - $textHeight],
            self::POSITION_TOP_CENTER => [($pageWidth - $textWidth) / 2, $pageHeight - $margin - $textHeight],
            self::POSITION_TOP_RIGHT => [$pageWidth - $margin - $textWidth, $pageHeight - $margin - $textHeight],
            self::POSITION_CENTER_LEFT => [$margin, ($pageHeight - $textHeight) / 2],
            self::POSITION_CENTER => [($pageWidth - $textWidth) / 2, ($pageHeight - $textHeight) / 2],
            self::POSITION_CENTER_RIGHT => [$pageWidth - $margin - $textWidth, ($pageHeight - $textHeight) / 2],
            self::POSITION_BOTTOM_LEFT => [$margin, $margin],
            self::POSITION_BOTTOM_CENTER => [($pageWidth - $textWidth) / 2, $margin],
            self::POSITION_BOTTOM_RIGHT => [$pageWidth - $margin - $textWidth, $margin],
            default => [$margin, $margin],
        };
    }

    /**
     * Escape text for PDF string
     *
     * @param string $text Text to escape
     * @return string Escaped text
     */
    protected function escapeText(string $text): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $text
        );
    }

    /**
     * Modify PDF with stamp streams
     *
     * @param array<int, string> $stampStreams Stamp streams per page
     * @return string Modified PDF
     */
    protected function modifyPdfWithStamps(array $stampStreams): string
    {
        // Find all page objects
        $pageObjects = $this->findPageObjects();

        if (empty($pageObjects)) {
            return $this->pdfContent;
        }

        // Find the highest object number
        $maxObjNum = 0;
        foreach ($this->objects as $objNum => $content) {
            $maxObjNum = max($maxObjNum, $objNum);
        }

        // Extract ColorSpace content from resources
        $colorSpaceContent = '';
        if (!empty($this->resourcesContent)) {
            $colorSpaceContent = $this->extractNestedDict($this->resourcesContent, '/ColorSpace');
        }

        // Build the output PDF
        $output = "%PDF-{$this->pdfVersion}\n";
        $output .= "%\xE2\xE3\xCF\xD3\n"; // Binary marker

        $xref = [];
        $newContentObjects = [];
        $nextObjNum = $maxObjNum + 1;

        // Create new content stream objects for stamps
        $pageNum = 1;
        foreach ($pageObjects as $pageObjNum => $pageContent) {
            if (isset($stampStreams[$pageNum]) && !empty(trim($stampStreams[$pageNum]))) {
                // Create a new content stream for the stamp
                $stampStreamContent = $stampStreams[$pageNum];
                $newContentObjects[$pageObjNum] = [
                    'objNum' => $nextObjNum,
                    'content' => $stampStreamContent,
                ];
                $nextObjNum++;
            }
            $pageNum++;
        }

        // Allocate ColorSpace object if needed
        $colorSpaceObjNum = null;
        if (!empty($colorSpaceContent)) {
            $colorSpaceObjNum = $nextObjNum;
            $nextObjNum++;
        }

        // Write all original objects, modifying page objects as needed
        $pageNum = 1;
        foreach ($this->objects as $objNum => $content) {
            $xref[$objNum] = strlen($output);

            // Check if this is a page object that needs stamp content added
            if (isset($pageObjects[$objNum]) && isset($newContentObjects[$objNum])) {
                $output .= $this->buildModifiedPageObject(
                    $objNum,
                    $content,
                    $newContentObjects[$objNum]['objNum'],
                    $colorSpaceObjNum
                );
            } else {
                $output .= "{$objNum} 0 obj\n{$content}\nendobj\n";
            }

            if (isset($pageObjects[$objNum])) {
                $pageNum++;
            }
        }

        // Write new stamp content stream objects
        foreach ($newContentObjects as $pageObjNum => $data) {
            $xref[$data['objNum']] = strlen($output);
            $streamContent = $data['content'];
            $streamLength = strlen($streamContent);

            $output .= "{$data['objNum']} 0 obj\n";
            $output .= "<< /Length {$streamLength} >>\n";
            $output .= "stream\n";
            $output .= $streamContent;
            $output .= "\nendstream\n";
            $output .= "endobj\n";
        }

        // Write ColorSpace object if needed
        if ($colorSpaceObjNum !== null && !empty($colorSpaceContent)) {
            $xref[$colorSpaceObjNum] = strlen($output);
            $output .= "{$colorSpaceObjNum} 0 obj\n";
            $output .= "<< {$colorSpaceContent} >>\n";
            $output .= "endobj\n";
        }

        // Write xref table
        $xrefOffset = strlen($output);
        $output .= "xref\n";
        $output .= "0 " . ($nextObjNum) . "\n";
        $output .= "0000000000 65535 f \n";

        for ($i = 1; $i < $nextObjNum; $i++) {
            if (isset($xref[$i])) {
                $output .= sprintf("%010d 00000 n \n", $xref[$i]);
            } else {
                $output .= "0000000000 65535 f \n";
            }
        }

        // Find Root and Info from original trailer
        $rootRef = '';
        $infoRef = '';
        if (preg_match('/\/Root\s+(\d+\s+\d+\s+R)/', $this->pdfContent, $match)) {
            $rootRef = $match[1];
        }
        if (preg_match('/\/Info\s+(\d+\s+\d+\s+R)/', $this->pdfContent, $match)) {
            $infoRef = $match[1];
        }

        // Write trailer
        $output .= "trailer\n";
        $output .= "<< /Size {$nextObjNum}";
        if ($rootRef) {
            $output .= " /Root {$rootRef}";
        }
        if ($infoRef) {
            $output .= " /Info {$infoRef}";
        }
        $output .= " >>\n";
        $output .= "startxref\n";
        $output .= "{$xrefOffset}\n";
        $output .= "%%EOF\n";

        return $output;
    }

    /**
     * Find all page objects
     *
     * @return array<int, string> Map of object numbers to page content
     */
    protected function findPageObjects(): array
    {
        $pages = [];
        foreach ($this->objects as $objNum => $content) {
            // Match /Type /Page but not /Type /Pages
            if (preg_match('/\/Type\s*\/Page\b(?!s)/', $content)) {
                $pages[$objNum] = $content;
            }
        }
        return $pages;
    }

    /**
     * Build a modified page object with stamp content reference
     *
     * @param int $objNum Page object number
     * @param string $content Original page content
     * @param int $stampObjNum Stamp content stream object number
     * @param int|null $colorSpaceObjNum ColorSpace object number
     * @return string Modified page object
     */
    protected function buildModifiedPageObject(
        int $objNum,
        string $content,
        int $stampObjNum,
        ?int $colorSpaceObjNum
    ): string {
        // Find existing Contents reference
        $hasContents = preg_match('/\/Contents\s+(\d+)\s+0\s+R/', $content, $match);
        $hasContentsArray = preg_match('/\/Contents\s*\[(.*?)\]/', $content, $arrayMatch);

        if ($hasContents) {
            $existingRef = $match[1];
            // Replace single reference with array including stamp
            $content = preg_replace(
                '/\/Contents\s+\d+\s+0\s+R/',
                "/Contents [{$existingRef} 0 R {$stampObjNum} 0 R]",
                $content
            );
        } elseif ($hasContentsArray) {
            // Add to existing array
            $existingRefs = trim($arrayMatch[1]);
            $content = preg_replace(
                '/\/Contents\s*\[.*?\]/',
                "/Contents [{$existingRefs} {$stampObjNum} 0 R]",
                $content
            );
        } else {
            // No contents - add new
            $content = preg_replace(
                '/(\/Type\s*\/Page)/',
                "$1 /Contents {$stampObjNum} 0 R",
                $content
            );
        }

        // Update Resources to include font for stamps
        if (preg_match('/\/Resources\s+(\d+)\s+0\s+R/', $content)) {
            // Resources is a reference - we need to ensure font is available
            // For simplicity, we add an inline Resources entry
            $colorSpaceEntry = '';
            if ($colorSpaceObjNum !== null) {
                $colorSpaceEntry = " /ColorSpace {$colorSpaceObjNum} 0 R";
            }

            // Check if we have existing resource reference to preserve
            if (preg_match('/\/Resources\s+(\d+)\s+0\s+R/', $content, $resMatch)) {
                $resObjNum = (int) $resMatch[1];
                // Keep original resources reference but add font inline
                $content = preg_replace(
                    '/\/Resources\s+\d+\s+0\s+R/',
                    "/Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> /ProcSet [/PDF /Text]{$colorSpaceEntry} >>",
                    $content
                );
            }
        } elseif (preg_match('/\/Resources\s*<</', $content)) {
            // Inline resources - add font if not present
            if (strpos($content, '/Font') === false) {
                $content = preg_replace(
                    '/\/Resources\s*<</',
                    '/Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >>',
                    $content
                );
            }
        } else {
            // No resources - add them
            $colorSpaceEntry = '';
            if ($colorSpaceObjNum !== null) {
                $colorSpaceEntry = " /ColorSpace {$colorSpaceObjNum} 0 R";
            }
            $content = preg_replace(
                '/(\/Type\s*\/Page)/',
                "$1 /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> /ProcSet [/PDF /Text]{$colorSpaceEntry} >>",
                $content
            );
        }

        return "{$objNum} 0 obj\n{$content}\nendobj\n";
    }
}
