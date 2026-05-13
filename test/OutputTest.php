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

    protected function getInternalUncompressedTestObject(): TestableOutput
    {
        return new TestableOutput('mm', true, false, false);
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
        return $page->getPage($data['pid']);
    }

    /** @param list<string> $fragments */
    private function assertContainsAllFragments(string $output, array $fragments): void
    {
        foreach ($fragments as $fragment) {
            $this->assertStringContainsString($fragment, $output);
        }
    }

    /**
     * @param list<string> $cmd
     * @return array{code:int,stdout:string,stderr:string}
     */
    private function runExternalCommand(array $cmd, string $cwd): array
    {
        $desc = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = \proc_open($cmd, $desc, $pipes, $cwd);
        if (!\is_resource($proc)) {
            return ['code' => 127, 'stdout' => '', 'stderr' => 'Unable to start process'];
        }

        \fclose($pipes[0]);
        $stdout = (string) \stream_get_contents($pipes[1]);
        \fclose($pipes[1]);
        $stderr = (string) \stream_get_contents($pipes[2]);
        \fclose($pipes[2]);
        $code = \proc_close($proc);

        return ['code' => $code, 'stdout' => $stdout, 'stderr' => $stderr];
    }

    private function isCommandAvailable(string $name): bool
    {
        $res = $this->runExternalCommand(['sh', '-lc', 'command -v ' . \escapeshellarg($name)], __DIR__ . '/..');
        return $res['code'] === 0 && \trim($res['stdout']) !== '';
    }

    public function testSubsetFontPdfHasReaderCompatibleFontObjects(): void
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', true, true, true);
        $font = $obj->font->insert($obj->pon, 'dejavusans', '', 12);
        $obj->addPage();
        $obj->page->addContent($font['out']);
        $obj->addHTMLCell(
            '<h1>Subset Font Regression</h1><p><b>Bold</b> THE QUICK BROWN FOX · π ≈ 3.14159 © ® ™</p>',
            15,
            20,
            180,
        );

        $raw = $obj->getOutPDFString();

        // Ensure CID metadata is explicit and no empty CID registry/ordering is emitted.
        $this->assertStringContainsString('/Subtype /CIDFontType2', $raw);
        $this->assertStringNotContainsString('/Registry () /Ordering ()', $raw);
        $this->assertMatchesRegularExpression('/\\/Registry \(Adobe\) \\/Ordering \(Identity\) \\/Supplement 0/', $raw);

        // Embedded subset streams should be non-trivial font payloads, not tiny/broken stubs.
        $matches = [];
        \preg_match_all('/\\/Length1\\s+(\\d+)/', $raw, $matches);
        $lengths = \array_map('intval', $matches[1]);
        if ($lengths === []) {
            $this->fail('Expected at least one Length1 entry in generated PDF.');
        }
        $this->assertGreaterThan(1000, \max($lengths));

        $tmpBase = \sys_get_temp_dir() . '/tc-lib-pdf-subset-' . \bin2hex(\random_bytes(6));
        $pdfPath = $tmpBase . '.pdf';
        \file_put_contents($pdfPath, $raw);

        try {
            if ($this->isCommandAvailable('pdftoppm')) {
                $popplerOut = $this->runExternalCommand([
                    'pdftoppm',
                    '-f',
                    '1',
                    '-singlefile',
                    '-png',
                    $pdfPath,
                    $tmpBase . '-poppler',
                ], __DIR__ . '/..');
                $this->assertSame(0, $popplerOut['code'], $popplerOut['stderr']);
                $this->assertStringNotContainsString("Couldn't create a font", $popplerOut['stderr']);
                if (\file_exists($tmpBase . '-poppler.png')) {
                    \unlink($tmpBase . '-poppler.png');
                }
            }

            if ($this->isCommandAvailable('mutool')) {
                $mupdfPng = $tmpBase . '-mupdf.png';
                $mupdfOut = $this->runExternalCommand([
                    'mutool',
                    'draw',
                    '-o',
                    $mupdfPng,
                    $pdfPath,
                    '1',
                ], __DIR__ . '/..');
                $this->assertSame(0, $mupdfOut['code'], $mupdfOut['stderr']);
                $this->assertStringNotContainsString('FT_New_Memory_Face', $mupdfOut['stderr']);
                $this->assertStringNotContainsString('locations (loca) table missing', $mupdfOut['stderr']);
                $this->assertStringNotContainsString('broken table', $mupdfOut['stderr']);
                if (\file_exists($mupdfPng)) {
                    \unlink($mupdfPng);
                }
            }
        } finally {
            if (\file_exists($pdfPath)) {
                \unlink($pdfPath);
            }
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

        $code = \str_replace(['AUTOLOAD_PATH', 'FONTS_PATH'], [$autoload, $fonts], $code);

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
        $this->assertTrue(\mkdir($dir, 0o777, true));

        try {
            $obj->savePDF($dir, 'raw-pdf-data');
            $filepath = $dir . '/saved-output.pdf';

            $this->assertFileExists($filepath);
            $this->assertSame('raw-pdf-data', (string) \file_get_contents($filepath));
        } finally {
            if (\file_exists($dir . '/saved-output.pdf')) {
                \unlink($dir . '/saved-output.pdf');
            }
            if (\is_dir($dir)) {
                \rmdir($dir);
            }
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

        $this->assertSame(
            [
                1 => \strpos($data, "1 0 obj\n"),
                3 => \strpos($data, "3 0 obj\n"),
                10 => \strpos($data, "10 0 obj\n"),
            ],
            $offsets,
        );
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

        $expected =
            "xref\n"
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
        $this->assertSame('[0.100000 0.200000 0.300000 0.400000]', $obj->exposeGetColorStringFromPercArray([
            0.1,
            0.2,
            0.3,
            0.4,
        ]));
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

    public function testGetOutResourcesDictAddsSvgMaskExtGStateWhenGraphHasNoExtGState(): void
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

        $obj->setSvgMasks([
            'MSK_ONLY' => [
                'id' => 'MSK_ONLY',
                'stream' => 'q Q',
                'bbox' => [0.0, 0.0, 10.0, 10.0],
                'gs_n' => 42,
            ],
        ]);

        $out = $obj->exposeGetOutResourcesDict();

        $this->assertStringContainsString('/ExtGState <<', $out);
        $this->assertStringContainsString('/MSK_ONLY 42 0 R', $out);
    }

    public function testGetOutResourcesDictMergesSvgMaskEntriesWithExistingExtGState(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        /** @var \Com\Tecnick\Pdf\Graph\Draw $graph */
        $graph = $this->getObjectProperty($obj, 'graph');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $outfont = new \Com\Tecnick\Pdf\Font\Output($font->getFonts(), $pon, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        // Ensure graph resources already contain at least one GS entry.
        $graph->getAlpha(0.6);

        $obj->setSvgMasks([
            'MSK_MERGE' => [
                'id' => 'MSK_MERGE',
                'stream' => 'q Q',
                'bbox' => [0.0, 0.0, 10.0, 10.0],
                'gs_n' => 43,
            ],
        ]);

        $out = $obj->exposeGetOutResourcesDict();

        $this->assertStringContainsString('/ExtGState <<', $out);
        $this->assertStringContainsString('/GS1', $out);
        $this->assertStringContainsString('/MSK_MERGE 43 0 R', $out);
    }

    public function testPatternStreamResourcesKeepOnlyReferencedFontAndSpotAliases(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');

        $timesfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/times.json');
        $font->insert($pon, 'times', '', 10, null, null, $timesfile);

        $outfont = new \Com\Tecnick\Pdf\Font\Output($font->getFonts(), $pon, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        /** @var \Com\Tecnick\Color\Pdf $color */
        $color = $this->getObjectProperty($obj, 'color');
        $this->setObjectProperty($color, 'spot_colors', [
            'spotA' => ['i' => 1, 'n' => 111],
            'spotB' => ['i' => 2, 'n' => 222],
        ]);

        $stream = '/F1 12 Tf /CS1 cs';
        $out = $obj->exposeGetPatternStreamResourceDict($stream);

        $this->assertStringContainsString(' /Font <<', $out);
        $this->assertStringContainsString(' /F1 ', $out);
        $this->assertStringNotContainsString(' /F2 ', $out);
        $this->assertStringContainsString(' /ColorSpace <<', $out);
        $this->assertStringContainsString(' /CS1 ', $out);
        $this->assertStringNotContainsString(' /CS2 ', $out);
    }

    public function testPatternStreamResourcesHandleEmptyFontDictionary(): void
    {
        $obj = $this->getInternalTestObject();

        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        $outfont = new \Com\Tecnick\Pdf\Font\Output([], 0, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        $out = $obj->exposeGetPatternStreamResourceDict('/F99 10 Tf');

        $this->assertSame('', $out);
    }

    public function testGetOutPatternsSkipsEntriesWithEmptyStreamData(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'patterns', [
            1 => [
                'n' => 0,
                'outdata' => '',
                'bbox' => [0.0, 0.0, 10.0, 10.0],
                'xstep' => 10.0,
                'ystep' => 10.0,
                'matrix' => [1.0, 0.0, 0.0, 1.0, 0.0, 0.0],
            ],
        ]);

        $out = $obj->exposeGetOutPatterns();

        $this->assertSame('', $out);
    }

    public function testPatternStreamResourcesFilterMixedCategoriesByStreamUsage(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        /** @var \Com\Tecnick\Color\Pdf $color */
        $color = $this->getObjectProperty($obj, 'color');
        /** @var \Com\Tecnick\Pdf\Graph\Draw $graph */
        $graph = $this->getObjectProperty($obj, 'graph');

        $timesfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/times.json');
        $font->insert($pon, 'times', '', 10, null, null, $timesfile);

        $outfont = new \Com\Tecnick\Pdf\Font\Output($font->getFonts(), $pon, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        $this->setObjectProperty($color, 'spot_colors', [
            'spotA' => ['i' => 1, 'n' => 111],
            'spotB' => ['i' => 2, 'n' => 222],
        ]);

        // Register at least two ExtGState entries so we can verify selective inclusion.
        $graph->getAlpha(0.9);
        $graph->getAlpha(0.5);

        $this->setObjectProperty($obj, 'xobjects', [
            'XO1' => ['n' => 201],
            'XO2' => ['n' => 202],
        ]);

        $stream = '/F1 10 Tf /CS1 cs /GS2 gs /XO2 Do';
        $out = $obj->exposeGetPatternStreamResourceDict($stream);

        $this->assertStringContainsString(' /Font <<', $out);
        $this->assertStringContainsString(' /F1 ', $out);
        $this->assertStringNotContainsString(' /F2 ', $out);
        $this->assertStringContainsString(' /ColorSpace <<', $out);
        $this->assertStringContainsString(' /CS1 ', $out);
        $this->assertStringNotContainsString(' /CS2 ', $out);
        $this->assertStringContainsString(' /ExtGState <<', $out);
        $this->assertStringContainsString(' /GS2 ', $out);
        $this->assertStringNotContainsString(' /GS1 ', $out);
        $this->assertStringContainsString(' /XObject <<', $out);
        $this->assertStringContainsString(' /XO2 202 0 R', $out);
        $this->assertStringNotContainsString(' /XO1 201 0 R', $out);
    }

    public function testPatternStreamResourcesParseGradientAndImageDoTokens(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        /** @var \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt */
        $encrypt = $this->getObjectProperty($obj, 'encrypt');
        /** @var \Com\Tecnick\Pdf\Graph\Draw $graph */
        $graph = $this->getObjectProperty($obj, 'graph');

        $outfont = new \Com\Tecnick\Pdf\Font\Output($font->getFonts(), $pon, $encrypt);
        $this->setObjectProperty($obj, 'outfont', $outfont);

        $graph->getLinearGradient(0.0, 0.0, 10.0, 10.0, 'red', 'blue');
        $graph->getOutGradientShaders($pon);
        $gradientId = $graph->getLastGradientID();

        $this->assertNotNull($gradientId);

        $out = $obj->exposeGetPatternStreamResourceDict('/Sh' . $gradientId . ' sh /I2 Do');

        $this->assertStringContainsString(' /Pattern <<', $out);
        $this->assertStringContainsString(' /XObject <<', $out);
    }

    public function testImplementedAnnotationSubtypeHelpersReturnExpectedFragments(): void
    {
        $obj = $this->getInternalTestObject();
        $lineOut = $obj->exposeGetOutAnnotationOptSubtypeLine([
            'opt' => [
                'l' => [1.0, 2.0, 3.0, 4.0],
                'le' => ['OpenArrow', 'ClosedArrow'],
                'ic' => [0.1, 0.2, 0.3],
                'cap' => true,
                'it' => 'LineDimension',
                'cp' => 'Inline',
                'co' => [1.5, 2.5],
            ],
        ]);
        $this->assertStringContainsString(' /L [', $lineOut);
        $this->assertStringContainsString(' /LE [/OpenArrow /ClosedArrow]', $lineOut);
        $this->assertStringContainsString(' /IC [0.100000 0.200000 0.300000]', $lineOut);
        $this->assertStringContainsString(' /Cap true', $lineOut);
        $this->assertStringContainsString(' /IT /LineDimension', $lineOut);
        $this->assertStringContainsString(' /CP /Inline', $lineOut);
        $this->assertStringContainsString(' /CO [', $lineOut);

        $squareOut = $obj->exposeGetOutAnnotationOptSubtypeSquare([
            'opt' => [
                'ic' => [0.2, 0.3, 0.4],
                'rd' => [1.0, 2.0, 3.0, 4.0],
            ],
        ]);
        $this->assertStringContainsString(' /IC [0.200000 0.300000 0.400000]', $squareOut);
        $this->assertStringContainsString(' /RD [', $squareOut);

        $circleOut = $obj->exposeGetOutAnnotationOptSubtypeCircle([
            'opt' => [
                'ic' => [0.4, 0.5, 0.6],
                'rd' => [1.0, 2.0, 3.0, 4.0],
            ],
        ]);
        $this->assertStringContainsString(' /IC [0.400000 0.500000 0.600000]', $circleOut);
        $this->assertStringContainsString(' /RD [', $circleOut);

        $polygonOut = $obj->exposeGetOutAnnotationOptSubtypePolygon([
            'opt' => [
                'vertices' => [1.0, 2.0, 3.0, 4.0, 5.0, 6.0],
                'ic' => [0.1, 0.1, 0.1],
                'it' => 'PolygonCloud',
            ],
        ]);
        $this->assertStringContainsString(' /Vertices [', $polygonOut);
        $this->assertStringContainsString(' /IC [0.100000 0.100000 0.100000]', $polygonOut);
        $this->assertStringContainsString(' /IT /PolygonCloud', $polygonOut);

        $polylineOut = $obj->exposeGetOutAnnotationOptSubtypePolyline([
            'opt' => [
                'vertices' => [1.0, 2.0, 3.0, 4.0],
                'le' => ['Circle', 'Slash'],
            ],
        ]);
        $this->assertStringContainsString(' /Vertices [', $polylineOut);
        $this->assertStringContainsString(' /LE [/Circle /Slash]', $polylineOut);

        $quad = [[1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0]];
        $this->assertStringContainsString(' /QuadPoints [', $obj->exposeGetOutAnnotationOptSubtypeHighlight(['opt' => [
            'quadpoints' => $quad,
        ]]));
        $this->assertStringContainsString(' /QuadPoints [', $obj->exposeGetOutAnnotationOptSubtypeUnderline(['opt' => [
            'quadpoints' => $quad,
        ]]));
        $this->assertStringContainsString(' /QuadPoints [', $obj->exposeGetOutAnnotationOptSubtypeSquiggly(['opt' => [
            'quadpoints' => $quad,
        ]]));
        $this->assertStringContainsString(' /QuadPoints [', $obj->exposeGetOutAnnotationOptSubtypeStrikeout(['opt' => [
            'quadpoints' => $quad,
        ]]));

        $stampOut = $obj->exposeGetOutAnnotationOptSubtypeStamp(['opt' => ['name' => 'Approved']]);
        $this->assertSame(' /Name /Approved', $stampOut);

        $caretOut = $obj->exposeGetOutAnnotationOptSubtypeCaret(['opt' => ['rd' => [1.0, 2.0, 3.0, 4.0], 'sy' => 'P']]);
        $this->assertStringContainsString(' /RD [', $caretOut);
        $this->assertStringContainsString(' /Sy /P', $caretOut);

        $inkOut = $obj->exposeGetOutAnnotationOptSubtypeInk([
            'opt' => [
                'inklist' => [
                    [1.0, 2.0, 3.0, 4.0],
                    [5.0, 6.0, 7.0, 8.0],
                ],
            ],
        ]);
        $this->assertStringContainsString(' /InkList [[', $inkOut);

        $popupOut = $obj->exposeGetOutAnnotationOptSubtypePopup(['opt' => ['parent' => ['n' => 9], 'open' => true]]);
        $this->assertStringContainsString(' /Parent 9 0 R', $popupOut);
        $this->assertStringContainsString(' /Open true', $popupOut);
    }

    public function testAdvancedAnnotationSubtypeHelpersReturnExpectedFragments(): void
    {
        $obj = $this->getInternalTestObject();

        $movieOut = $obj->exposeGetOutAnnotationOptSubtypeMovie([
            'n' => 11,
            'opt' => [
                't' => 'Movie title',
                'movie' => [
                    'f' => 'clip.mov',
                    'aspect' => [16.0, 9.0],
                    'rotate' => 90,
                    'poster' => true,
                ],
                'a' => [
                    'rate' => 1.25,
                    'volume' => 0.8,
                    'showcontrols' => true,
                    'mode' => 'Repeat',
                    'synchronous' => false,
                ],
            ],
        ]);
        $this->assertStringContainsString(' /T ', $movieOut);
        $this->assertStringContainsString(' /Movie << /F ', $movieOut);
        $this->assertStringContainsString(' /Aspect [16.000000 9.000000]', $movieOut);
        $this->assertStringContainsString(' /Rotate 90', $movieOut);
        $this->assertStringContainsString(' /Poster true', $movieOut);
        $this->assertStringContainsString(' /A <<', $movieOut);
        $this->assertStringContainsString(' /Rate 1.250000', $movieOut);
        $this->assertStringContainsString(' /Volume 0.800000', $movieOut);
        $this->assertStringContainsString(' /ShowControls true', $movieOut);
        $this->assertStringContainsString(' /Mode /Repeat', $movieOut);
        $this->assertStringContainsString(' /Synchronous false', $movieOut);

        $screenOut = $obj->exposeGetOutAnnotationOptSubtypeScreen([
            'n' => 12,
            'opt' => [
                't' => 'Screen title',
                'mk' => [
                    'r' => 180,
                    'bc' => [0.1, 0.2, 0.3],
                    'bg' => [0.7],
                    'ca' => '(CA)',
                    'rc' => '(RC)',
                    'ac' => '(AC)',
                    'tp' => 4,
                ],
                'a' => '/S /Named /N /NextPage',
                'aa' => '/E << /S /Named /N /PrevPage >>',
            ],
        ]);
        $this->assertStringContainsString(' /T ', $screenOut);
        $this->assertStringContainsString(' /MK <<', $screenOut);
        $this->assertStringContainsString(' /R 180', $screenOut);
        $this->assertStringContainsString(' /BC [0.100000 0.200000 0.300000]', $screenOut);
        $this->assertStringContainsString(' /BG [0.700000]', $screenOut);
        $this->assertStringContainsString(' /TP 4', $screenOut);
        $this->assertStringContainsString(' /A << /S /Named /N /NextPage >>', $screenOut);
        $this->assertStringContainsString(' /AA << /E << /S /Named /N /PrevPage >> >>', $screenOut);

        $redactOut = $obj->exposeGetOutAnnotationOptSubtypeRedact([
            'n' => 13,
            'opt' => [
                'quadpoints' => [[1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0]],
                'ic' => [0.0, 0.0, 0.0],
                'overlaytext' => 'REDACTED',
                'repeat' => true,
                'da' => '/F1 10 Tf',
                'q' => 2,
            ],
        ]);
        $this->assertStringContainsString(' /QuadPoints [', $redactOut);
        $this->assertStringContainsString(' /IC [0.000000 0.000000 0.000000]', $redactOut);
        $this->assertStringContainsString(' /OverlayText ', $redactOut);
        $this->assertStringContainsString(' /Repeat true', $redactOut);
        $this->assertStringContainsString(' /DA ', $redactOut);
        $this->assertStringContainsString(' /Q 2', $redactOut);

        $watermarkOut = $obj->exposeGetOutAnnotationOptSubtypeWatermark([
            'opt' => [
                'fixedprint' => [
                    'type' => 'FixedPrint',
                    'matrix' => [1.0, 0.0, 0.0, 1.0, 10.0, 20.0],
                    'h' => 0.25,
                    'v' => 0.75,
                ],
            ],
        ]);
        $this->assertStringContainsString(' /FixedPrint <<', $watermarkOut);
        $this->assertStringContainsString(' /Type /FixedPrint', $watermarkOut);
        $this->assertStringContainsString(
            ' /Matrix [1.000000 0.000000 0.000000 1.000000 10.000000 20.000000]',
            $watermarkOut,
        );
        $this->assertStringContainsString(' /H 0.250000', $watermarkOut);
        $this->assertStringContainsString(' /V 0.750000', $watermarkOut);
    }

    public function testLineAndQuadpointAnnotationHelpersIgnoreInvalidValues(): void
    {
        $obj = $this->getInternalTestObject();

        $lineOut = $obj->exposeGetOutAnnotationOptSubtypeLine([
            'opt' => [
                'l' => ['x', 'y', 'z', 'w'],
                'le' => [1, 2],
                'co' => ['left', 'right'],
            ],
        ]);
        $this->assertStringNotContainsString(' /L [', $lineOut);
        $this->assertStringNotContainsString(' /LE [', $lineOut);
        $this->assertStringNotContainsString(' /CO [', $lineOut);

        $lineBadStylesOut = $obj->exposeGetOutAnnotationOptSubtypeLine([
            'opt' => [
                'le' => ['BadStyle', 'AlsoBad'],
            ],
        ]);
        $this->assertStringNotContainsString(' /LE [', $lineBadStylesOut);

        $highlightOut = $obj->exposeGetOutAnnotationOptSubtypeHighlight([
            'opt' => [
                'quadpoints' => [
                    'not-an-array',
                    [1.0, 2.0, 3.0],
                ],
            ],
        ]);
        $this->assertSame('', $highlightOut);
    }

    public function testMovieSubtypeSupportsAdditionalPosterAndActionVariants(): void
    {
        $obj = $this->getInternalTestObject();

        $movieBoolActionOut = $obj->exposeGetOutAnnotationOptSubtypeMovie([
            'n' => 21,
            'opt' => [
                'movie' => [
                    'f' => 'clip.mov',
                    'poster' => '(poster-name)',
                ],
                'a' => true,
            ],
        ]);
        $this->assertStringContainsString(' /Poster ', $movieBoolActionOut);
        $this->assertStringContainsString(' /A true', $movieBoolActionOut);

        $movieActionOut = $obj->exposeGetOutAnnotationOptSubtypeMovie([
            'n' => 22,
            'opt' => [
                'movie' => ['f' => 'clip2.mov'],
                'a' => [
                    'start' => ['chapter-1', 5],
                    'duration' => ['segment-a', 2],
                    'fwscale' => [2, 3],
                ],
            ],
        ]);
        $this->assertStringContainsString(' /Start [', $movieActionOut);
        $this->assertStringContainsString(' /Duration [', $movieActionOut);
        $this->assertStringContainsString(' /FWScale [2 3]', $movieActionOut);
    }

    public function testLineSubtypeIncludesExtendedLineAndMeasureOptions(): void
    {
        $obj = $this->getInternalTestObject();

        $lineOut = $obj->exposeGetOutAnnotationOptSubtypeLine([
            'opt' => [
                'll' => 1.25,
                'lle' => 2.5,
                'llo' => 3.75,
                'measure' => [
                    'type' => 'Measure',
                    'subtype' => 'RL',
                ],
            ],
        ]);

        $this->assertStringContainsString(' /LL 1.250000', $lineOut);
        $this->assertStringContainsString(' /LLE 2.500000', $lineOut);
        $this->assertStringContainsString(' /LLO 3.750000', $lineOut);
        $this->assertStringContainsString(' /Measure << /Type /Measure /Subtype /RL >>', $lineOut);
    }

    public function testPolygonAndInkSubtypeAdditionalBranches(): void
    {
        $obj = $this->getInternalTestObject();

        $polygonOut = $obj->exposeGetOutAnnotationOptSubtypePolygon([
            'opt' => [
                'vertices' => [1.0, 2.0, 3.0, 4.0],
                'measure' => [
                    'type' => 'Measure',
                    'subtype' => 'RL',
                ],
            ],
        ]);
        $this->assertStringContainsString(' /Vertices [', $polygonOut);
        $this->assertStringContainsString(' /Measure << /Type /Measure /Subtype /RL >>', $polygonOut);

        $inkOut = $obj->exposeGetOutAnnotationOptSubtypeInk([
            'opt' => [
                'inklist' => [
                    'bad-line',
                    [1.0, 2.0, 3.0],
                ],
            ],
        ]);
        $this->assertSame('', $inkOut);
    }

    public function testMovieSubtypeCoversStartDurationAndFwPositionVariants(): void
    {
        $obj = $this->getInternalTestObject();

        $numericOut = $obj->exposeGetOutAnnotationOptSubtypeMovie([
            'n' => 23,
            'opt' => [
                'movie' => ['f' => 'clip3.mov'],
                'a' => [
                    'start' => 9,
                    'duration' => 7,
                ],
            ],
        ]);
        $this->assertStringContainsString(' /Start 9', $numericOut);
        $this->assertStringContainsString(' /Duration 7', $numericOut);

        $stringOut = $obj->exposeGetOutAnnotationOptSubtypeMovie([
            'n' => 24,
            'opt' => [
                'movie' => ['f' => 'clip4.mov'],
                'a' => [
                    'start' => 'chapter-a',
                    'duration' => 'segment-b',
                ],
            ],
        ]);
        $this->assertStringContainsString(' /Start ', $stringOut);
        $this->assertStringContainsString(' /Duration ', $stringOut);

        $arrayNumericOut = $obj->exposeGetOutAnnotationOptSubtypeMovie([
            'n' => 25,
            'opt' => [
                'movie' => ['f' => 'clip5.mov'],
                'a' => [
                    'start' => [3, 1],
                    'duration' => [4, 2],
                    'fwposition' => [0.25, 0.75],
                ],
            ],
        ]);
        $this->assertStringContainsString(' /Start [3 1]', $arrayNumericOut);
        $this->assertStringContainsString(' /Duration [4 2]', $arrayNumericOut);
        $this->assertStringContainsString(' /FWPosition [0.250000 0.750000]', $arrayNumericOut);
    }

    public function testIntentionallyUnsupportedAdvancedSubtypeHelpersReturnEmptyString(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypePrintermark([]));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtypeTrapnet([]));
        $this->assertSame('', $obj->exposeGetOutAnnotationOptSubtype3D([]));
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

        $out = $obj->exposeGetOutAnnotationOptSubtypeText(['opt' => [
            'open' => true,
            'statemodel' => 'Marked',
            'state' => 'Accepted',
        ]]);

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

    public function testGetOutAnnotationOptSubtypeLinkSkipsEmbeddedFileJavascriptInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua2');
        $this->setObjectProperty($obj, 'embeddedfiles', [
            'attach.bin' => ['a' => 2],
        ]);

        $embeddedFile = $obj->exposeGetOutAnnotationOptSubtypeLink(['txt' => '*attach.bin', 'opt' => []], 1, 12);

        $this->assertStringNotContainsString(' /S /JavaScript /JS ', $embeddedFile);
        $this->assertStringContainsString(' /H /I', $embeddedFile);
    }

    public function testGetOutAnnotationOptSubtypeLinkSkipsEmbeddedFileJavascriptInPdfxMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->setObjectProperty($obj, 'embeddedfiles', [
            'attach.bin' => ['a' => 2],
        ]);

        $embeddedFile = $obj->exposeGetOutAnnotationOptSubtypeLink(['txt' => '*attach.bin', 'opt' => []], 1, 12);

        $this->assertStringNotContainsString(' /S /JavaScript /JS ', $embeddedFile);
        $this->assertStringContainsString(' /H /I', $embeddedFile);
    }

    public function testGetOutAnnotationOptSubtypeLinkSuppressesGotoeAndGotorInPdfxMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->setObjectProperty($obj, 'embeddedfiles', [
            'sample.pdf' => ['a' => 4],
        ]);

        $namedDest = $obj->exposeGetOutAnnotationOptSubtypeLink(['txt' => '#dest-1', 'opt' => ['h' => 'N']], 2, 10);
        $externalUri = $obj->exposeGetOutAnnotationOptSubtypeLink(['txt' => 'https://example.com', 'opt' => []], 2, 11);
        $embeddedPdf = $obj->exposeGetOutAnnotationOptSubtypeLink(['txt' => '%sample.pdf', 'opt' => []], 2, 12);
        $relativePdf = $obj->exposeGetOutAnnotationOptSubtypeLink(
            ['txt' => 'docs/guide.pdf#named=Section2', 'opt' => []],
            2,
            13,
        );

        $this->assertStringContainsString(' /S /GoTo /D /dest-1', $namedDest);
        $this->assertStringContainsString(' /H /N', $namedDest);

        $this->assertStringNotContainsString(' /S /URI /URI ', $externalUri);
        $this->assertStringNotContainsString(' /A <<', $externalUri);
        $this->assertStringContainsString(' /H /I', $externalUri);

        $this->assertStringNotContainsString(' /S /GoToE', $embeddedPdf);
        $this->assertStringNotContainsString(' /A <<', $embeddedPdf);
        $this->assertStringContainsString(' /H /I', $embeddedPdf);

        $this->assertStringNotContainsString(' /S /GoToR', $relativePdf);
        $this->assertStringNotContainsString(' /A <<', $relativePdf);
        $this->assertStringContainsString(' /H /I', $relativePdf);
    }

    public function testGetOutAnnotationOptSubtypeLinkSuppressesExternalUriInPdfxMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);

        $externalUri = $obj->exposeGetOutAnnotationOptSubtypeLink(
            ['txt' => 'https://example.com/docs?a=1&b=2', 'opt' => []],
            2,
            11,
        );

        $this->assertStringNotContainsString(' /S /URI', $externalUri);
        $this->assertStringNotContainsString(' /A <<', $externalUri);
        $this->assertStringContainsString(' /H /I', $externalUri);
    }

    #[DataProvider('pdfxModeProvider')]
    public function testPdfxModeMatrixSuppressesInteractiveLinkActions(string $mode): void
    {
        $obj = new TestableOutput('mm', true, false, true, $mode);
        $this->setObjectProperty($obj, 'embeddedfiles', [
            'sample.pdf' => ['a' => 4],
        ]);

        $namedDest = $obj->exposeGetOutAnnotationOptSubtypeLink(['txt' => '#dest-1', 'opt' => ['h' => 'N']], 2, 10);
        $externalUri = $obj->exposeGetOutAnnotationOptSubtypeLink(['txt' => 'https://example.com', 'opt' => []], 2, 11);
        $embeddedPdf = $obj->exposeGetOutAnnotationOptSubtypeLink(['txt' => '%sample.pdf', 'opt' => []], 2, 12);
        $relativePdf = $obj->exposeGetOutAnnotationOptSubtypeLink(
            ['txt' => 'docs/guide.pdf#named=Section2', 'opt' => []],
            2,
            13,
        );

        $this->assertStringContainsString(' /S /GoTo /D /dest-1', $namedDest);
        $this->assertStringContainsString(' /H /N', $namedDest);

        $this->assertStringNotContainsString(' /S /URI', $externalUri);
        $this->assertStringNotContainsString(' /A <<', $externalUri);
        $this->assertStringContainsString(' /H /I', $externalUri);

        $this->assertStringNotContainsString(' /S /GoToE', $embeddedPdf);
        $this->assertStringNotContainsString(' /A <<', $embeddedPdf);
        $this->assertStringContainsString(' /H /I', $embeddedPdf);

        $this->assertStringNotContainsString(' /S /GoToR', $relativePdf);
        $this->assertStringNotContainsString(' /A <<', $relativePdf);
        $this->assertStringContainsString(' /H /I', $relativePdf);
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

    public function testGetOutAnnotationOptSubtypeWidgetOmitsJavascriptActionsInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');
        $annot = [
            'txt' => 'field-choice',
            'opt' => [
                'a' => '/S /JavaScript /JS (alert)',
                'aa' => '/E << /S /JavaScript /JS (x) >>',
                'h' => 'T',
                'ff' => 5,
            ],
        ];

        $out = $obj->exposeGetOutAnnotationOptSubtypeWidget($annot, 31);

        $this->assertStringNotContainsString(' /A << /S /JavaScript', $out);
        $this->assertStringNotContainsString(' /AA << /E << /S /JavaScript', $out);
    }

    public function testGetOutAnnotationOptSubtypeWidgetOmitsJavascriptActionsInPdfxMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $annot = [
            'txt' => 'field-choice',
            'opt' => [
                'a' => '/S /JavaScript /JS (alert)',
                'aa' => '/E << /S /JavaScript /JS (x) >>',
                'h' => 'T',
                'ff' => 5,
            ],
        ];

        $out = $obj->exposeGetOutAnnotationOptSubtypeWidget($annot, 31);

        $this->assertStringNotContainsString(' /A << /S /JavaScript', $out);
        $this->assertStringNotContainsString(' /AA << /E << /S /JavaScript', $out);
    }

    public function testGetOutAnnotationOptSubtypeWidgetOmitsNonJavascriptActionsInPdfxMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $annot = [
            'txt' => 'field-choice',
            'opt' => [
                'a' => '/S /ResetForm',
                'aa' => '/E << /S /URI /URI (https://example.test) >>',
                'h' => 'T',
                'ff' => 5,
            ],
        ];

        $out = $obj->exposeGetOutAnnotationOptSubtypeWidget($annot, 31);

        $this->assertStringNotContainsString(' /A << /S /ResetForm >>', $out);
        $this->assertStringNotContainsString(' /AA << /E << /S /URI /URI', $out);
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

    public function testGetOutPDFBodyIncludesStructTreeRootInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $page = $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');
        $obj->addTextCell('Tagged PDF/UA text', $page['pid'], 10, 10, 60, 10);

        $out = $obj->exposeGetOutPDFBody();

        $this->assertStringContainsString('/StructParents 0', $out);
        $this->assertMatchesRegularExpression('/\/Nums \[ 0 \[\s*\d+ 0 R\s*\]\s*\]/', $out);
        $this->assertStringContainsString('/Type /StructTreeRoot', $out);
        $this->assertStringContainsString('/ParentTree ', $out);
        $this->assertStringContainsString('/ParentTreeNextKey 1', $out);
        $this->assertStringContainsString('/K [ ', $out);
        $this->assertStringContainsString('/Type /StructElem /S /Document', $out);
        $this->assertStringContainsString('/Type /StructElem /S /P', $out);
        $this->assertStringContainsString('/Type /MCR', $out);
        $this->assertStringContainsString('/MCID 0', $out);
        $this->assertMatchesRegularExpression('/\/Pg \d+ 0 R/', $out);
        $this->assertStringContainsString('/StructTreeRoot ', $out);
    }

    public function testGetOutPDFBodyWrapsFirstPageContentInMarkedContentForPdfuaMode(): void
    {
        $obj = $this->getInternalUncompressedTestObject();
        $page = $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');
        $obj->addTextCell('Tagged PDF/UA text', $page['pid'], 10, 10, 60, 10);

        $out = $obj->exposeGetOutPDFBody();

        $this->assertStringContainsString('/P <</MCID 0>> BDC', $out);
        $this->assertStringContainsString('EMC', $out);
        $this->assertStringContainsString('/Type /MCR', $out);
    }

    public function testGetOutPDFBodyIncludesMultipleMcidsForMultipleTextCellsInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $page = $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');
        $obj->addTextCell('First PDF/UA text block', $page['pid'], 10, 10, 60, 10);
        $obj->addTextCell('Second PDF/UA text block', $page['pid'], 10, 20, 60, 10);

        $out = $obj->exposeGetOutPDFBody();

        $this->assertStringContainsString('/MCID 0', $out);
        $this->assertStringContainsString('/MCID 1', $out);
        $this->assertMatchesRegularExpression('/\/Type \/StructElem \/S \/P.*\/MCID 0/s', $out);
        $this->assertMatchesRegularExpression('/\/Type \/StructElem \/S \/P.*\/MCID 1/s', $out);
    }

    public function testGetOutPDFBodyBracketedTextCellsShareOneStructElem(): void
    {
        $obj = $this->getInternalTestObject();
        $page = $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        $obj->beginStructElem('H1', $page['pid']);
        $obj->addTextCell('Heading text', $page['pid'], 10, 10, 60, 10);
        $obj->addTextCell('Continuation', $page['pid'], 10, 20, 60, 10);
        $obj->endStructElem();

        $out = $obj->exposeGetOutPDFBody();

        // Both MCIDs should appear in the output.
        $this->assertStringContainsString('/MCID 0', $out);
        $this->assertStringContainsString('/MCID 1', $out);
        // There should be exactly one H1 StructElem containing both MCID references.
        $this->assertMatchesRegularExpression('/\/Type \/StructElem \/S \/H1.*\/MCID 0.*\/MCID 1/s', $out);
        // There should be no separate P StructElem for these fragments.
        $this->assertStringNotContainsString('/Type /StructElem /S /P', $out);
    }

    public function testGetOutPDFBodyBracketedContentUsesStructRoleInBdcTag(): void
    {
        $obj = $this->getInternalUncompressedTestObject();
        $page = $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        $obj->beginStructElem('H2', $page['pid']);
        $obj->addTextCell('Subheading', $page['pid'], 10, 10, 60, 10);
        $obj->endStructElem();

        $out = $obj->exposeGetOutPDFBody();

        // BDC tag in the content stream must use the struct elem role, not the fallback /P.
        $this->assertStringContainsString('/H2 <</MCID 0>> BDC', $out);
        $this->assertStringNotContainsString('/P <</MCID 0>> BDC', $out);
    }

    public function testGetOutPDFBodyTagsHtmlTextInPdfuaMode(): void
    {
        $obj = $this->getInternalUncompressedTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        $obj->addHTMLCell('<h1>PDF/UA</h1><p>Mode: pdfua</p>', 10, 10, 80, 20);

        $out = $obj->exposeGetOutPDFBody();

        $this->assertStringContainsString('/Type /StructTreeRoot', $out);
        $this->assertStringContainsString('/H1 <</MCID 0>> BDC', $out);
        $this->assertStringContainsString('/P <</MCID 1>> BDC', $out);
        $this->assertStringContainsString('/Type /StructElem /S /H1', $out);
        $this->assertStringContainsString('/Type /StructElem /S /P', $out);
    }

    public function testGetOutPDFBodyPreservesNestedStructElemHierarchy(): void
    {
        $obj = $this->getInternalUncompressedTestObject();
        $page = $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        $obj->beginStructElem('Sect', $page['pid']);
        $obj->beginStructElem('H1', $page['pid']);
        $obj->addTextCell('Nested heading', $page['pid'], 10, 10, 60, 10);
        $obj->endStructElem();
        $obj->beginStructElem('P', $page['pid']);
        $obj->addTextCell('Nested paragraph', $page['pid'], 10, 20, 60, 10);
        $obj->endStructElem();
        $obj->endStructElem();

        $out = $obj->exposeGetOutPDFBody();

        $docMatch = [];
        $this->assertSame(1, \preg_match(
            '/(\d+) 0 obj\s*<< \/Type \/StructElem \/S \/Document .*?\/K \[\s*(\d+) 0 R\s*\] >>/s',
            $out,
            $docMatch,
        ));

        $sectOid = $docMatch[2];
        $sectMatch = [];
        $this->assertSame(1, \preg_match(
            '/'
            . $sectOid
            . ' 0 obj\\s*<< \/Type \/StructElem \/S \/Sect \/P '
            . $docMatch[1]
            . ' 0 R .*?\/K \[\s*(\d+) 0 R\s+(\d+) 0 R\s*\] >>/s',
            $out,
            $sectMatch,
        ));

        $this->assertMatchesRegularExpression(
            '/' . $sectMatch[1] . ' 0 obj\\s*<< \/Type \/StructElem \/S \/H1 \/P ' . $sectOid . ' 0 R/s',
            $out,
        );
        $this->assertMatchesRegularExpression(
            '/' . $sectMatch[2] . ' 0 obj\\s*<< \/Type \/StructElem \/S \/P \/P ' . $sectOid . ' 0 R/s',
            $out,
        );
    }

    public function testGetOutPDFBodyTagsHtmlImageAsFigureWithAltInPdfuaMode(): void
    {
        $obj = new TestableOutput('mm', true, false, false, 'pdfua1');
        $this->initFontAndPage($obj);

        $img = \imagecreate(4, 4);
        \imagecolorallocate($img, 255, 255, 255);
        \ob_start();
        \imagepng($img);
        $raw = \ob_get_clean();
        $this->assertIsString($raw);
        $src = 'data:image/png;base64,' . \base64_encode($raw);

        $html = '<img src="' . $src . '" alt="Chart icon" width="4" height="4" />';
        $htmlOut = $obj->getHTMLCell($html, 0, 0, 20, 10);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $page->addContent($htmlOut, $page->getPageId());

        $out = $obj->exposeGetOutPDFBody();

        $this->assertStringContainsString('/Figure <</MCID 0>> BDC', $out);
        $this->assertStringContainsString('/Type /StructElem /S /Figure', $out);
        $this->assertStringContainsString('/Alt ', $out);
    }

    public function testGetOutPDFBodyTagsHtmlTableStructRolesInPdfuaMode(): void
    {
        $obj = new TestableOutput('mm', true, false, false, 'pdfua1');
        $this->initFontAndPage($obj);

        $html =
            '<table><thead><tr><th>Name</th><th>Value</th></tr></thead>'
            . '<tbody><tr><td>A</td><td>1</td></tr></tbody></table>';
        $htmlOut = $obj->getHTMLCell($html, 0, 0, 80, 30);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $page->addContent($htmlOut, $page->getPageId());

        $out = $obj->exposeGetOutPDFBody();

        $this->assertStringContainsString('/Type /StructElem /S /Table', $out);
        $this->assertStringContainsString('/Type /StructElem /S /TR', $out);
        $this->assertStringContainsString('/Type /StructElem /S /TH', $out);
        $this->assertStringContainsString('/Type /StructElem /S /TD', $out);
    }

    public function testGetOutPDFBodyTagsHtmlListStructRolesInPdfuaMode(): void
    {
        $obj = new TestableOutput('mm', true, false, false, 'pdfua1');
        $this->initFontAndPage($obj);

        $html = '<ul><li>Alpha</li><li>Beta</li></ul>';
        $htmlOut = $obj->getHTMLCell($html, 0, 0, 80, 30);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $page->addContent($htmlOut, $page->getPageId());

        $out = $obj->exposeGetOutPDFBody();

        $this->assertStringContainsString('/Type /StructElem /S /L', $out);
        $this->assertStringContainsString('/Type /StructElem /S /LI', $out);
        $this->assertStringContainsString('/Type /StructElem /S /LBody', $out);
    }

    public function testGetOutPDFBodyTagsManualFigureContentWithAltInPdfuaMode(): void
    {
        $obj = new TestableOutput('mm', true, false, false, 'pdfua1');
        $this->initFontAndPage($obj);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $pid = $page->getPageId();

        $style = [
            'all' => [
                'lineWidth' => 0.3,
                'lineCap' => 'butt',
                'lineJoin' => 'miter',
                'dashArray' => [],
                'dashPhase' => 0,
                'lineColor' => '#336699',
                'fillColor' => '#cce0ff',
            ],
        ];

        $obj->addTaggedFigureContent(
            $obj->graph->getRect(10.0, 10.0, 12.0, 8.0, 'DF', $style),
            $pid,
            'Blue rectangle sample figure',
        );

        $out = $obj->exposeGetOutPDFBody();

        $this->assertStringContainsString('/Figure <</MCID 0>> BDC', $out);
        $this->assertStringContainsString('/Type /StructElem /S /Figure', $out);
        $this->assertStringContainsString('/Alt ', $out);
    }

    public function testGetOutPDFBodyTagsHtmlFigureWithFigcaptionInPdfuaMode(): void
    {
        $obj = new TestableOutput('mm', true, false, false, 'pdfua1');
        $this->initFontAndPage($obj);

        $img = \imagecreate(4, 4);
        \imagecolorallocate($img, 255, 255, 255);
        \ob_start();
        \imagepng($img);
        $raw = \ob_get_clean();
        $this->assertIsString($raw);
        $src = 'data:image/png;base64,' . \base64_encode($raw);

        $html =
            '<figure><img src="'
            . $src
            . '" alt="Test dot" width="4" height="4" />'
            . '<figcaption>Caption text</figcaption></figure>';
        $htmlOut = $obj->getHTMLCell($html, 0, 0, 80, 30);

        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $page->addContent($htmlOut, $page->getPageId());

        $out = $obj->exposeGetOutPDFBody();

        // Single Figure struct elem (no nested Figure > Figure)
        $this->assertSame(
            1,
            \substr_count($out, '/Type /StructElem /S /Figure'),
            'Expected exactly one Figure StructElem',
        );
        $this->assertStringContainsString('/Alt ', $out);
        $this->assertStringContainsString('/Type /StructElem /S /Caption', $out);
    }

    public function testGetOutPDFBodySerializesStructElemAttributes(): void
    {
        $obj = $this->getInternalUncompressedTestObject();
        $page = $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        $obj->beginStructElem('TH', $page['pid'], null, ['Scope' => 'Column']);
        $obj->addTextCell('Header', $page['pid'], 10, 10, 40, 10);
        $obj->endStructElem();

        $out = $obj->exposeGetOutPDFBody();

        $this->assertStringContainsString('/Type /StructElem /S /TH', $out);
        $this->assertStringContainsString('/A << /Scope /Column >>', $out);
    }

    public function testGetOutPDFBodyPromotesStructElemIdAttributeToIdEntry(): void
    {
        $obj = $this->getInternalUncompressedTestObject();
        $page = $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        $obj->beginStructElem('Note', $page['pid'], null, ['ID' => 'note-1']);
        $obj->addTextCell('Note content', $page['pid'], 10, 10, 40, 10);
        $obj->endStructElem();

        $out = $obj->exposeGetOutPDFBody();

        $this->assertMatchesRegularExpression('/\/Type \/StructElem \/S \/Note.*\/ID /s', $out);
        $this->assertStringNotContainsString('/A << /ID /note-1 >>', $out);
    }

    public function testGetOutPDFBodyTagsHtmlLinkWithObjrAndStructParent(): void
    {
        $obj = $this->getInternalUncompressedTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        $obj->addHTMLCell('<p><a href="https://example.com">Link</a></p>', 10, 10, 80, 20);

        $out = $obj->exposeGetOutPDFBody();

        $this->assertStringContainsString('/Subtype /Link', $out);
        $this->assertStringContainsString('/StructParent ', $out);
        $this->assertStringContainsString('/Type /OBJR', $out);
        $this->assertStringContainsString('/Type /StructElem /S /Link', $out);
        $this->assertStringContainsString('/Tabs /S', $out);
    }

    public function testGetOutCatalogIncludesRequiredEntries(): void
    {
        $obj = $this->getInternalTestObject();
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(__DIR__
        . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);
        $this->addRawPageWithObjectNumber($obj, 6);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4]);
        $this->setObjectProperty($obj, 'display', [
            'layout' => 'SinglePage',
            'mode' => 'UseNone',
            'zoom' => 'fullpage',
        ]);
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

    public function testGetOutCatalogIncludesDssReferenceWhenAvailable(): void
    {
        $obj = $this->getInternalTestObject();
        $this->addRawPageWithObjectNumber($obj, 6);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4, 'dss' => 12]);

        $out = $obj->exposeGetOutCatalog();

        $this->assertStringContainsString('/DSS 12 0 R', $out);
    }

    public function testGetOutPDFBodyIncludesDssWhenLtvDssEnabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $certPath = __DIR__ . '/fixtures/cert_with_revocation_urls.pem';
        $certPem = (string) \file_get_contents($certPath);

        /** @var array<string, mixed> $signature */
        $signature = $this->getObjectProperty($obj, 'signature');
        $signature = \array_replace_recursive($signature, [
            'signcert' => $certPem,
            'extracerts' => $certPem,
            'ltv' => [
                'enabled' => true,
                'embed_ocsp' => true,
                'embed_crl' => true,
                'embed_certs' => true,
                'include_dss' => true,
                'include_vri' => true,
            ],
        ]);
        $this->setObjectProperty($obj, 'signature', $signature);
        $obj->setMockOcspResponse('mock-ocsp-response-binary');
        $obj->setMockCrlResponse('mock-crl-binary');

        $out = $obj->exposeGetOutPDFBody();

        $this->assertContainsAllFragments($out, [
            '/Type /DSS',
            '/VRI <<',
            '/OCSPs [',
            '/CRLs [',
            '/Certs [',
            '/Type /VRI',
            '/DSS ',
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

        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx4');
        $this->assertStringContainsString(
            "\xfe\xff\x00P\x00D\x00F\x00/\x00X\x00-\x004",
            $obj->exposeGetOutputIntentsPdfX(),
        );

        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx1a');
        $this->assertStringContainsString(
            "\xfe\xff\x00P\x00D\x00F\x00/\x00X\x00-\x001\x00a",
            $obj->exposeGetOutputIntentsPdfX(),
        );

        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx3');
        $this->assertStringContainsString(
            "\xfe\xff\x00P\x00D\x00F\x00/\x00X\x00-\x003",
            $obj->exposeGetOutputIntentsPdfX(),
        );

        $this->setObjectProperty($obj, 'pdfxMode', 'pdfx5');
        $this->assertStringContainsString(
            "\xfe\xff\x00P\x00D\x00F\x00/\x00X\x00-\x005",
            $obj->exposeGetOutputIntentsPdfX(),
        );

        $this->setObjectProperty($obj, 'pdfxMode', '');
        $this->assertStringContainsString(
            "\xfe\xff\x00O\x00F\x00C\x00O\x00M\x00_\x00P\x00O\x00_\x00P\x001\x00_\x00F\x006\x000\x00_\x009\x005",
            $obj->exposeGetOutputIntentsPdfX(),
        );
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

    public function testGetOutSignatureEmitsUserRightsSignatureForCertTypeZero(): void
    {
        $obj = $this->getInternalTestObject();
        $page = $this->addRawPageWithObjectNumber($obj, 6);
        $certPath = __DIR__ . '/../examples/data/cert/tcpdf.crt';
        $certPem = (string) \file_get_contents($certPath);

        $obj->setSignature([
            'appearance' => [
                'empty' => [],
                'name' => 'Signature',
                'page' => $page['pid'],
                'rect' => '10 10 60 20',
            ],
            'approval' => '',
            'cert_type' => 0,
            'extracerts' => '',
            'info' => [
                'ContactInfo' => '',
                'Location' => '',
                'Name' => '',
                'Reason' => '',
            ],
            'password' => '',
            'privkey' => $certPem,
            'signcert' => $certPem,
            'ltv' => [
                'enabled' => false,
                'embed_ocsp' => true,
                'embed_crl' => true,
                'embed_certs' => true,
                'include_dss' => true,
                'include_vri' => true,
            ],
        ]);

        $out = $obj->exposeGetOutSignature();

        $this->assertContainsAllFragments($out, [
            '/Type /Sig',
            '/TransformMethod /UR3',
            '/SubFilter /adbe.pkcs7.detached',
        ]);
    }

    public function testGetOutSignatureSkipsSignatureReferenceForApprovalMode(): void
    {
        $obj = $this->getInternalTestObject();
        $page = $this->addRawPageWithObjectNumber($obj, 6);
        $certPath = __DIR__ . '/../examples/data/cert/tcpdf.crt';
        $certPem = (string) \file_get_contents($certPath);

        $obj->setSignature([
            'appearance' => [
                'empty' => [],
                'name' => 'Signature',
                'page' => $page['pid'],
                'rect' => '10 10 60 20',
            ],
            'approval' => 'A',
            'cert_type' => 2,
            'extracerts' => '',
            'info' => [
                'ContactInfo' => '',
                'Location' => '',
                'Name' => '',
                'Reason' => '',
            ],
            'password' => '',
            'privkey' => $certPem,
            'signcert' => $certPem,
            'ltv' => [
                'enabled' => false,
                'embed_ocsp' => true,
                'embed_crl' => true,
                'embed_certs' => true,
                'include_dss' => true,
                'include_vri' => true,
            ],
        ]);

        $out = $obj->exposeGetOutSignature();

        $this->assertStringContainsString('/Type /Sig', $out);
        $this->assertStringNotContainsString('/Reference [ << /Type /SigRef', $out);
    }

    public function testGetOutSignatureIncludesCustomAppearanceStream(): void
    {
        $obj = $this->getInternalUncompressedTestObject();
        $page = $this->addRawPageWithObjectNumber($obj, 6);
        $certPath = __DIR__ . '/../examples/data/cert/tcpdf.crt';
        $certPem = (string) \file_get_contents($certPath);

        $obj->setSignature([
            'appearance' => [
                'ap' => [
                    'n' => 'q 1 0 0 rg 0 0 50 10 re f Q',
                ],
                'empty' => [],
                'name' => 'Signature',
                'page' => $page['pid'],
                'rect' => '10 10 60 20',
            ],
            'approval' => '',
            'cert_type' => 2,
            'extracerts' => '',
            'info' => [
                'ContactInfo' => '',
                'Location' => '',
                'Name' => '',
                'Reason' => '',
            ],
            'password' => '',
            'privkey' => $certPem,
            'signcert' => $certPem,
            'ltv' => [
                'enabled' => false,
                'embed_ocsp' => true,
                'embed_crl' => true,
                'embed_certs' => true,
                'include_dss' => true,
                'include_vri' => true,
            ],
        ]);

        $out = $obj->exposeGetOutSignature();

        $this->assertContainsAllFragments($out, [
            '/Subtype /Widget',
            '/AP << /N ',
            '/Subtype /Form',
            '0 0 50 10 re f',
        ]);
    }

    public function testGetOutSignatureIncludesFittedXObjectAppearance(): void
    {
        $obj = $this->getInternalUncompressedTestObject();
        $page = $this->addRawPageWithObjectNumber($obj, 6);
        $certPath = __DIR__ . '/../examples/data/cert/tcpdf.crt';
        $certPem = (string) \file_get_contents($certPath);

        /** @var array<string, array<string, mixed>> $xobjects */
        $xobjects = $this->getObjectProperty($obj, 'xobjects');
        $xobjects['IMP1'] = [
            'spot_colors' => [],
            'extgstate' => [],
            'gradient' => [],
            'font' => [],
            'image' => [],
            'xobject' => [],
            'annotations' => [],
            'id' => 'IMP1',
            'n' => 88,
            'x' => 0,
            'w' => 50,
            'y' => 0,
            'h' => 20,
            'outdata' => '',
            'pheight' => 0,
            'gheight' => 0,
        ];
        $this->setObjectProperty($obj, 'xobjects', $xobjects);

        $obj->setSignature([
            'appearance' => [
                'empty' => [],
                'name' => 'Signature',
                'page' => $page['pid'],
                'rect' => '10 10 110 50',
                'xobj' => 'IMP1',
            ],
            'approval' => '',
            'cert_type' => 2,
            'extracerts' => '',
            'info' => [
                'ContactInfo' => '',
                'Location' => '',
                'Name' => '',
                'Reason' => '',
            ],
            'password' => '',
            'privkey' => $certPem,
            'signcert' => $certPem,
            'ltv' => [
                'enabled' => false,
                'embed_ocsp' => true,
                'embed_crl' => true,
                'embed_certs' => true,
                'include_dss' => true,
                'include_vri' => true,
            ],
        ]);

        $out = $obj->exposeGetOutSignature();

        $this->assertContainsAllFragments($out, [
            '/AP << /N ',
            '/IMP1 Do',
            'q 2.000000 0 0 2.000000 0 0 cm',
        ]);
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
        $prefix = 'XPFX';
        $mid = '/Contents ';
        $sigSlot = '<' . \str_repeat('0', 11742) . '>';
        $suffix = 'XSFX';
        $pdfdoc = $prefix . $byterangePlaceholder . $mid . $sigSlot . $suffix . "\n";

        $result = $obj->exposePrepareDocumentForSignature($pdfdoc);

        $this->assertSame(0, $result['byte_range'][0]);
        $this->assertSame($result['pdfdoc_length'], \strlen($result['pdfdoc']));
        $this->assertStringNotContainsString('**********', $result['pdfdoc']);
        $this->assertMatchesRegularExpression('#/ByteRange\[0 \d+ \d+ \d+\] *#', $result['pdfdoc']);
        // Signature slot (SIGMAXLEN + 2 for < >) is stripped from the signing content
        $expectedLength = \strlen($prefix) + \strlen($byterangePlaceholder) + \strlen($mid) + \strlen($suffix);
        $this->assertSame($expectedLength, $result['pdfdoc_length']);
        // byte_range[3] must equal the length of the suffix
        $this->assertSame(\strlen($suffix), $result['byte_range'][3]);
    }

    public function testBuildTimestampRequestContainsSha256OidWhenNonceDisabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'sigtimestamp', [
            'enabled' => true,
            'host' => 'https://tsa.example.test',
            'username' => '',
            'password' => '',
            'cert' => '',
            'hash_algorithm' => 'sha256',
            'policy_oid' => '',
            'nonce_enabled' => false,
            'timeout' => 5,
            'verify_peer' => true,
        ]);

        $request = $obj->exposeBuildTimestampRequest('sigbin');

        $this->assertStringStartsWith("\x30", $request);
        $this->assertStringContainsString('608648016503040201', \bin2hex($request));
    }

    public function testApplySignatureTimestampWithMockedTsaResponse(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'sigtimestamp', [
            'enabled' => true,
            'host' => 'https://tsa.example.test',
            'username' => '',
            'password' => '',
            'cert' => '',
            'hash_algorithm' => 'sha256',
            'policy_oid' => '',
            'nonce_enabled' => false,
            'timeout' => 5,
            'verify_peer' => false,
        ]);

        // SEQUENCE { SEQUENCE { INTEGER 0 }, SEQUENCE { INTEGER 42 } }
        $obj->setMockTimestampResponse((string) \hex2bin('300A3003020100300302012A'));
        $signed = $obj->exposeApplySignatureTimestamp('sigbin');

        $this->assertSame('sigbin', $signed);
        $this->assertStringStartsWith("\x30", $obj->getCapturedTimestampRequest());
    }

    public function testExtractTimestampTokenFromResponseRejectsFailureStatus(): void
    {
        $obj = $this->getInternalTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        // SEQUENCE { SEQUENCE { INTEGER 2 } }
        $obj->exposeExtractTimestampTokenFromResponse((string) \hex2bin('30053003020102'));
    }

    public function testCollectValidationMaterialReturnsEmptyWhenLtvDisabled(): void
    {
        $obj = $this->getInternalTestObject();

        $material = $obj->exposeCollectValidationMaterial();

        $this->assertSame([], $material['cert_chain']);
        $this->assertSame([], $material['certs']);
        $this->assertSame([], $material['vri']);
    }

    public function testCollectValidationMaterialLoadsAndDeduplicatesCertificates(): void
    {
        $obj = $this->getInternalTestObject();
        $certPath = __DIR__ . '/../examples/data/cert/tcpdf.crt';
        $certPem = (string) \file_get_contents($certPath);

        $this->setObjectProperty($obj, 'signature', [
            'signcert' => $certPem,
            'extracerts' => $certPem,
            'ltv' => [
                'enabled' => true,
                'embed_ocsp' => false,
                'embed_crl' => false,
                'embed_certs' => true,
                'include_dss' => true,
                'include_vri' => true,
            ],
        ]);

        $material = $obj->exposeCollectValidationMaterial();

        $this->assertGreaterThan(0, \count($material['cert_chain']));
        $this->assertSame(1, \count($material['certs']));
        $this->assertSame(1, \count($material['vri']));
    }

    public function testCollectValidationMaterialCollectsOcspAndCrlWhenEnabled(): void
    {
        $obj = $this->getInternalTestObject();
        $certPath = __DIR__ . '/fixtures/cert_with_revocation_urls.pem';
        $certPem = (string) \file_get_contents($certPath);

        $this->setObjectProperty($obj, 'signature', [
            'signcert' => $certPem,
            'extracerts' => $certPem,
            'ltv' => [
                'enabled' => true,
                'embed_ocsp' => true,
                'embed_crl' => true,
                'embed_certs' => true,
                'include_dss' => true,
                'include_vri' => true,
            ],
        ]);
        $obj->setMockOcspResponse('mock-ocsp-response-binary');
        $obj->setMockCrlResponse('mock-crl-binary');

        $material = $obj->exposeCollectValidationMaterial();

        $this->assertSame(1, \count($material['ocsp']));
        $this->assertSame(1, \count($material['crls']));
        $this->assertSame('http://ocsp.example.com/', $obj->getCapturedOcspUrl());
        $this->assertSame('http://crl2.example.com/root.crl', $obj->getCapturedCrlUrl());
        $this->assertNotSame('', $obj->getCapturedOcspRequest());
        $this->assertSame(1, \count($material['vri']));

        $vri = \array_values($material['vri'])[0];
        $this->assertSame([0], $vri['ocsp']);
        $this->assertSame([0, 0], $vri['crls']);
    }

    public function testCollectValidationMaterialFallsBackToCrlWhenOcspFails(): void
    {
        $obj = $this->getInternalTestObject();
        $certPath = __DIR__ . '/fixtures/cert_with_revocation_urls.pem';
        $certPem = (string) \file_get_contents($certPath);

        $this->setObjectProperty($obj, 'signature', [
            'signcert' => $certPem,
            'extracerts' => $certPem,
            'ltv' => [
                'enabled' => true,
                'embed_ocsp' => true,
                'embed_crl' => true,
                'embed_certs' => true,
                'include_dss' => true,
                'include_vri' => true,
            ],
        ]);
        $obj->setMockOcspThrows(true);
        $obj->setMockCrlResponse('mock-crl-binary');

        $material = $obj->exposeCollectValidationMaterial();

        $this->assertSame([], $material['ocsp']);
        $this->assertSame(1, \count($material['crls']));
        $this->assertSame('http://ocsp.example.com/', $obj->getCapturedOcspUrl());

        $vri = \array_values($material['vri'])[0];
        $this->assertSame([], $vri['ocsp']);
        $this->assertSame([0, 0], $vri['crls']);
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

        [$aas, $apx] = $obj->exposeGetAnnotationAppearanceStream(['opt' => ['ap' => '/N 1 0 R']], 10.0, 5.0);

        $this->assertMatchesRegularExpression('#/AP <<\s*/N 1 0 R\s*>>#', $aas);
        $this->assertSame('', $apx);
    }

    public function testGetAnnotationAppearanceStreamWithArrayApStringDef(): void
    {
        $obj = $this->getInternalTestObject();

        [$aas, $apx] = $obj->exposeGetAnnotationAppearanceStream(['opt' => ['ap' => ['n' => 'q Q']]], 8.0, 4.0);

        $this->assertStringContainsString(' /N ', $aas);
        $this->assertStringContainsString('/Subtype /Form', $apx);
    }

    public function testGetAnnotationAppearanceStreamWithArrayApArrayDef(): void
    {
        $obj = $this->getInternalTestObject();

        [$aas, $apx] = $obj->exposeGetAnnotationAppearanceStream(
            ['opt' => ['ap' => ['n' => ['On' => 'q Q', 'Off' => '']]]],
            8.0,
            4.0,
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
            $this->assertStringContainsString($fragment, $out);
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

    public function testGetOutCatalogOmitsJavascriptTreeInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->addRawPageWithObjectNumber($obj, 3);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4]);
        $this->setObjectProperty($obj, 'jstree', '<< /Names [(JS) 1 0 R] >>');
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        $out = $obj->exposeGetOutCatalog();

        $this->assertStringNotContainsString('/JavaScript', $out);
    }

    public function testGetOutCatalogOmitsJavascriptTreeInPdfxMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->addRawPageWithObjectNumber($obj, 3);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4]);
        $this->setObjectProperty($obj, 'jstree', '<< /Names [(JS) 1 0 R] >>');
        $this->setObjectProperty($obj, 'pdfx', true);

        $out = $obj->exposeGetOutCatalog();

        $this->assertStringNotContainsString('/JavaScript', $out);
    }

    public function testGetOutCatalogIncludesMarkInfoAndDefaultLanguageInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->addRawPageWithObjectNumber($obj, 3);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4]);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');

        $out = $obj->exposeGetOutCatalog();

        $this->assertStringContainsString('/MarkInfo << /Marked true >>', $out);
        $this->assertStringContainsString('/Lang ', $out);
        $this->assertStringContainsString("\x00e\x00n\x00-\x00U\x00S", $out);
    }

    public function testGetOutCatalogIncludesStructTreeRootReferenceWhenAvailable(): void
    {
        $obj = $this->getInternalTestObject();
        $this->addRawPageWithObjectNumber($obj, 3);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4]);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');
        $this->setObjectProperty($obj, 'structtreerootoid', 42);

        $out = $obj->exposeGetOutCatalog();

        $this->assertStringContainsString('/StructTreeRoot 42 0 R', $out);
    }

    public function testGetOutPDFBodyResetsStructureObjectIdsOutsidePdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'parenttreeoid', 12);
        $this->setObjectProperty($obj, 'structtreerootoid', 34);
        $this->setObjectProperty($obj, 'pagestructparents', [9 => 0]);
        $this->setObjectProperty($obj, 'pagestructmcids', [9 => 1]);

        $method = new \ReflectionMethod($obj, 'getOutStructTreeRoot');
        $method->setAccessible(true);
        $out = $method->invoke($obj);

        $this->assertSame('', $out);
        $this->assertSame([], $this->getObjectProperty($obj, 'pagestructparents'));
        $this->assertSame([], $this->getObjectProperty($obj, 'pagestructmcids'));
        $this->assertSame(0, $this->getObjectProperty($obj, 'parenttreeoid'));
        $this->assertSame(0, $this->getObjectProperty($obj, 'structtreerootoid'));
    }

    public function testGetOutCatalogUsesConfiguredLanguageInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->addRawPageWithObjectNumber($obj, 3);
        $obj->setOutputState(9, ['pages' => 3, 'xmp' => 4]);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');
        $this->setObjectProperty($obj, 'lang', ['a_meta_language' => 'it-IT']);

        $out = $obj->exposeGetOutCatalog();

        $this->assertStringContainsString('/MarkInfo << /Marked true >>', $out);
        $this->assertStringContainsString('/Lang ', $out);
        $this->assertStringContainsString("\x00i\x00t\x00-\x00I\x00T", $out);
        $this->assertStringNotContainsString("\x00e\x00n\x00-\x00U\x00S", $out);
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
        $fontfile = (string) \realpath(__DIR__
        . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
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

    public function testGetOutXObjectsSuppressesTransparencyGroupInPdfx3(): void
    {
        $obj = new TestableOutput('mm', true, false, true, 'pdfx3');
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
            'XT3' => [
                'id' => 'XT3',
                'n' => 3,
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

        $this->assertStringNotContainsString('/Group << /Type /Group /S /Transparency', $out);
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

    public function testGetOutAnnotationsLinkAnnotationIncludesContentsInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');
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
        $this->assertStringContainsString('/Contents ', $out);
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

        $aoid = $obj->setAnnotation(5.0, 10.0, 80.0, 12.0, 'myfield', ['subtype' => 'Widget', 'ft' => 'Tx']);

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

        $aoid = $obj->setAnnotation(5.0, 10.0, 80.0, 12.0, 'Colored note', ['subtype' => 'text', 'c' => '#FF0000']);

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

    public function testGetOutBookmarksWithStarEmbeddedFileLinkOmitsJavascriptInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');
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

        $this->assertStringNotContainsString('/S /JavaScript', $out);
    }

    public function testGetOutBookmarksWithStarEmbeddedFileLinkOmitsJavascriptInPdfxMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $this->setObjectProperty($obj, 'pdfx', true);
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

        $this->assertStringNotContainsString('/S /JavaScript', $out);
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

    public function testGetOutBookmarksWithPercentEmbeddedPdfLinkOmitsGotoeInPdfxMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
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

        $this->assertStringNotContainsString('/S /GoToE', $out);
        $this->assertStringNotContainsString('/A 5', $out);
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

    public function testGetOutBookmarksWithExternalUriLinkOmitsUriActionInPdfxMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 6);

        $obj->setBookmark('External Site', 'https://example.com/docs?a=1&b=2', 0, $page['pid']);

        $out = $obj->exposeGetOutBookmarks();

        $this->assertStringNotContainsString('/S /URI', $out);
        $this->assertStringNotContainsString('/URI ', $out);
    }

    #[DataProvider('pdfxModeProvider')]
    public function testPdfxModeMatrixSuppressesInteractiveBookmarkActions(string $mode): void
    {
        $obj = new TestableOutput('mm', true, false, true, $mode);
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

        $obj->setBookmark('External Site', 'https://example.com/docs?a=1&b=2', 0, $page['pid']);
        $obj->setBookmark('Embedded PDF', '%manual.pdf', 0, $page['pid']);

        $out = $obj->exposeGetOutBookmarks();

        $this->assertStringNotContainsString('/S /URI', $out);
        $this->assertStringNotContainsString('/URI ', $out);
        $this->assertStringNotContainsString('/S /GoToE', $out);
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

    /** @return array<string, array{0: string}> */
    public static function pdfxModeProvider(): array
    {
        return [
            'pdfx_alias' => ['pdfx'],
            'pdfx1a' => ['pdfx1a'],
            'pdfx3' => ['pdfx3'],
            'pdfx4' => ['pdfx4'],
            'pdfx5' => ['pdfx5'],
        ];
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

    public function testGetOutJavascriptReturnsEmptyInPdfuaMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');
        $this->setObjectProperty($obj, 'javascript', 'app.alert("hi");');
        $this->setObjectProperty($obj, 'jsobjects', [[
            'n' => 1,
            'js' => 'console.println("x")',
            'onload' => true,
        ]]);

        $this->assertSame('', $obj->exposeGetOutJavascript());
    }

    public function testGetOutJavascriptReturnsEmptyInPdfxMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $this->setObjectProperty($obj, 'javascript', 'app.alert("hi");');
        $this->setObjectProperty($obj, 'jsobjects', [[
            'n' => 1,
            'js' => 'console.println("x")',
            'onload' => true,
        ]]);

        $this->assertSame('', $obj->exposeGetOutJavascript());
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
            'cert_type' => 0,
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

    public function testEndToEndOutputIncludesSignatureTsaAndDssInSingleRevision(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $page = $this->addRawPageWithObjectNumber($obj, 14);
        $certPath = (string) \realpath(__DIR__ . '/../examples/data/cert/tcpdf.crt');
        $certFile = 'file://' . $certPath;

        $obj->setSignature([
            'appearance' => [
                'empty' => [],
                'name' => 'SigE2E',
                'page' => $page['pid'],
                'rect' => '10 20 50 30',
            ],
            'approval' => '',
            'cert_type' => 2,
            'extracerts' => null,
            'info' => [
                'ContactInfo' => 'contact@example.test',
                'Location' => 'Lab',
                'Name' => 'Signer',
                'Reason' => 'End-to-end test',
            ],
            'password' => '',
            'privkey' => $certFile,
            'signcert' => $certFile,
            'ltv' => [
                'enabled' => true,
                'embed_ocsp' => false,
                'embed_crl' => false,
                'embed_certs' => true,
                'include_dss' => true,
                'include_vri' => true,
            ],
        ]);

        $obj->setSignTimeStamp([
            'enabled' => true,
            'host' => 'https://tsa.example.test',
            'username' => '',
            'password' => '',
            'cert' => '',
            'hash_algorithm' => 'sha256',
            'policy_oid' => '',
            'nonce_enabled' => false,
            'timeout' => 5,
            'verify_peer' => false,
        ]);

        // SEQUENCE { SEQUENCE { INTEGER 0 }, SEQUENCE { INTEGER 42 } }
        $obj->setMockTimestampResponse((string) \hex2bin('300A3003020100300302012A'));

        $pdf = $obj->getOutPDFString();

        $this->assertContainsAllFragments($pdf, [
            '/Type /Sig',
            '/SubFilter /adbe.pkcs7.detached',
            '/TransformMethod /DocMDP',
            '/Type /DSS',
            '/VRI <<',
            '/Certs [',
            '/DSS ',
            'startxref',
            '%%EOF',
        ]);
        $this->assertSame(1, \substr_count($pdf, '%%EOF'));
        $this->assertMatchesRegularExpression('#/ByteRange\[0 \d+ \d+ \d+\]#', $pdf);
        $this->assertStringNotContainsString('**********', $pdf);
        $this->assertStringStartsWith("\x30", $obj->getCapturedTimestampRequest());
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

        [$appearanceState, $appearanceXObject] = $obj->exposeGetAnnotationAppearanceStream(
            [
                'opt' => [
                    'ap' => [
                        'n' => ['On' => 123, 'Off' => 'q 1 0 0 1 0 0 cm Q'],
                    ],
                ],
            ],
            10,
            5,
        );
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
        \file_put_contents($tmpFile, 'embedded-content');

        try {
            $this->setPdfaModeOnObject($obj, 3);
            $this->setObjectProperty($obj, 'embeddedfiles', [
                'plain.txt' => [
                    'a' => 0,
                    'f' => 11,
                    'n' => 12,
                    'file' => $tmpFile,
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
                    'file' => $tmpFile,
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
            if (\file_exists($tmpFile)) {
                \unlink($tmpFile);
            }
        }
    }

    public function testGetOutEmbeddedFilesSkipsEmptyFiles(): void
    {
        $obj = $this->getInternalTestObject();
        $tmpFile = \tempnam(\sys_get_temp_dir(), 'tc-out-empty-');
        $this->assertNotFalse($tmpFile);
        \file_put_contents($tmpFile, '');

        try {
            $this->setObjectProperty($obj, 'embeddedfiles', [
                'empty.bin' => [
                    'a' => 0,
                    'f' => 63,
                    'n' => 64,
                    'file' => $tmpFile,
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
            if (\file_exists($tmpFile)) {
                \unlink($tmpFile);
            }
        }
    }

    public function testGetOutEmbeddedFilesSkipsUnreadableSourceFiles(): void
    {
        $obj = $this->getInternalTestObject();

        $throwingFile = new class() extends \Com\Tecnick\File\File {
            public function fileGetContents(string $path): string
            {
                throw new \Com\Tecnick\Pdf\Exception('mock unreadable file: ' . $path);
            }
        };
        $this->setObjectProperty($obj, 'file', $throwingFile);

        $this->setObjectProperty($obj, 'embeddedfiles', [
            'missing.bin' => [
                'a' => 0,
                'f' => 75,
                'n' => 76,
                'file' => '/tmp/mock.bin',
                'content' => '',
                'mimeType' => 'application/octet-stream',
                'afRelationship' => 'Source',
                'description' => '',
                'creationDate' => 0,
                'modDate' => 0,
            ],
        ]);

        $this->assertSame('', $obj->exposeGetOutEmbeddedFiles());
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

    public function testGetOutPDFTrailerSuppressesEncryptInPdfxMode(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $obj->setOutputState(4, ['catalog' => 7, 'info' => 8], 'FEEDBEEF', 12);

        $trailer = $obj->exposeGetOutPDFTrailer();

        $this->assertStringNotContainsString('/Encrypt', $trailer);
    }

    private function setPdfaModeOnObject(TestableOutput $obj, int $pdfa): void
    {
        $obj->setPdfaMode($pdfa);
    }

    // -------------------------------------------------------------------------
    // E-4 SVG mask PDF output tests
    // -------------------------------------------------------------------------

    /**
     * getOutSVGMasks emits Form XObject + SMask + ExtGState for a registered mask
     * and records the ExtGState object number in gs_n.
     */
    public function testGetOutSVGMasksEmitsThreePdfObjectsAndSetsGsN(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $obj->setSvgMasks([
            'MSK_AABBCCDD' => [
                'id' => 'MSK_AABBCCDD',
                'stream' => 'q Q',
                'bbox' => [0.0, 0.0, 595.0, 841.0],
                'gs_n' => 0,
            ],
        ]);

        $out = $obj->exposeGetOutSVGMasks();

        // Three PDF objects emitted.
        $this->assertSame(3, \substr_count($out, 'endobj'));

        // Form XObject.
        $this->assertStringContainsString('/Type /XObject', $out);
        $this->assertStringContainsString('/Subtype /Form', $out);
        $this->assertStringContainsString('/Group <<', $out);
        $this->assertStringContainsString('/CS /DeviceGray', $out);

        // SMask dict.
        $this->assertStringContainsString('/Type /Mask', $out);
        $this->assertStringContainsString('/S /Luminosity', $out);

        // ExtGState.
        $this->assertStringContainsString('/Type /ExtGState', $out);
        $this->assertStringContainsString('/SMask', $out);
        $this->assertStringContainsString('/AIS false', $out);

        // gs_n must be set to the ExtGState object number.
        $masks = $obj->getSvgMasks();
        $this->assertArrayHasKey('MSK_AABBCCDD', $masks);
        $this->assertIsArray($masks['MSK_AABBCCDD']);
        $mask = $masks['MSK_AABBCCDD'];
        $this->assertArrayHasKey('gs_n', $mask);
        $this->assertGreaterThan(0, $mask['gs_n']);
    }

    /**
     * getSVGMaskExtGStateEntries returns an entry for each mask with gs_n > 0.
     */
    public function testGetSVGMaskExtGStateEntriesReturnsResourceEntries(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $obj->setSvgMasks([
            'MSK_AA' => ['id' => 'MSK_AA', 'stream' => 'q Q', 'bbox' => [0.0, 0.0, 1.0, 1.0], 'gs_n' => 42],
            'MSK_BB' => ['id' => 'MSK_BB', 'stream' => 'q Q', 'bbox' => [0.0, 0.0, 1.0, 1.0], 'gs_n' => 0],
        ]);

        $entries = $obj->exposeGetSVGMaskExtGStateEntries();

        $this->assertStringContainsString('/MSK_AA 42 0 R', $entries);
        $this->assertStringNotContainsString('/MSK_BB', $entries);
    }

    /**
     * getOutSVGMasks skips masks with empty streams.
     */
    public function testGetOutSVGMasksSkipsEmptyStreamMasks(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);

        $obj->setSvgMasks([
            'MSK_EMPTY' => ['id' => 'MSK_EMPTY', 'stream' => '', 'bbox' => [0.0, 0.0, 1.0, 1.0], 'gs_n' => 0],
        ]);

        $out = $obj->exposeGetOutSVGMasks();

        $this->assertSame('', $out);
        $masks = $obj->getSvgMasks();
        $this->assertArrayHasKey('MSK_EMPTY', $masks);
        $this->assertIsArray($masks['MSK_EMPTY']);
        $mask = $masks['MSK_EMPTY'];
        $this->assertArrayHasKey('gs_n', $mask);
        $this->assertSame(0, $mask['gs_n']);
    }

    // -------------------------------------------------------------------------
    // Widget MK appearance image object references
    // -------------------------------------------------------------------------

    public function testGetOutAnnotationOptSubtypeWidgetMkImageRefsWithRegisteredImages(): void
    {
        $obj = $this->getInternalTestObject();

        /** @var \Com\Tecnick\Pdf\Image\Import $imageObj */
        $imageObj = $this->getObjectProperty($obj, 'image');

        // Build a minimal image cache entry with obj > 0.
        $minEntry = [
            'bits' => 8,
            'channels' => 3,
            'colspace' => 'DeviceRGB',
            'data' => '',
            'exturl' => false,
            'file' => '',
            'filter' => 'FlateDecode',
            'height' => 4,
            'icc' => '',
            'ismask' => false,
            'key' => '',
            'mapto' => \IMAGETYPE_PNG,
            'native' => true,
            'obj' => 0,
            'obj_alt' => 0,
            'obj_icc' => 0,
            'obj_pal' => 0,
            'pal' => '',
            'parms' => '',
            'raw' => '',
            'recode' => false,
            'recoded' => false,
            'splitalpha' => false,
            'trns' => [],
            'type' => \IMAGETYPE_PNG,
            'width' => 4,
        ];

        $keyI = $imageObj->getKey('icon-i.png');
        $keyRi = $imageObj->getKey('icon-ri.png');
        $keyIx = $imageObj->getKey('icon-ix.png');

        $entryI = \array_merge($minEntry, ['key' => $keyI, 'obj' => 51]);
        $entryRi = \array_merge($minEntry, ['key' => $keyRi, 'obj' => 52]);
        $entryIx = \array_merge($minEntry, ['key' => $keyIx, 'obj' => 53]);

        $this->setObjectProperty($imageObj, 'cache', [
            $keyI => $entryI,
            $keyRi => $entryRi,
            $keyIx => $entryIx,
        ]);

        $out = $obj->exposeGetOutAnnotationOptSubtypeWidget([
            'txt' => 'mk-image-field',
            'opt' => [
                'mk' => [
                    'i' => 'icon-i.png',
                    'ri' => 'icon-ri.png',
                    'ix' => 'icon-ix.png',
                ],
            ],
        ], 20);

        $this->assertStringContainsString(' /I 51 0 R', $out);
        $this->assertStringContainsString(' /RI 52 0 R', $out);
        $this->assertStringContainsString(' /IX 53 0 R', $out);
    }

    // -------------------------------------------------------------------------
    // extractNamedResourceRefs empty-name skip
    // -------------------------------------------------------------------------

    public function testExtractNamedResourceRefsSkipsEmptyAndNonStringNames(): void
    {
        $obj = $this->getInternalTestObject();

        $dict = '/Font << /F1 1 0 R /F2 2 0 R >>';

        // Empty string name is skipped; non-string names are skipped via is_string check.
        $out = $obj->exposeExtractNamedResourceRefs($dict, ['F1', '', 'F2']);
        $this->assertStringContainsString('/F1 1 0 R', $out);
        $this->assertStringContainsString('/F2 2 0 R', $out);
        // Only two entries should appear (the empty string was skipped).
        $this->assertSame(2, \substr_count($out, '0 R'));
    }

    // -------------------------------------------------------------------------
    // getOutStructTreeRoot with pdfuaMode set but empty structLog
    // -------------------------------------------------------------------------

    public function testGetOutStructTreeRootReturnsEmptyWhenStructLogIsEmpty(): void
    {
        $obj = $this->getInternalTestObject();

        // pdfuaMode is set but pdfuaStructLog is empty.
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');
        $this->setObjectProperty($obj, 'pdfuaStructLog', []);

        $out = $obj->exposeGetOutStructTreeRoot();

        $this->assertSame('', $out);

        // OID tracking must be reset.
        $this->assertSame(0, $this->getObjectProperty($obj, 'parenttreeoid'));
        $this->assertSame(0, $this->getObjectProperty($obj, 'structtreerootoid'));
    }

    // -------------------------------------------------------------------------
    // getOutAnnotationOptSubtypeRedact: /RO branch
    // -------------------------------------------------------------------------

    public function testGetOutAnnotationOptSubtypeRedactIncludesRoField(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetOutAnnotationOptSubtypeRedact([
            'n' => 50,
            'opt' => [
                'ro' => '(Redacted-XMP)',
            ],
        ]);

        $this->assertStringContainsString(' /RO ', $out);
    }

    // -------------------------------------------------------------------------
    // getOutAnnotationOptSubtypeWatermark: empty fixedprint returns ''
    // -------------------------------------------------------------------------

    public function testGetOutAnnotationOptSubtypeWatermarkReturnsEmptyWhenFixedprintProducesNoOutput(): void
    {
        $obj = $this->getInternalTestObject();

        // fixedprint array present but none of its keys have valid values.
        $out = $obj->exposeGetOutAnnotationOptSubtypeWatermark([
            'opt' => [
                'fixedprint' => [
                    'type' => 123,
                    'h' => 'not-numeric',
                    'v' => [],
                ],
            ],
        ]);

        $this->assertSame('', $out);
    }

    // -------------------------------------------------------------------------
    // getOutAnnotationRectDifferences via exposeGetOutAnnotationRD
    // -------------------------------------------------------------------------

    public function testGetOutAnnotationRectDifferencesWithValidRdArray(): void
    {
        $obj = $this->getInternalTestObject();

        $out = $obj->exposeGetOutAnnotationRD([
            'opt' => ['rd' => [1.0, 2.0, 3.0, 4.0]],
        ]);

        $this->assertStringContainsString(' /RD [', $out);
    }

    public function testGetOutAnnotationRectDifferencesReturnsEmptyForInvalidRd(): void
    {
        $obj = $this->getInternalTestObject();

        // Wrong count.
        $this->assertSame('', $obj->exposeGetOutAnnotationRD([
            'opt' => ['rd' => [1.0, 2.0]],
        ]));

        // Non-numeric value.
        $this->assertSame('', $obj->exposeGetOutAnnotationRD([
            'opt' => ['rd' => [1.0, 2.0, 'x', 4.0]],
        ]));

        // Missing rd key.
        $this->assertSame('', $obj->exposeGetOutAnnotationRD(['opt' => []]));
    }

    // -------------------------------------------------------------------------
    // collectValidationMaterial: empty pemInputs path (lines 4162, 4406)
    // -------------------------------------------------------------------------

    public function testCollectValidationMaterialReturnsEmptyWhenSigncertIsEmpty(): void
    {
        $obj = $this->getInternalTestObject();

        // ltv.enabled is truthy but signcert is '' → getCertificateSourceContent('') returns ''
        // → collectValidationCertificateInputs returns [] → line 4162 triggered.
        $this->setObjectProperty($obj, 'signature', [
            'signcert' => '',
            'extracerts' => '',
            'privkey' => null,
            'password' => '',
            'ltv' => ['enabled' => true],
        ]);

        $material = $obj->exposeCollectValidationMaterial();

        $this->assertSame([], $material['cert_chain']);
        $this->assertSame([], $material['certs']);
        $this->assertSame([], $material['ocsp']);
        $this->assertSame([], $material['crls']);
        $this->assertSame([], $material['vri']);
    }

    // -------------------------------------------------------------------------
    // setPageStructParents: skip page without 'n' key (line 1796)
    // -------------------------------------------------------------------------

    public function testSetPageStructParentsSkipsPagesWithoutObjectNumber(): void
    {
        $obj = $this->getInternalTestObject();

        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($obj, 'page');

        // Add a real page and then inject a fake one missing 'n'.
        $pageData = $pageObj->add([]);
        /** @var array<int, array<string, mixed>> $pages */
        $pages = $this->getObjectProperty($pageObj, 'page');
        $pages[$pageData['pid']]['n'] = 99;
        // Add a fake entry without 'n' key.
        $pages[9999] = ['pid' => 9999, 'content' => []];
        $this->setObjectProperty($pageObj, 'page', $pages);

        $obj->exposeSetPageStructParents('');

        // Only the real page (with 'n' = 99) should appear in pagestructparents.
        /** @var array<int, int> $structParents */
        $structParents = $this->getObjectProperty($obj, 'pagestructparents');
        $this->assertArrayHasKey(99, $structParents);
        $this->assertArrayNotHasKey(9999, $structParents);
    }

    public function testBuildTimestampRequestWithPolicyOidAndNonceEnabled(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'sigtimestamp', [
            'enabled' => true,
            'host' => 'https://tsa.example.test',
            'username' => '',
            'password' => '',
            'cert' => '',
            'hash_algorithm' => 'sha256',
            'policy_oid' => '1.2.3.4.5',
            'nonce_enabled' => true,
            'timeout' => 5,
            'verify_peer' => true,
        ]);

        $request = $obj->exposeBuildTimestampRequest('sigbin');

        // Result must be a valid DER SEQUENCE.
        $this->assertStringStartsWith("\x30", $request);
        // SHA-256 OID must be present.
        $this->assertStringContainsString('608648016503040201', \bin2hex($request));
    }

    public function testAsn1EncodeLengthShortForm(): void
    {
        $obj = $this->getInternalTestObject();

        // Length < 128 → single byte.
        $encoded = $obj->exposeAsn1EncodeLength(127);
        $this->assertSame("\x7f", $encoded);
    }

    public function testAsn1EncodeLengthLongForm(): void
    {
        $obj = $this->getInternalTestObject();

        // Length >= 128 → long form encoding.
        $encoded = $obj->exposeAsn1EncodeLength(256);
        // Expect 0x82 (2 length bytes) + 0x01 + 0x00.
        $this->assertSame("\x82\x01\x00", $encoded);
    }

    public function testAsn1EncodeIntegerZeroProducesZeroByte(): void
    {
        $obj = $this->getInternalTestObject();

        $encoded = $obj->exposeAsn1EncodeInteger(0);
        // Tag 0x02, length 0x01, value 0x00.
        $this->assertSame("\x02\x01\x00", $encoded);
    }

    public function testAsn1EncodeIntegerHighBitSetPrependsPaddingByte(): void
    {
        $obj = $this->getInternalTestObject();

        // 0x80 = 128: high bit is set, so a 0x00 padding byte must be prepended.
        $encoded = $obj->exposeAsn1EncodeInteger(0x80);
        // Tag 0x02, length 0x02, padding 0x00, value 0x80.
        $this->assertSame("\x02\x02\x00\x80", $encoded);
    }

    public function testPostTimestampRequestThrowsOnEmptyHost(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'sigtimestamp', [
            'enabled' => true,
            'host' => '',
            'username' => '',
            'password' => '',
            'cert' => '',
            'hash_algorithm' => 'sha256',
            'policy_oid' => '',
            'nonce_enabled' => false,
            'timeout' => 5,
            'verify_peer' => true,
        ]);
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->exposeParentPostTimestampRequest('req');
    }

    public function testGetCertificateSourceContentWithEmptySourceReturnsEmpty(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeGetCertificateSourceContent('');

        $this->assertSame('', $result);
    }

    public function testGetCertificateSourceContentWithInlinePemReturnsAsIs(): void
    {
        $obj = $this->getInternalTestObject();
        $pemContent = "-----BEGIN CERTIFICATE-----\nMIIBxxx\n-----END CERTIFICATE-----\n";

        $result = $obj->exposeGetCertificateSourceContent($pemContent);

        $this->assertSame($pemContent, $result);
    }

    public function testExtractPemCertificatesWithNoPemBlocksReturnsEmptyArray(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeExtractPemCertificates('no pem certificates here at all');

        $this->assertSame([], $result);
    }

    public function testGetPatternDictSkipsPatternWithoutNKey(): void
    {
        $obj = $this->getInternalTestObject();

        // Add two patterns: one with 'n' and one without.
        $this->setObjectProperty($obj, 'patterns', [
            'P1' => ['n' => 7],
            'P2' => [],
        ]);

        $result = $obj->exposeGetPatternDict();

        $this->assertStringContainsString('/P1 7 0 R', $result);
        $this->assertStringNotContainsString('/P2', $result);
    }

    public function testSavePdfThrowsOnInvalidDirectory(): void
    {
        $obj = $this->getInternalTestObject();
        $this->setObjectProperty($obj, 'pdffilename', 'test.pdf');
        $this->expectException(\Com\Tecnick\File\Exception::class);

        $obj->savePDF('/this/path/does/not/exist/at/all', '%PDF-1.4');
    }
}
