<?php
/**
 * 031_example_html_features.php
 *
 * @since       2026-04-27
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

// ----------


$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 031');
$pdf->setTitle('HTML Features Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example HTML selectors forms table tags');
$pdf->setPDFFilename('031_example_html_features.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$page01 = $pdf->addPage();
$pdf->setBookmark('HTML feature showcase', '', 0, -1, 0, 0, 'B', '');
$pdf->page->addContent($bfont['out']);

// NOTE:
// The HTML parser expects input already cleaned to XHTML-like markup (e.g. via tidyHTML),
// and attribute extraction is intentionally based on double-quoted values.
// Unsupported interactive selectors intentionally remain no-op (for example :hover).
// For table-structure tags, this example demonstrates current support for
// caption/colgroup/col/tfoot; unsupported styling on colgroup/col remains
// intentionally a no-op in this conservative implementation.

$html = <<<HTML
<style>
.demo {
  font-size: 10pt;
  color: #222222;
}
.demo h2 {
  color: #003366;
  margin: 0 0 3mm 0;
}
.demo .selector-list li:first-child {
  color: #0b6e4f;
  font-weight: bold;
}
.demo .selector-list li:last-child {
  color: #8a1c7c;
}
.demo .selector-list li:nth-child(2n+1) {
  background-color: #f4f7ff;
}
.demo .selector-list li:nth-child(-n+2) {
  text-decoration: underline;
}
.demo .group > p + a.target {
  color: #0066cc;
}
.demo .group > p ~ a.target {
  font-weight: bold;
}
.demo .generated::before {
  content: "[PRE] ";
  color: #b03030;
}
.demo .generated::after {
  content: " [POST]";
  font-weight: bold;
}
.demo p:empty {
  background-color: #fff2cc;
  border: 0.2mm solid #c7a600;
  height: 4mm;
}
.demo a:link {
  text-decoration: underline;
}
.demo table {
  border: 0.2mm solid #666666;
}
.demo th {
  background-color: #dddddd;
}
.demo input,
.demo select,
.demo textarea {
  border: 0.2mm solid #777777;
}
.note {
  font-size: 8pt;
  color: #555555;
}
</style>
<div class="demo">
  <h2>HTML Feature Showcase</h2>
  <p>This example demonstrates currently implemented selector, table, and form behavior in the HTML engine.</p>

  <h3>1) Pseudo-class selectors</h3>
  <ul class="selector-list">
    <li>First item (first-child + odd + -n+2)</li>
    <li>Second item (-n+2)</li>
    <li>Third item (odd)</li>
    <li>Fourth item (last-child)</li>
  </ul>

  <h3>2) Combinators (+ and ~)</h3>
  <div class="group">
    <p>Adjacent sibling trigger paragraph</p>
    <a class="target" href="https://github.com/tecnickcom/tc-lib-pdf">Adjacent + general sibling target link</a>
  </div>

  <h3>3) Empty pseudo-class</h3>
  <p></p>

  <h3>4) Pseudo-elements (::before / ::after, text-only content)</h3>
  <p><span class="generated">Generated marker sample</span></p>

  <h3>5) Stable table rendering with selectors applied</h3>
  <table border="1" cellpadding="2" cellspacing="0" style="width:100%">
    <tr>
      <th style="width:20%">Key</th>
      <th style="width:80%">Value</th>
    </tr>
    <tr>
      <td>first-child</td>
      <td>Supported</td>
    </tr>
    <tr>
      <td>last-child</td>
      <td>Supported</td>
    </tr>
    <tr>
      <td>nth-child</td>
      <td>Supports n, odd/even, and an+b (for example 2n+1, -n+2)</td>
    </tr>
    <tr>
      <td>empty</td>
      <td>Supported</td>
    </tr>
    <tr>
      <td>link</td>
      <td>Supported for anchors with href</td>
    </tr>
  </table>

  <h3>6) Table structure tags (caption, colgroup, col, tfoot)</h3>
  <table border="1" cellpadding="2" cellspacing="0" style="width:100%">
    <caption>Quarterly feature status</caption>
    <colgroup>
      <col width="22%">
      <col span="2" width="78%">
    </colgroup>
    <thead>
      <tr>
        <th>Area</th>
        <th>State</th>
        <th>Notes</th>
      </tr>
    </thead>
    <tr>
      <td>caption</td>
      <td>Rendered</td>
      <td>Displayed as a block-level table title.</td>
    </tr>
    <tr>
      <td>colgroup/col width hints</td>
      <td>Applied</td>
      <td>Used as precomputed column width hints before first-row fallback.</td>
    </tr>
    <tfoot>
      <tr>
        <td>tfoot</td>
        <td>Parsed</td>
        <td>Footer row contributes to table row/column bookkeeping.</td>
      </tr>
    </tfoot>
  </table>

  <h3>7) Form semantics (labels, field flags, select precedence)</h3>
  <p>
    <label for="contact_email">Email (for-id label):</label>
    <input type="email" id="contact_email" name="contact_email" value="user@example.com" maxlength="64" width="150">
  </p>
  <p>
    <label>Phone (wrapping label fallback):
      <input type="text" name="contact_phone" value="+39 010 123 456" size="70" width="150">
    </label>
  </p>
  <p>
    <label for="priority_pick">Priority:</label>
    <select id="priority_pick" name="priority_pick" value="normal" width="200">
      <optgroup label="Standard">
        <option value="normal">Normal</option>
        <option value="low">Low</option>
      </optgroup>
      <optgroup label="Urgent">
        <option value="high" selected>High (explicit selected wins)</option>
        <option value="critical">Critical</option>
      </optgroup>
    </select>
  </p>
  <p>
    <label for="notes">Notes (disabled textarea mapping):</label>
    <textarea id="notes" name="notes" cols="32" rows="2" maxlength="120" disabled>Handled by support queue</textarea>
    <br>
    <span class="note">Visible fallback text: Handled by support queue</span>
  </p>

  <p class="note">
    Unsupported selector examples intentionally remain unmatched: :hover, :focus, :active, and :visited.
  </p>
  <p class="note">
    Pseudo-elements are intentionally limited to text-only `content:"..."` behavior in this conservative implementation.
  </p>
  <p class="note">
    Unsupported styling on structural tags like colgroup/col is intentionally ignored in this conservative HTML engine pass.
  </p>
  <p class="note">
    This form section is focused on parser/field-mapping behavior; it is not intended to replicate browser form UX.
  </p>
</div>
HTML;

$pdf->addHTMLCell(
    $html, // string $html,
    15, // float $posx = 0,
    20, // float $posy = 0,
    180, // float $width = 0,
);

// ----------

// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF($rawpdf);
//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
