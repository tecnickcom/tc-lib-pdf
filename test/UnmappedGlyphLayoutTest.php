<?php

/**
 * UnmappedGlyphLayoutTest.php
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
 * Characters dropped for a missing glyph take no space in the text layout.
 *
 * @since       2026-11-20
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class UnmappedGlyphLayoutTest extends TestUtil
{
    /** FreeSans has no glyph for this ideograph. */
    private const DROPPED = "\u{540D}";

    /** @return array<string, array{0: string}> */
    public static function dropModeProvider(): array
    {
        return [
            'pdfua1' => ['pdfua1'],
            'pdfa2u' => ['pdfa2u'],
        ];
    }

    /**
     * @throws \Throwable
     */
    public function testPreparedTextDropsUnmappedCodepoints(): void
    {
        $pdf = $this->getDocument('pdfua1');
        [, $ordarr] = $pdf->exposePrepareTextWithBidi('A' . self::DROPPED . "\u{FE00}B\nC");

        $this->assertSame([0x41, 0xFE00, 0x42, 0x0A, 0x43], $ordarr);
        $this->assertCount(1, $pdf->getWarnings());
        $this->assertStringContainsString('character U+540D,', $pdf->getWarnings()[0] ?? '');
    }

    /**
     * @throws \Throwable
     */
    public function testPreparedTextKeepsUnmappedCodepointsWithoutConformanceMode(): void
    {
        $pdf = $this->getDocument('');
        [, $ordarr] = $pdf->exposePrepareTextWithBidi('A' . self::DROPPED . 'B');

        $this->assertContains(0x540D, $ordarr);
        $this->assertSame([], $pdf->getWarnings());
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('dropModeProvider')]
    public function testDroppedCharactersTakeNoLineSpace(string $mode): void
    {
        $cell = static fn(string $txt): string => '<table><tr><td style="width:40px">' . $txt . '</td></tr></table>';

        $dropped = $this->renderHTML($mode, $cell('AB' . \str_repeat(self::DROPPED, 30) . 'CD'));
        $reference = $this->renderHTML($mode, $cell('ABCD'));

        $this->assertSame($reference, $dropped);
        $this->assertNotContains([], $this->getGlyphStrings($dropped));
    }

    /**
     * @throws \Throwable
     */
    public function testUnmappedCharactersKeepLineSpaceWithoutConformanceMode(): void
    {
        $html = '<table><tr><td style="width:40px">AB' . \str_repeat(self::DROPPED, 30) . 'CD</td></tr></table>';

        $this->assertGreaterThan(2, \count($this->getGlyphStrings($this->renderHTML('', $html))));
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('dropModeProvider')]
    public function testBidiTextWithDroppedCharacterKeepsVisualOrder(string $mode): void
    {
        $hebrew = "\u{05E9}\u{05DC}\u{05D5}\u{05DD}";
        $arabic = "\u{0633}\u{0644}\u{0627}\u{0645}";
        $dropped = $this->renderHTML(
            $mode,
            '<p>abc ' . $hebrew . self::DROPPED . $hebrew . ' ' . $arabic . self::DROPPED . ' def</p>',
        );
        $reference = $this->renderHTML($mode, '<p>abc ' . $hebrew . $hebrew . ' ' . $arabic . ' def</p>');

        $this->assertSame($reference, $dropped);
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('dropModeProvider')]
    public function testHyphenationIgnoresDroppedCharacter(string $mode): void
    {
        $html = static fn(string $word): string => '<div style="width:10mm">' . \str_repeat($word . ' ', 6) . '</div>';

        $dropped = $this->renderHTML($mode, $html('hyp' . self::DROPPED . 'hen'), true);
        $reference = $this->renderHTML($mode, $html('hyphen'), true);

        $this->assertSame($reference, $dropped);
        $this->assertGreaterThan(6, \count($this->getGlyphStrings($reference)));
    }

    /**
     * A paragraph split across pages keeps every drawn character once.
     *
     * @throws \Throwable
     */
    #[DataProvider('dropModeProvider')]
    public function testPageSplitParagraphWithDroppedCharacters(string $mode): void
    {
        $words = [];
        for ($idx = 0; $idx < 120; ++$idx) {
            $words[] = 'word' . $idx;
        }

        $plain = \implode(' ', $words);
        $mixed = \implode(' ', \array_map(
            static fn(string $word): string => self::DROPPED . $word . self::DROPPED,
            $words,
        ));

        $dropped = $this->renderPages($mode, '<p>' . $mixed . '</p>');
        $reference = $this->renderPages($mode, '<p>' . $plain . '</p>');

        $this->assertCount(2, $reference);
        $this->assertSame($reference, $dropped);
    }

    /**
     * @throws \Throwable
     */
    private function getDocument(string $mode): TestableText
    {
        $pdf = new TestableText('mm', true, false, false, $mode);
        $pdf->setTitle('Unmapped glyph layout');
        $pdf->setLanguage('en-US');
        $font = $pdf->font->insert($pdf->pon, 'freesans', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);

        return $pdf;
    }

    /**
     * Render an HTML cell in a new document and return the page content.
     *
     * @throws \Throwable
     */
    private function renderHTML(string $mode, string $html, bool $hyphenate = false): string
    {
        $pdf = $this->getDocument($mode);
        if ($hyphenate) {
            $pdf->setTexHyphenPatterns(['hyphen' => 'hy3phen']);
        }

        $pdf->addHTMLCell(html: $html, posx: 15, posy: 20, width: 150);

        return \implode('', $pdf->page->getPage()['content']);
    }

    /**
     * Render an HTML cell near the bottom of the page in a new document and return
     * the content of each page.
     *
     * @return array<int, string>
     *
     * @throws \Throwable
     */
    private function renderPages(string $mode, string $html): array
    {
        $pdf = $this->getDocument($mode);
        $pdf->addHTMLCell(html: $html, posx: 15, posy: 250, width: 150);

        $pages = [];
        foreach ($pdf->page->getPages() as $page) {
            $pages[] = \implode('', $page['content']);
        }

        return $pages;
    }

    /**
     * Return the 2-byte glyph IDs of every Tj string of an uncompressed content stream.
     *
     * @return array<int, array<int, int>>
     */
    private function getGlyphStrings(string $content): array
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

            $strings[] = $raw === '' ? [] : $glyphs;
        }

        return $strings;
    }
}
