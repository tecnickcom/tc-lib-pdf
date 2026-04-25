<?php

/**
 * MetaInfoTest.php
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

class MetaInfoTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    protected function getInternalTestObject(): TestablMetaInfo
    {
        return new TestablMetaInfo();
    }


    public function testGetVersionReturnsNonEmptyString(): void
    {
        $obj = $this->getTestObject();
        $this->assertNotSame('', $obj->getVersion());
    }

    public function testMetadataSettersStoreNonEmptyValuesAndReturnSameInstance(): void
    {
        $obj = $this->getTestObject();

        $this->assertSame($obj, $obj->setCreator('creator-app'));
        $this->assertSame($obj, $obj->setAuthor('author-name'));
        $this->assertSame($obj, $obj->setSubject('subject-line'));
        $this->assertSame($obj, $obj->setTitle('doc-title'));
        $this->assertSame($obj, $obj->setKeywords('one two'));

        $this->assertSame('creator-app', $this->getObjectProperty($obj, 'creator'));
        $this->assertSame('author-name', $this->getObjectProperty($obj, 'author'));
        $this->assertSame('subject-line', $this->getObjectProperty($obj, 'subject'));
        $this->assertSame('doc-title', $this->getObjectProperty($obj, 'title'));
        $this->assertSame('one two', $this->getObjectProperty($obj, 'keywords'));
    }

    public function testMetadataSettersIgnoreEmptyValues(): void
    {
        $obj = $this->getTestObject();
        $before = $this->getObjectProperty($obj, 'title');

        $obj->setTitle('');

        $this->assertSame($before, $this->getObjectProperty($obj, 'title'));
    }

    public function testSetPDFVersionStoresExplicitVersion(): void
    {
        $obj = $this->getTestObject();
        $ret = $obj->setPDFVersion('1.6');

        $this->assertSame($obj, $ret);
        $this->assertSame('1.6', $this->getObjectProperty($obj, 'pdfver'));
    }

    #[DataProvider('pdfaVersionFixtureProvider')]
    public function testSetPDFVersionHonorsPdfaModes(int $pdfaMode, string $inputVersion, string $expectedVersion): void
    {
        $obj = $this->getTestObject();
        $pdfa = new \ReflectionProperty(\Com\Tecnick\Pdf\Tcpdf::class, 'pdfa');
        $pdfa->setAccessible(true);
        $pdfa->setValue($obj, $pdfaMode);

        $obj->setPDFVersion($inputVersion);

        $this->assertSame($expectedVersion, $this->getObjectProperty($obj, 'pdfver'));
    }

    #[DataProvider('pdfuaVersionFixtureProvider')]
    public function testSetPDFVersionHonorsPdfuaModes(
        string $pdfuaMode,
        string $inputVersion,
        string $expectedVersion
    ): void {
        $obj = $this->getTestObject();
        $pdfua = new \ReflectionProperty(\Com\Tecnick\Pdf\Tcpdf::class, 'pdfuaMode');
        $pdfua->setAccessible(true);
        $pdfua->setValue($obj, $pdfuaMode);

        $obj->setPDFVersion($inputVersion);

        $this->assertSame($expectedVersion, $this->getObjectProperty($obj, 'pdfver'));
    }

    public function testSetPDFVersionThrowsOnInvalidFormat(): void
    {
        $obj = $this->getTestObject();
        $this->expectException(\Com\Tecnick\Pdf\Exception::class);
        $this->expectExceptionMessage('Invalid PDF version format');

        $obj->setPDFVersion('1.A');
    }

    public function testSetSRGBTogglesFlag(): void
    {
        $obj = $this->getTestObject();
        $this->assertSame($obj, $obj->setSRGB(true));
        $this->assertTrue($this->getObjectProperty($obj, 'sRGB'));

        $obj->setSRGB(false);
        $this->assertFalse($this->getObjectProperty($obj, 'sRGB'));
    }

    public function testSetCustomXMPUpdatesKnownKeyOnly(): void
    {
        $obj = $this->getTestObject();
        $this->assertSame($obj, $obj->setCustomXMP('x:xmpmeta', '<custom/>'));

        /** @var array<string, string> $custom */
        $custom = $this->getObjectProperty($obj, 'custom_xmp');
        $this->assertSame('<custom/>', $custom['x:xmpmeta']);

        $obj->setCustomXMP('unknown-key', '<ignored/>');
        /** @var array<string, string> $custom */
        $custom = $this->getObjectProperty($obj, 'custom_xmp');
        $this->assertArrayNotHasKey('unknown-key', $custom);
    }

    public function testSetViewerPreferencesStoresPreferences(): void
    {
        $obj = $this->getTestObject();
        $pref = ['HideToolbar' => true, 'NumCopies' => 2, 'PrintScaling' => 'none'];

        $this->assertSame($obj, $obj->setViewerPreferences($pref));
        $this->assertSame($pref, $this->getObjectProperty($obj, 'viewerpref'));
    }

    /** @param ?array<string, mixed> $viewerPref */
    #[DataProvider('pagePrintScalingFixtureProvider')]
    public function testGetPagePrintScalingReturnsExpectedValue(?array $viewerPref, string $expectedToken): void
    {
        $obj = $this->getInternalTestObject();
        if ($viewerPref !== null) {
            $this->setObjectProperty($obj, 'viewerpref', $viewerPref);
        }

        $result = $obj->exposeGetPagePrintScaling();

        $this->assertStringContainsString('/PrintScaling', $result);
        $this->assertStringContainsString($expectedToken, $result);
    }

    public function testGetDuplexModeReturnsEmptyByDefault(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetDuplexMode();

        $this->assertSame('', $result);
    }

    public function testGetPageBoxNameReturnsMappedValueWhenAvailable(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'page', new TestableObjPageForMetaInfo());
        $this->setObjectProperty($obj, 'viewerpref', ['ViewArea' => 'MediaBox']);

        $result = $obj->exposeGetPageBoxName('ViewArea');

        $this->assertSame(' /ViewArea /MediaBox', $result);
    }

    public function testGetBooleanModeReturnsEmptyWhenNotSet(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetBooleanMode('HideToolbar');

        $this->assertSame('', $result);
    }

    #[DataProvider('duplexModeFixtureProvider')]
    public function testGetDuplexModeReturnsMappedValue(string $duplexMode, string $expectedOutput): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'viewerpref', ['Duplex' => $duplexMode]);

        $result = $obj->exposeGetDuplexMode();

        $this->assertStringContainsString($expectedOutput, $result);
    }

    #[DataProvider('booleanModeFixtureProvider')]
    public function testGetBooleanModeReturnsMappedValue(bool $value, string $expectedWord): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'viewerpref', ['HideToolbar' => $value]);

        $result = $obj->exposeGetBooleanMode('HideToolbar');

        $this->assertStringContainsString('/HideToolbar ' . $expectedWord, $result);
    }


    public function testGetFormattedDateReturnsPdfDateStyle(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetFormattedDate(1710000000);

        $this->assertMatchesRegularExpression('/^[0-9]{14}[\+\-Z\']/', $result);
    }

    public function testGetXMPFormattedDateReturnsIsoStyle(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetXMPFormattedDate(1710000000);

        $this->assertStringContainsString('T', $result);
    }

    public function testGetOutDateTimeStringBuildsEscapedDate(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetOutDateTimeString(1710000000, 1);

        $this->assertStringContainsString('D:', $result);
    }

    public function testGetOutDateTimeStringUsesDocumentTimeWhenInputIsZero(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'doctime', 1710001234);

        $result = $obj->exposeGetOutDateTimeString(0, 1);

        $this->assertStringContainsString('D:', $result);
    }

    public function testGetEscapedXMLEscapesSpecialChars(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetEscapedXML('<a&b>');

        $this->assertSame('&lt;a&amp;b&gt;', $result);
    }

    public function testGetOutMetaInfoContainsDocumentInfoKeys(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetOutMetaInfo();

        $this->assertStringContainsString('/Creator', $result);
        $this->assertStringContainsString('/Producer', $result);
        $this->assertStringContainsString('/Trapped /False', $result);
    }

    public function testGetOutXMPContainsMetadataStreamStructure(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetOutXMP();

        $this->assertStringContainsString('/Type /Metadata', $result);
        $this->assertStringContainsString('<x:xmpmeta', $result);
        $this->assertStringContainsString('endobj', $result);
    }

    public function testGetOutXMPIncludesPdfaBlockWhenPdfaEnabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfa', 3);
        $this->setObjectProperty($obj, 'pdfaConformance', 'U');

        $result = $obj->exposeGetOutXMP();

        $this->assertStringContainsString('<pdfaid:part>3</pdfaid:part>', $result);
        $this->assertStringContainsString('<pdfaid:conformance>U</pdfaid:conformance>', $result);
    }

    public function testGetOutXMPIncludesPdfuaBlockWhenPdfuaEnabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua2');

        $result = $obj->exposeGetOutXMP();

        $this->assertStringContainsString('xmlns:pdfuaid="http://www.aiim.org/pdfua/ns/id/"', $result);
        $this->assertStringContainsString('<pdfuaid:part>2</pdfuaid:part>', $result);
    }

    #[DataProvider('pdfxVersionFixtureProvider')]
    public function testSetPDFVersionHonorsPdfxModes(
        string $pdfxMode,
        string $inputVersion,
        string $expectedVersion
    ): void {
        $obj = $this->getTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->setObjectProperty($obj, 'pdfxMode', $pdfxMode);

        $obj->setPDFVersion($inputVersion);

        $this->assertSame($expectedVersion, $this->getObjectProperty($obj, 'pdfver'));
    }

    #[DataProvider('pdfxGtsVersionStringFixtureProvider')]
    public function testGetGtsPdfxVersionStringReturnsExpectedValue(
        string $pdfxMode,
        string $expected
    ): void {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->setObjectProperty($obj, 'pdfxMode', $pdfxMode);

        $this->assertSame($expected, $obj->exposeGetGtsPdfxVersionString());
    }

    public function testGetOutMetaInfoIncludesGtsPdfxVersionWhenPdfxEnabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx4');

        $result = $obj->exposeGetOutMetaInfo();

        // The key is a PDF name (ASCII); the value is encoded as a PDF text string.
        $this->assertStringContainsString('/GTS_PDFXVersion', $result);
    }

    public function testGetOutMetaInfoOmitsGtsPdfxVersionWhenPdfxDisabled(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetOutMetaInfo();

        $this->assertStringNotContainsString('/GTS_PDFXVersion', $result);
    }

    public function testGetOutXMPIncludesPdfxidBlockWhenPdfxEnabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx1a');

        $result = $obj->exposeGetOutXMP();

        $this->assertStringContainsString('xmlns:pdfxid="http://www.npes.org/pdfx/ns/id/"', $result);
        $this->assertStringContainsString('<pdfxid:GTS_PDFXVersion>PDF/X-1a:2003</pdfxid:GTS_PDFXVersion>', $result);
    }

    public function testGetOutXMPOmitsPdfxidBlockWhenPdfxDisabled(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetOutXMP();

        $this->assertStringNotContainsString('pdfxid', $result);
    }

    public function testGetOutViewerPrefIncludesDirectionAndKnownFlags(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setRTL(true);
        $obj->setViewerPreferences(['HideToolbar' => true, 'NumCopies' => 2]);

        $result = $obj->exposeGetOutViewerPref();

        $this->assertStringContainsString('/ViewerPreferences <<', $result);
        $this->assertStringContainsString('/Direction /R2L', $result);
        $this->assertStringContainsString('/HideToolbar true', $result);
        $this->assertStringContainsString('/NumCopies 2', $result);
    }

    public function testGetOutViewerPrefIncludesPageRangeAndDisplayMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->setViewerPreferences([
            'NonFullScreenPageMode' => 'UseOutlines',
            'PrintPageRange' => [1, 3],
            'NumCopies' => 2,
            'PrintScaling' => 'none',
            'PickTrayByPDFSize' => false,
            'ViewArea' => 'MediaBox',
            'ViewClip' => 'CropBox',
            'PrintArea' => 'TrimBox',
            'PrintClip' => 'BleedBox',
        ]);

        $result = $obj->exposeGetOutViewerPref();

        $this->assertStringContainsString('/NonFullScreenPageMode /UseOutlines', $result);
        $this->assertStringContainsString('/PrintPageRange [ 0 2 ]', $result);
        $this->assertStringContainsString('/PrintScaling /None', $result);
        $this->assertStringContainsString('/NumCopies 2', $result);
    }

    public function testGetOutViewerPrefForceDisplayDocTitleTrueInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        $result = $obj->exposeGetOutViewerPref();

        $this->assertStringContainsString('/DisplayDocTitle true', $result);
    }

    public function testGetOutViewerPrefRespectsExplicitDisplayDocTitleFalseInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');
        $obj->setViewerPreferences(['DisplayDocTitle' => false]);

        $result = $obj->exposeGetOutViewerPref();

        $this->assertStringContainsString('/DisplayDocTitle false', $result);
    }

    public function testGetOutViewerPrefDoesNotForceDisplayDocTitleOutsidePdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetOutViewerPref();

        $this->assertStringNotContainsString('/DisplayDocTitle', $result);
    }

    /** @return array<string, array{0: string, 1: string, 2: string}> */
    public static function pdfxVersionFixtureProvider(): array
    {
        return [
            'pdfx1a_enforces_min_1_3_when_lower'   => ['pdfx1a', '1.1', '1.3'],
            'pdfx1a_allows_higher_explicit'         => ['pdfx1a', '1.6', '1.6'],
            'pdfx3_enforces_min_1_3'                => ['pdfx3', '1.2', '1.3'],
            'pdfx4_enforces_min_1_6_when_lower'     => ['pdfx4', '1.3', '1.6'],
            'pdfx4_allows_higher_explicit'          => ['pdfx4', '1.7', '1.7'],
            'pdfx5_enforces_min_1_6'                => ['pdfx5', '1.4', '1.6'],
            'pdfx_generic_enforces_min_1_3'         => ['pdfx', '1.1', '1.3'],
        ];
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function pdfxGtsVersionStringFixtureProvider(): array
    {
        return [
            'pdfx1a' => ['pdfx1a', 'PDF/X-1a:2003'],
            'pdfx3'  => ['pdfx3',  'PDF/X-3:2003'],
            'pdfx4'  => ['pdfx4',  'PDF/X-4:2010'],
            'pdfx5'  => ['pdfx5',  'PDF/X-5g:2010'],
            'pdfx_generic_defaults_to_x3' => ['pdfx', 'PDF/X-3:2003'],
        ];
    }

    /** @return array<string, array{0: int, 1: string, 2: string}> */
    public static function pdfaVersionFixtureProvider(): array
    {
        return [
            'pdfa1_forces_1_4' => [1, '1.9', '1.4'],
            'pdfa2_forces_1_7' => [2, '1.5', '1.7'],
            'pdfa4_forces_2_0' => [4, '1.5', '2.0'],
        ];
    }

    /** @return array<string, array{0: string, 1: string, 2: string}> */
    public static function pdfuaVersionFixtureProvider(): array
    {
        return [
            'pdfua_defaults_to_1_7' => ['pdfua', '1.4', '1.7'],
            'pdfua1_forces_1_7' => ['pdfua1', '1.5', '1.7'],
            'pdfua2_forces_2_0' => ['pdfua2', '1.7', '2.0'],
        ];
    }

    /** @return array<string, array{0: ?array<string, mixed>, 1: string}> */
    public static function pagePrintScalingFixtureProvider(): array
    {
        return [
            'default_value' => [null, 'AppDefault'],
            'explicit_none' => [['PrintScaling' => 'none'], '/None'],
        ];
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function duplexModeFixtureProvider(): array
    {
        return [
            'simplex' => ['Simplex', '/Duplex /Simplex'],
            'short_edge' => ['DuplexFlipShortEdge', '/Duplex /DuplexFlipShortEdge'],
        ];
    }

    /** @return array<string, array{0: bool, 1: string}> */
    public static function booleanModeFixtureProvider(): array
    {
        return [
            'true_value' => [true, 'true'],
            'false_value' => [false, 'false'],
        ];
    }
}
