<?php

/**
 * BundledResourceTest.php
 *
 * @since       2026-11-15
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

use Com\Tecnick\Pdf\Tcpdf;
use PHPUnit\Framework\TestCase;

/**
 * Tests for reading the bundled sRGB ICC profile and package fonts when the 'allowedPaths'
 * file option covers neither the tc-lib-pdf package nor the font directory.
 */
class BundledResourceTest extends TestCase
{
    /** Directory used as the only allowed path. */
    private string $allowed = '';

    /**
     * @throws \Throwable
     */
    protected function setUp(): void
    {
        if (!\defined('K_PATH_FONTS')) {
            $fonts = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts');
            \define('K_PATH_FONTS', $fonts);
        }

        $this->allowed = \dirname(__DIR__) . '/target/tmptest_bundled';
        if (!\is_dir($this->allowed)) {
            \mkdir($this->allowed, 0o755, true);
        }
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function modeProvider(): array
    {
        return [
            'pdfa1b' => ['pdfa1b', false],
            'pdfa2b' => ['pdfa2b', false],
            'pdfa3b' => ['pdfa3b', false],
            'plain with sRGB' => ['', true],
        ];
    }

    /**
     * @throws \Throwable
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('modeProvider')]
    public function testIccProfileIsEmbeddedWithoutAnAllowedPathForIt(string $mode, bool $srgb): void
    {
        $pdf = $this->newPdf($mode);
        $pdf->setSRGB($srgb);
        $pdf->setTitle('ICC');
        $pdf->setLanguage('en-US');
        $font = $pdf->font->insert($pdf->pon, 'dejavusans', '', 11);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->addTextCell(txt: 'ICC test', posx: 20, posy: 20);

        $raw = $pdf->getOutPDFString();

        $this->assertStringContainsString('/OutputIntents', $raw);
        $this->assertSame(1, \preg_match('~<< /N 3 /Filter /FlateDecode /Length \d+ >> stream~', $raw));
    }

    /**
     * @throws \Throwable
     */
    public function testBundledFilesAreNotAddedToTheDocumentFileHelpers(): void
    {
        $pdf = $this->newPdf('pdfa3b');
        $icc = (string) \realpath(__DIR__ . '/../src/include/sRGB.icc.z');
        $font = (string) \realpath((string) \constant('K_PATH_FONTS') . '/core/helvetica.json');

        $this->assertFalse($pdf->file->isAllowedFile($icc));
        $this->assertFalse($pdf->markupFile->isAllowedFile($icc));
        $this->assertFalse($pdf->file->isAllowedFile($font));
        $this->assertFalse($pdf->markupFile->isAllowedFile($font));
    }

    /**
     * @throws \Throwable
     */
    public function testOutputFileCannotBeWrittenNextToTheIccProfile(): void
    {
        $pdf = $this->newPdf('pdfa3b');
        $pdf->addPage();
        $raw = $pdf->getOutPDFString();
        $pdf->setPDFFilename('bundled.pdf');
        $dir = (string) \realpath(__DIR__ . '/../src/include');

        try {
            $pdf->savePDF($dir, $raw);
            $this->fail('the PDF file was written into the package directory');
        } catch (\Com\Tecnick\File\Exception $e) {
            $this->assertStringContainsString('invalid file', $e->getMessage());
        } finally {
            if (\is_file($dir . '/bundled.pdf')) {
                \unlink($dir . '/bundled.pdf');
            }
        }
    }

    /**
     * Create a document whose only allowed path is an empty scratch directory.
     *
     * @throws \Throwable
     */
    private function newPdf(string $mode): Tcpdf
    {
        return new Tcpdf(compress: false, mode: $mode, fileOptions: [
            'allowedPaths' => [(string) \realpath($this->allowed)],
            'markupAllowedPaths' => [(string) \realpath($this->allowed)],
        ]);
    }
}
