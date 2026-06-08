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
    /** @throws \Throwable */
    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    /**
     * @return array{pid: int}
     * @throws \Throwable
     */
    private function initFontAndAddRawPage(\Com\Tecnick\Pdf\Tcpdf $obj): array
    {
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(__DIR__
        . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        /** @var array<string, mixed> $rawPage */
        $rawPage = $page->add([]);
        if (!isset($rawPage['pid']) || !\is_int($rawPage['pid'])) {
            $this->fail('Unexpected page id type from add([]).');
        }
        $pid = $rawPage['pid'];

        return ['pid' => $pid];
    }

    /** @throws \Throwable */
    public function testSetPDFFilenameAcceptsValidPdfName(): void
    {
        $obj = $this->getTestObject();
        $obj->setPDFFilename('my_test_file.pdf');

        $this->assertSame('my_test_file.pdf', $this->getObjectProperty($obj, 'pdffilename'));
        $this->assertSame('my_test_file.pdf', $this->getObjectProperty($obj, 'encpdffilename'));
    }

    /** @throws \Throwable */
    public function testSetPDFFilenameAcceptsUnicodeName(): void
    {
        $obj = $this->getTestObject();
        $obj->setPDFFilename('Resume 日本語.pdf');

        $this->assertSame('Resume 日本語.pdf', $this->getObjectProperty($obj, 'pdffilename'));
        $this->assertSame('Resume%20%E6%97%A5%E6%9C%AC%E8%AA%9E.pdf', $this->getObjectProperty($obj, 'encpdffilename'));
    }

    /** @throws \Throwable */
    public function testSetPDFFilenameNormalizesUnicodeToNfcWhenAvailable(): void
    {
        $obj = $this->getTestObject();
        $obj->setPDFFilename("Cafe\u{0301}.pdf");

        $expectedName = \class_exists('\\Normalizer') ? 'Café.pdf' : "Cafe\u{0301}.pdf";
        $expectedEncoded = \rawurlencode($expectedName);

        $this->assertSame($expectedName, $this->getObjectProperty($obj, 'pdffilename'));
        $this->assertSame($expectedEncoded, $this->getObjectProperty($obj, 'encpdffilename'));
    }

    /** @throws \Throwable */
    public function testSetPDFFilenameRejectsInvalidExtension(): void
    {
        $obj = $this->getTestObject();
        $before = (string) $this->getObjectProperty($obj, 'pdffilename');

        $obj->setPDFFilename('bad-name.txt');

        $this->assertSame($before, $this->getObjectProperty($obj, 'pdffilename'));
    }

    /** @throws \Throwable */
    public function testSetPDFFilenameRejectsControlCharacters(): void
    {
        $obj = $this->getTestObject();
        $before = (string) $this->getObjectProperty($obj, 'pdffilename');

        $obj->setPDFFilename("bad\tname.pdf");

        $this->assertSame($before, $this->getObjectProperty($obj, 'pdffilename'));
    }

    /** @throws \Throwable */
    public function testSetPDFFilenameRejectsMarkOnlyStem(): void
    {
        $obj = $this->getTestObject();
        $before = (string) $this->getObjectProperty($obj, 'pdffilename');

        $obj->setPDFFilename("\u{0301}.pdf");

        $this->assertSame($before, $this->getObjectProperty($obj, 'pdffilename'));
    }

    /** @throws \Throwable */
    public function testSetPDFFilenameRejectsDetachedMarkAfterSeparator(): void
    {
        $obj = $this->getTestObject();
        $before = (string) $this->getObjectProperty($obj, 'pdffilename');

        $obj->setPDFFilename("a \u{0301}.pdf");

        $this->assertSame($before, $this->getObjectProperty($obj, 'pdffilename'));
    }

    /** @throws \Throwable */
    public function testSetPDFFilenameRejectsNamesLongerThan255Bytes(): void
    {
        $obj = $this->getTestObject();
        $before = (string) $this->getObjectProperty($obj, 'pdffilename');

        $obj->setPDFFilename(\str_repeat('a', 252) . '.pdf');

        $this->assertSame($before, $this->getObjectProperty($obj, 'pdffilename'));
    }

    /** @throws \Throwable */
    public function testSetPDFFilenameAcceptsNamesUpTo255Bytes(): void
    {
        $obj = $this->getTestObject();
        $name = \str_repeat('a', 251) . '.pdf';

        $obj->setPDFFilename($name);

        $this->assertSame($name, $this->getObjectProperty($obj, 'pdffilename'));
        $this->assertSame($name, $this->getObjectProperty($obj, 'encpdffilename'));
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testConstructorAlignsFileIdWithInjectedEncryptionObject(): void
    {
        $fileid = \md5('tcpdf-encryption-fileid');
        $enc = new \Com\Tecnick\Pdf\Encrypt\Encrypt(true, $fileid, 2, ['modify', 'copy'], 'demo-user', 'demo-owner');

        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', $enc);

        $this->assertSame($fileid, $this->getObjectProperty($obj, 'fileid'));
    }

    /** @throws \Throwable */
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

        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null, [
            'defaultCurlOpts' => $defaultCurlOpts,
            'fixedCurlOpts' => $fixedCurlOpts,
            'allowedHosts' => $allowedHosts,
        ]);

        /** @var \Com\Tecnick\File\File $file */
        $file = $this->getObjectProperty($obj, 'file');
        /** @var \Com\Tecnick\Pdf\Image\Import $image */
        $image = $this->getObjectProperty($obj, 'image');
        /** @var \Com\Tecnick\File\File $imageFile */
        $imageFile = $this->getObjectProperty($image, 'fileHelper');

        $this->assertSame($allowedHosts, $this->getObjectProperty($file, 'allowedHosts'));
        $this->assertSame($defaultCurlOpts, $this->getObjectProperty($file, 'defaultCurlOpts'));
        $this->assertSame($fixedCurlOpts, $this->getObjectProperty($file, 'fixedCurlOpts'));
        $this->assertSame($allowedHosts, $this->getObjectProperty($imageFile, 'allowedHosts'));
        $this->assertSame($defaultCurlOpts, $this->getObjectProperty($imageFile, 'defaultCurlOpts'));
        $this->assertSame($fixedCurlOpts, $this->getObjectProperty($imageFile, 'fixedCurlOpts'));

        /** @var array<string> $allowedPaths */
        $allowedPaths = $this->getObjectProperty($file, 'allowedPaths');
        $this->assertContains((string) \realpath(__DIR__ . '/..'), $allowedPaths);
    }

    /** @throws \Throwable */
    public function testConstructorUsesCustomAllowedPathsWithoutMergingDefaults(): void
    {
        $customDir = (string) \realpath(__DIR__ . '/fixtures');
        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null, [
            'allowedPaths' => [$customDir],
        ]);

        /** @var \Com\Tecnick\File\File $file */
        $file = $this->getObjectProperty($obj, 'file');
        /** @var array<string> $allowedPaths */
        $allowedPaths = $this->getObjectProperty($file, 'allowedPaths');

        $this->assertNotContains((string) \realpath(__DIR__ . '/..'), $allowedPaths);
        $this->assertContains($customDir, $allowedPaths);
    }

    /** @throws \Throwable */
    public function testConstructorUsesMarkupAllowedPathsFallbackAndOverride(): void
    {
        $customDir = (string) \realpath(__DIR__ . '/fixtures');
        $markupDir = (string) \realpath(__DIR__ . '/..');

        $fallback = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null, [
            'allowedPaths' => [$customDir],
        ]);

        /** @var \Com\Tecnick\File\File $fallbackMarkupFile */
        $fallbackMarkupFile = $this->getObjectProperty($fallback, 'markupFile');
        /** @var array<string> $fallbackMarkupPaths */
        $fallbackMarkupPaths = $this->getObjectProperty($fallbackMarkupFile, 'allowedPaths');
        $this->assertSame([$customDir], $fallbackMarkupPaths);

        $override = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null, [
            'allowedPaths' => [$customDir],
            'markupAllowedPaths' => [$markupDir],
        ]);

        /** @var \Com\Tecnick\File\File $overrideMarkupFile */
        $overrideMarkupFile = $this->getObjectProperty($override, 'markupFile');
        /** @var array<string> $overrideMarkupPaths */
        $overrideMarkupPaths = $this->getObjectProperty($overrideMarkupFile, 'allowedPaths');
        $this->assertSame([$markupDir], $overrideMarkupPaths);
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testGetBarcodeReturnsDrawingCommands(): void
    {
        $obj = $this->getTestObject();
        $out = $obj->getBarcode('C39', 'ABC123');

        $this->assertNotSame('', $out);
        $this->bcAssertMatchesRegularExpression('/\bre\b/', $out);
    }

    /** @throws \Throwable */
    public function testGetBarcodeThrowsOnInvalidType(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);

        $obj->getBarcode('INVALID_TYPE', 'ABC123');
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testAddEmptySignatureAppearanceAddsEntry(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);
        $obj->addEmptySignatureAppearance(5, 6, 20, 10, $page['pid'], 'EmptySig');

        /** @var array{appearance: array{empty: array<int, array{name: string, page: int, objid: int}>}} $signature */
        $signature = $this->getObjectProperty($obj, 'signature');
        $this->assertNotEmpty($signature['appearance']['empty']);
        assert(isset($signature['appearance']['empty'][0]), "\$signature['appearance']['empty'][0] must be set");
        $entry = $signature['appearance']['empty'][0];
        $this->assertSame('EmptySig', $entry['name']);
        $this->assertSame($page['pid'], $entry['page']);
        $this->assertIsInt($entry['objid']);
    }

    /** @throws \Throwable */
    public function testAddTOCHandlesEmptyOutlineList(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndAddRawPage($obj);
        $obj->addTOC();

        /** @var array<int, array<string, mixed>> $outlines */
        $outlines = $this->getObjectProperty($obj, 'outlines');
        $this->assertCount(0, $outlines);
    }

    /** @throws \Throwable */
    public function testAddTOCProcessesBookmarks(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);
        $obj->setBookmark('Section 1', '', 0, $page['pid']);
        $obj->addTOC($page['pid']);

        /** @var array<int, array{t: string, p: int}> $outlines */
        $outlines = $this->getObjectProperty($obj, 'outlines');
        $this->assertCount(1, $outlines);
        assert(isset($outlines[0]), "\$outlines[0] must be set");
        $this->assertSame('Section 1', $outlines[0]['t']);
        $this->assertSame($page['pid'], $outlines[0]['p']);
    }

    /** @throws \Throwable */
    public function testNewLayerReturnsBeginLayerOperatorAndStoresLayer(): void
    {
        $obj = $this->getTestObject();
        $out = $obj->newLayer('Layer 1', ['view' => true], true, true, true);

        /** @var array<int, array{name: string, intent: string}> $layers */
        $layers = $this->getObjectProperty($obj, 'pdflayer');
        $this->assertSame(" /OC /LYR001 BDC\n", $out);
        $this->assertCount(1, $layers);
        assert(isset($layers[0]), "\$layers[0] must be set");
        $this->assertSame('Layer1', $layers[0]['name']);
        $this->assertSame('/View', $layers[0]['intent']);
    }

    /** @throws \Throwable */
    public function testCloseLayerReturnsEmcOperator(): void
    {
        $obj = $this->getTestObject();
        $this->assertSame("EMC\n", $obj->closeLayer());
    }

    /** @throws \Throwable */
    public function testNewLayerFallsBackToAutoNameAndAddsDesignIntent(): void
    {
        $obj = $this->getTestObject();
        $out = $obj->newLayer('***', ['design' => true], true, true, true);

        /** @var array<int, array{name: string, intent: string}> $layers */
        $layers = $this->getObjectProperty($obj, 'pdflayer');
        $this->assertSame(" /OC /LYR001 BDC\n", $out);
        $this->assertCount(1, $layers);
        assert(isset($layers[0]), "\$layers[0] must be set");
        $this->assertSame('LYR001', $layers[0]['name']);
        $this->assertSame('/Design', $layers[0]['intent']);
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testConstructorWithUnicodeDisabledSetsAsciiWhitespacePattern(): void
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', false, false, true);

        /** @var array{r: string} $regexp */
        $regexp = $this->getObjectProperty($obj, 'spaceregexp');
        $this->assertSame('/[^\S\xa0]/', $regexp['r']);
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testDefaultAndPdfx4KeepRgbProcessColors(): void
    {
        $default = new \Com\Tecnick\Pdf\Tcpdf();
        $pdfx4 = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx4');

        $this->assertStringContainsString(" rg\n", $default->color->getPdfColor('#336699'));
        $this->assertStringContainsString(" rg\n", $pdfx4->color->getPdfColor('#336699'));
    }

    /** @throws \Throwable */
    public function testCssColorModelsEmitExpectedPdfOperators(): void
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf();

        $cases = [
            'named navy' => ['navy', " rg\n"],
            'gray g()' => ['g(50%)', " g\n"],
            'hex short' => ['#0f0', " rg\n"],
            'hex long' => ['#1e90ff', " rg\n"],
            'rgb' => ['rgb(255, 99, 71)', " rg\n"],
            'rgba' => ['rgba(30, 144, 255, 0.25)', " rg\n"],
            'hsl' => ['hsl(120, 100%, 25%)', " rg\n"],
            'hsla' => ['hsla(300, 100%, 50%, 0.20)', " rg\n"],
            'lab' => ['lab(54.29% -19.04 38.25)', " scn\n"],
            'lab alpha' => ['lab(54.29% -19.04 38.25 / 0.35)', " scn\n"],
            'cmyk' => ['cmyk(67, 33, 0, 25)', " k\n"],
            'spot function' => ['spot(cyan, 35%)', " scn\n"],
        ];

        foreach ($cases as $label => [$color, $operator]) {
            $pdfColor = $obj->color->getPdfColor($color);
            $this->assertTrue($pdfColor !== '', 'Expected non-empty PDF color output for case: ' . $label);
            $this->assertStringContainsString($operator, $pdfColor, 'Unexpected PDF operator for case: ' . $label);
            if ($label === 'lab' || $label === 'lab alpha' || $label === 'spot function') {
                $this->assertStringContainsString('/CS', $pdfColor);
            }
        }
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testSetSignatureForExternalSigningUsesPlaceholderCertificate(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);

        $obj->setSignatureForExternalSigning([
            'appearance' => [
                'empty' => [],
                'name' => 'MainSig',
                'page' => $page['pid'],
                'rect' => '0 0 10 5',
            ],
            'approval' => '',
            'cert_type' => 2,
            'extracerts' => null,
            'info' => ['ContactInfo' => '', 'Location' => '', 'Name' => '', 'Reason' => ''],
            'password' => '',
            'privkey' => '',
            'signcert' => '',
        ]);

        /** @var array{signcert: string, privkey: string} $signature */
        $signature = $this->getObjectProperty($obj, 'signature');
        $this->assertSame('__external_signing__', $signature['signcert']);
        $this->assertSame('__external_signing__', $signature['privkey']);
        $this->assertTrue($this->getObjectProperty($obj, 'sign'));
    }

    /** @throws \Throwable */
    public function testGetExternalSignaturePreparationReturnsHashAndPreparedPdf(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);

        $obj->setSignatureForExternalSigning([
            'appearance' => [
                'empty' => [],
                'name' => 'MainSig',
                'page' => $page['pid'],
                'rect' => '0 0 10 5',
            ],
            'approval' => '',
            'cert_type' => 2,
            'extracerts' => null,
            'info' => ['ContactInfo' => '', 'Location' => '', 'Name' => '', 'Reason' => ''],
            'password' => '',
            'privkey' => '',
            'signcert' => '',
        ]);

        $prepared = $obj->getExternalSignaturePreparation('sha256');

        $this->assertSame('sha256', $prepared['algorithm']);
        $this->assertCount(4, $prepared['byte_range']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $prepared['hash_hex']);
        $this->assertNotSame('', $prepared['prepared_pdf']);
        $this->assertStringContainsString('/ByteRange[0 ', $prepared['prepared_pdf']);
        $this->assertStringNotContainsString('**********', $prepared['prepared_pdf']);
    }

    /** @throws \Throwable */
    public function testApplyExternalSignatureInjectsProvidedCmsData(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);

        $obj->setSignatureForExternalSigning([
            'appearance' => [
                'empty' => [],
                'name' => 'MainSig',
                'page' => $page['pid'],
                'rect' => '0 0 10 5',
            ],
            'approval' => '',
            'cert_type' => 2,
            'extracerts' => null,
            'info' => ['ContactInfo' => '', 'Location' => '', 'Name' => '', 'Reason' => ''],
            'password' => '',
            'privkey' => '',
            'signcert' => '',
        ]);

        $prepared = $obj->getExternalSignaturePreparation('sha256');
        $signedPdf = $obj->applyExternalSignature($prepared['prepared_pdf'], $prepared['byte_range'], 'ABC', 'binary');

        $this->assertStringContainsString('/Contents<414243', $signedPdf);
        $this->assertMatchesRegularExpression('#/ByteRange\[0 \d+ \d+ \d+\]#', $signedPdf);
    }

    /** @throws \Throwable */
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

        /** @var array{annotrefs: array<int>} $pageData */
        $pageData = $obj->page->getPage($page['pid']);
        $this->assertContains(123, $pageData['annotrefs']);
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testGetSignatureObjectIDReturnsReservedObjectId(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);
        $certPath = __DIR__ . '/../examples/data/cert/tcpdf.crt';
        $certPem = (string) \file_get_contents($certPath);

        $obj->setSignature([
            'appearance' => [
                'empty' => [],
                'name' => 'MainSig',
                'page' => $page['pid'],
                'rect' => '0 0 10 5',
            ],
            'approval' => '',
            'cert_type' => 2,
            'extracerts' => null,
            'info' => ['ContactInfo' => '', 'Location' => '', 'Name' => '', 'Reason' => ''],
            'password' => '',
            'privkey' => $certPem,
            'signcert' => $certPem,
        ]);

        $this->assertGreaterThan(0, $obj->getSignatureObjectID());
    }

    /** @throws \Throwable */
    public function testSetSignatureAppearanceStreamStoresModeAndState(): void
    {
        $obj = $this->getTestObject();
        $obj->setSignatureAppearanceStream('q 0 g Q', 'N', 'On');

        /** @var array{appearance: array{ap: array<string, array<string, string>>, as: string}} $signature */
        $signature = $this->getObjectProperty($obj, 'signature');
        $appearance = $signature['appearance'];
        $ap = $appearance['ap'];
        $normal = $ap['n'] ?? [];
        $this->assertSame('q 0 g Q', $normal['On'] ?? null);
        $this->assertSame('On', $signature['appearance']['as']);
    }

    /** @throws \Throwable */
    public function testSetSignatureAppearanceXObjectStoresXObjectId(): void
    {
        $obj = $this->getTestObject();
        $obj->setSignatureAppearanceXObject('IMP1');

        /** @var array{appearance: array{xobj: string}} $signature */
        $signature = $this->getObjectProperty($obj, 'signature');
        $this->assertSame('IMP1', $signature['appearance']['xobj']);
    }

    /** @throws \Throwable */
    public function testEnableSignatureApprovalTogglesFlag(): void
    {
        $obj = new TestableTcpdf();

        $ret = $obj->exposeEnableSignatureApproval(true);
        $this->assertSame($obj, $ret);
        $this->assertTrue($this->getObjectProperty($obj, 'sigapp'));

        $obj->exposeEnableSignatureApproval(false);
        $this->assertFalse($this->getObjectProperty($obj, 'sigapp'));
    }

    /** @throws \Throwable */
    public function testAddTOCSupportsRtlPositioningAndBookmarkColor(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);
        $obj->setBookmark('RTL entry', '', 0, $page['pid'], 0, 0, '', 'red');

        $obj->addTOC($page['pid'], 0, 0, -1, true);

        /** @var array<int, array{t: string}> $outlines */
        $outlines = $this->getObjectProperty($obj, 'outlines');
        $this->assertCount(1, $outlines);
        assert(isset($outlines[0]), "\$outlines[0] must be set");
        $this->assertSame('RTL entry', $outlines[0]['t']);
    }

    /** @throws \Throwable */
    public function testAddTOCHandlesRegionOverflowAndPageTransitions(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndAddRawPage($obj);
        $obj->setBookmark(\str_repeat('Long TOC line ', 250), '', 0, $page['pid']);

        $obj->addTOC($page['pid'], 0, 10000, 20, false);

        /** @var array<int, array{t: string}> $outlines */
        $outlines = $this->getObjectProperty($obj, 'outlines');
        $this->assertCount(1, $outlines);
        assert(isset($outlines[0]), "\$outlines[0] must be set");
        $this->assertStringStartsWith('Long TOC line', $outlines[0]['t']);
    }

    /** @throws \Throwable */
    public function testLanguageSettersUpdateLanguageMetadata(): void
    {
        $obj = $this->getTestObject();

        $obj->setLanguageArray(['a_meta_language' => 'it-IT', 'custom' => 'x']);
        /** @var array<string, string> $lang */
        $lang = $this->getObjectProperty($obj, 'lang');
        $this->assertSame('it-IT', $lang['a_meta_language'] ?? null);
        $this->assertSame('x', $lang['custom'] ?? null);

        $obj->setLanguage('en-US');
        /** @var array<string, string> $updated */
        $updated = $this->getObjectProperty($obj, 'lang');
        $this->assertSame('en-US', $updated['a_meta_language'] ?? null);
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

        $setSignatureObj = new \ReflectionMethod($obj, 'setSignature');
        $setSignatureObj->invoke($obj, $data);
    }

    /** @throws \Throwable */
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

        $setSignatureObj = new \ReflectionMethod($obj, 'setSignature');
        $setSignatureObj->invoke($obj, $data);
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

        $setSignTimeStampObj = new \ReflectionMethod($obj, 'setSignTimeStamp');
        $setSignTimeStampObj->invoke($obj, $data);
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

        $setSignTimeStampObj = new \ReflectionMethod($obj, 'setSignTimeStamp');
        $setSignTimeStampObj->invoke($obj, $data);
    }

    /** @throws \Throwable */
    public function testAddPagePartialMarginsKeepBottomFooterMarginsWhenCtCbMissing(): void
    {
        $obj = $this->getTestObject();

        $margin = [
            'PT' => 7,
            'PR' => 7,
            'PB' => 7,
            'PL' => 7,
            'HB' => 15,
            'FT' => 15,
        ];

        $landscape = $obj->addPage([
            'orientation' => 'L',
            'format' => 'A4',
            'margin' => $margin,
        ]);
        /** @var array{margin: array{CB: float, CT: float, FT: float, HB: float, PB: float}, FooterHeight: float, region: array<int, array{RB: float}>} $landscape */
        $this->bcAssertEqualsWithDelta(15.0, $landscape['margin']['CB']);
        $this->bcAssertEqualsWithDelta(15.0, $landscape['margin']['CT']);
        $this->bcAssertEqualsWithDelta(15.0, $landscape['margin']['FT']);
        $this->bcAssertEqualsWithDelta(15.0, $landscape['margin']['HB']);
        $this->bcAssertEqualsWithDelta(7.0, $landscape['margin']['PB']);
        $this->bcAssertEqualsWithDelta(8.0, $landscape['FooterHeight']);
        $landscapeRegion0 = $landscape['region'][0] ?? null;
        if (!\is_array($landscapeRegion0)) {
            $this->fail('Unexpected addPage() region payload for landscape page.');
        }

        $this->bcAssertEqualsWithDelta(15.0, $landscapeRegion0['RB']);

        $portrait = $obj->addPage([
            'orientation' => 'P',
            'format' => 'A4',
            'margin' => $margin,
        ]);
        /** @var array{margin: array{CB: float, CT: float, FT: float, HB: float, PB: float}, FooterHeight: float, region: array<int, array{RB: float}>} $portrait */
        $this->bcAssertEqualsWithDelta(15.0, $portrait['margin']['CB']);
        $this->bcAssertEqualsWithDelta(15.0, $portrait['margin']['CT']);
        $this->bcAssertEqualsWithDelta(15.0, $portrait['margin']['FT']);
        $this->bcAssertEqualsWithDelta(15.0, $portrait['margin']['HB']);
        $this->bcAssertEqualsWithDelta(7.0, $portrait['margin']['PB']);
        $this->bcAssertEqualsWithDelta(8.0, $portrait['FooterHeight']);
        $portraitRegion0 = $portrait['region'][0] ?? null;
        if (!\is_array($portraitRegion0)) {
            $this->fail('Unexpected addPage() region payload for portrait page.');
        }

        $this->bcAssertEqualsWithDelta(15.0, $portraitRegion0['RB']);
    }

    /** @throws \Throwable */
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
