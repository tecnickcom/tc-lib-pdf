<?php

/**
 * IdeographicBreakTest.php
 *
 * @since     2026-10-09
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

use Com\Tecnick\Unicode\Data\LineBreak;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Line breaking of ideographic text and of the line break controls.
 *
 * @since     2026-10-09
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class IdeographicBreakTest extends TestUtil
{
    private const JAPANESE =
        '吾輩は猫である。名前はまだ無い。どこで生れたかとんと見当がつかぬ。'
            . '何でも薄暗いじめじめした所でニャーニャー泣いていた事だけは記憶している。';

    public static function setUpBeforeClass(): void
    {
        self::setUpFontsPath();
    }

    /**
     * Returns a document with the given font of the bundled set and a page.
     *
     * @throws \Throwable
     */
    private function getDocument(string $font, string $file): \Com\Tecnick\Pdf\Tcpdf
    {
        $pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
        /** @var int $pon */
        $pon = $this->getObjectProperty($pdf, 'pon');
        $fontfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/' . $file);
        $metric = $pdf->font->insert($pon, $font, '', 10, null, null, $fontfile);
        $pdf->addPage();
        $pdf->page->addContent($metric['out']);

        return $pdf;
    }

    /**
     * Returns the text of each rendered line of a document that uses a CID font.
     *
     * @return array<int, string>
     */
    private function getLines(string $out): array
    {
        $mtch = [];
        \preg_match_all('#Td \(((?:\\\\.|[^\\\\)])*)\) Tj#s', $out, $mtch);
        $lines = [];
        foreach ($mtch[1] ?? [] as $raw) {
            $lines[] = (string) \mb_convert_encoding(\stripcslashes($raw), 'UTF-8', 'UTF-16BE');
        }

        return $lines;
    }

    /**
     * Checks that the Japanese text is split into lines that follow the line start and
     * line end rules and that keep every character.
     *
     * @param array<int, string> $lines
     */
    private function assertLineRules(array $lines): void
    {
        $this->assertGreaterThan(1, \count($lines));
        foreach ($lines as $idx => $line) {
            $first = \mb_substr($line, 0, 1);
            $last = \mb_substr($line, -1);
            if ($idx > 0) {
                $this->assertNotContains($first, ['、', '。', 'ー', 'ゃ', 'ゅ', 'ょ', 'っ', 'ャ', '」'], $line);
            }

            $this->assertNotContains($last, ['「', '（'], $line);
        }

        $this->assertSame(\str_replace(["\u{200B}"], '', self::JAPANESE), \implode('', $lines));
    }

    /**
     * @throws \Throwable
     */
    public function testFirstIdeographicOrdMatchesTheData(): void
    {
        $first = \PHP_INT_MAX;
        foreach (LineBreak::RANGES as [$from, , $class]) {
            if ($class !== 'ID' && $class !== 'H2' && $class !== 'H3') {
                continue;
            }

            $first = \min($first, $from);
        }

        $this->assertSame(\min($first, LineBreak::HANGUL_FIRST), (new TestableText())->exposeFirstIdeographicOrd());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function breakProvider(): array
    {
        return [
            'between ideographs' => ['漢字', "漢\u{200B}字"],
            'kana' => ['ねこ', "ね\u{200B}こ"],
            'not before a full stop' => ['猫。名', "猫。\u{200B}名"],
            'not before a small kana or a prolonged sound mark' => ['ニャー', 'ニャー'],
            'not after an opening bracket, not before a closing one' => ['「猫」', '「猫」'],
            'latin and ideographs' => ['Unicode対応', "Unicode\u{200B}対\u{200B}応"],
            'not next to a space' => ['A 漢', 'A 漢'],
            'not next to a word joiner' => ["漢\u{2060}字", "漢\u{2060}字"],
            'not before a combining mark' => ["漢\u{FE0F}字", "漢\u{FE0F}\u{200B}字"],
            'hangul syllables' => ['한국', "한\u{200B}국"],
            'latin only' => ['Latin text', 'Latin text'],
            'em dashes' => ['——', '——'],
        ];
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('breakProvider')]
    public function testAddOrdArrIdeographicBreaks(string $text, string $expected): void
    {
        $obj = new TestableText();
        $ordarr = \array_map(static fn(string $chr): int => (int) \mb_ord($chr), \mb_str_split($text));
        $out = \implode('', \array_map(\mb_chr(...), $obj->exposeAddOrdArrIdeographicBreaks($ordarr)));

        $this->assertSame($expected, $out);
    }

    /**
     * @throws \Throwable
     */
    public function testTextCellWrapsJapaneseText(): void
    {
        $pdf = $this->getDocument('cid0jp', 'cid0/cid0jp.json');
        $pdf->addTextCell(self::JAPANESE, -1, 15, 20, 90);
        $bbox = $pdf->getLastTextBBox();

        $this->assertLessThanOrEqual(90.001, $bbox['w']);
        $this->assertLineRules($this->getLines($pdf->getOutPDFString()));
    }

    /**
     * @throws \Throwable
     */
    public function testHTMLCellWrapsJapaneseText(): void
    {
        $pdf = $this->getDocument('cid0jp', 'cid0/cid0jp.json');
        $pdf->addHTMLCell('<p>' . self::JAPANESE . '</p>', 15, 20, 90);
        $this->assertLineRules($this->getLines($pdf->getOutPDFString()));

        $pdf = $this->getDocument('cid0jp', 'cid0/cid0jp.json');
        $pdf->addHTMLCell(
            '<table border="1"><tr><td style="width:50%;">' . self::JAPANESE . '</td><td></td></tr></table>',
            15,
            20,
            90,
        );
        $this->assertLineRules($this->getLines($pdf->getOutPDFString()));
    }

    /**
     * word-break: keep-all and a disabled ideographic line breaking keep the text on one line.
     *
     * @throws \Throwable
     */
    public function testIdeographicBreaksCanBeDisabled(): void
    {
        $pdf = $this->getDocument('cid0jp', 'cid0/cid0jp.json');
        $pdf->addHTMLCell('<p style="word-break:keep-all">' . self::JAPANESE . '</p>', 15, 20, 90);
        $this->assertCount(1, $this->getLines($pdf->getOutPDFString()));

        $pdf = $this->getDocument('cid0jp', 'cid0/cid0jp.json');
        $pdf->enableIdeographicBreaks(false);
        $pdf->addTextCell(self::JAPANESE, -1, 15, 20, 90);
        $this->assertCount(1, $this->getLines($pdf->getOutPDFString()));
    }

    /**
     * Soft hyphens, zero width spaces and <wbr> break the lines with a Unicode font, as
     * they do with a core font.
     *
     * @throws \Throwable
     */
    public function testLineBreakControlsWithAUnicodeFont(): void
    {
        foreach ([
            '<p>aaaaaaaaaa&shy;bbbbbbbbbb&shy;cccccccccc</p>',
            '<p>aaaaaaaaaa&#8203;bbbbbbbbbb&#8203;cccccccccc</p>',
            '<p>aaaaaaaaaa<wbr>bbbbbbbbbb<wbr>cccccccccc</p>',
            '<p style="word-break:break-all">aaaaaaaaaabbbbbbbbbbcccccccccc</p>',
        ] as $html) {
            $pdf = $this->getDocument('dejavusans', 'dejavu/dejavusans.json');
            $pdf->addHTMLCell($html, 15, 20, 30);
            $lines = $this->getLines($pdf->getOutPDFString());
            $this->assertGreaterThan(1, \count($lines), $html);
            foreach ($lines as $line) {
                $this->assertDoesNotMatchRegularExpression('/[\x{200B}\x{2060}]/u', $line);
            }
        }
    }

    /**
     * A <wbr> element with attributes or in upper case is a line break opportunity.
     *
     * @throws \Throwable
     */
    public function testWbrWithAttributesBreaksTheLine(): void
    {
        $pdf = $this->getDocument('dejavusans', 'dejavu/dejavusans.json');
        $pdf->addHTMLCell('<p>aaaaaaaaaa<wbr class="x">bbbbbbbbbb<WBR />cccccccccc</p>', 15, 20, 30);
        $lines = $this->getLines($pdf->getOutPDFString());
        $this->assertSame([10, 10, 10], \array_map(\mb_strlen(...), $lines));
    }
}
