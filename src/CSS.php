<?php

/**
 * CSS.php
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

use Com\Tecnick\Pdf\Exception as PdfException;
use Com\Tecnick\Color\Model as ColorModel;

/**
 * Com\Tecnick\Pdf\CSS
 *
 * CSS PDF class
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TCellBound from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type StyleData from \Com\Tecnick\Pdf\Graph\Base as BorderStyle
 *
 * @phpstan-type TCSSBorderSpacing array{
 *     'H': float,
 *     'V': float,
 * }
 */
abstract class CSS extends \Com\Tecnick\Pdf\SVG
{
    //@TODO: add missing methods

    /**
     * Default values for cell boundaries.
     *
     * @const TCSSBorderSpacing
     */
    protected const ZEROBORDERSPACE = [
        'H' => 0,
        'V' => 0,
    ];

    /**
     * Default CSS margin.
     *
     * @var TCellBound
     */
    protected $defCSSCellMargin = self::ZEROCELLBOUND;

    /**
     * Default CSS padding.
     *
     * @var TCellBound
     */
    protected $defCSSCellPadding = self::ZEROCELLBOUND;

    /**
     * Default CSS border space.
     *
     * @var TCSSBorderSpacing
     */
    protected $defCSSBorderSpacing = self::ZEROBORDERSPACE;

    /**
     * Maximum value that can be represented in Roman notation.
     *
     * @var int
     */
    protected const ROMAN_LIMIT = 3_999_999_999;

    /**
     * Maps Roman Vinculum symbols to number multipliers.
     *
     * @var array<string, int>
     */
    protected const ROMAN_VINCULUM = [
        '\u{033F}' => 1_000_000,
        '\u{0305}' => 1_000,
        '' => 1,
    ];

    /**
     * Maps Roman symbols to numbers.
     *
     * @var array<string, int>
     */
    protected const ROMAN_SYMBOL = [
        // standard notation
        'M' => 1_000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
    ];

    /**
     * Set the default CSS margin in user units.
     *
     * @param float $top    Top.
     * @param float $right  Right.
     * @param float $bottom Bottom.
     * @param float $left   Left.
     */
    public function setDefaultCSSMargin(
        float $top,
        float $right,
        float $bottom,
        float $left
    ): void {
        $this->defCSSCellMargin['T'] = $this->toPoints($top);
        $this->defCSSCellMargin['R'] = $this->toPoints($right);
        $this->defCSSCellMargin['B'] = $this->toPoints($bottom);
        $this->defCSSCellMargin['L'] = $this->toPoints($left);
    }

    /**
     * Set the default CSS padding in user units.
     *
     * @param float $top    Top.
     * @param float $right  Right.
     * @param float $bottom Bottom.
     * @param float $left   Left.
     */
    public function setDefaultCSSPadding(
        float $top,
        float $right,
        float $bottom,
        float $left
    ): void {
        $this->defCSSCellPadding['T'] = $this->toPoints($top);
        $this->defCSSCellPadding['R'] = $this->toPoints($right);
        $this->defCSSCellPadding['B'] = $this->toPoints($bottom);
        $this->defCSSCellPadding['L'] = $this->toPoints($left);
    }

    /**
     * Set the default CSS border spacing in user units.
     *
     * @param float $horiz Horizontal space.
     * @param float $vert Vertical space.
     */
    public function setDefaultCSSBorderSpacing(
        float $vert,
        float $horiz,
    ): void {
        $this->defCSSBorderSpacing['V'] = $this->toPoints($vert);
        $this->defCSSBorderSpacing['H'] = $this->toPoints($horiz);
    }

    /**
     * Returns the border width from CSS property.
     *
     * @param string $width border width.
     *
     * @return float width in internal points.
     */
    protected function getCSSBorderWidthPoints(string $width): float
    {
        return match ($width) {
            '' => 0.0,
            'thin' => 2.0,
            'medium' => 4.0,
            'thick' => 6.0,
            default => $this->getUnitValuePoints($width),
        };
    }

    /**
     * Returns the border width from CSS property.
     *
     * @param string $width border width.
     *
     * @return float width in user units.
     */
    protected function getCSSBorderWidth(string $width): float
    {
        return $this->toUnit($this->getCSSBorderWidthPoints($width));
    }

    /**
     * Returns the border dash style from CSS property.
     *
     * @param string $style Border style to convert.
     *
     * @return int Border dash style (return -1 in case of none or hidden border).
     */
    protected function getCSSBorderDashStyle(string $style): int
    {
        return match (\strtolower($style)) {
            'none' => -1,
            'hidden' => -1,
            'dotted' => 1,
            'dashed' => 3,
            'double' => 0,
            'groove' => 0,
            'ridge' => 0,
            'inset' => 0,
            'outset' => 0,
            'solid' => 0,
            default => 0,
        };
    }

