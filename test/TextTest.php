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

class TextTest extends TestUtil
{
    public static function setUpBeforeClass(): void
    {
        if (!\defined('K_PATH_FONTS')) {
            $fonts = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts');
            \define('K_PATH_FONTS', $fonts);
        }
    }

    /** @throws \Throwable */
    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    /** @throws \Throwable */
    protected function getInternalTestObject(): TestableText
    {
        return new TestableText();
    }

    /** @throws \Throwable */
    private function initUnicodeFont(\Com\Tecnick\Pdf\Tcpdf $obj): void
    {
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(__DIR__
        . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/dejavu/dejavusans.json');
        $font->insert($pon, 'dejavusans', '', 10, null, null, $fontfile);
    }

    /**
     * @param array<string, mixed> $page
     */
    private function requirePageId(array $page): int
    {
        if (!isset($page['pid']) || !\is_int($page['pid'])) {
            $this->fail('Expected addPage to return an integer pid');
        }

        return $page['pid'];
    }

    /** @throws \Throwable */
    public function testGetLastBBoxDefaultsToZeroBox(): void
    {
        $obj = $this->getTestObject();

        $this->assertSame(
            [
                'x' => 0.0,
                'y' => 0.0,
                'w' => 0.0,
                'h' => 0.0,
            ],
            $obj->getLastBBox(),
        );
    }

    /** @throws \Throwable */
    public function testGetLastTextBBoxDefaultsToZeroBox(): void
    {
        $obj = $this->getTestObject();

        $this->assertSame(
            [
                'x' => 0.0,
                'y' => 0.0,
                'w' => 0.0,
                'h' => 0.0,
            ],
            $obj->getLastTextBBox(),
        );
    }

    /** @throws \Throwable */
    public function testGetLastCellBBoxDefaultsToZeroBox(): void
    {
        $obj = $this->getTestObject();

        $this->assertSame(
            [
                'x' => 0.0,
                'y' => 0.0,
                'w' => 0.0,
                'h' => 0.0,
            ],
            $obj->getLastCellBBox(),
        );
    }

    /** @throws \Throwable */
    public function testLoadTexHyphenPatternsParsesFixture(): void
    {
        $obj = $this->getTestObject();
        $file = __DIR__ . '/fixtures/hyphen-test.tex';

        $patterns = $obj->loadTexHyphenPatterns($file);

        $this->assertArrayHasKey('hyphen', $patterns);
        $this->assertArrayHasKey('testing', $patterns);
        $this->assertArrayHasKey('abc', $patterns);
        $this->assertSame('hy4phen', $patterns['hyphen'] ?? null);
        $this->assertSame('test1ing', $patterns['testing'] ?? null);
        $this->assertSame('a1bc', $patterns['abc'] ?? null);
    }

    /** @throws \Throwable */
    public function testSetTexHyphenPatternsStoresPatterns(): void
    {
        $obj = $this->getTestObject();
        $patterns = ['hyphen' => 'hy4phen'];
        $obj->setTexHyphenPatterns($patterns);

        $this->assertSame($patterns, $this->getObjectProperty($obj, 'hyphen_patterns'));
    }

    /** @throws \Throwable */
    public function testEnableZeroWidthBreakPointsTogglesFlag(): void
    {
        $obj = $this->getTestObject();
        $obj->enableZeroWidthBreakPoints(true);
        $this->assertTrue($this->getObjectProperty($obj, 'autozerowidthbreaks'));

        $obj->enableZeroWidthBreakPoints(false);
        $this->assertFalse($this->getObjectProperty($obj, 'autozerowidthbreaks'));
    }

    /** @throws \Throwable */
    public function testAddPageReturnsPageData(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);

        $page = $obj->addPage();

        $this->assertArrayHasKey('pid', $page);
        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($obj, 'page');
        $this->assertSame($page['pid'] ?? null, $pageObj->getPageId());
    }

    /** @throws \Throwable */
    public function testDefaultPageContentReturnsPdfCommands(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $page = $obj->addPage();
        $pid = $this->requirePageId($page);

        $out = $obj->defaultPageContent($pid);

        $this->assertNotSame('', $out);
        $this->assertStringContainsString('BT', $out);
    }

