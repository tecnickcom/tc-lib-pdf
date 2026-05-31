<?php

/**
 * TestablePdfColor.php
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

class TestablePdfColor extends \Com\Tecnick\Pdf\PdfColor
{
    /** @return array{0: string, 1: float}|null */
    public function exposeParseSpotCssFunction(string $color): ?array
    {
        return $this->parseSpotCssFunction($color);
    }

    public function exposeParseSpotNameToken(#[\SensitiveParameter] string $token): string
    {
        return $this->parseSpotNameToken($token);
    }

    public function exposeParseSpotTintToken(#[\SensitiveParameter] string $token): ?float
    {
        return $this->parseSpotTintToken($token);
    }

    public function exposeGetLabProcessColor(string $color): ?\Com\Tecnick\Color\Model\Lab
    {
        return $this->getLabProcessColor($color);
    }

    public function exposeGetPdfLabProcessColor(\Com\Tecnick\Color\Model\Lab $labColor, bool $stroke): string
    {
        return $this->getPdfLabProcessColor($labColor, $stroke);
    }
}
