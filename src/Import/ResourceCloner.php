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
                if (!\array_key_exists($name, $resVal)) {
                    continue;
                }

                $out .=
                    '/'
                    . (string) $name
                    . ' '
                    . $this->serializeResourceValue($resVal[$name] ?? null, $src, $map)
                    . ' ';
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
     * Serialize a parsed resource value while remapping indirect references.
     *
     * @param mixed          $value Resource value.
     * @param SourceDocument $src   Source document.
     * @param ObjectMap      $map   Object map.
     *
     * @return string PDF token string.
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     */
    private function serializeResourceValue(mixed $value, SourceDocument $src, ObjectMap $map): string
    {
        if (\is_string($value)) {
            if ($this->isIndirectRef($value)) {
                $destNum = $this->enqueueObject(SourceDocument::refToKey($value), $src, $map);
                return $destNum . ' 0 R';
            }

            return $value;
        }

        if (\is_array($value)) {
            if (\array_is_list($value)) {
                $parts = [];
                $items = \array_values($value);
                $itemCount = \count($items);
                for ($idx = 0; $idx < $itemCount; ++$idx) {
                    $itemSlice = \array_slice($items, $idx, 1);
                    if (\count($itemSlice) !== 1) {
                        continue;
                    }

                    $parts[] = $this->serializeResourceValue($itemSlice[0], $src, $map);
                }

                return '[ ' . \implode(' ', $parts) . ' ]';
            }

            $out = '<< ';
            foreach (\array_keys($value) as $key) {
                if (!\array_key_exists($key, $value)) {
                    continue;
                }

                $out .=
                    '/' . (string) $key . ' ' . $this->serializeResourceValue($value[$key] ?? null, $src, $map) . ' ';
            }

            return $out . '>>';
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (\is_int($value) || \is_float($value)) {
            return (string) $value;
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
        return match (true) {
            !\is_array($raw) => \is_scalar($raw) ? (string) $raw : '',
            \is_string($raw[0] ?? null) && $raw[0] === 'objref' && \is_string($raw[1] ?? null) => $this->enqueueObject(
                SourceDocument::refToKey($raw[1]),
                $src,
                $map,
            ) . ' 0 R',
            \is_string($raw[0] ?? null) && $raw[0] === '<<' && \is_array($raw[1] ?? null)
                => $this->serializeNestedDictValue($raw[1], $src, $map),
            \is_string($raw[0] ?? null) && $raw[0] === '[' && \is_array($raw[1] ?? null) => $this->serializeArrayValue(
                $raw[1],
                $src,
                $map,
            ),
            \is_string($raw[0] ?? null) && $raw[0] === '/' => '/' . (\is_string($raw[1] ?? null) ? $raw[1] : ''),
            // Parser literal-string token `(` already carries PDF string escapes; preserve bytes verbatim.
            \is_string($raw[0] ?? null) && $raw[0] === '(' => '(' . (\is_string($raw[1] ?? null) ? $raw[1] : '') . ')',
            // Legacy synthetic token `string` is plain text and must be escaped for PDF literal syntax.
            \is_string($raw[0] ?? null) && $raw[0] === 'string' => '('
                . $this->escapePdfLiteralString(\is_string($raw[1] ?? null) ? $raw[1] : '')
                . ')',
            \is_string($raw[0] ?? null) && ($raw[0] === '<' || $raw[0] === 'hex') => '<'
                . (\is_string($raw[1] ?? null) ? $raw[1] : '')
                . '>',
            \is_scalar($raw[1] ?? null) => (string) $raw[1],
            \is_string($raw[0] ?? null) && $raw[0] !== '' => $raw[0],
            default => 'null',
        };
    }

    /**
     * Serialize a nested dictionary token as a PDF dictionary string.
     *
     * @param array<array-key, mixed> $raw Nested dictionary token payload.
     * @param SourceDocument    $src Source document.
     * @param ObjectMap         $map Object map.
     *
     * @return string Serialized PDF dictionary.
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     */
    private function serializeNestedDictValue(array $raw, SourceDocument $src, ObjectMap $map): string
    {
        /** @var array<string, string> $unused */
        $unused = [];

        return '<<' . $this->serializeDictArray(\array_values($raw), $src, $map, $unused) . '>>';
    }

    /**
     * Serialize a nested array token as a PDF array string.
     *
     * @param array<array-key, mixed> $raw Nested array token payload.
     * @param SourceDocument          $src Source document.
     * @param ObjectMap               $map Object map.
     *
     * @return string Serialized PDF array.
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     */
    private function serializeArrayValue(array $raw, SourceDocument $src, ObjectMap $map): string
    {
        $parts = [];
        $values = \array_values($raw);
        $itemCount = \count($values);
        for ($itemIdx = 0; $itemIdx < $itemCount; ++$itemIdx) {
            $parts[] = $this->serializeValue($values[$itemIdx] ?? null, $src, $map);
        }

        return '[' . \implode(' ', $parts) . ']';
    }

    /**
     * Escape a plain-text value for use inside a PDF literal string `( ... )`.
     */
    private function escapePdfLiteralString(string $value): string
    {
        return (string) \preg_replace('/([\\\\()])/', '\\\\$1', $value);
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
                    $filter = $this->extractFilterToken($vArr);
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
     * Serialize a parsed Filter token into a valid PDF /Filter value.
     *
     * Supports:
     * - name token: ['/', 'FlateDecode'] => '/FlateDecode'
     * - array token: ['[', [ ['/', 'FlateDecode'], ['/', 'ASCII85Decode'] ]]
     *
     * @param array<int, mixed> $token Raw parser token.
     */
    private function extractFilterToken(#[\SensitiveParameter] array $token): string
    {
        if (!\array_key_exists(0, $token) || !\is_string($token[0])) {
            return '';
        }

        $type = $token[0];

        if ($type === '/' && \array_key_exists(1, $token) && \is_string($token[1])) {
            return '/' . $token[1];
        }

        if ($type !== '[' || !\array_key_exists(1, $token) || !\is_array($token[1])) {
            return '';
        }

        $names = [];
        $items = \array_values($token[1]);
        $itemCount = \count($items);
        for ($idx = 0; $idx < $itemCount; ++$idx) {
            $itemSlice = \array_slice($items, $idx, 1);
            if (\count($itemSlice) !== 1 || !\is_array($itemSlice[0])) {
                continue;
            }

            $item = $itemSlice[0];
            if (($item[0] ?? '') !== '/' || !\is_string($item[1] ?? null)) {
                continue;
            }

            $names[] = '/' . $item[1];
        }

        if ($names === []) {
            return '';
        }

        if (\count($names) === 1) {
            return $names[0];
        }

        return '[ ' . \implode(' ', $names) . ' ]';
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
            // When /Contents is an array, each stream can carry its own filter.
            // Concatenate decoded bytes so the resulting Form stream is valid plain content.
            $combined .= $this->decodeMultiContentStream($stream['bytes'], $stream['filter']) . ' ';
        }

        return ['bytes' => \rtrim($combined), 'filter' => '', 'length' => \strlen(\rtrim($combined))];
    }

    /**
     * Decode one content stream for multi-stream concatenation.
     *
     * For single-stream imports we preserve original bytes and /Filter metadata.
     * For array /Contents we need plain bytes, so we best-effort decode known filters.
     * If decoding fails we keep the original bytes to avoid dropping content entirely.
     */
    private function decodeMultiContentStream(string $bytes, string $filter): string
    {
        $filters = $this->parseFilterChain($filter);
        if ($filters === []) {
            return $bytes;
        }

        $decoded = $bytes;
        foreach ($filters as $name) {
            if ($name !== 'FlateDecode' && $name !== 'Fl') {
                return $bytes;
            }

            $next = $this->tryDecodeZlib($decoded);
            if (!\is_string($next)) {
                $next = $this->tryGzUncompress($decoded);
            }

            if (!\is_string($next)) {
                $next = $this->tryGzInflate($decoded);
            }

            if (!\is_string($next)) {
                return $bytes;
            }

            $decoded = $next;
        }

        return $decoded;
    }

    /**
     * Attempt zlib decode without emitting runtime warnings.
     */
    private function tryDecodeZlib(string $data): string|false
    {
        \set_error_handler(static fn(): bool => true);
        try {
            $decoded = \zlib_decode($data);
        } finally {
            \restore_error_handler();
        }

        return \is_string($decoded) ? $decoded : false;
    }

    /**
     * Attempt gzuncompress without emitting runtime warnings.
     */
    private function tryGzUncompress(string $data): string|false
    {
        \set_error_handler(static fn(): bool => true);
        try {
            $decoded = \gzuncompress($data);
        } finally {
            \restore_error_handler();
        }

        return \is_string($decoded) ? $decoded : false;
    }

    /**
     * Attempt gzinflate without emitting runtime warnings.
     */
    private function tryGzInflate(string $data): string|false
    {
        \set_error_handler(static fn(): bool => true);
        try {
            $decoded = \gzinflate($data);
        } finally {
            \restore_error_handler();
        }

        return \is_string($decoded) ? $decoded : false;
    }

    /**
     * Parse a serialized /Filter token into an ordered list of filter names.
     *
     * @return array<int, string>
     */
    private function parseFilterChain(string $filter): array
    {
        $trimmed = \trim($filter);
        if ($trimmed === '') {
            return [];
        }

        if ($trimmed[0] === '/') {
            return [\ltrim($trimmed, '/')];
        }

        if ($trimmed[0] !== '[' || \substr($trimmed, -1) !== ']') {
            return [];
        }

        $matches = [];
        if (\preg_match_all('/\/([A-Za-z0-9]+)/', $trimmed, $matches) !== 1) {
            return [];
        }

        $names = $matches[1] ?? [];
        if ($names === []) {
            return [];
        }

        $out = [];
        foreach ($names as $name) {
            if ($name === '') {
                continue;
            }

            $out[] = $name;
        }

        return $out;
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
