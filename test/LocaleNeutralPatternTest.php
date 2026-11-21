<?php

/**
 * LocaleNeutralPatternTest.php
 *
 * @since       2026-11-20
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Static check of src/ for regular expressions and ctype calls whose result depends on LC_CTYPE.
 *
 * Fails on:
 * - a regex literal with the i modifier or an inline (?i flag;
 * - a regex without the u modifier that contains \s \S \w \W \b \B \h \H \v \V or a POSIX class;
 * - a regex fragment that contains one of those classes, unless it is an allowlisted constant
 *   used only in u patterns;
 * - a ctype_* call other than ctype_digit and ctype_xdigit.
 *
 * @since       2026-11-20
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
#[CoversNothing]
class LocaleNeutralPatternTest extends TestCase
{
    /**
     * Constants holding regex fragments that are used only inside u patterns.
     *
     * @var list<string>
     */
    private const FRAGMENT_ALLOWLIST = ['HTML_COLLAPSIBLE_SPACE', 'HTML_COLLAPSIBLE_SPACE_RUN', 'NO_BREAK_SPACE_CLASS'];

    /**
     * Functions and methods whose first argument is a regular expression.
     *
     * @var list<string>
     */
    private const PATTERN_FUNCTIONS = [
        'preg_filter',
        'preg_grep',
        'preg_match',
        'preg_match_all',
        'preg_replace',
        'preg_replace_callback',
        'preg_replace_callback_array',
        'preg_split',
        'matchfolded',
        'replacefolded',
        'matchallfolded',
    ];

    /**
     * ctype functions that do not depend on the locale.
     *
     * @var list<string>
     */
    private const CTYPE_ALLOWED = ['ctype_digit', 'ctype_xdigit'];

    /**
     * Valid PCRE modifier letters.
     */
    private const MODIFIERS = 'imsxuADSUXJnr';

    /**
     * Character class escapes that depend on the locale, or match bytes >= 0x80, without the u modifier.
     */
    private const BYTE_CLASSES = 'sSwWbBhHvV';

    public function testSourcePatternsAreLocaleNeutral(): void
    {
        $failures = [];
        foreach (self::sourceFiles() as $file) {
            $failures = [...$failures, ...self::checkFile($file)];
        }

        $this->assertSame([], $failures, \implode("\n", $failures));
    }

    public function testAllowlistedFragmentsAreUsedOnlyInUnicodePatterns(): void
    {
        $failures = [];
        foreach (self::sourceFiles() as $file) {
            $failures = [...$failures, ...self::checkAllowlistUses($file)];
        }

        $this->assertSame([], $failures, \implode("\n", $failures));
    }

    public function testDetectsUnsafeConstructs(): void
    {
        $code = <<<'PHP'
            <?php
            $a = \preg_match('/\s+/', $x);
            $b = \preg_match('/abc/i', $x);
            $c = \preg_replace('/(?i)abc/u', '', $x);
            $d = \preg_split('/[' . '\w' . ']/', $x);
            $e = \ctype_space($x);
            $f = \preg_match("/a{$x}\\b/", $x);
            $g = \preg_match('/[[:alpha:]]/', $x);
            PHP;
        $failures = self::checkCode('inline.php', $code);
        $this->assertCount(7, $failures, \implode("\n", $failures));
    }

    public function testAcceptsSafeConstructs(): void
    {
        $code = <<<'PHP'
            <?php
            $a = \preg_match('/[\t\n\x0B\f\r ]+/', $x);
            $b = \preg_match('/\s+\w/u', $x);
            $c = \preg_match('/\d+/', $x);
            $d = \preg_match('/^' . \preg_quote($y, '/') . '\s*$/u', $x);
            $e = \ctype_digit($x) && \ctype_xdigit($x);
            $f = '/Type /CryptFilter';
            $g = \preg_match('/\\\\s/', $x);
            PHP;
        $failures = self::checkCode('inline.php', $code);
        $this->assertSame([], $failures, \implode("\n", $failures));
    }

    /**
     * @return list<string>
     */
    private static function sourceFiles(): array
    {
        $files = [];
        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(\dirname(__DIR__) . '/src', \FilesystemIterator::SKIP_DOTS),
        );
        foreach ($iter as $info) {
            if (!$info instanceof \SplFileInfo || $info->getExtension() !== 'php') {
                continue;
            }

            $files[] = $info->getPathname();
        }

        \sort($files);

        return $files;
    }

    /**
     * @return list<string>
     */
    private static function checkFile(string $file): array
    {
        $code = \file_get_contents($file);
        self::assertIsString($code);

        return self::checkCode(\substr($file, \strlen(\dirname(__DIR__)) + 1), $code);
    }

    /**
     * @return list<string>
     */
    private static function checkCode(string $name, string $code): array
    {
        $tokens = self::tokens($code);
        $args = self::patternArguments($tokens);
        $consts = self::constantInitializers($tokens);
        $failures = [];

        foreach ($tokens as $idx => $tok) {
            if ($tok->is([\T_STRING, \T_NAME_FULLY_QUALIFIED])) {
                $fname = \strtolower(\ltrim($tok->text, '\\'));
                if (
                    \str_starts_with($fname, 'ctype_')
                    && !\in_array($fname, self::CTYPE_ALLOWED, true)
                    && self::isFunctionCall($tokens, $idx)
                ) {
                    $failures[] = self::failure($name, $tok, 'locale-dependent ' . $fname . '()');
                }

                continue;
            }

            $value = self::literalValue($tokens, $idx);
            if ($value === null) {
                continue;
            }

            $regex = self::parseRegex($value);
            if ($regex !== null) {
                $issue = self::regexIssue($regex[0], $regex[1]);
                if ($issue !== '') {
                    $failures[] = self::failure($name, $tok, $issue);
                }

                continue;
            }

            $arg = self::enclosingRange($args, $idx);
            if ($arg === null && self::tok($tokens, $idx - 1)->text === '.' && self::isCaselessTail($value)) {
                $failures[] = self::failure($name, $tok, 'i modifier in fragment');
            }

            $risky = self::riskyConstructs($value);
            if ($risky === []) {
                continue;
            }

            if ($arg !== null) {
                $mods = $arg[2];
                $issue = self::fragmentIssue($risky, $mods);
                if ($issue !== '') {
                    $failures[] = self::failure($name, $tok, $issue);
                }

                continue;
            }

            $const = self::enclosingRange($consts, $idx);
            if ($const !== null && \in_array($const[2], self::FRAGMENT_ALLOWLIST, true)) {
                if (\in_array('(?i', $risky, true)) {
                    $failures[] = self::failure($name, $tok, 'case-insensitive flag in fragment');
                }

                continue;
            }

            $failures[] = self::failure($name, $tok, 'locale-dependent ' . \implode(' ', $risky) . ' in fragment');
        }

        foreach ($args as $arg) {
            if ($arg[0] === $arg[1] || !\str_contains($arg[2], 'i')) {
                continue;
            }

            $failures[] = $name . ':' . $arg[3] . ': i modifier in pattern argument';
        }

        return \array_values(\array_unique($failures));
    }

    /**
     * @return list<string>
     */
    private static function checkAllowlistUses(string $file): array
    {
        $code = \file_get_contents($file);
        self::assertIsString($code);
        $name = \substr($file, \strlen(\dirname(__DIR__)) + 1);
        $tokens = self::tokens($code);
        $args = self::patternArguments($tokens);
        $consts = self::constantInitializers($tokens);
        $failures = [];

        foreach ($tokens as $idx => $tok) {
            if (!$tok->is(\T_STRING) || !\in_array($tok->text, self::FRAGMENT_ALLOWLIST, true)) {
                continue;
            }

            if (self::tok($tokens, $idx - 1)->text !== '::') {
                continue;
            }

            $arg = self::enclosingRange($args, $idx);
            if ($arg !== null && \str_contains($arg[2], 'u')) {
                continue;
            }

            $const = self::enclosingRange($consts, $idx);
            if ($const !== null && \in_array($const[2], self::FRAGMENT_ALLOWLIST, true)) {
                continue;
            }

            $failures[] = self::failure($name, $tok, $tok->text . ' used outside a u pattern');
        }

        return $failures;
    }

    /**
     * @return list<\PhpToken>
     */
    private static function tokens(string $code): array
    {
        return \array_values(\array_filter(
            \PhpToken::tokenize($code),
            static fn(\PhpToken $tok): bool => !$tok->is([\T_WHITESPACE, \T_COMMENT, \T_DOC_COMMENT]),
        ));
    }

    private static function failure(string $name, \PhpToken $tok, string $issue): string
    {
        return $name . ':' . $tok->line . ': ' . $issue . ': ' . $tok->text;
    }

    /**
     * Token at the index, or an empty whitespace token out of range.
     *
     * @param list<\PhpToken> $tokens
     */
    private static function tok(array $tokens, int $idx): \PhpToken
    {
        return $tokens[$idx] ?? new \PhpToken(\T_WHITESPACE, '');
    }

    /**
     * @param list<\PhpToken> $tokens
     */
    private static function isFunctionCall(array $tokens, int $idx): bool
    {
        if (self::tok($tokens, $idx + 1)->text !== '(') {
            return false;
        }

        return !\in_array(self::tok($tokens, $idx - 1)->text, ['function', '->', '?->', '::', 'const'], true);
    }

    /**
     * Decoded value of a string literal token, or null.
     *
     * @param list<\PhpToken> $tokens
     */
    private static function literalValue(array $tokens, int $idx): ?string
    {
        $tok = self::tok($tokens, $idx);
        if ($tok->is(\T_CONSTANT_ENCAPSED_STRING)) {
            $text = \ltrim($tok->text, 'bB');
            $body = \substr($text, 1, -1);

            return $text[0] === "'" ? \strtr($body, ['\\\\' => '\\', "\\'" => "'"]) : self::decodeDouble($body);
        }

        if (!$tok->is(\T_ENCAPSED_AND_WHITESPACE)) {
            return null;
        }

        for ($pos = $idx - 1; $pos >= 0; --$pos) {
            $open = self::tok($tokens, $pos);
            if ($open->is(\T_START_HEREDOC)) {
                return \str_contains($open->text, "'") ? $tok->text : self::decodeDouble($tok->text);
            }

            if ($open->text === '"') {
                return self::decodeDouble($tok->text);
            }
        }

        return $tok->text;
    }

    private static function decodeDouble(string $str): string
    {
        $map = [
            'n' => "\n",
            't' => "\t",
            'r' => "\r",
            'v' => "\v",
            'e' => "\e",
            'f' => "\f",
            '\\' => '\\',
            '$' => '$',
            '"' => '"',
        ];
        $out = '';
        $len = \strlen($str);
        for ($pos = 0; $pos < $len; ++$pos) {
            $chr = $str[$pos];
            if ($chr !== '\\' || ($pos + 1) >= $len) {
                $out .= $chr;
                continue;
            }

            $nxt = $str[$pos + 1];
            if (isset($map[$nxt])) {
                $out .= $map[$nxt];
                ++$pos;
                continue;
            }

            if ($nxt === 'x' && \strspn($str, '0123456789abcdefABCDEF', $pos + 2, 2) > 0) {
                $num = \strspn($str, '0123456789abcdefABCDEF', $pos + 2, 2);
                $out .= \chr((int) \hexdec(\substr($str, $pos + 2, $num)));
                $pos += 1 + $num;
                continue;
            }

            if (\strspn($nxt, '01234567') === 1) {
                $num = \strspn($str, '01234567', $pos + 1, 3);
                $out .= \chr((int) \octdec(\substr($str, $pos + 1, $num)) & 0xFF);
                $pos += $num;
                continue;
            }

            $out .= $chr;
        }

        return $out;
    }

    /**
     * Split a complete regex literal into body and modifiers.
     *
     * @return ?array{0: string, 1: string}
     */
    private static function parseRegex(string $value): ?array
    {
        if (\strlen($value) < 3) {
            return null;
        }

        $delim = $value[0];
        if (\strspn($delim, "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789\\ \t\r\n\0") === 1) {
            return null;
        }

        $close = ['(' => ')', '[' => ']', '{' => '}', '<' => '>'][$delim] ?? $delim;
        $end = \strrpos($value, $close);
        if ($end === false || $end === 0) {
            return null;
        }

        $mods = \substr($value, $end + 1);
        if (\strspn($mods, self::MODIFIERS) !== \strlen($mods)) {
            return null;
        }

        return [\substr($value, 1, $end - 1), $mods];
    }

    /**
     * Whether a fragment ends with a regex delimiter followed by modifiers that include i.
     */
    private static function isCaselessTail(string $value): bool
    {
        $num = \strspn(\strrev($value), self::MODIFIERS);
        if ($num === 0 || $num >= \strlen($value)) {
            return false;
        }

        return (
            \str_contains(\substr($value, -$num), 'i') && \str_contains('/#~%!@|', $value[\strlen($value) - $num - 1])
        );
    }

    private static function regexIssue(string $body, string $mods): string
    {
        if (\str_contains($mods, 'i')) {
            return 'i modifier';
        }

        return self::fragmentIssue(self::riskyConstructs($body), $mods);
    }

    /**
     * @param list<string> $risky
     */
    private static function fragmentIssue(array $risky, string $mods): string
    {
        if (\in_array('(?i', $risky, true)) {
            return 'case-insensitive flag (?i';
        }

        if ($risky === [] || \str_contains($mods, 'u')) {
            return '';
        }

        return 'locale-dependent ' . \implode(' ', $risky) . ' without u modifier';
    }

    /**
     * Locale-dependent constructs found in a regex body or fragment.
     *
     * @return list<string>
     */
    private static function riskyConstructs(string $value): array
    {
        $found = [];
        $len = \strlen($value);
        for ($pos = 0; $pos < $len; ++$pos) {
            $chr = $value[$pos];
            if ($chr === '\\' && ($pos + 1) < $len) {
                ++$pos;
                if (\str_contains(self::BYTE_CLASSES, $value[$pos])) {
                    $found[] = '\\' . $value[$pos];
                }

                continue;
            }

            if ($chr === '[' && \substr($value, $pos, 3) === '[[:') {
                $found[] = '[[:';
                continue;
            }

            if ($chr === '(' && \substr($value, $pos + 1, 1) === '?') {
                $num = \strspn($value, 'imnsxJU-^', $pos + 2);
                $flags = \substr($value, $pos + 2, $num);
                $after = \substr($value, $pos + 2 + $num, 1);
                if (\str_contains($flags, 'i') && ($after === ')' || $after === ':')) {
                    $found[] = '(?i';
                }
            }
        }

        return \array_values(\array_unique($found));
    }

    /**
     * Token ranges of the pattern argument of each regex call: [start, end, modifiers, line].
     *
     * @param list<\PhpToken> $tokens
     *
     * @return list<array{0: int, 1: int, 2: string, 3: int}>
     */
    private static function patternArguments(array $tokens): array
    {
        $ranges = [];
        foreach ($tokens as $idx => $tok) {
            if (
                !$tok->is([\T_STRING, \T_NAME_FULLY_QUALIFIED])
                || !\in_array(\strtolower(\ltrim($tok->text, '\\')), self::PATTERN_FUNCTIONS, true)
                || self::tok($tokens, $idx + 1)->text !== '('
                || $idx > 0 && self::tok($tokens, $idx - 1)->text === 'function'
            ) {
                continue;
            }

            $start = $idx + 2;
            $end = self::argumentEnd($tokens, $start);
            if (\strtolower(\ltrim($tok->text, '\\')) === 'preg_replace_callback_array') {
                continue;
            }

            $ranges[] = [$start, $end, self::trailingModifiers($tokens, $start, $end), $tok->line];
        }

        return $ranges;
    }

    /**
     * Index of the last token of the argument that starts at $start.
     *
     * @param list<\PhpToken> $tokens
     */
    private static function argumentEnd(array $tokens, int $start): int
    {
        $depth = 0;
        $count = \count($tokens);
        for ($pos = $start; $pos < $count; ++$pos) {
            $text = self::tok($tokens, $pos)->text;
            if (\in_array($text, ['(', '[', '{', '${'], true) || self::tok($tokens, $pos)->is(\T_CURLY_OPEN)) {
                ++$depth;
                continue;
            }

            if (\in_array($text, [')', ']', '}'], true)) {
                if ($depth === 0) {
                    return $pos - 1;
                }

                --$depth;
                continue;
            }

            if ($text === ',' && $depth === 0) {
                return $pos - 1;
            }
        }

        return $count - 1;
    }

    /**
     * Modifiers of the regex built by the argument tokens, read from its last string literal.
     *
     * @param list<\PhpToken> $tokens
     */
    private static function trailingModifiers(array $tokens, int $start, int $end): string
    {
        for ($pos = $end; $pos >= $start; --$pos) {
            $value = self::literalValue($tokens, $pos);
            if ($value === null) {
                if (self::tok($tokens, $pos)->text === '"') {
                    continue;
                }

                return '';
            }

            $num = \strspn(\strrev($value), self::MODIFIERS);
            if ($num === \strlen($value)) {
                return '';
            }

            return \substr($value, \strlen($value) - $num);
        }

        return '';
    }

    /**
     * Token ranges of constant initializers: [start, end, name, line].
     *
     * @param list<\PhpToken> $tokens
     *
     * @return list<array{0: int, 1: int, 2: string, 3: int}>
     */
    private static function constantInitializers(array $tokens): array
    {
        $ranges = [];
        $count = \count($tokens);
        foreach ($tokens as $idx => $tok) {
            if (!$tok->is(\T_CONST)) {
                continue;
            }

            $pos = $idx + 1;
            while ($pos < $count && self::tok($tokens, $pos)->text !== '=') {
                ++$pos;
            }

            $cname = self::tok($tokens, $pos - 1)->text;
            $start = $pos + 1;
            $depth = 0;
            for ($end = $start; $end < $count; ++$end) {
                $text = self::tok($tokens, $end)->text;
                if (\in_array($text, ['(', '['], true)) {
                    ++$depth;
                } elseif (\in_array($text, [')', ']'], true)) {
                    --$depth;
                } elseif (($text === ';' || $text === ',') && $depth === 0) {
                    break;
                }
            }

            $ranges[] = [$start, $end - 1, $cname, $tok->line];
        }

        return $ranges;
    }

    /**
     * Innermost range that contains the token index.
     *
     * @param list<array{0: int, 1: int, 2: string, 3: int}> $ranges
     *
     * @return ?array{0: int, 1: int, 2: string, 3: int}
     */
    private static function enclosingRange(array $ranges, int $idx): ?array
    {
        $found = null;
        foreach ($ranges as $range) {
            if ($idx < $range[0] || $idx > $range[1] || $found !== null && $range[0] < $found[0]) {
                continue;
            }

            $found = $range;
        }

        return $found;
    }
}
