<?php

/**
 * PageAliasTest.php
 *
 * @since       2002-08-03
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

/**
 * Page number aliases (~#PN, ~#PT) written with byte and composite fonts.
 */
class PageAliasTest extends TestUtil
{
    public static function setUpBeforeClass(): void
    {
        self::setUpFontsPath();
    }

    /**
     * Returns a document with the given number of pages and the font selected.
     *
     * @throws \Throwable
     */
    private function getDocument(string $font, int $pages = 1, string $mode = ''): \Com\Tecnick\Pdf\Tcpdf
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false, $mode);
        for ($idx = 0; $idx < $pages; ++$idx) {
            $obj->addPage();
        }

        $obj->page->addContent($obj->font->insert($obj->pon, $font, '', 10)['out']);

        return $obj;
    }

    /**
     * Adds a text cell on each page of the document.
     *
     * @throws \Throwable
     */
    private function addTextOnEveryPage(\Com\Tecnick\Pdf\Tcpdf $obj, string $txt, string $halign = 'L'): void
    {
        foreach (\array_keys($obj->page->getPages()) as $pid) {
            $obj->page->addContent($obj->getTextCell($txt, 10, 10, 100, 0, 0, 0, 'T', $halign), $pid);
        }
    }

    /**
     * Returns the escaped glyph index string of a character in the current font.
     *
     * @throws \Throwable
     */
    private function getGidStr(\Com\Tecnick\Pdf\Tcpdf $obj, string $chars): string
    {
        $str = '';
        foreach (\mb_str_split($chars) as $chr) {
            $gid = $obj->font->getGidForOrd(\mb_ord($chr));
            $str .= \chr($gid >> 8) . \chr($gid & 0xFF);
        }

        return \strtr($str, [')' => '\\)', '(' => '\\(', '\\' => '\\\\', "\r" => '\\r']);
    }

    /**
     * @throws \Throwable
     */
    public function testByteFontAliasesAreResolvedOnEachPage(): void
    {
        $obj = $this->getDocument('helvetica', 3);
        $this->addTextOnEveryPage($obj, 'Page ~#PN of ~#PT');

        $out = $obj->getOutPDFString();

        $this->assertStringContainsString('[(Page )(1)( of )(3)] TJ', $out);
        $this->assertStringContainsString('[(Page )(2)( of )(3)] TJ', $out);
        $this->assertStringContainsString('[(Page )(3)( of )(3)] TJ', $out);
        $this->assertStringNotContainsString('\\#A', $out);
        $this->assertStringNotContainsString('~#P', $out);
    }

    /**
     * @throws \Throwable
     */
    public function testGidFontAliasesAreResolvedWithRegisteredDigitGlyphs(): void
    {
        $obj = $this->getDocument('dejavusans', 2);
        $this->addTextOnEveryPage($obj, 'Page ~#PN of ~#PT');

        $out = $obj->getOutPDFString();

        $page = $this->getGidStr($obj, 'Page ');
        $sep = $this->getGidStr($obj, ' of ');
        foreach (['1', '2'] as $num) {
            $this->assertStringContainsString(
                '[('
                . $page
                . ')('
                . $this->getGidStr($obj, $num)
                . ')('
                . $sep
                . ')('
                . $this->getGidStr($obj, '2')
                . ')] TJ',
                $out,
            );
        }

        $this->assertStringNotContainsString('\\#A', $out);

        // The digits are part of the font widths and ToUnicode map even when no other
        // text of the document contains them.
        $font = $obj->font->getFont('dejavusans');
        foreach (\str_split('0123456789') as $digit) {
            $gid = $obj->font->getGidForOrd(\ord($digit));
            $this->assertSame(\ord($digit), $font['usedgid'][$gid] ?? null);
        }
    }

    /**
     * @throws \Throwable
     */
    public function testAliasesUseThePageGroupNumbers(): void
    {
        $obj = $this->getDocument('helvetica', 2);
        $obj->addPage(['group' => 1]);
        $this->addTextOnEveryPage($obj, 'P ~#PN/~#PT');

        $out = $obj->getOutPDFString();

        $this->assertStringContainsString('[(P )(1)(/)(2)] TJ', $out);
        $this->assertStringContainsString('[(P )(2)(/)(2)] TJ', $out);
        $this->assertStringContainsString('[(P )(1)(/)(1)] TJ', $out);
    }

    /**
     * A right-aligned or centered line is shifted by the width difference between
     * the aliases and their values, so that it keeps its aligned edge.
     *
     * Helvetica widths: ~ 584, # 556, P 667, N 722, T 611, 1 556.
     *
     * @throws \Throwable
     */
    public function testAlignedLineIsShiftedByTheAliasWidthDifference(): void
    {
        $obj = $this->getDocument('helvetica');
        $obj->page->addContent($obj->getTextCell('Page ~#PN of ~#PT', 10, 10, 100, 0, 0, 0, 'T', 'R'));
        $obj->page->addContent($obj->getTextCell('Page ~#PN of ~#PT', 10, 20, 100, 0, 0, 0, 'T', 'C'));

        $out = $obj->getOutPDFString();

        // (2529 - 556) + (2418 - 556)
        $this->assertStringContainsString('[-3835.000000 (Page )(1)( of )(1)] TJ', $out);
        $this->assertStringContainsString('[-1917.500000 (Page )(1)( of )(1)] TJ', $out);
    }

    /**
     * A line justified to a width keeps it: the width difference follows each alias.
     *
     * @throws \Throwable
     */
    public function testJustifiedLineKeepsItsWidth(): void
    {
        $obj = $this->getDocument('helvetica');
        $obj->page->addContent($obj->getTextLine('Page ~#PN end', 10, 10, 100));

        $out = $obj->getOutPDFString();

        $this->assertStringContainsString('[(Page )(1) -1973.000000 ( end)] TJ', $out);
    }

    /**
     * @throws \Throwable
     */
    public function testRightToLeftParagraphKeepsTheAliasTogether(): void
    {
        $obj = $this->getDocument('dejavusans');
        $obj->page->addContent($obj->getTextCell(
            "\u{05E2}\u{05DE}\u{05D5}\u{05D3} ~#PN",
            10,
            10,
            100,
            0,
            0,
            0,
            'T',
            'R',
        ));

        $out = $obj->getOutPDFString();

        // Visual order: the value, then the space and the reversed word.
        $this->assertStringContainsString(
            '('
            . $this->getGidStr($obj, '1')
            . ')('
            . $this->getGidStr($obj, " \u{05D3}\u{05D5}\u{05DE}\u{05E2}")
            . ')] TJ',
            $out,
        );
        $this->assertStringNotContainsString('\\#A', $out);
    }

    /**
     * A Form XObject is shared by the pages, so its aliases keep their text.
     *
     * @throws \Throwable
     */
    public function testXObjectAliasKeepsTheAliasText(): void
    {
        $obj = $this->getDocument('helvetica');
        $tid = $obj->newXObjectTemplate(50, 20);
        $obj->addXObjectContent($tid, $obj->getTextCell('X ~#PN', 0, 0, 40));
        $obj->exitXObjectTemplate();
        $obj->page->addContent($obj->getXObjectTemplate($tid, 10, 10, 50, 20));

        $out = $obj->getOutPDFString();

        $this->assertStringContainsString('[(X )(~#PN)] TJ', $out);
        $this->assertStringNotContainsString('\\#A', $out);
    }

    /**
     * @throws \Throwable
     */
    public function testActualTextAliasIsResolved(): void
    {
        $obj = $this->getDocument('dejavusans', 1, 'pdfua');
        $obj->setTitle('Aliases');
        $obj->setLanguage('en-US');
        $obj->addTextCell("\u{FB01}le ~#PN", -1, 10, 10, 100);

        $out = $obj->getOutPDFString();

        $expected = \bin2hex("\xFE\xFF" . (string) \mb_convert_encoding('file 1', 'UTF-16BE', 'UTF-8'));
        $this->assertStringContainsString('/ActualText <' . $expected . '>', $out);
        $this->assertStringNotContainsString('\\#A', $out);
    }

    /**
     * @throws \Throwable
     */
    public function testTextWithoutAliasesKeepsTheTextShowingOperator(): void
    {
        $obj = $this->getDocument('helvetica');
        $out = $obj->getTextCell('Plain ~# text', 10, 10, 100);

        $this->assertStringContainsString('(Plain ~# text) Tj', $out);
    }

    /**
     * @throws \Throwable
     */
    public function testUnknownMarkIsKept(): void
    {
        $obj = new TestableText();
        $this->assertSame('(\\#A99\\#)', $obj->exposeResolvePageAliasMarks('(\\#A99\\#)', 1, 1));
        $this->assertSame('(\\#A99\\#)', $obj->exposeResolvePageAliasMarks('(\\#A99\\#)'));
    }
}
