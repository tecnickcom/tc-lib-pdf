<?php

/**
 * TcpdfImporterFacadeTest.php
 *
 * @since       2026-04-25
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

    /**
     * @throws \Throwable
     */
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

    /**
     * @throws \Throwable
     */
    private function makePdf(): Tcpdf
    {
        $pdf = new Tcpdf();
        // A default font must be inserted before any addPage call so that
        // setPageContext / getOutCurrentFont does not receive a null font key.
        $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        return $pdf;
    }

    /**
     * @throws \Throwable
     */
    private function makePdfWithMode(string $mode): Tcpdf
    {
        $pdf = new Tcpdf(mode: $mode);
        $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        return $pdf;
    }

    /**
     * @throws \Throwable
     */
    private function getPdfVersion(Tcpdf $pdf): string
    {
        $ref = new \ReflectionClass($pdf);
        while ($ref !== false) {
            if ($ref->hasProperty('pdfver')) {
                $prop = $ref->getProperty('pdfver');
                return $this->stringValue($prop->getValue($pdf));
            }

            $ref = $ref->getParentClass();
        }

        return '';
    }

    private function stringValue(mixed $value): string
    {
        return \is_string($value) ? $value : '';
    }

    /**
     * Build a minimal one-page PDF whose /Contents is a single LZW stream.
     */
    private function buildLzwContentPdf(): string
    {
        $stream = $this->lzwEncode("BT /F1 12 Tf 20 160 Td (LZW) Tj ET\n");

        $objects = [];
        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj' . "\n";
        $objects[] = '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj' . "\n";
        $objects[] =
            '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] '
            . '/Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >> endobj'
            . "\n";
        $objects[] =
            '4 0 obj << /Length '
            . \strlen($stream)
            . ' /Filter /LZWDecode >> stream'
            . "\n"
            . $stream
            . "\n"
            . 'endstream endobj'
            . "\n";
        $objects[] = '5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj' . "\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [0 => 0];

        foreach ($objects as $idx => $obj) {
            $offsets[$idx + 1] = \strlen($pdf);
            $pdf .= $obj;
        }

        $xrefOffset = \strlen($pdf);
        $pdf .= 'xref' . "\n";
        $pdf .= '0 6' . "\n";
        $pdf .= '0000000000 65535 f ' . "\n";
        for ($objNum = 1; $objNum <= 5; ++$objNum) {
            $pdf .= \sprintf('%010d 00000 n ' . "\n", $offsets[$objNum] ?? 0);
        }

        $pdf .= 'trailer << /Size 6 /Root 1 0 R >>' . "\n";
        $pdf .= 'startxref' . "\n";
        $pdf .= $xrefOffset . "\n";
        return $pdf . ('%%EOF' . "\n");
    }

    /**
     * Encode data with the PDF variant of LZW (9 to 12 bit codes, early change).
     */
    private function lzwEncode(string $data): string
    {
        $dict = [];
        for ($idx = 0; $idx < 256; ++$idx) {
            $dict[\chr($idx)] = $idx;
        }

        $next = 258;
        $width = 9;
        $out = '';
        $buffer = 0;
        $bits = 0;
        $emit = static function (int $code) use (&$out, &$buffer, &$bits, &$width): void {
            $buffer = ($buffer << $width) | $code;
            $bits += $width;
            while ($bits >= 8) {
                $bits -= 8;
                $out .= \chr(($buffer >> $bits) & 0xFF);
            }
        };

        $emit(256);
        $word = '';
        for ($idx = 0, $len = \strlen($data); $idx < $len; ++$idx) {
            $char = $data[$idx];
            if (isset($dict[$word . $char])) {
                $word .= $char;
                continue;
            }

            $emit((int) ($dict[$word] ?? 0));
            $dict[$word . $char] = $next;
            ++$next;
            $width = $this->lzwCodeWidth($next);
            $word = $char;
        }

        if ($word !== '') {
            $emit((int) ($dict[$word] ?? 0));
        }

        $emit(257);
        if ($bits > 0) {
            $out .= \chr(($buffer << (8 - $bits)) & 0xFF);
        }

        return $out;
    }

    /**
     * Code width in bits for the next LZW code, with the PDF early change.
     */
    private function lzwCodeWidth(int $next): int
    {
        return match (true) {
            ($next + 1) > 2048 => 12,
            ($next + 1) > 1024 => 11,
            ($next + 1) > 512 => 10,
            default => 9,
        };
    }

    /**
     * Concatenate every Flate stream of a document after inflating it.
     */
    private function inflateStreams(string $raw): string
    {
        $out = '';
        $offset = 0;
        while (($start = \strpos($raw, "stream\n", $offset)) !== false) {
            $end = \strpos($raw, "\nendstream", $start);
            if ($end === false) {
                break;
            }

            $data = \substr($raw, $start + 7, $end - $start - 7);
            $offset = $end + 10;

            \set_error_handler(static fn(): bool => true);
            try {
                $inflated = \gzuncompress($data);
            } finally {
                \restore_error_handler();
            }

            if (\is_string($inflated)) {
                $out .= $inflated;
            }
        }

        return $out;
    }

    /**
     * Build a minimal one-page PDF whose /Contents is an array of two Flate streams.
     */
    private function buildMultiContentFlatePdf(): string
    {
        $streamA = \gzcompress("BT /F1 12 Tf 20 160 Td (A) Tj ET\n");
        $streamB = \gzcompress("BT /F1 12 Tf 20 140 Td (B) Tj ET\n");
        self::assertNotFalse($streamA);
        self::assertNotFalse($streamB);

        $objects = [];
        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj' . "\n";
        $objects[] = '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj' . "\n";
        $objects[] =
            '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] '
            . '/Resources << /Font << /F1 6 0 R >> >> /Contents [4 0 R 5 0 R] >> endobj'
            . "\n";
        $objects[] =
            '4 0 obj << /Length '
            . \strlen($streamA)
            . ' /Filter /FlateDecode >> stream'
            . "\n"
            . $streamA
            . "\n"
            . 'endstream endobj'
            . "\n";
        $objects[] =
            '5 0 obj << /Length '
            . \strlen($streamB)
            . ' /Filter /FlateDecode >> stream'
            . "\n"
            . $streamB
            . "\n"
            . 'endstream endobj'
            . "\n";
        $objects[] = '6 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj' . "\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [0 => 0];

        foreach ($objects as $idx => $obj) {
            $objNum = $idx + 1;
            $offsets[$objNum] = \strlen($pdf);
            $pdf .= $obj;
        }

        $xrefOffset = \strlen($pdf);
        $pdf .= 'xref' . "\n";
        $pdf .= '0 7' . "\n";
        $pdf .= '0000000000 65535 f ' . "\n";
        for ($objNum = 1; $objNum <= 6; ++$objNum) {
            $offset = $offsets[$objNum] ?? 0;
            $pdf .= \sprintf('%010d 00000 n ' . "\n", $offset);
        }

        $pdf .= 'trailer << /Size 7 /Root 1 0 R >>' . "\n";
        $pdf .= 'startxref' . "\n";
        $pdf .= $xrefOffset . "\n";
        $pdf .= '%%EOF' . "\n";

        return $pdf;
    }

    // ------------------------------------------------------------------ setImportSourceFile / setImportSourceData

    /**
     * @throws \Throwable
     */
    public function testSetImportSourceFileReturnsNonEmptyId(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $this->assertNotEmpty($srcId);
    }

    /**
     * @throws \Throwable
     */
    public function testSetImportSourceDataReturnsNonEmptyId(): void
    {
        $pdf = $this->makePdf();
        $data = (string) \file_get_contents($this->simplePdf);
        $srcId = $pdf->setImportSourceData($data);
        $this->assertNotEmpty($srcId);
    }

    /**
     * @throws \Throwable
     */
    public function testSetImportSourceFileThrowsForMissingFile(): void
    {
        $pdf = $this->makePdf();
        $this->expectException(ImportSourceNotFoundException::class);
        $pdf->setImportSourceFile('/nonexistent/path.pdf');
    }

    /**
     * @throws \Throwable
     */
    public function testSetImportSourceFileThrowsForEncryptedPdf(): void
    {
        $pdf = $this->makePdf();
        $this->expectException(ImportUnsupportedFeatureException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('encrypted PDF', '/') . '/');
        $pdf->setImportSourceFile($this->encryptedPdf);
    }

    /**
     * @throws \Throwable
     */
    public function testSetImportSourceFileWithPasswordThrowsActionableEncryptedError(): void
    {
        $pdf = $this->makePdf();
        $this->expectException(ImportUnsupportedFeatureException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('password-based import is not supported', '/') . '/');
        $pdf->setImportSourceFile($this->encryptedPdf, ['password' => 'secret']);
    }

    // ------------------------------------------------------------------ getSourcePageCount

    /**
     * @throws \Throwable
     */
    public function testGetSourcePageCountSimple(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $this->assertSame(1, $pdf->getSourcePageCount($srcId));
    }

    /**
     * @throws \Throwable
     */
    public function testGetSourcePageCountMultipage(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $this->assertSame(2, $pdf->getSourcePageCount($srcId));
    }

    // ------------------------------------------------------------------ importPage / importPages

    /**
     * @throws \Throwable
     */
    public function testImportPageReturnsPageTemplate(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $tpl = $pdf->importPage($srcId, 1);
        $this->assertInstanceOf(PageTemplate::class, $tpl);
        $this->assertGreaterThan(0.0, $tpl->getWidth());
        $this->assertGreaterThan(0.0, $tpl->getHeight());
    }

    /**
     * @throws \Throwable
     */
    public function testImportPageThrowsForOutOfRange(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->simplePdf);
        $this->expectException(ImportPageOutOfRangeException::class);
        $pdf->importPage($srcId, 99);
    }

    /**
     * @throws \Throwable
     */
    public function testImportPageUsesTrimBoxWhenRequested(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->boxOptionsPdf);
        $tpl = $pdf->importPage($srcId, 1, ['box' => 'TrimBox']);
        $this->assertEqualsWithDelta(460.0, $tpl->getWidth(), 0.01);
        $this->assertEqualsWithDelta(660.0, $tpl->getHeight(), 0.01);
    }

    /**
     * @throws \Throwable
     */
    public function testImportPageUsesArtBoxWhenRequested(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->boxOptionsPdf);
        $tpl = $pdf->importPage($srcId, 1, ['box' => 'ArtBox']);
        $this->assertEqualsWithDelta(440.0, $tpl->getWidth(), 0.01);
        $this->assertEqualsWithDelta(640.0, $tpl->getHeight(), 0.01);
    }

    /**
     * @throws \Throwable
     */
    public function testImportPageRespectsRotationByDefault(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->rotatedPdf);
        $tpl = $pdf->importPage($srcId, 1);
        $this->assertSame(90, $tpl->getRotation());
        $this->assertGreaterThan($tpl->getHeight(), $tpl->getWidth());
        $this->assertEqualsWithDelta(500.0, $tpl->getWidth(), 0.01);
        $this->assertEqualsWithDelta(300.0, $tpl->getHeight(), 0.01);
    }

    /**
     * @throws \Throwable
     */
    public function testImportPageCanDisableRotationRespect(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->rotatedPdf);
        $tpl = $pdf->importPage($srcId, 1, ['respectRotation' => false]);
        $this->assertSame(0, $tpl->getRotation());
        $this->assertGreaterThan($tpl->getWidth(), $tpl->getHeight());
        $this->assertEqualsWithDelta(300.0, $tpl->getWidth(), 0.01);
        $this->assertEqualsWithDelta(500.0, $tpl->getHeight(), 0.01);
    }

    /**
     * @throws \Throwable
     */
    public function testImportPageWithGroupXObjectBumpsPdfVersionTo14Minimum(): void
    {
        $pdf = $this->makePdf();
        $pdf->setPDFVersion('1.3');
        $srcId = $pdf->setImportSourceFile($this->transparencyPdf);
        $pdf->importPage($srcId, 1, ['groupXObject' => true]);
        $this->assertSame('1.4', $this->getPdfVersion($pdf));
    }

    /**
     * @throws \Throwable
     */
    public function testImportPageWithGroupXObjectDisabledKeepsVersion(): void
    {
        $pdf = $this->makePdf();
        $pdf->setPDFVersion('1.3');
        $srcId = $pdf->setImportSourceFile($this->transparencyPdf);
        $pdf->importPage($srcId, 1, ['groupXObject' => false]);
        $this->assertSame('1.3', $this->getPdfVersion($pdf));
    }

    /**
     * @throws \Throwable
     */
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

    /**
     * @throws \Throwable
     */
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

    /**
     * @throws \Throwable
     */
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

    /**
     * @throws \Throwable
     */
    public function testImportPagesExplicitRange(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $tpls = $pdf->importPages($srcId, [1]);
        $this->assertCount(1, $tpls);
        assert(isset($tpls[0]), "\$tpls[0] must be set");
        $this->assertInstanceOf(PageTemplate::class, $tpls[0]);
    }

    /**
     * @throws \Throwable
     */
    public function testImportPagesThrowsForOutOfRange(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $this->expectException(ImportPageOutOfRangeException::class);
        $pdf->importPages($srcId, [1, 99]);
    }

    // ------------------------------------------------------------------ useImportedPage

    /**
     * @throws \Throwable
     */
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

    /**
     * @throws \Throwable
     */
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

    /**
     * @throws \Throwable
     */
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

    /**
     * @throws \Throwable
     */
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

    /**
     * @throws \Throwable
     */
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

    /**
     * @throws \Throwable
     */
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

    /**
     * @throws \Throwable
     */
    public function testAppendDocumentCreatesOnePagePerSourcePage(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $tpls = $pdf->appendDocument($srcId);
        $this->assertCount(2, $tpls);
    }

    /**
     * @throws \Throwable
     */
    public function testAppendDocumentConcatenatesDecodedMultiStreamContents(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceData($this->buildMultiContentFlatePdf());

        $tpls = $pdf->appendDocument($srcId);
        $this->assertCount(1, $tpls);

        $raw = $pdf->getOutPDFString();
        $this->assertStringContainsString('(A) Tj ET', $raw);
        $this->assertStringContainsString('(B) Tj ET', $raw);
    }

    /**
     * @throws \Throwable
     */
    public function testAppendDocumentRewritesLzwContentAsFlate(): void
    {
        foreach (['', 'pdfa1b', 'pdfa3b'] as $mode) {
            $pdf = $mode === '' ? $this->makePdf() : $this->makePdfWithMode($mode);
            $srcId = $pdf->setImportSourceData($this->buildLzwContentPdf());

            $tpls = $pdf->appendDocument($srcId);
            $this->assertCount(1, $tpls);

            $raw = $pdf->getOutPDFString();
            $this->assertStringNotContainsString('/LZWDecode', $raw);
            $this->assertStringContainsString('(LZW) Tj ET', $this->inflateStreams($raw));
        }
    }

    /**
     * @throws \Throwable
     */
    public function testAppendDocumentDoesNotAddTransparencyGroupInPdfa1(): void
    {
        $pdfa1 = $this->makePdfWithMode('pdfa1b');
        $pdfa1->appendDocument($pdfa1->setImportSourceData($this->buildMultiContentFlatePdf()));
        $this->assertStringNotContainsString('/S /Transparency', $pdfa1->getOutPDFString());

        $pdfa3 = $this->makePdfWithMode('pdfa3b');
        $pdfa3->appendDocument($pdfa3->setImportSourceData($this->buildMultiContentFlatePdf()));
        $this->assertStringContainsString('/S /Transparency', $pdfa3->getOutPDFString());
    }

    /**
     * @throws \Throwable
     */
    public function testAppendDocumentWithRangeCreatesOnlyRequestedPages(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $tpls = $pdf->appendDocument($srcId, [2]);
        $this->assertCount(1, $tpls);
        assert(isset($tpls[0]), "\$tpls[0] must be set");
        $this->assertSame(2, $tpls[0]->getSourcePage());
    }

    /**
     * @throws \Throwable
     */
    public function testAppendDocumentRestoresCallerPageContext(): void
    {
        $pdf = $this->makePdf();

        // Create an initial page.
        $callerPage = $pdf->addPage();
        if (!isset($callerPage['pid']) || !\is_int($callerPage['pid'])) {
            $this->fail('Expected integer page id.');
        }
        $callerPid = $callerPage['pid'];

        // Append pages from a multi-page source.
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $pdf->appendDocument($srcId);

        // Current page ID should be restored to the caller's page.
        $this->assertSame($callerPid, $pdf->page->getPageID());
    }

    /**
     * @throws \Throwable
     */
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

    /**
     * @throws \Throwable
     */
    public function testAppendDocumentThrowsForOutOfRangePage(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $this->expectException(ImportPageOutOfRangeException::class);
        $pdf->appendDocument($srcId, [5]);
    }

    /**
     * @throws \Throwable
     */
    public function testAppendDocumentXObjectsRegistered(): void
    {
        $pdf = $this->makePdf();
        $srcId = $pdf->setImportSourceFile($this->multipagePdf);
        $tpls = $pdf->appendDocument($srcId);

        $pages = $pdf->page->getPages();
        $pageContent = '';
        foreach ($pages as $page) {
            $content = $page['content'];
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
