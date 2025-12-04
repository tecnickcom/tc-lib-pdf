<?php

/**
 * SVG.php
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
use TSVGAttribs;
use TSVGStyle;

/**
 * Com\Tecnick\Pdf\SVG
 *
 * SVG PDF class
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TTMatrix from \Com\Tecnick\Pdf\Graph\Base
 * @phpstan-import-type TRefUnitValues from \Com\Tecnick\Pdf\Base
 *
 * @phpstan-type TSVGSize array{
 *    'x': float,
 *    'y': float,
 *    'width': float,
 *    'height': float,
 *    'viewBox': array{float, float, float, float},
 *    'ar_align': string,
 *    'ar_ms': string,
 * }
 *
 * @phpstan-type TSCGCoord array{
 *    'x': float,
 *    'y': float,
 *    'x0': float,
 *    'y0': float,
 *    'xmin': float,
 *    'xmax': float,
 *    'ymin': float,
 *    'ymax': float,
 *    'xinit': float,
 *    'yinit': float,
 *    'xoffset': float,
 *    'yoffset': float,
 *    'relcoord': bool,
 *    'firstcmd': bool,
 * }
 *
 * @phpstan-type TSVGGradient array{
 *    'xref': string,
 *    'type': int,
 *    'gradientUnits': string,
 *    'mode': string,
 *    'coords': array<float>,
 *    'stops': array<int, array{
 *            'color': string,
 *            'exponent'?: float,
 *            'opacity'?: float,
 *            'offset'?: float,
 *        }>,
 *    'gradientTransform': array<float>,
 * }
 *
 * @phpstan-type TSVGStyle array{
 *    'alignment-baseline': string,
 *    'baseline-shift': string,
 *    'clip': string,
 *    'clip-path': string,
 *    'clip-rule': string,
 *    'color': string,
 *    'color-interpolation': string,
 *    'color-interpolation-filters': string,
 *    'color-profile': string,
 *    'color-rendering': string,
 *    'cursor': string,
 *    'direction': string,
 *    'display': string,
 *    'dominant-baseline': string,
 *    'enable-background': string,
 *    'fill': string,
 *    'fill-opacity': float,
 *    'fill-rule': string,
 *    'filter': string,
 *    'flood-color': string,
 *    'flood-opacity': float,
 *    'font': string,
 *    'font-family': string,
 *    'font-size': string,
 *    'font-size-val': float,
 *    'font-size-adjust': string,
 *    'font-stretch': string,
 *    'font-stretch-val': float,
 *    'font-style': string,
 *    'font-variant': string,
 *    'font-weight': string,
 *    'font-mode': string,
 *    'glyph-orientation-horizontal': string,
 *    'glyph-orientation-vertical': string,
 *    'image-rendering': string,
 *    'kerning': string,
 *    'letter-spacing': string,
 *    'letter-spacing-val': float,
 *    'lighting-color': string,
 *    'marker': string,
 *    'marker-end': string,
 *    'marker-mid': string,
 *    'marker-start': string,
 *    'mask': string,
 *    'objstyle': string,
 *    'opacity': float,
 *    'overflow': string,
 *    'pointer-events': string,
 *    'shape-rendering': string,
 *    'stop-color': string,
 *    'stop-opacity': float,
 *    'stroke': string,
 *    'stroke-dasharray': string,
 *    'stroke-dashoffset': float,
 *    'stroke-linecap': string,
 *    'stroke-linejoin': string,
 *    'stroke-miterlimit': float,
 *    'stroke-opacity': float,
 *    'stroke-width': float,
 *    'text-anchor': string,
 *    'text-decoration': string,
 *    'text-rendering': string,
 *    'unicode-bidi': string,
 *    'visibility': string,
 *    'word-spacing': string,
 *    'writing-mode': string,
 *    'text-color': string,
 *    'transfmatrix': TTMatrix,
 *  }
 *
 * @phpstan-type TSVGTextMode array{
 *    'rtl': bool,
 *    'invisible': bool,
 *    'stroke': int,
 *    'text-anchor': string,
 * }
 *
 * @phpstan-type TSVGAttributes array{
 *    'accumulate'?: string,
 *    'additive'?: string,
 *    'alignment-baseline'?: string,
 *    'amplitude'?: string,
 *    'attributeName'?: string,
 *    'attributeType'?: string,
 *    'azimuth'?: string,
 *    'baseFrequency'?: string,
 *    'baseProfile'?: string,
 *    'baseline-shift'?: string,
 *    'begin'?: string,
 *    'bias'?: string,
 *    'by'?: string,
 *    'calcMode'?: string,
 *    'class'?: string,
 *    'clip'?: string,
 *    'clip-path'?: string,
 *    'clip-rule'?: string,
 *    'clipPathUnits'?: string,
 *    'closing_tag'?: bool,
 *    'color'?: string,
 *    'color-interpolation'?: string,
 *    'color-interpolation-filters'?: string,
 *    'content'?: string,
 *    'crossorigin'?: string,
 *    'cursor'?: string,
 *    'cx'?: string,
 *    'cy'?: string,
 *    'd'?: string,
 *    'data-*'?: string,
 *    'decoding'?: string,
 *    'diffuseConstant'?: string,
 *    'direction'?: string,
 *    'display'?: string,
 *    'divisor'?: string,
 *    'dominant-baseline'?: string,
 *    'dur'?: string,
 *    'dx'?: string,
 *    'dy'?: string,
 *    'edgeMode'?: string,
 *    'elevation'?: string,
 *    'end'?: string,
 *    'exponent'?: string,
 *    'fill'?: string,
 *    'fill-opacity'?: string,
 *    'fill-rule'?: string,
 *    'filter'?: string,
 *    'filterUnits'?: string,
 *    'flood-color'?: string,
 *    'flood-opacity'?: string,
 *    'font-family'?: string,
 *    'font-size'?: string,
 *    'font-size-adjust'?: string,
 *    'font-stretch'?: string,
 *    'font-style'?: string,
 *    'font-variant'?: string,
 *    'font-weight'?: string,
 *    'fr'?: string,
 *    'from'?: string,
 *    'fx'?: string,
 *    'fy'?: string,
 *    'glyph-orientation-horizontal'?: string,
 *    'glyph-orientation-vertical'?: string,
 *    'gradientTransform'?: string,
 *    'gradientUnits'?: string,
 *    'height'?: string,
 *    'href'?: string,
 *    'hreflang'?: string,
 *    'id'?: string,
 *    'image-rendering'?: string,
 *    'in'?: string,
 *    'in2'?: string,
 *    'intercept'?: string,
 *    'k1'?: string,
 *    'k2'?: string,
 *    'k3'?: string,
 *    'k4'?: string,
 *    'kernelMatrix'?: string,
 *    'kernelUnitLength'?: string,
 *    'keyPoints'?: string,
 *    'keySplines'?: string,
 *    'keyTimes'?: string,
 *    'lang'?: string,
 *    'lengthAdjust'?: string,
 *    'letter-spacing'?: string,
 *    'lighting-color'?: string,
 *    'limitingConeAngle'?: string,
 *    'local'?: string,
 *    'marker-end'?: string,
 *    'marker-mid'?: string,
 *    'marker-start'?: string,
 *    'markerHeight'?: string,
 *    'markerUnits'?: string,
 *    'markerWidth'?: string,
 *    'mask'?: string,
 *    'maskContentUnits'?: string,
 *    'maskUnits'?: string,
 *    'max'?: string,
 *    'media'?: string,
 *    'method'?: string,
 *    'min'?: string,
 *    'mode'?: string,
 *    'numOctaves'?: string,
 *    'offset'?: string,
 *    'opacity'?: string,
 *    'operator'?: string,
 *    'order'?: string,
 *    'orient'?: string,
 *    'origin'?: string,
 *    'overflow'?: string,
 *    'paint-order'?: string,
 *    'path'?: string,
 *    'pathLength'?: string,
 *    'patternContentUnits'?: string,
 *    'patternTransform'?: string,
 *    'patternUnits'?: string,
 *    'ping'?: string,
 *    'pointer-events'?: string,
 *    'points'?: string,
 *    'pointsAtX'?: string,
 *    'pointsAtY'?: string,
 *    'pointsAtZ'?: string,
 *    'preserveAlpha'?: string,
 *    'preserveAspectRatio'?: string,
 *    'primitiveUnits'?: string,
 *    'r'?: string,
 *    'radius'?: string,
 *    'refX'?: string,
 *    'refY'?: string,
 *    'referrerPolicy'?: string,
 *    'rel'?: string,
 *    'rendering-intent'?: string,
 *    'repeatCount'?: string,
 *    'repeatDur'?: string,
 *    'requiredExtensions'?: string,
 *    'requiredFeatures'?: string,
 *    'restart'?: string,
 *    'result'?: string,
 *    'rotate'?: string,
 *    'rx'?: string,
 *    'ry'?: string,
 *    'scale'?: string,
 *    'seed'?: string,
 *    'shape-rendering'?: string,
 *    'side'?: string,
 *    'slope'?: string,
 *    'spacing'?: string,
 *    'specularConstant'?: string,
 *    'specularExponent'?: string,
 *    'speed'?: string,
 *    'spreadMethod'?: string,
 *    'startOffset'?: string,
 *    'stdDeviation'?: string,
 *    'stitchTiles'?: string,
 *    'stop-color'?: string,
 *    'stop-opacity'?: string,
 *    'stroke'?: string,
 *    'stroke-dasharray'?: string,
 *    'stroke-dashoffset'?: string,
 *    'stroke-linecap'?: string,
 *    'stroke-linejoin'?: string,
 *    'stroke-miterlimit'?: string,
 *    'stroke-opacity'?: string,
 *    'stroke-width'?: string,
 *    'style'?: string,
 *    'surfaceScale'?: string,
 *    'systemLanguage'?: string,
 *    'tabindex'?: string,
 *    'tableValues'?: string,
 *    'target'?: string,
 *    'targetX'?: string,
 *    'targetY'?: string,
 *    'text-anchor'?: string,
 *    'text-decoration'?: string,
 *    'text-rendering'?: string,
 *    'textLength'?: string,
 *    'to'?: string,
 *    'transform'?: string,
 *    'transform-origin'?: string,
 *    'type'?: string,
 *    'unicode-bidi'?: string,
 *    'values'?: string,
 *    'vector-effect'?: string,
 *    'version'?: string,
 *    'viewBox'?: string,
 *    'visibility'?: string,
 *    'width'?: string,
 *    'word-spacing'?: string,
 *    'writing-mode'?: string,
 *    'x'?: string,
 *    'x1'?: string,
 *    'x2'?: string,
 *    'xChannelSelector'?: string,
 *    'xlink:actuate'?: string,
 *    'xlink:arcrole'?: string,
 *    'xlink:href Deprecated'?: string,
 *    'xlink:href'?: string,
 *    'xlink:role'?: string,
 *    'xlink:show'?: string,
 *    'xlink:title'?: string,
 *    'xlink:type'?: string,
 *    'xml:lang'?: string,
 *    'xml:space'?: string,
 *    'y'?: string,
 *    'y1'?: string,
 *    'y2'?: string,
 *    'yChannelSelector'?: string,
 *    'z'?: string,
 *    'zoomAndPan'?: string,
 * }
 *
 * @phpstan-type TSVGAttribChild array{
 *    'name': string,
 *    'attr': TSVGAttributes,
 * }
 *
 * @phpstan-type TSVGAttribs array{
 *    'name': string,
 *    'attr': TSVGAttributes,
 *    'tm'?: TTMatrix,
 *    'child'?: array<string, TSVGAttribChild>,
 * }
 *
 * @phpstan-type TSVGObj array{
 *    'defsmode': bool,
 *    'clipmode': bool,
 *    'clipid': int,
 *    'tagdepth': int,
 *    'x0': float,
 *    'y0': float,
 *    'x': float,
 *    'y': float,
 *    'refunitval': TRefUnitValues,
 *    'gradientid': string,
 *    'gradients': array<string, TSVGGradient>,
 *    'clippaths': array<string, TSVGAttribs>,
 *    'defs': array<string, TSVGAttribs>,
 *    'cliptm': TTMatrix,
 *    'styles': array<int, TSVGStyle>,
 *    'child': array<int>,
 *    'textmode': TSVGTextMode,
 *    'text': string,
 *    'dir': string,
 *    'out': string,
 * }
 *
 */
abstract class SVG extends \Com\Tecnick\Pdf\Text
{
    /**
     * Deafult unit of measure for SVG (px = pixels).
     *
     * @var string
     */
    protected const SVGUNIT = 'px';

    /**
     * Default SVG minimum length in points.
     *
     * @var float
     */
    protected const SVGMINPNTLEN = 0.01;

   /**
     * Default SVG maximum value for float.
     *
     * @var float
     */
    protected const SVGMAXVAL = 2147483647.0;

   /**
    * Identity Transofrmation matrix.
    *
    * @var TTMatrix
    */
    protected const TMXID = [1.0, 0.0, 0.0, 1.0, 0.0, 0.0];

    /**
    * Array of inheritable SVG properties.
     *
     * @var array<string>
     */
    protected const SVGINHPROP = [
        'clip-rule',
        'color',
        'color-interpolation',
        'color-interpolation-filters',
        'color-profile',
        'color-rendering',
        'cursor',
        'direction',
        'display',
        'fill',
        'fill-opacity',
        'fill-rule',
        'font',
        'font-family',
        'font-size',
        'font-size-adjust',
        'font-stretch',
        'font-style',
        'font-variant',
        'font-weight',
        'glyph-orientation-horizontal',
        'glyph-orientation-vertical',
        'image-rendering',
        'kerning',
        'letter-spacing',
        'marker',
        'marker-end',
        'marker-mid',
        'marker-start',
        'pointer-events',
        'shape-rendering',
        'stroke',
        'stroke-dasharray',
        'stroke-dashoffset',
        'stroke-linecap',
        'stroke-linejoin',
        'stroke-miterlimit',
        'stroke-opacity',
        'stroke-width',
        'text-anchor',
        'text-rendering',
        'visibility',
        'word-spacing',
        'writing-mode',
    ];

    /**
     * Default SVG style.
     *
     * @var TSVGStyle
     */
    protected const DEFSVGSTYLE = [
        'alignment-baseline' => 'auto',
        'baseline-shift' => 'baseline',
        'clip' => 'auto',
        'clip-path' => 'none',
        'clip-rule' => 'nonzero',
        'color' => 'black',
        'color-interpolation' => 'sRGB',
        'color-interpolation-filters' => 'linearRGB',
        'color-profile' => 'auto',
        'color-rendering' => 'auto',
        'cursor' => 'auto',
        'direction' => 'ltr',
        'display' => 'inline',
        'dominant-baseline' => 'auto',
        'enable-background' => 'accumulate',
        'fill' => 'black',
        'fill-opacity' => 1.0,
        'fill-rule' => 'nonzero',
        'filter' => 'none',
        'flood-color' => 'black',
        'flood-opacity' => 1.0,
        'font' => '',
        'font-family' => 'helvetica',
        'font-size' => 'medium',
        'font-size-val' => 10.0,
        'font-size-adjust' => 'none',
        'font-stretch' => 'normal',
        'font-stretch-val' => 100.0,
        'font-style' => 'normal',
        'font-variant' => 'normal',
        'font-weight' => 'normal',
        'font-mode' => '',
        'glyph-orientation-horizontal' => '0deg',
        'glyph-orientation-vertical' => 'auto',
        'image-rendering' => 'auto',
        'kerning' => 'auto',
        'letter-spacing' => 'normal',
        'letter-spacing-val' => 0.0,
        'lighting-color' => 'white',
        'marker' => '',
        'marker-end' => 'none',
        'marker-mid' => 'none',
        'marker-start' => 'none',
        'mask' => 'none',
        'objstyle' => '',
        'opacity' => 1.0,
        'overflow' => 'auto',
        'pointer-events' => 'visiblePainted',
        'shape-rendering' => 'auto',
        'stop-color' => 'black',
        'stop-opacity' => 1.0,
        'stroke' => 'none',
        'stroke-dasharray' => 'none',
        'stroke-dashoffset' => 0.0,
        'stroke-linecap' => 'butt',
        'stroke-linejoin' => 'miter',
        'stroke-miterlimit' => 4.0,
        'stroke-opacity' => 1.0,
        'stroke-width' => 1.0,
        'text-anchor' => 'start',
        'text-decoration' => 'none',
        'text-rendering' => 'auto',
        'unicode-bidi' => 'normal',
        'visibility' => 'visible',
        'word-spacing' => 'normal',
        'writing-mode' => 'lr-tb',
        'text-color' => 'black',
        'transfmatrix' => self::TMXID,
    ];

