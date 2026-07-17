<?php

declare(strict_types=1);

/**
 * Output.php
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

use Com\Tecnick\File\Exception as FileException;
use Com\Tecnick\Pdf\Encrypt\Exception as EncryptException;
use Com\Tecnick\Pdf\Exception as PdfException;
use Com\Tecnick\Pdf\Font\Exception as FontException;
use Com\Tecnick\Pdf\Font\Output as OutFont;
use Com\Tecnick\Pdf\Page\Exception as PageException;
use Com\Tecnick\Pdf\Sign\Cms\Builder as SignBuilder;
use Com\Tecnick\Pdf\Sign\Config as SignConfig;
use Com\Tecnick\Pdf\Sign\Exception as SignException;
use Com\Tecnick\Pdf\Sign\Output\DocTimeStamp as SignDocTimeStamp;
use Com\Tecnick\Pdf\Sign\Output\Dss as SignDss;
use Com\Tecnick\Pdf\Sign\Output\Signature as SignSignature;
use Com\Tecnick\Pdf\Sign\Output\Widget as SignWidget;
use Com\Tecnick\Pdf\Sign\Signer;
use Com\Tecnick\Pdf\Sign\Timestamp\Client as TimestampClient;
use Com\Tecnick\Pdf\Sign\Timestamp\Config as TimestampConfig;
use Com\Tecnick\Unicode\Exception as UnicodeException;
use OpenSSLAsymmetricKey;

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
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type PageData from \Com\Tecnick\Pdf\Page\Box
 *
 * @phpstan-import-type TFourFloat from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotQuadPoint from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotBorderStyle from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotBorderEffect from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotMeasure from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotMarkup from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotStates from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotText from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TUriAction from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotActionDict from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotAdditionalActionDict from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotLink from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotFreeText from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotLine from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotSquare from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotCircle from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotPolygon from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotPolyline from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotTextMarkup from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotCaret from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotRubberStamp from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotInk from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotPopup from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotFileAttachment from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotSound from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotMovieDict from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotMovieActDict from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotMovie from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotIconFitDict from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotMKDict from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotScreen from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotWidget from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotFixedPrintDict from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotWatermark from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotRedact from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotOptsA from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotOptsB from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotOptsC from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotOptsD from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotOptsE from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnotOpts from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TAnnot from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TGTransparency from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TXOBject from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TPatternObject from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TSVGMaskObject from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TOutline from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TLtvConfig from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TSignature from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TSignTimeStamp from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TUserRights from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TEmbeddedFile from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TObjID from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TSignDocPrepared from \Com\Tecnick\Pdf\Base
 *
 * @SuppressWarnings("PHPMD")
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class Output extends \Com\Tecnick\Pdf\MetaInfo
{
    /**
     * Object to export font data.
     *
     * @var OutFont
     */
    protected OutFont $outfont;

    /**
     * Output-local transparency gate used by import and document assembly code.
     */
    protected function isTransparencyAllowed(): bool
    {
        if (!$this->pdfx) {
            return true;
        }

        return \in_array($this->pdfxMode, ['pdfx4', 'pdfx5'], true);
    }

    /**
     * Record, for each page, whether it actually uses transparency, so the page
     * layer can decide whether to emit the per-page transparency /Group.
     *
     * A page is flagged transparent when its content blends (a sub-1 alpha, a
     * non-Normal blend mode or a soft mask), paints a soft-masked image, draws
     * an imported page, or paints a Form XObject that itself blends. The actual
     * emission policy ('auto'/'always'/'never', set via
     * Tcpdf::setPageTransparencyGroup()) and the PDF/A suppression are applied by
     * the page layer; this method only supplies the facts it needs. Called once,
     * just before the page objects are serialized.
     *
     * @throws PageException
     */
    protected function detectPageTransparency(): void
    {
        $transpNames = $this->getTransparencyResourceNames();
        foreach ($this->page->getPages() as $pid => $page) {
            $stream = \implode("\n", $page['content']);
            $this->page->setPageTransparency($this->streamUsesTransparency($stream, $transpNames), $pid);
        }
    }

    /**
     * Build the set of resource names whose `gs` operator actually enables
     * transparency: ExtGState objects with a sub-1 alpha, a non-Normal blend
     * mode or a soft mask, plus every SVG soft-mask graphics state.
     *
     * @return array<string, true>
     */
    protected function getTransparencyResourceNames(): array
    {
        $names = [];
        foreach ($this->graph->getTransparencyExtGStateNames() as $name) {
            $names[$name] = true;
        }

        foreach (\array_keys($this->svgmasks) as $name) {
            $names[$name] = true;
        }

        return $names;
    }

    /**
     * Whether a content stream actually triggers transparency.
     *
     * A stream is considered transparent when it references a transparency
     * ExtGState (`/<name> gs`), paints a soft-masked image (`/IMGmask*`,
     * `/IMGplain*`), draws an imported page (`/IMP*`, treated conservatively as
     * potentially transparent), or paints a Form XObject that itself blends.
     * Referenced Form XObjects are inspected once, guarded against cycles.
     *
     * @param string              $stream      Content stream bytes.
     * @param array<string, true> $transpNames Transparency-bearing resource names.
     * @param array<string, true> $visited     Form XObject ids already inspected.
     */
    protected function streamUsesTransparency(string $stream, array $transpNames, array $visited = []): bool
    {
        if ($stream === '') {
            return false;
        }

        $matchGs = [];
        $gsCount = $transpNames === [] ? 0 : \preg_match_all('/\/([A-Za-z0-9_]+)\s+gs\b/', $stream, $matchGs);
        if ($gsCount !== false && $gsCount > 0) {
            foreach ($matchGs[1] ?? [] as $name) {
                if (isset($transpNames[$name])) {
                    return true;
                }
            }
        }

        $matchDo = [];
        $doCount = \preg_match_all('/\/([A-Za-z0-9_]+)\s+Do\b/', $stream, $matchDo);
        if ($doCount !== false && $doCount > 0) {
            foreach ($matchDo[1] ?? [] as $name) {
                if (
                    \preg_match('/^IMG(?:mask|plain)[0-9]+$/', $name) === 1
                    || \preg_match('/^IMP[0-9]+$/', $name) === 1
                ) {
                    return true;
                }

                $xobj = $this->xobjects[$name] ?? null;
                if (!\is_array($xobj)) {
                    continue;
                }

                if (($xobj['transparency'] ?? null) !== null) {
                    return true;
                }

                if (isset($visited[$name])) {
                    continue;
                }

                $visited[$name] = true;
                $inner = $xobj['outdata'];
                if ($inner !== '' && $this->streamUsesTransparency($inner, $transpNames, $visited)) {
                    return true;
                }
            }
        }

        return false;
    }

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
     * Struct parent keys assigned to annotation object IDs.
     *
     * @var array<int, int>
     */
    protected array $annotstructparents = [];

    /**
     * Returns the RAW PDF string.
     *
     * @return string PDF document content.
     *
     * @throws PdfException
     * @throws FileException
     * @throws UnicodeException
     * @throws EncryptException
     * @throws FontException
     * @throws PageException
     * @throws \Throwable
     */
    public function getOutPDFString(): string
    {
        $out = $this->getOutPDFHeader() . $this->getOutPDFBody();
        $startxref = \strlen($out);
        $offset = $this->getPDFObjectOffsets($out);
        $out .=
            $this->getOutPDFXref($offset)
            . $this->getOutPDFTrailer()
            . 'startxref'
            . "\n"
            . $startxref
            . "\n"
            . '%%EOF'
            . "\n";
        return $this->appendDocTimeStampRevision($this->appendDssRevision($this->signDocument($out)));
    }

    /**
     * Returns the PDF document header.
     */
    protected function getOutPDFHeader(): string
    {
        return '%PDF-' . $this->pdfver . "\n%\xE2\xE3\xCF\xD3\n";
    }

    /**
     * Returns the raw PDF Body section.
     *
     * @return string PDF body content.
     *
     * @throws PdfException
     * @throws FileException
     * @throws UnicodeException
     * @throws EncryptException
     * @throws FontException
     * @throws PageException
     * @throws \Throwable
     */
    protected function getOutPDFBody(): string
    {
        if ($this->pdfuaMode === '') {
            $this->pagestructmcids = [];
            $this->pdfuaStructLog = [];
            $this->pdfuaStructStack = [];
        }

        $this->detectPageTransparency();
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
            fileHelper: $this->file,
            subsetCache: $this->fontSubsetCacheAdapter(),
        );
        $out .= $this->outfont->getFontsBlock();
        $this->pon = $this->outfont->getObjectNumber();
        $out .= $this->image->getOutImagesBlock($this->pon);
        $this->pon = $this->image->getObjectNumber();
        $out .= $this->color->getPdfSpotObjects($this->pon);
        $out .= $this->graph->getOutGradientShaders($this->pon);
        $this->pon = $this->graph->getObjectNumber();
        $out .= $this->getOutXObjects();
        $out .= $this->getOutImportedObjects();
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
        $isEncrypted = $enc['encrypted'];
        // PDF/X prohibits encryption (ISO 15930); skip the encryption object when PDF/X mode is active.
        if ($isEncrypted && !$this->pdfx) {
            $out .= $this->encrypt->getPdfEncryptionObj($this->pon);
        }

        $out .= $this->getOutSignatureFields();
        $out .= $this->getOutSignature();
        // The DSS (PAdES B-LT) is emitted in a post-signing incremental revision
        // (appendDssRevision) because its VRI key is the SHA-1 of the final
        // signature /Contents, which does not exist until after signing.
        $out .= $this->getOutMetaInfo();
        $out .= $this->getOutXMP();
        $out .= $this->getOutICC();
        $result = $out . $this->getOutCatalog();
        $this->importer?->cleanUp();
        return $result;
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
        $matches = [];
        \preg_match_all('/(([0-9]+)[\s][0-9]+[\s]obj[\n])/i', $data, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $offset = [];
        foreach ($matches as $match) {
            $m2 = $match[2] ?? [];
            $key = (int) ($m2[0] ?? 0);
            $offset[$key] = (int) ($m2[1] ?? 0);
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
        $out = 'xref' . "\n" . '0 ' . ($this->pon + 1) . "\n" . '0000000000 65535 f ' . "\n";
        $freegen = $this->pon + 2;
        $lastobj = \array_key_last($offset) ?? 0;
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
        $out =
            'trailer'
            . "\n"
            . '<<'
            . ' /Size '
            . ($this->pon + 1)
            . ' /Root '
            . $this->objid['catalog']
            . ' 0 R'
            . ' /Info '
            . $this->objid['info']
            . ' 0 R';
        $encData = $this->encrypt->getEncryptionData();
        $enc = $encData;
        // PDF/X prohibits encryption; omit the /Encrypt trailer entry when PDF/X mode is active.
        if ((int) $enc['objid'] !== 0 && !$this->pdfx) {
            $out .= ' /Encrypt ' . (int) $enc['objid'] . ' 0 R';
        }

        return $out . (' /ID [ <' . $this->fileid . '> <' . $this->fileid . '> ] >>' . "\n");
    }

    /**
     * Append an incremental-update revision to a completed PDF document.
     *
     * The original bytes are never modified: the new object bodies are appended,
     * then a classic incremental xref table whose trailer chains to the previous
     * revision via /Prev. Objects are keyed by number so the caller (which
     * advances $this->pon while emitting via the package emitters) stays
     * authoritative over numbering; encryption of the object contents is the
     * caller's responsibility, while the trailer still advertises /Encrypt. This
     * is the foundation for PAdES B-LT (a post-signing DSS revision) and B-LTA
     * (a document timestamp revision).
     *
     * @param string             $pdf     The complete PDF, ending after its %%EOF.
     * @param array<int, string> $objects New/replacement object bodies keyed by
     *                                    object number (each the full
     *                                    "N 0 obj ... endobj\n" fragment).
     *
     * @return string The PDF with one appended revision, or $pdf unchanged when
     *         there is nothing to add.
     */
    protected function appendIncrementalRevision(string $pdf, array $objects): string
    {
        if ($objects === []) {
            return $pdf;
        }

        \ksort($objects);

        $body = '';
        $offsets = [];
        $base = \strlen($pdf);
        foreach ($objects as $num => $obj) {
            $offsets[$num] = $base + \strlen($body);
            $body .= $obj;
        }

        $out = $pdf . $body;
        $xrefOffset = \strlen($out);
        $size = \max($this->pon, \array_key_last($offsets) ?? 0) + 1;

        $out .= $this->buildIncrementalXref($offsets);
        $out .= $this->buildIncrementalTrailer($this->previousStartxref($pdf), $size);

        return $out . 'startxref' . "\n" . $xrefOffset . "\n" . '%%EOF' . "\n";
    }

    /**
     * Build a classic incremental xref table, grouping consecutive object
     * numbers into subsections. The free-list head is inherited from the
     * previous revision through /Prev, so it is not repeated here.
     *
     * @param array<int, int> $offsets Byte offsets keyed by object number.
     */
    protected function buildIncrementalXref(array $offsets): string
    {
        \ksort($offsets);

        $out = 'xref' . "\n";
        $runStart = 0;
        $run = [];
        $prev = null;
        foreach ($offsets as $num => $off) {
            if ($prev !== null && $num === ($prev + 1)) {
                $run[] = $off;
            } else {
                $out .= $run === [] ? '' : $this->xrefSubsection($runStart, $run);
                $runStart = $num;
                $run = [$off];
            }

            $prev = $num;
        }

        return $out . ($run === [] ? '' : $this->xrefSubsection($runStart, $run));
    }

    /**
     * Render one xref subsection header plus its in-use entries.
     *
     * @param list<int> $offsets Byte offsets of consecutive objects from $start.
     */
    private function xrefSubsection(int $start, array $offsets): string
    {
        $out = $start . ' ' . \count($offsets) . "\n";
        foreach ($offsets as $off) {
            $out .= \sprintf('%010d 00000 n ' . "\n", $off);
        }

        return $out;
    }

    /**
     * Build the incremental-update trailer, chaining to the previous revision.
     */
    private function buildIncrementalTrailer(int $prevStartxref, int $size): string
    {
        $out =
            'trailer'
            . "\n"
            . '<<'
            . ' /Size '
            . $size
            . ' /Root '
            . $this->objid['catalog']
            . ' 0 R'
            . ' /Info '
            . $this->objid['info']
            . ' 0 R'
            . ' /Prev '
            . $prevStartxref;

        $enc = $this->encrypt->getEncryptionData();
        if ((int) $enc['objid'] !== 0 && !$this->pdfx) {
            $out .= ' /Encrypt ' . (int) $enc['objid'] . ' 0 R';
        }

        return $out . ' /ID [ <' . $this->fileid . '> <' . $this->fileid . '> ] >>' . "\n";
    }

    /**
     * Read the byte offset of the document's current (soon to be previous) xref
     * section from its last startxref pointer.
     */
    private function previousStartxref(string $pdf): int
    {
        $pos = \strrpos($pdf, 'startxref');
        if ($pos === false) {
            return 0;
        }

        $matches = [];
        if (\preg_match('/\d+/', \substr($pdf, $pos + 9), $matches) === 1) {
            return (int) ($matches[0] ?? '0');
        }

        return 0;
    }

    /**
     * Returns the PDF object to include a standard sRGB_IEC61966-2.1 blackscaled ICC colour profile.
     *
     * @return string ICC profile PDF object or empty string.
     *
     * @throws PdfException
     * @throws EncryptException
     */
    protected function getOutICC(): string
    {
        if ($this->pdfa === 0 && !$this->sRGB) {
            return '';
        }

        $oid = ++$this->pon;
        $this->objid['srgbicc'] = $oid;
        $out = $oid . ' 0 obj' . "\n";
        try {
            $icc = $this->file->getLocalFileData(__DIR__ . '/include/sRGB.icc.z');
        } catch (FileException $e) {
            throw new PdfException('Unable to read sRGB.icc.z file', 0, $e);
        }

        if ($icc === false) {
            throw new PdfException('Unable to read sRGB.icc.z file');
        }

        $icc = $this->encrypt->encryptString($icc, $oid);
        return (
            $out
            . '<< /N 3 /Filter /FlateDecode /Length '
            . \strlen($icc)
            . ' >>'
            . ' stream'
            . "\n"
            . $icc
            . "\n"
            . 'endstream'
            . "\n"
            . 'endobj'
            . "\n"
        );
    }

    /**
     * Get OutputIntents for sRGB IEC61966-2.1 if required.
     *
     * @return string OutputIntents section.
     *
     * @throws UnicodeException
     * @throws EncryptException
     */
    protected function getOutputIntentsSrgb(): string
    {
        if ($this->objid['srgbicc'] === 0) {
            return '';
        }

        $oid = $this->objid['catalog'];
        return (
            ' /OutputIntents [<< /Type /OutputIntent /S /GTS_PDFA1 /OutputCondition '
            . $this->getOutTextString('sRGB IEC61966-2.1', $oid, true)
            . ' /OutputConditionIdentifier '
            . $this->getOutTextString('sRGB IEC61966-2.1', $oid, true)
            . ' /RegistryName '
            . $this->getOutTextString('http://www.color.org', $oid, true)
            . ' /Info '
            . $this->getOutTextString('sRGB IEC61966-2.1', $oid, true)
            . ' /DestOutputProfile '
            . $this->objid['srgbicc']
            . ' 0 R'
            . ' >>]'
        );
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

    /**
     * Get OutputIntents for PDF-X.
     *
     * @return string OutputIntents section.
     *
     * @throws UnicodeException
     * @throws EncryptException
     */
    protected function getOutputIntentsPdfX(): string
    {
        $oid = $this->objid['catalog'];
        $identifier = $this->getOutputIntentsPdfXIdentifier();
        return (
            ' /OutputIntents [<< /Type /OutputIntent /S /GTS_PDFX /OutputConditionIdentifier '
            . $this->getOutTextString($identifier, $oid, true)
            . ' /RegistryName '
            . $this->getOutTextString('http://www.color.org', $oid, true)
            . ' /Info '
            . $this->getOutTextString($identifier, $oid, true)
            . ' >>]'
        );
    }

    /**
     * Get all OutputIntents.
     *
     * @return string OutputIntents section or empty string.
     *
     * @throws UnicodeException
     * @throws EncryptException
     */
    protected function getOutputIntents(): string
    {
        if ($this->objid['catalog'] === 0) {
            return '';
        }

        if ($this->pdfx) {
            return $this->getOutputIntentsPdfX();
        }

        return $this->getOutputIntentsSrgb();
    }

    /**
     * Get the PDF layers.
     *
     * @return string PDF layers section or empty string.
     *
     * @throws UnicodeException
     * @throws EncryptException
     */
    protected function getPDFLayers(): string
    {
        if ($this->pdflayer === [] || $this->objid['catalog'] === 0) {
            return '';
        }

        $oid = $this->objid['catalog'];
        $lyrobjs = '';
        $lyrobjs_off = '';
        $lyrobjs_lock = '';
        foreach ($this->pdflayer as $layer) {
            $layer_obj_ref = ' ' . $layer['objid'] . ' 0 R';
            $lyrobjs .= $layer_obj_ref;
            if (!$layer['view']) {
                $lyrobjs_off .= $layer_obj_ref;
            }

            if ($layer['lock']) {
                $lyrobjs_lock .= $layer_obj_ref;
            }
        }

        return (
            ' /OCProperties << /OCGs ['
            . $lyrobjs
            . ' ]'
            . ' /D <<'
            . ' /Name '
            . $this->getOutTextString('Layers', $oid, true)
            . ' /Creator '
            . $this->getOutTextString($this->creator, $oid, true)
            . ' /BaseState /ON'
            . ' /OFF ['
            . $lyrobjs_off
            . ']'
            . ' /Locked ['
            . $lyrobjs_lock
            . ']'
            . ' /Intent /View'
            . ' /AS ['
            . ' << /Event /Print /OCGs ['
            . $lyrobjs
            . '] /Category [/Print] >>'
            . ' << /Event /View /OCGs ['
            . $lyrobjs
            . '] /Category [/View] >>'
            . ' ]'
            . ' /Order ['
            . $lyrobjs
            . ']'
            . ' /ListMode /AllPages'
            //.' /RBGroups ['..']'
            //.' /Locked ['..']'
            . ' >>'
            . ' >>'
        );
    }

    /**
     * Returns the PDF Catalog entry.
     *
     * @return string PDF Catalog object.
     *
     * @throws UnicodeException
     * @throws EncryptException
     * @throws FontException
     * @throws PageException
     */
    protected function getOutCatalog(): string
    {
        $oid = ++$this->pon;
        $this->objid['catalog'] = $oid;
        return $this->buildCatalogObject($oid);
    }

    /**
     * Build the Catalog object body for a given (already allocated) object number.
     *
     * Split out of getOutCatalog so the catalog can be re-emitted with the same
     * object number in a post-signing incremental revision (adding the /DSS
     * reference for PAdES B-LT). The method only reads state and is idempotent:
     * re-running it against the same document state yields identical bytes, so
     * emitting a second catalog version is safe.
     *
     * @return string PDF Catalog object.
     *
     * @throws UnicodeException
     * @throws EncryptException
     * @throws FontException
     * @throws PageException
     */
    protected function buildCatalogObject(int $oid): string
    {
        $out =
            $oid
            . ' 0 obj'
            . "\n"
            . '<<'
            . ' /Type /Catalog'
            . ' /Version /'
            . $this->pdfver
            //.' /Extensions <<>>'
            . ' /Pages '
            . $this->objid['pages']
            . ' 0 R';
        //.' /PageLabels ' //...

        $names = '';
        if ($this->pdfa === 0 && !$this->pdfx && $this->pdfuaMode === '' && $this->jstree !== '') {
            $names .= ' /JavaScript ' . $this->jstree;
        }

        if ($this->embeddedfiles !== []) {
            $afnames = [];
            $afobjs = [];
            foreach ($this->embeddedfiles as $efname => $efdata) {
                // The EmbeddedFiles name-tree key must be a plain (PDFDocEncoded)
                // byte string matching the Filespec /F, not UTF-16BE, otherwise
                // readers cannot resolve the embedded file by name.
                $afnames[] = $this->encrypt->escapeDataString($efname, $oid) . ' ' . $efdata['f'] . ' 0 R';
                $afobjs[] = $efdata['f'] . ' 0 R';
            }
            $names .= ' /EmbeddedFiles << /Names [ ' . \implode(' ', $afnames) . ' ] >>';
            // The /AF (Associated Files) array is an entry of the document Catalog
            // dictionary itself, not of the /Names tree (ISO 32000-2, PDF/A-3).
            $out .= ' /AF [ ' . \implode(' ', $afobjs) . ' ]';
        }

        // Only emit the /Names dictionary when it actually has content.
        if ($names !== '') {
            $out .= ' /Names <<' . $names . ' >>';
        }

        if ($this->objid['dests'] !== 0) {
            $out .= ' /Dests ' . $this->objid['dests'] . ' 0 R';
        }

        $objid = $this->objid + ['dss' => 0];
        $dssObjId = $objid['dss'];
        if ($dssObjId !== 0) {
            $out .= ' /DSS ' . $dssObjId . ' 0 R';
        }

        $out .= $this->getOutViewerPref();

        if ($this->display['layout'] !== '') {
            $out .= ' /PageLayout /' . $this->display['layout'];
        }

        if ($this->outlines !== []) {
            $out .= ' /Outlines ' . $this->outlinerootoid . ' 0 R';
            if ($this->display['mode'] === '') {
                $this->display['mode'] = 'UseOutlines';
            }
        }

        if ($this->display['mode'] !== '') {
            $out .= ' /PageMode /' . $this->display['mode'];
        }

        //$out .= ' /Threads []';

        $firstpage = $this->page->getPage(0);
        $fpo = (int) $firstpage['n'];
        if ($this->display['zoom'] === 'fullpage') {
            $out .= ' /OpenAction [' . $fpo . ' 0 R /Fit]';
        } elseif ($this->display['zoom'] === 'fullwidth') {
            $out .= ' /OpenAction [' . $fpo . ' 0 R /FitH null]';
        } elseif ($this->display['zoom'] === 'real') {
            $out .= ' /OpenAction [' . $fpo . ' 0 R /XYZ null null 1]';
        } elseif (!\is_string($this->display['zoom'])) {
            $zoomVal = (float) $this->display['zoom'];
            $out .= \sprintf(' /OpenAction [' . $fpo . ' 0 R /XYZ null null %F]', $zoomVal / 100);
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

        $language = $this->lang['a_meta_language'] ?? ($this->pdfuaMode !== '' ? 'en-US' : '');

        if ($language !== '') {
            $out .= ' /Lang ' . $this->getOutTextString($language, $oid, true);
        }

        //$out .= ' /SpiderInfo <<>>';
        $out .= $this->getOutputIntents();
        //$out .= ' /PieceInfo <<>>';
        $out .= $this->getPDFLayers();

        $isSign = $this->sign;
        $signature = $this->signature;

        // AcroForm
        if (
            $this->objid['form'] !== []
            || $isSign && (int) $signature['cert_type'] >= 0
            || $signature['appearance']['empty'] !== []
        ) {
            $out .= ' /AcroForm <<';
            $objrefs = '';
            $certType = (int) $signature['cert_type'];
            if ($isSign && $certType >= 0) {
                // set reference for signature object
                $objrefs .= $this->objid['signature'] . ' 0 R';
            }

            if ($signature['appearance']['empty'] !== []) {
                foreach ($signature['appearance']['empty'] as $esa) {
                    // set reference for empty signature objects
                    $objrefs .= ' ' . $esa['objid'] . ' 0 R';
                }
            }

            if ($this->objid['form'] !== []) {
                foreach ($this->objid['form'] as $objid) {
                    $objrefs .= ' ' . $objid . ' 0 R';
                }
            }

            // PAdES B-LTA document-timestamp field, added in a post-signing revision.
            if ($this->objid['doctimestamp'] !== 0) {
                $objrefs .= ' ' . $this->objid['doctimestamp'] . ' 0 R';
            }

            $out .= ' /Fields [' . $objrefs . ']';
            // It's better to turn off this value and set the appearance stream for
            // each annotation (/AP) to avoid conflicts with signature fields.
            if ($signature['approval'] !== 'A') {
                $out .= ' /NeedAppearances false';
            }

            if ($isSign && $certType >= 0) {
                if ($certType > 0) {
                    $out .= ' /SigFlags 3';
                } else {
                    $out .= ' /SigFlags 1';
                }
            }

            //$out .= ' /CO ';

            if ($this->annotation_fonts !== []) {
                $out .= ' /DR << /Font <<';
                foreach ($this->annotation_fonts as $fontkey => $fontid) {
                    $fontData = $this->font->getFont($fontkey);
                    $fontObjId = (int) $fontData['n'];
                    $out .= ' /F' . $fontid . ' ' . $fontObjId . ' 0 R';
                }

                $out .= ' >> >>';
            }

            $font = $this->font->getFont('helvetica');
            $fontIndex = (int) $font['i'];
            $out .= ' /DA ' . $this->encrypt->escapeDataString('/F' . $fontIndex . ' 0 Tf 0 g', $oid);
            $out .= ' /Q ' . ($this->rtl ? '2' : '0');
            //$out .= ' /XFA ';
            $out .= ' >>';

            // signatures
            if ($isSign && $certType >= 0 && $signature['approval'] !== 'A') {
                $out .= ' /Perms << ';
                if ($certType > 0) {
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

        $out .= ' >>' . "\n" . 'endobj' . "\n";
        return $out;
    }

    /**
     * Returns the PDF OCG entry.
     *
     * @throws EncryptException
     * @throws UnicodeException
     */
    protected function getOutOCG(): string
    {
        if ($this->pdflayer === []) {
            return '';
        }

        $out = '';
        foreach ($this->pdflayer as $key => $layer) {
            $oid = ++$this->pon;
            $this->pdflayer[$key]['objid'] = $oid;
            $out .= $oid . ' 0 obj' . "\n";

            $out .= '<< /Type /OCG /Name ' . $this->getOutTextString($layer['name'], $oid, true);

            if ($layer['intent'] !== '') {
                $out .= ' /Intent [' . $layer['intent'] . ']';
            }

            $out .= ' /Usage <<';
            $layer += ['print' => false];
            $printState = $this->getOnOff($layer['print']);
            $out .= ' /Print << /PrintState /' . $printState . ' >>';
            $out .= ' /View << /ViewState /' . $this->getOnOff($layer['view']) . ' >>';
            // Other (not-implemented) possible /Usage entries:
            //   CreatorInfo, Language, Export, Zoom, User, PageElement.
            $out .= ' >>'; // close /Usage

            $out .= ' >>' . "\n" . 'endobj' . "\n";
        }

        return $out;
    }

    /**
     * Returns the PDF Annotation code for Apearance Stream XObjects entry.
     *
     * @param float    $width  annotation width
     * @param float    $height annotation height
     * @param string $stream appearance stream
     *
     * @throws EncryptException
     * @throws PdfException
     */
    protected function getOutAPXObjects(float $width = 0, float $height = 0, string $stream = ''): string
    {
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
        $resobj = $this->objid['resdic'];
        return (
            $out
            . ' /BBox [0 0 '
            . $rect
            . ']'
            . ' /Matrix [1 0 0 1 0 0]'
            . ' /Resources '
            . $resobj
            . ' 0 R'
            . ' /Length '
            . \strlen($stream)
            . ' >>'
            . ' stream'
            . "\n"
            . $stream
            . "\n"
            . 'endstream'
            . "\n"
            . 'endobj'
            . "\n"
        );
    }

    /**
     * Flush raw PDF objects queued by the importer (imported Form XObjects and their dependencies).
     * Called from getOutPDFBody() immediately after getOutXObjects().
     */
    protected function getOutImportedObjects(): string
    {
        if ($this->importer === null) {
            return '';
        }

        return $this->importer->getOutImportedObjects();
    }

    /**
     * Returns the PDF XObjects entry.
     *
     * @throws EncryptException
     * @throws PdfException
     */
    protected function getOutXObjects(): string
    {
        $out = '';
        foreach ($this->xobjects as $data) {
            if ($data['outdata'] === '') {
                continue;
            }

            $out .= $data['n'] . ' 0 obj' . "\n" . '<< /Type /XObject /Subtype /Form /FormType 1';
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
                $this->toPoints($data['w'] + $data['x']),
                $this->toPoints($data['h'] - $data['y']),
            );

            $out .= ' /Matrix [1 0 0 1 0 0] /Resources << /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]';

            $out .= $this->graph->getOutExtGStateResourcesByKeys($data['extgstate']);
            $out .= $this->graph->getOutGradientResourcesByKeys($data['gradient']);
            $out .= $this->color->getPdfSpotResourcesByKeys($data['spot_colors']);
            $out .= $this->outfont->getOutFontDictByKeys($data['font']);

            if ($data['image'] !== [] || $data['xobject'] !== []) {
                $out .= ' /XObject <<';
                $out .= $this->image->getXobjectDictByKeys($data['image']);
                if ($data['xobject'] !== []) {
                    foreach ($data['xobject'] as $xid) {
                        $xref = $this->xobjects[$xid] ?? null;
                        if ($xref === null) {
                            continue;
                        }

                        $out .= ' /' . $xid . ' ' . $xref['n'] . ' 0 R';
                    }
                }
                $out .= ' >>';
            }

            $out .= ' >>'; // end of /Resources.

            if (($data['transparency'] ?? null) !== null && $this->isTransparencyAllowed()) {
                // set transparency group
                $out .= ' /Group << /Type /Group /S /Transparency';
                $out .= ' /CS /' . $data['transparency']['CS'];
                $out .= ' /I /' . ($data['transparency']['I'] ? 'true' : 'false');
                $out .= ' /K /' . ($data['transparency']['K'] ? 'true' : 'false');
                $out .= ' >>';
            }

            $stream = $this->encrypt->encryptString($stream, $data['n']);
            $out .=
                ' /Length '
                . \strlen($stream)
                . ' >>'
                . ' stream'
                . "\n"
                . $stream
                . "\n"
                . 'endstream'
                . "\n"
                . 'endobj'
                . "\n";
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
        $gsResources = $this->graph->getOutExtGStateResources();
        $maskGsEntries = $this->getSVGMaskExtGStateEntries();
        if ($maskGsEntries !== '') {
            if ($gsResources === '') {
                $gsResources = ' /ExtGState <<' . $maskGsEntries . ' >>' . "\n";
            } else {
                // Strip closing ' >>\n' and re-append with mask entries.
                $gsResources = \substr(\rtrim($gsResources), 0, -2) . $maskGsEntries . ' >>' . "\n";
            }
        }

        return (
            $this->objid['resdic']
            . ' 0 obj'
            . "\n"
            . '<<'
            . ' /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]'
            . $this->outfont->getOutFontDict()
            . $this->getXObjectDict()
            . $this->getPatternDict()
            . $this->getLayerDict()
            . $gsResources
            . $this->graph->getOutGradientResources()
            . $this->color->getPdfSpotResources()
            . ' >>'
            . "\n"
            . 'endobj'
            . "\n"
        );
    }

    /**
     * Returns the PDF Pattern objects entry.
     *
     * @throws EncryptException
     * @throws PdfException
     */
    protected function getOutPatterns(): string
    {
        $out = '';
        foreach ($this->patterns as $pid => $data) {
            if ($data['outdata'] === '') {
                continue;
            }

            if (!isset($this->patterns[$pid]['n']) || $this->patterns[$pid]['n'] === 0) {
                $this->patterns[$pid]['n'] = ++$this->pon;
            }

            $oid = $this->patterns[$pid]['n'];
            $stream = \trim($data['outdata']);
            $out .= $oid . ' 0 obj' . "\n";
            $out .= \sprintf(
                '<< /Type /Pattern /PatternType 1 /PaintType 1 /TilingType 1'
                . ' /BBox [%F %F %F %F] /XStep %F /YStep %F /Matrix [%F %F %F %F %F %F]',
                $data['bbox'][0],
                $data['bbox'][1],
                $data['bbox'][2],
                $data['bbox'][3],
                $data['xstep'],
                $data['ystep'],
                $data['matrix'][0],
                $data['matrix'][1],
                $data['matrix'][2],
                $data['matrix'][3],
                $data['matrix'][4],
                $data['matrix'][5],
            );
            $res = $this->getPatternStreamResourceDict($stream);
            $out .= ' /Resources << /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]' . $res . ' >>';

            if ($this->compress) {
                $stream = \gzcompress($stream);
                if ($stream === false) {
                    throw new PdfException('Unable to compress stream');
                }
                $out .= ' /Filter /FlateDecode';
            }

            $stream = $this->encrypt->encryptString($stream, $oid);
            $out .=
                ' /Length '
                . \strlen($stream)
                . ' >>'
                . ' stream'
                . "\n"
                . $stream
                . "\n"
                . 'endstream'
                . "\n"
                . 'endobj'
                . "\n";
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
     *
     * @throws EncryptException
     */
    protected function getOutSVGMasks(): string
    {
        $out = '';
        foreach ($this->svgmasks as $key => $data) {
            if ($data['stream'] === '') {
                continue;
            }

            $stream = $data['stream'];
            $bbox = $data['bbox'];
            $bboxStr = \sprintf('%F %F %F %F', $bbox[0], $bbox[1], $bbox[2], $bbox[3]);

            // Form XObject (DeviceGray transparency group for luminosity mask).
            $formOid = ++$this->pon;
            $formStream = $stream;
            $formHead =
                $formOid
                . ' 0 obj'
                . "\n"
                . '<<'
                . ' /Type /XObject'
                . ' /Subtype /Form'
                . ' /FormType 1'
                . ' /BBox ['
                . $bboxStr
                . ']'
                . ' /Group << /Type /Group /S /Transparency /CS /DeviceGray /I true >>';
            if ($this->compress) {
                $comp = \gzcompress($formStream);
                if ($comp !== false) {
                    $formStream = $comp;
                    $formHead .= ' /Filter /FlateDecode';
                }
            }

            $formStream = $this->encrypt->encryptString($formStream, $formOid);
            $out .=
                $formHead
                . ' /Length '
                . \strlen($formStream)
                . ' >>'
                . "\n"
                . 'stream'
                . "\n"
                . $formStream
                . "\n"
                . 'endstream'
                . "\n"
                . 'endobj'
                . "\n";

            // SMask dictionary.
            $smaskOid = ++$this->pon;
            $out .=
                $smaskOid
                . ' 0 obj'
                . "\n"
                . '<< /Type /Mask /S /Luminosity /G '
                . $formOid
                . ' 0 R >>'
                . "\n"
                . 'endobj'
                . "\n";

            // ExtGState with SMask reference.
            $gsOid = ++$this->pon;
            $out .=
                $gsOid
                . ' 0 obj'
                . "\n"
                . '<< /Type /ExtGState /SMask '
                . $smaskOid
                . ' 0 R /AIS false >>'
                . "\n"
                . 'endobj'
                . "\n";

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
            if ($mask['gs_n'] <= 0) {
                continue;
            }

            $out .= ' /' . $key . ' ' . $mask['gs_n'] . ' 0 R';
        }

        return $out;
    }

    /**
     * Build a minimized resources dictionary fragment for a pattern stream.
     */
    protected function getPatternStreamResourceDict(string $stream): string
    {
        $fontNames = [];
        $matchFont = [];
        $fontMatchCount = \preg_match_all('/\/(F[0-9]+)\s+[0-9\.\-]+\s+Tf\b/', $stream, $matchFont);
        if ($fontMatchCount !== false && $fontMatchCount > 0) {
            foreach ($matchFont[1] ?? [] as $name) {
                $fontNames[$name] = true;
            }
        }

        $extgstate = [];
        $matchGs = [];
        $gsMatchCount = \preg_match_all('/\/GS([0-9]+)\s+gs\b/', $stream, $matchGs);
        if ($gsMatchCount !== false && $gsMatchCount > 0) {
            foreach ($matchGs[1] ?? [] as $idx) {
                $extgstate[(int) $idx] = true;
            }
        }

        $gradient = [];
        $matchSh = [];
        $shMatchCount = \preg_match_all('/\/Sh([0-9]+)\s+sh\b/', $stream, $matchSh);
        if ($shMatchCount !== false && $shMatchCount > 0) {
            foreach ($matchSh[1] ?? [] as $idx) {
                $gradient[(int) $idx] = true;
            }
        }

        $spotNames = [];
        $matchCs = [];
        $csMatchCount = \preg_match_all('/\/(CS[0-9]+)\s+[cC]s\b/', $stream, $matchCs);
        if ($csMatchCount !== false && $csMatchCount > 0) {
            foreach ($matchCs[1] ?? [] as $name) {
                $spotNames[$name] = true;
            }
        }

        $imageKeys = [];
        $xobjectKeys = [];
        $matchDo = [];
        $doMatchCount = \preg_match_all('/\/([A-Za-z0-9_]+)\s+Do\b/', $stream, $matchDo);
        if ($doMatchCount !== false && $doMatchCount > 0) {
            foreach ($matchDo[1] ?? [] as $key) {
                $imgm = [];
                if (\preg_match('/^I([0-9]+)$/', $key, $imgm) === 1) {
                    $imageKeys[(int) ($imgm[1] ?? '0')] = true;
                    continue;
                }
                if (isset($this->xobjects[$key])) {
                    $xobjectKeys[$key] = true;
                }
            }
        }

        $out = '';
        if ($fontNames !== []) {
            $fontEntries = $this->extractNamedResourceRefs($this->outfont->getOutFontDict(), \array_keys($fontNames));
            if ($fontEntries !== '') {
                $out .= ' /Font <<' . $fontEntries . ' >>';
            }
        }
        if ($extgstate !== []) {
            $out .= $this->graph->getOutExtGStateResourcesByKeys(\array_map('intval', \array_keys($extgstate)));
        }
        if ($gradient !== []) {
            $out .= $this->graph->getOutGradientResourcesByKeys(\array_map('intval', \array_keys($gradient)));
        }
        if ($spotNames !== []) {
            $spotEntries = $this->extractNamedResourceRefs(
                $this->color->getPdfSpotResources(),
                \array_keys($spotNames),
            );
            if ($spotEntries !== '') {
                $out .= ' /ColorSpace <<' . $spotEntries . ' >>';
            }
        }
        if ($imageKeys !== [] || $xobjectKeys !== []) {
            $out .= ' /XObject <<';
            if ($imageKeys !== []) {
                $out .= $this->image->getXobjectDictByKeys(\array_map('intval', \array_keys($imageKeys)));
            }
            if ($xobjectKeys !== []) {
                foreach (\array_keys($xobjectKeys) as $xid) {
                    $xobjn = $this->xobjects[$xid]['n'] ?? 0;
                    $out .= ' /' . $xid . ' ' . (int) $xobjn . ' 0 R';
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
        if ($dict === '' || $names === []) {
            return '';
        }

        $out = '';
        foreach ($names as $name) {
            if ($name === '') {
                continue;
            }

            $match = [];
            $ok = \preg_match('/\/' . \preg_quote($name, '/') . '\s+([0-9]+)\s+0\s+R\b/', $dict, $match);
            if ($ok === 1 && isset($match[1])) {
                $out .= ' /' . $name . ' ' . (int) $match[1] . ' 0 R';
            }
        }

        return $out;
    }

    /**
     * Returns the PDF Destinations entry.
     *
     * @throws PageException
     */
    protected function getOutDestinations(): string
    {
        if ($this->dests === []) {
            return '';
        }

        $oid = ++$this->pon;
        $this->objid['dests'] = $oid;
        $out = $oid . ' 0 obj' . "\n" . '<< ';
        foreach ($this->dests as $name => $dst) {
            $page = $this->page->getPage($dst['p']);
            $poid = (int) $page['n'];
            $pheight = $page['pheight'];
            $pgx = $this->toPoints($dst['x']);
            $pgy = $this->toYPoints($dst['y'], $pheight);
            $out .= \sprintf(' /%s [%u 0 R /XYZ %F %F null]', $name, $poid, $pgx, $pgy);
        }

        return $out . ' >>' . "\n" . 'endobj' . "\n";
    }

    /**
     * Returns the PDF Embedded Files entry.
     *
     * @return string Embedded files PDF objects.
     *
     * @throws PdfException
     * @throws UnicodeException
     * @throws EncryptException
     * @throws FileException
     */
    protected function getOutEmbeddedFiles(): string
    {
        if ($this->pdfa === 1 || $this->pdfa === 2) {
            // embedded files are not allowed in PDF/A mode version 1 and 2
            return '';
        }

        $out = '';
        \reset($this->embeddedfiles);
        foreach ($this->embeddedfiles as $name => $data) {
            if ($data['content'] !== '') {
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
            $out .=
                $oid
                . ' 0 obj'
                . "\n"
                . '<<'
                // /F is the PDFDocEncoded (ASCII) file name; it must NOT be
                // UTF-16BE encoded or readers (and ZUGFeRD/Factur-X validators)
                // fail to match the embedded file. /UF carries the Unicode name
                // as UTF-16BE WITH the mandatory byte order mark.
                . ' /Type /Filespec /F '
                . $this->encrypt->escapeDataString($name, $oid)
                . ' /UF '
                . $this->getOutTextString($name, $oid, true)
                . ' /AFRelationship /'
                . $data['afRelationship']
                . ' /Desc '
                . $this->getOutTextString($data['description'], $data['f'], true)
                . ' /EF <</F '
                . $data['n']
                . ' 0 R>>'
                . ' >>'
                . "\n"
                . 'endobj'
                . "\n";

            // embedded file object
            $filter = '';
            if ($this->pdfa === 3) {
                $filter = ' /Subtype /' . \str_replace(['/', '+'], ['#2F', '#2B'], $data['mimeType']);
            } elseif ($this->compress) {
                $content = \gzcompress($content);
                if ($content === false) {
                    throw new PdfException('Unable to compress content');
                }
                $filter = ' /Filter /FlateDecode';
            }

            $stream = $this->encrypt->encryptString($content, $data['n']);
            $out .=
                "\n"
                . $data['n']
                . ' 0 obj'
                . "\n"
                . '<<'
                . ' /Type /EmbeddedFile'
                . $filter
                . ' /Length '
                . \strlen($stream)
                . ' /Params <<'
                . ' /Size '
                . $rawsize
                . ' /CreationDate '
                . $this->getOutDateTimeString($data['creationDate'], $data['n'])
                . ' /ModDate '
                . $this->getOutDateTimeString($data['modDate'], $data['n'])
                . ' >>'
                . ' >>'
                . ' stream'
                . "\n"
                . $stream
                . "\n"
                . 'endstream'
                . "\n"
                . 'endobj'
                . "\n";
        }

        return $out;
    }

    /**
     * Returns the PDF StructTreeRoot entry for tagged PDF modes.
     *
     * @throws EncryptException
     * @throws UnicodeException
     */
    protected function getOutStructTreeRoot(): string
    {
        if ($this->pdfuaMode === '') {
            $this->pagestructparents = [];
            $this->pagestructmcids = [];
            $this->annotstructparents = [];
            $this->parenttreeoid = 0;
            $this->structtreerootoid = 0;
            return '';
        }

        $structLog = $this->pdfuaStructLog;
        if ($structLog === []) {
            $this->annotstructparents = [];
            $this->parenttreeoid = 0;
            $this->structtreerootoid = 0;
            return '';
        }

        // Build pid -> page OID map from serialized page data.
        $pidToOid = [];
        foreach ($this->page->getPages() as $page) {
            $page += ['n' => 0];
            $pid = (int) $page['pid'];
            $pageObjN = (int) $page['n'];
            if ($pid < 0 || $pageObjN < 1) {
                continue;
            }

            $pidToOid[$pid] = $pageObjN;
        }

        $parentTreeOid = ++$this->pon;
        $structTreeRootOid = $parentTreeOid + 1;
        $documentStructElemOid = $parentTreeOid + 2;
        $namespaceOid = 0;
        if ($this->pdfuaMode === 'pdfua2') {
            $namespaceOid = $parentTreeOid + 3;
        }

        // Assign OIDs to each struct elem entry.
        // $elemOids[i] = OID for $structLog[i]
        $elemOids = [];
        $nextOid = $parentTreeOid + ($namespaceOid > 0 ? 4 : 3);
        foreach ($structLog as $idx => $_entry) {
            $elemOids[$idx] = $nextOid++;
        }

        $this->pon = $nextOid - 1;
        $this->parenttreeoid = $parentTreeOid;
        $this->structtreerootoid = $structTreeRootOid;

        $childEntryIdx = [];
        foreach ($structLog as $entry) {
            foreach ($entry['kids'] as $kid) {
                if ($kid['type'] !== 'elem') {
                    continue;
                }

                $childEntryIdx[$kid['id']] = true;
            }
        }

        $rootEntryIdx = [];
        foreach ($structLog as $idx => $_entry) {
            if (isset($childEntryIdx[$idx])) {
                continue;
            }

            $rootEntryIdx[] = $idx;
        }

        // Document StructElem.
        $firstPageOid = $this->pagestructparents === [] ? 0 : (int) \array_key_first($this->pagestructparents);
        if ($rootEntryIdx !== []) {
            $firstTaggedPageOid = $pidToOid[$structLog[$rootEntryIdx[0]]['pid']] ?? 0;
            if ($firstTaggedPageOid > 0) {
                $firstPageOid = $firstTaggedPageOid;
            }
        }

        $rootElemRefsStr = \implode(' ', \array_map(
            static fn(int $idx): string => ($elemOids[$idx] ?? 0) . ' 0 R',
            $rootEntryIdx,
        ));
        $documentStructElem = '<< /Type /StructElem /S /Document /P ' . $structTreeRootOid . ' 0 R';
        if ($firstPageOid > 0) {
            $documentStructElem .= ' /Pg ' . $firstPageOid . ' 0 R';
        }

        if ($namespaceOid > 0) {
            $documentStructElem .= ' /NS ' . $namespaceOid . ' 0 R';
        }

        $documentStructElem .= ' /K [ ' . $rootElemRefsStr . ' ] >>';

        // Nested StructElems and ParentTree map.
        $parentTreeMap = [];
        $annotParentMap = [];
        $structElemsOut = '';
        $entryParentOid = [];
        $entryOrder = [];
        $entryStack = [];
        $parentStack = [];
        foreach (\array_reverse($rootEntryIdx) as $rootIdx) {
            $entryStack[] = $rootIdx;
            $parentStack[] = $documentStructElemOid;
        }

        while ($entryStack !== []) {
            $entryIdx = \array_pop($entryStack);
            $parentOid = \array_pop($parentStack);
            if (isset($entryParentOid[$entryIdx])) {
                continue;
            }

            $entryParentOid[$entryIdx] = $parentOid;
            $entryOrder[] = $entryIdx;
            $entry = $structLog[$entryIdx];
            $entryOid = $elemOids[$entryIdx] ?? 0;
            $childElemIdx = [];
            foreach ($entry['kids'] as $kid) {
                if ($kid['type'] !== 'elem') {
                    continue;
                }

                $childElemIdx[] = $kid['id'];
            }

            foreach (\array_reverse($childElemIdx) as $kidIdx) {
                $entryStack[] = $kidIdx;
                $parentStack[] = $entryOid;
            }
        }

        foreach ($entryOrder as $entryIdx) {
            $entry = $structLog[$entryIdx];
            $entry += ['annots' => [], 'alt' => '', 'attr' => []];
            $entryPageOid = $pidToOid[$entry['pid']] ?? 0;
            $kidsOut = '';
            foreach ($entry['kids'] as $kid) {
                if ($kid['type'] === 'elem') {
                    $childIdx = $kid['id'];
                    $kidsOut .= ' ' . ($elemOids[$childIdx] ?? 0) . ' 0 R';
                    continue;
                }

                $mcid = $kid['id'];
                // Each MCID belongs to the page it was emitted on, which may differ from
                // the element's own page when the element's content wraps across a page
                // break. Use the kid's page so the MCR /Pg and ParentTree key are correct.
                $kidPageOid = $pidToOid[$kid['pid']] ?? $entryPageOid;
                if ($kidPageOid > 0) {
                    $parentTreeMap[$kidPageOid][$mcid] = $elemOids[$entryIdx] ?? 0;
                }

                $kidsOut .= ' << /Type /MCR /Pg ' . $kidPageOid . ' 0 R /MCID ' . $mcid . ' >>';
            }

            if ($entry['annots'] !== []) {
                foreach ($entry['annots'] as $annotOid) {
                    if ($annotOid <= 0) {
                        continue;
                    }

                    $annotParentMap[$annotOid] = $elemOids[$entryIdx] ?? 0;
                    $kidsOut .= ' << /Type /OBJR /Pg ' . $entryPageOid . ' 0 R /Obj ' . $annotOid . ' 0 R >>';
                }
            }

            $altOut = '';
            if ($entry['alt'] !== '') {
                $altOut = ' /Alt ' . $this->getOutTextString($entry['alt'], $elemOids[$entryIdx] ?? 0, true);
            }

            $attrOut = '';
            $idOut = '';
            if ($entry['attr'] !== []) {
                $attrPairs = '';
                foreach ($entry['attr'] as $akey => $aval) {
                    if ($akey === '' || $aval === '') {
                        continue;
                    }

                    if ($akey === 'ID') {
                        $idOut = ' /ID ' . $this->getOutTextString($aval, $elemOids[$entryIdx] ?? 0, false);
                        continue;
                    }

                    if ($akey === 'Headers') {
                        $headerIds = \preg_split('/[\s,]+/', \trim($aval));
                        if ($headerIds === false) {
                            continue;
                        }

                        $headerOut = '';
                        foreach ($headerIds as $headerId) {
                            if ($headerId === '') {
                                continue;
                            }

                            $headerOut .= ' ' . $this->getOutTextString($headerId, $elemOids[$entryIdx] ?? 0, false);
                        }

                        if ($headerOut !== '') {
                            $attrPairs .= ' /Headers [' . $headerOut . ' ]';
                        }
                        continue;
                    }

                    if ($akey === 'ColSpan' || $akey === 'RowSpan') {
                        // Table span attributes are integers, not names (ISO 32000-1 table 348).
                        if (\is_numeric($aval)) {
                            $attrPairs .= ' /' . $akey . ' ' . (int) $aval;
                        }

                        continue;
                    }

                    $attrPairs .= ' /' . $akey . ' /' . $aval;
                }

                if ($attrPairs !== '') {
                    $attrOut = ' /A <<' . $attrPairs . ' >>';
                }
            }

            $parentOid = $entryParentOid[$entryIdx] ?? $documentStructElemOid;
            $structElemsOut .=
                ($elemOids[$entryIdx] ?? 0)
                . ' 0 obj'
                . "\n"
                . '<< /Type /StructElem /S /'
                . $entry['role']
                . ' /P '
                . $parentOid
                . ' 0 R'
                . ' /Pg '
                . $entryPageOid
                . ' 0 R'
                . $altOut
                . $idOut
                . $attrOut
                . ' /K ['
                . $kidsOut
                . ' ] >>'
                . "\n"
                . 'endobj'
                . "\n";
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

        $this->annotstructparents = [];
        $nextParentKey = \count($this->pagestructparents);
        if ($annotParentMap !== []) {
            \ksort($annotParentMap);
            foreach ($annotParentMap as $annotOid => $structElemOid) {
                $this->annotstructparents[$annotOid] = $nextParentKey;
                $parentNums .= ' ' . $nextParentKey . ' ' . $structElemOid . ' 0 R';
                ++$nextParentKey;
            }
        }

        $out = $parentTreeOid . ' 0 obj' . "\n" . '<< /Nums [' . $parentNums . ' ] >>' . "\n" . 'endobj' . "\n";

        $namespaceOut = '';
        if ($namespaceOid > 0) {
            $namespaceOut =
                $namespaceOid
                . ' 0 obj'
                . "\n"
                . '<< /Type /Namespace /NS '
                . $this->getOutTextString('http://iso.org/pdf2/ssn', $namespaceOid, true)
                . ' >>'
                . "\n"
                . 'endobj'
                . "\n";
        }

        return (
            $out
            . $structTreeRootOid
            . ' 0 obj'
            . "\n"
            . '<< /Type /StructTreeRoot /ParentTree '
            . $parentTreeOid
            . ' 0 R /ParentTreeNextKey '
            . $nextParentKey
            . ' /K [ '
            . $documentStructElemOid
            . ' 0 R ]'
            . ($namespaceOid > 0 ? ' /Namespaces [ ' . $namespaceOid . ' 0 R ]' : '')
            . ' >>'
            . "\n"
            . 'endobj'
            . "\n"
            . $documentStructElemOid
            . ' 0 obj'
            . "\n"
            . $documentStructElem
            . "\n"
            . 'endobj'
            . "\n"
            . $namespaceOut
            . $structElemsOut
        );
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
            $page += ['n' => 0, 'annotrefs' => []];
            $pageObjN = (int) $page['n'];
            if ($pageObjN < 1) {
                continue;
            }

            $tabs = '';
            $annotRefs = $page['annotrefs'];
            if ($annotRefs !== []) {
                $tabs = '/Tabs /S' . "\n";
            }

            $needle = $pageObjN . ' 0 obj' . "\n<<\n/Type /Page\n";
            $replacement = $pageObjN . ' 0 obj' . "\n<<\n/Type /Page\n/StructParents " . $parentKey . "\n" . $tabs;
            $pdfpages = \str_replace($needle, $replacement, $pdfpages);
            $this->pagestructparents[$pageObjN] = $parentKey;
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
                (float) ($col[0] ?? 0.0),
                (float) ($col[1] ?? 0.0),
                (float) ($col[2] ?? 0.0),
                (float) ($col[3] ?? 0.0),
            ),
            3 => \sprintf('%F %F %F', (float) ($col[0] ?? 0.0), (float) ($col[1] ?? 0.0), (float) ($col[2] ?? 0.0)),
            1 => \sprintf('%F', (float) ($col[0] ?? 0.0)),
            default => '',
        };
        return $out . ']';
    }

    /**
     * Returns the PDF Annotations entry.
     *
     * @throws EncryptException
     * @throws PdfException
     * @throws PageException
     * @throws UnicodeException
     * @throws \Throwable
     */
    protected function getOutAnnotations(): string
    {
        $out = '';
        $pages = $this->page->getPages();
        foreach ($pages as $num => $page) {
            $page += ['annotrefs' => [], 'pheight' => 0.0, 'n' => 0, 'num' => $num];
            $annotRefs = $page['annotrefs'];

            $pageHeight = $page['pheight'];
            $pageObjN = (int) $page['n'];
            $pageNum = (int) $page['num'];
            foreach ($annotRefs as $key => $oid) {
                $rawAnnot = $this->annotation[$oid] ?? null;
                if (!\is_array($rawAnnot)) {
                    continue;
                }

                $rawAnnot += ['opt' => []];
                $rawOpt = $rawAnnot['opt'];
                $rawTxt = $rawAnnot['txt'];
                $rawN = $rawAnnot['n'];
                $rawX = $rawAnnot['x'];
                $rawY = $rawAnnot['y'];
                $rawW = $rawAnnot['w'];
                $rawH = $rawAnnot['h'];

                $opt = \array_change_key_case($rawOpt, CASE_LOWER);
                if (!isset($opt['subtype']) || !\is_string($opt['subtype']) || $opt['subtype'] === '') {
                    continue;
                }
                $subtype = $opt['subtype'];
                /** @var array<string, mixed> $annotOpt */
                $annotOpt = $opt;

                /** @var TAnnot $annot */
                $annot = [
                    'h' => $rawH,
                    'n' => (int) $rawN,
                    'opt' => $opt,
                    'txt' => $rawTxt,
                    'w' => $rawW,
                    'x' => $rawX,
                    'y' => $rawY,
                ];

                $out .= $this->getAnnotationRadioButtons($annot);
                $orx = $this->toPoints($annot['x']);
                $ory = $this->toYPoints($annot['y'] + $annot['h'], $pageHeight);
                $width = $this->toPoints($annot['w']);
                $height = $this->toPoints($annot['h']);
                $rect = \sprintf('%F %F %F %F', $orx, $ory, $orx + $width, $ory + $height);
                $out .=
                    (int) $oid
                    . ' 0 obj'
                    . "\n"
                    . '<<'
                    . ' /Type /Annot'
                    . ' /Subtype /'
                    . $subtype
                    . ' /Rect ['
                    . $rect
                    . ']';
                $ft = ['Btn', 'Tx', 'Ch', 'Sig'];
                $formfield =
                    isset($annotOpt['ft']) && \is_string($annotOpt['ft']) && \in_array($annotOpt['ft'], $ft, true);
                if ($formfield) {
                    $out .= ' /FT /' . $annotOpt['ft'];
                }

                if ($subtype !== 'Link' || $this->pdfuaMode !== '') {
                    $out .= ' /Contents ' . $this->getOutTextString($annot['txt'], $oid, true);
                }

                list($aas, $apx) = $this->getAnnotationAppearanceStream(['opt' => $annotOpt], $width, $height);

                $out .=
                    ' /P '
                    . $pageObjN
                    . ' 0 R'
                    . ' /NM '
                    . $this->encrypt->escapeDataString(\sprintf('%04u-%04u', $pageNum, (int) $key), $oid)
                    . ' /M '
                    . $this->getOutDateTimeString($this->docmodtime, $oid)
                    . $this->getOutAnnotationFlags($annot)
                    . $aas
                    . $this->getAnnotationBorder($annot);

                if (isset($annotOpt['c']) && \is_string($annotOpt['c']) && $annotOpt['c'] !== '') {
                    $out .= ' /C [ ' . $this->color->getPdfRgbComponents($annotOpt['c']) . ' ]';
                }

                if ($this->pdfuaMode !== '' && isset($this->annotstructparents[(int) $oid])) {
                    $oidKey = (int) $oid;
                    $out .= ' /StructParent ' . ($this->annotstructparents[$oidKey] ?? 0);
                }

                //$out .= ' /OC ';

                $out .=
                    $this->getOutAnnotationMarkups($annot, $oid, $annotOpt)
                    . $this->getOutAnnotationOptSubtype($annot, (int) $num, $oid, (int) $key, $annotOpt)
                    . ' >>'
                    . "\n"
                    . 'endobj'
                    . "\n"
                    . $apx;
                if (!$formfield) {
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
     *
     * @throws EncryptException
     */
    protected function getAnnotationRadioButtons(array $annot): string
    {
        $out = '';
        if (!isset($this->radiobuttons[$annot['txt']]['kids']) || $this->radiobuttons[$annot['txt']]['kids'] === []) {
            return $out;
        }

        $oid = $this->radiobuttons[$annot['txt']]['n'];
        $out = $oid . ' 0 obj' . "\n" . '<< /Type /Annot /Subtype /Widget /Rect [0 0 0 0]';
        if ($this->radiobuttons[$annot['txt']]['#readonly#']) {
            // read only
            $out .= ' /F 68 /Ff 49153';
        } else {
            $out .= ' /F 4 /Ff 49152'; // default print for PDF/A
        }

        $out .= ' /T ' . $this->encrypt->escapeDataString($annot['txt'], $oid);
        if (isset($annot['opt']['tu']) && $annot['opt']['tu'] !== '') {
            $out .= ' /TU ' . $this->encrypt->escapeDataString($annot['opt']['tu'], $oid);
        }

        $out .= ' /FT /Btn /Kids [';
        $defval = '';
        $defval = '';
        foreach ($this->radiobuttons[$annot['txt']]['kids'] as $kids) {
            $out .= ' ' . $kids['n'] . ' 0 R';
            if ($kids['def'] !== 'Off') {
                $defval = $kids['def'];
            }
        }

        $out .= ' ]';
        if ($defval !== '') {
            $out .= ' /V /' . $defval;
        }

        $out .= ' >>' . "\n" . 'endobj' . "\n";
        $this->objid['form'][] = $oid;
        $this->radiobuttons[$annot['txt']]['kids'] = []; // set only once
        return $out;
    }

    /**
     * Returns the Annotation code for Appearance Stream.
     *
     * @param array{opt: array<string, mixed>} $annot  Array containing page annotation options.
     * @param float    $width  Annotation width.
     * @param float    $height Annotation height.
     *
     * @return array{string, string}
     *
     * @throws EncryptException
     * @throws PdfException
     */
    protected function getAnnotationAppearanceStream(array $annot, float $width = 0, float $height = 0): array
    {
        $out = '';
        if (isset($annot['opt']['as']) && \is_string($annot['opt']['as']) && $annot['opt']['as'] !== '') {
            $out .= ' /AS /' . $annot['opt']['as'];
        }

        if (!isset($annot['opt']['ap']) || $annot['opt']['ap'] === '') {
            return [$out, ''];
        }

        $apxout = '';
        $out .= ' /AP <<';
        if (!\is_array($annot['opt']['ap'])) {
            $out .= (string) $annot['opt']['ap'];
        } else {
            foreach (\array_keys($annot['opt']['ap']) as $mode) {
                if (!\is_string($mode)) {
                    continue;
                }

                // $mode can be: n = normal; r = rollover; d = down;
                $out .= ' /' . \strtoupper($mode);
                if (\is_array($annot['opt']['ap'][$mode] ?? null)) {
                    $out .= ' <<';
                    foreach (\array_keys($annot['opt']['ap'][$mode]) as $apstate) {
                        if (!\is_string($apstate)) {
                            continue;
                        }

                        if (!\is_string($annot['opt']['ap'][$mode][$apstate] ?? null)) {
                            continue;
                        }
                        // reference to XObject that define the appearance for this mode-state
                        $apxout .= $this->getOutAPXObjects($width, $height, $annot['opt']['ap'][$mode][$apstate]);
                        $out .= ' /' . $apstate . ' ' . $this->pon . ' 0 R';
                    }

                    $out .= ' >>';
                } else {
                    if (!\is_string($annot['opt']['ap'][$mode] ?? null)) {
                        continue;
                    }

                    // reference to XObject that define the appearance for this mode
                    $apxout .= $this->getOutAPXObjects($width, $height, $annot['opt']['ap'][$mode]);
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
        if (isset($annot['opt']['bs']) && $annot['opt']['bs'] !== []) {
            $out .= ' /BS << /Type /Border';
            $out .= ' /W ' . (int) $annot['opt']['bs']['w'];

            $bstyles = ['S', 'D', 'B', 'I', 'U'];
            if ($annot['opt']['bs']['s'] !== '' && \in_array($annot['opt']['bs']['s'], $bstyles, true)) {
                $out .= ' /S /' . $annot['opt']['bs']['s'];
            }

            if (isset($annot['opt']['bs']['d'])) {
                $out .= ' /D [';
                foreach ($annot['opt']['bs']['d'] as $cord) {
                    $out .= ' ' . (int) $cord;
                }

                $out .= ']';
            }

            $out .= ' >>';
        } else {
            $out .= ' /Border [';
            $border = \is_array($annot['opt']['border'] ?? null) ? $annot['opt']['border'] : null;
            if (
                $border !== null
                && isset($border[0], $border[1], $border[2])
                && \is_numeric($border[0])
                && \is_numeric($border[1])
                && \is_numeric($border[2])
            ) {
                $out .= (int) $border[0] . ' ' . (int) $border[1] . ' ' . (int) $border[2];
                if (isset($border[3]) && \is_array($border[3])) {
                    $out .= ' [';
                    foreach (\array_keys($border[3]) as $dashkey) {
                        if (!\is_numeric($border[3][$dashkey] ?? null)) {
                            continue;
                        }

                        $out .= ' ' . (int) $border[3][$dashkey];
                    }

                    $out .= ' ]';
                }
            } else {
                $out .= '0 0 0';
            }

            $out .= ']';
        }

        if (isset($annot['opt']['be'])) {
            $be = $annot['opt']['be'];
            $out .= ' /BE <<';
            $bstyles = ['S', 'C'];
            $beStyle = \is_string($be['s'] ?? null) ? $be['s'] : '';
            if ($beStyle !== '' && \in_array($beStyle, $bstyles, true)) {
                $out .= ' /S /' . $beStyle;
            } else {
                $out .= ' /S /S';
            }

            $beIntensity = $be['i'] ?? null;
            if (\is_numeric($beIntensity) && $beIntensity >= 0 && $beIntensity <= 2) {
                $out .= \sprintf(' /I  %F', $beIntensity);
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
     * @param array<string, mixed> $rawOpt
     *
     * @throws EncryptException
     * @throws UnicodeException
     */
    protected function getOutAnnotationMarkups(array $annot, int $oid, array $rawOpt = []): string
    {
        $out = '';
        $rawOpt = $this->getAnnotationRawOptions($annot, $rawOpt);
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
        if ($annot['opt']['subtype'] === '' || !\in_array(\strtolower($annot['opt']['subtype']), $markups, true)) {
            return $out;
        }

        if (isset($annot['opt']['t']) && $annot['opt']['t'] !== '') {
            $out .= ' /T ' . $this->getOutTextString($annot['opt']['t'], $oid, true);
        }

        //$out .= ' /Popup ';
        if (isset($rawOpt['ca']) && \is_numeric($rawOpt['ca'])) {
            $out .= \sprintf(' /CA %F', (float) $rawOpt['ca']);
        }

        $rc = $annot['opt']['rc'] ?? null;
        if (\is_string($rc) && $rc !== '') {
            $out .= ' /RC ' . $this->getOutTextString($rc, $oid, true);
        }

        $out .= ' /CreationDate ' . $this->getOutDateTimeString($this->doctime, $oid);
        //$out .= ' /IRT ';
        if (isset($rawOpt['subj']) && \is_string($rawOpt['subj']) && $rawOpt['subj'] !== '') {
            $out .= ' /Subj ' . $this->getOutTextString($rawOpt['subj'], $oid, true);
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
        if (!\is_array($flags)) {
            return $flags;
        }

        $fval = 0;
        foreach ($flags as $flag) {
            $fval += match (\strtolower($flag)) {
                'invisible' => 1,
                'hidden' => 1 << 1,
                'print' => 1 << 2,
                'nozoom' => 1 << 3,
                'norotate' => 1 << 4,
                'noview' => 1 << 5,
                'readonly' => 1 << 6,
                'locked' => 1 << 7,
                'togglenoview' => 1 << 8,
                'lockedcontents' => 1 << 9,
                default => 0,
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
     * @param array<string, mixed> $rawOpt
     *
     * @throws EncryptException
     * @throws PageException
     * @throws UnicodeException
     * @throws \Throwable
     */
    protected function getOutAnnotationOptSubtype(
        array $annot,
        int $pagenum,
        int $oid,
        int $key,
        array $rawOpt = [],
    ): string {
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
            'screen' => $this->getOutAnnotationOptSubtypeScreen($annot, $rawOpt),
            'sound' => $this->getOutAnnotationOptSubtypeSound($annot),
            'square' => $this->getOutAnnotationOptSubtypeSquare($annot),
            'squiggly' => $this->getOutAnnotationOptSubtypeSquiggly($annot),
            'stamp' => $this->getOutAnnotationOptSubtypeStamp($annot),
            'strikeout' => $this->getOutAnnotationOptSubtypeStrikeout($annot),
            'text' => $this->getOutAnnotationOptSubtypeText($annot),
            'trapnet' => $this->getOutAnnotationOptSubtypeTrapnet($annot),
            'underline' => $this->getOutAnnotationOptSubtypeUnderline($annot),
            'watermark' => $this->getOutAnnotationOptSubtypeWatermark($annot),
            'widget' => $this->getOutAnnotationOptSubtypeWidget($annot, $oid, $rawOpt),
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
            $out .= ' /Open ' . ($annot['opt']['open'] ? 'true' : 'false');
        }

        $iconsapp = ['Comment', 'Help', 'Insert', 'Key', 'NewParagraph', 'Note', 'Paragraph'];
        if (isset($annot['opt']['name']) && \in_array($annot['opt']['name'], $iconsapp, true)) {
            $out .= ' /Name /' . $annot['opt']['name'];
        } else {
            $out .= ' /Name /Note';
        }

        if (!isset($annot['opt']['state']) && !isset($annot['opt']['statemodel'])) {
            return $out;
        }

        $statemodels = ['Marked', 'Review'];
        $stateModel = 'Marked';

        if (isset($annot['opt']['statemodel']) && \in_array($annot['opt']['statemodel'], $statemodels, true)) {
            $stateModel = $annot['opt']['statemodel'];
        }
        $out .= ' /StateModel /' . $stateModel;

        if ($stateModel === 'Marked') {
            $states = ['Accepted', 'Unmarked'];
        } else {
            $states = ['Accepted', 'Rejected', 'Cancelled', 'Completed', 'None'];
        }

        if (isset($annot['opt']['state']) && \in_array($annot['opt']['state'], $states, true)) {
            $out .= ' /State /' . $annot['opt']['state'];
        } elseif ($stateModel === 'Marked') {
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
     *
     * @throws EncryptException
     * @throws PageException
     * @throws UnicodeException
     */
    protected function getOutAnnotationOptSubtypeLink(array $annot, int $pagenum, int $oid): string
    {
        $out = '';
        if ($annot['txt'] !== '') {
            switch ($annot['txt'][0]) {
                case '#': // internal destination
                    $out .= ' /A << /S /GoTo /D /' . $this->encrypt->encodeNameObject(\substr($annot['txt'], 1)) . '>>';
                    break;
                case '@': // internal link ID
                    $link = $this->links[$annot['txt']] ?? null;
                    if (!\is_array($link)) {
                        break;
                    }

                    $l = $link;
                    $page = $this->page->getPage((int) $l['p']);
                    $pageObjN = (int) $page['n'];
                    $pageHeight = $page['pheight'];
                    $y = $this->toYPoints($l['y'], $pageHeight);
                    $out .= \sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $pageObjN, $y);
                    break;
                case '%': // embedded PDF file
                    if (!$this->pdfx) {
                        $filename = \basename(\substr($annot['txt'], 1));
                        $embeddedAction = $this->embeddedfiles[$filename]['a'] ?? null;
                        if (!\is_numeric($embeddedAction)) {
                            break;
                        }
                        $out .=
                            ' /A << /S /GoToE /D [0 /Fit] /NewWindow true /T << /R /C /P '
                            . ($pagenum - 1)
                            . ' /A '
                            . (int) $embeddedAction
                            . ' >>'
                            . ' >>';
                    }
                    break;
                case '*': // embedded generic file
                    $filename = \basename(\substr($annot['txt'], 1));
                    $jsa =
                        'var D=event.target.doc;var MyData=D.dataObjects;for (var i in MyData) if (MyData[i].path=="'
                        . $filename
                        . '")'
                        . ' D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
                    if (!$this->pdfx && $this->pdfuaMode === '') {
                        $out .= ' /A << /S /JavaScript /JS ' . $this->getOutTextString($jsa, $oid, true) . ' >>';
                    }
                    break;
                default:
                    $parsedUrl = \parse_url($annot['txt']);
                    if (
                        !isset($parsedUrl['scheme'])
                        && (isset($parsedUrl['path']) && \strtolower(\substr($parsedUrl['path'], -4)) === '.pdf')
                    ) {
                        // relative link to a PDF file
                        $dest = '[0 /Fit]'; // default page 0
                        if (isset($parsedUrl['fragment'])) {
                            // check for named destination
                            $tmp = \explode('=', $parsedUrl['fragment']);
                            $dest = '(' . (\count($tmp) === 2 ? $tmp[1] ?? '' : $tmp[0]) . ')';
                        }

                        if (!$this->pdfx) {
                            $out .=
                                ' /A << /S /GoToR /D '
                                . $dest
                                . ' /F '
                                . $this->encrypt->escapeDataString($this->unhtmlentities($parsedUrl['path']), $oid)
                                . ' /NewWindow true'
                                . ' >>';
                        }
                    } else {
                        // external URI link
                        if (!$this->pdfx) {
                            $out .=
                                ' /A << /S /URI /URI '
                                . $this->encrypt->escapeDataString($this->unhtmlentities($annot['txt']), $oid)
                                . ' >>';
                        }
                    }
                    break;
            }
        }

        $hmodes = ['N', 'I', 'O', 'P'];
        if (isset($annot['opt']['h']) && $annot['opt']['h'] !== '' && \in_array($annot['opt']['h'], $hmodes, true)) {
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
     *
     * @throws EncryptException
     * @throws UnicodeException
     */
    protected function getOutAnnotationOptSubtypeFreetext(array $annot, int $oid): string
    {
        $out = '';
        if (isset($annot['opt']['da']) && $annot['opt']['da'] !== '') {
            $out .= ' /DA ' . $this->encrypt->escapeDataString($annot['opt']['da'], $oid);
        }

        if (isset($annot['opt']['q']) && $annot['opt']['q'] >= 0 && $annot['opt']['q'] <= 2) {
            $out .= ' /Q ' . (int) $annot['opt']['q'];
        }

        if (isset($annot['opt']['rc'])) {
            $out .= ' /RC ' . $this->getOutTextString($annot['opt']['rc'], $annot['n'], true);
        }

        if (isset($annot['opt']['ds'])) {
            $out .= ' /DS ' . $this->getOutTextString($annot['opt']['ds'], $annot['n'], true);
        }

        if (isset($annot['opt']['cl'])) {
            $out .= ' /CL [';
            foreach ($annot['opt']['cl'] as $cl) {
                $out .= \sprintf('%F ', $this->toPoints(\floatval($cl)));
            }

            $out .= ']';
        }

        $tfit = ['FreeText', 'FreeTextCallout', 'FreeTextTypeWriter'];
        if (isset($annot['opt']['it']) && \in_array($annot['opt']['it'], $tfit, true)) {
            $out .= ' /IT /' . $annot['opt']['it'];
        }

        if (isset($annot['opt']['rd']) && \count($annot['opt']['rd']) === 4) {
            $l = $this->toPoints($annot['opt']['rd'][0]);
            $r = $this->toPoints($annot['opt']['rd'][1]);
            $t = $this->toPoints($annot['opt']['rd'][2]);
            $b = $this->toPoints($annot['opt']['rd'][3]);
            $out .= \sprintf(' /RD [%F %F %F %F]', $l, $r, $t, $b);
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
        if (isset($annot['opt']['le']) && \in_array($annot['opt']['le'], $lineendings, true)) {
            $out .= ' /LE /' . $annot['opt']['le'];
        }

        return $out;
    }

    /**
     * @return array<int, string>
     */
    protected function getAnnotationLineEndingStyles(): array
    {
        return [
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
    }

    /**
     * @param mixed $values
     */
    protected function getOutAnnotationPointsArray(mixed $values, int $minValues = 0, int $groupMultiple = 0): string
    {
        if (!\is_array($values)) {
            return '';
        }

        $coords = [];
        foreach (\array_keys($values) as $valkey) {
            if (!\is_numeric($values[$valkey] ?? null)) {
                continue;
            }

            $coords[] = \sprintf('%F', $this->toPoints((float) $values[$valkey]));
        }

        if (\count($coords) < $minValues || $groupMultiple > 0 && (\count($coords) % $groupMultiple) !== 0) {
            return '';
        }

        return '[' . \implode(' ', $coords) . ']';
    }

    /**
     * @param array<string, mixed> $annot
     */
    protected function getOutAnnotationRectDifferences(array $annot): string
    {
        if (!isset($annot['opt']) || !\is_array($annot['opt'])) {
            return '';
        }

        $opt = $annot['opt'];
        if (
            !isset($opt['rd'])
            || !\is_array($opt['rd'])
            || !\is_numeric($opt['rd'][0] ?? null)
            || !\is_numeric($opt['rd'][1] ?? null)
            || !\is_numeric($opt['rd'][2] ?? null)
            || !\is_numeric($opt['rd'][3] ?? null)
        ) {
            return '';
        }

        $rd = $opt['rd'];
        $out = $this->getOutAnnotationPointsArray($rd, 4, 4);
        if ($out === '') {
            return '';
        }

        return ' /RD ' . $out;
    }

    /**
     * @param array<string, mixed> $annot
     */
    protected function getOutAnnotationInteriorColor(array $annot): string
    {
        if (!isset($annot['opt']) || !\is_array($annot['opt'])) {
            return '';
        }

        $opt = $annot['opt'];
        if (!isset($opt['ic']) || !\is_array($opt['ic'])) {
            return '';
        }

        $ic = \array_map(static fn($v) => \is_numeric($v) ? (float) $v : 0.0, $opt['ic']);
        return ' /IC ' . static::getColorStringFromPercArray($ic);
    }

    /**
     * @param array<string, mixed> $annot
     */
    protected function getOutAnnotationLineEndings(array $annot): string
    {
        if (!isset($annot['opt']) || !\is_array($annot['opt'])) {
            return '';
        }

        $opt = $annot['opt'];
        if (!isset($opt['le']) || !\is_array($opt['le']) || \count($opt['le']) !== 2) {
            return '';
        }

        $lineEndings = $opt['le'];
        if (!\is_string($lineEndings[0] ?? null) || !\is_string($lineEndings[1] ?? null)) {
            return '';
        }

        $styles = $this->getAnnotationLineEndingStyles();
        if (!\in_array($lineEndings[0], $styles, true) || !\in_array($lineEndings[1], $styles, true)) {
            return '';
        }

        return ' /LE [/' . $lineEndings[0] . ' /' . $lineEndings[1] . ']';
    }

    /**
     * @param array<string, mixed> $annot
     */
    protected function getOutAnnotationQuadPoints(array $annot): string
    {
        if (!isset($annot['opt']) || !\is_array($annot['opt'])) {
            return '';
        }

        $opt = $annot['opt'];
        if (!isset($opt['quadpoints']) || !\is_array($opt['quadpoints'])) {
            return '';
        }

        $quad = [];
        foreach (\array_keys($opt['quadpoints']) as $pointsetkey) {
            if (!\is_array($opt['quadpoints'][$pointsetkey] ?? null)) {
                continue;
            }

            $coords = $this->getOutAnnotationPointsArray($opt['quadpoints'][$pointsetkey], 8, 8);
            if ($coords === '') {
                continue;
            }

            $quad[] = \substr($coords, 1, -1);
        }

        if ($quad === []) {
            return '';
        }

        return ' /QuadPoints [' . \implode(' ', $quad) . ']';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.line.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeLine(array $annot): string
    {
        $out = '';

        if (isset($annot['opt']['l'])) {
            $line = $this->getOutAnnotationPointsArray($annot['opt']['l'], 4, 4);
            if ($line !== '') {
                $out .= ' /L ' . $line;
            }
        }

        $out .= $this->getOutAnnotationLineEndings($annot);
        $out .= $this->getOutAnnotationInteriorColor($annot);

        if (isset($annot['opt']['ll'])) {
            $out .= \sprintf(' /LL %F', $annot['opt']['ll']);
        }

        if (isset($annot['opt']['lle'])) {
            $out .= \sprintf(' /LLE %F', $annot['opt']['lle']);
        }

        if (isset($annot['opt']['cap'])) {
            $out .= ' /Cap ' . ($annot['opt']['cap'] ? 'true' : 'false');
        }

        $lineIntents = ['LineArrow', 'LineDimension'];
        if (isset($annot['opt']['it']) && \in_array($annot['opt']['it'], $lineIntents, true)) {
            $out .= ' /IT /' . $annot['opt']['it'];
        }

        if (isset($annot['opt']['llo'])) {
            $out .= \sprintf(' /LLO %F', $annot['opt']['llo']);
        }

        $captionPos = ['Inline', 'Top'];
        if (isset($annot['opt']['cp']) && \in_array($annot['opt']['cp'], $captionPos, true)) {
            $out .= ' /CP /' . $annot['opt']['cp'];
        }

        if (isset($annot['opt']['measure'])) {
            $measure = '';
            $measureOpt = $annot['opt']['measure'];
            if (isset($measureOpt['type']) && $measureOpt['type'] !== '') {
                $measure .= ' /Type /' . $measureOpt['type'];
            }

            if (isset($measureOpt['subtype']) && $measureOpt['subtype'] !== '') {
                $measure .= ' /Subtype /' . $measureOpt['subtype'];
            }

            if ($measure !== '') {
                $out .= ' /Measure <<' . $measure . ' >>';
            }
        }

        if (isset($annot['opt']['co'])) {
            $co = $this->getOutAnnotationPointsArray($annot['opt']['co'], 2, 2);
            if ($co !== '') {
                $out .= ' /CO ' . $co;
            }
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.square.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeSquare(array $annot): string
    {
        return $this->getOutAnnotationInteriorColor($annot) . $this->getOutAnnotationRectDifferences($annot);
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.circle.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeCircle(array $annot): string
    {
        return $this->getOutAnnotationInteriorColor($annot) . $this->getOutAnnotationRectDifferences($annot);
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.polygon.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypePolygon(array $annot): string
    {
        $out = '';
        if (isset($annot['opt']['vertices'])) {
            $vertices = $this->getOutAnnotationPointsArray($annot['opt']['vertices'], 4, 2);
            if ($vertices !== '') {
                $out .= ' /Vertices ' . $vertices;
            }
        }

        $out .= $this->getOutAnnotationInteriorColor($annot);

        $polyIntents = ['PolygonCloud', 'PolyLineDimension'];
        if (isset($annot['opt']['it']) && \in_array($annot['opt']['it'], $polyIntents, true)) {
            $out .= ' /IT /' . $annot['opt']['it'];
        }

        if (isset($annot['opt']['measure'])) {
            $measure = '';
            $measureOpt = $annot['opt']['measure'];
            if (isset($measureOpt['type']) && $measureOpt['type'] !== '') {
                $measure .= ' /Type /' . $measureOpt['type'];
            }

            if (isset($measureOpt['subtype']) && $measureOpt['subtype'] !== '') {
                $measure .= ' /Subtype /' . $measureOpt['subtype'];
            }

            if ($measure !== '') {
                $out .= ' /Measure <<' . $measure . ' >>';
            }
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.polyline.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypePolyline(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypePolygon($annot) . $this->getOutAnnotationLineEndings($annot);
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.highlight.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeHighlight(array $annot): string
    {
        return $this->getOutAnnotationQuadPoints($annot);
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeUnderline(array $annot): string
    {
        return $this->getOutAnnotationQuadPoints($annot);
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.squiggly.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeSquiggly(array $annot): string
    {
        return $this->getOutAnnotationQuadPoints($annot);
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.strikeout.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeStrikeout(array $annot): string
    {
        return $this->getOutAnnotationQuadPoints($annot);
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.stamp.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeStamp(array $annot): string
    {
        $icons = [
            'Approved',
            'AsIs',
            'Confidential',
            'Departmental',
            'Draft',
            'Experimental',
            'Expired',
            'Final',
            'ForComment',
            'ForPublicRelease',
            'NotApproved',
            'NotForPublicRelease',
            'Sold',
            'TopSecret',
        ];

        if (isset($annot['opt']['name']) && \in_array($annot['opt']['name'], $icons, true)) {
            return ' /Name /' . $annot['opt']['name'];
        }

        return ' /Name /Draft';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.caret.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeCaret(array $annot): string
    {
        $out = $this->getOutAnnotationRectDifferences($annot);
        $symbols = ['P', 'None'];
        if (isset($annot['opt']['sy']) && \in_array($annot['opt']['sy'], $symbols, true)) {
            $out .= ' /Sy /' . $annot['opt']['sy'];
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.ink.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeInk(array $annot): string
    {
        if (!isset($annot['opt']['inklist'])) {
            return '';
        }

        $paths = [];
        foreach ($annot['opt']['inklist'] as $line) {
            $path = $this->getOutAnnotationPointsArray($line, 4, 2);
            if ($path === '') {
                continue;
            }

            $paths[] = $path;
        }

        if ($paths === []) {
            return '';
        }

        return ' /InkList [' . \implode(' ', $paths) . ']';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.popup.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypePopup(array $annot): string
    {
        $out = '';

        $parentOpt = $annot['opt']['parent'] ?? null;
        if (\is_array($parentOpt) && \is_numeric($parentOpt['n'] ?? null)) {
            $out .= ' /Parent ' . (int) $parentOpt['n'] . ' 0 R';
        }

        if (isset($annot['opt']['open'])) {
            $out .= ' /Open ' . ($annot['opt']['open'] ? 'true' : 'false');
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.fileattachment.
     *
     * @param TAnnot $annot Array containing page annotations.
     * @param int    $key Annotation index in the current page.
     */
    protected function getOutAnnotationOptSubtypeFileattachment(array $annot, int $key): string
    {
        if ($this->pdfa === 1 || $this->pdfa === 2 || !isset($annot['opt']['fs'])) {
            // embedded files are not allowed in PDF/A mode version 1 and 2
            return '';
        }

        $filename = \basename($annot['opt']['fs']);
        if (!isset($this->embeddedfiles[$filename]['f'])) {
            return '';
        }

        $out = ' /FS ' . $this->embeddedfiles[$filename]['f'] . ' 0 R';
        $iconsapp = ['Graph', 'Paperclip', 'PushPin', 'Tag'];
        if (isset($annot['opt']['name']) && \in_array($annot['opt']['name'], $iconsapp, true)) {
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
        if (!isset($annot['opt']['fs']) || $annot['opt']['fs'] === '') {
            return '';
        }

        $filename = \basename($annot['opt']['fs']);
        if (!isset($this->embeddedfiles[$filename]['f'])) {
            return '';
        }

        // Limited support: currently writes sound file reference and icon name.
        // Extended sound parameters (R, C, B, E, CO, CP) are intentionally not serialized yet.
        $out = ' /Sound ' . $this->embeddedfiles[$filename]['f'] . ' 0 R';
        $iconsapp = ['Speaker', 'Mic'];
        if (isset($annot['opt']['name']) && \in_array($annot['opt']['name'], $iconsapp, true)) {
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
     * @throws \Throwable
     */
    protected function getOutAnnotationOptSubtypeMovie(array $annot): string
    {
        $out = '';

        if (isset($annot['opt']['t']) && $annot['opt']['t'] !== '') {
            $out .= ' /T ' . $this->getOutTextString($annot['opt']['t'], $annot['n'], true);
        }

        if (isset($annot['opt']['movie'])) {
            $movieOpt = $annot['opt']['movie'];
            $movie = '';
            if ($movieOpt['f'] !== '') {
                $movie .= ' /F ' . $this->encrypt->escapeDataString($movieOpt['f'], $annot['n']);
            }

            if (\is_array($movieOpt['aspect'] ?? null) && \count($movieOpt['aspect']) === 2) {
                $movie .= \sprintf(' /Aspect [%F %F]', $movieOpt['aspect'][0], $movieOpt['aspect'][1]);
            }

            if (isset($movieOpt['rotate'])) {
                $movie .= ' /Rotate ' . (int) $movieOpt['rotate'];
            }

            if (isset($movieOpt['poster'])) {
                if (\is_bool($movieOpt['poster'])) {
                    $movie .= ' /Poster ' . ($movieOpt['poster'] ? 'true' : 'false');
                } else {
                    $movie .= ' /Poster ' . $this->encrypt->escapeDataString($movieOpt['poster'], $annot['n']);
                }
            }

            if ($movie !== '') {
                $out .= ' /Movie <<' . $movie . ' >>';
            }
        }

        if (isset($annot['opt']['a'])) {
            if (\is_bool($annot['opt']['a'])) {
                $out .= ' /A ' . ($annot['opt']['a'] ? 'true' : 'false');
                return $out;
            }

            $actionOpt = $annot['opt']['a'];
            if ($actionOpt === []) {
                return $out;
            }

            $action = '';
            $actionStart = $actionOpt['start'] ?? null;
            if ($actionStart !== null) {
                if (\is_numeric($actionStart)) {
                    $action .= ' /Start ' . (int) $actionStart;
                } elseif (\is_string($actionStart)) {
                    $action .= ' /Start ' . $this->encrypt->escapeDataString($actionStart, $annot['n']);
                } elseif (\count($actionStart) === 2) {
                    $action .= ' /Start [';
                    if (\is_numeric($actionStart[0])) {
                        $action .= (string) (int) $actionStart[0];
                    } else {
                        $action .= $this->encrypt->escapeDataString($actionStart[0], $annot['n']);
                    }

                    $action .= ' ' . (int) $actionStart[1] . ']';
                }
            }

            $actionDuration = $actionOpt['duration'] ?? null;
            if ($actionDuration !== null) {
                if (\is_numeric($actionDuration)) {
                    $action .= ' /Duration ' . (int) $actionDuration;
                } elseif (\is_string($actionDuration)) {
                    $action .= ' /Duration ' . $this->encrypt->escapeDataString($actionDuration, $annot['n']);
                } elseif (\count($actionDuration) === 2) {
                    $action .= ' /Duration [';
                    if (\is_numeric($actionDuration[0])) {
                        $action .= (string) (int) $actionDuration[0];
                    } else {
                        $action .= $this->encrypt->escapeDataString($actionDuration[0], $annot['n']);
                    }

                    $action .= ' ' . (int) $actionDuration[1] . ']';
                }
            }

            $actionRate = $actionOpt['rate'] ?? null;
            if (\is_numeric($actionRate)) {
                $action .= \sprintf(' /Rate %F', $actionRate);
            }

            $actionVolume = $actionOpt['volume'] ?? null;
            if (\is_numeric($actionVolume)) {
                $action .= \sprintf(' /Volume %F', $actionVolume);
            }

            $showControls = $actionOpt['showcontrols'] ?? null;
            if (\is_bool($showControls)) {
                $action .= ' /ShowControls ' . ($showControls ? 'true' : 'false');
            }

            $modes = ['Once', 'Open', 'Repeat', 'Palindrome'];
            $actionMode = $actionOpt['mode'] ?? '';
            if (\in_array($actionMode, $modes, true)) {
                $action .= ' /Mode /' . $actionMode;
            }

            $actionSync = $actionOpt['synchronous'] ?? null;
            if (\is_bool($actionSync)) {
                $action .= ' /Synchronous ' . ($actionSync ? 'true' : 'false');
            }

            $fwscale = $actionOpt['fwscale'] ?? null;
            if (\is_array($fwscale) && \count($fwscale) === 2) {
                $action .= ' /FWScale [' . (int) $fwscale[0] . ' ' . (int) $fwscale[1] . ']';
            }

            $fwposition = $actionOpt['fwposition'] ?? null;
            if (\is_array($fwposition) && \count($fwposition) === 2) {
                $action .= \sprintf(' /FWPosition [%F %F]', $fwposition[0], $fwposition[1]);
            }

            if ($action !== '') {
                $out .= ' /A <<' . $action . ' >>';
            }
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.widget.
     *
     * @param TAnnot $annot Array containing page annotations.
     * @param int    $oid   Annotation Object ID.
     * @param array<string, mixed> $rawOpt
     * @throws \Throwable
     */
    protected function getOutAnnotationOptSubtypeWidget(array $annot, int $oid, array $rawOpt = []): string
    {
        $out = '';
        $rawOpt = $this->getAnnotationRawOptions($annot, $rawOpt);
        $hmode = ['N', 'I', 'O', 'P', 'T'];
        if (isset($annot['opt']['h']) && $annot['opt']['h'] !== '' && \in_array($annot['opt']['h'], $hmode, true)) {
            $out .= ' /H /' . $annot['opt']['h'];
        }

        if (isset($annot['opt']['mk']) && $annot['opt']['mk'] !== []) {
            $mk = $annot['opt']['mk'];
            $out .= ' /MK <<';
            if (isset($mk['r']) && \is_numeric($mk['r'])) {
                $out .= ' /R ' . $mk['r'];
            }

            if (isset($mk['bc']) && \is_array($mk['bc'])) {
                $bc = \array_map(static fn($v) => \is_numeric($v) ? (float) $v : 0.0, $mk['bc']);
                $out .= ' /BC ' . static::getColorStringFromPercArray($bc);
            }

            if (isset($mk['bg']) && \is_array($mk['bg'])) {
                $bg = \array_map(static fn($v) => \is_numeric($v) ? (float) $v : 0.0, $mk['bg']);
                $out .= ' /BG ' . static::getColorStringFromPercArray($bg);
            }

            if (isset($mk['ca']) && \is_string($mk['ca'])) {
                $out .= ' /CA ' . $mk['ca'];
            }

            if (isset($mk['rc']) && \is_string($mk['rc'])) {
                $out .= ' /RC ' . $mk['rc'];
            }

            if (isset($mk['ac']) && \is_string($mk['ac'])) {
                $out .= ' /AC ' . $mk['ac'];
            }

            if (isset($mk['i']) && \is_string($mk['i'])) {
                $info = $this->image->getImageDataByKey($this->image->getKey($mk['i']));
                $out .= ' /I ' . (string) $info['obj'] . ' 0 R';
            }

            if (isset($mk['ri']) && \is_string($mk['ri'])) {
                $info = $this->image->getImageDataByKey($this->image->getKey($mk['ri']));
                $out .= ' /RI ' . (string) $info['obj'] . ' 0 R';
            }

            if (isset($mk['ix']) && \is_string($mk['ix'])) {
                $info = $this->image->getImageDataByKey($this->image->getKey($mk['ix']));
                $out .= ' /IX ' . (string) $info['obj'] . ' 0 R';
            }

            if (isset($mk['if']) && \is_array($mk['if']) && $mk['if'] !== []) {
                $mkIf = $mk['if'];
                $out .= ' /IF <<';
                $if_sw = ['A', 'B', 'S', 'N'];
                if (isset($mkIf['sw']) && \is_string($mkIf['sw']) && \in_array($mkIf['sw'], $if_sw, true)) {
                    $out .= ' /SW /' . $mkIf['sw'];
                }

                $if_s = ['A', 'P'];
                if (isset($mkIf['s']) && \is_string($mkIf['s']) && \in_array($mkIf['s'], $if_s, true)) {
                    $out .= ' /S /' . $mkIf['s'];
                }

                if (
                    isset($mkIf['a'])
                    && \is_array($mkIf['a'])
                    && isset($mkIf['a'][0], $mkIf['a'][1])
                    && \is_numeric($mkIf['a'][0])
                    && \is_numeric($mkIf['a'][1])
                ) {
                    $out .= \sprintf(' /A [%F %F]', $mkIf['a'][0], $mkIf['a'][1]);
                }

                if (($mkIf['fb'] ?? false) === true) {
                    $out .= ' /FB true';
                }

                $out .= '>>';
            }

            if (isset($mk['tp']) && \is_numeric($mk['tp']) && $mk['tp'] >= 0 && $mk['tp'] <= 6) {
                $out .= ' /TP ' . (int) $mk['tp'];
            }

            $out .= '>>';
        }

        // --- Entries for field dictionaries ---
        if (isset($this->radiobuttons[$annot['txt']]['n'])) {
            $out .= ' /Parent ' . $this->radiobuttons[$annot['txt']]['n'] . ' 0 R';
        }

        if (isset($annot['opt']['t'])) {
            $out .= ' /T ' . $this->encrypt->escapeDataString($annot['opt']['t'], $oid);
        }

        if (isset($annot['opt']['tu'])) {
            $out .= ' /TU ' . $this->encrypt->escapeDataString($annot['opt']['tu'], $oid);
        }

        if (isset($annot['opt']['tm'])) {
            $out .= ' /TM ' . $this->encrypt->escapeDataString($annot['opt']['tm'], $oid);
        }

        if (isset($annot['opt']['ff'])) {
            if (\is_array($annot['opt']['ff'])) {
                $flag = 0;
                foreach ($annot['opt']['ff'] as $val) {
                    if (!($val >= 1 && $val <= 32)) {
                        continue;
                    }

                    $flag += 1 << (\intval($val) - 1);
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
                foreach (\array_keys($annot['opt']['v']) as $vkey) {
                    if (\is_numeric($annot['opt']['v'][$vkey] ?? null)) {
                        $out .= \sprintf(' %F', \floatval($annot['opt']['v'][$vkey]));
                        continue;
                    }

                    if (!\is_string($annot['opt']['v'][$vkey] ?? null)) {
                        continue;
                    }

                    $out .= ' ' . $annot['opt']['v'][$vkey];
                }
            } else {
                if (\is_string($annot['opt']['v']) || \is_numeric($annot['opt']['v'])) {
                    $out .= ' ' . $this->getOutTextString((string) $annot['opt']['v'], $oid, true);
                }
            }
        }

        if (isset($annot['opt']['dv'])) {
            $out .= ' /DV';
            if (\is_array($annot['opt']['dv'])) {
                foreach (\array_keys($annot['opt']['dv']) as $dvkey) {
                    if (\is_numeric($annot['opt']['dv'][$dvkey] ?? null)) {
                        $out .= \sprintf(' %F', \floatval($annot['opt']['dv'][$dvkey]));
                        continue;
                    }

                    if (!\is_string($annot['opt']['dv'][$dvkey] ?? null)) {
                        continue;
                    }

                    $out .= ' ' . $annot['opt']['dv'][$dvkey];
                }
            } else {
                if (\is_string($annot['opt']['dv']) || \is_numeric($annot['opt']['dv'])) {
                    $out .= ' ' . $this->getOutTextString((string) $annot['opt']['dv'], $oid, true);
                }
            }
        }

        if (isset($annot['opt']['rv'])) {
            $out .= ' /RV';
            if (\is_array($annot['opt']['rv'])) {
                foreach (\array_keys($annot['opt']['rv']) as $rvkey) {
                    if (\is_numeric($annot['opt']['rv'][$rvkey] ?? null)) {
                        $out .= \sprintf(' %F', \floatval($annot['opt']['rv'][$rvkey]));
                        continue;
                    }

                    if (!\is_string($annot['opt']['rv'][$rvkey] ?? null)) {
                        continue;
                    }

                    $out .= ' ' . $annot['opt']['rv'][$rvkey];
                }
            } else {
                if (\is_string($annot['opt']['rv']) || \is_numeric($annot['opt']['rv'])) {
                    $out .= ' ' . $this->getOutTextString((string) $annot['opt']['rv'], $oid, true);
                }
            }
        }

        $action = $annot['opt']['a'] ?? '';
        if (
            \is_string($action)
            && $action !== ''
            && !$this->pdfx
            && ($this->pdfuaMode === '' || !\str_contains($action, '/JavaScript'))
        ) {
            $out .= ' /A << ' . $action . ' >>';
        }

        $additionalAction = $annot['opt']['aa'] ?? '';
        if (
            \is_string($additionalAction)
            && $additionalAction !== ''
            && !$this->pdfx
            && ($this->pdfuaMode === '' || !\str_contains($additionalAction, '/JavaScript'))
        ) {
            $out .= ' /AA << ' . $additionalAction . ' >>';
        }

        if (isset($annot['opt']['da']) && $annot['opt']['da'] !== '') {
            $out .= ' /DA ' . $this->encrypt->escapeDataString($annot['opt']['da'], $oid);
        }

        if (isset($annot['opt']['q']) && $annot['opt']['q'] >= 0 && $annot['opt']['q'] <= 2) {
            $out .= ' /Q ' . (int) $annot['opt']['q'];
        }

        if (isset($annot['opt']['opt']) && $annot['opt']['opt'] !== []) {
            $out .= ' /Opt [';
            /** @var mixed $copt */
            foreach ($annot['opt']['opt'] as $copt) {
                if (\is_array($copt)) {
                    if (!isset($copt[0], $copt[1]) || !\is_string($copt[0]) || !\is_string($copt[1])) {
                        continue;
                    }
                    $out .=
                        '['
                        . $this->getOutTextString($copt[0], $oid, true)
                        . $this->getOutTextString($copt[1], $oid, true)
                        . ']';
                } elseif (\is_string($copt) || \is_numeric($copt)) {
                    $out .= $this->getOutTextString(\strval($copt), $oid, true);
                }
            }

            $out .= ']';
        }

        if (isset($annot['opt']['i']) && $annot['opt']['i'] !== []) {
            $out .= ' /I [';
            /** @var mixed $copt */
            foreach ($annot['opt']['i'] as $copt) {
                if (!\is_numeric($copt)) {
                    continue;
                }

                $out .= \strval(\intval($copt)) . ' ';
            }

            $out .= ']';
        }

        if (isset($rawOpt['ti']) && \is_numeric($rawOpt['ti'])) {
            $out .= ' /TI ' . (int) $rawOpt['ti'];
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.screen.
     *
     * @param TAnnot $annot Array containing page annotations.
     * @param array<string, mixed> $rawOpt
     * @throws \Throwable
     */
    protected function getOutAnnotationOptSubtypeScreen(array $annot, array $rawOpt = []): string
    {
        $out = '';
        $rawOpt = $this->getAnnotationRawOptions($annot, $rawOpt);

        if (isset($annot['opt']['t']) && $annot['opt']['t'] !== '') {
            $out .= ' /T ' . $this->getOutTextString($annot['opt']['t'], $annot['n'], true);
        }

        if (isset($annot['opt']['mk']) && $annot['opt']['mk'] !== []) {
            $mk = $annot['opt']['mk'];
            $out .= ' /MK <<';
            if (isset($mk['r']) && \is_numeric($mk['r'])) {
                $out .= ' /R ' . $mk['r'];
            }

            if (isset($mk['bc']) && \is_array($mk['bc'])) {
                $bc = \array_map(static fn($v) => \is_numeric($v) ? (float) $v : 0.0, $mk['bc']);
                $out .= ' /BC ' . static::getColorStringFromPercArray($bc);
            }

            if (isset($mk['bg']) && \is_array($mk['bg'])) {
                $bg = \array_map(static fn($v) => \is_numeric($v) ? (float) $v : 0.0, $mk['bg']);
                $out .= ' /BG ' . static::getColorStringFromPercArray($bg);
            }

            if (isset($mk['ca']) && \is_string($mk['ca'])) {
                $out .= ' /CA ' . $mk['ca'];
            }

            if (isset($mk['rc']) && \is_string($mk['rc'])) {
                $out .= ' /RC ' . $mk['rc'];
            }

            if (isset($mk['ac']) && \is_string($mk['ac'])) {
                $out .= ' /AC ' . $mk['ac'];
            }

            if (isset($mk['tp']) && \is_numeric($mk['tp']) && $mk['tp'] >= 0 && $mk['tp'] <= 6) {
                $out .= ' /TP ' . (int) $mk['tp'];
            }

            $out .= ' >>';
        }

        if (
            !$this->pdfx
            && isset($rawOpt['a'])
            && \is_string($rawOpt['a'])
            && $rawOpt['a'] !== ''
            && ($this->pdfuaMode === '' || !\str_contains($rawOpt['a'], '/JavaScript'))
        ) {
            $out .= ' /A << ' . $rawOpt['a'] . ' >>';
        }

        if (
            !$this->pdfx
            && isset($rawOpt['aa'])
            && \is_string($rawOpt['aa'])
            && $rawOpt['aa'] !== ''
            && ($this->pdfuaMode === '' || !\str_contains($rawOpt['aa'], '/JavaScript'))
        ) {
            $out .= ' /AA << ' . $rawOpt['aa'] . ' >>';
        }

        return $out;
    }

    /**
     * Merge typed annotation options with an external raw option map.
     *
     * @param TAnnot $annot
     * @param array<array-key, mixed> $rawOpt
     *
     * @return array<array-key, mixed>
     */
    protected function getAnnotationRawOptions(array $annot, array $rawOpt = []): array
    {
        $annotOpt = $annot['opt'];
        \array_walk($annotOpt, static function (mixed $annotValue, int|string $key) use (&$rawOpt): void {
            if (\array_key_exists($key, $rawOpt)) {
                return;
            }

            $rawOpt[$key] = $annotValue;
        });

        return $rawOpt;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.printermark.
     *
     * @param TAnnot $_annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypePrintermark(array $_annot): string
    {
        // PrinterMark payload dictionaries are currently not supported.
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.redact.
     *
     * @param TAnnot $annot Array containing page annotations.
     * @throws \Throwable
     */
    protected function getOutAnnotationOptSubtypeRedact(array $annot): string
    {
        $out = $this->getOutAnnotationQuadPoints($annot) . $this->getOutAnnotationInteriorColor($annot);

        if (isset($annot['opt']['ro'])) {
            $out .= ' /RO ' . $this->encrypt->escapeDataString($annot['opt']['ro'], $annot['n']);
        }

        if (isset($annot['opt']['overlaytext'])) {
            $out .= ' /OverlayText ' . $this->getOutTextString($annot['opt']['overlaytext'], $annot['n'], true);
        }

        if (isset($annot['opt']['repeat'])) {
            $out .= ' /Repeat ' . ($annot['opt']['repeat'] ? 'true' : 'false');
        }

        if (isset($annot['opt']['da']) && $annot['opt']['da'] !== '') {
            $out .= ' /DA ' . $this->encrypt->escapeDataString($annot['opt']['da'], $annot['n']);
        }

        if (isset($annot['opt']['q']) && $annot['opt']['q'] >= 0 && $annot['opt']['q'] <= 2) {
            $out .= ' /Q ' . (int) $annot['opt']['q'];
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.trapnet.
     *
     * @param TAnnot $_annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeTrapnet(array $_annot): string
    {
        // TrapNet annotations are currently not supported.
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.watermark.
     *
     * @param TAnnot $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeWatermark(array $annot): string
    {
        $fixedPrint = $annot['opt']['fixedprint'] ?? null;
        if (!\is_array($fixedPrint)) {
            return '';
        }

        $out = '';
        /** @var mixed $fixedPrintType */
        $fixedPrintType = $fixedPrint['type'];
        if (\is_string($fixedPrintType) && $fixedPrintType !== '') {
            $out .= ' /Type /' . $fixedPrintType;
        }

        $matrix = $fixedPrint['matrix'] ?? null;
        if (\is_array($matrix) && \count($matrix) === 6) {
            $out .= \sprintf(
                ' /Matrix [%F %F %F %F %F %F]',
                $matrix[0],
                $matrix[1],
                $matrix[2],
                $matrix[3],
                $matrix[4],
                $matrix[5],
            );
        }

        $fixedPrintH = $fixedPrint['h'] ?? null;
        if (\is_numeric($fixedPrintH)) {
            $out .= \sprintf(' /H %F', $fixedPrintH);
        }

        $fixedPrintV = $fixedPrint['v'] ?? null;
        if (\is_numeric($fixedPrintV)) {
            $out .= \sprintf(' /V %F', $fixedPrintV);
        }

        if ($out === '') {
            return '';
        }

        return ' /FixedPrint <<' . $out . ' >>';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.3d.
     *
     * @param TAnnot $_annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtype3D(array $_annot): string
    {
        // 3D annotation dictionaries are currently not supported.
        return '';
    }

    /**
     * Returns the PDF Javascript entry.
     *
     * @throws \Throwable
     */
    protected function getOutJavascript(): string
    {
        if (
            $this->pdfa > 0
            || $this->pdfx
            || $this->pdfuaMode !== ''
            || $this->javascript === '' && $this->jsobjects === []
        ) {
            return '';
        }

        if (str_contains($this->javascript, 'this.addField')) {
            if (!$this->userrights['enabled']) {
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
        if ($this->javascript !== '') {
            // default Javascript object
            $oid = ++$this->pon;
            $out .=
                $oid
                . ' 0 obj'
                . "\n"
                . '<<'
                . ' /S /JavaScript /JS '
                . $this->getOutTextString($this->javascript, $oid, true)
                . ' >>'
                . "\n"
                . 'endobj'
                . "\n";
            $njs .= ' (EmbeddedJS) ' . $oid . ' 0 R';
        }

        foreach ($this->jsobjects as $key => $val) {
            // additional Javascript objects
            $oid = $val['n'];
            $out .=
                $oid
                . ' 0 obj'
                . "\n"
                . '<< '
                . '/S /JavaScript /JS '
                . $this->getOutTextString($val['js'], $oid, true)
                . ' >>'
                . "\n"
                . 'endobj'
                . "\n";
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
        \usort($this->outlines, static fn(array $left, array $right): int => (int) $left['p'] <=> (int) $right['p']);
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
                $parent = $lru[$o['l'] - 1] ?? 0;
                // set parent and last pointers
                $this->outlines[$i]['parent'] = $parent;
                $this->outlines[$parent]['last'] = $i;
                if ($o['l'] > $level) {
                    // level increasing: set first pointer
                    $this->outlines[$parent]['first'] = $i;
                }
            } else {
                $this->outlines[$i]['parent'] = $numbookmarks;
            }

            if ($o['l'] <= $level && $i > 0) {
                // set prev and next pointers
                $prev = $lru[$o['l']] ?? 0;
                $this->outlines[$prev]['next'] = $i;
                $this->outlines[$i]['prev'] = $prev;
            }

            $lru[$o['l']] = $i;
            $level = $o['l'];
        }
        return $lru[0] ?? 0;
    }

    /**
     * Returns the PDF Bookmarks entry.
     *
     * @return string Bookmarks PDF objects.
     *
     * @throws UnicodeException
     * @throws EncryptException
     * @throws \Throwable
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
            $out .=
                $oid
                . ' 0 obj'
                . "\n"
                . '<<'
                . ' /Title '
                . $this->getOutTextString($title, $oid, true)
                . ' /Parent '
                . ($first_oid + $outline['parent'])
                . ' 0 R';
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

            if ($outline['u'] !== '') {
                // link
                switch ($outline['u'][0]) {
                    case '#':
                        // internal destination
                        $out .= ' /Dest /' . $this->encrypt->encodeNameObject(\substr($outline['u'], 1));
                        break;
                    case '@':
                        // internal link ID
                        $link = $this->links[$outline['u']] ?? null;
                        if (!\is_array($link)) {
                            break;
                        }

                        $l = $link;
                        $page = $this->page->getPage((int) $l['p']);
                        $poid = (int) $page['n'];
                        $pheight = $page['pheight'];
                        $y = $this->toYPoints($l['y'], $pheight);
                        $out .= \sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $poid, $y);
                        break;
                    case '%':
                        // embedded PDF file
                        if (!$this->pdfx) {
                            $filename = \basename(\substr($outline['u'], 1));
                            $embeddedAction = $this->embeddedfiles[$filename]['a'] ?? null;
                            if (!\is_numeric($embeddedAction)) {
                                break;
                            }
                            $out .=
                                ' /A << /S /GoToE /D [0 /Fit] /NewWindow true /T << /R /C /P '
                                . ($outline['p'] - 1)
                                . ' /A '
                                . (int) $embeddedAction
                                . ' >>'
                                . ' >>';
                        }
                        break;
                    case '*':
                        // embedded generic file
                        $filename = \basename(\substr($outline['u'], 1));
                        $jsa =
                            'var D=event.target.doc;var MyData=D.dataObjects;'
                            . 'for (var i in MyData) if (MyData[i].path=="'
                            . $filename
                            . '")'
                            . ' D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
                        if (!$this->pdfx && $this->pdfuaMode === '') {
                            $out .= ' /A <</S /JavaScript /JS ' . $this->getOutTextString($jsa, $oid, true) . '>>';
                        }
                        break;
                    default:
                        // external URI link
                        if (!$this->pdfx) {
                            $out .=
                                ' /A << /S /URI /URI '
                                . $this->encrypt->escapeDataString($this->unhtmlentities($outline['u']), $oid)
                                . ' >>';
                        }
                        break;
                }
            } else {
                // link to a page
                $page = $this->page->getPage($outline['p']);
                $x = $this->toPoints($outline['x']);
                $poid = (int) $page['n'];
                $pheight = $page['pheight'];
                $y = $this->toYPoints($outline['y'], $pheight);
                $out .= \sprintf(' /Dest [%u 0 R /XYZ %F %F null]', $poid, $x, $y);
            }

            // set font style
            $style = 0;
            if ($outline['s'] !== '') {
                if (\str_contains($outline['s'], 'B')) {
                    $style |= 2; // bold
                }

                if (\str_contains($outline['s'], 'I')) {
                    $style |= 1; // oblique
                }
            }

            $out .= \sprintf(' /F %d', $style);
            // set bookmark color
            if ($outline['c'] === '') {
                $out .= ' /C [0.0 0.0 0.0]'; // black
            } else {
                $out .= ' /C [ ' . $this->color->getPdfRgbComponents($outline['c']) . ' ]';
            }

            $out .= ' /Count 0 >>' . "\n" . 'endobj' . "\n";
        }

        //Outline root
        $this->outlinerootoid = ++$this->pon;
        return (
            $out
            . $this->outlinerootoid
            . ' 0 obj'
            . "\n"
            . '<<'
            . ' /Type /Outlines'
            . ' /First '
            . $first_oid
            . ' 0 R'
            . ' /Last '
            . ($first_oid + $root_oid)
            . ' 0 R'
            . ' >>'
            . "\n"
            . 'endobj'
            . "\n"
        );
    }

    /**
     * Returns the PDF Signature Fields entry.
     *
     * @throws \Throwable
     */
    protected function getOutSignatureFields(): string
    {
        if ($this->signature === []) {
            return '';
        }

        $widget = new SignWidget();
        $stringEncoder =
            /** @throws \Throwable */
            fn(string $text, int $oid): string => $this->getOutTextString($text, $oid, true);

        $out = '';
        foreach ($this->signature['appearance']['empty'] as $key => $esa) {
            $page = $this->page->getPage($esa['page']);
            $signame = \sprintf('%s [%03d]', $esa['name'], $key + 1);
            $out .= $widget->annotation(
                $esa['objid'],
                $esa['rect'],
                (int) $page['n'],
                $signame,
                null,
                '',
                $stringEncoder,
            );
        }

        return $out;
    }

    /**
     * Sign the document.
     *
     * @param string $pdfdoc string containing the PDF document
     *
     * @return string Signed PDF document.
     *
     * @throws PdfException
     * @throws \Throwable
     */
    protected function signDocument(string $pdfdoc): string
    {
        if (!$this->sign) {
            return $pdfdoc;
        }

        $prepared = $this->prepareDocumentForSignature($pdfdoc);
        $pdfdoc = $prepared['pdfdoc'];
        $byteRange = $prepared['byte_range'];
        $cms = $this->buildSignatureCms($pdfdoc);
        $signature = $this->convertBinarySignatureToHex($cms);

        return \substr($pdfdoc, 0, $byteRange[1]) . '<' . $signature . '>' . \substr($pdfdoc, $byteRange[1]);
    }

    /**
     * Build the detached CMS (CAdES) signature over the ByteRange content using
     * the native tc-lib-pdf-sign builder, replacing the temp-file PKCS#7 path.
     *
     * The produced CMS carries the ESS signing-certificate-v2 signed attribute,
     * so it is a CAdES-BES structure for every profile; the legacy profile keeps
     * the /SubFilter /adbe.pkcs7.detached wrapper and stays verifiable.
     *
     * @param string $content ByteRange-covered document bytes to sign.
     *
     * @return string DER-encoded CMS ContentInfo.
     *
     * @throws PdfException If credentials cannot be loaded or signing fails.
     */
    protected function buildSignatureCms(string $content): string
    {
        $certDer = $this->loadSignerCertificate($this->signature['signcert']);
        $privateKey = $this->loadSignerPrivateKey($this->signature['privkey'], $this->signature['password']);
        $chainDer = $this->loadExtraCertificates($this->signature['extracerts'] ?? '');
        $timestampProvider = $this->sigtimestamp['enabled'] ? $this->requestSignatureTimestampToken(...) : null;

        try {
            $signBuilder = new SignBuilder();
            return $signBuilder->sign(
                $content,
                $certDer,
                $privateKey,
                $chainDer,
                $this->signatureDigestAlgorithm(),
                $this->docmodtime,
                $timestampProvider,
                $this->signatureIncludesSigningTime(),
            );
        } catch (SignException $e) {
            throw new PdfException('Unable to build the CMS signature: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Request an RFC 3161 signature timestamp token for the given signature bytes.
     *
     * The RFC 3161 request/response codec lives in the tc-lib-pdf-sign package;
     * this host method only owns the HTTP transport (postTimestampRequest, which
     * enforces the URL allow-list). The returned DER token is embedded by the CMS
     * builder as the id-aa-signatureTimeStampToken unsigned attribute (PAdES B-T).
     *
     * @param string $signature Raw SignerInfo signature bytes to be timestamped.
     *
     * @return string DER-encoded RFC 3161 timestamp token.
     *
     * @throws PdfException If the TSA configuration, transport, or response is invalid.
     */
    protected function requestSignatureTimestampToken(string $signature): string
    {
        try {
            $config = new TimestampConfig(
                $this->sigtimestamp['host'],
                $this->sigtimestamp['hash_algorithm'],
                $this->sigtimestamp['policy_oid'],
                $this->sigtimestamp['nonce_enabled'],
                (int) $this->sigtimestamp['timeout'],
                $this->sigtimestamp['verify_peer'],
                $this->sigtimestamp['username'],
                $this->sigtimestamp['password'],
                $this->sigtimestamp['cert'],
            );

            $timestampClient = new TimestampClient($config);
            return $timestampClient->requestToken($signature, $this->postTimestampRequest(...));
        } catch (SignException $e) {
            throw new PdfException('Unable to obtain the TSA timestamp: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Load a signing certificate (PEM string or file:// path) and return its DER.
     *
     * @param string $signcert Certificate source.
     *
     * @return string DER-encoded certificate.
     *
     * @throws PdfException If the certificate cannot be read or parsed.
     */
    protected function loadSignerCertificate(string $signcert): string
    {
        $pem = $this->readCertificateBundle($signcert);
        $matches = [];
        \preg_match_all('/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s', $pem, $matches);

        $first = $matches[0][0] ?? '';
        if ($first === '') {
            throw new PdfException('Unable to read the signing certificate');
        }

        return $this->certificatePemToDer($first);
    }

    /**
     * Load a signing private key (PEM string or file:// path) with its password.
     *
     * @param string $privkey  Private key source.
     * @param string $password Private key password.
     *
     * @throws PdfException If the private key cannot be loaded.
     */
    protected function loadSignerPrivateKey(
        string $privkey,
        #[\SensitiveParameter]
        string $password,
    ): OpenSSLAsymmetricKey {
        $key = \openssl_pkey_get_private($privkey, $password);
        if (!$key instanceof OpenSSLAsymmetricKey) {
            throw new PdfException('Unable to load the signing private key');
        }

        return $key;
    }

    /**
     * Load an optional extra-certificates PEM bundle (file:// path or PEM string)
     * as a list of DER certificate strings for embedding in the CMS.
     *
     * @param string $extracerts Bundle source, or '' for none.
     *
     * @return list<string>
     *
     * @throws PdfException If a certificate in the bundle cannot be decoded.
     */
    protected function loadExtraCertificates(string $extracerts): array
    {
        if ($extracerts === '') {
            return [];
        }

        $bundle = $this->readCertificateBundle($extracerts);
        $matches = [];
        \preg_match_all('/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s', $bundle, $matches);

        $ders = [];
        foreach ($matches[0] ?? [] as $pem) {
            $ders[] = $this->certificatePemToDer($pem);
        }

        return $ders;
    }

    /**
     * Read a certificate bundle from a PEM string or a (file://) file path.
     *
     * @throws PdfException If the file cannot be read.
     */
    protected function readCertificateBundle(string $source): string
    {
        if (\str_contains($source, '-----BEGIN')) {
            return $source;
        }

        $path = \str_starts_with($source, 'file://') ? \substr($source, 7) : $source;

        try {
            $data = $this->file->getFileData($path);
        } catch (FileException $e) {
            throw new PdfException('Unable to read the extra certificates file: ' . $e->getMessage(), 0, $e);
        }

        if ($data === false) {
            throw new PdfException('Unable to read the extra certificates file');
        }

        return $data;
    }

    /**
     * Decode a PEM certificate block to DER.
     *
     * @throws PdfException If the PEM cannot be decoded.
     */
    protected function certificatePemToDer(string $pem): string
    {
        $stripped = (string) \preg_replace('/-----[^-]+-----|\s+/', '', $pem);
        $der = \base64_decode($stripped, true);
        if ($der === false) {
            throw new PdfException('Unable to decode a certificate');
        }

        return $der;
    }

    /**
     * Resolve the CMS digest algorithm from the signature configuration.
     */
    protected function signatureDigestAlgorithm(): string
    {
        $digest = $this->signature['digest_algorithm'] ?? 'sha256';
        return \in_array($digest, SignConfig::DIGEST_ALGORITHMS, true) ? $digest : 'sha256';
    }

    /**
     * Resolve the /SubFilter for the configured signature profile.
     *
     * The default legacy profile keeps ISO 32000-1 /adbe.pkcs7.detached; any
     * PAdES profile emits /ETSI.CAdES.detached.
     */
    protected function signatureSubFilter(): string
    {
        $profile = $this->signature['profile'] ?? SignConfig::PROFILE_LEGACY;
        return $profile === SignConfig::PROFILE_LEGACY ? 'adbe.pkcs7.detached' : 'ETSI.CAdES.detached';
    }

    /**
     * Whether the CMS should carry the signing-time signed attribute.
     *
     * The legacy (ISO 32000-1) profile embeds it; every PAdES profile omits it,
     * because ETSI EN 319 142-1 forbids the CMS signing-time attribute and carries
     * the signing time in the /M signature dictionary entry instead. A PAdES CMS
     * that keeps signing-time is demoted by validators from PAdES-BASELINE-B to the
     * older PAdES-BES format.
     */
    protected function signatureIncludesSigningTime(): bool
    {
        $profile = $this->signature['profile'] ?? SignConfig::PROFILE_LEGACY;
        return $profile === SignConfig::PROFILE_LEGACY;
    }

    /**
     * Number of hex characters reserved for the signature /Contents placeholder.
     *
     * A signature timestamp embeds a full RFC 3161 token in the CMS, roughly
     * tripling its size, so extra room is reserved when timestamping is enabled;
     * otherwise the legacy SIGMAXLEN is kept so existing output is unchanged. The
     * placeholder emission, the ByteRange computation, and the hex padding all
     * read this so they stay in agreement.
     */
    protected function signatureContentsLength(): int
    {
        return $this->sigtimestamp['enabled'] ? $this::SIGMAXLEN + 20_000 : $this::SIGMAXLEN;
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
        $byteRangePos = \strpos($pdfdoc, $this::BYTERANGE);
        if ($byteRangePos === false) {
            $byteRangePos = 0;
        }

        $byteRange[1] = $byteRangePos + $byterangeLength + 10;
        $byteRange[2] = $byteRange[1] + $this->signatureContentsLength() + 2;
        $byteRange[3] = \strlen($pdfdoc) - $byteRange[2];
        $pdfdoc = \substr($pdfdoc, 0, $byteRange[1]) . \substr($pdfdoc, $byteRange[2]);

        $byterange = \sprintf('/ByteRange[0 %u %u %u]', $byteRange[1], $byteRange[2], $byteRange[3]);
        $byterange .= \str_repeat(' ', \max(0, $byterangeLength - \strlen($byterange)));
        $pdfdoc = \str_replace($this::BYTERANGE, $byterange, $pdfdoc);

        return [
            'byte_range' => $byteRange,
            'pdfdoc' => $pdfdoc,
            'pdfdoc_length' => \strlen($pdfdoc),
        ];
    }

    /**
     * Convert the binary signature to padded hexadecimal PDF contents.
     *
     * @param string $signature Digital signature as binary string.
     *
     * @return string Padded hexadecimal signature.
     *
     * @throws PdfException
     */
    protected function convertBinarySignatureToHex(string $signature): string
    {
        $hexSignature = \bin2hex($signature);
        $contentsLength = $this->signatureContentsLength();
        if (\strlen($hexSignature) > $contentsLength) {
            throw new PdfException('Signature is too large for the reserved PDF placeholder');
        }

        return \str_pad($hexSignature, $contentsLength, '0');
    }

    /**
     * Collect the long-term validation material for the signature via the
     * tc-lib-pdf-sign package.
     *
     * The signer certificate plus any extra certificates form a leaf-first chain
     * that the package Signer walks: OCSP is attempted for each certificate that
     * has an issuer in the chain, CRLs for every certificate's distribution
     * points, with responses deduplicated. The HTTP transports (postOcspRequest /
     * getCrlData) stay in the host, which owns networking and the URL allow-list;
     * a null transport skips that revocation source. The embed_* flags gate which
     * material is fetched and embedded.
     *
     * @return array{certs: list<string>, ocsp: list<string>, crls: list<string>}
     * @throws \Throwable
     */
    protected function collectDssMaterial(): array
    {
        $empty = ['certs' => [], 'ocsp' => [], 'crls' => []];

        $ltv = $this->signature['ltv'] ?? ['enabled' => false];
        if (!$ltv['enabled']) {
            return $empty;
        }

        $chainPem = \array_values($this->collectValidationCertificateInputs());
        if ($chainPem === []) {
            return $empty;
        }

        $ocspTransport = $ltv['embed_ocsp'] ?? false ? $this->postOcspRequest(...) : null;
        $crlTransport = $ltv['embed_crl'] ?? false ? $this->getCrlData(...) : null;

        try {
            $signer = new Signer();
            $material = $signer->collectValidationMaterial($chainPem, $ocspTransport, $crlTransport);
        } catch (SignException $e) {
            throw new PdfException('Unable to collect validation material: ' . $e->getMessage(), 0, $e);
        }

        if (!($ltv['embed_certs'] ?? false)) {
            $material['certs'] = [];
        }

        return $material;
    }

    /**
     * @return array<int, string>
     * @throws \Throwable
     */
    protected function collectValidationCertificateInputs(): array
    {
        $inputs = [];

        $main = $this->getCertificateSourceContent($this->signature['signcert']);
        if ($main !== '') {
            $inputs = \array_merge($inputs, $this->extractPemCertificates($main));
        }

        $extra = $this->signature['extracerts'] ?? '';
        if ($extra !== '') {
            $extraContent = $this->getCertificateSourceContent($extra);
            if ($extraContent !== '') {
                $inputs = \array_merge($inputs, $this->extractPemCertificates($extraContent));
            }
        }

        return $inputs;
    }

    /**
     * Append the PAdES B-LT Document Security Store as a post-signing incremental
     * revision.
     *
     * The DSS can only be produced after signing: its VRI key is the uppercase
     * SHA-1 of the final signature /Contents (ISO 32000-2 clause 12.8.4.3), which
     * does not exist until the signature is embedded. The validation material is
     * collected, emitted through the tc-lib-pdf-sign Output\Dss emitter (which
     * computes the correct VRI key), and appended as an incremental revision that
     * never touches the signed bytes; the document catalog is re-emitted in the
     * same revision carrying the /DSS reference.
     *
     * @param string $pdf The signed PDF document.
     *
     * @return string The document with the DSS revision appended, or $pdf unchanged
     *         when LTV/DSS is not requested or there is no material to embed.
     *
     * @throws \Throwable
     */
    protected function appendDssRevision(string $pdf): string
    {
        $this->objid['dss'] = 0;
        if (!$this->sign) {
            return $pdf;
        }

        $ltv = $this->signature['ltv'] ?? ['enabled' => false];
        if (!$ltv['enabled'] || !($ltv['include_dss'] ?? false)) {
            return $pdf;
        }

        $material = $this->collectDssMaterial();
        if ($material['certs'] === [] && $material['ocsp'] === [] && $material['crls'] === []) {
            return $pdf;
        }

        $encryptor =
            /** @throws \Throwable */
            fn(string $data, int $objectId): string => $this->encrypt->encryptString($data, $objectId);
        $signDss = new SignDss();
        $emitted = $signDss->emit($material, $this->extractSignatureContents($pdf), $this->pon, $encryptor);
        $objects = $emitted['objects'];
        if ($objects === []) {
            return $pdf;
        }

        $this->objid['dss'] = $emitted['object_id'];
        $objects[$this->objid['catalog']] = $this->buildCatalogObject($this->objid['catalog']);

        return $this->appendIncrementalRevision($pdf, $objects);
    }

    /**
     * Extract the raw signature /Contents bytes from a signed document.
     *
     * The signature /Contents is a hexadecimal string padded with zeros to the
     * reserved placeholder length; the decoded bytes (the CMS plus that padding)
     * are what a reader hashes for the DSS VRI key, so they are returned verbatim.
     *
     * @param string $pdf The signed PDF document.
     *
     * @return string Hex-decoded signature /Contents, or '' when none is present.
     */
    protected function extractSignatureContents(string $pdf): string
    {
        $needle = '/Contents<';
        $start = \strpos($pdf, $needle);
        if ($start === false) {
            return '';
        }

        $start += \strlen($needle);
        $end = \strpos($pdf, '>', $start);
        if ($end === false) {
            return '';
        }

        $bin = \hex2bin(\substr($pdf, $start, $end - $start));
        return $bin === false ? '' : $bin;
    }

    /**
     * Append the PAdES B-LTA archive document timestamp as a further incremental
     * revision, then timestamp it.
     *
     * For the `pades-b-lta` profile (which requires a configured TSA), a
     * `/Type /DocTimeStamp` value object plus an invisible signature-field widget
     * are emitted through the tc-lib-pdf-sign `Output\DocTimeStamp` / `Output\Widget`
     * emitters; the catalog is re-emitted with the timestamp field added to the
     * AcroForm `/Fields` (and the existing `/DSS` reference kept). A second signing
     * pass then covers the whole document up to that point with a bare RFC 3161
     * token (not a CAdES CMS), exactly like the main signature's ByteRange machinery.
     *
     * @param string $pdf The signed, DSS-augmented PDF document.
     *
     * @return string The document with the archive-timestamp revision appended, or
     *         $pdf unchanged when B-LTA is not requested.
     *
     * @throws \Throwable
     */
    protected function appendDocTimeStampRevision(string $pdf): string
    {
        $this->objid['doctimestamp'] = 0;
        if (!$this->sign) {
            return $pdf;
        }

        $profile = $this->signature['profile'] ?? SignConfig::PROFILE_LEGACY;
        if ($profile !== SignConfig::PROFILE_PADES_B_LTA || !$this->sigtimestamp['enabled']) {
            return $pdf;
        }

        $contentsLength = $this->signatureContentsLength();
        $dtsValueId = ++$this->pon;
        $widgetId = ++$this->pon;
        $this->objid['doctimestamp'] = $widgetId;

        $page = $this->page->getPage($this->signature['appearance']['page']);
        $pageObjN = (int) $page['n'];

        $stringEncoder =
            /** @throws \Throwable */
            fn(string $text, int $oid): string => $this->getOutTextString($text, $oid, true);

        $signDocTimeStamp = new SignDocTimeStamp();
        $signWidget = new SignWidget();
        $objects = [
            $dtsValueId => $signDocTimeStamp->valueObject($dtsValueId, $contentsLength),
            $widgetId => $signWidget->annotation(
                $widgetId,
                '0 0 0 0',
                $pageObjN,
                'DocTimeStamp',
                $dtsValueId,
                '',
                $stringEncoder,
            ),
            $this->objid['catalog'] => $this->buildCatalogObject($this->objid['catalog']),
        ];

        return $this->signDocTimeStamp($this->appendIncrementalRevision($pdf, $objects));
    }

    /**
     * Fill the DocTimeStamp placeholder with a bare RFC 3161 timestamp token over
     * its ByteRange, reusing the signature ByteRange machinery.
     *
     * @param string $pdfdoc Document carrying the DocTimeStamp ByteRange/Contents placeholder.
     *
     * @return string The document with the timestamp token embedded.
     *
     * @throws \Throwable
     */
    protected function signDocTimeStamp(string $pdfdoc): string
    {
        $prepared = $this->prepareDocumentForSignature($pdfdoc);
        $pdfdoc = $prepared['pdfdoc'];
        $byteRange = $prepared['byte_range'];
        $token = $this->requestSignatureTimestampToken($pdfdoc);
        $signature = $this->convertBinarySignatureToHex($token);

        return \substr($pdfdoc, 0, $byteRange[1]) . '<' . $signature . '>' . \substr($pdfdoc, $byteRange[1]);
    }

    /**
     * @throws \Throwable
     */
    protected function getCertificateSourceContent(string $source): string
    {
        if ($source === '') {
            return '';
        }

        if (\str_contains($source, '-----BEGIN CERTIFICATE-----')) {
            return $source;
        }

        $data = $this->file->getFileData($source);
        if ($data === false && !\str_starts_with($source, 'file://')) {
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
    /**
     * Extract PEM-encoded certificates from text.
     *
     * @param string $content Certificate bundle text.
     *
     * @return array<int, string>
     * @throws \Throwable
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

        /** @var array<int, string> */
        return array_values($matches[0] ?? []);
    }

    /**
     * Extract URLs of a given type from a certificate extension text block.
     *
     * Pass 'OCSP' to extract OCSP responder URIs from Authority Information Access,
     * or 'CRL' to extract CRL Distribution Point URIs.
     *
     * @return array<int, string>
     */
    /**
     * Send an OCSP request via HTTP POST.
     * Override in subclasses or test doubles to inject a canned response.
     *
     * @throws \Throwable
     */
    protected function postOcspRequest(string $url, string $request): string
    {
        $context = \stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/ocsp-request\r\nContent-Length: " . \strlen($request),
                'content' => $request,
                'timeout' => 30,
                // Do not follow redirects:
                // only the initial URL is checked against the allowlist.
                'follow_location' => 0,
                'max_redirects' => 0,
            ],
        ]);
        if (!$this->file->isValidURL($url)) {
            throw new PdfException('invalid OCSP URL');
        }
        $response = \file_get_contents($url, false, $context);
        if ($response === false) {
            throw new PdfException('OCSP request failed: ' . $url);
        }

        return $response;
    }

    /**
     * Fetch a CRL via HTTP GET.
     * Override in subclasses or test doubles to inject canned data.
     *
     * @throws \Throwable
     */
    protected function getCrlData(string $url): string
    {
        $data = $this->file->getFileData($url);
        if ($data === false) {
            throw new PdfException('CRL fetch failed: ' . $url);
        }

        return $data;
    }

    /**
     * Submit an RFC3161 TimeStampReq to the configured TSA endpoint.
     *
     * @param string $request DER-encoded timestamp request.
     *
     * @return string DER-encoded timestamp response.
     *
     * @throws Exception
     */
    protected function postTimestampRequest(string $request): string
    {
        $host = $this->sigtimestamp['host'];
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
            $auth = $this->sigtimestamp['username'] . ':' . $this->sigtimestamp['password'];
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
                CURLOPT_SSL_VERIFYPEER => $this->sigtimestamp['verify_peer'],
                CURLOPT_SSL_VERIFYHOST => $this->sigtimestamp['verify_peer'] ? 2 : 0,
            ];

            if ($this->sigtimestamp['cert'] !== '') {
                $opts[CURLOPT_CAINFO] = $this->sigtimestamp['cert'];
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
                // Do not follow redirects: only the initial host is validated
                // against the allowlist, so following a redirect could reach a
                // non-allowlisted (e.g. internal) target.
                'follow_location' => 0,
                'max_redirects' => 0,
            ],
            'ssl' => [
                'verify_peer' => $this->sigtimestamp['verify_peer'],
                'verify_peer_name' => $this->sigtimestamp['verify_peer'],
                'cafile' => $this->sigtimestamp['cert'],
            ],
        ]);

        if (!$this->file->isValidURL($host)) {
            throw new PdfException('invalid URL');
        }

        \set_error_handler(static fn(): bool => true);
        $response = \file_get_contents($host, false, $context);
        \restore_error_handler();
        if ($response === false) {
            throw new PdfException('Unable to request TSA timestamp');
        }

        return $response;
    }

    /** @param int<0, max> $value */
    protected function asn1EncodeBase128Int(int $value): string
    {
        $bytes = [$value & 0x7F];
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
     * Returns the PDF signarure entry.
     *
     * @throws \Throwable
     */
    protected function getOutSignature(): string
    {
        if (!$this->sign || $this->signature['cert_type'] < 0) {
            return '';
        }

        // widget annotation for signature
        $soid = $this->objid['signature'];
        $oid = $soid + 1;
        $page = $this->page->getPage($this->signature['appearance']['page']);
        $sigRect = \preg_split('/\s+/', \trim($this->signature['appearance']['rect']));
        $sigWidth = 0.0;
        $sigHeight = 0.0;
        if ($sigRect !== false && \count($sigRect) >= 4) {
            $x0Raw = $sigRect[0];
            $y0Raw = $sigRect[1] ?? null;
            $x1Raw = $sigRect[2] ?? null;
            $y1Raw = $sigRect[3] ?? null;
            $x0 = \is_numeric($x0Raw) ? \floatval($x0Raw) : 0.0;
            $y0 = \is_numeric($y0Raw) ? \floatval($y0Raw) : 0.0;
            $x1 = \is_numeric($x1Raw) ? \floatval($x1Raw) : 0.0;
            $y1 = \is_numeric($y1Raw) ? \floatval($y1Raw) : 0.0;
            $sigWidth = \abs($x1 - $x0);
            $sigHeight = \abs($y1 - $y0);
        }

        list($sigAppearance, $sigAppearanceXObj) = $this->getSignatureAppearanceStream($sigWidth, $sigHeight);
        $pageObjN = (int) $page['n'];

        $stringEncoder =
            /** @throws \Throwable */
            fn(string $text, int $obj): string => $this->getOutTextString($text, $obj, true);

        // Signature widget annotation (references the /Sig value object via /V).
        $signWidget = new SignWidget();
        $out = $signWidget->annotation(
            $soid,
            $this->signature['appearance']['rect'],
            $pageObjN,
            $this->signature['appearance']['name'],
            $oid,
            $sigAppearance,
            $stringEncoder,
        );
        $out .= $sigAppearanceXObj;

        // /Reference transform (DocMDP for a certification signature, UR3 for an
        // approval one); omitted entirely for an author signature ('A').
        $reference = '';
        if ($this->signature['approval'] !== 'A') {
            $params = $this->signature['cert_type'] > 0
                ? $this->getOutSignatureDocMDP()
                : $this->getOutSignatureUserRights();
            $reference = ' /Reference [ << /Type /SigRef' . $params . ' >> ]';
        }

        // /Sig value object (the byte skeleton + ByteRange/Contents placeholders).
        $signSignature = new SignSignature();
        $out .= $signSignature->valueObject(
            $oid,
            $this->signatureSubFilter(),
            $reference,
            $this->signature['info'],
            $this->getOutDateTimeString($this->docmodtime, $oid),
            $this->signatureContentsLength(),
            $stringEncoder,
        );

        return $out;
    }

    /**
     * Returns the signature widget Appearance Stream.
     *
     * @return array{string, string}
     * @throws \Throwable
     */
    protected function getSignatureAppearanceStream(float $width = 0, float $height = 0): array
    {
        $appearance = $this->signature['appearance'];

        $out = '';
        if (isset($appearance['as']) && $appearance['as'] !== '') {
            $out .= ' /AS /' . $appearance['as'];
        }

        if (
            (!isset($appearance['ap']) || $appearance['ap'] === '')
            && isset($appearance['xobj'])
            && $appearance['xobj'] !== ''
        ) {
            $xobjid = $appearance['xobj'];
            if (isset($this->xobjects[$xobjid]) && $this->xobjects[$xobjid] !== []) {
                $xobjw = $this->xobjects[$xobjid]['w'] ?? 0.0;
                $xobjh = $this->xobjects[$xobjid]['h'] ?? 0.0;
                if ($xobjw > 0.0 && $xobjh > 0.0) {
                    $sx = $width > 0.0 ? $width / $xobjw : 1.0;
                    $sy = $height > 0.0 ? $height / $xobjh : 1.0;
                    $appearance['ap'] = [
                        'n' => \sprintf('q %F 0 0 %F 0 0 cm /%s Do Q', $sx, $sy, $xobjid),
                    ];
                }
            }
        }

        if (!isset($appearance['ap']) || $appearance['ap'] === '') {
            return [$out, ''];
        }

        $apxout = '';
        $out .= ' /AP <<';
        if (!\is_array($appearance['ap'])) {
            $out .= $appearance['ap'];
            return [$out . ' >>', $apxout];
        }

        foreach ($appearance['ap'] as $mode => $def) {
            $out .= ' /' . \strtoupper($mode);
            if (\is_array($def)) {
                $out .= ' <<';
                foreach ($def as $apstate => $stream) {
                    $apxout .= $this->getOutAPXObjects($width, $height, $stream);
                    $out .= ' /' . $apstate . ' ' . $this->pon . ' 0 R';
                }

                $out .= ' >>';
                continue;
            }

            $apxout .= $this->getOutAPXObjects($width, $height, $def);
            $out .= ' ' . $this->pon . ' 0 R';
        }

        return [
            $out . ' >>',
            $apxout,
        ];
    }

    /**
     * Returns the PDF signarure entry.
     */
    protected function getOutSignatureDocMDP(): string
    {
        $signature = $this->signature + ['cert_type' => 0];
        $certType = $signature['cert_type'];
        if ($certType === 0) {
            return '';
        }

        return (
            ' /TransformMethod /DocMDP '
            . '/TransformParams <<'
            . ' /Type /TransformParams'
            . ' /P '
            . $certType
            . ' /V /1.2'
            . ' >>'
        );
    }

    /**
     * Returns the PDF signarure entry.
     */
    protected function getOutSignatureUserRights(): string
    {
        $out = ' /TransformMethod /UR3 /TransformParams << /Type /TransformParams /V /2.2';
        if ($this->userrights['document'] !== '') {
            $out .= ' /Document[' . $this->userrights['document'] . ']';
        }

        if ($this->userrights['form'] !== '') {
            $out .= ' /Form[' . $this->userrights['form'] . ']';
        }

        if ($this->userrights['signature'] !== '') {
            $out .= ' /Signature[' . $this->userrights['signature'] . ']';
        }

        if ($this->userrights['annots'] !== '') {
            $out .= ' /Annots[' . $this->userrights['annots'] . ']';
        }

        if ($this->userrights['ef'] !== '') {
            $out .= ' /EF[' . $this->userrights['ef'] . ']';
        }

        if ($this->userrights['formex'] !== '') {
            $out .= ' /FormEX[' . $this->userrights['formex'] . ']';
        }

        return $out . ' >>';
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
        if ($this->patterns === []) {
            return '';
        }

        $out = ' /Pattern <<';
        foreach ($this->patterns as $pid => $pattern) {
            $pattern += ['n' => 0];
            $patternN = $pattern['n'];
            if ($patternN === 0) {
                continue;
            }
            $out .= ' /' . $pid . ' ' . $patternN . ' 0 R';
        }

        return $out . ' >>';
    }

    /**
     * Get the PDF output string for Layer resources dictionary.
     */
    protected function getLayerDict(): string
    {
        if ($this->pdflayer === []) {
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
        if ($val === true || $val === 1 || $val === '1' || $val === 'true' || $val === 'on' || $val === 'yes') {
            return 'ON';
        }

        return 'OFF';
    }

    /**
     * Render the PDF in the browser or output the RAW data in the CLI.
     *
     * @param string $rawpdf Raw PDF data string from getOutPDFString().
     *
     * @throws \Throwable
     */
    public function renderPDF(string $rawpdf = ''): void
    {
        if (PHP_SAPI === 'cli') {
            $this->writeRawPdfOutput($rawpdf);
            return;
        }

        if (\headers_sent()) {
            throw new PdfException(
                'The PDF file cannot be sent because some data has already been output to the browser.',
            );
        }

        \header('Content-Type: application/pdf');
        \header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        \header('Pragma: public');
        \header('Expires: Sat, 01 Jan 2000 01:00:00 GMT'); // Date in the past
        \header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        \header(
            'Content-Disposition: inline; filename="'
            . $this->encpdffilename
            . '"; filename*=UTF-8\'\''
            . $this->encpdffilename,
        );
        if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
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
     * @throws \Throwable
     */
    public function downloadPDF(string $rawpdf = ''): void
    {
        if (\ob_get_contents()) {
            throw new PdfException('The PDF file cannot be sent, some data has already been output to the browser.');
        }

        if (\headers_sent()) {
            throw new PdfException(
                'The PDF file cannot be sent because some data has already been output to the browser.',
            );
        }

        \header('Content-Description: File Transfer');
        \header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        \header('Pragma: public');
        \header('Expires: Sat, 01 Jan 2000 01:00:00 GMT'); // Date in the past
        \header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        // force download dialog
        \header('Content-Type: application/pdf');
        if (!\str_contains(PHP_SAPI, 'cgi')) {
            \header('Content-Type: application/force-download', false);
            \header('Content-Type: application/octet-stream', false);
            \header('Content-Type: application/download', false);
        }

        // use the Content-Disposition header to supply a recommended filename
        \header(
            'Content-Disposition: attachment; filename="'
            . $this->encpdffilename
            . '";'
            . " filename*=UTF-8''"
            . $this->encpdffilename,
        );
        \header('Content-Transfer-Encoding: binary');
        if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
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
            if ($written === false || $written < 1) {
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
     * @throws \Throwable
     */
    public function savePDF(string $path = '', string $rawpdf = ''): void
    {
        $realpath = \realpath($path);
        if ($realpath === false || !\is_dir($realpath) || !\is_writable($realpath)) {
            throw new FileException('Invalid or not writable output directory: ' . $path);
        }

        $filepath = \implode('/', [$realpath, $this->pdffilename]);
        $fhd = $this->file->fopenLocal($filepath, 'wb');

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
        return (
            'Content-Type: application/pdf;'
            . "\r\n"
            . ' name="'
            . $this->encpdffilename
            . '"'
            . "\r\n"
            . 'Content-Transfer-Encoding: base64'
            . "\r\n"
            . 'Content-Disposition: attachment;'
            . "\r\n"
            . ' filename="'
            . $this->encpdffilename
            . '"'
            . "\r\n\r\n"
            . \chunk_split(\base64_encode($rawpdf), 76, "\r\n")
        );
    }
}
