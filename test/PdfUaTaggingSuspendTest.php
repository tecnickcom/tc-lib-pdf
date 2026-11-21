<?php

/**
 * PdfUaTaggingSuspendTest.php
 *
 * @since       2026-11-20
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Suspending PDF/UA tagging stops structure tagging and keeps the conformance rules.
 *
 * @since       2026-11-20
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class PdfUaTaggingSuspendTest extends TestUtil
{
    /**
     * @throws \Throwable
     */
    public function testSuspensionKeepsConformanceRules(): void
    {
        $pdf = new TestableText('mm', true, false, false, 'pdfua1');

        $this->assertTrue($pdf->exposeIsTaggedMode());

        $outer = $pdf->suspendPdfUaTagging();
        $inner = $pdf->suspendPdfUaTagging();
        $this->assertSame('pdfua1', $outer);
        $this->assertSame('pdfua1', $inner);
        $this->assertSame('pdfua1', $pdf->exposePdfUaMode());
        $this->assertFalse($pdf->exposeIsTaggedMode());
        $this->assertTrue($pdf->exposeForbidsUnmappedGlyphs());
        $this->assertTrue($pdf->exposeRequiresEmbeddedFonts());

        $pdf->resumePdfUaTagging($inner);
        $this->assertFalse($pdf->exposeIsTaggedMode());

        $pdf->resumePdfUaTagging($outer);
        $this->assertTrue($pdf->exposeIsTaggedMode());
        $this->assertSame('pdfua1', $pdf->exposePdfUaMode());
    }

    /**
     * @throws \Throwable
     */
    public function testResumeDoesNotChangeMode(): void
    {
        $pdf = new TestableText('mm', true, false, false, 'pdfua2');

        $pdf->suspendPdfUaTagging();
        $pdf->resumePdfUaTagging('');

        $this->assertSame('pdfua2', $pdf->exposePdfUaMode());
        $this->assertTrue($pdf->exposeIsTaggedMode());
        $this->assertTrue($pdf->exposeForbidsUnmappedGlyphs());

        $pdf->resumePdfUaTagging('');
        $this->assertTrue($pdf->exposeIsTaggedMode());
    }

    /** @return array<string, array{0: string}> */
    public static function unmappedGlyphModeProvider(): array
    {
        return [
            'pdfua1' => ['pdfua1'],
            'pdfua2' => ['pdfua2'],
            'pdfa2u' => ['pdfa2u'],
        ];
    }

    /**
     * The header replayed on the continuation page shows the same glyphs as the
     * first header and no .notdef glyph.
     *
     * @throws \Throwable
     */
    #[DataProvider('unmappedGlyphModeProvider')]
    public function testReplayedHeaderMatchesFirstHeader(string $mode): void
    {
        $pdf = new TestableText('mm', true, false, false, $mode);
        $pdf->setTitle('Replayed header');
        $pdf->setLanguage('en-US');
        $font = $pdf->font->insert($pdf->pon, 'freesans', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $this->assertTrue($pdf->exposeForbidsUnmappedGlyphs());

        // FreeSans covers neither of the two ideographs.
        $rows = \str_repeat('<tr><td>a</td><td>b</td></tr>', 80);
        $pdf->addHTMLCell(
            html: '<table><thead><tr><th>X'
            . "\u{540D}\u{524D}"
            . 'Y</th><th>Name</th></tr></thead><tbody>'
            . $rows
            . '</tbody></table>',
            posx: 15,
            posy: 20,
            width: 150,
        );

        $pages = \array_values($pdf->page->getPages());
        $this->assertCount(2, $pages);

        $first = $this->getPageGlyphStrings(\implode('', $pages[0]['content'] ?? []));
        $second = $this->getPageGlyphStrings(\implode('', $pages[1]['content'] ?? []));

        foreach (\array_merge($first, $second) as $glyphs) {
            $this->assertNotContains(0, $glyphs);
        }

        // The header cell keeps X and Y on both pages.
        $firstHeader = $first[0] ?? [];
        $this->assertCount(2, $firstHeader);
        $this->assertSame($firstHeader, $second[0] ?? []);
    }

    /**
     * Return the 2-byte glyph IDs of every Tj string of an uncompressed content stream.
     *
     * @return array<int, array<int, int>>
     */
    private function getPageGlyphStrings(string $content): array
    {
        $matches = [];
        \preg_match_all('/\(((?:\\\\.|[^\\\\)])*)\) Tj/s', $content, $matches);
        $escapes = ['r' => "\r", 'n' => "\n", 't' => "\t", 'b' => "\x08", 'f' => "\f"];
        $strings = [];
        foreach ($matches[1] ?? [] as $escaped) {
            $raw = '';
            $len = \strlen($escaped);
            for ($idx = 0; $idx < $len; ++$idx) {
                $chr = $escaped[$idx];
                if ($chr === '\\' && ($idx + 1) < $len) {
                    ++$idx;
                    $chr = $escapes[$escaped[$idx]] ?? $escaped[$idx];
                }

                $raw .= $chr;
            }

            $glyphs = [];
            foreach (\str_split($raw, 2) as $pair) {
                $glyphs[] = \strlen($pair) === 2 ? (\ord($pair[0]) << 8) | \ord($pair[1]) : -1;
            }

            $strings[] = $glyphs;
        }

        return $strings;
    }
}
