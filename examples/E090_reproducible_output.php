<?php

declare(strict_types=1);

/**
 * E090_reproducible_output.php
 *
 * Reproducible output: pinned document dates, file identifier and page times.
 * The pages added by a page break take the document modification date, so the
 * same input always produces the same bytes. The last page lists the time of
 * every page next to the expected one.
 *
 * @since       2026-10-09
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: local file reads (images, fonts, attachments) are restricted to an allowlist of
// trusted paths that covers this package tree, so run the examples in place. To read assets
// from other locations, list them in the 'allowedPaths' entry of the fileOptions constructor
// parameter (see E047_remote_resources_security.php).

// NOTE: run make fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf();

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 090');
$pdf->setTitle('Reproducible Output');
$pdf->setKeywords('TCPDF tc-lib-pdf example reproducible output page time file id');
$pdf->setPDFFilename('090_reproducible_output.pdf');

// Pinned values: the modification date is also the time of the pages added without one.
$created = 1_600_000_000; // 2020-09-13 12:26:40 UTC
$modified = 1_700_000_000; // 2023-11-14 22:13:20 UTC
$firstPage = 1_650_000_000; // 2022-04-15 05:20:00 UTC
$pdf->setDocCreationDate($created);
$pdf->setDocModificationDate($modified);
$pdf->setFileId('tc-lib-pdf-example-090');

$font = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

// The first page has an explicit time.
$pdf->addPage(['time' => $firstPage]);
$pdf->page->addContent($font['out']);

$html =
    '<h1>Reproducible output</h1>'
    . '<p>The creation date, the modification date and the file identifier are pinned. The first page has'
    . ' an explicit time; the pages added by the page breaks below take the modification date.</p>';
for ($par = 1; $par <= 24; ++$par) {
    $html .=
        '<p>Paragraph '
        . $par
        . '. '
        . \str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 8)
        . '</p>';
}

$pdf->addHTMLCell(html: $html, posx: 15, posy: 15, width: 180);

// A page with the epoch as explicit time.
$pdf->addPage(['time' => 0]);
$pdf->page->addContent($font['out']);
$pdf->addHTMLCell(
    html: '<p>This page has the explicit time 0 (1970-01-01 00:00:00 UTC).</p>',
    posx: 15,
    posy: 15,
    width: 180,
);

// Summary page: the time of every page next to the expected one.
$pdf->addPage();
$pdf->page->addContent($font['out']);

$pages = $pdf->page->getPages();
$lastpid = \count($pages) - 1;
$rows = '';
foreach ($pages as $pid => $page) {
    $expected = match (true) {
        $pid === 0 => $firstPage,
        $pid === ($lastpid - 1) => 0,
        default => $modified,
    };
    $color = $page['time'] === $expected ? '#007700' : '#cc0000';
    $rows .=
        '<tr><td>'
        . ($pid + 1)
        . '</td>'
        . '<td style="color:'
        . $color
        . ';">'
        . \gmdate('Y-m-d H:i:s', $page['time'])
        . '</td>'
        . '<td>'
        . \gmdate('Y-m-d H:i:s', $expected)
        . '</td></tr>';
}

$pdf->addHTMLCell(
    html: '<h2>Page times (UTC)</h2>'
    . '<table border="1" cellpadding="2"><tr><th width="20%">page</th><th width="40%">/LastModified</th>'
    . '<th width="40%">expected</th></tr>'
    . $rows
    . '</table>'
    . '<p>Running this example twice produces identical files (for example, compare them with cmp).</p>',
    posx: 15,
    posy: 15,
    width: 180,
);

// ----------

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
