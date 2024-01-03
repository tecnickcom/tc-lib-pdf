<?php

/**
 * Text.php
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2024 Nicola Asuni - Tecnick.com LTD
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
 * @copyright 2002-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type StyleDataOpt from \Com\Tecnick\Pdf\Graph\Style
 *
 * @phpstan-type TCellDef array{
 *        'margin': array{
 *            'T': float,
 *            'R': float,
 *            'B': float,
 *            'L': float,
 *        },
 *        'padding': array{
 *            'T': float,
 *            'R': float,
 *            'B': float,
 *            'L': float,
 *        },
 *    }
 */
abstract class Cell extends \Com\Tecnick\Pdf\Base
{
    /**
     * Default values for cell.
     *
     * @var TCellDef
     */
    protected $defcell = [
        'margin' => [
            'T' => 0,
            'R' => 0,
            'B' => 0,
            'L' => 0,
        ],
        'padding' => [
            'T' => 0,
            'R' => 0,
            'B' => 0,
            'L' => 0,
        ],
    ];

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
     * Increase the cell padding to account for the border tickness.
     *
     * @param array<int, StyleDataOpt> $styles Optional to overwrite the styles (see: getCurrentStyleArray).
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

        $minT = 0;
        $minR = 0;
        $minB = 0;
        $minL = 0;
        if (! empty($styles['all']['lineWidth'])) {
            $minT = $this->toPoints($styles['all']['lineWidth']);
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
            $minT = $this->toPoints((float) $styles[0]['lineWidth']);
            $minR = $this->toPoints((float) $styles[1]['lineWidth']);
            $minB = $this->toPoints((float) $styles[2]['lineWidth']);
            $minL = $this->toPoints((float) $styles[3]['lineWidth']);
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
     * Returns the minimum cell height in points for the current font.
     *
     * @param string    $align Text vertical alignment inside the cell:
     *                         - T=top; - C=center; - B=bottom; -
     *                         A=center-on-font-ascent; -
     *                         L=center-on-font-baseline; -
     *                         D=center-on-font-descent.
     * @param ?TCellDef $cell  Optional to overwrite cell parameters for padding, margin etc.
     */
    protected function cellMinHeight(
        string $align = 'C',
        ?array $cell = null
    ): float {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        $curfont = $this->font->getCurrentFont();
        switch ($align) {
            default:
            case 'C': // Center
                return ($curfont['height'] + (2 * max($cell['padding']['T'], $cell['padding']['B'])));
            case 'T': // Top
            case 'B': // Bottom
                return ($curfont['height'] + $cell['padding']['T'] + $cell['padding']['B']);
            case 'L': // Center on font Baseline
                return (2 * max(
                    ($cell['padding']['T'] + $curfont['ascent']),
                    ($cell['padding']['B'] - $curfont['descent'])
                ));
            case 'A': // Center on font Ascent
            case 'D': // Center on font Descent
                return (2 * ($curfont['height'] + max($cell['padding']['T'], $cell['padding']['B'])));
        }
    }

    /**
     * Returns the minimum cell width in points for the current text
     *
     * @param float     $txtwidth Text width in internal points.
     * @param string    $align    Cell horizontal alignment: L=left; C=center; R=right.
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

        switch ($align) {
            default:
            case 'L': // Left
            case 'R': // Right
                return ($txtwidth + $cell['padding']['L'] + $cell['padding']['R']);
            case 'C': // Center
                return ($txtwidth + (2 * max($cell['padding']['L'], $cell['padding']['R'])));
        }
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
     * @param string   $align  Cell horizontal alignment: L=left; C=center; R=right.
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

        return match ($align) {
            'L' => $pntx + $cell['margin']['L'],
            'R' => $pntx - $cell['margin']['R'] - $pwidth,
            'C' => $pntx - ($pwidth / 2),
            default => $pntx,
        };
    }

    /**
     * Returns the vertical distance between the cell top side and the text baseline.
     *
     * @param float     $pheight Cell height in internal points.
     * @param string    $align   Text vertical alignment inside the cell:
     *                           - T=top; - C=center; - B=bottom; -
     *                           A=center-on-font-ascent; -
     *                           L=center-on-font-baseline; -
     *                           D=center-on-font-descent.
     * @param ?TCellDef $cell    Optional to overwrite cell parameters for padding, margin etc.
     */
    protected function cellTextVAlign(
        float $pheight,
        string $align = 'C',
        ?array $cell = null
    ): float {
        if ($cell === null) {
            $cell = $this->defcell;
        }

        $curfont = $this->font->getCurrentFont();
        switch ($align) {
            default:
            case 'C': // Center
                return (($pheight / 2) + $curfont['midpoint']);
            case 'T': // Top
                return ($cell['padding']['T'] + $curfont['ascent']);
            case 'B': // Bottom
                return ($pheight - $cell['padding']['B'] + $curfont['descent']);
            case 'L': // Center on font Baseline
                return ($pheight / 2);
            case 'A': // Center on font Ascent
                return (($pheight / 2) + $curfont['ascent']);
            case 'D': // Center on font Descent
                return (($pheight / 2) + $curfont['descent']);
        }
    }

    /**
     * Returns the horizontal distance between the cell left side and the text left side.
     *
     * @param float     $pwidth    Cell width in internal points.
     * @param float     $txtpwidth Text width in internal points.
     * @param string    $align     Text vertical alignment inside the cell: L=left; C=center; R=right.
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

        switch ($align) {
            default:
            case 'L': // Left
                return ($cell['padding']['L']);
            case 'C': // Center
                return (($pwidth - $txtpwidth) / 2);
            case 'R': // Right
                return ($pwidth - $cell['padding']['R'] - $txtpwidth);
        }
    }

    /**
     * Returns the top Y coordinate of the cell wrapping the text.
     *
     * @param float     $txty    Text baseline top Y coordinate in internal points.
     * @param float     $pheight Cell height in internal points.
     * @param string    $align   Text vertical alignment inside the cell:
     *                           - T=top; - C=center; - B=bottom; -
     *                           A=center-on-font-ascent; -
     *                           L=center-on-font-baseline; -
     *                           D=center-on-font-descent.
     * @param ?TCellDef $cell    Optional to overwrite cell parameters for padding, margin etc.
     */
    protected function cellVPosFromText(
        float $txty,
        float $pheight,
        string $align = 'C',
        ?array $cell = null
    ): float {
        return ($txty + $this->cellTextVAlign($pheight, $align, $cell));
    }

    /**
     * Returns the left X coordinate of the cell wrapping the text.
     *
     * @param float     $txtx      Text left X coordinate in internal points.
     * @param float     $pwidth    Cell width in internal points.
     * @param float     $txtpwidth Text width in internal points.
     * @param string    $align     Text vertical alignment inside the cell: L=left; C=center; R=right.
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
     * Returns the baseline Y coordinate of the text inside the cell.
     *
     * @param float     $pnty    Cell top Y coordinate in internal points.
     * @param float     $pheight Cell height in internal points.
     * @param string    $align   Text vertical alignment inside the cell:
     *                           - T=top; - C=center; - B=bottom; -
     *                           A=center-on-font-ascent; -
     *                           L=center-on-font-baseline; -
     *                           D=center-on-font-descent.
     * @param ?TCellDef $cell    Optional to overwrite cell parameters for padding, margin etc.
     */
    protected function textVPosFromCell(
        float $pnty,
        float $pheight,
        string $align = 'C',
        ?array $cell = null
    ): float {
        return ($pnty - $this->cellTextVAlign($pheight, $align, $cell));
    }

    /**
     * Returns the left X coordinate of the text inside the cell.
     *
     * @param float     $txtx      Text left X coordinate in internal points.
     * @param float     $pwidth    Cell width in internal points.
     * @param float     $txtpwidth Text width in internal points.
     * @param string    $align     Text vertical alignment inside the cell: L=left; C=center; R=right.
     * @param ?TCellDef $cell      Optional to overwrite cell parameters for padding, margin etc.
     */
    protected function textHPosFromCell(
        float $txtx,
        float $pwidth,
        float $txtpwidth,
        string $align = 'L',
        ?array $cell = null
    ): float {
        return ($txtx + $this->cellTextHAlign($pwidth, $txtpwidth, $align, $cell));
    }
}
