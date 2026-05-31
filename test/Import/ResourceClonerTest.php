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
    /** @throws \Throwable */
    private function loadFixture(): SourceDocument
    {
        $path = __DIR__ . '/../fixtures/simple_import.pdf';
        $data = \file_get_contents($path);
        $this->assertNotFalse($data);
        return new SourceDocument($data);
    }

    /**
     * @param array<string, mixed> $objects
     * @throws \Throwable
     */
    private function makeMockSourceDocument(array $objects): SourceDocument
    {
        $src = $this->createStub(SourceDocument::class);

        $src->method('getObject')->willReturnCallback(static fn(string $ref): array => (
            isset($objects[$ref]) && \is_array($objects[$ref]) ? $objects[$ref] : []
        ));

        $src->method('findObject')->willReturnCallback(static fn(string $ref): ?array => isset($objects[$ref])
            && \is_array($objects[$ref])
                ? $objects[$ref]
                : null);

        return $src;
    }

    // -------------------------------------------------------------------------
    // getPon
    // -------------------------------------------------------------------------

    public function testGetPonReturnsInitialValue(): void
    {
        $cloner = new ResourceCloner(10);
        $this->assertSame(10, $cloner->getPon());
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testGetContentStreamEmptyPageReturnsEmptyStream(): void
    {
        $src = $this->loadFixture();
        $cloner = new ResourceCloner(0);

        $result = $cloner->getContentStream([], $src);
        $this->assertSame('', $result['bytes']);
        $this->assertSame('', $result['filter']);
        $this->assertSame(0, $result['length']);
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testCloneResourcesEmptyDictReturnsEmptyString(): void
    {
        $src = $this->loadFixture();
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $this->assertSame('', $cloner->cloneResources([], $src, $map));
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testCloneResourcesSerializesNestedEntriesAndPreservesInlineScalars(): void
    {
        $src = $this->loadFixture();
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $output = $cloner->cloneResources(
            [
                'ColorSpace' => [
                    'CS1' => '/DeviceRGB',
                    'CS2' => ['/DeviceCMYK'],
                ],
            ],
            $src,
            $map,
        );

        $this->assertStringContainsString('/CS1 /DeviceRGB', $output);
        $this->assertStringContainsString('/CS2 [ /DeviceCMYK ]', $output);
    }

    /** @throws \Throwable */
    public function testCloneResourcesSupportsScalarResourceValues(): void
    {
        $src = $this->makeMockSourceDocument([
            '5_0' => [
                ['numeric', 99],
            ],
        ]);
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $indirectOutput = $cloner->cloneResources(['XObject' => '5 0 R'], $src, $map);
        $inlineOutput = $cloner->cloneResources(['ColorSpace' => '/DeviceCMYK'], $src, $map);
        $nullOutput = $cloner->cloneResources(['Pattern' => 123], $src, $map);

        $this->assertMatchesRegularExpression('/\/XObject \d+ 0 R/', $indirectOutput);
        $this->assertStringContainsString('/ColorSpace /DeviceCMYK', $inlineOutput);
        $this->assertStringContainsString('/Pattern null', $nullOutput);
    }

    /** @throws \Throwable */
    public function testCloneResourcesPreservesNumericResourceNames(): void
    {
        $src = $this->makeMockSourceDocument([
            '9_0' => [
                ['numeric', 1],
            ],
        ]);
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $output = $cloner->cloneResources(
            [
                'Font' => [
                    9 => '9_0',
                    'n' => '9_0',
                ],
            ],
            $src,
            $map,
        );

        $this->assertStringContainsString('/Font << /9 ', $output);
        $this->assertStringContainsString('/n ', $output);
    }

    /** @throws \Throwable */
    public function testGetContentStreamRejectsUnexpectedContentsType(): void
    {
        $src = $this->loadFixture();
        $cloner = new ResourceCloner(0);

        $this->expectException(ImportCorruptedSourceException::class);
        $cloner->getContentStream(['Contents' => 42], $src);
    }

    /** @throws \Throwable */
    public function testGetContentStreamMultipleRefsSkipsInvalidEntriesAndReturnsNamedFilter(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                [
                    '<<',
                    [
                        ['/', 'Filter'],
                        ['/', 'FlateDecode'],
                    ],
                ],
                ['stream', 'alpha'],
            ],
            '2_0' => [
                [
                    '<<',
                    [
                        ['/', 'Length'],
                        ['numeric', 4],
                    ],
                ],
                ['stream', 'beta'],
            ],
        ]);
        $cloner = new ResourceCloner(0);

        $single = $cloner->getContentStream(['Contents' => '1_0'], $src);
        $combined = $cloner->getContentStream(['Contents' => ['1_0', 99, '2_0']], $src);

        $this->assertSame('/FlateDecode', $single['filter']);
        $this->assertSame('alpha beta', $combined['bytes']);
        $this->assertSame('', $combined['filter']);
    }

    /** @throws \Throwable */
    public function testGetContentStreamParsesArrayFilterToken(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                [
                    '<<',
                    [
                        ['/', 'Filter'],
                        [
                            '[',
                            [
                                ['/', 'FlateDecode'],
                            ],
                        ],
                    ],
                ],
                ['stream', 'alpha'],
            ],
        ]);
        $cloner = new ResourceCloner(0);

        $single = $cloner->getContentStream(['Contents' => '1_0'], $src);

        $this->assertSame('/FlateDecode', $single['filter']);
    }

    /** @throws \Throwable */
    public function testGetContentStreamParsesArrayFilterTokenWithMultipleNames(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                [
                    '<<',
                    [
                        ['/', 'Filter'],
                        [
                            '[',
                            [
                                ['/', 'ASCII85Decode'],
                                ['/', 'FlateDecode'],
                            ],
                        ],
                    ],
                ],
                ['stream', 'alpha'],
            ],
        ]);
        $cloner = new ResourceCloner(0);

        $single = $cloner->getContentStream(['Contents' => '1_0'], $src);

        $this->assertSame('[ /ASCII85Decode /FlateDecode ]', $single['filter']);
    }

    /** @throws \Throwable */
    public function testGetContentStreamMultipleFlateRefsAreDecodedBeforeConcatenation(): void
    {
        $streamA = \gzcompress('q 1 0 0 1 10 20 cm');
        $streamB = \gzcompress('BT /F1 12 Tf ET');
        $this->assertNotFalse($streamA);
        $this->assertNotFalse($streamB);

        $src = $this->makeMockSourceDocument([
            '1_0' => [
                [
                    '<<',
                    [
                        ['/', 'Filter'],
                        ['/', 'FlateDecode'],
                    ],
                ],
                ['stream', $streamA],
            ],
            '2_0' => [
                [
                    '<<',
                    [
                        ['/', 'Filter'],
                        ['/', 'FlateDecode'],
                    ],
                ],
                ['stream', $streamB],
            ],
        ]);
        $cloner = new ResourceCloner(0);

        $combined = $cloner->getContentStream(['Contents' => ['1_0', '2_0']], $src);

        $this->assertSame('', $combined['filter']);
        $this->assertStringContainsString('q 1 0 0 1 10 20 cm', $combined['bytes']);
        $this->assertStringContainsString('BT /F1 12 Tf ET', $combined['bytes']);
    }

    /** @throws \Throwable */
    public function testCloneResourcesPreservesNestedNumericResourceNames(): void
    {
        $src = $this->makeMockSourceDocument([
            '11_0' => [
                ['numeric', 7],
            ],
        ]);
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $output = $cloner->cloneResources(
            [
                'ExtGState' => [
                    'GS1' => [
                        9 => '11_0',
                    ],
                ],
            ],
            $src,
            $map,
        );

        $this->assertStringContainsString('/ExtGState << /GS1 << /9 ', $output);
    }

    /** @throws \Throwable */
    public function testGetContentStreamReturnsEmptyResultWhenStreamObjectHasNoBytes(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                [
                    '<<',
                    [
                        ['not-a-name', 'Filter'],
                        ['/',          'FlateDecode'],
                    ],
                ],
            ],
        ]);
        $cloner = new ResourceCloner(0);

        $result = $cloner->getContentStream(['Contents' => '1_0'], $src);

        $this->assertSame('', $result['bytes']);
        $this->assertSame('', $result['filter']);
        $this->assertSame(0, $result['length']);
    }

    // -------------------------------------------------------------------------
    // enqueueObject — dedup and cycle safety
    // -------------------------------------------------------------------------

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testEnqueueObjectReturnsAllocatedNumberWhenSourceIsPending(): void
    {
        $src = $this->makeMockSourceDocument([]);
        $map = $this->createStub(ObjectMap::class);
        $cloner = new ResourceCloner(0);

        $map->method('has')->willReturnCallback(static fn(string $srcRef): bool => $srcRef !== '7_0');
        $map->method('isInProgress')->willReturnCallback(static fn(string $srcRef): bool => $srcRef === '7_0');
        $map->method('get')->willReturnCallback(static fn(string $srcRef): int => $srcRef === '7_0' ? 7 : 0);

        $result = $cloner->enqueueObject('7_0', $src, $map);

        $this->assertSame(7, $result);
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testEnqueueObjectSerializesFirstScalarValueWhenNoDictOrStreamExists(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                ['endobj', ''],
                ['numeric', 123],
            ],
        ]);
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $destNum = $cloner->enqueueObject('1_0', $src, $map);
        $flushed = $map->flush();

        $this->assertStringContainsString($destNum . ' 0 obj', $flushed);
        $this->assertStringContainsString("\n123\n", $flushed);
    }

    /** @throws \Throwable */
    public function testEnqueueObjectSerializesFirstArrayValueWhenScalarObjectContainsArrayToken(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                [
                    '[',
                    [
                        ['numeric', 1],
                        ['numeric', 2],
                        ['/', 'Name'],
                    ],
                ],
            ],
        ]);
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $cloner->enqueueObject('1_0', $src, $map);
        $flushed = $map->flush();

        $this->assertStringContainsString('[1 2 /Name]', $flushed);
    }

    /** @throws \Throwable */
    public function testEnqueueObjectScalarObjRefRemapsAndQueuesReferencedObject(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                ['objref', '2 0 R'],
            ],
            '2_0' => [
                ['numeric', 7],
            ],
        ]);
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $cloner->enqueueObject('1_0', $src, $map);
        $flushed = $map->flush();

        $this->assertSame(2, \substr_count($flushed, "endobj\n"));
        $this->assertMatchesRegularExpression('/\d+ 0 R/', $flushed);
        $this->assertStringContainsString("\n7\n", $flushed);
    }

    /** @throws \Throwable */
    public function testEnqueueObjectScalarFallbackReturnsNullWhenNoSerializableValueExists(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                ['endobj', ''],
                'junk-token',
            ],
        ]);
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $cloner->enqueueObject('1_0', $src, $map);
        $flushed = $map->flush();

        $this->assertStringContainsString("\nnull\n", $flushed);
    }

    /** @throws \Throwable */
    public function testEnqueueObjectSerializesDictionaryValuesAcrossTokenTypes(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                [
                    '<<',
                    [
                        ['/', 'Name'],
                        ['string', 'Demo'],
                        ['/', 'Hex'],
                        ['hex', 'CAFE'],
                        ['/', 'Nums'],
                        [
                            '[',
                            [
                                ['numeric', 1],
                                ['numeric', 2],
                            ],
                        ],
                        ['/', 'Ref'],
                        ['objref', '2 0 R'],
                        ['/', 'Kind'],
                        ['/', 'Subtype'],
                    ],
                ],
            ],
            '2_0' => [
                ['numeric', 55],
            ],
        ]);
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $cloner->enqueueObject('1_0', $src, $map);
        $flushed = $map->flush();

        $this->assertStringContainsString('/Name (Demo)', $flushed);
        $this->assertStringContainsString('/Hex <CAFE>', $flushed);
        $this->assertStringContainsString('/Nums [1 2]', $flushed);
        $this->assertStringContainsString('/Kind /Subtype', $flushed);
        $this->assertMatchesRegularExpression('/\/Ref \d+ 0 R/', $flushed);
    }

    /**
     * Regression: tc-lib-pdf-parser tags literal-string dictionary values with
     * the open-paren byte `(` and hex-string values with `<` — NOT the legacy
     * `'string'` / `'hex'` literals that the original `serializeValue()` checked
     * for. Before the fix these values fell through to the bare-scalar path and
     * were emitted without their `( )` / `< >` delimiters, producing malformed
     * PDF dictionaries such as `/FontFamily Futura PT Book` instead of
     * `/FontFamily (Futura PT Book)`. This test mirrors
     * testEnqueueObjectSerializesDictionaryValuesAcrossTokenTypes above but
     * uses the parser's actual token tags.
     *
     * @throws \Throwable
     */
    public function testEnqueueObjectPreservesDelimitersForRealParserTokenTags(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                [
                    '<<',
                    [
                        ['/', 'FontFamily'],
                        ['(', 'Futura PT Book'],
                        ['/', 'CIDSet'],
                        ['<', 'CAFE'],
                    ],
                ],
            ],
        ]);
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $cloner->enqueueObject('1_0', $src, $map);
        $flushed = $map->flush();

        $this->assertStringContainsString('/FontFamily (Futura PT Book)', $flushed);
        $this->assertStringContainsString('/CIDSet <CAFE>', $flushed);
    }

    /** @throws \Throwable */
    public function testEnqueueObjectDoesNotDoubleEscapeParserLiteralStrings(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                [
                    '<<',
                    [
                        ['/', 'Lookup'],
                        ['(', '\\001\\002\\003'],
                    ],
                ],
            ],
        ]);
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $cloner->enqueueObject('1_0', $src, $map);
        $flushed = $map->flush();

        $this->assertStringContainsString('/Lookup (\\001\\002\\003)', $flushed);
        $this->assertStringNotContainsString('/Lookup (\\\\001\\\\002\\\\003)', $flushed);
    }

    /** @throws \Throwable */
    public function testEnqueueObjectSerializesStreamFilterAndSkipsMalformedDictPairs(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                [
                    '<<',
                    [
                        ['/', 'Filter'],
                        ['/', 'ASCIIHexDecode'],
                        ['/', 'Length'],
                        ['numeric', 999],
                        ['not-a-name', 'IgnoreMe'],
                        ['numeric', 77],
                        ['/', []],
                        ['string', 'skip'],
                    ],
                ],
                ['stream', 'ABCD'],
            ],
        ]);
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $cloner->enqueueObject('1_0', $src, $map);
        $flushed = $map->flush();

        $this->assertStringContainsString('/Filter /ASCIIHexDecode', $flushed);
        $this->assertStringContainsString('/Length 4', $flushed);
        $this->assertStringNotContainsString('999', $flushed);
        $this->assertStringNotContainsString('IgnoreMe', $flushed);
        $this->assertStringNotContainsString('skip', $flushed);
    }

    /** @throws \Throwable */
    public function testEnqueueObjectSerializesNestedDictionariesAndFallbackTokens(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                [
                    '<<',
                    [
                        ['/', 'Nested'],
                        [
                            '<<',
                            [
                                ['/', 'Flag'],
                                ['numeric', 1],
                            ],
                        ],
                        ['/', 'Literal'],
                        ['token', ['not-scalar']],
                        ['/', 'Unknown'],
                        [null, ['still-not-scalar']],
                    ],
                ],
            ],
            '2_0' => [
                [
                    '[',
                    [
                        5,
                        ['numeric', 2],
                    ],
                ],
            ],
        ]);
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $cloner->enqueueObject('1_0', $src, $map);
        $cloner->enqueueObject('2_0', $src, $map);
        $flushed = $map->flush();

        $this->assertStringContainsString('/Nested << /Flag 1>>', $flushed);
        $this->assertStringContainsString('/Literal token', $flushed);
        $this->assertStringContainsString('/Unknown null', $flushed);
        $this->assertStringContainsString('[5 2]', $flushed);
    }

    /** @throws \Throwable */
    public function testEnqueueObjectSkipsNonArrayEntriesBeforeReturningNullFallback(): void
    {
        $src = $this->makeMockSourceDocument([
            '1_0' => [
                'junk-token',
                123,
                ['endobj', ''],
                ['<<', null],
            ],
        ]);
        $map = new ObjectMap();
        $cloner = new ResourceCloner(0);

        $cloner->enqueueObject('1_0', $src, $map);
        $flushed = $map->flush();

        $this->assertStringContainsString("\nnull\n", $flushed);
    }

    // -------------------------------------------------------------------------
    // Shared resources across multiple importPage() calls (integration-level)
    // -------------------------------------------------------------------------

    /** @throws \Throwable */
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
