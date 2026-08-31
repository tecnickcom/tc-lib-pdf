<?php

/**
 * HybridProfileTest.php
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

use Com\Tecnick\Pdf\HybridProfile;

/**
 * HybridProfile enum test
 *
 * @since       2026-08-31
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class HybridProfileTest extends TestUtil
{
    public function testFacturXProfileValues(): void
    {
        $profile = HybridProfile::FacturX;

        $this->assertSame('factur-x.xml', $profile->fileName());
        $this->assertSame('urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#', $profile->namespaceUri());
        $this->assertSame('fx', $profile->prefix());
        $this->assertSame('1.0', $profile->version());
        $this->assertSame('Factur-X PDFA Extension Schema', $profile->schemaName());
        $this->assertSame('INVOICE', $profile->documentType());
    }

    public function testZugferdProfileValues(): void
    {
        $this->assertSame('ZUGFeRD-invoice.xml', HybridProfile::ZugferdV1->fileName());
        $this->assertSame('urn:ferd:pdfa:CrossIndustryDocument:invoice:1p0#', HybridProfile::ZugferdV1->namespaceUri());
        $this->assertSame('zugferd-invoice.xml', HybridProfile::ZugferdV2->fileName());
        $this->assertSame('2p0', HybridProfile::ZugferdV2->version());
        $this->assertSame('zf', HybridProfile::ZugferdV2->prefix());
    }

    public function testOrderXDocumentTypeIsOrder(): void
    {
        $this->assertSame('ORDER', HybridProfile::OrderX->documentType());
        $this->assertSame('order-x.xml', HybridProfile::OrderX->fileName());
    }

    public function testEveryCaseHasNonEmptyMetadata(): void
    {
        foreach (HybridProfile::cases() as $case) {
            $this->assertNotSame('', $case->fileName());
            $this->assertNotSame('', $case->namespaceUri());
            $this->assertNotSame('', $case->prefix());
            $this->assertNotSame('', $case->version());
            $this->assertNotSame('', $case->schemaName());
            $this->assertNotSame('', $case->documentType());
            $this->assertNotSame('', $case->description());
        }
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseRoundTrip(): void
    {
        foreach (HybridProfile::cases() as $case) {
            $this->assertSame($case, HybridProfile::fromLoose($case->value));
            $this->assertSame($case, HybridProfile::fromLoose($case));
        }
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseIsCaseInsensitiveAndTrimmed(): void
    {
        $this->assertSame(HybridProfile::FacturX, HybridProfile::fromLoose('  FacturX '));
    }

    /**
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function testFromLooseUnknownThrows(): void
    {
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);
        HybridProfile::fromLoose('nope');
    }
}
