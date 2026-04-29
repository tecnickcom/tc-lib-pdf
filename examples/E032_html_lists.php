<?php
/**
 * E032_html_lists.php
 *
 * @since       2026-04-28
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

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm', // string $unit = 'mm',
    true, // bool $isunicode = true,
    false, // bool $subsetfont = false,
    true, // bool $compress = true,
    '', // string $mode = '',
    null, // ?ObjEncrypt $objEncrypt = null,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 032');
$pdf->setTitle('HTML List Item CSS Variations');
$pdf->setKeywords('TCPDF tc-lib-pdf example html list css ul ol li');
$pdf->setPDFFilename('032_html_lists.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);
$pdf->enableDefaultPageContent();

$bfont = $pdf->font->insert($pdf->pon, 'dejavusans', '', 10);
// $bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$pdf->addPage();
$pdf->setBookmark('HTML list item CSS variations', '', 0, -1, 0, 0, 'B', '');
$pdf->page->addContent($bfont['out']);

// This example showcases all currently supported list-item CSS behavior:
// - list-style-type
// - list-style-position (inside / outside)
// - margin-left / padding-left
// - text-indent first-line behavior on list-item text
// - nested OL/UL interactions
// - li::marker styling (color, font-weight, font-style)
// - list-style-image CSS property parsing

$html = <<<HTML
<style>
body {
  color: #222222;
  font-size: 10pt;
}
h1 {
  color: #0f2b46;
  margin-bottom: 3mm;
}
h2 {
  color: #1e4f80;
  margin-top: 4mm;
  margin-bottom: 2mm;
}
.note {
  font-size: 8pt;
  color: #5b5b5b;
}
.panel {
  background-color: #f7fbff;
  padding: 2mm;
  margin-bottom: 2.5mm;
}

/* Variation set: list-style-type */
.ul-disc { list-style-type: disc; }
.ul-circle { list-style-type: circle; }
.ul-square { list-style-type: square; }
.ol-decimal { list-style-type: decimal; }
.ol-upper-alpha { list-style-type: upper-alpha; }
.ol-lower-roman { list-style-type: lower-roman; }

/* Variation set: list-style-position */
.pos-inside { list-style-position: inside; }
.pos-outside { list-style-position: outside; }

/* Variation set: margin/padding on containers and items */
.list-shift-a {
  margin-left: 0mm;
  padding-left: 3mm;
}
.list-shift-b {
  margin-left: 6mm;
  padding-left: 1mm;
}
.list-shift-c {
  margin-left: 10mm;
  padding-left: 0mm;
}
.list-shift-a li { margin-left: 0mm; padding-left: 0mm; }
.list-shift-b li { margin-left: 1mm; padding-left: 1mm; }
.list-shift-c li { margin-left: 2mm; padding-left: 0mm; }

/* Variation set: text-indent (first-line and hanging) */
.indent-first li { text-indent: 4mm; }
.indent-hanging li { text-indent: -2mm; }

/* Variation set: li::marker styling */
.marker-red li::marker { color: red; }
.marker-blue li::marker { color: blue; }
.marker-green li::marker { color: #008000; }

/* Variation set: list-style-image (custom bullets) */
.list-img-svg { list-style-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiPjxjaXJjbGUgY3g9IjQiIGN5PSI0IiByPSI0IiBmaWxsPSIjRkY2NjAwIi8+PC9zdmc+); }

/* Visual hint only */
.marker-guide,
.marker-line {
  border-left: 0.2mm solid #ff0000;
  padding-left: 1.5mm;
}
</style>

<h1>HTML List Item CSS Variations (Current Support)</h1>

<div class="panel">
  <h2>1) Unordered list styles (`list-style-type`)</h2>
  <ul class="ul-disc marker-guide">
    <li>Disc marker</li>
    <li>Nested sample
      <ul class="ul-circle">
        <li>Circle marker</li>
        <li>Another nested item</li>
      </ul>
    </li>
  </ul>
  <ul class="ul-square marker-guide">
    <li>Square marker</li>
    <li>Second square marker item</li>
  </ul>
</div>

