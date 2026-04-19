<?php
/**
 * 0004_example_page_formats.php
 *
 * @since       2026-04-19
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

\define('OUTPUT_FILE', \realpath(__DIR__ . '/../target') . '/example_page_formats.pdf');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm', // string $unit = 'mm',
    true, // bool $isunicode = true,
    false, // bool $subsetfont = false,
    false, // bool $compress = true,
    '', // string $mode = '',
    null, // ?ObjEncrypt $objEncrypt = null,
);

// ----------

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('John Doe');
$pdf->setSubject('tc-lib-pdf page formats example');
$pdf->setTitle('Page Formats Example');
$pdf->setKeywords('TCPDF tc-lib-pdf page formats example');
$pdf->setPDFFilename('example_page_formats.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent(false);

// ----------
// Get all page formats from the Format class
$formats = \Com\Tecnick\Pdf\Page\Format::FORMAT;

// Insert monospace font
$bfont = $pdf->font->insert($pdf->pon, 'courier', '', 8);

// Conversion factor: points to mm and inches
$mmRatio = \Com\Tecnick\Pdf\Page\Format::UNITRATIO['mm'];
$inRatio = \Com\Tecnick\Pdf\Page\Format::UNITRATIO['in'];

// Loop through all formats
foreach ($formats as $formatName => $dimensions) {
    // Add a new page using the named format.
    $page = $pdf->addPage(['format' => $formatName]);

    $pdf->graph->setPageWidth($page['width']);
    $pdf->graph->setPageHeight($page['height']);

    // Get dimensions.
    $widthPt = $dimensions[0];
    $heightPt = $dimensions[1];

    // Calculate dimensions in mm and inches.
    $widthMm = $widthPt / $mmRatio;
    $heightMm = $heightPt / $mmRatio;
    $widthIn = $widthPt / $inRatio;
    $heightIn = $heightPt / $inRatio;

    // Print multiline text at top-left corner.
    $x = 2;
    $y = 2;
    $w = 50;

    // Define multiline HTML text to display.
    $html = '<h2>'.$formatName.'</h2>';

    if ($page['width'] > $w + (2*$x)) {
        $html .= '<table border="1" cellspacing="0" cellpadding="2" style="text-align:right">
        <tr><th>unit</th><th>width</th><th>height</th></tr>';
        $html .= \sprintf('<tr><td>mm</td><td>%.3f</td><td>%.3f</td></tr>', $widthMm, $heightMm);
        $html .= \sprintf('<tr><td>in</td><td>%.3f</td><td>%.3f</td></tr>', $widthIn, $heightIn);
        $html .= \sprintf('<tr><td>pt</td><td>%.3f</td><td>%.3f</td></tr>', $widthPt, $heightPt);
        $html .= "</table>";
    }



    $pdf->page->addContent($bfont['out']);
    $pdf->page->addContent($pdf->getHTMLCell($html, $x, $y, $w));
}

// =============================================================

// ----------
// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// ----------

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__) . '/target', $rawpdf);
$pdf->renderPDF($rawpdf);
//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
