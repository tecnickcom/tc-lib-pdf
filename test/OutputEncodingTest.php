<?php

/**
 * OutputEncodingTest.php
 *
 * @since     2026-11-21
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

/**
 * Encoding of link targets and form state names taken from HTML, SVG and the API.
 *
 * @since     2026-11-21
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class OutputEncodingTest extends TestUtil
{
    /**
     * Returns an uncompressed document with the core Helvetica font and one page.
     *
     * @throws \Throwable
     */
    private function getDocument(): \Com\Tecnick\Pdf\Tcpdf
    {
        self::setUpFontsPath();
        $pdf = new \Com\Tecnick\Pdf\Tcpdf(compress: false);
        $this->initFontAndPage($pdf);

        return $pdf;
    }

    /**
     * @throws \Throwable
     */
    public function testHtmlLinkFragmentIsEscaped(): void
    {
        $pdf = $this->getDocument();
        $pdf->addHTMLCell(html: '<a href="a.pdf#x) /S /JavaScript /JS (MARK();">l</a>', posx: 15, posy: 15, width: 180);
        $pdf->addHTMLCell(
            html: '<a href="' . \htmlspecialchars('b.pdf#y)/MARK(z', \ENT_QUOTES) . '">l</a>',
            posx: 15,
            posy: 30,
            width: 180,
        );

        $raw = $pdf->getOutPDFString();

        $this->assertStringContainsString(' /A << /S /GoToR /D (x\) /S /JavaScript /JS \(MARK\(\);) /F (a.pdf)', $raw);
        $this->assertStringContainsString(' /A << /S /GoToR /D (y\)/MARK\(z) /F (b.pdf)', $raw);
    }

    /**
     * @throws \Throwable
     */
    public function testSvgLinkFragmentIsEscaped(): void
    {
        $pdf = $this->getDocument();
        $pdf->addSVG(
            '@<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"'
            . ' width="100" height="40"><a xlink:href="a.pdf#x) &gt;&gt; /AA &lt;&lt; /E &lt;&lt; /S /JavaScript'
            . ' /JS (MARK();) &gt;&gt; &gt;&gt; /Z &lt;&lt; /D (y"><text x="5" y="20">l</text></a></svg>',
            15,
            15,
            60,
            30,
        );

        $raw = $pdf->getOutPDFString();

        $this->assertStringContainsString(
            ' /A << /S /GoToR /D (x\) >> /AA << /E << /S /JavaScript /JS \(MARK\(\);\) >> >> /Z << /D \(y) /F (a.pdf)',
            $raw,
        );
    }

    /**
     * @throws \Throwable
     */
    public function testHtmlEmbeddedFileLinkNeedsEmbeddedFile(): void
    {
        $pdf = $this->getDocument();
        $pdf->addHTMLCell(html: "<a href='*x\") MARK(); if (\"'>l</a>", posx: 15, posy: 15, width: 180);

        $raw = $pdf->getOutPDFString();

        $this->assertStringContainsString('/Subtype /Link', $raw);
        $this->assertStringNotContainsString('/S /JavaScript', $raw);
    }

    /**
     * @throws \Throwable
     */
    public function testHtmlRadioValueIsEncodedAsName(): void
    {
        $pdf = $this->getDocument();
        $pdf->addHTMLCell(
            html: '<form><input type="radio" name="r" checked="checked"'
            . ' value="On /AA &lt;&lt; /E &lt;&lt; /S /JavaScript /JS (MARK();) &gt;&gt; &gt;&gt; /Z">'
            . '<input type="radio" name="s" checked="checked" value="Option 1"></form>',
            posx: 15,
            posy: 15,
            width: 180,
        );

        $raw = $pdf->getOutPDFString();
        $name =
            'On#20#2FAA#20#3C#3C#20#2FE#20#3C#3C#20#2FS#20#2FJavaScript#20#2FJS'
            . '#20#28MARK#28#29#3B#29#20#3E#3E#20#3E#3E#20#2FZ';

        $this->assertStringNotContainsString('/AA', $raw);
        $this->assertStringNotContainsString('/S /JavaScript', $raw);
        $this->assertSame(1, \substr_count($raw, ' /AS /' . $name . ' '));
        $this->assertSame(2, \substr_count($raw, ' /V /' . $name . ' '));
        $this->assertStringContainsString(' /N << /' . $name . ' ', $raw);
        $this->assertStringContainsString(' /AS /Option#201 ', $raw);
        $this->assertStringContainsString(' /V /Option#201 ', $raw);
    }
}
