<?php

/**
 * HTMLPageFlowTest.php
 *
 * @since     2026-10-10
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

/**
 * HTML content flowing over pages, table painting order and the state kept
 * between HTML calls.
 *
 * @since     2026-10-10
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class HTMLPageFlowTest extends TestUtil
{
    public static function setUpBeforeClass(): void
    {
        self::setUpFontsPath();
    }

    /**
     * Returns an uncompressed document with helvetica 10 and one page.
     *
     * @throws \Throwable
     */
    private function getDocument(): \Com\Tecnick\Pdf\Tcpdf
    {
        $pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
        /** @var int $pon */
        $pon = $this->getObjectProperty($pdf, 'pon');
        $font = $pdf->font->insert($pon, 'helvetica', '', 10);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);

        return $pdf;
    }

    /**
     * Returns the content stream of each page.
     *
     * @return list<string>
     */
    private function getPageContents(\Com\Tecnick\Pdf\Tcpdf $pdf): array
    {
        $out = [];
        foreach ($pdf->page->getPages() as $page) {
            $out[] = \implode("\n", $page['content']);
        }

        return $out;
    }

    /**
     * Returns the ordinate (points) of every text shown with Td, keyed by text, in
     * stream order.
     *
     * @return list<array{0: string, 1: float}>
     */
    private function getTextOrdinates(string $content): array
    {
        $matches = [];
        \preg_match_all('/ ([-0-9.]+) ([-0-9.]+) Td \(((?:\\\\.|[^\\\\)])*)\) Tj/', $content, $matches, PREG_SET_ORDER);
        $out = [];
        foreach ($matches as $match) {
            $out[] = [$match[3] ?? '', \floatval($match[2] ?? 0.0)];
        }

        return $out;
    }

    /**
     * Returns the content stream of the given page, or an empty string.
     */
    private function getPageContent(\Com\Tecnick\Pdf\Tcpdf $pdf, int $idx): string
    {
        return $this->getPageContents($pdf)[$idx] ?? '';
    }

    /**
     * Returns the text and the ordinate at the given position of a list.
     *
     * @param list<array{0: string, 1: float}> $list
     *
     * @return array{0: string, 1: float}
     */
    private function getTextAt(array $list, int $idx): array
    {
        return $list[$idx] ?? ['', 0.0];
    }

    /**
     * Returns the 120 numbered lines used by the page flow cases.
     */
    private function getNumberedLines(int $num = 120): string
    {
        $lines = [];
        for ($idx = 1; $idx <= $num; ++$idx) {
            $lines[] = 'Line ' . $idx;
        }

        return \implode('<br />', $lines);
    }

    /** @throws \Throwable */
    public function testGetHTMLCellKeepsContentOnTheCurrentPage(): void
    {
        $pdf = $this->getDocument();
        $pid = $pdf->page->getPageId();

        $out = $pdf->getHTMLCell('<p>' . $this->getNumberedLines() . '</p>', 15, 20, 180);

        $this->assertSame($pid, $pdf->page->getPageId());
        $this->assertCount(1, $pdf->page->getPages());
        $lines = $this->getTextOrdinates($out);
        $this->assertCount(120, $lines);
        for ($idx = 1; $idx < 120; ++$idx) {
            $this->assertLessThan($this->getTextAt($lines, $idx - 1)[1], $this->getTextAt($lines, $idx)[1]);
        }
    }

    /** @throws \Throwable */
    public function testGetHTMLCellIgnoresRequestedPageBreaks(): void
    {
        $pdf = $this->getDocument();

        $pdf->getHTMLCell(
            '<p>A</p><p style="page-break-before:always">B</p><p style="page-break-after:always">C</p>'
            . '<tcpdf method="pagebreak" /><p>D</p>',
            15,
            20,
            180,
        );

        $this->assertCount(1, $pdf->page->getPages());
    }

    /** @throws \Throwable */
    public function testExplicitRowPageBreakInStyledTableKeepsRowsOnTheirPages(): void
    {
        $pdf = $this->getDocument();
        $rows = '';
        for ($idx = 1; $idx <= 6; ++$idx) {
            $rows .=
                '<tr'
                . ($idx === 4 ? ' style="page-break-before:always"' : '')
                . '><td>row '
                . $idx
                . '</td><td>x</td></tr>';
        }

        $pdf->addHTMLCell(
            '<table border="1" cellpadding="2" style="background-color:#ddeeff">' . $rows . '</table>',
            15,
            20,
            180,
        );

        $pages = $this->getPageContents($pdf);
        $this->assertCount(2, $pages);
        $first = \array_column($this->getTextOrdinates($this->getPageContent($pdf, 0)), 0);
        $second = \array_column($this->getTextOrdinates($this->getPageContent($pdf, 1)), 0);
        $this->assertSame(['row 1', 'x', 'row 2', 'x', 'row 3', 'x'], $first);
        $this->assertSame(['row 4', 'x', 'row 5', 'x', 'row 6', 'x'], $second);
    }

    /** @throws \Throwable */
    public function testTcpdfPageBreakContinuesAtThePageTop(): void
    {
        $pdf = $this->getDocument();

        $pdf->addHTMLCell('<p>first</p><tcpdf method="pagebreak" /><p>AfterBreak</p>', 15, 100, 180);

        $pages = $this->getPageContents($pdf);
        $this->assertCount(2, $pages);
        $first = $this->getTextOrdinates($this->getPageContent($pdf, 0));
        $after = $this->getTextOrdinates($this->getPageContent($pdf, 1));
        $this->assertSame('first', $this->getTextAt($first, 0)[0]);
        $this->assertSame('AfterBreak', $this->getTextAt($after, 0)[0]);
        $this->assertGreaterThan($this->getTextAt($first, 0)[1], $this->getTextAt($after, 0)[1]);
    }

    /** @throws \Throwable */
    public function testExplicitPageBreakContinuesAtTheRegionTop(): void
    {
        $pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
        /** @var int $pon */
        $pon = $this->getObjectProperty($pdf, 'pon');
        $font = $pdf->font->insert($pon, 'helvetica', '', 10);
        $pdf->addPage(['margin' => ['PT' => 10, 'PB' => 10, 'CT' => 10, 'CB' => 10]]);
        $pdf->page->addContent($font['out']);

        $pdf->addHTMLCell('<p>first</p><p style="page-break-before:always">second</p>', 15, 100, 180);

        $pages = $this->getPageContents($pdf);
        $this->assertCount(2, $pages);
        $second = $this->getTextOrdinates($this->getPageContent($pdf, 1));
        $this->assertSame('second', $this->getTextAt($second, 0)[0]);
        // The baseline of the first line sits right below the 10 mm top margin.
        $pageh = $pdf->page->getPage()['height'];
        $top = $pdf->toPoints($pageh - 10.0);
        $this->assertLessThan($top, $this->getTextAt($second, 0)[1]);
        $this->assertGreaterThan($top - 15.0, $this->getTextAt($second, 0)[1]);
    }

    /** @throws \Throwable */
    public function testTcpdfPageBreakWithModeBreaksOnce(): void
    {
        $pdf = $this->getDocument();

        $pdf->addHTMLCell('<p>first</p><tcpdf method="pagebreak" pagebreak="true" /><p>AfterBreak</p>', 15, 100, 180);

        $pages = $this->getPageContents($pdf);
        $this->assertCount(2, $pages);
        $this->assertStringContainsString('(AfterBreak)', $this->getPageContent($pdf, 1));
    }

    /** @throws \Throwable */
    public function testTableBordersArePaintedAfterTheBackgroundsOfLaterRows(): void
    {
        $pdf = $this->getDocument();

        $pdf->addHTMLCell(
            '<table cellpadding="2">'
            . '<tr><td style="border-bottom: 2px solid #ff0000;">row 1</td></tr>'
            . '<tr><td style="background-color: #cccccc;">row 2</td></tr>'
            . '</table>',
            15,
            20,
            180,
        );

        $content = $this->getPageContent($pdf, 0);
        $fill = \strpos($content, '0.800000 0.800000 0.800000 rg');
        $stroke = \strpos($content, '1.000000 0.000000 0.000000 RG');
        $this->assertIsInt($fill);
        $this->assertIsInt($stroke);
        $this->assertGreaterThan($fill, $stroke);
    }

    /** @throws \Throwable */
    public function testTableBordersStayOnThePageOfTheirRows(): void
    {
        $pdf = $this->getDocument();
        $rows = '';
        for ($idx = 1; $idx <= 80; ++$idx) {
            $rows .= '<tr><td style="background-color:#cccccc;border:1px solid #ff0000">row ' . $idx . '</td></tr>';
        }

        $pdf->addHTMLCell('<table cellpadding="2">' . $rows . '</table>', 15, 20, 180);

        $pages = $this->getPageContents($pdf);
        $this->assertCount(2, $pages);
        foreach ($pages as $content) {
            $rowsOnPage = \substr_count($content, ') Tj');
            $this->assertGreaterThan(0, $rowsOnPage);
            // One fill and one stroked frame per row, every frame after the last fill.
            $this->assertSame($rowsOnPage, \substr_count($content, '0.800000 0.800000 0.800000 rg'));
            $this->assertSame($rowsOnPage, \substr_count($content, '1.000000 0.000000 0.000000 RG'));
            $lastFill = \strrpos($content, '0.800000 0.800000 0.800000 rg');
            $firstStroke = \strpos($content, '1.000000 0.000000 0.000000 RG');
            $this->assertIsInt($lastFill);
            $this->assertIsInt($firstStroke);
            $this->assertGreaterThan($lastFill, $firstStroke);
        }
    }

    /** @throws \Throwable */
    public function testBoundingBoxesKeepOnlyTheLastEntry(): void
    {
        $pdf = $this->getDocument();
        $rows = \str_repeat('<tr><td>a</td><td><b>b</b></td></tr>', 50);

        $pdf->addHTMLCell('<table border="1">' . $rows . '</table>', 15, 20, 180);

        foreach (['bbox', 'textbbox', 'cellbbox'] as $name) {
            /** @var array<int, array{x: float, y: float, w: float, h: float}> $list */
            $list = $this->getObjectProperty($pdf, $name);
            $this->assertCount(1, $list, $name);
        }

        $this->assertGreaterThan(0.0, $pdf->getLastTextBBox()['w']);
    }

    /** @throws \Throwable */
    public function testHTMLRenderingKeepsTheFontStackDepth(): void
    {
        $pdf = $this->getDocument();
        $depth = $pdf->font->getCurrentFont()['idx'];
        $html =
            '<p>plain <b>bold</b> <i>italic</i> <span style="font-size:14pt">big</span></p>'
            . '<ul><li>one</li><li style="list-style-type:disc">two</li></ul>'
            . '<table border="1"><caption>cap</caption><thead><tr><th>H</th></tr></thead>';

        foreach ([10, 200] as $num) {
            $pdf->addHTMLCell($html . \str_repeat('<tr><td>a <b>b</b></td></tr>', $num) . '</table>', 15, 20, 180);
            $this->assertSame($depth, $pdf->font->getCurrentFont()['idx']);
            $pdf->getHTMLCell($html . '</table>', 15, 20, 180);
            $this->assertSame($depth, $pdf->font->getCurrentFont()['idx']);
        }

        $this->assertSame('helvetica', $pdf->font->getCurrentFont()['key']);
        $this->assertEqualsWithDelta(10.0, $pdf->font->getCurrentFont()['size'], 0.001);
    }

    /** @throws \Throwable */
    public function testLastHTMLCellEndContinuesBelowThePreviousBlock(): void
    {
        $pdf = $this->getDocument();
        $this->assertSame(-1, $pdf->getLastHTMLCellEnd()['page']);

        $pdf->addHTMLCell('<table border="1" cellpadding="2"><tr><td>chunk 1</td></tr></table>', 15, 20, 180);
        $end = $pdf->getLastHTMLCellEnd();
        $this->assertSame($pdf->page->getPageId(), $end['page']);
        $this->assertGreaterThan(20.0, $end['y']);

        $pdf->addHTMLCell('<table border="1" cellpadding="2"><tr><td>chunk 2</td></tr></table>', 15, $end['y'], 180);

        $lines = $this->getTextOrdinates($this->getPageContent($pdf, 0));
        $this->assertSame('chunk 1', $this->getTextAt($lines, 0)[0]);
        $this->assertSame('chunk 2', $this->getTextAt($lines, 1)[0]);
        // One row below the other, without overlap: the row step is the row height.
        $this->assertEqualsWithDelta(14.56, $this->getTextAt($lines, 0)[1] - $this->getTextAt($lines, 1)[1], 0.01);

        $pdf->addHTMLCell('<p>text</p>', 15, 50, 180, 40);
        $this->assertEqualsWithDelta(90.0, $pdf->getLastHTMLCellEnd()['y'], 0.001);
    }

    /**
     * Returns the texts of each page, in stream order.
     *
     * @return list<list<string>>
     */
    private function getPageTexts(\Com\Tecnick\Pdf\Tcpdf $pdf): array
    {
        $out = [];
        foreach ($this->getPageContents($pdf) as $content) {
            $texts = [];
            foreach ($this->getTextOrdinates($content) as $line) {
                $texts[] = \trim($line[0]);
            }

            $out[] = $texts;
        }

        return $out;
    }

    /**
     * Returns the numbered lines from start to end joined with <br />.
     */
    private function getLines(string $prefix, int $num): string
    {
        $lines = [];
        for ($idx = 1; $idx <= $num; ++$idx) {
            $lines[] = $prefix . ' ' . $idx;
        }

        return \implode('<br />', $lines);
    }

    /** @throws \Throwable */
    public function testRowTallerThanAPageIsSplitOverThePages(): void
    {
        $pdf = $this->getDocument();

        $pdf->addHTMLCell(
            '<table border="1" cellpadding="2"><tr><td>' . $this->getLines('Line', 120) . '</td></tr></table>',
            15,
            20,
            180,
        );

        $pages = $this->getPageTexts($pdf);
        $this->assertCount(2, $pages);
        $this->assertSame('Line 1', $pages[0][0] ?? '');
        $all = \array_merge(...$pages);
        $this->assertSame(\explode('<br />', $this->getLines('Line', 120)), $all);
        foreach ($this->getPageContents($pdf) as $content) {
            foreach ($this->getTextOrdinates($content) as $line) {
                $this->assertGreaterThan(0.0, $line[1]);
            }
        }

        // The first part has no bottom edge and the second part no top edge.
        $first = $this->getPageContent($pdf, 0);
        $second = $this->getPageContent($pdf, 1);
        $this->assertStringNotContainsString("552.755906 0.000000 m\n42.519685 0.000000 l", $first);
        $this->assertStringNotContainsString("42.519685 841.890000 m\n552.755906 841.890000 l", $second);
    }

    /** @throws \Throwable */
    public function testCellsOfASplitRowContinueOnTheSamePages(): void
    {
        $pdf = $this->getDocument();

        $pdf->addHTMLCell(
            '<table border="1" cellpadding="2"><thead><tr><th>Head</th><th>Head</th></tr></thead>'
            . '<tr><td style="background-color:#ffeecc">'
            . $this->getLines('A', 40)
            . '</td><td style="background-color:#ccddff">'
            . $this->getLines('B', 120)
            . '</td></tr><tr><td>after</td><td>row</td></tr></table>',
            15,
            20,
            180,
        );

        $pages = $this->getPageTexts($pdf);
        $this->assertCount(2, $pages);
        $this->assertSame(['Head', 'Head'], \array_slice($pages[1] ?? [], 0, 2));
        $this->assertContains('A 40', $pages[0] ?? []);
        $this->assertContains('B 120', $pages[1] ?? []);
        $this->assertSame(['after', 'row'], \array_slice($pages[1] ?? [], -2));
        // Both cell backgrounds are on both pages.
        foreach ($this->getPageContents($pdf) as $content) {
            $this->assertStringContainsString('0.800000 0.866667 1.000000 rg', $content);
        }
    }

    /** @throws \Throwable */
    public function testRowFittingAnEmptyPageMovesWhole(): void
    {
        $pdf = $this->getDocument();

        $pdf->addHTMLCell(
            '<table border="1" cellpadding="2"><tr><td>' . $this->getLines('A', 60) . '</td></tr></table>',
            15,
            200,
            180,
        );

        $pages = $this->getPageTexts($pdf);
        $this->assertCount(2, $pages);
        $this->assertSame([], $pages[0] ?? []);
        $this->assertCount(60, $pages[1] ?? []);
    }

    /** @throws \Throwable */
    public function testKeepTogetherBlockTallerThanAPageStartsInPlace(): void
    {
        $pdf = $this->getDocument();

        $pdf->addHTMLCell(
            '<table border="1" cellpadding="2" nobr="true"><tr><td>' . $this->getLines('A', 120) . '</td></tr></table>',
            15,
            20,
            180,
        );

        $pages = $this->getPageTexts($pdf);
        $this->assertCount(2, $pages);
        $this->assertSame('A 1', $pages[0][0] ?? '');
    }

    /** @throws \Throwable */
    public function testLinkInTheContinuationOfASplitRowIsOnItsPage(): void
    {
        $pdf = $this->getDocument();

        $pdf->addHTMLCell(
            '<table border="1" cellpadding="2"><tr><td>'
            . $this->getLines('A', 100)
            . '<br /><a href="https://tcpdf.org">link</a></td></tr></table>',
            15,
            20,
            180,
        );

        $pages = $pdf->page->getPages();
        $this->assertCount(2, $pages);
        $this->assertSame([], $pages[0]['annotrefs'] ?? []);
        $this->assertNotSame([], $pages[1]['annotrefs'] ?? []);
    }

    /**
     * Returns a table whose first cell spans the given number of rows.
     */
    private function getRowspanTable(int $num): string
    {
        $rows = '';
        for ($idx = 1; $idx <= $num; ++$idx) {
            $rows .=
                '<tr>'
                . ($idx === 1 ? '<td rowspan="' . $num . '" style="background-color:#ffcc00">SPAN</td>' : '')
                . '<td>row '
                . $idx
                . '<br />a<br />b<br />c<br />d</td></tr>';
        }

        return '<table border="1" cellpadding="2">' . $rows . '<tr><td>last</td><td>row</td></tr></table>';
    }

    /** @throws \Throwable */
    public function testRowsSpannedByATallCellStayWhenTheyFit(): void
    {
        $pdf = $this->getDocument();
        $html =
            '<table border="1" cellpadding="2"><tr><td rowspan="4">S1<br />S2<br />S3<br />S4<br />S5<br />S6'
            . '<br />S7<br />S8</td><td>r1<br />a</td></tr><tr><td>r2<br />a</td></tr><tr><td>r3<br />a</td></tr>'
            . '<tr><td>r4<br />a</td></tr></table>';

        // The spanning cell is as tall as the four rows it covers: the rows fit below 245 mm.
        $pdf->addHTMLCell($html, 15, 245, 180);

        $pages = $this->getPageTexts($pdf);
        $this->assertCount(1, $pages);
        $this->assertContains('S8', $pages[0] ?? []);
        $this->assertContains('r4', $pages[0] ?? []);
    }

    /** @throws \Throwable */
    public function testRowsSpannedByACellMoveTogether(): void
    {
        $pdf = $this->getDocument();

        $pdf->addHTMLCell($this->getRowspanTable(6), 15, 250, 180);

        $pages = $this->getPageTexts($pdf);
        $this->assertCount(2, $pages);
        $this->assertSame([], $pages[0] ?? []);
        $this->assertContains('SPAN', $pages[1] ?? []);
        $this->assertStringContainsString('1.000000 0.800000 0.000000 rg', $this->getPageContent($pdf, 1));
    }

    /** @throws \Throwable */
    public function testCellSpanningRowsOverPagesContinuesOnEachPage(): void
    {
        $pdf = $this->getDocument();

        $pdf->addHTMLCell($this->getRowspanTable(40), 15, 20, 180);

        $pages = $this->getPageContents($pdf);
        $this->assertGreaterThan(2, \count($pages));
        $this->assertStringContainsString('(SPAN)', $pages[0] ?? '');
        foreach ($pages as $content) {
            $this->assertStringContainsString('1.000000 0.800000 0.000000 rg', $content);
        }

        $texts = \array_merge(...$this->getPageTexts($pdf));
        for ($idx = 1; $idx <= 40; ++$idx) {
            $this->assertContains('row ' . $idx, $texts);
        }
    }

    /**
     * Returns a table of four columns with the given number of rows.
     */
    private function getTableRows(int $num): string
    {
        $html = '<table border="1" cellpadding="2">';
        for ($idx = 1; $idx <= $num; ++$idx) {
            $html .=
                '<tr><td class="c1">Row ' . $idx . '</td><td>Lorem ipsum</td><td>dolor sit amet</td><td>1,00</td></tr>';
        }

        return $html . '</table>';
    }

    /**
     * Returns a stylesheet of 41 rules.
     */
    private function getStylesheet(): string
    {
        $css = '<style>';
        for ($idx = 0; $idx < 40; ++$idx) {
            $css .= '.c' . $idx . ' { color: #123456; font-weight: bold; padding: 1px 2px; } ';
        }

        return $css . 'td { border: 1px solid #000; }</style>';
    }

    /**
     * Returns a test object with helvetica 10 and one page.
     *
     * @throws \Throwable
     */
    private function getTestableDocument(): TestableHTML
    {
        $obj = new TestableHTML();
        $font = $obj->font->insert($obj->pon, 'helvetica', '', 10);
        $obj->addPage();
        $obj->page->addContent($font['out']);

        return $obj;
    }

    /**
     * Returns the memory used by the stored DOM of the given HTML.
     *
     * @throws \Throwable
     */
    private function getStoredDomMemory(TestableHTML $obj, string $html, int &$count): int
    {
        \gc_collect_cycles();
        $before = \memory_get_usage();
        $dom = $obj->exposeGetHTMLStoredDOM($html);
        $used = \memory_get_usage() - $before;
        $count = \count($dom);

        return $used;
    }

    /** @throws \Throwable */
    public function testTableDomDoesNotKeepCopiesOfRepeatedValues(): void
    {
        $count = 0;
        $used = $this->getStoredDomMemory($this->getTestableDocument(), $this->getTableRows(200), $count);

        // 14 nodes per row (no injected node per cell) and the document nodes.
        $this->assertSame(2807, $count);
        // About 19 KB per row of four cells; a full style copy on text or closing-tag
        // nodes, a copy of the repeated arrays of every cell, or an injected node per
        // cell exceeds the bound.
        $this->assertLessThan(200 * 22 * 1024, $used);
    }

    /** @throws \Throwable */
    public function testTableDomWithStylesheetDoesNotKeepCopiesOfRepeatedValues(): void
    {
        $count = 0;
        $used = $this->getStoredDomMemory(
            $this->getTestableDocument(),
            $this->getStylesheet() . $this->getTableRows(200),
            $count,
        );

        // The cells keep neither a copy of the stylesheet nor of their computed style arrays.
        $this->assertLessThan(200 * 22 * 1024, $used);
    }

    /** @throws \Throwable */
    public function testTableCellContentHoldsTheCellHtmlOnly(): void
    {
        $obj = $this->getTestableDocument();
        $html = $this->getStylesheet() . '<table><tr><td class="c1">a <b>b</b></td><td></td><td>c</tr></table>';
        $dom = $obj->exposeGetHTMLStoredDOM($html);

        $contents = [];
        foreach (\array_keys($dom) as $key) {
            $node = $obj->exposeGetHTMLDOMNode($dom, $key);
            if ($node === null || !$node['opening'] || $node['value'] !== 'td') {
                continue;
            }

            $contents[] = $node['content'];
        }

        // A closed cell ends with its end tag; the unclosed cell has no content.
        $this->assertSame(['a <b>b</b></td>', '</td>', ''], $contents);
    }

    /** @throws \Throwable */
    public function testTextAndClosingTagNodesAreStoredSlim(): void
    {
        $obj = new TestableHTML();
        $dom = $obj->exposeGetHTMLStoredDOM('<p style="color:red">a <b>b</b></p>');

        $text = 0;
        $closing = 0;
        foreach ($dom as $node) {
            if ($node['tag'] && $node['opening']) {
                $this->assertArrayNotHasKey('slim', $node);
                continue;
            }

            if (!isset($node['slim'])) {
                continue;
            }

            if ($node['tag']) {
                ++$closing;
                $this->assertArrayHasKey('stylesrc', $node);
                continue;
            }

            ++$text;
            $this->assertSame(['slim', 'tag', 'opening', 'value', 'parent', 'elkey'], \array_keys($node));
        }

        $this->assertSame(2, $text);
        $this->assertGreaterThanOrEqual(2, $closing);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function slimDomHtmlProvider(): array
    {
        $body =
            '<div style="text-align:center;color:#336699;line-height:14pt">A <b>bold <i>both</i></b> and '
            . '<span style="font-size:14pt;text-transform:uppercase">big</span> text<br/>'
            . '<ul><li>one</li><li>two <ol><li>n1</li></ol></li></ul><p dir="rtl">שלום <b>עולם</b></p>'
            . '<table border="1"><caption>cap <b>x</b></caption><thead><tr><th>H</th></tr></thead>'
            . '<tr><td style="white-space:nowrap">a b</td></tr></table>'
            . '<blockquote>q</blockquote><h1>T</h1><pre>x  y</pre><font color="red" size="2">f</font>'
            . '<p style="white-space:pre-line">l1'
            . "\n"
            . 'l2</p></div>';

        return [
            'no stylesheet' => [$body],
            'stylesheet' => [
                '<style>p { color: red; } .x b { font-size: 9pt; } li:last-child { font-style: italic; } '
                    . 'td { word-break: break-all; }</style>'
                    . $body
                    . '<p class="x">p <b>b</b> <span dir="rtl">r</span></p>',
            ],
        ];
    }

    /** @throws \Throwable */
    #[\PHPUnit\Framework\Attributes\DataProvider('slimDomHtmlProvider')]
    public function testSlimDomNodesHaveTheStyleOfFullNodes(string $html): void
    {
        $obj = $this->getTestableDocument();
        $full = $obj->exposeGetHTMLStoredDOM($html, false);
        $slim = $obj->exposeGetHTMLStoredDOM($html);

        $this->assertSame(\array_keys($full), \array_keys($slim));
        $numslim = 0;
        foreach ($full as $key => $node) {
            if (isset($slim[$key]['slim'])) {
                ++$numslim;
            }

            $view = $obj->exposeGetHTMLDOMNode($slim, $key);
            $this->assertNotNull($view);
            \ksort($node);
            \ksort($view);
            $this->assertSame($node, $view, 'DOM node ' . $key);
        }

        $this->assertGreaterThan(40, $numslim);
    }

    /**
     * Renders the text node of a preformatted block split at its line break.
     *
     * @return array{out: string, before: mixed, after: mixed}
     *
     * @throws \Throwable
     */
    private function renderSplitTextNode(bool $slim): array
    {
        $obj = $this->getTestableDocument();
        $obj->exposeInitHTMLCellContext(0, 0, 150, 60);
        $dom = $obj->exposeGetHTMLStoredDOM('<pre>one' . "\n" . 'two</pre>', $slim);

        $key = -1;
        foreach ($dom as $idx => $node) {
            if ($node['tag']) {
                continue;
            }

            $key = $idx;
        }

        $tpx = 0.0;
        $tpy = 0.0;
        $tpw = 150.0;
        $tph = 60.0;
        $result = $obj->exposeParseHTMLTextInDom($dom, $key, $tpx, $tpy, $tpw, $tph);

        return ['out' => $result['out'], 'before' => $dom[$key] ?? null, 'after' => $result['dom'][$key] ?? null];
    }

    /** @throws \Throwable */
    public function testSplitTextNodeStaysSlim(): void
    {
        $full = $this->renderSplitTextNode(false);
        $slim = $this->renderSplitTextNode(true);

        $this->assertStringContainsString('(two) Tj', $slim['out']);
        $this->assertSame($full['out'], $slim['out']);
        $this->assertIsArray($slim['before']);
        $this->assertArrayHasKey('slim', $slim['before']);
        $this->assertSame($slim['before'], $slim['after']);
    }

    /** @throws \Throwable */
    public function testRenderingKeepsTheInheritedPropertiesOfOpeningNodes(): void
    {
        $obj = new TestableHTMLDOMProbe();
        $font = $obj->font->insert($obj->pon, 'helvetica', '', 10);
        $obj->addPage();
        $obj->page->addContent($font['out']);
        $html =
            '<p>a <span color="#ff0000" bgcolor="#00ff00">red</span></p><table border="1">'
            . '<tr><th>h</th><td style="font-weight:bold">b</td></tr></table><ul><li>i</li></ul>'
            . '<div style="display:inline-block;width:20mm">ib</div>';
        $obj->addHTMLCell($html, 10, 10, 100);

        $snapshots = $obj->exposeInheritedSnapshots();
        $this->assertNotEmpty($snapshots);
        foreach ($snapshots as $snapshot) {
            $this->assertNotEmpty($snapshot['before']);
            $this->assertSame($snapshot['before'], $snapshot['after']);
        }
    }
}
