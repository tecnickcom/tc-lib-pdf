<?php

/**
 * TcpdfImporterFacadeTest.php
 *
 * @since       2026-04-25
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

use Com\Tecnick\Pdf\Import\ImportPageOutOfRangeException;
use Com\Tecnick\Pdf\Import\ImportSourceNotFoundException;
use Com\Tecnick\Pdf\Import\ImportUnsupportedFeatureException;
use Com\Tecnick\Pdf\Import\PageTemplate;
use Com\Tecnick\Pdf\Tcpdf;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the PDF import facade methods on Tcpdf.
 */
class TcpdfImporterFacadeTest extends TestCase
{
    /** Path to the single-page test fixture. */
    private string $simplePdf;

    /** Path to the two-page test fixture with a shared font. */
    private string $multipagePdf;

    /** Path to a fixture with explicit Media/Crop/Bleed/Trim/Art boxes. */
    private string $boxOptionsPdf;

    /** Path to a fixture with /Rotate 90 on the page dictionary. */
    private string $rotatedPdf;

    /** Path to a fixture containing alpha/blend ExtGState content. */
    private string $transparencyPdf;

    /** Path to a fixture with an /Encrypt trailer entry. */
    private string $encryptedPdf;

    protected function setUp(): void
    {
        if (!\defined('K_PATH_FONTS')) {
            $fonts = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts');
            \define('K_PATH_FONTS', $fonts);
        }

        $this->simplePdf = __DIR__ . '/fixtures/simple_import.pdf';
        $this->multipagePdf = __DIR__ . '/fixtures/multipage_import.pdf';
        $this->boxOptionsPdf = __DIR__ . '/fixtures/box_options_import.pdf';
        $this->rotatedPdf = __DIR__ . '/fixtures/rotated_import.pdf';
        $this->transparencyPdf = __DIR__ . '/fixtures/transparency_import.pdf';
        $this->encryptedPdf = __DIR__ . '/fixtures/encrypted_import_stub.pdf';
    }

    // ------------------------------------------------------------------ helpers

    private function makePdf(): Tcpdf
    {
        $pdf = new Tcpdf();
        // A default font must be inserted before any addPage call so that
        // setPageContext / getOutCurrentFont does not receive a null font key.
        $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        return $pdf;
    }

    private function makePdfWithMode(string $mode): Tcpdf
    {
        $pdf = new Tcpdf(mode: $mode);
        $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        return $pdf;
    }

    private function getPdfVersion(Tcpdf $pdf): string
    {
        $ref = new \ReflectionClass($pdf);
        while ($ref !== false) {
            if ($ref->hasProperty('pdfver')) {
                $prop = $ref->getProperty('pdfver');
                $prop->setAccessible(true);
                $val = $prop->getValue($pdf);
                return \is_string($val) ? $val : '';
            }

            $ref = $ref->getParentClass();
        }

        return '';
    }

    // ------------------------------------------------------------------ setImportSourceFile / setImportSourceData

    public function testSetImportSourceFileReturnsNonEmptyId(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $this->assertNotEmpty($srcId);
    }

    public function testSetImportSourceDataReturnsNonEmptyId(): void
    {
        $pdf = $this->makePdf();
        $data = (string) \file_get_contents($this->simplePdf);
        $srcId = $pdf->setImportSourceData($data);
        $this->assertNotEmpty($srcId);
    }

    public function testSetImportSourceFileThrowsForMissingFile(): void
    {
        $pdf = $this->makePdf();
        $this->expectException(ImportSourceNotFoundException::class);
        $pdf->setImportSourceFile('/nonexistent/path.pdf');
    }

    public function testSetImportSourceFileThrowsForEncryptedPdf(): void
    {
        $pdf = $this->makePdf();
        $this->expectException(ImportUnsupportedFeatureException::class);
        $this->expectExceptionMessage('encrypted PDF');
        $pdf->setImportSourceFile($this->encryptedPdf);
    }

    public function testSetImportSourceFileWithPasswordThrowsActionableEncryptedError(): void
    {
        $pdf = $this->makePdf();
        $this->expectException(ImportUnsupportedFeatureException::class);
        $this->expectExceptionMessage('password-based import is not supported');
        $pdf->setImportSourceFile($this->encryptedPdf, ['password' => 'secret']);
    }

    // ------------------------------------------------------------------ getSourcePageCount

    public function testGetSourcePageCountSimple(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $this->assertSame(1, $pdf->getSourcePageCount($srcId));
    }

    public function testGetSourcePageCountMultipage(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $this->assertSame(2, $pdf->getSourcePageCount($srcId));
    }

    // ------------------------------------------------------------------ importPage / importPages

