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
     * Convert user units to internal points unit.
     *
     * @param float $usr Value to convert.
     *
     * @return float
     */
    public function toPoints($usr)
    {
        return ((float) $usr * $this->kunit);
    }

    /**
     * Convert internal points to user unit.
     *
     * @param float $pnt Value to convert in user units.
     *
     * @return float
     */
    public function toUnit($pnt)
    {
        return ((float) $pnt / $this->kunit);
    }

    /**
     * Convert vertical user value to internal points unit.
     * Note: the internal Y points coordinate starts at the bottom left of the page.
     *
     * @param float  $usr    Value to convert.
     * @param float  $pageh  Optional page height in internal points ($pageh:$this->page->getPage()['pheight']).
     *
     * @return float
     */
    public function toYPoints($usr, $pageh = -1)
    {
        $pageh = $pageh >= 0 ? $pageh : $this->page->getPage()['pheight'];
        return ($pageh - $this->toPoints($usr));
    }

    /**
     * Convert vertical internal points value to user unit.
     * Note: the internal Y points coordinate starts at the bottom left of the page.
     *
     * @param float  $pnt    Value to convert.
     * @param float  $pageh  Optional page height in internal points ($pageh:$this->page->getPage()['pheight']).
     *
     * @return float
     */
    public function toYUnit($pnt, $pageh = -1)
    {
        $pageh = $pageh >= 0 ? $pageh : $this->page->getPage()['pheight'];
        return ($pageh - $this->toUnit($pnt));
    }

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
     * Returns the cell top-left Y coordinate.
     *
     * @param float  $pnty     Y coordinate in internal points.
     * @param float  $pheight  Cell height in internal points.
     * @param string $align    Cell vertical alignment: T=top; C=center; B=bottom.
     * @param array  $cell     Optional cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function cellVertAlign($pnty, $pheight, $align = 'T', array $cell = array())
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
     * Returns the top-left Y coordinate for the text inside the cell.
     *
     * @param float  $pnty    Y coordinate in internal points.
     * @param float  $pheight Cell height in internal points.
     * @param string $align   Text vertical alignment inside the cell: T=top; C=center; B=bottom.
     * @param array  $cell    Optional cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function cellTextVertAlign($pnty, $pheight, $align = 'T', array $cell = array())
    {
        if (empty($cell)) {
            $cell = $this->defcell;
        }
        $curfont = $this->font->getCurrentFont();
        switch ($align) {
            case 'T': // Top
                return ($pnty - $cell['padding']['T']);
            case 'C': // Center
                return ($pnty - (($pheight - $curfont['ascent'] - $curfont['descent']) / 2));
            case 'B': // Bottom
                return ($pnty - $pheight + $cell['padding']['B'] + $curfont['ascent'] + $curfont['descent']);
        }
        return $pnty;
    }

    /**
     * Returns the top-left Y coordinate for the cell containing the text.
     *
     * @param float  $pnty    Y coordinate in internal points of the text.
     * @param float  $pheight Cell height in internal points.
     * @param string $align   Text vertical alignment inside the cell: T=top; C=center; B=bottom.
     * @param array  $cell    Optional cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function textCellVertAlign($pnty, $pheight, $align = 'T', array $cell = array())
    {
        if (empty($cell)) {
            $cell = $this->defcell;
        }
        $curfont = $this->font->getCurrentFont();
        switch ($align) {
            case 'T': // Top
                return ($pnty + $cell['padding']['T']);
            case 'C': // Center
                return ($pnty + (($pheight - $curfont['ascent'] - $curfont['descent']) / 2));
            case 'B': // Bottom
                return ($pnty + $pheight - $cell['padding']['B'] - $curfont['ascent'] - $curfont['descent']);
        }
        return $pnty;
    }

    /**
     * Returns the cell minimum height in points for the current font.
     *
     * @param array  $cell  Optional cell parameters for padding, margin etc.
     *
     * @return float
     */
    protected function textCellMinHeight(array $cell = array())
    {
        if (empty($cell)) {
            $cell = $this->defcell;
        }
        $curfont = $this->font->getCurrentFont();
        return($curfont['ascent'] + $curfont['descent'] + $cell['padding']['T'] + $cell['padding']['B']);
    }

    /**
     * Increase the cell padding to account for the border tickness.
     *
     * @param array  $styles Array of styles - one style entry for each side (T,R,B,L) and/or one global "all" entry.
     * @param array  $cell   Optional cell parameters for padding, margin etc.
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
}
