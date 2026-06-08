<?php

/**
 * E047_remote_resources_security.php
 *
 * Demonstrates fileOptions host allowlisting and remote fetch controls while
 * keeping the default run deterministic (no network dependency required).
 *
 * @since       2026-05-01
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

require __DIR__ . '/../vendor/autoload.php';

define('K_PATH_FONTS', (string) realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$imgdir = (string) realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-image/test/images');
$allowedPaths = array_values(array_unique(array_filter([
    K_PATH_FONTS,
    $imgdir,
])));

$curlopts = [];
$fixedCurlOpts = [];

if (defined('CURLOPT_CONNECTTIMEOUT')) {
    $curlopts[CURLOPT_CONNECTTIMEOUT] = 4;
}

if (defined('CURLOPT_TIMEOUT')) {
    $curlopts[CURLOPT_TIMEOUT] = 8;
}

if (defined('CURLOPT_SSL_VERIFYHOST')) {
    $fixedCurlOpts[CURLOPT_SSL_VERIFYHOST] = 2;
}

if (defined('CURLOPT_SSL_VERIFYPEER')) {
    $fixedCurlOpts[CURLOPT_SSL_VERIFYPEER] = true;
}

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    isunicode: true,
    subsetfont: false,
    compress: true,
    mode: '',
    objEncrypt: null,
    fileOptions: [
        'allowedHosts' => ['assets.example.com'],
        'allowedPaths' => $allowedPaths,
        'maxRemoteSize' => 1024 * 1024,
        'curlopts' => $curlopts,
        'fixedCurlOpts' => $fixedCurlOpts,
    ],
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 047');
$pdf->setTitle('Remote Resource Security');
$pdf->setKeywords('TCPDF tc-lib-pdf example remote resources fileOptions security');
$pdf->setPDFFilename('047_remote_resources_security.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);
$pdf->enableDefaultPageContent();

$titlefont = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 14);
$textfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);
$smallfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 8);

$page = $pdf->addPage(['format' => 'A4']);

$pdf->page->addContent($titlefont['out']);
$pdf->page->addContent($pdf->getTextCell(
    txt: 'Remote Resources and fileOptions Security',
    posx: 15,
    posy: 14,
    width: 180,
    height: 0,
    offset: 0,
    linespace: 1,
    valign: 'T',
    halign: 'L',
));

$pdf->page->addContent($textfont['out']);
$pdf->page->addContent($pdf->getTextCell(
    txt: "This example configures host allowlisting, local path allowlisting, and transfer limits.\n"
    . "It renders local assets by default for deterministic runs.\n"
    . 'Optional remote probe can be enabled with ?probe=1 in browser mode.',
    posx: 15,
    posy: 26,
    width: 180,
    height: 0,
    offset: 0,
    linespace: 1.3,
    valign: 'T',
    halign: 'L',
));

$localImage = $imgdir . '/200x100_RGB.png';
$statusY = 60.0;

try {
    $iid = $pdf->image->add($localImage);
    $pdf->page->addContent($pdf->image->getSetImage($iid, 15, 54, 70, 35, (float) $page['height']));
    $localStatus = 'Local image load: OK (' . basename($localImage) . ')';
} catch (\Throwable $e) {
    $localStatus = 'Local image load: FAILED - ' . $e->getMessage();
}

$pdf->page->addContent($smallfont['out']);
$pdf->page->addContent($pdf->getTextCell(
    txt: $localStatus,
    posx: 90,
    posy: $statusY,
    width: 105,
    height: 0,
    offset: 0,
    linespace: 1,
    valign: 'T',
    halign: 'L',
));

$probe = false;
if (PHP_SAPI !== 'cli') {
    $probe = isset($_GET['probe']) && $_GET['probe'] === '1';
}

$remoteStatus = 'Remote probe: skipped (add ?probe=1 to run a blocked-host fetch attempt).';
if ($probe) {
    try {
        $pdf->image->add('https://untrusted.example.invalid/logo.png');
        $remoteStatus = 'Remote probe: request did not raise an exception (check your environment wrappers).';
    } catch (\Throwable $e) {
        $remoteStatus = 'Remote probe: blocked/failure as expected - ' . $e->getMessage();
    }
}

$pdf->page->addContent($pdf->getTextCell(
    txt: $remoteStatus,
    posx: 15,
    posy: 96,
    width: 180,
    height: 0,
    offset: 0,
    linespace: 1.2,
    valign: 'T',
    halign: 'L',
));

$pdf->page->addContent($textfont['out']);
$pdf->page->addContent($pdf->getTextCell(
    txt: "Configured policy:\n"
    . "- allowedHosts: assets.example.com\n"
    . "- allowedPaths: fonts + image fixture directories\n"
    . "- maxRemoteSize: 1 MiB\n"
    . "- curlopts: short connect/transfer timeout\n"
    . '- fixedCurlOpts: TLS peer+host verification',
    posx: 15,
    posy: 112,
    width: 180,
    height: 0,
    offset: 0,
    linespace: 1.35,
    valign: 'T',
    halign: 'L',
));

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF(rawpdf: $rawpdf);
