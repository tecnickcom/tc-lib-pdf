<?php

/**
 * ImporterTest.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test\Import;

use Com\Tecnick\File\File as ObjFile;
use Com\Tecnick\Pdf\Import\FontInspector;
use Com\Tecnick\Pdf\Import\ImportCorruptedSourceException;
use Com\Tecnick\Pdf\Import\Importer;
use Com\Tecnick\Pdf\Import\ImportPageOutOfRangeException;
use Com\Tecnick\Pdf\Import\ImportSourceNotFoundException;
use Com\Tecnick\Pdf\Import\ImportUnsupportedFeatureException;
use Com\Tecnick\Pdf\Import\ObjectMap;
use Com\Tecnick\Pdf\Import\PageTemplate;
use Com\Tecnick\Pdf\Import\SourceDocument;
use PHPUnit\Framework\TestCase;

class ImporterTest extends TestCase
{
    private function getObjectProperty(object $obj, string $name): mixed
    {
        $ref = new \ReflectionClass($obj);
        while ($ref !== false) {
            if ($ref->hasProperty($name)) {
                return $ref->getProperty($name)->getValue($obj);
            }

            $ref = $ref->getParentClass();
        }

        $this->fail('Property not found: ' . $name);
    }

    private function setObjectProperty(object $obj, string $name, mixed $value): void
    {
        $ref = new \ReflectionClass($obj);
        while ($ref !== false) {
            if ($ref->hasProperty($name)) {
                $ref->getProperty($name)->setValue($obj, $value);
                return;
            }

            $ref = $ref->getParentClass();
        }

        $this->fail('Property not found: ' . $name);
    }

    private function invokeImporterMethod(Importer $importer, string $method, mixed ...$args): mixed
    {
        $ref = new \ReflectionClass($importer);
        return $ref->getMethod($method)->invokeArgs($importer, $args);
    }

    private function fixtureData(): string
    {
        $path = __DIR__ . '/../fixtures/simple_import.pdf';
        $data = file_get_contents($path);
        $this->assertNotFalse($data);
        return $data;
    }

    private function multipageFixtureData(): string
    {
        $path = __DIR__ . '/../fixtures/multipage_import.pdf';
        $data = file_get_contents($path);
        $this->assertNotFalse($data);
        return $data;
    }

    private function encryptedFixtureData(): string
    {
        $path = __DIR__ . '/../fixtures/encrypted_import_stub.pdf';
        $data = file_get_contents($path);
        $this->assertNotFalse($data);
        return $data;
    }

    private function rotatedFixtureData(): string
    {
        $path = __DIR__ . '/../fixtures/rotated_import.pdf';
        $data = file_get_contents($path);
        $this->assertNotFalse($data);
        return $data;
    }

    /** @throws \Throwable */
    private function makeObjFile(): ObjFile
    {
        return new ObjFile(allowedPaths: ['*']);
    }

    /** @throws \Throwable */
    private function makeImporter(): Importer
    {
        $xobjects = [];
        $pon = 0;
        return new Importer($xobjects, $pon, $this->makeObjFile());
    }

    /**
     * Build a minimal classic-xref donor PDF with a configurable declared
     * /Count and a configurable number of real pages, to verify that the
     * declared value never drives page counting or import loops.
     * Only small values must ever be used here.
     */
    private function buildDonorPdf(?int $declaredCount, int $realPages): string
    {
        $objects = [];
        $objects[1] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        $kids = [];
        for ($idx = 0; $idx < $realPages; ++$idx) {
            $kids[] = (3 + $idx) . ' 0 R';
        }

        $countEntry = $declaredCount === null ? '' : ' /Count ' . $declaredCount;
        $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [" . implode(' ', $kids) . ']' . $countEntry . " >>\nendobj\n";
        for ($idx = 0; $idx < $realPages; ++$idx) {
            $num = 3 + $idx;
            $objects[$num] =
                $num . " 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] /Resources << >> >>\nendobj\n";
        }

        $pdf = "%PDF-1.7\n";
        $offsets = [];
        foreach ($objects as $num => $text) {
            $offsets[$num] = strlen($pdf);
            $pdf .= $text;
        }

        $xrefStart = strlen($pdf);
        $size = count($objects) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        foreach (array_keys($objects) as $num) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$num]);
        }

        return $pdf . "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$xrefStart}\n%%EOF";
    }

    /**
     * Build a donor PDF whose objects form a chain of indirect /Length references.
     *
     * @param int $chain Number of chained stream objects.
     */
    private function buildLengthChainPdf(int $chain): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] /Resources << >> >>',
        ];

        for ($idx = 0; $idx < $chain; ++$idx) {
            $objects[4 + $idx] = '<< /Length ' . (5 + $idx) . " 0 R >>\nstream\nab\nendstream";
        }

        $objects[4 + $chain] = '2';

        $pdf = "%PDF-1.7\n";
        $offsets = [];
        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= $num . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefStart = strlen($pdf);
        $size = count($objects) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        foreach (array_keys($objects) as $num) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$num]);
        }

        return $pdf . "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$xrefStart}\n%%EOF";
    }

    /**
     * Build a one-page PDF whose page resources reference the given number of Form XObjects.
     *
     * @param int $forms Number of Form XObjects listed in the page resources.
     */
    private function buildWideResourcePdf(int $forms): string
    {
        $refs = [];
        for ($idx = 0; $idx < $forms; ++$idx) {
            $refs[] = '/X' . $idx . ' ' . (5 + $idx) . ' 0 R';
        }

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 =>
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] /Contents 4 0 R'
                    . ' /Resources << /XObject << '
                    . implode(' ', $refs)
                    . ' >> >> >>',
            4 => "<< /Length 0 >>\nstream\n\nendstream",
        ];

        for ($idx = 0; $idx < $forms; ++$idx) {
            $objects[5 + $idx] =
                '<< /Type /XObject /Subtype /Form /BBox [0 0 1 1]'
                . " /Resources << /ProcSet [/PDF] >> /Length 0 >>\nstream\n\nendstream";
        }

        $pdf = "%PDF-1.7\n";
        $offsets = [];
        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= $num . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefStart = strlen($pdf);
        $size = count($objects) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        foreach (array_keys($objects) as $num) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$num]);
        }

        return $pdf . "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$xrefStart}\n%%EOF";
    }

    /**
     * Assemble a classic-xref PDF from a map of object number to object body.
     *
     * @param array<int, string> $objects Object bodies keyed by object number.
     */
    private function assemblePdf(array $objects): string
    {
        $pdf = "%PDF-1.7\n";
        $xrefRows = '';
        foreach ($objects as $num => $body) {
            $xrefRows .= sprintf("%010d 00000 n \n", strlen($pdf));
            $pdf .= $num . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefStart = strlen($pdf);
        $size = count($objects) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n" . $xrefRows;

        return $pdf . "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$xrefStart}\n%%EOF";
    }

    /**
     * Build a one-page PDF whose /Contents is an indirect reference to the
     * object given as the page content value.
     *
     * @param string             $contentsValue Value written as the page /Contents entry.
     * @param array<int, string> $extra         Additional objects, keyed by object number.
     */
    private function buildIndirectContentsPdf(string $contentsValue, array $extra): string
    {
        return $this->assemblePdf([
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 100] /Contents ' . $contentsValue . ' >>',
        ] + $extra);
    }

    /**
     * Build the body of a plain (unfiltered) stream object.
     */
    private function plainStreamObject(string $bytes): string
    {
        return '<< /Length ' . strlen($bytes) . " >>\nstream\n" . $bytes . "\nendstream";
    }

    /** @throws \Throwable */
    public function testImportPageResolvesContentsReferenceToStreamArray(): void
    {
        // /Contents 4 0 R where object 4 is [5 0 R 6 0 R], not a stream.
        $data = $this->buildIndirectContentsPdf('4 0 R', [
            4 => '[5 0 R 6 0 R]',
            5 => $this->plainStreamObject('10 20 m 30 40 l S'),
            6 => $this->plainStreamObject('50 60 m 70 80 l S'),
        ]);

        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $importer->importPage($srcId, 1);
        $out = $importer->getOutImportedObjects();

        $this->assertStringContainsString('10 20 m 30 40 l S', $out);
        $this->assertStringContainsString('50 60 m 70 80 l S', $out);
        $this->assertStringNotContainsString('/Length 0 >>', $out);
        $this->assertSame([], $importer->getWarnings());
    }

    /** @throws \Throwable */
    public function testImportPageResolvesContentsReferenceToSingleStreamArray(): void
    {
        $data = $this->buildIndirectContentsPdf('4 0 R', [
            4 => '[5 0 R]',
            5 => $this->plainStreamObject('10 20 m 30 40 l S'),
        ]);

        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $importer->importPage($srcId, 1);

        $this->assertStringContainsString('10 20 m 30 40 l S', $importer->getOutImportedObjects());
        $this->assertSame([], $importer->getWarnings());
    }

    /** @throws \Throwable */
    public function testImportPageReportsUnextractableContentStream(): void
    {
        // /Contents points to a dictionary that carries no stream.
        $data = $this->buildIndirectContentsPdf('4 0 R', [4 => '<< /Type /Metadata >>']);

        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $importer->importPage($srcId, 1);

        $this->assertStringContainsString('has a /Contents entry but no content stream could be extracted', implode(
            "\n",
            $importer->getWarnings(),
        ));
    }

    /** @throws \Throwable */
    public function testImportPageDoesNotReportLegallyEmptyContentStream(): void
    {
        $data = $this->buildIndirectContentsPdf('4 0 R', [4 => "<< /Length 0 >>\nstream\n\nendstream"]);

        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $importer->importPage($srcId, 1);

        $this->assertSame([], $importer->getWarnings());
    }

    /** @throws \Throwable */
    public function testImportPageDoesNotReportEmptyContentsArray(): void
    {
        // An empty /Contents array is a legal empty content: the page is blank by design.
        $data = $this->buildIndirectContentsPdf('4 0 R', [4 => '[]']);

        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $importer->importPage($srcId, 1);

        $this->assertSame([], $importer->getWarnings());
    }

    /** @throws \Throwable */
    public function testImportPageKeepsStreamWithFalseEndstreamMarkerInPayload(): void
    {
        // An indirect /Length that cannot be resolved makes the parser cut the payload at the
        // "endstream" marker it contains and tokenize the rest: the object is still a stream.
        $data = $this->buildIndirectContentsPdf('4 0 R', [
            4 => "<< /Length 99 0 R >>\nstream\nq endstream [(a) (b)] Q\nendstream",
        ]);

        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $importer->importPage($srcId, 1);

        $this->assertStringContainsString('q ', $importer->getOutImportedObjects());
        $this->assertSame([], $importer->getWarnings());
    }

    /** @throws \Throwable */
    public function testTruncatedEmbeddedFontWalkIsReported(): void
    {
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile(), requireEmbeddedFonts: true);

        $srcId = $importer->setImportSourceData($this->buildWideResourcePdf(FontInspector::MAX_RESOURCE_NODES + 1));
        $importer->importPage($srcId, 1);

        $warnings = implode("\n", $importer->getWarnings());

        $this->assertStringContainsString(
            'stopped after ' . FontInspector::MAX_RESOURCE_NODES . ' resource dictionaries',
            $warnings,
        );
    }

    /** @throws \Throwable */
    public function testCompleteEmbeddedFontWalkIsNotReported(): void
    {
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile(), requireEmbeddedFonts: true);

        $srcId = $importer->setImportSourceData($this->buildWideResourcePdf(4));
        $importer->importPage($srcId, 1);

        $this->assertSame([], $importer->getWarnings());
    }

    /** @throws \Throwable */
    public function testParserLimitsAreReportedAsImporterWarnings(): void
    {
        $importer = $this->makeImporter();
        $importer->setImportSourceData($this->buildLengthChainPdf(20), ['max_resolution_depth' => 4]);

        $warnings = $importer->getWarnings();

        $this->assertCount(1, $warnings);
        $this->assertStringContainsString('was not fully resolved while parsing', $warnings[0] ?? '');
        $this->assertStringContainsString('resolution depth limit (4)', $warnings[0] ?? '');
    }

    /** @throws \Throwable */
    public function testParserLimitWarningsAreNotDuplicatedPerSource(): void
    {
        $data = $this->buildLengthChainPdf(20);
        $importer = $this->makeImporter();
        $importer->setImportSourceData($data, ['max_resolution_depth' => 4]);
        $importer->setImportSourceData($data, ['max_resolution_depth' => 4]);

        $this->assertCount(1, $importer->getWarnings());
    }

    /** @throws \Throwable */
    public function testNoWarningIsReportedWhenNoLimitIsReached(): void
    {
        $importer = $this->makeImporter();
        $importer->setImportSourceData($this->buildLengthChainPdf(20));

        $this->assertSame([], $importer->getWarnings());
    }

    /** @throws \Throwable */
    public function testSetImportSourceDataReturnsSha256Id(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $this->assertSame(hash('sha256', $data), $srcId);
    }

    /** @throws \Throwable */
    public function testSetImportSourceFileReturnsSourceId(): void
    {
        $path = __DIR__ . '/../fixtures/simple_import.pdf';
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceFile($path);
        $this->assertNotEmpty($srcId);
    }

    /** @throws \Throwable */
    public function testSetImportSourceFileThrowsForMissingFile(): void
    {
        $importer = $this->makeImporter();
        $this->expectException(ImportSourceNotFoundException::class);
        $importer->setImportSourceFile('/nonexistent/path/to/file.pdf');
    }

    /** @throws \Throwable */
    public function testSetImportSourceDataIsIdempotent(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $id1 = $importer->setImportSourceData($data);
        $id2 = $importer->setImportSourceData($data);
        $this->assertSame($id1, $id2);
    }

    /** @throws \Throwable */
    public function testSetImportSourceDataAcceptsPasswordOptionForUnencryptedPdf(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data, ['password' => 'secret']);
        $this->assertNotEmpty($srcId);
    }

    /** @throws \Throwable */
    public function testSetImportSourceDataThrowsForEncryptedPdf(): void
    {
        $data = $this->encryptedFixtureData();
        $importer = $this->makeImporter();
        $this->expectException(ImportUnsupportedFeatureException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('encrypted PDF', '/') . '/');
        $importer->setImportSourceData($data);
    }

    /** @throws \Throwable */
    public function testSetImportSourceDataWithPasswordStillThrowsForEncryptedPdf(): void
    {
        $data = $this->encryptedFixtureData();
        $importer = $this->makeImporter();
        $this->expectException(ImportUnsupportedFeatureException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('password-based import is not supported', '/') . '/');
        $importer->setImportSourceData($data, ['password' => 'secret']);
    }

    /** @throws \Throwable */
    public function testGetSourcePageCountReturnsOne(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $this->assertSame(1, $importer->getSourcePageCount($srcId));
    }

    /** @throws \Throwable */
    public function testGetSourcePageCountThrowsForUnknownSource(): void
    {
        $importer = $this->makeImporter();
        $this->expectException(ImportSourceNotFoundException::class);
        $importer->getSourcePageCount('invalid-source-id');
    }

    /** @throws \Throwable */
    public function testGetSourcePageCountIgnoresForgedOversizedCount(): void
    {
        // The donor declares /Count 50 but only one page is reachable
        // through /Kids; the declared value must not be trusted.
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($this->buildDonorPdf(50, 1));
        $this->assertSame(1, $importer->getSourcePageCount($srcId));
    }

    /** @throws \Throwable */
    public function testGetSourcePageCountIgnoresUndersizedCount(): void
    {
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($this->buildDonorPdf(1, 2));
        $this->assertSame(2, $importer->getSourcePageCount($srcId));
    }

    /** @throws \Throwable */
    public function testGetSourcePageCountWorksWithoutDeclaredCount(): void
    {
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($this->buildDonorPdf(null, 2));
        $this->assertSame(2, $importer->getSourcePageCount($srcId));
    }

    /** @throws \Throwable */
    public function testImportPageReturnsPageTemplate(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);
        $tpl = $importer->importPage($srcId, 1);
        $this->assertInstanceOf(PageTemplate::class, $tpl);
    }

    /** @throws \Throwable */
    public function testImportPageRegistersXobject(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);
        $tpl = $importer->importPage($srcId, 1);
        $this->assertArrayHasKey($tpl->getXobjId(), $xobjects);
    }

    /** @throws \Throwable */
    public function testImportPageRebuildsMissingObjectMapForKnownSource(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);

        $this->setObjectProperty($importer, 'objectMaps', []);

        $tpl = $importer->importPage($srcId, 1, ['cache' => false]);
        /** @var array<string, ObjectMap> $maps */
        $maps = $this->getObjectProperty($importer, 'objectMaps');

        $this->assertInstanceOf(PageTemplate::class, $tpl);
        $this->assertIsArray($maps);
        $this->assertArrayHasKey($srcId, $maps);
        $this->assertInstanceOf(ObjectMap::class, $maps[$srcId] ?? null);
    }

    /** @throws \Throwable */
    public function testImportPageXobjectHasCorrectObjectNumber(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);
        $tpl = $importer->importPage($srcId, 1);
        // The xobject's object number must be a positive integer allocated from pon.
        $xobjId = $tpl->getXobjId();
        $xobj = [];
        if (isset($xobjects[$xobjId]) && \is_array($xobjects[$xobjId])) {
            $xobj = $xobjects[$xobjId];
        }
        $this->assertIsArray($xobj);
        $this->assertArrayHasKey('n', $xobj);
        $this->assertGreaterThan(0, $xobj['n'] ?? 0);
    }

    /** @throws \Throwable */
    public function testImportPageSwapsDimensionsForQuarterTurnRotation(): void
    {
        $data = $this->rotatedFixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);

        $tpl = $importer->importPage($srcId, 1);

        $this->assertSame(90, $tpl->getRotation());
        $this->assertSame(500.0, $tpl->getWidth());
        $this->assertSame(300.0, $tpl->getHeight());
    }

    /** @throws \Throwable */
    public function testImportPageCanIgnoreRotationWhenRequested(): void
    {
        $data = $this->rotatedFixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);

        $tpl = $importer->importPage($srcId, 1, ['respectRotation' => false, 'cache' => false]);

        $this->assertSame(0, $tpl->getRotation());
        $this->assertSame(300.0, $tpl->getWidth());
        $this->assertSame(500.0, $tpl->getHeight());
    }

    /** @throws \Throwable */
    public function testImportPageTemplateHasExpectedDimensions(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);
        $tpl = $importer->importPage($srcId, 1);
        // fixture mediabox is 612x792; cropbox falls back to mediabox
        $this->assertEqualsWithDelta(612.0, $tpl->getWidth(), 0.01);
        $this->assertEqualsWithDelta(792.0, $tpl->getHeight(), 0.01);
    }

    /** @throws \Throwable */
    public function testImportPageCacheReturnsIdenticalTemplate(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);
        $tpl1 = $importer->importPage($srcId, 1);
        $tpl2 = $importer->importPage($srcId, 1);
        $this->assertSame($tpl1->getXobjId(), $tpl2->getXobjId());
    }

    /** @throws \Throwable */
    public function testImportPageThrowsForOutOfRangePage(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $this->expectException(ImportPageOutOfRangeException::class);
        $importer->importPage($srcId, 999);
    }

    /** @throws \Throwable */
    public function testImportPageThrowsForUnknownSourceId(): void
    {
        $importer = $this->makeImporter();
        $this->expectException(ImportSourceNotFoundException::class);
        $importer->importPage('unknown-id', 1);
    }

    /** @throws \Throwable */
    public function testGetOutImportedObjectsReturnsNonEmptyString(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);
        $importer->importPage($srcId, 1);
        $out = $importer->getOutImportedObjects();
        $this->assertNotEmpty($out);
        $this->assertStringContainsString(' 0 obj', $out);
        $this->assertStringContainsString('endobj', $out);
    }

    /** @throws \Throwable */
    public function testGetOutImportedObjectsClearsQueue(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);
        $importer->importPage($srcId, 1);
        $importer->getOutImportedObjects();
        $this->assertSame('', $importer->getOutImportedObjects());
    }

    /** @throws \Throwable */
    public function testCleanUpReleasesState(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $importer->cleanUp();
        $this->expectException(ImportSourceNotFoundException::class);
        $importer->getSourcePageCount($srcId);
    }

    /** @throws \Throwable */
    public function testCleanUpClearsPageIndexCache(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $this->assertSame(1, $importer->getSourcePageCount($srcId));
        $importer->cleanUp();
        $this->assertSame([], $this->getObjectProperty($importer, 'pageIndexes'));
    }

    /** @throws \Throwable */
    public function testSelectBoxFallsBackToMediaBoxWhenRequestedBoxIsMissing(): void
    {
        $importer = $this->makeImporter();

        /** @var array{0: float, 1: float, 2: float, 3: float} $box */
        $box = $this->invokeImporterMethod(
            $importer,
            'selectBox',
            [
                'mediaBox' => [10, 20, 210, 420],
            ],
            'BleedBox',
        );

        $this->assertSame([10.0, 20.0, 210.0, 420.0], $box);
    }

    /** @throws \Throwable */
    public function testSelectBoxReturnsZeroBoxForInvalidCoordinates(): void
    {
        $importer = $this->makeImporter();

        /** @var array{0: float, 1: float, 2: float, 3: float} $box */
        $box = $this->invokeImporterMethod(
            $importer,
            'selectBox',
            [
                'cropBox' => [0, 1, 2, 'bad'],
            ],
            'CropBox',
        );

        $this->assertSame([0.0, 0.0, 0.0, 0.0], $box);
    }

    /** @throws \Throwable */
    public function testRotationMatrixSupportsHalfAndThreeQuarterTurns(): void
    {
        $importer = $this->makeImporter();

        /** @var array<int, float> $halfTurn */
        $halfTurn = $this->invokeImporterMethod($importer, 'rotationMatrix', 180, 200.0, 400.0);
        /** @var array<int, float> $threeQuarterTurn */
        $threeQuarterTurn = $this->invokeImporterMethod($importer, 'rotationMatrix', 270, 200.0, 400.0);
        /** @var array<int, float> $negativeQuarterTurn */
        $negativeQuarterTurn = $this->invokeImporterMethod($importer, 'rotationMatrix', -90, 200.0, 400.0);

        $this->assertSame([-1.0, 0.0, 0.0, -1.0, 200.0, 400.0], $halfTurn);
        $this->assertSame([0.0, 1.0, -1.0, 0.0, 400.0, 0.0], $threeQuarterTurn);
        $this->assertSame($threeQuarterTurn, $negativeQuarterTurn);
    }

    /** @throws \Throwable */
    public function testGetSourcePageCountThrowsWhenRootDictionaryHasNoPagesEntry(): void
    {
        $importer = $this->makeImporter();
        $sourceId = 'stub-source';
        $src = $this->createStub(SourceDocument::class);

        $src->method('getTrailer')->willReturn(['root' => '1 0 R']);
        $src->method('getObject')->willReturnCallback(static fn(string $ref): array => match ($ref) {
            '1_0' => [[
                '<<',
                [
                    ['/', 'Type'],
                    ['/', 'Catalog'],
                ],
            ]],
            default => [],
        });

        $this->setObjectProperty($importer, 'sources', [$sourceId => $src]);

        $this->expectException(ImportCorruptedSourceException::class);
        $importer->getSourcePageCount($sourceId);
    }

    /** @throws \Throwable */
    public function testGetSourcePageCountReturnsZeroForEmptyPageTree(): void
    {
        $importer = $this->makeImporter();
        $sourceId = 'stub-source';
        $src = $this->createStub(SourceDocument::class);

        $src->method('getTrailer')->willReturn(['root' => '1 0 R']);
        $src->method('getObject')->willReturnCallback(static fn(string $ref): array => match ($ref) {
            '1_0' => [[
                '<<',
                [
                    ['/', 'Pages'],
                    ['objref', '2 0 R'],
                ],
            ]],
            '2_0' => [[
                '<<',
                [
                    ['/', 'Type'],
                    ['/', 'Pages'],
                    ['/', 'Kids'],
                    ['[', []],
                ],
            ]],
            default => [],
        });

        $this->setObjectProperty($importer, 'sources', [$sourceId => $src]);

        $this->assertSame(0, $importer->getSourcePageCount($sourceId));
    }

    /** @throws \Throwable */
    public function testGetSourcePageCountThrowsWhenPagesNodeHasNoKids(): void
    {
        $importer = $this->makeImporter();
        $sourceId = 'stub-source';
        $src = $this->createStub(SourceDocument::class);

        $src->method('getTrailer')->willReturn(['root' => '1 0 R']);
        $src->method('getObject')->willReturnCallback(static fn(string $ref): array => match ($ref) {
            '1_0' => [[
                '<<',
                [
                    ['/', 'Pages'],
                    ['objref', '2 0 R'],
                ],
            ]],
            '2_0' => [[
                '<<',
                [
                    ['/', 'Type'],
                    ['/', 'Pages'],
                ],
            ]],
            default => [],
        });

        $this->setObjectProperty($importer, 'sources', [$sourceId => $src]);

        $this->expectException(ImportCorruptedSourceException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('/Kids', '/') . '/');
        $importer->getSourcePageCount($sourceId);
    }

    /** @throws \Throwable */
    public function testGetSourcePageCountIsCachedPerSource(): void
    {
        $importer = $this->makeImporter();
        $sourceId = 'stub-source';
        $src = $this->createStub(SourceDocument::class);
        $calls = 0;

        $src->method('getTrailer')->willReturn(['root' => '1 0 R']);
        $src->method('getObject')->willReturnCallback(static function (string $ref) use (&$calls): array {
            ++$calls;
            return match ($ref) {
                '1_0' => [[
                    '<<',
                    [
                        ['/', 'Pages'],
                        ['objref', '2 0 R'],
                    ],
                ]],
                '2_0' => [[
                    '<<',
                    [
                        ['/', 'Type'],
                        ['/', 'Pages'],
                        ['/', 'Kids'],
                        [
                            '[',
                            [
                                ['objref', '3 0 R'],
                            ],
                        ],
                    ],
                ]],
                '3_0' => [[
                    '<<',
                    [
                        ['/', 'Type'],
                        ['/', 'Page'],
                    ],
                ]],
                default => [],
            };
        });

        $this->setObjectProperty($importer, 'sources', [$sourceId => $src]);

        $this->assertSame(1, $importer->getSourcePageCount($sourceId));
        $callsAfterFirst = $calls;
        $this->assertGreaterThan(0, $callsAfterFirst);
        $this->assertSame(1, $importer->getSourcePageCount($sourceId));
        $this->assertSame($callsAfterFirst, $calls);
    }

    // -------------------------------------------------------------------------
    // importPages
    // -------------------------------------------------------------------------

    /** @throws \Throwable */
    public function testImportPagesWithNullRangeImportsAllPages(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $templates = $importer->importPages($srcId);
        // Fixture has one page.
        $this->assertCount(1, $templates);
        assert(isset($templates[0]), "\$templates[0] must be set");
        $this->assertInstanceOf(PageTemplate::class, $templates[0]);
    }

    /** @throws \Throwable */
    public function testImportPagesNullRangeIgnoresForgedOversizedCount(): void
    {
        // With a forged /Count the null range must import only the pages
        // actually reachable through /Kids, without materializing any
        // /Count-sized structure.
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($this->buildDonorPdf(50, 1));
        $templates = $importer->importPages($srcId, null);
        $this->assertCount(1, $templates);
        assert(isset($templates[0]), "\$templates[0] must be set");
        $this->assertInstanceOf(PageTemplate::class, $templates[0]);
    }

    /** @throws \Throwable */
    public function testImportPagesRangeBeyondReachablePagesThrows(): void
    {
        // The bounds check must use the verified page count, not the
        // forged declared /Count.
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($this->buildDonorPdf(50, 1));
        $this->expectException(ImportPageOutOfRangeException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('out of range [1,1]', '/') . '/');
        $importer->importPages($srcId, [2]);
    }

    /** @throws \Throwable */
    public function testImportPagesWithExplicitRange(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $templates = $importer->importPages($srcId, [1]);
        $this->assertCount(1, $templates);
        assert(isset($templates[0]), "\$templates[0] must be set");
        $this->assertInstanceOf(PageTemplate::class, $templates[0]);
    }

    /** @throws \Throwable */
    public function testImportPagesMatchesImportPageResult(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);

        $single = $importer->importPage($srcId, 1);
        $batch = $importer->importPages($srcId, [1]);

        assert(isset($batch[0]), "\$batch[0] must be set");
        // Same page imported again (cache hit) - must return the exact same template.
        $this->assertSame($single->getXobjId(), $batch[0]->getXobjId());
    }

    /**
     * Counting the pages and importing all of them must walk the page tree
     * exactly once per source: the flattened page index built on first use is
     * reused for every subsequent page resolution.
     *
     * @throws \Throwable
     */
    public function testImportPagesWalksPageTreeOnlyOnce(): void
    {
        $importer = $this->makeImporter();
        $src = new class($this->buildDonorPdf(null, 2)) extends SourceDocument {
            /** @var array<string, int> */
            public array $fetches = [];

            public function getObject(string $ref): array
            {
                $this->fetches[$ref] = ($this->fetches[$ref] ?? 0) + 1;
                return parent::getObject($ref);
            }
        };
        $sourceId = 'counting-source';
        $this->setObjectProperty($importer, 'sources', [$sourceId => $src]);

        $this->assertSame(2, $importer->getSourcePageCount($sourceId));
        $templates = $importer->importPages($sourceId);

        $this->assertCount(2, $templates);
        // Catalog, /Pages node, and each page leaf fetched exactly once for
        // the count plus the whole batch import.
        $this->assertSame(1, $src->fetches['1_0'] ?? 0);
        $this->assertSame(1, $src->fetches['2_0'] ?? 0);
        $this->assertSame(1, $src->fetches['3_0'] ?? 0);
        $this->assertSame(1, $src->fetches['4_0'] ?? 0);
    }

    /** @throws \Throwable */
    public function testImportPagesThrowsForUnknownSource(): void
    {
        $importer = $this->makeImporter();
        $this->expectException(ImportSourceNotFoundException::class);
        $importer->importPages('unknown-id');
    }

    /** @throws \Throwable */
    public function testImportPagesThrowsForOutOfRangePage(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $this->expectException(ImportPageOutOfRangeException::class);
        $importer->importPages($srcId, [1, 999]);
    }

    // -------------------------------------------------------------------------
    // Dedup: repeated import without cache must not inflate pon
    // -------------------------------------------------------------------------

    /** @throws \Throwable */
    public function testRepeatedImportNoCacheUsesSharedObjectMap(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);

        // First import (no cache): allocates objects from the source.
        $importer->importPage($srcId, 1, ['cache' => false]);
        $ponAfterFirst = $pon;
        $this->assertGreaterThan(0, $ponAfterFirst);

        // Second import of the same page (cache off): shared resources are already
        // in the ObjectMap - only the new Form XObject itself increments pon.
        $importer->importPage($srcId, 1, ['cache' => false]);
        $ponAfterSecond = $pon;

        // pon must have increased by exactly 1 (the new XObject), not by the full
        // resource set again.
        $this->assertSame(1, $ponAfterSecond - $ponAfterFirst);
    }

    /** @throws \Throwable */
    public function testRepeatedImportNoCacheDoesNotDuplicateAuxObjects(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);

        $importer->importPage($srcId, 1, ['cache' => false]);
        $importer->importPage($srcId, 1, ['cache' => false]);

        $out = $importer->getOutImportedObjects();
        // Count occurrences of the font object serialized string to verify
        // it appears exactly once (dedup works).
        $xobjCount = \substr_count($out, '/Type /XObject');
        $this->assertSame(2, $xobjCount, 'Each non-cached import should produce exactly one XObject');

        // The font object (5_0) should be written exactly once despite two imports.
        $fontCount = \substr_count($out, '/Type /Font');
        $this->assertSame(1, $fontCount, 'Shared font object must not be duplicated across imports');
    }

    // -------------------------------------------------------------------------
    // Multi-page fixture tests
    // -------------------------------------------------------------------------

    /** @throws \Throwable */
    public function testGetSourcePageCountMultipage(): void
    {
        $data = $this->multipageFixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $this->assertSame(2, $importer->getSourcePageCount($srcId));
    }

    /** @throws \Throwable */
    public function testImportPagesNullRangeImportsAllMultipagePages(): void
    {
        $data = $this->multipageFixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);
        $templates = $importer->importPages($srcId);
        $this->assertCount(2, $templates);
        assert(isset($templates[0]), "\$templates[0] must be set");
        $this->assertInstanceOf(PageTemplate::class, $templates[0]);
        assert(isset($templates[1]), "\$templates[1] must be set");
        $this->assertInstanceOf(PageTemplate::class, $templates[1]);
    }

    /** @throws \Throwable */
    public function testImportPagesMultipagePartialRange(): void
    {
        $data = $this->multipageFixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $templates = $importer->importPages($srcId, [2]);
        $this->assertCount(1, $templates);
        assert(isset($templates[0]), "\$templates[0] must be set");
        $this->assertEqualsWithDelta(612.0, $templates[0]->getWidth(), 0.01);
    }

    /** @throws \Throwable */
    public function testImportAllPagesMultipageSharedFontNotDuplicated(): void
    {
        $data = $this->multipageFixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon, $this->makeObjFile());
        $srcId = $importer->setImportSourceData($data);
        $importer->importPages($srcId);
        $out = $importer->getOutImportedObjects();

        // Two pages should produce two Form XObjects.
        $this->assertSame(2, \substr_count($out, '/Type /XObject'));
        // The shared font (5_0) must appear exactly once in the output.
        $this->assertSame(1, \substr_count($out, '/Type /Font'));
    }

    /**
     * importPages(null) must import nothing when the source reports a missing or
     * negative page count. Otherwise \range(1, $total) would yield a descending
     * sequence (e.g. [1, 0]) and attempt to import bogus page numbers.
     *
     * @throws \Throwable
     */
    public function testImportPagesWithNonPositivePageCountReturnsEmpty(): void
    {
        $xobjects = [];
        $pon = 0;
        $importer = new class($xobjects, $pon, $this->makeObjFile()) extends Importer {
            public int $fakeCount = 0;

            public function getSourcePageCount(string $sourceId): int
            {
                return $this->fakeCount;
            }
        };

        $importer->fakeCount = 0;
        $this->assertSame([], $importer->importPages('any-source', null));

        $importer->fakeCount = -3;
        $this->assertSame([], $importer->importPages('any-source', null));
    }
}