    /** @throws \Throwable */
    public function testDefaultPageContentUsesFooterArtifactInPdfUa(): void
    {
        $obj = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfua');
        $this->initFont($obj);
        $page = $obj->addPage();
        $pid = $this->requirePageId($page);

        $out = $obj->defaultPageContent($pid);

        $this->assertStringContainsString('/Artifact << /Type /Pagination /Subtype /Footer >> BDC', $out);
        $this->assertStringContainsString('EMC', $out);
        $this->assertStringNotContainsString('/MCID', $out);
    }

    /** @throws \Throwable */
    public function testArtifactHelpersAndAddArtifactContent(): void
    {
        $obj = $this->getTestObject();
        $this->assertSame('', $obj->beginArtifact('Pagination', 'Header'));
        $this->assertSame('', $obj->endArtifact());

        $pdfua = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfua');
        $this->initFont($pdfua);
        $page = $pdfua->addPage();

        $open = $pdfua->beginArtifact('Pagi nation', 'Head/er');
        $this->assertSame('/Artifact << /Type /Pagination /Subtype /Header >> BDC' . "\n", $open);
        $this->assertSame('EMC' . "\n", $pdfua->endArtifact());

        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($pdfua, 'page');
        $pid = $this->requirePageId($page);
        $pdfua->addArtifactContent("q\nQ\n", $pid, 'Pagination', 'Header');
        /** @var array<int, string> $content */
        $content = $pageObj->getPage($pid)['content'];

        $lastIdx = \count($content) - 1;
        $this->assertGreaterThanOrEqual(0, $lastIdx);
        $lastContent = $content[$lastIdx] ?? null;
        $this->assertIsString($lastContent);
        $this->assertStringContainsString('/Artifact << /Type /Pagination /Subtype /Header >> BDC', $lastContent);
        $this->assertStringContainsString("q\nQ\n", $lastContent);
        $this->assertStringContainsString('EMC', $lastContent);
        $this->assertStringNotContainsString('/MCID', $lastContent);
    }

