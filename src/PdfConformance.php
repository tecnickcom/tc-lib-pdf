<?php

declare(strict_types=1);

/**
 * PdfConformance.php
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
 * Com\Tecnick\Pdf\PdfConformance
 *
 * Backed enum for the PDF conformance mode accepted by Tcpdf::setPDFMode():
 * the empty string (no special conformance), a PDF/A level, a PDF/X profile or
 * a PDF/UA profile. The backing value is the lowercase mode identifier.
 *
 * @since       2026-07-17
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
enum PdfConformance: string
{
    /** No special conformance. */
    case None = '';

    case Pdfa1 = 'pdfa1';

    case Pdfa1a = 'pdfa1a';

    case Pdfa1b = 'pdfa1b';

    case Pdfa2 = 'pdfa2';

    case Pdfa2a = 'pdfa2a';

    case Pdfa2b = 'pdfa2b';

    case Pdfa2u = 'pdfa2u';

    case Pdfa3 = 'pdfa3';

    case Pdfa3a = 'pdfa3a';

    case Pdfa3b = 'pdfa3b';

    case Pdfa3u = 'pdfa3u';

    case Pdfx = 'pdfx';

    case Pdfx1a = 'pdfx1a';

    case Pdfx3 = 'pdfx3';

    case Pdfx4 = 'pdfx4';

    case Pdfx5 = 'pdfx5';

    case Pdfua = 'pdfua';

    case Pdfua1 = 'pdfua1';

    case Pdfua2 = 'pdfua2';

    /**
     * Resolve a loose conformance value to the matching enum case.
     *
     * Accepts the mode identifier (case-insensitive, surrounding whitespace
     * trimmed) or an enum instance (returned unchanged). Any unrecognized value
     * falls back to None (no special conformance), matching setPDFMode().
     *
     * @param string|self $value Conformance mode or enum case.
     */
    public static function fromLoose(string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom(\trim(\strtolower($value))) ?? self::None;
    }
}
