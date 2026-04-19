<?php

/**
 * TestableCSS.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

/**
 * @phpstan-import-type TCellBound from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type StyleData from \Com\Tecnick\Pdf\Graph\Base
 * @phpstan-import-type TCSSBorderSpacing from \Com\Tecnick\Pdf\CSS
 * @phpstan-import-type TCSSData from \Com\Tecnick\Pdf\CSS
 */
class TestableCSS extends \Com\Tecnick\Pdf\Tcpdf
{
    public function exposeGetCSSBorderWidthPoints(string $width): float
    {
        return $this->getCSSBorderWidthPoints($width);
    }

    public function exposeGetCSSBorderWidth(string $width): float
    {
        return $this->getCSSBorderWidth($width);
    }

    public function exposeGetCSSBorderDashStyle(string $style): int
    {
        return $this->getCSSBorderDashStyle($style);
    }

    /** @phpstan-return StyleData */
    public function exposeGetCSSDefaultBorderStyle(): array
    {
        return $this->getCSSDefaultBorderStyle();
    }

    /** @phpstan-return StyleData */
    public function exposeGetCSSBorderStyle(string $cssborder): array
    {
        return $this->getCSSBorderStyle($cssborder);
    }

    /** @phpstan-return TCellBound */
    public function exposeGetCSSPadding(string $csspadding, float $width = 0.0): array
    {
        return $this->getCSSPadding($csspadding, $width);
    }

    /** @phpstan-return TCellBound */
    public function exposeGetCSSMargin(string $cssmargin, float $width = 0.0): array
    {
        return $this->getCSSMargin($cssmargin, $width);
    }

    /** @phpstan-return TCSSBorderSpacing */
    public function exposeGetCSSBorderMargin(string $cssbspace, float $width = 0.0): array
    {
        return $this->getCSSBorderMargin($cssbspace, $width);
    }

    /** @phpstan-param array<int, array{c: string}> $css */
    public function exposeImplodeCSSData(array $css): string
    {
        /** @var array<string, TCSSData> $normalized */
        $normalized = [];
        foreach ($css as $index => $style) {
            $key = (string) $index;
            $normalized[$key] = [
                'k' => $key,
                'c' => $style['c'],
                's' => $key,
            ];
        }
        /** @var array<string, TCSSData> $normalized */

        return $this->implodeCSSData($normalized);
    }

    public function exposeTidyCSS(string $css): string
    {
        return $this->tidyCSS($css);
    }

    /** @phpstan-return array<string, string> */
    public function exposeExtractCSSproperties(string $css): array
    {
        return $this->extractCSSproperties($css);
    }

    public function exposeIntToRoman(int $num): string
    {
        return $this->intToRoman($num);
    }

    public function exposeUnhtmlentities(string $text): string
    {
        return $this->unhtmlentities($text);
    }

    /** @phpstan-return array<string, string> */
    public function exposeGetCSSArrayFromHTML(string &$html): array
    {
        return $this->getCSSArrayFromHTML($html);
    }

    public function exposeGetCSSColor(string $color): string
    {
        return $this->getCSSColor($color);
    }
}
