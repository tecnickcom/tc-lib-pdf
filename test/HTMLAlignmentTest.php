<?php

/**
 * HTMLAlignmentTest.php
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
 * Horizontal alignment of HTML lines made of several inline fragments.
 *
 * @since     2026-10-10
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class HTMLAlignmentTest extends TestUtil
{
    private const LOREM = 'Lorem ipsum dolor sit amet, <b>consectetur</b> adipiscing elit, sed do eiusmod';

    private const HEBREW =
        "\u{05D0}\u{05D1}\u{05D2}\u{05D3} \u{05D4}\u{05D5}\u{05D6}\u{05D7} \u{05D8}\u{05D9}\u{05DA}\u{05DB} "
            . "\u{05DC}\u{05DD}\u{05DE}\u{05DF} \u{05E0}\u{05E1}\u{05E2}\u{05E3}";

    public static function setUpBeforeClass(): void
    {
        self::setUpFontsPath();
    }

    /**
     * Returns a document with the given font and one page.
     *
     * @throws \Throwable
     */
    private function getDocument(string $font = 'helvetica'): TestableHTMLLineProbe
    {
        $pdf = new TestableHTMLLineProbe('mm', true, false, false);
        /** @var int $pon */
        $pon = $this->getObjectProperty($pdf, 'pon');
        $metric = $pdf->font->insert($pon, $font, '', 10);
        $pdf->addPage();
        $pdf->page->addContent($metric['out']);

        return $pdf;
    }

    /**
     * Renders the HTML at x = 15 mm and returns the extent [left, right] of each line,
     * in rendering order of the lines.
     *
     * @return list<array{0: float, 1: float}>
     *
     * @throws \Throwable
     */
    private function getLineExtents(TestableHTMLLineProbe $pdf, string $html): array
    {
        $pdf->resetProbeLines();
        $pdf->addHTMLCell($html, 15, 20, 180);
        // The fragments of a line in different fonts have baselines less than 1 mm apart.
        $extents = [];
        $baselines = [];
        foreach ($pdf->getProbeLines() as $line) {
            $idx = \count($extents);
            foreach ($baselines as $bidx => $baseline) {
                if (\abs($baseline - $line['y']) >= 1.0) {
                    continue;
                }

                $idx = $bidx;
                break;
            }

            $baselines[$idx] ??= $line['y'];
            $extent = $extents[$idx] ?? [$line['x'], $line['x'] + $line['w']];
            $extents[$idx] = [\min($extent[0], $line['x']), \max($extent[1], $line['x'] + $line['w'])];
        }

        \ksort($extents);

        return \array_values($extents);
    }

    /**
     * Asserts that every line is centered on the given abscissa.
     *
     * @param list<array{0: float, 1: float}> $extents
     */
    private function assertCentered(array $extents, float $center): void
    {
        $this->assertNotSame([], $extents);
        foreach ($extents as $idx => $extent) {
            $this->assertEqualsWithDelta($center, ($extent[0] + $extent[1]) / 2, 0.01, 'line ' . $idx);
        }
    }

    /** @throws \Throwable */
    public function testLineEndingBeforeAnInlineElementIsCentered(): void
    {
        $extents = $this->getLineExtents(
            $this->getDocument(),
            '<div style="text-align: center; width: 60mm;">' . self::LOREM . '</div>',
        );

        $this->assertCount(3, $extents);
        $this->assertCentered($extents, 45.0);
    }

    /** @throws \Throwable */
    public function testLineEndingBeforeAnInlineElementIsRightAligned(): void
    {
        $extents = $this->getLineExtents(
            $this->getDocument(),
            '<div style="text-align: right; width: 60mm;">' . self::LOREM . '</div>',
        );

        $this->assertCount(3, $extents);
        foreach ($extents as $extent) {
            $this->assertEqualsWithDelta(75.0, $extent[1], 0.01);
        }
    }

    /** @throws \Throwable */
    public function testCenteredTableCellLine(): void
    {
        $extents = $this->getLineExtents(
            $this->getDocument(),
            '<table width="60mm"><tr><td align="center">Lorem ipsum dolor sit amet, '
            . '<b>consectetur</b> adipiscing elit</td></tr></table>',
        );

        $this->assertCount(2, $extents);
        $this->assertCentered($extents, 45.0);
    }

    /** @throws \Throwable */
    public function testLastLineOfAWrappedFragmentIsAlignedWithTheFollowingContent(): void
    {
        $extents = $this->getLineExtents(
            $this->getDocument(),
            '<div style="text-align: center; width: 60mm;">Lorem ipsum dolor sit amet, consectetur '
            . 'adipiscing elit, sed <b>do</b> eiusmod tempor</div>'
            . '<div style="text-align: center; width: 60mm;"><i>Lorem ipsum</i> dolor sit amet, '
            . 'consectetur adipiscing <b>elit</b> sed do</div>',
        );

        $this->assertCount(5, $extents);
        $this->assertCentered($extents, 45.0);
    }

    /** @throws \Throwable */
    public function testLastLineBeforeALineBreakIsCenteredAlone(): void
    {
        $extents = $this->getLineExtents(
            $this->getDocument(),
            '<div style="text-align: center; width: 60mm;">Lorem ipsum dolor sit amet, consectetur '
            . 'adipiscing elit, sed do<br />eiusmod <b>tempor</b></div>',
        );

        $this->assertCount(3, $extents);
        $this->assertCentered($extents, 45.0);
    }

    /** @throws \Throwable */
    public function testJustifiedLinesAreUnchanged(): void
    {
        $extents = $this->getLineExtents(
            $this->getDocument(),
            '<div style="text-align: justify; width: 60mm;">Lorem ipsum dolor sit amet, consectetur '
            . 'adipiscing elit, sed <b>do</b> eiusmod tempor incididunt ut labore et dolore magna aliqua</div>',
        );

        $this->assertCount(4, $extents);
        for ($idx = 0; $idx < 3; ++$idx) {
            $this->assertEqualsWithDelta(15.0, ($extents[$idx] ?? [0.0, 0.0])[0], 0.01);
            $this->assertEqualsWithDelta(75.0, ($extents[$idx] ?? [0.0, 0.0])[1], 0.01);
        }
    }

    /** @throws \Throwable */
    public function testRightToLeftRunsFollowTextAlign(): void
    {
        $pdf = $this->getDocument('dejavusans');
        $mixed = self::HEBREW . ' <b>' . self::HEBREW . '</b> ' . self::HEBREW;
        $div = '<div dir="rtl" style="font-family:dejavusans;width:40mm;text-align:';

        $this->assertCentered($this->getLineExtents($pdf, $div . 'center">' . $mixed . '</div>'), 35.0);
        $this->assertCentered($this->getLineExtents($pdf, $div . 'center">' . self::LOREM . '</div>'), 35.0);

        foreach ($this->getLineExtents($pdf, $div . 'left">' . $mixed . '</div>') as $extent) {
            $this->assertEqualsWithDelta(15.0, $extent[0], 0.01);
        }

        foreach ($this->getLineExtents($pdf, $div . 'right">' . $mixed . '</div>') as $extent) {
            $this->assertEqualsWithDelta(55.0, $extent[1], 0.01);
        }

        $extents = $this->getLineExtents($pdf, $div . 'justify">' . $mixed . '</div>');
        $last = \array_pop($extents);
        $this->assertNotNull($last);
        $this->assertEqualsWithDelta(55.0, $last[1], 0.01);
        $this->assertNotSame([], $extents);
        foreach ($extents as $extent) {
            $this->assertEqualsWithDelta(15.0, $extent[0], 0.01);
            $this->assertEqualsWithDelta(55.0, $extent[1], 0.01);
        }
    }

    /** @throws \Throwable */
    public function testRightToLeftFragmentsAreSeparatedByOneSpace(): void
    {
        $pdf = $this->getDocument('dejavusans');
        $word = "\u{05D0}\u{05D1}\u{05D2}\u{05D3}";
        $pdf->resetProbeLines();
        $pdf->addHTMLCell(
            '<div dir="rtl" style="font-family:dejavusans;width:150mm">' . $word . '<b> ' . $word . '</b></div>',
            15,
            20,
            180,
        );

        $lines = $pdf->getProbeLines();
        $this->assertCount(2, $lines);
        $first = $lines[0] ?? ['x' => 0.0, 'w' => 0.0, 'y' => 0.0];
        $second = $lines[1] ?? ['x' => 0.0, 'w' => 0.0, 'y' => 0.0];
        // The bold space is 3.48 pt wide at 10 pt.
        $this->assertEqualsWithDelta($pdf->toUnit(3.48), $first['x'] - ($second['x'] + $second['w']), 0.01);
    }

    /** @throws \Throwable */
    public function testLastLineOfJustifiedRightToLeftTextStartsAtTheRight(): void
    {
        $pdf = $this->getDocument('dejavusans');
        $text = self::HEBREW . ' ' . self::HEBREW . ' ' . self::HEBREW;

        $extents = $this->getLineExtents(
            $pdf,
            '<div dir="rtl" style="font-family:dejavusans;width:40mm;text-align:justify">' . $text . '</div>',
        );
        $last = \array_pop($extents);
        $this->assertNotNull($last);
        $this->assertEqualsWithDelta(55.0, $last[1], 0.01);
        foreach ($extents as $extent) {
            $this->assertEqualsWithDelta(15.0, $extent[0], 0.01);
        }

        $pdf->resetProbeLines();
        $pdf->getTextCell(
            $text,
            15,
            100,
            40,
            0,
            0,
            0,
            'T',
            'J',
            null,
            [],
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            false,
            'R',
        );
        $lines = $pdf->getProbeLines();
        $final = \array_pop($lines);
        $this->assertNotNull($final);
        $this->assertEqualsWithDelta(55.0, $final['x'] + $final['w'], 0.01);
    }
}
