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
$pdf->setSubject('tc-lib-pdf example: 040');
$pdf->setTitle('Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example');
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

$pdf->addHTMLCell(
    $html,
    15,
    15,
    180,
);

$posx = 80;
$posy = 100;

$labelWidth = 62;
$labelX = 15;
$labelStyle = 'font-size:9pt; color:#333333; text-align:right;';

// text
$pdf->addHTMLCell('<p style="' . $labelStyle . '">Text Input:</p>', $labelX, $posy, $labelWidth, 5);
$fftextid = $pdf->addFFText('test_text', $posx, $posy, 50, 5);
$pdf->page->addAnnotRef($fftextid);

// radiobuttons
$pdf->addHTMLCell('<p style="' . $labelStyle . '">Radio – Option One:</p>', $labelX, $posy + 10, $labelWidth, 5);
$pdf->addHTMLCell('<p style="' . $labelStyle . '">Radio – Option Two:</p>', $labelX, $posy + 15, $labelWidth, 5);
$pdf->addHTMLCell('<p style="' . $labelStyle . '">Radio – Option Three:</p>', $labelX, $posy + 20, $labelWidth, 5);
$ffrbid1 = $pdf->addFFRadioButton('test_radiobutton', $posx, $posy + 10, 5, 'one');
$ffrbid2 = $pdf->addFFRadioButton('test_radiobutton', $posx, $posy + 15, 5, 'two', true);
$ffrbid3 = $pdf->addFFRadioButton('test_radiobutton', $posx, $posy + 20, 5, 'three');
$pdf->page->addAnnotRef($ffrbid1);
$pdf->page->addAnnotRef($ffrbid2);
$pdf->page->addAnnotRef($ffrbid3);

// checkbox
$pdf->addHTMLCell('<p style="' . $labelStyle . '">Checkbox:</p>', $labelX, $posy + 30, $labelWidth, 5);
$ffckbxid1 = $pdf->addFFCheckBox('test_checkbox', $posx, $posy + 30, 5);
$pdf->page->addAnnotRef($ffckbxid1);

// combobox
$pdf->addHTMLCell('<p style="' . $labelStyle . '">Combo Box:</p>', $labelX, $posy + 40, $labelWidth, 5);
$ffcmbxid1 = $pdf->addFFComboBox('test_combobox', $posx, $posy + 40, 50, 5, [
    ['0','one'],
    ['1','two'],
    ['2','three'],
]);
$pdf->page->addAnnotRef($ffcmbxid1);

// listbox
$pdf->addHTMLCell('<p style="' . $labelStyle . '">List Box:</p>', $labelX, $posy + 50, $labelWidth, 5);
$fflsbxid1 = $pdf->addFFListBox('test_listbox', $posx, $posy + 50, 50, 15,
    ['one', 'two', 'three'],
    ['subtype' => 'Widget'],
    ['multipleSelection'=>'true'],
);
$pdf->page->addAnnotRef($fflsbxid1);

// button - reset form
$pdf->addHTMLCell('<p style="' . $labelStyle . '">Actions:</p>', $labelX, $posy + 70, $labelWidth, 5);
$ffbtnid1 = $pdf->addFFButton('reset', $posx, $posy + 70, 20, 5, 
    "Reset", 
    ['S'=>'ResetForm'],
    ['subtype' => 'Widget'],
    [
        'lineWidth'=>2,
        'borderStyle'=>'beveled',
        'fillColor'=>'#80c4ff',
        'strokeColor'=>'#404040',
    ],
);
$pdf->page->addAnnotRef($ffbtnid1);

// button - print document
$ffbtnid2 = $pdf->addFFButton('print', $posx + 20, $posy + 70, 20, 5, 
    'Print', 
    'Print()',
    ['subtype' => 'Widget'],
    [
        'lineWidth'=>2,
        'borderStyle'=>'beveled',
        'fillColor'=>'#80c4ff',
        'strokeColor'=>'#404040',
    ],
);
$pdf->page->addAnnotRef($ffbtnid2);

// button - submit form
$ffbtnid3 = $pdf->addFFButton('submit', $posx + 40, $posy + 70, 20, 5, 
    'Submit', 
    [
        'S'=>'SubmitForm',
        'F'=>'http://localhost/printvars.php',
        'Flags'=>['ExportFormat'],
    ],
    ['subtype' => 'Widget'],
    [
        'lineWidth'=>2,
        'borderStyle'=>'beveled',
        'fillColor'=>'#80c4ff',
        'strokeColor'=>'#404040',
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
$pdf->appendRawJavaScript($formjs);

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
