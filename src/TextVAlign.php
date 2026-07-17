<?php

declare(strict_types=1);

/**
 * TextVAlign.php
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf;

/**
 * Com\Tecnick\Pdf\TextVAlign
 *
 * Backed enum for the vertical alignment of text inside a cell: T (top),
 * C (center), B (bottom), A (center on font ascent), L (center on font
 * baseline) or D (center on font descent).
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
enum TextVAlign: string
{
    case Top = 'T';

    case Center = 'C';

    case Bottom = 'B';

    /** Center on the font ascent line. */
    case Ascent = 'A';

    /** Center on the font baseline. */
    case Baseline = 'L';

    /** Center on the font descent line. */
    case Descent = 'D';

    /**
     * Resolve a loose vertical alignment value to the matching enum case.
     *
     * Accepts T, C, B, A, L or D (case-insensitive, surrounding whitespace
     * trimmed) or an enum instance (returned unchanged). Unknown values fall
     * back to Center, matching the cell vertical alignment default.
     *
     * @param string|self $value Vertical alignment or enum case.
     */
    public static function fromLoose(string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom(\strtoupper(\trim($value))) ?? self::Center;
    }
}
