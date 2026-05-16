<?php

declare(strict_types=1);

/**
 * Base.php
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

use Com\Tecnick\Barcode\Barcode as ObjBarcode;
use Com\Tecnick\File\Cache as ObjCache;
use Com\Tecnick\File\File as ObjFile;
use Com\Tecnick\Pdf\Encrypt\Encrypt as ObjEncrypt;
use Com\Tecnick\Pdf\Exception as PdfException;
use Com\Tecnick\Pdf\Font\Stack as ObjFont;
use Com\Tecnick\Pdf\Graph\Draw as ObjGraph;
use Com\Tecnick\Pdf\Image\Import as ObjImage;
use Com\Tecnick\Pdf\Import\ImporterInterface as ObjImporter;
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
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type PageInputData from \Com\Tecnick\Pdf\Page\Box
 * @phpstan-import-type PageData from \Com\Tecnick\Pdf\Page\Box
 * @phpstan-import-type TFontMetric from \Com\Tecnick\Pdf\Font\Stack
 *
 * @phpstan-type TViewerPref array{
 *     'HideToolbar'?: bool,
 *     'HideMenubar'?: bool,
 *     'HideWindowUI'?: bool,
 *     'FitWindow'?: bool,
 *     'CenterWindow'?: bool,
 *     'DisplayDocTitle'?: bool,
 *     'NonFullScreenPageMode'?: string,
 *     'Direction'?: string,
 *     'ViewArea'?: string,
 *     'ViewClip'?: string,
 *     'PrintArea'?: string,
 *     'PrintClip'?: string,
 *     'PrintScaling'?: string,
 *     'Duplex'?: string,
 *     'PickTrayByPDFSize'?: bool,
 *     'PrintPageRange'?: array<int>,
 *     'NumCopies'?: int,
 * }
 *
 * @phpstan-type TBBox array{
 *     'x': float, // left position
 *     'y': float, // top position
 *     'w': float, // width
 *     'h': float, // height
 * }
 *
 * @phpstan-type TStackUnitBBox array<int, TBBox>
 *
 * @phpstan-type TStackTextBBox array<int, TBBox>
 *
 * @phpstan-type TStackCellBBox array<int, TBBox>
 *
 * @phpstan-type TCellBound array{
 *     'T': float,
 *     'R': float,
 *     'B': float,
 *     'L': float,
 * }
 *
 * @phpstan-type TCellDef array{
 *     'margin': TCellBound,
 *     'padding': TCellBound,
 *     'borderpos': float,
 * }
 *
 * @phpstan-type TRefUnitValues array{
 *    'parent': float,
 *    'font': array{
 *       'rootsize': float,
 *       'size': float,
 *       'xheight': float,
 *       'zerowidth': float,
 *    },
 *    'viewport': array{
 *       'width': float,
 *       'height': float,
 *    },
 *    'page': array{
 *       'width': float,
 *       'height': float,
 *    },
 * }
 *
 * @phpstan-type TCustomXMP array{
 *    'x:xmpmeta': string,
 *    'x:xmpmeta.rdf:RDF': string,
 *    'x:xmpmeta.rdf:RDF.rdf:Description': string,
 *    'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas': string,
 *    'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag': string,
 * }
 *
 * @phpstan-type TPdfUaStructKid array{
 *    type: 'elem'|'mcid',
 *    id: int,
 * }
 *
 * @phpstan-type TPdfUaStructElem array{
 *    role: string,
 *    pid: int,
 *    mcids: int[],
 *    kids: TPdfUaStructKid[],
 *    alt?: string,
 *    annots?: int[],
 *    attr?: array<string, string>,
 * }
 *
 * @phpstan-type TFileOptions array{
 *   allowedHosts?: array<string>,
 *   maxRemoteSize?: int,
 *   curlopts?: array<int, bool|int|string>,
 *   defaultCurlOpts?: array<int, bool|int|string>,
 *   fixedCurlOpts?: array<int, bool|int|string>
 * }
 *
 * @phpstan-type TFourFloat array{
 *        float,
 *        float,
 *        float,
 *        float,
 *    }
 *
 * @phpstan-type TAnnotQuadPoint array{
 *        float,
 *        float,
 *        float,
 *        float,
 *        float,
 *        float,
 *        float,
 *        float,
 *    }
 *
 * @phpstan-type TAnnotBorderStyle array{
 *        'type'?: string,
 *        'w': int,
 *        's': string,
 *        'd'?: array<int>,
 *    }
 *
 * @phpstan-type TAnnotBorderEffect array{
 *        's'?: string,
 *        'i'?: float,
 *    }
 *
 * @phpstan-type TAnnotMeasure array{
 *        'type'?: string,
 *        'subtype'?: string,
 *    }
 *
 * @phpstan-type TAnnotMarkup array{
 *        't'?: string,
 *        'popup'?: array<mixed>,
 *        'ca'?: float,
 *        'rc'?: string,
 *        'creationdate'?: string,
 *        'irt'?: array<mixed>,
 *        'subj'?: string,
 *        'rt'?: string,
 *        'it'?: string,
 *        'exdata'?: array{
 *      'type'?: string,
 *      'subtype': string,
 *        },
 *    }
 *
 * @phpstan-type TAnnotStates array{
 *        'marked'?: string,
 *        'review'?: string,
 *    }
 *
 * @phpstan-type TAnnotText array{
 *        'subtype': string,
 *        'open'?: bool,
 *        'name'?: string,
 *        'state'?: string,
 *        'statemodel'?: string,
 *    }
 *
 * @phpstan-type TUriAction array{
 *       's': string,
 *       'uri': string,
 *       'ismap'?: bool,
 *    }
 *
 * @phpstan-type TAnnotActionDict array{
 *        'type'?: string,
 *        's'?: string,
 *        'next'?: array<int, array<mixed>>,
 *    }
 *
 * @phpstan-type TAnnotAdditionalActionDict array{
 *        'e'?: TAnnotActionDict,
 *        'x'?: TAnnotActionDict,
 *        'd'?: TAnnotActionDict,
 *        'u'?: TAnnotActionDict,
 *        'fo'?: TAnnotActionDict,
 *        'bi'?: TAnnotActionDict,
 *        'po'?: TAnnotActionDict,
 *        'pc'?: TAnnotActionDict,
 *        'pv'?: TAnnotActionDict,
 *        'pi'?: TAnnotActionDict,
 *    }
 *
 * @phpstan-type TAnnotLink array{
 *        'subtype': string,
 *        'a'?: TAnnotActionDict,
 *        'dest'?: string|array<mixed>,
 *        'h'?: string,
 *        'pa'?: TUriAction,
 *        'quadpoints'?: array<int, TAnnotQuadPoint>,
 *        'bs'?: TAnnotBorderStyle,
 *    }
 *
 * @phpstan-type TAnnotFreeText array{
 *        'subtype': string,
 *        'da': string,
 *        'q'?: int,
 *        'rc'?: string,
 *        'ds'?: string,
 *        'cl'?: array<float>,
 *        'it'?: string,
 *        'be'?: TAnnotBorderEffect,
 *        'rd'?: TFourFloat,
 *        'bs'?: TAnnotBorderStyle,
 *        'le'?: string,
 *    }
 *
 * @phpstan-type TAnnotLine array{
 *        'subtype': string,
 *        'l': TFourFloat,
 *        'bs'?: TAnnotBorderStyle,
 *        'le'?: array{
 *            string,
 *            string
 *        },
 *        'ic'?: TFourFloat,
 *        'll'?: float,
 *        'lle'?: float,
 *        'cap'?: bool,
 *        'it'?: string,
 *        'llo'?: float,
 *        'cp'?: string,
 *        'measure'?: TAnnotMeasure,
 *        'co'?: array{
 *            float,
 *            float
 *        },
 *    }
 *
 * @phpstan-type TAnnotSquare array{
 *        'subtype': string,
 *        'bs'?: TAnnotBorderStyle,
 *        'ic'?: TFourFloat,
 *        'be'?: TAnnotBorderEffect,
 *        'rd'?: TFourFloat,
 *    }
 *
 * @phpstan-type TAnnotCircle TAnnotSquare
 *
 * @phpstan-type TAnnotPolygon array{
 *        'subtype': string,
 *        'vertices'?: array<float>,
 *        'le'?: array{
 *            string,
 *            string
 *        },
 *        'bs'?: TAnnotBorderStyle,
 *        'ic'?: TFourFloat,
 *        'be'?: TAnnotBorderEffect,
 *        'it'?: string,
 *        'measure'?: TAnnotMeasure,
 *    }
 *
 * @phpstan-type TAnnotPolyline TAnnotPolygon
 *
 * @phpstan-type TAnnotTextMarkup array{
 *        'subtype': string,
 *        'quadpoints': array<int, TAnnotQuadPoint>,
 *    }
 *
 * @phpstan-type TAnnotCaret array{
 *        'subtype': string,
 *        'rd'?: TFourFloat,
 *        'sy'?: string,
 *    }
 *
 * @phpstan-type TAnnotRubberStamp array{
 *        'subtype': string,
 *        'name'?: string,
 *    }
 *
 * @phpstan-type TAnnotInk array{
 *        'subtype': string,
 *        'inklist'?: array<int, array<float>>,
 *        'bs'?: TAnnotBorderStyle,
 *    }
 *
 * @phpstan-type TAnnotPopup array{
 *        'subtype': string,
 *        'parent'?: array<mixed>,
 *        'open'?: bool,
 *    }
 *
 * @phpstan-type TAnnotFileAttachment array{
 *        'subtype': string,
 *        'fs'?: string,
 *        'name'?: string,
 *    }
 *
 * @phpstan-type TAnnotSound array{
 *        'subtype': string,
 *        'sound': string,
 *        'name'?: string,
 *    }
 *
 * @phpstan-type TAnnotMovieDict array{
 *        'f': string,
 *        'aspect'?: array{
 *            float,
 *            float
 *        },
 *        'rotate'?: int,
 *        'poster'?: bool|string,
 *    }
 *
 * @phpstan-type TAnnotMovieActDict array{
 *        'start'?: int|string|array{
 *            int|string,
 *            int
 *        },
 *        'duration'?: int|string|array{
 *            int|string,
 *            int
 *        },
 *        'rate'?: float,
 *        'volume'?: float,
 *        'showcontrols'?: bool,
 *        'mode'?: string,
 *        'synchronous'?: bool,
 *        'fwscale'?: array{
 *            int,
 *            int
 *        },
 *        'fwposition'?: array{
 *            float,
 *            float
 *        },
 *    }
 *
 * @phpstan-type TAnnotMovie array{
 *        'subtype': string,
 *        't'?: string,
 *        'movie'?: TAnnotMovieDict,
 *        'a'?: bool|TAnnotMovieActDict,
 *    }
 *
 * @phpstan-type TAnnotIconFitDict array{
 *        'sw'?: string,
 *        's'?: string,
 *        'a'?: array{
 *            float,
 *            float
 *        },
 *        'fb'?: bool,
 *    }
 *
 * @phpstan-type TAnnotMKDict array{
 *        'r'?: int,
 *        'bc'?: TFourFloat,
 *        'bg'?: array{float},
 *        'ca'?: string,
 *        'rc'?: string,
 *        'ac'?: string,
 *        'i'?: string,
 *        'ri'?: string,
 *        'ix'?: string,
 *        'if'?: TAnnotIconFitDict,
 *        'tp'?: int,
 *    }
 *
 * @phpstan-type TAnnotScreen array{
 *        'subtype': string,
 *        't'?: string,
 *        'mk'?: TAnnotMKDict,
 *        'a'?: TAnnotActionDict,
 *        'aa'?: TAnnotAdditionalActionDict,
 *    }
 *
 * @phpstan-type TAnnotWidget array{
 *        'subtype': string,
 *        'h'?: string,
 *        'mk'?: array<array-key, mixed>,
 *        'a'?: TAnnotActionDict,
 *        'aa'?: TAnnotAdditionalActionDict,
 *        'bs'?: TAnnotBorderStyle,
 *        'parent'?: array<mixed>,
 *        'border'?: array<mixed>,
 *        'f'?: int,
 *        'ff'?: int|array<int, int>,
 *        'dv'?: mixed,
 *        'v'?: mixed,
 *        'rv'?: mixed,
 *        't'?: string,
 *        'tm'?: string,
 *        'tu'?: string,
 *        'i'?: array<mixed>,
 *        'opt'?: array<int, string|array{mixed, string}>,
 *        'maxlen'?: int,
 *        'da'?: string,
 *    }
 *
 * @phpstan-type TAnnotFixedPrintDict array{
 *        'type': string,
 *        'matrix'?: array{
 *            float,
 *            float,
 *            float,
 *            float,
 *            float,
 *            float
 *        },
 *        'h'?: float,
 *        'v'?: float,
 *    }
 *
 * @phpstan-type TAnnotWatermark array{
 *        'subtype': string,
 *        'fixedprint'?: TAnnotFixedPrintDict,
 *    }
 *
 * @phpstan-type TAnnotRedact array{
 *        'subtype': string,
 *        'quadpoints'?: array<int, TAnnotQuadPoint>,
 *        'ic'?: TFourFloat,
 *        'ro'?: string,
 *        'overlaytext'?: string,
 *        'repeat'?: bool,
 *        'da'?: string,
 *        'q'?: int,
 *    }
 *
 * @phpstan-type TAnnotOptsA TAnnotText|TAnnotLink|TAnnotFreeText
 * @phpstan-type TAnnotOptsB TAnnotLine|TAnnotSquare|TAnnotCircle|TAnnotPolygon|TAnnotPolyline
 * @phpstan-type TAnnotOptsC TAnnotTextMarkup|TAnnotCaret|TAnnotRubberStamp|TAnnotInk|TAnnotPopup
 * @phpstan-type TAnnotOptsD TAnnotFileAttachment|TAnnotSound|TAnnotMovie
 * @phpstan-type TAnnotOptsE TAnnotScreen|TAnnotWidget|TAnnotWatermark|TAnnotRedact
 *
 * @phpstan-type TAnnotOpts TAnnotOptsA|TAnnotOptsB|TAnnotOptsC|TAnnotOptsD|TAnnotOptsE
 *
 * @phpstan-type TAnnot array{
 *        'n': int,
 *        'x': float,
 *        'y': float,
 *        'w': float,
 *        'h': float,
 *        'txt': string,
 *        'opt': TAnnotOpts,
 *    }
 *
 * @phpstan-type TGTransparency array{
 *         'CS': string,
 *         'I': bool,
 *         'K': bool,
 *     }
 *
 * @phpstan-type TXOBject array{
 *         'spot_colors': array<string>,
 *         'extgstate': array<int>,
 *         'gradient': array<int>,
 *         'font': array<string>,
 *         'image': array<int>,
 *         'xobject': array<string>,
 *         'annotations': array<int, TAnnot>,
 *         'transparency'?: ?TGTransparency,
 *         'id': string,
 *         'outdata': string,
 *         'n': int,
 *         'x': float,
 *         'y': float,
 *         'w': float,
 *         'h': float,
 *         'pheight': float,
 *         'gheight': float,
 *     }
 *
 * @phpstan-type TPatternObject array{
 *         'id': string,
 *         'n': int,
 *         'outdata': string,
 *         'bbox': array{
 *             float,
 *             float,
 *             float,
 *             float
 *         },
 *         'xstep': float,
 *         'ystep': float,
 *         'matrix': array{
 *             float,
 *             float,
 *             float,
 *             float,
 *             float,
 *             float
 *         },
 *     }
 *
 * @phpstan-type TSVGMaskObject array{
 *         'id': string,
 *         'stream': string,
 *         'bbox': array{
 *             float,
 *             float,
 *             float,
 *             float
 *         },
 *         'gs_n': int,
 *     }
 *
 * @phpstan-type TOutline array{
 *         't': string,
 *         'u': string,
 *         'l': int,
 *         'p': int,
 *         'x': float,
 *         'y': float,
 *         's': string,
 *         'c': string,
 *         'parent': int,
 *         'last': int,
 *         'first': int,
 *         'prev': int,
 *         'next': int,
 *     }
 *
 * @phpstan-type TLtvConfig array{
 *        'enabled': bool,
 *        'embed_ocsp': bool,
 *        'embed_crl': bool,
 *        'embed_certs': bool,
 *        'include_dss': bool,
 *        'include_vri': bool,
 *    }
 *
 * @phpstan-type TSignature array{
 *        'appearance': array{
 *            'ap'?: string|array<string, string|array<string, string>>,
 *            'as'?: string,
 *            'empty': array<int, array{
 *                'objid': int,
 *                'name': string,
 *                'page': int,
 *                'rect': string,
 *            }>,
 *            'name': string,
 *            'page': int,
 *            'rect': string,
 *            'xobj'?: string,
 *        },
 *        'approval': string,
 *        'cert_type': int,
 *        'extracerts': ?string,
 *        'info': array{
 *            'ContactInfo': string,
 *            'Location': string,
 *            'Name': string,
 *            'Reason': string,
 *        },
 *        'password': string,
 *        'privkey': string,
 *        'signcert': string,
 *        'ltv'?: TLtvConfig,
 *    }
 *
 * @phpstan-type TSignTimeStamp array{
 *        'enabled': bool,
 *        'host': string,
 *        'username': string,
 *        'password': string,
 *        'cert': string,
 *        'hash_algorithm': string,
 *        'policy_oid': string,
 *        'nonce_enabled': bool,
 *        'timeout': int,
 *        'verify_peer': bool,
 *    }
 *
 * @phpstan-type TUserRights array{
 *        'annots': string,
 *        'document': string,
 *        'ef': string,
 *        'enabled': bool,
 *        'form': string,
 *        'formex': string,
 *        'signature': string,
 *    }
 *
 * @phpstan-type TEmbeddedFile array{
 *        'a': int,
 *        'f': int,
 *        'n': int,
 *        'file': string,
 *        'content': string,
 *        'mimeType': string,
 *        'afRelationship': string,
 *        'description': string,
 *        'creationDate': int,
 *        'modDate': int,
 *    }
 *
 * @phpstan-type TObjID array{
 *        'catalog': int,
 *        'dests': int,
 *        'dss': int,
 *        'form': array<int>,
 *        'info': int,
 *        'pages': int,
 *        'resdic': int,
 *        'signature': int,
 *        'srgbicc': int,
 *        'xmp': int,
 *    }
 *
 * @phpstan-type TSignDocPrepared array{
 *        'byte_range': array{
 *            int,
 *            int,
 *            int,
 *            int
 *        },
 *        'pdfdoc': string,
 *        'pdfdoc_length': int,
 *    }
 *
 * @phpstan-type TValidationCert array{
 *        'pem': string,
 *        'der': string,
 *        'serial': string,
 *        'subject': string,
 *        'issuer': string,
 *        'ocsp_urls': array<int, string>,
 *        'crl_dp_urls': array<int, string>,
 *    }
 *
 * @phpstan-type TValidationVri array{
 *        'certs': array<int>,
 *        'ocsp': array<int>,
 *        'crls': array<int>,
 *    }
 *
 * @phpstan-type TValidationMaterial array{
 *        'cert_chain': array<int, TValidationCert>,
 *        'certs': array<int, string>,
 *        'ocsp': array<int, string>,
 *        'crls': array<int, string>,
 *        'vri': array<string, TValidationVri>,
 *    }
 *
 * @SuppressWarnings("PHPMD")
 */
