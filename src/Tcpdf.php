<?php
/**
 * Tcpdf.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2017 Nicola Asuni - Tecnick.com LTD
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
     * Document ID
     *
     * @var string
     */
    protected $fileid;

    /**
     * Unit of measure
     *
     * @var string
     */
    protected $unit = 'mm';

    /**
     * Unit of measure conversion ratio
     *
     * @var float
     */
    protected $kunit = 1.0;

    /**
     * True if we are in PDF/A mode.
     *
     * @var bool
     */
    protected $pdfa = false;

    /**
     * True if we are in PDF/X mode.
     *
     * @var bool
     */
    protected $pdfx = false;

    /**
     * True if the signature approval is enabled (for incremental updates).
     *
     * @var bool
     */
    protected $sigapp = false;

    /**
     * True to subset the fonts
     *
     * @var boolean
     */
    protected $subsetfont = false;

    /**
     * True for Unicode font mode
     *
     * @var boolean
     */
    protected $isunicode = true;

    /**
     * Current PDF object number
     *
     * @var int
     */
    protected $pon = 0;

    /**
     * PDF version
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
     * Initialize a new PDF object
     *
     * @param string     $unit        Unit of measure ('pt', 'mm', 'cm', 'in')
     * @param bool       $isunicode   True if the document is in Unicode mode
     * @param bool       $subsetfont  If true subset the embedded fonts to remove the unused characters
     * @param string     $mode        PDF mode: "pdfa", "pdfx" or empty
     * @param ObjEncrypt $encobj      Encryption object
     */
    public function __construct(
        $unit = 'mm',
        $isunicode = true,
        $subsetfont = false,
        $mode = '',
        ObjEncrypt $encobj = null
    ) {
        setlocale(LC_NUMERIC, 'C');
        $this->doctime = time();
        $this->docmodtime = $this->doctime;
        $seedobj = new \Com\Tecnick\Pdf\Encrypt\Type\Seed();
        $this->fileid = md5($seedobj->encrypt('TCPDF'));
        $this->unit = $unit;
        $this->isunicode = $isunicode;
        $this->subsetfont = $subsetfont;
        $this->pdfa = ($mode == 'pdfa');
        $this->pdfx = ($mode == 'pdfx');
        $this->setPDFVersion();
        $this->encrypt = $encobj;
        $this->initClassObjects();
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
}
