<?php

declare(strict_types=1);

/**
 * HybridProfile.php
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
 * Com\Tecnick\Pdf\HybridProfile
 *
 * Backed enum for the standard followed by a hybrid electronic document: a
 * PDF/A-3 file carrying a structured XML payload as an embedded file, as
 * produced by MetaInfo::setFacturX().
 *
 * Each case supplies the payload file name, the XMP namespace URI, prefix,
 * extension schema name, version and document type required by that standard.
 *
 * @since     2026-08-31
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
enum HybridProfile: string
{
    /** Factur-X 1.0.x, equivalent to ZUGFeRD 2.1 and later. */
    case FacturX = 'facturx';

    /** ZUGFeRD 1.0. */
    case ZugferdV1 = 'zugferdv1';

    /** ZUGFeRD 2.0. */
    case ZugferdV2 = 'zugferdv2';

    /** Order-X 1.0. */
    case OrderX = 'orderx';

    /**
     * Name the XML payload must be embedded with.
     */
    public function fileName(): string
    {
        return match ($this) {
            self::FacturX => 'factur-x.xml',
            self::ZugferdV1 => 'ZUGFeRD-invoice.xml',
            self::ZugferdV2 => 'zugferd-invoice.xml',
            self::OrderX => 'order-x.xml',
        };
    }

    /**
     * XMP namespace URI of the profile properties.
     */
    public function namespaceUri(): string
    {
        return match ($this) {
            self::FacturX => 'urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#',
            self::ZugferdV1 => 'urn:ferd:pdfa:CrossIndustryDocument:invoice:1p0#',
            self::ZugferdV2 => 'urn:zugferd:pdfa:CrossIndustryDocument:invoice:2p0#',
            self::OrderX => 'urn:factur-x:pdfa:CrossIndustryDocument:1p0#',
        };
    }

    /**
     * XMP namespace prefix of the profile properties.
     */
    public function prefix(): string
    {
        return match ($this) {
            self::FacturX, self::OrderX => 'fx',
            self::ZugferdV1, self::ZugferdV2 => 'zf',
        };
    }

    /**
     * Value of the Version XMP property.
     */
    public function version(): string
    {
        return match ($this) {
            self::FacturX, self::ZugferdV1, self::OrderX => '1.0',
            self::ZugferdV2 => '2p0',
        };
    }

    /**
     * Name of the PDF/A extension schema describing the profile properties.
     */
    public function schemaName(): string
    {
        return match ($this) {
            self::FacturX => 'Factur-X PDFA Extension Schema',
            self::ZugferdV1, self::ZugferdV2 => 'ZUGFeRD PDFA Extension Schema',
            self::OrderX => 'Order-X PDFA Extension Schema',
        };
    }

    /**
     * Default value of the DocumentType XMP property.
     */
    public function documentType(): string
    {
        return $this === self::OrderX ? 'ORDER' : 'INVOICE';
    }

    /**
     * Default description of the embedded file.
     */
    public function description(): string
    {
        return match ($this) {
            self::FacturX => 'Factur-X/ZUGFeRD electronic invoice',
            self::ZugferdV1, self::ZugferdV2 => 'ZUGFeRD electronic invoice',
            self::OrderX => 'Order-X electronic order',
        };
    }

    /**
     * Resolve a loose profile value to the matching enum case.
     *
     * Accepts the profile identifier (case-insensitive, surrounding whitespace
     * trimmed) or an enum instance (returned unchanged). Unknown values throw.
     *
     * @param string|self $value Profile identifier or enum case.
     *
     * @throws PdfException if the value is not a known profile.
     */
    public static function fromLoose(string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return (
            self::tryFrom(\trim(\strtolower($value))) ?? throw new PdfException(
                'the profile must be one of: '
                    . \implode(', ', \array_map(static fn(self $case): string => $case->value, self::cases())),
            )
        );
    }
}
