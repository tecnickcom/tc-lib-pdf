<?php
/**
 * E052_custom_fonts_fallback.php
 *
 * Demonstrates custom font selection, Unicode coverage strategy, and
 * font-family fallback across multiple scripts.
 *
 * What this demonstrates:
 * - font->insert(): loading built-in and extended font families.
 * - Subset vs full-embed tradeoff (constructor $subsetfont flag).
 * - Choosing fonts by Unicode coverage: helvetica (Latin), dejavusans
 *   (Latin + Greek + Cyrillic + IPA), unifont (full Unicode BMP).
 * - HTML font-family CSS mapping to pre-loaded font slots.
 * - Per-script font selection in addHTMLCell markup.
 *
 * Companion libraries: tc-lib-pdf-font, tc-lib-unicode, tc-lib-unicode-data.
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

// K_PATH_FONTS must point to the directory holding .z and .php font metric files.
define('K_PATH_FONTS', (string) realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// Subset font embedding: when $subsetfont = true (constructor arg 3), only the
// glyphs actually used in the document are embedded. This produces smaller PDFs
// but makes the font unsuitable for later editing. Set to false for archival PDFs
// (PDF/A, PDF/UA) or whenever the full character set must be available.
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm',  // unit
    true,  // isunicode – required for any non-Latin script
    false, // subsetfont – false = full embed; change to true to see size tradeoff
    true,  // compress
    '',    // mode
    null,  // objEncrypt
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 052');
$pdf->setTitle('Custom Fonts & Unicode Fallback');
$pdf->setKeywords('TCPDF tc-lib-pdf font unicode multilingual fallback subset dejavusans unifont');
$pdf->setPDFFilename('052_custom_fonts_fallback.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);
$pdf->enableDefaultPageContent();

// ----------
// Pre-load all font families that will be used in the document.
// font->insert() returns a font descriptor array. The 'out' key contains
// the PDF operator stream that must be injected onto a page before the
// font is used. Loading fonts before addPage() registers them in the
// font object pool; injecting 'out' at the start of each page activates
// the font for that page.

// Helvetica: core Type-1, no embedding required, Latin + standard symbols only.
$fHelvetica = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);
$fHelveticaB = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 11);

// Times New Roman (core): serif alternative for body text, Latin only.
$fTimes     = $pdf->font->insert($pdf->pon, 'times', '', 11);

// Courier (core): monospaced, Latin only; good for code samples.
$fCourier   = $pdf->font->insert($pdf->pon, 'courier', '', 10);

// DejaVu Sans: TrueType, Latin + Greek + Cyrillic + IPA + many other scripts.
// Covers most Western European, Eastern European, and Slavic languages.
$fDejavu    = $pdf->font->insert($pdf->pon, 'dejavusans', '', 11);

// Unifont: TrueType, very broad Unicode BMP (plane 0) coverage including Arabic,
// Hebrew, Thai, Devanagari, and many additional scripts.
// File size is large; use subset=true when embedding it in production PDFs.
$fUnifont   = $pdf->font->insert($pdf->pon, 'unifont', '', 11);

// ===| Page 1 – Font Coverage Overview |=====================================

$pdf->addPage(['format' => 'A4']);
$pdf->page->addContent($fHelvetica['out']);

$overviewHtml = '<h1 style="font-family: helvetica;">Font Selection &amp; Unicode Fallback</h1>
<p style="font-family: helvetica; font-size: 10pt;">
    PDF viewers cannot substitute fonts automatically — every glyph must come from
    an embedded font resource. The strategy is:
</p>
<ol style="font-family: helvetica; font-size: 10pt;">
    <li>Use <strong>core Type-1 fonts</strong> (helvetica, times, courier) for Latin-only text to
        avoid embedding overhead.</li>
    <li>Upgrade to <strong>DejaVu Sans</strong> for pages that mix Latin with Greek, Cyrillic, or
        extended Latin (diacritics, IPA).</li>
    <li>Use <strong>Unifont</strong> for pages that require full Unicode BMP coverage (Arabic, Hebrew,
        Thai, Devanagari, Hangul, CJK).</li>
</ol>
<p style="font-family: helvetica; font-size: 10pt;">
    In HTML cells, specify the font via <code>style="font-family: dejavusans"</code> or
    <code>style="font-family: unifont"</code>.
    The name must match the lowercase font identifier known to tc-lib-pdf-font.
</p>
<hr/>
<h2 style="font-family: helvetica;">Core Type-1 Fonts (Latin)</h2>
<p style="font-family: helvetica; font-size: 11pt;">
    <span style="font-family: helvetica;">Helvetica — The quick brown fox jumps over the lazy dog.</span>
</p>
<p style="font-family: times; font-size: 11pt;">
    <span style="font-family: times;">Times — The quick brown fox jumps over the lazy dog.</span>
</p>
<p style="font-family: courier; font-size: 10pt;">
    <span style="font-family: courier;">Courier — The quick brown fox jumps over the lazy dog.</span>
</p>';
$pdf->addHTMLCell($overviewHtml, 15, 20, 175);

// ===| Page 2 – DejaVu Sans: Latin + Greek + Cyrillic |======================

$pdf->addPage(['format' => 'A4']);
$pdf->page->addContent($fDejavu['out']);

$dejavuHtml = '<h2 style="font-family: dejavusans;">DejaVu Sans — Extended Coverage</h2>
<p style="font-family: dejavusans; font-size: 10pt;">
    DejaVu Sans covers Latin, Greek, Cyrillic, Armenian, Georgian, Hebrew,
    Arabic (basic), and many more. Use it when helvetica glyphs are missing.
</p>
<h3 style="font-family: dejavusans;">Latin Extended</h3>
<p style="font-family: dejavusans; font-size: 11pt;">
    Héllo Wörld — café, naïve, résumé, Ångström, façade, Ñoño.
</p>
<h3 style="font-family: dejavusans;">Greek (Ελληνικά)</h3>
<p style="font-family: dejavusans; font-size: 11pt;">
    Ξεσκεπάζω την ψυχοφθόρα βδελυγμία.
</p>
<h3 style="font-family: dejavusans;">Cyrillic (Русский)</h3>
<p style="font-family: dejavusans; font-size: 11pt;">
    Съешь же ещё этих мягких французских булок, да выпей чаю.
</p>
<h3 style="font-family: dejavusans;">IPA Phonetics</h3>
<p style="font-family: dejavusans; font-size: 11pt;">
    /ˌɪntəˈnæʃənl/ · /fəˈnɛtɪk/ · /ˈælfəbɪt/
</p>
<hr/>
<p style="font-family: dejavusans; font-size: 9pt; color: #555555;">
    Subset tip: dejavusans is ~750 KB uncompressed. With <code>$subsetfont = true</code> and
    only the glyphs used in this page, the embedded subset would be much smaller.
    For documents mixing many scripts across many pages, subset embedding is recommended.
</p>';
$pdf->addHTMLCell($dejavuHtml, 15, 20, 175);

// ===| Page 3 – Unifont: Full BMP |==========================================

$pdf->addPage(['format' => 'A4']);
$pdf->page->addContent($fUnifont['out']);

$unifontHtml = '<h2 style="font-family: unifont; font-size: 11pt; font-weight: normal;">Unifont — Full Unicode BMP Coverage</h2>
<p style="font-family: unifont; font-size: 11pt;">
    Unifont offers very broad Unicode Plane 0 (BMP) coverage.
    Use it as a last-resort fallback for scripts not covered by DejaVu.
    Production PDFs should always use <code>$subsetfont = true</code> with Unifont.
</p>
<h3 style="font-family: unifont; font-size: 11pt; font-weight: normal;">Arabic (العربية) — RTL</h3>
<p style="font-family: unifont; font-size: 11pt; text-align: right;">
    صِف خَلقَ خَودِكِ كَمِثلِ الشَّمسِ إِذ بَزَغَت
</p>
<h3 style="font-family: unifont; font-size: 11pt; font-weight: normal;">Hebrew (עברית) — RTL</h3>
<p style="font-family: unifont; font-size: 11pt; text-align: right;">
    דג סקרן שט בים מאוכזב ולפתע מצא חברה
</p>
<h3 style="font-family: unifont; font-size: 11pt; font-weight: normal;">Devanagari (हिन्दी)</h3>
<p style="font-family: unifont; font-size: 11pt;">
    ऋषि सुयश ने प्रख्यात ग्रंथ की रचना करके हमें ज्ञान दिया।
</p>
<hr/>
<p style="font-family: unifont; font-size: 11pt; color: #555555;">
    Fallback strategy: attempt to render with the most specific font first.
    Only escalate to Unifont for code points absent from narrower families.
    This minimises file size while ensuring every glyph is available.
</p>';
$pdf->addHTMLCell($unifontHtml, 15, 20, 175);

// ----------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
