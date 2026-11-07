<?php

/**
 * HTMLTcpdfTagTest.php
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

/**
 * TCPDF tags: signed payloads, objects laid out as image boxes and calls.
 *
 * @since     2026-11-07
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class HTMLTcpdfTagTest extends TestUtil
{
    /** Points per millimeter. */
    private const PT = 72.0 / 25.4;

    /**
     * @throws \Throwable
     */
    private function getObject(): TestableHTMLTcpdfTag
    {
        self::setUpFontsPath();
        $obj = new TestableHTMLTcpdfTag();
        $this->initFontAndPage($obj);

        return $obj;
    }

    /**
     * Return the placements of the form XObjects: [x, y, width, height] in points
     * (y is the bottom edge, from the bottom of the page).
     *
     * @return list<array{float, float, float, float}>
     */
    private function placements(TestableHTMLTcpdfTag $obj, string $out): array
    {
        $matches = [];
        \preg_match_all(
            '/([0-9.\-]+) [0-9.\-]+ [0-9.\-]+ ([0-9.\-]+) ([0-9.\-]+) ([0-9.\-]+) cm\n\/(XT[0-9]+) Do/',
            $out,
            $matches,
            PREG_SET_ORDER,
        );
        $boxes = [];
        /** @var array<string, array{w: float, h: float}> $xobjects */
        $xobjects = $this->getObjectProperty($obj, 'xobjects');
        foreach ($matches as $match) {
            $xobj = $xobjects[$match[5] ?? ''] ?? ['w' => 0.0, 'h' => 0.0];
            $boxes[] = [
                \floatval($match[3] ?? ''),
                \floatval($match[4] ?? ''),
                \floatval($match[1] ?? '') * $obj->toPoints($xobj['w']),
                \floatval($match[2] ?? '') * $obj->toPoints($xobj['h']),
            ];
        }

        return $boxes;
    }

    /**
     * Return the only form XObject placement of the output.
     *
     * @return array{float, float, float, float}
     */
    private function placement(TestableHTMLTcpdfTag $obj, string $out): array
    {
        $boxes = $this->placements($obj, $out);
        $this->assertCount(1, $boxes);

        return $boxes[0] ?? [0.0, 0.0, 0.0, 0.0];
    }

    /**
     * Return the text position (x, y) in points of the text $txt.
     *
     * @return array{float, float}
     */
    private function textPos(string $out, string $txt): array
    {
        $match = [];
        $found = \preg_match(
            '/BT 0 Tr ([0-9.\-]+) ([0-9.\-]+) Td \(' . \preg_quote($txt, '/') . '\) Tj ET/',
            $out,
            $match,
        );
        $this->assertSame(1, $found, 'text "' . $txt . '" must be drawn');

        return [\floatval($match[1] ?? ''), \floatval($match[2] ?? '')];
    }

    /** @throws \Throwable */
    public function testSerializedPayloadRoundTrips(): void
    {
        $obj = $this->getObject();
        $data = $obj->serializeHTMLTcpdfTag('box', [10, 20.5, 'block']);

        $this->assertMatchesRegularExpression('/^64\+[0-9a-f]{64}\+/', $data);
        $this->assertSame(['m' => 'box', 'p' => [10, 20.5, 'block']], $obj->exposeParseHTMLTcpdfSerializedData($data));
    }

    /** @throws \Throwable */
    public function testTamperedForeignAndMalformedPayloadsAreRejected(): void
    {
        $obj = $this->getObject();
        $data = $obj->serializeHTMLTcpdfTag('box', [10, 10]);
        $other = $this->getObject();
        $encoded = \substr($data, 67);

        $this->assertNull($obj->exposeParseHTMLTcpdfSerializedData($other->serializeHTMLTcpdfTag('box', [10, 10])));
        $this->assertNull($obj->exposeParseHTMLTcpdfSerializedData(\substr($data, 0, 66) . '+' . $encoded . '%20'));
        $this->assertNull($obj->exposeParseHTMLTcpdfSerializedData('64+' . \str_repeat('0', 64) . '+' . $encoded));
        $this->assertNull($obj->exposeParseHTMLTcpdfSerializedData('63+' . \substr($data, 3)));
        $this->assertNull($obj->exposeParseHTMLTcpdfSerializedData(\substr($data, 0, 68)));
        $this->assertNull($obj->exposeParseHTMLTcpdfSerializedData(\substr($data, 0, 67)));
        $this->assertNull($obj->exposeParseHTMLTcpdfSerializedData('64+' . \substr($data, 3, 64)));
        $this->assertNull($obj->exposeParseHTMLTcpdfSerializedData('+' . $data));
    }

    /** @throws \Throwable */
    public function testUnencodableParametersSerializeToAnEmptyString(): void
    {
        $obj = $this->getObject();
        $this->assertSame('', $obj->serializeHTMLTcpdfTag('box', ["\xB1"]));
    }

    /** @throws \Throwable */
    public function testBlockObjectInATableCellGrowsTheRow(): void
    {
        $obj = $this->getObject();
        $box = $obj->serializeHTMLTcpdfTag('box', [25, 25, 'block']);
        $html =
            '<table border="1" cellpadding="2"><tr><td width="70%">cell A</td>'
            . '<td width="30%"><tcpdf data="'
            . $box
            . '" /></td></tr>'
            . '<tr><td>cell A2</td><td>cell B2</td></tr></table><p>END</p>';
        $out = $obj->getHTMLCell($html, 10, 10, 180, 0);

        [$bx, $by, $bw, $bh] = $this->placement($obj, $out);
        $this->assertEqualsWithDelta(25 * self::PT, $bw, 0.01);
        $this->assertEqualsWithDelta(25 * self::PT, $bh, 0.01);

        // The object sits inside the second cell, at the top of the row.
        $cella = $this->textPos($out, 'cell A');
        $this->assertGreaterThan(136 * self::PT, $bx);
        $this->assertLessThanOrEqual(190 * self::PT, $bx + $bw);
        $this->assertGreaterThan($by + $bh - 12, $cella[1]);

        // The second row starts below the object.
        $rowa = $this->textPos($out, 'cell A2');
        $rowb = $this->textPos($out, 'cell B2');
        $this->assertLessThan($by, $rowa[1]);
        $this->assertEqualsWithDelta($rowa[1], $rowb[1], 0.001);
        $this->assertGreaterThan($rowa[0], $rowb[0]);
        $this->assertLessThan($rowa[1], $this->textPos($out, 'END')[1]);
    }

    /** @throws \Throwable */
    public function testInlineObjectStaysOnTheTextLine(): void
    {
        $obj = $this->getObject();
        $box = $obj->serializeHTMLTcpdfTag('box', [6, 6, 'inline', 'bottom']);
        $out = $obj->getHTMLCell('<p>left <tcpdf data="' . $box . '" /> right</p>', 10, 10, 180, 0);

        [$bx, , $bw] = $this->placement($obj, $out);
        $left = $this->textPos($out, 'left ');
        $right = $this->textPos($out, ' right');
        $this->assertGreaterThan($left[0], $bx);
        $this->assertGreaterThanOrEqual($bx + $bw - 0.01, $right[0]);
        $this->assertEqualsWithDelta($left[1], $right[1], 0.001);
    }

    /** @throws \Throwable */
    public function testBlockObjectBreaksTheLine(): void
    {
        $obj = $this->getObject();
        $box = $obj->serializeHTMLTcpdfTag('box', [6, 6, 'block']);
        $out = $obj->getHTMLCell('<p>left <tcpdf data="' . $box . '" /> right</p>', 10, 10, 180, 0);

        [$bx, $by, , $bh] = $this->placement($obj, $out);
        $this->assertEqualsWithDelta(10 * self::PT, $bx, 0.01);
        $this->assertLessThan($this->textPos($out, 'left ')[1], $by + $bh);
        $this->assertLessThan($by, $this->textPos($out, 'right')[1]);
    }

    /** @throws \Throwable */
    public function testBlockObjectFollowsTheHorizontalAlignment(): void
    {
        $obj = $this->getObject();
        $box = $obj->serializeHTMLTcpdfTag('box', [20, 10, 'block', 'bottom', 'C']);
        $out = $obj->getHTMLCell('<tcpdf data="' . $box . '" />', 10, 10, 180, 0);

        $box = $this->placement($obj, $out);
        $this->assertEqualsWithDelta(90 * self::PT, $box[0], 0.01);
    }

    /** @throws \Throwable */
    public function testBlockObjectAlignsLeftAndRight(): void
    {
        $obj = $this->getObject();
        $left = $obj->serializeHTMLTcpdfTag('box', [20, 10, 'block', 'bottom', 'L']);
        $right = $obj->serializeHTMLTcpdfTag('box', [20, 10, 'block', 'bottom', 'R']);
        $html =
            '<div style="text-align:center"><tcpdf data="'
            . $left
            . '" /><tcpdf data="'
            . $right
            . '" class="x" id="r" /></div>';
        $boxes = $this->placements($obj, $obj->getHTMLCell($html, 10, 10, 180, 0));

        $this->assertCount(2, $boxes);
        $this->assertEqualsWithDelta(10 * self::PT, $boxes[0][0] ?? 0.0, 0.01);
        $this->assertEqualsWithDelta(170 * self::PT, $boxes[1][0] ?? 0.0, 0.01);
    }

    /** @throws \Throwable */
    public function testPercentHeightKeepsTheObjectAspectRatio(): void
    {
        $obj = $this->getObject();
        $box = $obj->serializeHTMLTcpdfTag('box', [20, 10, 'block']);
        $out = $obj->getHTMLCell('<tcpdf data="' . $box . '" style="width:40mm;height:50%" />', 10, 10, 180, 0);

        $box = $this->placement($obj, $out);
        $this->assertEqualsWithDelta(40 * self::PT, $box[2], 0.01);
        $this->assertEqualsWithDelta(20 * self::PT, $box[3], 0.01);
    }

    /** @throws \Throwable */
    public function testDefaultHooksRunTheMethodAsACall(): void
    {
        $obj = $this->getObject();
        $data = $obj->serializeHTMLTcpdfTag('setNamedDestination', ['tagdest', -1, 1.0, 1.0]);
        $fail = $obj->serializeHTMLTcpdfTag('fail');
        $empty = $obj->serializeHTMLTcpdfTag('box', [0, 10]);
        $html = '<tcpdf data="' . $fail . '" /><tcpdf data="' . $empty . '" /><tcpdf data="' . $data . '" /><p>x</p>';
        $out = $obj->getHTMLCell($html, 10, 10, 180, 0);

        $this->assertSame([], $this->placements($obj, $out));
        /** @var array<string, mixed> $dests */
        $dests = $this->getObjectProperty($obj, 'dests');
        $this->assertArrayHasKey('tagdest', $dests);
    }

    /** @throws \Throwable */
    public function testCssWidthScalesTheObject(): void
    {
        $obj = $this->getObject();
        $box = $obj->serializeHTMLTcpdfTag('box', [20, 10, 'block']);
        $out = $obj->getHTMLCell('<tcpdf data="' . $box . '" style="width:40mm;height:20mm" />', 10, 10, 180, 0);

        $box = $this->placement($obj, $out);
        $this->assertEqualsWithDelta(40 * self::PT, $box[2], 0.01);
        $this->assertEqualsWithDelta(20 * self::PT, $box[3], 0.01);
    }

    /** @throws \Throwable */
    public function testObjectIsCreatedOncePerPayload(): void
    {
        $obj = $this->getObject();
        $box = $obj->serializeHTMLTcpdfTag('box', [5, 5, 'inline']);
        $tag = '<tcpdf data="' . $box . '" />';
        $out = $obj->getHTMLCell('<p>' . $tag . $tag . '</p>', 10, 10, 180, 0);
        $out .= $obj->getHTMLCell('<p>' . $tag . '</p>', 10, 50, 180, 0);

        $this->assertSame(1, $obj->objectCalls);
        $this->assertCount(3, $this->placements($obj, $out));
    }

    /** @throws \Throwable */
    public function testCallTagOutputFollowsTheDocumentOrder(): void
    {
        $obj = $this->getObject();
        $mark = $obj->serializeHTMLTcpdfTag('mark', ['one']);
        $out = $obj->getHTMLCell('<p>before</p><tcpdf data="' . $mark . '" /><p>after</p>', 10, 10, 180, 0);

        $this->assertCount(1, $obj->markCalls);
        [$label, $posx] = $obj->markCalls[0] ?? ['', 0.0, 0.0];
        $this->assertSame('one', $label);
        $this->assertEqualsWithDelta(10.0, $posx, 0.001);
        $before = \strpos($out, '(before) Tj');
        $markpos = \strpos($out, '% mark one');
        $after = \strpos($out, '(after) Tj');
        $this->assertNotFalse($before);
        $this->assertNotFalse($markpos);
        $this->assertNotFalse($after);
        $this->assertGreaterThan($before, $markpos);
        $this->assertGreaterThan($markpos, $after);
    }

    /** @throws \Throwable */
    public function testCallTagsDoNotRunInDiscardedPasses(): void
    {
        $obj = $this->getObject();
        $mark = $obj->serializeHTMLTcpdfTag('mark', ['measure']);
        $height = $obj->exposeMeasureHTMLCellRenderedHeight(
            '<p>x</p><tcpdf data="' . $mark . '" /><p>y</p>',
            0,
            0,
            100,
        );

        $this->assertGreaterThan(0.0, $height);
        $this->assertSame([], $obj->markCalls);
    }

    /** @throws \Throwable */
    public function testUnverifiedDisallowedAndForgedTagsDrawNothing(): void
    {
        $obj = $this->getObject();
        $other = $this->getObject();
        $foreign = $other->serializeHTMLTcpdfTag('box', [10, 10]);
        $foreignmark = $other->serializeHTMLTcpdfTag('mark', ['foreign']);
        $blocked = $obj->serializeHTMLTcpdfTag('setPDFFilename', ['blocked.pdf']);
        $html =
            '<tcpdf data="'
            . $foreign
            . '" /><tcpdf data="'
            . $foreignmark
            . '" />'
            . '<tcpdf data="'
            . $blocked
            . '" /><tcpdf data="" />'
            . '<p>forged <img src="tcpdfobj:00" width="10mm" height="10mm" alt="nothing" /></p>'
            . '<p>forged <img src="tcpdfobj:01" width="10mm" alt="nowidth" /></p>';
        $out = $obj->getHTMLCell($html, 10, 10, 180, 0);

        $this->assertSame([], $this->placements($obj, $out));
        $this->assertSame(0, $obj->objectCalls);
        $this->assertSame([], $obj->markCalls);
        $this->assertStringContainsString('(nothing)', $out);
        $this->assertNotSame('blocked.pdf', $this->getObjectProperty($obj, 'pdffilename'));
    }

    /** @throws \Throwable */
    public function testObjectMovesToTheNextPageWithItsRow(): void
    {
        $obj = $this->getObject();
        $box = $obj->serializeHTMLTcpdfTag('box', [40, 40, 'block']);
        $rows = \str_repeat('<tr><td>row</td><td>text</td></tr>', 62);
        $html = '<table border="1">' . $rows . '<tr><td>last</td><td><tcpdf data="' . $box . '" /></td></tr></table>';
        $obj->addHTMLCell($html, 10, 10, 180);

        $first = \implode("\n", $obj->page->getPage(0)['content']);
        $second = \implode("\n", $obj->page->getPage(1)['content']);
        $this->assertStringNotContainsString(' Do', $first);
        $this->assertStringContainsString(' Do', $second);
        $this->assertStringContainsString('(last) Tj', $second);
    }
}
