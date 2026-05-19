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

use Com\Tecnick\Pdf\Import\ImportCorruptedSourceException;
use Com\Tecnick\Pdf\Import\ImportPageOutOfRangeException;
use Com\Tecnick\Pdf\Import\PageResolver;
use Com\Tecnick\Pdf\Import\SourceDocument;
use PHPUnit\Framework\TestCase;

class PageResolverTest extends TestCase
{
    /** @throws \Throwable */
    private function loadDoc(): SourceDocument
    {
        $path = __DIR__ . '/../fixtures/simple_import.pdf';
        $data = file_get_contents($path);
        $this->assertNotFalse($data);
        return new SourceDocument($data);
    }

    /**
     * @param array<int, mixed> $pairs
     * @return array<int, mixed>
     */
    private function dictObject(array $pairs): array
    {
        return [['<<', $pairs]];
    }

    /**
     * @param array<string, array<int, mixed>> $objects
     * @throws \Throwable
     */
    private function mockDoc(array $objects): SourceDocument
    {
        $doc = $this->createStub(SourceDocument::class);
        $doc->method('getTrailer')->willReturn(['root' => '1 0 R']);
        $doc->method('getObject')->willReturnCallback(static function (string $ref) use ($objects): array {
            $object = $objects[$ref] ?? null;
            if ($object === null) {
                throw new ImportCorruptedSourceException('Missing object in test map: ' . $ref);
            }

            return $object;
        });
        $doc->method('findObject')->willReturnCallback(static fn(string $ref): ?array => $objects[$ref] ?? null);

        return $doc;
    }

    /** @throws \Throwable */
    public function testResolvePage1ReturnsExpectedMediaBox(): void
    {
        $resolver = new PageResolver();
        $resolved = $resolver->resolve($this->loadDoc(), 1);
        $this->assertArrayHasKey('mediaBox', $resolved);
        $this->assertCount(4, $resolved['mediaBox']);
        $this->assertEqualsWithDelta(612.0, $resolved['mediaBox'][2], 0.001);
        $this->assertEqualsWithDelta(792.0, $resolved['mediaBox'][3], 0.001);
    }

    /** @throws \Throwable */
    public function testResolvePage1HasResources(): void
    {
        $resolver = new PageResolver();
        $resolved = $resolver->resolve($this->loadDoc(), 1);
        $this->assertArrayHasKey('resources', $resolved);
    }

    /** @throws \Throwable */
    public function testResolvePage1RotateIsZero(): void
    {
        $resolver = new PageResolver();
        $resolved = $resolver->resolve($this->loadDoc(), 1);
        $this->assertSame(0, $resolved['rotate']);
    }

    /** @throws \Throwable */
    public function testResolveThrowsForPageZero(): void
    {
        $resolver = new PageResolver();
        $this->expectException(ImportPageOutOfRangeException::class);
        $resolver->resolve($this->loadDoc(), 0);
    }

    /** @throws \Throwable */
    public function testResolveThrowsForPageOutOfRange(): void
    {
        $resolver = new PageResolver();
        $this->expectException(ImportPageOutOfRangeException::class);
        $resolver->resolve($this->loadDoc(), 999);
    }

    /** @throws \Throwable */
    public function testResolveParsesInheritedRotateAndIndirectResources(): void
    {
        $resolver = new PageResolver();
        $doc = $this->mockDoc([
            '1_0' => $this->dictObject([
                ['/', 'Pages'],
                ['objref', '2 0 R'],
            ]),
            '2_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Pages'],
                ['/', 'Kids'],
                [
                    '[',
                    [
                        ['objref', '3 0 R'],
                    ],
                ],
                ['/', 'MediaBox'],
                [
                    '[',
                    [
                        ['numeric', 0],
                        ['numeric', 0],
                        ['numeric', 612],
                        ['numeric', 792],
                    ],
                ],
                ['/', 'Rotate'],
                ['numeric', '180'],
                ['/', 'Resources'],
                ['objref', '4 0 R'],
            ]),
            '3_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Page'],
            ]),
            '4_0' => $this->dictObject([
                ['/', 'Font'],
                [
                    '<<',
                    [
                        ['/', 'F1'],
                        ['objref', '5 0 R'],
                    ],
                ],
            ]),
            '5_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Font'],
            ]),
        ]);

        $resolved = $resolver->resolve($doc, 1);

        $this->assertSame(180, $resolved['rotate']);
        $this->assertArrayHasKey('Font', $resolved['resources']);
        if (!isset($resolved['resources']['Font']) || !\is_array($resolved['resources']['Font'])) {
            $this->fail('Expected Font resources array.');
        }

        $this->assertArrayHasKey('F1', $resolved['resources']['Font']);
    }

    /** @throws \Throwable */
    public function testResolveThrowsForPagesNodeWithoutKidsArray(): void
    {
        $resolver = new PageResolver();
        $doc = $this->mockDoc([
            '1_0' => $this->dictObject([
                ['/', 'Pages'],
                ['objref', '2 0 R'],
            ]),
            '2_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Pages'],
            ]),
        ]);

        $this->expectException(ImportCorruptedSourceException::class);
        $this->expectExceptionMessage('/Kids');
        $resolver->resolve($doc, 1);
    }

    /** @throws \Throwable */
    public function testResolveThrowsForUnexpectedPageTreeNodeType(): void
    {
        $resolver = new PageResolver();
        $doc = $this->mockDoc([
            '1_0' => $this->dictObject([
                ['/', 'Pages'],
                ['objref', '2 0 R'],
            ]),
            '2_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Catalog'],
                ['/', 'Kids'],
                [
                    '[',
                    [
                        ['objref', '3 0 R'],
                    ],
                ],
            ]),
            '3_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Page'],
            ]),
        ]);

        $this->expectException(ImportCorruptedSourceException::class);
        $this->expectExceptionMessage('Unexpected page tree node type');
        $resolver->resolve($doc, 1);
    }
}
