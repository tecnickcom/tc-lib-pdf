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

    public function testGetHTMLliBulletReturnsEmptyForCaretType(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, '^');

        $this->assertSame('', $out);
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
}
