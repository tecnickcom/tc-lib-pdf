<?php

/**
 * PdfToImage.php
 *
 * PDF to image conversion class using Imagick or Ghostscript.
 *
 * @category    Library
 * @package     PdfManipulate
 * @subpackage  ToImage
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2024-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */

namespace Com\Tecnick\Pdf\Manipulate;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * PDF to Image conversion class
 *
 * Provides functionality to convert PDF pages to images.
 * Requires either Imagick extension or Ghostscript.
 *
 * Example usage:
 * ```php
 * $converter = new PdfToImage();
 * $converter->loadFile('document.pdf')
 *           ->setFormat('png')
 *           ->setResolution(150);
 * $converter->saveAllPagesToDirectory('./images');
 * ```
 *
 * @category    Library
 * @package     PdfManipulate
 */
class PdfToImage
{
    /**
     * Image format: PNG
     */
    public const FORMAT_PNG = 'png';

    /**
     * Image format: JPEG
     */
    public const FORMAT_JPEG = 'jpeg';

    /**
     * Image format: WebP
     */
    public const FORMAT_WEBP = 'webp';

    /**
     * Image format: GIF
     */
    public const FORMAT_GIF = 'gif';

    /**
     * Backend: Imagick
     */
    public const BACKEND_IMAGICK = 'imagick';

    /**
     * Backend: Ghostscript
     */
    public const BACKEND_GHOSTSCRIPT = 'ghostscript';

    /**
     * Path to loaded PDF file
     *
     * @var string
     */
    protected string $filePath = '';

    /**
     * PDF content (if loaded from string)
     *
     * @var string
     */
    protected string $pdfContent = '';

    /**
     * Output image format
     *
     * @var string
     */
    protected string $format = self::FORMAT_PNG;

    /**
     * Image resolution in DPI
     *
     * @var int
     */
    protected int $resolution = 150;

    /**
     * Image quality (1-100)
     *
     * @var int
     */
    protected int $quality = 90;

    /**
     * Background color
     *
     * @var string
     */
    protected string $backgroundColor = 'white';

    /**
     * Number of pages in the PDF
     *
     * @var int
     */
    protected int $pageCount = 0;

    /**
     * Conversion backend to use
     *
     * @var string
     */
    protected string $backend = '';

    /**
     * Ghostscript binary path
     *
     * @var string
     */
    protected string $ghostscriptPath = 'gs';

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

        $this->filePath = $filePath;
        $this->pdfContent = '';
        $this->countPages();

        return $this;
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

        // Save to temp file for processing
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($tempFile, $content);

        $this->filePath = $tempFile;
        $this->pdfContent = $content;
        $this->countPages();

