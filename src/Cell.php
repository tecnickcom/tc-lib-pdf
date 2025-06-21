<?php

/**
 * Text.php
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
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
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type StyleDataOpt from \Com\Tecnick\Pdf\Graph\Style
 * @phpstan-import-type TCellDef from \Com\Tecnick\Pdf\Base
 *
 */
abstract class Cell extends \Com\Tecnick\Pdf\Base
{
    /**
     * Set the default cell margin in user units.
     *
     * @param float $top    Top.
     * @param float $right  Right.
     * @param float $bottom Bottom.
     * @param float $left   Left.
     */
    public function setDefaultCellMargin(
        float $top,
        float $right,
        float $bottom,
        float $left
    ): void {
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
    public function setDefaultCellPadding(
        float $top,
        float $right,
        float $bottom,
        float $left
    ): void {
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
            ($borderpos == self::BORDERPOS_DEFAULT)
            || ($borderpos == self::BORDERPOS_EXTERNAL)
            || ($borderpos == self::BORDERPOS_INTERNAL)
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
    protected function adjustMinCellPadding(
        array $styles = [],
        ?array $cell = null
    ): array {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        if ($styles === []) {
            $styles = $this->graph->getCurrentStyleArray();
        }

        $border_ratio = round(self::BORDERPOS_INTERNAL + $cell['borderpos'], 1);

        $minT = 0;
        $minR = 0;
        $minB = 0;
        $minL = 0;
        if (! empty($styles['all']['lineWidth'])) {
            $minT = $this->toPoints((float) $styles['all']['lineWidth'] * $border_ratio);
            $minR = $minT;
            $minB = $minT;
            $minL = $minT;
        } elseif (
            (count($styles) == 4)
            && isset($styles[0]['lineWidth'])
            && isset($styles[1]['lineWidth'])
            && isset($styles[2]['lineWidth'])
            && isset($styles[3]['lineWidth'])
        ) {
            $minT = $this->toPoints((float) $styles[0]['lineWidth'] * $border_ratio);
            $minR = $this->toPoints((float) $styles[1]['lineWidth'] * $border_ratio);
            $minB = $this->toPoints((float) $styles[2]['lineWidth'] * $border_ratio);
            $minL = $this->toPoints((float) $styles[3]['lineWidth'] * $border_ratio);
        } else {
            return $cell;
        }

        $cell['padding']['T'] = max($cell['padding']['T'], $minT);
        $cell['padding']['R'] = max($cell['padding']['R'], $minR);
        $cell['padding']['B'] = max($cell['padding']['B'], $minB);
        $cell['padding']['L'] = max($cell['padding']['L'], $minL);
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
     */
    protected function cellMinHeight(
        float $pheight = 0,
        string $align = 'C',
        ?array $cell = null
    ): float {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        $curfont = $this->font->getCurrentFont();

        if ($pheight == 0) {
            $pheight = $curfont['height'];
        }

        return match ($align) {
            'T', 'B' => ($pheight + $cell['padding']['T'] + $cell['padding']['B']),
            'L' => ($pheight - $curfont['height'] + (2 * max(
                ($cell['padding']['T'] + $curfont['ascent']),
                ($cell['padding']['B'] - $curfont['descent'])
            ))),
            'A', 'D' => ($pheight
            - $curfont['height']
            + (2 * ($curfont['height'] + max($cell['padding']['T'], $cell['padding']['B'])))),
            // default on 'C' case
            default => ($pheight + (2 * max($cell['padding']['T'], $cell['padding']['B']))),
        };
    }

    /**
     * Returns the minimum cell width in points for the current text
     *
     * @param float     $txtwidth Text width in internal points.
     * @param string    $align    Cell horizontal alignment: L=left; C=center; R=right; J=Justify.
     * @param ?TCellDef $cell     Optional to overwrite cell parameters for padding, margin etc.
     */
    protected function cellMinWidth(
        float $txtwidth,
        string $align = 'L',
        ?array $cell = null
    ): float {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        if ($align === '' || $align === 'J') { // Justify
            $align = $this->rtl ? 'R' : 'L';
        }

        return match ($align) {
            'C' => ceil($txtwidth + (2 * max($cell['padding']['L'], $cell['padding']['R']))),
            default => ceil($txtwidth + $cell['padding']['L'] + $cell['padding']['R']),
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
    protected function cellVPos(
        float $pnty,
        float $pheight,
        string $align = 'T',
        ?array $cell = null
    ): float {
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
     * @param float    $pntx   Starting top Y coordinate in internal points.
     * @param float    $pwidth Cell width in internal points.
     * @param string   $align  Cell horizontal alignment: L=left; C=center; R=right; J=Justify.
     * @param TCellDef $cell   Optional to overwrite cell parameters for padding, margin etc.
     */
    protected function cellHPos(
        float $pntx,
        float $pwidth,
        string $align = 'L',
        ?array $cell = null
    ): float {
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
     */
    protected function cellTextVAlign(
        float $cellpheight,
        float $txtpheight = 0,
        string $align = 'C',
        ?array $cell = null
    ): float {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        $curfont = $this->font->getCurrentFont();

        if ($txtpheight == 0) {
            $txtpheight = $curfont['height'];
        }

        return match ($align) {
            'T' => ($cell['padding']['T']),
            'B' => (($cellpheight - $txtpheight) - $cell['padding']['B']),
            'L' => ((($cellpheight - $txtpheight + $curfont['height']) / 2) - $curfont['ascent']),
            'A' => (($cellpheight - $txtpheight + $curfont['height']) / 2),
            'D' => ((($cellpheight - $txtpheight + $curfont['height']) / 2) - $curfont['height']),
            // default on 'C' case
            default => (($cellpheight - $txtpheight) / 2)
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
    protected function cellTextHAlign(
        float $pwidth,
        float $txtpwidth,
        string $align = 'L',
        ?array $cell = null
    ): float {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        if ($align === '' || $align === 'J') { // Justify
            $align = $this->rtl ? 'R' : 'L';
        }

        return match ($align) {
            'C' => (($pwidth - $txtpwidth) / 2),
            'R' => ($pwidth - $cell['padding']['R'] - $txtpwidth),
            // default on 'L' case
            default => ($cell['padding']['L']),
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
     */
    protected function cellVPosFromText(
        float $txty,
        float $cellpheight,
        float $txtpheight = 0,
        string $align = 'C',
        ?array $cell = null
    ): float {
        return ($txty + $this->cellTextVAlign($cellpheight, $txtpheight, $align, $cell));
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
        ?array $cell = null
    ): float {
        return ($txtx - $this->cellTextHAlign($pwidth, $txtpwidth, $align, $cell));
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
     */
    protected function textVPosFromCell(
        float $pnty,
        float $cellpheight,
        float $txtpheight = 0,
        string $align = 'C',
        ?array $cell = null
    ): float {
        return ($pnty - $this->cellTextVAlign($cellpheight, $txtpheight, $align, $cell));
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
        ?array $cell = null
    ): float {
        return ($pntx + $this->cellTextHAlign($pwidth, $txtpwidth, $align, $cell));
    }

    /**
     * Calculates the maximum width available for a cell that fits the current region width.
     *
     * @param float     $pntx      Cell left X coordinate in internal points.
     * @param ?TCellDef $cell      Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function cellMaxWidth(
        float $pntx = 0,
        ?array $cell = null
    ): float {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        $region = $this->page->getRegion();
        return ($this->toPoints($region['RW']) - $pntx - $cell['margin']['L'] - $cell['margin']['R']);
    }

    /**
     * Calculates the maximum width available for text within a cell.
     *
     * @param float     $pwidth    Cell width in internal points.
     * @param ?TCellDef $cell      Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float The maximum width available for text within the cell.
     */
    protected function textMaxWidth(
        float $pwidth,
        ?array $cell = null
    ): float {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        return ($pwidth - $cell['padding']['L'] - $cell['padding']['R']);
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
     */
    protected function textMaxHeight(
        float $pheight,
        string $align = 'T',
        ?array $cell = null
    ): float {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        $curfont = $this->font->getCurrentFont();
        $cph = ($pheight - $cell['margin']['T'] - $cell['margin']['B']);

        // Use a match expression to determine the maximum text height based on alignment.
        return match ($align) {
            // Top or Bottom
            'T', 'B' => ($cph - $cell['padding']['T'] - $cell['padding']['B']),
            // Center on font Baseline
            'L' => ($cph + $curfont['height'] - (2 * max(
                ($cell['padding']['T'] + $curfont['ascent']),
                ($cell['padding']['B'] - $curfont['descent'])
            ))),
            // Center on font Ascent or Descent
            'A', 'D' => ($cph
            + $curfont['height']
            - (2 * ($curfont['height'] + max($cell['padding']['T'], $cell['padding']['B'])))),
            // Default to Center 'C' case
            default => ($cph - (2 * max($cell['padding']['T'], $cell['padding']['B']))),
        };
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
     */
    protected function drawCell(
        float $pntx,
        float $pnty,
        float $pwidth,
        float $pheight,
        array $styles = [],
        ?array $cell = null
    ) {

        $drawfill = (!empty($styles['all']['fillColor']));
        $drawborder = (
            !empty($styles['all']['lineWidth'])
            || !empty($styles[0]['lineWidth'])
            || !empty($styles[1]['lineWidth'])
            || !empty($styles[2]['lineWidth'])
            || !empty($styles[3]['lineWidth'])
        );

        if (!$drawfill && !$drawborder) {
            return '';
        }

        if ($cell === null) {
            $cell = $this->defcell;
        }

        $styleall = (empty($styles['all']) ? [] : $styles['all']);

        $out = $this->graph->getStartTransform();
        $stoptr = $this->graph->getStopTransform();

        if (
            $drawfill
            && $drawborder
            && ($cell['borderpos'] == self::BORDERPOS_DEFAULT)
            && (count($styles) <= 1)
        ) {
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

        $adj = (isset($styles['all']['lineWidth'])
            ? $this->toPoints((float) $styles['all']['lineWidth'] * $cell['borderpos'])
            : 0);
        $adjx = (isset($styles['3']['lineWidth'])
            ? $this->toPoints((float) $styles['3']['lineWidth'] * $cell['borderpos'])
            : $adj);
        $adjy = isset($styles['0']['lineWidth'])
            ? $this->toPoints((float) $styles['0']['lineWidth'] * $cell['borderpos'])
            : $adj;
        $adjw = $adjx + (isset($styles['1']['lineWidth'])
            ? $this->toPoints((float) $styles['1']['lineWidth'] * $cell['borderpos'])
            : $adj);
        $adjh = $adjy + (isset($styles['2']['lineWidth'])
            ? $this->toPoints((float) $styles['2']['lineWidth'] * $cell['borderpos'])
            : $adj);

        // different border styles for each side
        $out .= $this->graph->getRect(
            $this->toUnit($pntx + $adjx),
            $this->toYUnit($pnty - $adjy),
            $this->toUnit($pwidth - $adjw),
            $this->toUnit($pheight - $adjh),
            's', // close and stroke the path
            $styles,
        );

        return $out . $stoptr;
    }
}
