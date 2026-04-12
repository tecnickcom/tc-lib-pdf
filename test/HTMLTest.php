<?php

/**
 * HTMLTest.php
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

namespace Test;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-import-type THTMLAttrib from \Com\Tecnick\Pdf\HTML
 */
class TestableHTML extends \Com\Tecnick\Pdf\Tcpdf
{
    public function exposeSanitizeHTML(string $html): string
    {
        return $this->sanitizeHTML($html);
    }

    /** @return array<int, string> */
    public function exposeParseHTMLTagMethods(): array
    {
        $ref = new \ReflectionClass(\Com\Tecnick\Pdf\HTML::class);
        $names = [];
        foreach ($ref->getMethods(\ReflectionMethod::IS_PROTECTED) as $method) {
            $name = $method->getName();
            if (
                \str_starts_with($name, 'parseHTMLTagOPEN')
                || \str_starts_with($name, 'parseHTMLTagCLOSE')
            ) {
                $names[] = $name;
            }
        }
        \sort($names);

        return $names;
    }

    /** @phpstan-param THTMLAttrib $elm */
    public function exposeInvokeParseHTMLTagMethod(
        string $method,
        array $elm,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $out = $this->{$method}($elm, $tpx, $tpy, $tpw, $tph);
        if (!\is_string($out)) {
            return '';
        }

        return $out;
    }

    /** @phpstan-return THTMLAttrib */
    public function exposeGetHTMLRootProperties(): array
    {
        return $this->getHTMLRootProperties();
    }

    /** @phpstan-return array<int, THTMLAttrib> */
    public function exposeGetHTMLDOM(string $html): array
    {
        return $this->getHTMLDOM($html);
    }

    public function exposeGetHTMLliBullet(
        int $depth,
        int $count,
        float $posx = 0,
        float $posy = 0,
        string $type = '',
    ): string {
        return $this->getHTMLliBullet($depth, $count, $posx, $posy, $type);
    }

    public function exposePageBreak(): int
    {
        return $this->pageBreak();
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeProcessHTMLDOMText(array &$dom, string $element, int $key, int $parent): void
    {
        $this->processHTMLDOMText($dom, $element, $key, $parent);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeInheritHTMLProperties(array &$dom, int $key, int $parent): void
    {
        $this->inheritHTMLProperties($dom, $key, $parent);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @phpstan-param array<int, string> $elm
     */
    public function exposeProcessHTMLDOMClosingTag(
        array &$dom,
        array $elm,
        int $key,
        int $parent,
        string $cssarray,
    ): void {
        $this->processHTMLDOMClosingTag($dom, $elm, $key, $parent, $cssarray);
    }

    /**
    * @phpstan-param array<int, THTMLAttrib> $dom
     * @phpstan-param array<string, string> $css
     * @phpstan-param array<int> $level
     */
    public function exposeProcessHTMLDOMOpeningTag(
        array &$dom,
        array $css,
        array $level,
        string $element,
        int $key,
        bool $thead,
    ): void {
        $this->processHTMLDOMOpeningTag($dom, $css, $level, $element, $key, $thead);
    }

    /** @phpstan-param THTMLAttrib $elm */
    public function exposeParseHTMLText(
        array $elm,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLText($elm, $tpx, $tpy, $tpw, $tph);
    }

    public function exposeInitHTMLCellContext(
        float $originx,
        float $originy,
        float $maxwidth,
        float $maxheight,
    ): void {
        $this->initHTMLCellContext($originx, $originy, $maxwidth, $maxheight);
    }

    /** @phpstan-param THTMLAttrib $elm */
    public function exposeCloseHTMLBlock(array $elm, float &$tpx, float &$tpy, float &$tpw): string
    {
        return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }
}

class TestableHTMLNobrProbe extends TestableHTML
{
    /** @var array<int, string> */
    private array $nobrOpenStates = [];

    /** @return array<int, string> */
    public function exposeNobrOpenStates(): array
    {
        return $this->nobrOpenStates;
    }

    protected function parseHTMLTagOPENdiv(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        $state = '';
        if (!empty($elm['attribute']['nobr']) && \is_string($elm['attribute']['nobr'])) {
            $state = $elm['attribute']['nobr'];
        }
        $this->nobrOpenStates[] = $state;

        return parent::parseHTMLTagOPENdiv($elm, $tpx, $tpy, $tpw, $tph);
    }
}

/**
 * @phpstan-import-type THTMLAttrib from \Com\Tecnick\Pdf\HTML
 */
class HTMLTest extends TestUtil
{
    public static function setUpBeforeClass(): void
    {
        self::setUpFontsPath();
    }

    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    protected function getInternalTestObject(): TestableHTML
    {
        return new TestableHTML();
    }

    protected function getNobrProbeTestObject(): TestableHTMLNobrProbe
    {
        return new TestableHTMLNobrProbe();
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @phpstan-return THTMLAttrib
     */
    private function makeHtmlNode(array $overrides = []): array
    {
        $node = [
            'align' => '',
            'attribute' => [],
            'bgcolor' => '',
            'block' => false,
            'border' => [],
            'clip' => false,
            'cols' => 0,
            'content' => '',
            'cssdata' => [],
            'csssel' => [],
            'dir' => 'ltr',
            'elkey' => 0,
            'fgcolor' => 'black',
            'fill' => false,
            'font-stretch' => 100.0,
            'fontname' => 'helvetica',
            'fontsize' => 10.0,
            'fontstyle' => '',
            'height' => 0.0,
            'hide' => false,
            'letter-spacing' => 0.0,
            'line-height' => 1.0,
            'listtype' => '',
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'opening' => false,
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'parent' => 0,
            'rows' => 0,
            'self' => false,
            'stroke' => 0.0,
            'strokecolor' => '',
            'style' => [],
            'tag' => true,
            'text-indent' => 0.0,
            'text-transform' => '',
            'thead' => '',
            'trids' => [],
            'value' => '',
            'width' => 0.0,
            'x' => 0.0,
            'y' => 0.0,
        ];
        /** @var THTMLAttrib $typed */
        $typed = \array_replace($node, $overrides);

        return $typed;
    }

    public function testStrTrimHelpers(): void
    {
        $obj = $this->getTestObject();

        $this->assertSame('abc  ', $obj->strTrimLeft('   abc  '));
        $this->assertSame('   abc', $obj->strTrimRight('   abc   '));
        $this->assertSame('abc', $obj->strTrim('   abc   '));
        $this->assertSame('-abc-', $obj->strTrim('   abc   ', '-'));
    }

    public function testSetULLIDotUsesDefaultsAndCustomImageValue(): void
    {
        $obj = $this->getTestObject();

        $obj->setULLIDot('disc');
        $this->assertSame('disc', $this->getObjectProperty($obj, 'ullidot'));

        $obj->setULLIDot('invalid-bullet');
        $this->assertSame('!', $this->getObjectProperty($obj, 'ullidot'));

        $obj->setULLIDot('img|png|4|4|bullet.png');
        $this->assertSame('img|png|4|4|bullet.png', $this->getObjectProperty($obj, 'ullidot'));
    }

    public function testTidyHTMLReturnsStyledXhtml(): void
    {
        if (!\function_exists('tidy_parse_string')) {
            $this->markTestSkipped('Tidy extension is not available.');
        }

        $obj = $this->getTestObject();
        $html = '<html><head><style>p { COLOR: RED; }</style></head><body><p>Hello</p><br></body></html>';
        $out = $obj->tidyHTML($html, 'body{font-size:10pt;}');

        $this->assertStringStartsWith('<style>', $out);
        $this->assertStringContainsString('body{font-size:10pt;}', $out);
        $this->assertStringContainsString('p { color: red; }', \strtolower($out));
        $this->assertStringContainsString('<br />', $out);
    }

    public function testIsValidCSSSelectorForTagMatchesClassAndId(): void
    {
        $obj = $this->getTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode(['value' => 'div', 'attribute' => ['id' => 'main', 'class' => 'hero card']]),
        ];

        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' div.hero'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' div#main'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' span.hero'));
    }

    public function testGetHTMLDOMCSSDataCollectsApplicableStyles(): void
    {
        $obj = $this->getTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode(['value' => 'p', 'attribute' => ['class' => 'x', 'style' => 'font-weight:bold;']]),
        ];
        $css = [
            '0010 p.x' => 'color:red;',
            '0001 div' => 'color:blue;',
        ];

        $obj->getHTMLDOMCSSData($dom, $css, 1);

        $this->assertNotEmpty($dom[1]['cssdata']);
        $combined = '';
        foreach ($dom[1]['cssdata'] as $row) {
            $combined .= $row['c'];
        }
        $this->assertStringContainsString('color:red', $combined);
        $this->assertStringContainsString('font-weight:bold', $combined);
    }

