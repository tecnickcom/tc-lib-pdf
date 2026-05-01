<?php
/**
 * E059_document_javascript.php
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

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

/**
 * Demonstrate document-level JavaScript actions.
 *
 * This example covers three distinct attachment mechanisms:
 *
 *   appendRawJavaScript()
 *       Appends raw JS to the single global script string.  The global script
 *       runs as a document-open action (equivalent to \WillSave / open trigger
 *       in Acrobat JS terms).  Useful for lightweight one-shot initialization
 *       that must not duplicate logic across multiple calls.
 *
 *   addRawJavaScriptObj($script, onload: true)
 *       Creates a separate JS object that is executed when the document is
 *       opened (document-level open action).  Suitable for modular scripts
 *       that need to be kept independent of the global string.
 *
 *   addRawJavaScriptObj($script, onload: false)
 *       Creates a JS object that is embedded but NOT wired as an open action.
 *       It can be referenced by annotation actions or triggered programmatically
 *       by other scripts (e.g. via a named action or a button field).
 *
 * NOTE: JavaScript is silently suppressed in PDF/A, PDF/X, and PDF/UA modes
 * because those standards forbid embedded scripts.  This example therefore
 * uses plain (unconstrained) PDF output.
 *
 * NOTE: Script execution requires a PDF reader with Acrobat JavaScript support
 * (e.g. Adobe Acrobat / Adobe Reader).  Many open-source readers do not run JS.
 */

// main TCPDF object — plain mode (no PDF/A, PDF/X, PDF/UA constraint)
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm',   // unit
    true,   // unicode
    false,  // subset fonts
    true,   // compress
    '',     // mode — plain PDF, JS allowed
    null,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 059');
$pdf->setTitle('Document-Level JavaScript Actions');
$pdf->setKeywords('TCPDF tc-lib-pdf example javascript document open close print save trigger');
$pdf->setPDFFilename('E059_document_javascript.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// -----------------------------------------------------------------------
// 1. appendRawJavaScript — global JS string (document-open trigger)
// -----------------------------------------------------------------------
// The JS string is accumulated and emitted as a single named JS object
// ("EmbeddedJS").  It fires when the document is opened.
$pdf->appendRawJavaScript(
    // Greet the user on open.
    'app.alert("Document opened via appendRawJavaScript.", 3);' . "\n"
);

// Multiple calls are concatenated in order.
$pdf->appendRawJavaScript(
    // Register a WillClose handler.
    'app.addStateChangeHandler("WillClose", function() {' . "\n"
    . '  app.alert("Document is closing (WillClose handler).", 3);' . "\n"
    . '});' . "\n"
);

// -----------------------------------------------------------------------
// 2. addRawJavaScriptObj — separate JS object, executed on open
// -----------------------------------------------------------------------
// Returns the PDF object ID so you can cross-reference if needed.
$onloadObjId = $pdf->addRawJavaScriptObj(
    // Register a WillPrint event to warn the user before printing.
    'app.addStateChangeHandler("WillPrint", function() {' . "\n"
    . '  app.alert("Document will be printed.", 3);' . "\n"
    . '});' . "\n",
    true, // onload: execute when the document is opened
);

// -----------------------------------------------------------------------
// 3. addRawJavaScriptObj — separate JS object, NOT an open action
// -----------------------------------------------------------------------
// This object is embedded in the PDF but is NOT automatically executed.
// A button field or annotation action can trigger it by name.
$idleObjId = $pdf->addRawJavaScriptObj(
    // A utility helper function that can be called from other scripts.
    'function showDocInfo() {' . "\n"
    . '  var msg = "Title: " + this.info.Title + "\\n"' . "\n"
    . '          + "Author: " + this.info.Author;' . "\n"
    . '  app.alert(msg, 3);' . "\n"
    . '}' . "\n",
    false, // onload: false — NOT executed automatically
);

// -----------------------------------------------------------------------
// Page 1 — Explanation
// -----------------------------------------------------------------------
$bfont  = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);
$bfontB = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 14);

$page1 = $pdf->addPage();

$html = <<<HTML
<h1 style="font-size:16pt; color:#003366;">Document-Level JavaScript Actions</h1>
<p style="font-size:10pt; color:#333333;">
This PDF demonstrates three distinct ways to attach JavaScript to a document
using tc-lib-pdf.  The scripts are embedded in the PDF structure; a compatible
reader with Acrobat JavaScript support is required to execute them.
</p>
<h2 style="font-size:13pt; color:#005599;">1. appendRawJavaScript()</h2>
<p style="font-size:9pt; color:#333333;">
Appends raw JS to the global script string.  The accumulated string fires when
the document is opened.  Two separate calls were made in this example: one to
show an alert on open, and one to register a <code>WillClose</code> handler.
</p>
<h2 style="font-size:13pt; color:#005599;">2. addRawJavaScriptObj(\$script, onload: true)</h2>
<p style="font-size:9pt; color:#333333;">
Creates a separate JS object (PDF object ID: <b>{$onloadObjId}</b>) and wires it as an
open action so it executes when the document is opened.  In this example the
script registers a <code>WillPrint</code> event handler.
</p>
<h2 style="font-size:13pt; color:#005599;">3. addRawJavaScriptObj(\$script, onload: false)</h2>
<p style="font-size:9pt; color:#333333;">
Creates a JS object (PDF object ID: <b>{$idleObjId}</b>) that is embedded but NOT
executed automatically.  It defines a helper function <code>showDocInfo()</code>
that other scripts or button-field actions can invoke.
</p>
<h2 style="font-size:13pt; color:#005599;">Suppression in conformance modes</h2>
<p style="font-size:9pt; color:#333333;">
All three JS APIs silently return without effect when the PDF is created in
PDF/A, PDF/X, or PDF/UA mode, because those standards forbid embedded scripts.
</p>
HTML;

$pdf->addHTMLCell($html, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
