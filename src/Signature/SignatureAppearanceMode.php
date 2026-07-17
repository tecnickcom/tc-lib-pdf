<?php

declare(strict_types=1);

/**
 * SignatureAppearanceMode.php
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

namespace Com\Tecnick\Pdf\Signature;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * Com\Tecnick\Pdf\Signature\SignatureAppearanceMode
 *
 * Backed enum for the signature widget appearance stream slot: N (normal),
 * R (rollover) or D (down), as validated by Tcpdf::setSignatureAppearanceStream().
 *
 * @since     2026-07-17
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
enum SignatureAppearanceMode: string
{
    /** Normal appearance (/N). */
    case Normal = 'N';

    /** Rollover appearance (/R). */
    case Rollover = 'R';

    /** Down appearance (/D). */
    case Down = 'D';

    /**
     * Resolve a loose appearance mode value to the matching enum case.
     *
     * Accepts N, R or D (case-insensitive) or an enum instance (returned
     * unchanged). Unknown values throw, matching setSignatureAppearanceStream().
     *
     * @param string|self $value Appearance mode or enum case.
     *
     * @throws PdfException if the value is not N, R or D.
     */
    public static function fromLoose(string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return match (\strtoupper($value)) {
            'N' => self::Normal,
            'R' => self::Rollover,
            'D' => self::Down,
            default => throw new PdfException('Invalid signature appearance mode (expected N, R, or D)'),
        };
    }
}
