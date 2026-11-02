<?php

/**
 * HTMLImageSourceTest.php
 *
 * @since     2026-11-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * Data URI and SVG image sources of HTML images and list markers.
 *
 * @since     2026-11-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class HTMLImageSourceTest extends TestUtil
{
    /**
     * A 10x10 red square.
     */
    private const SVG =
        '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10">'
            . '<rect width="10" height="10" fill="red"/></svg>';

    /**
     * Base64 of a 13x13 red PNG.
     */
    private const PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAA0AAAANCAIAAAD9iXMrAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAFElEQVQoz2P8z0AUYGIYVTec1QEAIYcBGb+3yrEAAAAASUVORK5CYII=';

    /**
     * @throws \Throwable
     */
    private function getObject(): TestableHTML
    {
        self::setUpFontsPath();
        $obj = new TestableHTML();
        $this->initFontAndPage($obj);

        return $obj;
    }

    /**
     * @return array<string, array{0: string, 1: ?array{mime: string, data: string}}>
     */
    public static function dataUriProvider(): array
    {
        return [
            'base64' => ['data:image/png;base64,QUJD', ['mime' => 'image/png', 'data' => 'ABC']],
            'base64 with whitespace' => ["data:image/png;base64,QU\n JD", ['mime' => 'image/png', 'data' => 'ABC']],
            'base64 percent-encoded' => ['data:;base64,Pz8%2B', ['mime' => 'text/plain', 'data' => '??>']],
            'parameters' => [
                'data:Image/SVG+XML;charset=utf-8;base64,QUJD',
                ['mime' => 'image/svg+xml', 'data' => 'ABC'],
            ],
            'percent-encoded' => ['data:image/svg+xml,%3Csvg%3E', ['mime' => 'image/svg+xml', 'data' => '<svg>']],
            'plain' => [
                'data:image/svg+xml;utf8,<svg fill="%23f00">',
                [
                    'mime' => 'image/svg+xml',
                    'data' => '<svg fill="#f00">',
                ],
            ],
            'surrounding whitespace' => [' data:,A ', ['mime' => 'text/plain', 'data' => 'A']],
            'no comma' => ['data:image/png;base64', null],
            'no data' => ['data:image/png;base64,', null],
            'bad base64' => ['data:image/png;base64,QU*D', null],
            'path' => ['images/data.png', null],
        ];
    }

    /**
     * @param ?array{mime: string, data: string} $expected
     *
     * @throws \Throwable
     */
    #[DataProvider('dataUriProvider')]
    public function testDecodeHTMLDataUri(string $src, ?array $expected): void
    {
        $this->assertSame($expected, (new TestableHTML())->exposeDecodeHTMLDataUri($src));
    }

    /**
     * @throws \Throwable
     */
    public function testSvgDataIsRecognizedByItsRootElement(): void
    {
        $obj = new TestableHTML();

        $this->assertTrue($obj->exposeIsHTMLSvgImageData(self::SVG));
        $this->assertTrue($obj->exposeIsHTMLSvgImageData(
            "\xEF\xBB\xBF<?xml version=\"1.0\"?>\n<!-- c -->\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\""
            . " \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n<svg/>",
        ));
        $this->assertTrue($obj->exposeIsHTMLSvgImageData('<svg:svg xmlns:svg="http://www.w3.org/2000/svg"/>'));
        $this->assertFalse($obj->exposeIsHTMLSvgImageData('<svgx/>'));
        $this->assertFalse($obj->exposeIsHTMLSvgImageData('<html><svg/></html>'));
        // raster bytes with an SVG text chunk
        $this->assertFalse($obj->exposeIsHTMLSvgImageData("\x89PNG\r\n\x1A\n<svg>"));

        $this->assertTrue($obj->exposeIsHTMLSvgImageSource('@' . self::SVG));
        $this->assertTrue($obj->exposeIsHTMLSvgImageSource('https://x.test/a.SVG?v=1#top'));
        $this->assertFalse($obj->exposeIsHTMLSvgImageSource('/tmp/a.svg.png'));
        $this->assertFalse($obj->exposeIsHTMLSvgImageSource("@\x89PNG<svg>"));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function svgSourceProvider(): array
    {
        return [
            'base64' => ['data:image/svg+xml;base64,' . \base64_encode(self::SVG)],
            'plain' => ['data:image/svg+xml;utf8,' . \htmlspecialchars(self::SVG)],
            'percent-encoded' => ['data:image/svg+xml,' . \rawurlencode(self::SVG)],
        ];
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('svgSourceProvider')]
    public function testSvgDataUriImageIsDrawn(string $src): void
    {
        $out = $this->getObject()->getHTMLCell(
            '<img src="' . $src . '" alt="MISSING" width="20" height="20">',
            10,
            10,
            100,
            0,
        );

        $this->assertStringNotContainsString('MISSING', $out);
        $this->assertMatchesRegularExpression('/ re\b/', $out);
    }

    /**
     * @throws \Throwable
     */
    public function testSvgDataUriImageIsDrawnInRightToLeftText(): void
    {
        $out = $this->getObject()->getHTMLCell(
            '<p dir="rtl">abc <img src="data:image/svg+xml;base64,'
            . \base64_encode(self::SVG)
            . '" alt="MISSING" width="20" height="20"> def</p>',
            10,
            10,
            100,
            0,
        );

        $this->assertStringNotContainsString('MISSING', $out);
        $this->assertMatchesRegularExpression('/ re\b/', $out);
    }

    /**
     * @throws \Throwable
     */
    #[RequiresPhpExtension('gd')]
    public function testRasterDataUriImageIsDrawn(): void
    {
        $out = $this->getObject()->getHTMLCell(
            '<img src="data:image/png;base64,' . self::PNG_BASE64 . '" alt="MISSING" width="20">',
            10,
            10,
            100,
            0,
        );

        $this->assertStringNotContainsString('MISSING', $out);
        $this->assertMatchesRegularExpression('/\/IMG(?:plain|mask)?\d+ Do Q/', $out);
    }

    /**
     * @throws \Throwable
     */
    public function testListStyleImageDataUriKinds(): void
    {
        $obj = $this->getObject();

        $marker = $obj->exposeGetHTMLListImageMarkerType('url("data:image/svg+xml,' . \rawurlencode(self::SVG) . '")');
        $this->assertStringStartsWith('img|svg|', $marker);
        $this->assertStringEndsWith('|@' . self::SVG, $marker);

        // the content decides: an SVG document declared with another type is still SVG
        $marker = $obj->exposeGetHTMLListImageMarkerType(
            'url(data:image/png;base64,' . \base64_encode(self::SVG) . ')',
        );
        $this->assertStringStartsWith('img|svg|', $marker);

        $marker = $obj->exposeGetHTMLListImageMarkerType('url(data:image/gif;base64,R0lGODlh)');
        $this->assertStringStartsWith('img|gif|', $marker);

        $this->assertSame('', $obj->exposeGetHTMLListImageMarkerType('url(data:image/png;base64,QU*D)'));
    }

    /**
     * @throws \Throwable
     */
    public function testListStyleImagePercentEncodedSvgIsDrawn(): void
    {
        $out = $this->getObject()->getHTMLCell(
            '<ul style="list-style-image:url(\'data:image/svg+xml,'
            . \rawurlencode(self::SVG)
            . '\')"><li>Item</li></ul>',
            10,
            10,
            100,
            0,
        );

        $this->assertMatchesRegularExpression('/ re\b/', $out);
    }
}
