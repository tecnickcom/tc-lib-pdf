<?php

/**
 * PdfaCidSetTest.php
 *
 * @since       2026-11-02
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
 * Tests for the CIDSet stream of the subset CIDFonts: ISO 19005-1 clause 6.3.5 requires
 * it, while ISO 19005-2 and ISO 19005-3 require a CIDSet, when present, to list every
 * glyph of the embedded program.
 */
class PdfaCidSetTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    protected function setUp(): void
    {
        if (!\defined('K_PATH_FONTS')) {
            $fonts = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts');
            \define('K_PATH_FONTS', $fonts);
        }
    }

    /**
     * @return array<string, array{0: string, 1: bool, 2: int}>
     */
    public static function modeProvider(): array
    {
        return [
            'pdfa1b subset' => ['pdfa1b', true, 1],
            'pdfa1b full' => ['pdfa1b', false, 0],
            'pdfa2b subset' => ['pdfa2b', true, 0],
            'pdfa3b subset' => ['pdfa3b', true, 0],
            'pdfua1 subset' => ['pdfua1', true, 0],
            'plain subset' => ['', true, 0],
        ];
    }

    /**
     * @throws \Throwable
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('modeProvider')]
    public function testCidSetIsEmittedOnlyForPdfa1SubsetFonts(string $mode, bool $subset, int $expected): void
    {
        $pdf = new Tcpdf(subsetfont: $subset, compress: false, mode: $mode);
        $pdf->setTitle('CIDSet');
        $pdf->setLanguage('en-US');
        $font = $pdf->font->insert($pdf->pon, 'dejavusans', '', 11);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->addTextCell(txt: 'Font test', posx: 20, posy: 20);

        $raw = $pdf->getOutPDFString();

        $this->assertSame($subset ? 1 : 0, \preg_match_all('~/BaseFont /[A-Z]{6}\+DejaVuSans /Name~', $raw));
        $this->assertSame($expected, \preg_match_all('~/CIDSet \d+ 0 R~', $raw));
    }
}
