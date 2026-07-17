<?php

/**
 * PdfConformanceTest.php
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

use Com\Tecnick\Pdf\PdfConformance;

/**
 * PdfConformance enum test
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class PdfConformanceTest extends TestUtil
{
    public function testCaseBackingValues(): void
    {
        $this->assertSame('', PdfConformance::None->value);
        $this->assertSame('pdfa1', PdfConformance::Pdfa1->value);
        $this->assertSame('pdfa1a', PdfConformance::Pdfa1a->value);
        $this->assertSame('pdfa1b', PdfConformance::Pdfa1b->value);
        $this->assertSame('pdfa2', PdfConformance::Pdfa2->value);
        $this->assertSame('pdfa2a', PdfConformance::Pdfa2a->value);
        $this->assertSame('pdfa2b', PdfConformance::Pdfa2b->value);
        $this->assertSame('pdfa2u', PdfConformance::Pdfa2u->value);
        $this->assertSame('pdfa3', PdfConformance::Pdfa3->value);
        $this->assertSame('pdfa3a', PdfConformance::Pdfa3a->value);
        $this->assertSame('pdfa3b', PdfConformance::Pdfa3b->value);
        $this->assertSame('pdfa3u', PdfConformance::Pdfa3u->value);
        $this->assertSame('pdfx', PdfConformance::Pdfx->value);
        $this->assertSame('pdfx1a', PdfConformance::Pdfx1a->value);
        $this->assertSame('pdfx3', PdfConformance::Pdfx3->value);
        $this->assertSame('pdfx4', PdfConformance::Pdfx4->value);
        $this->assertSame('pdfx5', PdfConformance::Pdfx5->value);
        $this->assertSame('pdfua', PdfConformance::Pdfua->value);
        $this->assertSame('pdfua1', PdfConformance::Pdfua1->value);
        $this->assertSame('pdfua2', PdfConformance::Pdfua2->value);
    }

    public function testFromLooseCanonical(): void
    {
        $this->assertSame(PdfConformance::Pdfa1b, PdfConformance::fromLoose('PDFA1B'));
        $this->assertSame(PdfConformance::Pdfx4, PdfConformance::fromLoose(' pdfx4 '));
        $this->assertSame(PdfConformance::None, PdfConformance::fromLoose(''));
    }

    public function testFromLoosePassesThroughEnumInstance(): void
    {
        $this->assertSame(PdfConformance::Pdfa3b, PdfConformance::fromLoose(PdfConformance::Pdfa3b));
    }

    public function testFromLooseRoundTrip(): void
    {
        foreach (PdfConformance::cases() as $case) {
            $this->assertSame($case, PdfConformance::fromLoose($case->value));
        }
    }

    public function testFromLooseUnknownFallsBack(): void
    {
        $this->assertSame(PdfConformance::None, PdfConformance::fromLoose('pdfa9'));
        $this->assertSame(PdfConformance::None, PdfConformance::fromLoose('nope'));
    }
}
