<?php

declare(strict_types=1);

/**
 * DictParser.php
 *
 * @since     2026-08-31
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
 * Com\Tecnick\Pdf\Import\DictParser
 *
 * Converts the raw object arrays returned by the PDF parser into PHP
 * associative arrays. Indirect references are left as their raw string values
 * for lazy resolution by the caller.
 *
 * @since     2026-08-31
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type RawObjectArray from \Com\Tecnick\Pdf\Parser\Process\RawObject
 */
final class DictParser
{
    /**
     * Convert a raw parsed object array to a dictionary (key => scalar/array).
     *
     * The first element of the object array whose type is "<<" (dictionary) is extracted.
     *
     * @param array<int, mixed> $objData Raw object data from the parser.
     *
     * @return array<string, mixed>
     *
     * @throws ImportCorruptedSourceException If no dictionary element is found.
     */
    public function objectToDict(array $objData): array
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
    public function parseDictArray(array $raw): array
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
    public function parseValue(mixed $raw): mixed
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

        // Names, literal strings and hexadecimal strings keep their PDF delimiters so that
        // they stay distinguishable from each other and from indirect references.
        if ($type === '/') {
            return '/' . (\is_string($raw[1] ?? null) ? $raw[1] : '');
        }

        if ($type === '(') {
            // The parser keeps the source escapes of a literal string verbatim.
            return '(' . (\is_string($raw[1] ?? null) ? $raw[1] : '') . ')';
        }

        if ($type === '<' || $type === 'hex') {
            return '<' . (\is_string($raw[1] ?? null) ? $raw[1] : '') . '>';
        }

        if (\in_array($type, ['string', 'numeric', 'boolean', 'null'], true)) {
            return $raw[1] ?? null;
        }

        // Fallback: return scalar value
        return $raw[1] ?? null;
    }

    /**
     * Resolve a value that may be an indirect reference to a dictionary.
     *
     * @param mixed          $value Raw value from a parsed dictionary.
     * @param SourceDocument $src   Source document the reference belongs to.
     *
     * @return array<string, mixed> The dictionary, or an empty array when it cannot be resolved.
     */
    public function resolveDict(mixed $value, SourceDocument $src): array
    {
        if (\is_array($value)) {
            /** @var array<string, mixed> $value */
            return $value;
        }

        if (!\is_string($value) || $value === '') {
            return [];
        }

        try {
            $obj = $src->findObject(SourceDocument::refToKey($value));
        } catch (ImportCorruptedSourceException) {
            return [];
        }

        if ($obj === null) {
            return [];
        }

        try {
            return $this->objectToDict($obj);
        } catch (ImportCorruptedSourceException) {
            return [];
        }
    }

    /**
     * Resolve a value that may be an indirect reference to an array.
     *
     * @param mixed          $value Raw value from a parsed dictionary.
     * @param SourceDocument $src   Source document the reference belongs to.
     *
     * @return array<int, mixed> The array items, or an empty array when it cannot be resolved.
     */
    public function resolveArray(mixed $value, SourceDocument $src): array
    {
        if (\is_array($value)) {
            return \array_values($value);
        }

        if (!\is_string($value) || $value === '') {
            return [];
        }

        try {
            $obj = $src->findObject(SourceDocument::refToKey($value));
        } catch (ImportCorruptedSourceException) {
            return [];
        }

        foreach (\array_values($obj ?? []) as $element) {
            if ($element[0] !== '[' || !\is_array($element[1])) {
                continue;
            }

            return \array_map($this->parseValue(...), \array_values($element[1]));
        }

        return [];
    }
}
