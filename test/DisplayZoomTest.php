<?php

/**
 * DisplayZoomTest.php
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

use Com\Tecnick\Pdf\DisplayZoom;

/**
 * DisplayZoom enum test
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class DisplayZoomTest extends TestUtil
{
    public function testCaseBackingValues(): void
    {
        $this->assertSame('fullpage', DisplayZoom::FullPage->value);
        $this->assertSame('fullwidth', DisplayZoom::FullWidth->value);
        $this->assertSame('real', DisplayZoom::Real->value);
        $this->assertSame('default', DisplayZoom::DefaultZoom->value);
    }

    public function testFromLooseCanonical(): void
    {
        $this->assertSame(DisplayZoom::FullPage, DisplayZoom::fromLoose('fullpage'));
        $this->assertSame(DisplayZoom::Real, DisplayZoom::fromLoose('real'));
    }

    public function testFromLoosePassesThroughEnumInstance(): void
    {
        $this->assertSame(DisplayZoom::Real, DisplayZoom::fromLoose(DisplayZoom::Real));
    }

    public function testFromLooseRoundTrip(): void
    {
        foreach (DisplayZoom::cases() as $case) {
            $this->assertSame($case, DisplayZoom::fromLoose($case->value));
        }
    }

    public function testFromLooseUnknownFallsBack(): void
    {
        $this->assertSame(DisplayZoom::DefaultZoom, DisplayZoom::fromLoose('FullPage'));
        $this->assertSame(DisplayZoom::DefaultZoom, DisplayZoom::fromLoose('zoom'));
        $this->assertSame(DisplayZoom::DefaultZoom, DisplayZoom::fromLoose(''));
    }
}