    /** @throws \Throwable */
    public function testDefaultPageContentPreservesCurrentUnicodeFont(): void
    {
        $obj = $this->getTestObject();
        $obj->enableDefaultPageContent();
        $this->initUnicodeFont($obj);
        $obj->addPage();

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');

        $this->assertSame('dejavusans', $font->getCurrentFontKey());
        $this->assertTrue($font->isCurrentUnicodeFont());

        $out = $obj->getTextCell('The quick brown fox', 1, 2, 20, 6, 0, 0, 'T', 'L');

        $this->assertStringContainsString("\000T\000h\000e", $out);
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testGetTextCellAcceptsNamedAndNumericBorderStyleSides(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $obj->addPage();

        $top = ['lineWidth' => 0.4, 'lineColor' => '#ff0000'];
        $right = ['lineWidth' => 0.5, 'lineColor' => '#00aa00'];
        $bottom = ['lineWidth' => 0.6, 'lineColor' => '#0000ff'];
        $left = ['lineWidth' => 0.7, 'lineColor' => '#222222'];

        $namedStyles = [
            'T' => $top,
            'R' => $right,
            'B' => $bottom,
            'L' => $left,
        ];

        $numericStyles = [
            0 => $top,
            1 => $right,
            2 => $bottom,
            3 => $left,
        ];

        $namedOut = $obj->getTextCell('Hello', 10, 20, 40, 12, 0, 0, 'T', 'L', null, $namedStyles);
        $numericOut = $obj->getTextCell('Hello', 10, 20, 40, 12, 0, 0, 'T', 'L', null, $numericStyles);

        $this->assertNotSame('', $namedOut);
        $this->assertSame($numericOut, $namedOut);
    }

    /** @throws \Throwable */
    public function testGetTextCellFitUnknownModeFallsBackToDisabled(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $obj->addPage();

        $txt = 'This is a long sentence that should overflow in a small fixed-height box.';
        $base = $obj->getTextCell(
            $txt,
            10,
            20,
            40,
            6,
            0,
            0,
            'T',
            'L',
            null,
            [],
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            true,
            '',
            null,
            '',
        );
        $fallback = $obj->getTextCell(
            $txt,
            10,
            20,
            40,
            6,
            0,
            0,
            'T',
            'L',
            null,
            [],
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            true,
            '',
            null,
            'x',
        );

        $this->assertSame($base, $fallback);
    }

    /** @throws \Throwable */
    public function testGetTextCellFitDisabledWhenCellSizePreconditionsFail(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $obj->addPage();

        $txt = 'Overflow text should keep baseline behavior when width or height is automatic.';

        $baseWidthAuto = $obj->getTextCell($txt, 10, 20, 0, 6, 0, 0, 'T', 'L');
        $fitWidthAuto = $obj->getTextCell(
            $txt,
            10,
            20,
            0,
            6,
            0,
            0,
            'T',
            'L',
            null,
            [],
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            true,
            '',
            null,
            'F',
        );
        $this->assertSame($baseWidthAuto, $fitWidthAuto);

        $baseHeightAuto = $obj->getTextCell($txt, 10, 20, 40, 0, 0, 0, 'T', 'L');
        $fitHeightAuto = $obj->getTextCell(
            $txt,
            10,
            20,
            40,
            0,
            0,
            0,
            'T',
            'L',
            null,
            [],
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            true,
            '',
            null,
            'T',
        );
        $this->assertSame($baseHeightAuto, $fitHeightAuto);
    }

    /** @throws \Throwable */
    public function testGetTextCellFitTruncateKeepsTextInsideCellHeight(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $obj->addPage();

        $txt = 'Truncation mode must cut content to fit both width and height constraints.';
        $base = $obj->getTextCell(
            $txt,
            10,
            20,
            35,
            5,
            0,
            0.5,
            'T',
            'L',
            null,
            [],
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            true,
            '',
            null,
            '',
        );
        $fit = $obj->getTextCell(
            $txt,
            10,
            20,
            35,
            5,
            0,
            0.5,
            'T',
            'L',
            null,
            [],
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            true,
            '',
            null,
            'T',
        );
        $bbox = $obj->getLastTextBBox();

        $this->assertNotSame($base, $fit);
        $this->assertLessThanOrEqual(5.0 + 0.01, $bbox['h']);
    }

    /** @throws \Throwable */
    public function testGetTextCellFitStretchRestoresFontState(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $obj->addPage();

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        $before = $font->getCurrentFont();

        $txt = 'UNBREAKABLETOKENUNBREAKABLETOKENUNBREAKABLETOKENUNBREAKABLETOKEN';
        $out = $obj->getTextCell(
            $txt,
            10,
            20,
            30,
            5,
            0,
            0,
            'T',
            'L',
            null,
            [],
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            true,
            '',
            null,
            'S',
        );

        $after = $font->getCurrentFont();
        $this->assertSame($before['stretching'], $after['stretching']);
        $this->assertStringContainsString(' Tz ', $out);
    }

    /** @throws \Throwable */
    public function testAddTextCellFitModesMatchGetTextCellAndRestoreFontState(): void
    {
        $txt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
        $styles = [
            'all' => [
                'lineWidth' => 0.3,
                'lineCap' => 'butt',
                'lineJoin' => 'miter',
                'dashArray' => [],
                'dashPhase' => 0,
                'lineColor' => '#4a5b70',
                'fillColor' => '#ffffff',
            ],
        ];

        foreach (['', 'T', 'S', 'F'] as $fit) {
            $getObj = $this->getTestObject();
            $this->initFont($getObj);
            $getObj->addPage();

            $expected = $getObj->getTextCell(
                $txt,
                10,
                20,
                80,
                10,
                0,
                0,
                'T',
                'L',
                null,
                $styles,
                0,
                0,
                0,
                0,
                true,
                true,
                false,
                false,
                false,
                false,
                false,
                false,
                '',
                null,
                $fit,
            );

            $addObj = $this->getTestObject();
            $this->initFont($addObj);
            $page = $addObj->addPage();
            $pid = $this->requirePageId($page);

            /** @var \Com\Tecnick\Pdf\Font\Stack $font */
            $font = $this->getObjectProperty($addObj, 'font');
            $before = $font->getCurrentFont();

            /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
            $pageObj = $this->getObjectProperty($addObj, 'page');
            $beforeContent = $pageObj->getPage($pid)['content'];
            $beforeCount = \count($beforeContent);

            $addObj->addTextCell(
                $txt,
                $pid,
                10,
                20,
                80,
                10,
                0,
                0,
                'T',
                'L',
                null,
                $styles,
                0,
                0,
                0,
                0,
                true,
                true,
                false,
                false,
                false,
                false,
                false,
                false,
                '',
                null,
                $fit,
            );

            $after = $font->getCurrentFont();
            $content = $pageObj->getPage($pid)['content'];
            $this->assertGreaterThanOrEqual($beforeCount, \count($content));
            $actual = \implode('', \array_slice($content, $beforeCount));

            $this->assertSame($before['size'], $after['size']);
            $this->assertSame($before['stretching'], $after['stretching']);
            $this->assertSame($expected, $actual);
        }

        $seqObj = $this->getTestObject();
        $this->initFont($seqObj);
        $seqPage = $seqObj->addPage();
        $seqPid = $this->requirePageId($seqPage);

        /** @var \Com\Tecnick\Pdf\Page\Page $seqPageObj */
        $seqPageObj = $this->getObjectProperty($seqObj, 'page');

        $seqObj->addTextCell(
            $txt,
            $seqPid,
            10,
            20,
            80,
            10,
            0,
            0,
            'T',
            'L',
            null,
            $styles,
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            false,
            '',
            null,
            'F',
        );

        $contentBeforeDisabled = $seqPageObj->getPage($seqPid)['content'];
        $beforeDisabledCount = \count($contentBeforeDisabled);

        $disabledTxt = 'Disabled mode should use base font.';
        $seqObj->addTextCell(
            $disabledTxt,
            $seqPid,
            10,
            40,
            80,
            10,
            0,
            0,
            'T',
            'L',
            null,
            $styles,
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            false,
            '',
            null,
            '',
        );

        $contentAfterDisabled = $seqPageObj->getPage($seqPid)['content'];
        $this->assertGreaterThan($beforeDisabledCount, \count($contentAfterDisabled));
        $disabledChunk = \implode('', \array_slice($contentAfterDisabled, $beforeDisabledCount));

        $expectedObj = $this->getTestObject();
        $this->initFont($expectedObj);
        $expectedObj->addPage();
        $expectedDisabled = $expectedObj->getTextCell(
            $disabledTxt,
            10,
            40,
            80,
            10,
            0,
            0,
            'T',
            'L',
            null,
            $styles,
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            false,
            '',
            null,
            '',
        );

        $this->assertSame($expectedDisabled, $disabledChunk);
    }

    /** @throws \Throwable */
    public function testGetTextCellFitStretchIgnoredWhenCompressionCannotReduceOverflow(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $obj->addPage();

        // "Short" easily fits in 80mm width, but the 2mm height is too small even for a single
        // line. Horizontal compression cannot reduce the line count, so fit=S must be a no-op.
        $txt = 'Short';
        $out = $obj->getTextCell(
            $txt,
            10,
            20,
            80,
            2,
            0,
            0,
            'T',
            'L',
            null,
            [],
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            true,
            '',
            null,
            'S',
        );

        $this->assertStringNotContainsString(' Tz ', $out);
    }

    /** @throws \Throwable */
    public function testGetTextCellFitStretchAppliesCompressionOnHeightOverflowFromWrapping(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $obj->addPage();

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        $before = $font->getCurrentFont();

        // Long text that wraps to multiple lines within 160mm but overflows the 6mm height.
        // fit=S should apply horizontal compression to reduce line count.
        $txt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
        $out = $obj->getTextCell(
            $txt,
            10,
            20,
            160,
            6,
            0,
            0,
            'T',
            'L',
            null,
            [],
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            true,
            '',
            null,
            'S',
        );

        $after = $font->getCurrentFont();
        $this->assertSame($before['stretching'], $after['stretching']);
        $this->assertStringContainsString(' Tz ', $out);
    }

    /** @throws \Throwable */
    public function testGetTextCellFitFontSizeRestoresFontState(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $obj->addPage();

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        $before = $font->getCurrentFont();

        $txt = 'Font-size fitting should reduce the size when needed and restore it after rendering.';
        $out = $obj->getTextCell(
            $txt,
            10,
            20,
            40,
            6,
            0,
            0.5,
            'T',
            'L',
            null,
            [],
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            true,
            '',
            null,
            'F',
        );
        $bbox = $obj->getLastTextBBox();

        $after = $font->getCurrentFont();
        $this->assertSame($before['size'], $after['size']);
        $this->assertStringContainsString(' Tf', $out);
        $this->assertGreaterThanOrEqual(2, \substr_count($out, ' Tf'));
        $this->assertLessThanOrEqual(6.0 + 0.01, $bbox['h']);
    }

    /** @throws \Throwable */
    public function testAddTextCellAppendsContentToPage(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $page = $obj->addPage();
        $pid = $this->requirePageId($page);

        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($obj, 'page');
        /** @var array<int, string> $before */
        $before = $pageObj->getPage($pid)['content'];

        $obj->addTextCell('Hello', $pid, 1, 2, 20, 6, 0, 0, 'T', 'L');

        /** @var array<int, string> $after */
        $after = $pageObj->getPage($pid)['content'];
        $this->assertGreaterThan(\count($before), \count($after));
        $lastKey = \array_key_last($after);
        $this->assertIsInt($lastKey);
        $lastValue = $after[$lastKey] ?? null;
        $this->assertIsString($lastValue);
        $this->assertNotSame('', $lastValue);
    }

    /** @throws \Throwable */
    public function testGetLastTextBBoxAndCellBBoxUpdatedByGetTextCell(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $obj->addPage();

        $obj->getTextCell('Hello world', 10, 20, 40, 12, 0, 0, 'T', 'L');

        $textbbox = $obj->getLastTextBBox();
        $cellbbox = $obj->getLastCellBBox();

        $this->assertGreaterThan(0.0, $textbbox['w']);
        $this->assertGreaterThan(0.0, $textbbox['h']);
        $this->assertGreaterThan(0.0, $cellbbox['w']);
        $this->assertGreaterThan(0.0, $cellbbox['h']);
    }

    /** @throws \Throwable */
    public function testGetLastCellBBoxUpdatedByAddTextCell(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $page = $obj->addPage();
        $pid = $this->requirePageId($page);

        $obj->addTextCell('Hello world', $pid, 10, 20, 40, 12, 0, 0, 'T', 'L');

        $cellbbox = $obj->getLastCellBBox();
        $textbbox = $obj->getLastTextBBox();
        $this->assertGreaterThan(0.0, $cellbbox['w']);
        $this->assertGreaterThan(0.0, $cellbbox['h']);
        $this->assertGreaterThan(0.0, $textbbox['w']);
        $this->assertGreaterThan(0.0, $textbbox['h']);
    }

    /** @throws \Throwable */
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
        $this->assertStringContainsString('TD raw', $obj->exposeGetOutTextPosXY('raw', 1, 2, 'TD'));
        $this->assertSame('T* raw', $obj->exposeGetOutTextPosXY('raw', 0, 0, 'T*'));
        $this->assertSame('', $obj->exposeGetOutTextPosXY('raw', 0, 0, 'NOPE'));

        /** @var array<int, array{0: callable(TestableText): string, 1: callable(TestableText): string, 2: string}>
         * $stateOperatorCases
         */
        $stateOperatorCases = [
            [
                static fn(TestableText $txtObj): string => $txtObj->exposeGetOutTextStateOperatorTc('raw', 0),
                static fn(TestableText $txtObj): string => $txtObj->exposeGetOutTextStateOperatorTc('raw', 1.5),
                ' Tc raw 0 Tc',
            ],
            [
                static fn(TestableText $txtObj): string => $txtObj->exposeGetOutTextStateOperatorTw('raw', 0),
                static fn(TestableText $txtObj): string => $txtObj->exposeGetOutTextStateOperatorTw('raw', 2),
                ' Tw raw 0 Tw',
            ],
            [
                static fn(TestableText $txtObj): string => $txtObj->exposeGetOutTextStateOperatorTz('raw', 1),
                static fn(TestableText $txtObj): string => $txtObj->exposeGetOutTextStateOperatorTz('raw', 80),
                ' Tz raw 100 Tz',
            ],
            [
                static fn(TestableText $txtObj): string => $txtObj->exposeGetOutTextStateOperatorTL('raw', 0),
                static fn(TestableText $txtObj): string => $txtObj->exposeGetOutTextStateOperatorTL('raw', 10),
                ' TL raw 0 TL',
            ],
            [
                static fn(TestableText $txtObj): string => $txtObj->exposeGetOutTextStateOperatorTs('raw', 0),
                static fn(TestableText $txtObj): string => $txtObj->exposeGetOutTextStateOperatorTs('raw', 3),
                ' Ts raw 0 Ts',
            ],
        ];
        foreach ($stateOperatorCases as [$defaultCase, $customCase, $expectedFragment]) {
            $this->assertSame('raw', $defaultCase($obj));
            $this->assertStringContainsString($expectedFragment, $customCase($obj));
        }

        $this->assertSame('raw', $obj->exposeGetOutTextStateOperatorTr('raw', 99));
        $this->assertStringContainsString('2 Tr raw', $obj->exposeGetOutTextStateOperatorTr('raw', 2));
        $this->assertStringContainsString('0.000000 w raw', $obj->exposeGetOutTextStateOperatorw('raw', -1));

        $this->assertSame('', $obj->exposeGetOutTextPosMatrix('raw', [1, 2]));
        $this->assertStringContainsString(' Tm raw', $obj->exposeGetOutTextPosMatrix('raw', [1, 0, 0, 1, 10, 20]));
        $this->assertSame('(abc) Tj', $obj->exposeGetOutTextShowing('abc', 'Tj'));
        $this->assertSame('[(abc)] TJ', $obj->exposeGetOutTextShowing('abc', 'TJ'));
        $this->assertSame("(abc) '", $obj->exposeGetOutTextShowing('abc', "'"));
        $this->assertSame('', $obj->exposeGetOutTextShowing('abc', 'X'));
        $this->assertSame("BT xyz ET\n", $obj->exposeGetOutTextObject('xyz'));
    }

