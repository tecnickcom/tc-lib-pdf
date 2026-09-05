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
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Import;

use Com\Tecnick\Pdf\Encrypt\Encrypt as ObjEncrypt;
use Com\Tecnick\Pdf\Filter\Filter as ObjFilter;
use Com\Tecnick\Pdf\Filter\FilterType;

/**
 * Com\Tecnick\Pdf\Import\ResourceCloner
 *
 * Deep-copies objects from the source document into the destination PDF
 * using an ObjectMap for reference remapping. Returns serialized PDF object bytes.
 * Streams and strings are encrypted with the key of the destination document.
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
 * @phpstan-import-type ResolvedPage from PageResolver
 */
class ResourceCloner
{
    /**
     * Filters that can be decoded to re-encode a source stream.
     *
     * @var array<int, FilterType>
     */
    private const DECODABLE_FILTERS = [
        FilterType::AsciiHexDecode,
        FilterType::Ascii85Decode,
        FilterType::LzwDecode,
        FilterType::FlateDecode,
        FilterType::RunLengthDecode,
    ];

    /**
     * Object number counter reference (shared with the output document).
     *
     * @var int
     */
    private int $pon;

    /**
     * PDF/A part of the destination document (0 when PDF/A is not active).
     *
     * @var int
     */
    private int $pdfa;

    /**
     * Encryption object of the destination document.
     */
    private ObjEncrypt $encrypt;

