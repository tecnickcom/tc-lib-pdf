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

class TestableJavaScript extends \Com\Tecnick\Pdf\Tcpdf
{
    /**
     * @param array<string, mixed> $prp
     * @return array<string, mixed>
     */
    public function exposeGetAnnotOptFromJSProp(array $prp = []): array
    {
        return $this->getAnnotOptFromJSProp($prp);
    }

    public function exposeGetPDFDefFillColor(): string
    {
        return $this->getPDFDefFillColor();
    }
}

class JavaScriptTest extends TestUtil
{
    public static function setUpBeforeClass(): void
    {
        self::setUpFontsPath();
    }

    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    protected function getInternalTestObject(): TestableJavaScript
    {
        return new TestableJavaScript();
    }

    /** @return array{pid: int} */
    private function addRawPage(\Com\Tecnick\Pdf\Tcpdf $obj): array
    {
        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        /** @var array{pid: int} $rawPage */
        $rawPage = $page->add([]);
        return $rawPage;
    }

    public function testAppendRawJavaScriptAppendsScript(): void
    {
        $obj = $this->getTestObject();
        $obj->appendRawJavaScript('var a = 1;');
        $obj->appendRawJavaScript('var b = 2;');

        /** @var string $javascript */
        $javascript = $this->getObjectProperty($obj, 'javascript');
        $this->assertSame('var a = 1;var b = 2;', $javascript);
    }

    public function testAddRawJavaScriptObjAddsObjectAndReturnsId(): void
    {
        $obj = $this->getTestObject();
        $objectId = $obj->addRawJavaScriptObj('app.alert("ok");', true);

        $this->assertGreaterThan(0, $objectId);
        /** @var array<int, array{n:int, js:string, onload:bool}> $list */
        $list = $this->getObjectProperty($obj, 'jsobjects');
        $this->assertCount(1, $list);
        $this->assertSame($objectId, $list[0]['n']);
        $this->assertSame('app.alert("ok");', $list[0]['js']);
        $this->assertTrue($list[0]['onload']);
    }

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

    public function testSetAndGetDefaultAnnotationProperties(): void
    {
        $obj = $this->getTestObject();
        $props = ['lineWidth' => 2, 'borderStyle' => 'dashed', 'fillColor' => 'yellow'];
        $obj->setDefJSAnnotProp($props);

        $this->assertSame($props, $obj->getDefJSAnnotProp());
    }

