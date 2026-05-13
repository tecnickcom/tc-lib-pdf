<?php

/**
 * TestableJavaScript.php
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

class TestableJavaScript extends \Com\Tecnick\Pdf\Tcpdf
{
    /**
     * @param array<string, mixed> $prp
     * @return array<string, mixed>
     */
    public function exposeGetAnnotOptFromJSProp(array $prp = []): array
    {
        return $this->getAnnotOptFromJSProp($prp);
    }

    public function exposeGetPDFDefFillColor(): string
    {
        return $this->getPDFDefFillColor();
    }

    /**
     * @param array<string, mixed> $opt
     * @param array<string, mixed> $jsp
     * @return array<string, mixed>
     */
    public function exposeMergeAnnotOptions(
        array $opt = ['subtype' => 'text'],
        array $jsp = [],
        string $color = '',
    ): array {
        $opt = \array_merge(['subtype' => 'text'], $opt);
        // @phpstan-ignore argument.type
        return $this->mergeAnnotOptions($opt, $jsp, $color);
    }
}
