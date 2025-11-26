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
 *    'xref': int,
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
 * @phpstan-type TSVGAttribs array{
 *    'attr': TSVGAttributes,
 *    'child'?: array<string, array{'name': string, 'attr': TSVGAttributes}>,
 * }
 *
 * @phpstan-type TSVGClipPath array{
 *    'name': string,
 *    'attr': TSVGAttribs,
 *    'tm': array<int, float>,
 * }
 *
 * @phpstan-type TSVGDefs array{
 *    'name': string,
 *    'attr': TSVGAttribs,
 * }
 *
 * @phpstan-type TSVGObj array{
 *    'defsmode': bool,
 *    'clipmode': bool,
 *    'clipid': int,
 *    'gradientid': int,
 *    'tagdepth': int,
 *    'x0': float,
 *    'y0': float,
 *    'x': float,
 *    'y': float,
 *    'gradients': array<int, TSVGGradient>,
 *    'clippaths': array<string, TSVGClipPath>,
 *    'defs': array<string, TSVGDefs>,
 *    'cliptm': array<float>,
 *    'styles': array<int, TSVGStyle>,
 *    'textmode': TSVGTextMode,
 *    'text': string,
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
        'transfmatrix' => [1.0, 0.0, 0.0, 1.0, 0.0, 0.0],
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
        'gradientid' => 0,
        'tagdepth' => 0,
        'x0' => 0.0,
        'y0' => 0.0,
        'x' => 0.0,
        'y' => 0.0,
        'gradients' => [],
        'clippaths' => [],
        'cliptm' => [],
        'defs' => [],
        'styles' => [0 => self::DEFSVGSTYLE],
        'textmode' => [
            'rtl' => false,
            'invisible' => false,
            'stroke' => 0,
            'text-anchor' => 'start',
        ],
        'text' => '',
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
            preg_match(
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
            $tmb[0] = floatval($regs[1]);
            $tmb[1] = floatval($regs[2]);
            $tmb[2] = floatval($regs[3]);
            $tmb[3] = floatval($regs[4]);
            $tmb[4] = floatval($regs[5]);
            $tmb[5] = floatval($regs[6]);
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
        if (preg_match('/([a-z0-9\-\.]+)[\,\s]+([a-z0-9\-\.]+)/si', $val, $regs)) {
            $tmb[4] = floatval($regs[1]);
            $tmb[5] = floatval($regs[2]);
            return $tmb;
        }
        if (preg_match('/([a-z0-9\-\.]+)/si', $val, $regs)) {
            $tmb[4] = floatval($regs[1]);
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
        if (preg_match('/([a-z0-9\-\.]+)[\,\s]+([a-z0-9\-\.]+)/si', $val, $regs)) {
            $tmb[0] = floatval($regs[1]);
            $tmb[3] = floatval($regs[2]);
            return $tmb;
        }
        if (preg_match('/([a-z0-9\-\.]+)/si', $val, $regs)) {
            $tmb[0] = floatval($regs[1]);
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
        if (preg_match('/([0-9\-\.]+)[\,\s]+([a-z0-9\-\.]+)[\,\s]+([a-z0-9\-\.]+)/si', $val, $regs)) {
            $ang = deg2rad(floatval($regs[1]));
            $trx = floatval($regs[2]);
            $try = floatval($regs[3]);
            $tmb[0] = cos($ang);
            $tmb[1] = sin($ang);
            $tmb[2] = -$tmb[1];
            $tmb[3] = $tmb[0];
            $tmb[4] = ($trx * (1 - $tmb[0])) - ($try * $tmb[2]);
            $tmb[5] = ($try * (1 - $tmb[3])) - ($trx * $tmb[1]);
            return $tmb;
        }
        if (preg_match('/([0-9\-\.]+)/si', $val, $regs)) {
            $ang = deg2rad(floatval($regs[1]));
            $tmb[0] = cos($ang);
            $tmb[1] = sin($ang);
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
        if (preg_match('/([0-9\-\.]+)/si', $val, $regs)) {
            $tmb[2] = tan(deg2rad(floatval($regs[1])));
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
        if (preg_match('/([0-9\-\.]+)/si', $val, $regs)) {
            $tmb[1] = tan(deg2rad(floatval($regs[1])));
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
            !preg_match_all(
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
     * @param TRefUnitValues $ref page height in internal points.
     *
     * @return TTMatrix Transformation matrix.
     */
    protected function convertSVGMatrix(
        array $trm,
        array $ref = self::REFUNITVAL,
    ): array {
        // $tmx = 0;
        $tmy = $ref['page']['height'];

        $trm[1] = -$trm[1];
        $trm[2] = -$trm[2];
        // ($tmx * (1 - $trm[0])) - ($tmy * $trm[2]) + $this->getUnitValuePoints($trm[4], $ref, self::SVGUNIT);
        $trm[4] = $this->getUnitValuePoints($trm[4], $ref, self::SVGUNIT) - ($tmy * $trm[2]);
        // ($tmy * (1 - $trm[3])) - ($tmx * $trm[1]) - $this->getUnitValuePoints($trm[5], $ref, self::SVGUNIT);
        $trm[5] = ($tmy * (1 - $trm[3])) - $this->getUnitValuePoints($trm[5], $ref, self::SVGUNIT);

        return $trm;
    }

    /**
     * Get the SVG tranformation matrix (CTM) PDF string.
     *
     * @param TTMatrix $trm original SVG transformation matrix.
     * @param TRefUnitValues $ref page height in internal points.
     *
     * @return string Transformation matrix (PDF string).
     */
    protected function getOutSVGTransformation(
        array $trm,
        array $ref = self::REFUNITVAL,
    ): string {
        return $this->graph->getTransformation(
            $this->convertSVGMatrix($trm, $ref),
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
        $parts = explode(':', $name);
        return end($parts);
    }

    protected function getSVGPath(
        string $attrd,
        string $mode = '',
    ): string {
        // set paint operator
        $pop = $this->graph->getPathPaintOp($mode, '');
        if (empty($pop)) {
            return '';
        }

        // extract paths
        $attrd = preg_replace('/([0-9ACHLMQSTVZ])([\-\+])/si', '\\1 \\2', $attrd);
        if (empty($attrd)) {
            return '';
        }

        $attrd = preg_replace('/(\.[0-9]+)(\.)/s', '\\1 \\2', $attrd);
        if (empty($attrd)) {
            return '';
        }

        $paths = [];
        preg_match_all('/([ACHLMQSTVZ])[\s]*+([^ACHLMQSTVZ\"]*+)/si', $attrd, $paths, PREG_SET_ORDER);

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
            $cmd = trim($val[1]);

            // relative or absolute coordinates
            $coord['relcoord'] = (strtolower($cmd) == $cmd);
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
            if (empty(preg_match_all('/-?\d*+\.?\d+/', trim($val[2]), $rprms))) {
                return '';
            }

            $rawparams = $rprms[0];

            foreach ($rawparams as $prk => $prv) {
                $params[$prk] = $this->getUnitValuePoints($prv, self::REFUNITVAL, self::SVGUNIT);
                if (abs($params[$prk]) < $this->svgminunitlen) {
                    // approximate little values to zero
                    $params[$prk] = 0;
                }
            }

            // store current origin point
            $coord['x0'] = $coord['x'];
            $coord['y0'] = $coord['y'];

            $out .= match (strtoupper($cmd)) {
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

        return $out . ' ' . $pop . "\n";
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
            $rpx = (float) max(abs($prm[($prk - 6)]), .000000001);
            $rpy = (float) max(abs($prm[($prk - 5)]), .000000001);
            $ang = -intval($rawparams[($prk - 4)]);
            $angle = deg2rad($ang);
            $laf = $rawparams[($prk - 3)]; // large-arc-flag
            $swf = $rawparams[($prk - 2)]; // sweep-flag
            $crd['x'] = $prm[($prk - 1)] + $crd['xoffset'];
            $crd['y'] = $prv + $crd['yoffset'];

            if (
                (abs($crd['x0'] - $crd['x']) < $this->svgminunitlen) &&
                (abs($crd['y0'] - $crd['y']) < $this->svgminunitlen)
            ) {
                // endpoints are almost identical
                $crd['xmin'] = (float) min($crd['xmin'], $crd['x']);
                $crd['ymin'] = (float) min($crd['ymin'], $crd['y']);
                $crd['xmax'] = (float) max($crd['xmax'], $crd['x']);
                $crd['ymax'] = (float) max($crd['ymax'], $crd['y']);
            } else {
                $cos_ang = cos($angle);
                $sin_ang = sin($angle);
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
                    $rpx *= sqrt($delta);
                    $rpy *= sqrt($delta);
                    $rx2 = $rpx * $rpx;
                    $ry2 = $rpy * $rpy;
                }
                $numerator = (($rx2 * $ry2) - ($rx2 * $pya2) - ($ry2 * $pxa2));
                $root = 0;
                if ($numerator > 0) {
                    $root = sqrt($numerator / (($rx2 * $pya2) + ($ry2 * $pxa2)));
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
                $angs = round(rad2deg($angs), 6);
                $angf = round(rad2deg($angf), 6);
                // covent angles to positive values
                if (($angs < 0) && ($angf < 0)) {
                    $angs += 360;
                    $angf += 360;
                }
                $pie = false;
                if (($key == 0) && (isset($paths[($key + 1)][1])) && (trim($paths[($key + 1)][1]) == 'z')) {
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
                $crd['xmin'] = (float) min($crd['xmin'], $crd['x'], $bbox[0]);
                $crd['ymin'] = (float) min($crd['ymin'], $crd['y'], $bbox[1]);
                $crd['xmax'] = (float) max($crd['xmax'], $crd['x'], $bbox[2]);
                $crd['ymax'] = (float) max($crd['ymax'], $crd['y'], $bbox[3]);
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
            $crd['xmin'] = (float) min($crd['xmin'], $crd['x'], $px1, $px2);
            $crd['ymin'] = (float) min($crd['ymin'], $crd['y'], $py1, $py2);
            $crd['xmax'] = (float) max($crd['xmax'], $crd['x'], $px1, $px2);
            $crd['ymax'] = (float) max($crd['ymax'], $crd['y'], $py1, $py2);
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
                (abs($crd['x0'] - $crd['x']) >= $this->svgminunitlen) ||
                (abs($crd['y0'] - $crd['y']) >= $this->svgminunitlen)
            ) {
                $out .= $this->graph->getRawLine($crd['x'], $crd['y']);
                $crd['x0'] = $crd['x'];
                $crd['y0'] = $crd['y'];
            }
            $crd['xmin'] = min($crd['xmin'], $crd['x']);
            $crd['xmax'] = max($crd['xmax'], $crd['x']);
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
                (abs($crd['x0'] - $crd['x']) >= $this->svgminunitlen) ||
                (abs($crd['y0'] - $crd['y']) >= $this->svgminunitlen)
            ) {
                $out .= $this->graph->getRawLine($crd['x'], $crd['y']);
                $crd['x0'] = $crd['x'];
                $crd['y0'] = $crd['y'];
            }
            $crd['xmin'] = min($crd['xmin'], $crd['x']);
            $crd['ymin'] = min($crd['ymin'], $crd['y']);
            $crd['xmax'] = max($crd['xmax'], $crd['x']);
            $crd['ymax'] = max($crd['ymax'], $crd['y']);
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
                (abs($crd['x0'] - $crd['x']) >= $this->svgminunitlen) ||
                (abs($crd['y0'] - $crd['y']) >= $this->svgminunitlen)
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
            $crd['xmin'] = min($crd['xmin'], $crd['x']);
            $crd['ymin'] = min($crd['ymin'], $crd['y']);
            $crd['xmax'] = max($crd['xmax'], $crd['x']);
            $crd['ymax'] = max($crd['ymax'], $crd['y']);
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
            $crd['xmin'] = min($crd['xmin'], $crd['x'], $pxa, $pxb);
            $crd['ymin'] = min($crd['ymin'], $crd['y'], $pya, $pyb);
            $crd['xmax'] = max($crd['xmax'], $crd['x'], $pxa, $pxb);
            $crd['ymax'] = max($crd['ymax'], $crd['y'], $pya, $pyb);
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
                ((strtoupper($paths[($key - 1)][1]) == 'C') ||
                (strtoupper($paths[($key - 1)][1]) == 'S'))
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
            $crd['xmin'] = min($crd['xmin'], $crd['x'], $px1, $px2);
            $crd['ymin'] = min($crd['ymin'], $crd['y'], $py1, $py2);
            $crd['xmax'] = max($crd['xmax'], $crd['x'], $px1, $px2);
            $crd['ymax'] = max($crd['ymax'], $crd['y'], $py1, $py2);
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
                ((strtoupper($paths[($key - 1)][1]) == 'Q') ||
                (strtoupper($paths[($key - 1)][1]) == 'T'))
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
            $crd['xmin'] = min($crd['xmin'], $crd['x'], $pxa, $pxb);
            $crd['ymin'] = min($crd['ymin'], $crd['y'], $pya, $pyb);
            $crd['xmax'] = max($crd['xmax'], $crd['x'], $pxa, $pxb);
            $crd['ymax'] = max($crd['ymax'], $crd['y'], $pya, $pyb);
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
                (abs($crd['x0'] - $crd['x']) >= $this->svgminunitlen) ||
                (abs($crd['y0'] - $crd['y']) >= $this->svgminunitlen)
            ) {
                $out .= $this->graph->getRawLine($crd['x'], $crd['y']);
                $crd['x0'] = $crd['x'];
                $crd['y0'] = $crd['y'];
            }
            $crd['ymin'] = min($crd['ymin'], $crd['y']);
            $crd['ymax'] = max($crd['ymax'], $crd['y']);
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
    protected function getTALetterSpacing(string $spacing, float $parent = 0): float
    {
        $spacing = trim($spacing);
        return match ($spacing) {
            'normal' => 0,
            'inherit' => $parent,
            default => $this->getUnitValuePoints($spacing, array_merge(self::REFUNITVAL, ['parent' => $parent])),
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
        $stretch = trim($stretch);
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
            default => $this->getUnitValuePoints($stretch, array_merge(self::REFUNITVAL, ['parent' => $parent]), '%'),
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
        $weight = trim($weight);
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
        $style = trim($style);
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
        $decoration = trim($decoration);
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
        if (preg_match('/' . $attr . '[\s]*+:[\s]*+([^\;\"]*+)/si', $tag, $regs)) {
            return trim($regs[1]);
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
            intval($svgstyle['font-size-val']),
        );

        return $fontmetric['out'];
    }

    /**
     * Parse the SVG stroke style.
     *
     * @param TSVGStyle $svgstyle SVG style.
     *
     * @return string the Raw PDF command to set the stroke.
     */
    protected function parseSVGStyleStroke(
        array &$svgstyle,
    ): string {
        if (empty($svgstyle['stroke']) || ($svgstyle['stroke'] == 'none')) {
            return '';
        }

        $strokestyle = $this->graph->getDefaultStyle();

        $col = $this->color->getColorObj($svgstyle['stroke']);
        if ($col == null) {
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

        $ref = self::REFUNITVAL;
        $ref['parent'] = 0;
        $strokestyle['lineWidth'] = $this->getUnitValuePoints(
            $svgstyle['stroke-width'],
            $ref,
        );

        $strokestyle['lineCap'] = $svgstyle['stroke-linecap'];
        $strokestyle['lineJoin'] = $svgstyle['stroke-linejoin'];
        //  $strokestyle['miterLimit'] = (10.0 / $this->kunit),
        $strokestyle['dashArray'] = (
            empty($svgstyle['stroke-dasharray']) || ($svgstyle['stroke-dasharray'] == 'none')
        ) ? [] : array_map(
            'intval',
            explode(' ', $svgstyle['stroke-dasharray'], 100),
        );
        // $strokestyle['dashPhase'] = 0,
        $strokestyle['lineColor'] = $svgstyle['stroke'];
        $strokestyle['fillColor'] = $svgstyle['stroke'];

        $out .= $this->graph->getStyleCmd($strokestyle);

        $svgstyle['objstyle'] .= 'D'; // @phpstan-ignore-line

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
            !preg_match(
                '/rect\(([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)\)/si',
                $svgstyle['clip'],
                $regs
            )
        ) {
            return '';
        }

        $top = $this->toUnit(
            $regs[1]
            ? $this->getUnitValuePoints($regs[1], self::REFUNITVAL, self::SVGUNIT)
            : 0
        );
        $right = $this->toUnit(
            $regs[2]
            ? $this->getUnitValuePoints($regs[2], self::REFUNITVAL, self::SVGUNIT)
            : 0
        );
        $bottom = $this->toUnit(
            $regs[3]
            ? $this->getUnitValuePoints($regs[3], self::REFUNITVAL, self::SVGUNIT)
            : 0
        );
        $left = $this->toUnit(
            $regs[4]
            ? $this->getUnitValuePoints($regs[4], self::REFUNITVAL, self::SVGUNIT)
            : 0
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
     * @param array<int, TSVGGradient> $gradients Gradients.
     * @param int $xref Gradient ID.
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
        array $gradients,
        int $xref,
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

        if (!empty($clip_fnc)) {
            $bbox = [];
            if (method_exists($this, $clip_fnc)) {
                $bbox = $this->$clip_fnc(...$clip_par);
            } elseif (method_exists($this->graph, $clip_fnc)) {
                $bbox = $this->graph->$clip_fnc(...$clip_par);
            }
            if (
                (!isset($gradient['type'])
                || ($gradient['type'] != 3))
                && is_array($bbox)
                && (count($bbox) == 4)
            ) {
                $grx = is_numeric($bbox[0]) ? (float)$bbox[0] : 0.0;
                $gry = is_numeric($bbox[1]) ? (float)$bbox[1] : 0.0;
                $grw = is_numeric($bbox[2]) ? (float)$bbox[2] : 0.0;
                $grh = is_numeric($bbox[3]) ? (float)$bbox[3] : 0.0;
            }
        }

        switch ($gradient['mode']) {
            case 'percentage':
                foreach ($gradient['coords'] as $key => $val) {
                    $gradient['coords'][$key] = (intval($val) / 100);
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
                    $grr = sqrt(pow(
                        ($gtm[0] * $gradient['coords'][4]),
                        2
                    ) + pow(
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
                $gradient['coords'][0] = $this->toUnit(
                    $this->getUnitValuePoints(
                        $gradient['coords'][0],
                        self::REFUNITVAL,
                        self::SVGUNIT
                    )
                );
                $gradient['coords'][1] = $this->toUnit(
                    $this->getUnitValuePoints(
                        $gradient['coords'][1],
                        self::REFUNITVAL,
                        self::SVGUNIT
                    )
                );
                $gradient['coords'][2] = $this->toUnit(
                    $this->getUnitValuePoints(
                        $gradient['coords'][2],
                        self::REFUNITVAL,
                        self::SVGUNIT
                    )
                );
                $gradient['coords'][3] = $this->toUnit(
                    $this->getUnitValuePoints(
                        $gradient['coords'][3],
                        self::REFUNITVAL,
                        self::SVGUNIT
                    )
                );
                $gradient['coords'][4] = $this->toUnit(
                    $this->getUnitValuePoints(
                        $gradient['coords'][4],
                        self::REFUNITVAL,
                        self::SVGUNIT
                    )
                );
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
        $gry = ($this->page->getPage()['height'] - $gry);
        if ($gradient['type'] == 3) {
            // circular gradient
            $gry -= ($gradient['coords'][1] * ($grw + $grh));
            $grh = $grw = max($grw, $grh);
        } else {
            $gry -= $grh;
        }

        $out = '';

        $out .= sprintf(
            '%F 0 0 %F %F %F cm',
            $this->toPoints($grw),
            $this->toPoints($grh),
            $this->toPoints($grx),
            $this->toPoints($gry)
        );

        if (count($gradient['stops']) > 1) {
            $out .= $this->graph->getGradient(
                $gradient['type'],
                $gradient['coords'],
                $gradient['stops'],
                '',
                false,
            );
        }

        return $out;
    }

    /**
     * Parse the SVG fill style.
     *
     * @param TSVGStyle $svgstyle SVG style.
     * @param array<int, TSVGGradient> $gradients Gradients.
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
        if (preg_match('/url\([\s]*\#([^\)]*)\)/si', $svgstyle['fill'], $regs)) {
            return $this->parseSVGStyleGradient(
                $gradients,
                intval($regs[1]),
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

        $svgstyle['objstyle'] .= ($svgstyle['fill-rule'] == 'evenodd') ? 'F*' : 'F';

        $out .= $col->getPdfColor();

        return $out;
    }

    /**
     * Parse the SVG style clip-path.
     *
     * @param TSVGStyle $svgstyle SVG style.
     * @param array<string, TSVGClipPath> $clippaths Clipping paths.
     * @return string the Raw PDF command.
     */
    protected function parseSVGStyleClipPath(
        array &$svgstyle,
        array $clippaths = [],
    ): string {
        $out = '';
        $regs = [];
        if (preg_match('/url\([\s]*\#([^\)]*)\)/si', $svgstyle['clip-path'], $regs)) {
            $clip_path = $clippaths[$regs[1]];
            foreach ($clip_path as $cp) {
                $cp = $cp; // @phpstan-ignore-line
                //@TODO $out .= $this->handleSVGTagStart('clip-path', $cp['name'], $cp['attr'], $cp['tm']);
            }
        }
        return $out;
    }

    /**
     * Parse the SVG style.
     *
     * @param TSVGObj $svgobj SVG object.
     * @param float $posx X position in user units.
     * @param float $posy Y position in user units.
     * @param float $width Width in user units.
     * @param float $height Height in user units.
     * @param string $clip_fnc Optional clipping function name.
     * @param array<mixed> $clip_par Optional clipping function parameters.
     *
     * @return string the Raw PDF command.
     */
    protected function parseSVGStyle(
        array &$svgobj,
        float $posx = 0,
        float $posy = 0,
        float $width = 1,
        float $height = 1,
        string $clip_fnc = '',
        array $clip_par = [],
    ): string {
        $sid = (int)array_key_last($svgobj['styles']);

        if (empty($svgobj['styles'][$sid]['opacity'])) {
            return '';
        }

        return $this->parseSVGStyleClipPath($svgobj['styles'][$sid], $svgobj['clippaths']) .
        $this->parseSVGStyleColor($svgobj['styles'][$sid]) .
            $this->parseSVGStyleClip(
                $svgobj['styles'][$sid],
                $posx,
                $posy,
                $width,
                $height
            ) .
            $this->parseSVGStyleFill(
                $svgobj['styles'][$sid],
                $svgobj['gradients'],
                $posx,
                $posy,
                $width,
                $height,
                $clip_fnc,
                $clip_par
            ) .
            $this->parseSVGStyleStroke($svgobj['styles'][$sid]) .
            $this->parseSVGStyleFont($svgobj['styles'][$sid], $svgobj['styles'][($sid - 1)]);
    }

    /**
     * Handler for the SVG character data.
     *
     * @param string $parser The XML parser calling the handler.
     * @param string $data Character data.
     *
     * @return void
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function handlerSVGCharacter(
        string $parser,
        string $data,
    ) {
        $soid = (int)array_key_last($this->svgobjs);
        if (($soid < 0) || !isset($this->svgobjs[$soid]['text'])) {
            return;
        }
        $this->svgobjs[$soid]['text'] .= $data;
    }

    /**
     * Handler for the end of an SVG tag.
     *
     * @param string $parser The XML parser calling the handler.
     * @param string $name Name of the element for which this handler is called.
     *
     * @return void
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function handleSVGTagEnd(
        string $parser,
        string $name,
    ): void {
        $name = $this->removeTagNamespace($name);

        $soid = (int)array_key_last($this->svgobjs);
        if ($soid < 0) {
            return;
        }

        if (
            $this->svgobjs[$soid]['defsmode']
            && !in_array($name, self::SVGDEFSMODEEND)
        ) {
            if (end($this->svgobjs[$soid]['defs']) !== false) {
                $last_svgdefs_id = (string)array_key_last($this->svgobjs[$soid]['defs']);
                if (!empty($this->svgobjs[$soid]['defs'][$last_svgdefs_id]['attr']['child'])) {
                    foreach (
                        $this->svgobjs[$soid]['defs'][$last_svgdefs_id]['attr']['child'] as $child
                    ) {
                        if (
                            isset($child['attr']['id']) &&
                            is_scalar($child['attr']['id']) &&
                            ($child['name'] == $name)
                        ) {
                            $closeKey = (string)$child['attr']['id'] . '_CLOSE';
                            $this->svgobjs[$soid]['defs'][$last_svgdefs_id]['attr']['child'][$closeKey] = [
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
                        $this->svgobjs[$soid]['defs'][$last_svgdefs_id]['attr']['child'][$closeKey] = [
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

        match ($name) {
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
     * @return void
     */
    protected function parseSVGTagENDdefs(int $soid): void
    {
        $this->svgobjs[$soid]['defsmode'] = false;
    }

    /**
     * Parse the SVG End tag 'defs'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return void
     */
    protected function parseSVGTagENDclipPath(int $soid): void
    {
        $this->svgobjs[$soid]['clipmode'] = false;
    }

    /**
     * Parse the SVG End tag 'defs'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return void
     */
    protected function parseSVGTagENDsvg(int $soid): void
    {
        if (--$this->svgobjs[$soid]['tagdepth'] <= 0) {
            return;
        }
        $this->parseSVGTagENDg($soid);
    }

    /**
     * Parse the SVG End tag 'defs'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return void
     */
    protected function parseSVGTagENDg(int $soid): void
    {
        array_pop($this->svgobjs[$soid]['styles']);
        $this->svgobjs[$soid]['out'] .= $this->graph->getStopTransform();
    }

    /**
     * Parse the SVG End tag 'defs'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return void
     */
    protected function parseSVGTagENDtspan(int $soid): void
    {
        $this->parseSVGTagENDtext($soid);
    }

    /**
     * Parse the SVG End tag 'defs'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return void
     */
    protected function parseSVGTagENDtext(int $soid): void
    {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            // @TODO : This implementation must be fixed to following the rule:
            // If the 'visibility' property is set to hidden on a 'tspan', 'tref' or 'altGlyph' element,
            // then the text is invisible but still takes up space in text layout calculations.
            return;
        }

        $curx = $this->svgobjs[$soid]['x'];
        $cury = $this->svgobjs[$soid]['y'];

        $anchor = $this->svgobjs[$soid]['textmode']['text-anchor'] ?? 'start';
        $txtanchor = match ($anchor) {
            'end' => 'E',
            'middle' => 'M',
            default => 'S',
        };

        $this->svgobjs[$soid]['out'] .= $this->getTextLine(
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

        $this->svgobjs[$soid]['text'] = ''; // reset text buffer
        $this->svgobjs[$soid]['out'] .= $this->graph->getStopTransform();

        if (!$this->svgobjs[$soid]['defsmode']) {
            array_pop($this->svgobjs[$soid]['styles']);
        }
    }

    /**
     * Handler for the start of an SVG tag.
     *
     * @param string $parser The XML parser calling the handler.
     * @param string $name Name of the element for which this handler is called.
     * @param TSVGAttribs $attribs Associative array with the element's attributes.
     * @param TTMatrix $ctm Current transformation matrix (optional).
     *
     * @return void
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function handleSVGTagStart(
        string $parser,
        string $name,
        array $attribs,
        array $ctm = [1,0,0,1,0,0], // identity matrix
    ): void {
        $name = $this->removeTagNamespace($name);

        $soid = (int)array_key_last($this->svgobjs);
        if ($soid < 0) {
            return;
        }

        if ($this->svgobjs[$soid]['clipmode']) {
            $this->svgobjs[$soid]['clippaths'][] = [
                'name' => $name,
                'attribs' => $attribs,
                'tm' => $this->svgobjs[$soid]['cliptm'],
            ];
            return;
        }

        if (
            $this->svgobjs[$soid]['defsmode']
            && !in_array($name, ['clipPath', 'linearGradient', 'radialGradient', 'stop'])
        ) {
            if (!isset($this->svgobjs[$soid]['clippaths'])) {
                $this->svgobjs[$soid]['clippaths'] = [];
            }

            if (isset($attribs['attr']['id'])) {
                $attribs['child'] = [];
                $this->svgobjs[$soid]['defs'][$attribs['attr']['id']] = [
                    'name' => $name,
                    'attr' => $attribs,
                ];
                return;
            }

            if (end($this->svgobjs[$soid]['defs']) !== false) {
                $last_svgdefs_id = key($this->svgobjs[$soid]['defs']);
                if (
                    !empty($this->svgobjs[$soid]['defs'][$last_svgdefs_id]['attr']['child'])
                    && is_array($this->svgobjs[$soid]['defs'][$last_svgdefs_id]['attr']['child'])
                ) {
                    $attribs['attr']['id'] = 'DF_' .
                    (count($this->svgobjs[$soid]['defs'][$last_svgdefs_id]['attr']['child']) + 1);
                    $this->svgobjs[$soid]['defs'][$last_svgdefs_id]['attr']['child'][$attribs['attr']['id']] = [
                        'name' => $name,
                        'attr' => $attribs,
                    ];
                    return;
                }
            }

            return;
        }

        $this->svgobjs[$soid]['clipmode'] = ($parser == 'clip-path');

        // process style
        $svgstyle = (array) $this->svgobjs[$soid]['styles'][0];
        $prev_svgstyle = (array) $this->svgobjs[$soid]['styles'][max(0, (count($this->svgobjs[$soid]['styles']) - 1))];

        if (
            $this->svgobjs[$soid]['clipmode'] &&
            !isset($attribs['attr']['fill']) &&
            (!isset($attribs['attr']['style']) ||
            (!preg_match('/[;\"\s]{1}fill[\s]*:[\s]*([^;\"]*)/si', $attribs['attr']['style'], $attrval)))
        ) {
            // default fill attribute for clipping
            $attribs['attr']['fill'] = 'none';
        }

        if (
            isset($attribs['attr']['style']) &&
            !empty($attribs['attr']['style']) &&
            ($attribs['attr']['style'][0] != ';')
        ) {
            // fix style for regular expression
            $attribs['attr']['style'] = ';' . $attribs['attr']['style'];
        }

        foreach ($prev_svgstyle as $key => $val) {
            if (in_array($key, self::SVGINHPROP)) {
                // inherit previous value
                $svgstyle[$key] = $val;
            }
            if (!empty($attribs['attr'][$key])) {
                // specific attribute settings
                if ($attribs['attr'][$key] == 'inherit') {
                    $svgstyle[$key] = $val;
                } else {
                    $svgstyle[$key] = $attribs['attr'][$key];
                }
            } elseif (!empty($attribs['attr']['style'])) {
                // CSS style syntax
                $attrval = [];
                if (
                    preg_match(
                        '/[;\"\s]{1}' . $key . '[\s]*:[\s]*([^;\"]*)/si',
                        $attribs['attr']['style'],
                        $attrval
                    )
                    && isset($attrval[1])
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
        if (!empty($attribs['attr']['transform'])) {
            $tmx = $this->graph->getCtmProduct($tmx, $this->getSVGTransformMatrix($attribs['attr']['transform']));
        }

        $svgstyle['transfmatrix'] = $tmx;

        $this->svgobjs[$soid]['textmode']['invisible'] = (
            ($svgstyle['visibility'] == 'hidden') ||
            ($svgstyle['visibility'] == 'collapse') ||
            ($svgstyle['display'] == 'none'));

        /** @var TSVGStyle $svgstyle */
        $svgstyle = (array) $svgstyle;

        // process tags

        match ($name) {
            'defs' => $this->parseSVGTagSTARTdefs($soid),
            'clipPath' => $this->parseSVGTagSTARTclipPath($soid, $tmx),
            'svg' => $this->parseSVGTagSTARTsvg($soid, $attribs['attr'], $svgstyle),
            'g' => $this->parseSVGTagSTARTg($soid, $attribs['attr'], $svgstyle),
            'linearGradient' => $this->parseSVGTagSTARTlinearGradient($soid, $attribs['attr']),
            'radialGradient' => $this->parseSVGTagSTARTradialGradient($soid, $attribs['attr']),
            'stop' => $this->parseSVGTagSTARTstop($soid, $attribs['attr'], $svgstyle),
            'path' => $this->parseSVGTagSTARTpath($soid, $attribs['attr'], $svgstyle),
            'rect' => $this->parseSVGTagSTARTrect($soid, $attribs['attr'], $svgstyle),
            'circle' => $this->parseSVGTagSTARTcircle($soid, $attribs['attr'], $svgstyle),
            'ellipse' => $this->parseSVGTagSTARTellipse($soid, $attribs['attr'], $svgstyle),
            'line' => $this->parseSVGTagSTARTline($soid, $attribs['attr'], $svgstyle),
            'polyline' => $this->parseSVGTagSTARTpolygon($soid, $attribs['attr'], $svgstyle),
            'polygon' => $this->parseSVGTagSTARTpolygon($soid, $attribs['attr'], $svgstyle),
            'image' => $this->parseSVGTagSTARTimage($soid),
            'text' => $this->parseSVGTagSTARTtext($soid, $attribs['attr'], $svgstyle),
            'tspan' => $this->parseSVGTagSTARTtspan($soid, $attribs['attr'], $svgstyle),
            'use' => $this->parseSVGTagSTARTuse($soid, $attribs, $parser),
            default => null,
        };

        // process child elements

        if (empty($attribs['child'])) {
            return;
        }

        $children = $attribs['child'];
        unset($attribs['child']);

        foreach ($children as $child) {
            if (empty($child['attr']) || !is_array($child['attr']) || !is_string($child['name'])) {
                continue;
            }
            if (empty($child['attr']['closing_tag'])) {
                $this->handleSVGTagStart('child-tag', $child['name'], $child['attr']); // @phpstan-ignore argument.type
                continue;
            }
            if (isset($child['attr']['content'])) {
                $this->svgobjs[$soid]['text'] = $child['attr']['content'];
            }
            $this->handleSVGTagEnd('child-tag', $child['name']);
        }
    }

    /**
     * Parse the SVG Start tag 'defs'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return void
     */
    protected function parseSVGTagSTARTdefs(int $soid)
    {
        $this->svgobjs[$soid]['defsmode'] = true;
    }

    /**
     * Parse the SVG Start tag 'clipPath'.
     *
     * @param int $soid ID of the current SVG object.
     * @param array<float> $tmx Current transformation matrix (optional).
     *
     * @return void
     */
    protected function parseSVGTagSTARTclipPath(int $soid, array $tmx = [])
    {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return;
        }

        $this->svgobjs[$soid]['clipmode'] = true;

        if (empty($this->svgobjs[$soid]['clipid'])) {
            $this->svgobjs[$soid]['clipid'] = 'CP_' . (count($this->svgobjs[$soid]['cliptm']) + 1);
        }

        $cid = $this->svgobjs[$soid]['clipid'];
        $this->svgobjs[$soid]['clippaths'][$cid] = [];
        $this->svgobjs[$soid]['cliptm'][$cid] = $tmx;
    }

    /**
     * Parse the SVG Start tag 'svg'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     *
     * @return void
     */
    protected function parseSVGTagSTARTsvg(int $soid, array $attr, array $svgstyle)
    {
        $this->svgobjs[$soid]['tagdepth']++;
        if ($this->svgobjs[$soid]['tagdepth'] <= 1) {
            // root SVG
            return;
        }
        // inner SVG
        array_push($this->svgobjs[$soid]['styles'], $svgstyle);
        $this->svgobjs[$soid]['out'] .= $this->graph->getStartTransform();
        $svgX = isset($attr['x']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['x'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0;
        $svgY = isset($attr['y']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['y'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0;
        $svgW = isset($attr['width']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['width'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0;
        $svgH = isset($attr['height']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['height'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0;
        // set x, y position using transform matrix
        $tmx = $this->graph->getCtmProduct($svgstyle['transfmatrix'], [1.0, 0.0, 0.0, 1.0, $svgX, $svgY]);
        $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($svgstyle['transfmatrix']);
        // set clipping for width and height
        $page = $this->page->getPage();
        $posx = 0;
        $posy = 0;
        $width = empty($svgW) ? ($page['width'] - $svgX) : $svgW;
        $height = empty($svgH) ? ($page['height'] - $svgY) : $svgH;
        // draw clipping rect
        $this->graph->getRawRect(
            $posx,
            $posy,
            $width,
            $height,
            'CNZ',
        );
        $this->parseSVGStyle(
            $this->svgobjs[$soid],
            $posx,
            $posy,
            $width,
            $height,
        );
        // parse viewbox, calculate extra transformation matrix
        if (empty($attr['viewBox'])) {
            $this->parseSVGStyle(
                $this->svgobjs[$soid],
                $posx,
                $posy,
                $width,
                $height,
            );
            return;
        }
        $tmp = [];
        preg_match_all("/[0-9]+/", $attr['viewBox'], $tmp);
        $tmp = $tmp[0];
        if (sizeof($tmp) != 4) {
            $this->parseSVGStyle(
                $this->svgobjs[$soid],
                $posx,
                $posy,
                $width,
                $height,
            );
            return;
        }
        $vbx = floatval($tmp[0]);
        $vby = floatval($tmp[1]);
        $vbw = floatval($tmp[2]);
        $vbh = floatval($tmp[3]);
        // get aspect ratio
        $tmp = [];
        $aspectX = 'xMid';
        $aspectY = 'YMid';
        $fit = 'meet';
        if (!empty($attr['preserveAspectRatio'])) {
            if ($attr['preserveAspectRatio'] == 'none') {
                $fit = 'none';
            } else {
                preg_match_all('/[a-zA-Z]+/', $attr['preserveAspectRatio'], $tmp);
                $tmp = $tmp[0];
                if (
                    (sizeof($tmp) == 2)
                    && (strlen($tmp[0]) == 8)
                    && (in_array(
                        $tmp[1],
                        array('meet', 'slice', 'none')
                    ))
                ) {
                    $aspectX = substr($tmp[0], 0, 4);
                    $aspectY = substr($tmp[0], 4, 4);
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
        $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($tmx);
        $this->parseSVGStyle(
            $this->svgobjs[$soid],
            $posx,
            $posy,
            $width,
            $height,
        );
    }

    /**
     * Parse the SVG Start tag 'g'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     *
     * @return void
     */
    protected function parseSVGTagSTARTg(int $soid, array $attr, array $svgstyle)
    {
        array_push($this->svgobjs[$soid]['styles'], $svgstyle);
        $this->svgobjs[$soid]['out'] .= $this->graph->getStartTransform();
        $posx = isset($attr['x']) ? $this->toUnit($this->getUnitValuePoints($attr['x'])) : 0.0;
        $posy = isset($attr['y']) ? $this->toUnit($this->getUnitValuePoints($attr['y'])) : 0.0;
        $width = 1.0; // isset($attr['width']) ? $this->toUnit($this->getUnitValuePoints($attr['width'])) : 1.0;
        $height = 1.0; // isset($attr['height']) ? $this->toUnit($this->getUnitValuePoints($attr['height'])) : 1.0;
        $tmx = $this->graph->getCtmProduct(
            $svgstyle['transfmatrix'],
            [$width, 0.0, 0.0, $height, $posx, $posy]
        );
        $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($tmx);
        $this->svgobjs[$soid]['out'] .= $this->parseSVGStyle(
            $this->svgobjs[$soid],
            $posx,
            $posy,
            $width,
            $height,
        );
    }

    /**
     * Parse the SVG Start tag 'linearGradient'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     *
     * @return void
     */
    protected function parseSVGTagSTARTlinearGradient(int $soid, array $attr)
    {
        if (($this->pdfa == 1) || ($this->pdfa == 2)) {
            return;
        }

        if (!isset($attr['id'])) {
            $attr['id'] = 'GR_' . (count($this->svgobjs[$soid]['gradients']) + 1);
        }
        $gid = $attr['id'];
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
        $ref = self::REFUNITVAL;
        if (
            ((!isset($attr['x1'])) && (!isset($attr['y1']))
            && (!isset($attr['x2'])) && (!isset($attr['y2'])))
            || ((isset($attr['x1']) && (substr($attr['x1'], -1) == '%'))
            || (isset($attr['y1']) && (substr($attr['y1'], -1) == '%'))
            || (isset($attr['x2']) && (substr($attr['x2'], -1) == '%'))
            || (isset($attr['y2']) && (substr($attr['y2'], -1) == '%')))
        ) {
            $this->svgobjs[$soid]['gradients'][$gid]['mode'] = 'percentage';
            $ref['parent'] = 100.0;
        } else {
            $this->svgobjs[$soid]['gradients'][$gid]['mode'] = 'measure';
        }
        $px1 = isset($attr['x1']) ? $this->toUnit($this->getUnitValuePoints($attr['x1'], $ref, self::SVGUNIT)) : 0.0;
        $py1 = isset($attr['y1']) ? $this->toUnit($this->getUnitValuePoints($attr['y1'], $ref, self::SVGUNIT)) : 0.0;
        $px2 = isset($attr['x2']) ? $this->toUnit($this->getUnitValuePoints($attr['x2'], $ref, self::SVGUNIT)) : 100.0;
        $py2 = isset($attr['y2']) ? $this->toUnit($this->getUnitValuePoints($attr['y2'], $ref, self::SVGUNIT)) : 0.0;
        if (isset($attr['gradientTransform'])) {
            $this->svgobjs[$soid]['gradients'][$gid]['gradientTransform'] =
                $this->getSVGTransformMatrix($attr['gradientTransform']);
        }
        $this->svgobjs[$soid]['gradients'][$gid]['coords'] = [$px1, $py1, $px2, $py2];
        if (isset($attr['xlink:href']) && !empty($attr['xlink:href'])) {
            // gradient is defined on another place
            $this->svgobjs[$soid]['gradients'][$gid]['xref'] = substr($attr['xlink:href'], 1);
        }
    }

    /**
     * Parse the SVG Start tag 'radialGradient'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     *
     * @return void
     */
    protected function parseSVGTagSTARTradialGradient(int $soid, array $attr)
    {
        if (($this->pdfa == 1) || ($this->pdfa == 2)) {
            return;
        }

        if (!isset($attr['id'])) {
            $attr['id'] = 'GR_' . (count($this->svgobjs[$soid]['gradients']) + 1);
        }
        $gid = $attr['id'];
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
        $ref = self::REFUNITVAL;
        if (
            ((!isset($attr['cx'])) && (!isset($attr['cy'])))
            || ((isset($attr['cx']) && (substr($attr['cx'], -1) == '%'))
            || (isset($attr['cy']) && (substr($attr['cy'], -1) == '%')))
        ) {
            $this->svgobjs[$soid]['gradients'][$gid]['mode'] = 'percentage';
        } elseif (isset($attr['r']) && is_numeric($attr['r']) && ($attr['r']) <= 1) {
            $this->svgobjs[$soid]['gradients'][$gid]['mode'] = 'ratio';
            $ref['parent'] = 100.0;
        } else {
            $this->svgobjs[$soid]['gradients'][$gid]['mode'] = 'measure';
        }
        $pcx = isset($attr['cx']) ? $this->toUnit($this->getUnitValuePoints($attr['cx'], $ref, self::SVGUNIT)) : 0.5;
        $pcy = isset($attr['cy']) ? $this->toUnit($this->getUnitValuePoints($attr['cy'], $ref, self::SVGUNIT)) : 0.5;
        $pfx = isset($attr['fx']) ? $this->toUnit($this->getUnitValuePoints($attr['fx'], $ref, self::SVGUNIT)) : $pcx;
        $pfy = isset($attr['fy']) ? $this->toUnit($this->getUnitValuePoints($attr['fy'], $ref, self::SVGUNIT)) : $pcy;
        $grr = isset($attr['r']) ? $this->toUnit($this->getUnitValuePoints($attr['r'], $ref, self::SVGUNIT)) : 0.5;
        if (isset($attr['gradientTransform'])) {
            $this->svgobjs[$soid]['gradients'][$gid]['gradientTransform'] =
                $this->getSVGTransformMatrix($attr['gradientTransform']);
        }
        $this->svgobjs[$soid]['gradients'][$gid]['coords'] = [$pcx, $pcy, $pfx, $pfy, $grr];
        if (isset($attr['xlink:href']) && !empty($attr['xlink:href'])) {
            // gradient is defined on another place
            $this->svgobjs[$soid]['gradients'][$gid]['xref'] = substr($attr['xlink:href'], 1);
        }
    }

    /**
     * Parse the SVG Start tag 'stop'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     *
     * @return void
     */
    protected function parseSVGTagSTARTstop(int $soid, array $attr, array $svgstyle)
    {
        $offset = isset($attr['offset']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['offset'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0;
        $stop_color = isset($svgstyle['stop-color']) ? $this->color->getColorObj($svgstyle['stop-color']) : 'black';
        $opacity = isset($svgstyle['stop-opacity']) ? min(
            0.0,
            max(
                1.0,
                floatval($svgstyle['stop-opacity'])
            )
        ) : 1.0;
        $gid = $this->svgobjs[$soid]['gradientid'];
        $this->svgobjs[$soid]['gradients'][$gid]['stops'][] = [
            'offset' => $offset,
            'color' => $stop_color,
            'opacity' => $opacity,
        ];
    }

    /**
     * Parse the SVG Start tag 'path'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     *
     * @return void
     */
    protected function parseSVGTagSTARTpath(int $soid, array $attr, array $svgstyle)
    {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return;
        }
        if (empty($attr['d'])) {
            return;
        }

        $ptd = trim($attr['d']);
        $posx = isset($attr['x']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['x'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0;
        $posy = isset($attr['y']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['y'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0;
        $width = isset($attr['width']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['width'], self::REFUNITVAL, self::SVGUNIT)
        ) : 1.0;
        $height = isset($attr['height']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['height'], self::REFUNITVAL, self::SVGUNIT)
        ) : 1.0;
        $tmx = $this->graph->getCtmProduct(
            $svgstyle['transfmatrix'],
            [$width, 0.0, 0.0, $height, $posx, $posy]
        );

        if ($this->svgobjs[$soid]['clipmode']) {
            $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($tmx);
            $this->svgobjs[$soid]['out'] .= $this->getSVGPath($ptd, 'CNZ');
            return;
        }

        $this->svgobjs[$soid]['out'] .= $this->graph->getStartTransform();
        $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($tmx);
        $obstyle = $this->parseSVGStyle(
            $this->svgobjs[$soid],
            $posx,
            $posy,
            $width,
            $height,
            'getSVGPath',
            [$ptd, 'CNZ'],
        );
        if (!empty($obstyle)) {
            $this->svgobjs[$soid]['out'] .= $this->getSVGPath($ptd, $obstyle);
        }
        $this->svgobjs[$soid]['out'] .= $this->graph->getStopTransform();
    }

    /**
     * Parse the SVG Start tag 'rect'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     *
     * @return void
     */
    protected function parseSVGTagSTARTrect(int $soid, array $attr, array $svgstyle)
    {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return;
        }
        $posx = (isset($attr['x']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['x'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0);
        $posy = (isset($attr['y']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['y'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0);
        $width = (isset($attr['width']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['width'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0);
        $height = (isset($attr['height']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['height'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0);
        $prx = (isset($attr['rx']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['rx'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0);
        $pry = (isset($attr['ry']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['ry'], self::REFUNITVAL, self::SVGUNIT)
        ) : $prx);
        if ($this->svgobjs[$soid]['clipmode']) {
            $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($svgstyle['transfmatrix']);
            $this->svgobjs[$soid]['out'] .= $this->graph->getRoundedRect(
                $posx,
                $posy,
                $width,
                $height,
                $prx,
                $pry,
                '1111',
                'CNZ',
            );
            return;
        }
        $this->svgobjs[$soid]['out'] .= $this->graph->getStartTransform();
        $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($svgstyle['transfmatrix']);
        $obstyle = $this->parseSVGStyle(
            $this->svgobjs[$soid],
            $posx,
            $posy,
            $width,
            $height,
            'getRoundedRect',
            [$posx, $posy, $width, $height, $prx, $pry, '1111', 'CNZ'],
        );
        if (!empty($obstyle)) {
            $this->svgobjs[$soid]['out'] .= $this->graph->getRoundedRect(
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
        $this->svgobjs[$soid]['out'] .= $this->graph->getStopTransform();
    }

    /**
     * Parse the SVG Start tag 'circle'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     *
     * @return void
     */
    protected function parseSVGTagSTARTcircle(int $soid, array $attr, array $svgstyle)
    {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return;
        }
        $crr = (isset($attr['r']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['r'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0);
        $ctx = (isset($attr['cx']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['cx'], self::REFUNITVAL, self::SVGUNIT)
        ) : (isset($attr['x']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['x'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0));
        $cty = (isset($attr['cy']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['cy'], self::REFUNITVAL, self::SVGUNIT)
        ) : (isset($attr['y']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['y'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0));
        $posx = ($ctx - $crr);
        $posy = ($cty - $crr);
        $width = (2 * $crr);
        $height = $width;
        if ($this->svgobjs[$soid]['clipmode']) {
            $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($svgstyle['transfmatrix']);
            $this->svgobjs[$soid]['out'] .= $this->graph->getCircle(
                $ctx,
                $cty,
                $crr,
                0,
                360,
                'CNZ',
                [],
                8
            );
            return;
        }
        $this->svgobjs[$soid]['out'] .= $this->graph->getStartTransform();
        $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($svgstyle['transfmatrix']);
        $obstyle = $this->parseSVGStyle(
            $this->svgobjs[$soid],
            $posx,
            $posy,
            $width,
            $height,
            'getCircle',
            [$ctx, $cty, $crr, 0, 360, 'CNZ'],
        );
        if (!empty($obstyle)) {
            $this->svgobjs[$soid]['out'] .= $this->graph->getCircle(
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
        $this->svgobjs[$soid]['out'] .= $this->graph->getStopTransform();
    }

    /**
     * Parse the SVG Start tag 'ellipse'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     *
     * @return void
     */
    protected function parseSVGTagSTARTellipse(int $soid, array $attr, array $svgstyle)
    {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return;
        }
        $erx = (isset($attr['rx']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['rx'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0);
        $ery = (isset($attr['ry']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['ry'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0);
        $ecx = (isset($attr['cx']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['cx'], self::REFUNITVAL, self::SVGUNIT)
        ) : (isset($attr['x']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['x'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0));
        $ecy = (isset($attr['cy']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['cy'], self::REFUNITVAL, self::SVGUNIT)
        ) : (isset($attr['y']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['y'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0));
        $posx = ($ecx - $erx);
        $posy = ($ecy - $ery);
        $width = (2 * $erx);
        $height = (2 * $ery);
        if ($this->svgobjs[$soid]['clipmode']) {
            $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($svgstyle['transfmatrix']);
            $this->svgobjs[$soid]['out'] .= $this->graph->getEllipse(
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
            return;
        }
        $this->svgobjs[$soid]['out'] .= $this->graph->getStartTransform();
        $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($svgstyle['transfmatrix']);
        $obstyle = $this->parseSVGStyle(
            $this->svgobjs[$soid],
            $posx,
            $posy,
            $width,
            $height,
            'getEllipse',
            [$ecx, $ecy, $erx, $ery, 0, 0, 360, 'CNZ'],
        );
        if (!empty($obstyle)) {
            $this->svgobjs[$soid]['out'] .= $this->graph->getEllipse(
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
        $this->svgobjs[$soid]['out'] .= $this->graph->getStopTransform();
    }

    /**
     * Parse the SVG Start tag 'line'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     *
     * @return void
     */
    protected function parseSVGTagSTARTline(int $soid, array $attr, array $svgstyle)
    {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return;
        }
        if ($this->svgobjs[$soid]['clipmode']) {
            return;
        }
        $posx1 = (isset($attr['x1']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['x1'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0);
        $posy1 = (isset($attr['y1']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['y1'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0);
        $posx2 = (isset($attr['x2']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['x2'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0);
        $posy2 = (isset($attr['y2']) ? $this->toUnit(
            $this->getUnitValuePoints($attr['y2'], self::REFUNITVAL, self::SVGUNIT)
        ) : 0.0);
        $posx = $posx1;
        $posy = $posy1;
        $width = abs($posx2 - $posx1);
        $height = abs($posy2 - $posy1);
        $this->svgobjs[$soid]['out'] .= $this->graph->getStartTransform();
        $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($svgstyle['transfmatrix']);
        $this->parseSVGStyle(
            $this->svgobjs[$soid],
            $posx,
            $posy,
            $width,
            $height,
            'getLine',
            [$posx1, $posy1, $posx2, $posy2],
        );
        $this->svgobjs[$soid]['out'] .= $this->graph->getLine(
            $posx1,
            $posy1,
            $posx2,
            $posy2,
        );
        $this->svgobjs[$soid]['out'] .= $this->graph->getStopTransform();
    }

    /**
     * Parse the SVG Start tag 'polygon'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     *
     * @return void
     */
    protected function parseSVGTagSTARTpolygon(int $soid, array $attr, array $svgstyle)
    {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return;
        }
        $attrpoints = (!empty($attr['points']) ? trim($attr['points']) : '0 0');
        // note that point may use a complex syntax not covered here
        $points = preg_split('/[\,\s]+/si', $attrpoints);
        if (!is_array($points) || count($points) < 4) {
            return;
        }
        $pset = [];
        $xmin = self::SVGMAXVAL;
        $xmax = 0.0;
        $ymin = self::SVGMAXVAL;
        $ymax = 0.0;
        foreach ($points as $key => $val) {
            $pset[$key] = $this->toUnit(
                $this->getUnitValuePoints($val, self::REFUNITVAL, self::SVGUNIT)
            );
            if (($key % 2) == 0) {
                // X coordinate
                $xmin = min($xmin, $pset[$key]);
                $xmax = max($xmax, $pset[$key]);
            } else {
                // Y coordinate
                $ymin = min($ymin, $pset[$key]);
                $ymax = max($ymax, $pset[$key]);
            }
        }
        $posx = $xmin;
        $posy = $ymin;
        $width = ($xmax - $xmin);
        $height = ($ymax - $ymin);
        if ($this->svgobjs[$soid]['clipmode']) {
            $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($svgstyle['transfmatrix']);
            $this->svgobjs[$soid]['out'] .= $this->graph->getPolygon(
                $pset,
                'CNZ',
            );
            return;
        }
        $this->svgobjs[$soid]['out'] .= $this->graph->getStartTransform();
        $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($svgstyle['transfmatrix']);
        $obstyle = $this->parseSVGStyle(
            $this->svgobjs[$soid],
            $posx,
            $posy,
            $width,
            $height,
            'getPolygon',
            [$pset, 'CNZ']
        );
        if (!empty($obstyle)) {
            $this->svgobjs[$soid]['out'] .= $this->graph->getPolygon(
                $pset,
                $obstyle,
            );
        }
        $this->svgobjs[$soid]['out'] .= $this->graph->getStopTransform();
    }

    /**
     * Parse the SVG Start tag 'image'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return void
     */
    protected function parseSVGTagSTARTimage(int $soid)
    {
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return;
        }
        $soid = $soid; // @phpstan-ignore-line
        //@TODO
    }

    /**
     * Parse the SVG Start tag 'text'.
     * Basic support only.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     *
     * @return void
     */
    protected function parseSVGTagSTARTtext(int $soid, array $attr, array $svgstyle, bool $is_tspan = false)
    {
        if (isset($this->svgobjs[$soid]['textmode']['text-anchor']) && !empty($this->svgobjs[$soid]['text'])) {
            // @TODO: unsupported feature
        }
        if (!empty($this->svgobjs[$soid]['textmode']['invisible'])) {
            return;
        }
        array_push($this->svgobjs[$soid]['styles'], $svgstyle);
        $posx = 0.0;
        $posy = 0.0;
        if (isset($attr['x'])) {
            $posx = $this->toUnit($this->getUnitValuePoints($attr['x'], self::REFUNITVAL, self::SVGUNIT));
        } elseif ($is_tspan) {
            $posx = $this->svgobjs[$soid]['x'];
        }
        if (isset($attr['dx'])) {
            $posx += $this->toUnit($this->getUnitValuePoints($attr['dx'], self::REFUNITVAL, self::SVGUNIT));
        }
        if (isset($attr['y'])) {
            $posy = $this->toUnit($this->getUnitValuePoints($attr['y'], self::REFUNITVAL, self::SVGUNIT));
        } elseif ($is_tspan) {
            $posy = $this->svgobjs[$soid]['y'];
        }
        if (isset($attr['dy'])) {
            $posy += $this->toUnit($this->getUnitValuePoints($attr['dy'], self::REFUNITVAL, self::SVGUNIT));
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
            $this->svgobjs[$soid]['textmode']['stroke'] = $this->toUnit(
                $this->getUnitValuePoints($svgstyle['stroke-width'], self::REFUNITVAL, self::SVGUNIT)
            );
        } else {
            $this->svgobjs[$soid]['textmode']['stroke'] = false;
        }
        $this->svgobjs[$soid]['out'] .= $this->graph->getStartTransform();
        $this->svgobjs[$soid]['out'] .= $this->getOutSVGTransformation($svgstyle['transfmatrix']);
        $this->parseSVGStyle(
            $this->svgobjs[$soid], // @phpstan-ignore-line argument.type
            $posx,
            $posy,
            1,
            1
        );
        $this->svgobjs[$soid]['x'] = $posx;
        $this->svgobjs[$soid]['y'] = $posy;
    }

    /**
     * Parse the SVG Start tag 'tspan'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     *
     * @return void
     */
    protected function parseSVGTagSTARTtspan(int $soid, array $attr, array $svgstyle)
    {
        $this->parseSVGTagSTARTtext($soid, $attr, $svgstyle, true);
    }

    /**
     * Parse the SVG Start tag 'use'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttribs $attribs Associative array with the element's attributes.
     * @param string $parser The XML parser calling the handler.
     *
     * @return void
     */
    protected function parseSVGTagSTARTuse(int $soid, array $attribs, string $parser)
    {
        $attr = $attribs['attr'];
        if (empty($attr['xlink:href'])) {
            return;
        }
        $svgdefid = substr($attr['xlink:href'], 1);
        if (empty($this->svgobjs[$soid]['defs'][$svgdefid])) {
            return;
        }
        $use = $this->svgobjs[$soid]['defs'][$svgdefid];
        if (isset($attr['xlink:href'])) {
            unset($attr['xlink:href']);
        }
        if (isset($attr['id'])) {
            unset($attr['id']);
        }
        if (isset($use['attr']['attr']['x']) && isset($attr['x'])) {
            $attr['x'] = strval(floatval($attr['x']) + floatval($use['attr']['attr']['x']));
        }
        if (isset($use['attr']['attr']['y']) && isset($attr['y'])) {
            $attr['y'] = strval(floatval($attr['y']) + floatval($use['attr']['attr']['y']));
        }
        if (empty($attr['style'])) {
            $attr['style'] = '';
        }
        if (!empty($use['attr']['attr']['style'])) {
            // merge styles
            $attr['style'] = str_replace(';;', ';', ';' . $use['attr']['attr']['style'] . $attr['style']);
        }
        $attribs['attr'] = array_merge($use['attr']['attr'], $attr);
        /** @var  TSVGAttribs $attribs */
        $attribs = (array) $attribs;
        $this->handleSVGTagStart($parser, $use['name'], $attribs);
    }
}
