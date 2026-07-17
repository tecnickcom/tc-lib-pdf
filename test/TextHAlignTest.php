<?php

/**
 * TextHAlignTest.php
 *
 * @since       2026-07-17
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

use Com\Tecnick\Pdf\TextHAlign;

/**
 * TextHAlign enum test
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class TextHAlignTest extends TestUtil
{
    public function testCaseBackingValues(): void
    {
        $this->assertSame('L', TextHAlign::Left->value);
        $this->assertSame('C', TextHAlign::Center->value);
        $this->assertSame('R', TextHAlign::Right->value);
        $this->assertSame('J', TextHAlign::Justify->value);
    }

    public function testFromLooseCanonical(): void
    {
        $this->assertSame(TextHAlign::Left, TextHAlign::fromLoose('l'));
        $this->assertSame(TextHAlign::Right, TextHAlign::fromLoose('R'));
        $this->assertSame(TextHAlign::Justify, TextHAlign::fromLoose(' j '));
    }

    public function testFromLoosePassesThroughEnumInstance(): void
    {
        $this->assertSame(TextHAlign::Justify, TextHAlign::fromLoose(TextHAlign::Justify));
    }

    public function testFromLooseRoundTrip(): void
    {
        foreach (TextHAlign::cases() as $case) {
            $this->assertSame($case, TextHAlign::fromLoose($case->value));
        }
    }

    public function testFromLooseUnknownFallsBack(): void
    {
        $this->assertSame(TextHAlign::Left, TextHAlign::fromLoose('Z'));
        $this->assertSame(TextHAlign::Left, TextHAlign::fromLoose(''));
    }
}
