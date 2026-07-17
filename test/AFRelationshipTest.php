<?php

/**
 * AFRelationshipTest.php
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

use Com\Tecnick\Pdf\AFRelationship;

/**
 * AFRelationship enum test
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class AFRelationshipTest extends TestUtil
{
    public function testCaseBackingValues(): void
    {
        $this->assertSame('Source', AFRelationship::Source->value);
        $this->assertSame('Data', AFRelationship::Data->value);
        $this->assertSame('Alternative', AFRelationship::Alternative->value);
        $this->assertSame('Supplement', AFRelationship::Supplement->value);
        $this->assertSame('Unspecified', AFRelationship::Unspecified->value);
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseCanonical(): void
    {
        $this->assertSame(AFRelationship::Source, AFRelationship::fromLoose('Source'));
        $this->assertSame(AFRelationship::Supplement, AFRelationship::fromLoose('Supplement'));
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLoosePassesThroughEnumInstance(): void
    {
        $this->assertSame(AFRelationship::Data, AFRelationship::fromLoose(AFRelationship::Data));
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseRoundTrip(): void
    {
        foreach (AFRelationship::cases() as $case) {
            $this->assertSame($case, AFRelationship::fromLoose($case->value));
        }
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseUnknownThrows(): void
    {
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);
        AFRelationship::fromLoose('Nope');
    }
}
