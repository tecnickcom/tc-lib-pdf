<?php

/**
 * PdfColorTest.php
 *
 * @since       2002-08-03
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
}
