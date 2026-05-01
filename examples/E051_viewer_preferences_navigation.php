<?php
/**
 * E051_viewer_preferences_navigation.php
 *
 * Demonstrates viewer preference configuration, display mode selection,
 * bookmarks, named destinations, and internal link annotations.
 *
 * What this demonstrates:
 * - setDisplayMode(): initial zoom, page layout, and page mode.
 * - setViewerPreferences(): HideToolbar, CenterWindow, Duplex, PrintScaling, etc.
 * - setNamedDestination(): register named jump targets for cross-document linking.
 * - addInternalLink(): page/position link ID for use inside the same document.
 * - setLink() + page->addAnnotRef(): clickable rectangle annotations.
 * - setBookmark(): outline entries that drive the bookmark panel.
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

require(__DIR__ . '/../vendor/autoload.php');

define('K_PATH_FONTS', (string) realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 051');
$pdf->setTitle('Viewer Preferences & Navigation');
$pdf->setKeywords('TCPDF tc-lib-pdf viewer preferences navigation bookmarks named destinations links');
$pdf->setPDFFilename('051_viewer_preferences_navigation.pdf');

// ----------
// Viewer preferences
// The viewer will open the document in single-page layout with outline panel
// visible. The window is centered on screen and the document title shown in
// the title bar.

$pdf->setViewerPreferences([
    'DisplayDocTitle'       => true,
    'CenterWindow'          => true,
    'FitWindow'             => false,
    'HideToolbar'           => false,
    'HideMenubar'           => false,
    'Duplex'                => 'DuplexFlipLongEdge',
    'PrintScaling'          => 'none',
    'NumCopies'             => 1,
    'PrintPageRange'        => [1, 4],
    'NonFullScreenPageMode' => 'UseOutlines',
]);

// Display mode: open at full width, single-page layout, with outlines panel.
$pdf->setDisplayMode('fullwidth', 'SinglePage', 'UseOutlines');

$pdf->enableDefaultPageContent();

// ----------

$headFont = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 16);
$subFont  = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 11);
$bodyFont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

// ===| Page 1 – Cover |======================================================

$page1 = $pdf->addPage(['format' => 'A4']);

// Register a named destination at the top of page 1. External documents or
// bookmark links can jump here via '#cover'.
$pdf->setNamedDestination('cover', $page1['pid'], 0, 0);

$pdf->setBookmark('Cover', '#cover', 0, $page1['pid'], 0, 0, 'B', 'darkblue');

$coverHtml = '<h1 style="font-family: helvetica; color: #1a3c6e; text-align: center; margin-top: 30mm;">
    Viewer Preferences &amp; Navigation Demo
</h1>
<p style="font-family: helvetica; font-size: 11pt; text-align: center; color: #444444;">
    tc-lib-pdf · Example 051
</p>
<hr/>
<p style="font-family: helvetica; font-size: 10pt;">
    This document demonstrates:
</p>
<ul style="font-family: helvetica; font-size: 10pt;">
    <li><strong>Viewer preferences</strong> — initial layout, duplex, print scaling, and window behavior.</li>
    <li><strong>Display mode</strong> — zoom, page layout, and panel visibility on open.</li>
    <li><strong>Named destinations</strong> — stable jump targets for cross-document links.</li>
    <li><strong>Internal link annotations</strong> — clickable regions mapped to page positions.</li>
    <li><strong>Bookmarks / outlines</strong> — hierarchical navigation panel entries.</li>
</ul>
<p style="font-family: helvetica; font-size: 10pt; margin-top: 8mm;">
    The navigation buttons on page 4 use <code>setLink</code> and <code>addInternalLink</code>
    to create clickable rectangles that jump to each chapter page.
</p>';
$pdf->addHTMLCell($coverHtml, 15, 20, 170);

// ===| Page 2 – Chapter 1 |==================================================

$page2 = $pdf->addPage(['format' => 'A4']);

$destChapter1 = $pdf->setNamedDestination('chapter-1', $page2['pid'], 0, 0);
$pdf->setBookmark('Chapter 1 — Overview', $destChapter1, 0, $page2['pid'], 0, 0, 'B', 'darkgreen');
$pdf->setBookmark('1.1 Introduction', '', 1, $page2['pid'], 0, 25);

$ch1Html = '<h2 style="font-family: helvetica; color: #1a6e3c;">Chapter 1 — Overview</h2>
<p style="font-family: helvetica; font-size: 10pt;">
    This chapter is the target of the named destination <code>chapter-1</code>.
    Clicking the button on the navigation page jumps here via an internal link annotation.
</p>
<h3 style="font-family: helvetica; color: #1a6e3c;">1.1 Introduction</h3>
<p style="font-family: helvetica; font-size: 10pt;">
    The <code>setNamedDestination()</code> method registers a jump target that can be referenced
    from bookmarks (using the <code>#</code> prefix), from link annotations, or from external
    documents pointing to this file.
</p>
<p style="font-family: helvetica; font-size: 10pt;">
    Named destinations are stored in the PDF <em>/Dests</em> dictionary and are independent
    of page numbering — they remain stable even if pages are reordered.
</p>';
$pdf->addHTMLCell($ch1Html, 15, 20, 170);

// ===| Page 3 – Chapter 2 |==================================================

$page3 = $pdf->addPage(['format' => 'A4']);

$destChapter2 = $pdf->setNamedDestination('chapter-2', $page3['pid'], 0, 0);
$pdf->setBookmark('Chapter 2 — Viewer Preferences', $destChapter2, 0, $page3['pid'], 0, 0, 'B', 'darkred');
$pdf->setBookmark('2.1 Preference Keys', '', 1, $page3['pid'], 0, 25);

$prefTable = '<h2 style="font-family: helvetica; color: #6e1a1a;">Chapter 2 — Viewer Preferences</h2>
<p style="font-family: helvetica; font-size: 10pt;">
    The <code>setViewerPreferences()</code> method populates the <em>/ViewerPreferences</em>
    dictionary in the PDF catalog. The following preferences are active in this document:
</p>
<table border="1" cellpadding="2" cellspacing="0" style="font-family: helvetica; font-size: 9pt; width: 100%;">
    <tr style="background-color: #dddddd;">
        <th>Key</th><th>Value</th><th>Effect</th>
    </tr>
    <tr><td>DisplayDocTitle</td><td>true</td><td>Show PDF title in window title bar.</td></tr>
    <tr><td>CenterWindow</td><td>true</td><td>Center the viewer window on screen.</td></tr>
    <tr><td>Duplex</td><td>DuplexFlipLongEdge</td><td>Default duplex setting for print dialog.</td></tr>
    <tr><td>PrintScaling</td><td>none</td><td>Disable auto-scaling in print dialog.</td></tr>
    <tr><td>NumCopies</td><td>1</td><td>Default copy count in print dialog.</td></tr>
    <tr><td>PrintPageRange</td><td>1–4</td><td>Default page range in print dialog.</td></tr>
    <tr><td>NonFullScreenPageMode</td><td>UseOutlines</td><td>Show outline panel when exiting full screen.</td></tr>
</table>';
$pdf->addHTMLCell($prefTable, 15, 20, 170);

// ===| Page 4 – Navigation Map |=============================================

$page4 = $pdf->addPage(['format' => 'A4']);

$pdf->setBookmark('Navigation Map', '', 0, $page4['pid'], 0, 0, '', 'gray');

$navHtml = '<h2 style="font-family: helvetica; color: #333333;">Navigation Map</h2>
<p style="font-family: helvetica; font-size: 10pt;">
    Each button below is a clickable link annotation (<code>setLink</code> + <code>addAnnotRef</code>)
    that uses <code>addInternalLink()</code> to jump to the start of a specific page.
</p>
<p style="font-family: helvetica; font-size: 10pt; margin-bottom: 8mm;">
    Alternatively, the bookmark panel (outline) on the left provides the same navigation
    via the bookmarks registered with <code>setBookmark()</code>.
</p>';
$pdf->addHTMLCell($navHtml, 15, 20, 170);

// Define style for button boxes
$btnFill  = ['all' => ['lineWidth' => 0.3, 'lineColor' => '#333366', 'fillColor' => '#e8eef8']];
$btnFont  = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 11);
$pdf->page->addContent($btnFont['out']);

// Helper to draw a navigation button and attach a link annotation
$drawNavButton = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    float $bx,
    float $by,
    float $bw,
    float $bh,
    string $label,
    int $targetPage,
    array $btnFill
) use ($page4): void {
    $pdf->page->addContent($pdf->graph->getRect($bx, $by, $bw, $bh, 'DF', $btnFill));
    // Reset fill color to dark text after the rect draw, which left the fill color
    // set to the button background color, making the label text invisible.
    $pdf->page->addContent($pdf->color->getPdfColor('#333366'));
    $pdf->page->addContent($pdf->getTextCell($label, $bx, $by + 3, $bw, $bh - 3, 0, 1, 'T', 'C'));
    $lnkid = $pdf->addInternalLink($targetPage, 0);
    $annid = $pdf->setLink($bx, $by, $bw, $bh, $lnkid);
    $pdf->page->addAnnotRef($annid);
};

$drawNavButton($pdf, 25, 80, 70, 14, 'Go to Cover (page 1)',      $page1['pid'], $btnFill);
$drawNavButton($pdf, 25, 100, 70, 14, 'Go to Chapter 1 (page 2)', $page2['pid'], $btnFill);
$drawNavButton($pdf, 25, 120, 70, 14, 'Go to Chapter 2 (page 3)', $page3['pid'], $btnFill);

// Restore the plain body font in the internal font stack before addHTMLCell;
// font->insert with 'B' above left the stack on the bold variant and would
// cause addHTMLCell to capture/restore 'helveticabB' → undefined key warning.
$pdf->font->insert($pdf->pon, 'helvetica', '', 10);

// Show named destination links using '#name' notation in bookmarks
$namedNoteHtml = '<p style="font-family: helvetica; font-size: 9pt; color: #555555; margin-top: 10mm;">
    The bookmark entries above the buttons use <code>setNamedDestination()</code> targets
    (<code>#cover</code>, <code>#chapter-1</code>, <code>#chapter-2</code>).
    Named destinations survive page renumbering and can be referenced from external documents.
</p>';
$pdf->addHTMLCell($namedNoteHtml, 15, 145, 170);

// ----------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
