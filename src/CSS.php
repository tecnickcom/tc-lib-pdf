<?php

declare(strict_types=1);

/**
 * CSS.php
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

use Com\Tecnick\Pdf\CSS\CascadeContext;
use Com\Tecnick\Pdf\CSS\ImportanceNormalizer;
use Com\Tecnick\Pdf\CSS\Specificity;

/**
 * Com\Tecnick\Pdf\CSS
 *
 * CSS PDF class
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TCellBound from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TRefUnitValues from \Com\Tecnick\Pdf\Base
 * @phpstan-type BorderStyle array{
 *     lineWidth: float,
 *     lineCap: string,
 *     lineJoin: string,
 *     miterLimit: float,
 *     dashArray: array<int>,
 *     dashPhase: float,
 *     lineColor: string,
 *     fillColor: string,
 *     cssBorderStyle?: string,
 * }
 * @phpstan-type BorderStyleOpt array{
 *     lineWidth?: float,
 *     lineCap?: string,
 *     lineJoin?: string,
 *     miterLimit?: float,
 *     dashArray?: array<int>,
 *     dashPhase?: float,
 *     lineColor?: string,
 *     fillColor?: string,
 *     cssBorderStyle?: string,
 * }
 *
 * @phpstan-type TCSSBorderSpacing array{
 *     'H': float,
 *     'V': float,
 * }
 *
 * @phpstan-type TCSSData array{
 *     'k': string,
 *     'c': string,
 *     's': string,
 * }
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class CSS extends \Com\Tecnick\Pdf\SVG
{
    /**
     * Default values for cell boundaries.
     *
     * @const TCSSBorderSpacing
     */
    protected const ZEROBORDERSPACE = [
        'H' => 0,
        'V' => 0,
    ];

    /**
     * Default CSS margin.
     *
     * @var TCellBound
     */
    protected array $defCSSCellMargin = self::ZEROCELLBOUND;

    /**
     * Default CSS padding.
     *
     * @var TCellBound
     */
    protected array $defCSSCellPadding = self::ZEROCELLBOUND;

    /**
     * Default CSS border space.
     *
     * @var TCSSBorderSpacing
     */
    protected array $defCSSBorderSpacing = self::ZEROBORDERSPACE;

    /**
     * Maximum value that can be represented in Roman notation.
     *
     * @var int
     */
    protected const ROMAN_LIMIT = 3_999_999_999;

    /**
     * Maps Roman Vinculum symbols to number multipliers.
     *
     * @var array<string, int>
     */
    protected const ROMAN_VINCULUM = [
        '\u{033F}' => 1_000_000,
        '\u{0305}' => 1_000,
        '' => 1,
    ];

    /**
     * Maps Roman symbols to numbers.
     *
     * @var array<string, int>
     */
    protected const ROMAN_SYMBOL = [
        // standard notation
        'M' => 1_000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
    ];

    /**
     * Non-print CSS media types.
     *
     * @var list<string>
     */
    protected const NON_PRINT_MEDIA_TYPES = [
        'screen',
        'aural',
        'braille',
        'embossed',
        'handheld',
        'projection',
        'speech',
        'tty',
        'tv',
    ];

    /**
     * Set the default CSS margin in user units.
     *
     * @param float $top    Top.
     * @param float $right  Right.
     * @param float $bottom Bottom.
     * @param float $left   Left.
     */
    public function setDefaultCSSMargin(float $top, float $right, float $bottom, float $left): void
    {
        $this->defCSSCellMargin['T'] = $this->toPoints($top);
        $this->defCSSCellMargin['R'] = $this->toPoints($right);
        $this->defCSSCellMargin['B'] = $this->toPoints($bottom);
        $this->defCSSCellMargin['L'] = $this->toPoints($left);
    }

    /**
     * Set the default CSS padding in user units.
     *
     * @param float $top    Top.
     * @param float $right  Right.
     * @param float $bottom Bottom.
     * @param float $left   Left.
     */
    public function setDefaultCSSPadding(float $top, float $right, float $bottom, float $left): void
    {
        $this->defCSSCellPadding['T'] = $this->toPoints($top);
        $this->defCSSCellPadding['R'] = $this->toPoints($right);
        $this->defCSSCellPadding['B'] = $this->toPoints($bottom);
        $this->defCSSCellPadding['L'] = $this->toPoints($left);
    }

    /**
     * Set the default CSS border spacing in user units.
     *
     * @param float $horiz Horizontal space.
     * @param float $vert Vertical space.
     */
    public function setDefaultCSSBorderSpacing(float $vert, float $horiz): void
    {
        $this->defCSSBorderSpacing['V'] = $this->toPoints($vert);
        $this->defCSSBorderSpacing['H'] = $this->toPoints($horiz);
    }

    /**
     * Returns the border width from CSS property.
     *
     * @param string $width border width.
     *
     * @return float width in internal points.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     */
    protected function getCSSBorderWidthPoints(string $width): float
    {
        return match ($width) {
            '' => 0.0,
            'thin' => 2.0,
            'medium' => 4.0,
            'thick' => 6.0,
            default => $this->getUnitValuePoints($width),
        };
    }

    /**
     * Returns the border width from CSS property.
     *
     * @param string $width border width.
     *
     * @return float width in user units.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     */
    protected function getCSSBorderWidth(string $width): float
    {
        return $this->toUnit($this->getCSSBorderWidthPoints($width));
    }

    /**
     * Returns the border dash style from CSS property.
     *
     * @param string $style Border style to convert.
     *
     * @return int Border dash style (return -1 in case of none or hidden border).
     */
    protected function getCSSBorderDashStyle(string $style): int
    {
        return match (\strtolower($style)) {
            'none' => -1,
            'hidden' => -1,
            'dotted' => 1,
            'dashed' => 3,
            'double' => 0,
            'groove' => 0,
            'ridge' => 0,
            'inset' => 0,
            'outset' => 0,
            'solid' => 0,
            default => 0,
        };
    }

    /**
     * Apply a CSS border-style keyword to a border style array.
     *
     * @param BorderStyle|BorderStyleOpt $border Border style array.
     * @param string $style CSS border-style keyword.
     *
     * @return BorderStyle
     */
    protected function applyCSSBorderStyleKeyword(array $border, string $style): array
    {
        /** @var BorderStyle $border */
        $border = \array_replace($this->getCSSDefaultBorderStyle(), $border);
        $style = \strtolower(\trim($style));
        $border['lineCap'] = 'square';
        $border['lineJoin'] = 'miter';
        $border['cssBorderStyle'] = $style;

        $dash = $this->getCSSBorderDashStyle($style);
        if ($dash < 0) {
            $border['dashArray'] = [];
            $border['dashPhase'] = 0;
            $border['lineWidth'] = 0;
            return $border;
        }

        $border['dashArray'] = $dash > 0 ? [$dash, $dash] : [];
        $border['dashPhase'] = $dash;

        return $border;
    }

    /**
     * Returns the default CSS borer style.
     *
     * @return BorderStyle
     */
    protected function getCSSDefaultBorderStyle(): array
    {
        return [
            'lineWidth' => 0,
            'lineCap' => 'square',
            'lineJoin' => 'miter',
            'miterLimit' => $this->toUnit(10.0),
            'dashArray' => [],
            'dashPhase' => 0,
            'lineColor' => 'black',
            'fillColor' => '',
        ];
    }

    /**
     * Returns the border style array from CSS border properties.
     *
     * @param string $cssborder border properties.
     *
     * @return BorderStyle border properties.
     *
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\Pdf\Exception
     */
    protected function getCSSBorderStyle(string $cssborder): array
    {
        $border = $this->getCSSDefaultBorderStyle();
        $bpropSplit = \preg_split('/[\s]+/', \trim($cssborder));
        $bprop = $bpropSplit === false ? [] : $bpropSplit;

        $count = \count($bprop);
        $lastBprop = $bprop[$count - 1] ?? '';
        if ($count > 0 && $lastBprop === '!important') {
            unset($bprop[$count - 1]);
            $bprop = \array_values($bprop);
            --$count;
        }
        switch ($count) {
            case 2:
                $width = 'medium';
                $style = $bprop[0] ?? '';
                $color = $bprop[1] ?? '';
                break;
            case 1:
                $width = 'medium';
                $style = $bprop[0] ?? '';
                $color = 'black';
                break;
            case 0:
                $width = 'medium';
                $style = 'solid';
                $color = 'black';
                break;
            default:
                $width = $bprop[0] ?? '';
                $style = $bprop[1] ?? '';
                $color = $bprop[2] ?? '';
                break;
        }
        $border = $this->applyCSSBorderStyleKeyword($border, $style);
        if (($border['cssBorderStyle'] ?? '') === 'none' || ($border['cssBorderStyle'] ?? '') === 'hidden') {
            return $border;
        }
        $border['lineWidth'] = $this->getCSSBorderWidth($width);
        $colobj = $this->color->getColorObj($color);
        $border['lineColor'] = $colobj === null ? 'black' : $colobj->getCssColor();
        return $border;
    }

    /**
     * Get the internal Cell padding from CSS attribute.
     *
     * @param string $csspadding padding properties.
     * @param float $width width of the containing element.
     *
     * @return TCellBound cell paddings.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    protected function getCSSPadding(string $csspadding, float $width = 0.0): array
    {
        $cellpad = $this->defCSSCellPadding;
        $padSplit = \preg_split('/[\s]+/', \trim($csspadding));
        $pad = $padSplit === false ? [] : $padSplit;

        switch (\count($pad)) {
            case 4:
                $cellpad['T'] = $pad[0] ?? '';
                $cellpad['R'] = $pad[1] ?? '';
                $cellpad['B'] = $pad[2] ?? '';
                $cellpad['L'] = $pad[3] ?? '';
                break;
            case 3:
                $cellpad['T'] = $pad[0] ?? '';
                $cellpad['R'] = $pad[1] ?? '';
                $cellpad['B'] = $pad[2] ?? '';
                $cellpad['L'] = $pad[1] ?? '';
                break;
            case 2:
                $cellpad['T'] = $pad[0] ?? '';
                $cellpad['R'] = $pad[1] ?? '';
                $cellpad['B'] = $pad[0] ?? '';
                $cellpad['L'] = $pad[1] ?? '';
                break;
            case 1:
                $cellpad['T'] = $pad[0] ?? '';
                $cellpad['R'] = $pad[0] ?? '';
                $cellpad['B'] = $pad[0] ?? '';
                $cellpad['L'] = $pad[0] ?? '';
                break;
            default:
                return $cellpad;
        }
        if ($width <= 0) {
            $region = $this->page->getRegion();
            $width = $region['RW'];
        }
        $ref = self::REFUNITVAL;
        $ref['parent'] = $width;
        $cellpad['T'] = $this->toUnit($this->getUnitValuePoints($cellpad['T'], $ref));
        $cellpad['R'] = $this->toUnit($this->getUnitValuePoints($cellpad['R'], $ref));
        $cellpad['B'] = $this->toUnit($this->getUnitValuePoints($cellpad['B'], $ref));
        $cellpad['L'] = $this->toUnit($this->getUnitValuePoints($cellpad['L'], $ref));
        return $cellpad;
    }

    /**
     * Get the internal Cell margin from CSS attribute.
     *
     * @param string $cssmargin margin properties.
     * @param float $width width of the containing element.
     *
     * @return TCellBound cell margins.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    protected function getCSSMargin(string $cssmargin, float $width = 0.0): array
    {
        $cellmrg = $this->defCSSCellMargin;
        $mrgSplit = \preg_split('/[\s]+/', \trim($cssmargin));
        $mrg = $mrgSplit === false ? [] : $mrgSplit;

        switch (\count($mrg)) {
            case 4:
                $cellmrg['T'] = $mrg[0] ?? '';
                $cellmrg['R'] = $mrg[1] ?? '';
                $cellmrg['B'] = $mrg[2] ?? '';
                $cellmrg['L'] = $mrg[3] ?? '';
                break;
            case 3:
                $cellmrg['T'] = $mrg[0] ?? '';
                $cellmrg['R'] = $mrg[1] ?? '';
                $cellmrg['B'] = $mrg[2] ?? '';
                $cellmrg['L'] = $mrg[1] ?? '';
                break;
            case 2:
                $cellmrg['T'] = $mrg[0] ?? '';
                $cellmrg['R'] = $mrg[1] ?? '';
                $cellmrg['B'] = $mrg[0] ?? '';
                $cellmrg['L'] = $mrg[1] ?? '';
                break;
            case 1:
                $cellmrg['T'] = $mrg[0] ?? '';
                $cellmrg['R'] = $mrg[0] ?? '';
                $cellmrg['B'] = $mrg[0] ?? '';
                $cellmrg['L'] = $mrg[0] ?? '';
                break;
            default:
                return $cellmrg;
        }
        if ($width <= 0) {
            $region = $this->page->getRegion();
            $width = $region['RW'];
        }
        $cellmrg['T'] = \str_replace('auto', '0', $cellmrg['T']);
        $cellmrg['R'] = \str_replace('auto', '0', $cellmrg['R']);
        $cellmrg['B'] = \str_replace('auto', '0', $cellmrg['B']);
        $cellmrg['L'] = \str_replace('auto', '0', $cellmrg['L']);
        $ref = self::REFUNITVAL;
        $ref['parent'] = $width;
        $cellmrg['T'] = $this->toUnit($this->getUnitValuePoints($cellmrg['T'], $ref));
        $cellmrg['R'] = $this->toUnit($this->getUnitValuePoints($cellmrg['R'], $ref));
        $cellmrg['B'] = $this->toUnit($this->getUnitValuePoints($cellmrg['B'], $ref));
        $cellmrg['L'] = $this->toUnit($this->getUnitValuePoints($cellmrg['L'], $ref));
        return $cellmrg;
    }

    /**
     * Get the border-spacing from CSS attribute.
     *
     * @param string $cssbspace border-spacing CSS properties.
     * @param float $width width of the containing element.
     *
     * @return TCSSBorderSpacing of border spacings.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    protected function getCSSBorderMargin(string $cssbspace, float $width = 0.0): array
    {
        $bsp = $this->defCSSBorderSpacing;
        $spaceSplit = \preg_split('/[\s]+/', \trim($cssbspace));
        $space = $spaceSplit === false ? [] : $spaceSplit;

        switch (\count($space)) {
            case 2:
                $bsp['H'] = $space[0] ?? '';
                $bsp['V'] = $space[1] ?? '';
                break;
            case 1:
                $bsp['H'] = $space[0] ?? '';
                $bsp['V'] = $space[0] ?? '';
                break;
            default:
                return $bsp;
        }
        if ($width <= 0) {
            $region = $this->page->getRegion();
            $width = $region['RW'];
        }
        $ref = self::REFUNITVAL;
        $ref['parent'] = $width;
        $bsp['H'] = $this->toUnit($this->getUnitValuePoints($bsp['H'], $ref));
        $bsp['V'] = $this->toUnit($this->getUnitValuePoints($bsp['V'], $ref));
        return $bsp;
    }

    /**
     * Implode CSS data array into a single string.
     *
     * @param array<string, TCSSData> $css array of CSS properties.
     *
     * @return string merged CSS properties.
     */
    protected function implodeCSSData(array $css): string
    {
        /** @var array<string, array{name: string, value: string, important: bool}> $decls */
        $decls = [];
        /** @var array<string, bool> $importantLonghands */
        $importantLonghands = [];
        /** @var list<string> $order */
        $order = [];

        foreach ($css as $style) {
            if ($style['c'] === '') {
                continue;
            }
            $csscmds = $this->splitCSSDeclarations($style['c']);
            foreach ($csscmds as $cmd) {
                $cmd = \trim($cmd);
                if ($cmd === '') {
                    continue;
                }
                $pos = \strpos($cmd, ':');
                if ($pos === false) {
                    continue;
                }

                $name = \trim(\substr($cmd, 0, $pos));
                if ($name === '') {
                    continue;
                }

                $value = \trim(\substr($cmd, $pos + 1));
                $important = \preg_match('/!\s*important\s*$/i', $value) === 1;
                if ($important) {
                    $value = \trim(\preg_replace('/!\s*important\s*$/i', '', $value) ?? $value);
                }

                $key = \strtolower($name);
                if (!$important && isset($importantLonghands[$key])) {
                    continue;
                }

                if (!isset($decls[$key])) {
                    $order[] = $key;
                    $decls[$key] = [
                        'name' => $name,
                        'value' => $value,
                        'important' => $important,
                    ];
                    if ($important) {
                        foreach (ImportanceNormalizer::getAffectedLonghands($key) as $affectedKey) {
                            $importantLonghands[$affectedKey] = true;
                        }
                    }
                    continue;
                }

                $decl = $decls[$key] ?? ['name' => '', 'value' => '', 'important' => false];
                if ($decl['important'] && !$important) {
                    // Existing !important declaration wins over later non-important declaration.
                    continue;
                }

                // Keep declaration order aligned with the latest winning occurrence.
                // This preserves shorthand/longhand cascade behavior across merged rules.
                $orderIdx = \array_search($key, $order, true);
                if ($orderIdx !== false) {
                    unset($order[$orderIdx]);
                }
                $order[] = $key;

                $decls[$key] = [
                    'name' => $name,
                    'value' => $value,
                    'important' => $important,
                ];

                if ($important) {
                    foreach (ImportanceNormalizer::getAffectedLonghands($key) as $affectedKey) {
                        $importantLonghands[$affectedKey] = true;
                    }
                }
            }
        }

        $out = '';
        foreach ($order as $key) {
            $decl = $decls[$key] ?? null;
            if ($decl === null) {
                continue;
            }

            $out .= $decl['name'] . ':' . $decl['value'];
            if ($decl['important']) {
                $out .= '!important';
            }
            $out .= ';';
        }

        return $out;
    }

    /**
     * Split a CSS declaration list on semicolons while preserving quoted strings
     * and parenthesized expressions (for example data URIs inside url(...)).
     *
     * @return list<string>
     */
    protected function splitCSSDeclarations(string $style): array
    {
        $out = [];
        $decl = '';
        $quote = '';
        $parenDepth = 0;
        $slen = \strlen($style);

        for ($idx = 0; $idx < $slen; ++$idx) {
            $chr = $style[$idx];

            if ($quote !== '') {
                $decl .= $chr;
                if ($chr === $quote && ($idx === 0 || $style[$idx - 1] !== '\\')) {
                    $quote = '';
                }

                continue;
            }

            if ($chr === '"' || $chr === "'") {
                $quote = $chr;
                $decl .= $chr;
                continue;
            }

            if ($chr === '(') {
                ++$parenDepth;
                $decl .= $chr;
                continue;
            }

            if ($chr === ')') {
                $parenDepth = \max(0, $parenDepth - 1);
                $decl .= $chr;
                continue;
            }

            if ($chr === ';' && $parenDepth === 0) {
                $out[] = $decl;
                $decl = '';
                continue;
            }

            $decl .= $chr;
        }

        if ($decl !== '') {
            $out[] = $decl;
        }

        return $out;
    }

    /**
     * Tidy up the CSS string by removing unsupported properties.
     *
     * @param string $css string containing CSS definitions.
     *
     * @return string
     */
    /**
     * Maximum recursion depth for @import resolution.
     */
    private const CSS_IMPORT_MAX_DEPTH = 8;

    /**
     * Strip @charset declaration from a CSS string and transcode to UTF-8 if needed.
     *
     * The CSS spec requires @charset (if present) to be the very first rule.
     * The declaration is always stripped since this library processes CSS as UTF-8 strings.
     * When the declared charset is not UTF-8 or ASCII, the content is transcoded
     * using mb_convert_encoding so that subsequent regex operations work correctly.
     *
     * @param string $css Raw CSS string, possibly starting with @charset.
     * @return string CSS string with @charset removed and content in UTF-8.
     */
    protected function normalizeCharset(string $css): string
    {
        if (!\str_contains($css, '@charset')) {
            return $css;
        }
        // @charset must be the first rule; allow leading whitespace/BOM
        $charsetMatch = [];
        if (\preg_match('/^(?:\xEF\xBB\xBF)?[\s]*@charset\s+"([^"]+)"\s*;/i', $css, $charsetMatch)) {
            $declared = \strtolower(\trim($charsetMatch[1] ?? ''));
            // Strip the @charset declaration (and any leading BOM)
            $css = (string) \preg_replace('/^(?:\xEF\xBB\xBF)?[\s]*@charset\s+"[^"]+"\s*;/i', '', $css);
            // Transcode to UTF-8 if charset is not already UTF-8 or ASCII
            if ($declared !== 'utf-8' && $declared !== 'utf8' && $declared !== 'us-ascii' && $declared !== 'ascii') {
                $converted = \mb_convert_encoding($css, 'UTF-8', $declared);
                if ($converted !== false) {
                    $css = $converted;
                }
            }
        }
        return $css;
    }

    /**
     * Extract and resolve @import rules from a CSS string.
     *
     * Fetches each imported file (local or remote, subject to the same security
     * policy as external stylesheets), recursively resolves its own @imports up
     * to CSS_IMPORT_MAX_DEPTH levels, and returns the imported content
     * prepended before the remaining CSS so that cascade source order is correct.
     *
     * @param string            $css   Raw CSS string that may contain @import rules.
     * @param int               $depth Current recursion depth (starts at 0).
     * @param array<string,bool> &$seen URLs already resolved in this import chain
     *                                 (prevents infinite loops and duplicate imports).
     *
     * @return string CSS string with @import rules replaced by fetched content.
     *
     * @throws \Com\Tecnick\File\Exception
     */
    protected function resolveImportRules(string $css, int $depth = 0, array &$seen = []): string
    {
        if ($depth >= self::CSS_IMPORT_MAX_DEPTH) {
            return $css;
        }

        // Match @import "url" [media]; or @import url('url') [media];
        // Capture: (1) url  (2) optional media list
        $pattern = '/@import\s+(?:url\([\'"]?([^\'")\s]+)[\'"]?\)|[\'"]([^\'"]+)[\'"])\s*([^;]*);/i';
        $imports = [];
        $matches = [];
        $matchCount = \preg_match_all($pattern, $css, $matches, \PREG_SET_ORDER);
        if ($matchCount !== false && $matchCount > 0) {
            foreach ($matches as $match) {
                $matchUrl = $match[1] ?? '';
                $url = $matchUrl !== '' ? \trim($matchUrl) : \trim($match[2] ?? '');
                $media = \strtolower(\trim($match[3] ?? ''));
                // Skip non-print media
                if ($media !== '' && $media !== 'all' && $media !== 'print') {
                    continue;
                }
                if ($url === '' || isset($seen[$url])) {
                    continue;
                }
                $seen[$url] = true;
                $imported = $this->file->getFileData($url);
                if ($imported === false || $imported === '') {
                    continue;
                }
                $imported = $this->normalizeCharset($imported);
                // Recursively resolve imports in the fetched file
                $imports[] = $this->resolveImportRules($imported, $depth + 1, $seen);
            }
        }
        // Strip all @import rules from the current CSS regardless of media
        $css = (string) \preg_replace('/@import\s+[^;]+;/i', '', $css);
        if ($imports === []) {
            return $css;
        }
        // Imported content comes first (CSS source order: imports precede the importing sheet)
        return \implode("\n", $imports) . "\n" . $css;
    }

    /**
     * Determines whether a CSS @media query condition applies to print output.
     *
     * Handles comma-separated media lists, complex conditions with 'and',
     * negated conditions with 'not', and feature-only queries (no explicit media type).
     *
     * @param string $query The @media query string (content between @media and {).
     * @return bool True if the query applies to print; false otherwise.
     */
    protected function isMediaPrintRelevant(string $query): bool
    {
        foreach (\explode(',', $query) as $condition) {
            $condition = \strtolower(\trim($condition));
            if ($condition === '') {
                continue;
            }
            // Check for 'not' negation prefix
            $negated = false;
            if (\str_starts_with($condition, 'not ') || $condition === 'not') {
                $negated = true;
                $condition = \ltrim(\substr($condition, 3));
            }
            // Extract media type: everything before the first 'and' keyword or feature '('
            $parts = \preg_split('/\band\b/', $condition, 2);
            $mediaType = \trim($parts[0] ?? '');
            // Feature-only query (starts with '(') — no media type means 'all'
            if ($mediaType === '' || $mediaType[0] === '(') {
                if (!$negated) {
                    return true;
                }
                continue;
            }
            $isPrintOrAll = $mediaType === 'print' || $mediaType === 'all';
            if ($negated) {
                // 'not print' / 'not all' → does not apply to print
                // 'not screen' / 'not tv' → applies to all non-screen, including print
                if (!$isPrintOrAll && \in_array($mediaType, self::NON_PRINT_MEDIA_TYPES, true)) {
                    return true;
                }
            } else {
                if ($isPrintOrAll) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function tidyCSS(string $css): string
    {
        if ($css === '') {
            return '';
        }
        // remove comments
        $css = \preg_replace('/\/\*[^\*]*\*\//', '', $css) ?? '';
        // remove newlines and multiple spaces
        $css = \preg_replace('/[\s]+/', ' ', $css) ?? '';
        // remove some spaces
        $css = \preg_replace('/[\s]*([;:\{\}]{1})[\s]*/', '\\1', $css) ?? '';
        // remove empty blocks
        $css = \preg_replace('/([^\}\{]+)\{\}/', '', $css) ?? '';
        // replace media type parenthesis
        $css = \preg_replace('/@media[\s]+([^\{]*)\{/i', '@media \\1§', $css) ?? '';
        $css = \preg_replace('/\}\}/si', '}§', $css) ?? '';
        // find media blocks (all, braille, embossed, handheld, print, projection, screen, speech, tty, tv)
        $blk = [];
        $matches = [];
        $matchCount = \preg_match_all('/@media[\s]+([^\§]*)§([^§]*)§/i', $css, $matches);
        if ($matchCount !== false && $matchCount > 0) {
            $mediaTypes = $matches[1] ?? [];
            $mediaContent = $matches[2] ?? [];
            foreach ($mediaTypes as $key => $type) {
                $content = $mediaContent[$key] ?? '';
                if ($content === '') {
                    continue;
                }

                $blk[$type] = $content;
            }
            // remove media blocks
            $css = \preg_replace('/@media[\s]+([^\§]*)§([^§]*)§/i', '', $css) ?? '';
        }
        // keep print-relevant media blocks; discard screen-only and other non-print types
        foreach ($blk as $type => $content) {
            if (!$this->isMediaPrintRelevant($type)) {
                continue;
            }

            $css .= $content;
        }
        return \trim($css);
    }

    /**
     * Extracts the CSS properties from a CSS string.
     *
     * @param string $css string containing CSS definitions.
     *
     * @return array<string, string> CSS properties.
     */
    /**
     * Extract CSS properties from a CSS data string and return as a sorted array.
     *
     * When CascadeContext is provided, maintains global source order across all CSS sources
     * for deterministic cascade behavior. When null, falls back to per-stylesheet source order.
     *
     * @param string $css CSS data string
     * @param CascadeContext|null $context Optional cascade context for global source order tracking
     * @return array<string, string> Array of parsed CSS selectors with properties
     *
     * @throws \Com\Tecnick\File\Exception
     */
    protected function extractCSSproperties(string $css, ?CascadeContext $context = null): array
    {
        $css = $this->normalizeCharset($css);
        $seen = [];
        $css = $this->resolveImportRules($css, 0, $seen);
        $css = $this->tidyCSS($css);
        if ($css === '') {
            return [];
        }
        $blk = [];
        $matches = [];
        // explode css data string into array
        if (\substr($css, -1) === '}') {
            // remove last parethesis
            $css = \substr($css, 0, -1);
        }
        $matches = \explode('}', $css);
        foreach ($matches as $key => $block) {
            // index 0 contains the CSS selector, index 1 contains CSS properties
            $blk[$key] = \explode('{', $block);
            if (!isset($blk[$key][1])) {
                // remove empty definitions
                unset($blk[$key]);
            }
        }
        // split groups of selectors (comma-separated list of selectors)
        foreach ($blk as $key => $block) {
            $selectorList = $block[0];
            $commaPos = \strpos($selectorList, ',');
            if ($commaPos === false || $commaPos <= 0) {
                continue;
            }

            $selectors = \explode(',', $selectorList);
            $blockProperties = $block[1] ?? '';
            foreach ($selectors as $sel) {
                $blk[] = [0 => \trim($sel), 1 => $blockProperties];
            }
            unset($blk[$key]);
        }
        // covert array to selector => properties
        $out = [];
        $sourceOrder = 0;
        foreach ($blk as $block) {
            $selector = $block[0];
            // calculate selector's specificity using CSS 2.1 tuple scoring
            $specificity = Specificity::fromSelector($selector);
            // Get global or per-stylesheet source order
            if ($context !== null) {
                $sourceOrder = $context->getNextNormalSourceOrder();
            } else {
                ++$sourceOrder;
            }
            // create sortable key: "{a:04d}{b:04d}{c:04d}_{index:06d} {selector}"
            $sortKey = $specificity->toSortKey($sourceOrder);
            // store with specificity key for proper ordering
            $out["{$sortKey} {$selector}"] = $block[1] ?? '';
        }
        // sort selectors alphabetically to account for specificity and source order
        \ksort($out, SORT_STRING);
        return $out;
    }

    /**
     * Returns the Roman representation of an integer number.
     * Roman standard notation can represent numbers up to 3,999.
     * For bigger numbers, up to two layers of the "vinculum" notation
     * are used for a max value of 3,999,999,999.
     *
     * @param int $num number to convert.
     *
     * @return string roman representation of the specified number.
     */
    protected function intToRoman(int $num): string
    {
        if ($num > self::ROMAN_LIMIT) {
            return \strval($num);
        }
        $rmn = '';
        foreach (self::ROMAN_VINCULUM as $sfx => $mul) {
            foreach (self::ROMAN_SYMBOL as $sym => $val) {
                $limit = (int) ($mul * $val);
                while ($num >= $limit) {
                    $rmn .= $sym[0] . $sfx . (\strlen($sym) > 1 ? $sym[1] . $sfx : '');
                    $num -= $limit;
                }
            }
        }
        while ($num >= 1) {
            $rmn .= 'I';
            $num--;
        }
        return $rmn;
    }

    /**
     * Reverse function for htmlentities.
     *
     * @param string $text_to_convert Text to convert.
     *
     * @return string converted text string
     */
    protected function unhtmlentities(string $text_to_convert): string
    {
        return \html_entity_decode($text_to_convert, ENT_QUOTES, $this->encoding);
    }

    /**
     * Decode a serialized CSS map and keep only string selector/value pairs.
     *
     * @return array<string, string>
     */
    protected function decodeCSSMap(string $payload): array
    {
        /** @var mixed $decoded */
        $decoded = \json_decode($this->unhtmlentities($payload), true);
        if (!\is_array($decoded)) {
            return [];
        }

        $css = [];
        foreach (\array_keys($decoded) as $selector) {
            if (!\is_string($selector)) {
                continue;
            }

            if (!\is_string($decoded[$selector] ?? null)) {
                continue;
            }

            $properties = $decoded[$selector];
            $css[$selector] = $properties;
        }

        return $css;
    }

    /**
     * Extract CSS styles from an HTML string.
     *
     * @param string $html HTML string to parse.
     *
     * @return array<string, string> CSS styles (selector => properties).
     *
     * @throws \Com\Tecnick\File\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getCSSArrayFromHTML(string &$html): array
    {
        /** @var array<string, string> $css */
        $css = [];

        // Create cascade context to track global source order across all CSS sources
        $cascadeCtx = new CascadeContext();

        $matches = [];
        $matchCount = \preg_match_all('/<cssarray>([^\<]*?)<\/cssarray>/is', $html, $matches);
        if ($matchCount !== false && $matchCount > 0) {
            $cssArrayPayload = $matches[1][0] ?? null;
            if (\is_string($cssArrayPayload)) {
                $css = \array_merge($css, $this->decodeCSSMap($cssArrayPayload));
            }
            $html = \preg_replace('/<cssarray>(.*?)<\/cssarray>/is', '', $html) ?? '';
        }

        // extract external CSS files
        $matches = [];
        $matchCount = \preg_match_all('/<link([^\>]*?)>/is', $html, $matches);
        if ($matchCount !== false && $matchCount > 0) {
            $cascadeCtx->setCurrentSourceType('external');
            $linkTags = $matches[1] ?? [];
            foreach ($linkTags as $link) {
                $type = [];
                if (\preg_match('/type[\s]*=[\s]*"text\/css"/', $link, $type) === 1) {
                    $type = [];
                    if (\preg_match('/media[\s]*=[\s]*"([^"]*)"/', $link, $type) === 1) {
                        // get 'all' and 'print' media, other media types are discarded
                        // (all, braille, embossed, handheld, print, projection, screen, speech, tty, tv)
                        if (isset($type[1]) && $type[1] !== '' && ($type[1] === 'all' || $type[1] === 'print')) {
                            $type = [];
                            if (\preg_match('/href[\s]*=[\s]*"([^"]*)"/', $link, $type) === 1) {
                                // read CSS data file
                                $cssdata = $this->file->getFileData(\trim($type[1] ?? ''));
                                if ($cssdata !== false && \strlen($cssdata) > 0) {
                                    $css = \array_merge($css, $this->extractCSSproperties($cssdata, $cascadeCtx));
                                }
                            }
                        }
                    } else {
                        // no media attribute defaults to "all"
                        $type = [];
                        if (\preg_match('/href[\s]*=[\s]*"([^"]*)"/', $link, $type) === 1) {
                            $cssdata = $this->file->getFileData(\trim($type[1] ?? ''));
                            if ($cssdata !== false && \strlen($cssdata) > 0) {
                                $css = \array_merge($css, $this->extractCSSproperties($cssdata, $cascadeCtx));
                            }
                        }
                    }
                }
            }
        }

        // extract style tags
        $matches = [];
        $matchCount = \preg_match_all('/<style([^\>]*?)>([^\<]*?)<\/style>/is', $html, $matches);
        if ($matchCount !== false && $matchCount > 0) {
            $cascadeCtx->setCurrentSourceType('embedded');
            $styleMediaDefs = $matches[1] ?? [];
            $styleBodies = $matches[2] ?? [];
            foreach ($styleMediaDefs as $key => $media) {
                $type = [];
                if (\preg_match('/media[\s]*=[\s]*"([^"]*)"/', $media, $type) === 1) {
                    // get 'all' and 'print' media, other media types are discarded
                    // (all, braille, embossed, handheld, print, projection, screen, speech, tty, tv)
                    if (isset($type[1]) && $type[1] !== '' && ($type[1] === 'all' || $type[1] === 'print')) {
                        $cssdata = $styleBodies[$key] ?? '';
                        $css = \array_merge($css, $this->extractCSSproperties($cssdata, $cascadeCtx));
                    }
                } else {
                    // no media attribute defaults to "all"
                    $cssdata = $styleBodies[$key] ?? '';
                    $css = \array_merge($css, $this->extractCSSproperties($cssdata, $cascadeCtx));
                }
            }
        }

        return $css;
    }

    /**
     * Parse and normalize CSS color.
     *
     * @param string $color CSS color string to parse.
     *
     * @return string CSS color representation.
     *
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getCSSColor(string $color): string
    {
        $colobj = $this->color->getColorObj($color);
        if ($colobj === null) {
            return '';
        }
        return $colobj->getCssColor();
    }
}
