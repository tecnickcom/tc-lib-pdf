<?php

declare(strict_types=1);

/**
 * ResourceCloner.php
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
 * Com\Tecnick\Pdf\Import\ResourceCloner
 *
 * Deep-copies objects from the source document into the destination PDF
 * using an ObjectMap for reference remapping. Returns serialized PDF object bytes.
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
 * @phpstan-import-type ResolvedPage from PageResolver
 */
class ResourceCloner
{
    /**
     * Object number counter reference (shared with the output document).
     *
     * @var int
     */
    private int $pon;

    /**
     * Constructor.
     *
     * @param int $pon Current PDF object number (passed by value; use allocate() to update externally).
     */
    public function __construct(int $pon)
    {
        $this->pon = $pon;
    }

    /**
     * Return the current object number counter (after any allocations made during cloning).
     *
     * @return int
     */
    public function getPon(): int
    {
        return $this->pon;
    }

    /**
     * Extract the raw bytes of the merged content stream for a page.
     *
     * Phase 1: single content stream only.
     * Phase 2 (future): array of /Contents refs decoded and concatenated.
     *
     * @param array<string, mixed> $pageDict Effective page dictionary.
     * @param SourceDocument       $src      Source document.
     *
     * @return array{bytes: string, filter: string, length: int}
     *
     * @throws ImportCorruptedSourceException If the content stream cannot be extracted.
     * @throws ImportUnsupportedFeatureException If /Contents is missing.
     */
    public function getContentStream(array $pageDict, SourceDocument $src): array
    {
        if (!isset($pageDict['Contents'])) {
            // Page has no content — return empty stream.
            return ['bytes' => '', 'filter' => '', 'length' => 0];
        }

        // Single reference (string like "5 0 R" or "5_0").
        if (\is_string($pageDict['Contents'])) {
            return $this->extractSingleStream(SourceDocument::refToKey($pageDict['Contents']), $src);
        }

        // Array of references — for Phase 1 we require exactly one element.
        if (\is_array($pageDict['Contents'])) {
            $contents = \array_values($pageDict['Contents']);
            if (\count($contents) === 1) {
                if (!\is_string($contents[0])) {
                    throw new ImportCorruptedSourceException('Invalid /Contents reference type.');
                }

                return $this->extractSingleStream(SourceDocument::refToKey($contents[0]), $src);
            }

            // Multiple streams: decode and concatenate.
            return $this->concatenateStreams($contents, $src);
        }

        throw new ImportCorruptedSourceException('Unexpected /Contents value type.');
    }

    /**
     * Walk the resource dictionary and enqueue all indirect objects for cloning.
     * Returns a serialized PDF resource dictionary string with remapped object numbers.
     *
     * @param array<string, mixed> $resources Resource dictionary.
     * @param SourceDocument       $src       Source document.
     * @param ObjectMap            $map       Object map for reference remapping.
     *
     * @return string Serialized PDF resource dictionary, e.g. "<< /Font << /F1 7 0 R >> >>".
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     */
    public function cloneResources(array $resources, SourceDocument $src, ObjectMap $map): string
    {
        if ($resources === []) {
            return '';
        }

        $out = '<<';
        $out .= ' /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]';

        foreach (\array_keys($resources) as $resType) {
            if ($resType === 'ProcSet') {
                continue; // already emitted above
            }

            $out .= ' /' . $resType . ' ';
            $out .= $this->cloneResourceEntry($resources[$resType], $src, $map);
        }

        $out .= ' >>';
        return $out;
    }

