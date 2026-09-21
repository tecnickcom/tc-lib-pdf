<?php

/**
 * SyntheticFontStyleTest.php
 *
 * @since       2026-09-21
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
 * Tests for the synthetic bold and italic applied to a font whose style variation
 * file is missing (issue #291): bold strokes the glyphs, italic shears the text
 * matrix, and the advance widths that drive the layout stay untouched.
 */
class SyntheticFontStyleTest extends TestCase
{
    /** Shear of the synthetic italic: tan(11 degrees), from the descriptor italic angle. */
    private const SHEAR = '0.194380';

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
     * @throws \Throwable
     */
    private function makePdf(string $family, string $style, float $size = 12.0): Tcpdf
    {
        $pdf = new Tcpdf();
        $font = $pdf->font->insert($pdf->pon, $family, $style, $size);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        return $pdf;
    }

    /**
     * A missing bold variation is painted by stroking the glyphs in fill-and-stroke
     * rendering mode, with a stroke width of a thirtieth of the font size.
     *
     * @throws \Throwable
     */
    public function testSyntheticBoldStrokesTheGlyphs(): void
    {
        $pdf = $this->makePdf('unifont', 'B', 30.0);
        $out = $pdf->getTextLine('bold', 10, 10);

        $this->assertStringContainsString('2 Tr', $out);
        $this->assertStringContainsString('1.000000 w', $out, 'The stroke width is 30/30 points.');
    }

    /**
     * A missing italic variation is painted by shearing the text matrix, so the
     * positioning switches from 'Td' to 'Tm'.
     *
     * @throws \Throwable
     */
    public function testSyntheticItalicShearsTheTextMatrix(): void
    {
        $pdf = $this->makePdf('unifont', 'I');
        $out = $pdf->getTextLine('italic', 10, 10);

        $this->assertStringContainsString('1.000000 0.000000 ' . self::SHEAR . ' 1.000000 ', $out);
        $this->assertStringContainsString(' Tm ', $out);
        $this->assertStringNotContainsString(' Td ', $out);
        $this->assertStringNotContainsString('2 Tr', $out, 'Italic alone must not stroke the glyphs.');
    }

    /**
     * A missing bold-italic variation combines both effects.
     *
     * @throws \Throwable
     */
    public function testSyntheticBoldItalicCombinesBothEffects(): void
    {
        $pdf = $this->makePdf('unifont', 'BI');
        $out = $pdf->getTextLine('bold italic', 10, 10);

        $this->assertStringContainsString(' Tm ', $out);
        $this->assertStringContainsString(self::SHEAR, $out);
        $this->assertStringContainsString('2 Tr', $out);
    }

    /**
     * A font that provides the requested style variation is rendered as it is.
     *
     * @throws \Throwable
     */
    public function testRealStyleVariationIsNotSynthesized(): void
    {
        foreach (['B', 'I', 'BI'] as $style) {
            $pdf = $this->makePdf('helvetica', $style);
            $out = $pdf->getTextLine('real', 10, 10);

            $this->assertStringContainsString(' Td ', $out, 'Style ' . $style);
            $this->assertStringNotContainsString(' Tm ', $out, 'Style ' . $style);
            $this->assertStringNotContainsString('2 Tr', $out, 'Style ' . $style);
            $this->assertStringNotContainsString(' w ', $out, 'Style ' . $style);
        }
    }

    /**
     * Text that is neither filled nor stroked is invisible or a clipping source, so
     * the synthetic bold must not paint it.
     *
     * @throws \Throwable
     */
    public function testSyntheticBoldSkipsUnpaintedText(): void
    {
        $pdf = $this->makePdf('unifont', 'B');

        $invisible = $pdf->getTextLine('hidden', 10, 10, fill: false);
        $this->assertStringContainsString('3 Tr', $invisible);
        $this->assertStringNotContainsString('1 Tr', $invisible);

        $clip = $pdf->getTextLine('clipped', 10, 10, fill: false, clip: true);
        $this->assertStringContainsString('7 Tr', $clip);
        $this->assertStringNotContainsString('5 Tr', $clip);
    }

