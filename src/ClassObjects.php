<?php

/**
 * ClassObjects.php
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
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
use Com\Tecnick\Pdf\PdfColor as ObjPdfColor;
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
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type TFileOptions array{
 *   allowedHosts?: array<string>,
 *   maxRemoteSize?: int,
 *   curlopts?: array<int, bool|int|string>,
 *   defaultCurlOpts?: array<int, bool|int|string>,
 *   fixedCurlOpts?: array<int, bool|int|string>
 * }
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class ClassObjects extends \Com\Tecnick\Pdf\Output
{
   /**
    * Initialize dependencies class objects.
    *
    * @param ?ObjEncrypt $objEncrypt  Encryption object.
    * @param TFileOptions|null $fileOptions Optional configuration for the shared file helper used
    *                                       to load external resources (images, fonts, SVG, etc.).
    *                                       Supported keys:
    *                                       - allowedHosts (string[]): Whitelist of host names that
    *                                         the library is allowed to fetch over HTTP/HTTPS. For
    *                                         security reasons remote URL loading is DISABLED by
    *                                         default; you MUST populate this list (for example
    *                                         ['example.com', 'cdn.example.com']) to enable any
    *                                         remote download. Local file paths are not affected.
    *                                       - maxRemoteSize (int): Maximum size in bytes accepted
    *                                         for a remote download (default 52428800 = 50 MiB).
    *                                       - curlopts (array<int,bool|int|string>): Per-request
    *                                         cURL options merged on top of the defaults (keys are
    *                                         CURLOPT_* constants).
    *                                       - defaultCurlOpts (array<int,bool|int|string>):
    *                                         Replaces the built-in default cURL options. Use with
    *                                         care; omit to keep the safe defaults.
    *                                       - fixedCurlOpts (array<int,bool|int|string>): cURL
    *                                         options that are always enforced and cannot be
    *                                         overridden by curlopts (for example to pin TLS
    *                                         settings).
    */
    public function initClassObjects(
        ?ObjEncrypt $objEncrypt = null,
        ?array $fileOptions = null
    ): void {
        if ($objEncrypt instanceof ObjEncrypt) {
            $this->encrypt = $objEncrypt;
        } else {
            $this->encrypt = new ObjEncrypt();
        }

        $this->color = new ObjPdfColor();
        $this->color->setForceDeviceCmyk($this->requiresPdfxDeviceCmyk());
        $this->barcode = new ObjBarcode();
        $this->file = new ObjFile(
            $fileOptions['allowedHosts'] ?? [],
            $fileOptions['maxRemoteSize'] ?? 52428800,
            $fileOptions['curlopts'] ?? [],
            $fileOptions['defaultCurlOpts'] ?? null,
            $fileOptions['fixedCurlOpts'] ?? null,
        );
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
            $fileOptions,
        );

        $this->image = new ObjImage(
            $this->kunit,
            $this->encrypt,
            $pdfamode,
            $this->compress,
            $fileOptions,
        );
    }
}
