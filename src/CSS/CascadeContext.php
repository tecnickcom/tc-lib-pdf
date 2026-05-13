<?php

declare(strict_types=1);

/**
 * CascadeContext.php
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
 * CSS Cascade Context - Tracks source order across all CSS sources
 *
 * Maintains deterministic cascade ordering by tracking global source position
 * across all CSS sources (external stylesheets, embedded styles, inline styles).
 *
 * Usage:
 *   $ctx = new CascadeContext();
 *   $css1 = extractCSSFrom('stylesheet1.css', $ctx);
 *   $css2 = extractCSSFrom('stylesheet2.css', $ctx);
 *   // css1 rules always have lower source order than css2 rules with same specificity
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class CascadeContext
{
    /**
     * Maximum value for normal (non-important, non-inline) source order
     * Normal rules from stylesheets use 0 to MAX_NORMAL_SOURCE_ORDER
     */
    public const MAX_NORMAL_SOURCE_ORDER = 9_999_999;

    /**
     * Inline style source order - higher than normal rules, lower than !important
     */
    public const INLINE_STYLE_SOURCE_ORDER = 10_000_000;

    /**
     * Minimum value for !important source order (reserved range)
     * !important rules use 100000000 and above for highest precedence
     */
    public const MIN_IMPORTANT_SOURCE_ORDER = 100_000_000;

    /**
     * Global counter for normal (non-important) rules across all sources
     */
    private int $normalSourceOrder = 0;

    /**
     * Global counter for !important rules across all sources
     * Starts at MIN_IMPORTANT_SOURCE_ORDER to ensure higher precedence
     */
    private int $importantSourceOrder = self::MIN_IMPORTANT_SOURCE_ORDER;

    /**
     * Track which source (external, embedded style, inline) we're processing
     * For debugging and diagnostics
     */
    private string $currentSourceType = 'embedded';

    /**
     * Get next source order value for a normal rule
     *
     * @return int Global source order for this rule
     */
    public function getNextNormalSourceOrder(): int
    {
        return ++$this->normalSourceOrder;
    }

    /**
     * Get next source order value for an !important rule
     *
     * @return int Global source order for this rule
     */
    public function getNextImportantSourceOrder(): int
    {
        return ++$this->importantSourceOrder;
    }

    /**
     * Get inline style source order (highest normal precedence)
     *
     * @return int
     */
    public static function getInlineStyleSourceOrder(): int
    {
        return self::INLINE_STYLE_SOURCE_ORDER;
    }

    /**
     * Set current source type for tracking/debugging
     *
     * @param string $type 'external', 'embedded', 'inline'
     * @return void
     */
    public function setCurrentSourceType(string $type): void
    {
        $this->currentSourceType = $type;
    }

    /**
     * Get current source type
     *
     * @return string
     */
    public function getCurrentSourceType(): string
    {
        return $this->currentSourceType;
    }

    /**
     * Get total rules processed (normal + important)
     *
     * @return int
     */
    public function getTotalRulesProcessed(): int
    {
        return $this->normalSourceOrder + ($this->importantSourceOrder - self::MIN_IMPORTANT_SOURCE_ORDER);
    }

    /**
     * Reset counters (for testing or document resets)
     *
     * @return void
     */
    public function reset(): void
    {
        $this->normalSourceOrder = 0;
        $this->importantSourceOrder = self::MIN_IMPORTANT_SOURCE_ORDER;
        $this->currentSourceType = 'embedded';
    }
}