    /**
     * Serialize and clone one top-level resource type entry.
     *
     * @param mixed                $resVal  Raw value.
     * @param SourceDocument       $src     Source document.
     * @param ObjectMap            $map     Object map.
     *
     * @return string Serialized PDF value for this resource entry.
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     */
    private function cloneResourceEntry(mixed $resVal, SourceDocument $src, ObjectMap $map): string
    {
        // Resource subdicts (Font, XObject, ExtGState, ColorSpace, Pattern, Shading) are dicts of name->ref.
        if (\is_array($resVal)) {
            $out = '<< ';
            foreach (\array_keys($resVal) as $name) {
                if (!\is_string($name) || !isset($resVal[$name]) || !\is_string($resVal[$name])) {
                    // Skip non-string (nested array) values.
                    continue;
                }

                if ($this->isIndirectRef($resVal[$name])) {
                    $destNum = $this->enqueueObject(SourceDocument::refToKey($resVal[$name]), $src, $map);
                    $out .= '/' . $name . ' ' . $destNum . ' 0 R ';
                } else {
                    // Inline scalar value.
                    $out .= '/' . $name . ' ' . $resVal[$name] . ' ';
                }
            }

            $out .= '>>';
            return $out;
        }

        if (\is_string($resVal)) {
            if ($this->isIndirectRef($resVal)) {
                $destNum = $this->enqueueObject(SourceDocument::refToKey($resVal), $src, $map);
                return $destNum . ' 0 R';
            }

            return $resVal;
        }

        return 'null';
    }

    /**
     * @param ObjectMap      $map    Object map.
     *
     * @return int Allocated destination object number.
     *
     * @throws ImportException
     */
    public function enqueueObject(string $srcRef, SourceDocument $src, ObjectMap $map): int
    {
        if ($map->has($srcRef)) {
            return $map->get($srcRef);
        }

        if ($map->isInProgress($srcRef)) {
            // Cycle: return already-allocated number.
            return $map->get($srcRef);
        }

        $destNum = $map->allocate($srcRef, $this->pon);
        $objData = $src->findObject($srcRef);
        if ($objData === null) {
            // Undefined reference: emit a null object.
            $map->enqueue($srcRef, $destNum . ' 0 obj null endobj' . "\n");
            return $destNum;
        }

        $serialized = $this->serializeObject($destNum, $objData, $src, $map);
        $map->enqueue($srcRef, $serialized);
        return $destNum;
    }

    /**
     * Serialize a raw parser object as a PDF object with a new destination object number.
     * All indirect references inside the object are remapped via ObjectMap.
     *
     * @param int               $destNum  New destination object number.
     * @param array<int, mixed> $objData  Raw parsed object data.
     * @param SourceDocument    $src      Source document.
     * @param ObjectMap         $map      Object map.
     *
     * @return string Serialized PDF object bytes ending with "endobj\n".
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     */
    private function serializeObject(int $destNum, array $objData, SourceDocument $src, ObjectMap $map): string
    {
        $dictPart = '';
        $streamBytes = null;
        /** @var array<string, string> $streamDict */
        $streamDict = [];

        $elements = \array_values($objData);
        $elmCount = \count($elements);
        for ($elmIdx = 0; $elmIdx < $elmCount; ++$elmIdx) {
            $elementSlice = \array_slice($elements, $elmIdx, 1);
            if (\count($elementSlice) !== 1 || !\is_array($elementSlice[0])) {
                continue;
            }

            if (($elementSlice[0][0] ?? null) === '<<' && \is_array($elementSlice[0][1] ?? null)) {
                $dictPart = $this->serializeDictArray(\array_values($elementSlice[0][1]), $src, $map, $streamDict);
            } elseif (($elementSlice[0][0] ?? null) === 'stream' && \is_string($elementSlice[0][1] ?? null)) {
                $streamBytes = $elementSlice[0][1];

                // Decoded stream is in element[3] if present; we use raw bytes.
            }
        }

        $out = $destNum . ' 0 obj' . "\n";

        if ($streamBytes !== null) {
            // Stream object: emit with original filter preserved.
            $filterEntry = '';
            if (isset($streamDict['Filter'])) {
                $filterEntry = ' /Filter ' . $streamDict['Filter'];
            }

            $out .=
                '<<'
                . $dictPart
                . $filterEntry
                . ' /Length '
                . \strlen($streamBytes)
                . ' >>'
                . "\nstream\n"
                . $streamBytes
                . "\nendstream\n";
        } elseif ($dictPart !== '') {
            $out .= '<<' . $dictPart . ">>\n";
        } else {
            // Scalar or array value.
            $out .= $this->serializeFirstValue($objData, $src, $map) . "\n";
        }

        $out .= 'endobj' . "\n";
        return $out;
    }