    /** @throws \Throwable */
    public function testTextCleanupHyphenationAndEscapingHelpers(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame('A B C', $obj->exposeCleanupText("A\rB\u{00A0}C\u{00AD}"));
        $this->assertSame([65, 173], \array_values($obj->exposeRemoveOrdArrSoftHyphens([65, 173, 8203, 173])));
        $this->assertSame([36, 8203, 65], $obj->exposeAddOrdArrBreakPoints([36, 65]));
        $this->assertSame([65, 66], $obj->exposeReplaceUnicodeChars([65, 66]));
        $this->assertSame('100%% ready', $obj->exposeEscapePerc('100% ready'));
    }

    /** @throws \Throwable */
    public function testSetPageContextAndStringWidthHelpers(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $page = $obj->addPage();
        $pid = $this->requirePageId($page);

        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($obj, 'page');
        /** @var array<int, string> $before */
        $before = $pageObj->getPage($pid)['content'];
        $obj->exposeSetPageContext($pid);
        /** @var array<int, string> $after */
        $after = $pageObj->getPage($pid)['content'];

        $this->assertGreaterThan(\count($before), \count($after));
        $this->assertSame(0.0, $obj->exposeGetStringWidth(''));
        $this->assertGreaterThan(0, $obj->exposeGetStringWidth('Hello'));
    }

