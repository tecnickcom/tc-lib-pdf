<?php

/**
 * HybridConformanceTest.php
 *
 * @since       2026-08-31
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

use Com\Tecnick\Pdf\HybridConformance;

/**
 * HybridConformance enum test
 *
 * @since       2026-08-31
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class HybridConformanceTest extends TestUtil
{
    public function testCaseBackingValues(): void
    {
        $this->assertSame('MINIMUM', HybridConformance::Minimum->value);
        $this->assertSame('BASIC WL', HybridConformance::BasicWl->value);
        $this->assertSame('BASIC', HybridConformance::Basic->value);
        $this->assertSame('COMFORT', HybridConformance::Comfort->value);
        $this->assertSame('EN 16931', HybridConformance::En16931->value);
        $this->assertSame('EXTENDED', HybridConformance::Extended->value);
        $this->assertSame('XRECHNUNG', HybridConformance::XRechnung->value);
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseRoundTrip(): void
    {
        foreach (HybridConformance::cases() as $case) {
            $this->assertSame($case, HybridConformance::fromLoose($case->value));
            $this->assertSame($case, HybridConformance::fromLoose($case));
        }
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseIgnoresCaseAndSpaces(): void
    {
        $this->assertSame(HybridConformance::En16931, HybridConformance::fromLoose('en16931'));
        $this->assertSame(HybridConformance::En16931, HybridConformance::fromLoose(' EN 16931 '));
        $this->assertSame(HybridConformance::BasicWl, HybridConformance::fromLoose('basicwl'));
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseUnknownThrows(): void
    {
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);
        HybridConformance::fromLoose('nope');
    }
}
