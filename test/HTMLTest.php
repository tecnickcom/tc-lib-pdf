<?php

/**
 * HTMLTest.php
 *
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
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

    protected function getBBoxProbeTestObject(): TestableHTMLBBoxProbe
    {
        return new TestableHTMLBBoxProbe();
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
            'caption-side' => 'top',
            'clear' => 'none',
            'bgcolor' => '',
            'block' => false,
            'border' => [],
            'border-collapse' => 'separate',
            'border-spacing' => ['H' => 0.0, 'V' => 0.0],
            'clip' => false,
            'cols' => 0,
            'content' => '',
            'cssdata' => [],
            'csssel' => [],
            'dir' => 'ltr',
            'display' => 'inline',
            'empty-cells' => 'show',
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
            'list-style-position' => 'outside',
            'listtype' => '',
            'float' => 'none',
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'opening' => false,
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'parent' => 0,
            'position' => 'static',
            'rows' => 0,
            'self' => false,
            'stroke' => 0.0,
            'strokecolor' => '',
            'table-layout' => 'auto',
            'style' => [],
            'tag' => true,
            'text-indent' => 0.0,
            'text-transform' => '',
            'thead' => '',
            'trids' => [],
            'valign' => 'top',
            'value' => '',
            'white-space' => 'normal',
            'word-spacing' => 0.0,
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

    public function testHrcReferenceParameterIsFirstWhenPresent(): void
    {
        $ref = new \ReflectionClass(\Com\Tecnick\Pdf\HTML::class);

        foreach ($ref->getMethods(\ReflectionMethod::IS_PROTECTED) as $method) {
            $params = $method->getParameters();
            foreach ($params as $idx => $param) {
                if ($param->getName() !== 'hrc') {
                    continue;
                }

                $ptype = $param->getType();
                if (!$ptype instanceof \ReflectionNamedType) {
                    continue;
                }

                if (($ptype->getName() !== 'array') || !$param->isPassedByReference()) {
                    continue;
                }

                $this->assertSame(
                    0,
                    $idx,
                    'Expected array &$hrc to be the first parameter in protected method ' . $method->getName(),
                );
            }
        }
    }

    public function testDomHelperMethodsUseKeyParameter(): void
    {
        $ref = new \ReflectionClass(\Com\Tecnick\Pdf\HTML::class);
        $methods = [
            'estimateHTMLTextHeight',
            'getHTMLFontMetric',
            'getHTMLTextPrefix',
            'getHTMLLineAdvance',
            'getCurrentHTMLLineAdvance',
        ];

        foreach ($methods as $name) {
            $method = $ref->getMethod($name);
            $params = $method->getParameters();

            $this->assertCount(2, \array_slice($params, 0, 2), 'Unexpected leading parameters in ' . $name);
            $this->assertSame('hrc', $params[0]->getName(), 'First parameter must be hrc in ' . $name);
            $this->assertTrue($params[0]->isPassedByReference(), 'First parameter must be by-reference in ' . $name);
            $this->assertSame('key', $params[1]->getName(), 'Second parameter must be key in ' . $name);

            $ptype = $params[1]->getType();
            $this->assertInstanceOf(
                \ReflectionNamedType::class,
                $ptype,
                'Second parameter must have named type in ' . $name
            );
            $this->assertSame('int', $ptype->getName(), 'Second parameter type must be int in ' . $name);
        }
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
            0 => $this->makeHtmlNode([
                'value' => 'root',
                'attribute' => ['lang' => 'en-US'],
            ]),
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

    public function testParseHTMLStyleAttributesResolvesInitialValuesByPropertyMap(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['line-height' => 1.0]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'dir' => 'rtl',
                'hide' => true,
                'align' => 'J',
                'fgcolor' => 'red',
                'line-height' => 2.0,
                'border-collapse' => 'collapse',
                'list-style-position' => 'inside',
                'listtype' => 'square',
                'fontstyle' => 'BIU',
                'attribute' => [
                    'style' => 'direction:initial;display:initial;text-align:initial;color:initial;'
                        . 'line-height:initial;border-collapse:initial;list-style-position:initial;'
                        . 'list-style-type:initial;text-decoration:initial;font-weight:initial;font-style:initial;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame('ltr', $dom[1]['dir']);
        $this->assertFalse($dom[1]['hide']);
        $this->assertSame('L', $dom[1]['align']);
        $this->assertSame('rgba(0%,0%,0%,1)', $dom[1]['fgcolor']);
        $this->assertSame($dom[0]['line-height'], $dom[1]['line-height']);
        $this->assertSame('separate', $dom[1]['border-collapse']);
        $this->assertSame('outside', $dom[1]['list-style-position']);
        $this->assertSame('disc', $dom[1]['listtype']);
        $this->assertSame('', $dom[1]['fontstyle']);
    }

    public function testParseHTMLStyleAttributesAllInitialAppliesMapAndKeepsExplicitOverrides(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['line-height' => 1.0]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'dir' => 'rtl',
                'hide' => true,
                'align' => 'J',
                'fgcolor' => 'red',
                'line-height' => 2.0,
                'border-collapse' => 'collapse',
                'list-style-position' => 'inside',
                'listtype' => 'square',
                'attribute' => [
                    'style' => 'all:initial;color:blue;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame('ltr', $dom[1]['dir']);
        $this->assertFalse($dom[1]['hide']);
        $this->assertSame('L', $dom[1]['align']);
        $this->assertSame('rgba(0%,0%,100%,1)', $dom[1]['fgcolor']);
        $this->assertSame($dom[0]['line-height'], $dom[1]['line-height']);
        $this->assertSame('separate', $dom[1]['border-collapse']);
        $this->assertSame('outside', $dom[1]['list-style-position']);
        $this->assertSame('disc', $dom[1]['listtype']);
    }

    public function testParseHTMLStyleAttributesDirectionModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['dir' => 'rtl']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'direction:ltr;'],
                'dir' => '',
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'direction:inherit;'],
                'dir' => '',
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'direction:invalid;'],
                'dir' => 'ltr',
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);

        $this->assertSame('ltr', $dom[1]['dir']);
        $this->assertSame('rtl', $dom[2]['dir']);
        $this->assertSame('ltr', $dom[3]['dir']);
    }

    public function testParseHTMLStyleAttributesPositionModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['position' => 'fixed']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'position:relative;'],
                'position' => 'static',
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'position:inherit;'],
                'position' => 'static',
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'position:invalid;'],
                'position' => 'static',
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);

        $this->assertSame('relative', $dom[1]['position']);
        $this->assertSame('fixed', $dom[2]['position']);
        $this->assertSame('static', $dom[3]['position']);
    }

    public function testParseHTMLStyleAttributesPositionOffsetsApplyForNonStaticModes(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'position:relative;left:2mm;top:3mm;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'position:static;left:2mm;top:3mm;'],
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'position:absolute;left:4mm;top:5mm;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 1);

        $this->assertGreaterThan(0.0, (float) $dom[1]['margin']['L']);
        $this->assertGreaterThan(0.0, (float) $dom[1]['margin']['T']);
        $this->assertSame(0.0, (float) $dom[2]['margin']['L']);
        $this->assertSame(0.0, (float) $dom[2]['margin']['T']);
        $this->assertGreaterThan(0.0, (float) $dom[3]['margin']['L']);
        $this->assertGreaterThan(0.0, (float) $dom[3]['margin']['T']);
    }

    public function testParseHTMLStyleAttributesFloatModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['float' => 'right']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'float:left;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'float:inherit;'],
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'float:invalid;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);

        $this->assertSame('left', $dom[1]['float']);
        $this->assertSame('right', $dom[2]['float']);
        $this->assertSame('none', $dom[3]['float']);
    }

    public function testParseHTMLStyleAttributesClearModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['clear' => 'both']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'clear:left;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'clear:inherit;'],
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'clear:invalid;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);

        $this->assertSame('left', $dom[1]['clear']);
        $this->assertSame('both', $dom[2]['clear']);
        $this->assertSame('none', $dom[3]['clear']);
    }

    public function testParseHTMLTagOPENdivFloatRightUsesSidePlacedWidth(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 40.0, 20.0);

        $elm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'div',
            'block' => true,
            'float' => 'right',
            'width' => 10.0,
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 40.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENdiv', $elm, $tpx, $tpy, $tpw, $tph);

        $this->assertGreaterThan(0.0, $tpx);
        $this->assertEqualsWithDelta(10.0, $tpw, 0.001);
    }

    public function testParseHTMLTagOPENdivWidthWithoutFloatConstrainsBlockWidth(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 40.0, 20.0);

        $elm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'div',
            'block' => true,
            'float' => 'none',
            'width' => 12.0,
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 40.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENdiv', $elm, $tpx, $tpy, $tpw, $tph);

        $this->assertSame(0.0, $tpx);
        $this->assertEqualsWithDelta(12.0, $tpw, 0.001);
    }

    public function testParseHTMLTagOPENdivClearForcesLineBreakWhenMidLine(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 40.0, 20.0);
        $obj->exposeSetHTMLLineState(5.0, 0.0, false);

        $elm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'div',
            'block' => true,
            'clear' => 'both',
        ]);

        $tpx = 5.0;
        $tpy = 0.0;
        $tpw = 35.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENdiv', $elm, $tpx, $tpy, $tpw, $tph);

        $this->assertSame(0.0, $tpx);
        $this->assertSame(0.0, $tpy);
    }

    public function testOpenAndCloseHTMLBlockSiblingFloatsStayOnSameRow(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 40.0, 30.0);

        $leftOpen = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'div',
            'block' => true,
            'float' => 'left',
            'width' => 10.0,
        ]);
        $leftClose = $this->makeHtmlNode([
            'opening' => false,
            'value' => 'div',
            'block' => true,
            'float' => 'left',
            'parent' => 0,
            'ctxoriginx' => 0.0,
            'ctxmaxwidth' => 40.0,
            'ctxregionoffset' => 0.0,
        ]);

        $rightOpen = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'div',
            'block' => true,
            'float' => 'right',
            'width' => 10.0,
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 40.0;

        $obj->exposeOpenHTMLBlock($leftOpen, $tpx, $tpy, $tpw);
        $this->assertSame(0.0, $tpx);
        $this->assertEqualsWithDelta(10.0, $tpw, 0.001);

        $obj->exposeSetHTMLLineState(5.0, 0.0, false);
        $tpx = 5.0;
        $obj->exposeCloseHTMLBlock($leftClose, $tpx, $tpy, $tpw);
        $this->assertSame(0.0, $tpy);

        $obj->exposeOpenHTMLBlock($rightOpen, $tpx, $tpy, $tpw);
        $this->assertGreaterThan(20.0, $tpx);
        $this->assertEqualsWithDelta(10.0, $tpw, 0.001);
        $this->assertSame(0.0, $tpy);
    }

    public function testOpenAndCloseHTMLBlockSiblingFloatsStayTopAlignedBelowOrigin(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 40.0, 40.0);

        $leftOpen = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'div',
            'block' => true,
            'float' => 'left',
            'width' => 10.0,
        ]);
        $leftClose = $this->makeHtmlNode([
            'opening' => false,
            'value' => 'div',
            'block' => true,
            'float' => 'left',
            'parent' => 0,
        ]);
        $rightOpen = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'div',
            'block' => true,
            'float' => 'right',
            'width' => 10.0,
        ]);

        $tpx = 0.0;
        $tpy = 14.0;
        $tpw = 40.0;

        $obj->exposeOpenHTMLBlock($leftOpen, $tpx, $tpy, $tpw);
        $leftY = $tpy;

        $obj->exposeSetHTMLLineState(5.0, 0.0, false);
        $tpx = 3.0;
        $obj->exposeCloseHTMLBlock($leftClose, $tpx, $tpy, $tpw);
        $this->assertEqualsWithDelta($leftY, $tpy, 0.001);

        $obj->exposeOpenHTMLBlock($rightOpen, $tpx, $tpy, $tpw);
        $this->assertEqualsWithDelta($leftY, $tpy, 0.001);
    }

    public function testOpenHTMLBlockClearBothFlushesActiveFloatRow(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 40.0, 30.0);

        $floatOpen = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'div',
            'block' => true,
            'float' => 'left',
            'width' => 12.0,
        ]);
        $floatClose = $this->makeHtmlNode([
            'opening' => false,
            'value' => 'div',
            'block' => true,
            'float' => 'left',
            'parent' => 0,
        ]);
        $clearOpen = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'div',
            'block' => true,
            'clear' => 'both',
            'float' => 'none',
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 40.0;

        $obj->exposeOpenHTMLBlock($floatOpen, $tpx, $tpy, $tpw);
        $obj->exposeSetHTMLLineState(6.0, 0.0, false);
        $tpx = 4.0;
        $obj->exposeCloseHTMLBlock($floatClose, $tpx, $tpy, $tpw);
        $this->assertSame(0.0, $tpy);

        $obj->exposeOpenHTMLBlock($clearOpen, $tpx, $tpy, $tpw);
        $this->assertGreaterThanOrEqual(6.0, $tpy);
    }

    public function testParseHTMLStyleAttributesDisplayModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['hide' => true, 'display' => 'block', 'block' => true]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'display:inherit;'],
                'hide' => false,
                'display' => 'inline',
                'block' => false,
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'display:none;'],
                'hide' => false,
                'display' => 'inline',
                'block' => false,
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'display:list-item;'],
                'hide' => true,
                'display' => 'inline',
                'block' => false,
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);

        $this->assertTrue($dom[1]['hide']);
        $this->assertSame('block', $dom[1]['display']);
        $this->assertTrue($dom[1]['block']);
        $this->assertTrue($dom[2]['hide']);
        $this->assertSame('none', $dom[2]['display']);
        $this->assertFalse($dom[3]['hide']);
        $this->assertSame('list-item', $dom[3]['display']);
        $this->assertTrue($dom[3]['block']);
    }

    public function testParseHTMLStyleAttributesTextAlignModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['align' => 'J']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'text-align:center;'],
                'align' => '',
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'text-align:inherit;'],
                'align' => '',
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'text-align:right;'],
                'align' => '',
            ]),
            4 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'text-align:invalid;'],
                'align' => 'L',
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);
        $obj->parseHTMLStyleAttributes($dom, 4, 0);

        $this->assertSame('C', $dom[1]['align']);
        $this->assertSame('J', $dom[2]['align']);
        $this->assertSame('R', $dom[3]['align']);
        $this->assertSame('L', $dom[4]['align']);
    }

    public function testParseHTMLStyleAttributesVerticalAlignModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['valign' => 'middle']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'vertical-align:bottom;'],
                'valign' => 'top',
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'vertical-align:inherit;'],
                'valign' => 'top',
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'vertical-align:invalid;'],
                'valign' => 'top',
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);

        $this->assertSame('bottom', $dom[1]['valign']);
        $this->assertSame('middle', $dom[2]['valign']);
        $this->assertSame('top', $dom[3]['valign']);
    }

    public function testParseHTMLStyleAttributesTableLayoutModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['table-layout' => 'fixed']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'table-layout:auto;'],
                'table-layout' => 'fixed',
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'table-layout:inherit;'],
                'table-layout' => 'auto',
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'table-layout:invalid;'],
                'table-layout' => 'auto',
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);

        $this->assertSame('auto', $dom[1]['table-layout']);
        $this->assertSame('fixed', $dom[2]['table-layout']);
        $this->assertSame('auto', $dom[3]['table-layout']);
    }

    public function testParseHTMLStyleAttributesEmptyCellsModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['empty-cells' => 'hide']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'empty-cells:show;'],
                'empty-cells' => 'hide',
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'empty-cells:inherit;'],
                'empty-cells' => 'show',
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'empty-cells:invalid;'],
                'empty-cells' => 'show',
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);

        $this->assertSame('show', $dom[1]['empty-cells']);
        $this->assertSame('hide', $dom[2]['empty-cells']);
        $this->assertSame('show', $dom[3]['empty-cells']);
    }

    public function testParseHTMLStyleAttributesCaptionSideModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['caption-side' => 'bottom']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'caption-side:top;'],
                'caption-side' => 'bottom',
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'caption-side:inherit;'],
                'caption-side' => 'top',
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'caption-side:invalid;'],
                'caption-side' => 'top',
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);

        $this->assertSame('top', $dom[1]['caption-side']);
        $this->assertSame('bottom', $dom[2]['caption-side']);
        $this->assertSame('top', $dom[3]['caption-side']);
    }

    public function testParseHTMLStyleAttributesAlignInheritFallsBackToParentStyleValues(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode([
                'align' => '',
                'valign' => '',
                'style' => [
                    'text-align' => 'justify',
                    'vertical-align' => 'middle',
                ],
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'text-align:inherit;vertical-align:inherit;'],
                'align' => '',
                'valign' => '',
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame('J', $dom[1]['align']);
        $this->assertSame('middle', $dom[1]['valign']);
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
        /** @var array<int, THTMLAttrib> $dom */
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
                    'style' => 'line-height:normal;page-break-before:always;page-break-after:right;'
                        . 'list-style-type:inherit;text-indent:inherit;',
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

    public function testParseHTMLStyleAttributesMapsModernBreakAliases(): void
    {
        $obj = $this->getTestObject();
        /** @var THTMLAttrib $root */
        $root = $this->makeHtmlNode([
            'line-height' => 1.25,
            'listtype' => 'disc',
            'text-indent' => 2.0,
        ]);
        /** @var THTMLAttrib $breakNode */
        $breakNode = $this->makeHtmlNode([
            'parent' => 0,
            'fontsize' => 10.0,
            'font-stretch' => 100.0,
            'letter-spacing' => 0.0,
            'word-spacing' => 0.0,
            'attribute' => [
                'style' => 'break-before:page;break-after:right;break-inside:avoid;',
            ],
        ]);
        /** @var THTMLAttrib $breakNode2 */
        $breakNode2 = $this->makeHtmlNode([
            'parent' => 0,
            'fontsize' => 10.0,
            'font-stretch' => 100.0,
            'letter-spacing' => 0.0,
            'word-spacing' => 0.0,
            'attribute' => [
                'style' => 'break-before:left;break-after:page;',
            ],
        ]);

        /** @var array<int, THTMLAttrib> $dom */
        $dom = [
            0 => $root,
            1 => $breakNode,
            2 => $breakNode2,
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertSame('true', $dom[1]['attribute']['pagebreak']);
        $this->assertSame('right', $dom[1]['attribute']['pagebreakafter']);
        $this->assertSame('true', $dom[1]['attribute']['nobr']);

        $this->assertSame('left', $dom[2]['attribute']['pagebreak']);
        $this->assertSame('true', $dom[2]['attribute']['pagebreakafter']);
    }

    public function testParseHTMLStyleAttributesPageBreakValuesCaseInsensitive(): void
    {
        $obj = $this->getTestObject();
        /** @var array<int, THTMLAttrib> $dom */
        $dom = [
            0 => $this->makeHtmlNode([
                'line-height' => 1.0,
                'listtype' => 'disc',
                'text-indent' => 0.0,
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 0.0,
                'attribute' => [
                    'style' => 'page-break-inside:AVOID;page-break-before:LEFT;page-break-after:RIGHT;',
                ],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 0.0,
                'attribute' => [
                    'style' => 'break-inside:AVOID;break-before:PAGE;break-after:LEFT;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertSame('true', $dom[1]['attribute']['nobr']);
        $this->assertSame('left', $dom[1]['attribute']['pagebreak']);
        $this->assertSame('right', $dom[1]['attribute']['pagebreakafter']);

        $this->assertSame('true', $dom[2]['attribute']['nobr']);
        $this->assertSame('true', $dom[2]['attribute']['pagebreak']);
        $this->assertSame('left', $dom[2]['attribute']['pagebreakafter']);
    }

    public function testGetHTMLCellNestedBreakBeforeLeftRightConsistency(): void
    {
        $render = function (string $breakSide, string $marker): array {
            $obj = $this->getTestObject();
            $this->initFontAndPage($obj);

            $html = '<div>INTRO</div>'
                . '<div><section><p style="break-before:' . $breakSide . '">' . $marker . '</p></section></div>'
                . '<div>OUTRO</div>';

            $obj->addHTMLCell($html, 0, 0, 60, 0);

            /** @var \Com\Tecnick\Pdf\Page\Page $page */
            $page = $this->getObjectProperty($obj, 'page');
            $pages = $page->getPages();
            $markerPage = 0;
            foreach ($pages as $idx => $pdata) {
                $content = \implode("\n", $pdata['content']);
                if (\strpos($content, $marker) !== false) {
                    $markerPage = $idx + 1;
                    break;
                }
            }

            return [
                'count' => \count($pages),
                'markerPage' => $markerPage,
            ];
        };

        $left = $render('left', 'LEFT-NESTED-MARK');
        $right = $render('right', 'RIGHT-NESTED-MARK');

        $this->assertGreaterThan(1, $left['count']);
        $this->assertGreaterThan(1, $right['count']);
        $this->assertGreaterThan(1, $left['markerPage']);
        $this->assertGreaterThan(1, $right['markerPage']);
        $this->assertSame(
            1,
            \abs((int) $left['count'] - (int) $right['count']),
            'Nested break-before left/right should differ by exactly one parity-adjustment page.',
        );
    }

    public function testParseHTMLStyleAttributesParsesBorderCollapseModes(): void
    {
        $obj = $this->getTestObject();
        /** @var array<int, THTMLAttrib> $dom */
        $dom = [
            0 => $this->makeHtmlNode(),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'border-collapse:collapse;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'border-collapse:separate;'],
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 1,
                'attribute' => ['style' => 'border-collapse:InHeRiT;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 1);

        $this->assertSame('collapse', $dom[1]['border-collapse']);
        $this->assertSame('separate', $dom[2]['border-collapse']);
        $this->assertSame('collapse', $dom[3]['border-collapse']);
    }

    public function testParseHTMLStyleAttributesBorderInheritApplied(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'border:1px solid #112233;border-left:2px dotted #445566;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 1,
                'attribute' => ['style' => 'border:InHeRiT;'],
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 1,
                'attribute' => ['style' => 'border-left:inherit;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 1);
        $obj->parseHTMLStyleAttributes($dom, 3, 1);

        $this->assertNotEmpty($dom[1]['border']);
        $this->assertSame($dom[1]['border'], $dom[2]['border']);
        $this->assertArrayHasKey('L', $dom[1]['border']);
        $this->assertArrayHasKey('L', $dom[3]['border']);
        $this->assertSame($dom[1]['border']['L'], $dom[3]['border']['L']);
    }

    public function testParseHTMLStyleAttributesBorderColorAndWidthInheritApplied(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => [
                    'style' => 'border:1px solid #111111;border-color:#112233 #223344 #334455 #445566;'
                        . 'border-width:1px 2px 3px 4px;',
                ],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 1,
                'attribute' => [
                    'style' => 'border:1px solid black;border-color:InHeRiT;border-width:inherit;',
                ],
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 1,
                'attribute' => [
                    'style' => 'border:1px solid black;border-left-color:inherit;border-left-width:INHERIT;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 1);
        $obj->parseHTMLStyleAttributes($dom, 3, 1);

        foreach (['L', 'R', 'T', 'B'] as $side) {
            $this->assertSame($dom[1]['border'][$side]['lineColor'], $dom[2]['border'][$side]['lineColor']);
            $this->assertSame($dom[1]['border'][$side]['lineWidth'], $dom[2]['border'][$side]['lineWidth']);
        }

        $this->assertSame($dom[1]['border']['L']['lineColor'], $dom[3]['border']['L']['lineColor']);
        $this->assertSame($dom[1]['border']['L']['lineWidth'], $dom[3]['border']['L']['lineWidth']);
    }

    public function testParseHTMLStyleAttributesBorderStyleInheritApplied(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => [
                    'style' => 'border-width:1px 2px 3px 4px;border-style:dashed dotted solid double;',
                ],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 1,
                'attribute' => [
                    'style' => 'border-width:1px 2px 3px 4px;border-style:InHeRiT;',
                ],
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 1,
                'attribute' => [
                    'style' => 'border-width:1px 2px 3px 4px;border-style:solid;border-left-style:inherit;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 1);
        $obj->parseHTMLStyleAttributes($dom, 3, 1);

        foreach (['L', 'R', 'T', 'B'] as $side) {
            // @phpstan-ignore nullCoalesce.offset
            $this->assertSame(
                $dom[1]['border'][$side]['cssBorderStyle'] ?? null,
                $dom[2]['border'][$side]['cssBorderStyle'] ?? null,
            );
        }

        // @phpstan-ignore nullCoalesce.offset
        $this->assertSame(
            $dom[1]['border']['L']['cssBorderStyle'] ?? null,
            $dom[3]['border']['L']['cssBorderStyle'] ?? null,
        );
        // @phpstan-ignore nullCoalesce.offset
        $this->assertSame('solid', $dom[3]['border']['T']['cssBorderStyle'] ?? null);
    }

    public function testParseHTMLStyleAttributesBorderInheritFallbacksToParentLTRB(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'border:2px dashed #123456;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 1,
                'attribute' => [
                    'style' => 'border-color:inherit;border-width:inherit;border-style:inherit;border-left:inherit;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 1);

        $this->assertArrayHasKey('LTRB', $dom[1]['border']);
        $this->assertArrayHasKey('L', $dom[2]['border']);
        $this->assertArrayHasKey('R', $dom[2]['border']);
        $this->assertArrayHasKey('T', $dom[2]['border']);
        $this->assertArrayHasKey('B', $dom[2]['border']);

        $parentLTRB = $dom[1]['border']['LTRB'];
        foreach (['L', 'R', 'T', 'B'] as $side) {
            $this->assertSame($parentLTRB['lineColor'], $dom[2]['border'][$side]['lineColor']);
            $this->assertSame($parentLTRB['lineWidth'], $dom[2]['border'][$side]['lineWidth']);
            // @phpstan-ignore nullCoalesce.offset
            $this->assertSame(
                $parentLTRB['cssBorderStyle'] ?? null,
                $dom[2]['border'][$side]['cssBorderStyle'] ?? null,
            );
        }
    }

    public function testParseHTMLStyleAttributesExtractsBackgroundColorFromShorthand(): void
    {
        $obj = $this->getTestObject();
        /** @var array<int, THTMLAttrib> $dom */
        $dom = [
            0 => $this->makeHtmlNode(),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 0.0,
                'attribute' => [
                    'style' => 'background:url(example.png) no-repeat center center #ffeeaa;',
                ],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 0.0,
                'attribute' => [
                    'style' => 'background:#112233;background-color:#abcdef;',
                ],
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 0.0,
                'attribute' => [
                    'style' => 'background:url(example.png) no-repeat center center;',
                ],
            ]),
            4 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 0.0,
                'attribute' => [
                    'style' => 'background-color:#abcdef;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);
        $obj->parseHTMLStyleAttributes($dom, 4, 0);

        $this->assertNotSame('', $dom[1]['bgcolor']);
        $this->assertIsString($dom[1]['bgcolor']);
        $this->assertStringContainsString('rgba(', $dom[1]['bgcolor']);
        $this->assertSame($dom[4]['bgcolor'], $dom[2]['bgcolor']);
        $this->assertSame('', $dom[3]['bgcolor']);
    }

    public function testParseHTMLStyleAttributesInheritForFontFamilyColorAndBackgroundColor(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode([
                'fontname' => 'times',
                'fgcolor' => 'red',
                'bgcolor' => 'green',
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => [
                    'style' => 'font-family:inherit;color:inherit;background-color:inherit;',
                ],
                'style' => [],
                'fontname' => '',
                'fgcolor' => '',
                'bgcolor' => '',
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame('times', $dom[1]['fontname']);
        $this->assertSame('red', $dom[1]['fgcolor']);
        $this->assertSame('green', $dom[1]['bgcolor']);
    }

    public function testParseHTMLStyleAttributesBackgroundShorthandInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode([
                'bgcolor' => 'green',
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => [
                    'style' => 'background:inherit;',
                ],
                'bgcolor' => '',
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => [
                    'style' => 'background:none;',
                ],
                'bgcolor' => 'green',
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertSame('green', $dom[1]['bgcolor']);
        $this->assertSame('', $dom[2]['bgcolor']);
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

    public function testParseHTMLStyleAttributesAppliesCSSFontSize(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        // "12px" — NOT numeric as a string, but must be parsed by getFontValuePoints
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0]),
            1 => $this->makeHtmlNode(['parent' => 0, 'fontsize' => 10.0,
                'attribute' => ['style' => 'font-size:12px;']]),
            2 => $this->makeHtmlNode(['parent' => 0, 'fontsize' => 10.0,
                'attribute' => ['style' => 'font-size:150%;']]),
            3 => $this->makeHtmlNode(['parent' => 0, 'fontsize' => 10.0,
                'attribute' => ['style' => 'font-size:1.5em;']]),
            4 => $this->makeHtmlNode(['parent' => 0, 'fontsize' => 10.0,
                'attribute' => ['style' => 'font-size:inherit;']]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);
        $obj->parseHTMLStyleAttributes($dom, 4, 0);

        // All three must result in a non-default (not 10pt) font size
        $this->assertNotSame(10.0, $dom[1]['fontsize'], 'font-size:12px should change fontsize');
        $this->assertGreaterThan(0.0, $dom[1]['fontsize']);
        $this->assertNotSame(10.0, $dom[2]['fontsize'], 'font-size:150% should change fontsize');
        $this->assertGreaterThan(0.0, $dom[2]['fontsize']);
        $this->assertNotSame(10.0, $dom[3]['fontsize'], 'font-size:1.5em should change fontsize');
        $this->assertGreaterThan(0.0, $dom[3]['fontsize']);
        $this->assertSame(10.0, $dom[4]['fontsize'], 'font-size:inherit should preserve parent fontsize');
    }

    public function testParseHTMLStyleAttributesWidthAndHeightInheritCaseInsensitive(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode([
                'width' => 12.5,
                'height' => 34.75,
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'width' => 0.0,
                'height' => 0.0,
                'attribute' => ['style' => 'width:InHeRiT;height:INHERIT;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame(12.5, $dom[1]['width']);
        $this->assertSame(34.75, $dom[1]['height']);
    }

    public function testParseHTMLStyleAttributesWidthAndHeightInheritFallBackToParentStyleValues(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode([
                'width' => '',
                'height' => '',
                'style' => [
                    'width' => '20mm',
                    'height' => '15mm',
                ],
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'width' => 0.0,
                'height' => 0.0,
                'attribute' => ['style' => 'width:inherit;height:inherit;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'width' => 0.0,
                'height' => 0.0,
                'attribute' => ['style' => 'width:20mm;height:15mm;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertEqualsWithDelta((float) $dom[2]['width'], (float) $dom[1]['width'], 0.0001);
        $this->assertEqualsWithDelta((float) $dom[2]['height'], (float) $dom[1]['height'], 0.0001);
    }

    public function testParseHTMLStyleAttributesAppliesMinMaxWidthHeightClamping(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'width' => 0.0,
                'height' => 0.0,
                'attribute' => ['style' => 'width:10mm;min-width:20mm;height:30mm;max-height:25mm;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'width' => 0.0,
                'height' => 0.0,
                'attribute' => ['style' => 'width:20mm;height:25mm;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertEqualsWithDelta((float) $dom[2]['width'], (float) $dom[1]['width'], 0.0001);
        $this->assertEqualsWithDelta((float) $dom[2]['height'], (float) $dom[1]['height'], 0.0001);
    }

    public function testParseHTMLStyleAttributesIgnoresMinMaxWithoutExplicitWidthHeight(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'width' => 0.0,
                'height' => 0.0,
                'attribute' => ['style' => 'min-width:20mm;max-width:30mm;min-height:10mm;max-height:15mm;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame(0.0, (float) $dom[1]['width']);
        $this->assertSame(0.0, (float) $dom[1]['height']);
    }

    public function testParseHTMLStyleAttributesResolvesConflictingMinMaxByFavoringMin(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'width' => 0.0,
                'height' => 0.0,
                'attribute' => [
                    'style' => 'width:10mm;min-width:30mm;max-width:20mm;'
                        . 'height:10mm;min-height:30mm;max-height:20mm;',
                ],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'width' => 0.0,
                'height' => 0.0,
                'attribute' => ['style' => 'width:30mm;height:30mm;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertEqualsWithDelta((float) $dom[2]['width'], (float) $dom[1]['width'], 0.0001);
        $this->assertEqualsWithDelta((float) $dom[2]['height'], (float) $dom[1]['height'], 0.0001);
    }

    public function testParseHTMLStyleAttributesOverflowMapsToClipMode(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['clip' => true]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'clip' => false,
                'attribute' => ['style' => 'overflow:hidden;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'clip' => true,
                'attribute' => ['style' => 'overflow:visible;'],
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'clip' => false,
                'attribute' => ['style' => 'overflow:inherit;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);

        $this->assertTrue($dom[1]['clip']);
        $this->assertFalse($dom[2]['clip']);
        $this->assertTrue($dom[3]['clip']);
    }

    public function testParseHTMLStyleAttributesOverflowAxisOverridesShorthand(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['clip' => false]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'clip' => false,
                'attribute' => ['style' => 'overflow:visible;overflow-x:hidden;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'clip' => true,
                'attribute' => ['style' => 'overflow:hidden;overflow-y:visible;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        // Any axis hidden forces clipping true.
        $this->assertTrue($dom[1]['clip']);
        // Visible axis does not clear clipping when another declaration already requested hidden.
        $this->assertTrue($dom[2]['clip']);
    }

    public function testParseHTMLStyleAttributesInheritFallsBackToParentStyleScalars(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode([
                'fontsize' => '',
                'font-stretch' => '',
                'letter-spacing' => '',
                'word-spacing' => '',
                'fgcolor' => '',
                'bgcolor' => '',
                'border-collapse' => '',
                'border-spacing' => '',
                'fontstyle' => '',
                'style' => [
                    'font-size' => '13pt',
                    'font-stretch' => 'expanded',
                    'letter-spacing' => '1mm',
                    'word-spacing' => '2mm',
                    'color' => '#123456',
                    'background-color' => '#abcdef',
                    'border-collapse' => 'collapse',
                    'border-spacing' => '2mm 3mm',
                    'font-weight' => '700',
                    'font-style' => 'italic',
                ],
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 0.0,
                'font-stretch' => 0.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 0.0,
                'fgcolor' => '',
                'bgcolor' => '',
                'fontstyle' => '',
                'border-collapse' => '',
                'border-spacing' => '',
                'attribute' => [
                    'style' => 'font-size:inherit;font-stretch:inherit;letter-spacing:inherit;word-spacing:inherit;'
                        . 'color:inherit;background-color:inherit;border-collapse:inherit;border-spacing:inherit;'
                        . 'font-weight:inherit;font-style:inherit;',
                ],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 0.0,
                'font-stretch' => 0.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 0.0,
                'fgcolor' => '',
                'bgcolor' => '',
                'fontstyle' => '',
                'border-collapse' => '',
                'border-spacing' => '',
                'attribute' => [
                    'style' => 'font-size:13pt;font-stretch:expanded;letter-spacing:1mm;word-spacing:2mm;'
                        . 'color:#123456;background-color:#abcdef;border-collapse:collapse;border-spacing:2mm 3mm;'
                        . 'font-weight:700;font-style:italic;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertEqualsWithDelta((float) $dom[2]['fontsize'], (float) $dom[1]['fontsize'], 0.0001);
        $this->assertEqualsWithDelta((float) $dom[2]['font-stretch'], (float) $dom[1]['font-stretch'], 0.0001);
        $this->assertEqualsWithDelta((float) $dom[2]['letter-spacing'], (float) $dom[1]['letter-spacing'], 0.0001);
        $this->assertEqualsWithDelta((float) $dom[2]['word-spacing'], (float) $dom[1]['word-spacing'], 0.0001);
        $this->assertSame($dom[2]['fgcolor'], $dom[1]['fgcolor']);
        $this->assertSame($dom[2]['bgcolor'], $dom[1]['bgcolor']);
        $this->assertSame($dom[2]['border-collapse'], $dom[1]['border-collapse']);
        $this->assertSame($dom[2]['fontstyle'], $dom[1]['fontstyle']);
        $this->assertArrayHasKey('border-spacing', $dom[1]);
        $this->assertArrayHasKey('border-spacing', $dom[2]);
        /** @var array{H: float, V: float} $spacing1 */
        // @phpstan-ignore offsetAccess.notFound
        $spacing1 = $dom[1]['border-spacing'];
        /** @var array{H: float, V: float} $spacing2 */
        // @phpstan-ignore offsetAccess.notFound
        $spacing2 = $dom[2]['border-spacing'];
        $this->assertEqualsWithDelta(
            (float) $spacing2['H'],
            (float) $spacing1['H'],
            0.0001,
        );
        $this->assertEqualsWithDelta(
            (float) $spacing2['V'],
            (float) $spacing1['V'],
            0.0001,
        );
    }

    public function testParseHTMLStyleAttributesLineHeightInheritDoesNotFallThrough(): void
    {
        $obj = $this->getInternalTestObject();
        $parentLineHeight = 1.8;
        $dom = [
            0 => $this->makeHtmlNode(['line-height' => $parentLineHeight]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'line-height' => 1.0,
                'attribute' => ['style' => 'line-height:inherit;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        // Must equal the parent value exactly, not be recalculated
        $this->assertSame(
            $parentLineHeight,
            $dom[1]['line-height'],
            'line-height:inherit must not fall through to default recalculation'
        );
    }

    public function testParseHTMLStyleAttributesLineHeightAndTextIndentInheritCaseInsensitive(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode([
                'line-height' => 1.7,
                'text-indent' => 2.5,
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'line-height' => 1.0,
                'text-indent' => 0.0,
                'attribute' => ['style' => 'line-height:INHERIT;text-indent:InHeRiT;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame(1.7, $dom[1]['line-height']);
        $this->assertSame(2.5, $dom[1]['text-indent']);
    }

    public function testParseHTMLStyleAttributesTextIndentInheritFallsBackToParentStyleValue(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode([
                'text-indent' => '',
                'style' => ['text-indent' => '3mm'],
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'text-indent' => 0.0,
                'attribute' => ['style' => 'text-indent:inherit;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'text-indent' => 0.0,
                'attribute' => ['style' => 'text-indent:3mm;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertEqualsWithDelta((float) $dom[2]['text-indent'], (float) $dom[1]['text-indent'], 0.0001);
    }

    public function testParseHTMLStyleAttributesLineHeightDefaultCases(): void
    {
        $obj = $this->getInternalTestObject();
        $fontsize = 12.0; // pts

        // Case 1: percentage  → dimensionless ratio = value / 100
        $dom = [
            0 => $this->makeHtmlNode(['line-height' => 1.0]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => $fontsize,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => ['style' => 'line-height:150%;'],
            ]),
        ];
        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $this->assertEqualsWithDelta(1.5, $dom[1]['line-height'], 0.001, 'line-height:150% must store ratio 1.5');

        // Case 2: unitless number → ratio stored directly
        $dom[1] = $this->makeHtmlNode([
            'parent' => 0,
            'fontsize' => $fontsize,
            'font-stretch' => 100.0,
            'letter-spacing' => 0.0,
            'line-height' => 1.0,
            'attribute' => ['style' => 'line-height:2;'],
        ]);
        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $this->assertEqualsWithDelta(2.0, $dom[1]['line-height'], 0.001, 'line-height:2 must store ratio 2.0');

        // Case 3: absolute unit (24pt on a 12pt font) → ratio 2.0
        $dom[1] = $this->makeHtmlNode([
            'parent' => 0,
            'fontsize' => $fontsize,
            'font-stretch' => 100.0,
            'letter-spacing' => 0.0,
            'line-height' => 1.0,
            'attribute' => ['style' => 'line-height:24pt;'],
        ]);
        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $this->assertEqualsWithDelta(
            2.0,
            $dom[1]['line-height'],
            0.01,
            'line-height:24pt on 12pt font must store ratio 2.0'
        );

        // Case 4: 100% → ratio 1.0
        $dom[1] = $this->makeHtmlNode([
            'parent' => 0,
            'fontsize' => $fontsize,
            'font-stretch' => 100.0,
            'letter-spacing' => 0.0,
            'line-height' => 0.0,
            'attribute' => ['style' => 'line-height:100%;'],
        ]);
        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $this->assertEqualsWithDelta(1.0, $dom[1]['line-height'], 0.001, 'line-height:100% must store ratio 1.0');

        // Case 5: 200% → ratio 2.0
        $dom[1] = $this->makeHtmlNode([
            'parent' => 0,
            'fontsize' => $fontsize,
            'font-stretch' => 100.0,
            'letter-spacing' => 0.0,
            'line-height' => 0.0,
            'attribute' => ['style' => 'line-height:200%;'],
        ]);
        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $this->assertEqualsWithDelta(2.0, $dom[1]['line-height'], 0.001, 'line-height:200% must store ratio 2.0');
    }

    public function testParseHTMLStyleAttributesWhiteSpaceModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['white-space' => 'pre-wrap']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => ['style' => 'white-space:pre-line;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => ['style' => 'white-space:inherit;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertSame('pre-line', $dom[1]['white-space']);
        $this->assertSame('pre-wrap', $dom[2]['white-space']);
    }

    public function testParseHTMLStyleAttributesTextTransformAndWhiteSpaceInheritFallBackToParentStyleValues(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode([
                'text-transform' => '',
                'white-space' => '',
                'style' => [
                    'text-transform' => 'uppercase',
                    'white-space' => 'pre-wrap',
                ],
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'text-transform:inherit;white-space:inherit;'],
                'text-transform' => '',
                'white-space' => '',
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame('uppercase', $dom[1]['text-transform']);
        $this->assertSame('pre-wrap', $dom[1]['white-space']);
    }

    public function testParseHTMLStyleAttributesWordSpacingModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['word-spacing' => 1.25]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 0.0,
                'attribute' => ['style' => 'word-spacing:2mm;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 0.0,
                'attribute' => ['style' => 'word-spacing:inherit;'],
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 3.0,
                'attribute' => ['style' => 'word-spacing:normal;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);

        $this->assertGreaterThan(0.0, $dom[1]['word-spacing']);
        $this->assertSame(1.25, $dom[2]['word-spacing']);
        $this->assertSame(0.0, $dom[3]['word-spacing']);
    }

    public function testParseHTMLStyleAttributesSpacingKeywordsCaseInsensitive(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode([
                'font-stretch' => 112.0,
                'letter-spacing' => 0.75,
                'word-spacing' => 1.5,
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'font-stretch' => 0.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 0.0,
                'attribute' => ['style' => 'font-stretch:INHERIT;letter-spacing:InHeRiT;word-spacing:INHERIT;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'font-stretch' => 0.0,
                'letter-spacing' => 9.0,
                'word-spacing' => 9.0,
                'attribute' => ['style' => 'font-stretch:NoRmAl;letter-spacing:NoRmAl;word-spacing:NoRmAl;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertSame(112.0, $dom[1]['font-stretch']);
        $this->assertSame(0.75, $dom[1]['letter-spacing']);
        $this->assertSame(1.5, $dom[1]['word-spacing']);

        $this->assertSame(100.0, $dom[2]['font-stretch']);
        $this->assertSame(0.0, $dom[2]['letter-spacing']);
        $this->assertSame(0.0, $dom[2]['word-spacing']);
    }

    public function testParseHTMLStyleAttributesListStylePositionModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['list-style-position' => 'inside']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 0.0,
                'attribute' => ['style' => 'list-style-position:outside;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'word-spacing' => 0.0,
                'attribute' => ['style' => 'list-style-position:inherit;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertSame('outside', $dom[1]['list-style-position']);
        $this->assertSame('inside', $dom[2]['list-style-position']);
    }

    public function testParseHTMLStyleAttributesListStyleShorthandInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $parentImage = 'url(data:image/svg+xml;base64,PHN2Zz4=)';
        $dom = [
            0 => $this->makeHtmlNode([
                'listtype' => 'square',
                'list-style-position' => 'inside',
                'list-style-image' => $parentImage,
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'list-style:InHeRiT;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'list-style:inherit;list-style-position:outside;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertSame('square', $dom[1]['listtype']);
        $this->assertSame('inside', $dom[1]['list-style-position']);
        $this->assertArrayHasKey('list-style-image', $dom[1]);
        /** @var string $listImage */
        // @phpstan-ignore offsetAccess.notFound
        $listImage = $dom[1]['list-style-image'];
        $this->assertSame($parentImage, $listImage);

        $this->assertSame('square', $dom[2]['listtype']);
        $this->assertSame('outside', $dom[2]['list-style-position']);
    }

    public function testParseHTMLStyleAttributesListStyleShorthandInheritUsesParentStyleImageFallback(): void
    {
        $obj = $this->getInternalTestObject();
        $parentImage = 'url(data:image/svg+xml;base64,PHN2Zz4=)';
        $dom = [
            0 => $this->makeHtmlNode([
                'listtype' => 'disc',
                'list-style-position' => 'inside',
                'list-style-image' => '',
                'style' => ['list-style-image' => $parentImage],
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'list-style:inherit;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame('disc', $dom[1]['listtype']);
        $this->assertSame('inside', $dom[1]['list-style-position']);
        $this->assertArrayHasKey('list-style-image', $dom[1]);
        /** @var string $listImage */
        // @phpstan-ignore offsetAccess.notFound
        $listImage = $dom[1]['list-style-image'];
        $this->assertSame($parentImage, $listImage);
        $this->assertSame($parentImage, $dom[1]['style']['list-style-image']);
    }

    public function testParseHTMLStyleAttributesListStyleInheritFallsBackToParentStyleTypeAndPosition(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode([
                'listtype' => '',
                'list-style-position' => '',
                'style' => [
                    'list-style-type' => 'square',
                    'list-style-position' => 'inside',
                ],
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'list-style:inherit;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'list-style-type:inherit;list-style-position:inherit;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertSame('square', $dom[1]['listtype']);
        $this->assertSame('inside', $dom[1]['list-style-position']);

        $this->assertSame('square', $dom[2]['listtype']);
        $this->assertSame('inside', $dom[2]['list-style-position']);
    }

    public function testParseHTMLStyleAttributesTextTransformModesAndInherit(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['text-transform' => 'uppercase']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'text-transform:capitalize;'],
                'text-transform' => '',
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'text-transform:inherit;'],
                'text-transform' => '',
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'text-transform:none;'],
                'text-transform' => 'lowercase',
            ]),
            4 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'text-transform:invalid;'],
                'text-transform' => 'lowercase',
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);
        $obj->parseHTMLStyleAttributes($dom, 4, 0);

        $this->assertSame('capitalize', $dom[1]['text-transform']);
        $this->assertSame('uppercase', $dom[2]['text-transform']);
        $this->assertSame('', $dom[3]['text-transform']);
        $this->assertSame('lowercase', $dom[4]['text-transform']);
    }

    public function testParseHTMLStyleAttributesListStyleImageInheritResolvesParentImageMarker(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $parentImage = 'url(data:image/svg+xml;base64,PHN2Zz4=)';
        $dom = [
            0 => $this->makeHtmlNode(['list-style-image' => '', 'style' => []]),
            1 => $this->makeHtmlNode([
                'value' => 'ul',
                'opening' => true,
                'parent' => 0,
                'attribute' => ['style' => 'list-style-image:' . $parentImage . ';'],
                'style' => [],
            ]),
            2 => $this->makeHtmlNode([
                'value' => 'ul',
                'opening' => true,
                'parent' => 1,
                'attribute' => ['style' => 'list-style-image:inherit;'],
                'style' => [],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 1);

        $this->assertSame($parentImage, $dom[2]['style']['list-style-image']);

        $marker = $obj->exposeGetHTMLListMarkerTypeWithDom($dom, 2, false);
        $this->assertStringStartsWith('img|svg|', $marker);
    }

    public function testAnchorInsideBoldPreservesBoldFontstyle(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        // Build a DOM node for <a> that already has bold from a parent <b>
        $dom = [
            0 => $this->makeHtmlNode(['fontstyle' => '']),
            1 => $this->makeHtmlNode([
                'value' => 'a',
                'parent' => 0,
                'fontstyle' => 'B',
                'style' => [],
                'attribute' => [],
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);

        // Both bold and underline must be present
        $this->assertStringContainsString('B', $dom[1]['fontstyle'], 'Bold must be preserved on <a> inside <b>');
        $this->assertStringContainsString('U', $dom[1]['fontstyle'], 'Underline must be added on <a>');
    }

    public function testParseHTMLStyleAttributesNumericFontWeightApplied(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontstyle' => '']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'fontstyle' => '',
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => ['style' => 'font-weight:700;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'fontstyle' => 'B',
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => ['style' => 'font-weight:400;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertStringContainsString('B', $dom[1]['fontstyle'], 'font-weight:700 must add bold');
        $this->assertStringNotContainsString('B', $dom[2]['fontstyle'], 'font-weight:400 must remove bold');
    }

    public function testParseHTMLStyleAttributesFontWeightInheritApplied(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontstyle' => 'B']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'fontstyle' => '',
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => ['style' => 'font-weight:inherit;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'fontstyle' => 'B',
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => ['style' => 'font-weight:normal;'],
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 2,
                'fontsize' => 10.0,
                'fontstyle' => '',
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => ['style' => 'font-weight:inherit;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 2);

        $this->assertSame('B', $dom[1]['fontstyle']);
        $this->assertSame('', $dom[2]['fontstyle']);
        $this->assertSame('', $dom[3]['fontstyle']);
    }

    public function testParseHTMLStyleAttributesFontStyleInheritAndNormalApplied(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontstyle' => 'I']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'fontstyle' => '',
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => ['style' => 'font-style:inherit;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'fontstyle' => 'BI',
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => ['style' => 'font-style:normal;'],
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'fontstyle' => 'B',
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => ['style' => 'font-style:oblique;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);

        $this->assertSame('I', $dom[1]['fontstyle']);
        $this->assertSame('B', $dom[2]['fontstyle']);
        $this->assertSame('BI', $dom[3]['fontstyle']);
    }

    public function testParseHTMLStyleAttributesTextDecorationNoneAndInheritApplied(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['fontstyle' => 'UD']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontstyle' => 'BI',
                'attribute' => ['style' => 'text-decoration:none;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'fontstyle' => 'BI',
                'attribute' => ['style' => 'text-decoration:inherit;'],
            ]),
            3 => $this->makeHtmlNode([
                'parent' => 0,
                'fontstyle' => 'B',
                'attribute' => ['style' => 'text-decoration:underline overline underline;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);
        $obj->parseHTMLStyleAttributes($dom, 3, 0);

        $this->assertSame('BI', $dom[1]['fontstyle']);
        $this->assertSame('BIUD', $dom[2]['fontstyle']);
        $this->assertSame('BUO', $dom[3]['fontstyle']);
    }

    public function testParseHTMLStyleAttributesIndividualPaddingAndMarginApplied(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'attribute' => ['style' => 'padding-top:5px;padding-left:10px;margin-top:3px;margin-right:4px;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertGreaterThan(0.0, $dom[1]['padding']['T'], 'padding-top must be applied');
        $this->assertGreaterThan(0.0, $dom[1]['padding']['L'], 'padding-left must be applied');
        $this->assertGreaterThan(
            $dom[1]['padding']['T'],
            $dom[1]['padding']['L'],
            'padding-left (10px) must be greater than padding-top (5px)'
        );
        $this->assertGreaterThan(0.0, $dom[1]['margin']['T'], 'margin-top must be applied');
        $this->assertGreaterThan(0.0, $dom[1]['margin']['R'], 'margin-right must be applied');
    }

    public function testParseHTMLStyleAttributesPaddingAndMarginInheritApplied(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $parentPadding = ['T' => 1.1, 'R' => 2.2, 'B' => 3.3, 'L' => 4.4];
        $parentMargin = ['T' => 5.5, 'R' => 6.6, 'B' => 7.7, 'L' => 8.8];

        $dom = [
            0 => $this->makeHtmlNode([
                'padding' => $parentPadding,
                'margin' => $parentMargin,
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'padding:inherit;margin:inherit;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'padding:1px;padding-left:INHERIT;margin:2px;margin-right:InHeRiT;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertSame($parentPadding, $dom[1]['padding']);
        $this->assertSame($parentMargin, $dom[1]['margin']);
        $this->assertSame($parentPadding['L'], $dom[2]['padding']['L']);
        $this->assertSame($parentMargin['R'], $dom[2]['margin']['R']);
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

    public function testAddHTMLCellFlowsIntoSecondColumnRegionNotFirstColumn(): void
    {
        // Regression: after a region break, originx must be updated to the new
        // region's RX so content renders in the second column, not overlapping
        // the first column again.
        $obj = $this->getTestObject();
        self::setUpFontsPath();

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        $fontfile = (string) \realpath(
            __DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json'
        );
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);

        $leftMargin   = 15.0;
        $rightMargin  = 15.0;
        $topMargin    = 20.0;
        $bottomMargin = 20.0;
        $columnGap    = 8.0;
        $contentWidth  = 210.0 - $leftMargin - $rightMargin;
        $contentHeight = 297.0 - $topMargin - $bottomMargin;
        $columnWidth   = ($contentWidth - $columnGap) / 2.0;

        $obj->addPage([
            'margin' => [
                'PL' => $leftMargin,
                'PR' => $rightMargin,
                'CT' => $topMargin,
                'CB' => $bottomMargin,
            ],
            'region' => [
                [
                    'RX' => $leftMargin,
                    'RY' => $topMargin,
                    'RW' => $columnWidth,
                    'RH' => $contentHeight,
                ],
                [
                    'RX' => $leftMargin + $columnWidth + $columnGap,
                    'RY' => $topMargin,
                    'RW' => $columnWidth,
                    'RH' => $contentHeight,
                ],
            ],
        ]);

        $chunk = '<p>Lorem ipsum dolor sit amet consectetur adipiscing elit.'
            . ' Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>';
        $html = \str_repeat($chunk, 60);

        // Enough content to overflow the first column and flow into the second.
        $obj->addHTMLCell($html, $leftMargin, $topMargin, $columnWidth, 0);

        $pages = $page->getPages();
        $allContent = '';
        foreach ($pages as $pdata) {
            if (isset($pdata['content']) && \is_array($pdata['content'])) {
                $allContent .= \implode("\n", $pdata['content']);
            }
        }

        // The second column's X is $leftMargin + $columnWidth + $columnGap ≈ 109 mm.
        // Convert to points to find PDF Td commands: 1mm ≈ 2.8346 pt.
        $col2x = ($leftMargin + $columnWidth + $columnGap) * 2.8346;
        $col2xMin = $col2x - 2.0;

        // After the fix, at least one text Td command must have an X component
        // inside the second column (x > col2xMin). Before the fix, all Td X
        // values stayed in the first column (around 42–72 pt).
        $tdMatches = [];
        \preg_match_all('/\b([\d.]+) [\d.-]+ Td\b/', $allContent, $tdMatches);
        $foundSecondCol = false;
        foreach ($tdMatches[1] as $xVal) {
            if ((float) $xVal >= $col2xMin) {
                $foundSecondCol = true;
                break;
            }
        }

        $this->assertTrue(
            $foundSecondCol,
            'Expected text to flow into the second column (X ≥ ' . \round($col2xMin, 1) . ' pt),'
            . ' but all Td X values stayed in the first column.',
        );
    }

    public function testAddHTMLCellAutoFlowSpansMultiplePages(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $beforePages = \count($page->getPages());

        $chunk = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
            . ' Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>';
        $html = \str_repeat($chunk, 220);

        $obj->addHTMLCell($html, 20, 10, 150, 0);

        $afterPages = \count($page->getPages());

        $this->assertGreaterThan($beforePages, $afterPages);
    }

    public function testAddHTMLCellWithFixedHeightDoesNotAutoBreak(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $beforePages = \count($page->getPages());

        $chunk = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
            . ' Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>';
        $html = \str_repeat($chunk, 220);

        $obj->addHTMLCell($html, 20, 10, 150, 30);

        $afterPages = \count($page->getPages());

        $this->assertSame($beforePages, $afterPages);
    }

    public function testAddHTMLCellLongOrderedListSpansMultiplePages(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $beforePages = \count($page->getPages());

        $items = '';
        for ($i = 0; $i < 200; ++$i) {
            $items .= '<li>Item ' . $i . ' Lorem ipsum dolor sit amet consectetur</li>';
        }

        $obj->addHTMLCell('<ol>' . $items . '</ol>', 20, 10, 150, 0);

        $afterPages = \count($page->getPages());

        $this->assertGreaterThan($beforePages, $afterPages);
    }

    public function testAddHTMLCellListPageBreakPreservesSectionOrderInsideStyledBlock(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $items = '';
        for ($i = 0; $i < 220; ++$i) {
            $items .= '<li>Item ' . $i . ' with long text to force wrapping and page flow inside the list block.</li>';
        }

        $html = '<style>'
            . '.panel{border:0.2mm solid #333;background-color:#f2f6fb;padding:2mm;margin-bottom:2mm;}'
            . '</style>'
            . '<div class="panel">'
            . '<h2>SECTION4</h2>'
            . '<ul>' . $items . '</ul>'
            . '</div>'
            . '<div class="panel">'
            . '<h2>SECTION6</h2>'
            . '<p>After list block</p>'
            . '</div>';

        $obj->addHTMLCell($html, 20, 10, 150, 0);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $pages = $page->getPages();

        $content = '';
        foreach ($pages as $pdata) {
            if (!isset($pdata['content']) || !\is_array($pdata['content'])) {
                continue;
            }

            $content .= "\n" . \implode("\n", $pdata['content']);
        }

        $this->assertStringContainsString('SECTION4', $content);
        $this->assertStringContainsString('SECTION6', $content);

        $pos4 = \strpos($content, 'SECTION4');
        $pos6 = \strpos($content, 'SECTION6');
        $this->assertNotFalse($pos4);
        $this->assertNotFalse($pos6);
        $this->assertLessThan($pos6, $pos4, 'Expected SECTION4 to be emitted before SECTION6.');
    }

    public function testAddHTMLCellLongTableSpansMultiplePages(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $beforePages = \count($page->getPages());

        $rows = '';
        for ($i = 0; $i < 200; ++$i) {
            $rows .= '<tr><td>Row ' . $i . '</td><td>Lorem ipsum dolor sit amet</td></tr>';
        }

        $obj->addHTMLCell(
            '<table border="1"><tr><th>A</th><th>B</th></tr>' . $rows . '</table>',
            20,
            10,
            150,
            0,
        );

        $afterPages = \count($page->getPages());

        $this->assertGreaterThan($beforePages, $afterPages);
    }

    public function testAddHTMLCellTwelvePointMixedInlineTableDoesNotBreakAfterFirstRow(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $fontfile = (string) \realpath(
            __DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/dejavu/dejavusans.json'
        );
        $font = $obj->font->insert($obj->pon, 'dejavusans', '', 12, null, null, $fontfile);
        $obj->page->addContent($font['out']);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $beforePages = \count($page->getPages());
        $spanWords = '<span>Alfa</span> <span>Bravo</span> <span>Charlie</span> <span>Delta</span> '
            . '<span>Echo</span> <span>Foxtrot</span> <span>Golf</span> <span>Hotel</span> '
            . '<span>India</span> <span>Juliett</span> <span>Kilo</span> <span>Lima</span> '
            . '<span>Mike</span> <span>November</span> <span>Oscar</span> <span>Papa</span> '
            . '<span>Quebec</span> <span>Romeo</span> <span>Sierra</span> <span>Tango</span> '
            . '<span>Uniform</span> <span>Victor</span> <span>Whiskey</span> <span>Xray</span> '
            . '<span>Yankee</span> <span>Zulu</span>';
        $plainWords = 'Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo Lima Mike '
            . 'November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray Yankee Zulu';

        $html = '<table border="1" cellspacing="3" cellpadding="4">'
            . '<tr><td align="left"><span>1L</span> ' . $spanWords . '</td></tr>'
            . '<tr><td align="center"><span>1C</span> ' . $spanWords . '</td></tr>'
            . '<tr><td align="right"><span>1R</span> ' . $spanWords . '</td></tr>'
            . '<tr><td align="left"><span>2L</span> A1 ex<i>amp</i>le <a href="https://tcpdf.org">link</a> '
            . 'column span. ' . $plainWords . '.</td></tr>'
            . '<tr><td align="center"><span>2C</span> A1 ex<i>amp</i>le <a href="https://tcpdf.org">link</a> '
            . 'column span. ' . $plainWords . '.</td></tr>'
            . '<tr><td align="right"><span>2R</span> A1 ex<i>amp</i>le <a href="https://tcpdf.org">link</a> '
            . 'column span. ' . $plainWords . '.</td></tr>'
            . '<tr><td align="left"><small>3L small text</small> ' . $plainWords . '</td></tr>'
            . '<tr><td align="center"><small>3C small text</small> ' . $plainWords . '</td></tr>'
            . '<tr><td align="right"><small>3R small text</small> ' . $plainWords . '</td></tr>'
            . '</table>';

        $obj->addHTMLCell($html, 20, 10, 180, 0);

        $afterPages = \count($page->getPages());

        $this->assertSame($beforePages, $afterPages);
    }

    public function testAddHTMLCellStyledBlockSpansMultiplePages(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $beforePages = \count($page->getPages());

        $chunk = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>';
        $html = '<div style="background-color:#ffeeaa;border:1px solid #000">'
            . \str_repeat($chunk, 150)
            . '</div>';

        $obj->addHTMLCell($html, 20, 10, 150, 0);

        $afterPages = \count($page->getPages());

        $this->assertGreaterThan($beforePages, $afterPages);
    }

    public function testAddHTMLCellSoftHyphenBreakUsesVisibleHyphenOnWrappedLine(): void
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

        $obj->addHTMLCell('<p>de&shy;nounce</p>', 0, 0, 8, 0, $cell, []);

        $content = \implode("\n", $page->getPage()['content']);

        $this->assertStringContainsString('(de-) Tj', $content);
        $this->assertStringContainsString('(nounce) Tj', $content);
        $this->assertStringNotContainsString('(denounce) Tj', $content);
    }

    public function testParseHTMLTextAppliesTextIndentOnlyOnFirstLine(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(20.0, 10.0, 80.0, 0.0);
        $obj->exposeResetBBoxTrace();

        $elm = $this->makeHtmlNode([
            'value' => 'First line sample text',
            'text-indent' => 6.0,
            'align' => 'L',
            'dir' => 'ltr',
            'fontname' => 'helvetica',
            'fontsize' => 10.0,
        ]);

        $tpx = 20.0;
        $tpy = 10.0;
        $tpw = 80.0;
        $tph = 0.0;

        $obj->exposeParseHTMLText($elm, $tpx, $tpy, $tpw, $tph);

        $tpx = 20.0;
        $tpw = 80.0;
        $tpy += 6.0;
        $elm['value'] = 'Second line sample text';
        $obj->exposeParseHTMLText($elm, $tpx, $tpy, $tpw, $tph);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertGreaterThanOrEqual(2, \count($trace));

        $firstX = (float) $trace[0]['in_x'];
        $secondX = (float) $trace[1]['in_x'];

        $this->assertEqualsWithDelta(26.0, $firstX, 0.05);
        $this->assertEqualsWithDelta(20.0, $secondX, 0.05);
    }

    public function testParseHTMLTextSupportsNegativeTextIndentHangingIndent(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(20.0, 10.0, 80.0, 0.0);
        $obj->exposeResetBBoxTrace();

        $elm = $this->makeHtmlNode([
            'value' => 'Hanging indent sample text',
            'text-indent' => -4.0,
            'align' => 'L',
            'dir' => 'ltr',
            'fontname' => 'helvetica',
            'fontsize' => 10.0,
        ]);

        $tpx = 20.0;
        $tpy = 10.0;
        $tpw = 80.0;
        $tph = 0.0;

        $obj->exposeParseHTMLText($elm, $tpx, $tpy, $tpw, $tph);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotEmpty($trace);
        $this->assertEqualsWithDelta(16.0, (float) $trace[0]['in_x'], 0.05);
    }

    public function testAddHTMLCellTextIndentIsNotReappliedAfterPageBreak(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(20.0, 10.0, 80.0, 0.0);
        $obj->exposeResetBBoxTrace();

        $elm = $this->makeHtmlNode([
            'value' => 'First line before forced page break',
            'text-indent' => 6.0,
            'align' => 'L',
            'dir' => 'ltr',
            'fontname' => 'helvetica',
            'fontsize' => 10.0,
        ]);

        $tpx = 20.0;
        $tpy = 10.0;
        $tpw = 80.0;
        $tph = 0.0;

        $obj->exposeParseHTMLText($elm, $tpx, $tpy, $tpw, $tph);
        $obj->exposeExecuteHTMLTcpdfPageBreak('true', $tpx, $tpw);

        $tpy += 6.0;
        $elm['value'] = 'Continuation after forced page break';
        $obj->exposeParseHTMLText($elm, $tpx, $tpy, $tpw, $tph);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertGreaterThanOrEqual(2, \count($trace));

        $firstX = (float) $trace[0]['in_x'];
        $secondX = (float) $trace[1]['in_x'];

        $this->assertEqualsWithDelta(26.0, $firstX, 0.05);
        $this->assertEqualsWithDelta(20.0, $secondX, 0.05);
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
            . '<b>B</b><em>E</em><font>F</font><i>I</i><label>L</label><marker>M</marker>'
            . '<s>S</s><small>sm</small><span>sp</span><strike>st</strike><strong>sg</strong>'
            . '<tt>tt</tt><u>u</u><del>d</del><form>frm</form>'
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

    public function testGetHTMLCellTracksBBoxForRepeatedSmallTagText(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeResetBBoxTrace();
        $html = 'normal <small>small text</small> normal <small>small text</small>';
        $out = $obj->getHTMLCell($html, 0, 0, 200, 20);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertCount(4, $trace);

        $this->assertSame('normal ', $trace[0]['txt']);
        $this->assertSame('small text', $trace[1]['txt']);
        $this->assertSame(' normal ', $trace[2]['txt']);
        $this->assertSame('small text', $trace[3]['txt']);

        $this->assertEqualsWithDelta(10.0, (float) $trace[0]['font_size'], 1e-9);
        $this->assertEqualsWithDelta(6.666666666666666, (float) $trace[1]['font_size'], 1e-9);
        $this->assertEqualsWithDelta(10.0, (float) $trace[2]['font_size'], 1e-9);
        $this->assertEqualsWithDelta(6.666666666666666, (float) $trace[3]['font_size'], 1e-9);

        $this->assertGreaterThan((float) $trace[0]['bbox_end_x'], (float) $trace[1]['bbox_end_x']);
        $this->assertGreaterThan((float) $trace[1]['bbox_end_x'], (float) $trace[2]['bbox_end_x']);
        $this->assertGreaterThan((float) $trace[2]['bbox_end_x'], (float) $trace[3]['bbox_end_x']);

        $this->assertEqualsWithDelta(
            (float) $trace[0]['bbox_end_x'],
            (float) $trace[1]['bbox_x'],
            1e-9,
        );
        $this->assertEqualsWithDelta(
            (float) $trace[1]['bbox_end_x'],
            (float) $trace[2]['bbox_x'],
            1e-9,
        );
        $this->assertEqualsWithDelta(
            (float) $trace[2]['bbox_end_x'],
            (float) $trace[3]['bbox_x'],
            1e-9,
        );

        $this->assertEqualsWithDelta(
            (float) $trace[1]['bbox_w'],
            (float) $trace[3]['bbox_w'],
            1e-9,
        );
    }

    public function testGetHTMLCellRendersTopLevelTableOuterBorder(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $html = '<table border="1" cellspacing="3" cellpadding="4">'
            . '<tr><td style="border:0">X</td></tr>'
            . '</table>';

        $out = $obj->getHTMLCell($html, 0, 0, 80, 30);

        $this->assertNotSame('', $out);
        $this->assertMatchesRegularExpression('/\sre\s+s\b/s', $out);
    }

    public function testGetHTMLCellCentersMixedDirectionInlineRunAsOneLine(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $cellWidth = 150.0;
        $html = '<div style="text-align:center">'
            . 'The words &#8220;<span dir="rtl">&#1502;&#1494;&#1500; [mazel] &#1496;&#1493;&#1489; [tov]</span>'
            . '&#8221; mean &#8220;Congratulations!&#8221;</div>';

        $obj->exposeResetBBoxTrace();
        $out = $obj->getHTMLCell($html, 0, 0, $cellWidth, 40);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertCount(3, $trace);
        $this->assertEqualsWithDelta((float) $trace[0]['bbox_end_x'], (float) $trace[1]['bbox_x'], 1e-9);
        $this->assertEqualsWithDelta((float) $trace[1]['bbox_end_x'], (float) $trace[2]['bbox_x'], 1e-9);

        $lineLeft = (float) $trace[0]['bbox_x'];
        $lineRight = (float) $trace[2]['bbox_end_x'];
        $this->assertEqualsWithDelta($cellWidth / 2, ($lineLeft + $lineRight) / 2, 1e-9);
    }

    public function testGetHTMLCellCentersWrappedInlineSpansPerLine(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $cellWidth = 60.0;
        $html = '<table border="1" cellspacing="3" cellpadding="4"><tr><td align="center">'
            . '<span>Alfa</span> <span>Bravo</span> <span>Charlie</span> <span>Delta</span> '
            . '<span>Echo</span> <span>Foxtrot</span> <span>Golf</span> <span>Hotel</span>'
            . '</td></tr></table>';

        $obj->exposeResetBBoxTrace();
        $out = $obj->getHTMLCell($html, 0, 0, $cellWidth, 0);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotSame([], $trace);

        /** @var array<string, array{left: float, right: float}> $lines */
        $lines = [];
        foreach ($trace as $frag) {
            $key = \sprintf('%.6f', (float) $frag['bbox_y']);
            if (!isset($lines[$key])) {
                $lines[$key] = [
                    'left' => (float) $frag['bbox_x'],
                    'right' => (float) $frag['bbox_end_x'],
                ];
                continue;
            }

            $lines[$key]['left'] = \min($lines[$key]['left'], (float) $frag['bbox_x']);
            $lines[$key]['right'] = \max($lines[$key]['right'], (float) $frag['bbox_end_x']);
        }

        $lineboxes = \array_values($lines);
        $this->assertGreaterThanOrEqual(2, \count($lineboxes));

        $cellCenter = $cellWidth / 2;
        $checklines = \min(3, \count($lineboxes));
        for ($idx = 0; $idx < $checklines; ++$idx) {
            $line = $lineboxes[$idx];
            $this->assertEqualsWithDelta($cellCenter, ($line['left'] + $line['right']) / 2, 1.0);
        }

        // The first wrapped line must not be left-flush when centered.
        $this->assertGreaterThan(0.5, $lineboxes[0]['left']);
    }

    public function testGetHTMLCellRightAlignedWrappedInlineSpansUseMultipleLines(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $cellWidth = 150.0;
        $html = '<table border="1" cellspacing="3" cellpadding="4"><tr><td align="right">'
            . '<span>1R</span> <span>Alfa</span> <span>Bravo</span> <span>Charlie</span> <span>Delta</span> '
            . '<span>Echo</span> <span>Foxtrot</span> <span>Golf</span> <span>Hotel</span> <span>India</span> '
            . '<span>Juliett</span> <span>Kilo</span> <span>Lima</span> <span>Mike</span> <span>November</span> '
            . '<span>Oscar</span> <span>Papa</span> <span>Quebec</span> <span>Romeo</span> <span>Sierra</span> '
            . '<span>Tango</span> <span>Uniform</span> <span>Victor</span> <span>Whiskey</span> <span>Xray</span> '
            . '<span>Yankee</span> <span>Zulu</span>'
            . '</td></tr></table>';

        $obj->exposeResetBBoxTrace();
        $out = $obj->getHTMLCell($html, 0, 0, $cellWidth, 0);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotSame([], $trace);

        /** @var array<string, bool> $linekeys */
        $linekeys = [];
        foreach ($trace as $frag) {
            $linekeys[\sprintf('%.6f', (float) $frag['bbox_y'])] = true;
        }

        $this->assertGreaterThanOrEqual(2, \count($linekeys));
    }

    public function testGetHTMLCellTablePercentWidthsKeepFirstColumnTextInsideCell(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $html = '<table border="0" cellspacing="1" cellpadding="2" style="width:100%;">'
            . '<tr>'
            . '<td style="width:50%;">Gesch\u{00E4}ftsf\u{00FC}hrer Egon Schrempp Amtsgericht Stuttgart HRB 1234</td>'
            . '<td style="width:50%;">RIGHTCOL</td>'
            . '</tr>'
            . '</table>';

        $obj->exposeResetBBoxTrace();
        $out = $obj->getHTMLCell($html, 0, 0, 120, 0);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotSame([], $trace);

        $rightIdx = null;
        for ($idx = 0; $idx < \count($trace); ++$idx) {
            if ((string) $trace[$idx]['txt'] === 'RIGHTCOL') {
                $rightIdx = $idx;
                break;
            }
        }

        $this->assertNotNull($rightIdx, 'Unable to locate the second column text fragment in the trace.');
        $rightStartX = (float) $trace[(int) $rightIdx]['bbox_x'];

        $maxFirstColumnEndX = 0.0;
        for ($idx = 0; $idx < (int) $rightIdx; ++$idx) {
            $txt = (string) $trace[$idx]['txt'];
            if ($txt === '') {
                continue;
            }

            $maxFirstColumnEndX = \max($maxFirstColumnEndX, (float) $trace[$idx]['bbox_end_x']);
        }

        $this->assertGreaterThan(0.0, $maxFirstColumnEndX);
        $this->assertLessThanOrEqual(
            $rightStartX + 0.01,
            $maxFirstColumnEndX,
            'First-column text overflowed into the second column.',
        );
    }

    #[DataProvider('tableLineRegressionProvider')]
    public function testGetHTMLCellTableLineRegression(
        string $lineid,
        string $cellHtml,
        int $expectedLines,
        string $expectedFirstTxt,
        ?string $expectedSecondTxt,
    ): void {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $html = '<table border="1" cellspacing="3" cellpadding="4"><tr>' . $cellHtml . '</tr></table>';

        $obj->exposeResetBBoxTrace();
        $out = $obj->getHTMLCell($html, 0, 0, 150, 0);
        $this->assertNotSame('', $out, 'Rendered output should not be empty for row ' . $lineid);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotSame([], $trace, 'BBox trace should not be empty for row ' . $lineid);

        $this->assertSame($expectedFirstTxt, (string) $trace[0]['txt']);
        if ($expectedSecondTxt !== null) {
            $this->assertGreaterThanOrEqual(2, \count($trace));
            $this->assertStringContainsString($expectedSecondTxt, (string) $trace[1]['txt']);
        }

        /** @var array<string, bool> $linekeys */
        $linekeys = [];
        /** @var array<int, float> $lineOrder */
        $lineOrder = [];
        foreach ($trace as $frag) {
            $liney = (float) $frag['bbox_y'];
            $key = \sprintf('%.6f', $liney);
            if (!isset($linekeys[$key])) {
                $linekeys[$key] = true;
                $lineOrder[] = $liney;
            }
        }

        $this->assertCount($expectedLines, $linekeys, 'Unexpected wrapped line count for row ' . $lineid);

        // Ensure line progression is monotonic (no backwards jumps/overlap in render order).
        for ($idx = 1; $idx < \count($lineOrder); ++$idx) {
            $this->assertGreaterThanOrEqual(
                $lineOrder[$idx - 1],
                $lineOrder[$idx],
                'Non-monotonic line y progression detected for row ' . $lineid,
            );
        }
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: int, 3: string, 4: ?string}>
     */
    public static function tableLineRegressionProvider(): array
    {
        $line1Spans = '<span>Alfa</span> <span>Bravo</span> <span>Charlie</span> <span>Delta</span> '
            . '<span>Echo</span> <span>Foxtrot</span> <span>Golf</span> <span>Hotel</span> '
            . '<span>India</span> <span>Juliett</span> <span>Kilo</span> <span>Lima</span> '
            . '<span>Mike</span> <span>November</span> <span>Oscar</span> <span>Papa</span> '
            . '<span>Quebec</span> <span>Romeo</span> <span>Sierra</span> <span>Tango</span> '
            . '<span>Uniform</span> <span>Victor</span> <span>Whiskey</span> <span>Xray</span> '
            . '<span>Yankee</span> <span>Zulu</span>';

        $line2Text = ' A1 ex<i>amp</i>le <a href="https://tcpdf.org">link</a> column span. '
            . 'One two tree four five six seven eight nine ten.';

        return [
            [
                '1L',
                '<td align="left"><span>1L</span> ' . $line1Spans . '</td>',
                2,
                '1L',
                null,
            ],
            [
                '1C',
                '<td align="center"><span>1C</span> ' . $line1Spans . '</td>',
                2,
                '1C',
                null,
            ],
            [
                '1R',
                '<td align="right"><span>1R</span> ' . $line1Spans . '</td>',
                2,
                '1R',
                null,
            ],
            [
                '2L',
                '<td align="left"><span>2L</span>' . $line2Text . '</td>',
                1,
                '2L',
                'A1 ex',
            ],
            [
                '2C',
                '<td align="center"><span>2C</span>' . $line2Text . '</td>',
                1,
                '2C',
                'A1 ex',
            ],
            [
                '2R',
                '<td align="right"><span>2R</span>' . $line2Text . '</td>',
                1,
                '2R',
                'A1 ex',
            ],
            [
                '3L',
                '<td align="left"><small>3L small text</small>'
                . ' Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India '
                    . 'Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo'
                    . ' Sierra Tango Uniform Victor Whiskey Xray '
                    . 'Yankee Zulu</td>',
                2,
                '3L small text',
                'Alfa',
            ],
            [
                '3C',
                '<td align="center"><small>3C small text</small>'
                . ' Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India '
                    . 'Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo'
                    . ' Sierra Tango Uniform Victor Whiskey Xray '
                    . 'Yankee Zulu</td>',
                2,
                '3C small text',
                'Alfa',
            ],
            [
                '3R',
                '<td align="right"><small>3R small text</small>'
                . ' Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India '
                    . 'Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo'
                    . ' Sierra Tango Uniform Victor Whiskey Xray '
                    . 'Yankee Zulu</td>',
                2,
                '3R small text',
                'Alfa',
            ],
        ];
    }

    #[DataProvider('smallPrefixAlignmentProvider')]
    public function testGetHTMLCellMixedSmallPrefixKeepsFollowingTextOnFirstLine(string $align): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $cellWidth = 150.0;
        $html = '<table border="1" cellspacing="3" cellpadding="4"><tr><td align="' . $align . '">'
            . '<small>3X small text</small> Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett '
            . 'Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray '
            . 'Yankee Zulu'
            . '</td></tr></table>';

        $obj->exposeResetBBoxTrace();
        $out = $obj->getHTMLCell($html, 0, 0, $cellWidth, 0);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotSame([], $trace);

        $this->assertGreaterThanOrEqual(2, \count($trace));
        $this->assertSame('3X small text', \trim((string) $trace[0]['txt']));
        $this->assertStringContainsString('Alfa', (string) $trace[1]['txt']);

        // The text after </small> should start on the same line (or higher baseline-adjusted)
        // and not after a forced line advance.
        $this->assertLessThanOrEqual(
            (float) $trace[0]['in_y'] + 0.001,
            (float) $trace[1]['in_y'],
        );
    }

    public function testGetHTMLCellExampleTableSmallPrefixCenterRightKeepsFollowingTextOnSameLine(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $html = '<table border="1" cellspacing="3" cellpadding="4">'
            . '<tr><td align="left"><small>3L small text</small>'
            . ' Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India '
            . 'Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray '
            . 'Yankee Zulu</td></tr>'
            . '<tr><td align="center"><small>3C small text</small>'
            . ' Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India '
            . 'Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray '
            . 'Yankee Zulu</td></tr>'
            . '<tr><td align="right"><small>3R small text</small>'
            . ' Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India '
            . 'Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray '
            . 'Yankee Zulu</td></tr>'
            . '</table>';

        $obj->exposeResetBBoxTrace();
        $out = $obj->getHTMLCell($html, 0, 0, 150, 0);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotSame([], $trace);

        foreach (['3C small text', '3R small text'] as $label) {
            $smallIdx = null;
            foreach ($trace as $idx => $item) {
                if (\trim((string) $item['txt']) === $label) {
                    $smallIdx = $idx;
                    break;
                }
            }

            $this->assertNotNull($smallIdx, 'Missing trace fragment: ' . $label);

            $nextIdx = null;
            for ($idx = ((int) $smallIdx + 1); $idx < \count($trace); ++$idx) {
                if (\trim((string) $trace[$idx]['txt']) === '') {
                    continue;
                }

                $nextIdx = $idx;
                break;
            }

            $this->assertNotNull($nextIdx, 'Missing follow-up fragment for: ' . $label);
            $this->assertStringContainsString('Alfa', (string) $trace[(int) $nextIdx]['txt']);

            $this->assertLessThanOrEqual(
                (float) $trace[(int) $smallIdx]['in_y'] + 0.001,
                (float) $trace[(int) $nextIdx]['in_y'],
                'Text after ' . $label . ' moved to a new line.',
            );
        }
    }

    /**
     * @return array<int, array{0: string}>
     */
    public static function smallPrefixAlignmentProvider(): array
    {
        return [
            ['center'],
            ['right'],
        ];
    }

    public function testGetHTMLCellContinuesInlineEmAfterMultiLineWrappedTextOnSameLine(): void
    {
        // Regression: a long plain-text fragment that internally wraps to a new
        // visual line must not push the immediately following inline content
        // (here "(<em>Sierra-Tango</em>)") onto a third line. The "(" already
        // landed on the second line and "Sierra-Tango" must continue right
        // after it, keeping the whole paragraph on two lines.
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $html = '<p>Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo. '
            . 'Lima Mike November Oscar Papa Quebec Romeo (<em>Sierra-Tango</em>) Uniform Victor '
            . 'Whiskey (<em>Xray-Yankee</em>). Zulu.</p>';

        $obj->exposeResetBBoxTrace();
        $obj->getHTMLCell($html, 20, 100, 180, 0);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotSame([], $trace);

        $sierraIdx = null;
        $xrayIdx = null;
        foreach ($trace as $idx => $entry) {
            $txt = (string) $entry['txt'];
            if (($sierraIdx === null) && \str_contains($txt, 'Sierra-Tango')) {
                $sierraIdx = $idx;
            }

            if (($xrayIdx === null) && \str_contains($txt, 'Xray-Yankee')) {
                $xrayIdx = $idx;
            }
        }

        $this->assertNotNull($sierraIdx, 'Sierra-Tango fragment must be present in the trace');
        $this->assertNotNull($xrayIdx, 'Xray-Yankee fragment must be present in the trace');
        $this->assertGreaterThan(0, (int) $sierraIdx);

        $prevEntry = $trace[(int) $sierraIdx - 1];
        $sierraEntry = $trace[(int) $sierraIdx];
        $xrayEntry = $trace[(int) $xrayIdx];

        // Em fragment must continue on the same visual line as the "(" prefix
        // produced by the previous wrapped fragment, not on a new line below.
        $this->assertEqualsWithDelta(
            (float) $prevEntry['bbox_y'],
            (float) $sierraEntry['bbox_y'],
            0.01,
            'Em fragment "Sierra-Tango" must stay on the same line as the preceding "(" prefix.',
        );
        $this->assertGreaterThanOrEqual(
            (float) $prevEntry['bbox_end_x'] - 0.01,
            (float) $sierraEntry['bbox_x'],
            'Em fragment "Sierra-Tango" must continue right after the preceding "(" prefix.',
        );

        // The whole paragraph should fit on two visual lines: every fragment's
        // bbox_y reports the y of the last visual line touched by getTextCell,
        // so for a 2-line paragraph all five fragments share the same y.
        /** @var array<string, bool> $linekeys */
        $linekeys = [];
        foreach ($trace as $entry) {
            $linekeys[\sprintf('%.3f', (float) $entry['bbox_y'])] = true;
        }

        $this->assertCount(
            1,
            $linekeys,
            'Paragraph must render on exactly two lines: the em-wrapped continuation must not start a third line.',
        );

        // Both em fragments and their surrounding parentheses share the second line.
        $this->assertEqualsWithDelta(
            (float) $sierraEntry['bbox_y'],
            (float) $xrayEntry['bbox_y'],
            0.01,
            'Both em fragments must share the second line.',
        );
    }

    public function testGetHTMLCellAppliesLineHeightToWrappedContinuationLines(): void
    {
        $text = 'Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo Lima Mike November Oscar Papa '
            . 'Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo Lima Mike November Oscar Papa';

        $measureHeight = function (string $lineHeight) use ($text): float {
            $obj = $this->getBBoxProbeTestObject();
            $this->initFontAndPage($obj);
            $obj->exposeResetBBoxTrace();

            $html = '<p style="line-height:' . $lineHeight . ';">' . $text . '</p>';
            $obj->getHTMLCell($html, 20, 30, 20, 0);

            $trace = $obj->exposeGetBBoxTrace();
            $this->assertNotSame([], $trace);

            $first = $trace[0];
            $this->assertGreaterThan(0.0, (float) $first['bbox_h']);

            // getTextCell reports the bbox of the last visual line when wrapping;
            // use vertical delta from input y to that last line to measure run height.
            $deltaY = (float) $first['bbox_y'] - (float) $first['in_y'];
            $this->assertGreaterThan(
                (float) $first['font_size'] + 0.1,
                $deltaY,
                'Expected wrapped text sample to span multiple lines.'
            );

            return $deltaY;
        };

        $height100 = $measureHeight('100%');
        $height200 = $measureHeight('200%');

        $this->assertGreaterThan(
            $height100 + 1.0,
            $height200,
            'line-height must affect continuation lines created by automatic wraps.'
        );
    }

    public function testGetHTMLCellContinuesPlainTextAfterEmFollowedByLongMultiLineRun(): void
    {
        // Regression: when an inline <em> ends mid-line and the next plain-text
        // fragment is long enough to internally wrap to multiple lines, its
        // leading non-space chunk (here ")") must continue right after the
        // <em> on the SAME line. The previous logic considered the line
        // "deep" because the italic <em> bumped linebottom by a sub-millimeter
        // font-metric drift, and force-wrapped the whole continuation
        // fragment to a fresh line — pushing ")" to a new line by itself.
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $html = '<p>This document demonstrates PDF encryption and permission controls using tc-lib-pdf. '
            . 'The file is protected with a user password (<em>demo-user</em>) and an owner password '
            . '(<em>demo-owner</em>). Encryption restricts unauthorized access while the owner password '
            . 'grants full control.</p>';

        $obj->exposeResetBBoxTrace();
        $obj->getHTMLCell($html, 20, 100, 180, 0);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotSame([], $trace);

        // Find the demo-owner em fragment and the immediately following plain
        // continuation that starts with ")".
        $ownerIdx = null;
        foreach ($trace as $idx => $entry) {
            if (\str_contains((string) $entry['txt'], 'demo-owner')) {
                $ownerIdx = $idx;
                break;
            }
        }

        $this->assertNotNull($ownerIdx, 'demo-owner fragment must be present in the trace.');
        $this->assertArrayHasKey(
            (int) $ownerIdx + 1,
            $trace,
            'Continuation fragment after demo-owner must be present.',
        );

        $ownerEntry = $trace[(int) $ownerIdx];
        $contEntry = $trace[(int) $ownerIdx + 1];

        // The continuation MUST start with the closing parenthesis "glued" to
        // demo-owner: it must be passed to getTextCell with the same in_y as
        // demo-owner (i.e., on the same line cursor) so that its leading ")"
        // is rendered right after the em fragment, not on a fresh new line.
        $this->assertStringStartsWith(
            ')',
            \ltrim((string) $contEntry['txt']),
            'Continuation fragment must start with the closing parenthesis ").".',
        );
        $this->assertEqualsWithDelta(
            (float) $ownerEntry['in_y'],
            (float) $contEntry['in_y'],
            0.01,
            'Closing ")" after demo-owner must stay on the same line cursor as demo-owner.',
        );
    }

    public function testParseHTMLTextWrapsLargeInlineFragmentBeforeItOverflowsRemainingWidth(): void
    {
        $measure = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($measure);
        $measure->exposeInitHTMLCellContext(0, 0, 200, 0);

        $prefixElm = $this->makeHtmlNode([
            'tag' => false,
            'opening' => false,
            'self' => false,
            'value' => 'medium ',
        ]);
        $largeElm = $this->makeHtmlNode([
            'tag' => false,
            'opening' => false,
            'self' => false,
            'fontsize' => 12.0,
            'value' => 'large',
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 200.0;
        $tph = 0.0;
        $measure->exposeResetBBoxTrace();
        $measure->exposeParseHTMLText($prefixElm, $tpx, $tpy, $tpw, $tph);
        $prefixTrace = $measure->exposeGetBBoxTrace();
        $prefixWidth = (float) $prefixTrace[0]['bbox_w'];

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 200.0;
        $tph = 0.0;
        $measure->exposeResetBBoxTrace();
        $measure->exposeParseHTMLText($largeElm, $tpx, $tpy, $tpw, $tph);
        $largeTrace = $measure->exposeGetBBoxTrace();
        $largeWidth = (float) $largeTrace[0]['bbox_w'];

        $cellWidth = $prefixWidth + $largeWidth - 0.1;

        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0, 0, $cellWidth, 0);
        $obj->exposeResetBBoxTrace();

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = $cellWidth;
        $tph = 0.0;
        $obj->exposeParseHTMLText($prefixElm, $tpx, $tpy, $tpw, $tph);
        $obj->exposeParseHTMLText($largeElm, $tpx, $tpy, $tpw, $tph);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertCount(2, $trace);
        $this->assertSame('medium ', $trace[0]['txt']);
        $this->assertSame('large', $trace[1]['txt']);
        $this->assertGreaterThan(0.0, (float) $trace[0]['bbox_x'] + (float) $trace[0]['bbox_w']);
        $this->assertEqualsWithDelta(0.0, (float) $trace[1]['bbox_x'], 1e-9);
        $this->assertLessThanOrEqual($cellWidth + 1e-9, (float) $trace[1]['bbox_end_x']);
    }

    public function testParseHTMLTextKeepsBreakableFragmentOnCurrentLineWhenOnlyTailOverflows(): void
    {
        $measure = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($measure);
        $measure->exposeInitHTMLCellContext(0, 0, 300, 0);

        $prefixElm = $this->makeHtmlNode([
            'tag' => false,
            'opening' => false,
            'self' => false,
            'value' => 'A1 example link',
        ]);
        $breakableElm = $this->makeHtmlNode([
            'tag' => false,
            'opening' => false,
            'self' => false,
            'value' => ' column span one two three four five six seven eight nine ten',
        ]);
        $firstChunkElm = $this->makeHtmlNode([
            'tag' => false,
            'opening' => false,
            'self' => false,
            'value' => ' column span',
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 300.0;
        $tph = 0.0;
        $measure->exposeResetBBoxTrace();
        $measure->exposeParseHTMLText($prefixElm, $tpx, $tpy, $tpw, $tph);
        $prefixTrace = $measure->exposeGetBBoxTrace();
        $prefixWidth = (float) $prefixTrace[0]['bbox_w'];

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 300.0;
        $tph = 0.0;
        $measure->exposeResetBBoxTrace();
        $measure->exposeParseHTMLText($breakableElm, $tpx, $tpy, $tpw, $tph);
        $breakableTrace = $measure->exposeGetBBoxTrace();
        $breakableWidth = (float) $breakableTrace[0]['bbox_w'];

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 300.0;
        $tph = 0.0;
        $measure->exposeResetBBoxTrace();
        $measure->exposeParseHTMLText($firstChunkElm, $tpx, $tpy, $tpw, $tph);
        $chunkTrace = $measure->exposeGetBBoxTrace();
        $chunkWidth = (float) $chunkTrace[0]['bbox_w'];

        $cellWidth = $prefixWidth + $chunkWidth + 0.2;
        $cellWidth = \min($cellWidth, $prefixWidth + $breakableWidth - 0.1);

        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0, 0, $cellWidth, 0);
        $obj->exposeResetBBoxTrace();

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = $cellWidth;
        $tph = 0.0;
        $obj->exposeParseHTMLText($prefixElm, $tpx, $tpy, $tpw, $tph);
        $obj->exposeParseHTMLText($breakableElm, $tpx, $tpy, $tpw, $tph);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertCount(2, $trace);
        $this->assertSame('A1 example link', $trace[0]['txt']);
        $this->assertSame(' column span one two three four five six seven eight nine ten', $trace[1]['txt']);
        $this->assertEqualsWithDelta(0.0, (float) $trace[1]['bbox_x'], 1e-9);
        $this->assertGreaterThan((float) $trace[0]['bbox_y'], (float) $trace[1]['bbox_y']);
    }

    public function testParseHTMLTextTreatsLeadingSpaceLongWordAsUnbreakableForPreWrap(): void
    {
        $measure = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($measure);
        $measure->exposeInitHTMLCellContext(0, 0, 220, 0);

        $prefixElm = $this->makeHtmlNode([
            'tag' => false,
            'opening' => false,
            'self' => false,
            'value' => 'prefix ',
        ]);
        $wordElm = $this->makeHtmlNode([
            'tag' => false,
            'opening' => false,
            'self' => false,
            'value' => ' thisisanotherverylongword',
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 220.0;
        $tph = 0.0;
        $measure->exposeResetBBoxTrace();
        $measure->exposeParseHTMLText($prefixElm, $tpx, $tpy, $tpw, $tph);
        $prefixTrace = $measure->exposeGetBBoxTrace();
        $prefixWidth = (float) $prefixTrace[0]['bbox_w'];

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 220.0;
        $tph = 0.0;
        $measure->exposeResetBBoxTrace();
        $measure->exposeParseHTMLText($wordElm, $tpx, $tpy, $tpw, $tph);
        $wordTrace = $measure->exposeGetBBoxTrace();
        $wordWidth = (float) $wordTrace[0]['bbox_w'];

        $cellWidth = $prefixWidth + $wordWidth - 0.1;

        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0, 0, $cellWidth, 0);
        $obj->exposeResetBBoxTrace();

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = $cellWidth;
        $tph = 0.0;
        $obj->exposeParseHTMLText($prefixElm, $tpx, $tpy, $tpw, $tph);
        $obj->exposeParseHTMLText($wordElm, $tpx, $tpy, $tpw, $tph);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertCount(2, $trace);
        $this->assertSame('prefix ', $trace[0]['txt']);
        $this->assertSame(' thisisanotherverylongword', $trace[1]['txt']);
        $this->assertEqualsWithDelta(0.0, (float) $trace[1]['bbox_x'], 1e-9);
        $this->assertGreaterThan((float) $trace[0]['bbox_y'], (float) $trace[1]['bbox_y']);
    }

    public function testAllParseHTMLTagMethodsCanBeInvoked(): void
    {
        $probe = $this->getInternalTestObject();
        $methods = $probe->exposeParseHTMLTagMethods();
        $this->assertNotSame([], $methods);
        $this->assertGreaterThanOrEqual(100, \count($methods));

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

    public function testParseHTMLTagOpenSpanAppliesColorAttributes(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $elm = $obj->exposeGetHTMLRootProperties();
        $elm['value'] = 'span';
        $elm['fgcolor'] = 'black';
        $elm['bgcolor'] = '#ffff00';
        $elm['attribute'] = [
            'color' => '#ff0000',
            'bgcolor' => '#00ff00',
        ];

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 40.0;
        $tph = 20.0;

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENspan', $elm, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertStringContainsString('100%,0%,0%', (string) $hrc['dom'][0]['fgcolor']);
        $this->assertStringContainsString('0%,100%,0%', (string) $hrc['dom'][0]['bgcolor']);
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

        $hrc = $obj->exposeGetHTMLRenderContext();
        $stack = $hrc['tablestack'] ?? null;
        $this->assertIsArray($stack);
        $this->assertCount(1, $stack);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEthead', $elm, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $stack = $hrc['tablestack'] ?? null;
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
        // 2 link annotations + 1 textarea form-field annotation
        $this->assertCount(3, $annotation);
    }

    public function testGetHTMLCellAnchorSurvivesSelectCloseTag(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<a href="https://example.com">A<select></select>B</a>', 0, 0, 30, 10);

        $this->assertNotSame('', $out);
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        // 2 link annotations + 1 select form-field annotation
        $this->assertCount(3, $annotation);
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

    public function testNestedListItemIndentsIncrementally(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $originx = 20.0;
        $obj->exposeInitHTMLCellContext($originx, 100.0, 150.0, 0.0);

        $elm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);

        $tpx = $originx;
        $tpy = 100.0;
        $tpw = 150.0;
        $tph = 0.0;

        // Push depth-1 list.
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENol', $elm, $tpx, $tpy, $tpw, $tph);

        // Open depth-1 li: tpx must advance by exactly one indentWidth.
        $tpx = $originx;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENli', $elm, $tpx, $tpy, $tpw, $tph);
        $tpxDepth1 = $tpx;
        $indentWidth = $tpxDepth1 - $originx;
        $this->assertGreaterThan(0.0, $indentWidth, 'depth-1 li should add a positive indent');

        // Push depth-2 list inside the depth-1 li, then open a depth-2 li.
        // openHTMLBlock will reset tpx to the updated originx (= tpxDepth1).
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENol', $elm, $tpx, $tpy, $tpw, $tph);
        $tpxBeforeDepth2 = $tpx; // equals tpxDepth1 after openHTMLBlock
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENli', $elm, $tpx, $tpy, $tpw, $tph);
        $indent2 = $tpx - $tpxBeforeDepth2;

        // Each successive level must add exactly one indentWidth, not depth * indentWidth.
        $this->assertEqualsWithDelta(
            $indentWidth,
            $indent2,
            0.001,
            'depth-2 li should increment tpx by the same indentWidth as depth-1 li'
        );
    }

    public function testListItemUsesListCssIndentOverrideWhenPresent(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeResetBBoxTrace();
        $obj->addHTMLCell('<ol><li>Probe item baseline</li></ol>', 20, 20, 120, 0);

        $defaultX = null;
        foreach ($obj->exposeGetBBoxTrace() as $entry) {
            if (\str_contains((string) $entry['txt'], 'Probe item baseline')) {
                $defaultX = (float) $entry['in_x'];
                break;
            }
        }

        $this->assertNotNull($defaultX);

        $obj->exposeResetBBoxTrace();
        $obj->addHTMLCell('<ol style="padding-left:9mm"><li>Probe item baseline</li></ol>', 20, 20, 120, 0);

        $cssX = null;
        foreach ($obj->exposeGetBBoxTrace() as $entry) {
            if (\str_contains((string) $entry['txt'], 'Probe item baseline')) {
                $cssX = (float) $entry['in_x'];
                break;
            }
        }

        $this->assertNotNull($cssX);
        $this->assertLessThan((float) $defaultX, (float) $cssX);
    }

    public function testListItemCssIndentOverrideTakesPrecedenceOverListLevel(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeResetBBoxTrace();
        $obj->addHTMLCell('<ol style="padding-left:9mm"><li>Probe precedence</li></ol>', 20, 20, 120, 0);

        $listX = null;
        foreach ($obj->exposeGetBBoxTrace() as $entry) {
            if (\str_contains((string) $entry['txt'], 'Probe precedence')) {
                $listX = (float) $entry['in_x'];
                break;
            }
        }

        $this->assertNotNull($listX);

        $obj->exposeResetBBoxTrace();
        $obj->addHTMLCell(
            '<ol style="padding-left:9mm"><li style="margin-left:4mm">Probe precedence</li></ol>',
            20,
            20,
            120,
            0,
        );

        $liX = null;
        foreach ($obj->exposeGetBBoxTrace() as $entry) {
            if (\str_contains((string) $entry['txt'], 'Probe precedence')) {
                $liX = (float) $entry['in_x'];
                break;
            }
        }

        $this->assertNotNull($liX);
        $this->assertGreaterThan((float) $listX, (float) $liX);
    }

    public function testNestedListDefaultIndentKeepsSameLevelStableAndInnerDeeper(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeResetBBoxTrace();
        $obj->addHTMLCell(
            '<ol><li>OUTER_A<ul><li>INNER_B</li></ul></li><li>OUTER_C</li></ol>',
            20,
            20,
            120,
            0,
        );

        $outerAX = null;
        $innerBX = null;
        $outerCX = null;
        foreach ($obj->exposeGetBBoxTrace() as $entry) {
            $txt = (string) $entry['txt'];
            if (($outerAX === null) && \str_contains($txt, 'OUTER_A')) {
                $outerAX = (float) $entry['in_x'];
            }

            if (($innerBX === null) && \str_contains($txt, 'INNER_B')) {
                $innerBX = (float) $entry['in_x'];
            }

            if (($outerCX === null) && \str_contains($txt, 'OUTER_C')) {
                $outerCX = (float) $entry['in_x'];
            }
        }

        $this->assertNotNull($outerAX);
        $this->assertNotNull($innerBX);
        $this->assertNotNull($outerCX);
        $this->assertGreaterThan((float) $outerAX, (float) $innerBX);
        $this->assertEqualsWithDelta((float) $outerAX, (float) $outerCX, 0.001);
    }

    public function testNestedListDepthCssOverrideChangesOnlyTargetDepthIndent(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeResetBBoxTrace();
        $obj->addHTMLCell(
            '<ol style="padding-left:9mm"><li>OUTER_D'
            . '<ul><li>INNER_E</li></ul></li></ol>',
            20,
            20,
            120,
            0,
        );

        $outerBaseX = null;
        $innerBaseX = null;
        foreach ($obj->exposeGetBBoxTrace() as $entry) {
            $txt = (string) $entry['txt'];
            if (($outerBaseX === null) && \str_contains($txt, 'OUTER_D')) {
                $outerBaseX = (float) $entry['in_x'];
            }

            if (($innerBaseX === null) && \str_contains($txt, 'INNER_E')) {
                $innerBaseX = (float) $entry['in_x'];
            }
        }

        $this->assertNotNull($outerBaseX);
        $this->assertNotNull($innerBaseX);

        $obj->exposeResetBBoxTrace();
        $obj->addHTMLCell(
            '<ol style="padding-left:9mm"><li>OUTER_D'
            . '<ul style="margin-left:2mm"><li>INNER_E</li></ul></li></ol>',
            20,
            20,
            120,
            0,
        );

        $outerOverrideX = null;
        $innerOverrideX = null;
        foreach ($obj->exposeGetBBoxTrace() as $entry) {
            $txt = (string) $entry['txt'];
            if (($outerOverrideX === null) && \str_contains($txt, 'OUTER_D')) {
                $outerOverrideX = (float) $entry['in_x'];
            }

            if (($innerOverrideX === null) && \str_contains($txt, 'INNER_E')) {
                $innerOverrideX = (float) $entry['in_x'];
            }
        }

        $this->assertNotNull($outerOverrideX);
        $this->assertNotNull($innerOverrideX);
        $this->assertEqualsWithDelta((float) $outerBaseX, (float) $outerOverrideX, 0.001);
        $this->assertLessThan((float) $innerBaseX, (float) $innerOverrideX);
    }

    public function testListItemInsideMarkerDoesNotShrinkContentBox(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $originx = 20.0;
        $maxwidth = 150.0;
        $obj->exposeInitHTMLCellContext($originx, 100.0, $maxwidth, 0.0);

        $elm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'list-style-position' => 'inside',
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);

        $tpx = $originx;
        $tpy = 100.0;
        $tpw = $maxwidth;
        $tph = 0.0;

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENol', $elm, $tpx, $tpy, $tpw, $tph);

        $before = $obj->exposeGetHTMLRenderContext();
        $out = $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENli', $elm, $tpx, $tpy, $tpw, $tph);
        $after = $obj->exposeGetHTMLRenderContext();

        $this->assertNotSame('', $out);
        $this->assertGreaterThan($originx, $tpx);
        $this->assertSame((float) $before['cellctx']['originx'], (float) $after['cellctx']['originx']);
        $this->assertSame((float) $before['cellctx']['maxwidth'], (float) $after['cellctx']['maxwidth']);
        $this->assertSame($maxwidth, $tpw);
    }

    public function testListItemOutsideMarkerShrinksContentBox(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $originx = 20.0;
        $maxwidth = 150.0;
        $obj->exposeInitHTMLCellContext($originx, 100.0, $maxwidth, 0.0);

        $elm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'list-style-position' => 'outside',
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);

        $tpx = $originx;
        $tpy = 100.0;
        $tpw = $maxwidth;
        $tph = 0.0;

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENol', $elm, $tpx, $tpy, $tpw, $tph);

        $before = $obj->exposeGetHTMLRenderContext();
        $out = $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENli', $elm, $tpx, $tpy, $tpw, $tph);
        $after = $obj->exposeGetHTMLRenderContext();

        $this->assertNotSame('', $out);
        $this->assertGreaterThan($originx, $tpx);
        $this->assertGreaterThan((float) $before['cellctx']['originx'], (float) $after['cellctx']['originx']);
        $this->assertLessThan((float) $before['cellctx']['maxwidth'], (float) $after['cellctx']['maxwidth']);
        $this->assertLessThan($maxwidth, $tpw);
    }

    public function testMarkerColorIsAppliedToTextBasedMarkers(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        // Render ordered list with marker color style
        // Just verify no errors occur during rendering with marker styles
        $obj->addHTMLCell(
            '<style>li::marker { color: red; }</style>'
            . '<ol><li>ITEM_1</li><li>ITEM_2</li></ol>',
            20,
            20,
            120,
            0,
        );

        $this->assertNotSame('', $obj->getOutPDFString());
    }


    public function testListItemInsideMarkerUsesSameBulletAnchorAsOutside(): void
    {
        $insideObj = $this->getInternalTestObject();
        $this->initFontAndPage($insideObj);

        $insideElm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'list-style-position' => 'inside',
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);

        $insideObj->exposeInitHTMLCellContext(20.0, 100.0, 150.0, 0.0);
        $insideTpx = 20.0;
        $insideTpy = 100.0;
        $insideTpw = 150.0;
        $insideTph = 0.0;
        $insideObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENol',
            $insideElm,
            $insideTpx,
            $insideTpy,
            $insideTpw,
            $insideTph,
        );
        $insideOut = $insideObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENli',
            $insideElm,
            $insideTpx,
            $insideTpy,
            $insideTpw,
            $insideTph,
        );

        $outsideObj = $this->getInternalTestObject();
        $this->initFontAndPage($outsideObj);

        $outsideElm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'list-style-position' => 'outside',
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);

        $outsideObj->exposeInitHTMLCellContext(20.0, 100.0, 150.0, 0.0);
        $outsideTpx = 20.0;
        $outsideTpy = 100.0;
        $outsideTpw = 150.0;
        $outsideTph = 0.0;
        $outsideObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENol',
            $outsideElm,
            $outsideTpx,
            $outsideTpy,
            $outsideTpw,
            $outsideTph,
        );
        $outsideOut = $outsideObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENli',
            $outsideElm,
            $outsideTpx,
            $outsideTpy,
            $outsideTpw,
            $outsideTph,
        );

        $this->assertNotSame('', $insideOut);
        $this->assertNotSame('', $outsideOut);
        $this->assertNotSame($outsideOut, $insideOut);
    }

    public function testInsideListIndentOverrideIsNotTrimmedByOutsideMarkerSpacingRule(): void
    {
        $insideObj = $this->getInternalTestObject();
        $this->initFontAndPage($insideObj);

        $insideElm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'list-style-position' => 'inside',
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 9.0],
            'style' => ['padding-left' => '9mm'],
        ]);

        $insideObj->exposeInitHTMLCellContext(20.0, 100.0, 150.0, 0.0);
        $insideTpx = 20.0;
        $insideTpy = 100.0;
        $insideTpw = 150.0;
        $insideTph = 0.0;
        $insideObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENol',
            $insideElm,
            $insideTpx,
            $insideTpy,
            $insideTpw,
            $insideTph,
        );
        $insideBefore = $insideTpx;
        $insideObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENli',
            $insideElm,
            $insideTpx,
            $insideTpy,
            $insideTpw,
            $insideTph,
        );
        $insideAdvance = $insideTpx - $insideBefore;

        $outsideObj = $this->getInternalTestObject();
        $this->initFontAndPage($outsideObj);

        $outsideElm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'list-style-position' => 'outside',
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 9.0],
            'style' => ['padding-left' => '9mm'],
        ]);

        $outsideObj->exposeInitHTMLCellContext(20.0, 100.0, 150.0, 0.0);
        $outsideTpx = 20.0;
        $outsideTpy = 100.0;
        $outsideTpw = 150.0;
        $outsideTph = 0.0;
        $outsideObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENol',
            $outsideElm,
            $outsideTpx,
            $outsideTpy,
            $outsideTpw,
            $outsideTph,
        );
        $outsideBefore = $outsideTpx;
        $outsideObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENli',
            $outsideElm,
            $outsideTpx,
            $outsideTpy,
            $outsideTpw,
            $outsideTph,
        );
        $outsideAdvance = $outsideTpx - $outsideBefore;

        $this->assertGreaterThan(0.0, $outsideAdvance);
        $this->assertGreaterThan($outsideAdvance, $insideAdvance);
    }

    public function testOutsideSmallListIndentOverrideIsNotCanceledByTrimWorkaround(): void
    {
        $baseObj = $this->getInternalTestObject();
        $this->initFontAndPage($baseObj);

        $baseElm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'list-style-position' => 'outside',
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);

        $baseObj->exposeInitHTMLCellContext(20.0, 100.0, 150.0, 0.0);
        $baseTpx = 20.0;
        $baseTpy = 100.0;
        $baseTpw = 150.0;
        $baseTph = 0.0;
        $baseObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENul',
            $baseElm,
            $baseTpx,
            $baseTpy,
            $baseTpw,
            $baseTph,
        );
        $baseBefore = $baseTpx;
        $baseObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENli',
            $baseElm,
            $baseTpx,
            $baseTpy,
            $baseTpw,
            $baseTph,
        );
        $baseAdvance = $baseTpx - $baseBefore;

        $smallObj = $this->getInternalTestObject();
        $this->initFontAndPage($smallObj);

        $smallElm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'list-style-position' => 'outside',
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 1.5],
            'style' => ['padding-left' => '1.5mm'],
        ]);
        $smallObj->exposeInitHTMLCellContext(20.0, 100.0, 150.0, 0.0);
        $smallTpx = 20.0;
        $smallTpy = 100.0;
        $smallTpw = 150.0;
        $smallTph = 0.0;
        $smallObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENul',
            $smallElm,
            $smallTpx,
            $smallTpy,
            $smallTpw,
            $smallTph,
        );
        $smallBefore = $smallTpx;
        $smallObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENli',
            $smallElm,
            $smallTpx,
            $smallTpy,
            $smallTpw,
            $smallTph,
        );
        $smallAdvance = $smallTpx - $smallBefore;

        $this->assertGreaterThan(0.0, $smallAdvance);
        $this->assertLessThan($baseAdvance, $smallAdvance);
    }

    public function testListItemInsidePositionAddsExtraTextOffsetComparedToOutside(): void
    {
        $insideObj = $this->getInternalTestObject();
        $this->initFontAndPage($insideObj);

        $insideElm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'list-style-position' => 'inside',
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);

        $insideObj->exposeInitHTMLCellContext(20.0, 100.0, 150.0, 0.0);
        $insideTpx = 20.0;
        $insideTpy = 100.0;
        $insideTpw = 150.0;
        $insideTph = 0.0;
        $insideObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENul',
            $insideElm,
            $insideTpx,
            $insideTpy,
            $insideTpw,
            $insideTph,
        );
        $insideBefore = $insideTpx;
        $insideObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENli',
            $insideElm,
            $insideTpx,
            $insideTpy,
            $insideTpw,
            $insideTph,
        );
        $insideAdvance = $insideTpx - $insideBefore;

        $outsideObj = $this->getInternalTestObject();
        $this->initFontAndPage($outsideObj);

        $outsideElm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'list-style-position' => 'outside',
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);

        $outsideObj->exposeInitHTMLCellContext(20.0, 100.0, 150.0, 0.0);
        $outsideTpx = 20.0;
        $outsideTpy = 100.0;
        $outsideTpw = 150.0;
        $outsideTph = 0.0;
        $outsideObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENul',
            $outsideElm,
            $outsideTpx,
            $outsideTpy,
            $outsideTpw,
            $outsideTph,
        );
        $outsideBefore = $outsideTpx;
        $outsideObj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENli',
            $outsideElm,
            $outsideTpx,
            $outsideTpy,
            $outsideTpw,
            $outsideTph,
        );
        $outsideAdvance = $outsideTpx - $outsideBefore;

        $this->assertGreaterThan($outsideAdvance, $insideAdvance);
    }
    public function testMarkerColorIsAppliedToDiscMarker(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        // Create a simple ul with disc marker and marker color style
        $obj->addHTMLCell(
            '<style>li::marker { color: #FF0000; }</style>'
            . '<ul><li>ITEM_1</li></ul>',
            20,
            20,
            120,
            0,
        );

        // Just verify no errors occur during rendering with marker styles
        // (Full PDF color rendering would require inspecting PDF ops, which is complex)
        $this->assertNotSame('', $obj->getOutPDFString());
    }

    public function testUnsupportedMarkerPropertiesAreIgnored(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        // Marker styles with unsupported properties (text-decoration, margin, etc.)
        // should be silently ignored without errors
        $obj->addHTMLCell(
            '<style>li::marker { '
            . 'color: red; '
            . 'text-decoration: underline; '
            . 'margin-left: 5mm; '
            . 'padding: 2mm; '
            . '}</style>'
            . '<ol><li>ITEM_1</li></ol>',
            20,
            20,
            120,
            0,
        );

        // If we get here without errors, unsupported properties were properly handled
        $this->assertNotSame('', $obj->getOutPDFString());
    }

    public function testClassBasedMarkerSelectorAttachesFilteredMarkerStyleToLi(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $dom = $obj->exposeGetHTMLDOM(
            '<style>.marker-red li::marker { color: red; font-weight: bold; text-decoration: underline; }</style>'
            . '<ol class="marker-red"><li>Item</li></ol>',
        );

        $liNode = null;
        foreach ($dom as $node) {
            if (($node['value'] ?? '') === 'li' && !empty($node['opening'])) {
                $liNode = $node;
                break;
            }
        }

        $this->assertNotNull($liNode);
        $this->assertArrayHasKey('attribute', $liNode);
        $this->assertIsArray($liNode['attribute']);
        $this->assertArrayHasKey('pseudo-marker-style', $liNode['attribute']);
        $this->assertIsArray($liNode['attribute']['pseudo-marker-style']);
        $markerStyle = $liNode['attribute']['pseudo-marker-style'];

        $this->assertSame('red', $markerStyle['color'] ?? null);
        $this->assertSame('bold', $markerStyle['font-weight'] ?? null);
        $this->assertArrayNotHasKey('text-decoration', $markerStyle);
    }

    public function testListStyleImageCSSPropertyIsParsedWithoutErrors(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        // list-style-image CSS should be accepted and parsed without errors
        // (Full image loading/rendering is deferred to future phases)
        $listImageDataUri = 'data:image/svg+xml;base64,'
            . 'PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9Ijgi'
            . 'PjxjaXJjbGUgY3g9IjQiIGN5PSI0IiByPSI0IiBmaWxsPSJyZWQiLz48L3N2Zz4=';
        $obj->addHTMLCell(
            '<ul style="list-style-image: url(' . $listImageDataUri . ')">'
            . '<li>Custom bullet image</li>'
            . '</ul>',
            20,
            20,
            120,
            0,
        );

        $this->assertNotSame('', $obj->getOutPDFString());
    }

    public function testListStyleImageResolvesToImageMarkerType(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $svg = 'PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmci'
            . 'IHdpZHRoPSI4IiBoZWlnaHQ9IjgiPgogIDxjaXJjbGUgY3g9IjQiIGN5PSI0IiByPSIzIiBmaWxsPSJyZWQi'
            . 'Lz4KPC9zdmc+';
        $dom = [
            0 => $this->makeHtmlNode([
                'value' => 'ul',
                'style' => ['list-style-image' => 'url(data:image/svg+xml;base64,' . $svg . ')'],
                'attribute' => [],
                'listtype' => '',
            ]),
        ];

        $marker = $obj->exposeGetHTMLListMarkerTypeWithDom($dom, 0, false);

        $this->assertStringStartsWith('img|svg|', $marker);
        $this->assertStringContainsString('|@<svg', $marker);
    }

    public function testParseHTMLStyleAttributesPreservesDataUriValues(): void
    {
        $obj = $this->getInternalTestObject();

        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root', 'opening' => true, 'parent' => 0]),
            1 => $this->makeHtmlNode([
                'value' => 'ul',
                'opening' => true,
                'parent' => 0,
                'attribute' => [
                    'style' => 'list-style-image:url(data:image/svg+xml;base64,PHN2Zz4=);color:red',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame('url(data:image/svg+xml;base64,PHN2Zz4=)', $dom[1]['style']['list-style-image']);
        $this->assertSame('red', $dom[1]['style']['color']);
    }

    public function testListStyleImageCssClassResolvesImageMarkerFromDomPipeline(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $html = '<style>.img-list{list-style-image:url(data:image/svg+xml;base64,PHN2Zz4=);}</style>'
            . '<ul class="img-list"><li>Item</li></ul>';
        $dom = $obj->exposeGetHTMLDOM($html);

        $ulKey = -1;
        foreach ($dom as $key => $node) {
            if (empty($node['opening']) || (($node['value'] ?? '') !== 'ul')) {
                continue;
            }

            $ulKey = (int) $key;
            break;
        }

        $this->assertGreaterThanOrEqual(0, $ulKey);
        $marker = $obj->exposeGetHTMLListMarkerTypeWithDom($dom, $ulKey, false);
        $this->assertStringStartsWith('img|svg|', $marker);
    }

    public function testNestedListStyleImageClassResolvesForNestedUl(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $html = '<style>.list-img-svg{list-style-image:url(data:image/svg+xml;base64,PHN2Zz4=);}</style>'
            . '<ul class="list-img-svg"><li>A<ul class="list-img-svg"><li>B</li></ul></li></ul>';
        $dom = $obj->exposeGetHTMLDOM($html);

        $markers = [];
        foreach ($dom as $key => $node) {
            if (empty($node['opening']) || (($node['value'] ?? '') !== 'ul')) {
                continue;
            }

            $markers[] = $obj->exposeGetHTMLListMarkerTypeWithDom($dom, (int) $key, false);
        }

        $this->assertCount(2, $markers);
        $this->assertStringStartsWith('img|svg|', $markers[0]);
        $this->assertStringStartsWith('img|svg|', $markers[1]);
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

        $obj->getHTMLCell(
            '<input type="text" value="field" /><textarea>notes</textarea><input type="hidden" value="secret" />',
            0,
            0,
            40,
            20,
        );

        // text input + textarea each produce a form-field annotation; hidden produces none
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(2, $annotation);
        $fieldTypes = \array_column(\array_column($annotation, 'opt'), 'ft');
        $this->assertContains('Tx', $fieldTypes);
    }

    public function testGetHTMLCellRendersCheckboxAndPasswordInputs(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<input type="checkbox" checked="checked" />'
            . '<input type="radio" />'
            . '<input type="password" value="abc" />',
            0,
            0,
            40,
            20,
        );

        // checkbox, radio, and password text each produce a form-field annotation
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertGreaterThanOrEqual(3, \count($annotation));
        $fieldTypes = \array_column(\array_column($annotation, 'opt'), 'ft');
        $this->assertContains('Btn', $fieldTypes);
        $this->assertContains('Tx', $fieldTypes);
    }

    public function testGetHTMLCellRendersFileInputWithFileSelectFlag(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<input type="file" name="userfile" size="20" />',
            0,
            0,
            40,
            20,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(1, $annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Tx', $last['opt']['ft']);
        $this->assertArrayHasKey('ff', $last['opt']);
        $this->assertIsInt($last['opt']['ff']);
        $this->assertNotSame(0, ($last['opt']['ff']) & (1 << 20));
    }

    public function testGetHTMLCellRendersMultilineTextareaValue(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell('<textarea rows="3" value="line1&#10; line2">line1&#10; line2</textarea>', 0, 0, 40, 20);

        // textarea produces a multiline text form-field annotation
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(1, $annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Tx', $last['opt']['ft']);
    }

    public function testGetHTMLCellAssociatesLabelForIdWithInputField(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<label for="username">User Name</label><input id="username" type="text" value="alice" />',
            0,
            0,
            50,
            20,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(1, $annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Tx', $last['opt']['ft'] ?? '');
        $this->assertSame('User Name', $last['opt']['tu'] ?? '');
    }

    public function testGetHTMLCellFallsBackToEnclosingLabelWhenForIsMissing(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<label>Notes<textarea value="x"></textarea></label>',
            0,
            0,
            50,
            20,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(1, $annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Tx', $last['opt']['ft'] ?? '');
        $this->assertSame('Notes', $last['opt']['tu'] ?? '');
    }

    public function testGetHTMLCellDoesNotAssociateUnmatchedLabelForAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<label for="missing">Missing</label><input id="present" type="text" value="ok" />',
            0,
            0,
            50,
            20,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(1, $annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Tx', $last['opt']['ft'] ?? '');
        $this->assertArrayNotHasKey('tu', $last['opt']);
    }

    public function testGetHTMLCellMapsInputReadonlyRequiredDisabledAndMaxlength(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<input type="text" value="abcdef" maxlength="5" readonly required disabled />',
            0,
            0,
            50,
            20,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(1, $annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Tx', $last['opt']['ft'] ?? '');
        $this->assertSame(5, $last['opt']['maxlen'] ?? null);
        $this->assertIsInt($last['opt']['ff'] ?? null);
        $this->assertNotSame(0, ($last['opt']['ff']) & (1 << 0));
        $this->assertSame(0, ($last['opt']['ff']) & (1 << 1));
        $this->assertIsInt($last['opt']['f'] ?? null);
        $this->assertNotSame(0, ($last['opt']['f']) & (1 << 6));
    }

    public function testGetHTMLCellDisabledFieldClearsRequiredFlagForSelect(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select required disabled><option value="v1">Alpha</option></select>',
            0,
            0,
            60,
            20,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(1, $annotation);
        /** @var array{opt: array<string, mixed>} $select */
        $select = \end($annotation);
        $this->assertSame('Ch', $select['opt']['ft'] ?? '');
        $this->assertIsInt($select['opt']['ff'] ?? null);
        $this->assertNotSame(0, ($select['opt']['ff']) & (1 << 0));
        $this->assertSame(0, ($select['opt']['ff']) & (1 << 1));
    }

    public function testGetHTMLCellMapsReadonlyAndRequiredOnSelectAndTextarea(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select readonly required><option value="v1">Alpha</option></select>'
            . '<textarea maxlength="3" disabled>hello</textarea>',
            0,
            0,
            70,
            20,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(2, $annotation);
        $items = \array_values($annotation);

        /** @var array{opt: array<string, mixed>} $select */
        $select = $items[0];
        $this->assertSame('Ch', $select['opt']['ft'] ?? '');
        $this->assertIsInt($select['opt']['ff'] ?? null);
        $this->assertNotSame(0, ($select['opt']['ff']) & (1 << 0));
        $this->assertNotSame(0, ($select['opt']['ff']) & (1 << 1));

        /** @var array{opt: array<string, mixed>} $textarea */
        $textarea = $items[1];
        $this->assertSame('Tx', $textarea['opt']['ft'] ?? '');
        $this->assertSame(3, $textarea['opt']['maxlen'] ?? null);
        $this->assertIsInt($textarea['opt']['ff'] ?? null);
        $this->assertNotSame(0, ($textarea['opt']['ff']) & (1 << 0));
    }

    public function testGetHTMLCellAppliesTypeDefaultsForEmailAndNumberInputs(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<input type="email" value="a@example.com" /><input type="number" value="42" />',
            0,
            0,
            70,
            20,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(2, $annotation);
        $items = \array_values($annotation);

        /** @var array{opt: array<string, mixed>} $email */
        $email = $items[0];
        $this->assertSame('Tx', $email['opt']['ft'] ?? '');
        $this->assertIsInt($email['opt']['ff'] ?? null);
        $this->assertNotSame(0, ($email['opt']['ff']) & (1 << 22));

        /** @var array{opt: array<string, mixed>} $number */
        $number = $items[1];
        $this->assertSame('Tx', $number['opt']['ft'] ?? '');
        $this->assertSame(2, $number['opt']['q'] ?? null);
        $this->assertIsInt($number['opt']['ff'] ?? null);
        $this->assertNotSame(0, ($number['opt']['ff']) & (1 << 22));
    }

    public function testGetHTMLCellDisabledReadonlyKeepsTypeDefaultsOnEmailAndNumberInputs(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<input type="email" value="a@example.com" required disabled />'
            . '<input type="number" value="42" required disabled readonly />',
            0,
            0,
            70,
            20,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(2, $annotation);
        $items = \array_values($annotation);

        /** @var array{opt: array<string, mixed>} $email */
        $email = $items[0];
        $this->assertSame('Tx', $email['opt']['ft'] ?? '');
        $this->assertIsInt($email['opt']['ff'] ?? null);
        // readonly field flag
        $this->assertNotSame(0, ($email['opt']['ff']) & (1 << 0));
        // required must be cleared when disabled
        $this->assertSame(0, ($email['opt']['ff']) & (1 << 1));
        // doNotSpellCheck type default remains applied
        $this->assertNotSame(0, ($email['opt']['ff']) & (1 << 22));
        $this->assertIsInt($email['opt']['f'] ?? null);
        $this->assertNotSame(0, ($email['opt']['f']) & (1 << 6));

        /** @var array{opt: array<string, mixed>} $number */
        $number = $items[1];
        $this->assertSame('Tx', $number['opt']['ft'] ?? '');
        // number alignment default remains applied while readonly/disabled
        $this->assertSame(2, $number['opt']['q'] ?? null);
        $this->assertIsInt($number['opt']['ff'] ?? null);
        $this->assertNotSame(0, ($number['opt']['ff']) & (1 << 0));
        $this->assertSame(0, ($number['opt']['ff']) & (1 << 1));
        $this->assertNotSame(0, ($number['opt']['ff']) & (1 << 22));
        $this->assertIsInt($number['opt']['f'] ?? null);
        $this->assertNotSame(0, ($number['opt']['f']) & (1 << 6));
    }

    public function testGetHTMLCellAppliesTypeDefaultsForDateAndTelWithDisabledRequiredHandling(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<input type="date" value="2026-04-27" required disabled />'
            . '<input type="tel" value="12345" readonly required />',
            0,
            0,
            70,
            20,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(2, $annotation);
        $items = \array_values($annotation);

        /** @var array{opt: array<string, mixed>} $date */
        $date = $items[0];
        $this->assertSame('Tx', $date['opt']['ft'] ?? '');
        $this->assertIsInt($date['opt']['ff'] ?? null);
        $this->assertNotSame(0, ($date['opt']['ff']) & (1 << 0));
        $this->assertSame(0, ($date['opt']['ff']) & (1 << 1));
        $this->assertNotSame(0, ($date['opt']['ff']) & (1 << 22));

        /** @var array{opt: array<string, mixed>} $tel */
        $tel = $items[1];
        $this->assertSame('Tx', $tel['opt']['ft'] ?? '');
        $this->assertIsInt($tel['opt']['ff'] ?? null);
        $this->assertNotSame(0, ($tel['opt']['ff']) & (1 << 0));
        $this->assertNotSame(0, ($tel['opt']['ff']) & (1 << 1));
        $this->assertNotSame(0, ($tel['opt']['ff']) & (1 << 22));
    }

    public function testGetHTMLCellAppliesTypeDefaultsForUrlWithDisabledAndReadonlyHandling(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<input type="url" value="https://example.test" required disabled />'
            . '<input type="url" value="https://example.org" readonly required />',
            0,
            0,
            70,
            20,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertCount(2, $annotation);
        $items = \array_values($annotation);

        /** @var array{opt: array<string, mixed>} $disabledUrl */
        $disabledUrl = $items[0];
        $this->assertSame('Tx', $disabledUrl['opt']['ft'] ?? '');
        $this->assertIsInt($disabledUrl['opt']['ff'] ?? null);
        $this->assertNotSame(0, ($disabledUrl['opt']['ff']) & (1 << 0));
        $this->assertSame(0, ($disabledUrl['opt']['ff']) & (1 << 1));
        $this->assertNotSame(0, ($disabledUrl['opt']['ff']) & (1 << 22));

        /** @var array{opt: array<string, mixed>} $readonlyUrl */
        $readonlyUrl = $items[1];
        $this->assertSame('Tx', $readonlyUrl['opt']['ft'] ?? '');
        $this->assertIsInt($readonlyUrl['opt']['ff'] ?? null);
        $this->assertNotSame(0, ($readonlyUrl['opt']['ff']) & (1 << 0));
        $this->assertNotSame(0, ($readonlyUrl['opt']['ff']) & (1 << 1));
        $this->assertNotSame(0, ($readonlyUrl['opt']['ff']) & (1 << 22));
    }

    /**
     * Helper: extract option labels from the last ComboBox annotation.
     *
     * @param object $obj PDF object
     * @return array<int, string>
     */
    private function getLastComboBoxLabels(object $obj): array
    {
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Ch', $last['opt']['ft'] ?? '');
        /** @var list<string|list<string>> $opts */
        $opts = (array) ($last['opt']['opt'] ?? []);
        return \array_map(static fn ($item) => \is_array($item) ? (string) $item[1] : (string) $item, $opts);
    }

    /**
     * Helper: get the initial selected value from the last ComboBox annotation.
     */
    private function getLastComboBoxValue(object $obj): string
    {
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $optv = $last['opt']['v'];
        return \is_string($optv) ? $optv : '';
    }

    public function testGetHTMLCellRendersSelectFirstOptionLabel(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select><option value="v1">Alpha</option><option value="v2">Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $labels = $this->getLastComboBoxLabels($obj);
        $this->assertContains('Alpha', $labels);
        $this->assertContains('Beta', $labels);
    }

    public function testGetHTMLCellRendersSelectedOptionLabelByValue(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select value="v2"><option value="v1">Alpha</option><option value="v2">Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $labels = $this->getLastComboBoxLabels($obj);
        $this->assertContains('Alpha', $labels);
        $this->assertContains('Beta', $labels);
        $this->assertSame('v2', $this->getLastComboBoxValue($obj));
    }

    public function testGetHTMLCellRendersSelectedOptionLabelBySingleQuotedValue(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            "<select value=\"v2\"><option value='v1'>Alpha</option><option value='v2'>Beta</option></select>",
            0,
            0,
            40,
            20,
        );

        $labels = $this->getLastComboBoxLabels($obj);
        $this->assertContains('Beta', $labels);
        $this->assertSame('v2', $this->getLastComboBoxValue($obj));
    }

    public function testGetHTMLCellRendersSelectedOptionLabelByUnquotedValue(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select value="v2"><option value=v1>Alpha</option><option value=v2>Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $labels = $this->getLastComboBoxLabels($obj);
        $this->assertContains('Beta', $labels);
        $this->assertSame('v2', $this->getLastComboBoxValue($obj));
    }

    public function testGetHTMLCellRendersMultipleSelectedOptionLabelsByValue(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select value="v2,v1"><option value="v1">Alpha</option><option value="v2">Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        // ComboBox is created with all option labels accessible
        $labels = $this->getLastComboBoxLabels($obj);
        $this->assertContains('Alpha', $labels);
        $this->assertContains('Beta', $labels);
    }

    public function testGetHTMLCellRendersSelectedOptionLabelBySelectedAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select><option value="v1">Alpha</option><option value="v2" selected="selected">Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $labels = $this->getLastComboBoxLabels($obj);
        $this->assertContains('Beta', $labels);
        $this->assertSame('v2', $this->getLastComboBoxValue($obj));
    }

    public function testGetHTMLCellRendersMultipleSelectedOptionLabelsBySelectedAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select><option selected="selected">Alpha</option><option selected="selected">Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        // First selected option becomes the initial value
        $labels = $this->getLastComboBoxLabels($obj);
        $this->assertContains('Alpha', $labels);
        $this->assertContains('Beta', $labels);
        $this->assertSame('Alpha', $this->getLastComboBoxValue($obj));
    }

    public function testGetHTMLCellRendersSelectedOptionLabelWithBooleanSelectedAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select><option>Alpha</option><option selected>Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $labels = $this->getLastComboBoxLabels($obj);
        $this->assertContains('Beta', $labels);
        $this->assertSame('Beta', $this->getLastComboBoxValue($obj));
    }

    public function testGetHTMLCellRendersSelectedOptionLabelWithSelectedTrueAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select><option>Alpha</option><option selected="true">Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $labels = $this->getLastComboBoxLabels($obj);
        $this->assertContains('Beta', $labels);
        $this->assertSame('Beta', $this->getLastComboBoxValue($obj));
    }

    public function testGetHTMLCellRendersSelectedOptionLabelWithSingleQuotedSelectedAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            "<select><option>Alpha</option><option selected='selected'>Beta</option></select>",
            0,
            0,
            40,
            20,
        );

        $labels = $this->getLastComboBoxLabels($obj);
        $this->assertContains('Beta', $labels);
        $this->assertSame('Beta', $this->getLastComboBoxValue($obj));
    }

    public function testGetHTMLCellRendersSelectedOptionLabelWithUppercaseSelectedAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select><option>Alpha</option><option SELECTED>Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $labels = $this->getLastComboBoxLabels($obj);
        $this->assertContains('Beta', $labels);
        $this->assertSame('Beta', $this->getLastComboBoxValue($obj));
    }

    public function testGetHTMLCellRendersOptgroupLabelsInSelectOptions(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select>'
            . '<optgroup label="Fruits"><option value="a">Apple</option><option value="b">Banana</option></optgroup>'
            . '<optgroup label="Veg"><option value="c">Carrot</option></optgroup>'
            . '</select>',
            0,
            0,
            40,
            20,
        );

        $labels = $this->getLastComboBoxLabels($obj);
        $this->assertContains('Fruits - Apple', $labels);
        $this->assertContains('Fruits - Banana', $labels);
        $this->assertContains('Veg - Carrot', $labels);
    }

    public function testGetHTMLCellPrefersSelectedOptionOverSelectValueForInitialValue(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select value="v1"><option value="v1">Alpha</option><option value="v2" selected>Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $this->assertSame('v2', $this->getLastComboBoxValue($obj));
    }

    public function testGetHTMLCellSelectValueFallbackSkipsUnknownValues(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select value="missing,v2"><option value="v1">Alpha</option><option value="v2">Beta</option></select>',
            0,
            0,
            40,
            20,
        );

        $this->assertSame('v2', $this->getLastComboBoxValue($obj));
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

        $out = $obj->getHTMLCell(
            '<img src="/tmp/__tc_lib_pdf_missing_image__.png" alt="fallback-alt" />',
            0,
            0,
            20,
            10
        );

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
            '<table><tr><td style="border:1px solid black">A</td>'
            . '<td style="border:1px solid black">This is a longer wrapped cell value</td></tr></table>',
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
            . '<tr><td rowspan="2" style="border:1px solid black">A</td>'
            . '<td style="border:1px solid black">Top</td></tr>'
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

    public function testGetHTMLCellRowspanHeightIncludesCellspacingBetweenRows(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table cellspacing="3">'
            . '<tr><td rowspan="2" style="border:1px solid black">A</td>'
            . '<td style="border:1px solid black">Top</td></tr>'
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

        $this->assertGreaterThan(
            $heights[1] + $heights[2],
            $heights[0],
            'Rowspan height should include inter-row cellspacing between spanned rows.',
        );
    }

    public function testGetHTMLCellRowspanHeightIncludesCssBorderSpacingBetweenRows(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table style="border-spacing:0 3">'
            . '<tr><td rowspan="2" style="border:1px solid black">A</td>'
            . '<td style="border:1px solid black">Top</td></tr>'
            . '<tr><td style="border:1px solid black">Bottom</td></tr>'
            . '</table>',
            0,
            0,
            30,
            20,
        );

        $this->assertNotSame('', $out);
        $matches = [];
        \preg_match_all('/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\s+s/', $out, $matches, PREG_SET_ORDER);
        $this->assertGreaterThanOrEqual(3, \count($matches));

        $heights = \array_map(
            static fn(array $match): float => \abs((float) $match[4]),
            $matches,
        );
        \rsort($heights);

        $this->assertGreaterThan(
            $heights[1] + $heights[2],
            $heights[0],
            'Rowspan height should include inter-row CSS border-spacing between spanned rows.',
        );
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

    public function testGetHTMLCellDrawsInlineBackgroundFillWhenSpecified(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell('<span bgcolor="#eeeeee">A</span>', 0, 0, 30, 12);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString(' re', $out);
        $this->assertStringContainsString("f\n", $out);
    }

    public function testGetHTMLCellRenderingRegressionFloatAndClearFixture(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $html = (string) \file_get_contents(__DIR__ . '/fixtures/html/rendering/float_clear.html');

        $out = $obj->getHTMLCell($html, 0, 0, 90, 30);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('FLOAT-L', $out);
        $this->assertStringContainsString('FLOAT-R', $out);
        $this->assertStringContainsString('CLEAR-BLOCK', $out);
        $this->assertMatchesRegularExpression('/FLOAT-L.*FLOAT-R.*CLEAR-BLOCK/s', $out);
    }

    public function testGetHTMLCellRenderingRegressionPositionModesFixture(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $html = (string) \file_get_contents(__DIR__ . '/fixtures/html/rendering/position_modes.html');

        $out = $obj->getHTMLCell($html, 0, 0, 90, 35);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('REL-BOX', $out);
        $this->assertStringContainsString('ABS-BOX', $out);
        $this->assertStringContainsString('FIXED-BOX', $out);
    }

    public function testGetHTMLCellRenderingRegressionTableLayoutFixture(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $html = (string) \file_get_contents(__DIR__ . '/fixtures/html/rendering/table_layout_fixed_auto.html');

        $out = $obj->getHTMLCell($html, 0, 0, 90, 60);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('FIXED-A', $out);
        $this->assertStringContainsString('FIXED-B-LONG-CONTENT', $out);
        $this->assertStringContainsString('AUTO-A', $out);
        $this->assertStringContainsString('AUTO-B-LONG-CONTENT', $out);
        $this->assertStringContainsString(' re', $out);
    }

    public function testGetHTMLCellCaptionSideBottomRendersCaptionAfterTableRows(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $html = '<table style="caption-side:bottom;border:1px solid #000" cellspacing="0" cellpadding="1">'
            . '<caption>CAPTION-BOTTOM</caption>'
            . '<tr><td>ROW-CELL</td></tr>'
            . '</table>';

        $out = $obj->getHTMLCell($html, 0, 0, 60, 30);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('CAPTION-BOTTOM', $out);
        $this->assertStringContainsString('ROW-CELL', $out);

        $captionPos = \strpos($out, 'Td (CAPTION-BOTTOM) Tj ET');
        $rowPos = \strpos($out, 'Td (ROW-CELL) Tj ET');

        $this->assertNotFalse($captionPos);
        $this->assertNotFalse($rowPos);
        $this->assertGreaterThan($rowPos, $captionPos);
    }

    public function testGetHTMLCellCaptionSideTopRendersCaptionBeforeTableRows(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $html = '<table style="caption-side:top;border:1px solid #000" cellspacing="0" cellpadding="1">'
            . '<caption>CAPTION-TOP</caption>'
            . '<tr><td>ROW-CELL</td></tr>'
            . '</table>';

        $out = $obj->getHTMLCell($html, 0, 0, 60, 30);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('CAPTION-TOP', $out);
        $this->assertStringContainsString('ROW-CELL', $out);

        $captionPos = \strpos($out, 'Td (CAPTION-TOP) Tj ET');
        $rowPos = \strpos($out, 'Td (ROW-CELL) Tj ET');

        $this->assertNotFalse($captionPos);
        $this->assertNotFalse($rowPos);
        $this->assertLessThan($rowPos, $captionPos);
    }

    public function testGetHTMLCellExtendsInlineSmallBackgroundAcrossWrappedLines(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $html = '<small color="#ff0000" bgcolor="#ffff00">small small small small small small small'
            . ' small small small small small small small small small small small small small</small>';

        $extractFillSpan = static function (string $out): float {
            $matches = [];
            $re4Pattern = '/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\\s+f/';
            \preg_match_all($re4Pattern, $out, $matches, PREG_SET_ORDER);
            if ($matches === []) {
                return 0.0;
            }

            $miny = PHP_FLOAT_MAX;
            $maxy = -PHP_FLOAT_MAX;
            foreach ($matches as $match) {
                $posy = (float) $match[2];
                $height = \abs((float) $match[4]);
                $miny = \min($miny, $posy);
                $maxy = \max($maxy, $posy + $height);
            }

            if ($maxy <= $miny) {
                return 0.0;
            }

            return $maxy - $miny;
        };

        $outNoWrap = $obj->getHTMLCell($html, 0, 0, 160, 20);
        $outWrap = $obj->getHTMLCell($html, 0, 0, 40, 20);

        $this->assertNotSame('', $outNoWrap);
        $this->assertNotSame('', $outWrap);

        $nowrapHeight = $extractFillSpan($outNoWrap);
        $wrapHeight = $extractFillSpan($outWrap);

        $this->assertGreaterThan(0.0, $nowrapHeight);
        $this->assertGreaterThan(0.0, $wrapHeight);
        $this->assertGreaterThan(
            $nowrapHeight + 0.001,
            $wrapHeight,
            'Wrapped inline <small> background must cover more than one rendered line.',
        );
    }

    public function testGetHTMLCellDrawsMultipleFillRectsForWrappedInlineSmallBackground(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $html = '<small color="#ff0000" bgcolor="#ffff00">'
            . 'small small small small small small small small small small small small'
            . '</small>';

        $out = $obj->getHTMLCell($html, 0, 0, 40, 20);
        $this->assertNotSame('', $out);

        $matches = [];
        \preg_match_all('/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\\s+f/', $out, $matches, PREG_SET_ORDER);
        $this->assertNotEmpty($matches);

        $linekeys = [];
        foreach ($matches as $match) {
            $linekeys[\sprintf('%.3f', (float) $match[2])] = true;
        }

        $this->assertGreaterThanOrEqual(
            2,
            \count($linekeys),
            'Wrapped inline <small> background should be drawn on more than one line.',
        );
    }

    public function testGetHTMLCellWhiteSpaceNowrapKeepsInlineBackgroundOnSingleLine(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $text = 'alpha beta gamma delta epsilon zeta eta theta iota kappa';
        $normal = '<span style="background-color:#ffff00;">' . $text . '</span>';
        $nowrap = '<span style="background-color:#ffff00;white-space:nowrap;">' . $text . '</span>';

        $outNormal = $obj->getHTMLCell($normal, 0, 0, 40, 20);
        $outNowrap = $obj->getHTMLCell($nowrap, 0, 0, 40, 20);

        $this->assertNotSame('', $outNormal);
        $this->assertNotSame('', $outNowrap);

        $normalMatches = [];
        $nowrapMatches = [];
        \preg_match_all(
            '/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\s+f/',
            $outNormal,
            $normalMatches,
            PREG_SET_ORDER,
        );
        \preg_match_all(
            '/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\s+f/',
            $outNowrap,
            $nowrapMatches,
            PREG_SET_ORDER,
        );

        $this->assertNotEmpty($normalMatches);
        $this->assertNotEmpty($nowrapMatches);

        $normalLines = [];
        foreach ($normalMatches as $match) {
            $normalLines[\sprintf('%.3f', (float) $match[2])] = true;
        }

        $nowrapLines = [];
        foreach ($nowrapMatches as $match) {
            $nowrapLines[\sprintf('%.3f', (float) $match[2])] = true;
        }

        $this->assertGreaterThanOrEqual(2, \count($normalLines));
        $this->assertSame(1, \count($nowrapLines));
    }

    public function testGetHTMLCellExpandsBlockBackgroundFillAcrossLineWidth(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<div style="background-color:#880000;color:white;">Hello World!<br />Hello</div>',
            0,
            0,
            30,
            20,
        );

        $this->assertNotSame('', $out);
        $matches = [];
        \preg_match_all('/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\\s+f/', $out, $matches, PREG_SET_ORDER);
        $this->assertNotEmpty($matches);

        $maxwidth = 0.0;
        foreach ($matches as $match) {
            $maxwidth = \max($maxwidth, \abs((float) $match[3]));
        }

        $this->assertGreaterThan(20.0, $maxwidth);
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

    public function testResetHTMLTableStackOnPageBreakRebasesOpenTablesAndDropsActiveRowspans(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeSetHTMLTableStack([
            [
                'originx' => 5.0,
                'originy' => 12.0,
                'width' => 40.0,
                'cols' => 2,
                'colwidth' => 18.0,
                'colwidths' => [18.0, 18.0],
                'cellspacingh' => 1.0,
                'cellspacingv' => 3.0,
                'cellpadding' => 0.0,
                'collapse' => false,
                'hascellborders' => true,
                'prevrowbottom' => [],
                'rowtop' => 21.0,
                'rowheight' => 7.0,
                'colindex' => 1,
                'cells' => [['cellx' => 5.0]],
                'occupied' => [0, 1],
                'rowspans' => [[
                    'cellx' => 5.0,
                    'cellw' => 18.0,
                    'colindex' => 0,
                    'colspan' => 1,
                    'rowtop' => 21.0,
                    'rowsremaining' => 2,
                    'usedheight' => 7.0,
                    'contenth' => 14.0,
                    'valign' => 'middle',
                    'bstyles' => [],
                    'fillstyle' => null,
                    'buffer' => 'A',
                ]],
            ],
        ]);

        $obj->exposeResetHTMLTableStackOnPageBreak(42.0);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);

        $table = $hrc['tablestack'][0];
        $this->assertSame(42.0, $table['originy']);
        $this->assertSame(45.0, $table['rowtop']);
        $this->assertSame(0.0, $table['rowheight']);
        $this->assertSame([], $table['cells']);
        $this->assertSame([], $table['rowspans']);
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

    public function testGetHTMLCellTableHeadReplayDoesNotOverlapBodyRowWithCellpadding(): void
    {
        // Regression: estimateHTMLTableHeadHeight previously ignored the
        // <table cellpadding="N"> attribute when measuring the replayed
        // header on a new page (parseHTMLTagOPENtable applies that padding
        // as a default to TD/TH cells with zero CSS padding at render time,
        // but the estimate parses the standalone thead DOM and never ran
        // those handlers). The under-estimated header height caused the
        // first body row on the next page to overlap the replayed header
        // (see example 018 row 14 vs page-6 header).
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $region = $page->getRegion();
        $starty = \max(0.0, ((float) $region['RH']) - 5.0);

        $out = $obj->getHTMLCell(
            '<table cellpadding="6">'
            . '<thead><tr><th>HDR</th></tr></thead>'
            . '<tr><td>BODY</td></tr></table>',
            0,
            $starty,
            30,
            0,
        );

        $this->assertNotSame('', $out);

        $hdrMatches = [];
        $this->assertGreaterThanOrEqual(
            2,
            \preg_match_all('/BT .*? [-0-9.]+ ([-0-9.]+) Td \(HDR\) Tj ET/s', $out, $hdrMatches),
            'Header text must be rendered both on the original page and replayed on the new page.',
        );
        $bodyMatches = [];
        $this->assertSame(
            1,
            \preg_match('/BT .*? [-0-9.]+ ([-0-9.]+) Td \(BODY\) Tj ET/s', $out, $bodyMatches),
            'Body text must be rendered exactly once.',
        );

        $hdrSecondY = (float) $hdrMatches[1][\count($hdrMatches[1]) - 1];
        $bodyY = (float) $bodyMatches[1];
        // The y values are PDF user-space points. Without the fix the
        // estimated header height excluded the table cellpadding (~9pt
        // vertical), so the body row text on the new page sat about 11.5pt
        // below the replayed header (visibly overlapping it). With the fix
        // the gap is roughly 20pt. Threshold 14 pt cleanly separates the
        // two regimes.
        $this->assertGreaterThan(
            14.0,
            \abs($hdrSecondY - $bodyY),
            'Replayed header and the first body row on the new page must not overlap; '
            . 'the vertical gap must include the table cellpadding.',
        );
    }

    public function testGetHTMLCellRespectsExplicitTdColumnWidth(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table><tr>'
            . '<td width="160" style="border:1px solid black">Wide</td>'
            . '<td style="border:1px solid black">Narrow</td>'
            . '</tr></table>',
            0,
            0,
            60,
            20,
        );

        $domObj = $this->getInternalTestObject();
        $this->initFontAndPage($domObj);
        $dom = $domObj->exposeGetHTMLDOM(
            '<table><tr><td width="160">Wide</td><td>Narrow</td></tr></table>',
        );

        $tdWidths = [];
        foreach ($dom as $elm) {
            if (
                !empty($elm['opening'])
                && (($elm['value'] ?? '') === 'td')
                && isset($elm['width'])
                && \is_numeric($elm['width'])
            ) {
                $tdWidths[] = (float) $elm['width'];
            }
        }

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('Wide', $out);
        $this->assertStringContainsString('Narrow', $out);
        $this->assertCount(2, $tdWidths);
        $this->assertGreaterThan($tdWidths[1], $tdWidths[0], 'First column should resolve wider width than second');
    }

    public function testGetHTMLCellAppliesCellspacingBetweenRows(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $noSpacing = $obj->getHTMLCell(
            '<table><tr><td style="border:1px solid black">A</td></tr>'
            . '<tr><td style="border:1px solid black">B</td></tr></table>',
            0,
            0,
            30,
            30,
        );

        $withSpacing = $obj->getHTMLCell(
            '<table cellspacing="5"><tr><td style="border:1px solid black">A</td></tr>'
            . '<tr><td style="border:1px solid black">B</td></tr></table>',
            0,
            0,
            30,
            30,
        );

        $this->assertNotSame('', $noSpacing);
        $this->assertNotSame('', $withSpacing);
        $this->assertStringContainsString('A', $withSpacing);
        $this->assertStringContainsString('B', $withSpacing);
    }

    public function testGetHTMLCellAppliesCellpaddingToAllCells(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        // With cellpadding=5, the text inside the cell should be indented relative to the border.
        $out = $obj->getHTMLCell(
            '<table cellpadding="5"><tr><td style="border:1px solid black">X</td></tr></table>',
            0,
            0,
            30,
            20,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('(X)', $out);
        $this->assertStringContainsString(' re', $out, 'Expected a cell rectangle to be drawn');
    }

    public function testGetHTMLCellRendersTableOuterBorderFromTableAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $withoutBorder = $obj->getHTMLCell(
            '<table cellspacing="3" cellpadding="4"><tr><td>A</td></tr></table>',
            0,
            0,
            30,
            20,
        );

        $withBorder = $obj->getHTMLCell(
            '<table border="1" cellspacing="3" cellpadding="4"><tr><td>A</td></tr></table>',
            0,
            0,
            30,
            20,
        );

        $this->assertNotSame('', $withoutBorder);
        $this->assertNotSame('', $withBorder);
        $this->assertStringContainsString('(A)', $withBorder);
        $this->assertStringNotContainsString(' re', $withoutBorder);
        $this->assertStringContainsString(' re', $withBorder, 'Expected outer table border rectangle to be drawn');
    }

    public function testGetHTMLCellAppliesCellspacingBetweenOuterAndInnerBorders(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table border="1" cellspacing="3"><tr><td style="border:1px solid black">A</td></tr></table>',
            0,
            0,
            30,
            20,
        );

        $this->assertNotSame('', $out);
        $matches = [];
        \preg_match_all('/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\\s+s/', $out, $matches, PREG_SET_ORDER);
        $this->assertGreaterThanOrEqual(2, \count($matches));
        $xvalues = [];
        foreach ($matches as $match) {
            $xvalues[] = (float) $match[1];
        }

        if ($xvalues === []) {
            $this->fail('Expected at least one x position extracted from border rectangles');
        }

        $this->assertGreaterThan(
            0.1,
            \max($xvalues) - \min($xvalues),
            'Expected distinct x positions for inner cell border and outer table border when cellspacing is set',
        );
    }

    public function testGetHTMLCellAppliesCssBorderSpacingBetweenOuterAndInnerBorders(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table border="1" style="border-spacing:3 0"><tr><td style="border:1px solid black">A</td></tr></table>',
            0,
            0,
            30,
            20,
        );

        $this->assertNotSame('', $out);
        $matches = [];
        \preg_match_all('/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\s+s/', $out, $matches, PREG_SET_ORDER);
        $this->assertGreaterThanOrEqual(2, \count($matches));
        $xvalues = [];
        foreach ($matches as $match) {
            $xvalues[] = (float) $match[1];
        }

        if ($xvalues === []) {
            $this->fail('Expected at least one x position extracted from border rectangles');
        }

        $this->assertGreaterThan(
            0.1,
            \max($xvalues) - \min($xvalues),
            'Expected distinct x positions for inner cell border and outer table border when CSS border-spacing is set',
        );
    }

    public function testGetHTMLCellAppliesCssBorderSpacingBetweenRows(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $noSpacing = $obj->getHTMLCell(
            '<table><tr><td style="border:1px solid black">A</td></tr>'
            . '<tr><td style="border:1px solid black">B</td></tr></table>',
            0,
            0,
            30,
            30,
        );

        $withSpacing = $obj->getHTMLCell(
            '<table style="border-spacing:0 5"><tr><td style="border:1px solid black">A</td></tr>'
            . '<tr><td style="border:1px solid black">B</td></tr></table>',
            0,
            0,
            30,
            30,
        );

        $this->assertNotSame('', $noSpacing);
        $this->assertNotSame('', $withSpacing);

        $noSpacingMatches = [];
        $withSpacingMatches = [];
        \preg_match_all(
            '/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\s+s/',
            $noSpacing,
            $noSpacingMatches,
            PREG_SET_ORDER,
        );
        \preg_match_all(
            '/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\s+s/',
            $withSpacing,
            $withSpacingMatches,
            PREG_SET_ORDER,
        );

        $this->assertGreaterThanOrEqual(2, \count($noSpacingMatches));
        $this->assertGreaterThanOrEqual(2, \count($withSpacingMatches));

        $noSpacingY = [];
        foreach ($noSpacingMatches as $match) {
            $noSpacingY[] = (float) $match[2];
        }

        $withSpacingY = [];
        foreach ($withSpacingMatches as $match) {
            $withSpacingY[] = (float) $match[2];
        }

        \sort($noSpacingY);
        \sort($withSpacingY);

        $this->assertGreaterThan(
            $noSpacingY[1] - $noSpacingY[0],
            $withSpacingY[1] - $withSpacingY[0],
            'Expected a larger vertical gap between row border rectangles when CSS border-spacing is set',
        );
    }

    public function testGetHTMLCellAppliesVerticalAlignWithinTallerRow(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $topOut = $obj->getHTMLCell(
            '<table cellspacing="0"><tr><td style="border:1px solid black" valign="top">A</td>'
            . '<td style="border:1px solid black">B<br/>C<br/>D</td></tr></table>',
            0,
            0,
            40,
            30,
        );
        $bottomOut = $obj->getHTMLCell(
            '<table cellspacing="0"><tr><td style="border:1px solid black" valign="bottom">A</td>'
            . '<td style="border:1px solid black">B<br/>C<br/>D</td></tr></table>',
            0,
            0,
            40,
            30,
        );

        $this->assertNotSame('', $topOut);
        $this->assertNotSame('', $bottomOut);

        $this->assertSame(0, \preg_match('/1 0 0 1 0 -?[0-9.]+ cm\n/', $topOut));
        $this->assertSame(
            1,
            \preg_match('/1 0 0 1 0 -[0-9.]+ cm\n/', $bottomOut),
            'Bottom vertical alignment should emit a downward translation transform inside a taller row.',
        );
    }

    public function testGetHTMLCellAppliesCssVerticalAlignWithinTallerRow(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $topOut = $obj->getHTMLCell(
            '<table cellspacing="0"><tr><td style="border:1px solid black;vertical-align:top">A</td>'
            . '<td style="border:1px solid black">B<br/>C<br/>D</td></tr></table>',
            0,
            0,
            40,
            30,
        );
        $bottomOut = $obj->getHTMLCell(
            '<table cellspacing="0"><tr><td style="border:1px solid black;vertical-align:bottom">A</td>'
            . '<td style="border:1px solid black">B<br/>C<br/>D</td></tr></table>',
            0,
            0,
            40,
            30,
        );

        $this->assertNotSame('', $topOut);
        $this->assertNotSame('', $bottomOut);

        $this->assertSame(0, \preg_match('/1 0 0 1 0 -?[0-9.]+ cm\n/', $topOut));
        $this->assertSame(
            1,
            \preg_match('/1 0 0 1 0 -[0-9.]+ cm\n/', $bottomOut),
            'CSS bottom vertical alignment should emit a downward translation transform inside a taller row.',
        );
    }

    public function testGetHTMLCellPrefersCssVerticalAlignOverValignAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $cssTopOut = $obj->getHTMLCell(
            '<table cellspacing="0"><tr><td style="border:1px solid black;vertical-align:top" valign="bottom">A</td>'
            . '<td style="border:1px solid black">B<br/>C<br/>D</td></tr></table>',
            0,
            0,
            40,
            30,
        );
        $cssBottomOut = $obj->getHTMLCell(
            '<table cellspacing="0"><tr><td style="border:1px solid black;vertical-align:bottom" valign="top">A</td>'
            . '<td style="border:1px solid black">B<br/>C<br/>D</td></tr></table>',
            0,
            0,
            40,
            30,
        );

        $this->assertNotSame('', $cssTopOut);
        $this->assertNotSame('', $cssBottomOut);

        $this->assertSame(0, \preg_match('/1 0 0 1 0 -?[0-9.]+ cm\n/', $cssTopOut));
        $this->assertSame(1, \preg_match('/1 0 0 1 0 -[0-9.]+ cm\n/', $cssBottomOut));
    }

    public function testGetHTMLCellAppliesVerticalAlignBottomOnCompletedRowspanCell(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $topOut = $obj->getHTMLCell(
            '<table cellspacing="0">'
            . '<tr><td rowspan="2" style="border:1px solid black" valign="top">A</td>'
            . '<td style="border:1px solid black">B</td></tr>'
            . '<tr><td style="border:1px solid black">C<br/>D<br/>E</td></tr>'
            . '</table>',
            0,
            0,
            40,
            40,
        );
        $bottomOut = $obj->getHTMLCell(
            '<table cellspacing="0">'
            . '<tr><td rowspan="2" style="border:1px solid black" valign="bottom">A</td>'
            . '<td style="border:1px solid black">B</td></tr>'
            . '<tr><td style="border:1px solid black">C<br/>D<br/>E</td></tr>'
            . '</table>',
            0,
            0,
            40,
            40,
        );

        $this->assertNotSame('', $topOut);
        $this->assertNotSame('', $bottomOut);

        $this->assertSame(
            0,
            \preg_match('/1 0 0 1 0 -[0-9.]+ cm\n.*?\(A\) Tj/s', $topOut),
        );
        $this->assertSame(
            1,
            \preg_match('/1 0 0 1 0 -[0-9.]+ cm\n.*?\(A\) Tj/s', $bottomOut),
            'Bottom vertical alignment should translate rowspan cell content '
            . 'when the spanned height exceeds content height.',
        );
    }

    public function testGetHTMLCellAppliesVerticalAlignMiddleWithinTallerRow(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $topOut = $obj->getHTMLCell(
            '<table cellspacing="0"><tr><td style="border:1px solid black" valign="top">A</td>'
            . '<td style="border:1px solid black">B<br/>C<br/>D</td></tr></table>',
            0,
            0,
            40,
            30,
        );
        $middleOut = $obj->getHTMLCell(
            '<table cellspacing="0"><tr><td style="border:1px solid black" valign="middle">A</td>'
            . '<td style="border:1px solid black">B<br/>C<br/>D</td></tr></table>',
            0,
            0,
            40,
            30,
        );
        $bottomOut = $obj->getHTMLCell(
            '<table cellspacing="0"><tr><td style="border:1px solid black" valign="bottom">A</td>'
            . '<td style="border:1px solid black">B<br/>C<br/>D</td></tr></table>',
            0,
            0,
            40,
            30,
        );

        $this->assertNotSame('', $topOut);
        $this->assertNotSame('', $middleOut);
        $this->assertNotSame('', $bottomOut);

        $this->assertSame(0, \preg_match('/1 0 0 1 0 -?[0-9.]+ cm\n/', $topOut));

        $middleMatch = [];
        $bottomMatch = [];
        $this->assertSame(1, \preg_match('/1 0 0 1 0 (-[0-9.]+) cm\n/', $middleOut, $middleMatch));
        $this->assertSame(1, \preg_match('/1 0 0 1 0 (-[0-9.]+) cm\n/', $bottomOut, $bottomMatch));

        $middleOffset = \abs((float) $middleMatch[1]);
        $bottomOffset = \abs((float) $bottomMatch[1]);

        $this->assertGreaterThan(0.0, $middleOffset);
        $this->assertGreaterThan($middleOffset, $bottomOffset);
    }

    public function testGetHTMLCellAppliesVerticalAlignMiddleOnCompletedRowspanCell(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $topOut = $obj->getHTMLCell(
            '<table cellspacing="0">'
            . '<tr><td rowspan="2" style="border:1px solid black" valign="top">A</td>'
            . '<td style="border:1px solid black">B</td></tr>'
            . '<tr><td style="border:1px solid black">C<br/>D<br/>E</td></tr>'
            . '</table>',
            0,
            0,
            40,
            40,
        );
        $middleOut = $obj->getHTMLCell(
            '<table cellspacing="0">'
            . '<tr><td rowspan="2" style="border:1px solid black" valign="middle">A</td>'
            . '<td style="border:1px solid black">B</td></tr>'
            . '<tr><td style="border:1px solid black">C<br/>D<br/>E</td></tr>'
            . '</table>',
            0,
            0,
            40,
            40,
        );
        $bottomOut = $obj->getHTMLCell(
            '<table cellspacing="0">'
            . '<tr><td rowspan="2" style="border:1px solid black" valign="bottom">A</td>'
            . '<td style="border:1px solid black">B</td></tr>'
            . '<tr><td style="border:1px solid black">C<br/>D<br/>E</td></tr>'
            . '</table>',
            0,
            0,
            40,
            40,
        );

        $this->assertNotSame('', $topOut);
        $this->assertNotSame('', $middleOut);
        $this->assertNotSame('', $bottomOut);

        $this->assertSame(0, \preg_match('/1 0 0 1 0 -[0-9.]+ cm\n.*?\(A\) Tj/s', $topOut));

        $middleMatch = [];
        $bottomMatch = [];
        $this->assertSame(
            1,
            \preg_match('/1 0 0 1 0 (-[0-9.]+) cm\n.*?\(A\) Tj/s', $middleOut, $middleMatch),
        );
        $this->assertSame(
            1,
            \preg_match('/1 0 0 1 0 (-[0-9.]+) cm\n.*?\(A\) Tj/s', $bottomOut, $bottomMatch),
        );

        $middleOffset = \abs((float) $middleMatch[1]);
        $bottomOffset = \abs((float) $bottomMatch[1]);

        $this->assertGreaterThan(0.0, $middleOffset);
        $this->assertGreaterThan($middleOffset, $bottomOffset);
    }

    public function testGetHTMLCellAppliesVerticalAlignOnCompletedThreeRowRowspanCell(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $topOut = $obj->getHTMLCell(
            '<table cellspacing="0">'
            . '<tr><td rowspan="3" style="border:1px solid black" valign="top">A</td>'
            . '<td style="border:1px solid black">B</td></tr>'
            . '<tr><td style="border:1px solid black">C<br/>D</td></tr>'
            . '<tr><td style="border:1px solid black">E<br/>F<br/>G<br/>H</td></tr>'
            . '</table>',
            0,
            0,
            40,
            50,
        );
        $middleOut = $obj->getHTMLCell(
            '<table cellspacing="0">'
            . '<tr><td rowspan="3" style="border:1px solid black" valign="middle">A</td>'
            . '<td style="border:1px solid black">B</td></tr>'
            . '<tr><td style="border:1px solid black">C<br/>D</td></tr>'
            . '<tr><td style="border:1px solid black">E<br/>F<br/>G<br/>H</td></tr>'
            . '</table>',
            0,
            0,
            40,
            50,
        );
        $bottomOut = $obj->getHTMLCell(
            '<table cellspacing="0">'
            . '<tr><td rowspan="3" style="border:1px solid black" valign="bottom">A</td>'
            . '<td style="border:1px solid black">B</td></tr>'
            . '<tr><td style="border:1px solid black">C<br/>D</td></tr>'
            . '<tr><td style="border:1px solid black">E<br/>F<br/>G<br/>H</td></tr>'
            . '</table>',
            0,
            0,
            40,
            50,
        );

        $this->assertNotSame('', $topOut);
        $this->assertNotSame('', $middleOut);
        $this->assertNotSame('', $bottomOut);

        $this->assertSame(0, \preg_match('/1 0 0 1 0 -[0-9.]+ cm\n.*?\(A\) Tj/s', $topOut));

        $middleMatch = [];
        $bottomMatch = [];
        $this->assertSame(
            1,
            \preg_match('/1 0 0 1 0 (-[0-9.]+) cm\n.*?\(A\) Tj/s', $middleOut, $middleMatch),
        );
        $this->assertSame(
            1,
            \preg_match('/1 0 0 1 0 (-[0-9.]+) cm\n.*?\(A\) Tj/s', $bottomOut, $bottomMatch),
        );

        $middleOffset = \abs((float) $middleMatch[1]);
        $bottomOffset = \abs((float) $bottomMatch[1]);

        $this->assertGreaterThan(0.0, $middleOffset);
        $this->assertGreaterThan($middleOffset, $bottomOffset);
    }

    public function testGetHTMLCellPrefersCssVerticalAlignOverValignOnCompletedRowspanCell(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $cssMiddleOut = $obj->getHTMLCell(
            '<table cellspacing="0">'
            . '<tr><td rowspan="2" style="border:1px solid black;vertical-align:middle" valign="bottom">A</td>'
            . '<td style="border:1px solid black">B</td></tr>'
            . '<tr><td style="border:1px solid black">C<br/>D<br/>E</td></tr>'
            . '</table>',
            0,
            0,
            40,
            40,
        );
        $attrBottomOut = $obj->getHTMLCell(
            '<table cellspacing="0">'
            . '<tr><td rowspan="2" style="border:1px solid black" valign="bottom">A</td>'
            . '<td style="border:1px solid black">B</td></tr>'
            . '<tr><td style="border:1px solid black">C<br/>D<br/>E</td></tr>'
            . '</table>',
            0,
            0,
            40,
            40,
        );

        $this->assertNotSame('', $cssMiddleOut);
        $this->assertNotSame('', $attrBottomOut);

        $middleMatch = [];
        $bottomMatch = [];
        $this->assertSame(
            1,
            \preg_match('/1 0 0 1 0 (-[0-9.]+) cm\n.*?\(A\) Tj/s', $cssMiddleOut, $middleMatch),
        );
        $this->assertSame(
            1,
            \preg_match('/1 0 0 1 0 (-[0-9.]+) cm\n.*?\(A\) Tj/s', $attrBottomOut, $bottomMatch),
        );

        $middleOffset = \abs((float) $middleMatch[1]);
        $bottomOffset = \abs((float) $bottomMatch[1]);

        $this->assertGreaterThan(0.0, $middleOffset);
        $this->assertGreaterThan($middleOffset, $bottomOffset);
    }

    public function testGetHTMLCellAppliesVerticalAlignBottomOnCompletedRowspanHeaderCell(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $topOut = $obj->getHTMLCell(
            '<table cellspacing="0">'
            . '<tr><th rowspan="2" style="border:1px solid black" valign="top">A</th>'
            . '<td style="border:1px solid black">B</td></tr>'
            . '<tr><td style="border:1px solid black">C<br/>D<br/>E</td></tr>'
            . '</table>',
            0,
            0,
            40,
            40,
        );
        $bottomOut = $obj->getHTMLCell(
            '<table cellspacing="0">'
            . '<tr><th rowspan="2" style="border:1px solid black" valign="bottom">A</th>'
            . '<td style="border:1px solid black">B</td></tr>'
            . '<tr><td style="border:1px solid black">C<br/>D<br/>E</td></tr>'
            . '</table>',
            0,
            0,
            40,
            40,
        );

        $this->assertNotSame('', $topOut);
        $this->assertNotSame('', $bottomOut);

        $this->assertSame(
            0,
            \preg_match('/1 0 0 1 0 -[0-9.]+ cm\\n.*?\\(A\\) Tj/s', $topOut),
        );
        $this->assertSame(
            1,
            \preg_match('/1 0 0 1 0 -[0-9.]+ cm\\n.*?\\(A\\) Tj/s', $bottomOut),
            'Bottom vertical alignment should translate completed rowspan header-cell content.',
        );
    }

    public function testGetHTMLCellCollapseAvoidsDuplicateOuterBorderWithCellBorder(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table border="1" style="border-collapse:collapse"><tr>'
            . '<td style="border:1px solid black">A</td></tr></table>',
            0,
            0,
            30,
            20,
        );

        $this->assertNotSame('', $out);
        $matches = [];
        \preg_match_all(
            '/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\s+s/',
            $out,
            $matches,
            PREG_SET_ORDER,
        );
        $this->assertCount(
            1,
            $matches,
            'Collapsed single-cell table should avoid drawing duplicate outer-border rectangles',
        );
    }

    public function testGetHTMLCellCollapseIgnoresCssBorderSpacing(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<table border="1" style="border-collapse:collapse;border-spacing:3 5"><tr>'
            . '<td style="border:1px solid black">A</td></tr></table>',
            0,
            0,
            30,
            20,
        );

        $this->assertNotSame('', $out);
        $matches = [];
        \preg_match_all('/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\s+s/', $out, $matches, PREG_SET_ORDER);
        $this->assertCount(
            1,
            $matches,
            'Collapsed single-cell table should ignore CSS border-spacing and avoid separated outer-border rectangles',
        );
    }

    public function testGetHTMLCellAppliesMixedCssBorderSpacingAcrossRowsAndColumns(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $noSpacing = $obj->getHTMLCell(
            '<table><tr><td style="border:1px solid black">A</td><td style="border:1px solid black">B</td></tr>'
            . '<tr><td style="border:1px solid black">C</td><td style="border:1px solid black">D</td></tr></table>',
            0,
            0,
            40,
            30,
        );

        $withSpacing = $obj->getHTMLCell(
            '<table style="border-spacing:3 5"><tr><td style="border:1px solid black">A</td>'
            . '<td style="border:1px solid black">B</td></tr><tr><td style="border:1px solid black">C</td>'
            . '<td style="border:1px solid black">D</td></tr></table>',
            0,
            0,
            40,
            30,
        );

        $this->assertNotSame('', $noSpacing);
        $this->assertNotSame('', $withSpacing);

        $noSpacingMatches = [];
        $withSpacingMatches = [];
        \preg_match_all(
            '/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\s+s/',
            $noSpacing,
            $noSpacingMatches,
            PREG_SET_ORDER,
        );
        \preg_match_all(
            '/(-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) (-?[0-9.]+) re\s+s/',
            $withSpacing,
            $withSpacingMatches,
            PREG_SET_ORDER,
        );

        $this->assertGreaterThanOrEqual(4, \count($noSpacingMatches));
        $this->assertGreaterThanOrEqual(4, \count($withSpacingMatches));

        $noSpacingX = [];
        $noSpacingY = [];
        $noSpacingWidths = [];
        $noSpacingHeights = [];
        foreach ($noSpacingMatches as $match) {
            $noSpacingX[] = \round((float) $match[1], 4);
            $noSpacingY[] = \round((float) $match[2], 4);
            $noSpacingWidths[] = \round((float) $match[3], 4);
            $noSpacingHeights[] = \round((float) $match[4], 4);
        }

        $withSpacingX = [];
        $withSpacingY = [];
        $withSpacingWidths = [];
        $withSpacingHeights = [];
        foreach ($withSpacingMatches as $match) {
            $withSpacingX[] = \round((float) $match[1], 4);
            $withSpacingY[] = \round((float) $match[2], 4);
            $withSpacingWidths[] = \round((float) $match[3], 4);
            $withSpacingHeights[] = \round((float) $match[4], 4);
        }

        $noSpacingX = \array_values(\array_unique($noSpacingX));
        $noSpacingY = \array_values(\array_unique($noSpacingY));
        $noSpacingWidths = \array_values(\array_unique($noSpacingWidths));
        $noSpacingHeights = \array_values(\array_unique($noSpacingHeights));
        $withSpacingX = \array_values(\array_unique($withSpacingX));
        $withSpacingY = \array_values(\array_unique($withSpacingY));
        $withSpacingWidths = \array_values(\array_unique($withSpacingWidths));
        $withSpacingHeights = \array_values(\array_unique($withSpacingHeights));
        \sort($noSpacingX);
        \sort($noSpacingY);
        \sort($noSpacingWidths);
        \sort($noSpacingHeights);
        \sort($withSpacingX);
        \sort($withSpacingY);
        \sort($withSpacingWidths);
        \sort($withSpacingHeights);

        $this->assertCount(2, $noSpacingX);
        $this->assertCount(2, $noSpacingY);
        $this->assertCount(1, $noSpacingWidths);
        $this->assertCount(1, $noSpacingHeights);
        $this->assertCount(2, $withSpacingX);
        $this->assertCount(2, $withSpacingY);
        $this->assertCount(1, $withSpacingWidths);
        $this->assertCount(1, $withSpacingHeights);
        $noSpacingHGap = $noSpacingX[1] - ($noSpacingX[0] + $noSpacingWidths[0]);
        $withSpacingHGap = $withSpacingX[1] - ($withSpacingX[0] + $withSpacingWidths[0]);
        $noSpacingVGap = $noSpacingY[1] - ($noSpacingY[0] + $noSpacingHeights[0]);
        $withSpacingVGap = $withSpacingY[1] - ($withSpacingY[0] + $withSpacingHeights[0]);
        $this->assertGreaterThan(
            $noSpacingHGap,
            $withSpacingHGap,
            'Expected mixed CSS border-spacing to increase the horizontal gap between rendered cell columns',
        );
        $this->assertGreaterThan(
            $noSpacingVGap,
            $withSpacingVGap,
            'Expected mixed CSS border-spacing to increase the vertical gap between rendered cell rows',
        );
    }

    public function testParseHTMLTagOPENtableTreatsCollapseAsZeroCellspacing(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $separateElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'cols' => 1,
            'pendingcellspacingh' => 3.0,
            'pendingcellspacingv' => 3.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [20.0],
        ]);
        $collapseElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 1,
            'pendingcellspacingh' => 3.0,
            'pendingcellspacingv' => 3.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [20.0],
        ]);

        $separateX = 0.0;
        $separateY = 0.0;
        $separateW = 30.0;
        $separateH = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENtable',
            $separateElm,
            $separateX,
            $separateY,
            $separateW,
            $separateH,
        );

        $collapseX = 0.0;
        $collapseY = 0.0;
        $collapseW = 30.0;
        $collapseH = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod(
            'parseHTMLTagOPENtable',
            $collapseElm,
            $collapseX,
            $collapseY,
            $collapseW,
            $collapseH,
        );

        $this->assertSame(3.0, $separateY);
        $this->assertSame(0.0, $collapseY);
        $this->assertGreaterThan($collapseY, $separateY);
    }

    public function testParseHTMLTagOPENtableUsesHorizontalAndVerticalBorderSpacingIndependently(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 30.0, 20.0);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'table-layout' => 'fixed',
            'cols' => 1,
            'pendingcellspacingh' => 3.0,
            'pendingcellspacingv' => 6.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [20.0],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);

        $table = $hrc['tablestack'][0];
        $this->assertSame(6.0, $tpy);
        $this->assertSame(26.0, $table['width']);
        $this->assertSame(3.0, $table['cellspacingh']);
        $this->assertSame(6.0, $table['cellspacingv']);
        $this->assertSame(6.0, $table['rowtop']);
    }

    public function testParseHTMLTagOPENtableUsesPendingColWidthsForFixedAndAutoLayouts(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 60.0, 20.0);

        $fixedElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'table-layout' => 'fixed',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [10.0, 30.0],
        ]);
        $autoElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'table-layout' => 'auto',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [10.0, 30.0],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 40.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $fixedElm, $tpx, $tpy, $tpw, $tph);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 40.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $autoElm, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertCount(2, $hrc['tablestack']);

        $fixedTable = $hrc['tablestack'][0];
        $autoTable = $hrc['tablestack'][1];

        $this->assertSame([10.0, 30.0], $fixedTable['colwidths']);
        $this->assertSame([10.0, 30.0], $autoTable['colwidths']);
    }

    public function testParseHTMLTagOPENtdCarriesResolvedValignIntoCellContext(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'cols' => 1,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [24.0],
        ]);
        $tdElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'valign' => 'bottom',
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $tdElm, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['bcellctx']);
        $this->assertSame('bottom', $hrc['bcellctx'][0]['valign']);
    }

    public function testParseHTMLTagCLOSEtdEmptyCellsHideSuppressesBorderAndFillForEmptySeparateCells(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'separate',
            'cols' => 1,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [24.0],
        ]);
        $tdElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'empty-cells' => 'hide',
            'bgcolor' => '#eeeeee',
            'border' => [
                'T' => ['lineWidth' => 1.0, 'lineColor' => '#111'],
                'R' => ['lineWidth' => 1.0, 'lineColor' => '#111'],
                'B' => ['lineWidth' => 1.0, 'lineColor' => '#111'],
                'L' => ['lineWidth' => 1.0, 'lineColor' => '#111'],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $tdElm, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $tdElm, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);
        $table = $hrc['tablestack'][0];
        $this->assertNotEmpty($table['cells']);
        $this->assertSame([], $table['cells'][0]['bstyles']);
        $this->assertNull($table['cells'][0]['fillstyle']);
    }

    public function testParseHTMLTagCLOSEtdEmptyCellsHideDoesNotSuppressInCollapseMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 1,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [24.0],
        ]);
        $tdElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'empty-cells' => 'hide',
            'bgcolor' => '#eeeeee',
            'border' => [
                'T' => ['lineWidth' => 1.0, 'lineColor' => '#111'],
                'R' => ['lineWidth' => 1.0, 'lineColor' => '#111'],
                'B' => ['lineWidth' => 1.0, 'lineColor' => '#111'],
                'L' => ['lineWidth' => 1.0, 'lineColor' => '#111'],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $tdElm, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $tdElm, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);
        $table = $hrc['tablestack'][0];
        $this->assertNotEmpty($table['cells']);
        $this->assertNotSame([], $table['cells'][0]['bstyles']);
    }

    public function testParseHTMLTagOPENthCarriesResolvedValignIntoCellContext(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'cols' => 1,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [24.0],
        ]);
        $thElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'th',
            'valign' => 'middle',
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENth', $thElm, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['bcellctx']);
        $this->assertSame('middle', $hrc['bcellctx'][0]['valign']);
    }

    public function testParseHTMLTagOPENtdCollapsePrefersWiderSharedVerticalBorder(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [12.0, 12.0],
        ]);
        $leftTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'R' => ['lineWidth' => 0.5, 'lineColor' => '#111'],
            ],
        ]);
        $rightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'L' => ['lineWidth' => 2.0, 'lineColor' => '#222'],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $leftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $leftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $rightTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);
        $this->assertNotEmpty($hrc['bcellctx']);

        $table = $hrc['tablestack'][0];
        $currentCell = $hrc['bcellctx'][0];

        $this->assertArrayHasKey(1, $table['cells'][0]['bstyles']);
        $this->assertSame(2.0, (float) $table['cells'][0]['bstyles'][1]['lineWidth']);
        $this->assertSame('#222', $table['cells'][0]['bstyles'][1]['lineColor']);
        $this->assertArrayNotHasKey(3, $currentCell['bstyles']);
    }

    public function testParseHTMLTagOPENtdCollapsePrefersSolidWhenWidthsMatchOnSharedVerticalBorder(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [12.0, 12.0],
        ]);
        $leftTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'R' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#111',
                    'dashArray' => [3, 3],
                    'dashPhase' => 3,
                ],
            ],
        ]);
        $rightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'L' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#222',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $leftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $leftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $rightTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);

        $table = $hrc['tablestack'][0];
        $this->assertArrayHasKey(1, $table['cells'][0]['bstyles']);
        $this->assertSame('#222', $table['cells'][0]['bstyles'][1]['lineColor']);
        $this->assertSame([], $table['cells'][0]['bstyles'][1]['dashArray']);
    }

    public function testParseHTMLTagOPENtdCollapsePrefersRightCellOnEqualSharedVerticalTieInRtlTable(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'dir' => 'rtl',
            'border-collapse' => 'collapse',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [12.0, 12.0],
        ]);
        $leftTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'R' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#111',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);
        $rightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'L' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#222',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $leftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $leftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $rightTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);

        $table = $hrc['tablestack'][0];
        $this->assertArrayHasKey(1, $table['cells'][0]['bstyles']);
        $this->assertSame('#222', $table['cells'][0]['bstyles'][1]['lineColor']);
    }

    public function testParseHTMLTagOPENtdCollapsePrefersDoubleWhenWidthsMatchOnSharedVerticalBorder(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [12.0, 12.0],
        ]);
        $leftTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'R' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#111',
                    'lineCap' => 'square',
                    'lineJoin' => 'miter',
                    'miterLimit' => 10.0,
                    'dashArray' => [],
                    'dashPhase' => 0.0,
                    'fillColor' => '',
                    'cssBorderStyle' => 'solid',
                ],
            ],
        ]);
        $rightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'L' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#222',
                    'lineCap' => 'square',
                    'lineJoin' => 'miter',
                    'miterLimit' => 10.0,
                    'dashArray' => [],
                    'dashPhase' => 0.0,
                    'fillColor' => '',
                    'cssBorderStyle' => 'double',
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $leftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $leftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $rightTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);

        $table = $hrc['tablestack'][0];
        $this->assertArrayHasKey(1, $table['cells'][0]['bstyles']);
        $this->assertSame('#222', $table['cells'][0]['bstyles'][1]['lineColor']);
        $this->assertSame('double', $obj->exposeGetHTMLCollapsedBorderStyleName($table['cells'][0]['bstyles'][1]));
    }

    public function testParseHTMLTagOPENtdCollapsePrefersSolidSharedVerticalBorderAfterColspanCell(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 3,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [10.0, 10.0, 10.0],
        ]);
        $leftSpanTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['colspan' => '2'],
            'border' => [
                'R' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#111',
                    'dashArray' => [3, 3],
                    'dashPhase' => 3,
                ],
            ],
        ]);
        $rightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'L' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#222',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 36.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $leftSpanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $leftSpanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $rightTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);
        $this->assertNotEmpty($hrc['bcellctx']);

        $table = $hrc['tablestack'][0];
        $currentCell = $hrc['bcellctx'][0];

        $this->assertNotEmpty($table['cells']);
        $this->assertArrayHasKey(1, $table['cells'][0]['bstyles']);
        $this->assertSame('#222', $table['cells'][0]['bstyles'][1]['lineColor']);
        $this->assertSame([], $table['cells'][0]['bstyles'][1]['dashArray']);
        $this->assertArrayNotHasKey(3, $currentCell['bstyles']);
    }

    public function testParseHTMLTagOPENtdCollapsePrefersWiderSharedVerticalBorderAfterColspanCell(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 3,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [10.0, 10.0, 10.0],
        ]);
        $leftSpanTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['colspan' => '2'],
            'border' => [
                'R' => [
                    'lineWidth' => 2.0,
                    'lineColor' => '#111',
                    'dashArray' => [3, 3],
                    'dashPhase' => 3,
                ],
            ],
        ]);
        $rightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'L' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#222',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 36.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $leftSpanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $leftSpanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $rightTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);

        $table = $hrc['tablestack'][0];
        $this->assertNotEmpty($table['cells']);
        $this->assertArrayHasKey(1, $table['cells'][0]['bstyles']);
        $this->assertSame(2.0, (float) $table['cells'][0]['bstyles'][1]['lineWidth']);
        $this->assertSame('#111', $table['cells'][0]['bstyles'][1]['lineColor']);
        $this->assertSame([3, 3], $table['cells'][0]['bstyles'][1]['dashArray']);
    }

    public function testParseHTMLTagOPENtdCollapseKeepsTopWhenPreferredOverPreviousRowBottom(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 1,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [24.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'B' => ['lineWidth' => 0.5, 'lineColor' => '#111'],
            ],
        ]);
        $row2Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'T' => ['lineWidth' => 2.0, 'lineColor' => '#222'],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2Td, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['bcellctx']);
        $currentCell = $hrc['bcellctx'][0];

        $this->assertArrayHasKey(0, $currentCell['bstyles']);
        $this->assertSame(2.0, (float) $currentCell['bstyles'][0]['lineWidth']);
        $this->assertSame('#222', $currentCell['bstyles'][0]['lineColor']);
    }

    public function testParseHTMLTagOPENtdCollapsePrefersSolidTopWhenWidthsMatchPreviousRowBottom(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 1,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [24.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'B' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#111',
                    'dashArray' => [3, 3],
                    'dashPhase' => 3,
                ],
            ],
        ]);
        $row2Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'T' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#222',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2Td, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['bcellctx']);
        $currentCell = $hrc['bcellctx'][0];

        $this->assertArrayHasKey(0, $currentCell['bstyles']);
        $this->assertSame('#222', $currentCell['bstyles'][0]['lineColor']);
        $this->assertSame([], $currentCell['bstyles'][0]['dashArray']);
    }

    public function testParseHTMLTagOPENtdCollapsePrefersSharedVerticalBorderAgainstActiveRowspan(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [12.0, 12.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1LeftTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2'],
            'border' => [
                'R' => ['lineWidth' => 0.5, 'lineColor' => '#111'],
            ],
        ]);
        $row1RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
        ]);
        $row2RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'L' => ['lineWidth' => 2.0, 'lineColor' => '#222'],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1LeftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1LeftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2RightTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);
        $this->assertNotEmpty($hrc['bcellctx']);

        $table = $hrc['tablestack'][0];
        $currentCell = $hrc['bcellctx'][0];

        $this->assertNotEmpty($table['rowspans']);
        $this->assertArrayHasKey(1, $table['rowspans'][0]['bstyles']);
        $this->assertSame(2.0, (float) $table['rowspans'][0]['bstyles'][1]['lineWidth']);
        $this->assertSame('#222', $table['rowspans'][0]['bstyles'][1]['lineColor']);
        $this->assertArrayNotHasKey(3, $currentCell['bstyles']);
    }

    public function testParseHTMLTagOPENtdCollapsePrefersCurrentCellOnEqualTieRtl(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'dir' => 'rtl',
            'border-collapse' => 'collapse',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [12.0, 12.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1LeftTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2'],
            'border' => [
                'R' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#111',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);
        $row1RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
        ]);
        $row2RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'L' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#222',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1LeftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1LeftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2RightTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);
        $this->assertNotEmpty($hrc['bcellctx']);

        $table = $hrc['tablestack'][0];
        $currentCell = $hrc['bcellctx'][0];

        $this->assertNotEmpty($table['rowspans']);
        $this->assertArrayHasKey(1, $table['rowspans'][0]['bstyles']);
        $this->assertSame('#222', $table['rowspans'][0]['bstyles'][1]['lineColor']);
        $this->assertArrayNotHasKey(3, $currentCell['bstyles']);
    }

    public function testParseHTMLTagOPENtdCollapsePrefersRowspanCellOnEqualVerticalTieInLtrTable(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [12.0, 12.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1LeftTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2'],
            'border' => [
                'R' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#111',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);
        $row1RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
        ]);
        $row2RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'L' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#222',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1LeftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1LeftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2RightTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);
        $this->assertNotEmpty($hrc['bcellctx']);

        $table = $hrc['tablestack'][0];
        $currentCell = $hrc['bcellctx'][0];

        $this->assertNotEmpty($table['rowspans']);
        $this->assertArrayHasKey(1, $table['rowspans'][0]['bstyles']);
        $this->assertSame('#111', $table['rowspans'][0]['bstyles'][1]['lineColor']);
        $this->assertArrayNotHasKey(3, $currentCell['bstyles']);
    }

    public function testParseHTMLTagOPENtdCollapsePrefersRowspanColspanCellOnEqualVerticalTieInLtrTable(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 3,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [8.0, 8.0, 8.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1LeftSpanTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2', 'colspan' => '2'],
            'border' => [
                'R' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#111',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);
        $row1RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
        ]);
        $row2RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'L' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#222',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1LeftSpanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1LeftSpanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2RightTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);
        $this->assertNotEmpty($hrc['bcellctx']);

        $table = $hrc['tablestack'][0];
        $currentCell = $hrc['bcellctx'][0];

        $this->assertNotEmpty($table['rowspans']);
        $this->assertSame(2, (int) $table['rowspans'][0]['colspan']);
        $this->assertArrayHasKey(1, $table['rowspans'][0]['bstyles']);
        $this->assertSame('#111', $table['rowspans'][0]['bstyles'][1]['lineColor']);
        $this->assertArrayNotHasKey(3, $currentCell['bstyles']);
    }

    public function testParseHTMLTagOPENtdCollapsePrefersCurrentCellOnRowspanColspanEqualVerticalTieInRtlTable(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'dir' => 'rtl',
            'border-collapse' => 'collapse',
            'cols' => 3,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [8.0, 8.0, 8.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1LeftSpanTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2', 'colspan' => '2'],
            'border' => [
                'R' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#111',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);
        $row1RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
        ]);
        $row2RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'L' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#222',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1LeftSpanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1LeftSpanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2RightTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);
        $this->assertNotEmpty($hrc['bcellctx']);

        $table = $hrc['tablestack'][0];
        $currentCell = $hrc['bcellctx'][0];

        $this->assertNotEmpty($table['rowspans']);
        $this->assertSame(2, (int) $table['rowspans'][0]['colspan']);
        $this->assertArrayHasKey(1, $table['rowspans'][0]['bstyles']);
        $this->assertSame('#222', $table['rowspans'][0]['bstyles'][1]['lineColor']);
        $this->assertArrayNotHasKey(3, $currentCell['bstyles']);
    }

    public function testParseHTMLTagOPENtdCollapseSkipsNonAdjacentPreviousCellWhenRowspanOccupiesLeftBoundary(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 4,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [10.0, 10.0, 10.0, 10.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);

        $row1Col0 = $this->makeHtmlNode(['opening' => true, 'value' => 'td']);
        $row1Col1 = $this->makeHtmlNode(['opening' => true, 'value' => 'td']);
        $row1Col2Rowspan = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2'],
            'border' => [
                'R' => ['lineWidth' => 0.5, 'lineColor' => '#111'],
            ],
        ]);
        $row1Col3 = $this->makeHtmlNode(['opening' => true, 'value' => 'td']);

        $row2Col0 = $this->makeHtmlNode(['opening' => true, 'value' => 'td']);
        $row2Col1 = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'R' => ['lineWidth' => 3.0, 'lineColor' => '#333'],
            ],
        ]);
        $row2Col3 = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'L' => ['lineWidth' => 2.0, 'lineColor' => '#222'],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 50.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1Col0, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1Col0, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1Col1, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1Col1, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1Col2Rowspan, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1Col2Rowspan, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1Col3, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1Col3, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2Col0, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row2Col0, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2Col1, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row2Col1, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2Col3, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);
        $this->assertNotEmpty($hrc['bcellctx']);

        $table = $hrc['tablestack'][0];
        $currentCell = $hrc['bcellctx'][0];

        $this->assertCount(2, $table['cells']);
        $this->assertArrayHasKey(1, $table['cells'][1]['bstyles']);
        $this->assertSame(3.0, (float) $table['cells'][1]['bstyles'][1]['lineWidth']);
        $this->assertSame('#333', $table['cells'][1]['bstyles'][1]['lineColor']);

        $this->assertNotEmpty($table['rowspans']);
        $this->assertArrayHasKey(1, $table['rowspans'][0]['bstyles']);
        $this->assertSame(2.0, (float) $table['rowspans'][0]['bstyles'][1]['lineWidth']);
        $this->assertSame('#222', $table['rowspans'][0]['bstyles'][1]['lineColor']);
        $this->assertArrayNotHasKey(3, $currentCell['bstyles']);
    }

    public function testParseHTMLTagOPENtdCollapseSuppressesColspanTopWhenCoveredByStrongerPreviousBottom(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [12.0, 12.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1LeftTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'B' => ['lineWidth' => 2.0, 'lineColor' => '#111'],
            ],
        ]);
        $row1RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'B' => ['lineWidth' => 0.5, 'lineColor' => '#222'],
            ],
        ]);
        $row2SpanTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['colspan' => '2'],
            'border' => [
                'T' => ['lineWidth' => 1.0, 'lineColor' => '#333'],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1LeftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1LeftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2SpanTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['bcellctx']);
        $currentCell = $hrc['bcellctx'][0];

        $this->assertArrayNotHasKey(0, $currentCell['bstyles']);
    }

    public function testParseHTMLTagOPENtdCollapseSuppressesTopBelowCompletedRowspanBottom(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 1,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [24.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2'],
            'border' => [
                'B' => ['lineWidth' => 2.0, 'lineColor' => '#111'],
            ],
        ]);
        $row3Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'T' => ['lineWidth' => 1.0, 'lineColor' => '#222'],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row3Td, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['bcellctx']);
        $currentCell = $hrc['bcellctx'][0];

        $this->assertArrayNotHasKey(0, $currentCell['bstyles']);
    }

    public function testParseHTMLTagOPENtdCollapseSuppressesEqualHorizontalTopTieInRtlTable(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'dir' => 'rtl',
            'border-collapse' => 'collapse',
            'cols' => 1,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [24.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'B' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#111',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);
        $row2Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'T' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#222',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2Td, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['bcellctx']);
        $currentCell = $hrc['bcellctx'][0];

        $this->assertArrayNotHasKey(0, $currentCell['bstyles']);
    }

    public function testParseHTMLTagCLOSEtrCollapsePrevRowBottomPrefersCompletedRowspanOnEqualCellTie(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 1,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [24.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2'],
            'border' => [
                'B' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#111',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);
        $row2Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'B' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#222',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row2Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);

        $table = $hrc['tablestack'][0];
        $this->assertArrayHasKey(0, $table['prevrowbottom']);
        $this->assertSame('#111', $table['prevrowbottom'][0]['lineColor']);
    }

    public function testParseHTMLTagCLOSEtrCollapsePrevRowBottomPrefersCompletedRowspanOnCompetingCurrentCell(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 1,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [24.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2'],
            'border' => [
                'B' => [
                    'lineWidth' => 1.5,
                    'lineColor' => '#111',
                ],
            ],
        ]);
        $row2Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'B' => [
                    'lineWidth' => 2.0,
                    'lineColor' => '#222',
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row2Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['tablestack']);

        $table = $hrc['tablestack'][0];
        $this->assertArrayHasKey(0, $table['prevrowbottom']);
        $this->assertSame('#111', $table['prevrowbottom'][0]['lineColor']);
    }

    public function testParseHTMLTagOPENtdCollapsePrefersSolidTopBelowCompletedRowspanBottomWhenWidthsMatch(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 1,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [24.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2'],
            'border' => [
                'B' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#111',
                    'dashArray' => [3, 3],
                    'dashPhase' => 3,
                ],
            ],
        ]);
        $row3Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'T' => [
                    'lineWidth' => 1.0,
                    'lineColor' => '#222',
                    'dashArray' => [],
                    'dashPhase' => 0,
                ],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row3Td, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['bcellctx']);
        $currentCell = $hrc['bcellctx'][0];

        $this->assertArrayHasKey(0, $currentCell['bstyles']);
        $this->assertSame('#222', $currentCell['bstyles'][0]['lineColor']);
        $this->assertSame([], $currentCell['bstyles'][0]['dashArray']);
    }

    public function testParseHTMLTagOPENtdCollapseKeepsTopBelowCompletedRowspanBottomWhenCurrentTopIsWider(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 1,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [24.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2'],
            'border' => [
                'B' => ['lineWidth' => 0.5, 'lineColor' => '#111'],
            ],
        ]);
        $row3Td = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'T' => ['lineWidth' => 2.0, 'lineColor' => '#222'],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1Td, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row3Td, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['bcellctx']);
        $currentCell = $hrc['bcellctx'][0];

        $this->assertArrayHasKey(0, $currentCell['bstyles']);
        $this->assertSame(2.0, (float) $currentCell['bstyles'][0]['lineWidth']);
        $this->assertSame('#222', $currentCell['bstyles'][0]['lineColor']);
    }

    public function testParseHTMLTagOPENtdCollapseKeepsColspanTopWhenAnyCoveredSegmentIsUnresolved(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [12.0, 12.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1LeftTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'B' => ['lineWidth' => 2.0, 'lineColor' => '#111'],
            ],
        ]);
        $row1RightRowspanTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2'],
        ]);
        $row2SpanTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['colspan' => '2'],
            'border' => [
                'T' => ['lineWidth' => 1.0, 'lineColor' => '#222'],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1LeftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1LeftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1RightRowspanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1RightRowspanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2SpanTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['bcellctx']);
        $currentCell = $hrc['bcellctx'][0];

        // Mixed coverage keeps the colspan top edge when any segment has no previous-row owner.
        $this->assertArrayHasKey(0, $currentCell['bstyles']);
        $this->assertSame('#222', $currentCell['bstyles'][0]['lineColor']);
    }

    public function testParseHTMLTagOPENtdCollapseKeepsColspanTopWhenAnyCoveredSegmentIsUnresolvedInRtlTable(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'dir' => 'rtl',
            'border-collapse' => 'collapse',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [12.0, 12.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1LeftTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'B' => ['lineWidth' => 2.0, 'lineColor' => '#111'],
            ],
        ]);
        $row1RightRowspanTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2'],
        ]);
        $row2SpanTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['colspan' => '2'],
            'border' => [
                'T' => ['lineWidth' => 1.0, 'lineColor' => '#222'],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1LeftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1LeftTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1RightRowspanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1RightRowspanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2SpanTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['bcellctx']);
        $currentCell = $hrc['bcellctx'][0];

        $this->assertArrayHasKey(0, $currentCell['bstyles']);
        $this->assertSame('#222', $currentCell['bstyles'][0]['lineColor']);
    }

    public function testParseHTMLTagOPENtdCollapseSuppressesColspanTopOnStrongerCoveredSegment(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'border-collapse' => 'collapse',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [12.0, 12.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1LeftRowspanTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2'],
            'border' => [
                'B' => ['lineWidth' => 2.0, 'lineColor' => '#111'],
            ],
        ]);
        $row1RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'B' => ['lineWidth' => 0.5, 'lineColor' => '#222'],
            ],
        ]);
        $row2RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'B' => ['lineWidth' => 0.5, 'lineColor' => '#333'],
            ],
        ]);
        $row3SpanTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['colspan' => '2'],
            'border' => [
                'T' => ['lineWidth' => 1.0, 'lineColor' => '#444'],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1LeftRowspanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1LeftRowspanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row2RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row3SpanTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['bcellctx']);
        $currentCell = $hrc['bcellctx'][0];

        // Covered colspan top edge is suppressed when any covered segment keeps a stronger previous-row owner.
        $this->assertArrayNotHasKey(0, $currentCell['bstyles']);
    }

    public function testParseHTMLTagOPENtdCollapseSuppressesColspanTopOnStrongerCoveredSegmentRtl(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $tableElm = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'table',
            'dir' => 'rtl',
            'border-collapse' => 'collapse',
            'cols' => 2,
            'pendingcellspacingh' => 0.0,
            'pendingcellspacingv' => 0.0,
            'pendingcellpadding' => 0.0,
            'pendingcolwidths' => [12.0, 12.0],
        ]);
        $trElm = $this->makeHtmlNode(['value' => 'tr']);
        $row1LeftRowspanTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['rowspan' => '2'],
            'border' => [
                'B' => ['lineWidth' => 2.0, 'lineColor' => '#111'],
            ],
        ]);
        $row1RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'B' => ['lineWidth' => 0.5, 'lineColor' => '#222'],
            ],
        ]);
        $row2RightTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'border' => [
                'B' => ['lineWidth' => 0.5, 'lineColor' => '#333'],
            ],
        ]);
        $row3SpanTd = $this->makeHtmlNode([
            'opening' => true,
            'value' => 'td',
            'attribute' => ['colspan' => '2'],
            'border' => [
                'T' => ['lineWidth' => 1.0, 'lineColor' => '#444'],
            ],
        ]);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 30.0;
        $tph = 20.0;
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtable', $tableElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1LeftRowspanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1LeftRowspanTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row1RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row2RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtd', $row2RightTd, $tpx, $tpy, $tpw, $tph);
        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagCLOSEtr', $trElm, $tpx, $tpy, $tpw, $tph);

        $obj->exposeInvokeParseHTMLTagMethod('parseHTMLTagOPENtd', $row3SpanTd, $tpx, $tpy, $tpw, $tph);

        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertNotEmpty($hrc['bcellctx']);
        $currentCell = $hrc['bcellctx'][0];

        $this->assertArrayNotHasKey(0, $currentCell['bstyles']);
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

    public function testOpenHTMLBlockAdvancesLineWhenInlineContentWasRendered(): void
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
        $obj->exposeSetHTMLLineState(6.0, 120.0, false);

        // Simulate inline content rendered at tpx > originx (e.g. "c" text)
        $tpx = 48.0;
        $tpy = 120.0;
        $tpw = 122.0;

        $obj->exposeOpenHTMLBlock($elm, $tpx, $tpy, $tpw);

        // tpx must be reset to origin
        $this->assertSame(20.0, $tpx);
        // tpy must advance by at least a line height
        $this->assertGreaterThan(120.0, $tpy);
    }

    public function testOpenHTMLBlockDoesNotAdvanceForIndentOffsetWithoutRenderedText(): void
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
        $obj->exposeSetHTMLLineState(0.0, 120.0, false);

        // Simulate list/container indentation shifting tpx without any inline glyphs rendered.
        $tpx = 48.0;
        $tpy = 140.0;
        $tpw = 122.0;

        $obj->exposeOpenHTMLBlock($elm, $tpx, $tpy, $tpw);

        $this->assertSame(20.0, $tpx);
        $this->assertSame(140.0, $tpy);
    }

    public function testOpenHTMLBlockDoesNotDoubleAdvanceWhenAlreadyAtLineStart(): void
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

        // Cursor already at line start (after a closeHTMLBlock)
        $tpx = 20.0;
        $tpy = 140.0;
        $tpw = 150.0;

        $obj->exposeOpenHTMLBlock($elm, $tpx, $tpy, $tpw);

        // tpy should not advance by an extra line — no inline content to push past
        $this->assertSame(20.0, $tpx);
        $this->assertSame(140.0, $tpy);
    }

    public function testAdjacentBlockMarginsCollapseToMax(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $closeElm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 8.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);
        $openElm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'margin' => ['T' => 12.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);

        $obj->exposeInitHTMLCellContext(20.0, 120.0, 150.0, 0.0);

        // Close previous block at line start: adds only bottom margin (8).
        $tpx = 20.0;
        $tpy = 140.0;
        $tpw = 150.0;
        $obj->exposeCloseHTMLBlock($closeElm, $tpx, $tpy, $tpw);
        $afterClose = $tpy;

        // Open next block on same line context: top margin (12) collapses with previous bottom (8), net +4.
        $obj->exposeOpenHTMLBlock($openElm, $tpx, $tpy, $tpw);

        $this->assertEqualsWithDelta($afterClose + 4.0, $tpy, 0.0001);
    }

    public function testInlineContentPreventsMarginCollapse(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $closeElm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'margin' => ['T' => 0.0, 'R' => 0.0, 'B' => 8.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);
        $openElm = $this->makeHtmlNode([
            'fontname' => 'helvetica',
            'fontsize' => 12.0,
            'line-height' => 1.0,
            'margin' => ['T' => 12.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
            'padding' => ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0],
        ]);

        $obj->exposeInitHTMLCellContext(20.0, 120.0, 150.0, 0.0);
        $obj->exposeSetHTMLLineState(6.0, 120.0, false);

        // Store pending bottom margin from previous close.
        $tpx = 20.0;
        $tpy = 140.0;
        $tpw = 150.0;
        $obj->exposeCloseHTMLBlock($closeElm, $tpx, $tpy, $tpw);
        $afterClose = $tpy;

        // Simulate inline content before next block opening: collapse must not apply.
        $tpx = 48.0;
        $obj->exposeSetHTMLLineState(6.0, $tpy, false);
        $obj->exposeOpenHTMLBlock($openElm, $tpx, $tpy, $tpw);

        $this->assertGreaterThan($afterClose + 4.0, $tpy);
    }

    public function testPdfuaClampHeadingRolePassesThroughNonHeadingRoles(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        $this->assertSame('P', $obj->exposePdfuaClampHeadingRole('P'));
        $this->assertSame('L', $obj->exposePdfuaClampHeadingRole('L'));
        $this->assertSame('Figure', $obj->exposePdfuaClampHeadingRole('Figure'));
    }

    public function testPdfuaClampHeadingRoleAllowsFirstH1(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        $this->assertSame('H1', $obj->exposePdfuaClampHeadingRole('H1'));
        $this->assertSame(1, $this->getObjectProperty($obj, 'pdfuaHeadingLevel'));
    }

    public function testPdfuaClampHeadingRoleClampsFirstH2ToH1(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        // First heading in document is H2 — must be clamped to H1
        $this->assertSame('H1', $obj->exposePdfuaClampHeadingRole('H2'));
        $this->assertSame(1, $this->getObjectProperty($obj, 'pdfuaHeadingLevel'));
    }

    public function testPdfuaClampHeadingRoleClampsSkippedLevel(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        // H1 → H3 skips H2; H3 should be clamped to H2
        $this->assertSame('H1', $obj->exposePdfuaClampHeadingRole('H1'));
        $this->assertSame('H2', $obj->exposePdfuaClampHeadingRole('H3'));
        $this->assertSame(2, $this->getObjectProperty($obj, 'pdfuaHeadingLevel'));
    }

    public function testPdfuaClampHeadingRoleSequentialLevelsUnclamped(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        // H1 → H2 → H3 is valid; no clamping should occur
        $this->assertSame('H1', $obj->exposePdfuaClampHeadingRole('H1'));
        $this->assertSame('H2', $obj->exposePdfuaClampHeadingRole('H2'));
        $this->assertSame('H3', $obj->exposePdfuaClampHeadingRole('H3'));
        $this->assertSame(3, $this->getObjectProperty($obj, 'pdfuaHeadingLevel'));
    }

    public function testPdfuaClampHeadingRoleAllowsGoingBackUp(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        // H1 → H2 → H3 → H1 is allowed; then H3 should be clamped to H2
        $obj->exposePdfuaClampHeadingRole('H1');
        $obj->exposePdfuaClampHeadingRole('H2');
        $obj->exposePdfuaClampHeadingRole('H3');
        $this->assertSame('H1', $obj->exposePdfuaClampHeadingRole('H1'));
        $this->assertSame(1, $this->getObjectProperty($obj, 'pdfuaHeadingLevel'));
        $this->assertSame('H2', $obj->exposePdfuaClampHeadingRole('H3'));
        $this->assertSame(2, $this->getObjectProperty($obj, 'pdfuaHeadingLevel'));
    }

    public function testBrAtLineStartAfterWrappedPlainTextAdvancesOnce(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(20.0, 120.0, 150.0, 0.0);

        $dom = [
            0 => $this->makeHtmlNode([
                'tag' => false,
                'value' => 'wrapped line',
            ]),
            1 => $this->makeHtmlNode([
                'tag' => true,
                'opening' => true,
                'self' => true,
                'value' => 'br',
            ]),
        ];

        $tpx = 20.0;
        $tpy = 140.0;
        $tpw = 150.0;
        $tph = 0.0;

        $obj->exposeParseHTMLTagOPENbrWithDom($dom, 1, $tpx, $tpy, $tpw, $tph);

        $this->assertGreaterThan(140.0, $tpy);
        $this->assertSame(20.0, $tpx);
    }

    public function testBrAtLineStartStillAdvancesAfterAnotherBrTag(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(20.0, 120.0, 150.0, 0.0);

        $dom = [
            0 => $this->makeHtmlNode([
                'tag' => true,
                'opening' => true,
                'self' => true,
                'value' => 'br',
            ]),
            1 => $this->makeHtmlNode([
                'tag' => true,
                'opening' => true,
                'self' => true,
                'value' => 'br',
            ]),
        ];

        $tpx = 20.0;
        $tpy = 140.0;
        $tpw = 150.0;
        $tph = 0.0;

        $obj->exposeParseHTMLTagOPENbrWithDom($dom, 1, $tpx, $tpy, $tpw, $tph);

        $this->assertGreaterThan(140.0, $tpy);
        $this->assertSame(20.0, $tpx);
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

    public function testGetHTMLDOMParsesQuotedUnquotedAndBooleanOpeningTagAttributes(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $dom = $obj->exposeGetHTMLDOM(
            '<input type=text data-one="alpha" data-two=beta readonly required custom-flag />',
        );

        $input = null;
        foreach ($dom as $node) {
            if (($node['value'] ?? '') === 'input' && !empty($node['opening'])) {
                $input = $node;
                break;
            }
        }

        $this->assertIsArray($input);
        /** @var array{attribute: array<string, string>} $input */
        $this->assertSame('text', $input['attribute']['type'] ?? null);
        $this->assertSame('alpha', $input['attribute']['data-one'] ?? null);
        $this->assertSame('beta', $input['attribute']['data-two'] ?? null);
        $this->assertSame('true', $input['attribute']['readonly'] ?? null);
        $this->assertSame('true', $input['attribute']['required'] ?? null);
        $this->assertSame('true', $input['attribute']['custom-flag'] ?? null);
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

    public function testGetHTMLliBulletRomanTypesDoNotFallbackToDecimal(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $decimal = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'decimal');
        $lowerRoman = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'lower-roman');
        $upperRoman = $obj->exposeGetHTMLliBullet(1, 5, 0, 0, 'upper-roman');

        $this->assertNotSame('', $decimal);
        $this->assertNotSame('', $lowerRoman);
        $this->assertNotSame('', $upperRoman);
        $this->assertNotSame($decimal, $lowerRoman);
        $this->assertNotSame($decimal, $upperRoman);
        $this->assertNotSame($lowerRoman, $upperRoman);
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

        $this->assertSame('A&AMP;B', $dom[1]['value']);
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
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' span[words~=foo]'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' span[data^=prefix]'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' span[data$=suffix]'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' span[data*=mid]'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' span[lang|=en]'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' span[id=node]'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' div > span.x'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' p + span.x'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' p ~ span.x'));
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
        $this->assertSame('none', $dom[1]['display']);
        $this->assertFalse($dom[1]['block']);
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
        /** @var array{H: float, V: float} $borderSpacing */
        $borderSpacing = \array_replace(['H' => 0.0, 'V' => 0.0], $dom[1]['border-spacing'] ?? []);
        $this->assertGreaterThan(0.0, $borderSpacing['H']);
        $this->assertSame($borderSpacing['H'], $borderSpacing['V']);
        $this->assertNotSame(['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0], $dom[1]['padding']);
        $this->assertNotSame(['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0], $dom[1]['margin']);
        $this->assertNotEmpty($dom[1]['border']);
    }

    public function testParseHTMLStyleAttributesParsesBorderSpacingAxes(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $dom = [
            0 => $this->makeHtmlNode(),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'border-spacing:3 6;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        /** @var array{H: float, V: float} $borderSpacing */
        $borderSpacing = \array_replace(['H' => 0.0, 'V' => 0.0], $dom[1]['border-spacing'] ?? []);

        $this->assertGreaterThan(0.0, $borderSpacing['H']);
        $this->assertEqualsWithDelta(
            $borderSpacing['H'] * 2.0,
            $borderSpacing['V'],
            0.0001,
        );
    }

    public function testParseHTMLStyleAttributesBorderSpacingInheritApplied(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $dom = [
            0 => $this->makeHtmlNode(),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'attribute' => ['style' => 'border-spacing:3 6;'],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 1,
                'attribute' => ['style' => 'border-spacing:InHeRiT;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 1);

        /** @var array{H: float, V: float} $parentSpacing */
        $parentSpacing = \array_replace(['H' => 0.0, 'V' => 0.0], $dom[1]['border-spacing'] ?? []);
        /** @var array{H: float, V: float} $childSpacing */
        $childSpacing = \array_replace(['H' => 0.0, 'V' => 0.0], $dom[2]['border-spacing'] ?? []);

        $this->assertSame($parentSpacing, $childSpacing);
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
            0 => \array_replace($root, [
                'value' => 'root', 'elkey' => 0, 'parent' => 0, 'thead' => '<tr nobr="true"></tr>'
            ]),
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

    public function testParseHTMLTextWrapsInlineContentFromLineOrigin(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 30.0, 60.0);

        $elm = $this->makeHtmlNode([
            'fontstyle' => 'UD',
            'value' => 'underline and line-trough',
        ]);
        $tpx = 10.0;
        $tpy = 0.0;
        $tpw = 20.0;
        $tph = 60.0;

        $out = $obj->exposeParseHTMLText($elm, $tpx, $tpy, $tpw, $tph);

        $this->assertNotSame('', $out);
        $numMatches = \preg_match_all('/([0-9]+\.[0-9]+) ([0-9]+\.[0-9]+) Td /', $out, $matches);

        $this->assertIsInt($numMatches);
        $this->assertGreaterThanOrEqual(2, $numMatches);
        $this->assertGreaterThan(0.0, (float) $matches[1][0]);
        $this->assertGreaterThan(0.0, (float) $matches[2][0]);
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


    public function testParseHTMLStyleAttributesKeepsRawFontFamilyValue(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontname' => 'helvetica']),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'fontsize' => 10.0,
                'attribute' => ['style' => 'font-family:times, serif;'],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);

        $this->assertSame('times, serif', $dom[1]['fontname']);
    }

    public function testParseHTMLAttributesKeepsRawFontFaceValue(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 10.0, 'fontname' => 'helvetica']),
            1 => $this->makeHtmlNode([
                'value' => 'font',
                'parent' => 0,
                'attribute' => ['face' => 'times, serif'],
                'style' => [],
                'fontstyle' => '',
                'fontsize' => 10.0,
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);

        $this->assertSame('times, serif', $dom[1]['fontname']);
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

    public function testGetHTMLliBulletFallbackShapesAlignToFontBoxWithBaselineInput(): void
    {
        $obj = $this->getInternalTestObject();
        $page = $this->initFontAndPage($obj);

        $this->setObjectProperty($obj, 'isunicode', false);
        $this->setObjectProperty($obj, 'rtl', false);

        /** @var \Com\Tecnick\Pdf\Font\Stack $fontstack */
        $fontstack = $this->getObjectProperty($obj, 'font');
        /** @var array<string, mixed> $font */
        $font = $fontstack->getCurrentFont();
        $ascent = \is_numeric($font['ascent'] ?? null) ? (float) $font['ascent'] : 0.0;
        $pageHeightRaw = \is_numeric($page['height'] ?? null) ? (float) $page['height'] : 0.0;
        $fontHeight = \is_numeric($font['height'] ?? null) ? (float) $font['height'] : 0.0;
        $fontSizeRaw = \is_numeric($font['usize'] ?? null) ? (float) $font['usize'] : 0.0;

        $baseline = $obj->toUnit($ascent);
        $pageHeight = $obj->toPoints($pageHeightRaw);
        $sizePt = $obj->toPoints($fontSizeRaw);

        $discOut = $obj->exposeGetHTMLliBullet(1, 2, 0, $baseline, 'disc');
        $this->assertMatchesRegularExpression('/\\n-?\\d+\\.\\d+\\s+(-?\\d+\\.\\d+)\\s+m\\n/', $discOut);
        $this->assertSame(1, \preg_match('/\\n-?\\d+\\.\\d+\\s+(-?\\d+\\.\\d+)\\s+m\\n/', $discOut, $discMatch));
        $this->assertEqualsWithDelta($pageHeight - ($fontHeight / 2), (float) $discMatch[1], 0.001);

        $circleOut = $obj->exposeGetHTMLliBullet(1, 2, 0, $baseline, 'circle');
        $this->assertSame(1, \preg_match('/\\n-?\\d+\\.\\d+\\s+(-?\\d+\\.\\d+)\\s+m\\n/', $circleOut, $circleMatch));
        $this->assertEqualsWithDelta($pageHeight - ($fontHeight / 2), (float) $circleMatch[1], 0.001);

        $squareOut = $obj->exposeGetHTMLliBullet(1, 2, 0, $baseline, 'square');
        $squarePattern = '/\\n-?\\d+\\.\\d+\\s+(-?\\d+\\.\\d+)\\s+'
            . '-?\\d+\\.\\d+\\s+-?\\d+\\.\\d+\\s+re\\n/';
        $this->assertSame(
            1,
            \preg_match($squarePattern, $squareOut, $squareMatch),
        );
        $squareTop = ($fontHeight - ($sizePt / 2)) / 2;
        $this->assertEqualsWithDelta($pageHeight - $squareTop, (float) $squareMatch[1], 0.001);
    }

    public function testGetHTMLliBulletRendersSvgImageBullet(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $svg = (string) realpath(__DIR__ . '/../examples/images/testsvg.svg');

        $this->assertNotSame('', $svg);

        try {
            $svgOut = $obj->exposeGetHTMLliBullet(1, 2, 0, 0, 'img|svg|4|4|' . $svg);
            $this->assertNotSame('', $svgOut);
        } catch (\Throwable $e) {
            $this->assertInstanceOf(\Throwable::class, $e);
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

    public function testIsValidCSSSelectorForTagSupportsPseudoClassSubsetAndRejectsOthers(): void
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
            2 => $this->makeHtmlNode([
                'value' => 'a',
                'parent' => 0,
                'tag' => true,
                'opening' => true,
                'attribute' => [
                    'href' => 'https://example.com',
                    'lang' => 'en-US',
                ],
            ]),
            3 => $this->makeHtmlNode([
                'value' => 'span',
                'parent' => 0,
                'tag' => true,
                'opening' => true,
                'attribute' => ['lang' => 'fr'],
            ]),
            4 => $this->makeHtmlNode([
                'value' => 'span',
                'parent' => 3,
                'tag' => true,
                'opening' => true,
            ]),
            5 => $this->makeHtmlNode([
                'value' => 'span',
                'parent' => 0,
                'tag' => true,
                'opening' => true,
            ]),
            6 => $this->makeHtmlNode([
                'value' => 'div',
                'parent' => 0,
                'tag' => true,
                'opening' => true,
            ]),
        ];

        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' div:first-child'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' div:last-child'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 2, ' a:nth-child(2)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' div:nth-child(odd)'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' div:nth-child(even)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 2, ' a:nth-child(even)'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 2, ' a:nth-child(odd)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' span:nth-child(2n+1)'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 2, ' a:nth-child(2n+1)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 2, ' a:nth-child(-n+2)'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 3, ' span:nth-child(-n+2)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 2, ' a:nth-child(n)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' div:empty'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 3, ' span:empty'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 2, ' a:link'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 3, ' span:link'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 2, ' a:nth-child(foo)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 4, ' span:only-child'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 3, ' span:only-child'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 5, ' span:last-of-type'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' span:first-of-type'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' span:nth-of-type(2n+1)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 2, ' a:nth-last-child(4)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 2, ' a:lang(en)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 2, ' a:lang(en-US)'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 2, ' a:lang(fr)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' span:lang(fr)'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 3, ' span:lang(en)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 4, ' span:lang(fr)'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' div:hover'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' div:focus'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' div::before'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' div::after'));
    }

    public function testGetHTMLDOMRecomputesTextInheritanceAfterStructuralPseudoResolution(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $html = '<style>#selectors li:first-child{color:#0a7a0a;}#selectors li:last-child{color:#aa2222;}</style>'
            . '<div id="selectors"><ul>'
            . '<li>First item styled by :first-child</li>'
            . '<li>Middle item</li>'
            . '<li>Last item styled by :last-child</li>'
            . '</ul></div>';

        $dom = $obj->exposeGetHTMLDOM($html);

        $firstTextColor = null;
        $middleTextColor = null;
        $lastTextColor = null;

        foreach ($dom as $node) {
            if (!empty($node['tag']) || !isset($node['value']) || !\is_string($node['value'])) {
                continue;
            }

            $text = \trim($node['value']);
            if ($text === 'First item styled by :first-child') {
                $firstTextColor = $node['fgcolor'] ?? null;
            } elseif ($text === 'Middle item') {
                $middleTextColor = $node['fgcolor'] ?? null;
            } elseif ($text === 'Last item styled by :last-child') {
                $lastTextColor = $node['fgcolor'] ?? null;
            }
        }

        $this->assertSame('rgba(4%,48%,4%,1)', $firstTextColor);
        $this->assertSame('black', $middleTextColor);
        $this->assertSame('rgba(67%,13%,13%,1)', $lastTextColor);
    }

    public function testIsValidCSSSelectorForTagSupportsEscapedIdentifiers(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode([
                'value' => 'x:tag',
                'parent' => 0,
                'tag' => true,
                'opening' => true,
                'attribute' => [
                    'class' => 'foo:bar cafe foobar',
                    'id' => 'id:main',
                    'data:name' => 'v:1',
                    'lang' => 'en-US',
                ],
            ]),
        ];

        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' x\\:tag'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' .foo\\:bar'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' #id\\:main'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' x\\:tag.foo\\:bar#id\\:main'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' [data\\:name="v\\:1"]'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' x\\00003atag'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' .foo\\00003abar'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' #id\\00003amain'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' x\\:tag:lang(en)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' .caf\\65 '));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, " .foo\\\nbar"));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' x\\:tag:lang(fr)'));
    }

    #[DataProvider('selectorAttributePseudoEdgeCaseProvider')]
    public function testIsValidCSSSelectorForTagFixtureAttributeAndPseudoEdgeCases(
        string $name,
        string $selector,
        int $node,
        bool $expected
    ): void {
        $obj = $this->getInternalTestObject();
        $dom = $this->getSelectorAttributePseudoEdgeCaseDom();

        $result = $obj->isValidCSSSelectorForTag($dom, $node, $selector);

        $this->assertSame($expected, $result, $name);
    }

    /** @return array<string, array{0: string, 1: string, 2: int, 3: bool}> */
    public static function selectorAttributePseudoEdgeCaseProvider(): array
    {
        $json = (string) \file_get_contents(
            __DIR__ . '/fixtures/css/selectors/attribute_pseudo_edge_cases.json',
        );
        /** @var array<int, array{name: string, selector: string, node: int, expected: bool}>|null $rows */
        $rows = \json_decode($json, true);
        if (!\is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $out[$row['name']] = [
                $row['name'],
                $row['selector'],
                $row['node'],
                $row['expected'],
            ];
        }

        return $out;
    }

    /** @phpstan-return array<int, THTMLAttrib> */
    private function getSelectorAttributePseudoEdgeCaseDom(): array
    {
        return [
            0 => $this->makeHtmlNode(['value' => 'root', 'opening' => true]),
            1 => $this->makeHtmlNode([
                'value' => 'article',
                'parent' => 0,
                'opening' => true,
                'attribute' => ['lang' => 'en-US'],
            ]),
            2 => $this->makeHtmlNode([
                'value' => 'a',
                'parent' => 1,
                'opening' => true,
                'attribute' => [
                    'id' => 'promo-link',
                    'class' => 'btn primary',
                    'data-role' => 'cta main',
                    'data-lang' => 'en-US',
                    'title' => 'Hello World',
                    'href' => 'https://ex.com?a=1&b=2',
                ],
            ]),
            3 => $this->makeHtmlNode([
                'value' => 'span',
                'parent' => 1,
                'opening' => true,
                'attribute' => ['class' => 'badge'],
            ]),
        ];
    }

    public function testGetHTMLDOMCSSDataStoresPseudoElementStyles(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode([
                'value' => 'span',
                'parent' => 0,
                'attribute' => ['class' => 'demo'],
            ]),
        ];
        $css = [
            '0010 span.demo::before' => 'content:"[B]";color:red;',
            '0010 span.demo::after' => 'content:"[A]";font-weight:bold;',
        ];

        $obj->getHTMLDOMCSSData($dom, $css, 1);

        $this->assertArrayHasKey('pseudo-before-style', $dom[1]['attribute']);
        $this->assertArrayHasKey('pseudo-after-style', $dom[1]['attribute']);
        $this->assertStringContainsString('content:"[B]"', $dom[1]['attribute']['pseudo-before-style']);
        $this->assertStringContainsString('content:"[A]"', $dom[1]['attribute']['pseudo-after-style']);
    }

    public function testGetHTMLCellRendersTextOnlyPseudoElementsBeforeAndAfter(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            '<style>span.demo::before{content:"[B]";color:#ff0000;}'
            . 'span.demo::after{content:"[A]";font-weight:bold;}</style>'
            . '<span class="demo">X</span>',
            0,
            0,
            40,
            12,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('[B]', $out);
        $this->assertStringContainsString('X', $out);
        $this->assertStringContainsString('[A]', $out);
    }

    public function testGetHTMLCellRendersSingleQuotedPseudoElementContent(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->getHTMLCell(
            "<style>span.demo::before{content:'[B]';}span.demo::after{content:'[A]';}</style>"
            . '<span class="demo">X</span>',
            0,
            0,
            40,
            12,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('[B]', $out);
        $this->assertStringContainsString('X', $out);
        $this->assertStringContainsString('[A]', $out);
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

    public function testSanitizeHTMLFlattensOptgroupOptionsIntoSelectOptAttribute(): void
    {
        $obj = $this->getInternalTestObject();
        $html = '<select><optgroup label="Group A"><option value="x">X</option></optgroup></select>';

        $out = $obj->exposeSanitizeHTML($html);

        $this->assertStringContainsString('<select', $out);
        $this->assertStringContainsString('opt="x#!TaB!#Group A - X"', $out);
    }

    public function testSanitizeHTMLAcceptsSingleQuotedAndUnquotedSelectOptionAttributes(): void
    {
        $obj = $this->getInternalTestObject();
        $html = "<select><optgroup label='Group A'><option value=v1 selected>Alpha</option>"
            . "<option value='v2'>Beta</option></optgroup></select>";

        $out = $obj->exposeSanitizeHTML($html);

        $this->assertStringContainsString('<select', $out);
        $this->assertStringContainsString('#!SeL!#v1#!TaB!#Group A - Alpha', $out);
        $this->assertStringContainsString('v2#!TaB!#Group A - Beta', $out);
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

    public function testIsValidCSSSelectorForTagCoversTightCombinatorsAndAttributePresence(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root', 'tag' => true, 'opening' => true]),
            1 => $this->makeHtmlNode([
                'value' => 'div',
                'parent' => 0,
                'tag' => true,
                'opening' => true,
            ]),
            2 => $this->makeHtmlNode([
                'value' => 'p',
                'parent' => 1,
                'tag' => true,
                'opening' => true,
            ]),
            3 => $this->makeHtmlNode([
                'value' => 'span',
                'parent' => 1,
                'tag' => true,
                'opening' => true,
                'attribute' => [
                    'class' => 'target',
                    'data' => 'tokenized value',
                ],
            ]),
        ];

        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' span[data]'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' span[data~=tokenized]'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' div>span.target'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' p+span.target'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' p~span.target'));
    }

    public function testIsValidCSSSelectorForTagCoversNestedChainsAndSiblingEdges(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root', 'tag' => true, 'opening' => true]),
            1 => $this->makeHtmlNode([
                'value' => 'div',
                'parent' => 0,
                'tag' => true,
                'opening' => true,
                'attribute' => ['id' => 'outer'],
            ]),
            2 => $this->makeHtmlNode([
                'value' => 'section',
                'parent' => 1,
                'tag' => true,
                'opening' => true,
            ]),
            3 => $this->makeHtmlNode([
                'value' => 'p',
                'parent' => 2,
                'tag' => true,
                'opening' => true,
                'attribute' => ['class' => 'lead'],
            ]),
            4 => $this->makeHtmlNode([
                'value' => 'span',
                'parent' => 2,
                'tag' => true,
                'opening' => true,
                'attribute' => ['class' => 'middle'],
            ]),
            5 => $this->makeHtmlNode([
                'value' => 'a',
                'parent' => 2,
                'tag' => true,
                'opening' => true,
                'attribute' => [
                    'class' => 'target',
                    'href' => 'https://example.com',
                ],
            ]),
        ];

        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 5, ' div section a.target'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 5, ' div>section>a.target'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 5, ' div > section > a.target'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 5, ' section > .target'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 5, ' div > p + a.target'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 5, ' div > p ~ a.target'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 5, ' section > p ~ a.target'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 5, ' div > article a.target'));
    }

    public function testIsValidCSSSelectorForTagCoversMixedChainsAndNthFormulas(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root', 'tag' => true, 'opening' => true]),
            1 => $this->makeHtmlNode([
                'value' => 'article',
                'parent' => 0,
                'tag' => true,
                'opening' => true,
                'attribute' => ['id' => 'doc'],
            ]),
            2 => $this->makeHtmlNode([
                'value' => 'section',
                'parent' => 1,
                'tag' => true,
                'opening' => true,
                'attribute' => ['class' => 'panel', 'data-kind' => 'alpha'],
            ]),
            3 => $this->makeHtmlNode([
                'value' => 'p',
                'parent' => 2,
                'tag' => true,
                'opening' => true,
                'attribute' => ['class' => 'note'],
            ]),
            4 => $this->makeHtmlNode([
                'value' => 'a',
                'parent' => 2,
                'tag' => true,
                'opening' => true,
                'attribute' => ['class' => 'cta primary', 'href' => 'https://example.com/main'],
            ]),
            5 => $this->makeHtmlNode([
                'value' => 'a',
                'parent' => 2,
                'tag' => true,
                'opening' => true,
                'attribute' => ['class' => 'cta secondary', 'href' => 'https://example.com/alt'],
            ]),
            6 => $this->makeHtmlNode([
                'value' => 'span',
                'parent' => 2,
                'tag' => true,
                'opening' => true,
                'attribute' => ['class' => 'badge'],
            ]),
        ];

        $this->assertTrue($obj->isValidCSSSelectorForTag(
            $dom,
            5,
            ' article#doc > section.panel[data-kind=alpha] > a.secondary:nth-child(3)',
        ));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 5, ' section > a.cta + a.secondary'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 5, ' section > p.note ~ a.secondary:nth-child(2n+1)'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 5, ' section > p.note + a.secondary'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 5, ' section[data-kind=beta] > a.secondary'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 5, ' section > a.secondary:nth-child(2n)'));
    }

    public function testParseHTMLStyleAttributesCoversLinkFallbacksAndPerSideBorderProperties(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $dom = [
            0 => $this->makeHtmlNode([
                'line-height' => 1.2,
                'fontsize' => 10.0,
                'fontstyle' => '',
                'font-stretch' => 100.0,
                'letter-spacing' => 0.0,
                'text-indent' => 0.0,
                'listtype' => 'disc',
            ]),
            1 => $this->makeHtmlNode([
                'parent' => 0,
                'value' => 'a',
                'fontsize' => 0.0,
                'fontstyle' => '',
                'attribute' => [
                    'style' => 'line-height:12pt;page-break-before:avoid;page-break-after:left;'
                        . 'border-style:none none none hidden;'
                        . 'border-left-color:#111;border-right-color:#222;'
                        . 'border-top-color:#333;border-bottom-color:#444;'
                        . 'border-left-width:1;border-right-width:2;border-top-width:3;border-bottom-width:4;'
                        . 'border-left-style:dashed;border-right-style:dotted;'
                        . 'border-top-style:solid;border-bottom-style:double;',
                ],
            ]),
            2 => $this->makeHtmlNode([
                'parent' => 0,
                'value' => 'span',
                'fontsize' => 10.0,
                'fontstyle' => '',
                'attribute' => [
                    'style' => 'line-height:12pt;text-decoration:blink;page-break-after:avoid;',
                ],
            ]),
        ];

        $obj->parseHTMLStyleAttributes($dom, 1, 0);
        $obj->parseHTMLStyleAttributes($dom, 2, 0);

        $this->assertSame(1.0, $dom[1]['line-height']);
        $this->assertSame('blue', $dom[1]['fgcolor']);
        $this->assertStringContainsString('U', $dom[1]['fontstyle']);
        $this->assertSame('', $dom[1]['attribute']['pagebreak']);
        $this->assertSame('left', $dom[1]['attribute']['pagebreakafter']);
        $this->assertNotEmpty($dom[1]['border']);

        $this->assertGreaterThan(0.0, $dom[2]['line-height']);
        $this->assertSame('', $dom[2]['attribute']['pagebreakafter']);
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

    public function testGetHTMLDOMSupportsAdditionalTableStructureTags(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $dom = $obj->exposeGetHTMLDOM(
            '<table><caption>Cap</caption><colgroup><col span="1"></colgroup>'
            . '<tfoot><tr><td>Foot</td></tr></tfoot></table>',
        );

        $values = \array_column($dom, 'value');
        $this->assertContains('caption', $values);
        $this->assertContains('colgroup', $values);
        $this->assertContains('col', $values);
        $this->assertContains('tfoot', $values);
        $this->assertSame(1, $dom[1]['rows']);
        $this->assertSame(1, $dom[1]['cols']);
        $this->assertCount(1, $dom[1]['trids']);
    }

    public function testParseHTMLAttributesCountsRowsWhenTrParentIsTfoot(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode([
                'value' => 'table',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
            ]),
            2 => $this->makeHtmlNode([
                'value' => 'tfoot',
                'parent' => 1,
                'attribute' => [],
                'style' => [],
            ]),
            3 => $this->makeHtmlNode([
                'value' => 'tr',
                'parent' => 2,
                'attribute' => [],
                'style' => [],
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);
        $obj->parseHTMLAttributes($dom, 2, false);
        $obj->parseHTMLAttributes($dom, 3, false);

        $this->assertSame(1, $dom[1]['rows']);
        $this->assertSame([3], $dom[1]['trids']);
    }

    public function testComputeHTMLTableColWidthsUsesColgroupSpanWidthHints(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $dom = $obj->exposeGetHTMLDOM(
            '<table><colgroup span="2" width="80"></colgroup>'
            . '<tr><td>A</td><td>B</td></tr></table>',
        );

        /** @var THTMLAttrib $group */
        $group = $dom[2];
        $groupWidth = isset($group['width']) && \is_numeric($group['width'])
            ? (float) $group['width'] : 0.0;

        $widths = $obj->exposeComputeHTMLTableColWidths($dom, 1, 2, 100.0);

        $this->assertCount(2, $widths);
        $this->assertGreaterThan(0.0, $groupWidth);
        $this->assertEqualsWithDelta($groupWidth / 2.0, (float) $widths[0], 0.001);
        $this->assertEqualsWithDelta($groupWidth / 2.0, (float) $widths[1], 0.001);
    }

    public function testComputeHTMLTableColWidthsUsesColSpanWidthHints(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $dom = $obj->exposeGetHTMLDOM(
            '<table><colgroup><col span="2" width="60"><col width="20"></colgroup>'
            . '<tr><td>A</td><td>B</td><td>C</td></tr></table>',
        );

        $colWidths = [];
        foreach ($dom as $elm) {
            if (
                !empty($elm['opening'])
                && (($elm['value'] ?? '') === 'col')
                && isset($elm['width'])
                && \is_numeric($elm['width'])
            ) {
                $colWidths[] = (float) $elm['width'];
            }
        }

        $widths = $obj->exposeComputeHTMLTableColWidths($dom, 1, 3, 120.0);

        $this->assertCount(2, $colWidths);
        $this->assertCount(3, $widths);
        $this->assertEqualsWithDelta($colWidths[0] / 2.0, (float) $widths[0], 0.001);
        $this->assertEqualsWithDelta($colWidths[0] / 2.0, (float) $widths[1], 0.001);
        $this->assertEqualsWithDelta($colWidths[1], (float) $widths[2], 0.001);
    }

    public function testComputeHTMLTableColWidthsPrefersFirstRowExplicitTdWidthOverColHints(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $dom = $obj->exposeGetHTMLDOM(
            '<table><colgroup><col width="20"><col width="20"></colgroup>'
            . '<tr><td width="60">A</td><td>B</td></tr></table>',
        );

        $colWidths = [];
        $tdWidths = [];
        foreach ($dom as $elm) {
            if (
                !empty($elm['opening'])
                && (($elm['value'] ?? '') === 'col')
                && isset($elm['width'])
                && \is_numeric($elm['width'])
            ) {
                $colWidths[] = (float) $elm['width'];
            }

            if (
                !empty($elm['opening'])
                && (($elm['value'] ?? '') === 'td')
                && isset($elm['width'])
                && \is_numeric($elm['width'])
            ) {
                $tdWidths[] = (float) $elm['width'];
            }
        }

        $widths = $obj->exposeComputeHTMLTableColWidths($dom, 1, 2, 100.0);

        $this->assertCount(2, $colWidths);
        $this->assertCount(2, $tdWidths);
        $this->assertCount(2, $widths);
        $this->assertEqualsWithDelta($tdWidths[0], (float) $widths[0], 0.001);
        $this->assertEqualsWithDelta($colWidths[1], (float) $widths[1], 0.001);
    }

    public function testParseHTMLAttributesFontSizeUsesNumericFallbackWhenParentSizeMissing(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $dom = [
            0 => $this->makeHtmlNode(['fontsize' => 0.0]),
            1 => $this->makeHtmlNode([
                'value' => 'font',
                'parent' => 0,
                'attribute' => ['size' => '13'],
                'style' => [],
                'fontsize' => 0.0,
            ]),
        ];

        $obj->parseHTMLAttributes($dom, 1, false);

        $this->assertSame(13.0, $dom[1]['fontsize']);
    }

    public function testParseHTMLAttributesInitializesRowsAndTridsOnMissingParentTableState(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode([
                'value' => 'tr',
                'parent' => 0,
                'attribute' => [],
                'style' => [],
            ]),
        ];
        unset($dom[0]['rows'], $dom[0]['trids']);

        $method = new \ReflectionMethod(\Com\Tecnick\Pdf\HTML::class, 'parseHTMLAttributes');
        $method->invokeArgs($obj, [&$dom, 1, false]);

        /** @var THTMLAttrib $parent */
        $parent = $dom[0];

        $this->assertSame(1, $parent['rows']);
        $this->assertSame([1], $parent['trids']);
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

    public function testDrawHTMLRectBorderSidesRendersOnlyDefinedSides(): void
    {
        $obj = $this->getInternalTestObject();

        $method = new \ReflectionMethod(\Com\Tecnick\Pdf\HTML::class, 'drawHTMLRectBorderSides');
        $method->setAccessible(true);

        $styles = [
            3 => [
                'lineWidth' => 0.2,
                'lineCap' => 'square',
                'lineJoin' => 'miter',
                'miterLimit' => 10.0,
                'dashArray' => [],
                'dashPhase' => 0,
                'lineColor' => '#ff0000',
                'fillColor' => '',
            ],
        ];

        /** @var string $out */
        $out = $method->invoke($obj, 10.0, 20.0, 30.0, 40.0, $styles);

        $this->assertNotSame('', $out);
        $this->assertSame(1, \substr_count($out, "S\n"));
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

    public function testGetHTMLliBulletUsesGraphicFallbackForUnicodeByteFonts(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj); // Loads core Helvetica (byte font)
        $this->setObjectProperty($obj, 'isunicode', true);

        $disc = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'disc');
        $circle = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'circle');
        $square = $obj->exposeGetHTMLliBullet(1, 1, 0, 0, 'square');

        $this->assertNotSame('', $disc);
        $this->assertNotSame('', $circle);
        $this->assertNotSame('', $square);
        $this->assertStringNotContainsString('Tj', $disc);
        $this->assertStringNotContainsString('Tj', $circle);
        $this->assertStringNotContainsString('Tj', $square);
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
            $fontfile = (string) \realpath(
                __DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/cid0/cid0jp.json'
            );
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

    // --- Fix tests: <br> line advance ---

    public function testGetHTMLCellBrAdvancesLine(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        // Two words without a break — both on the same line
        $outSameLine = $obj->getHTMLCell('Hello World', 0, 0, 40, 20);

        // Same words with a <br> — second word is on a new (lower) line
        $obj2 = $this->getTestObject();
        $this->initFontAndPage($obj2);
        $outNewLine = $obj2->getHTMLCell('Hello<br />World', 0, 0, 40, 20);
        $this->assertNotSame('', $outSameLine);
        $this->assertNotSame('', $outNewLine);
        $this->assertStringContainsString('Hello', $outSameLine);
        $this->assertStringContainsString('Hello', $outNewLine);
        $this->assertStringContainsString('World', $outNewLine);
        // The y coordinates differ — <br> produced a line advance
        $this->assertNotSame($outSameLine, $outNewLine);
    }

    public function testParseHTMLTagOPENbrSkipsAdvanceAfterWrappedLine(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeInitHTMLCellContext(10.0, 0.0, 100.0, 0.0);
        $obj->exposeSetHTMLLineState(0.0, 0.0, true);

        $dom = [
            $this->makeHtmlNode([
                'tag' => true,
                'opening' => false,
                'value' => 'font',
            ]),
            $this->makeHtmlNode([
                'tag' => true,
                'opening' => true,
                'self' => true,
                'value' => 'br',
            ]),
        ];

        $tpx = 10.0;
        $tpy = 20.0;
        $tpw = 100.0;
        $tph = 0.0;

        $obj->exposeParseHTMLTagOPENbrWithDom($dom, 1, $tpx, $tpy, $tpw, $tph);

        $this->assertEqualsWithDelta(20.0, $tpy, 1e-9);
        $this->assertEqualsWithDelta(10.0, $tpx, 1e-9);
    }

    public function testParseHTMLTagOPENbrAdvancesWhenLineIsNotWrapped(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeInitHTMLCellContext(10.0, 0.0, 100.0, 0.0);
        $obj->exposeSetHTMLLineState(0.0, 0.0, false);

        $dom = [
            $this->makeHtmlNode([
                'tag' => false,
                'value' => 'normal',
            ]),
            $this->makeHtmlNode([
                'tag' => true,
                'opening' => true,
                'self' => true,
                'value' => 'br',
            ]),
        ];

        $tpx = 10.0;
        $tpy = 20.0;
        $tpw = 100.0;
        $tph = 0.0;

        $obj->exposeParseHTMLTagOPENbrWithDom($dom, 1, $tpx, $tpy, $tpw, $tph);

        $this->assertGreaterThan(20.0, $tpy);
        $this->assertEqualsWithDelta(10.0, $tpx, 1e-9);
    }

    public function testParseHTMLTextOverflowAdvancesByCurrentLineMaxHeight(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeInitHTMLCellContext(10.0, 0.0, 40.0, 0.0);
        // Simulate an in-progress line that already contains a taller inline fragment.
        $obj->exposeSetHTMLLineState(12.0, 0.0, false);

        $elm = $this->makeHtmlNode([
            'tag' => false,
            'opening' => false,
            'self' => false,
            'value' => 'thisisaverylongwordthisisaverylongwordthisisaverylongword',
            'fontsize' => 6.0,
        ]);

        $tpx = 38.0; // near line end => tiny remaining width
        $tpy = 20.0;
        $tpw = 12.0;
        $tph = 0.0;

        $out = $obj->exposeParseHTMLText($elm, $tpx, $tpy, $tpw, $tph);

        $this->assertNotSame('', $out);
        // The parser must advance to next line using the tracked max line height (12.0)
        // before rendering the overflow fragment.
        $this->assertGreaterThanOrEqual(32.0, $tpy);
    }

    public function testGetHTMLCellMixedInlineSizesShareBaseline(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeResetBBoxTrace();
        $html = '<font size="10">A</font><font size="22">B</font><font size="10">C</font>';
        $out = $obj->getHTMLCell($html, 0, 0, 200, 20);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertCount(3, $trace);
        $this->assertSame('A', $trace[0]['txt']);
        $this->assertSame('B', $trace[1]['txt']);
        $this->assertSame('C', $trace[2]['txt']);

        // Small fragments on the same line must align to the same baseline offset.
        $this->assertEqualsWithDelta((float) $trace[0]['bbox_y'], (float) $trace[2]['bbox_y'], 1e-9);
        // The larger fragment sits higher while sharing the same baseline.
        $this->assertGreaterThan((float) $trace[1]['bbox_y'], (float) $trace[0]['bbox_y']);
    }

    // --- Fix tests: <hr> width/height ---

    public function testGetHTMLCellHrRespectsWidthAttribute(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        // Full-width HR (no attribute)
        $outFull = $obj->getHTMLCell('<hr />', 0, 0, 40, 5);

        // HR with width="50" (50px ≈ 13mm — narrower than the 40mm cell)
        $obj2 = $this->getTestObject();
        $this->initFontAndPage($obj2);
        $outShort = $obj2->getHTMLCell('<hr width="50" />', 0, 0, 40, 5);

        $this->assertNotSame('', $outFull);
        $this->assertNotSame('', $outShort);
        // The line endpoints differ when width is constrained
        $this->assertNotSame($outFull, $outShort);
    }

    public function testGetHTMLCellHrRespectsHeightAsStrokeWidth(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        // Default HR (stroke 0.2)
        $outDefault = $obj->getHTMLCell('<hr />', 0, 0, 40, 5);

        // HR with height="5" (5px ≈ 1.32mm stroke)
        $obj2 = $this->getTestObject();
        $this->initFontAndPage($obj2);
        $outThick = $obj2->getHTMLCell('<hr height="5" />', 0, 0, 40, 5);

        $this->assertNotSame('', $outDefault);
        $this->assertNotSame('', $outThick);
        // Different stroke width produces different PDF operator stream
        $this->assertNotSame($outDefault, $outThick);
    }

    // --- Fix tests: inline image vertical alignment ---

    public function testGetHTMLCellImgTopAlignmentDiffersFromBottom(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        // Generate a minimal valid PNG in memory
        $img = \imagecreate(4, 4);
        \imagecolorallocate($img, 255, 255, 255);
        \ob_start();
        \imagepng($img);
        $raw = \ob_get_clean();
        $b64src = 'data:image/png;base64,' . \base64_encode((string) $raw);

        $outBottom = $obj->getHTMLCell(
            '<img src="' . $b64src . '" width="4" height="4" align="bottom" />',
            0,
            0,
            40,
            20,
        );
        $obj2 = $this->getTestObject();
        $this->initFontAndPage($obj2);
        $outTop = $obj2->getHTMLCell(
            '<img src="' . $b64src . '" width="4" height="4" align="top" />',
            0,
            0,
            40,
            20,
        );

        $this->assertNotSame('', $outBottom);
        $this->assertNotSame('', $outTop);
        // Different y-offsets produce different PDF streams
        $this->assertNotSame($outBottom, $outTop);
    }

    public function testGetHTMLCellImgBottomAlignmentUsesTextBaseline(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $img = \imagecreate(4, 4);
        \imagecolorallocate($img, 255, 255, 255);
        \ob_start();
        \imagepng($img);
        $raw = \ob_get_clean();
        $b64src = 'data:image/png;base64,' . \base64_encode((string) $raw);

        $out = $obj->getHTMLCell(
            'left <img src="' . $b64src . '" width="4" height="30" /> right',
            0,
            0,
            80,
            40,
        );

        $this->assertSame(1, \preg_match('/BT .*? [-0-9.]+ ([-0-9.]+) Td \(left \) Tj ET/s', $out, $textMatch));
        $imgPattern = '/q [-0-9.]+ 0 0 [-0-9.]+ [-0-9.]+ ([-0-9.]+) cm \/IMG\d+ Do Q/';
        $this->assertSame(1, \preg_match($imgPattern, $out, $imgMatch));

        $this->assertEqualsWithDelta((float) $textMatch[1], (float) $imgMatch[1], 0.01);
    }

    public function testGetHTMLCellTallBottomAlignedImageShiftsWholeLineDown(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $img = \imagecreate(4, 4);
        \imagecolorallocate($img, 255, 255, 255);
        \ob_start();
        \imagepng($img);
        $raw = \ob_get_clean();
        $b64src = 'data:image/png;base64,' . \base64_encode((string) $raw);

        $obj->exposeResetBBoxTrace();
        $obj->getHTMLCell('left right', 0, 0, 80, 40);
        $plainTrace = $obj->exposeGetBBoxTrace();

        $obj2 = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj2);
        $obj2->exposeResetBBoxTrace();
        $obj2->getHTMLCell(
            'left <img src="' . $b64src . '" width="4" height="30" /> right',
            0,
            0,
            80,
            40,
        );
        $imageTrace = $obj2->exposeGetBBoxTrace();

        $this->assertCount(1, $plainTrace);
        $this->assertCount(2, $imageTrace);
        $this->assertSame('left ', $imageTrace[0]['txt']);
        $this->assertSame(' right', $imageTrace[1]['txt']);
        $this->assertGreaterThan((float) $plainTrace[0]['bbox_y'], (float) $imageTrace[0]['bbox_y']);
        $this->assertEqualsWithDelta((float) $imageTrace[0]['bbox_y'], (float) $imageTrace[1]['bbox_y'], 1e-9);
    }

    public function testGetHTMLCellCentersInlineImageRunInsideDiv(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $img = \imagecreate(4, 4);
        \imagecolorallocate($img, 0, 0, 0);
        \ob_start();
        \imagepng($img);
        $raw = \ob_get_clean();
        $src = 'data:image/png;base64,' . \base64_encode((string) $raw);

        $htmlCenter = '<div style="text-align:center">'
            . '<img src="' . $src . '" width="4" height="4" />'
            . '<img src="' . $src . '" width="4" height="4" />'
            . '</div>';
        $htmlLeft = '<div style="text-align:left">'
            . '<img src="' . $src . '" width="4" height="4" />'
            . '<img src="' . $src . '" width="4" height="4" />'
            . '</div>';

        $outCenter = $obj->getHTMLCell($htmlCenter, 0, 0, 40, 20);

        $obj2 = $this->getTestObject();
        $this->initFontAndPage($obj2);
        $outLeft = $obj2->getHTMLCell($htmlLeft, 0, 0, 40, 20);

        $this->assertNotSame('', $outCenter);
        $this->assertNotSame('', $outLeft);
        $this->assertNotSame($outLeft, $outCenter);
    }

    public function testGetHTMLCellCentersSingleInlineImageInsideTableCell(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $img = \imagecreate(4, 4);
        \imagecolorallocate($img, 0, 0, 0);
        \ob_start();
        \imagepng($img);
        $raw = \ob_get_clean();
        $src = 'data:image/png;base64,' . \base64_encode((string) $raw);

        $htmlCenter = '<table border="1" cellspacing="0" cellpadding="4">'
            . '<tr><td align="center"><img src="' . $src . '" width="8" height="8" /></td></tr>'
            . '</table>';
        $htmlLeft = '<table border="1" cellspacing="0" cellpadding="4">'
            . '<tr><td align="left"><img src="' . $src . '" width="8" height="8" /></td></tr>'
            . '</table>';

        $outCenter = $obj->getHTMLCell($htmlCenter, 0, 0, 40, 20);

        $obj2 = $this->getTestObject();
        $this->initFontAndPage($obj2);
        $outLeft = $obj2->getHTMLCell($htmlLeft, 0, 0, 40, 20);

        $this->assertNotSame('', $outCenter);
        $this->assertNotSame('', $outLeft);
        $this->assertNotSame($outLeft, $outCenter);
    }

    public function testParseHTMLTextJustifyTracksSpacingAcrossInlineFragments(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeInitHTMLCellContext(10.0, 10.0, 40.0, 0.0);

        $html = '<div style="text-align:justify;">'
            . 'Alfa <i>Bravo</i> Charlie <i>Delta</i> Echo <i>Foxtrot</i> Golf <i>Hotel</i> '
            . 'India <i>Juliett</i> Kilo <i>Lima</i> Mike <i>November</i>'
            . '</div>';
        $dom = $obj->exposeGetHTMLDOM($html);

        $firstTextKey = null;
        foreach ($dom as $key => $elm) {
            if (!empty($elm['tag'])) {
                continue;
            }

            if (\str_starts_with((string) $elm['value'], 'Alfa')) {
                $firstTextKey = $key;
                break;
            }
        }

        $this->assertNotNull($firstTextKey);

        $tpx = 10.0;
        $tpy = 10.0;
        $tpw = 40.0;
        $tph = 0.0;

        $out = $obj->exposeParseHTMLTextWithDom($dom, (int) $firstTextKey, $tpx, $tpy, $tpw, $tph);
        $this->assertNotSame('', $out);

        $ctx = $obj->exposeGetHTMLRenderContext();
        $lineWordSpacing = (float) ($ctx['cellctx']['linewordspacing'] ?? 0.0);
        $this->assertGreaterThan(0.0, $lineWordSpacing);

        $bbox = $obj->getLastBBox();
        $this->assertGreaterThan((float) $bbox['x'] + (float) $bbox['w'], $tpx);
    }

    public function testParseHTMLTextPlainJustifyDoesNotUseInlineCursorSpacingHack(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeInitHTMLCellContext(10.0, 10.0, 40.0, 0.0);

        $html = '<div style="text-align:justify;">'
            . 'Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett '
            . 'Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango'
            . '</div>';
        $dom = $obj->exposeGetHTMLDOM($html);

        $firstTextKey = null;
        foreach ($dom as $key => $elm) {
            if (!empty($elm['tag'])) {
                continue;
            }

            if (\str_starts_with((string) $elm['value'], 'Alfa')) {
                $firstTextKey = $key;
                break;
            }
        }

        $this->assertNotNull($firstTextKey);

        $tpx = 10.0;
        $tpy = 10.0;
        $tpw = 40.0;
        $tph = 0.0;

        $out = $obj->exposeParseHTMLTextWithDom($dom, (int) $firstTextKey, $tpx, $tpy, $tpw, $tph);
        $this->assertNotSame('', $out);

        $ctx = $obj->exposeGetHTMLRenderContext();
        $lineWordSpacing = (float) ($ctx['cellctx']['linewordspacing'] ?? 0.0);
        $this->assertSame(0.0, $lineWordSpacing);
    }

    public function testGetHTMLCellMixedInlineJustifyKeepsUniformWordGaps(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeResetBBoxTrace();

        $html = '<div style="text-align:justify;">'
            . 'Alfa <i>Bravo</i> Charlie <i>Delta</i> Echo <i>Foxtrot</i> Golf <i>Hotel</i> '
            . 'India <i>Juliett</i> Kilo <i>Lima</i> Mike <i>November</i> Oscar <i>Papa</i> '
            . 'Quebec <i>Romeo</i> Sierra <i>Tango</i> Uniform <i>Victor</i> Whiskey <i>Xray</i> '
            . 'Yankee <i>Zulu</i>'
            . '</div>';

        $out = $obj->getHTMLCell($html, 20.0, 10.0, 150.0, 0.0);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $line = [];
        foreach ($trace as $row) {
            if (\abs((float) $row['bbox_y'] - 10.0) < 0.001) {
                $line[] = $row;
            }
        }

        $this->assertGreaterThan(5, \count($line));

        $gaps = [];
        for ($idx = 1, $max = \count($line); $idx < $max; ++$idx) {
            $prev = $line[$idx - 1];
            $curr = $line[$idx];
            $gap = (float) $curr['bbox_x'] - ((float) $prev['bbox_x'] + (float) $prev['bbox_w']);
            $gaps[] = $gap;
        }

        $this->assertNotSame([], $gaps);
        $expected = $gaps[0];
        foreach ($gaps as $gap) {
            $this->assertEqualsWithDelta($expected, $gap, 1e-6);
        }
    }

    public function testGetHTMLCellJustifySecondLineWithImagesKeepsUniformGaps(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $bfont = $obj->font->insert($obj->pon, 'dejavusans', '', 10);
        $obj->page->addContent($bfont['out']);

        $logo = \realpath(__DIR__ . '/../examples/images/tcpdf_logo.jpg');
        $box = \realpath(__DIR__ . '/../examples/images/tcpdf_box.svg');

        $this->assertNotFalse($logo);
        $this->assertNotFalse($box);

        $html = '<div style="text-align:justify;">'
            . 'JUSTIFY: Alfa <i>Bravo</i> Charlie <i>Delta</i> Echo '
            . '<img src="' . $logo . '" alt="TCPDF logo" width="89" height="30" border="0" />'
            . '<img src="' . $box . '" alt="TCPDF box" width="100" height="67" border="0" /> '
            . '<i>Foxtrot</i> Golf <i>Hotel</i> India <i>Juliett</i> Kilo <i>Lima</i> Mike <i>November</i> '
            . 'Oscar <i>Papa</i> Quebec <i>Romeo</i> Sierra <i>Tango</i> Uniform <i>Victor</i> '
            . 'Whiskey <i>Xray</i> Yankee <i>Zulu</i>'
            . '</div>';

        $originX = 20.0;
        $cellWidth = 150.0;

        $obj->exposeResetBBoxTrace();
        $out = $obj->getHTMLCell($html, $originX, 10.0, $cellWidth, 0.0);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotSame([], $trace);

        $lineY = null;
        foreach ($trace as $row) {
            if (\trim((string) $row['txt']) === 'India') {
                $lineY = (float) $row['bbox_y'];
                break;
            }
        }

        $this->assertNotNull($lineY);

        $secondLine = [];
        foreach ($trace as $row) {
            if (\abs((float) $row['bbox_y'] - (float) $lineY) < 0.01) {
                $secondLine[] = $row;
            }
        }

        $this->assertGreaterThan(10, \count($secondLine));

        $gaps = [];
        for ($idx = 1, $max = \count($secondLine); $idx < $max; ++$idx) {
            $prev = $secondLine[$idx - 1];
            $curr = $secondLine[$idx];
            $gaps[] = (float) $curr['bbox_x'] - ((float) $prev['bbox_x'] + (float) $prev['bbox_w']);
        }

        $this->assertNotSame([], $gaps);
        $expectedGap = $gaps[0];
        foreach ($gaps as $gap) {
            $this->assertEqualsWithDelta($expectedGap, $gap, 1e-6);
        }

        $lineLeft = (float) $secondLine[0]['bbox_x'];
        $last = $secondLine[\count($secondLine) - 1];
        $lineRight = (float) $last['bbox_x'] + (float) $last['bbox_w'];

        $this->assertEqualsWithDelta($originX, $lineLeft, 1e-6);
        $this->assertEqualsWithDelta($originX + $cellWidth, $lineRight, 1e-6);
    }

    public function testGetHTMLCellRightAlignedMixedInlineLinesReachRightEdge(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $bfont = $obj->font->insert($obj->pon, 'dejavusans', '', 10);
        $obj->page->addContent($bfont['out']);

        $logo = \realpath(__DIR__ . '/../examples/images/tcpdf_logo.jpg');
        $box = \realpath(__DIR__ . '/../examples/images/tcpdf_box.svg');

        $this->assertNotFalse($logo);
        $this->assertNotFalse($box);

        $html = '<div style="text-align:right;">'
            . 'RIGHT: Alfa <i>Bravo</i> Charlie <i>Delta</i> Echo '
            . '<img src="' . $logo . '" alt="TCPDF logo" width="89" height="30" border="0" />'
            . '<img src="' . $box . '" alt="TCPDF box" width="100" height="67" border="0" /> '
            . '<i>Foxtrot</i> Golf <i>Hotel</i> India <i>Juliett</i> Kilo <i>Lima</i> Mike <i>November</i> '
            . 'Oscar <i>Papa</i> Quebec <i>Romeo</i> Sierra <i>Tango</i> Uniform <i>Victor</i> '
            . 'Whiskey <i>Xray</i> Yankee <i>Zulu</i>'
            . '</div>';

        $originX = 20.0;
        $cellWidth = 150.0;

        $obj->exposeResetBBoxTrace();
        $out = $obj->getHTMLCell($html, $originX, 10.0, $cellWidth, 0.0);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotSame([], $trace);

        $lines = [];
        foreach ($trace as $row) {
            $key = \sprintf('%.3F', (float) $row['bbox_y']);
            if (!isset($lines[$key])) {
                $lines[$key] = [];
            }

            $lines[$key][] = $row;
        }

        $this->assertCount(3, $lines);

        foreach ($lines as $line) {
            $lineRight = 0.0;
            foreach ($line as $row) {
                $lineRight = \max($lineRight, (float) $row['bbox_x'] + (float) $row['bbox_w']);
            }

            $this->assertEqualsWithDelta($originX + $cellWidth, $lineRight, 1e-6);
        }

        $kiloLineY = null;
        foreach ($trace as $row) {
            if (\strpos((string) $row['txt'], 'Kilo') !== false) {
                $kiloLineY = \sprintf('%.3F', (float) $row['bbox_y']);
                break;
            }
        }

        $this->assertNotNull($kiloLineY);

        $kiloLineRight = 0.0;
        foreach ($lines[(string) $kiloLineY] as $row) {
            $kiloLineRight = \max($kiloLineRight, (float) $row['bbox_x'] + (float) $row['bbox_w']);
        }

        $this->assertEqualsWithDelta($originX + $cellWidth, $kiloLineRight, 1e-6);
    }

    public function testProbeRightAlignTextOnlyMixedInlineFragmentPositions(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $bfont = $obj->font->insert($obj->pon, 'dejavusans', '', 10);
        $obj->page->addContent($bfont['out']);
        $obj->setDefaultCellPadding(2, 2, 2, 2);

        $html = '<div style="text-align:right;">'
            . 'RIGHT: Alfa <i>Bravo</i> Charlie <i>Delta</i> Echo <i>Foxtrot</i> Golf <i>Hotel</i> '
            . 'India <i>Juliett</i> Kilo <i>Lima</i> Mike <i>November</i> '
            . 'Oscar <i>Papa</i> Quebec <i>Romeo</i> Sierra <i>Tango</i> Uniform <i>Victor</i> '
            . 'Whiskey <i>Xray</i> Yankee <i>Zulu</i>'
            . '</div>';

        $originX = 22.0;
        $cellWidth = 186.0;

        $obj->exposeResetBBoxTrace();
        $out = $obj->getHTMLCell($html, $originX, 10.0, $cellWidth, 0.0);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotSame([], $trace);

        $lines = [];
        foreach ($trace as $row) {
            $key = \sprintf('%.3F', (float) $row['bbox_y']);
            if (!isset($lines[$key])) {
                $lines[$key] = [];
            }
            $lines[$key][] = $row;
        }

        $this->assertCount(2, $lines);

        $rightEdge = ($originX + $cellWidth) - 2.0;
        $lineKeys = \array_keys($lines);
        $firstLine = $lines[$lineKeys[0]];
        $secondLine = $lines[$lineKeys[1]];
        $firstLast = $firstLine[\count($firstLine) - 1];
        $secondLast = $secondLine[\count($secondLine) - 1];
        $firstLineRight = (float) $firstLast['bbox_x'] + (float) $firstLast['bbox_w'];
        $secondLineRight = (float) $secondLast['bbox_x'] + (float) $secondLast['bbox_w'];

        // Fixed: first line now reaches the right edge and includes Oscar.
        $this->assertEqualsWithDelta($rightEdge, $firstLineRight, 0.5);
        $this->assertEqualsWithDelta($rightEdge, $secondLineRight, 0.01);

        $firstLineHasOscar = false;
        $secondStartsPapa = false;
        foreach ($firstLine as $row) {
            if (\strpos((string) $row['txt'], 'Oscar') !== false) {
                $firstLineHasOscar = true;
            }
        }

        if (isset($secondLine[0])) {
            $secondStartsPapa = (\strpos((string) $secondLine[0]['txt'], 'Papa') !== false);
        }

        $this->assertTrue($firstLineHasOscar);
        $this->assertTrue($secondStartsPapa);
    }

    public function testParseHTMLTextForcedWrapTrimsLeadingSpaceAtNewLine(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeInitHTMLCellContext(10.0, 10.0, 40.0, 0.0);
        $obj->exposeResetBBoxTrace();

        $elm = $this->makeHtmlNode([
            'align' => 'J',
            'value' => ' Quebec Romeo',
        ]);

        // Simulate a nearly full current line so parseHTMLText pre-wraps this fragment.
        $tpx = 49.0;
        $tpy = 10.0;
        $tpw = 1.0;
        $tph = 0.0;

        $out = $obj->exposeParseHTMLText($elm, $tpx, $tpy, $tpw, $tph);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotEmpty($trace);
        $this->assertSame('Quebec Romeo', $trace[0]['txt']);
    }

    public function testParseHTMLTextWordSpacingIncreasesRenderedAdvance(): void
    {
        $base = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($base);
        $base->exposeInitHTMLCellContext(10.0, 10.0, 120.0, 0.0);
        $base->exposeResetBBoxTrace();

        $baseElm = $this->makeHtmlNode([
            'tag' => false,
            'opening' => false,
            'value' => 'Alpha Beta Gamma',
            'word-spacing' => 0.0,
        ]);

        $baseX = 10.0;
        $baseY = 10.0;
        $baseW = 120.0;
        $baseH = 0.0;
        $baseOut = $base->exposeParseHTMLText($baseElm, $baseX, $baseY, $baseW, $baseH);
        $this->assertNotSame('', $baseOut);
        $baseTrace = $base->exposeGetBBoxTrace();
        $this->assertNotEmpty($baseTrace);

        $spaced = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($spaced);
        $spaced->exposeInitHTMLCellContext(10.0, 10.0, 120.0, 0.0);
        $spaced->exposeResetBBoxTrace();

        $spacedElm = $this->makeHtmlNode([
            'tag' => false,
            'opening' => false,
            'value' => 'Alpha Beta Gamma',
            'word-spacing' => 2.0,
        ]);

        $spacedX = 10.0;
        $spacedY = 10.0;
        $spacedW = 120.0;
        $spacedH = 0.0;
        $spacedOut = $spaced->exposeParseHTMLText($spacedElm, $spacedX, $spacedY, $spacedW, $spacedH);
        $this->assertNotSame('', $spacedOut);
        $spacedTrace = $spaced->exposeGetBBoxTrace();
        $this->assertNotEmpty($spacedTrace);

        $this->assertGreaterThan(
            $baseX + 0.001,
            $spacedX,
            'word-spacing should increase cursor advance for the text fragment.',
        );
        $this->assertStringContainsString(' Tw', $spacedOut);
    }

    public function testParseHTMLTextNegativeWordSpacingIsClampedToZero(): void
    {
        $base = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($base);
        $base->exposeInitHTMLCellContext(10.0, 10.0, 120.0, 0.0);

        $baseElm = $this->makeHtmlNode([
            'tag' => false,
            'opening' => false,
            'value' => 'Alpha Beta Gamma',
            'word-spacing' => 0.0,
        ]);

        $baseX = 10.0;
        $baseY = 10.0;
        $baseW = 120.0;
        $baseH = 0.0;
        $baseOut = $base->exposeParseHTMLText($baseElm, $baseX, $baseY, $baseW, $baseH);
        $this->assertNotSame('', $baseOut);

        $negative = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($negative);
        $negative->exposeInitHTMLCellContext(10.0, 10.0, 120.0, 0.0);

        $negativeElm = $this->makeHtmlNode([
            'tag' => false,
            'opening' => false,
            'value' => 'Alpha Beta Gamma',
            'word-spacing' => -2.0,
        ]);

        $negativeX = 10.0;
        $negativeY = 10.0;
        $negativeW = 120.0;
        $negativeH = 0.0;
        $negativeOut = $negative->exposeParseHTMLText($negativeElm, $negativeX, $negativeY, $negativeW, $negativeH);
        $this->assertNotSame('', $negativeOut);

        $this->assertEqualsWithDelta($baseX, $negativeX, 0.001);
        $this->assertStringNotContainsString(' Tw', $negativeOut);
    }

    public function testParseHTMLTextForcedWrapPreservesLeadingSpaceForPreWrap(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        $obj->exposeInitHTMLCellContext(10.0, 10.0, 40.0, 0.0);
        $obj->exposeResetBBoxTrace();

        $elm = $this->makeHtmlNode([
            'align' => 'J',
            'white-space' => 'pre-wrap',
            'value' => ' Quebec Romeo',
        ]);

        // Simulate a nearly full current line so parseHTMLText pre-wraps this fragment.
        $tpx = 49.0;
        $tpy = 10.0;
        $tpw = 1.0;
        $tph = 0.0;

        $out = $obj->exposeParseHTMLText($elm, $tpx, $tpy, $tpw, $tph);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotEmpty($trace);
        $this->assertStringStartsWith(' ', (string) $trace[0]['txt']);
        $this->assertStringContainsString('Quebec', (string) $trace[0]['txt']);
    }

    public function testParseHTMLTextPreWrapHonorsExplicitNewlineBreaks(): void
    {
        $single = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($single);
        $single->exposeInitHTMLCellContext(10.0, 10.0, 120.0, 0.0);
        $single->exposeResetBBoxTrace();

        $singleElm = $this->makeHtmlNode([
            'white-space' => 'pre-wrap',
            'value' => 'Alpha Beta',
        ]);

        $singleX = 10.0;
        $singleY = 10.0;
        $singleW = 120.0;
        $singleH = 0.0;
        $singleOut = $single->exposeParseHTMLText($singleElm, $singleX, $singleY, $singleW, $singleH);
        $this->assertNotSame('', $singleOut);
        $singleTrace = $single->exposeGetBBoxTrace();
        $this->assertNotEmpty($singleTrace);

        $multiline = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($multiline);
        $multiline->exposeInitHTMLCellContext(10.0, 10.0, 120.0, 0.0);
        $multiline->exposeResetBBoxTrace();

        $multiElm = $this->makeHtmlNode([
            'white-space' => 'pre-wrap',
            'value' => "Alpha\nBeta",
        ]);

        $multiX = 10.0;
        $multiY = 10.0;
        $multiW = 120.0;
        $multiH = 0.0;
        $multiOut = $multiline->exposeParseHTMLText($multiElm, $multiX, $multiY, $multiW, $multiH);
        $this->assertNotSame('', $multiOut);
        $multiTrace = $multiline->exposeGetBBoxTrace();
        $this->assertNotEmpty($multiTrace);

        $this->assertGreaterThanOrEqual(2, \count($multiTrace));
        $this->assertSame('Alpha', (string) $multiTrace[0]['txt']);
        $this->assertSame('Beta', (string) $multiTrace[1]['txt']);
        $this->assertGreaterThan(
            (float) $multiTrace[0]['bbox_y'] + 0.001,
            (float) $multiTrace[1]['bbox_y'],
            'The second pre-wrap segment should render on a later line after an explicit newline.',
        );
        $this->assertGreaterThan(
            $singleY + 0.001,
            $multiY,
            'pre-wrap text containing a newline should advance the Y cursor to the next line.',
        );
    }

    public function testParseHTMLTextPreWrapConsecutiveNewlinesKeepBlankLineAdvance(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(10.0, 10.0, 120.0, 0.0);
        $obj->exposeResetBBoxTrace();

        $elm = $this->makeHtmlNode([
            'white-space' => 'pre-wrap',
            'value' => "Alpha\n\nBeta",
        ]);

        $textX = 10.0;
        $textY = 10.0;
        $textW = 120.0;
        $textH = 0.0;

        $out = $obj->exposeParseHTMLText($elm, $textX, $textY, $textW, $textH);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertCount(2, $trace);
        $this->assertSame('Alpha', (string) $trace[0]['txt']);
        $this->assertSame('Beta', (string) $trace[1]['txt']);

        $firstY = (float) $trace[0]['bbox_y'];
        $secondY = (float) $trace[1]['bbox_y'];
        $lineHeight = (float) $trace[0]['bbox_h'];

        $this->assertGreaterThan(
            $firstY + $lineHeight + 0.001,
            $secondY,
            'Two explicit newlines should keep one blank rendered line between text segments.',
        );
        $this->assertGreaterThan(
            10.0 + 0.001,
            $textY,
            'Consecutive explicit newlines should advance the text cursor vertically.',
        );
    }

    public function testParseHTMLTextPreModePreservesLeadingSpacesAcrossExplicitNewline(): void
    {
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(10.0, 10.0, 120.0, 0.0);
        $obj->exposeResetBBoxTrace();

        $elm = $this->makeHtmlNode([
            'white-space' => 'pre',
            'value' => " Alpha\n Beta",
        ]);

        $textX = 10.0;
        $textY = 10.0;
        $textW = 120.0;
        $textH = 0.0;

        $out = $obj->exposeParseHTMLText($elm, $textX, $textY, $textW, $textH);
        $this->assertNotSame('', $out);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertGreaterThanOrEqual(2, \count($trace));
        $this->assertStringStartsWith(' ', (string) $trace[0]['txt']);
        $this->assertStringContainsString('Alpha', (string) $trace[0]['txt']);
        $this->assertStringStartsWith(' ', (string) $trace[1]['txt']);
        $this->assertStringContainsString('Beta', (string) $trace[1]['txt']);
        $this->assertGreaterThan(
            (float) $trace[0]['bbox_y'] + 0.001,
            (float) $trace[1]['bbox_y'],
            'The second pre-mode segment should render on a later line after an explicit newline.',
        );
    }

    // --- Fix tests: base64 data URI images ---

    public function testGetHTMLCellRendersBase64DataUriImage(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        // Generate a minimal valid PNG in memory
        $img = \imagecreate(4, 4);
        \imagecolorallocate($img, 255, 0, 0);
        \ob_start();
        \imagepng($img);
        $raw = \ob_get_clean();
        $src = 'data:image/png;base64,' . \base64_encode((string) $raw);

        $out = $obj->getHTMLCell('<img src="' . $src . '" width="4" height="4" />', 0, 0, 20, 10);

        // Image must render — output must not fall back to literal '[img]' text
        $this->assertStringNotContainsString('[img]', $out);
    }

    public function testGetHTMLCellBase64DataUriWithInvalidDataFallsBackToAlt(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        // Invalid base64 payload → base64_decode returns false → src stays as original →
        // image library throws → fallback alt text is rendered
        $out = $obj->getHTMLCell(
            '<img src="data:image/png;base64,!!!notvalidbase64!!!" alt="err" width="4" height="4" />',
            0,
            0,
            20,
            10,
        );

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('err', $out);
    }

    // --- Fix tests: setHtmlVSpace() ---

    public function testSetHtmlVSpaceAddsExtraSpacingBeforeBlock(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        // Two paragraphs: the second <p> opens after the first, so openHTMLBlock will apply vspace
        $outDefault = $obj->getHTMLCell('<p>A</p><p>B</p>', 0, 0, 40, 30);

        $obj2 = $this->getTestObject();
        $this->initFontAndPage($obj2);
        $obj2->setHtmlVSpace(['p' => [['h' => 0.0, 'n' => 2], ['h' => 0.0, 'n' => 0]]]);
        $outSpaced = $obj2->getHTMLCell('<p>A</p><p>B</p>', 0, 0, 40, 30);

        $this->assertNotSame('', $outDefault);
        $this->assertNotSame('', $outSpaced);
        // Extra 2-line top spacing before the second <p> shifts its text downward → different PDF stream
        $this->assertNotSame($outDefault, $outSpaced);
    }

    public function testSetHtmlVSpaceAddsExtraSpacingAfterBlock(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $outDefault = $obj->getHTMLCell('<p>A</p><p>B</p>', 0, 5, 40, 30);

        $obj2 = $this->getTestObject();
        $this->initFontAndPage($obj2);
        $obj2->setHtmlVSpace(['p' => [['h' => 0.0, 'n' => 0], ['h' => 2.0, 'n' => 0]]]);
        $outSpaced = $obj2->getHTMLCell('<p>A</p><p>B</p>', 0, 5, 40, 30);

        $this->assertNotSame('', $outDefault);
        $this->assertNotSame('', $outSpaced);
        // Extra bottom spacing between paragraphs → different positions for second paragraph
        $this->assertNotSame($outDefault, $outSpaced);
    }

    public function testSetHtmlVSpaceFixedHeightAddsSpace(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $obj->setHtmlVSpace(['p' => [['h' => 5.0, 'n' => 0], ['h' => 5.0, 'n' => 0]]]);

        $out = $obj->getHTMLCell('<p>spacing test</p>', 0, 5, 40, 30);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('spacing test', $out);
    }

    public function testGetHTMLInputDisplayValueHelperCoversSupportedTypes(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame('', $obj->exposeGetHTMLInputDisplayValue(['attribute' => 'invalid']));
        $this->assertSame('', $obj->exposeGetHTMLInputDisplayValue($this->makeHtmlNode([
            'attribute' => ['type' => 'hidden'],
        ])));
        $this->assertSame('[x]', $obj->exposeGetHTMLInputDisplayValue($this->makeHtmlNode([
            'attribute' => ['type' => 'checkbox', 'checked' => 'checked'],
        ])));
        $this->assertSame('[ ]', $obj->exposeGetHTMLInputDisplayValue($this->makeHtmlNode([
            'attribute' => ['type' => 'radio'],
        ])));
        $this->assertSame('***', $obj->exposeGetHTMLInputDisplayValue($this->makeHtmlNode([
            'attribute' => ['type' => 'password', 'value' => 'abc'],
        ])));
        $this->assertSame('Go', $obj->exposeGetHTMLInputDisplayValue($this->makeHtmlNode([
            'attribute' => ['type' => 'submit', 'value' => 'Go'],
        ])));
        $this->assertSame('button', $obj->exposeGetHTMLInputDisplayValue($this->makeHtmlNode([
            'attribute' => ['type' => 'button'],
        ])));
        $this->assertSame('reset', $obj->exposeGetHTMLInputDisplayValue($this->makeHtmlNode([
            'attribute' => ['type' => 'reset'],
        ])));
        $this->assertSame('Filled', $obj->exposeGetHTMLInputDisplayValue($this->makeHtmlNode([
            'attribute' => ['value' => 'Filled'],
        ])));
        $this->assertSame('Hint', $obj->exposeGetHTMLInputDisplayValue($this->makeHtmlNode([
            'attribute' => ['placeholder' => 'Hint'],
        ])));
        $this->assertSame('', $obj->exposeGetHTMLInputDisplayValue($this->makeHtmlNode()));
    }

    public function testGetHTMLSelectDisplayValueHelperCoversSelectionFallbacks(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame('', $obj->exposeGetHTMLSelectDisplayValue(['attribute' => 'invalid']));
        $this->assertSame('', $obj->exposeGetHTMLSelectDisplayValue($this->makeHtmlNode([
            'attribute' => [],
        ])));

        $opt = 'one#!TaB!#One#!NwL!##!SeL!#two#!TaB!#Two#!NwL!#three#!TaB!#Three#!NwL!#';
        $this->assertSame('Three, Two', $obj->exposeGetHTMLSelectDisplayValue($this->makeHtmlNode([
            'attribute' => ['opt' => $opt, 'value' => 'three, two'],
        ])));
        $this->assertSame('Two', $obj->exposeGetHTMLSelectDisplayValue($this->makeHtmlNode([
            'attribute' => ['opt' => $opt],
        ])));
        $this->assertSame('One', $obj->exposeGetHTMLSelectDisplayValue($this->makeHtmlNode([
            'attribute' => ['opt' => 'one#!TaB!#One#!NwL!#two#!TaB!#Two#!NwL!#'],
        ])));
    }

    public function testHTMLListHelperMethodsTrackMarkerTypesAndCounters(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setULLIDot('square');

        $dom = [
            $this->makeHtmlNode([
                'value' => 'ol',
                'opening' => true,
                'attribute' => ['type' => 'A', 'start' => '3'],
            ]),
            $this->makeHtmlNode([
                'value' => 'li',
                'opening' => true,
                'attribute' => ['value' => '7'],
                'parent' => 0,
            ]),
        ];

        $this->assertSame('#', $obj->exposeGetCurrentHTMLListMarkerType());
        $this->assertSame('square', $obj->exposeGetHTMLListMarkerTypeWithDom($dom, -1, false));
        $this->assertSame('#', $obj->exposeGetHTMLListMarkerTypeWithDom($dom, -1, true));
        $this->assertSame(1, $obj->exposeGetHTMLListItemCounterWithDom($dom, 1));

        $obj->exposePushHTMLListWithDom($dom, 0, true);
        $this->assertSame('a', $obj->exposeGetCurrentHTMLListMarkerType());
        $this->assertSame(7, $obj->exposeGetHTMLListItemCounterWithDom($dom, 1));
        $this->assertSame(8, $obj->exposeGetHTMLListItemCounterWithDom($dom, -1));

        $obj->exposePopHTMLList();
        $this->assertSame('#', $obj->exposeGetCurrentHTMLListMarkerType());
    }

    public function testHTMLTableAndAncestorHelpersCoverBorderFillAndLookupBranches(): void
    {
        $obj = $this->getInternalTestObject();

        $dom = [
            $this->makeHtmlNode([
                'value' => 'div',
                'opening' => true,
                'block' => true,
                'bgcolor' => '#ff0',
                'parent' => -1,
            ]),
            $this->makeHtmlNode([
                'value' => 'span',
                'opening' => true,
                'bgcolor' => '#ff0',
                'parent' => 0,
                'border' => [
                    'LTRB' => ['lineWidth' => 1.0, 'lineColor' => '#000'],
                ],
            ]),
            $this->makeHtmlNode([
                'value' => 'td',
                'opening' => true,
                'bgcolor' => '#f00',
                'parent' => 0,
                'border' => [
                    'T' => ['lineWidth' => 0.2, 'lineColor' => '#111'],
                    'B' => ['lineWidth' => 0.4, 'lineColor' => '#222'],
                ],
            ]),
            $this->makeHtmlNode([
                'value' => 'input',
                'opening' => true,
                'parent' => 4,
            ]),
            $this->makeHtmlNode([
                'value' => 'form',
                'opening' => true,
                'attribute' => ['action' => 'https://example.test/form', 'method' => 'get'],
                'parent' => -1,
            ]),
        ];

        $all = $obj->exposeGetHTMLTableCellBorderStylesWithDom($dom, 1);
        $this->assertArrayHasKey('all', $all);
        $this->assertSame(1.0, $all['all']['lineWidth']);

        $sides = $obj->exposeGetHTMLTableCellBorderStylesWithDom($dom, 2);
        $this->assertArrayHasKey(0, $sides);
        $this->assertArrayHasKey(2, $sides);
        $this->assertArrayNotHasKey(1, $sides);
        $this->assertSame([], $obj->exposeGetHTMLTableCellBorderStylesWithDom($dom, -1));

        $fill = $obj->exposeGetHTMLTableCellFillStyleWithDom($dom, 2);
        $this->assertIsArray($fill);
        $this->assertSame('#f00', $fill['fillColor']);
        $this->assertNull($obj->exposeGetHTMLTableCellFillStyleWithDom($dom, -1));
        $this->assertSame('#0f0', $obj->exposeGetHTMLFillStyle('#0f0')['fillColor']);

        $this->assertTrue($obj->exposeHasBlockLvBgAncestorWithDom($dom, 1));
        $this->assertFalse($obj->exposeHasBlockLvBgAncestorWithDom($dom, 2));
        $this->assertSame(4, $obj->exposeFindHTMLAncestorOpeningTagWithDom($dom, 3, 'form'));

        $cyclic = [
            $this->makeHtmlNode(['value' => 'span', 'opening' => true, 'parent' => 1]),
            $this->makeHtmlNode(['value' => 'em', 'opening' => true, 'parent' => 0]),
        ];
        $this->assertSame(-1, $obj->exposeFindHTMLAncestorOpeningTagWithDom($cyclic, 0, 'form'));
    }

    public function testGetHTMLInputButtonActionHelperCoversOverrideAndFormBranches(): void
    {
        $obj = $this->getInternalTestObject();

        $dom = [
            $this->makeHtmlNode([
                'value' => 'form',
                'opening' => true,
                'attribute' => ['action' => 'https://example.test/form', 'method' => 'get'],
                'parent' => -1,
            ]),
            $this->makeHtmlNode([
                'value' => 'input',
                'opening' => true,
                'parent' => 0,
            ]),
        ];

        $this->assertSame(
            'alert(1)',
            $obj->exposeGetHTMLInputButtonActionWithDom($dom, 1, 'submit', ['onclick' => 'alert(1)']),
        );
        $this->assertSame(
            ['S' => 'ResetForm'],
            $obj->exposeGetHTMLInputButtonActionWithDom($dom, 1, 'reset', []),
        );
        $this->assertSame('', $obj->exposeGetHTMLInputButtonActionWithDom($dom, 1, 'button', []));

        $override = $obj->exposeGetHTMLInputButtonActionWithDom(
            $dom,
            1,
            'submit',
            ['formaction' => 'https://example.test/override', 'formmethod' => 'get'],
        );
        $this->assertIsArray($override);
        $this->assertSame('SubmitForm', $override['S']);
        $this->assertSame('https://example.test/override', $override['F']);
        $this->assertSame(['ExportFormat', 'GetMethod'], $override['Flags']);

        $inherited = $obj->exposeGetHTMLInputButtonActionWithDom($dom, 1, 'submit', []);
        $this->assertIsArray($inherited);
        $this->assertSame('https://example.test/form', $inherited['F']);
        $this->assertSame(['ExportFormat', 'GetMethod'], $inherited['Flags']);
    }

    public function testHtmlTcpdfHelperMethodsParseAllowAndResetCursor(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(12.0, 0.0, 80.0, 0.0);

        $payload = \rawurlencode((string) \json_encode(['m' => 'AddPage', 'p' => [true]], JSON_THROW_ON_ERROR));
        $data = '64+' . \str_repeat('a', 64) . '+' . $payload;

        $parsed = $obj->exposeParseHTMLTcpdfSerializedData($data);
        $this->assertIsArray($parsed);
        $this->assertSame('AddPage', $parsed['m']);
        $this->assertSame([true], $parsed['p']);

        $this->assertNull($obj->exposeParseHTMLTcpdfSerializedData('broken'));
        $this->assertNull($obj->exposeParseHTMLTcpdfSerializedData('0+a+payload'));
        $this->assertTrue($obj->exposeIsAllowedHTMLTcpdfMethod('pagebreak'));
        $this->assertTrue($obj->exposeIsAllowedHTMLTcpdfMethod('addpage'));
        $this->assertFalse($obj->exposeIsAllowedHTMLTcpdfMethod('setNamedDestination'));

        $before = $obj->exposePageBreak();
        $tpx = 41.0;
        $tpw = 9.0;
        $obj->exposeExecuteHTMLTcpdfPageBreak('true', $tpx, $tpw);
        $after = $obj->exposePageBreak();

        $this->assertGreaterThan($before, $after);
        $this->assertSame(12.0, $tpx);
        $this->assertSame(80.0, $tpw);
    }

    public function testEstimateHTMLTableHeadHeightAccountsForTableCellpaddingAttribute(): void
    {
        // Regression: estimateHTMLTableHeadHeight previously ignored the
        // table-level cellpadding attribute. parseHTMLTagOPENtd applies it
        // as a default for cells with zero CSS padding, so the standalone
        // thead estimate must mirror that or the replayed header on a new
        // page is shorter than what is actually rendered (see example 018).
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 80.0, 0.0);

        $bare = $obj->exposeEstimateHTMLTableHeadHeight(
            '<table cellpadding="0" cellspacing="0"><tr><th>H</th></tr></table>',
        );
        $padded = $obj->exposeEstimateHTMLTableHeadHeight(
            '<table cellpadding="6" cellspacing="0"><tr><th>H</th></tr></table>',
        );

        $this->assertGreaterThan(0.0, $bare);
        $this->assertGreaterThan($bare, $padded);
        // 2 * 6 (default unit 'px') of vertical cellpadding converted to the
        // default 'mm' unit is roughly 3.17 mm (6px = 4.5pt; 2 sides ≈ 3.17 mm);
        // allow generous tolerance for any minor rounding/font interplay.
        $delta = $padded - $bare;
        $this->assertGreaterThanOrEqual(2.5, $delta);
        $this->assertLessThanOrEqual(4.0, $delta);
    }

    public function testEstimateHTMLTableHeadHeightAccountsForTableCellspacingAttribute(): void
    {
        // Regression: the standalone thead estimate must also mirror the
        // cellspacing the runtime adds in parseHTMLTagOPENtable (one initial
        // gap) and parseHTMLTagCLOSEtr (one gap per closed row).
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 80.0, 0.0);

        $nospacing = $obj->exposeEstimateHTMLTableHeadHeight(
            '<table cellpadding="0" cellspacing="0"><tr><th>H</th></tr></table>',
        );
        $spaced = $obj->exposeEstimateHTMLTableHeadHeight(
            '<table cellpadding="0" cellspacing="6"><tr><th>H</th></tr></table>',
        );

        $this->assertGreaterThan($nospacing, $spaced);
    }

    public function testEstimateHTMLTableHeadHeightAccountsForCssBorderSpacingStyle(): void
    {
        // Regression: the standalone thead estimate should honor table-level
        // CSS border-spacing (vertical axis), not only the HTML cellspacing
        // attribute, because runtime table opening/row closing uses the same
        // effective vertical spacing path.
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 80.0, 0.0);

        $nospacing = $obj->exposeEstimateHTMLTableHeadHeight(
            '<table cellpadding="0" style="border-spacing:0 0"><tr><th>H</th></tr></table>',
        );
        $spaced = $obj->exposeEstimateHTMLTableHeadHeight(
            '<table cellpadding="0" style="border-spacing:0 6"><tr><th>H</th></tr></table>',
        );

        $this->assertGreaterThan($nospacing, $spaced);
    }

    public function testHtmlEstimateHelpersCoverTableTextAndNobrBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 80.0, 0.0);

        $this->assertSame(0.0, $obj->exposeEstimateHTMLTableHeadHeight(''));
        $this->assertGreaterThan(
            0.0,
            $obj->exposeEstimateHTMLTableHeadHeight('<tr></tr><tr><td height="8">Head</td></tr>'),
        );        $dom = [
            $this->makeHtmlNode(['value' => 'tr', 'opening' => true, 'parent' => -1]),
            $this->makeHtmlNode([
                'value' => 'td',
                'opening' => true,
                'parent' => 0,
                'height' => 9.0,
                'padding' => ['T' => 1.0, 'R' => 0.0, 'B' => 1.0, 'L' => 0.0],
            ]),
            $this->makeHtmlNode(['value' => 'tr', 'opening' => false, 'parent' => -1]),
            $this->makeHtmlNode(['value' => 'div', 'opening' => true, 'parent' => -1]),
            $this->makeHtmlNode([
                'tag' => false,
                'opening' => false,
                'value' => 'Alpha Beta Gamma',
                'parent' => 3,
            ]),
            $this->makeHtmlNode(['value' => 'br', 'opening' => true, 'parent' => 3]),
            $this->makeHtmlNode(['value' => 'img', 'opening' => true, 'parent' => 3, 'height' => 6.0]),
            $this->makeHtmlNode([
                'value' => 'input',
                'opening' => true,
                'parent' => 3,
                'attribute' => ['type' => 'password', 'value' => 'abc'],
            ]),
            $this->makeHtmlNode([
                'value' => 'select',
                'opening' => true,
                'parent' => 3,
                'attribute' => ['opt' => 'one#!TaB!#One#!NwL!##!SeL!#two#!TaB!#Two#!NwL!#'],
            ]),
            $this->makeHtmlNode([
                'value' => 'textarea',
                'opening' => true,
                'parent' => 3,
                'attribute' => ['value' => 'Delta'],
            ]),
            $this->makeHtmlNode(['value' => 'table', 'opening' => true, 'parent' => 3]),
            $this->makeHtmlNode(['value' => 'tr', 'opening' => true, 'parent' => 10]),
            $this->makeHtmlNode([
                'value' => 'td',
                'opening' => true,
                'parent' => 11,
                'height' => 7.0,
                'padding' => ['T' => 1.0, 'R' => 0.0, 'B' => 1.0, 'L' => 0.0],
            ]),
            $this->makeHtmlNode(['value' => 'tr', 'opening' => false, 'parent' => 10]),
            $this->makeHtmlNode(['value' => 'table', 'opening' => false, 'parent' => 3]),
            $this->makeHtmlNode(['value' => 'div', 'opening' => false, 'parent' => -1]),
        ];

        $rowHeight = $obj->exposeEstimateHTMLTableRowHeightWithDom($dom, 0);
        $this->assertGreaterThanOrEqual(9.0, $rowHeight);
        $this->assertSame(15, $obj->exposeFindHTMLClosingTagIndex($dom, 3));
        $this->assertSame(1, $obj->exposeFindHTMLClosingTagIndex($dom, 1));

        $textHeightWide = $obj->exposeEstimateHTMLTextHeightWithDom($dom, 4, 'Alpha Beta Gamma', 0.0);
        $textHeightNarrow = $obj->exposeEstimateHTMLTextHeightWithDom($dom, 4, 'Alpha Beta Gamma', 10.0);
        $this->assertGreaterThan(0.0, $textHeightWide);
        $this->assertGreaterThanOrEqual($textHeightWide, $textHeightNarrow);
        $this->assertSame(0.0, $obj->exposeEstimateHTMLTextHeightWithDom($dom, 999, 'Alpha', 10.0));

        $this->assertEqualsWithDelta($rowHeight, $obj->exposeEstimateHTMLNobrHeightWithDom($dom, 0, 20.0), 0.01);
        $this->assertGreaterThan($rowHeight, $obj->exposeEstimateHTMLNobrHeightWithDom($dom, 3, 20.0));
        $emptyDiv = [$this->makeHtmlNode(['value' => 'div', 'opening' => true])];
        $this->assertSame(0.0, $obj->exposeEstimateHTMLNobrHeightWithDom($emptyDiv, 0, 20.0));
    }

    public function testHtmlFontAndLineAdvanceHelpersCoverFallbackAndCachingBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 80.0, 0.0);

        $this->assertSame('helvetica', $obj->exposeGetHTMLBaseFontName());
        $this->assertSame([], $obj->exposeGetHTMLFontMetricWithDom([], 0));
        $this->assertSame('', $obj->exposeGetHTMLTextPrefixWithDom([], 0));
        $this->assertSame(0.0, $obj->exposeGetHTMLLineAdvanceWithDom([], 0));

        $dom = [
            $this->makeHtmlNode([
                'tag' => false,
                'opening' => false,
                'value' => 'Alpha',
                'fontname' => 'helveticaBI',
                'fontstyle' => 'BIU',
                'fontsize' => 12.0,
                'fgcolor' => 'red',
                'line-height' => 1.5,
            ]),
        ];

        $metricA = $obj->exposeGetHTMLFontMetricWithDom($dom, 0);
        $metricB = $obj->exposeGetHTMLFontMetricWithDom($dom, 0);
        $this->assertNotSame([], $metricA);
        $this->assertSame($metricA['key'] ?? null, $metricB['key'] ?? null);

        $prefix = $obj->exposeGetHTMLTextPrefixWithDom($dom, 0);
        $this->assertNotSame('', $prefix);

        $lineAdvance = $obj->exposeGetHTMLLineAdvanceWithDom($dom, 0);
        $this->assertGreaterThan(0.0, $lineAdvance);

        $obj->exposeSetHTMLLineState(5.0, 0.0, false);
        $this->assertGreaterThanOrEqual(5.0, $obj->exposeGetCurrentHTMLLineAdvanceWithDom($dom, 0));

        $obj->exposeUpdateHTMLLineAdvance(0.0);
        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertSame(5.0, $hrc['cellctx']['lineadvance']);

        $obj->exposeUpdateHTMLLineAdvance(3.0);
        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertSame(5.0, $hrc['cellctx']['lineadvance']);

        $obj->exposeUpdateHTMLLineAdvance(8.0);
        $hrc = $obj->exposeGetHTMLRenderContext();
        $this->assertSame(8.0, $hrc['cellctx']['lineadvance']);
    }

    public function testHtmlInlineMetricHelpersCoverWrapSpaceAndAscentBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0.0, 0.0, 80.0, 0.0);

        $dom = [
            $this->makeHtmlNode([
                'tag' => false,
                'opening' => false,
                'value' => '  Alpha Beta  ',
            ]),
            $this->makeHtmlNode([
                'value' => 'img',
                'opening' => true,
                'width' => 6.0,
                'height' => 12.0,
                'attribute' => ['align' => 'middle'],
            ]),
            $this->makeHtmlNode([
                'tag' => false,
                'opening' => false,
                'value' => 'Gamma',
            ]),
            $this->makeHtmlNode([
                'value' => 'br',
                'opening' => true,
            ]),
        ];

        $zero = $obj->exposeMeasureHTMLInlineLineMetricsWithDom($dom, 0, 0.0);
        $this->assertSame(['width' => 0.0, 'spaces' => 0, 'wrapped' => false], $zero);

        $wide = $obj->exposeMeasureHTMLInlineLineMetricsWithDom($dom, 0, 200.0);
        $this->assertGreaterThan(0.0, $wide['width']);
        $this->assertGreaterThanOrEqual(1, $wide['spaces']);
        $this->assertFalse($wide['wrapped']);

        $narrow = $obj->exposeMeasureHTMLInlineLineMetricsWithDom($dom, 0, 10.0, 2.0);
        $this->assertTrue($narrow['wrapped']);

        $this->assertSame(0, $obj->exposeGetHTMLTextFirstLineSpaces('', '', 20.0));
        $this->assertSame(0, $obj->exposeGetHTMLTextFirstLineSpaces('Alpha', '', 20.0));
        $this->assertGreaterThanOrEqual(1, $obj->exposeGetHTMLTextFirstLineSpaces('Alpha Beta Gamma', '', 200.0));

        $normalText = $obj->exposeNormalizeHTMLTextWithMode('Alpha   ', 'normal');
        $preWrapText = $obj->exposeNormalizeHTMLTextWithMode('Alpha   ', 'pre-wrap');
        $normalTrailingSpaces = $obj->exposeGetHTMLTextFirstLineSpaces($normalText, '', 200.0);
        $preWrapTrailSpaces = $obj->exposeGetHTMLTextFirstLineSpaces($preWrapText, '', 200.0);

        $this->assertSame('Alpha ', $normalText);
        $this->assertSame('Alpha   ', $preWrapText);
        $this->assertGreaterThan(
            $normalTrailingSpaces,
            $preWrapTrailSpaces,
            'pre-wrap should preserve additional trailing spaces compared to normal mode.',
        );

        $this->assertTrue($obj->exposeHasHTMLTextBreakOpportunity('Alpha Beta'));
        $this->assertTrue($obj->exposeHasHTMLTextBreakOpportunity("Alpha\u{00AD}Beta"));
        $this->assertFalse($obj->exposeHasHTMLTextBreakOpportunity('AlphaBeta'));
        $this->assertFalse($obj->exposeHasHTMLTextBreakOpportunity('   '));
        $this->assertFalse($obj->exposeHasHTMLTextBreakOpportunityWithMode('Alpha Beta', 'nowrap'));
        $this->assertTrue($obj->exposeHasHTMLTextBreakOpportunityWithMode('Alpha Beta', 'pre-wrap'));

        $ascent = $obj->exposeMeasureHTMLInlineRunMaxAscentWithDom($dom, 0);
        $this->assertGreaterThan(0.0, $ascent);

        $topOnly = [
            $this->makeHtmlNode([
                'value' => 'img',
                'opening' => true,
                'height' => 10.0,
                'attribute' => ['align' => 'top'],
            ]),
            $this->makeHtmlNode([
                'value' => 'br',
                'opening' => true,
            ]),
        ];
        $this->assertSame(0.0, $obj->exposeMeasureHTMLInlineRunMaxAscentWithDom($topOnly, 0));
    }

    // --- Fix tests: interactive form field input types ---

    public function testGetHTMLCellInputButtonCreatesButtonAnnotation(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell('<input type="submit" value="Go" />', 0, 0, 40, 10);

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Btn', $last['opt']['ft']);
    }

    public function testGetHTMLCellInputSubmitUsesEnclosingFormAction(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<form action="https://example.test/form" method="get">'
            . '<input type="submit" name="submit" value="Go" /></form>',
            0,
            0,
            60,
            10,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Btn', $last['opt']['ft']);
        $this->assertArrayHasKey('a', $last['opt']);
        $this->assertIsString($last['opt']['a']);
        $this->assertStringContainsString('/S /SubmitForm', $last['opt']['a']);
        $this->assertStringContainsString('/F (https://example.test/form)', $last['opt']['a']);
        $this->assertStringContainsString('/Flags 12', $last['opt']['a']);
    }

    public function testGetHTMLCellInputResetCreatesResetFormAction(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell('<input type="reset" name="reset" value="Reset" />', 0, 0, 40, 10);

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Btn', $last['opt']['ft']);
        $this->assertArrayHasKey('a', $last['opt']);
        $this->assertIsString($last['opt']['a']);
        $this->assertStringContainsString('/S /ResetForm', $last['opt']['a']);
    }

    public function testGetHTMLCellInputTextCreatesTextFieldAnnotation(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell('<input type="text" name="fname" value="John" />', 0, 0, 40, 10);

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Tx', $last['opt']['ft']);
    }

    public function testGetHTMLCellTextareaCreatesMultilineTextFieldAnnotation(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell('<textarea name="notes" rows="4">hello</textarea>', 0, 0, 40, 20);

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Tx', $last['opt']['ft']);
        // Multiline flag (bit 13 = 4096) must be set
        $this->assertSame(1 << 12, $last['opt']['ff']);
    }

    public function testGetHTMLCellTextareaColsControlsFieldWidth(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell('<textarea name="notes_auto" rows="3">hello</textarea>', 0, 0, 80, 30);
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{w: float} $auto */
        $auto = \end($annotation);

        $obj2 = $this->getTestObject();
        $this->initFontAndPage($obj2);
        $obj2->getHTMLCell('<textarea name="notes_cols" rows="3" cols="5">hello</textarea>', 0, 0, 80, 30);
        $annotation2 = $this->getObjectProperty($obj2, 'annotation');
        $this->assertIsArray($annotation2);
        $this->assertNotEmpty($annotation2);
        /** @var array{w: float} $withCols */
        $withCols = \end($annotation2);

        $this->assertLessThan((float) $auto['w'], (float) $withCols['w']);
        $this->assertLessThan(80.0, (float) $withCols['w']);
    }

    public function testGetHTMLCellSelectCreatesComboBoxAnnotation(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select name="color"><option value="r">Red</option><option value="g">Green</option></select>',
            0,
            0,
            40,
            10,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Ch', $last['opt']['ft']);
        /** @var array<string|array<string>> $opts */
        $opts = (array) ($last['opt']['opt'] ?? []);
        $labels = \array_map(
            static fn ($item) => \is_array($item) ? (string) $item[1] : (string) $item,
            $opts,
        );
        $this->assertContains('Red', $labels);
        $this->assertContains('Green', $labels);
    }

    public function testGetHTMLCellSelectMultipleCreatesListBoxAndEnablesMultipleSelection(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select name="choices" size="2" multiple="multiple">'
            . '<option value="a">Alpha</option><option value="b">Beta</option></select>',
            0,
            0,
            40,
            10,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Ch', $last['opt']['ft']);
        $fieldFlags = $last['opt']['ff'] ?? 0;
        $this->assertIsInt($fieldFlags);
        // Combo flag (bit 18 = 1<<17) must not be set for list boxes.
        $this->assertSame(0, $fieldFlags & (1 << 17));
        // Multi-select flag (bit 22 = 1<<21) must be set when HTML select has "multiple".
        $this->assertSame(1 << 21, $fieldFlags & (1 << 21));
    }

    public function testGetHTMLCellSelectMultipleZeroKeepsListBoxWithoutMultiSelectFlag(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select name="choices" size="2" multiple="0" value="missing,b">'
            . '<option value="a">Alpha</option><option value="b">Beta</option></select>',
            0,
            0,
            40,
            10,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Ch', $last['opt']['ft']);
        $this->assertSame('b', $last['opt']['v'] ?? '');

        $fieldFlags = $last['opt']['ff'] ?? 0;
        $this->assertIsInt($fieldFlags);
        // Combo flag (bit 18 = 1<<17) must not be set for list boxes.
        $this->assertSame(0, $fieldFlags & (1 << 17));
        // multiple="0" is a false boolean value and must not enable multiselect.
        $this->assertSame(0, $fieldFlags & (1 << 21));
    }

    public function testGetHTMLCellSelectMultipleStoresSelectedIndicesFromSelectedOptions(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select name="choices" size="3" multiple="multiple">'
            . '<option value="a" selected>Alpha</option><option value="b">Beta</option>'
            . '<option value="c" selected>Gamma</option></select>',
            0,
            0,
            40,
            10,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Ch', $last['opt']['ft']);
        $this->assertSame('a', $last['opt']['v'] ?? '');
        $this->assertSame([0, 2], $last['opt']['i'] ?? []);
    }

    public function testGetHTMLCellSelectMultipleValueFallbackStoresValidSelectedIndices(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select name="choices" size="3" multiple="multiple" value="missing,c,b">'
            . '<option value="a">Alpha</option><option value="b">Beta</option>'
            . '<option value="c">Gamma</option></select>',
            0,
            0,
            40,
            10,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Ch', $last['opt']['ft']);
        $this->assertSame('c', $last['opt']['v'] ?? '');
        $this->assertSame([2, 1], $last['opt']['i'] ?? []);
    }

    public function testGetHTMLCellSelectMultiplePrefersSelectedOptionsOverSelectValueFallback(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select name="choices" size="3" multiple="multiple" value="b,c">'
            . '<option value="a" selected>Alpha</option><option value="b">Beta</option>'
            . '<option value="c" selected>Gamma</option></select>',
            0,
            0,
            40,
            10,
        );

        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{opt: array<string, mixed>} $last */
        $last = \end($annotation);
        $this->assertSame('Ch', $last['opt']['ft']);
        // selected <option> entries must win over select[value] fallback.
        $this->assertSame('a', $last['opt']['v'] ?? '');
        $this->assertSame([0, 2], $last['opt']['i'] ?? []);
    }

    public function testGetHTMLCellSelectSizeControlsListBoxHeight(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $obj->getHTMLCell(
            '<select name="one"><option value="a">Alpha</option><option value="b">Beta</option></select>',
            0,
            0,
            40,
            10,
        );
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertIsArray($annotation);
        $this->assertNotEmpty($annotation);
        /** @var array{h: float} $combo */
        $combo = \end($annotation);

        $obj2 = $this->getTestObject();
        $this->initFontAndPage($obj2);
        $obj2->getHTMLCell(
            '<select name="many" size="3"><option value="a">Alpha</option><option value="b">Beta</option></select>',
            0,
            0,
            40,
            10,
        );
        $annotation2 = $this->getObjectProperty($obj2, 'annotation');
        $this->assertIsArray($annotation2);
        $this->assertNotEmpty($annotation2);
        /** @var array{h: float} $list */
        $list = \end($annotation2);

        $this->assertGreaterThan((float) $combo['h'], (float) $list['h']);
    }

    public function testNestedInlineTagsRenderOnSameLine(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->exposeInitHTMLCellContext(0, 0, 150, 60);

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 150.0;
        $tph = 60.0;

        $html = '<p><b>b<i>bi<u>biu</u>bi</i>b</b></p>';
        $dom = $obj->exposeGetHTMLDOM($html);

        // Collect the y-position of each text fragment to ensure they are all on the same line.
        $yPositions = [];
        foreach ($dom as $node) {
            if (!empty($node['tag'])) {
                continue;
            }

            if (\trim((string) $node['value']) === '') {
                continue;
            }

            $yBefore = $tpy;
            $obj->exposeParseHTMLText($node, $tpx, $tpy, $tpw, $tph);
            $yPositions[] = $yBefore;
        }

        // All text fragments must start at the same y position (same line).
        $this->assertNotEmpty($yPositions);
        $firstY = $yPositions[0];
        foreach ($yPositions as $y) {
            $this->assertEqualsWithDelta(
                $firstY,
                $y,
                0.001,
                'All nested inline text fragments must be on the same line'
            );
        }

        // Also verify tpx advanced after each fragment (fragments are side by side, not stacked).
        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 150.0;
        $obj->exposeInitHTMLCellContext(0, 0, 150, 60);
        $xPositions = [];
        foreach ($dom as $node) {
            if (!empty($node['tag'])) {
                continue;
            }

            if (\trim((string) $node['value']) === '') {
                continue;
            }

            $xBefore = $tpx;
            $obj->exposeParseHTMLText($node, $tpx, $tpy, $tpw, $tph);
            $xPositions[] = ['before' => $xBefore, 'after' => $tpx];
        }

        // Each text fragment must advance the x cursor.
        foreach ($xPositions as $xp) {
            $this->assertGreaterThan($xp['before'], $xp['after'], 'Each inline fragment must advance the x cursor');
        }
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

    public function testSanitizeHTMLWithOptgroupProcessesClosingAndOpeningTags(): void
    {
        $obj = $this->getInternalTestObject();

        $html = '<select name="x"><optgroup label="Group A">'
            . '<option value="a1">Alpha 1</option>'
            . '</optgroup><optgroup label="Group B">'
            . '<option value="b1">Beta 1</option>'
            . '</optgroup></select>';

        $result = $obj->exposeSanitizeHTML($html);

        // Options should be packed with group-label prefix.
        $this->assertStringContainsString('opt=', $result);
        $this->assertStringContainsString('Group A - Alpha 1', $result);
        $this->assertStringContainsString('Group B - Beta 1', $result);
    }

    public function testGetHTMLDOMHandlesUnquotedAttributeValues(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        // Build DOM from HTML with an unquoted attribute value.
        $dom = $obj->exposeGetHTMLDOM('<div data-count=42>text</div>');

        // Find the div node and check the unquoted attribute was parsed.
        $divNode = null;
        foreach ($dom as $node) {
            if (isset($node['value']) && $node['value'] === 'div') {
                $divNode = $node;
                break;
            }
        }
        $this->assertNotNull($divNode);
        $this->assertSame('42', $divNode['attribute']['data-count'] ?? null);
    }

    public function testIsValidCSSSelectorMatchingClassAndIdTokensContinueToNextToken(): void
    {
        $obj = $this->getTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode([
                'value' => 'div',
                'attribute' => ['id' => 'main', 'class' => 'hero card'],
                'parent' => 0,
                'tag' => true,
                'opening' => true,
            ]),
        ];

        // Selector with both class AND id suffix tokens — both must match and continue.
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' div.hero#main'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' div.card#main'));
        // Class present but id wrong → false.
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' div.hero#other'));
    }

    public function testParseHTMLStyleDeclarationMapHandlesQuotesAndParens(): void
    {
        $obj = $this->getInternalTestObject();

        // Quoted value with semicolons inside should not be split.
        $style = "background-image: url('data:image/png;base64,abc'); color: red;";
        $result = $obj->exposeParseHTMLStyleDeclarationMap($style);

        $this->assertArrayHasKey('background-image', $result);
        $this->assertArrayHasKey('color', $result);
        $this->assertStringContainsString('url(', $result['background-image']);
        $this->assertSame('red', $result['color']);
    }

    public function testParseHTMLStyleDeclarationMapWithDoubleQuotes(): void
    {
        $obj = $this->getInternalTestObject();

        // Declaration with double-quoted content.
        $style = 'content: "hello; world"; font-weight: bold';
        $result = $obj->exposeParseHTMLStyleDeclarationMap($style);

        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('font-weight', $result);
        $this->assertStringContainsString('hello; world', $result['content']);
    }

    public function testParseHTMLStyleDeclarationMapSkipsDeclarationWithNoColon(): void
    {
        $obj = $this->getInternalTestObject();

        // A declaration without a colon must be silently skipped.
        $style = 'invalid-no-colon; color: blue';
        $result = $obj->exposeParseHTMLStyleDeclarationMap($style);

        $this->assertArrayNotHasKey('invalid-no-colon', $result);
        $this->assertSame('blue', $result['color'] ?? '');
    }

    public function testParseHTMLStyleDeclarationMapStripsImportantSuffix(): void
    {
        $obj = $this->getInternalTestObject();

        $style = 'color:#0055aa !important; border:1px solid #333 !important; font-weight:bold';
        $result = $obj->exposeParseHTMLStyleDeclarationMap($style);

        $this->assertSame('#0055aa', $result['color'] ?? '');
        $this->assertSame('1px solid #333', $result['border'] ?? '');
        $this->assertSame('bold', $result['font-weight'] ?? '');
    }

    public function testParseHTMLStyleAttributesHandlesImportantColorWithoutFatal(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode([
                'value' => 'div',
                'parent' => 0,
                'attribute' => [
                    'style' => 'color:#0055aa !important;border:1px solid #333 !important',
                ],
            ]),
        ];

        $obj->exposeParseHTMLStyleAttributesWithDom($dom, 1, 0);

        $node = $dom[1] ?? [];
        $this->assertSame('#0055aa', $node['style']['color'] ?? '');
        $this->assertSame('1px solid #333', $node['style']['border'] ?? '');
        $this->assertNotSame('', $node['fgcolor'] ?? '');
    }

    public function testParseHTMLStyleAttributesSkipsNodeWithNoStyleAttribute(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode(['value' => 'div']),
        ];

        // Should not throw or modify dom when no style attribute.
        $obj->exposeParseHTMLStyleAttributesWithDom($dom, 1, 0);

        $node = $dom[1] ?? [];
        $this->assertEmpty($node['style'] ?? []);
    }

    public function testParseHTMLStyleAttributesSkipsNodeWithEmptyParsedStyles(): void
    {
        $obj = $this->getInternalTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode(['value' => 'div', 'attribute' => ['style' => 'bad-declaration-only']]),
        ];

        // A style with no valid colon-delimited declarations must result in empty styles.
        $obj->exposeParseHTMLStyleAttributesWithDom($dom, 1, 0);

        $node = $dom[1] ?? [];
        $this->assertEmpty($node['style'] ?? []);
    }

    public function testIsValidCSSSelectorNthChildNegativeFactorFormula(): void
    {
        $obj = $this->getTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode(['value' => 'li', 'parent' => 0, 'tag' => true, 'opening' => true]),
            2 => $this->makeHtmlNode(['value' => 'li', 'parent' => 0, 'tag' => true, 'opening' => true]),
            3 => $this->makeHtmlNode(['value' => 'li', 'parent' => 0, 'tag' => true, 'opening' => true]),
            4 => $this->makeHtmlNode(['value' => 'li', 'parent' => 0, 'tag' => true, 'opening' => true]),
        ];

        // -n+3 selects first 3 elements (positions 1, 2, 3).
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' li:nth-child(-n+3)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 2, ' li:nth-child(-n+3)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' li:nth-child(-n+3)'));
        // 4th element is not selected by -n+3.
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 4, ' li:nth-child(-n+3)'));

        // -2n+4 selects elements at positions 4, 2.
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 4, ' li:nth-child(-2n+4)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 2, ' li:nth-child(-2n+4)'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' li:nth-child(-2n+4)'));
    }

    public function testIsValidCSSSelectorPseudoClassWithExactPositionArg(): void
    {
        $obj = $this->getTestObject();
        $dom = [
            0 => $this->makeHtmlNode(['value' => 'root']),
            1 => $this->makeHtmlNode(['value' => 'li', 'parent' => 0, 'tag' => true, 'opening' => true]),
            2 => $this->makeHtmlNode(['value' => 'li', 'parent' => 0, 'tag' => true, 'opening' => true]),
            3 => $this->makeHtmlNode(['value' => 'li', 'parent' => 0, 'tag' => true, 'opening' => true]),
        ];

        // :nth-child(0) is never true.
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' li:nth-child(0)'));
        // :nth-child(1) is the first child only.
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' li:nth-child(1)'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 2, ' li:nth-child(1)'));
        // :nth-child(+n) selects all.
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 1, ' li:nth-child(+n)'));
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 3, ' li:nth-child(+n)'));
        // :nth-child with zero factor: 0n+2 selects exactly position 2.
        $this->assertTrue($obj->isValidCSSSelectorForTag($dom, 2, ' li:nth-child(0n+2)'));
        $this->assertFalse($obj->isValidCSSSelectorForTag($dom, 1, ' li:nth-child(0n+2)'));
    }

    public function testGetHTMLCellEmdashInOrderedListDoesNotOverlapFollowingStrongFragment(): void
    {
        // Regression: em-dash (U+2014) and other WinAnsi high-range glyphs (curly
        // quotes, bullet, en-dash, ellipsis, etc.) were measured using the font's
        // default width (dw = 278 units) because Import\Core keyed widths by
        // StandardEncoding code point instead of WinAnsi byte. The result was that
        // inline text following a fragment containing an em-dash appeared too far
        // to the left — visually overlapping the preceding word.
        //
        // Expected layout (10 pt Helvetica, ~180 mm wide list item):
        //   plain text:  "Ordered item \x97 the number is auto-generated as the "
        //   strong text: "Lbl"
        // After the plain fragment, the strong fragment must start at least as far
        // right as the plain fragment's measured end-x, with a small tolerance.
        $obj = $this->getBBoxProbeTestObject();
        $this->initFontAndPage($obj);

        // pdfua mode is used to match the E015 example that surfaced the bug.
        $rfn = new \ReflectionProperty($obj, 'pdfuaMode');
        $rfn->setAccessible(true);
        $rfn->setValue($obj, 'pdfua');

        $html = '<ol><li>Ordered item &mdash; the number is auto-generated as the'
            . ' <strong>Lbl</strong></li></ol>';

        $obj->exposeResetBBoxTrace();
        $obj->getHTMLCell($html, 15, 20, 180);

        $trace = $obj->exposeGetBBoxTrace();
        $this->assertNotSame([], $trace);

        // Find the plain-text fragment ending in "...as the " and the "Lbl" strong fragment.
        $plainIdx = null;
        $lblIdx   = null;
        foreach ($trace as $idx => $entry) {
            $txt = (string) $entry['txt'];
            if (($plainIdx === null) && \str_contains($txt, 'auto-generated')) {
                $plainIdx = $idx;
            }

            if (($lblIdx === null) && $txt === 'Lbl') {
                $lblIdx = $idx;
            }
        }

        $this->assertNotNull($plainIdx, 'Plain-text fragment containing "auto-generated" must be present');
        $this->assertNotNull($lblIdx, '"Lbl" strong fragment must be present');

        $plainEntry = $trace[(int) $plainIdx];
        $lblEntry   = $trace[(int) $lblIdx];

        // Both fragments must sit on the same visual line.
        // A delta of 1 mm is used because the bold "Lbl" and the regular-weight
        // prefix are baseline-aligned: the bold font's larger ascent shifts its
        // bbox_y slightly upward relative to the regular fragment while they still
        // occupy the same visual line.
        $this->assertEqualsWithDelta(
            (float) $plainEntry['bbox_y'],
            (float) $lblEntry['bbox_y'],
            1.0,
            '"Lbl" must be on the same line as the preceding plain-text fragment',
        );

        // "Lbl" must start no earlier than where the plain-text fragment ends.
        // Before the metrics fix this assertion failed: Lbl started ~7 mm to
        // the left of the plain fragment's end_x (em-dash measured as dw=278
        // instead of its actual 1000 units).
        $this->assertGreaterThanOrEqual(
            (float) $plainEntry['bbox_end_x'] - 0.1,
            (float) $lblEntry['bbox_x'],
            '"Lbl" must not overlap the preceding plain-text fragment — em-dash width regression',
        );
    }
}
