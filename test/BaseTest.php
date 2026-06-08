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

use PHPUnit\Framework\Attributes\DataProvider;

class BaseTest extends TestUtil
{
    private function invokeBaseMethod(object $obj, string $method, mixed ...$args): mixed
    {
        $ref = new \ReflectionClass($obj);
        return $ref->getMethod($method)->invokeArgs($obj, $args);
    }

    /** @throws \Throwable */
    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    /** @throws \Throwable */
    protected function getInternalTestObject(): TestableBase
    {
        return new TestableBase();
    }

    /** @throws \Throwable */
    public function testToPointsAndToUnitRoundTrip(): void
    {
        $obj = $this->getTestObject();
        $usr = 12.34;
        $pnt = $obj->toPoints($usr);

        $this->assertGreaterThan(0, $pnt);
        $this->bcAssertEqualsWithDelta($usr, $obj->toUnit($pnt), 0.0001);
    }

    /** @throws \Throwable */
    public function testToYPointsAndToYUnitWithExplicitPageHeight(): void
    {
        $obj = $this->getTestObject();
        $usr = 10.0;
        $pageh = 200.0;

        $yPoints = $obj->toYPoints($usr, $pageh);
        $this->bcAssertEqualsWithDelta($pageh - $obj->toPoints($usr), $yPoints, 0.0001);

        $yUnit = $obj->toYUnit($yPoints, $pageh);
        $this->bcAssertEqualsWithDelta($usr, $yUnit, 0.0001);
    }

    /** @throws \Throwable */
    public function testEnableDefaultPageContentTogglesFlag(): void
    {
        $obj = $this->getTestObject();
        $obj->enableDefaultPageContent(false);
        $this->assertFalse($this->getObjectProperty($obj, 'defPageContentEnabled'));

        $obj->enableDefaultPageContent(true);
        $this->assertTrue($this->getObjectProperty($obj, 'defPageContentEnabled'));
    }

    /** @throws \Throwable */
    public function testSetRTLReturnsSameInstanceAndSetsProperty(): void
    {
        $obj = $this->getTestObject();
        $ret = $obj->setRTL(true);

        $this->assertSame($obj, $ret);
        $this->assertTrue($this->getObjectProperty($obj, 'rtl'));

        $obj->setRTL(false);
        $this->assertFalse($this->getObjectProperty($obj, 'rtl'));
    }

    /** @throws \Throwable */
    #[DataProvider('unitValueConversionProvider')]
    public function testGetUnitValuePointsConvertsCommonUnits(string $input, float $expected, float $delta): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetUnitValuePoints($input);

