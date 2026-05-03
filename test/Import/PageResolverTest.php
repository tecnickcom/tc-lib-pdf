<?php

/**
 * PageResolverTest.php
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
use Com\Tecnick\Pdf\Import\PageResolver;
use Com\Tecnick\Pdf\Import\SourceDocument;
use PHPUnit\Framework\TestCase;

class PageResolverTest extends TestCase
{
    private function loadDoc(): SourceDocument
    {
        $path = __DIR__ . '/../fixtures/simple_import.pdf';
        $data = file_get_contents($path);
        $this->assertNotFalse($data);
        return new SourceDocument((string) $data);
    }

    public function testResolvePage1ReturnsExpectedMediaBox(): void
    {
        $resolver = new PageResolver();
        $resolved = $resolver->resolve($this->loadDoc(), 1);
        $this->assertArrayHasKey('mediaBox', $resolved);
        $this->assertCount(4, $resolved['mediaBox']);
        $this->assertEqualsWithDelta(612.0, $resolved['mediaBox'][2], 0.001);
        $this->assertEqualsWithDelta(792.0, $resolved['mediaBox'][3], 0.001);
    }

    public function testResolvePage1HasResources(): void
    {
        $resolver = new PageResolver();
        $resolved = $resolver->resolve($this->loadDoc(), 1);
        $this->assertArrayHasKey('resources', $resolved);
    }

    public function testResolvePage1RotateIsZero(): void
    {
        $resolver = new PageResolver();
        $resolved = $resolver->resolve($this->loadDoc(), 1);
        $this->assertSame(0, $resolved['rotate']);
    }

    public function testResolveThrowsForPageZero(): void
    {
        $resolver = new PageResolver();
        $this->expectException(ImportPageOutOfRangeException::class);
        $resolver->resolve($this->loadDoc(), 0);
    }

    public function testResolveThrowsForPageOutOfRange(): void
    {
        $resolver = new PageResolver();
        $this->expectException(ImportPageOutOfRangeException::class);
        $resolver->resolve($this->loadDoc(), 999);
    }
}
