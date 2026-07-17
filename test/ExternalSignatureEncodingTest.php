<?php

/**
 * ExternalSignatureEncodingTest.php
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

use Com\Tecnick\Pdf\Signature\ExternalSignatureEncoding;

/**
 * ExternalSignatureEncoding enum test
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class ExternalSignatureEncodingTest extends TestUtil
{
    public function testCaseBackingValues(): void
    {
        $this->assertSame('binary', ExternalSignatureEncoding::Binary->value);
        $this->assertSame('base64', ExternalSignatureEncoding::Base64->value);
        $this->assertSame('hex', ExternalSignatureEncoding::Hex->value);
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseCanonical(): void
    {
        $this->assertSame(ExternalSignatureEncoding::Hex, ExternalSignatureEncoding::fromLoose('HEX'));
        $this->assertSame(ExternalSignatureEncoding::Base64, ExternalSignatureEncoding::fromLoose(' base64 '));
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLoosePassesThroughEnumInstance(): void
    {
        $this->assertSame(
            ExternalSignatureEncoding::Hex,
            ExternalSignatureEncoding::fromLoose(ExternalSignatureEncoding::Hex),
        );
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseRoundTrip(): void
    {
        foreach (ExternalSignatureEncoding::cases() as $case) {
            $this->assertSame($case, ExternalSignatureEncoding::fromLoose($case->value));
        }
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseUnknownThrows(): void
    {
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);
        ExternalSignatureEncoding::fromLoose('rot13');
    }
}
