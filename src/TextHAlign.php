<?php

declare(strict_types=1);

/**
 * TextHAlign.php
 *
 * @since     2026-07-17
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
 * Com\Tecnick\Pdf\TextHAlign
 *
 * Backed enum for the horizontal alignment of text inside a cell: L (left),
 * C (center), R (right) or J (justify).
 *
 * @since     2026-07-17
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
enum TextHAlign: string
{
    case Left = 'L';

    case Center = 'C';

    case Right = 'R';

    case Justify = 'J';

    /**
     * Resolve a loose horizontal alignment value to the matching enum case.
     *
     * Accepts L, C, R or J (case-insensitive, surrounding whitespace trimmed) or
     * an enum instance (returned unchanged). Unknown values fall back to Left,
     * matching the cell horizontal alignment default.
     *
     * @param string|self $value Horizontal alignment or enum case.
     */
    public static function fromLoose(string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom(\strtoupper(\trim($value))) ?? self::Left;
    }
}
