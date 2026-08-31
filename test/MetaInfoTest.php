<?php

/**
 * MetaInfoTest.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

use PHPUnit\Framework\Attributes\DataProvider;

class MetaInfoTest extends TestUtil
{
    /** @throws \Throwable */
    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    /** @throws \Throwable */
    protected function getInternalTestObject(): TestablMetaInfo
    {
        return new TestablMetaInfo();
    }

    /** @throws \Throwable */
    public function testGetVersionReturnsNonEmptyString(): void
    {
        $obj = $this->getTestObject();
        $this->assertNotSame('', $obj->getVersion());
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testMetadataSettersIgnoreEmptyValues(): void
    {
        $obj = $this->getTestObject();
        $before = (string) $this->getObjectProperty($obj, 'title');

        $obj->setTitle('');

        $this->assertSame($before, $this->getObjectProperty($obj, 'title'));
    }

    /** @throws \Throwable */
    public function testSetPDFVersionStoresExplicitVersion(): void
    {
        $obj = $this->getTestObject();
        $ret = $obj->setPDFVersion('1.6');

        $this->assertSame($obj, $ret);
        $this->assertSame('1.6', $this->getObjectProperty($obj, 'pdfver'));
    }

    /** @throws \Throwable */
    #[DataProvider('pdfaVersionFixtureProvider')]
    public function testSetPDFVersionHonorsPdfaModes(int $pdfaMode, string $inputVersion, string $expectedVersion): void
    {
        $obj = $this->getTestObject();
        $pdfa = new \ReflectionProperty(\Com\Tecnick\Pdf\Tcpdf::class, 'pdfa');
        $pdfa->setValue($obj, $pdfaMode);

        $obj->setPDFVersion($inputVersion);

        $this->assertSame($expectedVersion, $this->getObjectProperty($obj, 'pdfver'));
    }

    /** @throws \Throwable */
    #[DataProvider('pdfuaVersionFixtureProvider')]
    public function testSetPDFVersionHonorsPdfuaModes(
        string $pdfuaMode,
        string $inputVersion,
        string $expectedVersion,
    ): void {
        $obj = $this->getTestObject();
        $pdfua = new \ReflectionProperty(\Com\Tecnick\Pdf\Tcpdf::class, 'pdfuaMode');
        $pdfua->setValue($obj, $pdfuaMode);

        $obj->setPDFVersion($inputVersion);

        $this->assertSame($expectedVersion, $this->getObjectProperty($obj, 'pdfver'));
    }

    /** @throws \Throwable */
    public function testSetPDFVersionThrowsOnInvalidFormat(): void
    {
        $obj = $this->getTestObject();
        $this->expectException(\Com\Tecnick\Pdf\Exception::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('Invalid PDF version format', '/') . '/');

        $obj->setPDFVersion('1.A');
    }

    /** @throws \Throwable */
    public function testSetPDFVersionPinsVersionWhenPdfxEnabled(): void
    {
        $obj = $this->getTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx4');

        // Each PDF/X part is defined against one exact PDF version, so the requested
        // version is ignored just as it is in PDF/A mode.
        $obj->setPDFVersion('1.A');

        $this->assertSame('1.6', $this->getObjectProperty($obj, 'pdfver'));
    }

    /** @throws \Throwable */
    public function testSetSRGBTogglesFlag(): void
    {
        $obj = $this->getTestObject();
        $this->assertSame($obj, $obj->setSRGB(true));
        $this->assertTrue($this->getObjectProperty($obj, 'sRGB'));

        $obj->setSRGB(false);
        $this->assertFalse($this->getObjectProperty($obj, 'sRGB'));
    }

    /** @throws \Throwable */
    public function testSetCustomXMPUpdatesKnownKeyOnly(): void
    {
        $obj = $this->getTestObject();
        $this->assertSame($obj, $obj->setCustomXMP('x:xmpmeta', '<custom/>'));

        /** @var array<string, string> $custom */
        $custom = $this->getObjectProperty($obj, 'custom_xmp');
        $this->assertArrayHasKey('x:xmpmeta', $custom);
        $this->assertSame('<custom/>', $custom['x:xmpmeta'] ?? null);

        $obj->setCustomXMP('unknown-key', '<ignored/>');
        /** @var array<string, string> $custom */
        $custom = $this->getObjectProperty($obj, 'custom_xmp');
        $this->assertArrayNotHasKey('unknown-key', $custom);
    }

    /** @throws \Throwable */
    public function testSetCustomXMPIgnoresEmptyKeyOrPayload(): void
    {
        $obj = $this->getTestObject();
        /** @var array<string, string> $before */
        $before = $this->getObjectProperty($obj, 'custom_xmp');

        $this->assertSame($obj, $obj->setCustomXMP('', '<custom/>'));
        $this->assertSame($obj, $obj->setCustomXMP('x:xmpmeta', ''));

        /** @var array<string, string> $after */
        $after = $this->getObjectProperty($obj, 'custom_xmp');
        $this->assertSame($before, $after);
    }

    /** @throws \Throwable */
    public function testSetCustomXMPAppendsRepeatedFragments(): void
    {
        $obj = $this->getTestObject();
        $obj->setCustomXMP('x:xmpmeta', '<first/>');
        $obj->setCustomXMP('x:xmpmeta', '<second/>');

        /** @var array<string, string> $custom */
        $custom = $this->getObjectProperty($obj, 'custom_xmp');
        $this->assertSame("<first/>\n<second/>", $custom['x:xmpmeta'] ?? null);
    }

    /** @throws \Throwable */
    public function testSetCustomXMPReplacesAndClearsOnRequest(): void
    {
        $obj = $this->getTestObject();
        $obj->setCustomXMP('x:xmpmeta', '<first/>');
        $obj->setCustomXMP('x:xmpmeta', '<second/>', true);

        /** @var array<string, string> $custom */
        $custom = $this->getObjectProperty($obj, 'custom_xmp');
        $this->assertSame('<second/>', $custom['x:xmpmeta'] ?? null);

        $obj->setCustomXMP('x:xmpmeta', '', true);
        /** @var array<string, string> $custom */
        $custom = $this->getObjectProperty($obj, 'custom_xmp');
        $this->assertSame('', $custom['x:xmpmeta'] ?? null);
    }

    /** @throws \Throwable */
    public function testSetCustomXMPAcceptsFragmentsValidInTheKeyContext(): void
    {
        $obj = $this->getTestObject();

        $obj->setCustomXMP('x:xmpmeta.rdf:RDF', '<rdf:Description rdf:about=""/>');
        $obj->setCustomXMP(
            'x:xmpmeta.rdf:RDF',
            '<rdf:Description rdf:about="" xmlns:tc="urn:tc"><tc:a>1</tc:a></rdf:Description>',
        );
        $obj->setCustomXMP(
            'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag',
            '<rdf:li rdf:parseType="Resource"><pdfaSchema:prefix>tc</pdfaSchema:prefix></rdf:li>',
        );
        $obj->setCustomXMP('x:xmpmeta.rdf:RDF.rdf:Description', '<!--CUSTOM-->');

        /** @var array<string, string> $custom */
        $custom = $this->getObjectProperty($obj, 'custom_xmp');
        $this->assertStringContainsString('<tc:a>1</tc:a>', $custom['x:xmpmeta.rdf:RDF'] ?? '');
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('invalidCustomXMPProvider')]
    public function testSetCustomXMPRejectsMalformedFragments(string $key, string $xmp): void
    {
        $obj = $this->getTestObject();

        $this->expectException(\Com\Tecnick\Pdf\Exception::class);
        $obj->setCustomXMP($key, $xmp);
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function invalidCustomXMPProvider(): array
    {
        return [
            'unbalanced' => ['x:xmpmeta', '<open>'],
            'stray_close' => ['x:xmpmeta.rdf:RDF', '</rdf:Description>'],
            'undeclared_prefix' => ['x:xmpmeta.rdf:RDF.rdf:Description', '<tc:a>1</tc:a>'],
            // The extension schema description declares no 'x' prefix.
            'prefix_out_of_scope' => ['x:xmpmeta.rdf:RDF.rdf:Description', '<x:a/>'],
            'doctype' => ['x:xmpmeta', '<!DOCTYPE foo><foo/>'],
            'entity' => ['x:xmpmeta', '<!ENTITY xxe SYSTEM "file:///etc/passwd">'],
            'bad_attribute' => ['x:xmpmeta', '<a b=c/>'],
        ];
    }

    /** @throws \Throwable */
    public function testSetCustomXMPKeepsTheStoredValueWhenARejectedFragmentFollows(): void
    {
        $obj = $this->getTestObject();
        $obj->setCustomXMP('x:xmpmeta', '<valid/>');

        try {
            $obj->setCustomXMP('x:xmpmeta', '<broken>');
        } catch (\Com\Tecnick\Pdf\Exception) {
            // expected
        }

        /** @var array<string, string> $custom */
        $custom = $this->getObjectProperty($obj, 'custom_xmp');
        $this->assertSame('<valid/>', $custom['x:xmpmeta'] ?? null);
    }

    /** @throws \Throwable */
    protected function getFacturXTestObject(): TestablMetaInfo
    {
        $obj = new TestablMetaInfo(mode: \Com\Tecnick\Pdf\PdfConformance::Pdfa3);
        $obj->setFacturX('<rsm:CrossIndustryInvoice/>');
        return $obj;
    }

    /** @throws \Throwable */
    public function testSetFacturXEmbedsThePayloadAsAnAlternativeAssociatedFile(): void
    {
        $obj = $this->getFacturXTestObject();

        /** @var array<string, array<string, mixed>> $files */
        $files = $this->getObjectProperty($obj, 'embeddedfiles');
        $this->assertArrayHasKey('factur-x.xml', $files);
        $this->assertSame('<rsm:CrossIndustryInvoice/>', $files['factur-x.xml']['content'] ?? null);
        $this->assertSame('text/xml', $files['factur-x.xml']['mimeType'] ?? null);
        $this->assertSame('Alternative', $files['factur-x.xml']['afRelationship'] ?? null);
        $this->assertSame('Factur-X/ZUGFeRD electronic invoice', $files['factur-x.xml']['description'] ?? null);
    }

    /** @throws \Throwable */
    public function testSetFacturXStoresTheProfileMetadataAndReturnsSameInstance(): void
    {
        $obj = new TestablMetaInfo(mode: \Com\Tecnick\Pdf\PdfConformance::Pdfa3);

        $this->assertSame($obj, $obj->setFacturX('<rsm:CrossIndustryInvoice/>'));

        $this->assertSame(
            [
                'filename' => 'factur-x.xml',
                'doctype' => 'INVOICE',
                'version' => '1.0',
                'conformance' => 'EN 16931',
                'uri' => 'urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#',
                'prefix' => 'fx',
                'schema' => 'Factur-X PDFA Extension Schema',
            ],
            $this->getObjectProperty($obj, 'hybriddoc'),
        );
    }

    /** @throws \Throwable */
    public function testSetFacturXAppliesTheProfileDefaults(): void
    {
        $obj = new TestablMetaInfo(mode: \Com\Tecnick\Pdf\PdfConformance::Pdfa3);
        $obj->setFacturX(
            '<rsm:CrossIndustryOrder/>',
            \Com\Tecnick\Pdf\HybridProfile::OrderX,
            \Com\Tecnick\Pdf\HybridConformance::Extended,
        );

        /** @var array<string, string> $meta */
        $meta = $this->getObjectProperty($obj, 'hybriddoc');
        $this->assertSame('order-x.xml', $meta['filename'] ?? null);
        $this->assertSame('ORDER', $meta['doctype'] ?? null);
        $this->assertSame('EXTENDED', $meta['conformance'] ?? null);

        /** @var array<string, mixed> $files */
        $files = $this->getObjectProperty($obj, 'embeddedfiles');
        $this->assertArrayHasKey('order-x.xml', $files);
    }

    /** @throws \Throwable */
    public function testSetFacturXAcceptsExplicitOverrides(): void
    {
        $obj = new TestablMetaInfo(mode: \Com\Tecnick\Pdf\PdfConformance::Pdfa3);
        $obj->setFacturX(
            xml: '<rsm:CrossIndustryInvoice/>',
            profile: 'facturx',
            level: 'en16931',
            doctype: 'CREDITNOTE',
            filename: '/tmp/custom.xml',
            version: '1.07',
            desc: 'custom description',
        );

        /** @var array<string, string> $meta */
        $meta = $this->getObjectProperty($obj, 'hybriddoc');
        $this->assertSame('custom.xml', $meta['filename'] ?? null);
        $this->assertSame('CREDITNOTE', $meta['doctype'] ?? null);
        $this->assertSame('1.07', $meta['version'] ?? null);

        /** @var array<string, array<string, mixed>> $files */
        $files = $this->getObjectProperty($obj, 'embeddedfiles');
        $this->assertSame('custom description', $files['custom.xml']['description'] ?? null);
    }

    /** @throws \Throwable */
    public function testSetFacturXRejectsAnEmptyPayload(): void
    {
        $obj = new TestablMetaInfo(mode: \Com\Tecnick\Pdf\PdfConformance::Pdfa3);

        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);
        $obj->setFacturX("  \n ");
    }

    /** @throws \Throwable */
    public function testGetOutXMPDeclaresTheProfilePropertiesAndExtensionSchema(): void
    {
        $result = $this->getFacturXTestObject()->exposeGetOutXMP();

        $this->assertStringContainsString(
            '<rdf:Description rdf:about="" xmlns:fx="urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#">',
            $result,
        );
        $this->assertStringContainsString('<fx:DocumentType>INVOICE</fx:DocumentType>', $result);
        $this->assertStringContainsString('<fx:DocumentFileName>factur-x.xml</fx:DocumentFileName>', $result);
        $this->assertStringContainsString('<fx:Version>1.0</fx:Version>', $result);
        $this->assertStringContainsString('<fx:ConformanceLevel>EN 16931</fx:ConformanceLevel>', $result);
        $this->assertStringContainsString(
            '<pdfaSchema:schema>Factur-X PDFA Extension Schema</pdfaSchema:schema>',
            $result,
        );
        $this->assertStringContainsString('<pdfaProperty:name>ConformanceLevel</pdfaProperty:name>', $result);
    }

    /** @throws \Throwable */
    public function testGetOutXMPKeepsProfilePropertiesAlongsideCustomFragments(): void
    {
        $obj = $this->getFacturXTestObject();
        $obj->setCustomXMP('x:xmpmeta.rdf:RDF', '<!--CUSTOM-->');
        $obj->setCustomXMP('x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag', '<!--CUSTOMBAG-->');

        $result = $obj->exposeGetOutXMP();

        // A custom fragment on the same XMP key must not replace the profile block.
        $this->assertStringContainsString('<!--CUSTOM-->', $result);
        $this->assertStringContainsString('<!--CUSTOMBAG-->', $result);
        $this->assertStringContainsString('<fx:DocumentType>INVOICE</fx:DocumentType>', $result);
        $this->assertStringContainsString('<pdfaSchema:prefix>fx</pdfaSchema:prefix>', $result);
    }

    /** @throws \Throwable */
    public function testGetOutXMPWithFacturXIsWellFormedXml(): void
    {
        $result = $this->getFacturXTestObject()->exposeGetOutXMP();

        $start = \strpos($result, '<x:xmpmeta');
        $end = \strpos($result, '</x:xmpmeta>');
        $this->assertIsInt($start);
        $this->assertIsInt($end);

        $doc = new \DOMDocument();
        $this->assertTrue($doc->loadXML(\substr($result, $start, $end - $start + 12)));
    }

    /** @throws \Throwable */
    public function testGetOutXMPOmitsTheProfileBlockWithoutSetFacturX(): void
    {
        $obj = new TestablMetaInfo(mode: \Com\Tecnick\Pdf\PdfConformance::Pdfa3);

        $result = $obj->exposeGetOutXMP();

        $this->assertStringNotContainsString('DocumentFileName', $result);
        $this->assertStringNotContainsString('urn:factur-x:', $result);
    }

    /** @throws \Throwable */
    public function testGetOutMetaInfoWarnsWhenFacturXIsNotPdfa3(): void
    {
        $obj = new TestablMetaInfo();
        $obj->setFacturX('<rsm:CrossIndustryInvoice/>');

        $warned = false;
        \set_error_handler(static function (int $errno, string $errstr) use (&$warned): bool {
            if ($errno !== E_USER_WARNING || !\str_contains($errstr, 'requires PDF/A-3')) {
                return false;
            }

            $warned = true;
            return true;
        });

        try {
            $obj->exposeGetOutMetaInfo();
        } finally {
            \restore_error_handler();
        }

        $this->assertTrue($warned);
    }

    /** @throws \Throwable */
    public function testSetViewerPreferencesStoresPreferences(): void
    {
        $obj = $this->getTestObject();
        $pref = ['HideToolbar' => true, 'NumCopies' => 2, 'PrintScaling' => 'none'];

        $this->assertSame($obj, $obj->setViewerPreferences($pref));
        $this->assertSame($pref, $this->getObjectProperty($obj, 'viewerpref'));
    }

    /**
     * @param ?array<string, mixed> $viewerPref
     * @throws \Throwable
     */
    #[DataProvider('pagePrintScalingFixtureProvider')]
    public function testGetPagePrintScalingReturnsExpectedValue(
        ?array $viewerPref,
        #[\SensitiveParameter]
        string $expectedToken,
    ): void {
        $obj = $this->getInternalTestObject();
        if ($viewerPref !== null) {
            $this->setObjectProperty($obj, 'viewerpref', $viewerPref);
        }

        $result = $obj->exposeGetPagePrintScaling();

        $this->assertStringContainsString('/PrintScaling', $result);
        $this->assertStringContainsString($expectedToken, $result);
    }

    /** @throws \Throwable */
    public function testGetDuplexModeReturnsEmptyByDefault(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetDuplexMode();

        $this->assertSame('', $result);
    }

    /** @throws \Throwable */
    public function testGetPageBoxNameReturnsMappedValueWhenAvailable(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'page', new TestableObjPageForMetaInfo());
        $this->setObjectProperty($obj, 'viewerpref', ['ViewArea' => 'MediaBox']);

        $result = $obj->exposeGetPageBoxName('ViewArea');

        $this->assertSame(' /ViewArea /MediaBox', $result);
    }

    /** @throws \Throwable */
    public function testGetBooleanModeReturnsEmptyWhenNotSet(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetBooleanMode('HideToolbar');

        $this->assertSame('', $result);
    }

    /** @throws \Throwable */
    #[DataProvider('duplexModeFixtureProvider')]
    public function testGetDuplexModeReturnsMappedValue(string $duplexMode, string $expectedOutput): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'viewerpref', ['Duplex' => $duplexMode]);

        $result = $obj->exposeGetDuplexMode();

        $this->assertStringContainsString($expectedOutput, $result);
    }

    /** @throws \Throwable */
    #[DataProvider('booleanModeFixtureProvider')]
    public function testGetBooleanModeReturnsMappedValue(bool $value, string $expectedWord): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'viewerpref', ['HideToolbar' => $value]);

        $result = $obj->exposeGetBooleanMode('HideToolbar');

        $this->assertStringContainsString('/HideToolbar ' . $expectedWord, $result);
    }

    /** @throws \Throwable */
    public function testGetFormattedDateReturnsPdfDateStyle(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetFormattedDate(1710000000);

        $this->assertMatchesRegularExpression('/^[0-9]{14}[\+\-Z\']/', $result);
    }

    /** @throws \Throwable */
    public function testGetXMPFormattedDateReturnsIsoStyle(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetXMPFormattedDate(1710000000);

        $this->assertStringContainsString('T', $result);
    }

    /** @throws \Throwable */
    public function testGetOutDateTimeStringBuildsEscapedDate(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetOutDateTimeString(1710000000, 1);

        $this->assertStringContainsString('D:', $result);
    }

    /** @throws \Throwable */
    public function testGetOutDateTimeStringUsesDocumentTimeWhenInputIsZero(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'doctime', 1710001234);

        $result = $obj->exposeGetOutDateTimeString(0, 1);

        $this->assertStringContainsString('D:', $result);
    }

    /** @throws \Throwable */
    public function testGetEscapedXMLEscapesSpecialChars(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetEscapedXML('<a&b>');

        $this->assertSame('&lt;a&amp;b&gt;', $result);
    }

    /** @throws \Throwable */
    public function testGetOutMetaInfoContainsDocumentInfoKeys(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setCreator('Test Creator');

        $result = $obj->exposeGetOutMetaInfo();

        $this->assertStringContainsString('/Creator', $result);
        $this->assertStringContainsString('/Producer', $result);
        $this->assertStringContainsString('/Trapped /False', $result);
    }

    /** @throws \Throwable */
    public function testGetOutMetaInfoOmitsUnsetDocumentInfoKeys(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetOutMetaInfo();

        // An unset entry carries no information: it is omitted rather than
        // filled with a placeholder value.
        foreach (['/Creator', '/Author', '/Subject', '/Title', '/Keywords'] as $key) {
            $this->assertStringNotContainsString($key, $result);
        }

        $this->assertStringContainsString('/Producer', $result);
    }

    /** @throws \Throwable */
    public function testGetOutXMPOmitsUnsetDublinCoreProperties(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetOutXMP();

        foreach (['<dc:title>', '<dc:creator>', '<dc:description>', '<dc:subject>', '<xmp:CreatorTool>'] as $tag) {
            $this->assertStringNotContainsString($tag, $result);
        }

        $this->assertStringContainsString('<dc:format>application/pdf</dc:format>', $result);
        $this->assertStringContainsString('<pdf:Producer>', $result);
    }

    /** @throws \Throwable */
    public function testGetOutXMPContainsMetadataStreamStructure(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetOutXMP();

        $this->assertStringContainsString('/Type /Metadata', $result);
        $this->assertStringContainsString('<x:xmpmeta', $result);
        $this->assertStringContainsString('endobj', $result);
    }

    /** @throws \Throwable */
    public function testGetOutXMPIncludesPdfaBlockWhenPdfaEnabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfa', 3);
        $this->setObjectProperty($obj, 'pdfaConformance', 'U');

        $result = $obj->exposeGetOutXMP();

        $this->assertStringContainsString('<pdfaid:part>3</pdfaid:part>', $result);
        $this->assertStringContainsString('<pdfaid:conformance>U</pdfaid:conformance>', $result);
    }

    /** @throws \Throwable */
    public function testGetOutXMPDescribesAdobePdfExtensionSchemaForTrapped(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfa', 3);

        $result = $obj->exposeGetOutXMP();

        // pdf:Trapped mirrors the Info dictionary /Trapped entry but is not part of the
        // predefined set, so the schema entry describing it has to be complete.
        $this->assertStringContainsString('<pdf:Trapped>False</pdf:Trapped>', $result);
        $this->assertStringContainsString('<pdfaSchema:prefix>pdf</pdfaSchema:prefix>', $result);
        $this->assertStringContainsString('<pdfaSchema:schema>Adobe PDF Schema</pdfaSchema:schema>', $result);
        $this->assertStringContainsString('<pdfaProperty:name>Trapped</pdfaProperty:name>', $result);
    }

    /** @throws \Throwable */
    #[DataProvider('conformanceModeFixtureProvider')]
    public function testGetOutXMPExtensionSchemasAllDeclareValueType(
        string $property,
        string|int|bool $value,
        int $expected,
    ): void {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, $property, $value);

        $result = $obj->exposeGetOutXMP();

        $this->assertSame($expected, \substr_count($result, '<pdfaSchema:schema>'));
        $this->assertSame($expected, \substr_count($result, '<pdfaSchema:valueType>'));
    }

    /** @throws \Throwable */
    public function testGetOutXMPOmitsExtensionSchemasWithoutConformanceMode(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetOutXMP();

        $this->assertStringNotContainsString('pdfaExtension:schemas', $result);
    }

    /** @throws \Throwable */
    #[DataProvider('customXmpExtensionKeyFixtureProvider')]
    public function testGetOutXMPKeepsCustomXMPWithoutConformanceMode(string $key, bool $hasSchemas): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setCustomXMP($key, '<!--CUSTOM-->');

        $result = $obj->exposeGetOutXMP();

        $this->assertStringContainsString('<!--CUSTOM-->', $result);
        $this->assertStringContainsString('xmlns:pdfaExtension=', $result);
        $this->assertSame($hasSchemas, \str_contains($result, '<pdfaExtension:schemas>'));
    }

    /** @throws \Throwable */
    public function testGetOutXMPDeclaresPdfaidExtensionSchemaOnlyWhenPdfaEnabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfa', 3);

        $result = $obj->exposeGetOutXMP();

        $this->assertStringContainsString('<pdfaSchema:prefix>pdfaid</pdfaSchema:prefix>', $result);

        $plain = $this->getInternalTestObject();
        $plain->setCustomXMP('x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag', '<!--CUSTOM-->');

        $this->assertStringNotContainsString(
            '<pdfaSchema:prefix>pdfaid</pdfaSchema:prefix>',
            $plain->exposeGetOutXMP(),
        );
    }

    /** @throws \Throwable */
    #[DataProvider('pdfuaExtensionSchemaFixtureProvider')]
    public function testGetOutXMPDeclaresPdfuaidExtensionSchema(string $pdfuaMode, bool $hasRev): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', $pdfuaMode);

        $result = $obj->exposeGetOutXMP();

        $this->assertStringContainsString('<pdfaSchema:prefix>pdfuaid</pdfaSchema:prefix>', $result);
        $this->assertStringContainsString('<pdfaSchema:schema>PDF/UA Universal Accessibility Schema', $result);
        $this->assertSame($hasRev, \str_contains($result, '<pdfaProperty:name>rev</pdfaProperty:name>'));
    }

    /** @throws \Throwable */
    public function testGetOutXMPDeclaresPdfxidExtensionSchemaWhenPdfxEnabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx4');

        $result = $obj->exposeGetOutXMP();

        $this->assertStringContainsString('<pdfaSchema:prefix>pdfxid</pdfaSchema:prefix>', $result);
        $this->assertStringContainsString('<pdfaProperty:name>GTS_PDFXVersion</pdfaProperty:name>', $result);
    }

    /** @throws \Throwable */
    #[DataProvider('conformanceModeFixtureProvider')]
    public function testGetOutXMPIsWellFormedXmlInEveryMode(
        string $property,
        string|int|bool $value,
        int $expected,
    ): void {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, $property, $value);
        $obj->setCustomXMP('x:xmpmeta.rdf:RDF.rdf:Description', '<!--CUSTOM-->');

        $result = $obj->exposeGetOutXMP();

        $this->assertSame($expected > 0, \str_contains($result, '<pdfaExtension:schemas>'));

        $start = \strpos($result, '<x:xmpmeta');
        $end = \strpos($result, '</x:xmpmeta>');
        $this->assertIsInt($start);
        $this->assertIsInt($end);

        $xml = \substr($result, $start, $end - $start + 12);

        $doc = new \DOMDocument();
        $this->assertTrue($doc->loadXML($xml));
    }

    /** @throws \Throwable */
    public function testGetOutXMPIncludesPdfuaBlockWhenPdfuaEnabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua2');

        $result = $obj->exposeGetOutXMP();

        $this->assertStringContainsString('xmlns:pdfuaid="http://www.aiim.org/pdfua/ns/id/"', $result);
        $this->assertStringContainsString('<pdfuaid:part>2</pdfuaid:part>', $result);
    }

    /** @throws \Throwable */
    #[DataProvider('pdfxVersionFixtureProvider')]
    public function testSetPDFVersionHonorsPdfxModes(
        string $pdfxMode,
        string $inputVersion,
        string $expectedVersion,
    ): void {
        $obj = $this->getTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->setObjectProperty($obj, 'pdfxMode', $pdfxMode);

        $obj->setPDFVersion($inputVersion);

        $this->assertSame($expectedVersion, $this->getObjectProperty($obj, 'pdfver'));
    }

    /** @throws \Throwable */
    #[DataProvider('pdfxGtsVersionStringFixtureProvider')]
    public function testGetGtsPdfxVersionStringReturnsExpectedValue(string $pdfxMode, string $expected): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->setObjectProperty($obj, 'pdfxMode', $pdfxMode);

        $this->assertSame($expected, $obj->exposeGetGtsPdfxVersionString());
    }

    /** @throws \Throwable */
    public function testGetOutMetaInfoIncludesGtsPdfxVersionWhenPdfxEnabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx4');
        // PDF/X requires a title; without one the output warns.
        $obj->setTitle('PDF/X test document');

        $result = $obj->exposeGetOutMetaInfo();

        // The key is a PDF name (ASCII); the value is encoded as a PDF text string.
        $this->assertStringContainsString('/GTS_PDFXVersion', $result);
    }

    /** @throws \Throwable */
    public function testGetOutMetaInfoOmitsGtsPdfxVersionWhenPdfxDisabled(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetOutMetaInfo();

        $this->assertStringNotContainsString('/GTS_PDFXVersion', $result);
    }

    /** @throws \Throwable */
    public function testGetOutXMPIncludesPdfxidBlockWhenPdfxEnabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx1a');

        $result = $obj->exposeGetOutXMP();

        $this->assertStringContainsString('xmlns:pdfxid="http://www.npes.org/pdfx/ns/id/"', $result);
        $this->assertStringContainsString('<pdfxid:GTS_PDFXVersion>PDF/X-1a:2003</pdfxid:GTS_PDFXVersion>', $result);
    }

    /** @throws \Throwable */
    public function testGetOutXMPOmitsPdfxidBlockWhenPdfxDisabled(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetOutXMP();

        $this->assertStringNotContainsString('pdfxid', $result);
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testGetOutViewerPrefForceDisplayDocTitleTrueInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        $result = $obj->exposeGetOutViewerPref();

        $this->assertStringContainsString('/DisplayDocTitle true', $result);
    }

    /** @throws \Throwable */
    public function testGetOutViewerPrefRespectsExplicitDisplayDocTitleFalseInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');
        $obj->setViewerPreferences(['DisplayDocTitle' => false]);

        $result = $obj->exposeGetOutViewerPref();

        $this->assertStringContainsString('/DisplayDocTitle false', $result);
    }

    /** @throws \Throwable */
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
            'pdfx1a_pins_1_4_when_lower' => ['pdfx1a', '1.1', '1.4'],
            'pdfx1a_pins_1_4_when_higher' => ['pdfx1a', '1.6', '1.4'],
            'pdfx3_pins_1_4' => ['pdfx3', '1.2', '1.4'],
            'pdfx4_pins_1_6_when_lower' => ['pdfx4', '1.3', '1.6'],
            'pdfx4_pins_1_6_when_higher' => ['pdfx4', '1.7', '1.6'],
            'pdfx5_pins_1_6' => ['pdfx5', '1.4', '1.6'],
            'pdfx_generic_pins_1_4' => ['pdfx', '1.1', '1.4'],
            'pdfx_ignores_malformed_version' => ['pdfx1a', '1.A', '1.4'],
        ];
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function pdfxGtsVersionStringFixtureProvider(): array
    {
        return [
            'pdfx1a' => ['pdfx1a', 'PDF/X-1a:2003'],
            'pdfx3' => ['pdfx3', 'PDF/X-3:2003'],
            'pdfx4' => ['pdfx4', 'PDF/X-4:2010'],
            'pdfx5' => ['pdfx5', 'PDF/X-5g:2010'],
            'pdfx_generic_defaults_to_x3' => ['pdfx', 'PDF/X-3:2003'],
        ];
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function customXmpExtensionKeyFixtureProvider(): array
    {
        return [
            'bag' => ['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag', true],
            'schemas' => ['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas', true],
            'description' => ['x:xmpmeta.rdf:RDF.rdf:Description', false],
        ];
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function pdfuaExtensionSchemaFixtureProvider(): array
    {
        return [
            'pdfua_defaults_to_part_1' => ['pdfua', false],
            'pdfua1_has_no_rev' => ['pdfua1', false],
            'pdfua2_has_rev' => ['pdfua2', true],
        ];
    }

    /** @return array<string, array{0: string, 1: string|int|bool, 2: int}> */
    public static function conformanceModeFixtureProvider(): array
    {
        return [
            // Every conformance mode describes the xmpMM and Adobe PDF schemas plus
            // the schema identifying the mode itself.
            'none' => ['pdfa', 0, 0],
            'pdfa1' => ['pdfa', 1, 3],
            'pdfa2' => ['pdfa', 2, 3],
            'pdfa3' => ['pdfa', 3, 3],
            'pdfua1' => ['pdfuaMode', 'pdfua1', 3],
            'pdfua2' => ['pdfuaMode', 'pdfua2', 3],
            'pdfx' => ['pdfx', true, 3],
        ];
    }

    /** @return array<string, array{0: int, 1: string, 2: string}> */
    public static function pdfaVersionFixtureProvider(): array
    {
        return [
            'pdfa1_forces_1_4' => [1, '1.9', '1.4'],
            'pdfa2_forces_1_7' => [2, '1.5', '1.7'],
            'pdfa3_forces_1_7' => [3, '1.5', '1.7'],
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