    /**
     * Serialize a parsed dictionary array into a PDF dict string, remapping object references.
     *
     * @param array<int, mixed>    $raw        Raw dictionary pairs.
     * @param SourceDocument       $src        Source document.
     * @param ObjectMap            $map        Object map.
     * @param array<string, string> $streamDict Populated with Length/Filter entries for stream objects.
     *
     * @return string PDF dict content (without outer << >>).
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     */
    private function serializeDictArray(array $raw, SourceDocument $src, ObjectMap $map, array &$streamDict): string
    {
        $out = '';
        $pairs = \array_values($raw);
        $cnt = \count($pairs);
        for ($idx = 0; $idx < ($cnt - 1); $idx += 2) {
            $pair = \array_slice($pairs, $idx, 2);
            if (\count($pair) < 2 || !\is_array($pair[0]) || ($pair[0][0] ?? null) !== '/') {
                continue;
            }

            if (!\array_key_exists(1, $pair[0]) || !\is_string($pair[0][1])) {
                continue;
            }

            $key = \ltrim($pair[0][1], '/');
            if ($key === 'Length') {
                continue;
            }

            $serializedVal = $this->serializeValue($pair[1], $src, $map);

            if ($key === 'Filter') {
                $streamDict['Filter'] = $serializedVal;
                continue;
            }

            $out .= ' /' . $key . ' ' . $serializedVal;
            $streamDict[$key] = $serializedVal;
        }

        return $out;
    }

    /**
     * Serialize a single raw parser value to a PDF token string.
     *
     * @param mixed          $raw Raw element.
     * @param SourceDocument $src Source document.
     * @param ObjectMap      $map Object map.
     *
     * @return string PDF token.
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     */
    private function serializeValue(mixed $raw, SourceDocument $src, ObjectMap $map): string
    {
        if (!\is_array($raw)) {
            return \is_scalar($raw) ? (string) $raw : '';
        }

        $type = \is_string($raw[0] ?? null) ? $raw[0] : '';

        if ($type === 'objref' && \is_string($raw[1] ?? null)) {
            $destNum = $this->enqueueObject(SourceDocument::refToKey($raw[1]), $src, $map);
            return $destNum . ' 0 R';
        }

        if ($type === '<<' && \is_array($raw[1] ?? null)) {
            /** @var array<string, string> $unused */
            $unused = [];
            $inner = $this->serializeDictArray(\array_values($raw[1]), $src, $map, $unused);
            return '<<' . $inner . '>>';
        }

        if ($type === '[' && \is_array($raw[1] ?? null)) {
            $parts = \array_map(fn($item) => $this->serializeValue($item, $src, $map), $raw[1]);
            return '[' . \implode(' ', $parts) . ']';
        }

        if ($type === '/') {
            return '/' . (\is_string($raw[1] ?? null) ? $raw[1] : '');
        }

        if ($type === 'string') {
            return '(' . \addslashes(\is_string($raw[1] ?? null) ? $raw[1] : '') . ')';
        }

        if ($type === 'hex') {
            return '<' . (\is_string($raw[1] ?? null) ? $raw[1] : '') . '>';
        }

        if (\is_scalar($raw[1] ?? null)) {
            return (string) $raw[1];
        }

        if ($type !== '') {
            return $type;
        }

        return 'null';
    }

