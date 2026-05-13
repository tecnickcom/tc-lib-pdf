<?php

/**
 * ResourceClonerTest.php
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

use Com\Tecnick\Pdf\Import\ImportCorruptedSourceException;
use Com\Tecnick\Pdf\Import\ObjectMap;
use Com\Tecnick\Pdf\Import\ResourceCloner;
use Com\Tecnick\Pdf\Import\SourceDocument;
use PHPUnit\Framework\TestCase;

class ResourceClonerTest extends TestCase
{
    private function loadFixture(): SourceDocument
    {
        $path = __DIR__ . '/../fixtures/simple_import.pdf';
        $data = \file_get_contents($path);
        $this->assertNotFalse($data);
        return new SourceDocument($data);
    }

    // -------------------------------------------------------------------------
    // getPon
    // -------------------------------------------------------------------------

    public function testGetPonReturnsInitialValue(): void
    {
        $cloner = new ResourceCloner(10);
        $this->assertSame(10, $cloner->getPon());
    }

    public function testGetPonUpdatesAfterEnqueue(): void
    {
        $src = $this->loadFixture();
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        // Enqueuing font object 5_0 must allocate a new destination number.
        $destNum = $cloner->enqueueObject('5_0', $src, $map);
        $this->assertGreaterThan(0, $destNum);
        $this->assertSame($destNum, $cloner->getPon());
    }

    // -------------------------------------------------------------------------
    // getContentStream
    // -------------------------------------------------------------------------

    public function testGetContentStreamEmptyPageReturnsEmptyStream(): void
    {
        $src = $this->loadFixture();
        $cloner = new ResourceCloner(0);

        $result = $cloner->getContentStream([], $src);
        $this->assertSame('', $result['bytes']);
        $this->assertSame('', $result['filter']);
        $this->assertSame(0, $result['length']);
    }

    public function testGetContentStreamSingleRef(): void
    {
        $src = $this->loadFixture();
        $cloner = new ResourceCloner(0);

        // Object 4_0 is the content stream in the fixture.
        $pageDict = ['Contents' => '4_0'];
        $result = $cloner->getContentStream($pageDict, $src);

        $this->assertNotEmpty($result['bytes']);
        $this->assertStringContainsString('BT', $result['bytes']);
        $this->assertSame(\strlen($result['bytes']), $result['length']);
    }

    public function testGetContentStreamArrayWithSingleRef(): void
    {
        $src = $this->loadFixture();
        $cloner = new ResourceCloner(0);

        // Array of one ref — should behave identically to single-ref case.
        $pageDict = ['Contents' => ['4_0']];
        $result = $cloner->getContentStream($pageDict, $src);

        $this->assertNotEmpty($result['bytes']);
        $this->assertStringContainsString('BT', $result['bytes']);
    }

    public function testGetContentStreamMultipleRefsAreConcatenated(): void
    {
        $src = $this->loadFixture();
        $cloner = new ResourceCloner(0);

        // Use the same stream twice to test the concatenation path.
        $pageDict = ['Contents' => ['4_0', '4_0']];
        $result = $cloner->getContentStream($pageDict, $src);

        // Bytes must appear twice in the concatenated output.
        $singleStream = $cloner->getContentStream(['Contents' => '4_0'], $src);
        $this->assertStringContainsString($singleStream['bytes'], $result['bytes']);
        $this->assertGreaterThan($singleStream['length'], $result['length']);
        // Multi-stream concatenation always returns empty filter.
        $this->assertSame('', $result['filter']);
    }

    public function testGetContentStreamThrowsForInvalidArrayEntry(): void
    {
        $src = $this->loadFixture();
        $cloner = new ResourceCloner(0);

        // A non-string element inside the /Contents array is invalid.
        $pageDict = ['Contents' => [42]];
        $this->expectException(ImportCorruptedSourceException::class);
        $cloner->getContentStream($pageDict, $src);
    }

    // -------------------------------------------------------------------------
    // cloneResources
    // -------------------------------------------------------------------------

    public function testCloneResourcesEmptyDictReturnsEmptyString(): void
    {
        $src = $this->loadFixture();
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $this->assertSame('', $cloner->cloneResources([], $src, $map));
    }

    public function testCloneResourcesFontRefsAreRemapped(): void
    {
        $src = $this->loadFixture();
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        // Minimal resource dict: Font -> F1 -> indirect ref to object 5_0.
        $resources = ['Font' => ['F1' => '5_0']];
        $output = $cloner->cloneResources($resources, $src, $map);

        // Output must start with << and contain a /Font entry.
        $this->assertStringStartsWith('<<', $output);
        $this->assertStringContainsString('/Font', $output);
        // The ref to 5_0 must be remapped to a new object number in "N 0 R" format.
        $this->assertMatchesRegularExpression('/\d+ 0 R/', $output);
    }

    public function testCloneResourcesProcSetSkipped(): void
    {
        $src = $this->loadFixture();
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $resources = ['ProcSet' => ['/PDF', '/Text']];
        $output = $cloner->cloneResources($resources, $src, $map);

        // ProcSet is re-emitted as a standard fixed list, so source array is ignored.
        $this->assertStringContainsString('/ProcSet', $output);
    }

    // -------------------------------------------------------------------------
    // enqueueObject — dedup and cycle safety
    // -------------------------------------------------------------------------

    public function testEnqueueObjectDedupReturnsSameDestNumber(): void
    {
        $src = $this->loadFixture();
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $num1 = $cloner->enqueueObject('5_0', $src, $map);
        $num2 = $cloner->enqueueObject('5_0', $src, $map);

        // Same source ref must always map to the same destination number.
        $this->assertSame($num1, $num2);
        // pon must be incremented only once.
        $this->assertSame(1, $cloner->getPon());
    }

    public function testEnqueueObjectDedupAfterFlush(): void
    {
        $src = $this->loadFixture();
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $num1 = $cloner->enqueueObject('5_0', $src, $map);
        $map->flush();

        // After flushing the queue, the map must still hold the allocation.
        $num2 = $cloner->enqueueObject('5_0', $src, $map);
        $this->assertSame($num1, $num2);
        // No new pon increment.
        $this->assertSame(1, $cloner->getPon());
    }

    public function testEnqueueObjectForUndefinedRefEmitsNullObject(): void
    {
        $src = $this->loadFixture();
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        // 99_0 does not exist in the fixture — must get a null placeholder.
        $destNum = $cloner->enqueueObject('99_0', $src, $map);
        $this->assertGreaterThan(0, $destNum);

        $flushed = $map->flush();
        $this->assertStringContainsString($destNum . ' 0 obj', $flushed);
        $this->assertStringContainsString('null', $flushed);
        $this->assertStringContainsString('endobj', $flushed);
    }

    public function testEnqueueObjectSerializesStreamObject(): void
    {
        $src = $this->loadFixture();
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        // Object 4_0 is a stream object in the fixture.
        $destNum = $cloner->enqueueObject('4_0', $src, $map);
        $flushed = $map->flush();

        $this->assertStringContainsString($destNum . ' 0 obj', $flushed);
        $this->assertStringContainsString('stream', $flushed);
        $this->assertStringContainsString('endstream', $flushed);
        $this->assertStringContainsString('endobj', $flushed);
    }

    public function testEnqueueObjectMultipleDistinctRefsIncreasePon(): void
    {
        $src = $this->loadFixture();
        $map = new ObjectMap();
        $cloner = new ResourceCloner(5);

        $cloner->enqueueObject('4_0', $src, $map);
        $cloner->enqueueObject('5_0', $src, $map);

        // Each unique ref increments pon once.
        $this->assertSame(7, $cloner->getPon());
    }

    // -------------------------------------------------------------------------
    // Shared resources across multiple importPage() calls (integration-level)
    // -------------------------------------------------------------------------

    public function testSharedObjectNotDuplicatedInFlushedOutput(): void
    {
        $src = $this->loadFixture();
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        // Simulate two pages sharing font object 5_0.
        $resources = ['Font' => ['F1' => '5_0']];

        // First "page" import: clone resources and flush.
        $cloner->cloneResources($resources, $src, $map);
        $firstFlush = $map->flush();

        // Second "page" import: same shared resource — nothing new should be queued.
        $cloner->cloneResources($resources, $src, $map);
        $secondFlush = $map->flush();

        // First flush must contain 5_0's serialized data.
        $this->assertNotEmpty($firstFlush);
        // Second flush must be empty because 5_0 was already allocated and not re-queued.
        $this->assertSame('', $secondFlush);
    }
}
