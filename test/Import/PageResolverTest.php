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
    private function invokeResolverMethod(PageResolver $resolver, string $method, mixed ...$args): mixed
    {
        $ref = new \ReflectionClass($resolver);
        return $ref->getMethod($method)->invokeArgs($resolver, $args);
    }

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
        $doc->method('getObject')->willReturnCallback(static fn(string $ref): array => $objects[$ref] ?? []);
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
    public function testResolveThrowsWhenRootPagesEntryIsMissing(): void
    {
        $resolver = new PageResolver();
        $doc = $this->mockDoc([
            '1_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Catalog'],
            ]),
        ]);

        $this->expectException(ImportCorruptedSourceException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('missing /Pages entry', '/') . '/');
        $resolver->resolve($doc, 1);
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
    public function testResolveSkipsInvalidKidsAndUsesInlineResourcesAndBoxFallbacks(): void
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
                        ['numeric', 7],
                        ['objref', '3 0 R'],
                    ],
                ],
                ['/', 'MediaBox'],
                [
                    '[',
                    [
                        ['numeric', 0],
                        ['numeric', 0],
                        ['numeric', 400],
                        ['numeric', 600],
                    ],
                ],
            ]),
            '3_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Page'],
                ['/', 'CropBox'],
                ['string', 'invalid'],
                ['/', 'Rotate'],
                ['numeric', 90],
                ['/', 'Resources'],
                [
                    '<<',
                    [
                        ['/', 'ProcSet'],
                        [
                            '[',
                            [
                                ['/', '/PDF'],
                                ['/', '/Text'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $resolved = $resolver->resolve($doc, 1);

        $this->assertSame(90, $resolved['rotate']);
        $this->assertSame([0.0, 0.0, 400.0, 600.0], $resolved['mediaBox']);
        $this->assertSame($resolved['mediaBox'], $resolved['cropBox']);
        $this->assertSame($resolved['cropBox'], $resolved['bleedBox']);
        $this->assertSame($resolved['cropBox'], $resolved['trimBox']);
        $this->assertSame($resolved['cropBox'], $resolved['artBox']);
        $this->assertSame(['/PDF', '/Text'], $resolved['resources']['ProcSet'] ?? null);
    }

    /** @throws \Throwable */
    public function testResolveThrowsWhenResolvedPageIsMissingMediaBox(): void
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
            ]),
            '3_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Page'],
            ]),
        ]);

        $this->expectException(ImportCorruptedSourceException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('missing /MediaBox', '/') . '/');
        $resolver->resolve($doc, 1);
    }

    /** @throws \Throwable */
    public function testResolveAcceptsIndirectMediaBoxReference(): void
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
            ]),
            '3_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Page'],
                ['/', 'MediaBox'],
                ['objref', '8 0 R'],
            ]),
            '8_0' => [
                [
                    '[',
                    [
                        ['numeric', 0],
                        ['numeric', 0],
                        ['numeric', 612],
                        ['numeric', 792],
                    ],
                ],
            ],
        ]);

        $resolved = $resolver->resolve($doc, 1);

        $this->assertSame([0.0, 0.0, 612.0, 792.0], $resolved['mediaBox']);
        $this->assertSame($resolved['mediaBox'], $resolved['cropBox']);
    }

    /** @throws \Throwable */
    public function testResolveThrowsWhenIndirectMediaBoxReferenceIsMissing(): void
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
            ]),
            '3_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Page'],
                ['/', 'MediaBox'],
                ['objref', '999 0 R'],
            ]),
        ]);

        $this->expectException(ImportCorruptedSourceException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('missing /MediaBox', '/') . '/');
        $resolver->resolve($doc, 1);
    }

    /** @throws \Throwable */
    public function testResolveThrowsWhenIndirectMediaBoxObjectIsMalformed(): void
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
            ]),
            '3_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Page'],
                ['/', 'MediaBox'],
                ['objref', '8 0 R'],
            ]),
            '8_0' => [
                ['keyword', 'invalid-box-token'],
            ],
        ]);

        $this->expectException(ImportCorruptedSourceException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('missing /MediaBox', '/') . '/');
        $resolver->resolve($doc, 1);
    }

    /** @throws \Throwable */
    public function testResolveMergesParentAndChildResourceDictionaries(): void
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
                        ['numeric', 200],
                        ['numeric', 300],
                    ],
                ],
                ['/', 'Resources'],
                [
                    '<<',
                    [
                        ['/', 'Font'],
                        [
                            '<<',
                            [
                                ['/', 'F1'],
                                ['objref', '10 0 R'],
                            ],
                        ],
                        ['/', 'XObject'],
                        [
                            '<<',
                            [
                                ['/', 'Im1'],
                                ['objref', '11 0 R'],
                            ],
                        ],
                    ],
                ],
            ]),
            '3_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Page'],
                ['/', 'Resources'],
                [
                    '<<',
                    [
                        ['/', 'Font'],
                        [
                            '<<',
                            [
                                ['/', 'F2'],
                                ['objref', '12 0 R'],
                            ],
                        ],
                    ],
                ],
            ]),
            '10_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Font'],
            ]),
            '11_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'XObject'],
            ]),
            '12_0' => $this->dictObject([
                ['/', 'Type'],
                ['/', 'Font'],
            ]),
        ]);

        $resolved = $resolver->resolve($doc, 1);

        $resources = $resolved['resources'];
        $this->assertIsArray($resources);

        if (!isset($resources['Font']) || !\is_array($resources['Font'])) {
            $this->fail('Expected merged Font dictionary.');
        }

        if (!isset($resources['XObject']) || !\is_array($resources['XObject'])) {
            $this->fail('Expected inherited XObject dictionary.');
        }

        $this->assertArrayHasKey('F1', $resources['Font']);
        $this->assertArrayHasKey('F2', $resources['Font']);
        $this->assertArrayHasKey('Im1', $resources['XObject']);
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
        $this->expectExceptionMessageMatches('/' . preg_quote('/Kids', '/') . '/');
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
        $this->expectExceptionMessageMatches('/' . preg_quote('Unexpected page tree node type', '/') . '/');
        $resolver->resolve($doc, 1);
    }

    public function testObjectToDictThrowsWhenDictionaryElementIsMissing(): void
    {
        $resolver = new PageResolver();

        $this->expectException(ImportCorruptedSourceException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('Expected dictionary object', '/') . '/');
        $this->invokeResolverMethod($resolver, 'objectToDict', ['not-an-array-element']);
    }

    public function testParseDictArraySkipsMalformedKeysAndParsesFallbackValueTypes(): void
    {
        $resolver = new PageResolver();

        /** @var array<string, mixed> $parsed */
        $parsed = $this->invokeResolverMethod($resolver, 'parseDictArray', [
            ['numeric', 1],
            ['string', 'ignored-non-name-key'],
            ['/', 123],
            ['string', 'ignored-non-string-name'],
            ['/', '/Literal'],
            'plain-text',
            ['/', '/Ref'],
            ['objref'],
            ['/', '/Unknown'],
            ['keyword', 'fallback-value'],
            ['/', '/Empty'],
            [],
        ]);

        $this->assertSame(
            [
                'Literal' => 'plain-text',
                'Ref' => '',
                'Unknown' => 'fallback-value',
                'Empty' => null,
            ],
            \array_intersect_key($parsed, [
                'Literal' => true,
                'Ref' => true,
                'Unknown' => true,
                'Empty' => true,
            ]),
        );
        $this->assertArrayNotHasKey('123', $parsed);
    }
}