    /**
     * Constructor.
     *
     * @param int         $pon     Current PDF object number (passed by value; read the updated counter back
     *                             via getPon()).
     * @param int         $pdfa    PDF/A part of the destination document (0 when PDF/A is not active).
     * @param ?ObjEncrypt $encrypt Encryption object of the destination document; a disabled one is used
     *                             when null.
     *
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    public function __construct(int $pon, int $pdfa = 0, ?ObjEncrypt $encrypt = null)
    {
        $this->pon = $pon;
        $this->pdfa = $pdfa;
        $this->encrypt = $encrypt ?? new ObjEncrypt();
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
     * Handles a single /Contents reference as well as an array of references,
     * which are decoded and concatenated into one stream.
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
            // Page has no content: return an empty stream.
            return ['bytes' => '', 'filter' => '', 'length' => 0];
        }

        // Single reference (string like "5 0 R" or "5_0").
        if (\is_string($pageDict['Contents'])) {
            return $this->extractSingleStream(SourceDocument::refToKey($pageDict['Contents']), $src);
        }

        // Array of references: a single element is extracted directly.
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
     * @param int                  $ownerNum  Number of the object the dictionary is written into,
     *                                        used as encryption key for the strings it contains.
     *
     * @return string Serialized PDF resource dictionary, e.g. "<< /Font << /F1 7 0 R >> >>".
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    public function cloneResources(array $resources, SourceDocument $src, ObjectMap $map, int $ownerNum = 0): string
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
            $out .= $this->cloneResourceEntry($resources[$resType] ?? null, $src, $map, $ownerNum);
        }

        $out .= ' >>';
        return $out;
    }

    /**
     * Serialize and clone one top-level resource type entry.
     *
     * @param mixed                $resVal   Raw value.
     * @param SourceDocument       $src      Source document.
     * @param ObjectMap            $map      Object map.
     * @param int                  $ownerNum Number of the object the value is written into.
     *
     * @return string Serialized PDF value for this resource entry.
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    private function cloneResourceEntry(mixed $resVal, SourceDocument $src, ObjectMap $map, int $ownerNum): string
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
                    . $this->serializeResourceValue($resVal[$name] ?? null, $src, $map, $ownerNum)
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

            return $this->reencryptStringToken($resVal, $ownerNum);
        }

        return 'null';
    }

    /**
     * Serialize a parsed resource value while remapping indirect references.
     *
     * @param mixed          $value    Resource value.
     * @param SourceDocument $src      Source document.
     * @param ObjectMap      $map      Object map.
     * @param int            $ownerNum Number of the object the value is written into.
     *
     * @return string PDF token string.
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    private function serializeResourceValue(mixed $value, SourceDocument $src, ObjectMap $map, int $ownerNum): string
    {
        if (\is_string($value)) {
            if ($this->isIndirectRef($value)) {
                $destNum = $this->enqueueObject(SourceDocument::refToKey($value), $src, $map);
                return $destNum . ' 0 R';
            }

            return $this->reencryptStringToken($value, $ownerNum);
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

                    $parts[] = $this->serializeResourceValue($itemSlice[0], $src, $map, $ownerNum);
                }

                return '[ ' . \implode(' ', $parts) . ' ]';
            }

            $out = '<< ';
            foreach (\array_keys($value) as $key) {
                if (!\array_key_exists($key, $value)) {
                    continue;
                }

                $out .=
                    '/'
                    . (string) $key
                    . ' '
                    . $this->serializeResourceValue($value[$key] ?? null, $src, $map, $ownerNum)
                    . ' ';
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
     * Clone a referenced source object into the destination document.
     *
     * @param string         $srcRef Source object reference key (see SourceDocument::refToKey()).
     * @param SourceDocument $src    Source document.
     * @param ObjectMap      $map    Object map.
     *
     * @return int Allocated destination object number.
     *
     * @throws ImportException
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
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
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
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
                $dictPart = $this->serializeDictArray(
                    \array_values($elementSlice[0][1]),
                    $src,
                    $map,
                    $streamDict,
                    $destNum,
                );
            } elseif (($elementSlice[0][0] ?? null) === 'stream' && \is_string($elementSlice[0][1] ?? null)) {
                $streamBytes = $elementSlice[0][1];

                // Decoded stream is in element[3] if present; we use raw bytes.
            }
        }

        $out = $destNum . ' 0 obj' . "\n";

        if ($streamBytes !== null) {
            // Stream object: the source filter is preserved unless the destination mode forbids it.
            $parms = $streamDict['DecodeParms'] ?? $streamDict['DP'] ?? '';
            $matches = [];
            \preg_match('/\/EarlyChange\s+(\d+)/', $parms, $matches);
            $earlyChange = isset($matches[1]) && \is_numeric($matches[1]) ? (int) $matches[1] : 1;
            $normalized = $this->normalizeStreamFilter(
                $streamBytes,
                $streamDict['Filter'] ?? '',
                $parms !== '',
                $earlyChange,
            );
            // The stream is filtered first and encrypted last, so /Filter stays as normalized.
            $streamBytes = $this->encryptStream($normalized['bytes'], $destNum, $streamDict);
            $filterEntry = $normalized['filter'] === '' ? '' : ' /Filter ' . $normalized['filter'];

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
            $out .= $this->serializeFirstValue($objData, $src, $map, $destNum) . "\n";
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
     * @param int                  $ownerNum   Number of the object the dictionary is written into.
     *
     * @return string PDF dict content (without outer << >>).
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    private function serializeDictArray(
        array $raw,
        SourceDocument $src,
        ObjectMap $map,
        array &$streamDict,
        int $ownerNum,
    ): string {
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

            $serializedVal = $this->serializeValue($pair[1], $src, $map, $ownerNum);

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
     * @param mixed          $raw      Raw element.
     * @param SourceDocument $src      Source document.
     * @param ObjectMap      $map      Object map.
     * @param int            $ownerNum Number of the object the value is written into.
     *
     * @return string PDF token.
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    private function serializeValue(mixed $raw, SourceDocument $src, ObjectMap $map, int $ownerNum): string
    {
        return match (true) {
            !\is_array($raw) => \is_scalar($raw) ? (string) $raw : '',
            \is_string($raw[0] ?? null) && $raw[0] === 'objref' && \is_string($raw[1] ?? null) => $this->enqueueObject(
                SourceDocument::refToKey($raw[1]),
                $src,
                $map,
            ) . ' 0 R',
            \is_string($raw[0] ?? null) && $raw[0] === '<<' && \is_array($raw[1] ?? null)
                => $this->serializeNestedDictValue($raw[1], $src, $map, $ownerNum),
            \is_string($raw[0] ?? null) && $raw[0] === '[' && \is_array($raw[1] ?? null) => $this->serializeArrayValue(
                $raw[1],
                $src,
                $map,
                $ownerNum,
            ),
            \is_string($raw[0] ?? null) && $raw[0] === '/' => '/' . (\is_string($raw[1] ?? null) ? $raw[1] : ''),
            // Parser literal-string token `(` already carries PDF string escapes.
            \is_string($raw[0] ?? null) && $raw[0] === '(' => $this->serializeEscapedLiteralString(
                \is_string($raw[1] ?? null) ? $raw[1] : '',
                $ownerNum,
            ),
            // Synthetic token `string` is plain text and must be escaped for PDF literal syntax.
            \is_string($raw[0] ?? null) && $raw[0] === 'string' => $this->serializeLiteralString(
                \is_string($raw[1] ?? null) ? $raw[1] : '',
                $ownerNum,
            ),
            \is_string($raw[0] ?? null) && ($raw[0] === '<' || $raw[0] === 'hex') => $this->serializeHexString(
                \is_string($raw[1] ?? null) ? $raw[1] : '',
                $ownerNum,
            ),
            \is_scalar($raw[1] ?? null) => (string) $raw[1],
            \is_string($raw[0] ?? null) && $raw[0] !== '' => $raw[0],
            default => 'null',
        };
    }

    /**
     * Serialize a nested dictionary token as a PDF dictionary string.
     *
     * @param array<array-key, mixed> $raw      Nested dictionary token payload.
     * @param SourceDocument          $src      Source document.
     * @param ObjectMap               $map      Object map.
     * @param int                     $ownerNum Number of the object the dictionary is written into.
     *
     * @return string Serialized PDF dictionary.
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    private function serializeNestedDictValue(array $raw, SourceDocument $src, ObjectMap $map, int $ownerNum): string
    {
        /** @var array<string, string> $unused */
        $unused = [];

