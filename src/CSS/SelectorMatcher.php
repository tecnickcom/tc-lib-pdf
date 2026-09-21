<?php

declare(strict_types=1);

/**
 * SelectorMatcher.php
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

namespace Com\Tecnick\Pdf\CSS;

/**
 * Com\Tecnick\Pdf\CSS\SelectorMatcher
 *
 * CSS selector compiler and matcher for the SVG cascade.
 *
 * Supports type, class, id and universal selectors, attribute selectors, the
 * descendant, child, adjacent sibling and general sibling combinators, and the
 * structural pseudo-classes that a document read in order can decide:
 * ':root', ':first-child', ':nth-child()', ':not()' over a simple compound.
 *
 * A selector that needs to look ahead of the current element (':last-child',
 * ':only-child', ':nth-last-child()', ':empty') or that uses anything else is
 * rejected by compile(), so it applies to nothing rather than applying wrongly.
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type TSelNode array{
 *     'name': string,
 *     'id': string,
 *     'classes': array<int, string>,
 *     'attr': array<string, string>,
 *     'index': int,
 *     'depth': int,
 * }
 *
 * @phpstan-type TSelPart array{
 *     'combinator': string,
 *     'type': string,
 *     'id': string,
 *     'classes': array<int, string>,
 *     'attrs': array<int, array{'name': string, 'op': string, 'value': string}>,
 *     'pseudo': array<int, array{'name': string, 'arg': string}>,
 * }
 */
class SelectorMatcher
{
    /**
     * Matches one compound selector: an optional type followed by any number of
     * id, class, attribute and pseudo-class parts.
     */
    private const COMPOUND_REGEX = '/^(\*|[A-Za-z][\w-]*)?((?:[.#][\w-]+|\[[^\]]*\]|:[a-z-]+(?:\([^)]*\))?)*)$/';

    /**
     * Matches a single simple part inside a compound selector.
     */
    private const PART_REGEX = '/[.#][\w-]+|\[[^\]]*\]|:[a-z-]+(?:\([^)]*\))?/';

    /**
     * Matches an attribute selector body.
     */
    private const ATTR_REGEX = '/^\[\s*([\w-]+)\s*(?:([~|^$*]?=)\s*("[^"]*"|\'[^\']*\'|[^\]\s]*)\s*)?\]$/';

    /**
     * Pseudo-classes that a document read in order can decide.
     *
     * @var array<string, true>
     */
    private const SUPPORTED_PSEUDO = [
        'root' => true,
        'first-child' => true,
        'nth-child' => true,
        'not' => true,
    ];

    /**
     * Compile a selector into the parts used by matches().
     *
     * @param string $selector Selector with its white space already collapsed.
     *
     * @return array<int, TSelPart>|null Compiled parts, or null when unsupported.
     */
    public static function compile(string $selector): ?array
    {
        $selector = \trim($selector);
        if ($selector === '') {
            return null;
        }

        $tokens = self::tokenize($selector);
        if ($tokens === []) {
            return null;
        }

        $parts = [];
        $combinator = '';
        foreach ($tokens as $chunk) {
            if (\in_array($chunk, ['>', '+', '~'], true)) {
                if ($parts === [] || $combinator !== '') {
                    return null;
                }

                $combinator = $chunk;
                continue;
            }

            $compound = self::compileCompound($chunk);
            if ($compound === null) {
                return null;
            }

            if ($parts !== []) {
                $compound['combinator'] = $combinator === '' ? ' ' : $combinator;
            }

            $parts[] = $compound;
            $combinator = '';
        }

        return $combinator === '' && $parts !== [] ? $parts : null;
    }

    /**
     * Split a selector into compound selectors and combinators.
     *
     * Brackets and parentheses are skipped over, so a combinator character
     * inside an attribute value or a functional pseudo-class is not a token.
     *
     * @param string $selector Selector with its white space already collapsed.
     *
     * @return array<int, string>
     */
    private static function tokenize(string $selector): array
    {
        $tokens = [];
        $buffer = '';
        $depth = 0;
        $quote = '';

        foreach (\str_split($selector) as $char) {
            if ($quote !== '') {
                $buffer .= $char;
                if ($char === $quote) {
                    $quote = '';
                }

                continue;
            }

            if ($char === '"' || $char === "'") {
                $quote = $char;
                $buffer .= $char;
                continue;
            }

            if ($char === '[' || $char === '(') {
                $depth++;
            } elseif ($char === ']' || $char === ')') {
                $depth--;
            }

            if ($depth > 0 || $char !== ' ' && $char !== '>' && $char !== '+' && $char !== '~') {
                $buffer .= $char;
                continue;
            }

            if ($buffer !== '') {
                $tokens[] = $buffer;
                $buffer = '';
            }

            if ($char !== ' ') {
                $tokens[] = $char;
            }
        }

        if ($buffer !== '') {
            $tokens[] = $buffer;
        }

        return $depth === 0 && $quote === '' ? $tokens : [];
    }

