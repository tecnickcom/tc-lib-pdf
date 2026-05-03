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

use Com\Tecnick\Pdf\Import\ImportPageOutOfRangeException;
use Com\Tecnick\Pdf\Import\ImportSourceNotFoundException;
use Com\Tecnick\Pdf\Import\ImportUnsupportedFeatureException;
use Com\Tecnick\Pdf\Import\Importer;
use Com\Tecnick\Pdf\Import\PageTemplate;
use PHPUnit\Framework\TestCase;

class ImporterTest extends TestCase
{
    private function fixtureData(): string
    {
        $path = __DIR__ . '/../fixtures/simple_import.pdf';
        $data = file_get_contents($path);
        $this->assertNotFalse($data);
        return (string) $data;
    }

    private function multipageFixtureData(): string
    {
        $path = __DIR__ . '/../fixtures/multipage_import.pdf';
        $data = file_get_contents($path);
        $this->assertNotFalse($data);
        return (string) $data;
    }

    private function encryptedFixtureData(): string
    {
        $path = __DIR__ . '/../fixtures/encrypted_import_stub.pdf';
        $data = file_get_contents($path);
        $this->assertNotFalse($data);
        return (string) $data;
    }

    private function makeImporter(): Importer
    {
        $xobjects = [];
        $pon = 0;
        return new Importer($xobjects, $pon);
    }

    public function testSetImportSourceDataReturnsSha256Id(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $this->assertSame(hash('sha256', $data), $srcId);
    }

    public function testSetImportSourceFileReturnsSourceId(): void
    {
        $path = __DIR__ . '/../fixtures/simple_import.pdf';
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceFile($path);
        $this->assertNotEmpty($srcId);
    }

    public function testSetImportSourceFileThrowsForMissingFile(): void
    {
        $importer = $this->makeImporter();
        $this->expectException(ImportSourceNotFoundException::class);
        $importer->setImportSourceFile('/nonexistent/path/to/file.pdf');
    }

    public function testSetImportSourceDataIsIdempotent(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $id1 = $importer->setImportSourceData($data);
        $id2 = $importer->setImportSourceData($data);
        $this->assertSame($id1, $id2);
    }

