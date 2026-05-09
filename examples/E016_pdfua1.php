<?php
/**
 * E016_pdfua1.php
 *
 * Demonstrates HTML auto-tagging in 'pdfua1' (PDF/UA-1, ISO 14289-1) mode.
 *
 * PDF/UA-1 is the original accessible-PDF profile, standardised as ISO 14289-1:2012
 * and built on PDF 1.7.  When this mode is active, tc-lib-pdf:
 *   - Writes StructTreeRoot, ParentTree, and MarkInfo /Marked true
 *   - Forces ViewerPreferences /DisplayDocTitle true
 *   - Sets /Lang from the document language (default en-US)
 *   - Clamps heading levels to prevent skipped H1-H6 levels
 *   - Emits ActualText for ligatures and special glyphs
 *
 * Every HTML element rendered by addHTMLCell() is automatically mapped to the
 * corresponding PDF structure role — no beginStructElem() calls required.
 * Compare with E063 which builds the same structure tree manually.
 *
 * Auto-tagged roles exercised here:
 *   h1-h3       → H1-H3     (heading hierarchy)
 *   p           → P         (paragraph)
 *   blockquote  → BlockQuote
 *   pre         → Code
 *   ul / ol     → L         (list; li → LI > Lbl + LBody auto-generated)
 *   figure      → Figure
 *   img[alt]    → Figure + /Alt (alt text written to struct-elem dictionary)
 *   figcaption  → Caption
 *   table       → Table     (tr → TR, th → TH, td → TD, caption → Caption)
 *   a           → Link
 */

require(__DIR__ . '/../vendor/autoload.php');

\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfua1');
$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setTitle('PDF/UA-1 Auto-Tagged HTML');
$pdf->setSubject('tc-lib-pdf example: 016 — pdfua1 mode HTML tagging showcase');
$pdf->setKeywords('TCPDF tc-lib-pdf pdfua1 PDF/UA-1 ISO14289 tagged PDF accessibility HTML auto-tag');
$pdf->setPDFFilename('E016_pdfua1.pdf');
$pdf->setLanguage('en-US');

$font = $pdf->font->insert($pdf->pon, 'dejavusans', '', 10);
$pdf->addPage();
$pdf->page->addContent($font['out']);

$imgPath = __DIR__ . '/images/tcpdf_logo.jpg';

