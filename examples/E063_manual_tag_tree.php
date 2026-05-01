<?php
/**
 * E063_manual_tag_tree.php
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

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

/**
 * Demonstrate manual PDF/UA structure-element tagging via
 * beginStructElem() / endStructElem().
 *
 * Background
 * ──────────
 * Accessible (tagged) PDFs carry a logical structure tree that screen readers
 * use to navigate and read content in reading order.  When tc-lib-pdf operates
 * in a pdfua or pdfua1/pdfua2 mode it auto-tags content produced by
 * addHTMLCell() using the HTML semantic elements (h1→H1, p→P, etc.).
 *
 * For content produced with the lower-level getTextCell() API — or for cases
 * where you need precise control over structure roles — you can bracket each
 * logical block with:
 *
 *   beginStructElem(string $role, int $pid)
 *       Opens a structure-element bracket.  All tagged content produced
 *       until the matching endStructElem() is associated with this element.
 *       $role is a PDF structure role (see PDF 32000 §14.8):
 *         Headings : H, H1 … H6
 *         Body     : P, Blockquote, Note, Caption
 *         List     : L, LI, Lbl, LBody
 *         Table    : Table, TR, TH, TD
 *         Inline   : Span, Link, Reference, BibEntry
 *         Other    : Document, Part, Sect, Art, Figure, TOC, TOCI
 *       $pid is the page ID from addPage()['pid'].
 *
 *   endStructElem()
 *       Closes the current bracket.  The completed element (with all its
 *       tagged content MCIDs) is appended to the structure log.
 *       Empty elements (no tagged content inside the bracket) are silently
 *       discarded.
 *
 * When pdfuaMode is '' (plain PDF mode) both methods are no-ops, so the
 * same code runs safely in plain and PDF/UA modes without changes.
 *
 * NOTE: getTextCell() is the return-string variant used with
 *   $pdf->page->addContent(...)
 * addTextCell() is the void variant that writes directly to the page;
 * it has a different signature (second arg is int $pid).
 *
 * This example uses 'pdfua1' mode and manually tags H1, H2, H3, P, Caption,
 * Note, and Blockquote roles using getTextCell() inside each bracket.
 *
 * Compare with E015–E017 which enable pdfua mode via addHTMLCell (auto-tagged).
 */

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm',
    true,
    false,
    true,
    'pdfua1',   // PDF/UA-1 mode — structure tree is written to output
    null,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 063');
$pdf->setTitle('Manual Tag Tree (PDF/UA Structure Elements)');
$pdf->setKeywords('TCPDF tc-lib-pdf example pdfua tagged structure accessibility wcag screen reader');
$pdf->setPDFFilename('E063_manual_tag_tree.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

$leftMargin = 15.0;
$rightMargin = 15.0;
$pdf->setDefaultCellMargin(0.0, $rightMargin, 0.0, 0.0);

$setFont = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    string $family,
    string $style,
    int $size,
): array {
    $font = $pdf->font->insert($pdf->pon, $family, $style, $size);
    $pdf->page->addContent($font['out']);
    return $font;
};

// -----------------------------------------------------------------------
// Font setup — insert before addPage()
// -----------------------------------------------------------------------
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);

// -----------------------------------------------------------------------
// Page 1 — H1, H2, H3, P, Caption, Figure structure elements
// -----------------------------------------------------------------------
$page1 = $pdf->addPage();
$pid1  = $page1['pid'];

// --- H1 heading ---
$setFont($pdf, 'helvetica', 'B', 20);

$pdf->beginStructElem('H1', $pid1);
$pdf->page->addContent(
    $pdf->getTextCell(
        'Manual Tag Tree — PDF/UA Structure Elements',
        $leftMargin, 20.0, 0.0, 0.0,
        drawcell: false, valign: 'T', halign: 'L',
    )
);
$pdf->endStructElem();

// --- Introductory paragraph (P) ---
$setFont($pdf, 'helvetica', '', 10);

$intro = 'This document uses beginStructElem() and endStructElem() to build '
    . 'the accessibility structure tree manually.  Every piece of text is '
    . 'wrapped in an explicit structure-element bracket that maps content '
    . 'to a PDF logical role (H1, H2, H3, P, Caption).  A screen reader can '
    . 'navigate the heading hierarchy and read body paragraphs in the correct '
    . 'logical order.';

