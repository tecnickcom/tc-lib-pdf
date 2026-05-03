<?php

/**
 * ObjectMapTest.php
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

use Com\Tecnick\Pdf\Import\ImportException;
use Com\Tecnick\Pdf\Import\ObjectMap;
use PHPUnit\Framework\TestCase;

class ObjectMapTest extends TestCase
{
    public function testHasReturnsFalseForUnknownRef(): void
    {
        $map = new ObjectMap();
        $this->assertFalse($map->has('1_0'));
    }

    public function testAllocateAssignsIncreasingObjectNumbers(): void
    {
        $map = new ObjectMap();
        $pon = 10;
        $num1 = $map->allocate('1_0', $pon);
        $num2 = $map->allocate('2_0', $pon);
        $this->assertSame(11, $num1);
        $this->assertSame(12, $num2);
        $this->assertSame(12, $pon);
    }

    public function testAllocateIsIdempotentForSameRef(): void
    {
        $map = new ObjectMap();
        $pon = 5;
        $first = $map->allocate('3_0', $pon);
        $second = $map->allocate('3_0', $pon);
        $this->assertSame($first, $second);
    }

    public function testHasReturnsTrueAfterAllocate(): void
    {
        $map = new ObjectMap();
        $pon = 0;
        $map->allocate('5_0', $pon);
        $this->assertTrue($map->has('5_0'));
    }

    public function testIsInProgressTrueBeforeEnqueue(): void
    {
        $map = new ObjectMap();
        $pon = 0;
        $map->allocate('6_0', $pon);
        $this->assertTrue($map->isInProgress('6_0'));
    }

    public function testIsInProgressFalseAfterEnqueue(): void
    {
        $map = new ObjectMap();
        $pon = 0;
        $map->allocate('7_0', $pon);
        $map->enqueue('7_0', '7 0 obj null endobj');
        $this->assertFalse($map->isInProgress('7_0'));
    }

    public function testFlushReturnsQueuedDataAndClears(): void
    {
        $map = new ObjectMap();
        $pon = 0;
        $map->allocate('8_0', $pon);
        $map->enqueue('8_0', '8 0 obj null endobj');
        $out = $map->flush();
        $this->assertStringContainsString('8 0 obj', $out);
        // second flush should be empty
        $this->assertSame('', $map->flush());
    }

    public function testGetThrowsForUnallocatedRef(): void
    {
        $map = new ObjectMap();
        $this->expectException(ImportException::class);
        $map->get('99_0');
    }

    public function testGetMapReturnsFullMap(): void
    {
        $map = new ObjectMap();
        $pon = 0;
        $map->allocate('1_0', $pon);
        $map->allocate('2_0', $pon);
        $full = $map->getMap();
        $this->assertCount(2, $full);
        $this->assertArrayHasKey('1_0', $full);
        $this->assertArrayHasKey('2_0', $full);
    }

    public function testFlushPreservesMapForDedup(): void
    {
        $map = new ObjectMap();
        $pon = 0;
        $map->allocate('10_0', $pon);
        $map->enqueue('10_0', '10 0 obj null endobj');
        $map->flush();

        // After flush the queue is empty but the mapping must still be intact.
        $this->assertTrue($map->has('10_0'));
        $this->assertSame(1, $map->get('10_0'));
        // A second flush of the now-empty queue should produce an empty string.
        $this->assertSame('', $map->flush());
    }

    public function testDedupAcrossMultipleAllocations(): void
    {
        $map = new ObjectMap();
        $pon = 0;
        $num1 = $map->allocate('20_0', $pon);
        $map->enqueue('20_0', '1 0 obj null endobj');
        $map->flush();

        // Re-allocating the same ref after flush must return the original number.
        $num2 = $map->allocate('20_0', $pon);
        $this->assertSame($num1, $num2);
        // pon must not have been incremented again.
        $this->assertSame(1, $pon);
    }
}
