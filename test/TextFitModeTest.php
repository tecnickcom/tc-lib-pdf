<?php

/**
 * TextFitModeTest.php
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

use Com\Tecnick\Pdf\TextFitMode;

/**
 * TextFitMode enum test
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class TextFitModeTest extends TestUtil
{
    public function testCaseBackingValues(): void
    {
        $this->assertSame('', TextFitMode::Off->value);
        $this->assertSame('T', TextFitMode::Truncate->value);
        $this->assertSame('S', TextFitMode::Stretch->value);
        $this->assertSame('F', TextFitMode::ShrinkFont->value);
    }

    public function testFromLooseCanonical(): void
    {
        $this->assertSame(TextFitMode::Truncate, TextFitMode::fromLoose('t'));
        $this->assertSame(TextFitMode::ShrinkFont, TextFitMode::fromLoose(' F '));
        $this->assertSame(TextFitMode::Off, TextFitMode::fromLoose(''));
    }

    public function testFromLoosePassesThroughEnumInstance(): void
    {
        $this->assertSame(TextFitMode::Stretch, TextFitMode::fromLoose(TextFitMode::Stretch));
    }

    public function testFromLooseRoundTrip(): void
    {
        foreach (TextFitMode::cases() as $case) {
            $this->assertSame($case, TextFitMode::fromLoose($case->value));
        }
    }

    public function testFromLooseUnknownFallsBack(): void
    {
        $this->assertSame(TextFitMode::Off, TextFitMode::fromLoose('X'));
        $this->assertSame(TextFitMode::Off, TextFitMode::fromLoose('wrap'));
    }
}
