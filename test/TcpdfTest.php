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

use PHPUnit\Framework\Attributes\DataProvider;

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

    /** @return array{pid: int} */
    private function initFontAndAddRawPage(\Com\Tecnick\Pdf\Tcpdf $obj): array
    {
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(
            __DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json'
        );
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

    public function testConstructorAlignsFileIdWithInjectedEncryptionObject(): void
    {
        $fileid = \md5('tcpdf-encryption-fileid');
        $enc = new \Com\Tecnick\Pdf\Encrypt\Encrypt(
            true,
            $fileid,
            2,
            ['modify', 'copy'],
            'demo-user',
            'demo-owner'
        );

        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', $enc);

        $this->assertSame($fileid, $this->getObjectProperty($obj, 'fileid'));
    }

    public function testConstructorPassesFileOptionsToSharedFileHelper(): void
    {
        $defaultCurlOpts = [
            CURLOPT_TIMEOUT => 12,
            CURLOPT_USERAGENT => 'tc-lib-pdf-test',
        ];
        $fixedCurlOpts = [
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
        ];
        $allowedHosts = ['localhost', 'example.test'];

        $obj = new \Com\Tecnick\Pdf\Tcpdf(
            'mm',
            true,
            false,
            true,
            '',
            null,
            [
                'defaultCurlOpts' => $defaultCurlOpts,
                'fixedCurlOpts' => $fixedCurlOpts,
                'allowedHosts' => $allowedHosts,
            ]
        );

        /** @var \Com\Tecnick\File\File $file */
        $file = $this->getObjectProperty($obj, 'file');
        /** @var \Com\Tecnick\Pdf\Image\Import $image */
        $image = $this->getObjectProperty($obj, 'image');
        /** @var \Com\Tecnick\File\File $imageFile */
        $imageFile = $this->getObjectProperty($image, 'file');

        $this->assertSame($allowedHosts, $this->getObjectProperty($file, 'allowedHosts'));
        $this->assertSame($defaultCurlOpts, $this->getObjectProperty($file, 'defaultCurlOpts'));
        $this->assertSame($fixedCurlOpts, $this->getObjectProperty($file, 'fixedCurlOpts'));
        $this->assertSame($allowedHosts, $this->getObjectProperty($imageFile, 'allowedHosts'));
        $this->assertSame($defaultCurlOpts, $this->getObjectProperty($imageFile, 'defaultCurlOpts'));
        $this->assertSame($fixedCurlOpts, $this->getObjectProperty($imageFile, 'fixedCurlOpts'));
    }

    #[DataProvider('displayModeFixtureProvider')]
    public function testSetDisplayModeStoresExpectedZoom(string|int $inputZoom, string|int $expectedZoom): void
    {
        $obj = $this->getTestObject();
        $ret = $obj->setDisplayMode($inputZoom);

        /** @var array{zoom: string|int|float} $display */
        $display = $this->getObjectProperty($obj, 'display');
        $this->assertSame($obj, $ret);
        $this->assertSame($expectedZoom, $display['zoom']);
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

    /** @return array<string, array{0: string|int, 1: string|int}> */
    public static function displayModeFixtureProvider(): array
    {
        return [
            'invalid_token_uses_default' => ['invalid-zoom-token', 'default'],
            'numeric_zoom_is_preserved' => [125, 125],
        ];
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
            'hash_algorithm' => 'sha256',
            'policy_oid' => '',
            'nonce_enabled' => true,
            'timeout' => 5,
            'verify_peer' => true,
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
            'hash_algorithm' => 'sha512',
            'policy_oid' => '1.2.3.4',
            'nonce_enabled' => false,
            'timeout' => 9,
            'verify_peer' => false,
        ]);

        /** @var array{enabled: bool, host: string, hash_algorithm: string, timeout: int, verify_peer: bool} $timeStamp */
        $timeStamp = $this->getObjectProperty($obj, 'sigtimestamp');
        $this->assertTrue($timeStamp['enabled']);
        $this->assertSame('https://tsa.example.test', $timeStamp['host']);
        $this->assertSame('sha512', $timeStamp['hash_algorithm']);
        $this->assertSame(9, $timeStamp['timeout']);
        $this->assertFalse($timeStamp['verify_peer']);
    }

    public function testSetSignTimeStampThrowsOnInvalidHashAlgorithm(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->setSignTimeStamp([
            'enabled' => true,
            'host' => 'https://tsa.example.test',
            'username' => '',
            'password' => '',
            'cert' => '',
            'hash_algorithm' => 'sha1',
            'policy_oid' => '',
            'nonce_enabled' => true,
            'timeout' => 5,
            'verify_peer' => true,
        ]);
    }

    public function testSetSignatureStoresLtvOptions(): void
    {
        $obj = $this->getTestObject();

        $obj->setSignature([
            'appearance' => ['empty' => [], 'name' => '', 'page' => 0, 'rect' => ''],
            'approval' => '',
            'cert_type' => 0,
            'extracerts' => null,
            'info' => ['ContactInfo' => '', 'Location' => '', 'Name' => '', 'Reason' => ''],
            'password' => '',
            'privkey' => '',
            'signcert' => 'dummy-signcert',
            'ltv' => [
                'enabled' => true,
                'embed_ocsp' => false,
                'embed_crl' => false,
                'embed_certs' => true,
                'include_dss' => true,
                'include_vri' => true,
            ],
        ]);

        /** @var array{ltv: array{enabled: bool, embed_ocsp: bool, embed_crl: bool}} $signature */
        $signature = $this->getObjectProperty($obj, 'signature');
        $this->assertTrue($signature['ltv']['enabled']);
        $this->assertFalse($signature['ltv']['embed_ocsp']);
        $this->assertFalse($signature['ltv']['embed_crl']);
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

    public function testNewLayerFallsBackToAutoNameAndAddsDesignIntent(): void
    {
        $obj = $this->getTestObject();
        $out = $obj->newLayer('***', ['design' => true], true, true, true);

        /** @var array<int, array{name: string, intent: string}> $layers */
        $layers = $this->getObjectProperty($obj, 'pdflayer');
        $this->assertSame(" /OC /LYR001 BDC\n", $out);
        $this->assertCount(1, $layers);
        $this->assertSame('LYR001', $layers[0]['name']);
        $this->assertSame('/Design', $layers[0]['intent']);
    }

    public function testConstructorPdfaModesSetExpectedFlags(): void
    {
        $pdfa1u = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfa1u');
        $this->assertSame(1, $this->getObjectProperty($pdfa1u, 'pdfa'));
        $this->assertSame('B', $this->getObjectProperty($pdfa1u, 'pdfaConformance'));

        $pdfa2u = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfa2u');
        $this->assertSame(2, $this->getObjectProperty($pdfa2u, 'pdfa'));
        $this->assertSame('U', $this->getObjectProperty($pdfa2u, 'pdfaConformance'));

        foreach (['pdfx', 'pdfx1a', 'pdfx3', 'pdfx4', 'pdfx5'] as $mode) {
            $pdfx = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, $mode);
            $this->assertTrue($this->getObjectProperty($pdfx, 'pdfx'));
            $this->assertSame($mode, $this->getObjectProperty($pdfx, 'pdfxMode'));
            $this->assertSame('', $this->getObjectProperty($pdfx, 'pdfuaMode'));
        }

        foreach (['pdfua', 'pdfua1', 'pdfua2'] as $mode) {
            $pdfua = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, $mode);
            $this->assertSame($mode, $this->getObjectProperty($pdfua, 'pdfuaMode'));
            $this->assertFalse($this->getObjectProperty($pdfua, 'pdfx'));
            $this->assertSame('', $this->getObjectProperty($pdfua, 'pdfxMode'));
        }

        $unknown = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfunknown');
        $this->assertFalse($this->getObjectProperty($unknown, 'pdfx'));
        $this->assertSame('', $this->getObjectProperty($unknown, 'pdfxMode'));
        $this->assertSame('', $this->getObjectProperty($unknown, 'pdfuaMode'));
        $this->assertSame(0, $this->getObjectProperty($unknown, 'pdfa'));
    }

    public function testConstructorWithUnicodeDisabledSetsAsciiWhitespacePattern(): void
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', false, false, true);

        /** @var array{r: string} $regexp */
        $regexp = $this->getObjectProperty($obj, 'spaceregexp');
        $this->assertSame('/[^\S\xa0]/', $regexp['r']);
    }

    public function testPdfxRestrictiveModesForceDeviceCmykProcessColors(): void
    {
        foreach (['pdfx', 'pdfx1a', 'pdfx3'] as $mode) {
            $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, $mode);

            $fill = $obj->color->getPdfColor('#336699');
            $stroke = $obj->color->getPdfColor('#336699', true);

            $this->assertStringContainsString(" k\n", $fill);
            $this->assertStringNotContainsString(" rg\n", $fill);
            $this->assertStringContainsString(" K\n", $stroke);
            $this->assertStringNotContainsString(" RG\n", $stroke);

            // Spot colors remain spot operators in restrictive PDF/X modes.
            $spot = $obj->color->getPdfColor('cyan');
            $this->assertStringContainsString('scn', $spot);
            $this->assertStringContainsString('/CS', $spot);
        }
    }

    public function testDefaultAndPdfx4KeepRgbProcessColors(): void
    {
        $default = new \Com\Tecnick\Pdf\Tcpdf();
        $pdfx4 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx4');

        $this->assertStringContainsString(" rg\n", $default->color->getPdfColor('#336699'));
        $this->assertStringContainsString(" rg\n", $pdfx4->color->getPdfColor('#336699'));
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

    public function testSetSignAnnotRefsReturnsWhenNoEmptySignaturesAreDefined(): void
    {
        $obj = new TestableTcpdf();
        $page = $this->initFontAndAddRawPage($obj);

        $this->setObjectProperty($obj, 'objid', ['signature' => 123]);
        $this->setObjectProperty($obj, 'signature', [
            'appearance' => [
                'empty' => [],
                'name' => 'MainSig',
                'page' => $page['pid'],
                'rect' => '0 0 10 10',
            ],
            'approval' => false,
            'cert_type' => 0,
            'extracerts' => '',
            'info' => ['ContactInfo' => '', 'Location' => '', 'Name' => '', 'Reason' => ''],
            'password' => '',
            'privkey' => '',
            'signcert' => '',
        ]);

        $obj->exposeSetSignAnnotRefs();

        /** @var array{appearance: array{empty: array<int, mixed>, page: int}} $signature */
        $signature = $this->getObjectProperty($obj, 'signature');
        $this->assertSame([], $signature['appearance']['empty']);
        $this->assertSame($page['pid'], $signature['appearance']['page']);
    }

    public function testSetSignatureAppearanceAddsMainSignatureAnnotationReference(): void
    {
        $obj = new TestableTcpdf();
        $this->initFontAndAddRawPage($obj);
        $page = $this->initFontAndAddRawPage($obj);

        $this->setObjectProperty($obj, 'objid', ['signature' => 321]);
        $obj->setSignatureAppearance(1, 2, 3, 4, $page['pid'], 'MainSig');

        /** @var array{appearance: array{page: int, name: string}} $signature */
        $signature = $this->getObjectProperty($obj, 'signature');
        $this->assertSame($page['pid'], $signature['appearance']['page']);
        $this->assertSame('MainSig', $signature['appearance']['name']);
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

    public function testAddTOCHandlesRegionOverflowAndPageTransitions(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);
        $obj->setBookmark(\str_repeat('Long TOC line ', 250), '', 0, $page['pid']);

        $obj->addTOC($page['pid'], 0, 10000, 20, false);

        /** @var array<int, array{t: string}> $outlines */
        $outlines = $this->getObjectProperty($obj, 'outlines');
        $this->assertCount(1, $outlines);
        $this->assertStringStartsWith('Long TOC line', $outlines[0]['t']);
    }

    public function testLanguageSettersUpdateLanguageMetadata(): void
    {
        $obj = $this->getTestObject();

        $obj->setLanguageArray(['a_meta_language' => 'it-IT', 'custom' => 'x']);
        /** @var array<string, string> $lang */
        $lang = $this->getObjectProperty($obj, 'lang');
        $this->assertSame('it-IT', $lang['a_meta_language']);
        $this->assertSame('x', $lang['custom']);

        $obj->setLanguage('en-US');
        /** @var array<string, string> $updated */
        $updated = $this->getObjectProperty($obj, 'lang');
        $this->assertSame('en-US', $updated['a_meta_language']);
    }

    public function testSetSignatureAddsDefaultLtvOptionsWhenMissing(): void
    {
        $obj = $this->getTestObject();

        /** @var array<string, mixed> $signatureState */
        $signatureState = $this->getObjectProperty($obj, 'signature');
        unset($signatureState['ltv']);
        $this->setObjectProperty($obj, 'signature', $signatureState);

        $obj->setSignature([
            'appearance' => ['empty' => [], 'name' => '', 'page' => 0, 'rect' => ''],
            'approval' => '',
            'cert_type' => 0,
            'extracerts' => null,
            'info' => ['ContactInfo' => '', 'Location' => '', 'Name' => '', 'Reason' => ''],
            'password' => '',
            'privkey' => '',
            'signcert' => 'dummy-signcert',
        ]);

        /** @var array{ltv: array{enabled: bool, embed_ocsp: bool, embed_crl: bool, embed_certs: bool, include_dss: bool, include_vri: bool}} $signature */
        $signature = $this->getObjectProperty($obj, 'signature');
        $this->assertFalse($signature['ltv']['enabled']);
        $this->assertTrue($signature['ltv']['embed_ocsp']);
        $this->assertTrue($signature['ltv']['embed_crl']);
        $this->assertTrue($signature['ltv']['embed_certs']);
        $this->assertTrue($signature['ltv']['include_dss']);
        $this->assertTrue($signature['ltv']['include_vri']);
    }

    public function testSetSignatureThrowsWhenLtvIsNotArray(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $data = [
            'appearance' => ['empty' => [], 'name' => '', 'page' => 0, 'rect' => ''],
            'approval' => '',
            'cert_type' => 0,
            'extracerts' => null,
            'info' => ['ContactInfo' => '', 'Location' => '', 'Name' => '', 'Reason' => ''],
            'password' => '',
            'privkey' => '',
            'signcert' => 'dummy-signcert',
            'ltv' => 'invalid',
        ];

        (new \ReflectionMethod($obj, 'setSignature'))->invoke($obj, $data);
    }

    public function testSetSignatureThrowsWhenLtvKeyIsInvalidType(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $data = [
            'appearance' => ['empty' => [], 'name' => '', 'page' => 0, 'rect' => ''],
            'approval' => '',
            'cert_type' => 0,
            'extracerts' => null,
            'info' => ['ContactInfo' => '', 'Location' => '', 'Name' => '', 'Reason' => ''],
            'password' => '',
            'privkey' => '',
            'signcert' => 'dummy-signcert',
            'ltv' => [
                'enabled' => true,
                'embed_ocsp' => true,
                'embed_crl' => true,
                'embed_certs' => true,
                'include_dss' => true,
                'include_vri' => 'yes',
            ],
        ];

        (new \ReflectionMethod($obj, 'setSignature'))->invoke($obj, $data);
    }

    public function testSetSignTimeStampThrowsOnInvalidPolicyOid(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->setSignTimeStamp([
            'enabled' => true,
            'host' => 'https://tsa.example.test',
            'username' => '',
            'password' => '',
            'cert' => '',
            'hash_algorithm' => 'sha256',
            'policy_oid' => 'invalid-oid',
            'nonce_enabled' => true,
            'timeout' => 5,
            'verify_peer' => true,
        ]);
    }

    public function testSetSignTimeStampThrowsOnInvalidNonceType(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $data = [
            'enabled' => true,
            'host' => 'https://tsa.example.test',
            'username' => '',
            'password' => '',
            'cert' => '',
            'hash_algorithm' => 'sha256',
            'policy_oid' => '',
            'nonce_enabled' => 1,
            'timeout' => 5,
            'verify_peer' => true,
        ];

        (new \ReflectionMethod($obj, 'setSignTimeStamp'))->invoke($obj, $data);
    }

    public function testSetSignTimeStampThrowsOnInvalidTimeout(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->setSignTimeStamp([
            'enabled' => true,
            'host' => 'https://tsa.example.test',
            'username' => '',
            'password' => '',
            'cert' => '',
            'hash_algorithm' => 'sha256',
            'policy_oid' => '',
            'nonce_enabled' => true,
            'timeout' => 0,
            'verify_peer' => true,
        ]);
    }

    public function testSetSignTimeStampThrowsOnInvalidVerifyPeerType(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $data = [
            'enabled' => true,
            'host' => 'https://tsa.example.test',
            'username' => '',
            'password' => '',
            'cert' => '',
            'hash_algorithm' => 'sha256',
            'policy_oid' => '',
            'nonce_enabled' => true,
            'timeout' => 5,
            'verify_peer' => 1,
        ];

        (new \ReflectionMethod($obj, 'setSignTimeStamp'))->invoke($obj, $data);
    }

    public function testPdfColorGetterAndInvalidColorFallback(): void
    {
        $default = $this->getTestObject();
        /** @var \Com\Tecnick\Pdf\PdfColor $defaultColor */
        $defaultColor = $this->getObjectProperty($default, 'color');
        $this->assertFalse($defaultColor->isForceDeviceCmyk());

        $pdfx = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx');
        /** @var \Com\Tecnick\Pdf\PdfColor $pdfxColor */
        $pdfxColor = $this->getObjectProperty($pdfx, 'color');
        $this->assertTrue($pdfxColor->isForceDeviceCmyk());
        $this->assertSame('', $pdfxColor->getPdfColor('not-a-color-value'));
    }
}