$pdf->beginStructElem('P', $pid1);
$pdf->page->addContent(
    $pdf->getTextCell($intro, $leftMargin, 32.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J')
);
$pdf->endStructElem();

// --- Section 1: H2 + P ---
$setFont($pdf, 'helvetica', 'B', 14);

$pdf->beginStructElem('H2', $pid1);
$pdf->page->addContent(
    $pdf->getTextCell('Section 1 — Heading Roles', $leftMargin, 55.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'L')
);
$pdf->endStructElem();

$setFont($pdf, 'helvetica', '', 10);

$sec1body = 'Structure roles H1 through H6 define the heading hierarchy. '
    . 'H1 is the document title; H2 is a top-level section heading; H3 is a '
    . 'sub-section; and so on.  Screen readers present this outline as a '
    . 'navigable table of contents.  Nesting is implied by the sequence of '
    . 'structure elements logged by endStructElem().';

$pdf->beginStructElem('P', $pid1);
$pdf->page->addContent(
    $pdf->getTextCell($sec1body, $leftMargin, 64.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J')
);
$pdf->endStructElem();

// --- Sub-section: H3 ---
$setFont($pdf, 'helvetica', 'BI', 11);

$pdf->beginStructElem('H3', $pid1);
$pdf->page->addContent(
    $pdf->getTextCell('1.1 — Sub-section with H3 role', $leftMargin, 85.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'L')
);
$pdf->endStructElem();

$setFont($pdf, 'helvetica', '', 10);

$pdf->beginStructElem('P', $pid1);
$pdf->page->addContent(
    $pdf->getTextCell(
        'An H3 element is used here to show a third level in the outline. '
        . 'The structure tree will contain: H1 > H2 > H3 > P, reflecting the '
        . 'logical reading order of this page.',
        $leftMargin, 93.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J',
    )
);
$pdf->endStructElem();

// --- Section 2: H2 + P + Figure + Caption ---
$setFont($pdf, 'helvetica', 'B', 14);

$pdf->beginStructElem('H2', $pid1);
$pdf->page->addContent(
    $pdf->getTextCell('Section 2 — Figure and Caption Roles', $leftMargin, 113.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'L')
);
$pdf->endStructElem();

$setFont($pdf, 'helvetica', '', 10);

$pdf->beginStructElem('P', $pid1);
$pdf->page->addContent(
    $pdf->getTextCell(
        'The Figure role wraps graphical content. '
        . 'A Caption role immediately following a Figure provides an '
        . 'accessible description of the visual element for screen-reader users.',
        $leftMargin, 122.0, 150, 0.0, drawcell: false, valign: 'T', halign: 'J',
    )
);
$pdf->endStructElem();

// Draw a simple coloured rectangle as a stand-in "figure".
// Note: graph drawing calls are NOT tagged — only text is taggable via
// beginStructElem/endStructElem.  For a real Figure, wrap any associated
// alt-text in a Figure bracket.
$figStyle = [
    'all' => [
        'lineWidth' => 0.5,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => '#336699',
        'fillColor' => '#cce0ff',
    ],
];
$pdf->page->addContent(
    $pdf->graph->getRect($leftMargin, 143.0, 180.0, 25.0, 'DF', $figStyle)
);

// Alt-text placeholder for the figure — tagged as Figure role.
$pdf->beginStructElem('Figure', $pid1);
$pdf->page->addContent(
    $pdf->color->getPdfColor('black')
    .
    $pdf->getTextCell(
        '[Figure: placeholder rectangle — in production, place image here]',
        $leftMargin+10, 151.0, 0.0, 0.0, drawcell: false, valign: 'M', halign: 'C',
    )
);
$pdf->endStructElem();

// Caption for the figure.
$setFont($pdf, 'helvetica', 'I', 8);

$pdf->beginStructElem('Caption', $pid1);
$pdf->page->addContent(
    $pdf->getTextCell(
        'Figure 1 — Placeholder rectangle representing graphical content (Caption role)',
        $leftMargin, 170.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'C',
    )
);
$pdf->endStructElem();

// -----------------------------------------------------------------------
// Page 2 — Note, Blockquote, and multi-paragraph body
// -----------------------------------------------------------------------
$page2 = $pdf->addPage();
$pid2  = $page2['pid'];

$setFont($pdf, 'helvetica', 'B', 14);

$pdf->beginStructElem('H2', $pid2);
$pdf->page->addContent(
    $pdf->getTextCell('Page 2 — Note, Blockquote, and Multi-element Brackets', $leftMargin, 20.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'L')
);
$pdf->endStructElem();

$setFont($pdf, 'helvetica', '', 10);

// Note element
$pdf->beginStructElem('Note', $pid2);
$pdf->page->addContent(
    $pdf->getTextCell(
        'NOTE: beginStructElem() / endStructElem() are no-ops in plain PDF mode '
        . '(pdfuaMode = "").  The same code runs safely in both plain and PDF/UA '
        . 'modes without conditional branching.',
        $leftMargin, 32.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J',
    )
);
$pdf->endStructElem();

// Blockquote element
$setFont($pdf, 'helvetica', 'BI', 11);

$pdf->beginStructElem('Blockquote', $pid2);
$pdf->page->addContent(
    $pdf->getTextCell(
        '"Tagged PDF is the foundation of accessible PDF. '
        . 'Without a well-formed structure tree, automated tools and assistive '
        . 'technologies cannot reliably interpret document content."',
        $leftMargin, 52.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J',
    )
);
$pdf->endStructElem();

$setFont($pdf, 'helvetica', 'I', 8);

$pdf->beginStructElem('Caption', $pid2);
$pdf->page->addContent(
    $pdf->getTextCell('— ISO 14289-1:2012 (PDF/UA-1) rationale', $leftMargin, 72.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'R')
);
$pdf->endStructElem();

// Multi-paragraph body section
$setFont($pdf, 'helvetica', '', 10);

$paras = [
    'Each call to endStructElem() closes the innermost open bracket.  '
    . 'The current implementation logs a flat sequence of completed elements; '
    . 'the PDF viewer builds the visual hierarchy from the role sequence and '
    . 'the order in which elements were closed.',

    'A structure bracket that receives no tagged content (i.e. no getTextCell '
    . 'call between beginStructElem and endStructElem) is silently discarded.  '
    . 'This prevents empty structure nodes from appearing in the tree.',

    'For table structures (Table, TR, TH, TD) use the same pattern: open a '
    . 'Table bracket, then TR for each row, then TH/TD for each cell, then '
    . 'close in reverse order.  The library accumulates MCIDs and emits the '
    . 'complete structure tree when getOutPDFString() is called.',
];

$yPos = 85.0;
foreach ($paras as $para) {
    $pdf->beginStructElem('P', $pid2);
    $pdf->page->addContent(
        $pdf->getTextCell($para, $leftMargin, $yPos, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J')
    );
    $pdf->endStructElem();
    $yPos += 25.0;
}

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