$html = '<h1>PDF/UA-1 — Auto-Tagged HTML Showcase</h1>'
    . '<p>Mode: <strong>pdfua1</strong> (ISO&#160;14289-1, PDF&#160;1.7). This document'
    . ' demonstrates every HTML element that <code>addHTMLCell()</code> auto-tags with a'
    . ' PDF/UA-1 structure role. PDF/UA-1 is the original accessible-PDF standard: it mandates'
    . ' a tagged structure tree, document language, <code>MarkInfo /Marked true</code>,'
    . ' and <code>ViewerPreferences /DisplayDocTitle true</code>. All of these are written'
    . ' automatically when the <em>pdfua1</em> mode is active.</p>'

    . '<h2>1 — Heading and Paragraph Roles</h2>'
    . '<h3>H3 Sub-heading</h3>'
    . '<p>The <code>h1</code>-<code>h6</code> tags map to PDF roles <strong>H1-H6</strong>.'
    . ' The <code>p</code> tag maps to <strong>P</strong>.'
    . ' PDF/UA-1 requires that heading levels are not skipped: the library clamps each'
    . ' heading to at most one level deeper than the previous heading to maintain a'
    . ' well-formed outline that screen readers can navigate.</p>'

    . '<h2>2 — Block Roles</h2>'
    . '<blockquote>&ldquo;Tagged PDF is the foundation of accessible PDF. Without a'
    . ' well-formed structure tree, automated tools and assistive technologies cannot'
    . ' reliably interpret document content.&rdquo; &mdash; ISO&#160;14289-1 rationale</blockquote>'
    . '<p>The <code>blockquote</code> tag maps to <strong>BlockQuote</strong>.</p>'

    . '<h2>3 — List Structure (L &gt; LI &gt; Lbl + LBody)</h2>'
    . '<p>Both <code>ul</code> and <code>ol</code> map to the <strong>L</strong> role.'
    . ' Each <code>li</code> auto-generates <strong>LI &gt; {Lbl, LBody}</strong> children:</p>'
    . '<ul>'
    . '<li>Unordered item &mdash; <code>ul</code>/<code>li</code> tags produce L/LI structure</li>'
    . '<li>The bullet character is placed in a <strong>Lbl</strong> child element</li>'
    . '<li>Body text is wrapped in a <strong>LBody</strong> child element</li>'
    . '</ul>'
    . '<ol>'
    . '<li>Ordered item &mdash; the number is auto-generated as the <strong>Lbl</strong></li>'
    . '<li>Body text is again auto-wrapped in <strong>LBody</strong></li>'
    . '<li>Nesting: L &gt; LI &gt; {Lbl, LBody}</li>'
    . '</ol>'

    . '<h2>4 — Figure, Image Alt-Text, and Caption</h2>'
    . '<figure>'
    . '<img src="' . $imgPath . '" alt="TCPDF logo: blue text on white background" width="89" height="30" />'
    . '<figcaption>Figure 1 &mdash; TCPDF logo. The <code>alt</code> attribute becomes'
    . ' <strong>/Alt</strong> in the Figure structure-element dictionary.'
    . ' PDF/UA-1 requires every Figure to carry an /Alt or /ActualText entry so'
    . ' screen readers can describe non-text content. The <code>figcaption</code> tag'
    . ' maps to <strong>Caption</strong>.</figcaption>'
    . '</figure>'

    . '<h2>5 — Table Structure (Table &gt; TR &gt; TH/TD)</h2>'
    . '<table border="1" cellspacing="0" cellpadding="3">'
    . '<caption>Table 1 &mdash; HTML tags and their PDF/UA-1 structure roles</caption>'
    . '<tr><th>HTML tag</th><th>PDF structure role</th><th>Category</th></tr>'
    . '<tr><td>h1&ndash;h6</td><td>H1&ndash;H6</td><td>Heading</td></tr>'
    . '<tr><td>p</td><td>P</td><td>Body</td></tr>'
    . '<tr><td>blockquote</td><td>BlockQuote</td><td>Body</td></tr>'
    . '<tr><td>pre</td><td>Code</td><td>Body</td></tr>'
    . '<tr><td>ul, ol</td><td>L</td><td>List</td></tr>'
    . '<tr><td>li</td><td>LI + Lbl + LBody</td><td>List</td></tr>'
    . '<tr><td>figure</td><td>Figure</td><td>Illustration</td></tr>'
    . '<tr><td>img (with alt)</td><td>Figure + /Alt</td><td>Illustration</td></tr>'
    . '<tr><td>figcaption, caption</td><td>Caption</td><td>Illustration</td></tr>'
    . '<tr><td>table</td><td>Table</td><td>Table</td></tr>'
    . '<tr><td>tr</td><td>TR</td><td>Table</td></tr>'
    . '<tr><td>td</td><td>TD</td><td>Table</td></tr>'
    . '<tr><td>th</td><td>TH</td><td>Table</td></tr>'
    . '<tr><td>a</td><td>Link</td><td>Inline</td></tr>'
    . '</table>'

    . '<h2>6 — Hyperlink (Link Role)</h2>'
    . '<p>This sentence contains a'
    . ' <a href="https://github.com/tecnickcom/tc-lib-pdf" style="text-decoration:none;">hyperlink to the tc-lib-pdf repository</a>;'
    . ' the <code>a</code> element is tagged with the <strong>Link</strong> structure role'
    . ' automatically. PDF/UA-1 requires that link annotations have an accessible name,'
    . ' which the library derives from the link text in the structure tree.</p>';

$pdf->addHTMLCell($html, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
