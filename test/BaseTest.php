<?php

/**
 * BaseTest.php
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

class TestableBase extends \Com\Tecnick\Pdf\Tcpdf
{
    public function exposeGetUnitValuePoints(
        string|float|int $val,
        array $ref = self::REFUNITVAL,
        string $defunit = 'px',
    ): float {
        return $this->getUnitValuePoints($val, $ref, $defunit);
    }

    public function exposeGetFontValuePoints(
        string|float|int $val,
        array $ref = self::REFUNITVAL,
        string $defunit = 'pt',
    ): float {
        return $this->getFontValuePoints($val, $ref, $defunit);
    }

    public function exposeSetTmpRTL(string $mode): void
    {
        $this->setTmpRTL($mode);
    }

    public function exposeIsRTL(): bool
    {
        return $this->isRTL();
    }
}

class BaseTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    protected function getInternalTestObject(): TestableBase
    {
        return new TestableBase();
    }

    private function getObjectProperty(object $obj, string $name): mixed
    {
        $ref = new \ReflectionClass($obj);
        while ($ref !== false) {
            if ($ref->hasProperty($name)) {
                $prop = $ref->getProperty($name);
                $prop->setAccessible(true);
                return $prop->getValue($obj);
            }
            $ref = $ref->getParentClass();
        }

        $this->fail('Property not found: ' . $name);
    }

    public function testToPointsAndToUnitRoundTrip(): void
    {
        $obj = $this->getTestObject();
        $usr = 12.34;
        $pnt = $obj->toPoints($usr);

        $this->assertGreaterThan(0, $pnt);
        $this->bcAssertEqualsWithDelta($usr, $obj->toUnit($pnt), 0.0001);
    }

    public function testToYPointsAndToYUnitWithExplicitPageHeight(): void
    {
        $obj = $this->getTestObject();
        $usr = 10.0;
        $pageh = 200.0;

        $yp = $obj->toYPoints($usr, $pageh);
        $this->bcAssertEqualsWithDelta($pageh - $obj->toPoints($usr), $yp, 0.0001);

        $yu = $obj->toYUnit($yp, $pageh);
        $this->bcAssertEqualsWithDelta($usr, $yu, 0.0001);
    }

    public function testEnableDefaultPageContentTogglesFlag(): void
    {
        $obj = $this->getTestObject();
        $obj->enableDefaultPageContent(false);
        $this->assertFalse($this->getObjectProperty($obj, 'defPageContentEnabled'));

        $obj->enableDefaultPageContent(true);
        $this->assertTrue($this->getObjectProperty($obj, 'defPageContentEnabled'));
    }

    public function testSetRTLReturnsSameInstanceAndSetsProperty(): void
    {
        $obj = $this->getTestObject();
        $ret = $obj->setRTL(true);

        $this->assertSame($obj, $ret);
        $this->assertTrue($this->getObjectProperty($obj, 'rtl'));

        $obj->setRTL(false);
        $this->assertFalse($this->getObjectProperty($obj, 'rtl'));
    }

    public function testGetUnitValuePointsConvertsPixels(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetUnitValuePoints('10px');

        $this->assertGreaterThan(0, $result);
        $this->assertIsFloat($result);
    }

    public function testGetUnitValuePointsConvertsPoints(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetUnitValuePoints('12pt');

        $this->assertSame(12.0, $result);
    }

    public function testGetUnitValuePointsConvertsCentimeters(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetUnitValuePoints('1cm');

        // 1 cm = 1 * 72 / 2.54 ≈ 28.35 points
        $this->bcAssertEqualsWithDelta(28.35, $result, 0.1);
    }

    public function testGetUnitValuePointsConvertsInches(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetUnitValuePoints('1in');

        // 1 inch = 72 points (DPI_PDF)
        $this->bcAssertEqualsWithDelta(72.0, $result, 0.1);
    }

    public function testGetUnitValuePointsConvertsNumericDefault(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetUnitValuePoints(5.5);

        $this->assertGreaterThan(0, $result);
        $this->assertIsFloat($result);
    }

    public function testGetUnitValuePointsThrowsForInvalidValue(): void
    {
        $obj = $this->getInternalTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->exposeGetUnitValuePoints('invalid!!!');
    }

    public function testGetFontValuePointsConvertsNamedFontSize(): void
    {
        $obj = $this->getInternalTestObject();
        $ref = [
            'parent' => 12.0,
            'font' => ['size' => 12.0, 'xheight' => 1.0, 'zerowidth' => 1.0, 'rootsize' => 16.0],
            'viewport' => ['width' => 800.0, 'height' => 600.0],
        ];

        $result = $obj->exposeGetFontValuePoints('larger', $ref);

        // 'larger' should result in parent + larger factor
        $this->assertGreaterThan($ref['parent'], $result);
    }

    public function testGetFontValuePointsDelegatesUnknownUnitToGetUnitValuePoints(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetFontValuePoints('10mm');

        // 10 mm = 10 * 72 / 25.4 ≈ 28.35 points
        $this->bcAssertEqualsWithDelta(28.35, $result, 0.1);
    }

    public function testSetTmpRTLWithRMode(): void
    {
        $obj = $this->getInternalTestObject();

        $obj->exposeSetTmpRTL('R');

        $this->assertTrue($obj->exposeIsRTL());
    }

    public function testSetTmpRTLWithLMode(): void
    {
        $obj = $this->getInternalTestObject();

        $obj->exposeSetTmpRTL('L');

        $this->assertFalse($obj->exposeIsRTL());
    }

    public function testSetTmpRTLWithEmptyString(): void
    {
        $obj = $this->getInternalTestObject();

        $obj->exposeSetTmpRTL('');

        $this->assertFalse($obj->exposeIsRTL());
    }

    public function testIsRTLReturnsFalseByDefault(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeIsRTL();

        $this->assertFalse($result);
    }

    public function testIsRTLReturnsTrueWhenGlobalRTLSet(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setRTL(true);

        $result = $obj->exposeIsRTL();

        $this->assertTrue($result);
    }
}