    /**
     * Returns the default CSS borer style.
     *
     * @return BorderStyle
     */
    protected function getCSSDefaultBorderStyle(): array
    {
        return [
            'lineWidth' => 0,
            'lineCap' => 'square',
            'lineJoin' => 'miter',
            'miterLimit' => $this->toUnit(10.0),
            'dashArray' => [],
            'dashPhase' => 0,
            'lineColor' => 'black',
            'fillColor' => '',
        ];
    }

    /**
     * Returns the border style array from CSS border properties.
     *
     * @param string $cssborder border properties.
     *
     * @return BorderStyle border properties.
     */
    protected function getCSSBorderStyle(string $cssborder): array
    {
        $border = $this->getCSSDefaultBorderStyle();
        $bprop = \preg_split('/[\s]+/', \trim($cssborder));
        if ($bprop === false) {
            return $border;
        }
        $count = \count($bprop);
        if (($count > 0) && ($bprop[$count - 1] === '!important')) {
            unset($bprop[$count - 1]);
            --$count;
        }
        switch ($count) {
            case 2:
                $width = 'medium';
                $style = $bprop[0];
                $color = $bprop[1];
                break;
            case 1:
                $width = 'medium';
                $style = $bprop[0];
                $color = 'black';
                break;
            case 0:
                $width = 'medium';
                $style = 'solid';
                $color = 'black';
                break;
            default:
                $width = $bprop[0];
                $style = $bprop[1];
                $color = $bprop[2];
                break;
        }
        if ($style == 'none') {
            return $border;
        }
        $dash = $this->getCSSBorderDashStyle($style);
        if ($dash < 0) {
            return $border;
        }
        $border['dashPhase'] = $dash;
        $border['lineWidth'] = $this->getCSSBorderWidth($width);
        $colobj = $this->color->getColorObj($color);
        $border['lineColor'] = empty($colobj) ? 'black' : $colobj->getCssColor();
        return $border;
    }

    /**
     * Get the internal Cell padding from CSS attribute.
     *
     * @param string $csspadding padding properties.
     * @param float $width width of the containing element.
     *
     * @return TCellBound cell paddings.
     */
    protected function getCSSPadding(string $csspadding, float $width = 0.0): array
    {
        /** @var TCellBound $cellpad */
        $cellpad = $this->defCSSCellPadding;
        $pad = \preg_split('/[\s]+/', \trim($csspadding));
        if ($pad === false) {
            return $cellpad;
        }
        switch (\count($pad)) {
            case 4:
                $cellpad['T'] = $pad[0];
                $cellpad['R'] = $pad[1];
                $cellpad['B'] = $pad[2];
                $cellpad['L'] = $pad[3];
                break;
            case 3:
                $cellpad['T'] = $pad[0];
                $cellpad['R'] = $pad[1];
                $cellpad['B'] = $pad[2];
                $cellpad['L'] = $pad[1];
                break;
            case 2:
                $cellpad['T'] = $pad[0];
                $cellpad['R'] = $pad[1];
                $cellpad['B'] = $pad[0];
                $cellpad['L'] = $pad[1];
                break;
            case 1:
                $cellpad['T'] = $pad[0];
                $cellpad['R'] = $pad[0];
                $cellpad['B'] = $pad[0];
                $cellpad['L'] = $pad[0];
                break;
            default:
                return $cellpad;
        }
        if ($width <= 0) {
            $region = $this->page->getRegion();
            $width = $region['RW'];
        }
        $ref = self::REFUNITVAL;
        $ref['parent'] = $width;
        $cellpad['T'] = $this->toUnit($this->getUnitValuePoints($cellpad['T'], $ref));
        $cellpad['R'] = $this->toUnit($this->getUnitValuePoints($cellpad['R'], $ref));
        $cellpad['B'] = $this->toUnit($this->getUnitValuePoints($cellpad['B'], $ref));
        $cellpad['L'] = $this->toUnit($this->getUnitValuePoints($cellpad['L'], $ref));
        return $cellpad;
    }