        return '<<' . $this->serializeDictArray(\array_values($raw), $src, $map, $unused, $ownerNum) . '>>';
    }

    /**
     * Serialize a nested array token as a PDF array string.
     *
     * @param array<array-key, mixed> $raw      Nested array token payload.
     * @param SourceDocument          $src      Source document.
     * @param ObjectMap               $map      Object map.
     * @param int                     $ownerNum Number of the object the array is written into.
     *
     * @return string Serialized PDF array.
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    private function serializeArrayValue(array $raw, SourceDocument $src, ObjectMap $map, int $ownerNum): string
    {
        $parts = [];
        $values = \array_values($raw);
        $itemCount = \count($values);
        for ($itemIdx = 0; $itemIdx < $itemCount; ++$itemIdx) {
            $parts[] = $this->serializeValue($values[$itemIdx] ?? null, $src, $map, $ownerNum);
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
     * @param array<int, mixed> $objData  Raw object data.
     * @param SourceDocument    $src      Source document.
     * @param ObjectMap         $map      Object map.
     * @param int               $ownerNum Number of the object the value is written into.
     *
     * @return string PDF token.
     *
     * @throws ImportCorruptedSourceException
     * @throws ImportException
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    private function serializeFirstValue(array $objData, SourceDocument $src, ObjectMap $map, int $ownerNum): string
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

            return $this->serializeValue($elementSlice[0], $src, $map, $ownerNum);
        }

        return 'null';
    }

    /**
     * Encrypt a cloned stream with the key of the object that carries it.
     *
     * ISO 32000 excludes from encryption the metadata streams of a document that declares
     * /EncryptMetadata false and the streams whose crypt filter is /Identity.
     *
     * @param string                $bytes      Stream bytes, already filtered.
     * @param int                   $destNum    Destination object number.
     * @param array<string, string> $streamDict Serialized entries of the stream dictionary.
     *
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    private function encryptStream(string $bytes, int $destNum, array $streamDict): string
    {
        $encData = $this->encrypt->getEncryptionData();
        if (!$encData['encrypted']) {
            return $bytes;
        }

        if (!$encData['EncryptMetadata'] && ($streamDict['Type'] ?? '') === '/Metadata') {
            return $bytes;
        }

        if (\str_contains($streamDict['Filter'] ?? '', '/Crypt')) {
            // The crypt filter name is carried by /DecodeParms and defaults to /Identity.
            $parms = $streamDict['DecodeParms'] ?? $streamDict['DP'] ?? '';
            if (!\str_contains($parms, '/Name') || \str_contains($parms, '/Identity')) {
                return $bytes;
            }
        }

        return $this->encrypt->encryptString($bytes, $destNum);
    }

    /**
     * Serialize a string value that is already a PDF token (`(...)` or `<...>`),
     * re-encrypting its content for the destination document.
     *
     * @param string $value    Serialized string token.
     * @param int    $ownerNum Number of the object the value is written into.
     *
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    private function reencryptStringToken(string $value, int $ownerNum): string
    {
        if (!$this->encrypt->getEncryptionData()['encrypted'] || \strlen($value) < 2) {
            return $value;
        }

        if ($value[0] === '(' && \str_ends_with($value, ')')) {
            return $this->serializeEscapedLiteralString(\substr($value, 1, -1), $ownerNum);
        }

        if ($value[0] === '<' && \str_ends_with($value, '>')) {
            return $this->serializeHexString(\substr($value, 1, -1), $ownerNum);
        }

        return $value;
    }

    /**
     * Serialize a literal string whose PDF escape sequences are already in place.
     *
     * The bytes are kept verbatim unless the destination document is encrypted, in which
     * case the string is decoded and re-encrypted.
     *
     * @param string $escaped  String content, without the enclosing parentheses.
     * @param int    $ownerNum Number of the object the string is written into.
     *
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    private function serializeEscapedLiteralString(string $escaped, int $ownerNum): string
    {
        if (!$this->encrypt->getEncryptionData()['encrypted']) {
            return '(' . $escaped . ')';
        }

        return $this->serializeLiteralString($this->unescapePdfLiteralString($escaped), $ownerNum);
    }

    /**
     * Serialize raw string bytes as a PDF string, encrypted for the destination document.
     *
     * Encrypted values are written in hexadecimal form because ciphertext is binary.
     *
     * @param string $bytes    Raw string bytes.
     * @param int    $ownerNum Number of the object the string is written into.
     *
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    private function serializeLiteralString(string $bytes, int $ownerNum): string
    {
        if (!$this->encrypt->getEncryptionData()['encrypted']) {
            return '(' . $this->escapePdfLiteralString($bytes) . ')';
        }

        return '<' . \bin2hex($this->encrypt->encryptString($bytes, $ownerNum)) . '>';
    }

    /**
     * Serialize the digits of a PDF hexadecimal string, encrypted for the destination document.
     *
     * @param string $hex      Hexadecimal digits, without delimiters.
     * @param int    $ownerNum Number of the object the string is written into.
     *
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    private function serializeHexString(string $hex, int $ownerNum): string
    {
        if (!$this->encrypt->getEncryptionData()['encrypted']) {
            return '<' . $hex . '>';
        }

        return '<' . \bin2hex($this->encrypt->encryptString($this->hexStringToBytes($hex), $ownerNum)) . '>';
    }

    /**
     * Convert the digits of a PDF hexadecimal string into raw bytes.
     *
     * An odd number of digits is completed with a trailing zero, as the format requires.
     *
     * @param string $hex Hexadecimal digits, without delimiters.
     */
    private function hexStringToBytes(string $hex): string
    {
        $digits = (string) \preg_replace('/[^0-9A-Fa-f]/', '', $hex);
        if ((\strlen($digits) % 2) !== 0) {
            $digits .= '0';
        }

        $bytes = \hex2bin($digits);
        return $bytes === false ? '' : $bytes;
    }

    /**
     * Decode the escape sequences of a PDF literal string into its raw bytes.
     *
     * @param string $value String content, without the enclosing parentheses.
     */
    private function unescapePdfLiteralString(string $value): string
    {
        $out = '';
        $len = \strlen($value);
        for ($idx = 0; $idx < $len; ++$idx) {
            if ($value[$idx] !== '\\') {
                $out .= $value[$idx];
                continue;
            }

            ++$idx;
            if ($idx >= $len) {
                break;
            }

            $out .= $this->decodeEscapeSequence($value, $idx);
        }

        return $out;
    }

    /**
     * Decode the escape sequence that starts at the given offset of a PDF literal string.
     *
     * @param string $value  String content, without the enclosing parentheses.
     * @param int    $idx    Offset of the escaped character; advanced past the sequence.
     *
     * @return string Decoded bytes, empty for a line continuation.
     */
    private function decodeEscapeSequence(string $value, int &$idx): string
    {
        $chr = $value[$idx];
        $len = \strlen($value);

        if ($chr >= '0' && $chr <= '7') {
            $octal = $chr;
            while (\strlen($octal) < 3 && ($idx + 1) < $len && $value[$idx + 1] >= '0' && $value[$idx + 1] <= '7') {
                ++$idx;
                $octal .= $value[$idx];
            }

            return \chr((int) \octdec($octal) % 256);
        }

        if ($chr === "\r") {
            // Line continuation: a CRLF pair counts as one line ending.
            if (($idx + 1) < $len && $value[$idx + 1] === "\n") {
                ++$idx;
            }

            return '';
        }

        return match ($chr) {
            'n' => "\n",
            'r' => "\r",
            't' => "\t",
            'b' => "\x08",
            'f' => "\x0c",
            "\n" => '',
            // A backslash before any other character is dropped, including \( \) and \\.
            default => $chr,
        };
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
     * @throws ImportUnsupportedFeatureException If a filter forbidden by the destination mode survives.
     */
    private function extractSingleStream(string $objRef, SourceDocument $src): array
    {
        $objData = $src->getObject($objRef);
        // First pass: find the filter and the decode parameters from the stream dict.
        $filter = '';
        $hasDecodeParms = false;
        $earlyChange = 1;
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
                    continue;
                }

                if ($key === 'DecodeParms' || $key === 'DP') {
                    $hasDecodeParms = true;
                    $earlyChange = $this->extractEarlyChange($vArr);
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
            $normalized = $this->normalizeStreamFilter($raw, $filter, $hasDecodeParms, $earlyChange);

            return [
                'bytes' => $normalized['bytes'],
                'filter' => $normalized['filter'],
                'length' => \strlen($normalized['bytes']),
            ];
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
     * @throws ImportUnsupportedFeatureException If a filter forbidden by the destination mode survives.
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
     * Single-stream imports keep the original bytes and /Filter metadata. An
     * array /Contents needs plain bytes, so decodable filters are applied; the
     * original bytes are kept when decoding fails.
     */
    private function decodeMultiContentStream(string $bytes, string $filter): string
    {
        return $this->decodeFilterChain($bytes, $this->resolveFilterChain($filter), 1) ?? $bytes;
    }

    /**
     * Replace a source filter that the destination conformance mode does not accept.
     *
     * LZWDecode streams are decoded and re-encoded with FlateDecode. Any /DecodeParms
     * entry stays valid because the predictor stage is filter independent, so a chain is
     * only collapsed to a single FlateDecode when the stream carries no /DecodeParms.
     *
     * @param string $bytes           Raw stream bytes.
     * @param string $filter          Serialized /Filter value.
     * @param bool   $hasDecodeParms  True when the stream dictionary has a /DecodeParms entry.
     * @param int    $earlyChange     LZWDecode /EarlyChange parameter.
     *
     * @return array{bytes: string, filter: string} Input values when no rewrite is needed or possible.
     *
     * @throws ImportUnsupportedFeatureException If a filter forbidden by the destination mode survives.
     */
    private function normalizeStreamFilter(
        string $bytes,
        string $filter,
        bool $hasDecodeParms = false,
        int $earlyChange = 1,
    ): array {
        $chain = $this->resolveFilterChain($filter);
        if ($chain === []) {
            return ['bytes' => $bytes, 'filter' => $filter];
        }

        if ($this->pdfa === 1 && \in_array(FilterType::JpxDecode, $chain, true)) {
            throw new ImportUnsupportedFeatureException(
                'The source stream uses the JPXDecode filter, which ISO 19005-1 does not allow.',
            );
        }

        // LZWDecode is the only filter ISO 19005 forbids in every PDF/A part.
        if (!\in_array(FilterType::LzwDecode, $chain, true)) {
            return ['bytes' => $bytes, 'filter' => $filter];
        }

        $decoded = \count($chain) === 1 || !$hasDecodeParms
            ? $this->decodeFilterChain($bytes, $chain, $earlyChange)
            : null;

        if ($decoded !== null) {
            $flate = \gzcompress($decoded);
            if ($flate !== false) {
                return ['bytes' => $flate, 'filter' => '/FlateDecode'];
            }
        }

        if ($this->pdfa > 0) {
            throw new ImportUnsupportedFeatureException(
                'The source stream uses the LZWDecode filter, which ISO 19005 forbids,'
                . ' and it cannot be re-encoded with FlateDecode.',
            );
        }

        return ['bytes' => $bytes, 'filter' => $filter];
    }

    /**
     * Decode a stream through an ordered filter chain.
     *
     * The /DecodeParms predictor stage is deliberately left out: the caller keeps
     * the original /DecodeParms entry, which is applied when the stream is read back.
     *
     * @param array<int, ?FilterType> $chain       Ordered filters, null for an unknown name.
     * @param int                     $earlyChange LZWDecode /EarlyChange parameter.
     *
     * @return string|null Decoded bytes, or null when a filter of the chain cannot be applied.
     */
    private function decodeFilterChain(string $bytes, array $chain, int $earlyChange): ?string
    {
        foreach ($chain as $type) {
            if (!\in_array($type, self::DECODABLE_FILTERS, true)) {
                return null;
            }
        }

        try {
            /** @var array<int, FilterType> $chain */
            return (new ObjFilter())->decodeAll($chain, $bytes, ['EarlyChange' => $earlyChange]);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Read the /EarlyChange value from a parsed /DecodeParms token.
     *
     * @param mixed $token Raw parser token.
     *
     * @return int The declared value, or 1 (the PDF default) when absent.
     */
    private function extractEarlyChange(#[\SensitiveParameter] mixed $token): int
    {
        if (!\is_array($token) || ($token[0] ?? '') !== '<<' || !\is_array($token[1] ?? null)) {
            return 1;
        }

        $pairs = \array_values($token[1]);
        $cnt = \count($pairs);
        for ($idx = 0; $idx < ($cnt - 1); $idx += 2) {
            $pair = \array_values(\array_slice($pairs, $idx, 2));
            if (\count($pair) !== 2 || !\is_array($pair[0]) || !\is_array($pair[1])) {
                continue;
            }

            if (($pair[0][0] ?? '') !== '/' || \ltrim((string) ($pair[0][1] ?? ''), '/') !== 'EarlyChange') {
                continue;
            }

            if (\is_numeric($pair[1][1] ?? null)) {
                return (int) $pair[1][1];
            }
        }

        return 1;
    }

    /**
     * Parse a serialized /Filter token into an ordered list of filters.
     *
     * Both the PDF filter names and the inline-image abbreviations are recognised;
     * a name that is not a standard filter is reported as null.
     *
     * @return array<int, ?FilterType>
     */
    private function resolveFilterChain(string $filter): array
    {
        $trimmed = \trim($filter);
        if ($trimmed === '') {
            return [];
        }

        if ($trimmed[0] === '/') {
            return [$this->resolveFilterType(\ltrim($trimmed, '/'))];
        }

        if ($trimmed[0] !== '[' || \substr($trimmed, -1) !== ']') {
            return [];
        }

        $matches = [];
        $found = \preg_match_all('/\/([A-Za-z0-9]+)/', $trimmed, $matches);
        if ($found === false || $found < 1) {
            return [];
        }

        $out = [];
        foreach ($matches[1] ?? [] as $name) {
            if ($name === '') {
                continue;
            }

            $out[] = $this->resolveFilterType($name);
        }

        return $out;
    }

    /**
     * Resolve a PDF filter name to its type, or null when it is not a standard filter.
     */
    private function resolveFilterType(string $name): ?FilterType
    {
        try {
            return FilterType::fromLoose($name);
        } catch (\Com\Tecnick\Pdf\Filter\Exception) {
            return null;
        }
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
