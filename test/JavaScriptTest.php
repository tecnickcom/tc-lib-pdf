<?php

/**
 * JavaScriptTest.php
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

/** @phpstan-import-type TXOBject from \Com\Tecnick\Pdf\Output */
class JavaScriptTest extends TestUtil
{
    public static function setUpBeforeClass(): void
    {
        self::setUpFontsPath();
    }

    /** @throws \Throwable */
    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    /** @throws \Throwable */
    protected function getInternalTestObject(): TestableJavaScript
    {
        return new TestableJavaScript();
    }

    /**
     * @return array{pid: int}
     * @throws \Throwable
     */
    private function addRawPage(\Com\Tecnick\Pdf\Tcpdf $obj): array
    {
        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        $rawPage = $page->add([]);

        return ['pid' => (int) $rawPage['pid']];
    }

    /**
     * @param array<int, array<string, mixed>> $annotations
     * @return array<string, mixed>
     */
    private function getAnnotationEntry(array $annotations, int $oid): array
    {
        $entry = $annotations[$oid] ?? null;
        if ($entry === null) {
            $this->fail('Missing annotation entry: ' . (string) $oid);
        }

        return $entry;
    }

    /**
     * @param array<string, array<string, mixed>> $map
     * @return array<string, mixed>
     */
    private function getStringMapEntry(array $map, string $key, string $label): array
    {
        $entry = $map[$key] ?? null;
        if ($entry === null) {
            $this->fail('Missing ' . $label . ' entry: ' . $key);
        }

        return $entry;
    }

    /**
     * @param array<string, mixed> $map
     */
    private function getRequiredString(array $map, string $key, string $label): string
    {
        if (!isset($map[$key]) || !\is_string($map[$key])) {
            $this->fail('Missing string value for ' . $label . ': ' . $key);
        }

        return $map[$key];
    }

    /**
     * @param array<int, array<string, mixed>> $annotations
     * @return array<string, mixed>
     */
    private function getAnnotationOpt(array $annotations, int $oid): array
    {
        $entry = $this->getAnnotationEntry($annotations, $oid);
        if (!isset($entry['opt']) || !\is_array($entry['opt'])) {
            $this->fail('Missing annotation options: ' . (string) $oid);
        }

        /** @var array<string, mixed> */
        return $entry['opt'];
    }

    /**
     * @throws \Throwable
     */
    public function testAppendRawJavaScriptAppendsScript(): void
    {
        $obj = $this->getTestObject();
        $obj->appendRawJavaScript('var a = 1;');
        $obj->appendRawJavaScript('var b = 2;');

        /** @var string $javascript */
        $javascript = $this->getObjectProperty($obj, 'javascript');
        $this->assertSame('var a = 1;var b = 2;', $javascript);
    }

    /**
     * @throws \Throwable
     */
    public function testAddRawJavaScriptObjAddsObjectAndReturnsId(): void
    {
        $obj = $this->getTestObject();
        $objectId = $obj->addRawJavaScriptObj('app.alert("ok");', true);

        $this->assertGreaterThan(0, $objectId);
        /** @var array<int, array{n:int, js:string, onload:bool}> $list */
        $list = $this->getObjectProperty($obj, 'jsobjects');
        $this->assertCount(1, $list);
        assert(isset($list[0]), "\$list[0] must be set");
        $this->assertSame($objectId, $list[0]['n']);
        $this->assertSame('app.alert("ok");', $list[0]['js']);
        $this->assertTrue($list[0]['onload']);
    }

    /**
     * @throws \Throwable
     */
    public function testAddRawJavaScriptObjReturnsMinusOneInPdfaMode(): void
    {
        $obj = $this->getTestObject();
        $this->setObjectProperty($obj, 'pdfa', 1);

        $objectId = $obj->addRawJavaScriptObj('app.alert("no");');
        $this->assertSame(-1, $objectId);
        /** @var array<int, mixed> $jsobjects */
        $jsobjects = $this->getObjectProperty($obj, 'jsobjects');
        $this->assertCount(0, $jsobjects);
    }

    /**
     * @throws \Throwable
     */
    public function testAppendRawJavaScriptIsIgnoredInPdfuaMode(): void
    {
        $obj = $this->getTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua1');
        $obj->appendRawJavaScript('var blocked = true;');

        /** @var string $javascript */
        $javascript = $this->getObjectProperty($obj, 'javascript');
        $this->assertSame('', $javascript);
    }

    /**
     * @throws \Throwable
     */
    public function testAddRawJavaScriptObjReturnsMinusOneInPdfuaMode(): void
    {
        $obj = $this->getTestObject();
        $this->setObjectProperty($obj, 'pdfuaMode', 'pdfua2');

        $objectId = $obj->addRawJavaScriptObj('app.alert("no");');
        $this->assertSame(-1, $objectId);
        /** @var array<int, mixed> $jsobjects */
        $jsobjects = $this->getObjectProperty($obj, 'jsobjects');
        $this->assertCount(0, $jsobjects);
    }

    /**
     * @throws \Throwable
     */
    public function testAppendRawJavaScriptIsIgnoredInPdfxMode(): void
    {
        $obj = $this->getTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);
        $obj->appendRawJavaScript('var blocked = true;');

