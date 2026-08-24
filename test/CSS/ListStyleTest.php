<?php

/**
 * ListStyleTest.php
 *
 * @since       2026-08-24
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

use Com\Tecnick\Pdf\CSS\ListStyle;

/**
 * Test list marker type resolution and list counter generation
 */
class ListStyleTest extends TestUtil
{
    public function testResolveTypeMapsTheCaseSensitiveAttributeValues(): void
    {
        $this->assertSame('decimal', ListStyle::resolveType('1'));
        $this->assertSame('lower-alpha', ListStyle::resolveType('a'));
        $this->assertSame('upper-alpha', ListStyle::resolveType('A'));
        $this->assertSame('lower-roman', ListStyle::resolveType('i'));
        $this->assertSame('upper-roman', ListStyle::resolveType('I'));
        $this->assertSame('upper-alpha', ListStyle::resolveType('  A  '));
    }

    public function testResolveTypeKeepsTheKeywordsCaseInsensitive(): void
    {
        $this->assertSame('disc', ListStyle::resolveType('DISC'));
        $this->assertSame('square', ListStyle::resolveType('Square'));
        $this->assertSame('circle', ListStyle::resolveType('CiRcLe'));
        $this->assertSame('upper-alpha', ListStyle::resolveType('UPPER-ALPHA'));
        $this->assertSame('lower-roman', ListStyle::resolveType('lower-roman'));
    }

    public function testResolveTypeCollapsesTheKeywordAliases(): void
    {
        $this->assertSame('lower-alpha', ListStyle::resolveType('lower-latin'));
        $this->assertSame('upper-alpha', ListStyle::resolveType('upper-latin'));
        $this->assertSame('upper-armenian', ListStyle::resolveType('armenian'));
    }

    public function testResolveTypeKeepsSentinelsAndImageMarkers(): void
    {
        $this->assertSame('', ListStyle::resolveType(''));
        $this->assertSame('!', ListStyle::resolveType('!'));
        $this->assertSame('#', ListStyle::resolveType('#'));
        $this->assertSame('^', ListStyle::resolveType('^'));
        $this->assertSame('img|png|4|4|Icon.PNG', ListStyle::resolveType('img|png|4|4|Icon.PNG'));
        $this->assertSame('bogus', ListStyle::resolveType('BOGUS'));
    }

    public function testIsUnorderedDetectsTheMarkerTypesWithoutCounter(): void
    {
        $this->assertTrue(ListStyle::isUnordered('disc'));
        $this->assertTrue(ListStyle::isUnordered('CIRCLE'));
        $this->assertTrue(ListStyle::isUnordered('square'));
        $this->assertTrue(ListStyle::isUnordered('none'));
        $this->assertFalse(ListStyle::isUnordered('decimal'));
        $this->assertFalse(ListStyle::isUnordered('A'));
        $this->assertFalse(ListStyle::isUnordered('hebrew'));
    }

    public function testIsKnownTypeRejectsUnsupportedValues(): void
    {
        $this->assertTrue(ListStyle::isKnownType('A'));
        $this->assertTrue(ListStyle::isKnownType('upper-latin'));
        $this->assertTrue(ListStyle::isKnownType('DISC'));
        $this->assertTrue(ListStyle::isKnownType('none'));
        $this->assertTrue(ListStyle::isKnownType('decimal-leading-zero'));
        $this->assertTrue(ListStyle::isKnownType('cjk-ideographic'));
        $this->assertFalse(ListStyle::isKnownType(''));
        $this->assertFalse(ListStyle::isKnownType('bogus'));
        $this->assertFalse(ListStyle::isKnownType('true'));
        $this->assertFalse(ListStyle::isKnownType('inherit'));
        $this->assertFalse(ListStyle::isKnownType('!'));
        $this->assertFalse(ListStyle::isKnownType('img|png|4|4|icon.png'));
    }

    public function testResolveKnownTypeReturnsTheCanonicalKeywordOrNothing(): void
    {
        $this->assertSame('upper-alpha', ListStyle::resolveKnownType('A'));
        $this->assertSame('upper-alpha', ListStyle::resolveKnownType('upper-latin'));
        $this->assertSame('disc', ListStyle::resolveKnownType('DISC'));
        $this->assertSame('upper-armenian', ListStyle::resolveKnownType('armenian'));
        $this->assertSame('', ListStyle::resolveKnownType(''));
        $this->assertSame('', ListStyle::resolveKnownType('bogus'));
        $this->assertSame('', ListStyle::resolveKnownType('true'));
        $this->assertSame('', ListStyle::resolveKnownType('inherit'));
        $this->assertSame('', ListStyle::resolveKnownType('img|png|4|4|icon.png'));
    }

