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
 */
class TestableOutput extends \Com\Tecnick\Pdf\Tcpdf
{
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

        /** @var TAnnot $annot */
        $annot = \array_replace_recursive($base, $annotData);
        return $annot;
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

    public function exposeGetOutBookmarks(): string
    {
        return $this->getOutBookmarks();
    }

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

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeMovie(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeMovie($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeScreen(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeScreen($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypePrintermark(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypePrintermark($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
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

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetAnnotationRadioButtons(array $annot): string
    {
        return $this->getAnnotationRadioButtons($this->toAnnot($annot));
    }

    /**
     * @phpstan-param array<string, mixed> $annot
     * @phpstan-return array{string, string}
     */
    public function exposeGetAnnotationAppearanceStream(array $annot, float $width = 0, float $height = 0): array
    {
        return $this->getAnnotationAppearanceStream($this->toAnnot($annot), $width, $height);
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationMarkups(array $annot, int $oid): string
    {
        return $this->getOutAnnotationMarkups($this->toAnnot($annot), $oid);
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtype(array $annot, int $pagenum, int $oid, int $key): string
    {
        return $this->getOutAnnotationOptSubtype($this->toAnnot($annot), $pagenum, $oid, $key);
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeText(array $annot): string
    {
        return $this->getOutAnnotationOptSubtypeText($this->toAnnot($annot));
    }

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeLink(array $annot, int $pagenum, int $oid): string
    {
        return $this->getOutAnnotationOptSubtypeLink($this->toAnnot($annot), $pagenum, $oid);
    }

    /** @phpstan-param array<string, mixed> $annot */
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

    /** @phpstan-param array<string, mixed> $annot */
    public function exposeGetOutAnnotationOptSubtypeWidget(array $annot, int $oid): string
    {
        return $this->getOutAnnotationOptSubtypeWidget($this->toAnnot($annot), $oid);
    }

    public function exposeGetOutPDFHeader(): string
    {
        return $this->getOutPDFHeader();
    }

    public function exposeGetOutPDFBody(): string
    {
        return $this->getOutPDFBody();
    }

    public function exposeGetOutCatalog(): string
    {
        return $this->getOutCatalog();
    }

    public function exposeGetOutICC(): string
    {
        return $this->getOutICC();
    }

    public function exposeGetOutputIntentsSrgb(): string
    {
        return $this->getOutputIntentsSrgb();
    }

    public function exposeGetOutputIntentsPdfX(): string
    {
        return $this->getOutputIntentsPdfX();
    }

    public function exposeGetOutputIntents(): string
    {
        return $this->getOutputIntents();
    }

    public function exposeGetPDFLayers(): string
    {
        return $this->getPDFLayers();
    }

    public function exposeGetOutOCG(): string
    {
        return $this->getOutOCG();
    }

    public function exposeGetOutAPXObjects(float $width = 0, float $height = 0, string $stream = ''): string
    {
        return $this->getOutAPXObjects($width, $height, $stream);
    }

    public function exposeGetOutXObjects(): string
    {
        return $this->getOutXObjects();
    }

    public function exposeGetOutEmbeddedFiles(): string
    {
        return $this->getOutEmbeddedFiles();
    }

    public function exposeGetOutAnnotations(): string
    {
        return $this->getOutAnnotations();
    }

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

    public function exposeWritePreparedDocumentForSignature(string $pdfdoc): string
    {
        return $this->writePreparedDocumentForSignature($pdfdoc);
    }

    public function exposeCreatePkcs7SignatureFile(string $tempdoc): string
    {
        return $this->createPkcs7SignatureFile($tempdoc);
    }

    public function exposeExtractSignatureFromPkcs7File(string $tempsign, int $pdfdocLength): string
    {
        return $this->extractSignatureFromPkcs7File($tempsign, $pdfdocLength);
    }

    public function exposeConvertBinarySignatureToHex(string $signature): string
    {
        return $this->convertBinarySignatureToHex($signature);
    }

    public function exposeSignDocument(string $pdfdoc): string
    {
        return $this->signDocument($pdfdoc);
    }

    public function exposeApplySignatureTimestamp(string $signature): string
    {
        return $this->applySignatureTimestamp($signature);
    }

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

    public function exposeGetOutSignatureInfo(int $oid): string
    {
        return $this->getOutSignatureInfo($oid);
    }

    /** @phpstan-param array<string, int|array<int>> $objid */
    public function setOutputState(int $pon, array $objid, string $fileid = 'ABC123', int $encryptObjId = 0): void
    {
        $this->pon = $pon;
        foreach ($objid as $key => $value) {
            if (!\array_key_exists($key, $this->objid)) {
                continue;
            }

            if ($key === 'form') {
                if (\is_array($value)) {
                    /** @var array<int> $form */
                    $form = \array_map(static fn ($objId): int => (int) $objId, $value);
                    $this->objid['form'] = $form;
                }

                continue;
            }

            if (\is_int($value)) {
                $this->objid[$key] = $value;
            }
        }
        $this->fileid = $fileid;

        $ref = new \ReflectionObject($this->encrypt);
        $prop = $ref->getProperty('encryptdata');
        $prop->setAccessible(true);
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
}