    /**
     * Get the internal Cell margin from CSS attribute.
     *
     * @param string $cssmargin margin properties.
     * @param float $width width of the containing element.
     *
     * @return TCellBound cell margins.
     */
    protected function getCSSMargin(string $cssmargin, float $width = 0.0): array
    {
        /** @var TCellBound $cellmrg */
        $cellmrg = $this->defCSSCellMargin;
        $mrg = \preg_split('/[\s]+/', \trim($cssmargin));
        if ($mrg === false) {
            return $cellmrg;
        }
        switch (\count($mrg)) {
            case 4:
                $cellmrg['T'] = $mrg[0];
                $cellmrg['R'] = $mrg[1];
                $cellmrg['B'] = $mrg[2];
                $cellmrg['L'] = $mrg[3];
                break;
            case 3:
                $cellmrg['T'] = $mrg[0];
                $cellmrg['R'] = $mrg[1];
                $cellmrg['B'] = $mrg[2];
                $cellmrg['L'] = $mrg[1];
                break;
            case 2:
                $cellmrg['T'] = $mrg[0];
                $cellmrg['R'] = $mrg[1];
                $cellmrg['B'] = $mrg[0];
                $cellmrg['L'] = $mrg[1];
                break;
            case 1:
                $cellmrg['T'] = $mrg[0];
                $cellmrg['R'] = $mrg[0];
                $cellmrg['B'] = $mrg[0];
                $cellmrg['L'] = $mrg[0];
                break;
            default:
                return $cellmrg;
        }
        if ($width <= 0) {
            $region = $this->page->getRegion();
            $width = $region['RW'];
        }
        $cellmrg['T'] = \str_replace('auto', '0', $cellmrg['T']);
        $cellmrg['R'] = \str_replace('auto', '0', $cellmrg['R']);
        $cellmrg['B'] = \str_replace('auto', '0', $cellmrg['B']);
        $cellmrg['L'] = \str_replace('auto', '0', $cellmrg['L']);
        $ref = self::REFUNITVAL;
        $ref['parent'] = $width;
        $cellmrg['T'] = $this->toUnit($this->getUnitValuePoints($cellmrg['T'], $ref));
        $cellmrg['R'] = $this->toUnit($this->getUnitValuePoints($cellmrg['R'], $ref));
        $cellmrg['B'] = $this->toUnit($this->getUnitValuePoints($cellmrg['B'], $ref));
        $cellmrg['L'] = $this->toUnit($this->getUnitValuePoints($cellmrg['L'], $ref));
        return $cellmrg;
    }

    /**
     * Get the border-spacing from CSS attribute.
     *
     * @param string $cssbspace border-spacing CSS properties.
     * @param float $width width of the containing element.
     *
     * @return TCSSBorderSpacing of border spacings.
     */
    protected function getCSSBorderMargin(string $cssbspace, float $width = 0.0): array
    {
        /** @var TCSSBorderSpacing $bsp */
        $bsp = $this->defCSSBorderSpacing;
        $space = \preg_split('/[\s]+/', \trim($cssbspace));
        if ($space === false) {
            return $bsp;
        }
        switch (\count($space)) {
            case 2:
                $bsp['H'] = $space[0];
                $bsp['V'] = $space[1];
                break;
            case 1:
                $bsp['H'] = $space[0];
                $bsp['V'] = $space[0];
                break;
            default:
                return $bsp;
        }
        if ($width <= 0) {
            $region = $this->page->getRegion();
            $width = $region['RW'];
        }
        $ref = self::REFUNITVAL;
        $ref['parent'] = $width;
        $bsp['H'] = $this->toUnit($this->getUnitValuePoints($bsp['H'], $ref));
        $bsp['V'] = $this->toUnit($this->getUnitValuePoints($bsp['V'], $ref));
        return $bsp;
    }

    /**
     * Implode CSS data array into a single string.
     *
     * @param array<string, string> $css array of CSS properties.
     *
     * @return string merged CSS properties.
     */
    protected function implodeCSSData(array $css): string
    {
        $out = '';
        foreach ($css as $style) {
            if (!\is_array($style) || empty($style['c']) || (!\is_string($style['c']))) {
                continue;
            }
            $csscmds = \explode(';', $style['c']);
            foreach ($csscmds as $cmd) {
                if (empty($cmd)) {
                    continue;
                }
                $pos = \strpos($cmd, ':');
                if ($pos === false) {
                    continue;
                }
                $cmd = \substr($cmd, 0, ($pos + 1));
                if (\strpos($out, $cmd) !== false) {
                    // remove duplicate commands (last commands have high priority)
                    $out = \preg_replace('/' . $cmd . '[^;]+/i', '', $out) ?? '';
                }
            }
            $out .= ';' . $style['c'];
        }
        // remove multiple semicolons
        $out = \preg_replace('/[;]+/', ';', $out) ?? '';
        return $out;
    }