<div class="panel">
  <h2>2) Ordered list styles (`list-style-type`)</h2>
  <ol class="ol-decimal marker-guide">
    <li>Decimal marker</li>
    <li>Second item</li>
  </ol>
  <ol class="ol-upper-alpha marker-guide">
    <li>Upper-alpha marker</li>
    <li>Second item</li>
  </ol>
  <ol class="ol-lower-roman marker-guide">
    <li>Lower-roman marker</li>
    <li>Second item</li>
  </ol>
</div>

<div class="panel">
  <h2>3) Marker position (`list-style-position`)</h2>
  <ul class="ul-disc pos-outside marker-guide">
    <li>Outside marker with long text to show wrap behavior for this rendering path in the PDF engine.</li>
    <li>Second outside item</li>
  </ul>
  <ul class="ul-disc pos-inside marker-guide">
    <li>Inside marker with long text to show wrap behavior for this rendering path in the PDF engine.</li>
    <li>Second inside item</li>
  </ul>
</div>

<div class="panel">
  <h2>4) Container and item indentation (`margin-left` / `padding-left`)</h2>
  <ul class="ul-disc list-shift-a marker-guide">
    <li>Shift A</li>
    <li>Shift A nested
      <ol class="ol-decimal">
        <li>Nested ordered item</li>
      </ol>
    </li>
  </ul>

  <ul class="ul-disc list-shift-b marker-guide">
    <li>Shift B</li>
    <li>Shift B nested
      <ol class="ol-upper-alpha">
        <li>Nested ordered item</li>
      </ol>
    </li>
  </ul>

  <ul class="ul-disc list-shift-c marker-guide">
    <li>Shift C</li>
    <li>Shift C nested
      <ol class="ol-lower-roman">
        <li>Nested ordered item</li>
      </ol>
    </li>
  </ul>
</div>

<div class="panel">
  <h2>5) Mixed nesting matrix (`ol` inside `ul`, `ul` inside `ol`)</h2>
  <ul class="ul-square marker-guide">
    <li>UL root item
      <ol class="ol-decimal pos-outside">
        <li>OL nested in UL
          <ul class="ul-circle pos-inside">
            <li>UL nested in OL</li>
            <li>Second nested UL item</li>
          </ul>
        </li>
      </ol>
    </li>
  </ul>
</div>

<div class="panel">
  <h2>6) List text indentation (`text-indent`)</h2>
  <ol class="ol-decimal indent-first marker-guide">
    <li>First-line indent with long text to make wrapping visible in the list-item rendering path. First-line indent with long text to make wrapping visible in the list-item rendering path.</li>
    <li>First-line indent with long text to <span>make</span> wrapping visible in the list-item rendering path. (B) First-line indent with long text to make wrapping visible in the list-item rendering path.</li>
    <li>Second item with first-line indent style applied.</li>
  </ol>

  <ol class="ol-decimal indent-hanging marker-guide">
    <li>Hanging-indent style (negative text-indent) with long text to highlight first-line offset behavior.</li>
    <li>Second item using the same hanging-indent rule.</li>
  </ol>
</div>

<div class="panel">
  <h2>7) Marker color styling (`li::marker`)</h2>
  <ol class="ol-decimal marker-red marker-guide">
    <li>Red marker color applied via li::marker selector</li>
    <li>Second item with red marker styling</li>
  </ol>
  <ul class="ul-disc marker-blue marker-guide">
    <li>Blue marker color applied via li::marker selector</li>
    <li>Second unordered item with blue marker</li>
  </ul>
  <ol class="ol-upper-alpha marker-green marker-guide">
    <li>Green marker color applied via li::marker selector</li>
    <li>Second item with green marker styling</li>
  </ol>
</div>

<div class="panel">
  <h2>8) Custom bullet images (`list-style-image`)</h2>
  <p class="note">Note: Image bullet rendering is subject to image loading availability and fallback behavior.</p>
  <ul class="list-img-svg marker-guide">
    <li>Custom SVG bullet marker image</li>
    <li>Second item with custom image bullet</li>
    <li>Nested list with custom bullets
      <ul class="list-img-svg">
        <li>Nested custom image bullet item</li>
        <li>Another nested custom image bullet</li>
      </ul>
    </li>
  </ul>
</div>
HTML;

$pdf->addHTMLCell(
    $html, // string $html,
    15, // float $posx = 0,
    20, // float $posy = 0,
    185, // float $width = 0,
);

// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF($rawpdf);
//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
