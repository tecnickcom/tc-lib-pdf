<?php

declare(strict_types=1);

/**
 * PageResolver.php
 *
 * @since     2026-05-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Import;

/**
 * Com\Tecnick\Pdf\Import\PageResolver
 *
 * Traverses the PDF page tree and returns the effective page dictionary
 * with all inherited attributes (MediaBox, CropBox, Rotate, Resources …)
 * resolved for a 1-based page number.
 *
 * @since     2026-05-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type RawObjectArray from \Com\Tecnick\Pdf\Parser\Process\RawObject
 *
 * @phpstan-type PageBox array{0: float, 1: float, 2: float, 3: float}
 *
 * @phpstan-type ResolvedPage array{
 *     'dict':       array<string, mixed>,
 *     'mediaBox':   PageBox,
 *     'cropBox':    PageBox,
 *     'bleedBox':   PageBox,
 *     'trimBox':    PageBox,
 *     'artBox':     PageBox,
 *     'rotate':     int,
 *     'resources':  array<string, mixed>,
 * }
 */
class PageResolver
{
    /**
     * Inheritable page attributes.
     *
     * @var array<int, string>
     */
    private const INHERITABLE = ['MediaBox', 'CropBox', 'BleedBox', 'TrimBox', 'ArtBox', 'Rotate', 'Resources'];

    /**
     * Resolve the effective page dictionary for the given 1-based page number.
     *
     * @param SourceDocument $src      Parsed source document.
     * @param int            $pageNum  1-based page number to resolve.
     *
     * @phpstan-return ResolvedPage
     * @return array<string, mixed>
     *
     * @throws ImportPageOutOfRangeException If the page number is out of range.
     * @throws ImportCorruptedSourceException If the page tree is malformed.
     */
    public function resolve(SourceDocument $src, int $pageNum): array
    {
        if ($pageNum < 1) {
            throw new ImportPageOutOfRangeException('Page number must be >= 1, got: ' . $pageNum);
        }

        $trailer = $src->getTrailer();
        $rootRef = SourceDocument::refToKey($trailer['root']);
        $rootObj = $src->getObject($rootRef);
        $rootDict = $this->objectToDict($rootObj);

        if (!isset($rootDict['Pages'])) {
            throw new ImportCorruptedSourceException('PDF /Root is missing /Pages entry.');
        }

        $pagesRef = SourceDocument::refToKey(\is_string($rootDict['Pages']) ? $rootDict['Pages'] : '');
        $pagesObj = $src->getObject($pagesRef);
        $pagesDict = $this->objectToDict($pagesObj);

        $inherited = $this->extractInheritable($pagesDict);
        $remaining = $pageNum;
        $pageDict = $this->walkTree($src, $pagesDict, $inherited, $remaining);

        if ($pageDict === null) {
            throw new ImportPageOutOfRangeException('Page ' . $pageNum . ' not found; document has fewer pages.');
        }

        return $this->buildResolved($pageDict, $src);
    }

