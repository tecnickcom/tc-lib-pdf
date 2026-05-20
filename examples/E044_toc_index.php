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
require __DIR__ . '/../vendor/autoload.php';

\define('OUTPUT_FILE', \realpath(__DIR__ . '/../target') . '/example.pdf');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    isunicode: true,
    subsetfont: false,
    compress: true,
    mode: '',
    objEncrypt: null,
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
    $pdf->setBookmark(
        name: $section['title'],
        link: '',
        level: 0,
        page: -1,
        posx: 0,
        posy: 0,
        fstyle: 'B',
        color: $section['color'],
    );
    $pdf->setBookmark(
        name: $section['title'] . ' notes',
        link: '',
        level: 1,
        page: -1,
        posx: 0,
        posy: 0,
        fstyle: '',
        color: 'black',
    );

    $pdf->page->addContent($bfont2['out'], $page['pid']);
    $pdf->page->addContent(
        $pdf->getTextCell(
            txt: 'Example 044 - ' . $section['title'],
            posx: 15,
            posy: 20,
            width: 180,
            height: 0,
            offset: 0,
            linespace: 1,
            valign: 'T',
            halign: 'L',
        ),
        $page['pid'],
    );

    $pdf->page->addContent($bfont3['out'], $page['pid']);
    $pdf->page->addContent(
        $pdf->getTextCell(
            txt: 'Page ' . ($index + 1) . ' of 4',
            posx: 15,
            posy: 34,
            width: 180,
            height: 0,
            offset: 0,
            linespace: 1,
            valign: 'T',
            halign: 'L',
        ),
        $page['pid'],
    );
    $pdf->page->addContent(
        $pdf->getTextCell(
            txt: $section['summary'],
            posx: 15,
            posy: 48,
            width: 180,
            height: 0,
            offset: 0,
            linespace: 1,
            valign: 'T',
            halign: 'L',
        ),
        $page['pid'],
    );
    $pdf->page->addContent(
        $pdf->getTextCell(
            txt: $section['topic'],
            posx: 15,
            posy: 62,
            width: 180,
            height: 0,
            offset: 0,
            linespace: 1,
            valign: 'T',
            halign: 'L',
        ),
        $page['pid'],
    );
}

// ----------

$pageTOC = $pdf->addPage();
$pdf->setBookmark(name: 'TOC', link: '', level: 0, page: -1, posx: 0, posy: 0, fstyle: 'B', color: 'black');

$pdf->page->addContent($bfont2['out'], $pageTOC['pid']);
$pdf->page->addContent(
    $pdf->getTextCell(
        txt: 'Table of contents',
        posx: 15,
        posy: 18,
        width: 180,
        height: 0,
        offset: 0,
        linespace: 1,
        valign: 'T',
        halign: 'L',
    ),
    $pageTOC['pid'],
);
$pdf->page->addContent($bfont1['out'], $pageTOC['pid']);

$pdf->setDefaultCellMargin(top: 0, right: 0, bottom: 0, left: 0);
$pdf->setDefaultCellPadding(top: 1, right: 1, bottom: 1, left: 1);

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

$pdf->addTOC(page: -1, posx: 15, posy: 30, width: 170, rtl: false);

// =============================================================

// ----------
// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// ----------

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF(rawpdf: $rawpdf);

//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
