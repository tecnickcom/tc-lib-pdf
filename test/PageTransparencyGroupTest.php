<?php

/**
 * PageTransparencyGroupTest.php
 *
 * @since       2026-06-19
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

use Com\Tecnick\Pdf\Exception as PdfException;
use Com\Tecnick\Pdf\Tcpdf;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the per-page transparency /Group mode (issue #243): standard pages
 * declare a transparency group, which is omitted automatically for fully-opaque
 * pages ('auto'), always kept ('always') or always dropped ('never').
 */
class PageTransparencyGroupTest extends TestCase
{
    /** Exact per-page transparency group token emitted on standard pages. */
    private const PAGE_GROUP = '/Group << /Type /Group /S /Transparency /CS /DeviceRGB >>';

    /** Path to a fixture containing alpha/blend ExtGState content. */
    private string $transparencyPdf;

    /**
     * @throws \Throwable
     */
    protected function setUp(): void
    {
        if (!\defined('K_PATH_FONTS')) {
            $fonts = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts');
            \define('K_PATH_FONTS', $fonts);
        }

        $this->transparencyPdf = __DIR__ . '/fixtures/transparency_import.pdf';
    }

    /**
     * @throws \Throwable
     */
    private function makePdf(): Tcpdf
    {
        $pdf = new Tcpdf();
        $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        return $pdf;
    }

    /**
     * Adds a page and paints fully-opaque content (no transparency operators).
     *
     * @throws \Throwable
     */
    private function addOpaquePage(Tcpdf $pdf): void
    {
        $pdf->addPage();
        $pdf->page->addContent('0 0 100 100 re f');
    }

    /**
     * Adds a page and paints content behind a real (sub-1) constant alpha.
     *
     * @throws \Throwable
     */
    private function addTransparentPage(Tcpdf $pdf): void
    {
        $pdf->addPage();
        $pdf->page->addContent($pdf->graph->getAlpha(0.5));
        $pdf->page->addContent('0 0 100 100 re f');
    }

    /**
     * @throws \Throwable
     */
    public function testAutoModeOmitsGroupOnFullyOpaquePage(): void
    {
        $pdf = $this->makePdf();
        $this->addOpaquePage($pdf);

        $raw = $pdf->getOutPDFString();
        $this->assertStringNotContainsString(self::PAGE_GROUP, $raw);
    }

    /**
     * @throws \Throwable
     */
    public function testAutoModeKeepsGroupOnTransparentPage(): void
    {
        $pdf = $this->makePdf();
        $this->addTransparentPage($pdf);

        $raw = $pdf->getOutPDFString();
        $this->assertStringContainsString(self::PAGE_GROUP, $raw);
    }

    /**
     * @throws \Throwable
     */
    public function testAutoModeDecidesPerPage(): void
    {
        $pdf = $this->makePdf();
        $this->addOpaquePage($pdf); // page 0: opaque -> no group
        $this->addTransparentPage($pdf); // page 1: alpha  -> group
        $this->addOpaquePage($pdf); // page 2: opaque -> no group

        $raw = $pdf->getOutPDFString();
        $this->assertSame(1, \substr_count($raw, self::PAGE_GROUP));
    }

    /**
     * @throws \Throwable
     */
    public function testAlwaysModeKeepsGroupOnOpaquePages(): void
    {
        $pdf = $this->makePdf();
        $pdf->setPageTransparencyGroup('always');
        $this->addOpaquePage($pdf);
        $this->addOpaquePage($pdf);

        $raw = $pdf->getOutPDFString();
        $this->assertSame(2, \substr_count($raw, self::PAGE_GROUP));
    }

    /**
     * @throws \Throwable
     */
    public function testNeverModeOmitsGroupEvenOnTransparentPage(): void
    {
        $pdf = $this->makePdf();
        $pdf->setPageTransparencyGroup('never');
        $this->addTransparentPage($pdf);

        $raw = $pdf->getOutPDFString();
        $this->assertStringNotContainsString(self::PAGE_GROUP, $raw);
    }

    /**
     * @throws \Throwable
     */
    public function testSetPageTransparencyGroupIsCaseInsensitiveAndChainable(): void
    {
        $pdf = $this->makePdf();
        $this->assertSame($pdf, $pdf->setPageTransparencyGroup('NEVER'));
        $this->addTransparentPage($pdf);

        $raw = $pdf->getOutPDFString();
        $this->assertStringNotContainsString(self::PAGE_GROUP, $raw);
    }

    /**
     * @throws \Throwable
     */
    public function testInvalidModeThrows(): void
    {
        $pdf = $this->makePdf();
        $this->expectException(PdfException::class);
        $pdf->setPageTransparencyGroup('flatten');
    }

    /**
     * A page that paints an imported page is treated conservatively: the
     * imported content may blend, so the page keeps its transparency group.
     *
     * @throws \Throwable
     */
    public function testImportedPageKeepsGroupInAutoMode(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->transparencyPdf);
        $tpl = $pdf->importPage($srcId, 1);
        $pdf->addPage();
        $pdf->useImportedPage($tpl, 10, 10, 120, 80, ['keepAspectRatio' => false]);

        $raw = $pdf->getOutPDFString();
        $this->assertStringContainsString(self::PAGE_GROUP, $raw);
    }

    /**
     * @throws \Throwable
     */
    public function testPdfaStillSuppressesGroupRegardlessOfMode(): void
    {
        $pdf = new Tcpdf(mode: 'pdfa2b');
        $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->setPageTransparencyGroup('always');
        $this->addTransparentPage($pdf);

        $raw = $pdf->getOutPDFString();
        $this->assertStringNotContainsString(self::PAGE_GROUP, $raw);
    }
}