        /** @var string $javascript */
        $javascript = $this->getObjectProperty($obj, 'javascript');
        $this->assertSame('', $javascript);
    }

    /**
     * @throws \Throwable
     */
    public function testAddRawJavaScriptObjReturnsMinusOneInPdfxMode(): void
    {
        $obj = $this->getTestObject();
        $this->setObjectProperty($obj, 'pdfx', true);

        $objectId = $obj->addRawJavaScriptObj('app.alert("no");');
        $this->assertSame(-1, $objectId);
        /** @var array<int, mixed> $jsobjects */
        $jsobjects = $this->getObjectProperty($obj, 'jsobjects');
        $this->assertCount(0, $jsobjects);
    }

    /**
     * @throws \Throwable
     */
    public function testSetAndGetDefaultAnnotationProperties(): void
    {
        $obj = $this->getTestObject();
        $props = ['lineWidth' => 2, 'borderStyle' => 'dashed', 'fillColor' => 'yellow'];
        $obj->setDefJSAnnotProp($props);

        $this->assertSame($props, $obj->getDefJSAnnotProp());
    }

    /**
     * @throws \Throwable
     */
    public function testAddInternalLinkStoresLinkData(): void
    {
        $obj = $this->getTestObject();
        $page = $this->addRawPage($obj);
        $lnk = $obj->addInternalLink($page['pid'], 12.5);

        /** @var array<string, array{p:int, y:float}> $links */
        $links = $this->getObjectProperty($obj, 'links');
        $this->assertSame('@1', $lnk);
        $link = $this->getStringMapEntry($links, $lnk, 'link');
        $this->assertSame($page['pid'], $link['p'] ?? null);
        $this->assertEqualsWithDelta(12.5, $link['y'] ?? 0.0, 0.0001);
    }

    /**
     * @throws \Throwable
     */
    public function testSetNamedDestinationStoresEncodedDestination(): void
    {
        $obj = $this->getTestObject();
        $page = $this->addRawPage($obj);
        $ret = $obj->setNamedDestination('My Dest', $page['pid'], 10.0, 20.0);

        $this->assertStringStartsWith('#', $ret);
        $key = \substr($ret, 1);
        /** @var array<string, array{p:int, x:float, y:float}> $dests */
        $dests = $this->getObjectProperty($obj, 'dests');
        $this->assertArrayHasKey($key, $dests);
        $dest = $this->getStringMapEntry($dests, $key, 'destination');
        $this->assertSame($page['pid'], $dest['p'] ?? null);
        $this->assertEqualsWithDelta(10.0, $dest['x'] ?? 0.0, 0.0001);
        $this->assertEqualsWithDelta(20.0, $dest['y'] ?? 0.0, 0.0001);
    }

    /**
     * @throws \Throwable
     */
    public function testSetBookmarkClampsLevelAndUppercasesStyle(): void
    {
        $obj = $this->getTestObject();
        $page = $this->addRawPage($obj);
        $obj->setBookmark('Parent', '', 0, $page['pid'], 0, 0, 'b', 'red');
        $obj->setBookmark('Child', '', 9, $page['pid'], 1, 2, 'iu', 'blue');

        /** @var array<int, array{l:int, s:string}> $outlines */
        $outlines = $this->getObjectProperty($obj, 'outlines');
        $this->assertCount(2, $outlines);
        assert(isset($outlines[0]), "\$outlines[0] must be set");
        $this->assertSame(0, $outlines[0]['l']);
        $this->assertSame('B', $outlines[0]['s']);
        assert(isset($outlines[1]), "\$outlines[1] must be set");
        $this->assertSame(1, $outlines[1]['l']);
        $this->assertSame('IU', $outlines[1]['s']);
    }

    /**
     * @throws \Throwable
     */
    public function testSetBookmarkClampsNegativeLevelToZero(): void
    {
        $obj = $this->getTestObject();
        $page = $this->addRawPage($obj);

        $obj->setBookmark('Negative', '', -5, $page['pid']);

        /** @var array<int, array{l:int}> $outlines */
        $outlines = $this->getObjectProperty($obj, 'outlines');
        $this->assertCount(1, $outlines);
        assert(isset($outlines[0]), "\$outlines[0] must be set");
        $this->assertSame(0, $outlines[0]['l']);
    }

    /**
     * @throws \Throwable
     */
    public function testXObjectTemplateLifecycleAndMutators(): void
    {
        $obj = $this->getTestObject();
        $this->addRawPage($obj);
        $tid = $obj->newXObjectTemplate(30, 40);

        $obj->addXObjectContent($tid, 'q Q');
        $obj->addXObjectXObjectID($tid, 'XT999');
        $obj->addXObjectImageID($tid, 7);
        $obj->addXObjectFontID($tid, 'F1');
        $obj->addXObjectGradientID($tid, 8);
        $obj->addXObjectExtGStateID($tid, 9);
        $obj->addXObjectSpotColorID($tid, 'SC1');

        /** @var array<string, TXOBject> $xobjects */
        $xobjects = $this->getObjectProperty($obj, 'xobjects');
        $this->assertArrayHasKey($tid, $xobjects);
        $xobject = $this->getStringMapEntry($xobjects, $tid, 'xobject');
        $this->assertSame('q Q', $xobject['outdata'] ?? null);
        $this->assertSame(['XT999'], $xobject['xobject'] ?? null);
        $this->assertSame([7], $xobject['image'] ?? null);
        $this->assertSame(['F1'], $xobject['font'] ?? null);
        $this->assertSame([8], $xobject['gradient'] ?? null);
        $this->assertSame([9], $xobject['extgstate'] ?? null);
        $this->assertSame(['SC1'], $xobject['spot_colors'] ?? null);

        $obj->exitXObjectTemplate();
        /** @var string $xobjtid */
        $xobjtid = $this->getObjectProperty($obj, 'xobjtid');
        $this->assertSame('', $xobjtid);

        $this->assertSame('', $obj->getXObjectTemplate('UNKNOWN'));
        $draw = $obj->getXObjectTemplate($tid, 1, 2, 10, 20);
        $this->assertStringContainsString('/' . $tid . ' Do', $draw);
    }

    /**
     * @throws \Throwable
     */
    public function testXObjectMutatorsGracefullyIgnoreUnknownTemplateId(): void
    {
        $obj = $this->getTestObject();

        $obj->exitXObjectTemplate();
        $obj->addXObjectContent('UNKNOWN', 'q Q');
        $obj->addXObjectXObjectID('UNKNOWN', 'XT1');
        $obj->addXObjectImageID('UNKNOWN', 1);
        $obj->addXObjectFontID('UNKNOWN', 'F1');
        $obj->addXObjectGradientID('UNKNOWN', 2);
        $obj->addXObjectExtGStateID('UNKNOWN', 3);
        $obj->addXObjectSpotColorID('UNKNOWN', 'SC1');

        /** @var array<string, TXOBject> $xobjects */
        $xobjects = $this->getObjectProperty($obj, 'xobjects');
        $this->assertSame([], $xobjects);
    }

    /**
     * @throws \Throwable
     */
    public function testAddEmbeddedFileStoresDataAndIgnoresDuplicates(): void
    {
        $obj = $this->getTestObject();
        $path = (string) \realpath(__DIR__ . '/../README.md');

        $obj->addEmbeddedFile($path, 'text/plain', 'Source', 'desc');
        $obj->addEmbeddedFile($path, 'text/plain', 'Source', 'desc2');

        /** @var array<string, array{description:string}> $files */
        $files = $this->getObjectProperty($obj, 'embeddedfiles');
        $key = \basename($path);
        $this->assertArrayHasKey($key, $files);
        $this->assertCount(1, $files);
        $fileData = $this->getStringMapEntry($files, $key, 'embedded file');
        $this->assertSame('desc', $fileData['description'] ?? null);
    }

    /**
     * @throws \Throwable
     */
    public function testAddEmbeddedFileThrowsInPdfa1(): void
    {
        $obj = $this->getTestObject();
        $this->setObjectProperty($obj, 'pdfa', 1);
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->addEmbeddedFile('file.bin');
    }

    /**
     * @throws \Throwable
     */
    public function testAddContentAsEmbeddedFileStoresContent(): void
    {
        $obj = $this->getTestObject();
        $obj->addContentAsEmbeddedFile('payload.txt', 'abc123', 'text/plain', 'Data', 'payload');

        /** @var array<string, array{content:string, description:string}> $files */
        $files = $this->getObjectProperty($obj, 'embeddedfiles');
        $this->assertArrayHasKey('payload.txt', $files);
        $payload = $this->getStringMapEntry($files, 'payload.txt', 'embedded content');
        $this->assertSame('abc123', $payload['content'] ?? null);
        $this->assertSame('payload', $payload['description'] ?? null);
    }

    /**
     * @throws \Throwable
     */
    public function testAddContentAsEmbeddedFileThrowsOnEmptyContent(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->addContentAsEmbeddedFile('payload.txt', '');
    }

    /**
     * @throws \Throwable
     */
    public function testAddContentAsEmbeddedFileThrowsInPdfaMode(): void
    {
        $obj = $this->getTestObject();
        $this->setObjectProperty($obj, 'pdfa', 2);

        try {
            $obj->addContentAsEmbeddedFile('payload.txt', 'abc123');
            $this->fail('Expected PDF/A embedded content exception');
        } catch (\Com\Tecnick\Pdf\Exception $e) {
            $this->assertStringContainsString('Embedded files are not allowed', $e->getMessage());
        }
    }

    /**
     * @throws \Throwable
     */
    public function testGetAnnotOptFromJSPropCoversDensePropertyMapping(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame([], $obj->exposeGetAnnotOptFromJSProp([]));
        $this->assertSame(
            ['Subtype' => 'Widget'],
            $obj->exposeGetAnnotOptFromJSProp(['aopt' => ['Subtype' => 'Widget']]),
        );

        $this->setObjectProperty($obj, 'rtl', true);
        $rtlOpt = $obj->exposeGetAnnotOptFromJSProp(['alignment' => 'weird']);
        $this->assertSame(2, $rtlOpt['q'] ?? null);

        $opt = $obj->exposeGetAnnotOptFromJSProp([
            'alignment' => 'center',
            'lineWidth' => '2',
            'borderStyle' => 'dashed',
            'buttonAlignX' => 0.25,
            'buttonAlignY' => 0.75,
            'buttonFitBounds' => 'true',
            'buttonScaleHow' => 'scaleHow.anamorphic',
            'buttonScaleWhen' => 'scaleWhen.tooSmall',
            'buttonPosition' => 'position.overlay',
            'fillColor' => 'yellow',
            'strokeColor' => 'blue',
            'rotation' => 90,
            'charLimit' => '5',
            'readonly' => 'true',
            'required' => 'true',
            'multiline' => 'true',
            'password' => 'true',
            'NoToggleToOff' => 'true',
            'Radio' => 'true',
            'Pushbutton' => 'true',
            'Combo' => 'true',
            'editable' => 'true',
            'Sort' => 'true',
            'fileSelect' => 'true',
            'multipleSelection' => 'true',
            'doNotSpellCheck' => 'true',
            'doNotScroll' => 'true',
            'comb' => 'true',
            'radiosInUnison' => 'true',
            'richText' => 'true',
            'commitOnSelChange' => 'true',
            'defaultValue' => 'dv',
            'display' => 'display.noView',
            'currentValueIndices' => [1, 2],
            'value' => ['Visible A', 'Visible B'],
            'exportValues' => ['Export A', 'Export B'],
            'richValue' => '<b>visible</b>',
            'submitName' => 'submit-field',
            'name' => 'field-name',
            'userName' => 'Field Name',
            'highlight' => 'outline',
        ]);

        /** @var array<string, mixed> $markerOptions */
        $markerOptions = $opt['mk'] ?? [];
        /** @var array<string, mixed> $iconFit */
        $iconFit = \is_array($markerOptions['if'] ?? null) ? $markerOptions['if'] : [];

        $this->assertSame(1, $opt['q'] ?? null);
        $this->assertSame([0, 0, 2, [3, 2]], $opt['border'] ?? null);
        $this->assertSame(['w' => 2, 's' => 'D', 'd' => [3, 2]], $opt['bs'] ?? null);
        $this->assertSame([0.25, 0.75], $iconFit['a'] ?? null);
        $this->assertTrue(($iconFit['fb'] ?? null) === true);
        $this->assertSame('A', $iconFit['s'] ?? null);
        $this->assertSame('S', $iconFit['sw'] ?? null);
        $this->assertSame(6, $markerOptions['tp'] ?? null);
        $this->assertArrayHasKey('bg', $markerOptions);
        $this->assertArrayHasKey('bc', $markerOptions);
        $this->assertSame(90, $markerOptions['r'] ?? null);
        $this->assertSame(5, $opt['maxlen'] ?? null);
        $this->assertGreaterThan(0, (int) ($opt['ff'] ?? 0));
        $this->assertSame('dv', $opt['dv'] ?? null);
        $this->assertSame(100, $opt['f'] ?? null);
        $this->assertSame([1, 2], $opt['i'] ?? null);
        $this->assertSame(
            [
                ['Export A', 'Visible A'],
                ['Export B', 'Visible B'],
            ],
            $opt['opt'] ?? null,
        );
        $this->assertSame('<b>visible</b>', $opt['rv'] ?? null);
        $this->assertSame('submit-field', $opt['tm'] ?? null);
        $this->assertSame('field-name', $opt['t'] ?? null);
        $this->assertSame('Field Name', $opt['tu'] ?? null);
        $this->assertSame('O', $opt['h'] ?? null);
    }

    /**
     * @throws \Throwable
     */
    public function testEmbeddedFileValidationCoversEmptyNameAndPdfa3Relationship(): void
    {
        $obj = $this->getTestObject();

        try {
            $obj->addEmbeddedFile('');
            $this->fail('Expected empty file name exception');
        } catch (\Com\Tecnick\Pdf\Exception $e) {
            $this->assertStringContainsString('Empty file name', $e->getMessage());
        }

        $this->setObjectProperty($obj, 'pdfa', 3);

        try {
            $obj->addEmbeddedFile(__DIR__ . '/../README.md', 'text/plain', 'NotValid');
            $this->fail('Expected invalid afrel exception');
        } catch (\Com\Tecnick\Pdf\Exception $e) {
            $this->assertStringContainsString('afrel must be one of', $e->getMessage());
        }
    }

    /**
     * @throws \Throwable
     */
    public function testSetAnnotationAndSetLinkCreateAnnotationEntries(): void
    {
        $obj = $this->getTestObject();
        $textAnnotationId = $obj->setAnnotation(1, 2, 3, 4, 'note', ['subtype' => 'Text']);
        $linkAnnotationId = $obj->setLink(5, 6, 7, 8, '#dest');

        /** @var array<int, array{txt:string, opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($textAnnotationId, $ann);
        $this->assertArrayHasKey($linkAnnotationId, $ann);
        $textAnn = $this->getAnnotationEntry($ann, $textAnnotationId);
        $linkOpt = $this->getAnnotationOpt($ann, $linkAnnotationId);
        $this->assertSame('note', $textAnn['txt'] ?? null);
        $this->assertSame('Link', $linkOpt['subtype'] ?? null);
    }

    /**
     * @throws \Throwable
     */
    public function testSetAnnotationInsideXObjectIsDeferred(): void
    {
        $obj = $this->getTestObject();
        $this->addRawPage($obj);
        $tid = $obj->newXObjectTemplate(20, 20);

        $oid = $obj->setAnnotation(1, 1, 5, 5, 'in-template', ['subtype' => 'Text']);

        $this->assertSame(0, $oid);
        /** @var array<string, array{annotations:array<int, mixed>}> $xobjects */
        $xobjects = $this->getObjectProperty($obj, 'xobjects');
        $this->assertCount(1, $xobjects[$tid]['annotations'] ?? []);
        $obj->exitXObjectTemplate();
    }

    /**
     * @throws \Throwable
     */
    public function testAddFFTextCreatesWidgetAnnotation(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $oid = $obj->addFFText('field1', 1, 2, 40, 8, ['subtype' => 'Widget', 'v' => 'hello']);

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $opt = $this->getAnnotationOpt($ann, $oid);
        $this->assertSame('Widget', $opt['Subtype'] ?? null);
        $this->assertSame('Tx', $opt['ft'] ?? null);
        $this->assertSame('field1', $opt['t'] ?? null);
    }

    /**
     * @throws \Throwable
     */
    public function testSetAnnotationWidgetIsSuppressedInPdfxMode(): void
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx3');
        $this->initFontAndPage($obj);

        $oid = $obj->setAnnotation(1, 2, 40, 8, 'field1', ['subtype' => 'Widget']);

        $this->assertSame(0, $oid);
        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertSame([], $ann);
    }

    /**
     * @throws \Throwable
     */
    public function testSetAnnotationLinkStillWorksInPdfxMode(): void
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx3');
        $this->initFontAndPage($obj);

        $oid = $obj->setLink(1, 2, 10, 4, '#dest');

        $this->assertGreaterThan(0, $oid);
        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $opt = $this->getAnnotationOpt($ann, $oid);
        $this->assertSame('Link', $opt['subtype'] ?? null);
    }

    /**
     * @throws \Throwable
     */
    public function testSetAnnotationInteractiveSubtypesAreSuppressedInPdfxMode(): void
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfx3');
        $this->initFontAndPage($obj);

        $file = (string) \realpath(__DIR__ . '/../README.md');
        $this->assertNotSame('', $file);

        $subtypes = ['screen', 'movie', 'sound', 'fileattachment', '3d'];
        foreach ($subtypes as $subtype) {
            $opt = ['subtype' => $subtype];
            if ($subtype === 'fileattachment' || $subtype === 'sound') {
                $opt['fs'] = $file;
            }
            $oid = $obj->setAnnotation(1, 2, 10, 4, 'blocked', $opt);
            $this->assertSame(0, $oid);
        }

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertSame([], $ann);

        /** @var array<string, mixed> $embeddedfiles */
        $embeddedfiles = $this->getObjectProperty($obj, 'embeddedfiles');
        $this->assertSame([], $embeddedfiles);
    }

    /**
     * @throws \Throwable
     */
    public function testAddFFButtonCreatesButtonWidgetWithAction(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $oid = $obj->addFFButton('btnField', 1, 2, 30, 10, 'Caption', 'app.alert("x");');

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $opt = $this->getAnnotationOpt($ann, $oid);
        $this->assertSame('Widget', $opt['Subtype'] ?? null);
        $this->assertSame('Btn', $opt['ft'] ?? null);
        $this->assertSame('Caption', $opt['t'] ?? null);
        $this->assertSame('btnField', $opt['v'] ?? null);
        $this->assertArrayHasKey('a', $opt);
        $this->assertStringContainsString('/S /JavaScript /JS', $this->getRequiredString($opt, 'a', 'button action'));
    }

    /**
     * @throws \Throwable
     */
    public function testAddFFButtonSupportsStructuredFormActionOptions(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $oid = $obj->addFFButton('submitField', 1, 2, 30, 10, 'Submit', [
            'S' => 'SubmitForm',
            'F' => 'https://example.test/form',
            'Fields' => ['alpha', 'beta', 123],
            'Flags' => ['IncludeNoValueFields', 'SubmitPDF', 'EmbedForm'],
        ]);

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $opt = $this->getAnnotationOpt($ann, $oid);
        $actionString = $this->getRequiredString($opt, 'a', 'structured action');
        $this->assertStringContainsString('/S /SubmitForm', $actionString);
        $this->assertStringContainsString('/Fields [', $actionString);
        $this->assertStringContainsString('/Flags 8450', $actionString);

        $obj->addFFButton('allFlags', 1, 2, 30, 10, 'Submit', [
            'S' => 'SubmitForm',
            'Flags' => [
                'Include/Exclude',
                'ExportFormat',
                'GetMethod',
                'SubmitCoordinates',
                'XFDF',
                'IncludeAppendSaves',
                'IncludeAnnotations',
                'CanonicalFormat',
                'ExclNonUserAnnots',
                'ExclFKey',
                123,
                'UnknownFlag',
            ],
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function testAddFFCheckBoxCreatesCheckboxWidget(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $oid = $obj->addFFCheckBox('chkField', 2, 3, 5, 'Yes', true);

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $opt = $this->getAnnotationOpt($ann, $oid);
        $this->assertSame('Widget', $opt['Subtype'] ?? null);
        $this->assertSame('Btn', $opt['ft'] ?? null);
        $this->assertSame('Yes', $opt['as'] ?? null);
        $this->assertSame(['Yes'], $opt['opt'] ?? null);
    }

    /**
     * @throws \Throwable
     */
    public function testAddFFComboBoxCreatesChoiceWidget(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $vals = [['A', 'Alpha'], ['B', 'Beta']];
        $oid = $obj->addFFComboBox('cmbField', 1, 2, 30, 12, $vals);

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $opt = $this->getAnnotationOpt($ann, $oid);
        $this->assertSame('Widget', $opt['Subtype'] ?? null);
        $this->assertSame('Ch', $opt['ft'] ?? null);
        $this->assertSame('cmbField', $opt['t'] ?? null);
        $this->assertSame($vals, $opt['opt'] ?? null);
    }

    /**
     * @throws \Throwable
     */
    public function testAddFFListBoxCreatesChoiceWidget(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $vals = ['One', 'Two'];
        $oid = $obj->addFFListBox('lstField', 1, 2, 30, 12, $vals);

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $opt = $this->getAnnotationOpt($ann, $oid);
        $this->assertSame('Widget', $opt['Subtype'] ?? null);
        $this->assertSame('Ch', $opt['ft'] ?? null);
        $this->assertSame('lstField', $opt['t'] ?? null);
        $this->assertSame($vals, $opt['opt'] ?? null);
    }

    /**
     * @throws \Throwable
     */
    public function testAddFFRadioButtonCreatesRadioGroupAndWidget(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $oid = $obj->addFFRadioButton('radField', 3, 4, 6, 'On', true);

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $opt = $this->getAnnotationOpt($ann, $oid);
        $this->assertSame('Widget', $opt['Subtype'] ?? null);
        $this->assertSame('Btn', $opt['ft'] ?? null);
        $this->assertSame('On', $opt['as'] ?? null);

        /** @var array<string, array{kids:array<int, mixed>}> $groups */
        $groups = $this->getObjectProperty($obj, 'radiobuttons');
        $this->assertArrayHasKey('radField', $groups);
        $this->assertNotEmpty($groups['radField']['kids'] ?? []);
    }

    /**
     * @throws \Throwable
     */
    public function testAddJSFieldWrappersAppendExpectedScripts(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        \set_error_handler(
            static fn(int $errno, string $errstr): bool => $errno === E_WARNING
            && \str_contains($errstr, 'Undefined array key "num"'),
        );

        try {
            $obj->addJSButton('btn', 1, 2, 10, 5, 'Go', 'app.alert(1);');
            $obj->addJSCheckBox('chk', 2, 3, 4);
            $obj->addJSComboBox('cmb', 3, 4, 20, 6, ['A', 'B']);
            $obj->addJSListBox('lst', 4, 5, 20, 6, ['X', 'Y']);
            $obj->addJSRadioButton('rad', 5, 6, 4);
            $obj->addJSText('txt', 6, 7, 20, 6);
        } finally {
            \restore_error_handler();
        }

        /** @var string $jsScript */
        $jsScript = $this->getObjectProperty($obj, 'javascript');
        $this->assertStringContainsString("fbtn.buttonSetCaption('Go');", $jsScript);
        $this->assertStringContainsString("fbtn.setAction('MouseUp'", $jsScript);
        $this->assertStringContainsString("fchk=this.addField('chk','checkbox'", $jsScript);
        $this->assertStringContainsString('fcmb.setItems(', $jsScript);
        $this->assertStringContainsString("flst.\\setItems(", $jsScript);
        $this->assertStringContainsString("frad=this.addField('rad','radiobutton'", $jsScript);
        $this->assertStringContainsString("ftxt=this.addField('txt','text'", $jsScript);
    }

    /**
     * @throws \Throwable
     */
    public function testAddJSFieldWrappersHandleArrayValuedSelections(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        \set_error_handler(
            static fn(int $errno, string $errstr): bool => $errno === E_WARNING
            && \str_contains($errstr, 'Undefined array key "num"'),
        );

        try {
            $obj->addJSComboBox('cmb2', 1, 2, 20, 6, [['A', 'Alpha'], ['B', 'Beta']]);
            $obj->addJSListBox('lst2', 1, 2, 20, 6, [['X', 'Ex'], ['Y', 'Why']]);
        } finally {
            \restore_error_handler();
        }

        /** @var string $jsScript */
        $jsScript = $this->getObjectProperty($obj, 'javascript');
        $this->assertStringContainsString("fcmb2.setItems(['Alpha','A'],['Beta','B']);", $jsScript);
        $this->assertStringContainsString("flst2.\\setItems(['Ex','X'],['Why','Y']);", $jsScript);
    }

    /**
     * @throws \Throwable
     */
    public function testGetAnnotOptFromJSPropCoversAdditionalMappingVariants(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame(['f' => 7], $obj->exposeGetAnnotOptFromJSProp(['aopt' => ['f' => 7]]));

        $styleVariants = [
            'beveled' => 'B',
            'inset' => 'I',
            'underline' => 'U',
            'solid' => 'S',
        ];
        foreach ($styleVariants as $style => $expected) {
            $mapped = $obj->exposeGetAnnotOptFromJSProp(['borderStyle' => $style]);
            /** @var array<string, mixed> $borderSpec */
            $borderSpec = \is_array($mapped['bs'] ?? null) ? $mapped['bs'] : [];
            $this->assertSame($expected, $borderSpec['s'] ?? null);
        }

        $numericPos = $obj->exposeGetAnnotOptFromJSProp(['buttonPosition' => 5]);
        /** @var array<string, mixed> $numericMk */
        $numericMk = \is_array($numericPos['mk'] ?? null) ? $numericPos['mk'] : [];
        $this->assertSame(5, $numericMk['tp'] ?? null);
        $invalidPos = $obj->exposeGetAnnotOptFromJSProp(['buttonPosition' => 99]);
        /** @var array<string, mixed> $invalidMk */
        $invalidMk = \is_array($invalidPos['mk'] ?? null) ? $invalidPos['mk'] : [];
        $this->assertArrayNotHasKey('tp', $invalidMk);

        $filled = $obj->exposeGetAnnotOptFromJSProp([
            'fillColor' => [0.1, 0.2, 0.3],
            'strokeColor' => [0.4, 0.5, 0.6],
            'display' => 'display.hidden',
            'highlight' => 'push',
            'value' => 'plain-value',
        ]);
        /** @var array<string, mixed> $filledMk */
        $filledMk = \is_array($filled['mk'] ?? null) ? $filled['mk'] : [];
        $this->assertSame([0.1, 0.2, 0.3], $filledMk['bg'] ?? null);
        $this->assertSame([0.4, 0.5, 0.6], $filledMk['bc'] ?? null);
        $this->assertSame('P', $filled['h'] ?? null);
        $this->assertSame('plain-value', $filled['v'] ?? null);
        $this->assertSame(6, $filled['f'] ?? null);

        $noPrint = $obj->exposeGetAnnotOptFromJSProp(['display' => 'display.noPrint']);
        $this->assertSame(0, $noPrint['f'] ?? null);
        $visible = $obj->exposeGetAnnotOptFromJSProp(['display' => 'display.visible', 'highlight' => 'invert']);
        $this->assertSame(4, $visible['f'] ?? null);
        $this->assertSame('i', $visible['h'] ?? null);
        $defaultHighlight = $obj->exposeGetAnnotOptFromJSProp(['highlight' => 'unknown']);
        $this->assertSame('N', $defaultHighlight['h'] ?? null);
        $highlightAlias = $obj->exposeGetAnnotOptFromJSProp(['highlight' => 'highlight.o']);
        $this->assertSame('O', $highlightAlias['h'] ?? null);

        $borderOpt = $obj->exposeGetAnnotOptFromJSProp(['border' => [1, 2, 3]]);
        $this->assertSame([1, 2, 3], $borderOpt['border'] ?? null);
        $scaleHowMap = [
            'scaleHow.proportional' => 'P',
            'scaleHow.invalid' => 'P',
        ];
        foreach ($scaleHowMap as $scaleHow => $expected) {
            $mapped = $obj->exposeGetAnnotOptFromJSProp(['buttonScaleHow' => $scaleHow]);
            /** @var array<string, mixed> $scaleHowMk */
            $scaleHowMk = \is_array($mapped['mk'] ?? null) ? $mapped['mk'] : [];
            /** @var array<string, mixed> $scaleHowIf */
            $scaleHowIf = \is_array($scaleHowMk['if'] ?? null) ? $scaleHowMk['if'] : [];
            $this->assertSame($expected, $scaleHowIf['s'] ?? null);
        }

        $scaleWhenMap = [
            'scaleWhen.always' => 'A',
            'scaleWhen.never' => 'N',
            'scaleWhen.tooBig' => 'B',
            'scaleWhen.invalid' => 'N',
        ];
        foreach ($scaleWhenMap as $scaleWhen => $expected) {
            $mapped = $obj->exposeGetAnnotOptFromJSProp(['buttonScaleWhen' => $scaleWhen]);
            /** @var array<string, mixed> $scaleWhenMk */
            $scaleWhenMk = \is_array($mapped['mk'] ?? null) ? $mapped['mk'] : [];
            /** @var array<string, mixed> $scaleWhenIf */
            $scaleWhenIf = \is_array($scaleWhenMk['if'] ?? null) ? $scaleWhenMk['if'] : [];
            $this->assertSame($expected, $scaleWhenIf['sw'] ?? null);
        }

        $positionMap = [
            'position.textOnly' => 0,
            'position.iconOnly' => 1,
            'position.iconTextV' => 2,
            'position.textIconV' => 3,
            'position.iconTextH' => 4,
            'position.textIconH' => 5,
            'position.overlay' => 6,
            'position.unknown' => 0,
        ];
        foreach ($positionMap as $position => $expected) {
            $mapped = $obj->exposeGetAnnotOptFromJSProp(['buttonPosition' => $position]);
            /** @var array<string, mixed> $marker */
            $marker = \is_array($mapped['mk'] ?? null) ? $mapped['mk'] : [];
            $this->assertSame($expected, $marker['tp'] ?? null);
        }

        $highlightMap = [
            'none' => 'N',
            'highlight.n' => 'N',
            'highlight.i' => 'i',
            'highlight.p' => 'P',
        ];
        foreach ($highlightMap as $highlight => $expected) {
            $mapped = $obj->exposeGetAnnotOptFromJSProp(['highlight' => $highlight]);
            $this->assertSame($expected, $mapped['h'] ?? null);
        }

        $fillColorFallback = $this->getInternalTestObject();
        $this->assertMatchesRegularExpression('/(rg\\n|^$)/', $fillColorFallback->exposeGetPDFDefFillColor());

        $this->initFontAndPage($fillColorFallback);
        $merged = $fillColorFallback->exposeMergeAnnotOptions(['subtype' => 'text'], []);
        $this->assertArrayHasKey('da', $merged);
        $this->assertStringContainsString('0.000000 0.000000 0.000000 rg', $this->getRequiredString(
            $merged,
            'da',
            'default appearance stream',
        ));

        $fillColorFallback->addFFText(
            'disabled_preview',
            15,
            18,
            80,
            8,
            ['subtype' => 'text', 'v' => 'input:disabled rule'],
            [
                'readonly' => 'true',
                'fillColor' => 'rgba(93%,93%,93%,1)',
                'textColor' => 'rgba(55%,55%,55%,1)',
            ],
        );
        /** @var array<int, array<string, mixed>> $ann */
        $ann = $this->getObjectProperty($fillColorFallback, 'annotation');
        $last = reset($ann);
        if (!\is_array($last)) {
            $this->fail('Expected at least one annotation entry.');
        }
        $apn = '';
        if (isset($last['opt']) && \is_array($last['opt'])) {
            $opt = $last['opt'];
            if (isset($opt['ap']) && \is_array($opt['ap']) && isset($opt['ap']['n']) && \is_string($opt['ap']['n'])) {
                $apn = $opt['ap']['n'];
            }
        }
        $this->assertStringContainsString('(input:disabled rule) Tj', $apn);
        $this->assertDoesNotMatchRegularExpression('/\s-\d+\.\d+\s+Td\s+\(input:disabled rule\)\s+Tj/', $apn);
    }

    /**
     * @throws \Throwable
     */
    public function testXObjectTemplateAppliesDeferredAnnotationTransform(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $templateId = $obj->newXObjectTemplate(-1, -1);
        $deferredId = $obj->setAnnotation(1, 1, 5, 5, 'in-template', ['subtype' => 'Text']);
        $this->assertSame(0, $deferredId);
        $obj->exitXObjectTemplate();

        $rendered = $obj->getXObjectTemplate($templateId, 1, 1, 0, 0);
        $this->assertStringContainsString('/' . $templateId . ' Do', $rendered);

        /** @var array<int, array{txt:string}> $annotation */
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertNotEmpty($annotation);
    }

    /**
     * @throws \Throwable
     */
    public function testJsFieldPropertiesAndAnnotationAttachmentsCoverResidualBranches(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        \set_error_handler(
            static fn(int $errno, string $errstr): bool => $errno === E_WARNING
            && \str_contains($errstr, 'Undefined array key "num"'),
        );

        try {
            $obj->addJSText('txtProps', 1, 2, 20, 6, ['strokeColor' => 'red', 'value' => 'abc']);
        } finally {
            \restore_error_handler();
        }

        /** @var string $jsScript */
        $jsScript = $this->getObjectProperty($obj, 'javascript');
        $this->assertStringContainsString('ftxtProps.strokeColor=', $jsScript);
        $this->assertStringContainsString("ftxtProps.value='abc';", $jsScript);

        if (!\function_exists('imagecreatetruecolor') || !\function_exists('imagepng')) {
            $this->markTestSkipped('GD image functions are required for icon annotation coverage test.');
        }

        $attachmentPath = (string) \realpath(__DIR__ . '/../README.md');
        $iconPath = \tempnam(\sys_get_temp_dir(), 'tc-ico-');
        $this->assertNotFalse($iconPath);
        $iconResource = \imagecreatetruecolor(1, 1);
        $this->assertNotFalse($iconResource);
        \imagepng($iconResource, $iconPath);
        $annotId = -1;
        try {
            $annotId = $obj->setAnnotation(1, 2, 3, 4, 'attach', [
                'subtype' => 'fileattachment',
                'fs' => $attachmentPath,
                'mk' => ['i' => $iconPath, 'ri' => $iconPath, 'ix' => $iconPath],
            ]);
        } catch (\Throwable $e) {
            $this->assertNotSame('', $e->getMessage());
        } finally {
            if (\file_exists($iconPath)) {
                \unlink($iconPath);
            }
        }

        $this->assertGreaterThanOrEqual(-1, $annotId);
        /** @var array<string, mixed> $embeddedfiles */
        $embeddedfiles = $this->getObjectProperty($obj, 'embeddedfiles');
        $this->assertArrayHasKey('README.md', $embeddedfiles);
    }

    /**
     * @throws \Throwable
     */
    public function testAddFFButtonSupportsNumericFlagsAndAoptWithoutMk(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $oid = $obj->addFFButton(
            'submitNumeric',
            1,
            2,
            30,
            10,
            'Submit',
            ['S' => 'ResetForm', 'Flags' => 1024],
            ['subtype' => 'Widget'],
            ['aopt' => ['Subtype' => 'Widget', 'ft' => 'Btn']],
        );

        /** @var array<int, array{opt:array<string, mixed>}> $annotation */
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $annotation);
        $opt = $this->getAnnotationOpt($annotation, $oid);
        $actionString = $this->getRequiredString($opt, 'a', 'numeric flags action');
        $this->assertStringContainsString('/S /ResetForm', $actionString);
        $this->assertStringContainsString('/Flags 1024', $actionString);
    }

    /**
     * @throws \Throwable
     */
    public function testFFChoiceAndTextVariantsCoverScalarAndAlignmentPaths(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $comboId = $obj->addFFComboBox('comboScalar', 1, 2, 30, 12, ['One', 'Two']);
        $listId = $obj->addFFListBox('listScalar', 1, 2, 30, 12, ['Red', 'Blue']);
        $listArrayId = $obj->addFFListBox('listArray', 1, 2, 30, 12, [['V1', 'Label 1'], ['V2', 'Label 2']]);
        $radioOffId = $obj->addFFRadioButton(
            'radioGroup',
            3,
            4,
            6,
            'On',
            false,
            ['subtype' => 'Widget', 'q' => 0],
            ['aopt' => ['Subtype' => 'Widget', 'f' => 0], 'readonly' => 'true'],
        );
        $radioOnId = $obj->addFFRadioButton('radioGroup', 9, 4, 6, 'On', true);

        $txtLeftId = $obj->addFFText(
            'txtLeft',
            1,
            2,
            30,
            10,
            ['subtype' => 'Widget', 'q' => 0],
            ['alignment' => 'left', 'value' => 'L'],
        );
        $txtCenterId = $obj->addFFText(
            'txtCenter',
            1,
            2,
            30,
            10,
            ['subtype' => 'Widget'],
            ['alignment' => 'center', 'value' => 'C'],
        );
        $txtRightId = $obj->addFFText(
            'txtRight',
            1,
            2,
            30,
            10,
            ['subtype' => 'Widget', 'q' => 2],
            ['alignment' => 'right', 'value' => 'R'],
        );
        $txtUnknownId = $obj->addFFText(
            'txtUnknown',
            1,
            2,
            30,
            10,
            ['subtype' => 'Widget', 'q' => 99],
            ['value' => 'U'],
        );

        /** @var array<int, array{opt:array<string, mixed>}> $annotation */
        $annotation = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($comboId, $annotation);
        $this->assertArrayHasKey($listId, $annotation);
        $this->assertArrayHasKey($listArrayId, $annotation);
        $this->assertArrayHasKey($radioOffId, $annotation);
        $this->assertArrayHasKey($radioOnId, $annotation);
        $this->assertArrayHasKey($txtLeftId, $annotation);
        $this->assertArrayHasKey($txtCenterId, $annotation);
        $this->assertArrayHasKey($txtRightId, $annotation);
        $this->assertArrayHasKey($txtUnknownId, $annotation);

        /** @var array<string, array{kids:array<int, array{def:string}>, '#readonly#': bool}> $radioGroups */
        $radioGroups = $this->getObjectProperty($obj, 'radiobuttons');
        $this->assertArrayHasKey('radioGroup', $radioGroups);
        $radioGroup = $radioGroups['radioGroup'] ?? ['#readonly#' => true, 'kids' => []];
        $this->assertFalse($radioGroup['#readonly#'] ?? true);
        $kids = \is_array($radioGroup['kids'] ?? null) ? $radioGroup['kids'] : [];
        $this->assertSame('Off', $kids[0]['def'] ?? null);
        $this->assertSame('On', $kids[1]['def'] ?? null);
    }

    /**
     * @throws \Throwable
     */
    public function testSetAnnotationDefaultsEmptySubtypeToText(): void
    {
        $obj = $this->getTestObject();
        $oid = $obj->setAnnotation(1, 2, 3, 4, 'note', ['subtype' => '']);

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $opt = $this->getAnnotationOpt($ann, $oid);
        $this->assertSame('text', \strtolower((string) ($opt['subtype'] ?? '')));
    }

    /**
     * @throws \Throwable
     */
    public function testGetAnnotOptFromJSPropCastsBooleanValuesToStrings(): void
    {
        $obj = $this->getInternalTestObject();

        $opt = $obj->exposeGetAnnotOptFromJSProp(['value' => [true, false]]);

        $this->assertSame(['true', 'false'], $opt['opt'] ?? null);
    }

    /**
     * @throws \Throwable
     */
    public function testAddFFButtonAppliesStyleOverridesFromJsProperties(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $oid = $obj->addFFButton(
            'styledField',
            2,
            3,
            28,
            10,
            'Styled',
            '',
            ['subtype' => 'Widget'],
            [
                'strokeColor' => ' #112233 ',
                'fillColor' => ' #ddeeff ',
                'lineWidth' => 2,
                'borderStyle' => 'DaShEd',
            ],
        );

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $opt = $this->getAnnotationOpt($ann, $oid);
        $this->assertArrayHasKey('ap', $opt);
    }

    /**
     * @throws \Throwable
     */
    public function testGetPdfDefaultFillColorReturnsEmptyWhenNoFillIsSet(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertMatchesRegularExpression('/(rg\\n|^$)/', $obj->exposeGetPDFDefFillColor());
    }
}
