<?php

/**
 * ImporterTest.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test\Import;

use Com\Tecnick\File\File as ObjFile;
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

    private function makeObjFile(): ObjFile
    {
        return new ObjFile(allowedPaths: ['*']);
    }

    private function makeImporter(): Importer
    {
        $xobjects = [];
        $pon = 0;
        return new Importer($xobjects, $pon, $this->makeObjFile());
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
    public function testParseSimpleDictSkipsMalformedEntriesAndCollectsScalarValues(): void
    {
        $importer = $this->makeImporter();

        /** @var array<string, mixed> $dict */
        $dict = $this->invokeImporterMethod($importer, 'parseSimpleDict', [
            'junk-token',
            ['stream', 'ignored'],
            [
                '<<',
                [
                    ['not-a-name', 'Skip'],
                    ['numeric', 1],
                    ['/'],
                    ['numeric', 2],
                    ['/', []],
                    ['numeric', 3],
                    ['/', 'MissingInner'],
                    ['string'],
                    ['/', 'Pages'],
                    ['objref', '2 0 R'],
                    ['/', 'Count'],
                    2,
                ],
            ],
        ]);
        $this->assertSame(
            [
                'Pages' => '2 0 R',
                'Count' => '2',
            ],
            $dict,
        );
    }

    /** @throws \Throwable */
    public function testParseSimpleDictThrowsWhenDictionaryTokenIsMissing(): void
    {
        $importer = $this->makeImporter();

        $this->expectException(ImportCorruptedSourceException::class);
        $this->invokeImporterMethod($importer, 'parseSimpleDict', [
            'junk-token',
            ['stream', 'ignored'],
        ]);
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
    public function testGetSourcePageCountReturnsZeroWhenPagesCountIsMissing(): void
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

        $this->assertSame(0, $importer->getSourcePageCount($sourceId));
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
        // Same page imported again (cache hit) — must return the exact same template.
        $this->assertSame($single->getXobjId(), $batch[0]->getXobjId());
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
        // in the ObjectMap — only the new Form XObject itself increments pon.
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
}
