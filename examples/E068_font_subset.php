<?php
/**
 * E068_font_subset.php
 *
 * @since       2026-05-04
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

// main TCPDF object with font subsetting enabled
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm', // string $unit = 'mm',
    true, // bool $isunicode = true,
    true, // bool $subsetfont = true, enables font subsetting
    true, // bool $compress = true,
    '', // string $mode = '',
    null, // ?ObjEncrypt $objEncrypt = null,
);

// Insert DejaVu Sans font for subsetting test
$bfont = $pdf->font->insert($pdf->pon, 'dejavusans', '', 12);

// Add first page
$page = $pdf->addPage();

// Add font to the page
$pdf->page->addContent($bfont['out']);

// HTML content demonstrating varied character sets for subsetting
$html = <<<'EOD'
<h1>Font Subsetting Example</h1>
<p>This document demonstrates <b>font subsetting</b> with DejaVu Sans font.</p>
<p>Font subsetting reduces the file size by including only the characters 
that are actually used in the document, rather than embedding the entire font.</p>

<h2>Character Sets</h2>
<p><b>English:</b> THE QUICK BROWN FOX JUMPS OVER THE LAZY DOG - the quick brown fox jumps over the lazy dog.</p>
<p><b>Numbers:</b> 0123456789 · π ≈ 3.14159</p>
<p><b>Punctuation:</b> !@#$%^&*()[]{}–—«»‹›„“”‚‘’"'</p>
<p><b>Symbols:</b> © ® ™ € £ ¥ ¢ § ¶ • ‰</p>

<h2>Benefits of Font Subsetting</h2>
<ul>
<li>Reduced PDF file size</li>
<li>Faster document transmission</li>
<li>Only embedded characters are included</li>
<li>Maintains font fidelity for used characters</li>
</ul>

<h2>Text Samples</h2>
<p>Regular text in DejaVu Sans font for testing character coverage.</p>
<p><b>Bold text</b> is also included in the subset.</p>
EOD;

// render the HTML content
$pdf->addHTMLCell($html, 15, 15, 180);

// Get the PDF content
$rawpdf = $pdf->getOutPDFString();

// Render the PDF content
$pdf->renderPDF($rawpdf);
