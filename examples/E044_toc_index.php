<?php
/**
 * E044_toc_index.php
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
$pdf->setSubject('tc-lib-pdf example: 044');
$pdf->setTitle('Table of Contents and Index');
$pdf->setKeywords('TCPDF tc-lib-pdf toc index bookmarks pagination');
$pdf->setPDFFilename('044_toc_index.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont1 = $pdf->font->insert($pdf->pon, 'dejavusans', '', 12);
$bfont2 = $pdf->font->insert($pdf->pon, 'dejavusans', 'B', 18);
$bfont3 = $pdf->font->insert($pdf->pon, 'dejavusans', '', 10);


// test images directory
$imgdir = \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-image/test/images/');


// ----------

$sections = [
    [
        'title' => 'Introduction',
        'summary' => 'This page introduces the outline entries that will be collected by addTOC into a generated table of contents.',
        'topic' => 'The top-level bookmark uses the same explicit style arguments shown in the overview example.',
        'color' => 'blue',
    ],
    [
        'title' => 'Planning',
        'summary' => 'This page adds another destination so the table of contents can render a second page number.',
        'topic' => 'A second level bookmark is also added on each page to show indented TOC entries.',
        'color' => 'green',
    ],
    [
        'title' => 'Execution',
        'summary' => 'This page stands in for the main body of the document and contributes more outline targets.',
        'topic' => 'Using separate pages makes the addTOC output easier to verify visually.',
        'color' => 'red',
    ],
    [
        'title' => 'Results',
        'summary' => 'This last content page completes the sample before the dedicated TOC page is inserted.',
        'topic' => 'The final PDF contains five pages total: four content pages and one generated TOC page.',
        'color' => 'orange',
    ],
];

foreach ($sections as $index => $section) {
    $page = $pdf->addPage();
    $pdf->setBookmark($section['title'], '', 0, -1, 0, 0, 'B', $section['color']);
    $pdf->setBookmark($section['title'] . ' notes', '', 1, -1, 0, 0, '', 'black');

    $pdf->page->addContent($bfont2['out'], $page['pid']);
    $pdf->page->addContent(
        $pdf->getTextCell('Example 044 - ' . $section['title'], 15, 20, 180, 0, 0, 1, 'T', 'L'),
        $page['pid']
    );

    $pdf->page->addContent($bfont3['out'], $page['pid']);
    $pdf->page->addContent(
        $pdf->getTextCell('Page ' . ($index + 1) . ' of 4', 15, 34, 180, 0, 0, 1, 'T', 'L'),
        $page['pid']
    );
    $pdf->page->addContent(
        $pdf->getTextCell($section['summary'], 15, 48, 180, 0, 0, 1, 'T', 'L'),
        $page['pid']
    );
    $pdf->page->addContent(
        $pdf->getTextCell($section['topic'], 15, 62, 180, 0, 0, 1, 'T', 'L'),
        $page['pid']
    );
}

// ----------

$pageTOC = $pdf->addPage();
$pdf->setBookmark('TOC', '', 0, -1, 0, 0, 'B', 'black');

$pdf->page->addContent($bfont2['out'], $pageTOC['pid']);
$pdf->page->addContent(
    $pdf->getTextCell('Table of contents', 15, 18, 180, 0, 0, 1, 'T', 'L'),
    $pageTOC['pid']
);
$pdf->page->addContent($bfont1['out'], $pageTOC['pid']);

$pdf->setDefaultCellMargin(0,0,0,0);
$pdf->setDefaultCellPadding(1,1,1,1);

$style_cell_toc = [
    'all' => [
        'lineWidth' => 0,
        'lineCap' => 'round',
        'lineJoin' => 'round',
        'miterLimit' => 0,
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => '',
    ],
];

$pdf->graph->add($style_cell_toc);

$pdf->addTOC(-1, 15, 30, 170, false);


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
