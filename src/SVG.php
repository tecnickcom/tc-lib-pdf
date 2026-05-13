<?php

declare(strict_types=1);

/**
 * SVG.php
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
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
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
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
 *    'viewBox': array{
 *       float,
 *       float,
 *       float,
 *       float
 *    },
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
 *    'mix-blend-mode': string,
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
 *    'vertical'?: bool,
 *    'linkhref'?: string,
 *    'linkx'?: float,
 *    'linky'?: float,
 *    'baseline'?: string,
 *    'rotate'?: float,
 *    'textlength'?: float,
 *    'lengthadjust'?: string,
 *    'xlist'?: array<int, float>,
 *    'ylist'?: array<int, float>,
 *    'rotlist'?: array<int, float>,
 *    'textpathpoints'?: array<int, array{
 *       0: float,
 *       1: float
 *    }>,
 *    'textpathoffset'?: float,
 *    'textpathmethod'?: string,
 *    'textpathspacing'?: string,
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
 * @phpstan-type TSVGSwitchState array{
 *    'depth': int,
 *    'selected': bool,
 *    'skipdepth': int,
 * }
 *
 * @phpstan-type TSVGObj array{
 *    'defsmode': bool,
 *    'clipmode': bool,
 *    'clipid': int|string,
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
 *    'xmldepth': int,
 *    'switchstack'?: array<int, TSVGSwitchState>,
 *    'markermode': int,
 *    'patternmode': int,
 *    'textmode': TSVGTextMode,
 *    'charskip': int,
 *    'text': string,
 *    'dir': string,
 *    'out': string,
 * }
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class SVG extends \Com\Tecnick\Pdf\Text
{
    /**
     * Tags whose character data must not be rendered as drawing text.
     *
     * @var array<int, string>
     */
    protected const SVGCHARDATASKIPTAGS = [
        'desc',
        'title',
        'metadata',
        'style',
        'script',
        // SVG filter primitives — PDF has no equivalent pixel-pipeline; entire
        // subtree content is discarded to avoid garbled output.
        'filter',
        'feBlend',
        'feColorMatrix',
        'feComponentTransfer',
        'feComposite',
        'feConvolveMatrix',
        'feDiffuseLighting',
        'feDisplacementMap',
        'feDistantLight',
        'feDropShadow',
        'feFlood',
        'feFuncA',
        'feFuncB',
        'feFuncG',
        'feFuncR',
        'feGaussianBlur',
        'feImage',
        'feMerge',
        'feMergeNode',
        'feMorphology',
        'feOffset',
        'fePointLight',
        'feSpecularLighting',
        'feSpotLight',
        'feTile',
        'feTurbulence',
    ];

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
     * Default SVG minimum float diff.
     *
     * @var float
     */
    protected const SVGMINFLOATDIFF = 0.000_01;

    /**
     * Default SVG maximum value for float.
     *
     * @var float
     */
    protected const SVGMAXVAL = 2_147_483_647.0;

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
        'mix-blend-mode',
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
        'mix-blend-mode' => 'normal',
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
     *
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
        'symbol',
        'marker',
        'pattern',
        'mask',
        'filter',
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
        'symbol',
        'marker',
        'pattern',
        'mask',
        'filter',
    ];

    /**
     * Default SVG object properties.
     *
     * @var TSVGObj
     */
    protected const SVGDEFOBJ = [
        'defsmode' => false,
        'clipmode' => false,
        'clipid' => '',
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
        'xmldepth' => 0,
        'switchstack' => [],
        'markermode' => 0,
        'patternmode' => 0,
        'textmode' => [
            'rtl' => false,
            'invisible' => false,
            'stroke' => 0,
            'text-anchor' => 'start',
            'vertical' => false,
            'linkhref' => '',
            'linkx' => 0.0,
            'linky' => 0.0,
        ],
        'charskip' => 0,
        'text' => '',
        'dir' => '',
        'out' => '',
    ];

    /**
     * Map SVG blend mode names to PDF names.
     *
     * @var array<string, string>
     */
    protected const SVGBLENDMODE = [
        'color-dodge' => 'ColorDodge',
        'color-burn' => 'ColorBurn',
        'hard-light' => 'HardLight',
        'soft-light' => 'SoftLight',
        'normal' => 'Normal',
        'multiply' => 'Multiply',
        'screen' => 'Screen',
        'overlay' => 'Overlay',
        'darken' => 'Darken',
        'lighten' => 'Lighten',
        'difference' => 'Difference',
        'exclusion' => 'Exclusion',
        'hue' => 'Hue',
        'saturation' => 'Saturation',
        'color' => 'Color',
        'luminosity' => 'Luminosity',
    ];

    /**
     * SVG gradient attributes.
     *
     * @var array<string>
     */
    protected const SVGGRADIENTATTRIB = [
        'id',
        'x1',
        'y1',
        'x2',
        'y2',
        'cx',
        'cy',
        'fx',
        'fy',
        'r',
        'offset',
        'gradientUnits',
        'gradientTransform',
        'xlink:href',
        'href',
        'stop-color',
        'stop-opacity',
        'style',
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
        if ($ref === null) {
            if ($soid > 0 && isset($this->svgobjs[$soid]['refunitval']) && $this->svgobjs[$soid]['refunitval'] !== []) {
                $ref = $this->svgobjs[$soid]['refunitval'];
            } else {
                $ref = self::REFUNITVAL;
            }
        }
        return $this->getUnitValuePoints($val, $ref, self::SVGUNIT);
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
        if (\preg_match(
            '/([a-z0-9\-\.]+)[\,\s]+'
            . '([a-z0-9\-\.]+)[\,\s]+'
            . '([a-z0-9\-\.]+)[\,\s]+'
            . '([a-z0-9\-\.]+)[\,\s]+'
            . '([a-z0-9\-\.]+)[\,\s]+'
            . '([a-z0-9\-\.]+)/si',
            $val,
            $regs,
        )) {
            $tmb[0] = \floatval($regs[1] ?? 0);
            $tmb[1] = \floatval($regs[2] ?? 0);
            $tmb[2] = \floatval($regs[3] ?? 0);
            $tmb[3] = \floatval($regs[4] ?? 0);
            $tmb[4] = \floatval($regs[5] ?? 0);
            $tmb[5] = \floatval($regs[6] ?? 0);
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
            $tmb[4] = \floatval($regs[1] ?? 0);
            $tmb[5] = \floatval($regs[2] ?? 0);
            return $tmb;
        }
        if (\preg_match('/([a-z0-9\-\.]+)/si', $val, $regs)) {
            $tmb[4] = \floatval($regs[1] ?? 0);
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
            $tmb[0] = \floatval($regs[1] ?? 0);
            $tmb[3] = \floatval($regs[2] ?? 0);
            return $tmb;
        }
        if (\preg_match('/([a-z0-9\-\.]+)/si', $val, $regs)) {
            $tmb[0] = \floatval($regs[1] ?? 0);
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
            $ang = \deg2rad(\floatval($regs[1] ?? 0));
            $trx = \floatval($regs[2] ?? 0);
            $try = \floatval($regs[3] ?? 0);
            $tmb[0] = \cos($ang);
            $tmb[1] = \sin($ang);
            $tmb[2] = -$tmb[1];
            $tmb[3] = $tmb[0];
            $tm0 = $tmb[0];
            $tm1 = $tmb[1];
            $tm2 = $tmb[2];
            $tm3 = $tmb[3];
            $tmb[4] = ($trx * (1 - $tm0)) - ($try * $tm2);
            $tmb[5] = ($try * (1 - $tm3)) - ($trx * $tm1);
            return $tmb;
        }
        if (\preg_match('/([0-9\-\.]+)/si', $val, $regs)) {
            $ang = \deg2rad(\floatval($regs[1] ?? 0));
            $tmb[0] = \cos($ang);
            $tmb[1] = \sin($ang);
            $tmb[2] = -$tmb[1];
            $tmb[3] = $tmb[0];
            $tmb[4] = 0.0;
            $tmb[5] = 0.0;
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
            $tmb[2] = \tan(\deg2rad(\floatval($regs[1] ?? 0)));
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
            $tmb[1] = \tan(\deg2rad(\floatval($regs[1] ?? 0)));
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

        $matchCount = \preg_match_all(
            '/(matrix|translate|scale|rotate|skewX|skewY)[\s]*+\(([^\)]+)\)/si',
            $attr,
            $transform,
            PREG_SET_ORDER,
        );
        if ($matchCount === false || $matchCount === 0) {
            return $tma;
        }

        foreach ($transform as $data) {
            if (!isset($data[2]) || $data[2] === '') {
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
    protected function convertSVGMatrix(array $trm, int $soid = 0): array
    {
        $ref = $this->svgobjs[$soid]['refunitval'] ?? self::REFUNITVAL;
        $pheight = $ref['page']['height'];
        $tm1 = $trm[1];
        $tm2 = $trm[2];
        $tm3 = $trm[3];
        $tm4 = $trm[4];
        $tm5 = $trm[5];
        $trm[1] = -$tm1;
        $trm[2] = -$tm2;
        $trm[4] = $this->svgUnitToPoints($tm4, $soid) - ($pheight * $trm[2]);
        $trm[5] = ($pheight * (1 - $tm3)) - $this->svgUnitToPoints($tm5, $soid);
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
    protected function getOutSVGTransformation(array $trm, int $soid = 0): string
    {
        return $this->graph->getTransformation($this->convertSVGMatrix($trm, $soid));
    }

    /**
     * Return the tag name without the namespace
     *
     * @param string $name Tag name
     *
     * @return string Tag name without the namespace
     */
    protected function removeTagNamespace(string $name): string
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
    protected function getSVGPath(int $soid, string $attrd, string $mode = ''): string
    {
        // set paint operator
        $pop = $this->graph->getPathPaintOp($mode, '');
        if ($pop === '') {
            return '';
        }

        // extract paths
        $attrd = \preg_replace('/([0-9ACHLMQSTVZ])([\-\+])/si', '\\1 \\2', $attrd);
        if (!\is_string($attrd) || $attrd === '') {
            return '';
        }

        $attrd = \preg_replace('/(\.[0-9]+)(\.)/s', '\\1 \\2', $attrd);
        if (!\is_string($attrd) || $attrd === '') {
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
            if (!isset($val[1], $val[2])) {
                continue;
            }
            // get curve type
            $cmd = \trim($val[1]);

            // relative or absolute coordinates
            $coord['relcoord'] = \strtolower($cmd) === $cmd;
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
            $rawparams = \is_array($rprms[0] ?? null) ? $rprms[0] : [];

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

        if ($out === '') {
            return '';
        }

        $this->bbox[] = [
            'x' => $coord['xmin'],
            'y' => $coord['ymin'],
            'w' => $coord['xmax'] - $coord['xmin'],
            'h' => $coord['ymax'] - $coord['ymin'],
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
            $idx = (int) $prk;
            if ((($idx + 1) % 7) !== 0) {
                continue;
            }

            $crd['x0'] = $crd['x'];
            $crd['y0'] = $crd['y'];
            $rpx = \max(\abs($prm[$idx - 6] ?? 0.0), .000_000_001);
            $rpy = \max(\abs($prm[$idx - 5] ?? 0.0), .000_000_001);
            $ang = -\intval($rawparams[$idx - 4] ?? '0');
            $angle = \deg2rad($ang);
            $laf = (int) ($rawparams[$idx - 3] ?? 0); // large-arc-flag
            $swf = (int) ($rawparams[$idx - 2] ?? 0); // sweep-flag
            $crd['x'] = ($prm[$idx - 1] ?? 0.0) + $crd['xoffset'];
            $crd['y'] = $prv + $crd['yoffset'];

            if (
                \abs($crd['x0'] - $crd['x']) < $this->svgminunitlen
                && \abs($crd['y0'] - $crd['y']) < $this->svgminunitlen
            ) {
                // endpoints are almost identical
                $crd['xmin'] = \min($crd['xmin'], $crd['x']);
                $crd['ymin'] = \min($crd['ymin'], $crd['y']);
                $crd['xmax'] = \max($crd['xmax'], $crd['x']);
                $crd['ymax'] = \max($crd['ymax'], $crd['y']);
            } else {
                $cos_ang = \cos($angle);
                $sin_ang = \sin($angle);
                $cra = ($crd['x0'] - $crd['x']) / 2;
                $crb = ($crd['y0'] - $crd['y']) / 2;
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
                $numerator = ($rx2 * $ry2) - ($rx2 * $pya2) - ($ry2 * $pxa2);
                $root = 0;
                if ($numerator > 0) {
                    $root = \sqrt($numerator / (($rx2 * $pya2) + ($ry2 * $pxa2)));
                }
                if ($laf === $swf) {
                    $root *= -1;
                }
                $cax = $root * (($rpx * $pya) / $rpy);
                $cay = -$root * (($rpy * $pxa) / $rpx);
                // coordinates of ellipse center
                $pcx = ($cax * $cos_ang) - ($cay * $sin_ang) + (($crd['x0'] + $crd['x']) / 2);
                $pcy = ($cax * $sin_ang) + ($cay * $cos_ang) + (($crd['y0'] + $crd['y']) / 2);
                // get angles
                $angs = $this->graph->getVectorsAngle(1, 0, ($pxa - $cax) / $rpx, ($cay - $pya) / $rpy);
                $dang = $this->graph->getVectorsAngle(
                    ($pxa - $cax) / $rpx,
                    ($pya - $cay) / $rpy,
                    (-$pxa - $cax) / $rpx,
                    (-$pya - $cay) / $rpy,
                );
                if ($swf === 0 && $dang > 0) {
                    $dang -= 2 * M_PI;
                } elseif ($swf === 1 && $dang < 0) {
                    $dang += 2 * M_PI;
                }
                $angf = $angs - $dang;
                if ($swf === 0 && $angs > $angf || $swf === 1 && $angs < $angf) {
                    // reverse angles
                    $tmp = $angs;
                    $angs = $angf;
                    $angf = $tmp;
                }
                $angs = \round(\rad2deg($angs), 6);
                $angf = \round(\rad2deg($angf), 6);
                // covent angles to positive values
                if ($angs < 0 && $angf < 0) {
                    $angs += 360;
                    $angf += 360;
                }
                $pie = false;
                $nextCmd = $paths[$key + 1][1] ?? '';
                if ($key === 0 && \trim($nextCmd) === 'z') {
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
                    $swf === 0,
                    true,
                    $bbox,
                );
                $crd['xmin'] = (float) \min($crd['xmin'], $crd['x'], $bbox[0] ?? $crd['x']);
                $crd['ymin'] = (float) \min($crd['ymin'], $crd['y'], $bbox[1] ?? $crd['y']);
                $crd['xmax'] = (float) \max($crd['xmax'], $crd['x'], $bbox[2] ?? $crd['x']);
                $crd['ymax'] = (float) \max($crd['ymax'], $crd['y'], $bbox[3] ?? $crd['y']);
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
            $idx = (int) $prk;
            if ((($idx + 1) % 6) !== 0) {
                continue;
            }
            $px1 = ($prm[$idx - 5] ?? 0.0) + $crd['xoffset'];
            $py1 = ($prm[$idx - 4] ?? 0.0) + $crd['yoffset'];
            $px2 = ($prm[$idx - 3] ?? 0.0) + $crd['xoffset'];
            $py2 = ($prm[$idx - 2] ?? 0.0) + $crd['yoffset'];
            $crd['x'] = ($prm[$idx - 1] ?? 0.0) + $crd['xoffset'];
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
                \abs($crd['x0'] - $crd['x']) >= $this->svgminunitlen
                || \abs($crd['y0'] - $crd['y']) >= $this->svgminunitlen
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
            if (!\is_int($prk)) {
                continue;
            }

            if (($prk % 2) === 0) {
                $crd['x'] = $prv + $crd['xoffset'];
                continue;
            }

            $crd['y'] = $prv + $crd['yoffset'];
            if (
                \abs($crd['x0'] - $crd['x']) >= $this->svgminunitlen
                || \abs($crd['y0'] - $crd['y']) >= $this->svgminunitlen
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
            if (!\is_int($prk)) {
                continue;
            }

            if (($prk % 2) === 0) {
                $crd['x'] = $prv + $crd['xoffset'];
                continue;
            }

            $crd['y'] = $prv + $crd['yoffset'];
            if (
                $crd['firstcmd']
                || \abs($crd['x0'] - $crd['x']) >= $this->svgminunitlen
                || \abs($crd['y0'] - $crd['y']) >= $this->svgminunitlen
            ) {
                if ($prk === 1) {
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
            if (!\is_int($prk)) {
                continue;
            }

            if ((($prk + 1) % 4) !== 0) {
                continue;
            }

            // convert quadratic points to cubic points
            $px1 = ($prm[$prk - 3] ?? 0.0) + $crd['xoffset'];
            $py1 = ($prm[$prk - 2] ?? 0.0) + $crd['yoffset'];
            $pxa = ($crd['x'] + (2 * $px1)) / 3;
            $pya = ($crd['y'] + (2 * $py1)) / 3;
            $crd['x'] = ($prm[$prk - 1] ?? 0.0) + $crd['xoffset'];
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
        $prevCmd = $key > 0 ? \strtoupper($paths[$key - 1][1] ?? '') : '';

        foreach ($prm as $prk => $prv) {
            if (!\is_int($prk)) {
                continue;
            }

            if ((($prk + 1) % 4) !== 0) {
                continue;
            }

            if ($prevCmd === 'C' || $prevCmd === 'S') {
                $px1 = (2 * $crd['x']) - $px2;
                $py1 = (2 * $crd['y']) - $py2;
            } else {
                $px1 = $crd['x'];
                $py1 = $crd['y'];
            }

            $px2 = ($prm[$prk - 3] ?? 0.0) + $crd['xoffset'];
            $py2 = ($prm[$prk - 2] ?? 0.0) + $crd['yoffset'];
            $crd['x'] = ($prm[$prk - 1] ?? 0.0) + $crd['xoffset'];
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
        $prevCmd = $key > 0 ? \strtoupper($paths[$key - 1][1] ?? '') : '';

        foreach ($prm as $prk => $prv) {
            if (!\is_int($prk)) {
                continue;
            }

            if (($prk % 2) === 0) {
                continue;
            }

            if ($prevCmd === 'Q' || $prevCmd === 'T') {
                $px1 = (2 * $crd['x']) - $px1;
                $py1 = (2 * $crd['y']) - $py1;
            } else {
                $px1 = $crd['x'];
                $py1 = $crd['y'];
            }

            // convert quadratic points to cubic points
            $pxa = ($crd['x'] + (2 * $px1)) / 3;
            $pya = ($crd['y'] + (2 * $py1)) / 3;
            $crd['x'] = ($prm[$prk - 1] ?? 0.0) + $crd['xoffset'];
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
                \abs($crd['x0'] - $crd['x']) >= $this->svgminunitlen
                || \abs($crd['y0'] - $crd['y']) >= $this->svgminunitlen
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
        $crd['x'] = $crd['xinit'];
        $crd['x0'] = $crd['xinit'];
        $crd['y'] = $crd['yinit'];
        $crd['y0'] = $crd['yinit'];
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
            'wider' => $parent + 10,
            'narrower' => $parent - 10,
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
     * Normalize a CSS mix-blend-mode value to the PDF blend mode name.
     *
     * CSS uses kebab-case; PDF uses CamelCase. Unknown values fall back to 'Normal'.
     *
     * @param string $mode CSS blend mode value (e.g. 'multiply', 'color-dodge').
     *
     * @return string PDF blend mode name (e.g. 'Multiply', 'ColorDodge').
     */
    protected function normalizeSVGBlendMode(string $mode): string
    {
        $mode = \strtolower(\trim($mode));
        return isset(self::SVGBLENDMODE[$mode]) ? self::SVGBLENDMODE[$mode] : 'Normal';
    }

    /**
     * Normalize an SVG opacity value to the 0..1 range.
     *
     * @param string|float|int $alpha Opacity value from parsed SVG style.
     */
    protected function normalizeSVGAlphaValue(string|float|int $alpha): float
    {
        return \max(0.0, \min(1.0, (float) $alpha));
    }

    /**
     * Emit a partial graphics state update for SVG alpha/blend settings.
     *
     * @param ?float  $strokingAlpha    Stroked alpha (CA), when provided.
     * @param ?float  $nonstrokingAlpha Non-stroked alpha (ca), when provided.
     * @param string  $blendMode        PDF blend mode name.
     */
    protected function getSVGExtGState(
        ?float $strokingAlpha = null,
        ?float $nonstrokingAlpha = null,
        string $blendMode = 'Normal',
    ): string {
        if (!$this->isTransparencyAllowed()) {
            return '';
        }

        $parms = [];
        if ($strokingAlpha !== null) {
            $parms['CA'] = $strokingAlpha;
        }
        if ($nonstrokingAlpha !== null) {
            $parms['ca'] = $nonstrokingAlpha;
        }
        if ($blendMode !== '') {
            $parms['BM'] = '/' . $blendMode;
        }

        if ($parms === []) {
            return '';
        }

        return $this->graph->getExtGState($parms);
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
        $regs = [];
        if (\preg_match('/' . $attr . '[\s]*+:[\s]*+([^\;\"]*+)/si', $tag, $regs)) {
            return \trim($regs[1] ?? '');
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
     * @throws \Com\Tecnick\Pdf\Font\Exception
     */
    protected function parseSVGStyleFont(array &$svgstyle, array $parent = self::DEFSVGSTYLE): string
    {
        if ($svgstyle['font'] !== '') {
            // get font attributes from CSS style
            $font = $svgstyle['font'];
            $svgstyle['font-family'] = $this->parseCSSAttrib($font, 'font-family', $svgstyle['font-family']);
            $svgstyle['font-size-adjust'] = $this->parseCSSAttrib(
                $font,
                'font-size-adjust',
                $svgstyle['font-size-adjust'],
            );
            $svgstyle['font-size'] = $this->parseCSSAttrib($font, 'font-size', $svgstyle['font-size']);
            $svgstyle['font-stretch'] = $this->parseCSSAttrib($font, 'font-stretch', $svgstyle['font-stretch']);
            $svgstyle['font-style'] = $this->parseCSSAttrib($font, 'font-style', $svgstyle['font-style']);
            $svgstyle['font-variant'] = $this->parseCSSAttrib($font, 'font-variant', $svgstyle['font-variant']);
            $svgstyle['font-weight'] = $this->parseCSSAttrib($font, 'font-weight', $svgstyle['font-weight']);
            $svgstyle['letter-spacing'] = $this->parseCSSAttrib($font, 'letter-spacing', $svgstyle['letter-spacing']);
            $svgstyle['text-decoration'] = $this->parseCSSAttrib(
                $font,
                'text-decoration',
                $svgstyle['text-decoration'],
            );
        }

        $svgstyle['font-family'] = $svgstyle['font-family'] === ''
            ? $parent['font-family']
            : $this->font->getFontFamilyName($svgstyle['font-family']);

        $svgstyle['letter-spacing-val'] = $this->getTALetterSpacing(
            $svgstyle['letter-spacing'],
            $parent['letter-spacing-val'],
        );
        $svgstyle['font-stretch-val'] = $this->getTAFontStretching(
            $svgstyle['font-stretch'],
            $parent['font-stretch-val'],
        );

        $ref = self::REFUNITVAL;
        $ref['parent'] = $parent['font-size-val'];
        $ref['font']['rootsize'] = $parent['font-size-val'];
        $ref['font']['size'] = $parent['font-size-val'];
        $ref['font']['xheight'] = $parent['font-size-val'] / 2;
        $ref['font']['zerowidth'] = $parent['font-size-val'] / 3;
        $svgstyle['font-size-val'] = $this->getFontValuePoints($svgstyle['font-size'], $ref);

        $svgstyle['font-mode'] = '';
        $svgstyle['font-mode'] .= $this->getTAFontWeight($svgstyle['font-weight']);
        $svgstyle['font-mode'] .= $this->getTAFontStyle($svgstyle['font-style']);
        $svgstyle['font-mode'] .= $this->getTAFontDecoration($svgstyle['text-decoration']);

        $fontmetric = $this->font->insert(
            $this->pon,
            $svgstyle['font-family'],
            $svgstyle['font-mode'],
            $svgstyle['font-size-val'],
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
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function parseSVGStyleStroke(int $soid, array &$svgstyle): string
    {
        if ($svgstyle['stroke'] === '' || $svgstyle['stroke'] === 'none') {
            return '';
        }

        $strokestyle = $this->graph->getDefaultStyle();

        $col = $this->color->getColorObj($svgstyle['stroke']);
        if ($col === null) {
            return '';
        }

        $out = '';
        $blendMode = $this->normalizeSVGBlendMode($svgstyle['mix-blend-mode']);
        $baseOpacity = $this->normalizeSVGAlphaValue($svgstyle['opacity']);
        $strokeOpacity = $this->normalizeSVGAlphaValue($svgstyle['stroke-opacity']);
        $strokeAlpha = $baseOpacity * $strokeOpacity;
        $rgba = $col->toRgbArray();
        $alpha = $rgba['alpha'] ?? null;
        if (\is_float($alpha) && $alpha < 1) {
            $strokeAlpha *= $this->normalizeSVGAlphaValue($alpha);
        }

        if (\abs($strokeAlpha - $baseOpacity) > self::SVGMINFLOATDIFF) {
            $out .= $this->getSVGExtGState($strokeAlpha, null, $blendMode);
        }

        if (!isset($this->svgobjs[$soid]['refunitval'])) {
            return '';
        }

        $ref = $this->svgobjs[$soid]['refunitval'];
        $ref['parent'] = 0.0;
        $strokestyle['lineWidth'] = $this->svgUnitToUnit($svgstyle['stroke-width'], -1, $ref);

        $strokestyle['lineCap'] = $svgstyle['stroke-linecap'];
        $strokestyle['lineJoin'] = $svgstyle['stroke-linejoin'];
        //  $strokestyle['miterLimit'] = (10.0 / $this->kunit),
        if ($svgstyle['stroke-dasharray'] === '' || $svgstyle['stroke-dasharray'] === 'none') {
            $strokestyle['dashArray'] = [];
        } else {
            // Normalise each dash/gap token to user units so that values with
            // unit suffixes (px, pt, mm, %, …) produce correct dash lengths.
            $dashRef = $ref;
            $dashRef['parent'] = 0.0;
            $strokestyle['dashArray'] = \array_map(
                fn(string $tok): int => (int) \round($this->svgUnitToUnit(\trim($tok), -1, $dashRef)),
                \explode(' ', $svgstyle['stroke-dasharray'], 100),
            );
        }
        // $strokestyle['dashPhase'] = 0,
        $strokestyle['lineColor'] = $svgstyle['stroke'];
        unset($strokestyle['fillColor']);

        $out .= $this->graph->getStyleCmd($strokestyle);

        $objstyle = 'D';
        if (!\str_contains($svgstyle['objstyle'], $objstyle)) {
            $svgstyle['objstyle'] .= $objstyle;
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
    protected function parseSVGStyleColor(array &$svgstyle): string
    {
        $out = '';
        $blendMode = $this->normalizeSVGBlendMode($svgstyle['mix-blend-mode']);

        if ($this->isTransparencyAllowed() && ($svgstyle['opacity'] < 1 || $blendMode !== 'Normal')) {
            $out .= $this->graph->getAlpha($svgstyle['opacity'], $blendMode);
        }

        if ($svgstyle['color'] !== '') {
            $this->graph->add(['fillColor' => $svgstyle['color']], true);
        }

        if ($svgstyle['text-color'] !== '') {
            $out .= $this->color->getPdfColor($svgstyle['text-color']);
        }

        return $out;
    }

    /**
     * Apply an SVG mask="url(#id)" as a PDF SMask ExtGState.
     *
     * Renders the mask's child content to a stream that will be emitted as a
     * Form XObject (with a DeviceGray transparency group) at PDF body time.
     * The Form XObject is referenced by an SMask dict which is in turn
     * referenced by an ExtGState.  The returned PDF command activates that
     * ExtGState before the masked element is drawn.
     *
     * @param int $soid SVG object ID.
     * @param TSVGStyle $svgstyle Current SVG style.
     *
     * @return string Raw PDF command or empty string.
     */
    protected function parseSVGStyleMask(int $soid, array $svgstyle): string
    {
        if (!$this->isTransparencyAllowed()) {
            return '';
        }

        if (!isset($this->svgobjs[$soid])) {
            return '';
        }
        /** @var TSVGObj $svgobj */
        $svgobj = &$this->svgobjs[$soid];

        if ((int) $svgobj['patternmode'] > 0) {
            return '';
        }

        $maskRef = $svgstyle['mask'];
        if ($maskRef === 'none' || $maskRef === '') {
            return '';
        }

        $regs = [];
        if (!\preg_match('/url\(\s*#([^)]+)\s*\)/i', $maskRef, $regs)) {
            return '';
        }

        $maskId = \trim($regs[1] ?? '');
        if ($maskId === '') {
            return '';
        }

        $maskDef = $svgobj['defs'][$maskId] ?? null;
        if ($maskDef === null) {
            return '';
        }

        if ($maskDef['name'] !== 'mask') {
            return '';
        }

        $maskKey = 'MSK_' . \strtoupper(\substr(\md5($maskId), 0, 16));

        if (!isset($this->svgmasks[$maskKey])) {
            $pheight = $svgobj['refunitval']['page']['height'];
            $pwidth = $svgobj['refunitval']['page']['width'];

            $maskParser = \xml_parser_create('UTF-8');
            $stream = '';
            $svgobj['patternmode']++;
            try {
                if (isset($maskDef['child']) && $maskDef['child'] !== []) {
                    /** @var array<array-key, mixed> $maskChildren */
                    $maskChildren = $maskDef['child'];
                    foreach ($maskChildren as $child) {
                        if (!\is_array($child) || !isset($child['name']) || !\is_string($child['name'])) {
                            continue;
                        }
                        if (!isset($child['attr']) || !\is_array($child['attr'])) {
                            continue;
                        }

                        $childName = $child['name'];
                        /** @var TSVGAttributes $childAttr */
                        $childAttr = $child['attr'];

                        $prevOut = $svgobj['out'];
                        $prevLen = \strlen($prevOut);
                        if (isset($childAttr['closing_tag'])) {
                            if (isset($childAttr['content']) && $childAttr['content'] !== '') {
                                $svgobj['text'] .= $childAttr['content'];
                            }

                            $this->handleSVGTagEnd($maskParser, $childName);
                        } else {
                            $this->handleSVGTagStart($maskParser, $childName, $childAttr, $soid);
                        }

                        $currOut = $svgobj['out'];
                        $currLen = \strlen($currOut);
                        if ($currLen > $prevLen) {
                            $stream .= \substr($currOut, $prevLen);
                            $svgobj['out'] = $prevOut;
                        }
                    }
                }
            } finally {
                $svgobj['patternmode'] = \max(0, $svgobj['patternmode'] - 1);
                unset($maskParser);
            }

            $this->svgmasks[$maskKey] = [
                'id' => $maskKey,
                'stream' => $stream,
                'bbox' => [0.0, 0.0, $pwidth, $pheight],
                'gs_n' => 0,
            ];
        }
        return '/' . $maskKey . ' gs' . "\n";
    }

    /**
     * Parse an SVG glyph-orientation angle value.
     *
     * Accepts bare numbers (degrees) and explicit `deg`, `rad`, `grad` units.
     *
     * @param string $value Raw CSS value.
     * @param float $default Default angle in degrees when parsing fails.
     *
     * @return float Angle in degrees.
     */
    protected function parseSVGGlyphOrientationAngle(string $value, float $default): float
    {
        $val = \trim(\strtolower($value));
        if ($val === '') {
            return $default;
        }

        $regs = [];
        if (!\preg_match('/^([-+]?\d*\.?\d+)(deg|rad|grad)?$/', $val, $regs)) {
            return $default;
        }

        $angStr = $regs[1] ?? '';
        if (!\is_numeric($angStr)) {
            return $default;
        }

        $ang = (float) $angStr;
        $unit = $regs[2] ?? 'deg';

        return match ($unit) {
            'rad' => \rad2deg($ang),
            'grad' => $ang * 0.9,
            default => $ang,
        };
    }

    /**
     * Resolve text rotation implied by writing-mode and glyph-orientation.
     *
     * @param TSVGStyle $svgstyle SVG style.
     * @param bool $isVertical True when writing-mode is vertical.
     *
     * @return float Rotation in degrees.
     */
    protected function getSVGGlyphOrientationRotation(array $svgstyle, bool $isVertical): float
    {
        if ($isVertical) {
            // S-2 baseline: vertical writing stacks glyphs and rotates them 90°.
            $default = 90.0;
            $gvert = \strtolower($svgstyle['glyph-orientation-vertical']);
            if ($gvert === 'auto') {
                return $default;
            }

            return $this->parseSVGGlyphOrientationAngle($gvert, $default);
        }

        $default = 0.0;
        $ghorz = \strtolower($svgstyle['glyph-orientation-horizontal']);
        if ($ghorz === 'auto') {
            return $default;
        }

        return $this->parseSVGGlyphOrientationAngle($ghorz, $default);
    }

    /**
     * Map SVG rendering-hint properties to PDF rendering-intent operators.
     *
     * SVG `color-rendering`, `image-rendering`, `shape-rendering`, and
     * `text-rendering` are advisory hints with no guaranteed effect in any
     * renderer.  PDF supports a /ri (rendering intent) operator with four
     * standard values; this method performs a best-effort mapping:
     *
     *  - `optimizeQuality`  / `crispEdges`     → /RelativeColorimetric (default)
     *  - `optimizeSpeed`    / `pixelated`      → /AbsoluteColorimetric
     *  - `auto`             (any)              → no operator emitted (use PDF default)
     *
     * The SVG `color-interpolation` / `color-interpolation-filters` values
     * (`sRGB`, `linearRGB`) cannot be faithfully represented through the ri
     * operator alone and are therefore silently accepted but not forwarded.
     *
     * @param TSVGStyle $svgstyle SVG style.
     *
     * @return string Raw PDF command or empty string.
     */
    protected function parseSVGStyleRenderingHints(array $svgstyle): string
    {
        // Collect the most-demanding rendering hint across the four properties.
        $hints = [
            $svgstyle['color-rendering'],
            $svgstyle['image-rendering'],
            $svgstyle['shape-rendering'],
            $svgstyle['text-rendering'],
        ];

        $intent = '';
        foreach ($hints as $h) {
            switch (\strtolower($h)) {
                case 'optimizequality':
                case 'crispedges':
                case 'geometricprecision':
                    // Quality-first hints → RelativeColorimetric (PDF default, but
                    // emit explicitly so the value is recorded in the content stream).
                    if ($intent === '') {
                        $intent = 'RelativeColorimetric';
                    }

                    break;
                case 'optimizespeed':
                case 'pixelated':
                    // Speed-first hints → AbsoluteColorimetric.
                    $intent = 'AbsoluteColorimetric';
                    break;
            }
        }

        if ($intent === '') {
            return '';
        }

        return '/' . $intent . ' ri' . "\n";
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
        if (!\preg_match(
            '/rect\(([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)\)/si',
            $svgstyle['clip'],
            $regs,
        )) {
            return '';
        }

        $regTop = $regs[1] ?? '';
        $regRight = $regs[2] ?? '';
        $regBottom = $regs[3] ?? '';
        $regLeft = $regs[4] ?? '';

        $top = $this->toUnit($regTop !== '' ? $this->svgUnitToPoints($regTop) : 0.0);
        $right = $this->toUnit($regRight !== '' ? $this->svgUnitToPoints($regRight) : 0.0);
        $bottom = $this->toUnit($regBottom !== '' ? $this->svgUnitToPoints($regBottom) : 0.0);
        $left = $this->toUnit($regLeft !== '' ? $this->svgUnitToPoints($regLeft) : 0.0);

        $clx = $posx + $left;
        $cly = $posy + $top;
        $clw = $width - $left - $right;
        $clh = $height - $top - $bottom;
        $eoclip = $svgstyle['clip-rule'] === 'evenodd';

        return $this->graph->getClippingRect($clx, $cly, $clw, $clh, $eoclip);
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
     *
     * @throws \Com\Tecnick\Pdf\Graph\Exception
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
        $svgobj = $this->svgobjs[$soid] ?? null;
        if ($svgobj === null) {
            return '';
        }

        $gradient = $gradients[$xref] ?? null;
        if ($gradient === null) {
            return '';
        }
        $gradientXref = $gradient['xref'] ?? '';
        if ($gradientXref !== '' && isset($gradients[$gradientXref])) {
            // reference to another gradient definition
            $newgradient = $gradients[$gradientXref];
            $newgradient['coords'] = $gradient['coords'];
            $newgradient['mode'] = $gradient['mode'];
            $newgradient['type'] = $gradient['type'] ?? 2;
            $newgradient['gradientUnits'] = $gradient['gradientUnits'];
            if (isset($gradient['gradientTransform'])) {
                $newgradient['gradientTransform'] = $gradient['gradientTransform'];
            }
            $gradient = $newgradient;
        }

        $gradient['coords'] = [
            0 => $gradient['coords'][0] ?? 0.0,
            1 => $gradient['coords'][1] ?? 0.0,
            2 => $gradient['coords'][2] ?? 0.0,
            3 => $gradient['coords'][3] ?? 0.0,
            4 => $gradient['coords'][4] ?? 0.0,
        ];
        $gradientType = (int) ($gradient['type'] ?? 2);

        $out = '';
        $out .= $this->graph->getStartTransform();

        if ($clip_fnc !== '') {
            $bboxid_start = \array_key_last($this->bbox);
            $bboxidStart = \is_int($bboxid_start) ? $bboxid_start : -1;
            $out .= $this->applySVGClipFunction($clip_fnc, $clip_par);
            $bboxid_last = \array_key_last($this->bbox);
            $bboxidLast = \is_int($bboxid_last) ? $bboxid_last : -1;

            if ($bboxidLast > $bboxidStart && isset($this->bbox[$bboxidLast]) && $gradientType !== 3) {
                $bbox = $this->bbox[$bboxidLast];
                $grx = $bbox['x'];
                $gry = $bbox['y'];
                $grw = $bbox['w'];
                $grh = $bbox['h'];
            }
        }

        switch ($gradient['mode']) {
            case 'percentage':
                foreach ($gradient['coords'] as $key => $val) {
                    $gradient['coords'][$key] = \intval($val) / 100;
                    if ($val < 0) {
                        $gradient['coords'][$key] = 0;
                    } elseif ($val > 1) {
                        $gradient['coords'][$key] = 1;
                    }
                }
                break;
            case 'measure':
                if (isset($gradient['gradientTransform'])) {
                    $gtm0 = $gradient['gradientTransform'][0] ?? 0.0;
                    $gtm1 = $gradient['gradientTransform'][1] ?? 0.0;
                    $gtm2 = $gradient['gradientTransform'][2] ?? 0.0;
                    $gtm3 = $gradient['gradientTransform'][3] ?? 0.0;
                    $gtm4 = $gradient['gradientTransform'][4] ?? 0.0;
                    $gtm5 = $gradient['gradientTransform'][5] ?? 0.0;
                    $coord4 = $gradient['coords'][4];
                    // apply transformation matrix
                    $gxa = ($gtm0 * $gradient['coords'][0]) + ($gtm2 * $gradient['coords'][1]) + $gtm4;
                    $gya = ($gtm1 * $gradient['coords'][0]) + ($gtm3 * $gradient['coords'][1]) + $gtm5;
                    $gxb = ($gtm0 * $gradient['coords'][2]) + ($gtm2 * $gradient['coords'][3]) + $gtm4;
                    $gyb = ($gtm1 * $gradient['coords'][2]) + ($gtm3 * $gradient['coords'][3]) + $gtm5;
                    $grr = \sqrt(($gtm0 * $coord4) ** 2 + ($gtm1 * $coord4) ** 2);
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
                if ($gradient['gradientUnits'] === 'objectBoundingBox') {
                    // convert to SVG coordinate system
                    $gradient['coords'][0] += $grx;
                    $gradient['coords'][1] += $gry;
                    $gradient['coords'][2] += $grx;
                    $gradient['coords'][3] += $gry;
                }
                // calculate percentages
                $gradient['coords'][0] = ($gradient['coords'][0] - $grx) / $grw;
                $gradient['coords'][1] = ($gradient['coords'][1] - $gry) / $grh;
                $gradient['coords'][2] = ($gradient['coords'][2] - $grx) / $grw;
                $gradient['coords'][3] = ($gradient['coords'][3] - $gry) / $grh;
                $gradient['coords'][4] /= $grw;
                break;
        }

        if (
            $gradientType === 2
            && $gradient['coords'][0] === $gradient['coords'][2]
            && $gradient['coords'][1] === $gradient['coords'][3]
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
        $gry = $this->toUnit($svgobj['refunitval']['page']['height']) - $gry;
        if ($gradientType === 3) {
            // circular gradient
            $gry -= $gradient['coords'][1] * ($grw + $grh);
            $maxGradSpan = \max($grw, $grh);
            $grw = $maxGradSpan;
            $grh = $maxGradSpan;
        } else {
            $gry -= $grh;
        }

        $out .= \sprintf(
            "%F 0 0 %F %F %F cm\n",
            $this->toPoints($grw),
            $this->toPoints($grh),
            $this->toPoints($grx),
            $this->toPoints($gry),
        );

        if (\count($gradient['stops']) > 1) {
            $out .= $this->graph->getGradient($gradientType, $gradient['coords'], $gradient['stops'], '', false);
        }

        $out .= $this->graph->getStopTransform();

        return $out;
    }

    /**
     * Resolve and invoke supported clip/path functions with strict argument checks.
     *
     * @param string $clip_fnc Function name.
     * @param array<mixed> $clip_par Function parameters.
     *
     * @return string
     */
    protected function applySVGClipFunction(string $clip_fnc, array $clip_par): string
    {
        return match ($clip_fnc) {
            'getSVGPath' => isset($clip_par[0], $clip_par[1], $clip_par[2])
                && \is_int($clip_par[0])
                && \is_string($clip_par[1])
                && \is_string($clip_par[2])
                    ? $this->getSVGPath($clip_par[0], $clip_par[1], $clip_par[2])
                    : '',
            'getRoundedRect' => isset(
                $clip_par[0],
                $clip_par[1],
                $clip_par[2],
                $clip_par[3],
                $clip_par[4],
                $clip_par[5],
                $clip_par[6],
                $clip_par[7],
            )
                && (\is_int($clip_par[0]) || \is_float($clip_par[0]))
                && (\is_int($clip_par[1]) || \is_float($clip_par[1]))
                && (\is_int($clip_par[2]) || \is_float($clip_par[2]))
                && (\is_int($clip_par[3]) || \is_float($clip_par[3]))
                && (\is_int($clip_par[4]) || \is_float($clip_par[4]))
                && (\is_int($clip_par[5]) || \is_float($clip_par[5]))
                && \is_string($clip_par[6])
                && \is_string($clip_par[7])
                    ? $this->graph->getRoundedRect(
                        (float) $clip_par[0],
                        (float) $clip_par[1],
                        (float) $clip_par[2],
                        (float) $clip_par[3],
                        (float) $clip_par[4],
                        (float) $clip_par[5],
                        $clip_par[6],
                        $clip_par[7],
                    )
                    : '',
            'getCircle' => isset($clip_par[0], $clip_par[1], $clip_par[2], $clip_par[3], $clip_par[4], $clip_par[5])
                && (\is_int($clip_par[0]) || \is_float($clip_par[0]))
                && (\is_int($clip_par[1]) || \is_float($clip_par[1]))
                && (\is_int($clip_par[2]) || \is_float($clip_par[2]))
                && (\is_int($clip_par[3]) || \is_float($clip_par[3]))
                && (\is_int($clip_par[4]) || \is_float($clip_par[4]))
                && \is_string($clip_par[5])
                    ? $this->graph->getCircle(
                        (float) $clip_par[0],
                        (float) $clip_par[1],
                        (float) $clip_par[2],
                        (float) $clip_par[3],
                        (float) $clip_par[4],
                        $clip_par[5],
                    )
                    : '',
            'getEllipse' => isset(
                $clip_par[0],
                $clip_par[1],
                $clip_par[2],
                $clip_par[3],
                $clip_par[4],
                $clip_par[5],
                $clip_par[6],
                $clip_par[7],
            )
                && (\is_int($clip_par[0]) || \is_float($clip_par[0]))
                && (\is_int($clip_par[1]) || \is_float($clip_par[1]))
                && (\is_int($clip_par[2]) || \is_float($clip_par[2]))
                && (\is_int($clip_par[3]) || \is_float($clip_par[3]))
                && (\is_int($clip_par[4]) || \is_float($clip_par[4]))
                && (\is_int($clip_par[5]) || \is_float($clip_par[5]))
                && (\is_int($clip_par[6]) || \is_float($clip_par[6]))
                && \is_string($clip_par[7])
                    ? $this->graph->getEllipse(
                        (float) $clip_par[0],
                        (float) $clip_par[1],
                        (float) $clip_par[2],
                        (float) $clip_par[3],
                        (float) $clip_par[4],
                        (float) $clip_par[5],
                        (float) $clip_par[6],
                        $clip_par[7],
                    )
                    : '',
            'getLine' => isset($clip_par[0], $clip_par[1], $clip_par[2], $clip_par[3])
                && (\is_int($clip_par[0]) || \is_float($clip_par[0]))
                && (\is_int($clip_par[1]) || \is_float($clip_par[1]))
                && (\is_int($clip_par[2]) || \is_float($clip_par[2]))
                && (\is_int($clip_par[3]) || \is_float($clip_par[3]))
                    ? $this->graph->getLine(
                        (float) $clip_par[0],
                        (float) $clip_par[1],
                        (float) $clip_par[2],
                        (float) $clip_par[3],
                    )
                    : '',
            'getPolygon' => isset($clip_par[0], $clip_par[1]) && \is_array($clip_par[0]) && \is_string($clip_par[1])
                ? $this->graph->getPolygon(\array_map('floatval', $clip_par[0]), $clip_par[1])
                : '',
            'getClippingRect' => isset($clip_par[0], $clip_par[1], $clip_par[2], $clip_par[3])
                && (\is_int($clip_par[0]) || \is_float($clip_par[0]))
                && (\is_int($clip_par[1]) || \is_float($clip_par[1]))
                && (\is_int($clip_par[2]) || \is_float($clip_par[2]))
                && (\is_int($clip_par[3]) || \is_float($clip_par[3]))
                    ? $this->graph->getClippingRect(
                        (float) $clip_par[0],
                        (float) $clip_par[1],
                        (float) $clip_par[2],
                        (float) $clip_par[3],
                        $this->normalizeSVGBoolLike($clip_par[4] ?? false),
                    )
                    : '',
            default => '',
        };
    }

    /**
     * Normalize scalar-like values into strict booleans.
     */
    protected function normalizeSVGBoolLike(mixed $value): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        if (\is_int($value) || \is_float($value)) {
            return (float) $value !== 0.0;
        }

        if (\is_string($value)) {
            return \in_array(\strtolower(\trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    /**
     * Resolve a pattern length optionally expressed as percentage.
     *
     * @param string $raw Raw pattern length value.
     * @param float $base Base value for percentages.
     * @param int $soid SVG object ID.
     *
     * @return float
     */
    protected function resolveSVGPatternLength(string $raw, float $base, int $soid): float
    {
        $raw = \trim($raw);
        if ($raw === '') {
            return 0.0;
        }

        if (\str_ends_with($raw, '%')) {
            $pctRaw = \trim(\substr($raw, 0, -1));
            if (!\is_numeric($pctRaw)) {
                return 0.0;
            }
            $pct = (float) $pctRaw;
            return ($pct / 100.0) * $base;
        }

        return $this->svgUnitToUnit($raw, $soid);
    }

    /**
     * Resolve a pattern definition, including href inheritance chain.
     *
     * Child pattern attributes override inherited parent values.
     * Child pattern content is inherited only when the child has no own content.
     *
     * @param int $soid SVG object ID.
     * @param string $patternId Pattern ID without '#'.
     *
     * @return ?TSVGAttribs
     */
    protected function resolveSVGPatternDef(int $soid, string $patternId): ?array
    {
        $resolved = $this->svgobjs[$soid]['defs'][$patternId] ?? null;
        if ($resolved === null) {
            return null;
        }

        if ($resolved['name'] !== 'pattern') {
            return null;
        }

        $resolvedAttr = $resolved['attr'];
        $resolvedChild = $resolved['child'] ?? [];

        $seen = [$patternId => true];
        $href = $resolvedAttr['xlink:href'] ?? $resolvedAttr['href'] ?? '';
        while ($href !== '' && $href[0] === '#') {
            $parentId = \substr($href, 1);
            if ($parentId === '' || isset($seen[$parentId])) {
                break;
            }
            $seen[$parentId] = true;

            $parent = $this->svgobjs[$soid]['defs'][$parentId] ?? null;
            if ($parent === null) {
                break;
            }

            if ($parent['name'] !== 'pattern') {
                break;
            }

            $parentAttr = $parent['attr'];
            $resolvedAttr = \array_replace($parentAttr, $resolvedAttr);
            /** @var TSVGAttributes $resolvedAttr */

            if ($resolvedChild === [] && isset($parent['child']) && $parent['child'] !== []) {
                $resolvedChild = $parent['child'];
            }

            $href = $parentAttr['xlink:href'] ?? $parentAttr['href'] ?? '';
        }

        return [
            'name' => 'pattern',
            'attr' => $resolvedAttr,
            'child' => $resolvedChild,
        ];
    }

    /**
     * Register a PDF tiling pattern resource for an SVG pattern definition.
     *
     * @param int $soid SVG object ID.
     * @param string $patternId Pattern ID without '#'.
     * @param TSVGAttribs $patterndef Resolved pattern definition.
     * @param TSVGAttributes $attr Resolved pattern attributes.
     * @param float $tileX Tile origin x in user units.
     * @param float $tileY Tile origin y in user units.
     * @param float $tileW Tile width in user units.
     * @param float $tileH Tile height in user units.
     * @param float $width Target object width in user units.
     * @param float $height Target object height in user units.
     *
     * @return string Pattern resource ID or empty string on failure.
     */
    protected function registerSVGPatternResource(
        int $soid,
        string $patternId,
        array $patterndef,
        array $attr,
        float $tileX,
        float $tileY,
        float $tileW,
        float $tileH,
        float $width,
        float $height,
    ): string {
        if ($tileW <= $this->svgminunitlen || $tileH <= $this->svgminunitlen) {
            return '';
        }

        $contentUnits = $attr['patternContentUnits'] ?? 'userSpaceOnUse';
        $patternTransform = isset($attr['patternTransform']) && $attr['patternTransform'] !== ''
            ? $this->getSVGTransformMatrix($attr['patternTransform'])
            : self::TMXID;
        $viewBoxTm = self::TMXID;
        $hasViewBox = false;
        if (isset($attr['viewBox']) && $attr['viewBox'] !== '') {
            $vals = \preg_split('/[\s,]+/', \trim($attr['viewBox']), -1, \PREG_SPLIT_NO_EMPTY);
            if (\is_array($vals) && isset($vals[0], $vals[1], $vals[2], $vals[3])) {
                $vbx = $this->svgUnitToUnit($vals[0], $soid);
                $vby = $this->svgUnitToUnit($vals[1], $soid);
                $vbw = \abs($this->svgUnitToUnit($vals[2], $soid));
                $vbh = \abs($this->svgUnitToUnit($vals[3], $soid));

                if ($vbw > 0.0 && $vbh > 0.0) {
                    $hasViewBox = true;
                    $viewScaleX = $tileW / $vbw;
                    $viewScaleY = $tileH / $vbh;
                    $viewOffsetX = 0.0;
                    $viewOffsetY = 0.0;

                    $aspectRaw = $attr['preserveAspectRatio'] ?? 'xMidYMid meet';
                    $aspectFit = 'meet';
                    $aspectX = 'xMid';
                    $aspectY = 'YMid';
                    if (\trim($aspectRaw) === 'none') {
                        $aspectFit = 'none';
                    } else {
                        $aspectMatches = [];
                        \preg_match_all('/[a-zA-Z]+/', $aspectRaw, $aspectMatches);
                        $tokens = $aspectMatches[0] ?? [];
                        $firstToken = $tokens[0] ?? '';
                        if (\strtolower($firstToken) === 'defer') {
                            \array_shift($tokens);
                            $firstToken = $tokens[0] ?? '';
                        }

                        if ($firstToken !== '' && \strlen($firstToken) === 8) {
                            $alignToken = $firstToken;
                            $aspectX = \substr($alignToken, 0, 4);
                            $aspectY = \substr($alignToken, 4, 4);
                            $aspectMode = $tokens[1] ?? '';
                            if ($aspectMode !== '' && \in_array($aspectMode, ['meet', 'slice', 'none'], true)) {
                                $aspectFit = $aspectMode;
                            }
                        } elseif ($firstToken !== '' && \in_array($firstToken, ['meet', 'slice', 'none'], true)) {
                            $aspectFit = $firstToken;
                        }
                    }

                    if ($aspectFit !== 'none') {
                        $scaleX = $tileW / $vbw;
                        $scaleY = $tileH / $vbh;
                        $scale = $aspectFit === 'slice' ? \max($scaleX, $scaleY) : \min($scaleX, $scaleY);
                        $viewScaleX = $scale;
                        $viewScaleY = $scale;
                        $scaledW = $vbw * $scale;
                        $scaledH = $vbh * $scale;
                        $viewOffsetX = match ($aspectX) {
                            'xMax' => $tileW - $scaledW,
                            'xMid' => ($tileW - $scaledW) / 2.0,
                            default => 0.0,
                        };
                        $viewOffsetY = match ($aspectY) {
                            'YMax' => $tileH - $scaledH,
                            'YMid' => ($tileH - $scaledH) / 2.0,
                            default => 0.0,
                        };
                    }

                    $viewBoxTm = [
                        $viewScaleX,
                        0.0,
                        0.0,
                        $viewScaleY,
                        $viewOffsetX - ($viewScaleX * $vbx),
                        $viewOffsetY - ($viewScaleY * $vby),
                    ];
                }
            }
        }

        $contentTm = self::TMXID;
        // SVG2: patternContentUnits has no effect when a viewBox is specified.
        if ($contentUnits === 'objectBoundingBox' && !$hasViewBox) {
            $contentTm = $this->graph->getCtmProduct($contentTm, [$width, 0.0, 0.0, $height, 0.0, 0.0]);
        }
        $contentTm = $this->graph->getCtmProduct($contentTm, $viewBoxTm);

        $stream = $this->graph->getStartTransform();
        $stream .= $this->getOutSVGTransformation($contentTm, $soid);

        $patParser = \xml_parser_create('UTF-8');

        $this->svgobjs[$soid]['patternmode'] = (int) ($this->svgobjs[$soid]['patternmode'] ?? 0) + 1;
        try {
            if (isset($patterndef['child']) && $patterndef['child'] !== []) {
                foreach ($patterndef['child'] as $child) {
                    $prevOut = $this->svgobjs[$soid]['out'];
                    $prevLen = \strlen($prevOut);
                    if (isset($child['attr']['closing_tag'])) {
                        if (isset($child['attr']['content']) && $child['attr']['content'] !== '') {
                            $this->svgobjs[$soid]['text'] .= $child['attr']['content'];
                        }
                        $this->handleSVGTagEnd($patParser, $child['name']);
                    } else {
                        /** @var TSVGAttributes $childAttr */
                        $childAttr = $child['attr'];
                        $this->handleSVGTagStart($patParser, $child['name'], $childAttr, $soid);
                    }

                    $currOut = $this->svgobjs[$soid]['out'];
                    $currLen = \strlen($currOut);
                    if ($currLen > $prevLen) {
                        $stream .= \substr($currOut, $prevLen);

                        $this->svgobjs[$soid]['out'] = $prevOut;
                    }
                }
            }
        } finally {
            $this->svgobjs[$soid]['patternmode'] = \max(0, (int) $this->svgobjs[$soid]['patternmode'] - 1);
            unset($patParser);
        }

        $stream .= $this->graph->getStopTransform();
        if (\trim($stream) === '') {
            return '';
        }

        $patternMatrix = $this->graph->getCtmProduct([1.0, 0.0, 0.0, 1.0, $tileX, $tileY], $patternTransform);
        $pid =
            'PTN_'
            . \strtoupper(\substr(
                \md5(\sprintf(
                    '%s|%F|%F|%F|%F|%F|%F|%F|%F',
                    $patternId,
                    $tileX,
                    $tileY,
                    $tileW,
                    $tileH,
                    $width,
                    $height,
                    $patternMatrix[4],
                    $patternMatrix[5],
                )),
                0,
                16,
            ));

        if (!isset($this->patterns[$pid])) {
            $this->patterns[$pid] = [
                'id' => $pid,
                'n' => 0,
                'outdata' => $stream,
                'bbox' => [0.0, 0.0, $tileW, $tileH],
                'xstep' => $tileW,
                'ystep' => $tileH,
                'matrix' => [
                    $patternMatrix[0],
                    $patternMatrix[1],
                    $patternMatrix[2],
                    $patternMatrix[3],
                    $patternMatrix[4],
                    $patternMatrix[5],
                ],
            ];
        }

        return $pid;
    }

    /**
     * Parse SVG pattern fill style from defs.
     *
     * @param int $soid SVG object ID.
     * @param string $patternId Pattern ID without '#'.
     * @param float $posx X position in user units.
     * @param float $posy Y position in user units.
     * @param float $width Width in user units.
     * @param float $height Height in user units.
     * @param string $clip_fnc Optional clipping function name.
     * @param array<mixed> $clip_par Optional clipping function parameters.
     *
     * @return string
     */
    protected function parseSVGStylePattern(
        int $soid,
        string $patternId,
        float $posx,
        float $posy,
        float $width,
        float $height,
        string $clip_fnc = '',
        array $clip_par = [],
    ): string {
        if (!isset($this->svgobjs[$soid])) {
            return '';
        }

        /** @var TSVGObj $svgobj */
        $svgobj = &$this->svgobjs[$soid];

        if ((int) ($this->svgobjs[$soid]['patternmode'] ?? 0) > 0) {
            return '';
        }

        $patterndef = $this->resolveSVGPatternDef($soid, $patternId);
        if ($patterndef === null) {
            return '';
        }

        $attr = $patterndef['attr'];
        $units = $attr['patternUnits'] ?? 'objectBoundingBox';
        $isObjectBox = $units !== 'userSpaceOnUse';

        $tileX = $this->resolveSVGPatternLength($attr['x'] ?? '0', $isObjectBox ? $width : 1.0, $soid);
        $tileY = $this->resolveSVGPatternLength($attr['y'] ?? '0', $isObjectBox ? $height : 1.0, $soid);
        $tileW = $this->resolveSVGPatternLength($attr['width'] ?? '0', $isObjectBox ? $width : 1.0, $soid);
        $tileH = $this->resolveSVGPatternLength($attr['height'] ?? '0', $isObjectBox ? $height : 1.0, $soid);

        if ($isObjectBox) {
            $tileX += $posx;
            $tileY += $posy;
        }

        if ($tileW <= $this->svgminunitlen || $tileH <= $this->svgminunitlen) {
            return '';
        }

        $patternResId = $this->registerSVGPatternResource(
            $soid,
            $patternId,
            $patterndef,
            $attr,
            $tileX,
            $tileY,
            $tileW,
            $tileH,
            $width,
            $height,
        );
        if ($patternResId !== '') {
            return '/Pattern cs /' . $patternResId . " scn\n";
        }

        $ixStart = (int) \floor(($posx - $tileX) / $tileW) - 1;
        $ixEnd = (int) \ceil(($posx + $width - $tileX) / $tileW) + 1;
        $iyStart = (int) \floor(($posy - $tileY) / $tileH) - 1;
        $iyEnd = (int) \ceil(($posy + $height - $tileY) / $tileH) + 1;

        $out = '';
        $out .= $this->graph->getStartTransform();

        if ($clip_fnc !== '') {
            $out .= $this->applySVGClipFunction($clip_fnc, $clip_par);
        }

        $contentUnits = $attr['patternContentUnits'] ?? 'userSpaceOnUse';
        $patternTransform = isset($attr['patternTransform']) && $attr['patternTransform'] !== ''
            ? $this->getSVGTransformMatrix($attr['patternTransform'])
            : self::TMXID;
        $viewBoxTm = self::TMXID;
        $hasViewBox = false;
        if (isset($attr['viewBox']) && $attr['viewBox'] !== '') {
            $vals = \preg_split('/[\s,]+/', \trim($attr['viewBox']), -1, \PREG_SPLIT_NO_EMPTY);
            if (\is_array($vals) && isset($vals[0], $vals[1], $vals[2], $vals[3])) {
                $vbx = $this->svgUnitToUnit($vals[0], $soid);
                $vby = $this->svgUnitToUnit($vals[1], $soid);
                $vbw = \abs($this->svgUnitToUnit($vals[2], $soid));
                $vbh = \abs($this->svgUnitToUnit($vals[3], $soid));

                if ($vbw > 0.0 && $vbh > 0.0) {
                    $hasViewBox = true;
                    $viewScaleX = $tileW / $vbw;
                    $viewScaleY = $tileH / $vbh;
                    $viewOffsetX = 0.0;
                    $viewOffsetY = 0.0;

                    $aspectRaw = $attr['preserveAspectRatio'] ?? 'xMidYMid meet';
                    $aspectFit = 'meet';
                    $aspectX = 'xMid';
                    $aspectY = 'YMid';
                    if (\trim($aspectRaw) === 'none') {
                        $aspectFit = 'none';
                    } else {
                        $aspectMatches = [];
                        \preg_match_all('/[a-zA-Z]+/', $aspectRaw, $aspectMatches);
                        $tokens = $aspectMatches[0] ?? [];
                        $firstToken = $tokens[0] ?? '';
                        if (\strtolower($firstToken) === 'defer') {
                            \array_shift($tokens);
                            $firstToken = $tokens[0] ?? '';
                        }

                        if ($firstToken !== '' && \strlen($firstToken) === 8) {
                            $alignToken = $firstToken;
                            $aspectX = \substr($alignToken, 0, 4);
                            $aspectY = \substr($alignToken, 4, 4);
                            $aspectMode = $tokens[1] ?? '';
                            $aspectFit = $aspectMode !== '' && \in_array($aspectMode, ['meet', 'slice', 'none'], true)
                                ? $aspectMode
                                : $aspectFit;
                        } elseif ($firstToken !== '' && \in_array($firstToken, ['meet', 'slice', 'none'], true)) {
                            $aspectFit = $firstToken;
                        }
                    }

                    if ($aspectFit !== 'none') {
                        $scaleX = $tileW / $vbw;
                        $scaleY = $tileH / $vbh;
                        $scale = $aspectFit === 'slice' ? \max($scaleX, $scaleY) : \min($scaleX, $scaleY);
                        $viewScaleX = $scale;
                        $viewScaleY = $scale;
                        $scaledW = $vbw * $scale;
                        $scaledH = $vbh * $scale;
                        $viewOffsetX = match ($aspectX) {
                            'xMax' => $tileW - $scaledW,
                            'xMid' => ($tileW - $scaledW) / 2.0,
                            default => 0.0,
                        };
                        $viewOffsetY = match ($aspectY) {
                            'YMax' => $tileH - $scaledH,
                            'YMid' => ($tileH - $scaledH) / 2.0,
                            default => 0.0,
                        };
                    }

                    $viewBoxTm = [
                        $viewScaleX,
                        0.0,
                        0.0,
                        $viewScaleY,
                        $viewOffsetX - ($viewScaleX * $vbx),
                        $viewOffsetY - ($viewScaleY * $vby),
                    ];
                }
            }
        }
        $patParser = \xml_parser_create('UTF-8');

        $svgobj['patternmode'] = (int) $svgobj['patternmode'] + 1;
        try {
            for ($iy = $iyStart; $iy <= $iyEnd; ++$iy) {
                for ($ix = $ixStart; $ix <= $ixEnd; ++$ix) {
                    $tilePosX = $tileX + ($ix * $tileW);
                    $tilePosY = $tileY + ($iy * $tileH);
                    if (
                        $tilePosX > ($posx + $width)
                        || ($tilePosX + $tileW) < $posx
                        || $tilePosY > ($posy + $height)
                        || ($tilePosY + $tileH) < $posy
                    ) {
                        continue;
                    }

                    $out .= $this->graph->getStartTransform();
                    $tileTm = [1.0, 0.0, 0.0, 1.0, $tilePosX, $tilePosY];
                    // SVG2: patternContentUnits has no effect when a viewBox is specified.
                    if ($contentUnits === 'objectBoundingBox' && !$hasViewBox) {
                        $tileTm = $this->graph->getCtmProduct($tileTm, [$width, 0.0, 0.0, $height, 0.0, 0.0]);
                    }
                    $tileTm = $this->graph->getCtmProduct($tileTm, $patternTransform);
                    $tileTm = $this->graph->getCtmProduct($tileTm, $viewBoxTm);
                    $out .= $this->getOutSVGTransformation($tileTm, $soid);

                    if (isset($patterndef['child']) && $patterndef['child'] !== []) {
                        foreach ($patterndef['child'] as $child) {
                            $childName = $child['name'];
                            $childAttr = $child['attr'];
                            $prevOut = $svgobj['out'];
                            $prevLen = \strlen($prevOut);
                            if (!isset($childAttr['closing_tag'])) {
                                $this->handleSVGTagStart($patParser, $childName, $childAttr, $soid);
                                continue;
                            }

                            $childContent = $childAttr['content'] ?? null;
                            if (\is_string($childContent) && $childContent !== '') {
                                // Replay text captured in defs before closing the element.

                                $svgobj['text'] .= $childContent;
                            }
                            $this->handleSVGTagEnd($patParser, $childName);

                            $currOut = $svgobj['out'];
                            $currLen = \strlen($currOut);
                            if ($currLen > $prevLen) {
                                $out .= \substr($currOut, $prevLen);
                                // keep replay output scoped to this pattern fill stream

                                $svgobj['out'] = $prevOut;
                            }
                        }
                    }

                    $out .= $this->graph->getStopTransform();
                }
            }
        } finally {
            $svgobj['patternmode'] = \max(0, (int) $svgobj['patternmode'] - 1);
            unset($patParser);
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
        if ($svgstyle['fill'] === '' || $svgstyle['fill'] === 'none') {
            return '';
        }

        if ((int) ($this->svgobjs[$soid]['patternmode'] ?? 0) > 0) {
            return '';
        }

        $out = '';
        $blendMode = $this->normalizeSVGBlendMode($svgstyle['mix-blend-mode']);
        $baseOpacity = $this->normalizeSVGAlphaValue($svgstyle['opacity']);
        $fillOpacity = $this->normalizeSVGAlphaValue($svgstyle['fill-opacity']);
        $fillAlpha = $baseOpacity * $fillOpacity;

        $regs = [];
        if (\preg_match('/url\([\s]*\#([^\)]*)\)/si', $svgstyle['fill'], $regs)) {
            $fillRef = $regs[1] ?? null;
            if (!\is_string($fillRef) || $fillRef === '') {
                return $out;
            }

            if (\abs($fillAlpha - $baseOpacity) > self::SVGMINFLOATDIFF) {
                $out .= $this->getSVGExtGState(null, $fillAlpha, $blendMode);
            }

            $filldef = $this->svgobjs[$soid]['defs'][$fillRef] ?? null;
            if ($filldef !== null && $filldef !== []) {
                if ($filldef['name'] === 'pattern') {
                    $patternOut = $this->parseSVGStylePattern(
                        $soid,
                        $fillRef,
                        $posx,
                        $posy,
                        $width,
                        $height,
                        $clip_fnc,
                        $clip_par,
                    );
                    if (\str_contains($patternOut, '/Pattern cs /')) {
                        $objstyle = $svgstyle['fill-rule'] === 'evenodd' ? 'F*' : 'F';
                        if (!\str_contains($svgstyle['objstyle'], $objstyle)) {
                            $svgstyle['objstyle'] .= $objstyle;
                        }
                    }

                    return $out . $patternOut;
                }
            }

            return $out
            . $this->parseSVGStyleGradient(
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
        if ($col === null) {
            return $out;
        }

        $rgba = $col->toRgbArray();
        $fillAlphaAlpha = isset($rgba['alpha']) ? $rgba['alpha'] : 1.0;
        if ($fillAlphaAlpha < 1) {
            $fillAlpha *= $this->normalizeSVGAlphaValue($fillAlphaAlpha);
        }

        if (\abs($fillAlpha - $baseOpacity) > self::SVGMINFLOATDIFF) {
            $out .= $this->getSVGExtGState(null, $fillAlpha, $blendMode);
        }

        $objstyle = $svgstyle['fill-rule'] === 'evenodd' ? 'F*' : 'F';
        if (!\str_contains($svgstyle['objstyle'], $objstyle)) {
            $svgstyle['objstyle'] .= $objstyle;
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
    protected function parseSVGStyleClipPath(\XMLParser $parser, int $soid, array $clippaths = []): void
    {
        foreach ($clippaths as $cp) {
            $this->handleSVGTagStart($parser, $cp['name'], $cp['attr'], $soid, true, $cp['tm'] ?? self::TMXID);
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
        if (!isset($this->svgobjs[$soid])) {
            return '';
        }

        $svgobj = $this->svgobjs[$soid];
        $clipPaths = $svgobj['clippaths'] ?? [];
        $gradients = $svgobj['gradients'] ?? [];

        $svgstyle['opacity'] = $this->normalizeSVGAlphaValue($svgstyle['opacity']);
        if ($svgstyle['opacity'] <= 0.0) {
            return '';
        }

        $this->parseSVGStyleClipPath($parser, $soid, $clipPaths);

        $out = '';
        $out .= $this->parseSVGStyleColor($svgstyle);
        $out .= $this->parseSVGStyleMask($soid, $svgstyle);
        $out .= $this->parseSVGStyleClip($svgstyle, $posx, $posy, $width, $height);
        $out .= $this->parseSVGStyleFill(
            $soid,
            $svgstyle,
            $gradients,
            $posx,
            $posy,
            $width,
            $height,
            $clip_fnc,
            $clip_par,
        );
        $out .= $this->parseSVGStyleStroke($soid, $svgstyle);
        $out .= $this->parseSVGStyleFont($svgstyle, $prev_svgstyle);
        $out .= $this->parseSVGStyleRenderingHints($svgstyle);

        $objstyle = $svgstyle['objstyle'];

        return $out;
    }

    /**
     * Handler for the SVG character data.
     *
     * @param \XMLParser $_parser The XML parser calling the handler.
     * @param string $data Character data.
     *
     * @return void
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function handlerSVGCharacter(\XMLParser $_parser, string $data): void
    {
        $soid = (int) \array_key_last($this->svgobjs);
        if ($soid < 0 || !isset($this->svgobjs[$soid]['text'])) {
            return;
        }
        if ((int) $this->svgobjs[$soid]['charskip'] > 0) {
            return;
        }

        $this->svgobjs[$soid]['text'] .= $data;
    }

    /**
     * Handler for the end of an SVG tag.
     *
     * @param \XMLParser $_parser The XML parser calling the handler.
     * @param string $name Name of the element for which this handler is called.
     *
     * @return void
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function handleSVGTagEnd(\XMLParser $_parser, string $name): void
    {
        $name = $this->removeTagNamespace($name);

        $soidkey = \array_key_last($this->svgobjs);
        if ($soidkey === null) {
            return;
        }
        $soid = (int) $soidkey;

        $xmldepth = (int) ($this->svgobjs[$soid]['xmldepth'] ?? 0);
        if ($xmldepth <= 0) {
            $xmldepth = 1;
        }

        try {
            if (\in_array($name, self::SVGCHARDATASKIPTAGS, true)) {
                $this->svgobjs[$soid]['charskip'] = \max(0, (int) $this->svgobjs[$soid]['charskip'] - 1);
                return;
            }

            // E-8: skip subtree ends for non-selected <switch> siblings.
            if (isset($this->svgobjs[$soid]['switchstack']) && $this->svgobjs[$soid]['switchstack'] !== []) {
                $switchkey = (int) \array_key_last($this->svgobjs[$soid]['switchstack']);
                $switchctx = $this->svgobjs[$soid]['switchstack'][$switchkey];
                $skipDepth = (int) $switchctx['skipdepth'];

                if ($skipDepth > 0 && $xmldepth > $skipDepth) {
                    return;
                }

                if ($skipDepth > 0 && $xmldepth === $skipDepth) {
                    $switchctx['skipdepth'] = 0;

                    $this->svgobjs[$soid]['switchstack'][$switchkey] = $switchctx;
                    return;
                }
            }

            if ($this->svgobjs[$soid]['defsmode'] && !\in_array($name, self::SVGDEFSMODEEND, true)) {
                if (\end($this->svgobjs[$soid]['defs']) !== false) {
                    $last_svgdefs_id = (string) \array_key_last($this->svgobjs[$soid]['defs']);
                    if (
                        isset($this->svgobjs[$soid]['defs'][$last_svgdefs_id]['child'])
                        && $this->svgobjs[$soid]['defs'][$last_svgdefs_id]['child'] !== []
                    ) {
                        foreach ($this->svgobjs[$soid]['defs'][$last_svgdefs_id]['child'] as $child) {
                            if (!(isset($child['attr']['id']) && $child['name'] === $name)) {
                                continue;
                            }

                            $closeKey = $child['attr']['id'] . '_CLOSE';

                            $this->svgobjs[$soid]['defs'][$last_svgdefs_id]['child'][$closeKey] = [
                                'name' => $name,
                                'attr' => [
                                    'closing_tag' => true,
                                    'content' => $this->svgobjs[$soid]['text'],
                                ],
                            ];
                            return;
                        }
                        if ($this->svgobjs[$soid]['defs'][$last_svgdefs_id]['name'] === $name) {
                            $closeKey = $last_svgdefs_id . '_CLOSE';

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

            $this->svgobjs[$soid]['out'] .= match ($name) {
                'defs' => $this->parseSVGTagENDdefs($soid),
                'clipPath' => $this->parseSVGTagENDclipPath($soid),
                'svg' => $this->parseSVGTagENDsvg($soid),
                'g' => $this->parseSVGTagENDg($soid),
                'text' => $this->parseSVGTagENDtext($soid),
                'tspan' => $this->parseSVGTagENDtspan($soid),
                'textPath' => $this->parseSVGTagENDtextPath($soid),
                'symbol' => $this->parseSVGTagENDsymbol($soid),
                'marker' => $this->parseSVGTagENDmarker($soid),
                'pattern' => $this->parseSVGTagENDpattern($soid),
                'mask' => $this->parseSVGTagENDmask($soid),
                'filter' => $this->parseSVGTagENDfilter($soid),
                'a' => $this->parseSVGTagENDa($soid),
                'switch' => $this->parseSVGTagENDswitch($soid),
                default => '',
            };

            // Pop completed switch context.
            if (
                isset($this->svgobjs[$soid]['switchstack'])
                && $this->svgobjs[$soid]['switchstack'] !== []
                && $name === 'switch'
            ) {
                $switchkey = (int) \array_key_last($this->svgobjs[$soid]['switchstack']);
                $switchctx = $this->svgobjs[$soid]['switchstack'][$switchkey];
                if ($xmldepth === (int) ($switchctx['depth'] ?? -1)) {
                    unset($this->svgobjs[$soid]['switchstack'][$switchkey]);
                }
            }
        } finally {
            $this->svgobjs[$soid]['xmldepth'] = \max(0, (int) $this->svgobjs[$soid]['xmldepth'] - 1);
        }
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
        return $this->setSVGDefsMode($soid, false);
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
        if (!isset($this->svgobjs[$soid]['tagdepth'])) {
            return '';
        }

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
        if (isset($this->svgobjs[$soid]['styles'])) {
            \array_pop($this->svgobjs[$soid]['styles']);
        }

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
     * Parse the SVG End tag 'textPath'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string
     */
    protected function parseSVGTagENDtextPath(int $soid): string
    {
        return $this->parseSVGTagENDtext($soid);
    }

    /**
     * Render an SVG text run, optionally wrapped in a rotation transform.
     *
     * @param int $soid ID of the current SVG object.
     * @param string $text Text to render.
     * @param float $posx X coordinate.
     * @param float $posy Y coordinate.
     * @param float $width Forced text width.
     * @param float $strokeWidth Text stroke width.
     * @param string $txtanchor Text anchor mode.
     * @param float $rotate Rotation in degrees.
     *
     * @return string
     */
    protected function getSVGTextRunOutput(
        int $soid,
        string $text,
        float $posx,
        float $posy,
        float $width,
        float $strokeWidth,
        string $txtanchor,
        float $rotate = 0.0,
    ): string {
        $out = '';
        if ($rotate !== 0.0) {
            $rad = \deg2rad(-$rotate);
            $cos = \cos($rad);
            $sin = \sin($rad);
            $out .= $this->graph->getStartTransform();
            $out .= $this->getOutSVGTransformation([$cos, $sin, -$sin, $cos, $posx, $posy], $soid);
            $posx = 0.0;
            $posy = 0.0;
        }

        $out .= $this->getTextLine(
            $text,
            $posx,
            $posy,
            $width,
            $strokeWidth,
            0,
            0,
            0,
            true,
            $strokeWidth > 0,
            false,
            false,
            false,
            false,
            $this->svgobjs[$soid]['textmode']['rtl'] ? 'R' : '',
            $txtanchor,
            null,
        );

        if ($rotate !== 0.0) {
            $out .= $this->graph->getStopTransform();
        }

        return $out;
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
        if ($this->svgobjs[$soid]['textmode']['invisible'] ?? false) {
            // Per SVG spec, visibility:hidden text is invisible but still consumes layout space.
            // Advance the cursor by the text width without emitting any drawing operators.
            $txt = $this->svgobjs[$soid]['text'] ?? '';
            if ($txt !== '') {
                if ($this->svgobjs[$soid]['textmode']['vertical'] ?? false) {
                    $this->svgobjs[$soid]['y'] += $this->getStringWidth($txt);
                } else {
                    $this->svgobjs[$soid]['x'] += $this->getStringWidth($txt);
                }
            }

            $this->svgobjs[$soid]['text'] = '';
            if (!$this->svgobjs[$soid]['defsmode']) {
                \array_pop($this->svgobjs[$soid]['styles']);
            }

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

        // S-1: compute Y offset for dominant-baseline / alignment-baseline.
        $baselineOffset = 0.0;
        $baselineKw = $this->svgobjs[$soid]['textmode']['baseline'] ?? 'auto';
        if ($baselineKw !== 'auto' && $baselineKw !== 'alphabetic') {
            $curfont = $this->font->getCurrentFont();
            $ascent = $this->toUnit($curfont['ascent']);
            $descent = $this->toUnit($curfont['descent']);
            $baselineOffset = match ($baselineKw) {
                'hanging' => -$ascent,
                'text-before-edge' => -$ascent,
                'middle', 'central' => -($ascent / 2.0),
                'mathematical' => -($ascent * 0.5),
                'ideographic', 'text-after-edge' => $descent,
                default => 0.0,
            };
        }

        // S-4: first-angle rotation for the text run.
        $rotate = $this->svgobjs[$soid]['textmode']['rotate'] ?? 0.0;

        // E-6: when a textPath is active, derive per-glyph x/y/angle lists
        // from the sampled path and glyph advances before rendering.
        $this->applyTextPathGlyphLayout($soid);

        // S-3: textLength adjustment.
        $textLengthTarget = $this->svgobjs[$soid]['textmode']['textlength'] ?? 0.0;
        $lengthAdjust = $this->svgobjs[$soid]['textmode']['lengthadjust'] ?? 'spacing';
        $forcedWidth = 0.0; // passed to getTextLine for spacing-only adjust
        $scaleX = 1.0; // used for spacingAndGlyphs

        if ($textLengthTarget > 0.0) {
            $actualWidth = $this->getStringWidth($this->svgobjs[$soid]['text']);
            if ($actualWidth > 0.0) {
                if ($lengthAdjust === 'spacingAndGlyphs') {
                    $scaleX = $textLengthTarget / $actualWidth;
                } else {
                    // 'spacing': pass target width to getTextLine for word/letter spacing.
                    $forcedWidth = $textLengthTarget;
                }
            }
        }

        // R-1: multi-value x / y lists — emit one call per character if lists present.
        $xlist = $this->svgobjs[$soid]['textmode']['xlist'] ?? [];
        $ylist = $this->svgobjs[$soid]['textmode']['ylist'] ?? [];
        $rotlist = $this->svgobjs[$soid]['textmode']['rotlist'] ?? [];
        $isVertical = $this->svgobjs[$soid]['textmode']['vertical'] ?? false;

        $out = '';

        if ($xlist !== [] || $ylist !== [] || $rotlist !== []) {
            // Emit individual character positions for multi-value coordinate lists.
            $chars = \mb_str_split($this->svgobjs[$soid]['text'], 1, 'UTF-8');
            foreach ($chars as $idx => $ch) {
                $charX = $xlist[$idx] ?? $curx;
                $charY = ($ylist[$idx] ?? $cury) + $baselineOffset;
                $charRotate = $rotlist[$idx] ?? $rotate;
                $originX = $charX;
                $originY = $charY;
                $out .= $this->getSVGTextRunOutput(
                    $soid,
                    $ch,
                    $charX,
                    $charY,
                    0,
                    $this->svgobjs[$soid]['textmode']['stroke'],
                    $txtanchor,
                    $charRotate,
                );
                if ($isVertical) {
                    $cury = $originY + $this->getStringWidth($ch);
                } else {
                    $curx = $originX + $this->getStringWidth($ch);
                }
            }
        } elseif ($isVertical) {
            // S-2: basic vertical writing mode; stack glyphs along Y axis.
            $chars = \mb_str_split($this->svgobjs[$soid]['text'], 1, 'UTF-8');
            $strokeWidth = (float) ($this->svgobjs[$soid]['textmode']['stroke'] ?? 0.0);
            foreach ($chars as $ch) {
                $charX = $curx;
                $charY = $cury + $baselineOffset;
                $out .= $this->getSVGTextRunOutput($soid, $ch, $charX, $charY, 0, $strokeWidth, $txtanchor, $rotate);
                $cury += $this->getStringWidth($ch);
            }
        } else {
            $renderY = $cury + $baselineOffset;
            if ($scaleX !== 1.0) {
                $out .= $this->graph->getStartTransform();
                $out .= $this->getOutSVGTransformation([$scaleX, 0.0, 0.0, 1.0, 0.0, 0.0], $soid);
            }
            if ($rotate !== 0.0) {
                $out .= $this->getSVGTextRunOutput(
                    $soid,
                    $this->svgobjs[$soid]['text'],
                    $curx,
                    $renderY,
                    $forcedWidth,
                    $this->svgobjs[$soid]['textmode']['stroke'],
                    $txtanchor,
                    $rotate,
                );
            } else {
                $out .= $this->getSVGTextRunOutput(
                    $soid,
                    $this->svgobjs[$soid]['text'],
                    $curx,
                    $renderY,
                    $forcedWidth,
                    $this->svgobjs[$soid]['textmode']['stroke'],
                    $txtanchor,
                );
            }
            if ($scaleX !== 1.0) {
                $out .= $this->graph->getStopTransform();
            }
        }

        $this->svgobjs[$soid]['text'] = ''; // reset text buffer
        $out .= $this->graph->getStopTransform();

        if (!$this->svgobjs[$soid]['defsmode']) {
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
            $soid = (int) \array_key_last($this->svgobjs);
        }
        if (!isset($this->svgobjs[$soid])) {
            return;
        }

        $name = $this->removeTagNamespace($name);

        // Track absolute XML nesting depth for switch child selection.

        $this->svgobjs[$soid]['xmldepth'] = (int) ($this->svgobjs[$soid]['xmldepth'] ?? 0) + 1;
        $xmldepth = (int) $this->svgobjs[$soid]['xmldepth'];

        if (\in_array($name, self::SVGCHARDATASKIPTAGS, true)) {
            $this->svgobjs[$soid]['charskip'] = (int) $this->svgobjs[$soid]['charskip'] + 1;
            return;
        }

        // E-8: render only the first direct child of each <switch>.
        if (isset($this->svgobjs[$soid]['switchstack']) && $this->svgobjs[$soid]['switchstack'] !== []) {
            $switchkey = (int) \array_key_last($this->svgobjs[$soid]['switchstack']);
            $switchctx = $this->svgobjs[$soid]['switchstack'][$switchkey];
            $skipDepth = (int) $switchctx['skipdepth'];

            if ($skipDepth > 0 && $xmldepth > $skipDepth) {
                return;
            }

            if ($xmldepth === ((int) $switchctx['depth'] + 1)) {
                if ($switchctx['selected']) {
                    $switchctx['skipdepth'] = $xmldepth;

                    $this->svgobjs[$soid]['switchstack'][$switchkey] = $switchctx;
                    return;
                }

                $switchctx['selected'] = true;

                $this->svgobjs[$soid]['switchstack'][$switchkey] = $switchctx;
            }
        }

        if ($name === 'switch') {
            $this->svgobjs[$soid]['switchstack'][] = [
                'depth' => $xmldepth,
                'selected' => false,
                'skipdepth' => 0,
            ];
        }

        if ($this->svgobjs[$soid]['clipmode']) {
            $this->svgobjs[$soid]['clippaths'][] = [
                'name' => $name,
                'attr' => $attr,
                'tm' => $this->svgobjs[$soid]['cliptm'],
            ];
            return;
        }

        if ($this->svgobjs[$soid]['defsmode'] && !\in_array($name, self::SVGDEFSMODESTART, true)) {
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
                $defsEntry = $this->svgobjs[$soid]['defs'][$last_svgdefs_id] ?? [];
                if (!isset($defsEntry['child'])) {
                    $defsEntry['child'] = [];
                    $this->svgobjs[$soid]['defs'][$last_svgdefs_id] = $defsEntry;
                }

                if (\is_array($defsEntry['child'] ?? null)) {
                    $attr['id'] = 'DF_' . (\count($defsEntry['child']) + 1);
                    $defsEntry['child'][$attr['id']] = [
                        'name' => $name,
                        'attr' => $attr,
                    ];
                    $this->svgobjs[$soid]['defs'][$last_svgdefs_id] = $defsEntry;
                    return;
                }
            }

            return;
        }

        $this->svgobjs[$soid]['clipmode'] = $clipmode;

        // default style
        $svgstyle = self::DEFSVGSTYLE;
        if (isset($this->svgobjs[$soid]['styles'][0])) {
            $svgstyle = \array_merge(self::DEFSVGSTYLE, $this->svgobjs[$soid]['styles'][0]);
        }

        // last style
        $sid = (int) (\array_key_last($this->svgobjs[$soid]['styles']) ?? 0);
        $psid = \max(0, $sid - 1);
        $prev_svgstyle = self::DEFSVGSTYLE;
        if (isset($this->svgobjs[$soid]['styles'][$psid])) {
            $prev_svgstyle = \array_merge(self::DEFSVGSTYLE, $this->svgobjs[$soid]['styles'][$psid]);
        }
        $attrval = [];

        if (
            $this->svgobjs[$soid]['clipmode']
            && !isset($attr['fill'])
            && (
                !isset($attr['style'])
                || !\preg_match('/[;\"\s]{1}fill[\s]*:[\s]*([^;\"]*)/si', $attr['style'], $attrval)
            )
        ) {
            // default fill attribute for clipping
            $attr['fill'] = 'none';
        }

        if (isset($attr['style']) && $attr['style'] !== '' && $attr['style'][0] !== ';') {
            // fix style for regular expression
            $attr['style'] = ';' . $attr['style'];
        }

        foreach ($prev_svgstyle as $key => $val) {
            if (\in_array($key, self::SVGINHPROP, true)) {
                // inherit previous value
                $svgstyle[$key] = $val;
            }
            if (isset($attr[$key]) && $attr[$key] !== '') {
                // specific attribute settings
                if ($attr[$key] === 'inherit') {
                    $svgstyle[$key] = $val;
                } else {
                    $svgstyle[$key] = $attr[$key];
                }
            } elseif (isset($attr['style']) && $attr['style'] !== '') {
                // CSS style syntax
                $attrval = [];
                if (\preg_match('/[;\"\s]{1}' . $key . '[\s]*:[\s]*([^;\"]*)/si', $attr['style'], $attrval)) {
                    if (isset($attrval[1])) {
                        if ($attrval[1] === 'inherit') {
                            $svgstyle[$key] = $val;
                        } else {
                            $svgstyle[$key] = $attrval[1];
                        }
                    }
                }
            }
        }

        $tmx = $ctm;
        if (isset($attr['transform']) && $attr['transform'] !== '') {
            $tmx = $this->graph->getCtmProduct($tmx, $this->getSVGTransformMatrix($attr['transform']));
        }

        $svgstyle['transfmatrix'] = $tmx;

        $visibility = $svgstyle['visibility'];
        $display = $svgstyle['display'];
        $this->svgobjs[$soid]['textmode']['invisible'] =
            $visibility === 'hidden' || $visibility === 'collapse' || $display === 'none';

        // push new style
        //$this->svgobjs[$soid]['styles'][] = $svgstyle;

        /** @var TSVGStyle $svgstyle */

        // process tags

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
            'polyline' => $this->parseSVGTagSTARTpolygon($parser, $soid, $attr, $svgstyle, $prev_svgstyle, true),
            'polygon' => $this->parseSVGTagSTARTpolygon($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'image' => $this->parseSVGTagSTARTimage($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'text' => $this->parseSVGTagSTARTtext($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'tspan' => $this->parseSVGTagSTARTtspan($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'textPath' => $this->parseSVGTagSTARTtextPath($parser, $soid, $attr, $svgstyle, $prev_svgstyle),
            'use' => $this->parseSVGTagSTARTuse($parser, $soid, $attr),
            'symbol' => $this->parseSVGTagSTARTsymbol($soid, $attr),
            'marker' => $this->parseSVGTagSTARTmarker($soid, $attr),
            'pattern' => $this->parseSVGTagSTARTpattern($soid, $attr),
            'mask' => $this->parseSVGTagSTARTmask($soid, $attr),
            'filter' => $this->parseSVGTagSTARTfilter($soid, $attr),
            'a' => $this->parseSVGTagSTARTa($soid, $attr),
            'switch' => $this->parseSVGTagSTARTswitch($soid),
            default => '',
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
        return $this->setSVGDefsMode($soid, true);
    }

    /**
     * Toggle defs capture mode.
     *
     * @param int $soid ID of the current SVG object.
     * @param bool $enabled Whether defs capture is enabled.
     *
     * @return string
     */
    protected function setSVGDefsMode(int $soid, bool $enabled): string
    {
        $this->svgobjs[$soid]['defsmode'] = $enabled;
        return '';
    }

    /**
     * Register a defs-backed container and enable defs capture mode.
     *
     * @param int $soid ID of the current SVG object.
     * @param string $name SVG tag name.
     * @param TSVGAttributes $attr SVG attributes.
     *
     * @return string
     */
    protected function registerSVGDefsContainer(int $soid, string $name, array $attr): string
    {
        if (isset($attr['id'])) {
            $this->svgobjs[$soid]['defs'][$attr['id']] = [
                'name' => $name,
                'attr' => $attr,
                'child' => [],
            ];
        }

        return $this->setSVGDefsMode($soid, true);
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
        if ($this->svgobjs[$soid]['textmode']['invisible'] ?? false) {
            return '';
        }

        $this->svgobjs[$soid]['clipmode'] = true;

        if ($this->svgobjs[$soid]['clipid'] === '') {
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
        $pageWidth = $page['width'];
        $pageHeight = $page['height'];
        $width = $svgW <= 0.0 ? $pageWidth - $svgX : $svgW;
        $height = $svgH <= 0.0 ? $pageHeight - $svgY : $svgH;
        // draw clipping rect
        $out .= $this->graph->getRawRect($posx, $posy, $width, $height, 'CNZ');
        // parse viewbox, calculate extra transformation matrix
        if (!isset($attr['viewBox']) || $attr['viewBox'] === '') {
            return $out
            . $this->parseSVGStyle($parser, $soid, $svgstyle, $prev_svgstyle, $posx, $posy, $width, $height);
        }
        $tmp = [];
        \preg_match_all('/[0-9]+/', $attr['viewBox'], $tmp);
        $tmp = $tmp[0];
        if (\count($tmp) !== 4) {
            return $out
            . $this->parseSVGStyle($parser, $soid, $svgstyle, $prev_svgstyle, $posx, $posy, $width, $height);
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
        if (isset($attr['preserveAspectRatio']) && $attr['preserveAspectRatio'] !== '') {
            if ($attr['preserveAspectRatio'] === 'none') {
                $fit = 'none';
            } else {
                \preg_match_all('/[a-zA-Z]+/', $attr['preserveAspectRatio'], $tmp);
                $tmp = $tmp[0];
                if (
                    \count($tmp) === 2
                    && \strlen($tmp[0]) === 8
                    && \in_array($tmp[1], ['meet', 'slice', 'none'], true)
                ) {
                    $aspectX = \substr($tmp[0], 0, 4);
                    $aspectY = \substr($tmp[0], 4, 4);
                    $fit = $tmp[1];
                }
            }
        }
        $wsr = $svgW / $vbw;
        $hsr = $svgH / $vbh;
        $asx = 0;
        $asy = 0;
        if ($fit === 'meet' && $hsr < $wsr || $fit === 'slice' && $hsr > $wsr) {
            if ($aspectX === 'xMax') {
                $asx = ($vbw * ($wsr / $hsr)) - $vbw;
            }
            if ($aspectX === 'xMid') {
                $asx = (($vbw * ($wsr / $hsr)) - $vbw) / 2;
            }
            $wsr = $hsr;
        } elseif ($fit === 'meet' && $hsr > $wsr || $fit === 'slice' && $hsr < $wsr) {
            if ($aspectY === 'YMax') {
                $asy = ($vbh * ($hsr / $wsr)) - $vbh;
            }
            if ($aspectY === 'YMid') {
                $asy = (($vbh * ($hsr / $wsr)) - $vbh) / 2;
            }
            $hsr = $wsr;
        }
        $newtmx = [$wsr, 0.0, 0.0, $hsr, ($wsr * ($asx - $vbx)) - $svgX, ($hsr * ($asy - $vby)) - $svgY];
        $tmx = $this->graph->getCtmProduct($tmx, $newtmx);
        $out .= $this->getOutSVGTransformation($tmx, $soid);
        $out .= $this->parseSVGStyle($parser, $soid, $svgstyle, $prev_svgstyle, $posx, $posy, $width, $height);

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

        \array_push($this->svgobjs[$soid]['styles'], $svgstyle);
        $out .= $this->graph->getStartTransform();
        $posx = isset($attr['x']) ? $this->svgUnitToUnit($attr['x'], $soid) : 0.0;
        $posy = isset($attr['y']) ? $this->svgUnitToUnit($attr['y'], $soid) : 0.0;
        $width = 1.0; // isset($attr['width']) ? $this->svgUnitToUnit($attr['width'], $soid) : 1.0;
        $height = 1.0; // isset($attr['height']) ? $this->svgUnitToUnit($attr['height'], $soid) : 1.0;
        $tmx = $this->graph->getCtmProduct($svgstyle['transfmatrix'], [$width, 0.0, 0.0, $height, $posx, $posy]);
        $out .= $this->getOutSVGTransformation($tmx, $soid);
        $out .= $this->parseSVGStyle($parser, $soid, $svgstyle, $prev_svgstyle, $posx, $posy, $width, $height);

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
        if ($this->pdfa === 1 || $this->pdfa === 2) {
            return '';
        }

        if (!isset($attr['id'])) {
            $attr['id'] = 'GR_' . (\count($this->svgobjs[$soid]['gradients']) + 1);
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
        if (
            !isset($attr['x1']) && !isset($attr['y1']) && !isset($attr['x2']) && !isset($attr['y2'])
            || (
                isset($attr['x1'])
                && \substr($attr['x1'], -1) === '%'
                || isset($attr['y1'])
                && \substr($attr['y1'], -1) === '%'
                || isset($attr['x2'])
                && \substr($attr['x2'], -1) === '%'
                || isset($attr['y2'])
                && \substr($attr['y2'], -1) === '%'
            )
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
            $this->svgobjs[$soid]['gradients'][$gid]['gradientTransform'] = $this->getSVGTransformMatrix(
                $attr['gradientTransform'],
            );
        }
        $this->svgobjs[$soid]['gradients'][$gid]['coords'] = [$px1, $py1, $px2, $py2];
        $gradHref = $attr['xlink:href'] ?? $attr['href'] ?? '';
        if ($gradHref !== '') {
            // gradient is defined on another place
            $this->svgobjs[$soid]['gradients'][$gid]['xref'] = \substr($gradHref, 1);
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
        if ($this->pdfa === 1 || $this->pdfa === 2) {
            return '';
        }

        if (!isset($attr['id'])) {
            $attr['id'] = 'GR_' . (\count($this->svgobjs[$soid]['gradients']) + 1);
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
        if (
            !isset($attr['cx']) && !isset($attr['cy'])
            || (
                isset($attr['cx'])
                && \substr($attr['cx'], -1) === '%'
                || isset($attr['cy'])
                && \substr($attr['cy'], -1) === '%'
            )
        ) {
            $this->svgobjs[$soid]['gradients'][$gid]['mode'] = 'percentage';
        } elseif (isset($attr['r']) && \is_numeric($attr['r']) && $attr['r'] <= 1) {
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
            $this->svgobjs[$soid]['gradients'][$gid]['gradientTransform'] = $this->getSVGTransformMatrix(
                $attr['gradientTransform'],
            );
        }
        $this->svgobjs[$soid]['gradients'][$gid]['coords'] = [$pcx, $pcy, $pfx, $pfy, $grr];
        $gradHref = $attr['xlink:href'] ?? $attr['href'] ?? '';
        if ($gradHref !== '') {
            // gradient is defined on another place
            $this->svgobjs[$soid]['gradients'][$gid]['xref'] = \substr($gradHref, 1);
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
    protected function parseSVGTagSTARTstop(int $soid, array $attr, array $svgstyle): string
    {
        $offset = isset($attr['offset']) ? $this->svgUnitToUnit($attr['offset'], $soid) : 0.0;
        $stop_color = $svgstyle['stop-color'];
        // Normalize stop colors to hex RGB so all gradient stops share one
        // color space. Without this, named colors (e.g. "white") resolve to
        // CMYK while hex colors resolve to RGB, producing corrupt gradients.
        $colobj = $this->color->getColorObj($stop_color);
        if ($colobj !== null) {
            $stop_color = $colobj->getRgbHexColor();
        }
        $opacity = \max(0.0, \min(1.0, \floatval($svgstyle['stop-opacity'])));
        $gid = $this->svgobjs[$soid]['gradientid'];

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
        if ($this->svgobjs[$soid]['textmode']['invisible'] ?? false) {
            return '';
        }
        if (!isset($attr['d']) || $attr['d'] === '') {
            return '';
        }

        $ptd = \trim($attr['d']);

        $posx = isset($attr['x']) ? $this->svgUnitToUnit($attr['x'], $soid) : 0.0;
        $posy = isset($attr['y']) ? $this->svgUnitToUnit($attr['y'], $soid) : 0.0;
        $width = isset($attr['width']) ? $this->svgUnitToUnit($attr['width'], $soid) : 1.0;
        $height = isset($attr['height']) ? $this->svgUnitToUnit($attr['height'], $soid) : 1.0;
        $tmx = $this->graph->getCtmProduct($svgstyle['transfmatrix'], [$width, 0.0, 0.0, $height, $posx, $posy]);

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

        if ($obstyle !== '') {
            $out .= $this->getSVGPath($soid, $ptd, $obstyle);
        }

        $segments = $this->getSVGPathMarkerSegments($soid, $ptd);
        $out .= $this->renderSVGMarkersForSegments($parser, $soid, $svgstyle, $segments);

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
        if ($this->svgobjs[$soid]['textmode']['invisible'] ?? false) {
            return '';
        }
        $posx = isset($attr['x']) ? $this->svgUnitToUnit($attr['x'], $soid) : 0.0;
        $posy = isset($attr['y']) ? $this->svgUnitToUnit($attr['y'], $soid) : 0.0;
        $width = isset($attr['width']) ? $this->svgUnitToUnit($attr['width'], $soid) : 0.0;
        $height = isset($attr['height']) ? $this->svgUnitToUnit($attr['height'], $soid) : 0.0;
        $prx = isset($attr['rx']) ? $this->svgUnitToUnit($attr['rx'], $soid) : 0.0;
        $pry = isset($attr['ry']) ? $this->svgUnitToUnit($attr['ry'], $soid) : $prx;
        $out = '';
        if ($this->svgobjs[$soid]['clipmode']) {
            $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
            $out .= $this->graph->getRoundedRect($posx, $posy, $width, $height, $prx, $pry, '1111', 'CNZ');
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
        if ($obstyle !== '') {
            $out .= $this->graph->getRoundedRect($posx, $posy, $width, $height, $prx, $pry, '1111', $obstyle);
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
        if ($this->svgobjs[$soid]['textmode']['invisible'] ?? false) {
            return '';
        }
        $crr = isset($attr['r']) ? $this->svgUnitToUnit($attr['r'], $soid) : 0.0;
        if (isset($attr['cx'])) {
            $ctx = $this->svgUnitToUnit($attr['cx'], $soid);
        } elseif (isset($attr['x'])) {
            $ctx = $this->svgUnitToUnit($attr['x'], $soid);
        } else {
            $ctx = 0.0;
        }
        if (isset($attr['cy'])) {
            $cty = $this->svgUnitToUnit($attr['cy'], $soid);
        } elseif (isset($attr['y'])) {
            $cty = $this->svgUnitToUnit($attr['y'], $soid);
        } else {
            $cty = 0.0;
        }
        $posx = $ctx - $crr;
        $posy = $cty - $crr;
        $width = 2 * $crr;
        $height = $width;
        $out = '';
        if ($this->svgobjs[$soid]['clipmode']) {
            $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
            $out .= $this->graph->getCircle($ctx, $cty, $crr, 0, 360, 'CNZ', [], 8);
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
        if ($obstyle !== '') {
            $out .= $this->graph->getCircle($ctx, $cty, $crr, 0, 360, $obstyle, [], 8);
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
        if ($this->svgobjs[$soid]['textmode']['invisible'] ?? false) {
            return '';
        }
        $erx = isset($attr['rx']) ? $this->svgUnitToUnit($attr['rx'], $soid) : 0.0;
        $ery = isset($attr['ry']) ? $this->svgUnitToUnit($attr['ry'], $soid) : 0.0;
        if (isset($attr['cx'])) {
            $ecx = $this->svgUnitToUnit($attr['cx'], $soid);
        } elseif (isset($attr['x'])) {
            $ecx = $this->svgUnitToUnit($attr['x'], $soid);
        } else {
            $ecx = 0.0;
        }
        if (isset($attr['cy'])) {
            $ecy = $this->svgUnitToUnit($attr['cy'], $soid);
        } elseif (isset($attr['y'])) {
            $ecy = $this->svgUnitToUnit($attr['y'], $soid);
        } else {
            $ecy = 0.0;
        }
        $posx = $ecx - $erx;
        $posy = $ecy - $ery;
        $width = 2 * $erx;
        $height = 2 * $ery;
        $out = '';
        if ($this->svgobjs[$soid]['clipmode']) {
            $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
            $out .= $this->graph->getEllipse($ecx, $ecy, $erx, $ery, 0, 0, 360, 'CNZ', [], 8);
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
        if ($obstyle !== '') {
            $out .= $this->graph->getEllipse($ecx, $ecy, $erx, $ery, 0, 0, 360, $obstyle, [], 8);
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
        if ($this->svgobjs[$soid]['textmode']['invisible'] ?? false) {
            return '';
        }
        if ($this->svgobjs[$soid]['clipmode']) {
            return '';
        }
        $posx1 = isset($attr['x1']) ? $this->svgUnitToUnit($attr['x1'], $soid) : 0.0;
        $posy1 = isset($attr['y1']) ? $this->svgUnitToUnit($attr['y1'], $soid) : 0.0;
        $posx2 = isset($attr['x2']) ? $this->svgUnitToUnit($attr['x2'], $soid) : 0.0;
        $posy2 = isset($attr['y2']) ? $this->svgUnitToUnit($attr['y2'], $soid) : 0.0;
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
        $out .= $this->graph->getLine($posx1, $posy1, $posx2, $posy2);
        $out .= $this->parseSVGLineMarkers($parser, $soid, $svgstyle, $posx1, $posy1, $posx2, $posy2);
        $out .= $this->graph->getStopTransform();
        return $out;
    }

    /**
     * Parse marker URLs and render start/end markers for a straight line segment.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param float $startX Segment start X.
     * @param float $startY Segment start Y.
     * @param float $endX Segment end X.
     * @param float $endY Segment end Y.
     *
     * @return string
     */
    protected function parseSVGLineMarkers(
        \XMLParser $parser,
        int $soid,
        array $svgstyle,
        float $startX,
        float $startY,
        float $endX,
        float $endY,
    ): string {
        if ((int) ($this->svgobjs[$soid]['markermode'] ?? 0) > 0) {
            return '';
        }

        return $this->renderSVGMarkersForSegments($parser, $soid, $svgstyle, [[
            'x1' => $startX,
            'y1' => $startY,
            'x2' => $endX,
            'y2' => $endY,
            'angle' => \rad2deg(\atan2($endY - $startY, $endX - $startX)),
        ]]);
    }

    /**
     * Render marker-start/marker-mid/marker-end for an array of path segments.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param array<int, array{x1: float, y1: float, x2: float, y2: float, angle: float}> $segments
     *
     * @return string
     */
    protected function renderSVGMarkersForSegments(
        \XMLParser $parser,
        int $soid,
        array $svgstyle,
        array $segments,
    ): string {
        if ($segments === [] || (int) ($this->svgobjs[$soid]['markermode'] ?? 0) > 0) {
            return '';
        }

        $strokeWidth = $this->svgUnitToUnit($svgstyle['stroke-width'], $soid);
        if ($strokeWidth <= 0.0) {
            $strokeWidth = 1.0;
        }

        $first = $segments[0];
        $last = $segments[\count($segments) - 1];

        $markerAll = $svgstyle['marker'];
        $markerStartRaw = $svgstyle['marker-start'];
        $markerMidRaw = $svgstyle['marker-mid'];
        $markerEndRaw = $svgstyle['marker-end'];

        // If any specific marker anchor is explicitly set to a non-default
        // value, respect explicit 'none' on other anchors instead of falling
        // back from shorthand.
        $hasExplicitSpecific =
            $markerStartRaw !== '' && $markerStartRaw !== 'none'
            || $markerMidRaw !== '' && $markerMidRaw !== 'none'
            || $markerEndRaw !== '' && $markerEndRaw !== 'none';

        $markerStart = $this->getSVGResolvedMarker($markerStartRaw, $markerAll, !$hasExplicitSpecific);
        $markerMid = $this->getSVGResolvedMarker($markerMidRaw, $markerAll, !$hasExplicitSpecific);
        $markerEnd = $this->getSVGResolvedMarker($markerEndRaw, $markerAll, !$hasExplicitSpecific);

        $out = '';
        $out .= $this->renderSVGMarker(
            $parser,
            $soid,
            $markerStart,
            $first['x1'],
            $first['y1'],
            $first['angle'],
            $strokeWidth,
            true,
        );

        $midMarker = $markerMid;
        if ($midMarker !== '' && $midMarker !== 'none' && \count($segments) > 1) {
            for ($idx = 1, $max = \count($segments); $idx < $max; ++$idx) {
                $prev = $segments[$idx - 1];
                $next = $segments[$idx];
                $prevAngleRad = \deg2rad($prev['angle']);
                $nextAngleRad = \deg2rad($next['angle']);
                $vectorX = \cos($prevAngleRad) + \cos($nextAngleRad);
                $vectorY = \sin($prevAngleRad) + \sin($nextAngleRad);
                $midAngle =
                    \abs($vectorX) < self::SVGMINFLOATDIFF && \abs($vectorY) < self::SVGMINFLOATDIFF
                        ? $next['angle']
                        : \rad2deg(\atan2($vectorY, $vectorX));
                $out .= $this->renderSVGMarker(
                    $parser,
                    $soid,
                    $midMarker,
                    $next['x1'],
                    $next['y1'],
                    $midAngle,
                    $strokeWidth,
                    false,
                );
            }

            $closedPath =
                \abs($first['x1'] - $last['x2']) < self::SVGMINFLOATDIFF
                && \abs($first['y1'] - $last['y2']) < self::SVGMINFLOATDIFF;
            if ($closedPath) {
                $prevAngleRad = \deg2rad($last['angle']);
                $nextAngleRad = \deg2rad($first['angle']);
                $vectorX = \cos($prevAngleRad) + \cos($nextAngleRad);
                $vectorY = \sin($prevAngleRad) + \sin($nextAngleRad);
                $midAngle =
                    \abs($vectorX) < self::SVGMINFLOATDIFF && \abs($vectorY) < self::SVGMINFLOATDIFF
                        ? $first['angle']
                        : \rad2deg(\atan2($vectorY, $vectorX));
                $out .= $this->renderSVGMarker(
                    $parser,
                    $soid,
                    $midMarker,
                    $first['x1'],
                    $first['y1'],
                    $midAngle,
                    $strokeWidth,
                    false,
                );
            }
        }

        $out .= $this->renderSVGMarker(
            $parser,
            $soid,
            $markerEnd,
            $last['x2'],
            $last['y2'],
            $last['angle'],
            $strokeWidth,
            false,
        );

        return $out;
    }

    /**
     * Resolve marker shorthand fallback for marker-start/mid/end.
     *
     * @param string $specific Marker-specific value.
     * @param string $markerAll Shorthand marker value.
     * @param bool $fallbackFromNone If true, 'none' can fallback to shorthand.
     *
     * @return string
     */
    protected function getSVGResolvedMarker(string $specific, string $markerAll, bool $fallbackFromNone = true): string
    {
        if ($specific !== '') {
            if ($specific === 'none' && !$fallbackFromNone) {
                return 'none';
            }
            if ($specific !== 'none') {
                return $specific;
            }
        }

        if ($specific === 'none' && $fallbackFromNone && ($markerAll === '' || $markerAll === 'none')) {
            return $specific;
        }

        if ($markerAll !== '' && $markerAll !== 'none') {
            return $markerAll;
        }

        return $specific;
    }

    /**
     * Build drawable segment anchors from an SVG path string for marker placement.
     *
     * @param int $soid SVG object ID.
     * @param string $attrd Path data.
     *
     * @return array<
     *   int,
     *   array{x1: float, y1: float, x2: float, y2: float, angle: float, startAngle: float, endAngle: float}
     * >
     */
    protected function getSVGPathMarkerSegments(int $soid, string $attrd): array
    {
        $attrd = \preg_replace('/([0-9ACHLMQSTVZ])([\-\+])/si', '\\1 \\2', $attrd);
        if (!\is_string($attrd) || $attrd === '') {
            return [];
        }

        $attrd = \preg_replace('/(\.[0-9]+)(\.)/s', '\\1 \\2', $attrd);
        if (!\is_string($attrd) || $attrd === '') {
            return [];
        }

        $paths = [];
        \preg_match_all('/([ACHLMQSTVZ])[\s]*+([^ACHLMQSTVZ\"]*+)/si', $attrd, $paths, PREG_SET_ORDER);
        if ($paths === []) {
            return [];
        }

        $segments = [];
        $currentX = 0.0;
        $currentY = 0.0;
        $subpathStartX = 0.0;
        $subpathStartY = 0.0;
        $prevCmd = '';
        $cp2x = 0.0;
        $cp2y = 0.0;
        $qp1x = 0.0;
        $qp1y = 0.0;

        foreach ($paths as $path) {
            $cmd = \trim($path[1]);
            if ($cmd === '') {
                continue;
            }

            $upper = \strtoupper($cmd);
            $rel = \strtolower($cmd) === $cmd;
            $raw = [];
            \preg_match_all('/-?\d+(?:\.\d+)?/', \trim($path[2]), $raw);
            $rawparams = $raw[0];
            $params = [];
            foreach ($rawparams as $prv) {
                $val = $this->svgUnitToUnit($prv, $soid);
                $params[] = \abs($val) < $this->svgminunitlen ? 0.0 : $val;
            }

            $addSegment = static function (float $startX, float $startY, float $endX, float $endY) use (
                &$segments,
            ): void {
                if (\abs($endX - $startX) < self::SVGMINFLOATDIFF && \abs($endY - $startY) < self::SVGMINFLOATDIFF) {
                    return;
                }
                $segments[] = [
                    'x1' => $startX,
                    'y1' => $startY,
                    'x2' => $endX,
                    'y2' => $endY,
                    'angle' => \rad2deg(\atan2($endY - $startY, $endX - $startX)),
                    'startAngle' => \rad2deg(\atan2($endY - $startY, $endX - $startX)),
                    'endAngle' => \rad2deg(\atan2($endY - $startY, $endX - $startX)),
                ];
            };

            $setLastSegmentAngles = static function (float $startAngle, float $endAngle) use (&$segments): void {
                $last = \array_key_last($segments);
                if (!\is_int($last)) {
                    return;
                }

                $lastSegment = $segments[$last];
                $lastSegment['startAngle'] = $startAngle;
                $lastSegment['endAngle'] = $endAngle;
                $segments[$last] = $lastSegment;
            };

            if ($upper === 'M') {
                for ($i = 0; ($i + 1) < \count($params); $i += 2) {
                    $nextX = $params[$i] + ($rel ? $currentX : 0.0);
                    $nextY = $params[$i + 1] + ($rel ? $currentY : 0.0);
                    if ($i === 0) {
                        $currentX = $nextX;
                        $currentY = $nextY;
                        $subpathStartX = $nextX;
                        $subpathStartY = $nextY;
                    } else {
                        $addSegment($currentX, $currentY, $nextX, $nextY);
                        $currentX = $nextX;
                        $currentY = $nextY;
                    }
                }
                $prevCmd = 'M';
                continue;
            }

            if ($upper === 'L') {
                for ($i = 0; ($i + 1) < \count($params); $i += 2) {
                    $nextX = $params[$i] + ($rel ? $currentX : 0.0);
                    $nextY = $params[$i + 1] + ($rel ? $currentY : 0.0);
                    $addSegment($currentX, $currentY, $nextX, $nextY);
                    $currentX = $nextX;
                    $currentY = $nextY;
                }
                $prevCmd = 'L';
                continue;
            }

            if ($upper === 'H') {
                foreach ($params as $val) {
                    $nextX = $val + ($rel ? $currentX : 0.0);
                    $addSegment($currentX, $currentY, $nextX, $currentY);
                    $currentX = $nextX;
                }
                $prevCmd = 'H';
                continue;
            }

            if ($upper === 'V') {
                foreach ($params as $val) {
                    $nextY = $val + ($rel ? $currentY : 0.0);
                    $addSegment($currentX, $currentY, $currentX, $nextY);
                    $currentY = $nextY;
                }
                $prevCmd = 'V';
                continue;
            }

            if ($upper === 'C') {
                for ($i = 0; ($i + 5) < \count($params); $i += 6) {
                    $cp1x = $params[$i] + ($rel ? $currentX : 0.0);
                    $cp1y = $params[$i + 1] + ($rel ? $currentY : 0.0);
                    $cp2x = $params[$i + 2] + ($rel ? $currentX : 0.0);
                    $cp2y = $params[$i + 3] + ($rel ? $currentY : 0.0);
                    $nextX = $params[$i + 4] + ($rel ? $currentX : 0.0);
                    $nextY = $params[$i + 5] + ($rel ? $currentY : 0.0);
                    $addSegment($currentX, $currentY, $nextX, $nextY);
                    $setLastSegmentAngles(
                        \rad2deg(\atan2($cp1y - $currentY, $cp1x - $currentX)),
                        \rad2deg(\atan2($nextY - $cp2y, $nextX - $cp2x)),
                    );
                    $currentX = $nextX;
                    $currentY = $nextY;
                }
                $prevCmd = 'C';
                continue;
            }

            if ($upper === 'S') {
                for ($i = 0; ($i + 3) < \count($params); $i += 4) {
                    $cp1x = $currentX;
                    $cp1y = $currentY;
                    if ($prevCmd === 'C' || $prevCmd === 'S') {
                        $cp1x = (2 * $currentX) - $cp2x;
                        $cp1y = (2 * $currentY) - $cp2y;
                    }
                    $cp2x = $params[$i] + ($rel ? $currentX : 0.0);
                    $cp2y = $params[$i + 1] + ($rel ? $currentY : 0.0);
                    $nextX = $params[$i + 2] + ($rel ? $currentX : 0.0);
                    $nextY = $params[$i + 3] + ($rel ? $currentY : 0.0);
                    $addSegment($currentX, $currentY, $nextX, $nextY);
                    $setLastSegmentAngles(
                        \rad2deg(\atan2($cp1y - $currentY, $cp1x - $currentX)),
                        \rad2deg(\atan2($nextY - $cp2y, $nextX - $cp2x)),
                    );
                    $currentX = $nextX;
                    $currentY = $nextY;
                }
                $prevCmd = 'S';
                continue;
            }

            if ($upper === 'Q') {
                for ($i = 0; ($i + 3) < \count($params); $i += 4) {
                    $qp1x = $params[$i] + ($rel ? $currentX : 0.0);
                    $qp1y = $params[$i + 1] + ($rel ? $currentY : 0.0);
                    $nextX = $params[$i + 2] + ($rel ? $currentX : 0.0);
                    $nextY = $params[$i + 3] + ($rel ? $currentY : 0.0);
                    $addSegment($currentX, $currentY, $nextX, $nextY);
                    $setLastSegmentAngles(
                        \rad2deg(\atan2($qp1y - $currentY, $qp1x - $currentX)),
                        \rad2deg(\atan2($nextY - $qp1y, $nextX - $qp1x)),
                    );
                    $currentX = $nextX;
                    $currentY = $nextY;
                }
                $prevCmd = 'Q';
                continue;
            }

            if ($upper === 'T') {
                for ($i = 0; ($i + 1) < \count($params); $i += 2) {
                    if ($prevCmd === 'Q' || $prevCmd === 'T') {
                        $qp1x = (2 * $currentX) - $qp1x;
                        $qp1y = (2 * $currentY) - $qp1y;
                    } else {
                        $qp1x = $currentX;
                        $qp1y = $currentY;
                    }
                    $nextX = $params[$i] + ($rel ? $currentX : 0.0);
                    $nextY = $params[$i + 1] + ($rel ? $currentY : 0.0);
                    $addSegment($currentX, $currentY, $nextX, $nextY);
                    $setLastSegmentAngles(
                        \rad2deg(\atan2($qp1y - $currentY, $qp1x - $currentX)),
                        \rad2deg(\atan2($nextY - $qp1y, $nextX - $qp1x)),
                    );
                    $currentX = $nextX;
                    $currentY = $nextY;
                }
                $prevCmd = 'T';
                continue;
            }

            if ($upper === 'A') {
                for ($i = 0; ($i + 6) < \count($params); $i += 7) {
                    $startX0 = $currentX;
                    $startY0 = $currentY;
                    $radiusX = (float) \max(\abs($params[$i]), .000_000_001);
                    $radiusY = (float) \max(\abs($params[$i + 1]), .000_000_001);
                    $xAxisRot = (float) ($rawparams[$i + 2] ?? 0.0);
                    $largeArcFlag = (float) ($rawparams[$i + 3] ?? 0.0) >= 0.5;
                    $sweepFlag = (float) ($rawparams[$i + 4] ?? 0.0) >= 0.5;
                    $nextX = $params[$i + 5] + ($rel ? $currentX : 0.0);
                    $nextY = $params[$i + 6] + ($rel ? $currentY : 0.0);
                    $addSegment($currentX, $currentY, $nextX, $nextY);
                    $samples = $this->sampleTextPathArc(
                        $startX0,
                        $startY0,
                        $radiusX,
                        $radiusY,
                        $xAxisRot,
                        $largeArcFlag,
                        $sweepFlag,
                        $nextX,
                        $nextY,
                    );

                    $startPx = $nextX;
                    $startPy = $nextY;
                    if ($samples !== []) {
                        $startPx = $samples[0][0];
                        $startPy = $samples[0][1];
                    }
                    $startDx = $startPx - $startX0;
                    $startDy = $startPy - $startY0;
                    if (\abs($startDx) < self::SVGMINFLOATDIFF && \abs($startDy) < self::SVGMINFLOATDIFF) {
                        $startDx = $nextX - $startX0;
                        $startDy = $nextY - $startY0;
                    }

                    $endAx = $startX0;
                    $endAy = $startY0;
                    if ($samples !== []) {
                        $sampleCount = \count($samples);
                        if ($sampleCount >= 2) {
                            $endAx = $samples[$sampleCount - 2][0];
                            $endAy = $samples[$sampleCount - 2][1];
                        }
                    }

                    $setLastSegmentAngles(
                        \rad2deg(\atan2($startDy, $startDx)),
                        \rad2deg(\atan2($nextY - $endAy, $nextX - $endAx)),
                    );
                    $currentX = $nextX;
                    $currentY = $nextY;
                }
                $prevCmd = 'A';
                continue;
            }

            if ($upper === 'Z') {
                $addSegment($currentX, $currentY, $subpathStartX, $subpathStartY);
                $currentX = $subpathStartX;
                $currentY = $subpathStartY;
                $prevCmd = 'Z';
            }
        }

        return $segments;
    }

    /**
     * Build line segments from a polygon/polyline numeric point list.
     *
     * @param array<int, float> $pset Point list (x1,y1,x2,y2,...).
     * @param bool $closed Whether to close back to the first point.
     *
     * @return array<int, array{x1: float, y1: float, x2: float, y2: float, angle: float, startAngle: float, endAngle: float}>
     */
    protected function getSVGPolylineSegments(array $pset, bool $closed = false): array
    {
        $segments = [];
        $pointCount = (int) \floor(\count($pset) / 2);
        if ($pointCount < 2) {
            return $segments;
        }

        for ($i = 0; $i < ($pointCount - 1); ++$i) {
            $startX = $pset[2 * $i];
            $startY = $pset[(2 * $i) + 1];
            $endX = $pset[2 * ($i + 1)];
            $endY = $pset[(2 * ($i + 1)) + 1];
            if (\abs($endX - $startX) < self::SVGMINFLOATDIFF && \abs($endY - $startY) < self::SVGMINFLOATDIFF) {
                continue;
            }
            $segments[] = [
                'x1' => $startX,
                'y1' => $startY,
                'x2' => $endX,
                'y2' => $endY,
                'angle' => \rad2deg(\atan2($endY - $startY, $endX - $startX)),
                'startAngle' => \rad2deg(\atan2($endY - $startY, $endX - $startX)),
                'endAngle' => \rad2deg(\atan2($endY - $startY, $endX - $startX)),
            ];
        }

        if ($closed) {
            $startX = $pset[2 * ($pointCount - 1)];
            $startY = $pset[(2 * ($pointCount - 1)) + 1];
            $endX = $pset[0];
            $endY = $pset[1];
            if (\abs($endX - $startX) >= self::SVGMINFLOATDIFF || \abs($endY - $startY) >= self::SVGMINFLOATDIFF) {
                $segments[] = [
                    'x1' => $startX,
                    'y1' => $startY,
                    'x2' => $endX,
                    'y2' => $endY,
                    'angle' => \rad2deg(\atan2($endY - $startY, $endX - $startX)),
                    'startAngle' => \rad2deg(\atan2($endY - $startY, $endX - $startX)),
                    'endAngle' => \rad2deg(\atan2($endY - $startY, $endX - $startX)),
                ];
            }
        }

        return $segments;
    }

    /**
     * Extract marker definition ID from a marker style value.
     *
     * @param string $marker Marker style value.
     *
     * @return string
     */
    protected function getSVGMarkerId(string $marker): string
    {
        if ($marker === '' || $marker === 'none') {
            return '';
        }

        $matches = [];
        if (!\preg_match('/^url\([\s]*#([^)\s]+)[\s]*\)$/', \trim($marker), $matches)) {
            return '';
        }

        return $matches[1];
    }

    /**
     * Render a marker definition at a given anchor point.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param string $marker Marker style value.
     * @param float $anchorX Anchor X.
     * @param float $anchorY Anchor Y.
     * @param float $segmentAngle Segment angle in degrees.
     * @param float $strokeWidth Segment stroke width.
     * @param bool $isStart Whether this is a start marker.
     *
     * @return string
     */
    protected function renderSVGMarker(
        \XMLParser $parser,
        int $soid,
        string $marker,
        float $anchorX,
        float $anchorY,
        float $segmentAngle,
        float $strokeWidth,
        bool $isStart,
    ): string {
        $markerId = $this->getSVGMarkerId($marker);
        if ($markerId === '' || !isset($this->svgobjs[$soid]['defs'][$markerId])) {
            return '';
        }

        /** @var TSVGAttribs $markerdef */
        $markerdef = $this->svgobjs[$soid]['defs'][$markerId];
        if ($markerdef['name'] !== 'marker') {
            return '';
        }

        $markerAttr = $markerdef['attr'];

        $mkw = isset($markerAttr['markerWidth']) ? $this->svgUnitToUnit($markerAttr['markerWidth'], $soid) : 3.0;
        $mkh = isset($markerAttr['markerHeight']) ? $this->svgUnitToUnit($markerAttr['markerHeight'], $soid) : 3.0;

        $vbx = 0.0;
        $vby = 0.0;
        $vbw = $mkw;
        $vbh = $mkh;
        if (isset($markerAttr['viewBox']) && $markerAttr['viewBox'] !== '') {
            $vals = \preg_split('/[\s,]+/', \trim($markerAttr['viewBox']), -1, \PREG_SPLIT_NO_EMPTY);
            if (\is_array($vals) && \count($vals) >= 4) {
                $vbx = $this->svgUnitToUnit($vals[0], $soid);
                $vby = $this->svgUnitToUnit($vals[1], $soid);
                $vbw = \abs($this->svgUnitToUnit($vals[2], $soid));
                $vbh = \abs($this->svgUnitToUnit($vals[3], $soid));
            }
        }

        $refX = $this->resolveSVGMarkerRefCoordinate($markerAttr['refX'] ?? '', $vbx, $vbw, $soid);
        $refY = $this->resolveSVGMarkerRefCoordinate($markerAttr['refY'] ?? '', $vby, $vbh, $soid);
        $markerUnits = $markerAttr['markerUnits'] ?? 'strokeWidth';
        $markerScale = $markerUnits === 'userSpaceOnUse' ? 1.0 : $strokeWidth;

        $viewScaleX = 1.0;
        $viewScaleY = 1.0;
        $viewOffsetX = 0.0;
        $viewOffsetY = 0.0;
        $aspectRaw = $markerAttr['preserveAspectRatio'] ?? 'xMidYMid meet';
        $aspectFit = 'meet';
        $aspectX = 'xMid';
        $aspectY = 'YMid';
        if (\trim($aspectRaw) === 'none') {
            $aspectFit = 'none';
        } else {
            $aspectMatches = [];
            \preg_match_all('/[a-zA-Z]+/', $aspectRaw, $aspectMatches);
            $tokens = $aspectMatches[0];
            if ($tokens !== []) {
                if (\strtolower($tokens[0]) === 'defer') {
                    \array_shift($tokens);
                }

                if ($tokens !== [] && \strlen($tokens[0]) === 8) {
                    $alignToken = $tokens[0];
                    $aspectX = \substr($alignToken, 0, 4);
                    $aspectY = \substr($alignToken, 4, 4);
                    if (isset($tokens[1]) && \in_array($tokens[1], ['meet', 'slice', 'none'], true)) {
                        $aspectFit = $tokens[1];
                    }
                } elseif ($tokens !== [] && \in_array($tokens[0], ['meet', 'slice', 'none'], true)) {
                    $aspectFit = $tokens[0];
                }
            }
        }

        if ($vbw > 0.0 && $vbh > 0.0 && $mkw > 0.0 && $mkh > 0.0) {
            if ($aspectFit === 'none') {
                $viewScaleX = $mkw / $vbw;
                $viewScaleY = $mkh / $vbh;
            } else {
                $scaleX = $mkw / $vbw;
                $scaleY = $mkh / $vbh;
                $scale = $aspectFit === 'slice' ? \max($scaleX, $scaleY) : \min($scaleX, $scaleY);
                $viewScaleX = $scale;
                $viewScaleY = $scale;
                $scaledW = $vbw * $scale;
                $scaledH = $vbh * $scale;
                $viewOffsetX = match ($aspectX) {
                    'xMax' => $mkw - $scaledW,
                    'xMid' => ($mkw - $scaledW) / 2.0,
                    default => 0.0,
                };
                $viewOffsetY = match ($aspectY) {
                    'YMax' => $mkh - $scaledH,
                    'YMid' => ($mkh - $scaledH) / 2.0,
                    default => 0.0,
                };
            }
        }

        $orient = \trim($markerAttr['orient'] ?? '0');
        $angle = 0.0;
        if ($orient === 'auto' || $orient === 'auto-start-reverse') {
            $angle = $segmentAngle;
            if ($isStart && $orient === 'auto-start-reverse') {
                $angle += 180.0;
            }
        } else {
            $omatch = [];
            if (\preg_match('/^([+-]?\d+(?:\.\d+)?)(deg)?$/i', $orient, $omatch) === 1) {
                $angle = (float) $omatch[1];
            }
        }

        $rad = \deg2rad(-$angle);
        $cos = \cos($rad);
        $sin = \sin($rad);

        $transformMatrix = $this->graph->getCtmProduct([1.0, 0.0, 0.0, 1.0, $anchorX, $anchorY], [
            $cos,
            $sin,
            -$sin,
            $cos,
            0.0,
            0.0,
        ]);
        $transformMatrix = $this->graph->getCtmProduct($transformMatrix, [
            1.0,
            0.0,
            0.0,
            1.0,
            $viewOffsetX,
            $viewOffsetY,
        ]);
        $transformMatrix = $this->graph->getCtmProduct($transformMatrix, [
            $markerScale * $viewScaleX,
            0.0,
            0.0,
            $markerScale * $viewScaleY,
            0.0,
            0.0,
        ]);
        $transformMatrix = $this->graph->getCtmProduct($transformMatrix, [1.0, 0.0, 0.0, 1.0, -$vbx, -$vby]);
        $transformMatrix = $this->graph->getCtmProduct($transformMatrix, [1.0, 0.0, 0.0, 1.0, -$refX, -$refY]);

        $out = $this->graph->getStartTransform();
        $out .= $this->getOutSVGTransformation($transformMatrix, $soid);

        // Prevent marker content from recursively emitting nested markers.

        $this->svgobjs[$soid]['markermode'] = (int) ($this->svgobjs[$soid]['markermode'] ?? 0) + 1;

        try {
            if (isset($markerdef['child']) && $markerdef['child'] !== []) {
                foreach ($markerdef['child'] as $child) {
                    if (!isset($child['name'])) {
                        continue;
                    }
                    if (isset($child['attr']['closing_tag'])) {
                        $this->handleSVGTagEnd($parser, $child['name']);
                    } else {
                        /** @var TSVGAttributes $childAttr */
                        $childAttr = $child['attr'];
                        $this->handleSVGTagStart($parser, $child['name'], $childAttr, $soid);
                    }
                }
            }
        } finally {
            $this->svgobjs[$soid]['markermode'] = \max(0, (int) $this->svgobjs[$soid]['markermode'] - 1);
        }

        $out .= $this->graph->getStopTransform();
        return $out;
    }

    /**
     * Resolve marker refX/refY values, including percentage coordinates.
     *
     * @param string $raw Ref attribute value.
     * @param float $viewBoxMin ViewBox minimum coordinate (x or y).
     * @param float $viewBoxSize ViewBox size (width or height).
     * @param int $soid SVG object ID.
     *
     * @return float
     */
    protected function resolveSVGMarkerRefCoordinate(
        string $raw,
        float $viewBoxMin,
        float $viewBoxSize,
        int $soid,
    ): float {
        $raw = \trim($raw);
        if ($raw === '') {
            return 0.0;
        }

        if (\str_ends_with($raw, '%')) {
            $pct = (float) \substr($raw, 0, -1);
            return $viewBoxMin + (($pct / 100.0) * $viewBoxSize);
        }

        return $this->svgUnitToUnit($raw, $soid);
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
        bool $isPolyline = false,
    ): string {
        if ($this->svgobjs[$soid]['textmode']['invisible'] ?? false) {
            return '';
        }
        $attrpoints = isset($attr['points']) && $attr['points'] !== '' ? \trim($attr['points']) : '0 0';
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
            if (($key % 2) === 0) {
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
        $width = $xmax - $xmin;
        $height = $ymax - $ymin;
        $out = '';
        if ($this->svgobjs[$soid]['clipmode']) {
            $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
            $out .= $this->graph->getPolygon($pset, 'CNZ');
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
            [$pset, 'CNZ'],
        );
        if ($obstyle !== '') {
            $out .= $this->graph->getPolygon($pset, $obstyle);
        }

        $segments = $this->getSVGPolylineSegments($pset, !$isPolyline);
        $out .= $this->renderSVGMarkersForSegments($parser, $soid, $svgstyle, $segments);

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
        if ($this->svgobjs[$soid]['textmode']['invisible'] ?? false) {
            return '';
        }
        if ($this->svgobjs[$soid]['clipmode']) {
            return '';
        }
        // SVG 2 uses plain 'href'; fall back from xlink:href for compatibility.
        $img = $attr['xlink:href'] ?? $attr['href'] ?? '';
        if ($img === '') {
            return '';
        }
        $posx = isset($attr['x']) ? $this->svgUnitToUnit($attr['x'], $soid) : 0.0;
        $posy = isset($attr['y']) ? $this->svgUnitToUnit($attr['y'], $soid) : 0.0;
        $width = isset($attr['width']) ? $this->svgUnitToUnit($attr['width'], $soid) : 0.0;
        $height = isset($attr['height']) ? $this->svgUnitToUnit($attr['height'], $soid) : 0.0;
        $out = '';
        $out .= $this->graph->getStartTransform();
        $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
        $out .= $this->parseSVGStyle($parser, $soid, $svgstyle, $prev_svgstyle, $posx, $posy, $width, $height);
        if (
            'svg' === \strtolower(\trim(\pathinfo(
                ($purl = \parse_url($img, PHP_URL_PATH)) ? $purl : '',
                PATHINFO_EXTENSION,
            )))
        ) {
            try {
                $child = $this->addSVG($img, $posx, $posy, $width, $height);
            } catch (Exception $e) {
                return '';
            }

            $this->svgobjs[$soid]['child'][] = $child;
            return $out;
        }
        $match = [];
        if (\preg_match('/^data:image\/[^;]+;base64,/', $img, $match) > 0) {
            // embedded image encoded as base64
            $raw = \base64_decode(\substr($img, \strlen($match[0])), true);
            if ($raw === false) {
                return $out;
            }
            $img = '@' . $raw;
        }

        if (
            isset($this->svgobjs[$soid]['dir'])
            && $this->svgobjs[$soid]['dir'] !== ''
            && ($img[0] === '.' || \basename($img) === $img)
        ) {
            // replace relative path with full server path
            $img = $this->svgobjs[$soid]['dir'] . '/' . $img;
        }

        $imgid = $this->image->add($img);
        // R-2: honour preserveAspectRatio for raster images.
        $renderX = $posx;
        $renderY = $posy;
        $renderW = $width;
        $renderH = $height;
        $par = $attr['preserveAspectRatio'] ?? 'xMidYMid meet';
        if ($par !== 'none' && $width > 0.0 && $height > 0.0) {
            try {
                $imgdata = $this->image->getImageDataByKey($this->image->getKey($img));
                $intrW = (float) $imgdata['width'];
                $intrH = (float) $imgdata['height'];
                if ($intrW > 0.0 && $intrH > 0.0) {
                    $parTokens = [];
                    \preg_match_all('/[a-zA-Z]+/', $par, $parTokens);
                    $parTokens = $parTokens[0];
                    $fit = \count($parTokens) >= 2 ? $parTokens[\count($parTokens) - 1] : 'meet';
                    $scaleW = $width / $intrW;
                    $scaleH = $height / $intrH;
                    $scale = $fit === 'slice' ? \max($scaleW, $scaleH) : \min($scaleW, $scaleH);
                    $scaledW = $intrW * $scale;
                    $scaledH = $intrH * $scale;
                    $alignStr = \count($parTokens) >= 2 ? $parTokens[0] : 'xMidYMid';
                    $offX = match (\substr($alignStr, 0, 4)) {
                        'xMax' => $width - $scaledW,
                        'xMid' => ($width - $scaledW) / 2.0,
                        default => 0.0,
                    };
                    $offY = match (\substr($alignStr, 4, 4)) {
                        'YMax' => $height - $scaledH,
                        'YMid' => ($height - $scaledH) / 2.0,
                        default => 0.0,
                    };
                    $renderX = $posx + $offX;
                    $renderY = $posy + $offY;
                    $renderW = $scaledW;
                    $renderH = $scaledH;
                }
            } catch (\Throwable $e) {
                // Image metadata unavailable; use original dimensions unchanged.
                unset($e);
            }
        }
        $out .= $this->image->getSetImage(
            $imgid,
            $renderX,
            $renderY,
            $renderW,
            $renderH,
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
        $out = '';
        if (isset($this->svgobjs[$soid]['text']) && $this->svgobjs[$soid]['text'] !== '') {
            // Flush the text accumulated between an outer <text> start and this
            // nested <text>/<tspan> start.  We only emit the text-line operator and
            // clear the buffer.  We deliberately do NOT close the outer transform or
            // pop the styles stack — those bookkeeping operations are the
            // responsibility of the matching </text> end handler; duplicating them
            // here would corrupt the PDF graphics-state stack.
            $anchor = $this->svgobjs[$soid]['textmode']['text-anchor'];
            $txtanchor = match ($anchor) {
                'end' => 'E',
                'middle' => 'M',
                default => 'S',
            };
            if (!$this->svgobjs[$soid]['textmode']['invisible']) {
                $out .= $this->getTextLine(
                    $this->svgobjs[$soid]['text'],
                    $this->svgobjs[$soid]['x'],
                    $this->svgobjs[$soid]['y'],
                    0,
                    $this->svgobjs[$soid]['textmode']['stroke'],
                    0,
                    0,
                    0,
                    true,
                    $this->svgobjs[$soid]['textmode']['stroke'] > 0,
                    false,
                    false,
                    false,
                    false,
                    $this->svgobjs[$soid]['textmode']['rtl'] ? 'R' : '',
                    $txtanchor,
                    null,
                );
            } else {
                // Invisible text still advances the cursor by the text width.
                if ($this->svgobjs[$soid]['textmode']['vertical'] ?? false) {
                    $this->svgobjs[$soid]['y'] += $this->getStringWidth($this->svgobjs[$soid]['text']);
                } else {
                    $this->svgobjs[$soid]['x'] += $this->getStringWidth($this->svgobjs[$soid]['text']);
                }
            }

            $this->svgobjs[$soid]['text'] = '';
        }

        if ($this->svgobjs[$soid]['textmode']['invisible']) {
            return $out;
        }

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
        $this->svgobjs[$soid]['textmode']['text-anchor'] = $svgstyle['text-anchor'] ?? 'start';
        $this->svgobjs[$soid]['textmode']['rtl'] = ($svgstyle['direction'] ?? 'ltr') === 'rtl';
        $wmode = $svgstyle['writing-mode'];
        $this->svgobjs[$soid]['textmode']['vertical'] =
            \str_starts_with($wmode, 'tb') || \str_starts_with($wmode, 'vertical');
        $this->svgobjs[$soid]['textmode']['rotate'] = $this->getSVGGlyphOrientationRotation(
            $svgstyle,
            $this->svgobjs[$soid]['textmode']['vertical'],
        );
        if ($svgstyle['stroke'] !== 'none' && $svgstyle['stroke-width'] > 0) {
            $this->svgobjs[$soid]['textmode']['stroke'] = $this->svgUnitToUnit($svgstyle['stroke-width'], $soid);
        } else {
            $this->svgobjs[$soid]['textmode']['stroke'] = 0.0;
        }

        // S-1: dominant-baseline / alignment-baseline Y offset.
        $this->svgobjs[$soid]['textmode']['baseline'] = $svgstyle['dominant-baseline'];

        // S-3: textLength and lengthAdjust.
        $this->svgobjs[$soid]['textmode']['textlength'] = isset($attr['textLength'])
            ? $this->svgUnitToUnit($attr['textLength'], $soid)
            : 0.0;
        $this->svgobjs[$soid]['textmode']['lengthadjust'] = $attr['lengthAdjust'] ?? 'spacing';

        // S-4: parse rotate list; first angle remains run fallback.
        $this->svgobjs[$soid]['textmode']['rotlist'] = [];
        if (isset($attr['rotate']) && $attr['rotate'] !== '') {
            $rotvals = \preg_split('/[\s,]+/', \trim($attr['rotate']), -1, \PREG_SPLIT_NO_EMPTY);
            if ($rotvals !== []) {
                $this->svgobjs[$soid]['textmode']['rotate'] = (float) $rotvals[0];
                foreach ($rotvals as $rotval) {
                    $this->svgobjs[$soid]['textmode']['rotlist'][] = (float) $rotval;
                }
            }
        }

        // R-1: multi-value x / y coordinate lists.
        $this->svgobjs[$soid]['textmode']['xlist'] = [];
        $this->svgobjs[$soid]['textmode']['ylist'] = [];
        $this->svgobjs[$soid]['textmode']['textpathpoints'] = [];
        $this->svgobjs[$soid]['textmode']['textpathoffset'] = 0.0;
        $this->svgobjs[$soid]['textmode']['textpathmethod'] = 'align';
        $this->svgobjs[$soid]['textmode']['textpathspacing'] = 'exact';
        if (isset($attr['x']) && \str_contains($attr['x'], ' ')) {
            foreach (\preg_split('/[\s,]+/', \trim($attr['x']), -1, \PREG_SPLIT_NO_EMPTY) as $xv) {
                $this->svgobjs[$soid]['textmode']['xlist'][] = $this->svgUnitToUnit($xv, $soid);
            }
        }
        if (isset($attr['y']) && \str_contains($attr['y'], ' ')) {
            foreach (\preg_split('/[\s,]+/', \trim($attr['y']), -1, \PREG_SPLIT_NO_EMPTY) as $yv) {
                $this->svgobjs[$soid]['textmode']['ylist'][] = $this->svgUnitToUnit($yv, $soid);
            }
        }
        $out .= $this->graph->getStartTransform();
        $out .= $this->getOutSVGTransformation($svgstyle['transfmatrix'], $soid);
        $out .= $this->parseSVGStyle($parser, $soid, $svgstyle, $prev_svgstyle, $posx, $posy, 1, 1);
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
        return $this->parseSVGTagSTARTtext($parser, $soid, $attr, $svgstyle, $prev_svgstyle, true);
    }

    /**
     * Resolve textPath reference to a sequence of points.
     *
     * @param int $soid ID of the current SVG object.
     * @param string $href Reference URI (typically '#id').
     *
     * @return array<int, array{0: float, 1: float}>|null
     */
    protected function getTextPathPoints(int $soid, string $href): ?array
    {
        $pathDef = $this->resolveTextPathDef($soid, $href);
        if ($pathDef === null) {
            return null;
        }

        return $this->getTextPathPointsFromDef($soid, $pathDef['name'], $pathDef['attr']);
    }

    /**
     * Resolve textPath href to a defs entry.
     *
     * @param int $soid ID of the current SVG object.
     * @param string $href Reference URI (typically '#id').
     *
     * @return array{name: string, attr: TSVGAttributes}|null
     */
    protected function resolveTextPathDef(int $soid, string $href): ?array
    {
        if ($href === '' || $href[0] !== '#') {
            return null;
        }

        $pathId = \substr($href, 1);
        if ($pathId === '' || !isset($this->svgobjs[$soid]['defs'][$pathId])) {
            return null;
        }

        /** @var TSVGAttribs $def */
        $def = $this->svgobjs[$soid]['defs'][$pathId];
        if ($def['name'] === '' || $def['attr'] === []) {
            return null;
        }

        /** @var TSVGAttributes $defAttr */
        $defAttr = $def['attr'];

        return [
            'name' => $def['name'],
            'attr' => $defAttr,
        ];
    }

    /**
     * Resolve shape-specific points for textPath layout.
     *
     * @param int $soid ID of the current SVG object.
     * @param string $defName Defs element name.
     * @param TSVGAttributes $defAttr Defs element attributes.
     *
     * @return array<int, array{0: float, 1: float}>|null
     */
    protected function getTextPathPointsFromDef(int $soid, string $defName, array $defAttr): ?array
    {
        if ($defName === 'line') {
            $startX = $this->svgUnitToUnit($defAttr['x1'] ?? '0', $soid);
            $startY = $this->svgUnitToUnit($defAttr['y1'] ?? '0', $soid);
            $endX = $this->svgUnitToUnit($defAttr['x2'] ?? '0', $soid);
            $endY = $this->svgUnitToUnit($defAttr['y2'] ?? '0', $soid);
            return [[$startX, $startY], [$endX, $endY]];
        }

        if ($defName === 'polyline' || $defName === 'polygon') {
            $attrPoints = $defAttr['points'] ?? '';
            $points = \preg_split('/[\,\s]+/si', \trim($attrPoints), -1, \PREG_SPLIT_NO_EMPTY);
            if (!\is_array($points) || \count($points) < 4) {
                return null;
            }
            $ptlist = [];
            for ($idx = 0; ($idx + 1) < \count($points); $idx += 2) {
                $ptlist[] = [
                    $this->svgUnitToUnit($points[$idx], $soid),
                    $this->svgUnitToUnit($points[$idx + 1], $soid),
                ];
            }
            if ($defName === 'polygon') {
                $firstPoint = $ptlist[0] ?? [0.0, 0.0];
                $ptlist[] = [$firstPoint[0], $firstPoint[1]];
            }
            return \count($ptlist) >= 2 ? $ptlist : null;
        }

        if ($defName === 'path') {
            return $this->getTextPathPointsFromPathData($soid, $defAttr['d'] ?? '');
        }

        return null;
    }

    /**
     * Parse path data into sampled points for textPath layout.
     *
     * @param int $soid ID of the current SVG object.
     * @param string $pathData Path d attribute.
     *
     * @return array<int, array{0: float, 1: float}>|null
     */
    protected function getTextPathPointsFromPathData(int $soid, string $pathData): ?array
    {
        $tokenMatch = [];
        \preg_match_all('/[MmLlHhVvCcSsQqTtAaZz]|-?(?:\d+\.?\d*|\.\d+)(?:[eE][-+]?\d+)?/', $pathData, $tokenMatch);
        $tokens = $tokenMatch[0];
        if ($tokens === []) {
            return null;
        }

        $ptlist = [];
        $curX = 0.0;
        $curY = 0.0;
        $subX = 0.0;
        $subY = 0.0;
        $command = '';
        $lastCurveCtrlX = 0.0;
        $lastCurveCtrlY = 0.0;
        $lastQuadCtrlX = 0.0;
        $lastQuadCtrlY = 0.0;
        $hasCurveCtrl = false;
        $hasQuadCtrl = false;
        $tokCount = \count($tokens);
        $idx = 0;

        while ($idx < $tokCount) {
            $token = $tokens[$idx];
            if (\preg_match('/^[A-Za-z]$/', $token) === 1) {
                $command = $token;
                ++$idx;
                if ($command === 'Z' || $command === 'z') {
                    if ($ptlist !== []) {
                        $ptlist[] = [$subX, $subY];
                        $curX = $subX;
                        $curY = $subY;
                        $hasCurveCtrl = false;
                        $hasQuadCtrl = false;
                    }
                }
                continue;
            }

            if ($command === '') {
                ++$idx;
                continue;
            }

            $isRel = \ctype_lower($command);
            $cmd = \strtolower($command);

            if ($cmd === 'm' || $cmd === 'l' || $cmd === 't') {
                if (($idx + 1) >= $tokCount) {
                    break;
                }
                $t0 = $tokens[$idx] ?? '';
                $t1 = $tokens[$idx + 1] ?? '';
                $endX = $this->svgUnitToUnit($t0, $soid);
                $endY = $this->svgUnitToUnit($t1, $soid);
                if ($isRel) {
                    $endX += $curX;
                    $endY += $curY;
                }
                if ($cmd === 't') {
                    $ctrlX = $curX;
                    $ctrlY = $curY;
                    if ($hasQuadCtrl) {
                        $ctrlX = (2.0 * $curX) - $lastQuadCtrlX;
                        $ctrlY = (2.0 * $curY) - $lastQuadCtrlY;
                    }
                    $samples = $this->sampleTextPathQuadratic($curX, $curY, $ctrlX, $ctrlY, $endX, $endY, 12);
                    foreach ($samples as $point) {
                        $ptlist[] = $point;
                    }
                    $lastQuadCtrlX = $ctrlX;
                    $lastQuadCtrlY = $ctrlY;
                    $hasQuadCtrl = true;
                    $hasCurveCtrl = false;
                } else {
                    $ptlist[] = [$endX, $endY];
                    $hasCurveCtrl = false;
                    $hasQuadCtrl = false;
                }
                $curX = $endX;
                $curY = $endY;
                if ($cmd === 'm') {
                    $subX = $curX;
                    $subY = $curY;
                    $command = $isRel ? 'l' : 'L';
                }
                $idx += 2;
                continue;
            }

            if ($cmd === 'h') {
                $t0 = $tokens[$idx] ?? '';
                $valX = $this->svgUnitToUnit($t0, $soid);
                $curX = $isRel ? $curX + $valX : $valX;
                $ptlist[] = [$curX, $curY];
                $hasCurveCtrl = false;
                $hasQuadCtrl = false;
                ++$idx;
                continue;
            }

            if ($cmd === 'v') {
                $t0 = $tokens[$idx] ?? '';
                $valY = $this->svgUnitToUnit($t0, $soid);
                $curY = $isRel ? $curY + $valY : $valY;
                $ptlist[] = [$curX, $curY];
                $hasCurveCtrl = false;
                $hasQuadCtrl = false;
                ++$idx;
                continue;
            }

            if ($cmd === 'c') {
                if (($idx + 5) >= $tokCount) {
                    break;
                }
                $t0 = $tokens[$idx] ?? '';
                $t1 = $tokens[$idx + 1] ?? '';
                $t2 = $tokens[$idx + 2] ?? '';
                $t3 = $tokens[$idx + 3] ?? '';
                $t4 = $tokens[$idx + 4] ?? '';
                $t5 = $tokens[$idx + 5] ?? '';
                $ctrl1X = $this->svgUnitToUnit($t0, $soid);
                $ctrl1Y = $this->svgUnitToUnit($t1, $soid);
                $ctrl2X = $this->svgUnitToUnit($t2, $soid);
                $ctrl2Y = $this->svgUnitToUnit($t3, $soid);
                $endX = $this->svgUnitToUnit($t4, $soid);
                $endY = $this->svgUnitToUnit($t5, $soid);
                if ($isRel) {
                    $ctrl1X += $curX;
                    $ctrl1Y += $curY;
                    $ctrl2X += $curX;
                    $ctrl2Y += $curY;
                    $endX += $curX;
                    $endY += $curY;
                }
                $samples = $this->sampleTextPathCubic(
                    $curX,
                    $curY,
                    $ctrl1X,
                    $ctrl1Y,
                    $ctrl2X,
                    $ctrl2Y,
                    $endX,
                    $endY,
                    12,
                );
                foreach ($samples as $point) {
                    $ptlist[] = $point;
                }
                $curX = $endX;
                $curY = $endY;
                $lastCurveCtrlX = $ctrl2X;
                $lastCurveCtrlY = $ctrl2Y;
                $hasCurveCtrl = true;
                $hasQuadCtrl = false;
                $idx += 6;
                continue;
            }

            if ($cmd === 's') {
                if (($idx + 3) >= $tokCount) {
                    break;
                }
                $ctrl1X = $curX;
                $ctrl1Y = $curY;
                if ($hasCurveCtrl) {
                    $ctrl1X = (2.0 * $curX) - $lastCurveCtrlX;
                    $ctrl1Y = (2.0 * $curY) - $lastCurveCtrlY;
                }
                $t0 = $tokens[$idx] ?? '';
                $t1 = $tokens[$idx + 1] ?? '';
                $t2 = $tokens[$idx + 2] ?? '';
                $t3 = $tokens[$idx + 3] ?? '';
                $ctrl2X = $this->svgUnitToUnit($t0, $soid);
                $ctrl2Y = $this->svgUnitToUnit($t1, $soid);
                $endX = $this->svgUnitToUnit($t2, $soid);
                $endY = $this->svgUnitToUnit($t3, $soid);
                if ($isRel) {
                    $ctrl2X += $curX;
                    $ctrl2Y += $curY;
                    $endX += $curX;
                    $endY += $curY;
                }
                $samples = $this->sampleTextPathCubic(
                    $curX,
                    $curY,
                    $ctrl1X,
                    $ctrl1Y,
                    $ctrl2X,
                    $ctrl2Y,
                    $endX,
                    $endY,
                    12,
                );
                foreach ($samples as $point) {
                    $ptlist[] = $point;
                }
                $curX = $endX;
                $curY = $endY;
                $lastCurveCtrlX = $ctrl2X;
                $lastCurveCtrlY = $ctrl2Y;
                $hasCurveCtrl = true;
                $hasQuadCtrl = false;
                $idx += 4;
                continue;
            }

            if ($cmd === 'q') {
                if (($idx + 3) >= $tokCount) {
                    break;
                }
                $t0 = $tokens[$idx] ?? '';
                $t1 = $tokens[$idx + 1] ?? '';
                $t2 = $tokens[$idx + 2] ?? '';
                $t3 = $tokens[$idx + 3] ?? '';
                $ctrlX = $this->svgUnitToUnit($t0, $soid);
                $ctrlY = $this->svgUnitToUnit($t1, $soid);
                $endX = $this->svgUnitToUnit($t2, $soid);
                $endY = $this->svgUnitToUnit($t3, $soid);
                if ($isRel) {
                    $ctrlX += $curX;
                    $ctrlY += $curY;
                    $endX += $curX;
                    $endY += $curY;
                }
                $samples = $this->sampleTextPathQuadratic($curX, $curY, $ctrlX, $ctrlY, $endX, $endY, 12);
                foreach ($samples as $point) {
                    $ptlist[] = $point;
                }
                $curX = $endX;
                $curY = $endY;
                $lastQuadCtrlX = $ctrlX;
                $lastQuadCtrlY = $ctrlY;
                $hasQuadCtrl = true;
                $hasCurveCtrl = false;
                $idx += 4;
                continue;
            }

            if ($cmd === 'a') {
                if (($idx + 6) >= $tokCount) {
                    break;
                }
                $t0 = $tokens[$idx] ?? '';
                $t1 = $tokens[$idx + 1] ?? '';
                $t2 = $tokens[$idx + 2] ?? '';
                $t3 = $tokens[$idx + 3] ?? '';
                $t4 = $tokens[$idx + 4] ?? '';
                $t5 = $tokens[$idx + 5] ?? '';
                $t6 = $tokens[$idx + 6] ?? '';
                $radiusX = $this->svgUnitToUnit($t0, $soid);
                $radiusY = $this->svgUnitToUnit($t1, $soid);
                $xAxisRot = (float) $t2;
                $largeArcFlag = (float) $t3 >= 0.5;
                $sweepFlag = (float) $t4 >= 0.5;
                $endX = $this->svgUnitToUnit($t5, $soid);
                $endY = $this->svgUnitToUnit($t6, $soid);
                if ($isRel) {
                    $endX += $curX;
                    $endY += $curY;
                }
                $samples = $this->sampleTextPathArc(
                    $curX,
                    $curY,
                    $radiusX,
                    $radiusY,
                    $xAxisRot,
                    $largeArcFlag,
                    $sweepFlag,
                    $endX,
                    $endY,
                );
                foreach ($samples as $point) {
                    $ptlist[] = $point;
                }
                $curX = $endX;
                $curY = $endY;
                if ($samples === []) {
                    $ptlist[] = [$curX, $curY];
                }
                $hasCurveCtrl = false;
                $hasQuadCtrl = false;
                $idx += 7;
                continue;
            }

            $hasCurveCtrl = false;
            $hasQuadCtrl = false;
            ++$idx;
        }

        return \count($ptlist) >= 2 ? $ptlist : null;
    }

    /**
     * Sample a quadratic bezier into a sequence of points.
     *
     * @return array<int, array{0: float, 1: float}>
     */
    protected function sampleTextPathQuadratic(
        float $startX,
        float $startY,
        float $ctrlX,
        float $ctrlY,
        float $endX,
        float $endY,
        int $steps,
    ): array {
        $points = [];
        $stepCount = \max(2, $steps);
        for ($step = 1; $step <= $stepCount; ++$step) {
            $param = (float) $step / (float) $stepCount;
            $inv = 1.0 - $param;
            $pointX = ($inv * $inv * $startX) + (2.0 * $inv * $param * $ctrlX) + ($param * $param * $endX);
            $pointY = ($inv * $inv * $startY) + (2.0 * $inv * $param * $ctrlY) + ($param * $param * $endY);
            $points[] = [$pointX, $pointY];
        }
        return $points;
    }

    /**
     * Sample a cubic bezier into a sequence of points.
     *
     * @return array<int, array{0: float, 1: float}>
     */
    protected function sampleTextPathCubic(
        float $startX,
        float $startY,
        float $ctrl1X,
        float $ctrl1Y,
        float $ctrl2X,
        float $ctrl2Y,
        float $endX,
        float $endY,
        int $steps,
    ): array {
        $points = [];
        $stepCount = \max(2, $steps);
        for ($step = 1; $step <= $stepCount; ++$step) {
            $param = (float) $step / (float) $stepCount;
            $inv = 1.0 - $param;
            $inv2 = $inv * $inv;
            $inv3 = $inv2 * $inv;
            $par2 = $param * $param;
            $par3 = $par2 * $param;
            $pointX =
                ($inv3 * $startX) + (3.0 * $inv2 * $param * $ctrl1X) + (3.0 * $inv * $par2 * $ctrl2X) + ($par3 * $endX);
            $pointY =
                ($inv3 * $startY) + (3.0 * $inv2 * $param * $ctrl1Y) + (3.0 * $inv * $par2 * $ctrl2Y) + ($par3 * $endY);
            $points[] = [$pointX, $pointY];
        }
        return $points;
    }

    /**
     * Sample an elliptical arc segment into a sequence of points.
     *
     * @return array<int, array{0: float, 1: float}>
     */
    protected function sampleTextPathArc(
        float $startX,
        float $startY,
        float $radiusX,
        float $radiusY,
        float $xAxisRotation,
        bool $largeArcFlag,
        bool $sweepFlag,
        float $endX,
        float $endY,
    ): array {
        if (\abs($startX - $endX) < self::SVGMINFLOATDIFF && \abs($startY - $endY) < self::SVGMINFLOATDIFF) {
            return [];
        }

        $radiusX = \abs($radiusX);
        $radiusY = \abs($radiusY);
        if ($radiusX <= self::SVGMINFLOATDIFF || $radiusY <= self::SVGMINFLOATDIFF) {
            return [[$endX, $endY]];
        }

        $phi = \deg2rad(\fmod($xAxisRotation, 360.0));
        $cosPhi = \cos($phi);
        $sinPhi = \sin($phi);

        $deltaX = ($startX - $endX) / 2.0;
        $deltaY = ($startY - $endY) / 2.0;
        $xPrime = ($cosPhi * $deltaX) + ($sinPhi * $deltaY);
        $yPrime = (-$sinPhi * $deltaX) + ($cosPhi * $deltaY);

        $rx2 = $radiusX * $radiusX;
        $ry2 = $radiusY * $radiusY;
        $xp2 = $xPrime * $xPrime;
        $yp2 = $yPrime * $yPrime;

        $lambda = ($xp2 / $rx2) + ($yp2 / $ry2);
        if ($lambda > 1.0) {
            $scale = \sqrt($lambda);
            $radiusX *= $scale;
            $radiusY *= $scale;
            $rx2 = $radiusX * $radiusX;
            $ry2 = $radiusY * $radiusY;
        }

        $sign = $largeArcFlag === $sweepFlag ? -1.0 : 1.0;
        $numerator = ($rx2 * $ry2) - ($rx2 * $yp2) - ($ry2 * $xp2);
        $denominator = ($rx2 * $yp2) + ($ry2 * $xp2);
        $coef = 0.0;
        if ($denominator > self::SVGMINFLOATDIFF) {
            $coef = $sign * \sqrt(\max(0.0, $numerator / $denominator));
        }

        $centerPrimeX = $coef * (($radiusX * $yPrime) / $radiusY);
        $centerPrimeY = $coef * -(($radiusY * $xPrime) / $radiusX);

        $centerX = ($cosPhi * $centerPrimeX) - ($sinPhi * $centerPrimeY) + (($startX + $endX) / 2.0);
        $centerY = ($sinPhi * $centerPrimeX) + ($cosPhi * $centerPrimeY) + (($startY + $endY) / 2.0);

        $unitStartX = ($xPrime - $centerPrimeX) / $radiusX;
        $unitStartY = ($yPrime - $centerPrimeY) / $radiusY;
        $unitEndX = (-$xPrime - $centerPrimeX) / $radiusX;
        $unitEndY = (-$yPrime - $centerPrimeY) / $radiusY;

        $thetaStart = $this->getArcVectorAngle(1.0, 0.0, $unitStartX, $unitStartY);
        $thetaDelta = $this->getArcVectorAngle($unitStartX, $unitStartY, $unitEndX, $unitEndY);

        if (!$sweepFlag && $thetaDelta > 0.0) {
            $thetaDelta -= 2.0 * \M_PI;
        } elseif ($sweepFlag && $thetaDelta < 0.0) {
            $thetaDelta += 2.0 * \M_PI;
        }

        $segmentCount = \max(4, (int) \ceil(\abs($thetaDelta) / (\M_PI / 12.0)));
        $points = [];
        for ($seg = 1; $seg <= $segmentCount; ++$seg) {
            $ratio = (float) $seg / (float) $segmentCount;
            $theta = $thetaStart + ($thetaDelta * $ratio);
            $cosTheta = \cos($theta);
            $sinTheta = \sin($theta);
            $pointX = $centerX + ($cosPhi * $radiusX * $cosTheta) - ($sinPhi * $radiusY * $sinTheta);
            $pointY = $centerY + ($sinPhi * $radiusX * $cosTheta) + ($cosPhi * $radiusY * $sinTheta);
            $points[] = [$pointX, $pointY];
        }

        return $points;
    }

    /**
     * Return signed angle between two vectors.
     */
    protected function getArcVectorAngle(
        float $vectorStartX,
        float $vectorStartY,
        float $vectorEndX,
        float $vectorEndY,
    ): float {
        $dot = ($vectorStartX * $vectorEndX) + ($vectorStartY * $vectorEndY);
        $det = ($vectorStartX * $vectorEndY) - ($vectorStartY * $vectorEndX);
        return \atan2($det, $dot);
    }

    /**
     * Get total polyline length from point list.
     *
     * @param array<int, array{0: float, 1: float}> $points
     */
    protected function getTextPathLength(array $points): float
    {
        $length = 0.0;
        for ($idx = 1; $idx < \count($points); ++$idx) {
            $pt0 = $points[$idx - 1] ?? [0.0, 0.0];
            $pt1 = $points[$idx] ?? [0.0, 0.0];
            $deltaX = ($pt1[0] ?? 0.0) - ($pt0[0] ?? 0.0);
            $deltaY = ($pt1[1] ?? 0.0) - ($pt0[1] ?? 0.0);
            $length += \sqrt(($deltaX * $deltaX) + ($deltaY * $deltaY));
        }
        return $length;
    }

    /**
     * Interpolate the point and angle at a given offset along the polyline.
     *
     * @param array<int, array{0: float, 1: float}> $points
     * @return array{0: float, 1: float, 2: float}|null (x, y, angleDeg)
     */
    protected function getTextPathPointAtOffset(array $points, float $offset): ?array
    {
        if (\count($points) < 2) {
            return null;
        }

        $total = $this->getTextPathLength($points);
        if ($total <= 0.0) {
            return null;
        }

        $remaining = \max(0.0, \min($offset, $total));
        for ($idx = 1; $idx < \count($points); ++$idx) {
            $pt0 = $points[$idx - 1] ?? [0.0, 0.0];
            $pt1 = $points[$idx] ?? [0.0, 0.0];
            $startX = $pt0[0] ?? 0.0;
            $startY = $pt0[1] ?? 0.0;
            $endX = $pt1[0] ?? 0.0;
            $endY = $pt1[1] ?? 0.0;
            $deltaX = $endX - $startX;
            $deltaY = $endY - $startY;
            $segLength = \sqrt(($deltaX * $deltaX) + ($deltaY * $deltaY));
            if ($segLength <= 0.0) {
                continue;
            }
            if ($remaining <= $segLength) {
                $ratio = $remaining / $segLength;
                $pointX = $startX + ($deltaX * $ratio);
                $pointY = $startY + ($deltaY * $ratio);
                $angle = \rad2deg(\atan2($deltaY, $deltaX));
                return [$pointX, $pointY, $angle];
            }
            $remaining -= $segLength;
        }

        $last = $points[\count($points) - 1] ?? [0.0, 0.0];
        $prev = $points[\count($points) - 2] ?? [0.0, 0.0];
        $angle = \rad2deg(\atan2(($last[1] ?? 0.0) - ($prev[1] ?? 0.0), ($last[0] ?? 0.0) - ($prev[0] ?? 0.0)));
        return [$last[0] ?? 0.0, $last[1] ?? 0.0, $angle];
    }

    /**
     * Parse the SVG Start tag 'textPath'.
     *
     * @param \XMLParser $parser The XML parser.
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     * @param TSVGStyle $svgstyle Current SVG style.
     * @param TSVGStyle $prev_svgstyle Previous SVG style.
     *
     * @return string
     */
    protected function parseSVGTagSTARTtextPath(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        $textPathAttr = $attr;
        $href = $attr['xlink:href'] ?? $attr['href'] ?? '';
        $startOffsetRaw = $attr['startOffset'] ?? '0';

        $points = $this->getTextPathPoints($soid, $href);
        if ($points !== null) {
            $pathLength = $this->getTextPathLength($points);
            $startOffset = 0.0;

            if (\str_contains($startOffsetRaw, '%')) {
                $startOffset = ($pathLength * \floatval(\str_replace('%', '', $startOffsetRaw))) / 100.0;
            } elseif ($startOffsetRaw !== '') {
                $startOffset = $this->svgUnitToUnit($startOffsetRaw, $soid);
            }

            if ($pathLength > 0.0) {
                $pathPoint = $this->getTextPathPointAtOffset($points, $startOffset);
                if ($pathPoint !== null) {
                    $textPathAttr['x'] = (string) $pathPoint[0];
                    $textPathAttr['y'] = (string) $pathPoint[1];
                    if (!isset($textPathAttr['rotate']) || $textPathAttr['rotate'] === '') {
                        $textPathAttr['rotate'] = (string) $pathPoint[2];
                    }
                }
            }
        }

        if (isset($textPathAttr['xlink:href'])) {
            unset($textPathAttr['xlink:href']);
        }
        if (isset($textPathAttr['href'])) {
            unset($textPathAttr['href']);
        }

        /** @var TSVGAttributes $textPathAttr */

        $out = $this->parseSVGTagSTARTtext($parser, $soid, $textPathAttr, $svgstyle, $prev_svgstyle, true);

        if ($points !== null) {
            $pathLength = $this->getTextPathLength($points);
            $startOffset = 0.0;
            if ($pathLength > 0.0) {
                if (\str_contains($startOffsetRaw, '%')) {
                    $startOffset = ($pathLength * \floatval(\str_replace('%', '', $startOffsetRaw))) / 100.0;
                } elseif ($startOffsetRaw !== '') {
                    $startOffset = $this->svgUnitToUnit($startOffsetRaw, $soid);
                }
            }

            $this->svgobjs[$soid]['textmode']['textpathpoints'] = $points;

            $this->svgobjs[$soid]['textmode']['textpathoffset'] = $startOffset;

            $this->svgobjs[$soid]['textmode']['textpathmethod'] = $attr['method'] ?? 'align';

            $this->svgobjs[$soid]['textmode']['textpathspacing'] = $attr['spacing'] ?? 'exact';
        }

        return $out;
    }

    /**
     * Build per-glyph layout arrays for an active textPath run.
     *
     * `method="stretch"` scales glyph advances to fill available path length.
     * `spacing="auto"` adjusts inter-glyph gaps to consume the available path.
     *
     * @param int $soid ID of the current SVG object.
     */
    protected function applyTextPathGlyphLayout(int $soid): void
    {
        $text = $this->svgobjs[$soid]['text'] ?? '';
        if ($text === '') {
            return;
        }

        $textPathPoints = $this->svgobjs[$soid]['textmode']['textpathpoints'] ?? [];
        if (\count($textPathPoints) < 2) {
            return;
        }

        $textPathLength = $this->getTextPathLength($textPathPoints);
        $startOffset = $this->svgobjs[$soid]['textmode']['textpathoffset'] ?? 0.0;
        $availableLength = \max(0.0, $textPathLength - $startOffset);
        $pathMethod = $this->svgobjs[$soid]['textmode']['textpathmethod'] ?? 'align';
        $pathSpacing = $this->svgobjs[$soid]['textmode']['textpathspacing'] ?? 'exact';
        $chars = \mb_str_split($text, 1, 'UTF-8');
        $charCount = \count($chars);
        if ($charCount === 0) {
            return;
        }

        $advances = [];
        $baseAdvance = 0.0;
        foreach ($chars as $charGlyph) {
            $charAdvance = $this->getStringWidth($charGlyph);
            $advances[] = $charAdvance;
            $baseAdvance += $charAdvance;
        }

        if ($pathMethod === 'stretch' && $baseAdvance > 0.0 && $availableLength > 0.0) {
            $stretchScale = $availableLength / $baseAdvance;
            foreach ($advances as $key => $charAdvance) {
                $advances[$key] = $charAdvance * $stretchScale;
            }
            $baseAdvance = $availableLength;
        }

        $gapAdjust = 0.0;
        if ($pathSpacing === 'auto' && $charCount > 1 && $availableLength > 0.0) {
            $remainingGap = $availableLength - $baseAdvance;
            if ($remainingGap > 0.0) {
                $gapAdjust = $remainingGap / (float) ($charCount - 1);
            }
        }

        $xcoords = [];
        $ycoords = [];
        $angles = [];
        $pathOffset = $startOffset;

        foreach ($chars as $charIndex => $charGlyph) {
            $pathPoint = $this->getTextPathPointAtOffset($textPathPoints, $pathOffset);
            if ($pathPoint === []) {
                break;
            }
            $xcoords[] = $pathPoint[0];
            $ycoords[] = $pathPoint[1];
            $angles[] = $pathPoint[2];
            $pathOffset += $advances[$charIndex] ?? $this->getStringWidth($charGlyph);
            if ($charIndex < ($charCount - 1)) {
                $pathOffset += $gapAdjust;
            }
        }

        if ($xcoords !== [] && $ycoords !== []) {
            $this->svgobjs[$soid]['textmode']['xlist'] = $xcoords;

            $this->svgobjs[$soid]['textmode']['ylist'] = $ycoords;

            $this->svgobjs[$soid]['textmode']['rotlist'] = $angles;

            $this->svgobjs[$soid]['x'] = $xcoords[0];

            $this->svgobjs[$soid]['y'] = $ycoords[0];
            if (
                (
                    !isset($this->svgobjs[$soid]['textmode']['rotate'])
                    || $this->svgobjs[$soid]['textmode']['rotate'] === 0.0
                )
                && $angles !== []
            ) {
                $this->svgobjs[$soid]['textmode']['rotate'] = $angles[0];
            }
        }
    }

    /**
     * Parse the SVG Start tag 'symbol'.
     *
     * A <symbol> is a reusable graphic container that is captured into defs.
     * It is never rendered directly; it is expanded only when referenced by <use>.
     * The defsmode flag is set here so that subsequent child elements are stored
     * in the defs array rather than rendered immediately.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     *
     * @return string
     */
    protected function parseSVGTagSTARTsymbol(int $soid, array $attr): string
    {
        return $this->registerSVGDefsContainer($soid, 'symbol', $attr);
    }

    /**
     * Parse the SVG End tag 'symbol'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string
     */
    protected function parseSVGTagENDsymbol(int $soid): string
    {
        return $this->setSVGDefsMode($soid, false);
    }

    /**
     * Parse the SVG Start tag 'marker'.
     *
     * Markers are reusable defs objects referenced by marker-start/mid/end.
     * This handler captures marker metadata and enables defs-child buffering.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     *
     * @return string
     */
    protected function parseSVGTagSTARTmarker(int $soid, array $attr): string
    {
        return $this->registerSVGDefsContainer($soid, 'marker', $attr);
    }

    /**
     * Parse the SVG End tag 'marker'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string
     */
    protected function parseSVGTagENDmarker(int $soid): string
    {
        return $this->setSVGDefsMode($soid, false);
    }

    /**
     * Parse the SVG Start tag 'pattern'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     *
     * @return string
     */
    protected function parseSVGTagSTARTpattern(int $soid, array $attr): string
    {
        return $this->registerSVGDefsContainer($soid, 'pattern', $attr);
    }

    /**
     * Parse the SVG End tag 'pattern'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string
     */
    protected function parseSVGTagENDpattern(int $soid): string
    {
        return $this->setSVGDefsMode($soid, false);
    }

    /**
     * Parse the SVG Start tag 'mask'.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     *
     * @return string
     */
    protected function parseSVGTagSTARTmask(int $soid, array $attr): string
    {
        return $this->registerSVGDefsContainer($soid, 'mask', $attr);
    }

    /**
     * Parse the SVG End tag 'mask'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string
     */
    protected function parseSVGTagENDmask(int $soid): string
    {
        return $this->setSVGDefsMode($soid, false);
    }

    /**
     * Parse the SVG Start tag 'filter'.
     *
     * SVG filters (<filter> with fe* primitives) define a pixel-level processing
     * pipeline that has no direct equivalent in static PDF.  This handler is a
     * deliberate no-op: the <filter> element and all its fe* children are already
     * excluded from the character-data parser (see SVGCHARDATASKIPTAGS), so this
     * method exists only to silence the dispatch-table default branch and to make
     * the intentional non-support self-documenting.
     *
     * A filter reference on a shape element (filter="url(#f)") is also silently
     * ignored — the shape is rendered without the filter effect applied.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     *
     * @return string Empty string — filters are not supported in PDF output.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function parseSVGTagSTARTfilter(int $soid, array $attr): string
    {
        // Filters cannot be represented in static PDF.  Mark defs-mode so the
        // fe* child elements captured by SVGCHARDATASKIPTAGS are discarded, and
        // register a placeholder in defs so a filter="url(#id)" reference does
        // not trigger spurious fallback paths elsewhere.
        return $this->registerSVGDefsContainer($soid, 'filter', $attr);
    }

    /**
     * Parse the SVG End tag 'filter'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string Empty string — filters are not supported in PDF output.
     */
    protected function parseSVGTagENDfilter(int $soid): string
    {
        return $this->setSVGDefsMode($soid, false);
    }

    /**
     * Parse the SVG Start tag 'a' (hyperlink).
     *
     * Records the link URL and bounding origin.  Child elements are rendered
     * normally; the annotation is emitted on the matching </a> end tag.
     * Unsupported SVG feature: full bounding-box tracking of arbitrary child
     * shapes is not implemented.  A best-effort URI annotation is emitted at the
     * current cursor position with a placeholder size on </a>.
     *
     * @param int $soid ID of the current SVG object.
     * @param TSVGAttributes $attr SVG attributes.
     *
     * @return string
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function parseSVGTagSTARTa(int $soid, array $attr): string
    {
        $href = $attr['xlink:href'] ?? $attr['href'] ?? '';
        if ($href === '') {
            return '';
        }

        $this->svgobjs[$soid]['textmode']['linkhref'] = $href;

        $this->svgobjs[$soid]['textmode']['linkx'] = $this->svgobjs[$soid]['x'];

        $this->svgobjs[$soid]['textmode']['linky'] = $this->svgobjs[$soid]['y'];
        return '';
    }

    /**
     * Parse the SVG End tag 'a'.
     *
     * @param int $soid ID of the current SVG object.
     *
     * @return string
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function parseSVGTagENDa(int $soid): string
    {
        $href = $this->svgobjs[$soid]['textmode']['linkhref'] ?? '';
        if ($href === '') {
            return '';
        }

        $startX = $this->svgobjs[$soid]['textmode']['linkx'] ?? $this->svgobjs[$soid]['x'];
        $startY = $this->svgobjs[$soid]['textmode']['linky'] ?? $this->svgobjs[$soid]['y'];
        $endX = $this->svgobjs[$soid]['x'];
        $endY = $this->svgobjs[$soid]['y'];

        $deltaX = \abs($endX - $startX);
        $deltaY = \abs($endY - $startY);
        $font = $this->font->getCurrentFont();
        $fontHeight = $this->toUnit($font['size']);
        if ($fontHeight <= 0.0) {
            $fontHeight = 1.0;
        }

        if ($this->svgobjs[$soid]['textmode']['vertical'] ?? false) {
            $width = \max($deltaX, $this->getStringWidth('M'));
            $height = \max($deltaY, $fontHeight);
        } else {
            $width = \max($deltaX, $this->getStringWidth(' '));
            $height = \max($deltaY, $fontHeight);
        }

        $posx = \min($startX, $endX);
        $posy = \min($startY, $endY);
        if (\method_exists($this, 'setLink')) {
            $lnkid = $this->setLink($posx, $posy, $width, $height, $href);
            if (\is_int($lnkid)) {
                $this->page->addAnnotRef($lnkid, $this->page->getPageID());
            }
        }

        $this->svgobjs[$soid]['textmode']['linkhref'] = '';

        $this->svgobjs[$soid]['textmode']['linkx'] = $this->svgobjs[$soid]['x'];

        $this->svgobjs[$soid]['textmode']['linky'] = $this->svgobjs[$soid]['y'];
        return '';
    }

    /**
     * Parse the SVG Start tag 'switch'.
     *
     * In a static PDF context we treat all feature requirements as satisfied
     * and simply render the first child (by letting the parser continue normally).
     *
     * @param int $_soid ID of the current SVG object.
     *
     * @return string
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     * @phpstan-param TSVGAttributes $_attr
     */
    protected function parseSVGTagSTARTswitch(int $_soid, array $_attr = []): string
    {
        return '';
    }

    /**
     * Parse the SVG End tag 'switch'.
     *
     * @param int $_soid ID of the current SVG object.
     *
     * @return string
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function parseSVGTagENDswitch(int $_soid): string
    {
        return '';
    }

    /**
     * Normalize a potentially malformed SVG node name to a safe string.
     */
    protected function normalizeSVGNodeName(mixed $name): string
    {
        return \is_string($name) ? $name : '';
    }

    /**
     * Parse the SVG Start tag 'use'.
     *
     * @param \XMLParser $parser The XML parser calling the handler.
     * @param int $soid ID of the current SVG object.
     * @phpstan-param TSVGAttributes $attr
     *
     * @return string
     */
    protected function parseSVGTagSTARTuse(\XMLParser $parser, int $soid, array $attr): string
    {
        // SVG 2 uses plain 'href'; fall back from xlink:href for compatibility.
        $href = $attr['xlink:href'] ?? $attr['href'] ?? '';
        if ($href === '') {
            return '';
        }

        if (!isset($this->svgobjs[$soid])) {
            return '';
        }

        $svgdefid = \substr($href, 1);
        if (!isset($this->svgobjs[$soid]['defs'][$svgdefid])) {
            return '';
        }
        $defs = $this->svgobjs[$soid]['defs'];
        /** @var TSVGAttribs $use */
        $use = $defs[$svgdefid];

        if (isset($attr['xlink:href'])) {
            unset($attr['xlink:href']);
        }
        if (isset($attr['href'])) {
            unset($attr['href']);
        }
        if (isset($attr['id'])) {
            unset($attr['id']);
        }

        // When the target is a <symbol>, expand it like an inner <svg>:
        // apply the <use> x/y offset, optional width/height, and the symbol's viewBox.
        if ($use['name'] === 'symbol') {
            $symAttr = $use['attr'];
            $useX = isset($attr['x']) ? $this->svgUnitToUnit($attr['x'], $soid) : 0.0;
            $useY = isset($attr['y']) ? $this->svgUnitToUnit($attr['y'], $soid) : 0.0;
            if (isset($attr['width'])) {
                $useW = $this->svgUnitToUnit($attr['width'], $soid);
            } elseif (isset($symAttr['width'])) {
                $useW = $this->svgUnitToUnit($symAttr['width'], $soid);
            } else {
                $useW = 0.0;
            }
            if (isset($attr['height'])) {
                $useH = $this->svgUnitToUnit($attr['height'], $soid);
            } elseif (isset($symAttr['height'])) {
                $useH = $this->svgUnitToUnit($symAttr['height'], $soid);
            } else {
                $useH = 0.0;
            }

            // E-1/R-3 hardening: if use width/height are omitted, use viewBox size.
            if (($useW <= 0.0 || $useH <= 0.0) && isset($symAttr['viewBox']) && $symAttr['viewBox'] !== '') {
                $viewBoxVals = \preg_split('/[\s,]+/', \trim($symAttr['viewBox']), -1, \PREG_SPLIT_NO_EMPTY);
                if (\is_array($viewBoxVals) && isset($viewBoxVals[2], $viewBoxVals[3])) {
                    $vbw = \abs($this->svgUnitToUnit($viewBoxVals[2], $soid));
                    $vbh = \abs($this->svgUnitToUnit($viewBoxVals[3], $soid));
                    if ($useW <= 0.0 && $vbw > 0.0) {
                        $useW = $vbw;
                    }
                    if ($useH <= 0.0 && $vbh > 0.0) {
                        $useH = $vbh;
                    }
                }
            }

            // Build an attr array that looks like an inner <svg> while preserving
            // use-level style/transform/inherited attributes for compatibility.
            /** @var TSVGAttributes $svglikeAttr */
            $svglikeAttr = $attr;
            $svglikeAttr['x'] = (string) $useX;
            $svglikeAttr['y'] = (string) $useY;
            $svglikeAttr['width'] = (string) $useW;
            $svglikeAttr['height'] = (string) $useH;
            if (!isset($svglikeAttr['viewBox']) && isset($symAttr['viewBox']) && $symAttr['viewBox'] !== '') {
                $svglikeAttr['viewBox'] = $symAttr['viewBox'];
            }
            if (
                !isset($svglikeAttr['preserveAspectRatio'])
                && isset($symAttr['preserveAspectRatio'])
                && $symAttr['preserveAspectRatio'] !== ''
            ) {
                $svglikeAttr['preserveAspectRatio'] = $symAttr['preserveAspectRatio'];
            }

            // Temporarily bump tagdepth so parseSVGTagSTARTsvg treats this as an inner SVG.

            $this->svgobjs[$soid]['tagdepth'] += 1;
            $defStyleRaw = \end($this->svgobjs[$soid]['styles']);
            $defStyle = \is_array($defStyleRaw) ? $defStyleRaw : self::DEFSVGSTYLE;

            $out = '';
            $useStyle = $defStyle;

            // Preserve symbol-level presentation/style attributes first so the
            // symbol container behaves like an inner <svg> wrapper. Use-level
            // presentation/style attrs are then applied on top as the final override.
            $symbolStyleTag = '';
            if (isset($symAttr['style']) && $symAttr['style'] !== '') {
                $symbolStyleTag = $symAttr['style'][0] === ';' ? $symAttr['style'] : ';' . $symAttr['style'];
            }
            foreach (self::SVGINHPROP as $styleKey) {
                if (isset($symAttr[$styleKey]) && \is_string($symAttr[$styleKey]) && $symAttr[$styleKey] !== '') {
                    $useStyle[$styleKey] = $symAttr[$styleKey];
                } elseif ($symbolStyleTag !== '') {
                    $styleDefault = $useStyle[$styleKey] ?? '';
                    $useStyle[$styleKey] = $this->parseCSSAttrib(
                        $symbolStyleTag,
                        $styleKey,
                        \is_scalar($styleDefault) ? (string) $styleDefault : '',
                    );
                }
            }

            if (isset($symAttr['transform']) && $symAttr['transform'] !== '') {
                $useTransform = \is_array($useStyle['transfmatrix']) ? $useStyle['transfmatrix'] : self::TMXID;
                $useStyle['transfmatrix'] = $this->graph->getCtmProduct(
                    $useTransform,
                    $this->getSVGTransformMatrix($symAttr['transform']),
                );
            }

            // Preserve use-level presentation/style attributes for symbol expansion.
            $styleTag = '';
            if (isset($svglikeAttr['style']) && $svglikeAttr['style'] !== '') {
                $styleTag = $svglikeAttr['style'][0] === ';' ? $svglikeAttr['style'] : ';' . $svglikeAttr['style'];
            }
            foreach (self::SVGINHPROP as $styleKey) {
                if (
                    isset($svglikeAttr[$styleKey])
                    && \is_string($svglikeAttr[$styleKey])
                    && $svglikeAttr[$styleKey] !== ''
                ) {
                    $useStyle[$styleKey] = $svglikeAttr[$styleKey];
                } elseif ($styleTag !== '') {
                    $styleDefault = $useStyle[$styleKey] ?? '';
                    $useStyle[$styleKey] = $this->parseCSSAttrib(
                        $styleTag,
                        $styleKey,
                        \is_scalar($styleDefault) ? (string) $styleDefault : '',
                    );
                }
            }

            if (isset($svglikeAttr['transform']) && $svglikeAttr['transform'] !== '') {
                $useTransform = \is_array($useStyle['transfmatrix']) ? $useStyle['transfmatrix'] : self::TMXID;
                $useStyle['transfmatrix'] = $this->graph->getCtmProduct(
                    $useTransform,
                    $this->getSVGTransformMatrix($svglikeAttr['transform']),
                );
            }

            if (!\is_array($useStyle['transfmatrix'])) {
                $useStyle['transfmatrix'] = self::TMXID;
            }

            /** @var TSVGStyle $useStyleTyped */
            $useStyleTyped = $useStyle;

            $out .= $this->parseSVGTagSTARTsvg($parser, $soid, $svglikeAttr, $useStyleTyped, $defStyle);

            // Replay each child element stored under the symbol def.
            if (isset($use['child']) && $use['child'] !== []) {
                foreach ($use['child'] as $child) {
                    $childName = $this->normalizeSVGNodeName($child['name']);
                    if ($childName === '') {
                        continue;
                    }

                    if (isset($child['attr']['closing_tag'])) {
                        // closing-tag sentinel — emit the matching end handler
                        $this->handleSVGTagEnd($parser, $childName);
                    } else {
                        $childAttr = $child['attr'];
                        $this->handleSVGTagStart($parser, $childName, $childAttr, $soid);
                    }
                }
            }

            $out .= $this->parseSVGTagENDsvg($soid);
            // parseSVGTagENDsvg calls parseSVGTagENDg which decrements tagdepth via its
            // own stack pop; we also decremented it above, so restore the balance.
            if (isset($this->svgobjs[$soid]['tagdepth'])) {
                $this->svgobjs[$soid]['tagdepth'] -= 1;
            }
            return $out;
        }

        if (isset($use['attr']['x'], $attr['x'])) {
            $attr['x'] = \strval(\floatval($attr['x']) + \floatval($use['attr']['x']));
        }
        if (isset($use['attr']['y'], $attr['y'])) {
            $attr['y'] = \strval(\floatval($attr['y']) + \floatval($use['attr']['y']));
        }
        if (!isset($attr['style']) || $attr['style'] === '') {
            $attr['style'] = '';
        }
        if (isset($use['attr']['style']) && $use['attr']['style'] !== '') {
            // merge styles
            $attr['style'] = \str_replace(';;', ';', ';' . $use['attr']['style'] . $attr['style']);
        }
        /** @var TSVGAttributes $attr */
        $attr = \array_merge($use['attr'], $attr);
        $useName = $this->normalizeSVGNodeName($use['name']);
        if ($useName === '') {
            return '';
        }

        $this->handleSVGTagStart($parser, $useName, $attr, $soid);
        return '';
    }

    /**
     * Get the SVG data from a file or data string.
     *
     * @param string $img
     *
     * @return string
     *
     * @throws \Com\Tecnick\File\Exception
     */
    protected function getRawSVGData(string $img): string
    {
        if ($img === '' || $img[0] === '@' && \strlen($img) === 1) {
            return '';
        }
        if ($img[0] === '@') { // image from string
            return \substr($img, 1);
        }
        $data = $this->file->getFileData($img);

        return \is_string($data) ? $data : '';
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
            'viewBox' => [0.0, 0.0, 0.0, 0.0],
            'ar_align' => 'xMidYMid',
            'ar_ms' => 'meet',
        ];

        $regs = [];
        \preg_match('/<svg([^\>]*)>/si', $data, $regs);
        if (!isset($regs[1]) || $regs[1] === '') {
            return $out;
        }

        $tmp = [];
        if (\preg_match('/[\s]+x[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp) && isset($tmp[1])) {
            $out['x'] = $this->svgUnitToUnit($tmp[1]);
        }
        $tmp = [];
        if (\preg_match('/[\s]+y[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp) && isset($tmp[1])) {
            $out['y'] = $this->svgUnitToUnit($tmp[1]);
        }
        $tmp = [];
        if (\preg_match('/[\s]+width[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp) && isset($tmp[1])) {
            $out['width'] = $this->svgUnitToUnit($tmp[1]);
        }
        $tmp = [];
        if (\preg_match('/[\s]+height[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp) && isset($tmp[1])) {
            $out['height'] = $this->svgUnitToUnit($tmp[1]);
        }

        $tmp = [];
        if (!\preg_match(
            '/[\s]+viewBox[\s]*=[\s]*"[\s]*([0-9\.\-]+)[\s]+([0-9\.\-]+)[\s]+([0-9\.]+)[\s]+([0-9\.]+)[\s]*"/si',
            $regs[1],
            $tmp,
        )) {
            return $out;
        }

        if (isset($tmp[1], $tmp[2], $tmp[3], $tmp[4])) {
            $vb0 = $this->svgUnitToUnit($tmp[1]);
            $vb1 = $this->svgUnitToUnit($tmp[2]);
            $vb2 = $this->svgUnitToUnit($tmp[3]);
            $vb3 = $this->svgUnitToUnit($tmp[4]);
            $out['viewBox'] = [
                0 => $vb0,
                1 => $vb1,
                2 => $vb2,
                3 => $vb3,
            ];
        }

        // get aspect ratio
        $tmp = [];
        if (!\preg_match('/[\s]+preserveAspectRatio[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
            return $out;
        }

        if (!isset($tmp[1])) {
            return $out;
        }

        $asr = \preg_split('/[\s]+/si', $tmp[1]);
        if (!\is_array($asr) || \count($asr) < 1) {
            return $out;
        }
        switch (\count($asr)) {
            case 3:
                if (isset($asr[1], $asr[2])) {
                    $out['ar_align'] = $asr[1];
                    $out['ar_ms'] = $asr[2];
                }
                break;
            case 2:
                if (isset($asr[0], $asr[1])) {
                    $out['ar_align'] = $asr[0];
                    $out['ar_ms'] = $asr[1];
                }
                break;
            case 1:
                $out['ar_align'] = $asr[0];
                $out['ar_ms'] = 'meet';
                break;
        }

        return $out;
    }

    /**
     * Pre-scan SVG data to collect gradient definitions before the main parse.
     *
     * SVG allows forward references — elements can reference gradients defined
     * later in <defs>. Because the main parser generates PDF commands in a
     * single pass, gradients must be registered first. This lightweight scan
     * extracts <linearGradient>, <radialGradient> and <stop> elements and
     * feeds them through the existing tag handlers so the gradient arrays are
     * populated before any drawing element needs them.
     *
     * @param string $data Raw SVG XML string.
     * @param int    $soid SVG object ID.
     */
    protected function prescanSVGGradients(string $data, int $soid): void
    {
        $gradientDepth = 0;
        $startHandler = function (\XMLParser $xmlParser, string $name, array $attr) use ($soid, &$gradientDepth): void {
            unset($xmlParser);
            $attr = $this->getSVGPrescanAttributes($attr);
            $name = $this->removeTagNamespace($name);
            switch ($name) {
                case 'linearGradient':
                    $this->parseSVGTagSTARTlinearGradient($soid, $attr);
                    $gradientDepth = 1;
                    break;
                case 'radialGradient':
                    $this->parseSVGTagSTARTradialGradient($soid, $attr);
                    $gradientDepth = 1;
                    break;
                case 'stop':
                    if ($gradientDepth === 0) {
                        break;
                    }
                    $svgstyle = $this->getSVGPrescanStopStyle($attr);
                    $this->parseSVGTagSTARTstop($soid, $attr, $svgstyle);
                    break;
            }
        };
        $endHandler = function (\XMLParser $xmlParser, string $name) use (&$gradientDepth): void {
            unset($xmlParser);
            $name = $this->removeTagNamespace($name);
            if ($name === 'linearGradient' || $name === 'radialGradient') {
                $gradientDepth = 0;
            }
        };

        $scanner = \xml_parser_create('UTF-8');
        \xml_parser_set_option($scanner, XML_OPTION_CASE_FOLDING, 0);
        \xml_set_element_handler($scanner, $startHandler, $endHandler);
        \xml_parse($scanner, $data);
        unset($scanner);
    }

    /**
     * Normalize XML parser callback attributes for gradient prescan handlers.
     *
     * @param array<int|string, mixed> $xmlAttr Raw XML callback attributes.
     *
     * @return TSVGAttributes
     */
    protected function getSVGPrescanAttributes(array $xmlAttr): array
    {
        /** @var TSVGAttributes $attr */
        $attr = [];

        foreach (self::SVGGRADIENTATTRIB as $key) {
            if (!isset($xmlAttr[$key])) {
                continue;
            }

            if (!\is_scalar($xmlAttr[$key])) {
                continue;
            }

            $value = (string) $xmlAttr[$key];
            switch ($key) {
                case 'id':
                case 'x1':
                case 'y1':
                case 'x2':
                case 'y2':
                case 'cx':
                case 'cy':
                case 'fx':
                case 'fy':
                case 'r':
                case 'offset':
                case 'gradientUnits':
                case 'gradientTransform':
                case 'xlink:href':
                case 'href':
                case 'stop-color':
                case 'stop-opacity':
                case 'style':
                    $attr[$key] = $value;
                    break;
            }
        }

        return $attr;
    }

    /**
     * Build the minimal typed style array needed to parse a gradient stop.
     *
     * @param TSVGAttributes $attr Prescanned stop tag attributes.
     *
     * @return TSVGStyle
     */
    protected function getSVGPrescanStopStyle(array $attr): array
    {
        $svgstyle = self::DEFSVGSTYLE;

        if (isset($attr['stop-color'])) {
            $svgstyle['stop-color'] = $attr['stop-color'];
        }
        if (isset($attr['stop-opacity'])) {
            $svgstyle['stop-opacity'] = $this->normalizeSVGAlphaValue($attr['stop-opacity']);
        }

        // Check inline style attribute for stop-color / stop-opacity.
        if (isset($attr['style'])) {
            $matches = [];
            if (\preg_match('/stop-color\s*:\s*([^;]+)/i', $attr['style'], $matches)) {
                if (isset($matches[1])) {
                    $svgstyle['stop-color'] = \trim($matches[1]);
                }
            }
            if (\preg_match('/stop-opacity\s*:\s*([^;]+)/i', $attr['style'], $matches)) {
                if (isset($matches[1])) {
                    $svgstyle['stop-opacity'] = $this->normalizeSVGAlphaValue($matches[1]);
                }
            }
        }

        return $svgstyle;
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
     *
     * @throws PdfException If SVG data is invalid or parsing fails.
     * @throws \Com\Tecnick\File\Exception If the SVG file cannot be read.
     * @throws \Com\Tecnick\Pdf\Page\Exception If page dimensions cannot be read.
     */
    public function addSVG(
        string $img,
        float $posx = 0.0,
        float $posy = 0.0,
        float $width = 0.0,
        float $height = 0.0,
        float $pageheight = 0.0,
    ): int {
        if ($pageheight <= 0.0) {
            $pageheight = $this->page->getPage()['height'];
        }
        $prevPageHeight = $this->graph->setPageHeight($pageheight);

        $imgdir = \dirname($img);
        if ($imgdir === '.') {
            $imgdir = '';
        }

        $data = $this->getRawSVGData($img);
        if ($data === '') {
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
        if ($width <= 0.0 && $height <= 0.0) {
            // convert image size to document unit
            $width = $size['width'];
            $height = $size['height'];
        } elseif ($width <= 0.0) {
            $width = ($height * $size['width']) / $size['height'];
        } elseif ($height <= 0.0) {
            $height = ($width * $size['height']) / $size['width'];
        }

        if ($size['viewBox'][2] > 0.0 && $size['viewBox'][3] > 0.0) {
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
        $sizeX = $size['x'];
        $sizeY = $size['y'];
        $sizeWidth = $size['width'];
        $sizeHeight = $size['height'];
        $svgoffset_x = $this->toPoints($posx - $sizeX);
        $svgoffset_y = $this->toPoints($sizeY - $posy);
        $svgscale_x = $width / $sizeWidth;
        $svgscale_y = $height / $sizeHeight;

        // scaling && alignment
        if ($size['ar_align'] !== 'none') {
            // force uniform scaling
            if ($size['ar_ms'] === 'slice') {
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

        $soid = (int) \array_key_last($this->svgobjs);
        $soid++;

        $this->svgobjs[$soid] = self::SVGDEFOBJ;
        $this->svgobjs[$soid]['dir'] = $imgdir;
        $this->svgobjs[$soid]['refunitval']['page']['height'] = $this->toPoints($pageheight);

        $out = '';
        $out .= $this->graph->getStartTransform();
        $out .= $this->graph->getRawRect($posx, $posy, $width, $height, 'CNZ');

        // scale && translate
        $esx = $this->toPoints($sizeX * (1 - $svgscale_x));
        $fsy = $this->toPoints(($pageheight - $sizeY) * (1 - $svgscale_y));

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

        // Pre-scan SVG to collect gradient definitions so that forward
        // references (e.g. <defs> at the end of the file) are available
        // when elements that use them are processed during the main parse.
        $this->prescanSVGGradients($data, $soid);

        // creates a new XML parser to be used by the other XML functions
        $parser = \xml_parser_create('UTF-8');
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
                \xml_error_string(\xml_get_error_code($parser)),
                \xml_get_current_line_number($parser),
            ));
        }

        // >= PHP 7.0.0 "explicitly unset the reference to parser to avoid memory leaks"
        unset($parser);

        if (!isset($this->svgobjs[$soid]['out'])) {
            $this->svgobjs[$soid]['out'] = '';
        }
        $this->svgobjs[$soid]['out'] .= $this->graph->getStopTransform();
        $this->graph->setPageHeight($prevPageHeight);

        return $soid;
    }

    /**
     * Get the PDF output string to print the specified SVG object.
     *
     * @param int   $soid       SVG Object ID (as returned by addSVG).
     *
     * @return string Image PDF page content.
     *
     * @throws PdfException If the SVG object ID is unknown.
     */
    public function getSetSVG(int $soid): string
    {
        if (!isset($this->svgobjs[$soid])) {
            throw new PdfException('Unknownn SVG ID: ' . $soid);
        }

        $out = $this->svgobjs[$soid]['out'] ?? '';
        $children = $this->svgobjs[$soid]['child'] ?? [];

        foreach ($children as $chid) {
            $out .= $this->getSetSVG($chid);
        }

        return $out;
    }
}
