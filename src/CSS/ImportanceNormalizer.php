<?php

declare(strict_types=1);

/**
 * ImportanceNormalizer.php
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

namespace Com\Tecnick\Pdf\CSS;

/**
 * Normalizes CSS declarations to ensure !important is properly propagated
 * to all longhands when shorthand properties are used.
 *
 * @since     2026-05-08
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class ImportanceNormalizer
{
    /**
     * Maps CSS shorthand properties to their longhands.
     *
     * @var array<string, array<string>>
     */
    private const SHORTHAND_EXPANSIONS = [
        'border' => [
            'border-width',
            'border-style',
            'border-color',
        ],
        'border-width' => [
            'border-top-width',
            'border-right-width',
            'border-bottom-width',
            'border-left-width',
        ],
        'border-style' => [
            'border-top-style',
            'border-right-style',
            'border-bottom-style',
            'border-left-style',
        ],
        'border-color' => [
            'border-top-color',
            'border-right-color',
            'border-bottom-color',
            'border-left-color',
        ],
        'margin' => [
            'margin-top',
            'margin-right',
            'margin-bottom',
            'margin-left',
        ],
        'padding' => [
            'padding-top',
            'padding-right',
            'padding-bottom',
            'padding-left',
        ],
    ];

    /**
     * Return all longhand properties affected by a shorthand, including nested shorthands.
     *
     * @return list<string>
     */
    public static function getAffectedLonghands(string $property): array
    {
        $affected = [];
        self::collectAffectedLonghands($affected, $property, []);

        return \array_values(\array_unique($affected));
    }

    /**
     * @param array<int, string> $affected
     * @param array<string, bool> $visited
     */
    private static function collectAffectedLonghands(array &$affected, string $property, array $visited): void
    {
        if (isset($visited[$property]) || !isset(self::SHORTHAND_EXPANSIONS[$property])) {
            return;
        }

        $visited[$property] = true;
        foreach (self::SHORTHAND_EXPANSIONS[$property] as $longhand) {
            $affected[] = $longhand;
            self::collectAffectedLonghands($affected, $longhand, $visited);
        }
    }

    /**
     * Normalize a CSS declaration list to ensure !important is properly applied to all longhands.
     *
     * When a shorthand property has !important, all its longhands should inherit !important.
     * This ensures consistent precedence across the cascade.
     *
     * @param string $declarations CSS declaration string (e.g., "color: red; border: 1px solid !important;")
     * @return string Normalized CSS declaration string with !important propagated to longhands
     */
    public static function normalize(string $declarations): string
    {
        if ($declarations === '') {
            return '';
        }

        // Parse declarations into property => (value, important flag)
        $parsed = self::parseDeclarations($declarations);

        // Expand shorthands, propagating !important to longhands
        $expanded = self::expandShorthands($parsed);

        // Rebuild the declaration string
        return self::rebuildDeclarations($expanded);
    }

    /**
     * Parse CSS declarations into an associative array.
     *
     * @param string $declarations CSS declaration string
     * @return array<string, array{value: string, important: bool}>
     */
    private static function parseDeclarations(string $declarations): array
    {
        $result = [];
        $decls = \explode(';', $declarations);

        foreach ($decls as $decl) {
            $decl = \trim($decl);
            if ($decl === '') {
                continue;
            }

            $pos = \strpos($decl, ':');
            if ($pos === false) {
                continue;
            }

            $name = \strtolower(\trim(\substr($decl, 0, $pos)));
            if ($name === '') {
                continue;
            }

            $value = \trim(\substr($decl, $pos + 1));
            $important = \preg_match('/!\s*important\s*$/i', $value) === 1;
            if ($important) {
                $value = \trim(\preg_replace('/!\s*important\s*$/i', '', $value) ?? $value);
            }

            $result[$name] = [
                'value' => $value,
                'important' => $important,
            ];
        }

        return $result;
    }

    /**
     * Expand shorthand properties to include longhands for !important propagation.
     *
     * @param array<string, array{value: string, important: bool}> $parsed
     * @return array<string, array{'important': bool, 'value': string, '_from_shorthand'?: true}>
     */
    private static function expandShorthands(array $parsed): array
    {
        /** @var array<string, array{'important': bool, 'value': string, '_from_shorthand'?: true}> $result */
        $result = [];

        foreach ($parsed as $property => $declaration) {
            // If this is a shorthand property with !important,
            // mark its longhands for !important propagation
            if ($declaration['important']) {
                foreach (self::getAffectedLonghands($property) as $longhand) {
                    if (!isset($result[$longhand])) {
                        $result[$longhand] = [
                            'value' => '',
                            'important' => true,
                            '_from_shorthand' => true,
                        ];
                    } else {
                        $result[$longhand]['value'] ??= '';
                        $result[$longhand]['important'] = true;
                    }
                }
            }

            // Add the property (shorthand or regular) to result
            $result[$property] = $declaration;
        }

        return $result;
    }

    /**
     * Rebuild CSS declaration string from parsed declarations.
     *
     * @param array<string, array{'important': bool, 'value': string, '_from_shorthand'?: true}> $declarations
     * @return string
     */
    private static function rebuildDeclarations(array $declarations): string
    {
        $result = '';

        foreach ($declarations as $property => $declaration) {
            // Skip empty longhands created only for !important propagation
            if ($declaration['value'] === '' && isset($declaration['_from_shorthand'])) {
                continue;
            }

            $result .= $property . ':' . $declaration['value'];
            if ($declaration['important']) {
                $result .= '!important';
            }
            $result .= ';';
        }

        return $result;
    }
}
