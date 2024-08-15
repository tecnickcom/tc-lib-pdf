<?php

/**
 * ClassObjects.php
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf;

use Com\Tecnick\Barcode\Barcode as ObjBarcode;
use Com\Tecnick\Color\Pdf as ObjColor;
use Com\Tecnick\File\Cache as ObjCache;
use Com\Tecnick\File\File as ObjFile;
use Com\Tecnick\Pdf\Encrypt\Encrypt as ObjEncrypt;
use Com\Tecnick\Pdf\Font\Stack as ObjFont;
use Com\Tecnick\Pdf\Graph\Draw as ObjGraph;
use Com\Tecnick\Pdf\Image\Import as ObjImage;
use Com\Tecnick\Pdf\Page\Page as ObjPage;
use Com\Tecnick\Unicode\Convert as ObjUniConvert;

/**
 * Com\Tecnick\Pdf\ClassObjects
 *
 * External class objects PDF class
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 */
class ClassObjects
{
   /**
    * Encrypt object.
    */
    public ObjEncrypt $encrypt;

   /**
    * Color object.
    */
    public ObjColor $color;

   /**
    * Barcode object.
    */
    public ObjBarcode $barcode;

   /**
    * File object.
    */
    public ObjFile $file;

   /**
    * Cache object.
    */
    public ObjCache $cache;

   /**
    * Unicode Convert object.
    */
    public ObjUniConvert $uniconv;

   /**
    * Page object.
    */
    public ObjPage $page;

   /**
    * Graph object.
    */
    public ObjGraph $graph;

   /**
    * Font object.
    */
    public ObjFont $font;

   /**
    * Image Import object.
    */
    public ObjImage $image;

    /**
     * Unit of measure conversion ratio.
     */
    protected float $kunit = 1.0;

   /**
    * Initialize dependencies class objects.
    *
    * @param string      $unit       Unit of measure ('pt', 'mm', 'cm', 'in').
    * @param bool        $isunicode  True if the document is in Unicode mode.
    * @param bool        $subsetfont If true subset the embedded fonts to remove the unused characters.
    * @param bool        $compress   Set to false to disable stream compression.
    * @param bool        $sigapp     Enable signature approval (for incremental updates).
    * @param bool        $pdfa       True if PDF/A Mode.
    * @param ?ObjEncrypt $objEncrypt Encryption object.
    */
    public function __construct(
        string $unit = 'mm',
        bool $isunicode = true,
        bool $subsetfont = false,
        bool $compress = true,
        bool $sigapp = false,
        bool $pdfa = false,
        ?ObjEncrypt $objEncrypt = null
    ) {
        if ($objEncrypt instanceof ObjEncrypt) {
            $this->encrypt = $objEncrypt;
        } else {
            $this->encrypt = new ObjEncrypt();
        }

        $this->color = new ObjColor();
        $this->barcode = new ObjBarcode();
        $this->file = new ObjFile();
        $this->cache = new ObjCache();
        $this->uniconv = new ObjUniConvert();

        $this->page = new ObjPage(
            $unit,
            $this->color,
            $this->encrypt,
            $pdfa,
            $compress,
            $sigapp,
        );

        $kunit = $this->page->getKUnit();

        $this->graph = new ObjGraph(
            $kunit,
            0, // $this->dep->graph->setPageWidth($pagew)
            0, // $this->dep->graph->setPageHeight($pageh)
            $this->color,
            $this->encrypt,
            $pdfa,
            $compress,
        );

        $this->font = new ObjFont(
            $kunit,
            $subsetfont,
            $isunicode,
            $pdfa,
        );

        $this->image = new ObjImage(
            $kunit,
            $this->encrypt,
            $pdfa,
            $compress,
        );
    }
}
