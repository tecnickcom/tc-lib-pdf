<?php

/**
 * BaseInitClassObjectsTest.php
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

class BaseInitClassObjectsTest extends TestUtil
{
    /** @throws \Throwable */
    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testInitClassObjectsUsesProvidedEncryptObject(): void
    {
        $obj = $this->getTestObject();
        $enc = new \Com\Tecnick\Pdf\Encrypt\Encrypt();

        $obj->initClassObjects($enc);

        $this->assertSame($enc, $this->getObjectProperty($obj, 'encrypt'));
    }

    /** @throws \Throwable */
    public function testInitClassObjectsRaisesVersionForEncryptionV2(): void
    {
        $obj = $this->getTestObject();
        $this->setObjectProperty($obj, 'pdfver', '1.3');

        $enc = new class() extends \Com\Tecnick\Pdf\Encrypt\Encrypt {
            public function getEncryptionData(): array
            {
                $data = parent::getEncryptionData();
                $data['encrypted'] = true;
                $data['V'] = 2;
                return $data;
            }
        };

        $obj->initClassObjects($enc);

        $this->assertSame('1.4', $this->getObjectProperty($obj, 'pdfver'));
    }

    /** @throws \Throwable */
    public function testInitClassObjectsRaisesVersionForEncryptionLegacyMode(): void
    {
        $obj = $this->getTestObject();
        $this->setObjectProperty($obj, 'pdfver', '1.0');

        $enc = new class() extends \Com\Tecnick\Pdf\Encrypt\Encrypt {
            public function getEncryptionData(): array
            {
                $data = parent::getEncryptionData();
                $data['encrypted'] = true;
                $data['V'] = 1;
                return $data;
            }
        };

        $obj->initClassObjects($enc);

        $this->assertSame('1.1', $this->getObjectProperty($obj, 'pdfver'));
    }
}
