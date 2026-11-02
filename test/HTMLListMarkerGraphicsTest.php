<?php

/**
 * HTMLListMarkerGraphicsTest.php
 *
 * @since     2026-11-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Graphics state of the vector list markers.
 *
 * @since     2026-11-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class HTMLListMarkerGraphicsTest extends TestUtil
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function unitProvider(): array
    {
        return ['mm' => ['mm'], 'pt' => ['pt'], 'in' => ['in']];
    }

    /**
     * The disc, circle and square markers keep the miter limit of the graphics state.
     *
     * @throws \Throwable
     */
    #[DataProvider('unitProvider')]
    public function testVectorMarkersSetNoMiterLimit(string $unit): void
    {
        self::setUpFontsPath();
        $pdf = new \Com\Tecnick\Pdf\Tcpdf(unit: $unit, compress: false);
        $this->initFontAndPage($pdf);
        $pdf->addHTMLCell(
            '<ul><li>Outer<ul><li>Inner<ul><li>Third</li></ul></li></ul></li></ul>'
            . '<ul style="list-style-type: square"><li>Square</li></ul>',
            1,
            1,
            3,
        );
        $out = $pdf->getOutPDFString();

        $this->assertSame(0, \preg_match_all('/(?:^|\s)[-0-9.]+ M\s/', $out));
        // the markers are drawn as filled and stroked paths
        $this->assertMatchesRegularExpression('/\sf\s/', $out);
    }
}
