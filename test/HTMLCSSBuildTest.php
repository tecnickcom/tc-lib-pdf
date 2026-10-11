<?php

/**
 * HTMLCSSBuildTest.php
 *
 * @since     2026-10-11
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */

namespace Test;

/**
 * Selector matching while the HTML DOM is built with a stylesheet.
 *
 * @since     2026-10-11
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class HTMLCSSBuildTest extends TestUtil
{
    public static function setUpBeforeClass(): void
    {
        self::setUpFontsPath();
    }

    /**
     * Returns HTML with nested lists, tables and siblings of several types.
     */
    private function getStructuredBody(): string
    {
        return (
            '<div><p>a</p><p></p><span>s</span><p>b <b>x</b><i>y</i></p>'
            . '<ul><li>one</li><li>two<ol><li>n1</li><li>n2</li><li>n3</li></ol></li><li>three</li></ul>'
            . '<table border="1"><caption>c</caption><thead><tr><th>h1</th><th>h2</th></tr></thead>'
            . '<tr><td>a1</td><td>a2</td><td>a3</td></tr><tr><td>b1</td><td><table><tr><td>in</td></tr>'
            . '</table></td></tr></table><h2>t</h2><p lang="it">ciao</p></div>'
        );
    }

    /**
     * Returns a stylesheet using every structural pseudo-class and sibling combinator.
     */
    private function getStructuralStylesheet(): string
    {
        return (
            '<style>li:first-child { color: #ff0000; } li:last-child { font-weight: bold; } '
            . 'td:nth-child(2n+1) { background-color: #eeeeee; } li:nth-last-child(2) { font-style: italic; } '
            . 'td:only-child { color: #00ff00; } p:first-of-type { font-size: 9pt; } '
            . 'p:last-of-type { font-size: 11pt; } p:nth-of-type(2) { color: #0000ff; } '
            . 'p:empty { height: 2mm; } td + td { color: #333333; } h2 ~ p { color: #444444; } '
            . 'p:lang(it) { font-style: italic; }</style>'
        );
    }

    /** @throws \Throwable */
    public function testChildIndexMatchesTheDomScan(): void
    {
        $obj = new TestableHTMLCSSBuildProbe();
        $obj->font->insert($obj->pon, 'helvetica', '', 10);
        $obj->exposeGetHTMLDOM($this->getStructuralStylesheet() . $this->getStructuredBody());

        $this->assertGreaterThan(100, $obj->exposeIndexedLookups());
        $this->assertSame([], $obj->exposeChildKeyMismatches());
    }

    /**
     * Returns the minimum DOM build time (seconds) of three runs.
     *
     * @throws \Throwable
     */
    private function getBuildTime(TestableHTML $obj, string $html): float
    {
        $best = INF;
        for ($run = 0; $run < 3; ++$run) {
            $start = \hrtime(true);
            $obj->exposeGetHTMLDOM($html);
            $best = \min($best, (\hrtime(true) - $start) / 1e9);
        }

        return $best;
    }

    /**
     * Returns a table with the given number of rows of four cells.
     */
    private function getTable(int $rows): string
    {
        $html = '<table border="1" cellpadding="2">';
        for ($idx = 1; $idx <= $rows; ++$idx) {
            $html .= '<tr><td>Row ' . $idx . '</td><td>Lorem ipsum</td><td>dolor sit amet</td><td>1,00</td></tr>';
        }

        return $html . '</table>';
    }

    /** @throws \Throwable */
    public function testStructuralSelectorsKeepTheBuildTimeLinear(): void
    {
        $obj = new TestableHTML();
        $obj->font->insert($obj->pon, 'helvetica', '', 10);
        $css = '<style>td:last-child { color: #ff0000; } tr:nth-child(odd) { background-color: #eeeeee; }</style>';

        $small = $this->getBuildTime($obj, $css . $this->getTable(100));
        $large = $this->getBuildTime($obj, $css . $this->getTable(800));

        // Eight times the rows: a linear build takes about eight times longer, a
        // build that scans the DOM for every sibling lookup about 64 times.
        $this->assertLessThan(16.0, $large / $small);
    }

    /**
     * Sorts the keys of an array and of its nested arrays.
     *
     * @param array<array-key, mixed> $arr
     *
     * @return array<array-key, mixed>
     */
    private function sortKeys(array $arr): array
    {
        \ksort($arr);

        return \array_map(fn(mixed $val): mixed => \is_array($val) ? $this->sortKeys($val) : $val, $arr);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function streamingStylesheetProvider(): array
    {
        return [
            'type, class and id' => [
                '<style>p { color: #ff0000; } .x { font-weight: bold; } #y { font-size: 9pt; } '
                    . 'td { border: 1px solid #000000; padding: 1mm; } th { background-color: #cccccc; }</style>',
            ],
            'preceding siblings and ancestors' => [
                '<style>li:first-child { color: #ff0000; } td:nth-child(2n+1) { background-color: #eeeeee; } '
                    . 'p:first-of-type { font-size: 9pt; } p:nth-of-type(2) { color: #0000ff; } '
                    . 'td + td { color: #333333; } h2 ~ p { color: #444444; } div p b { font-style: italic; } '
                    . 'ul > li { margin-left: 2mm; } p:lang(it) { font-style: italic; }</style>',
            ],
            'attributes and pseudo-elements' => [
                '<style>p[lang] { color: #00ff00; } table[border="1"] { font-size: 8pt; } '
                    . 'p::before { content: "> "; } li::marker { color: #ff0000; } a:link { color: #0000ff; }</style>',
            ],
        ];
    }

    /** @throws \Throwable */
    #[\PHPUnit\Framework\Attributes\DataProvider('streamingStylesheetProvider')]
    public function testStylesheetWithoutFinalTreeSelectorsSkipsTheFinalTreePass(string $css): void
    {
        $obj = new TestableHTMLCSSBuildProbe();
        $obj->font->insert($obj->pon, 'helvetica', '', 10);
        $obj->addPage();
        $html = $css . '<p class="x" id="y">z</p>' . $this->getStructuredBody();

        $streamed = $obj->exposeGetHTMLDOM($html);
        $this->assertSame(0, $obj->exposeFinalTreePasses());

        $this->setObjectProperty($obj, 'htmlAlwaysRecomputeCSS', true);
        $recomputed = $obj->exposeGetHTMLDOM($html);
        $this->assertSame(1, $obj->exposeFinalTreePasses());

        $this->assertSame(\array_keys($recomputed), \array_keys($streamed));
        foreach ($recomputed as $key => $node) {
            $this->assertSame($this->sortKeys($node), $this->sortKeys($streamed[$key] ?? []), 'DOM node ' . $key);
        }
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function finalTreeSelectorProvider(): array
    {
        return [
            'last-child' => ['td:last-child'],
            'nth-last-child' => ['td:nth-last-child(1)'],
            'only-child' => ['tr:only-child td'],
            'last-of-type' => ['td:last-of-type'],
            'empty' => ['td:empty'],
            'negated' => ['td:not(:last-child)'],
        ];
    }

    /** @throws \Throwable */
    #[\PHPUnit\Framework\Attributes\DataProvider('finalTreeSelectorProvider')]
    public function testFinalTreeSelectorRunsTheFinalTreePass(string $selector): void
    {
        $obj = new TestableHTMLCSSBuildProbe();
        $obj->font->insert($obj->pon, 'helvetica', '', 10);
        $obj->exposeGetHTMLDOM(
            '<style>' . $selector . ' { color: #ff0000; }</style><table><tr><td>a</td></tr></table>',
        );

        $this->assertSame(1, $obj->exposeFinalTreePasses());
    }

    /** @throws \Throwable */
    public function testLastChildSelectorMatchesTheLastCell(): void
    {
        $obj = new TestableHTML();
        $obj->font->insert($obj->pon, 'helvetica', '', 10);
        $dom = $obj->exposeGetHTMLDOM(
            '<style>td:last-child { font-weight: bold; }</style><table><tr><td>a</td><td>b</td><td>c</td></tr></table>',
        );

        $cells = [];
        foreach ($dom as $node) {
            if (!$node['tag'] || !$node['opening'] || $node['value'] !== 'td') {
                continue;
            }

            $cells[] = $node['fontstyle'];
        }

        $this->assertSame(['', '', 'B'], $cells);
    }

    /** @throws \Throwable */
    public function testRuleIndexMatchesTheSameRulesAsAFullScan(): void
    {
        $css =
            '<style>* { letter-spacing: 0; } p { color: #ff0000; } P.UP { font-size: 9pt; } .x { font-weight: bold; } '
            . '.x.y { color: #00ff00; } #Y { font-size: 11pt; } div .x b { font-style: italic; } '
            . 'ul > li { margin-left: 2mm; } li:first-child { color: #ff0000; } td + td { color: #333333; } '
            . 'h2 ~ p { color: #444444; } p[lang] { color: #00ff00; } [lang="it"] { font-style: italic; } '
            . 'td:not(.x) { padding: 1mm; } .\31 a { color: #0000ff; } li::marker { color: #ff0000; } '
            . 'p.x::before { content: "[ "; } .x::after { content: " ]"; } table td.y { font-size: 8pt; } '
            . 'a:link { color: #0000ff; } :lang(it) { font-weight: bold; }</style>';
        $html =
            $css
            . '<p class="x y" id="y">a <b>b</b></p><p class="up">u</p><p class="1a">e</p>'
            . '<div><span class="x"><b>n</b></span></div><table><tr><td class="y">c</td><td>d</td></tr></table>'
            . $this->getStructuredBody();

        $obj = new TestableHTMLCSSBuildProbe();
        $obj->font->insert($obj->pon, 'helvetica', '', 10);
        $obj->addPage();
        $indexed = $obj->exposeGetHTMLDOM($html);
        $obj->fullRuleScan = true;
        $scanned = $obj->exposeGetHTMLDOM($html);

        $this->assertSame($scanned, $indexed);
        $matched = 0;
        foreach ($indexed as $node) {
            if ($node['csssel'] === []) {
                continue;
            }

            ++$matched;
        }

        $this->assertGreaterThan(20, $matched);
    }

    /**
     * Returns the font style of every opening node with the given tag.
     *
     * @return list<string>
     *
     * @throws \Throwable
     */
    private function getTagFontStyles(string $html, string $tag): array
    {
        $obj = new TestableHTML();
        $obj->font->insert($obj->pon, 'helvetica', '', 10);
        $styles = [];
        foreach ($obj->exposeGetHTMLDOM($html) as $node) {
            if (!$node['tag'] || !$node['opening'] || $node['value'] !== $tag) {
                continue;
            }

            $styles[] = $node['fontstyle'];
        }

        return $styles;
    }

    /** @throws \Throwable */
    public function testSpanAttributeSelectorMatchesOnlyDeclaredSpans(): void
    {
        $cells = '<table><tr><td colspan="1">a</td><td>b</td><td rowspan="1">c</td></tr></table>';
        $streaming = '<style>td[colspan="1"], td[rowspan] { font-weight: bold; }</style>';
        $finalTree = '<style>td[colspan="1"], td[rowspan] { font-weight: bold; } td:last-child { color: #ff0000; }</style>';

        $this->assertSame(['B', '', 'B'], $this->getTagFontStyles($streaming . $cells, 'td'));
        $this->assertSame(['B', '', 'B'], $this->getTagFontStyles($finalTree . $cells, 'td'));
    }

    /** @throws \Throwable */
    public function testStyleBlockContainingLessThanIsApplied(): void
    {
        $obj = new TestableHTML();
        $obj->font->insert($obj->pon, 'helvetica', '', 10);
        $dom = $obj->exposeGetHTMLDOM(
            '<style>p { font-weight: bold; } p::after { content: " <"; } /* a < b */</style><p>a</p>',
        );

        $text = '';
        $pstyles = [];
        foreach ($dom as $node) {
            if (!$node['tag']) {
                $text .= $node['value'];
                continue;
            }

            if ($node['opening'] && $node['value'] === 'p') {
                $pstyles[] = $node['fontstyle'];
            }
        }

        $this->assertSame(['B'], $pstyles);
        $this->assertStringNotContainsString('font-weight', $text);
    }

    /** @throws \Throwable */
    public function testStyleBlockInsideCommentDelimitersIsApplied(): void
    {
        $this->assertSame(
            ['B'],
            $this->getTagFontStyles('<style><!-- p { font-weight: bold; } --></style><p>a</p>', 'p'),
        );
    }
}
