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
     * Returns the minimum cell height in points for the current font.
     *
     * @param array  $cell  Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function textCellMinHeight(array $cell = array())
    {
        if (empty($cell)) {
            $cell = $this->defcell;
        }
        $curfont = $this->font->getCurrentFont();
        return($curfont['height'] + $cell['padding']['T'] + $cell['padding']['B']);
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
     * Returns the cell top-left Y coordinate to account for margins.
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
        // cell vertical alignment
        switch ($align) {
            case 'T': // Top
                return ($pnty - $cell['margin']['T']);
            case 'C': // Center
                return ($pnty + (($cell['margin']['T'] + $pheight + $cell['margin']['B']) / 2));
            case 'B': // Bottom
                return ($pnty + $cell['margin']['B'] + $pheight);
        }
        return $pnty;
    }

    /**
     * Returns the vertical distance between the cell top-left corner and the text baseline.
     *
     * @param float  $pheight Cell height in internal points.
     * @param string $align   Text vertical alignment inside the cell:
     *                        T=top; C=center; B=bottom; A=ascent; L=baseline; D=descent.
     * @param array  $cell    Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function cellTextAlignment($pheight, $align = 'C', array $cell = array())
    {
        if (empty($cell)) {
            $cell = $this->defcell;
        }
        $curfont = $this->font->getCurrentFont();
        switch ($align) {
            default:
            case 'C': // Center
                return ($curfont['midpoint'] + ($pheight / 2));
            case 'T': // Top
                return ($curfont['ascent'] + $cell['padding']['T']);
            case 'B': // Bottom
                return ($curfont['descent'] - $cell['padding']['B'] + $pheight);
            case 'L': // Center on font Baseline
                return ($pheight / 2);
            case 'A': // Center on font Ascent
                return ($curfont['ascent'] + ($pheight / 2));
            case 'D': // Center on font Descent
                return ($curfont['descent'] + ($pheight / 2));
        }
    }

    /**
     * Returns the top-left Y coordinate of the cell wrapping the text.
     *
     * @param float  $txty    Text baseline top Y coordinate in internal points.
     * @param float  $pheight Cell height in internal points.
     * @param string $align   Text vertical alignment inside the cell:
     *                        T=top; C=center; B=bottom; A=ascent; L=baseline; D=descent.
     * @param array  $cell    Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function cellVPosFromText($txty, $pheight, $align = 'C', array $cell = array())
    {
        return ($txty + $this->cellTextAlignment($pheight, $align, $cell));
    }

    /**
     * Returns the baseline Y coordinate of the text inside the cell.
     *
     * @param float  $pnty    Cell top Y coordinate in internal points.
     * @param float  $pheight Cell height in internal points.
     * @param string $align   Text vertical alignment inside the cell:
     *                        T=top; C=center; B=bottom; A=ascent; L=baseline; D=descent.
     * @param array  $cell    Optional to overwrite cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function textVPosFromCell($pnty, $pheight, $align = 'C', array $cell = array())
    {
        return ($pnty - $this->cellTextAlignment($pheight, $align, $cell));
    }
}
