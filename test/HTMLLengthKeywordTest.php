<?php

/**
 * HTMLLengthKeywordTest.php
 *
 * @since     2026-11-02
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

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * CSS-wide keywords and keyword or invalid values of the HTML length properties and
 * attributes.
 *
 * @since     2026-11-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type THTMLAttrib from \Com\Tecnick\Pdf\HTML
 */
class HTMLLengthKeywordTest extends TestUtil
{
    /**
     * Returns the first opening node of the HTML with the given tag.
     *
     * @return THTMLAttrib
     *
     * @throws \Throwable
     */
    private function getNode(string $html, string $tag): array
    {
        self::setUpFontsPath();
        $obj = new TestableHTML();
        $this->initFontAndPage($obj);
        foreach ($obj->exposeGetHTMLDOM($html) as $node) {
            if ($node['value'] === $tag && $node['opening']) {
                return $node;
            }
        }

        $this->fail('No ' . $tag . ' node');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function declarationProvider(): array
    {
        $properties = [
            'width',
            'height',
            'min-width',
            'max-width',
            'min-height',
            'max-height',
            'margin',
            'margin-top',
            'padding',
            'padding-left',
            'font-size',
            'line-height',
            'border-width',
            'border-top-width',
            'border-spacing',
            'letter-spacing',
            'word-spacing',
            'text-indent',
            'top',
            'left',
        ];
        $values = ['auto', 'none', 'normal', 'unset', 'revert', 'revert-layer', 'initial', 'inherit', '', 'x', '-'];
        $out = [];
        foreach ($properties as $property) {
            foreach ($values as $value) {
                $out[$property . ': ' . $value] = [$property . ': ' . $value];
            }
        }

        $out['important'] = ['width: auto !important'];
        $out['margin with a bad value'] = ['margin: 2px none'];

        return $out;
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('declarationProvider')]
    public function testKeywordDeclarationRenders(string $declaration): void
    {
        self::setUpFontsPath();
        $pdf = new \Com\Tecnick\Pdf\Tcpdf(compress: false);
        $this->initFontAndPage($pdf);
        $pdf->addHTMLCell('<div><p style="' . $declaration . '">x</p></div>', 10, 10, 100);

        $this->assertStringContainsString('(x) Tj', $pdf->getOutPDFString());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function attributeProvider(): array
    {
        $out = [];
        foreach (['table', 'td', 'img', 'col', 'hr', 'div'] as $tag) {
            foreach (['width', 'height'] as $attr) {
                foreach (['auto', '', 'none'] as $value) {
                    $html = match ($tag) {
                        'td' => '<table><tr><td ' . $attr . '="' . $value . '">x</td></tr></table>',
                        'col' => '<table><col ' . $attr . '="' . $value . '"><tr><td>x</td></tr></table>',
                        'img' => '<img src="missing.png" alt="x" ' . $attr . '="' . $value . '">',
                        'hr' => '<hr ' . $attr . '="' . $value . '"><p>x</p>',
                        'table' => '<table ' . $attr . '="' . $value . '"><tr><td>x</td></tr></table>',
                        default => '<div ' . $attr . '="' . $value . '">x</div>',
                    };
                    $out[$tag . ' ' . $attr . '=' . $value] = [$html];
                }
            }
        }

        return $out;
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('attributeProvider')]
    public function testKeywordAttributeRenders(string $html): void
    {
        self::setUpFontsPath();
        $pdf = new \Com\Tecnick\Pdf\Tcpdf(compress: false);
        $this->initFontAndPage($pdf);
        $pdf->addHTMLCell($html, 10, 10, 100);

        $this->assertStringContainsString('(x) Tj', $pdf->getOutPDFString());
    }

    /**
     * @throws \Throwable
     */
    public function testUnsetInheritsAnInheritedPropertyAndResetsAnotherOne(): void
    {
        $node = $this->getNode(
            '<div style="font-size: 20pt; margin-top: 10pt"><p style="font-size: unset; margin-top: unset">x</p></div>',
            'p',
        );

        $this->assertSame(20.0, $node['fontsize']);
        $this->assertSame(0.0, $node['margin']['T'] ?? -1.0);
    }

    /**
     * @throws \Throwable
     */
    public function testRevertKeepsTheDefaults(): void
    {
        $plain = $this->getNode('<div style="font-size: 20pt"><h1>x</h1></div>', 'h1');
        $reverted = $this->getNode(
            '<div style="font-size: 20pt"><h1 style="font-size: revert; margin-top: revert-layer">x</h1></div>',
            'h1',
        );

        $this->assertSame($plain['fontsize'], $reverted['fontsize']);
        $this->assertSame($plain['margin']['T'] ?? -1.0, $reverted['margin']['T'] ?? -2.0);
    }

    /**
     * @throws \Throwable
     */
    public function testInvalidValueKeepsTheInheritedValue(): void
    {
        $node = $this->getNode('<div style="font-size: 20pt"><p style="font-size: auto">x</p></div>', 'p');
        $this->assertSame(20.0, $node['fontsize']);

        $node = $this->getNode('<p style="border-top-width: initial; padding-left: none">x</p>', 'p');
        $this->assertSame(0.0, $node['padding']['L'] ?? -1.0);
    }

    /**
     * @throws \Throwable
     */
    public function testStyleDimensionTakesPrecedenceOverTheAttribute(): void
    {
        $node = $this->getNode('<img src="x.png" width="300" height="200" style="height: auto">', 'img');
        $this->assertGreaterThan(0.0, $node['width']);
        $this->assertSame(0.0, $node['height']);

        $node = $this->getNode('<table><tr><td width="100" style="width: 20px">x</td></tr></table>', 'td');
        $this->assertEqualsWithDelta((20.0 * 0.75) / (72 / 25.4), $node['width'], 0.001);

        $node = $this->getNode('<div width="auto" height="">x</div>', 'div');
        $this->assertSame(0.0, $node['width']);
        $this->assertSame(0.0, $node['height']);
    }

    /**
     * @throws \Throwable
     */
    public function testListMarkerWithKeywordFontSizeRenders(): void
    {
        self::setUpFontsPath();
        $pdf = new \Com\Tecnick\Pdf\Tcpdf(compress: false);
        $this->initFontAndPage($pdf);
        $pdf->addHTMLCell('<style>li::marker{font-size:auto}</style><ol><li>x</li></ol>', 10, 10, 100);

        $this->assertStringContainsString('(x) Tj', $pdf->getOutPDFString());
    }
}
