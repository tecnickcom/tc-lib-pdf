<?php

/**
 * AsciiPatternTest.php
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

use Com\Tecnick\Pdf\AsciiPattern;
use PHPUnit\Framework\TestCase;

/**
 * ASCII character sets and case-insensitive matching helpers.
 *
 * @since       2026-11-20
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class AsciiPatternTest extends TestCase
{
    /**
     * @return list<int>
     */
    private static function matchedBytes(string $pattern): array
    {
        return \array_values(\array_filter(
            \range(0, 255),
            static fn(int $ord): bool => \preg_match($pattern, \chr($ord)) === 1,
        ));
    }

    public function testCharacterSets(): void
    {
        $this->assertSame([9, 10, 11, 12, 13, 32], self::matchedBytes('/^[' . AsciiPattern::SPACE . ']$/'));
        $word = [...\range(48, 57), ...\range(65, 90), 95, ...\range(97, 122)];
        $this->assertSame($word, self::matchedBytes('/^[' . AsciiPattern::WORD . ']$/'));
        $this->assertSame(
            [...\range(65, 90), ...\range(97, 122)],
            self::matchedBytes('/^[' . AsciiPattern::ALPHA . ']$/'),
        );
        $this->assertSame(
            [...\range(48, 57), ...\range(65, 90), ...\range(97, 122)],
            self::matchedBytes('/^[' . AsciiPattern::ALNUM . ']$/'),
        );
    }

    public function testIsSpaceAndIsLower(): void
    {
        $spaces = [];
        $lower = [];
        for ($ord = 0; $ord < 256; ++$ord) {
            if (AsciiPattern::isSpace(\chr($ord))) {
                $spaces[] = $ord;
            }

            if (AsciiPattern::isLower(\chr($ord))) {
                $lower[] = $ord;
            }
        }

        $this->assertSame([9, 10, 11, 12, 13, 32], $spaces);
        $this->assertSame(\range(97, 122), $lower);
        $this->assertFalse(AsciiPattern::isSpace(''));
        $this->assertFalse(AsciiPattern::isSpace('  '));
        $this->assertFalse(AsciiPattern::isLower(''));
        $this->assertFalse(AsciiPattern::isLower('ab'));
    }

    public function testMatchFoldedKeepsTheOriginalCase(): void
    {
        $matches = [];
        $this->assertSame(1, AsciiPattern::matchFolded('/url\(([^)]*)\)/', 'fill: URL(#MyGradient)', $matches));
        $this->assertSame(['URL(#MyGradient)', '#MyGradient'], $matches);

        $this->assertSame(0, AsciiPattern::matchFolded('/url\(/', 'fill: none', $matches));
        $this->assertSame([], $matches);
    }

    public function testMatchFoldedOffsetsAfterMultibyteText(): void
    {
        $matches = [];
        $subject = "Voil\u{00E0} \u{0160}\u{30E0} <IMG SRC=\"Pic.PNG\">";
        $this->assertSame(1, AsciiPattern::matchFolded('/<img src="([^"]*)">/', $subject, $matches));
        $this->assertSame(['<IMG SRC="Pic.PNG">', 'Pic.PNG'], $matches);
    }

    public function testMatchFoldedUnsetGroups(): void
    {
        $matches = [];
        $this->assertSame(1, AsciiPattern::matchFolded('/(a)?(b)(c)?/', 'xBc', $matches));
        $this->assertSame(['Bc', '', 'B', 'c'], $matches);
    }

    public function testMatchAllFolded(): void
    {
        $matches = [];
        $subject = '@Import "One.css"; @IMPORT "Two.css";';
        $this->assertSame(2, AsciiPattern::matchAllFolded('/@import "([^"]+)";/', $subject, $matches));
        $this->assertSame(
            [
                ['@Import "One.css";', '@IMPORT "Two.css";'],
                ['One.css',            'Two.css'],
            ],
            $matches,
        );

        $this->assertSame(2, AsciiPattern::matchAllFolded('/@import "([^"]+)";/', $subject, $matches, \PREG_SET_ORDER));
        $this->assertSame(
            [
                ['@Import "One.css";', 'One.css'],
                ['@IMPORT "Two.css";', 'Two.css'],
            ],
            $matches,
        );

        $this->assertSame(0, AsciiPattern::matchAllFolded('/@media/', $subject, $matches));
        $this->assertSame([[]], $matches);
    }

    public function testReplaceFoldedKeepsTheTextOutsideTheMatches(): void
    {
        $out = AsciiPattern::replaceFolded(
            '/<textarea([^>]*)>([^<]*)<\/textarea>/',
            static fn(array $mtc): string => '[' . ($mtc[1] ?? '') . '|' . ($mtc[2] ?? '') . ']',
            "\u{00C0} <TextArea Name=\"A\">Hello World</TEXTAREA> \u{30E0} <textarea>X</textarea>.",
        );
        $this->assertSame("\u{00C0} [ Name=\"A\"|Hello World] \u{30E0} [|X].", $out);
    }

    public function testReplaceFoldedLimit(): void
    {
        $remove = static fn(): string => '';
        $this->assertSame('a B c', AsciiPattern::replaceFolded('/!important/', $remove, 'a!IMPORTANT B!Important c'));
        $this->assertSame('a B!Important c', AsciiPattern::replaceFolded(
            '/!important/',
            $remove,
            'a!IMPORTANT B!Important c',
            1,
        ));
        $this->assertSame('abc', AsciiPattern::replaceFolded('/x/', $remove, 'abc', 0));
    }
}
