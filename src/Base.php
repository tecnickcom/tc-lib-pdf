<?php

/**
 * Base.php
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

use Com\Tecnick\Barcode\Barcode as ObjBarcode;
use Com\Tecnick\Color\Pdf as ObjColor;
use Com\Tecnick\File\Cache as ObjCache;
use Com\Tecnick\File\File as ObjFile;
use Com\Tecnick\Pdf\Encrypt\Encrypt as ObjEncrypt;
use Com\Tecnick\Pdf\Font\Stack as ObjFont;
use Com\Tecnick\Pdf\Graph\Draw as ObjGraph;
use Com\Tecnick\Pdf\Image\Import as ObjImage;
use Com\Tecnick\Pdf\Page\Page as ObjPage;
use Com\Tecnick\Unicode\Convert as ObjUniConvert;

/**
 * Com\Tecnick\Pdf\Base
 *
 * Output PDF data
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type PageInputData from \Com\Tecnick\Pdf\Page\Box
 * @phpstan-import-type PageData from \Com\Tecnick\Pdf\Page\Box
 * @phpstan-import-type TFontMetric from \Com\Tecnick\Pdf\Font\Stack
 *
 * @phpstan-type TViewerPref array{
 *        'HideToolbar'?: bool,
 *        'HideMenubar'?: bool,
 *        'HideWindowUI'?: bool,
 *        'FitWindow'?: bool,
 *        'CenterWindow'?: bool,
 *        'DisplayDocTitle'?: bool,
 *        'NonFullScreenPageMode'?: string,
 *        'Direction'?: string,
 *        'ViewArea'?: string,
 *        'ViewClip'?: string,
 *        'PrintArea'?: string,
 *        'PrintClip'?: string,
 *        'PrintScaling'?: string,
 *        'Duplex'?: string,
 *        'PickTrayByPDFSize'?: bool,
 *        'PrintPageRange'?: array<int>,
 *        'NumCopies'?: int,
 *    }
 *
 * @phpstan-type TBBox array{
 *          'x': float,
 *          'y': float,
 *          'w': float,
 *          'h': float,
 *      }
 *
 * @phpstan-type TCellDef array{
 *        'margin': array{
 *            'T': float,
 *            'R': float,
 *            'B': float,
 *            'L': float,
 *        },
 *        'padding': array{
 *            'T': float,
 *            'R': float,
 *            'B': float,
 *            'L': float,
 *        },
 *       'borderpos': float,
 *    }
 *
 * @phpstan-type TStackBBox array<int, TBBox>
 *
 * @phpstan-import-type TAnnot from Output
 * @phpstan-import-type TEmbeddedFile from Output
 * @phpstan-import-type TObjID from Output
 * @phpstan-import-type TOutline from Output
 * @phpstan-import-type TSignature from Output
 * @phpstan-import-type TSignTimeStamp from Output
 * @phpstan-import-type TGTransparency from Output
 * @phpstan-import-type TUserRights from Output
 * @phpstan-import-type TXOBject from Output
 *
 * @SuppressWarnings(PHPMD)
 */
abstract class Base
{
   /**
    * Encrypt object.
    */
    public ObjEncrypt $encrypt;

   /**
    * Color object.
    */
    public ObjColor $color;

   /**
    * Barcode object.
    */
    public ObjBarcode $barcode;

   /**
    * File object.
    */
    public ObjFile $file;

   /**
    * Cache object.
    */
    public ObjCache $cache;

   /**
    * Unicode Convert object.
    */
    public ObjUniConvert $uniconv;

   /**
    * Page object.
    */
    public ObjPage $page;

   /**
    * Graph object.
    */
    public ObjGraph $graph;

   /**
    * Font object.
    */
    public ObjFont $font;

   /**
    * Image Import object.
    */
    public ObjImage $image;

    /**
     * TCPDF version.
     */
    protected string $version = '8.0.77';

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
     * (PDF reference, "Viewer Preferences").
     *
     * @var TViewerPref
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
     *
     * @var array<string, TEmbeddedFile>
     */
    protected array $embeddedfiles = [];

