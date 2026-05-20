<?php

/**
 * E054_page_groups_numbering.php
 *
 * Demonstrates publishing-style mixed page numbering: Roman-numeral front matter
 * followed by Arabic body pages, with a generated table of contents and
 * multi-level bookmarks as cross-reference anchors.
 *
 * What this demonstrates:
 * - setBookmark(): hierarchical outline entries with styles and colors.
 * - setNamedDestination(): stable jump targets independent of page order.
 * - addTOC(): auto-generated TOC page linked back to bookmarked sections.
 * - setDisplayMode(): open with outlines panel showing.
 * - Logical vs physical page numbering: inject visible Roman/Arabic labels
 *   via page header content while the PDF page object index remains 0-based.
 *
 * @since       2026-05-01
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.

require __DIR__ . '/../vendor/autoload.php';

define('K_PATH_FONTS', (string) realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    isunicode: true,
    subsetfont: false,
    compress: true,
    mode: '',
    objEncrypt: null,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 054');
$pdf->setTitle('Page Groups & Mixed Numbering');
$pdf->setKeywords('TCPDF tc-lib-pdf page groups numbering roman arabic TOC bookmarks');
$pdf->setPDFFilename('054_page_groups_numbering.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

// Open with the outline panel visible and full-width zoom.
$pdf->setDisplayMode(zoom: 'fullwidth', layout: 'SinglePage', mode: 'UseOutlines');

$pdf->enableDefaultPageContent();

// ----------
// Fonts

$titleFont = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 16);
$chFont = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 14);
$bodyFont = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$smallFont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

// ----------
// Helpers

/** Convert an integer 1–3999 to an uppercase Roman numeral. */
$toRoman = static function (int $n): string {
    $map = [
        1000 => 'M',
        900 => 'CM',
        500 => 'D',
        400 => 'CD',
        100 => 'C',
        90 => 'XC',
        50 => 'L',
        40 => 'XL',
        10 => 'X',
        9 => 'IX',
        5 => 'V',
        4 => 'IV',
        1 => 'I',
    ];
    $result = '';
    foreach ($map as $val => $sym) {
        while ($n >= $val) {
            $result .= $sym;
            $n -= $val;
        }
    }
    return $result;
};

/**
 * Render a page number footer for the current page.
 * @param \Com\Tecnick\Pdf\Tcpdf $pdf
 * @param int $pid Page PID
 * @param string $label Visible page number string (e.g. "I", "1")
 * @param array{out: string} $font Font descriptor
 */
$addPageFooter = static function (\Com\Tecnick\Pdf\Tcpdf $pdf, int $pid, string $label, array $font): void {
    $pdf->page->addContent($font['out'], $pid);
    $pdf->page->addContent(
        $pdf->getTextCell(
            txt: '— ' . $label . ' —',
            posx: 0,
            posy: 282,
            width: 210,
            height: 0,
            offset: 0,
            linespace: 1,
            valign: 'T',
            halign: 'C',
        ),
        $pid,
    );
};

// ===| FRONT MATTER |========================================================
// Pages i, ii (TOC placeholder + Preface)
// These pages use Roman numerals in the footer.

// ----- Page i: TOC placeholder -----
$tocPage = $pdf->addPage(['format' => 'A4']);
$pdf->setBookmark(
    name: 'Table of Contents',
    link: '',
    level: 0,
    page: $tocPage['pid'],
    posx: 0,
    posy: 0,
    fstyle: 'B',
    color: 'gray',
);
$addPageFooter($pdf, $tocPage['pid'], $toRoman(1), $smallFont);

// Reserve header text for this page
$pdf->page->addContent($titleFont['out'], $tocPage['pid']);
$pdf->page->addContent(
    $pdf->getTextCell(
        txt: 'TABLE OF CONTENTS',
        posx: 15,
        posy: 20,
        width: 180,
        height: 0,
        offset: 0,
        linespace: 1,
        valign: 'T',
        halign: 'C',
    ),
    $tocPage['pid'],
);
// The actual TOC content is injected by addTOC() at the end of the script.

// ----- Page ii: Preface -----
$prefacePage = $pdf->addPage(['format' => 'A4']);
$pdf->setBookmark(
    name: 'Preface',
    link: '',
    level: 0,
    page: $prefacePage['pid'],
    posx: 0,
    posy: 0,
    fstyle: '',
    color: 'gray',
);
$addPageFooter($pdf, $prefacePage['pid'], $toRoman(2), $smallFont);

