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

use PHPUnit\Framework\Attributes\DataProvider;

class OutputTest extends TestUtil
{
    public static function setUpBeforeClass(): void
    {
        self::setUpFontsPath();
    }

    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    protected function getInternalTestObject(): TestableOutput
    {
        return new TestableOutput();
    }

    /** @return array{pid:int,n:int,content:array<int,string>} */
    protected function addRawPageWithObjectNumber(\Com\Tecnick\Pdf\Tcpdf $obj, int $objectNumber): array
    {
        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        /** @var array{pid:int} $data */
        $data = $page->add([]);
        /** @var array<int, array<string, mixed>> $pages */
        $pages = $this->getObjectProperty($page, 'page');
        $pages[$data['pid']]['n'] = $objectNumber;
        $this->setObjectProperty($page, 'page', $pages);
        /** @var array{pid:int,n:int,content:array<int,string>} $pageData */
        $pageData = $page->getPage($data['pid']);
        return $pageData;
    }

    /** @param list<string> $fragments */
    private function assertContainsAllFragments(string $output, array $fragments): void
    {
        foreach ($fragments as $fragment) {
            $this->assertStringContainsString($fragment, $output);
        }
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

    public function testRenderPDFInCliPreservesBinaryPdfBytes(): void
    {
        $script = \sys_get_temp_dir() . '/tc-lib-pdf-render-' . \bin2hex(\random_bytes(6)) . '.php';
        $autoload = \var_export(__DIR__ . '/../vendor/autoload.php', true);
        $fonts = \var_export((string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'), true);

        $code = <<<'PHP'
<?php
require AUTOLOAD_PATH;
define('K_PATH_FONTS', FONTS_PATH);

$pdf = new \Com\Tecnick\Pdf\Tcpdf();
$font = $pdf->font->insert($pdf->pon, 'dejavusans', '', 12);
$pdf->addPage();
$pdf->page->addContent($font['out']);
$pdf->page->addContent(
    $pdf->getTextCell('The quick brown fox jumps over the lazy dog', 15, 15, 150, valign: 'T', halign: 'L')
);

$raw = $pdf->getOutPDFString();
fwrite(STDERR, md5($raw));
$pdf->renderPDF($raw);
PHP;

        $code = \str_replace(
            ['AUTOLOAD_PATH', 'FONTS_PATH'],
            [$autoload, $fonts],
            $code
        );

        \file_put_contents($script, $code);

        $cmd = [PHP_BINARY, $script];
        $desc = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = \proc_open($cmd, $desc, $pipes, __DIR__ . '/..');
        if (!\is_resource($proc)) {
            \unlink($script);
            $this->fail('Unable to start PHP subprocess for renderPDF regression test.');
        }

        \fclose($pipes[0]);
        $stdout = (string) \stream_get_contents($pipes[1]);
        \fclose($pipes[1]);
        $stderr = (string) \stream_get_contents($pipes[2]);
        \fclose($pipes[2]);
        $exitCode = \proc_close($proc);
        \unlink($script);

        $this->assertSame(0, $exitCode, $stderr);
        $this->assertSame($stderr, \md5($stdout));
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

    #[DataProvider('onOffProvider')]
    public function testGetOnOffMapsTruthyAndFalsyValues(mixed $input, string $expected): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame($expected, $obj->exposeGetOnOff($input));
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

        $this->assertContainsAllFragments($out, [
            '/Title (',
            "\x00F\x00i\x00r\x00s\x00t\x00 \x00S\x00e\x00c\x00t\x00i\x00o\x00n",
            '/Dest [3 0 R /XYZ 34.015748 745.512047 null]',
            '/F 2 /C [ 1.000000 0.000000 0.000000 ]',
            '/Dest /target',
            '/F 1 /C [0.0 0.0 0.0]',
            '/Type /Outlines',
        ]);
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

    public function testGetXObjectDictIncludesCustomObjectEntries(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'xobjects', [
            'XO1' => ['n' => 21],
            'XO2' => ['n' => 22],
        ]);

        $out = $obj->exposeGetXObjectDict();

        $this->assertStringContainsString('/XO1 21 0 R', $out);
        $this->assertStringContainsString('/XO2 22 0 R', $out);
    }

    public function testGetLayerDictIncludesLayerReferences(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdflayer', [
            ['layer' => 'L1', 'objid' => 31],
            ['layer' => 'L2', 'objid' => 32],
        ]);

        $out = $obj->exposeGetLayerDict();

        $this->assertStringContainsString('/L1 31 0 R', $out);
        $this->assertStringContainsString('/L2 32 0 R', $out);
    }

    public function testGetOutResourcesDictIncludesProcSetAndEmptyFontDict(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        // Initialize outfont property (normally done in getOutPDFBody)
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        /** @var int $pon */
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
        /** @var array<string, mixed> $annot */
        $annot = [];

        /** @var array<int, callable(TestableOutput): string> $todoSubtypeChecks */
        $todoSubtypeChecks = [
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeLine($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeSquare($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeCircle($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypePolygon($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypePolyline($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeHighlight($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeUnderline($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeSquiggly($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeStrikeout($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeStamp($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeCaret($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeInk($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypePopup($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeMovie($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeScreen($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypePrintermark($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeRedact($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeTrapnet($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtypeWatermark($annot),
            static fn (TestableOutput $outObj): string => $outObj->exposeGetOutAnnotationOptSubtype3D($annot),
        ];

        foreach ($todoSubtypeChecks as $check) {
            $this->assertSame('', $check($obj));
        }
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

        $out = $obj->exposeGetOutAnnotationOptSubtypeText(
            ['opt' => ['open' => true, 'statemodel' => 'Marked', 'state' => 'Accepted']]
        );

        $this->assertStringContainsString(' /Open true', $out);
        $this->assertStringContainsString(' /Name /Note', $out);
        $this->assertStringContainsString(' /StateModel /Marked', $out);
        $this->assertStringContainsString(' /State /Accepted', $out);
    }

    /**
     * @param array<string, mixed> $annot
     * @param list<string>         $expectedFragments
     */
    #[DataProvider('annotationSubtypeLinkSimpleProvider')]
    public function testGetOutAnnotationOptSubtypeLinkSimpleTargets(
        array $annot,
        int $apid,
        int $oid,
        array $expectedFragments,
    ): void {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetOutAnnotationOptSubtypeLink($annot, $apid, $oid);

        foreach ($expectedFragments as $fragment) {
            $this->assertIsString($fragment);
            $this->assertStringContainsString($fragment, $out);
        }
    }

    public function testGetOutAnnotationOptSubtypeLinkHandlesInternalAndEmbeddedTargets(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'embeddedfiles', [
            'sample.pdf' => ['a' => 4],
            'attach.bin' => ['a' => 2],
        ]);

        $namedDest = $obj->exposeGetOutAnnotationOptSubtypeLink(['txt' => '#dest-1', 'opt' => ['h' => 'N']], 2, 10);
        $embeddedPdf = $obj->exposeGetOutAnnotationOptSubtypeLink(['txt' => '%sample.pdf', 'opt' => []], 3, 11);
        $embeddedFile = $obj->exposeGetOutAnnotationOptSubtypeLink(['txt' => '*attach.bin', 'opt' => []], 1, 12);

        $this->assertStringContainsString(' /S /GoTo /D /dest-1', $namedDest);
        $this->assertStringContainsString(' /H /N', $namedDest);

        $this->assertStringContainsString(' /S /GoToE', $embeddedPdf);
        $this->assertStringContainsString(' /P 2 /A 4', $embeddedPdf);

        $this->assertStringContainsString(' /S /JavaScript /JS ', $embeddedFile);
        $this->assertStringContainsString(' /H /I', $embeddedFile);
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

    public function testGetOutAnnotationOptSubtypeWidgetIncludesAppearanceAndChoiceOptions(): void
    {
        $obj = $this->getInternalTestObject();
        $annot = [
            'txt' => 'field-choice',
            'opt' => [
                'h' => 'T',
                'ff' => 5,
                'v' => ['One', 2],
                'dv' => ['Two', 3],
                'rv' => ['Three', 4],
                'a' => '/S /ResetForm',
                'aa' => '/E << /S /JavaScript /JS (x) >>',
                'opt' => ['one', ['v2', 'Label Two'], 3],
                'ti' => 1,
                'i' => [0, 2, 'x'],
                'mk' => [
                    'r' => 90,
                    'bc' => [0.1, 0.2, 0.3],
                    'bg' => [0.9],
                    'ca' => '(CA)',
                    'rc' => '(RC)',
                    'ac' => '(AC)',
                    'if' => [
                        'sw' => 'B',
                        's' => 'P',
                        'a' => [0.2, 0.8],
                        'fb' => true,
                    ],
                    'tp' => 3,
                ],
            ],
        ];

        $out = $obj->exposeGetOutAnnotationOptSubtypeWidget($annot, 31);

        $this->assertStringContainsString(' /H /T', $out);
        $this->assertStringContainsString(' /MK <<', $out);
        $this->assertStringContainsString(' /R 90', $out);
        $this->assertStringContainsString(' /IF << /SW /B /S /P /A [0.200000 0.800000] /FB true>>', $out);
        $this->assertStringContainsString(' /TP 3', $out);
        $this->assertStringContainsString(' /Ff 5', $out);
        $this->assertStringContainsString(' /A << /S /ResetForm >>', $out);
        $this->assertStringContainsString(' /AA << /E << /S /JavaScript /JS (x) >> >>', $out);
        $this->assertStringContainsString(' /Opt [', $out);
        $this->assertStringContainsString(' /TI 1', $out);
        $this->assertStringContainsString(' /I [0 2 ]', $out);
    }

    public function testGetOutAnnotationOptSubtypeDispatcherRoutesKnownAndUnknownSubtypes(): void
    {
        $obj = $this->getInternalTestObject();

        $textOut = $obj->exposeGetOutAnnotationOptSubtype(['opt' => ['subtype' => 'Text']], 1, 10, 0);
        $unkOut = $obj->exposeGetOutAnnotationOptSubtype(['opt' => ['subtype' => 'Unknown']], 1, 10, 0);

        $this->assertStringContainsString(' /Name /Note', $textOut);
        $this->assertSame('', $unkOut);
    }

    public function testGetOutAnnotationOptSubtypeDispatcherCoversRemainingKnownSubtypes(): void
    {
        $obj = $this->getInternalTestObject();

        $subtypes = [
            '3D',
            'Caret',
            'Circle',
            'FileAttachment',
            'FreeText',
            'Highlight',
            'Ink',
            'Line',
            'Link',
            'Movie',
            'Polygon',
            'Polyline',
            'Popup',
            'PrinterMark',
            'Redact',
            'Screen',
            'Sound',
            'Square',
            'Squiggly',
            'Stamp',
            'StrikeOut',
            'TrapNet',
            'Underline',
            'Watermark',
            'Widget',
        ];

        foreach ($subtypes as $subtype) {
            $out = $obj->exposeGetOutAnnotationOptSubtype(['opt' => ['subtype' => $subtype]], 1, 10, 0);
            $this->assertGreaterThanOrEqual(0, \strlen($out));
        }
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
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(
            __DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json'
        );
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);
        $this->addRawPageWithObjectNumber($obj, 6);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4]);
        $this->setObjectProperty(
            $obj,
            'display',
            ['layout' => 'SinglePage', 'mode' => 'UseNone', 'zoom' => 'fullpage']
        );
        $this->setObjectProperty($obj, 'lang', ['a_meta_language' => 'en-US']);

        $out = $obj->exposeGetOutCatalog();

        $this->assertContainsAllFragments($out, [
            '/Type /Catalog',
            '/Pages 3 0 R',
            '/PageLayout /SinglePage',
            '/PageMode /UseNone',
            '/OpenAction [6 0 R /Fit]',
            '/Metadata 4 0 R',
            '/Lang ',
        ]);
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
        $this->assertContainsAllFragments($srgb, [
            '/GTS_PDFA1',
            '/DestOutputProfile 4 0 R',
        ]);

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

        $this->assertContainsAllFragments($apx, [
            '/Subtype /Form',
            '/BBox [0 0 12.000000 7.000000]',
        ]);
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

        $userRightsOut = $obj->exposeGetOutSignatureUserRights();
        $this->assertStringContainsString('/TransformMethod /UR3', $userRightsOut);

        $this->assertSame('', $obj->exposeGetOutSignatureInfo(11));
        $this->setObjectProperty($obj, 'signature', ['info' => ['Name' => 'John', 'Reason' => 'Approval']]);
        $info = $obj->exposeGetOutSignatureInfo(11);
        $this->assertContainsAllFragments($info, [
            '/Name ',
            '/Reason ',
        ]);

        $this->setObjectProperty($obj, 'signature', ['cert_type' => 2]);
        $this->assertStringContainsString('/TransformMethod /DocMDP', $obj->exposeGetOutSignatureDocMDP());
    }

    public function testConvertBinarySignatureToHexPadsToSigMaxLen(): void
    {
        $obj = $this->getInternalTestObject();

        $binary = "\x01\x02\x03";
        $hex = $obj->exposeConvertBinarySignatureToHex($binary);

        $this->assertSame(11742, \strlen($hex));
        $this->assertStringStartsWith('010203', $hex);
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $hex);
    }

    public function testPrepareDocumentForSignatureComputesByteRangeAndStripsSlot(): void
    {
        $obj = $this->getInternalTestObject();

        $byterangePlaceholder = '/ByteRange[0 ********** ********** **********]';
        $prefix   = 'XPFX';
        $mid      = '/Contents ';
        $sigSlot  = '<' . \str_repeat('0', 11742) . '>';
        $suffix   = 'XSFX';
        $pdfdoc   = $prefix . $byterangePlaceholder . $mid . $sigSlot . $suffix . "\n";

        $result = $obj->exposePrepareDocumentForSignature($pdfdoc);

    $this->assertSame(0, $result['byte_range'][0]);
        $this->assertSame($result['pdfdoc_length'], \strlen($result['pdfdoc']));
        $this->assertStringNotContainsString('**********', $result['pdfdoc']);
        $this->assertMatchesRegularExpression(
            '#/ByteRange\[0 \d+ \d+ \d+\] *#',
            $result['pdfdoc']
        );
        // Signature slot (SIGMAXLEN + 2 for < >) is stripped from the signing content
        $expectedLength = \strlen($prefix) + \strlen($byterangePlaceholder) + \strlen($mid) + \strlen($suffix);
        $this->assertSame($expectedLength, $result['pdfdoc_length']);
        // byte_range[3] must equal the length of the suffix
        $this->assertSame(\strlen($suffix), $result['byte_range'][3]);
    }

    public function testGetAnnotationFlagsCodeWithIntegerInput(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame(7, $obj->exposeGetAnnotationFlagsCode(7));
        $this->assertSame(0, $obj->exposeGetAnnotationFlagsCode(0));
    }

    public function testGetAnnotationFlagsCodeCoversAllIndividualFlags(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame(1, $obj->exposeGetAnnotationFlagsCode(['invisible']));
        $this->assertSame(4, $obj->exposeGetAnnotationFlagsCode(['print']));
        $this->assertSame(8, $obj->exposeGetAnnotationFlagsCode(['nozoom']));
        $this->assertSame(16, $obj->exposeGetAnnotationFlagsCode(['norotate']));
        $this->assertSame(32, $obj->exposeGetAnnotationFlagsCode(['noview']));
        $this->assertSame(256, $obj->exposeGetAnnotationFlagsCode(['togglenoview']));
        $this->assertSame(0, $obj->exposeGetAnnotationFlagsCode(['unknown-flag']));
    }

    public function testGetOutICCWithSRGBFlagGeneratesBlock(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'sRGB', true);

        $out = $obj->exposeGetOutICC();

        $this->assertStringContainsString('/N 3', $out);
        $this->assertStringContainsString('/Filter /FlateDecode', $out);
        $this->assertStringContainsString('endobj', $out);
    }

    public function testGetAnnotationBorderWithBorderArrayFallback(): void
    {
        $obj = $this->getInternalTestObject();

        $withBorder = $obj->exposeGetAnnotationBorder(['opt' => ['border' => [1, 2, 3, [4, 5]]]]);
        $this->assertStringContainsString(' /Border [1 2 3 [ 4 5 ]]', $withBorder);

        $defaultBorder = $obj->exposeGetAnnotationBorder(['opt' => []]);
        $this->assertStringContainsString(' /Border [0 0 0]', $defaultBorder);
    }

    public function testGetAnnotationBorderBeUsesDefaultStyleWhenInvalidS(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetAnnotationBorder(['opt' => ['be' => ['s' => 'X']]]);
        $this->assertStringContainsString(' /BE << /S /S>>', $out);
    }

    public function testGetAnnotationBorderBeSkipsInvalidIntensity(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetAnnotationBorder(['opt' => ['be' => ['s' => 'C', 'i' => 5.0]]]);
        $this->assertStringContainsString('/S /C', $out);
        $this->assertStringNotContainsString('/I', $out);
    }

    public function testGetAnnotationAppearanceStreamWithStringAp(): void
    {
        $obj = $this->getInternalTestObject();

        [$aas, $apx] = $obj->exposeGetAnnotationAppearanceStream(
            ['opt' => ['ap' => '/N 1 0 R']],
            10.0,
            5.0
        );

        $this->assertMatchesRegularExpression('#/AP <<\s*/N 1 0 R\s*>>#', $aas);
        $this->assertSame('', $apx);
    }

    public function testGetAnnotationAppearanceStreamWithArrayApStringDef(): void
    {
        $obj = $this->getInternalTestObject();

        [$aas, $apx] = $obj->exposeGetAnnotationAppearanceStream(
            ['opt' => ['ap' => ['n' => 'q Q']]],
            8.0,
            4.0
        );

        $this->assertStringContainsString(' /N ', $aas);
        $this->assertStringContainsString('/Subtype /Form', $apx);
    }

    public function testGetAnnotationAppearanceStreamWithArrayApArrayDef(): void
    {
        $obj = $this->getInternalTestObject();

        [$aas, $apx] = $obj->exposeGetAnnotationAppearanceStream(
            ['opt' => ['ap' => ['n' => ['On' => 'q Q', 'Off' => '']]]],
            8.0,
            4.0
        );

        $this->assertStringContainsString(' /N <<', $aas);
        $this->assertStringContainsString(' /On ', $aas);
        $this->assertStringContainsString('/Subtype /Form', $apx);
    }

    public function testGetAnnotationRadioButtonsWithKidsAndReadonly(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'radiobuttons', [
            'gender' => [
                'n' => 5,
                '#readonly#' => false,
                'kids' => [
                    ['n' => 6, 'def' => 'Male'],
                    ['n' => 7, 'def' => 'Off'],
                ],
            ],
        ]);

        $out = $obj->exposeGetAnnotationRadioButtons(['txt' => 'gender', 'opt' => []]);

        $this->assertStringContainsString('/FT /Btn', $out);
        $this->assertStringContainsString('/Kids [', $out);
        $this->assertStringContainsString(' 6 0 R', $out);
        $this->assertStringContainsString('/V /Male', $out);
        $this->assertStringNotContainsString('/F 68', $out);
    }

    public function testGetAnnotationRadioButtonsReadonlyFlag(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'radiobuttons', [
            'choice' => [
                'n' => 10,
                '#readonly#' => true,
                'kids' => [
                    ['n' => 11, 'def' => 'Off'],
                ],
            ],
        ]);

        $out = $obj->exposeGetAnnotationRadioButtons(['txt' => 'choice', 'opt' => []]);

        $this->assertStringContainsString('/F 68 /Ff 49153', $out);
    }

    public function testGetAnnotationRadioButtonsWithTuField(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'radiobuttons', [
            'rb' => [
                'n' => 20,
                '#readonly#' => false,
                'kids' => [['n' => 21, 'def' => 'Off']],
            ],
        ]);

        $out = $obj->exposeGetAnnotationRadioButtons([
            'txt' => 'rb',
            'opt' => ['tu' => 'Tooltip text'],
        ]);

        $this->assertStringContainsString('/TU ', $out);
    }

    /**
     * @param array<string, mixed> $opt
     * @param list<string>         $expectedFragments
     */
    #[DataProvider('annotationSubtypeTextProvider')]
    public function testGetOutAnnotationOptSubtypeTextVariants(array $opt, array $expectedFragments): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetOutAnnotationOptSubtypeText(['opt' => $opt]);

        foreach ($expectedFragments as $fragment) {
            $this->assertStringContainsString((string) $fragment, $out);
        }
    }

    /** @return array<string, array{0: mixed, 1: string}> */
    public static function onOffProvider(): array
    {
        return [
            'true_is_on' => [true, 'ON'],
            'zero_is_off' => [0, 'OFF'],
        ];
    }

    /** @return array<string, array{0: array<string, mixed>, 1: array<int, string>}> */
    public static function annotationSubtypeTextProvider(): array
    {
        return [
            'known_icon_name' => [
                ['name' => 'Help'],
                [' /Name /Help'],
            ],
            'unknown_icon_defaults_to_note' => [
                ['name' => 'Unknown'],
                [' /Name /Note'],
            ],
            'invalid_state_model_falls_back' => [
                ['statemodel' => 'Invalid', 'state' => 'SomeState'],
                [' /StateModel /Marked', ' /State /Unmarked'],
            ],
            'review_rejected_state' => [
                ['statemodel' => 'Review', 'state' => 'Rejected'],
                [' /StateModel /Review', ' /State /Rejected'],
            ],
            'review_invalid_state_defaults_none' => [
                ['statemodel' => 'Review', 'state' => 'InvalidState'],
                [' /State /None'],
            ],
            'open_false' => [
                ['open' => false],
                [' /Open false'],
            ],
        ];
    }

    /** @return array<string, array{0: array<string, mixed>, 1: int, 2: int, 3: list<string>}> */
    public static function annotationSubtypeLinkSimpleProvider(): array
    {
        return [
            'external_uri_default_highlight' => [
                ['txt' => 'https://example.com', 'opt' => []],
                1,
                10,
                [' /S /URI', ' /URI ', ' /H /I'],
            ],
            'relative_pdf_gotor' => [
                ['txt' => 'docs/guide.pdf#named=Section2', 'opt' => []],
                1,
                22,
                [' /S /GoToR', ' /D (Section2)', ' /F ', ' /NewWindow true', ' /H /I'],
            ],
        ];
    }

    public function testGetOutAnnotationOptSubtypeLinkWithAtInternalLink(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 5);

        $this->setObjectProperty($obj, 'links', ['@1' => ['p' => $page['pid'], 'y' => 15.0]]);

        $out = $obj->exposeGetOutAnnotationOptSubtypeLink(['txt' => '@1', 'opt' => []], 1, 20);

        $this->assertStringContainsString('/Dest [', $out);
        $this->assertStringContainsString('/XYZ 0 ', $out);
    }

    public function testGetOutAnnotationOptSubtypeFreetextDsAndCl(): void
    {
        $obj = $this->getInternalTestObject();
        $annot = [
            'n' => 10,
            'opt' => [
                'da' => '/F1 12 Tf',
                'rc' => '<p>Rich</p>',
                'ds' => 'font: Arial 12pt',
                'cl' => [10.0, 20.0, 30.0],
            ],
        ];

        $out = $obj->exposeGetOutAnnotationOptSubtypeFreetext($annot, 10);

        $this->assertStringContainsString(' /RC ', $out);
        $this->assertStringContainsString(' /DS ', $out);
        $this->assertStringContainsString(' /CL [', $out);
    }

    public function testGetOutAnnotationOptSubtypeWidgetImageLookupsWithMkIcons(): void
    {
        $obj = $this->getInternalTestObject();

        foreach (['i', 'ri', 'ix'] as $ikey) {
            try {
                $obj->exposeGetOutAnnotationOptSubtypeWidget([
                    'txt' => 'mk-icons',
                    'opt' => [
                        'mk' => [
                            $ikey => 'non-existent-icon',
                        ],
                    ],
                ], 77);
                $this->fail('Expected missing image key exception for mk.' . $ikey);
            } catch (\Com\Tecnick\Pdf\Image\Exception $e) {
                $this->assertStringContainsString('Unknownn key', $e->getMessage());
            }
        }
    }


    public function testGetOutAnnotationOptSubtypeWidgetWithTuTmAndScalarValues(): void
    {
        $obj = $this->getInternalTestObject();
        $annot = [
            'txt' => 'field',
            'opt' => [
                'tu' => 'Tooltip',
                'tm' => 'Mapping',
                'v' => 'scalar-value',
                'dv' => 'default',
                'rv' => 'rich',
            ],
        ];

        $out = $obj->exposeGetOutAnnotationOptSubtypeWidget($annot, 15);

        $this->assertStringContainsString(' /TU ', $out);
        $this->assertStringContainsString(' /TM ', $out);
        $this->assertStringContainsString(' /V ', $out);
        $this->assertStringContainsString(' /DV ', $out);
        $this->assertStringContainsString(' /RV ', $out);
    }

    public function testGetOutAnnotationOptSubtypeWidgetWithParent(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'radiobuttons', [
            'rb-field' => ['n' => 99],
        ]);

        $out = $obj->exposeGetOutAnnotationOptSubtypeWidget([
            'txt' => 'rb-field',
            'opt' => [],
        ], 50);

        $this->assertStringContainsString(' /Parent 99 0 R', $out);
    }

    public function testGetOutAnnotationOptSubtypeWidgetOptChoiceStrings(): void
    {
        $obj = $this->getInternalTestObject();
        $annot = [
            'txt' => 'combo',
            'opt' => [
                'opt' => [
                    'stringopt',
                    ['key1', 'Label One'],
                    ['bad-count'],
                    42,
                ],
            ],
        ];

        $out = $obj->exposeGetOutAnnotationOptSubtypeWidget($annot, 31);

        $this->assertStringContainsString(' /Opt [', $out);
    }

    public function testGetOutCatalogWithEmbeddedFilesAndJavascriptTree(): void
    {
        $obj = $this->getInternalTestObject();
            $this->addRawPageWithObjectNumber($obj, 3);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4]);
        $this->setObjectProperty($obj, 'jstree', '<< /Names [(JS) 1 0 R] >>');
        $this->setObjectProperty($obj, 'embeddedfiles', [
            'doc.pdf' => [
                'a' => 0,
                'f' => 5,
                'n' => 6,
                'file' => '',
                'content' => 'data',
                'mimeType' => 'application/pdf',
                'afRelationship' => 'Source',
                'description' => 'test',
                'creationDate' => 0,
                'modDate' => 0,
            ],
        ]);
        $this->setObjectProperty($obj, 'objid', [
            'catalog' => 0,
            'dests' => 7,
            'form' => [],
            'info' => 0,
            'pages' => 3,
            'resdic' => 0,
            'signature' => 0,
            'srgbicc' => 0,
            'xmp' => 4,
        ]);

        $out = $obj->exposeGetOutCatalog();

        $this->assertContainsAllFragments($out, [
            '/JavaScript',
            '/AF [',
            '/EmbeddedFiles',
            '/Dests 7 0 R',
        ]);
    }

    #[DataProvider('catalogZoomModeProvider')]
    public function testGetOutCatalogZoomModes(string|int $zoom, string $expectedFragment): void
    {
        $obj = $this->getInternalTestObject();
        $this->addRawPageWithObjectNumber($obj, 6);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4]);
        $this->setObjectProperty($obj, 'display', ['layout' => '', 'mode' => 'UseNone', 'zoom' => $zoom]);

        $out = $obj->exposeGetOutCatalog();

        $this->assertStringContainsString($expectedFragment, $out);
    }

    /** @return array<string, array{0: string|int, 1: string}> */
    public static function catalogZoomModeProvider(): array
    {
        return [
            'fullwidth' => ['fullwidth', '/FitH null]'],
            'real' => ['real', '/XYZ null null 1]'],
            'numeric' => [150, '/XYZ null null'],
        ];
    }

    public function testGetOutCatalogWithOutlinesAutoSetsDisplayMode(): void
    {
        $obj = $this->getInternalTestObject();
            $page = $this->addRawPageWithObjectNumber($obj, 6);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4]);
        $obj->setBookmark('Chapter 1', '', 0, $page['pid']);
        $obj->exposeGetOutBookmarks();
        $this->setObjectProperty($obj, 'display', ['layout' => '', 'mode' => '', 'zoom' => 'default']);

        $out = $obj->exposeGetOutCatalog();

        $this->assertContainsAllFragments($out, [
            '/Outlines ',
            '/PageMode /UseOutlines',
        ]);
    }

    public function testGetOutCatalogWithFormFields(): void
    {
        $obj = $this->getInternalTestObject();
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(
            __DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json'
        );
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);
            $this->addRawPageWithObjectNumber($obj, 6);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4, 'form' => [5, 6]]);