    public function testImportPageReturnsPageTemplate(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $tpl = $pdf->importPage($srcId, 1);
        $this->assertInstanceOf(PageTemplate::class, $tpl);
        $this->assertGreaterThan(0.0, $tpl->getWidth());
        $this->assertGreaterThan(0.0, $tpl->getHeight());
    }

    public function testImportPageThrowsForOutOfRange(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $this->expectException(ImportPageOutOfRangeException::class);
        $pdf->importPage($srcId, 99);
    }

    public function testImportPageUsesTrimBoxWhenRequested(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->boxOptionsPdf);
        $tpl = $pdf->importPage($srcId, 1, ['box' => 'TrimBox']);
        $this->assertEqualsWithDelta(460.0, $tpl->getWidth(), 0.01);
        $this->assertEqualsWithDelta(660.0, $tpl->getHeight(), 0.01);
    }

    public function testImportPageUsesArtBoxWhenRequested(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->boxOptionsPdf);
        $tpl = $pdf->importPage($srcId, 1, ['box' => 'ArtBox']);
        $this->assertEqualsWithDelta(440.0, $tpl->getWidth(), 0.01);
        $this->assertEqualsWithDelta(640.0, $tpl->getHeight(), 0.01);
    }

    public function testImportPageRespectsRotationByDefault(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->rotatedPdf);
        $tpl = $pdf->importPage($srcId, 1);
        $this->assertSame(90, $tpl->getRotation());
    }

    public function testImportPageCanDisableRotationRespect(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->rotatedPdf);
        $tpl = $pdf->importPage($srcId, 1, ['respectRotation' => false]);
        $this->assertSame(0, $tpl->getRotation());
    }

    public function testImportPageWithGroupXObjectBumpsPdfVersionTo14Minimum(): void
    {
        $pdf = $this->makePdf();
        $pdf->setPDFVersion('1.3');
        $srcId = $pdf->setImportSourceFile($this->transparencyPdf);
        $pdf->importPage($srcId, 1, ['groupXObject' => true]);
        $this->assertSame('1.4', $this->getPdfVersion($pdf));
    }

    public function testImportPageWithGroupXObjectDisabledKeepsVersion(): void
    {
        $pdf = $this->makePdf();
        $pdf->setPDFVersion('1.3');
        $srcId = $pdf->setImportSourceFile($this->transparencyPdf);
        $pdf->importPage($srcId, 1, ['groupXObject' => false]);
        $this->assertSame('1.3', $this->getPdfVersion($pdf));
    }

    public function testImportPageEmitsTransparencyGroupByDefault(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->transparencyPdf);
        $tpl = $pdf->importPage($srcId, 1);
        $pdf->addPage();
        $pdf->useImportedPage($tpl, 10, 10, 120, 80, ['keepAspectRatio' => false]);

        $raw = $pdf->getOutPDFString();
        $this->assertStringContainsString('/Group << /Type /Group /S /Transparency >>', $raw);
    }

    public function testImportPageSuppressesTransparencyGroupInPdfx3(): void
    {
        $pdf = $this->makePdfWithMode('pdfx3');
        $srcId = $pdf->setImportSourceFile($this->transparencyPdf);
        $tpl = $pdf->importPage($srcId, 1, ['groupXObject' => true]);
        $pdf->addPage();
        $pdf->useImportedPage($tpl, 10, 10, 120, 80, ['keepAspectRatio' => false]);

        $raw = $pdf->getOutPDFString();
        $this->assertStringNotContainsString('/Group << /Type /Group /S /Transparency >>', $raw);
    }

    public function testImportPagesNullRangeImportsAll(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $tpls = $pdf->importPages($srcId);
        $this->assertCount(2, $tpls);
        foreach ($tpls as $tpl) {
            $this->assertInstanceOf(PageTemplate::class, $tpl);
        }
    }

    public function testImportPagesExplicitRange(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $tpls = $pdf->importPages($srcId, [1]);
        $this->assertCount(1, $tpls);
        $this->assertInstanceOf(PageTemplate::class, $tpls[0]);
    }

    public function testImportPagesThrowsForOutOfRange(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $this->expectException(ImportPageOutOfRangeException::class);
        $pdf->importPages($srcId, [1, 99]);
    }

    // ------------------------------------------------------------------ useImportedPage

    public function testUseImportedPageReturnsPlacementDimensions(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $tpl = $pdf->importPage($srcId, 1);
        $pdf->addPage();
        $placed = $pdf->useImportedPage($tpl, 10.0, 10.0, 100.0, null, []);
        $this->assertArrayHasKey('x', $placed);
        $this->assertArrayHasKey('y', $placed);
        $this->assertArrayHasKey('width', $placed);
        $this->assertArrayHasKey('height', $placed);
        $this->assertEqualsWithDelta(100.0, $placed['width'], 0.01);
    }

    public function testUseImportedPageAlignCenterCentersInsideRequestedBox(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $tpl = $pdf->importPage($srcId, 1);
        $pdf->addPage();

        $placed = $pdf->useImportedPage($tpl, 10.0, 20.0, 100.0, 200.0, ['keepAspectRatio' => true, 'align' => 'CC']);

        // In a 100x200 box with source ratio 612:792, width is the limiting axis.
        $this->assertEqualsWithDelta(100.0, $placed['width'], 0.01);
        $this->assertEqualsWithDelta(129.41, $placed['height'], 0.05);
        $this->assertEqualsWithDelta(10.0, $placed['x'], 0.01);
        $this->assertEqualsWithDelta(55.29, $placed['y'], 0.05);
    }

    public function testUseImportedPageWithClipAddsClipOperatorToPageContent(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $tpl = $pdf->importPage($srcId, 1);
        $pdf->addPage();

        $pdf->useImportedPage($tpl, 15.0, 25.0, 80.0, 60.0, ['clip' => true, 'keepAspectRatio' => false]);

        $page = $pdf->page->getPage();
        $content = \implode('', $page['content']);
        $this->assertStringContainsString(' re W n ', $content);
    }

    // ------------------------------------------------------------------ addPageFromImport

    public function testAddPageFromImportCreatesPageAndReturnsTemplate(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $tpl = $pdf->addPageFromImport($srcId, 1);
        $this->assertInstanceOf(PageTemplate::class, $tpl);

        // The first page has pid 0; subsequent pages have positive pids.
        $pageId = $pdf->page->getPageID();
        $this->assertGreaterThanOrEqual(0, $pageId);
    }

    public function testAddPageFromImportPageDimensionsMatchTemplate(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $tpl = $pdf->addPageFromImport($srcId, 1);

        $pageId = $pdf->page->getPageID();
        $page = $pdf->page->getPage($pageId);
        $pageW = $page['width'];
        $pageH = $page['height'];
        $this->assertGreaterThan(0.0, $pageW);
        $this->assertGreaterThan(0.0, $pageH);
        // The aspect ratio of the page must match the template.
        $this->assertEqualsWithDelta($tpl->getWidth() / $tpl->getHeight(), $pageW / $pageH, 0.01);
    }

    public function testAddPageFromImportPlacesXObject(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $tpl = $pdf->addPageFromImport($srcId, 1);

        $pageId = $pdf->page->getPageID();
        $pageContent = $pdf->page->getPage($pageId);
        // The page content should reference the XObject.
        $content = \implode('', $pageContent['content']);
        $this->assertStringContainsString($tpl->getXobjId(), $content);
    }

    // ------------------------------------------------------------------ appendDocument

    public function testAppendDocumentCreatesOnePagePerSourcePage(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $tpls = $pdf->appendDocument($srcId);
        $this->assertCount(2, $tpls);
    }

    public function testAppendDocumentWithRangeCreatesOnlyRequestedPages(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $tpls = $pdf->appendDocument($srcId, [2]);
        $this->assertCount(1, $tpls);
        $this->assertSame(2, $tpls[0]->getSourcePage());
    }

    public function testAppendDocumentRestoresCallerPageContext(): void
    {
        $pdf = $this->makePdf();

        // Create an initial page.
        $callerPage = $pdf->addPage();
        $callerPid = $callerPage['pid'];

        // Append pages from a multi-page source.
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $pdf->appendDocument($srcId);

        // Current page ID should be restored to the caller's page.
        $this->assertSame($callerPid, $pdf->page->getPageID());
    }

    public function testAppendDocumentWithNoPriorPageLeavesCurrentOnLastAppended(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $tpls = $pdf->appendDocument($srcId);

        // No prior page (pid is -1), so restore does not run;
        // the current page is the last appended one.
        $finalPid = $pdf->page->getPageID();
        $this->assertGreaterThan(0, $finalPid);
        // Both appended pages should be reachable.
        $this->assertCount(2, $tpls);
    }

    public function testAppendDocumentThrowsForOutOfRangePage(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $this->expectException(ImportPageOutOfRangeException::class);
        $pdf->appendDocument($srcId, [5]);
    }

    public function testAppendDocumentXObjectsRegistered(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $tpls = $pdf->appendDocument($srcId);

        $pages = $pdf->page->getPages();
        $pageContent = '';
        foreach ($pages as $page) {
            $content = $page['content'] ?? [];
            if ($content === []) {
                continue;
            }

            $pageContent .= \implode('', $content);
        }

        foreach ($tpls as $tpl) {
            $this->assertStringContainsString($tpl->getXobjId(), $pageContent);
        }
    }
}
