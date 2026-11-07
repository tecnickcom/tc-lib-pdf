<?php

/**
 * HTMLLinearTimeTest.php
 *
 * @since     2026-11-07
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
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

/**
 * HTML and CSS scanners: results and running time on large inputs.
 *
 * @since     2026-11-07
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type THTMLDOMNode from \Com\Tecnick\Pdf\HTML
 */
class HTMLLinearTimeTest extends TestUtil
{
    /**
     * Regular expression with the same matches as getCSSStyleBlocks(), used as reference on short inputs.
     */
    private const STYLE_BLOCK_REGEX = '/<style([^>]*?)>([^<]*+(?:<(?!\/style\b)[^<]*+)*+)<\/style>/is';

    /**
     * Maximum running time, in seconds, of one call on a large input.
     */
    private const MAX_SECONDS = 1.0;

    /**
     * @return list<array{0: int, 1: int, 2: string, 3: string}>
     *
     * @mago-expect analysis:docblock-type-mismatch
     */
    private static function regexBlocks(string $regex, string $html): array
    {
        $matches = [];
        \preg_match_all($regex, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        /** @var list<array<int, array{0: string, 1: int}>> $matches */
        $blocks = [];
        foreach ($matches as $match) {
            [$full, $offset] = $match[0] ?? ['', 0];
            $blocks[] = [$offset, $offset + \strlen($full), ($match[1] ?? [''])[0], ($match[2] ?? [''])[0]];
        }

        return $blocks;
    }

    /**
     * @param list<array{start: int, end: int, attr: string, body: string}> $blocks
     *
     * @return list<array{0: int, 1: int, 2: string, 3: string}>
     */
    private static function blockList(array $blocks): array
    {
        $list = [];
        foreach ($blocks as $block) {
            $list[] = [$block['start'], $block['end'], $block['attr'], $block['body']];
        }

        return $list;
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function styleBlockProvider(): array
    {
        return [
            'plain' => ['<style>p{color:red}</style>'],
            'attributes' => ['<style type="text/css" media="print">p{}</style>x<STYLE>q{}</STYLE>'],
            'closer with space' => ['<style>a</style ><style>b</style>'],
            'closer with word char' => ['<style>a</styles>b</style_x></style>'],
            'name prefix' => ['<styles>a</style><stylex q>b</style>'],
            'unclosed then closed' => ['<style>a<style>b</style>'],
            'no closer' => ['<style><style><style>'],
            'no gt' => ['<style'],
            'bad closer last' => ['<style><style>a</style x>'],
            'content with tags' => ['<style><!-- p{} --><b></style>'],
            'end of string closer' => ['<style>a</style'],
        ];
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('styleBlockProvider')]
    public function testStyleBlocksMatchReferenceRegex(string $html): void
    {
        $obj = new TestableHTML();
        $this->assertSame(
            self::regexBlocks(self::STYLE_BLOCK_REGEX, $html),
            self::blockList($obj->exposeGetCSSStyleBlocks($html)),
        );
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string}>
     */
    public static function elementBlockProvider(): array
    {
        return [
            'head' => [
                '<head><title>t</title></head><HEAD x>y</HEAD>',
                '<head',
                '</head>',
                '/<head([^>]*?)>(.*?)<\/head>/is',
            ],
            'header prefix' => ['<header>a</head>b</header>', '<head', '</head>', '/<head([^>]*?)>(.*?)<\/head>/is'],
            'head no closer' => ['<head><head>x', '<head', '</head>', '/<head([^>]*?)>(.*?)<\/head>/is'],
            'select' => [
                '<select a>1</select><SELECT>2</SELECT><select',
                '<select',
                '</select>',
                "'<select([^>]*)>(.*?)</select>'si",
            ],
            'button' => [
                '<button type=x>b</button><button>',
                '<button',
                '</button>',
                "'<button([^>]*)>(.*?)</button>'si",
            ],
            'cssarray' => [
                '<cssarray>a</cssarray><cssarray x>b</cssarray>',
                '<cssarray>',
                '</cssarray>',
                '/<cssarray>()(.*?)<\/cssarray>/is',
            ],
        ];
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('elementBlockProvider')]
    public function testElementBlocksMatchReferenceRegex(string $html, string $open, string $close, string $regex): void
    {
        $obj = new TestableHTML();
        $this->assertSame(
            self::regexBlocks($regex, $html),
            self::blockList($obj->exposeGetHTMLElementBlocks($html, $open, $close)),
        );
    }

    /**
     * @throws \Throwable
     */
    public function testSelectTokens(): void
    {
        $obj = new TestableHTML();
        $this->assertSame(
            [
                ['optgroup',  ' label="G"', ''],
                ['option',    ' value="1"', 'One'],
                ['/optgroup', '',           ''],
                ['option',    '',           'Two'],
                ['optgroup',  '',           ''],
            ],
            $obj->exposeGetHTMLSelectTokens(
                '<optgroup label="G"><option value="1">One</option></optgroup><OPTION>Two</OPTION><option>x<optgroup>',
            ),
        );
    }

    /**
     * @throws \Throwable
     */
    public function testTagAttributeValue(): void
    {
        $obj = new TestableHTML();
        $this->assertSame('a b', $obj->exposeGetHTMLTagAttributeValue(' x="1"  value = "a b"', 'value'));
        $this->assertSame('q', $obj->exposeGetHTMLTagAttributeValue(" \tVALUE='q' value=r", 'value'));
        $this->assertSame('r', $obj->exposeGetHTMLTagAttributeValue(' value=r>', 'value'));
        $this->assertSame('', $obj->exposeGetHTMLTagAttributeValue(' value=""', 'value'));
        $this->assertNull($obj->exposeGetHTMLTagAttributeValue('value="x" xvalue="y"', 'value'));
    }

    /**
     * @throws \Throwable
     */
    public function testStyleValueTokens(): void
    {
        $obj = new TestableHTML();
        $this->assertSame(['1px', 'calc(1px + 2px)'], $obj->exposeGetHTMLStyleValueTokens(' 1px calc(1px + 2px) '));
        $this->assertSame(['a(b)c', 'd'], $obj->exposeGetHTMLStyleValueTokens("a(b)c\td"));
        $this->assertSame(['1px', '2px'], $obj->exposeGetHTMLStyleValueTokens('1px (2px'));
        $this->assertSame(['1px)', '2px'], $obj->exposeGetHTMLStyleValueTokens('1px) 2px'));
        $this->assertSame([], $obj->exposeGetHTMLStyleValueTokens('(((('));
    }

    /**
     * @throws \Throwable
     */
    public function testBackgroundShorthandColor(): void
    {
        $obj = new TestableHTML();
        $this->assertSame('rgb(0,0,255)', $obj->exposeGetHTMLBackgroundShorthandColor('url(a b.png) no-repeat blue'));
        $this->assertSame('rgb(255,0,0)', $obj->exposeGetHTMLBackgroundShorthandColor('rgb(255, 0, 0) x'));
        $this->assertSame('', $obj->exposeGetHTMLBackgroundShorthandColor('a red )'));
    }

    /**
     * @throws \Throwable
     */
    public function testSanitizeHTML(): void
    {
        $obj = new TestableHTML();
        $kept = $obj->exposeKeptSpace();
        $this->assertSame(
            '<pre class="c">a' . $kept . $kept . 'b<br' . $kept . '/><b>c' . $kept . 'd</b><br' . $kept . '/></pre>',
            $obj->exposeSanitizeHTML("<pre class=\"c\">a  b\n<b>c d</b>\n</pre>"),
        );
        $this->assertSame('<p>x</p><select name="s" opt="#!SeL!#1#!TaB!#G - One#!NwL!#2#!TaB!#Two#!NwL!#Three" />', $obj->exposeSanitizeHTML(
            '<p>x</p><select name="s"><optgroup label="G"><option value="1" selected>One</option></optgroup>'
            . "<option value='2'>Two</option><option>Three</option></select>",
        ));
        $this->assertSame(
            '<form><button name="b" type="submit" value="Send" /><button type="button" value="B" /></form>',
            $obj->exposeSanitizeHTML('<form><button  type="submit" value="x" name="b"><b>Send</b></button>'
            . '<button type=bogus>B</button></form>'),
        );
        $this->assertSame(
            'xq{}y',
            $obj->exposeSanitizeHTML('<head><title>t</title></head><style>p{color:red}</style>x<style>q{}</style >y'),
        );
    }

    /**
     * @throws \Throwable
     */
    public function testTextareaQuotesStayInsideTheValue(): void
    {
        $obj = new TestableHTML();
        $this->assertSame(
            '<textarea name="t" value="say \'\'hi\'\'" />',
            $obj->exposeSanitizeHTML('<textarea name="t">say "hi"</textarea>'),
        );
        $this->assertSame(
            "<textarea name=\"t\" value=\"a ''b'' ''c''\" />",
            $obj->exposeSanitizeHTML("<textarea name=\"t\">a\n\"b\" \"c\"</textarea>"),
        );

        foreach ($obj->exposeGetHTMLDOM('<textarea name="t">x" readonly="readonly</textarea>') as $node) {
            if (!($node['value'] === 'textarea' && $node['opening'])) {
                continue;
            }

            $this->assertSame("x'' readonly=''readonly", $node['attribute']['value'] ?? null);
            $this->assertArrayNotHasKey('readonly', $node['attribute']);
            return;
        }

        $this->fail('No textarea node');
    }

    /**
     * @throws \Throwable
     */
    public function testClosingKeysMatchTheForwardScan(): void
    {
        $obj = new TestableHTML();
        $dom = $obj->exposeGetHTMLDOM(
            '<table><tr><td><table><tr><td>a</td></tr></table><td>b<tr><td>c</table>'
            . '<div><span><div>d</div><label>e<input type="text" name="i"><select name="s">'
            . '<b><i>f</b></i></div>',
        );
        $checked = 0;
        foreach ($dom as $key => $node) {
            if (!isset($node['closekey'])) {
                continue;
            }

            $plain = $dom;
            unset($plain[$key]['closekey']);
            $this->assertSame($obj->exposeFindHTMLClosingTagIndex($plain, $key), $node['closekey']);
            ++$checked;
        }

        $this->assertGreaterThan(10, $checked);
    }

    /**
     * @throws \Throwable
     */
    public function testLabelText(): void
    {
        $obj = new TestableHTML();
        $html =
            '<label for="f">Name</label><label for="g"></label><label for="g">Second</label>'
            . '<input type="text" id="f" name="a"><input type="text" id="g" name="b">'
            . '<label>Outer <span><input type="text" name="c"></span></label>';
        $dom = $obj->exposeGetHTMLDOM($html);
        $labels = [];
        foreach ($dom as $key => $node) {
            if (!($node['value'] === 'input' && $node['opening'])) {
                continue;
            }

            $labels[] = $obj->exposeGetHTMLLabelTextForControlWithDom($dom, $key);
        }

        $this->assertSame(['Name', 'Second', 'Outer'], $labels);

        $other = $obj->exposeGetHTMLDOM('<label for="f">Other</label><input type="text" id="f" name="a">');
        foreach ($other as $key => $node) {
            if (!($node['value'] === 'input' && $node['opening'])) {
                continue;
            }

            $this->assertSame('Other', $obj->exposeGetHTMLLabelTextForControlWithDom($other, $key));
        }
    }

    /**
     * @throws \Throwable
     */
    public function testInheritedTextProperty(): void
    {
        $obj = new TestableHTML();
        $dom = $obj->exposeGetHTMLDOM(
            '<div style="word-break:break-all"><span><b>x</b></span></div><p><span>y</span></p>',
        );
        $values = [];
        foreach ($dom as $key => $node) {
            if (!(!$node['tag'] && \in_array($node['value'], ['x', 'y'], true))) {
                continue;
            }

            $values[] = $obj->exposeGetHTMLInheritedTextPropertyWithDom($dom, $key, 'word-break');
        }

        $this->assertSame(['break-all', 'normal'], $values);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function largeHTMLProvider(): array
    {
        return [
            'open style tags' => [\str_repeat('<style>', 36_000)],
            'closer before open style tags' => ['</style>' . \str_repeat('<style>', 36_000)],
            'style closer without gt' => [\str_repeat('<style>', 36_000) . '</style x>'],
            'open head tags' => [\str_repeat('<head>', 40_000)],
            'open select tags' => [\str_repeat('<select>', 30_000)],
            'pre newlines' => ['<pre>' . \str_repeat("a\n", 64_000) . '</pre>'],
            'pre spaces' => ['<pre>' . \str_repeat('a ', 64_000) . '</pre>'],
            'textarea newlines' => ['<textarea name="t">' . \str_repeat("a\n", 64_000) . '</textarea>'],
            'pre blocks' => [\str_repeat('<pre>a</pre>', 20_000)],
            'pre closers' => [\str_repeat('</pre>', 40_000)],
            'option attribute spaces' => [
                '<select name="s"><option' . \str_repeat(' ', 128_000) . 'x value="v">o</option></select>',
            ],
            'button attribute spaces' => ['<button' . \str_repeat(' ', 128_000) . 'x type="button">b</button>'],
            'open option tags' => ['<select name="s">' . \str_repeat('<option>', 30_000) . '</select>'],
            'img attribute spaces' => ['<img src="a.png"' . \str_repeat(' ', 128_000) . '>'],
        ];
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('largeHTMLProvider')]
    public function testLargeHTMLIsSanitizedInBoundedTime(string $html): void
    {
        $obj = new TestableHTML();
        $cssobj = new TestableCSS();
        $start = \microtime(true);
        $css = $html;
        $cssobj->exposeGetCSSArrayFromHTML($css);
        $obj->exposeSanitizeHTML($html);
        $this->assertLessThan(self::MAX_SECONDS, \microtime(true) - $start);
    }

    /**
     * @mago-expect lint:no-ini-set
     * @throws \Throwable
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testLargeHTMLIsSanitizedInBoundedTimeWithoutJIT(): void
    {
        $jit = (string) \ini_get('pcre.jit');
        \ini_set('pcre.jit', '0');
        try {
            foreach (self::largeHTMLProvider() as $name => [$html]) {
                $obj = new TestableHTML();
                $start = \microtime(true);
                $obj->exposeSanitizeHTML($html);
                $this->assertLessThan(self::MAX_SECONDS, \microtime(true) - $start, $name);
            }
        } finally {
            \ini_set('pcre.jit', $jit);
        }
    }

    /**
     * @throws \Throwable
     */
    public function testLargeCSSValuesInBoundedTime(): void
    {
        $obj = new TestableHTML();
        $start = \microtime(true);
        $this->assertSame([], $obj->exposeGetHTMLStyleValueTokens(\str_repeat('(', 128_000)));
        $this->assertSame('', $obj->exposeGetHTMLBackgroundShorthandColor('a' . \str_repeat(' ', 128_000) . ')'));
        $this->assertSame('', $obj->exposeGetHTMLBackgroundShorthandColor(\str_repeat(' a', 64_000) . ')'));
        $this->assertLessThan(self::MAX_SECONDS, \microtime(true) - $start);
    }

    /**
     * @throws \Throwable
     */
    public function testLongQuotedStringsAndSelectors(): void
    {
        $spec = \Com\Tecnick\Pdf\CSS\Specificity::fromSelector('a[title="' . \str_repeat('x', 64_000) . '"]');
        $this->assertSame(0, $spec->idCount);
        $this->assertSame(1, $spec->classCount);
        $this->assertSame(1, $spec->typeCount);
        $this->assertNotNull(\Com\Tecnick\Pdf\CSS\SelectorMatcher::compile(\str_repeat('.a', 20_000)));
    }

    /**
     * @throws \Throwable
     */
    public function testSvgImageDataWithLongLeadingWhitespace(): void
    {
        $obj = new TestableHTML();
        $this->assertTrue($obj->exposeIsHTMLSvgImageData(\str_repeat(' ', 60_000) . '<svg>'));
        $this->assertFalse($obj->exposeIsHTMLSvgImageData(\str_repeat(' ', 60_000) . '<svgx>'));
    }

    /**
     * @throws \Throwable
     */
    public function testLargePemCertificate(): void
    {
        $obj = new TestableOutput();
        $pem =
            "-----BEGIN CERTIFICATE-----\n"
            . \chunk_split(\str_repeat('A', 64_000), 64, "\n")
            . "-----END CERTIFICATE-----\n";
        $this->assertSame([\rtrim($pem)], $obj->exposeExtractPemCertificates($pem . "x\n"));
    }
}