abstract class Base
{
    /**
     * TCPDF version.
     */
    protected string $version = '8.22.1';

    /**
     * Encrypt object.
     */
    public ObjEncrypt $encrypt;

    /**
     * Color object.
     */
    public PdfColor $color;

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
    protected string $title = '';

    /**
     * Space-separated list of keywords associated with the document.
     */
    protected string $keywords = 'TCPDF';

    /**
     * Additional custom XMP data.
     *
     * @var TCustomXMP
     */
    protected array $custom_xmp = [
        'x:xmpmeta' => '',
        'x:xmpmeta.rdf:RDF' => '',
        'x:xmpmeta.rdf:RDF.rdf:Description' => '',
        'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas' => '',
        'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag' => '',
    ];

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
     * Boolean flag to set temporary document language direction.
     *    False = LTR = Left-To-Right.
     *    True = RTL = Right-To-Left.
     *
     * @val bool
     */
    protected bool $tmprtl = false;

    /**
     * Document ID.
     */
    protected string $fileid;

    /**
     * Unit of measure.
     */
    protected string $unit = 'mm';

    /**
     * Minimum SVG unit length in points.
     */
    protected float $svgminunitlen = 0.0;

    /**
     * Valid HTML/CSS/SVG units.
     *
     * @var array<string>
     */
    protected const VALIDUNITS = [
        '%',
        'ch',
        'cm',
        'em',
        'ex',
        'in',
        'mm',
        'pc',
        'pt',
        'px',
        'rem',
        'vh',
        'vmax',
        'vmin',
        'vw',
    ];