    /**
     * Compile one compound selector.
     *
     * @param string $compound Compound selector without combinators.
     *
     * @return TSelPart|null
     */
    private static function compileCompound(string $compound): ?array
    {
        $match = [];
        if (\preg_match(self::COMPOUND_REGEX, $compound, $match) !== 1) {
            return null;
        }

        $part = [
            'combinator' => '',
            'type' => ($match[1] ?? '') === '*' ? '' : $match[1] ?? '',
            'id' => '',
            'classes' => [],
            'attrs' => [],
            'pseudo' => [],
        ];

        $simple = [];
        \preg_match_all(self::PART_REGEX, $match[2] ?? '', $simple);
        foreach ($simple[0] ?? [] as $item) {
            if (!self::compileSimple($item, $part)) {
                return null;
            }
        }

        return $part;
    }

    /**
     * Add one simple selector to a compound.
     *
     * @param string $item Simple selector.
     * @param TSelPart $part Compound updated in place.
     */
    private static function compileSimple(string $item, array &$part): bool
    {
        if ($item[0] === '.') {
            $part['classes'][] = \substr($item, 1);
            return true;
        }

        if ($item[0] === '#') {
            $part['id'] = \substr($item, 1);
            return true;
        }

        if ($item[0] === '[') {
            $attr = [];
            if (\preg_match(self::ATTR_REGEX, $item, $attr) !== 1) {
                return false;
            }

            $value = $attr[3] ?? '';
            if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
                $value = \substr($value, 1, -1);
            }

            $part['attrs'][] = [
                'name' => $attr[1] ?? '',
                'op' => $attr[2] ?? '',
                'value' => $value,
            ];
            return true;
        }

        $pseudo = [];
        \preg_match('/^:([a-z-]+)(?:\((.*)\))?$/', $item, $pseudo);
        $name = $pseudo[1] ?? '';
        if (!isset(self::SUPPORTED_PSEUDO[$name])) {
            return false;
        }

        $arg = \trim($pseudo[2] ?? '');
        if ($name === 'not' && self::compileCompound($arg) === null) {
            return false;
        }

        if ($name === 'nth-child' && self::parseNth($arg) === null) {
            return false;
        }

