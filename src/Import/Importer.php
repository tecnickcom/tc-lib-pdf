<?php

/**
 * Importer.php
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
 * Com\Tecnick\Pdf\Import\Importer
 *
 * Orchestrates PDF import: loads source documents, resolves pages, clones resources,
 * builds Form XObjects, and registers them for deferred output via getOutImportedObjects().
 *
 * @since     2026-05-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TXOBject from \Com\Tecnick\Pdf\Output
 *
 * @phpstan-type ImportOptions array{
 *     box?:           string,
 *     respectRotation?: bool,
 *     groupXObject?:  bool,
 *     cache?:         bool,
 * }
 *
 * @SuppressWarnings("CouplingBetweenObjects")
 */
class Importer implements ImporterInterface
{
    /**
     * Registered source documents keyed by source ID.
     *
     * @var array<string, SourceDocument>
     */
    private array $sources = [];

    /**
     * Object map keyed by source ID (one map per source for cross-page dedup).
     *
     * @var array<string, ObjectMap>
     */
    private array $objectMaps = [];

    /**
     * Cache of already-imported templates keyed by "sourceId:pageNum:box".
     *
     * @var array<string, PageTemplate>
     */
    private array $templateCache = [];

    /**
     * Raw PDF object bytes queued for deferred write, keyed by XObject template ID.
     *
     * @var array<string, string>
     */
    private array $rawObjects = [];

    /**
     * Reference to the destination xobjects registry (same reference as $pdf->xobjects).
     * Written via PHP reference binding; phpstan cannot track reference writes as reads.
     *
     * @var array<string, mixed>
     */
    // @phpstan-ignore-next-line property.onlyWritten (reference binding; writes propagate to caller's array)
    private array $xobjects;

    /**
     * Reference to the destination PDF object-number counter.
     *
     * @var int
     */
    private int $pon;

    /**
     * Constructor.
     *
     * @param array<string, mixed> $xobjects Reference to the destination document's xobjects array.
     * @param int                  $pon      Reference to the PDF object number counter.
     */
    public function __construct(array &$xobjects, int &$pon)
    {
        // Bind by reference so importPage() writes directly into $pdf->xobjects.
        // @phpstan-ignore assign.propertyType
        $this->xobjects = &$xobjects;
        $this->pon = &$pon;
    }

    /**
     * Register a source PDF file.
     *
     * @param string             $path File path to a readable PDF.
     * @param array<string, mixed> $cfg  Optional parser configuration.
     *
     * @return string Source document identifier.
     *
     * @throws ImportSourceNotFoundException   If the file cannot be read.
     * @throws ImportCorruptedSourceException  If the file cannot be parsed.
     * @throws ImportUnsupportedFeatureException If the source is encrypted.
     */
    public function setImportSourceFile(string $path, array $cfg = []): string
    {
        $realPath = \realpath($path);
        if ($realPath === false || !\is_readable($realPath)) {
            throw new ImportSourceNotFoundException('Source PDF file not found or not readable: ' . $path);
        }

        $data = \file_get_contents($realPath);
        if ($data === false) {
            throw new ImportSourceNotFoundException('Unable to read source PDF file: ' . $realPath);
        }

        return $this->setImportSourceData($data, $cfg);
    }

    /**
     * Register a source PDF from raw binary data.
     *
     * @param string              $data Raw PDF binary data.
     * @param array<string, mixed> $cfg  Optional parser configuration.
     *
     * @return string Source document identifier (SHA-256 of the data).
     *
     * @throws ImportCorruptedSourceException    If the data cannot be parsed.
     * @throws ImportUnsupportedFeatureException If the source is encrypted.
     */
    public function setImportSourceData(string $data, array $cfg = []): string
    {
        $doc = new SourceDocument($data, $cfg);
        $srcId = $doc->getId();
        if (!isset($this->sources[$srcId])) {
            $this->sources[$srcId] = $doc;
            $this->objectMaps[$srcId] = new ObjectMap();
        }

        return $srcId;
    }

