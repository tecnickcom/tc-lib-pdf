<?php

/**
 * ClassObjectsTest.php
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

class ClassObjectsTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    private function getObjectProperty(object $obj, string $name): mixed
    {
        $ref = new \ReflectionClass($obj);
        while ($ref !== false) {
            if ($ref->hasProperty($name)) {
                $prop = $ref->getProperty($name);
                $prop->setAccessible(true);
                return $prop->getValue($obj);
            }
            $ref = $ref->getParentClass();
        }

        $this->fail('Property not found: ' . $name);
    }

    public function testInitClassObjectsInitializesDependencies(): void
    {
        $obj = $this->getTestObject();
        $obj->initClassObjects();

        $this->assertInstanceOf(\Com\Tecnick\Pdf\Encrypt\Encrypt::class, $this->getObjectProperty($obj, 'encrypt'));
        $this->assertInstanceOf(\Com\Tecnick\Color\Pdf::class, $this->getObjectProperty($obj, 'color'));
        $this->assertInstanceOf(\Com\Tecnick\Barcode\Barcode::class, $this->getObjectProperty($obj, 'barcode'));
        $this->assertInstanceOf(\Com\Tecnick\Pdf\Page\Page::class, $this->getObjectProperty($obj, 'page'));
        $this->assertInstanceOf(\Com\Tecnick\Pdf\Graph\Draw::class, $this->getObjectProperty($obj, 'graph'));
        $this->assertInstanceOf(\Com\Tecnick\Pdf\Font\Stack::class, $this->getObjectProperty($obj, 'font'));
        $this->assertInstanceOf(\Com\Tecnick\Pdf\Image\Import::class, $this->getObjectProperty($obj, 'image'));
    }

    public function testInitClassObjectsUsesProvidedEncryptObject(): void
    {
        $obj = $this->getTestObject();
        $enc = new \Com\Tecnick\Pdf\Encrypt\Encrypt();

        $obj->initClassObjects($enc);

        $this->assertSame($enc, $this->getObjectProperty($obj, 'encrypt'));
    }
}
