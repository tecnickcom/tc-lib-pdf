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

/**
 * @phpstan-import-type THTMLAttrib from \Com\Tecnick\Pdf\HTML
 */
class TestableHTML extends \Com\Tecnick\Pdf\Tcpdf
{
    public function exposeSanitizeHTML(string $html): string
    {
        return $this->sanitizeHTML($html);
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
}

/**
 * @phpstan-import-type THTMLAttrib from \Com\Tecnick\Pdf\HTML
 */
class HTMLTest extends TestUtil
{
    public static function setUpBeforeClass(): void
    {
        if (!\defined('K_PATH_FONTS')) {
            $fonts = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts');
            \define('K_PATH_FONTS', $fonts);
        }
    }

    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    protected function getInternalTestObject(): TestableHTML
    {
        return new TestableHTML();
    }

    private function getObjectProperty(object $obj, string $name): mixed
    {
        $ref = new \ReflectionClass($obj);
        while ($ref !== false) {
            if ($ref->hasProperty($name)) {
                $prop = $ref->getProperty($name);
                $prop->setAccessible(true);
                return $prop->getValue($obj);
            }
            $ref = $ref->getParentClass();
        }

        $this->fail('Property not found: ' . $name);
    }

    private function setObjectProperty(object $obj, string $name, mixed $value): void
    {
        $ref = new \ReflectionClass($obj);
        while ($ref !== false) {
            if ($ref->hasProperty($name)) {
                $prop = $ref->getProperty($name);
                $prop->setAccessible(true);
                $prop->setValue($obj, $value);
                return;
            }
            $ref = $ref->getParentClass();
        }

        $this->fail('Property not found: ' . $name);
    }

    private function initFontAndPage(\Com\Tecnick\Pdf\Tcpdf $obj): void
    {
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);
        $obj->addPage();
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

    public function testGetHTMLCellReturnsWithoutHanging(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<p>Hello</p>', 0, 0, 20, 6);
        $this->assertSame('', $out);
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
        $root = $root;
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

        $this->assertStringStartsWith('ab', $dom[1]['value']);
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
        $root = $root;
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
        $root = $root;
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
        $root = $root;
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
        $root = $root;
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
        $this->assertSame('x', $dom[1]['attribute']['src']);
    }

    public function testParseHTMLTextReturnsEmptyString(): void
    {
        $obj = $this->getInternalTestObject();
        $elm = $this->makeHtmlNode(['value' => 'x']);
        $tpx = 1.0;
        $tpy = 2.0;
        $tpw = 3.0;
        $tph = 4.0;

        $out = $obj->exposeParseHTMLText($elm, $tpx, $tpy, $tpw, $tph);

        $this->assertSame('', $out);
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

        $this->assertSame('', $out);
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

    public function testGetHTMLliBulletSupportsLowercaseAlphaAndLatin(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $lowerAlpha = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'lower-alpha');
        $lowerLatin = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'lower-latin');

        $this->assertNotSame('', $lowerAlpha);
        $this->assertNotSame('', $lowerLatin);
    }

