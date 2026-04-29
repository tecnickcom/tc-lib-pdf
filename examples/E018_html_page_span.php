<?php
/**
 * E018_html_page_span.php
 *
 * @since       2026-04-25
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
$pdf->setSubject('tc-lib-pdf example: 018');
$pdf->setTitle('Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example');
$pdf->setPDFFilename('018_html_page_span.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont1 = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);


// test images directory
$imgdir = \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-image/test/images/');


$pageV01 = $pdf->addPage();

$bfont6 = $pdf->font->insert($pdf->pon, 'dejavusans', '', 10);

$pdf->page->addContent($bfont6['out']);


$html = 'Some special characters: &lt; € &euro; &#8364; &amp; è &egrave; &copy; &gt; \\slash \\\\double-slash \\\\\\triple-slash
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
    '<h1>HTML page break example</h1>'.$html.$html.$html.$html, // string $html,
    20, // float $posx = 0,
    10, // float $posy = 0,
    150, // float $width = 0,
);

// =============================================================
// Styled block (background + border) spanning multiple pages.

$blockchunk = '<p>'
    . 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium '
    . 'doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore '
    . 'veritatis et quasi architecto beatae vitae dicta sunt explicabo.'
    . '</p>';

$blockhtml = '<h2>Styled block across pages</h2>'
    . '<div style="background-color:#ffeeaa;border:1px solid #888;padding:4px">'
    . '<p><b>Block-level container</b> &mdash; the background and border continue '
    . 'on each page until the content ends.</p>'
    . \str_repeat($blockchunk, 30)
    . '</div>';

$pdf->addHTMLCell(
    $blockhtml,
    20, // float $posx
    100, // float $posy
    150, // float $width
);

// =============================================================
// Table spanning multiple pages with header row replay.

$tableRows = '';
for ($i = 1; $i <= 20; ++$i) {
    $tableRows .= '<tr>'
        . '<td>' . $i . '</td>'
        . '<td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>'
        . '<td style="text-align:right">' . \number_format($i * 12.34, 2) . '</td>'
        . '</tr>';
}

$tablehtml = '<h2>Table across pages</h2>'
    . '<table border="1" cellpadding="3" cellspacing="0">'
    . '<thead>'
    . '<tr style="background-color:#cccccc">'
    . '<th>#</th><th>Description</th><th>Amount</th>'
    . '</tr>'
    . '</thead>'
    . $tableRows
    . '</table>';

$pdf->addHTMLCell(
    $tablehtml,
    20, // float $posx
    120, // float $posy
    150, // float $width
);

// =============================================================

// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF($rawpdf);
//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
