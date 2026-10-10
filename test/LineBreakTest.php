<?php

/**
 * LineBreakTest.php
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

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Line breaks after hyphens and inside words wider than the line.
 *
 * @since     2026-10-10
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class LineBreakTest extends TestUtil
{
    private const LONGWORD = 'Supercalifragilisticexpialidociousandevenlonger';

    public static function setUpBeforeClass(): void
    {
        self::setUpFontsPath();
    }

    /**
     * Returns an uncompressed document with helvetica 10 and one page.
     *
     * @throws \Throwable
     */
    private function getDocument(): TestableText
    {
        $pdf = new TestableText('mm', true, false, false);
        /** @var int $pon */
        $pon = $this->getObjectProperty($pdf, 'pon');
        $font = $pdf->font->insert($pon, 'helvetica', '', 10);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);

        return $pdf;
    }

    /**
     * Returns the content stream of the current page.
     *
     * @throws \Throwable
     */
    private function getContent(\Com\Tecnick\Pdf\Tcpdf $pdf): string
    {
        return \implode("\n", $pdf->page->getPage()['content']);
    }

    /**
     * Returns the abscissa (points) and the string of every Td/Tj pair.
     *
     * @return list<array{x: float, txt: string}>
     */
    private function getTextRuns(string $content): array
    {
        $matches = [];
        \preg_match_all('/ ([-0-9.]+) [-0-9.]+ Td \(((?:\\\\.|[^\\\\)])*)\) Tj/', $content, $matches, PREG_SET_ORDER);
        $out = [];
        foreach ($matches as $match) {
            $out[] = ['x' => \floatval($match[1] ?? 0.0), 'txt' => $match[2] ?? ''];
        }

        return $out;
    }

    /**
     * Returns the width (points) of a string in the current font.
     *
     * @throws \Throwable
     */
    private function getWidth(\Com\Tecnick\Pdf\Tcpdf $pdf, string $txt): float
    {
        $method = new \ReflectionMethod(\Com\Tecnick\Pdf\Text::class, 'getStringWidth');
        $width = $method->invoke($pdf, $txt);
        $this->assertIsFloat($width);

        return $pdf->toPoints($width);
    }

    /**
     * Returns the lines of splitLines() for the code points in the current font.
     *
     * @param list<int> $ordarr
     *
     * @return list<array{pos: int, chars: int, totwidth: float}>
     *
     * @throws \Throwable
     */
    private function splitLines(TestableText $pdf, array $ordarr, float $pwidth, float $offset): array
    {
        $out = [];
        foreach ($pdf->exposeSplitLines($ordarr, $pdf->font->getOrdArrDims($ordarr), $pwidth, $offset) as $line) {
            $out[] = ['pos' => $line['pos'], 'chars' => $line['chars'], 'totwidth' => $line['totwidth']];
        }

        return $out;
    }

    /**
     * Returns the code points of a string.
     *
     * @return list<int>
     */
    private function getOrdArr(string $txt): array
    {
        $out = [];
        foreach (\mb_str_split($txt, 1, 'UTF-8') as $chr) {
            $out[] = (int) \mb_ord($chr, 'UTF-8');
        }

        return $out;
    }

    /**
     * Returns the code points with the hyphen break opportunities added, as a string
     * where each ZERO WIDTH SPACE is shown as "|".
     *
     * @throws \Throwable
     */
    private function getHyphenBreaks(\Com\Tecnick\Pdf\Tcpdf $pdf, string $txt): string
    {
        $method = new \ReflectionMethod(\Com\Tecnick\Pdf\Text::class, 'addOrdArrHyphenBreaks');
        $ordarr = $method->invoke($pdf, $this->getOrdArr($txt));
        $this->assertIsArray($ordarr);
        $out = '';
        foreach ($ordarr as $ord) {
            $this->assertIsInt($ord);
            $out .= $ord === 0x200B ? '|' : \mb_chr($ord, 'UTF-8');
        }

        return $out;
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function hyphenBreakProvider(): array
    {
        return [
            'compound' => ['well-known', 'well-|known'],
            'several hyphens' => ['state-of-the-art', 'state-|of-|the-|art'],
            'word-initial hyphen' => ['-foo', '-foo'],
            'final hyphen' => ['foo-', 'foo-'],
            'between digits' => ['1-2', '1-2'],
            'before digits' => ['ABC-123', 'ABC-123'],
            'negative number' => ['-5', '-5'],
            'non-breaking hyphen' => ["a\u{2011}b", "a\u{2011}b"],
            'word joiner' => ["a-\u{2060}b", "a-\u{2060}b"],
            'en dash' => ["a\u{2013}b", "a\u{2013}|b"],
            'hyphen' => ["a\u{2010}b", "a\u{2010}|b"],
            'combining mark' => ["e\u{0301}-x", "e\u{0301}-|x"],
            'space after' => ['a- b', 'a- b'],
        ];
    }

    /** @throws \Throwable */
    #[DataProvider('hyphenBreakProvider')]
    public function testHyphenBreakOpportunities(string $txt, string $expected): void
    {
        $this->assertSame($expected, $this->getHyphenBreaks($this->getDocument(), $txt));
    }

    /** @throws \Throwable */
    public function testFirstAutomaticBreakIncludesHyphens(): void
    {
        $pdf = $this->getDocument();
        $method = new \ReflectionMethod(\Com\Tecnick\Pdf\Text::class, 'getOrdArrFirstAutoBreak');

        $this->assertSame(5, $method->invoke($pdf, $this->getOrdArr('well-known')));
        $this->assertSame(-1, $method->invoke($pdf, $this->getOrdArr('wellknown')));

        $pdf->enableHyphenBreaks(false);
        $this->assertSame(-1, $method->invoke($pdf, $this->getOrdArr('well-known')));
    }

    /** @throws \Throwable */
    public function testHyphenatedWordsBreakInANarrowColumn(): void
    {
        $pdf = $this->getDocument();

        $pdf->addHTMLCell(
            '<table border="1" cellpadding="2"><tr>'
            . '<td width="90">Long-distance well-known state-of-the-art</td><td>next</td>'
            . '</tr></table>',
            15,
            20,
            180,
        );

        $runs = $this->getTextRuns($this->getContent($pdf));
        $this->assertSame(
            ['Long-distance ', 'well-known ', 'state-of-the-', 'art', 'next'],
            \array_column($runs, 'txt'),
        );

        // 90px column at 15 mm with a 2px padding: the text ends before 108.52 pt.
        foreach ($runs as $run) {
            if ($run['txt'] === 'next') {
                continue;
            }

            $this->assertLessThanOrEqual(108.53, $run['x'] + $this->getWidth($pdf, \rtrim($run['txt'])));
        }
    }

    /** @throws \Throwable */
    public function testHyphenBreaksCanBeDisabled(): void
    {
        $pdf = $this->getDocument();
        $pdf->enableHyphenBreaks(false);
        $pdf->enableWordBreakFallback(false);

        $pdf->addHTMLCell('<table><tr><td width="90">state-of-the-art</td><td>next</td></tr></table>', 15, 20, 180);

        $this->assertStringContainsString('(state-of-the-art) Tj', $this->getContent($pdf));
    }

    /** @throws \Throwable */
    public function testNoWrapKeepsWordsTogether(): void
    {
        $pdf = $this->getDocument();

        $pdf->addHTMLCell(
            '<table><tr><td width="40" style="white-space:nowrap">state-of-the-art '
            . self::LONGWORD
            . '</td><td>next</td></tr></table>',
            15,
            20,
            180,
        );

        $runs = \array_column($this->getTextRuns($this->getContent($pdf)), 'txt');
        $this->assertSame(
            ['state-of-the-art ' . self::LONGWORD, 'next'],
            \array_map(static fn(string $txt): string => \str_replace("\xA0", ' ', $txt), $runs),
        );
    }

    /** @throws \Throwable */
    public function testWordWiderThanAColumnIsBrokenBetweenCharacters(): void
    {
        $pdf = $this->getDocument();

        $pdf->addHTMLCell(
            '<table border="1" cellpadding="2"><tr>'
            . '<td width="40">'
            . self::LONGWORD
            . ' text</td><td>next column</td>'
            . '</tr></table>',
            15,
            20,
            180,
        );

        $runs = $this->getTextRuns($this->getContent($pdf));
        $pieces = '';
        foreach ($runs as $run) {
            if ($run['txt'] === 'next column') {
                continue;
            }

            $pieces .= \rtrim($run['txt']);
            // 40px column at 15 mm with a 2px padding: the text ends before 72.52 pt.
            $this->assertLessThanOrEqual(72.53, $run['x'] + $this->getWidth($pdf, \rtrim($run['txt'])));
        }

        $this->assertSame(self::LONGWORD . 'text', $pieces);
        $this->assertGreaterThan(3, \count($runs));
    }

    /** @throws \Throwable */
    public function testWordWiderThanATextCellIsBroken(): void
    {
        $pdf = $this->getDocument();

        $pdf->page->addContent($pdf->getTextCell(self::LONGWORD . ' text', 15, 20, 12, 0, 0, 0, 'T', 'L'));

        $runs = $this->getTextRuns($this->getContent($pdf));
        $this->assertGreaterThan(3, \count($runs));
        foreach ($runs as $run) {
            $this->assertLessThanOrEqual(
                $pdf->toPoints(27.0) + 0.01,
                $run['x'] + $this->getWidth($pdf, \rtrim($run['txt'])),
            );
        }
    }

    /** @throws \Throwable */
    public function testWordBreakFallbackCanBeDisabled(): void
    {
        $pdf = $this->getDocument();
        $pdf->enableWordBreakFallback(false);

        $pdf->page->addContent($pdf->getTextCell(self::LONGWORD . ' text', 15, 20, 12, 0, 0, 0, 'T', 'L'));

        $this->assertStringContainsString('(' . self::LONGWORD . ' ) Tj', $this->getContent($pdf));
    }

    /** @throws \Throwable */
    public function testSplitLinesBreaksAWordWiderThanTheLine(): void
    {
        $pdf = $this->getDocument();
        $ordarr = $this->getOrdArr('ab ' . self::LONGWORD . ' cd');
        $pwidth = 60.0;

        $lines = $this->splitLines($pdf, $ordarr, $pwidth, 0.0);

        $text = '';
        foreach ($lines as $line) {
            $this->assertLessThanOrEqual($pwidth + 0.01, $line['totwidth']);
            $chunk = \array_slice($ordarr, $line['pos'], $line['chars']);
            $text .= \implode('', \array_map(static fn(int $ord): string => (string) \mb_chr($ord), $chunk)) . '|';
        }

        // The short word stays on its line, the long word starts a new one.
        $this->assertStringStartsWith('ab|Super', $text);
        $this->assertSame('ab' . self::LONGWORD . 'cd', \str_replace(['|', ' '], '', $text));
        $this->assertGreaterThan(3, \count($lines));

        // A first line shortened by an offset is used for the first piece.
        $lines = $this->splitLines($pdf, $this->getOrdArr(self::LONGWORD), $pwidth, 30.0);
        $this->assertLessThanOrEqual(30.01, ($lines[0] ?? ['totwidth' => 99.0])['totwidth']);
    }

    /** @throws \Throwable */
    public function testSplitLinesKeepsCombiningMarksWithTheirBase(): void
    {
        $pdf = $this->getDocument();
        $ordarr = $this->getOrdArr(\str_repeat("e\u{0301}", 30));

        $lines = $this->splitLines($pdf, $ordarr, 20.0, 0.0);

        $this->assertGreaterThan(1, \count($lines));
        foreach ($lines as $line) {
            $this->assertSame(0x65, $ordarr[$line['pos']] ?? null);
        }
    }

    /** @throws \Throwable */
    public function testSplitLinesTakesOneGlyphWiderThanTheLine(): void
    {
        $this->assertCount(3, $this->splitLines($this->getDocument(), $this->getOrdArr('WWW'), 2.0, 0.0));
    }

    /** @throws \Throwable */
    public function testTextLineDoesNotWriteLineBreakControls(): void
    {
        $pdf = $this->getDocument();
        $pdf->page->addContent($pdf->getTextLine('tc-lib-pdf state-of-the-art', 20, 20));
        $content = $this->getContent($pdf);
        $this->assertStringContainsString('(tc-lib-pdf state-of-the-art) Tj', $content);

        $pdf = new TestableText('mm', true, false, false);
        /** @var int $pon */
        $pon = $this->getObjectProperty($pdf, 'pon');
        $fontfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/cid0/cid0jp.json');
        $font = $pdf->font->insert($pon, 'cid0jp', '', 10, null, null, $fontfile);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->page->addContent($pdf->getTextLine('日本語のテキスト', 20, 20));
        $content = $this->getContent($pdf);
        // UTF-16BE code units of the eight characters, without U+200B between them.
        $this->assertStringContainsString("\x65\xE5\x67\x2C\x8A\x9E\x30\x6E", $content);
        $this->assertStringNotContainsString("\x20\x0B", $content);
    }

    /** @throws \Throwable */
    public function testAutoTableColumnsHoldTheirLongestWord(): void
    {
        $pdf = new TestableText('mm', true, false, false);
        /** @var int $pon */
        $pon = $this->getObjectProperty($pdf, 'pon');
        $font = $pdf->font->insert($pon, 'dejavusans', '', 10);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $rows = '';
        for ($idx = 1; $idx <= 3; ++$idx) {
            $rows .=
                '<tr><td>'
                . $idx
                . '</td><td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td><td>'
                . \number_format($idx * 12.34, 2)
                . '</td></tr>';
        }

        $pdf->addHTMLCell(
            '<table border="1" cellpadding="3" cellspacing="0"><thead><tr>'
            . '<th>#</th><th>Description</th><th>Amount</th></tr></thead>'
            . $rows
            . '</table>',
            20,
            20,
            150,
        );

        // Three header cells and three rows of three cells: the bold "Amount" is not broken.
        $this->assertSame(12, \substr_count($this->getContent($pdf), ' Td ('));
    }

    /** @throws \Throwable */
    public function testMinimumColumnWidthsTakeTheWidthFromWiderColumns(): void
    {
        $pdf = $this->getDocument();
        $method = new \ReflectionMethod(\Com\Tecnick\Pdf\HTML::class, 'applyHTMLTableMinColWidths');

        // Column 1 needs 30 and has 20: the 10 come from column 0, which has 50 of slack.
        $this->assertSame(
            [50.0, 30.0, 20.0],
            $method->invoke(
                $pdf,
                [60.0, 20.0, 20.0],
                [0 => 10.0, 1 => 30.0],
                [
                    2 => true,
                ],
            ),
        );
        // Not enough slack: the deficit columns share what there is, the sum is kept.
        $this->assertSame([10.0, 30.0], $method->invoke($pdf, [15.0, 25.0], [0 => 10.0, 1 => 40.0], []));
        // Nothing to move.
        $this->assertSame([20.0, 20.0], $method->invoke($pdf, [20.0, 20.0], [0 => 10.0, 1 => 10.0], []));
    }

    /** @throws \Throwable */
    public function testFitModesKeepMeasuringTheWholeWord(): void
    {
        $pdf = $this->getDocument();

        $pdf->page->addContent($pdf->getTextCell(self::LONGWORD, 15, 20, 30, 10, fit: 'F'));

        // The font is shrunk until the word fits, instead of breaking it.
        $this->assertStringContainsString('(' . self::LONGWORD . ') Tj', $this->getContent($pdf));
    }
}