    /**
     * The synthesis paints the glyphs and never touches the advance widths, so the
     * measured width of a run is the same in every style.
     *
     * @throws \Throwable
     */
    public function testSyntheticStylesDoNotChangeTheMeasuredWidth(): void
    {
        $ordarr = [87, 105, 100, 116, 104];
        $regular = $this->makePdf('unifont', '')->font->getOrdArrWidth($ordarr);
        $this->assertGreaterThan(0.0, $regular);

        foreach (['B', 'I', 'BI'] as $style) {
            $this->assertSame(
                $regular,
                $this->makePdf('unifont', $style)->font->getOrdArrWidth($ordarr),
                'Style ' . $style . ' must measure like the regular font.',
            );
        }
    }

    /**
     * Every synthesized style resolves to the font the family does ship, so the glyph
     * program is embedded once however many styles the document uses.
     *
     * @throws \Throwable
     */
    public function testSyntheticStylesEmbedTheFontOnce(): void
    {
        $pdf = new Tcpdf(subsetfont: false);
        $font = $pdf->font->insert($pdf->pon, 'unifont', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->addHTMLCell(
            html: '<p>Regular <b>Bold</b> <i>Italic</i> <b><i>Bold Italic</i></b></p>',
            posx: 10,
            posy: 10,
            width: 180,
        );

        $raw = $pdf->getOutPDFString();

        $this->assertSame(1, \preg_match_all('~/FontFile2~', $raw), 'One embedded program.');
        $this->assertSame(
            ['Unifont'],
            \array_values(\array_unique($this->baseFonts($raw))),
            'The descriptor names the font that is embedded.',
        );
    }

    /**
     * A run that follows a synthesized one is drawn in its own style: the font key is the
     * same for every style of the family, so the style alone tells them apart.
     *
     * @throws \Throwable
     */
    public function testASyntheticStyleDoesNotLeakIntoTheNextRun(): void
    {
        $pdf = $this->makePdf('unifont', '');
        $frag = $pdf->getHTMLCell('<p>AAA<b>BBB</b>CCC<i>DDD</i>EEE</p>', 0, 0, 160, 30);

        $drawn = $this->drawnTextObjects($frag);
        $this->assertCount(5, $drawn);

        $this->assertSame(
            [false, true, false, false, false],
            \array_map(static fn(string $obj): bool => \str_contains($obj, '2 Tr'), $drawn),
            'Only the bold run is stroked.',
        );
        $this->assertSame(
            [false, false, false, true, false],
            \array_map(static fn(string $obj): bool => \str_contains($obj, ' Tm '), $drawn),
            'Only the italic run is sheared.',
        );
    }

    /**
     * Returns the text objects of a fragment that draw a string.
     *
     * @return array<int, string>
     */
    private function drawnTextObjects(string $frag): array
    {
        $matches = [];
        \preg_match_all('~BT (.*?) ET~s', $frag, $matches);
        $objects = [];
        foreach ($matches[1] ?? [] as $obj) {
            if (!\str_contains($obj, ') Tj')) {
                continue;
            }

            $objects[] = $obj;
        }

        return $objects;
    }

    /**
     * @return array<int, string>
     */
    private function baseFonts(string $raw): array
    {
        $matches = [];
        \preg_match_all('~/BaseFont\s*/([^\s/>]+)~', $raw, $matches);
        $names = $matches[1] ?? [];
        \sort($names);
        return $names;
    }

    /**
     * A synthetic bold strokes the glyphs, so the HTML renderer must set the stroke
     * colour to the text colour: the outline would otherwise keep the stroke colour
     * left by an earlier operation.
     *
     * @throws \Throwable
     */
    public function testHTMLSyntheticBoldStrokesWithTheTextColor(): void
    {
        $pdf = $this->makePdf('unifont', '');
        $frag = $pdf->getHTMLCell('<p style="color:red"><b>red bold</b></p>', 0, 0, 120, 30);

        $pos = \strpos($frag, '2 Tr');
        $this->assertNotFalse($pos, 'The synthetic bold must stroke the glyphs.');
        $this->assertStringContainsString(
            '1.000000 0.000000 0.000000 RG',
            \substr($frag, 0, $pos),
            'The outline must use the text colour.',
        );
    }
}
