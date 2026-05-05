<?php
/**
 * E070_default_page_content_import.php
 *
 * @since       2026-05-05
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
 * Custom PDF class that imports and reuses a source page as default page content.
 */
class PdfWithImportedDefaultPage extends \Com\Tecnick\Pdf\Tcpdf
{
    /** @var ?\Com\Tecnick\Pdf\Import\PageTemplateInterface */
    private $defaultTpl = null;

    /**
     * Register source data and pick the page template to be used as default content.
     *
     * This uses the same import flow as E065:
     * - setImportSourceData(...)
     * - importPage(...)
     *
     * @param string $sourcePdfData Raw source PDF bytes.
     * @param int    $sourcePageNum 1-based source page number to import.
     */
    public function setDefaultImportedPage(string $sourcePdfData, int $sourcePageNum = 1): void
    {
        $sourceId = $this->setImportSourceData($sourcePdfData);
        $pageCount = $this->getSourcePageCount($sourceId);

        if (($sourcePageNum < 1) || ($sourcePageNum > $pageCount)) {
            throw new \InvalidArgumentException(
                'Requested source page is out of range. Available pages: ' . $pageCount
            );
        }

        $this->defaultTpl = $this->importPage($sourceId, $sourcePageNum, [
            'box' => 'CropBox',
            'cache' => true,
        ]);
    }

    /**
     * Place the imported template on every new page.
     *
     * @param int $pid Page index (0-based).
     *
     * @return string Empty stream; content is added directly by useImportedPage().
     */
    public function defaultPageContent(int $pid = -1): string
    {
        if ($this->defaultTpl === null) {
            return '';
        }

        if ($pid < 0) {
            $pid = $this->page->getPageId();
        }

        $page = $this->page->getPage($pid);

        // Fill the whole destination page with the imported source page.
        $this->useImportedPage(
            $this->defaultTpl,
            0.0,
            0.0,
            (float) $page['width'],
            (float) $page['height'],
            [
                'keepAspectRatio' => true,
                'align' => 'CC',
                'clip' => true,
            ]
        );

        return '';
    }
}

// ---- Step 1: build a source PDF that will be imported ----

$src = new \Com\Tecnick\Pdf\Tcpdf();
$srcFont = $src->font->insert($src->pon, 'helvetica', '', 14);

$src->addPage();
$src->page->addContent($srcFont['out']);
$src->addHTMLCell(
    '<h1>Imported default page (source)</h1>'
    . '<p>This page is imported and reused as defaultPageContent() in E070.</p>',
    20,
    30,
    170
);

$sourcePdfData = $src->getOutPDFString();

// ---- Step 2: create destination PDF using the custom subclass ----

$pdf = new PdfWithImportedDefaultPage(
    'mm',
    true,
    false,
    true,
    '',
    null,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 070');
$pdf->setTitle('Default Page Content from Imported PDF');
$pdf->setKeywords('TCPDF tc-lib-pdf example import defaultPageContent template');
$pdf->setPDFFilename('070_default_page_content_import.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

// Reuse source page 1 as the default page content for all pages.
$pdf->setDefaultImportedPage($sourcePdfData, 1);
$pdf->enableDefaultPageContent();

$bodyFont = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
$titleFont = $pdf->font->insert($pdf->pon, 'helvetica', 'B', 13);

// Page 1
$pdf->addPage();
$pdf->page->addContent($titleFont['out']);
$pdf->page->addContent($pdf->getTextCell('Page 1 - Foreground content', 20, 85, 170, 0, 0, 0, 'T', 'L'));

$pdf->page->addContent($bodyFont['out']);
$pdf->page->addContent(
    $pdf->getTextCell(
        'The page background comes from an imported PDF page loaded via setImportSourceData() and importPage(), then applied in defaultPageContent().',
        20,
        95,
        170,
        0,
        0,
        2,
        'T',
        'J'
    )
);

// Page 2
$pdf->addPage();
$pdf->page->addContent($titleFont['out']);
$pdf->page->addContent($pdf->getTextCell('Page 2 - Same imported default content', 20, 85, 170, 0, 0, 0, 'T', 'L'));

$pdf->page->addContent($bodyFont['out']);
$pdf->page->addContent(
    $pdf->getTextCell(
        'Because defaultPageContent() runs whenever addPage() is called, the imported source page is automatically placed on every new page.',
        20,
        95,
        170,
        0,
        0,
        2,
        'T',
        'J'
    )
);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);