<?php
/**
 * E043_html_tables.php
 *
 * @since       2017-05-08
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


\define('OUTPUT_FILE', \realpath(__DIR__ . '/../target') . '/example.pdf');

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
$pdf->setSubject('tc-lib-pdf example: 043');
$pdf->setTitle('Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example');
$pdf->setPDFFilename('043_html_tables.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont1 = $pdf->font->insert($pdf->pon, 'dejavusans', '', 10);


// test images directory
$imgdir = \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-image/test/images/');

// ----------

$page1 = $pdf->addPage();

$subtable = '<table border="1" cellspacing="6" cellpadding="4"><tr><td>a</td><td>b</td></tr><tr><td>c</td><td>d</td></tr></table>';

$html1 = '<h2>HTML TABLES (A)</h2>
<table border="1" cellspacing="3" cellpadding="4">
	<tr>
		<th align="center">#</th>
		<th align="right">RIGHT align</th>
		<th align="left">LEFT align</th>
		<th>4A</th>
	</tr>
	<tr>
		<td>1</td>
		<td bgcolor="#cccccc" align="center" colspan="2">A1 ex<i>amp</i>le <a href="https://tcpdf.org">link</a> column span. One two tree four five six seven eight nine ten.<br />line after br<br /><small>small text</small> normal <sub>subscript</sub> normal <sup>superscript</sup> normal  bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla<ol><li>first<ol><li>sublist</li><li>sublist</li></ol></li><li>second</li></ol><small color="#FF0000" bgcolor="#FFFF00">small small small small small small small small small small small small small small small small small small small small</small></td>
		<td>4B</td>
	</tr>
	<tr>
		<td>'.$subtable.'</td>
		<td bgcolor="#0000FF" color="yellow" align="center">A2 € &euro; &#8364; &amp; è &egrave;<br/>A2 € &euro; &#8364; &amp; è &egrave;</td>
		<td bgcolor="#FFFF00" align="left"><span color="#FF0000">Red</span> Yellow BG</td>
		<td>4C</td>
	</tr>
	<tr>
		<td>1A</td>
		<td rowspan="2" colspan="2" bgcolor="#FFFFCC">2AA<br />2AB<br />2AC</td>
		<td bgcolor="#FF0000">4D</td>
	</tr>
	<tr>
		<td>1B</td>
		<td>4E</td>
	</tr>
	<tr>
		<td>1C</td>
		<td>2C</td>
		<td>3C</td>
		<td>4F</td>
	</tr>
</table>';

$pdf->addHTMLCell(
    $html1,
    20,
    10,
    180,
);

// ----------

$page2 = $pdf->addPage();

$html2 = <<<EOF
<!-- EXAMPLE OF CSS STYLE -->
<style>
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
</style>

<h2>HTML TABLES (B)</h2>
<table class="first" cellpadding="4" cellspacing="6">
 <tr>
  <td width="30" align="center"><b>No.</b></td>
  <td width="140" align="center" bgcolor="#FFFF00"><b>XXXX</b></td>
  <td width="140" align="center"><b>XXXX</b></td>
  <td width="80" align="center"> <b>XXXX</b></td>
  <td width="80" align="center"><b>XXXX</b></td>
  <td width="45" align="center"><b>XXXX</b></td>
 </tr>
 <tr>
  <td width="30" align="center">1.</td>
  <td width="140" rowspan="6" class="second">BRDD<br />XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX</td>
  <td width="140">XXXX<br />XXXX</td>
  <td width="80">XXXX<br />XXXX</td>
  <td width="80">XXXX</td>
  <td align="center" width="45">XXXX<br />XXXX</td>
 </tr>
 <tr>
  <td width="30" align="center" rowspan="3">2.</td>
  <td width="140" rowspan="3">XXXX<br />XXXX</td>
  <td width="80">XXXX<br />XXXX</td>
  <td width="80">XXXX<br />XXXX</td>
  <td align="center" width="45">XXXX<br />XXXX</td>
 </tr>
 <tr>
  <td width="80">XXXX<br />XXXX<br />XXXX<br />XXXX</td>
  <td width="80">XXXX<br />XXXX</td>
  <td align="center" width="45">XXXX<br />XXXX</td>
 </tr>
 <tr>
  <td width="80" rowspan="2" >XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX</td>
  <td width="80">XXXX<br />XXXX</td>
  <td align="center" width="45">XXXX<br />XXXX</td>
 </tr>
 <tr>
  <td width="30" align="center">3.</td>
  <td width="140">XXXX<br />XXXX</td>
  <td width="80">XXXX<br />XXXX</td>
  <td align="center" width="45">XXXX<br />XXXX</td>
 </tr>
 <tr bgcolor="#FFFF80">
  <td width="30" align="center">4.</td>
  <td width="140" bgcolor="#00CC00" color="#FFFF00">XXXX<br />XXXX</td>
  <td width="80">XXXX<br />XXXX</td>
  <td width="80">XXXX<br />XXXX</td>
  <td align="center" width="45">XXXX<br />XXXX</td>
 </tr>
</table>
EOF;

$pdf->addHTMLCell(
    $html2,
    20,
    10,
    180,
);

// ----------


$page3 = $pdf->addPage();

$html3 = <<<EOD
<h2>HTML TABLES (C)</h2>
<table cellspacing="0" cellpadding="1" border="1" style="font-size:x-small;">
	<tr>
		<td rowspan="3">COL 1 - ROW 1<br />COLSPAN 3</td>
		<td>COL 2 - ROW 1</td>
		<td>COL 3 - ROW 1</td>
	</tr>
	<tr>
		<td rowspan="2">COL 2 - ROW 2 - COLSPAN 2<br />text line<br />text line<br />text line<br />text line</td>
		<td>COL 3 - ROW 2</td>
	</tr>
	<tr>
		<td>COL 3 - ROW 3</td>
	</tr>
</table>
<hr />
<table cellspacing="0" cellpadding="1" border="1" style="font-size:x-small;">
	<tr>
		<td rowspan="3">COL 1 - ROW 1<br />COLSPAN 3<br />text line<br />text line<br />text line<br />text line<br />text line<br />text line</td>
		<td>COL 2 - ROW 1</td>
		<td>COL 3 - ROW 1</td>
	</tr>
	<tr>
		<td rowspan="2">COL 2 - ROW 2 - COLSPAN 2<br />text line<br />text line<br />text line<br />text line</td>
		<td>COL 3 - ROW 2</td>
	</tr>
	<tr>
		<td>COL 3 - ROW 3</td>
	</tr>
</table>
<hr />
<table cellspacing="0" cellpadding="1" border="1" style="font-size:x-small;">
	<tr>
		<td rowspan="3">COL 1 - ROW 1<br />COLSPAN 3<br />text line<br />text line<br />text line<br />text line<br />text line<br />text line</td>
		<td>COL 2 - ROW 1</td>
		<td>COL 3 - ROW 1</td>
	</tr>
	<tr>
		<td rowspan="2">COL 2 - ROW 2 - COLSPAN 2<br />text line<br />text line<br />text line<br />text line</td>
		<td>COL 3 - ROW 2<br />text line<br />text line</td>
	</tr>
	<tr>
		<td>COL 3 - ROW 3</td>
	</tr>
</table>
<hr />
<table border="1" style="font-size:x-small;">
	<tr>
		<th rowspan="3">Left column</th>
		<th colspan="5">Heading Column Span 5</th>
		<th colspan="9">Heading Column Span 9</th>
	</tr>
	<tr>
		<th rowspan="2">Rowspan 2<br />This is some text that fills the table cell.</th>
		<th colspan="2">span 2</th>
		<th colspan="2">span 2</th>
		<th rowspan="2">2 rows</th>
		<th colspan="8">Colspan 8</th>
	</tr>
	<tr>
		<th>1a</th>
		<th>2a</th>
		<th>1b</th>
		<th>2b</th>
		<th>1</th>
		<th>2</th>
		<th>3</th>
		<th>4</th>
		<th>5</th>
		<th>6</th>
		<th>7</th>
		<th>8</th>
	</tr>
</table>
EOD;

$pdf->addHTMLCell(
    $html3,
    20,
    10,
    180,
);

// ----------

$page4 = $pdf->addPage();

$html4 = '<h2>HTML TABLES (D)</h2>
<table border="1" cellspacing="3" cellpadding="4">
	<tr>
		<td align="left"><span>1L</span> <span>Alfa</span> <span>Bravo</span> <span>Charlie</span> <span>Delta</span> <span>Echo</span> <span>Foxtrot</span> <span>Golf</span> <span>Hotel</span> <span>India</span> <span>Juliett</span> <span>Kilo</span> <span>Lima</span> <span>Mike</span> <span>November</span> <span>Oscar</span> <span>Papa</span> <span>Quebec</span> <span>Romeo</span> <span>Sierra</span> <span>Tango</span> <span>Uniform</span> <span>Victor</span> <span>Whiskey</span> <span>Xray</span> <span>Yankee</span> <span>Zulu</span></td>
	</tr>
	<tr>
		<td align="center"><span>1C</span> <span>Alfa</span> <span>Bravo</span> <span>Charlie</span> <span>Delta</span> <span>Echo</span> <span>Foxtrot</span> <span>Golf</span> <span>Hotel</span> <span>India</span> <span>Juliett</span> <span>Kilo</span> <span>Lima</span> <span>Mike</span> <span>November</span> <span>Oscar</span> <span>Papa</span> <span>Quebec</span> <span>Romeo</span> <span>Sierra</span> <span>Tango</span> <span>Uniform</span> <span>Victor</span> <span>Whiskey</span> <span>Xray</span> <span>Yankee</span> <span>Zulu</span></td>
	</tr>
	<tr>
		<td align="right"><span>1R</span> <span>Alfa</span> <span>Bravo</span> <span>Charlie</span> <span>Delta</span> <span>Echo</span> <span>Foxtrot</span> <span>Golf</span> <span>Hotel</span> <span>India</span> <span>Juliett</span> <span>Kilo</span> <span>Lima</span> <span>Mike</span> <span>November</span> <span>Oscar</span> <span>Papa</span> <span>Quebec</span> <span>Romeo</span> <span>Sierra</span> <span>Tango</span> <span>Uniform</span> <span>Victor</span> <span>Whiskey</span> <span>Xray</span> <span>Yankee</span> <span>Zulu</span></td>
	</tr>
	<tr>
		<td align="left"><span>2L</span> A1 ex<i>amp</i>le <a href="https://tcpdf.org">link</a> column span. Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray Yankee Zulu.</td>
	</tr>
	<tr>
		<td align="center"><span>2C</span> A1 ex<i>amp</i>le <a href="https://tcpdf.org">link</a> column span. Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray Yankee Zulu.</td>
	</tr>
	<tr>
		<td align="right"><span>2R</span> A1 ex<i>amp</i>le <a href="https://tcpdf.org">link</a> column span. Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray Yankee Zulu.</td>
	</tr>
	<tr>
		<td align="left"><small>3L small text</small> Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray Yankee Zulu</td>
	</tr>
	<tr>
		<td align="center"><small>3C small text</small> Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray Yankee Zulu</td>
	</tr>
	<tr>
		<td align="right"><small>3R small text</small> Alfa Bravo Charlie Delta Echo Foxtrot Golf Hotel India Juliett Kilo Lima Mike November Oscar Papa Quebec Romeo Sierra Tango Uniform Victor Whiskey Xray Yankee Zulu</td>
	</tr>
</table>

<hr />

<table border="1" cellspacing="3" cellpadding="4">
	<tr>
		<td align="left"><img src="images/tcpdf_logo.jpg" alt="TCPDF logo" width="60" height="20" border="0" /></td>
	</tr>
	<tr>
		<td align="center"><img src="images/tcpdf_logo.jpg" alt="TCPDF logo" width="60" height="20" border="0" /></td>
	</tr>
	<tr>
		<td align="right"><img src="images/tcpdf_logo.jpg" alt="TCPDF logo" width="60" height="20" border="0" /></td>
	</tr>
</table>';

$pdf->addHTMLCell(
    $html4,
    20,
    10,
    180,
);

// ----------

// =============================================================

// ----------
// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// ----------

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF($rawpdf);
//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
