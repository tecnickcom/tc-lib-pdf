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
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
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
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
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
     * True when a DeviceCMYK color operator was emitted.
     */
    protected bool $deviceCmykEmitted = false;

    /**
     * Names of the emitted spot colors whose Separation alternate space is DeviceCMYK.
     *
     * @var array<string, true>
     */
    protected array $cmykSpotColors = [];

    /**
     * Return true when a DeviceCMYK color operator was emitted.
     */
    public function hasEmittedDeviceCmyk(): bool
    {
        return $this->deviceCmykEmitted;
    }

    /**
     * Return the names of the emitted spot colors whose Separation alternate
     * space is DeviceCMYK.
     *
     * @return array<int, string>
     */
    public function getEmittedCmykSpotColors(): array
    {
        return \array_keys($this->cmykSpotColors);
    }

    /**
     * Record the given spot color when its Separation alternate space is DeviceCMYK.
     *
     * @param string $color Name of a spot color that resolves to a Separation resource.
     */
    protected function trackCmykSpotColor(string $color): void
    {
        try {
            $spot = $this->getSpotColor($color);
        } catch (\Com\Tecnick\Color\Exception $colorException) {
            unset($colorException);
            return;
        }

        if ($spot['space'] !== 'DeviceCMYK') {
            return;
        }

        $this->cmykSpotColors[$spot['name']] = true;
    }

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

    /**
     * Parse and unquote a spot() color name token.
     *
     * @param string $token Raw name token from the spot() CSS function.
     *
     * @return string The cleaned spot color name, or an empty string when the token is empty.
     */
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

    /**
     * Parse a spot() tint token into a factor in the range 0-1.
     *
     * Accepts a percentage (e.g. "50%") or a bare number; values above 1 and up to 100
     * are treated as percentages. The result is clamped to the range 0-1.
     *
     * @param string $token Raw tint token from the spot() CSS function.
     *
     * @return ?float The tint factor in the range 0-1, or null when the token is empty or non-numeric.
     */
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
     *
     * @param string $color     Color name, hex value, or spot() CSS function.
     * @param bool   $stroke    If true return the stroking color operator, otherwise the non-stroking one.
     * @param float  $tint      Tint value in the range 0-1 applied to spot colors.
     * @param bool   $allowSpot True to resolve (and register) spot colors, false to force a device color.
     *
     * @return string PDF color operator string.
     */
    public function getPdfColor(string $color, bool $stroke = false, float $tint = 1, bool $allowSpot = true): string
    {
        $spotFromCss = $this->parseSpotCssFunction($color);
        $explicitSpot = $spotFromCss !== null;
        if ($explicitSpot) {
            $color = $spotFromCss[0];
            $tint *= $spotFromCss[1];
        }

        if (!$this->forceDeviceCmyk) {
            // A Separation (spot) color is used only when explicitly requested
            // through the spot() CSS function, or when the name refers to a spot
            // color already registered by the caller. Bare color names
            // (e.g. "black", "red") resolve to process colors.
            if ($allowSpot && ($explicitSpot || $this->isRegisteredSpotColor($color))) {
                $this->trackCmykSpotColor($color);
                return parent::getPdfColor($color, $stroke, $tint, true);
            }

            // A Lab process color is emitted through a Separation resource, so it
            // is available only when spot colors are allowed.
            if ($allowSpot) {
                $labColor = $this->getLabProcessColor($color);
                if ($labColor instanceof \Com\Tecnick\Color\Model\Lab) {
                    return $this->getPdfLabProcessColor($labColor, $stroke);
                }
            }

            return $this->getPdfProcessColor($color, $stroke);
        }

        // Preserve spot color behavior under PDF/X restrictions.
        if ($allowSpot) {
            try {
                $col = $this->getSpotColor($color);
                $this->trackCmykSpotColor($color);
                $tint = \sprintf('cs %F scn', \max(0, \min(1, $tint)));
                if ($stroke) {
                    $tint = \strtoupper($tint);
                }

                return \sprintf('/CS%d %s' . "\n", $col['i'], $tint);
            } catch (\Com\Tecnick\Color\Exception $colorException) {
                // Spot-color lookup may fail for process colors; fall back to CMYK conversion below.
                unset($colorException);
            }
        }

        $model = $this->getColorObject($color, $allowSpot);
        if (!$model instanceof \Com\Tecnick\Color\Model) {
            return '';
        }

        $cmyk = new \Com\Tecnick\Color\Model\Cmyk($model->toCmykArray());
        $this->deviceCmykEmitted = true;
        return $cmyk->getPdfColor($stroke);
    }

    /**
     * Return true when the given name refers to a spot color that has already
     * been registered by the caller (and is therefore safe to emit as a
     * Separation resource).
     */
    protected function isRegisteredSpotColor(string $color): bool
    {
        $key = $this->normalizeSpotColorName($color);
        if ($key === '') {
            return false;
        }

        return \array_key_exists($key, $this->getSpotColors());
    }

    /**
     * Resolve a color name/value to a process color operator, bypassing the
     * default spot-color name table so that common color names are not turned
     * into DeviceCMYK Separations.
     */
    protected function getPdfProcessColor(string $color, bool $stroke): string
    {
        $model = $this->tryGetColorObj($color);

        if (!$model instanceof \Com\Tecnick\Color\Model) {
            return '';
        }

        if ($model instanceof \Com\Tecnick\Color\Model\Cmyk) {
            $this->deviceCmykEmitted = true;
        }

        return $model->getPdfColor($stroke);
    }

    /**
     * Return a Lab model for a CSS Lab process color, if any.
     */
    protected function getLabProcessColor(string $color): ?\Com\Tecnick\Color\Model\Lab
    {
        $model = $this->tryGetColorObj($color);

        return $model instanceof \Com\Tecnick\Color\Model\Lab ? $model : null;
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

            try {
                $spotKey = $this->addSpotLabColor(
                    $spotName,
                    $lab['lstar'] ?? 0.0,
                    $lab['astar'] ?? 0.0,
                    $lab['bstar'] ?? 0.0,
                );
            } catch (\Com\Tecnick\Color\Exception $colorException) {
                // Registration may fail; fall back to a device color.
                unset($colorException);
                return $labColor->getPdfColor($stroke);
            }

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
