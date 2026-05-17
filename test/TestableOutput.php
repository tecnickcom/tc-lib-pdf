<?php

/**
 * TestableOutput.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

/**
 * @phpstan-import-type TAnnot from \Com\Tecnick\Pdf\Output
 * @phpstan-import-type TObjID from \Com\Tecnick\Pdf\Output
 * @phpstan-import-type TOutline from \Com\Tecnick\Pdf\Output
 * @phpstan-import-type TSignDocPrepared from \Com\Tecnick\Pdf\Output
 * @phpstan-import-type TValidationMaterial from \Com\Tecnick\Pdf\Output
 * @phpstan-import-type TSVGMaskObject from \Com\Tecnick\Pdf\Base
 */
class TestableOutput extends \Com\Tecnick\Pdf\Tcpdf
{
    protected string $mockTsaResp = '';
    protected bool $mockTsaThrows = false;
    protected string $tsaReq = '';
    protected string $mockOcspResp = '';
    protected bool $mockOcspThrows = false;
    protected string $ocspReq = '';
    protected string $ocspUrl = '';
    protected string $mockCrlResp = '';
    protected bool $mockCrlThrows = false;
    protected string $crlUrl = '';

    /**
     * @phpstan-param array<string, mixed> $annotData
     * @phpstan-return TAnnot
     */
    private function toAnnot(array $annotData): array
    {
        $base = [
            'n' => 1,
            'x' => 0.0,
            'y' => 0.0,
            'w' => 0.0,
            'h' => 0.0,
            'txt' => '',
            'opt' => ['subtype' => 'text'],
        ];

        /** @var TAnnot */
        return \array_replace_recursive($base, $annotData);
    }

    /** @phpstan-return array<int> */
    public function exposeGetPDFObjectOffsets(string $data): array
    {
        return $this->getPDFObjectOffsets($data);
    }

    /** @phpstan-param array<int> $offset */
    public function exposeGetOutPDFXref(array $offset): string
    {
        return $this->getOutPDFXref($offset);
    }

    public function exposeGetOutPDFTrailer(): string
    {
        return $this->getOutPDFTrailer();
    }