    /**
     * Recursively walk the page tree to find the $remaining-th Page node.
     *
     * @param SourceDocument       $src       Source document.
     * @param array<string, mixed> $nodeDict  Current Pages or Page dictionary.
     * @param array<string, mixed> $inherited Inherited attributes from parent.
     * @param int                  $remaining Remaining pages to skip (decremented).
     *
     * @return array<string, mixed>|null Resolved page dict or null if not found in this subtree.
     *
     * @throws ImportCorruptedSourceException On malformed tree.
     */
    private function walkTree(SourceDocument $src, array $nodeDict, array $inherited, int &$remaining): ?array
    {
        $nodeType = '';
        if (isset($nodeDict['Type']) && \is_string($nodeDict['Type'])) {
            $nodeType = $nodeDict['Type'];
        }

        // merge inherited from parent into this node
        $merged = \array_merge($inherited, $this->extractInheritable($nodeDict));

        if ($nodeType === 'Page') {
            --$remaining;
            if ($remaining === 0) {
                return \array_merge($merged, $nodeDict);
            }

            return null;
        }

        if ($nodeType !== 'Pages') {
            throw new ImportCorruptedSourceException('Unexpected page tree node type: ' . $nodeType);
        }

        if (!isset($nodeDict['Kids']) || !\is_array($nodeDict['Kids'])) {
            throw new ImportCorruptedSourceException('/Pages node is missing /Kids array.');
        }

        $kids = \array_values($nodeDict['Kids']);
        $kidCount = \count($kids);
        for ($kidIdx = 0; $kidIdx < $kidCount; ++$kidIdx) {
            $kidRefSlice = \array_slice($kids, $kidIdx, 1);
            if (\count($kidRefSlice) !== 1 || !\is_string($kidRefSlice[0])) {
                continue;
            }

            $kidRef = $kidRefSlice[0];

            $kidKey = SourceDocument::refToKey($kidRef);
            $kidObj = $src->getObject($kidKey);
            $kidDict = $this->objectToDict($kidObj);
            $result = $this->walkTree($src, $kidDict, $merged, $remaining);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Build a ResolvedPage from a raw merged page dictionary.
     *
     * @param array<string, mixed> $dict Merged page dictionary.
     *
     * @phpstan-return ResolvedPage
     * @return array<string, mixed>
     *
     * @throws ImportCorruptedSourceException If page boxes or resources are malformed.
     */
    private function buildResolved(array $dict, SourceDocument $src): array
    {
        $mediaBox = $this->parseBox($dict['MediaBox'] ?? null);

        if ($mediaBox === null) {
            throw new ImportCorruptedSourceException('Page is missing /MediaBox.');
        }

        $cropBox = $this->parseBox($dict['CropBox'] ?? null) ?? $mediaBox;
        $bleedBox = $this->parseBox($dict['BleedBox'] ?? null) ?? $cropBox;
        $trimBox = $this->parseBox($dict['TrimBox'] ?? null) ?? $cropBox;
        $artBox = $this->parseBox($dict['ArtBox'] ?? null) ?? $cropBox;
        if (isset($dict['Rotate']) && \is_int($dict['Rotate'])) {
            $rotate = $dict['Rotate'];
        } elseif (isset($dict['Rotate']) && \is_numeric($dict['Rotate'])) {
            $rotate = (int) $dict['Rotate'];
        } else {
            $rotate = 0;
        }

        $resources = [];
        if (isset($dict['Resources']) && (\is_array($dict['Resources']) || \is_string($dict['Resources']))) {
            $resources = $dict['Resources'];
        }

        // If Resources is an indirect reference string, resolve it now.
        if (\is_string($resources) && $resources !== '') {
            $resKey = SourceDocument::refToKey($resources);
            $resObj = $src->findObject($resKey);
            $resources = $resObj !== null ? $this->objectToDict($resObj) : [];
        }

        /** @var array<string, mixed> $resources */
        $resources = \is_array($resources) ? $resources : [];

        return [
            'dict' => $dict,
            'mediaBox' => $mediaBox,
            'cropBox' => $cropBox,
            'bleedBox' => $bleedBox,
            'trimBox' => $trimBox,
            'artBox' => $artBox,
            'rotate' => $rotate,
            'resources' => $resources,
        ];
    }

    /**
     * Extract inheritable attributes from a dictionary.
     *
     * @param array<string, mixed> $dict Node dictionary.
     *
     * @return array<string, mixed>
     */
    private function extractInheritable(array $dict): array
    {
        $out = [];
        foreach (self::INHERITABLE as $key) {
            if (!isset($dict[$key])) {
                continue;
            }

            $out[$key] = $dict[$key];
        }

        return $out;
    }

    /**
     * Parse a PDF box array (4 numeric values) into a typed float tuple.
     *
     * @param mixed $raw Raw value from parsed dictionary.
     *
     * @phpstan-return PageBox|null
     * @return array<int, float>|null
     */
    private function parseBox(mixed $raw): ?array
    {
        if (!\is_array($raw) || \count($raw) < 4) {
            return null;
        }

        $vals = \array_values($raw);
        if (
            !\array_key_exists(0, $vals)
            || !\array_key_exists(1, $vals)
            || !\array_key_exists(2, $vals)
            || !\array_key_exists(3, $vals)
        ) {
            return null;
        }

        return [
            \is_numeric($vals[0]) ? (float) $vals[0] : 0.0,
            \is_numeric($vals[1]) ? (float) $vals[1] : 0.0,
            \is_numeric($vals[2]) ? (float) $vals[2] : 0.0,
            \is_numeric($vals[3]) ? (float) $vals[3] : 0.0,
        ];
    }

    /**
     * Convert a raw parsed object array to a dictionary (key => scalar/array).
     *
     * The first element of the object array whose type is "<<" (dictionary) is extracted.
     * All values that are indirect references (type "objref") are left as their raw
     * string values for lazy resolution by callers.
     *
     * @param array<int, mixed> $objData Raw object data from the parser.
     *
     * @return array<string, mixed>
     *
     * @throws ImportCorruptedSourceException If no dictionary element is found.
     */
    private function objectToDict(array $objData): array
    {
        $elements = \array_values($objData);
        $elmCount = \count($elements);
        for ($elmIdx = 0; $elmIdx < $elmCount; ++$elmIdx) {
            $elementSlice = \array_slice($elements, $elmIdx, 1);
            if (\count($elementSlice) !== 1 || !\is_array($elementSlice[0])) {
                continue;
            }

            if (($elementSlice[0][0] ?? null) === '<<' && \is_array($elementSlice[0][1] ?? null)) {
                return $this->parseDictArray(\array_values($elementSlice[0][1]));
            }
        }

        throw new ImportCorruptedSourceException('Expected dictionary object but none found.');
    }

    /**
     * Recursively convert a raw parser dictionary array into a PHP associative array.
     * Each entry in the raw array is a pair [key_element, value_element].
     *
     * @param array<int, mixed> $raw Raw dictionary pairs from the parser.
     *
     * @return array<string, mixed>
     */
    private function parseDictArray(array $raw): array
    {
        $dict = [];
        $pairs = \array_values($raw);
        $cnt = \count($pairs);
        for ($idx = 0; $idx < ($cnt - 1); $idx += 2) {
            $pair = \array_slice($pairs, $idx, 2);
            if (\count($pair) < 2) {
                continue;
            }

            if (!\is_array($pair[0]) || ($pair[0][0] ?? null) !== '/') {
                continue;
            }

            if (!\array_key_exists(1, $pair[0]) || !\is_string($pair[0][1])) {
                continue;
            }

            $key = \ltrim($pair[0][1], '/');
            $dict[$key] = $this->parseValue($pair[1]);
        }

        return $dict;
    }

    /**
     * Convert a single raw parser value to a PHP scalar, array, or reference string.
     *
     * @param mixed $raw Raw element from the parser.
     *
     * @return mixed
     */
    private function parseValue(mixed $raw): mixed
    {
        if (!\is_array($raw)) {
            return $raw;
        }

        if (!\array_key_exists(0, $raw)) {
            return null;
        }

        $type = \is_string($raw[0]) ? $raw[0] : '';

        if ($type === '<<' && \array_key_exists(1, $raw) && \is_array($raw[1])) {
            return $this->parseDictArray(\array_values($raw[1]));
        }

        if ($type === '[' && \array_key_exists(1, $raw) && \is_array($raw[1])) {
            return \array_map($this->parseValue(...), $raw[1]);
        }

        if ($type === 'objref') {
            // Return the raw reference string; callers resolve via SourceDocument::refToKey()
            return \array_key_exists(1, $raw) && \is_string($raw[1]) ? $raw[1] : '';
        }

        if (\in_array($type, ['/', 'string', 'numeric', 'boolean', 'null'], true)) {
            return $raw[1] ?? null;
        }

        // Fallback: return scalar value
        return $raw[1] ?? null;
    }
}
