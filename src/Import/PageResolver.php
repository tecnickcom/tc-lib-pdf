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
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
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
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
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
     * Hard ceiling on page tree nodes visited in a single walk.
     * Far above any legitimate document; defense in depth on top of the
     * duplicate-reference guard, which already bounds every walk by the
     * number of distinct objects in the source file.
     */
    public const MAX_PAGE_TREE_NODES = 1_000_000;

    /**
     * Resolve the effective page dictionary for the given 1-based page number.
     *
     * Convenience wrapper that builds the page index and resolves from it.
     * Callers importing many pages from the same source should build the
     * index once with buildPageIndex() and use resolveFromIndex(), so the
     * page tree is walked only once per source.
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

        return $this->resolveFromIndex($src, $this->buildPageIndex($src), $pageNum);
    }

    /**
     * Resolve a page against a page index previously built by buildPageIndex().
     *
     * @param SourceDocument                   $src     Parsed source document the index was built from.
     * @param array<int, array<string, mixed>> $index   Flattened page index in document order.
     * @param int                              $pageNum 1-based page number to resolve.
     *
     * @phpstan-return ResolvedPage
     * @return array<string, mixed>
     *
     * @throws ImportPageOutOfRangeException If the page number is out of range.
     * @throws ImportCorruptedSourceException If page boxes or resources are malformed.
     */
    public function resolveFromIndex(SourceDocument $src, array $index, int $pageNum): array
    {
        if ($pageNum < 1) {
            throw new ImportPageOutOfRangeException('Page number must be >= 1, got: ' . $pageNum);
        }

        $pageDict = $index[$pageNum - 1] ?? null;
        if ($pageDict === null) {
            throw new ImportPageOutOfRangeException('Page ' . $pageNum . ' not found; document has fewer pages.');
        }

        return $this->buildResolved($pageDict, $src);
    }

    /**
     * Build the flattened page index: one effective page dictionary (the page's
     * own entries merged over the attributes inherited from its ancestor /Pages
     * nodes) per reachable page, in document order.
     *
     * The walk is iterative, visits every node exactly once (a global visited
     * set rejects duplicate and cyclic references) and is bounded by $maxNodes,
     * so a hostile page tree can neither recurse nor amplify the traversal.
     * The declared /Count entry is intentionally ignored: it is under the
     * control of whoever produced the source file and must never size an
     * allocation or bound a loop.
     *
     * @param SourceDocument $src      Parsed source document.
     * @param int            $maxNodes Maximum number of tree nodes to visit.
     *
     * @return array<int, array<string, mixed>> Effective page dictionaries in document order.
     *
     * @throws ImportCorruptedSourceException If the page tree is malformed, contains
     *                                        duplicate or cyclic references, or exceeds
     *                                        the node budget.
     */
    public function buildPageIndex(SourceDocument $src, int $maxNodes = self::MAX_PAGE_TREE_NODES): array
    {
        $trailer = $src->getTrailer();
        $rootRef = SourceDocument::refToKey($trailer['root']);
        $rootObj = $src->getObject($rootRef);
        $rootDict = $this->objectToDict($rootObj);

        if (!isset($rootDict['Pages'])) {
            throw new ImportCorruptedSourceException('PDF /Root is missing /Pages entry.');
        }

        /** @var array<int, array{0: string, 1: array<string, mixed>}> $stack */
        $stack = [[SourceDocument::refToKey(\is_string($rootDict['Pages']) ? $rootDict['Pages'] : ''), []]];

        /** @var array<string, bool> $visited */
        $visited = [];

        /** @var array<int, array<string, mixed>> $index */
        $index = [];
        $nodes = 0;
        while ($stack !== []) {
            [$ref, $inherited] = \array_pop($stack);
            if (isset($visited[$ref])) {
                throw new ImportCorruptedSourceException('Duplicate or cyclic reference in page tree at node: ' . $ref);
            }

            $visited[$ref] = true;
            ++$nodes;
            if ($nodes > $maxNodes) {
                throw new ImportCorruptedSourceException('Page tree exceeds the maximum node budget: ' . $maxNodes);
            }

            $nodeDict = $this->objectToDict($src->getObject($ref));
            $nodeType = isset($nodeDict['Type']) && \is_string($nodeDict['Type']) ? $nodeDict['Type'] : '';
            if ($nodeType === 'Page') {
                $index[] = $this->effectivePageDict($inherited, $nodeDict);
                continue;
            }

            if ($nodeType !== 'Pages') {
                throw new ImportCorruptedSourceException('Unexpected page tree node type: ' . $nodeType);
            }

            if (!isset($nodeDict['Kids']) || !\is_array($nodeDict['Kids'])) {
                throw new ImportCorruptedSourceException('/Pages node is missing /Kids array.');
            }

            $merged = $this->mergeInherited($inherited, $nodeDict);

            // Push the kids in reverse so the LIFO stack pops them in document order.
            /** @var mixed $kid */
            foreach (\array_reverse(\array_values($nodeDict['Kids'])) as $kid) {
                if (!\is_string($kid)) {
                    continue;
                }

                $stack[] = [SourceDocument::refToKey($kid), $merged];
            }
        }

        return $index;
    }

    /**
     * Count the pages actually reachable through the /Kids page tree.
     *
     * The declared /Count entry of the /Pages dictionary is intentionally
     * ignored: it is under the control of whoever produced the source file
     * and must never size an allocation or bound a loop. The walk applies
     * the same acceptance rules as resolve(), so both methods always agree
     * on which pages are reachable.
     *
     * @param SourceDocument $src      Parsed source document.
     * @param int            $maxNodes Maximum number of tree nodes to visit.
     *
     * @return int Number of reachable pages.
     *
     * @throws ImportCorruptedSourceException If the page tree is malformed, contains
     *                                        duplicate or cyclic references, or exceeds
     *                                        the node budget.
     */
    public function countPages(SourceDocument $src, int $maxNodes = self::MAX_PAGE_TREE_NODES): int
    {
        return \count($this->buildPageIndex($src, $maxNodes));
    }

    /**
     * Merge a node's inheritable attributes over the attributes inherited
     * from its ancestors, deep-merging Resources dictionaries.
     *
     * @param array<string, mixed> $inherited Attributes inherited from ancestor nodes.
     * @param array<string, mixed> $nodeDict  Current node dictionary.
     *
     * @return array<string, mixed>
     */
    private function mergeInherited(array $inherited, array $nodeDict): array
    {
        $merged = \array_merge($inherited, $this->extractInheritable($nodeDict));
        if (
            isset($inherited['Resources'], $nodeDict['Resources'])
            && \is_array($inherited['Resources'])
            && \is_array($nodeDict['Resources'])
        ) {
            $merged['Resources'] = \array_replace_recursive($inherited['Resources'], $nodeDict['Resources']);
        }

        return $merged;
    }

    /**
     * Build the effective dictionary for a Page leaf: the page's own entries
     * win over inherited attributes, with Resources dictionaries deep-merged.
     *
     * @param array<string, mixed> $inherited Attributes inherited from ancestor nodes.
     * @param array<string, mixed> $pageDict  Page leaf dictionary.
     *
     * @return array<string, mixed>
     */
    private function effectivePageDict(array $inherited, array $pageDict): array
    {
        $merged = $this->mergeInherited($inherited, $pageDict);
        $effective = \array_merge($merged, $pageDict);
        if (
            isset($merged['Resources'], $pageDict['Resources'])
            && \is_array($merged['Resources'])
            && \is_array($pageDict['Resources'])
        ) {
            $effective['Resources'] = \array_replace_recursive($merged['Resources'], $pageDict['Resources']);
        }

        return $effective;
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
        $mediaBox = $this->resolveBox($dict['MediaBox'] ?? null, $src);

        if ($mediaBox === null) {
            throw new ImportCorruptedSourceException('Page is missing /MediaBox.');
        }

        $cropBox = $this->resolveBox($dict['CropBox'] ?? null, $src) ?? $mediaBox;
        $bleedBox = $this->resolveBox($dict['BleedBox'] ?? null, $src) ?? $cropBox;
        $trimBox = $this->resolveBox($dict['TrimBox'] ?? null, $src) ?? $cropBox;
        $artBox = $this->resolveBox($dict['ArtBox'] ?? null, $src) ?? $cropBox;
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
     * Resolve a page box that may be inline or an indirect reference.
     *
     * @param mixed          $raw Raw value from page dictionary.
     * @param SourceDocument $src Source document.
     *
     * @phpstan-return PageBox|null
     * @return array<int, float>|null
     */
    private function resolveBox(mixed $raw, SourceDocument $src): ?array
    {
        $box = $this->parseBox($raw);
        if ($box !== null) {
            return $box;
        }

        if (!\is_string($raw) || $raw === '') {
            return null;
        }

        try {
            $ref = SourceDocument::refToKey($raw);
        } catch (ImportCorruptedSourceException) {
            return null;
        }

        $obj = $src->findObject($ref);
        if ($obj === null) {
            return null;
        }

        foreach (\array_values($obj) as $element) {
            if ($element[0] !== '[' || !\is_array($element[1])) {
                continue;
            }

            $values = \array_map($this->parseValue(...), \array_values($element[1]));
            return $this->parseBox($values);
        }

        return null;
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
