<?php
/**
 * 005_example_header_footer.php
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
require(__DIR__ . '/../vendor/autoload.php');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// ----------

/**
 * Custom PDF class with a repeating page header and footer.
 *
 * Extends Tcpdf and overrides defaultPageContent() so that every
 * new page automatically receives a branded header and a numbered footer.
 * The mechanism is activated by calling enableDefaultPageContent(true).
 */
class PdfWithHeaderFooter extends \Com\Tecnick\Pdf\Tcpdf
{
    /** Horizontal margin used by the header and footer bands (mm). */
    private const HF_MARGIN = 10.0;

    /** Height of the header band (mm). */
    private const HEADER_H = 12.0;

    /** Height of the footer band (mm). */
    private const FOOTER_H = 10.0;

    /** Document title shown left-aligned in the header. */
    private string $headerTitle = '';

    /** Subtitle / date shown right-aligned in the header. */
    private string $headerSubtitle = '';

    /**
     * Set the text displayed in the page header.
     *
     * @param string $title    Left-aligned title.
     * @param string $subtitle Right-aligned subtitle (e.g. date or company name).
     */
    public function setHeaderText(string $title, string $subtitle): void
    {
        $this->headerTitle = $title;
        $this->headerSubtitle = $subtitle;
    }

    /**
     * Generates the repeating header and footer for every page.
     *
     * This method is called automatically by setPageContext() whenever a new
     * page is added, provided enableDefaultPageContent(true) has been called.
     *
     * @param int $pid Page index (0-based).
     *
     * @return string Raw PDF stream prepended to the page content.
     */
    public function defaultPageContent(int $pid = -1): string
    {
        if ($pid < 0) {
            $pid = $this->page->getPageId();
        }

        // Insert the default font once and cache it for subsequent pages.
        if ($this->defaultfont === null) {
            $this->defaultfont = $this->font->insert($this->pon, 'helvetica', '', 9);
        }

        $page = $this->page->getPage($pid);
        $pw   = $page['width'];
        $ph   = $page['height'];

        // Keep graph coordinates in sync with the current page size.
        $this->graph->setPageWidth($pw);
        $this->graph->setPageHeight($ph);

        $lm   = self::HF_MARGIN;           // left margin x
        $rm   = $pw - self::HF_MARGIN;     // right margin x
        $tw   = $pw - (2 * self::HF_MARGIN); // usable band width

        $lineStyle = [
            'lineWidth' => 0.25,
            'lineCap'   => 'butt',
            'lineJoin'  => 'miter',
            'dashArray' => [],
            'dashPhase' => 0,
            'lineColor' => '#555555',
        ];

        $out = $this->graph->getStartTransform();
        $out .= $this->defaultfont['out'];

        // ---- HEADER ------------------------------------------------

        $headerY = self::HF_MARGIN;

        // Title – left-aligned, bold
        if ($this->headerTitle !== '') {
            $bfontBold = $this->font->insert($this->pon, 'helvetica', 'B', 10);
            $out .= $bfontBold['out'];
            $out .= $this->color->getPdfColor('#1a3a6b');
            $out .= $this->getTextCell(
                $this->headerTitle,
                $lm,
                $headerY,
                $tw * 0.65,    // 65 % of the usable width
                self::HEADER_H,
                0,
                0,
                'C',           // valign: centre inside the band
                'L',           // halign: left
            );
            $out .= $this->defaultfont['out'];
        }

        // Subtitle – right-aligned
        if ($this->headerSubtitle !== '') {
            $out .= $this->color->getPdfColor('#555555');
            $out .= $this->getTextCell(
                $this->headerSubtitle,
                $lm + $tw * 0.65,
                $headerY,
                $tw * 0.35,    // remaining 35 %
                self::HEADER_H,
                0,
                0,
                'C',           // valign: centre inside the band
                'R',           // halign: right
            );
        }

        // Separator line below the header
        $headerLineY = $headerY + self::HEADER_H;
        $out .= $this->graph->getLine($lm, $headerLineY, $rm, $headerLineY, $lineStyle);

        // ---- FOOTER ------------------------------------------------

        $footerLineY = $ph - self::HF_MARGIN - self::FOOTER_H;

        // Separator line above the footer
        $out .= $this->graph->getLine($lm, $footerLineY, $rm, $footerLineY, $lineStyle);

        // Page number – centred
        $out .= $this->color->getPdfColor('#555555');
        $out .= $this->getTextCell(
            'Page ' . ($pid + 1),
            $lm,
            $footerLineY,
            $tw,
            self::FOOTER_H,
            0,
            0,
            'C',               // valign: centre inside the band
            'C',               // halign: centre
        );

        $out .= $this->graph->getStopTransform();

        return $out;
    }
}

