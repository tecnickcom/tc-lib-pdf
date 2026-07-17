<?php

declare(strict_types=1);

/**
 * ExternalSignatureEncoding.php
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

namespace Com\Tecnick\Pdf\Signature;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * Com\Tecnick\Pdf\Signature\ExternalSignatureEncoding
 *
 * Backed enum for the encoding of an externally-produced signature value passed
 * to Tcpdf::applyExternalSignature(): binary, base64 or hex.
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
enum ExternalSignatureEncoding: string
{
    case Binary = 'binary';

    case Base64 = 'base64';

    case Hex = 'hex';

    /**
     * Resolve a loose signature encoding value to the matching enum case.
     *
     * Accepts the canonical name (case-insensitive, surrounding whitespace
     * trimmed) or an enum instance (returned unchanged). Unknown values throw,
     * matching applyExternalSignature().
     *
     * @param string|self $value Signature encoding or enum case.
     *
     * @throws PdfException if the value is not a known signature encoding.
     */
    public static function fromLoose(string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom(\strtolower(\trim($value))) ?? throw new PdfException('Invalid signature encoding');
    }
}
