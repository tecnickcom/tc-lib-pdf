<?php

/**
 * HTMLAttributeValueTest.php
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

use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * Character references in HTML attribute values.
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
class HTMLAttributeValueTest extends TestUtil
{
    /**
     * Base64 of a 13x13 red PNG with both '+' and '=' in it.
     */
    private const PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAA0AAAANCAIAAAD9iXMrAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAFElEQVQoz2P8z0AUYGIYVTec1QEAIYcBGb+3yrEAAAAASUVORK5CYII=';

    /**
     * Returns the attributes of the first node of the HTML with the given tag.
     *
     * @return THTMLAttrib['attribute']
     *
     * @throws \Throwable
     */
    private function getAttributes(string $html, string $tag): array
    {
        $obj = new TestableHTML();
        foreach ($obj->exposeGetHTMLDOM($html) as $node) {
            if ($node['value'] === $tag && $node['opening']) {
                return $node['attribute'];
            }
        }

        $this->fail('No ' . $tag . ' node');
    }

    /**
     * Returns a page with the core Helvetica font and the HTML drawn at the top.
     *
     * @param array<int, string> $allowedPaths Extra local paths the document may read.
     *
     * @throws \Throwable
     */
    private function render(string $html, array $allowedPaths = []): string
    {
        self::setUpFontsPath();
        $pdf = new \Com\Tecnick\Pdf\Tcpdf(fileOptions: ['allowedPaths' => [
            ...$allowedPaths,
            (string) \constant('K_PATH_FONTS'),
        ]]);
        $this->initFontAndPage($pdf);

        return $pdf->getHTMLCell($html, 10, 10, 100, 0);
    }

    /**
     * @throws \Throwable
     */
    public function testAttributeValuesAreDecodedOnce(): void
    {
        $attr = $this->getAttributes(
            '<img src="a&amp;b.png?x=1&#38;y=&#x32;" alt="Tom &amp; Jerry &quot;TJ&quot;" title="&lt;t&gt;"'
            . ' data-x="&amp;amp;">',
            'img',
        );

        $this->assertSame('a&b.png?x=1&y=2', $attr['src'] ?? null);
        $this->assertSame('Tom & Jerry "TJ"', $attr['alt'] ?? null);
        $this->assertSame('<t>', $attr['title'] ?? null);
        $this->assertSame('&amp;', $attr['data-x'] ?? null);
    }

    /**
     * A link target is decoded when the link annotation is written, so it is kept as it
     * is in the DOM.
     *
     * @throws \Throwable
     */
    public function testLinkTargetIsKeptForTheAnnotation(): void
    {
        $attr = $this->getAttributes('<a href="https://x.test/?a=1&amp;b=2">x</a>', 'a');
        $this->assertSame('https://x.test/?a=1&amp;b=2', $attr['href'] ?? null);

        self::setUpFontsPath();
        $pdf = new \Com\Tecnick\Pdf\Tcpdf();
        $this->initFontAndPage($pdf);
        $pdf->addHTMLCell('<a href="https://x.test/?a=1&amp;b=2&amp;amp;c">x</a>', 10, 10, 100);
        $out = $pdf->getOutPDFString();

        $this->assertStringContainsString('/URI (https://x.test/?a=1&b=2&amp;c)', $out);
    }

    /**
     * @throws \Throwable
     */
    public function testFormValuesAreDecoded(): void
    {
        $attr = $this->getAttributes('<input type="text" name="n" value="a &lt; b &amp; c">', 'input');
        $this->assertSame('a < b & c', $attr['value'] ?? null);

        $attr = $this->getAttributes('<button>Say "hi"</button>', 'button');
        $this->assertSame('Say "hi"', $attr['value'] ?? null);
    }

    /**
     * @throws \Throwable
     */
    #[RequiresPhpExtension('gd')]
    public function testDataUriWithCharacterReferencesLoads(): void
    {
        $this->assertStringContainsString('+', self::PNG_BASE64);
        $this->assertStringContainsString('=', self::PNG_BASE64);
        $src = \str_replace(['+', '='], ['&#43;', '&#61;'], self::PNG_BASE64);

        $out = $this->render('<img src="data:image/png;base64,' . $src . '" alt="MISSING" width="10" height="10">');

        $this->assertMatchesRegularExpression('/\/IMG(?:plain|mask)?\d+ Do Q/', $out);
        $this->assertStringNotContainsString('MISSING', $out);
    }

    /**
     * @throws \Throwable
     */
    #[RequiresPhpExtension('gd')]
    public function testFilePathWithAmpersandLoads(): void
    {
        $dir = \sys_get_temp_dir() . '/tcpdf-attr-' . \bin2hex(\random_bytes(4));
        if (!\is_dir($dir)) {
            \mkdir($dir);
        }

        $file = $dir . '/a&b.png';
        \file_put_contents($file, (string) \base64_decode(self::PNG_BASE64, true));
        try {
            $out = $this->render('<img src="' . $dir . '/a&amp;b.png" alt="MISSING" width="10" height="10">', [$dir]);
        } finally {
            \unlink($file);
            \rmdir($dir);
        }

        $this->assertMatchesRegularExpression('/\/IMG(?:plain|mask)?\d+ Do Q/', $out);
        $this->assertStringNotContainsString('MISSING', $out);
    }

    /**
     * @throws \Throwable
     */
    public function testAltTextIsDecoded(): void
    {
        $out = $this->render('<img src="missing.png" alt="Tom &amp; Jerry">');

        $this->assertStringContainsString('(Tom & Jerry)', $out);
    }
}
