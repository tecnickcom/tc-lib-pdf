<?php
/**
 * E069_html_line_height.php
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
$pdf->setSubject('tc-lib-pdf example: 069');
$pdf->setTitle('HTML Line Height');
$pdf->setKeywords('TCPDF tc-lib-pdf html css line-height typography spacing');
$pdf->setPDFFilename('069_html_line_height.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// Insert DejaVu Sans font for subsetting test
$bfont = $pdf->font->insert($pdf->pon, 'dejavusans', '', 12);

// Add first page
$page = $pdf->addPage();

// Add font to the page
$pdf->page->addContent($bfont['out']);

$html = <<<EOF
<h1>Line-Height CSS Tests</h1>
<h2>1) Line-height with percentages</h2>
<p style="line-height: 100%;">
Line-height: 100%<br />
This paragraph uses a line-height of 100%, which is the same as the font size.
Each line should be tightly spaced without extra vertical space.
This is useful when you want compact text layout.
</p>

<p style="line-height: 150%;">
Line-height: 150%<br />
This paragraph uses a line-height of 150%, which adds moderate spacing between lines.
The lines are more readable and have better visual separation.
This is a common default setting for body text in many documents.
</p>

<p style="line-height: 200%;">
Line-height: 200%<br />
This paragraph uses a line-height of 200%, which doubles the font size for vertical spacing.
The lines have significant space between them, making the text very readable.
This is often used for accessibility or when line spacing is deliberately increased.
</p>

<h2>2) Line-height with absolute units</h2>
<p style="line-height: 10pt;">
Line-height: 10pt<br />
This paragraph uses an absolute line-height of 10 points.
The spacing between lines is fixed at exactly 10 points.
The font size remains 10pt, so this creates tightly packed lines.
</p>

<p style="line-height: 15pt;">
Line-height: 15pt<br />
This paragraph uses an absolute line-height of 15 points.
Each line has exactly 15 points of vertical space allocated.
This provides moderate spacing for improved readability.
</p>

<p style="line-height: 20pt;">
Line-height: 20pt<br />
This paragraph uses an absolute line-height of 20 points.
The lines are well-spaced with 20 points of vertical space each.
This creates a more spacious and airy appearance in the document.
</p>

<h2>3) Line-height with relative units (em)</h2>
<p style="line-height: 0.8em;">
Line-height: 0.8em<br />
This paragraph uses a line-height of 0.8em, which is 80% of the current font size.
The lines are compressed together with minimal vertical space.
This is useful for creating a compact visual effect.
</p>

<p style="line-height: 1.2em;">
Line-height: 1.2em<br />
This paragraph uses a line-height of 1.2em, which is 120% of the current font size.
The lines have a balanced amount of spacing for good readability.
This is similar to using a 120% line-height percentage.
</p>

<p style="line-height: 2em;">
Line-height: 2em<br />
This paragraph uses a line-height of 2em, which is 200% of the current font size.
The lines are widely separated with generous vertical spacing.
This creates a very open and spacious text layout.
</p>

<h2>4) Line-height with unitless numbers</h2>
<p style="line-height: 0.9;">
Line-height: 0.9 (unitless)<br />
This paragraph uses a unitless line-height value of 0.9.
The multiplier is applied to the font size (0.9 × 10pt = 9pt).
This creates compact line spacing.
</p>

<p style="line-height: 1.5;">
Line-height: 1.5 (unitless)<br />
This paragraph uses a unitless line-height value of 1.5.
The multiplier is applied to the font size (1.5 × 10pt = 15pt).
This is a very popular setting for comfortable reading.
The spacing is proportional to the font size.
</p>

<p style="line-height: 2.5;">
Line-height: 2.5 (unitless)<br />
This paragraph uses a unitless line-height value of 2.5.
The multiplier is applied to the font size (2.5 × 10pt = 25pt).
The lines are very widely spaced and easy to read.
This is often used for accessibility requirements.
</p>

<h2>5) Line-height with millimeters</h2>
<p style="line-height: 5mm;">
Line-height: 5mm<br />
This paragraph uses an absolute line-height of 5 millimeters.
The vertical spacing between lines is exactly 5mm.
Different units like millimeters are sometimes used in print design.
</p>

<p style="line-height: 8mm;">
Line-height: 8mm<br />
This paragraph uses an absolute line-height of 8 millimeters.
The increased spacing makes the text more readable and open.
Millimeters are useful for precise layout control in documents.
</p>

<h2>6) Comparison: normal vs specific values</h2>
<p style="line-height: normal;">
Line-height: normal<br />
This paragraph uses the default line-height value (typically around 1.0-1.2).
The spacing is determined by the browser or PDF renderer default.
This is the baseline for comparison with other values.
</p>

<p style="line-height: 1;">
Line-height: 1 (unitless)<br />
This paragraph uses a unitless line-height of exactly 1.
The vertical space is the same as the font size.
This creates tightly spaced lines with minimal separation.
</p>
EOF;

$pdf->addHTMLCell(
    $html, // string $html,
    20, // float $posx = 0,
    10, // float $posy = 0,
    150, // float $width = 0,
);

// ----------

// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF($rawpdf);
//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
