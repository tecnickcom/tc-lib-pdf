<?php

/**
 * PdfColorTest.php
 *
 * @since       2002-08-03
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

class PdfColorTest extends TestUtil
{
    protected function getTestObject(): TestablePdfColor
    {
        return new TestablePdfColor();
    }

    public function testForceDeviceCmykFlagToggles(): void
    {
        $obj = $this->getTestObject();

        $this->assertFalse($obj->isForceDeviceCmyk());
        $obj->setForceDeviceCmyk(true);
        $this->assertTrue($obj->isForceDeviceCmyk());
        $obj->setForceDeviceCmyk(false);
        $this->assertFalse($obj->isForceDeviceCmyk());
    }

    public function testParseSpotCssFunctionReturnsNullForNonSpotInput(): void
    {
        $obj = $this->getTestObject();

        $this->assertNull($obj->exposeParseSpotCssFunction('rgb(1,2,3)'));
    }

    public function testParseSpotCssFunctionReturnsNullForInvalidSyntax(): void
    {
        $obj = $this->getTestObject();

        $this->assertNull($obj->exposeParseSpotCssFunction('spot('));
    }

    public function testParseSpotCssFunctionParsesQuotedNameAndPercentTint(): void
    {
        $obj = $this->getTestObject();

        $out = $obj->exposeParseSpotCssFunction('spot("Brand\\" Orange", 40%)');

        $this->assertIsArray($out);
        $this->assertSame('Brand" Orange', $out[0]);
        $this->bcAssertEqualsWithDelta(0.4, $out[1], 0.000001);
    }

    public function testParseSpotCssFunctionUsesDefaultTintWhenOmitted(): void
    {
        $obj = $this->getTestObject();

        $out = $obj->exposeParseSpotCssFunction('spot(cyan)');

        $this->assertIsArray($out);
        $this->assertSame('cyan', $out[0]);
        $this->bcAssertEqualsWithDelta(1.0, $out[1], 0.000001);
    }

    public function testParseSpotCssFunctionRejectsInvalidNameOrTint(): void
    {
        $obj = $this->getTestObject();

        $this->assertNull($obj->exposeParseSpotCssFunction('spot("", 0.5)'));
        $this->assertNull($obj->exposeParseSpotCssFunction('spot(cyan, abc)'));
    }

    public function testParseSpotNameTokenNormalizesQuotedAndRawTokens(): void
    {
        $obj = $this->getTestObject();

        $this->assertSame('', $obj->exposeParseSpotNameToken('   '));
        $this->assertSame('Brand\" Ink', $obj->exposeParseSpotNameToken('"Brand\\\" Ink"'));
        $this->assertSame('raw-name', $obj->exposeParseSpotNameToken(' raw-name '));
    }

    public function testParseSpotTintTokenParsesAndClampsValues(): void
    {
        $obj = $this->getTestObject();

        $this->assertNull($obj->exposeParseSpotTintToken(''));
        $this->assertNull($obj->exposeParseSpotTintToken('abc'));
        $this->bcAssertEqualsWithDelta(0.4, (float) $obj->exposeParseSpotTintToken('40%'), 0.000001);
        $this->bcAssertEqualsWithDelta(0.4, (float) $obj->exposeParseSpotTintToken('40'), 0.000001);
        $this->bcAssertEqualsWithDelta(1.0, (float) $obj->exposeParseSpotTintToken('500%'), 0.000001);
        $this->bcAssertEqualsWithDelta(0.0, (float) $obj->exposeParseSpotTintToken('-1'), 0.000001);
    }

    public function testGetLabProcessColorReturnsNullForInvalidAndNonLabInput(): void
    {
        $obj = $this->getTestObject();

        $this->assertNull($obj->exposeGetLabProcessColor(''));
        $this->assertNull($obj->exposeGetLabProcessColor('#ff0000'));
    }

    public function testGetLabProcessColorReturnsLabModelForLabInput(): void
    {
        $obj = $this->getTestObject();

        $lab = $obj->exposeGetLabProcessColor('lab(50% 20 30)');

        $this->assertInstanceOf(\Com\Tecnick\Color\Model\Lab::class, $lab);
    }

    public function testGetPdfColorUsesLabSpotPathWhenNotForced(): void
    {
        $obj = $this->getTestObject();

        $out = $obj->getPdfColor('lab(50% 20 30)');

        $this->bcAssertMatchesRegularExpression('/^\/CS\d+\s+cs\s+1\.000000\s+scn\n$/', $out);
    }

    public function testGetPdfColorUsesParentPathForRegularColorWhenNotForced(): void
    {
        $obj = $this->getTestObject();

        $out = $obj->getPdfColor('#ff0000');

        $this->assertStringContainsString('rg', $out);
    }

    public function testGetPdfColorParsesSpotCssAndAppliesTint(): void
    {
        $obj = $this->getTestObject();

        $out = $obj->getPdfColor('spot(cyan,40%)');

        $this->bcAssertMatchesRegularExpression('/^\/CS\d+\s+cs\s+0\.400000\s+scn\n$/', $out);
    }

    public function testGetPdfColorBareNameResolvesToProcessColorNotSpot(): void
    {
        $obj = $this->getTestObject();

        // A bare color name that also exists in the default spot-color table
        // must resolve to a process color (DeviceRGB) and must NOT be emitted
        // as a Separation, otherwise PDF/A documents with an RGB OutputIntent
        // become non-compliant.
        $out = $obj->getPdfColor('black');

        $this->assertStringContainsString(' rg', $out);
        $this->assertStringNotContainsString('scn', $out);
        $this->assertStringNotContainsString('/CS', $out);
        $this->assertFalse($obj->exposeIsRegisteredSpotColor('black'));
    }

    public function testGetPdfColorUsesSpotForRegisteredSpotColorByBareName(): void
    {
        $obj = $this->getTestObject();
        $obj->addSpotColor('MyBrand', new \Com\Tecnick\Color\Model\Cmyk([
            'cyan' => 0.1,
            'magenta' => 0.2,
            'yellow' => 0.3,
            'key' => 0.4,
            'alpha' => 1.0,
        ]));

        $this->assertTrue($obj->exposeIsRegisteredSpotColor('MyBrand'));

        $out = $obj->getPdfColor('MyBrand');

        $this->bcAssertMatchesRegularExpression('/^\/CS\d+\s+cs\s+1\.000000\s+scn\n$/', $out);
    }

    public function testGetPdfProcessColorReturnsEmptyForUnknownColor(): void
    {
        $obj = $this->getTestObject();

        $this->assertSame('', $obj->exposeGetPdfProcessColor('not-a-real-color', false));
    }

    public function testGetPdfColorReturnsEmptyForInvalidSpotCssTint(): void
    {
        $obj = $this->getTestObject();

        $this->assertSame('', $obj->getPdfColor('spot(cyan,abc)'));
    }

    public function testGetPdfColorForcedModeConvertsProcessColorToCmyk(): void
    {
        $obj = $this->getTestObject();
        $obj->setForceDeviceCmyk(true);

        $out = $obj->getPdfColor('#ff0000');

        $this->assertStringContainsString(' k', $out);
    }

    public function testGetPdfColorForcedModePreservesSpotAndStrokeCase(): void
    {
        $obj = $this->getTestObject();
        $obj->setForceDeviceCmyk(true);

        $out = $obj->getPdfColor('spot(cyan,40%)', true);

        $this->bcAssertMatchesRegularExpression('/^\/CS\d+\s+CS\s+0\.400000\s+SCN\n$/', $out);
    }

    public function testGetPdfColorForcedModeReturnsEmptyForUnresolvableColor(): void
    {
        $obj = $this->getTestObject();
        $obj->setForceDeviceCmyk(true);

        $this->assertSame('', $obj->getPdfColor('unknown-color-name'));
    }

    public function testGetPdfLabProcessColorCachesSpotKeyAndSupportsStrokeOutput(): void
    {
        $obj = $this->getTestObject();
        $lab = new \Com\Tecnick\Color\Model\Lab(['lstar' => 50.0, 'astar' => 20.0, 'bstar' => 30.0]);

        $first = $obj->exposeGetPdfLabProcessColor($lab, false);
        $second = $obj->exposeGetPdfLabProcessColor($lab, true);

        $this->bcAssertMatchesRegularExpression('/^\/CS\d+\s+cs\s+1\.000000\s+scn\n$/', $first);
        $this->bcAssertMatchesRegularExpression('/^\/CS\d+\s+CS\s+1\.000000\s+SCN\n$/', $second);
    }

    public function testGetPdfLabProcessColorFallsBackWhenSpotLookupFails(): void
    {
        $obj = $this->getTestObject();
        $lab = new \Com\Tecnick\Color\Model\Lab(['lstar' => 50.0, 'astar' => 20.0, 'bstar' => 30.0]);

        $cacheKey = \sprintf('%F|%F|%F', 50.0, 20.0, 30.0);
        $this->setObjectProperty($obj, 'labSpotKeys', [$cacheKey => 'missing_spot_key']);

        $out = $obj->exposeGetPdfLabProcessColor($lab, false);

        $this->assertSame($lab->getPdfColor(false), $out);
    }

    // ---------------------------------------------------------------------
    // Regression tests for spot-color name preservation.
    //
    // tc-lib-color >= 2.11 preserves the original spot-color name (including
    // spaces and uppercase) and emits it as a properly escaped PDF name object
    // in the Separation color space, instead of the normalized lowercase key.
    // See https://github.com/tecnickcom/tc-lib-pdf/issues/209
    //
    // tc-lib-pdf delegates Separation object emission to tc-lib-color, so these
    // tests pin the end-to-end contract at the tc-lib-pdf integration boundary
    // (the PdfColor adapter) and guard against a future dependency regression.
    // ---------------------------------------------------------------------

    public function testGetPdfSpotObjectsPreservesCmykSpotColorName(): void
    {
        $obj = $this->getTestObject();
        $obj->addSpotColor('SPOTTYPE 123 C', new \Com\Tecnick\Color\Model\Cmyk([
            'cyan' => 0.0,
            'magenta' => 0.24,
            'yellow' => 0.94,
            'key' => 0.0,
            'alpha' => 1.0,
        ]));

        $pon = 5;
        $out = $obj->getPdfSpotObjects($pon);

        // The original name is preserved and encoded as a PDF name object
        // ("SPACE" => "#20"), not collapsed to the normalized lowercase key.
        $this->assertStringContainsString('[/Separation /SPOTTYPE#20123#20C /DeviceCMYK', $out);
        $this->assertStringNotContainsString('spottype123c', $out);
        $this->assertStringNotContainsString('/SPOTTYPE 123 C', $out);

        // The CMYK alternate components are emitted in the C1 array.
        $this->assertStringContainsString('0.000000 0.240000 0.940000 0.000000', $out);

        // The object number reference is advanced past the emitted object.
        $this->assertSame(6, $pon);
    }

    public function testGetPdfSpotObjectsEncodesPdfNameDelimitersInSpotName(): void
    {
        $obj = $this->getTestObject();
        $obj->addSpotColor('Spot (Test)/50%', new \Com\Tecnick\Color\Model\Cmyk([
            'cyan' => 0.1,
            'magenta' => 0.2,
            'yellow' => 0.3,
            'key' => 0.4,
            'alpha' => 1.0,
        ]));

        $pon = 0;
        $out = $obj->getPdfSpotObjects($pon);

        // Spaces and the PDF delimiters "(", ")", "/" and "%" are escaped per
        // ISO 32000-1:2008 7.3.5, so the name remains a valid PDF name object.
        $this->assertStringContainsString('/Separation /Spot#20#28Test#29#2F50#25 /DeviceCMYK', $out);
    }

    public function testGetPdfSpotObjectsPreservesLabSpotColorName(): void
    {
        $obj = $this->getTestObject();
        $obj->addSpotLabColor('Brand Lab Ink', 50.0, 20.0, 30.0);

        $pon = 0;
        $out = $obj->getPdfSpotObjects($pon);

        // Lab-based spot colors must preserve their name in the same way.
        $this->assertStringContainsString('[/Separation /Brand#20Lab#20Ink [/Lab', $out);
        $this->assertStringNotContainsString('brandlabink', $out);
    }

    public function testGetPdfColorByRegisteredNameEmitsSeparationWithPreservedName(): void
    {
        $obj = $this->getTestObject();
        $obj->addSpotColor('SPOTTYPE 123 C', new \Com\Tecnick\Color\Model\Cmyk([
            'cyan' => 0.0,
            'magenta' => 0.24,
            'yellow' => 0.94,
            'key' => 0.0,
            'alpha' => 1.0,
        ]));

        // A bare reference to a registered spot color emits a Separation
        // operator referencing the color-space index with the given tint.
        $ref = $obj->getPdfColor('SPOTTYPE 123 C', false, 0.5);
        $this->bcAssertMatchesRegularExpression('/^\/CS\d+\s+cs\s+0\.500000\s+scn\n$/', $ref);

        // ...and the Separation object that backs that reference keeps the name.
        $pon = 0;
        $this->assertStringContainsString('/Separation /SPOTTYPE#20123#20C', $obj->getPdfSpotObjects($pon));
    }
}
