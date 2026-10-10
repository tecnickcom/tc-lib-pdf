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

    /** @throws \Throwable */
    public function testTableDomDoesNotKeepCopiesOfRepeatedValues(): void
    {
        $obj = new TestableHTML();
        $html = '<table border="1" cellpadding="2">';
        for ($idx = 1; $idx <= 200; ++$idx) {
            $html .= '<tr><td>Row ' . $idx . '</td><td>Lorem ipsum</td><td>dolor sit amet</td><td>1,00</td></tr>';
        }

        $html .= '</table>';

        \gc_collect_cycles();
        $before = \memory_get_usage();
        $dom = $obj->exposeGetHTMLDOM($html);
        $used = \memory_get_usage() - $before;

        // 14 nodes per row (no injected node per cell) and the document nodes.
        $this->assertCount(2807, $dom);
        // About 38 KB per row of four cells; a copy of the border and attribute arrays
        // of every cell, or an injected node per cell, exceeds the bound.
        $this->assertLessThan(200 * 45 * 1024, $used);
    }
}
