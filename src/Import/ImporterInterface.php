<?php

/**
 * ImporterInterface.php
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
 * Com\Tecnick\Pdf\Import\ImporterInterface
 *
 * Contract for the PDF import orchestrator.
 * Defining this interface decouples the tc-lib-pdf main library from the concrete
 * import implementation, so the Import\ classes can be extracted into a separate
 * tc-lib-pdf-import package without breaking the public API.
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type ImportOptions array{
 *     box?:               string,
 *     respectRotation?:   bool,
 *     groupXObject?:      bool,
 *     cache?:             bool,
 * }
 */
interface ImporterInterface
{
    /**
     * Register a source PDF file and return a stable source identifier.
     *
     * @param string               $path File path to a readable PDF.
     * @param array<string, mixed> $cfg  Optional parser configuration.
     *
     * @return string Source document identifier.
     *
     * @throws ImportSourceNotFoundException     If the file cannot be read.
     * @throws ImportCorruptedSourceException    If the file cannot be parsed.
     * @throws ImportUnsupportedFeatureException If the source is encrypted.
     */
    public function setImportSourceFile(string $path, array $cfg = []): string;

    /**
     * Register a source PDF from raw binary data and return a stable source identifier.
     *
     * @param string               $data Raw PDF binary data.
     * @param array<string, mixed> $cfg  Optional parser configuration.
     *
     * @return string Source document identifier (SHA-256 of the data).
     *
     * @throws ImportCorruptedSourceException    If the data cannot be parsed.
     * @throws ImportUnsupportedFeatureException If the source is encrypted.
     */
    public function setImportSourceData(string $data, array $cfg = []): string;

    /**
     * Return the total number of pages in a registered source document.
     *
     * @param string $sourceId Source document identifier returned by setImportSource*.
     *
     * @return int Total page count.
     *
     * @throws ImportSourceNotFoundException  If the source ID is not registered.
     * @throws ImportCorruptedSourceException If the page tree is malformed.
     */
    public function getSourcePageCount(string $sourceId): int;

    /**
     * Import one page from a registered source document and return a PageTemplateInterface.
     *
     * @param string               $sourceId Source document identifier.
     * @param int                  $pageNum  1-based page number.
     * @param array<string, mixed> $options  Import options (box, groupXObject, cache, respectRotation).
     *
     * @return PageTemplateInterface Imported page template.
     *
     * @throws ImportSourceNotFoundException     If the source ID is not registered.
     * @throws ImportPageOutOfRangeException     If the page number is out of range.
     * @throws ImportCorruptedSourceException    If the page tree is malformed.
     * @throws ImportUnsupportedFeatureException If an unsupported feature is encountered.
     */
    public function importPage(string $sourceId, int $pageNum, array $options = []): PageTemplateInterface;

    /**
     * Import multiple pages from a registered source document.
     *
     * @param string               $sourceId Source document identifier.
     * @param array<int>|null      $range    1-based page numbers to import, or null for all pages.
     * @param array<string, mixed> $options  Import options (same as importPage).
     *
     * @return array<int, PageTemplateInterface> Indexed array, one entry per requested page.
     *
     * @throws ImportSourceNotFoundException     If the source ID is not registered.
     * @throws ImportPageOutOfRangeException     If any page number is out of range.
     * @throws ImportCorruptedSourceException    If the page tree is malformed.
     * @throws ImportUnsupportedFeatureException If an unsupported feature is encountered.
     */
    public function importPages(string $sourceId, ?array $range = null, array $options = []): array;

    /**
     * Flush all queued raw PDF object bytes to the output stream.
     * Must be called during the PDF body write phase (after XObjects have been emitted).
     *
     * @return string Serialized PDF object bytes ready for appending to the output stream.
     */
    public function getOutImportedObjects(): string;

    /**
     * Release parser memory and cached resources.
     * Should be called after getOutImportedObjects() completes.
     */
    public function cleanUp(): void;
}
