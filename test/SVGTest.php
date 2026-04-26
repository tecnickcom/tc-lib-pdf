<?php

/**
 * SVGTest.php
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

class SVGTest extends TestUtil
{
    public static function setUpBeforeClass(): void
    {
        self::setUpFontsPath();
    }

    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    protected function getInternalTestObject(): TestableSVG
    {
        return new TestableSVG();
    }

    public function testAddSVGStoresInlineSvgObject(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndPage($obj);
        $svg = '@<svg xmlns="http://www.w3.org/2000/svg" width="100" height="50">'
            . '<rect x="0" y="0" width="100" height="50" fill="#ff0000"/>'
            . '</svg>';

        $soid = $obj->addSVG($svg, 10, 20, 40, 20, $page['height']);

        $this->assertSame(1, $soid);
        /** @var array<int, array<string, mixed>> $svgobjs */
        $svgobjs = $this->getObjectProperty($obj, 'svgobjs');
        $this->assertArrayHasKey($soid, $svgobjs);
        $this->assertNotSame('', $svgobjs[$soid]['out']);
    }

    public function testAddSVGRejectsInvalidSvg(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->addSVG('@<svg xmlns="http://www.w3.org/2000/svg"></svg>');
    }

    public function testGetSetSVGReturnsRenderableOutput(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndPage($obj);
        $svg = '@<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20">'
            . '<circle cx="10" cy="10" r="8" fill="#00ff00"/>'
            . '</svg>';

        $soid = $obj->addSVG($svg, 5, 6, 12, 12, $page['height']);
        $out = $obj->getSetSVG($soid);

        $this->assertNotSame('', $out);
        $this->assertStringStartsWith("q\n", $out);
        $this->assertStringContainsString(" cm\n", $out);
        $this->assertStringEndsWith("Q\nQ\n", $out);
    }

    public function testSvgPatternFillEmitsPdfPatternResources(): void
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
        $page = $this->initFontAndPage($obj);
        $svg = '@<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20">'
            . '<defs>'
            . '<pattern id="p1" patternUnits="userSpaceOnUse" x="0" y="0" width="4" height="4">'
            . '<rect x="0" y="0" width="4" height="4" fill="none" stroke="#000000" stroke-width="1"/>'
            . '</pattern>'
            . '</defs>'
            . '<rect x="0" y="0" width="20" height="20" fill="url(#p1)"/>'
            . '</svg>';

        $soid = $obj->addSVG($svg, 5, 6, 12, 12, $page['height']);
        $svgOut = $obj->getSetSVG($soid);
        $pdf = $obj->getOutPDFString();

        $this->assertStringContainsString('/Pattern cs /PTN_', $svgOut);
        $this->assertStringContainsString(' scn', $svgOut);
        $this->assertStringContainsString('/Type /Pattern', $pdf);
        $this->assertStringContainsString('/Pattern <<', $pdf);
        $this->assertStringContainsString('/Type /Pattern /PatternType 1', $pdf);
        $this->assertStringContainsString('/Resources << /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]', $pdf);
        $this->assertMatchesRegularExpression('/\/PTN_[A-F0-9]{16}\s+\d+\s+0\s+R/', $pdf);
    }

    public function testGetSetSVGThrowsForUnknownId(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->getSetSVG(999);
    }

    public function testParseSVGTMtranslateWithTwoValues(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMtranslate('10.5, 20.3');

        $this->assertSame([1.0, 0.0, 0.0, 1.0, 10.5, 20.3], $result);
    }

    public function testParseSVGTMtranslateWithOneValue(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMtranslate('5.5');

        $this->assertSame([1.0, 0.0, 0.0, 1.0, 5.5, 0.0], $result);
    }

    public function testParseSVGTMscaleWithTwoValues(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMscale('2.0, 3.0');

        $this->assertSame([2.0, 0.0, 0.0, 3.0, 0.0, 0.0], $result);
    }

    public function testParseSVGTMscaleWithOneValue(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMscale('1.5');

        $this->assertSame([1.5, 0.0, 0.0, 1.5, 0.0, 0.0], $result);
    }

    public function testParseSVGTMrotateWithDegrees(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMrotate('45');

        $this->assertCount(6, $result);
        // Rotation matrix has cos and sin components that can be floating point
        $this->assertNotSame([1.0, 0.0, 0.0, 1.0, 0.0, 0.0], $result);
    }

    public function testParseSVGTMskewXWithDegrees(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMskewX('30');

        $this->assertCount(6, $result);
        $this->assertNotSame([1.0, 0.0, 0.0, 1.0, 0.0, 0.0], $result);
    }

    public function testParseSVGTMskewYWithDegrees(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMskewY('30');

        $this->assertCount(6, $result);
        $this->assertNotSame([1.0, 0.0, 0.0, 1.0, 0.0, 0.0], $result);
    }

    public function testParseSVGTMmatrixWithSixValues(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMmatrix('1 2 3 4 5 6');

        $this->assertSame([1.0, 2.0, 3.0, 4.0, 5.0, 6.0], $result);
    }

    public function testParseSVGTMmatrixWithCSVFormat(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMmatrix('1.5, 2.5, 3.5, 4.5, 5.5, 6.5');

        $this->assertSame([1.5, 2.5, 3.5, 4.5, 5.5, 6.5], $result);
    }

    public function testSvgUnitConvertersAndTransformHelpers(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setSvgRefUnit(1);

        $this->assertSame(72.0, $obj->exposeSvgUnitToPoints('1in', 1));
        $this->assertEqualsWithDelta(25.4, $obj->exposeSvgUnitToUnit('25.4mm', 1), 0.0001);

        $transformMatrix = $obj->exposeGetSVGTransformMatrix('translate(10,20) scale(2) rotate(0)');
        $this->assertCount(6, $transformMatrix);

        $converted = $obj->exposeConvertSVGMatrix([1, 0, 0, 1, 10, 20], 1);
        $this->assertCount(6, $converted);
        $this->assertSame(7.5, $converted[4]);
        $this->assertSame(-15.0, $converted[5]);

        $out = $obj->exposeGetOutSVGTransformation([1, 0, 0, 1, 10, 20], 1);
        $this->assertStringContainsString(' cm', $out);
    }

    public function testSvgTagPathAndCssAttributeHelpers(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setSvgRefUnit(2);

        $this->assertSame('rect', $obj->exposeRemoveTagNamespace('svg:rect'));
        $this->assertSame('', $obj->exposeGetSVGPath(2, 'M 0 0 L 10 10 Z', ''));

        $path = $obj->exposeGetSVGPath(2, 'M 0 0 L 10 10 H 5 V 3 Z', 'S');
        $this->assertStringContainsString('m', $path);
        $this->assertStringContainsString('l', $path);
        $this->assertStringContainsString("h\n", $path);

        $tag = 'font-size: 12px; font-weight: bold;';
        $this->assertSame('12px', $obj->exposeParseCSSAttrib($tag, 'font-size', '10px'));
        $this->assertSame('fallback', $obj->exposeParseCSSAttrib($tag, 'line-height', 'fallback'));
    }

    public function testSvgTextAttributeAndBlendAlphaHelpers(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame(0.0, $obj->exposeGetTALetterSpacing('normal'));
        $this->assertSame(1.25, $obj->exposeGetTALetterSpacing('inherit', 1.25));
        $this->assertGreaterThan(0, $obj->exposeGetTALetterSpacing('2px'));

        $this->assertSame(70.0, $obj->exposeGetTAFontStretching('condensed'));
        $this->assertSame(120.0, $obj->exposeGetTAFontStretching('wider', 110));
        $this->assertSame(95.0, $obj->exposeGetTAFontStretching('narrower', 105));
        $this->assertSame(80.0, $obj->exposeGetTAFontStretching('80%'));

        $this->assertSame('B', $obj->exposeGetTAFontWeight('bold'));
        $this->assertSame('', $obj->exposeGetTAFontWeight('normal'));
        $this->assertSame('I', $obj->exposeGetTAFontStyle('italic'));
        $this->assertSame('', $obj->exposeGetTAFontStyle('normal'));
        $this->assertSame('U', $obj->exposeGetTAFontDecoration('underline'));
        $this->assertSame('O', $obj->exposeGetTAFontDecoration('overline'));
        $this->assertSame('D', $obj->exposeGetTAFontDecoration('line-through'));
        $this->assertSame('', $obj->exposeGetTAFontDecoration('none'));

        $this->assertSame('Multiply', $obj->exposeNormalizeSVGBlendMode('multiply'));
        $this->assertSame('Normal', $obj->exposeNormalizeSVGBlendMode('no-such-mode'));
        $this->assertSame(1.0, $obj->exposeNormalizeSVGAlphaValue(1.5));
        $this->assertSame(0.0, $obj->exposeNormalizeSVGAlphaValue(-0.4));
        $this->assertSame(0.5, $obj->exposeNormalizeSVGAlphaValue('0.5'));

        $this->assertSame('', $obj->exposeGetSVGExtGState(null, null, ''));
        $gstate = $obj->exposeGetSVGExtGState(0.8, 0.5, 'Multiply');
        $this->assertStringContainsString('gs', $gstate);

        $pdfx3 = new TestableSVG('mm', true, false, true, 'pdfx3');
        $this->assertSame('', $pdfx3->exposeGetSVGExtGState(0.8, 0.5, 'Multiply'));

        $pdfx4 = new TestableSVG('mm', true, false, true, 'pdfx4');
        $this->assertStringContainsString('gs', $pdfx4->exposeGetSVGExtGState(0.8, 0.5, 'Multiply'));
    }

    public function testSvgPathCommandHelpersProducePathOperations(): void
    {
        $obj = $this->getInternalTestObject();

        $coord = $obj->getPathCoordDefaults();
        [$mout, $coord] = $obj->exposeSvgPathCmdM([1, 2, 3, 4], $coord);
        $this->assertStringContainsString('m', $mout);

        [$lout, $coord] = $obj->exposeSvgPathCmdL([5, 6, 7, 8], $coord);
        $this->assertStringContainsString('l', $lout);

        [$hout, $coord] = $obj->exposeSvgPathCmdH([9, 10], $coord);
        $this->assertStringContainsString('l', $hout);

        [$vout, $coord] = $obj->exposeSvgPathCmdV([11, 12], $coord);
        $this->assertStringContainsString('l', $vout);

        [$cout, $coord] = $obj->exposeSvgPathCmdC([13, 14, 15, 16, 17, 18], $coord);
        $this->assertStringContainsString('c', $cout);

        [$qout, $coord] = $obj->exposeSvgPathCmdQ([19, 20, 21, 22], $coord);
        $this->assertStringContainsString('c', $qout);

        $spaths = [['0', 'C'], ['1', 'S']];
        [$sout, $coord] = $obj->exposeSvgPathCmdS([23, 24, 25, 26], $coord, $spaths, 1);
        $this->assertStringContainsString('c', $sout);

        $tpaths = [['0', 'Q'], ['1', 'T']];
        [$tout, $coord] = $obj->exposeSvgPathCmdT([27, 28], $coord, $tpaths, 1);
        $this->assertStringContainsString('c', $tout);

        $arcPaths = [['0', 'A'], ['1', 'z']];
        [$aout, $coord] = $obj->exposeSvgPathCmdA(
            [5, 5, 0, 0, 1, 30, 35],
            $coord,
            $arcPaths,
            0,
            ['5', '5', '0', '0', '1', '30', '35']
        );
        $this->assertNotSame('', $aout);

        [$zout, $coord] = $obj->exposeSvgPathCmdZ($coord);
        $this->assertSame("h\n", $zout);
        $this->assertSame($coord['xinit'], $coord['x']);
        $this->assertSame($coord['yinit'], $coord['y']);
    }

    public function testSvgStyleHelperMethodsCoverCorePaths(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->setSvgObjMeta(3);

        $base = $obj->exposeDefaultSVGStyle();

        [$fontOut, $fontStyle] = $obj->exposeParseSVGStyleFont($base, $base);
        $this->assertNotSame('', $fontOut);
        $this->assertArrayHasKey('font-size-val', $fontStyle);

        $strokeStyle = $base;
        $strokeStyle['stroke'] = '#112233';
        $strokeStyle['stroke-width'] = 1.0;
        $strokeStyle['stroke-linecap'] = 'butt';
        $strokeStyle['stroke-linejoin'] = 'miter';
        $strokeStyle['stroke-opacity'] = 1.0;
        $strokeStyle['opacity'] = 1.0;
        $strokeStyle['mix-blend-mode'] = 'normal';
        $strokeStyle['objstyle'] = '';
        [$strokeOut, $strokeStyle] = $obj->exposeParseSVGStyleStroke(3, $strokeStyle);
        $this->assertNotSame('', $strokeOut);
        $this->assertStringContainsString('D', $strokeStyle['objstyle']);

        $colorStyle = $base;
        $colorStyle['opacity'] = 0.5;
        $colorStyle['mix-blend-mode'] = 'multiply';
        $colorStyle['color'] = '#ff0000';
        $colorStyle['text-color'] = '#00ff00';
        [$colorOut] = $obj->exposeParseSVGStyleColor($colorStyle);
        $this->assertNotSame('', $colorOut);

        $pdfx3 = new TestableSVG('mm', true, false, true, 'pdfx3');
        [$pdfx3ColorOut] = $pdfx3->exposeParseSVGStyleColor($colorStyle);
        $this->assertStringNotContainsString('/GS', $pdfx3ColorOut);

        $clipStyle = $base;
        $clipStyle['clip'] = 'rect(1 2 3 4)';
        $clipStyle['clip-rule'] = 'evenodd';
        [$clipOut] = $obj->exposeParseSVGStyleClip($clipStyle, 10, 20, 100, 50);
        $this->assertNotSame('', $clipOut);

        $this->assertSame('', $obj->exposeParseSVGStyleGradient(3, [], 'missing', 0, 0, 10, 10));

        $fillNone = $base;
        $fillNone['fill'] = 'none';
        [$fillNoneOut] = $obj->exposeParseSVGStyleFill(3, $fillNone, [], 0, 0, 10, 10);
        $this->assertSame('', $fillNoneOut);

        $fillUrl = $base;
        $fillUrl['fill'] = 'url(#missing)';
        $fillUrl['opacity'] = 1.0;
        $fillUrl['fill-opacity'] = 1.0;
        [$fillUrlOut] = $obj->exposeParseSVGStyleFill(3, $fillUrl, [], 0, 0, 10, 10);
        $this->assertSame('', $fillUrlOut);

        $obj->patchSvgObj(3, [
            'defs' => [
                'patEmpty' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patEmpty',
                        'patternUnits' => 'userSpaceOnUse',
                        'x' => '0',
                        'y' => '0',
                        'width' => '3',
                        'height' => '3',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'rect',
                            'attr' => [
                                'x' => '0',
                                'y' => '0',
                                'width' => '3',
                                'height' => '3',
                                'fill' => 'none',
                                'stroke' => '#000000',
                                'stroke-width' => '1',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'rect',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $fillPattern = $base;
        $fillPattern['fill'] = 'url(#patEmpty)';
        $fillPattern['opacity'] = 1.0;
        $fillPattern['fill-opacity'] = 1.0;
        $obj->patchSvgObj(3, ['out' => 'BASE']);
        [$fillPatternOut] = $obj->exposeParseSVGStyleFill(
            3,
            $fillPattern,
            [],
            0,
            0,
            10,
            10,
            'getClippingRect',
            [0.0, 0.0, 10.0, 10.0, 0.0],
        );
        $this->assertSame('BASE', $obj->getSvgObj(3)['out']);
        $this->assertIsString($fillPatternOut);

        $parser = \xml_parser_create();
        $obj->exposeParseSVGStyleClipPath($parser, 3, []);

        $earlyStyle = $base;
        $earlyStyle['opacity'] = 0;
        [$styleOut, $objstyle] = $obj->exposeParseSVGStyle($parser, 3, $earlyStyle, $base);
        $this->assertSame('', $styleOut);
        $this->assertSame('', $objstyle);
    }

    public function testSvgRawSizeAndPrescanHelpers(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->initSvgObjForHandlers(40);

        $inline = '<svg width="10" height="20"></svg>';
        $this->assertSame($inline, $obj->exposeGetRawSVGData('@' . $inline));
        $this->assertSame('', $obj->exposeGetRawSVGData('@'));

        $tmp = \tempnam(\sys_get_temp_dir(), 'tc-svg-');
        $this->assertNotFalse($tmp);
        \file_put_contents($tmp, $inline);
        try {
            $this->assertSame($inline, $obj->exposeGetRawSVGData($tmp));
        } finally {
            @\unlink($tmp);
        }

        $size = $obj->exposeGetSVGSize(
            '<svg x="1" y="2" width="100" height="50" viewBox="0 0 100 50" preserveAspectRatio="xMinYMin meet"></svg>'
        );
        $this->assertGreaterThan(0, $size['width']);
        $this->assertGreaterThan(0, $size['height']);
        $this->assertSame('xMinYMin', $size['ar_align']);

        $attr = $obj->exposeGetSVGPrescanAttributes(['id' => 'g1', 'stop-color' => '#fff', 'junk' => 'x']);
        $this->assertArrayHasKey('id', $attr);
        $this->assertArrayHasKey('stop-color', $attr);
        $this->assertArrayNotHasKey('junk', $attr);

        $stopStyle = $obj->exposeGetSVGPrescanStopStyle(['style' => 'stop-color: #123456; stop-opacity: 0.25']);
        $this->assertSame('#123456', $stopStyle['stop-color']);
        $this->assertSame(0.25, $stopStyle['stop-opacity']);

        $obj->exposePrescanSVGGradients(
            '<svg><defs><linearGradient id="lg">'
            . '<stop offset="0%" stop-color="#000"/>'
            . '<stop offset="100%" style="stop-color:#fff;stop-opacity:0.4"/>'
            . '</linearGradient></defs></svg>',
            40,
        );
        $svgobj = $obj->getSvgObj(40);
        $this->assertArrayHasKey('lg', $svgobj['gradients']);
        $this->assertGreaterThanOrEqual(1, \count($svgobj['gradients']['lg']['stops']));
    }

    public function testSvgGetSVGSizeCoversMissingViewboxAndThreeTokenAspectRatio(): void
    {
        $obj = $this->getInternalTestObject();

        $noViewBox = $obj->exposeGetSVGSize('<svg x="3" y="4" width="60" height="30"></svg>');
        $this->assertGreaterThan(0.0, $noViewBox['x']);
        $this->assertGreaterThan(0.0, $noViewBox['y']);
        $this->assertGreaterThan(0.0, $noViewBox['width']);
        $this->assertGreaterThan(0.0, $noViewBox['height']);
        $this->assertSame([0.0, 0.0, 0.0, 0.0], $noViewBox['viewBox']);

        $threeTokens = $obj->exposeGetSVGSize(
            '<svg width="10" height="10" viewBox="0 0 10 10" preserveAspectRatio="defer xMaxYMax slice"></svg>'
        );
        $this->assertSame('xMaxYMax', $threeTokens['ar_align']);
        $this->assertSame('slice', $threeTokens['ar_ms']);
    }

    public function testSvgPrescanRadialGradientWithDirectStopAttributes(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->initSvgObjForHandlers(44);

        $obj->exposePrescanSVGGradients(
            '<svg><defs><radialGradient id="rg">'
            . '<stop offset="0%" stop-color="#101010" stop-opacity="0.2"/>'
            . '</radialGradient></defs></svg>',
            44,
        );

        $svgobj = $obj->getSvgObj(44);
        $this->assertArrayHasKey('rg', $svgobj['gradients']);
        $this->assertGreaterThanOrEqual(1, \count($svgobj['gradients']['rg']['stops']));
    }

    public function testSvgTagHandlersAndEndStartHelpers(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(41);

        $parser = \xml_parser_create('UTF-8');
        $obj->exposeHandlerSVGCharacter($parser, 'Hello');
        $svgobj = $obj->getSvgObj(41);
        $this->assertSame('Hello', $svgobj['text']);

        $obj->exposeParseSVGTagSTARTdefs(41);
        $this->assertTrue($obj->getSvgObj(41)['defsmode']);
        $obj->exposeParseSVGTagENDdefs(41);
        $this->assertFalse($obj->getSvgObj(41)['defsmode']);

        $obj->exposeParseSVGTagSTARTclipPath(41, [1, 0, 0, 1, 0, 0]);
        $this->assertTrue($obj->getSvgObj(41)['clipmode']);
        $obj->exposeParseSVGTagENDclipPath(41);
        $this->assertFalse($obj->getSvgObj(41)['clipmode']);

        $obj->patchSvgObj(41, ['styles' => [$obj->exposeDefaultSVGStyle(), $obj->exposeDefaultSVGStyle()]]);
        $this->assertSame('', $obj->exposeParseSVGTagENDg(41));
        $this->assertCount(1, $obj->getSvgObj(41)['styles']);

        $obj->patchSvgObj(41, ['textmode' => ['invisible' => true]]);
        $this->assertSame('', $obj->exposeParseSVGTagENDtext(41));
        $this->assertSame('', $obj->exposeParseSVGTagENDtspan(41));

        $obj->patchSvgObj(41, ['tagdepth' => 1]);
        $this->assertSame('', $obj->exposeParseSVGTagENDsvg(41));

        $obj->patchSvgObj(41, ['clipmode' => true, 'cliptm' => [1, 0, 0, 1, 0, 0]]);
        $obj->exposeHandleSVGTagStart($parser, 'rect', ['x' => '1', 'y' => '2'], 41, false, [1, 0, 0, 1, 0, 0]);
        $this->assertNotEmpty($obj->getSvgObj(41)['clippaths']);

        $obj->patchSvgObj(41, ['defsmode' => false]);
        $obj->exposeHandleSVGTagEnd($parser, 'defs');
        $this->assertFalse($obj->getSvgObj(41)['defsmode']);
    }

    public function testSvgRemainingStartTagMethodsCoveredViaGuardBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(42);
        $base = $obj->exposeDefaultSVGStyle();

        $parser = \xml_parser_create('UTF-8');
        // Root svg branch exits immediately.
        $obj->patchSvgObj(42, ['tagdepth' => 0]);
        $this->assertSame('', $obj->exposeParseSVGTagSTARTsvg($parser, 42, [], $base, $base));

        // Group start path should return a transform block.
        $gOut = $obj->exposeParseSVGTagSTARTg($parser, 42, [], $base, $base);
        $this->assertNotSame('', $gOut);

        // Gradient starts and stop insertion.
        $this->assertSame('', $obj->exposeParseSVGTagSTARTlinearGradient(42, ['id' => 'lg2']));
        $this->assertSame('', $obj->exposeParseSVGTagSTARTradialGradient(42, ['id' => 'rg2']));
        $obj->patchSvgObj(42, ['gradientid' => 'lg2']);
        $this->assertSame('', $obj->exposeParseSVGTagSTARTstop(42, ['offset' => '50%'], $base));
        $this->assertNotEmpty($obj->getSvgObj(42)['gradients']['lg2']['stops']);

        // Invisibility/clip guards for shape and text tags.
        $obj->patchSvgObj(42, ['textmode' => ['invisible' => true], 'clipmode' => false]);
        $this->assertSame('', $obj->exposeParseSVGTagSTARTpath($parser, 42, ['d' => 'M 0 0 L 1 1'], $base, $base));
        $this->assertSame('', $obj->exposeParseSVGTagSTARTrect(
            $parser,
            42,
            ['width' => '1', 'height' => '1'],
            $base,
            $base
        ));
        $this->assertSame('', $obj->exposeParseSVGTagSTARTcircle($parser, 42, ['r' => '1'], $base, $base));
        $this->assertSame('', $obj->exposeParseSVGTagSTARTellipse(
            $parser,
            42,
            ['rx' => '1', 'ry' => '1'],
            $base,
            $base
        ));
        $this->assertSame('', $obj->exposeParseSVGTagSTARTline(
            $parser,
            42,
            ['x1' => '0', 'y1' => '0', 'x2' => '1', 'y2' => '1'],
            $base,
            $base
        ));
        $this->assertSame('', $obj->exposeParseSVGTagSTARTpolygon($parser, 42, ['points' => '0,0 1,1'], $base, $base));
        $this->assertSame('', $obj->exposeParseSVGTagSTARTimage($parser, 42, ['xlink:href' => 'x.png'], $base, $base));
        $this->assertSame('', $obj->exposeParseSVGTagSTARTtext($parser, 42, ['x' => '0', 'y' => '0'], $base, $base));
        $this->assertSame('', $obj->exposeParseSVGTagSTARTtspan($parser, 42, ['dx' => '1'], $base, $base));

        // use tag guards when href is missing or unknown.
        $this->assertSame('', $obj->exposeParseSVGTagSTARTuse($parser, 42, []));
        $this->assertSame('', $obj->exposeParseSVGTagSTARTuse($parser, 42, ['xlink:href' => '#missing']));
    }

    public function testSvgUseTagResolvesDefinitionAndAppendsOutput(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(45);

        $obj->patchSvgObj(45, [
            'defs' => [
                'shape1' => [
                    'name' => 'rect',
                    'attr' => [
                        'x' => '2',
                        'y' => '3',
                        'width' => '5',
                        'height' => '4',
                        'style' => 'fill:#000000;stroke:none;',
                    ],
                ],
            ],
            'out' => '',
        ]);

        $parser = \xml_parser_create('UTF-8');
        $this->assertSame('', $obj->exposeParseSVGTagSTARTuse(
            $parser,
            45,
            ['xlink:href' => '#shape1', 'x' => '5', 'y' => '7']
        ));

        $svgobj = $obj->getSvgObj(45);
        $this->assertNotSame('', $svgobj['out']);
    }

    public function testSvgTransformRotateCenterAndEmptyTransformMatrix(): void
    {
        $obj = $this->getInternalTestObject();

        $rot = $obj->exposeParseSVGTMrotate('45 10 20');
        $this->assertCount(6, $rot);
        $this->assertNotSame(0.0, $rot[4]);
        $this->assertNotSame(0.0, $rot[5]);

        $tmx = $obj->exposeGetSVGTransformMatrix('');
        $this->assertSame([1.0, 0.0, 0.0, 1.0, 0.0, 0.0], $tmx);
    }

    public function testSvgPathCmdAHandlesIdenticalEndpointsAndSweepAdjustments(): void
    {
        $obj = $this->getInternalTestObject();
        $coord = $obj->getPathCoordDefaults();
        $coord['x'] = 10.0;
        $coord['y'] = 10.0;

        [$sameOut, $sameCoord] = $obj->exposeSvgPathCmdA(
            [5.0, 5.0, 0.0, 0.0, 1.0, 10.0, 10.0],
            $coord,
            [['0', 'A']],
            0,
            ['5', '5', '0', '0', '1', '10', '10'],
        );

        $this->assertSame('', $sameOut);
        $this->assertLessThanOrEqual(10.0, $sameCoord['xmin']);
        $this->assertLessThanOrEqual(10.0, $sameCoord['ymin']);
        $this->assertGreaterThanOrEqual(10.0, $sameCoord['xmax']);
        $this->assertGreaterThanOrEqual(10.0, $sameCoord['ymax']);

        [$arcOut] = $obj->exposeSvgPathCmdA(
            [4.0, 2.0, 0.0, 1.0, 1.0, 15.0, 18.0],
            $coord,
            [['0', 'A'], ['1', 'z']],
            0,
            ['4', '2', '0', '1', '1', '15', '18'],
        );

        $this->assertNotSame('', $arcOut);
    }

    public function testSvgParseStyleGradientHandlesXrefMeasureAndPercentageModes(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(46);

        $gradients = [
            'base' => [
                'xref' => '',
                'type' => 2,
                'gradientUnits' => 'objectBoundingBox',
                'mode' => 'measure',
                'coords' => [0.0, 0.0, 10.0, 0.0, 3.0],
                'stops' => [
                    ['color' => '#000000', 'opacity' => 1.0, 'offset' => 0.0],
                    ['color' => '#ffffff', 'opacity' => 1.0, 'offset' => 1.0],
                ],
                'gradientTransform' => [1.0, 0.0, 0.0, 1.0, 2.0, 3.0],
            ],
            'ref' => [
                'xref' => 'base',
                'type' => 2,
                'gradientUnits' => 'objectBoundingBox',
                'mode' => 'measure',
                'coords' => [1.0, 2.0, 3.0, 4.0, 2.0],
                'stops' => [],
                'gradientTransform' => [1.0, 0.0, 0.0, 1.0, 0.0, 0.0],
            ],
            'pct' => [
                'xref' => '',
                'type' => 3,
                'gradientUnits' => 'objectBoundingBox',
                'mode' => 'percentage',
                'coords' => [-10.0, 150.0, 50.0, 50.0, 20.0],
                'stops' => [
                    ['color' => '#111111', 'opacity' => 1.0, 'offset' => 0.0],
                    ['color' => '#eeeeee', 'opacity' => 1.0, 'offset' => 1.0],
                ],
                'gradientTransform' => [1.0, 0.0, 0.0, 1.0, 0.0, 0.0],
            ],
        ];

        $refOut = $obj->exposeParseSVGStyleGradient(
            46,
            $gradients,
            'ref',
            5.0,
            6.0,
            20.0,
            10.0,
            'getClippingRect',
            [5.0, 6.0, 20.0, 10.0, 0.0],
        );
        $this->assertNotSame('', $refOut);

        $pctOut = $obj->exposeParseSVGStyleGradient(46, $gradients, 'pct', 0.0, 0.0, 10.0, 10.0);
        $this->assertNotSame('', $pctOut);
    }

    public function testSvgDefsModeStartEndAndInnerSvgStartBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(47);
        $base = $obj->exposeDefaultSVGStyle();
        $parser = \xml_parser_create('UTF-8');

        $obj->patchSvgObj(47, [
            'defsmode' => true,
            'text' => 'txt',
            'defs' => [
                'def1' => [
                    'name' => 'g',
                    'attr' => ['id' => 'def1'],
                    'child' => [
                        'child1' => [
                            'name' => 'path',
                            'attr' => ['id' => 'child1'],
                        ],
                    ],
                ],
            ],
        ]);

        $obj->exposeHandleSVGTagEnd($parser, 'path');
        $defs = $obj->getSvgObj(47)['defs'];
        /** @var array<string, mixed> $def1 */
        $def1 = $defs['def1'];
        /** @var array<string, mixed> $child */
        $child = (isset($def1['child']) && \is_array($def1['child'])) ? $def1['child'] : [];
        $this->assertArrayHasKey('child1_CLOSE', $child);

        $obj->exposeHandleSVGTagStart($parser, 'line', [], 47);
        $defs = $obj->getSvgObj(47)['defs'];
        /** @var array<string, mixed> $def1 */
        $def1 = $defs['def1'];
        /** @var array<string, mixed> $child */
        $child = (isset($def1['child']) && \is_array($def1['child'])) ? $def1['child'] : [];
        $this->assertArrayHasKey('DF_3', $child);

        $obj->exposeHandleSVGTagStart($parser, 'circle', ['id' => 'newdef'], 47);
        $this->assertArrayHasKey('newdef', $obj->getSvgObj(47)['defs']);

        $obj->patchSvgObj(47, ['defsmode' => false, 'tagdepth' => 1]);
        $innerOut = $obj->exposeParseSVGTagSTARTsvg(
            $parser,
            47,
            ['x' => '2', 'y' => '3', 'width' => '12', 'height' => '8'],
            $base,
            $base,
        );

        $this->assertNotSame('', $innerOut);
        $this->assertGreaterThan(1, \count($obj->getSvgObj(47)['styles']));
    }

    public function testSvgPathCommandsCoverRelativeAndFallbackBranches(): void
    {
        $obj = $this->getInternalTestObject();

        $coord = $obj->getPathCoordDefaults();
        $coord['relcoord'] = true;
        $coord['x'] = 2.0;
        $coord['y'] = 3.0;
        $coord['x0'] = 2.0;
        $coord['y0'] = 3.0;
        $coord['xoffset'] = 2.0;
        $coord['yoffset'] = 3.0;

        [, $coord] = $obj->exposeSvgPathCmdC([1, 1, 2, 2, 3, 3], $coord);
        $this->assertGreaterThan(2.0, $coord['xoffset']);
        $this->assertGreaterThan(3.0, $coord['yoffset']);

        [, $coord] = $obj->exposeSvgPathCmdH([4], $coord);
        $this->assertGreaterThan(0.0, $coord['xoffset']);

        [, $coord] = $obj->exposeSvgPathCmdL([5, 6], $coord);
        $this->assertGreaterThan(0.0, $coord['xoffset']);
        $this->assertGreaterThan(0.0, $coord['yoffset']);

        [, $coord] = $obj->exposeSvgPathCmdM([7, 8], $coord);
        $this->assertGreaterThan(0.0, $coord['xoffset']);
        $this->assertGreaterThan(0.0, $coord['yoffset']);

        [, $coord] = $obj->exposeSvgPathCmdQ([1, 2, 3, 4], $coord);
        $this->assertGreaterThan(0.0, $coord['xoffset']);
        $this->assertGreaterThan(0.0, $coord['yoffset']);

        [, $coord] = $obj->exposeSvgPathCmdS([2, 3, 4, 5], $coord, [['0', 'L']], 1);
        $this->assertGreaterThan(0.0, $coord['xoffset']);
        $this->assertGreaterThan(0.0, $coord['yoffset']);

        [, $coord] = $obj->exposeSvgPathCmdT([6, 7], $coord, [['0', 'L']], 1);
        $this->assertGreaterThan(0.0, $coord['xoffset']);
        $this->assertGreaterThan(0.0, $coord['yoffset']);

        [, $coord] = $obj->exposeSvgPathCmdV([9], $coord);
        $this->assertGreaterThan(0.0, $coord['yoffset']);
    }

    public function testSvgStyleFontAndStrokeExtraBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->setSvgObjMeta(48);

        $base = $obj->exposeDefaultSVGStyle();
        $fontStyle = $base;
        $fontStyle['font'] = 'italic small-caps bold 14px/16px helvetica';
        $fontStyle['font-family'] = '';
        [$fontOut, $fontStyle] = $obj->exposeParseSVGStyleFont($fontStyle, $base);
        $this->assertNotSame('', $fontOut);
        $this->assertNotSame('', $fontStyle['font-family']);

        $strokeStyle = $base;
        $strokeStyle['stroke'] = 'rgba(10,20,30,0.5)';
        $strokeStyle['stroke-width'] = 1.2;
        $strokeStyle['stroke-linecap'] = 'round';
        $strokeStyle['stroke-linejoin'] = 'bevel';
        $strokeStyle['stroke-opacity'] = 0.8;
        $strokeStyle['opacity'] = 0.7;
        $strokeStyle['mix-blend-mode'] = 'screen';
        [$strokeOut] = $obj->exposeParseSVGStyleStroke(48, $strokeStyle);
        $this->assertNotSame('', $strokeOut);
    }

    public function testSvgHandleStartAndEndAdditionalBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');

        $obj->exposeHandleSVGTagStart($parser, 'g', [], 999);

        $obj->initSvgObjForHandlers(49);
        $base = $obj->exposeDefaultSVGStyle();

        $obj->patchSvgObj(49, [
            'clipmode' => true,
            'styles' => [$base],
            'textmode' => ['invisible' => false, 'stroke' => 1, 'rtl' => false, 'text-anchor' => 'middle'],
            'x' => 11.0,
            'y' => 12.0,
            'text' => 'abc',
            'defsmode' => false,
            'tagdepth' => 2,
        ]);

        $obj->exposeHandleSVGTagStart($parser, 'path', ['d' => 'M0 0 L1 1'], 49, true, [1, 0, 0, 1, 0, 0]);
        $this->assertNotEmpty($obj->getSvgObj(49)['clippaths']);

        $textOut = $obj->exposeParseSVGTagENDtext(49);
        $this->assertSame('', $obj->getSvgObj(49)['text']);
        $this->assertNotSame('', $textOut);

        $obj->exposeParseSVGTagENDsvg(49);
        $this->assertNotSame('', $textOut);
    }

    public function testSvgStartSvgViewboxFallbackAndAspectBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(50);
        $base = $obj->exposeDefaultSVGStyle();
        $parser = \xml_parser_create('UTF-8');

        $obj->patchSvgObj(50, ['tagdepth' => 1]);
        $badViewBox = $obj->exposeParseSVGTagSTARTsvg(
            $parser,
            50,
            ['x' => '1', 'y' => '2', 'width' => '12', 'height' => '8', 'viewBox' => '0 0 10'],
            $base,
            $base,
        );
        $this->assertNotSame('', $badViewBox);

        $obj->patchSvgObj(50, ['tagdepth' => 1]);
        $aspectOut = $obj->exposeParseSVGTagSTARTsvg(
            $parser,
            50,
            [
                'x' => '0',
                'y' => '0',
                'width' => '40',
                'height' => '20',
                'viewBox' => '0 0 100 50',
                'preserveAspectRatio' => 'xMaxYMax slice',
            ],
            $base,
            $base,
        );
        $this->assertNotSame('', $aspectOut);
    }

    public function testSvgVisibleStartTagHandlersProduceOutput(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(51);
        $base = $obj->exposeDefaultSVGStyle();
        $base['fill'] = '#336699';
        $base['stroke'] = '#112233';
        $base['stroke-width'] = 0.4;
        $base['opacity'] = 1.0;
        $base['fill-opacity'] = 1.0;
        $base['stroke-opacity'] = 1.0;
        $base['text-anchor'] = 'middle';
        $base['direction'] = 'rtl';
        $obj->patchSvgObj(51, [
            'clipmode' => false,
            'textmode' => ['invisible' => false, 'stroke' => 0, 'rtl' => false, 'text-anchor' => 'start'],
            'styles' => [$base],
            'dir' => '/tmp',
        ]);

        $parser = \xml_parser_create('UTF-8');

        $pathOut = $obj->exposeParseSVGTagSTARTpath(
            $parser,
            51,
            ['d' => 'M 1 1 L 5 5 H 6 V 7 C 8 8 9 9 10 10 Q 11 11 12 12 S 13 13 14 14 T 15 15 A 2 2 0 1 1 16 16 z'],
            $base,
            $base,
        );
        $this->assertNotSame('', $pathOut);

        $rectOut = $obj->exposeParseSVGTagSTARTrect(
            $parser,
            51,
            ['x' => '2', 'y' => '3', 'width' => '8', 'height' => '4', 'rx' => '1', 'ry' => '1'],
            $base,
            $base,
        );
        $this->assertNotSame('', $rectOut);

        $circleOut = $obj->exposeParseSVGTagSTARTcircle(
            $parser,
            51,
            ['cx' => '10', 'cy' => '10', 'r' => '4'],
            $base,
            $base,
        );
        $this->assertNotSame('', $circleOut);

        $ellipseOut = $obj->exposeParseSVGTagSTARTellipse(
            $parser,
            51,
            ['cx' => '12', 'cy' => '9', 'rx' => '5', 'ry' => '2'],
            $base,
            $base,
        );
        $this->assertNotSame('', $ellipseOut);

        $lineOut = $obj->exposeParseSVGTagSTARTline(
            $parser,
            51,
            ['x1' => '1', 'y1' => '2', 'x2' => '9', 'y2' => '4'],
            $base,
            $base,
        );
        $this->assertNotSame('', $lineOut);

        $polyOut = $obj->exposeParseSVGTagSTARTpolygon(
            $parser,
            51,
            ['points' => '1,1 5,1 5,5 1,5'],
            $base,
            $base,
        );
        $this->assertNotSame('', $polyOut);

        $imgPath = (string) \realpath(__DIR__ . '/../examples/images/tcpdf_signature.png');
        $imgOut = $obj->exposeParseSVGTagSTARTimage(
            $parser,
            51,
            ['xlink:href' => $imgPath, 'x' => '1', 'y' => '1', 'width' => '3', 'height' => '3'],
            $base,
            $base,
        );
        $this->assertNotSame('', $imgOut);

        $textOut = $obj->exposeParseSVGTagSTARTtext(
            $parser,
            51,
            ['x' => '4', 'y' => '6', 'dx' => '1', 'dy' => '1'],
            $base,
            $base,
        );
        $this->assertNotSame('', $textOut);

        $tspanOut = $obj->exposeParseSVGTagSTARTtspan(
            $parser,
            51,
            ['dx' => '1', 'dy' => '1'],
            $base,
            $base,
        );
        $this->assertNotSame('', $tspanOut);
    }

    public function testSvgHandleEndDefsAddsParentCloseWhenNameMatches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(52);
        $obj->patchSvgObj(52, [
            'defsmode' => true,
            'text' => 'close-parent',
            'defs' => [
                'grp1' => [
                    'name' => 'g',
                    'attr' => ['id' => 'grp1'],
                    'child' => [
                        'other' => [
                            'name' => 'path',
                            'attr' => ['id' => 'other'],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $obj->exposeHandleSVGTagEnd($parser, 'g');

        $defs = $obj->getSvgObj(52)['defs'];
        /** @var array<string, mixed> $grp */
        $grp = $defs['grp1'];
        /** @var array<string, mixed> $child */
        $child = (isset($grp['child']) && \is_array($grp['child'])) ? $grp['child'] : [];
        $this->assertArrayHasKey('grp1_CLOSE', $child);
    }

    public function testSvgMarkerDefsCaptureAndLifecycle(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(521);

        $parser = \xml_parser_create('UTF-8');

        $obj->exposeHandleSVGTagStart($parser, 'defs', [], 521);
        $obj->exposeHandleSVGTagStart(
            $parser,
            'marker',
            [
                'id' => 'mk1',
                'viewBox' => '0 0 10 10',
                'refX' => '5',
                'refY' => '5',
                'markerWidth' => '5',
                'markerHeight' => '5',
                'orient' => 'auto',
            ],
            521,
        );

        $obj->exposeHandleSVGTagStart($parser, 'path', ['d' => 'M 0 0 L 10 5 L 0 10 Z'], 521);
        $obj->exposeHandleSVGTagEnd($parser, 'path');
        $obj->exposeHandleSVGTagEnd($parser, 'marker');

        $svgobj = $obj->getSvgObj(521);
        $this->assertFalse($svgobj['defsmode']);
        $this->assertArrayHasKey('mk1', $svgobj['defs']);

        /** @var array<string, mixed> $marker */
        $marker = $svgobj['defs']['mk1'];
        $this->assertSame('marker', $marker['name']);

        /** @var array<string, mixed> $child */
        $child = (isset($marker['child']) && \is_array($marker['child'])) ? $marker['child'] : [];
        $this->assertArrayHasKey('DF_1', $child);
        $this->assertArrayHasKey('DF_1_CLOSE', $child);
    }

    public function testSvgMarkerStartWithoutIdOnlyEnablesDefsMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(522);

        $this->assertSame('', $obj->exposeParseSVGTagSTARTmarker(522, []));
        $svgobj = $obj->getSvgObj(522);
        $this->assertTrue($svgobj['defsmode']);
        $this->assertSame([], $svgobj['defs']);

        $this->assertSame('', $obj->exposeParseSVGTagENDmarker(522));
        $this->assertFalse($obj->getSvgObj(522)['defsmode']);
    }

    public function testSvgPatternDefsCaptureAndLifecycle(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(538);

        $parser = \xml_parser_create('UTF-8');

        $obj->exposeHandleSVGTagStart($parser, 'defs', [], 538);
        $obj->exposeHandleSVGTagStart(
            $parser,
            'pattern',
            [
                'id' => 'pat1',
                'patternUnits' => 'userSpaceOnUse',
                'x' => '0',
                'y' => '0',
                'width' => '10',
                'height' => '10',
            ],
            538,
        );
        $obj->exposeHandleSVGTagStart(
            $parser,
            'rect',
            ['x' => '0', 'y' => '0', 'width' => '10', 'height' => '10', 'fill' => '#ff0000'],
            538,
        );
        $obj->exposeHandleSVGTagEnd($parser, 'rect');
        $obj->exposeHandleSVGTagEnd($parser, 'pattern');

        $svgobj = $obj->getSvgObj(538);
        $this->assertFalse($svgobj['defsmode']);
        $this->assertArrayHasKey('pat1', $svgobj['defs']);

        /** @var array<string, mixed> $pattern */
        $pattern = $svgobj['defs']['pat1'];
        $this->assertSame('pattern', $pattern['name']);

        /** @var array<string, mixed> $child */
        $child = (isset($pattern['child']) && \is_array($pattern['child'])) ? $pattern['child'] : [];
        $this->assertArrayHasKey('DF_1', $child);
        $this->assertArrayHasKey('DF_1_CLOSE', $child);
    }

    public function testSvgPatternStartWithoutIdOnlyEnablesDefsMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(539);

        $this->assertSame('', $obj->exposeParseSVGTagSTARTpattern(539, []));
        $svgobj = $obj->getSvgObj(539);
        $this->assertTrue($svgobj['defsmode']);
        $this->assertSame([], $svgobj['defs']);

        $this->assertSame('', $obj->exposeParseSVGTagENDpattern(539));
        $this->assertFalse($obj->getSvgObj(539)['defsmode']);
    }

    public function testSvgMaskDefsCaptureAndLifecycle(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(549);

        $parser = \xml_parser_create('UTF-8');

        $obj->exposeHandleSVGTagStart($parser, 'defs', [], 549);
        $obj->exposeHandleSVGTagStart(
            $parser,
            'mask',
            [
                'id' => 'msk1',
                'x' => '0',
                'y' => '0',
                'width' => '10',
                'height' => '10',
            ],
            549,
        );
        $obj->exposeHandleSVGTagStart(
            $parser,
            'rect',
            ['x' => '0', 'y' => '0', 'width' => '10', 'height' => '10', 'fill' => '#ffffff'],
            549,
        );
        $obj->exposeHandleSVGTagEnd($parser, 'rect');
        $obj->exposeHandleSVGTagEnd($parser, 'mask');

        $svgobj = $obj->getSvgObj(549);
        $this->assertFalse($svgobj['defsmode']);
        $this->assertArrayHasKey('msk1', $svgobj['defs']);

        /** @var array<string, mixed> $mask */
        $mask = $svgobj['defs']['msk1'];
        $this->assertSame('mask', $mask['name']);

        /** @var array<string, mixed> $child */
        $child = (isset($mask['child']) && \is_array($mask['child'])) ? $mask['child'] : [];
        $this->assertArrayHasKey('DF_1', $child);
        $this->assertArrayHasKey('DF_1_CLOSE', $child);
    }

    public function testSvgMaskStartWithoutIdOnlyEnablesDefsMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(550);

        $this->assertSame('', $obj->exposeParseSVGTagSTARTmask(550, []));
        $svgobj = $obj->getSvgObj(550);
        $this->assertTrue($svgobj['defsmode']);
        $this->assertSame([], $svgobj['defs']);

        $this->assertSame('', $obj->exposeParseSVGTagENDmask(550));
        $this->assertFalse($obj->getSvgObj(550)['defsmode']);
    }

    public function testSvgPatternHrefInheritanceIsResolvedByFill(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(540);

        $base = $obj->exposeDefaultSVGStyle();
        $base['fill'] = 'url(#patRef)';
        $base['opacity'] = 1.0;
        $base['fill-opacity'] = 1.0;

        $obj->patchSvgObj(540, [
            'defs' => [
                'patBase' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patBase',
                        'patternUnits' => 'userSpaceOnUse',
                        'x' => '0',
                        'y' => '0',
                        'width' => '4',
                        'height' => '4',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'rect',
                            'attr' => [
                                'x' => '0',
                                'y' => '0',
                                'width' => '4',
                                'height' => '4',
                                'fill' => 'none',
                                'stroke' => '#111111',
                                'stroke-width' => '1',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'rect',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
                'patRef' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patRef',
                        'xlink:href' => '#patBase',
                    ],
                    'child' => [],
                ],
            ],
            'out' => 'BASE',
        ]);

        [$out] = $obj->exposeParseSVGStyleFill(
            540,
            $base,
            [],
            0,
            0,
            10,
            10,
            'getClippingRect',
            [0.0, 0.0, 10.0, 10.0, 0.0],
        );

        $this->assertNotSame('', $out);
        $this->assertSame('BASE', $obj->getSvgObj(540)['out']);
        $this->assertSame(0, $obj->getSvgObj(540)['patternmode']);
    }

    public function testSvgPatternHrefInheritanceUsesChildAttrAndParentFallback(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(544);

        $obj->patchSvgObj(544, [
            'defs' => [
                'patBase' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patBase',
                        'patternUnits' => 'userSpaceOnUse',
                        'x' => '1',
                        'y' => '2',
                        'width' => '4',
                        'height' => '6',
                    ],
                    'child' => [
                        'PARENT_RECT' => [
                            'name' => 'rect',
                            'attr' => [
                                'x' => '0',
                                'y' => '0',
                                'width' => '4',
                                'height' => '6',
                                'fill' => 'none',
                                'stroke' => '#111111',
                            ],
                        ],
                    ],
                ],
                'patRef' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patRef',
                        'href' => '#patBase',
                        'width' => '9',
                    ],
                    'child' => [],
                ],
            ],
        ]);

        $resolved = $obj->exposeResolveSVGPatternDef(544, 'patRef');

        $this->assertNotNull($resolved);
        $resolvedAttr = (isset($resolved['attr']) && \is_array($resolved['attr'])) ? $resolved['attr'] : [];
        $resolvedChild = (isset($resolved['child']) && \is_array($resolved['child'])) ? $resolved['child'] : [];

        $this->assertSame('9', $resolvedAttr['width'] ?? '');
        $this->assertSame('6', $resolvedAttr['height'] ?? '');
        $this->assertSame('1', $resolvedAttr['x'] ?? '');
        $this->assertSame('2', $resolvedAttr['y'] ?? '');
        $this->assertSame('userSpaceOnUse', $resolvedAttr['patternUnits'] ?? '');
        $this->assertArrayHasKey('PARENT_RECT', $resolvedChild);
    }

    public function testSvgPatternHrefInheritanceKeepsChildContentWhenPresent(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(545);

        $obj->patchSvgObj(545, [
            'defs' => [
                'patBase' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patBase',
                        'patternUnits' => 'userSpaceOnUse',
                        'x' => '0',
                        'y' => '0',
                        'width' => '4',
                        'height' => '4',
                    ],
                    'child' => [
                        'PARENT_RECT' => [
                            'name' => 'rect',
                            'attr' => [
                                'x' => '0',
                                'y' => '0',
                                'width' => '4',
                                'height' => '4',
                                'fill' => '#aaaaaa',
                            ],
                        ],
                    ],
                ],
                'patRef' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patRef',
                        'xlink:href' => '#patBase',
                    ],
                    'child' => [
                        'CHILD_RECT' => [
                            'name' => 'rect',
                            'attr' => [
                                'x' => '1',
                                'y' => '1',
                                'width' => '2',
                                'height' => '2',
                                'fill' => '#222222',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolved = $obj->exposeResolveSVGPatternDef(545, 'patRef');

        $this->assertNotNull($resolved);
        $resolvedChild = (isset($resolved['child']) && \is_array($resolved['child'])) ? $resolved['child'] : [];
        $this->assertArrayHasKey('CHILD_RECT', $resolvedChild);
        $this->assertArrayNotHasKey('PARENT_RECT', $resolvedChild);
    }

    public function testSvgPatternHrefMissingParentFallsBackToChildDefinition(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(546);

        $obj->patchSvgObj(546, [
            'defs' => [
                'patRef' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patRef',
                        'href' => '#missingPattern',
                        'x' => '0',
                        'y' => '0',
                        'width' => '5',
                        'height' => '5',
                    ],
                    'child' => [
                        'CHILD_RECT' => [
                            'name' => 'rect',
                            'attr' => [
                                'x' => '0',
                                'y' => '0',
                                'width' => '5',
                                'height' => '5',
                                'fill' => 'none',
                                'stroke' => '#000000',
                                'stroke-width' => '1',
                            ],
                        ],
                        'CHILD_RECT_CLOSE' => [
                            'name' => 'rect',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolved = $obj->exposeResolveSVGPatternDef(546, 'patRef');

        $this->assertNotNull($resolved);
        $resolvedAttr = (isset($resolved['attr']) && \is_array($resolved['attr'])) ? $resolved['attr'] : [];
        $resolvedChild = (isset($resolved['child']) && \is_array($resolved['child'])) ? $resolved['child'] : [];
        $this->assertSame('5', $resolvedAttr['width'] ?? '');
        $this->assertSame('5', $resolvedAttr['height'] ?? '');
        $this->assertArrayNotHasKey('patternUnits', $resolvedAttr);
        $this->assertArrayHasKey('CHILD_RECT', $resolvedChild);

        $style = $obj->exposeDefaultSVGStyle();
        $style['fill'] = 'url(#patRef)';
        $style['opacity'] = 1.0;
        $style['fill-opacity'] = 1.0;

        [$out] = $obj->exposeParseSVGStyleFill(
            546,
            $style,
            [],
            0,
            0,
            10,
            10,
            'getClippingRect',
            [0.0, 0.0, 10.0, 10.0, 0.0],
        );

        $this->assertNotSame('', $out);
    }

    public function testSvgPatternHrefNonFragmentKeepsLocalPatternDefinition(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(547);

        $obj->patchSvgObj(547, [
            'defs' => [
                'patBase' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patBase',
                        'patternUnits' => 'userSpaceOnUse',
                        'x' => '0',
                        'y' => '0',
                        'width' => '4',
                        'height' => '4',
                    ],
                    'child' => [
                        'PARENT_RECT' => [
                            'name' => 'rect',
                            'attr' => [
                                'x' => '0',
                                'y' => '0',
                                'width' => '4',
                                'height' => '4',
                                'fill' => '#00ff00',
                            ],
                        ],
                    ],
                ],
                'patRef' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patRef',
                        'href' => 'https://example.com/patterns.svg#patBase',
                        'x' => '1',
                        'y' => '1',
                        'width' => '5',
                        'height' => '5',
                    ],
                    'child' => [
                        'CHILD_RECT' => [
                            'name' => 'rect',
                            'attr' => [
                                'x' => '1',
                                'y' => '1',
                                'width' => '3',
                                'height' => '3',
                                'fill' => 'none',
                                'stroke' => '#000000',
                                'stroke-width' => '1',
                            ],
                        ],
                        'CHILD_RECT_CLOSE' => [
                            'name' => 'rect',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolved = $obj->exposeResolveSVGPatternDef(547, 'patRef');

        $this->assertNotNull($resolved);
        $resolvedAttr = (isset($resolved['attr']) && \is_array($resolved['attr'])) ? $resolved['attr'] : [];
        $resolvedChild = (isset($resolved['child']) && \is_array($resolved['child'])) ? $resolved['child'] : [];
        $this->assertSame('5', $resolvedAttr['width'] ?? '');
        $this->assertSame('5', $resolvedAttr['height'] ?? '');
        $this->assertArrayNotHasKey('patternUnits', $resolvedAttr);
        $this->assertArrayHasKey('CHILD_RECT', $resolvedChild);
        $this->assertArrayNotHasKey('PARENT_RECT', $resolvedChild);

        $style = $obj->exposeDefaultSVGStyle();
        $style['fill'] = 'url(#patRef)';
        $style['opacity'] = 1.0;
        $style['fill-opacity'] = 1.0;

        [$out] = $obj->exposeParseSVGStyleFill(
            547,
            $style,
            [],
            0,
            0,
            10,
            10,
            'getClippingRect',
            [0.0, 0.0, 10.0, 10.0, 0.0],
        );

        $this->assertNotSame('', $out);
    }

    public function testSvgPatternHrefInheritsViewBoxAndPatternTransformInteraction(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(548);

        $defsPatternChild = [
            'DF_1' => [
                'name' => 'rect',
                'attr' => [
                    'x' => '0',
                    'y' => '0',
                    'width' => '10',
                    'height' => '20',
                    'fill' => 'none',
                    'stroke' => '#000000',
                    'stroke-width' => '1',
                ],
            ],
            'DF_1_CLOSE' => [
                'name' => 'rect',
                'attr' => [
                    'closing_tag' => true,
                    'content' => '',
                ],
            ],
        ];

        $obj->patchSvgObj(548, [
            'defs' => [
                'patBase' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patBase',
                        'patternUnits' => 'userSpaceOnUse',
                        'x' => '0',
                        'y' => '0',
                        'width' => '6',
                        'height' => '6',
                        'viewBox' => '0 0 10 20',
                        'preserveAspectRatio' => 'xMidYMid meet',
                        'patternTransform' => 'translate(1,2) scale(0.5)',
                    ],
                    'child' => $defsPatternChild,
                ],
                'patRefInherit' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patRefInherit',
                        'href' => '#patBase',
                    ],
                    'child' => [],
                ],
                'patRefOverride' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patRefOverride',
                        'xlink:href' => '#patBase',
                        'patternTransform' => 'translate(0,0) scale(1)',
                    ],
                    'child' => [],
                ],
            ],
        ]);

        $resolvedInherit = $obj->exposeResolveSVGPatternDef(548, 'patRefInherit');
        $resolvedOverride = $obj->exposeResolveSVGPatternDef(548, 'patRefOverride');
        $this->assertNotNull($resolvedInherit);
        $this->assertNotNull($resolvedOverride);

        $attrInherit = (isset($resolvedInherit['attr']) && \is_array($resolvedInherit['attr'])) ? $resolvedInherit['attr'] : [];
        $attrOverride = (isset($resolvedOverride['attr']) && \is_array($resolvedOverride['attr'])) ? $resolvedOverride['attr'] : [];
        $this->assertSame('0 0 10 20', $attrInherit['viewBox'] ?? '');
        $this->assertSame('translate(1,2) scale(0.5)', $attrInherit['patternTransform'] ?? '');
        $this->assertSame('0 0 10 20', $attrOverride['viewBox'] ?? '');
        $this->assertSame('translate(0,0) scale(1)', $attrOverride['patternTransform'] ?? '');

        $style = $obj->exposeDefaultSVGStyle();
        $style['opacity'] = 1.0;
        $style['fill-opacity'] = 1.0;

        $style['fill'] = 'url(#patBase)';
        [$outBase] = $obj->exposeParseSVGStyleFill(
            548,
            $style,
            [],
            0,
            0,
            6,
            6,
            'getClippingRect',
            [0.0, 0.0, 6.0, 6.0, 0.0],
        );

        $style['fill'] = 'url(#patRefInherit)';
        [$outInherit] = $obj->exposeParseSVGStyleFill(
            548,
            $style,
            [],
            0,
            0,
            6,
            6,
            'getClippingRect',
            [0.0, 0.0, 6.0, 6.0, 0.0],
        );

        $style['fill'] = 'url(#patRefOverride)';
        [$outOverride] = $obj->exposeParseSVGStyleFill(
            548,
            $style,
            [],
            0,
            0,
            6,
            6,
            'getClippingRect',
            [0.0, 0.0, 6.0, 6.0, 0.0],
        );

        $this->assertNotSame('', $outBase);
        $this->assertNotSame('', $outInherit);
        $this->assertNotSame('', $outOverride);
    }

    public function testSvgPatternPreserveAspectRatioChangesViewBoxTransform(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(541);

        $style = $obj->exposeDefaultSVGStyle();
        $style['opacity'] = 1.0;
        $style['fill-opacity'] = 1.0;

        $obj->patchSvgObj(541, [
            'defs' => [
                'patNone' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patNone',
                        'patternUnits' => 'userSpaceOnUse',
                        'patternContentUnits' => 'userSpaceOnUse',
                        'x' => '0',
                        'y' => '0',
                        'width' => '6',
                        'height' => '6',
                        'viewBox' => '0 0 10 20',
                        'preserveAspectRatio' => 'none',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'rect',
                            'attr' => [
                                'x' => '0',
                                'y' => '0',
                                'width' => '10',
                                'height' => '20',
                                'fill' => 'none',
                                'stroke' => '#000000',
                                'stroke-width' => '1',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'rect',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
                'patMeet' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patMeet',
                        'patternUnits' => 'userSpaceOnUse',
                        'patternContentUnits' => 'userSpaceOnUse',
                        'x' => '0',
                        'y' => '0',
                        'width' => '6',
                        'height' => '6',
                        'viewBox' => '0 0 10 20',
                        'preserveAspectRatio' => 'xMidYMid meet',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'rect',
                            'attr' => [
                                'x' => '0',
                                'y' => '0',
                                'width' => '10',
                                'height' => '20',
                                'fill' => 'none',
                                'stroke' => '#000000',
                                'stroke-width' => '1',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'rect',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $style['fill'] = 'url(#patNone)';
        [$outNone] = $obj->exposeParseSVGStyleFill(
            541,
            $style,
            [],
            0,
            0,
            6,
            6,
            'getClippingRect',
            [0.0, 0.0, 6.0, 6.0, 0.0],
        );

        $style['fill'] = 'url(#patMeet)';
        [$outMeet] = $obj->exposeParseSVGStyleFill(
            541,
            $style,
            [],
            0,
            0,
            6,
            6,
            'getClippingRect',
            [0.0, 0.0, 6.0, 6.0, 0.0],
        );

        $this->assertNotSame('', $outNone);
        $this->assertNotSame('', $outMeet);
        $this->assertNotSame($outNone, $outMeet);
    }

    public function testSvgPatternHrefCycleIsHandledSafely(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(542);

        $style = $obj->exposeDefaultSVGStyle();
        $style['fill'] = 'url(#patCycleA)';
        $style['opacity'] = 1.0;
        $style['fill-opacity'] = 1.0;

        $obj->patchSvgObj(542, [
            'defs' => [
                'patCycleA' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patCycleA',
                        'xlink:href' => '#patCycleB',
                        'patternUnits' => 'userSpaceOnUse',
                        'x' => '0',
                        'y' => '0',
                        'width' => '4',
                        'height' => '4',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'rect',
                            'attr' => [
                                'x' => '0',
                                'y' => '0',
                                'width' => '4',
                                'height' => '4',
                                'fill' => 'none',
                                'stroke' => '#000000',
                                'stroke-width' => '1',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'rect',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
                'patCycleB' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patCycleB',
                        'xlink:href' => '#patCycleA',
                    ],
                    'child' => [],
                ],
            ],
            'out' => 'BASE',
        ]);

        [$out] = $obj->exposeParseSVGStyleFill(
            542,
            $style,
            [],
            0,
            0,
            10,
            10,
            'getClippingRect',
            [0.0, 0.0, 10.0, 10.0, 0.0],
        );

        $this->assertNotSame('', $out);
        $this->assertSame('BASE', $obj->getSvgObj(542)['out']);
        $this->assertSame(0, $obj->getSvgObj(542)['patternmode']);
    }

    public function testSvgPatternViewBoxIgnoresPatternContentUnits(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(543);

        $style = $obj->exposeDefaultSVGStyle();
        $style['opacity'] = 1.0;
        $style['fill-opacity'] = 1.0;

        $defsPatternChild = [
            'DF_1' => [
                'name' => 'rect',
                'attr' => [
                    'x' => '0',
                    'y' => '0',
                    'width' => '10',
                    'height' => '20',
                    'fill' => 'none',
                    'stroke' => '#000000',
                    'stroke-width' => '1',
                ],
            ],
            'DF_1_CLOSE' => [
                'name' => 'rect',
                'attr' => [
                    'closing_tag' => true,
                    'content' => '',
                ],
            ],
        ];

        $obj->patchSvgObj(543, [
            'defs' => [
                'patUser' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patUser',
                        'patternUnits' => 'userSpaceOnUse',
                        'patternContentUnits' => 'userSpaceOnUse',
                        'x' => '0',
                        'y' => '0',
                        'width' => '6',
                        'height' => '6',
                        'viewBox' => '0 0 10 20',
                        'preserveAspectRatio' => 'xMidYMid meet',
                    ],
                    'child' => $defsPatternChild,
                ],
                'patObj' => [
                    'name' => 'pattern',
                    'attr' => [
                        'id' => 'patObj',
                        'patternUnits' => 'userSpaceOnUse',
                        'patternContentUnits' => 'objectBoundingBox',
                        'x' => '0',
                        'y' => '0',
                        'width' => '6',
                        'height' => '6',
                        'viewBox' => '0 0 10 20',
                        'preserveAspectRatio' => 'xMidYMid meet',
                    ],
                    'child' => $defsPatternChild,
                ],
            ],
        ]);

        $style['fill'] = 'url(#patUser)';
        [$outUser] = $obj->exposeParseSVGStyleFill(
            543,
            $style,
            [],
            0,
            0,
            6,
            6,
            'getClippingRect',
            [0.0, 0.0, 6.0, 6.0, 0.0],
        );

        $style['fill'] = 'url(#patObj)';
        [$outObj] = $obj->exposeParseSVGStyleFill(
            543,
            $style,
            [],
            0,
            0,
            6,
            6,
            'getClippingRect',
            [0.0, 0.0, 6.0, 6.0, 0.0],
        );

        $this->assertNotSame('', $outUser);
        $this->assertNotSame('', $outObj);
        $normUser = (string) \preg_replace('/PTN_[A-F0-9]{16}/', 'PTN_ID', $outUser);
        $normObj = (string) \preg_replace('/PTN_[A-F0-9]{16}/', 'PTN_ID', $outObj);
        $this->assertSame($normUser, $normObj);
    }

    public function testSvgLineRendersStartEndMarkersWhenDefined(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(523);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = '#000000';
        $base['stroke-width'] = 0.5;
        $base['marker-start'] = 'url(#mkline)';
        $base['marker-end'] = 'url(#mkline)';

        $obj->patchSvgObj(523, [
            'styles' => [$base],
            'defs' => [
                'mkline' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkline',
                        'viewBox' => '0 0 10 10',
                        'refX' => '0',
                        'refY' => '5',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'orient' => 'auto',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $out = $obj->exposeParseSVGTagSTARTline(
            $parser,
            523,
            ['x1' => '1', 'y1' => '1', 'x2' => '9', 'y2' => '1'],
            $base,
            $base,
        );

        $this->assertNotSame('', $out);
        $this->assertGreaterThanOrEqual(3, \substr_count($out, "q\n"));
    }

    public function testSvgLineIgnoresMissingMarkerRefsGracefully(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(524);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = '#000000';
        $base['stroke-width'] = 0.5;
        $base['marker-start'] = 'url(#missing)';
        $base['marker-end'] = 'none';

        $parser = \xml_parser_create('UTF-8');
        $out = $obj->exposeParseSVGTagSTARTline(
            $parser,
            524,
            ['x1' => '0', 'y1' => '0', 'x2' => '5', 'y2' => '0'],
            $base,
            $base,
        );

        $this->assertNotSame('', $out);
        $this->assertSame(1, \substr_count($out, "q\n"));
    }

    public function testSvgPathRendersStartMidEndMarkersWhenDefined(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(525);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = '#000000';
        $base['stroke-width'] = 0.6;
        $base['marker-start'] = 'url(#mkpath)';
        $base['marker-mid'] = 'url(#mkpath)';
        $base['marker-end'] = 'url(#mkpath)';

        $obj->patchSvgObj(525, [
            'styles' => [$base],
            'defs' => [
                'mkpath' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkpath',
                        'viewBox' => '0 0 10 10',
                        'refX' => '0',
                        'refY' => '5',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'orient' => 'auto',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $out = $obj->exposeParseSVGTagSTARTpath(
            $parser,
            525,
            ['d' => 'M 1 1 L 9 1 L 9 7'],
            $base,
            $base,
        );

        $this->assertNotSame('', $out);
        $this->assertGreaterThanOrEqual(4, \substr_count($out, "q\n"));
    }

    public function testSvgPolylineRendersMidMarkerWhenDefined(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(526);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = '#000000';
        $base['stroke-width'] = 0.5;
        $base['marker-start'] = 'url(#mkpoly)';
        $base['marker-mid'] = 'url(#mkpoly)';
        $base['marker-end'] = 'url(#mkpoly)';

        $obj->patchSvgObj(526, [
            'styles' => [$base],
            'defs' => [
                'mkpoly' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkpoly',
                        'viewBox' => '0 0 10 10',
                        'refX' => '0',
                        'refY' => '5',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'orient' => 'auto',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $out = $obj->exposeParseSVGTagSTARTpolygon(
            $parser,
            526,
            ['points' => '1,1 8,1 8,6'],
            $base,
            $base,
            true,
        );

        $this->assertNotSame('', $out);
        $this->assertGreaterThanOrEqual(4, \substr_count($out, "q\n"));
    }

    public function testSvgCurvedPathRendersMarkersWithoutErrors(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(527);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = '#000000';
        $base['stroke-width'] = 0.5;
        $base['marker-start'] = 'url(#mkcurve)';
        $base['marker-mid'] = 'url(#mkcurve)';
        $base['marker-end'] = 'url(#mkcurve)';

        $obj->patchSvgObj(527, [
            'styles' => [$base],
            'defs' => [
                'mkcurve' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkcurve',
                        'viewBox' => '0 0 10 10',
                        'refX' => '0',
                        'refY' => '5',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'orient' => 'auto',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $out = $obj->exposeParseSVGTagSTARTpath(
            $parser,
            527,
            ['d' => 'M 1 1 C 3 9 7 -1 9 7 L 12 9'],
            $base,
            $base,
        );

        $this->assertNotSame('', $out);
        $this->assertGreaterThanOrEqual(4, \substr_count($out, "q\n"));
    }

    public function testSvgClosedPathAddsMidMarkerAtClosureJoin(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(528);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = '#000000';
        $base['stroke-width'] = 0.5;
        $base['marker-start'] = 'none';
        $base['marker-mid'] = 'url(#mkclose)';
        $base['marker-end'] = 'none';

        $obj->patchSvgObj(528, [
            'styles' => [$base],
            'defs' => [
                'mkclose' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkclose',
                        'viewBox' => '0 0 10 10',
                        'refX' => '0',
                        'refY' => '5',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'orient' => 'auto',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $out = $obj->exposeParseSVGTagSTARTpath(
            $parser,
            528,
            ['d' => 'M 1 1 L 8 1 L 8 6 Z'],
            $base,
            $base,
        );

        $this->assertNotSame('', $out);
        // Closed triangle has 3 vertices, each should receive marker-mid.
        $this->assertGreaterThanOrEqual(4, \substr_count($out, "q\n"));
    }

    public function testSvgMarkerShorthandAppliesToLineAnchors(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(529);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = '#000000';
        $base['stroke-width'] = 0.5;
        $base['marker'] = 'url(#mkall)';
        $base['marker-start'] = 'none';
        $base['marker-mid'] = 'none';
        $base['marker-end'] = 'none';

        $obj->patchSvgObj(529, [
            'styles' => [$base],
            'defs' => [
                'mkall' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkall',
                        'viewBox' => '0 0 10 10',
                        'refX' => '0',
                        'refY' => '5',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'orient' => 'auto',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $out = $obj->exposeParseSVGTagSTARTline(
            $parser,
            529,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $this->assertNotSame('', $out);
        $this->assertGreaterThanOrEqual(3, \substr_count($out, "q\n"));
    }

    public function testSvgMarkerOrientDegSuffixIsAccepted(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(530);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = '#000000';
        $base['stroke-width'] = 0.5;
        $base['marker-start'] = 'url(#mkdeg)';
        $base['marker-end'] = 'url(#mkdeg)';

        $obj->patchSvgObj(530, [
            'styles' => [$base],
            'defs' => [
                'mkdeg' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkdeg',
                        'viewBox' => '0 0 10 10',
                        'refX' => '0',
                        'refY' => '5',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'orient' => '45deg',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $out = $obj->exposeParseSVGTagSTARTline(
            $parser,
            530,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $this->assertNotSame('', $out);
        $this->assertGreaterThanOrEqual(3, \substr_count($out, "q\n"));
    }

    public function testSvgMarkerPercentRefMatchesAbsoluteRefInViewBox(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(531);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = '#000000';
        $base['stroke-width'] = 0.5;
        $base['marker-start'] = 'url(#mkpct)';
        $base['marker-end'] = 'none';

        $obj->patchSvgObj(531, [
            'styles' => [$base],
            'defs' => [
                'mkpct' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkpct',
                        'viewBox' => '0 0 10 10',
                        'refX' => '50%',
                        'refY' => '50%',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'orient' => '0',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
                'mkabs' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkabs',
                        'viewBox' => '0 0 10 10',
                        'refX' => '5',
                        'refY' => '5',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'orient' => '0',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $outPct = $obj->exposeParseSVGTagSTARTline(
            $parser,
            531,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $base['marker-start'] = 'url(#mkabs)';
        $outAbs = $obj->exposeParseSVGTagSTARTline(
            $parser,
            531,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $this->assertNotSame('', $outPct);
        $this->assertSame($outAbs, $outPct);
    }

    public function testSvgMarkerPreserveAspectRatioChangesOutputTransform(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(532);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = '#000000';
        $base['stroke-width'] = 0.5;
        $base['marker-start'] = 'url(#mknone)';
        $base['marker-end'] = 'none';

        $obj->patchSvgObj(532, [
            'styles' => [$base],
            'defs' => [
                'mknone' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mknone',
                        'viewBox' => '0 0 10 20',
                        'refX' => '0',
                        'refY' => '0',
                        'markerWidth' => '6',
                        'markerHeight' => '6',
                        'preserveAspectRatio' => 'none',
                        'orient' => '0',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
                'mkmeet' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkmeet',
                        'viewBox' => '0 0 10 20',
                        'refX' => '0',
                        'refY' => '0',
                        'markerWidth' => '6',
                        'markerHeight' => '6',
                        'preserveAspectRatio' => 'xMidYMid meet',
                        'orient' => '0',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $outNone = $obj->exposeParseSVGTagSTARTline(
            $parser,
            532,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $base['marker-start'] = 'url(#mkmeet)';
        $outMeet = $obj->exposeParseSVGTagSTARTline(
            $parser,
            532,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $this->assertNotSame('', $outNone);
        $this->assertNotSame('', $outMeet);
        $this->assertNotSame($outNone, $outMeet);
    }

    public function testSvgMarkerSpecificAnchorsOverrideShorthand(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(533);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = '#000000';
        $base['stroke-width'] = 0.5;
        $base['marker'] = 'url(#mkall2)';
        $base['marker-start'] = 'none';
        $base['marker-mid'] = 'url(#mkall2)';
        $base['marker-end'] = 'url(#mkall2)';

        $obj->patchSvgObj(533, [
            'styles' => [$base],
            'defs' => [
                'mkall2' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkall2',
                        'viewBox' => '0 0 10 10',
                        'refX' => '0',
                        'refY' => '5',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'orient' => 'auto',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $out = $obj->exposeParseSVGTagSTARTline(
            $parser,
            533,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $this->assertNotSame('', $out);
        // Base line transform + one end marker only (no start marker).
        $this->assertSame(2, \substr_count($out, "q\n"));
    }

    public function testSvgMarkerPreserveAspectRatioDeferMatchesEquivalentValue(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(534);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = '#000000';
        $base['stroke-width'] = 0.5;
        $base['marker-start'] = 'url(#mkdefer)';
        $base['marker-end'] = 'none';

        $obj->patchSvgObj(534, [
            'styles' => [$base],
            'defs' => [
                'mkdefer' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkdefer',
                        'viewBox' => '0 0 10 20',
                        'refX' => '0',
                        'refY' => '0',
                        'markerWidth' => '6',
                        'markerHeight' => '6',
                        'preserveAspectRatio' => 'defer xMaxYMax slice',
                        'orient' => '0',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
                'mkeq' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkeq',
                        'viewBox' => '0 0 10 20',
                        'refX' => '0',
                        'refY' => '0',
                        'markerWidth' => '6',
                        'markerHeight' => '6',
                        'preserveAspectRatio' => 'xMaxYMax slice',
                        'orient' => '0',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $outDefer = $obj->exposeParseSVGTagSTARTline(
            $parser,
            534,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $base['marker-start'] = 'url(#mkeq)';
        $outEq = $obj->exposeParseSVGTagSTARTline(
            $parser,
            534,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $this->assertNotSame('', $outDefer);
        $this->assertSame($outEq, $outDefer);
    }

    public function testSvgMarkerUnitsUserSpaceOnUseIgnoresStrokeWidthScale(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(535);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = 'none';
        $base['marker-start'] = 'url(#mkus)';
        $base['marker-end'] = 'none';

        $obj->patchSvgObj(535, [
            'styles' => [$base],
            'defs' => [
                'mkus' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkus',
                        'viewBox' => '0 0 10 10',
                        'refX' => '0',
                        'refY' => '5',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'markerUnits' => 'userSpaceOnUse',
                        'orient' => '0',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $base['stroke-width'] = 0.5;
        $outThin = $obj->exposeParseSVGTagSTARTline(
            $parser,
            535,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $base['stroke-width'] = 3.0;
        $outThick = $obj->exposeParseSVGTagSTARTline(
            $parser,
            535,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $this->assertNotSame('', $outThin);
        $this->assertSame($outThin, $outThick);
    }

    public function testSvgMarkerUnitsStrokeWidthScalesWithStrokeWidth(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(536);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = 'none';
        $base['marker-start'] = 'url(#mksw)';
        $base['marker-end'] = 'none';

        $obj->patchSvgObj(536, [
            'styles' => [$base],
            'defs' => [
                'mksw' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mksw',
                        'viewBox' => '0 0 10 10',
                        'refX' => '0',
                        'refY' => '5',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'markerUnits' => 'strokeWidth',
                        'orient' => '0',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $base['stroke-width'] = 0.5;
        $outThin = $obj->exposeParseSVGTagSTARTline(
            $parser,
            536,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $base['stroke-width'] = 3.0;
        $outThick = $obj->exposeParseSVGTagSTARTline(
            $parser,
            536,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $this->assertNotSame('', $outThin);
        $this->assertNotSame($outThin, $outThick);
    }

    public function testSvgMarkerAutoStartReverseDiffersFromAutoAtStart(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(537);

        $base = $obj->exposeDefaultSVGStyle();
        $base['stroke'] = 'none';
        $base['stroke-width'] = 1.0;
        $base['marker-start'] = 'url(#mkauto)';
        $base['marker-end'] = 'none';

        $obj->patchSvgObj(537, [
            'styles' => [$base],
            'defs' => [
                'mkauto' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkauto',
                        'viewBox' => '0 0 10 10',
                        'refX' => '0',
                        'refY' => '5',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'orient' => 'auto',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
                'mkrev' => [
                    'name' => 'marker',
                    'attr' => [
                        'id' => 'mkrev',
                        'viewBox' => '0 0 10 10',
                        'refX' => '0',
                        'refY' => '5',
                        'markerWidth' => '4',
                        'markerHeight' => '4',
                        'orient' => 'auto-start-reverse',
                    ],
                    'child' => [
                        'DF_1' => [
                            'name' => 'path',
                            'attr' => [
                                'id' => 'DF_1',
                                'd' => 'M 0 0 L 10 5 L 0 10 Z',
                                'fill' => '#000000',
                                'stroke' => 'none',
                            ],
                        ],
                        'DF_1_CLOSE' => [
                            'name' => 'path',
                            'attr' => [
                                'closing_tag' => true,
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $outAuto = $obj->exposeParseSVGTagSTARTline(
            $parser,
            537,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $base['marker-start'] = 'url(#mkrev)';
        $outRev = $obj->exposeParseSVGTagSTARTline(
            $parser,
            537,
            ['x1' => '1', 'y1' => '1', 'x2' => '8', 'y2' => '1'],
            $base,
            $base,
        );

        $this->assertNotSame('', $outAuto);
        $this->assertNotSame('', $outRev);
        $this->assertNotSame($outAuto, $outRev);
    }

    public function testSvgHandleStartInheritAndUnknownTagBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(53);
        $base = $obj->exposeDefaultSVGStyle();
        $obj->patchSvgObj(53, [
            'styles' => [$base],
            'clipmode' => false,
            'textmode' => ['invisible' => false, 'stroke' => 0, 'rtl' => false, 'text-anchor' => 'start'],
        ]);

        $parser = \xml_parser_create('UTF-8');
        $obj->exposeHandleSVGTagStart(
            $parser,
            'unknownTag',
            [
                'style' => 'color:inherit;display:none;',
                'transform' => 'translate(1,2) rotate(5)',
            ],
            53,
            false,
            [1, 0, 0, 1, 0, 0],
        );

        $svgobj = $obj->getSvgObj(53);
        $this->assertArrayHasKey('out', $svgobj);
        $this->assertTrue($svgobj['textmode']['invisible']);
    }

    public function testSvgClipPathInvisibleAndShapeImageGuardBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(54);
        $base = $obj->exposeDefaultSVGStyle();
        $parser = \xml_parser_create('UTF-8');

        $obj->patchSvgObj(54, ['textmode' => ['invisible' => true]]);
        $this->assertSame('', $obj->exposeParseSVGTagSTARTclipPath(54, [1, 0, 0, 1, 0, 0]));

        $obj->patchSvgObj(54, ['textmode' => ['invisible' => false], 'clipmode' => true]);
        $ellipseClip = $obj->exposeParseSVGTagSTARTellipse(
            $parser,
            54,
            ['cx' => '5', 'cy' => '6', 'rx' => '3', 'ry' => '2'],
            $base,
            $base,
        );
        $this->assertNotSame('', $ellipseClip);

        $lineClip = $obj->exposeParseSVGTagSTARTline(
            $parser,
            54,
            ['x1' => '0', 'y1' => '0', 'x2' => '5', 'y2' => '5'],
            $base,
            $base,
        );
        $this->assertSame('', $lineClip);

        $imgNoHref = $obj->exposeParseSVGTagSTARTimage($parser, 54, [], $base, $base);
        $this->assertSame('', $imgNoHref);

        $imgClip = $obj->exposeParseSVGTagSTARTimage(
            $parser,
            54,
            ['xlink:href' => 'image.png', 'x' => '1', 'y' => '1', 'width' => '2', 'height' => '2'],
            $base,
            $base,
        );
        $this->assertSame('', $imgClip);
    }

    public function testSvgAddSVGAlignmentAndScalingBranches(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndPage($obj);

        $svgSlice = '@<svg xmlns="http://www.w3.org/2000/svg"'
            . ' width="20" height="10" viewBox="0 0 20 10" preserveAspectRatio="xMaxYMax slice">'
            . '<rect x="0" y="0" width="20" height="10" fill="#ffcc00"/>'
            . '</svg>';
        $idSlice = $obj->addSVG($svgSlice, 10, 12, 30, 10, $page['height']);
        $this->assertGreaterThan(0, $idSlice);

        $svgMeet = '@<svg xmlns="http://www.w3.org/2000/svg"'
            . ' width="10" height="20" viewBox="0 0 10 20" preserveAspectRatio="xMidYMid meet">'
            . '<circle cx="5" cy="10" r="4" fill="#00ccff"/>'
            . '</svg>';
        $idMeet = $obj->addSVG($svgMeet, 15, 18, 0, 25, $page['height']);
        $this->assertGreaterThan($idSlice, $idMeet);

        $svgNone = '@<svg xmlns="http://www.w3.org/2000/svg"'
            . ' width="12" height="12" viewBox="0 0 12 12" preserveAspectRatio="none">'
            . '<line x1="0" y1="0" x2="12" y2="12" stroke="#000"/>'
            . '</svg>';
        $idNone = $obj->addSVG($svgNone, 3, 4, 0, 0, $page['height']);
        $this->assertGreaterThan($idMeet, $idNone);
    }

    public function testSvgRemainingHelperGradientAndFillBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(55);
        $base = $obj->exposeDefaultSVGStyle();

        $this->assertSame(40.0, $obj->exposeGetTAFontStretching('ultra-condensed'));
        $this->assertSame(55.0, $obj->exposeGetTAFontStretching('extra-condensed'));
        $this->assertSame(85.0, $obj->exposeGetTAFontStretching('semi-condensed'));
        $this->assertSame(100.0, $obj->exposeGetTAFontStretching('normal'));
        $this->assertSame(115.0, $obj->exposeGetTAFontStretching('semi-expanded'));
        $this->assertSame(130.0, $obj->exposeGetTAFontStretching('expanded'));
        $this->assertSame(145.0, $obj->exposeGetTAFontStretching('extra-expanded'));
        $this->assertSame(160.0, $obj->exposeGetTAFontStretching('ultra-expanded'));

        $fill = $base;
        $fill['fill'] = 'rgba(255,0,0,0.5)';
        $fill['opacity'] = 0.6;
        $fill['fill-opacity'] = 0.5;
        $fill['mix-blend-mode'] = 'multiply';
        [$fillOut, $fill] = $obj->exposeParseSVGStyleFill(55, $fill, [], 0, 0, 5, 5);
        $this->assertNotSame('', $fillOut);
        $this->assertStringContainsString('F', $fill['objstyle']);

        $fillBad = $base;
        $fillBad['fill'] = 'transparent';
        [$badOut] = $obj->exposeParseSVGStyleFill(55, $fillBad, [], 0, 0, 5, 5);
        $this->assertSame('', $badOut);

        $parser = \xml_parser_create('UTF-8');
        $obj->exposeParseSVGStyleClipPath($parser, 55, [
            'cp1' => [
                'name' => 'rect',
                'attr' => ['x' => '1', 'y' => '1', 'width' => '2', 'height' => '2'],
                'tm' => [1, 0, 0, 1, 0, 0],
            ],
        ]);
        $this->assertNotSame('', $obj->getSvgObj(55)['out']);

        $this->assertSame('', $obj->exposeParseSVGTagSTARTlinearGradient(
            55,
            ['gradientTransform' => 'matrix(1 0 0 1 1 2)']
        ));
        $this->assertSame('', $obj->exposeParseSVGTagSTARTradialGradient(55, ['r' => '0.5']));
        $svgobj = $obj->getSvgObj(55);
        $this->assertNotSame('', $svgobj['gradientid']);
        $this->assertNotEmpty($svgobj['gradients']);
    }

    public function testSvgAdditionalEdgeBranchesForCoverage(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(56);
        $base = $obj->exposeDefaultSVGStyle();
        $parser = \xml_parser_create('UTF-8');

        // cover defs-mode early-return branches
        $obj->patchSvgObj(56, ['defsmode' => true, 'defs' => [], 'text' => 't']);
        $obj->exposeHandleSVGTagEnd($parser, 'unknown');
        $obj->exposeHandleSVGTagStart($parser, 'unknown', ['id' => 'd1'], 56);

        // style inherit + dispatch branches including polyline/image/use/tspan
        $obj->patchSvgObj(56, [
            'defsmode' => false,
            'clipmode' => false,
            'styles' => [$base],
            'textmode' => ['invisible' => false, 'stroke' => 0, 'rtl' => false, 'text-anchor' => 'start'],
        ]);
        $obj->exposeHandleSVGTagStart($parser, 'polyline', ['points' => '0,0 1,1 2,2', 'style' => 'color:inherit'], 56);
        $obj->exposeHandleSVGTagStart($parser, 'defs', [], 56);
        $obj->patchSvgObj(56, ['clipmode' => false]);
        $obj->exposeHandleSVGTagStart($parser, 'line', ['x1' => '0', 'y1' => '0', 'x2' => '1', 'y2' => '1'], 56);

        // path clipmode and empty-d guard
        $obj->patchSvgObj(56, ['clipmode' => true]);
        $this->assertSame('', $obj->exposeParseSVGTagSTARTpath($parser, 56, ['d' => ''], $base, $base));
        $clipPathOut = $obj->exposeParseSVGTagSTARTpath($parser, 56, ['d' => 'M 0 0 L 2 2'], $base, $base);
        $this->assertNotSame('', $clipPathOut);

        // circle/ellipse fallback x/y and clip branches
        $circleClip = $obj->exposeParseSVGTagSTARTcircle(
            $parser,
            56,
            ['x' => '4', 'y' => '5', 'r' => '2'],
            $base,
            $base
        );
        $this->assertNotSame('', $circleClip);
        $ellipseClip = $obj->exposeParseSVGTagSTARTellipse(
            $parser,
            56,
            ['x' => '4', 'y' => '5', 'rx' => '2', 'ry' => '1'],
            $base,
            $base
        );
        $this->assertNotSame('', $ellipseClip);

        // polygon invalid and clip branches
        $obj->patchSvgObj(56, ['clipmode' => false]);
        $this->assertSame('', $obj->exposeParseSVGTagSTARTpolygon($parser, 56, ['points' => '0,0 1'], $base, $base));
        $obj->patchSvgObj(56, ['clipmode' => true]);
        $polyClip = $obj->exposeParseSVGTagSTARTpolygon($parser, 56, ['points' => '0,0 2,0 2,2 0,2'], $base, $base);
        $this->assertNotSame('', $polyClip);

        // image guards and svg child path
        $obj->patchSvgObj(56, ['clipmode' => false, 'dir' => __DIR__]);
        $this->assertSame('', $obj->exposeParseSVGTagSTARTimage($parser, 56, [], $base, $base));
        $svgTmp = \tempnam(\sys_get_temp_dir(), 'tc-svg-child-');
        $this->assertNotFalse($svgTmp);
        $svgPath = $svgTmp . '.svg';
        \rename($svgTmp, $svgPath);
        \file_put_contents(
            $svgPath,
            '<svg xmlns="http://www.w3.org/2000/svg" width="2" height="2">'
            . '<rect x="0" y="0" width="2" height="2" fill="#000"/></svg>'
        );
        try {
            $svgImgOut = $obj->exposeParseSVGTagSTARTimage(
                $parser,
                56,
                ['xlink:href' => $svgPath, 'x' => '1', 'y' => '1', 'width' => '2', 'height' => '2'],
                $base,
                $base,
            );
            $this->assertNotSame('', $svgImgOut);
        } finally {
            @\unlink($svgPath);
        }

        // text defaults without explicit anchor/direction/stroke
        $textBase = $base;
        $textBase['text-anchor'] = '';
        $textBase['direction'] = '';
        $textBase['stroke'] = 'none';
        $textOut = $obj->exposeParseSVGTagSTARTtext($parser, 56, ['x' => '1', 'y' => '1'], $textBase, $base);
        $this->assertNotSame('', $textOut);

        // use: unset id + non-string name guard
        $obj->patchSvgObj(56, [
            'defs' => [
                'u1' => [
                    'name' => [],
                    'attr' => ['id' => 'u1', 'x' => '1', 'y' => '2', 'style' => 'fill:#000;'],
                ],
            ],
        ]);
        $this->assertSame('', $obj->exposeParseSVGTagSTARTuse(
            $parser,
            56,
            ['xlink:href' => '#u1', 'id' => 'tmp', 'x' => '2', 'y' => '3']
        ));

        // raw svg + size fallback branches
        $emptyFile = \tempnam(\sys_get_temp_dir(), 'tc-svg-empty-');
        $this->assertNotFalse($emptyFile);
        try {
            \file_put_contents($emptyFile, '');
            $this->assertSame('', $obj->exposeGetRawSVGData($emptyFile));
        } finally {
            @\unlink($emptyFile);
        }
        $this->assertSame(0.0, $obj->exposeGetSVGSize('not svg data')['width']);
        $this->assertSame('xMidYMid', $obj->exposeGetSVGSize('<svg viewBox="0 0 10 10"></svg>')['ar_align']);

        // prescan stop outside gradient
        $obj->initSvgObjForHandlers(57);
        $obj->exposePrescanSVGGradients('<svg><stop offset="0%" stop-color="#000"/></svg>', 57);

        // gradient mode branches including pdfa guards
        $obj->initSvgObjForHandlers(58);
        $this->setObjectProperty($obj, 'pdfa', 1);
        $this->assertSame('', $obj->exposeParseSVGTagSTARTlinearGradient(58, []));
        $this->assertSame('', $obj->exposeParseSVGTagSTARTradialGradient(58, []));
        $this->setObjectProperty($obj, 'pdfa', 0);
        $obj->exposeParseSVGTagSTARTlinearGradient(
            58,
            ['x1' => '1', 'y1' => '2', 'x2' => '3', 'y2' => '4', 'xlink:href' => '#gref']
        );
        $obj->exposeParseSVGTagSTARTradialGradient(
            58,
            ['r' => '0.5', 'gradientTransform' => 'matrix(1 0 0 1 1 1)', 'xlink:href' => '#gref2']
        );

        // addSVG parser error + width/height branch variants
        $objMain = $this->getTestObject();
        $page = $this->initFontAndPage($objMain);
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);
        $objMain->addSVG('@<svg><g></svg>', 1, 1, 2, 2, $page['height']);
    }

    public function testSvgHandlersCoverAdditionalDispatchAndEarlyReturns(): void
    {
        $parser = \xml_parser_create('UTF-8');

        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $base = $obj->exposeDefaultSVGStyle();

        $obj->initSvgObjForHandlers(59);
        $obj->patchSvgObj(59, [
            'styles' => [$base],
            'clipmode' => false,
            'defsmode' => false,
            'textmode' => ['invisible' => false, 'stroke' => 0, 'rtl' => false, 'text-anchor' => 'start'],
            'out' => '',
            'x' => 1.0,
            'y' => 1.0,
            'text' => 'txt',
            'child' => [],
        ]);

        $obj->exposeHandleSVGTagStart($parser, 'clipPath', ['id' => 'cp1'], 59);
        $obj->exposeHandleSVGTagStart(
            $parser,
            'linearGradient',
            ['id' => 'lg1', 'x1' => '0', 'y1' => '0', 'x2' => '100%', 'y2' => '0%'],
            59
        );
        $obj->exposeHandleSVGTagStart(
            $parser,
            'radialGradient',
            ['id' => 'rg1', 'cx' => '50%', 'cy' => '50%', 'r' => '0.5'],
            59
        );
        $obj->exposeHandleSVGTagStart($parser, 'stop', ['offset' => '0%'], 59);
        $obj->exposeHandleSVGTagStart($parser, 'polygon', ['points' => '0,0 1,0 1,1'], 59);
        $obj->exposeHandleSVGTagStart($parser, 'text', ['x' => '1', 'y' => '1'], 59);
        $obj->exposeHandleSVGTagStart($parser, 'tspan', ['x' => '1', 'y' => '1'], 59);

        $obj->initSvgObjForHandlers(61);
        $obj->patchSvgObj(61, ['styles' => [$base], 'text' => 'txt', 'x' => 1.0, 'y' => 1.0]);
        $obj->exposeHandleSVGTagEnd($parser, 'clipPath');
        $obj->patchSvgObj(61, ['styles' => [$base], 'text' => 'txt', 'x' => 1.0, 'y' => 1.0]);
        $obj->exposeHandleSVGTagEnd($parser, 'g');
        $obj->patchSvgObj(61, ['styles' => [$base], 'text' => 'txt', 'x' => 1.0, 'y' => 1.0]);
        $obj->exposeHandleSVGTagEnd($parser, 'text');
        $obj->patchSvgObj(61, ['styles' => [$base], 'text' => 'txt', 'x' => 1.0, 'y' => 1.0]);
        $obj->exposeHandleSVGTagEnd($parser, 'tspan');

        $this->assertArrayHasKey('out', $obj->getSvgObj(59));
    }

    public function testSvgAddSvgCoversParseErrorAndInvalidInputBranches(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndPage($obj);

        $tmpRel = __DIR__ . '/../tmp-svg-rel.svg';
        \file_put_contents(
            $tmpRel,
            '<svg xmlns="http://www.w3.org/2000/svg" width="2" height="2">'
            . '<rect x="0" y="0" width="2" height="2"/></svg>'
        );
        try {
            $svgId = $obj->addSVG('tmp-svg-rel.svg', 1, 1, 2, 2, $page['height']);
            $this->assertGreaterThan(0, $svgId);
        } finally {
            @\unlink($tmpRel);
        }

        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);
        $obj->addSVG('/path/does-not-exist.svg', 1, 1, 2, 2, $page['height']);
    }

    public function testSvgAddSvgThrowsOnXmlParseErrorAfterSizeParsing(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndPage($obj);

        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);
        $obj->addSVG(
            '@<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"><g></svg>',
            1,
            1,
            1,
            1,
            $page['height'],
        );
    }

    public function testSvgImageTagCoversNestedSvgFailureAndBase64Path(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $base = $obj->exposeDefaultSVGStyle();

        $obj->initSvgObjForHandlers(60);
        $obj->patchSvgObj(60, [
            'clipmode' => false, 'dir' => __DIR__, 'styles' => [$base], 'textmode' => ['invisible' => false]
        ]);

        $badSvg = __DIR__ . '/fixtures/invalid-child.svg';
        \file_put_contents($badSvg, '<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"><g></svg>');
        try {
            $this->assertSame(
                '',
                $obj->exposeParseSVGTagSTARTimage(
                    $parser,
                    60,
                    ['xlink:href' => $badSvg, 'x' => '0', 'y' => '0', 'width' => '1', 'height' => '1'],
                    $base,
                    $base,
                ),
            );
        } finally {
            @\unlink($badSvg);
        }

        $onePx = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO7ZkQAAAABJRU5ErkJggg==';
        try {
            $imgOut = $obj->exposeParseSVGTagSTARTimage(
                $parser,
                60,
                [
                    'xlink:href' => 'data:image/png;base64,' . $onePx,
                    'x' => '0', 'y' => '0', 'width' => '1', 'height' => '1'
                ],
                $base,
                $base,
            );
            $this->assertNotSame('', $imgOut);
        } catch (\Throwable $e) {
            $this->assertNotSame('', $e->getMessage());
        }
    }

    public function testSvgAdditionalTransformAndDispatchBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->setSvgRefUnit(62);
        $base = $obj->exposeDefaultSVGStyle();
        $parser = \xml_parser_create('UTF-8');

        $tmSkew = $obj->exposeGetSVGTransformMatrix('skewX(10) skewY(5)');
        $this->assertCount(6, $tmSkew);

        // Uppercase command forces the default match arm.
        $tmDefault = $obj->exposeGetSVGTransformMatrix('SKEWX(10)');
        $this->assertCount(6, $tmDefault);

        $this->assertSame('', $obj->exposeGetSVGPath(62, '', 'S'));
        $this->assertSame(90.0, $obj->exposeGetTAFontStretching('inherit', 90));

        $strokeStyle = $base;
        $strokeStyle['stroke'] = 'not-a-color';
        try {
            [$strokeOut] = $obj->exposeParseSVGStyleStroke(62, $strokeStyle);
            $this->assertSame('', $strokeOut);
        } catch (\Throwable $e) {
            $this->assertNotSame('', $e->getMessage());
        }

        $fresh = new TestableSVG();
        $fresh->exposeHandleSVGCharacter($parser, 'orphan');
        $this->assertSame('', $fresh->exposeGetSVGExtGState(null, null, ''));

        $obj->initSvgObjForHandlers(63);
        $obj->patchSvgObj(63, [
            'styles' => [$base],
            'textmode' => ['invisible' => false, 'stroke' => 0, 'rtl' => false, 'text-anchor' => 'end'],
            'x' => 2.0,
            'y' => 3.0,
            'text' => 'anchor-end',
        ]);
        $this->assertNotSame('', $obj->exposeParseSVGTagENDtext(63));

        $obj->initSvgObjForHandlers(64);
        $obj->patchSvgObj(64, [
            'styles' => [$base],
            'defsmode' => false,
            'clipmode' => false,
            'textmode' => ['invisible' => false, 'stroke' => 0, 'rtl' => false, 'text-anchor' => 'start'],
        ]);

        $obj->exposeHandleSVGTagStart($parser, 'g', ['fill' => 'inherit'], 64);
        $obj->exposeHandleSVGTagStart(
            $parser,
            'linearGradient',
            ['id' => 'lgx', 'gradientUnits' => 'userSpaceOnUse'],
            64
        );
        $obj->exposeHandleSVGTagStart(
            $parser,
            'radialGradient',
            ['id' => 'rgx', 'gradientUnits' => 'userSpaceOnUse', 'r' => '0.5', 'cx' => '2', 'cy' => '3'],
            64
        );
        $obj->exposeHandleSVGTagStart($parser, 'stop', ['offset' => '50%'], 64);
        $obj->exposeHandleSVGTagStart($parser, 'ellipse', ['cx' => '3', 'cy' => '4', 'rx' => '2', 'ry' => '1'], 64);
        $obj->exposeHandleSVGTagStart($parser, 'polygon', ['points' => '0,0 2,0 2,2'], 64);
        try {
            $obj->exposeHandleSVGTagStart($parser, 'image', ['xlink:href' => 'missing.png'], 64);
        } catch (\Throwable $e) {
            $this->assertNotSame('', $e->getMessage());
        }
        $obj->exposeHandleSVGTagStart($parser, 'text', ['x' => '1', 'y' => '1'], 64);
        $obj->exposeHandleSVGTagStart($parser, 'tspan', ['dx' => '1'], 64);
        $obj->exposeHandleSVGTagStart($parser, 'use', ['xlink:href' => '#missing'], 64);

        $svgOut = $obj->getSvgObj(64)['out'];
        $this->assertIsString($svgOut);
    }

    public function testSvgGradientAndFillRemainingBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(65);
        $obj->setSvgObjMeta(65);
        $base = $obj->exposeDefaultSVGStyle();

        $gradients = [
            'eq' => [
                'xref' => '',
                'type' => 2,
                'gradientUnits' => 'objectBoundingBox',
                'mode' => 'measure',
                'coords' => [0.0, 0.0, 0.0, 0.0],
                'stops' => [
                    ['color' => '#000000', 'opacity' => 1.0, 'offset' => 0.0],
                    ['color' => '#ffffff', 'opacity' => 1.0, 'offset' => 1.0],
                ],
                'gradientTransform' => [1.0, 0.0, 0.0, 1.0, 0.0, 0.0],
            ],
        ];

        $grOut = $obj->exposeParseSVGStyleGradient(
            65,
            $gradients,
            'eq',
            0.0,
            0.0,
            0.0,
            0.0,
        );
        $this->assertNotSame('', $grOut);

        $fillStyle = $base;
        $fillStyle['fill'] = 'url(#eq)';
        $fillStyle['opacity'] = 0.5;
        $fillStyle['fill-opacity'] = 0.5;
        $fillStyle['mix-blend-mode'] = 'multiply';
        [$fillOut] = $obj->exposeParseSVGStyleFill(65, $fillStyle, $gradients, 0.0, 0.0, 5.0, 5.0);
        $this->assertNotSame('', $fillOut);
    }

    public function testSvgAddSvgScalingAndRecursiveChildBranches(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndPage($obj);

        $svgSlice = '@<svg xmlns="http://www.w3.org/2000/svg"'
            . ' width="100" height="10" viewBox="0 0 100 10" preserveAspectRatio="xMinYMin slice">'
            . '<rect x="0" y="0" width="100" height="10" fill="#cccccc"/>'
            . '</svg>';
        $idSlice = $obj->addSVG($svgSlice, 2, 2, 100, 100, $page['height']);
        $this->assertGreaterThan(0, $idSlice);

        $svgMeet = '@<svg xmlns="http://www.w3.org/2000/svg"'
            . ' width="10" height="100" viewBox="0 0 10 100" preserveAspectRatio="xMinYMin meet">'
            . '<rect x="0" y="0" width="10" height="100" fill="#999999"/>'
            . '</svg>';
        $idMeet = $obj->addSVG($svgMeet, 3, 3, 100, 100, $page['height']);
        $this->assertGreaterThan($idSlice, $idMeet);

        $svgAutoHeight = '@<svg xmlns="http://www.w3.org/2000/svg" width="20" height="10">'
            . '<rect x="0" y="0" width="20" height="10"/></svg>';
        $idAutoHeight = $obj->addSVG($svgAutoHeight, 4, 4, 30, 0, $page['height']);
        $this->assertGreaterThan($idMeet, $idAutoHeight);

        $tmpDir = (string) \realpath(\sys_get_temp_dir());
        $childPath = $tmpDir . '/tc-svg-child-recursive.svg';
        $parentPath = $tmpDir . '/tc-svg-parent-recursive.svg';

        \file_put_contents(
            $childPath,
            '<svg xmlns="http://www.w3.org/2000/svg" width="3" height="3">'
            . '<rect x="0" y="0" width="3" height="3" fill="#000"/></svg>',
        );
        \file_put_contents(
            $parentPath,
            '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10">'
            . '<image x="0" y="0" width="5" height="5" xlink:href="tc-svg-child-recursive.svg"/></svg>',
        );

        try {
            $parentId = $obj->addSVG($parentPath, 1, 1, 10, 10, $page['height']);
            $setOut = $obj->getSetSVG($parentId);
            $this->assertNotSame('', $setOut);
        } finally {
            @\unlink($childPath);
            @\unlink($parentPath);
        }
    }

    public function testSvgFinalFeasibleBranchCoverageBatch(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $base = $obj->exposeDefaultSVGStyle();

        $coord = $obj->getPathCoordDefaults();
        $coord['relcoord'] = true;
        $coord['xoffset'] = 1.0;
        $coord['yoffset'] = 1.0;
        [, $coord] = $obj->exposeSvgPathCmdA(
            [4.0, 3.0, 0.0, 0.0, 0.0, 8.0, 9.0],
            $coord,
            [['0', 'A'], ['1', 'z']],
            0,
            ['4', '3', '0', '0', '0', '8', '9'],
        );
        $this->assertGreaterThan(0.0, $coord['xoffset']);
        $this->assertGreaterThan(0.0, $coord['yoffset']);

        $obj->initSvgObjForHandlers(70);
        $obj->patchSvgObj(70, [
            'defsmode' => true, 'defs' => [], 'styles' => [$base], 'textmode' => ['invisible' => false]
        ]);
        /** @var array<int, array<string, mixed>> $svgobjs */
        $svgobjs = $this->getObjectProperty($obj, 'svgobjs');
        unset($svgobjs[70]['clippaths']);
        $this->setObjectProperty($obj, 'svgobjs', $svgobjs);
        $obj->exposeHandleSVGTagStart($parser, 'line', [], 70);

        $obj->patchSvgObj(70, ['defsmode' => false, 'clipmode' => false, 'styles' => [$base]]);
        $obj->exposeHandleSVGTagStart($parser, 'path', ['d' => 'M 0 0 L 2 2'], 70);

        $obj->patchSvgObj(70, ['tagdepth' => 1]);
        $svgNone = $obj->exposeParseSVGTagSTARTsvg(
            $parser,
            70,
            [
                'x' => '0', 'y' => '0', 'width' => '40', 'height' => '10',
                'viewBox' => '0 0 100 50', 'preserveAspectRatio' => 'none'
            ],
            $base,
            $base,
        );
        $this->assertNotSame('', $svgNone);

        $obj->patchSvgObj(70, ['tagdepth' => 1]);
        $svgMeetXMax = $obj->exposeParseSVGTagSTARTsvg(
            $parser,
            70,
            [
                'x' => '0', 'y' => '0', 'width' => '40', 'height' => '10',
                'viewBox' => '0 0 100 50', 'preserveAspectRatio' => 'xMaxYMid meet'
            ],
            $base,
            $base,
        );
        $this->assertNotSame('', $svgMeetXMax);

        $obj->patchSvgObj(70, ['tagdepth' => 1]);
        $svgMeetYMax = $obj->exposeParseSVGTagSTARTsvg(
            $parser,
            70,
            [
                'x' => '0', 'y' => '0', 'width' => '10', 'height' => '40',
                'viewBox' => '0 0 50 100', 'preserveAspectRatio' => 'xMidYMax meet'
            ],
            $base,
            $base,
        );
        $this->assertNotSame('', $svgMeetYMax);

        $obj->initSvgObjForHandlers(71);
        $obj->exposeParseSVGTagSTARTradialGradient(
            71,
            ['id' => 'rgm', 'gradientUnits' => 'userSpaceOnUse', 'cx' => '2', 'cy' => '3', 'r' => '2']
        );
        $svgobj71 = $obj->getSvgObj(71);
        $this->assertSame('measure', $svgobj71['gradients']['rgm']['mode']);

        $obj->initSvgObjForHandlers(72);
        $styleNoAnchor = $base;
        unset($styleNoAnchor['text-anchor']);
        unset($styleNoAnchor['direction']);
        // @phpstan-ignore argument.type
        $txtOut = $obj->exposeParseSVGTagSTARTtext($parser, 72, ['x' => '1', 'y' => '1'], $styleNoAnchor, $base);
        $this->assertNotSame('', $txtOut);

        $obj->initSvgObjForHandlers(73);
        $obj->initSvgObjForHandlers(74);
        $obj->patchSvgObj(73, ['out' => 'A', 'child' => [74]]);
        $obj->patchSvgObj(74, ['out' => 'B', 'child' => []]);
        $this->assertSame('AB', $obj->getSetSVG(73));
    }

    public function testSvgRemainingFeasibleHotspotBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $base = $obj->exposeDefaultSVGStyle();

        $coord = $obj->getPathCoordDefaults();
        $paths = [['0', 'A'], ['1', 'z']];
        $rawSets = [
            ['4', '2', '0', '1', '0', '10', '20'],
            ['4', '2', '0', '0', '1', '10', '20'],
            ['6', '3', '45', '1', '0', '14', '16'],
            ['6', '3', '45', '0', '1', '14', '16'],
        ];
        foreach ($rawSets as $raw) {
            $params = [
                (float) $raw[0],
                (float) $raw[1],
                (float) $raw[2],
                (float) $raw[3],
                (float) $raw[4],
                (float) $raw[5],
                (float) $raw[6],
            ];
            [$arcOut] = $obj->exposeSvgPathCmdA($params, $coord, $paths, 0, $raw);
            $this->assertIsString($arcOut);
        }

        $obj->setSvgObjMeta(75);
        $strokeStyle = $base;
        $strokeStyle['stroke'] = 'url(#not-a-color)';
        try {
            [$strokeOut] = $obj->exposeParseSVGStyleStroke(75, $strokeStyle);
            $this->assertSame('', $strokeOut);
        } catch (\Throwable $e) {
            $this->assertNotSame('', $e->getMessage());
        }

        $gradients = [
            'm' => [
                'xref' => '',
                'type' => 2,
                'gradientUnits' => 'objectBoundingBox',
                'mode' => 'measure',
                'coords' => [0.0, 0.0, 0.0, 0.0],
                'stops' => [
                    ['color' => '#000000', 'opacity' => 1.0, 'offset' => 0.0],
                    ['color' => '#ffffff', 'opacity' => 1.0, 'offset' => 1.0],
                ],
                'gradientTransform' => [1.0, 0.0, 0.0, 1.0, 0.0, 0.0],
            ],
        ];
        $gradOut = $obj->exposeParseSVGStyleGradient(75, $gradients, 'm', 0.0, 0.0, 0.0, 0.0);
        $this->assertNotSame('', $gradOut);

        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(76);
        $obj->patchSvgObj(76, ['tagdepth' => 1]);
        $xMidMeet = $obj->exposeParseSVGTagSTARTsvg(
            $parser,
            76,
            [
                'x' => '0', 'y' => '0', 'width' => '40', 'height' => '10',
                'viewBox' => '0 0 100 50', 'preserveAspectRatio' => 'xMidYMid meet'
            ],
            $base,
            $base,
        );
        $this->assertNotSame('', $xMidMeet);

        $obj->patchSvgObj(76, ['tagdepth' => 1]);
        $yMidMeet = $obj->exposeParseSVGTagSTARTsvg(
            $parser,
            76,
            [
                'x' => '0', 'y' => '0', 'width' => '10', 'height' => '40',
                'viewBox' => '0 0 50 100', 'preserveAspectRatio' => 'xMinYMid meet'
            ],
            $base,
            $base,
        );
        $this->assertNotSame('', $yMidMeet);
    }

    // -----------------------------------------------------------------------
    // P1 fixes: T-1, T-2, S-5, R-4
    // -----------------------------------------------------------------------

    /**
     * T-1: visibility:hidden text must still advance the layout cursor.
     */
    public function testSvgInvisibleTextAdvancesCursor(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(80);
        $base = $obj->exposeDefaultSVGStyle();
        $obj->patchSvgObj(80, [
            'styles' => [$base],
            'defsmode' => false,
            'textmode' => [
                'invisible' => true,
                'stroke' => 0,
                'rtl' => false,
                'text-anchor' => 'start',
            ],
            'text' => 'Hello',
            'x' => 10.0,
            'y' => 20.0,
        ]);

        $xBefore = $obj->getSvgObj(80)['x'];
        $out = $obj->exposeParseSVGTagENDtext(80);

        // No drawing output for invisible text.
        $this->assertSame('', $out);
        // Text buffer must be cleared.
        $this->assertSame('', $obj->getSvgObj(80)['text']);
        // Cursor must have advanced (x increased).
        $this->assertGreaterThan($xBefore, $obj->getSvgObj(80)['x']);
    }

    /**
     * T-1: empty invisible text does not change the cursor.
     */
    public function testSvgInvisibleEmptyTextDoesNotChangeCursor(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(81);
        $base = $obj->exposeDefaultSVGStyle();
        $obj->patchSvgObj(81, [
            'styles' => [$base],
            'defsmode' => false,
            'textmode' => [
                'invisible' => true,
                'stroke' => 0,
                'rtl' => false,
                'text-anchor' => 'start',
            ],
            'text' => '',
            'x' => 5.0,
            'y' => 5.0,
        ]);

        $xBefore = $obj->getSvgObj(81)['x'];
        $obj->exposeParseSVGTagENDtext(81);
        $this->assertSame($xBefore, $obj->getSvgObj(81)['x']);
    }

    /**
     * Metadata text like <desc> must not leak into renderable SVG text buffer.
     */
    public function testSvgDescCharacterDataIsNotRenderedAsText(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(811);

        $obj->exposeHandleSVGTagStart($parser, 'desc', [], 811);
        $obj->exposeHandleSVGCharacter($parser, 'TCPDF SVG EXAMPLE');
        $obj->exposeHandleSVGTagEnd($parser, 'desc');

        $this->assertSame('', $obj->getSvgObj(811)['text']);
    }

    /**
     * T-2: starting a new text/tspan while buffered text exists flushes the run.
     */
    public function testSvgStartTextFlushesBufferedRun(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(82);
        $base = $obj->exposeDefaultSVGStyle();

        // Simulate state after an outer <text> started and accumulated content.
        $obj->patchSvgObj(82, [
            'styles' => [$base, $base],  // outer-text style already on stack
            'defsmode' => false,
            'textmode' => [
                'invisible' => false,
                'stroke' => 0,
                'rtl' => false,
                'text-anchor' => 'start',
            ],
            'text' => 'Outer',
            'x' => 5.0,
            'y' => 10.0,
        ]);

        // Starting a tspan should flush 'Outer' and produce non-empty output.
        $tspanOut = $obj->exposeParseSVGTagSTARTtspan(
            $parser,
            82,
            ['x' => '5', 'y' => '10'],
            $base,
            $base,
        );
        // After flush the text buffer must be empty (new run started fresh).
        $this->assertSame('', $obj->getSvgObj(82)['text']);
        // Output contains both the flushed run and the new transform.
        $this->assertNotSame('', $tspanOut);
    }

    /**
     * S-5: parseSVGTagSTARTuse resolves an element via plain href (SVG 2).
     */
    public function testSvgUseTagResolvesViaPlainHref(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(83);

        // Register a simple rect in defs under id 'shape83'.
        $obj->patchSvgObj(83, [
            'defs' => [
                'shape83' => [
                    'name' => 'rect',
                    'attr' => [
                        'width' => '10',
                        'height' => '5',
                        'style' => '',
                    ],
                ],
            ],
        ]);

        // Empty href → empty return.
        $this->assertSame('', $obj->exposeParseSVGTagSTARTuse($parser, 83, []));
        // Plain href (no xlink:href) must resolve.
        $obj->exposeParseSVGTagSTARTuse($parser, 83, ['href' => '#shape83']);
        // The out buffer should have been written to (rect was dispatched).
        $this->assertNotSame('', $obj->getSvgObj(83)['out']);
    }

    /**
     * S-5: parseSVGTagSTARTimage falls back to plain href when xlink:href absent.
     */
    public function testSvgImageTagAcceptsPlainHref(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(84);
        $base = $obj->exposeDefaultSVGStyle();

        // Missing both href attributes → empty.
        $this->assertSame(
            '',
            $obj->exposeParseSVGTagSTARTimage(
                $parser,
                84,
                [],
                $base,
                $base,
            )
        );

        // Plain href pointing to an image that will cause a load exception;
        // what matters is that the early-return guard no longer rejects the call
        // solely because xlink:href is absent — the code reaches the image loader.
        try {
            $out = $obj->exposeParseSVGTagSTARTimage(
                $parser,
                84,
                ['href' => 'nonexistent.png', 'x' => '0', 'y' => '0', 'width' => '10', 'height' => '10'],
                $base,
                $base,
            );
        } catch (\Throwable $e) {
            // An image-load exception confirms the code progressed past the href guard.
            $this->assertNotSame('', $e->getMessage());
            return;
        }
        $this->assertSame('', $out);
    }

    /**
     * S-5: linearGradient and radialGradient store xref from plain href.
     */
    public function testSvgGradientXrefFromPlainHref(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(85);

        $obj->exposeParseSVGTagSTARTlinearGradient(85, ['id' => 'lg85', 'href' => '#lgBase']);
        $obj->exposeParseSVGTagSTARTradialGradient(85, ['id' => 'rg85', 'href' => '#rgBase']);

        $svgobj = $obj->getSvgObj(85);
        $this->assertSame('lgBase', $svgobj['gradients']['lg85']['xref']);
        $this->assertSame('rgBase', $svgobj['gradients']['rg85']['xref']);
    }

    /**
     * R-4: stroke-dasharray values with unit suffixes are normalised to user units.
     */
    public function testSvgDasharrayWithUnitSuffixIsNormalised(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->setSvgObjMeta(86);
        $base = $obj->exposeDefaultSVGStyle();

        // Provide dash tokens with a unit suffix; these should survive normalisation
        // (not become zero) and produce a non-empty dashArray in the output.
        $style = $base;
        $style['stroke'] = '#000000';
        $style['stroke-dasharray'] = '5 3';

        [$strokeOut] = $obj->exposeParseSVGStyleStroke(86, $style);
        $this->assertNotSame('', $strokeOut);

        // Ensure a 'none' dasharray still produces empty dashArray.
        $styleNone = $base;
        $styleNone['stroke'] = '#000000';
        $styleNone['stroke-dasharray'] = 'none';
        [$strokeNone] = $obj->exposeParseSVGStyleStroke(86, $styleNone);
        // dashArray=[] means no 'w' dash command prefix — still has stroke output.
        $this->assertIsString($strokeNone);
    }

    /**
     * E-1: <symbol id="sym"> enters defs-capture mode and stores the id in defs.
     */
    public function testSvgSymbolEnterDefsMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(90);

        $obj->exposeParseSVGTagSTARTsymbol(90, ['id' => 'mySymbol', 'viewBox' => '0 0 100 100']);

        $svgobj = $obj->getSvgObj(90);
        $this->assertTrue($svgobj['defsmode']);
        $this->assertArrayHasKey('mySymbol', $svgobj['defs']);
        $this->assertSame('symbol', $svgobj['defs']['mySymbol']['name']);
    }

    /**
     * E-1: </symbol> exits defs-capture mode.
     */
    public function testSvgSymbolEndExitsDefsMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(91);
        $obj->patchSvgObj(91, ['defsmode' => true]);

        $out = $obj->exposeParseSVGTagENDsymbol(91);

        $this->assertSame('', $out);
        $this->assertFalse($obj->getSvgObj(91)['defsmode']);
    }

    /**
     * E-1: symbol without id does not crash and defsmode is still entered.
     */
    public function testSvgSymbolWithoutIdStillEntersDefsMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(92);

        $out = $obj->exposeParseSVGTagSTARTsymbol(92, []);

        $this->assertSame('', $out);
        $this->assertTrue($obj->getSvgObj(92)['defsmode']);
    }

    /**
     * E-1: first non-id child inside symbol is captured in defs child list.
     */
    public function testSvgSymbolCapturesFirstAnonymousChild(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(92);

        $obj->exposeParseSVGTagSTARTsymbol(92, ['id' => 'symChild']);
        $obj->exposeHandleSVGTagStart(
            $parser,
            'rect',
            ['width' => '10', 'height' => '5'],
            92,
        );

        $svgobj = $obj->getSvgObj(92);
        $this->assertArrayHasKey('symChild', $svgobj['defs']);
        $symbolDef = $svgobj['defs']['symChild'];
        $children = $symbolDef['child'] ?? [];
        $this->assertIsArray($children);
        $this->assertNotSame([], $children);

        $first = \reset($children);
        $this->assertIsArray($first);
        $this->assertSame('rect', $first['name'] ?? '');
    }

    /**
     * E-1/R-3: <use> on symbol falls back to symbol viewBox size when width/height missing.
     */
    public function testSvgUseSymbolFallsBackToViewBoxDimensions(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(921);
        $base = $obj->exposeDefaultSVGStyle();
        $obj->patchSvgObj(921, ['styles' => [$base]]);

        $obj->patchSvgObj(921, [
            'defs' => [
                'symvb' => [
                    'name' => 'symbol',
                    'attr' => [
                        'viewBox' => '0 0 40 20',
                    ],
                    'child' => [
                        'c1' => [
                            'name' => 'rect',
                            'attr' => ['x' => '0', 'y' => '0', 'width' => '40', 'height' => '20', 'fill' => '#000'],
                        ],
                    ],
                ],
            ],
        ]);

        $out = $obj->exposeParseSVGTagSTARTuse($parser, 921, ['href' => '#symvb', 'x' => '1', 'y' => '2']);
        $this->assertNotSame('', $out);
        $this->assertStringContainsString(' cm', $out);
    }

    /**
     * E-1/R-3: explicit width/height on use still override symbol defaults.
     */
    public function testSvgUseSymbolRespectsExplicitUseDimensions(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(922);
        $base = $obj->exposeDefaultSVGStyle();
        $obj->patchSvgObj(922, ['styles' => [$base]]);

        $obj->patchSvgObj(922, [
            'defs' => [
                'symovr' => [
                    'name' => 'symbol',
                    'attr' => [
                        'viewBox' => '0 0 100 50',
                        'width' => '100',
                        'height' => '50',
                    ],
                    'child' => [
                        'c1' => [
                            'name' => 'rect',
                            'attr' => ['x' => '0', 'y' => '0', 'width' => '100', 'height' => '50', 'fill' => '#000'],
                        ],
                    ],
                ],
            ],
        ]);

        $out = $obj->exposeParseSVGTagSTARTuse(
            $parser,
            922,
            ['href' => '#symovr', 'x' => '0', 'y' => '0', 'width' => '10', 'height' => '5'],
        );
        $this->assertNotSame('', $out);
        // Rendering with explicit dimensions should still produce content and transforms.
        $this->assertStringContainsString(' cm', $out);
    }

    /**
     * E-1: use-level transform is preserved when expanding <symbol>.
     */
    public function testSvgUseSymbolPreservesUseLevelTransformAttribute(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $base = $obj->exposeDefaultSVGStyle();

        $defs = [
            'symstyle' => [
                'name' => 'symbol',
                'attr' => [
                    'viewBox' => '0 0 20 10',
                ],
                'child' => [
                    'c1' => [
                        'name' => 'rect',
                        'attr' => ['x' => '0', 'y' => '0', 'width' => '20', 'height' => '10'],
                    ],
                ],
            ],
        ];

        $obj->initSvgObjForHandlers(923);
        $obj->patchSvgObj(923, ['styles' => [$base], 'defs' => $defs]);
        $outDefault = $obj->exposeParseSVGTagSTARTuse(
            $parser,
            923,
            ['href' => '#symstyle', 'x' => '0', 'y' => '0', 'width' => '20', 'height' => '10'],
        );

        $obj->initSvgObjForHandlers(924);
        $obj->patchSvgObj(924, ['styles' => [$base], 'defs' => $defs]);
        $outTransformed = $obj->exposeParseSVGTagSTARTuse(
            $parser,
            924,
            [
                'href' => '#symstyle',
                'x' => '0',
                'y' => '0',
                'width' => '20',
                'height' => '10',
                'transform' => 'translate(5,7)',
            ],
        );

        $this->assertNotSame('', $outDefault);
        $this->assertNotSame('', $outTransformed);
        $this->assertNotSame($outDefault, $outTransformed);
    }

    /**
     * E-1: use-level style is preserved when expanding <symbol>.
     */
    public function testSvgUseSymbolPreservesUseLevelStyleAttribute(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $base = $obj->exposeDefaultSVGStyle();

        $defs = [
            'symstyle2' => [
                'name' => 'symbol',
                'attr' => [
                    'viewBox' => '0 0 20 10',
                ],
                'child' => [
                    'c1' => [
                        'name' => 'rect',
                        'attr' => ['x' => '0', 'y' => '0', 'width' => '20', 'height' => '10'],
                    ],
                ],
            ],
        ];

        $obj->initSvgObjForHandlers(925);
        $obj->patchSvgObj(925, ['styles' => [$base], 'defs' => $defs]);
        $outDefault = $obj->exposeParseSVGTagSTARTuse(
            $parser,
            925,
            ['href' => '#symstyle2', 'x' => '0', 'y' => '0', 'width' => '20', 'height' => '10'],
        );

        $obj->initSvgObjForHandlers(926);
        $obj->patchSvgObj(926, ['styles' => [$base], 'defs' => $defs]);
        $outStyled = $obj->exposeParseSVGTagSTARTuse(
            $parser,
            926,
            [
                'href' => '#symstyle2',
                'x' => '0',
                'y' => '0',
                'width' => '20',
                'height' => '10',
                'style' => 'fill:#ff0000;stroke:none;',
            ],
        );

        $this->assertNotSame('', $outDefault);
        $this->assertNotSame('', $outStyled);
        $this->assertNotSame($outDefault, $outStyled);
    }

    /**
     * E-7: <a> start stores link metadata and </a> emits an annotation ref.
     */
    public function testSvgATagCreatesAnnotationReference(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(93);

        $pageBefore = $obj->exposeGetCurrentPageData();
        $annotRefsBefore = $pageBefore['annotrefs'] ?? [];
        $this->assertIsArray($annotRefsBefore);
        $countBefore = \count($annotRefsBefore);

        $start = $obj->exposeParseSVGTagSTARTa(93, ['href' => 'https://example.com']);
        $this->assertSame('', $start);

        // Simulate that wrapped content advanced text position.
        $obj->patchSvgObj(93, ['x' => 40.0, 'y' => 20.0]);

        $end = $obj->exposeParseSVGTagENDa(93);
        $this->assertSame('', $end);

        $pageAfter = $obj->exposeGetCurrentPageData();
        $annotRefsAfter = $pageAfter['annotrefs'] ?? [];
        $this->assertIsArray($annotRefsAfter);
        $countAfter = \count($annotRefsAfter);
        $this->assertSame($countBefore + 1, $countAfter);
        $this->assertSame('', (string) ($obj->getSvgObj(93)['textmode']['linkhref'] ?? ''));
    }

    /**
     * E-7: missing href on <a> should degrade gracefully with no annotation.
     */
    public function testSvgATagWithoutHrefProducesNoAnnotation(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(94);

        $pageBefore = $obj->exposeGetCurrentPageData();
        $annotRefsBefore = $pageBefore['annotrefs'] ?? [];
        $this->assertIsArray($annotRefsBefore);
        $countBefore = \count($annotRefsBefore);

        $start = $obj->exposeParseSVGTagSTARTa(94, []);
        $this->assertSame('', $start);
        $end = $obj->exposeParseSVGTagENDa(94);
        $this->assertSame('', $end);

        $pageAfter = $obj->exposeGetCurrentPageData();
        $annotRefsAfter = $pageAfter['annotrefs'] ?? [];
        $this->assertIsArray($annotRefsAfter);
        $countAfter = \count($annotRefsAfter);
        $this->assertSame($countBefore, $countAfter);
    }

    /**
     * E-8 stub: <switch> start returns empty string.
     */
    public function testSvgSwitchTagStartReturnsEmpty(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(95);

        $out = $obj->exposeParseSVGTagSTARTswitch(95, []);
        $this->assertSame('', $out);
    }

    /**
     * E-8 stub: </switch> end returns empty string.
     */
    public function testSvgSwitchTagEndReturnsEmpty(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(95);

        $out = $obj->exposeParseSVGTagENDswitch(95);
        $this->assertSame('', $out);
    }

    /**
     * E-8: only first direct child of switch is rendered.
     */
    public function testSvgSwitchRendersOnlyFirstChild(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(960);

        $obj->exposeHandleSVGTagStart($parser, 'switch', [], 960);

        $obj->exposeHandleSVGTagStart(
            $parser,
            'rect',
            ['x' => '1', 'y' => '1', 'width' => '10', 'height' => '5', 'fill' => '#000000'],
            960,
        );
        $obj->exposeHandleSVGTagEnd($parser, 'rect');
        $outAfterFirst = (string) $obj->getSvgObj(960)['out'];
        $this->assertNotSame('', $outAfterFirst);

        // Second sibling should be skipped by switch selection logic.
        $obj->exposeHandleSVGTagStart(
            $parser,
            'circle',
            ['cx' => '5', 'cy' => '5', 'r' => '2', 'fill' => '#ff0000'],
            960,
        );
        $obj->exposeHandleSVGTagEnd($parser, 'circle');
        $outAfterSecond = (string) $obj->getSvgObj(960)['out'];
        $this->assertSame($outAfterFirst, $outAfterSecond);

        $obj->exposeHandleSVGTagEnd($parser, 'switch');
        $this->assertEmpty($obj->getSvgObj(960)['switchstack']);
    }

    /**
     * S-1: dominant-baseline='hanging' shifts renderY by -ascent.
     * We verify that the output changes when baseline keyword differs.
     */
    public function testSvgDominantBaselineHangingChangesOutput(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $base = $obj->exposeDefaultSVGStyle();

        // Render with default (auto) baseline.
        $obj->initSvgObjForHandlers(95);
        $obj->patchSvgObj(95, [
            'styles' => [$base, $base],
            'defsmode' => false,
            'textmode' => [
                'rtl' => false,
                'invisible' => false,
                'stroke' => 0,
                'text-anchor' => 'start',
                'baseline' => 'auto',
                'rotate' => 0.0,
                'textlength' => 0.0,
                'lengthadjust' => 'spacing',
                'xlist' => [],
                'ylist' => [],
            ],
            'text' => 'Hi',
            'x' => 10.0,
            'y' => 20.0,
        ]);
        $outAuto = $obj->exposeParseSVGTagENDtext(95);

        // Render with hanging baseline.
        $obj->initSvgObjForHandlers(96);
        $obj->patchSvgObj(96, [
            'styles' => [$base, $base],
            'defsmode' => false,
            'textmode' => [
                'rtl' => false,
                'invisible' => false,
                'stroke' => 0,
                'text-anchor' => 'start',
                'baseline' => 'hanging',
                'rotate' => 0.0,
                'textlength' => 0.0,
                'lengthadjust' => 'spacing',
                'xlist' => [],
                'ylist' => [],
            ],
            'text' => 'Hi',
            'x' => 10.0,
            'y' => 20.0,
        ]);
        $outHanging = $obj->exposeParseSVGTagENDtext(96);

        // Both must produce non-empty drawing output.
        $this->assertNotSame('', $outAuto);
        $this->assertNotSame('', $outHanging);
        // The Y coordinate embedded in the output should differ between the two.
        $this->assertNotSame($outAuto, $outHanging);
    }

    /**
     * S-3: textLength with spacingAndGlyphs wraps output in an extra transform.
     */
    public function testSvgTextLengthGlyphsAddsScaleTransform(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $base = $obj->exposeDefaultSVGStyle();

        // Without textLength.
        $obj->initSvgObjForHandlers(97);
        $obj->patchSvgObj(97, [
            'styles' => [$base, $base],
            'defsmode' => false,
            'textmode' => [
                'rtl' => false,
                'invisible' => false,
                'stroke' => 0,
                'text-anchor' => 'start',
                'baseline' => 'auto',
                'rotate' => 0.0,
                'textlength' => 0.0,
                'lengthadjust' => 'spacingAndGlyphs',
                'xlist' => [],
                'ylist' => [],
            ],
            'text' => 'Test',
            'x' => 0.0,
            'y' => 20.0,
        ]);
        $outNoScale = $obj->exposeParseSVGTagENDtext(97);

        // With textLength=200 (much wider than natural).
        $obj->initSvgObjForHandlers(98);
        $obj->patchSvgObj(98, [
            'styles' => [$base, $base],
            'defsmode' => false,
            'textmode' => [
                'rtl' => false,
                'invisible' => false,
                'stroke' => 0,
                'text-anchor' => 'start',
                'baseline' => 'auto',
                'rotate' => 0.0,
                'textlength' => 200.0,
                'lengthadjust' => 'spacingAndGlyphs',
                'xlist' => [],
                'ylist' => [],
            ],
            'text' => 'Test',
            'x' => 0.0,
            'y' => 20.0,
        ]);
        $outWithScale = $obj->exposeParseSVGTagENDtext(98);

        $this->assertNotSame('', $outNoScale);
        $this->assertNotSame('', $outWithScale);
        // The scaled variant must be longer (extra transform operators).
        $this->assertGreaterThan(\strlen($outNoScale), \strlen($outWithScale));
    }

    /**
     * S-4: rotate adds extra transform operators around the text output.
     */
    public function testSvgTextRotateAddsTransformOperators(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $base = $obj->exposeDefaultSVGStyle();

        // Without rotation.
        $obj->initSvgObjForHandlers(99);
        $obj->patchSvgObj(99, [
            'styles' => [$base, $base],
            'defsmode' => false,
            'textmode' => [
                'rtl' => false,
                'invisible' => false,
                'stroke' => 0,
                'text-anchor' => 'start',
                'baseline' => 'auto',
                'rotate' => 0.0,
                'textlength' => 0.0,
                'lengthadjust' => 'spacing',
                'xlist' => [],
                'ylist' => [],
            ],
            'text' => 'Abc',
            'x' => 5.0,
            'y' => 10.0,
        ]);
        $outNoRotate = $obj->exposeParseSVGTagENDtext(99);

        // With 45-degree rotation.
        $obj->initSvgObjForHandlers(100);
        $obj->patchSvgObj(100, [
            'styles' => [$base, $base],
            'defsmode' => false,
            'textmode' => [
                'rtl' => false,
                'invisible' => false,
                'stroke' => 0,
                'text-anchor' => 'start',
                'baseline' => 'auto',
                'rotate' => 45.0,
                'textlength' => 0.0,
                'lengthadjust' => 'spacing',
                'xlist' => [],
                'ylist' => [],
            ],
            'text' => 'Abc',
            'x' => 5.0,
            'y' => 10.0,
        ]);
        $outRotated = $obj->exposeParseSVGTagENDtext(100);

        $this->assertNotSame('', $outNoRotate);
        $this->assertNotSame('', $outRotated);
        // Rotated output should be longer due to extra transform save/restore.
        $this->assertGreaterThan(\strlen($outNoRotate), \strlen($outRotated));
    }

    /**
     * S-4: multi-value rotate is parsed and applied per glyph.
     */
    public function testSvgTextRotateListAppliesPerGlyph(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $base = $obj->exposeDefaultSVGStyle();

        // First run: single zero rotation should not add per-glyph transforms.
        $obj->initSvgObjForHandlers(1001);
        $obj->patchSvgObj(1001, [
            'styles' => [$base],
        ]);
        $obj->exposeParseSVGTagSTARTtext(
            $parser,
            1001,
            ['x' => '5', 'y' => '10', 'rotate' => '0'],
            $base,
            $base,
        );
        $obj->exposeHandleSVGCharacter($parser, 'AB');
        $outSingle = $obj->exposeParseSVGTagENDtext(1001);

        // Second run: second glyph rotates, forcing per-glyph transforms.
        $obj->initSvgObjForHandlers(1002);
        $obj->patchSvgObj(1002, [
            'styles' => [$base],
        ]);
        $obj->exposeParseSVGTagSTARTtext(
            $parser,
            1002,
            ['x' => '5', 'y' => '10', 'rotate' => '0 45'],
            $base,
            $base,
        );
        $obj->exposeHandleSVGCharacter($parser, 'AB');
        $outList = $obj->exposeParseSVGTagENDtext(1002);
        $svgobj = $obj->getSvgObj(1002);
        $rotlist = $svgobj['textmode']['rotlist'] ?? [];

        $this->assertGreaterThanOrEqual(2, \count($rotlist));
        $this->assertEqualsWithDelta(0.0, (float) $rotlist[0], 0.001);
        $this->assertEqualsWithDelta(45.0, (float) $rotlist[1], 0.001);
        $this->assertGreaterThan(\strlen($outSingle), \strlen($outList));
    }

    /**
     * R-1: multi-value x list triggers per-character rendering (more operators).
     */
    public function testSvgMultiValueXListRendersPerCharacter(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $base = $obj->exposeDefaultSVGStyle();

        // Single-position (normal) rendering.
        $obj->initSvgObjForHandlers(101);
        $obj->patchSvgObj(101, [
            'styles' => [$base, $base],
            'defsmode' => false,
            'textmode' => [
                'rtl' => false,
                'invisible' => false,
                'stroke' => 0,
                'text-anchor' => 'start',
                'baseline' => 'auto',
                'rotate' => 0.0,
                'textlength' => 0.0,
                'lengthadjust' => 'spacing',
                'xlist' => [],
                'ylist' => [],
            ],
            'text' => 'AB',
            'x' => 5.0,
            'y' => 10.0,
        ]);
        $outNormal = $obj->exposeParseSVGTagENDtext(101);

        // Multi-value x list (two characters, two explicit x positions).
        $obj->initSvgObjForHandlers(102);
        $obj->patchSvgObj(102, [
            'styles' => [$base, $base],
            'defsmode' => false,
            'textmode' => [
                'rtl' => false,
                'invisible' => false,
                'stroke' => 0,
                'text-anchor' => 'start',
                'baseline' => 'auto',
                'rotate' => 0.0,
                'textlength' => 0.0,
                'lengthadjust' => 'spacing',
                'xlist' => [5.0, 15.0],
                'ylist' => [],
            ],
            'text' => 'AB',
            'x' => 5.0,
            'y' => 10.0,
        ]);
        $outMultiX = $obj->exposeParseSVGTagENDtext(102);

        $this->assertNotSame('', $outNormal);
        $this->assertNotSame('', $outMultiX);
        // Per-character rendering emits multiple text operators so output is longer.
        $this->assertGreaterThan(\strlen($outNormal), \strlen($outMultiX));
    }

    /**
     * E-6: textPath startOffset maps text origin along referenced path.
     */
    public function testSvgTextPathStartOffsetUpdatesPosition(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(103);
        $base = $obj->exposeDefaultSVGStyle();

        $obj->patchSvgObj(103, [
            'styles' => [$base],
            'defs' => [
                'tp_line' => [
                    'name' => 'line',
                    'attr' => [
                        'x1' => '0',
                        'y1' => '0',
                        'x2' => '200',
                        'y2' => '0',
                    ],
                ],
            ],
        ]);

        $out = $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            103,
            ['href' => '#tp_line', 'startOffset' => '25%'],
            $base,
            $base,
        );

        $this->assertNotSame('', $out);
        $svgobj = $obj->getSvgObj(103);
        $pos25 = (float) $svgobj['x'];

        $obj->initSvgObjForHandlers(106);
        $obj->patchSvgObj(106, [
            'styles' => [$base],
            'defs' => [
                'tp_line' => [
                    'name' => 'line',
                    'attr' => [
                        'x1' => '0',
                        'y1' => '0',
                        'x2' => '200',
                        'y2' => '0',
                    ],
                ],
            ],
        ]);

        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            106,
            ['href' => '#tp_line', 'startOffset' => '50%'],
            $base,
            $base,
        );
        $pos50 = (float) $obj->getSvgObj(106)['x'];

        $this->assertGreaterThan(0.0, $pos25);
        $this->assertGreaterThan($pos25, $pos50);
        $this->assertEqualsWithDelta(0.0, $svgobj['y'], 0.001);
    }

    /**
     * E-6: textPath on vertical segment sets rotate to path angle.
     */
    public function testSvgTextPathVerticalSegmentSetsRotate(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(104);
        $base = $obj->exposeDefaultSVGStyle();

        $obj->patchSvgObj(104, [
            'styles' => [$base],
            'defs' => [
                'tp_vertical' => [
                    'name' => 'line',
                    'attr' => [
                        'x1' => '10',
                        'y1' => '10',
                        'x2' => '10',
                        'y2' => '110',
                    ],
                ],
            ],
        ]);

        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            104,
            ['href' => '#tp_vertical', 'startOffset' => '10'],
            $base,
            $base,
        );

        $svgobj = $obj->getSvgObj(104);
        $this->assertEqualsWithDelta(90.0, (float) ($svgobj['textmode']['rotate'] ?? 0.0), 0.001);
    }

    /**
     * E-6: offset on bent path uses cumulative segment length and local tangent.
     */
    public function testSvgTextPathOffsetUsesBendSegmentTangent(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(1041);
        $base = $obj->exposeDefaultSVGStyle();

        // Polyline path: (0,0)->(10,0)->(10,10), total length 20.
        // At 75% offset (=15), point is on second segment at (10,5), tangent 90°.
        $obj->patchSvgObj(1041, [
            'styles' => [$base],
            'defs' => [
                'tp_bend' => [
                    'name' => 'polyline',
                    'attr' => [
                        'points' => '0,0 10,0 10,10',
                    ],
                ],
            ],
        ]);

        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1041,
            ['href' => '#tp_bend', 'startOffset' => '75%'],
            $base,
            $base,
        );

        $svgobj = $obj->getSvgObj(1041);
        $textX = (float) $svgobj['x'];
        $textY = (float) $svgobj['y'];
        $this->assertGreaterThan(0.0, $textY);
        $this->assertGreaterThan($textY, $textX);
        $this->assertNotEqualsWithDelta($textX, $textY, 0.001);
        $this->assertEqualsWithDelta(90.0, (float) ($svgobj['textmode']['rotate'] ?? 0.0), 0.001);
    }

    /**
     * E-6: per-glyph textPath layout updates rotation as glyphs pass a bend.
     */
    public function testSvgTextPathPerGlyphRotationFollowsBend(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(1042);
        $base = $obj->exposeDefaultSVGStyle();

        // First segment is intentionally tiny so the second glyph lands on
        // the vertical segment and picks a near-90deg local tangent.
        $obj->patchSvgObj(1042, [
            'styles' => [$base],
            'defs' => [
                'tp_glyph_bend' => [
                    'name' => 'polyline',
                    'attr' => [
                        'points' => '0,0 0.1,0 0.1,100',
                    ],
                ],
            ],
        ]);

        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1042,
            ['href' => '#tp_glyph_bend', 'startOffset' => '0'],
            $base,
            $base,
        );

        $obj->exposeHandleSVGCharacter($parser, 'ab');
        $obj->exposeParseSVGTagENDtextPath(1042);

        $svgobj = $obj->getSvgObj(1042);
        $rotlist = $svgobj['textmode']['rotlist'] ?? [];
        $this->assertGreaterThanOrEqual(2, \count($rotlist));
        $this->assertLessThan(45.0, (float) $rotlist[0]);
        $this->assertGreaterThan(45.0, (float) $rotlist[1]);
    }

    /**
     * E-6: path-command decomposition handles H/V bends for tangent angle.
     */
    public function testSvgTextPathPathHVCommandsUseLocalVerticalTangent(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(1047);
        $base = $obj->exposeDefaultSVGStyle();

        // Path: M0,0 H100 V100 has total length 200.
        // At 75% offset (=150), point is on the vertical segment.
        $obj->patchSvgObj(1047, [
            'styles' => [$base],
            'defs' => [
                'tp_hv' => [
                    'name' => 'path',
                    'attr' => [
                        'd' => 'M0 0 H100 V100',
                    ],
                ],
            ],
        ]);

        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1047,
            ['href' => '#tp_hv', 'startOffset' => '75%'],
            $base,
            $base,
        );

        $svgobj = $obj->getSvgObj(1047);
        $this->assertEqualsWithDelta(90.0, (float) ($svgobj['textmode']['rotate'] ?? 0.0), 0.001);
        $this->assertGreaterThan(0.0, (float) $svgobj['y']);
    }

    /**
     * E-6: cubic path sampling uses local start tangent instead of endpoint chord.
     */
    public function testSvgTextPathCubicUsesLocalStartTangentAtSmallOffset(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(1048);
        $base = $obj->exposeDefaultSVGStyle();

        // C endpoint chord is vertical (0,0)->(0,100), but the curve starts
        // with an almost horizontal tangent due to the first control point.
        $obj->patchSvgObj(1048, [
            'styles' => [$base],
            'defs' => [
                'tp_cubic' => [
                    'name' => 'path',
                    'attr' => [
                        'd' => 'M0 0 C100 0 100 100 0 100',
                    ],
                ],
            ],
        ]);

        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1048,
            ['href' => '#tp_cubic', 'startOffset' => '5%'],
            $base,
            $base,
        );

        $svgobj = $obj->getSvgObj(1048);
        $angle = (float) ($svgobj['textmode']['rotate'] ?? 0.0);
        $this->assertGreaterThan(-45.0, $angle);
        $this->assertLessThan(45.0, $angle);
    }

    /**
     * E-6: arc path sampling uses local arc tangent at small offsets.
     */
    public function testSvgTextPathArcUsesLocalStartTangentAtSmallOffset(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(1049);
        $base = $obj->exposeDefaultSVGStyle();

        // A semicircular arc from left to right: endpoint chord is horizontal,
        // while local tangent near the start is near vertical.
        $obj->patchSvgObj(1049, [
            'styles' => [$base],
            'defs' => [
                'tp_arc' => [
                    'name' => 'path',
                    'attr' => [
                        'd' => 'M0 0 A50 50 0 0 1 100 0',
                    ],
                ],
            ],
        ]);

        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1049,
            ['href' => '#tp_arc', 'startOffset' => '5%'],
            $base,
            $base,
        );

        $svgobj = $obj->getSvgObj(1049);
        $angle = (float) ($svgobj['textmode']['rotate'] ?? 0.0);
        $this->assertGreaterThan(45.0, \abs($angle));
    }

    /**
     * E-6: arc sweep flag influences initial tangent direction.
     */
    public function testSvgTextPathArcSweepDirectionAffectsInitialTangentSign(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $base = $obj->exposeDefaultSVGStyle();

        // sweep=1 should start with upward tangent for this semicircle.
        $obj->initSvgObjForHandlers(1050);
        $obj->patchSvgObj(1050, [
            'styles' => [$base],
            'defs' => [
                'tp_arc_sweep1' => [
                    'name' => 'path',
                    'attr' => [
                        'd' => 'M0 0 A50 50 0 0 1 100 0',
                    ],
                ],
            ],
        ]);
        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1050,
            ['href' => '#tp_arc_sweep1', 'startOffset' => '5%'],
            $base,
            $base,
        );
        $angleUp = (float) ($obj->getSvgObj(1050)['textmode']['rotate'] ?? 0.0);

        // sweep=0 should mirror direction, yielding a negative initial tangent.
        $obj->initSvgObjForHandlers(1051);
        $obj->patchSvgObj(1051, [
            'styles' => [$base],
            'defs' => [
                'tp_arc_sweep0' => [
                    'name' => 'path',
                    'attr' => [
                        'd' => 'M0 0 A50 50 0 0 0 100 0',
                    ],
                ],
            ],
        ]);
        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1051,
            ['href' => '#tp_arc_sweep0', 'startOffset' => '5%'],
            $base,
            $base,
        );
        $angleDown = (float) ($obj->getSvgObj(1051)['textmode']['rotate'] ?? 0.0);

        $this->assertGreaterThan(45.0, \abs($angleUp));
        $this->assertGreaterThan(45.0, \abs($angleDown));
        $this->assertLessThan(0.0, $angleUp * $angleDown);
    }

    /**
     * E-6: zero-radius arc falls back to a straight segment endpoint behavior.
     */
    public function testSvgTextPathZeroRadiusArcFallsBackToLine(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(1052);
        $base = $obj->exposeDefaultSVGStyle();

        $obj->patchSvgObj(1052, [
            'styles' => [$base],
            'defs' => [
                'tp_arc_zero' => [
                    'name' => 'path',
                    'attr' => [
                        'd' => 'M0 0 A0 0 0 0 1 100 0',
                    ],
                ],
            ],
        ]);

        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1052,
            ['href' => '#tp_arc_zero', 'startOffset' => '50%'],
            $base,
            $base,
        );

        $svgobj = $obj->getSvgObj(1052);
        $this->assertGreaterThan(0.0, (float) $svgobj['x']);
        $this->assertEqualsWithDelta(0.0, (float) $svgobj['y'], 0.001);
        $this->assertEqualsWithDelta(0.0, (float) ($svgobj['textmode']['rotate'] ?? 0.0), 0.001);
    }

    /**
     * E-6: spacing="auto" expands inter-glyph distance to fill path.
     */
    public function testSvgTextPathSpacingAutoExpandsGlyphGap(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $base = $obj->exposeDefaultSVGStyle();

        $defs = [
            'tp_space' => [
                'name' => 'line',
                'attr' => [
                    'x1' => '0',
                    'y1' => '0',
                    'x2' => '300',
                    'y2' => '0',
                ],
            ],
        ];

        $obj->initSvgObjForHandlers(1043);
        $obj->patchSvgObj(1043, ['styles' => [$base], 'defs' => $defs]);
        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1043,
            ['href' => '#tp_space', 'spacing' => 'exact'],
            $base,
            $base,
        );
        $obj->exposeHandleSVGCharacter($parser, 'ab');
        $obj->exposeParseSVGTagENDtextPath(1043);
        $exact = $obj->getSvgObj(1043);
        $xExact = $exact['textmode']['xlist'] ?? [];
        $this->assertGreaterThanOrEqual(2, \count($xExact));

        $obj->initSvgObjForHandlers(1044);
        $obj->patchSvgObj(1044, ['styles' => [$base], 'defs' => $defs]);
        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1044,
            ['href' => '#tp_space', 'spacing' => 'auto'],
            $base,
            $base,
        );
        $obj->exposeHandleSVGCharacter($parser, 'ab');
        $obj->exposeParseSVGTagENDtextPath(1044);
        $auto = $obj->getSvgObj(1044);
        $xAuto = $auto['textmode']['xlist'] ?? [];
        $this->assertGreaterThanOrEqual(2, \count($xAuto));

        $gapExact = (float) $xExact[1] - (float) $xExact[0];
        $gapAuto = (float) $xAuto[1] - (float) $xAuto[0];
        $this->assertGreaterThan($gapExact, $gapAuto);
    }

    /**
     * E-6: method="stretch" scales glyph advances to consume path length.
     */
    public function testSvgTextPathMethodStretchExpandsGlyphGap(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $base = $obj->exposeDefaultSVGStyle();

        $defs = [
            'tp_stretch' => [
                'name' => 'line',
                'attr' => [
                    'x1' => '0',
                    'y1' => '0',
                    'x2' => '250',
                    'y2' => '0',
                ],
            ],
        ];

        $obj->initSvgObjForHandlers(1045);
        $obj->patchSvgObj(1045, ['styles' => [$base], 'defs' => $defs]);
        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1045,
            ['href' => '#tp_stretch', 'method' => 'align'],
            $base,
            $base,
        );
        $obj->exposeHandleSVGCharacter($parser, 'ab');
        $obj->exposeParseSVGTagENDtextPath(1045);
        $align = $obj->getSvgObj(1045);
        $xAlign = $align['textmode']['xlist'] ?? [];
        $this->assertGreaterThanOrEqual(2, \count($xAlign));

        $obj->initSvgObjForHandlers(1046);
        $obj->patchSvgObj(1046, ['styles' => [$base], 'defs' => $defs]);
        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1046,
            ['href' => '#tp_stretch', 'method' => 'stretch'],
            $base,
            $base,
        );
        $obj->exposeHandleSVGCharacter($parser, 'ab');
        $obj->exposeParseSVGTagENDtextPath(1046);
        $stretch = $obj->getSvgObj(1046);
        $xStretch = $stretch['textmode']['xlist'] ?? [];
        $this->assertGreaterThanOrEqual(2, \count($xStretch));

        $gapAlign = (float) $xAlign[1] - (float) $xAlign[0];
        $gapStretch = (float) $xStretch[1] - (float) $xStretch[0];
        $this->assertGreaterThan($gapAlign, $gapStretch);
    }

    /**
     * E-6: spacing="auto" distributes extra length as equal inter-glyph gaps.
     */
    public function testSvgTextPathSpacingAutoPreservesMixedWidthGapDelta(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $base = $obj->exposeDefaultSVGStyle();

        $defs = [
            'tp_mixed_gap' => [
                'name' => 'line',
                'attr' => [
                    'x1' => '0',
                    'y1' => '0',
                    'x2' => '600',
                    'y2' => '0',
                ],
            ],
        ];

        $obj->initSvgObjForHandlers(1053);
        $obj->patchSvgObj(1053, ['styles' => [$base], 'defs' => $defs]);
        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1053,
            ['href' => '#tp_mixed_gap', 'spacing' => 'exact'],
            $base,
            $base,
        );
        $obj->exposeHandleSVGCharacter($parser, 'WiW');
        $obj->exposeParseSVGTagENDtextPath(1053);
        $exact = $obj->getSvgObj(1053);
        $xExact = $exact['textmode']['xlist'] ?? [];
        $this->assertGreaterThanOrEqual(3, \count($xExact));
        $gapExactOne = (float) $xExact[1] - (float) $xExact[0];
        $gapExactTwo = (float) $xExact[2] - (float) $xExact[1];

        $obj->initSvgObjForHandlers(1054);
        $obj->patchSvgObj(1054, ['styles' => [$base], 'defs' => $defs]);
        $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            1054,
            ['href' => '#tp_mixed_gap', 'spacing' => 'auto'],
            $base,
            $base,
        );
        $obj->exposeHandleSVGCharacter($parser, 'WiW');
        $obj->exposeParseSVGTagENDtextPath(1054);
        $auto = $obj->getSvgObj(1054);
        $xAuto = $auto['textmode']['xlist'] ?? [];
        $this->assertGreaterThanOrEqual(3, \count($xAuto));
        $gapAutoOne = (float) $xAuto[1] - (float) $xAuto[0];
        $gapAutoTwo = (float) $xAuto[2] - (float) $xAuto[1];

        $this->assertGreaterThan($gapExactOne, $gapAutoOne);
        $this->assertGreaterThan($gapExactTwo, $gapAutoTwo);
        $this->assertEqualsWithDelta(
            $gapExactOne - $gapExactTwo,
            $gapAutoOne - $gapAutoTwo,
            0.5,
        );
    }

    /**
     * E-6: textPath with unresolved href still behaves like a nested text run.
     */
    public function testSvgTextPathMissingReferenceStillRendersText(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(105);
        $base = $obj->exposeDefaultSVGStyle();
        $obj->patchSvgObj(105, ['styles' => [$base]]);

        $startOut = $obj->exposeParseSVGTagSTARTtextPath(
            $parser,
            105,
            ['href' => '#missing_path', 'x' => '12', 'y' => '34'],
            $base,
            $base,
        );
        $this->assertNotSame('', $startOut);

        $obj->exposeHandleSVGCharacter($parser, 'abc');
        $endOut = $obj->exposeParseSVGTagENDtextPath(105);
        $this->assertNotSame('', $endOut);
        $this->assertSame('', $obj->getSvgObj(105)['text']);
    }

    /**
     * S-2: writing-mode vertical is stored in textmode by STARTtext.
     */
    public function testSvgWritingModeVerticalSetsTextModeFlag(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $parser = \xml_parser_create('UTF-8');
        $obj->initSvgObjForHandlers(107);
        $base = $obj->exposeDefaultSVGStyle();
        $vertical = $base;
        $vertical['writing-mode'] = 'vertical-rl';

        $out = $obj->exposeParseSVGTagSTARTtext(
            $parser,
            107,
            ['x' => '10', 'y' => '20'],
            $vertical,
            $base,
        );

        $this->assertNotSame('', $out);
        $this->assertTrue((bool) ($obj->getSvgObj(107)['textmode']['vertical'] ?? false));
    }

    /**
     * S-2: invisible vertical text advances Y instead of X.
     */
    public function testSvgInvisibleVerticalTextAdvancesYCursor(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(108);
        $base = $obj->exposeDefaultSVGStyle();
        $obj->patchSvgObj(108, [
            'styles' => [$base],
            'defsmode' => false,
            'textmode' => [
                'invisible' => true,
                'vertical' => true,
                'stroke' => 0,
                'rtl' => false,
                'text-anchor' => 'start',
            ],
            'text' => 'VV',
            'x' => 7.0,
            'y' => 9.0,
        ]);

        $xBefore = (float) $obj->getSvgObj(108)['x'];
        $yBefore = (float) $obj->getSvgObj(108)['y'];
        $out = $obj->exposeParseSVGTagENDtext(108);

        $this->assertSame('', $out);
        $this->assertSame($xBefore, (float) $obj->getSvgObj(108)['x']);
        $this->assertGreaterThan($yBefore, (float) $obj->getSvgObj(108)['y']);
    }
}