        $part['pseudo'][] = [
            'name' => $name,
            'arg' => $arg,
        ];
        return true;
    }

    /**
     * Parse an An+B argument into its [a, b] coefficients.
     *
     * @param string $arg Argument of nth-child().
     *
     * @return array{0: int, 1: int}|null
     */
    private static function parseNth(string $arg): ?array
    {
        $arg = \strtolower(\str_replace(' ', '', $arg));
        if ($arg === 'odd') {
            return [2, 1];
        }

        if ($arg === 'even') {
            return [2, 0];
        }

        $match = [];
        if (\preg_match('/^([+-]?\d*)n([+-]\d+)?$/', $arg, $match) === 1) {
            $coeff = $match[1] ?? '';
            $step = match ($coeff) {
                '', '+' => 1,
                '-' => -1,
                default => (int) $coeff,
            };
            return [$step, (int) ($match[2] ?? '0')];
        }

        return \preg_match('/^[+-]?\d+$/', $arg) === 1 ? [0, (int) $arg] : null;
    }

    /**
     * Return true when the last node of the chain matches the compiled selector.
     *
     * @param array<int, TSelPart> $parts Compiled selector.
     * @param array<int, array{'node': TSelNode, 'prev': array<int, TSelNode>}> $chain
     *        Open elements from the root to the element under test.
     */
    public static function matches(array $parts, array $chain): bool
    {
        $last = \count($parts) - 1;
        $level = \count($chain) - 1;
        if ($last < 0 || $level < 0) {
            return false;
        }

        return self::matchFrom($parts, $last, $chain, $level);
    }

    /**
     * Match the compiled selector from a given part against a given chain level.
     *
     * @param array<int, TSelPart> $parts Compiled selector.
     * @param int $index Part to match.
     * @param array<int, array{'node': TSelNode, 'prev': array<int, TSelNode>}> $chain Open elements.
     * @param int $level Chain level to match the part against.
     * @param ?TSelNode $node Node to match instead of the one held at that level.
     */
    private static function matchFrom(array $parts, int $index, array $chain, int $level, ?array $node = null): bool
    {
        if (!isset($chain[$level])) {
            return false;
        }

        $part = $parts[$index] ?? null;
        if ($part === null) {
            return false;
        }

        $node ??= $chain[$level]['node'];
        if (!self::matchCompound($part, $node, $level)) {
            return false;
        }

        if ($index === 0) {
            return true;
        }

        return match ($part['combinator']) {
            '>' => self::matchFrom($parts, $index - 1, $chain, $level - 1),
            '+' => self::matchSibling($parts, $index - 1, $chain, $level, $node, true),
            '~' => self::matchSibling($parts, $index - 1, $chain, $level, $node, false),
            default => self::matchAncestor($parts, $index - 1, $chain, $level - 1),
        };
    }

    /**
     * Match a part against any ancestor at or above a level.
     *
     * @param array<int, TSelPart> $parts Compiled selector.
     * @param int $index Part to match.
     * @param array<int, array{'node': TSelNode, 'prev': array<int, TSelNode>}> $chain Open elements.
     * @param int $level Highest level to try.
     */
    private static function matchAncestor(array $parts, int $index, array $chain, int $level): bool
    {
        for ($idx = $level; $idx >= 0; $idx--) {
            if (self::matchFrom($parts, $index, $chain, $idx)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Match a part against the preceding siblings of a node.
     *
     * @param array<int, TSelPart> $parts Compiled selector.
     * @param int $index Part to match.
     * @param array<int, array{'node': TSelNode, 'prev': array<int, TSelNode>}> $chain Open elements.
     * @param int $level Level the siblings live at.
     * @param TSelNode $node Node whose siblings are examined.
     * @param bool $adjacent True to test only the immediately preceding sibling.
     */
    private static function matchSibling(
        array $parts,
        int $index,
        array $chain,
        int $level,
        array $node,
        bool $adjacent,
    ): bool {
        $prev = $chain[$level]['prev'] ?? [];
        // The chain carries the siblings of the element under test; a sibling
        // reached through a combinator only sees the ones before it.
        $prev = \array_slice($prev, 0, $node['index']);
        if ($prev === []) {
            return false;
        }

        if ($adjacent) {
            return self::matchFrom($parts, $index, $chain, $level, \end($prev));
        }

        foreach (\array_reverse($prev) as $sibling) {
            if (self::matchFrom($parts, $index, $chain, $level, $sibling)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return true when a node satisfies one compound selector.
     *
     * @param TSelPart $part Compound selector.
     * @param TSelNode $node Node under test.
     * @param int $level Depth of the node in the chain.
     */
    private static function matchCompound(array $part, array $node, int $level): bool
    {
        if ($part['type'] !== '' && $part['type'] !== $node['name']) {
            return false;
        }

        if ($part['id'] !== '' && $part['id'] !== $node['id']) {
            return false;
        }

        foreach ($part['classes'] as $class) {
            if (!\in_array($class, $node['classes'], true)) {
                return false;
            }
        }

        foreach ($part['attrs'] as $attr) {
            if (!self::matchAttribute($attr, $node)) {
                return false;
            }
        }

        foreach ($part['pseudo'] as $pseudo) {
            if (!self::matchPseudo($pseudo, $node, $level)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return true when a node satisfies one attribute selector.
     *
     * @param array{'name': string, 'op': string, 'value': string} $attr Attribute selector.
     * @param TSelNode $node Node under test.
     */
    private static function matchAttribute(array $attr, array $node): bool
    {
        if (!isset($node['attr'][$attr['name']])) {
            return false;
        }

        $actual = $node['attr'][$attr['name']];
        $value = $attr['value'];

        return match ($attr['op']) {
            '' => true,
            '=' => $actual === $value,
            '~=' => $value !== '' && \in_array($value, self::splitWords($actual), true),
            '|=' => $actual === $value || \str_starts_with($actual, $value . '-'),
            '^=' => $value !== '' && \str_starts_with($actual, $value),
            '$=' => $value !== '' && \str_ends_with($actual, $value),
            '*=' => $value !== '' && \str_contains($actual, $value),
            default => false,
        };
    }

    /**
     * Split a white space separated attribute value into its words.
     *
     * @param string $value Attribute value.
     *
     * @return array<int, string>
     */
    private static function splitWords(string $value): array
    {
        $words = \preg_split('/\s+/', $value, -1, \PREG_SPLIT_NO_EMPTY);

        return \is_array($words) ? $words : [];
    }

    /**
     * Return true when a node satisfies one pseudo-class.
     *
     * @param array{'name': string, 'arg': string} $pseudo Pseudo-class.
     * @param TSelNode $node Node under test.
     * @param int $level Depth of the node in the chain.
     */
    private static function matchPseudo(array $pseudo, array $node, int $level): bool
    {
        switch ($pseudo['name']) {
            case 'root':
                return $level === 0;
            case 'first-child':
                return $node['index'] === 0;
            case 'nth-child':
                $nth = self::parseNth($pseudo['arg']);
                if ($nth === null) {
                    return false;
                }

                // The index is zero based here and one based in the selector.
                $pos = $node['index'] + 1;
                if ($nth[0] === 0) {
                    return $pos === $nth[1];
                }

                $offset = $pos - $nth[1];
                return ($offset % $nth[0]) === 0 && \intdiv($offset, $nth[0]) >= 0;
            default:
                $inner = self::compileCompound($pseudo['arg']);
                return $inner !== null && !self::matchCompound($inner, $node, $level);
        }
    }
}
