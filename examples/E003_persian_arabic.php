<?php

/**
 * E003_persian_arabic.php
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
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    isunicode: true,
    subsetfont: false,
    compress: false,
    mode: '',
    objEncrypt: null,
);

// ----------

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 003');
$pdf->setTitle('Persian and Arabic Text Rendering');
$pdf->setKeywords('TCPDF tc-lib-pdf persian arabic rtl bidi unicode shaping');
$pdf->setPDFFilename('003_persian_arabic.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent(false);

$bfont = $pdf->font->insert($pdf->pon, 'unifont', '', 14);

$page = $pdf->addPage(['format' => 'A4']);

$pdf->page->addContent($bfont['out']);

$html = '<h1>RTL</h1>';

$html .= '<div style="text-align:right"><span color="#660000">Persian example:</span><br />سلام بالاخره مشکل PDF فارسی به طور کامل حل شد. اینم یک نمونش.<br />مشکل حرف \"ژ\" در بعضی کلمات مانند کلمه ویژه نیز بر طرف شد.<br />نگارش حروف لام و الف پشت سر هم نیز تصحیح شد.<br />با تشکر از  "Asuni Nicola" و محمد علی گل کار برای پشتیبانی زبان فارسی.</div><hr />';

$html .= '<div><span color="#0000ff">Hi, At last Problem of Persian PDF Solved completely. This is a example for it.<br />Problem of "jeh" letter in some word like "ویژه" (=special) fix too.<br />The joining of laa and alf letter fix now.<br />Special thanks to "Nicola Asuni" and "Mohamad Ali Golkar" for Persian support.</span></div><hr />';

$html .= '<div style="text-align:right">بِسْمِ اللهِ الرَّحْمنِ الرَّحِيمِ</div><hr />';

$html .=
    '<div style="text-align:right">'
    . 'تمَّ بِحمد الله حلّ مشكلة الكتابة باللغة العربية في ملفات الـ<span color="#FF0000">PDF</span> مع دعم الكتابة <span color="#0000FF">من اليمين إلى اليسار</span> و<span color="#009900">الحركَات</span> .<br />تم الحل بواسطة <span color="#993399">صالح المطرفي و Asuni Nicola</span>  . '
    . '</div><hr />';

$html .= '<div color="#0000ff">This is Arabic "العربية" Example With TCPDF.</div><hr />';

// The page above keeps the document base direction LTR and relies on
// text-align:right plus the bidi algorithm to lay out the Arabic text.
$pdf->addHTMLCell(html: $html, posx: 15, posy: 15, width: 180);

// =============================================================
// Document base direction RTL: setRTL(true)
$pdf->setRTL(true);

// Plain-text form of the paragraph (no markup) for the text API.
$arabicText =
    'تمَّ بِحمد الله حلّ مشكلة الكتابة باللغة العربية في ملفات الـPDF ' . 'مع دعم الكتابة من اليمين إلى اليسار والحركَات .';

// HTML form of the same paragraph (with inline colored spans) for the HTML API.
$arabicHtml =
    '<div style="text-align:right">'
    . 'تمَّ بِحمد الله حلّ مشكلة الكتابة باللغة العربية في ملفات الـ<span color="#FF0000">PDF</span>'
    . ' مع دعم الكتابة <span color="#0000FF">من اليمين إلى اليسار</span>'
    . ' و<span color="#009900">الحركَات</span> .'
    . '</div>';

$pdf->page->addContent($pdf->graph->add(['fillColor' => 'black']));

// --- Text variant (addTextCell) ---
$pdf->addTextCell(
    txt: 'setRTL(true) — text variant (addTextCell):',
    posx: 15,
    posy: 140,
    width: 180,
    halign: 'L',
    drawcell: false,
);
$pdf->addTextCell(txt: $arabicText, posx: 15, posy: 150, width: 180, drawcell: false);

// --- HTML variant (addHTMLCell) ---
$pdf->addTextCell(
    txt: 'setRTL(true) — HTML variant (addHTMLCell):',
    posx: 15,
    posy: 170,
    width: 180,
    halign: 'L',
    drawcell: false,
);
$pdf->addHTMLCell(html: $arabicHtml, posx: 15, posy: 180, width: 180);

// =============================================================

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF(rawpdf: $rawpdf);