    /**
     * List of possible SVG font attributes to parse.
     *
     * @var array<string>
     */
    protected const FONTATTRIBS = [
        'font-family',
        'font-size-adjust',
        'font-size',
        'font-stretch',
        'font-style',
        'font-variant',
        'font-weight',
        'letter-spacing',
        'text-decoration',
    ];

    /**
     * Modes to check when processing SVG start tags.
     *
     * @var array<int, string>
     */
    protected const SVGDEFSMODESTART = [
        'clipPath',
        'linearGradient',
        'radialGradient',
        'stop',
    ];

    /**
     * Modes to check when processing SVG end tags.
     *
     * @var array<int, string>
     */
    protected const SVGDEFSMODEEND = [
        'defs',
        'clipPath',
        'linearGradient',
        'radialGradient',
        'stop',
    ];

    /**
     * Default SVG object properties.
     *
     * @var TSVGObj
     */
    protected const SVGDEFOBJ = [
        'defsmode' => false,
        'clipmode' => false,
        'clipid' => 0,
        'tagdepth' => 0,
        'x0' => 0.0,
        'y0' => 0.0,
        'x' => 0.0,
        'y' => 0.0,
        'refunitval' => self::REFUNITVAL,
        'gradientid' => '',
        'gradients' => [],
        'clippaths' => [],
        'cliptm' => self::TMXID,
        'defs' => [],
        'styles' => [0 => self::DEFSVGSTYLE],
        'child' => [],
        'textmode' => [
            'rtl' => false,
            'invisible' => false,
            'stroke' => 0,
            'text-anchor' => 'start',
        ],
        'text' => '',
        'dir' => '',
        'out' => '',
    ];

    /**
     * SVG object properties.
     *
     * @var array<int, TSVGObj>
     */
    protected array $svgobjs = [];

    /**
     * SVG minimum length in points.
     *
     * @var float
     */
    protected float $svgminunitlen = 0;

    /**
     * Convert value from SVG units to internal points.
     *
     * @param string|float|int $val Value to convert in user units.
     * @param int $soid SVG object ID.
     * @param ?TRefUnitValues $ref overrides the svg reference unit values.
     */
    protected function svgUnitToPoints(string|float|int $val, int $soid = -1, ?array $ref = null): float
    {
        if (empty($ref)) {
            if (($soid > 0) && (!empty($this->svgobjs[$soid]['refunitval']))) {
                $ref = $this->svgobjs[$soid]['refunitval'];
            } else {
                $ref = self::REFUNITVAL;
            }
        }
        return $this->getUnitValuePoints(
            $val,
            $ref,
            self::SVGUNIT,
        );
    }

    /**
     * Convert value from SVG units to user units.
     *
     * @param string|float|int $val Value to convert in user units.
     * @param int $soid SVG object ID.
     * @param ?TRefUnitValues $ref overrides the svg reference unit values.
     */
    protected function svgUnitToUnit(string|float|int $val, int $soid = -1, ?array $ref = null): float
    {
        return $this->toUnit($this->svgUnitToPoints($val, $soid, $ref));
    }

    /**
     * Parse the SVG transformation 'matrix'.
     *
     * @param string $val Transformation matrix string to parse.
     *
     * @return TTMatrix Transformation matrix.
     */
    protected function parseSVGTMmatrix(string $val): array
    {
        $tmb = $this->graph::IDMATRIX;
        $regs = [];
        if (
            \preg_match(
                '/([a-z0-9\-\.]+)[\,\s]+'
                . '([a-z0-9\-\.]+)[\,\s]+'
                . '([a-z0-9\-\.]+)[\,\s]+'
                . '([a-z0-9\-\.]+)[\,\s]+'
                . '([a-z0-9\-\.]+)[\,\s]+'
                . '([a-z0-9\-\.]+)/si',
                $val,
                $regs,
            )
        ) {
            $tmb[0] = \floatval($regs[1]);
            $tmb[1] = \floatval($regs[2]);
            $tmb[2] = \floatval($regs[3]);
            $tmb[3] = \floatval($regs[4]);
            $tmb[4] = \floatval($regs[5]);
            $tmb[5] = \floatval($regs[6]);
        }
        return $tmb;
    }

    /**
     * Parse the SVG transformation 'translate'.
     *
     * @param string $val Transformation matrix string to parse.
     *
     * @return TTMatrix Transformation matrix.
     */
    protected function parseSVGTMtranslate(string $val): array
    {
        $tmb = $this->graph::IDMATRIX;
        $regs = [];
        if (\preg_match('/([a-z0-9\-\.]+)[\,\s]+([a-z0-9\-\.]+)/si', $val, $regs)) {
            $tmb[4] = \floatval($regs[1]);
            $tmb[5] = \floatval($regs[2]);
            return $tmb;
        }
        if (\preg_match('/([a-z0-9\-\.]+)/si', $val, $regs)) {
            $tmb[4] = \floatval($regs[1]);
        }
        return $tmb;
    }

    /**
     * Parse the SVG transformation 'scale'.
     *
     * @param string $val Transformation matrix string to parse.
     *
     * @return TTMatrix Transformation matrix.
     */
    protected function parseSVGTMscale(string $val): array
    {
        $tmb = $this->graph::IDMATRIX;
        $regs = [];
        if (\preg_match('/([a-z0-9\-\.]+)[\,\s]+([a-z0-9\-\.]+)/si', $val, $regs)) {
            $tmb[0] = \floatval($regs[1]);
            $tmb[3] = \floatval($regs[2]);
            return $tmb;
        }
        if (\preg_match('/([a-z0-9\-\.]+)/si', $val, $regs)) {
            $tmb[0] = \floatval($regs[1]);
            $tmb[3] = $tmb[0];
        }
        return $tmb;
    }

    /**
     * Parse the SVG transformation 'rotate'.
     *
     * @param string $val Transformation matrix string to parse.
     *
     * @return TTMatrix Transformation matrix.
     */
    protected function parseSVGTMrotate(string $val): array
    {
        $tmb = $this->graph::IDMATRIX;
        $regs = [];
        if (\preg_match('/([0-9\-\.]+)[\,\s]+([a-z0-9\-\.]+)[\,\s]+([a-z0-9\-\.]+)/si', $val, $regs)) {
            $ang = \deg2rad(\floatval($regs[1]));
            $trx = \floatval($regs[2]);
            $try = \floatval($regs[3]);
            $tmb[0] = \cos($ang);
            $tmb[1] = \sin($ang);
            $tmb[2] = -$tmb[1];
            $tmb[3] = $tmb[0];
            $tmb[4] = ($trx * (1 - $tmb[0])) - ($try * $tmb[2]);
            $tmb[5] = ($try * (1 - $tmb[3])) - ($trx * $tmb[1]);
            return $tmb;
        }
        if (\preg_match('/([0-9\-\.]+)/si', $val, $regs)) {
            $ang = \deg2rad(\floatval($regs[1]));
            $tmb[0] = \cos($ang);
            $tmb[1] = \sin($ang);
            $tmb = [$tmb[0], $tmb[1], -$tmb[1], $tmb[0], 0, 0];
        }
        return $tmb;
    }

    /**
     * Parse the SVG transformation 'skewX'.
     *
     * @param string $val Transformation matrix string to parse.
     *
     * @return TTMatrix Transformation matrix.
     */
    protected function parseSVGTMskewX(string $val): array
    {
        $tmb = $this->graph::IDMATRIX;
        $regs = [];
        if (\preg_match('/([0-9\-\.]+)/si', $val, $regs)) {
            $tmb[2] = \tan(\deg2rad(\floatval($regs[1])));
        }
        return $tmb;
    }

    /**
     * Parse the SVG transformation 'skewY'.
     *
     * @param string $val Transformation matrix string to parse.
     *
     * @return TTMatrix Transformation matrix.
     */
    protected function parseSVGTMskewY(string $val): array
    {
        $tmb = $this->graph::IDMATRIX;
        $regs = [];
        if (\preg_match('/([0-9\-\.]+)/si', $val, $regs)) {
            $tmb[1] = \tan(\deg2rad(\floatval($regs[1])));
        }
        return $tmb;
    }

    /**
     * Get the tranformation matrix from the SVG 'transform' attribute.
     *
     * @param string $attr Transformation attribute.
     *
     * @return TTMatrix Transformation matrix.
     */
    protected function getSVGTransformMatrix(string $attr): array
    {
        $transform = [];
        $tma = $this->graph::IDMATRIX;

        if (
            !\preg_match_all(
                '/(matrix|translate|scale|rotate|skewX|skewY)[\s]*+\(([^\)]+)\)/si',
                $attr,
                $transform,
                PREG_SET_ORDER,
            ) > 0
        ) {
            return $tma;
        }

        foreach ($transform as $data) {
            if (empty($data[2])) {
                continue;
            }

            $val = $data[2];

            $tmb = match ($data[1]) {
                'matrix' => $this->parseSVGTMmatrix($val),
                'translate' => $this->parseSVGTMtranslate($val),
                'scale' => $this->parseSVGTMscale($val),
                'rotate' => $this->parseSVGTMrotate($val),
                'skewX' => $this->parseSVGTMskewX($val),
                'skewY' => $this->parseSVGTMskewY($val),
                default => $this->graph::IDMATRIX,
            };
            $tma = $this->graph->getCtmProduct($tma, $tmb);
        }

        return $tma;
    }

    /**
     * Convert SVG transformation matrix to PDF.
     *
     * @param TTMatrix $trm original SVG transformation matrix.
     * @param int $soid SVG object ID.
     *
     * @return TTMatrix Transformation matrix.
     */
    protected function convertSVGMatrix(
        array $trm,
        int $soid = 0,
    ): array {
        $pheight = $this->svgobjs[$soid]['refunitval']['page']['height'];
        $trm[1] = -$trm[1];
        $trm[2] = -$trm[2];
        $trm[4] = $this->svgUnitToPoints($trm[4], $soid) - ($pheight * $trm[2]);
        $trm[5] = ($pheight * (1 - $trm[3])) - $this->svgUnitToPoints($trm[5], $soid);
        return $trm;
    }

    /**
     * Get the SVG tranformation matrix (CTM) PDF string.
     *
     * @param TTMatrix $trm original SVG transformation matrix.
     * @param int $soid SVG object ID.
     *
     * @return string Transformation matrix (PDF string).
     */
    protected function getOutSVGTransformation(
        array $trm,
        int $soid = 0,
    ): string {
        return $this->graph->getTransformation(
            $this->convertSVGMatrix($trm, $soid),
        );
    }

    /**
     * Return the tag name without the namespace
     *
     * @param string $name Tag name
     *
     * @return string Tag name without the namespace
     */
    protected function removeTagNamespace(string $name)
    {
        $parts = \explode(':', $name);
        return \end($parts);
    }

    /**
     * Draw the SVG path.
     *
     * @param int $soid ID of the current SVG object.
     * @param string $attrd
     * @param string $mode
     * @return string
     */
    protected function getSVGPath(
        int $soid,
        string $attrd,
        string $mode = '',
    ): string {
        // set paint operator
        $pop = $this->graph->getPathPaintOp($mode, '');
        if (empty($pop)) {
            return '';
        }

        // extract paths
        $attrd = \preg_replace('/([0-9ACHLMQSTVZ])([\-\+])/si', '\\1 \\2', $attrd);
        if (empty($attrd)) {
            return '';
        }

        $attrd = \preg_replace('/(\.[0-9]+)(\.)/s', '\\1 \\2', $attrd);
        if (empty($attrd)) {
            return '';
        }

        $paths = [];
        \preg_match_all('/([ACHLMQSTVZ])[\s]*+([^ACHLMQSTVZ\"]*+)/si', $attrd, $paths, PREG_SET_ORDER);

        // initialize variables
        $out = '';

        $coord = [
            'x' => 0.0,
            'y' => 0.0,
            'x0' => 0.0,
            'y0' => 0.0,
            'xmin' => self::SVGMAXVAL,
            'xmax' => 0.0,
            'ymin' => self::SVGMAXVAL,
            'ymax' => 0.0,
            'xinit' => 0.0,
            'yinit' => 0.0,
            'xoffset' => 0.0,
            'yoffset' => 0.0,
            'relcoord' => false,
            'firstcmd' => true,
        ];

        // draw curve pieces
        foreach ($paths as $key => $val) {
            // get curve type
            $cmd = \trim($val[1]);

            // relative or absolute coordinates
            $coord['relcoord'] = (\strtolower($cmd) == $cmd);
            if ($coord['relcoord']) {
                // use relative coordinated instead of absolute
                $coord['xoffset'] = $coord['x'];
                $coord['yoffset'] = $coord['y'];
            } else {
                $coord['xoffset'] = 0.0;
                $coord['yoffset'] = 0.0;
            }

            $rawparams = [];
            $params = [];

            // get curve parameters
            $rprms = [];
            \preg_match_all('/-?\d+(?:\.\d+)?/', \trim($val[2]), $rprms);
            $rawparams = $rprms[0];

            foreach ($rawparams as $prk => $prv) {
                $params[$prk] = $this->svgUnitToUnit($prv, $soid);
                if (\abs($params[$prk]) < $this->svgminunitlen) {
                    // approximate little values to zero
                    $params[$prk] = 0.0;
                }
            }

            // store current origin point
            $coord['x0'] = $coord['x'];
            $coord['y0'] = $coord['y'];

            $out .= match (\strtoupper($cmd)) {
                'A' => $this->svgPathCmdA($params, $coord, $paths, $key, $rawparams),
                'C' => $this->svgPathCmdC($params, $coord),
                'H' => $this->svgPathCmdH($params, $coord),
                'L' => $this->svgPathCmdL($params, $coord),
                'M' => $this->svgPathCmdM($params, $coord),
                'Q' => $this->svgPathCmdQ($params, $coord),
                'S' => $this->svgPathCmdS($params, $coord, $paths, $key),
                'T' => $this->svgPathCmdT($params, $coord, $paths, $key),
                'V' => $this->svgPathCmdV($params, $coord),
                'Z' => $this->svgPathCmdZ($coord),
                default => '',
            };

            $coord['firstcmd'] = false;
        }

        $this->bbox[] = [
            'x' => $coord['xmin'],
            'y' => $coord['ymin'],
            'w' => ($coord['xmax'] - $coord['xmin']),
            'h' => ($coord['ymax'] - $coord['ymin']),
        ];

        $out .= $pop;

        return $out;
    }

