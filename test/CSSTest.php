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

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-import-type TCellBound from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TCSSBorderSpacing from \Com\Tecnick\Pdf\CSS
 */
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

    private function initPageContext(TestableCSS $obj): void
    {
        $this->initFont($obj);
        $obj->addPage();
    }

    #[DataProvider('cssDefaultSetterProvider')]
    public function testSetDefaultCSSSettersStorePointValues(string $target): void
    {
        $obj = $this->getTestObject();
        switch ($target) {
            case 'margin':
                $obj->setDefaultCSSMargin(1.0, 2.0, 3.0, 4.0);
                /** @var TCellBound $margin */
                $margin = $this->getObjectProperty($obj, 'defCSSCellMargin');
                $this->bcAssertEqualsWithDelta($obj->toPoints(1.0), $margin['T']);
                $this->bcAssertEqualsWithDelta($obj->toPoints(2.0), $margin['R']);
                $this->bcAssertEqualsWithDelta($obj->toPoints(3.0), $margin['B']);
                $this->bcAssertEqualsWithDelta($obj->toPoints(4.0), $margin['L']);
                break;
            case 'padding':
                $obj->setDefaultCSSPadding(0.5, 1.5, 2.5, 3.5);
                /** @var TCellBound $padding */
                $padding = $this->getObjectProperty($obj, 'defCSSCellPadding');
                $this->bcAssertEqualsWithDelta($obj->toPoints(0.5), $padding['T']);
                $this->bcAssertEqualsWithDelta($obj->toPoints(1.5), $padding['R']);
                $this->bcAssertEqualsWithDelta($obj->toPoints(2.5), $padding['B']);
                $this->bcAssertEqualsWithDelta($obj->toPoints(3.5), $padding['L']);
                break;
            case 'spacing':
                $obj->setDefaultCSSBorderSpacing(1.25, 2.75);
                /** @var TCSSBorderSpacing $spacing */
                $spacing = $this->getObjectProperty($obj, 'defCSSBorderSpacing');
                $this->bcAssertEqualsWithDelta($obj->toPoints(1.25), $spacing['V']);
                $this->bcAssertEqualsWithDelta($obj->toPoints(2.75), $spacing['H']);
                break;
            default:
                $this->fail('Unknown fixture target: ' . $target);
        }
    }

    /** @return array<string, array{0: string}> */
    public static function cssDefaultSetterProvider(): array
    {
        return [
            'margin' => ['margin'],
            'padding' => ['padding'],
            'spacing' => ['spacing'],
        ];
    }

    public function testGetCSSBorderWidthPointsHandlesNamedValues(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame(0.0, $obj->exposeGetCSSBorderWidthPoints(''));
        $this->assertSame(2.0, $obj->exposeGetCSSBorderWidthPoints('thin'));
        $this->assertSame(4.0, $obj->exposeGetCSSBorderWidthPoints('medium'));
        $this->assertSame(6.0, $obj->exposeGetCSSBorderWidthPoints('thick'));
        $this->assertGreaterThan(0.0, $obj->exposeGetCSSBorderWidthPoints('8px'));
    }

    public function testGetCSSBorderWidthConvertsToUserUnits(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetCSSBorderWidth('thin');

        $this->assertGreaterThan(0.0, $out);
    }

    #[DataProvider('cssBorderDashStyleProvider')]
    public function testGetCSSBorderDashStyleMapsKnownStyles(string $style, int $expected): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame($expected, $obj->exposeGetCSSBorderDashStyle($style));
    }

    /** @return array<string, array{0: string, 1: int}> */
    public static function cssBorderDashStyleProvider(): array
    {
        return [
            'none' => ['none', -1],
            'hidden' => ['hidden', -1],
            'dotted' => ['dotted', 1],
            'dashed' => ['dashed', 3],
            'double' => ['double', 0],
            'groove' => ['groove', 0],
            'ridge' => ['ridge', 0],
            'inset' => ['inset', 0],
            'outset' => ['outset', 0],
            'solid' => ['solid', 0],
            'custom' => ['custom', 0],
        ];
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
        $this->assertIsString($out['lineColor']);
        $this->assertStringContainsString('rgba(', $out['lineColor']);
    }

    public function testGetCSSBorderStyleCoversFallbackBranches(): void
    {
        $obj = $this->getInternalTestObject();

        $two = $obj->exposeGetCSSBorderStyle('dotted blue !important');
        $this->assertSame(1, $two['dashPhase']);
        $this->assertGreaterThan(0.0, $two['lineWidth']);
        $this->assertIsString($two['lineColor']);
        /** @var string $twoLineColor */
        $twoLineColor = $two['lineColor'];
        $this->assertStringContainsString('rgba(', $twoLineColor);

        $one = $obj->exposeGetCSSBorderStyle('solid');
        $this->assertSame(0, $one['dashPhase']);
        $this->assertGreaterThan(0.0, $one['lineWidth']);
        $this->assertIsString($one['lineColor']);
        /** @var string $oneLineColor */
        $oneLineColor = $one['lineColor'];
        $this->assertStringContainsString('rgba(', $oneLineColor);

        $none = $obj->exposeGetCSSBorderStyle('none');
        $this->assertSame(0, $none['lineWidth']);
        $this->assertSame('black', $none['lineColor']);

        $hidden = $obj->exposeGetCSSBorderStyle('hidden red');
        $this->assertSame(0, $hidden['lineWidth']);
        $this->assertSame([], $hidden['dashArray']);
        $this->assertSame('black', $hidden['lineColor']);

        $invalidColor = $obj->exposeGetCSSBorderStyle('1px solid transparent');
        $this->assertSame('black', $invalidColor['lineColor']);

        $importantOnly = $obj->exposeGetCSSBorderStyle('!important');
        $this->assertSame(0, $importantOnly['dashPhase']);
        $this->assertGreaterThan(0.0, $importantOnly['lineWidth']);
        $this->assertIsString($importantOnly['lineColor']);
        /** @var string $lineColor */
        $lineColor = $importantOnly['lineColor'];
        $this->assertStringContainsString('rgba(', $lineColor);
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

    public function testGetCSSPaddingCoversRemainingForms(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initPageContext($obj);

        $three = $obj->exposeGetCSSPadding('10% 20% 30%');
        $this->assertGreaterThan(0.0, $three['T']);
        $this->assertGreaterThan(0.0, $three['R']);
        $this->bcAssertEqualsWithDelta($three['R'], $three['L']);

        $two = $obj->exposeGetCSSPadding('1 2', 100.0);
        $this->bcAssertEqualsWithDelta($two['T'], $two['B']);
        $this->bcAssertEqualsWithDelta($two['R'], $two['L']);

        $one = $obj->exposeGetCSSPadding('3', 100.0);
        $this->bcAssertEqualsWithDelta($one['T'], $one['R']);
        $this->bcAssertEqualsWithDelta($one['R'], $one['B']);
        $this->bcAssertEqualsWithDelta($one['B'], $one['L']);

        $defaults = $obj->exposeGetCSSPadding('1 2 3 4 5', 100.0);
        $this->assertSame(['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0], $defaults);
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

    public function testGetCSSMarginCoversRemainingForms(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initPageContext($obj);

        $three = $obj->exposeGetCSSMargin('1 auto 3');
        $this->assertGreaterThan(0.0, $three['T']);
        $this->assertSame(0.0, $three['R']);
        $this->assertGreaterThan(0.0, $three['B']);
        $this->assertSame(0.0, $three['L']);

        $two = $obj->exposeGetCSSMargin('2 4', 100.0);
        $this->bcAssertEqualsWithDelta($two['T'], $two['B']);
        $this->bcAssertEqualsWithDelta($two['R'], $two['L']);

        $one = $obj->exposeGetCSSMargin('auto', 100.0);
        $this->assertSame(0.0, $one['T']);
        $this->assertSame(0.0, $one['R']);
        $this->assertSame(0.0, $one['B']);
        $this->assertSame(0.0, $one['L']);

        $defaults = $obj->exposeGetCSSMargin('1 2 3 4 5', 100.0);
        $this->assertSame(['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0], $defaults);
    }

    public function testGetCSSBorderMarginParsesTwoValues(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetCSSBorderMargin('1 2', 100.0);

        $this->assertGreaterThan(0.0, $out['H']);
        $this->assertGreaterThan(0.0, $out['V']);
    }

    public function testGetCSSBorderMarginCoversSingleValueAndDefault(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initPageContext($obj);

        $one = $obj->exposeGetCSSBorderMargin('5');
        $this->assertGreaterThan(0.0, $one['H']);
        $this->bcAssertEqualsWithDelta($one['H'], $one['V']);

        $defaults = $obj->exposeGetCSSBorderMargin('1 2 3', 100.0);
        $this->assertSame(['H' => 0, 'V' => 0], $defaults);
    }

    public function testIntToRomanConvertsTypicalNumber(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeIntToRoman(14);

        $this->assertSame('XIV', $out);
        $this->assertSame('I', $obj->exposeIntToRoman(1));
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
        $css = '/*c*/ body { color : red ; } @media screen { p{color:blue;} }'
            . ' @media print { h1 { font-weight : bold ; } }';

        $out = $obj->exposeTidyCSS($css);

        $this->assertStringContainsString('body{color:red;}', $out);
        $this->assertStringContainsString('h1{font-weight:bold;}', $out);
        $this->assertStringNotContainsString('p{color:blue;}', $out);
    }

    public function testTidyCSSHandlesEmptyInputAndAllMedia(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame('', $obj->exposeTidyCSS(''));

        $out = $obj->exposeTidyCSS('@media all { h2 { color : green ; } }');

        $this->assertSame('h2{color:green;}', $out);
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

    public function testExtractCSSpropertiesHandlesEmptyAndOrphanBlocks(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame([], $obj->exposeExtractCSSproperties(''));

        $out = $obj->exposeExtractCSSproperties('a{color:red;} orphan');

        $this->assertCount(1, $out);
        $this->assertContains('color:red;', \array_values($out));
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

    public function testImplodeCSSDataSkipsEmptyEntriesAndInvalidCommands(): void
    {
        $obj = $this->getInternalTestObject();
        $css = [
            ['c' => ''],
            ['c' => 'broken;color:red;;margin:1px'],
            ['c' => 'color:blue'],
        ];

        $out = $obj->exposeImplodeCSSData($css);

        $this->assertStringContainsString('margin:1px', $out);
        $this->assertStringContainsString('color:blue', $out);
        $this->assertStringNotContainsString('color:red', $out);
        $this->assertStringNotContainsString(';;', $out);
    }

    public function testGetCSSArrayFromHTMLExtractsAndRemovesCssarrayTag(): void
    {
        $obj = $this->getInternalTestObject();
        $html = '<cssarray>{"h1":"color:red;"}</cssarray><p>ok</p>';

        $out = $obj->exposeGetCSSArrayFromHTML($html);

        $this->assertSame('color:red;', $out['h1']);
        $this->assertStringNotContainsString('<cssarray>', $html);
    }

    public function testGetCSSArrayFromHTMLExtractsExternalAndInlineCss(): void
    {
        $obj = $this->getInternalTestObject();
        $cssfile = (string) \realpath(__DIR__ . '/fixtures/css/external.css');
        $html = '<link rel="stylesheet" type="text/css" media="screen" href="skip.css">'
            . '<link rel="stylesheet" type="text/css" media="print" href="' . $cssfile . '">'
            . '<style media="screen">ignored{color:red;}</style>'
            . '<style media="all">h4{font-weight:bold;}</style>'
            . '<style media="print">h5{margin:0;}</style>';

        $out = $obj->exposeGetCSSArrayFromHTML($html);
        $vals = \array_values($out);

        $this->assertContains('color:green;', $vals);
        $this->assertContains('font-weight:bold;', $vals);
        $this->assertContains('margin:0;', $vals);
        $this->assertNotContains('color:red;', $vals);
    }

    public function testGetCSSArrayFromHTMLStyleWithoutMediaAttribute(): void
    {
        $obj = $this->getInternalTestObject();
        $html = '<style>h1{color:blue;}</style>'
            . '<style media="all">h2{font-size:14pt;}</style>';

        $out = $obj->exposeGetCSSArrayFromHTML($html);
        $vals = \array_values($out);

        $this->assertContains('color:blue;', $vals);
        $this->assertContains('font-size:14pt;', $vals);
    }

    public function testGetCSSColorNormalizesValidColor(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetCSSColor('#ff0000');

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('rgba(', $out);
    }

    public function testGetCSSColorReturnsEmptyStringForInvalidColor(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame('', $obj->exposeGetCSSColor(''));
    }
}
