<?php

declare(strict_types=1);

/**
 * PdfColor.php
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

/**
 * Com\Tecnick\Pdf\PdfColor
 *
 * PDF color adapter with conformance-aware output.
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class PdfColor extends \Com\Tecnick\Color\Pdf
{
    /**
     * Force DeviceCMYK output for process colors.
     */
    protected bool $forceDeviceCmyk = false;

    /**
     * Enable or disable DeviceCMYK forcing for process colors.
     */
    public function setForceDeviceCmyk(bool $enabled): void
    {
        $this->forceDeviceCmyk = $enabled;
    }

    /**
     * Return true when DeviceCMYK forcing is enabled.
     */
    public function isForceDeviceCmyk(): bool
    {
        return $this->forceDeviceCmyk;
    }

    /**
     * Return a PDF color operator string.
     *
     * When DeviceCMYK forcing is enabled, process colors are emitted as CMYK operators
     * to avoid DeviceRGB output in restrictive PDF/X modes.
     */
    public function getPdfColor(string $color, bool $stroke = false, float $tint = 1): string
    {
        if (!$this->forceDeviceCmyk) {
            return parent::getPdfColor($color, $stroke, $tint);
        }

        // Preserve spot color behavior under PDF/X restrictions.
        try {
            $col = $this->getSpotColor($color);
            $tint = \sprintf('cs %F scn', \max(0, \min(1, $tint)));
            if ($stroke) {
                $tint = \strtoupper($tint);
            }

            return \sprintf('/CS%d %s' . "\n", $col['i'], $tint);
        } catch (\Com\Tecnick\Color\Exception $colorException) {
            // Spot-color lookup may fail for process colors; fall back to CMYK conversion below.
            unset($colorException);
        }

        $model = $this->getColorObject($color);
        if (!$model instanceof \Com\Tecnick\Color\Model) {
            return '';
        }

        $cmyk = new \Com\Tecnick\Color\Model\Cmyk($model->toCmykArray());
        return $cmyk->getPdfColor($stroke);
    }
}