    /**
     * Tidy up the CSS string by removing unsupported properties.
     *
     * @param string $css string containing CSS definitions.
     *
     * @return string
     */
    protected function tidyCSS($css): string
    {
        if (empty($css)) {
            return '';
        }
        // remove comments
        $css = \preg_replace('/\/\*[^\*]*\*\//', '', $css) ?? '';
        // remove newlines and multiple spaces
        $css = \preg_replace('/[\s]+/', ' ', $css) ?? '';
        // remove some spaces
        $css = \preg_replace('/[\s]*([;:\{\}]{1})[\s]*/', '\\1', $css) ?? '';
        // remove empty blocks
        $css = \preg_replace('/([^\}\{]+)\{\}/', '', $css) ?? '';
        // replace media type parenthesis
        $css = \preg_replace('/@media[\s]+([^\{]*)\{/i', '@media \\1§', $css) ?? '';
        $css = \preg_replace('/\}\}/si', '}§', $css) ?? '';
        // find media blocks (all, braille, embossed, handheld, print, projection, screen, speech, tty, tv)
        $blk = [];
        $matches = [];
        if (\preg_match_all('/@media[\s]+([^\§]*)§([^§]*)§/i', $css, $matches) > 0) {
            foreach ($matches[1] as $key => $type) {
                $blk[$type] = $matches[2][$key];
            }
            // remove media blocks
            $css = \preg_replace('/@media[\s]+([^\§]*)§([^§]*)§/i', '', $css) ?? '';
        }
        // keep 'all' and 'print' media, other media types are discarded
        if (!empty($blk['all'])) {
            $css .= $blk['all'];
        }
        if (!empty($blk['print'])) {
            $css .= $blk['print'];
        }
        return \trim($css);
    }

    /**
     * Extracts the CSS properties from a CSS string.
     *
     * @param string $css string containing CSS definitions.
     *
     * @return array<string, string> CSS properties.
     */
    protected function extractCSSproperties($css): array
    {
        $css = $this->tidyCSS($css);
        if (empty($css)) {
            return [];
        }
        $blk = [];
        $matches = [];
        // explode css data string into array
        if (\substr($css, -1) == '}') {
            // remove last parethesis
            $css = \substr($css, 0, -1);
        }
        $matches = \explode('}', $css);
        foreach ($matches as $key => $block) {
            // index 0 contains the CSS selector, index 1 contains CSS properties
            $blk[$key] = \explode('{', $block);
            if (!isset($blk[$key][1])) {
                // remove empty definitions
                unset($blk[$key]);
            }
        }
        // split groups of selectors (comma-separated list of selectors)
        foreach ($blk as $key => $block) {
            if (\strpos($block[0], ',') > 0) {
                $selectors = \explode(',', $block[0]);
                foreach ($selectors as $sel) {
                    $blk[] = [0 => \trim($sel), 1 => $block[1]];
                }
                unset($blk[$key]);
            }
        }
        // covert array to selector => properties
        $out = [];
        foreach ($blk as $block) {
            $selector = $block[0];
            // calculate selector's specificity
            $matches = [];
            $sta = 0; // the declaration is not from is a 'style' attribute
            // number of ID attributes
            $stb = \intval(\preg_match_all('/[\#]/', $selector, $matches));
            // number of other attributes
            $stc = \intval(\preg_match_all('/[\[\.]/', $selector, $matches));
            // number of pseudo-classes
            $stc += \intval(\preg_match_all(
                '/[\:]link|visited|hover|active|focus|target|lang|enabled|disabled'
                . '|checked|indeterminate|root|nth|first|last|only|empty|contains|not/i',
                $selector,
                $matches,
            ));
            // number of element names
            $std = \intval(\preg_match_all('/[\>\+\~\s]{1}[a-zA-Z0-9]+/', " $selector", $matches));
            // number of pseudo-elements
            $std += \intval(\preg_match_all('/[\:][\:]/', $selector, $matches));
            $specificity = $sta . $stb . $stc . $std;
            // add specificity to the beginning of the selector
            $out["$specificity $selector"] = $block[1];
        }
        // sort selectors alphabetically to account for specificity
        \ksort($out, SORT_STRING);
        return $out;
    }

    /**
     * Returns the Roman representation of an integer number.
     * Roman standard notation can represent numbers up to 3,999.
     * For bigger numbers, up to two layers of the "vinculum" notation
     * are used for a max value of 3,999,999,999.
     *
     * @param int $num number to convert.
     *
     * @return string roman representation of the specified number.
     */
    protected function intToRoman(int $num): string
    {
        if ($num > self::ROMAN_LIMIT) {
            return \strval($num);
        }
        $rmn = '';
        foreach (self::ROMAN_VINCULUM as $sfx => $mul) {
            foreach (self::ROMAN_SYMBOL as $sym => $val) {
                $limit = (int)($mul * $val);
                while ($num >= $limit) {
                    $rmn .= $sym[0] . $sfx . (!empty($sym[1]) ? $sym[1] . $sfx : '');
                    $num -= $limit;
                }
            }
        }
        while ($num >= 1) {
            $rmn .= 'I';
            $num--;
        }
        return $rmn;
    }
}
