<?php
/**
 * E073_css_supported_categories.php
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
HTML;

$pdf->addHTMLCell($html, 15, 18, 180, 0);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
