<?php

declare(strict_types=1);

/**
 * AsciiPattern.php
 *
 * @since     2026-11-20
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
 * Com\Tecnick\Pdf\AsciiPattern
 *
 * ASCII character sets and case-insensitive matching that do not depend on LC_CTYPE.
 *
 * @internal
 *
 * @since     2026-11-20
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
final class AsciiPattern
{
    /**
     * Regex class members matched by \s in the C locale.
     */
    public const SPACE = '\t\n\x0B\f\r ';

    /**
     * Regex class members matched by \w in the C locale.
     */
    public const WORD = '0-9A-Za-z_';

    /**
     * Regex class members for ASCII letters.
     */
    public const ALPHA = 'A-Za-z';

    /**
     * Regex class members for ASCII letters and digits.
     */
    public const ALNUM = '0-9A-Za-z';

    /**
     * Whether the string is one byte matched by \s in the C locale.
     */
    public static function isSpace(string $chr): bool
    {
        return \strlen($chr) === 1 && \str_contains(" \t\n\v\f\r", $chr);
    }

    /**
     * Whether the string is one ASCII lower-case letter.
     */
    public static function isLower(string $chr): bool
    {
        return \strlen($chr) === 1 && $chr >= 'a' && $chr <= 'z';
    }

    /**
     * ASCII case-insensitive preg_match().
     *
     * The pattern is matched against the ASCII lower-cased subject, so its letters must be lower-case.
     * Captures are read from the original subject.
     *
     * @param string $pattern Regular expression without the i modifier.
     * @param string $subject Input string.
     * @param array<int|string, string> $matches Captures, as set by preg_match() without flags.
     *
     * @param-out array<int|string, string> $matches
     *
     * @return int 1 on match, 0 otherwise.
     */
    public static function matchFolded(string $pattern, string $subject, array &$matches = []): int
    {
        $found = [];
        $matches = [];
        if (\preg_match($pattern, \strtolower($subject), $found, \PREG_OFFSET_CAPTURE) !== 1) {
            return 0;
        }

        foreach ($found as $key => $cap) {
            $matches[$key] = self::originalCapture($subject, $cap);
        }

        return 1;
    }

    /**
     * ASCII case-insensitive preg_match_all().
     *
     * The pattern is matched against the ASCII lower-cased subject, so its letters must be lower-case.
     * Captures are read from the original subject.
     *
     * @param string $pattern Regular expression without the i modifier.
     * @param string $subject Input string.
     * @param array<int|string, array<int|string, string>> $matches Captures, as set by preg_match_all().
     * @param int $order PREG_PATTERN_ORDER or PREG_SET_ORDER.
     *
     * @param-out array<int|string, array<int|string, string>> $matches
     *
     * @return int|false Number of matches, or false on regex error.
     */
    public static function matchAllFolded(
        string $pattern,
        string $subject,
        array &$matches = [],
        int $order = \PREG_PATTERN_ORDER,
    ): int|false {
        $found = [];
        $matches = [];
        $flags = ($order === \PREG_SET_ORDER ? \PREG_SET_ORDER : \PREG_PATTERN_ORDER) | \PREG_OFFSET_CAPTURE;
        $ret = \preg_match_all($pattern, \strtolower($subject), $found, $flags);
        if ($ret === false) {
            return false;
        }

        foreach ($found as $key => $group) {
            $matches[$key] = [];
            foreach ($group as $sub => $cap) {
                $matches[$key][$sub] = self::originalCapture($subject, $cap);
            }
        }

        return $ret;
    }

    /**
     * ASCII case-insensitive preg_replace_callback().
     *
     * The pattern is matched against the ASCII lower-cased subject, so its letters must be lower-case.
     * The callback receives captures read from the original subject, and the text outside the matches
     * keeps its original case.
     *
     * @param string $pattern Regular expression without the i modifier.
     * @param callable(array<int|string, string>): string $callback Replacement callback.
     * @param string $subject Input string.
     * @param int $limit Maximum number of replacements, or -1 for no limit.
     *
     * @return ?string Result, or null on regex error.
     */
    public static function replaceFolded(string $pattern, callable $callback, string $subject, int $limit = -1): ?string
    {
        $found = [];
        $ret = \preg_match_all($pattern, \strtolower($subject), $found, \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE);
        if ($ret === false) {
            return null;
        }

        $out = '';
        $pos = 0;
        $count = 0;
        foreach ($found as $set) {
            if ($limit >= 0 && $count >= $limit) {
                break;
            }

            $caps = [];
            foreach ($set as $key => $cap) {
                $caps[$key] = self::originalCapture($subject, $cap);
            }

            $start = (int) ($set[0][1] ?? 0);
            $out .= \substr($subject, $pos, $start - $pos) . $callback($caps);
            $pos = $start + \strlen($set[0][0] ?? '');
            ++$count;
        }

        return $out . \substr($subject, $pos);
    }

    /**
     * Text of the original subject at the offset and length of a capture from the lower-cased subject.
     *
     * @param mixed $cap Capture text and offset, as set by PREG_OFFSET_CAPTURE.
     */
    private static function originalCapture(string $subject, mixed $cap): string
    {
        if (!\is_array($cap) || !isset($cap[0], $cap[1]) || !\is_string($cap[0]) || !\is_int($cap[1]) || $cap[1] < 0) {
            return '';
        }

        return \substr($subject, $cap[1], \strlen($cap[0]));
    }
}