    public function testParseHTMLStyleAttributesParsesBasicInlineStyles(): void
    {
        $obj = $this->getTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode([
                'value' => 'div',
                'attribute' => ['style' => 'direction:rtl;display:none;text-transform:uppercase;text-align:center;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame('rtl', $dom[1]['dir']);
        $this->assertTrue($dom[1]['hide']);
        $this->assertSame('uppercase', $dom[1]['text-transform']);
        $this->assertSame('C', $dom[1]['align']);
    }

    public function testParseHTMLAttributesSetsTagSpecificDefaults(): void
    {
        $obj = $this->getTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontstyle' => '']),
            1 => $this->makeHtmlNode([
                'value' => 'a',
                'attribute' => [],
                'style' => [],
                'fontstyle' => '',
                'fontsize' => 10.0,
                'parent' => 0,
                'align' => '',
                'hide' => false,
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);
        $this->assertStringContainsString('U', $dom[1]['fontstyle']);
    }

    public function testParseHTMLAttributesCoversFontTableAndHeadingBranches(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontstyle' => '']),
            1 => $this->makeHtmlNode([
                'value' => 'font',
                'parent' => 0,
                'attribute' => ['face' => 'helvetica', 'size' => '+2'],
                'style' => [],
                'fontstyle' => '',
                'fontsize' => 10.0,
            ]),
            2 => $this->makeHtmlNode([
                'value' => 'table',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
            ]),
            3 => $this->makeHtmlNode([
                'value' => 'tr',
                'parent' => 2,
                'attribute' => [],
                'style' => [],
            ]),
            4 => $this->makeHtmlNode([
                'value' => 'td',
                'parent' => 3,
                'attribute' => ['colspan' => '2'],
                'style' => [],
            ]),
            5 => $this->makeHtmlNode([
                'value' => 'h2',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
                'fontstyle' => '',
                'fontsize' => 10.0,
            ]),
            6 => $this->makeHtmlNode([
                'value' => 'ul',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
                'align' => '',
            ]),
            7 => $this->makeHtmlNode([
                'value' => 'small',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
                'fontsize' => 10.0,
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);
        $obj->parseHTMLAttributes($dom, 2, false);
        $obj->parseHTMLAttributes($dom, 3, false);
        $obj->parseHTMLAttributes($dom, 4, false);
        $obj->parseHTMLAttributes($dom, 5, false);
        $obj->parseHTMLAttributes($dom, 6, false);
        $obj->parseHTMLAttributes($dom, 7, false);

        $this->assertSame(12.0, $dom[1]['fontsize']);
        $this->assertSame(1, $dom[2]['rows']);
        $this->assertSame([3], $dom[2]['trids']);
        $this->assertSame(2, $dom[3]['cols']);
        $this->assertSame('2', $dom[4]['attribute']['colspan']);
        $this->assertSame(14.0, $dom[5]['fontsize']);
        $this->assertStringContainsString('B', $dom[5]['fontstyle']);
        $this->assertSame('L', $dom[6]['align']);
        $this->assertGreaterThan(0.0, $dom[7]['fontsize']);
        $this->assertLessThan(10.0, $dom[7]['fontsize']);
    }

    public function testParseHTMLStyleAttributesCoversPageBreakAndInheritanceModes(): void
    {
        $obj = $this->getTestObject();
        $dom = [
            0 => $this->makeHtmlNode([
                'line-height' => 1.25,
                'listtype' => 'disc',
                'text-indent' => 2.0,
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => [
                    'style' => 'line-height:normal;page-break-before:always;page-break-after:right;list-style-type:inherit;text-indent:inherit;',
                ],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => [
                    'style' => 'line-height:normal;page-break-before:left;page-break-after:always;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertSame(1.25, $dom[1]['line-height']);
        $this->assertSame('right', $dom[1]['attribute']['pagebreakafter']);
        $this->assertSame('disc', $dom[1]['listtype']);
        $this->assertSame(2.0, $dom[1]['text-indent']);

        $this->assertSame(1.25, $dom[2]['line-height']);
        $this->assertArrayHasKey('pagebreakafter', $dom[2]['attribute']);
        $this->assertSame('true', $dom[2]['attribute']['pagebreakafter']);
    }

    public function testParseHTMLAttributesCoversDisplayColorAndGeometryAttributes(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontstyle' => '']),
            1 => $this->makeHtmlNode([
                'value' => 'span',
                'parent' => 0,
                'fontsize' => 10.0,
                'attribute' => [
                    'display' => 'none',
                    'dir' => 'rtl',
                    'color' => 'red',
                    'bgcolor' => '#00ff00',
                    'strokecolor' => 'blue',
                    'width' => '20',
                    'height' => '10',
                    'align' => 'center',
                    'stroke' => '0.2',
                    'fill' => 'true',
                    'clip' => 'true',
                    'border' => '1',
                ],
                'style' => [],
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);

        $this->assertTrue($dom[1]['hide']);
        $this->assertSame('rtl', $dom[1]['dir']);
        $this->assertStringContainsString('rgba(', $dom[1]['fgcolor']);
        $this->assertNotSame('', $dom[1]['bgcolor']);
        $this->assertStringContainsString('rgba(', $dom[1]['strokecolor']);
        $this->assertGreaterThan(0.0, $dom[1]['width']);
        $this->assertGreaterThan(0.0, $dom[1]['height']);
        $this->assertSame('C', $dom[1]['align']);
        $this->assertGreaterThan(0.0, $dom[1]['stroke']);
        $this->assertTrue($dom[1]['fill']);
        $this->assertTrue($dom[1]['clip']);
        $this->assertArrayHasKey('LTRB', $dom[1]['border']);
    }

    public function testGetHTMLCellRendersParagraphText(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<p>Hello</p>', 0, 0, 20, 6);
        $this->assertNotSame('', $out);
        $this->assertStringContainsString('BT', $out);
        $this->assertStringContainsString('Hello', $out);
    }

    public function testGetHTMLCellCreatesNamedDestinationFromIdAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell('<div id="sec-1">Hello</div>', 10, 12, 40, 20);

        /** @var array<string, array<string, int|float>> $dests */
        $dests = $this->getObjectProperty($obj, 'dests');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $name = $encrypt->encodeNameObject('sec-1');

        $this->assertArrayHasKey($name, $dests);
        $this->assertSame($page->getPageID(), $dests[$name]['p']);
    }

    public function testGetHTMLCellUsesStylesToDrawOuterCell(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $cell = [
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'borderpos' => \Com\Tecnick\Pdf\Base::BORDERPOS_DEFAULT,
        ];
        $styles = [
            'all' => [
                'lineWidth' => 0.2,
                'lineCap' => 'butt',
                'lineJoin' => 'miter',
                'miterLimit' => 10,
                'dashArray' => [],
                'dashPhase' => 0,
                'lineColor' => 'black',
                'fillColor' => '#eeeeee',
            ],
        ];

        $out = $obj->getHTMLCell('<p>A</p>', 0, 0, 20, 8, $cell, $styles);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString(' re', $out);
    }

    public function testAddHTMLCellAppendsContentToCurrentPage(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $before = $page->getPage();
        $beforeCount = \count($before['content']);

        $obj->addHTMLCell('<p>AddedByMethod</p>', 0, 0, 30, 10);

        $after = $page->getPage();
        $afterCount = \count($after['content']);

        $this->assertGreaterThan($beforeCount, $afterCount);
        $this->assertStringContainsString('AddedByMethod', \implode("\n", $after['content']));
    }

    public function testAddHTMLCellDrawsStyledOuterCell(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');

        $cell = [
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'borderpos' => \Com\Tecnick\Pdf\Base::BORDERPOS_DEFAULT,
        ];
        $styles = [
            'all' => [
                'lineWidth' => 0.2,
                'lineCap' => 'butt',
                'lineJoin' => 'miter',
                'miterLimit' => 10,
                'dashArray' => [],
                'dashPhase' => 0,
                'lineColor' => 'black',
                'fillColor' => '#eeeeee',
            ],
        ];

        $obj->addHTMLCell('<p>StyledAdd</p>', 0, 0, 0, 0, $cell, $styles);

        $after = $page->getPage();
        $content = \implode("\n", $after['content']);

        $this->assertStringContainsString('StyledAdd', $content);
        $this->assertStringContainsString(' re', $content);
    }

    public function testGetHTMLCellUsesCellPaddingForContentPosition(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $nopad = [
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'borderpos' => \Com\Tecnick\Pdf\Base::BORDERPOS_DEFAULT,
        ];
        $pad = [
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 12.0],
            'borderpos' => \Com\Tecnick\Pdf\Base::BORDERPOS_DEFAULT,
        ];

        $plainOut = $obj->getHTMLCell('<p>A</p>', 0, 0, 20, 8, $nopad, []);
        $paddedOut = $obj->getHTMLCell('<p>A</p>', 0, 0, 20, 8, $pad, []);

        $this->assertNotSame('', $plainOut);
        $this->assertNotSame('', $paddedOut);
        $this->assertNotSame($plainOut, $paddedOut);
    }

    public function testGetHTMLCellWidthZeroUsesAvailableRegionWidthForStyledCell(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $cell = [
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'borderpos' => \Com\Tecnick\Pdf\Base::BORDERPOS_DEFAULT,
        ];
        $styles = [
            'all' => [
                'lineWidth' => 0.2,
                'lineCap' => 'butt',
                'lineJoin' => 'miter',
                'miterLimit' => 10,
                'dashArray' => [],
                'dashPhase' => 0,
                'lineColor' => 'black',
                'fillColor' => '#eeeeee',
            ],
        ];

        $out = $obj->getHTMLCell('<p>A</p>', 0, 0, 0, 8, $cell, $styles);

        $this->assertNotSame('', $out);
        $matches = [];
        \preg_match('/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re/s', $out, $matches);
        $this->assertNotEmpty($matches);
        $this->assertGreaterThan(0.0, \abs((float) $matches[3]));
    }

    public function testGetHTMLCellCoversAllSupportedTagsWithoutErrors(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $html = '<body>'
            . '<a href="https://example.com">'
            . '<b>B</b><em>E</em><font>F</font><i>I</i><label>L</label><marker>M</marker><s>S</s><small>sm</small><span>sp</span><strike>st</strike><strong>sg</strong><tt>tt</tt><u>u</u><del>d</del><form>frm</form>'
            . '</a>'
            . '<blockquote>q</blockquote>'
            . '<div>dv</div>'
            . '<dl><dt>dt</dt><dd>dd</dd></dl>'
            . '<h1>1</h1><h2>2</h2><h3>3</h3><h4>4</h4><h5>5</h5><h6>6</h6>'
            . '<hr></hr><br></br>'
            . '<img alt="img"></img>'
            . '<input value="inp"></input>'
            . '<ol><li>o1</li></ol>'
            . '<ul><li>u1</li></ul>'
            . '<select value="v2"><option value="v1">A</option><option value="v2" selected>B</option></select>'
            . '<output value="out"></output>'
            . '<p>p<sub>sub</sub><sup>sup</sup></p>'
            . '<pre>pre</pre>'
            . '<table><thead><tr><th>H</th></tr></thead><tr><td>T</td></tr></table>'
            . '<tablehead><tr><td>TH</td></tr></tablehead>'
            . '<tcpdf method="noop"></tcpdf>'
            . '<textarea value="txt"></textarea>'
            . '</body>';

        $out = $obj->getHTMLCell($html, 0, 0, 80, 60);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('BT', $out);
    }

    public function testAllParseHTMLTagMethodsCanBeInvoked(): void
    {
        $probe = $this->getInternalTestObject();
        $methods = $probe->exposeParseHTMLTagMethods();
        $this->assertNotSame([], $methods);
        $this->assertCount(100, $methods);

        foreach ($methods as $method) {
            $obj = $this->getInternalTestObject();
            $this->initFontAndPage($obj);

            $elm = $obj->exposeGetHTMLRootProperties();
            $tag = \preg_replace('/^parseHTMLTag(?:OPEN|CLOSE)/', '', $method) ?? '';
            $elm['value'] = \strtolower($tag);
            $elm['attribute'] = [];

            if ($method === 'parseHTMLTagOPENa') {
                $elm['attribute'] = ['href' => 'https://example.com'];
            }
            if ($method === 'parseHTMLTagOPENimg') {
                $elm['attribute'] = ['alt' => 'img'];
            }
            if ($method === 'parseHTMLTagOPENinput') {
                $elm['attribute'] = ['value' => 'v'];
            }
            if ($method === 'parseHTMLTagOPENoption') {
                $elm['attribute'] = ['value' => 'v'];
            }
            if ($method === 'parseHTMLTagOPENoutput') {
                $elm['attribute'] = ['value' => 'o'];
            }
            if ($method === 'parseHTMLTagOPENselect') {
                $elm['attribute'] = ['opt' => 'v#!TaB!#Label#!NwL!#', 'value' => 'v'];
            }
            if ($method === 'parseHTMLTagOPENtextarea') {
                $elm['attribute'] = ['value' => 'txt'];
            }
            if ($method === 'parseHTMLTagOPENtcpdf') {
                $elm['attribute'] = ['method' => 'noop'];
            }

            $tpx = 0.0;
            $tpy = 0.0;
            $tpw = 40.0;
            $tph = 20.0;

            $obj->exposeInvokeParseHTMLTagMethod($method, $elm, $tpx, $tpy, $tpw, $tph);
        }
    }

    public function testParseHTMLTagTheadOpenCloseManageTableStack(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $elm = $obj->exposeGetHTMLRootProperties();
        $elm['value'] = 'thead';
        $elm['cols'] = 2;

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 40.0;
        $tph = 20.0;

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENthead', $elm, $tpx, $tpy, $tpw, $tph);

        $stack = $this->getObjectProperty($obj, 'htmltablestack');
        $this->assertIsArray($stack);
        $this->assertCount(1, $stack);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEthead', $elm, $tpx, $tpy, $tpw, $tph);

        $stack = $this->getObjectProperty($obj, 'htmltablestack');
        $this->assertIsArray($stack);
        $this->assertCount(0, $stack);
    }

    public function testGetHTMLCellCreatesLinkAnnotationForAnchorText(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<a href="https://example.com">Click</a>', 0, 0, 30, 10);

        $this->assertNotSame('', $out);
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotSame([], $annotation);

        $haslink = false;
        foreach ($annotation as $annot) {
            if (!\is_array($annot)) {
                continue;
            }

            $txt = $annot['txt'] ?? '';
            $opt = $annot['opt'] ?? [];
            if (!\is_array($opt)) {
                continue;
            }

            if (($txt === 'https://example.com') && (($opt['subtype'] ?? '') === 'Link')) {
                $haslink = true;
                break;
            }
        }

        $this->assertTrue($haslink);
    }

    public function testGetHTMLCellAnchorDoesNotLeakToFollowingText(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<a href="https://example.com">A</a>B', 0, 0, 30, 10);

        $this->assertNotSame('', $out);
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(1, $annotation);
    }

    public function testGetHTMLCellAnchorSurvivesTextareaCloseTag(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<a href="https://example.com">A<textarea value=""></textarea>B</a>', 0, 0, 30, 10);

        $this->assertNotSame('', $out);
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(2, $annotation);
    }

    public function testGetHTMLCellAnchorSurvivesSelectCloseTag(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<a href="https://example.com">A<select></select>B</a>', 0, 0, 30, 10);

        $this->assertNotSame('', $out);
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(2, $annotation);
    }

    public function testGetHTMLCellAnchorSurvivesDelTag(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<a href="https://example.com"><del>A</del>B</a>', 0, 0, 30, 10);

        $this->assertNotSame('', $out);
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(2, $annotation);
    }

    public function testGetHTMLCellRendersOrderedListMarkers(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<ol><li>One</li><li>Two</li></ol>', 0, 0, 30, 12);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('(1.)', $out);
        $this->assertStringContainsString('(2.)', $out);
        $this->assertStringContainsString('One', $out);
        $this->assertStringContainsString('Two', $out);
    }

    public function testGetHTMLCellRendersUnorderedListMarkers(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<ul><li>First</li><li>Second</li></ul>', 0, 0, 30, 12);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('First', $out);
        $this->assertStringContainsString('Second', $out);
        $this->assertStringContainsString('BT', $out);
    }

    public function testGetHTMLCellRendersBasicTableCells(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table><tr><th>H1</th><th>H2</th></tr><tr><td>A</td><td>B</td></tr></table>',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('H1', $out);
        $this->assertStringContainsString('H2', $out);
        $this->assertStringContainsString('(A)', $out);
        $this->assertStringContainsString('(B)', $out);
    }

    public function testGetHTMLCellRendersTableWithColspan(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table><tr><td colspan="2">Top</td></tr><tr><td>Left</td><td>Right</td></tr></table>',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('(Top)', $out);
        $this->assertStringContainsString('(Left)', $out);
        $this->assertStringContainsString('(Right)', $out);
    }

    public function testGetHTMLCellSuppressesNestedNobrAttribute(): void
    {
        $obj = $this->getNobrProbeTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell('<div nobr="true"><div nobr="true">A</div>B</div>', 0, 0, 40, 20);
        $states = $obj->exposeNobrOpenStates();

        $this->assertCount(2, $states);
        $this->assertSame('true', $states[0]);
        $this->assertSame('', $states[1]);
    }

    public function testGetHTMLCellBreaksBeforeNobrBlockOnOverflow(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $before = $page->getPageId();
        $region = $page->getRegion();
        $starty = \max(0.0, ((float) $region['RH']) - 5.0);

        $out = $obj->getHTMLCell(
            '<div nobr="true"><p>A</p><p>B</p></div>',
            0,
            $starty,
            30,
            0,
        );

        $after = $page->getPageId();

        $this->assertNotSame('', $out);
        $this->assertGreaterThan($before, $after);
        $this->assertStringContainsString('(A)', $out);
        $this->assertStringContainsString('(B)', $out);
    }

    public function testGetHTMLCellRendersInputAndTextareaValues(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<input type="text" value="field" /><textarea>notes</textarea><input type="hidden" value="secret" />',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('field', $out);
        $this->assertStringContainsString('notes', $out);
        $this->assertStringNotContainsString('secret', $out);
    }

    public function testGetHTMLCellRendersCheckboxAndPasswordInputs(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<input type="checkbox" checked="checked" />'
            . '<input type="radio" />'
            . '<input type="password" value="abc" />',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('[x]', $out);
        $this->assertStringContainsString('[ ]', $out);
        $this->assertStringContainsString('***', $out);
        $this->assertStringNotContainsString('abc', $out);
    }

    public function testGetHTMLCellRendersMultilineTextareaValue(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<textarea>line1' . "\n" . ' line2</textarea>', 0, 0, 40, 20);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('line1', $out);
        $this->assertStringContainsString(' line2', $out);
    }

    public function testGetHTMLCellRendersSelectFirstOptionLabel(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<select><option value="v1">Alpha</option><option value="v2">Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Alpha', $out);
    }

    public function testGetHTMLCellRendersSelectedOptionLabelByValue(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<select value="v2"><option value="v1">Alpha</option><option value="v2">Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Beta', $out);
    }

    public function testGetHTMLCellRendersSelectedOptionLabelBySingleQuotedValue(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            "<select value=\"v2\"><option value='v1'>Alpha</option><option value='v2'>Beta</option></select>",
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Beta', $out);
    }

    public function testGetHTMLCellRendersSelectedOptionLabelByUnquotedValue(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<select value="v2"><option value=v1>Alpha</option><option value=v2>Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Beta', $out);
    }

    public function testGetHTMLCellRendersMultipleSelectedOptionLabelsByValue(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<select value="v2,v1"><option value="v1">Alpha</option><option value="v2">Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Beta, Alpha', $out);
    }

    public function testGetHTMLCellRendersSelectedOptionLabelBySelectedAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<select><option value="v1">Alpha</option><option value="v2" selected="selected">Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Beta', $out);
    }

    public function testGetHTMLCellRendersMultipleSelectedOptionLabelsBySelectedAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<select><option selected="selected">Alpha</option><option selected="selected">Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Alpha, Beta', $out);
    }

    public function testGetHTMLCellRendersSelectedOptionLabelWithBooleanSelectedAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<select><option>Alpha</option><option selected>Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Beta', $out);
    }

    public function testGetHTMLCellRendersSelectedOptionLabelWithSelectedTrueAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<select><option>Alpha</option><option selected="true">Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Beta', $out);
    }

    public function testGetHTMLCellRendersSelectedOptionLabelWithSingleQuotedSelectedAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            "<select><option>Alpha</option><option selected='selected'>Beta</option></select>",
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Beta', $out);
    }

    public function testGetHTMLCellRendersSelectedOptionLabelWithUppercaseSelectedAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<select><option>Alpha</option><option SELECTED>Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Beta', $out);
    }

    public function testGetHTMLCellRendersImgFallbackWhenImageCannotLoad(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<img src="/tmp/__tc_lib_pdf_missing_image__.png" />', 0, 0, 20, 10);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('[img]', $out);
    }

    public function testGetHTMLCellImgWithoutSrcUsesAltFallbackText(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<img alt="x" />', 0, 0, 20, 10);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Tj', $out);
        $this->assertStringContainsString('x', $out);
    }

    public function testGetHTMLCellImgInvalidSrcUsesAltFallbackText(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<img src="/tmp/__tc_lib_pdf_missing_image__.png" alt="fallback-alt" />', 0, 0, 20, 10);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Tj', $out);
        $this->assertStringContainsString('fallback-alt', $out);
    }