        return $this;
    }

    /**
     * Count pages in the PDF
     *
     * @return void
     */
    protected function countPages(): void
    {
        $this->pageCount = 0;

        if ($this->hasImagick()) {
            try {
                $im = new \Imagick();
                $im->pingImage($this->filePath);
                $this->pageCount = $im->getNumberImages();
                $im->clear();
                $im->destroy();
            } catch (\ImagickException $e) {
                // Fallback to parsing
                $this->countPagesFromContent();
            }
        } else {
            $this->countPagesFromContent();
        }
    }

    /**
     * Count pages by parsing PDF content
     *
     * @return void
     */
    protected function countPagesFromContent(): void
    {
        $content = $this->pdfContent ?: file_get_contents($this->filePath);
        if (preg_match('/\/Count\s+(\d+)/', $content, $matches)) {
            $this->pageCount = (int) $matches[1];
        } else {
            // Count /Type /Page occurrences
            $this->pageCount = preg_match_all('/\/Type\s*\/Page[^s]/', $content);
        }
    }

    /**
     * Set output image format
     *
     * @param string $format Format (png, jpeg, webp, gif)
     * @return static
     * @throws PdfException If format not supported
     */
    public function setFormat(string $format): static
    {
        $format = strtolower($format);
        $format = $format === 'jpg' ? 'jpeg' : $format;

        if (!in_array($format, [self::FORMAT_PNG, self::FORMAT_JPEG, self::FORMAT_WEBP, self::FORMAT_GIF], true)) {
            throw new PdfException("Unsupported format: {$format}");
        }

        $this->format = $format;
        return $this;
    }

    /**
     * Set image resolution in DPI
     *
     * @param int $dpi Resolution in dots per inch
     * @return static
     */
    public function setResolution(int $dpi): static
    {
        $this->resolution = max(72, min(600, $dpi));
        return $this;
    }

    /**
     * Set image quality (for JPEG/WebP)
     *
     * @param int $quality Quality (1-100)
     * @return static
     */
    public function setQuality(int $quality): static
    {
        $this->quality = max(1, min(100, $quality));
        return $this;
    }

    /**
     * Set background color
     *
     * @param string $color Color name or hex
     * @return static
     */
    public function setBackgroundColor(string $color): static
    {
        $this->backgroundColor = $color;
        return $this;
    }

    /**
     * Set Ghostscript binary path
     *
     * @param string $path Path to gs binary
     * @return static
     */
    public function setGhostscriptPath(string $path): static
    {
        $this->ghostscriptPath = $path;
        return $this;
    }

    /**
     * Force a specific backend
     *
     * @param string $backend Backend name (imagick or ghostscript)
     * @return static
     */
    public function setBackend(string $backend): static
    {
        $this->backend = strtolower($backend);
        return $this;
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
     * Get supported formats
     *
     * @return array<string>
     */
    public function getSupportedFormats(): array
    {
        return [self::FORMAT_PNG, self::FORMAT_JPEG, self::FORMAT_WEBP, self::FORMAT_GIF];
    }

    /**
     * Check if Imagick is available
     *
     * @return bool
     */
    public function hasImagick(): bool
    {
        return extension_loaded('imagick') && class_exists('\Imagick');
    }

    /**
     * Check if Ghostscript is available
     *
     * @return bool
     */
    public function hasGhostscript(): bool
    {
        $output = [];
        $result = -1;
        @exec($this->ghostscriptPath . ' --version 2>&1', $output, $result);
        return $result === 0;
    }

    /**
     * Get available backend
     *
     * @return string|null Backend name or null
     */
    public function getAvailableBackend(): ?string
    {
        if (!empty($this->backend)) {
            return $this->backend;
        }

        if ($this->hasImagick()) {
            return self::BACKEND_IMAGICK;
        }

        if ($this->hasGhostscript()) {
            return self::BACKEND_GHOSTSCRIPT;
        }

        return null;
    }

    /**
     * Convert a single page to image
     *
     * @param int $pageNum Page number (1-based)
     * @return string Image binary data
     * @throws PdfException If conversion fails
     */
    public function convertPage(int $pageNum): string
    {
        if (empty($this->filePath)) {
            throw new PdfException('No PDF loaded');
        }

        if ($pageNum < 1 || $pageNum > $this->pageCount) {
            throw new PdfException("Invalid page number: {$pageNum}");
        }

        $backend = $this->getAvailableBackend();

        if ($backend === self::BACKEND_IMAGICK) {
            return $this->convertPageWithImagick($pageNum);
        }

        if ($backend === self::BACKEND_GHOSTSCRIPT) {
            return $this->convertPageWithGhostscript($pageNum);
        }

        throw new PdfException('No conversion backend available. Install Imagick or Ghostscript.');
    }

    /**
     * Convert page using Imagick
     *
     * @param int $pageNum Page number (1-based)
     * @return string Image binary data
     * @throws PdfException If conversion fails
     */
    protected function convertPageWithImagick(int $pageNum): string
    {
        try {
            $im = new \Imagick();
            $im->setResolution($this->resolution, $this->resolution);
            $im->readImage($this->filePath . '[' . ($pageNum - 1) . ']');
            $im->setImageBackgroundColor($this->backgroundColor);
            $im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
            $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);

            $im->setImageFormat($this->format);

            if ($this->format === self::FORMAT_JPEG || $this->format === self::FORMAT_WEBP) {
                $im->setImageCompressionQuality($this->quality);
            }

            $data = $im->getImageBlob();
            $im->clear();
            $im->destroy();

            return $data;
        } catch (\ImagickException $e) {
            throw new PdfException('Imagick conversion failed: ' . $e->getMessage());
        }
    }

    /**
     * Convert page using Ghostscript
     *
     * @param int $pageNum Page number (1-based)
     * @return string Image binary data
     * @throws PdfException If conversion fails
     */
    protected function convertPageWithGhostscript(int $pageNum): string
    {
        $tempOutput = tempnam(sys_get_temp_dir(), 'gs_') . '.' . $this->format;

        $device = match ($this->format) {
            self::FORMAT_PNG => 'png16m',
            self::FORMAT_JPEG => 'jpeg',
            self::FORMAT_GIF => 'gif',
            self::FORMAT_WEBP => 'png16m', // GS doesn't have webp, convert after
            default => 'png16m',
        };

        $cmd = sprintf(
            '%s -dNOPAUSE -dBATCH -dSAFER -dQUIET ' .
            '-sDEVICE=%s -r%d -dFirstPage=%d -dLastPage=%d ' .
            '-sOutputFile=%s %s 2>&1',
            escapeshellcmd($this->ghostscriptPath),
            $device,
            $this->resolution,
            $pageNum,
            $pageNum,
            escapeshellarg($tempOutput),
            escapeshellarg($this->filePath)
        );

        $output = [];
        $result = -1;
        exec($cmd, $output, $result);

        if ($result !== 0 || !file_exists($tempOutput)) {
            throw new PdfException('Ghostscript conversion failed: ' . implode("\n", $output));
        }

        $data = file_get_contents($tempOutput);
        @unlink($tempOutput);

        if ($data === false) {
            throw new PdfException('Failed to read converted image');
        }

        // Convert to WebP if needed (GS doesn't support it directly)
        if ($this->format === self::FORMAT_WEBP && function_exists('imagecreatefromstring')) {
            $img = imagecreatefromstring($data);
            if ($img !== false) {
                ob_start();
                imagewebp($img, null, $this->quality);
                $data = ob_get_clean();
                imagedestroy($img);
            }
        }

        return $data;
    }

    /**
     * Convert all pages to images
     *
     * @return array<int, string> Array of image data keyed by page number
     * @throws PdfException If conversion fails
     */
    public function convertAllPages(): array
    {
        $images = [];

        for ($i = 1; $i <= $this->pageCount; $i++) {
            $images[$i] = $this->convertPage($i);
        }

        return $images;
    }

    /**
     * Save a single page as image file
     *
     * @param int $pageNum Page number (1-based)
     * @param string $outputPath Output file path
     * @return bool True on success
     * @throws PdfException If conversion fails
     */
    public function savePageToFile(int $pageNum, string $outputPath): bool
    {
        $data = $this->convertPage($pageNum);
        $result = file_put_contents($outputPath, $data);
        return $result !== false;
    }

    /**
     * Save all pages to a directory
     *
     * @param string $directory Output directory
     * @param string $prefix File name prefix
     * @return array<int, string> Array of file paths keyed by page number
     * @throws PdfException If conversion fails
     */
    public function saveAllPagesToDirectory(string $directory, string $prefix = 'page'): array
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new PdfException("Cannot create directory: {$directory}");
            }
        }

        $files = [];

        for ($i = 1; $i <= $this->pageCount; $i++) {
            $filename = sprintf('%s_%03d.%s', $prefix, $i, $this->format);
            $filepath = rtrim($directory, '/') . '/' . $filename;

            $this->savePageToFile($i, $filepath);
            $files[$i] = $filepath;
        }

        return $files;
    }

    /**
     * Get image dimensions for a page
     *
     * @param int $pageNum Page number (1-based)
     * @return array{width: int, height: int}|null Dimensions or null
     */
    public function getPageDimensions(int $pageNum): ?array
    {
        if (!$this->hasImagick()) {
            return null;
        }

        try {
            $im = new \Imagick();
            $im->setResolution($this->resolution, $this->resolution);
            $im->readImage($this->filePath . '[' . ($pageNum - 1) . ']');

            $width = $im->getImageWidth();
            $height = $im->getImageHeight();

            $im->clear();
            $im->destroy();

            return ['width' => $width, 'height' => $height];
        } catch (\ImagickException $e) {
            return null;
        }
    }

    /**
     * Destructor - cleanup temp files
     */
    public function __destruct()
    {
        // Clean up temp file if we created one from content
        if (!empty($this->pdfContent) && !empty($this->filePath) && file_exists($this->filePath)) {
            @unlink($this->filePath);
        }
    }
}
