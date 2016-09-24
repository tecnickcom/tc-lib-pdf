<?php
/**
 * Tcpdf.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2016 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf;

use \Com\Tecnick\Pdf\Exception as PdfException;
use \Com\Tecnick\Color\Pdf as ObjColor;
use \Com\Tecnick\Barcode\Barcode as ObjBarcode;
use \Com\Tecnick\File\File as ObjFile;
use \Com\Tecnick\Unicode\Convert as ObjUniConvert;
use \Com\Tecnick\Pdf\Encrypt\Encrypt as ObjEncrypt;
use \Com\Tecnick\Pdf\Page\Page as ObjPage;
use \Com\Tecnick\Pdf\Graph\Draw as ObjGraph;
use \Com\Tecnick\Pdf\Font\Stack as ObjFont;
use \Com\Tecnick\Pdf\Image\Import as ObjImage;

/**
 * Com\Tecnick\Pdf\Tcpdf
 *
 * Tcpdf PDF class
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2016 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class Tcpdf
{
    /**
     * Unit of measure
     *
     * @var string
     */
    protected $unit = 'mm';

    /**
     * Unit of measure conversion ratio
     *
     * @var float
     */
    protected $kunit = 1.0;

    /**
     * True if we are in PDF/A mode.
     *
     * @var bool
     */
    protected $pdfa = false;

    /**
     * True if the signature approval is enabled (for incremental updates).
     *
     * @var bool
     */
    protected $sigapp = false;

    /**
     * True to subset the fonts
     *
     * @var boolean
     */
    protected $subsetfont = false;

    /**
     * True for Unicode font mode
     *
     * @var boolean
     */
    protected $unicodemode = true;

    /**
     * Color object
     *
     * @var \Com\Tecnick\Color\Pdf
     */
    protected $color;

    /**
     * Barcode object
     *
     * @var \Com\Tecnick\Barcode\Barcode
     */
    protected $barcode;

    /**
     * File object
     *
     * @var \Com\Tecnick\File\File
     */
    protected $file;

    /**
     * Unicode Convert object
     *
     * @var \Com\Tecnick\Unicode\Convert
     */
    protected $uniconv;

    /**
     * Encrypt object
     *
     * @var \Com\Tecnick\Pdf\Encrypt\Encrypt
     */
    protected $encrypt;

    /**
     * Page object
     *
     * @var \Com\Tecnick\Pdf\Page\Page
     */
    protected $page;

    /**
     * Graph object
     *
     * @var \Com\Tecnick\Pdf\Graph\Draw
     */
    protected $graph;

    /**
     * Font object
     *
     * @var \Com\Tecnick\Pdf\Font\Stack
     */
    protected $font;

    /**
     * Image Import object
     *
     * @var \Com\Tecnick\Pdf\Image\Import
     */
    protected $image;

    /**
     * Initialize a new PDF object
     *
     * @param string     $unit        Unit of measure ('pt', 'mm', 'cm', 'in')
     * @param bool       $unicodemode True if the document is in Unicode mode
     * @param bool       $subsetfont  If true subset the embedded fonts to remove the unused characters
     * @param bool       $pdfa        True to produce a PDF/A document (some features will be bisabled)
     * @param ObjEncrypt $encobj      Encryption object
     */
    public function __construct(
        $unit = 'mm',
        $unicodemode = true,
        $subsetfont = false,
        $pdfa = false,
        \Com\Tecnick\Pdf\Encrypt\Encrypt $encobj = null
    ) {
    
        $this->unit = $unit;
        $this->unicodemode = $unicodemode;
        $this->subsetfont = $subsetfont;
        $this->pdfa = $pdfa;

        $this->color = new ObjColor;
        $this->barcode = new ObjBarcode;
        $this->file = new ObjFile;
        $this->uniconv = new ObjUniConvert;

        if ($encobj === null) {
            $this->encrypt = new ObjEncrypt();
        } else {
            $this->encrypt = $encobj;
        }

        $this->page = new ObjPage(
            $this->unit,
            $this->color,
            $this->encrypt,
            $this->pdfa,
            $this->sigapp
        );
        $this->kunit = $this->page->getKUnit();
        //$this->page->enableSignatureApproval($sigapp);

        $this->graph = new ObjGraph(
            $this->kunit,
            0,
            0,
            $this->color,
            $this->encrypt,
            $this->pdfa
        );
        //$this->graph->setPageHeight($pageh)
        //$this->graph->setPageWidth($pagew);

        $this->font = new ObjFont(
            $this->kunit,
            $this->subsetfont,
            $this->unicodemode,
            $this->pdfa
        );
        
        $this->image = new ObjImage(
            $this->kunit,
            $this->encrypt,
            $this->pdfa
        );
    }
}
