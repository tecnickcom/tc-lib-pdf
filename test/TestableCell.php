<?php

/**
 * TestableCell.php
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
    public function exposeCellTextVAlign(
        float $cellpheight,
        float $txtpheight = 0,
        string $align = 'C',
        ?array $cell = null,
    ): float {
        return $this->cellTextVAlign($cellpheight, $txtpheight, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeCellTextHAlign(
        float $pwidth,
        float $txtpwidth,
        string $align = 'L',
        ?array $cell = null,
    ): float {
        return $this->cellTextHAlign($pwidth, $txtpwidth, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeCellVPosFromText(
        float $txty,
        float $cellpheight,
        float $txtpheight = 0,
        string $align = 'C',
        ?array $cell = null,
    ): float {
        return $this->cellVPosFromText($txty, $cellpheight, $txtpheight, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeCellHPosFromText(
        float $txtx,
        float $pwidth,
        float $txtpwidth,
        string $align = 'L',
        ?array $cell = null,
    ): float {
        return $this->cellHPosFromText($txtx, $pwidth, $txtpwidth, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeTextVPosFromCell(
        float $pnty,
        float $cellpheight,
        float $txtpheight = 0,
        string $align = 'C',
        ?array $cell = null,
    ): float {
        return $this->textVPosFromCell($pnty, $cellpheight, $txtpheight, $align, $cell);
    }

    /** @phpstan-param TCellDef|null $cell */
    public function exposeTextHPosFromCell(
        float $pntx,
        float $pwidth,
        float $txtpwidth,
        string $align = 'L',
        ?array $cell = null,
    ): float {
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
