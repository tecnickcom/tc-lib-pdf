<?php

declare(strict_types=1);

/**
 * SourceDocument.php
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

use Com\Tecnick\Pdf\Parser\Parser;

/**
 * Com\Tecnick\Pdf\Import\SourceDocument
 *
 * Wraps a parsed source PDF document and provides access to its objects and trailer.
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
 * @phpstan-import-type XrefData from \Com\Tecnick\Pdf\Parser\Process\XrefStream
 */
class SourceDocument
{
    /**
     * Unique identifier for this source document (sha256 of the raw data).
     *
     * @var string
     */
    private string $docId;

    /**
     * XREF and trailer data.
     *
     * @phpstan-var XrefData
     * @var array<string, mixed>
     */
    private array $xref;

    /**
     * All parsed objects.
     *
     * @phpstan-var array<string, array<int, RawObjectArray>>
     * @var array<string, array<int, mixed>>
     */
    private array $objects;

    /**
     * Load and parse a PDF from raw binary data.
     *
     * @param string             $data PDF binary data.
     * @param array<string, mixed> $cfg  Parser configuration.
     *
     * @throws ImportCorruptedSourceException  If the PDF cannot be parsed.
     * @throws ImportUnsupportedFeatureException  If the PDF is encrypted.
     */
    public function __construct(string $data, array $cfg = [])
    {
        $this->docId = \hash('sha256', $data);
        $passwordProvided = false;
        $parserCfg = $this->normalizeParserConfig($cfg, $passwordProvided);
        try {
            $parser = new Parser($parserCfg);
            [$this->xref, $this->objects] = $parser->parse($data);
        } catch (\Exception $exc) {
            throw new ImportCorruptedSourceException('Failed to parse PDF: ' . $exc->getMessage(), 0, $exc);
        }

        if (isset($this->xref['trailer']['encrypt'])) {
            if ($passwordProvided) {
                throw new ImportUnsupportedFeatureException('Cannot import encrypted PDF documents: '
                . 'password-based import is not supported by the current parser.');
            }

            throw new ImportUnsupportedFeatureException(
                'Cannot import encrypted PDF documents: password support is not available in the current parser.',
            );
        }
    }

    /**
     * Normalize source configuration for parser construction.
     *
     * Accepted password aliases are captured for better error messages when
     * encrypted inputs are detected, even though current parser versions do not
     * support password-based decryption.
     *
     * @param array<string, mixed> $cfg
     *
     * @return array<string, bool>
     */
    private function normalizeParserConfig(array $cfg, bool &$passwordProvided): array
    {
        $passwordKeys = ['password', 'user_password', 'owner_password'];
        foreach ($passwordKeys as $key) {
            if (!isset($cfg[$key]) || !\is_string($cfg[$key])) {
                continue;
            }

            if ($cfg[$key] !== '') {
                $passwordProvided = true;
                break;
            }
        }

        /** @var array<string, bool> $parserCfg */
        $parserCfg = [];
        if (isset($cfg['ignore_filter_errors']) && \is_bool($cfg['ignore_filter_errors'])) {
            $parserCfg['ignore_filter_errors'] = $cfg['ignore_filter_errors'];
        }

        return $parserCfg;
    }

    /**
     * Return the unique document identifier.
     *
     * @return string SHA-256 hash of the raw PDF data.
     */
    public function getId(): string
    {
        return $this->docId;
    }

    /**
     * Return the trailer array.
     *
     * @phpstan-return XrefData['trailer']
     * @return array<string, mixed>
     */
    public function getTrailer(): array
    {
        return $this->xref['trailer'];
    }

    /**
     * Return all xref entries.
     *
     * @return array<string, int>
     */
    public function getXref(): array
    {
        return $this->xref['xref'];
    }

    /**
     * Retrieve a specific parsed object by its source reference ("objnum_generation").
     *
     * @param string $ref Source object reference.
     *
     * @phpstan-return array<int, RawObjectArray>
     * @return array<int, mixed>
     *
     * @throws ImportCorruptedSourceException If the reference does not exist.
     */
    public function getObject(string $ref): array
    {
        return (
            $this->objects[$ref] ?? throw new ImportCorruptedSourceException('Object not found in source PDF: ' . $ref)
        );
    }

    /**
     * Retrieve a specific parsed object by its source reference, or null if it does not exist.
     *
     * @param string $ref Source object reference.
     *
     * @phpstan-return array<int, RawObjectArray>|null
     * @return array<int, mixed>|null
     */
    public function findObject(string $ref): ?array
    {
        return $this->objects[$ref] ?? null;
    }

    /**
     * Resolve an indirect reference string ("objnum generation R") to a source object ref key.
     * Returns the "objnum_generation" form, e.g. "3_0".
     *
     * @param string $refStr Raw reference string from the parsed PDF (e.g. "3 0 R").
     *
     * @return string Object ref key (e.g. "3_0").
     *
     * @throws ImportCorruptedSourceException On malformed reference.
     */
    public static function refToKey(string $refStr): string
    {
        $refStr = \trim($refStr);
        $mtch = [];
        if (\preg_match('/^(\d+)\s+(\d+)\s+R$/', $refStr, $mtch) === 1 && isset($mtch[1], $mtch[2])) {
            return $mtch[1] . '_' . $mtch[2];
        }

        // Already in "num_gen" key form?
        if (\preg_match('/^\d+_\d+$/', $refStr)) {
            return $refStr;
        }

        throw new ImportCorruptedSourceException('Invalid indirect reference: ' . $refStr);
    }

    /**
     * Return the total number of objects in the parsed document.
     *
     * @return int
     */
    public function objectCount(): int
    {
        return \count($this->objects);
    }
}