    /**
     * Return the total number of pages in a registered source document.
     *
     * @param string $sourceId Source document identifier.
     *
     * @return int Total page count.
     *
     * @throws ImportSourceNotFoundException If the source ID is not registered.
     * @throws ImportCorruptedSourceException If the page tree is malformed.
     */
    public function getSourcePageCount(string $sourceId): int
    {
        $src = $this->requireSource($sourceId);
        $trailer = $src->getTrailer();
        $rootRef = SourceDocument::refToKey($trailer['root']);
        $rootObj = $src->getObject($rootRef);
        $rootDict = $this->parseSimpleDict($rootObj);
        if (!isset($rootDict['Pages'])) {
            throw new ImportCorruptedSourceException('PDF /Root is missing /Pages entry.');
        }

        $pagesRef = SourceDocument::refToKey(\is_string($rootDict['Pages']) ? $rootDict['Pages'] : '');
        $pagesObj = $src->getObject($pagesRef);
        $pagesDict = $this->parseSimpleDict($pagesObj);
        $countRaw = $pagesDict['Count'] ?? 0;
        return \is_int($countRaw) ? $countRaw : (\is_numeric($countRaw) ? (int) $countRaw : 0);
    }

    /**
     * Import one page from a registered source document and return a PageTemplate.
     *
     * @param string        $sourceId  Source document identifier.
     * @param int           $pageNum   1-based page number.
     * @param array<string, mixed> $options Import options (box, groupXObject, cache).
     *
     * @return PageTemplateInterface Imported page template.
     *
     * @throws ImportSourceNotFoundException     If the source ID is not registered.
     * @throws ImportPageOutOfRangeException     If the page number is out of range.
     * @throws ImportCorruptedSourceException    If the page tree is malformed.
     * @throws ImportUnsupportedFeatureException If an unsupported feature is encountered.
     */
    public function importPage(string $sourceId, int $pageNum, array $options = []): PageTemplateInterface
    {
        $boxOpt = $options['box'] ?? 'CropBox';
        $useBox = \is_string($boxOpt) ? $boxOpt : 'CropBox';
        $respectRotation = (bool) ($options['respectRotation'] ?? true);
        $useGroup = (bool) ($options['groupXObject'] ?? true);
        $useCache = (bool) ($options['cache'] ?? true);

        $cacheKey = $sourceId . ':' . $pageNum . ':' . $useBox . ':' . ($respectRotation ? 'R1' : 'R0');
        if ($useCache && isset($this->templateCache[$cacheKey])) {
            return $this->templateCache[$cacheKey];
        }

        $src = $this->requireSource($sourceId);
        $resolver = new PageResolver();
        $resolved = $resolver->resolve($src, $pageNum);

        $box = $this->selectBox($resolved, $useBox);
        $rotate = $respectRotation ? $resolved['rotate'] : 0;
        $map = $this->objectMaps[$sourceId];

        // Allocate object number for the Form XObject.
        $xobjNum = ++$this->pon;
        $tid = 'IMP' . $xobjNum;

        // Clone resources.
        $cloner = new ResourceCloner($this->pon);
        $resDict = $cloner->cloneResources($resolved['resources'], $src, $map);
        $this->pon = $cloner->getPon();

        // Extract content stream.
        $contentStream = $cloner->getContentStream($resolved['dict'], $src);
        $this->pon = $cloner->getPon();

        // Flush cloned auxiliary objects.
        $rawAuxObjects = $map->flush();
        if ($rawAuxObjects !== '') {
            $this->rawObjects[$tid . '_aux'] = $rawAuxObjects;
        }

        // Compute BBox and Matrix.
        [$xMin, $yMin, $xMax, $yMax] = $box;
        $bboxW = $xMax - $xMin;
        $bboxH = $yMax - $yMin;
        $matrix = $this->rotationMatrix($rotate, $bboxW, $bboxH);
        $matrixStr = \implode(' ', $matrix);

        // Serialize the Form XObject.
        $streamBytes = $contentStream['bytes'];
        $filterEntry = $contentStream['filter'] !== '' ? ' /Filter ' . $contentStream['filter'] : '';
        $groupEntry = $useGroup ? ' /Group << /Type /Group /S /Transparency >>' : '';

        $xobjOut = $xobjNum . ' 0 obj' . "\n"
            . '<< /Type /XObject /Subtype /Form /FormType 1'
            . \sprintf(' /BBox [%F %F %F %F]', $xMin, $yMin, $xMax, $yMax)
            . ' /Matrix [' . $matrixStr . ']'
            . ' /Resources ' . $resDict
            . $groupEntry
            . $filterEntry
            . ' /Length ' . \strlen($streamBytes)
            . ' >>'
            . "\nstream\n"
            . $streamBytes
            . "\nendstream\n"
            . "endobj\n";

        $this->rawObjects[$tid] = $xobjOut;

        // Register in xobjects so getXObjectDict() emits the resource dict entry.
        // outdata is intentionally empty so getOutXObjects() skips it.
        $this->xobjects[$tid] = [
            'spot_colors' => [],
            'extgstate' => [],
            'gradient' => [],
            'font' => [],
            'image' => [],
            'xobject' => [],
            'annotations' => [],
            'id' => $tid,
            'n' => $xobjNum,
            'x' => 0.0,
            'y' => 0.0,
            'w' => $bboxW,
            'h' => $bboxH,
            'outdata' => '',
            'pheight' => 0.0,
            'gheight' => 0.0,
        ];

        // Determine user-unit dimensions (points → same unit as pon; leave in pt for now).
        $tpl = new PageTemplate(
            $tid,
            $bboxW,
            $bboxH,
            $rotate,
            $sourceId,
            $pageNum,
            [$xMin, $yMin, $xMax, $yMax]
        );

        if ($useCache) {
            $this->templateCache[$cacheKey] = $tpl;
        }

        return $tpl;
    }