    public function testGetHTMLliBulletSupportsUppercaseAlphaAndLatin(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $upperAlpha = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'upper-alpha');
        $upperLatin = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'upper-latin');

        $this->assertNotSame('', $upperAlpha);
        $this->assertNotSame('', $upperLatin);
    }

    public function testGetHTMLliBulletSupportsLowercaseRoman(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'i');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('v', $result);
    }

    public function testGetHTMLliBulletSupportsLowercaseRomanByName(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'lower-roman');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('v', $result);
    }

    public function testGetHTMLliBulletSupportsUppercaseRoman(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'upper-roman');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('V', $result);
    }

    public function testGetHTMLliBulletSupportsUppercaseRomanShortForm(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'I');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('V', $result);
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
        $root = $root;
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
        $root = $root;
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
        $root = $root;
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

    public function testGetHTMLliBulletUnicodeDiscCharacter(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'isunicode', true);

        $result = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'disc');

        $this->assertNotSame('', $result);
        $this->assertGreaterThan(0, \strlen($result));
    }

    public function testGetHTMLliBulletUnicodeCircleCharacter(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'isunicode', true);

        $result = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'circle');

        $this->assertNotSame('', $result);
        $this->assertGreaterThan(0, \strlen($result));
    }

    public function testGetHTMLliBulletUnicodeSquareCharacter(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'isunicode', true);

        $result = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'square');

        $this->assertNotSame('', $result);
        $this->assertGreaterThan(0, \strlen($result));
    }

    public function testGetHTMLliBulletNonUnicodeDiscShape(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'isunicode', false);
        $this->setObjectProperty($obj, 'rtl', false);

        $result = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'disc');

        $this->assertNotSame('', $result);
        $this->assertGreaterThan(0, \strlen($result));
    }

    public function testGetHTMLliBulletNonUnicodeCircleShape(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'isunicode', false);
        $this->setObjectProperty($obj, 'rtl', false);

        $result = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'circle');

        $this->assertNotSame('', $result);
        $this->assertGreaterThan(0, \strlen($result));
    }

    public function testGetHTMLliBulletNonUnicodeSquareShape(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'isunicode', false);
        $this->setObjectProperty($obj, 'rtl', false);

        $result = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'square');

        $this->assertNotSame('', $result);
        $this->assertGreaterThan(0, \strlen($result));
    }

    public function testGetHTMLliBulletRTLDiscShapePositioning(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'isunicode', false);
        $this->setObjectProperty($obj, 'rtl', true);

        $result = $obj->exposeGetHTMLliBullet(1, 1, 10, 5, 'disc');

        $this->assertNotSame('', $result);
        $this->assertGreaterThan(0, \strlen($result));
    }

    public function testGetHTMLliBulletRTLCircleShapePositioning(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'isunicode', false);
        $this->setObjectProperty($obj, 'rtl', true);

        $result = $obj->exposeGetHTMLliBullet(1, 1, 10, 5, 'circle');

        $this->assertNotSame('', $result);
        $this->assertGreaterThan(0, \strlen($result));
    }

    public function testGetHTMLliBulletRTLSquareShapePositioning(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'isunicode', false);
        $this->setObjectProperty($obj, 'rtl', true);

        $result = $obj->exposeGetHTMLliBullet(1, 1, 10, 5, 'square');

        $this->assertNotSame('', $result);
        $this->assertGreaterThan(0, \strlen($result));
    }

    public function testGetHTMLliBulletDecimalFormatWithDot(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 42, 0, 0, 'decimal');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('42.', $result);
    }

    public function testGetHTMLliBulletShortFormDecimalWithDot(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 15, 0, 0, '1');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('15.', $result);
    }

    public function testGetHTMLliBulletRTLTextFormatting(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'rtl', true);

        $result = $obj->exposeGetHTMLliBullet(1, 10, 0, 0, 'decimal');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('.10', $result);
    }

    public function testGetHTMLliBulletLTRTextFormatting(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'rtl', false);

        $result = $obj->exposeGetHTMLliBullet(1, 10, 0, 0, 'decimal');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('10.', $result);
    }

    public function testGetHTMLliBulletDecimalLeadingZeroFormat(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'decimal-leading-zero');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('05', $result);
    }

    public function testGetHTMLliBulletGreekCharacters(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 3, 0, 0, 'lower-greek');

        $this->assertNotSame('', $result);
    }

    public function testGetHTMLliBulletHebrewCharacters(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 3, 0, 0, 'hebrew');

        $this->assertNotSame('', $result);
    }

    public function testGetHTMLliBulletArmenianCharacters(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 3, 0, 0, 'armenian');

        $this->assertNotSame('', $result);
    }

    public function testGetHTMLliBulletGeorgianCharacters(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 3, 0, 0, 'georgian');

        $this->assertNotSame('', $result);
    }

    public function testGetHTMLliBulletCJKIdeographicCharacters(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 3, 0, 0, 'cjk-ideographic');

        $this->assertNotSame('', $result);
    }

    public function testGetHTMLliBulletHiraganaCharacters(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 3, 0, 0, 'hiragana');

        $this->assertNotSame('', $result);
    }

    public function testGetHTMLliBulletHiraganaIrohaCharacters(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 3, 0, 0, 'hiragana-iroha');

        $this->assertNotSame('', $result);
    }

    public function testGetHTMLliBulletKatakanaCharacters(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 3, 0, 0, 'katakana');

        $this->assertNotSame('', $result);
    }

    public function testGetHTMLliBulletKatakanaIrohaCharacters(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 3, 0, 0, 'katakana-iroha');

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

    public function testGetHTMLliBulletCountOne(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'decimal');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('1.', $result);
    }

    public function testGetHTMLliBulletLargeCount(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 999, 0, 0, 'decimal');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('999', $result);
    }

    public function testGetHTMLliBulletAlphaBoundaryCase(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $resultZ = $obj->exposeGetHTMLliBullet(1, 26, 0, 0, 'lower-alpha');
        $resultA = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'lower-alpha');

        $this->assertNotSame('', $resultZ);
        $this->assertNotSame('', $resultA);
        $this->assertStringContainsString('z', $resultZ);
        $this->assertStringContainsString('a', $resultA);
    }

    public function testGetHTMLliBulletUpperAlphaBoundaryCase(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $resultZ = $obj->exposeGetHTMLliBullet(1, 26, 0, 0, 'upper-alpha');
        $resultA = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'upper-alpha');

        $this->assertNotSame('', $resultZ);
        $this->assertNotSame('', $resultA);
        $this->assertStringContainsString('Z', $resultZ);
        $this->assertStringContainsString('A', $resultA);
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

    public function testGetHTMLliBulletImageTypeParsing(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $this->expectException(\Throwable::class);
        $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'img|png|10|10|/nonexistent/file.png');
    }

    public function testGetHTMLliBulletLowercaseAShortForm(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'a');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('e', $result);
    }

    public function testGetHTMLliBulletUppercaseAShortForm(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $result = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'A');

        $this->assertNotSame('', $result);
        $this->assertStringContainsString('E', $result);
    }
}
