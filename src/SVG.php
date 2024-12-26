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

    protected float $svgminunitlen = 0;

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

    protected function gatSVGPath(
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
            'xmin' => 2147483647.0,
            'xmax' => 0.0,
            'ymin' => 2147483647.0,
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
                if (($swf == 0) and ($dang > 0)) {
                    $dang -= (2 * M_PI);
                } elseif (($swf == 1) and ($dang < 0)) {
                    $dang += (2 * M_PI);
                }
                $angf = $angs - $dang;
                if ((($swf == 0) and ($angs > $angf)) or (($swf == 1) and ($angs < $angf))) {
                    // reverse angles
                    $tmp = $angs;
                    $angs = $angf;
                    $angf = $tmp;
                }
                $angs = round(rad2deg($angs), 6);
                $angf = round(rad2deg($angf), 6);
                // covent angles to positive values
                if (($angs < 0) and ($angf < 0)) {
                    $angs += 360;
                    $angf += 360;
                }
                $pie = false;
                if (($key == 0) and (isset($paths[($key + 1)][1])) and (trim($paths[($key + 1)][1]) == 'z')) {
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
        switch ($spacing) {
            case 'normal':
                return 0;
            case 'inherit':
                return $parent;
        }

        $ref = self::REFUNITVAL;
        $ref['parent'] = $parent;

        return $this->getUnitValuePoints($spacing, self::REFUNITVAL);
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
        switch ($stretch) {
            case 'ultra-condensed':
                return 40;
            case 'extra-condensed':
                return 55;
            case 'condensed':
                return 70;
            case 'semi-condensed':
                return 85;
            case 'normal':
                return 100;
            case 'semi-expanded':
                return 115;
            case 'expanded':
                return 130;
            case 'extra-expanded':
                return 145;
            case 'ultra-expanded':
                return 160;
            case 'wider':
                return ($parent + 10);
            case 'narrower':
                return ($parent - 10);
            case 'inherit':
                return $parent;
        }

        $ref = self::REFUNITVAL;
        $ref['parent'] = $parent;

        return $this->getUnitValuePoints($stretch, $ref, '%');
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
        switch ($weight) {
            case 'bold':
            case 'bolder':
                return 'B';
            case 'normal':
                return '';
        }

        return '';
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
        switch ($style) {
            case 'italic':
            case 'oblique':
                return 'I';
            case 'normal':
                return '';
        }

        return '';
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
        switch ($decoration) {
            case 'underline':
                return 'U';
            case 'overline':
                return 'O';
            case 'line-through':
                return 'D';
        }

        return '';
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
}
