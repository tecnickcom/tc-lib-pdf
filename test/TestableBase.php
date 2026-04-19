<?php

/**
 * TestableBase.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

/** @phpstan-import-type TRefUnitValues from \Com\Tecnick\Pdf\Base */
class TestableBase extends \Com\Tecnick\Pdf\Tcpdf
{
    /** @phpstan-param TRefUnitValues $ref */
    public function exposeGetUnitValuePoints(
        string|float|int $val,
        array $ref = self::REFUNITVAL,
        string $defunit = 'px',
    ): float {
        return $this->getUnitValuePoints($val, $ref, $defunit);
    }

    /** @phpstan-param TRefUnitValues $ref */
    public function exposeGetFontValuePoints(
        string|float|int $val,
        array $ref = self::REFUNITVAL,
        string $defunit = 'pt',
    ): float {
        return $this->getFontValuePoints($val, $ref, $defunit);
    }

    public function exposeSetTmpRTL(string $mode): void
    {
        $this->setTmpRTL($mode);
    }

    public function exposeIsRTL(): bool
    {
        return $this->isRTL();
    }
}
