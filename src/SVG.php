<?php

/**
 * SVG.php
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
 * @copyright 2002-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TTMatrix from \Com\Tecnick\Pdf\Graph\Base
 * @phpstan-import-type TRefUnitValues from \Com\Tecnick\Pdf\Base
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
 *    'font-size-adjust': string,
 *    'font-stretch': string,
 *    'font-style': string,
 *    'font-variant': string,
 *    'font-weight': string,
 *    'glyph-orientation-horizontal': string,
 *    'glyph-orientation-vertical': string,
 *    'image-rendering': string,
 *    'kerning': string,
 *    'letter-spacing': string,
 *    'lighting-color': string,
 *    'marker': string,
 *    'marker-end': string,
 *    'marker-mid': string,
 *    'marker-start': string,
 *    'mask': string,
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
        'font-size-adjust' => 'none',
        'font-stretch' => 'normal',
        'font-style' => 'normal',
        'font-variant' => 'normal',
        'font-weight' => 'normal',
        'glyph-orientation-horizontal' => '0deg',
        'glyph-orientation-vertical' => 'auto',
        'image-rendering' => 'auto',
        'kerning' => 'auto',
        'letter-spacing' => 'normal',
        'lighting-color' => 'white',
        'marker' => '',
        'marker-end' => 'none',
        'marker-mid' => 'none',
        'marker-start' => 'none',
        'mask' => 'none',
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
     * Stack of SVG styles.
     *
     * @var array<TSVGStyle>
     */
    protected array $svgstyles = [self::DEFSVGSTYLE];

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
                '/(matrix|translate|scale|rotate|skewX|skewY)[\s]*\(([^\)]+)\)/si',
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

            $tmb = $this->graph::IDMATRIX;
            $val = $data[2];
            $regs = [];

            switch ($data[1]) {
                case 'matrix':
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
                    break;
                case 'translate':
                    if (preg_match('/([a-z0-9\-\.]+)[\,\s]+([a-z0-9\-\.]+)/si', $val, $regs)) {
                        $tmb[4] = floatval($regs[1]);
                        $tmb[5] = floatval($regs[2]);
                        break;
                    }
                    if (preg_match('/([a-z0-9\-\.]+)/si', $val, $regs)) {
                        $tmb[4] = floatval($regs[1]);
                    }
                    break;
                case 'scale':
                    if (preg_match('/([a-z0-9\-\.]+)[\,\s]+([a-z0-9\-\.]+)/si', $val, $regs)) {
                        $tmb[0] = floatval($regs[1]);
                        $tmb[3] = floatval($regs[2]);
                        break;
                    }
                    if (preg_match('/([a-z0-9\-\.]+)/si', $val, $regs)) {
                        $tmb[0] = floatval($regs[1]);
                        $tmb[3] = $tmb[0];
                    }
                    break;
                case 'rotate':
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
                        break;
                    }
                    if (preg_match('/([0-9\-\.]+)/si', $val, $regs)) {
                        $ang = deg2rad(floatval($regs[1]));
                        $tmb[0] = cos($ang);
                        $tmb[1] = sin($ang);
                        $tmb = [$tmb[0], $tmb[1], -$tmb[1], $tmb[0], 0, 0];
                    }
                    break;
                case 'skewX':
                    if (preg_match('/([0-9\-\.]+)/si', $val, $regs)) {
                        $tmb[2] = tan(deg2rad(floatval($regs[1])));
                    }
                    break;
                case 'skewY':
                    if (preg_match('/([0-9\-\.]+)/si', $val, $regs)) {
                        $tmb[1] = tan(deg2rad(floatval($regs[1])));
                    }
                    break;
            }

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

    /*
    protected function applySVGStyle(
        array $svgstyle,
        array $ref = self::REFUNITVAL,
        float $posx = 0.0,
        float $posy = 0.0,
        float $width = 1.0,
        float $height = 1.0,
    ): string {
        return '';
    }*/
}
