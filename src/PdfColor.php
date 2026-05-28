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
     * Cache normalized spot keys for Lab process colors.
     *
     * @var array<string, string>
     */
    protected array $labSpotKeys = [];

    /**
     * Parse CSS spot(name[, tint]) and return [name, tint] when valid.
     *
     * @return array{0: string, 1: float}|null
     */
    protected function parseSpotCssFunction(string $color): ?array
    {
        $trimmed = \trim($color);
        if (!\str_starts_with(\strtolower($trimmed), 'spot(')) {
            return null;
        }

        $match = [];
        $ok = \preg_match(
            '/^spot\(\s*("(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\'|[^,\)]+?)\s*(?:,\s*([^\)]+?)\s*)?\)$/i',
            $trimmed,
            $match,
        );
        if ($ok !== 1) {
            return null;
        }

        $nameToken = \trim($match[1] ?? '');
        $name = $this->parseSpotNameToken($nameToken);
        if ($name === '') {
            return null;
        }

        $tintToken = \trim($match[2] ?? '');
        $tint = $tintToken === '' ? 1.0 : $this->parseSpotTintToken($tintToken);
        if ($tint === null) {
            return null;
        }

        return [$name, $tint];
    }

    protected function parseSpotNameToken(#[\SensitiveParameter] string $token): string
    {
        $token = \trim($token);
        if ($token === '') {
            return '';
        }

        $first = $token[0];
        $last = $token[\strlen($token) - 1];
        if ($first === '"' && $last === '"' || $first === '\'' && $last === '\'') {
            $token = \substr($token, 1, -1);
            $token = \stripcslashes($token);
        }

        return \trim($token);
    }

    protected function parseSpotTintToken(#[\SensitiveParameter] string $token): ?float
    {
        $token = \trim($token);
        if ($token === '') {
            return null;
        }

        $percent = \str_ends_with($token, '%');
        $num = $percent ? \substr($token, 0, -1) : $token;
        if (!\is_numeric($num)) {
            return null;
        }

        $tint = (float) $num;
        if ($percent) {
            $tint /= 100.0;
        } elseif ($tint > 1.0 && $tint <= 100.0) {
            $tint /= 100.0;
        }

        return \max(0.0, \min(1.0, $tint));
    }

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
        $spotFromCss = $this->parseSpotCssFunction($color);
        if ($spotFromCss !== null) {
            $color = $spotFromCss[0];
            $tint *= $spotFromCss[1];
        }

        if (!$this->forceDeviceCmyk) {
            $labColor = $this->getLabProcessColor($color);
            if ($labColor instanceof \Com\Tecnick\Color\Model\Lab) {
                return $this->getPdfLabProcessColor($labColor, $stroke);
            }

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

    /**
     * Return a Lab model for a CSS Lab process color, if any.
     */
    protected function getLabProcessColor(string $color): ?\Com\Tecnick\Color\Model\Lab
    {
        $model = $this->tryGetColorObj($color);

        if (!$model instanceof \Com\Tecnick\Color\Model) {
            return null;
        }

        if ($model->getType() !== 'LAB') {
            return null;
        }

        return new \Com\Tecnick\Color\Model\Lab($model->toLabArray());
    }

    /**
     * Emit PDF color command for a Lab process color using a Separation/Lab resource.
     */
    protected function getPdfLabProcessColor(\Com\Tecnick\Color\Model\Lab $labColor, bool $stroke): string
    {
        $lab = $labColor->toLabArray();
        $cacheKey = \sprintf('%F|%F|%F', $lab['lstar'] ?? 0.0, $lab['astar'] ?? 0.0, $lab['bstar'] ?? 0.0);
        $spotKey = $this->labSpotKeys[$cacheKey] ?? '';

        if ($spotKey === '') {
            $spotName = 'LAB_' . \substr(\sha1($cacheKey), 0, 16);
            $spotKey = $this->addSpotLabColor(
                $spotName,
                $lab['lstar'] ?? 0.0,
                $lab['astar'] ?? 0.0,
                $lab['bstar'] ?? 0.0,
            );
            $this->labSpotKeys[$cacheKey] = $spotKey;
        }

        try {
            $spot = $this->getSpotColor($spotKey);
        } catch (\Com\Tecnick\Color\Exception $colorException) {
            unset($colorException);
            return $labColor->getPdfColor($stroke);
        }

        $mode = 'cs 1.000000 scn';
        if ($stroke) {
            $mode = \strtoupper($mode);
        }

        return \sprintf('/CS%d %s' . "\n", $spot['i'], $mode);
    }
}