    /**
     * Process SCG path command 'A' (elliptical arc).
     *
     * @param array<float> $prm Parameters.
     * @param TSCGCoord $crd Current coordinates.
     * @param array<array<string>> $paths All paths.
     * @param int $key Current key.
     * @param array<string> $rawparams Raw parameters.
     *
     * @return string
     */
    protected function svgPathCmdA(array $prm, array &$crd, array $paths, int $key, array $rawparams): string
    {
        $out = '';

        foreach ($prm as $prk => $prv) {
            if ((($prk + 1) % 7) != 0) {
                continue;
            }

            $crd['x0'] = $crd['x'];
            $crd['y0'] = $crd['y'];
            $rpx = (float) \max(\abs($prm[($prk - 6)]), .000000001);
            $rpy = (float) \max(\abs($prm[($prk - 5)]), .000000001);
            $ang = -\intval($rawparams[($prk - 4)]);
            $angle = \deg2rad($ang);
            $laf = $rawparams[($prk - 3)]; // large-arc-flag
            $swf = $rawparams[($prk - 2)]; // sweep-flag
            $crd['x'] = $prm[($prk - 1)] + $crd['xoffset'];
            $crd['y'] = $prv + $crd['yoffset'];

            if (
                (\abs($crd['x0'] - $crd['x']) < $this->svgminunitlen) &&
                (\abs($crd['y0'] - $crd['y']) < $this->svgminunitlen)
            ) {
                // endpoints are almost identical
                $crd['xmin'] = (float) \min($crd['xmin'], $crd['x']);
                $crd['ymin'] = (float) \min($crd['ymin'], $crd['y']);
                $crd['xmax'] = (float) \max($crd['xmax'], $crd['x']);
                $crd['ymax'] = (float) \max($crd['ymax'], $crd['y']);
            } else {
                $cos_ang = \cos($angle);
                $sin_ang = \sin($angle);
                $cra = (($crd['x0'] - $crd['x']) / 2);
                $crb = (($crd['y0'] - $crd['y']) / 2);
                $pxa = ($cra * $cos_ang) - ($crb * $sin_ang);
                $pya = ($cra * $sin_ang) + ($crb * $cos_ang);
                $rx2 = $rpx * $rpx;
                $ry2 = $rpy * $rpy;
                $pxa2 = $pxa * $pxa;
                $pya2 = $pya * $pya;
                $delta = ($pxa2 / $rx2) + ($pya2 / $ry2);
                if ($delta > 1) {
                    $rpx *= \sqrt($delta);
                    $rpy *= \sqrt($delta);
                    $rx2 = $rpx * $rpx;
                    $ry2 = $rpy * $rpy;
                }
                $numerator = (($rx2 * $ry2) - ($rx2 * $pya2) - ($ry2 * $pxa2));
                $root = 0;
                if ($numerator > 0) {
                    $root = \sqrt($numerator / (($rx2 * $pya2) + ($ry2 * $pxa2)));
                }
                if ($laf == $swf) {
                    $root *= -1;
                }
                $cax = $root * (($rpx * $pya) / $rpy);
                $cay = -$root * (($rpy * $pxa) / $rpx);
                // coordinates of ellipse center
                $pcx = ($cax * $cos_ang) - ($cay * $sin_ang) + (($crd['x0'] + $crd['x']) / 2);
                $pcy = ($cax * $sin_ang) + ($cay * $cos_ang) + (($crd['y0'] + $crd['y']) / 2);
                // get angles
                $angs = $this->graph->getVectorsAngle(
                    1,
                    0,
                    (($pxa - $cax) / $rpx),
                    (($cay - $pya) / $rpy),
                );
                $dang = $this->graph->getVectorsAngle(
                    (($pxa - $cax) / $rpx),
                    (($pya - $cay) / $rpy),
                    ((-$pxa - $cax) / $rpx),
                    ((-$pya - $cay) / $rpy),
                );
                if (($swf == 0) && ($dang > 0)) {
                    $dang -= (2 * M_PI);
                } elseif (($swf == 1) && ($dang < 0)) {
                    $dang += (2 * M_PI);
                }
                $angf = $angs - $dang;
                if ((($swf == 0) && ($angs > $angf)) || (($swf == 1) && ($angs < $angf))) {
                    // reverse angles
                    $tmp = $angs;
                    $angs = $angf;
                    $angf = $tmp;
                }
                $angs = \round(\rad2deg($angs), 6);
                $angf = \round(\rad2deg($angf), 6);
                // covent angles to positive values
                if (($angs < 0) && ($angf < 0)) {
                    $angs += 360;
                    $angf += 360;
                }
                $pie = false;
                if (($key == 0) && (isset($paths[($key + 1)][1])) && (\trim($paths[($key + 1)][1]) == 'z')) {
                    $pie = true;
                }
                // list($axmin, $aymin, $axmax, $aymax)
                $bbox = [0, 0, 0, 0];
                $out .= $this->graph->getRawEllipticalArc(
                    $pcx,
                    $pcy,
                    $rpx,
                    $rpy,
                    $ang,
                    $angs,
                    $angf,
                    $pie,
                    2,
                    false,
                    ($swf == 0),
                    true,
                    $bbox,
                );
                $crd['xmin'] = (float) \min($crd['xmin'], $crd['x'], $bbox[0]);
                $crd['ymin'] = (float) \min($crd['ymin'], $crd['y'], $bbox[1]);
                $crd['xmax'] = (float) \max($crd['xmax'], $crd['x'], $bbox[2]);
                $crd['ymax'] = (float) \max($crd['ymax'], $crd['y'], $bbox[3]);
            }

            if ($crd['relcoord']) {
                $crd['xoffset'] = $crd['x'];
                $crd['yoffset'] = $crd['y'];
            }
        }


        return $out;
    }

    /**
     * Process SCG path command 'C' (curveto).
     *
     * @param array<float> $prm Parameters.
     * @param TSCGCoord $crd Current coordinates.
     *
     * @return string
     */
    protected function svgPathCmdC(array $prm, array &$crd): string
    {
        $out = '';

        foreach ($prm as $prk => $prv) {
            if ((($prk + 1) % 6) != 0) {
                continue;
            }
            $px1 = $prm[($prk - 5)] + $crd['xoffset'];
            $py1 = $prm[($prk - 4)] + $crd['yoffset'];
            $px2 = $prm[($prk - 3)] + $crd['xoffset'];
            $py2 = $prm[($prk - 2)] + $crd['yoffset'];
            $crd['x'] = $prm[($prk - 1)] + $crd['xoffset'];
            $crd['y'] = $prv + $crd['yoffset'];
            $out .= $this->graph->getRawCurve($px1, $py1, $px2, $py2, $crd['x'], $crd['y']);
            $crd['xmin'] = (float) \min($crd['xmin'], $crd['x'], $px1, $px2);
            $crd['ymin'] = (float) \min($crd['ymin'], $crd['y'], $py1, $py2);
            $crd['xmax'] = (float) \max($crd['xmax'], $crd['x'], $px1, $px2);
            $crd['ymax'] = (float) \max($crd['ymax'], $crd['y'], $py1, $py2);
            if ($crd['relcoord']) {
                $crd['xoffset'] = $crd['x'];
                $crd['yoffset'] = $crd['y'];
            }
        }

        return $out;
    }

    /**
     * Process SCG path command 'H' (horizontal lineto).
     *
     * @param array<float> $prm Parameters.
     * @param TSCGCoord $crd Current coordinates.
     *
     * @return string
     */
    protected function svgPathCmdH(array $prm, array &$crd): string
    {
        $out = '';

        foreach ($prm as $prv) {
            $crd['x'] = $prv + $crd['xoffset'];
            if (
                (\abs($crd['x0'] - $crd['x']) >= $this->svgminunitlen) ||
                (\abs($crd['y0'] - $crd['y']) >= $this->svgminunitlen)
            ) {
                $out .= $this->graph->getRawLine($crd['x'], $crd['y']);
                $crd['x0'] = $crd['x'];
                $crd['y0'] = $crd['y'];
            }
            $crd['xmin'] = \min($crd['xmin'], $crd['x']);
            $crd['xmax'] = \max($crd['xmax'], $crd['x']);
            if ($crd['relcoord']) {
                $crd['xoffset'] = $crd['x'];
            }
        }

        return $out;
    }

    /**
     * Process SCG path command 'L' (lineto).
     *
     * @param array<float> $prm Parameters.
     * @param TSCGCoord $crd Current coordinates.
     *
     * @return string
     */
    protected function svgPathCmdL(array $prm, array &$crd): string
    {
        $out = '';

        foreach ($prm as $prk => $prv) {
            if (($prk % 2) == 0) {
                $crd['x'] = $prv + $crd['xoffset'];
                continue;
            }

            $crd['y'] = $prv + $crd['yoffset'];
            if (
                (\abs($crd['x0'] - $crd['x']) >= $this->svgminunitlen) ||
                (\abs($crd['y0'] - $crd['y']) >= $this->svgminunitlen)
            ) {
                $out .= $this->graph->getRawLine($crd['x'], $crd['y']);
                $crd['x0'] = $crd['x'];
                $crd['y0'] = $crd['y'];
            }
            $crd['xmin'] = \min($crd['xmin'], $crd['x']);
            $crd['ymin'] = \min($crd['ymin'], $crd['y']);
            $crd['xmax'] = \max($crd['xmax'], $crd['x']);
            $crd['ymax'] = \max($crd['ymax'], $crd['y']);
            if ($crd['relcoord']) {
                $crd['xoffset'] = $crd['x'];
                $crd['yoffset'] = $crd['y'];
            }
        }

        return $out;
    }

    /**
     * Process SCG path command 'M' (moveto)
     *
     * @param array<float> $prm Parameters.
     * @param TSCGCoord $crd Current coordinates.
     *
     * @return string
     */
    protected function svgPathCmdM(array $prm, array &$crd): string
    {
        $out = '';

        foreach ($prm as $prk => $prv) {
            if (($prk % 2) == 0) {
                $crd['x'] = $prv + $crd['xoffset'];
                continue;
            }

            $crd['y'] = $prv + $crd['yoffset'];
            if (
                $crd['firstcmd'] ||
                (\abs($crd['x0'] - $crd['x']) >= $this->svgminunitlen) ||
                (\abs($crd['y0'] - $crd['y']) >= $this->svgminunitlen)
            ) {
                if ($prk == 1) {
                    $out .= $this->graph->getRawPoint($crd['x'], $crd['y']);
                    $crd['firstcmd'] = false;
                    $crd['xinit'] = $crd['x'];
                    $crd['yinit'] = $crd['y'];
                } else {
                    $out .= $this->graph->getRawLine($crd['x'], $crd['y']);
                }
                $crd['x0'] = $crd['x'];
                $crd['y0'] = $crd['y'];
            }
            $crd['xmin'] = \min($crd['xmin'], $crd['x']);
            $crd['ymin'] = \min($crd['ymin'], $crd['y']);
            $crd['xmax'] = \max($crd['xmax'], $crd['x']);
            $crd['ymax'] = \max($crd['ymax'], $crd['y']);
            if ($crd['relcoord']) {
                $crd['xoffset'] = $crd['x'];
                $crd['yoffset'] = $crd['y'];
            }
        }

        return $out;
    }

    /**
     * Process SCG path command 'Q' (quadratic Bezier curveto).
     *
     * @param array<float> $prm Parameters.
     * @param TSCGCoord $crd Current coordinates.
     *
     * @return string
     */
    protected function svgPathCmdQ(array $prm, array &$crd): string
    {
        $out = '';

        foreach ($prm as $prk => $prv) {
            if ((($prk + 1) % 4) != 0) {
                continue;
            }

            // convert quadratic points to cubic points
            $px1 = $prm[($prk - 3)] + $crd['xoffset'];
            $py1 = $prm[($prk - 2)] + $crd['yoffset'];
            $pxa = ($crd['x'] + (2 * $px1)) / 3;
            $pya = ($crd['y'] + (2 * $py1)) / 3;
            $crd['x'] = $prm[($prk - 1)] + $crd['xoffset'];
            $crd['y'] = $prv + $crd['yoffset'];
            $pxb = ($crd['x'] + (2 * $px1)) / 3;
            $pyb = ($crd['y'] + (2 * $py1)) / 3;
            $out .= $this->graph->getRawCurve($pxa, $pya, $pxb, $pyb, $crd['x'], $crd['y']);
            $crd['xmin'] = \min($crd['xmin'], $crd['x'], $pxa, $pxb);
            $crd['ymin'] = \min($crd['ymin'], $crd['y'], $pya, $pyb);
            $crd['xmax'] = \max($crd['xmax'], $crd['x'], $pxa, $pxb);
            $crd['ymax'] = \max($crd['ymax'], $crd['y'], $pya, $pyb);
            if ($crd['relcoord']) {
                $crd['xoffset'] = $crd['x'];
                $crd['yoffset'] = $crd['y'];
            }
        }

        return $out;
    }

    /**
     * Process SCG path command 'S' (shorthand/smooth curveto).
     *
     * @param array<float> $prm Parameters.
     * @param TSCGCoord $crd Current coordinates.
     * @param array<array<string>> $paths All paths.
     * @param int $key Current key.
     *
     * @return string
     */
    protected function svgPathCmdS(array $prm, array &$crd, array $paths, int $key): string
    {
        $out = '';

        $px2 = 0.0;
        $py2 = 0.0;

        foreach ($prm as $prk => $prv) {
            if ((($prk + 1) % 4) != 0) {
                continue;
            }

            if (
                ($key > 0) &&
                ((\strtoupper($paths[($key - 1)][1]) == 'C') ||
                (\strtoupper($paths[($key - 1)][1]) == 'S'))
            ) {
                $px1 = (2 * $crd['x']) - $px2;
                $py1 = (2 * $crd['y']) - $py2;
            } else {
                $px1 = $crd['x'];
                $py1 = $crd['y'];
            }

            $px2 = $prm[($prk - 3)] + $crd['xoffset'];
            $py2 = $prm[($prk - 2)] + $crd['yoffset'];
            $crd['x'] = $prm[($prk - 1)] + $crd['xoffset'];
            $crd['y'] = $prv + $crd['yoffset'];
            $out .= $this->graph->getRawCurve($px1, $py1, $px2, $py2, $crd['x'], $crd['y']);
            $crd['xmin'] = \min($crd['xmin'], $crd['x'], $px1, $px2);
            $crd['ymin'] = \min($crd['ymin'], $crd['y'], $py1, $py2);
            $crd['xmax'] = \max($crd['xmax'], $crd['x'], $px1, $px2);
            $crd['ymax'] = \max($crd['ymax'], $crd['y'], $py1, $py2);
            if ($crd['relcoord']) {
                $crd['xoffset'] = $crd['x'];
                $crd['yoffset'] = $crd['y'];
            }
        }

        return $out;
    }

