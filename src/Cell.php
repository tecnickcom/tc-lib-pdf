<?php

/**
 * Text.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2023 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf;

/**
 * Com\Tecnick\Pdf\Cell
 *
 * Cell PDF data
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2023 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
abstract class Cell extends \Com\Tecnick\Pdf\Base
{
    /**
     * Default values for cell.
     *
     * @var array
     */
    protected $defcell = array(
        'margin'  => array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0),
        'padding' => array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0)
    );

    /**
     * Set the default cell margin in user units.
     *
     * @param float $top    Top.
     * @param float $right  Right.
     * @param float $bottom Bottom.
     * @param float $left   Left.
     */
    public function setDefaultCellMargin($top, $right, $bottom, $left)
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
    public function setDefaultCellPadding($top, $right, $bottom, $left)
    {
        $this->defcell['padding']['T'] = $this->toPoints($top);
        $this->defcell['padding']['R'] = $this->toPoints($right);
        $this->defcell['padding']['B'] = $this->toPoints($bottom);
        $this->defcell['padding']['L'] = $this->toPoints($left);
    }

    /**
     * Increase the cell padding to account for the border tickness.
     *
     * @param array  $styles Optional to overwrite the styles (see: getCurrentStyleArray).
     * @param array  $cell   Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return array
     */
    protected function adjustMinCellPadding(array $styles = array(), array $cell = array())
    {
        if (empty($cell)) {
            $cell = $this->defcell;
        }
        if (empty($styles)) {
            $styles = $this->graph->getCurrentStyleArray();
        }
        $minT = $minR = $minB = $minL = 0;
        if (!empty($styles['all']['lineWidth'])) {
            $minT = $minR = $minB = $minL = $this->toPoints($styles['all']['lineWidth']);
        } elseif (count($styles) == 4) {
            $minT = $this->toPoints($styles[0]['lineWidth']);
            $minR = $this->toPoints($styles[1]['lineWidth']);
            $minB = $this->toPoints($styles[2]['lineWidth']);
            $minL = $this->toPoints($styles[3]['lineWidth']);
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
     * @param string $align   Text vertical alignment inside the cell:
     *                        - T=top;
     *                        - C=center;
     *                        - B=bottom;
     *                        - A=center-on-font-ascent;
     *                        - L=center-on-font-baseline;
     *                        - D=center-on-font-descent.
     * @param array  $cell  Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function cellMinHeight($align = 'C', array $cell = array())
    {
        if (empty($cell)) {
            $cell = $this->defcell;
        }
        $curfont = $this->font->getCurrentFont();
        switch ($align) {
            default:
            case 'C': // Center
                return ($curfont['height'] +  (2 * max($cell['padding']['T'], $cell['padding']['B'])));
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
     * @param float  $txtwidth Text width in internal points.
     * @param string $align    Cell horizontal alignment: L=left; C=center; R=right.
     * @param array  $cell     Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function cellMinWidth($txtwidth, $align = 'L', array $cell = array())
    {
        if (empty($cell)) {
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
     * @param float  $pnty     Starting top Y coordinate in internal points.
     * @param float  $pheight  Cell height in internal points.
     * @param string $align    Cell vertical alignment: T=top; C=center; B=bottom.
     * @param array  $cell     Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function cellVPos($pnty, $pheight, $align = 'T', array $cell = array())
    {
        if (empty($cell)) {
            $cell = $this->defcell;
        }
        switch ($align) {
            case 'T': // Top
                return ($pnty - $cell['margin']['T']);
            case 'C': // Center
                return ($pnty + ($pheight / 2));
            case 'B': // Bottom
                return ($pnty + $cell['margin']['B'] + $pheight);
        }
        return $pnty;
    }

    /**
     * Returns the adjusted cell left X coordinate to account for margins.
     *
     * @param float  $pntx     Starting top Y coordinate in internal points.
     * @param float  $pwidth   Cell width in internal points.
     * @param string $align    Cell horizontal alignment: L=left; C=center; R=right.
     * @param array  $cell     Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function cellHPos($pntx, $pwidth, $align = 'L', array $cell = array())
    {
        if (empty($cell)) {
            $cell = $this->defcell;
        }
        switch ($align) {
            case 'L': // Left
                return ($pntx + $cell['margin']['L']);
            case 'R': // Right
                return ($pntx - $cell['margin']['R'] - $pwidth);
            case 'C': // Center
                return ($pntx - ($pwidth / 2));
        }
        return $pntx;
    }

    /**
     * Returns the vertical distance between the cell top side and the text baseline.
     *
     * @param float  $pheight Cell height in internal points.
     * @param string $align   Text vertical alignment inside the cell:
     *                        - T=top;
     *                        - C=center;
     *                        - B=bottom;
     *                        - A=center-on-font-ascent;
     *                        - L=center-on-font-baseline;
     *                        - D=center-on-font-descent.
     * @param array  $cell    Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function cellTextVAlign($pheight, $align = 'C', array $cell = array())
    {
        if (empty($cell)) {
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
     * @param float  $pwidth    Cell width in internal points.
     * @param float  $txtpwidth Text width in internal points.
     * @param string $align     Text vertical alignment inside the cell: L=left; C=center; R=right.
     * @param array  $cell      Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function cellTextHAlign($pwidth, $txtpwidth, $align = 'L', array $cell = array())
    {
        if (empty($cell)) {
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
     * @param float  $txty    Text baseline top Y coordinate in internal points.
     * @param float  $pheight Cell height in internal points.
     * @param string $align   Text vertical alignment inside the cell:
     *                        - T=top;
     *                        - C=center;
     *                        - B=bottom;
     *                        - A=center-on-font-ascent;
     *                        - L=center-on-font-baseline;
     *                        - D=center-on-font-descent.
     * @param array  $cell    Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function cellVPosFromText($txty, $pheight, $align = 'C', array $cell = array())
    {
        return ($txty + $this->cellTextVAlign($pheight, $align, $cell));
    }

    /**
     * Returns the left X coordinate of the cell wrapping the text.
     *
     * @param float  $txtx      Text left X coordinate in internal points.
     * @param float  $pwidth    Cell width in internal points.
     * @param float  $txtpwidth Text width in internal points.
     * @param string $align     Text vertical alignment inside the cell: L=left; C=center; R=right.
     * @param array  $cell      Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function cellHPosFromText($txtx, $pwidth, $txtpwidth, $align = 'L', array $cell = array())
    {
        return ($txtx - $this->cellTextHAlign($pwidth, $txtpwidth, $align, $cell));
    }

    /**
     * Returns the baseline Y coordinate of the text inside the cell.
     *
     * @param float  $pnty    Cell top Y coordinate in internal points.
     * @param float  $pheight Cell height in internal points.
     * @param string $align   Text vertical alignment inside the cell:
     *                        - T=top;
     *                        - C=center;
     *                        - B=bottom;
     *                        - A=center-on-font-ascent;
     *                        - L=center-on-font-baseline;
     *                        - D=center-on-font-descent.
     * @param array  $cell    Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function textVPosFromCell($pnty, $pheight, $align = 'C', array $cell = array())
    {
        return ($pnty - $this->cellTextVAlign($pheight, $align, $cell));
    }

    /**
     * Returns the left X coordinate of the text inside the cell.
     *
     * @param float  $txtx      Text left X coordinate in internal points.
     * @param float  $pwidth    Cell width in internal points.
     * @param float  $txtpwidth Text width in internal points.
     * @param string $align     Text vertical alignment inside the cell: L=left; C=center; R=right.
     * @param array  $cell      Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function textHPosFromCell($txtx, $pwidth, $txtpwidth, $align = 'L', array $cell = array())
    {
        return ($txtx + $this->cellTextHAlign($pwidth, $txtpwidth, $align, $cell));
    }
}
