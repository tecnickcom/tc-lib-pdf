<?php
/**
 * E031_html_features.php
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
$pdf->setPDFFilename('031_html_features.pdf');

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

// HTML B

$pageV02 = $pdf->addPage();

$textcolors = '<h2>Text</h1>';
$bgcolors = '<h2>Background</h2>';

foreach($pdf->color::WEBHEX as $k => $v) {
	$textcolors .= '<span color="#'.$v.'">'.$k.'</span> ';
    $bgcolors .= '<span bgcolor="#'.$v.'" color="#333333">'.$k.'</span> ';
}

$html_02 = '<h1>HTML Colors</h1>'.$textcolors.'<hr />'.$bgcolors;

$pdf->addHTMLCell(
    $html_02,
    20, 
    10,
    180,
);

// ----------

// HTML C

$pageV03 = $pdf->addPage();

$html_03 = '<h1>Various HTML Tests</h1>
<font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font> <font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font> <font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font> <font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font> <font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font>';



// Test fonts nesting
$htmlt1 = 'Default <font face="courier">Courier <font face="helvetica">Helvetica <font face="times">Times <font face="dejavusans">dejavusans </font>Times </font>Helvetica </font>Courier </font>Default';
$htmlt2 = '<small>small text</small> normal <small>small text</small> normal <sub>subscript</sub> normal <sup>superscript</sup> normal';
$htmlt3 = '<font size="10" color="#ff7f50">The</font> <font size="10" color="#6495ed">quick</font> <font size="14" color="#dc143c">brown</font> <font size="18" color="#008000">fox</font> <font size="22"><a href="https://tcpdf.org">jumps</a></font> <font size="22" color="#a0522d">over</font> <font size="18" color="#da70d6">the</font> <font size="14" color="#9400d3">lazy</font> <font size="10" color="#4169ef">dog</font>.';

$html_03 .= '<br />'.$htmlt1.'<br />'.$htmlt2.'<br />'.$htmlt3.'<br />'.$htmlt3.'<br />'.$htmlt2;

$html_03 .= <<<EOF
<hr />
<h2>Div Blocks</h2>
<div style="background-color:#880000;color:white;">
Hello World!<br />
Hello
</div>
<pre style="background-color:#336699;color:white;">
int main() {
    printf("HelloWorld");
    return 0;
}
</pre>
<tt>Monospace font</tt>, normal font, <tt>monospace font</tt>, normal font.
<br />
<div style="background-color:#880000;color:white;">DIV LEVEL 1<div style="background-color:#008800;color:white;">DIV LEVEL 2</div>DIV LEVEL 1</div>
<br />
<span style="background-color:#880000;color:white;">SPAN LEVEL 1 <span style="background-color:#008800;color:white;">SPAN LEVEL 2</span> SPAN LEVEL 1</span>
EOF;

$pdf->addHTMLCell(
    $html_03,
    20,
    10,
    180,
);

// ----------

// HTML E-A

$pageV05A = $pdf->addPage();

$html_05A = '<h2>HTML Text Alignment (A)</h2>
Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. <em>Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?</em> <em>Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?</em><br /><br /><b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i><br /><br /><b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u>';


$pdf->addHTMLCell(
    $html_05A,
    20,
    10,
    180,
);

// ----------

// HTML E-L

$html_05L = '<p>Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo. Lima Mike November Oscar Papa Quebec Romeo (<em>Sierra-Tango</em>) Uniform Victor Whiskey (<em>Xray-Yankee</em>). Zulu. Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo. Lima Mike November Oscar Papa Quebec Romeo (<em>Sierra-Tango</em>) Uniform Victor Whiskey (<em>Xray-Yankee</em>). Zulu. Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo. Lima Mike November Oscar Papa Quebec Romeo (<em>Sierra-Tango</em>) Uniform Victor Whiskey (<em>Xray-Yankee</em>). Zulu.</p>';

$pdf->addHTMLCell(
    $html_05L,
    20,
    100,
    180,
);

// ----------

// HTML E-B

$pageV05B = $pdf->addPage();

$html_05B = '<h2>HTML Text Alignment (B)</h2>
<div style="text-align:justify;">JUSTIFY: Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray Yankee Zulu Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo Lima Mike November Oscar Papa
Quebec Romeo</div>
<br />
<div style="text-align:left;">LEFT: Alfa <i>Bravo</i> Charlie <i>Delta</i> Echo <i>Foxtrot</i> Golf <i>Hotel</i> India <i>Juliett</i> Kilo <i>Lima</i> Mike <i>November</i> Oscar <i>Papa</i> Quebec <i>Romeo</i> Sierra <i>Tango</i> Uniform <i>Victor</i> Whiskey <i>Xray</i> Yankee <i>Zulu</i></div>
<br />
<div style="text-align:center;">CENTER: Alfa <i>Bravo</i> Charlie <i>Delta</i> Echo <i>Foxtrot</i> Golf <i>Hotel</i> India <i>Juliett</i> Kilo <i>Lima</i> Mike <i>November</i> Oscar <i>Papa</i> Quebec <i>Romeo</i> Sierra <i>Tango</i> Uniform <i>Victor</i> Whiskey <i>Xray</i> Yankee <i>Zulu</i></div>
<br />
<div style="text-align:right;">RIGHT: Alfa <i>Bravo</i> Charlie <i>Delta</i> Echo <i>Foxtrot</i> Golf <i>Hotel</i> India <i>Juliett</i> Kilo <i>Lima</i> Mike <i>November</i> Oscar <i>Papa</i> Quebec <i>Romeo</i> Sierra <i>Tango</i> Uniform <i>Victor</i> Whiskey <i>Xray</i> Yankee <i>Zulu</i></div>
<br />
<div style="text-align:justify;">JUSTIFY: Alfa <i>Bravo</i> Charlie <i>Delta</i> Echo <i>Foxtrot</i> Golf <i>Hotel</i> India <i>Juliett</i> Kilo <i>Lima</i> Mike <i>November</i> Oscar <i>Papa</i> Quebec <i>Romeo</i> Sierra <i>Tango</i> Uniform <i>Victor</i> Whiskey <i>Xray</i> Yankee <i>Zulu</i></div>
<br />
<div style="text-align:justify;">JUSTIFY: Alfa <i>Bravo</i> Charlie <i>Delta</i> Echo <img src="images/tcpdf_logo.jpg" alt="TCPDF logo" width="89" height="30" border="0" /><img src="images/tcpdf_box.svg" alt="TCPDF box" width="100" height="67" border="0" /> <i>Foxtrot</i> Golf <i>Hotel</i> India <i>Juliett</i> Kilo <i>Lima</i> Mike <i>November</i> Oscar <i>Papa</i> Quebec <i>Romeo</i> Sierra <i>Tango</i> Uniform <i>Victor</i> Whiskey <i>Xray</i> Yankee <i>Zulu</i></div>
<br />
<div style="text-align:left;">LEFT: Alfa <i>Bravo</i> Charlie <i>Delta</i> Echo <img src="images/tcpdf_logo.jpg" alt="TCPDF logo" width="89" height="30" border="0" /><img src="images/tcpdf_box.svg" alt="TCPDF box" width="100" height="67" border="0" /> <i>Foxtrot</i> Golf <i>Hotel</i> India <i>Juliett</i> Kilo <i>Lima</i> Mike <i>November</i> Oscar <i>Papa</i> Quebec <i>Romeo</i> Sierra <i>Tango</i> Uniform <i>Victor</i> Whiskey <i>Xray</i> Yankee <i>Zulu</i></div>
<br />
<div style="text-align:center;">CENTER: Alfa <i>Bravo</i> Charlie <i>Delta</i> Echo <img src="images/tcpdf_logo.jpg" alt="TCPDF logo" width="89" height="30" border="0" /><img src="images/tcpdf_box.svg" alt="TCPDF box" width="100" height="67" border="0" /> <i>Foxtrot</i> Golf <i>Hotel</i> India <i>Juliett</i> Kilo <i>Lima</i> Mike <i>November</i> Oscar <i>Papa</i> Quebec <i>Romeo</i> Sierra <i>Tango</i> Uniform <i>Victor</i> Whiskey <i>Xray</i> Yankee <i>Zulu</i></div>
<br />
<div style="text-align:right;">RIGHT: Alfa <i>Bravo</i> Charlie <i>Delta</i> Echo <img src="images/tcpdf_logo.jpg" alt="TCPDF logo" width="89" height="30" border="0" /><img src="images/tcpdf_box.svg" alt="TCPDF box" width="100" height="67" border="0" /> <i>Foxtrot</i> Golf <i>Hotel</i> India <i>Juliett</i> Kilo <i>Lima</i> Mike <i>November</i> Oscar <i>Papa</i> Quebec <i>Romeo</i> Sierra <i>Tango</i> Uniform <i>Victor</i> Whiskey <i>Xray</i> Yankee <i>Zulu</i></div>';


$pdf->addHTMLCell(
    $html_05B,
    20,
    10,
    180,
);

// ----------

// HTML F

$pageV06 = $pdf->addPage();

$html_06 = <<<EOF
<!-- EXAMPLE OF CSS STYLE -->
<style>
	h1 {
		color: navy;
		font-family: times;
		font-size: 24pt;
		text-decoration: underline;
	}
	p.first {
		color: #003300;
		font-family: helvetica;
		font-size: 12pt;
	}
	p.first span {
		color: #006600;
		font-style: italic;
	}
	p#second {
		color: rgb(00,63,127);
		font-family: times;
		font-size: 12pt;
		text-align: justify;
	}
	p#second > span {
		background-color: #FFFFAA;
	}
	table.first {
		color: #003300;
		font-family: helvetica;
		font-size: 8pt;
		border-left: 3px solid red;
		border-right: 3px solid #FF00FF;
		border-top: 3px solid green;
		border-bottom: 3px solid blue;
		background-color: #ccffcc;
	}
	td {
		border: 2px solid blue;
		background-color: #ffffee;
	}
	td.second {
		border: 2px dashed green;
	}
	div.test {
		color: #CC0000;
		background-color: #FFFF66;
		font-family: helvetica;
		font-size: 10pt;
		border-style: solid solid solid solid;
		border-width: 2px 2px 2px 2px;
		border-color: green #FF00FF blue red;
		text-align: center;
	}
	.lowercase {
		text-transform: lowercase;
	}
	.uppercase {
		text-transform: uppercase;
	}
	.capitalize {
		text-transform: capitalize;
	}
</style>

<h1 class="title">Example of <i style="color:#990000">XHTML + CSS</i></h1>

<p class="first">Example of paragraph with class selector. <span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In sed imperdiet lectus. Phasellus quis velit velit, non condimentum quam. Sed neque urna, ultrices ac volutpat vel, laoreet vitae augue. Sed vel velit erat. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras eget velit nulla, eu sagittis elit. Nunc ac arcu est, in lobortis tellus. Praesent condimentum rhoncus sodales. In hac habitasse platea dictumst. Proin porta eros pharetra enim tincidunt dignissim nec vel dolor. Cras sapien elit, ornare ac dignissim eu, ultricies ac eros. Maecenas augue magna, ultrices a congue in, mollis eu nulla. Nunc venenatis massa at est eleifend faucibus. Vivamus sed risus lectus, nec interdum nunc.</span></p>

<p id="second">Example of paragraph with ID selector. <span>Fusce et felis vitae diam lobortis sollicitudin. Aenean tincidunt accumsan nisi, id vehicula quam laoreet elementum. Phasellus egestas interdum erat, et viverra ipsum ultricies ac. Praesent sagittis augue at augue volutpat eleifend. Cras nec orci neque. Mauris bibendum posuere blandit. Donec feugiat mollis dui sit amet pellentesque. Sed a enim justo. Donec tincidunt, nisl eget elementum aliquam, odio ipsum ultrices quam, eu porttitor ligula urna at lorem. Donec varius, eros et convallis laoreet, ligula tellus consequat felis, ut ornare metus tellus sodales velit. Duis sed diam ante. Ut rutrum malesuada massa, vitae consectetur ipsum rhoncus sed. Suspendisse potenti. Pellentesque a congue massa.</span></p>

<div class="test">example of DIV with border and fill.
<br />Lorem ipsum dolor sit amet, consectetur adipiscing elit.
<br /><span class="lowercase">text-transform <b>LOWERCASE</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit.</span>
<br /><span class="uppercase">text-transform <b>uppercase</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit.</span>
<br /><span class="capitalize">text-transform <b>cAPITALIZE</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit.</span>
</div>
EOF;

$pdf->addHTMLCell(
    $html_06,
    20,
    10,
    180,
);

// ----------

// HTML A

$pageV01 = $pdf->addPage();

$bfont6 = $pdf->font->insert($pdf->pon, 'dejavusans', '', 10);

$pdf->page->addContent($bfont6['out']);

$style_cell = [
    'all' => [
        'lineWidth' => 1,
        'lineCap' => 'round',
        'lineJoin' => 'round',
        'miterLimit' => 1,
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => 'green',
        'fillColor' => '#fdfca5',
    ],
];


$html_01 = '<h1>HTML Example A</h1>
Some special characters: &lt; € &euro; &#8364; &amp; è &egrave; &copy; &gt; \\slash \\\\double-slash \\\\\\triple-slash
<h2>List</h2>
List example:
<ol>
	<li><b>bold text</b></li>
	<li><i>italic text</i></li>
	<li><u>underlined text</u></li>
	<li><b>b<i>bi<u>biu</u>bi</i>b</b></li>
	<li><a href="https://tcpdf.org" dir="ltr">link to https://tcpdf.org</a></li>
	<li>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.<br />Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</li>
	<li>SUBLIST
		<ol>
			<li>row one
				<ul>
					<li>sublist</li>
				</ul>
			</li>
			<li>row two</li>
		</ol>
	</li>
	<li><b>T</b>E<i>S</i><u>T</u> <del>line through</del></li>
	<li><font size="+3">font + 3</font></li>
	<li><small>small text</small> normal <small>small text</small> normal <sub>subscript</sub> normal <sup>superscript</sup> normal</li>
</ol>
<dl>
	<dt>Coffee</dt>
	<dd>Black hot drink</dd>
	<dt>Milk</dt>
	<dd>White cold drink</dd>
</dl>

<div style="text-align:center">The words &#8220;<span dir="rtl">&#1502;&#1494;&#1500; [mazel] &#1496;&#1493;&#1489; [tov]</span>&#8221; mean &#8220;Congratulations!&#8221;</div>

<p>This is just an example of html code to demonstrate some supported CSS inline styles.
<span style="font-weight: bold;">bold text</span>
<span style="text-decoration: line-through;">line-trough</span>
<span style="text-decoration: underline line-through;">underline and line-trough</span>
<span style="color: rgb(0, 128, 64);">color</span>
<span style="background-color: rgb(255, 0, 0); color: rgb(255, 255, 255);">background color</span>
<span style="font-weight: bold;">bold</span>
<span style="font-size: xx-small;">xx-small</span>
<span style="font-size: x-small;">x-small</span>
<span style="font-size: small;">small</span>
<span style="font-size: medium;">medium</span>
<span style="font-size: large;">large</span>
<span style="font-size: x-large;">x-large</span>
<span style="font-size: xx-large;">xx-large</span>
</p>';


$pdf->addHTMLCell(
    $html_01, // string $html,
    20, // float $posx = 0,
    10, // float $posy = 0,
    150, // float $width = 0,
    0, // float $height = 0,
    null, // ?array $cell = null,
    $style_cell, // array $styles = [],
);

// ----------


// ----------

// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF($rawpdf);
//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