    /**
     * Process SCG path command 'T' (shorthand/smooth quadratic Bezier curveto).
     *
     * @param array<float> $prm Parameters.
     * @param TSCGCoord $crd Current coordinates.
     * @param array<array<string>> $paths All paths.
     * @param int $key Current key.
     *
     * @return string
     */
    protected function svgPathCmdT(array $prm, array &$crd, array $paths, int $key): string
    {
        $out = '';

        $px1 = 0.0;
        $py1 = 0.0;

        foreach ($prm as $prk => $prv) {
            if (($prk % 2) == 0) {
                continue;
            }

            if (
                ($key > 0) &&
                ((\strtoupper($paths[($key - 1)][1]) == 'Q') ||
                (\strtoupper($paths[($key - 1)][1]) == 'T'))
            ) {
                $px1 = (2 * $crd['x']) - $px1;
                $py1 = (2 * $crd['y']) - $py1;
            } else {
                $px1 = $crd['x'];
                $py1 = $crd['y'];
            }

            // convert quadratic points to cubic points
            $pxa = ($crd['x'] + (2 * $px1)) / 3;
            $pya = ($crd['y'] + (2 * $py1)) / 3;
            $crd['x'] = $prm[($prk - 1)] + $crd['xoffset'];
            $crd['y'] = $prv + $crd['yoffset'];
            $pxb = ($crd['x'] + (2 * $px1)) / 3;
            $pyb = ($crd['y'] + (2 * $py1)) / 3;
            $out .= $this->graph->getRawCurve($pxa, $pya, $pxb, $pyb, $crd['x'], $crd['y']);
            $crd['xmin'] = \min($crd['xmin'], $crd['x'], $pxa, $pxb);
            $crd['ymin'] = \min($crd['ymin'], $crd['y'], $pya, $pyb);
            $crd['xmax'] = \max($crd['xmax'], $crd['x'], $pxa, $pxb);
            $crd['ymax'] = \max($crd['ymax'], $crd['y'], $pya, $pyb);
            if ($crd['relcoord']) {
                $crd['xoffset'] = $crd['x'];
                $crd['yoffset'] = $crd['y'];
            }
        }

        return $out;
    }

    /**
     * Process SCG path command 'V' (vertical lineto).
     *
     * @param array<float> $prm Parameters.
     * @param TSCGCoord $crd Current coordinates.
     *
     * @return string
     */
    protected function svgPathCmdV(array $prm, array &$crd): string
    {
        $out = '';

        foreach ($prm as $prv) {
            $crd['y'] = $prv + $crd['yoffset'];
            if (
                (\abs($crd['x0'] - $crd['x']) >= $this->svgminunitlen) ||
                (\abs($crd['y0'] - $crd['y']) >= $this->svgminunitlen)
            ) {
                $out .= $this->graph->getRawLine($crd['x'], $crd['y']);
                $crd['x0'] = $crd['x'];
                $crd['y0'] = $crd['y'];
            }
            $crd['ymin'] = \min($crd['ymin'], $crd['y']);
            $crd['ymax'] = \max($crd['ymax'], $crd['y']);
            if ($crd['relcoord']) {
                $crd['yoffset'] = $crd['y'];
            }
        }

        return $out;
    }

    /**
     * Process SCG path command 'Z'.
     *
     * @param TSCGCoord $crd Current coordinates.
     *
     * @return string
     */
    protected function svgPathCmdZ(array &$crd): string
    {
        $crd['x'] = $crd['x0'] = $crd['xinit'];
        $crd['y'] = $crd['y0'] = $crd['yinit'];
        return "h\n";
    }

    // ----------------------------------------

    /**
     * Returns the letter-spacing value.
     *
     * @param string $spacing letter-spacing value.
     * @param float $parent font spacing (tracking) value of the parent element.
     *
     * @return float Quantity to increases or decreases the space between characters in a text.
     */
    protected function getTALetterSpacing(string $spacing, float $parent = 0.0): float
    {
        $spacing = \trim($spacing);
        return match ($spacing) {
            'normal' => 0.0,
            'inherit' => $parent,
            default => $this->svgUnitToPoints($spacing, -1, \array_merge(self::REFUNITVAL, ['parent' => $parent])),
        };
    }

    /**
     * Returns the percentage of font stretching.
     *
     * @param string $stretch stretch mode
     * @param float $parent stretch value of the parent element
     *
     * @return float font stretching percentage
     */
    protected function getTAFontStretching(string $stretch, float $parent = 100): float
    {
        $stretch = \trim($stretch);
        return match ($stretch) {
            'ultra-condensed' => 40,
            'extra-condensed' => 55,
            'condensed' => 70,
            'semi-condensed' => 85,
            'normal' => 100,
            'semi-expanded' => 115,
            'expanded' => 130,
            'extra-expanded' => 145,
            'ultra-expanded' => 160,
            'wider' => ($parent + 10),
            'narrower' => ($parent - 10),
            'inherit' => $parent,
            default => $this->getUnitValuePoints($stretch, \array_merge(self::REFUNITVAL, ['parent' => $parent]), '%'),
        };
    }

    /**
     * Returns the font weight letter.
     *
     * @param string $weight Font weight Description.
     *
     * @return string Font weight Letter('B'|'').
     */
    protected function getTAFontWeight(string $weight): string
    {
        $weight = \trim($weight);
        return match ($weight) {
            'bold', 'bolder' => 'B',
            // default to 'normal'
            default => '',
        };
    }

    /**
     * Returns the font style letter.
     *
     * @param string $style Font style Description.
     *
     * @return string Font style Letter ('I'|'').
     */
    protected function getTAFontStyle(string $style): string
    {
        $style = \trim($style);
        return match ($style) {
            'italic', 'oblique' => 'I',
            // default to 'normal'
            default => '',
        };
    }

    /**
     * Returns the font decoration letter
     *
     * @param string $decoration Font decoration Description.
     *
     * @return string Font decoration Letter('U'|'O'|'D'|'').
     */
    protected function getTAFontDecoration(string $decoration): string
    {
        $decoration = \trim($decoration);
        return match ($decoration) {
            'underline' => 'U',
            'overline' => 'O',
            'line-through' => 'D',
            default => '',
        };
    }

    /**
     * Parse the SVG style font attributes.
     *
     * @param string $tag Font tag content.
     * @param string $attr Attribute name.
     * @param string $default Default value.
     *
     * @return string
     */
    protected function parseCSSAttrib(string $tag, string $attr, string $default = ''): string
    {
        if (\preg_match('/' . $attr . '[\s]*+:[\s]*+([^\;\"]*+)/si', $tag, $regs)) {
            return \trim($regs[1]);
        }
        return $default;
    }

    /**
     * Parse the SVG font style.
     *
     * @param TSVGStyle $svgstyle SVG style.
     * @param TSVGStyle $parent Parent SVG style.
     *
     * @return string the Raw PDF command to insert the font.
     */
    protected function parseSVGStyleFont(
        array &$svgstyle,
        array $parent = self::DEFSVGSTYLE,
    ): string {
        if (!empty($svgstyle['font'])) {
            // get font attributes from CSS style
            $font = $svgstyle['font'];
            foreach (self::FONTATTRIBS as $attr) {
                $svgstyle[$attr] = $this->parseCSSAttrib(
                    $font, // @phpstan-ignore-line
                    $attr,
                    $svgstyle[$attr], // @phpstan-ignore-line
                );
            }
        }

        $svgstyle['font-family'] = (empty($svgstyle['font-family'])) ?
        $parent['font-family'] :
        $this->font->getFontFamilyName($svgstyle['font-family']); // @phpstan-ignore-line

        $svgstyle['letter-spacing-val'] = $this->getTALetterSpacing(
            $svgstyle['letter-spacing'], // @phpstan-ignore-line
            $parent['letter-spacing-val'],
        );
        $svgstyle['font-stretch-val'] = $this->getTAFontStretching(
            $svgstyle['font-stretch'], // @phpstan-ignore-line
            $parent['font-stretch-val'],
        );

        $ref = self::REFUNITVAL;
        $ref['parent'] = $parent['font-size-val'];
        $ref['font']['rootsize'] = $parent['font-size-val'];
        $ref['font']['size'] = $parent['font-size-val'];
        $ref['font']['xheight'] = ($parent['font-size-val'] / 2);
        $ref['font']['zerowidth'] = ($parent['font-size-val'] / 3);
        $svgstyle['font-size-val'] = $this->getFontValuePoints(
            $svgstyle['font-size'],  // @phpstan-ignore-line
            $ref,
        );

        $svgstyle['font-mode'] = '';
        $svgstyle['font-mode'] .= $this->getTAFontWeight(
            $svgstyle['font-weight'], // @phpstan-ignore-line
        );
        $svgstyle['font-mode'] .= $this->getTAFontStyle(
            $svgstyle['font-style'], // @phpstan-ignore-line
        );
        $svgstyle['font-mode'] .= $this->getTAFontDecoration(
            $svgstyle['text-decoration'], // @phpstan-ignore-line
        );

        $fontmetric = $this->font->insert(
            $this->pon,
            $svgstyle['font-family'],
            $svgstyle['font-mode'],
            \intval($svgstyle['font-size-val']),
        );

        return $fontmetric['out'];
    }

    /**
     * Parse the SVG stroke style.
     *
     * @param int $soid SVG object ID.
     * @param TSVGStyle $svgstyle SVG style.
     *
     * @return string the Raw PDF command to set the stroke.
     */
    protected function parseSVGStyleStroke(
        int $soid,
        array &$svgstyle,
    ): string {
        if (empty($svgstyle['stroke']) || ($svgstyle['stroke'] == 'none')) {
            return '';
        }

        $strokestyle = $this->graph->getDefaultStyle();

        $col = $this->color->getColorObj($svgstyle['stroke']);
        if (empty($col)) {
            return '';
        }

        $out = '';

        if ($svgstyle['stroke-opacity'] < 1) {
            $out .= $this->graph->getAlpha($svgstyle['stroke-opacity']);
        } else {
            $rgba = $col->toRgbArray();
            if (isset($rgba['alpha']) && ($rgba['alpha'] < 1)) {
                $out .= $this->graph->getAlpha($rgba['alpha']);
            }
        }

        $ref = $this->svgobjs[$soid]['refunitval'];
        $ref['parent'] = 0;
        $strokestyle['lineWidth'] = $this->svgUnitToUnit(
            $svgstyle['stroke-width'],
            -1,
            $ref,
        );

        $strokestyle['lineCap'] = $svgstyle['stroke-linecap'];
        $strokestyle['lineJoin'] = $svgstyle['stroke-linejoin'];
        //  $strokestyle['miterLimit'] = (10.0 / $this->kunit),
        $strokestyle['dashArray'] = (
            empty($svgstyle['stroke-dasharray']) || ($svgstyle['stroke-dasharray'] == 'none')
        ) ? [] : \array_map(
            'intval',
            \explode(' ', $svgstyle['stroke-dasharray'], 100),
        );
        // $strokestyle['dashPhase'] = 0,
        $strokestyle['lineColor'] = $svgstyle['stroke'];
        unset($strokestyle['fillColor']);

        $out .= $this->graph->getStyleCmd($strokestyle);

        $objstyle = 'D';
        if (\strpos($svgstyle['objstyle'], $objstyle) === false) {
            $svgstyle['objstyle'] .= $objstyle; // @phpstan-ignore-line
        }

        return $out;
    }

    /**
     * Parse the SVG opacity, color and text-color styles.
     *
     * @param TSVGStyle $svgstyle SVG style.
     *
     * @return string the Raw PDF command to set the stroke.
     */
    protected function parseSVGStyleColor(
        array &$svgstyle,
    ): string {
        $out = '';

        if ($svgstyle['opacity'] < 1) {
            $out .= $this->graph->getAlpha($svgstyle['opacity']);
        }

        if (!empty($svgstyle['color'])) {
            $this->graph->add(['fillColor' => $svgstyle['color']], true);
        }

        if (!empty($svgstyle['text-color'])) {
            $out .= $this->color->getPdfColor($svgstyle['text-color']);
        }

        return $out;
    }

    /**
     * Parse the SVG clip style.
     *
     * @param TSVGStyle $svgstyle SVG style.
     * @param float $posx X position in user units.
     * @param float $posy Y position in user units.
     * @param float $width Width in user units.
     * @param float $height Height in user units.
     *
     * @return string the Raw PDF command to set the stroke.
     */
    protected function parseSVGStyleClip(
        array &$svgstyle,
        float $posx,
        float $posy,
        float $width,
        float $height,
    ): string {
        $regs = [];
        if (
            !\preg_match(
                '/rect\(([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)\)/si',
                $svgstyle['clip'],
                $regs
            )
        ) {
            return '';
        }

        $top = $this->toUnit(
            $regs[1]
            ? $this->svgUnitToPoints($regs[1])
            : 0.0
        );
        $right = $this->toUnit(
            $regs[2]
            ? $this->svgUnitToPoints($regs[2])
            : 0.0
        );
        $bottom = $this->toUnit(
            $regs[3]
            ? $this->svgUnitToPoints($regs[3])
            : 0.0
        );
        $left = $this->toUnit(
            $regs[4]
            ? $this->svgUnitToPoints($regs[4])
            : 0.0
        );

        $clx = $posx + $left;
        $cly = $posy + $top;
        $clw = $width - $left - $right;
        $clh = $height - $top - $bottom;
        $eoclip = ($svgstyle['clip-rule'] == 'evenodd');

        return $this->graph->getClippingRect(
            $clx,
            $cly,
            $clw,
            $clh,
            $eoclip,
        );
    }

