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

class JavaScriptTest extends TestUtil
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

    /** @return array{pid: int} */
    private function addRawPage(\Com\Tecnick\Pdf\Tcpdf $obj): array
    {
        /** @var \Com\Tecnick\Pdf\Page\Page $page */
        $page = $this->getObjectProperty($obj, 'page');
        /** @var array{pid: int} $rawPage */
        $rawPage = $page->add([]);
        return $rawPage;
    }

    /** @return array{pid: int} */
    private function initFontAndPage(\Com\Tecnick\Pdf\Tcpdf $obj): array
    {
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);
        /** @var array{pid: int} $page */
        $page = $obj->addPage();
        return $page;
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
}