    public function testGetHTMLCellDrawsTableCellBorderWhenSpecified(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<table><tr><td style="border:1px solid black">A</td></tr></table>', 0, 0, 30, 12);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString(' re', $out);
    }

    public function testGetHTMLCellDrawsUniformBorderHeightAcrossRow(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table><tr><td style="border:1px solid black">A</td><td style="border:1px solid black">This is a longer wrapped cell value</td></tr></table>',
            0,
            0,
            25,
            30,
        );

        $this->assertNotSame('', $out);
        $matches = [];
        \preg_match_all('/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\\s+s/', $out, $matches, PREG_SET_ORDER);
        $this->assertGreaterThanOrEqual(2, \count($matches));
        $this->assertEqualsWithDelta(
            \abs((float) $matches[0][4]),
            \abs((float) $matches[1][4]),
            0.0001,
        );
    }

    public function testGetHTMLCellRendersTableWithRowspanContent(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table>'
            . '<tr><td rowspan="2">A</td><td>Top</td></tr>'
            . '<tr><td>Bottom</td></tr>'
            . '</table>',
            0,
            0,
            30,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('(A)', $out);
        $this->assertStringContainsString('(Top)', $out);
        $this->assertStringContainsString('(Bottom)', $out);
    }

    public function testGetHTMLCellDrawsRowspanBorderAcrossTwoRows(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table>'
            . '<tr><td rowspan="2" style="border:1px solid black">A</td><td style="border:1px solid black">Top</td></tr>'
            . '<tr><td style="border:1px solid black">Bottom</td></tr>'
            . '</table>',
            0,
            0,
            30,
            20,
        );

        $this->assertNotSame('', $out);
        $matches = [];
        \preg_match_all('/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\\s+s/', $out, $matches, PREG_SET_ORDER);
        $this->assertGreaterThanOrEqual(3, \count($matches));

        $heights = \array_map(
            static fn(array $match): float => \abs((float) $match[4]),
            $matches,
        );
        \rsort($heights);

        $this->assertGreaterThan($heights[1], $heights[0]);
        $this->assertEqualsWithDelta($heights[0], $heights[1] + $heights[2], 0.0001);
    }

    public function testGetHTMLCellDrawsTableCellBackgroundFillWhenSpecified(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<table><tr><td style="background-color:#eeeeee">A</td></tr></table>', 0, 0, 30, 12);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString(' re', $out);
        $this->assertStringContainsString("f\n", $out);
    }

    public function testGetHTMLCellRendersTableHeadAndBodyRows(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table><thead><tr><th>H</th></tr></thead><tr><td>T</td></tr></table>',
            0,
            0,
            30,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('H', $out);
        $this->assertStringContainsString('T', $out);
    }

    public function testGetHTMLCellReplaysTableHeadOnExplicitBodyRowPageBreak(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table><thead><tr><th>H</th></tr></thead>'
            . '<tr style="page-break-before:always"><td>T</td></tr></table>',
            0,
            0,
            30,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertGreaterThanOrEqual(2, \substr_count($out, '(H)'));
        $this->assertStringContainsString('(T)', $out);
    }

    public function testGetHTMLCellReplaysTableHeadOnAutomaticRowOverflow(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $region = $page->getRegion();
        $starty = \max(0.0, ((float) $region['RH']) - 10.0);

        $out = $obj->getHTMLCell(
            '<table><thead><tr><th>H</th></tr></thead>'
            . '<tr><td>Tall</td></tr>'
            . '<tr><td>Next</td></tr></table>',
            0,
            $starty,
            30,
            0,
        );

        $this->assertNotSame('', $out);
        $this->assertGreaterThanOrEqual(2, \substr_count($out, '(H)'));
        $this->assertStringContainsString('(Tall)', $out);
        $this->assertStringContainsString('(Next)', $out);
    }

    public function testGetHTMLCellTreatsFormAsBlockContainer(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<form>A</form>B', 0, 0, 30, 20);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('A', $out);
        $this->assertStringContainsString('B', $out);

        $this->assertMatchesRegularExpression('/\(A\) Tj.*\(B\) Tj/s', $out);
    }

    public function testCloseHTMLBlockAdvancesWhenInlineContentWasRendered(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $elm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 16.0,
            'line-height' => 1.0,
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);

        $obj->exposeInitHTMLCellContext(20.0, 120.0, 150.0, 0.0);

        $tpx = 48.0;
        $tpy = 120.0;
        $tpw = 122.0;

        $obj->exposeCloseHTMLBlock($elm, $tpx, $tpy, $tpw);

        $this->assertSame(20.0, $tpx);
        $this->assertSame(150.0, $tpw);
        $this->assertGreaterThan(120.0, $tpy);
    }

    public function testCloseHTMLBlockDoesNotAddExtraLineWhenAlreadyAtLineStart(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $elm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 16.0,
            'line-height' => 1.0,
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);

        $obj->exposeInitHTMLCellContext(20.0, 120.0, 150.0, 0.0);

        $tpx = 20.0;
        $tpy = 140.0;
        $tpw = 150.0;

        $obj->exposeCloseHTMLBlock($elm, $tpx, $tpy, $tpw);

        $this->assertSame(20.0, $tpx);
        $this->assertSame(150.0, $tpw);
        $this->assertSame(140.0, $tpy);
    }

    public function testSanitizeHTMLRemovesHeadAndStyleBlocks(): void
    {
        $obj = $this->getInternalTestObject();
        $html = '<head><style>p{color:red;}</style></head><p>A</p><script>x</script>';

        $out = $obj->exposeSanitizeHTML($html);

        $this->assertStringNotContainsString('<head', $out);
        $this->assertStringNotContainsString('<style', $out);
        $this->assertStringContainsString('<p>', $out);
    }

    public function testSanitizeHTMLNormalizesSelectTextareaAndImgBlocks(): void
    {
        $obj = $this->getInternalTestObject();
        $html = '<select><option value="v1">Alpha</option><option>Beta</option></select>'
            . '<textarea>x"y' . "\n" . 'z</textarea><img src="a.png"> tail';

        $out = $obj->exposeSanitizeHTML($html);

        $this->assertStringContainsString('<select opt="v1#!TaB!#Alpha#!NwL!#Beta" />', $out);
        $this->assertStringContainsString('<textarea value="x\'\'y', $out);
        $this->assertStringContainsString('z" />', $out);
        $this->assertStringContainsString('<img src="a.png"><span><marker style="font-size:0"/></span>', $out);
    }

    public function testSanitizeHTMLPreservesPreNewlinesAndSpaces(): void
    {
        $obj = $this->getInternalTestObject();
        $html = '<pre>line1' . "\n" . ' line2</pre>';

        $out = $obj->exposeSanitizeHTML($html);

        $this->assertStringContainsString('<pre>', $out);
        $this->assertStringContainsString('line1', $out);
        $this->assertStringContainsString('&nbsp;', $out);
        $this->assertStringContainsString('line2', $out);
        $this->assertStringContainsString('</pre>', $out);
    }

    public function testGetHTMLRootPropertiesIncludesExpectedDefaults(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $root = $obj->exposeGetHTMLRootProperties();

        $this->assertArrayHasKey('fontname', $root);
        $this->assertArrayHasKey('fontsize', $root);
        $this->assertArrayHasKey('padding', $root);
        $this->assertArrayHasKey('margin', $root);
        $this->assertSame('black', $root['fgcolor']);
    }

    public function testGetHTMLDOMBuildsRootAndTagNodes(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $dom = $obj->exposeGetHTMLDOM('<p class="x">Hello</p>');

        $this->assertArrayHasKey(0, $dom);
        $this->assertNotEmpty($dom);
        $this->assertGreaterThanOrEqual(2, \count($dom));
    }

    public function testProcessHTMLDOMClosingTagStoresTableHeadAndNoBrAttribute(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $root = $obj->exposeGetHTMLRootProperties();
        /** @var THTMLAttrib $root */
        $dom = [
            0 => \array_replace($root, ['value' => 'table', 'elkey' => 0, 'parent' => 0, 'thead' => '']),
            1 => \array_replace($root, [
                'value' => 'tr',
                'elkey' => 1,
                'parent' => 0,
                'thead' => 'true',
                'attribute' => [],
            ]),
            2 => \array_replace($root, ['value' => 'tr', 'elkey' => 2, 'parent' => 1]),
        ];
        $elm = ['<table>', '<tr>', '</tr>'];

        $obj->exposeProcessHTMLDOMClosingTag($dom, $elm, 2, 1, '<cssarray>x</cssarray>');

        $this->assertSame('true', $dom[1]['attribute']['nobr']);
        $this->assertStringContainsString('<cssarray>x</cssarray><table>', $dom[0]['thead']);
    }

    public function testGetHTMLliBulletReturnsEmptyForCaretType(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, '^');

        $this->assertSame('', $out);
    }

    public function testGetHTMLliBulletSupportsDefaultUnorderedAndOrderedTypes(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $defaultUnordered = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, '!');
        $defaultOrdered = $obj->exposeGetHTMLliBullet(2, 12, 0, 0, '#');

        $this->assertNotSame('', $defaultUnordered);
        $this->assertNotSame('', $defaultOrdered);
        $this->assertNotSame($defaultUnordered, $defaultOrdered);
    }

    public function testGetHTMLliBulletSupportsOrderedFormatVariants(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $decimalLeadingZero = $obj->exposeGetHTMLliBullet(1, 7, 0, 0, 'decimal-leading-zero');
        $upperRoman = $obj->exposeGetHTMLliBullet(1, 7, 0, 0, 'upper-roman');
        $upperAlpha = $obj->exposeGetHTMLliBullet(1, 7, 0, 0, 'upper-alpha');

        $this->assertNotSame('', $decimalLeadingZero);
        $this->assertNotSame('', $upperRoman);
        $this->assertNotSame('', $upperAlpha);
        $this->assertNotSame($decimalLeadingZero, $upperRoman);
        $this->assertNotSame($upperRoman, $upperAlpha);
    }

    public function testPageBreakReturnsCurrentOrNextPageId(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $before = $page->getPageId();
        $after = $obj->exposePageBreak();

        $this->assertGreaterThanOrEqual($before, $after);
    }

    public function testProcessHTMLDOMTextAppliesTransformAndDecodesEntities(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['text-transform' => 'uppercase']),
            1 => $this->makeHtmlNode(['value' => '']),
        ];

        $obj->exposeProcessHTMLDOMText($dom, 'a&amp;b', 1, 0);

        $this->assertSame('a&b', $dom[1]['value']);
    }

    public function testProcessHTMLDOMTextAppliesMappedCaseTransform(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['text-transform' => 'lowercase']),
            1 => $this->makeHtmlNode(['value' => '']),
        ];

        $obj->exposeProcessHTMLDOMText($dom, 'AB&NBSP;C', 1, 0);

        $value = $dom[1]['value'];
        $this->assertStringStartsWith('ab', $value);
    }

    public function testGetHTMLDOMCSSDataSkipsInheritedAndInvalidSelectors(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root', 'csssel' => [' p.x']]),
            1 => $this->makeHtmlNode([
                'value' => 'p',
                'parent' => 0,
                'attribute' => ['class' => 'x'],
            ]),
        ];
        $css = [
            '0010 p.x' => 'color:red;',
            'badselector' => 'color:blue;',
        ];

        $obj->getHTMLDOMCSSData($dom, $css, 1);

        $this->assertEmpty($dom[1]['cssdata']);
    }

    public function testIsValidCSSSelectorForTagCoversAttributeOperatorsAndCombinators(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root', 'tag' => true, 'opening' => true]),
            1 => $this->makeHtmlNode([
                'value' => 'div',
                'parent' => 0,
                'tag' => true,
                'opening' => true,
                'attribute' => ['id' => 'main', 'class' => 'container'],
            ]),
            2 => $this->makeHtmlNode([
                'value' => 'p',
                'parent' => 1,
                'tag' => true,
                'opening' => true,
                'attribute' => ['class' => 'sib'],
            ]),
            3 => $this->makeHtmlNode([
                'value' => 'span',
                'parent' => 1,
                'tag' => true,
                'opening' => true,
                'attribute' => [
                    'class' => 'x y',
                    'id' => 'node',
                    'words' => 'foo bar',
                    'data' => 'prefix-mid-suffix',
                    'lang' => 'en-us',
                ],
            ]),
        ];

        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' span.x'));
        $obj->isValidCSSSelectorForTag($dom, 3, ' span[words~=foo]');
        $obj->isValidCSSSelectorForTag($dom, 3, ' span[data^=prefix]');
        $obj->isValidCSSSelectorForTag($dom, 3, ' span[data$=suffix]');
        $obj->isValidCSSSelectorForTag($dom, 3, ' span[data*=mid]');
        $obj->isValidCSSSelectorForTag($dom, 3, ' span[lang|=en]');
        $obj->isValidCSSSelectorForTag($dom, 3, ' span[id=node]');
        $obj->isValidCSSSelectorForTag($dom, 3, ' div > span.x');
        $obj->isValidCSSSelectorForTag($dom, 3, ' p + span.x');
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 3, ' p ~ span.x'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 3, ' span:hover'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 3, '['));
    }

    public function testParseHTMLStyleAttributesCoversExtendedCssBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $dom = [
            0 => $this->makeHtmlNode([
                'line-height' => 1.2,
                'fontsize' => 10.0,
                'listtype' => 'disc',
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'text-indent' => 2.0,
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'value' => 'a',
                'fontsize' => 10.0,
                'fontstyle' => 'B',
                'attribute' => [
                    'style' => 'direction:rtl;display:none;font-family:helvetica;list-style-type:inherit;'
                        . 'text-indent:3mm;text-transform:capitalize;font-size:12;font-stretch:120;'
                        . 'letter-spacing:0.2;line-height:2;font-weight:normal;font-style:italic;'
                        . 'color:red;background-color:#00ff00;text-decoration:underline line-through overline;'
                        . 'width:20;height:10;text-align:right;padding:1 2 3 4;margin:1 2 3 4;'
                        . 'border:1 solid black;border-color:red green blue black;border-width:1 2 3 4;'
                        . 'border-style:solid dashed dotted double;padding-left:1;padding-right:2;'
                        . 'padding-top:3;padding-bottom:4;margin-left:auto;margin-right:2;'
                        . 'margin-top:1;margin-bottom:3;border-left:1 solid #111;border-right:2 dashed #222;'
                        . 'border-top:3 dotted #333;border-bottom:4 double #444;border-spacing:2;'
                        . 'page-break-inside:avoid;page-break-before:left;page-break-after:right;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame('rtl', $dom[1]['dir']);
        $this->assertTrue($dom[1]['hide']);
        $this->assertSame('disc', $dom[1]['listtype']);
        $this->assertNotSame('', $dom[1]['text-transform']);
        $this->assertGreaterThan(0.0, $dom[1]['fontsize']);
        $this->assertGreaterThan(0.0, $dom[1]['line-height']);
        $this->assertNotSame('', $dom[1]['fgcolor']);
        $this->assertNotSame('', $dom[1]['bgcolor']);
        $this->assertSame('R', $dom[1]['align']);
        $this->assertSame('true', $dom[1]['attribute']['nobr']);
        $this->assertSame('left', $dom[1]['attribute']['pagebreak']);
        $this->assertSame('right', $dom[1]['attribute']['pagebreakafter']);
        $this->assertNotSame(['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0], $dom[1]['padding']);
        $this->assertNotSame(['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0], $dom[1]['margin']);
        $this->assertNotEmpty($dom[1]['border']);
    }

    public function testInheritHTMLPropertiesMergesParentDefaults(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['align' => 'L', 'fontname' => 'helvetica']),
            1 => $this->makeHtmlNode(['align' => 'R']),
        ];

        $obj->exposeInheritHTMLProperties($dom, 1, 0);

        $this->assertSame('R', $dom[1]['align']);
        $this->assertSame('helvetica', $dom[1]['fontname']);
    }

    public function testProcessHTMLDOMOpeningTagMarksNodeAsOpening(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $root = $obj->exposeGetHTMLRootProperties();
        /** @var THTMLAttrib $root */
        $root['parent'] = 0;
        $root['value'] = 'root';
        $dom = [
            0 => $root,
            1 => $root,
        ];
        $dom[1]['parent'] = 0;
        $dom[1]['value'] = 'p';

        $obj->exposeProcessHTMLDOMOpeningTag($dom, [], [0], 'p', 1, false);

        $this->assertTrue($dom[1]['opening']);
    }

    public function testProcessHTMLDOMClosingTagSetsParentContent(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $root = $obj->exposeGetHTMLRootProperties();
        /** @var THTMLAttrib $root */
        $root['parent'] = 0;
        $dom = [
            0 => $root,
            1 => $root,
        ];
        $dom[1]['value'] = 'p';
        $dom[1]['parent'] = 0;
        $elm = ['<p>', '</p>'];

        $obj->exposeProcessHTMLDOMClosingTag($dom, $elm, 1, 0, '');

        $this->assertArrayHasKey('content', $dom[0]);
    }

    public function testProcessHTMLDOMClosingTagHandlesTdContentAndNestedTableHeaderCleanup(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $root = $obj->exposeGetHTMLRootProperties();
        /** @var THTMLAttrib $root */
        $dom = [
            0 => \array_replace($root, ['value' => 'table', 'elkey' => 0, 'parent' => 0]),
            1 => \array_replace($root, ['value' => 'tr', 'elkey' => 1, 'parent' => 0]),
            2 => \array_replace($root, ['value' => 'td', 'elkey' => 2, 'parent' => 1, 'content' => '']),
            3 => \array_replace($root, ['value' => '', 'elkey' => 3, 'parent' => 2, 'tag' => false]),
            4 => \array_replace($root, ['value' => 'td', 'elkey' => 4, 'parent' => 2]),
        ];
        $elm = [
            '<table>',
            '<tr>',
            '<td>',
            '<table><thead>A</thead></table>',
            '</td>',
        ];

        $obj->exposeProcessHTMLDOMClosingTag($dom, $elm, 4, 2, '<cssarray>x</cssarray>');

        $this->assertStringContainsString('<table nested="true">', $dom[2]['content']);
        $this->assertStringNotContainsString('<thead>', $dom[2]['content']);
        $this->assertStringNotContainsString('</thead>', $dom[2]['content']);

        $dom = [
            0 => \array_replace($root, ['value' => 'root', 'elkey' => 0, 'parent' => 0, 'thead' => '<tr nobr="true"></tr>']),
            1 => \array_replace($root, ['value' => 'table', 'elkey' => 1, 'parent' => 0]),
        ];
        $elm = ['<root>', '</table>'];

        $obj->exposeProcessHTMLDOMClosingTag($dom, $elm, 1, 0, '');

        $this->assertStringNotContainsString(' nobr="true"', $dom[0]['thead']);
        $this->assertStringEndsWith('</tablehead>', $dom[0]['thead']);
    }

    public function testProcessHTMLDOMOpeningTagMergesCssAndDetectsSelfClosingTags(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $root = $obj->exposeGetHTMLRootProperties();
        /** @var THTMLAttrib $root */
        $dom = [
            0 => \array_replace($root, ['parent' => 0, 'value' => 'root']),
            1 => \array_replace($root, ['parent' => 0, 'value' => 'img']),
        ];

        $obj->exposeProcessHTMLDOMOpeningTag(
            $dom,
            ['0010 *' => 'color:red;'],
            [0],
            '<img src="x" />',
            1,
            false,
        );

        $this->assertTrue($dom[1]['self']);
        $attr = $dom[1]['attribute'];
        $src = $attr['src'] ?? null;
        $this->assertIsString($src);
        $this->assertSame('x', $src);
    }

    public function testParseHTMLTextRendersTextAndAdvancesCursor(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $elm = $this->makeHtmlNode(['value' => 'x']);
        $tpx = 1.0;
        $tpy = 2.0;
        $tpw = 3.0;
        $tph = 4.0;

        $out = $obj->exposeParseHTMLText($elm, $tpx, $tpy, $tpw, $tph);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('BT', $out);
        $this->assertGreaterThan(1.0, $tpx);
    }

    public function testGetHTMLDOMTextNodesInheritParentFormatting(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $dom = $obj->exposeGetHTMLDOM('<span style="color:red;font-weight:bold">Hello</span>');

        $this->assertSame($dom[1]['fgcolor'], $dom[2]['fgcolor']);
        $this->assertStringContainsString('B', $dom[2]['fontstyle']);
        $this->assertSame('Hello', $dom[2]['value']);
    }

    public function testGetHTMLliBulletNoneAndCustomImageTypeBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $this->assertSame('', $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'none'));

        $this->expectException(\Throwable::class);
        $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'img|png|4|4|missing.png');
    }

    public function testGetHTMLliBulletCoversUnicodeAndAdditionalOrderedTypes(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $this->setObjectProperty($obj, 'isunicode', false);
        $this->setObjectProperty($obj, 'rtl', false);

        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'disc'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'circle'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'square'));

        $this->setObjectProperty($obj, 'isunicode', true);
        $this->setObjectProperty($obj, 'rtl', true);

        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'disc'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'circle'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'square'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'lower-greek'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'hebrew'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'armenian'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'georgian'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'hiragana'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'hiragana-iroha'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'katakana'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'katakana-iroha'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'lower-latin'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'upper-latin'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, '1'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'decimal'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'decimal-leading-zero'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'lower-roman'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'upper-roman'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'lower-alpha'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'upper-alpha'));
        $this->assertNotSame('', $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'fallback-type'));

        $svg = (string) \realpath(__DIR__ . '/../examples/images/testsvg.svg.bak');
        if ($svg !== '') {
            try {
                $out = $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'img|svg|4|4|' . $svg);
                $this->assertNotSame('', $out);
            } catch (\Throwable $e) {
                $this->assertInstanceOf(\Throwable::class, $e);
            }
        }
    }

    public function testGetHTMLCellCoversHiddenNodesAndPageBreakModes(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $html = '<img src="x" style="display:none" />'
            . '<div style="display:none"><span>skip</span></div>'
            . '<p style="page-break-before:right">R</p>'
            . '<p style="page-break-before:always">A</p>';

        $out = $obj->getHTMLCell($html, 0, 0, 20, 6);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('(R)', $out);
        $this->assertStringContainsString('(A)', $out);
        $this->assertStringNotContainsString('skip', $out);
    }

    public function testGetHTMLCellCoversPageBreakAfterModes(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $html = '<p style="page-break-after:right">R</p>'
            . '<p style="page-break-after:always">A</p>'
            . '<p>Z</p>';

        $out = $obj->getHTMLCell($html, 0, 0, 20, 6);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('(R)', $out);
        $this->assertStringContainsString('(A)', $out);
        $this->assertStringContainsString('(Z)', $out);
    }

    public function testGetHTMLCellCoversSelfClosingPageBreakAfterMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $before = $obj->exposePageBreak();

        $html = '<img alt="x" style="page-break-after:always" />'
            . '<p>AfterBreak</p>';

        $out = $obj->getHTMLCell($html, 0, 0, 20, 6);

        $after = $obj->exposePageBreak();

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('(AfterBreak)', $out);
        $this->assertGreaterThan($before + 1, $after);
    }

    public function testGetHTMLCellCoversTcpdfPageBreakMethod(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $html = '<tcpdf method="pagebreak" />'
            . '<p>AfterBreak</p>';

        $out = $obj->getHTMLCell($html, 0, 0, 20, 6);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('(AfterBreak)', $out);
    }

    public function testGetHTMLCellCoversTcpdfSerializedPageBreakData(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $before = $obj->exposePageBreak();
        $payload = \urlencode((string) \json_encode(['m' => 'AddPage', 'p' => []]));
        $hash = \str_repeat('a', 64);
        $data = '64+' . $hash . '+' . $payload;
        $html = '<tcpdf data="' . $data . '" /><p>AfterBreak</p>';

        $out = $obj->getHTMLCell($html, 0, 0, 20, 6);
        $after = $obj->exposePageBreak();

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('(AfterBreak)', $out);
        $this->assertGreaterThan($before, $after);
    }

    public function testGetHTMLCellIgnoresDisallowedTcpdfSerializedMethod(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        /** @var array<string, array<string, int|float>> $beforeDests */
        $beforeDests = $this->getObjectProperty($obj, 'dests');

        $payload = \urlencode((string) \json_encode([
            'm' => 'setNamedDestination',
            'p' => ['blocked-dest', -1, 1.0, 1.0],
        ]));
        $hash = \str_repeat('a', 64);
        $data = '64+' . $hash . '+' . $payload;
        $obj->getHTMLCell('<tcpdf data="' . $data . '" /><p>X</p>', 0, 0, 20, 6);

        /** @var array<string, array<string, int|float>> $afterDests */
        $afterDests = $this->getObjectProperty($obj, 'dests');
        $this->assertCount(\count($beforeDests), $afterDests);
    }

    public function testIsValidCSSSelectorForTagRejectsPseudoClassesAndPseudoElements(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode([
                'value' => 'div',
                'parent' => 0,
                'tag' => true,
                'opening' => true,
            ]),
        ];

        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' div:hover'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' div:focus'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' div::before'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' div::after'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' div:first-child'));
    }

    public function testIsValidCSSSelectorForTagHandlesInvalidSyntax(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode(['value' => 'div', 'parent' => 0]),
        ];

        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ''));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, '['));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ']'));
    }

    public function testSanitizeHTMLHandlesConsecutivePreTags(): void
    {
        $obj = $this->getInternalTestObject();
        $html = '<pre>line1</pre><pre>line2</pre>';

        $out = $obj->exposeSanitizeHTML($html);

        $this->assertStringContainsString('<pre>line1</pre>', $out);
        $this->assertStringContainsString('<pre>line2</pre>', $out);
    }

    public function testSanitizeHTMLHandlesTextareaWithNewlineCharacters(): void
    {
        $obj = $this->getInternalTestObject();
        $html = '<textarea>line1' . "\n" . 'line2</textarea>';

        $out = $obj->exposeSanitizeHTML($html);

        $this->assertStringContainsString('<textarea', $out);
        $this->assertStringContainsString('line1', $out);
        $this->assertStringContainsString('line2', $out);
    }

    public function testSanitizeHTMLHandlesImagesWithoutSrc(): void
    {
        $obj = $this->getInternalTestObject();
        $html = '<img alt="test"><p>after</p>';

        $out = $obj->exposeSanitizeHTML($html);

        $this->assertStringContainsString('<img', $out);
        $this->assertStringContainsString('<p>', $out);
    }

    public function testSanitizeHTMLHandlesEmptySelectAndOption(): void
    {
        $obj = $this->getInternalTestObject();
        $html = '<select></select>';

        $out = $obj->exposeSanitizeHTML($html);

        $this->assertStringContainsString('<select', $out);
    }

    public function testParseHTMLStyleAttributesHandlesLineHeightNormalValue(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['line-height' => 1.0, 'fontsize' => 10.0]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'line-height' => 1.0,
                'attribute' => [
                    'style' => 'line-height:normal;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame(1.0, $dom[1]['line-height']);
    }

    public function testParseHTMLStyleAttributesBorderShorthandParsing(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'attribute' => [
                    'style' => 'border:1px solid black;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertNotEmpty($dom[1]['border']);
    }

    public function testParseHTMLAttributesHandlesFontTagWithSizePrefix(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontstyle' => '']),
            1 => $this->makeHtmlNode([
                'value' => 'font',
                'parent' => 0,
                'attribute' => ['size' => '-1'],
                'style' => [],
                'fontstyle' => '',
                'fontsize' => 10.0,
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);

        $this->assertGreaterThan(0.0, $dom[1]['fontsize']);
        $this->assertLessThan(10.0, $dom[1]['fontsize']);
    }

    public function testParseHTMLAttributesHandlesFontTagWithPlusPrefix(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontstyle' => '']),
            1 => $this->makeHtmlNode([
                'value' => 'font',
                'parent' => 0,
                'attribute' => ['size' => '+2'],
                'style' => [],
                'fontstyle' => '',
                'fontsize' => 10.0,
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);

        $this->assertGreaterThan(10.0, $dom[1]['fontsize']);
    }

    public function testParseHTMLAttributesHandlesHeading2Tag(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontstyle' => '']),
            1 => $this->makeHtmlNode([
                'value' => 'h2',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
                'fontstyle' => '',
                'fontsize' => 10.0,
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);

        $this->assertGreaterThan(10.0, $dom[1]['fontsize']);
        $this->assertStringContainsString('B', $dom[1]['fontstyle']);
    }

    #[DataProvider('htmlLiBulletNamedTypeProvider')]
    public function testGetHTMLliBulletSupportsNamedTypes(string $type, ?string $expectedFragment): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, $type);

        $this->assertNotSame('', $result);
        if ($expectedFragment !== null) {
            $this->assertStringContainsString($expectedFragment, $result);
        }
    }

    public function testProcessHTMLDOMTextAppliesCapitalizeTransform(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['text-transform' => 'capitalize']),
            1 => $this->makeHtmlNode(['value' => '']),
        ];

        $obj->exposeProcessHTMLDOMText($dom, 'hello world', 1, 0);

        $this->assertNotSame('hello world', $dom[1]['value']);
    }

    public function testProcessHTMLDOMClosingTagHandlesNonTableElements(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $root = $obj->exposeGetHTMLRootProperties();
        /** @var THTMLAttrib $root */
        $dom = [
            0 => $root,
            1 => $root,
        ];
        $dom[1]['value'] = 'div';
        $dom[1]['parent'] = 0;
        $elm = ['<div>', '</div>'];

        $obj->exposeProcessHTMLDOMClosingTag($dom, $elm, 1, 0, '');

        $this->assertArrayHasKey('content', $dom[0]);
    }

    public function testProcessHTMLDOMOpeningTagDetectsSelfClosingImg(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $root = $obj->exposeGetHTMLRootProperties();
        /** @var THTMLAttrib $root */
        $dom = [
            0 => $root,
            1 => $root,
        ];
        $dom[1]['parent'] = 0;
        $dom[1]['value'] = 'img';

        $obj->exposeProcessHTMLDOMOpeningTag($dom, [], [0], 'img', 1, false);

        $this->assertTrue($dom[1]['self']);
    }

    public function testProcessHTMLDOMOpeningTagDetectsSelfClosingBr(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $root = $obj->exposeGetHTMLRootProperties();
        /** @var THTMLAttrib $root */
        $dom = [
            0 => $root,
            1 => $root,
        ];
        $dom[1]['parent'] = 0;
        $dom[1]['value'] = 'br';

        $obj->exposeProcessHTMLDOMOpeningTag($dom, [], [0], 'br', 1, false);

        $this->assertTrue($dom[1]['self']);
    }

    public function testPageBreakMovesToNextPageRegion(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $pid = $obj->exposePageBreak();

        $this->assertGreaterThan(0, $pid);
    }

    public function testInheritHTMLPropertiesPreservesChildOverrides(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['align' => 'L', 'fontname' => 'helvetica', 'fontsize' => 10.0]),
            1 => $this->makeHtmlNode(['align' => 'R', 'fontsize' => 0.0]),
        ];

        $obj->exposeInheritHTMLProperties($dom, 1, 0);

        $this->assertSame('R', $dom[1]['align']);
        $this->assertSame('helvetica', $dom[1]['fontname']);
        $this->assertSame(0.0, $dom[1]['fontsize']);
    }

    public function testGetHTMLDOMCSSDataHandlesMultiplePriorities(): void
    {
        $obj = $this->getTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root', 'csssel' => ['0010 p', '0020 p.x', '0005 p']]),
            1 => $this->makeHtmlNode([
                'value' => 'p',
                'parent' => 0,
                'attribute' => ['class' => 'x'],
            ]),
        ];
        $css = [
            '0010 p' => 'color:red;',
            '0020 p.x' => 'color:blue;',
            '0005 p' => 'color:green;',
        ];

        $obj->getHTMLDOMCSSData($dom, $css, 1);

        $this->assertNotEmpty($dom[1]['cssdata']);
        $this->assertGreaterThanOrEqual(2, \count($dom[1]['cssdata']));
    }

    public function testIsValidCSSSelectorForTagCoversMultipleCases(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'div', 'tag' => true, 'opening' => true]),
            1 => $this->makeHtmlNode([
                'value' => 'p',
                'parent' => 0,
                'tag' => true,
                'opening' => true,
                'attribute' => ['id' => 'main'],
            ]),
        ];

        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' p'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' p#main'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' span'));
    }

    public function testParseHTMLAttributesHandlesTableRowsAndCols(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0]),
            1 => $this->makeHtmlNode([
                'value' => 'table',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
            ]),
            2 => $this->makeHtmlNode([
                'value' => 'tr',
                'parent' => 1,
                'attribute' => [],
                'style' => [],
            ]),
            3 => $this->makeHtmlNode([
                'value' => 'td',
                'parent' => 2,
                'attribute' => ['colspan' => '2', 'rowspan' => '2'],
                'style' => [],
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);
        $obj->parseHTMLAttributes($dom, 2, false);
        $obj->parseHTMLAttributes($dom, 3, false);

        $this->assertGreaterThan(0, $dom[1]['rows']);
        $this->assertSame('2', $dom[3]['attribute']['rowspan']);
    }

    public function testParseHTMLStyleAttributesHandlesMultipleBorderSides(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'attribute' => [
                    'style' => 'border-left:1 solid red;border-right:2 dashed blue;'
                        . 'border-top:3 dotted green;border-bottom:4 double black;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertNotEmpty($dom[1]['border']);
    }

    public function testParseHTMLStyleAttributesHandlesPaddingAndMarginValues(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'attribute' => [
                    'style' => 'padding:5px 10px;margin:1px 2px 3px 4px;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertNotSame(['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0], $dom[1]['padding']);
        $this->assertNotSame(['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0], $dom[1]['margin']);
    }

    public function testParseHTMLAttributesHandlesStrongAndEmphasisTags(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontstyle' => '']),
            1 => $this->makeHtmlNode([
                'value' => 'strong',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
                'fontstyle' => '',
            ]),
            2 => $this->makeHtmlNode([
                'value' => 'em',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
                'fontstyle' => '',
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);
        $obj->parseHTMLAttributes($dom, 2, false);

        $this->assertStringContainsString('B', $dom[1]['fontstyle']);
        $this->assertStringContainsString('I', $dom[2]['fontstyle']);
    }

    public function testParseHTMLAttributesHandlesUnderlineTag(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontstyle' => '']),
            1 => $this->makeHtmlNode([
                'value' => 'u',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
                'fontstyle' => '',
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);

        $this->assertStringContainsString('U', $dom[1]['fontstyle']);
    }

    public function testParseHTMLAttributesHandlesDeleteTag(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontstyle' => '']),
            1 => $this->makeHtmlNode([
                'value' => 'del',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
                'fontstyle' => '',
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);

        $this->assertStringContainsString('D', $dom[1]['fontstyle']);
    }

    public function testParseHTMLAttributesHandlesPreTag(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontname' => 'helvetica']),
            1 => $this->makeHtmlNode([
                'value' => 'pre',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
                'fontname' => 'helvetica',
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);

        $this->assertNotSame('helvetica', $dom[1]['fontname']);
    }

    public function testParseHTMLAttributesHandleTtTag(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontname' => 'helvetica']),
            1 => $this->makeHtmlNode([
                'value' => 'tt',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
                'fontname' => 'helvetica',
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);

        $this->assertNotSame('helvetica', $dom[1]['fontname']);
    }

    public function testSanitizeHTMLPreservesHeadingTags(): void
    {
        $obj = $this->getInternalTestObject();
        $html = '<h1>Title</h1><h2>Subtitle</h2>';

        $out = $obj->exposeSanitizeHTML($html);

        $this->assertStringContainsString('<h1>', $out);
        $this->assertStringContainsString('<h2>', $out);
        $this->assertStringContainsString('Title', $out);
    }

    public function testSanitizeHTMLHandlesDivWrappers(): void
    {
        $obj = $this->getInternalTestObject();
        $html = '<div class="container"><p>Content</p></div>';

        $out = $obj->exposeSanitizeHTML($html);

        $this->assertStringContainsString('<div', $out);
        $this->assertStringContainsString('<p>', $out);
    }

    public function testParseHTMLAttributesHandlesListTypeInheritance(): void
    {
        $obj = $this->getTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['align' => 'L']),
            1 => $this->makeHtmlNode([
                'value' => 'ul',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
                'align' => '',
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);

        $this->assertNotSame('', $dom[1]['align']);
    }

    public function testGetHTMLliBulletHandlesDepthCycling(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result1 = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, '!');
        $result2 = $obj->exposeGetHTMLliBullet(2, 1, 0, 0, '!');
        $result3 = $obj->exposeGetHTMLliBullet(4, 1, 0, 0, '!');

        $this->assertNotSame('', $result1);
        $this->assertNotSame('', $result2);
        $this->assertNotSame('', $result3);
    }

    #[DataProvider('htmlLiBulletShapeProvider')]
    public function testGetHTMLliBulletShapeVariants(
        string $type,
        bool $isunicode,
        bool $rtl,
        float $posx,
        float $posy,
    ): void {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'isunicode', $isunicode);
        $this->setObjectProperty($obj, 'rtl', $rtl);

        $result = $obj->exposeGetHTMLliBullet(1, 1, $posx, $posy, $type);

        $this->assertNotSame('', $result);
        $this->assertGreaterThan(0, \strlen($result));
    }

    #[DataProvider('htmlLiBulletNumericFormatProvider')]
    public function testGetHTMLliBulletNumericFormats(string $type, int $count, string $expectedFragment): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, $count, 0, 0, $type);

        $this->assertNotSame('', $result);
        $this->assertStringContainsString($expectedFragment, $result);
    }

    #[DataProvider('htmlLiBulletTextDirectionProvider')]
    public function testGetHTMLliBulletTextFormattingByDirection(bool $rtl, string $expectedFragment): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'rtl', $rtl);

        $result = $obj->exposeGetHTMLliBullet(1, 10, 0, 0, 'decimal');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString($expectedFragment, $result);
    }

    #[DataProvider('htmlLiBulletScriptTypeProvider')]
    public function testGetHTMLliBulletUnicodeAndScriptTypes(string $type): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        if ($type === 'cjk-ideographic') {
            /** @var \Com\Tecnick\Pdf\Font\Stack $font */
            $font = $this->getObjectProperty($obj, 'font');
            /** @var int $pon */
            $pon = $this->getObjectProperty($obj, 'pon');
            $fontfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/cid0/cid0jp.json');
            if ($fontfile === '') {
                $this->markTestSkipped('CID0JP font definition is not available.');
            }
            $font->insert($pon, 'cid0jp', '', 10, null, null, $fontfile);
        }

        $count = ($type === 'cjk-ideographic') ? 1 : 3;
        $result = $obj->exposeGetHTMLliBullet(1, $count, 0, 0, $type);

        $this->assertNotSame('', $result);
    }

    public function testGetHTMLliBulletEmptyTypeStringFallsBackToDefault(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, '');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('5', $result);
    }

    #[DataProvider('htmlLiBulletCountProvider')]
    public function testGetHTMLliBulletCountFormatting(int $count, string $expectedFragment): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, $count, 0, 0, 'decimal');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString($expectedFragment, $result);
    }

    #[DataProvider('htmlLiBulletAlphaBoundaryProvider')]
    public function testGetHTMLliBulletAlphaBoundaryCase(
        string $type,
        string $expectedLast,
        string $expectedFirst,
    ): void {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $resultZ = $obj->exposeGetHTMLliBullet(1, 26, 0, 0, $type);
        $resultA = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, $type);

        $this->assertNotSame('', $resultZ);
        $this->assertNotSame('', $resultA);
        $this->assertStringContainsString($expectedLast, $resultZ);
        $this->assertStringContainsString($expectedFirst, $resultA);
    }

    public function testGetHTMLliBulletWithNonZeroPositions(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 5, 100, 200, 'decimal');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('5', $result);
    }

    public function testGetHTMLliBulletDepthModuloCalculation(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'isunicode', true);

        $depthOne = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, '!');
        $depthFour = $obj->exposeGetHTMLliBullet(4, 1, 0, 0, '!');
        $depthSeven = $obj->exposeGetHTMLliBullet(7, 1, 0, 0, '!');

        $this->assertNotSame('', $depthOne);
        $this->assertNotSame('', $depthFour);
        $this->assertNotSame('', $depthSeven);
    }

    /** @return array<string, array{0: string}> */
    public static function htmlLiBulletScriptTypeProvider(): array
    {
        return [
            'lower-greek' => ['lower-greek'],
            'hebrew' => ['hebrew'],
            'armenian' => ['armenian'],
            'georgian' => ['georgian'],
            'cjk-ideographic' => ['cjk-ideographic'],
            'hiragana' => ['hiragana'],
            'hiragana-iroha' => ['hiragana-iroha'],
            'katakana' => ['katakana'],
            'katakana-iroha' => ['katakana-iroha'],
        ];
    }

    /** @return array<string, array{0: int, 1: string}> */
    public static function htmlLiBulletCountProvider(): array
    {
        return [
            'count-one' => [1, '1.'],
            'count-large' => [999, '999'],
        ];
    }

    /** @return array<string, array{0: string, 1: int, 2: string}> */
    public static function htmlLiBulletNumericFormatProvider(): array
    {
        return [
            'decimal' => ['decimal', 42, '42.'],
            'short-decimal' => ['1', 15, '15.'],
            'leading-zero' => ['decimal-leading-zero', 5, '05'],
        ];
    }

    /** @return array<string, array{0: bool, 1: string}> */
    public static function htmlLiBulletTextDirectionProvider(): array
    {
        return [
            'rtl' => [true, '.10'],
            'ltr' => [false, '10.'],
        ];
    }

    /** @return array<string, array{0: string, 1: string, 2: string}> */
    public static function htmlLiBulletAlphaBoundaryProvider(): array
    {
        return [
            'lower-alpha' => ['lower-alpha', 'z', 'a'],
            'upper-alpha' => ['upper-alpha', 'Z', 'A'],
        ];
    }

    public function testGetHTMLliBulletImageTypeParsing(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $this->expectException(\Throwable::class);
        $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'img|png|10|10|/nonexistent/file.png');
    }

    #[DataProvider('htmlLiBulletShortAlphaProvider')]
    public function testGetHTMLliBulletShortAlphaForms(string $type, string $expectedFragment): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, $type);

        $this->assertNotSame('', $result);
        $this->assertStringContainsString($expectedFragment, $result);
    }

    /** @return array<string, array{0: string, 1: ?string}> */
    public static function htmlLiBulletNamedTypeProvider(): array
    {
        return [
            'lower-alpha' => ['lower-alpha', null],
            'lower-latin' => ['lower-latin', null],
            'upper-alpha' => ['upper-alpha', null],
            'upper-latin' => ['upper-latin', null],
            'roman-lower-short' => ['i', 'v'],
            'roman-lower-name' => ['lower-roman', 'v'],
            'roman-upper-name' => ['upper-roman', 'V'],
            'roman-upper-short' => ['I', 'V'],
        ];
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function htmlLiBulletShortAlphaProvider(): array
    {
        return [
            'short-lower-a' => ['a', 'e'],
            'short-upper-a' => ['A', 'E'],
        ];
    }

    /** @return array<string, array{0: string, 1: bool, 2: bool, 3: float, 4: float}> */
    public static function htmlLiBulletShapeProvider(): array
    {
        return [
            'unicode-disc' => ['disc', true, false, 0.0, 0.0],
            'unicode-circle' => ['circle', true, false, 0.0, 0.0],
            'unicode-square' => ['square', true, false, 0.0, 0.0],
            'non-unicode-disc' => ['disc', false, false, 0.0, 0.0],
            'non-unicode-circle' => ['circle', false, false, 0.0, 0.0],
            'non-unicode-square' => ['square', false, false, 0.0, 0.0],
            'rtl-disc' => ['disc', false, true, 10.0, 5.0],
            'rtl-circle' => ['circle', false, true, 10.0, 5.0],
            'rtl-square' => ['square', false, true, 10.0, 5.0],
        ];
    }
}
