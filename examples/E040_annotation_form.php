<?php

/**
 * E040_annotation_form.php
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
$pdf->setSubject('tc-lib-pdf example: 040');
$pdf->setTitle('Annotation Form Widgets');
$pdf->setKeywords('TCPDF tc-lib-pdf annotations form widgets acroform fields');
$pdf->setPDFFilename('040_annotation_form.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont1 = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// test images directory
$imgdir = \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-image/test/images/');

// ----------

// Annotation Form Fields

$page1 = $pdf->addPage();

$bfont1 = $pdf->font->insert($pdf->pon, 'dejavusans', '', 12);
$pdf->page->addContent($bfont1['out']);

$html = <<<HTML
    <h1 style="font-size:16pt; color:#003366;">Annotation Form Example</h1>
    <p style="font-size:9pt; color:#333333;">
    This page demonstrates interactive PDF form fields including text input,
    radio buttons, a checkbox, combo and list boxes, plus reset, print, and
    submit actions.
    </p>
    HTML;

$pdf->addHTMLCell(html: $html, posx: 15, posy: 15, width: 180);

$posx = 80;
$posy = 100;

$labelWidth = 62;
$labelX = 15;
$labelStyle = 'font-size:9pt; color:#333333; text-align:right;';

// text
$pdf->addHTMLCell(
    html: '<p style="' . $labelStyle . '">Text Input:</p>',
    posx: $labelX,
    posy: $posy,
    width: $labelWidth,
    height: 5,
);
$fftextid = $pdf->addFFText('test_text', $posx, $posy, 50, 5);
$pdf->page->addAnnotRef($fftextid);

// radiobuttons
$pdf->addHTMLCell(
    html: '<p style="' . $labelStyle . '">Radio – Option One:</p>',
    posx: $labelX,
    posy: $posy + 10,
    width: $labelWidth,
    height: 5,
);
$pdf->addHTMLCell(
    html: '<p style="' . $labelStyle . '">Radio – Option Two:</p>',
    posx: $labelX,
    posy: $posy + 15,
    width: $labelWidth,
    height: 5,
);
$pdf->addHTMLCell(
    html: '<p style="' . $labelStyle . '">Radio – Option Three:</p>',
    posx: $labelX,
    posy: $posy + 20,
    width: $labelWidth,
    height: 5,
);
$ffrbid1 = $pdf->addFFRadioButton(name: 'test_radiobutton', posx: $posx, posy: $posy + 10, width: 5, onvalue: 'one');
$ffrbid2 = $pdf->addFFRadioButton(
    name: 'test_radiobutton',
    posx: $posx,
    posy: $posy + 15,
    width: 5,
    onvalue: 'two',
    checked: true,
);
$ffrbid3 = $pdf->addFFRadioButton(name: 'test_radiobutton', posx: $posx, posy: $posy + 20, width: 5, onvalue: 'three');
$pdf->page->addAnnotRef($ffrbid1);
$pdf->page->addAnnotRef($ffrbid2);
$pdf->page->addAnnotRef($ffrbid3);

// checkbox
$pdf->addHTMLCell(
    html: '<p style="' . $labelStyle . '">Checkbox:</p>',
    posx: $labelX,
    posy: $posy + 30,
    width: $labelWidth,
    height: 5,
);
$ffckbxid1 = $pdf->addFFCheckBox('test_checkbox', $posx, $posy + 30, 5);
$pdf->page->addAnnotRef($ffckbxid1);

// combobox
$pdf->addHTMLCell(
    html: '<p style="' . $labelStyle . '">Combo Box:</p>',
    posx: $labelX,
    posy: $posy + 40,
    width: $labelWidth,
    height: 5,
);
$ffcmbxid1 = $pdf->addFFComboBox('test_combobox', $posx, $posy + 40, 50, 5, [
    ['0', 'one'],
    ['1', 'two'],
    ['2', 'three'],
]);
$pdf->page->addAnnotRef($ffcmbxid1);

// listbox
$pdf->addHTMLCell(
    html: '<p style="' . $labelStyle . '">List Box:</p>',
    posx: $labelX,
    posy: $posy + 50,
    width: $labelWidth,
    height: 5,
);
$fflsbxid1 = $pdf->addFFListBox(
    'test_listbox',
    $posx,
    $posy + 50,
    50,
    15,
    ['one', 'two', 'three'],
    ['subtype' => 'Widget'],
    ['multipleSelection' => 'true'],
);
$pdf->page->addAnnotRef($fflsbxid1);

// button - reset form
$pdf->addHTMLCell(
    html: '<p style="' . $labelStyle . '">Actions:</p>',
    posx: $labelX,
    posy: $posy + 70,
    width: $labelWidth,
    height: 5,
);
$ffbtnid1 = $pdf->addFFButton(
    name: 'reset',
    posx: $posx,
    posy: $posy + 70,
    width: 20,
    height: 5,
    caption: 'Reset',
    action: ['S' => 'ResetForm'],
    opt: ['subtype' => 'Widget'],
    jsp: [
        'lineWidth' => 2,
        'borderStyle' => 'beveled',
        'fillColor' => '#80c4ff',
        'strokeColor' => '#404040',
    ],
);
$pdf->page->addAnnotRef($ffbtnid1);

// button - print document
$ffbtnid2 = $pdf->addFFButton(
    name: 'print',
    posx: $posx + 20,
    posy: $posy + 70,
    width: 20,
    height: 5,
    caption: 'Print',
    action: 'Print()',
    opt: ['subtype' => 'Widget'],
    jsp: [
        'lineWidth' => 2,
        'borderStyle' => 'beveled',
        'fillColor' => '#80c4ff',
        'strokeColor' => '#404040',
    ],
);
$pdf->page->addAnnotRef($ffbtnid2);

// button - submit form
$ffbtnid3 = $pdf->addFFButton(
    name: 'submit',
    posx: $posx + 40,
    posy: $posy + 70,
    width: 20,
    height: 5,
    caption: 'Submit',
    action: [
        'S' => 'SubmitForm',
        'F' => 'http://localhost/printvars.php',
        'Flags' => ['ExportFormat'],
    ],
    opt: ['subtype' => 'Widget'],
    jsp: [
        'lineWidth' => 2,
        'borderStyle' => 'beveled',
        'fillColor' => '#80c4ff',
        'strokeColor' => '#404040',
    ],
);
$pdf->page->addAnnotRef($ffbtnid3);

// JavaScript form validation functions
$formjs = <<<EOD
    function CheckField(name,message) {
    	var f = getField(name);
    	if(f.value == '') {
    	    app.alert(message);
    	    f.setFocus();
    	    return false;
    	}
    	return true;
    }
    function Print() {
    	if(!CheckField('test_text','test_text is mandatory')) {return;}
    	print();
    }
    EOD;

// Add raw JavaScript code
$pdf->appendRawJavaScript(script: $formjs);

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
