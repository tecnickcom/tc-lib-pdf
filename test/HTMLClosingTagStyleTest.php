<?php

/**
 * HTMLClosingTagStyleTest.php
 *
 * @since     2026-10-11
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */

namespace Test;

/**
 * Style of HTML closing-tag nodes, the last line of a closing element and the font
 * of the fragments replayed for a table (caption and header rows).
 *
 * @since     2026-10-11
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class HTMLClosingTagStyleTest extends TestUtil
{
    private const UNUSED_STYLESHEET = '<style>p.unused { color: red; }</style>';

    public static function setUpBeforeClass(): void
    {
        self::setUpFontsPath();
    }

    /**
     * Returns an uncompressed document with helvetica at the given size and one page.
     *
     * @throws \Throwable
     */
    private function getDocument(float $fontsize = 10.0): \Com\Tecnick\Pdf\Tcpdf
    {
        $pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, false);
        /** @var int $pon */
        $pon = $this->getObjectProperty($pdf, 'pon');
        $font = $pdf->font->insert($pon, 'helvetica', '', $fontsize);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);

        return $pdf;
    }

    /**
     * Returns every text shown with Td on every page, in stream order, with its page
     * index, font size and baseline ordinate (points). Texts are trimmed.
     *
     * @return list<array{text: string, page: int, size: float, y: float}>
     */
    private function getTexts(\Com\Tecnick\Pdf\Tcpdf $pdf): array
    {
        $out = [];
        foreach (\array_values($pdf->page->getPages()) as $pidx => $page) {
            $content = \implode("\n", $page['content']);
            $matches = [];
            \preg_match_all(
                '/\/F\d+ ([0-9.]+) Tf|([-0-9.]+) ([-0-9.]+) Td \(((?:\\\\.|[^\\\\)])*)\) Tj/',
                $content,
                $matches,
                PREG_SET_ORDER,
            );
            $size = 0.0;
            foreach ($matches as $match) {
                $fontsize = $match[1] ?? '';
                if ($fontsize !== '') {
                    $size = \floatval($fontsize);
                    continue;
                }

                $out[] = [
                    'text' => \trim($match[4] ?? ''),
                    'page' => $pidx,
                    'size' => $size,
                    'y' => \floatval($match[3] ?? 0.0),
                ];
            }
        }

        return $out;
    }

    /**
     * Returns the texts of the given HTML rendered at the top of a new document.
     *
     * @return list<array{text: string, page: int, size: float, y: float}>
     *
     * @throws \Throwable
     */
    private function renderTexts(string $html, float $fontsize = 10.0): array
    {
        $pdf = $this->getDocument($fontsize);
        $pdf->addHTMLCell($html, 10, 10, 150);

        return $this->getTexts($pdf);
    }

    /**
     * Returns the first rendered text equal to the given string.
     *
     * @param list<array{text: string, page: int, size: float, y: float}> $texts
     *
     * @return array{text: string, page: int, size: float, y: float}
     */
    private function findText(array $texts, string $text, int $nth = 0): array
    {
        foreach ($texts as $item) {
            if ($item['text'] !== $text) {
                continue;
            }

            if ($nth === 0) {
                return $item;
            }

            --$nth;
        }

        $this->fail('Text not found: ' . $text);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function closingTagHtmlProvider(): array
    {
        return [
            'heading in a block' => ['<div><h1>Title</h1>after</div>'],
            'small font paragraph' => ['<div><p style="font-size:8pt">small</p><p>next</p></div>'],
            'list' => ['<ul><li>one <b>bold</b></li><li>two</li></ul>after'],
            'table cell' => ['<table border="1"><tr><td style="font-size:7pt">cell</td></tr></table>after'],
            'rtl paragraph' => ['<div><p dir="rtl" style="font-size:9pt">שלום <i>עולם</i></p>after</div>'],
        ];
    }

    /** @throws \Throwable */
    #[\PHPUnit\Framework\Attributes\DataProvider('closingTagHtmlProvider')]
    public function testClosingTagNodesDoNotDependOnAStylesheet(string $html): void
    {
        $obj = new TestableHTML();
        $obj->font->insert($obj->pon, 'helvetica', '', 10);

        foreach ([true, false] as $slim) {
            $plain = $obj->exposeGetHTMLStoredDOM($html, $slim);
            $styled = $obj->exposeGetHTMLStoredDOM(self::UNUSED_STYLESHEET . $html, $slim);
            $this->assertSame(\array_keys($plain), \array_keys($styled));
            $closing = 0;
            foreach (\array_keys($plain) as $key) {
                $node = $obj->exposeGetHTMLDOMNode($plain, $key);
                $this->assertNotNull($node);
                if (!$node['tag'] || $node['opening']) {
                    continue;
                }

                ++$closing;
                $this->assertSame($node, $obj->exposeGetHTMLDOMNode($styled, $key), 'closing node ' . $key);
            }

            $this->assertGreaterThan(0, $closing);
        }

        $this->assertSame($this->renderTexts($html), $this->renderTexts(self::UNUSED_STYLESHEET . $html));
    }

    /** @throws \Throwable */
    public function testClosingTagNodeTakesTheStyleAfterTheElement(): void
    {
        $obj = new TestableHTML();
        $obj->font->insert($obj->pon, 'helvetica', '', 10);
        $html = '<div><h1>Title</h1>after</div>';

        foreach (['', self::UNUSED_STYLESHEET] as $css) {
            $dom = $obj->exposeGetHTMLStoredDOM($css . $html);
            foreach (\array_keys($dom) as $key) {
                $node = $obj->exposeGetHTMLDOMNode($dom, $key);
                if ($node === null || !$node['tag'] || $node['opening'] || $node['value'] !== 'h1') {
                    continue;
                }

                $this->assertSame(10.0, $node['fontsize']);
                $this->assertSame('', $node['fontstyle']);
            }
        }
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function compactLastLineProvider(): array
    {
        return [
            'small font' => ['font-size:8pt'],
            'compact line height' => ['line-height:50%'],
            'absolute line height' => ['line-height:6pt'],
        ];
    }

    /** @throws \Throwable */
    #[\PHPUnit\Framework\Attributes\DataProvider('compactLastLineProvider')]
    public function testLastLineOfABlockHasTheLineHeightOfTheBlock(string $style): void
    {
        $html =
            '<div><p style="margin:0;'
            . $style
            . '">one<br />two</p>'
            . '<p style="margin:0;'
            . $style
            . '">three</p></div>';

        foreach ([10.0, 20.0] as $callersize) {
            foreach (['', self::UNUSED_STYLESHEET] as $css) {
                $texts = $this->renderTexts($css . $html, $callersize);
                $one = $this->findText($texts, 'one');
                $two = $this->findText($texts, 'two');
                $three = $this->findText($texts, 'three');
                $pitch = $one['y'] - $two['y'];
                $this->assertGreaterThan(0.0, $pitch);
                $this->assertEqualsWithDelta($pitch, $two['y'] - $three['y'], 0.001);
            }
        }
    }

    /** @throws \Throwable */
    public function testLastLineDoesNotDependOnTheCallerFont(): void
    {
        $html = '<div><p style="font-size:8pt">small</p><p style="font-size:10pt">next</p></div>';

        $this->assertSame($this->renderTexts($html, 10.0), $this->renderTexts($html, 20.0));
    }

    /**
     * @return array<string, array{0: string, 1: float}>
     */
    public static function captionFontProvider(): array
    {
        $table = '<table border="1"><caption>Cap</caption><tr><td>c</td></tr></table>';

        return [
            'after a large block' => ['<div style="font-size:16pt"><p>big</p></div>' . $table, 10.0],
            'after a heading' => ['<div><h3>Heading</h3>' . $table . '</div>', 10.0],
            'inside a large block' => ['<div style="font-size:16pt">' . $table . '</div>', 16.0],
            'bottom caption inside a large block' => [
                '<div style="font-size:16pt"><table border="1"><tr><td>c</td></tr>'
                    . '<caption style="caption-side:bottom">Cap</caption></table></div>',
                16.0,
            ],
        ];
    }

    /** @throws \Throwable */
    #[\PHPUnit\Framework\Attributes\DataProvider('captionFontProvider')]
    public function testTableCaptionHasTheFontOfItsTable(string $html, float $size): void
    {
        foreach (['', self::UNUSED_STYLESHEET] as $css) {
            $this->assertSame($size, $this->findText($this->renderTexts($css . $html), 'Cap')['size']);
        }
    }

    /** @throws \Throwable */
    public function testRepeatedTableHeaderHasTheFontOfItsTable(): void
    {
        $html = '<table border="1"><thead><tr><th>Head</th></tr></thead>';
        for ($idx = 0; $idx < 120; ++$idx) {
            $html .= '<tr style="font-size:6pt"><td>r' . $idx . '</td></tr>';
        }

        $html .= '</table>';
        $texts = $this->renderTexts($html);

        $first = $this->findText($texts, 'Head');
        $repeated = $this->findText($texts, 'Head', 1);
        $this->assertSame(0, $first['page']);
        $this->assertSame(1, $repeated['page']);
        $this->assertSame(10.0, $first['size']);
        $this->assertSame(10.0, $repeated['size']);
    }

    /** @throws \Throwable */
    public function testBottomTagSpaceUsesTheLineHeightOfTheElement(): void
    {
        $html =
            '<div><p style="margin:0;font-size:8pt">one<br />two</p>'
            . '<p style="margin:0;font-size:8pt">three</p></div>';
        $pdf = $this->getDocument();
        $pdf->setHtmlVSpace(['p' => [0 => ['h' => 0, 'n' => 0], 1 => ['h' => 0, 'n' => 1]]]);
        $pdf->addHTMLCell($html, 10, 10, 150);
        $texts = $this->getTexts($pdf);

        $one = $this->findText($texts, 'one');
        $two = $this->findText($texts, 'two');
        $three = $this->findText($texts, 'three');
        $pitch = $one['y'] - $two['y'];
        $this->assertEqualsWithDelta(2.0 * $pitch, $two['y'] - $three['y'], 0.001);
    }
}
