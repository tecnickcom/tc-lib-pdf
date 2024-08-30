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
 */
abstract class SVG extends \Com\Tecnick\Pdf\Text
{
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
}