    /**
     * Map of relative font sizes.
     * The key is the relative size and the value is the font size increment in points.
     *
     * @var array<string, float>
     */
    protected const FONTRELSIZE = [
        'xx-small' => -4.0,
        'x-small' => -3.0,
        'smaller' => -3.0,
        'small' => -2.0,
        'medium' => 0.0,
        'large' => 2.0,
        'larger' => 3.0,
        'x-large' => 4.0,
        'xx-large' => 6.0,
    ];

    /**
     * Ration for small font.
     *
     * @var float
     */
    protected const FONT_SMALL_RATIO = 2 / 3;

    /**
     * Default monospaced font.
     *
     * @var string
     */
    protected const FONT_MONO = 'courier';

    /**
     * Default eference values for unit conversion.
     *
     * @var TRefUnitValues
     */
    protected const REFUNITVAL = [
        'parent' => 1.0,
        'font' => [
            'rootsize' => 10.0,
            'size' => 10.0,
            'xheight' => 5.0,
            'zerowidth' => 3.0,
        ],
        'viewport' => [
            'width' => 1000.0,
            'height' => 1000.0,
        ],
        'page' => [
            'width' => 595.276,
            'height' => 841.890,
        ],
    ];

    /**
     * DPI (Dot Per Inch) PDF Document Resolution (do not change).
     * 1pt = 1/72 inch.
     *
     * @var float
     */
    protected const DPI_PDF = 72.0;

