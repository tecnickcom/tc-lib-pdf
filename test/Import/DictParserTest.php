<?php

/**
 * DictParserTest.php
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

use Com\Tecnick\Pdf\Import\DictParser;
use Com\Tecnick\Pdf\Import\ImportCorruptedSourceException;
use Com\Tecnick\Pdf\Import\SourceDocument;
use PHPUnit\Framework\TestCase;

class DictParserTest extends TestCase
{
    /** @throws \Throwable */
    private function loadDoc(): SourceDocument
    {
        $path = __DIR__ . '/../fixtures/simple_import.pdf';
        $data = file_get_contents($path);
        $this->assertNotFalse($data);
        return new SourceDocument($data);
    }

    /** @throws \Throwable */
    public function testObjectToDictThrowsWhenDictionaryElementIsMissing(): void
    {
        $parser = new DictParser();

        $this->expectException(ImportCorruptedSourceException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('Expected dictionary object', '/') . '/');
        $parser->objectToDict(['not-an-array-element']);
    }

    public function testParseDictArraySkipsMalformedKeysAndParsesFallbackValueTypes(): void
    {
        $parser = new DictParser();

        $parsed = $parser->parseDictArray([
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

    /** @throws \Throwable */
    public function testResolveDictReturnsAnEmptyArrayForAnUnresolvableValue(): void
    {
        $parser = new DictParser();
        $doc = $this->loadDoc();

        $this->assertSame([], $parser->resolveDict(null, $doc));
        $this->assertSame([], $parser->resolveDict('not a reference', $doc));
        $this->assertSame([], $parser->resolveDict('9999 0 R', $doc));
        $this->assertSame(['Type' => '/Page'], $parser->resolveDict(['Type' => '/Page'], $doc));
    }

    /** @throws \Throwable */
    public function testResolveArrayReturnsAnEmptyArrayForAnUnresolvableValue(): void
    {
        $parser = new DictParser();
        $doc = $this->loadDoc();

        $this->assertSame([], $parser->resolveArray(null, $doc));
        $this->assertSame([], $parser->resolveArray('not a reference', $doc));
        $this->assertSame([], $parser->resolveArray('9999 0 R', $doc));
        $this->assertSame(['a', 'b'], $parser->resolveArray(['a', 'b'], $doc));
    }
}
