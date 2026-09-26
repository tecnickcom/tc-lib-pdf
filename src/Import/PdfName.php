<?php

declare(strict_types=1);

/**
 * PdfName.php
 *
 * @since     2026-09-26
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Import;

/**
 * Com\Tecnick\Pdf\Import\PdfName
 *
 * Serializes decoded PDF name objects (PDF 32000-1 7.3.5).
 *
 * @since     2026-09-26
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
final class PdfName
{
    /**
     * Regular characters in the 0x21-0x7E range that must be written as #xx escapes.
     *
     * @var string
     */
    private const ESCAPED_CHARS = '#%()/<>[]{}';

    /**
     * Return the PDF name token for a decoded name.
     *
     * Bytes outside 0x21-0x7E, the delimiters and the number sign are written as #xx.
     *
     * @param int|string $name Decoded name, without the leading solidus.
     *
     * @return string Name token, including the leading solidus.
     */
    public static function token(int|string $name): string
    {
        $name = (string) $name;
        $out = '/';
        $len = \strlen($name);
        for ($idx = 0; $idx < $len; ++$idx) {
            $chr = $name[$idx];
            $ord = \ord($chr);
            if ($ord < 0x21 || $ord > 0x7E || \str_contains(self::ESCAPED_CHARS, $chr)) {
                $out .= \sprintf('#%02X', $ord);
                continue;
            }

            $out .= $chr;
        }

        return $out;
    }
}