    public function testCounterTextWrapsTheLatinAlphabet(): void
    {
        $this->assertSame('a', ListStyle::counterText('lower-alpha', 1));
        $this->assertSame('z', ListStyle::counterText('lower-alpha', 26));
        $this->assertSame('aa', ListStyle::counterText('lower-alpha', 27));
        $this->assertSame('ab', ListStyle::counterText('lower-alpha', 28));
        $this->assertSame('az', ListStyle::counterText('lower-alpha', 52));
        $this->assertSame('ba', ListStyle::counterText('lower-alpha', 53));
        $this->assertSame('zz', ListStyle::counterText('lower-alpha', 702));
        $this->assertSame('aaa', ListStyle::counterText('lower-alpha', 703));
        $this->assertSame('AA', ListStyle::counterText('upper-alpha', 27));
        $this->assertSame('AAA', ListStyle::counterText('upper-alpha', 703));
    }

    public function testCounterTextResolvesTheAttributeValues(): void
    {
        $this->assertSame(ListStyle::counterText('upper-alpha', 27), ListStyle::counterText('A', 27));
        $this->assertSame(ListStyle::counterText('lower-alpha', 27), ListStyle::counterText('a', 27));
        $this->assertSame(ListStyle::counterText('upper-roman', 4), ListStyle::counterText('I', 4));
        $this->assertSame(ListStyle::counterText('lower-roman', 4), ListStyle::counterText('i', 4));
        $this->assertSame('7', ListStyle::counterText('1', 7));
    }

    public function testCounterTextSkipsTheGreekFinalSigma(): void
    {
        $this->assertSame("\u{03B1}", ListStyle::counterText('lower-greek', 1));
        $this->assertSame("\u{03C1}", ListStyle::counterText('lower-greek', 17));
        $this->assertSame("\u{03C3}", ListStyle::counterText('lower-greek', 18));
        $this->assertSame("\u{03C9}", ListStyle::counterText('lower-greek', 24));
        $this->assertSame("\u{03B1}\u{03B1}", ListStyle::counterText('lower-greek', 25));
    }

    public function testCounterTextUsesTheKanaSyllabaryOrder(): void
    {
        $this->assertSame("\u{3042}", ListStyle::counterText('hiragana', 1));
        $this->assertSame("\u{3044}", ListStyle::counterText('hiragana', 2));
        $this->assertSame("\u{3093}", ListStyle::counterText('hiragana', 48));
        $this->assertSame("\u{3042}\u{3042}", ListStyle::counterText('hiragana', 49));
        $this->assertSame("\u{3044}", ListStyle::counterText('hiragana-iroha', 1));
        $this->assertSame("\u{308D}", ListStyle::counterText('hiragana-iroha', 2));
        $this->assertSame("\u{30A2}", ListStyle::counterText('katakana', 1));
        $this->assertSame("\u{30A4}", ListStyle::counterText('katakana-iroha', 1));
        $this->assertSame("\u{30ED}", ListStyle::counterText('katakana-iroha', 2));
        $this->assertSame("\u{30B9}", ListStyle::counterText('katakana-iroha', 47));
    }

    public function testCounterTextBuildsTheAdditiveNumerals(): void
    {
        $this->assertSame("\u{05D9}\u{05D0}", ListStyle::counterText('hebrew', 11));
        $this->assertSame("\u{05D8}\u{05D5}", ListStyle::counterText('hebrew', 15));
        $this->assertSame("\u{05D8}\u{05D6}", ListStyle::counterText('hebrew', 16));
        $this->assertSame("\u{05D9}\u{05D6}", ListStyle::counterText('hebrew', 17));
        $this->assertSame("\u{05EA}", ListStyle::counterText('hebrew', 400));

        $this->assertSame("\u{054C}\u{054B}\u{0541}\u{0537}", ListStyle::counterText('upper-armenian', 1987));
        $this->assertSame("\u{057C}\u{057B}\u{0571}\u{0567}", ListStyle::counterText('lower-armenian', 1987));
        $this->assertSame("\u{10E9}\u{10E8}\u{10DE}\u{10D6}", ListStyle::counterText('georgian', 1987));

        $this->assertSame("\u{4E00}", ListStyle::counterText('cjk-ideographic', 1));
        $this->assertSame("\u{5341}\u{4E00}", ListStyle::counterText('cjk-ideographic', 11));
        $this->assertSame("\u{4E8C}\u{5341}", ListStyle::counterText('cjk-ideographic', 20));
        $this->assertSame("\u{767E}", ListStyle::counterText('cjk-ideographic', 100));
    }

