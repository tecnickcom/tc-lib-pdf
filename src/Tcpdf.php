<?php

/**
 * Tcpdf.php
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

use Com\Tecnick\Barcode\Exception as BarcodeException;
use Com\Tecnick\Pdf\Encrypt\Encrypt as ObjEncrypt;
use Com\Tecnick\Pdf\Exception as PdfException;
use Com\Tecnick\Color\Pdf as ObjColor;
use Com\Tecnick\Pdf\Font\Stack as ObjFont;
use Com\Tecnick\Pdf\Graph\Draw as ObjGraph;
use Com\Tecnick\Pdf\Image\Import as ObjImage;

/**
 * Com\Tecnick\Pdf\Tcpdf
 *
 * Tcpdf PDF class
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type StyleDataOpt from \Com\Tecnick\Pdf\Graph\Base
 * @phpstan-import-type PageData from \Com\Tecnick\Pdf\Page\Box
 *
 * @phpstan-import-type TAnnotOpts from Output
 * @phpstan-import-type TSignature from Output
 * @phpstan-import-type TSignTimeStamp from Output
 * @phpstan-import-type TGTransparency from Output
 * @phpstan-import-type TUserRights from Output
 * @phpstan-import-type TXOBject from Output
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
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
        $this->doctime = time();
        $this->docmodtime = $this->doctime;
        $seed = new \Com\Tecnick\Pdf\Encrypt\Type\Seed();
        $this->fileid = md5($seed->encrypt('TCPDF'));
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
        if (preg_match('/^pdfa([1-3])$/', $mode, $matches) === 1) {
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
        if (sprintf('%.1F', 1.0) != '1.0') {
            setlocale(LC_NUMERIC, 'C');
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
        if ($this->isunicode && (@preg_match('/\pL/u', 'a') == 1)) {
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
        $bname = basename($name);
        if (preg_match('/^[\w,\s-]+(\.pdf)?$/i', $bname) === 1) {
            $this->pdffilename = $bname;
            $this->encpdffilename = rawurlencode($bname);
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
        $parts = explode('/', $regexp);
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
        $this->display['zoom'] = (is_numeric($zoom) || in_array($zoom, $this::VALIDZOOM)) ? $zoom : 'default';
        $this->display['layout'] = $this->page->getLayout($layout);
        $this->display['page'] = $this->page->getDisplay($mode);
        return $this;
    }

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

    /**
     * Add an annotation and returns the object id.
     *
     * @param float      $posx   Abscissa of upper-left corner.
     * @param float      $posy   Ordinate of upper-left corner.
     * @param float      $width  Width.
     * @param float      $height Height.
     * @param string     $txt    Annotation text or alternate content.
     * @param TAnnotOpts $opt    Array of options (Annotation Types) - all lowercase.
     *
     * @return int Object ID.
     */
    public function setAnnotation(
        float $posx,
        float $posy,
        float $width,
        float $height,
        string $txt,
        array $opt = [
            'subtype' => 'text',
        ]
    ): int {
        if (!empty($this->xobjtid)) {
            // Store annotationparameters for later use on a XObject template.
            $this->xobject[$this->xobjtid]['annotations'][] = [
                'n' => 0,
                'x' => $posx,
                'y' => $posy,
                'w' => $width,
                'h' => $height,
                'txt' => $txt,
                'opt' => $opt,
            ];

            return 0;
        }

        $oid = ++$this->pon;
        $this->annotation[$oid] = [
            'n' => $oid,
            'x' => $posx,
            'y' => $posy,
            'w' => $width,
            'h' => $height,
            'txt' => $txt,
            'opt' => $opt,
        ];
        switch (strtolower($opt['subtype'])) {
            case 'fileattachment':
            case 'sound':
                if (empty($opt['fs'])) {
                    throw new PdfException('Missing file attachment');
                }
                $filekey = basename((string) $opt['fs']);
                if (
                    ! empty($opt['fs'])
                    && is_string($opt['fs'])
                    && empty($this->embeddedfiles[$filekey])
                ) {
                    $this->embeddedfiles[$filekey] = [
                        'a' => 0,
                        'f' => ++$this->pon,
                        'n' => ++$this->pon,
                        'file' => $opt['fs'],
                    ];
                }
        }

        // Add widgets annotation's icons
        if (isset($opt['mk']['i'])) {
            $this->image->add($opt['mk']['i']);
        }

        if (isset($opt['mk']['ri'])) {
            $this->image->add($opt['mk']['ri']);
        }

        if (isset($opt['mk']['ix'])) {
            $this->image->add($opt['mk']['ix']);
        }

        return $oid;
    }

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
        $this->userrights = array_merge($this->userrights, $rights);
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
        $this->signature = array_merge($this->signature, $data);

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
        $this->sigtimestamp = array_merge($this->sigtimestamp, $data);

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

        $sigapp['page'] = ($page < 1) ? $this->page->getPage()['pid'] : $page;
        $sigapp['name'] = (empty($name)) ? 'Signature' : $name;

        $pntx = $this->toPoints($posx);
        $pnty = $this->toYUnit(($posy + $heigth), $this->page->getPage($sigapp['page'])['pheight']);
        $pntw = $this->toPoints($width);
        $pnth = $this->toPoints($heigth);

        $sigapp['rect'] = sprintf('%F %F %F %F', $pntx, $pnty, ($pntx + $pntw), ($pnty + $pnth));

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

    /**
     * Create a new XObject template and return the object id.
     *
     * An XObject Template is a PDF block that is a self-contained description
     * of any sequence of graphics objects (including path objects, text objects,
     * and sampled images). An XObject Template may be painted multiple times,
     * either on several pages or at several locations on the same page and
     * produces the same results each time, subject only to the graphics state
     * at the time it is invoked.
     *
     * @param float $width  Width of the XObject.
     * @param float $heigth Height of the XObject.
     * @param ?TGTransparency $transpgroup Optional group attributes.
     *
     * @return TXOBject XObject template object.
     */
    public function newXObjectTemplate(
        float $width = 0,
        float $heigth = 0,
        ?array $transpgroup = null,
    ): array {
        $oid = ++$this->pon;
        $tid = 'XT' . $oid;
        $this->xobjtid = $tid;

        $region = $this->page->getRegion();

        if (empty($width) || $width < 0) {
            $width = $region['RW'];
        }

        if (empty($heigth) || $heigth < 0) {
            $heigth = $region['RH'];
        }

        $this->xobject[$tid] = [
            'id' => $tid,
            'n' => $oid,
            'x' => 0,
            'y' => 0,
            'w' => $width,
            'h' => $heigth,
            'outdata' => '',
            'transpgroup' => $transpgroup,
        ];

        return $this->xobject[$tid];
    }

    /**
     * Exit from the XObject template mode.
     *
     * See: newXObjectTemplate.
     */
    public function exitXObjectTemplate(): void
    {
        $this->xobjtid = '';
    }

    /**
     * Returns the PDF code to render the specified XObject template.
     *
     * See: newXObjectTemplate.
     *
     * @param TXOBject    $xobj        The XObject Template object as returned by the newXObjectTemplate method.
     * @param float       $posx        Abscissa of upper-left corner.
     * @param float       $posy        Ordinate of upper-left corner.
     * @param float       $width       Width.
     * @param float       $height      Height.
     * @param string      $valign      Vertical alignment inside the specified box: T=top; C=center; B=bottom.
     * @param string      $halign      Horizontal alignment inside the specified box: L=left; C=center; R=right.
     *
     * @return string The PDF code to render the specified XObject template.
     */
    public function getXObjectTemplate(
        array $xobj,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        string $valign = 'T',
        string $halign = 'L',
    ): string {
        $this->xobjtid = '';
        $region = $this->page->getRegion();

        if (empty($width) || $width < 0) {
            $width = min($xobj['w'], $region['RW']);
        }

        if (empty($height) || $height < 0) {
            $height = min($xobj['h'], $region['RH']);
        }

        $tplx = $this->cellHPos($posx, $width, $halign, $this->defcell);
        $tply = $this->cellVPos($posy, $height, $valign, $this->defcell);

        $this->bbox[] = [
            'x' => $tplx,
            'y' => $tply,
            'w' => $width,
            'h' => $height,
        ];

        $out = $this->graph->getStartTransform();
        $ctm = [
            0 => ($width / $xobj['w']),
            1 => 0,
            2 => 0,
            3 => ($height / $xobj['h']),
            4 => $this->toPoints($tplx),
            5 => $this->toYPoints($tply + $height),
        ];
        $out .= $this->graph->getTransformation($ctm);
        $out .= '/' . $xobj['id'] . ' Do' . "\n";
        $out .= $this->graph->getStopTransform();

        if (!empty($xobj['annotations'])) {
            foreach ($xobj['annotations'] as $annot) {
                // transform original coordinates
                $clt = $this->graph->getCtmProduct(
                    $ctm,
                    array(
                        1,
                        0,
                        0,
                        1,
                        $this->toPoints($annot['x']),
                        $this->toPoints(-$annot['y']),
                    ),
                );
                $anx = $this->toUnit($clt[4]);
                $any = $this->toYUnit($clt[5] + $this->toUnit($height));

                $crb = $this->graph->getCtmProduct(
                    $ctm,
                    array(
                        1,
                        0,
                        0,
                        1,
                        $this->toPoints(($annot['x'] + $annot['w'])),
                        $this->toPoints((-$annot['y'] - $annot['h'])),
                    ),
                );
                $anw = $this->toUnit($crb[4]) - $anx;
                $anh = $this->toYUnit($crb[5] + $this->toUnit($height)) - $any;

                $out .= $this->setAnnotation(
                    $anx,
                    $any,
                    $anw,
                    $anh,
                    $annot['txt'],
                    $annot['opt']
                );
            }
        }

        return $out;
    }
}
