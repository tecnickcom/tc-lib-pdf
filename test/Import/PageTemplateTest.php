<?php

/**
 * PageTemplateTest.php
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

namespace Test\Import;

use Com\Tecnick\Pdf\Import\PageTemplate;
use PHPUnit\Framework\TestCase;

class PageTemplateTest extends TestCase
{
    public function testGettersReturnConstructorValues(): void
    {
        $mediaBox = [0.0, 0.0, 612.0, 792.0];
        $template = new PageTemplate('TPL42', 612.0, 792.0, 90, 'source-abc', 3, $mediaBox);

        $this->assertSame('TPL42', $template->getXobjId());
        $this->assertSame(612.0, $template->getWidth());
        $this->assertSame(792.0, $template->getHeight());
        $this->assertSame(90, $template->getRotation());
        $this->assertSame('source-abc', $template->getSourceId());
        $this->assertSame(3, $template->getSourcePage());
        $this->assertSame($mediaBox, $template->getMediaBox());
    }

    public function testReadonlyPropertiesMirrorGetters(): void
    {
        $mediaBox = [10.0, 20.0, 210.0, 297.0];
        $template = new PageTemplate('TPL99', 200.0, 277.0, 0, 'source-def', 12, $mediaBox);

        $this->assertSame($template->xobjId, $template->getXobjId());
        $this->assertSame($template->width, $template->getWidth());
        $this->assertSame($template->height, $template->getHeight());
        $this->assertSame($template->rotation, $template->getRotation());
        $this->assertSame($template->sourceId, $template->getSourceId());
        $this->assertSame($template->sourcePage, $template->getSourcePage());
        $this->assertSame($template->mediaBox, $template->getMediaBox());
    }
}