    /** @phpstan-param array<float|int> $color */
    public function exposeGetColorStringFromPercArray(array $color): string
    {
        return self::getColorStringFromPercArray($color);
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetAnnotationBorder(array $annot): string
    {
        return $this->getAnnotationBorder($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationFlags(array $annot): string
    {
        return $this->getOutAnnotationFlags($this->toAnnot($annot));
    }

    /** @phpstan-param array<string>|int $flags */
    public function exposeGetAnnotationFlagsCode(int|array $flags): int
    {
        return $this->getAnnotationFlagsCode($flags);
    }

    public function exposeGetOnOff(mixed $val): string
    {
        return $this->getOnOff($val);
    }

    /** @throws \Throwable */
    public function exposeGetOutDestinations(): string
    {
        return $this->getOutDestinations();
    }

    public function exposeSortBookmarks(): void
    {
        $this->sortBookmarks();
    }

    public function exposeProcessPrevNextBookmarks(): int
    {
        return $this->processPrevNextBookmarks();
    }

    /** @throws \Throwable */
    public function exposeGetOutBookmarks(): string
    {
        return $this->getOutBookmarks();
    }

    /** @throws \Throwable */
    public function exposeGetOutJavascript(): string
    {
        return $this->getOutJavascript();
    }

    public function exposeGetXObjectDict(): string
    {
        return $this->getXObjectDict();
    }

    public function exposeGetLayerDict(): string
    {
        return $this->getLayerDict();
    }

    public function exposeGetOutResourcesDict(): string
    {
        return $this->getOutResourcesDict();
    }

    public function exposeGetPatternStreamResourceDict(string $stream): string
    {
        return $this->getPatternStreamResourceDict($stream);
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeLine(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeLine($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeSquare(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeSquare($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeCircle(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeCircle($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypePolygon(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypePolygon($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypePolyline(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypePolyline($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeHighlight(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeHighlight($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeUnderline(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeUnderline($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeSquiggly(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeSquiggly($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeStrikeout(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeStrikeout($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeStamp(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeStamp($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeCaret(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeCaret($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeInk(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeInk($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypePopup(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypePopup($this->toAnnot($annot));
    }

    /**
     * @phpstan-param array<string, mixed> $annot
     * @throws \Throwable
     */
    public function exposeGetOutAnnotationOptSubtypeMovie(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeMovie($this->toAnnot($annot));
    }

    /**
     * @phpstan-param array<string, mixed> $annot
     * @throws \Throwable
     */
    public function exposeGetOutAnnotationOptSubtypeScreen(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeScreen($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypePrintermark(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypePrintermark($this->toAnnot($annot));
    }

    /**
     * @phpstan-param array<string, mixed> $annot
     * @throws \Throwable
     */
    public function exposeGetOutAnnotationOptSubtypeRedact(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeRedact($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeTrapnet(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeTrapnet($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeWatermark(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeWatermark($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtype3D(array $annot): string
    {
        return $this->getOutAnnotationOptSubtype3D($this->toAnnot($annot));
    }

    /**
     * @phpstan-param array<string, mixed> $annot
     * @throws \Throwable
     */
    public function exposeGetAnnotationRadioButtons(array $annot): string
    {
        return $this->getAnnotationRadioButtons($this->toAnnot($annot));
    }

    /**
     * @phpstan-param array<string, mixed> $annot
     * @return array{string, string}
     * @throws \Throwable
     */
    public function exposeGetAnnotationAppearanceStream(array $annot, float $width = 0, float $height = 0): array
    {
        $method = new \ReflectionMethod($this, 'getAnnotationAppearanceStream');
        /** @var array{string, string} */
        return $method->invokeArgs($this, [$this->toAnnot($annot), $width, $height]);
    }

    /**
     * @phpstan-param array<string, mixed> $annot
     * @throws \Throwable
     */
    public function exposeGetOutAnnotationMarkups(array $annot, int $oid): string
    {
        return $this->getOutAnnotationMarkups($this->toAnnot($annot), $oid);
    }

    /**
     * @phpstan-param array<string, mixed> $annot
     * @throws \Throwable
     */
    public function exposeGetOutAnnotationOptSubtype(array $annot, int $pagenum, int $oid, int $key): string
    {
        return $this->getOutAnnotationOptSubtype($this->toAnnot($annot), $pagenum, $oid, $key);
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeText(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeText($this->toAnnot($annot));
    }

    /**
     * @phpstan-param array<string, mixed> $annot
     * @throws \Throwable
     */
    public function exposeGetOutAnnotationOptSubtypeLink(array $annot, int $pagenum, int $oid): string
    {
        return $this->getOutAnnotationOptSubtypeLink($this->toAnnot($annot), $pagenum, $oid);
    }

    /**
     * @phpstan-param array<string, mixed> $annot
     * @throws \Throwable
     */
    public function exposeGetOutAnnotationOptSubtypeFreetext(array $annot, int $oid): string
    {
        return $this->getOutAnnotationOptSubtypeFreetext($this->toAnnot($annot), $oid);
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeFileattachment(array $annot, int $key): string
    {
        return $this->getOutAnnotationOptSubtypeFileattachment($this->toAnnot($annot), $key);
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeSound(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeSound($this->toAnnot($annot));
    }

    /**
     * @phpstan-param array<string, mixed> $annot
     * @throws \Throwable
     */
    public function exposeGetOutAnnotationOptSubtypeWidget(array $annot, int $oid): string
    {
        return $this->getOutAnnotationOptSubtypeWidget($this->toAnnot($annot), $oid);
    }

    public function exposeGetOutPDFHeader(): string
    {
        return $this->getOutPDFHeader();
    }

    /** @throws \Throwable */
    public function exposeGetOutPDFBody(): string
    {
        return $this->getOutPDFBody();
    }

    /** @throws \Throwable */
    public function exposeGetOutCatalog(): string
    {
        return $this->getOutCatalog();
    }

    /** @throws \Throwable */
    public function exposeGetOutICC(): string
    {
        return $this->getOutICC();
    }

    /** @throws \Throwable */
    public function exposeGetOutputIntentsSrgb(): string
    {
        return $this->getOutputIntentsSrgb();
    }

    /** @throws \Throwable */
    public function exposeGetOutputIntentsPdfX(): string
    {
        return $this->getOutputIntentsPdfX();
    }

    /** @throws \Throwable */
    public function exposeGetOutputIntents(): string
    {
        return $this->getOutputIntents();
    }

    /** @throws \Throwable */
    public function exposeGetPDFLayers(): string
    {
        return $this->getPDFLayers();
    }

    /** @throws \Throwable */
    public function exposeGetOutOCG(): string
    {
        return $this->getOutOCG();
    }

    /** @throws \Throwable */
    public function exposeGetOutAPXObjects(float $width = 0, float $height = 0, string $stream = ''): string
    {
        return $this->getOutAPXObjects($width, $height, $stream);
    }

    /** @throws \Throwable */
    public function exposeGetOutXObjects(): string
    {
        return $this->getOutXObjects();
    }

    /** @throws \Throwable */
    public function exposeGetOutPatterns(): string
    {
        return $this->getOutPatterns();
    }

    /** @throws \Throwable */
    public function exposeGetOutEmbeddedFiles(): string
    {
        return $this->getOutEmbeddedFiles();
    }

    /** @throws \Throwable */
    public function exposeGetOutAnnotations(): string
    {
        return $this->getOutAnnotations();
    }

    /** @throws \Throwable */
    public function exposeGetOutSignatureFields(): string
    {
        return $this->getOutSignatureFields();
    }

    /**
     * @phpstan-return TSignDocPrepared
     */
    public function exposePrepareDocumentForSignature(string $pdfdoc): array
    {
        return $this->prepareDocumentForSignature($pdfdoc);
    }

    /** @throws \Throwable */
    public function exposeWritePreparedDocumentForSignature(string $pdfdoc): string
    {
        return $this->writePreparedDocumentForSignature($pdfdoc);
    }

    /** @throws \Throwable */
    public function exposeCreatePkcs7SignatureFile(string $tempdoc): string
    {
        return $this->createPkcs7SignatureFile($tempdoc);
    }

    /** @throws \Throwable */
    public function exposeExtractSignatureFromPkcs7File(string $tempsign, int $pdfdocLength): string
    {
        return $this->extractSignatureFromPkcs7File($tempsign, $pdfdocLength);
    }

    /** @throws \Throwable */
    public function exposeConvertBinarySignatureToHex(string $signature): string
    {
        return $this->convertBinarySignatureToHex($signature);
    }

    /** @throws \Throwable */
    public function exposeBuildTimestampRequest(string $signature): string
    {
        return $this->buildTimestampRequest($signature);
    }

    /**
     * @phpstan-return TValidationMaterial
     * @throws \Throwable
     */
    public function exposeCollectValidationMaterial(): array
    {
        return $this->collectValidationMaterial();
    }

    /** @throws \Throwable */
    public function exposeExtractTimestampTokenFromResponse(string $response): string
    {
        return $this->extractTimestampTokenFromResponse($response);
    }

    public function setMockTimestampResponse(string $response): void
    {
        $this->mockTsaResp = $response;
    }

    public function setMockTimestampThrows(bool $throws): void
    {
        $this->mockTsaThrows = $throws;
    }

    public function getCapturedTimestampRequest(): string
    {
        return $this->tsaReq;
    }

    public function setMockOcspResponse(string $response): void
    {
        $this->mockOcspResp = $response;
    }

    public function setMockOcspThrows(bool $throws): void
    {
        $this->mockOcspThrows = $throws;
    }

    public function getCapturedOcspRequest(): string
    {
        return $this->ocspReq;
    }

    public function getCapturedOcspUrl(): string
    {
        return $this->ocspUrl;
    }

    public function setMockCrlResponse(string $response): void
    {
        $this->mockCrlResp = $response;
    }

    public function setMockCrlThrows(bool $throws): void
    {
        $this->mockCrlThrows = $throws;
    }

    public function getCapturedCrlUrl(): string
    {
        return $this->crlUrl;
    }

    protected function postTimestampRequest(string $request): string
    {
        $this->tsaReq = $request;
        if ($this->mockTsaThrows) {
            throw new \Com\Tecnick\Pdf\Exception('mock tsa transport error');
        }

        if ($this->mockTsaResp !== '') {
            return $this->mockTsaResp;
        }

        return parent::postTimestampRequest($request);
    }

    protected function postOcspRequest(string $url, string $request): string
    {
        $this->ocspUrl = $url;
        $this->ocspReq = $request;
        if ($this->mockOcspThrows) {
            throw new \Com\Tecnick\Pdf\Exception('mock ocsp transport error');
        }

        if ($this->mockOcspResp !== '') {
            return $this->mockOcspResp;
        }

        return parent::postOcspRequest($url, $request);
    }

    protected function getCrlData(string $url): string
    {
        $this->crlUrl = $url;
        if ($this->mockCrlThrows) {
            throw new \Com\Tecnick\Pdf\Exception('mock crl transport error');
        }

        if ($this->mockCrlResp !== '') {
            return $this->mockCrlResp;
        }

        return parent::getCrlData($url);
    }

    /** @throws \Throwable */
    public function exposeSignDocument(string $pdfdoc): string
    {
        return $this->signDocument($pdfdoc);
    }

    /** @throws \Throwable */
    public function exposeApplySignatureTimestamp(string $signature): string
    {
        return $this->applySignatureTimestamp($signature);
    }

    /** @throws \Throwable */
    public function exposeGetOutSignature(): string
    {
        return $this->getOutSignature();
    }

    public function exposeGetOutSignatureDocMDP(): string
    {
        return $this->getOutSignatureDocMDP();
    }

    public function exposeGetOutSignatureUserRights(): string
    {
        return $this->getOutSignatureUserRights();
    }

    /** @throws \Throwable */
    public function exposeGetOutSignatureInfo(int $oid): string
    {
        return $this->getOutSignatureInfo($oid);
    }

    /** @phpstan-param array<string, int|array<int>> $objid */
    public function setOutputState(int $pon, array $objid, string $fileid = 'ABC123', int $encryptObjId = 0): void
    {
        $this->pon = $pon;
        $form = $objid['form'] ?? null;
        if (\is_array($form)) {
            $typedForm = \array_map(static fn($objId): int => (int) $objId, $form);
            $this->objid['form'] = $typedForm;
        }

        $intKeys = ['catalog', 'dests', 'dss', 'info', 'pages', 'resdic', 'signature', 'srgbicc', 'xmp'];
        foreach ($intKeys as $key) {
            $value = $objid[$key] ?? null;
            if (!\is_int($value)) {
                continue;
            }

            switch ($key) {
                case 'catalog':
                    $this->objid['catalog'] = $value;
                    break;
                case 'dests':
                    $this->objid['dests'] = $value;
                    break;
                case 'dss':
                    $this->objid['dss'] = $value;
                    break;
                case 'info':
                    $this->objid['info'] = $value;
                    break;
                case 'pages':
                    $this->objid['pages'] = $value;
                    break;
                case 'resdic':
                    $this->objid['resdic'] = $value;
                    break;
                case 'signature':
                    $this->objid['signature'] = $value;
                    break;
                case 'srgbicc':
                    $this->objid['srgbicc'] = $value;
                    break;
                case 'xmp':
                    $this->objid['xmp'] = $value;
                    break;
            }
        }
        $this->fileid = $fileid;

        $ref = new \ReflectionObject($this->encrypt);
        $prop = $ref->getProperty('encryptdata');
        /** @var array<string, mixed> $data */
        $data = $prop->getValue($this->encrypt);
        $data['objid'] = $encryptObjId;
        $prop->setValue($this->encrypt, $data);
    }

    public function setPdfaMode(int $pdfa): void
    {
        $this->pdfa = $pdfa;
    }

    /** @phpstan-return array<int, TOutline> */
    public function getOutlinesState(): array
    {
        return $this->outlines;
    }

    public function getJavascriptTree(): string
    {
        return $this->jstree;
    }

    /** @throws \Throwable */
    public function exposeGetOutSVGMasks(): string
    {
        return $this->getOutSVGMasks();
    }

    public function exposeGetSVGMaskExtGStateEntries(): string
    {
        return $this->getSVGMaskExtGStateEntries();
    }

    /** @phpstan-param array<string, array<string, mixed>> $masks */
    public function setSvgMasks(array $masks): void
    {
        /** @var array<string, TSVGMaskObject> $typedMasks */
        $typedMasks = [];
        foreach ($masks as $key => $mask) {
            if (!isset($mask['bbox']) || !\is_array($mask['bbox'])) {
                continue;
            }
            $bbox = $mask['bbox'];

            $bbox0 = isset($bbox[0]) && \is_numeric($bbox[0]) ? (float) $bbox[0] : 0.0;
            $bbox1 = isset($bbox[1]) && \is_numeric($bbox[1]) ? (float) $bbox[1] : 0.0;
            $bbox2 = isset($bbox[2]) && \is_numeric($bbox[2]) ? (float) $bbox[2] : 0.0;
            $bbox3 = isset($bbox[3]) && \is_numeric($bbox[3]) ? (float) $bbox[3] : 0.0;

            $typedMasks[$key] = [
                'id' => isset($mask['id']) ? (string) $mask['id'] : $key,
                'stream' => isset($mask['stream']) ? (string) $mask['stream'] : '',
                'bbox' => [
                    $bbox0,
                    $bbox1,
                    $bbox2,
                    $bbox3,
                ],
                'gs_n' => (int) ($mask['gs_n'] ?? 0),
            ];
        }

        $this->svgmasks = $typedMasks;
    }

    /** @return array<string, mixed> */
    public function getSvgMasks(): array
    {
        return $this->svgmasks;
    }

    /**
     * @param array<string> $names
     */
    public function exposeExtractNamedResourceRefs(string $dict, array $names): string
    {
        return $this->extractNamedResourceRefs($dict, $names);
    }

    /** @throws \Throwable */
    public function exposeGetOutStructTreeRoot(): string
    {
        return $this->getOutStructTreeRoot();
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationRD(array $annot): string
    {
        return $this->getOutAnnotationRectDifferences($annot);
    }

    public function exposeSetPageStructParents(string $pdfpages): string
    {
        return $this->setPageStructParents($pdfpages);
    }

    /** @throws \Throwable */
    public function exposePostTimestampRequest(string $request): string
    {
        return $this->postTimestampRequest($request);
    }

    /** @throws \Throwable */
    public function exposeParentPostTimestampRequest(string $request): string
    {
        return parent::postTimestampRequest($request);
    }

    /** @throws \Throwable */
    public function exposeGetCertificateSourceContent(string $source): string
    {
        return $this->getCertificateSourceContent($source);
    }

    /**
     * @return array<int, string>
     * @throws \Throwable
     */
    public function exposeExtractPemCertificates(string $content): array
    {
        return $this->extractPemCertificates($content);
    }

    public function exposeGetPatternDict(): string
    {
        return $this->getPatternDict();
    }

    /**
     * @phpstan-param int<0, max> $value
     * @throws \Throwable
     */
    public function exposeAsn1EncodeInteger(int $value): string
    {
        return $this->asn1EncodeInteger($value);
    }

    /**
     * @phpstan-param int<0, max> $length
     * @throws \Throwable
     */
    public function exposeAsn1EncodeLength(int $length): string
    {
        return $this->asn1EncodeLength($length);
    }

    /** @throws \Throwable */
    public function exposeWriteRawPdfOutput(string $rawpdf): void
    {
        $this->writeRawPdfOutput($rawpdf);
    }
}
