<?php
/**
 * ClassObjects.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2017 Nicola Asuni - Tecnick.com LTD
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
use \Com\Tecnick\File\Cache as ObjCache;
use \Com\Tecnick\Unicode\Convert as ObjUniConvert;
use \Com\Tecnick\Pdf\Encrypt\Encrypt as ObjEncrypt;
use \Com\Tecnick\Pdf\Page\Page as ObjPage;
use \Com\Tecnick\Pdf\Graph\Draw as ObjGraph;
use \Com\Tecnick\Pdf\Font\Stack as ObjFont;
use \Com\Tecnick\Pdf\Image\Import as ObjImage;

/**
 * Com\Tecnick\Pdf\ClassObjects
 *
 * External class objects PDF class
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2017 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
abstract class ClassObjects extends \Com\Tecnick\Pdf\MetaInfo
{
    /**
     * Encrypt object
     *
     * @var \Com\Tecnick\Pdf\Encrypt\Encrypt
     */
    public $encrypt;

    /**
     * Color object
     *
     * @var \Com\Tecnick\Color\Pdf
     */
    public $color;

    /**
     * Barcode object
     *
     * @var \Com\Tecnick\Barcode\Barcode
     */
    public $barcode;

    /**
     * File object
     *
     * @var \Com\Tecnick\File\File
     */
    public $file;

    /**
     * Cache object
     *
     * @var \Com\Tecnick\File\Cache
     */
    public $cache;

    /**
     * Unicode Convert object
     *
     * @var \Com\Tecnick\Unicode\Convert
     */
    public $uniconv;

    /**
     * Page object
     *
     * @var \Com\Tecnick\Pdf\Page\Page
     */
    public $page;

    /**
     * Graph object
     *
     * @var \Com\Tecnick\Pdf\Graph\Draw
     */
    public $graph;

    /**
     * Font object
     *
     * @var \Com\Tecnick\Pdf\Font\Stack
     */
    public $font;

    /**
     * Image Import object
     *
     * @var \Com\Tecnick\Pdf\Image\Import
     */
    public $image;

    /**
     * Initialize class objects
     */
    protected function initClassObjects()
    {
        $this->color = new ObjColor;
        $this->barcode = new ObjBarcode;
        $this->file = new ObjFile;
        $this->cache = new ObjCache;
        $this->uniconv = new ObjUniConvert;
        
        if ($this->encrypt === null) {
            $this->encrypt = new ObjEncrypt();
        }
        
        $this->page = new ObjPage(
            $this->unit,
            $this->color,
            $this->encrypt,
            $this->pdfa,
            $this->compress,
            $this->sigapp
        );
        $this->kunit = $this->page->getKUnit();

        $this->graph = new ObjGraph(
            $this->kunit,
            0, // $this->graph->setPageWidth($pagew)
            0, // $this->graph->setPageHeight($pageh)
            $this->color,
            $this->encrypt,
            $this->pdfa,
            $this->compress
        );

        $this->font = new ObjFont(
            $this->kunit,
            $this->subsetfont,
            $this->isunicode,
            $this->pdfa,
            $this->compress
        );
        
        $this->image = new ObjImage(
            $this->kunit,
            $this->encrypt,
            $this->pdfa,
            $this->compress
        );
    }

    /**
     * Enable or disable the the Signature Approval
     *
     * @param boolean $enabled It true enable the Signature Approval
     */
    protected function enableSignatureApproval($enabled = true)
    {
        $this->sigapp = (bool) $enabled;
        $this->page->enableSignatureApproval($this->sigapp);
        return $this;
    }
}
