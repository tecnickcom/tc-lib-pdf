<?php

/**
 * Tcpdf.php
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

use Com\Tecnick\Barcode\Exception as BarcodeException;
use Com\Tecnick\Pdf\Encrypt\Encrypt as ObjEncrypt;
use Com\Tecnick\Pdf\Exception as PdfException;
use Com\Tecnick\Pdf\Table\Table;
use Com\Tecnick\Pdf\Table\TableCell;
use Com\Tecnick\Pdf\RichText;
use Com\Tecnick\Pdf\Font\VariableFont;
use Com\Tecnick\Pdf\Forms\FieldCalculation;
use Com\Tecnick\Pdf\Forms\FieldValidator;
use Com\Tecnick\Pdf\Forms\ConditionalVisibility;
use Com\Tecnick\Pdf\Manipulate;
use Com\Tecnick\Pdf\Html\HtmlRenderer;

/**
 * Com\Tecnick\Pdf\Tcpdf
 *
 * Tcpdf PDF class
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
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
     * Supported modes:
     * - 'pdfa1', 'pdfa1a', 'pdfa1b': PDF/A-1 with optional conformance level
     * - 'pdfa2', 'pdfa2a', 'pdfa2b', 'pdfa2u': PDF/A-2 with optional conformance level
     * - 'pdfa3', 'pdfa3a', 'pdfa3b', 'pdfa3u': PDF/A-3 with optional conformance level
     * - 'pdfx': PDF/X mode
     *
     * Conformance levels:
     * - 'a': Accessible (tagged PDF + Unicode)
     * - 'b': Basic (visual appearance only)
     * - 'u': Unicode (basic + Unicode mapping, PDF/A-2 and PDF/A-3 only)
     *
     * @param string $mode Input PDF/A mode.
     */
    protected function setPDFMode(string $mode): void
    {
        $this->pdfx = ($mode == 'pdfx');
        $this->pdfa = 0;
        $this->pdfaConformance = 'B';

        // Match pdfa1, pdfa2, pdfa3 with optional conformance level (a, b, u)
        $matches = [];
        if (\preg_match('/^pdfa([1-3])([abu])?$/i', $mode, $matches) === 1) {
            $this->pdfa = (int) $matches[1];

            // Set conformance level if specified
            if (isset($matches[2]) && $matches[2] !== '') {
                $conformance = \strtoupper($matches[2]);
                // 'U' conformance only valid for PDF/A-2 and PDF/A-3
                if ($conformance === 'U' && $this->pdfa === 1) {
                    $conformance = 'B'; // Fallback to 'B' for PDF/A-1
                }
                $this->pdfaConformance = $conformance;
            }
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

    // ===| TABLE |=========================================================

    /**
     * Create a new table object.
     *
     * Usage:
     * ```php
     * $table = $pdf->createTable(['borderWidth' => 0.3, 'cellPadding' => 2]);
     * $table->setPosition(10, 50);
     * $table->setWidth(190);
     * $table->addHeaderRow(['Name', 'Age', 'City']);
     * $table->addRow(['John Doe', '30', 'New York']);
     * $table->addRow(['Jane Smith', '25', 'Los Angeles']);
     * $table->render();
     * ```
     *
     * @param array{
     *     'borderWidth'?: float,
     *     'borderColor'?: string,
     *     'backgroundColor'?: string,
     *     'headerBackgroundColor'?: string,
     *     'headerTextColor'?: string,
     *     'cellPadding'?: float,
     *     'width'?: float,
     * } $style Table style options
     *
     * @return Table
     */
    public function createTable(array $style = []): Table
    {
        return new Table($this, $style);
    }

    /**
     * Create a new table cell with custom properties.
     *
     * Usage:
     * ```php
     * $cell = $pdf->createTableCell('Content', 2, 1, ['backgroundColor' => '#ffff00']);
     * $table->addRow([$cell, 'Other content']);
     * ```
     *
     * @param string $content Cell content
     * @param int $colspan Number of columns to span
     * @param int $rowspan Number of rows to span
     * @param array{
     *     'backgroundColor'?: string,
     *     'borderColor'?: string,
     *     'borderWidth'?: float,
     *     'borderTop'?: bool,
     *     'borderRight'?: bool,
     *     'borderBottom'?: bool,
     *     'borderLeft'?: bool,
     *     'paddingTop'?: float,
     *     'paddingRight'?: float,
     *     'paddingBottom'?: float,
     *     'paddingLeft'?: float,
     *     'textColor'?: string,
     *     'fontFamily'?: string,
     *     'fontSize'?: float,
     *     'fontStyle'?: string,
     * } $style Cell style options
     *
     * @return TableCell
     */
    public function createTableCell(
        string $content = '',
        int $colspan = 1,
        int $rowspan = 1,
        array $style = []
    ): TableCell {
        return new TableCell($content, $colspan, $rowspan, $style);
    }

    // ===| RICH TEXT |=====================================================

    /**
     * Create a new RichText builder object.
     *
     * Rich text content can be used in FreeText annotations and form fields.
     * It uses a subset of XHTML for formatting.
     *
     * Usage:
     * ```php
     * $rt = $pdf->createRichText();
     * $rt->addBold('Important: ')
     *    ->addText('This is a message with ')
     *    ->addColored('colored text', '#ff0000')
     *    ->addText(' and ')
     *    ->addItalic('italic text');
     * $content = $rt->build();
     * ```
     *
     * @param string $fontFamily Default font family
     * @param float $fontSize Default font size in points
     * @param string $textColor Default text color (hex format)
     *
     * @return RichText
     */
    public function createRichText(
        string $fontFamily = 'Helvetica',
        float $fontSize = 12,
        string $textColor = '#000000'
    ): RichText {
        return new RichText($fontFamily, $fontSize, $textColor);
    }

    /**
     * Add a FreeText annotation with rich text content.
     *
     * FreeText annotations display text directly on the page without
     * requiring the user to open a popup. Rich text allows formatting.
     *
     * Usage:
     * ```php
     * $rt = $pdf->createRichText();
     * $rt->addBold('Note: ')->addText('Review this section');
     *
     * $pdf->addRichTextAnnotation(
     *     20, 100, 80, 20,
     *     $rt->build(),
     *     'Note: Review this section',  // Plain text fallback
     *     ['borderColor' => '#ff0000']
     * );
     * ```
     *
     * @param float $x X position in user units
     * @param float $y Y position in user units
     * @param float $w Width in user units
     * @param float $h Height in user units
     * @param string $richContent Rich text XHTML content
     * @param string $plainText Plain text fallback
     * @param array{
     *     'fontKey'?: string,
     *     'fontSize'?: float,
     *     'textColor'?: string,
     *     'borderColor'?: string,
     *     'borderWidth'?: float,
     *     'backgroundColor'?: string,
     *     'align'?: int,
     * } $options Additional options
     *
     * @return int Annotation object ID
     */
    public function addRichTextAnnotation(
        float $x,
        float $y,
        float $w,
        float $h,
        string $richContent,
        string $plainText = '',
        array $options = []
    ): int {
        $fontKey = $options['fontKey'] ?? 'Helv';
        $fontSize = $options['fontSize'] ?? 12;
        $textColor = $options['textColor'] ?? '#000000';
        $align = $options['align'] ?? 0; // 0=left, 1=center, 2=right

        // Create Default Appearance string
        $da = RichText::createDA($fontKey, $fontSize, $textColor);

        // Build annotation options
        $opt = [
            'subtype' => 'FreeText',
            'da' => $da,
            'q' => $align,
            'rc' => $richContent,
        ];

        // Add border if specified
        if (isset($options['borderColor']) || isset($options['borderWidth'])) {
            $opt['bs'] = [
                's' => 'S',
                'w' => $options['borderWidth'] ?? 1,
            ];
        }

        // Add background color
        if (isset($options['backgroundColor'])) {
            $hex = ltrim($options['backgroundColor'], '#');
            $opt['ic'] = [
                hexdec(substr($hex, 0, 2)) / 255,
                hexdec(substr($hex, 2, 2)) / 255,
                hexdec(substr($hex, 4, 2)) / 255,
                1.0
            ];
        }

        return $this->setAnnotation($x, $y, $w, $h, $plainText, $opt);
    }

    /**
     * Add a rich text form field (text field with formatting support).
     *
     * Creates a text field that supports rich text input and display.
     *
     * Usage:
     * ```php
     * $rt = $pdf->createRichText();
     * $rt->addBold('Default ')
     *    ->addItalic('formatted ')
     *    ->addText('text');
     *
     * $pdf->addRichTextField(
     *     'notes',
     *     20, 100, 100, 30,
     *     $rt->build(),
     *     ['multiline' => true]
     * );
     * ```
     *
     * @param string $name Field name
     * @param float $x X position in user units
     * @param float $y Y position in user units
     * @param float $w Width in user units
     * @param float $h Height in user units
     * @param string $richValue Initial rich text value
     * @param array{
     *     'fontKey'?: string,
     *     'fontSize'?: float,
     *     'textColor'?: string,
     *     'borderColor'?: string,
     *     'backgroundColor'?: string,
     *     'multiline'?: bool,
     *     'readonly'?: bool,
     * } $options Field options
     *
     * @return int Field object ID
     */
    public function addRichTextField(
        string $name,
        float $x,
        float $y,
        float $w,
        float $h,
        string $richValue = '',
        array $options = []
    ): int {
        $fontKey = $options['fontKey'] ?? 'Helv';
        $fontSize = $options['fontSize'] ?? 12;
        $textColor = $options['textColor'] ?? '#000000';

        // Build field properties
        $prop = [
            'richText' => 'true',
            'richValue' => $richValue,
        ];

        if (!empty($options['multiline'])) {
            $prop['multiline'] = 'true';
        }

        if (!empty($options['readonly'])) {
            $prop['readonly'] = 'true';
        }

        // Border styling
        if (isset($options['borderColor'])) {
            $prop['strokeColor'] = $options['borderColor'];
        }

        // Background styling
        if (isset($options['backgroundColor'])) {
            $prop['fillColor'] = $options['backgroundColor'];
        }

        // Default appearance
        $da = RichText::createDA($fontKey, $fontSize, $textColor);

        return $this->setFormField(
            'text',
            $name,
            $x,
            $y,
            $w,
            $h,
            $prop,
            [], // styles
            '', // javascript action
            $da
        );
    }

    // ===| VARIABLE FONTS |================================================

    /**
     * Create a VariableFont analyzer for a font file.
     *
     * Variable fonts contain multiple styles in a single file with
     * continuous variation along axes (weight, width, slant, etc.).
     *
     * Usage:
     * ```php
     * $vf = $pdf->analyzeVariableFont('/path/to/font.ttf');
     *
     * if ($vf->isVariableFont()) {
     *     // Get available axes
     *     $axes = $vf->getAxes();
     *     foreach ($axes as $tag => $axis) {
     *         echo "{$axis['name']}: {$axis['minValue']} - {$axis['maxValue']}\n";
     *     }
     *
     *     // Set specific values
     *     $vf->setWeight(600);  // Semi-bold
     *     $vf->setWidth(75);    // Condensed
     *
     *     // Get CSS font-variation-settings string
     *     echo $vf->getVariationSettings();
     * }
     * ```
     *
     * Note: Full variable font rendering requires PDF 2.0 and compatible
     * viewers. For maximum compatibility, use static font instances or
     * the web font CSS approach with font-variation-settings.
     *
     * @param string $fontPath Path to the font file
     * @return VariableFont
     */
    public function analyzeVariableFont(string $fontPath): VariableFont
    {
        return new VariableFont($fontPath);
    }

    /**
     * Get information about a variable font's axes.
     *
     * @param string $fontPath Path to the font file
     * @return array{
     *     'isVariable': bool,
     *     'axes': array<string, array{
     *         'tag': string,
     *         'name': string,
     *         'minValue': float,
     *         'defaultValue': float,
     *         'maxValue': float,
     *     }>,
     *     'instances': array<string, array{
     *         'name': string,
     *         'coordinates': array<string, float>,
     *     }>,
     * }
     */
    public function getVariableFontInfo(string $fontPath): array
    {
        $vf = new VariableFont($fontPath);

        return [
            'isVariable' => $vf->isVariableFont(),
            'axes' => $vf->getAxes(),
            'instances' => $vf->getInstances(),
        ];
    }

    // =========================================================================
    // ADVANCED ACROFORMS - Calculated Fields, Validation, Conditional Visibility
    // =========================================================================

    /**
     * Create a field calculation builder.
     *
     * Field calculations allow automatic computation of values based on
     * other form fields. Common uses include:
     * - Sum totals (e.g., invoice line items)
     * - Averages (e.g., grade calculations)
     * - Products (e.g., quantity * price)
     * - Custom formulas
     *
     * Example:
     * ```php
     * // Sum of multiple fields
     * $calc = $pdf->createFieldCalculation('total', FieldCalculation::TYPE_SUM)
     *     ->setSourceFields(['item1', 'item2', 'item3']);
     *
     * // Or use static factory methods
     * $calc = FieldCalculation::sum('total', ['item1', 'item2', 'item3']);
     * $calc = FieldCalculation::product('lineTotal', ['quantity', 'price']);
     * $calc = FieldCalculation::average('avgScore', ['score1', 'score2', 'score3']);
     *
     * // Custom JavaScript expression
     * $calc = FieldCalculation::custom('tax', 'subtotal * 0.1', ['subtotal']);
     * ```
     *
     * @param string $targetField Name of the field to receive calculated value
     * @param array<string> $sourceFields Source field names
     * @param string $type Calculation type (use FieldCalculation::TYPE_* constants)
     * @return FieldCalculation
     */
    public function createFieldCalculation(
        string $targetField,
        array $sourceFields = [],
        string $type = FieldCalculation::TYPE_SUM
    ): FieldCalculation {
        return match ($type) {
            FieldCalculation::TYPE_SUM => FieldCalculation::sum($targetField, $sourceFields),
            FieldCalculation::TYPE_PRODUCT => FieldCalculation::product($targetField, $sourceFields),
            FieldCalculation::TYPE_AVERAGE => FieldCalculation::average($targetField, $sourceFields),
            FieldCalculation::TYPE_MIN => FieldCalculation::min($targetField, $sourceFields),
            FieldCalculation::TYPE_MAX => FieldCalculation::max($targetField, $sourceFields),
            default => FieldCalculation::sum($targetField, $sourceFields),
        };
    }

    /**
     * Create a field validator builder.
     *
     * Field validators define validation rules for form fields.
     * Validation is performed via JavaScript when the user exits
     * the field or submits the form.
     *
     * Example:
     * ```php
     * // Create validator with multiple rules
     * $validator = $pdf->createFieldValidator('email')
     *     ->required('Email is required')
     *     ->email('Please enter a valid email');
     *
     * // Numeric validation with range
     * $validator = $pdf->createFieldValidator('age')
     *     ->required()
     *     ->integer('Please enter a whole number')
     *     ->range(18, 120, 'Age must be between 18 and 120');
     *
     * // Custom validation
     * $validator = $pdf->createFieldValidator('username')
     *     ->required()
     *     ->length(3, 20, 'Username must be 3-20 characters')
     *     ->regex('^[a-zA-Z0-9_]+$', 'Only letters, numbers, and underscores allowed');
     * ```
     *
     * @param string $fieldName Name of the field to validate
     * @return FieldValidator
     */
    public function createFieldValidator(string $fieldName): FieldValidator
    {
        return FieldValidator::forField($fieldName);
    }

    /**
     * Create a conditional visibility rule builder.
     *
     * Conditional visibility allows showing or hiding form fields
     * based on the values of other fields. This is useful for:
     * - Progressive disclosure forms
     * - Conditional sections
     * - Dynamic form layouts
     *
     * Example:
     * ```php
     * // Show field when checkbox is checked
     * $visibility = $pdf->createConditionalVisibility('otherDetails')
     *     ->showWhenChecked('hasOther');
     *
     * // Show multiple fields when dropdown equals value
     * $visibility = $pdf->createConditionalVisibility(['address', 'city', 'zip'])
     *     ->showWhenEquals('contactMethod', 'mail');
     *
     * // Complex conditions
     * $visibility = $pdf->createConditionalVisibility('discountField')
     *     ->showWhenGreaterThan('orderTotal', 100)
     *     ->andWhen('memberStatus', ConditionalVisibility::OP_EQUALS, 'premium');
     * ```
     *
     * @param string|array<string> $targetFields Field(s) to show/hide
     * @return ConditionalVisibility
     */
    public function createConditionalVisibility(string|array $targetFields): ConditionalVisibility
    {
        return ConditionalVisibility::forFields($targetFields);
    }

    /**
     * Add a text field with calculation.
     *
     * Creates a text field that automatically calculates its value
     * from other fields.
     *
     * @param string $name Field name
     * @param float $x X position
     * @param float $y Y position
     * @param float $w Width
     * @param float $h Height
     * @param FieldCalculation $calculation Calculation definition
     * @param array<string, mixed> $options Additional field options
     * @return int Field object number
     */
    public function addCalculatedField(
        string $name,
        float $x,
        float $y,
        float $w,
        float $h,
        FieldCalculation $calculation,
        array $options = []
    ): int {
        // Generate JavaScript for calculation
        $jsCode = $calculation->toJavaScript();

        // Set up the field with calculation action
        $options['aa'] = ($options['aa'] ?? '') . '/C << /S /JavaScript /JS (' . $this->escapeJavaScript($jsCode) . ') >>';

        // Make field read-only by default for calculated fields
        if (!isset($options['readonly'])) {
            $options['readonly'] = true;
        }

        return $this->form->addTextField(
            $this->pon,
            $name,
            $this->toPoints($x),
            $this->toYPoints($y, $h),
            $this->toPoints($w),
            $this->toPoints($h),
            $options
        );
    }

    /**
     * Add a text field with validation.
     *
     * Creates a text field with JavaScript validation rules.
     *
     * @param string $name Field name
     * @param float $x X position
     * @param float $y Y position
     * @param float $w Width
     * @param float $h Height
     * @param FieldValidator $validator Validation rules
     * @param array<string, mixed> $options Additional field options
     * @return int Field object number
     */
    public function addValidatedField(
        string $name,
        float $x,
        float $y,
        float $w,
        float $h,
        FieldValidator $validator,
        array $options = []
    ): int {
        // Generate JavaScript for validation
        $validateJs = $validator->toJavaScript();
        $keystrokeJs = $validator->toKeystrokeJavaScript();

        // Add validation actions
        $aa = $options['aa'] ?? '';
        if ($validateJs !== '') {
            $aa .= '/V << /S /JavaScript /JS (' . $this->escapeJavaScript($validateJs) . ') >>';
        }
        if ($keystrokeJs !== '') {
            $aa .= '/K << /S /JavaScript /JS (' . $this->escapeJavaScript($keystrokeJs) . ') >>';
        }
        $options['aa'] = $aa;

        return $this->form->addTextField(
            $this->pon,
            $name,
            $this->toPoints($x),
            $this->toYPoints($y, $h),
            $this->toPoints($w),
            $this->toPoints($h),
            $options
        );
    }

    /**
     * Apply conditional visibility to fields.
     *
     * This adds the necessary JavaScript to show/hide fields based
     * on conditions. The JavaScript is added to the source field(s)
     * as a change action.
     *
     * @param ConditionalVisibility $visibility Visibility rules
     * @return self
     */
    public function applyConditionalVisibility(ConditionalVisibility $visibility): self
    {
        $jsCode = $visibility->toJavaScript();
        if ($jsCode === '') {
            return $this;
        }

        // Add the visibility script to document-level JavaScript
        $this->addJavaScript('visibility_' . implode('_', $visibility->getTargetFields()), $jsCode);

        return $this;
    }

    /**
     * Add document-level JavaScript.
     *
     * @param string $name Script name
     * @param string $script JavaScript code
     * @return self
     */
    public function addJavaScript(string $name, string $script): self
    {
        $this->jsobjects[$name] = $script;
        return $this;
    }

    /**
     * Escape JavaScript string for PDF.
     *
     * @param string $js JavaScript code
     * @return string Escaped JavaScript
     */
    protected function escapeJavaScript(string $js): string
    {
        return str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', '\\r', '\\n'],
            $js
        );
    }

    // ===| PDF MANIPULATION |=================================================

    /**
     * Create a new PDF Merger instance.
     *
     * The merger allows combining multiple PDF files into one.
     *
     * Usage:
     * ```php
     * $merger = $pdf->createMerger();
     * $merger->addFile('document1.pdf')
     *        ->addFile('document2.pdf', [1, 3, 5]) // specific pages
     *        ->addFile('document3.pdf', '1-5');    // page range
     * $merged = $merger->merge();
     * ```
     *
     * @return Manipulate\PdfMerger
     */
    public function createMerger(): Manipulate\PdfMerger
    {
        return new Manipulate\PdfMerger();
    }

    /**
     * Create a new PDF Splitter instance.
     *
     * The splitter allows extracting pages from a PDF document.
     *
     * Usage:
     * ```php
     * $splitter = $pdf->createSplitter();
     * $splitter->loadFile('document.pdf');
     * $page1 = $splitter->extractPage(1);
     * $pages = $splitter->extractPages([1, 3, 5]);
     * $chunks = $splitter->splitByPageCount(10);
     * ```
     *
     * @return Manipulate\PdfSplitter
     */
    public function createSplitter(): Manipulate\PdfSplitter
    {
        return new Manipulate\PdfSplitter();
    }

    /**
     * Create a new PDF Stamper instance.
     *
     * The stamper allows adding watermarks, stamps, and overlays to PDFs.
     *
     * Usage:
     * ```php
     * $stamper = $pdf->createStamper();
     * $stamper->loadFile('document.pdf')
     *         ->addWatermark('CONFIDENTIAL')
     *         ->addPageNumbers('Page {page} of {total}')
     *         ->addDateStamp();
     * $stamped = $stamper->apply();
     * ```
     *
     * @return Manipulate\PdfStamper
     */
    public function createStamper(): Manipulate\PdfStamper
    {
        return new Manipulate\PdfStamper();
    }

    /**
     * Merge multiple PDF files into one.
     *
     * Convenience method that creates a merger, adds files, and returns result.
     *
     * @param array<string> $files Array of PDF file paths
     * @return string Merged PDF content
     * @throws Exception If merge fails
     */
    public function mergePdfFiles(array $files): string
    {
        $merger = $this->createMerger();
        foreach ($files as $file) {
            $merger->addFile($file);
        }
        return $merger->merge();
    }

    /**
     * Split a PDF file into individual pages.
     *
     * @param string $filePath Path to source PDF
     * @param string $outputDir Output directory for split pages
     * @param string $prefix Filename prefix
     * @return array<string> Array of created file paths
     * @throws Exception If split fails
     */
    public function splitPdfFile(string $filePath, string $outputDir, string $prefix = 'page_'): array
    {
        $splitter = $this->createSplitter();
        $splitter->loadFile($filePath);
        return $splitter->splitToFiles($outputDir, $prefix);
    }

    /**
     * Add a watermark to a PDF file.
     *
     * @param string $filePath Path to source PDF
     * @param string $watermarkText Watermark text
     * @param string $outputPath Output file path
     * @return bool True on success
     * @throws Exception If operation fails
     */
    public function addWatermarkToFile(string $filePath, string $watermarkText, string $outputPath): bool
    {
        $stamper = $this->createStamper();
        $stamper->loadFile($filePath)
                ->addWatermark($watermarkText);
        return $stamper->applyToFile($outputPath);
    }

    /**
     * Create a new PDF Metadata Editor instance.
     *
     * The metadata editor allows modifying PDF document information properties.
     *
     * Usage:
     * ```php
     * $editor = $pdf->createMetadataEditor();
     * $editor->loadFile('document.pdf')
     *        ->setTitle('New Title')
     *        ->setAuthor('John Doe')
     *        ->setSubject('Document Subject')
     *        ->setKeywords('pdf, document, keywords');
     * $modified = $editor->apply();
     * ```
     *
     * @return Manipulate\PdfMetadataEditor
     */
    public function createMetadataEditor(): Manipulate\PdfMetadataEditor
    {
        return new Manipulate\PdfMetadataEditor();
    }

    /**
     * Edit metadata of an existing PDF file.
     *
     * @param string $filePath Path to source PDF
     * @param array<string, string> $metadata Associative array of metadata fields
     * @param string $outputPath Output file path
     * @return bool True on success
     * @throws Exception If operation fails
     */
    public function editPdfMetadata(string $filePath, array $metadata, string $outputPath): bool
    {
        $editor = $this->createMetadataEditor();
        $editor->loadFile($filePath)->setMetadata($metadata);
        return $editor->applyToFile($outputPath);
    }

    /**
     * Create a new PDF Bookmark Manager instance.
     *
     * The bookmark manager allows adding, editing, and removing PDF bookmarks (outlines).
     *
     * Usage:
     * ```php
     * $manager = $pdf->createBookmarkManager();
     * $manager->loadFile('document.pdf')
     *         ->addBookmark('Chapter 1', 1)
     *         ->addBookmark('Section 1.1', 2, 1)
     *         ->addBookmark('Chapter 2', 5);
     * $modified = $manager->apply();
     * ```
     *
     * @return Manipulate\PdfBookmarkManager
     */
    public function createBookmarkManager(): Manipulate\PdfBookmarkManager
    {
        return new Manipulate\PdfBookmarkManager();
    }

    /**
     * Add bookmarks to an existing PDF file.
     *
     * @param string $filePath Path to source PDF
     * @param array<array{title: string, page: int, level?: int}> $bookmarks Bookmarks to add
     * @param string $outputPath Output file path
     * @return bool True on success
     * @throws Exception If operation fails
     */
    public function addPdfBookmarks(string $filePath, array $bookmarks, string $outputPath): bool
    {
        $manager = $this->createBookmarkManager();
        $manager->loadFile($filePath)->addBookmarks($bookmarks);
        return $manager->applyToFile($outputPath);
    }

    /**
     * Create a new PDF Barcode Stamper instance.
     *
     * The barcode stamper allows adding barcodes and QR codes to PDF documents.
     *
     * Usage:
     * ```php
     * $stamper = $pdf->createBarcodeStamper();
     * $stamper->loadFile('document.pdf')
     *         ->addQRCode('https://example.com', 50, 700, 100)
     *         ->addCode128('ABC123', 50, 600, 150, 50);
     * $modified = $stamper->apply();
     * ```
     *
     * @return Manipulate\PdfBarcodeStamper
     */
    public function createBarcodeStamper(): Manipulate\PdfBarcodeStamper
    {
        return new Manipulate\PdfBarcodeStamper();
    }

    /**
     * Add a QR code to an existing PDF file.
     *
     * @param string $filePath Path to source PDF
     * @param string $content QR code content
     * @param float $x X position
     * @param float $y Y position
     * @param float $size Size
     * @param string $outputPath Output file path
     * @param array<int>|string $pages Pages to add to
     * @return bool True on success
     * @throws Exception If operation fails
     */
    public function addQRCodeToPdf(
        string $filePath,
        string $content,
        float $x,
        float $y,
        float $size,
        string $outputPath,
        array|string $pages = 'all'
    ): bool {
        $stamper = $this->createBarcodeStamper();
        $stamper->loadFile($filePath)
                ->addQRCode($content, $x, $y, $size, $pages);
        return $stamper->applyToFile($outputPath);
    }

    /**
     * Create a new PDF Page Rotator instance.
     *
     * The rotator allows rotating pages in PDF documents.
     *
     * Usage:
     * ```php
     * $rotator = $pdf->createPageRotator();
     * $rotator->loadFile('document.pdf')
     *         ->rotatePage(1, 90)
     *         ->rotatePages(180, [2, 4]);
     * $modified = $rotator->apply();
     * ```
     *
     * @return Manipulate\PdfPageRotator
     */
    public function createPageRotator(): Manipulate\PdfPageRotator
    {
        return new Manipulate\PdfPageRotator();
    }

    /**
     * Rotate pages in an existing PDF file.
     *
     * @param string $filePath Path to source PDF
     * @param int $degrees Rotation in degrees (0, 90, 180, 270)
     * @param string $outputPath Output file path
     * @param array<int>|string $pages Pages to rotate
     * @return bool True on success
     * @throws Exception If operation fails
     */
    public function rotatePdfPages(
        string $filePath,
        int $degrees,
        string $outputPath,
        array|string $pages = 'all'
    ): bool {
        $rotator = $this->createPageRotator();
        $rotator->loadFile($filePath)->rotatePages($degrees, $pages);
        return $rotator->applyToFile($outputPath);
    }

    // ===| HTML RENDERING |=================================================

    /**
     * Create a new HTML renderer instance.
     *
     * The HTML renderer converts HTML content with CSS styling to PDF output.
     *
     * Supported HTML elements:
     * - Block elements: p, div, h1-h6, blockquote, pre
     * - Inline elements: span, b, strong, i, em, u, s, strike
     * - Lists: ul, ol, li
     * - Tables: table, tr, th, td
     * - Links: a
     * - Images: img
     * - Line breaks: br, hr
     *
     * Supported CSS properties:
     * - Font: font-family, font-size, font-weight, font-style
     * - Color: color, background-color
     * - Text: text-align, text-decoration, line-height
     *
     * Usage:
     * ```php
     * $renderer = $pdf->createHtmlRenderer();
     * $renderer->setMargins(15, 15, 15);
     * $renderer->render('<h1>Title</h1><p>Paragraph content</p>');
     * ```
     *
     * @return HtmlRenderer
     */
    public function createHtmlRenderer(): HtmlRenderer
    {
        return new HtmlRenderer($this);
    }

    /**
     * Write HTML content directly to the current page.
     *
     * This is a convenience method that creates an HTML renderer
     * and renders the content at the specified position.
     *
     * Usage:
     * ```php
     * $pdf->addPage();
     * $y = $pdf->writeHTML('<h1>Welcome</h1><p>This is a test.</p>');
     * // $y contains the final Y position after rendering
     * ```
     *
     * @param string $html HTML content to render
     * @param float $x X position (0 = use left margin)
     * @param float $y Y position (0 = current position)
     * @param float $width Content width (0 = auto)
     * @return float Final Y position after rendering
     */
    public function writeHTML(string $html, float $x = 0, float $y = 0, float $width = 0): float
    {
        $renderer = $this->createHtmlRenderer();
        return $renderer->render($html, $x, $y, $width);
    }

    /**
     * Write HTML content with custom margins.
     *
     * @param string $html HTML content to render
     * @param float $marginLeft Left margin
     * @param float $marginTop Top margin
     * @param float $marginRight Right margin
     * @return float Final Y position after rendering
     */
    public function writeHTMLWithMargins(
        string $html,
        float $marginLeft = 10,
        float $marginTop = 10,
        float $marginRight = 10
    ): float {
        $renderer = $this->createHtmlRenderer();
        $renderer->setMargins($marginLeft, $marginTop, $marginRight);
        return $renderer->render($html);
    }
}