// ----------

// main PDF object using the custom subclass
$pdf = new PdfWithHeaderFooter(
    'mm',  // string $unit = 'mm',
    true,  // bool $isunicode = true,
    false, // bool $subsetfont = false,
    true,  // bool $compress = true,
    '',    // string $mode = '',
    null,  // ?ObjEncrypt $objEncrypt = null,
);

// ----------

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 005');
$pdf->setTitle('Header & Footer Example');
$pdf->setKeywords('TCPDF tc-lib-pdf example header footer');
$pdf->setPDFFilename('005_example_header_footer.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

// Set the text that will appear in the header on every page.
$pdf->setHeaderText('My Document Title', \date('Y-m-d'));

// Enable automatic header/footer: calls defaultPageContent() for every new page.
$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont = $pdf->font->insert($pdf->pon, 'helvetica', '', 11);
$bfontB = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 13);

// ----------
// Add first page

$page01 = $pdf->addPage();
$pdf->setBookmark('Page 1', '', 0, -1, 0, 0, 'B', 'blue');

// Page content starts below the header band (HF_MARGIN + HEADER_H + separator ≈ 25 mm).
$contentY = 28.0;

$pdf->page->addContent($bfontB['out']);

$title1 = $pdf->getTextCell(
    'Page 1 — Custom Repeating Header & Footer',
    10, $contentY, 190, 0, 0, 0, 'T', 'L',
);
$pdf->page->addContent($title1);

$pdf->page->addContent($bfont['out']);

$body1 = 'This is the first page of a document that demonstrates how to add a custom page header and footer that automatically repeats on every new page using tc-lib-pdf.

The header displays the document title (left) and the date (right), separated from the page content by a thin horizontal rule. The footer shows the page number centred at the bottom, also separated by a horizontal rule.

This is achieved by subclassing \Com\Tecnick\Pdf\Tcpdf and overriding the public defaultPageContent() method, then activating the mechanism once with enableDefaultPageContent(true) before adding any pages.

Because setPageContext() calls defaultPageContent() automatically every time addPage() is used, the header and footer appear on every page without any additional code in the page-building section.';

$txt1 = $pdf->getTextCell(
    $body1,
    10, $contentY + 10, 190, 0,
    15,  // first-line indent (mm)
    2,   // extra line spacing (mm)
    'T', 'J',
);
$pdf->page->addContent($txt1);

// ----------
// Add second page

$page02 = $pdf->addPage();
$pdf->setBookmark('Page 2', '', 0, -1, 0, 0, 'B', 'green');

$pdf->page->addContent($bfontB['out']);

$title2 = $pdf->getTextCell(
    'Page 2 — Continued Content',
    10, $contentY, 190, 0, 0, 0, 'T', 'L',
);
$pdf->page->addContent($title2);

$pdf->page->addContent($bfont['out']);

$body2 = 'This is the second page. The header and footer are present here too without any manual intervention — the overridden defaultPageContent() method takes care of them automatically.

Any further pages added with addPage() would also receive the same header and footer, making the approach suitable for multi-page reports, invoices, or any document type that requires consistent page decoration.';

$txt2 = $pdf->getTextCell(
    $body2,
    10, $contentY + 10, 190, 0,
    15,  // first-line indent (mm)
    2,   // extra line spacing (mm)
    'T', 'J',
);
$pdf->page->addContent($txt2);

// ----------
// Output the PDF

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF($rawpdf);
