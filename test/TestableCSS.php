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
    /** @throws \Throwable */
    public function exposeGetCSSBorderWidthPoints(string $width): float
    {
        return $this->getCSSBorderWidthPoints($width);
    }

    /** @throws \Throwable */
    public function exposeGetCSSBorderWidth(string $width): float
    {
        return $this->getCSSBorderWidth($width);
    }

    public function exposeGetCSSBorderDashStyle(string $style): int
    {
        return $this->getCSSBorderDashStyle($style);
    }

    /** @return StyleData */
    public function exposeGetCSSDefaultBorderStyle(): array
    {
        /** @var StyleData */
        return $this->getCSSDefaultBorderStyle();
    }

    /**
     * @return StyleData
     * @throws \Throwable
     */
    public function exposeGetCSSBorderStyle(string $cssborder): array
    {
        /** @var StyleData */
        return $this->getCSSBorderStyle($cssborder);
    }

    /**
     * @phpstan-return TCellBound
     * @throws \Throwable
     */
    public function exposeGetCSSPadding(string $csspadding, float $width = 0.0): array
    {
        return $this->getCSSPadding($csspadding, $width);
    }

    /**
     * @phpstan-return TCellBound
     * @throws \Throwable
     */
    public function exposeGetCSSMargin(string $cssmargin, float $width = 0.0): array
    {
        return $this->getCSSMargin($cssmargin, $width);
    }

    /**
     * @phpstan-return TCSSBorderSpacing
     * @throws \Throwable
     */
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

        return $this->implodeCSSData($normalized);
    }

    public function exposeTidyCSS(string $css): string
    {
        return $this->tidyCSS($css);
    }

    public function exposeNormalizeCharset(string $css): string
    {
        return $this->normalizeCharset($css);
    }

    public function exposeIsMediaPrintRelevant(string $query): bool
    {
        return $this->isMediaPrintRelevant($query);
    }

    /**
     * @param array<string, bool> $seen
     * @throws \Throwable
     */
    public function exposeResolveImportRules(string $css, int $depth = 0, array &$seen = []): string
    {
        return $this->resolveImportRules($css, $depth, $seen);
    }

    /**
     * @phpstan-return array<string, string>
     * @throws \Throwable
     */
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

    /**
     * @phpstan-return array<string, string>
     * @throws \Throwable
     */
    public function exposeGetCSSArrayFromHTML(string &$html): array
    {
        return $this->getCSSArrayFromHTML($html);
    }

    /** @throws \Throwable */
    public function exposeGetCSSColor(string $color): string
    {
        return $this->getCSSColor($color);
    }

    /** @return list<string> */
    public function exposeSplitCSSWhitespaceTokens(string $value): array
    {
        return $this->splitCSSWhitespaceTokens($value);
    }

    /** @return list<string> */
    public function exposeSplitCSSDeclarations(string $style): array
    {
        return $this->splitCSSDeclarations($style);
    }

    /** @return array<string, string> */
    public function exposeDecodeCSSMap(string $payload): array
    {
        return $this->decodeCSSMap($payload);
    }

    public function exposeStripAndRegisterCSSSpotRules(string $css): string
    {
        return $this->stripAndRegisterCSSSpotRules($css);
    }

    public function exposeParseSpotCssColorFunction(string $color): ?string
    {
        return $this->parseSpotCssColorFunction($color);
    }

    public function exposeParseSpotColorNameToken(#[\SensitiveParameter] string $token): string
    {
        return $this->parseSpotColorNameToken($token);
    }

    public function exposeFormatSpotColorNameToken(string $name): string
    {
        return $this->formatSpotColorNameToken($name);
    }

    /** @return array{0: float, 1: float, 2: float, 3: float}|null */
    public function exposeParseSpotComponentList(string $value): ?array
    {
        return $this->parseSpotComponentList($value);
    }

    /** @return array{0: float, 1: float, 2: float}|null */
    public function exposeParseLabComponentList(string $value): ?array
    {
        return $this->parseLabComponentList($value);
    }

    public function exposeParseSpotTintValue(string $value): ?float
    {
        return $this->parseSpotTintValue($value);
    }

    public function exposeParseLabLstarValue(string $value): ?float
    {
        return $this->parseLabLstarValue($value);
    }

    public function exposeParseLabAxisValue(string $value): ?float
    {
        return $this->parseLabAxisValue($value);
    }
}
