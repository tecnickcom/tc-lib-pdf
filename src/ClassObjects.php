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
     * Initialize class objects.
     */
    protected function initClassObjects(): void
    {
        $cobjs = $this->newClassObjects();
        $this->color = $cobjs['color'];
        $this->barcode = $cobjs['barcode'];
        $this->file = $cobjs['file'];
        $this->cache = $cobjs['cache'];
        $this->uniconv = $cobjs['uniconv'];
        $this->encrypt = $cobjs['encrypt'];
        $this->page = $cobjs['page'];
        $this->kunit = $cobjs['kunit'];
        $this->graph = $cobjs['graph'];
        $this->font = $cobjs['ont'];
        $this->image = $cobjs['image'];
    }

    /**
     * Returns an array of class objects.
     */
    protected function newClassObjects(): array
    {
        $cobjs = [];

        $cobjs['color'] = new ObjColor();
        $cobjs['barcode'] = new ObjBarcode();
        $cobjs['file'] = new ObjFile();
        $cobjs['cache'] = new ObjCache();
        $cobjs['uniconv'] = new ObjUniConvert();
        $cobjs['encrypt'] = new ObjEncrypt();

        $cobjs['page'] = new ObjPage(
            $this->unit,
            $cobjs['color'],
            $cobjs['encrypt'],
            (bool) $this->pdfa,
            $this->compress,
            $this->sigapp
        );

        $cobjs['kunit'] = $cobjs['page']->getKUnit();

        $cobjs['graph'] = new ObjGraph(
            $cobjs['kunit'],
            0, // $this->graph->setPageWidth($pagew)
            0, // $this->graph->setPageHeight($pageh)
            $cobjs['color'],
            $cobjs['encrypt'],
            (bool) $this->pdfa,
            $this->compress
        );

        $cobjs['ont'] = new ObjFont(
            $cobjs['kunit'],
            $this->subsetfont,
            $this->isunicode,
            (bool) $this->pdfa
        );

        $cobjs['image'] = new ObjImage(
            $cobjs['kunit'],
            $cobjs['encrypt'],
            (bool) $this->pdfa,
            $this->compress
        );

        return $cobjs;
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