    /**
     * Annotations indexed bu object IDs.
     *
     * @var array<int, TAnnot>
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
     *
     * @var TObjID
     */
    protected array $objid = [
        'catalog' => 0,
        'dests' => 0,
        'form' => [],
        'info' => 0,
        'pages' => 0,
        'resdic' => 0,
        'signature' => 0,
        'srgbicc' => 0,
        'xmp' => 0,
    ];

    /**
     * Current XOBject template ID.
     *
     * @var string
     */
    protected string $xobjtid = '';

    /**
     * Outlines Data.
     *
     * @var array<int, TOutline>
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

    // /**
    //  * Embedded files Object IDs by name.
    //  */
    // protected array $efnames = [];

    /**
     * Signature Data.
     *
     * @var TSignature
     */
    protected array $signature = [
        'appearance' => [
            'empty' => [],
            'name' => '',
            'page' => 0,
            'rect' => '',
        ],
        'approval' => '',
        'cert_type' => -1,
        'extracerts' => null,
        'info' => [
            'ContactInfo' => '',
            'Location' => '',
            'Name' => '',
            'Reason' => '',
        ],
        'password' => '',
        'privkey' => '',
        'signcert' => '',
    ];

    /**
     * Signature Timestamp Data.
     *
     * @var TSignTimeStamp
     */
    protected array $sigtimestamp = [
        'enabled' => false,
        'host' => '',
        'username' => '',
        'password' => '',
        'cert' => '',
    ];

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
     *
     * @var TUserRights
     */
    protected array $userrights = [
        'annots' => '/Create/Delete/Modify/Copy/Import/Export',
        'document' => '/FullSave',
        'ef' => '/Create/Delete/Modify/Import',
        'enabled' => false,
        'form' => '/Add/Delete/FillIn/Import/Export/SubmitStandalone/SpawnTemplate',
        'formex' => '', // 'BarcodePlaintext',
        'signature' => '/Modify',
    ];

    /**
     * XObjects data.
     *
     * @var array<string, TXOBject>
     */
    protected array $xobjects = [];

    /**
     * Stack of bounding boxes [x, y, width, height] in user units.
     *
     * @var TStackBBox
     */
    protected array $bbox = [[
        'x' => 0,
        'y' => 0,
        'w' => 0,
        'h' => 0,
    ]];

    /**
     * Set to true to enable the default page footer.
     *
     * @var bool
     */
    protected bool $defPageContentEnabled = false;

    /**
     * Default font for defautl page content.
     *
     * @var ?TFontMetric
     */
    protected ?array $defaultfont = null;
    /**
     * The default relative position of the cell origin when
     * the border is centered on the cell edge.
     */
    public const BORDERPOS_DEFAULT = 0;

    /**
     * The relative position of the cell origin when
     * the border is external to the cell edge.
     */
    public const BORDERPOS_EXTERNAL = -0.5; //-1/2

    /**
     * The relative position of the cell origin when
     * the border is internal to the cell edge.
     */
    public const BORDERPOS_INTERNAL = 0.5; // 1/2

    /**
     * Default values for cell.
     *
     * @const TCellDef
     */
    public const ZEROCELL = [
        'margin' => [
            'T' => 0,
            'R' => 0,
            'B' => 0,
            'L' => 0,
        ],
        'padding' => [
            'T' => 0,
            'R' => 0,
            'B' => 0,
            'L' => 0,
        ],
        'borderpos' => self::BORDERPOS_DEFAULT,
    ];

    /**
     * Default values for cell.
     *
     * @var TCellDef
     */
    protected $defcell = self::ZEROCELL;

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
        return $this->toUnit($pageh - $pnt);
    }

    /**
     * Enable or disable the default page content.
     *
     * @param bool $enable Enable or disable the default page content.
     *
     * @return void
     */
    public function enableDefaultPageContent(bool $enable = true): void
    {
        $this->defPageContentEnabled = $enable;
    }
}
