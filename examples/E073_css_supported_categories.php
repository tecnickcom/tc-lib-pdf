<?php
/**
 * E073_css_supported_categories.php
 *
 * Demonstrates the major CSS feature categories supported by tc-lib-pdf,
 * including table/float/clear interaction, CSS shorthand inherit propagation,
 * fieldset and inline-block element styling, overflow-wrap, and complex
 * CSS selector combinators.
 *
 * @since       2026-05-08
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

$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 073');
$pdf->setTitle('CSS Supported Categories');
$pdf->setKeywords('TCPDF tc-lib-pdf example CSS');
$pdf->setPDFFilename('073_css_supported_categories.pdf');


$pdf->font->insert($pdf->pon, 'courier', '', 11);

$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);
$pdf->addPage();
$pdf->page->addContent($bfont['out']);

$html = <<<HTML
<style>
@media all {
  body { font-family: helvetica; font-size: 10pt; }

  .panel { margin: 2mm 0; padding: 2mm; border: 0.3mm solid #333; }

  /* Cascade and source order */
  .cascade-color { color: #cc0000; border: 0.3mm solid #888; }
  .cascade-color { color: #0055aa; border: 0.6mm dashed #0055aa; }

  /* Selectors */
  #selectors li:first-child { color: #0a7a0a; }
  #selectors li:last-child { color: #aa2222; }
  #selectors a[href^="https"] { text-decoration: underline; }
  [role~="chip"] { background-color: #efefef; padding: 1mm; border: 0.2mm solid #888; }

  /* Box and typography */
  .box { margin: 1mm; padding: 2mm; border: 0.3mm solid #000; width: 60mm; }
  .typo { line-height: 150%; text-transform: capitalize; letter-spacing: 0.15mm; word-spacing: 0.25mm; }

  /* Floats / clear / position */
  .float-left { float: left; width: 35mm; border: 0.3mm solid #000; }
  .float-right { float: right; width: 35mm; border: 0.3mm solid #000; }
  .clear-both { clear: both; }
  .pos-rel { position: relative; left: 2mm; }

  /* Tables */
  .tbl-fixed { width: 90mm; table-layout: fixed; border-collapse: collapse; border: 0.3mm solid #000; }
  .tbl-auto { width: 90mm; table-layout: auto; border-collapse: separate; border-spacing: 1mm 0.5mm; border: 0.3mm solid #000; }
  .tbl-fixed td, .tbl-auto td { border: 0.2mm solid #333; padding: 1mm; }

  /* Paged media */
  .page-break { page-break-before: always; }
  .avoid-break { page-break-inside: avoid; }
}

@media print {
  .print-only { font-weight: bold; }
}
</style>
<h1>CSS Supported Categories Showcase</h1>
<p>This document provides one consolidated example of major CSS categories currently supported by tc-lib-pdf.</p>

<h2>1) Cascade and source order</h2>
<div class="panel cascade-color">This text is resolved through cascade with later source-order override.</div>

<h2>2) Selectors (attribute + pseudo-class subset)</h2>
<div id="selectors" class="panel">
<ul>
<li>First item styled by :first-child</li>
<li>Middle item</li>
<li>Last item styled by :last-child</li>
</ul>
<p><a href="https://example.com">Link with href^ selector</a></p>
<span role="tag chip">Attribute selector [role~=chip]</span>
</div>

<h2>3) Box model and typography</h2>
<div class="panel">
<div class="box typo">box-model sample with margin, padding, border, width, line-height, text-transform, letter-spacing and word-spacing.</div>
</div>

<h2>4) Float, clear, and position</h2>
<div class="panel avoid-break">
<div class="float-left">FLOAT LEFT</div>
<div class="float-right">FLOAT RIGHT</div>
<div class="clear-both pos-rel">Clear-both block with relative offset.</div>
</div>

<h2>5) Table layout and borders</h2>
<div class="panel">
<table class="tbl-fixed">
<tr><td width="25%">Fixed A</td><td width="75%">Fixed B with longer content</td></tr>
</table>
<table class="tbl-auto">
<tr><td>Auto A</td><td>Auto B with longer content</td></tr>
</table>
</div>

<div class="page-break"></div>
<h2>6) Paged media aliases and print media</h2>
<div class="panel print-only">This block is inside @media print and uses page-break-before for explicit pagination.</div>
<p>End of CSS category showcase.</p>

<div class="page-break"></div>
<h2>7) Table, Float, and Clear Interaction</h2>
<p>Two floated KPI boxes followed by a clear:both div and a full-width table.
The table begins below the floats at full available width.</p>
<style>
.kpi-box { float: left; width: 44%; margin-right: 4%; padding: 3pt;
           border: 0.4pt solid #9db0c1; background: #f4f8fb; }
.kpi-box h3 { margin: 0 0 2pt 0; font-size: 10pt; }
.kpi-clear { clear: both; height: 0; line-height: 0; }
.kpi-table { width: 100%; border-collapse: collapse; margin-top: 4pt; }
.kpi-table th, .kpi-table td { border: 0.4pt solid #95a8ba; padding: 2pt; font-size: 9pt; }
.kpi-table th { background: #e4edf5; }
</style>
<div class="kpi-box"><h3>Uptime</h3><p style="margin:0;">99.92%</p></div>
<div class="kpi-box"><h3>Alerts</h3><p style="margin:0;">17 open, 4 critical</p></div>
<div class="kpi-clear"></div>
<table class="kpi-table">
  <tr><th>Service</th><th>Latency P95</th><th>Error Rate</th><th>Status</th></tr>
  <tr><td>Gateway</td><td>238ms</td><td>0.4%</td><td>Degraded</td></tr>
  <tr><td>Billing</td><td>122ms</td><td>0.1%</td><td>Healthy</td></tr>
</table>

<h2>8) CSS Shorthand inherit Keyword</h2>
<p>Shorthand properties (margin, padding, border, background, list-style)
propagate the inherit keyword to each decomposed longhand.</p>
<style>
.inherit-parent { margin: 3pt; padding: 4pt; border: 0.4pt solid #336699;
                  background: #eef3fb; color: #1a3558; }
.inherit-child-margin { margin: inherit; background: #ddeeff; padding: 2pt; }
.inherit-child-padding { padding: inherit; border: inherit; background: #d4f0d4; }
.inherit-list-parent { list-style: disc inside; color: #553300; }
.inherit-list-child { list-style: inherit; margin-left: 5mm; }
</style>
<div class="inherit-parent">
  Parent: margin 3pt, padding 4pt, border blue, background #eef3fb.
  <div class="inherit-child-margin">Child with margin:inherit — same outer spacing as parent.</div>
  <div class="inherit-child-padding">Child with padding:inherit and border:inherit — matches parent box insets.</div>
</div>
<ul class="inherit-list-parent">
  <li>Parent list (disc, inside)</li>
  <li><ul class="inherit-list-child"><li>Child with list-style:inherit — disc inside preserved.</li></ul></li>
</ul>

<h2>9) Fieldset, Legend, and Inline-Block Styling</h2>
<p>Structural styles on fieldset-like containers, legend labels, and inline-block spans.
Note: interactive pseudo-states (:hover, :focus) are not applicable in static PDF output.</p>
<style>
.demo-fieldset { border: 0.4pt solid #9ea7b3; margin: 0 0 4pt 0; padding: 4pt; }
.demo-legend { font-weight: bold; font-size: 9pt; }
.demo-label { display: inline-block; width: 80pt; font-weight: bold; color: #333; }
.demo-input { display: inline-block; border: 0.4pt solid #b9c1cb; padding: 1pt 3pt;
              width: 160pt; background: #fafbfc; }
.demo-help { color: #53606f; font-size: 8pt; margin-top: 2pt; }
</style>
<div class="demo-fieldset">
  <span class="demo-legend">Account Details</span>
  <div><span class="demo-label">Full name</span><span class="demo-input">Jane Example</span></div>
  <div><span class="demo-label">Email</span><span class="demo-input">jane@example.test</span></div>
  <p class="demo-help">Note: interactive states (:focus, :hover) are not applicable to static PDF output.</p>
</div>

<h2>10) Overflow-Wrap and Complex Selector Combinators</h2>
<p>Long unbreakable words in narrow containers with overflow-wrap; child (&gt;), adjacent (+), and general sibling (~) combinators.</p>
<style>
.overflow-box { width: 70mm; border: 0.4pt solid #aaa; padding: 2pt; overflow-wrap: break-word; word-break: break-word; }
.combo-parent > p { color: #004488; }
.combo-parent > p + span { font-style: italic; }
.combo-parent > p ~ span { text-decoration: underline; }
</style>
<div class="overflow-box">
Shortword and <code>verylongunbreakableidentifier_thatcouldcauseoverflowInNarrowColumns</code> handled.
</div>
<div class="combo-parent">
  <p>Direct child paragraph (color #004488 via &gt; combinator).</p>
  <span>Adjacent sibling span (italic via p + span).</span>
  <span>General sibling span (underline via p ~ span).</span>
</div>

<div class="page-break"></div>
<h2>11) Admin Float + Table Clearance</h2>
<p>The table top border must start below the dotted guide and below both KPI cards, without touching or overlapping card borders.</p>
<style>
.tracker-wrap { border: 0.4pt solid #a6b3bf; padding: 6pt; background: #fbfcfd; }
.tracker-kpi { float: left; width: 48%; margin-right: 2%; padding: 6pt;
               border: 0.5pt solid #9db0c1; background: #f4f8fb; }
.tracker-kpi-last { margin-right: 0; }
.tracker-kpi h3 { margin: 0 0 4pt 0; font-size: 11pt; }
.tracker-clear { clear: both; height: 0; line-height: 0; }
.tracker-guide { margin-top: 4pt; border-top: 0.6pt dotted #d66; color: #a33; font-size: 8pt; }
.tracker-table { width: 100%; border-collapse: collapse; margin-top: 8pt; }
.tracker-table th, .tracker-table td { border: 0.5pt solid #95a8ba; padding: 3pt; }
.tracker-table th { background: #e4edf5; }
</style>
<div class="tracker-wrap">
  <div class="tracker-kpi"><h3>Uptime</h3><p style="margin:0;">99.92% this month</p></div>
  <div class="tracker-kpi tracker-kpi-last"><h3>Alerts</h3><p style="margin:0;">17 open, 4 critical</p></div>
  <div class="tracker-clear"></div>
  <div class="tracker-guide">Guide: table must begin below this line.</div>
  <table class="tracker-table">
    <tr><th>Service</th><th>Latency P95</th><th>Error Rate</th><th>Status</th></tr>
    <tr><td>Gateway</td><td>238ms</td><td>0.4%</td><td>Degraded</td></tr>
    <tr><td>Billing</td><td>122ms</td><td>0.1%</td><td>Healthy</td></tr>
    <tr><td>Notifications</td><td>305ms</td><td>0.8%</td><td>Warning</td></tr>
    <tr><td>Search</td><td>188ms</td><td>0.2%</td><td>Healthy</td></tr>
  </table>
</div>

<div class="page-break"></div>
<h2>12) Font Shorthand Parsing and Inherit</h2>
<p>This section showcases the newly added support for mapping CSS <code>font</code> shorthand
to longhands and propagating <code>font: inherit</code> to child elements.</p>
<style>
.font-demo-wrap { border: 0.4pt solid #9aa6b2; padding: 6pt; background: #fbfcff; }
.font-explicit {
  font: italic 700 14pt/1.5 "Times New Roman", serif;
  color: #1f3d5a;
  margin-bottom: 5pt;
}
.font-explicit code {
  font: normal 400 9pt/1.2 courier;
  color: #5b2d00;
}
.font-inherit-parent {
  font: oblique 600 12pt/1.4 helvetica;
  background: #f0f6fb;
  border: 0.4pt solid #9db4c9;
  padding: 4pt;
  margin-bottom: 5pt;
}
.font-inherit-child {
  font: inherit;
  border: 0.4pt dashed #aac0d5;
  padding: 3pt;
  margin-top: 3pt;
}
.font-shorthand-plus-longhand {
  font: italic 700 13pt/1.4 "Times New Roman", serif;
  font-style: normal;
  border: 0.4pt solid #c2c2c2;
  padding: 3pt;
}
</style>

<div class="font-demo-wrap">
  <div class="font-explicit">
    Explicit shorthand: <strong>italic + bold + 14pt + line-height 1.5 + Times family</strong>.
    <br /><code>font: italic 700 14pt/1.5 "Times New Roman", serif;</code>
  </div>

  <div class="font-inherit-parent">
    Parent uses shorthand font definition (oblique, semi-bold, 12pt, line-height 1.4).
    <div class="font-inherit-child">
      Child uses <code>font: inherit</code> and should visually match the parent font settings.
    </div>
  </div>

  <div class="font-shorthand-plus-longhand">
    Shorthand + explicit longhand override: italic from shorthand is overridden by
    <code>font-style: normal</code>, while weight/size/family from shorthand remain.
  </div>
</div>

<div class="page-break"></div>
<h2>13) Form Control CSS to Widget Mapping</h2>
<p>This section demonstrates static style mapping from CSS to AcroForm widgets:
background color, border color/width/style, and text alignment.</p>
<style>
.form-grid { border: 0.4pt solid #b0bac6; background: #f8fafc; padding: 6pt; }
.form-row { margin-bottom: 5pt; }
.form-label { display: inline-block; width: 95pt; font-weight: bold; color: #34495e; }
.ctl-text {
  width: 180pt;
  border: 0.8pt dashed #3a6ea5;
  background: #eaf3ff;
  color: #123d66;
  text-align: center;
}
.ctl-number {
  width: 120pt;
  border: 0.8pt solid #6a8f44;
  background: #f2faea;
  color: #2e4f1d;
}
.ctl-select {
  width: 180pt;
  border: 0.8pt solid #8e44ad;
  background: #f6ebfb;
  color: #4c1f61;
}
.ctl-textarea {
  width: 180pt;
  border: 0.8pt solid #c57b1d;
  background: #fff7e8;
  color: #6f3d00;
}
.ctl-button {
  width: 95pt;
  border: 0.8pt solid #7f8c8d;
  background: #eef1f2;
  color: #2f3a3b;
}
</style>

<div class="form-grid">
  <div class="form-row">
    <span class="form-label">Styled text input</span>
    <input class="ctl-text" type="text" name="demo_text" value="Centered text" />
  </div>
  <div class="form-row">
    <span class="form-label">Styled number input</span>
    <input class="ctl-number" type="number" name="demo_num" value="12345" />
  </div>
  <div class="form-row">
    <span class="form-label">Styled select</span>
    <select class="ctl-select" name="demo_plan">
      <option value="starter">Starter</option>
      <option value="pro" selected="selected">Professional</option>
      <option value="ent">Enterprise</option>
    </select>
  </div>
  <div class="form-row">
    <span class="form-label">Styled textarea</span>
    <textarea class="ctl-textarea" name="demo_notes" rows="3" cols="26">Multiline notes example</textarea>
  </div>
  <div class="form-row">
    <span class="form-label">Styled button</span>
    <input class="ctl-button" type="button" name="demo_btn" value="Action" />
  </div>
</div>
HTML;

$pdf->addHTMLCell($html, 15, 18, 180, 0);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