        $this->bcAssertEqualsWithDelta($expected, $result, $delta);
    }

    /** @throws \Throwable */
    public function testGetUnitValuePointsConvertsRelativeAndViewportUnits(): void
    {
        $obj = $this->getInternalTestObject();
        $ref = [
            'parent' => 120.0,
            'font' => ['size' => 12.0, 'xheight' => 5.0, 'zerowidth' => 6.0, 'rootsize' => 16.0],
            'viewport' => ['width' => 300.0, 'height' => 200.0],
            'page' => ['width' => 300.0, 'height' => 200.0],
        ];

        $this->bcAssertEqualsWithDelta(12.0, $obj->exposeGetUnitValuePoints('2ch', $ref), 0.0001);
        $this->bcAssertEqualsWithDelta(12.0, $obj->exposeGetUnitValuePoints('10%', $ref), 0.0001);
        $this->bcAssertEqualsWithDelta(24.0, $obj->exposeGetUnitValuePoints('2em', $ref), 0.0001);
        $this->bcAssertEqualsWithDelta(10.0, $obj->exposeGetUnitValuePoints('2ex', $ref), 0.0001);
        $this->bcAssertEqualsWithDelta(24.0, $obj->exposeGetUnitValuePoints('2pc', $ref), 0.0001);
        $this->bcAssertEqualsWithDelta(32.0, $obj->exposeGetUnitValuePoints('2rem', $ref), 0.0001);
        $this->bcAssertEqualsWithDelta(20.0, $obj->exposeGetUnitValuePoints('10vh', $ref), 0.0001);
        $this->bcAssertEqualsWithDelta(30.0, $obj->exposeGetUnitValuePoints('10vw', $ref), 0.0001);
        $this->bcAssertEqualsWithDelta(30.0, $obj->exposeGetUnitValuePoints('10vmax', $ref), 0.0001);
        $this->bcAssertEqualsWithDelta(20.0, $obj->exposeGetUnitValuePoints('10vmin', $ref), 0.0001);
    }

    /** @throws \Throwable */
    public function testGetUnitValuePointsConvertsNumericDefault(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetUnitValuePoints(5.5);

        $this->assertGreaterThan(0, $result);
    }

    /** @throws \Throwable */
    public function testGetUnitValuePointsThrowsForInvalidValue(): void
    {
        $obj = $this->getInternalTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->exposeGetUnitValuePoints('invalid!!!');
    }

    /** @throws \Throwable */
    public function testGetFontValuePointsConvertsNamedFontSize(): void
    {
        $obj = $this->getInternalTestObject();
        $ref = [
            'parent' => 12.0,
            'font' => ['size' => 12.0, 'xheight' => 1.0, 'zerowidth' => 1.0, 'rootsize' => 16.0],
            'viewport' => ['width' => 800.0, 'height' => 600.0],
            'page' => ['width' => 800.0, 'height' => 600.0],
        ];

        $result = $obj->exposeGetFontValuePoints('larger', $ref);

        // 'larger' should result in parent + larger factor
        $this->assertGreaterThan($ref['parent'], $result);
    }

    /** @throws \Throwable */
    public function testGetFontValuePointsDelegatesUnknownUnitToGetUnitValuePoints(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetFontValuePoints('10mm');

        // 10 mm = 10 * 72 / 25.4 ≈ 28.35 points
        $this->bcAssertEqualsWithDelta(28.35, $result, 0.1);
    }

    /** @throws \Throwable */
    #[DataProvider('tmpRtlModeProvider')]
    public function testSetTmpRTLWithMode(string $mode, bool $expectedRtl): void
    {
        $obj = $this->getInternalTestObject();

        $obj->exposeSetTmpRTL($mode);

        $this->assertSame($expectedRtl, $obj->exposeIsRTL());
    }

    /** @throws \Throwable */
    #[DataProvider('isRtlStateProvider')]
    public function testIsRTLReturnsExpectedState(bool $globalRtl, bool $expected): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setRTL($globalRtl);

        $result = $obj->exposeIsRTL();

        $this->assertSame($expected, $result);
    }

    /** @throws \Throwable */
    public function testIsTransparencyAllowedReturnsTrueWhenPdfxDisabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', false);
        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx1a');

        $this->assertTrue($this->invokeBaseMethod($obj, 'isTransparencyAllowed') === true);
    }

    /** @throws \Throwable */
    public function testIsTransparencyAllowedDependsOnPdfxMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);

        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx4');
        $this->assertTrue($this->invokeBaseMethod($obj, 'isTransparencyAllowed'));

        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx3');
        $this->assertFalse($this->invokeBaseMethod($obj, 'isTransparencyAllowed'));
    }

    /** @throws \Throwable */
    public function testRequiresPdfxDeviceCmykReturnsFalseWhenPdfxDisabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', false);
        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx1a');

        $this->assertFalse($this->invokeBaseMethod($obj, 'requiresPdfxDeviceCmyk'));
    }

    /** @throws \Throwable */
    public function testRequiresPdfxDeviceCmykDependsOnPdfxMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);

        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx3');
        $this->assertTrue($this->invokeBaseMethod($obj, 'requiresPdfxDeviceCmyk'));

        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx5');
        $this->assertFalse($this->invokeBaseMethod($obj, 'requiresPdfxDeviceCmyk'));
    }

    /** @throws \Throwable */
    public function testDefaultFileAllowedPathsIncludesKnownRoots(): void
    {
        $obj = $this->getTestObject();
        $paths = $obj->defaultFileAllowedPaths();

        $tmpRoot = \realpath(\sys_get_temp_dir());
        $pkgRoot = \realpath(__DIR__ . '/..');
        $fontsRoot = \defined('K_PATH_FONTS') ? \realpath((string) \constant('K_PATH_FONTS')) : false;

        if (\is_string($tmpRoot) && $tmpRoot !== '') {
            $this->assertContains($tmpRoot, $paths);
        }

        if (\is_string($pkgRoot) && $pkgRoot !== '') {
            $this->assertContains($pkgRoot, $paths);
        }

        if (\is_string($fontsRoot) && $fontsRoot !== '') {
            $this->assertContains($fontsRoot, $paths);
        }
    }

    /** @throws \Throwable */
    public function testDefaultFileAllowedPathsReturnsUniqueNonEmptyResolvedPaths(): void
    {
        $obj = $this->getTestObject();
        $paths = $obj->defaultFileAllowedPaths();

        foreach ($paths as $path) {
            $this->assertNotSame('', $path);
            $this->assertSame($path, \realpath($path));
        }

        $this->assertCount(\count(\array_unique($paths)), $paths);
    }

    /** @throws \Throwable */
    public function testDefaultMarkupAllowedPathsExcludeSystemTemp(): void
    {
        $obj = $this->getTestObject();
        $paths = $obj->defaultMarkupAllowedPaths();

        $tmpRoot = \realpath(\sys_get_temp_dir());
        if (\is_string($tmpRoot) && $tmpRoot !== '') {
            $this->assertNotContains($tmpRoot, $paths);
        }

        $pkgRoot = \realpath(__DIR__ . '/..');
        if (\is_string($pkgRoot) && $pkgRoot !== '') {
            $this->assertContains($pkgRoot, $paths);
        }
    }

    /** @throws \Throwable */
    public function testDefaultFileAllowedPathsSkipsUnresolvedCandidates(): void
    {
        $obj = $this->getTestObject();
        $paths = $obj->defaultFileAllowedPaths();

        $baseFileClass = new \ReflectionClass(\Com\Tecnick\Pdf\Base::class);
        $baseFile = $baseFileClass->getFileName();
        $this->assertIsString($baseFile);

        $vendorCandidate = \dirname($baseFile) . '/../../../vendor/tecnickcom';
        $resolvedVendorCandidate = \realpath($vendorCandidate);

        if (!\is_string($resolvedVendorCandidate) || $resolvedVendorCandidate === '') {
            $this->assertNotContains($vendorCandidate, $paths);
            return;
        }

        $this->assertContains($resolvedVendorCandidate, $paths);
    }

    /** @return array<string, array{0: string, 1: float, 2: float}> */
    public static function unitValueConversionProvider(): array
    {
        return [
            'px' => ['96px', 72.0, 0.1],
            'pt' => ['12pt', 12.0, 0.0001],
            'cm' => ['1cm', 28.35, 0.1],
            'in' => ['1in', 72.0, 0.1],
        ];
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function tmpRtlModeProvider(): array
    {
        return [
            'R_mode' => ['R', true],
            'L_mode' => ['L', false],
            'empty_mode' => ['', false],
        ];
    }

    /** @return array<string, array{0: bool, 1: bool}> */
    public static function isRtlStateProvider(): array
    {
        return [
            'default_false' => [false, false],
            'global_true' => [true, true],
        ];
    }
}
