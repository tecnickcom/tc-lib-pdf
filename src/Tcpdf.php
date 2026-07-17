<?php

declare(strict_types=1);

/**
 * Tcpdf.php
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf;

use Com\Tecnick\Barcode\Exception as BarcodeException;
use Com\Tecnick\Pdf\Cache\CacheInterface as ObjExtCache;
use Com\Tecnick\Pdf\Encrypt\Encrypt as ObjEncrypt;
use Com\Tecnick\Pdf\Exception as PdfException;
use Com\Tecnick\Pdf\Import\Importer as ObjImporter;
use Com\Tecnick\Pdf\Import\ImporterInterface;
use Com\Tecnick\Pdf\Import\PageTemplateInterface;
use Com\Tecnick\Pdf\Page\PageDisplayMode;
use Com\Tecnick\Pdf\Page\PageLayout;
use Com\Tecnick\Pdf\Page\TransparencyGroupMode;
use Com\Tecnick\Pdf\Page\Unit;
use Com\Tecnick\Pdf\Sign\Config as SignConfig;
use Com\Tecnick\Pdf\Signature\ExternalSignatureEncoding;
use Com\Tecnick\Pdf\Signature\SignatureAppearanceMode;

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
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type StyleDataOpt from \Com\Tecnick\Pdf\Graph\Base
 * @phpstan-import-type PageData from \Com\Tecnick\Pdf\Page\Box
 * @phpstan-import-type PageInputData from \Com\Tecnick\Pdf\Page\Box
 * @phpstan-import-type TFontMetric from \Com\Tecnick\Pdf\Font\Stack
 *
 * @phpstan-import-type TSignature from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TSignDocPrepared from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TSignTimeStamp from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TUserRights from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TFileOptions from Base
 * @mixin \Com\Tecnick\Pdf\Base
 * @mixin \Com\Tecnick\Pdf\Text
 * @method void initClassObjects(?ObjEncrypt $objEncrypt = null, ?array $fileOptions = null, ?\Com\Tecnick\Pdf\Cache\CacheInterface $cache = null)
 * @method float toPoints(float $usr)
 * @method float toUnit(float $pnt)
 * @method float toYUnit(float $pnt, float $pageh = -1)
 * @method string defaultPageContent(int $pid = -1)
 * @method array<string, mixed> addPage(array $data = [])
 * @method void setPageContext(int $pid = -1)
 * @method void addTextCell(string $txt, int $pid = -1, float $posx = 0, float $posy = 0, float $width = 0, float $height = 0, float $offset = 0, float $linespace = 0, string $valign = 'T', string $halign = '', ?array $cell = null, array $styles = [], float $strokewidth = 0, float $wordspacing = 0, float $leading = 0, float $rise = 0, bool $jlast = true, bool $fill = true, bool $stroke = false, bool $underline = false, bool $linethrough = false, bool $overline = false, bool $clip = false, bool $drawcell = true, string $forcedir = '', ?array $shadow = null, string $fit = '')
 * @method array<string, mixed> getLastBBox()
 * @property bool $defPageContentEnabled
 *
 * @property string $unit
 * @property bool $subsetfont
 * @property bool $compress
 * @property string $pdffilename
 * @property string $encpdffilename
 * @property array{r: string, p: string, m: string} $spaceregexp
 * @property array{zoom: int|string, layout: string, mode: string} $display
 * @property array<string, string> $lang
 * @property TUserRights $userrights
 * @property TSignature $signature
 * @property bool $sign
 * @property bool $sigapp
 * @property TSignTimeStamp $sigtimestamp
 * @property array<string, mixed> $defcell
 * @property array<int, array<string, mixed>> $outlines
 * @property ImporterInterface|null $importer
 * @property array<string, mixed> $xobjects
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
class Tcpdf extends \Com\Tecnick\Pdf\Output
{
    /**
     * Fluent signature facade instance (null until first signature() call).
     *
     * @var \Com\Tecnick\Pdf\Signature\Facade|null
     */
    private ?\Com\Tecnick\Pdf\Signature\Facade $signatureFacade = null;

    /**
     * Initialize a new PDF object.
     *
     * @param string|Unit $unit        Unit of measure ('pt', 'mm', 'cm', 'in') or a Unit enum case.
     * @param bool        $isunicode   True if the document is in Unicode mode.
     * @param bool        $subsetfont  If true subset the embedded fonts to remove the unused characters.
     * @param bool        $compress    Set to false to disable stream compression.
     * @param string|PdfConformance $mode PDF mode: "pdfa1", "pdfa2", "pdfa3", "pdfx", "pdfx1a", "pdfx3",
     *                                 "pdfx4", "pdfx5", "pdfua", "pdfua1", "pdfua2", empty, or a PdfConformance case.
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
     *                                       - allowedPaths (string[]): Trusted local path
     *                                         prefixes for file:// reads. Defaults are
     *                                         automatically computed from the package location
     *                                         to cover bundled example assets.
     * @param ?ObjExtCache $cache Optional external cache reused by every cacheable sub-library
     *                            (font subsets, images, ...). Implement
     *                            Com\Tecnick\Pdf\Cache\CacheInterface to bridge your own backend
     *                            (filesystem, APCu, Redis, PSR-16, ...); the application owns the
     *                            backend, its (de)serialization, expiration and size limits. Null
     *                            (default) disables external caching. No backend is shipped.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     * @throws \Random\RandomException
     */
    public function __construct(
        string|Unit $unit = 'mm',
        bool $isunicode = true,
        bool $subsetfont = false,
        bool $compress = true,
        string|PdfConformance $mode = '',
        ?ObjEncrypt $objEncrypt = null,
        ?array $fileOptions = null,
        ?ObjExtCache $cache = null,
    ) {
        $this->setDecimalSeparator();
        $this->doctime = \time();
        $this->docmodtime = $this->doctime;
        $seed = new \Com\Tecnick\Pdf\Encrypt\Type\Seed();
        $this->fileid = \md5($seed->encrypt('TCPDF'));
        if ($objEncrypt instanceof ObjEncrypt) {
            $encData = $objEncrypt->getEncryptionData();
            if ($encData['encrypted'] && $encData['fileid'] !== '') {
                // Keep trailer ID aligned with encryption key derivation input.
                $this->fileid = $objEncrypt->convertStringToHexString($encData['fileid']);
            }
        }
        $this->setPDFFilename($this->fileid . '.pdf');
        $this->unit = $unit instanceof Unit ? $unit->value : $unit;
        $this->setUnicodeMode($isunicode);
        $this->subsetfont = $subsetfont;
        $this->setPDFMode($mode);
        $this->setCompressMode($compress);
        $this->setPDFVersion();
        $this->initClassObjects($objEncrypt, $fileOptions, $cache);
    }

    /**
     * Set the PDF mode.
     *
     * Supported modes:
     * - 'pdfa1', 'pdfa1a', 'pdfa1b': PDF/A-1 with optional conformance level
     * - 'pdfa2', 'pdfa2a', 'pdfa2b', 'pdfa2u': PDF/A-2 with optional conformance level
     * - 'pdfa3', 'pdfa3a', 'pdfa3b', 'pdfa3u': PDF/A-3 with optional conformance level
     * - 'pdfx', 'pdfx1a', 'pdfx3', 'pdfx4', 'pdfx5': PDF/X modes
     * - 'pdfua', 'pdfua1', 'pdfua2': PDF/UA modes
     *
     * Conformance levels:
     * - 'a': Accessible (tagged PDF + Unicode)
     * - 'b': Basic (visual appearance only)
     * - 'u': Unicode (basic + Unicode mapping, PDF/A-2 and PDF/A-3 only)
     *
     * @param string|PdfConformance $mode Input PDF conformance mode (or enum case).
     */
    protected function setPDFMode(string|PdfConformance $mode): void
    {
        if ($mode instanceof PdfConformance) {
            $mode = $mode->value;
        }

        $normalizedMode = \trim(\strtolower($mode));

        $this->pdfx = false;
        $this->pdfxMode = '';
        $this->pdfuaMode = '';
        $this->pdfa = 0;
        $this->pdfaConformance = 'B';

        if (\preg_match('/^pdfx(?:1a|3|4|5)?$/', $normalizedMode) === 1) {
            $this->pdfx = true;
            $this->pdfxMode = $normalizedMode;
            return;
        }

        if (\preg_match('/^pdfua(?:1|2)?$/', $normalizedMode) === 1) {
            $this->pdfuaMode = $normalizedMode;
            return;
        }

        $matches = [];
        if (\preg_match('/^pdfa([1-3])([abu])?$/i', $normalizedMode, $matches) === 1) {
            $pdfaPart = $matches[1] ?? null;
            if ($pdfaPart === null || $pdfaPart === '') {
                return;
            }

            $this->pdfa = (int) $pdfaPart;
            if (isset($matches[2]) && $matches[2] !== '') {
                $conf = \strtoupper($matches[2]);
                if ($conf === 'U' && $this->pdfa === 1) {
                    $conf = 'B';
                }
                $this->pdfaConformance = $conf;
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
        $this->compress = $compress && $this->pdfa !== 3;
    }

    /**
     * Set the decimal separator.
     */
    protected function setDecimalSeparator(): void
    {
        // Ensure numeric formatting uses dot as decimal separator for PDF output.
        $decimalPoint = \localeconv()['decimal_point'];
        if ($decimalPoint !== '.') {
            \setlocale(LC_NUMERIC, 'C');
        }
    }

    /**
     * Set the Unicode mode.
     *
     * @param bool $isunicode True when using Unicode mode.
     */
    protected function setUnicodeMode(bool $isunicode): void
    {
        $this->isunicode = $isunicode;
        // check if PCRE Unicode support is enabled
        if ($this->isunicode && \preg_match('/\pL/u', 'a') === 1) {
            $this->setSpaceRegexp('/(?!\xa0)[\s\p{Z}]/u');
            return;
        }

        // PCRE unicode support is turned OFF
        $this->setSpaceRegexp('/[^\S\xa0]/');
    }

    /**
     * Sets the language array used for document metadata (for example
     * the 'a_meta_language' entry that maps to the PDF Catalog /Lang key).
     *
     * @param array<string, string> $lang Associative array of language entries.
     */
    public function setLanguageArray(array $lang): void
    {
        $this->lang = $lang;
    }

    /**
     * Sets the document language code (PDF Catalog /Lang entry),
     * for example 'en-US' or 'it-IT'.
     *
     * @param string $code Language code (RFC 3066 / BCP 47).
     */
    public function setLanguage(string $code): void
    {
        $this->lang['a_meta_language'] = $code;
    }

    /**
     * Set the PDF document base file name.
     * Valid base names may contain Unicode letters, marks, numbers,
     * underscore (_), comma (,), space, and hyphen (-).
     * The base name is normalized to Unicode NFC before validation.
     * The validated filename length is limited to 255 bytes.
     * If a file extension is present, it must be '.pdf' (case-insensitive).
     * Any directory path is ignored and only the basename is used.
     *
     * @param string $name File name.
     */
    public function setPDFFilename(string $name): void
    {
        $bname = \basename($name);
        if (\class_exists('\\Normalizer')) {
            $normalized = \Normalizer::normalize($bname, \Normalizer::FORM_C);
            if ($normalized !== false) {
                $bname = $normalized;
            }
        }

        if (\strlen($bname) > 255) {
            return;
        }

        // Enforce combining marks to be attached to a base character and require at least one base char.
        $regexp = '/^(?=[\\p{L}\\p{N}_, -]*[\\p{L}\\p{N}])(?:[\\p{L}\\p{N}][\\p{M}]*|[_, -])+(?:\\.[Pp][Dd][Ff])?$/u';
        if (\preg_match($regexp, $bname) === 1) {
            $this->pdffilename = $bname;
            $this->encpdffilename = \rawurlencode($bname);
        }
    }

    /**
     * Set regular expression to detect whitespaces or word separators.
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
            'p' => !isset($parts[1]) || $parts[1] === '' ? '[\s]' : $parts[1],
            'm' => !isset($parts[2]) || $parts[2] === '' ? '' : $parts[2],
        ];
    }

    /**
     * Controls emission of the per-page transparency /Group entry on standard
     * (non PDF/A) pages.
     *
     * Every standard tc-lib-pdf page declares a transparency group
     * (/Group << /Type /Group /S /Transparency /CS /DeviceRGB >>). This makes
     * blending color-managed and portable, but a conforming interpreter must
     * composite such a page through the transparency pipeline even when every
     * mark is fully opaque. Conservative print firmware does this at device
     * resolution, which can add a flat per-page cost. Omitting the group on
     * pages that contain no actual transparency removes that cost without
     * changing the appearance of opaque pages.
     *
     * Modes:
     * - 'auto'   : (default) emit the group only on pages that actually use
     *              transparency (a fill/stroke alpha below 1, a non-Normal blend
     *              mode, a soft mask, a soft-masked image, an imported page, or a
     *              referenced transparency-group XObject). Fully-opaque pages are
     *              flattened.
     * - 'always' : always emit the group on every standard page (legacy
     *              behaviour, maximally portable for blended content).
     * - 'never'  : never emit the group. Use only for print targets known to be
     *              free of transparency; blending becomes implementation-defined,
     *              like classic TCPDF output.
     *
     * Has no effect in PDF/A mode, where the group is already suppressed.
     *
     * @param string|TransparencyGroupMode $mode One of 'auto', 'always', 'never', or a TransparencyGroupMode case.
     *
     * @throws \Com\Tecnick\Pdf\Exception If the mode is not recognized.
     */
    public function setPageTransparencyGroup(string|TransparencyGroupMode $mode = 'auto'): static
    {
        if ($mode instanceof TransparencyGroupMode) {
            $mode = $mode->value;
        }

        $normalized = \strtolower(\trim($mode));
        if (!\in_array($normalized, ['auto', 'always', 'never'], true)) {
            throw new PdfException('Invalid page transparency group mode: ' . $mode);
        }

        $this->page->setPageTransparencyGroupMode($normalized);
        return $this;
    }

    /**
     * Defines the way the document is to be displayed by the viewer.
     *
     * @param int|string|DisplayZoom $zoom The zoom to use (or a DisplayZoom enum case).
     *                           It can be one of the following string values or a number indicating the
     *                           zooming factor to use.
     *                           * fullpage: displays the entire page on screen * fullwidth: uses
     *                           maximum width of window
     *                           * real: uses real size (equivalent to 100% zoom) * default: uses
     *                           viewer default mode
     * @param string|PageLayout $layout The page layout (or a PageLayout enum case). Possible values are:
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
     * @param string|PageDisplayMode $mode A name object (or PageDisplayMode enum case) for how the document is displayed:
     *                           * UseNone Neither document outline nor thumbnail images visible
     *                           * UseOutlines Document outline visible
     *                           * UseThumbs Thumbnail images visible
     *                           * FullScreen Full screen, with no menu bar, window controls,
     *                           or any other window visible
     *                           * UseOC (PDF 1.5) Optional content group panel visible
     *                           * UseAttachments (PDF 1.6) Attachments panel visible
     */
    public function setDisplayMode(
        int|string|DisplayZoom $zoom = 'default',
        string|PageLayout $layout = 'SinglePage',
        string|PageDisplayMode $mode = 'UseNone',
    ): static {
        if ($zoom instanceof DisplayZoom) {
            $zoom = $zoom->value;
        }

        $this->display['zoom'] = \is_numeric($zoom) || \in_array($zoom, $this::VALIDZOOM, true) ? $zoom : 'default';
        $this->display['layout'] = $this->page->getLayout($layout);
        $this->display['mode'] = $this->page->getDisplay($mode);
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
     * @throws \Com\Tecnick\Color\Exception
     */
    public function getBarcode(
        string $type,
        string $code,
        float $posx = 0,
        float $posy = 0,
        int $width = -1,
        int $height = -1,
        array $padding = [0, 0, 0, 0],
        array $style = [],
    ): string {
        $model = $this->barcode->getBarcodeObj($type, $code, $width, $height, 'black', $padding);
        $bars = $model->getBarsArrayXYWH();
        $out = '';
        $out .= $this->graph->getStartTransform();
        $out .= $this->graph->getStyleCmd($style);
        foreach ($bars as $bar) {
            /** @var array{0: numeric, 1: numeric, 2: numeric, 3: numeric} $bar */
            $x = (float) $bar[0];
            $y = (float) $bar[1];
            $w = (float) $bar[2];
            $h = (float) $bar[3];
            $out .= $this->graph->getBasicRect($posx + $x, $posy + $y, $w, $h, 'f');
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
     *
     * Also available through the fluent API: signature()->userRights().
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
     *            - ap (string|array) Optional annotation appearance stream definition (/AP).
     *              Same format as generic annotation appearances: keyed by mode (n/r/d),
     *              each mode accepting either a stream string or a state=>stream map.
     *            - as (string) Optional annotation appearance state (/AS).
     *            - empty (bool) Array of empty signatures:
     *                - objid (int) Object id.
     *                - name (string) Name of the signature field.
     *                - page (int) Page number.
     *                - rect (array) Rectangle of the signature field.
     *            - name (string) Name of the signature field.
     *            - page (int) Page number.
     *            - rect (array) Rectangle of the signature field.
     *            - xobj (string) Optional Form XObject ID to auto-fit as normal appearance.
     *        - approval (string) Set to 'A' to enable the approval signature eg. for PDF incremental update.
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
     *        - ltv (array) LTV collection options.
     *            - enabled (bool) Enable validation material collection.
     *            - embed_ocsp (bool) Allow OCSP material collection.
     *            - embed_crl (bool) Allow CRL material collection.
     *            - embed_certs (bool) Embed certificate bytes in validation material.
     *            - include_dss (bool) Include DSS objects in output.
     *            - include_vri (bool) Include VRI map in output.
     *
     * Also available through the fluent API: signature()->configure().
     *
     * @throws PdfException
     */
    public function setSignature(array $data): void
    {
        if (\array_key_exists('ltv', $data)) {
            /** @var mixed $ltvData */
            $ltvData = $data['ltv'];
            if (!\is_array($ltvData)) {
                throw new PdfException('Invalid signature LTV options');
            }

            $ltvDefaults = [
                'enabled' => false,
                'embed_ocsp' => true,
                'embed_crl' => true,
                'embed_certs' => true,
                'include_dss' => true,
                'include_vri' => true,
            ];
            /** @var array<string, mixed> $ltvData */
            $ltvData = \array_merge($ltvDefaults, $ltvData);

            foreach (['enabled', 'embed_ocsp', 'embed_crl', 'embed_certs', 'include_dss', 'include_vri'] as $key) {
                /** @var mixed $ltvVal */
                $ltvVal = $ltvData[$key] ?? null;
                if (!\is_bool($ltvVal)) {
                    throw new PdfException('Invalid signature LTV option: ' . $key);
                }
            }
        }

        $this->signature = \array_merge($this->signature, $data);

        if (!isset($this->signature['ltv'])) {
            $this->signature['ltv'] = [
                'enabled' => false,
                'embed_ocsp' => true,
                'embed_crl' => true,
                'embed_certs' => true,
                'include_dss' => true,
                'include_vri' => true,
            ];
        }

        if ($this->signature['signcert'] === '') {
            throw new PdfException('Invalid signing certificate (signcert)');
        }

        if ($this->signature['privkey'] === '') {
            $this->signature['privkey'] = $this->signature['signcert'];
        }

        ++$this->pon;
        $this->objid['signature'] = $this->pon; // Signature widget annotation object id.
        ++$this->pon; // Signature appearance object id ($this->objid['signature'] + 1).

        $this->setSignAnnotRefs();

        $this->sign = true;
    }

    /**
     * Enable a signature placeholder for a remote signing workflow.
     *
     * This reserves the signature widget and placeholder bytes without requiring
     * a local private key. The actual CMS/PKCS#7 value can be embedded later
     * with applyExternalSignature().
     *
     * @param TSignature $data Signature data.
     *
     * @throws PdfException
     *
     * Also available through the fluent API: signature()->external()->configure().
     */
    public function setSignatureForExternalSigning(array $data): void
    {
        if ($data['signcert'] === '') {
            $data['signcert'] = '__external_signing__';
        }

        if ($data['privkey'] === '') {
            $data['privkey'] = $data['signcert'];
        }

        $this->setSignature($data);
    }

    /**
     * Build the document for external signing and compute the digest over ByteRange data.
     *
     * @param string $algorithm Hash algorithm accepted by hash() (default: sha256).
     *
     * @return array{
     *          algorithm: string,
     *          byte_range: array{int,int,int,int},
     *          prepared_pdf: string,
     *          hash_raw: string,
     *          hash_hex: string,
     *          hash_base64: string,
     *        }
     *
     * @throws PdfException
     * @throws \Throwable
     *
     * Also available through the fluent API: signature()->external()->prepare().
     */
    public function getExternalSignaturePreparation(string $algorithm = 'sha256'): array
    {
        if (!$this->sign || $this->signature['cert_type'] < 0) {
            throw new PdfException('External signature placeholder is not configured');
        }

        $algorithm = \strtolower(\trim($algorithm));
        if ($algorithm === '' || !\in_array($algorithm, \hash_algos(), true)) {
            throw new PdfException('Invalid hash algorithm');
        }

        $pdfdoc = $this->getOutPDFHeader() . $this->getOutPDFBody();
        $startxref = \strlen($pdfdoc);
        $offset = $this->getPDFObjectOffsets($pdfdoc);
        $pdfdoc .=
            $this->getOutPDFXref($offset)
            . $this->getOutPDFTrailer()
            . 'startxref'
            . "\n"
            . $startxref
            . "\n"
            . '%%EOF'
            . "\n";

        $prepared = $this->prepareDocumentForSignature($pdfdoc);

        $hashRaw = \hash($algorithm, $prepared['pdfdoc'], true);

        return [
            'algorithm' => $algorithm,
            'byte_range' => $prepared['byte_range'],
            'prepared_pdf' => $prepared['pdfdoc'],
            'hash_raw' => $hashRaw,
            'hash_hex' => \bin2hex($hashRaw),
            'hash_base64' => \base64_encode($hashRaw),
        ];
    }

    /**
     * Inject an externally generated CMS/PKCS#7 signature into a prepared document.
     *
     * @param string $preparedPdf Prepared PDF returned by getExternalSignaturePreparation().
     * @param array{int,int,int,int} $byteRange ByteRange returned by getExternalSignaturePreparation().
     * @param string $signature External signature data.
     * @param string|ExternalSignatureEncoding $encoding Signature encoding: binary, base64, hex, or enum case.
     *
     * @return string Fully signed PDF document.
     *
     * @throws PdfException
     *
     * Also available through the fluent API: signature()->external()->apply().
     */
    public function applyExternalSignature(
        string $preparedPdf,
        array $byteRange,
        string $signature,
        string|ExternalSignatureEncoding $encoding = 'binary',
    ): string {
        $rangeCount = \count($byteRange);
        if ($rangeCount !== 4) {
            throw new PdfException('Invalid ByteRange data');
        }

        $pos = (int) $byteRange[1];
        if ($pos < 0 || $pos > \strlen($preparedPdf)) {
            throw new PdfException('Invalid ByteRange insertion position');
        }

        $encoding = ExternalSignatureEncoding::fromLoose($encoding)->value;
        $hexSignature = '';

        if ($encoding === 'hex') {
            $hexSignature = \preg_replace('/\s+/', '', $signature) ?? '';
            if ($hexSignature === '' || \preg_match('/^[0-9a-fA-F]+$/', $hexSignature) !== 1) {
                throw new PdfException('Invalid hexadecimal signature');
            }
        } elseif ($encoding === 'base64') {
            $binarySignature = \base64_decode($signature, true);
            if ($binarySignature === false) {
                throw new PdfException('Invalid base64 signature');
            }

            $hexSignature = \bin2hex($binarySignature);
        } else {
            $hexSignature = \bin2hex($signature);
        }

        $contentsLength = $this->signatureContentsLength();
        if (\strlen($hexSignature) > $contentsLength) {
            throw new PdfException('Signature is too large for the reserved PDF placeholder');
        }

        $hexSignature = \str_pad(\strtolower($hexSignature), $contentsLength, '0');
        return \substr($preparedPdf, 0, $pos) . '<' . $hexSignature . '>' . \substr($preparedPdf, $pos);
    }

    /**
     * Get the signature widget object ID.
     *
     * @return int Signature widget annotation object ID (0 if not initialized).
     *
     * Also available through the fluent API: signature()->widgetObjectId().
     */
    public function getSignatureObjectID(): int
    {
        return $this->objid['signature'];
    }

    /**
     * Enable or disable the Signature Approval
     *
     * @param bool $enabled If true enable the Signature Approval
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
     *        - hash_algorithm (string) Digest algorithm: sha256, sha384, sha512.
     *        - policy_oid (string) Optional timestamp policy OID.
     *        - nonce_enabled (bool) Add nonce to the timestamp request.
     *        - timeout (int) Request timeout in seconds.
     *        - verify_peer (bool) Validate TSA TLS certificate.
     *
     * @throws PdfException
     *
     * Also available through the fluent API: signature()->timestamp().
     */
    public function setSignTimeStamp(array $data): void
    {
        /** @var array<string, mixed> $rawData */
        $rawData = $data;
        if (\array_key_exists('nonce_enabled', $rawData) && !\is_bool($rawData['nonce_enabled'])) {
            throw new PdfException('Invalid TSA nonce setting');
        }

        if (\array_key_exists('verify_peer', $rawData) && !\is_bool($rawData['verify_peer'])) {
            throw new PdfException('Invalid TSA verify peer setting');
        }

        $this->sigtimestamp = \array_merge($this->sigtimestamp, $data);

        /** @var array<string, mixed> $sigtimestamp */
        $sigtimestamp = $this->sigtimestamp;

        $enabled = isset($sigtimestamp['enabled']) && $sigtimestamp['enabled'] === true;
        $host = isset($sigtimestamp['host']) && \is_string($sigtimestamp['host']) ? $sigtimestamp['host'] : '';

        if ($enabled && $host === '') {
            throw new PdfException('Invalid TSA host');
        }

        $hashAlgorithm = isset($sigtimestamp['hash_algorithm']) && \is_string($sigtimestamp['hash_algorithm'])
            ? \strtolower($sigtimestamp['hash_algorithm'])
            : '';
        if (!\in_array($hashAlgorithm, ['sha256', 'sha384', 'sha512'], true)) {
            throw new PdfException('Invalid TSA hash algorithm');
        }

        $policyOid = isset($sigtimestamp['policy_oid']) && \is_string($sigtimestamp['policy_oid'])
            ? $sigtimestamp['policy_oid']
            : '';
        if ($policyOid !== '' && \preg_match('/^\\d+(?:\\.\\d+)+$/', $policyOid) !== 1) {
            throw new PdfException('Invalid TSA policy OID');
        }

        $timeout = isset($sigtimestamp['timeout']) && \is_int($sigtimestamp['timeout']) ? $sigtimestamp['timeout'] : 0;
        if ($timeout < 1) {
            throw new PdfException('Invalid TSA timeout');
        }
    }

    /**
     * Request a PAdES B-LTA archive: switch the signature profile to pades-b-lta
     * and ensure the long-term validation store (DSS) is emitted.
     *
     * The next getOutPDFString() then produces the signature revision, the DSS
     * revision, and a /Type /DocTimeStamp archive-timestamp revision. A TSA must be
     * configured (setSignTimeStamp / signature()->timestamp()) since B-LTA builds
     * on B-T; without it the timestamp revision is skipped. Existing LTV options are
     * preserved; only enabled and include_dss are forced on.
     */
    public function upgradeSignatureToLta(): void
    {
        $ltv = $this->signature['ltv'] ?? [];
        $this->signature['ltv'] = [
            'enabled' => true,
            'embed_ocsp' => $ltv['embed_ocsp'] ?? true,
            'embed_crl' => $ltv['embed_crl'] ?? true,
            'embed_certs' => $ltv['embed_certs'] ?? true,
            'include_dss' => true,
            'include_vri' => $ltv['include_vri'] ?? true,
        ];
        $this->signature['profile'] = SignConfig::PROFILE_PADES_B_LTA;
    }

    /**
     * Get a signature appearance (page and rectangle coordinates).
     *
     * @param float $posx Abscissa of the upper-left corner.
     * @param float $posy Ordinate of the upper-left corner.
     * @param float $width Width of the signature area.
     * @param float $height Height of the signature area.
     * @param int $page Page number (pid).
     * @param string $name Name of the signature.
     *
     * @return array{
     *           'name': string,
     *           'page': int,
     *           'rect': string,
     *         } Array defining page and rectangle coordinates of signature appearance.
     *
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    protected function getSignatureAppearanceArray(
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        int $page = -1,
        string $name = '',
    ): array {
        $sigapp = [];

        $sigapp['page'] = $page < 0 ? $this->page->getPageID() : $page;
        $sigapp['name'] = $name === '' ? 'Signature' : $name;

        $pntx = $this->toPoints($posx);
        $pnty = $this->toYUnit($posy + $height, $this->page->getPage($sigapp['page'])['pheight']);
        $pntw = $this->toPoints($width);
        $pnth = $this->toPoints($height);

        $sigapp['rect'] = \sprintf('%F %F %F %F', $pntx, $pnty, $pntx + $pntw, $pnty + $pnth);

        return $sigapp;
    }

    /**
     * Set the digital signature appearance (a clickable rectangle area to get signature properties).
     *
     * @param float $posx Abscissa of the upper-left corner.
     * @param float $posy Ordinate of the upper-left corner.
     * @param float $width Width of the signature area.
     * @param float $height Height of the signature area.
     * @param int $page optional page number (if < 0 the current page is used).
     * @param string $name Name of the signature.
     *
     * @throws \Com\Tecnick\Pdf\Page\Exception
     *
     * Also available through the fluent API: signature()->appearance()->place().
     */
    public function setSignatureAppearance(
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        int $page = -1,
        string $name = '',
    ): void {
        $data = $this->getSignatureAppearanceArray($posx, $posy, $width, $height, $page, $name);
        $this->signature['appearance']['page'] = $data['page'];
        $this->signature['appearance']['name'] = $data['name'];
        $this->signature['appearance']['rect'] = $data['rect'];
        $this->setSignAnnotRefs();
    }

    /**
     * Set a custom appearance stream for the signature widget annotation.
     *
     * @param string $stream Appearance stream content.
     * @param string|SignatureAppearanceMode $mode Appearance mode: N (normal), R (rollover), D (down), or enum case.
     * @param string $state Optional appearance state name.
     *
     * @throws PdfException
     *
     * Also available through the fluent API: signature()->appearance()->stream().
     */
    public function setSignatureAppearanceStream(
        string $stream,
        string|SignatureAppearanceMode $mode = 'N',
        string $state = '',
    ): void {
        if ($stream === '') {
            throw new PdfException('The signature appearance stream cannot be empty');
        }

        $mode = \strtolower(SignatureAppearanceMode::fromLoose($mode)->value);

        if (!isset($this->signature['appearance']['ap']) || !\is_array($this->signature['appearance']['ap'])) {
            $this->signature['appearance']['ap'] = [];
        }

        if ($state === '') {
            $this->signature['appearance']['ap'][$mode] = $stream;
            return;
        }

        if (
            !isset($this->signature['appearance']['ap'][$mode])
            || !\is_array($this->signature['appearance']['ap'][$mode])
        ) {
            $this->signature['appearance']['ap'][$mode] = [];
        }

        $this->signature['appearance']['ap'][$mode][$state] = $stream;
        $this->signature['appearance']['as'] = $state;
    }

    /**
     * Set a Form XObject ID as signature widget appearance source.
     *
     * The XObject will be auto-fitted to the signature rectangle.
     *
     * @param string $xobjid XObject resource name (for example "IMP1").
     *
     * @throws PdfException
     *
     * Also available through the fluent API: signature()->appearance()->xobject().
     */
    public function setSignatureAppearanceXObject(string $xobjid): void
    {
        $xobjid = \trim($xobjid);
        if (\preg_match('/^[A-Za-z0-9_]+$/', $xobjid) !== 1) {
            throw new PdfException('Invalid signature appearance XObject ID');
        }

        $this->signature['appearance']['xobj'] = $xobjid;
    }

    /**
     * Add an empty digital signature appearance (a clickable rectangle area to get signature properties).
     *
     * @param float $posx Abscissa of the upper-left corner.
     * @param float $posy Ordinate of the upper-left corner.
     * @param float $width Width of the signature area.
     * @param float $height Height of the signature area.
     * @param int $page optional page number (if < 0 the current page is used).
     * @param string $name Name of the signature.
     *
     * @throws \Com\Tecnick\Pdf\Page\Exception
     *
     * Also available through the fluent API: signature()->emptyField().
     */
    public function addEmptySignatureAppearance(
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        int $page = -1,
        string $name = '',
    ): void {
        ++$this->pon;
        $data = $this->getSignatureAppearanceArray($posx, $posy, $width, $height, $page, $name);
        $this->signature['appearance']['empty'][] = [
            'objid' => $this->pon,
            'name' => $data['name'],
            'page' => $data['page'],
            'rect' => $data['rect'],
        ];
        $this->setSignAnnotRefs();
    }

    /**
     * Set the signature annotation references.
     */
    protected function setSignAnnotRefs(): void
    {
        $signatureObjId = (int) $this->objid['signature'];
        if ($signatureObjId === 0) {
            return;
        }

        try {
            $appearancePage = $this->signature['appearance']['page'];
            $appearanceRect = $this->signature['appearance']['rect'];
            if ($appearancePage >= 0 && $appearanceRect !== '') {
                $this->page->addAnnotRef($signatureObjId, $appearancePage);
            }

            $emptyAppearances = $this->signature['appearance']['empty'];
            if ($emptyAppearances === []) {
                return;
            }

            foreach ($emptyAppearances as $esa) {
                $this->page->addAnnotRef($esa['objid'], $esa['page']);
            }
        } catch (\Com\Tecnick\Pdf\Page\Exception) {
            return;
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
        $layer = \sprintf('LYR%03d', \count($this->pdflayer) + 1);
        $name = (string) \preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
        if ($name === '') {
            $name = $layer;
        }

        $intarr = [];
        if (isset($intent['view']) && $intent['view']) {
            $intarr[] = '/View';
        }
        if (isset($intent['design']) && $intent['design']) {
            $intarr[] = '/Design';
        }

        $this->pdflayer[] = [
            'layer' => $layer,
            'name' => $name,
            'intent' => \implode(' ', $intarr),
            'print' => $print,
            'view' => $view,
            'lock' => $lock,
            'objid' => 0,
        ];

        return ' /OC /' . $layer . ' BDC' . "\n";
    }

    /**
     * Close the current optional content (layer) marked-content sequence.
     *
     * @return string PDF marked-content operator that ends the layer opened by newLayer().
     */
    public function closeLayer(): string
    {
        return 'EMC' . "\n";
    }

    // ===| TOC |===========================================================

    /**
     * Add a Table of Contents (TOC) to the document.
     * The bookmarks are created via the setBookmark() method.
     *
     * @param int   $page  Page number.
     * @param float $posx  Abscissa of the upper-left corner.
     * @param float $posy  Ordinate of the upper-left corner.
     * @param float $width Width of the TOC area.
     * @param bool  $rtl   Right-To-Left - If true prints the TOC in RTL mode.
     * @param StyleDataOpt $linestyle Line style for the space filler.
     *
     * @throws \Com\Tecnick\File\Exception
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Pdf\Image\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     * @throws \Com\Tecnick\Unicode\Exception
     * @throws PdfException
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
            'dashArray' => [1, 1],
            'dashPhase' => 0,
            'lineColor' => 'gray',
            'fillColor' => '',
        ],
    ): void {
        if ($width <= 0) {
            $reg = $this->page->getRegion();
            $width = $reg['RW'];
        }

        /** @var array{cw: array<int, numeric>, dw: numeric, idx: int, size: numeric, spacing: float, stretching: float} $curfont */
        $curfont = $this->font->getCurrentFont();

        // width to accommodate the number (max 9 digits space).
        $cwVal = (float) ($curfont['cw'][48] ?? $curfont['dw']);
        $chrw = $this->toUnit($cwVal); // 48 ASCII = '0'.
        $indent = 2 * $chrw; // each level is indented by 2 characters.
        $numwidth = 9 * $chrw; // maximum 9 digits to print the page number.
        $txtwidth = $width - $numwidth;

        $defcellData = $this->defcell;
        $marginData = $defcellData['margin'];
        $paddingData = $defcellData['padding'];
        $marginT = $this->getNumericFloatValue($marginData['T']);
        $paddingT = $this->getNumericFloatValue($paddingData['T']);
        $cellSpaceT = $this->toUnit($marginT + $paddingT);
        $marginB = $this->getNumericFloatValue($marginData['B']);
        $paddingB = $this->getNumericFloatValue($paddingData['B']);
        $cellSpaceB = $this->toUnit($marginB + $paddingB);
        $marginL = $this->getNumericFloatValue($marginData['L']);
        $paddingR = $this->getNumericFloatValue($paddingData['R']);
        $cellSpaceH = $chrw + $this->toUnit($marginL + $marginL + $paddingR + $paddingR);

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

        $tocCellStyle = ['all' => ['fillColor' => '#e8f4ff']];

        $pid = $page < 0 ? (int) $this->page->getPageID() : (int) $page;

        $outlines = $this->outlines;
        foreach ($outlines as $bmrk) {
            $bmrkStyle = $bmrk['s'];
            $bmrkLevel = $bmrk['l'];
            $bmrkText = $bmrk['t'];
            $bmrkPage = $bmrk['p'];
            $bmrkY = $bmrk['y'];
            $bmrkColor = $bmrk['c'];
            $bmrkLink = $bmrk['u'];
            $fontSize = (float) $curfont['size'];

            $font = $this->font->cloneFont(
                $this->pon,
                $curfont['idx'],
                $bmrkStyle . ($bmrkLevel === 0 ? 'B' : ''),
                (int) \round($fontSize - $bmrkLevel),
                $curfont['spacing'],
                $curfont['stretching'],
            );
            $fontHeight = $font['height'];
            $fontOut = $font['out'];

            $region = $this->page->getRegion($pid);
            $regionHeight = $region['RH'];

            if (($posy + $cellSpaceT + $cellSpaceB + $fontHeight) > $regionHeight) {
                $this->page->getNextRegion($pid);
                $curpid = (int) $this->page->getPageId();
                if ($curpid > $pid) {
                    $pid = $curpid;
                    $this->setPageContext($pid);
                }
                $region = $this->page->getRegion($pid);
                $posy = 0; // $region['RY'];
            }

            $this->page->addContent($this->graph->getStartTransform(), $pid);
            $this->page->addContent($fontOut, $pid);

            if ($bmrkColor !== '') {
                $col = $this->color->getPdfFillColor($bmrkColor);
                $this->page->addContent($col, $pid);
            }

            if ($bmrkLink === '') {
                $bmrkLink = $this->addInternalLink($bmrkPage, $bmrkY);
            }

            $offset = $indent * $bmrkLevel;
            // add bookmark text
            $prevpid = (int) $this->page->getPageID();
            $this->addTextCell(
                $bmrkText,
                $pid,
                $txt_posx,
                $posy,
                $txtwidth,
                0,
                $offset,
                0,
                'T',
                $aligntext,
                null,
                $tocCellStyle,
            );

            $bbox = $this->getLastBBox();
            /** @var array{w: numeric, h: numeric, y: numeric} $bbox */
            $wtxt = (float) $bbox['w'];

            $pageid = (int) $this->page->getPageID();
            if ($pageid > $prevpid) {
                $this->page->addContent($this->graph->getStopTransform(), $pid);
                $regionRH = $region['RH'];
                $lnkid = $this->setLink($posx, $posy, $width, $regionRH - $posy, $bmrkLink);
                $this->page->addAnnotRef($lnkid, $pid);
                $pid = $pageid;
                $this->page->addContent($this->graph->getStartTransform(), $pid);
                $this->page->addContent($fontOut, $pid);
            }

            $bboxY = (float) $bbox['y'];
            $posy = $bboxY - $cellSpaceT; // align number with the last line of the text

            // add page number
            $this->addTextCell(
                (string) ($bmrkPage + 1),
                $pid,
                $num_posx,
                $posy,
                $numwidth,
                0,
                0,
                0,
                'T',
                $alignnum,
                null,
                $tocCellStyle,
            );

            $bbox = $this->getLastBBox();
            /** @var array{w: numeric, h: numeric, y: numeric} $bbox */
            $wnum = (float) $bbox['w'];

            // add line to fill the gap between text and number
            $fontAscent = $font['ascent'];
            $line_posx = $cellSpaceH + $offset + $posx + ($rtl ? $wnum : $wtxt);
            $line_posy = $bboxY + $this->toUnit($fontAscent);
            $lineLength = $width - $wtxt - $wnum - (2 * $cellSpaceH) - $offset;
            $line = $this->graph->getLine($line_posx, $line_posy, $line_posx + $lineLength, $line_posy, $linestyle);
            $this->page->addContent($line, $pid);

            $bboxH = (float) $bbox['h'];
            $lnkid = $this->setLink($posx, $bboxY, $width, $bboxH, $bmrkLink);
            $this->page->addAnnotRef($lnkid, $pid);

            $this->page->addContent($this->graph->getStopTransform(), $pid);

            // Move to the next line.
            $posy = $bboxY + $bboxH + $cellSpaceB;
        }
    }

    // -------------------------------------------------------------------------
    // PDF Import API
    // -------------------------------------------------------------------------

    /**
     * Fluent entry point for the signature subsystem.
     *
     * Groups signature configuration, timestamp, user rights, appearance, empty
     * fields, and external signing behind one discoverable object. It forwards to
     * the underlying setSignature()/setSignTimeStamp()/... methods, which remain
     * fully supported; the facade is a convenience wrapper, not a replacement.
     *
     * @return \Com\Tecnick\Pdf\Signature\Facade
     */
    public function signature(): \Com\Tecnick\Pdf\Signature\Facade
    {
        return $this->signatureFacade ??= new \Com\Tecnick\Pdf\Signature\Facade($this);
    }

    /**
     * Return the lazy-initialized importer instance.
     *
     * @return ImporterInterface
     */
    private function getImporter(): ImporterInterface
    {
        if ($this->importer === null) {
            // Pass xobjects by reference so Importer writes directly into this document's registry.
            $xobjects = &$this->xobjects;
            $importFile = clone $this->file;
            $importFile->setAllowedPaths(['*']);
            $this->importer = new ObjImporter($xobjects, $this->pon, $importFile);
        }

        return $this->importer;
    }

    /**
     * Register a source PDF file for import.
     *
     * @param string              $path File path to a readable PDF.
     * @param array<string, mixed> $cfg  Optional parser configuration.
     *
     * @return string Source document identifier.
     *
     * @throws \Com\Tecnick\Pdf\Import\ImportSourceNotFoundException
     * @throws \Com\Tecnick\Pdf\Import\ImportCorruptedSourceException
     * @throws \Com\Tecnick\Pdf\Import\ImportUnsupportedFeatureException
     */
    public function setImportSourceFile(string $path, array $cfg = []): string
    {
        return $this->getImporter()->setImportSourceFile($path, $cfg);
    }

    /**
     * Register a source PDF from raw binary data.
     *
     * @param string              $data Raw PDF binary data.
     * @param array<string, mixed> $cfg  Optional parser configuration.
     *
     * @return string Source document identifier.
     *
     * @throws \Com\Tecnick\Pdf\Import\ImportCorruptedSourceException
     * @throws \Com\Tecnick\Pdf\Import\ImportUnsupportedFeatureException
     */
    public function setImportSourceData(string $data, array $cfg = []): string
    {
        return $this->getImporter()->setImportSourceData($data, $cfg);
    }

    /**
     * Return the total number of pages in a registered source document.
     *
     * @param string $sourceId Source document identifier.
     *
     * @return int Total page count.
     *
     * @throws \Com\Tecnick\Pdf\Import\ImportSourceNotFoundException
     * @throws \Com\Tecnick\Pdf\Import\ImportCorruptedSourceException
     */
    public function getSourcePageCount(string $sourceId): int
    {
        return $this->getImporter()->getSourcePageCount($sourceId);
    }

    /**
     * Import one page from a registered source document and return a PageTemplateInterface.
     *
     * @param string                 $sourceId Source document identifier.
     * @param int                    $pageNum  1-based page number.
     * @param array<string, mixed>   $options  Import options.
     *
     * @return PageTemplateInterface Imported page template ready for placement.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     * @throws \Com\Tecnick\Pdf\Import\ImportException
     */
    public function importPage(string $sourceId, int $pageNum, array $options = []): PageTemplateInterface
    {
        $options = $this->normalizeImportOptions($options);
        return $this->getImporter()->importPage($sourceId, $pageNum, $options);
    }

    /**
     * Place an imported page template onto the current page.
     *
     * @param PageTemplateInterface $tpl     Template to place.
     * @param float                $xpos    X position in user units.
     * @param float                $ypos    Y position in user units.
     * @param float|null           $width   Target width (null = use template width).
     * @param float|null           $height  Target height (null = use template height).
     * @param array<string, mixed> $options Placement options.
     *
     * @return array{x: float, y: float, width: float, height: float} Actual placed rect in user units.
     *
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    public function useImportedPage(
        PageTemplateInterface $tpl,
        float $xpos,
        float $ypos,
        ?float $width = null,
        ?float $height = null,
        array $options = [],
    ): array {
        $tplW = $this->toUnit($tpl->getWidth());
        $tplH = $this->toUnit($tpl->getHeight());

        $boxW = $width ?? $tplW;
        $boxH = $height ?? $tplH;

        $dstW = $boxW;
        $dstH = $boxH;

        $keepAspect =
            !\array_key_exists('keepAspectRatio', $options)
            || $options['keepAspectRatio'] === true
            || $options['keepAspectRatio'] === 1
            || $options['keepAspectRatio'] === '1'
            || $options['keepAspectRatio'] === 'true';
        if ($keepAspect && $tplW > 0 && $tplH > 0) {
            $scaleX = $dstW / $tplW;
            $scaleY = $dstH / $tplH;
            $scale = \min($scaleX, $scaleY);
            $dstW = $tplW * $scale;
            $dstH = $tplH * $scale;
        }

        $align = 'TL';
        if (isset($options['align']) && \is_string($options['align'])) {
            $align = \strtoupper($options['align']);
        }
        if (\strlen($align) !== 2) {
            $align = 'TL';
        }

        $vAlign = $align[0] ?? 'T';
        $hAlign = $align[1] ?? 'L';

        $offX = 0.0;
        $offY = 0.0;
        $freeW = $boxW - $dstW;
        $freeH = $boxH - $dstH;

        if ($hAlign === 'C') {
            $offX = $freeW / 2;
        } elseif ($hAlign === 'R') {
            $offX = $freeW;
        }

        if ($vAlign === 'C') {
            $offY = $freeH / 2;
        } elseif ($vAlign === 'B') {
            $offY = $freeH;
        }

        $finalX = $xpos + $offX;
        $finalY = $ypos + $offY;

        $scalePt = $this->toPoints(1.0);
        $scaleX2 = $tplW > 0 ? $dstW / $tplW : 1.0;
        $scaleY2 = $tplH > 0 ? $dstH / $tplH : 1.0;

        // PDF coordinate system has Y increasing upward; page origin is bottom-left.
        $xPt = $this->toPoints($finalX);
        /** @var array{pheight: float} $pageData */
        $pageData = $this->page->getPage();
        $pageHeightPt = $pageData['pheight'];
        $yPt = $pageHeightPt - $this->toPoints($finalY) - ($dstH * $scalePt);

        $matrix = \sprintf('%F 0 0 %F %F %F', $scaleX2, $scaleY2, $xPt, $yPt);
        $content = 'q ';

        $clip =
            isset($options['clip'])
            && (
                $options['clip'] === true
                || $options['clip'] === 1
                || $options['clip'] === '1'
                || $options['clip'] === 'true'
            );
        if ($clip) {
            $clipX = $this->toPoints($xpos);
            $clipY = $pageHeightPt - $this->toPoints($ypos) - $this->toPoints($boxH);
            $clipW = $this->toPoints($boxW);
            $clipH = $this->toPoints($boxH);
            $content .= \sprintf('%F %F %F %F re W n ', $clipX, $clipY, $clipW, $clipH);
        }

        $content .= $matrix . ' cm /' . $tpl->getXobjId() . ' Do Q';
        $this->page->addContent($content);
        return ['x' => $finalX, 'y' => $finalY, 'width' => $dstW, 'height' => $dstH];
    }

    /**
     * Import all pages (or a range) from a registered source document.
     *
     * @param string          $sourceId Source document identifier.
     * @param array<int>|null $range    1-based page numbers to import, or null for all pages.
     * @param array<string, mixed> $options  Import options (same as importPage).
     *
     * @return array<int, PageTemplateInterface> One PageTemplateInterface per requested page.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     * @throws \Com\Tecnick\Pdf\Import\ImportException
     */
    public function importPages(string $sourceId, ?array $range = null, array $options = []): array
    {
        $options = $this->normalizeImportOptions($options);
        return $this->getImporter()->importPages($sourceId, $range, $options);
    }

    /**
     * Add a new page sized to match an imported template and place the template filling the page.
     *
     * @param string               $sourceId Source document identifier.
     * @param int                  $pageNum  1-based page number.
     * @param array<string, mixed> $options  Import options (same as importPage).
     *
     * @return PageTemplateInterface The imported page template.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     * @throws \Com\Tecnick\Pdf\Import\ImportException
     * @throws \Com\Tecnick\Pdf\Page\Exception
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Unicode\Exception
     */
    public function addPageFromImport(string $sourceId, int $pageNum, array $options = []): PageTemplateInterface
    {
        $tpl = $this->importPage($sourceId, $pageNum, $options);
        $tplW = $this->toUnit($tpl->getWidth());
        $tplH = $this->toUnit($tpl->getHeight());
        $this->addPage([
            'format' => '',
            'width' => $tplW,
            'height' => $tplH,
            'orientation' => $tplW > $tplH ? 'L' : 'P',
        ]);
        $this->useImportedPage($tpl, 0.0, 0.0, $tplW, $tplH, ['keepAspectRatio' => false]);
        return $tpl;
    }

    /**
     * Append all pages (or a range) from a source document, each on its own new page.
     *
     * The caller's current page context (active page, graph dimensions) is preserved
     * so document flow can continue on the same page after the call returns.
     *
     * @param string               $sourceId Source document identifier.
     * @param array<int>|null      $range    1-based page numbers to import, or null for all pages.
     * @param array<string, mixed> $options  Import options (same as importPage).
     *
     * @return array<int, PageTemplateInterface> One PageTemplateInterface per appended page.
     *
     * @throws \Com\Tecnick\Pdf\Exception
     * @throws \Com\Tecnick\Pdf\Import\ImportException
     * @throws \Com\Tecnick\Pdf\Page\Exception
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Unicode\Exception
     */
    public function appendDocument(string $sourceId, ?array $range = null, array $options = []): array
    {
        $prevPid = (int) $this->page->getPageID();

        $templates = $this->importPages($sourceId, $range, $options);
        foreach ($templates as $tpl) {
            $tplW = $this->toUnit($tpl->getWidth());
            $tplH = $this->toUnit($tpl->getHeight());
            $this->addPage([
                'format' => '',
                'width' => $tplW,
                'height' => $tplH,
                'orientation' => $tplW > $tplH ? 'L' : 'P',
            ]);
            $this->useImportedPage($tpl, 0.0, 0.0, $tplW, $tplH, ['keepAspectRatio' => false]);
        }

        if ($prevPid >= 0) {
            /** @var array{width: float, height: float} $prevPage */
            $prevPage = $this->page->setCurrentPage($prevPid);
            $this->graph->setPageWidth($prevPage['width']);
            $this->graph->setPageHeight($prevPage['height']);
        }

        return $templates;
    }

    /**
     * Normalize import options according to current conformance constraints.
     *
     * - `groupXObject` is automatically disabled when transparency is not allowed.
     * - when `groupXObject` is enabled, enforce a minimum PDF version of 1.4.
     *
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     *
     * @throws PdfException
     */
    private function normalizeImportOptions(array $options): array
    {
        $useGroup =
            !\array_key_exists('groupXObject', $options)
            || $options['groupXObject'] === true
            || $options['groupXObject'] === 1
            || $options['groupXObject'] === '1'
            || $options['groupXObject'] === 'true';
        if ($useGroup && !$this->isTransparencyAllowed()) {
            $useGroup = false;
        }

        if ($useGroup && \version_compare($this->pdfver, '1.4', '<')) {
            $this->setPDFVersion('1.4');
        }

        $options['groupXObject'] = $useGroup;
        return $options;
    }

    /**
     * Normalize numeric-like input to float.
     */
    private function getNumericFloatValue(mixed $value): float
    {
        return \is_numeric($value) ? (float) $value : 0.0;
    }
}
