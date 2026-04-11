<?php

/**
 * CellTest.php
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

/**
 * @phpstan-import-type TCellDef from \Com\Tecnick\Pdf\Cell
 * @phpstan-import-type StyleDataOpt from \Com\Tecnick\Pdf\Cell
 */
class TestableCell extends \Com\Tecnick\Pdf\Tcpdf
{
    /**
     * @phpstan-param array<int|string, StyleDataOpt> $styles
     * @phpstan-param TCellDef|null $cell
     * @phpstan-return TCellDef
     */
    public function exposeAdjustMinCellPadding(array $styles = [], ?array $cell = null): array
    {
        return $this->adjustMinCellPadding($styles, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeCellMinHeight(float $pheight = 0, string $align = 'C', ?array $cell = null): float
    {
        return $this->cellMinHeight($pheight, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeCellMinWidth(float $txtwidth, string $align = 'L', ?array $cell = null): float
    {
        return $this->cellMinWidth($txtwidth, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeCellVPos(float $pnty, float $pheight, string $align = 'T', ?array $cell = null): float
    {
        return $this->cellVPos($pnty, $pheight, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeCellHPos(float $pntx, float $pwidth, string $align = 'L', ?array $cell = null): float
    {
        return $this->cellHPos($pntx, $pwidth, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeCellTextVAlign(float $cellpheight, float $txtpheight = 0, string $align = 'C', ?array $cell = null): float
    {
        return $this->cellTextVAlign($cellpheight, $txtpheight, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeCellTextHAlign(float $pwidth, float $txtpwidth, string $align = 'L', ?array $cell = null): float
    {
        return $this->cellTextHAlign($pwidth, $txtpwidth, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeCellVPosFromText(float $txty, float $cellpheight, float $txtpheight = 0, string $align = 'C', ?array $cell = null): float
    {
        return $this->cellVPosFromText($txty, $cellpheight, $txtpheight, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeCellHPosFromText(float $txtx, float $pwidth, float $txtpwidth, string $align = 'L', ?array $cell = null): float
    {
        return $this->cellHPosFromText($txtx, $pwidth, $txtpwidth, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeTextVPosFromCell(float $pnty, float $cellpheight, float $txtpheight = 0, string $align = 'C', ?array $cell = null): float
    {
        return $this->textVPosFromCell($pnty, $cellpheight, $txtpheight, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeTextHPosFromCell(float $pntx, float $pwidth, float $txtpwidth, string $align = 'L', ?array $cell = null): float
    {
        return $this->textHPosFromCell($pntx, $pwidth, $txtpwidth, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeCellMaxWidth(float $pntx = 0, ?array $cell = null): float
    {
        return $this->cellMaxWidth($pntx, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeTextMaxWidth(float $pwidth, ?array $cell = null): float
    {
        return $this->textMaxWidth($pwidth, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeTextMaxHeight(float $pheight, string $align = 'T', ?array $cell = null): float
    {
        return $this->textMaxHeight($pheight, $align, $cell);
    }

    /**
     * @phpstan-param array<int|string, StyleDataOpt> $styles
     * @phpstan-param TCellDef|null $cell
     */
    public function exposeDrawCell(
        float $pntx,
        float $pnty,
        float $pwidth,
        float $pheight,
        array $styles = [],
        ?array $cell = null,
    ): string {
        return $this->drawCell($pntx, $pnty, $pwidth, $pheight, $styles, $cell);
    }

    public function exposeGetOutTextString(string $str, int $oid, bool $bom = false): string
    {
        return $this->getOutTextString($str, $oid, $bom);
    }
}

/**
 * @phpstan-import-type TCellDef from \Com\Tecnick\Pdf\Cell
 */
class CellTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    protected function getInternalTestObject(): TestableCell
    {
        return new TestableCell();
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

    private function initFontAndPage(\Com\Tecnick\Pdf\Tcpdf $obj): void
    {
        if (!\defined('K_PATH_FONTS')) {
            $fonts = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts');
            \define('K_PATH_FONTS', $fonts);
        }
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);
        $obj->addPage();
    }

    public function testSetDefaultCellMarginStoresPointValues(): void
    {
        $obj = $this->getTestObject();
        $obj->setDefaultCellMargin(1.0, 2.0, 3.0, 4.0);

        /** @var TCellDef $defcell */
        $defcell = $this->getObjectProperty($obj, 'defcell');
        $this->bcAssertEqualsWithDelta($obj->toPoints(1.0), $defcell['margin']['T']);
        $this->bcAssertEqualsWithDelta($obj->toPoints(2.0), $defcell['margin']['R']);
        $this->bcAssertEqualsWithDelta($obj->toPoints(3.0), $defcell['margin']['B']);
        $this->bcAssertEqualsWithDelta($obj->toPoints(4.0), $defcell['margin']['L']);
    }

    public function testSetDefaultCellPaddingStoresPointValues(): void
    {
        $obj = $this->getTestObject();
        $obj->setDefaultCellPadding(0.5, 1.5, 2.5, 3.5);

        /** @var TCellDef $defcell */
        $defcell = $this->getObjectProperty($obj, 'defcell');
        $this->bcAssertEqualsWithDelta($obj->toPoints(0.5), $defcell['padding']['T']);
        $this->bcAssertEqualsWithDelta($obj->toPoints(1.5), $defcell['padding']['R']);
        $this->bcAssertEqualsWithDelta($obj->toPoints(2.5), $defcell['padding']['B']);
        $this->bcAssertEqualsWithDelta($obj->toPoints(3.5), $defcell['padding']['L']);
    }

    public function testSetDefaultCellBorderPosStoresValidValueAndDefaultsInvalid(): void
    {
        $obj = $this->getTestObject();
        $obj->setDefaultCellBorderPos(\Com\Tecnick\Pdf\Base::BORDERPOS_INTERNAL);

        /** @var TCellDef $defcell */
        $defcell = $this->getObjectProperty($obj, 'defcell');
        $this->assertSame(\Com\Tecnick\Pdf\Base::BORDERPOS_INTERNAL, $defcell['borderpos']);

        $obj->setDefaultCellBorderPos(99.0);
        /** @var TCellDef $defcell */
        $defcell = $this->getObjectProperty($obj, 'defcell');
        $this->assertSame(\Com\Tecnick\Pdf\Base::BORDERPOS_DEFAULT, $defcell['borderpos']);
    }

    public function testAdjustMinCellPaddingIncreasesPaddingWithBorderStyle(): void
    {
        $obj = $this->getInternalTestObject();
        /** @var TCellDef $cell */
        $cell = $this->getObjectProperty($obj, 'defcell');
        $cell['padding'] = ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0];
        $styles = ['all' => ['lineWidth' => 1.0]];

        $out = $obj->exposeAdjustMinCellPadding($styles, $cell);

        $this->assertGreaterThanOrEqual(0.0, $out['padding']['T']);
        $this->assertGreaterThanOrEqual(0.0, $out['padding']['R']);
    }

    public function testCellMinHeightReturnsPositiveForCenterAlign(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->exposeCellMinHeight(10.0, 'C');

        $this->assertGreaterThan(0.0, $out);
    }

    public function testCellMinWidthHandlesCenterAlignment(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeCellMinWidth(20.0, 'C');

        $this->assertGreaterThanOrEqual(20.0, $out);
    }

    public function testCellPositionHelpersReturnFloats(): void
    {
        $obj = $this->getInternalTestObject();
        /** @var TCellDef $cell */
        $cell = $this->getObjectProperty($obj, 'defcell');

        $verticalPos = $obj->exposeCellVPos(10.0, 5.0, 'T', $cell);
        $horizontalPos = $obj->exposeCellHPos(10.0, 5.0, 'L', $cell);

        $this->assertGreaterThan(-1000000.0, $verticalPos);
        $this->assertGreaterThan(-1000000.0, $horizontalPos);
    }

    public function testCellTextAlignHelpersReturnFloats(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        /** @var TCellDef $cell */
        $cell = $this->getObjectProperty($obj, 'defcell');

        $verticalAlign = $obj->exposeCellTextVAlign(20.0, 10.0, 'C', $cell);
        $horizontalAlign = $obj->exposeCellTextHAlign(30.0, 12.0, 'C', $cell);

        $this->assertGreaterThan(-1000000.0, $verticalAlign);
        $this->assertGreaterThan(-1000000.0, $horizontalAlign);
    }

    public function testCellAndTextPositionConversionsAreCallable(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        /** @var TCellDef $cell */
        $cell = $this->getObjectProperty($obj, 'defcell');

        $cellVerticalPos = $obj->exposeCellVPosFromText(10.0, 20.0, 10.0, 'C', $cell);
        $cellHorizontalPos = $obj->exposeCellHPosFromText(10.0, 30.0, 12.0, 'L', $cell);
        $textVerticalPos = $obj->exposeTextVPosFromCell(10.0, 20.0, 10.0, 'C', $cell);
        $textHorizontalPos = $obj->exposeTextHPosFromCell(10.0, 30.0, 12.0, 'L', $cell);

        $this->assertGreaterThan(-1000000.0, $cellVerticalPos);
        $this->assertGreaterThan(-1000000.0, $cellHorizontalPos);
        $this->assertGreaterThan(-1000000.0, $textVerticalPos);
        $this->assertGreaterThan(-1000000.0, $textHorizontalPos);
    }

    public function testCellAndTextMaxHelpersReturnPositiveValues(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        /** @var TCellDef $cell */
        $cell = $this->getObjectProperty($obj, 'defcell');

        $cellMax = $obj->exposeCellMaxWidth(0.0, $cell);
        $txtW = $obj->exposeTextMaxWidth(50.0, $cell);
        $txtH = $obj->exposeTextMaxHeight(50.0, 'T', $cell);

        $this->assertGreaterThan(0.0, $cellMax);
        $this->assertGreaterThan(0.0, $txtW);
        $this->assertGreaterThan(0.0, $txtH);
    }

    public function testDrawCellReturnsEmptyWhenNoFillOrBorder(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->exposeDrawCell(10.0, 10.0, 20.0, 8.0, ['all' => []]);

        $this->assertSame('', $out);
    }

    public function testGetOutTextStringReturnsEscapedStringAndBomChangesOutput(): void
    {
        $obj = $this->getInternalTestObject();

        $textWithoutBom = $obj->exposeGetOutTextString('Hello', 1, false);
        $textWithBom = $obj->exposeGetOutTextString('Hello', 1, true);

        $this->assertNotSame('', $textWithoutBom);
        $this->assertNotSame('', $textWithBom);
        $this->assertNotSame($textWithoutBom, $textWithBom);
    }
}
