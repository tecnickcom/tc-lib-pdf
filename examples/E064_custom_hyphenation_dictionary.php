<?php
/**
 * E064_custom_hyphenation_dictionary.php
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
 * Demonstrate external TeX hyphenation pattern loading via
 * loadTexHyphenPatterns() + setTexHyphenPatterns().
 *
 * Background
 * ──────────
 * tc-lib-pdf inherits hyphenation support from the tc-lib-unicode library.
 * By default NO hyphenation patterns are loaded; hyphenation only occurs
 * when explicit soft-hyphens (&shy;) are present in the source text
 * (see E028_text_hyphenation.php for that approach).
 *
 * When you call setTexHyphenPatterns() with a loaded pattern array, the
 * library automatically inserts soft-hyphen break points based on the
 * pattern rules while laying out each text run.  This is especially useful
 * for narrow columns, justified text, or languages with long compound words
 * (German, Dutch, Finnish, etc.) where manual soft-hyphen insertion would
 * be impractical.
 *
 * API
 * ───
 *   loadTexHyphenPatterns(string $file): array<string,string>
 *       Parses a TeX .tex pattern file (the standard format used by CTAN
 *       packages such as hyph-utf8) and returns a pattern array.
 *       The file must contain a \patterns{ ... } block.
 *
 *   setTexHyphenPatterns(array $patterns): void
 *       Installs the pattern array as the active hyphenation dictionary.
 *       Pass an empty array [] to disable automatic hyphenation.
 *
 * Full pattern files for ~100 languages are available from CTAN:
 *   https://www.ctan.org/tex-archive/language/hyph-utf8/tex/generic/hyph-utf8/patterns/tex
 *
 * This example uses a small Dutch sample pattern file bundled with the
 * examples to keep the demo self-contained.  The same technique works with
 * any complete CTAN TeX pattern file.
 *
 * Comparison layout
 * ─────────────────
 * The page shows the same Dutch paragraph rendered twice side by side:
 *   Left  column — NO patterns loaded (only explicit &shy; break points)
 *   Right column — Dutch sample patterns loaded via loadTexHyphenPatterns()
 *
 * Dutch is a good demonstration language because it has many long compound
 * words (e.g. "woordenschat", "zelfstandigheid", "werkgelegenheid") that
 * benefit greatly from automatic hyphenation in narrow columns.
 */

// Path to the bundled sample Dutch TeX pattern file.
$patternFile = \realpath(__DIR__ . '/data/hyph-nl-sample.tex');
if ($patternFile === false) {
    throw new \RuntimeException('Missing pattern file: examples/data/hyph-nl-sample.tex');
}

// -----------------------------------------------------------------------
// Common document setup
// -----------------------------------------------------------------------
$pdf = new \Com\Tecnick\Pdf\Tcpdf('mm', true, false, true, '', null);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 064');
$pdf->setTitle('Custom TeX Hyphenation Dictionary');
$pdf->setKeywords('TCPDF tc-lib-pdf example hyphenation tex patterns dutch custom dictionary');
$pdf->setPDFFilename('E064_custom_hyphenation_dictionary.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// -----------------------------------------------------------------------
// Load the Dutch pattern file BEFORE generating content.
// -----------------------------------------------------------------------

// Step 1: parse the .tex file into a pattern array.
$dutchPatterns = $pdf->loadTexHyphenPatterns($patternFile);

// Step 2: install as the active dictionary.
$pdf->setTexHyphenPatterns($dutchPatterns);

// -----------------------------------------------------------------------
// Fonts
// -----------------------------------------------------------------------
$fontTitle = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 14);
$fontH2    = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 11);
$fontBody  = $pdf->font->insert($pdf->pon, 'times', '', 9);
$fontCode  = $pdf->font->insert($pdf->pon, 'courier', '', 8);
$fontSmall = $pdf->font->insert($pdf->pon, 'helvetica', '', 8);

