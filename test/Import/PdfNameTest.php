<?php

/**
 * PdfNameTest.php
 *
 * @since       2026-09-26
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test\Import;

use Com\Tecnick\Pdf\Import\PdfName;
use PHPUnit\Framework\TestCase;

class PdfNameTest extends TestCase
{
    public function testTokenKeepsRegularCharacters(): void
    {
        $this->assertSame('/FlateDecode', PdfName::token('FlateDecode'));
        $this->assertSame('/A-B_C.D;E+F!G*H~', PdfName::token('A-B_C.D;E+F!G*H~'));
        $this->assertSame('/13', PdfName::token('13'));
    }

    public function testTokenReturnsTheEmptyName(): void
    {
        $this->assertSame('/', PdfName::token(''));
    }

    public function testTokenEscapesWhiteSpace(): void
    {
        $this->assertSame('/Spot#20Color', PdfName::token('Spot Color'));
        $this->assertSame('/HKS#2013', PdfName::token('HKS 13'));
        $this->assertSame('/A#09B#0AC#0DD#0CE', PdfName::token("A\tB\nC\rD\fE"));
    }

    public function testTokenEscapesDelimitersAndNumberSign(): void
    {
        $this->assertSame('/#23#25#28#29#2F#3C#3E#5B#5D#7B#7D', PdfName::token('#%()/<>[]{}'));
    }

    public function testTokenEscapesALeadingSolidus(): void
    {
        $this->assertSame('/#2FG1', PdfName::token('/G1'));
    }

    public function testTokenEscapesControlAndHighBytes(): void
    {
        $this->assertSame('/#00#7F#80#C3#A9#FF', PdfName::token("\x00\x7F\x80\xC3\xA9\xFF"));
    }

    public function testTokenAcceptsIntegerNames(): void
    {
        $this->assertSame('/9', PdfName::token(9));
    }
}