    /**
     * Parse the SVG fill style.
     *
     * @parma int $soid SVG object ID.
     * @param array<string, TSVGGradient> $gradients Gradients.
     * @param string $xref Gradient ID.
     * @param float $grx X position in user units.
     * @param float $gry Y position in user units.
     * @param float $grw Width in user units.
     * @param float $grh Height in user units.
     * @param string $clip_fnc Optional clipping function name.
     * @param array<mixed> $clip_par Optional clipping function parameters.
     *
     * @return string the Raw PDF command.
     */
    protected function parseSVGStyleGradient(
        int $soid,
        array $gradients,
        string $xref,
        float $grx,
        float $gry,
        float $grw,
        float $grh,
        string $clip_fnc = '',
        array $clip_par = [],
    ): string {
        $gradient = $gradients[$xref] ?? null;
        if ($gradient === null) {
            return '';
        }
        if (!empty($gradient['xref'])) {
            // reference to another gradient definition
            $newgradient = $gradients[$gradient['xref']];
            $newgradient['coords'] = $gradient['coords'];
            $newgradient['mode'] = $gradient['mode'];
            $newgradient['type'] = $gradient['type'];
            $newgradient['gradientUnits'] = $gradient['gradientUnits'];
            if (isset($gradient['gradientTransform'])) {
                $newgradient['gradientTransform'] = $gradient['gradientTransform'];
            }
            $gradient = $newgradient;
        }

        $out = '';
        $out .= $this->graph->getStartTransform();

        if (!empty($clip_fnc)) {
            $bboxid_start = \array_key_last($this->bbox);
            $fnout = null;
            if (\method_exists($this, $clip_fnc)) {
                $fnout = $this->$clip_fnc(...$clip_par);
            } elseif (\method_exists($this->graph, $clip_fnc)) {
                $fnout = $this->graph->$clip_fnc(...$clip_par);
            }
            if (\is_string($fnout)) {
                $out .= $fnout;
            }
            $bboxid_last = \array_key_last($this->bbox);

            if (
                ($bboxid_last > $bboxid_start)
                && (!isset($gradient['type']) || ($gradient['type'] != 3))
            ) {
                $bbox = $this->bbox[$bboxid_last];
                $grx = \is_numeric($bbox['x']) ? (float)$bbox['x'] : 0.0;
                $gry = \is_numeric($bbox['y']) ? (float)$bbox['y'] : 0.0;
                $grw = \is_numeric($bbox['w']) ? (float)$bbox['w'] : 0.0;
                $grh = \is_numeric($bbox['h']) ? (float)$bbox['h'] : 0.0;
            }
        }

        switch ($gradient['mode']) {
            case 'percentage':
                foreach ($gradient['coords'] as $key => $val) {
                    $gradient['coords'][$key] = (\intval($val) / 100);
                    if ($val < 0) {
                        $gradient['coords'][$key] = 0;
                    } elseif ($val > 1) {
                        $gradient['coords'][$key] = 1;
                    }
                }
                break;
            case 'measure':
                if (!isset($gradient['coords'][4])) {
                    $gradient['coords'][4] = 0.5;
                }
                if (!empty($gradient['gradientTransform'])) {
                    $gtm = $gradient['gradientTransform'];
                    // apply transformation matrix
                    $gxa = ($gtm[0] * $gradient['coords'][0]) + ($gtm[2] * $gradient['coords'][1]) + $gtm[4];
                    $gya = ($gtm[1] * $gradient['coords'][0]) + ($gtm[3] * $gradient['coords'][1]) + $gtm[5];
                    $gxb = ($gtm[0] * $gradient['coords'][2]) + ($gtm[2] * $gradient['coords'][3]) + $gtm[4];
                    $gyb = ($gtm[1] * $gradient['coords'][2]) + ($gtm[3] * $gradient['coords'][3]) + $gtm[5];
                    $grr = \sqrt(\pow(
                        ($gtm[0] * $gradient['coords'][4]),
                        2
                    ) + \pow(
                        ($gtm[1] * $gradient['coords'][4]),
                        2
                    ));
                    $gradient['coords'][0] = $gxa;
                    $gradient['coords'][1] = $gya;
                    $gradient['coords'][2] = $gxb;
                    $gradient['coords'][3] = $gyb;
                    $gradient['coords'][4] = $grr;
                }
                // convert SVG coordinates to user units
                $gradient['coords'][0] = $this->svgUnitToUnit($gradient['coords'][0], $soid);
                $gradient['coords'][1] = $this->svgUnitToUnit($gradient['coords'][1], $soid);
                $gradient['coords'][2] = $this->svgUnitToUnit($gradient['coords'][2], $soid);
                $gradient['coords'][3] = $this->svgUnitToUnit($gradient['coords'][3], $soid);
                $gradient['coords'][4] = $this->svgUnitToUnit($gradient['coords'][4], $soid);
                if ($grw <= $this->svgminunitlen) {
                    $grw = $this->svgminunitlen;
                }
                if ($grh <= $this->svgminunitlen) {
                    $grh = $this->svgminunitlen;
                }
                // shift units
                if ($gradient['gradientUnits'] == 'objectBoundingBox') {
                    // convert to SVG coordinate system
                    $gradient['coords'][0] += $grx;
                    $gradient['coords'][1] += $gry;
                    $gradient['coords'][2] += $grx;
                    $gradient['coords'][3] += $gry;
                }
                // calculate percentages
                $gradient['coords'][0] = (($gradient['coords'][0] - $grx) / $grw);
                $gradient['coords'][1] = (($gradient['coords'][1] - $gry) / $grh);
                $gradient['coords'][2] = (($gradient['coords'][2] - $grx) / $grw);
                $gradient['coords'][3] = (($gradient['coords'][3] - $gry) / $grh);
                $gradient['coords'][4] /= $grw;
                break;
        }


        if (
            ($gradient['type'] == 2)
            && ($gradient['coords'][0] == $gradient['coords'][2])
            && ($gradient['coords'][1] == $gradient['coords'][3])
        ) {
            // single color (no shading)
            $gradient['coords'][0] = 1;
            $gradient['coords'][1] = 0;
            $gradient['coords'][2] = 0.999;
            $gradient['coords'][3] = 0;
        }

        // swap Y coordinates
        $tmp = $gradient['coords'][1];
        $gradient['coords'][1] = $gradient['coords'][3];
        $gradient['coords'][3] = $tmp;

        // set transformation map for gradient
        $gry = ($this->toUnit($this->svgobjs[$soid]['refunitval']['page']['height']) - $gry);
        if ($gradient['type'] == 3) {
            // circular gradient
            $gry -= ($gradient['coords'][1] * ($grw + $grh));
            $grh = $grw = \max($grw, $grh);
        } else {
            $gry -= $grh;
        }

        $out .= \sprintf(
            '%F 0 0 %F %F %F cm' . "\n",
            $this->toPoints($grw),
            $this->toPoints($grh),
            $this->toPoints($grx),
            $this->toPoints($gry),
        );

        if (\count($gradient['stops']) > 1) {
            $out .= $this->graph->getGradient(
                $gradient['type'],
                $gradient['coords'],
                $gradient['stops'],
                '',
                false,
            );
        }

        $out .= $this->graph->getStopTransform();

        return $out;
    }

    /**
     * Parse the SVG fill style.
     *
     * @param int $soid SVG object ID.
     * @param TSVGStyle $svgstyle SVG style.
     * @param array<string, TSVGGradient> $gradients Gradients.
     * @param float $posx X position in user units.
     * @param float $posy Y position in user units.
     * @param float $width Width in user units.
     * @param float $height Height in user units.
     * @param string $clip_fnc Optional clipping function name.
     * @param array<mixed> $clip_par Optional clipping function parameters.
     *
     * @return string the Raw PDF command.
     */
    protected function parseSVGStyleFill(
        int $soid,
        array &$svgstyle,
        array $gradients,
        float $posx,
        float $posy,
        float $width,
        float $height,
        string $clip_fnc = '',
        array $clip_par = [],
    ): string {
        if (empty($svgstyle['fill']) || ($svgstyle['fill'] == 'none')) {
            return '';
        }

        $regs = [];
        if (\preg_match('/url\([\s]*\#([^\)]*)\)/si', $svgstyle['fill'], $regs)) {
            return $this->parseSVGStyleGradient(
                $soid,
                $gradients,
                $regs[1],
                $posx,
                $posy,
                $width,
                $height,
                $clip_fnc,
                $clip_par,
            );
        }

        $col = $this->color->getColorObj($svgstyle['fill']);
        if ($col == null) {
            return '';
        }

        $out = '';

        if ($svgstyle['fill-opacity'] < 1) {
            $out .= $this->graph->getAlpha($svgstyle['fill-opacity']);
        } else {
            $rgba = $col->toRgbArray();
            if (isset($rgba['alpha']) && ($rgba['alpha'] < 1)) {
                $out .= $this->graph->getAlpha($rgba['alpha']);
            }
        }

        $objstyle = ($svgstyle['fill-rule'] == 'evenodd') ? 'F*' : 'F';
        if (\strpos($svgstyle['objstyle'], $objstyle) === false) {
            $svgstyle['objstyle'] .= $objstyle; // @phpstan-ignore-line
        }

        $out .= $col->getPdfColor();

        return $out;
    }

    /**
     * Parse the SVG style clip-path.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid SVG object ID.
     * @param array<string, TSVGAttribs> $clippaths Clipping paths.
     */
    protected function parseSVGStyleClipPath(
        \XMLParser $parser,
        int $soid,
        array $clippaths = [],
    ): void {
        foreach ($clippaths as $cp) {
            $this->handleSVGTagStart(
                $parser,
                $cp['name'],
                $cp['attr'],
                $soid,
                true,
                $cp['tm'] ?? self::TMXID,
            );
        }
    }

    /**
     * Parse the SVG style.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid SVG object ID.
     * @param TSVGStyle $svgstyle SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     * @param float $posx X position in user units.
     * @param float $posy Y position in user units.
     * @param float $width Width in user units.
     * @param float $height Height in user units.
     * @param string $objstyle Style to return.
     * @param string $clip_fnc Optional clipping function name.
     * @param array<mixed> $clip_par Optional clipping function parameters.
     *
     * @return string the object style.
     */
    protected function parseSVGStyle(
        \XMLParser $parser,
        int $soid,
        array $svgstyle,
        array $prev_svgstyle,
        float $posx = 0,
        float $posy = 0,
        float $width = 1,
        float $height = 1,
        string &$objstyle = '',
        string $clip_fnc = '',
        array $clip_par = [],
    ): string {
        if (empty($svgstyle['opacity'])) {
            return '';
        }

        $this->parseSVGStyleClipPath($parser, $soid, $this->svgobjs[$soid]['clippaths']);

        $out = '';
        $out .= $this->parseSVGStyleColor($svgstyle);
        $out .= $this->parseSVGStyleClip(
            $svgstyle,
            $posx,
            $posy,
            $width,
            $height
        );
        $out .= $this->parseSVGStyleFill(
            $soid,
            $svgstyle,
            $this->svgobjs[$soid]['gradients'],
            $posx,
            $posy,
            $width,
            $height,
            $clip_fnc,
            $clip_par
        );
        $out .= $this->parseSVGStyleStroke($soid, $svgstyle);
        $out .= $this->parseSVGStyleFont(
            $svgstyle,
            $prev_svgstyle,
        );

        $objstyle = $svgstyle['objstyle'];

        return $out;
    }

    /**
     * Handler for the SVG character data.
     *
     * @param \XMLParser $parser The XML parser calling the handler.
     * @param string $data Character data.
     *
     * @return void
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function handlerSVGCharacter(
        \XMLParser $parser,
        string $data,
    ) {
        $soid = (int)\array_key_last($this->svgobjs);
        if (($soid < 0) || !isset($this->svgobjs[$soid]['text'])) {
            return;
        }
        // @phpstan-ignore assign.propertyType
        $this->svgobjs[$soid]['text'] .= $data;
    }

    /**
     * Handler for the end of an SVG tag.
     *
     * @param \XMLParser $parser The XML parser calling the handler.
     * @param string $name Name of the element for which this handler is called.
     *
     * @return void
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function handleSVGTagEnd(
        \XMLParser $parser,
        string $name,
    ): void {
        $name = $this->removeTagNamespace($name);

        $soid = (int)\array_key_last($this->svgobjs);
        if ($soid < 0) {
            return;
        }

        if (
            $this->svgobjs[$soid]['defsmode']
            && !\in_array($name, self::SVGDEFSMODEEND)
        ) {
            if (\end($this->svgobjs[$soid]['defs']) !== false) {
                $last_svgdefs_id = (string)\array_key_last($this->svgobjs[$soid]['defs']);
                if (!empty($this->svgobjs[$soid]['defs'][$last_svgdefs_id]['child'])) {
                    foreach (
                        $this->svgobjs[$soid]['defs'][$last_svgdefs_id]['child'] as $child
                    ) {
                        if (
                            isset($child['attr']['id']) &&
                            \is_scalar($child['attr']['id']) &&
                            ($child['name'] == $name)
                        ) {
                            // @phpstan-ignore assign.propertyType
                            $closeKey = (string)$child['attr']['id'] . '_CLOSE';
                            // @phpstan-ignore assign.propertyType
                            $this->svgobjs[$soid]['defs'][$last_svgdefs_id]['child'][$closeKey] = [
                                'name' => $name,
                                'attr' => [
                                    'closing_tag' => true,
                                    'content' => $this->svgobjs[$soid]['text'],
                                ],
                            ];
                            return;
                        }
                    }
                    if ($this->svgobjs[$soid]['defs'][$last_svgdefs_id]['name'] == $name) {
                        $closeKey = (string)$last_svgdefs_id . '_CLOSE';
                        // @phpstan-ignore assign.propertyType
                        $this->svgobjs[$soid]['defs'][$last_svgdefs_id]['child'][$closeKey] = [
                            'name' => $name,
                            'attr' => [
                                'closing_tag' => true,
                                'content' => $this->svgobjs[$soid]['text'],
                            ],
                        ];
                        return;
                    }
                }
            }
            return;
        }

        // @phpstan-ignore assign.propertyType
        $this->svgobjs[$soid]['out'] .= match ($name) {
            'defs' => $this->parseSVGTagENDdefs($soid),
            'clipPath' => $this->parseSVGTagENDclipPath($soid),
            'svg' => $this->parseSVGTagENDsvg($soid),
            'g' => $this->parseSVGTagENDg($soid),
            'text' => $this->parseSVGTagENDtext($soid),
            'tspan' => $this->parseSVGTagENDtspan($soid),
            default => null,
        };
    }

    /**
     * Parse the SVG End tag 'defs'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string
     */
    protected function parseSVGTagENDdefs(int $soid): string
    {
        // @phpstan-ignore assign.propertyType
        $this->svgobjs[$soid]['defsmode'] = false;
        return '';
    }

    /**
     * Parse the SVG End tag 'clipPath'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string
     */
    protected function parseSVGTagENDclipPath(int $soid): string
    {
        // @phpstan-ignore assign.propertyType
        $this->svgobjs[$soid]['clipmode'] = false;
        return '';
    }

    /**
     * Parse the SVG End tag 'svg'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string
     */
    protected function parseSVGTagENDsvg(int $soid): string
    {
        // @phpstan-ignore assign.propertyType
        if (--$this->svgobjs[$soid]['tagdepth'] <= 0) {
            return '';
        }
        return $this->parseSVGTagENDg($soid);
    }

    /**
     * Parse the SVG End tag 'g'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string
     */
    protected function parseSVGTagENDg(int $soid): string
    {
        // @phpstan-ignore assign.propertyType
        \array_pop($this->svgobjs[$soid]['styles']);
        return $this->graph->getStopTransform();
    }

    /**
     * Parse the SVG End tag 'tspan'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string
     */
    protected function parseSVGTagENDtspan(int $soid): string
    {
        return $this->parseSVGTagENDtext($soid);
    }

    /**
     * Parse the SVG End tag 'text'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string
     */
    protected function parseSVGTagENDtext(int $soid): string
    {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            // @TODO : This implementation must be fixed to following the rule:
            // If the 'visibility' property is set to hidden on a 'tspan', 'tref' or 'altGlyph' element,
            // then the text is invisible but still takes up space in text layout calculations.
            return '';
        }

        $curx = $this->svgobjs[$soid]['x'];
        $cury = $this->svgobjs[$soid]['y'];

        $anchor = $this->svgobjs[$soid]['textmode']['text-anchor'] ?? 'start';
        $txtanchor = match ($anchor) {
            'end' => 'E',
            'middle' => 'M',
            default => 'S',
        };

        $out = '';

        $out .= $this->getTextLine(
            $this->svgobjs[$soid]['text'],
            $curx,
            $cury,
            0,
            $this->svgobjs[$soid]['textmode']['stroke'],
            0,
            0,
            0,
            true,
            ($this->svgobjs[$soid]['textmode']['stroke'] > 0),
            false,
            false,
            false,
            false,
            ($this->svgobjs[$soid]['textmode']['rtl'] ? 'R' : ''),
            $txtanchor,
            null, //?array $shadow = null,
        );

        // @phpstan-ignore assign.propertyType
        $this->svgobjs[$soid]['text'] = ''; // reset text buffer
        $out .= $this->graph->getStopTransform();

        if (!$this->svgobjs[$soid]['defsmode']) {
            // @phpstan-ignore assign.propertyType
            \array_pop($this->svgobjs[$soid]['styles']);
        }

