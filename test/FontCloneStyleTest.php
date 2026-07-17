<?php

/**
 * FontCloneStyleTest.php
 *
 * @since       2026-07-12
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

/**
 * Regression tests for the font style cloning.
 *
 * Com\Tecnick\Pdf\Font\Stack::cloneFont() used to forward the definition file of the source font
 * to the requested style, so a style that was not already loaded was silently rendered with the
 * glyphs and the metrics of the source style.
 * See: https://github.com/tecnickcom/tc-lib-pdf-font/issues/19
 *
 * @since       2026-07-12
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TFontData from \Com\Tecnick\Pdf\Font\Load
 */
class FontCloneStyleTest extends TestUtil
{
    /** @throws \Throwable */
    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        self::setUpFontsPath();
        return new \Com\Tecnick\Pdf\Tcpdf(unit: 'mm', isunicode: true);
    }

    /**
     * Returns the raw buffer data of the given font key.
     *
     * @return TFontData
     *
     * @throws \Throwable
     */
    private function getFontData(\Com\Tecnick\Pdf\Tcpdf $obj, string $key): array
    {
        $this->assertArrayHasKey($key, $obj->font->getFonts(), 'The font ' . $key . ' has not been loaded');
        return $obj->font->getFont($key);
    }

    /** @throws \Throwable */
    public function testCloneFontLoadsTheDefinitionFileOfTheRequestedStyle(): void
    {
        $obj = $this->getTestObject();
        $obj->addPage();
        $obj->font->insert($obj->pon, 'freesans', '', 12);

        $bold = $obj->font->cloneFont($obj->pon, null, 'B', 12);

        $this->assertSame('freesansB', $bold['key']);
        $this->assertSame('B', $bold['style']);

        $data = $this->getFontData($obj, 'freesansB');
        $this->assertSame('FreeSansBold', $data['name']);
        $this->assertStringEndsWith('freesansb.json', $data['ifile']);
        $this->assertFalse($data['fakestyle'], 'The real bold definition file must be used');
    }

    /** @throws \Throwable */
    public function testCloneFontLoadsTheDefinitionFileOfCombinedStyles(): void
    {
        $obj = $this->getTestObject();
        $obj->addPage();
        $obj->font->insert($obj->pon, 'freesans', '', 12);

        $italic = $obj->font->cloneFont($obj->pon, null, 'I', 12);
        $this->assertSame('freesansI', $italic['key']);
        $this->assertSame('FreeSansOblique', $this->getFontData($obj, 'freesansI')['name']);

        $bolditalic = $obj->font->cloneFont($obj->pon, null, 'BI', 12);
        $this->assertSame('freesansBI', $bolditalic['key']);
        $this->assertSame('FreeSansBoldOblique', $this->getFontData($obj, 'freesansBI')['name']);
    }

    /**
     * Cloning a style that was not loaded before must return the same font as cloning a style
     * that was already loaded: this is the inconsistency reported in the upstream issue.
     *
     * @throws \Throwable
     */
    public function testCloneFontIsConsistentWhenTheStyleIsAlreadyLoaded(): void
    {
        $preloaded = $this->getTestObject();
        $preloaded->addPage();
        $preloaded->font->insert($preloaded->pon, 'freesans', '', 12);
        $preloaded->font->insert($preloaded->pon, 'freesans', 'B', 12);
        $preloaded->font->insert($preloaded->pon, 'freesans', '', 12);
        $expected = $preloaded->font->cloneFont($preloaded->pon, null, 'B', 12);

        $cloned = $this->getTestObject();
        $cloned->addPage();
        $cloned->font->insert($cloned->pon, 'freesans', '', 12);
        $actual = $cloned->font->cloneFont($cloned->pon, null, 'B', 12);

        $this->assertSame($expected['key'], $actual['key']);
        $this->assertSame($expected['cw'], $actual['cw'], 'The cloned font must have the bold glyph widths');
        $this->assertSame($expected['height'], $actual['height']);
    }

    /**
     * The bold glyphs of FreeSans are wider than the regular ones: a wrongly loaded bold font
     * would return the regular widths.
     *
     * @throws \Throwable
     */
    public function testCloneFontUsesTheMetricsOfTheRequestedStyle(): void
    {
        $obj = $this->getTestObject();
        $obj->addPage();
        $regular = $obj->font->insert($obj->pon, 'freesans', '', 12);
        $regularwidth = $obj->font->getCharWidth(0x48); // 'H'

        $bold = $obj->font->cloneFont($obj->pon, null, 'B', 12);
        $boldwidth = $obj->font->getCharWidth(0x48); // 'H'

        $this->assertNotSame($regular['key'], $bold['key']);
        $this->assertGreaterThan($regularwidth, $boldwidth, 'The bold glyph must be wider than the regular one');
    }

    /**
     * The source font is loaded with an explicit definition file (as tc-lib-pdf does for the
     * default font): the style variant must still be resolved.
     *
     * @throws \Throwable
     */
    public function testCloneFontResolvesTheStyleOfAFontLoadedWithAnExplicitFile(): void
    {
        $obj = $this->getTestObject();
        $obj->addPage();
        $ifile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
        $obj->font->insert($obj->pon, 'helvetica', '', 10, null, null, $ifile);

        $bold = $obj->font->cloneFont($obj->pon, null, 'B', 10);

        $this->assertSame('helveticaB', $bold['key']);
        $data = $this->getFontData($obj, 'helveticaB');
        $this->assertSame('Helvetica-Bold', $data['name']);
        $this->assertStringEndsWith('helveticab.json', $data['ifile']);
    }

    /**
     * When no definition file exists for the requested style, the artificial style must be used.
     *
     * @throws \Throwable
     */
    public function testCloneFontFallsBackToTheArtificialStyle(): void
    {
        $obj = $this->getTestObject();
        $obj->addPage();
        $obj->font->insert($obj->pon, 'dejavumathtexgyre', '', 12);

        $bold = $obj->font->cloneFont($obj->pon, null, 'B', 12);

        $this->assertSame('dejavumathtexgyreB', $bold['key']);
        $data = $this->getFontData($obj, 'dejavumathtexgyreB');
        $this->assertTrue($data['fakestyle'], 'The artificial bold style must be enabled');
        $this->assertTrue($data['mode']['bold']);
    }

    /** @throws \Throwable */
    public function testCloneFontReturnsToTheRegularStyle(): void
    {
        $obj = $this->getTestObject();
        $obj->addPage();
        $obj->font->insert($obj->pon, 'freesans', 'B', 12);

        $regular = $obj->font->cloneFont($obj->pon, null, '', 12);

        $this->assertSame('freesans', $regular['key']);
        $this->assertSame('FreeSans', $this->getFontData($obj, 'freesans')['name']);
    }

    /**
     * Tcpdf::addTOC() clones the current font to render the top level bookmarks in bold.
     *
     * @throws \Throwable
     */
    public function testAddTOCRendersTopLevelBookmarksWithTheRealBoldFont(): void
    {
        $obj = $this->getTestObject();
        $page = $obj->addPage();
        if (!isset($page['pid']) || !\is_int($page['pid'])) {
            $this->fail('Unexpected addPage() return shape.');
        }

        $obj->font->insert($obj->pon, 'freesans', '', 12);

        $obj->setBookmark('Chapter One', '', 0, $page['pid']);
        $obj->addTOC($page['pid']);

        $data = $this->getFontData($obj, 'freesansB');
        $this->assertSame('FreeSansBold', $data['name']);
        $this->assertStringEndsWith('freesansb.json', $data['ifile']);
    }
}