    /**
     * Serialize the first scalar or array value from a raw object (non-dict, non-stream).
     *
     * @param array<int, mixed> $objData Raw object data.
     * @param SourceDocument    $src     Source document.
     * @param ObjectMap         $map     Object map.
     *
     * @return string PDF token.
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     */
    private function serializeFirstValue(array $objData, SourceDocument $src, ObjectMap $map): string
    {
        $elements = \array_values($objData);
        $elmCount = \count($elements);
        for ($elmIdx = 0; $elmIdx < $elmCount; ++$elmIdx) {
            $elementSlice = \array_slice($elements, $elmIdx, 1);
            if (\count($elementSlice) !== 1 || !\is_array($elementSlice[0])) {
                continue;
            }

            if (\in_array($elementSlice[0][0] ?? '', ['endobj', '<<', 'stream'], true)) {
                continue;
            }

            return $this->serializeValue($elementSlice[0], $src, $map);
        }

        return 'null';
    }

    /**
     * Extract a single stream object's raw bytes plus filter metadata.
     *
     * @param string         $objRef Object reference key.
     * @param SourceDocument $src    Source document.
     *
     * @return array{bytes: string, filter: string, length: int}
     *
     * @throws ImportCorruptedSourceException
     */
    private function extractSingleStream(string $objRef, SourceDocument $src): array
    {
        $objData = $src->getObject($objRef);
        // First pass: find the filter from the stream dict.
        $filter = '';
        foreach ($objData as $element) {
            if ($element[0] !== '<<' || !\is_array($element[1])) {
                continue;
            }

            $pairs = \array_values($element[1]);
            $cnt = \count($pairs);
            for ($idx = 0; $idx < ($cnt - 1); $idx += 2) {
                $pair = \array_values(\array_slice($pairs, $idx, 2));
                if (\count($pair) !== 2) {
                    continue;
                }

                $keyEl = \reset($pair);
                $valEl = \next($pair);

                if ($keyEl[0] !== '/' || !\is_string($keyEl[1])) {
                    continue;
                }

                $vArr = \is_array($valEl) ? $valEl : [];

                $key = \ltrim($keyEl[1], '/');
                if ($key === 'Filter') {
                    $vType = isset($vArr[0]) ? $vArr[0] : '';
                    $vArrVal = $vArr[1] ?? '';
                    $vVal = \is_string($vArrVal) ? $vArrVal : '';
                    $filter = $vType === '/' ? '/' . $vVal : $vVal;
                    break 2;
                }
            }
        }
        // Second pass: find the stream bytes.
        foreach ($objData as $element) {
            if ($element[0] !== 'stream') {
                continue;
            }

            $rawVal = $element[1];
            $raw = \is_string($rawVal) ? $rawVal : '';
            return ['bytes' => $raw, 'filter' => $filter, 'length' => \strlen($raw)];
        }

        return ['bytes' => '', 'filter' => '', 'length' => 0];
    }

    /**
     * Decode and concatenate multiple content streams.
     *
     * @param array<int, mixed> $refs Array of reference values.
     * @param SourceDocument    $src  Source document.
     *
     * @return array{bytes: string, filter: string, length: int}
     *
     * @throws ImportCorruptedSourceException
     */
    private function concatenateStreams(array $refs, SourceDocument $src): array
    {
        $combined = '';
        $values = \array_values($refs);
        $count = \count($values);
        for ($idx = 0; $idx < $count; ++$idx) {
            $refSlice = \array_slice($values, $idx, 1);
            if (\count($refSlice) !== 1 || !\is_string($refSlice[0])) {
                continue;
            }

            $stream = $this->extractSingleStream(SourceDocument::refToKey($refSlice[0]), $src);
            $combined .= $stream['bytes'] . ' ';
        }

        return ['bytes' => \rtrim($combined), 'filter' => '', 'length' => \strlen(\rtrim($combined))];
    }

    /**
     * Check whether a string is a PDF indirect reference ("N G R" or "N_G" format).
     *
     * @param string $val Value to test.
     *
     * @return bool
     */
    private function isIndirectRef(string $val): bool
    {
        $val = \trim($val);
        // Standard PDF ref form: "5 0 R"
        if (\preg_match('/^\d+\s+\d+\s+R$/', $val)) {
            return true;
        }

        // Parser internal form: "5_0"
        return (bool) \preg_match('/^\d+_\d+$/', $val);
    }
}
