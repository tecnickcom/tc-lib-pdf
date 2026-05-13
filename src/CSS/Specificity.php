<?php

declare(strict_types=1);

/**
 * Specificity.php
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
 * CSS Specificity tuple calculator and comparator
 *
 * Implements CSS 2.1 specificity (a,b,c) tuple scoring:
 * - a = number of ID selectors
 * - b = number of class selectors, attribute selectors, and pseudo-classes
 * - c = number of type selectors and pseudo-elements
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class Specificity
{
    /**
     * Number of ID selectors
     */
    public int $idCount = 0;

    /**
     * Number of class selectors, attribute selectors, and pseudo-classes
     */
    public int $classCount = 0;

    /**
     * Number of type selectors and pseudo-elements
     */
    public int $typeCount = 0;

    /**
     * Initialize specificity tuple
     *
     * @param int $idCount Number of ID selectors
     * @param int $classCount Number of class/attribute/pseudo-class selectors
     * @param int $typeCount Number of type selectors and pseudo-elements
     */
    public function __construct(int $idCount = 0, int $classCount = 0, int $typeCount = 0)
    {
        $this->idCount = \max(0, $idCount);
        $this->classCount = \max(0, $classCount);
        $this->typeCount = \max(0, $typeCount);
    }

    /**
     * Backward-compatible access for legacy tuple property names.
     *
     * @param string $name
     * @return int|null
     */
    public function __get(string $name): ?int
    {
        return match ($name) {
            'a' => $this->idCount,
            'b' => $this->classCount,
            'c' => $this->typeCount,
            default => null,
        };
    }

    /**
     * Parse specificity tuple from a selector string
     *
     * @param string $selector CSS selector string
     * @return self
     */
    public static function fromSelector(string $selector): self
    {
        $matches = [];

        // Count ID selectors (#id)
        $idCount = (int) \preg_match_all('/[\#]/', $selector, $matches);

        // Count class selectors (.class) and attribute selectors ([attr])
        $classCount = (int) \preg_match_all('/[\[\.]/', $selector, $matches);

        // Count pseudo-classes (:pseudo) - not pseudo-elements (::pseudo)
        $classCount += (int) \preg_match_all(
            '/(?<!:):(?!:)(link|visited|hover|active|focus|target|lang|enabled|disabled'
            . '|checked|indeterminate|root|nth|first|last|only|empty|contains|not)/i',
            $selector,
            $matches,
        );

        // Count type selectors (element names)
        $typeCount = (int) \preg_match_all('/[\>\+\~\s]{1}[a-zA-Z0-9]+/', " {$selector}", $matches);

        // Count pseudo-elements (::before, ::after)
        $typeCount += (int) \preg_match_all('/::/', $selector, $matches);

        return new self($idCount, $classCount, $typeCount);
    }

    /**
     * Compare two specificity tuples
     *
     * @param self $other The specificity to compare against
     * @return int -1 if this < other, 0 if equal, 1 if this > other
     */
    public function compareTo(self $other): int
    {
        if ($this->idCount !== $other->idCount) {
            return $this->idCount < $other->idCount ? -1 : 1;
        }
        if ($this->classCount !== $other->classCount) {
            return $this->classCount < $other->classCount ? -1 : 1;
        }
        if ($this->typeCount !== $other->typeCount) {
            return $this->typeCount < $other->typeCount ? -1 : 1;
        }
        return 0;
    }

    /**
     * Check if this specificity is less than another
     *
     * @param self $other
     * @return bool
     */
    public function isLessThan(self $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    /**
     * Check if this specificity is greater than another
     *
     * @param self $other
     * @return bool
     */
    public function isGreaterThan(self $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    /**
     * Check if this specificity equals another
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->compareTo($other) === 0;
    }

    /**
     * Convert specificity to a sortable string key with source order index
     *
     * Format: "{a:04d}{b:04d}{c:04d}_{index:06d}"
     * This allows string-based sorting to maintain specificity precedence
     *
     * @param int $sourceOrder Source order index for tie-breaking
     * @return string Sortable key
     */
    public function toSortKey(int $sourceOrder = 0): string
    {
        return \sprintf('%04d%04d%04d_%06d', $this->idCount, $this->classCount, $this->typeCount, $sourceOrder);
    }

    /**
     * Convert specificity to display string (a,b,c format)
     *
     * @return string
     */
    public function toString(): string
    {
        return \sprintf('(%d,%d,%d)', $this->idCount, $this->classCount, $this->typeCount);
    }

    /**
     * Legacy string representation for backward compatibility
     * Format: "0abc" (e.g., "0123" for a=0, b=1, c=2, d=3)
     *
     * Note: This is maintained for backward compatibility with existing code,
     * but internally we use numeric tuple comparison.
     *
     * @param int $inlineStyle 0 for rule, 1 for inline
     * @return string
     */
    public function toLegacyString(int $inlineStyle = 0): string
    {
        return \sprintf('%d%d%d%d', $inlineStyle, $this->idCount, $this->classCount, $this->typeCount);
    }

    /**
     * Parse specificity from legacy string format
     *
     * @param string $legacyStr Legacy format string (e.g., "0123")
     * @return self
     */
    public static function fromLegacyString(string $legacyStr): self
    {
        // Extract digits from legacy format "0abc"
        $idCount = (int) \substr($legacyStr, 1, 1);
        $classCount = (int) \substr($legacyStr, 2, 1);
        $typeCount = (int) \substr($legacyStr, 3, 1);

        return new self($idCount, $classCount, $typeCount);
    }
}