    /**
     * DPI (Dot Per Inch) Image/CSS Resolution (do not change).
     * 1pt = 1/96 inch.
     *
     * @var float
     */
    protected const DPI_IMG = 96.0;

    /**
     * DPI (Dot Per Inch) ratio between internal PDF points and pixels.
     *
     * @var float
     */
    protected const DPI_PIXEL_RATIO = self::DPI_PDF / self::DPI_IMG;

    /**
     * Unit of measure conversion ratio.
     */
    protected float $kunit = 1.0;

    /**
     * Version of the PDF/A mode or 0 otherwise.
     */
    protected int $pdfa = 0;

    /**
     * PDF/A conformance level:
     * - 'A' (Accessible): Full compliance including tagged PDF and Unicode mapping.
     * - 'B' (Basic): Visual appearance preservation.
     * - 'U' (Unicode): Basic + Unicode character mapping (PDF/A-2 and PDF/A-3 only).
     */
    protected string $pdfaConformance = 'B';

    /**
     * Enable stream compression.
     */
    protected bool $compress = true;

    /**
     * True if we are in PDF/X mode.
     */
    protected bool $pdfx = false;

    /**
     * Normalized PDF/X mode string or empty when disabled.
     */
    protected string $pdfxMode = '';

    /**
     * Normalized PDF/UA mode string or empty when disabled.
     */
    protected string $pdfuaMode = '';

