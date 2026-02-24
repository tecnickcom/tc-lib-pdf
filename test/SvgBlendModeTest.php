<?php

/**
 * SvgBlendModeTest.php
 *
 * @since       2026-02-24
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

use Com\Tecnick\Pdf\Tcpdf;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Exposes the protected normalizeSVGBlendMode method for unit testing.
 */
class TestableSvgBlendMode extends Tcpdf
{
    public function normalizeBlendMode(string $css): string
    {
        return $this->normalizeSVGBlendMode($css);
    }
}

/**
 * Tests for SVG mix-blend-mode support.
 */
class SvgBlendModeTest extends TestUtil
{
    private TestableSvgBlendMode $pdf;

    protected function setUp(): void
    {
        $this->pdf = new TestableSvgBlendMode();
    }

    // -------------------------------------------------------------------------
    // Unit tests: normalizeSVGBlendMode()
    // -------------------------------------------------------------------------

    #[DataProvider('singleWordBlendModeProvider')]
    public function testNormalizeSingleWordModes(string $css, string $expected): void
    {
        $this->assertEquals($expected, $this->pdf->normalizeBlendMode($css));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function singleWordBlendModeProvider(): array
    {
        return [
            'normal'     => ['normal',     'Normal'],
            'multiply'   => ['multiply',   'Multiply'],
            'screen'     => ['screen',     'Screen'],
            'overlay'    => ['overlay',    'Overlay'],
            'darken'     => ['darken',     'Darken'],
            'lighten'    => ['lighten',    'Lighten'],
            'difference' => ['difference', 'Difference'],
            'exclusion'  => ['exclusion',  'Exclusion'],
            'hue'        => ['hue',        'Hue'],
            'saturation' => ['saturation', 'Saturation'],
            'color'      => ['color',      'Color'],
            'luminosity' => ['luminosity', 'Luminosity'],
        ];
    }

    #[DataProvider('kebabBlendModeProvider')]
    public function testNormalizeKebabCaseModes(string $css, string $expected): void
    {
        $this->assertEquals($expected, $this->pdf->normalizeBlendMode($css));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function kebabBlendModeProvider(): array
    {
        return [
            'color-dodge' => ['color-dodge', 'ColorDodge'],
            'color-burn'  => ['color-burn',  'ColorBurn'],
            'hard-light'  => ['hard-light',  'HardLight'],
            'soft-light'  => ['soft-light',  'SoftLight'],
        ];
    }

    public function testNormalizeUnknownValueFallsBackToNormal(): void
    {
        $this->assertEquals('Normal', $this->pdf->normalizeBlendMode('dissolve'));
        $this->assertEquals('Normal', $this->pdf->normalizeBlendMode(''));
        $this->assertEquals('Normal', $this->pdf->normalizeBlendMode('not-a-mode'));
    }

    public function testNormalizeMixedCaseHandledGracefully(): void
    {
        $this->assertEquals('Multiply',   $this->pdf->normalizeBlendMode('Multiply'));
        $this->assertEquals('Multiply',   $this->pdf->normalizeBlendMode('MULTIPLY'));
        $this->assertEquals('Screen',     $this->pdf->normalizeBlendMode('Screen'));
        $this->assertEquals('ColorDodge', $this->pdf->normalizeBlendMode('Color-Dodge'));
    }

    // -------------------------------------------------------------------------
    // Integration tests: PDF output inspection
    // -------------------------------------------------------------------------

    private function makePdf(): Tcpdf
    {
        if (!defined('K_PATH_FONTS')) {
            define(
                'K_PATH_FONTS',
                __DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/'
            );
        }

        $pdf = new Tcpdf();
        $pdf->font->insert($pdf->pon, 'helvetica', '', 10);
        $pdf->addPage([]);
        return $pdf;
    }

    private function buildSvgWithFill(string $blendMode): string
    {
        return '@<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
            . '<rect width="100" height="100" fill="#ff0000"'
            . ' style="mix-blend-mode:' . $blendMode . '"/>'
            . '</svg>';
    }

    private function buildSvgWithStroke(string $blendMode): string
    {
        return '@<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
            . '<rect width="100" height="100" fill="none"'
            . ' stroke="#000000" stroke-width="4"'
            . ' style="mix-blend-mode:' . $blendMode . '"/>'
            . '</svg>';
    }

    private function renderSvg(string $inlineSvg): string
    {
        $pdf = $this->makePdf();
        $soid = $pdf->addSVG($inlineSvg, 0, 0, 50, 50);
        $pdf->page->addContent($pdf->getSetSVG($soid));
        return $pdf->getOutPDFString();
    }

    public function testFillMultiplyEmitsBlendMode(): void
    {
        $output = $this->renderSvg($this->buildSvgWithFill('multiply'));
        $this->assertStringContainsString('/BM /Multiply', $output);
    }

    public function testFillColorDodgeEmitsBlendMode(): void
    {
        $output = $this->renderSvg($this->buildSvgWithFill('color-dodge'));
        $this->assertStringContainsString('/BM /ColorDodge', $output);
    }

    public function testStrokeDifferenceEmitsBlendMode(): void
    {
        $output = $this->renderSvg($this->buildSvgWithStroke('difference'));
        $this->assertStringContainsString('/BM /Difference', $output);
    }

    public function testBlendModeInheritsFromGroup(): void
    {
        $svg = '@<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
            . '<g style="mix-blend-mode:screen">'
            . '<rect width="50" height="50" fill="#00ff00"/>'
            . '</g>'
            . '</svg>';
        $output = $this->renderSvg($svg);
        $this->assertStringContainsString('/BM /Screen', $output);
    }

    public function testNoBlendModeDoesNotEmitBmOperator(): void
    {
        $svg = '@<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
            . '<rect width="100" height="100" fill="#ff0000"/>'
            . '</svg>';
        $output = $this->renderSvg($svg);
        $this->assertStringNotContainsString('/BM /', $output);
    }

    public function testGradientFillWithBlendModeEmitsBm(): void
    {
        $svg = '@<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
            . '<defs>'
            . '<linearGradient id="g1" x1="0" y1="0" x2="1" y2="0">'
            . '<stop offset="0%" stop-color="#ff0000"/>'
            . '<stop offset="100%" stop-color="#0000ff"/>'
            . '</linearGradient>'
            . '</defs>'
            . '<rect width="100" height="100" fill="url(#g1)"'
            . ' style="mix-blend-mode:multiply"/>'
            . '</svg>';
        $output = $this->renderSvg($svg);
        $this->assertStringContainsString('/BM /Multiply', $output);
    }

    public function testGradientFillWithoutBlendModeNoBm(): void
    {
        $svg = '@<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
            . '<defs>'
            . '<linearGradient id="g2" x1="0" y1="0" x2="1" y2="0">'
            . '<stop offset="0%" stop-color="#ff0000"/>'
            . '<stop offset="100%" stop-color="#0000ff"/>'
            . '</linearGradient>'
            . '</defs>'
            . '<rect width="100" height="100" fill="url(#g2)"/>'
            . '</svg>';
        $output = $this->renderSvg($svg);
        $this->assertStringNotContainsString('/BM /', $output);
    }

    public function testFillBlendModePreservesElementOpacity(): void
    {
        $svg = '@<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
            . '<rect width="100" height="100" fill="#ff0000"'
            . ' style="opacity:0.5;mix-blend-mode:multiply"/>'
            . '</svg>';
        $output = $this->renderSvg($svg);

        $this->assertStringContainsString(
            '/CA 0.500000 /ca 0.500000 /BM /Multiply',
            $output
        );
        $this->assertStringNotContainsString(
            '/CA 1.000000 /ca 1.000000 /BM /Multiply',
            $output
        );
    }

    public function testFillBlendModeCombinesElementAndFillOpacity(): void
    {
        $svg = '@<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
            . '<rect width="100" height="100" fill="#ff0000"'
            . ' style="opacity:0.5;fill-opacity:0.8;mix-blend-mode:multiply"/>'
            . '</svg>';
        $output = $this->renderSvg($svg);

        $this->assertStringContainsString('/ca 0.400000 /BM /Multiply', $output);
        $this->assertStringNotContainsString('/ca 0.800000 /BM /Multiply', $output);
    }

    public function testStrokeBlendModeCombinesElementAndStrokeOpacity(): void
    {
        $svg = '@<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
            . '<rect width="100" height="100" fill="none" stroke="#000000" stroke-width="4"'
            . ' style="opacity:0.5;stroke-opacity:0.8;mix-blend-mode:multiply"/>'
            . '</svg>';
        $output = $this->renderSvg($svg);

        $this->assertStringContainsString('/CA 0.400000 /BM /Multiply', $output);
        $this->assertStringNotContainsString('/CA 0.800000 /ca 0.800000 /BM /Multiply', $output);
    }
}
