<?php

/**
 * ClassObjects.php
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
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
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class ClassObjects extends \Com\Tecnick\Pdf\Output
{
   /**
    * Initialize dependencies class objects.
    *
    * @param ?ObjEncrypt $objEncrypt Encryption object.
    */
    public function initClassObjects(
        ?ObjEncrypt $objEncrypt = null
    ): void {
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

        $pdfamode = (bool) ($this->pdfa > 0);

        $this->page = new ObjPage(
            $this->unit,
            $this->color,
            $this->encrypt,
            $pdfamode,
            $this->compress,
            $this->sigapp,
        );

        $this->kunit = $this->page->getKUnit();
        $this->svgminunitlen = $this->toUnit($this::SVGMINPNTLEN);

        $this->graph = new ObjGraph(
            $this->kunit,
            0, // $this->graph->setPageWidth($pagew)
            0, // $this->graph->setPageHeight($pageh)
            $this->color,
            $this->encrypt,
            $pdfamode,
            $this->compress,
        );

        $this->font = new ObjFont(
            $this->kunit,
            $this->subsetfont,
            $this->isunicode,
            $pdfamode,
        );

        $this->image = new ObjImage(
            $this->kunit,
            $this->encrypt,
            $pdfamode,
            $this->compress,
        );
    }
}
