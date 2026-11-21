<?php

/**
 * LocaleIndependenceTest.php
 *
 * @since       2026-11-20
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

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

/**
 * HTML, CSS and SVG output under the C locale and under the test locale built by "make locale-fixture".
 *
 * The test locale classifies Latin-1 letters as alpha, U+00A0 as space, and maps i/I as in Turkish.
 *
 * @since       2026-11-20
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class LocaleIndependenceTest extends TestUtil
{
    /**
     * HTML from tecnickcom/tc-lib-pdf#331.
     */
    private const ISSUE_HTML = '<p>Voilà</p><p>Ça marche</p>';

    /**
     * @return array<string, string>
     */
    private static function htmlCorpus(): array
    {
        $chars = ['Š', 'ム', 'Å'];
        $html = [
            'issue' => self::ISSUE_HTML,
        ];
        foreach ($chars as $num => $chr) {
            $html['closing-tags-' . $num] =
                '<p>A' . $chr . '</p><div><b>B' . $chr . '</b> <i>C' . $chr . '</i></div><h1>D' . $chr . '</h1>';
            $html['table-' . $num] = '<table border="1"><tr><td>E' . $chr . '</td><th>F' . $chr . '</th></tr></table>';
            $html['list-' . $num] =
                '<ul><li>G' . $chr . '</li><li>H' . $chr . ' </li></ul><ol><li>I' . $chr . '</li></ol>';
            $html['image-' . $num] =
                '<p>J'
                . $chr
                . ' <img src="@'
                . self::svgImage()
                . '" width="20" height="10"> K'
                . $chr
                . '</p><ul><li>'
                . $chr
                . '<img src="@'
                . self::svgImage()
                . '" width="10" height="10"></li></ul>';
        }

        $html['uppercase'] =
            '<STYLE>P.X { COLOR: RED !IMPORTANT; FONT-WEIGHT: BOLD; }</STYLE>'
            . '<P CLASS="X" STYLE="COLOR:BLUE; MARGIN:0IN; PADDING: 0IN">Uppercase Ünïcode</P>'
            . '<DIV STYLE="FONT: ITALIC BOLD 12PX/30PX Georgia, SERIF">Font Shorthand</DIV>'
            . '<TABLE><TR><TD STYLE="BORDER: 1PX SOLID #ABCDEF">Cell</TD></TR></TABLE>';
        $html['important'] =
            '<style>p { color: red !IMPORTANT; } p.y { color: green ! Important; }</style>'
            . '<p style="color:blue">x</p><p class="y" style="color:blue">y</p>';
        $html['pseudo'] =
            '<style>LI:FIRST-CHILD { color: #FF0000; } li:LAST-CHILD { color: RGB(0, 0, 255); }'
            . ' P::BEFORE { content: "Before "; } p.q::after { CONTENT: " After"; }</style>'
            . '<ul><li>one</li><li>two</li><li>three</li></ul><p>Para</p><p class="q">Quote</p>';
        $html['colors'] =
            '<p style="color:#ABCDEF">hex</p><p style="color:RGB(10, 20, 30)">rgb</p>'
            . '<p style="color:Rgba(10, 20, 30, 0.5)">rgba</p><p style="color:HSL(120, 50%, 50%)">hsl</p>'
            . '<p style="background-color:LightGoldenRodYellow; color:INDIGO">named</p>';
        $html['charset'] = '<style>@charset "iso-8859-1"; p.c { font-weight: bold; }</style><p class="c">Charset Latin</p>';
        $html['forms'] =
            '<form><select name="s"><OPTION value="a">Ä</OPTION><option SELECTED value="b">Bé</option></select>'
            . '<textarea name="t">Line ì'
            . "\n"
            . 'Line Ä</textarea>'
            . '<input type="text" name="i" value="Ïnput"></form>'
            . '<p>wo<wbr>rd W<WBR>ord</p><pre>  pre  à'
            . "\n"
            . '  Å  </pre>';

        return $html;
    }

    private static function svgImage(): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="10"><rect width="20" height="10"/></svg>';
    }

    /**
     * @return array<string, string>
     */
    private static function svgCorpus(): array
    {
        return [
            'paths' =>
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">'
                    . '<path d="M10 10 L50 10 l0 40 h-20 V80 Z m5 5 c5 5 10 5 15 0 s5 -5 10 0 Q60 60 70 70 t10 10'
                    . ' A10 10 0 0 1 90 90 a5 5 0 1 0 -5 -5 z" fill="#ABCDEF" STROKE="BLACK"/>'
                    . '<path d="m20,20l10,10H40v10C45,45,50,50,55,45S60,40,65,45"/></svg>',
            'style' =>
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">'
                    . '<defs><linearGradient id="Grad"><stop offset="0" STYLE="STOP-COLOR:#FF0000"/>'
                    . '<stop offset="1" style="Stop-Opacity: 0.5; stop-color: RGB(0,0,255)"/></linearGradient></defs>'
                    . '<rect x="10" y="10" width="80" height="30" STYLE="FILL:URL(#Grad); STROKE: #000 !IMPORTANT"/>'
                    . '<g transform="translate(10,50) rotate(10) scale(0.5) skewX(5)"><rect width="20" height="20"/></g>'
                    . '</svg>',
            'text' =>
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 50" width="200" height="50">'
                    . '<text x="10" y="20" style="font-family:helvetica; font-size:12px">Voilà Ça Åre Ünïcode</text>'
                    . '<text x="10" y="40">Š ム Å à</text></svg>',
        ];
    }

    /**
     * @return array<string, string>
     *
     * @throws \Throwable
     */
    private function renderCorpus(): array
    {
        $out = [];
        foreach (self::htmlCorpus() as $name => $html) {
            $obj = new \Com\Tecnick\Pdf\Tcpdf();
            $this->initFontAndPage($obj);
            $out['html-' . $name] = $obj->getHTMLCell($html, 10, 10, 180, 0);
        }

        foreach (self::svgCorpus() as $name => $svg) {
            $obj = new \Com\Tecnick\Pdf\Tcpdf();
            $page = $this->initFontAndPage($obj);
            $soid = $obj->addSVG('@' . $svg, 10, 10, 100, 100, $page['height']);
            /** @var array<int, array{out?: string}> $svgobjs */
            $svgobjs = $this->getObjectProperty($obj, 'svgobjs');
            $out['svg-' . $name] = $svgobjs[$soid]['out'] ?? '';
        }

        return $out;
    }

    /**
     * @throws \Throwable
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testOutputDoesNotDependOnTheLocale(): void
    {
        $locpath = \dirname(__DIR__) . '/target/locale';
        if (!\is_dir($locpath . '/hostile')) {
            $this->markTestSkipped('test locale not built: run "make locale-fixture"');
        }

        $oldLocale = \setlocale(\LC_CTYPE, '0');
        $oldLocpath = \getenv('LOCPATH');
        try {
            \setlocale(\LC_CTYPE, 'C');
            $reference = $this->renderCorpus();

            \putenv('LOCPATH=' . $locpath);
            if (\setlocale(\LC_CTYPE, 'hostile') !== 'hostile') {
                $this->markTestSkipped('cannot select the test locale');
            }

            $this->assertSame(1, \preg_match('/\s/', "\xA0"), 'the test locale is not active');
            $output = $this->renderCorpus();
        } finally {
            \setlocale(\LC_CTYPE, $oldLocale === false ? 'C' : $oldLocale);
            \putenv($oldLocpath === false ? 'LOCPATH' : 'LOCPATH=' . $oldLocpath);
        }

        foreach ($reference as $name => $stream) {
            $this->assertNotSame('', $stream, $name);
            $this->assertSame($stream, $output[$name] ?? null, $name);
        }

        $this->assertSame(2, \substr_count($output['html-issue'] ?? '', ' Tj'));
    }
}
