<?php
/**
 * E015_pdfua.php
 *
 * Demonstrates HTML auto-tagging in 'pdfua' (generic alias) mode.
 *
 * Every HTML element rendered by addHTMLCell() is automatically mapped to the
 * corresponding PDF structure role — no beginStructElem() calls required.
 * Compare with E063 which builds the same structure tree manually via the
 * lower-level addTextCell() / beginStructElem() API.
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

$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, 'pdfua');
$pdf->setTitle('PDF/UA Auto-Tagged HTML');
$pdf->setSubject('tc-lib-pdf example: 015 — pdfua mode HTML tagging showcase');
$pdf->setKeywords('TCPDF tc-lib-pdf pdfua tagged PDF accessibility HTML auto-tag structure');
$pdf->setPDFFilename('E015_pdfua.pdf');
$pdf->setLanguage('en-US');

$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);
$pdf->addPage();
$pdf->page->addContent($font['out']);

$imgPath = __DIR__ . '/images/tcpdf_logo.jpg';

$html = '<h1>PDF/UA — Auto-Tagged HTML Showcase</h1>'
    . '<p>Mode: <strong>pdfua</strong> (generic alias). This document demonstrates every HTML'
    . ' element that <code>addHTMLCell()</code> auto-tags with a PDF structure role.'
    . ' No <code>beginStructElem()</code> calls are required: the library maps each HTML tag'
    . ' to its PDF role automatically when a PDF/UA mode is active. The generic'
    . ' <em>pdfua</em> alias behaves identically to <em>pdfua1</em> for structure-tree'
    . ' output and defaults to PDF 1.7.</p>'

    . '<h2>1 — Heading and Paragraph Roles</h2>'
    . '<h3>H3 Sub-heading</h3>'
    . '<p>The <code>h1</code>-<code>h6</code> tags map to PDF roles <strong>H1-H6</strong>.'
    . ' The <code>p</code> tag maps to <strong>P</strong>.'
    . ' Heading levels are clamped to prevent skipped levels in the structure tree,'
    . ' satisfying the PDF/UA requirement that no heading level may be skipped.</p>'

    . '<h2>2 — Block Roles</h2>'
    . '<blockquote>&ldquo;Tagged PDF is the foundation of accessible PDF. Without a'
    . ' well-formed structure tree, automated tools and assistive technologies cannot'
    . ' reliably interpret document content.&rdquo; &mdash; ISO&#160;14289-1 rationale</blockquote>'
    . '<p>The <code>blockquote</code> tag maps to <strong>BlockQuote</strong>.'
    . ' The <code>pre</code> tag maps to <strong>Code</strong>:</p>'
    . '<pre>// pre / code block example&#10;function greet(string $name): string {&#10;    return "Hello, " . $name . "!";&#10;}</pre>'

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
    . '<figcaption>Figure 1 &mdash; TCPDF logo. The <code>alt</code> attribute is written as'
    . ' <strong>/Alt</strong> in the Figure structure-element dictionary so screen readers'
    . ' can describe the image. The <code>figcaption</code> tag maps to'
    . ' <strong>Caption</strong>.</figcaption>'
    . '</figure>'

    . '<h2>5 — Table Structure (Table &gt; TR &gt; TH/TD)</h2>'
    . '<table border="1" cellspacing="0" cellpadding="3">'
    . '<caption>Table 1 &mdash; HTML tags and their PDF/UA structure roles (auto-tagged by addHTMLCell)</caption>'
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
    . '<tr><td>th</td><td>TH</td><td>Table</td></tr>'
    . '<tr><td>td</td><td>TD</td><td>Table</td></tr>'
    . '<tr><td>a</td><td>Link</td><td>Inline</td></tr>'
    . '</table>'

    . '<h2>6 — Hyperlink (Link Role)</h2>'
    . '<p>This sentence contains a'
    . ' <a href="https://github.com/tecnickcom/tc-lib-pdf">hyperlink to the tc-lib-pdf repository</a>;'
    . ' the <code>a</code> element is tagged with the <strong>Link</strong> structure role'
    . ' automatically. The link text is accessible to screen readers via the structure tree.</p>';

$pdf->addHTMLCell($html, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
