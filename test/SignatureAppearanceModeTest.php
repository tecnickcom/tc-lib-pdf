<?php

/**
 * SignatureAppearanceModeTest.php
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

use Com\Tecnick\Pdf\Signature\SignatureAppearanceMode;

/**
 * SignatureAppearanceMode enum test
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class SignatureAppearanceModeTest extends TestUtil
{
    public function testCaseBackingValues(): void
    {
        $this->assertSame('N', SignatureAppearanceMode::Normal->value);
        $this->assertSame('R', SignatureAppearanceMode::Rollover->value);
        $this->assertSame('D', SignatureAppearanceMode::Down->value);
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseCanonicalAndCaseInsensitive(): void
    {
        $this->assertSame(SignatureAppearanceMode::Normal, SignatureAppearanceMode::fromLoose('N'));
        $this->assertSame(SignatureAppearanceMode::Rollover, SignatureAppearanceMode::fromLoose('r'));
        $this->assertSame(SignatureAppearanceMode::Down, SignatureAppearanceMode::fromLoose('D'));
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLoosePassesThroughEnumInstance(): void
    {
        $this->assertSame(
            SignatureAppearanceMode::Rollover,
            SignatureAppearanceMode::fromLoose(SignatureAppearanceMode::Rollover),
        );
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseRoundTrip(): void
    {
        foreach (SignatureAppearanceMode::cases() as $case) {
            $this->assertSame($case, SignatureAppearanceMode::fromLoose($case->value));
        }
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseUnknownThrows(): void
    {
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);
        SignatureAppearanceMode::fromLoose('X');
    }
}
