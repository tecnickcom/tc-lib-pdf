<?php

/**
 * Tcpdf.php
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

use Com\Tecnick\Barcode\Exception as BarcodeException;
use Com\Tecnick\Pdf\Encrypt\Encrypt as ObjEncrypt;
use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * Com\Tecnick\Pdf\Tcpdf
 *
 * Tcpdf PDF class
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type StyleDataOpt from \Com\Tecnick\Pdf\Graph\Base
 * @phpstan-import-type PageData from \Com\Tecnick\Pdf\Page\Box
 * @phpstan-import-type PageInputData from \Com\Tecnick\Pdf\Page\Box
 * @phpstan-import-type TFontMetric from \Com\Tecnick\Pdf\Font\Stack
 *
 * @phpstan-import-type TSignature from Output
 * @phpstan-import-type TSignTimeStamp from Output
 * @phpstan-import-type TUserRights from Output
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
class Tcpdf extends \Com\Tecnick\Pdf\ClassObjects
{
    /**
     * Initialize a new PDF object.
     *
     * @param string      $unit       Unit of measure ('pt', 'mm', 'cm', 'in').
     * @param bool        $isunicode  True if the document is in Unicode mode.
     * @param bool        $subsetfont If true subset the embedded fonts to remove the unused characters.
     * @param bool        $compress   Set to false to disable stream compression.
     * @param string      $mode       PDF mode: "pdfa1", "pdfa2", "pdfa3", "pdfx" or empty.
     * @param ?ObjEncrypt $objEncrypt Encryption object.
     */
    public function __construct(
        string $unit = 'mm',
        bool $isunicode = true,
        bool $subsetfont = false,
        bool $compress = true,
        string $mode = '',
        ?ObjEncrypt $objEncrypt = null
    ) {
        $this->setDecimalSeparator();
        $this->doctime = \time();
        $this->docmodtime = $this->doctime;
        $seed = new \Com\Tecnick\Pdf\Encrypt\Type\Seed();
        $this->fileid = \md5($seed->encrypt('TCPDF'));
        $this->setPDFFilename($this->fileid . '.pdf');
        $this->unit = $unit;
        $this->setUnicodeMode($isunicode);
        $this->subsetfont = $subsetfont;
        $this->setPDFMode($mode);
        $this->setCompressMode($compress);
        $this->setPDFVersion();
        $this->initClassObjects($objEncrypt);
    }

    /**
     * Set the pdf mode.
     *
     * @param string $mode Input PDFA mode.
     */
    protected function setPDFMode(string $mode): void
    {
        $this->pdfx = ($mode == 'pdfx');
        $this->pdfa = 0;
        $matches = ['', '0'];
        if (\preg_match('/^pdfa([1-3])$/', $mode, $matches) === 1) {
            $this->pdfa = (int) $matches[1];
        }
    }

    /**
     * Set the compression mode.
     *
     * @param bool $compress Set to false to disable stream compression.
     */
    protected function setCompressMode(bool $compress): void
    {
        $this->compress = (($compress) && ($this->pdfa != 3));
    }

    /**
     * Set the decimal separator.
     *
     * @throw PdfException in case of error.
     */
    protected function setDecimalSeparator(): void
    {
        // check for locale-related bug
        if (1.1 == 1) { /* @phpstan-ignore-line */
            throw new PdfException("Don't alter the locale before including class file");
        }

        // check for decimal separator
        // @phpstan-ignore notEqual.alwaysFalse
        if (\sprintf('%.1F', 1.0) != '1.0') {
            \setlocale(LC_NUMERIC, 'C');
        }
    }

    /**
     * Set the decimal separator.
     *
     * @param bool $isunicode True when using Unicode mode.
     */
    protected function setUnicodeMode(bool $isunicode): void
    {
        $this->isunicode = $isunicode;
        // check if PCRE Unicode support is enabled
        if ($this->isunicode && (@\preg_match('/\pL/u', 'a') == 1)) {
            $this->setSpaceRegexp('/(?!\xa0)[\s\p{Z}]/u');
            return;
        }

        // PCRE unicode support is turned OFF
        $this->setSpaceRegexp('/[^\S\xa0]/');
    }

    /**
     * Set the pdf document base file name.
     * If the file extension is present, it must be '.pdf' or '.PDF'.
     *
     * @param string $name File name.
     */
    public function setPDFFilename(string $name): void
    {
        $bname = \basename($name);
        if (\preg_match('/^[\w,\s-]+(\.pdf)?$/i', $bname) === 1) {
            $this->pdffilename = $bname;
            $this->encpdffilename = \rawurlencode($bname);
        }
    }

    /**
     * Set regular expression to detect withespaces or word separators.
     * The pattern delimiter must be the forward-slash character "/".
     * Some example patterns are:
     * <pre>
     * Non-Unicode or missing PCRE unicode support: "/[^\S\xa0]/"
     * Unicode and PCRE unicode support: "/(?!\xa0)[\s\p{Z}]/u"
     * Unicode and PCRE unicode support in Chinese mode: "/(?!\xa0)[\s\p{Z}\p{Lo}]/u"
     * if PCRE unicode support is turned ON ("\P" is the negate class of "\p"):
     *      \s     : any whitespace character
     *      \p{Z}  : any separator
     *      \p{Lo} : Unicode letter or ideograph that does not have lowercase and uppercase variants.
     *      \xa0   : Unicode Character 'NO-BREAK SPACE' (U+00A0)
     * </pre>
     *
     * @param string $regexp regular expression (leave empty for default).
     */
    public function setSpaceRegexp(string $regexp = '/[^\S\xa0]/'): void
    {
        $parts = \explode('/', $regexp);
        $this->spaceregexp = [
            'r' => $regexp,
            'p' => (empty($parts[1]) ? '[\s]' : $parts[1]),
            'm' => (empty($parts[2]) ? '' : $parts[2]),
        ];
    }

    /**
     * Defines the way the document is to be displayed by the viewer.
     *
     * @param int|string $zoom   The zoom to use.
     *                           It can be one of the following string values or a number indicating the
     *                           zooming factor to use.
     *                           * fullpage: displays the entire page on screen * fullwidth: uses
     *                           maximum width of window
     *                           * real: uses real size (equivalent to 100% zoom) * default: uses
     *                           viewer default mode
     * @param string     $layout The page layout. Possible values are:
     *                           * SinglePage Display one page at a time
     *                           * OneColumn Display the pages in one column
     *                           * TwoColumnLeft Display the pages in two columns,
     *                           with  odd-numbered pages on the left
     *                           * TwoColumnRight Display the pages in
     *                           two columns, with odd-numbered pages
     *                           on the right
     *                           * TwoPageLeft Display the pages two at a time,
     *                           with odd-numbered pages on the left
     *                           * TwoPageRight Display the pages two at a time,
     *                           with odd-numbered pages on the right
     * @param string     $mode   A name object specifying how the document should be displayed when opened:
     *                           * UseNone Neither document outline nor thumbnail images visible
     *                           * UseOutlines Document outline visible
     *                           * UseThumbs Thumbnail images visible
     *                           * FullScreen Full screen, with no menu bar, window controls,
     *                           or any other window visible
     *                           * UseOC (PDF 1.5) Optional content group panel visible
     *                           * UseAttachments (PDF 1.6) Attachments panel visible
     */
    public function setDisplayMode(
        int|string $zoom = 'default',
        string $layout = 'SinglePage',
        string $mode = 'UseNone'
    ): static {
        $this->display['zoom'] = (\is_numeric($zoom) || \in_array($zoom, $this::VALIDZOOM)) ? $zoom : 'default';
        $this->display['layout'] = $this->page->getLayout($layout);
        $this->display['page'] = $this->page->getDisplay($mode);
        return $this;
    }

    // ===| BARCODE |=======================================================


    /**
     * Get a barcode PDF code.
     *
     * @param string                    $type    Barcode type.
     * @param string                    $code    Barcode content.
     * @param float                     $posx    Abscissa of upper-left corner.
     * @param float                     $posy    Ordinate of upper-left corner.
     * @param int                       $width   Barcode width in user units (excluding padding).
     *                                           A negative value indicates the multiplication
     *                                           factor for each column.
     * @param int                       $height  Barcode height in user units (excluding padding).
     *                                           A negative value indicates the multiplication
     *                                           factor for each row.
     * @param array{int, int, int, int} $padding Additional padding to add around the barcode
     *                                           (top, right, bottom, left) in user units. A
     *                                           negative value indicates the multiplication
     *                                           factor for each row or column.
     * @param StyleDataOpt              $style   Array of style options.
     *
     * @throws BarcodeException in case of error
     */
    public function getBarcode(
        string $type,
        string $code,
        float $posx = 0,
        float $posy = 0,
        int $width = -1,
        int $height = -1,
        array $padding = [0, 0, 0, 0],
        array $style = []
    ): string {
        $model = $this->barcode->getBarcodeObj($type, $code, $width, $height, 'black', $padding);
        $bars = $model->getBarsArrayXYWH();
        $out = '';
        $out .= $this->graph->getStartTransform();
        $out .= $this->graph->getStyleCmd($style);
        foreach ($bars as $bar) {
            $out .= $this->graph->getBasicRect(($posx + $bar[0]), ($posy + $bar[1]), $bar[2], $bar[3], 'f');
        }

        return $out . $this->graph->getStopTransform();
    }

    // ===| SIGNATURE |=====================================================

    /**
     * Set User's Rights for the PDF Reader.
     * WARNING: This is experimental and currently doesn't work because requires a private key.
     * Check the PDF Reference 8.7.1 Transform Methods,
     * Table 8.105 Entries in the UR transform parameters dictionary.
     *
     * @param TUserRights $rights User rights:
     *        - annots (string) Names specifying additional annotation-related usage rights for the document.
     *          Valid names in PDF 1.5 and later are /Create/Delete/Modify/Copy/Import/Export, which permit
     *          the user to perform the named operation on annotations.
     *        - document (string) Names specifying additional document-wide usage rights for the document.
     *          The only defined value is "/FullSave", which permits a user to save the document along with
     *          modified form and/or annotation data.
     *        - ef (string) Names specifying additional usage rights for named embedded files in the document.
     *          Valid names are /Create/Delete/Modify/Import, which permit the user to perform the named
     *          operation on named embedded files Names specifying additional embedded-files-related usage
     *          rights for the document.
     *        - enabled (bool) If true enable user's rights on PDF reader.
     *        - form (string) Names specifying additional form-field-related usage rights for the document.
     *          Valid names are: /Add/Delete/FillIn/Import/Export/SubmitStandalone/SpawnTemplate.
     *        - formex (string) Names specifying additional form-field-related usage rights. The only valid
     *          name is BarcodePlaintext, which permits text form field data to be encoded as a plaintext
     *          two-dimensional barcode.
     *        - signature (string) Names specifying additional signature-related usage rights for the document.
     *          The only defined value is /Modify, which permits a user to apply a digital signature to an
     *          existing signature form field or clear a signed signature form field.
     */
    public function setUserRights(array $rights): void
    {
        $this->userrights = \array_merge($this->userrights, $rights);
    }

    /**
     * Enable document signature (requires the OpenSSL Library).
     * The digital signature improve document authenticity and integrity and allows
     * to enable extra features on PDF Reader.
     *
     * To create self-signed signature:
     *   openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
     * To export crt to p12:
     *   openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
     * To convert pfx certificate to pem:
     *   openssl pkcs12 -in tcpdf.pfx -out tcpdf.crt -nodes
     *
     * @param TSignature $data Signature data:
     *        - appearance (array) Signature appearance.
     *            - empty (bool) Array of empty signatures:
     *                - objid (int) Object id.
     *                - name (string) Name of the signature field.
     *                - page (int) Page number.
     *                - rect (array) Rectangle of the signature field.
     *            - name (string) Name of the signature field.
     *            - page (int) Page number.
     *            - rect (array) Rectangle of the signature field.
     *        - approval (bool) Enable approval signature eg. for PDF incremental update.
     *        - cert_type (int) The access permissions granted for this document. Valid values shall be:
     *            1 = No changes to the document shall be permitted;
     *                any change to the document shall invalidate the signature;
     *            2 = Permitted changes shall be filling in forms, instantiating page templates, and signing;
     *            other changes shall invalidate the signature;
     *            3 = Permitted changes shall be the same as for 2, as well as annotation creation,
     *                deletion, and modification;
     *            other changes shall invalidate the signature.
     *        - extracerts (string) Specifies the name of a file containing a bunch of extra certificates
     *          to include in the signature
     *            which can for example be used to help the recipient to verify the certificate that you used.
     *        - info (array) Optional information.
     *            - ContactInfo (string)
     *            - Location (string)
     *            - Name (string)
     *            - Reason (string)
     *        - password (string)
     *        - privkey (string) Private key (string or filename prefixed with 'file://').
     *        - signcert (string) Signing certificate (string or filename prefixed with 'file://').
     */
    public function setSignature(array $data): void
    {
        $this->signature = \array_merge($this->signature, $data);

        if (empty($this->signature['signcert'])) {
            throw new PdfException('Invalid signing certificate (signcert)');
        }

        if (empty($this->signature['privkey'])) {
            $this->signature['privkey'] = $this->signature['signcert'];
        }

        ++$this->pon;
        $this->objid['signature'] = $this->pon; // Signature widget annotation object id.
        ++$this->pon; // Signature appearance object id ($this->objid['signature'] + 1).

        $this->setSignAnnotRefs();

        $this->sign = true;
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

    /**
     * Set the signature timestamp.
     *
     * @param TSignTimeStamp $data Signature timestamp data:
     *        - enabled (bool) If true enable timestamp signature.
     *        - host (string) Time Stamping Authority (TSA) server (prefixed with 'https://')
     *        - username (string) TSA username or authorization PEM file.
     *        - password (string) TSA password.
     *        - cert (string) cURL optional location of TSA certificate for authorization.
     */
    public function setSignTimeStamp(array $data): void
    {
        $this->sigtimestamp = \array_merge($this->sigtimestamp, $data);

        if ($this->sigtimestamp['enabled'] && empty($this->sigtimestamp['host'])) {
            throw new PdfException('Invalid TSA host');
        }
    }

    /**
     * Get a signature appearance (page and rectangle coordinates).
     *
     * @param float $posx Abscissa of the upper-left corner.
     * @param float $posy Ordinate of the upper-left corner.
     * @param float $width Width of the signature area.
     * @param float $heigth Height of the signature area.
     * @param int $page Page number (pid).
     * @param string $name Name of the signature.
     *
     * @return array{
     *           'name': string,
     *           'page': int,
     *           'rect': string,
     *         } Array defining page and rectangle coordinates of signature appearance.
     */
    protected function getSignatureAppearanceArray(
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $heigth = 0,
        int $page = -1,
        string $name = ''
    ): array {
        $sigapp = [];

        $sigapp['page'] = ($page < 0) ? $this->page->getPageID() : $page;
        $sigapp['name'] = (empty($name)) ? 'Signature' : $name;

        $pntx = $this->toPoints($posx);
        $pnty = $this->toYUnit(($posy + $heigth), $this->page->getPage($sigapp['page'])['pheight']);
        $pntw = $this->toPoints($width);
        $pnth = $this->toPoints($heigth);

        $sigapp['rect'] = \sprintf('%F %F %F %F', $pntx, $pnty, ($pntx + $pntw), ($pnty + $pnth));

        return $sigapp;
    }

    /**
     * Set the digital signature appearance (a cliccable rectangle area to get signature properties).
     *
     * @param float $posx Abscissa of the upper-left corner.
     * @param float $posy Ordinate of the upper-left corner.
     * @param float $width Width of the signature area.
     * @param float $heigth Height of the signature area.
     * @param int $page option page number (if < 0 the current page is used).
     * @param string $name Name of the signature.
     */
    public function setSignatureAppearance(
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $heigth = 0,
        int $page = -1,
        string $name = ''
    ): void {
        $data = $this->getSignatureAppearanceArray($posx, $posy, $width, $heigth, $page, $name);
        $this->signature['appearance']['page'] = $data['page'];
        $this->signature['appearance']['name'] = $data['name'];
        $this->signature['appearance']['rect'] = $data['rect'];
        $this->setSignAnnotRefs();
    }

    /**
     * Add an empty digital signature appearance (a cliccable rectangle area to get signature properties).
     *
     * @param float $posx Abscissa of the upper-left corner.
     * @param float $posy Ordinate of the upper-left corner.
     * @param float $width Width of the signature area.
     * @param float $heigth Height of the signature area.
     * @param int $page option page number (if < 0 the current page is used).
     * @param string $name Name of the signature.
     */
    public function addEmptySignatureAppearance(
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $heigth = 0,
        int $page = -1,
        string $name = ''
    ): void {
        ++$this->pon;
        $data = $this->getSignatureAppearanceArray($posx, $posy, $width, $heigth, $page, $name);
        $this->signature['appearance']['empty'][] = [
            'objid' => $this->pon,
            'name' => $data['name'],
            'page' => $data['page'],
            'rect' => $data['rect'],
        ];
        $this->setSignAnnotRefs();
    }

    /*
    * Set the signature annotation references.
    */
    protected function setSignAnnotRefs(): void
    {
        if (empty($this->objid['signature'])) {
            return;
        }

        if (!empty($this->signature['appearance']['page'])) {
            $this->page->addAnnotRef($this->objid['signature'], $this->signature['appearance']['page']);
        }

        if (empty($this->signature['appearance']['empty'])) {
            return;
        }

        foreach ($this->signature['appearance']['empty'] as $esa) {
            $this->page->addAnnotRef($esa['objid'], $esa['page']);
        }
    }

    // ===| LAYERS |========================================================

    /**
     * Creates and return a new PDF Layer.
     *
     * @param string $name Layer name (only a-z letters and numbers). Leave empty for automatic name.
     * @param array{'view'?: bool, 'design'?: bool} $intent intended use of the graphics in the layer.
     * @param bool $print Set the printability of the layer.
     * @param bool $view Set the visibility of the layer.
     * @param bool $lock Set the lock state of the layer.
     *
     * @return string
     */
    public function newLayer(
        string $name = '',
        array $intent = [],
        bool $print = true,
        bool $view = true,
        bool $lock = true,
    ): string {
        $layer = \sprintf('LYR%03d', (\count($this->pdflayer) + 1));
        $name = \preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
        if (empty($name)) {
            $name = $layer;
        }

        $intarr = [];
        if (!empty($intent['view'])) {
            $intarr[] = '/View';
        }
        if (!empty($intent['design'])) {
            $intarr[] = '/Design';
        }

        $this->pdflayer[] = array(
            'layer' => $layer,
            'name' => $name,
            'intent' => \implode(' ', $intarr),
            'print' => $print,
            'view' => $view,
            'lock' => $lock,
            'objid' => 0,
        );

        return ' /OC /' . $layer . ' BDC' . "\n";
    }

    public function closeLayer(): string
    {
        return 'EMC' . "\n";
    }

    // ===| TOC |===========================================================

    /**
     * Add a Table of Contents (TOC) to the document.
     * The bookmars are created via the setBookmark() method.
     *
     * @param int   $page  Page number.
     * @param float $posx  Abscissa of the upper-left corner.
     * @param float $posy  Ordinate of the upper-left corner.
     * @param float $width Width of the signature area.
     * @param bool  $rtl   Right-To-Left - If true prints the TOC in RTL mode.
     * @param StyleDataOpt $linestyle Line style for the space filler.
     *
     * @return void
     */
    public function addTOC(
        int $page = -1,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        bool $rtl = false,
        array $linestyle = [
            'lineWidth' => 0.3,
            'lineCap' => 'butt',
            'lineJoin' => 'miter',
            'dashArray' => [1,1],
            'dashPhase' => 0,
            'lineColor' => 'gray',
            'fillColor' => '',
        ],
    ): void {
        if (empty($width) || $width < 0) {
            $width = $this->page->getRegion()['RW'];
        }

        $curfont = $this->font->getCurrentFont();

        // width to accomodate the number (max 9 digits space).
        $chrw = $this->toUnit($curfont['cw'][48] ?? $curfont['dw']); // 48 ASCII = '0'.
        $indent = 2 * $chrw; // each level is indented by 2 characters.
        $numwidth = 9 * $chrw; // maximum 9 digits to print the page number.
        $txtwidth = ($width - $numwidth);

        $cellSpaceT = $this->toUnit(
            $this->defcell['margin']['T'] +
            $this->defcell['padding']['T']
        );
        $cellSpaceB = $this->toUnit(
            $this->defcell['margin']['B'] +
            $this->defcell['padding']['B']
        );
        $cellSpaceH = $chrw + $this->toUnit(
            $this->defcell['margin']['L'] +
            $this->defcell['margin']['L'] +
            $this->defcell['padding']['R'] +
            $this->defcell['padding']['R']
        );

        $aligntext = 'L';
        $alignnum = 'R';
        $txt_posx = $posx;
        $num_posx = $posx + $txtwidth;
        if ($rtl) {
            $aligntext = 'R';
            $alignnum = 'L';
            $txt_posx = $posx + $numwidth;
            $num_posx = $posx;
        }

        $pid = ($page < 0) ? $this->page->getPageID() : $page;

        foreach ($this->outlines as $bmrk) {
            $font = $this->font->cloneFont(
                $this->pon,
                $curfont['idx'],
                $bmrk['s'] . (($bmrk['l'] == 0) ? 'B' : ''),
                (int) \round($curfont['size'] - $bmrk['l']),
                $curfont['spacing'],
                $curfont['stretching'],
            );

            $region = $this->page->getRegion($pid);

            if (($posy + $cellSpaceT + $cellSpaceB + $font['height']) > $region['RH']) {
                $this->page->getNextRegion($pid);
                $curpid = $this->page->getPageId();
                if ($curpid > $pid) {
                    $pid = $curpid;
                    $this->setPageContext($pid);
                }
                $region = $this->page->getRegion($pid);
                $posy = 0; // $region['RY'];
            }

            $this->page->addContent($this->graph->getStartTransform(), $pid);
            $this->page->addContent($font['out'], $pid);

            if (! empty($bmrk['c'])) {
                $col = $this->color->getPdfColor($bmrk['c']);
                $this->page->addContent($col, $pid);
            }

            if (empty($bmrk['u'])) {
                $bmrk['u'] = $this->addInternalLink($bmrk['p'], $bmrk['y']);
            }

            $offset = ($indent * $bmrk['l']);
            // add bookmark text
            $this->addTextCell(
                $bmrk['t'],
                $pid,
                $txt_posx,
                $posy,
                $txtwidth,
                0,
                $offset,
                0,
                'T',
                $aligntext,
            );

            $bbox = $this->getLastBBox();
            $wtxt = $bbox['w'];

            $pageid = $this->page->getPageID();
            if ($pageid > $pid) {
                $this->page->addContent($this->graph->getStopTransform(), $pid);
                $lnkid = $this->setLink(
                    $posx,
                    $posy,
                    $width,
                    ($region['RH'] - $posy),
                    $bmrk['u'],
                );
                $this->page->addAnnotRef($lnkid, $pid);
                $pid = $pageid;
                $this->page->addContent($this->graph->getStartTransform(), $pid);
                $this->page->addContent($font['out'], $pid);
            }

            $posy = $bbox['y'] - $cellSpaceT; // align number with the last line of the text

            // add page number
            $this->addTextCell(
                (string) ($bmrk['p'] + 1),
                $pid,
                $num_posx,
                $posy,
                $numwidth,
                0,
                0,
                0,
                'T',
                $alignnum,
            );

            $bbox = $this->getLastBBox();
            $wnum = $bbox['w'];

            // add line to fill the gap between text and number
            $line_posx = ($cellSpaceH + $offset + $posx + ($rtl ? $wnum : $wtxt));
            $line_posy = $bbox['y'] + $this->toUnit($font['ascent']);
            $line = $this->graph->getLine(
                $line_posx,
                $line_posy,
                $line_posx + ($width - $wtxt - $wnum - (2 * $cellSpaceH) - $offset),
                $line_posy,
                $linestyle,
            );
            $this->page->addContent($line, $pid);

            $lnkid = $this->setLink(
                $posx,
                $bbox['y'],
                $width,
                $bbox['h'],
                $bmrk['u'],
            );
            $this->page->addAnnotRef($lnkid, $pid);

            $this->page->addContent($this->graph->getStopTransform(), $pid);

            // Move to the next line.
            $posy = $bbox['y'] + $bbox['h'] + $cellSpaceB;
        }
    }
}
