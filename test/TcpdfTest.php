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

class TestableTcpdf extends \Com\Tecnick\Pdf\Tcpdf
{
    public function exposeEnableSignatureApproval(bool $enabled = true): static
    {
        return $this->enableSignatureApproval($enabled);
    }
}

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

    /** @return array{pid: int} */
    private function initFontAndAddRawPage(\Com\Tecnick\Pdf\Tcpdf $obj): array
    {
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        /** @var array{pid: int} $rawPage */
        $rawPage = $page->add([]);
        return $rawPage;
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

        /** @var array{r: string, p: string, m: string} $regexp */
        $regexp = $this->getObjectProperty($obj, 'spaceregexp');
        $this->assertSame('/abc/i', $regexp['r']);
        $this->assertSame('abc', $regexp['p']);
        $this->assertSame('i', $regexp['m']);
    }

    public function testSetDisplayModeDefaultsInvalidZoomToDefault(): void
    {
        $obj = $this->getTestObject();
        $ret = $obj->setDisplayMode('invalid-zoom-token');

        /** @var array{zoom: string|int|float} $display */
        $display = $this->getObjectProperty($obj, 'display');
        $this->assertSame($obj, $ret);
        $this->assertSame('default', $display['zoom']);
    }

    public function testSetDisplayModeAcceptsNumericZoom(): void
    {
        $obj = $this->getTestObject();
        $obj->setDisplayMode(125);

        /** @var array{zoom: string|int|float} $display */
        $display = $this->getObjectProperty($obj, 'display');
        $this->assertSame(125, $display['zoom']);
    }

    public function testSetUserRightsMergesValues(): void
    {
        $obj = $this->getTestObject();
        $obj->setUserRights([
            'annots' => '/All',
            'document' => '/FullSave',
            'ef' => '/All',
            'enabled' => true,
            'form' => '/All',
            'formex' => '/All',
            'signature' => '/All',
        ]);

        /** @var array{enabled: bool, document: string} $rights */
        $rights = $this->getObjectProperty($obj, 'userrights');
        $this->assertTrue($rights['enabled']);
        $this->assertSame('/FullSave', $rights['document']);
    }

    public function testSetSignTimeStampThrowsWhenEnabledWithoutHost(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->setSignTimeStamp([
            'enabled' => true,
            'host' => '',
            'username' => '',
            'password' => '',
            'cert' => '',
        ]);
    }

    public function testSetSignTimeStampStoresDataWhenHostProvided(): void
    {
        $obj = $this->getTestObject();
        $obj->setSignTimeStamp([
            'enabled' => true,
            'host' => 'https://tsa.example.test',
            'username' => '',
            'password' => '',
            'cert' => '',
        ]);

        /** @var array{enabled: bool, host: string} $timeStamp */
        $timeStamp = $this->getObjectProperty($obj, 'sigtimestamp');
        $this->assertTrue($timeStamp['enabled']);
        $this->assertSame('https://tsa.example.test', $timeStamp['host']);
    }

    public function testSetSignatureThrowsOnMissingSigningCertificate(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->setSignature([
            'appearance' => ['empty' => [], 'name' => '', 'page' => 0, 'rect' => ''],
            'approval' => '',
            'cert_type' => 0,
            'extracerts' => null,
            'info' => ['ContactInfo' => '', 'Location' => '', 'Name' => '', 'Reason' => ''],
            'password' => '',
            'privkey' => '',
            'signcert' => '',
        ]);
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

        /** @var array{appearance: array{page: int, name: string, rect: string}} $signature */
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

        /** @var array{appearance: array{empty: array<int, array{name: string, page: int, objid: int}>}} $signature */
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

        /** @var array<int, array<string, mixed>> $outlines */
        $outlines = $this->getObjectProperty($obj, 'outlines');
        $this->assertCount(0, $outlines);
    }

    public function testAddTOCProcessesBookmarks(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);
        $obj->setBookmark('Section 1', '', 0, $page['pid']);
        $obj->addTOC($page['pid']);

        /** @var array<int, array{t: string, p: int}> $outlines */
        $outlines = $this->getObjectProperty($obj, 'outlines');
        $this->assertCount(1, $outlines);
        $this->assertSame('Section 1', $outlines[0]['t']);
        $this->assertSame($page['pid'], $outlines[0]['p']);
    }

    public function testNewLayerReturnsBeginLayerOperatorAndStoresLayer(): void
    {
        $obj = $this->getTestObject();
        $out = $obj->newLayer('Layer 1', ['view' => true], true, true, true);

        /** @var array<int, array{name: string, intent: string}> $layers */
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

    public function testConstructorPdfaModesSetExpectedFlags(): void
    {
        $pdfa1u = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfa1u');
        $this->assertSame(1, $this->getObjectProperty($pdfa1u, 'pdfa'));
        $this->assertSame('B', $this->getObjectProperty($pdfa1u, 'pdfaConformance'));

        $pdfa2u = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfa2u');
        $this->assertSame(2, $this->getObjectProperty($pdfa2u, 'pdfa'));
        $this->assertSame('U', $this->getObjectProperty($pdfa2u, 'pdfaConformance'));

        $pdfx = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx');
        $this->assertTrue($this->getObjectProperty($pdfx, 'pdfx'));
    }

    public function testConstructorWithUnicodeDisabledSetsAsciiWhitespacePattern(): void
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', false, false, true);

        /** @var array{r: string} $regexp */
        $regexp = $this->getObjectProperty($obj, 'spaceregexp');
        $this->assertSame('/[^\S\xa0]/', $regexp['r']);
    }

    public function testSetSignatureSetsDefaultPrivkeyAndSignFlag(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);

        $obj->setSignature([
            'appearance' => [
                'empty' => [
                    [
                        'objid' => 321,
                        'name' => 'EmptySig',
                        'page' => $page['pid'],
                        'rect' => '0 0 1 1',
                    ],
                ],
                'name' => 'MainSig',
                'page' => $page['pid'],
                'rect' => '0 0 10 5',
            ],
            'approval' => '',
            'cert_type' => 2,
            'extracerts' => '',
            'info' => ['ContactInfo' => '', 'Location' => '', 'Name' => '', 'Reason' => ''],
            'password' => '',
            'privkey' => '',
            'signcert' => 'dummy-signcert',
        ]);

        /** @var array{privkey: string, signcert: string} $signature */
        $signature = $this->getObjectProperty($obj, 'signature');
        $this->assertSame('dummy-signcert', $signature['privkey']);
        $this->assertTrue($this->getObjectProperty($obj, 'sign'));
    }

    public function testEnableSignatureApprovalTogglesFlag(): void
    {
        $obj = new TestableTcpdf();

        $ret = $obj->exposeEnableSignatureApproval(true);
        $this->assertSame($obj, $ret);
        $this->assertTrue($this->getObjectProperty($obj, 'sigapp'));

        $obj->exposeEnableSignatureApproval(false);
        $this->assertFalse($this->getObjectProperty($obj, 'sigapp'));
    }

    public function testAddTOCSupportsRtlPositioningAndBookmarkColor(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);
        $obj->setBookmark('RTL entry', '', 0, $page['pid'], 0, 0, '', 'red');

        $obj->addTOC($page['pid'], 0, 0, -1, true);

        /** @var array<int, array{t: string}> $outlines */
        $outlines = $this->getObjectProperty($obj, 'outlines');
        $this->assertCount(1, $outlines);
        $this->assertSame('RTL entry', $outlines[0]['t']);
    }
}
