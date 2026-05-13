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
        $css =
            '/*c*/ body { color : red ; } @media screen { p{color:blue;} }'
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

    public function testExtractCSSpropertiesConformanceEscapedSelectorsFixture(): void
    {
        $obj = $this->getInternalTestObject();
        $css = (string) \file_get_contents(__DIR__ . '/fixtures/css/parser/escaped_selectors.css');

        $out = $obj->exposeExtractCSSproperties($css);
        $bySelector = $this->normalizeSelectorMap($out);

        $this->assertCount(3, $bySelector);
        $this->assertArrayHasKey('.icon\\:warning[data-kind="print-a"]:first-child', $bySelector);
        $this->assertSame('color:red;', $bySelector['.icon\\:warning[data-kind="print-a"]:first-child']);
        $this->assertArrayHasKey('#hero\\#title > a.link\\+cta[href*="campaign=42"]', $bySelector);
        $this->assertSame('margin:0;', $bySelector['#hero\\#title > a.link\\+cta[href*="campaign=42"]']);
        $this->assertArrayHasKey('nav ul li:nth-child(2) > a[title="A > B"]', $bySelector);
        $this->assertSame('text-decoration:underline;', $bySelector['nav ul li:nth-child(2) > a[title="A > B"]']);
    }

    public function testExtractCSSpropertiesConformanceComplexValuesFixture(): void
    {
        $obj = $this->getInternalTestObject();
        $css = (string) \file_get_contents(__DIR__ . '/fixtures/css/parser/complex_values.css');

        $out = $obj->exposeExtractCSSproperties($css);
        $bySelector = $this->normalizeSelectorMap($out);

        $this->assertCount(3, $bySelector);
        $this->assertArrayHasKey('a[href^="mailto:"]::after', $bySelector);
        $this->assertSame('content:"mailto:user@example.com;subject=test";', $bySelector['a[href^="mailto:"]::after']);

        $this->assertArrayHasKey('div[data-url*="example.com?a=1&b=2"]', $bySelector);
        $this->assertSame(
            'background-image:url("https://example.com/a;b.png?x=1&y=2");'
            . 'font-family:"Open Sans", "Noto Sans", sans-serif;',
            $bySelector['div[data-url*="example.com?a=1&b=2"]'],
        );

        $this->assertArrayHasKey('p.note', $bySelector);
        $this->assertSame(
            'background:linear-gradient(90deg, rgba(0,0,0,.1), rgba(255,255,255,.8));',
            $bySelector['p.note'],
        );
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

    public function testImplodeCSSDataImportantShorthandBeatsLaterNonImportantLonghand(): void
    {
        $obj = $this->getInternalTestObject();
        $css = [
            ['c' => 'margin:1px !important;'],
            ['c' => 'margin-top:5px;'],
        ];

        $out = \str_replace(' ', '', $obj->exposeImplodeCSSData($css));

        $this->assertStringContainsString('margin:1px!important;', $out);
        $this->assertStringNotContainsString('margin-top:5px', $out);
    }

    /** @param list<string> $styles */
    #[DataProvider('cssCascadeImportantSourceOrderProvider')]
    public function testImplodeCSSDataCascadeImportantAndSourceOrder(
        string $name,
        array $styles,
        string $expected,
    ): void {
        $obj = $this->getInternalTestObject();

        /** @var list<array{c: string}> $css */
        $css = [];
        foreach ($styles as $style) {
            if (!\is_string($style)) {
                continue;
            }
            $css[] = ['c' => $style];
        }

        $out = $obj->exposeImplodeCSSData($css);

        $this->assertSame($expected, $out, $name);
    }

    /** @return array<string, array{0: string, 1: list<string>, 2: string}> */
    public static function cssCascadeImportantSourceOrderProvider(): array
    {
        $json = (string) \file_get_contents(__DIR__ . '/fixtures/css/cascade/important_source_order.json');
        /** @var array<int, array{name: string, styles: list<string>, expected: string}>|null $rows */
        $rows = \json_decode($json, true);
        if (!\is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $out[$row['name']] = [$row['name'], $row['styles'], $row['expected']];
        }

        return $out;
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
        $html =
            '<link rel="stylesheet" type="text/css" media="screen" href="skip.css">'
            . '<link rel="stylesheet" type="text/css" media="print" href="'
            . $cssfile
            . '">'
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
        $html = '<style>h1{color:blue;}</style><style media="all">h2{font-size:14pt;}</style>';

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

    public function testResolveImportRulesStripsImportAndPrependsContent(): void
    {
        $obj = $this->getInternalTestObject();
        $base = (string) \realpath(__DIR__ . '/fixtures/css/import/base.css');
        $css = '@import "' . $base . '";' . "\n" . 'h3{margin:0;}';
        $seen = [];

        $result = $obj->exposeResolveImportRules($css, 0, $seen);

        // Imported content appears before the importing sheet's own rules
        $this->assertStringContainsString('h1', $result);
        $this->assertStringContainsString('h3', $result);
        $pos_h1 = (int) \strpos($result, 'h1');
        $pos_h3 = (int) \strpos($result, 'h3');
        $this->assertLessThan($pos_h3, $pos_h1, '@import content should precede importing sheet rules');
        // @import directive itself is removed
        $this->assertStringNotContainsString('@import', $result);
    }

    public function testResolveImportRulesHandlesUrlSyntax(): void
    {
        $obj = $this->getInternalTestObject();
        $base = (string) \realpath(__DIR__ . '/fixtures/css/import/base.css');
        $css = "@import url('" . $base . "');\nh4{color:green;}";
        $seen = [];

        $result = $obj->exposeResolveImportRules($css, 0, $seen);

        $this->assertStringContainsString('h1', $result);
        $this->assertStringNotContainsString('@import', $result);
    }

    public function testResolveImportRulesSkipsNonPrintMedia(): void
    {
        $obj = $this->getInternalTestObject();
        $base = (string) \realpath(__DIR__ . '/fixtures/css/import/base.css');
        $css = '@import "' . $base . '" screen;' . "\n" . 'h3{margin:0;}';
        $seen = [];

        $result = $obj->exposeResolveImportRules($css, 0, $seen);

        $this->assertStringNotContainsString('h1', $result);
        $this->assertStringNotContainsString('@import', $result);
    }

    public function testResolveImportRulesAllowsPrintMedia(): void
    {
        $obj = $this->getInternalTestObject();
        $base = (string) \realpath(__DIR__ . '/fixtures/css/import/base.css');
        $css = '@import "' . $base . '" print;' . "\n" . 'h3{margin:0;}';
        $seen = [];

        $result = $obj->exposeResolveImportRules($css, 0, $seen);

        $this->assertStringContainsString('h1', $result);
    }

    public function testResolveImportRulesDeduplicatesUrls(): void
    {
        $obj = $this->getInternalTestObject();
        $base = (string) \realpath(__DIR__ . '/fixtures/css/import/base.css');
        $css = '@import "' . $base . '";' . "\n" . '@import "' . $base . '";' . "\n" . 'h3{margin:0;}';
        $seen = [];

        $result = $obj->exposeResolveImportRules($css, 0, $seen);

        // File should appear exactly once despite two @import rules for the same URL
        $this->assertSame(1, \substr_count($result, 'h1'));
    }

    public function testResolveImportRulesStopsAtMaxDepth(): void
    {
        $obj = $this->getInternalTestObject();
        $base = (string) \realpath(__DIR__ . '/fixtures/css/import/base.css');
        $css = '@import "' . $base . '";' . "\n" . 'h3{margin:0;}';
        $seen = [];

        // At max depth the import should not be resolved
        $result = $obj->exposeResolveImportRules($css, 8, $seen);

        $this->assertStringNotContainsString('h1', $result);
        $this->assertStringContainsString('@import', $result);
    }

    public function testExtractCSSpropertiesResolvesImportBeforeParsing(): void
    {
        $obj = $this->getInternalTestObject();
        $base = (string) \realpath(__DIR__ . '/fixtures/css/import/base.css');
        $css = '@import "' . $base . '";' . "\n" . 'h3{margin:0;}';

        $result = $this->normalizeSelectorMap($obj->exposeExtractCSSproperties($css));

        $this->assertArrayHasKey('h1', $result);
        $this->assertArrayHasKey('h2', $result);
        $this->assertArrayHasKey('h3', $result);
    }

    public function testGetCSSArrayFromHTMLResolvesImportInStyleTag(): void
    {
        $obj = $this->getInternalTestObject();
        $base = (string) \realpath(__DIR__ . '/fixtures/css/import/base.css');
        $html = '<style>@import "' . $base . '"; h3{margin:0;}</style><p>ok</p>';

        $result = $this->normalizeSelectorMap($obj->exposeGetCSSArrayFromHTML($html));

        $this->assertArrayHasKey('h1', $result);
        $this->assertArrayHasKey('h3', $result);
    }

    /**
     * @param array<string, string> $styles
     *
     * @return array<string, string>
     */
    private function normalizeSelectorMap(array $styles): array
    {
        $normalized = [];
        foreach ($styles as $selector => $declarations) {
            // Remove specificity prefix (format: digits_digits or just digits followed by space)
            $pure = (string) \preg_replace('/^[\d_]+\s+/', '', $selector);
            $normalized[$pure] = $declarations;
        }

        return $normalized;
    }

    // --- isMediaPrintRelevant ---

    /** @return array<string, array{string, bool}> */
    public static function mediaRelevanceProvider(): array
    {
        return [
            'print' => ['print', true],
            'all' => ['all', true],
            'print and condition' => ['print and (min-width:600px)', true],
            'all and condition' => ['all and (orientation:portrait)', true],
            'feature only' => ['(max-width:800px)', true],
            'screen' => ['screen', false],
            'tv' => ['tv', false],
            'screen, print' => ['screen, print', true],
            'screen, all' => ['screen, all', true],
            'not screen' => ['not screen', true],
            'not print' => ['not print', false],
            'not all' => ['not all', false],
            'not tv' => ['not tv', true],
            'empty string' => ['', false],
        ];
    }

    #[DataProvider('mediaRelevanceProvider')]
    public function testIsMediaPrintRelevant(string $query, bool $expected): void
    {
        $obj = $this->getInternalTestObject();
        $this->assertSame($expected, $obj->exposeIsMediaPrintRelevant($query));
    }

    public function testTidyCSSKeepsPrintAndCondition(): void
    {
        $obj = $this->getInternalTestObject();
        $css = '@media print and (min-width:600px) { h1{color:red;} } @media screen{p{color:blue;}}';

        $out = $obj->exposeTidyCSS($css);

        $this->assertStringContainsString('h1{color:red;}', $out);
        $this->assertStringNotContainsString('p{color:blue;}', $out);
    }

    public function testTidyCSSKeepsCommaListWithPrint(): void
    {
        $obj = $this->getInternalTestObject();
        $css = '@media screen, print { h2{font-size:14pt;} }';

        $out = $obj->exposeTidyCSS($css);

        $this->assertStringContainsString('h2{font-size:14pt;}', $out);
    }

    public function testTidyCSSKeepsFeatureOnlyQuery(): void
    {
        $obj = $this->getInternalTestObject();
        $css = '@media (max-width:800px) { p{margin:0;} }';

        $out = $obj->exposeTidyCSS($css);

        $this->assertStringContainsString('p{margin:0;}', $out);
    }

    public function testTidyCSSDropsNotPrint(): void
    {
        $obj = $this->getInternalTestObject();
        $css = '@media not print { h3{color:green;} } body{color:black;}';

        $out = $obj->exposeTidyCSS($css);

        $this->assertStringNotContainsString('h3{color:green;}', $out);
        $this->assertStringContainsString('body{color:black;}', $out);
    }

    public function testTidyCSSKeepsNotScreen(): void
    {
        $obj = $this->getInternalTestObject();
        $css = '@media not screen { h4{color:navy;} }';

        $out = $obj->exposeTidyCSS($css);

        $this->assertStringContainsString('h4{color:navy;}', $out);
    }

    // --- normalizeCharset ---

    public function testNormalizeCharsetStripsUtf8Declaration(): void
    {
        $obj = $this->getInternalTestObject();
        $css = '@charset "UTF-8"; body { color:red; }';

        $out = $obj->exposeNormalizeCharset($css);

        $this->assertStringNotContainsString('@charset', $out);
        $this->assertStringContainsString('body', $out);
    }

    public function testNormalizeCharsetStripsUtf8DeclarationCaseInsensitive(): void
    {
        $obj = $this->getInternalTestObject();
        $css = '@charset "utf-8"; h1 { font-size:12pt; }';

        $out = $obj->exposeNormalizeCharset($css);

        $this->assertStringNotContainsString('@charset', $out);
        $this->assertStringContainsString('h1', $out);
    }

    public function testNormalizeCharsetStripsAsciiDeclaration(): void
    {
        $obj = $this->getInternalTestObject();
        $css = '@charset "US-ASCII"; p { margin:0; }';

        $out = $obj->exposeNormalizeCharset($css);

        $this->assertStringNotContainsString('@charset', $out);
        $this->assertStringContainsString('p', $out);
    }

    public function testNormalizeCharsetStripsWithLeadingBom(): void
    {
        $obj = $this->getInternalTestObject();
        $css = "\xEF\xBB\xBF" . '@charset "UTF-8"; h2 { color:blue; }';

        $out = $obj->exposeNormalizeCharset($css);

        $this->assertStringNotContainsString('@charset', $out);
        $this->assertStringContainsString('h2', $out);
        // BOM should also be stripped
        $this->assertStringNotContainsString("\xEF\xBB\xBF", $out);
    }

    public function testNormalizeCharsetPassesThroughWhenAbsent(): void
    {
        $obj = $this->getInternalTestObject();
        $css = 'body { color:red; }';

        $out = $obj->exposeNormalizeCharset($css);

        $this->assertSame($css, $out);
    }

    public function testNormalizeCharsetTranscodesIso88591(): void
    {
        $obj = $this->getInternalTestObject();
        // "résumé" in ISO-8859-1 encoding
        $iso = '@charset "ISO-8859-1"; ' . \mb_convert_encoding('p { content:"résumé"; }', 'ISO-8859-1', 'UTF-8');

        $out = $obj->exposeNormalizeCharset($iso);

        $this->assertStringNotContainsString('@charset', $out);
        // After transcoding the content should be valid UTF-8
        $this->assertTrue(\mb_check_encoding($out, 'UTF-8'));
        $this->assertStringContainsString('résumé', $out);
    }

    public function testExtractCSSpropertiesStripsCharset(): void
    {
        $obj = $this->getInternalTestObject();
        $css = '@charset "UTF-8"; h1 { color:red; } h2 { font-size:14pt; }';

        $result = $this->normalizeSelectorMap($obj->exposeExtractCSSproperties($css));

        $this->assertArrayHasKey('h1', $result);
        $this->assertArrayHasKey('h2', $result);
    }

    public function testResolveImportRulesStripsCharsetFromImportedFile(): void
    {
        $obj = $this->getInternalTestObject();
        $charsetFile = (string) \realpath(__DIR__ . '/fixtures/css/charset/with-charset.css');
        $css = '@import "' . $charsetFile . '"; h3{margin:0;}';
        $seen = [];

        $result = $obj->exposeResolveImportRules($css, 0, $seen);

        $this->assertStringNotContainsString('@charset', $result);
        $this->assertStringContainsString('h1', $result);
    }
}
