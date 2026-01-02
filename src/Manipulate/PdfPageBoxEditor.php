<?php

/**
 * PdfPageBoxEditor.php
 *
 * PDF Page box editing functionality (MediaBox, CropBox, etc.).
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
 * PDF Page Box Editor
 *
 * Edits page boxes (MediaBox, CropBox, BleedBox, TrimBox, ArtBox) in PDF documents.
 *
 * @category  Library
 * @package   Pdf
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class PdfPageBoxEditor
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
     * Page box modifications
     *
     * @var array<int, array<string, array<float>>>
     */
    protected array $pageBoxes = [];

    /**
     * Current object number
     */
    protected int $objectNumber = 1;

    /**
     * PDF version
     */
    protected string $pdfVersion = '1.7';

    /**
     * Standard page sizes in points (width x height)
     *
     * @var array<string, array{0: float, 1: float}>
     */
    protected array $standardSizes = [
        'A0' => [2383.94, 3370.39],
        'A1' => [1683.78, 2383.94],
        'A2' => [1190.55, 1683.78],
        'A3' => [841.89, 1190.55],
        'A4' => [595.28, 841.89],
        'A5' => [419.53, 595.28],
        'A6' => [297.64, 419.53],
        'LETTER' => [612.0, 792.0],
        'LEGAL' => [612.0, 1008.0],
        'TABLOID' => [792.0, 1224.0],
    ];

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
        $this->pageBoxes = [];
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
        $this->pageBoxes = [];
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
     * Get page count
     *
     * @return int Number of pages
     */
    public function getPageCount(): int
    {
        $count = 0;
        foreach ($this->objects as $objContent) {
            if ($this->isPageObject($objContent)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Set MediaBox for pages
     *
     * @param float $llx Lower-left X
     * @param float $lly Lower-left Y
     * @param float $urx Upper-right X
     * @param float $ury Upper-right Y
     * @param array<int>|string $pages Pages to apply to
     * @return static
     */
    public function setMediaBox(
        float $llx,
        float $lly,
        float $urx,
        float $ury,
        array|string $pages = 'all'
    ): static {
        return $this->setBox('MediaBox', [$llx, $lly, $urx, $ury], $pages);
    }

    /**
     * Set CropBox for pages
     *
     * @param float $llx Lower-left X
     * @param float $lly Lower-left Y
     * @param float $urx Upper-right X
     * @param float $ury Upper-right Y
     * @param array<int>|string $pages Pages to apply to
     * @return static
     */
    public function setCropBox(
        float $llx,
        float $lly,
        float $urx,
        float $ury,
        array|string $pages = 'all'
    ): static {
        return $this->setBox('CropBox', [$llx, $lly, $urx, $ury], $pages);
    }

    /**
     * Set BleedBox for pages
     *
     * @param float $llx Lower-left X
     * @param float $lly Lower-left Y
     * @param float $urx Upper-right X
     * @param float $ury Upper-right Y
     * @param array<int>|string $pages Pages to apply to
     * @return static
     */
    public function setBleedBox(
        float $llx,
        float $lly,
        float $urx,
        float $ury,
        array|string $pages = 'all'
    ): static {
        return $this->setBox('BleedBox', [$llx, $lly, $urx, $ury], $pages);
    }

    /**
     * Set TrimBox for pages
     *
     * @param float $llx Lower-left X
     * @param float $lly Lower-left Y
     * @param float $urx Upper-right X
     * @param float $ury Upper-right Y
     * @param array<int>|string $pages Pages to apply to
     * @return static
     */
    public function setTrimBox(
        float $llx,
        float $lly,
        float $urx,
        float $ury,
        array|string $pages = 'all'
    ): static {
        return $this->setBox('TrimBox', [$llx, $lly, $urx, $ury], $pages);
    }

    /**
     * Set ArtBox for pages
     *
     * @param float $llx Lower-left X
     * @param float $lly Lower-left Y
     * @param float $urx Upper-right X
     * @param float $ury Upper-right Y
     * @param array<int>|string $pages Pages to apply to
     * @return static
     */
    public function setArtBox(
        float $llx,
        float $lly,
        float $urx,
        float $ury,
        array|string $pages = 'all'
    ): static {
        return $this->setBox('ArtBox', [$llx, $lly, $urx, $ury], $pages);
    }

    /**
     * Set a box for pages
     *
     * @param string $boxType Box type (MediaBox, CropBox, etc.)
     * @param array<float> $box Box coordinates [llx, lly, urx, ury]
     * @param array<int>|string $pages Pages to apply to
     * @return static
     */
    protected function setBox(string $boxType, array $box, array|string $pages): static
    {
        $totalPages = $this->getPageCount();

        if ($pages === 'all') {
            for ($i = 1; $i <= $totalPages; $i++) {
                $this->pageBoxes[$i][$boxType] = $box;
            }
        } elseif (is_array($pages)) {
            foreach ($pages as $pageNum) {
                if ($pageNum >= 1 && $pageNum <= $totalPages) {
                    $this->pageBoxes[$pageNum][$boxType] = $box;
                }
            }
        }

        return $this;
    }

    /**
     * Crop pages to specified dimensions
     *
     * @param float $width Width in points
     * @param float $height Height in points
     * @param array<int>|string $pages Pages to crop
     * @param string $position Position: 'center', 'top-left', 'top-right', 'bottom-left', 'bottom-right'
     * @return static
     */
    public function cropTo(
        float $width,
        float $height,
        array|string $pages = 'all',
        string $position = 'center'
    ): static {
        $totalPages = $this->getPageCount();
        $parsedPages = $this->parsePages();

        $pageList = $pages === 'all' ? range(1, $totalPages) : $pages;

        foreach ($pageList as $pageNum) {
            if ($pageNum < 1 || $pageNum > $totalPages) {
                continue;
            }

            $pageIdx = $pageNum - 1;
            if (!isset($parsedPages[$pageIdx])) {
                continue;
            }

            $currentBox = $parsedPages[$pageIdx]['mediaBox'];
            $currentWidth = $currentBox[2] - $currentBox[0];
            $currentHeight = $currentBox[3] - $currentBox[1];

            [$llx, $lly] = $this->calculateCropPosition(
                $currentWidth,
                $currentHeight,
                $width,
                $height,
                $position
            );

            $llx += $currentBox[0];
            $lly += $currentBox[1];

            $this->pageBoxes[$pageNum]['CropBox'] = [$llx, $lly, $llx + $width, $lly + $height];
        }

        return $this;
    }

    /**
     * Calculate crop position offset
     *
     * @param float $currentWidth Current page width
     * @param float $currentHeight Current page height
     * @param float $newWidth New width
     * @param float $newHeight New height
     * @param string $position Position type
     * @return array{0: float, 1: float} Offset [x, y]
     */
    protected function calculateCropPosition(
        float $currentWidth,
        float $currentHeight,
        float $newWidth,
        float $newHeight,
        string $position
    ): array {
        $offsetX = 0;
        $offsetY = 0;

        switch ($position) {
            case 'center':
                $offsetX = ($currentWidth - $newWidth) / 2;
                $offsetY = ($currentHeight - $newHeight) / 2;
                break;
            case 'top-left':
                $offsetX = 0;
                $offsetY = $currentHeight - $newHeight;
                break;
            case 'top-right':
                $offsetX = $currentWidth - $newWidth;
                $offsetY = $currentHeight - $newHeight;
                break;
            case 'bottom-left':
                $offsetX = 0;
                $offsetY = 0;
                break;
            case 'bottom-right':
                $offsetX = $currentWidth - $newWidth;
                $offsetY = 0;
                break;
        }

        return [$offsetX, $offsetY];
    }

    /**
     * Crop pages to standard size
     *
     * @param string $size Size name (A4, LETTER, etc.)
     * @param array<int>|string $pages Pages to crop
     * @param string $position Position: 'center', 'top-left', 'top-right', 'bottom-left', 'bottom-right'
     * @return static
     * @throws PdfException If unknown size
     */
    public function cropToSize(string $size, array|string $pages = 'all', string $position = 'center'): static
    {
        $size = strtoupper($size);
        if (!isset($this->standardSizes[$size])) {
            throw new PdfException("Unknown page size: {$size}");
        }

        [$width, $height] = $this->standardSizes[$size];

        return $this->cropTo($width, $height, $pages, $position);
    }

    /**
     * Resize pages to standard size
     *
     * @param string $size Size name (A4, LETTER, etc.)
     * @param array<int>|string $pages Pages to resize
     * @return static
     * @throws PdfException If unknown size
     */
    public function resizeTo(string $size, array|string $pages = 'all'): static
    {
        $size = strtoupper($size);
        if (!isset($this->standardSizes[$size])) {
            throw new PdfException("Unknown page size: {$size}");
        }

        [$width, $height] = $this->standardSizes[$size];

        return $this->resizeToCustom($width, $height, $pages);
    }

    /**
     * Resize pages to custom dimensions
     *
     * @param float $width Width in points
     * @param float $height Height in points
     * @param array<int>|string $pages Pages to resize
     * @return static
     */
    public function resizeToCustom(float $width, float $height, array|string $pages = 'all'): static
    {
        return $this->setMediaBox(0, 0, $width, $height, $pages);
    }

    /**
     * Add margin to pages (shrink content area)
     *
     * @param float $margin Margin in points (all sides)
     * @param array<int>|string $pages Pages to apply to
     * @return static
     */
    public function addMargin(float $margin, array|string $pages = 'all'): static
    {
        return $this->addMargins($margin, $margin, $margin, $margin, $pages);
    }

    /**
     * Add margins to pages (shrink content area)
     *
     * @param float $top Top margin
     * @param float $right Right margin
     * @param float $bottom Bottom margin
     * @param float $left Left margin
     * @param array<int>|string $pages Pages to apply to
     * @return static
     */
    public function addMargins(
        float $top,
        float $right,
        float $bottom,
        float $left,
        array|string $pages = 'all'
    ): static {
        $totalPages = $this->getPageCount();
        $parsedPages = $this->parsePages();

        $pageList = $pages === 'all' ? range(1, $totalPages) : $pages;

        foreach ($pageList as $pageNum) {
            if ($pageNum < 1 || $pageNum > $totalPages) {
                continue;
            }

            $pageIdx = $pageNum - 1;
            if (!isset($parsedPages[$pageIdx])) {
                continue;
            }

            $currentBox = $parsedPages[$pageIdx]['mediaBox'];

            $this->pageBoxes[$pageNum]['CropBox'] = [
                $currentBox[0] + $left,
                $currentBox[1] + $bottom,
                $currentBox[2] - $right,
                $currentBox[3] - $top,
            ];
        }

        return $this;
    }

    /**
     * Get supported page sizes
     *
     * @return array<string>
     */
    public function getSupportedSizes(): array
    {
        return array_keys($this->standardSizes);
    }

    /**
     * Get page size dimensions
     *
     * @param string $size Size name
     * @return array{0: float, 1: float}|null Width and height or null
     */
    public function getPageSizeDimensions(string $size): ?array
    {
        $size = strtoupper($size);
        return $this->standardSizes[$size] ?? null;
    }

    /**
     * Get MediaBox for a specific page
     *
     * @param int $page Page number (1-based)
     * @return array{0: float, 1: float, 2: float, 3: float} [llx, lly, urx, ury]
     * @throws PdfException If page not found
     */
    public function getMediaBox(int $page): array
    {
        $parsedPages = $this->parsePages();
        $pageIdx = $page - 1;

        if (!isset($parsedPages[$pageIdx])) {
            throw new PdfException("Page {$page} not found");
        }

        // Check if we have a pending modification
        if (isset($this->pageBoxes[$page]['MediaBox'])) {
            return $this->pageBoxes[$page]['MediaBox'];
        }

        return $parsedPages[$pageIdx]['mediaBox'];
    }

    /**
     * Get all boxes for a specific page
     *
     * @param int $page Page number (1-based)
     * @return array<string, array{0: float, 1: float, 2: float, 3: float}|null>
     * @throws PdfException If page not found
     */
    public function getAllBoxes(int $page): array
    {
        $parsedPages = $this->parsePages();
        $pageIdx = $page - 1;

        if (!isset($parsedPages[$pageIdx])) {
            throw new PdfException("Page {$page} not found");
        }

        $boxes = [
            'MediaBox' => $parsedPages[$pageIdx]['mediaBox'],
            'CropBox' => $parsedPages[$pageIdx]['cropBox'] ?? null,
            'BleedBox' => $parsedPages[$pageIdx]['bleedBox'] ?? null,
            'TrimBox' => $parsedPages[$pageIdx]['trimBox'] ?? null,
            'ArtBox' => $parsedPages[$pageIdx]['artBox'] ?? null,
        ];

        // Overlay with pending modifications
        if (isset($this->pageBoxes[$page])) {
            foreach ($this->pageBoxes[$page] as $boxName => $boxValue) {
                $boxes[$boxName] = $boxValue;
            }
        }

        return $boxes;
    }

    /**
     * Get all pending modifications
     *
     * @return array<int, array<string, array{0: float, 1: float, 2: float, 3: float}>>
     */
    public function getAllModifications(): array
    {
        return $this->pageBoxes;
    }

    /**
     * Reset modifications for a specific page
     *
     * @param int $page Page number (1-based)
     * @return static
     */
    public function resetPageModifications(int $page): static
    {
        unset($this->pageBoxes[$page]);
        return $this;
    }

    /**
     * Reset all modifications
     *
     * @return static
     */
    public function resetAllModifications(): static
    {
        $this->pageBoxes = [];
        return $this;
    }

    /**
     * Clear all box modifications
     *
     * @return static
     * @deprecated Use resetAllModifications() instead
     */
    public function clearModifications(): static
    {
        return $this->resetAllModifications();
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
     * Rebuild PDF with modified boxes
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

        // Pages and content
        foreach ($pages as $index => $page) {
            $pageObjNum = $pageObjNums[$index];
            $contentObjNum = $contentObjNums[$pageObjNum];
            $pageNumber = $index + 1;

            // Get modified boxes
            $boxes = $this->pageBoxes[$pageNumber] ?? [];

            // Use modified MediaBox or original
            $mediaBox = $boxes['MediaBox'] ?? $page['mediaBox'];
            $mediaBoxStr = sprintf(
                '[%.4f %.4f %.4f %.4f]',
                $mediaBox[0],
                $mediaBox[1],
                $mediaBox[2],
                $mediaBox[3]
            );

            $offsets[$pageObjNum] = strlen($pdf);
            $pdf .= "{$pageObjNum} 0 obj\n";
            $pdf .= "<<\n";
            $pdf .= "/Type /Page\n";
            $pdf .= "/Parent {$pagesObjNum} 0 R\n";
            $pdf .= "/MediaBox {$mediaBoxStr}\n";

            // Add other boxes if set
            foreach (['CropBox', 'BleedBox', 'TrimBox', 'ArtBox'] as $boxType) {
                if (isset($boxes[$boxType])) {
                    $boxStr = sprintf(
                        '[%.4f %.4f %.4f %.4f]',
                        $boxes[$boxType][0],
                        $boxes[$boxType][1],
                        $boxes[$boxType][2],
                        $boxes[$boxType][3]
                    );
                    $pdf .= "/{$boxType} {$boxStr}\n";
                }
            }

            $pdf .= "/Resources <<\n";
            $pdf .= "  /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]\n";
            $pdf .= "  /Font << /F1 {$fontObjNum} 0 R >>\n";
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
