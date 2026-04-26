<?php

/**
 * Output.php
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

use Com\Tecnick\Pdf\Exception as PdfException;
use Com\Tecnick\Pdf\Font\Output as OutFont;

/**
 * Com\Tecnick\Pdf\Output
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
 * @phpstan-import-type PageData from \Com\Tecnick\Pdf\Page\Box
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
 *        'type': string,
 *        'w': int,
 *        's': string,
 *        'd': array<int>,
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
 *        'le'?: array{string, string},
 *        'ic'?: TFourFloat,
 *        'll'?: float,
 *        'lle'?: float,
 *        'cap'?: bool,
 *        'it'?: string,
 *        'llo'?: float,
 *        'cp'?: string,
 *        'measure'?: TAnnotMeasure,
 *        'co'?: array{float, float},
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
 *        'le'?: array{string, string},
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
 *        'aspect'?: array{float, float},
 *        'rotate'?: int,
 *        'poster'?: bool|string,
 *    }
 *
 * @phpstan-type TAnnotMovieActDict array{
 *        'start'?: int|string|array{int|string, int},
 *        'duration'?: int|string|array{int|string, int},
 *        'rate'?: float,
 *        'volume'?: float,
 *        'showcontrols'?: bool,
 *        'mode'?: string,
 *        'synchronous'?: bool,
 *        'fwscale'?: array{int, int},
 *        'fwposition'?: array{float, float},
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
 *        'a'?: array{float, float},
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
 *        'mk'?: TAnnotMKDict,
 *        'a'?: TAnnotActionDict,
 *        'aa'?: TAnnotAdditionalActionDict,
 *        'bs'?: TAnnotBorderStyle,
 *        'parent'?: array<mixed>,
 *    }
 *
 * @phpstan-type TAnnotFixedPrintDict array{
 *        'type': string,
 *        'matrix'?: array{float, float, float, float, float, float},
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
 *         'bbox': array{float, float, float, float},
 *         'xstep': float,
 *         'ystep': float,
 *         'matrix': array{float, float, float, float, float, float},
 *     }
 *
 * @phpstan-type TSVGMaskObject array{
 *         'id': string,
 *         'stream': string,
 *         'bbox': array{float, float, float, float},
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
 * @phpstan-type TSignature array{
 *        'appearance': array{
 *            'empty': array<int, array{
 *                'objid': int,
 *                'name': string,
 *                'page': int,
 *                'rect': string,
 *            }>,
 *            'name': string,
 *            'page': int,
 *            'rect': string,
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
 * @phpstan-type TLtvConfig array{
 *        'enabled': bool,
 *        'embed_ocsp': bool,
 *        'embed_crl': bool,
 *        'embed_certs': bool,
 *        'include_dss': bool,
 *        'include_vri': bool,
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
 *        'byte_range': array{int, int, int, int},
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
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class Output extends \Com\Tecnick\Pdf\MetaInfo
{
    /**
     * Object to export fornt data.
     *
     * @var OutFont
     */
    protected OutFont $outfont;

    /**
     * PDF layers.
     *
     * @var array<int, array{
     *         'layer': string,
     *         'name': string,
     *         'intent': string,
     *         'print': bool,
     *         'view': bool,
     *         'lock': bool,
     *         'objid': int,
     *     }>
     */
    protected array $pdflayer = [];

    /**
     * Language array.
     *
     * @var array<string, string>
     */
    protected array $lang = [];

    /**
     * StructTreeRoot object ID.
     */
    protected int $structtreerootoid = 0;

    /**
     * ParentTree object ID.
     */
    protected int $parenttreeoid = 0;

    /**
     * Struct parent keys assigned to page object IDs.
     *
     * @var array<int, int>
     */
    protected array $pagestructparents = [];

    /**
     * Count of MCID-tagged content blocks per page object ID, for PDF/UA structure tree building.
     *
     * @var array<int, int>
     */
    protected array $pagestructmcids = [];

    /**
     * Returns the RAW PDF string.
     */
    public function getOutPDFString(): string
    {
        $out = $this->getOutPDFHeader()
            . $this->getOutPDFBody();
        $startxref = \strlen($out);
        $offset = $this->getPDFObjectOffsets($out);
        $out .= $this->getOutPDFXref($offset)
            . $this->getOutPDFTrailer()
            . 'startxref' . "\n"
            . $startxref . "\n"
            . '%%EOF' . "\n";
        return $this->signDocument($out);
    }

    /**
     * Returns the PDF document header.
     */
    protected function getOutPDFHeader(): string
    {
        return '%PDF-' . $this->pdfver . "\n"
            . "%\xE2\xE3\xCF\xD3\n";
    }

    /**
     * Returns the raw PDF Body section.
     */
    protected function getOutPDFBody(): string
    {
        if ($this->pdfuaMode === '') {
            $this->pagestructmcids = [];
            $this->pdfuaStructLog = [];
            $this->pdfuaStructStack = [];
        }

        $out = $this->page->getPdfPages($this->pon);
        $this->objid['pages'] = $this->page->getRootObjID();
        if ($this->pdfuaMode !== '') {
            $out = $this->setPageStructParents($out);
        } else {
            $this->pagestructparents = [];
        }
        $out .= $this->graph->getOutExtGState($this->pon);
        $this->pon = $this->graph->getObjectNumber();
        $out .= $this->getOutOCG();
        $this->outfont = new OutFont(
            $this->font->getFonts(),
            $this->pon,
            $this->encrypt,
        );
        $out .= $this->outfont->getFontsBlock();
        $this->pon = $this->outfont->getObjectNumber();
        $out .= $this->image->getOutImagesBlock($this->pon);
        $this->pon = $this->image->getObjectNumber();
        $out .= $this->color->getPdfSpotObjects($this->pon);
        $out .= $this->graph->getOutGradientShaders($this->pon);
        $this->pon = $this->graph->getObjectNumber();
        $out .= $this->getOutXObjects();
        $out .= $this->getOutPatterns();
        $out .= $this->getOutSVGMasks();
        $out .= $this->getOutResourcesDict();
        $out .= $this->getOutDestinations();
        $out .= $this->getOutEmbeddedFiles();
        $out .= $this->getOutStructTreeRoot();
        $out .= $this->getOutAnnotations();
        $out .= $this->getOutJavascript();
        $out .= $this->getOutBookmarks();
        $enc = $this->encrypt->getEncryptionData();
        // PDF/X prohibits encryption (ISO 15930); skip the encryption object when PDF/X mode is active.
        if ($enc['encrypted'] && !$this->pdfx) {
            $out .= $this->encrypt->getPdfEncryptionObj($this->pon);
        }

        $out .= $this->getOutSignatureFields();
        $out .= $this->getOutSignature();
        $out .= $this->getOutDssObjects();
        $out .= $this->getOutMetaInfo();
        $out .= $this->getOutXMP();
        $out .= $this->getOutICC();
        return $out . $this->getOutCatalog();
    }

    /**
     * Returns the ordered offset array for each object.
     *
     * @param string $data Raw PDF data
     *
     * @return array<int> - Ordered offset array for each PDF object
     */
    protected function getPDFObjectOffsets(string $data): array
    {
        \preg_match_all('/(([0-9]+)[\s][0-9]+[\s]obj[\n])/i', $data, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $offset = [];
        foreach ($matches as $match) {
            $offset[($match[2][0])] = $match[2][1];
        }

        \ksort($offset);
        return $offset;
    }

    /**
     * Returns the PDF XREF section.
     *
     * @param array<int> $offset Ordered offset array for each PDF object
     */
    protected function getOutPDFXref(array $offset): string
    {
        $out = 'xref' . "\n"
            . '0 ' . ($this->pon + 1) . "\n"
            . '0000000000 65535 f ' . "\n";
        $freegen = ($this->pon + 2);
        $lastobj = \array_key_last($offset);
        for ($idx = 1; $idx <= $lastobj; ++$idx) {
            if (isset($offset[$idx])) {
                $out .= \sprintf('%010d 00000 n ' . "\n", $offset[$idx]);
            } else {
                $out .= \sprintf('0000000000 %05d f ' . "\n", $freegen);
                ++$freegen;
            }
        }

        return $out;
    }

    /**
     * Returns the PDF Trailer section.
     */
    protected function getOutPDFTrailer(): string
    {
        $out = 'trailer' . "\n"
            . '<<'
            . ' /Size ' . ($this->pon + 1)
            . ' /Root ' . $this->objid['catalog'] . ' 0 R'
            . ' /Info ' . $this->objid['info'] . ' 0 R';
        $enc = $this->encrypt->getEncryptionData();
        // PDF/X prohibits encryption; omit the /Encrypt trailer entry when PDF/X mode is active.
        if (! empty($enc['objid']) && !$this->pdfx) {
            $out .= ' /Encrypt ' . $enc['objid'] . ' 0 R';
        }

        return $out . (' /ID [ <' . $this->fileid . '> <' . $this->fileid . '> ]'
            . ' >>' . "\n");
    }

    /**
     * Returns the PDF object to include a standard sRGB_IEC61966-2.1 blackscaled ICC colour profile.
     */
    protected function getOutICC(): string
    {
        if ($this->pdfa === 0 && ! $this->sRGB) {
            return '';
        }

        $oid = ++$this->pon;
        $this->objid['srgbicc'] = $oid;
        $out = $oid . ' 0 obj' . "\n";
        $icc = \file_get_contents(__DIR__ . '/include/sRGB.icc.z');
        if ($icc === false) {
            throw new PdfException('Unable to read sRGB.icc.z file');
        }

        $icc = $this->encrypt->encryptString($icc, $oid);
        return $out . '<< /N 3 /Filter /FlateDecode /Length ' . \strlen($icc)
            . ' >>'
            . ' stream' . "\n"
            . $icc . "\n"
            . 'endstream' . "\n"
            . 'endobj' . "\n";
    }

    /**
     * Get OutputIntents for sRGB IEC61966-2.1 if required.
     */
    protected function getOutputIntentsSrgb(): string
    {
        if (empty($this->objid['srgbicc'])) {
            return '';
        }

        $oid = $this->objid['catalog'];
        return ' /OutputIntents [<< /Type /OutputIntent /S /GTS_PDFA1 /OutputCondition '
            . $this->getOutTextString('sRGB IEC61966-2.1', $oid, true)
            . ' /OutputConditionIdentifier ' . $this->getOutTextString('sRGB IEC61966-2.1', $oid, true)
            . ' /RegistryName ' . $this->getOutTextString('http://www.color.org', $oid, true)
            . ' /Info ' . $this->getOutTextString('sRGB IEC61966-2.1', $oid, true)
            . ' /DestOutputProfile ' . $this->objid['srgbicc'] . ' 0 R'
            . ' >>]';
    }

    /**
     * Get OutputIntents for PDF-X if required.
     */
    protected function getOutputIntentsPdfXIdentifier(): string
    {
        return match ($this->pdfxMode) {
            'pdfx1a' => 'PDF/X-1a',
            'pdfx3' => 'PDF/X-3',
            'pdfx4' => 'PDF/X-4',
            'pdfx5' => 'PDF/X-5',
            default => 'OFCOM_PO_P1_F60_95',
        };
    }

    protected function getOutputIntentsPdfX(): string
    {
        $oid = $this->objid['catalog'];
        $identifier = $this->getOutputIntentsPdfXIdentifier();
        return ' /OutputIntents [<< /Type /OutputIntent /S /GTS_PDFX /OutputConditionIdentifier '
            . $this->getOutTextString($identifier, $oid, true)
            . ' /RegistryName ' . $this->getOutTextString('http://www.color.org', $oid, true)
            . ' /Info ' . $this->getOutTextString($identifier, $oid, true)
            . ' >>]';
    }

    protected function getOutputIntents(): string
    {
        if (empty($this->objid['catalog'])) {
            return '';
        }

        if ($this->pdfx) {
            return $this->getOutputIntentsPdfX();
        }

        return $this->getOutputIntentsSrgb();
    }

    /**
     * Get the PDF layers.
     */
    protected function getPDFLayers(): string
    {
        if (empty($this->pdflayer) || empty($this->objid['catalog'])) {
            return '';
        }

        $oid = $this->objid['catalog'];
        $lyrobjs = '';
        $lyrobjs_off = '';
        $lyrobjs_lock = '';
        foreach ($this->pdflayer as $layer) {
            $layer_obj_ref = ' ' . $layer['objid'] . ' 0 R';
            $lyrobjs .= $layer_obj_ref;
            if ($layer['view'] === false) {
                $lyrobjs_off .= $layer_obj_ref;
            }

            if ($layer['lock']) {
                $lyrobjs_lock .= $layer_obj_ref;
            }
        }

        return ' /OCProperties << /OCGs [' . $lyrobjs . ' ]'
            . ' /D <<'
            . ' /Name ' . $this->getOutTextString('Layers', $oid, true)
            . ' /Creator ' . $this->getOutTextString($this->creator, $oid, true)
            . ' /BaseState /ON'
            . ' /OFF [' . $lyrobjs_off . ']'
            . ' /Locked [' . $lyrobjs_lock . ']'
            . ' /Intent /View'
            . ' /AS ['
            . ' << /Event /Print /OCGs [' . $lyrobjs . '] /Category [/Print] >>'
            . ' << /Event /View /OCGs [' . $lyrobjs . '] /Category [/View] >>'
            . ' ]'
            . ' /Order [' . $lyrobjs . ']'
            . ' /ListMode /AllPages'
            //.' /RBGroups ['..']'
            //.' /Locked ['..']'
            . ' >>'
            . ' >>';
    }

    /**
     * Returns the PDF Catalog entry.
     */
    protected function getOutCatalog(): string
    {
        $oid = ++$this->pon;
        $this->objid['catalog'] = $oid;
        $out = $oid . ' 0 obj' . "\n"
            . '<<'
            . ' /Type /Catalog'
            . ' /Version /' . $this->pdfver
            //.' /Extensions <<>>'
            . ' /Pages ' . $this->objid['pages'] . ' 0 R'
            //.' /PageLabels ' //...
            . ' /Names <<';
        if ($this->pdfa === 0 && ! $this->pdfx && $this->pdfuaMode === '' && $this->jstree !== '') {
            $out .= ' /JavaScript ' . $this->jstree;
        }

        if ($this->embeddedfiles !== []) {
            $afnames = [];
            $afobjs = [];
            foreach ($this->embeddedfiles as $efname => $efdata) {
                $afnames[] = $this->getOutTextString($efname, $oid) . ' ' . $efdata['f'] . ' 0 R';
                $afobjs[] = $efdata['f'] . ' 0 R';
            }
            $out .= ' /AF [ ' . \implode(' ', $afobjs) . ' ]';
            $out .= ' /EmbeddedFiles << /Names [ ' . \implode(' ', $afnames) . ' ] >>';
        }

        $out .= ' >>';

        if (! empty($this->objid['dests'])) {
            $out .= ' /Dests ' . ($this->objid['dests']) . ' 0 R';
        }

        if (! empty($this->objid['dss'])) {
            $out .= ' /DSS ' . $this->objid['dss'] . ' 0 R';
        }

        $out .= $this->getOutViewerPref();

        if (! empty($this->display['layout'])) {
            $out .= ' /PageLayout /' . $this->display['layout'];
        }

        if ($this->outlines !== []) {
            $out .= ' /Outlines ' . $this->outlinerootoid . ' 0 R';
            if (empty($this->display['mode'])) {
                $this->display['mode'] = 'UseOutlines';
            }
        }

        if (! empty($this->display['mode'])) {
            $out .= ' /PageMode /' . $this->display['mode'];
        }

        //$out .= ' /Threads []';

        $firstpage = $this->page->getPage(0);
        $fpo = $firstpage['n'];
        if ($this->display['zoom'] == 'fullpage') {
            $out .= ' /OpenAction [' . $fpo . ' 0 R /Fit]';
        } elseif ($this->display['zoom'] == 'fullwidth') {
            $out .= ' /OpenAction [' . $fpo . ' 0 R /FitH null]';
        } elseif ($this->display['zoom'] == 'real') {
            $out .= ' /OpenAction [' . $fpo . ' 0 R /XYZ null null 1]';
        } elseif (! \is_string($this->display['zoom'])) {
            $out .= \sprintf(' /OpenAction [' . $fpo . ' 0 R /XYZ null null %F]', ($this->display['zoom'] / 100));
        }

        //$out .= ' /AA <<>>';
        //$out .= ' /URI <<>>';
        $out .= ' /Metadata ' . $this->objid['xmp'] . ' 0 R';
        if ($this->structtreerootoid > 0) {
            $out .= ' /StructTreeRoot ' . $this->structtreerootoid . ' 0 R';
        }

        if ($this->pdfuaMode !== '') {
            $out .= ' /MarkInfo << /Marked true >>';
        }

        $language = $this->lang['a_meta_language'] ?? (($this->pdfuaMode !== '') ? 'en-US' : '');

        if ($language !== '') {
            $out .= ' /Lang ' . $this->getOutTextString($language, $oid, true);
        }

        //$out .= ' /SpiderInfo <<>>';
        $out .= $this->getOutputIntents();
        //$out .= ' /PieceInfo <<>>';
        $out .= $this->getPDFLayers();

        // AcroForm
        if (
            ! empty($this->objid['form'])
            || ($this->sign && ($this->signature['cert_type'] >= 0))
            || ! empty($this->signature['appearance']['empty'])
        ) {
            $out .= ' /AcroForm <<';
            $objrefs = '';
            if ($this->sign && ($this->signature['cert_type'] >= 0)) {
                // set reference for signature object
                $objrefs .= $this->objid['signature'] . ' 0 R';
            }

            if (! empty($this->signature['appearance']['empty'])) {
                foreach ($this->signature['appearance']['empty'] as $esa) {
                    // set reference for empty signature objects
                    $objrefs .= ' ' . $esa['objid'] . ' 0 R';
                }
            }

            if (! empty($this->objid['form'])) {
                foreach ($this->objid['form'] as $objid) {
                    $objrefs .= ' ' . $objid . ' 0 R';
                }
            }

            $out .= ' /Fields [' . $objrefs . ']';
            // It's better to turn off this value and set the appearance stream for
            // each annotation (/AP) to avoid conflicts with signature fields.
            if (empty($this->signature['approval']) || ($this->signature['approval'] != 'A')) {
                $out .= ' /NeedAppearances false';
            }

            if ($this->sign && ($this->signature['cert_type'] >= 0)) {
                if ($this->signature['cert_type'] > 0) {
                    $out .= ' /SigFlags 3';
                } else {
                    $out .= ' /SigFlags 1';
                }
            }

            //$out .= ' /CO ';

            if (! empty($this->annotation_fonts)) {
                $out .= ' /DR << /Font <<';
                foreach ($this->annotation_fonts as $fontkey => $fontid) {
                    $out .= ' /F' . $fontid . ' ' . $this->font->getFont($fontkey)['n'] . ' 0 R';
                }

                $out .= ' >> >>';
            }

            $font = $this->font->getFont('helvetica');
            $out .= ' /DA ' . $this->encrypt->escapeDataString('/F' . $font['i'] . ' 0 Tf 0 g', $oid);
            $out .= ' /Q ' . (($this->rtl) ? '2' : '0');
            //$out .= ' /XFA ';
            $out .= ' >>';

            // signatures
            if (
                $this->sign && ($this->signature['cert_type'] >= 0)
                && (empty($this->signature['approval']) || ($this->signature['approval'] != 'A'))
            ) {
                $out .= ' /Perms << ';
                if ($this->signature['cert_type'] > 0) {
                    $out .= '/DocMDP ';
                } else {
                    $out .= '/UR3 ';
                }

                $out .= ($this->objid['signature'] + 1) . ' 0 R >>';
            }
        }

        //$out .= ' /Legal <<>>';
        //$out .= ' /Requirements []';
        //$out .= ' /Collection <<>>';
        //$out .= ' /NeedsRendering true';

        $out .= ' >>' . "\n"
            . 'endobj' . "\n";
        return $out;
    }

    /**
     * Returns the PDF OCG entry.
     */
    protected function getOutOCG(): string
    {
        if (empty($this->pdflayer)) {
            return '';
        }

        $out = '';
        foreach ($this->pdflayer as $key => $layer) {
            $oid = ++$this->pon;
            $this->pdflayer[$key]['objid'] = $oid;
            $out .= $oid . ' 0 obj' . "\n";

            $out .= '<< /Type /OCG'
                . ' /Name ' . $this->getOutTextString($layer['name'], $oid, true);

            if (!empty($layer['intent'])) {
                $out .= ' /Intent [' . $layer['intent'] . ']';
            }

            $out .= ' /Usage <<';
            if (isset($layer['print'])) {
                $out .= ' /Print << /PrintState /' . $this->getOnOff($layer['print']) . ' >>';
            }
            $out .= ' /View << /ViewState /' . $this->getOnOff($layer['view']) . ' >>';
            // Other (not-implemented) possible /Usage entries:
            //   CreatorInfo, Language, Export, Zoom, User, PageElement.
            $out .= ' >>'; // close /Usage

            $out .= ' >>' . "\n"
                . 'endobj' . "\n";
        }

        return $out;
    }

    /**
     * Returns the PDF Annotation code for Apearance Stream XObjects entry.
     *
     * @param float    $width  annotation width
     * @param float    $height annotation height
     * @param string $stream appearance stream
     */
    protected function getOutAPXObjects(
        float $width = 0,
        float $height = 0,
        string $stream = ''
    ): string {
        $stream = \trim($stream);
        $oid = ++$this->pon;
        $out = $oid . ' 0 obj' . "\n";
        $tid = 'AX' . $oid;
        $this->xobjects[$tid] = [
            'spot_colors' => [],
            'extgstate' => [],
            'gradient' => [],
            'font' => [],
            'image' => [],
            'xobject' => [],
            'annotations' => [],
            'id' => $tid,
            'n' => $oid,
            'x' => 0,
            'w' => 0,
            'y' => 0,
            'h' => 0,
            'outdata' => '',
            'pheight' => 0,
            'gheight' => 0,
        ];
        $out .= '<< /Type /XObject /Subtype /Form /FormType 1';
        if ($this->compress) {
            $stream = \gzcompress($stream);
            if ($stream === false) {
                throw new PdfException('Unable to compress stream');
            }
            $out .= ' /Filter /FlateDecode';
        }

        $stream = $this->encrypt->encryptString($stream, $oid);
        $rect = \sprintf('%F %F', $width, $height);
        $resobj = $this->objid['resdic'] ?? $this->page->getResourceDictObjID();
        return $out . ' /BBox [0 0 ' . $rect . ']'
            . ' /Matrix [1 0 0 1 0 0]'
            . ' /Resources ' . $resobj . ' 0 R'
            . ' /Length ' . \strlen($stream)
            . ' >>'
            . ' stream' . "\n"
            . $stream . "\n"
            . 'endstream' . "\n"
            . 'endobj' . "\n";
    }

    /**
     * Returns the PDF XObjects entry.
     */
    protected function getOutXObjects(): string
    {
        $out = '';
        foreach ($this->xobjects as $data) {
            if (empty($data['outdata'])) {
                continue;
            }

            $out .= $data['n'] . ' 0 obj' . "\n"
                . '<<'
                . ' /Type /XObject'
                . ' /Subtype /Form'
                . ' /FormType 1';
            $stream = \trim($data['outdata']);
            if ($this->compress) {
                $stream = \gzcompress($stream);
                if ($stream === false) {
                    throw new PdfException('Unable to compress stream');
                }
                $out .= ' /Filter /FlateDecode';
            }

            $out .= \sprintf(
                ' /BBox [%F %F %F %F]',
                $this->toPoints($data['x']),
                $this->toPoints(-$data['y']),
                $this->toPoints(($data['w'] + $data['x'])),
                $this->toPoints(($data['h'] - $data['y']))
            );

            $out .= ' /Matrix [1 0 0 1 0 0]'
            . ' /Resources <<'
            . ' /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]';

            $out .= $this->graph->getOutExtGStateResourcesByKeys($data['extgstate']);
            $out .= $this->graph->getOutGradientResourcesByKeys($data['gradient']);
            $out .= $this->color->getPdfSpotResourcesByKeys($data['spot_colors']);
            $out .= $this->outfont->getOutFontDictByKeys($data['font']);

            if (! empty($data['image']) || ! empty($data['xobject'])) {
                $out .= ' /XObject <<';
                $out .= $this->image->getXobjectDictByKeys($data['image']);
                if (! empty($data['xobject'])) {
                    foreach ($data['xobject'] as $xid) {
                        $out .= ' /' . $xid . ' ' . $this->xobjects[$xid]['n'] . ' 0 R';
                    }
                }
                $out .= ' >>';
            }

            $out .= ' >>'; // end of /Resources.

            if (isset($data['transparency']) && $this->isTransparencyAllowed()) {
                // set transparency group
                $out .= ' /Group << /Type /Group /S /Transparency';
                if (!empty($data['transparency'])) {
                    $out .= ' /CS /' . $data['transparency']['CS'];
                    $out .= ' /I /' . (($data['transparency']['I'] === true) ? 'true' : 'false');
                    $out .= ' /K /' . (($data['transparency']['K'] === true) ? 'true' : 'false');
                }
                $out .= ' >>';
            }

            $stream = $this->encrypt->encryptString($stream, $data['n']);
            $out .= ' /Length ' . \strlen($stream)
                . ' >>'
                . ' stream' . "\n"
                . $stream . "\n"
                . 'endstream' . "\n"
                . 'endobj' . "\n";
        }

        return $out;
    }

    /**
     * Returns the PDF Resources Dictionary entry.
     */
    protected function getOutResourcesDict(): string
    {
        $this->objid['resdic'] = $this->page->getResourceDictObjID();
        // Merge SVG mask ExtGState entries into the /ExtGState resource dict.
        $gsResources    = $this->graph->getOutExtGStateResources();
        $maskGsEntries  = $this->getSVGMaskExtGStateEntries();
        if ($maskGsEntries !== '') {
            if ($gsResources === '') {
                $gsResources = ' /ExtGState <<' . $maskGsEntries . ' >>' . "\n";
            } else {
                // Strip closing ' >>\n' and re-append with mask entries.
                $gsResources = \substr(\rtrim($gsResources), 0, -2) . $maskGsEntries . ' >>' . "\n";
            }
        }

        return $this->objid['resdic'] . ' 0 obj' . "\n"
            . '<<'
            . ' /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]'
            . $this->outfont->getOutFontDict()
            . $this->getXObjectDict()
            . $this->getPatternDict()
            . $this->getLayerDict()
            . $gsResources
            . $this->graph->getOutGradientResources()
            . $this->color->getPdfSpotResources()
            . ' >>' . "\n"
            . 'endobj' . "\n";
    }

    /**
     * Returns the PDF Pattern objects entry.
     */
    protected function getOutPatterns(): string
    {
        $out = '';
        foreach ($this->patterns as $pid => $data) {
            if (empty($data['outdata'])) {
                continue;
            }

            if (empty($this->patterns[$pid]['n'])) {
                $this->patterns[$pid]['n'] = ++$this->pon;
            }

            $oid = $this->patterns[$pid]['n'];
            $stream = \trim($data['outdata']);
            $out .= $oid . ' 0 obj' . "\n"
                . '<<'
                . ' /Type /Pattern'
                . ' /PatternType 1'
                . ' /PaintType 1'
                . ' /TilingType 1'
                . \sprintf(
                    ' /BBox [%F %F %F %F]',
                    $data['bbox'][0],
                    $data['bbox'][1],
                    $data['bbox'][2],
                    $data['bbox'][3],
                )
                . ' /XStep ' . \sprintf('%F', $data['xstep'])
                . ' /YStep ' . \sprintf('%F', $data['ystep'])
                . \sprintf(
                    ' /Matrix [%F %F %F %F %F %F]',
                    $data['matrix'][0],
                    $data['matrix'][1],
                    $data['matrix'][2],
                    $data['matrix'][3],
                    $data['matrix'][4],
                    $data['matrix'][5],
                );
            $res = $this->getPatternStreamResourceDict($stream);
            $out .= ' /Resources <<'
                . ' /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]'
                . $res
                . ' >>';

            if ($this->compress) {
                $stream = \gzcompress($stream);
                if ($stream === false) {
                    throw new PdfException('Unable to compress stream');
                }
                $out .= ' /Filter /FlateDecode';
            }

            $stream = $this->encrypt->encryptString($stream, $oid);
            $out .= ' /Length ' . \strlen($stream)
                . ' >>'
                . ' stream' . "\n"
                . $stream . "\n"
                . 'endstream' . "\n"
                . 'endobj' . "\n";
        }

        return $out;
    }

    /**
     * Returns the PDF Form XObject + SMask + ExtGState objects for SVG masks.
     *
     * Each registered SVG mask produces three chained PDF objects:
     *  1. A Form XObject with a DeviceGray transparency group containing the
     *     mask shape stream.
     *  2. A Mask dict pointing at that Form XObject (Luminosity mode).
     *  3. An ExtGState with /SMask referencing the Mask dict.
     *
     * After emission the gs_n for each mask is set so that
     * getSVGMaskExtGStateEntries() can include them in the resources dict.
     *
     * @return string Raw PDF objects.
     */
    protected function getOutSVGMasks(): string
    {
        $out = '';
        foreach ($this->svgmasks as $key => $data) {
            if (empty($data['stream'])) {
                continue;
            }

            $stream  = $data['stream'];
            $bbox    = $data['bbox'];
            $bboxStr = \sprintf('%F %F %F %F', $bbox[0], $bbox[1], $bbox[2], $bbox[3]);

            // Form XObject (DeviceGray transparency group for luminosity mask).
            $formOid    = ++$this->pon;
            $formStream = $stream;
            $formHead   = $formOid . ' 0 obj' . "\n"
                . '<<'
                . ' /Type /XObject'
                . ' /Subtype /Form'
                . ' /FormType 1'
                . ' /BBox [' . $bboxStr . ']'
                . ' /Group << /Type /Group /S /Transparency /CS /DeviceGray /I true >>';
            if ($this->compress) {
                $comp = \gzcompress($formStream);
                if ($comp !== false) {
                    $formStream = $comp;
                    $formHead  .= ' /Filter /FlateDecode';
                }
            }

            $formStream = $this->encrypt->encryptString($formStream, $formOid);
            $out .= $formHead
                . ' /Length ' . \strlen($formStream)
                . ' >>' . "\n"
                . 'stream' . "\n"
                . $formStream . "\n"
                . 'endstream' . "\n"
                . 'endobj' . "\n";

            // SMask dictionary.
            $smaskOid = ++$this->pon;
            $out .= $smaskOid . ' 0 obj' . "\n"
                . '<< /Type /Mask /S /Luminosity /G ' . $formOid . ' 0 R >>' . "\n"
                . 'endobj' . "\n";

            // ExtGState with SMask reference.
            $gsOid = ++$this->pon;
            $out .= $gsOid . ' 0 obj' . "\n"
                . '<< /Type /ExtGState /SMask ' . $smaskOid . ' 0 R /AIS false >>' . "\n"
                . 'endobj' . "\n";

            $this->svgmasks[$key]['gs_n'] = $gsOid;
        }

        return $out;
    }

    /**
     * Returns ExtGState resource-dict entries for SVG masks.
     *
     * Returns a string like " /MSK_xxx N 0 R /MSK_yyy M 0 R" for inclusion
     * inside the page /ExtGState resource dictionary.
     *
     * @return string
     */
    protected function getSVGMaskExtGStateEntries(): string
    {
        $out = '';
        foreach ($this->svgmasks as $key => $mask) {
            if ($mask['gs_n'] > 0) {
                $out .= ' /' . $key . ' ' . $mask['gs_n'] . ' 0 R';
            }
        }

        return $out;
    }

    /**
     * Build a minimized resources dictionary fragment for a pattern stream.
     */
    protected function getPatternStreamResourceDict(string $stream): string
    {
        $fontNames = [];
        if (\preg_match_all('/\/(F[0-9]+)\s+[0-9\.\-]+\s+Tf\b/', $stream, $matchFont) > 0) {
            foreach ($matchFont[1] as $name) {
                $fontNames[$name] = true;
            }
        }

        $extgstate = [];
        if (\preg_match_all('/\/GS([0-9]+)\s+gs\b/', $stream, $matchGs) > 0) {
            foreach ($matchGs[1] as $idx) {
                $extgstate[(int) $idx] = true;
            }
        }

        $gradient = [];
        if (\preg_match_all('/\/Sh([0-9]+)\s+sh\b/', $stream, $matchSh) > 0) {
            foreach ($matchSh[1] as $idx) {
                $gradient[(int) $idx] = true;
            }
        }

        $spotNames = [];
        if (\preg_match_all('/\/(CS[0-9]+)\s+[cC]s\b/', $stream, $matchCs) > 0) {
            foreach ($matchCs[1] as $name) {
                $spotNames[$name] = true;
            }
        }

        $imageKeys = [];
        $xobjectKeys = [];
        if (\preg_match_all('/\/([A-Za-z0-9_]+)\s+Do\b/', $stream, $matchDo) > 0) {
            foreach ($matchDo[1] as $key) {
                if (\preg_match('/^I([0-9]+)$/', $key, $imgm) === 1) {
                    $imageKeys[(int) $imgm[1]] = true;
                    continue;
                }
                if (isset($this->xobjects[$key])) {
                    $xobjectKeys[$key] = true;
                }
            }
        }

        $out = '';
        if (!empty($fontNames)) {
            $fontEntries = $this->extractNamedResourceRefs($this->outfont->getOutFontDict(), \array_keys($fontNames));
            if ($fontEntries !== '') {
                $out .= ' /Font <<' . $fontEntries . ' >>';
            }
        }
        if (!empty($extgstate)) {
            $out .= $this->graph->getOutExtGStateResourcesByKeys(\array_map('intval', \array_keys($extgstate)));
        }
        if (!empty($gradient)) {
            $out .= $this->graph->getOutGradientResourcesByKeys(\array_map('intval', \array_keys($gradient)));
        }
        if (!empty($spotNames)) {
            $spotEntries = $this->extractNamedResourceRefs(
                $this->color->getPdfSpotResources(),
                \array_keys($spotNames),
            );
            if ($spotEntries !== '') {
                $out .= ' /ColorSpace <<' . $spotEntries . ' >>';
            }
        }
        if (!empty($imageKeys) || !empty($xobjectKeys)) {
            $out .= ' /XObject <<';
            if (!empty($imageKeys)) {
                $out .= $this->image->getXobjectDictByKeys(\array_map('intval', \array_keys($imageKeys)));
            }
            if (!empty($xobjectKeys)) {
                foreach (\array_keys($xobjectKeys) as $xid) {
                    $out .= ' /' . $xid . ' ' . $this->xobjects[$xid]['n'] . ' 0 R';
                }
            }
            $out .= ' >>';
        }

        return $out;
    }

    /**
     * Extract named object references from a resource dictionary fragment.
     *
     * @param string $dict Full dictionary fragment (e.g. '/Font << /F1 1 0 R >>').
     * @param array<string> $names Resource names to retain.
     */
    protected function extractNamedResourceRefs(string $dict, array $names): string
    {
        if (($dict === '') || empty($names)) {
            return '';
        }

        $out = '';
        foreach ($names as $name) {
            if (!\is_string($name) || ($name === '')) {
                continue;
            }

            $match = [];
            $ok = \preg_match('/\/' . \preg_quote($name, '/') . '\s+([0-9]+)\s+0\s+R\b/', $dict, $match);
            if (($ok === 1) && isset($match[1])) {
                $out .= ' /' . $name . ' ' . (int) $match[1] . ' 0 R';
            }
        }

        return $out;
    }

    /**
     * Returns the PDF Destinations entry.
     */
    protected function getOutDestinations(): string
    {
        if (empty($this->dests)) {
            return '';
        }

        $oid = ++$this->pon;
        $this->objid['dests'] = $oid;
        $out = $oid . ' 0 obj' . "\n"
            . '<< ';
        foreach ($this->dests as $name => $dst) {
            $page = $this->page->getPage($dst['p']);
            $poid = $page['n'];
            $pgx = $this->toPoints($dst['x']);
            $pgy = $this->toYPoints($dst['y'], $page['pheight']);
            $out .= ' /' . $name . ' ' . \sprintf('[%u 0 R /XYZ %F %F null]', $poid, $pgx, $pgy);
        }

        return $out . ' >>' . "\n"
            . 'endobj' . "\n";
    }

    /**
     * Returns the PDF Embedded Files entry.
     */
    protected function getOutEmbeddedFiles(): string
    {
        if (($this->pdfa == 1) || ($this->pdfa == 2)) {
            // embedded files are not allowed in PDF/A mode version 1 and 2
            return '';
        }

        $out = '';
        \reset($this->embeddedfiles);
        foreach ($this->embeddedfiles as $name => $data) {
            if (!empty($data['content'])) {
                // if content is already set, use it
                $content = $data['content'];
            } else {
                try {
                    $content = $this->file->fileGetContents($data['file']);
                } catch (Exception) {
                    continue; // silently skip the file
                }

                $ctime = \filectime($data['file']);
                if ($ctime !== false) {
                    $data['creationDate'] = $ctime;
                }

                $mtime = \filemtime($data['file']);
                if ($mtime !== false) {
                    $data['modDate'] = $mtime;
                }
            }

            $rawsize = \strlen($content);
            if ($rawsize <= 0) {
                continue; // silently skip the file
            }

            // update name tree
            $oid = $data['f'];
            // embedded file specification object
            $out .= $oid . ' 0 obj' . "\n"
                . '<<'
                . ' /Type /Filespec /F ' . $this->getOutTextString($name, $oid)
                . ' /UF ' . $this->getOutTextString($name, $oid)
                . ' /AFRelationship /' . ($data['afRelationship'] ?? 'Source')
                . ' /Desc ' . $this->getOutTextString(($data['description'] ?? '-'), $data['f'])
                . ' /EF <</F ' . $data['n'] . ' 0 R>>'
                . ' >>' . "\n"
                . 'endobj' . "\n";

            // embedded file object
            $filter = '';
            if ($this->pdfa == 3) {
                $filter = ' /Subtype /'
                    . \str_replace(['/', '+'], ['#2F', '#2B'], ($data['mimeType'] ?? 'application/octet-stream'));
            } elseif ($this->compress) {
                $content = \gzcompress($content);
                if ($content === false) {
                    throw new PdfException('Unable to compress content');
                }
                $filter = ' /Filter /FlateDecode';
            }

            $stream = $this->encrypt->encryptString($content, $data['n']);
            $out .= "\n"
                . $data['n'] . ' 0 obj' . "\n"
                . '<<'
                . ' /Type /EmbeddedFile'
                . $filter
                . ' /Length ' . \strlen($stream)
                . ' /Params <<'
                . ' /Size ' . $rawsize
                . ' /CreationDate ' . $this->getOutDateTimeString($data['creationDate'], $data['n'])
                . ' /ModDate ' . $this->getOutDateTimeString($data['modDate'], $data['n'])
                . ' >>'
                . ' >>'
                . ' stream' . "\n"
                . $stream . "\n"
                . 'endstream' . "\n"
                . 'endobj' . "\n";
        }

        return $out;
    }

    /**
     * Returns the PDF StructTreeRoot entry for tagged PDF modes.
     */
    protected function getOutStructTreeRoot(): string
    {
        if ($this->pdfuaMode === '') {
            $this->pagestructparents = [];
            $this->pagestructmcids = [];
            $this->parenttreeoid = 0;
            $this->structtreerootoid = 0;
            return '';
        }

        $structLog = $this->pdfuaStructLog;
        if ($structLog === []) {
            $this->parenttreeoid = 0;
            $this->structtreerootoid = 0;
            return '';
        }

        // Build pid -> page OID map from serialized page data.
        $pidToOid = [];
        foreach ($this->page->getPages() as $page) {
            if (isset($page['pid']) && \is_int($page['pid']) && isset($page['n']) && \is_int($page['n'])) {
                $pidToOid[$page['pid']] = $page['n'];
            }
        }

        $parentTreeOid = ++$this->pon;
        $structTreeRootOid = $parentTreeOid + 1;
        $documentStructElemOid = $parentTreeOid + 2;

        // Assign OIDs to each struct elem entry.
        // $elemOids[i] = OID for $structLog[i]
        $elemOids = [];
        $nextOid = $parentTreeOid + 3;
        foreach ($structLog as $idx => $_entry) {
            $elemOids[$idx] = $nextOid++;
        }

        $this->pon = $nextOid - 1;
        $this->parenttreeoid = $parentTreeOid;
        $this->structtreerootoid = $structTreeRootOid;

        // Build ParentTree /Nums array:
        // For each page, collect which struct elem OIDs correspond to each MCID in order.
        // pageoid -> mcid -> structElemOid
        $parentTreeMap = [];
        foreach ($structLog as $idx => $entry) {
            $pageOid = $pidToOid[$entry['pid']] ?? 0;
            if ($pageOid > 0) {
                foreach ($entry['mcids'] as $mcid) {
                    $parentTreeMap[$pageOid][$mcid] = $elemOids[$idx];
                }
            }
        }

        $parentNums = '';
        foreach ($this->pagestructparents as $pageOid => $key) {
            $parentNums .= ' ' . $key . ' [';
            if (isset($parentTreeMap[$pageOid])) {
                \ksort($parentTreeMap[$pageOid]);
                foreach ($parentTreeMap[$pageOid] as $structElemOid) {
                    $parentNums .= ' ' . $structElemOid . ' 0 R';
                }
            }

            $parentNums .= ' ]';
        }

        $out = $parentTreeOid . ' 0 obj' . "\n"
            . '<< /Nums [' . $parentNums . ' ] >>' . "\n"
            . 'endobj' . "\n";

        // Document StructElem.
        $firstPageOid = empty($this->pagestructparents) ? 0 : (int) array_key_first($this->pagestructparents);
        $firstTaggedPageOid = $pidToOid[$structLog[0]['pid']] ?? 0;
        if ($firstTaggedPageOid > 0) {
            $firstPageOid = $firstTaggedPageOid;
        }

        $allElemRefsStr = \implode(' ', \array_map(static fn(int $oid) => $oid . ' 0 R', $elemOids));
        $documentStructElem = '<< /Type /StructElem /S /Document /P ' . $structTreeRootOid . ' 0 R';
        if ($firstPageOid > 0) {
            $documentStructElem .= ' /Pg ' . $firstPageOid . ' 0 R';
        }

        $documentStructElem .= ' /K [ ' . $allElemRefsStr . ' ] >>';

        // Paragraph/heading StructElems.
        $structElemsOut = '';
        foreach ($structLog as $idx => $entry) {
            $entryPageOid = $pidToOid[$entry['pid']] ?? 0;
            $mcrList = '';
            foreach ($entry['mcids'] as $mcid) {
                $mcrList .= ' << /Type /MCR /Pg ' . $entryPageOid . ' 0 R /MCID ' . $mcid . ' >>';
            }

            $altOut = '';
            if (isset($entry['alt']) && \is_string($entry['alt']) && $entry['alt'] !== '') {
                $altOut = ' /Alt ' . $this->getOutTextString($entry['alt'], $elemOids[$idx], true);
            }

            $structElemsOut .= $elemOids[$idx] . ' 0 obj' . "\n"
                . '<< /Type /StructElem /S /' . $entry['role'] . ' /P ' . $documentStructElemOid . ' 0 R'
                . ' /Pg ' . $entryPageOid . ' 0 R'
                . $altOut
                . ' /K [' . $mcrList . ' ] >>' . "\n"
                . 'endobj' . "\n";
        }

        return $out
            . $structTreeRootOid . ' 0 obj' . "\n"
            . '<< /Type /StructTreeRoot /ParentTree ' . $parentTreeOid . ' 0 R /ParentTreeNextKey '
            . \count($this->pagestructparents) . ' /K [ ' . $documentStructElemOid . ' 0 R ] >>' . "\n"
            . 'endobj' . "\n"
            . $documentStructElemOid . ' 0 obj' . "\n"
            . $documentStructElem . "\n"
            . 'endobj' . "\n"
            . $structElemsOut;
    }

    /**
     * Inject StructParents entries into serialized page objects for PDF/UA mode.
     */
    protected function setPageStructParents(string $pdfpages): string
    {
        $this->pagestructparents = [];
        $this->pagestructmcids = [];
        $pages = $this->page->getPages();
        $parentKey = 0;
        foreach ($pages as $page) {
            if (! isset($page['n']) || ! \is_int($page['n'])) {
                continue;
            }

            $needle = $page['n'] . ' 0 obj' . "\n<<" . "\n/Type /Page" . "\n";
            $replacement = $page['n'] . ' 0 obj' . "\n<<" . "\n/Type /Page" . "\n/StructParents " . $parentKey . "\n";
            $pdfpages = \str_replace($needle, $replacement, $pdfpages);
            $this->pagestructparents[$page['n']] = $parentKey;
            ++$parentKey;
        }

        return $pdfpages;
    }

    /**
     * Convert a color array into a string representation for annotations.
     * The number of array elements determines the colour space in which the colour shall be defined:
     *     0 No colour; transparent
     *     1 DeviceGray
     *     3 DeviceRGB
     *     4 DeviceCMYK
     *
     * @param array<int|float> $color Array of colors.
     */
    protected static function getColorStringFromPercArray(array $color): string
    {
        $col = \array_values($color);
        $out = '[';
        $out .= match (\count($color)) {
            4 => \sprintf(
                '%F %F %F %F',
                (float) $col[0],
                (float) $col[1],
                (float) $col[2],
                (float) $col[3],
            ),
            3 => \sprintf(
                '%F %F %F',
                (float) $col[0],
                (float) $col[1],
                (float) $col[2],
            ),
            1 => \sprintf(
                '%F',
                (float) $col[0],
            ),
            default => '',
        };
        return $out . ']';
    }

    /**
     * Returns the PDF Annotations entry.
     */
    protected function getOutAnnotations(): string
    {
        $out = '';
        $pages = $this->page->getPages();
        foreach ($pages as $num => $page) {
            foreach ($page['annotrefs'] as $key => $oid) {
                if (empty($this->annotation[$oid])) {
                    continue;
                }
                $annot = $this->annotation[$oid];
                $annot['opt'] = \array_change_key_case($annot['opt'], CASE_LOWER);
                $out .= $this->getAnnotationRadioButtons($annot); // @phpstan-ignore-line
                $orx = $this->toPoints($annot['x']);
                $ory = $this->toYPoints(($annot['y'] + $annot['h']), $page['pheight']);
                $width = $this->toPoints($annot['w']);
                $height = $this->toPoints($annot['h']);
                $rect = \sprintf('%F %F %F %F', $orx, $ory, $orx + $width, $ory + $height);
                $out .= ((int) $oid) . ' 0 obj' . "\n" // @phpstan-ignore-line
                    . '<<'
                    . ' /Type /Annot'
                    . ' /Subtype /' . $annot['opt']['subtype']
                    . ' /Rect [' . $rect . ']';
                $ft = ['Btn', 'Tx', 'Ch', 'Sig'];
                $formfield = (! empty($annot['opt']['ft']) && \in_array($annot['opt']['ft'], $ft));
                if ($formfield) {
                    $out .= ' /FT /' . $annot['opt']['ft']; // @phpstan-ignore-line
                }

                if ($annot['opt']['subtype'] !== 'Link' || $this->pdfuaMode !== '') {
                    $out .= ' /Contents ' . $this->getOutTextString($annot['txt'], $oid, true);
                }

                list($aas, $apx) = $this->getAnnotationAppearanceStream(
                    $annot,
                    $width,
                    $height,
                );

                $out .= ' /P ' . $page['n'] . ' 0 R'
                    . ' /NM ' . $this->encrypt->escapeDataString(\sprintf('%04u-%04u', $page['num'], $key), $oid)
                    . ' /M ' . $this->getOutDateTimeString($this->docmodtime, $oid)
                    . $this->getOutAnnotationFlags($annot) // @phpstan-ignore-line
                    . $aas
                    . $this->getAnnotationBorder($annot); // @phpstan-ignore-line

                if (! empty($annot['opt']['c']) && \is_string($annot['opt']['c'])) {
                     $out .= ' /C [ ' . $this->color->getPdfRgbComponents($annot['opt']['c']) . ' ]';
                }

                //$out .= ' /StructParent ';
                //$out .= ' /OC ';

                $out .= $this->getOutAnnotationMarkups($annot, $oid) // @phpstan-ignore-line
                    . $this->getOutAnnotationOptSubtype($annot, $num, $oid, $key) // @phpstan-ignore-line
                    . ' >>' . "\n"
                    . 'endobj' . "\n"
                    . $apx;
                if (! $formfield) {
                    continue;
                }

                if (isset($this->radiobuttons[$annot['txt']])) {
                    continue;
                }

                $this->objid['form'][] = $oid;
            }
        }

        return $out;
    }

    /**
     * Returns the Annotation code for Radio buttons.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getAnnotationRadioButtons(array $annot): string
    {
        $out = '';
        if (
            empty($this->radiobuttons[$annot['txt']]['kids'])
            || ! \is_array($this->radiobuttons[$annot['txt']])
        ) {
            return $out;
        }

        $oid = $this->radiobuttons[$annot['txt']]['n'];
        $out = $oid . ' 0 obj' . "\n"
            . '<<'
            . ' /Type /Annot'
            . ' /Subtype /Widget'
            . ' /Rect [0 0 0 0]';
        if ($this->radiobuttons[$annot['txt']]['#readonly#']) {
            // read only
            $out .= ' /F 68 /Ff 49153';
        } else {
            $out .= ' /F 4 /Ff 49152'; // default print for PDF/A
        }

        $out .= ' /T ' . $this->encrypt->escapeDataString($annot['txt'], $oid);
        if (! empty($annot['opt']['tu']) && \is_string($annot['opt']['tu'])) {
            $out .= ' /TU ' . $this->encrypt->escapeDataString($annot['opt']['tu'], $oid);
        }

        $out .= ' /FT /Btn /Kids [';
        $defval = '';
        foreach ($this->radiobuttons[$annot['txt']]['kids'] as $kids) {
                $out .= ' ' . $kids['n'] . ' 0 R';
            if ($kids['def'] !== 'Off') {
                $defval = $kids['def'];
            }
        }

        $out .= ' ]';
        if (!empty($defval) && \is_string($defval)) {
            $out .= ' /V /' . $defval;
        }

        $out .= ' >>' . "\n"
            . 'endobj' . "\n";
        $this->objid['form'][] = $oid;
        $this->radiobuttons[$annot['txt']]['kids'] = []; // set only once
        return $out;
    }

    /**
     * Returns the Annotation code for Appearance Stream.
     *
     * @param TAnnot $annot  Array containing page annotations.
     * @param float    $width  Annotation width.
     * @param float    $height Annotation height.
     *
     * @return array{string, string}
     */
    protected function getAnnotationAppearanceStream(
        array $annot,
        float $width = 0,
        float $height = 0
    ): array {
        $out = '';
        if (! empty($annot['opt']['as']) && \is_string($annot['opt']['as'])) {
            $out .= ' /AS /' . $annot['opt']['as'];
        }

        if (empty($annot['opt']['ap'])) {
            return [$out, ''];
        }

        $apxout = '';
        $out .= ' /AP <<';
        if (! \is_array($annot['opt']['ap'])) {
            $out .= $annot['opt']['ap'];
        } else {
            foreach ($annot['opt']['ap'] as $mode => $def) {
                // $mode can be: n = normal; r = rollover; d = down;
                $out .= ' /' . \strtoupper($mode);
                if (\is_array($def)) {
                    $out .= ' <<';
                    foreach ($def as $apstate => $stream) {
                        if (!\is_string($stream)) {
                            continue;
                        }
                        // reference to XObject that define the appearance for this mode-state
                        $apxout .= $this->getOutAPXObjects(
                            $width,
                            $height,
                            $stream,
                        );
                        $out .= ' /' . $apstate . ' ' . $this->pon . ' 0 R';
                    }

                    $out .= ' >>';
                } elseif (\is_string($def)) {
                    // reference to XObject that define the appearance for this mode
                    $apxout .= $this->getOutAPXObjects(
                        $width,
                        $height,
                        $def,
                    );
                    $out .= ' ' . $this->pon . ' 0 R';
                }
            }
        }

        return [
            $out . ' >>',
            $apxout,
        ];
    }

    /**
     * Returns the Annotation code for Borders.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getAnnotationBorder(array $annot): string
    {
        $out = '';
        if (
            ! empty($annot['opt']['bs'])
            && (\is_array($annot['opt']['bs']))
        ) {
            $out .= ' /BS << /Type /Border';
            if (isset($annot['opt']['bs']['w']) && \is_numeric($annot['opt']['bs']['w'])) {
                $out .= ' /W ' . (int) $annot['opt']['bs']['w'];
            }

            $bstyles = ['S', 'D', 'B', 'I', 'U'];
            if (
                ! empty($annot['opt']['bs']['s'])
                && \is_string($annot['opt']['bs']['s'])
                && \in_array($annot['opt']['bs']['s'], $bstyles)
            ) {
                $out .= ' /S /' . $annot['opt']['bs']['s'];
            }

            if (
                isset($annot['opt']['bs']['d'])
                && (\is_array($annot['opt']['bs']['d']))
            ) {
                $out .= ' /D [';
                foreach ($annot['opt']['bs']['d'] as $cord) {
                    if (\is_numeric($cord)) {
                        $out .= ' ' . (int) $cord;
                    }
                }

                $out .= ']';
            }

            $out .= ' >>';
        } else {
            $out .= ' /Border [';
            if (
                isset($annot['opt']['border'])
                && (\count($annot['opt']['border']) >= 3)
                && \is_numeric($annot['opt']['border'][0])
                && \is_numeric($annot['opt']['border'][1])
                && \is_numeric($annot['opt']['border'][2])
            ) {
                $out .= (int) $annot['opt']['border'][0]
                    . ' ' . (int) $annot['opt']['border'][1]
                    . ' ' . (int) $annot['opt']['border'][2];
                if (
                    isset($annot['opt']['border'][3])
                    && \is_array($annot['opt']['border'][3])
                ) {
                    $out .= ' [';
                    foreach ($annot['opt']['border'][3] as $dash) {
                        if (\is_numeric($dash)) {
                            $out .= ' ' . (int) $dash;
                        }
                    }

                    $out .= ' ]';
                }
            } else {
                $out .= '0 0 0';
            }

            $out .= ']';
        }

        if (isset($annot['opt']['be']) && (\is_array($annot['opt']['be']))) {
            $out .= ' /BE <<';
            $bstyles = ['S', 'C'];
            if (
                ! empty($annot['opt']['be']['s'])
                && \is_string($annot['opt']['be']['s'])
                && \in_array($annot['opt']['be']['s'], $bstyles)
            ) {
                $out .= ' /S /' . $annot['opt']['be']['s'];
            } else {
                $out .= ' /S /S';
            }

            if (
                isset($annot['opt']['be']['i'])
                && \is_numeric($annot['opt']['be']['i'])
                && ($annot['opt']['be']['i'] >= 0)
                && ($annot['opt']['be']['i'] <= 2)
            ) {
                $out .= ' /I ' . \sprintf(' %F', $annot['opt']['be']['i']);
            }

            $out .= '>>';
        }

        return $out;
    }

    /**
     * Returns the Annotation code for Makups.
     *
     * @param TAnnot $annot Array containing page annotations.
     * @param int    $oid   Annotation Object ID.
     */
    protected function getOutAnnotationMarkups(
        array $annot,
        int $oid
    ): string {
        $out = '';
        $markups = [
            'text',
            'freetext',
            'line',
            'square',
            'circle',
            'polygon',
            'polyline',
            'highlight',
            'underline',
            'squiggly',
            'strikeout',
            'stamp',
            'caret',
            'ink',
            'fileattachment',
            'sound',
        ];
        if (empty($annot['opt']['subtype']) || ! \in_array(\strtolower($annot['opt']['subtype']), $markups)) {
            return $out;
        }

        if (! empty($annot['opt']['t']) && \is_string($annot['opt']['t'])) {
            $out .= ' /T ' . $this->getOutTextString($annot['opt']['t'], $oid, true);
        }

        //$out .= ' /Popup ';
        if (isset($annot['opt']['ca'])) {
            $out .= ' /CA ' . \sprintf('%F', (float) $annot['opt']['ca']);
        }

        if (isset($annot['opt']['rc'])) {
            $out .= ' /RC ' . $this->getOutTextString($annot['opt']['rc'], $oid, true);
        }

        $out .= ' /CreationDate ' . $this->getOutDateTimeString($this->doctime, $oid);
        //$out .= ' /IRT ';
        if (isset($annot['opt']['subj'])) {
            $out .= ' /Subj ' . $this->getOutTextString($annot['opt']['subj'], $oid, true);
        }

        //$out .= ' /RT ';
        //$out .= ' /IT ';
        //$out .= ' /ExData ';
        return $out;
    }

    /**
     * Returns the Annotation code for Flags.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationFlags(array $annot): string
    {
        $fval = 4;
        if (isset($annot['opt']['f'])) {
            $fval = $this->getAnnotationFlagsCode($annot['opt']['f']);
        }

        if ($this->pdfa > 0) {
            // force print flag for PDF/A mode
            $fval |= 4;
        }

        return ' /F ' . $fval;
    }

    /**
     * Returns the Annotation Flags code.
     *
     * @param int|array<string> $flags Annotation flags.
     */
    protected function getAnnotationFlagsCode(int|array $flags): int
    {
        if (! \is_array($flags)) {
            return $flags;
        }

        $fval = 0;
        foreach ($flags as $flag) {
            $fval += match (\strtolower($flag)) {
                'invisible'      => 1 << 0,
                'hidden'         => 1 << 1,
                'print'          => 1 << 2,
                'nozoom'         => 1 << 3,
                'norotate'       => 1 << 4,
                'noview'         => 1 << 5,
                'readonly'       => 1 << 6,
                'locked'         => 1 << 7,
                'togglenoview'   => 1 << 8,
                'lockedcontents' => 1 << 9,
                default          => 0,
            };
        }

        return $fval;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.
     *
     * @param TAnnot $annot   Array containing page annotations.
     * @param int    $pagenum Page number.
     * @param int    $oid     Annotation Object ID.
     * @param int    $key     Annotation index in the current page.
     */
    protected function getOutAnnotationOptSubtype(array $annot, int $pagenum, int $oid, int $key): string
    {
        return match (\strtolower($annot['opt']['subtype'])) {
            '3d' => $this->getOutAnnotationOptSubtype3D($annot),
            'caret' => $this->getOutAnnotationOptSubtypeCaret($annot),
            'circle' => $this->getOutAnnotationOptSubtypeCircle($annot),
            'fileattachment' => $this->getOutAnnotationOptSubtypeFileattachment($annot, $key),
            'freetext' => $this->getOutAnnotationOptSubtypeFreetext($annot, $oid),
            'highlight' => $this->getOutAnnotationOptSubtypeHighlight($annot),
            'ink' => $this->getOutAnnotationOptSubtypeInk($annot),
            'line' => $this->getOutAnnotationOptSubtypeLine($annot),
            'link' => $this->getOutAnnotationOptSubtypeLink($annot, $pagenum, $oid),
            'movie' => $this->getOutAnnotationOptSubtypeMovie($annot),
            'polygon' => $this->getOutAnnotationOptSubtypePolygon($annot),
            'polyline' => $this->getOutAnnotationOptSubtypePolyline($annot),
            'popup' => $this->getOutAnnotationOptSubtypePopup($annot),
            'printermark' => $this->getOutAnnotationOptSubtypePrintermark($annot),
            'redact' => $this->getOutAnnotationOptSubtypeRedact($annot),
            'screen' => $this->getOutAnnotationOptSubtypeScreen($annot),
            'sound' => $this->getOutAnnotationOptSubtypeSound($annot),
            'square' => $this->getOutAnnotationOptSubtypeSquare($annot),
            'squiggly' => $this->getOutAnnotationOptSubtypeSquiggly($annot),
            'stamp' => $this->getOutAnnotationOptSubtypeStamp($annot),
            'strikeout' => $this->getOutAnnotationOptSubtypeStrikeout($annot),
            'text' => $this->getOutAnnotationOptSubtypeText($annot),
            'trapnet' => $this->getOutAnnotationOptSubtypeTrapnet($annot),
            'underline' => $this->getOutAnnotationOptSubtypeUnderline($annot),
            'watermark' => $this->getOutAnnotationOptSubtypeWatermark($annot),
            'widget' => $this->getOutAnnotationOptSubtypeWidget($annot, $oid),
            default => '',
        };
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.text.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeText(array $annot): string
    {
        $out = '';
        if (isset($annot['opt']['open'])) {
            $out .= ' /Open ' . ($annot['opt']['open'] === true ? 'true' : 'false');
        }

        $iconsapp = ['Comment', 'Help', 'Insert', 'Key', 'NewParagraph', 'Note', 'Paragraph'];
        if (
            isset($annot['opt']['name'])
            && \in_array($annot['opt']['name'], $iconsapp)
        ) {
            $out .= ' /Name /' . $annot['opt']['name'];
        } else {
            $out .= ' /Name /Note';
        }

        if (! isset($annot['opt']['state']) && ! isset($annot['opt']['statemodel'])) {
            return $out;
        }

        $statemodels = ['Marked', 'Review'];

        if (
            isset($annot['opt']['statemodel'])
            && \in_array($annot['opt']['statemodel'], $statemodels)
        ) {
            $out .= ' /StateModel /' . $annot['opt']['statemodel'];
        } else {
            $annot['opt']['statemodel'] = 'Marked';
            $out .= ' /StateModel /' . $annot['opt']['statemodel'];
        }

        if ($annot['opt']['statemodel'] == 'Marked') {
            $states = ['Accepted', 'Unmarked'];
        } else {
            $states = ['Accepted', 'Rejected', 'Cancelled', 'Completed', 'None'];
        }

        if (
            isset($annot['opt']['state'])
            && \in_array($annot['opt']['state'], $states)
        ) {
            $out .= ' /State /' . $annot['opt']['state'];
        } elseif ($annot['opt']['statemodel'] == 'Marked') {
            $out .= ' /State /Unmarked';
        } else {
            $out .= ' /State /None';
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.link.
     *
     * @param TAnnot $annot   Array containing page annotations.
     * @param int    $pagenum Page number.
     * @param int    $oid     Annotation Object ID.
     */
    protected function getOutAnnotationOptSubtypeLink(
        array $annot,
        int $pagenum,
        int $oid
    ): string {
        $out = '';
        if (! empty($annot['txt'])) {
            switch ($annot['txt'][0]) {
                case '#': // internal destination
                    $out .= ' /A << /S /GoTo /D /'
                    . $this->encrypt->encodeNameObject(\substr($annot['txt'], 1)) . '>>';
                    break;
                case '@': // internal link ID
                        $l = $this->links[$annot['txt']];
                        $page = $this->page->getPage($l['p']);
                        $y = $this->toYPoints($l['y'], $page['pheight']);
                        $out .= \sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $page['n'], $y);
                    break;
                case '%': // embedded PDF file
                    if (! $this->pdfx) {
                        $filename = \basename(\substr($annot['txt'], 1));
                        $out .= ' /A << /S /GoToE /D [0 /Fit] /NewWindow true /T << /R /C /P ' . ($pagenum - 1)
                            . ' /A ' . $this->embeddedfiles[$filename]['a']
                            . ' >>'
                            . ' >>';
                    }
                    break;
                case '*': // embedded generic file
                    $filename = \basename(\substr($annot['txt'], 1));
                    $jsa = 'var D=event.target.doc;var MyData=D.dataObjects;for (var i in MyData) if (MyData[i].path=="'
                        . $filename . '")'
                        . ' D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
                    if (! $this->pdfx && ($this->pdfuaMode === '')) {
                        $out .= ' /A << /S /JavaScript /JS '
                            . $this->getOutTextString($jsa, $oid, true) . ' >>';
                    }
                    break;
                default:
                    $parsedUrl = \parse_url($annot['txt']);
                    if (
                        empty($parsedUrl['scheme'])
                        && (isset($parsedUrl['path']) && $parsedUrl['path'] !== ''
                        && \strtolower(\substr($parsedUrl['path'], -4)) == '.pdf')
                    ) {
                        // relative link to a PDF file
                        $dest = '[0 /Fit]'; // default page 0
                        if (! empty($parsedUrl['fragment'])) {
                            // check for named destination
                            $tmp = \explode('=', $parsedUrl['fragment']);
                            $dest = '(' . ((\count($tmp) == 2) ? $tmp[1] : $tmp[0]) . ')';
                        }

                        if (! $this->pdfx) {
                            $out .= ' /A << /S /GoToR /D ' . $dest
                                . ' /F '
                                . $this->encrypt->escapeDataString($this->unhtmlentities($parsedUrl['path']), $oid)
                                . ' /NewWindow true'
                                . ' >>';
                        }
                    } else {
                        // external URI link
                        if (! $this->pdfx) {
                            $out .= ' /A << /S /URI /URI '
                                . $this->encrypt->escapeDataString($this->unhtmlentities($annot['txt']), $oid)
                                . ' >>';
                        }
                    }
                    break;
            }
        }

        $hmodes = ['N', 'I', 'O', 'P'];
        if (! empty($annot['opt']['h']) && \in_array($annot['opt']['h'], $hmodes)) {
            $out .= ' /H /' . $annot['opt']['h'];
        } else {
            $out .= ' /H /I';
        }

        //$out .= ' /PA ';
        //$out .= ' /Quadpoints ';
        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.freetext.
     *
     * @param TAnnot $annot Array containing page annotations.
     * @param int    $oid     Annotation Object ID.
     */
    protected function getOutAnnotationOptSubtypeFreetext(array $annot, int $oid): string
    {
        $out = '';
        if (! empty($annot['opt']['da'])) {
            $out .= ' /DA ' . $this->encrypt->escapeDataString($annot['opt']['da'], $oid);
        }

        if (
            isset($annot['opt']['q'])
            && \is_numeric($annot['opt']['q'])
            && ($annot['opt']['q'] >= 0)
            && ($annot['opt']['q'] <= 2)
        ) {
            $out .= ' /Q ' . (int) $annot['opt']['q'];
        }

        if (isset($annot['opt']['rc'])) {
            $out .= ' /RC ' . $this->getOutTextString($annot['opt']['rc'], $annot['n'], true);
        }

        if (isset($annot['opt']['ds'])) {
            $out .= ' /DS ' . $this->getOutTextString($annot['opt']['ds'], $annot['n'], true);
        }

        if (isset($annot['opt']['cl']) && \is_array($annot['opt']['cl'])) {
            $out .= ' /CL [';
            foreach ($annot['opt']['cl'] as $cl) {
                if (\is_numeric($cl)) {
                    $out .= \sprintf('%F ', $this->toPoints(\floatval($cl)));
                }
            }

            $out .= ']';
        }

        $tfit = ['FreeText', 'FreeTextCallout', 'FreeTextTypeWriter'];
        if (isset($annot['opt']['it']) && \in_array($annot['opt']['it'], $tfit)) {
            $out .= ' /IT /' . $annot['opt']['it'];
        }

        if (
            isset($annot['opt']['rd'])
            && \is_array($annot['opt']['rd'])
            && (\count($annot['opt']['rd']) == 4)
            && \is_numeric($annot['opt']['rd'][0])
            && \is_numeric($annot['opt']['rd'][1])
            && \is_numeric($annot['opt']['rd'][2])
            && \is_numeric($annot['opt']['rd'][3])
        ) {
            $l = $this->toPoints((float) $annot['opt']['rd'][0]);
            $r = $this->toPoints((float) $annot['opt']['rd'][1]);
            $t = $this->toPoints((float) $annot['opt']['rd'][2]);
            $b = $this->toPoints((float) $annot['opt']['rd'][3]);
            $out .= ' /RD [' . \sprintf('%F %F %F %F', $l, $r, $t, $b) . ']';
        }

        $lineendings = [
            'Square',
            'Circle',
            'Diamond',
            'OpenArrow',
            'ClosedArrow',
            'None',
            'Butt',
            'ROpenArrow',
            'RClosedArrow',
            'Slash',
        ];
        if (isset($annot['opt']['le']) && \in_array($annot['opt']['le'], $lineendings)) {
            $out .= ' /LE /' . $annot['opt']['le'];
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.line.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeLine(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.square.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeSquare(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.circle.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeCircle(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.polygon.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypePolygon(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.polyline.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypePolyline(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.highlight.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeHighlight(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeUnderline(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.squiggly.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeSquiggly(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.strikeout.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeStrikeout(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.stamp.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeStamp(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.caret.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeCaret(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.ink.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeInk(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.popup.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypePopup(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.fileattachment.
     *
     * @param TAnnot $annot Array containing page annotations.
     * @param int    $key Annotation index in the current page.
     */
    protected function getOutAnnotationOptSubtypeFileattachment(
        array $annot,
        int $key
    ): string {
        if (($this->pdfa == 1) || ($this->pdfa == 2) || ! isset($annot['opt']['fs'])) {
            // embedded files are not allowed in PDF/A mode version 1 and 2
            return '';
        }

        $filename = \basename($annot['opt']['fs']);
        if (! isset($this->embeddedfiles[$filename]['f'])) {
            return '';
        }

        $out = ' /FS ' . $this->embeddedfiles[$filename]['f'] . ' 0 R';
        $iconsapp = ['Graph', 'Paperclip', 'PushPin', 'Tag'];
        if (isset($annot['opt']['name']) && \in_array($annot['opt']['name'], $iconsapp)) {
            $out .= ' /Name /' . $annot['opt']['name'];
        } else {
            $out .= ' /Name /PushPin';
        }

        // index (zero-based) of the annotation in the Annots array of this page
        $this->embeddedfiles[$filename]['a'] = $key;
        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.sound.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeSound(array $annot): string
    {
        $out = '';
        if (empty($annot['opt']['fs'])) {
            return '';
        }

        $filename = \basename($annot['opt']['fs']);
        if (! isset($this->embeddedfiles[$filename]['f'])) {
            return '';
        }

        // ... TO BE COMPLETED ...
        // /R /C /B /E /CO /CP
        $out = ' /Sound ' . $this->embeddedfiles[$filename]['f'] . ' 0 R';
        $iconsapp = ['Speaker', 'Mic'];
        if (isset($annot['opt']['name']) && \in_array($annot['opt']['name'], $iconsapp)) {
            $out .= ' /Name /' . $annot['opt']['name'];
        } else {
            $out .= ' /Name /Speaker';
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.movie.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeMovie(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.widget.
     *
     * @param TAnnot $annot Array containing page annotations.
     * @param int    $oid   Annotation Object ID.
     */
    protected function getOutAnnotationOptSubtypeWidget(
        array $annot,
        int $oid
    ): string {
        $out = '';
        $hmode = ['N', 'I', 'O', 'P', 'T'];
        if (! empty($annot['opt']['h']) && \in_array($annot['opt']['h'], $hmode)) {
            $out .= ' /H /' . $annot['opt']['h'];
        }

        if (! empty($annot['opt']['mk']) && \is_array($annot['opt']['mk'])) {
            $out .= ' /MK <<';
            if (
                isset($annot['opt']['mk']['r'])
                && \is_numeric($annot['opt']['mk']['r'])
            ) {
                $out .= ' /R ' . $annot['opt']['mk']['r'];
            }

            if (isset($annot['opt']['mk']['bc'])) {
                if (\is_array($annot['opt']['mk']['bc'])) {
                    $out .= ' /BC '
                    . static::getColorStringFromPercArray($annot['opt']['mk']['bc']); // @phpstan-ignore argument.type
                }
            }

            if (isset($annot['opt']['mk']['bg'])) {
                if (\is_array($annot['opt']['mk']['bg'])) {
                    $out .= ' /BG '
                    . static::getColorStringFromPercArray($annot['opt']['mk']['bg']); // @phpstan-ignore argument.type
                }
            }

            if (
                isset($annot['opt']['mk']['ca'])
                && \is_string($annot['opt']['mk']['ca'])
            ) {
                $out .= ' /CA ' . $annot['opt']['mk']['ca'];
            }

            if (
                isset($annot['opt']['mk']['rc'])
                && \is_string($annot['opt']['mk']['rc'])
            ) {
                $out .= ' /RC ' . $annot['opt']['mk']['rc'];
            }

            if (
                isset($annot['opt']['mk']['ac'])
                && \is_string($annot['opt']['mk']['ac'])
            ) {
                $out .= ' /AC ' . $annot['opt']['mk']['ac'];
            }

            if (isset($annot['opt']['mk']['i']) && \is_string($annot['opt']['mk']['i'])) {
                $info = $this->image->getImageDataByKey($this->image->getKey($annot['opt']['mk']['i']));
                if (! empty($info['obj'])) {
                    $out .= ' /I ' . $info['obj'] . ' 0 R';
                }
            }

            if (isset($annot['opt']['mk']['ri']) && \is_string($annot['opt']['mk']['ri'])) {
                $info = $this->image->getImageDataByKey($this->image->getKey($annot['opt']['mk']['ri']));
                if (! empty($info['obj'])) {
                    $out .= ' /RI ' . $info['obj'] . ' 0 R';
                }
            }

            if (isset($annot['opt']['mk']['ix']) && \is_string($annot['opt']['mk']['ix'])) {
                $info = $this->image->getImageDataByKey($this->image->getKey($annot['opt']['mk']['ix']));
                if (! empty($info['obj'])) {
                    $out .= ' /IX ' . $info['obj'] . ' 0 R';
                }
            }

            if (! empty($annot['opt']['mk']['if']) && \is_array($annot['opt']['mk']['if'])) {
                $out .= ' /IF <<';
                $if_sw = ['A', 'B', 'S', 'N'];
                if (
                    isset($annot['opt']['mk']['if']['sw'])
                    && \is_string($annot['opt']['mk']['if']['sw'])
                    && \in_array($annot['opt']['mk']['if']['sw'], $if_sw)
                ) {
                    $out .= ' /SW /' . $annot['opt']['mk']['if']['sw'];
                }

                $if_s = ['A', 'P'];
                if (
                    isset($annot['opt']['mk']['if']['s'])
                    && \is_string($annot['opt']['mk']['if']['s'])
                    && \in_array($annot['opt']['mk']['if']['s'], $if_s)
                ) {
                    $out .= ' /S /' . $annot['opt']['mk']['if']['s'];
                }

                if (
                    isset($annot['opt']['mk']['if']['a'])
                    && (\is_array($annot['opt']['mk']['if']['a']))
                    && (\count($annot['opt']['mk']['if']['a']) == 2)
                    && \is_numeric($annot['opt']['mk']['if']['a'][0])
                    && \is_numeric($annot['opt']['mk']['if']['a'][1])
                ) {
                    $out .= \sprintf(
                        ' /A [%F %F]',
                        $annot['opt']['mk']['if']['a'][0],
                        $annot['opt']['mk']['if']['a'][1]
                    );
                }

                if (isset($annot['opt']['mk']['if']['fb']) && ($annot['opt']['mk']['if']['fb'])) {
                    $out .= ' /FB true';
                }

                $out .= '>>';
            }

            if (
                isset($annot['opt']['mk']['tp'])
                && \is_numeric($annot['opt']['mk']['tp'])
                && ($annot['opt']['mk']['tp'] >= 0)
                && ($annot['opt']['mk']['tp'] <= 6)
            ) {
                $out .= ' /TP ' . (int) $annot['opt']['mk']['tp'];
            }

            $out .= '>>';
        }

        // --- Entries for field dictionaries ---
        if (isset($this->radiobuttons[$annot['txt']]['n'])) {
            $out .= ' /Parent ' . $this->radiobuttons[$annot['txt']]['n'] . ' 0 R';
        }

        if (isset($annot['opt']['t']) && \is_string($annot['opt']['t'])) {
            $out .= ' /T ' . $this->encrypt->escapeDataString($annot['opt']['t'], $oid);
        }

        if (isset($annot['opt']['tu']) && \is_string($annot['opt']['tu'])) {
            $out .= ' /TU ' . $this->encrypt->escapeDataString($annot['opt']['tu'], $oid);
        }

        if (isset($annot['opt']['tm']) && \is_string($annot['opt']['tm'])) {
            $out .= ' /TM ' . $this->encrypt->escapeDataString($annot['opt']['tm'], $oid);
        }

        if (isset($annot['opt']['ff'])) {
            if (\is_array($annot['opt']['ff'])) {
                // array of bit settings
                $flag = 0;
                foreach ($annot['opt']['ff'] as $val) {
                    if (\is_numeric($val) && ($val >= 1) && ($val <= 32)) {
                        $flag += 1 << (\intval($val) - 1);
                    }
                }
            } else {
                $flag = (int) $annot['opt']['ff'];
            }

            $out .= ' /Ff ' . $flag;
        }

        if (isset($annot['opt']['maxlen'])) {
            $out .= ' /MaxLen ' . (int) $annot['opt']['maxlen'];
        }

        if (isset($annot['opt']['v'])) {
            $out .= ' /V';
            if (\is_array($annot['opt']['v'])) {
                foreach ($annot['opt']['v'] as $optval) {
                    if (\is_numeric($optval)) {
                        $optval = \sprintf('%F', \floatval($optval));
                    } elseif (!\is_string($optval)) {
                        continue;
                    }

                    $out .= ' ' . $optval;
                }
            } else {
                $out .= ' ' . $this->getOutTextString($annot['opt']['v'], $oid, true);
            }
        }

        if (isset($annot['opt']['dv'])) {
            $out .= ' /DV';
            if (\is_array($annot['opt']['dv'])) {
                foreach ($annot['opt']['dv'] as $optval) {
                    if (\is_numeric($optval)) {
                        $optval = \sprintf('%F', \floatval($optval));
                    } elseif (!\is_string($optval)) {
                        continue;
                    }

                    $out .= ' ' . $optval;
                }
            } else {
                $out .= ' ' . $this->getOutTextString($annot['opt']['dv'], $oid, true);
            }
        }

        if (isset($annot['opt']['rv'])) {
            $out .= ' /RV';
            if (\is_array($annot['opt']['rv'])) {
                foreach ($annot['opt']['rv'] as $optval) {
                    if (\is_numeric($optval)) {
                        $optval = \sprintf('%F', \floatval($optval));
                    } elseif (!\is_string($optval)) {
                        continue;
                    }

                    $out .= ' ' . $optval;
                }
            } else {
                $out .= ' ' . $this->getOutTextString($annot['opt']['rv'], $oid, true);
            }
        }

        $action = $annot['opt']['a'] ?? '';
        if (
            \is_string($action)
            && ($action !== '')
            && (! $this->pdfx)
            && (($this->pdfuaMode === '') || ! \str_contains($action, '/JavaScript'))
        ) {
            $out .= ' /A << ' . $action . ' >>';
        }

        $additionalAction = $annot['opt']['aa'] ?? '';
        if (
            \is_string($additionalAction)
            && ($additionalAction !== '')
            && (! $this->pdfx)
            && (($this->pdfuaMode === '') || ! \str_contains($additionalAction, '/JavaScript'))
        ) {
            $out .= ' /AA << ' . $additionalAction . ' >>';
        }

        if (! empty($annot['opt']['da'])) {
            $out .= ' /DA ' . $this->encrypt->escapeDataString($annot['opt']['da'], $oid);
        }

        if (
            isset($annot['opt']['q'])
            && \is_numeric($annot['opt']['q'])
            && ($annot['opt']['q'] >= 0)
            && ($annot['opt']['q'] <= 2)
        ) {
            $out .= ' /Q ' . (int) $annot['opt']['q'];
        }

        if (! empty($annot['opt']['opt']) && \is_array($annot['opt']['opt'])) {
            $out .= ' /Opt [';
            foreach ($annot['opt']['opt'] as $copt) {
                if (\is_array($copt)) {
                    if ((\count($copt) != 2) || ! \is_string($copt[0]) || ! \is_string($copt[1])) {
                        continue;
                    }
                    $out .= '[' . $this->getOutTextString($copt[0], $oid, true)
                        . $this->getOutTextString($copt[1], $oid, true) . ']';
                } elseif (\is_string($copt) || \is_numeric($copt)) {
                    $out .= $this->getOutTextString(\strval($copt), $oid, true);
                }
            }

            $out .= ']';
        }

        if (isset($annot['opt']['ti'])) {
            $out .= ' /TI ' . (int) $annot['opt']['ti'];
        }

        if (! empty($annot['opt']['i']) && \is_array($annot['opt']['i'])) {
            $out .= ' /I [';
            foreach ($annot['opt']['i'] as $copt) {
                if (\is_numeric($copt)) {
                    $out .= \strval(\intval($copt)) . ' ';
                }
            }

            $out .= ']';
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.screen.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeScreen(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.printermark.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypePrintermark(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.redact.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeRedact(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.trapnet.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeTrapnet(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.watermark.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeWatermark(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.3d.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtype3D(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the PDF Javascript entry.
     */
    protected function getOutJavascript(): string
    {
        if (
            ($this->pdfa > 0)
            || $this->pdfx
            || ($this->pdfuaMode !== '')
            || (empty($this->javascript) && empty($this->jsobjects))
        ) {
            return '';
        }

        if (\strpos($this->javascript, 'this.addField') > 0) {
            if (! $this->userrights['enabled']) {
                // $this->setUserRights();
            }

            // The following two lines are used to avoid form fields duplication after saving.
            // The addField method only works when releasing user rights (UR3).
            $pattern = "ftcpdfdocsaved=this.addField('%s','%s',%d,[%F,%F,%F,%F]);";
            $jsa = \sprintf($pattern, 'tcpdfdocsaved', 'text', 0, 0, 1, 0, 1);
            $jsb = "getField('tcpdfdocsaved').value='saved';";
            $this->javascript = $jsa . "\n" . $this->javascript . "\n" . $jsb;
        }

        $out = '';
        // name tree for javascript
        $njs = '<< /Names [';
        if (! empty($this->javascript)) {
            // default Javascript object
            $oid = ++$this->pon;
            $out .= $oid . ' 0 obj' . "\n"
            . '<<'
            . ' /S /JavaScript /JS '
            . $this->getOutTextString($this->javascript, $oid, true)
            . ' >>' . "\n"
            . 'endobj' . "\n";
            $njs .= ' (EmbeddedJS) ' . $oid . ' 0 R';
        }

        foreach ($this->jsobjects as $key => $val) {
            // additional Javascript objects
            $oid = $val['n'];
            $out .= $oid . ' 0 obj' . "\n"
            . '<< '
            . '/S /JavaScript /JS '
            . $this->getOutTextString($val['js'], $oid, true)
            . ' >>' . "\n"
            . 'endobj' . "\n";
            if ($val['onload']) {
                $njs .= ' (JS' . $key . ') ' . $oid . ' 0 R';
            }
        }

        $njs .= ' ] >>';
        $this->jstree = $njs;
        return $out;
    }

    /**
     * Sort bookmarks by page and original position.
     */
    protected function sortBookmarks(): void
    {
        $outline_p = [];
        $outline_k = [];
        foreach ($this->outlines as $key => $row) {
            $outline_p[$key] = $row['p'];
            $outline_k[$key] = $key;
        }

        // sort outlines by page and original position
        \array_multisort($outline_p, SORT_NUMERIC, SORT_ASC, $outline_k, SORT_NUMERIC, SORT_ASC, $this->outlines);
    }

    /**
     * Process the bookmarks to get the previous and next one.
     *
     * @return int first bookmark object ID
     */
    protected function processPrevNextBookmarks(): int
    {
        $numbookmarks = \count($this->outlines);
        $this->sortBookmarks();
        $lru = [];
        $level = 0;
        foreach ($this->outlines as $i => $o) {
            if ($o['l'] > 0) {
                $parent = $lru[($o['l'] - 1)];
                // set parent and last pointers
                $this->outlines[$i]['parent'] = $parent;
                $this->outlines[$parent]['last'] = $i; // @phpstan-ignore assign.propertyType
                if ($o['l'] > $level) {
                    // level increasing: set first pointer
                    $this->outlines[$parent]['first'] = $i; // @phpstan-ignore assign.propertyType
                }
            } else {
                $this->outlines[$i]['parent'] = $numbookmarks;
            }

            if (($o['l'] <= $level) && ($i > 0)) {
                // set prev and next pointers
                $prev = $lru[$o['l']];
                $this->outlines[$prev]['next'] = $i; // @phpstan-ignore assign.propertyType
                $this->outlines[$i]['prev'] = $prev; // @phpstan-ignore assign.propertyType
            }

            $lru[$o['l']] = $i;
            $level = $o['l'];
        }
        return $lru[0];
    }

    /**
     * Returns the PDF Bookmarks entry.
     */
    protected function getOutBookmarks(): string
    {
        if ($this->outlines === []) {
            return '';
        }

        $numbookmarks = \is_countable($this->outlines) ? \count($this->outlines) : 0;
        if ($numbookmarks <= 0) {
            return '';
        }

        $root_oid = $this->processPrevNextBookmarks();
        $first_oid = $this->pon + 1;
        $nltags = '/<br[\s]?\/>|<\/(blockquote|dd|dl|div|dt|h1|h2|h3|h4|h5|h6|hr|li|ol|p|pre|ul|tcpdf|table|tr|td)>/si';
        $out = '';
        foreach ($this->outlines as $outline) {
            // covert HTML title to string
            $search = [$nltags, "/[\r]+/si", "/[\n]+/si"];
            $replace = ["\n", '', "\n"];
            $title = \preg_replace($search, $replace, $outline['t']);
            if ($title === null) {
                $title = '';
            }
            $title = \strip_tags($title);

            $title = \preg_replace("/^\s+|\s+$/u", '', $title);
            if ($title === null) {
                $title = '';
            }

            $oid = ++$this->pon;
            $out .= $oid . ' 0 obj' . "\n"
                . '<<'
                . ' /Title ' . $this->getOutTextString($title, $oid, true)
                . ' /Parent ' . ($first_oid + $outline['parent']) . ' 0 R';
            if ($outline['prev'] >= 0) {
                $out .= ' /Prev ' . ($first_oid + $outline['prev']) . ' 0 R';
            }
            if ($outline['next'] >= 0) {
                $out .= ' /Next ' . ($first_oid + $outline['next']) . ' 0 R';
            }
            if ($outline['first'] >= 0) {
                $out .= ' /First ' . ($first_oid + $outline['first']) . ' 0 R';
            }
            if ($outline['last'] >= 0) {
                $out .= ' /Last ' . ($first_oid + $outline['last']) . ' 0 R';
            }

            if (! empty($outline['u'])) {
                // link
                switch ($outline['u'][0]) {
                    case '#':
                        // internal destination
                        $out .= ' /Dest /' . $this->encrypt->encodeNameObject(\substr($outline['u'], 1));
                        break;
                    case '@':
                            // internal link ID
                            $l = $this->links[$outline['u']];
                            $page = $this->page->getPage($l['p']);
                            $y = $this->toYPoints($l['y'], $page['pheight']);
                            $out .= \sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $page['n'], $y);
                        break;
                    case '%':
                        // embedded PDF file
                        if (! $this->pdfx) {
                            $filename = \basename(\substr($outline['u'], 1));
                            $out .= ' /A << /S /GoToE /D [0 /Fit] /NewWindow true /T << /R /C /P '
                                . ($outline['p'] - 1)
                                . ' /A ' . $this->embeddedfiles[$filename]['a'] . ' >>'
                                . ' >>';
                        }
                        break;
                    case '*':
                        // embedded generic file
                        $filename = \basename(\substr($outline['u'], 1));
                        $jsa = 'var D=event.target.doc;var MyData=D.dataObjects;'
                        . 'for (var i in MyData) if (MyData[i].path=="'
                        . $filename . '")'
                        . ' D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
                        if (! $this->pdfx && ($this->pdfuaMode === '')) {
                            $out .= ' /A <</S /JavaScript /JS '
                            . $this->getOutTextString($jsa, $oid, true) . '>>';
                        }
                        break;
                    default:
                        // external URI link
                        if (! $this->pdfx) {
                            $out .= ' /A << /S /URI /URI '
                                . $this->encrypt->escapeDataString($this->unhtmlentities($outline['u']), $oid)
                                . ' >>';
                        }
                        break;
                }
            } else {
                // link to a page
                $page = $this->page->getPage($outline['p']);
                $x = $this->toPoints($outline['x']);
                $y = $this->toYPoints($outline['y'], $page['pheight']);
                $out .= ' ' . \sprintf('/Dest [%u 0 R /XYZ %F %F null]', $page['n'], $x, $y);
            }

            // set font style
            $style = 0;
            if (! empty($outline['s'])) {
                if (\str_contains($outline['s'], 'B')) {
                    $style |= 2; // bold
                }

                if (\str_contains($outline['s'], 'I')) {
                    $style |= 1; // oblique
                }
            }

            $out .= \sprintf(' /F %d', $style);
            // set bookmark color
            if (empty($outline['c'])) {
                $out .= ' /C [0.0 0.0 0.0]'; // black
            } else {
                 $out .= ' /C [ ' . $this->color->getPdfRgbComponents($outline['c']) . ' ]';
            }

            $out .= ' /Count 0 >>' . "\n"
                . 'endobj' . "\n";
        }

        //Outline root
        $this->outlinerootoid = ++$this->pon;
        return $out . $this->outlinerootoid . ' 0 obj' . "\n"
            . '<<'
            . ' /Type /Outlines'
            . ' /First ' . $first_oid . ' 0 R'
            . ' /Last ' . ($first_oid + $root_oid) . ' 0 R'
            . ' >>' . "\n"
            . 'endobj' . "\n";
    }

    /**
     * Returns the PDF Signature Fields entry.
     */
    protected function getOutSignatureFields(): string
    {
        if ($this->signature === []) {
            return '';
        }

        $out = '';
        foreach ($this->signature['appearance']['empty'] as $key => $esa) {
            $page = $this->page->getPage($esa['page']);
            $signame = $esa['name'] . \sprintf(' [%03d]', ($key + 1));
            $out .= $esa['objid'] . ' 0 obj' . "\n"
                . '<<'
                . ' /Type /Annot'
                . ' /Subtype /Widget'
                . ' /Rect [' . $esa['rect'] . ']'
                . ' /P ' . $page['n'] . ' 0 R' // link to signature appearance page
                . ' /F 4'
                . ' /FT /Sig'
                . ' /T ' . $this->getOutTextString($signame, $esa['objid'], true)
                . ' /Ff 0'
                . ' >>'
                . "\n" . 'endobj' . "\n";
        }

        return $out;
    }

    /**
     * Sign the document.
     *
     * @param string $pdfdoc string containing the PDF document
     *
     * @return string Signed PDF document.
     */
    protected function signDocument(string $pdfdoc): string
    {
        if (! $this->sign) {
            return $pdfdoc;
        }

        $prepared = $this->prepareDocumentForSignature($pdfdoc);
        $pdfdoc = $prepared['pdfdoc'];
        $pdfdocLength = $prepared['pdfdoc_length'];
        $byteRange = $prepared['byte_range'];
        $tempdoc = $this->writePreparedDocumentForSignature($pdfdoc);
        $tempsign = $this->createPkcs7SignatureFile($tempdoc);
        $signature = $this->extractSignatureFromPkcs7File($tempsign, $pdfdocLength);
        $signature = $this->applySignatureTimestamp($signature);
        $signature = $this->convertBinarySignatureToHex($signature);

        return \substr($pdfdoc, 0, $byteRange[1]) . '<' . $signature . '>' . \substr($pdfdoc, $byteRange[1]);
    }

    /**
     * Prepare the document bytes and ByteRange placeholder for signing.
     *
     * @param string $pdfdoc PDF document with signature placeholder.
     *
     * @return TSignDocPrepared
     */
    protected function prepareDocumentForSignature(string $pdfdoc): array
    {
        // remove last newline
        $pdfdoc = \substr($pdfdoc, 0, -1);
        // remove filler space
        $byterangeLength = \strlen($this::BYTERANGE);
        $byteRange = [0, 0, 0, 0];
        $byteRange[1] = \strpos($pdfdoc, $this::BYTERANGE) + $byterangeLength + 10;
        $byteRange[2] = $byteRange[1] + $this::SIGMAXLEN + 2;
        $byteRange[3] = \strlen($pdfdoc) - $byteRange[2];
        $pdfdoc = \substr($pdfdoc, 0, $byteRange[1]) . \substr($pdfdoc, $byteRange[2]);

        $byterange = \sprintf('/ByteRange[0 %u %u %u]', $byteRange[1], $byteRange[2], $byteRange[3]);
        $byterange .= \str_repeat(' ', ($byterangeLength - \strlen($byterange)));
        $pdfdoc = \str_replace($this::BYTERANGE, $byterange, $pdfdoc);

        return [
            'byte_range' => $byteRange,
            'pdfdoc' => $pdfdoc,
            'pdfdoc_length' => \strlen($pdfdoc),
        ];
    }

    /**
     * Write the prepared document bytes to a temporary file for OpenSSL signing.
     *
     * @param string $pdfdoc Prepared PDF document bytes.
     */
    protected function writePreparedDocumentForSignature(string $pdfdoc): string
    {
        $tempdoc = $this->cache->getNewFileName('doc', $this->fileid);
        if ($tempdoc === false) {
            throw new PdfException('Unable to create temporary document file for signature');
        }

        $handle = $this->file->fopenLocal($tempdoc, 'wb');
        \fwrite($handle, $pdfdoc);
        \fclose($handle);

        return $tempdoc;
    }

    /**
     * Create the detached PKCS#7 signature file for the prepared document.
     *
     * @param string $tempdoc Temporary PDF document path.
     */
    protected function createPkcs7SignatureFile(string $tempdoc): string
    {
        $tempsign = $this->cache->getNewFileName('sig', $this->fileid);
        if ($tempsign === false) {
            throw new PdfException('Unable to create temporary signature file');
        }

        $signed = \openssl_pkcs7_sign(
            $tempdoc,
            $tempsign,
            $this->signature['signcert'],
            [$this->signature['privkey'], $this->signature['password']],
            [],
            PKCS7_BINARY | PKCS7_DETACHED,
            $this->signature['extracerts']
        );
        if ($signed !== true) {
            throw new PdfException('Unable to generate PKCS#7 signature');
        }

        return $tempsign;
    }

    /**
     * Extract the binary signature from the PKCS#7 output file.
     *
     * @param string $tempsign Signed output file path.
     * @param int $pdfdocLength Length of the prepared PDF content.
     */
    protected function extractSignatureFromPkcs7File(string $tempsign, int $pdfdocLength): string
    {
        $signature = $this->file->getFileData($tempsign);
        if ($signature === false) {
            throw new PdfException('Unable to read signature file');
        }

        $signature = \substr($signature, $pdfdocLength);
        $signature = \substr($signature, (\strpos($signature, "%%EOF\n\n------") + 13));

        $tmparr = \explode("\n\n", $signature);
        $signature = $tmparr[1] ?? '';
        $signature = \base64_decode(\trim($signature));
        if ($signature === false) {
            throw new PdfException('Unable to decode signature');
        }

        return $signature;
    }

    /**
     * Convert the binary signature to padded hexadecimal PDF contents.
     *
     * @param string $signature Digital signature as binary string.
     */
    protected function convertBinarySignatureToHex(string $signature): string
    {
        $signature = \unpack('H*', $signature);
        if ($signature === false) {
            throw new PdfException('Unable to unpack signature');
        }

        $signature = \current($signature);
        if (! \is_string($signature)) {
            throw new PdfException('Invalid signature');
        }

        return \str_pad($signature, $this::SIGMAXLEN, '0');
    }

    /**
     * Add TSA timestamp to the signature.
     *
     * @param string $signature Digital signature as binary string.
     */
    protected function applySignatureTimestamp(string $signature): string
    {
        if (! $this->sigtimestamp['enabled']) {
            return $signature;
        }

        // Phase 2 groundwork: validate RFC3161 request/response lifecycle.
        // CMS unsigned-attributes embedding is added in the next phase slice.
        $token = $this->requestTimestampToken($signature);
        if ($token === '') {
            throw new PdfException('Unable to extract TSA token');
        }

        return $signature;
    }

    /**
     * @param string $signature Digital signature as binary string.
     */
    protected function requestTimestampToken(string $signature): string
    {
        $request = $this->buildTimestampRequest($signature);
        $response = $this->postTimestampRequest($request);
        return $this->extractTimestampTokenFromResponse($response);
    }

    /**
     * Collect deterministic validation material for LTV embedding.
     *
     * @return TValidationMaterial
     */
    protected function collectValidationMaterial(): array
    {
        $empty = [
            'cert_chain' => [],
            'certs' => [],
            'ocsp' => [],
            'crls' => [],
            'vri' => [],
        ];

        $ltv = $this->signature['ltv'] ?? ['enabled' => false];
        if (empty($ltv['enabled'])) {
            return $empty;
        }

        $pemInputs = $this->collectValidationCertificateInputs();
        if ($pemInputs === []) {
            return $empty;
        }

        $certChain = [];
        $certs = [];
        $certIndexes = [];
        $seen = [];
        foreach ($pemInputs as $pem) {
            $cert = $this->normalizeValidationCertificate($pem);
            $fingerprint = \hash('sha256', $cert['der']);
            if (isset($seen[$fingerprint])) {
                continue;
            }

            $seen[$fingerprint] = true;
            $certChain[] = $cert;
            if (! empty($ltv['embed_certs'])) {
                $certIndexes[] = \count($certs);
                $certs[] = $cert['der'];
            }
        }

        if ($certChain === []) {
            return $empty;
        }

        /** @var array<int, string> $ocsp */
        $ocsp = [];
        /** @var array<string, int> $ocspDedup */
        $ocspDedup = [];
        /** @var array<int, string> $crls */
        $crls = [];
        /** @var array<string, int> $crlDedup */
        $crlDedup = [];
        $vri = [];

        if (! empty($ltv['include_vri'])) {
            $signerCert = $certChain[0];
            $issuerCert = $certChain[1] ?? $signerCert;
            $revocation = $this->collectRevocationForCert(
                $signerCert,
                $issuerCert,
                ! empty($ltv['embed_ocsp']),
                ! empty($ltv['embed_crl']),
                $ocsp,
                $ocspDedup,
                $crls,
                $crlDedup
            );
            $vriKey = \strtoupper(\hash('sha1', $signerCert['der']));
            $vri[$vriKey] = [
                'certs' => $certIndexes,
                'ocsp' => $revocation['ocsp'],
                'crls' => $revocation['crls'],
            ];
        }

        return [
            'cert_chain' => $certChain,
            'certs' => $certs,
            'ocsp' => $ocsp,
            'crls' => $crls,
            'vri' => $vri,
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function collectValidationCertificateInputs(): array
    {
        $inputs = [];

        $main = $this->getCertificateSourceContent((string) $this->signature['signcert']);
        if ($main !== '') {
            $inputs = \array_merge($inputs, $this->extractPemCertificates($main));
        }

        $extra = (string) ($this->signature['extracerts'] ?? '');
        if ($extra !== '') {
            $extraContent = $this->getCertificateSourceContent($extra);
            if ($extraContent !== '') {
                $inputs = \array_merge($inputs, $this->extractPemCertificates($extraContent));
            }
        }

        return $inputs;
    }

    /**
     * Emit DSS and VRI objects for the current signature.
     */
    protected function getOutDssObjects(): string
    {
        $this->objid['dss'] = 0;
        $ltv = $this->signature['ltv'] ?? ['enabled' => false];
        if (empty($ltv['enabled']) || empty($ltv['include_dss'])) {
            return '';
        }

        $material = $this->collectValidationMaterial();
        if (
            $material['certs'] === []
            && $material['ocsp'] === []
            && $material['crls'] === []
            && $material['vri'] === []
        ) {
            return '';
        }

        $out = '';
        $certObjIds = $this->emitDssBinaryObjects($out, $material['certs']);
        $ocspObjIds = $this->emitDssBinaryObjects($out, $material['ocsp']);
        $crlObjIds = $this->emitDssBinaryObjects($out, $material['crls']);
        $vriObjIds = $this->emitDssVriObjects($out, $material['vri'], $certObjIds, $ocspObjIds, $crlObjIds);

        $oid = ++$this->pon;
        $this->objid['dss'] = $oid;
        $out .= $oid . " 0 obj\n";
        $out .= '<< /Type /DSS';

        if ($vriObjIds !== []) {
            $out .= ' /VRI <<';
            foreach ($vriObjIds as $vriKey => $vriOid) {
                $out .= ' /' . $vriKey . ' ' . $vriOid . ' 0 R';
            }
            $out .= ' >>';
        }

        if ($ocspObjIds !== []) {
            $out .= ' /OCSPs [';
            foreach ($ocspObjIds as $objId) {
                $out .= ' ' . $objId . ' 0 R';
            }
            $out .= ' ]';
        }

        if ($crlObjIds !== []) {
            $out .= ' /CRLs [';
            foreach ($crlObjIds as $objId) {
                $out .= ' ' . $objId . ' 0 R';
            }
            $out .= ' ]';
        }

        if ($certObjIds !== []) {
            $out .= ' /Certs [';
            foreach ($certObjIds as $objId) {
                $out .= ' ' . $objId . ' 0 R';
            }
            $out .= ' ]';
        }

        $out .= ' >>' . "\n";
        $out .= 'endobj' . "\n";

        return $out;
    }

    /**
     * @param string                $out   Output buffer.
     * @param array<int, string>    $items Binary payloads.
     * @return array<int, int>
     */
    private function emitDssBinaryObjects(string &$out, array $items): array
    {
        $objIds = [];
        foreach ($items as $index => $item) {
            $oid = ++$this->pon;
            $objIds[$index] = $oid;
            $stream = $this->encrypt->encryptString($item, $oid);
            $out .= $oid . " 0 obj\n";
            $out .= '<< /Length ' . \strlen($stream) . ' >>' . "\n";
            $out .= 'stream' . "\n";
            $out .= $stream . "\n";
            $out .= 'endstream' . "\n";
            $out .= 'endobj' . "\n";
        }

        return $objIds;
    }

    /**
     * @param string                                $out       Output buffer.
     * @param array<string, TValidationVri>         $vriItems  VRI entries.
     * @param array<int, int>                       $certObjId Certificate object IDs by index.
     * @param array<int, int>                       $ocspObjId OCSP object IDs by index.
     * @param array<int, int>                       $crlObjId  CRL object IDs by index.
     * @return array<string, int>
     */
    private function emitDssVriObjects(
        string &$out,
        array $vriItems,
        array $certObjId,
        array $ocspObjId,
        array $crlObjId
    ): array {
        $objIds = [];
        foreach ($vriItems as $vriKey => $item) {
            $oid = ++$this->pon;
            $objIds[$vriKey] = $oid;
            $out .= $oid . " 0 obj\n";
            $out .= '<< /Type /VRI';

            if ($item['certs'] !== []) {
                $out .= ' /Cert [';
                foreach ($item['certs'] as $index) {
                    if (isset($certObjId[$index])) {
                        $out .= ' ' . $certObjId[$index] . ' 0 R';
                    }
                }
                $out .= ' ]';
            }

            if ($item['ocsp'] !== []) {
                $out .= ' /OCSP [';
                foreach ($item['ocsp'] as $index) {
                    if (isset($ocspObjId[$index])) {
                        $out .= ' ' . $ocspObjId[$index] . ' 0 R';
                    }
                }
                $out .= ' ]';
            }

            if ($item['crls'] !== []) {
                $out .= ' /CRL [';
                foreach ($item['crls'] as $index) {
                    if (isset($crlObjId[$index])) {
                        $out .= ' ' . $crlObjId[$index] . ' 0 R';
                    }
                }
                $out .= ' ]';
            }

            $out .= ' >>' . "\n";
            $out .= 'endobj' . "\n";
        }

        return $objIds;
    }

    protected function getCertificateSourceContent(string $source): string
    {
        if ($source === '') {
            return '';
        }

        if (\str_contains($source, '-----BEGIN CERTIFICATE-----')) {
            return $source;
        }

        $data = $this->file->getFileData($source);
        if ($data === false && ! \str_starts_with($source, 'file://')) {
            $data = $this->file->getFileData('file://' . $source);
        }

        if ($data === false) {
            throw new PdfException('Unable to read validation certificate source');
        }

        return $data;
    }

    /**
     * @return array<int, string>
     */
    protected function extractPemCertificates(string $content): array
    {
        $matches = [];
        $pattern = '/-----BEGIN CERTIFICATE-----(?:.|\n|\r)+?-----END CERTIFICATE-----/';
        $ok = \preg_match_all($pattern, $content, $matches);
        if ($ok === false) {
            throw new PdfException('Unable to parse certificate bundle');
        }

        if ($ok === 0) {
            return [];
        }

        /** @var array<int, string> $pem */
        $pem = $matches[0];
        return $pem;
    }

    /**
     * @param string $pem PEM certificate.
     *
     * @return TValidationCert
     */
    protected function normalizeValidationCertificate(string $pem): array
    {
        $x509 = \openssl_x509_read($pem);
        if ($x509 === false) {
            throw new PdfException('Invalid validation certificate');
        }

        $exported = '';
        $exportOk = \openssl_x509_export($x509, $exported, true);
        if ($exportOk !== true || ! \is_string($exported)) {
            throw new PdfException('Unable to export validation certificate');
        }

        $pem = $exported;
        $parsed = \openssl_x509_parse($x509, false);
        if ($parsed === false) {
            $parsed = [];
        }

        $extensions = [];
        if (isset($parsed['extensions']) && \is_array($parsed['extensions'])) {
            $extensions = $parsed['extensions'];
        }

        $base64 = \str_replace(
            ["-----BEGIN CERTIFICATE-----", "-----END CERTIFICATE-----", "\r", "\n"],
            '',
            $pem
        );
        $der = \base64_decode($base64, true);
        if ($der === false) {
            throw new PdfException('Unable to decode validation certificate');
        }

        $subject = isset($parsed['name']) && \is_string($parsed['name']) ? $parsed['name'] : '';
        $issuer = '';
        if (isset($parsed['issuer']) && \is_array($parsed['issuer'])) {
            $issuer = \json_encode($parsed['issuer']);
            if ($issuer === false) {
                $issuer = '';
            }
        }


        $serial = '';
        if (isset($parsed['serialNumberHex']) && \is_string($parsed['serialNumberHex'])) {
            $serial = $parsed['serialNumberHex'];
        }

        $aiaText = isset($extensions['authorityInfoAccess'])
            && \is_string($extensions['authorityInfoAccess'])
            ? (string) $extensions['authorityInfoAccess']
            : '';
        $cdpText = isset($extensions['crlDistributionPoints'])
            && \is_string($extensions['crlDistributionPoints'])
            ? (string) $extensions['crlDistributionPoints']
            : '';

        return [
            'pem' => $pem,
            'der' => $der,
            'serial' => $serial,
            'subject' => $subject,
            'issuer' => $issuer,
            'ocsp_urls' => $this->extractCertExtensionUrls($aiaText, 'OCSP'),
            'crl_dp_urls' => $this->extractCertExtensionUrls($cdpText, 'CRL'),
        ];
    }

    /**
     * Extract URLs of a given type from a certificate extension text block.
     *
     * Pass 'OCSP' to extract OCSP responder URIs from Authority Information Access,
     * or 'CRL' to extract CRL Distribution Point URIs.
     *
     * @return array<int, string>
     */
    protected function extractCertExtensionUrls(string $text, string $type): array
    {
        if ($text === '') {
            return [];
        }

        $pattern = ($type === 'OCSP')
            ? '/OCSP\s*-\s*URI:(https?:\/\/[^\s,]+)/i'
            : '/URI:(https?:\/\/[^\s]+)/i';

        $matches = [];
        if (\preg_match_all($pattern, $text, $matches) === false) {
            return [];
        }

        $urls = $matches[1];
        return $urls;
    }

    /**
     * Extract the raw DER bytes of the Subject Name and the public key value
     * from a DER-encoded X.509 certificate.
     *
     * The Subject bytes are the full DER encoding of the issuer Name SEQUENCE.
     * The pubkey bytes are the BIT STRING value without the leading unused-bits byte.
     *
     * @return array{subject: string, pubkey: string}
     */
    private function extractDerSubjectAndPubkey(string $certDer): array
    {
        $certOff = 0;
        $certTlv = $this->asn1ReadTlv($certDer, $certOff);

        $tbsOff = 0;
        $tbsTlv = $this->asn1ReadTlv($certTlv['value'], $tbsOff);
        $tbs = $tbsTlv['value'];

        $off = 0;
        if ($off < \strlen($tbs) && (\ord($tbs[$off]) & 0xe0) === 0xa0) {
            $this->asn1ReadTlv($tbs, $off);
        }

        $this->asn1ReadTlv($tbs, $off);
        $this->asn1ReadTlv($tbs, $off);

        $issuerStart = $off;
        $this->asn1ReadTlv($tbs, $off);
        $subjectDer = \substr($tbs, $issuerStart, $off - $issuerStart);

        $this->asn1ReadTlv($tbs, $off);
        $this->asn1ReadTlv($tbs, $off);

        $spki = $this->asn1ReadTlv($tbs, $off);
        $spkiOff = 0;
        $this->asn1ReadTlv($spki['value'], $spkiOff);
        $bitStr = $this->asn1ReadTlv($spki['value'], $spkiOff);
        $pubkey = \substr($bitStr['value'], 1);

        return ['subject' => $subjectDer, 'pubkey' => $pubkey];
    }

    /**
     * Encode a raw big-endian byte string as an ASN.1 DER INTEGER.
     */
    private function asn1EncodeIntegerBytes(string $bytes): string
    {
        if ($bytes === '') {
            $bytes = "\x00";
        }

        if ((\ord($bytes[0]) & 0x80) !== 0) {
            $bytes = "\x00" . $bytes;
        }

        return "\x02" . $this->asn1EncodeLength(\strlen($bytes)) . $bytes;
    }

    /**
     * Build an RFC 2560 OCSPRequest in DER format for a single certificate.
     *
     * Uses SHA-1 as the hash algorithm for CertID as required by RFC 2560.
     *
     * @phpstan-param TValidationCert $leafCert
     * @phpstan-param TValidationCert $issuerCert
     */
    protected function buildOcspRequest(array $leafCert, array $issuerCert): string
    {
        $issuerInfo = $this->extractDerSubjectAndPubkey($issuerCert['der']);
        $issuerNameHash = \hash('sha1', $issuerInfo['subject'], true);
        $issuerKeyHash = \hash('sha1', $issuerInfo['pubkey'], true);

        $decoded = $leafCert['serial'] !== '' ? \hex2bin($leafCert['serial']) : false;
        $serialBytes = \is_string($decoded) ? $decoded : "\x00";

        $algId = $this->asn1EncodeSequence(
            $this->asn1EncodeObjectIdentifier('1.3.14.3.2.26') . $this->asn1EncodeNull()
        );
        $certId = $this->asn1EncodeSequence(
            $algId
            . $this->asn1EncodeOctetString($issuerNameHash)
            . $this->asn1EncodeOctetString($issuerKeyHash)
            . $this->asn1EncodeIntegerBytes($serialBytes)
        );
        $requestList = $this->asn1EncodeSequence($this->asn1EncodeSequence($certId));
        return $this->asn1EncodeSequence($this->asn1EncodeSequence($requestList));
    }

    /**
     * Collect OCSP responses and CRL data for one certificate.
     * Updates the provided lists and dedup maps in place.
     *
     * @phpstan-param TValidationCert       $cert
     * @phpstan-param TValidationCert|null  $issuerCert
     * @phpstan-param array<int, string>    $ocspList
     * @phpstan-param array<string, int>    $ocspDedup
     * @phpstan-param array<int, string>    $crlList
     * @phpstan-param array<string, int>    $crlDedup
     * @return array{ocsp: array<int>, crls: array<int>}
     */
    private function collectRevocationForCert(
        array $cert,
        ?array $issuerCert,
        bool $embedOcsp,
        bool $embedCrl,
        array &$ocspList,
        array &$ocspDedup,
        array &$crlList,
        array &$crlDedup
    ): array {
        $ocspIdxs = [];
        $crlIdxs = [];

        if ($embedOcsp && $issuerCert !== null && $cert['ocsp_urls'] !== []) {
            $requestDer = $this->buildOcspRequest($cert, $issuerCert);
            foreach ($cert['ocsp_urls'] as $url) {
                try {
                    $resp = $this->postOcspRequest($url, $requestDer);
                } catch (\Throwable) {
                    continue;
                }

                $fp = \hash('sha256', $resp);
                if (! isset($ocspDedup[$fp])) {
                    $ocspDedup[$fp] = \count($ocspList);
                    $ocspList[] = $resp;
                }

                $ocspIdxs[] = $ocspDedup[$fp];
            }
        }

        if ($embedCrl && $cert['crl_dp_urls'] !== []) {
            foreach ($cert['crl_dp_urls'] as $url) {
                try {
                    $crlData = $this->getCrlData($url);
                } catch (\Throwable) {
                    continue;
                }

                $fp = \hash('sha256', $crlData);
                if (! isset($crlDedup[$fp])) {
                    $crlDedup[$fp] = \count($crlList);
                    $crlList[] = $crlData;
                }

                $crlIdxs[] = $crlDedup[$fp];
            }
        }

        return ['ocsp' => $ocspIdxs, 'crls' => $crlIdxs];
    }

    /**
     * Send an OCSP request via HTTP POST.
     * Override in subclasses or test doubles to inject a canned response.
     */
    protected function postOcspRequest(string $url, string $request): string
    {
        $context = \stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/ocsp-request\r\nContent-Length: " . \strlen($request),
                'content' => $request,
                'timeout' => 30,
            ],
        ]);
        $response = \file_get_contents($url, false, $context);
        if ($response === false) {
            throw new PdfException('OCSP request failed: ' . $url);
        }

        return $response;
    }

    /**
     * Fetch a CRL via HTTP GET.
     * Override in subclasses or test doubles to inject canned data.
     */
    protected function getCrlData(string $url): string
    {
        $data = \file_get_contents($url);
        if ($data === false) {
            throw new PdfException('CRL fetch failed: ' . $url);
        }

        return $data;
    }

    protected function buildTimestampRequest(string $signature): string
    {
        $hashAlgo = \strtolower((string) $this->sigtimestamp['hash_algorithm']);
        $hash = \hash($hashAlgo, $signature, true);

        $oid = $this->getTimestampHashAlgorithmOid($hashAlgo);
        $messageImprint = $this->asn1EncodeSequence(
            $this->asn1EncodeSequence($this->asn1EncodeObjectIdentifier($oid) . $this->asn1EncodeNull())
            . $this->asn1EncodeOctetString($hash)
        );

        $body = $this->asn1EncodeInteger(1) . $messageImprint;
        if (! empty($this->sigtimestamp['policy_oid'])) {
            $body .= $this->asn1EncodeObjectIdentifier((string) $this->sigtimestamp['policy_oid']);
        }

        if ($this->sigtimestamp['nonce_enabled']) {
            $body .= $this->asn1EncodeInteger(\random_int(1, PHP_INT_MAX));
        }

        $body .= $this->asn1EncodeBoolean(true);
        return $this->asn1EncodeSequence($body);
    }

    /**
     * Submit an RFC3161 TimeStampReq to the configured TSA endpoint.
     *
     * @param string $request DER-encoded timestamp request.
     */
    protected function postTimestampRequest(string $request): string
    {
        $host = (string) $this->sigtimestamp['host'];
        if ($host === '') {
            throw new PdfException('Invalid TSA host');
        }

        $timeout = (int) $this->sigtimestamp['timeout'];
        if ($timeout < 1) {
            $timeout = 5;
        }

        $headers = [
            'Content-Type: application/timestamp-query',
            'Accept: application/timestamp-reply',
        ];

        if ($this->sigtimestamp['username'] !== '') {
            $auth = (string) $this->sigtimestamp['username'] . ':' . (string) $this->sigtimestamp['password'];
            $headers[] = 'Authorization: Basic ' . \base64_encode($auth);
        }

        if (\function_exists('curl_init')) {
            $ch = \curl_init($host);
            if ($ch === false) {
                throw new PdfException('Unable to initialize TSA request');
            }

            $opts = [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $request,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => $timeout,
                CURLOPT_SSL_VERIFYPEER => (bool) $this->sigtimestamp['verify_peer'],
                CURLOPT_SSL_VERIFYHOST => (bool) $this->sigtimestamp['verify_peer'] ? 2 : 0,
            ];

            if (! empty($this->sigtimestamp['cert'])) {
                $opts[CURLOPT_CAINFO] = (string) $this->sigtimestamp['cert'];
            }

            \curl_setopt_array($ch, $opts);
            $response = \curl_exec($ch);
            if ($response === false) {
                throw new PdfException('Unable to request TSA timestamp');
            }

            return $response === true ? '' : $response;
        }

        $context = \stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => \implode("\r\n", $headers),
                'content' => $request,
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => (bool) $this->sigtimestamp['verify_peer'],
                'verify_peer_name' => (bool) $this->sigtimestamp['verify_peer'],
                'cafile' => (string) $this->sigtimestamp['cert'],
            ],
        ]);

        $response = @\file_get_contents($host, false, $context);
        if ($response === false) {
            throw new PdfException('Unable to request TSA timestamp');
        }

        return $response;
    }

    /**
     * Extract timestamp token from RFC3161 TimeStampResp.
     *
     * @param string $response DER-encoded timestamp response.
     */
    protected function extractTimestampTokenFromResponse(string $response): string
    {
        if ($response === '') {
            throw new PdfException('Empty TSA response');
        }

        /** @var int<0, max> $offset */
        $offset = 0;
        $root = $this->asn1ReadTlv($response, $offset);
        if (($root['tag'] !== 0x30) || ($offset !== \strlen($response))) {
            throw new PdfException('Invalid TSA response');
        }

        /** @var int<0, max> $inner */
        $inner = 0;
        $statusSeq = $this->asn1ReadTlv($root['value'], $inner);
        if ($statusSeq['tag'] !== 0x30) {
            throw new PdfException('Invalid TSA status response');
        }

        /** @var int<0, max> $statusOffset */
        $statusOffset = 0;
        $status = $this->asn1ReadTlv($statusSeq['value'], $statusOffset);
        if ($status['tag'] !== 0x02) {
            throw new PdfException('Invalid TSA status code');
        }

        $statusCode = $this->asn1DecodeInteger($status['value']);
        if (($statusCode !== 0) && ($statusCode !== 1)) {
            throw new PdfException('TSA request rejected');
        }

        if ($inner >= \strlen($root['value'])) {
            throw new PdfException('Missing TSA token');
        }

        $token = $this->asn1ReadTlv($root['value'], $inner);
        if ($token['tag'] !== 0x30) {
            throw new PdfException('Invalid TSA token structure');
        }

        return $token['raw'];
    }

    protected function getTimestampHashAlgorithmOid(string $algorithm): string
    {
        return match ($algorithm) {
            'sha256' => '2.16.840.1.101.3.4.2.1',
            'sha384' => '2.16.840.1.101.3.4.2.2',
            'sha512' => '2.16.840.1.101.3.4.2.3',
            default => throw new PdfException('Unsupported TSA hash algorithm'),
        };
    }

    /** @param int<0, max> $length */
    protected function asn1EncodeLength(int $length): string
    {
        if ($length < 128) {
            return \chr($length);
        }

        $encoded = '';
        $value = $length;
        while ($value > 0) {
            $encoded = \chr((int)($value & 0xFF)) . $encoded;
            $value = (int) ($value / 256);
        }

        $encodedLength = \strlen($encoded);
        if ($encodedLength > 0x7F) {
            throw new PdfException('ASN.1 length encoding overflow');
        }

        /** @var int<0, 127> $encodedLength */
        return \chr(0x80 | $encodedLength) . $encoded;
    }

    /** @param int<0, max> $value */
    protected function asn1EncodeInteger(int $value): string
    {
        $data = '';
        $num = $value;
        while ($num > 0) {
            $data = \chr((int)($num & 0xFF)) . $data;
            $num = (int) ($num / 256);
        }

        if ($data === '') {
            $data = "\x00";
        }

        if ((\ord($data[0]) & 0x80) !== 0) {
            $data = "\x00" . $data;
        }

        return "\x02" . $this->asn1EncodeLength(\strlen($data)) . $data;
    }

    protected function asn1EncodeBoolean(bool $value): string
    {
        return "\x01\x01" . ($value ? "\xFF" : "\x00");
    }

    protected function asn1EncodeNull(): string
    {
        return "\x05\x00";
    }

    protected function asn1EncodeOctetString(string $value): string
    {
        return "\x04" . $this->asn1EncodeLength(\strlen($value)) . $value;
    }

    protected function asn1EncodeSequence(string $value): string
    {
        return "\x30" . $this->asn1EncodeLength(\strlen($value)) . $value;
    }

    protected function asn1EncodeObjectIdentifier(string $oid): string
    {
        $parts = \array_map('intval', \explode('.', $oid));
        if (\count($parts) < 2) {
            throw new PdfException('Invalid OID');
        }

        $data = \chr((int)((($parts[0] * 40) + $parts[1]) & 0xFF));
        $count = \count($parts);
        for ($idx = 2; $idx < $count; ++$idx) {
            $part = (int) \max(0, $parts[$idx]);
            $data .= $this->asn1EncodeBase128Int($part);
        }

        return "\x06" . $this->asn1EncodeLength(\strlen($data)) . $data;
    }

    /** @param int<0, max> $value */
    protected function asn1EncodeBase128Int(int $value): string
    {
        $bytes = [($value & 0x7F)];
        $value = (int) ($value / 128);
        while ($value > 0) {
            \array_unshift($bytes, ($value & 0x7F) | 0x80);
            $value = (int) ($value / 128);
        }

        $out = '';
        foreach ($bytes as $byte) {
            $out .= \chr($byte);
        }

        return $out;
    }

    /**
     * @param int $offset
     *
     * @return array{tag: int, value: string, raw: string}
     */
    protected function asn1ReadTlv(string $data, int &$offset): array
    {
        if ($offset >= \strlen($data)) {
            throw new PdfException('Malformed ASN.1 structure');
        }

        $start = $offset;
        $tag = \ord($data[$offset]);
        ++$offset;

        $length = $this->asn1ReadLength($data, $offset);
        if (($offset + $length) > \strlen($data)) {
            throw new PdfException('Malformed ASN.1 length');
        }

        $value = \substr($data, $offset, $length);
        $offset += $length;
        $raw = \substr($data, $start, ($offset - $start));

        return ['tag' => $tag, 'value' => $value, 'raw' => $raw];
    }

    /** @param int $offset */
    protected function asn1ReadLength(string $data, int &$offset): int
    {
        if ($offset >= \strlen($data)) {
            throw new PdfException('Malformed ASN.1 length');
        }

        $first = \ord($data[$offset]);
        ++$offset;
        if (($first & 0x80) === 0) {
            return $first;
        }

        $numBytes = ($first & 0x7F);
        if (($numBytes < 1) || ($numBytes > 4) || (($offset + $numBytes) > \strlen($data))) {
            throw new PdfException('Unsupported ASN.1 length');
        }

        $length = 0;
        for ($idx = 0; $idx < $numBytes; ++$idx) {
            $length = ($length * 256) + \ord($data[$offset + $idx]);
        }

        $offset += $numBytes;
        return $length;
    }

    protected function asn1DecodeInteger(string $value): int
    {
        if ($value === '') {
            throw new PdfException('Invalid ASN.1 integer');
        }

        $int = 0;
        $len = \strlen($value);
        for ($idx = 0; $idx < $len; ++$idx) {
            $int = ($int * 256) + \ord($value[$idx]);
        }

        return $int;
    }

    /**
     * Returns the PDF signarure entry.
     */
    protected function getOutSignature(): string
    {
        if ((! $this->sign) || ($this->signature['cert_type'] < 0)) {
            return '';
        }

        // widget annotation for signature
        $soid = $this->objid['signature'];
        $oid = $soid + 1;
        $page = $this->page->getPage($this->signature['appearance']['page']);
        $out = $soid . ' 0 obj' . "\n"
            . '<<'
            . ' /Type /Annot'
            . ' /Subtype /Widget'
            . ' /Rect [' . $this->signature['appearance']['rect'] . ']'
            . ' /P ' . $page['n'] . ' 0 R' // link to signature appearance page
            . ' /F 4'
            . ' /FT /Sig'
            . ' /T ' . $this->getOutTextString($this->signature['appearance']['name'], $soid, true)
            . ' /Ff 0'
            . ' /V ' . $oid . ' 0 R'
            . ' >>' . "\n"
            . 'endobj' . "\n";
        $out .= $oid . ' 0 obj' . "\n";
        $out .= '<< /Type /Sig /Filter /Adobe.PPKLite /SubFilter /adbe.pkcs7.detached '
            . $this::BYTERANGE
            . ' /Contents<' . \str_repeat('0', $this::SIGMAXLEN) . '>';
        if (empty($this->signature['approval']) || ($this->signature['approval'] != 'A')) {
            $out .= ' /Reference [ << /Type /SigRef';
            if ($this->signature['cert_type'] > 0) {
                $out .= $this->getOutSignatureDocMDP();
            } else {
                $out .= $this->getOutSignatureUserRights();
            }

            // optional digest data (values must be calculated and replaced later)
            //$out .= ' /Data ********** 0 R'
            //    .' /DigestMethod /MD5'
            //    .' /DigestLocation[********** 34]'
            //    .' /DigestValue<********************************>';
            $out .= ' >> ]'; // end of reference
        }

        $out .= $this->getOutSignatureInfo($oid);
        return $out . ' /M '
            . $this->getOutDateTimeString($this->docmodtime, $oid)
            . ' >>' . "\n"
            . 'endobj' . "\n";
    }

    /**
     * Returns the PDF signarure entry.
     */
    protected function getOutSignatureDocMDP(): string
    {
        if (empty($this->signature['cert_type'])) {
            return '';
        }

        return ' /TransformMethod /DocMDP '
            . '/TransformParams <<'
            . ' /Type /TransformParams'
            . ' /P ' . $this->signature['cert_type']
            . ' /V /1.2'
            . ' >>';
    }

    /**
     * Returns the PDF signarure entry.
     */
    protected function getOutSignatureUserRights(): string
    {
        $out = ' /TransformMethod /UR3 /TransformParams << /Type /TransformParams /V /2.2';
        if (! empty($this->userrights['document'])) {
            $out .= ' /Document[' . $this->userrights['document'] . ']';
        }

        if (! empty($this->userrights['form'])) {
            $out .= ' /Form[' . $this->userrights['form'] . ']';
        }

        if (! empty($this->userrights['signature'])) {
            $out .= ' /Signature[' . $this->userrights['signature'] . ']';
        }

        if (! empty($this->userrights['annots'])) {
            $out .= ' /Annots[' . $this->userrights['annots'] . ']';
        }

        if (! empty($this->userrights['ef'])) {
            $out .= ' /EF[' . $this->userrights['ef'] . ']';
        }

        if (! empty($this->userrights['formex'])) {
            $out .= ' /FormEX[' . $this->userrights['formex'] . ']';
        }

        return $out . ' >>';
    }

    /**
     * Returns the PDF signarure info section.
     *
     * @param int $oid Object ID.
     */
    protected function getOutSignatureInfo(int $oid): string
    {
        $out = '';
        if (! empty($this->signature['info']['Name'])) {
            $out .= ' /Name ' . $this->getOutTextString($this->signature['info']['Name'], $oid, true);
        }

        if (! empty($this->signature['info']['Location'])) {
            $out .= ' /Location ' . $this->getOutTextString($this->signature['info']['Location'], $oid, true);
        }

        if (! empty($this->signature['info']['Reason'])) {
            $out .= ' /Reason ' . $this->getOutTextString($this->signature['info']['Reason'], $oid, true);
        }

        if (! empty($this->signature['info']['ContactInfo'])) {
            $out .= ' /ContactInfo ' . $this->getOutTextString($this->signature['info']['ContactInfo'], $oid, true);
        }

        return $out;
    }

    /**
     * Get the PDF output string for XObject resources dictionary.
     */
    protected function getXObjectDict(): string
    {
        $out = ' /XObject <<';

        foreach ($this->xobjects as $id => $oid) {
            $out .= ' /' . $id . ' ' . $oid['n'] . ' 0 R';
        }

        $out .= $this->image->getXobjectDict();

        return $out . ' >>';
    }

    /**
     * Get the PDF output string for Pattern resources dictionary.
     */
    protected function getPatternDict(): string
    {
        if (empty($this->patterns)) {
            return '';
        }

        $out = ' /Pattern <<';
        foreach ($this->patterns as $pid => $pattern) {
            if (empty($pattern['n'])) {
                continue;
            }
            $out .= ' /' . $pid . ' ' . $pattern['n'] . ' 0 R';
        }

        return $out . ' >>';
    }

    /**
     * Get the PDF output string for Layer resources dictionary.
     */
    protected function getLayerDict(): string
    {
        if (empty($this->pdflayer)) {
            return '';
        }

        $out = ' /Properties <<';

        foreach ($this->pdflayer as $layer) {
            $out .= ' /' . $layer['layer'] . ' ' . $layer['objid'] . ' 0 R';
        }

        return $out . ' >>';
    }

    /**
     * Returns 'ON' if $val is true, 'OFF' otherwise.
     *
     * @param mixed $val Item to parse for boolean value.
     */
    protected function getOnOff(mixed $val): string
    {
        if ((bool) $val) {
            return 'ON';
        }

        return 'OFF';
    }

    /**
     * Render the PDF in the browser or output the RAW data in the CLI.
     *
     * @param string $rawpdf Raw PDF data string from getOutPDFString().
     *
     * @throw PdfException in case of error.
     */
    public function renderPDF(string $rawpdf = ''): void
    {
        if (PHP_SAPI == 'cli') {
            $this->writeRawPdfOutput($rawpdf);
            return;
        }

        if (\headers_sent()) {
            throw new PdfException(
                'The PDF file cannot be sent because some data has already been output to the browser.'
            );
        }

        \header('Content-Type: application/pdf');
        \header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        \header('Pragma: public');
        \header('Expires: Sat, 01 Jan 2000 01:00:00 GMT'); // Date in the past
        \header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        \header(
            'Content-Disposition: inline; filename="' . $this->encpdffilename . '"; filename*=UTF-8\'\''
            . $this->encpdffilename
        );
        if (empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            // the content length may vary if the server is using compression
            \header('Content-Length: ' . \strlen($rawpdf));
        }

        $this->writeRawPdfOutput($rawpdf);
    }

    /**
     * Trigger the browser Download dialog to download the PDF document.
     *
     * @param string $rawpdf Raw PDF data string from getOutPDFString().
     *
     * @throw PdfException in case of error.
     */
    public function downloadPDF(string $rawpdf = ''): void
    {
        if (\ob_get_contents()) {
            throw new PdfException(
                'The PDF file cannot be sent, some data has already been output to the browser.'
            );
        }

        if (\headers_sent()) {
            throw new PdfException(
                'The PDF file cannot be sent because some data has already been output to the browser.'
            );
        }

        \header('Content-Description: File Transfer');
        \header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        \header('Pragma: public');
        \header('Expires: Sat, 01 Jan 2000 01:00:00 GMT'); // Date in the past
        \header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        // force download dialog
        \header('Content-Type: application/pdf');
        if (! \str_contains(PHP_SAPI, 'cgi')) {
            \header('Content-Type: application/force-download', false);
            \header('Content-Type: application/octet-stream', false);
            \header('Content-Type: application/download', false);
        }

        // use the Content-Disposition header to supply a recommended filename
        \header(
            'Content-Disposition: attachment; filename="' . $this->encpdffilename . '";'
            . " filename*=UTF-8''" . $this->encpdffilename
        );
        \header('Content-Transfer-Encoding: binary');
        if (empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            // the content length may vary if the server is using compression
            \header('Content-Length: ' . \strlen($rawpdf));
        }

        $this->writeRawPdfOutput($rawpdf);
    }

    /**
     * Write raw PDF bytes to the current output stream.
     *
     * Using php://output avoids text-oriented output paths that may alter
     * binary PDF content under some SAPIs or output handlers.
     *
     * @throws PdfException in case of error.
     */
    protected function writeRawPdfOutput(string $rawpdf): void
    {
        $output = \fopen('php://output', 'wb');
        if ($output === false) {
            throw new PdfException('Unable to open the output stream for PDF rendering.');
        }

        $remaining = \strlen($rawpdf);
        $offset = 0;
        while ($remaining > 0) {
            $written = \fwrite($output, \substr($rawpdf, $offset), $remaining);
            if (($written === false) || ($written < 1)) {
                \fclose($output);
                throw new PdfException('Unable to write the PDF data to the output stream.');
            }

            $offset += $written;
            $remaining -= $written;
        }

        \fclose($output);
    }

    /**
     * Save the PDF document to a local file.
     *
     * @param string $path   Path to the output file.
     * @param string $rawpdf Raw PDF data string from getOutPDFString().
     */
    public function savePDF(
        string $path = '',
        string $rawpdf = ''
    ): void {
        $filepath = \implode('/', [\realpath($path), $this->pdffilename]);
        $fhd = $this->file->fopenLocal($filepath, 'wb');
        if (! $fhd) {
            throw new PdfException('Unable to create output file: ' . $filepath);
        }

        \fwrite($fhd, $rawpdf, \strlen($rawpdf));
        \fclose($fhd);
    }

    /**
     * Returns the PDF as base64 mime multi-part email attachment (RFC 2045).
     *
     * @param string $rawpdf Raw PDF data string from getOutPDFString().
     *
     * @return string Email attachment as raw string.
     */
    public function getMIMEAttachmentPDF(string $rawpdf = ''): string
    {
        return 'Content-Type: application/pdf;' . "\r\n"
        . ' name="' . $this->encpdffilename . '"' . "\r\n"
        . 'Content-Transfer-Encoding: base64' . "\r\n"
        . 'Content-Disposition: attachment;' . "\r\n"
        . ' filename="' . $this->encpdffilename . '"' . "\r\n\r\n"
        . \chunk_split(\base64_encode($rawpdf), 76, "\r\n");
    }
}
