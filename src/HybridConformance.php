<?php

declare(strict_types=1);

/**
 * HybridConformance.php
 *
 * @since     2026-08-31
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

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * Com\Tecnick\Pdf\HybridConformance
 *
 * Backed enum for the conformance level of the XML payload of a hybrid
 * electronic document, written as the ConformanceLevel XMP property by
 * MetaInfo::setFacturX(). The backing value is the literal property value.
 *
 * Not every level is defined by every profile: MINIMUM, BASIC WL and XRECHNUNG
 * belong to Factur-X and ZUGFeRD 2, while COMFORT belongs to ZUGFeRD 1.
 *
 * @since     2026-08-31
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
enum HybridConformance: string
{
    case Minimum = 'MINIMUM';

    case BasicWl = 'BASIC WL';

    case Basic = 'BASIC';

    case Comfort = 'COMFORT';

    case En16931 = 'EN 16931';

    case Extended = 'EXTENDED';

    case XRechnung = 'XRECHNUNG';

    /**
     * Resolve a loose conformance level to the matching enum case.
     *
     * Accepts the property value (case-insensitive, whitespace ignored, so both
     * 'EN 16931' and 'en16931' resolve) or an enum instance (returned
     * unchanged). Unknown values throw.
     *
     * @param string|self $value Conformance level or enum case.
     *
     * @throws PdfException if the value is not a known conformance level.
     */
    public static function fromLoose(string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        $key = \str_replace(' ', '', \strtoupper(\trim($value)));
        foreach (self::cases() as $case) {
            if (\str_replace(' ', '', $case->value) === $key) {
                return $case;
            }
        }

        throw new PdfException(
            'the conformance level must be one of: '
                . \implode(', ', \array_map(static fn(self $case): string => $case->value, self::cases())),
        );
    }
}