    /** @throws \Throwable */
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

        [$textWithNewline, $ordarrWithNewline, $dimWithNewline] = $obj->exposePrepareText("Cell Borders\nnextline");
        $newlineLines = $obj->exposeSplitLines($ordarrWithNewline, $dimWithNewline, 1000);
        $this->assertSame("Cell Borders\nnextline", $textWithNewline);
        $this->assertCount(2, $newlineLines);
    }

    /** @throws \Throwable */
    public function testGetOutTextLineAndOutTextLineRenderFromPreparedText(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        [$txt, $ordarr, $dim] = $obj->exposePrepareText('Hello world');

        $this->assertSame('', $obj->exposeGetOutTextLine('', [], []));
        $this->assertSame('', $obj->exposeOutTextLine('', [], []));

        $raw = $obj->exposeGetOutTextLine(
            $txt,
            $ordarr,
            $dim,
            1,
            2,
            0,
            0,
            0,
            0,
            0,
            true,
            false,
            true,
            true,
            true,
            false,
        );
        $out = $obj->exposeOutTextLine($txt, $ordarr, $dim, 1, 2, 0, 0, 0, 0, 0, true, false, true, true, true, false);

        $this->assertStringContainsString('BT ', $raw);
        $this->assertStringContainsString(' ET', $raw);
        $this->assertStringContainsString('re f', $out);
    }

    /** @throws \Throwable */
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

    /** @throws \Throwable */
    public function testTextAdditionalBranchesForCoverage(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $page = $obj->addPage();

        $cellNoBox = $obj->getTextCell(
            'NoBox',
            1,
            2,
            0,
            0,
            0,
            0,
            'T',
            'L',
            null,
            [],
            0,
            0,
            0,
            0,
            true,
            true,
            false,
            false,
            false,
            false,
            false,
            false,
        );
        $this->assertNotSame('', $cellNoBox);

        $obj->addTextCell('', -1, 1, 1, 0, 0, 0, 0, 'T', '');
        $obj->addTextCell('AutoAlign', -1, 1, 1, 0, 0, 0, 0, 'T', '');

        $this->assertSame('', $obj->exposeRawOutTextLines([], [], 0, 0, 0, 0, 0));

        $obj->setTexHyphenPatterns(['hyphen' => 'hy3phen']);
        $obj->enableZeroWidthBreakPoints(true);
        [, $ordarrPrepared] = $obj->exposePrepareText('hyphen,word');
        $this->assertNotEmpty($ordarrPrepared);

        $ordarr = $obj->exposeHyphenateWordOrdArr(['testing' => 'te3st2ing'], $obj->exposeStrToOrdArr('testing'));
        $dim = $obj->exposeGetOrdArrDims($ordarr);
        $lines = $obj->exposeSplitLines($ordarr, $dim, 5);
        $this->assertNotEmpty($lines);

        $this->setObjectProperty($obj, 'isunicode', true);
        $justOrdArr = $obj->exposeStrToOrdArr('word word');
        $justDim = $obj->exposeGetOrdArrDims($justOrdArr);
        $just = $obj->exposeGetJustifiedString('word word', $justOrdArr, $justDim, 20);
        $this->assertNotSame('', $just);

        $tmp = \tempnam(\sys_get_temp_dir(), 'tc-hyp-');
        $this->assertNotFalse($tmp);
        \file_put_contents($tmp, "\\patterns{\n\n hy4phen   test1ing \n}");
        $parsed = $obj->loadTexHyphenPatterns($tmp);
        if (\file_exists($tmp)) {
            \unlink($tmp);
        }
        $this->assertArrayHasKey('hyphen', $parsed);

        $this->setObjectProperty($obj, 'defPageContentEnabled', true);
        $pid = $this->requirePageId($page);
        $obj->exposeSetPageContext($pid);
        $defaultOut = $obj->defaultPageContent();
        $this->assertStringContainsString('BT', $defaultOut);
    }

    /** @throws \Throwable */
    public function testTextRemainingBranchCoverageBatch(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        $obj->getTextLine('Hello', 10, 20, 0, 0, 0, 0, 0, true, false, false, false, false, false, '', 'M');
        $middleLtr = $obj->getLastBBox();
        $obj->getTextLine('Hello', 10, 20, 0, 0, 0, 0, 0, true, false, false, false, false, false, 'R', 'M');
        $middleRtl = $obj->getLastBBox();
        $obj->getTextLine('Hello', 10, 20, 0, 0, 0, 0, 0, true, false, false, false, false, false, '', 'E');
        $endLtr = $obj->getLastBBox();
        $obj->getTextLine('Hello', 10, 20, 0, 0, 0, 0, 0, true, false, false, false, false, false, 'R', 'E');
        $endRtl = $obj->getLastBBox();

        $this->assertLessThan(10, $middleLtr['x'] ?? 0);
        $this->assertGreaterThan(10, $middleRtl['x'] ?? 0);
        $this->assertLessThan(10, $endLtr['x'] ?? 0);
        $this->assertGreaterThan(10, $endRtl['x'] ?? 0);

        $shadow = [
            'xoffset' => -1.5,
            'yoffset' => -2.0,
            'opacity' => 0.5,
            'mode' => 'Normal',
            'color' => 'gray',
        ];
        $shadowOut = $obj->getTextLine(
            'Hello world',
            5,
            6,
            0,
            0,
            0,
            0,
            0,
            true,
            false,
            false,
            false,
            false,
            false,
            '',
            '',
            $shadow,
        );
        $this->assertSame(2, \substr_count($shadowOut, 'BT '));
        $this->assertStringContainsString('/GS', $shadowOut);
        $soft = $obj->exposeStrToOrdArr("test\u{00AD}ing words");
        $softDim = $obj->exposeGetOrdArrDims($soft);
        $softLines = $obj->exposeSplitLines($soft, $softDim, 5);
        $this->assertGreaterThan(1, \count($softLines));
        $lastIndex = \count($softLines) - 1;
        $this->assertGreaterThanOrEqual(0, $lastIndex);
        $lastLine = $softLines[$lastIndex] ?? null;
        $this->assertIsArray($lastLine);
        $this->assertGreaterThan(0, $lastLine['chars']);

        $this->assertSame('', $obj->exposeRawGetOutTextLine('Hello', [], []));
        $this->assertSame('', $obj->exposeRawOutTextLine('Hello', [], []));
        $this->assertSame('', $obj->exposeRawGetOutTextPosMatrix('raw', [1, 2]));

        $unicode = $this->getInternalTestObject();
        $this->initUnicodeFont($unicode);
        $unicode->addPage();
        $this->setObjectProperty($unicode, 'isunicode', true);

        [$unicodeText, $unicodeOrdArr, $unicodeDim] = $unicode->exposePrepareText("A \u{05D0} B", 'R');
        $this->assertNotEmpty($unicodeOrdArr);
        $this->assertGreaterThan(0, $unicodeDim['totwidth']);

        $unicodePlain = $unicode->exposeGetJustifiedString($unicodeText, $unicodeOrdArr, $unicodeDim, 0);
        $unicodeJustified = $unicode->exposeGetJustifiedString($unicodeText, $unicodeOrdArr, $unicodeDim, 40);
        $this->assertStringContainsString('Tj', $unicodePlain);
        $this->assertStringContainsString('TJ', $unicodeJustified);

        $invalid = \tempnam(\sys_get_temp_dir(), 'tc-hyp-invalid-');
        $this->assertNotFalse($invalid);
        \file_put_contents($invalid, "% comment only\n\\patternsMissing{hy4phen}");

        try {
            $obj->loadTexHyphenPatterns($invalid);
            $this->fail('Expected invalid hyphenation pattern section exception');
        } catch (\Com\Tecnick\Pdf\Exception $e) {
            $this->assertStringContainsString('Invalid hyphenation pattern section', $e->getMessage());
        } finally {
            if (\file_exists($invalid)) {
                \unlink($invalid);
            }
        }
    }

    /** @throws \Throwable */
    public function testPdfUaActualTextLigatureHelpersAndTagging(): void
    {
        $obj = $this->getInternalTestObject();

        $noLigature = $obj->exposeGetActualTextForOrdarr($obj->exposeStrToOrdArr('office'));
        $this->assertSame('', $noLigature);

        $this->assertSame('fi', $obj->exposeGetActualTextForOrdarr([0xFB01]));
        $this->assertSame('ffi', $obj->exposeGetActualTextForOrdarr([0xFB03]));

        $mixed = $obj->exposeGetActualTextForOrdarr([0x0061, 0xFB01, 0x0062]);
        $this->assertSame('afib', $mixed);

        $formatted = $obj->exposeFormatPdfUaActualText('fi');
        $this->assertSame('<feff00660069>', $formatted);

        $pdfua = new TestableText('mm', true, false, true, 'pdfua');
        $withActual = $pdfua->exposeTagPdfUaTextContent("BT (x) Tj ET\n", 0, 'fi');
        $withoutActual = $pdfua->exposeTagPdfUaTextContent("BT (x) Tj ET\n", 0);

        $this->assertStringContainsString('/P <</MCID 0 /ActualText <feff00660069>>> BDC', $withActual);
        $this->assertStringContainsString('EMC', $withActual);
        $this->assertStringContainsString('/P <</MCID 1>> BDC', $withoutActual);
        $this->assertStringNotContainsString('/ActualText', $withoutActual);

        $multiLine = "BT (line1) Tj ET\nBT (line2) Tj ET\n";
        $wrappedMultiLine = $pdfua->exposeTagPdfUaTextContent($multiLine, 0);

        $this->assertSame(1, \substr_count($wrappedMultiLine, '/P <</MCID 2>> BDC'));
        $this->assertSame(1, \substr_count($wrappedMultiLine, 'EMC'));
        $this->assertStringContainsString("BT (line1) Tj ET\nBT (line2) Tj ET\n", $wrappedMultiLine);
    }

    /** @throws \Throwable */
    public function testGetTextLineOmitsShadowAlphaInPdfx3(): void
    {
        $obj = new TestableText('mm', true, false, true, 'pdfx3');
        $this->initFont($obj);
        $obj->addPage();

        $shadow = [
            'xoffset' => -1.5,
            'yoffset' => -2.0,
            'opacity' => 0.5,
            'mode' => 'Normal',
            'color' => 'gray',
        ];

        $shadowOut = $obj->getTextLine(
            'Hello world',
            5,
            6,
            0,
            0,
            0,
            0,
            0,
            true,
            false,
            false,
            false,
            false,
            false,
            '',
            '',
            $shadow,
        );

        $this->assertSame(2, \substr_count($shadowOut, 'BT '));
        $this->assertStringNotContainsString('/GS', $shadowOut);
    }
}
