<?php

/**
 * CSSTest.php
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

class TestableCSS extends \Com\Tecnick\Pdf\Tcpdf
{
    public function exposeGetCSSBorderWidthPoints(string $width): float
    {
        return $this->getCSSBorderWidthPoints($width);
    }

    public function exposeGetCSSBorderWidth(string $width): float
    {
        return $this->getCSSBorderWidth($width);
    }

    public function exposeGetCSSBorderDashStyle(string $style): int
    {
        return $this->getCSSBorderDashStyle($style);
    }

    public function exposeGetCSSDefaultBorderStyle(): array
    {
        return $this->getCSSDefaultBorderStyle();
    }

    public function exposeGetCSSBorderStyle(string $cssborder): array
    {
        return $this->getCSSBorderStyle($cssborder);
    }

    public function exposeGetCSSPadding(string $csspadding, float $width = 0.0): array
    {
        return $this->getCSSPadding($csspadding, $width);
    }

    public function exposeGetCSSMargin(string $cssmargin, float $width = 0.0): array
    {
        return $this->getCSSMargin($cssmargin, $width);
    }

    public function exposeGetCSSBorderMargin(string $cssbspace, float $width = 0.0): array
    {
        return $this->getCSSBorderMargin($cssbspace, $width);
    }

    public function exposeImplodeCSSData(array $css): string
    {
        return $this->implodeCSSData($css);
    }

    public function exposeTidyCSS(string $css): string
    {
        return $this->tidyCSS($css);
    }

    public function exposeExtractCSSproperties(string $css): array
    {
        return $this->extractCSSproperties($css);
    }

    public function exposeIntToRoman(int $num): string
    {
        return $this->intToRoman($num);
    }

    public function exposeUnhtmlentities(string $text): string
    {
        return $this->unhtmlentities($text);
    }

    public function exposeGetCSSArrayFromHTML(string &$html): array
    {
        return $this->getCSSArrayFromHTML($html);
    }

    public function exposeGetCSSColor(string $color): string
    {
        return $this->getCSSColor($color);
    }
}

class CSSTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    protected function getInternalTestObject(): TestableCSS
    {
        return new TestableCSS();
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

    public function testSetDefaultCSSMarginStoresPointValues(): void
    {
        $obj = $this->getTestObject();
        $obj->setDefaultCSSMargin(1.0, 2.0, 3.0, 4.0);

        $margin = $this->getObjectProperty($obj, 'defCSSCellMargin');
        $this->bcAssertEqualsWithDelta($obj->toPoints(1.0), $margin['T']);
        $this->bcAssertEqualsWithDelta($obj->toPoints(2.0), $margin['R']);
        $this->bcAssertEqualsWithDelta($obj->toPoints(3.0), $margin['B']);
        $this->bcAssertEqualsWithDelta($obj->toPoints(4.0), $margin['L']);
    }

    public function testSetDefaultCSSPaddingStoresPointValues(): void
    {
        $obj = $this->getTestObject();
        $obj->setDefaultCSSPadding(0.5, 1.5, 2.5, 3.5);

        $padding = $this->getObjectProperty($obj, 'defCSSCellPadding');
        $this->bcAssertEqualsWithDelta($obj->toPoints(0.5), $padding['T']);
        $this->bcAssertEqualsWithDelta($obj->toPoints(1.5), $padding['R']);
        $this->bcAssertEqualsWithDelta($obj->toPoints(2.5), $padding['B']);
        $this->bcAssertEqualsWithDelta($obj->toPoints(3.5), $padding['L']);
    }

    public function testSetDefaultCSSBorderSpacingStoresPointValues(): void
    {
        $obj = $this->getTestObject();
        $obj->setDefaultCSSBorderSpacing(1.25, 2.75);

        $spacing = $this->getObjectProperty($obj, 'defCSSBorderSpacing');
        $this->bcAssertEqualsWithDelta($obj->toPoints(1.25), $spacing['V']);
        $this->bcAssertEqualsWithDelta($obj->toPoints(2.75), $spacing['H']);
    }

    public function testGetCSSBorderWidthPointsHandlesNamedValues(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame(0.0, $obj->exposeGetCSSBorderWidthPoints(''));
        $this->assertSame(2.0, $obj->exposeGetCSSBorderWidthPoints('thin'));
        $this->assertSame(4.0, $obj->exposeGetCSSBorderWidthPoints('medium'));
        $this->assertSame(6.0, $obj->exposeGetCSSBorderWidthPoints('thick'));
    }

    public function testGetCSSBorderWidthConvertsToUserUnits(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetCSSBorderWidth('thin');

        $this->assertGreaterThan(0.0, $out);
    }

    public function testGetCSSBorderDashStyleMapsKnownStyles(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame(-1, $obj->exposeGetCSSBorderDashStyle('none'));
        $this->assertSame(1, $obj->exposeGetCSSBorderDashStyle('dotted'));
        $this->assertSame(3, $obj->exposeGetCSSBorderDashStyle('dashed'));
        $this->assertSame(0, $obj->exposeGetCSSBorderDashStyle('solid'));
    }

    public function testGetCSSDefaultBorderStyleProvidesDefaults(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetCSSDefaultBorderStyle();

        $this->assertSame(0, $out['lineWidth']);
        $this->assertSame('black', $out['lineColor']);
        $this->assertSame([], $out['dashArray']);
    }

    public function testGetCSSBorderStyleParsesWidthStyleColor(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetCSSBorderStyle('2px dashed red');

        $this->assertGreaterThan(0.0, $out['lineWidth']);
        $this->assertSame(3, $out['dashPhase']);
        $this->assertStringContainsString('rgba(', $out['lineColor']);
    }

    public function testGetCSSPaddingParsesFourValues(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetCSSPadding('1 2 3 4', 200.0);

        $this->assertGreaterThan(0.0, $out['T']);
        $this->assertGreaterThan(0.0, $out['R']);
        $this->assertGreaterThan(0.0, $out['B']);
        $this->assertGreaterThan(0.0, $out['L']);
    }

    public function testGetCSSMarginConvertsAutoToZero(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetCSSMargin('auto 2 auto 4', 200.0);

        $this->assertSame(0.0, $out['T']);
        $this->assertGreaterThan(0.0, $out['R']);
        $this->assertSame(0.0, $out['B']);
        $this->assertGreaterThan(0.0, $out['L']);
    }

    public function testGetCSSBorderMarginParsesTwoValues(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetCSSBorderMargin('1 2', 100.0);

        $this->assertGreaterThan(0.0, $out['H']);
        $this->assertGreaterThan(0.0, $out['V']);
    }

    public function testIntToRomanConvertsTypicalNumber(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeIntToRoman(14);

        $this->assertSame('XIV', $out);
    }

    public function testIntToRomanReturnsDecimalAboveLimit(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeIntToRoman(4000000000);

        $this->assertSame('4000000000', $out);
    }

    public function testUnhtmlentitiesDecodesHtmlEntities(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeUnhtmlentities('&lt;a&amp;b&gt;');

        $this->assertSame('<a&b>', $out);
    }

    public function testTidyCSSKeepsAllAndPrintMediaOnly(): void
    {
        $obj = $this->getInternalTestObject();
        $css = '/*c*/ body { color : red ; } @media screen { p{color:blue;} } @media print { h1 { font-weight : bold ; } }';

        $out = $obj->exposeTidyCSS($css);

        $this->assertStringContainsString('body{color:red;}', $out);
        $this->assertStringContainsString('h1{font-weight:bold;}', $out);
        $this->assertStringNotContainsString('p{color:blue;}', $out);
    }

    public function testExtractCSSpropertiesParsesSelectorsAndValues(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeExtractCSSproperties('h1, h2 { color:red; } #id .x { margin:0; }');

        $this->assertNotEmpty($out);
        $vals = \array_values($out);
        $this->assertContains('color:red;', $vals);
        $this->assertContains('margin:0;', $vals);
    }

    public function testImplodeCSSDataPrefersLastDuplicateCommand(): void
    {
        $obj = $this->getInternalTestObject();
        $css = [
            ['c' => 'color:red;margin:1px'],
            ['c' => 'color:blue'],
        ];

        $out = $obj->exposeImplodeCSSData($css);

        $this->assertStringContainsString('color:blue', $out);
        $this->assertStringNotContainsString('color:red', $out);
    }

    public function testGetCSSArrayFromHTMLExtractsAndRemovesCssarrayTag(): void
    {
        $obj = $this->getInternalTestObject();
        $html = '<cssarray>{"h1":"color:red;"}</cssarray><p>ok</p>';

        $out = $obj->exposeGetCSSArrayFromHTML($html);

        $this->assertSame('color:red;', $out['h1']);
        $this->assertStringNotContainsString('<cssarray>', $html);
    }

    public function testGetCSSColorNormalizesValidColor(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetCSSColor('#ff0000');

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('rgba(', $out);
    }
}
