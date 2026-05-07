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
 * For content produced with the lower-level text APIs — or for cases
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
 * NOTE: beginStructElem()/endStructElem() only associate MCIDs with content
 * emitted through the tagged text path. For manual tagging, use addTextCell()
 * (second argument is the page ID) rather than writing raw getTextCell()
 * output directly with page->addContent().
 *
 * This example uses 'pdfua1' mode and demonstrates the full manual-tagging
 * surface across four pages:
 *
 *   Page 1 — Heading roles (H1, H2, H3), P, Figure, Caption
 *   Page 2 — Note, Blockquote, multi-paragraph body
 *   Page 3 — Sect/Art grouping containers; list structure (L > LI > Lbl + LBody)
 *   Page 4 — Table nesting (Table > TR > TH / TD); Figure with /Alt alternate text
 *
 * Non-semantic decorative content can be emitted as Artifact marked-content
 * with addArtifactContent() / beginArtifact() / endArtifact().  This example
 * adds decorative separator rules as /Artifact with /Type /Layout.
 *
 * All content is tagged with addTextCell() inside explicit struct-elem brackets.
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

$leftMargin = 15.0;
$rightMargin = 15.0;
$pdf->setDefaultCellMargin(0.0, $rightMargin, 0.0, 0.0);

$setFont = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    string $family,
    string $style,
    int $size,
): array {
    if ($family === 'helvetica') {
        $family = 'dejavusans';
    }

    $font = $pdf->font->insert($pdf->pon, $family, $style, $size);
    $pdf->page->addContent($font['out']);
    return $font;
};

$addDecorativeSeparator = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    int $pid,
    float $posY,
) use ($leftMargin): void {
    $lineStyle = [
        'all' => [
            'lineWidth' => 0.25,
            'lineCap' => 'butt',
            'lineJoin' => 'miter',
            'dashArray' => [],
            'dashPhase' => 0,
            'lineColor' => '#bdbdbd',
            'fillColor' => '',
        ],
    ];

    $pdf->addArtifactContent(
        $pdf->graph->getLine($leftMargin, $posY, 195.0, $posY, $lineStyle),
        $pid,
        'Layout'
    );
};

// -----------------------------------------------------------------------
// Font setup — insert before addPage()
// -----------------------------------------------------------------------
$pdf->font->insert($pdf->pon, 'dejavusans', '', 10);

// -----------------------------------------------------------------------
// Page 1 — H1, H2, H3, P, Caption, Figure structure elements
// -----------------------------------------------------------------------
$page1 = $pdf->addPage();
$pid1  = $page1['pid'];
$addDecorativeSeparator($pdf, $pid1, 16.0);

// --- H1 heading ---
$setFont($pdf, 'helvetica', 'B', 20);

