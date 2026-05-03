<?php

/**
 * ResourceCloner.php
 *
 * @since     2002-08-03
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
 * @since     2002-08-03
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

        $contents = $pageDict['Contents'];

        // Single reference (string like "5 0 R" or "5_0").
        if (\is_string($contents)) {
            return $this->extractSingleStream(SourceDocument::refToKey($contents), $src);
        }

        // Array of references — for Phase 1 we require exactly one element.
        if (\is_array($contents)) {
            if (\count($contents) === 1) {
                $ref = \reset($contents);
                if (!\is_string($ref)) {
                    throw new ImportCorruptedSourceException('Invalid /Contents reference type.');
                }

                return $this->extractSingleStream(SourceDocument::refToKey($ref), $src);
            }

            // Multiple streams: decode and concatenate.
            return $this->concatenateStreams(\array_values($contents), $src);
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
     */
    public function cloneResources(array $resources, SourceDocument $src, ObjectMap $map): string
    {
        if (empty($resources)) {
            return '';
        }

        $out = '<<';
        $out .= ' /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]';

        foreach ($resources as $resType => $resVal) {
            if ($resType === 'ProcSet') {
                continue; // already emitted above
            }

            $out .= ' /' . $resType . ' ';
            $out .= $this->cloneResourceEntry($resVal, $src, $map);
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
     */
    private function cloneResourceEntry(
        mixed $resVal,
        SourceDocument $src,
        ObjectMap $map
    ): string {
        // Resource subdicts (Font, XObject, ExtGState, ColorSpace, Pattern, Shading) are dicts of name->ref.
        if (\is_array($resVal)) {
            $out = '<< ';
            foreach ($resVal as $name => $ref) {
                if (!\is_string($ref)) {
                    // Skip non-string (nested array) values.
                    continue;
                }

                if ($this->isIndirectRef($ref)) {
                    $destNum = $this->enqueueObject(SourceDocument::refToKey($ref), $src, $map);
                    $out .= '/' . $name . ' ' . $destNum . ' 0 R ';
                } else {
                    // Inline scalar value.
                    $out .= '/' . $name . ' ' . $ref . ' ';
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

        return \is_scalar($resVal) ? (string) $resVal : '';
    }

    /**
     * Allocate a destination object number for a source reference and recursively clone the object.
     *
     * @param string         $srcRef Source object reference.
     * @param SourceDocument $src    Source document.
     * @param ObjectMap      $map    Object map.
     *
     * @return int Allocated destination object number.
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
     */
    private function serializeObject(
        int $destNum,
        array $objData,
        SourceDocument $src,
        ObjectMap $map
    ): string {
        $dictPart = '';
        $streamBytes = null;
        /** @var array<string, string> $streamDict */
        $streamDict = [];

        foreach ($objData as $element) {
            if (!\is_array($element)) {
                continue;
            }

            $type = $element[0] ?? '';
            $val = $element[1] ?? null;

            if ($type === '<<' && \is_array($val)) {
                $dictPart = $this->serializeDictArray(\array_values($val), $src, $map, $streamDict);
            } elseif ($type === 'stream' && \is_string($val)) {
                $streamBytes = $val;
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

            $out .= '<<' . $dictPart . $filterEntry
                . ' /Length ' . \strlen($streamBytes)
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
     */
    private function serializeDictArray(
        array $raw,
        SourceDocument $src,
        ObjectMap $map,
        array &$streamDict
    ): string {
        $out = '';
        $cnt = \count($raw);
        for ($idx = 0; $idx < $cnt - 1; $idx += 2) {
            $keyEl = $raw[$idx];
            $valEl = $raw[$idx + 1];
            if (!\is_array($keyEl) || ($keyEl[0] ?? '') !== '/') {
                continue;
            }

            $keyName = $keyEl[1] ?? '';
            $key = \ltrim(\is_string($keyName) ? $keyName : '', '/');
            if ($key === 'Length') {
                continue;
            }

            $serializedVal = $this->serializeValue($valEl, $src, $map);

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
     */
    private function serializeValue(mixed $raw, SourceDocument $src, ObjectMap $map): string
    {
        if (!\is_array($raw)) {
            return \is_scalar($raw) ? (string) $raw : '';
        }

        $typeRaw = $raw[0] ?? '';
        $type = \is_string($typeRaw) ? $typeRaw : '';
        $val = $raw[1] ?? null;

        if ($type === 'objref' && \is_string($val)) {
            $destNum = $this->enqueueObject(SourceDocument::refToKey($val), $src, $map);
            return $destNum . ' 0 R';
        }

        if ($type === '<<' && \is_array($val)) {
            /** @var array<string, string> $unused */
            $unused = [];
            $inner = $this->serializeDictArray(\array_values($val), $src, $map, $unused);
            return '<<' . $inner . '>>';
        }

        if ($type === '[' && \is_array($val)) {
            $parts = \array_map(fn($item) => $this->serializeValue($item, $src, $map), $val);
            return '[' . \implode(' ', $parts) . ']';
        }

        if ($type === '/') {
            return '/' . (\is_string($val) ? $val : '');
        }

        if ($type === 'string') {
            return '(' . \addslashes(\is_string($val) ? $val : '') . ')';
        }

        if ($type === 'hex') {
            return '<' . (\is_string($val) ? $val : '') . '>';
        }

        return \is_scalar($val) ? (string) $val : ($type !== '' ? $type : 'null');
    }

    /**
     * Serialize the first scalar or array value from a raw object (non-dict, non-stream).
     *
     * @param array<int, mixed> $objData Raw object data.
     * @param SourceDocument    $src     Source document.
     * @param ObjectMap         $map     Object map.
     *
     * @return string PDF token.
     */
    private function serializeFirstValue(array $objData, SourceDocument $src, ObjectMap $map): string
    {
        foreach ($objData as $element) {
            if (!\is_array($element)) {
                continue;
            }

            $type = $element[0] ?? '';
            if (\in_array($type, ['endobj', '<<', 'stream'], true)) {
                continue;
            }

            return $this->serializeValue($element, $src, $map);
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
     */
    private function extractSingleStream(string $objRef, SourceDocument $src): array
    {
        $objData = $src->getObject($objRef);
            // First pass: find the filter from the stream dict.
            $filter = '';
        foreach ($objData as $element) {
            if (!\is_array($element) || ($element[0] ?? '') !== '<<') {
                continue;
            }
            /** @var array<int, mixed> $pairs */
            $pairs = \is_array($element[1] ?? null) ? \array_values($element[1]) : [];
            $cnt = \count($pairs);
            for ($idx = 0; $idx < $cnt - 1; $idx += 2) {
                $kEl = $pairs[$idx];
                $vEl = $pairs[$idx + 1];
                if (!\is_array($kEl) || ($kEl[0] ?? '') !== '/') {
                    continue;
                }
                $kElVal = $kEl[1] ?? '';
                $key = \ltrim(\is_string($kElVal) ? $kElVal : '', '/');
                if ($key === 'Filter') {
                    $vArr = \is_array($vEl) ? $vEl : [];
                    $vArrType = $vArr[0] ?? '';
                    $vType = \is_string($vArrType) ? $vArrType : '';
                    $vArrVal = $vArr[1] ?? '';
                    if (\is_string($vArrVal) && $vArrVal !== '') {
                        $vVal = $vArrVal;
                    } elseif (\is_string($vEl)) {
                        $vVal = $vEl;
                    } else {
                        $vVal = '';
                    }
                    $filter = ($vType === '/') ? '/' . $vVal : $vVal;
                    break 2;
                }
            }
        }
        // Second pass: find the stream bytes.
        foreach ($objData as $element) {
            if (!\is_array($element) || ($element[0] ?? '') !== 'stream') {
                continue;
            }

            $rawVal = $element[1] ?? '';
            $raw = \is_string($rawVal) ? $rawVal : '';
            // element[3] is the decoded stream if decoding was requested.
            $decoded = $element[3][1] ?? null;
            $bytes = (isset($element[3]) && \is_array($element[3]) && \is_string($decoded))
                ? $decoded
                : $raw;
            // If we got decoded bytes, the filter is no longer needed.
            if (isset($element[3]) && \is_array($element[3]) && \is_string($decoded)) {
                $filter = '';
            }

            return ['bytes' => $bytes, 'filter' => $filter, 'length' => \strlen($bytes)];
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
     */
    private function concatenateStreams(array $refs, SourceDocument $src): array
    {
        $combined = '';
        foreach ($refs as $ref) {
            if (!\is_string($ref)) {
                continue;
            }

            $stream = $this->extractSingleStream(SourceDocument::refToKey($ref), $src);
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
