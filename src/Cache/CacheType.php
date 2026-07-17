<?php

declare(strict_types=1);

/**
 * CacheType.php
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

namespace Com\Tecnick\Pdf\Cache;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * Com\Tecnick\Pdf\Cache\CacheType
 *
 * Backed enum for the cacheable subsystem type. The backing value of each case
 * matches a CacheInterface::TYPE_* constant.
 *
 * @since     2026-07-17
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
enum CacheType: string
{
    /** TrueType font subset programs (CacheInterface::TYPE_FONT). */
    case Font = 'font';

    /** Processed image data (CacheInterface::TYPE_IMAGE). */
    case Image = 'image';

    /**
     * Resolve a loose cache type value to the matching enum case.
     *
     * Accepts the canonical type name or an enum instance (returned unchanged).
     * Unknown values throw.
     *
     * @param string|self $value Cache type name or enum case.
     *
     * @throws PdfException if the value is not a known cache type.
     */
    public static function fromLoose(string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom($value) ?? throw new PdfException('unknown cache type: ' . $value);
    }
}
