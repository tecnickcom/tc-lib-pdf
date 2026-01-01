<?php

/**
 * PdfParser.php
 *
 * @since     2025-01-01
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Signature;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * PDF Parser for Incremental Updates
 *
 * Parses existing PDF documents to support incremental updates for multiple signatures.
 *
 * @since     2025-01-01
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type XrefEntry array{
 *     'offset': int,
 *     'generation': int,
 *     'free': bool,
 * }
 *
 * @phpstan-type Trailer array{
 *     'Size': int,
 *     'Root': string,
 *     'Info'?: string,
 *     'ID'?: array<string>,
 *     'Prev'?: int,
 * }
 */
class PdfParser
{
    /**
     * PDF content
     */
    protected string $pdfContent = '';

    /**
     * PDF version
     */
    protected string $pdfVersion = '1.7';

    /**
     * Cross-reference table
     *
     * @var array<int, XrefEntry>
     */
    protected array $xref = [];

    /**
     * Trailer dictionary
     *
     * @var Trailer
     */
    protected array $trailer = [
        'Size' => 0,
        'Root' => '',
    ];

    /**
     * Position of the last xref table
     */
    protected int $xrefPosition = 0;

    /**
     * Highest object number
     */
    protected int $maxObjectNumber = 0;

    /**
     * Existing signature fields
     *
     * @var array<int, array{
     *     'objNum': int,
     *     'name': string,
     *     'page': int,
     *     'rect': array<float>,
     *     'signed': bool,
     * }>
     */
    protected array $signatureFields = [];

    /**
     * Page objects
     *
     * @var array<int, array{
     *     'objNum': int,
     *     'objRef': string,
     * }>
     */
    protected array $pages = [];

    /**
     * Constructor
     *
     * @param string $pdfContent PDF content to parse
     */
    public function __construct(string $pdfContent)
    {
        $this->pdfContent = $pdfContent;
        $this->parse();
    }

    /**
     * Parse the PDF document
     */
    protected function parse(): void
    {
        $this->parsePdfVersion();
        $this->parseXref();
        $this->parseTrailer();
        $this->parsePages();
        $this->parseSignatureFields();
    }

    /**
     * Parse PDF version from header
     */
    protected function parsePdfVersion(): void
    {
        if (preg_match('/%PDF-(\d+\.\d+)/', $this->pdfContent, $matches)) {
            $this->pdfVersion = $matches[1];
        }
    }

    /**
     * Find and parse the cross-reference table
     */
    protected function parseXref(): void
    {
        // Find startxref position
        if (!preg_match('/startxref\s*(\d+)\s*%%EOF\s*$/s', $this->pdfContent, $matches)) {
            throw new PdfException('Cannot find startxref in PDF');
        }

        $this->xrefPosition = (int)$matches[1];

        // Check if it's a cross-reference stream or table
        $xrefData = substr($this->pdfContent, $this->xrefPosition, 1024);

        if (str_starts_with($xrefData, 'xref')) {
            $this->parseXrefTable($this->xrefPosition);
        } else {
            // Cross-reference stream (PDF 1.5+)
            $this->parseXrefStream($this->xrefPosition);
        }
    }

    /**
     * Parse traditional xref table
     *
     * @param int $position Position of xref keyword
     */
    protected function parseXrefTable(int $position): void
    {
        $data = substr($this->pdfContent, $position);

        // Skip "xref" keyword
        $data = substr($data, 4);

        // Parse xref subsections
        while (preg_match('/^\s*(\d+)\s+(\d+)\s*\n/', $data, $matches)) {
            $startObj = (int)$matches[1];
            $count = (int)$matches[2];
            $data = substr($data, strlen($matches[0]));

            for ($i = 0; $i < $count; $i++) {
                if (preg_match('/^(\d{10})\s+(\d{5})\s+([fn])\s*[\r\n]+/', $data, $entry)) {
                    $objNum = $startObj + $i;
                    $this->xref[$objNum] = [
                        'offset' => (int)$entry[1],
                        'generation' => (int)$entry[2],
                        'free' => ($entry[3] === 'f'),
                    ];

                    if (!$this->xref[$objNum]['free'] && $objNum > $this->maxObjectNumber) {
                        $this->maxObjectNumber = $objNum;
                    }

                    $data = substr($data, strlen($entry[0]));
                }
            }

            // Check for another subsection or trailer
            if (str_starts_with(trim($data), 'trailer')) {
                break;
            }
        }
    }

    /**
     * Parse cross-reference stream (PDF 1.5+)
     *
     * @param int $position Position of xref stream object
     */
    protected function parseXrefStream(int $position): void
    {
        // Extract the stream object
        $data = substr($this->pdfContent, $position);

        if (!preg_match('/^(\d+)\s+\d+\s+obj/', $data, $matches)) {
            throw new PdfException('Invalid xref stream object');
        }

        // For now, fall back to searching for objects directly
        $this->parseObjectsDirectly();
    }

    /**
     * Parse objects directly (fallback method)
     */
    protected function parseObjectsDirectly(): void
    {
        preg_match_all('/(\d+)\s+\d+\s+obj/', $this->pdfContent, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[1] as $match) {
            $objNum = (int)$match[0];
            $offset = (int)$match[1];

            $this->xref[$objNum] = [
                'offset' => $offset,
                'generation' => 0,
                'free' => false,
            ];

            if ($objNum > $this->maxObjectNumber) {
                $this->maxObjectNumber = $objNum;
            }
        }
    }

