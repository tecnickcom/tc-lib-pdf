<?php

/**
 * TextTest.php
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

class TestableText extends \Com\Tecnick\Pdf\Tcpdf
{
    public function exposeGetOutUTOLine(float $pntx, float $pnty, float $pwidth, float $psize): string
    {
        return $this->getOutUTOLine($pntx, $pnty, $pwidth, $psize);
    }

    public function exposeCleanupText(string $txt): string
    {
        return $this->cleanupText($txt);
    }

    public function exposeGetOutTextPosXY(string $raw, float $posx = 0, float $posy = 0, string $mode = 'Td'): string
    {
        return $this->getOutTextPosXY($raw, $posx, $posy, $mode);
    }

    public function exposeGetTextRenderingMode(bool $fill = true, bool $stroke = false, bool $clip = false): int
    {
        return $this->getTextRenderingMode($fill, $stroke, $clip);
    }

    public function exposeGetOutTextStateOperatorTc(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorTc($raw, $value);
    }

    public function exposeGetOutTextStateOperatorTw(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorTw($raw, $value);
    }

    public function exposeGetOutTextStateOperatorTz(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorTz($raw, $value);
    }

    public function exposeGetOutTextStateOperatorTL(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorTL($raw, $value);
    }

    public function exposeGetOutTextStateOperatorTr(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorTr($raw, $value);
    }

    public function exposeGetOutTextStateOperatorTs(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorTs($raw, $value);
    }

    public function exposeGetOutTextStateOperatorw(string $raw, int|float $value = 0): string
    {
        return $this->getOutTextStateOperatorw($raw, $value);
    }

    public function exposeGetOutTextPosMatrix(string $raw, array $matrix = [1, 0, 0, 1, 0, 0]): string
    {
        return $this->getOutTextPosMatrix($raw, $matrix);
    }

    public function exposeGetOutTextShowing(string $str, string $mode = 'Tj'): string
    {
        return $this->getOutTextShowing($str, $mode);
    }

    public function exposeGetOutTextObject(string $raw = ''): string
    {
        return $this->getOutTextObject($raw);
    }

    public function exposeReplaceUnicodeChars(array $ordarr): array
    {
        return $this->replaceUnicodeChars($ordarr);
    }

    public function exposeRemoveOrdArrSoftHyphens(array $ordarr): array
    {
        return $this->removeOrdArrSoftHyphens($ordarr);
    }

    public function exposeAddOrdArrBreakPoints(array $ordarr): array
    {
        return $this->addOrdArrBreakPoints($ordarr);
    }

    public function exposeSetPageContext(int $pid = -1): void
    {
        $this->setPageContext($pid);
    }

    public function exposeEscapePerc(string $str): string
    {
        return $this->escapePerc($str);
    }

    public function exposeGetStringWidth(string $str): float
    {
        return $this->getStringWidth($str);
    }

    public function exposePrepareText(string $txt, string $forcedir = ''): array
    {
        $ordarr = [];
        $dim = self::DIM_DEFAULT;
        $this->prepareText($txt, $ordarr, $dim, $forcedir);
        return [$txt, $ordarr, $dim];
    }

    public function exposeSplitLines(array $ordarr, array $dim, float $pwidth, float $poffset = 0): array
    {
        return $this->splitLines($ordarr, $dim, $pwidth, $poffset);
    }

    public function exposeGetOutTextLine(
        string $txt,
        array $ordarr,
        array $dim,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $strokewidth = 0,
        float $wordspacing = 0,
        float $leading = 0,
        float $rise = 0,
        bool $fill = true,
        bool $stroke = false,
        bool $underline = false,
        bool $linethrough = false,
        bool $overline = false,
        bool $clip = false,
        ?array $shadow = null,
    ): string {
        return $this->getOutTextLine(
            $txt,
            $ordarr,
            $dim,
            $posx,
            $posy,
            $width,
            $strokewidth,
            $wordspacing,
            $leading,
            $rise,
            $fill,
            $stroke,
            $underline,
            $linethrough,
            $overline,
            $clip,
            $shadow,
        );
    }

    public function exposeOutTextLine(
        string $txt,
        array $ordarr,
        array $dim,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $strokewidth = 0,
        float $wordspacing = 0,
        float $leading = 0,
        float $rise = 0,
        bool $fill = true,
        bool $stroke = false,
        bool $underline = false,
        bool $linethrough = false,
        bool $overline = false,
        bool $clip = false,
    ): string {
        return $this->outTextLine(
            $txt,
            $ordarr,
            $dim,
            $posx,
            $posy,
            $width,
            $strokewidth,
            $wordspacing,
            $leading,
            $rise,
            $fill,
            $stroke,
            $underline,
            $linethrough,
            $overline,
            $clip,
        );
    }

    public function exposeOutTextLines(
        array $ordarr,
        array $lines,
        float $posx,
        float $posy,
        float $width,
        float $offset,
        float $fontascent,
        float $linespace = 0,
        float $strokewidth = 0,
        float $wordspacing = 0,
        float $leading = 0,
        float $rise = 0,
        string $halign = '',
        bool $jlast = true,
        bool $fill = true,
        bool $stroke = false,
        bool $underline = false,
        bool $linethrough = false,
        bool $overline = false,
        bool $clip = false,
        ?array $shadow = null,
    ): string {
        return $this->outTextLines(
            $ordarr,
            $lines,
            $posx,
            $posy,
            $width,
            $offset,
            $fontascent,
            $linespace,
            $strokewidth,
            $wordspacing,
            $leading,
            $rise,
            $halign,
            $jlast,
            $fill,
            $stroke,
            $underline,
            $linethrough,
            $overline,
            $clip,
            $shadow,
        );
    }

    public function exposeGetJustifiedString(string $txt, array $ordarr, array $dim, float $width = 0): string
    {
        return $this->getJustifiedString($txt, $ordarr, $dim, $width);
    }

    public function exposeHyphenateTextOrdArr(array $phyphens, array $ordarr): array
    {
        return $this->hyphenateTextOrdArr($phyphens, $ordarr);
    }

    public function exposeHyphenateWordOrdArr(
        array $phyphens,
        array $ordarr,
        int $leftmin = 1,
        int $rightmin = 2,
        int $charmin = 1,
        int $charmax = 8,
    ): array {
        return $this->hyphenateWordOrdArr($phyphens, $ordarr, $leftmin, $rightmin, $charmin, $charmax);
    }

    public function exposeStrToOrdArr(string $txt): array
    {
        return $this->uniconv->strToOrdArr($txt);
    }
}

class TextTest extends TestUtil
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

    protected function getInternalTestObject(): TestableText
    {
        return new TestableText();
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

    private function initFont(\Com\Tecnick\Pdf\Tcpdf $obj): void
    {
        $font = $this->getObjectProperty($obj, 'font');
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);
    }

    public function testGetLastBBoxDefaultsToZeroBox(): void
    {
        $obj = $this->getTestObject();

        $this->assertSame([
            'x' => 0.0,
            'y' => 0.0,
            'w' => 0.0,
            'h' => 0.0,
        ], $obj->getLastBBox());
    }

    public function testLoadTexHyphenPatternsParsesFixture(): void
    {
        $obj = $this->getTestObject();
        $file = __DIR__ . '/fixtures/hyphen-test.tex';

        $patterns = $obj->loadTexHyphenPatterns($file);

        $this->assertSame('hy4phen', $patterns['hyphen']);
        $this->assertSame('test1ing', $patterns['testing']);
        $this->assertSame('a1bc', $patterns['abc']);
    }

    public function testSetTexHyphenPatternsStoresPatterns(): void
    {
        $obj = $this->getTestObject();
        $patterns = ['hyphen' => 'hy4phen'];
        $obj->setTexHyphenPatterns($patterns);

        $this->assertSame($patterns, $this->getObjectProperty($obj, 'hyphen_patterns'));
    }

    public function testEnableZeroWidthBreakPointsTogglesFlag(): void
    {
        $obj = $this->getTestObject();
        $obj->enableZeroWidthBreakPoints(true);
        $this->assertTrue($this->getObjectProperty($obj, 'autozerowidthbreaks'));

        $obj->enableZeroWidthBreakPoints(false);
        $this->assertFalse($this->getObjectProperty($obj, 'autozerowidthbreaks'));
    }

    public function testAddPageReturnsPageData(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);

        $page = $obj->addPage();

        $this->assertArrayHasKey('pid', $page);
        $this->assertSame($page['pid'], $this->getObjectProperty($obj, 'page')->getPageId());
    }

    public function testDefaultPageContentReturnsPdfCommands(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $page = $obj->addPage();

        $out = $obj->defaultPageContent($page['pid']);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('BT', $out);
    }

    public function testGetTextLineAndGetTextCellHandleBasicInput(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $obj->addPage();

        $this->assertSame('', $obj->getTextLine(''));
        $this->assertSame('', $obj->getTextCell(''));

        $line = $obj->getTextLine('Hello', 1, 2);
        $cell = $obj->getTextCell('Hello', 1, 2, 20, 6, 0, 0, 'T', 'L');

        $this->assertNotSame('', $line);
        $this->assertNotSame('', $cell);
    }

    public function testAddTextCellAppendsContentToPage(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $page = $obj->addPage();

        $pageObj = $this->getObjectProperty($obj, 'page');
        $before = $pageObj->getPage($page['pid'])['content'];

        $obj->addTextCell('Hello', $page['pid'], 1, 2, 20, 6, 0, 0, 'T', 'L');

        $after = $pageObj->getPage($page['pid'])['content'];
        $this->assertGreaterThan(\count($before), \count($after));
        $this->assertIsString($after[\array_key_last($after)]);
        $this->assertNotSame('', $after[\array_key_last($after)]);
    }

    public function testTextOperatorHelpersCoverModesAndFormatting(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        $this->assertSame('1.000000 2.000000 3.000000 0.500000 re f' . "\n", $obj->exposeGetOutUTOLine(1, 2, 3, 0.5));
        $this->assertSame(0, $obj->exposeGetTextRenderingMode(true, false, false));
        $this->assertSame(3, $obj->exposeGetTextRenderingMode(false, false, false));
        $this->assertSame(7, $obj->exposeGetTextRenderingMode(false, false, true));

        $this->assertStringContainsString('Td raw', $obj->exposeGetOutTextPosXY('raw', 1, 2, 'Td'));
        $this->assertSame('T* raw', $obj->exposeGetOutTextPosXY('raw', 0, 0, 'T*'));
        $this->assertSame('', $obj->exposeGetOutTextPosXY('raw', 0, 0, 'NOPE'));

        $this->assertSame('raw', $obj->exposeGetOutTextStateOperatorTc('raw', 0));
        $this->assertStringContainsString(' Tc raw 0 Tc', $obj->exposeGetOutTextStateOperatorTc('raw', 1.5));
        $this->assertSame('raw', $obj->exposeGetOutTextStateOperatorTw('raw', 0));
        $this->assertStringContainsString(' Tw raw 0 Tw', $obj->exposeGetOutTextStateOperatorTw('raw', 2));
        $this->assertSame('raw', $obj->exposeGetOutTextStateOperatorTz('raw', 1));
        $this->assertStringContainsString(' Tz raw 100 Tz', $obj->exposeGetOutTextStateOperatorTz('raw', 80));
        $this->assertSame('raw', $obj->exposeGetOutTextStateOperatorTL('raw', 0));
        $this->assertStringContainsString(' TL raw 0 TL', $obj->exposeGetOutTextStateOperatorTL('raw', 10));
        $this->assertSame('raw', $obj->exposeGetOutTextStateOperatorTr('raw', 99));
        $this->assertStringContainsString('2 Tr raw', $obj->exposeGetOutTextStateOperatorTr('raw', 2));
        $this->assertSame('raw', $obj->exposeGetOutTextStateOperatorTs('raw', 0));
        $this->assertStringContainsString(' Ts raw 0 Ts', $obj->exposeGetOutTextStateOperatorTs('raw', 3));
        $this->assertStringContainsString('0.000000 w raw', $obj->exposeGetOutTextStateOperatorw('raw', -1));

        $this->assertSame('', $obj->exposeGetOutTextPosMatrix('raw', [1, 2]));
        $this->assertStringContainsString(' Tm raw', $obj->exposeGetOutTextPosMatrix('raw', [1, 0, 0, 1, 10, 20]));
        $this->assertSame('(abc) Tj', $obj->exposeGetOutTextShowing('abc', 'Tj'));
        $this->assertSame('[(abc)] TJ', $obj->exposeGetOutTextShowing('abc', 'TJ'));
        $this->assertSame("(abc) '", $obj->exposeGetOutTextShowing('abc', "'"));
        $this->assertSame('', $obj->exposeGetOutTextShowing('abc', 'X'));
        $this->assertSame("BT xyz ET\n", $obj->exposeGetOutTextObject('xyz'));
    }

    public function testTextCleanupHyphenationAndEscapingHelpers(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame('A B C', $obj->exposeCleanupText("A\rB\u{00A0}C\u{00AD}"));
        $this->assertSame([65, 173], \array_values($obj->exposeRemoveOrdArrSoftHyphens([65, 173, 8203, 173])));
        $this->assertSame([36, 8203, 65], $obj->exposeAddOrdArrBreakPoints([36, 65]));
        $this->assertSame([65, 66], $obj->exposeReplaceUnicodeChars([65, 66]));
        $this->assertSame('100%% ready', $obj->exposeEscapePerc('100% ready'));
    }

    public function testSetPageContextAndStringWidthHelpers(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $page = $obj->addPage();

        $pageObj = $this->getObjectProperty($obj, 'page');
        $before = $pageObj->getPage($page['pid'])['content'];
        $obj->exposeSetPageContext($page['pid']);
        $after = $pageObj->getPage($page['pid'])['content'];

        $this->assertGreaterThan(\count($before), \count($after));
        $this->assertSame(0.0, $obj->exposeGetStringWidth(''));
        $this->assertGreaterThan(0, $obj->exposeGetStringWidth('Hello'));
    }

    public function testPrepareTextAndSplitLinesCoverEmptyAndMultiLineCases(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        [$txt, $ordarr, $dim] = $obj->exposePrepareText("Hello\r world");
        $linesWide = $obj->exposeSplitLines($ordarr, $dim, 1000);
        $linesNarrow = $obj->exposeSplitLines($ordarr, $dim, 10);

        $this->assertSame('Hello  world', $txt);
        $this->assertNotEmpty($ordarr);
        $this->assertGreaterThan(0, $dim['totwidth']);
        $this->assertCount(1, $linesWide);
        $this->assertGreaterThan(1, \count($linesNarrow));
        $this->assertSame([], $obj->exposeSplitLines([], $dim, 10));
    }

    public function testGetOutTextLineAndOutTextLineRenderFromPreparedText(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        [$txt, $ordarr, $dim] = $obj->exposePrepareText('Hello world');

        $this->assertSame('', $obj->exposeGetOutTextLine('', [], []));
        $this->assertSame('', $obj->exposeOutTextLine('', [], []));

        $raw = $obj->exposeGetOutTextLine($txt, $ordarr, $dim, 1, 2, 0, 0, 0, 0, 0, true, false, true, true, true, false);
        $out = $obj->exposeOutTextLine($txt, $ordarr, $dim, 1, 2, 0, 0, 0, 0, 0, true, false, true, true, true, false);

        $this->assertStringContainsString('BT ', $raw);
        $this->assertStringContainsString(' ET', $raw);
        $this->assertStringContainsString('re f', $out);
    }

    public function testOutTextLinesGetJustifiedStringAndHyphenationHelpers(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        [$txt, $ordarr, $dim] = $obj->exposePrepareText('Hello world again');
        $lines = $obj->exposeSplitLines($ordarr, $dim, 20);

        $this->assertSame('', $obj->exposeOutTextLines([], [], 0, 0, 0, 0, 0));

        $block = $obj->exposeOutTextLines($ordarr, $lines, 1, 1, 30, 0, 1.5, 0, 0, 0, 0, 0, 'J', false);
        $this->assertStringContainsString('BT ', $block);

        $just = $obj->exposeGetJustifiedString($txt, $ordarr, $dim, 40);
        $this->assertStringContainsString('Tw', $just);

        $patterns = ['hyphen' => 'hy4phen'];
        $word = $obj->exposeStrToOrdArr('hyphen');
        $text = $obj->exposeStrToOrdArr('hyphen,test');
        $hypWord = $obj->exposeHyphenateWordOrdArr($patterns, $word);
        $hypText = $obj->exposeHyphenateTextOrdArr($patterns, $text);

        $this->assertNotEmpty($hypWord);
        $this->assertNotEmpty($hypText);
        $this->assertSame($word, $obj->exposeHyphenateWordOrdArr([], $word));
    }
}