    public function testSetImportSourceDataAcceptsPasswordOptionForUnencryptedPdf(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data, ['password' => 'secret']);
        $this->assertNotEmpty($srcId);
    }

    public function testSetImportSourceDataThrowsForEncryptedPdf(): void
    {
        $data = $this->encryptedFixtureData();
        $importer = $this->makeImporter();
        $this->expectException(ImportUnsupportedFeatureException::class);
        $this->expectExceptionMessage('encrypted PDF');
        $importer->setImportSourceData($data);
    }

    public function testSetImportSourceDataWithPasswordStillThrowsForEncryptedPdf(): void
    {
        $data = $this->encryptedFixtureData();
        $importer = $this->makeImporter();
        $this->expectException(ImportUnsupportedFeatureException::class);
        $this->expectExceptionMessage('password-based import is not supported');
        $importer->setImportSourceData($data, ['password' => 'secret']);
    }

    public function testGetSourcePageCountReturnsOne(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $this->assertSame(1, $importer->getSourcePageCount($srcId));
    }

    public function testGetSourcePageCountThrowsForUnknownSource(): void
    {
        $importer = $this->makeImporter();
        $this->expectException(ImportSourceNotFoundException::class);
        $importer->getSourcePageCount('invalid-source-id');
    }

    public function testImportPageReturnsPageTemplate(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon);
        $srcId = $importer->setImportSourceData($data);
        $tpl = $importer->importPage($srcId, 1);
        $this->assertInstanceOf(PageTemplate::class, $tpl);
    }

    public function testImportPageRegistersXobject(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon);
        $srcId = $importer->setImportSourceData($data);
        $tpl = $importer->importPage($srcId, 1);
        $this->assertArrayHasKey($tpl->getXobjId(), $xobjects);
    }

    public function testImportPageXobjectHasCorrectObjectNumber(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon);
        $srcId = $importer->setImportSourceData($data);
        $tpl = $importer->importPage($srcId, 1);
        // The xobject's object number must be a positive integer allocated from pon.
        $xobj = $xobjects[$tpl->getXobjId()];
        $this->assertIsArray($xobj);
        $this->assertArrayHasKey('n', $xobj);
        $this->assertGreaterThan(0, $xobj['n']);
    }

    public function testImportPageTemplateHasExpectedDimensions(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon);
        $srcId = $importer->setImportSourceData($data);
        $tpl = $importer->importPage($srcId, 1);
        // fixture mediabox is 612x792; cropbox falls back to mediabox
        $this->assertEqualsWithDelta(612.0, $tpl->getWidth(), 0.01);
        $this->assertEqualsWithDelta(792.0, $tpl->getHeight(), 0.01);
    }

    public function testImportPageCacheReturnsIdenticalTemplate(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon);
        $srcId = $importer->setImportSourceData($data);
        $tpl1 = $importer->importPage($srcId, 1);
        $tpl2 = $importer->importPage($srcId, 1);
        $this->assertSame($tpl1->getXobjId(), $tpl2->getXobjId());
    }

    public function testImportPageThrowsForOutOfRangePage(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $this->expectException(ImportPageOutOfRangeException::class);
        $importer->importPage($srcId, 999);
    }

    public function testImportPageThrowsForUnknownSourceId(): void
    {
        $importer = $this->makeImporter();
        $this->expectException(ImportSourceNotFoundException::class);
        $importer->importPage('unknown-id', 1);
    }

    public function testGetOutImportedObjectsReturnsNonEmptyString(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon);
        $srcId = $importer->setImportSourceData($data);
        $importer->importPage($srcId, 1);
        $out = $importer->getOutImportedObjects();
        $this->assertNotEmpty($out);
        $this->assertStringContainsString(' 0 obj', $out);
        $this->assertStringContainsString('endobj', $out);
    }

    public function testGetOutImportedObjectsClearsQueue(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon);
        $srcId = $importer->setImportSourceData($data);
        $importer->importPage($srcId, 1);
        $importer->getOutImportedObjects();
        $this->assertSame('', $importer->getOutImportedObjects());
    }

    public function testCleanUpReleasesState(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $importer->cleanUp();
        $this->expectException(ImportSourceNotFoundException::class);
        $importer->getSourcePageCount($srcId);
    }

    // -------------------------------------------------------------------------
    // importPages
    // -------------------------------------------------------------------------

    public function testImportPagesWithNullRangeImportsAllPages(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $templates = $importer->importPages($srcId);
        // Fixture has one page.
        $this->assertCount(1, $templates);
        $this->assertInstanceOf(PageTemplate::class, $templates[0]);
    }

    public function testImportPagesWithExplicitRange(): void
    {
        $data = $this->fixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $templates = $importer->importPages($srcId, [1]);
        $this->assertCount(1, $templates);
        $this->assertInstanceOf(PageTemplate::class, $templates[0]);
    }

    public function testImportPagesMatchesImportPageResult(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon);
        $srcId = $importer->setImportSourceData($data);

        $single = $importer->importPage($srcId, 1);
        $batch  = $importer->importPages($srcId, [1]);

        // Same page imported again (cache hit) — must return the exact same template.
        $this->assertSame($single->getXobjId(), $batch[0]->getXobjId());
    }

    public function testImportPagesThrowsForUnknownSource(): void
    {
        $importer = $this->makeImporter();
        $this->expectException(ImportSourceNotFoundException::class);
        $importer->importPages('unknown-id');
    }

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

    public function testRepeatedImportNoCacheUsesSharedObjectMap(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon);
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

    public function testRepeatedImportNoCacheDoesNotDuplicateAuxObjects(): void
    {
        $data = $this->fixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon);
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

    public function testGetSourcePageCountMultipage(): void
    {
        $data = $this->multipageFixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $this->assertSame(2, $importer->getSourcePageCount($srcId));
    }

    public function testImportPagesNullRangeImportsAllMultipagePages(): void
    {
        $data = $this->multipageFixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon);
        $srcId = $importer->setImportSourceData($data);
        $templates = $importer->importPages($srcId);
        $this->assertCount(2, $templates);
        $this->assertInstanceOf(PageTemplate::class, $templates[0]);
        $this->assertInstanceOf(PageTemplate::class, $templates[1]);
    }

    public function testImportPagesMultipagePartialRange(): void
    {
        $data = $this->multipageFixtureData();
        $importer = $this->makeImporter();
        $srcId = $importer->setImportSourceData($data);
        $templates = $importer->importPages($srcId, [2]);
        $this->assertCount(1, $templates);
        $this->assertEqualsWithDelta(612.0, $templates[0]->getWidth(), 0.01);
    }

    public function testImportAllPagesMultipageSharedFontNotDuplicated(): void
    {
        $data = $this->multipageFixtureData();
        $xobjects = [];
        $pon = 0;
        $importer = new Importer($xobjects, $pon);
        $srcId = $importer->setImportSourceData($data);
        $importer->importPages($srcId);
        $out = $importer->getOutImportedObjects();

        // Two pages should produce two Form XObjects.
        $this->assertSame(2, \substr_count($out, '/Type /XObject'));
        // The shared font (5_0) must appear exactly once in the output.
        $this->assertSame(1, \substr_count($out, '/Type /Font'));
    }
}