    /**
     * Parse the trailer dictionary
     */
    protected function parseTrailer(): void
    {
        if (preg_match('/trailer\s*<<(.+?)>>/s', $this->pdfContent, $matches)) {
            $trailerDict = $matches[1];

            // Parse Size
            if (preg_match('/\/Size\s+(\d+)/', $trailerDict, $m)) {
                $this->trailer['Size'] = (int)$m[1];
            }

            // Parse Root
            if (preg_match('/\/Root\s+(\d+\s+\d+\s+R)/', $trailerDict, $m)) {
                $this->trailer['Root'] = $m[1];
            }

            // Parse Info
            if (preg_match('/\/Info\s+(\d+\s+\d+\s+R)/', $trailerDict, $m)) {
                $this->trailer['Info'] = $m[1];
            }

            // Parse ID
            if (preg_match('/\/ID\s*\[\s*<([^>]+)>\s*<([^>]+)>\s*\]/', $trailerDict, $m)) {
                $this->trailer['ID'] = [$m[1], $m[2]];
            }

            // Parse Prev
            if (preg_match('/\/Prev\s+(\d+)/', $trailerDict, $m)) {
                $this->trailer['Prev'] = (int)$m[1];
            }
        }
    }

    /**
     * Parse page objects
     */
    protected function parsePages(): void
    {
        // Find Pages object
        if (!preg_match('/\/Type\s*\/Pages\s.*?\/Kids\s*\[([^\]]+)\]/s', $this->pdfContent, $matches)) {
            return;
        }

        $kids = $matches[1];
        preg_match_all('/(\d+)\s+\d+\s+R/', $kids, $pageRefs);

        foreach ($pageRefs[0] as $index => $ref) {
            $objNum = (int)$pageRefs[1][$index];
            $this->pages[$index + 1] = [
                'objNum' => $objNum,
                'objRef' => $ref,
            ];
        }
    }

    /**
     * Parse existing signature fields
     */
    protected function parseSignatureFields(): void
    {
        // Find signature fields (FT /Sig)
        preg_match_all(
            '/(\d+)\s+\d+\s+obj\s*<<[^>]*\/FT\s*\/Sig[^>]*>>/s',
            $this->pdfContent,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $index => $match) {
            $objNum = (int)$match[1];
            $fieldDict = $match[0];

            // Check if signed (has /V reference)
            $signed = (bool)preg_match('/\/V\s+\d+\s+\d+\s+R/', $fieldDict);

            // Extract field name
            $name = 'Signature' . ($index + 1);
            if (preg_match('/\/T\s*\(([^)]+)\)/', $fieldDict, $m)) {
                $name = $m[1];
            } elseif (preg_match('/\/T\s*<([^>]+)>/', $fieldDict, $m)) {
                $name = hex2bin($m[1]) ?: $name;
            }

            // Extract rect
            $rect = [0.0, 0.0, 0.0, 0.0];
            if (preg_match('/\/Rect\s*\[\s*([\d.\s]+)\s*\]/', $fieldDict, $m)) {
                $rectValues = preg_split('/\s+/', trim($m[1]));
                if ($rectValues !== false && count($rectValues) >= 4) {
                    $rect = array_map('floatval', array_slice($rectValues, 0, 4));
                }
            }

            // Extract page reference
            $page = 1;
            if (preg_match('/\/P\s+(\d+)\s+\d+\s+R/', $fieldDict, $m)) {
                // Find page number from object number
                foreach ($this->pages as $pageNum => $pageData) {
                    if ($pageData['objNum'] === (int)$m[1]) {
                        $page = $pageNum;
                        break;
                    }
                }
            }

            $this->signatureFields[] = [
                'objNum' => $objNum,
                'name' => $name,
                'page' => $page,
                'rect' => $rect,
                'signed' => $signed,
            ];
        }
    }

    /**
     * Get the PDF content
     *
     * @return string PDF content
     */
    public function getContent(): string
    {
        return $this->pdfContent;
    }

    /**
     * Get PDF version
     *
     * @return string PDF version
     */
    public function getVersion(): string
    {
        return $this->pdfVersion;
    }

    /**
     * Get the cross-reference table
     *
     * @return array<int, XrefEntry> Cross-reference table
     */
    public function getXref(): array
    {
        return $this->xref;
    }

    /**
     * Get the trailer dictionary
     *
     * @return Trailer Trailer dictionary
     */
    public function getTrailer(): array
    {
        return $this->trailer;
    }

    /**
     * Get the position of the last xref
     *
     * @return int Xref position
     */
    public function getXrefPosition(): int
    {
        return $this->xrefPosition;
    }

    /**
     * Get the highest object number
     *
     * @return int Maximum object number
     */
    public function getMaxObjectNumber(): int
    {
        return $this->maxObjectNumber;
    }

    /**
     * Get all signature fields
     *
     * @return array<int, array{
     *     'objNum': int,
     *     'name': string,
     *     'page': int,
     *     'rect': array<float>,
     *     'signed': bool,
     * }> Signature fields
     */
    public function getSignatureFields(): array
    {
        return $this->signatureFields;
    }

    /**
     * Get unsigned signature fields
     *
     * @return array<int, array{
     *     'objNum': int,
     *     'name': string,
     *     'page': int,
     *     'rect': array<float>,
     *     'signed': bool,
     * }> Unsigned signature fields
     */
    public function getUnsignedFields(): array
    {
        return array_filter(
            $this->signatureFields,
            fn($field) => !$field['signed']
        );
    }

    /**
     * Get pages
     *
     * @return array<int, array{
     *     'objNum': int,
     *     'objRef': string,
     * }> Pages
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * Get the length of the PDF content
     *
     * @return int Content length
     */
    public function getContentLength(): int
    {
        return strlen($this->pdfContent);
    }
}