        return $out;
    }

    /**
     * Handler for the start of an SVG tag.
     *
     * @param \XMLParser $parser The XML parser calling the handler.
     * @param string $name Name of the element for which this handler is called.
     * @param TSVGAttributes $attr Associative array with the element's attributes.
     * @param int $soid ID of the current SVG object.
     * @param bool $clipmode Clip-path mode (optional).
     * @param TTMatrix $ctm Current transformation matrix (optional).
     *
     * @return void
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function handleSVGTagStart(
        \XMLParser $parser,
        string $name,
        array $attr,
        int $soid = -1,
        bool $clipmode = false,
        array $ctm = self::TMXID, // identity matrix
    ): void {
        if ($soid < 0) {
            $soid = (int)\array_key_last($this->svgobjs);
        }
        if (empty($this->svgobjs[$soid])) {
            return;
        }

        $name = $this->removeTagNamespace($name);

        if ($this->svgobjs[$soid]['clipmode']) {
            // @phpstan-ignore assign.propertyType
            $this->svgobjs[$soid]['clippaths'][] = [
                'name' => $name,
                'attr' => $attr,
                'tm' => $this->svgobjs[$soid]['cliptm'],
            ];
            return;
        }

        if (
            $this->svgobjs[$soid]['defsmode']
            && !\in_array($name, self::SVGDEFSMODESTART)
        ) {
            if (!isset($this->svgobjs[$soid]['clippaths'])) {
                $this->svgobjs[$soid]['clippaths'] = [];
            }

            if (isset($attr['id'])) {
                $this->svgobjs[$soid]['defs'][$attr['id']] = [
                    'name' => $name,
                    'attr' => $attr,
                ];
                return;
            }

            if (\end($this->svgobjs[$soid]['defs']) !== false) {
                $last_svgdefs_id = \key($this->svgobjs[$soid]['defs']);
                if (
                    !empty($this->svgobjs[$soid]['defs'][$last_svgdefs_id]['child'])
                    && \is_array($this->svgobjs[$soid]['defs'][$last_svgdefs_id]['child'])
                ) {
                    $attr['id'] = 'DF_' .
                    (\count($this->svgobjs[$soid]['defs'][$last_svgdefs_id]['child']) + 1);
                    $this->svgobjs[$soid]['defs'][$last_svgdefs_id]['child'][$attr['id']] = [
                        'name' => $name,
                        'attr' => $attr
                    ];
                    return;
                }
            }

            return;
        }

        $this->svgobjs[$soid]['clipmode'] = $clipmode;

        // default style
        $svgstyle = (array) $this->svgobjs[$soid]['styles'][0];

        // last style
        $sid = (int)\array_key_last($this->svgobjs[$soid]['styles']);
        $psid = \max(0, $sid - 1);
        $prev_svgstyle = (array) $this->svgobjs[$soid]['styles'][$psid];

        if (
            $this->svgobjs[$soid]['clipmode'] &&
            !isset($attr['fill']) &&
            (!isset($attr['style']) ||
            (!\preg_match('/[;\"\s]{1}fill[\s]*:[\s]*([^;\"]*)/si', $attr['style'], $attrval)))
        ) {
            // default fill attribute for clipping
            $attr['fill'] = 'none';
        }

        if (
            isset($attr['style']) &&
            !empty($attr['style']) &&
            ($attr['style'][0] != ';')
        ) {
            // fix style for regular expression
            $attr['style'] = ';' . $attr['style'];
        }

        foreach ($prev_svgstyle as $key => $val) {
            if (\in_array($key, self::SVGINHPROP)) {
                // inherit previous value
                $svgstyle[$key] = $val;
            }
            if (!empty($attr[$key])) {
                // specific attribute settings
                if ($attr[$key] == 'inherit') {
                    $svgstyle[$key] = $val;
                } else {
                    $svgstyle[$key] = $attr[$key];
                }
            } elseif (!empty($attr['style'])) {
                // CSS style syntax
                $attrval = [];
                if (
                    \preg_match(
                        '/[;\"\s]{1}' . $key . '[\s]*:[\s]*([^;\"]*)/si',
                        $attr['style'],
                        $attrval
                    )
                ) {
                    if ($attrval[1] == 'inherit') {
                        $svgstyle[$key] = $val;
                    } else {
                        $svgstyle[$key] = $attrval[1];
                    }
                }
            }
        }

        $tmx = $ctm;
        if (!empty($attr['transform'])) {
            $tmx = $this->graph->getCtmProduct($tmx, $this->getSVGTransformMatrix($attr['transform']));
        }

        $svgstyle['transfmatrix'] = $tmx;

        $this->svgobjs[$soid]['textmode']['invisible'] = (
            ($svgstyle['visibility'] == 'hidden') ||
            ($svgstyle['visibility'] == 'collapse') ||
            ($svgstyle['display'] == 'none'));

        // push new style
        //$this->svgobjs[$soid]['styles'][] = $svgstyle;

        /** @var TSVGStyle $svgstyle */
        $svgstyle = (array) $svgstyle;

        // process tags
        // @phpstan-ignore assign.propertyType
        $this->svgobjs[$soid]['out'] .= match ($name) {
            'defs' => $this->parseSVGTagSTARTdefs($soid),
            'clipPath' => $this->parseSVGTagSTARTclipPath($soid, $tmx),
            'svg' => $this->parseSVGTagSTARTsvg($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'g' => $this->parseSVGTagSTARTg($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'linearGradient' => $this->parseSVGTagSTARTlinearGradient($soid, $attr),
            'radialGradient' => $this->parseSVGTagSTARTradialGradient($soid, $attr),
            'stop' => $this->parseSVGTagSTARTstop($soid, $attr, $svgstyle),
            'path' => $this->parseSVGTagSTARTpath($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'rect' => $this->parseSVGTagSTARTrect($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'circle' => $this->parseSVGTagSTARTcircle($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'ellipse' => $this->parseSVGTagSTARTellipse($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'line' => $this->parseSVGTagSTARTline($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'polyline' => $this->parseSVGTagSTARTpolygon($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'polygon' => $this->parseSVGTagSTARTpolygon($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'image' => $this->parseSVGTagSTARTimage($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'text' => $this->parseSVGTagSTARTtext($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'tspan' => $this->parseSVGTagSTARTtspan($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'use' => $this->parseSVGTagSTARTuse($parser, $soid, $attr),
            default => null,
        };
    }

    /**
     * Parse the SVG Start tag 'defs'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string
     */
    protected function parseSVGTagSTARTdefs(int $soid): string
    {
        // @phpstan-ignore assign.propertyType
        $this->svgobjs[$soid]['defsmode'] = true;
        return '';
    }

    /**
     * Parse the SVG Start tag 'clipPath'.
     *
     * @param int $soid ID of the current SVG object.
     * @param array<float> $tmx Current transformation matrix (optional).
     *
     * @return string
     */
    protected function parseSVGTagSTARTclipPath(int $soid, array $tmx = []): string
    {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return '';
        }

        // @phpstan-ignore assign.propertyType
        $this->svgobjs[$soid]['clipmode'] = true;

        if (empty($this->svgobjs[$soid]['clipid'])) {
            $this->svgobjs[$soid]['clipid'] = 'CP_' . (\count($this->svgobjs[$soid]['cliptm']) + 1);
        }

        $cid = $this->svgobjs[$soid]['clipid'];
        $this->svgobjs[$soid]['clippaths'][$cid] = [];
        $this->svgobjs[$soid]['cliptm'][$cid] = $tmx;
        return '';
    }

    /**
     * Parse the SVG Start tag 'svg'.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     *
     * @return string
     */
    protected function parseSVGTagSTARTsvg(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        // @phpstan-ignore assign.propertyType
        $this->svgobjs[$soid]['tagdepth']++;
        if ($this->svgobjs[$soid]['tagdepth'] <= 1) {
            // root SVG
            return '';
        }
        // inner SVG
        $out = '';
        \array_push($this->svgobjs[$soid]['styles'], $svgstyle);
        $out .= $this->graph->getStartTransform();
        $svgX = isset($attr['x']) ? $this->svgUnitToUnit($attr['x'], $soid) : 0.0;
        $svgY = isset($attr['y']) ? $this->svgUnitToUnit($attr['y'], $soid) : 0.0;
        $svgW = isset($attr['width']) ? $this->svgUnitToUnit($attr['width'], $soid) : 0.0;
        $svgH = isset($attr['height']) ? $this->svgUnitToUnit($attr['height'], $soid) : 0.0;
        // set x, y position using transform matrix
        $tmx = $this->graph->getCtmProduct($svgstyle['transfmatrix'], [1.0, 0.0, 0.0, 1.0, $svgX, $svgY]);
        $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
        // set clipping for width and height
        $page = $this->page->getPage();
        $posx = 0;
        $posy = 0;
        $width = empty($svgW) ? ($page['width'] - $svgX) : $svgW;
        $height = empty($svgH) ? ($page['height'] - $svgY) : $svgH;
        // draw clipping rect
        $out .=  $this->graph->getRawRect(
            $posx,
            $posy,
            $width,
            $height,
            'CNZ',
        );
        $out .= $this->parseSVGStyle(
            $parser,
            $soid,
            $svgstyle,
            $prev_svgstyle,
            $posx,
            $posy,
            $width,
            $height,
        );
        // parse viewbox, calculate extra transformation matrix
        if (empty($attr['viewBox'])) {
            $out .= $this->parseSVGStyle(
                $parser,
                $soid,
                $svgstyle,
                $prev_svgstyle,
                $posx,
                $posy,
                $width,
                $height,
            );
            return $out;
        }
        $tmp = [];
        \preg_match_all("/[0-9]+/", $attr['viewBox'], $tmp);
        $tmp = $tmp[0];
        if (\sizeof($tmp) != 4) {
            $out .= $this->parseSVGStyle(
                $parser,
                $soid,
                $svgstyle,
                $prev_svgstyle,
                $posx,
                $posy,
                $width,
                $height,
            );
            return $out;
        }
        $vbx = \floatval($tmp[0]);
        $vby = \floatval($tmp[1]);
        $vbw = \floatval($tmp[2]);
        $vbh = \floatval($tmp[3]);
        // get aspect ratio
        $tmp = [];
        $aspectX = 'xMid';
        $aspectY = 'YMid';
        $fit = 'meet';
        if (!empty($attr['preserveAspectRatio'])) {
            if ($attr['preserveAspectRatio'] == 'none') {
                $fit = 'none';
            } else {
                \preg_match_all('/[a-zA-Z]+/', $attr['preserveAspectRatio'], $tmp);
                $tmp = $tmp[0];
                if (
                    (\sizeof($tmp) == 2)
                    && (\strlen($tmp[0]) == 8)
                    && (\in_array(
                        $tmp[1],
                        array('meet', 'slice', 'none')
                    ))
                ) {
                    $aspectX = \substr($tmp[0], 0, 4);
                    $aspectY = \substr($tmp[0], 4, 4);
                    $fit = $tmp[1];
                }
            }
        }
        $wsr = ($svgW / $vbw);
        $hsr = ($svgH / $vbh);
        $asx = $asy = 0;
        if ((($fit == 'meet') && ($hsr < $wsr)) || (($fit == 'slice') && ($hsr > $wsr))) {
            if ($aspectX == 'xMax') {
                $asx = (($vbw * ($wsr / $hsr)) - $vbw);
            }
            if ($aspectX == 'xMid') {
                $asx = ((($vbw * ($wsr / $hsr)) - $vbw) / 2);
            }
            $wsr = $hsr;
        } elseif ((($fit == 'meet') && ($hsr > $wsr)) || (($fit == 'slice') && ($hsr < $wsr))) {
            if ($aspectY == 'YMax') {
                $asy = (($vbh * ($hsr / $wsr)) - $vbh);
            }
            if ($aspectY == 'YMid') {
                $asy = ((($vbh * ($hsr / $wsr)) - $vbh) / 2);
            }
            $hsr = $wsr;
        }
        $newtmx = [$wsr, 0.0, 0.0, $hsr, (($wsr * ($asx - $vbx)) - $svgX), (($hsr * ($asy - $vby)) - $svgY)];
        $tmx = $this->graph->getCtmProduct($tmx, $newtmx);
        $out .= $this->getOutSVGTransformation($tmx, $soid);
        $out .= $this->parseSVGStyle(
            $parser,
            $soid,
            $svgstyle,
            $prev_svgstyle,
            $posx,
            $posy,
            $width,
            $height,
        );

        return $out;
    }

    /**
     * Parse the SVG Start tag 'g'.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     *
     * @return string
     */
    protected function parseSVGTagSTARTg(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        $out = '';
        // @phpstan-ignore assign.propertyType
        \array_push($this->svgobjs[$soid]['styles'], $svgstyle);
        $out .= $this->graph->getStartTransform();
        $posx = isset($attr['x']) ? $this->svgUnitToUnit($attr['x'], $soid) : 0.0;
        $posy = isset($attr['y']) ? $this->svgUnitToUnit($attr['y'], $soid) : 0.0;
        $width = 1.0; // isset($attr['width']) ? $this->svgUnitToUnit($attr['width'], $soid) : 1.0;
        $height = 1.0; // isset($attr['height']) ? $this->svgUnitToUnit($attr['height'], $soid) : 1.0;
        $tmx = $this->graph->getCtmProduct(
            $svgstyle['transfmatrix'],
            [$width, 0.0, 0.0, $height, $posx, $posy]
        );
        $out .= $this->getOutSVGTransformation($tmx, $soid);
        $out .= $this->parseSVGStyle(
            $parser,
            $soid,
            $svgstyle,
            $prev_svgstyle,
            $posx,
            $posy,
            $width,
            $height,
        );

        return $out;
    }

    /**
     * Parse the SVG Start tag 'linearGradient'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     *
     * @return string
     */
    protected function parseSVGTagSTARTlinearGradient(int $soid, array $attr): string
    {
        if (($this->pdfa == 1) || ($this->pdfa == 2)) {
            return '';
        }

        if (!isset($attr['id'])) {
            $attr['id'] = 'GR_' . (\count($this->svgobjs[$soid]['gradients']) + 1);
        }
        $gid = $attr['id'];
        // @phpstan-ignore assign.propertyType
        $this->svgobjs[$soid]['gradientid'] = $gid;
        $this->svgobjs[$soid]['gradients'][$gid] = [];
        $this->svgobjs[$soid]['gradients'][$gid]['type'] = 2;
        $this->svgobjs[$soid]['gradients'][$gid]['stops'] = [];
        if (isset($attr['gradientUnits'])) {
            $this->svgobjs[$soid]['gradients'][$gid]['gradientUnits'] = $attr['gradientUnits'];
        } else {
            $this->svgobjs[$soid]['gradients'][$gid]['gradientUnits'] = 'objectBoundingBox';
        }
        // $attr['spreadMethod']
        if (
            ((!isset($attr['x1'])) && (!isset($attr['y1']))
            && (!isset($attr['x2'])) && (!isset($attr['y2'])))
            || ((isset($attr['x1']) && (\substr($attr['x1'], -1) == '%'))
            || (isset($attr['y1']) && (\substr($attr['y1'], -1) == '%'))
            || (isset($attr['x2']) && (\substr($attr['x2'], -1) == '%'))
            || (isset($attr['y2']) && (\substr($attr['y2'], -1) == '%')))
        ) {
            $this->svgobjs[$soid]['gradients'][$gid]['mode'] = 'percentage';
        } else {
            $this->svgobjs[$soid]['gradients'][$gid]['mode'] = 'measure';
        }
        $px1 = $attr['x1'] ?? 0.0;
        $py1 = $attr['y1'] ?? 0.0;
        $px2 = $attr['x2'] ?? 100.0;
        $py2 = $attr['y2'] ?? 0.0;
        if (isset($attr['gradientTransform'])) {
            $this->svgobjs[$soid]['gradients'][$gid]['gradientTransform'] =
                $this->getSVGTransformMatrix($attr['gradientTransform']);
        }
        $this->svgobjs[$soid]['gradients'][$gid]['coords'] = [$px1, $py1, $px2, $py2];
        if (!empty($attr['xlink:href'])) {
            // gradient is defined on another place
            $this->svgobjs[$soid]['gradients'][$gid]['xref'] = \substr($attr['xlink:href'], 1);
        }
        return '';
    }

    /**
     * Parse the SVG Start tag 'radialGradient'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     *
     * @return string
     */
    protected function parseSVGTagSTARTradialGradient(int $soid, array $attr): string
    {
        if (($this->pdfa == 1) || ($this->pdfa == 2)) {
            return '';
        }

        if (!isset($attr['id'])) {
            $attr['id'] = 'GR_' . (\count($this->svgobjs[$soid]['gradients']) + 1);
        }
        $gid = $attr['id'];
        // @phpstan-ignore assign.propertyType
        $this->svgobjs[$soid]['gradientid'] = $gid;
        $this->svgobjs[$soid]['gradients'][$gid] = [];
        $this->svgobjs[$soid]['gradients'][$gid]['type'] = 3;
        $this->svgobjs[$soid]['gradients'][$gid]['stops'] = [];
        if (isset($attr['gradientUnits'])) {
            $this->svgobjs[$soid]['gradients'][$gid]['gradientUnits'] = $attr['gradientUnits'];
        } else {
            $this->svgobjs[$soid]['gradients'][$gid]['gradientUnits'] = 'objectBoundingBox';
        }
        // $attr['spreadMethod']
        if (
            ((!isset($attr['cx'])) && (!isset($attr['cy'])))
            || ((isset($attr['cx']) && (\substr($attr['cx'], -1) == '%'))
            || (isset($attr['cy']) && (\substr($attr['cy'], -1) == '%')))
        ) {
            $this->svgobjs[$soid]['gradients'][$gid]['mode'] = 'percentage';
        } elseif (isset($attr['r']) && \is_numeric($attr['r']) && ($attr['r']) <= 1) {
            $this->svgobjs[$soid]['gradients'][$gid]['mode'] = 'ratio';
        } else {
            $this->svgobjs[$soid]['gradients'][$gid]['mode'] = 'measure';
        }
        $pcx = $attr['cx'] ?? 0.5;
        $pcy = $attr['cy'] ?? 0.5;
        $pfx = $attr['fx'] ?? $pcx;
        $pfy = $attr['fy'] ?? $pcy;
        $grr = $attr['r'] ?? 0.5;
        if (isset($attr['gradientTransform'])) {
            $this->svgobjs[$soid]['gradients'][$gid]['gradientTransform'] =
                $this->getSVGTransformMatrix($attr['gradientTransform']);
        }
        $this->svgobjs[$soid]['gradients'][$gid]['coords'] = [$pcx, $pcy, $pfx, $pfy, $grr];
        if (!empty($attr['xlink:href'])) {
            // gradient is defined on another place
            $this->svgobjs[$soid]['gradients'][$gid]['xref'] = \substr($attr['xlink:href'], 1);
        }
        return '';
    }

    /**
     * Parse the SVG Start tag 'stop'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     *
     * @return string
     */
    protected function parseSVGTagSTARTstop(
        int $soid,
        array $attr,
        array $svgstyle,
    ): string {
        $offset = isset($attr['offset']) ? $this->svgUnitToUnit($attr['offset'], $soid) : 0.0;
        $stop_color = $svgstyle['stop-color'] ?? 'black';
        $opacity = isset($svgstyle['stop-opacity']) ? \max(
            0.0,
            \min(
                1.0,
                \floatval($svgstyle['stop-opacity'])
            )
        ) : 1.0;
        $gid = $this->svgobjs[$soid]['gradientid'];
        // @phpstan-ignore assign.propertyType
        $this->svgobjs[$soid]['gradients'][$gid]['stops'][] = [
            'offset' => $offset,
            'color' => $stop_color,
            'opacity' => $opacity,
        ];
        return '';
    }

    /**
     * Parse the SVG Start tag 'path'.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     *
     * @return string
     */
    protected function parseSVGTagSTARTpath(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return '';
        }
        if (empty($attr['d'])) {
            return '';
        }

        $ptd = \trim($attr['d']);

        $posx = isset($attr['x']) ? $this->svgUnitToUnit($attr['x'], $soid) : 0.0;
        $posy = isset($attr['y']) ? $this->svgUnitToUnit($attr['y'], $soid) : 0.0;
        $width = isset($attr['width']) ? $this->svgUnitToUnit($attr['width'], $soid) : 1.0;
        $height = isset($attr['height']) ? $this->svgUnitToUnit($attr['height'], $soid) : 1.0;
        $tmx = $this->graph->getCtmProduct(
            $svgstyle['transfmatrix'],
            [$width, 0.0, 0.0, $height, $posx, $posy]
        );

        $out = '';

        if ($this->svgobjs[$soid]['clipmode']) {
            $out .= $this->getOutSVGTransformation($tmx, $soid);
            $out .= $this->getSVGPath($soid, $ptd, 'CNZ');
            return $out;
        }

        $out .= $this->graph->getStartTransform();
        $out .= $this->getOutSVGTransformation($tmx, $soid);
        $obstyle = '';
        $out .= $this->parseSVGStyle(
            $parser,
            $soid,
            $svgstyle,
            $prev_svgstyle,
            $posx,
            $posy,
            $width,
            $height,
            $obstyle,
            'getSVGPath',
            [$soid, $ptd, 'CNZ'],
        );

        if (!empty($obstyle)) {
            $out .= $this->getSVGPath($soid, $ptd, $obstyle);
        }

        $out .= $this->graph->getStopTransform();

        return $out;
    }

    /**
     * Parse the SVG Start tag 'rect'.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     *
     * @return string
     */
    protected function parseSVGTagSTARTrect(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return '';
        }
        $posx = (isset($attr['x']) ? $this->svgUnitToUnit($attr['x'], $soid) : 0.0);
        $posy = (isset($attr['y']) ? $this->svgUnitToUnit($attr['y'], $soid) : 0.0);
        $width = (isset($attr['width']) ? $this->svgUnitToUnit($attr['width'], $soid) : 0.0);
        $height = (isset($attr['height']) ? $this->svgUnitToUnit($attr['height'], $soid) : 0.0);
        $prx = (isset($attr['rx']) ? $this->svgUnitToUnit($attr['rx'], $soid) : 0.0);
        $pry = (isset($attr['ry']) ? $this->svgUnitToUnit($attr['ry'], $soid) : $prx);
        $out = '';
        if ($this->svgobjs[$soid]['clipmode']) {
            $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
            $out .= $this->graph->getRoundedRect(
                $posx,
                $posy,
                $width,
                $height,
                $prx,
                $pry,
                '1111',
                'CNZ',
            );
            return $out;
        }
        $out .= $this->graph->getStartTransform();
        $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
        $obstyle = '';
        $out .= $this->parseSVGStyle(
            $parser,
            $soid,
            $svgstyle,
            $prev_svgstyle,
            $posx,
            $posy,
            $width,
            $height,
            $obstyle,
            'getRoundedRect',
            [$posx, $posy, $width, $height, $prx, $pry, '1111', 'CNZ'],
        );
        if (!empty($obstyle)) {
            $out .= $this->graph->getRoundedRect(
                $posx,
                $posy,
                $width,
                $height,
                $prx,
                $pry,
                '1111',
                $obstyle,
            );
        }

        $out .= $this->graph->getStopTransform();

        return $out;
    }

    /**
     * Parse the SVG Start tag 'circle'.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     *
     * @return string
     */
    protected function parseSVGTagSTARTcircle(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return '';
        }
        $crr = (isset($attr['r']) ? $this->svgUnitToUnit($attr['r'], $soid) : 0.0);
        $ctx = (isset($attr['cx']) ? $this->svgUnitToUnit(
            $attr['cx'],
            $soid,
        ) : (isset($attr['x']) ? $this->svgUnitToUnit(
            $attr['x'],
            $soid,
        ) : 0.0));
        $cty = (isset($attr['cy']) ? $this->svgUnitToUnit(
            $attr['cy'],
            $soid,
        ) : (isset($attr['y']) ? $this->svgUnitToUnit(
            $attr['y'],
            $soid,
        ) : 0.0));
        $posx = ($ctx - $crr);
        $posy = ($cty - $crr);
        $width = (2 * $crr);
        $height = $width;
        $out = '';
        if ($this->svgobjs[$soid]['clipmode']) {
            $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
            $out .= $this->graph->getCircle(
                $ctx,
                $cty,
                $crr,
                0,
                360,
                'CNZ',
                [],
                8
            );
            return $out;
        }
        $out .= $this->graph->getStartTransform();
        $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
        $obstyle = '';
        $out .= $this->parseSVGStyle(
            $parser,
            $soid,
            $svgstyle,
            $prev_svgstyle,
            $posx,
            $posy,
            $width,
            $height,
            $obstyle,
            'getCircle',
            [$ctx, $cty, $crr, 0, 360, 'CNZ'],
        );
        if (!empty($obstyle)) {
            $out .= $this->graph->getCircle(
                $ctx,
                $cty,
                $crr,
                0,
                360,
                $obstyle,
                [],
                8
            );
        }
        $out .= $this->graph->getStopTransform();
        return $out;
    }

    /**
     * Parse the SVG Start tag 'ellipse'.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     *
     * @return string
     */
    protected function parseSVGTagSTARTellipse(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return '';
        }
        $erx = (isset($attr['rx']) ? $this->svgUnitToUnit($attr['rx'], $soid) : 0.0);
        $ery = (isset($attr['ry']) ? $this->svgUnitToUnit($attr['ry'], $soid) : 0.0);
        $ecx = (isset($attr['cx']) ? $this->svgUnitToUnit(
            $attr['cx'],
            $soid,
        ) : (isset($attr['x']) ? $this->svgUnitToUnit(
            $attr['x'],
            $soid,
        ) : 0.0));
        $ecy = (isset($attr['cy']) ? $this->svgUnitToUnit(
            $attr['cy'],
            $soid,
        ) : (isset($attr['y']) ? $this->svgUnitToUnit(
            $attr['y'],
            $soid,
        ) : 0.0));
        $posx = ($ecx - $erx);
        $posy = ($ecy - $ery);
        $width = (2 * $erx);
        $height = (2 * $ery);
        $out = '';
        if ($this->svgobjs[$soid]['clipmode']) {
            $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
            $out .= $this->graph->getEllipse(
                $ecx,
                $ecy,
                $erx,
                $ery,
                0,
                0,
                360,
                'CNZ',
                [],
                8
            );
            return $out;
        }
        $out .= $this->graph->getStartTransform();
        $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
        $obstyle = '';
        $out .= $this->parseSVGStyle(
            $parser,
            $soid,
            $svgstyle,
            $prev_svgstyle,
            $posx,
            $posy,
            $width,
            $height,
            $obstyle,
            'getEllipse',
            [$ecx, $ecy, $erx, $ery, 0, 0, 360, 'CNZ'],
        );
        if (!empty($obstyle)) {
            $out .= $this->graph->getEllipse(
                $ecx,
                $ecy,
                $erx,
                $ery,
                0,
                0,
                360,
                $obstyle,
                [],
                8
            );
        }
        $out .= $this->graph->getStopTransform();

        return $out;
    }

    /**
     * Parse the SVG Start tag 'line'.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     *
     * @return string
     */
    protected function parseSVGTagSTARTline(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return '';
        }
        if ($this->svgobjs[$soid]['clipmode']) {
            return '';
        }
        $posx1 = (isset($attr['x1']) ? $this->svgUnitToUnit($attr['x1'], $soid) : 0.0);
        $posy1 = (isset($attr['y1']) ? $this->svgUnitToUnit($attr['y1'], $soid) : 0.0);
        $posx2 = (isset($attr['x2']) ? $this->svgUnitToUnit($attr['x2'], $soid) : 0.0);
        $posy2 = (isset($attr['y2']) ? $this->svgUnitToUnit($attr['y2'], $soid) : 0.0);
        $posx = $posx1;
        $posy = $posy1;
        $width = \abs($posx2 - $posx1);
        $height = \abs($posy2 - $posy1);
        $out = '';
        $out .= $this->graph->getStartTransform();
        $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
        $obstyle = '';
        $out .= $this->parseSVGStyle(
            $parser,
            $soid,
            $svgstyle,
            $prev_svgstyle,
            $posx,
            $posy,
            $width,
            $height,
            $obstyle,
            'getLine',
            [$posx1, $posy1, $posx2, $posy2],
        );
        $out .= $this->graph->getLine(
            $posx1,
            $posy1,
            $posx2,
            $posy2,
        );
        $out .= $this->graph->getStopTransform();
        return $out;
    }

    /**
     * Parse the SVG Start tag 'polygon'.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     *
     * @return string
     */
    protected function parseSVGTagSTARTpolygon(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return '';
        }
        $attrpoints = (!empty($attr['points']) ? \trim($attr['points']) : '0 0');
        // note that point may use a complex syntax not covered here
        $points = \preg_split('/[\,\s]+/si', $attrpoints);
        if (!\is_array($points) || \count($points) < 4) {
            return '';
        }
        $pset = [];
        $xmin = self::SVGMAXVAL;
        $xmax = 0.0;
        $ymin = self::SVGMAXVAL;
        $ymax = 0.0;
        foreach ($points as $key => $val) {
            $pset[$key] = $this->svgUnitToUnit($val, $soid);
            if (($key % 2) == 0) {
                // X coordinate
                $xmin = \min($xmin, $pset[$key]);
                $xmax = \max($xmax, $pset[$key]);
            } else {
                // Y coordinate
                $ymin = \min($ymin, $pset[$key]);
                $ymax = \max($ymax, $pset[$key]);
            }
        }
        $posx = $xmin;
        $posy = $ymin;
        $width = ($xmax - $xmin);
        $height = ($ymax - $ymin);
        $out = '';
        if ($this->svgobjs[$soid]['clipmode']) {
            $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
            $out .= $this->graph->getPolygon(
                $pset,
                'CNZ',
            );
            return $out;
        }
        $out .= $this->graph->getStartTransform();
        $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
        $obstyle = '';
        $out .= $this->parseSVGStyle(
            $parser,
            $soid,
            $svgstyle,
            $prev_svgstyle,
            $posx,
            $posy,
            $width,
            $height,
            $obstyle,
            'getPolygon',
            [$pset, 'CNZ']
        );
        if (!empty($obstyle)) {
            $out .= $this->graph->getPolygon(
                $pset,
                $obstyle,
            );
        }
        $out .= $this->graph->getStopTransform();
        return $out;
    }

    /**
     * Parse the SVG Start tag 'image'.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     *
     * @return string
     */
    protected function parseSVGTagSTARTimage(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return '';
        }
        if ($this->svgobjs[$soid]['clipmode']) {
            return '';
        }
        if (empty($attr['xlink:href'])) {
            return '';
        }
        $img = $attr['xlink:href'];
        $posx = (isset($attr['x']) ? $this->svgUnitToUnit($attr['x'], $soid) : 0.0);
        $posy = (isset($attr['y']) ? $this->svgUnitToUnit($attr['y'], $soid) : 0.0);
        $width = (isset($attr['width']) ? $this->svgUnitToUnit($attr['width'], $soid) : 0.0);
        $height = (isset($attr['height']) ? $this->svgUnitToUnit($attr['height'], $soid) : 0.0);
        $out = '';
        $out .= $this->graph->getStartTransform();
        $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
        $out .= $this->parseSVGStyle(
            $parser,
            $soid,
            $svgstyle,
            $prev_svgstyle,
            $posx,
            $posy,
            $width,
            $height,
        );
        if (
            'svg' === \strtolower(
                \trim(
                    \pathinfo(
                        ($purl = \parse_url($img, PHP_URL_PATH)) ? $purl : '',
                        PATHINFO_EXTENSION
                    ),
                )
            )
        ) {
            try {
                $child = $this->addSVG($img, $posx, $posy, $width, $height);
            } catch (Exception $e) {
                return '';
            }
            // @phpstan-ignore assign.propertyType
            $this->svgobjs[$soid]['child'][] = $child;
            return $out;
        }
        if (\preg_match('/^data:image\/[^;]+;base64,/', $img, $match) > 0) {
            // embedded image encoded as base64
            $img = '@' . \base64_decode(\substr($img, \strlen($match[0])));
        }

        if (!empty($this->svgobjs[$soid]['dir']) && (($img[0] == '.') || (\basename($img) == $img))) {
            // replace relative path with full server path
            $img = $this->svgobjs[$soid]['dir'] . '/' . $img;
        }

        $imgid = $this->image->add($img);
        $out .= $this->image->getSetImage(
            $imgid,
            $posx,
            $posy,
            $width,
            $height,
            $this->page->getPage()['height'],
        );
        $out .= $this->graph->getStopTransform();
        return $out;
    }

    /**
     * Parse the SVG Start tag 'text'.
     * Basic support only.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     * @param bool $is_tspan True if the tag is 'tspan'.
     *
     * @return string
     */
    protected function parseSVGTagSTARTtext(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
        bool $is_tspan = false,
    ): string {
        if (isset($this->svgobjs[$soid]['textmode']['text-anchor']) && !empty($this->svgobjs[$soid]['text'])) {
            // @TODO: unsupported feature
        }
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return '';
        }
        // @phpstan-ignore assign.propertyType
        \array_push($this->svgobjs[$soid]['styles'], $svgstyle);
        $posx = 0.0;
        $posy = 0.0;
        if (isset($attr['x'])) {
            $posx = $this->svgUnitToUnit($attr['x'], $soid);
        } elseif ($is_tspan) {
            $posx = $this->svgobjs[$soid]['x'];
        }
        if (isset($attr['dx'])) {
            $posx += $this->svgUnitToUnit($attr['dx'], $soid);
        }
        if (isset($attr['y'])) {
            $posy = $this->svgUnitToUnit($attr['y'], $soid);
        } elseif ($is_tspan) {
            $posy = $this->svgobjs[$soid]['y'];
        }
        if (isset($attr['dy'])) {
            $posy += $this->svgUnitToUnit($attr['dy'], $soid);
        }
        $svgstyle['text-color'] = $svgstyle['fill'];
        $this->svgobjs[$soid]['text'] = '';
        if (isset($svgstyle['text-anchor'])) {
            $this->svgobjs[$soid]['textmode']['text-anchor'] = $svgstyle['text-anchor'];
        } else {
            $this->svgobjs[$soid]['textmode']['text-anchor'] = 'start';
        }
        if (isset($svgstyle['direction'])) {
            $this->svgobjs[$soid]['textmode']['rtl'] = ($svgstyle['direction'] == 'rtl') ;
        } else {
            $this->svgobjs[$soid]['textmode']['rtl'] = false;
        }
        if (
            isset($svgstyle['stroke'])
            && ($svgstyle['stroke'] != 'none')
            && isset($svgstyle['stroke-width'])
            && ($svgstyle['stroke-width'] > 0)
        ) {
            $this->svgobjs[$soid]['textmode']['stroke'] = $this->svgUnitToUnit($svgstyle['stroke-width'], $soid);
        } else {
            $this->svgobjs[$soid]['textmode']['stroke'] = false;
        }
        $out = '';
        $out .= $this->graph->getStartTransform();
        $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
        $out .= $this->parseSVGStyle(
            $parser,
            $soid,
            $svgstyle,
            $prev_svgstyle,
            $posx,
            $posy,
            1,
            1
        );
        $this->svgobjs[$soid]['x'] = $posx;
        $this->svgobjs[$soid]['y'] = $posy;
        return $out;
    }

    /**
     * Parse the SVG Start tag 'tspan'.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     *
     * @return string
     */
    protected function parseSVGTagSTARTtspan(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        return $this->parseSVGTagSTARTtext(
            $parser,
            $soid,
            $attr,
            $svgstyle,
            $prev_svgstyle,
            true,
        );
    }

    /**
     * Parse the SVG Start tag 'use'.
     *
     * @param \XMLParser $parser The XML parser calling the handler.
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     *
     * @return string
     */
    protected function parseSVGTagSTARTuse(\XMLParser $parser, int $soid, array $attr): string
    {
        if (empty($attr['xlink:href'])) {
            return '';
        }
        $svgdefid = \substr($attr['xlink:href'], 1);
        if (empty($this->svgobjs[$soid]['defs'][$svgdefid])) {
            return '';
        }
        /** @var TSVGAttribs $use */
        $use = $this->svgobjs[$soid]['defs'][$svgdefid];

        if (isset($attr['xlink:href'])) {
            unset($attr['xlink:href']);
        }
        if (isset($attr['id'])) {
            unset($attr['id']);
        }
        if (isset($use['attr']['x']) && isset($attr['x'])) {
            $attr['x'] = \strval(\floatval($attr['x']) + \floatval($use['attr']['x']));
        }
        if (isset($use['attr']['y']) && isset($attr['y']) && \is_string($use['attr']['y'])) {
            $attr['y'] = \strval(\floatval($attr['y']) + \floatval($use['attr']['y']));
        }
        if (empty($attr['style'])) {
            $attr['style'] = '';
        }
        if (!empty($use['attr']['style']) && \is_string($use['attr']['style'])) {
            // merge styles
            $attr['style'] = \str_replace(';;', ';', ';' . $use['attr']['style'] . $attr['style']);
        }
        /** @var TSVGAttributes $attr */
        $attr = \array_merge($use['attr'], $attr);
        if (!\is_string($use['name'])) {
            return '';
        }
        $this->handleSVGTagStart(
            $parser,
            $use['name'],
            $attr,
            $soid,
        );
        return '';
    }

    /**
     * Get the SVG data from a file or data string.
     *
     * @param string $img
     *
     * @return string
     */
    protected function getRawSVGData(string $img): string
    {
        if (empty($img) || (($img[0] === '@') && (\strlen($img) === 1))) {
            return '';
        }
        if ($img[0] === '@') { // image from string
            return \substr($img, 1);
        }
        $data = $this->file->getFileData($img);
        if (empty($data)) {
            return '';
        }
        return $data;
    }

    /**
     * Get the SVG size from the SVG data.
     *
     * @param string $data The string containing the SVG image data.
     *
     * @return TSVGSize Associative array with dimensions.
     */
    protected function getSVGSize(string $data): array
    {
        $out = [
            'x' => 0.0,
            'y' => 0.0,
            'width' => 0.0,
            'height' => 0.0,
            'viewBox' => [0.0,0.0,0.0,0.0],
            'ar_align' => 'xMidYMid',
            'ar_ms' => 'meet',
        ];

        \preg_match('/<svg([^\>]*)>/si', $data, $regs);
        if (!isset($regs[1]) || empty($regs[1])) {
            return $out;
        }

        $tmp = [];
        if (\preg_match('/[\s]+x[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
            $out['x'] = $this->svgUnitToUnit($tmp[1]);
        }
        $tmp = array();
        if (\preg_match('/[\s]+y[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
            $out['y'] = $this->svgUnitToUnit($tmp[1]);
        }
        $tmp = array();
        if (\preg_match('/[\s]+width[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
            $out['width'] = $this->svgUnitToUnit($tmp[1]);
        }
        $tmp = array();
        if (\preg_match('/[\s]+height[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
            $out['height'] = $this->svgUnitToUnit($tmp[1]);
        }

        $tmp = [];
        if (
            !\preg_match(
                '/[\s]+viewBox[\s]*=[\s]*"[\s]*([0-9\.\-]+)[\s]+([0-9\.\-]+)[\s]+([0-9\.]+)[\s]+([0-9\.]+)[\s]*"/si',
                $regs[1],
                $tmp,
            )
        ) {
            return $out;
        }

        if (\count($tmp) == 5) {
            \array_shift($tmp);
            foreach ($tmp as $key => $val) {
                $out['viewBox'][$key] = $this->svgUnitToUnit($val);
            }
        }

        // get aspect ratio
        $tmp = [];
        if (!\preg_match('/[\s]+preserveAspectRatio[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
            return $out;
        }

        $asr = \preg_split('/[\s]+/si', $tmp[1]);
        if (!\is_array($asr) || \count($asr) < 1) {
            return $out;
        }
        switch (\count($asr)) {
            case 3:
                $out['ar_align'] = $asr[1];
                $out['ar_ms'] = $asr[2];
                break;
            case 2:
                $out['ar_align'] = $asr[0];
                $out['ar_ms'] = $asr[1];
                break;
            case 1:
                $out['ar_align'] = $asr[0];
                $out['ar_ms'] = 'meet';
                break;
        }

        return $out;
    }

    /**
     * Add a new SVG image and return its object ID.
     *
     * @param string $img The string containing the SVG image data or the path to the SVG file.
     * @param float $posx X position in user units.
     * @param float $posy Y position in user units.
     * @param float $width Width in user units.
     * @param float $height Height in user units.
     * @param float $pageheight Page height in user units.
     *
     * @return int The SVG object ID.
     */
    public function addSVG(
        string $img,
        float $posx = 0.0,
        float $posy = 0.0,
        float $width = 0.0,
        float $height = 0.0,
        float $pageheight = 0.0,
    ): int {
        if (empty($pageheight)) {
            $pageheight = $this->page->getPage()['height'];
        }
        $this->graph->setPageHeight($pageheight);

        $imgdir = \dirname($img);
        if ($imgdir === '.') {
            $imgdir = '';
        }

        $data = $this->getRawSVGData($img);
        if (empty($data)) {
            throw new PdfException('Invalid SVG');
        }

        $size = $this->getSVGSize($data);
        if ($size['width'] <= 0.0 || $size['height'] <= 0.0) {
            throw new PdfException('Invalid SVG size');
        }

        if ($size['width'] <= 0.0) {
            $size['width'] = 1.0;
        }
        if ($size['height'] <= 0.0) {
            $size['height'] = 1.0;
        }

        // calculate image width && height on document
        if (($width <= 0.0) && ($height <= 0.0)) {
            // convert image size to document unit
            $width = $size['width'];
            $height = $size['height'];
        } elseif ($width <= 0.0) {
            $width = $height * $size['width'] / $size['height'];
        } elseif ($height <= 0.0) {
            $height = $width * $size['height'] / $size['width'];
        }

        if (!empty($size['viewBox'][2]) && !empty($size['viewBox'][3])) {
            $size['width'] = $size['viewBox'][2];
            $size['height'] = $size['viewBox'][3];
        } else {
            if ($size['width'] <= 0) {
                $size['width'] = $width;
            }
            if ($size['height'] <= 0) {
                $size['height'] = $height;
            }
        }

        // SVG position && scale factors
        $svgoffset_x = $this->toPoints($posx - $size['x']);
        $svgoffset_y = $this->toPoints($size['y'] - $posy);
        $svgscale_x = $width / $size['width'];
        $svgscale_y = $height / $size['height'];

        // scaling && alignment
        if ($size['ar_align'] != 'none') {
            // force uniform scaling
            if ($size['ar_ms'] == 'slice') {
                // the entire viewport is covered by the viewBox
                if ($svgscale_x > $svgscale_y) {
                    $svgscale_y = $svgscale_x;
                } elseif ($svgscale_x < $svgscale_y) {
                    $svgscale_x = $svgscale_y;
                }
            } else { // meet
                // the entire viewBox is visible within the viewport
                if ($svgscale_x < $svgscale_y) {
                    $svgscale_y = $svgscale_x;
                } elseif ($svgscale_x > $svgscale_y) {
                    $svgscale_x = $svgscale_y;
                }
            }
            // correct X alignment
            switch (\substr($size['ar_align'], 1, 3)) {
                case 'Min':
                    // do nothing
                    break;
                case 'Max':
                    $svgoffset_x += $this->toPoints($width - ($size['width'] * $svgscale_x));
                    break;
                default:
                case 'Mid':
                    $svgoffset_x += $this->toPoints(($width - ($size['width'] * $svgscale_x)) / 2);
                    break;
            }
            // correct Y alignment
            switch (\substr($size['ar_align'], 5)) {
                case 'Min':
                    // do nothing
                    break;
                case 'Max':
                    $svgoffset_y -= $this->toPoints($height - ($size['height'] * $svgscale_y));
                    break;
                default:
                case 'Mid':
                    $svgoffset_y -= $this->toPoints(($height - ($size['height'] * $svgscale_y)) / 2);
                    break;
            }
        }

        $soid = (int)\array_key_last($this->svgobjs);
        $soid++;

        // @phpstan-ignore assign.propertyType
        $this->svgobjs[$soid] = self::SVGDEFOBJ;
        $this->svgobjs[$soid]['dir'] = $imgdir;
        $this->svgobjs[$soid]['refunitval']['page']['height'] = $this->toPoints($pageheight);

        $out = '';
        $out .= $this->graph->getStartTransform();
        $out .= $this->graph->getRawRect(
            $posx,
            $posy,
            $width,
            $height,
            'CNZ',
        );

        // scale && translate
        $esx = $this->toPoints($size['x'] * (1 - $svgscale_x));
        $fsy = $this->toPoints(($pageheight - $size['y']) * (1 - $svgscale_y));

        $ctm = [
            0 => $svgscale_x,
            1 => 0.0,
            2 => 0.0,
            3 => $svgscale_y,
            4 => $esx + $svgoffset_x,
            5 => $fsy + $svgoffset_y,
        ];

        $out .= $this->graph->getTransformation($ctm);

        $this->svgobjs[$soid]['out'] .= $out;

        // creates a new XML parser to be used by the other XML functions
        $parser = \xml_parser_create('UTF-8');
        // the following function allows to use parser inside object
        \xml_set_object($parser, $this);
        // disable case-folding for this XML parser
        \xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        // sets the element handler functions for the XML parser
        \xml_set_element_handler($parser, [$this, 'handleSVGTagStart'], [$this, 'handleSVGTagEnd']);
        // sets the character data handler function for the XML parser
        \xml_set_character_data_handler($parser, [$this, 'handlerSVGCharacter']);

        // start parsing an XML document
        if (!\xml_parse($parser, $data)) {
            throw new PdfException(\sprintf(
                'SVG Error: %s at line %d',
                \xml_error_string(
                    \xml_get_error_code($parser)
                ),
                \xml_get_current_line_number($parser),
            ),);
        }

        // free this XML parser
        \xml_parser_free($parser);
        // >= PHP 7.0.0 "explicitly unset the reference to parser to avoid memory leaks"
        unset($parser);

        $this->svgobjs[$soid]['out'] .= $this->graph->getStopTransform();

        return $soid;
    }

    /**
     * Get the PDF output string to print the specified SVG object.
     *
     * @param int   $soid       SVG Object ID (as returned by addSVG).
     *
     * @return string Image PDF page content.
     */
    public function getSetSVG(int $soid): string
    {
        if (empty($this->svgobjs[$soid])) {
            throw new PdfException('Unknown SVG ID: ' . $soid);
        }

        $out = $this->svgobjs[$soid]['out'];

        foreach ($this->svgobjs[$soid]['child'] as $chid) {
            $out .= $this->getSetSVG($chid);
        }

        return $out;
    }
}