        $out = $obj->exposeGetOutCatalog();

        $this->assertContainsAllFragments($out, [
            '/AcroForm <<',
            '/Fields [',
            ' 5 0 R',
            '/NeedAppearances false',
        ]);
    }

    public function testGetOutCatalogWithSignatureAcroformVariants(): void
    {
        $obj = $this->getInternalTestObject();
        $pageInfo = $this->initFontAndPage($obj);
        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($obj, 'page');
        /** @var array<int, array<string, mixed>> $pgdata */
        $pgdata = $this->getObjectProperty($pageObj, 'page');
        $pgdata[$pageInfo['pid']]['n'] = 6;
        $pgdata[$pageInfo['pid']]['num'] = 1;
        $this->setObjectProperty($pageObj, 'page', $pgdata);
        $this->addRawPageWithObjectNumber($obj, 6);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4, 'signature' => 40, 'form' => [51]]);

        $this->setObjectProperty($obj, 'sign', true);
        $this->setObjectProperty($obj, 'annotation_fonts', ['helvetica' => 1]);
        $this->setObjectProperty($obj, 'signature', [
            'cert_type' => 0,
            'approval' => 'P',
            'appearance' => [
                'empty' => [
                    ['objid' => 41],
                ],
            ],
        ]);

        $ur3Out = $obj->exposeGetOutCatalog();
        $this->assertContainsAllFragments($ur3Out, [
            '/Fields [40 0 R 41 0 R 51 0 R]',
            '/SigFlags 1',
            '/Perms << /UR3 41 0 R >>',
            '/DR << /Font <<',
        ]);

        $this->setObjectProperty($obj, 'signature', [
            'cert_type' => 2,
            'approval' => 'P',
            'appearance' => [
                'empty' => [
                    ['objid' => 41],
                ],
            ],
        ]);

        $docmdpOut = $obj->exposeGetOutCatalog();
        $this->assertContainsAllFragments($docmdpOut, [
            '/SigFlags 3',
            '/Perms << /DocMDP 41 0 R >>',
        ]);
    }

    public function testGetPDFLayersWithViewFalseAndLockTrue(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'objid', [
            'catalog' => 7,
            'dests' => 0,
            'form' => [],
            'info' => 0,
            'pages' => 0,
            'resdic' => 0,
            'signature' => 0,
            'srgbicc' => 0,
            'xmp' => 0,
        ]);
        $this->setObjectProperty($obj, 'pdflayer', [[
            'layer' => 'lyr1',
            'name' => 'Invisible Layer',
            'view' => false,
            'lock' => true,
            'intent' => '',
            'print' => true,
            'objid' => 3,
        ]]);

        $out = $obj->exposeGetPDFLayers();

        $this->assertContainsAllFragments($out, [
            '/OFF [ 3 0 R]',
            '/Locked [ 3 0 R]',
        ]);
    }

    public function testGetOutOCGWithPrintAndIntent(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdflayer', [[
            'layer' => 'lyr1',
            'name' => 'Print Layer',
            'view' => true,
            'lock' => false,
            'intent' => '/View',
            'print' => true,
            'objid' => 0,
        ]]);

        $out = $obj->exposeGetOutOCG();

        $this->assertContainsAllFragments($out, [
            '/Intent [/View]',
            '/Print << /PrintState /ON >>',
        ]);
    }

    public function testGetOutXObjectsWithNonEmptyOutdata(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $outfont = new \Com\Tecnick\Pdf\Font\Output($font->getFonts(), $pon, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        $this->setObjectProperty($obj, 'xobjects', [
            'XT1' => [
                'id' => 'XT1',
                'n' => 1,
                'x' => 0.0,
                'y' => 0.0,
                'w' => 100.0,
                'h' => 50.0,
                'pheight' => 0.0,
                'gheight' => 0.0,
                'outdata' => 'q Q',
                'spot_colors' => [],
                'extgstate' => [],
                'gradient' => [],
                'font' => [],
                'image' => [],
                'xobject' => [],
                'annotations' => [],
                'transparency' => null,
            ],
        ]);

        $out = $obj->exposeGetOutXObjects();

        $this->assertContainsAllFragments($out, [
            '/Type /XObject',
            '/Subtype /Form',
            '/BBox [',
        ]);
    }

    public function testGetOutXObjectsWithTransparencyGroup(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $outfont = new \Com\Tecnick\Pdf\Font\Output($font->getFonts(), $pon, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        $this->setObjectProperty($obj, 'xobjects', [
            'XT2' => [
                'id' => 'XT2',
                'n' => 2,
                'x' => 0.0,
                'y' => 0.0,
                'w' => 50.0,
                'h' => 25.0,
                'pheight' => 0.0,
                'gheight' => 0.0,
                'outdata' => 'q Q',
                'spot_colors' => [],
                'extgstate' => [],
                'gradient' => [],
                'font' => [],
                'image' => [],
                'xobject' => [],
                'annotations' => [],
                'transparency' => ['CS' => 'DeviceRGB', 'I' => true, 'K' => false],
            ],
        ]);

        $out = $obj->exposeGetOutXObjects();

        $this->assertContainsAllFragments($out, [
            '/Group << /Type /Group /S /Transparency',
            '/CS /DeviceRGB',
            '/I /true',
            '/K /false',
        ]);
    }

    public function testGetOutEmbeddedFilesWithContent(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->addContentAsEmbeddedFile('hello.txt', 'Hello World!', 'text/plain', 'Source', 'Test file');

        $out = $obj->exposeGetOutEmbeddedFiles();

        $this->assertContainsAllFragments($out, [
            '/Type /Filespec',
            '/Type /EmbeddedFile',
            '/AFRelationship /Source',
        ]);
    }

    public function testGetOutEmbeddedFilesSkippedInPdfa1And2(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setPdfaMode(1);

        $out = $obj->exposeGetOutEmbeddedFiles();

        $this->assertSame('', $out);
    }

    public function testGetOutAnnotationsWithTextAnnotation(): void
    {
        $obj = $this->getInternalTestObject();
            $pageInfo = $this->initFontAndPage($obj);
            /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
            $pageObj = $this->getObjectProperty($obj, 'page');
            /** @var array<int, array<string, mixed>> $pgdata */
            $pgdata = $this->getObjectProperty($pageObj, 'page');
            $pgdata[$pageInfo['pid']]['n'] = 5;
                $pgdata[$pageInfo['pid']]['num'] = 1;
            $this->setObjectProperty($pageObj, 'page', $pgdata);

        $aoid = $obj->setAnnotation(10.0, 20.0, 50.0, 10.0, 'Test note', ['subtype' => 'text']);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $page->addAnnotRef($aoid);

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $outfont = new \Com\Tecnick\Pdf\Font\Output($font->getFonts(), $pon, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        $out = $obj->exposeGetOutAnnotations();

        $this->assertContainsAllFragments($out, [
            '/Type /Annot',
            '/Subtype /text',
            '/Rect [',
            '/Contents ',
        ]);
    }

    public function testGetOutAnnotationsWithLinkAnnotation(): void
    {
        $obj = $this->getInternalTestObject();
            $pageInfo = $this->initFontAndPage($obj);
            /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
            $pageObj = $this->getObjectProperty($obj, 'page');
            /** @var array<int, array<string, mixed>> $pgdata */
            $pgdata = $this->getObjectProperty($pageObj, 'page');
            $pgdata[$pageInfo['pid']]['n'] = 5;
                $pgdata[$pageInfo['pid']]['num'] = 1;
            $this->setObjectProperty($pageObj, 'page', $pgdata);

        $aoid = $obj->setAnnotation(10.0, 20.0, 50.0, 10.0, 'https://example.com', ['subtype' => 'Link']);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $page->addAnnotRef($aoid);

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $outfont = new \Com\Tecnick\Pdf\Font\Output($font->getFonts(), $pon, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        $out = $obj->exposeGetOutAnnotations();

        $this->assertStringContainsString('/Subtype /Link', $out);
        $this->assertStringNotContainsString('/Contents ', $out);
    }

    public function testGetOutAnnotationsWithFormFieldAnnotation(): void
    {
        $obj = $this->getInternalTestObject();
            $pageInfo = $this->initFontAndPage($obj);
            /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
            $pageObj = $this->getObjectProperty($obj, 'page');
            /** @var array<int, array<string, mixed>> $pgdata */
            $pgdata = $this->getObjectProperty($pageObj, 'page');
            $pgdata[$pageInfo['pid']]['n'] = 5;
                $pgdata[$pageInfo['pid']]['num'] = 1;
            $this->setObjectProperty($pageObj, 'page', $pgdata);

        $aoid = $obj->setAnnotation(
            5.0,
            10.0,
            80.0,
            12.0,
            'myfield',
            ['subtype' => 'Widget', 'ft' => 'Tx']
        );

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $page->addAnnotRef($aoid);

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $outfont = new \Com\Tecnick\Pdf\Font\Output($font->getFonts(), $pon, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        $out = $obj->exposeGetOutAnnotations();

        $this->assertStringContainsString('/FT /Tx', $out);
    }

    public function testGetOutAnnotationsWithColorOption(): void
    {
        $obj = $this->getInternalTestObject();
            $pageInfo = $this->initFontAndPage($obj);
            /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
            $pageObj = $this->getObjectProperty($obj, 'page');
            /** @var array<int, array<string, mixed>> $pgdata */
            $pgdata = $this->getObjectProperty($pageObj, 'page');
            $pgdata[$pageInfo['pid']]['n'] = 5;
                $pgdata[$pageInfo['pid']]['num'] = 1;
            $this->setObjectProperty($pageObj, 'page', $pgdata);

        $aoid = $obj->setAnnotation(
            5.0,
            10.0,
            80.0,
            12.0,
            'Colored note',
            ['subtype' => 'text', 'c' => '#FF0000']
        );

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $page->addAnnotRef($aoid);

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $outfont = new \Com\Tecnick\Pdf\Font\Output($font->getFonts(), $pon, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        $out = $obj->exposeGetOutAnnotations();

        $this->assertStringContainsString('/C [', $out);
    }

    public function testGetOutBookmarksWithAtLinkType(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 5);

        $this->setObjectProperty($obj, 'links', ['@1' => ['p' => $page['pid'], 'y' => 10.0]]);
        $obj->setBookmark('Linked Section', '@1', 0, $page['pid']);

        $out = $obj->exposeGetOutBookmarks();

        $this->assertStringContainsString('/Dest [', $out);
        $this->assertStringContainsString('/XYZ 0 ', $out);
    }

    public function testGetOutBookmarksWithStarEmbeddedFileLink(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 5);

        $this->setObjectProperty($obj, 'embeddedfiles', [
            'report.bin' => [
                'a' => 2,
                'f' => 3,
                'n' => 4,
                'file' => '',
                'content' => 'data',
                'mimeType' => 'application/octet-stream',
                'afRelationship' => 'Source',
                'description' => '',
                'creationDate' => 0,
                'modDate' => 0,
            ],
        ]);
        $obj->setBookmark('Embedded File', '*report.bin', 0, $page['pid']);

        $out = $obj->exposeGetOutBookmarks();

        $this->assertStringContainsString('/S /JavaScript', $out);
    }

    public function testGetOutBookmarksWithPercentEmbeddedPdfLink(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 7);

        $this->setObjectProperty($obj, 'embeddedfiles', [
            'manual.pdf' => [
                'a' => 5,
                'f' => 3,
                'n' => 4,
                'file' => '',
                'content' => 'data',
                'mimeType' => 'application/pdf',
                'afRelationship' => 'Source',
                'description' => 'Embedded PDF',
                'creationDate' => 0,
                'modDate' => 0,
            ],
        ]);
        $obj->setBookmark('Embedded PDF', '%manual.pdf', 0, $page['pid']);

        $out = $obj->exposeGetOutBookmarks();

        $this->assertStringContainsString('/S /GoToE', $out);
        $this->assertStringContainsString('/A 5', $out);
    }

    public function testGetOutBookmarksWithExternalUriLink(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 6);

        $obj->setBookmark('External Site', 'https://example.com/docs?a=1&b=2', 0, $page['pid']);

        $out = $obj->exposeGetOutBookmarks();

        $this->assertStringContainsString('/S /URI', $out);
        $this->assertStringContainsString('/URI ', $out);
    }

    public function testGetOutBookmarksWithNoUrlUsesPageDest(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 8);

        $obj->setBookmark('Page Section', '', 0, $page['pid'], 5.0, 10.0);

        $out = $obj->exposeGetOutBookmarks();

        $this->assertStringContainsString('/Dest [', $out);
        $this->assertStringContainsString('/XYZ ', $out);
    }

    public function testGetOutBookmarksIncludesFirstAndLastForParentsWithChildren(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 10);

        $obj->setBookmark('Parent', '', 0, $page['pid']);
        $obj->setBookmark('Child 1', '', 1, $page['pid']);
        $obj->setBookmark('Child 2', '', 1, $page['pid']);

        $out = $obj->exposeGetOutBookmarks();

        $this->assertStringContainsString('/First ', $out);
        $this->assertStringContainsString('/Last ', $out);
    }

    public function testGetOutJavascriptWithAddFieldTriggersWrapper(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'javascript', "var f=1;\nthis.addField('x','text',0,[0,0,100,50]);");

        $out = $obj->exposeGetOutJavascript();

        /** @var string $jsmod */
        $jsmod = $this->getObjectProperty($obj, 'javascript');
        $this->assertStringContainsString('ftcpdfdocsaved', $jsmod);
        $this->assertStringContainsString('/S /JavaScript', $out);
    }

    public function testGetOutSignatureUserRightsWithAllFields(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'userrights', [
            'enabled' => true,
            'document' => '/FullSave',
            'form' => '/Add /FillIn',
            'signature' => '/Modify',
            'annots' => '/Create /Delete /Modify /Copy /Import /Export',
            'ef' => '/Create /Delete /Modify /Import',
            'formex' => '',
        ]);

        $out = $obj->exposeGetOutSignatureUserRights();

        $this->assertContainsAllFragments($out, [
            '/TransformMethod /UR3',
            '/Document[/FullSave]',
            '/Form[/Add /FillIn]',
            '/Signature[/Modify]',
            '/Annots[/Create /Delete /Modify /Copy /Import /Export]',
            '/EF[/Create /Delete /Modify /Import]',
        ]);
    }

    public function testGetOutSignatureFieldsWithAppearanceEntries(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 9);

        $this->setObjectProperty($obj, 'signature', [
            'appearance' => [
                'empty' => [[
                    'page' => $page['pid'],
                    'name' => 'ApprovalSig',
                    'objid' => 91,
                    'rect' => '10 20 30 40',
                ]],
            ],
        ]);

        $out = $obj->exposeGetOutSignatureFields();

        $this->assertContainsAllFragments($out, [
            '/Subtype /Widget',
            '/FT /Sig',
            '/T ',
        ]);
    }

    public function testGetOutSignatureFieldsReturnsEmptyWithEmptySignatureArray(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'signature', []);

        $this->assertSame('', $obj->exposeGetOutSignatureFields());
    }

    public function testGetOutSignatureWithDocMdpAndApprovalModes(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 11);

        $this->setObjectProperty($obj, 'sign', true);
        $this->setObjectProperty($obj, 'objid', ['signature' => 70]);
        $this->setObjectProperty($obj, 'signature', [
            'cert_type' => 2,
            'approval' => 'P',
            'appearance' => [
                'page' => $page['pid'],
                'rect' => '5 15 45 25',
                'name' => 'SigMain',
            ],
            'info' => ['Name' => 'Tester'],
        ]);

        $outWithRef = $obj->exposeGetOutSignature();
        $this->assertContainsAllFragments($outWithRef, [
            '/Type /Sig',
            '/TransformMethod /DocMDP',
            '/Reference [ << /Type /SigRef',
        ]);

        $this->setObjectProperty($obj, 'signature', [
            'cert_type' => 2,
            'approval' => 'A',
            'appearance' => [
                'page' => $page['pid'],
                'rect' => '5 15 45 25',
                'name' => 'SigMain',
            ],
        ]);

        $outApproval = $obj->exposeGetOutSignature();
        $this->assertStringNotContainsString('/Reference [ << /Type /SigRef', $outApproval);
    }

    public function testGetOutSignatureWithUserRightsReferenceWhenCertTypeZero(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 13);

        $this->setObjectProperty($obj, 'sign', true);
        $this->setObjectProperty($obj, 'objid', ['signature' => 80]);
        $this->setObjectProperty($obj, 'signature', [
            'cert_type' => -1,
            'approval' => 'P',
            'appearance' => [
                'page' => $page['pid'],
                'rect' => '6 16 46 26',
                'name' => 'SigUR3',
            ],
        ]);

        $out = $obj->exposeGetOutSignature();

        $this->assertStringContainsString('/TransformMethod /UR3', $out);
    }

    public function testGetOutSignatureDocMDPReturnsEmptyWhenCertTypeMissing(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'signature', []);

        $this->assertSame('', $obj->exposeGetOutSignatureDocMDP());
    }

    public function testGetOutSignatureUserRightsWithFormExField(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'userrights', [
            'enabled' => true,
            'document' => '',
            'form' => '',
            'signature' => '',
            'annots' => '',
            'ef' => '',
            'formex' => '/BarcodePlaintext',
        ]);

        $out = $obj->exposeGetOutSignatureUserRights();

        $this->assertStringContainsString('/FormEX[/BarcodePlaintext]', $out);
    }

    public function testGetOutSignatureInfoWithAllOptionalFields(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'signature', [
            'info' => [
                'Name' => 'N',
                'Location' => 'L',
                'Reason' => 'R',
                'ContactInfo' => 'C',
            ],
        ]);

        $out = $obj->exposeGetOutSignatureInfo(33);

        $this->assertContainsAllFragments($out, [
            '/Name ',
            '/Location ',
            '/Reason ',
            '/ContactInfo ',
        ]);
    }

    public function testSavePDFThrowsWhenDirectoryDoesNotExist(): void
    {
        $obj = $this->getTestObject();
        $obj->setPDFFilename('output.pdf');

            $this->expectException(\Throwable::class);
        $obj->savePDF('/path/that/does/not/exist/at/all', 'data');
    }

    public function testGetOutAnnotationMarkupsWithRcAndCa(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetOutAnnotationMarkups([
            'opt' => [
                'subtype' => 'ink',
                'rc' => 'Rich content',
                'ca' => 0.75,
            ],
        ], 5);

        $this->assertContainsAllFragments($out, [
            ' /RC ',
            ' /CA 0.750000',
            ' /CreationDate ',
        ]);
    }

    public function testGetOutAnnotationMarkupsIgnoresNonMarkupSubtype(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetOutAnnotationMarkups([
            'opt' => ['subtype' => 'Link'],
        ], 5);

        $this->assertSame('', $out);
    }

    public function testOutputAdditionalAnnotationBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $pageInfo = $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($obj, 'page');
        $pageObj->addAnnotRef(999, $pageInfo['pid']);
        $this->assertSame('', $obj->exposeGetOutAnnotations());

        $missingAttachment = $obj->exposeGetOutAnnotationOptSubtypeFileattachment([
            'opt' => ['fs' => 'missing.bin'],
        ], 1);
        $this->assertSame('', $missingAttachment);

        $this->setObjectProperty($obj, 'embeddedfiles', ['doc.txt' => ['f' => 7]]);
        $defaultAttachIcon = $obj->exposeGetOutAnnotationOptSubtypeFileattachment([
            'opt' => ['fs' => 'doc.txt', 'name' => 'UnknownIcon'],
        ], 2);
        $this->assertContainsAllFragments($defaultAttachIcon, [
            ' /FS 7 0 R',
            ' /Name /PushPin',
        ]);

        $missingSound = $obj->exposeGetOutAnnotationOptSubtypeSound([
            'opt' => ['fs' => 'missing.wav'],
        ]);
        $this->assertSame('', $missingSound);

        $this->setObjectProperty($obj, 'embeddedfiles', ['snd.wav' => ['f' => 9]]);
        $defaultSoundIcon = $obj->exposeGetOutAnnotationOptSubtypeSound([
            'opt' => ['fs' => 'snd.wav', 'name' => 'UnknownMic'],
        ]);
        $this->assertContainsAllFragments($defaultSoundIcon, [
            ' /Sound 9 0 R',
            ' /Name /Speaker',
        ]);

        $widget = $obj->exposeGetOutAnnotationOptSubtypeWidget([
            'txt' => 'field-mixed',
            'opt' => [
                'h' => 'I',
                'v' => ['A', new \stdClass(), 1],
                'dv' => ['B', new \stdClass(), 2],
                'rv' => ['C', new \stdClass(), 3],
            ],
        ], 31);
        $this->assertContainsAllFragments($widget, [
            ' /V A 1.000000',
            ' /DV B 2.000000',
            ' /RV C 3.000000',
        ]);

        [$appearanceState, $appearanceXObject] = $obj->exposeGetAnnotationAppearanceStream([
            'opt' => [
                'ap' => [
                    'n' => ['On' => 123, 'Off' => 'q 1 0 0 1 0 0 cm Q'],
                ],
            ],
        ], 10, 5);
        $this->assertContainsAllFragments($appearanceState, [
            ' /AP <<',
            '/Off',
        ]);
        $this->assertContainsAllFragments($appearanceXObject, [
            '/Subtype /Form',
        ]);
    }

    public function testOutputAdditionalEmbeddedFileBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $tmpFile = \tempnam(\sys_get_temp_dir(), 'tc-out-ef-');
        $this->assertNotFalse($tmpFile);
        \file_put_contents((string) $tmpFile, 'embedded-content');

        try {
            $this->setPdfaModeOnObject($obj, 3);
            $this->setObjectProperty($obj, 'embeddedfiles', [
                'plain.txt' => [
                    'a' => 0,
                    'f' => 11,
                    'n' => 12,
                    'file' => (string) $tmpFile,
                    'content' => '',
                    'mimeType' => 'text/plain',
                    'afRelationship' => 'Source',
                    'description' => 'desc',
                    'creationDate' => \time(),
                    'modDate' => \time(),
                ],
            ]);
            $pdfa3Out = $obj->exposeGetOutEmbeddedFiles();
            $this->assertStringContainsString('/Subtype /text#2Fplain', $pdfa3Out);

            $this->setPdfaModeOnObject($obj, 0);
            $this->setObjectProperty($obj, 'embeddedfiles', [
                'plain2.txt' => [
                    'a' => 0,
                    'f' => 21,
                    'n' => 22,
                    'file' => (string) $tmpFile,
                    'content' => '',
                    'mimeType' => 'text/plain',
                    'afRelationship' => 'Source',
                    'description' => 'desc2',
                    'creationDate' => \time(),
                    'modDate' => \time(),
                ],
            ]);
            $compressedOut = $obj->exposeGetOutEmbeddedFiles();
            $this->assertStringContainsString('/Filter /FlateDecode', $compressedOut);
        } finally {
            @\unlink((string) $tmpFile);
        }
    }

    public function testGetOutEmbeddedFilesSkipsEmptyFiles(): void
    {
        $obj = $this->getInternalTestObject();
        $tmpFile = \tempnam(\sys_get_temp_dir(), 'tc-out-empty-');
        $this->assertNotFalse($tmpFile);
        \file_put_contents((string) $tmpFile, '');

        try {
            $this->setObjectProperty($obj, 'embeddedfiles', [
                'empty.bin' => [
                    'a' => 0,
                    'f' => 63,
                    'n' => 64,
                    'file' => (string) $tmpFile,
                    'content' => '',
                    'mimeType' => 'application/octet-stream',
                    'afRelationship' => 'Source',
                    'description' => '',
                    'creationDate' => 0,
                    'modDate' => 0,
                ],
            ]);

            $this->assertSame('', $obj->exposeGetOutEmbeddedFiles());
        } finally {
            @\unlink((string) $tmpFile);
        }
    }

    public function testGetOutXObjectsIncludesNestedXObjectReferences(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $outfont = new \Com\Tecnick\Pdf\Font\Output($font->getFonts(), $pon, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        $this->setObjectProperty($obj, 'xobjects', [
            'XO1' => [
                'n' => 101,
                'x' => 0,
                'y' => 0,
                'w' => 10,
                'h' => 10,
                'outdata' => 'q Q',
                'spot_colors' => [],
                'extgstate' => [],
                'gradient' => [],
                'font' => [],
                'image' => [],
                'xobject' => ['XO2'],
                'annotations' => [],
                'id' => 'XO1',
                'pheight' => 0,
                'gheight' => 0,
            ],
            'XO2' => [
                'n' => 102,
                'x' => 0,
                'y' => 0,
                'w' => 10,
                'h' => 10,
                'outdata' => '',
                'spot_colors' => [],
                'extgstate' => [],
                'gradient' => [],
                'font' => [],
                'image' => [],
                'xobject' => [],
                'annotations' => [],
                'id' => 'XO2',
                'pheight' => 0,
                'gheight' => 0,
            ],
        ]);

        $out = $obj->exposeGetOutXObjects();

        $this->assertStringContainsString('/XObject <<', $out);
        $this->assertStringContainsString('/XO2 102 0 R', $out);
    }

    public function testGetOutAnnotationsSkipsFormRegistrationForKnownRadioButtonGroup(): void
    {
        $obj = $this->getInternalTestObject();
        $pageInfo = $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($obj, 'page');
        /** @var array<int, array<string, mixed>> $pgdata */
        $pgdata = $this->getObjectProperty($pageObj, 'page');
        $pgdata[$pageInfo['pid']]['n'] = 12;
        $pgdata[$pageInfo['pid']]['num'] = 1;
        $pgdata[$pageInfo['pid']]['annotrefs'] = [77];
        $this->setObjectProperty($pageObj, 'page', $pgdata);

        $this->setObjectProperty($obj, 'radiobuttons', [
            'rb-direct' => [
                'n' => 88,
                '#readonly#' => false,
                'kids' => [
                    ['n' => 89, 'def' => 'Off'],
                ],
            ],
        ]);

        $this->setObjectProperty($obj, 'annotation', [
            77 => [
                'n' => 77,
                'x' => 5.0,
                'y' => 10.0,
                'w' => 40.0,
                'h' => 8.0,
                'txt' => 'rb-direct',
                'opt' => [
                    'subtype' => 'Widget',
                    'ft' => 'Btn',
                ],
            ],
        ]);

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $outfont = new \Com\Tecnick\Pdf\Font\Output($font->getFonts(), $pon, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        $out = $obj->exposeGetOutAnnotations();

        $this->assertStringContainsString('/FT /Btn', $out);
        $this->assertStringContainsString('/Parent 88 0 R', $out);
    }

    private function setPdfaModeOnObject(TestableOutput $obj, int $pdfa): void
    {
        $obj->setPdfaMode($pdfa);
    }
}
