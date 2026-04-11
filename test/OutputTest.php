<?php

/**
 * OutputTest.php
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

class TestableOutput extends \Com\Tecnick\Pdf\Tcpdf
{
    public function exposeGetPDFObjectOffsets(string $data): array
    {
        return $this->getPDFObjectOffsets($data);
    }

    public function exposeGetOutPDFXref(array $offset): string
    {
        return $this->getOutPDFXref($offset);
    }

    public function exposeGetOutPDFTrailer(): string
    {
        return $this->getOutPDFTrailer();
    }

    public function exposeGetColorStringFromPercArray(array $color): string
    {
        return self::getColorStringFromPercArray($color);
    }

    public function exposeGetAnnotationBorder(array $annot): string
    {
        return $this->getAnnotationBorder($annot);
    }

    public function exposeGetOutAnnotationFlags(array $annot): string
    {
        return $this->getOutAnnotationFlags($annot);
    }

    public function exposeGetAnnotationFlagsCode(int|array $flags): int
    {
        return $this->getAnnotationFlagsCode($flags);
    }

    public function exposeGetOnOff(mixed $val): string
    {
        return $this->getOnOff($val);
    }

    public function exposeGetOutDestinations(): string
    {
        return $this->getOutDestinations();
    }

    public function exposeSortBookmarks(): void
    {
        $this->sortBookmarks();
    }

    public function exposeProcessPrevNextBookmarks(): int
    {
        return $this->processPrevNextBookmarks();
    }

    public function exposeGetOutBookmarks(): string
    {
        return $this->getOutBookmarks();
    }

    public function exposeGetOutJavascript(): string
    {
        return $this->getOutJavascript();
    }

    public function exposeGetXObjectDict(): string
    {
        return $this->getXObjectDict();
    }

    public function exposeGetLayerDict(): string
    {
        return $this->getLayerDict();
    }

    public function exposeGetOutResourcesDict(): string
    {
        return $this->getOutResourcesDict();
    }

    public function exposeGetOutAnnotationOptSubtypeLine(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeLine($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeSquare(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeSquare($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeCircle(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeCircle($annot);
    }

    public function exposeGetOutAnnotationOptSubtypePolygon(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypePolygon($annot);
    }

    public function exposeGetOutAnnotationOptSubtypePolyline(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypePolyline($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeHighlight(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeHighlight($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeUnderline(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeUnderline($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeSquiggly(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeSquiggly($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeStrikeout(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeStrikeout($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeStamp(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeStamp($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeCaret(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeCaret($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeInk(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeInk($annot);
    }

    public function exposeGetOutAnnotationOptSubtypePopup(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypePopup($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeMovie(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeMovie($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeScreen(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeScreen($annot);
    }

    public function exposeGetOutAnnotationOptSubtypePrintermark(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypePrintermark($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeRedact(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeRedact($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeTrapnet(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeTrapnet($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeWatermark(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeWatermark($annot);
    }

    public function exposeGetOutAnnotationOptSubtype3D(array $annot): string
    {
        return $this->getOutAnnotationOptSubtype3D($annot);
    }

    public function exposeGetAnnotationRadioButtons(array $annot): string
    {
        return $this->getAnnotationRadioButtons($annot);
    }

    public function exposeGetAnnotationAppearanceStream(array $annot, float $width = 0, float $height = 0): array
    {
        return $this->getAnnotationAppearanceStream($annot, $width, $height);
    }

    public function exposeGetOutAnnotationMarkups(array $annot, int $oid): string
    {
        return $this->getOutAnnotationMarkups($annot, $oid);
    }

    public function exposeGetOutAnnotationOptSubtype(array $annot, int $pagenum, int $oid, int $key): string
    {
        return $this->getOutAnnotationOptSubtype($annot, $pagenum, $oid, $key);
    }

    public function exposeGetOutAnnotationOptSubtypeText(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeText($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeLink(array $annot, int $pagenum, int $oid): string
    {
        return $this->getOutAnnotationOptSubtypeLink($annot, $pagenum, $oid);
    }

    public function exposeGetOutAnnotationOptSubtypeFreetext(array $annot, int $oid): string
    {
        return $this->getOutAnnotationOptSubtypeFreetext($annot, $oid);
    }

    public function exposeGetOutAnnotationOptSubtypeFileattachment(array $annot, int $key): string
    {
        return $this->getOutAnnotationOptSubtypeFileattachment($annot, $key);
    }

    public function exposeGetOutAnnotationOptSubtypeSound(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeSound($annot);
    }

    public function exposeGetOutAnnotationOptSubtypeWidget(array $annot, int $oid): string
    {
        return $this->getOutAnnotationOptSubtypeWidget($annot, $oid);
    }

    public function exposeGetOutPDFHeader(): string
    {
        return $this->getOutPDFHeader();
    }

    public function exposeGetOutPDFBody(): string
    {
        return $this->getOutPDFBody();
    }

    public function exposeGetOutCatalog(): string
    {
        return $this->getOutCatalog();
    }

    public function exposeGetOutICC(): string
    {
        return $this->getOutICC();
    }

    public function exposeGetOutputIntentsSrgb(): string
    {
        return $this->getOutputIntentsSrgb();
    }

    public function exposeGetOutputIntentsPdfX(): string
    {
        return $this->getOutputIntentsPdfX();
    }

    public function exposeGetOutputIntents(): string
    {
        return $this->getOutputIntents();
    }

    public function exposeGetPDFLayers(): string
    {
        return $this->getPDFLayers();
    }

    public function exposeGetOutOCG(): string
    {
        return $this->getOutOCG();
    }

    public function exposeGetOutAPXObjects(float $width = 0, float $height = 0, string $stream = ''): string
    {
        return $this->getOutAPXObjects($width, $height, $stream);
    }

    public function exposeGetOutXObjects(): string
    {
        return $this->getOutXObjects();
    }

    public function exposeGetOutEmbeddedFiles(): string
    {
        return $this->getOutEmbeddedFiles();
    }

    public function exposeGetOutAnnotations(): string
    {
        return $this->getOutAnnotations();
    }

    public function exposeGetOutSignatureFields(): string
    {
        return $this->getOutSignatureFields();
    }

    public function exposeSignDocument(string $pdfdoc): string
    {
        return $this->signDocument($pdfdoc);
    }

    public function exposeApplySignatureTimestamp(string $signature): string
    {
        return $this->applySignatureTimestamp($signature);
    }

    public function exposeGetOutSignature(): string
    {
        return $this->getOutSignature();
    }

    public function exposeGetOutSignatureDocMDP(): string
    {
        return $this->getOutSignatureDocMDP();
    }

    public function exposeGetOutSignatureUserRights(): string
    {
        return $this->getOutSignatureUserRights();
    }

    public function exposeGetOutSignatureInfo(int $oid): string
    {
        return $this->getOutSignatureInfo($oid);
    }

    public function setOutputState(int $pon, array $objid, string $fileid = 'ABC123', int $encryptObjId = 0): void
    {
        $this->pon = $pon;
        $this->objid = \array_replace($this->objid, $objid);
        $this->fileid = $fileid;

        $ref = new \ReflectionObject($this->encrypt);
        $prop = $ref->getProperty('encryptdata');
        $prop->setAccessible(true);
        $data = $prop->getValue($this->encrypt);
        $data['objid'] = $encryptObjId;
        $prop->setValue($this->encrypt, $data);
    }

    public function setPdfaMode(int $pdfa): void
    {
        $this->pdfa = $pdfa;
    }

    public function getOutlinesState(): array
    {
        return $this->outlines;
    }

    public function getJavascriptTree(): string
    {
        return $this->jstree;
    }
}

class OutputTest extends TestUtil
{
    public static function setUpBeforeClass(): void
    {
        if (!\defined('K_PATH_FONTS')) {
            $fonts = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts');
            \define('K_PATH_FONTS', $fonts);
        }
    }

    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    protected function getInternalTestObject(): TestableOutput
    {
        return new TestableOutput();
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

    private function setObjectProperty(object $obj, string $name, mixed $value): void
    {
        $ref = new \ReflectionClass($obj);
        while ($ref !== false) {
            if ($ref->hasProperty($name)) {
                $prop = $ref->getProperty($name);
                $prop->setAccessible(true);
                $prop->setValue($obj, $value);
                return;
            }
            $ref = $ref->getParentClass();
        }

        $this->fail('Property not found: ' . $name);
    }

    private function initFontAndPage(\Com\Tecnick\Pdf\Tcpdf $obj): array
    {
        $font = $this->getObjectProperty($obj, 'font');
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);
        return $obj->addPage();
    }

    private function addRawPageWithObjectNumber(\Com\Tecnick\Pdf\Tcpdf $obj, int $objectNumber): array
    {
        $page = $this->getObjectProperty($obj, 'page');
        $data = $page->add([]);
        $pages = $this->getObjectProperty($page, 'page');
        $pages[$data['pid']]['n'] = $objectNumber;
        $this->setObjectProperty($page, 'page', $pages);
        return $page->getPage($data['pid']);
    }

    public function testGetOutPDFStringReturnsRawPdfDocument(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $raw = $obj->getOutPDFString();

        $this->assertStringStartsWith('%PDF-', $raw);
        $this->assertStringContainsString('/Type /Catalog', $raw);
        $this->assertStringContainsString("startxref\n", $raw);
        $this->assertStringEndsWith("%%EOF\n", $raw);
    }

    public function testRenderPDFInCliEchoesRawString(): void
    {
        $obj = $this->getTestObject();

        \ob_start();
        $obj->renderPDF('raw-pdf-data');
        $out = (string) \ob_get_clean();

        $this->assertSame('raw-pdf-data', $out);
    }

    public function testDownloadPDFRejectsExistingOutputBufferContent(): void
    {
        $obj = $this->getTestObject();
        $level = \ob_get_level();

        \ob_start();
        echo 'already-sent';

        try {
            $obj->downloadPDF('raw-pdf-data');
            $this->fail('Expected downloadPDF to throw when output buffer already contains content.');
        } catch (\Com\Tecnick\Pdf\Exception $e) {
            $this->assertStringContainsString('cannot be sent', $e->getMessage());
        } finally {
            while (\ob_get_level() > $level) {
                \ob_end_clean();
            }
        }
    }

    public function testDownloadPDFOutputsRawDataWhenBufferIsClean(): void
    {
        $obj = $this->getTestObject();

        \ob_start();
        $obj->downloadPDF('raw-pdf-data');
        $out = (string) \ob_get_clean();

        $this->assertSame('raw-pdf-data', $out);
    }

    public function testSavePDFWritesRawPdfToDisk(): void
    {
        $obj = $this->getTestObject();
        $obj->setPDFFilename('saved-output.pdf');
        $dir = \sys_get_temp_dir() . '/tc-lib-pdf-output-' . \bin2hex(\random_bytes(4));
        $this->assertTrue(\mkdir($dir, 0777, true));

        try {
            $obj->savePDF($dir, 'raw-pdf-data');
            $filepath = $dir . '/saved-output.pdf';

            $this->assertFileExists($filepath);
            $this->assertSame('raw-pdf-data', (string) \file_get_contents($filepath));
        } finally {
            @\unlink($dir . '/saved-output.pdf');
            @\rmdir($dir);
        }
    }

    public function testGetMIMEAttachmentPDFReturnsBase64Attachment(): void
    {
        $obj = $this->getTestObject();
        $obj->setPDFFilename('mail-output.pdf');

        $mime = $obj->getMIMEAttachmentPDF('raw-pdf-data');

        $this->assertStringContainsString('Content-Type: application/pdf;', $mime);
        $this->assertStringContainsString('name="mail-output.pdf"', $mime);
        $this->assertStringContainsString('Content-Transfer-Encoding: base64', $mime);
        $this->assertStringContainsString(\chunk_split(\base64_encode('raw-pdf-data'), 76, "\r\n"), $mime);
    }

    public function testGetPDFObjectOffsetsReturnsSortedPositions(): void
    {
        $obj = $this->getInternalTestObject();
        $data = "3 0 obj\nthird\nendobj\n1 0 obj\nfirst\nendobj\n10 0 obj\ntenth\nendobj\n";

        $offsets = $obj->exposeGetPDFObjectOffsets($data);

        $this->assertSame([
            1 => \strpos($data, "1 0 obj\n"),
            3 => \strpos($data, "3 0 obj\n"),
            10 => \strpos($data, "10 0 obj\n"),
        ], $offsets);
    }

    public function testGetOutPDFXrefFormatsPresentAndMissingObjects(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setOutputState(4, ['catalog' => 7, 'info' => 8]);

        $xref = $obj->exposeGetOutPDFXref([
            1 => 9,
            3 => 30,
            4 => 50,
        ]);

        $expected = "xref\n"
            . "0 5\n"
            . "0000000000 65535 f \n"
            . "0000000009 00000 n \n"
            . "0000000000 00006 f \n"
            . "0000000030 00000 n \n"
            . "0000000050 00000 n \n";

        $this->assertSame($expected, $xref);
    }

    public function testGetOutPDFTrailerIncludesCatalogInfoAndFileId(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setOutputState(4, ['catalog' => 7, 'info' => 8], 'FEEDBEEF');

        $trailer = $obj->exposeGetOutPDFTrailer();

        $this->assertStringContainsString("trailer\n", $trailer);
        $this->assertStringContainsString(' /Size 5 /Root 7 0 R /Info 8 0 R', $trailer);
        $this->assertStringContainsString(' /ID [ <FEEDBEEF> <FEEDBEEF> ]', $trailer);
        $this->assertStringNotContainsString(' /Encrypt ', $trailer);
    }

    public function testGetOutPDFTrailerIncludesEncryptReferenceWhenPresent(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setOutputState(4, ['catalog' => 7, 'info' => 8], 'FEEDBEEF', 12);

        $trailer = $obj->exposeGetOutPDFTrailer();

        $this->assertStringContainsString(' /Encrypt 12 0 R', $trailer);
    }

    public function testGetColorStringFromPercArrayFormatsSupportedColorSpaces(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame('[0.500000]', $obj->exposeGetColorStringFromPercArray([0.5]));
        $this->assertSame('[0.100000 0.200000 0.300000]', $obj->exposeGetColorStringFromPercArray([0.1, 0.2, 0.3]));
        $this->assertSame(
            '[0.100000 0.200000 0.300000 0.400000]',
            $obj->exposeGetColorStringFromPercArray([0.1, 0.2, 0.3, 0.4])
        );
        $this->assertSame('[]', $obj->exposeGetColorStringFromPercArray([]));
    }

    public function testGetAnnotationFlagsCodeAndOutputHandlePdfa(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame(580, $obj->exposeGetAnnotationFlagsCode(['print', 'readonly', 'lockedcontents']));
        $this->assertSame(' /F 128', $obj->exposeGetOutAnnotationFlags(['opt' => ['f' => ['locked']]]));

        $obj->setPdfaMode(1);
        $this->assertSame(' /F 6', $obj->exposeGetOutAnnotationFlags(['opt' => ['f' => ['hidden']]]));
    }

    public function testGetAnnotationBorderFormatsBorderStyleAndEffect(): void
    {
        $obj = $this->getInternalTestObject();
        $annot = [
            'opt' => [
                'bs' => ['w' => 2, 's' => 'D', 'd' => [3, 1]],
                'be' => ['s' => 'C', 'i' => 1.5],
            ],
        ];

        $border = $obj->exposeGetAnnotationBorder($annot);

        $this->assertStringContainsString(' /BS << /Type /Border /W 2 /S /D /D [ 3 1] >>', $border);
        $this->assertStringContainsString(' /BE << /S /C /I  1.500000>>', $border);
    }

    public function testGetOnOffMapsTruthyAndFalsyValues(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame('ON', $obj->exposeGetOnOff(true));
        $this->assertSame('OFF', $obj->exposeGetOnOff(0));
    }

    public function testGetOutDestinationsSerializesNamedDestinationCoordinates(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 3);
        $dest = $obj->setNamedDestination('Section One', $page['pid'], 10.0, 20.0);

        $out = $obj->exposeGetOutDestinations();

        $this->assertStringStartsWith("1 0 obj\n<< ", $out);
        $this->assertStringContainsString(' /Section#20One [3 0 R /XYZ 28.346457 785.197087 null]', $out);
        $this->assertStringEndsWith(" >>\nendobj\n", $out);
        $this->assertSame('#Section#20One', $dest);
    }

    public function testSortBookmarksOrdersByPageThenOriginalIndex(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page1 = $this->addRawPageWithObjectNumber($obj, 3);
        $page2 = $this->addRawPageWithObjectNumber($obj, 4);

        $obj->setBookmark('Later on page 2', '', 0, $page2['pid']);
        $obj->setBookmark('Earlier on page 1', '', 0, $page1['pid']);
        $obj->setBookmark('Also on page 2', '', 0, $page2['pid']);

        $obj->exposeSortBookmarks();
        $outlines = $obj->getOutlinesState();

        $this->assertSame('Earlier on page 1', $outlines[0]['t']);
        $this->assertSame('Later on page 2', $outlines[1]['t']);
        $this->assertSame('Also on page 2', $outlines[2]['t']);
    }

    public function testProcessPrevNextBookmarksAssignsHierarchyLinks(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 3);

        $obj->setBookmark('Root A', '', 0, $page['pid']);
        $obj->setBookmark('Child A1', '', 1, $page['pid']);
        $obj->setBookmark('Root B', '', 0, $page['pid']);

        $root = $obj->exposeProcessPrevNextBookmarks();
        $outlines = $obj->getOutlinesState();

        $this->assertSame(2, $root);
        $this->assertSame(3, $outlines[0]['parent']);
        $this->assertSame(1, $outlines[0]['first']);
        $this->assertSame(1, $outlines[0]['last']);
        $this->assertSame(0, $outlines[1]['parent']);
        $this->assertSame(2, $outlines[0]['next']);
        $this->assertSame(0, $outlines[2]['prev']);
        $this->assertSame(3, $outlines[2]['parent']);
    }

    public function testGetOutBookmarksSerializesOutlineObjectsAndRoot(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page1 = $this->addRawPageWithObjectNumber($obj, 3);
        $page2 = $this->addRawPageWithObjectNumber($obj, 4);

        $obj->setBookmark('First <b>Section</b>', '', 0, $page1['pid'], 12.0, 34.0, 'B', 'red');
        $obj->setBookmark('Second Section', '#target', 0, $page2['pid'], 1.0, 2.0, 'I');

        $out = $obj->exposeGetOutBookmarks();

        $this->assertStringContainsString('/Title (', $out);
        $this->assertStringContainsString("\x00F\x00i\x00r\x00s\x00t\x00 \x00S\x00e\x00c\x00t\x00i\x00o\x00n", $out);
        $this->assertStringContainsString('/Dest [3 0 R /XYZ 34.015748 745.512047 null]', $out);
        $this->assertStringContainsString('/F 2 /C [ 1.000000 0.000000 0.000000 ]', $out);
        $this->assertStringContainsString('/Dest /target', $out);
        $this->assertStringContainsString('/F 1 /C [0.0 0.0 0.0]', $out);
        $this->assertStringContainsString('/Type /Outlines', $out);
    }

    public function testGetOutJavascriptBuildsObjectsAndNameTree(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->appendRawJavaScript('app.alert("hi");');
        $obj->addRawJavaScriptObj('console.println("loaded");', true);
        $obj->addRawJavaScriptObj('console.println("skip");', false);

        $out = $obj->exposeGetOutJavascript();

        $this->assertStringContainsString('/S /JavaScript /JS ', $out);
        $this->assertStringContainsString("\x00a\x00p\x00p", $out);
        $this->assertStringContainsString("\x00l\x00o\x00a\x00d\x00e\x00d", $out);
        $this->assertStringContainsString("\x00s\x00k\x00i\x00p", $out);
        $this->assertSame('<< /Names [ (EmbeddedJS) 3 0 R (JS0) 1 0 R ] >>', $obj->getJavascriptTree());
    }

    public function testGetXObjectDictEmptyWhenNoImagesOrXObjects(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetXObjectDict();

        $this->assertSame(' /XObject << >>', $out);
    }

    public function testGetLayerDictEmptyWhenNoLayers(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetLayerDict();

        $this->assertSame('', $out);
    }

    public function testGetOutResourcesDictIncludesProcSetAndEmptyFontDict(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        // Initialize outfont property (normally done in getOutPDFBody)
        $font = $this->getObjectProperty($obj, 'font');
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        $pon = $this->getObjectProperty($obj, 'pon');
        $outfont = new \Com\Tecnick\Pdf\Font\Output($font->getFonts(), $pon, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        $out = $obj->exposeGetOutResourcesDict();

        $this->assertStringContainsString('1 0 obj', $out);
        $this->assertStringContainsString(' /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]', $out);
        $this->assertStringContainsString(' /XObject << >>', $out);
        $this->assertStringEndsWith(" >>\nendobj\n", $out);
    }

    public function testTodoAnnotationSubtypeHelpersCurrentlyReturnEmptyString(): void
    {
        $obj = $this->getInternalTestObject();
        $annot = [];

        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeLine($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeSquare($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeCircle($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypePolygon($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypePolyline($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeHighlight($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeUnderline($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeSquiggly($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeStrikeout($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeStamp($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeCaret($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeInk($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypePopup($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeMovie($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeScreen($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypePrintermark($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeRedact($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeTrapnet($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeWatermark($annot));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtype3D($annot));
    }

    public function testGetAnnotationRadioButtonsReturnsEmptyWhenNoKids(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetAnnotationRadioButtons(['txt' => 'missing', 'opt' => []]);

        $this->assertSame('', $out);
    }

    public function testGetAnnotationAppearanceStreamWithoutApReturnsOnlyAsFlag(): void
    {
        $obj = $this->getInternalTestObject();

        [$aas, $apx] = $obj->exposeGetAnnotationAppearanceStream(['opt' => ['as' => 'On']], 10.0, 5.0);

        $this->assertSame(' /AS /On', $aas);
        $this->assertSame('', $apx);
    }

    public function testGetOutAnnotationMarkupsReturnsMarkupFieldsForTextSubtype(): void
    {
        $obj = $this->getInternalTestObject();
        $annot = [
            'opt' => [
                'subtype' => 'text',
                't' => 'Author',
                'ca' => 0.5,
                'rc' => 'Reply',
                'subj' => 'Topic',
            ],
        ];

        $out = $obj->exposeGetOutAnnotationMarkups($annot, 10);

        $this->assertStringContainsString(' /T ', $out);
        $this->assertStringContainsString(' /CA 0.500000', $out);
        $this->assertStringContainsString(' /CreationDate ', $out);
        $this->assertStringContainsString(' /Subj ', $out);
    }

    public function testGetOutAnnotationOptSubtypeTextReturnsDefaultsAndState(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetOutAnnotationOptSubtypeText(['opt' => ['open' => true, 'statemodel' => 'Marked', 'state' => 'Accepted']]);

        $this->assertStringContainsString(' /Open true', $out);
        $this->assertStringContainsString(' /Name /Note', $out);
        $this->assertStringContainsString(' /StateModel /Marked', $out);
        $this->assertStringContainsString(' /State /Accepted', $out);
    }

    public function testGetOutAnnotationOptSubtypeLinkBuildsExternalUriAndDefaultHighlight(): void
    {
        $obj = $this->getInternalTestObject();
        $annot = ['txt' => 'https://example.com', 'opt' => []];

        $out = $obj->exposeGetOutAnnotationOptSubtypeLink($annot, 1, 10);

        $this->assertStringContainsString(' /S /URI', $out);
        $this->assertStringContainsString(' /URI ', $out);
        $this->assertStringContainsString(' /H /I', $out);
    }

    public function testGetOutAnnotationOptSubtypeFreetextFormatsKnownOptions(): void
    {
        $obj = $this->getInternalTestObject();
        $annot = [
            'n' => 10,
            'opt' => [
                'da' => '/F1 10 Tf',
                'q' => 2,
                'it' => 'FreeText',
                'rd' => [1, 2, 3, 4],
                'le' => 'Square',
            ],
        ];

        $out = $obj->exposeGetOutAnnotationOptSubtypeFreetext($annot, 10);

        $this->assertStringContainsString(' /DA ', $out);
        $this->assertStringContainsString(' /Q 2', $out);
        $this->assertStringContainsString(' /IT /FreeText', $out);
        $this->assertStringContainsString(' /RD [', $out);
        $this->assertStringContainsString(' /LE /Square', $out);
    }

    public function testGetOutAnnotationOptSubtypeFileattachmentHandlesPdfaAndEmbeddedFile(): void
    {
        $obj = $this->getInternalTestObject();
        $annot = ['opt' => ['fs' => 'doc.txt', 'name' => 'Tag']];

        $obj->setPdfaMode(1);
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeFileattachment($annot, 2));

        $obj->setPdfaMode(0);
        $this->setObjectProperty($obj, 'embeddedfiles', ['doc.txt' => ['f' => 7]]);
        $out = $obj->exposeGetOutAnnotationOptSubtypeFileattachment($annot, 2);

        $this->assertStringContainsString(' /FS 7 0 R', $out);
        $this->assertStringContainsString(' /Name /Tag', $out);
    }

    public function testGetOutAnnotationOptSubtypeSoundReturnsExpectedNameAndReference(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeSound(['opt' => []]));

        $this->setObjectProperty($obj, 'embeddedfiles', ['snd.wav' => ['f' => 9]]);
        $out = $obj->exposeGetOutAnnotationOptSubtypeSound(['opt' => ['fs' => 'snd.wav', 'name' => 'Mic']]);

        $this->assertStringContainsString(' /Sound 9 0 R', $out);
        $this->assertStringContainsString(' /Name /Mic', $out);
    }

    public function testGetOutAnnotationOptSubtypeWidgetIncludesBasicWidgetEntries(): void
    {
        $obj = $this->getInternalTestObject();
        $annot = [
            'txt' => 'field-key',
            'opt' => [
                'h' => 'N',
                't' => 'FieldName',
                'q' => 1,
                'ff' => [1, 2],
                'maxlen' => 12,
                'v' => 'abc',
                'dv' => 'def',
                'rv' => 'ghi',
                'da' => '/F1 10 Tf',
            ],
        ];

        $out = $obj->exposeGetOutAnnotationOptSubtypeWidget($annot, 10);

        $this->assertStringContainsString(' /H /N', $out);
        $this->assertStringContainsString(' /T ', $out);
        $this->assertStringContainsString(' /Ff 3', $out);
        $this->assertStringContainsString(' /MaxLen 12', $out);
        $this->assertStringContainsString(' /Q 1', $out);
        $this->assertStringContainsString(' /DA ', $out);
    }

    public function testGetOutAnnotationOptSubtypeDispatcherRoutesKnownAndUnknownSubtypes(): void
    {
        $obj = $this->getInternalTestObject();

        $textOut = $obj->exposeGetOutAnnotationOptSubtype(['opt' => ['subtype' => 'Text']], 1, 10, 0);
        $unkOut = $obj->exposeGetOutAnnotationOptSubtype(['opt' => ['subtype' => 'Unknown']], 1, 10, 0);

        $this->assertStringContainsString(' /Name /Note', $textOut);
        $this->assertSame('', $unkOut);
    }

    public function testGetOutPDFHeaderReturnsVersionedHeader(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetOutPDFHeader();

        $this->assertStringStartsWith('%PDF-', $out);
        $this->assertStringContainsString("%\xE2\xE3\xCF\xD3\n", $out);
    }

    public function testGetOutPDFBodyBuildsPageTreeResourcesAndCatalog(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $out = $obj->exposeGetOutPDFBody();

        $this->assertStringContainsString('/Type /Pages', $out);
        $this->assertStringContainsString('/Type /Catalog', $out);
        $this->assertStringContainsString('/Type /Page', $out);
    }

    public function testGetOutCatalogIncludesRequiredEntries(): void
    {
        $obj = $this->getInternalTestObject();
        $font = $this->getObjectProperty($obj, 'font');
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);
        $this->addRawPageWithObjectNumber($obj, 6);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4]);
        $this->setObjectProperty($obj, 'display', ['layout' => 'SinglePage', 'mode' => 'UseNone', 'zoom' => 'fullpage']);
        $this->setObjectProperty($obj, 'lang', ['a_meta_language' => 'en-US']);

        $out = $obj->exposeGetOutCatalog();

        $this->assertStringContainsString('/Type /Catalog', $out);
        $this->assertStringContainsString('/Pages 3 0 R', $out);
        $this->assertStringContainsString('/PageLayout /SinglePage', $out);
        $this->assertStringContainsString('/PageMode /UseNone', $out);
        $this->assertStringContainsString('/OpenAction [6 0 R /Fit]', $out);
        $this->assertStringContainsString('/Metadata 4 0 R', $out);
        $this->assertStringContainsString('/Lang ', $out);
    }

    public function testGetOutICCRespectsPdfaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->assertSame('', $obj->exposeGetOutICC());

        $obj->setPdfaMode(1);
        $out = $obj->exposeGetOutICC();

        $this->assertStringContainsString('/N 3', $out);
        $this->assertStringContainsString('endobj', $out);
    }

    public function testOutputIntentsHelpersHandleCatalogAndModes(): void
    {
        $obj = $this->getInternalTestObject();
        $this->assertSame('', $obj->exposeGetOutputIntents());

        $this->setObjectProperty($obj, 'objid', ['catalog' => 7, 'srgbicc' => 4]);
        $srgb = $obj->exposeGetOutputIntentsSrgb();
        $this->assertStringContainsString('/GTS_PDFA1', $srgb);
        $this->assertStringContainsString('/DestOutputProfile 4 0 R', $srgb);

        $this->setObjectProperty($obj, 'pdfx', true);
        $this->assertStringContainsString('/GTS_PDFX', $obj->exposeGetOutputIntentsPdfX());
        $this->assertStringContainsString('/GTS_PDFX', $obj->exposeGetOutputIntents());
    }

    public function testGetPDFLayersAndGetOutOCGDefaultAndNonEmpty(): void
    {
        $obj = $this->getInternalTestObject();
        $this->assertSame('', $obj->exposeGetPDFLayers());
        $this->assertSame('', $obj->exposeGetOutOCG());

        $this->setObjectProperty($obj, 'objid', ['catalog' => 7]);
        $this->setObjectProperty($obj, 'pdflayer', [[
            'name' => 'Layer A',
            'view' => true,
            'lock' => false,
            'intent' => '/View',
        ]]);

        $ocg = $obj->exposeGetOutOCG();
        $layers = $obj->exposeGetPDFLayers();

        $this->assertStringContainsString('/Type /OCG', $ocg);
        $this->assertStringContainsString('/OCProperties << /OCGs [', $layers);
    }

    public function testGetOutAPXObjectsAndXObjectsEmptyPaths(): void
    {
        $obj = $this->getInternalTestObject();

        $apx = $obj->exposeGetOutAPXObjects(12.0, 7.0, 'q Q');
        $xobj = $obj->exposeGetOutXObjects();

        $this->assertStringContainsString('/Subtype /Form', $apx);
        $this->assertStringContainsString('/BBox [0 0 12.000000 7.000000]', $apx);
        $this->assertSame('', $xobj);
    }

    public function testGetOutEmbeddedFilesAndAnnotationsDefaultEmpty(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame('', $obj->exposeGetOutEmbeddedFiles());
        $this->assertSame('', $obj->exposeGetOutAnnotations());
    }

    public function testSignatureHelpersDefaultPathsAndInfoFormatting(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame('', $obj->exposeGetOutSignatureFields());
        $this->assertSame('abc', $obj->exposeSignDocument('abc'));
        $this->assertSame('sigbin', $obj->exposeApplySignatureTimestamp('sigbin'));
        $this->assertSame('', $obj->exposeGetOutSignature());
        $this->assertStringContainsString('/TransformMethod /DocMDP', $obj->exposeGetOutSignatureDocMDP());

        $ur = $obj->exposeGetOutSignatureUserRights();
        $this->assertStringContainsString('/TransformMethod /UR3', $ur);

        $this->assertSame('', $obj->exposeGetOutSignatureInfo(11));
        $this->setObjectProperty($obj, 'signature', ['info' => ['Name' => 'John', 'Reason' => 'Approval']]);
        $info = $obj->exposeGetOutSignatureInfo(11);
        $this->assertStringContainsString('/Name ', $info);
        $this->assertStringContainsString('/Reason ', $info);

        $this->setObjectProperty($obj, 'signature', ['cert_type' => 2]);
        $this->assertStringContainsString('/TransformMethod /DocMDP', $obj->exposeGetOutSignatureDocMDP());
    }
}
