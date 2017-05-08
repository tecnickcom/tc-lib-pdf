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
}