// -----------------------------------------------------------------------
// Dutch sample text — a paragraph with long compound words.
// -----------------------------------------------------------------------
$dutchPara = 'De Nederlandse taal staat bekend om zijn lange samenstellingen. '
    . 'Woorden zoals woordenschatontwikkeling, zelfstandigheidsbewegingen, '
    . 'werkgelegenheidsbeleid, verantwoordelijkheidsgevoelens, '
    . 'maatschappelijkontwikkelingswerk en informatietechnologieonderwijs '
    . 'zijn perfecte voorbeelden van samenstellingen die automatische '
    . 'woordafbreking vereisen voor een goede leesbaarheid in smalle kolommen. '
    . 'Zonder patronen worden zulke woorden niet afgebroken en loopt de tekst '
    . 'over de kolomrand of ontstaan er grote witruimten bij uitgevulde tekst. '
    . 'Met de juiste TeX-patronen worden breekpunten automatisch ingevoegd '
    . 'op de linguistisch correcte syllabegrenzen van elk woord.';

// -----------------------------------------------------------------------
// Page 1 — Side-by-side comparison
// -----------------------------------------------------------------------
$page1 = $pdf->addPage();

// --- Title ---
$pdf->page->addContent($fontTitle['out']);
$pdf->page->addContent(
    $pdf->getTextCell(
        'Custom TeX Hyphenation Dictionary',
        15.0, 18.0, 180.0, 0.0,
        drawcell: false, valign: 'T', halign: 'L',
    )
);

$pdf->page->addContent($fontSmall['out']);
$pdf->page->addContent(
    $pdf->getTextCell(
        'loadTexHyphenPatterns() + setTexHyphenPatterns() — Dutch sample patterns vs no patterns',
        15.0, 27.0, 180.0, 0.0,
        drawcell: false, valign: 'T', halign: 'L',
    )
);

// --- Column headers ---
$pdf->page->addContent($fontH2['out']);
$pdf->page->addContent(
    $pdf->getTextCell('Without patterns', 15.0, 37.0, 87.0, 0.0, drawcell: false, valign: 'T', halign: 'C')
);
$pdf->page->addContent(
    $pdf->getTextCell('With Dutch patterns', 108.0, 37.0, 87.0, 0.0, drawcell: false, valign: 'T', halign: 'C')
);

// Separator line between header and body
$sepStyle = [
    'lineWidth'  => 0.3,
    'lineCap'    => 'butt',
    'lineJoin'   => 'miter',
    'dashArray'  => [],
    'dashPhase'  => 0,
    'lineColor'  => '#336699',
    'fillColor'  => '',
];
$pdf->page->addContent(
    $pdf->graph->getLine(15.0, 43.5, 195.0, 43.5, $sepStyle)
);

// Vertical divider between columns
$divStyle = [
    'lineWidth'  => 0.2,
    'lineCap'    => 'butt',
    'lineJoin'   => 'miter',
    'dashArray'  => [],
    'dashPhase'  => 0,
    'lineColor'  => '#aaaaaa',
    'fillColor'  => '',
];
$pdf->page->addContent(
    $pdf->graph->getLine(103.0, 44.0, 103.0, 200.0, $divStyle)
);

// -----------------------------------------------------------------------
// Left column — no automatic hyphenation
// -----------------------------------------------------------------------

// Temporarily disable patterns.
$pdf->setTexHyphenPatterns([]);

$leftHtml = '<div style="text-align:justify; font-size:9pt; color:#222222;">'
    . $dutchPara
    . '</div>';

// Re-insert non-bold font before left column addHTMLCell.
$pdf->font->insert($pdf->pon, 'helvetica', '', 9);
$pdf->addHTMLCell($leftHtml, 15.0, 45.0, 87.0);

// -----------------------------------------------------------------------
// Right column — Dutch patterns active
// -----------------------------------------------------------------------

// Re-enable Dutch patterns.
$pdf->setTexHyphenPatterns($dutchPatterns);

$rightHtml = '<div style="text-align:justify; font-size:9pt; color:#222222;">'
    . $dutchPara
    . '</div>';

// Reset font stack to non-bold before second addHTMLCell.
$pdf->font->insert($pdf->pon, 'helvetica', '', 9);
$pdf->addHTMLCell($rightHtml, 108.0, 45.0, 87.0);

// -----------------------------------------------------------------------
// Pattern statistics block (below columns)
// -----------------------------------------------------------------------
$pdf->page->addContent($fontSmall['out']);

$patternCount = \count($dutchPatterns);
$pdf->page->addContent(
    $pdf->getTextCell(
        "Pattern file: examples/data/hyph-nl-sample.tex  |  Patterns loaded: {$patternCount}"
        . '  |  Source: CTAN hyph-utf8 (excerpt)',
        15.0, 205.0, 180.0, 0.0,
        drawcell: false, valign: 'T', halign: 'C',
    )
);