$prefaceHtml = '<h2 style="font-family: helvetica; text-align: center;">Preface</h2>
<p style="font-family: helvetica; font-size: 10pt;">
    This document demonstrates mixed page numbering conventions used in publishing workflows.
    Front matter (title, preface, table of contents) carries Roman numerals (i, ii, iii …),
    while the body text uses Arabic numerals (1, 2, 3 …).
</p>
<p style="font-family: helvetica; font-size: 10pt;">
    In tc-lib-pdf, both logical page numbers displayed in footers and structural bookmarks are
    controlled independently. The PDF page object index is always zero-based; the visible label
    is rendered as regular text content. Named destinations remain stable regardless of the
    visible numbering scheme.
</p>';
$pdf->addHTMLCell(html: $prefaceHtml, posx: 15, posy: 30, width: 175, height: $prefacePage['pid']);

// ===| BODY |================================================================
// Chapters 1–3, Arabic page numbers 1–3

/** @var array<int, array{title: string, sub: list<string>, color: string, bodyText: string}> $chapters */
$chapters = [
    [
        'title' => 'Chapter 1 — Foundations',
        'sub' => ['1.1 History', '1.2 Key Concepts'],
        'color' => '#1a3c6e',
        'bodyText' => 'This chapter introduces the foundational ideas. Page number 1 (Arabic) appears in the footer. The bookmark entry for this chapter points to this page via a named destination.',
    ],
    [
        'title' => 'Chapter 2 — Methods',
        'sub' => ['2.1 Approach A', '2.2 Approach B'],
        'color' => '#1a6e3c',
        'bodyText' => 'This chapter details the methodology. Page number 2 (Arabic) appears below. Bookmark level-1 entries for subsections use the same page pid with a vertical offset.',
    ],
    [
        'title' => 'Chapter 3 — Results',
        'sub' => ['3.1 Findings', '3.2 Conclusions'],
        'color' => '#6e1a1a',
        'bodyText' => 'Final chapter with results and conclusions. Page number 3 (Arabic). The TOC on page i links back to all three chapter bookmarks.',
    ],
];

foreach ($chapters as $idx => $ch) {
    $arabicNum = $idx + 1;
    $chPage = $pdf->addPage(['format' => 'A4']);

    // Register named destination so TOC links are stable.
    $destName = $pdf->setNamedDestination(name: 'chapter-' . $arabicNum, page: $chPage['pid'], posx: 0, posy: 0);

    // Top-level bookmark (bold, chapter color)
    $pdf->setBookmark(
        name: $ch['title'],
        link: $destName,
        level: 0,
        page: $chPage['pid'],
        posx: 0,
        posy: 0,
        fstyle: 'B',
        color: $ch['color'],
    );

    // Sub-section bookmarks (indented, normal weight)
    foreach ($ch['sub'] as $si => $subTitle) {
        $subY = 70.0 + ($si * 20.0);
        $pdf->setBookmark(
            name: $subTitle,
            link: '',
            level: 1,
            page: $chPage['pid'],
            posx: 0,
            posy: $subY,
            fstyle: '',
            color: $ch['color'],
        );
    }

    // Page footer with Arabic numeral
    $addPageFooter($pdf, $chPage['pid'], (string) $arabicNum, $smallFont);

    // Chapter content
    $chHtml =
        '<h2 style="font-family: helvetica; color: '
        . $ch['color']
        . ';">'
        . htmlspecialchars($ch['title'])
        . '</h2>
<p style="font-family: helvetica; font-size: 10pt;">'
        . htmlspecialchars($ch['bodyText'])
        . '</p>';
    foreach ($ch['sub'] as $subTitle) {
        $chHtml .=
            '<h3 style="font-family: helvetica; color: '
            . $ch['color']
            . ';">'
            . htmlspecialchars($subTitle)
            . '</h3>
<p style="font-family: helvetica; font-size: 10pt;">Section content would go here.</p>';
    }
    $pdf->addHTMLCell(html: $chHtml, posx: 15, posy: 20, width: 175, height: $chPage['pid']);
}

// ===| GENERATE TOC |========================================================
// addTOC() injects the outline entries onto the reserved TOC page (page i),
// starting at y=35 (below the "TABLE OF CONTENTS" heading).
// It uses the current font for rendering — activate bodyFont first.

$pdf->page->addContent($bodyFont['out'], $tocPage['pid']);
$pdf->addTOC(page: $tocPage['pid'], posx: 15, posy: 35, width: 175);

// ----------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