    /**
     * Import a range of pages from a registered source document.
     *
     * @param string        $sourceId Source document identifier.
     * @param array<int>|null $range  1-based page numbers to import, or null to import all pages.
     * @param array<string, mixed> $options Import options (same as importPage).
     *
     * @return array<int, PageTemplateInterface> Indexed array of PageTemplate, one per requested page.
     *
     * @throws ImportSourceNotFoundException     If the source ID is not registered.
     * @throws ImportPageOutOfRangeException     If any page number is out of range.
     * @throws ImportCorruptedSourceException    If the page tree is malformed.
     * @throws ImportUnsupportedFeatureException If an unsupported feature is encountered.
     */
    public function importPages(string $sourceId, ?array $range = null, array $options = []): array
    {
        $total = $this->getSourcePageCount($sourceId);

        if ($range === null) {
            $range = \range(1, $total);
        } else {
            foreach ($range as $pageNum) {
                $num = (int) $pageNum;
                if ($num < 1 || $num > $total) {
                    throw new ImportPageOutOfRangeException(
                        'Page number ' . $num . ' is out of range [1,' . $total . '].'
                    );
                }
            }
        }

        $templates = [];
        foreach ($range as $pageNum) {
            $templates[] = $this->importPage($sourceId, (int) $pageNum, $options);
        }

        return $templates;
    }

    /**
     * Flush all queued raw PDF objects to the output stream.
     * Called from getOutPDFBody() after getOutXObjects().
     *
     * @return string Serialized PDF object bytes.
     */
    public function getOutImportedObjects(): string
    {
        $out = '';
        foreach ($this->rawObjects as $data) {
            $out .= $data;
        }

        $this->rawObjects = [];
        return $out;
    }

