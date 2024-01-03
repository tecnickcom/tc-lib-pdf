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
 */
abstract class ClassObjects extends \Com\Tecnick\Pdf\Output
{
    /**
     * Initialize class objects
     */
    protected function initClassObjects(): void
    {
        $this->color = new ObjColor();
        $this->barcode = new ObjBarcode();
        $this->file = new ObjFile();
        $this->cache = new ObjCache();
        $this->uniconv = new ObjUniConvert();
        $this->encrypt = new ObjEncrypt();

        $this->page = new ObjPage(
            $this->unit,
            $this->color,
            $this->encrypt,
            (bool) $this->pdfa,
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
            (bool) $this->pdfa,
            $this->compress
        );

        $this->font = new ObjFont(
            $this->kunit,
            $this->subsetfont,
            $this->isunicode,
            (bool) $this->pdfa
        );

        $this->image = new ObjImage(
            $this->kunit,
            $this->encrypt,
            (bool) $this->pdfa,
            $this->compress
        );
    }

    /**
     * Enable or disable the the Signature Approval
     *
     * @param bool $enabled It true enable the Signature Approval
     */
    protected function enableSignatureApproval(bool $enabled = true): static
    {
        $this->sigapp = $enabled;
        $this->page->enableSignatureApproval($this->sigapp);
        return $this;
    }
}
