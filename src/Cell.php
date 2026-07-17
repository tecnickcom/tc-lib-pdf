<?php

declare(strict_types=1);

/**
 * Cell.php
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf;

/**
 * Com\Tecnick\Pdf\Cell
 *
 * Cell PDF data
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type StyleDataOpt from \Com\Tecnick\Pdf\Graph\Base
 * @phpstan-import-type TCellDef from \Com\Tecnick\Pdf\Base
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class Cell extends \Com\Tecnick\Pdf\Base
{
    /**
     * Normalize optional side aliases to numeric indexes used internally.
     *
     * Side mapping: T=0, R=1, B=2, L=3.
     * Numeric indexes take precedence when both forms are provided.
     *
     * @param array<int|string, array<array<int>|float|int|string>|float|string> $styles
     *
     * @return array<int|string, StyleDataOpt>
     */
    protected function normalizeCellSideStyles(array $styles): array
    {
        if ($styles === []) {
            return $styles;
        }

        $normalized = [];
        foreach ($styles as $key => $style) {
            if (!\is_array($style)) {
                continue;
            }

            /** @var StyleDataOpt $style */
            $normalized[$key] = $style;
        }

        $sideMap = [
            'T' => 0,
            'R' => 1,
            'B' => 2,
            'L' => 3,
        ];

        foreach ($sideMap as $sideKey => $sideIdx) {
            if (!(!isset($normalized[$sideIdx]) && isset($normalized[$sideKey]) && $normalized[$sideKey] !== [])) {
                continue;
            }

            $normalized[$sideIdx] = $normalized[$sideKey];
        }

        return $normalized;
    }

    /**
     * Set the default cell margin in user units.
     *
     * @param float $top    Top.
     * @param float $right  Right.
     * @param float $bottom Bottom.
     * @param float $left   Left.
     */
    public function setDefaultCellMargin(float $top, float $right, float $bottom, float $left): void
    {
        $this->defcell['margin']['T'] = $this->toPoints($top);
        $this->defcell['margin']['R'] = $this->toPoints($right);
        $this->defcell['margin']['B'] = $this->toPoints($bottom);
        $this->defcell['margin']['L'] = $this->toPoints($left);
    }

    /**
     * Set the default cell padding in user units.
     *
     * @param float $top    Top.
     * @param float $right  Right.
     * @param float $bottom Bottom.
     * @param float $left   Left.
     */
    public function setDefaultCellPadding(float $top, float $right, float $bottom, float $left): void
    {
        $this->defcell['padding']['T'] = $this->toPoints($top);
        $this->defcell['padding']['R'] = $this->toPoints($right);
        $this->defcell['padding']['B'] = $this->toPoints($bottom);
        $this->defcell['padding']['L'] = $this->toPoints($left);
    }

    /**
     * Sets the default cell border position.
     *
     * @param float $borderpos The border position to set:
     *                       BORDERPOS_DEFAULT
     *                       BORDERPOS_EXTERNAL
     *                       BORDERPOS_INTERNAL
     */
    public function setDefaultCellBorderPos(float $borderpos): void
    {
        if (
            $borderpos === self::BORDERPOS_DEFAULT
            || $borderpos === self::BORDERPOS_EXTERNAL
            || $borderpos === self::BORDERPOS_INTERNAL
        ) {
            $this->defcell['borderpos'] = $borderpos;
            return;
        }

        $this->defcell['borderpos'] = self::BORDERPOS_DEFAULT;
    }

    /**
     * Increase the cell padding to account for the border tickness.
     *
     * @param array<int|string, StyleDataOpt> $styles Optional to overwrite the styles (see: getCurrentStyleArray).
     * @param ?TCellDef                $cell   Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return TCellDef
     */
    protected function adjustMinCellPadding(array $styles = [], ?array $cell = null): array
    {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        if ($styles === []) {
            $styles = $this->graph->getCurrentStyleArray();
        }

        $styles = $this->normalizeCellSideStyles($styles);

        $border_ratio = \round(self::BORDERPOS_INTERNAL + $cell['borderpos'], 1);

        $minT = 0;
        $minR = 0;
        $minB = 0;
        $minL = 0;
        if (isset($styles['all']['lineWidth']) && $styles['all']['lineWidth'] > 0) {
            $minT = $this->toPoints($styles['all']['lineWidth'] * $border_ratio);
            $minR = $minT;
            $minB = $minT;
            $minL = $minT;
        } elseif (
            isset($styles[0]['lineWidth'], $styles[1]['lineWidth'], $styles[2]['lineWidth'], $styles[3]['lineWidth'])
        ) {
            $minT = $this->toPoints($styles[0]['lineWidth'] * $border_ratio);
            $minR = $this->toPoints($styles[1]['lineWidth'] * $border_ratio);
            $minB = $this->toPoints($styles[2]['lineWidth'] * $border_ratio);
            $minL = $this->toPoints($styles[3]['lineWidth'] * $border_ratio);
        } else {
            return $cell;
        }

        $cell['padding']['T'] = \max($cell['padding']['T'], $minT);
        $cell['padding']['R'] = \max($cell['padding']['R'], $minR);
        $cell['padding']['B'] = \max($cell['padding']['B'], $minB);
        $cell['padding']['L'] = \max($cell['padding']['L'], $minL);
        return $cell;
    }

    /**
     * Returns the minimum cell height in points for the current text height.
     *
     * @param float     $pheight Text height in internal points.
     * @param string    $align Text vertical alignment inside the cell:
     *                          - T=top;
     *                          - C=center;
     *                          - B=bottom;
     *                          - A=center-on-font-ascent;
     *                          - L=center-on-font-baseline;
     *                          - D=center-on-font-descent.
     * @param ?TCellDef $cell  Optional to overwrite cell parameters for padding, margin etc.
     *
     * @throws \Com\Tecnick\Pdf\Font\Exception
     */
    protected function cellMinHeight(float $pheight = 0, string $align = 'C', ?array $cell = null): float
    {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        $curfont = $this->font->getCurrentFont();
        $fontHeight = $curfont['height'];
        $fontAscent = $curfont['ascent'];
        $fontDescent = $curfont['descent'];

        if ($pheight === 0.0) {
            $pheight = $fontHeight;
        }

        return match ($align) {
            'T', 'B' => $pheight + $cell['padding']['T'] + $cell['padding']['B'],
            'L' => $pheight - $fontHeight
                + (2 * \max($cell['padding']['T'] + $fontAscent, $cell['padding']['B'] - $fontDescent)),
            'A', 'D' => $pheight - $fontHeight
                + (2 * ($fontHeight + \max($cell['padding']['T'], $cell['padding']['B']))),
            // default on 'C' case
            default => $pheight + (2 * \max($cell['padding']['T'], $cell['padding']['B'])),
        };
    }

    /**
     * Returns the minimum cell width in points for the current text
     *
     * @param float     $txtwidth Text width in internal points.
     * @param string    $align    Cell horizontal alignment: L=left; C=center; R=right; J=Justify.
     * @param ?TCellDef $cell     Optional to overwrite cell parameters for padding, margin etc.
     */
    protected function cellMinWidth(float $txtwidth, string $align = 'L', ?array $cell = null): float
    {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        if ($align === '' || $align === 'J') { // Justify
            $align = $this->rtl ? 'R' : 'L';
        }

        return match ($align) {
            'C' => \ceil($txtwidth + (2 * \max($cell['padding']['L'], $cell['padding']['R']))),
            default => \ceil($txtwidth + $cell['padding']['L'] + $cell['padding']['R']),
        };
    }

    /**
     * Returns the adjusted cell top Y coordinate to account for margins.
     *
     * @param float     $pnty    Starting top Y coordinate in internal points.
     * @param float     $pheight Cell height in internal points.
     * @param string    $align   Cell vertical alignment: T=top; C=center; B=bottom.
     * @param ?TCellDef $cell    Optional to overwrite cell parameters for padding, margin etc.
     */
    protected function cellVPos(float $pnty, float $pheight, string $align = 'T', ?array $cell = null): float
    {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        return match ($align) {
            'T' => $pnty - $cell['margin']['T'],
            'C' => $pnty + ($pheight / 2),
            'B' => $pnty + $cell['margin']['B'] + $pheight,
            default => $pnty,
        };
    }

    /**
     * Returns the adjusted cell left X coordinate to account for margins.
     *
     * @param float     $pntx   Starting left X coordinate in internal points.
     * @param float     $pwidth Cell width in internal points.
     * @param string    $align  Cell horizontal alignment: L=left; C=center; R=right; J=Justify.
     * @param ?TCellDef $cell   Optional to overwrite cell parameters for padding, margin etc.
     */
    protected function cellHPos(float $pntx, float $pwidth, string $align = 'L', ?array $cell = null): float
    {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        if ($align === '' || $align === 'J') { // Justify
            $align = $this->rtl ? 'R' : 'L';
        }

        return match ($align) {
            'L' => $pntx + $cell['margin']['L'],
            'R' => $pntx - $cell['margin']['R'] - $pwidth,
            'C' => $pntx - ($pwidth / 2),
            default => $pntx,
        };
    }

    /**
     * Returns the vertical distance between the cell top side and the text.
     *
     * @param float     $cellpheight Cell height in internal points.
     * @param float     $txtpheight  Text height in internal points.
     * @param string    $align   Text vertical alignment inside the cell:
     *                           - T=top;
     *                           - C=center;
     *                           - B=bottom;
     *                           - A=center-on-font-ascent;
     *                           - L=center-on-font-baseline;
     *                           - D=center-on-font-descent.
     * @param ?TCellDef $cell    Optional to overwrite cell parameters for padding, margin etc.
     *
     * @throws \Com\Tecnick\Pdf\Font\Exception
     */
    protected function cellTextVAlign(
        float $cellpheight,
        float $txtpheight = 0,
        string $align = 'C',
        ?array $cell = null,
    ): float {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        $curfont = $this->font->getCurrentFont();
        $fontHeight = $curfont['height'];
        $fontAscent = $curfont['ascent'];

        if ($txtpheight === 0.0) {
            $txtpheight = $fontHeight;
        }

        return match ($align) {
            'T' => $cell['padding']['T'],
            'B' => $cellpheight - $txtpheight - $cell['padding']['B'],
            'L' => (($cellpheight - $txtpheight + $fontHeight) / 2) - $fontAscent,
            'A' => ($cellpheight - $txtpheight + $fontHeight) / 2,
            'D' => (($cellpheight - $txtpheight + $fontHeight) / 2) - $fontHeight,
            // default on 'C' case
            default => ($cellpheight - $txtpheight) / 2,
        };
    }

    /**
     * Returns the horizontal distance between the cell left side and the text left side.
     *
     * @param float     $pwidth    Cell width in internal points.
     * @param float     $txtpwidth Text width in internal points.
     * @param string    $align     Text horizontal alignment inside the cell: L=left; C=center; R=right; J=Justify.
     * @param ?TCellDef $cell      Optional to overwrite cell parameters for padding, margin etc.
     */
    protected function cellTextHAlign(float $pwidth, float $txtpwidth, string $align = 'L', ?array $cell = null): float
    {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        if ($align === '' || $align === 'J') { // Justify
            $align = $this->rtl ? 'R' : 'L';
        }

        return match ($align) {
            'C' => ($pwidth - $txtpwidth) / 2,
            'R' => $pwidth - $cell['padding']['R'] - $txtpwidth,
            // default on 'L' case
            default => $cell['padding']['L'],
        };
    }

    /**
     * Returns the top Y coordinate of the cell wrapping the text.
     *
     * @param float     $txty    Text baseline top Y coordinate in internal points.
     * @param float     $cellpheight Cell height in internal points.
     * @param float     $txtpheight  Text height in internal points.
     * @param string    $align   Text vertical alignment inside the cell:
     *                           - T=top;
     *                           - C=center;
     *                           - B=bottom;
     *                           - A=center-on-font-ascent;
     *                           - L=center-on-font-baseline;
     *                           - D=center-on-font-descent.
     * @param ?TCellDef $cell    Optional to overwrite cell parameters for padding, margin etc.
     *
     * @throws \Com\Tecnick\Pdf\Font\Exception
     */
    protected function cellVPosFromText(
        float $txty,
        float $cellpheight,
        float $txtpheight = 0,
        string $align = 'C',
        ?array $cell = null,
    ): float {
        return $txty + $this->cellTextVAlign($cellpheight, $txtpheight, $align, $cell);
    }

    /**
     * Returns the left X coordinate of the cell wrapping the text.
     *
     * @param float     $txtx      Text left X coordinate in internal points.
     * @param float     $pwidth    Cell width in internal points.
     * @param float     $txtpwidth Text width in internal points.
     * @param string    $align     Text horizontal alignment inside the cell: L=left; C=center; R=right.
     * @param ?TCellDef $cell      Optional to overwrite cell parameters for padding, margin etc.
     */
    protected function cellHPosFromText(
        float $txtx,
        float $pwidth,
        float $txtpwidth,
        string $align = 'L',
        ?array $cell = null,
    ): float {
        return $txtx - $this->cellTextHAlign($pwidth, $txtpwidth, $align, $cell);
    }

    /**
     * Returns the top Y coordinate of the text inside the cell.
     *
     * @param float     $pnty    Cell top Y coordinate in internal points.
     * @param float     $cellpheight Cell height in internal points.
     * @param float     $txtpheight  Text height in internal points.
     * @param string    $align   Text vertical alignment inside the cell:
     *                           - T=top;
     *                           - C=center;
     *                           - B=bottom;
     *                           - A=center-on-font-ascent;
     *                           - L=center-on-font-baseline;
     *                           - D=center-on-font-descent.
     * @param ?TCellDef $cell    Optional to overwrite cell parameters for padding, margin etc.
     *
     * @throws \Com\Tecnick\Pdf\Font\Exception
     */
    protected function textVPosFromCell(
        float $pnty,
        float $cellpheight,
        float $txtpheight = 0,
        string $align = 'C',
        ?array $cell = null,
    ): float {
        return $pnty - $this->cellTextVAlign($cellpheight, $txtpheight, $align, $cell);
    }

    /**
     * Returns the left X coordinate of the text inside the cell.
     *
     * @param float     $pntx      Cell left X coordinate in internal points.
     * @param float     $pwidth    Cell width in internal points.
     * @param float     $txtpwidth Text width in internal points.
     * @param string    $align     Text horizontal alignment inside the cell: L=left; C=center; R=right.
     * @param ?TCellDef $cell      Optional to overwrite cell parameters for padding, margin etc.
     */
    protected function textHPosFromCell(
        float $pntx,
        float $pwidth,
        float $txtpwidth,
        string $align = 'L',
        ?array $cell = null,
    ): float {
        return $pntx + $this->cellTextHAlign($pwidth, $txtpwidth, $align, $cell);
    }

    /**
     * Calculates the maximum width available for a cell that fits the current region width.
     *
     * @param float     $pntx      Cell left X coordinate in internal points.
     * @param ?TCellDef $cell      Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     *
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    protected function cellMaxWidth(float $pntx = 0, ?array $cell = null): float
    {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        $region = $this->page->getRegion();
        return $this->toPoints($region['RW']) - $pntx - $cell['margin']['L'] - $cell['margin']['R'];
    }

    /**
     * Calculates the maximum width available for text within a cell.
     *
     * @param float     $pwidth    Cell width in internal points.
     * @param ?TCellDef $cell      Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float The maximum width available for text within the cell.
     */
    protected function textMaxWidth(float $pwidth, ?array $cell = null): float
    {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        return $pwidth - $cell['padding']['L'] - $cell['padding']['R'];
    }

    /**
     * Calculates the maximum height available for text within a cell.
     *
     * @param float     $pheight Available vertical space in internal points.
     * @param string    $align   Text vertical alignment inside the cell:
     *                           - T=top;
     *                           - C=center;
     *                           - B=bottom;
     *                           - A=center-on-font-ascent;
     *                           - L=center-on-font-baseline;
     *                           - D=center-on-font-descent.
     * @param ?TCellDef $cell      Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float The maximum width available for text within the cell.
     *
     * @throws \Com\Tecnick\Pdf\Font\Exception
     */
    protected function textMaxHeight(float $pheight, string $align = 'T', ?array $cell = null): float
    {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        $curfont = $this->font->getCurrentFont();
        $fontHeight = $curfont['height'];
        $fontAscent = $curfont['ascent'];
        $fontDescent = $curfont['descent'];
        $cph = $pheight - $cell['margin']['T'] - $cell['margin']['B'];

        // Use a match expression to determine the maximum text height based on alignment.
        return match ($align) {
            // Top or Bottom
            'T', 'B' => $cph - $cell['padding']['T'] - $cell['padding']['B'],
            // Center on font Baseline
            'L' => $cph + $fontHeight
                - (2 * \max($cell['padding']['T'] + $fontAscent, $cell['padding']['B'] - $fontDescent)),
            // Center on font Ascent or Descent
            'A', 'D' => $cph + $fontHeight - (2 * ($fontHeight + \max($cell['padding']['T'], $cell['padding']['B']))),
            // Default to Center 'C' case
            default => $cph - (2 * \max($cell['padding']['T'], $cell['padding']['B'])),
        };
    }

    /**
     * Returns true when the style defines a visible border stroke.
     *
     * @param StyleDataOpt $style Style data.
     */
    protected function styleHasVisibleLineWidth(array $style): bool
    {
        return isset($style['lineWidth']) && $style['lineWidth'] > 0.0;
    }

    /**
     * Returns the PDF code to draw the text cell border and background.
     *
     * @param float     $pntx     Cell left X coordinate in internal points.
     * @param float     $pnty     Cell top Y coordinate in internal points.
     * @param float     $pwidth   Cell width in internal points.
     * @param float     $pheight  Cell height in internal points.
     * @param array<int|string, StyleDataOpt> $styles Optional to overwrite the styles (see: getCurrentStyleArray).
     * @param ?TCellDef $cell     Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return string
     *
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    protected function drawCell(
        float $pntx,
        float $pnty,
        float $pwidth,
        float $pheight,
        array $styles = [],
        ?array $cell = null,
    ): string {
        $styles = $this->normalizeCellSideStyles($styles);

        $drawfill = isset($styles['all']['fillColor']) && $styles['all']['fillColor'] !== '';
        $drawborder =
            isset($styles['all']['lineWidth']) && $styles['all']['lineWidth'] > 0
            || isset($styles[0]['lineWidth']) && $styles[0]['lineWidth'] > 0
            || isset($styles[1]['lineWidth']) && $styles[1]['lineWidth'] > 0
            || isset($styles[2]['lineWidth']) && $styles[2]['lineWidth'] > 0
            || isset($styles[3]['lineWidth']) && $styles[3]['lineWidth'] > 0;

        if (!$drawfill && !$drawborder) {
            return '';
        }

        if ($cell === null) {
            $cell = $this->defcell;
        }

        $styleall = $styles['all'] ?? [];

        $out = $this->graph->getStartTransform();
        $stoptr = $this->graph->getStopTransform();

        if ($drawfill && $drawborder && $cell['borderpos'] === self::BORDERPOS_DEFAULT && \count($styles) <= 1) {
            // single default border style for all sides
            $out .= $this->graph->getBasicRect(
                $this->toUnit($pntx),
                $this->toYUnit($pnty),
                $this->toUnit($pwidth),
                $this->toUnit($pheight),
                'b', // close, fill, and then stroke the path
                $styleall,
            );

            return $out . $stoptr;
        }

        if ($drawfill) {
            $out .= $this->graph->getBasicRect(
                $this->toUnit($pntx),
                $this->toYUnit($pnty),
                $this->toUnit($pwidth),
                $this->toUnit($pheight),
                'f', // fill the path
                $styleall,
            );
        }

        if (!$drawborder) {
            return $out . $stoptr;
        }

        $adj = isset($styles['all']['lineWidth'])
            ? $this->toPoints($styles['all']['lineWidth'] * $cell['borderpos'])
            : 0.0;
        $adjx = isset($styles['3']['lineWidth'])
            ? $this->toPoints($styles['3']['lineWidth'] * $cell['borderpos'])
            : $adj;
        $adjy = isset($styles['0']['lineWidth'])
            ? $this->toPoints($styles['0']['lineWidth'] * $cell['borderpos'])
            : $adj;
        $adjw =
            $adjx
            + (
                isset($styles['1']['lineWidth'])
                    ? $this->toPoints($styles['1']['lineWidth'] * $cell['borderpos'])
                    : $adj
            );
        $adjh =
            $adjy
            + (
                isset($styles['2']['lineWidth'])
                    ? $this->toPoints($styles['2']['lineWidth'] * $cell['borderpos'])
                    : $adj
            );

        // draw only sides with a positive stroke width;
        // PDF "0 w" is a hairline stroke, not "no border".
        $rectx = $this->toUnit($pntx + $adjx);
        $recty = $this->toYUnit($pnty - $adjy);
        $rectw = $this->toUnit($pwidth - $adjw);
        $recth = $this->toUnit($pheight - $adjh);
        $sidestyles = [
            0 => $styles[0] ?? $styleall,
            1 => $styles[1] ?? $styleall,
            2 => $styles[2] ?? $styleall,
            3 => $styles[3] ?? $styleall,
        ];

        $out .= $this->drawCellBorderSides($rectx, $recty, $rectw, $recth, $sidestyles);

        return $out . $stoptr;
    }

    /**
     * Returns the PDF code to stroke the (possibly partial) cell border.
     *
     * Consecutive visible sides that share an identical style are stroked as a
     * single continuous path so their shared corners are joined (mitered)
     * instead of being drawn as independent, butt-capped segments that would
     * leave the corners open. Sides with differing styles are kept separate so
     * multi-colour borders keep their own line caps.
     *
     * @param float $rectx Border rectangle left X coordinate (user units).
     * @param float $recty Border rectangle top Y coordinate (user units).
     * @param float $rectw Border rectangle width (user units).
     * @param float $recth Border rectangle height (user units).
     * @param array{0: StyleDataOpt, 1: StyleDataOpt, 2: StyleDataOpt, 3: StyleDataOpt} $sidestyles
     *        Side styles (0=top, 1=right, 2=bottom, 3=left).
     *
     * @return string
     */
    protected function drawCellBorderSides(
        float $rectx,
        float $recty,
        float $rectw,
        float $recth,
        array $sidestyles,
    ): string {
        // Corner coordinates; sides chain cyclically:
        // 0 (top) -> 1 (right) -> 2 (bottom) -> 3 (left) -> 0.
        $tlx = $rectx;
        $tly = $recty;
        $trx = $rectx + $rectw;
        $bry = $recty + $recth;

        // [startX, startY, endX, endY] of each side, addressed by index.
        $segOf = static fn(int $side) => match ($side) {
            0 => [$tlx, $tly, $trx, $tly], // top:    TL -> TR
            1 => [$trx, $tly, $trx, $bry], // right:  TR -> BR
            2 => [$trx, $bry, $tlx, $bry], // bottom: BR -> BL
            default => [$tlx, $bry, $tlx, $tly], // left: BL -> TL
        };
        $styleOf = static fn(int $side) => match ($side) {
            0 => $sidestyles[0],
            1 => $sidestyles[1],
            2 => $sidestyles[2],
            default => $sidestyles[3],
        };

        $v0 = $this->styleHasVisibleLineWidth($sidestyles[0]);
        $v1 = $this->styleHasVisibleLineWidth($sidestyles[1]);
        $v2 = $this->styleHasVisibleLineWidth($sidestyles[2]);
        $v3 = $this->styleHasVisibleLineWidth($sidestyles[3]);
        $visOf = static fn(int $side) => match ($side) {
            0 => $v0,
            1 => $v1,
            2 => $v2,
            default => $v3,
        };

        // All four sides visible with an identical style: stroke a single closed
        // rectangle so every corner is joined by the line-join.
        if (
            $v0
            && $v1
            && $v2
            && $v3
            && $sidestyles[0] === $sidestyles[1]
            && $sidestyles[1] === $sidestyles[2]
            && $sidestyles[2] === $sidestyles[3]
        ) {
            return $this->graph->getBasicRect($rectx, $recty, $rectw, $recth, 'S', $sidestyles[0]);
        }

        // A side and the next one ($side + 1) % 4 can share a stroked path when
        // both are visible and carry the same style.
        $joinOf = static fn(int $side): bool => (
            $visOf($side)
            && $visOf(($side + 1) % 4)
            && $styleOf($side) === $styleOf(($side + 1) % 4)
        );

        $out = '';
        foreach ([0, 1, 2, 3] as $start) {
            if (!$visOf($start) || $joinOf(($start + 3) % 4)) {
                continue; // not visible, or this side continues a run started earlier
            }

            // Walk the maximal run of consecutive same-styled visible sides and
            // stroke it as a single continuous (poly)line so the shared corners
            // are joined instead of being drawn as independent, butt-capped
            // segments.
            $seg = $segOf($start);
            $poly = [$seg[0], $seg[1], $seg[2], $seg[3]];
            $cur = $start;
            while ($joinOf($cur) && (($cur + 1) % 4) !== $start) {
                $cur = ($cur + 1) % 4;
                $seg = $segOf($cur);
                $poly[] = $seg[2];
                $poly[] = $seg[3];
            }

            $out .= $this->graph->getBasicPolygon($poly, 'S', $styleOf($start));
        }

        return $out;
    }

    /**
     * Format a text string for output.
     *
     * @param string $str String to escape.
     * @param int    $oid Current PDF object number.
     * @param bool   $bom If true set the Byte Order Mark (BOM).
     *
     * @return string escaped string.
     *
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     * @throws \Com\Tecnick\Unicode\Exception
     */
    protected function getOutTextString(string $str, int $oid, bool $bom = false): string
    {
        if ($this->isunicode) {
            $str = $this->uniconv->toUTF16BE($str);
            if ($bom) {
                $str = "\xFE\xFF" . $str; // Byte Order Mark (BOM)
            }
        }

        return $this->encrypt->escapeDataString($str, $oid);
    }
}
