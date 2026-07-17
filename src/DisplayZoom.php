<?php

declare(strict_types=1);

/**
 * DisplayZoom.php
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
 * Com\Tecnick\Pdf\DisplayZoom
 *
 * Backed enum for the named document zoom modes accepted by
 * Tcpdf::setDisplayMode() (in addition to a numeric zoom factor).
 *
 * @since     2026-07-17
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
enum DisplayZoom: string
{
    /** Display the entire page on screen. */
    case FullPage = 'fullpage';

    /** Use the full width of the window. */
    case FullWidth = 'fullwidth';

    /** Real size (100%). */
    case Real = 'real';

    /** Use the viewer default zoom. */
    case DefaultZoom = 'default';

    /**
     * Resolve a loose named zoom value to the matching enum case.
     *
     * Accepts the canonical name or an enum instance (returned unchanged).
     * Unknown values fall back to DefaultZoom, matching setDisplayMode().
     *
     * @param string|self $value Named zoom or enum case.
     */
    public static function fromLoose(string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom($value) ?? self::DefaultZoom;
    }
}