    /**
     * Count of MCID-tagged content blocks per page (keyed by page PID) for PDF/UA output.
     *
     * @var array<int, int>
     */
    protected array $pdfuapagemcid = [];

    /**
     * Stack of currently open PDF/UA structure elements.
     * Each entry preserves its ordered kids (MCRs and nested StructElems).
     *
     * @var array<int, TPdfUaStructElem>
     */
    protected array $pdfuaStructStack = [];

    /**
     * Log of completed PDF/UA structure elements.
     * Parent/child relationships are preserved through the ordered kids list.
     *
     * @var array<int, TPdfUaStructElem>
     */
    protected array $pdfuaStructLog = [];

    /**
     * Tracks the last emitted heading level (1-6) for PDF/UA heading-nesting validation.
     * 0 means no heading has been emitted yet in the current document.
     */
    protected int $pdfuaHeadingLevel = 0;

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
        'dss' => 0,
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

    /**
     * Signature Data.
     *
     * @var TSignature
     */
    protected array $signature = [
        'appearance' => [
            'ap' => [],
            'as' => '',
            'empty' => [],
            'name' => '',
            'page' => 0,
            'rect' => '',
            'xobj' => '',
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
        'ltv' => [
            'enabled' => false,
            'embed_ocsp' => true,
            'embed_crl' => true,
            'embed_certs' => true,
            'include_dss' => true,
            'include_vri' => true,
        ],
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
        'hash_algorithm' => 'sha256',
        'policy_oid' => '',
        'nonce_enabled' => true,
        'timeout' => 5,
        'verify_peer' => true,
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
    protected const SIGMAXLEN = 11_742;

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
     * PDF importer instance (null until first import call).
     *
     * @var ObjImporter|null
     */
    protected ?ObjImporter $importer = null;

    /**
     * Pattern objects data.
     *
     * @var array<string, TPatternObject>
     */
    protected array $patterns = [];

    /**
     * SVG mask objects data (Form XObject + SMask + ExtGState pipeline).
     *
     * @var array<string, TSVGMaskObject>
     */
    protected array $svgmasks = [];

    /**
     * Stack of Unit bounding boxes [x, y, w, h] in user units.
     *
     * @var TStackUnitBBox
     */
    protected array $bbox = [[
        'x' => 0.0,
        'y' => 0.0,
        'w' => 0.0,
        'h' => 0.0,
    ]];

    /**
     * Stack of Text bounding boxes [x, y, w, h] in user units.
     *
     * @var TStackTextBBox
     */
    protected array $textbbox = [[
        'x' => 0.0,
        'y' => 0.0,
        'w' => 0.0,
        'h' => 0.0,
    ]];

    /**
     * Stack of Cell bounding boxes [x, y, w, h] in user units.
     *
     * @var TStackCellBBox
     */
    protected array $cellbbox = [[
        'x' => 0.0,
        'y' => 0.0,
        'w' => 0.0,
        'h' => 0.0,
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
    public const BORDERPOS_DEFAULT = 0.0;

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
     * Default values for cell boundaries.
     *
     * @const TCellBound
     */
    public const ZEROCELLBOUND = [
        'T' => 0.0,
        'R' => 0.0,
        'B' => 0.0,
        'L' => 0.0,
    ];

    /**
     * Default values for cell.
     *
     * @const TCellDef
     */
    public const ZEROCELL = [
        'margin' => self::ZEROCELLBOUND,
        'padding' => self::ZEROCELLBOUND,
        'borderpos' => self::BORDERPOS_DEFAULT,
    ];

    /**
     * Default values for cell.
     *
     * @var TCellDef
     */
    protected array $defcell = self::ZEROCELL;

    /**
     * Convert user units to internal points unit.
     *
     * @param float $usr Value to convert.
     */
    public function toPoints(float $usr): float
    {
        return $usr * $this->kunit;
    }

    /**
     * Convert internal points to user unit.
     *
     * @param float $pnt Value to convert in user units.
     */
    public function toUnit(float $pnt): float
    {
        return $pnt / $this->kunit;
    }

    /**
     * Convert vertical user value to internal points unit.
     * Note: the internal Y points coordinate starts at the bottom left of the page.
     *
     * @param float $usr   Value to convert.
     * @param float $pageh Optional page height in internal points ($pageh:$this->page->getPage()['pheight']).
     *
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    public function toYPoints(float $usr, float $pageh = -1): float
    {
        if ($pageh < 0) {
            $page = $this->page->getPage();
            $pheight = $page['pheight'];
            return $pheight - $this->toPoints($usr);
        }
        return $pageh - $this->toPoints($usr);
    }

    /**
     * Convert vertical internal points value to user unit.
     * Note: the internal Y points coordinate starts at the bottom left of the page.
     *
     * @param float $pnt   Value to convert.
     * @param float $pageh Optional page height in internal points ($pageh:$this->page->getPage()['pheight']).
     *
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    public function toYUnit(float $pnt, float $pageh = -1): float
    {
        if ($pageh < 0) {
            $page = $this->page->getPage();
            $pheight = $page['pheight'];
            return $this->toUnit($pheight - $pnt);
        }
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

    /**
     * Convert value from given unit to points.
     *
     * @param string|float|int $val    The numeric value, possibly with unit.
     * @param array{
     *     'font': array{'rootsize': float, 'size': float, 'xheight': float, 'zerowidth': float},
     *     'page': array{'height': float, 'width': float},
     *     'parent': float,
     *     'viewport': array{'height': float, 'width': float}
     * } $ref Reference unit values.
     * @param string           $defunit Default unit name.
     *
     * @return float
     *
     * @throws PdfException
     */
    protected function getUnitValuePoints(
        string|float|int $val,
        array $ref = self::REFUNITVAL,
        string $defunit = 'px',
    ): float {
        $unit = 'px';
        if (\in_array($defunit, self::VALIDUNITS, true)) {
            $unit = $defunit;
        }

        $value = 0.0;
        if (\is_numeric($val)) {
            $value = \floatval($val);
        } else {
            $match = [];
            if (\preg_match('/([0-9\.\-\+]+)([a-z%]{0,4})/', $val, $match) === 1 && isset($match[1], $match[2])) {
                $value = \floatval($match[1]);
                if (\in_array($match[2], self::VALIDUNITS, true)) {
                    $unit = $match[2];
                }
            } else {
                throw new PdfException('Invalid value: ' . $val);
            }
        }

        return match ($unit) {
            // Percentage relative to the parent element.
            '%' => ($value * $ref['parent']) / 100,
            // Relative to the width of the "0" (zero)
            'ch' => $value * $ref['font']['zerowidth'],
            // Centimeters.
            'cm' => ($value * self::DPI_PDF) / 2.54,
            // Relative to the font-size of the element.
            'em' => $value * $ref['font']['size'],
            // Relative to the x-height of the current font.
            'ex' => $value * $ref['font']['xheight'],
            // Inches.
            'in' => $value * self::DPI_PDF,
            // Millimeters.
            'mm' => ($value * self::DPI_PDF) / 25.4,
            // One pica is 12 points.
            'pc' => $value * 12,
            // Points.
            'pt' => $value,
            // Pixels.
            'px' => $value * self::DPI_PIXEL_RATIO,
            // Relative to font-size of the root element.
            'rem' => $value * $ref['font']['rootsize'],
            // Relative to 1% of the height of the viewport.
            'vh' => ($value * $ref['viewport']['height']) / 100,
            // Relative to 1% of viewport's* larger dimension.
            'vmax' => ($value * \max($ref['viewport']['height'], $ref['viewport']['width'])) / 100,
            // Relative to 1% of viewport's smaller dimension.
            'vmin' => ($value * \min($ref['viewport']['height'], $ref['viewport']['width'])) / 100,
            // Relative to 1% of the width of the viewport.
            'vw' => ($value * $ref['viewport']['width']) / 100,
            default => throw new PdfException('Unsupported unit: ' . $unit),
        };
    }

    /**
     * Converts a string containing font size value to internal points.
     * This is used to convert values for SVG, CSS, HTML.
     *
     * @param string|float|int $val String containing values and unit.
     * @param TRefUnitValues $ref Reference values in internal points.
     * @param string $defunit Default unit (can be one of the VALIDUNITS).
     *
     * @return float Internal points value.
     *
     * @throws PdfException
     */
    protected function getFontValuePoints(
        string|float|int $val,
        array $ref = self::REFUNITVAL,
        string $defunit = 'pt',
    ): float {
        if (\is_string($val) && isset(self::FONTRELSIZE[$val])) {
            return $ref['parent'] + self::FONTRELSIZE[$val];
        }

        return $this->getUnitValuePoints($val, $ref, $defunit);
    }

    /**
     * Set the default document language direction.
     *
     * @param bool $enabled False = LTR = Left-To-Right; True = RTL = Right-To-Left.
     */
    public function setRTL(bool $enabled): static
    {
        $this->rtl = $enabled;
        return $this;
    }

    /**
     * Force temporary RTL language direction.
     *
     * @param string $mode 'L' = 'LTR' = Left-To-Right; 'R' = 'RTL' = Right-To-Left.
     */
    protected function setTmpRTL(string $mode): void
    {
        $this->tmprtl = $mode !== '' && \strtoupper($mode[0]) === 'R';
    }

    /**
     * Return the current temporary RTL status.
     *
     * @return bool
     */
    protected function isRTL(): bool
    {
        return $this->rtl || $this->tmprtl;
    }

    /**
     * Return true when transparency features are allowed for the active conformance mode.
     *
     * PDF/X-1a and PDF/X-3 (including generic PDF/X alias handling) disallow live transparency,
     * while PDF/X-4 and PDF/X-5 allow it.
     */
    protected function isTransparencyAllowed(): bool
    {
        if (!$this->pdfx) {
            return true;
        }

        return \in_array($this->pdfxMode, ['pdfx4', 'pdfx5'], true);
    }

    /**
     * Return true when the active PDF/X variant should avoid DeviceRGB process colors.
     *
     * PDF/X-1a and PDF/X-3 are treated as restrictive process-color variants in this
     * implementation. PDF/X-4 and PDF/X-5 remain unrestricted.
     */
    protected function requiresPdfxDeviceCmyk(): bool
    {
        if (!$this->pdfx) {
            return false;
        }

        return !\in_array($this->pdfxMode, ['pdfx4', 'pdfx5'], true);
    }

    /**
     * Initialize dependencies class objects.
     *
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
     *
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    public function initClassObjects(?ObjEncrypt $objEncrypt = null, ?array $fileOptions = null): void
    {
        if ($objEncrypt instanceof ObjEncrypt) {
            $this->encrypt = $objEncrypt;
        } else {
            $this->encrypt = new ObjEncrypt();
        }

        $this->color = new PdfColor();
        $this->color->setForceDeviceCmyk($this->requiresPdfxDeviceCmyk());
        $this->barcode = new ObjBarcode();
        $this->file = new ObjFile(
            $fileOptions['allowedHosts'] ?? [],
            $fileOptions['maxRemoteSize'] ?? 52_428_800,
            $fileOptions['curlopts'] ?? [],
            $fileOptions['defaultCurlOpts'] ?? null,
            $fileOptions['fixedCurlOpts'] ?? null,
        );
        $this->cache = new ObjCache();
        $this->uniconv = new ObjUniConvert();

        $pdfamode = $this->pdfa > 0;

        $this->page = new ObjPage($this->unit, $this->color, $this->encrypt, $pdfamode, $this->compress, $this->sigapp);

        $this->kunit = $this->page->getKUnit();
        $this->svgminunitlen = $this->toUnit(0.01);

        $this->graph = new ObjGraph(
            $this->kunit,
            0, // $this->graph->setPageWidth($pagew)
            0, // $this->graph->setPageHeight($pageh)
            $this->color,
            $this->encrypt,
            $pdfamode,
            $this->compress,
        );

        $this->font = new ObjFont($this->kunit, $this->subsetfont, $this->isunicode, $pdfamode, $fileOptions);

        $this->image = new ObjImage($this->kunit, $this->encrypt, $pdfamode, $this->compress, $fileOptions);
    }
}