// -----------------------------------------------------------------------
// Page 2 — API reference and usage guidance
// -----------------------------------------------------------------------
$page2 = $pdf->addPage();
// Re-insert non-bold base font so the font stack is clean before addHTMLCell.
$pdf->font->insert($pdf->pon, 'helvetica', '', 8);

$html2 = <<<'HTML'
<h1 style="font-size:14pt; color:#003366;">API Reference and Usage Guidance</h1>

<h2 style="font-size:11pt; color:#005599;">loadTexHyphenPatterns(string $file): array</h2>
<p style="font-size:9pt; color:#333333;">
Reads a TeX hyphenation pattern file and returns an associative array mapping
pattern keys to their weighted strings.  The file must contain a
<code>\patterns{ ... }</code> block in standard TeX format.  Comments
(lines starting with <code>%</code>) are stripped before parsing.
</p>
<p style="font-size:9pt; color:#333333;">
The returned array can be stored and reused across multiple PDF objects
without re-reading the file:
</p>
<pre style="font-size:8pt; color:#003366; background:#f0f4ff;">
$patterns = $pdf->loadTexHyphenPatterns('/path/to/hyph-en-us.tex');
$pdf->setTexHyphenPatterns($patterns);
</pre>

<h2 style="font-size:11pt; color:#005599;">setTexHyphenPatterns(array $patterns): void</h2>
<p style="font-size:9pt; color:#333333;">
Installs the pattern array as the active hyphenation dictionary.  Hyphenation
applies to all subsequent text layout calls.  Pass an empty array
<code>[]</code> to disable automatic hyphenation.
</p>

<h2 style="font-size:11pt; color:#005599;">Obtaining pattern files</h2>
<p style="font-size:9pt; color:#333333;">
Full-quality pattern files for approximately 100 languages are available from
the CTAN hyph-utf8 package:<br />
<b>https://www.ctan.org/tex-archive/language/hyph-utf8/tex/generic/hyph-utf8/patterns/tex</b><br /><br />
Notable files:
</p>
<table border="0" cellpadding="2">
  <tr><td style="font-size:8pt; width:55mm; color:#003366; font-weight:bold;">File</td>
      <td style="font-size:8pt; color:#003366; font-weight:bold;">Language</td></tr>
  <tr><td style="font-size:8pt;">hyph-en-us.tex</td><td style="font-size:8pt;">English (US)</td></tr>
  <tr><td style="font-size:8pt;">hyph-de-1996.tex</td><td style="font-size:8pt;">German (1996 reform)</td></tr>
  <tr><td style="font-size:8pt;">hyph-nl.tex</td><td style="font-size:8pt;">Dutch</td></tr>
  <tr><td style="font-size:8pt;">hyph-fr.tex</td><td style="font-size:8pt;">French</td></tr>
  <tr><td style="font-size:8pt;">hyph-pl.tex</td><td style="font-size:8pt;">Polish</td></tr>
  <tr><td style="font-size:8pt;">hyph-fi.tex</td><td style="font-size:8pt;">Finnish</td></tr>
  <tr><td style="font-size:8pt;">hyph-pt.tex</td><td style="font-size:8pt;">Portuguese</td></tr>
  <tr><td style="font-size:8pt;">hyph-sv.tex</td><td style="font-size:8pt;">Swedish</td></tr>
</table>

<h2 style="font-size:11pt; color:#005599;">Soft hyphens vs automatic patterns — when to use each</h2>
<p style="font-size:9pt; color:#333333;">
<b>&amp;shy; (soft hyphens in HTML)</b> — best for single documents with known content
where exact break points matter (e.g. trade-marked compound names, proper nouns
that patterns would get wrong).  See E028_text_hyphenation.php.<br /><br />
<b>setTexHyphenPatterns()</b> — best for documents with large amounts of flowing
text where manual annotation is impractical (articles, books, reports).  Patterns
are applied automatically to every word during layout without modifying the source
string.
</p>

<h2 style="font-size:11pt; color:#005599;">Switching languages mid-document</h2>
<p style="font-size:9pt; color:#333333;">
You can switch the active dictionary between pages or even between HTML cells
by calling <code>setTexHyphenPatterns()</code> again with a different pattern
array.  Load all required pattern arrays at startup to avoid disk I/O during
layout, then swap them as needed.
</p>
HTML;

$pdf->addHTMLCell($html2, 15.0, 20.0, 180.0);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
