<?php

declare(strict_types=1);

/**
 * TextFitMode.php
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
 * Com\Tecnick\Pdf\TextFitMode
 *
 * Backed enum for the text-cell auto-fit mode: '' disables auto-fit, otherwise
 * T, S or F select a fit strategy (see Text::normalizeTextCellFitMode()).
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
enum TextFitMode: string
{
    /** Auto-fit disabled. */
    case Off = '';

    /** Truncate the text to fit the cell width and height. */
    case Truncate = 'T';

    /** Compress the text horizontally to best fit the cell width. */
    case Stretch = 'S';

    /** Decrease the font size only when the text is too large. */
    case ShrinkFont = 'F';

    /**
     * Resolve a loose fit mode value to the matching enum case.
     *
     * Accepts T, S or F (case-insensitive, surrounding whitespace trimmed) or an
     * enum instance (returned unchanged). Any other value disables auto-fit
     * (Off), matching Text::normalizeTextCellFitMode().
     *
     * @param string|self $value Fit mode or enum case.
     */
    public static function fromLoose(string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom(\strtoupper(\trim($value))) ?? self::Off;
    }
}