    public function testCounterTextFallsBackToDecimalOutsideTheStyleRange(): void
    {
        $this->assertSame('11000', ListStyle::counterText('hebrew', 11000));
        $this->assertSame('10000', ListStyle::counterText('upper-armenian', 10000));
        $this->assertSame('10000', ListStyle::counterText('lower-armenian', 10000));
        $this->assertSame('20000', ListStyle::counterText('georgian', 20000));
        $this->assertSame('10000', ListStyle::counterText('cjk-ideographic', 10000));
        $this->assertSame('4000000000', ListStyle::counterText('upper-roman', 4_000_000_000));

        foreach (self::COUNTER_STYLES as $style) {
            $this->assertSame('0', ListStyle::counterText($style, 0), $style);
            $this->assertSame('-3', ListStyle::counterText($style, -3), $style);
        }
    }

    public function testCounterTextPadsTheLeadingZeroStyle(): void
    {
        $this->assertSame('01', ListStyle::counterText('decimal-leading-zero', 1));
        $this->assertSame('09', ListStyle::counterText('decimal-leading-zero', 9));
        $this->assertSame('10', ListStyle::counterText('decimal-leading-zero', 10));
        $this->assertSame('100', ListStyle::counterText('decimal-leading-zero', 100));
        $this->assertSame('-03', ListStyle::counterText('decimal-leading-zero', -3));
        $this->assertSame('00', ListStyle::counterText('decimal-leading-zero', 0));
    }

    public function testCounterTextUsesDecimalForTheUnknownStyles(): void
    {
        $this->assertSame('5', ListStyle::counterText('decimal', 5));
        $this->assertSame('5', ListStyle::counterText('bogus', 5));
        $this->assertSame('5', ListStyle::counterText('', 5));
        $this->assertSame('5', ListStyle::counterText('disc', 5));
    }

    public function testRomanUsesTheVinculumNotationAboveTheStandardRange(): void
    {
        $this->assertSame('I', ListStyle::roman(1));
        $this->assertSame('XIV', ListStyle::roman(14));
        $this->assertSame('MMMCMXCIX', ListStyle::roman(3999));
        $this->assertSame("I\u{0305}V\u{0305}", ListStyle::roman(4000));
        $this->assertSame("I\u{0305}V\u{0305}I", ListStyle::roman(4001));
        $this->assertStringNotContainsString('\u{', ListStyle::roman(4000));
        $this->assertSame('4000000000', ListStyle::roman(4_000_000_000));
        $this->assertSame('0', ListStyle::roman(0));
        $this->assertSame('-3', ListStyle::roman(-3));
        $this->assertSame(\strtolower(ListStyle::roman(4000)), ListStyle::counterText('lower-roman', 4000));
    }

    public function testPdfUaNumberingMapsTheStylesAndTagFallbacks(): void
    {
        $this->assertSame('UpperAlpha', ListStyle::pdfUaNumbering('A', 'ol'));
        $this->assertSame('LowerAlpha', ListStyle::pdfUaNumbering('a', 'ol'));
        $this->assertSame('UpperRoman', ListStyle::pdfUaNumbering('I', 'ol'));
        $this->assertSame('LowerRoman', ListStyle::pdfUaNumbering('i', 'ol'));
        $this->assertSame('Decimal', ListStyle::pdfUaNumbering('1', 'ol'));
        $this->assertSame('UpperAlpha', ListStyle::pdfUaNumbering('upper-latin', 'ol'));
        $this->assertSame('Disc', ListStyle::pdfUaNumbering('disc', 'ul'));
        $this->assertSame('Circle', ListStyle::pdfUaNumbering('circle', 'ul'));
        $this->assertSame('Square', ListStyle::pdfUaNumbering('SQUARE', 'ul'));
        $this->assertSame('None', ListStyle::pdfUaNumbering('none', 'ul'));
        $this->assertSame('Decimal', ListStyle::pdfUaNumbering('hebrew', 'ol'));
        $this->assertSame('Disc', ListStyle::pdfUaNumbering('', 'UL'));
        $this->assertSame('Decimal', ListStyle::pdfUaNumbering('', 'ol'));
        $this->assertSame('', ListStyle::pdfUaNumbering('', 'div'));
    }

    public function testCounterTextAlwaysReturnsValidUtf8(): void
    {
        $counts = [-3, 0, 1, 2, 25, 26, 27, 47, 48, 49, 100, 702, 703, 3999, 4000, 9999, 11000];
        foreach (self::COUNTER_STYLES as $style) {
            foreach ($counts as $count) {
                $text = ListStyle::counterText($style, $count);
                $this->assertNotSame('', $text, $style . ' ' . $count);
                $this->assertSame(1, \preg_match('//u', $text), $style . ' ' . $count);
            }
        }
    }

    /**
     * Counter styles with a symbol set.
     *
     * @var array<string>
     */
    private const COUNTER_STYLES = [
        'cjk-ideographic',
        'georgian',
        'hebrew',
        'hiragana',
        'hiragana-iroha',
        'katakana',
        'katakana-iroha',
        'lower-alpha',
        'lower-armenian',
        'lower-greek',
        'lower-roman',
        'upper-alpha',
        'upper-armenian',
        'upper-roman',
    ];
}
