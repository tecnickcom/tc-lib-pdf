<?php

declare(strict_types=1);

/**
 * AFRelationship.php
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

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * Com\Tecnick\Pdf\AFRelationship
 *
 * Backed enum for the PDF/A-3 embedded file /AFRelationship value, as validated
 * by JavaScript::addEmbeddedFile().
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
enum AFRelationship: string
{
    case Source = 'Source';

    case Data = 'Data';

    case Alternative = 'Alternative';

    case Supplement = 'Supplement';

    case Unspecified = 'Unspecified';

    /**
     * Resolve a loose AFRelationship value to the matching enum case.
     *
     * Accepts the exact relationship name or an enum instance (returned
     * unchanged). Unknown values throw, matching addEmbeddedFile().
     *
     * @param string|self $value AFRelationship name or enum case.
     *
     * @throws PdfException if the value is not a known AFRelationship.
     */
    public static function fromLoose(string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return (
            self::tryFrom($value) ?? throw new PdfException(
                'afrel must be one of: '
                    . \implode(', ', \array_map(static fn(self $case): string => $case->value, self::cases())),
            )
        );
    }
}
