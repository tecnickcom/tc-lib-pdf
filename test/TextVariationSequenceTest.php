<?php

/**
 * TextVariationSequenceTest.php
 *
 * @since     2026-11-02
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
 * Text with Unicode Variation Sequences.
 *
 * The fixture font test/fixtures/fonts/ivs/ivstest.json is the tc-lib-pdf-font test font
 * ivstest.ttf: glyphs .notdef (0), space (1), A (2, 600 units), A.vs1 (3, 700), A.vs2 (4),
 * B (5), U+908A (6, 1000) and U+908A.vs1 (7, 900), with the sequences A+FE00 (default),
 * A+FE01 and B+FE01 (A.vs1), A+FE02 (A.vs2), U+908A+E0100 (default) and
 * U+908A+E0101 (U+908A.vs1).
 *
 * @since     2026-11-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class TextVariationSequenceTest extends TestUtil
{
    /**
     * Returns a document with a page and the fixture font selected at 10 points.
     *
     * @throws \Throwable
     */
    private function getObject(string $mode = ''): \Com\Tecnick\Pdf\Tcpdf
    {
        self::setUpFontsPath();
        $dir = (string) \realpath(__DIR__ . '/fixtures/fonts/ivs');
        $pdf = new \Com\Tecnick\Pdf\Tcpdf(unit: 'pt', compress: false, mode: $mode, fileOptions: ['allowedPaths' => [
            $dir,
            (string) \constant('K_PATH_FONTS'),
        ]]);
        $pdf->addPage();
        $font = $pdf->font->insert($pdf->pon, 'ivstest', '', 10, null, null, $dir . '/ivstest.json');
        $pdf->page->addContent($font['out']);

        return $pdf;
    }

    /**
     * @throws \Throwable
     */
    public function testSequenceIsShownWithItsGlyph(): void
    {
        $pdf = $this->getObject();
        $out = $pdf->getTextCell("A\u{FE01}A\u{908A}\u{E0101}B\u{FE0F}", 10, 10, 300);

        // A.vs1, A, U+908A.vs1, B: an unmapped sequence shows the base glyph
        $this->assertStringContainsString("(\x00\x03\x00\x02\x00\x07\x00\x05) Tj", $out);
    }

    /**
     * @throws \Throwable
     */
    public function testSequenceTakesTheWidthOfItsGlyph(): void
    {
        $pdf = $this->getObject();

        $pdf->getTextCell("A\u{FE01}", 10, 10, 0);
        $this->assertEqualsWithDelta(7.0, \floatval($pdf->getLastBBox()['w'] ?? 0.0), 0.001);

        $pdf->getTextCell("\u{908A}\u{E0101}\u{908A}", 10, 10, 0);
        $this->assertEqualsWithDelta(19.0, \floatval($pdf->getLastBBox()['w'] ?? 0.0), 0.001);
    }

    /**
     * @throws \Throwable
     */
    public function testExtractedTextKeepsTheSelectors(): void
    {
        $pdf = $this->getObject();
        $pdf->page->addContent($pdf->getTextCell("A A\u{FE01} B\u{FE01} A\u{FE00}", 10, 10, 300));
        $out = $pdf->getOutPDFString();

        $matches = [];
        $this->assertSame(1, \preg_match('#/ToUnicode (\d+) 0 R#', $out, $matches));
        $this->assertSame(1, \preg_match(
            '#\n' . ($matches[1] ?? '') . ' 0 obj\n<<[^>]*>> stream\n(.*?)\nendstream#s',
            $out,
            $matches,
        ));
        $cmap = \gzuncompress($matches[1] ?? '');
        $this->assertIsString($cmap);
        $this->assertStringContainsString(
            "<0001> <0020>\n<0002> <0041>\n<0003> <0041fe01>\n<0008> <0042fe01>\n<0009> <0041fe00>\n",
            $cmap,
        );
        $this->assertMatchesRegularExpression('#/CIDToGIDMap \d+ 0 R#', $out);
    }

    /**
     * A line is never broken between a base character and its selector.
     *
     * @throws \Throwable
     */
    public function testSelectorStaysWithItsBaseWhenTheLineWraps(): void
    {
        $pdf = $this->getObject();
        $out = $pdf->getTextCell(\str_repeat("\u{908A}\u{E0101}", 12), 10, 10, 40);

        $this->assertGreaterThan(1, \substr_count($out, ' Tj'));
        $this->assertSame(12, \substr_count($out, "\x00\x07"));
        $this->assertSame(0, \substr_count($out, "\x00\x06"));
    }

    /**
     * @throws \Throwable
     */
    public function testHtmlTextShowsTheSequence(): void
    {
        $pdf = $this->getObject();
        $out = $pdf->getHTMLCell("<p>A\u{FE02} \u{908A}\u{E0101}</p>", 10, 10, 300, 0);

        $this->assertStringContainsString("\x00\x04", $out);
        $this->assertStringContainsString("\x00\x07", $out);
    }

    /**
     * A font with no variation sequence shows no glyph for a selector.
     *
     * @throws \Throwable
     */
    public function testByteFontDropsTheSelector(): void
    {
        $pdf = $this->getObject();
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);
        $pdf->page->addContent($font['out']);

        $out = $pdf->getTextCell("A\u{FE01}B", 10, 10, 300);
        $this->assertStringContainsString('(AB) Tj', $out);
        $this->assertEqualsWithDelta(
            ($pdf->font->getCharWidth(0x41) + $pdf->font->getCharWidth(0x42)) / 1.0,
            \floatval($pdf->getLastBBox()['w'] ?? 0.0),
            0.001,
        );
    }

    /**
     * A conformance mode that forbids unmapped glyphs keeps the selectors.
     *
     * @throws \Throwable
     */
    public function testConformanceModeKeepsTheSelectors(): void
    {
        $pdf = $this->getObject('pdfa2b');
        $out = $pdf->getTextCell("A\u{FE01}", 10, 10, 300);

        $this->assertStringContainsString("(\x00\x03) Tj", $out);
        /** @var array<int, string> $warnings */
        $warnings = $this->getObjectProperty($pdf, 'warnings');
        $this->assertSame([], \preg_grep('/U\+FE01/', $warnings));
    }
}