$pdf->beginStructElem('H1', $pid1);
$pdf->addTextCell(
    'Manual Tag Tree — PDF/UA Structure Elements',
    $pid1,
    $leftMargin,
    20.0,
    0.0,
    0.0,
    drawcell: false,
    valign: 'T',
    halign: 'L',
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
$pdf->addTextCell($intro, $pid1, $leftMargin, 32.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J');
$pdf->endStructElem();

// --- Section 1: H2 + P ---
$setFont($pdf, 'helvetica', 'B', 14);

$pdf->beginStructElem('H2', $pid1);
$pdf->addTextCell('Section 1 — Heading Roles', $pid1, $leftMargin, 55.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'L');
$pdf->endStructElem();

$setFont($pdf, 'helvetica', '', 10);

$sec1body = 'Structure roles H1 through H6 define the heading hierarchy. '
    . 'H1 is the document title; H2 is a top-level section heading; H3 is a '
    . 'sub-section; and so on.  Screen readers present this outline as a '
    . 'navigable table of contents.  Nesting is implied by the sequence of '
    . 'structure elements logged by endStructElem().';

$pdf->beginStructElem('P', $pid1);
$pdf->addTextCell($sec1body, $pid1, $leftMargin, 64.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J');
$pdf->endStructElem();

// --- Sub-section: H3 ---
$setFont($pdf, 'helvetica', 'BI', 11);

$pdf->beginStructElem('H3', $pid1);
$pdf->addTextCell('1.1 — Sub-section with H3 role', $pid1, $leftMargin, 85.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'L');
$pdf->endStructElem();

$setFont($pdf, 'helvetica', '', 10);

$pdf->beginStructElem('P', $pid1);
$pdf->addTextCell(
    'An H3 element is used here to show a third level in the outline. '
    . 'The structure tree will contain: H1 > H2 > H3 > P, reflecting the '
    . 'logical reading order of this page.',
    $pid1,
    $leftMargin,
    93.0,
    0.0,
    0.0,
    drawcell: false,
    valign: 'T',
    halign: 'J',
);
$pdf->endStructElem();

// --- Section 2: H2 + P + Figure + Caption ---
$setFont($pdf, 'helvetica', 'B', 14);

$pdf->beginStructElem('H2', $pid1);
$pdf->addTextCell('Section 2 — Figure and Caption Roles', $pid1, $leftMargin, 113.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'L');
$pdf->endStructElem();

$setFont($pdf, 'helvetica', '', 10);

$pdf->beginStructElem('P', $pid1);
$pdf->addTextCell(
    'The Figure role wraps graphical content. '
    . 'A Caption role immediately following a Figure provides an '
    . 'accessible description of the visual element for screen-reader users.',
    $pid1,
    $leftMargin,
    122.0,
    150,
    0.0,
    drawcell: false,
    valign: 'T',
    halign: 'J',
);
$pdf->endStructElem();

// Draw and tag a simple coloured rectangle as Figure content.
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
$pdf->addTaggedFigureContent(
    $pdf->graph->getStartTransform()
    . $pdf->graph->getRect($leftMargin, 143.0, 180.0, 25.0, 'DF', $figStyle)
    . $pdf->graph->getStopTransform(),
    $pid1,
    'Blue placeholder rectangle representing a figure area'
);

// Caption for the figure.
$pdf->page->addContent($pdf->color->getPdfColor('black'), $pid1);
$setFont($pdf, 'helvetica', 'I', 8);

$pdf->beginStructElem('Caption', $pid1);
$pdf->addTextCell(
    'Figure 1 — Placeholder rectangle representing graphical content (Caption role)',
    $pid1,
    $leftMargin,
    170.0,
    0.0,
    0.0,
    drawcell: false,
    valign: 'T',
    halign: 'C',
);
$pdf->endStructElem();

// -----------------------------------------------------------------------
// Page 2 — Note, Blockquote, and multi-paragraph body
// -----------------------------------------------------------------------
$page2 = $pdf->addPage();
$pid2  = $page2['pid'];
$addDecorativeSeparator($pdf, $pid2, 16.0);

$setFont($pdf, 'helvetica', 'B', 14);

$pdf->beginStructElem('H2', $pid2);
$pdf->addTextCell(
    'Page 2 — Note, Blockquote, and Multi-element Brackets',
    $pid2,
    $leftMargin,
    20.0,
    0.0,
    0.0,
    drawcell: false,
    valign: 'T',
    halign: 'L',
);
$pdf->endStructElem();

$setFont($pdf, 'helvetica', '', 10);

// Note element (with explicit ID for validator compatibility)
$pdf->beginStructElem('Note', $pid2, null, ['ID' => 'note-p2-01']);
$pdf->addTextCell(
    'NOTE: beginStructElem() / endStructElem() are no-ops in plain PDF mode '
    . '(pdfuaMode = "").  The same code runs safely in both plain and PDF/UA '
    . 'modes without conditional branching.',
    $pid2,
    $leftMargin,
    32.0,
    0.0,
    0.0,
    drawcell: false,
    valign: 'T',
    halign: 'J',
);
$pdf->endStructElem();

// Blockquote element
$setFont($pdf, 'helvetica', 'BI', 11);

$pdf->beginStructElem('BlockQuote', $pid2);
$pdf->addTextCell(
    '"Tagged PDF is the foundation of accessible PDF. '
    . 'Without a well-formed structure tree, automated tools and assistive '
    . 'technologies cannot reliably interpret document content."',
    $pid2,
    $leftMargin,
    52.0,
    0.0,
    0.0,
    drawcell: false,
    valign: 'T',
    halign: 'J',
);
$pdf->endStructElem();

$setFont($pdf, 'helvetica', 'I', 8);

$pdf->beginStructElem('Caption', $pid2);
$pdf->addTextCell(
    '— ISO 14289-1:2012 (PDF/UA-1) rationale',
    $pid2,
    $leftMargin,
    72.0,
    0.0,
    0.0,
    drawcell: false,
    valign: 'T',
    halign: 'R',
);
$pdf->endStructElem();

// Multi-paragraph body section
$setFont($pdf, 'helvetica', '', 10);

$paras = [
    'Each call to endStructElem() closes the innermost open bracket.  '
    . 'Completed elements keep their nested parent-child relationships, so '
    . 'the emitted structure tree follows the bracket hierarchy and preserves '
    . 'the reading order of nested content blocks.',

    'A structure bracket that receives no tagged content (i.e. no addTextCell '
    . 'call between beginStructElem and endStructElem) is silently discarded.  '
    . 'This prevents empty structure nodes from appearing in the tree.',

    'Pages 3 and 4 of this document demonstrate the remaining role categories: '
    . 'grouping containers (Sect, Art), list structure (L, LI, Lbl, LBody), '
    . 'and table structure (Table, TR, TH, TD) — each requiring multi-level '
    . 'nested brackets that the library resolves at getOutPDFString() time.',
];

$yPos = 85.0;
foreach ($paras as $para) {
    $pdf->beginStructElem('P', $pid2);
    $pdf->addTextCell($para, $pid2, $leftMargin, $yPos, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J');
    $pdf->endStructElem();
    $yPos += 25.0;
}

// -----------------------------------------------------------------------
// Page 3 — Sect/Art grouping containers + List structure
// -----------------------------------------------------------------------
$page3 = $pdf->addPage();
$pid3  = $page3['pid'];
$addDecorativeSeparator($pdf, $pid3, 16.0);

$setFont($pdf, 'helvetica', 'B', 16);

$pdf->beginStructElem('H2', $pid3);
$pdf->addTextCell(
    'Page 3 — Grouping Containers and List Structure',
    $pid3, $leftMargin, 20.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'L'
);
$pdf->endStructElem();

// ── Sect wrapping a sub-section (H3 + P inside a Sect container) ──────
$setFont($pdf, 'helvetica', '', 10);

$pdf->beginStructElem('Sect', $pid3);

    $setFont($pdf, 'helvetica', 'B', 13);
    $pdf->beginStructElem('H3', $pid3);
    $pdf->addTextCell(
        '3.1 — Sect Container',
        $pid3, $leftMargin, 34.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'L'
    );
    $pdf->endStructElem();

    $setFont($pdf, 'helvetica', '', 10);
    $pdf->beginStructElem('P', $pid3);
    $pdf->addTextCell(
        'The Sect (Section) role is a grouping container: it contributes no '
        . 'content of its own but wraps related blocks — here an H3 and this P '
        . 'paragraph — into a single logical section node in the structure tree. '
        . 'Screen readers and PDF validators see the H3 + P as children of the Sect.',
        $pid3, $leftMargin, 43.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J'
    );
    $pdf->endStructElem();

$pdf->endStructElem(); // Sect

// ── Art container ─────────────────────────────────────────────────────
$pdf->beginStructElem('Art', $pid3);

    $setFont($pdf, 'helvetica', 'B', 13);
    $pdf->beginStructElem('H3', $pid3);
    $pdf->addTextCell(
        '3.2 — Art Container',
        $pid3, $leftMargin, 65.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'L'
    );
    $pdf->endStructElem();

    $setFont($pdf, 'helvetica', '', 10);
    $pdf->beginStructElem('P', $pid3);
    $pdf->addTextCell(
        'The Art (Article) role marks a self-contained composition: its content '
        . 'could stand alone as a discrete piece.  It is distinguished from Sect '
        . 'in that Sect groups thematically related material within a larger work, '
        . 'while Art denotes an independently distributable unit.',
        $pid3, $leftMargin, 74.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J'
    );
    $pdf->endStructElem();

$pdf->endStructElem(); // Art

// ── List: L > LI > {Lbl + LBody} ─────────────────────────────────────
$setFont($pdf, 'helvetica', 'B', 13);
$pdf->beginStructElem('H3', $pid3);
$pdf->addTextCell(
    '3.3 — List Structure (L > LI > Lbl + LBody)',
    $pid3, $leftMargin, 97.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'L'
);
$pdf->endStructElem();

$setFont($pdf, 'helvetica', '', 10);
$pdf->beginStructElem('P', $pid3);
$pdf->addTextCell(
    'Each list item (LI) contains a label (Lbl) and a body (LBody). '
    . 'The outer L bracket wraps all items into one logical list node.',
    $pid3, $leftMargin, 107.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J'
);
$pdf->endStructElem();

$listItems = [
    ['Headings (H1–H6)', 'Define the document outline navigated by screen readers.'],
    ['Body roles (P, Note, Blockquote)', 'Carry prose content at the block level.'],
    ['Grouping roles (Sect, Art, Part)', 'Wrap related blocks without adding visible content.'],
    ['List roles (L, LI, Lbl, LBody)', 'Express enumerated or bulleted list structure.'],
    ['Table roles (Table, TR, TH, TD)', 'Capture row/column relationships in data tables.'],
];

$pdf->beginStructElem('L', $pid3);

$itemY = 122.0;
foreach ($listItems as [$label, $body]) {
    $pdf->beginStructElem('LI', $pid3);

        $setFont($pdf, 'helvetica', 'B', 10);
        $pdf->beginStructElem('Lbl', $pid3);
        $pdf->addTextCell(
            '• ' . $label,
            $pid3, $leftMargin, $itemY, 65.0, 0.0, drawcell: false, valign: 'T', halign: 'L'
        );
        $pdf->endStructElem(); // Lbl

        $setFont($pdf, 'helvetica', '', 10);
        $pdf->beginStructElem('LBody', $pid3);
        $pdf->addTextCell(
            $body,
            $pid3, $leftMargin + 65.0, $itemY, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J'
        );
        $pdf->endStructElem(); // LBody

    $pdf->endStructElem(); // LI
    $itemY += 8.0;
}

$pdf->endStructElem(); // L

// -----------------------------------------------------------------------
// Page 4 — Table nesting + Figure with /Alt alternate description
// -----------------------------------------------------------------------
$page4 = $pdf->addPage();
$pid4  = $page4['pid'];
$addDecorativeSeparator($pdf, $pid4, 16.0);

$setFont($pdf, 'helvetica', 'B', 16);
$pdf->beginStructElem('H2', $pid4);
$pdf->addTextCell(
    'Page 4 — Table Structure and Figure with Alt-Text',
    $pid4, $leftMargin, 20.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'L'
);
$pdf->endStructElem();

// ── Table ─────────────────────────────────────────────────────────────
$setFont($pdf, 'helvetica', '', 10);
$pdf->beginStructElem('P', $pid4);
$pdf->addTextCell(
    'The Table role wraps a complete data table.  Each row is a TR; header '
    . 'cells are TH; data cells are TD.  All brackets must be closed '
    . 'inside-out before moving to the next row.',
    $pid4, $leftMargin, 32.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J'
);
$pdf->endStructElem();

// Column geometry (three columns)
$colX = [$leftMargin, $leftMargin + 55.0, $leftMargin + 120.0];
$colW = [55.0, 65.0, 60.0];
$rowH = 8.0;
$tableY = 52.0;

$tableHeaders = ['Structure Role', 'Category', 'Nesting context'];
$tableHeaderIds = ['th-role', 'th-category', 'th-context'];
$tableRows = [
    ['H1–H6',           'Heading',   'Document / Sect / Art'],
    ['P',               'Body',      'Document / Sect / Art'],
    ['Note, Blockquote','Body',      'Document / Sect / Art'],
    ['L',               'List',      'Document / Sect'],
    ['LI',              'List item', 'Inside L'],
    ['Lbl / LBody',     'List parts','Inside LI'],
    ['Table',           'Table',     'Document / Sect'],
    ['TR',              'Table row', 'Inside Table'],
    ['TH / TD',         'Table cell','Inside TR'],
    ['Sect / Art / Part','Container', 'Document-level'],
    ['Figure',          'Graphic',   'Document / Sect'],
    ['Caption',         'Caption',   'After Figure'],
];

$pdf->beginStructElem('Table', $pid4);

    // Header row
    $pdf->beginStructElem('TR', $pid4);
    foreach ($tableHeaders as $idx => $hdr) {
        $setFont($pdf, 'helvetica', 'B', 9);
        $pdf->beginStructElem('TH', $pid4, null, [
            'O' => 'Table',
            'ID' => $tableHeaderIds[$idx],
            'Scope' => 'Column',
        ]);
        $pdf->addTextCell(
            $hdr, $pid4, $colX[$idx], $tableY, $colW[$idx], 0.0,
            drawcell: false, valign: 'T', halign: 'L'
        );
        $pdf->endStructElem(); // TH
    }
    $pdf->endStructElem(); // TR (header)

    // Data rows
    foreach ($tableRows as $row) {
        $tableY += $rowH;
        $pdf->beginStructElem('TR', $pid4);
        foreach ($row as $idx => $cell) {
            $setFont($pdf, 'helvetica', '', 9);
            $pdf->beginStructElem('TD', $pid4, null, [
                'O' => 'Table',
                'Headers' => $tableHeaderIds[$idx],
            ]);
            $pdf->addTextCell(
                $cell, $pid4, $colX[$idx], $tableY, $colW[$idx], 0.0,
                drawcell: false, valign: 'T', halign: 'L'
            );
            $pdf->endStructElem(); // TD
        }
        $pdf->endStructElem(); // TR
    }

$pdf->endStructElem(); // Table

// ── Figure with /Alt alternate description ────────────────────────────
$figY = $tableY + 18.0;

$setFont($pdf, 'helvetica', 'B', 13);
$pdf->beginStructElem('H3', $pid4);
$pdf->addTextCell(
    '4.1 — Figure with /Alt Entry',
    $pid4, $leftMargin, $figY, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'L'
);
$pdf->endStructElem();

$figY += 10.0;

$setFont($pdf, 'helvetica', '', 10);
$pdf->beginStructElem('P', $pid4);
$pdf->addTextCell(
    'For non-text graphics, use addTaggedFigureContent() and pass an /Alt '
    . 'description for accessibility.  The string is written into the Figure '
    . 'structure-element dictionary and read aloud by screen readers when '
    . 'the graphical content cannot be represented as plain text.',
    $pid4, $leftMargin, $figY, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'J'
);
$pdf->endStructElem();

$figY += 22.0;

// Draw a simple placeholder rectangle for the figure.
$figStyle = [
    'all' => [
        'lineWidth'  => 0.5,
        'lineCap'    => 'butt',
        'lineJoin'   => 'miter',
        'dashArray'  => [],
        'dashPhase'  => 0,
        'lineColor'  => '#4a7c59',
        'fillColor'  => '#d6ead9',
    ],
];
$pdf->addTaggedFigureContent(
    $pdf->graph->getStartTransform()
    . $pdf->graph->getRect($leftMargin, $figY, 180.0, 22.0, 'DF', $figStyle)
    . $pdf->graph->getStopTransform(),
    $pid4,
    'Horizontal bar chart illustrating the relative nesting depth of each PDF structure role category'
);

$pdf->page->addContent($pdf->color->getPdfColor('black'), $pid4);
$setFont($pdf, 'helvetica', 'I', 8);
$pdf->beginStructElem('Caption', $pid4);
$pdf->addTextCell(
    'Figure 2 — Placeholder for a bar chart; /Alt text is written to the Figure struct-element dictionary',
    $pid4, $leftMargin, $figY + 24.0, 0.0, 0.0, drawcell: false, valign: 'T', halign: 'C'
);
$pdf->endStructElem();

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
