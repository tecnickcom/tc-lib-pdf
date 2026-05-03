<?php

/**
 * SourceDocumentTest.php
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
use Com\Tecnick\Pdf\Import\ImportUnsupportedFeatureException;
use Com\Tecnick\Pdf\Import\SourceDocument;
use PHPUnit\Framework\TestCase;

class SourceDocumentTest extends TestCase
{
    private function loadFixture(): string
    {
        $path = __DIR__ . '/../fixtures/simple_import.pdf';
        $data = file_get_contents($path);
        $this->assertNotFalse($data);
        return (string) $data;
    }

    public function testConstructSucceedsWithValidPdf(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        $this->assertNotEmpty($doc->getId());
    }

    public function testIdIsSha256OfData(): void
    {
        $data = $this->loadFixture();
        $doc = new SourceDocument($data);
        $this->assertSame(hash('sha256', $data), $doc->getId());
    }

    public function testGetTrailerContainsRoot(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        $trailer = $doc->getTrailer();
        $this->assertArrayHasKey('root', $trailer);
    }

    public function testGetXrefReturnsNonEmptyArray(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        $xref = $doc->getXref();
        $this->assertNotEmpty($xref);
    }

    public function testGetObjectReturnsDataForKnownRef(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        // Object 1 is /Catalog in the fixture.
        $obj = $doc->getObject('1_0');
        $this->assertNotEmpty($obj);
    }

    public function testGetObjectThrowsForUnknownRef(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        $this->expectException(ImportCorruptedSourceException::class);
        $doc->getObject('999_0');
    }

    public function testFindObjectReturnsNullForUnknownRef(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        $this->assertNull($doc->findObject('999_0'));
    }

    public function testConstructThrowsOnEmptyData(): void
    {
        $this->expectException(ImportCorruptedSourceException::class);
        new SourceDocument('');
    }

    public function testConstructThrowsOnGarbage(): void
    {
        $this->expectException(ImportCorruptedSourceException::class);
        new SourceDocument('this is not a pdf');
    }

    public function testRefToKeyConvertsNormalRef(): void
    {
        $this->assertSame('3_0', SourceDocument::refToKey('3 0 R'));
    }

    public function testRefToKeyPassesThroughKeyForm(): void
    {
        $this->assertSame('3_0', SourceDocument::refToKey('3_0'));
    }

    public function testRefToKeyThrowsOnInvalidRef(): void
    {
        $this->expectException(ImportCorruptedSourceException::class);
        SourceDocument::refToKey('not a ref');
    }

    public function testObjectCountReturnsPositiveInteger(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        $this->assertGreaterThan(0, $doc->objectCount());
    }
}
