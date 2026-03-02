<?php

/**
 * PdfBarcodeStamper.php
 *
 * PDF Barcode/QR Code stamping functionality.
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
use Com\Tecnick\Barcode\Barcode as BarcodeGenerator;

/**
 * PDF Barcode Stamper
 *
 * Adds barcodes and QR codes to PDF documents.
 *
 * @category  Library
 * @package   Pdf
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class PdfBarcodeStamper
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
     * Parsed objects
     *
     * @var array<int, string>
     */
    protected array $objects = [];

    /**
     * Barcodes to add
     *
     * @var array<array{type: string, content: string, x: float, y: float, w: float, h: float, pages: array<int>|string, color?: string}>
     */
    protected array $barcodes = [];

    /**
     * Current object number
     */
    protected int $objectNumber = 1;

    /**
     * PDF version
     */
    protected string $pdfVersion = '1.7';

    /**
     * Barcode generator instance
     */
    protected ?BarcodeGenerator $barcodeGenerator = null;

    /**
     * Resources dictionary content (ColorSpace, XObject, etc.)
     */
    protected string $resourcesContent = '';

    /**
     * Supported barcode types
     *
     * @var array<string, string>
     */
    protected array $barcodeTypes = [
        'QRCODE' => 'QRCODE',
        'QR' => 'QRCODE',
        'C128' => 'C128',
        'CODE128' => 'C128',
        'C39' => 'C39',
        'CODE39' => 'C39',
        'EAN13' => 'EAN13',
        'EAN8' => 'EAN8',
        'UPCA' => 'UPCA',
        'UPCE' => 'UPCE',
        'I25' => 'I25',
        'ITF' => 'I25',
        'DATAMATRIX' => 'DATAMATRIX',
        'PDF417' => 'PDF417',
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->barcodeGenerator = new BarcodeGenerator();
    }

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
        $this->barcodes = [];
        $this->parseContent();

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
        $this->barcodes = [];
        $this->parseContent();

        return $this;
    }

    /**
     * Parse PDF content
     */
    protected function parseContent(): void
    {
        if (preg_match('/%PDF-(\d+\.\d+)/', $this->pdfContent, $match)) {
            $this->pdfVersion = $match[1];
        }

        $this->objects = $this->extractObjects($this->pdfContent);
        $this->extractResources();
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
     * Add a QR code to the PDF
     *
     * @param string $content Content to encode in QR code
     * @param float $x X position in points
     * @param float $y Y position in points (from bottom)
     * @param float $size Size (width/height) in points
     * @param array<int>|string $pages Pages to add QR to ('all' or array of page numbers)
     * @param string $color Hex color (default: black)
     * @return static
     */
    public function addQRCode(
        string $content,
        float $x,
        float $y,
        float $size,
        array|string $pages = 'all',
        string $color = '#000000'
    ): static {
        $this->barcodes[] = [
            'type' => 'QRCODE',
            'content' => $content,
            'x' => $x,
            'y' => $y,
            'w' => $size,
            'h' => $size,
            'pages' => $pages,
            'color' => $color,
        ];

        return $this;
    }

    /**
     * Add a barcode to the PDF
     *
     * @param string $type Barcode type (C128, C39, EAN13, etc.)
     * @param string $content Content to encode
     * @param float $x X position in points
     * @param float $y Y position in points (from bottom)
     * @param float $width Width in points
     * @param float $height Height in points
     * @param array<int>|string $pages Pages to add barcode to
     * @param string $color Hex color
     * @return static
     * @throws PdfException If barcode type not supported
     */
    public function addBarcode(
        string $type,
        string $content,
        float $x,
        float $y,
        float $width,
        float $height,
        array|string $pages = 'all',
        string $color = '#000000'
    ): static {
        $type = strtoupper($type);
        if (!isset($this->barcodeTypes[$type])) {
            throw new PdfException("Unsupported barcode type: {$type}");
        }

        $this->barcodes[] = [
            'type' => $this->barcodeTypes[$type],
            'content' => $content,
            'x' => $x,
            'y' => $y,
            'w' => $width,
            'h' => $height,
            'pages' => $pages,
            'color' => $color,
        ];

        return $this;
    }

    /**
     * Add a DataMatrix code
     *
     * @param string $content Content to encode
     * @param float $x X position
     * @param float $y Y position
     * @param float $size Size
     * @param array<int>|string $pages Pages
     * @param string $color Color
     * @return static
     */
    public function addDataMatrix(
        string $content,
        float $x,
        float $y,
        float $size,
        array|string $pages = 'all',
        string $color = '#000000'
    ): static {
        return $this->addBarcode('DATAMATRIX', $content, $x, $y, $size, $size, $pages, $color);
    }

    /**
     * Add a Code 128 barcode
     *
     * @param string $content Content to encode
     * @param float $x X position
     * @param float $y Y position
     * @param float $width Width
     * @param float $height Height
     * @param array<int>|string $pages Pages
     * @param string $color Color
     * @return static
     */
    public function addCode128(
        string $content,
        float $x,
        float $y,
        float $width,
        float $height,
        array|string $pages = 'all',
        string $color = '#000000'
    ): static {
        return $this->addBarcode('C128', $content, $x, $y, $width, $height, $pages, $color);
    }

    /**
     * Get supported barcode types
     *
     * @return array<string>
     */
    public function getSupportedTypes(): array
    {
        return array_keys($this->barcodeTypes);
    }

    /**
     * Clear all barcodes
     *
     * @return static
     */
    public function clearBarcodes(): static
    {
        $this->barcodes = [];
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
     * Generate barcode graphics for PDF
     *
     * @param array{type: string, content: string, x: float, y: float, w: float, h: float, color?: string} $barcode
     * @param float $pageHeight Page height for Y coordinate conversion
     * @return string PDF graphics commands
     */
    protected function generateBarcodeGraphics(array $barcode, float $pageHeight): string
    {
        try {
            $barcodeObj = $this->barcodeGenerator->getBarcodeObj(
                $barcode['type'],
                $barcode['content'],
                (int) $barcode['w'],
                (int) $barcode['h'],
                $barcode['color'] ?? '#000000'
            );

            // Get barcode data array
            $barcodeData = $barcodeObj->getArray();

            if (empty($barcodeData['bars'])) {
                return '';
            }

            // Get the actual barcode dimensions from the library
            $barcodeWidth = $barcodeData['width'];
            $barcodeHeight = $barcodeData['height'];

            if ($barcodeWidth === 0 || $barcodeHeight === 0) {
                return '';
            }

            // Calculate scaling to fit our target size
            $scaleX = $barcode['w'] / $barcodeWidth;
            $scaleY = $barcode['h'] / $barcodeHeight;

            // Parse color
            $color = $barcode['color'] ?? '#000000';
            $rgb = $this->hexToRgb($color);

            $graphics = "q\n";
            $graphics .= sprintf("%.3f %.3f %.3f rg\n", $rgb[0], $rgb[1], $rgb[2]);

            // Draw each bar using the bars array [x, y, width, height]
            foreach ($barcodeData['bars'] as $bar) {
                // bar format: [x, y, width, height]
                $barX = $barcode['x'] + ($bar[0] * $scaleX);
                // PDF Y coordinate: convert from top-left origin to bottom-left origin
                $barY = $pageHeight - $barcode['y'] - (($bar[1] + $bar[3]) * $scaleY);
                $barW = $bar[2] * $scaleX;
                $barH = $bar[3] * $scaleY;

                $graphics .= sprintf(
                    "%.3f %.3f %.3f %.3f re f\n",
                    $barX,
                    $barY,
                    $barW,
                    $barH
                );
            }

            $graphics .= "Q\n";

            return $graphics;

        } catch (\Exception $e) {
            // If barcode generation fails, return empty string
            return '';
        }
    }

    /**
     * Convert hex color to RGB (0-1 range)
     *
     * @param string $hex Hex color
     * @return array{0: float, 1: float, 2: float}
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        return [$r, $g, $b];
    }

    /**
     * Rebuild PDF with barcodes
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

        $catalogObjNum = $this->objectNumber++;
        $pagesObjNum = $this->objectNumber++;
        $fontObjNum = $this->objectNumber++;

        // ColorSpace object (if present in original)
        $colorSpaceObjNum = null;
        $colorSpaceContent = '';
        if (!empty($this->resourcesContent)) {
            if (preg_match('/\/ColorSpace\s*<<\s*\/CS1\s+(\d+)\s+\d+\s+R/', $this->resourcesContent, $csMatch)) {
                $origCsObjNum = (int)$csMatch[1];
                if (isset($this->objects[$origCsObjNum])) {
                    $colorSpaceObjNum = $this->objectNumber++;
                    $colorSpaceContent = $this->objects[$origCsObjNum];
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

        // Pages and content
        foreach ($pages as $index => $page) {
            $pageObjNum = $pageObjNums[$index];
            $contentObjNum = $contentObjNums[$pageObjNum];
            $pageNumber = $index + 1;

            $mediaBox = sprintf(
                '[%.4f %.4f %.4f %.4f]',
                $page['mediaBox'][0],
                $page['mediaBox'][1],
                $page['mediaBox'][2],
                $page['mediaBox'][3]
            );

            $pageHeight = $page['mediaBox'][3] - $page['mediaBox'][1];

            // Build content with barcodes
            $stream = $page['contentStream'];

            // Add barcodes for this page
            foreach ($this->barcodes as $barcode) {
                if ($this->isPageIncluded($pageNumber, $barcode['pages'], count($pages))) {
                    $barcodeGraphics = $this->generateBarcodeGraphics($barcode, $pageHeight);
                    if (!empty($barcodeGraphics)) {
                        $stream .= "\n" . $barcodeGraphics;
                    }
                }
            }

            $streamLength = strlen($stream);

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

            $offsets[$contentObjNum] = strlen($pdf);
            $pdf .= "{$contentObjNum} 0 obj\n";
            $pdf .= "<<\n/Length {$streamLength}\n>>\n";
            $pdf .= "stream\n";
            $pdf .= $stream;
            $pdf .= "\nendstream\n";
            $pdf .= "endobj\n";
        }

        // xref
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
     * Check if page is included
     *
     * @param int $pageNumber Page number (1-indexed)
     * @param array<int>|string $pages Page specification
     * @param int $totalPages Total pages
     * @return bool
     */
    protected function isPageIncluded(int $pageNumber, array|string $pages, int $totalPages): bool
    {
        if ($pages === 'all') {
            return true;
        }

        if (is_array($pages)) {
            return in_array($pageNumber, $pages);
        }

        return false;
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
