<?php

/**
 * E049_output_targets_integration.php
 *
 * Demonstrates routing one generated PDF to multiple output targets:
 * render, download, save, and MIME attachment payload.
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

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    isunicode: true,
    subsetfont: false,
    compress: true,
    mode: '',
    objEncrypt: null,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 049');
$pdf->setTitle('Output Targets Integration');
$pdf->setKeywords('TCPDF tc-lib-pdf example output render download save mime');
$pdf->setPDFFilename('049_output_targets_integration.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);
$pdf->enableDefaultPageContent();

$titlefont = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 14);
$textfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$pdf->addPage(['format' => 'A4']);

$mode = 'render';
if (PHP_SAPI === 'cli') {
    $mode = (string) ($argv[1] ?? 'render');
} elseif (isset($_GET['mode']) && is_string($_GET['mode'])) {
    $mode = $_GET['mode'];
}

$mode = strtolower(trim($mode));
$allowedModes = ['render', 'download', 'save', 'mime'];
if (!in_array($mode, $allowedModes, true)) {
    $mode = 'render';
}

$pdf->page->addContent($titlefont['out']);
$pdf->page->addContent($pdf->getTextCell(
    txt: 'Output Targets Integration',
    posx: 15,
    posy: 16,
    width: 180,
    height: 0,
    offset: 0,
    linespace: 1,
    valign: 'T',
    halign: 'L',
));

$pdf->page->addContent($textfont['out']);
$pdf->page->addContent($pdf->getTextCell(
    txt: "This example creates one raw PDF payload and sends it through different targets.\n"
    . 'Current mode: '
    . strtoupper($mode)
    . "\n"
    . "Browser usage: ?mode=render|download|save|mime\n"
    . 'CLI usage: php examples/E049_output_targets_integration.php render',
    posx: 15,
    posy: 30,
    width: 180,
    height: 0,
    offset: 0,
    linespace: 1.35,
    valign: 'T',
    halign: 'L',
));

$rawpdf = $pdf->getOutPDFString();

if ($mode === 'download') {
    $pdf->downloadPDF(rawpdf: $rawpdf);
    exit();
}

if ($mode === 'save') {
    $targetDir = (string) realpath(__DIR__ . '/../target');
    $pdf->savePDF(path: $targetDir, rawpdf: $rawpdf);

    if (PHP_SAPI !== 'cli') {
        header('Content-Type: text/plain; charset=utf-8');
    }

    echo 'Saved PDF to: ' . $targetDir . '/' . '049_output_targets_integration.pdf' . "\n";
    exit();
}

if ($mode === 'mime') {
    $mimeAttachment = $pdf->getMIMEAttachmentPDF(rawpdf: $rawpdf);

    if (PHP_SAPI !== 'cli') {
        header('Content-Type: text/plain; charset=utf-8');
    }

    echo "MIME attachment preview (first 20 lines):\n\n";
    $lines = explode("\n", str_replace("\r\n", "\n", $mimeAttachment));
    echo implode("\n", array_slice($lines, 0, 20));
    echo "\n\nTotal MIME length: " . strlen($mimeAttachment) . " bytes\n";
    exit();
}

$pdf->renderPDF(rawpdf: $rawpdf);
