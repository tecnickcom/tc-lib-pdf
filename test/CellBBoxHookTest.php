<?php

/**
 * CellBBoxHookTest.php
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

/**
 * The cell bounding box hook.
 *
 * @since     2026-11-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class CellBBoxHookTest extends TestUtil
{
    /**
     * Every drawn cell passes its box to pushCellBBox(), and the list keeps only the last one.
     *
     * @throws \Throwable
     */
    public function testEveryCellBoxGoesThroughTheHook(): void
    {
        self::setUpFontsPath();
        $pdf = new class extends \Com\Tecnick\Pdf\Tcpdf {
            /** @var array<int, array{x: float, y: float, w: float, h: float}> */
            public array $pushed = [];

            protected function pushCellBBox(array $box): void
            {
                parent::pushCellBBox($box);
                $this->pushed[] = $box;
            }
        };
        $this->initFontAndPage($pdf);

        $pdf->page->addContent($pdf->getTextCell('one', 10, 10, 50));
        $pdf->page->addContent($pdf->getTextCell('two', 10, 20, 50));
        $pdf->addTextCell("three\nfour", -1, 10, 30, 50);

        $this->assertCount(3, $pdf->pushed);
        $last = $pdf->pushed[2] ?? null;
        $this->assertNotNull($last);
        $this->assertSame($last, $pdf->getLastCellBBox());
        /** @var array<int, mixed> $list */
        $list = $this->getObjectProperty($pdf, 'cellbbox');
        $this->assertCount(1, $list);
        $this->assertSame([3], \array_keys($list));
    }
}
