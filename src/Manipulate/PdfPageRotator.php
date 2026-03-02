<?php

/**
 * PdfPageRotator.php
 *
 * PDF Page rotation functionality.
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
 * PDF Page Rotator
 *
 * Rotates pages in PDF documents.
 *
 * @category  Library
 * @package   Pdf
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class PdfPageRotator
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
     * Page rotations (page number => degrees)
     *
     * @var array<int, int>
     */
    protected array $rotations = [];

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
     * Valid rotation values
     *
     * @var array<int>
     */
    protected array $validRotations = [0, 90, 180, 270];

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
        $this->rotations = [];
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
        $this->rotations = [];
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
     * Extract resources dictionary from page objects
     */
    protected function extractResources(): void
    {
        foreach ($this->objects as $objContent) {
            // Look for Resources dictionary with ColorSpace
            if (preg_match('/\/Resources\s+(\d+)\s+0\s+R/', $objContent, $match)) {
                $resourcesObjNum = (int)$match[1];
                if (isset($this->objects[$resourcesObjNum])) {
                    $this->resourcesContent = $this->objects[$resourcesObjNum];
                    return;
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
     * Rotate a single page
     *
     * @param int $pageNum Page number (1-indexed)
     * @param int $degrees Rotation in degrees (0, 90, 180, 270)
     * @return static
     * @throws PdfException If invalid rotation
     */
    public function rotatePage(int $pageNum, int $degrees): static
    {
        $degrees = $this->normalizeRotation($degrees);
        $this->rotations[$pageNum] = $degrees;

        return $this;
    }

    /**
     * Rotate multiple pages
     *
     * @param int $degrees Rotation in degrees
     * @param array<int>|string $pages Pages to rotate ('all' or array of page numbers)
     * @return static
     */
    public function rotatePages(int $degrees, array|string $pages = 'all'): static
    {
        $degrees = $this->normalizeRotation($degrees);
        $totalPages = $this->getPageCount();

        if ($pages === 'all') {
            for ($i = 1; $i <= $totalPages; $i++) {
                $this->rotations[$i] = $degrees;
            }
        } elseif (is_array($pages)) {
            foreach ($pages as $pageNum) {
                if ($pageNum >= 1 && $pageNum <= $totalPages) {
                    $this->rotations[$pageNum] = $degrees;
                }
            }
        }

        return $this;
    }

    /**
     * Rotate all pages clockwise by 90 degrees
     *
     * @param array<int>|string $pages Pages to rotate
     * @return static
     */
    public function rotateClockwise(array|string $pages = 'all'): static
    {
        return $this->rotatePages(90, $pages);
    }

    /**
     * Rotate all pages counter-clockwise by 90 degrees
     *
     * @param array<int>|string $pages Pages to rotate
     * @return static
     */
    public function rotateCounterClockwise(array|string $pages = 'all'): static
    {
        return $this->rotatePages(270, $pages);
    }

    /**
     * Rotate all pages by 180 degrees (upside down)
     *
     * @param array<int>|string $pages Pages to rotate
     * @return static
     */
    public function rotateUpsideDown(array|string $pages = 'all'): static
    {
        return $this->rotatePages(180, $pages);
    }

    /**
     * Rotate odd pages
     *
     * @param int $degrees Rotation degrees
     * @return static
     */
    public function rotateOddPages(int $degrees): static
    {
        $totalPages = $this->getPageCount();
        $oddPages = [];

        for ($i = 1; $i <= $totalPages; $i += 2) {
            $oddPages[] = $i;
        }

        return $this->rotatePages($degrees, $oddPages);
    }

    /**
     * Rotate even pages
     *
     * @param int $degrees Rotation degrees
     * @return static
     */
    public function rotateEvenPages(int $degrees): static
    {
        $totalPages = $this->getPageCount();
        $evenPages = [];

        for ($i = 2; $i <= $totalPages; $i += 2) {
            $evenPages[] = $i;
        }

        return $this->rotatePages($degrees, $evenPages);
    }

    /**
     * Reset rotation for a page (set to 0 degrees)
     *
     * @param int $pageNum Page number
     * @return static
     */
    public function resetPageRotation(int $pageNum): static
    {
        $this->rotations[$pageNum] = 0;
        return $this;
    }

    /**
     * Reset all rotations
     *
     * @return static
     */
    public function resetAllRotations(): static
    {
        $this->rotations = [];
        return $this;
    }

    /**
     * Get current rotation for a page
     *
     * @param int $pageNum Page number
     * @return int Rotation in degrees
     */
    public function getPageRotation(int $pageNum): int
    {
        return $this->rotations[$pageNum] ?? 0;
    }

    /**
     * Get all rotations
     *
     * @return array<int, int> Page number => degrees
     */
    public function getAllRotations(): array
    {
        return $this->rotations;
    }

    /**
     * Normalize rotation to valid value
     *
     * @param int $degrees Rotation degrees
     * @return int Normalized rotation (0, 90, 180, 270)
     */
    protected function normalizeRotation(int $degrees): int
    {
        // Normalize to 0-359 range
        $degrees = $degrees % 360;
        if ($degrees < 0) {
            $degrees += 360;
        }

        // Round to nearest valid value
        if ($degrees < 45) {
            return 0;
        } elseif ($degrees < 135) {
            return 90;
        } elseif ($degrees < 225) {
            return 180;
        } elseif ($degrees < 315) {
            return 270;
        } else {
            return 0;
        }
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
     * Rebuild PDF with rotations
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

        // Allocate ColorSpace object if we have ColorSpace in resources
        $colorSpaceObjNum = null;
        if (!empty($this->resourcesContent) && str_contains($this->resourcesContent, '/ColorSpace')) {
            $colorSpaceObjNum = $this->objectNumber++;
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

        // ColorSpace (if needed)
        if ($colorSpaceObjNum !== null) {
            $offsets[$colorSpaceObjNum] = strlen($pdf);
            $pdf .= "{$colorSpaceObjNum} 0 obj\n";
            $pdf .= "[/Separation /Black /DeviceCMYK << /FunctionType 2 /Domain [0 1] /C0 [0 0 0 0] /C1 [0 0 0 1] /N 1 >>]\n";
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

            // Get rotation for this page
            $rotation = $this->rotations[$pageNumber] ?? 0;

            $offsets[$pageObjNum] = strlen($pdf);
            $pdf .= "{$pageObjNum} 0 obj\n";
            $pdf .= "<<\n";
            $pdf .= "/Type /Page\n";
            $pdf .= "/Parent {$pagesObjNum} 0 R\n";
            $pdf .= "/MediaBox {$mediaBox}\n";

            // Add rotation if not 0
            if ($rotation !== 0) {
                $pdf .= "/Rotate {$rotation}\n";
            }

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
