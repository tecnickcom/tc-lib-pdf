<?php
/**
 * E045_encryption_and_permissions.php
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

$fileId = \md5('E045_encryption_and_permissions');
$encrypt = new \Com\Tecnick\Pdf\Encrypt\Encrypt(
    true,
    $fileId,
    2,
    ['modify', 'copy', 'annot-forms', 'assemble'],
    'demo-user',
    'demo-owner'
);

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm', // string $unit = 'mm',
    true, // bool $isunicode = true,
    false, // bool $subsetfont = false,
    true, // bool $compress = true,
    '', // string $mode = '',
    $encrypt, // ?ObjEncrypt $objEncrypt = null,
);

// ----------


$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 045');
$pdf->setTitle('Encryption and Permissions');
$pdf->setKeywords('TCPDF tc-lib-pdf encryption permissions user password owner password rights');
$pdf->setPDFFilename('E045_encryption_and_permissions.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

$page01 = $pdf->addPage();
$pdf->page->addContent($bfont['out']);

$html =  <<<HTML
<h1>Encryption and Permissions</h1>
<p>This document demonstrates PDF encryption and permission controls using tc-lib-pdf.
The file is protected with a user password (<em>demo-user</em>) and an owner password (<em>demo-owner</em>).
Encryption restricts unauthorized access while the owner password grants full control.
The allowed permissions for this document are: <strong>modify</strong>, <strong>copy</strong>,
<strong>annot-forms</strong>, and <strong>assemble</strong>.
All other operations, such as printing, are denied.</p>
HTML;

$pdf->addHTMLCell(
    $html,
    20,
    10,
    180, 
);

// ----------

// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// Various output modes:

//$pdf->savePDF(\dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF($rawpdf);
//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
