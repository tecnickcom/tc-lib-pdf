<?php

declare(strict_types=1);

/**
 * FontInspector.php
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
 * Com\Tecnick\Pdf\Import\FontInspector
 *
 * Walks the font resources of an imported page and reports the fonts whose
 * program is not embedded in the source document. Such a font is copied into
 * the destination document as it stands, which no conformance mode requiring
 * embedded fonts accepts (ISO 19005-1 clause 6.3.4, ISO 19005-2 and
 * ISO 19005-3 clause 6.2.11.4.1).
 *
 * @since     2026-08-31
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
final class FontInspector
{
    /**
     * Maximum number of resource dictionaries visited in a single walk.
     */
    public const MAX_RESOURCE_NODES = 1024;

    /**
     * The keys of a font descriptor that hold an embedded font program.
     *
     * @var array<int, string>
     */
    private const FONT_FILE_KEYS = ['FontFile', 'FontFile2', 'FontFile3'];

    /**
     * Raw parser object converter.
     */
    private DictParser $dict;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dict = new DictParser();
    }

    /**
     * Return the base font names, in resource order, of the fonts used by the
     * given page resources whose program is not embedded in the source.
     *
     * The walk follows the /Font entries of the page resources and of every
     * nested Form XObject, and stops after MAX_RESOURCE_NODES dictionaries.
     *
     * @param array<string, mixed> $resources Resolved page resource dictionary.
     * @param SourceDocument       $src       Source document the resources belong to.
     *
     * @return array<int, string>
     */
    public function findNonEmbeddedFonts(array $resources, SourceDocument $src): array
    {
        $missing = [];
        $visited = [];
        $queue = [$resources];
        $nodes = 0;
        while ($queue !== []) {
            $node = \array_pop($queue);
            ++$nodes;
            if ($nodes > self::MAX_RESOURCE_NODES) {
                break;
            }

            $fonts = $this->dict->resolveDict($node['Font'] ?? null, $src);
            foreach (\array_keys($fonts) as $fontKey) {
                $name = $this->nonEmbeddedFontName($fonts[$fontKey] ?? null, $src);
                if ($name === '') {
                    continue;
                }

                $missing[$name] = true;
            }

            $this->queueFormResources($node['XObject'] ?? null, $src, $visited, $queue);
        }

        return \array_keys($missing);
    }

    /**
     * Append the resource dictionary of every not yet visited Form XObject to the walk queue.
     *
     * @param mixed                             $xobjects Raw /XObject resource entry.
     * @param SourceDocument                    $src      Source document.
     * @param array<string, true>               $visited  References already queued.
     * @param array<int, array<string, mixed>>  $queue    Walk queue.
     */
    private function queueFormResources(mixed $xobjects, SourceDocument $src, array &$visited, array &$queue): void
    {
        $entries = $this->dict->resolveDict($xobjects, $src);
        foreach (\array_keys($entries) as $entryKey) {
            if (!$this->markVisited($entries[$entryKey] ?? null, $visited)) {
                continue;
            }

            $xobject = $this->dict->resolveDict($entries[$entryKey] ?? null, $src);
            if ($this->nameValue($xobject['Subtype'] ?? null) !== 'Form') {
                continue;
            }

            $nested = $this->dict->resolveDict($xobject['Resources'] ?? null, $src);
            if ($nested === []) {
                continue;
            }

            $queue[] = $nested;
        }
    }

    /**
     * Mark an indirect reference as visited, and return false when it was already
     * visited or is malformed. A value that is not a reference is always accepted.
     *
     * @param mixed               $value   Raw resource entry value.
     * @param array<string, true> $visited References already visited.
     */
    private function markVisited(mixed $value, array &$visited): bool
    {
        if (!\is_string($value)) {
            return true;
        }

        try {
            $key = SourceDocument::refToKey($value);
        } catch (ImportCorruptedSourceException) {
            return false;
        }

        if (isset($visited[$key])) {
            return false;
        }

        $visited[$key] = true;
        return true;
    }

    /**
     * Return the base font name of a font resource whose program is not embedded,
     * or an empty string when the font carries its program.
     *
     * @param mixed          $fontValue Raw /Font resource entry value.
     * @param SourceDocument $src       Source document.
     */
    private function nonEmbeddedFontName(mixed $fontValue, SourceDocument $src): string
    {
        $font = $this->dict->resolveDict($fontValue, $src);
        if ($font === []) {
            return '';
        }

        $subtype = $this->nameValue($font['Subtype'] ?? null);
        if ($subtype === 'Type3') {
            // A Type 3 font defines its glyphs as content streams, so it is self-contained.
            return '';
        }

        $descriptorHolder = $font;
        if ($subtype === 'Type0') {
            $descendants = $this->dict->resolveArray($font['DescendantFonts'] ?? null, $src);
            $descriptorHolder = \array_key_exists(0, $descendants)
                ? $this->dict->resolveDict($descendants[0], $src)
                : [];
        }

        if ($this->hasFontProgram($descriptorHolder, $src)) {
            return '';
        }

        $name = $this->nameValue($font['BaseFont'] ?? null);
        return $name === '' ? 'unnamed font' : $name;
    }

    /**
     * Return true when the font descriptor of the given font dictionary holds an
     * embedded font program.
     *
     * @param array<string, mixed> $font Font or descendant font dictionary.
     * @param SourceDocument       $src  Source document.
     */
    private function hasFontProgram(array $font, SourceDocument $src): bool
    {
        $descriptor = $this->dict->resolveDict($font['FontDescriptor'] ?? null, $src);
        foreach (self::FONT_FILE_KEYS as $key) {
            if (isset($descriptor[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return a PDF name value without its leading solidus, or an empty string
     * when the value is not a name.
     *
     * @param mixed $value Raw dictionary value.
     */
    private function nameValue(mixed $value): string
    {
        return \is_string($value) ? \ltrim($value, '/') : '';
    }
}