    /**
     * Release parser memory and cached resources.
     * Should be called after getOutPDFBody() completes.
     */
    public function cleanUp(): void
    {
        $this->sources = [];
        $this->objectMaps = [];
        $this->rawObjects = [];
    }

    /**
     * Return the SourceDocument for a registered source ID.
     *
     * @param string $sourceId Source document identifier.
     *
     * @return SourceDocument
     *
     * @throws ImportSourceNotFoundException If not found.
     */
    private function requireSource(string $sourceId): SourceDocument
    {
        if (!isset($this->sources[$sourceId])) {
            throw new ImportSourceNotFoundException('Source ID not registered: ' . $sourceId);
        }

        return $this->sources[$sourceId];
    }

    /**
     * Select the effective page box from the resolved page.
     *
     * @param array<string, mixed> $resolved Resolved page from PageResolver.
     * @param string               $boxName  Preferred box name.
     *
     * @return array<int, float> Box as [x0, y0, x1, y1].
     */
    private function selectBox(array $resolved, string $boxName): array
    {
        $boxMap = [
            'MediaBox' => 'mediaBox',
            'CropBox'  => 'cropBox',
            'BleedBox' => 'bleedBox',
            'TrimBox'  => 'trimBox',
            'ArtBox'   => 'artBox',
        ];

        $key = $boxMap[$boxName] ?? 'cropBox';
        /** @var array<int, float> $box */
        $box = $resolved[$key] ?? $resolved['mediaBox'];
        return $box;
    }

    /**
     * Compute the CTM matrix for a given rotation angle (0/90/180/270).
     *
     * Rotation matrix
     * 0:   [1  0  0  1  0  0]
     * 90:  [0  1 -1  0  H  0]
     * 180: [-1 0  0 -1  W  H]
     * 270: [0 -1  1  0  0  W]
     *
     * @param int   $rotate Page rotation in degrees.
     * @param float $wid    Page width in points.
     * @param float $hgt    Page height in points.
     *
     * @return array<int, float> Six-element CTM array [a, b, c, d, e, f].
     */
    private function rotationMatrix(int $rotate, float $wid, float $hgt): array
    {
        return match ($rotate % 360) {
            90  => [0.0, 1.0, -1.0, 0.0, $hgt, 0.0],
            180 => [-1.0, 0.0, 0.0, -1.0, $wid, $hgt],
            270 => [0.0, -1.0, 1.0, 0.0, 0.0, $wid],
            default => [1.0, 0.0, 0.0, 1.0, 0.0, 0.0],
        };
    }

    /**
     * Extract a minimal key->value dict from a raw parsed object (for trailer-level lookups).
     *
     * @param array<int, mixed> $objData Raw object data.
     *
     * @return array<string, mixed>
     *
     * @throws ImportCorruptedSourceException If no dictionary found.
     */
    private function parseSimpleDict(array $objData): array
    {
        foreach ($objData as $element) {
            if (!\is_array($element) || ($element[0] ?? '') !== '<<') {
                continue;
            }

            if (!\is_array($element[1] ?? null)) {
                continue;
            }

            $dict = [];
            $raw = $element[1];
            $cnt = \count($raw);
            for ($idx = 0; $idx < $cnt - 1; $idx += 2) {
                $keyEl = $raw[$idx];
                $valEl = $raw[$idx + 1];
                if (!\is_array($keyEl) || ($keyEl[0] ?? '') !== '/') {
                    continue;
                }

                $keyName = $keyEl[1] ?? '';
                $key = \ltrim(\is_string($keyName) ? $keyName : '', '/');
                $val = \is_array($valEl) ? ($valEl[1] ?? null) : $valEl;
                if ($val !== null && !\is_array($val) && \is_scalar($val)) {
                    $dict[$key] = (string) $val;
                }
            }

            return $dict;
        }

        throw new ImportCorruptedSourceException('Expected dictionary object but none found.');
    }
}
