<?php

/**
 * TextVAlignTest.php
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

use Com\Tecnick\Pdf\TextVAlign;

/**
 * TextVAlign enum test
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class TextVAlignTest extends TestUtil
{
    public function testCaseBackingValues(): void
    {
        $this->assertSame('T', TextVAlign::Top->value);
        $this->assertSame('C', TextVAlign::Center->value);
        $this->assertSame('B', TextVAlign::Bottom->value);
        $this->assertSame('A', TextVAlign::Ascent->value);
        $this->assertSame('L', TextVAlign::Baseline->value);
        $this->assertSame('D', TextVAlign::Descent->value);
    }

    public function testFromLooseCanonical(): void
    {
        $this->assertSame(TextVAlign::Top, TextVAlign::fromLoose('t'));
        $this->assertSame(TextVAlign::Descent, TextVAlign::fromLoose('d'));
        $this->assertSame(TextVAlign::Ascent, TextVAlign::fromLoose('A'));
    }

    public function testFromLoosePassesThroughEnumInstance(): void
    {
        $this->assertSame(TextVAlign::Baseline, TextVAlign::fromLoose(TextVAlign::Baseline));
    }

    public function testFromLooseRoundTrip(): void
    {
        foreach (TextVAlign::cases() as $case) {
            $this->assertSame($case, TextVAlign::fromLoose($case->value));
        }
    }

    public function testFromLooseUnknownFallsBack(): void
    {
        $this->assertSame(TextVAlign::Center, TextVAlign::fromLoose('Z'));
        $this->assertSame(TextVAlign::Center, TextVAlign::fromLoose(''));
    }
}