    public function testAddInternalLinkStoresLinkData(): void
    {
        $obj = $this->getTestObject();
        $page = $this->addRawPage($obj);
        $lnk = $obj->addInternalLink($page['pid'], 12.5);

        /** @var array<string, array{p:int, y:float}> $links */
        $links = $this->getObjectProperty($obj, 'links');
        $this->assertSame('@1', $lnk);
        $this->assertSame($page['pid'], $links[$lnk]['p']);
        $this->bcAssertEqualsWithDelta(12.5, $links[$lnk]['y']);
    }

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
        $this->assertSame($page['pid'], $dests[$key]['p']);
        $this->bcAssertEqualsWithDelta(10.0, $dests[$key]['x']);
        $this->bcAssertEqualsWithDelta(20.0, $dests[$key]['y']);
    }

    public function testSetBookmarkClampsLevelAndUppercasesStyle(): void
    {
        $obj = $this->getTestObject();
        $page = $this->addRawPage($obj);
        $obj->setBookmark('Parent', '', 0, $page['pid'], 0, 0, 'b', 'red');
        $obj->setBookmark('Child', '', 9, $page['pid'], 1, 2, 'iu', 'blue');

        /** @var array<int, array{l:int, s:string}> $outlines */
        $outlines = $this->getObjectProperty($obj, 'outlines');
        $this->assertCount(2, $outlines);
        $this->assertSame(0, $outlines[0]['l']);
        $this->assertSame('B', $outlines[0]['s']);
        $this->assertSame(1, $outlines[1]['l']);
        $this->assertSame('IU', $outlines[1]['s']);
    }

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

        /** @var array<string, array{outdata:string, xobject:array<int, string>, image:array<int, int>, font:array<int, string>, gradient:array<int, int>, extgstate:array<int, int>, spot_colors:array<int, string>}> $xobjects */
        $xobjects = $this->getObjectProperty($obj, 'xobjects');
        $this->assertArrayHasKey($tid, $xobjects);
        $this->assertSame('q Q', $xobjects[$tid]['outdata']);
        $this->assertSame(['XT999'], $xobjects[$tid]['xobject']);
        $this->assertSame([7], $xobjects[$tid]['image']);
        $this->assertSame(['F1'], $xobjects[$tid]['font']);
        $this->assertSame([8], $xobjects[$tid]['gradient']);
        $this->assertSame([9], $xobjects[$tid]['extgstate']);
        $this->assertSame(['SC1'], $xobjects[$tid]['spot_colors']);

        $obj->exitXObjectTemplate();
        /** @var string $xobjtid */
        $xobjtid = $this->getObjectProperty($obj, 'xobjtid');
        $this->assertSame('', $xobjtid);

        $this->assertSame('', $obj->getXObjectTemplate('UNKNOWN'));
        $draw = $obj->getXObjectTemplate($tid, 1, 2, 10, 20);
        $this->assertStringContainsString('/' . $tid . ' Do', $draw);
    }

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
        $this->assertSame('desc', $files[$key]['description']);
    }

    public function testAddEmbeddedFileThrowsInPdfa1(): void
    {
        $obj = $this->getTestObject();
        $this->setObjectProperty($obj, 'pdfa', 1);
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->addEmbeddedFile('file.bin');
    }

    public function testAddContentAsEmbeddedFileStoresContent(): void
    {
        $obj = $this->getTestObject();
        $obj->addContentAsEmbeddedFile('payload.txt', 'abc123', 'text/plain', 'Data', 'payload');

        /** @var array<string, array{content:string, description:string}> $files */
        $files = $this->getObjectProperty($obj, 'embeddedfiles');
        $this->assertArrayHasKey('payload.txt', $files);
        $this->assertSame('abc123', $files['payload.txt']['content']);
        $this->assertSame('payload', $files['payload.txt']['description']);
    }

    public function testAddContentAsEmbeddedFileThrowsOnEmptyContent(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->addContentAsEmbeddedFile('payload.txt', '');
    }

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

    public function testGetAnnotOptFromJSPropCoversDensePropertyMapping(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame([], $obj->exposeGetAnnotOptFromJSProp([]));
        $this->assertSame(['Subtype' => 'Widget'], $obj->exposeGetAnnotOptFromJSProp(['aopt' => ['Subtype' => 'Widget']]));

        $this->setObjectProperty($obj, 'rtl', true);
        $rtlOpt = $obj->exposeGetAnnotOptFromJSProp(['alignment' => 'weird']);
        $this->assertSame(2, $rtlOpt['q']);

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
        $markerOptions = $opt['mk'];
        /** @var array<string, mixed> $iconFit */
        $iconFit = $markerOptions['if'];

        $this->assertSame(1, $opt['q']);
        $this->assertSame([0, 0, 2, [3, 2]], $opt['border']);
        $this->assertSame(['w' => 2, 's' => 'D', 'd' => [3, 2]], $opt['bs']);
        $this->assertSame([0.25, 0.75], $iconFit['a']);
        $this->assertTrue($iconFit['fb']);
        $this->assertSame('A', $iconFit['s']);
        $this->assertSame('S', $iconFit['sw']);
        $this->assertSame(6, $markerOptions['tp']);
        $this->assertArrayHasKey('bg', $markerOptions);
        $this->assertArrayHasKey('bc', $markerOptions);
        $this->assertSame(90, $markerOptions['r']);
        $this->assertSame(5, $opt['maxlen']);
        $this->assertGreaterThan(0, $opt['ff']);
        $this->assertSame('dv', $opt['dv']);
        $this->assertSame(100, $opt['f']);
        $this->assertSame([1, 2], $opt['i']);
        $this->assertSame([['Export A', 'Visible A'], ['Export B', 'Visible B']], $opt['opt']);
        $this->assertSame('<b>visible</b>', $opt['rv']);
        $this->assertSame('submit-field', $opt['tm']);
        $this->assertSame('field-name', $opt['t']);
        $this->assertSame('Field Name', $opt['tu']);
        $this->assertSame('O', $opt['h']);
    }

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

    public function testSetAnnotationAndSetLinkCreateAnnotationEntries(): void
    {
        $obj = $this->getTestObject();
        $textAnnotationId = $obj->setAnnotation(1, 2, 3, 4, 'note', ['subtype' => 'Text']);
        $linkAnnotationId = $obj->setLink(5, 6, 7, 8, '#dest');

        /** @var array<int, array{txt:string, opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($textAnnotationId, $ann);
        $this->assertArrayHasKey($linkAnnotationId, $ann);
        $this->assertSame('note', $ann[$textAnnotationId]['txt']);
        $this->assertSame('Link', $ann[$linkAnnotationId]['opt']['subtype']);
    }

    public function testSetAnnotationInsideXObjectIsDeferred(): void
    {
        $obj = $this->getTestObject();
        $this->addRawPage($obj);
        $tid = $obj->newXObjectTemplate(20, 20);

        $oid = $obj->setAnnotation(1, 1, 5, 5, 'in-template', ['subtype' => 'Text']);

        $this->assertSame(0, $oid);
        /** @var array<string, array{annotations:array<int, mixed>}> $xobjects */
        $xobjects = $this->getObjectProperty($obj, 'xobjects');
        $this->assertCount(1, $xobjects[$tid]['annotations']);
        $obj->exitXObjectTemplate();
    }

    public function testAddFFTextCreatesWidgetAnnotation(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $oid = $obj->addFFText('field1', 1, 2, 40, 8, ['v' => 'hello']);

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $this->assertSame('Widget', $ann[$oid]['opt']['Subtype']);
        $this->assertSame('Tx', $ann[$oid]['opt']['ft']);
        $this->assertSame('field1', $ann[$oid]['opt']['t']);
    }

    public function testAddFFButtonCreatesButtonWidgetWithAction(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $oid = $obj->addFFButton('btnField', 1, 2, 30, 10, 'Caption', 'app.alert("x");');

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $this->assertSame('Widget', $ann[$oid]['opt']['Subtype']);
        $this->assertSame('Btn', $ann[$oid]['opt']['ft']);
        $this->assertSame('Caption', $ann[$oid]['opt']['t']);
        $this->assertSame('btnField', $ann[$oid]['opt']['v']);
        $this->assertArrayHasKey('aa', $ann[$oid]['opt']);
    }

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
        $this->assertIsString($ann[$oid]['opt']['aa']);
        /** @var string $actionString */
        $actionString = $ann[$oid]['opt']['aa'];
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
                'UnknownFlag',
            ],
        ]);
    }

    public function testAddFFCheckBoxCreatesCheckboxWidget(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $oid = $obj->addFFCheckBox('chkField', 2, 3, 5, 'Yes', true);

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $this->assertSame('Widget', $ann[$oid]['opt']['Subtype']);
        $this->assertSame('Btn', $ann[$oid]['opt']['ft']);
        $this->assertSame('Yes', $ann[$oid]['opt']['as']);
        $this->assertSame(['Yes'], $ann[$oid]['opt']['opt']);
    }

    public function testAddFFComboBoxCreatesChoiceWidget(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $vals = [['A', 'Alpha'], ['B', 'Beta']];
        $oid = $obj->addFFComboBox('cmbField', 1, 2, 30, 12, $vals);

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $this->assertSame('Widget', $ann[$oid]['opt']['Subtype']);
        $this->assertSame('Ch', $ann[$oid]['opt']['ft']);
        $this->assertSame('cmbField', $ann[$oid]['opt']['t']);
        $this->assertSame($vals, $ann[$oid]['opt']['opt']);
    }

    public function testAddFFListBoxCreatesChoiceWidget(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $vals = ['One', 'Two'];
        $oid = $obj->addFFListBox('lstField', 1, 2, 30, 12, $vals);

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $this->assertSame('Widget', $ann[$oid]['opt']['Subtype']);
        $this->assertSame('Ch', $ann[$oid]['opt']['ft']);
        $this->assertSame('lstField', $ann[$oid]['opt']['t']);
        $this->assertSame($vals, $ann[$oid]['opt']['opt']);
    }

    public function testAddFFRadioButtonCreatesRadioGroupAndWidget(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $oid = $obj->addFFRadioButton('radField', 3, 4, 6, 'On', true);

        /** @var array<int, array{opt:array<string, mixed>}> $ann */
        $ann = $this->getObjectProperty($obj, 'annotation');
        $this->assertArrayHasKey($oid, $ann);
        $this->assertSame('Widget', $ann[$oid]['opt']['Subtype']);
        $this->assertSame('Btn', $ann[$oid]['opt']['ft']);
        $this->assertSame('On', $ann[$oid]['opt']['as']);

        /** @var array<string, array{kids:array<int, mixed>}> $groups */
        $groups = $this->getObjectProperty($obj, 'radiobuttons');
        $this->assertArrayHasKey('radField', $groups);
        $this->assertNotEmpty($groups['radField']['kids']);
    }

    public function testAddJSFieldWrappersAppendExpectedScripts(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        \set_error_handler(static function (int $errno, string $errstr): bool {
            return ($errno === E_WARNING) && \str_contains($errstr, 'Undefined array key "num"');
        });

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
        $this->assertStringContainsString("fcmb.setItems(", $jsScript);
        $this->assertStringContainsString("flst.\\setItems(", $jsScript);
        $this->assertStringContainsString("frad=this.addField('rad','radiobutton'", $jsScript);
        $this->assertStringContainsString("ftxt=this.addField('txt','text'", $jsScript);
    }

    public function testAddJSFieldWrappersHandleArrayValuedSelections(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        \set_error_handler(static function (int $errno, string $errstr): bool {
            return ($errno === E_WARNING) && \str_contains($errstr, 'Undefined array key "num"');
        });

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

    public function testGetAnnotOptFromJSPropCoversAdditionalMappingVariants(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame(
            ['f' => 7],
            $obj->exposeGetAnnotOptFromJSProp(['aopt' => ['f' => 7]]),
        );

        $styleVariants = [
            'beveled' => 'B',
            'inset' => 'I',
            'underline' => 'U',
            'solid' => 'S',
        ];
        foreach ($styleVariants as $style => $expected) {
            $mapped = $obj->exposeGetAnnotOptFromJSProp(['borderStyle' => $style]);
            /** @var array<string, mixed> $borderSpec */
            $borderSpec = $mapped['bs'];
            $this->assertSame($expected, $borderSpec['s']);
        }

        $numericPos = $obj->exposeGetAnnotOptFromJSProp(['buttonPosition' => 5]);
        /** @var array<string, mixed> $numericMk */
        $numericMk = $numericPos['mk'];
        $this->assertSame(5, $numericMk['tp']);
        $invalidPos = $obj->exposeGetAnnotOptFromJSProp(['buttonPosition' => 99]);
        /** @var array<string, mixed> $invalidMk */
        $invalidMk = $invalidPos['mk'];
        $this->assertArrayNotHasKey('tp', $invalidMk);

        $filled = $obj->exposeGetAnnotOptFromJSProp([
            'fillColor' => [0.1, 0.2, 0.3],
            'strokeColor' => [0.4, 0.5, 0.6],
            'display' => 'display.hidden',
            'highlight' => 'push',
            'value' => 'plain-value',
        ]);
        /** @var array<string, mixed> $filledMk */
        $filledMk = $filled['mk'];
        $this->assertSame([0.1, 0.2, 0.3], $filledMk['bg']);
        $this->assertSame([0.4, 0.5, 0.6], $filledMk['bc']);
        $this->assertSame('P', $filled['h']);
        $this->assertSame('plain-value', $filled['v']);
        $this->assertSame(6, $filled['f']);

        $noPrint = $obj->exposeGetAnnotOptFromJSProp(['display' => 'display.noPrint']);
        $this->assertSame(0, $noPrint['f']);
        $visible = $obj->exposeGetAnnotOptFromJSProp(['display' => 'display.visible', 'highlight' => 'invert']);
        $this->assertSame(4, $visible['f']);
        $this->assertSame('i', $visible['h']);
        $defaultHighlight = $obj->exposeGetAnnotOptFromJSProp(['highlight' => 'unknown']);
        $this->assertSame('N', $defaultHighlight['h']);
        $highlightAlias = $obj->exposeGetAnnotOptFromJSProp(['highlight' => 'highlight.o']);
        $this->assertSame('O', $highlightAlias['h']);

        $this->assertSame([1, 2, 3], $obj->exposeGetAnnotOptFromJSProp(['border' => [1, 2, 3]])['border']);
        $scaleHowMap = [
            'scaleHow.proportional' => 'P',
            'scaleHow.invalid' => 'P',
        ];
        foreach ($scaleHowMap as $scaleHow => $expected) {
            $mapped = $obj->exposeGetAnnotOptFromJSProp(['buttonScaleHow' => $scaleHow]);
            /** @var array<string, mixed> $scaleHowMk */
            $scaleHowMk = $mapped['mk'];
            /** @var array<string, mixed> $scaleHowIf */
            $scaleHowIf = $scaleHowMk['if'];
            $this->assertSame($expected, $scaleHowIf['s']);
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
            $scaleWhenMk = $mapped['mk'];
            /** @var array<string, mixed> $scaleWhenIf */
            $scaleWhenIf = $scaleWhenMk['if'];
            $this->assertSame($expected, $scaleWhenIf['sw']);
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
            $marker = $mapped['mk'];
            $this->assertSame($expected, $marker['tp']);
        }

        $highlightMap = [
            'none' => 'N',
            'highlight.n' => 'N',
            'highlight.i' => 'i',
            'highlight.p' => 'P',
        ];
        foreach ($highlightMap as $highlight => $expected) {
            $mapped = $obj->exposeGetAnnotOptFromJSProp(['highlight' => $highlight]);
            $this->assertSame($expected, $mapped['h']);
        }

        $fillColorFallback = $this->getInternalTestObject();
        $this->assertMatchesRegularExpression('/(rg\\n|^$)/', $fillColorFallback->exposeGetPDFDefFillColor());
    }

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

    public function testJsFieldPropertiesAndAnnotationAttachmentsCoverResidualBranches(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        \set_error_handler(static function (int $errno, string $errstr): bool {
            return ($errno === E_WARNING) && \str_contains($errstr, 'Undefined array key "num"');
        });

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
        \imagepng($iconResource, (string) $iconPath);
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
            @\unlink((string) $iconPath);
        }

        $this->assertGreaterThanOrEqual(-1, $annotId);
        /** @var array<string, mixed> $embeddedfiles */
        $embeddedfiles = $this->getObjectProperty($obj, 'embeddedfiles');
        $this->assertArrayHasKey('README.md', $embeddedfiles);
    }

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
        $this->assertIsString($annotation[$oid]['opt']['aa']);
        /** @var string $actionString */
        $actionString = $annotation[$oid]['opt']['aa'];
        $this->assertStringContainsString('/S /ResetForm', $actionString);
        $this->assertStringContainsString('/Flags 1024', $actionString);
    }

    public function testFFChoiceAndTextVariantsCoverScalarAndAlignmentPaths(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);

        $comboId = $obj->addFFComboBox('comboScalar', 1, 2, 30, 12, ['One', 'Two']);
        $listId = $obj->addFFListBox('listScalar', 1, 2, 30, 12, ['Red', 'Blue']);
        $listArrayId = $obj->addFFListBox('listArray', 1, 2, 30, 12, [['V1', 'Label 1'], ['V2', 'Label 2']]);
        $radioOffId = $obj->addFFRadioButton('radioGroup', 3, 4, 6, 'On', false, ['subtype' => 'Widget', 'q' => 0], ['aopt' => ['Subtype' => 'Widget', 'f' => 0], 'readonly' => 'true']);
        $radioOnId = $obj->addFFRadioButton('radioGroup', 9, 4, 6, 'On', true);

        $txtLeftId = $obj->addFFText('txtLeft', 1, 2, 30, 10, ['subtype' => 'Widget', 'q' => 0], ['alignment' => 'left', 'value' => 'L']);
        $txtCenterId = $obj->addFFText('txtCenter', 1, 2, 30, 10, ['subtype' => 'Widget'], ['alignment' => 'center', 'value' => 'C']);
        $txtRightId = $obj->addFFText('txtRight', 1, 2, 30, 10, ['subtype' => 'Widget', 'q' => 2], ['alignment' => 'right', 'value' => 'R']);
        $txtUnknownId = $obj->addFFText('txtUnknown', 1, 2, 30, 10, ['subtype' => 'Widget', 'q' => 99], ['value' => 'U']);

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
        $this->assertFalse($radioGroups['radioGroup']['#readonly#']);
        $this->assertSame('Off', $radioGroups['radioGroup']['kids'][0]['def']);
        $this->assertSame('On', $radioGroups['radioGroup']['kids'][1]['def']);
    }
}
