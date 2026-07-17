<?php

/**
 * CacheTypeTest.php
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

use Com\Tecnick\Pdf\Cache\CacheType;

/**
 * CacheType enum test
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class CacheTypeTest extends TestUtil
{
    public function testCaseBackingValues(): void
    {
        $this->assertSame('font', CacheType::Font->value);
        $this->assertSame('image', CacheType::Image->value);
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseCanonical(): void
    {
        $this->assertSame(CacheType::Font, CacheType::fromLoose('font'));
        $this->assertSame(CacheType::Image, CacheType::fromLoose('image'));
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLoosePassesThroughEnumInstance(): void
    {
        $this->assertSame(CacheType::Font, CacheType::fromLoose(CacheType::Font));
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseRoundTrip(): void
    {
        foreach (CacheType::cases() as $case) {
            $this->assertSame($case, CacheType::fromLoose($case->value));
        }
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseUnknownThrows(): void
    {
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);
        CacheType::fromLoose('svg');
    }
}
