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
    public function testSetCurrentPageMovesToRequestedPageAndReturnsPageData(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);

        $first = $obj->addPage([
            'orientation' => 'P',
            'format' => 'A4',
        ]);
        $second = $obj->addPage([
            'orientation' => 'L',
            'format' => 'A4',
        ]);

        $firstPid = $this->requirePageId($first);
        $secondPid = $this->requirePageId($second);
        $this->assertNotSame($firstPid, $secondPid);

        $page = $obj->setCurrentPage($firstPid);

        $this->assertSame($firstPid, $page['pid']);
        if (!isset($first['width'], $first['height'], $page['width'], $page['height'])) {
            $this->fail('Expected page width/height to be available after setCurrentPage().');
        }

        $this->assertSame($first['width'], $page['width']);
        $this->assertSame($first['height'], $page['height']);

        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($obj, 'page');
        $this->assertSame($firstPid, $pageObj->getPageId());

        $current = $obj->setCurrentPage();
        $this->assertSame($firstPid, $current['pid']);
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
    public function testGetTextCellFitFontSizeUsesHeuristicSearchForHyphenatedLongWord(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();
        $obj->setTexHyphenPatterns([
            'a' => 'a1',
            'b' => 'b1',
            'c' => 'c1',
            'd' => 'd1',
            'e' => 'e1',
            'f' => 'f1',
            'g' => 'g1',
            'h' => 'h1',
            'i' => 'i1',
            'j' => 'j1',
            'k' => 'k1',
            'l' => 'l1',
            'm' => 'm1',
            'n' => 'n1',
            'o' => 'o1',
            'p' => 'p1',
            'q' => 'q1',
            'r' => 'r1',
            's' => 's1',
            't' => 't1',
            'u' => 'u1',
            'v' => 'v1',
            'w' => 'w1',
            'x' => 'x1',
            'y' => 'y1',
            'z' => 'z1',
        ]);

        $prepared = $obj->exposePrepareText(
            'Loremipsumdolorsitametconsecteturadipiscingelitseddoeiusmodtemporincididuntutlaboreetdoloremagnaaliqua.',
        );
        $ordarr = $prepared[1];
        $fit = $obj->exposeFitTextCellByFontSize($ordarr, 160.0, 5.0, 0.0, 0.0);

        $this->assertTrue($fit['fontchanged']);
        $this->assertGreaterThan(150.0, $fit['layout']['maxwidth']);
        $this->assertStringContainsString('4.000000 Tf', $fit['fontout']);
    }

    /** @throws \Throwable */
    public function testFitTextCellByFontSizeReturnsBaseWhenLayoutAlreadyFits(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        [, $ordarr] = $obj->exposePrepareText('fit');
        $fit = $obj->exposeFitTextCellByFontSize($ordarr, 200.0, 200.0, 0.0, 0.0);

        $this->assertFalse($fit['fontchanged']);
        $this->assertSame('', $fit['fontout']);
    }

    /** @throws \Throwable */
    public function testFitTextCellByFontSizeStopsWhenCurrentSizeIsMinimum(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $curfont = $font->getCurrentFont();
        $font->cloneFont($pon, $curfont['idx'], null, 4.0);

        [, $ordarr] = $obj->exposePrepareText('A long word that cannot fit inside a tiny box');
        $fit = $obj->exposeFitTextCellByFontSize($ordarr, 5.0, 2.0, 0.0, 0.0);

        $this->assertFalse($fit['fontchanged']);
        $this->assertSame('', $fit['fontout']);
    }

    /** @throws \Throwable */
    public function testFitTextCellByStretchReturnsBaseForNoOverflowAndHeightOnlyOverflow(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        [, $ordarr, $dim] = $obj->exposePrepareText('short text');

        $noOverflow = $obj->exposeFitTextCellByStretch($ordarr, $dim, 300.0, 300.0, 0.0, 0.0);
        $heightOnlyOverflow = $obj->exposeFitTextCellByStretch($ordarr, $dim, 300.0, 0.1, 0.0, 0.0);

        $this->assertFalse($noOverflow['fontchanged']);
        $this->assertFalse($heightOnlyOverflow['fontchanged']);
        $this->assertSame(300.0, $noOverflow['linewidth']);
        $this->assertSame(300.0, $heightOnlyOverflow['linewidth']);
    }

    /** @throws \Throwable */
    public function testFitTextCellByFontSizeFallbackProbeBranchWithTestDouble(): void
    {
        $obj = new class extends TestableText {
            private int $probeCalls = 0;

            /** @phpstan-return array{lines: array<int, array{pos:int, chars:int, spaces:int, septype:string, totwidth:float, totspacewidth:float, words:int}>, maxwidth: float, txtheight: float} */
            protected function getTextCellLayout(
                array $ordarr,
                array $dim,
                float $splitWidth,
                float $displayWidth,
                float $scale,
                float $offsetPoints,
                float $lineSpacePoints,
            ): array {
                return [
                    'lines' => [[
                        'pos' => 0,
                        'chars' => 1,
                        'spaces' => 0,
                        'septype' => 'BN',
                        'totwidth' => 100.0,
                        'totspacewidth' => 0.0,
                        'words' => 1,
                    ]],
                    'maxwidth' => 100.0,
                    'txtheight' => 100.0,
                ];
            }

            /** @phpstan-return array{size: float, dim: array{chars: int, spaces: int, words: int, totwidth: float, totspacewidth: float, split: array<int, array{pos: int, ord: int, septype: string, spaces: int, totwidth: float, totspacewidth: float, wordwidth: float}>}, layout: array{lines: array<int, array{pos:int, chars:int, spaces:int, septype:string, totwidth:float, totspacewidth:float, words:int}>, maxwidth: float, txtheight: float}, fits: bool} */
            protected function probeTextCellFontSizeFit(
                array $ordarr,
                float $size,
                float $maxWidth,
                float $maxHeight,
                float $offsetPoints,
                float $lineSpacePoints,
            ): array {
                ++$this->probeCalls;
                if ($this->probeCalls === 1) {
                    return [
                        'size' => $size,
                        'dim' => self::DIM_DEFAULT,
                        'layout' => ['lines' => [], 'maxwidth' => 100.0, 'txtheight' => 100.0],
                        'fits' => false,
                    ];
                }

                if ($this->probeCalls === 2) {
                    return [
                        'size' => $size,
                        'dim' => self::DIM_DEFAULT,
                        'layout' => ['lines' => [], 'maxwidth' => 1.0, 'txtheight' => 1.0],
                        'fits' => true,
                    ];
                }

                $fits = ($this->probeCalls % 2) === 0;
                return [
                    'size' => $size,
                    'dim' => self::DIM_DEFAULT,
                    'layout' => ['lines' => [], 'maxwidth' => $fits ? 1.0 : 100.0, 'txtheight' => $fits ? 1.0 : 100.0],
                    'fits' => $fits,
                ];
            }

            /**
             * @phpstan-param array<int, int> $ordarr
             * @phpstan-return array{fontchanged: bool, fontout: string, dim: array{chars: int, spaces: int, words: int, totwidth: float, totspacewidth: float, split: array<int, array{pos: int, ord: int, septype: string, spaces: int, totwidth: float, totspacewidth: float, wordwidth: float}>}, layout: array{lines: array<int, array{pos:int, chars:int, spaces:int, septype:string, totwidth:float, totspacewidth:float, words:int}>, maxwidth: float, txtheight: float}}
             * @throws \Com\Tecnick\Pdf\Font\Exception
             */
            public function runFit(array $ordarr): array
            {
                return $this->fitTextCellByFontSize($ordarr, 10.0, 10.0, 0.0, 0.0);
            }
        };

        $this->initFont($obj);
        $obj->addPage();
        $result = $obj->runFit([65]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('fontchanged', $result);
    }

    /** @throws \Throwable */
    public function testFitTextCellByFontSizeNoChangeBranchWithTestDouble(): void
    {
        $obj = new class extends TestableText {
            /** @phpstan-return array{lines: array<int, array{pos:int, chars:int, spaces:int, septype:string, totwidth:float, totspacewidth:float, words:int}>, maxwidth: float, txtheight: float} */
            protected function getTextCellLayout(
                array $ordarr,
                array $dim,
                float $splitWidth,
                float $displayWidth,
                float $scale,
                float $offsetPoints,
                float $lineSpacePoints,
            ): array {
                return [
                    'lines' => [[
                        'pos' => 0,
                        'chars' => 1,
                        'spaces' => 0,
                        'septype' => 'BN',
                        'totwidth' => 100.0,
                        'totspacewidth' => 0.0,
                        'words' => 1,
                    ]],
                    'maxwidth' => 100.0,
                    'txtheight' => 100.0,
                ];
            }

            /** @phpstan-return array{size: float, dim: array{chars: int, spaces: int, words: int, totwidth: float, totspacewidth: float, split: array<int, array{pos: int, ord: int, septype: string, spaces: int, totwidth: float, totspacewidth: float, wordwidth: float}>}, layout: array{lines: array<int, array{pos:int, chars:int, spaces:int, septype:string, totwidth:float, totspacewidth:float, words:int}>, maxwidth: float, txtheight: float}, fits: bool} */
            protected function probeTextCellFontSizeFit(
                array $ordarr,
                float $size,
                float $maxWidth,
                float $maxHeight,
                float $offsetPoints,
                float $lineSpacePoints,
            ): array {
                return [
                    'size' => 10.0,
                    'dim' => self::DIM_DEFAULT,
                    'layout' => ['lines' => [], 'maxwidth' => 100.0, 'txtheight' => 100.0],
                    'fits' => true,
                ];
            }

            /**
             * @phpstan-param array<int, int> $ordarr
             * @phpstan-return array{fontchanged: bool, fontout: string, dim: array{chars: int, spaces: int, words: int, totwidth: float, totspacewidth: float, split: array<int, array{pos: int, ord: int, septype: string, spaces: int, totwidth: float, totspacewidth: float, wordwidth: float}>}, layout: array{lines: array<int, array{pos:int, chars:int, spaces:int, septype:string, totwidth:float, totspacewidth:float, words:int}>, maxwidth: float, txtheight: float}}
             * @throws \Com\Tecnick\Pdf\Font\Exception
             */
            public function runFit(array $ordarr): array
            {
                return $this->fitTextCellByFontSize($ordarr, 10.0, 10.0, 0.0, 0.0);
            }
        };

        $this->initFont($obj);
        $obj->addPage();
        $result = $obj->runFit([65]);
        $this->assertFalse($result['fontchanged']);
        $this->assertSame('', $result['fontout']);
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
    public function testAddTextCellImplicitCurrentPageTracksLastAutoBrokenPage(): void
    {
        $obj = $this->getTestObject();
        $this->initFont($obj);
        $firstPage = $obj->addPage([
            'region' => [
                ['RX' => 0.0, 'RY' => 0.0, 'RW' => 80.0, 'RH' => 6.0],
            ],
        ]);
        $firstPid = $this->requirePageId($firstPage);

        $txt = \str_repeat("Lorem ipsum dolor sit amet, consectetur adipiscing elit.\n", 40);
        $obj->addTextCell(
            $txt,
            -1,
            1,
            1,
            78,
            0,
            0,
            0,
            'T',
            'J',
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

        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($obj, 'page');
        $lastPid = $pageObj->getPageId();

        $this->assertGreaterThan($firstPid, $lastPid);
        $this->assertSame($lastPid, $pageObj->getPage()['pid']);
        $this->assertSame($lastPid, $obj->setCurrentPage()['pid']);
    }

    /** @throws \Throwable */
    public function testAddTextCellXYForwardsRegionRelativeCoordinates(): void
    {
        $obj = new class extends TestableText {
            /** @var array<string, mixed> */
            public array $captured = [];

            public function addTextCell(
                string $txt,
                int $pid = -1,
                float $posx = 0,
                float $posy = 0,
                float $width = 0,
                float $height = 0,
                float $offset = 0,
                float $linespace = 0,
                string $valign = 'T',
                string $halign = '',
                ?array $cell = null,
                array $styles = [],
                float $strokewidth = 0,
                float $wordspacing = 0,
                float $leading = 0,
                float $rise = 0,
                bool $jlast = true,
                bool $fill = true,
                bool $stroke = false,
                bool $underline = false,
                bool $linethrough = false,
                bool $overline = false,
                bool $clip = false,
                bool $drawcell = true,
                string $forcedir = '',
                ?array $shadow = null,
                string $fit = '',
            ): void {
                $this->captured = [
                    'txt' => $txt,
                    'pid' => $pid,
                    'posx' => $posx,
                    'posy' => $posy,
                    'width' => $width,
                    'height' => $height,
                    'offset' => $offset,
                    'linespace' => $linespace,
                    'valign' => $valign,
                    'halign' => $halign,
                    'cell' => $cell,
                    'styles' => $styles,
                    'strokewidth' => $strokewidth,
                    'wordspacing' => $wordspacing,
                    'leading' => $leading,
                    'rise' => $rise,
                    'jlast' => $jlast,
                    'fill' => $fill,
                    'stroke' => $stroke,
                    'underline' => $underline,
                    'linethrough' => $linethrough,
                    'overline' => $overline,
                    'clip' => $clip,
                    'drawcell' => $drawcell,
                    'forcedir' => $forcedir,
                    'shadow' => $shadow,
                    'fit' => $fit,
                ];
            }
        };

        $this->initFont($obj);
        $page = $obj->addPage([
            'region' => [
                [
                    'RX' => 15.0,
                    'RY' => 20.0,
                    'RW' => 120.0,
                    'RH' => 80.0,
                ],
            ],
        ]);
        $pid = $this->requirePageId($page);

        $obj->addTextCellXY('Hello', $pid, 40.0, 55.0, 20.0, 6.0, 1.5, 2.5, 'B', 'R');

        $this->assertSame(
            [
                'txt' => 'Hello',
                'pid' => $pid,
                'posx' => 25.0,
                'posy' => 35.0,
                'width' => 20.0,
                'height' => 6.0,
                'offset' => 1.5,
                'linespace' => 2.5,
                'valign' => 'B',
                'halign' => 'R',
                'cell' => null,
                'styles' => [],
                'strokewidth' => 0.0,
                'wordspacing' => 0.0,
                'leading' => 0.0,
                'rise' => 0.0,
                'jlast' => true,
                'fill' => true,
                'stroke' => false,
                'underline' => false,
                'linethrough' => false,
                'overline' => false,
                'clip' => false,
                'drawcell' => true,
                'forcedir' => '',
                'shadow' => null,
                'fit' => '',
            ],
            $obj->captured,
        );
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
        $trailingWordText = $obj->exposeStrToOrdArr('hyphen,word');
        $hypWord = $obj->exposeHyphenateWordOrdArr($patterns, $word);
        $hypText = $obj->exposeHyphenateTextOrdArr($patterns, $text);
        $hypTrailingWordText = $obj->exposeHyphenateTextOrdArr($patterns, $trailingWordText);

        $this->assertNotEmpty($hypWord);
        $this->assertNotEmpty($hypText);
        $this->assertSame($obj->exposeStrToOrdArr('word'), \array_slice($hypTrailingWordText, -4));
        $this->assertSame($word, $obj->exposeHyphenateWordOrdArr([], $word));
    }

    /** @throws \Throwable */
    public function testOutTextLinesLowercaseAlignAndInlineWordSpacingBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        [, $ordarr] = $obj->exposePrepareText('a b c d');

        /** @var array<int, array{pos:int, chars:int, spaces:int, totwidth:float, totspacewidth:float, words:int, septype:string}> $lines */
        $lines = [
            [
                'pos' => 0,
                'chars' => 3,
                'spaces' => 1,
                'totwidth' => 10.0,
                'totspacewidth' => 2.0,
                'words' => 2,
                'septype' => 'S',
            ],
            [
                'pos' => 4,
                'chars' => 3,
                'spaces' => 1,
                'totwidth' => 10.0,
                'totspacewidth' => 2.0,
                'words' => 2,
                'septype' => 'S',
            ],
        ];

        $center = $obj->exposeOutTextLines(
            $ordarr,
            $lines,
            1.0,
            1.0,
            40.0,
            0.0,
            1.5,
            0.0,
            0.0,
            0.6,
            0.0,
            0.0,
            'c',
            false,
        );
        $right = $obj->exposeOutTextLines(
            $ordarr,
            $lines,
            1.0,
            1.0,
            40.0,
            0.0,
            1.5,
            0.0,
            0.0,
            0.6,
            0.0,
            0.0,
            'r',
            false,
        );
        $auto = $obj->exposeOutTextLines($ordarr, $lines, 1.0, 1.0, 40.0, 0.0, 1.5, 0.0, 0.0, 0.6, 0.0, 0.0, '', false);

        $this->assertStringContainsString('BT ', $center);
        $this->assertStringContainsString('BT ', $right);
        $this->assertStringContainsString('BT ', $auto);
    }

    /** @throws \Throwable */
    public function testLastBBoxHelpersFallbackToZeroForSparseArrayIndexes(): void
    {
        $obj = $this->getTestObject();

        /** @var array<string, float> $box */
        $box = ['x' => 1.0, 'y' => 2.0, 'w' => 3.0, 'h' => 4.0];
        $this->setObjectProperty($obj, 'bbox', [5 => $box]);
        $this->setObjectProperty($obj, 'textbbox', [7 => $box]);
        $this->setObjectProperty($obj, 'cellbbox', [9 => $box]);

        $zero = ['x' => 0.0, 'y' => 0.0, 'w' => 0.0, 'h' => 0.0];
        $this->assertSame($zero, $obj->getLastBBox());
        $this->assertSame($zero, $obj->getLastTextBBox());
        $this->assertSame($zero, $obj->getLastCellBBox());

        $this->setObjectProperty($obj, 'bbox', []);
        $this->setObjectProperty($obj, 'textbbox', []);
        $this->setObjectProperty($obj, 'cellbbox', []);
        $this->assertSame($zero, $obj->getLastBBox());
        $this->assertSame($zero, $obj->getLastTextBBox());
        $this->assertSame($zero, $obj->getLastCellBBox());
    }

    /** @throws \Throwable */
    public function testSplitLinesHandlesMissingSplitEntriesDefensively(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        /** @var array{chars:int, spaces:int, totwidth:float, totspacewidth:float, words:int, split:array<int, array{pos:int, ord:int, septype:string, spaces:int, totwidth:float, totspacewidth:float, wordwidth:float}>} $dim */
        $dim = [
            'chars' => 10,
            'spaces' => 1,
            'totwidth' => 20.0,
            'totspacewidth' => 2.0,
            'words' => 2,
            'split' => [
                0 => [
                    'pos' => 0,
                    'ord' => 32,
                    'septype' => 'WS',
                    'spaces' => 0,
                    'totwidth' => 5.0,
                    'totspacewidth' => 1.0,
                    'wordwidth' => 4.0,
                ],
            ],
        ];

        $lines = $obj->exposeSplitLines([65, 32, 66], $dim, 8.0);
        $this->assertIsArray($lines);
    }

    /** @throws \Throwable */
    public function testRawTextLineMethodsReturnEmptyOnEmptyOrdinalArray(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        /** @var array{chars:int, spaces:int, totwidth:float, totspacewidth:float, words:int, split:array<int, array{pos:int, ord:int, septype:string, spaces:int, totwidth:float, totspacewidth:float, wordwidth:float}>} $dim */
        $dim = [
            'chars' => 1,
            'spaces' => 0,
            'totwidth' => 1.0,
            'totspacewidth' => 0.0,
            'words' => 1,
            'split' => [],
        ];

        $this->assertSame('', $obj->exposeRawGetOutTextLine('X', [], $dim));
        $this->assertSame('', $obj->exposeRawOutTextLine('X', [], $dim));
    }

    /** @throws \Throwable */
    public function testGetOutTextPosMatrixReturnsEmptyForInvalidMatrixSizeViaReflection(): void
    {
        $obj = $this->getInternalTestObject();
        $method = new \ReflectionMethod(\Com\Tecnick\Pdf\Text::class, 'getOutTextPosMatrix');
        /** @var array{float, float, float, float, float, float} $invalid */
        $invalid = [1.0, 0.0, 0.0, 1.0, 0.0];
        $this->assertSame('', $method->invoke($obj, 'X', $invalid));
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

    /** @throws \Throwable */
    public function testPdfUaStructElementLifecycleAndNesting(): void
    {
        $plain = $this->getInternalTestObject();
        $plain->beginStructElem('P', 0, 'ignored');
        $plain->endStructElem();
        $this->assertSame([], $this->getObjectProperty($plain, 'pdfuaStructStack'));

        $obj = new TestableText('mm', true, false, true, 'pdfua');
        $this->initFont($obj);
        $page = $obj->addPage();
        $pid = $this->requirePageId($page);

        $obj->beginStructElem('Figure', $pid, 'Parent alt', ['Lang' => 'en-US']);
        $obj->beginStructElem('P', $pid);
        $obj->exposeTagPdfUaTextContent("BT (nested) Tj ET\n", $pid);
        $obj->endStructElem();
        $obj->endStructElem();

        /** @var array<int, array<string, mixed>> $log */
        $log = $this->getObjectProperty($obj, 'pdfuaStructLog');
        $this->assertCount(2, $log);
        $this->assertSame('P', $log[0]['role'] ?? null);
        $this->assertSame('Figure', $log[1]['role'] ?? null);
        $this->assertSame('Parent alt', $log[1]['alt'] ?? null);
        $this->assertSame(['Lang' => 'en-US'], $log[1]['attr'] ?? null);

        /** @var array<int, mixed> $kids */
        $kids = \is_array($log[1]['kids'] ?? null) ? $log[1]['kids'] : [];
        $this->assertNotEmpty($kids);
    }

    /** @throws \Throwable */
    public function testPdfUaFigureTaggingAndAnnotationRegistrationBranches(): void
    {
        $obj = new TestableText('mm', true, false, true, 'pdfua');
        $this->initFont($obj);
        $page = $obj->addPage();
        $pid = $this->requirePageId($page);

        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($obj, 'page');
        $beforeContent = $pageObj->getPage($pid)['content'];
        $beforeCount = \count($beforeContent);
        $obj->addTaggedFigureContent('', $pid, '');
        $afterEmpty = $pageObj->getPage($pid)['content'];
        $this->assertCount($beforeCount, $afterEmpty);

        $wrapped = $obj->exposeTagPdfUaFigureContent("q\nQ\n", $pid, 'Standalone figure');
        $this->assertStringContainsString('/Figure <</MCID', $wrapped);
        $this->assertStringContainsString('EMC', $wrapped);

        $obj->beginStructElem('Figure', $pid, 'Open figure');
        $obj->addTaggedFigureContent("q\nQ\n", $pid, 'ignored alt');
        $obj->exposeRegisterPdfUaAnnotation(42, $pid);

        /** @var array<int, array<string, mixed>> $stack */
        $stack = $this->getObjectProperty($obj, 'pdfuaStructStack');
        $topKey = \array_key_last($stack);
        $top = \is_int($topKey) && isset($stack[$topKey]) ? $stack[$topKey] : null;
        $this->assertIsArray($top);
        $this->assertSame([42], $top['annots'] ?? null);
        $this->assertSame('Open figure', $top['alt'] ?? null);

        $obj->endStructElem();

        $obj->exposeRegisterPdfUaAnnotation(77, $pid);

        /** @var array<int, array<string, mixed>> $log */
        $log = $this->getObjectProperty($obj, 'pdfuaStructLog');
        $lastKey = \array_key_last($log);
        $last = \is_int($lastKey) && isset($log[$lastKey]) ? $log[$lastKey] : null;
        $this->assertIsArray($last);
        $this->assertSame('Link', $last['role'] ?? null);
        $this->assertSame([77], $last['annots'] ?? null);
    }

    /** @throws \Throwable */
    public function testRemoveOrdArrSoftHyphensKeepsTrailingSoftHyphenOnlyWhenPresent(): void
    {
        $obj = $this->getInternalTestObject();

        $withoutTrailing = $obj->exposeRemoveOrdArrSoftHyphens([65, 173, 8203, 66]);
        $withTrailing = $obj->exposeRemoveOrdArrSoftHyphens([65, 8203, 173]);

        $this->assertSame([65, 66], $withoutTrailing);
        $this->assertSame([65, 173], $withTrailing);
    }

    /** @throws \Throwable */
    public function testTextBaseCleanupAndSoftHyphenRemovalImplementationsAreCallable(): void
    {
        $obj = $this->getInternalTestObject();

        $cleanupMethod = new \ReflectionMethod(\Com\Tecnick\Pdf\Text::class, 'cleanupText');
        $this->assertSame('A B C', $cleanupMethod->invoke($obj, "A\rB\u{00A0}C\u{00AD}"));

        $removeMethod = new \ReflectionMethod(\Com\Tecnick\Pdf\Text::class, 'removeOrdArrSoftHyphens');
        $this->assertSame([65, 66], $removeMethod->invoke($obj, [65, 173, 8203, 66]));
        $this->assertSame([65, 173], $removeMethod->invoke($obj, [65, 8203, 173]));
    }

    /** @throws \Throwable */
    public function testLoadTexHyphenPatternsSkipsEmptyPatternTokens(): void
    {
        $obj = $this->getInternalTestObject();
        $tmp = \tempnam(\sys_get_temp_dir(), 'tc-hyp-empty-');
        $this->assertNotFalse($tmp);

        \file_put_contents($tmp, "\\patterns{\n\n hy4phen      test1ing   \n}");
        $patterns = $obj->loadTexHyphenPatterns($tmp);
        if (\file_exists($tmp)) {
            \unlink($tmp);
        }

        $this->assertSame('hy4phen', $patterns['hyphen'] ?? null);
        $this->assertSame('test1ing', $patterns['testing'] ?? null);
    }

    /** @throws \Throwable */
    public function testFitTextCellByTruncationGuardBranchesViaReflection(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        $method = new \ReflectionMethod(\Com\Tecnick\Pdf\Text::class, 'fitTextCellByTruncation');
        $this->assertSame([], $method->invoke($obj, [], 20.0, 10.0, 0.0, 0.0));

        [, $shortOrdarr] = $obj->exposePrepareText('fit');
        $this->assertSame($shortOrdarr, $method->invoke($obj, $shortOrdarr, 300.0, 300.0, 0.0, 0.0));

        [, $longOrdarr] = $obj->exposePrepareText(
            'This sentence is intentionally long to force truncation checks inside tiny cells.',
        );
        $this->assertSame([], $method->invoke($obj, $longOrdarr, 20.0, 0.1, 0.0, 0.0));
    }

    /** @throws \Throwable */
    public function testPdfUaTaggingGuardsAndArtifactBranches(): void
    {
        $plain = $this->getInternalTestObject();
        $this->assertSame('q Q', $plain->exposeTagPdfUaFigureContent('q Q', 0, 'alt'));

        $obj = new TestableText('mm', true, false, true, 'pdfua');
        $this->initFont($obj);
        $page = $obj->addPage();
        $pid = $this->requirePageId($page);

        $this->assertSame('/Artifact BMC' . "\n", $obj->beginArtifact());

        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($obj, 'page');
        $beforeCount = \count($pageObj->getPage($pid)['content']);
        $obj->addArtifactContent('', $pid, 'Pagination', 'Footer');
        $afterEmptyCount = \count($pageObj->getPage($pid)['content']);
        $this->assertSame($beforeCount, $afterEmptyCount);

        $obj->addArtifactContent('q 0 0 1 rg Q', $pid, 'Pagination', 'Footer');
        $artifactContent = \implode("\n", $pageObj->getPage($pid)['content']);
        $this->assertStringContainsString('/Artifact << /Type /Pagination /Subtype /Footer >> BDC', $artifactContent);
        $this->assertStringContainsString('q 0 0 1 rg Q' . "\n", $artifactContent);

        $taggedInvalid = $obj->exposeTagPdfUaTextContent('q 1 0 0 1 0 0 cm Q', $pid, 'fi');
        $this->assertSame('q 1 0 0 1 0 0 cm Q', $taggedInvalid);

        $logBefore = \json_encode($this->getObjectProperty($obj, 'pdfuaStructLog'), \JSON_THROW_ON_ERROR);
        $obj->exposeRegisterPdfUaAnnotation(0, $pid);
        $logAfter = \json_encode($this->getObjectProperty($obj, 'pdfuaStructLog'), \JSON_THROW_ON_ERROR);
        $this->assertSame($logBefore, $logAfter);
    }

    /** @throws \Throwable */
    public function testPdfUaFigureTaggingAddsAltOnOpenFigureAndParentKid(): void
    {
        $obj = new TestableText('mm', true, false, true, 'pdfua');
        $this->initFont($obj);
        $page = $obj->addPage();
        $pid = $this->requirePageId($page);

        $obj->beginStructElem('Figure', $pid);
        $obj->exposeTagPdfUaFigureContent("q\nQ\n", $pid, 'late alt');
        /** @var array<int, array<string, mixed>> $stack */
        $stack = $this->getObjectProperty($obj, 'pdfuaStructStack');
        $figureKey = \array_key_last($stack);
        $figureTop = \is_int($figureKey) && isset($stack[$figureKey]) ? $stack[$figureKey] : null;
        $this->assertIsArray($figureTop);
        $this->assertSame('late alt', $figureTop['alt'] ?? null);
        $obj->endStructElem();

        $obj->beginStructElem('P', $pid);
        $obj->exposeTagPdfUaFigureContent("q\nQ\n", $pid, 'child figure');
        /** @var array<int, array<string, mixed>> $stack */
        $stack = $this->getObjectProperty($obj, 'pdfuaStructStack');
        $parentKey = \array_key_last($stack);
        $parent = \is_int($parentKey) && isset($stack[$parentKey]) ? $stack[$parentKey] : null;
        $this->assertIsArray($parent);
        /** @var array<int, mixed> $kids */
        $kids = \is_array($parent['kids'] ?? null) ? $parent['kids'] : [];
        $this->assertNotEmpty($kids);

        $inlineNoNl = $obj->exposeTagPdfUaFigureContent('q Q', $pid, 'inline');
        $this->assertStringContainsString("q Q\nEMC\n", $inlineNoNl);
        $obj->endStructElem();
    }

    /** @throws \Throwable */
    public function testEndStructElemReturnsWhenTopIndexIsMissing(): void
    {
        $obj = new TestableText('mm', true, false, true, 'pdfua');
        /** @var array<int, array<string, mixed>|null> $sparse */
        $sparse = [
            5 => null,
        ];
        $this->setObjectProperty($obj, 'pdfuaStructStack', $sparse);
        $obj->endStructElem();
        $this->assertSame([], $this->getObjectProperty($obj, 'pdfuaStructLog'));
    }

    /** @throws \Throwable */
    public function testSplitLinesSkipsMissingWordEntriesViaReflection(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $obj->addPage();

        $method = new \ReflectionMethod(\Com\Tecnick\Pdf\Text::class, 'splitLines');
        /** @var array<int, int> $ordarr */
        $ordarr = [65, 32, 66];
        /** @var array<string, mixed> $dim */
        $dim = [
            'chars' => 3,
            'spaces' => 1,
            'words' => 2,
            'totwidth' => 12.0,
            'totspacewidth' => 2.0,
            'split' => [
                1 => [
                    'pos' => 1,
                    'ord' => 32,
                    'septype' => 'WS',
                    'spaces' => 1,
                    'totwidth' => 6.0,
                    'totspacewidth' => 2.0,
                    'wordwidth' => 4.0,
                ],
            ],
        ];

        $this->assertIsArray($method->invoke($obj, $ordarr, $dim, 8.0, 0.0));
    }

    /** @throws \Throwable */
    public function testUnicodeJustifiedStringHandlesZeroFontSizeGuard(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initUnicodeFont($obj);
        $obj->addPage();
        $this->setObjectProperty($obj, 'isunicode', true);

        $markerMethod = new \ReflectionMethod(\Com\Tecnick\Pdf\Text::class, 'getTextCellTruncationMarkerOrdArr');
        $this->assertSame([0x2026], $markerMethod->invoke($obj));

        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $cur = $font->getCurrentFont();
        $font->cloneFont($pon, $cur['idx'], null, 0.0);

        [, $ordarr, $dim] = $obj->exposePrepareText("A \u{05D0} B", 'R');
        $just = $obj->exposeGetJustifiedString("A \u{05D0} B", $ordarr, $dim, 20.0);
        $this->assertStringContainsString('TJ', $just);
    }

    /** @throws \Throwable */
    public function testAddTextCellMultiBlockDrawCellUsesFillColorOnContinuationBorders(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $page = $obj->addPage([
            'region' => [
                ['RX' => 0.0, 'RY' => 0.0, 'RW' => 60.0, 'RH' => 4.0],
                ['RX' => 0.0, 'RY' => 6.0, 'RW' => 60.0, 'RH' => 4.0],
                ['RX' => 0.0, 'RY' => 12.0, 'RW' => 60.0, 'RH' => 4.0],
            ],
        ]);
        $pid = $this->requirePageId($page);

        $styles = [
            'all' => [
                'lineWidth' => 0.4,
                'lineColor' => '#111111',
                'fillColor' => '#00ff00',
                'lineCap' => 'butt',
                'lineJoin' => 'miter',
                'dashArray' => [],
                'dashPhase' => 0,
            ],
        ];

        $obj->addTextCell(
            'Long long long long long long long long long long long long long long long long long long long text',
            $pid,
            1,
            1,
            25,
            0,
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
            true,
        );

        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($obj, 'page');
        $content = \implode("\n", $pageObj->getPage($pid)['content']);
        $this->assertStringContainsString('0.000000 1.000000 0.000000 rg', $content);
    }

    /** @throws \Throwable */
    public function testAddTextCellSpansRegionsAndRestoresFontOutputSuffix(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFont($obj);
        $page = $obj->addPage([
            'region' => [
                ['RX' => 0.0, 'RY' => 0.0, 'RW' => 80.0, 'RH' => 6.0],
                ['RX' => 0.0, 'RY' => 8.0, 'RW' => 80.0, 'RH' => 6.0],
            ],
        ]);
        $pid = $this->requirePageId($page);

        /** @var \Com\Tecnick\Pdf\Page\Page $pageObj */
        $pageObj = $this->getObjectProperty($obj, 'page');
        $before = $pageObj->getPage($pid)['content'];
        $beforeCount = \count($before);

        $styles = [
            'all' => [
                'lineWidth' => 0.4,
                'lineCap' => 'butt',
                'lineJoin' => 'miter',
                'dashArray' => [],
                'dashPhase' => 0,
                'lineColor' => '#3a4a5a',
                'fillColor' => '#dddddd',
            ],
        ];

        $txt =
            'This is a long text block that must wrap across tiny regions to exercise multi-block handling.'
            . ' The renderer should continue to the next region and still close with restored font output.';

        $obj->addTextCell(
            $txt,
            $pid,
            1,
            1,
            78,
            0,
            0,
            0,
            'T',
            'J',
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
            true,
            '',
            null,
            'F',
        );

        /** @var array<int, string> $after */
        $after = $pageObj->getPage($pid)['content'];
        $this->assertGreaterThan($beforeCount, \count($after));
        $chunk = \implode('', \array_slice($after, $beforeCount));
        $this->assertStringContainsString('BT ', $chunk);

        /** @var array<int, array{x: float, y: float, w: float, h: float}> $cellBoxes */
        $cellBoxes = $this->getObjectProperty($obj, 'cellbbox');
        $this->assertGreaterThan(1, \count($cellBoxes));
    }
}
