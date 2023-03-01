<?php
/**
 * Tcpdf.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2023 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf;

use \Com\Tecnick\Pdf\Exception as PdfException;
use \Com\Tecnick\Pdf\Encrypt\Encrypt as ObjEncrypt;

/**
 * Com\Tecnick\Pdf\Tcpdf
 *
 * Tcpdf PDF class
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2017 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class Tcpdf extends \Com\Tecnick\Pdf\ClassObjects
{
    /**
     * Document ID.
     *
     * @var string
     */
    protected $fileid;

    /**
     * Unit of measure.
     *
     * @var string
     */
    protected $unit = 'mm';

    /**
     * Unit of measure conversion ratio.
     *
     * @var float
     */
    protected $kunit = 1.0;

    /**
     * Version of the PDF/A mode or 0 otherwise.
     *
     * @var int
     */
    protected $pdfa = 0;

    /**
     * Enable stream compression.
     *
     * @var int
     */
    protected $compress = true;

    /**
     * True if we are in PDF/X mode.
     *
     * @var bool
     */
    protected $pdfx = false;

    /**
     * True if the document is signed.
     *
     * @var bool
     */
    protected $sign = false;

    /**
     * True if the signature approval is enabled (for incremental updates).
     *
     * @var bool
     */
    protected $sigapp = false;

    /**
     * True to subset the fonts.
     *
     * @var boolean
     */
    protected $subsetfont = false;

    /**
     * True for Unicode font mode.
     *
     * @var boolean
     */
    protected $isunicode = true;

    /**
     * Document encoding.
     *
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Current PDF object number.
     *
     * @var int
     */
    public $pon = 0;

    /**
     * PDF version.
     *
     * @var string
     */
    protected $pdfver = '1.7';

    /**
     * Defines the way the document is to be displayed by the viewer.
     *
     * @var string
     */
    protected $display = array('zoom' => 'default', 'layout' => 'SinglePage', 'mode' => 'UseNone');

    /**
     * Embedded files data.
     *
     * @var array
     */
    protected $embeddedfiles = array();

    /**
     * Array containing the regular expression used to identify withespaces or word separators.
     *
     * @var array
     */
    protected $spaceregexp = array('r' => '/[^\S\xa0]/', 'p' => '[^\S\xa0]', 'm' => '');

    /**
     * Initialize a new PDF object.
     *
     * @param string     $unit        Unit of measure ('pt', 'mm', 'cm', 'in').
     * @param bool       $isunicode   True if the document is in Unicode mode.
     * @param bool       $subsetfont  If true subset the embedded fonts to remove the unused characters.
     * @param bool       $compress    Set to false to disable stream compression.
     * @param string     $mode        PDF mode: "pdfa1", "pdfa2", "pdfa3", "pdfx" or empty.
     * @param ObjEncrypt $encobj      Encryption object.
     */
    public function __construct(
        $unit = 'mm',
        $isunicode = true,
        $subsetfont = false,
        $compress = true,
        $mode = '',
        ObjEncrypt $encobj = null
    ) {
        $this->setDecimalSeparator();
        $this->doctime = time();
        $this->docmodtime = $this->doctime;
        $seedobj = new \Com\Tecnick\Pdf\Encrypt\Type\Seed();
        $this->fileid = md5($seedobj->encrypt('TCPDF'));
        $this->unit = $unit;
        $this->setUnicodeMode($isunicode);
        $this->subsetfont = (bool) $subsetfont;
        $this->setPDFMode($mode);
        $this->setCompressMode($compress);
        $this->setPDFVersion();
        $this->encrypt = $encobj;
        $this->initClassObjects();
    }

    /**
     * Set the pdf mode.
     *
     * @param string $mode Input PDFA mode.
     */
    protected function setPDFMode($mode)
    {
        $this->pdfx = ($mode == 'pdfx');
        $this->pdfa = 0;
        $matches = array('', '0');
        if (preg_match('/^pdfa([1-3])$/', $mode, $matches) === 1) {
            $this->pdfa = (int) $matches[1];
        }
    }

    /**
     * Set the compression mode.
     *
     * @param bool $compress Set to false to disable stream compression.
     */
    protected function setCompressMode($compress)
    {
        $this->compress = (((bool) $compress) && ($this->pdfa != 3));
    }

    /**
     * Set the decimal separator.
     *
     * @throw PdfException in case of error.
     */
    protected function setDecimalSeparator()
    {
        // check for locale-related bug
        if (1.1 == 1) {
            throw new PdfException('Don\'t alter the locale before including class file');
        }
        // check for decimal separator
        if (sprintf('%.1F', 1.0) != '1.0') {
            setlocale(LC_NUMERIC, 'C');
        }
    }

    /**
     * Set the decimal separator.
     *
     * @param bool $unicode True when using Unicode mode.
     */
    protected function setUnicodeMode($isunicode)
    {
        $this->isunicode = (bool) $isunicode;
        // check if PCRE Unicode support is enabled
        if ($this->isunicode && (@preg_match('/\pL/u', 'a') == 1)) {
            $this->setSpaceRegexp('/(?!\xa0)[\s\p{Z}]/u');
            return;
        }
        // PCRE unicode support is turned OFF
        $this->setSpaceRegexp('/[^\S\xa0]/');
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
    public function setSpaceRegexp($regexp = '/[^\S\xa0]/')
    {
        $parts = explode('/', $regexp);
        $this->spaceregexp = array(
            'r' => $regexp,
            'p' => (empty($parts[1]) ? '[\s]' : $parts[1]),
            'm' => (empty($parts[2]) ? '' : $parts[2]),
        );
    }

    /**
     * Defines the way the document is to be displayed by the viewer.
     *
     * @param mixed  $zoom   The zoom to use. It can be one of the following string values or a number indicating the
     *                       zooming factor to use.
     *                       * fullpage: displays the entire page on screen
     *                       * fullwidth: uses maximum width of window
     *                       * real: uses real size (equivalent to 100% zoom)
     *                       * default: uses viewer default mode
     * @param string $layout The page layout. Possible values are:
     *                       * SinglePage Display one page at a time
     *                       * OneColumn Display the pages in one column
     *                       * TwoColumnLeft Display the pages in two columns, with odd-numbered pages on the left
     *                       * TwoColumnRight Display the pages in two columns, with odd-numbered pages on the right
     *                       * TwoPageLeft Display the pages two at a time, with odd-numbered pages on the left
     *                       * TwoPageRight Display the pages two at a time, with odd-numbered pages on the right
     * @param string $mode   A name object specifying how the document should be displayed when opened:
     *                       * UseNone Neither document outline nor thumbnail images visible
     *                       * UseOutlines Document outline visible
     *                       * UseThumbs Thumbnail images visible
     *                       * FullScreen Full screen, with no menu bar, window controls, or any other window visible
     *                       * UseOC (PDF 1.5) Optional content group panel visible
     *                       * UseAttachments (PDF 1.6) Attachments panel visible
     */
    public function setDisplayMode($zoom = 'default', $layout = 'SinglePage', $mode = 'UseNone')
    {
        if (is_numeric($zoom) || in_array($zoom, $this->valid_zoom)) {
            $this->display['zoom'] = $zoom;
        } else {
            $this->display['zoom'] = 'default';
        }
        $this->display['layout'] = $this->page->getLayout($layout);
        $this->display['page'] = $this->page->getDisplay($mode);
        return $this;
    }

    /**
     * Get a barcode PDF code.
     *
     * @param string $type    Barcode type.
     * @param string $code    Barcode content.
     * @param float  $posx   Abscissa of upper-left corner.
     * @param float  $posy   Ordinate of upper-left corner.
     * @param int    $width   Barcode width in user units (excluding padding).
     *                        A negative value indicates the multiplication factor for each column.
     * @param int    $height  Barcode height in user units (excluding padding).
     *                        A negative value indicates the multiplication factor for each row.
     * @param array  $padding Additional padding to add around the barcode (top, right, bottom, left) in user units.
     *                        A negative value indicates the multiplication factor for each row or column.
     *
     * @return string
     *
     * @throws BarcodeException in case of error
     */
    public function getBarcode(
        $type,
        $code,
        $posx = 0,
        $posy = 0,
        $width = -1,
        $height = -1,
        $padding = array(0, 0, 0, 0),
        array $style = array()
    ) {
        $bobj = $this->barcode->getBarcodeObj($type, $code, $width, $height, 'black', $padding);
        $bars = $bobj->getBarsArray('XYWH');
        $out = '';
        $out .= $this->graph->getStartTransform();
        $out .= $this->graph->getStyleCmd($style);
        foreach ($bars as $rect) {
            $out .= $this->graph->getBasicRect(($posx + $rect[0]), ($posy + $rect[1]), $rect[2], $rect[3], 'f');
        }
        $out .= $this->graph->getStopTransform();
        return $out;
    }
}
