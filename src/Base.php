<?php

/**
 * Base.php
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

/**
 * Com\Tecnick\Pdf\Base
 *
 * Output PDF data
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2023 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * @SuppressWarnings(PHPMD)
 */
abstract class Base
{
    /**
     * Encrypt object
     *
     * @var \Com\Tecnick\Pdf\Encrypt\Encrypt
     */
    public $encrypt;

    /**
     * Color object
     *
     * @var \Com\Tecnick\Color\Pdf
     */
    public $color;

    /**
     * Barcode object
     *
     * @var \Com\Tecnick\Barcode\Barcode
     */
    public $barcode;

    /**
     * File object
     *
     * @var \Com\Tecnick\File\File
     */
    public $file;

    /**
     * Cache object
     *
     * @var \Com\Tecnick\File\Cache
     */
    public $cache;

    /**
     * Unicode Convert object
     *
     * @var \Com\Tecnick\Unicode\Convert
     */
    public $uniconv;

    /**
     * Page object
     *
     * @var \Com\Tecnick\Pdf\Page\Page
     */
    public $page;

    /**
     * Graph object
     *
     * @var \Com\Tecnick\Pdf\Graph\Draw
     */
    public $graph;

    /**
     * Font object
     *
     * @var \Com\Tecnick\Pdf\Font\Stack
     */
    public $font;

    /**
     * Image Import object
     *
     * @var \Com\Tecnick\Pdf\Image\Import
     */
    public $image;

    /**
     * TCPDF version.
     *
     * @var string
     */
    protected $version = '8.0.28';

    /**
     * Time is seconds since EPOCH when the document was created.
     *
     * @var int
     */
    protected $doctime = 0;

    /**
     *  Time is seconds since EPOCH when the document was modified.
     *
     * @var int
     */
    protected $docmodtime = 0;

    /**
     * The name of the application that generates the PDF.
     *
     * If the document was converted to PDF from another format,
     * the name of the conforming product that created the original document from which it was converted.
     *
     * @var string
     */
    protected $creator = 'TCPDF';

    /**
     * The name of the person who created the document.
     *
     * @var string
     */
    protected $author = 'TCPDF';

    /**
     * Subject of the document.
     *
     * @var string
     */
    protected $subject = '-';

    /**
     * Title of the document.
     *
     * @var string
     */
    protected $title = 'PDF Document';

    /**
     * Space-separated list of keywords associated with the document.
     *
     * @var string
     */
    protected $keywords = 'TCPDF';

    /**
     * Additional XMP data to be appended just before the end of "x:xmpmeta" tag.
     *
     * @var string
     */
    protected $custom_xmp = '';

    /**
     * Additional XMP RDF data to be appended just before the end of "rdf:RDF" tag.
     *
     * @var string
     */
    protected $custom_xmp_rdf = '';

    /**
     * Set this to TRUE to add the default sRGB ICC color profile
     *
     * @var bool
     */
    protected $sRGB = false;

    /**
     * Viewer preferences dictionary controlling the way the document is to be presented on the screen or in print.
     * (Section 8.1 of PDF reference, "Viewer Preferences").
     *
     * @var array
     */
    protected $viewerpref = array();

    /**
     * Boolean flag to set the default document language direction.
     *    False = LTR = Left-To-Right.
     *    True = RTL = Right-To-Left.
     *
     * @val bool
     */
    protected $rtl = false;

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
     * @var bool
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
     * @var array
     */
    protected $display = array('zoom' => 'default', 'layout' => 'SinglePage', 'mode' => 'UseNone');

    /**
     * Embedded files data.
     *
     * @var array
     */
    protected $embeddedfiles = array();

    /**
     * Annotations indexed bu object IDs.
     *
     * @var array
     */
    protected $annotation = array();

    /**
     * Array containing the regular expression used to identify withespaces or word separators.
     *
     * @var array
     */
    protected $spaceregexp = array('r' => '/[^\S\xa0]/', 'p' => '[^\S\xa0]', 'm' => '');

    /**
     * File name of the PDF document.
     *
     * @var string
     */
    protected $pdffilename;

    /**
     * Raw encoded fFile name of the PDF document.
     *
     * @var string
     */
    protected $encpdffilename;

    /**
     * Array containing the ID of some named PDF objects.
     *
     * @var array
     */
    protected $objid = array();

    /**
     * Store XObject.
     *
     * @var array
     */
    protected $xobject = array();

    /**
     * Outlines Data.
     *
     * @var array
     */
    protected $outlines = array();

    /**
     * Outlines Root object ID.
     *
     * @var int
     */
    protected $outlinerootoid = 0;

    /**
     * Javascript catalog entry.
     *
     * @var string
     */
    protected $jstree = '';

    /**
     * Embedded files Object IDs by name.
     *
     * @var array
     */
    protected $efnames = array();

    /**
     * Signature Data.
     *
     * @var array
     */
    protected $signature = array();

    /**
     * ByteRange placemark used during digital signature process.
     *
     * @var string
    */
    protected static $byterange = '/ByteRange[0 ********** ********** **********]';

    /**
     * Digital signature max length.
     *
     * @var int
     */
    protected static $sigmaxlen = 11742;

    /**
     * User rights Data.
     *
     * @var array
     */
    protected $userrights = array();

    /**
     * XObjects data.
     *
     * @var array
     */
    protected $xobjects = array();
}
