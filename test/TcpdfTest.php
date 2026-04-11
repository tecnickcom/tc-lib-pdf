<?php

/**
 * TcpdfTest.php
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

/**
 * Tcpdf Pdf class test
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class TcpdfTest extends TestUtil
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

    private function initFontAndAddRawPage(\Com\Tecnick\Pdf\Tcpdf $obj): array
    {
        $font = $this->getObjectProperty($obj, 'font');
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);

        $page = $this->getObjectProperty($obj, 'page');
        return $page->add([]);
    }

    public function testSetPDFFilenameAcceptsValidPdfName(): void
    {
        $obj = $this->getTestObject();
        $obj->setPDFFilename('my_test_file.pdf');

        $this->assertSame('my_test_file.pdf', $this->getObjectProperty($obj, 'pdffilename'));
        $this->assertSame('my_test_file.pdf', $this->getObjectProperty($obj, 'encpdffilename'));
    }

    public function testSetPDFFilenameRejectsInvalidExtension(): void
    {
        $obj = $this->getTestObject();
        $before = $this->getObjectProperty($obj, 'pdffilename');

        $obj->setPDFFilename('bad-name.txt');

        $this->assertSame($before, $this->getObjectProperty($obj, 'pdffilename'));
    }

    public function testSetSpaceRegexpParsesPatternAndModifiers(): void
    {
        $obj = $this->getTestObject();
        $obj->setSpaceRegexp('/abc/i');

        $regexp = $this->getObjectProperty($obj, 'spaceregexp');
        $this->assertSame('/abc/i', $regexp['r']);
        $this->assertSame('abc', $regexp['p']);
        $this->assertSame('i', $regexp['m']);
    }

    public function testSetDisplayModeDefaultsInvalidZoomToDefault(): void
    {
        $obj = $this->getTestObject();
        $ret = $obj->setDisplayMode('invalid-zoom-token');

        $display = $this->getObjectProperty($obj, 'display');
        $this->assertSame($obj, $ret);
        $this->assertSame('default', $display['zoom']);
    }

    public function testSetDisplayModeAcceptsNumericZoom(): void
    {
        $obj = $this->getTestObject();
        $obj->setDisplayMode(125);

        $display = $this->getObjectProperty($obj, 'display');
        $this->assertSame(125, $display['zoom']);
    }

    public function testSetUserRightsMergesValues(): void
    {
        $obj = $this->getTestObject();
        $obj->setUserRights(['enabled' => true, 'document' => '/FullSave']);

        $rights = $this->getObjectProperty($obj, 'userrights');
        $this->assertTrue($rights['enabled']);
        $this->assertSame('/FullSave', $rights['document']);
    }

    public function testSetSignTimeStampThrowsWhenEnabledWithoutHost(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->setSignTimeStamp(['enabled' => true, 'host' => '']);
    }

    public function testSetSignTimeStampStoresDataWhenHostProvided(): void
    {
        $obj = $this->getTestObject();
        $obj->setSignTimeStamp(['enabled' => true, 'host' => 'https://tsa.example.test']);

        $ts = $this->getObjectProperty($obj, 'sigtimestamp');
        $this->assertTrue($ts['enabled']);
        $this->assertSame('https://tsa.example.test', $ts['host']);
    }

    public function testSetSignatureThrowsOnMissingSigningCertificate(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->setSignature(['signcert' => '']);
    }

    public function testGetBarcodeReturnsDrawingCommands(): void
    {
        $obj = $this->getTestObject();
        $out = $obj->getBarcode('C39', 'ABC123');

        $this->assertNotSame('', $out);
        $this->bcAssertMatchesRegularExpression('/\bre\b/', $out);
    }

    public function testGetBarcodeThrowsOnInvalidType(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);

        $obj->getBarcode('INVALID_TYPE', 'ABC123');
    }

    public function testSetSignatureAppearanceStoresPageNameAndRect(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);
        $obj->setSignatureAppearance(10, 20, 30, 15, $page['pid'], 'MainSig');

        $signature = $this->getObjectProperty($obj, 'signature');
        $appearance = $signature['appearance'];

        $this->assertSame($page['pid'], $appearance['page']);
        $this->assertSame('MainSig', $appearance['name']);
        $this->bcAssertMatchesRegularExpression('/^[-0-9.]+\s+[-0-9.]+\s+[-0-9.]+\s+[-0-9.]+$/', $appearance['rect']);
    }

    public function testAddEmptySignatureAppearanceAddsEntry(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);
        $obj->addEmptySignatureAppearance(5, 6, 20, 10, $page['pid'], 'EmptySig');

        $signature = $this->getObjectProperty($obj, 'signature');
        $this->assertNotEmpty($signature['appearance']['empty']);
        $entry = $signature['appearance']['empty'][0];
        $this->assertSame('EmptySig', $entry['name']);
        $this->assertSame($page['pid'], $entry['page']);
        $this->assertIsInt($entry['objid']);
    }

    public function testAddTOCHandlesEmptyOutlineList(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndAddRawPage($obj);
        $obj->addTOC();

        $outlines = $this->getObjectProperty($obj, 'outlines');
        $this->assertCount(0, $outlines);
    }

    public function testAddTOCProcessesBookmarks(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);
        $obj->setBookmark('Section 1', '', 0, $page['pid']);
        $obj->addTOC($page['pid']);

        $outlines = $this->getObjectProperty($obj, 'outlines');
        $this->assertCount(1, $outlines);
        $this->assertSame('Section 1', $outlines[0]['t']);
        $this->assertSame($page['pid'], $outlines[0]['p']);
    }

    public function testNewLayerReturnsBeginLayerOperatorAndStoresLayer(): void
    {
        $obj = $this->getTestObject();
        $out = $obj->newLayer('Layer 1', ['view' => true], true, true, true);

        $layers = $this->getObjectProperty($obj, 'pdflayer');
        $this->assertSame(" /OC /LYR001 BDC\n", $out);
        $this->assertCount(1, $layers);
        $this->assertSame('Layer1', $layers[0]['name']);
        $this->assertSame('/View', $layers[0]['intent']);
    }

    public function testCloseLayerReturnsEmcOperator(): void
    {
        $obj = $this->getTestObject();
        $this->assertSame("EMC\n", $obj->closeLayer());
    }
}
