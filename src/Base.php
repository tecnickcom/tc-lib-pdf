<?php

/**
 * Base.php
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2023 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf;

use Com\Tecnick\Barcode\Barcode;
use Com\Tecnick\Color\Pdf;
use Com\Tecnick\File\Cache;
use Com\Tecnick\File\File;
use Com\Tecnick\Pdf\Encrypt\Encrypt;
use Com\Tecnick\Pdf\Font\Stack;
use Com\Tecnick\Pdf\Graph\Draw;
use Com\Tecnick\Pdf\Image\Import;
use Com\Tecnick\Pdf\Page\Page;
use Com\Tecnick\Unicode\Convert;

/**
 * Com\Tecnick\Pdf\Base
 *
 * Output PDF data
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2023 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @SuppressWarnings(PHPMD)
 */
abstract class Base
{
    /**
     * Encrypt object
     */
    public Encrypt $encrypt;

    /**
     * Color object
     */
    public Pdf $color;

    /**
     * Barcode object
     */
    public Barcode $barcode;

    /**
     * File object
     */
    public File $file;

    /**
     * Cache object
     */
    public Cache $cache;

    /**
     * Unicode Convert object
     */
    public Convert $uniconv;

    /**
     * Page object
     */
    public Page $page;

    /**
     * Graph object
     */
    public Draw $graph;

    /**
     * Font object
     */
    public Stack $font;

    /**
     * Image Import object
     */
    public Import $image;

    /**
     * TCPDF version.
     */
    protected string $version = '8.0.28';

    /**
     * Time is seconds since EPOCH when the document was created.
     */
    protected int $doctime = 0;

    /**
     *  Time is seconds since EPOCH when the document was modified.
     */
    protected int $docmodtime = 0;

    /**
     * The name of the application that generates the PDF.
     *
     * If the document was converted to PDF from another format,
     * the name of the conforming product that created the original document from which it was converted.
     */
    protected string $creator = 'TCPDF';

    /**
     * The name of the person who created the document.
     */
    protected string $author = 'TCPDF';

    /**
     * Subject of the document.
     */
    protected string $subject = '-';

    /**
     * Title of the document.
     */
    protected string $title = 'PDF Document';

    /**
     * Space-separated list of keywords associated with the document.
     */
    protected string $keywords = 'TCPDF';

    /**
     * Additional XMP data to be appended just before the end of "x:xmpmeta" tag.
     */
    protected string $custom_xmp = '';

    /**
     * Additional XMP RDF data to be appended just before the end of "rdf:RDF" tag.
     */
    protected string $custom_xmp_rdf = '';

    /**
     * Set this to TRUE to add the default sRGB ICC color profile
     */
    protected bool $sRGB = false;

    /**
     * Viewer preferences dictionary controlling the way the document is to be presented on the screen or in print.
     * (Section 8.1 of PDF reference, "Viewer Preferences").
     *
     * @var array Viewer Preferences
     */
    protected array $viewerpref = [];

    /**
     * Boolean flag to set the default document language direction.
     *    False = LTR = Left-To-Right.
     *    True = RTL = Right-To-Left.
     *
     * @val bool
     */
    protected bool $rtl = false;

    /**
     * Document ID.
     */
    protected string $fileid;

    /**
     * Unit of measure.
     */
    protected string $unit = 'mm';

    /**
     * Unit of measure conversion ratio.
     */
    protected float $kunit = 1.0;

    /**
     * Version of the PDF/A mode or 0 otherwise.
     */
    protected int $pdfa = 0;

    /**
     * Enable stream compression.
     */
    protected bool $compress = true;

    /**
     * True if we are in PDF/X mode.
     */
    protected bool $pdfx = false;

    /**
     * True if the document is signed.
     */
    protected bool $sign = false;

    /**
     * True if the signature approval is enabled (for incremental updates).
     */
    protected bool $sigapp = false;

    /**
     * True to subset the fonts.
     */
    protected bool $subsetfont = false;

    /**
     * True for Unicode font mode.
     */
    protected bool $isunicode = true;

    /**
     * Document encoding.
     */
    protected string $encoding = 'UTF-8';

    /**
     * Current PDF object number.
     */
    public int $pon = 0;

    /**
     * PDF version.
     */
    protected string $pdfver = '1.7';

    /**
     * Defines the way the document is to be displayed by the viewer.
     *
     * @var array{
     *          zoom: int|string,
     *          layout: string,
     *          mode: string,
     *      }
     */
    protected array $display = [
        'zoom' => 'default',
        'layout' => 'SinglePage',
        'mode' => 'UseNone',
    ];

    /**
     * Embedded files data.
     */
    protected array $embeddedfiles = [];

    /**
     * Annotations indexed bu object IDs.
     */
    protected array $annotation = [];

    /**
     * Array containing the regular expression used to identify withespaces or word separators.
     *
     * @var array{
     *         r: string,
     *         p: string,
     *         m: string,
     *      }
     */
    protected array $spaceregexp = [
        'r' => '/[^\S\xa0]/',
        'p' => '[^\S\xa0]',
        'm' => '',
    ];

    /**
     * File name of the PDF document.
     */
    protected string $pdffilename;

    /**
     * Raw encoded fFile name of the PDF document.
     */
    protected string $encpdffilename;

    /**
     * Array containing the ID of some named PDF objects.
     */
    protected array $objid = [];

    /**
     * Store XObject.
     */
    protected array $xobject = [];

    /**
     * Outlines Data.
     */
    protected array $outlines = [];

    /**
     * Outlines Root object ID.
     */
    protected int $outlinerootoid = 0;

    /**
     * Javascript catalog entry.
     */
    protected string $jstree = '';

    /**
     * Embedded files Object IDs by name.
     */
    protected array $efnames = [];

    /**
     * Signature Data.
     */
    protected array $signature = [];

    /**
     * ByteRange placemark used during digital signature process.
     *
     * @var string
     */
    protected const BYTERANGE = '/ByteRange[0 ********** ********** **********]';

    /**
     * Digital signature max length.
     *
     * @var int
     */
    protected const SIGMAXLEN = 11742;

    /**
     * User rights Data.
     */
    protected array $userrights = [];

    /**
     * XObjects data.
     */
    protected array $xobjects = [];

    /**
     * Convert user units to internal points unit.
     *
     * @param float $usr Value to convert.
     */
    public function toPoints(float $usr): float
    {
        return ($usr * $this->kunit);
    }

    /**
     * Convert internal points to user unit.
     *
     * @param float $pnt Value to convert in user units.
     */
    public function toUnit(float $pnt): float
    {
        return ($pnt / $this->kunit);
    }

    /**
     * Convert vertical user value to internal points unit.
     * Note: the internal Y points coordinate starts at the bottom left of the page.
     *
     * @param float $usr   Value to convert.
     * @param float $pageh Optional page height in internal points ($pageh:$this->page->getPage()['pheight']).
     */
    public function toYPoints(float $usr, float $pageh = -1): float
    {
        $pageh = $pageh >= 0 ? $pageh : $this->page->getPage()['pheight'];
        return ($pageh - $this->toPoints($usr));
    }

    /**
     * Convert vertical internal points value to user unit.
     * Note: the internal Y points coordinate starts at the bottom left of the page.
     *
     * @param float $pnt   Value to convert.
     * @param float $pageh Optional page height in internal points ($pageh:$this->page->getPage()['pheight']).
     */
    public function toYUnit(float $pnt, float $pageh = -1): float
    {
        $pageh = $pageh >= 0 ? $pageh : $this->page->getPage()['pheight'];
        return ($pageh - $this->toUnit($pnt));
    }
}
