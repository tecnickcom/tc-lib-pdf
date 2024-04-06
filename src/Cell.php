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
     * @const TCellDef
     */
    public const ZEROCELL = [
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
     * Default values for cell.
     *
     * @var TCellDef
     */
    protected $defcell = self::ZEROCELL;

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

        $minT = 0;
        $minR = 0;
        $minB = 0;
        $minL = 0;
        if (! empty($styles['all']['lineWidth'])) {
            $minT = $this->toPoints((float) $styles['all']['lineWidth']);
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

        switch ($align) {
            default:
            case 'C': // Center
                return ($pheight + (2 * max($cell['padding']['T'], $cell['padding']['B'])));
            case 'T': // Top
            case 'B': // Bottom
                return ($pheight + $cell['padding']['T'] + $cell['padding']['B']);
            case 'L': // Center on font Baseline
                return ($pheight - $curfont['height'] + (2 * max(
                    ($cell['padding']['T'] + $curfont['ascent']),
                    ($cell['padding']['B'] - $curfont['descent'])
                )));
            case 'A': // Center on font Ascent
            case 'D': // Center on font Descent
                return ($pheight
                    - $curfont['height']
                    + (2 * ($curfont['height'] + max($cell['padding']['T'], $cell['padding']['B']))));
        }
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

        switch ($align) {
            case '':
            case 'J': // Justify
                $align = $this->rtl ? 'R' : 'L';
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

        switch ($align) {
            case '':
            case 'J': // Justify
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

        switch ($align) {
            default:
            case 'C': // Center
                return (($cellpheight - $txtpheight) / 2);
            case 'T': // Top
                return ($cell['padding']['T']);
            case 'B': // Bottom
                return (($cellpheight - $txtpheight) - $cell['padding']['B']);
            case 'L': // Center on font Baseline
                return ((($cellpheight - $txtpheight + $curfont['height']) / 2) - $curfont['ascent']);
            case 'A': // Center on font Ascent
                return (($cellpheight - $txtpheight + $curfont['height']) / 2);
            case 'D': // Center on font Descent
                return ((($cellpheight - $txtpheight + $curfont['height']) / 2) - $curfont['height']);
        }
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

        switch ($align) {
            case '':
            case 'J': // Justify
                $align = $this->rtl ? 'R' : 'L';
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

        switch ($align) {
            default:
            case 'C': // Center
                return ($cph - (2 * max($cell['padding']['T'], $cell['padding']['B'])));
            case 'T': // Top
            case 'B': // Bottom
                return ($cph - $cell['padding']['T'] - $cell['padding']['B']);
            case 'L': // Center on font Baseline
                return ($cph + $curfont['height'] - (2 * max(
                    ($cell['padding']['T'] + $curfont['ascent']),
                    ($cell['padding']['B'] - $curfont['descent'])
                )));
            case 'A': // Center on font Ascent
            case 'D': // Center on font Descent
                return ($cph
                    + $curfont['height']
                    - (2 * ($curfont['height'] + max($cell['padding']['T'], $cell['padding']['B']))));
        }
    }

    /**
     * Sets the page context by adding the previous page font and graphic settings.
     *
     * @return void
     */
    protected function setPageContext(): void
    {
        $this->page->addContent($this->font->getOutCurrentFont());
    }

    /**
     * Returns the PDF code to draw the text cell border and background.
     *
     * @param float     $pntx     Cell left X coordinate in internal points.
     * @param float     $pnty     Cell top Y coordinate in internal points.
     * @param float     $pwidth   Cell width in internal points.
     * @param float     $pheight  Cell height in internal points.
     * @param array<int|string, StyleDataOpt> $styles Optional to overwrite the styles (see: getCurrentStyleArray).
     *
     * @return string
     */
    protected function drawCell(
        float $pntx,
        float $pnty,
        float $pwidth,
        float $pheight,
        array $styles = []
    ) {
        $mode = empty($styles['all']['fillColor']) ? 's' : 'b';

        $out = $this->graph->getStartTransform();

        if (count($styles) <= 1) {
            $out .= $this->graph->getBasicRect(
                $this->toUnit($pntx),
                $this->toYUnit($pnty),
                $this->toUnit($pwidth),
                $this->toUnit($pheight),
                $mode,
                (empty($styles['all']) ? [] : $styles['all']),
            );
        } else {
            $out .= $this->graph->getRect(
                $this->toUnit($pntx),
                $this->toYUnit($pnty),
                $this->toUnit($pwidth),
                $this->toUnit($pheight),
                $mode,
                $styles,
            );
        }

        $out .= $this->graph->getStopTransform();

        return $out;
    }
}
