<?php

/**
 * Output.php
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

use Com\Tecnick\Pdf\Exception as PdfException;
use Com\Tecnick\Pdf\Font\Output as OutFont;

/**
 * Com\Tecnick\Pdf\Output
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
abstract class Output extends \Com\Tecnick\Pdf\MetaInfo
{
    protected $pdflayer;

    protected $l;

    protected $annotation_fonts;

    protected $dests;

    protected $radiobuttonGroups;

    protected $links;

    protected $javascript;

    protected $jsobjects;

    /**
     * Returns the RAW PDF string.
     */
    public function getOutPDFString(): string
    {
        $out = $this->getOutPDFHeader()
            . $this->getOutPDFBody();
        $startxref = strlen($out);
        $offset = $this->getPDFObjectOffsets($out);
        $out .= $this->getOutPDFXref($offset)
            . $this->getOutPDFTrailer()
            . 'startxref' . "\n"
            . $startxref . "\n"
            . '%%EOF' . "\n";
        return $out . $this->signDocument($out);
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
        $out = $this->page->getPdfPages($this->pon);
        $this->objid['pages'] = $this->page->getRootObjID();
        $out .= $this->graph->getOutExtGState($this->pon);
        $this->pon = $this->graph->getObjectNumber();
        $out .= $this->getOutOCG();
        $output = new OutFont(
            $this->font->getFonts(),
            $this->pon,
            $this->encrypt
        );
        $out .= $output->getFontsBlock();
        $this->pon = $output->getObjectNumber();
        $out .= $this->image->getOutImagesBlock($this->pon);
        $this->pon = $this->image->getObjectNumber();
        $out .= $this->color->getPdfSpotObjects($this->pon);
        $out .= $this->graph->getOutGradientShaders($this->pon);
        $this->pon = $this->graph->getObjectNumber();
        $out .= $this->getOutXObjects();
        $out .= $this->getOutResourcesDict();
        $out .= $this->getOutDestinations();
        $out .= $this->getOutEmbeddedFiles();
        $out .= $this->getOutAnnotations();
        $out .= $this->getOutJavascript();
        $out .= $this->getOutBookmarks();
        $enc = $this->encrypt->getEncryptionData();
        if ($enc['encrypted']) {
            $out .= $this->encrypt->getPdfEncryptionObj($this->pon);
        }

        $out .= $this->getOutSignatureFields();
        $out .= $this->getOutSignature();
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
     * @return array - Ordered offset array for each PDF object
     */
    protected function getPDFObjectOffsets(string $data): array
    {
        preg_match_all('/(([0-9]+)[\s][0-9]+[\s]obj[\n])/i', $data, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $offset = [];
        foreach ($matches as $match) {
            $offset[($match[2][0])] = $match[2][1];
        }

        ksort($offset);
        return $offset;
    }

    /**
     * Returns the PDF XREF section.
     *
     * @param array $offset Ordered offset array for each PDF object
     */
    protected function getOutPDFXref(array $offset): string
    {
        $out = 'xref' . "\n"
            . '0 ' . ($this->pon + 1) . "\n"
            . '0000000000 65535 f ' . "\n";
        $freegen = ($this->pon + 2);
        $lastobj = array_key_last($offset);
        for ($idx = 1; $idx <= $lastobj; ++$idx) {
            if (isset($offset[$idx])) {
                $out .= sprintf('%010d 00000 n ' . "\n", $offset[$idx]);
            } else {
                $out .= sprintf('0000000000 %05d f ' . "\n", $freegen);
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
        if (! empty($enc['objid'])) {
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
        $icc = file_get_contents(__DIR__ . '/include/sRGB.icc.z');
        if ($icc === false) {
            throw new PdfException('Unable to read sRGB.icc.z file');
        }

        $icc = $this->encrypt->encryptString($icc, $oid);
        return $out . ('<< /N 3 /Filter /FlateDecode /Length ' . strlen($icc)
            . ' >>'
            . ' stream' . "\n"
            . $icc . "\n"
            . 'endstream' . "\n"
            . 'endobj' . "\n");
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
    protected function getOutputIntentsPdfX(): string
    {
        $oid = $this->objid['catalog'];
        return ' /OutputIntents [<< /Type /OutputIntent /S /GTS_PDFX /OutputConditionIdentifier '
            . $this->getOutTextString('OFCOM_PO_P1_F60_95', $oid, true)
            . ' /RegistryName ' . $this->getOutTextString('http://www.color.org', $oid, true)
            . ' /Info ' . $this->getOutTextString('OFCOM_PO_P1_F60_95', $oid, true)
            . ' >>]';
    }

    protected function getOutputIntents(): string
    {
        if (empty($this->objid['catalog'])) {
            return '';
        }

        if ($this->pdfx) {
            $this->getOutputIntentsPdfX();
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
        if ($this->pdfa === 0 && $this->jstree !== '') {
            $out .= ' /JavaScript ' . $this->jstree;
        }

        if ($this->embeddedfiles !== []) {
            $out .= ' /EmbeddedFiles << /Names [';
            foreach ($this->embeddedfiles as $efname => $efdata) {
                $out .= ' ' . $this->getOutTextString($efname, $oid) . ' ' . $efdata['f'] . ' 0 R';
            }

            $out .= ' ] >>';
        }

        $out .= ' >>';

        if (! empty($this->objid['dests'])) {
            $out .= ' /Dests ' . ($this->objid['dests']) . ' 0 R';
        }

        $out .= $this->getOutViewerPref();

        if (! empty($this->display['layout'])) {
            $out .= ' /PageLayout /' . $this->display['layout'];
        }

        if (! empty($this->display['mode'])) {
            $out .= ' /PageMode /' . $this->display['mode'];
        }

        if ($this->outlines !== []) {
            $out .= ' /Outlines ' . $this->outlinerootoid . ' 0 R';
            $out .= ' /PageMode /UseOutlines';
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
        } elseif (! is_string($this->display['zoom'])) {
            $out .= sprintf(' /OpenAction [' . $fpo . ' 0 R /XYZ null null %F]', ($this->display['zoom'] / 100));
        }

        //$out .= ' /AA <<>>';
        //$out .= ' /URI <<>>';
        $out .= ' /Metadata ' . $this->objid['xmp'] . ' 0 R';
        //$out .= ' /StructTreeRoot <<>>';
        //$out .= ' /MarkInfo <<>>';

        if (! empty($this->l['a_meta_language'])) {
            $out .= ' /Lang ' . $this->getOutTextString($this->l['a_meta_language'], $oid, true);
        }

        //$out .= ' /SpiderInfo <<>>';
        $out .= $this->getOutputIntents();
        //$out .= ' /PieceInfo <<>>';
        $out .= $this->getPDFLayers();

        // AcroForm
        if (
            ! empty($this->objid['form'])
            || ($this->sign && isset($this->signature['cert_type']))
            || ! empty($this->signature['appearance']['empty'])
        ) {
            $out .= ' /AcroForm <<';
            $objrefs = '';
            if ($this->sign && isset($this->signature['cert_type'])) {
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

            if ($this->sign && isset($this->signature['cert_type'])) {
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
            $out .= ' /DA (/F' . $font['i'] . ' 0 Tf 0 g)';
            $out .= ' /Q ' . (($this->rtl) ? '2' : '0');
            //$out .= ' /XFA ';
            $out .= ' >>';

            // signatures
            if (
                $this->sign && isset($this->signature['cert_type'])
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
            $out .= '<<  /Type /OCG /Name ' . $this->getOutTextString($layer['name'], $oid, true)
                . ' /Usage <<';
            if (isset($layer['print']) && ($layer['print'] !== null)) {
                $out .= ' /Print <</PrintState /' . $this->getOnOff($layer['print']) . '>>';
            }

            $out .= ' /View <</ViewState /' . $this->getOnOff($layer['view']) . '>>'
                . ' >>'
                . ' >>' . "\n"
                . 'endobj' . "\n";
        }

        return $out;
    }

    /**
     * Returns the PDF Annotation code for Apearance Stream XObjects entry.
     *
     * @param int $width annotation width
     * @param int $height annotation height
     * @param string $stream appearance stream
     */
    protected function getOutAPXObjects(
        int $width = 0,
        int $height = 0,
        string $stream = ''
    ): string {
        $stream = trim($stream);
        $oid = ++$this->pon;
        $out = $oid . ' 0 obj' . "\n";
        $this->xobjects['AX' . $oid] = [
            'n' => $oid,
        ];
        $out .= '<< /Type /XObject /Subtype /Form /FormType 1';
        if ($this->compress) {
            $stream = gzcompress($stream);
            if ($stream === false) {
                throw new PdfException('Unable to compress stream');
            }
            $out .= ' /Filter /FlateDecode';
        }

        $stream = $this->encrypt->encryptString($stream, $oid);
        $rect = sprintf('%F %F', $width, $height);
        return $out . (' /BBox [0 0 ' . $rect . ']'
            . ' /Matrix [1 0 0 1 0 0]'
            . ' /Resources 2 0 R'
            . ' /Length ' . strlen($stream)
            . ' >>'
            . ' stream' . "\n"
            . $stream . "\n"
            . 'endstream' . "\n"
            . 'endobj' . "\n");
    }

    /**
     * Returns the PDF XObjects entry.
     */
    protected function getOutXObjects(): string
    {
        $out = '';
        foreach ($this->xobject as $data) {
            if (empty($data['outdata'])) {
                continue;
            }

            $out .= ' ' . $data['n'] . ' 0 R' . "\n"
                . '<<'
                . ' /Type /XObject'
                . ' /Subtype /Form'
                . ' /FormType 1';
            $stream = trim($data['outdata']);
            if ($this->compress) {
                $stream = gzcompress($stream);
                if ($stream === false) {
                    throw new PdfException('Unable to compress stream');
                }
                $out .= ' /Filter /FlateDecode';
            }

            $out .= sprintf(
                ' /BBox [%F %F %F %F]',
                $this->toPoints($data['x']),
                $this->toPoints(-$data['y']),
                $this->toPoints(($data['w'] + $data['x'])),
                $this->toPoints(($data['h'] - $data['y']))
            );
            $out .= ' /Matrix [1 0 0 1 0 0] /Resources << /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]';
            if (! empty($data['fonts'])) {
                $fonts = $data['fonts']->getFonts();
                $out = ' /Font <<';
                foreach ($fonts as $font) {
                    $out .= ' /F' . $font['i'] . ' ' . $font['n'] . ' 0 R';
                }

                $out .= ' >>';
            }

            if (! empty($data['extgstates'])) {
                $out .= $data['extgstates']->getOutExtGStateResources();
            }

            if (! empty($data['gradients'])) {
                $out .= $data['gradients']->getOutGradientResources();
            }

            if (! empty($data['spot_colors'])) {
                $out .= $data['spot_colors']->getPdfSpotResources();
            }

            // images or nested xobjects
            if (! empty($data['images']) || ! empty($data['xobjects'])) {
                $out .= ' /XObject <<';
                foreach ($data['images'] as $imgid) {
                    $out .= ' /I' . $imgid . ' ' . $this->xobject['I' . $imgid]['n'] . ' 0 R';
                }

                foreach ($data['xobjects'] as $sub_id => $sub_objid) {
                    $out .= ' /' . $sub_id . ' ' . $sub_objid['n'] . ' 0 R';
                }

                $out .= ' >>';
            }

            $out .= ' >>';
            if (! empty($data['group'])) {
                // set transparency group
                $out .= ' /Group << /Type /Group /S /Transparency';
                if (is_array($data['group'])) {
                    if (! empty($data['group']['CS'])) {
                        $out .= ' /CS /' . $data['group']['CS'];
                    }

                    if (isset($data['group']['I'])) {
                        $out .= ' /I /' . ($data['group']['I'] === true ? 'true' : 'false');
                    }

                    if (isset($data['group']['K'])) {
                        $out .= ' /K /' . ($data['group']['K'] === true ? 'true' : 'false');
                    }
                }

                $out .= ' >>';
            }

            $stream = $this->encrypt->encryptString($stream, $data['n']);
            $out .= ' /Length ' . strlen($stream)
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
        return $this->objid['resdic'] . ' 0 obj' . "\n"
            . '<<'
            . ' /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]'
            . $this->getOutFontDic()
            . $this->getXObjectDic()
            . $this->getLayerDic()
            . $this->graph->getOutExtGStateResources()
            . $this->graph->getOutGradientResources()
            . $this->color->getPdfSpotResources()
            . ' >>' . "\n"
            . 'endobj' . "\n";
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
            $out .= ' /' . $name . ' ' . sprintf('[%u 0 R /XYZ %F %F null]', $poid, $pgx, $pgy);
        }

        return $out . (' >>' . "\n"
            . 'endobj' . "\n");
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
        reset($this->embeddedfiles);
        foreach ($this->embeddedfiles as $name => $data) {
            try {
                $content = $this->file->fileGetContents($data['file']);
            } catch (Exception) {
                continue; // silently skip the file
            }

            $rawsize = strlen($content);
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
                . ' /AFRelationship /Source'
                . ' /EF <</F ' . $data['n'] . ' 0 R>>'
                . ' >>' . "\n"
                . 'endobj' . "\n";
            // embedded file object
            $filter = '';
            if ($this->pdfa == 3) {
                $filter = ' /Subtype /text#2Fxml';
            } elseif ($this->compress) {
                $content = gzcompress($content);
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
                . ' /Length ' . strlen($stream)
                . ' /Params <</Size ' . $rawsize . '>>'
                . ' >>'
                . ' stream' . "\n"
                . $stream . "\n"
                . 'endstream' . "\n"
                . 'endobj' . "\n";
        }

        return $out;
    }

    /**
     * Convert a color array into a string representation for annotations.
     * The number of array elements determines the colour space in which the colour shall be defined:
     *     0 No colour; transparent
     *     1 DeviceGray
     *     3 DeviceRGB
     *     4 DeviceCMYK
     *
     * @param array<int|float> $colors Array of colors.
     */
    protected static function getColorStringFromArray(array $colors): string
    {
        $col = array_values($colors);
        $out = '[';
        match (count($colors)) {
            4 => $out .= sprintf(
                '%F %F %F %F',
                (max(0, min(100, (float) $col[0])) / 100),
                (max(0, min(100, (float) $col[1])) / 100),
                (max(0, min(100, (float) $col[2])) / 100),
                (max(0, min(100, (float) $col[3])) / 100)
            ),
            3 => $out .= sprintf(
                '%F %F %F',
                (max(0, min(255, (float) $col[0])) / 255),
                (max(0, min(255, (float) $col[1])) / 255),
                (max(0, min(255, (float) $col[2])) / 255)
            ),
            1 => $out .= sprintf(
                '%F',
                (max(0, min(255, (float) $col[0])) / 255)
            ),
            default => $out . ']',
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
                $annot = $this->annotation[$oid];
                $annot['opt'] = array_change_key_case($annot['opt'], CASE_LOWER);
                $out .= $this->getAnnotationRadiobuttonGroups($annot);
                $orx = $this->toPoints($annot['x']);
                $ory = $this->toYPoints(($annot['y'] + $annot['h']), $page['pheight']);
                $width = $this->toPoints($annot['w']);
                $height = $this->toPoints($annot['h']);
                $rect = sprintf('%F %F %F %F', $orx, $ory, $orx + $width, $ory + $height);
                $out .= $oid . ' 0 obj' . "\n"
                    . '<<'
                    . ' /Type /Annot'
                    . ' /Subtype /' . $annot['opt']['subtype']
                    . ' /Rect [' . $rect . ']';
                $ft = ['Btn', 'Tx', 'Ch', 'Sig'];
                $formfield = (! empty($annot['opt']['ft']) && in_array($annot['opt']['ft'], $ft));
                if ($formfield) {
                    $out .= ' /FT /' . $annot['opt']['ft'];
                }

                if ($annot['opt']['subtype'] !== 'Link') {
                    $out .= ' /Contents ' . $this->getOutTextString($annot['txt'], $oid, true);
                }

                $out .= ' /P ' . $page['n'] . ' 0 R'
                    . ' /NM ' . $this->encrypt->escapeDataString(sprintf('%04u-%04u', $page['num'], $key), $oid)
                    . ' /M ' . $this->getOutDateTimeString($this->docmodtime, $oid)
                    . $this->getOutAnnotationFlags($annot)
                    . $this->getAnnotationAppearanceStream($annot, (int) $width, (int) $height)
                    . $this->getAnnotationBorder($annot);
                if (! empty($annot['opt']['c']) && is_array($annot['opt']['c'])) {
                    $out .= ' /C ' . static::getColorStringFromArray($annot['opt']['c']);
                }

                //$out .= ' /StructParent ';
                //$out .= ' /OC ';
                $out .= $this->getOutAnnotationMarkups($annot, $oid)
                    . $this->getOutAnnotationOptSubtype($annot, $num, $oid, $key)
                    . ' >>' . "\n"
                    . 'endobj' . "\n";
                if (! $formfield) {
                    continue;
                }

                if (isset($this->radiobuttonGroups[$annot['txt']])) {
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
     * @param array $annot   Array containing page annotations.
     */
    protected function getAnnotationRadiobuttonGroups(array $annot): string
    {
        $out = '';
        if (
            empty($this->radiobuttonGroups[$annot['txt']])
            || ! is_array($this->radiobuttonGroups[$annot['txt']])
        ) {
            return $out;
        }

        $oid = $this->radiobuttonGroups[$annot['txt']]['n'];
        $out = $oid . ' 0 obj' . "\n"
            . '<<'
            . ' /Type /Annot'
            . ' /Subtype /Widget'
            . ' /Rect [0 0 0 0]';
        if ($this->radiobuttonGroups[$annot['txt']]['#readonly#']) {
            // read only
            $out .= ' /F 68 /Ff 49153';
        } else {
            $out .= ' /F 4 /Ff 49152'; // default print for PDF/A
        }

        $out .= ' /T ' . $this->encrypt->escapeDataString($annot['txt'], $oid);
        if (! empty($annot['opt']['tu']) && is_string($annot['opt']['tu'])) {
            $out .= ' /TU ' . $this->encrypt->escapeDataString($annot['opt']['tu'], $oid);
        }

        $out .= ' /FT /Btn /Kids [';
        $defval = '';
        foreach ($this->radiobuttonGroups[$annot['txt']] as $data) {
            if (isset($data['kid'])) {
                $out .= ' ' . $data['kid'] . ' 0 R';
                if ($data['def'] !== 'Off') {
                    $defval = $data['def'];
                }
            }
        }

        $out .= ' ]';
        if (! empty($defval)) {
            $out .= ' /V /' . $defval;
        }

        $out .= ' >>' . "\n"
            . 'endobj' . "\n";
        $this->objid['form'][] = $oid;
        // store object id to be used on Parent entry of Kids
        $this->radiobuttonGroups[$annot['txt']] = $oid;
        return $out;
    }

    /**
     * Returns the Annotation code for Appearance Stream.
     *
     * @param array $annot  Array containing page annotations.
     * @param int $width     Annotation width.
     * @param int $height    Annotation height.
     */
    protected function getAnnotationAppearanceStream(
        array $annot,
        int $width = 0,
        int $height = 0
    ): string {
        $out = '';
        if (! empty($annot['opt']['as']) && is_string($annot['opt']['as'])) {
            $out .= ' /AS /' . $annot['opt']['as'];
        }

        if (empty($annot['opt']['ap'])) {
            return $out;
        }

        $out .= ' /AP <<';
        if (! is_array($annot['opt']['ap'])) {
            $out .= $annot['opt']['ap'];
        } else {
            foreach ($annot['opt']['ap'] as $mode => $def) {
                // $mode can be: n = normal; r = rollover; d = down;
                $out .= ' /' . strtoupper($mode);
                if (is_array($def)) {
                    $out .= ' <<';
                    foreach ($def as $apstate => $stream) {
                        // reference to XObject that define the appearance for this mode-state
                        $apsobjid = $this->getOutAPXObjects($width, $height, $stream);
                        $out .= ' /' . $apstate . ' ' . $apsobjid . ' 0 R';
                    }

                    $out .= ' >>';
                } else {
                    // reference to XObject that define the appearance for this mode
                    $apsobjid = $this->getOutAPXObjects($width, $height, $def);
                    $out .= ' ' . $apsobjid . ' 0 R';
                }
            }
        }

        return $out . ' >>';
    }

    /**
     * Returns the Annotation code for Borders.
     *
     * @param array $annot  Array containing page annotations.
     */
    protected function getAnnotationBorder(array $annot): string
    {
        $out = '';
        if (! empty($annot['opt']['bs']) && (is_array($annot['opt']['bs']))) {
            $out .= ' /BS << /Type /Border';
            if (isset($annot['opt']['bs']['w'])) {
                $out .= ' /W ' . (int) $annot['opt']['bs']['w'];
            }

            $bstyles = ['S', 'D', 'B', 'I', 'U'];
            if (! empty($annot['opt']['bs']['s']) && in_array($annot['opt']['bs']['s'], $bstyles)) {
                $out .= ' /S /' . $annot['opt']['bs']['s'];
            }

            if (isset($annot['opt']['bs']['d']) && (is_array($annot['opt']['bs']['d']))) {
                $out .= ' /D [';
                foreach ($annot['opt']['bs']['d'] as $cord) {
                    $out .= ' ' . (int) $cord;
                }

                $out .= ']';
            }

            $out .= ' >>';
        } else {
            $out .= ' /Border [';
            if (isset($annot['opt']['border']) && (count($annot['opt']['border']) >= 3)) {
                $out .= (int) $annot['opt']['border'][0]
                    . ' ' . (int) $annot['opt']['border'][1]
                    . ' ' . (int) $annot['opt']['border'][2];
                if (isset($annot['opt']['border'][3]) && is_array($annot['opt']['border'][3])) {
                    $out .= ' [';
                    foreach ($annot['opt']['border'][3] as $dash) {
                        $out .= ' ' . (int) $dash;
                    }

                    $out .= ' ]';
                }
            } else {
                $out .= '0 0 0';
            }

            $out .= ']';
        }

        if (isset($annot['opt']['be']) && (is_array($annot['opt']['be']))) {
            $out .= ' /BE <<';
            $bstyles = ['S', 'C'];
            if (! empty($annot['opt']['be']['s']) && in_array($annot['opt']['be']['s'], $bstyles)) {
                $out .= ' /S /' . $annot['opt']['bs']['s'];
            } else {
                $out .= ' /S /S';
            }

            if (
                isset($annot['opt']['be']['i'])
                && ($annot['opt']['be']['i'] >= 0)
                && ($annot['opt']['be']['i'] <= 2)
            ) {
                $out .= ' /I ' . sprintf(' %F', $annot['opt']['be']['i']);
            }

            $out .= '>>';
        }

        return $out;
    }

    /**
     * Returns the Annotation code for Makups.
     *
     * @param array $annot Array containing page annotations.
     * @param int   $oid   Annotation Object ID.
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
        if (empty($annot['opt']['subtype']) || ! in_array(strtolower($annot['opt']['subtype']), $markups)) {
            return $out;
        }

        if (! empty($annot['opt']['t']) && is_string($annot['opt']['t'])) {
            $out .= ' /T ' . $this->getOutTextString($annot['opt']['t'], $oid, true);
        }

        //$out .= ' /Popup ';
        if (isset($annot['opt']['ca'])) {
            $out .= ' /CA ' . sprintf('%F', (float) $annot['opt']['ca']);
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
     * @param array $annot Array containing page annotations.
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
     * @param int|array $flags Annotation flags.
     */
    protected function getAnnotationFlagsCode(int|array $flags): int
    {
        if (! is_array($flags)) {
            return $flags;
        }

        $fval = 0;
        foreach ($flags as $flag) {
            switch (strtolower($flag)) {
                case 'invisible':
                    $fval += 1 << 0;
                    break;
                case 'hidden':
                    $fval += 1 << 1;
                    break;
                case 'print':
                    $fval += 1 << 2;
                    break;
                case 'nozoom':
                    $fval += 1 << 3;
                    break;
                case 'norotate':
                    $fval += 1 << 4;
                    break;
                case 'noview':
                    $fval += 1 << 5;
                    break;
                case 'readonly':
                    $fval += 1 << 6;
                    break;
                case 'locked':
                    $fval += 1 << 7;
                    break;
                case 'togglenoview':
                    $fval += 1 << 8;
                    break;
                case 'lockedcontents':
                    $fval += 1 << 9;
                    break;
                default:
                    break;
            }
        }

        return $fval;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.
     *
     * @param array $annot   Array containing page annotations.
     * @param int   $pagenum Page number.
     * @param int   $oid     Annotation Object ID.
     * @param int   $key     Annotation index in the current page.
     */
    protected function getOutAnnotationOptSubtype(array $annot, int $pagenum, int $oid, int $key): string
    {
        return match (strtolower($annot['opt']['subtype'])) {
            'text' => $this->getOutAnnotationOptSubtypeText($annot),
            'link' => $this->getOutAnnotationOptSubtypeLink($annot, $pagenum, $oid),
            'freetext' => $this->getOutAnnotationOptSubtypeFreetext($annot),
            'line' => $this->getOutAnnotationOptSubtypeLine($annot),
            'square' => $this->getOutAnnotationOptSubtypeSquare($annot),
            'circle' => $this->getOutAnnotationOptSubtypeCircle($annot),
            'polygon' => $this->getOutAnnotationOptSubtypePolygon($annot),
            'polyline' => $this->getOutAnnotationOptSubtypePolyline($annot),
            'highlight' => $this->getOutAnnotationOptSubtypeHighlight($annot),
            'underline' => $this->getOutAnnotationOptSubtypeUnderline($annot),
            'squiggly' => $this->getOutAnnotationOptSubtypeSquiggly($annot),
            'strikeout' => $this->getOutAnnotationOptSubtypeStrikeout($annot),
            'stamp' => $this->getOutAnnotationOptSubtypeStamp($annot),
            'caret' => $this->getOutAnnotationOptSubtypeCaret($annot),
            'ink' => $this->getOutAnnotationOptSubtypeInk($annot),
            'popup' => $this->getOutAnnotationOptSubtypePopup($annot),
            'fileattachment' => $this->getOutAnnotationOptSubtypeFileattachment($annot, $key),
            'sound' => $this->getOutAnnotationOptSubtypeSound($annot),
            'movie' => $this->getOutAnnotationOptSubtypeMovie($annot),
            'widget' => $this->getOutAnnotationOptSubtypeWidget($annot, $oid),
            'screen' => $this->getOutAnnotationOptSubtypeScreen($annot),
            'printermark' => $this->getOutAnnotationOptSubtypePrintermark($annot),
            'trapnet' => $this->getOutAnnotationOptSubtypeTrapnet($annot),
            'watermark' => $this->getOutAnnotationOptSubtypeWatermark($annot),
            '3d' => $this->getOutAnnotationOptSubtype3D($annot),
            default => '',
        };
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.text.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeText(array $annot): string
    {
        $out = '';
        if (isset($annot['opt']['open'])) {
            $out .= ' /Open ' . (strtolower($annot['opt']['open']) == 'true' ? 'true' : 'false');
        }

        $iconsapp = ['Comment', 'Help', 'Insert', 'Key', 'NewParagraph', 'Note', 'Paragraph'];
        if (isset($annot['opt']['name']) && in_array($annot['opt']['name'], $iconsapp)) {
            $out .= ' /Name /' . $annot['opt']['name'];
        } else {
            $out .= ' /Name /Note';
        }

        $hasStateModel = isset($annot['opt']['statemodel']);
        $hasState = isset($annot['opt']['state']);
        $statemodels = ['Marked', 'Review'];
        if (! $hasStateModel && ! $hasState) {
            return $out;
        }

        if ($hasStateModel && in_array($annot['opt']['statemodel'], $statemodels)) {
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

        if ($hasState && in_array($annot['opt']['state'], $states)) {
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
     * @param array $annot   Array containing page annotations.
     * @param int   $pagenum Page number.
     * @param int   $oid     Annotation Object ID.
     */
    protected function getOutAnnotationOptSubtypeLink(
        array $annot,
        int $pagenum,
        int $oid
    ): string {
        $out = '';
        if (! empty($annot['txt']) && is_string($annot['txt'])) {
            switch ($annot['txt'][0]) {
                case '#': // internal destination
                    $out .= ' /A << /S /GoTo /D /' . $this->encrypt->encodeNameObject(substr($annot['txt'], 1)) . '>>';
                    break;
                case '%': // embedded PDF file
                    $filename = basename(substr($annot['txt'], 1));
                    $out .= ' /A << /S /GoToE /D [0 /Fit] /NewWindow true /T << /R /C /P ' . ($pagenum - 1)
                        . ' /A ' . $this->embeddedfiles[$filename]['a']
                        . ' >>'
                        . ' >>';
                    break;
                case '*': // embedded generic file
                    $filename = basename(substr($annot['txt'], 1));
                    $jsa = 'var D=event.target.doc;var MyData=D.dataObjects;for (var i in MyData) if (MyData[i].path=="'
                        . $filename . '")'
                        . ' D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
                    $out .= ' /A << /S /JavaScript /JS '
                        . $this->getOutTextString($jsa, $oid, true) . ' >>';
                    break;
                default:
                    $parsedUrl = parse_url($annot['txt']);
                    if (
                        empty($parsedUrl['scheme'])
                        && (isset($parsedUrl['path']) && $parsedUrl['path'] !== ''
                        && strtolower(substr($parsedUrl['path'], -4)) == '.pdf')
                    ) {
                        // relative link to a PDF file
                        $dest = '[0 /Fit]'; // default page 0
                        if (! empty($parsedUrl['fragment'])) {
                            // check for named destination
                            $tmp = explode('=', $parsedUrl['fragment']);
                            $dest = '(' . ((count($tmp) == 2) ? $tmp[1] : $tmp[0]) . ')';
                        }

                        $out .= ' /A << /S /GoToR /D ' . $dest
                            . ' /F ' . $this->encrypt->escapeDataString($this->unhtmlentities($parsedUrl['path']), $oid)
                            . ' /NewWindow true'
                            . ' >>';
                    } else {
                        // external URI link
                        $out .= ' /A << /S /URI /URI '
                            . $this->encrypt->escapeDataString($this->unhtmlentities($annot['txt']), $oid)
                            . ' >>';
                    }

                    break;
            }
        } elseif (! empty($this->links[$annot['txt']])) {
            // internal link ID
            $l = $this->links[$annot['txt']];
            $page = $this->page->getPage($l['p']);
            $y = $this->toYPoints($l['y'], $page['height']);
            $out .= sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $page['n'], $y);
        }

        $hmodes = ['N', 'I', 'O', 'P'];
        if (! empty($annot['opt']['h']) && in_array($annot['opt']['h'], $hmodes)) {
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
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeFreetext(array $annot): string
    {
        $out = '';
        if (! empty($annot['opt']['da'])) {
            $out .= ' /DA (' . $annot['opt']['da'] . ')';
        }

        if (isset($annot['opt']['q']) && ($annot['opt']['q'] >= 0) && ($annot['opt']['q'] <= 2)) {
            $out .= ' /Q ' . (int) $annot['opt']['q'];
        }

        if (isset($annot['opt']['rc'])) {
            $out .= ' /RC ' . $this->getOutTextString($annot['opt']['rc'], $annot['n'], true);
        }

        if (isset($annot['opt']['ds'])) {
            $out .= ' /DS ' . $this->getOutTextString($annot['opt']['ds'], $annot['n'], true);
        }

        if (isset($annot['opt']['cl']) && is_array($annot['opt']['cl'])) {
            $out .= ' /CL [';
            foreach ($annot['opt']['cl'] as $cl) {
                $out .= sprintf('%F ', $this->toPoints($cl));
            }

            $out .= ']';
        }

        $tfit = ['FreeText', 'FreeTextCallout', 'FreeTextTypeWriter'];
        if (isset($annot['opt']['it']) && in_array($annot['opt']['it'], $tfit)) {
            $out .= ' /IT /' . $annot['opt']['it'];
        }

        if (isset($annot['opt']['rd']) && is_array($annot['opt']['rd'])) {
            $l = $this->toPoints($annot['opt']['rd'][0]);
            $r = $this->toPoints($annot['opt']['rd'][1]);
            $t = $this->toPoints($annot['opt']['rd'][2]);
            $b = $this->toPoints($annot['opt']['rd'][3]);
            $out .= ' /RD [' . sprintf('%F %F %F %F', $l, $r, $t, $b) . ']';
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
        if (isset($annot['opt']['le']) && in_array($annot['opt']['le'], $lineendings)) {
            $out .= ' /LE /' . $annot['opt']['le'];
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.line.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeLine(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.square.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeSquare(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.circle.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeCircle(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.polygon.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypePolygon(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.polyline.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypePolyline(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.highlight.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeHighlight(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeUnderline(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.squiggly.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeSquiggly(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.strikeout.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeStrikeout(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.stamp.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeStamp(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.caret.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeCaret(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.ink.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeInk(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.popup.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypePopup(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.fileattachment.
     *
     * @param array $annot Array containing page annotations.
     * @param int   $key   Annotation index in the current page.
     */
    protected function getOutAnnotationOptSubtypeFileattachment(
        array $annot,
        int $key
    ): string {
        if (($this->pdfa == 1) || ($this->pdfa == 2) || ! isset($annot['opt']['fs'])) {
            // embedded files are not allowed in PDF/A mode version 1 and 2
            return '';
        }

        $filename = basename($annot['opt']['fs']);
        if (! isset($this->embeddedfiles[$filename]['f'])) {
            return '';
        }

        $out = ' /FS ' . $this->embeddedfiles[$filename]['f'] . ' 0 R';
        $iconsapp = ['Graph', 'Paperclip', 'PushPin', 'Tag'];
        if (isset($annot['opt']['name']) && in_array($annot['opt']['name'], $iconsapp)) {
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
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeSound(array $annot): string
    {
        $out = '';
        if (empty($annot['opt']['fs'])) {
            return '';
        }

        $filename = basename($annot['opt']['fs']);
        if (! isset($this->embeddedfiles[$filename]['f'])) {
            return '';
        }

        // ... TO BE COMPLETED ...
        // /R /C /B /E /CO /CP
        $out = ' /Sound ' . $this->embeddedfiles[$filename]['f'] . ' 0 R';
        $iconsapp = ['Speaker', 'Mic'];
        if (isset($annot['opt']['name']) && in_array($annot['opt']['name'], $iconsapp)) {
            $out .= ' /Name /' . $annot['opt']['name'];
        } else {
            $out .= ' /Name /Speaker';
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.movie.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeMovie(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.widget.
     *
     * @param array $annot Array containing page annotations.
     * @param int   $oid   Annotation Object ID.
     */
    protected function getOutAnnotationOptSubtypeWidget(
        array $annot,
        int $oid
    ): string {
        $out = '';
        $hmode = ['N', 'I', 'O', 'P', 'T'];
        if (! empty($annot['opt']['h']) && in_array($annot['opt']['h'], $hmode)) {
            $out .= ' /H /' . $annot['opt']['h'];
        }

        if (! empty($annot['opt']['mk']) && is_array($annot['opt']['mk'])) {
            $out .= ' /MK <<';
            if (isset($annot['opt']['mk']['r'])) {
                $out .= ' /R ' . $annot['opt']['mk']['r'];
            }

            if (isset($annot['opt']['mk']['bc']) && (is_array($annot['opt']['mk']['bc']))) {
                $out .= ' /BC ' . static::getColorStringFromArray($annot['opt']['mk']['bc']);
            }

            if (isset($annot['opt']['mk']['bg']) && (is_array($annot['opt']['mk']['bg']))) {
                $out .= ' /BG ' . static::getColorStringFromArray($annot['opt']['mk']['bg']);
            }

            if (isset($annot['opt']['mk']['ca'])) {
                $out .= ' /CA ' . $annot['opt']['mk']['ca'];
            }

            if (isset($annot['opt']['mk']['rc'])) {
                $out .= ' /RC ' . $annot['opt']['mk']['rc'];
            }

            if (isset($annot['opt']['mk']['ac'])) {
                $out .= ' /AC ' . $annot['opt']['mk']['ac'];
            }

            if (isset($annot['opt']['mk']['i'])) {
                $info = $this->image->getImageDataByKey($this->image->getKey($annot['opt']['mk']['i']));
                if (! empty($info['obj'])) {
                    $out .= ' /I ' . $info['obj'] . ' 0 R';
                }
            }

            if (isset($annot['opt']['mk']['ri'])) {
                $info = $this->image->getImageDataByKey($this->image->getKey($annot['opt']['mk']['ri']));
                if (! empty($info['obj'])) {
                    $out .= ' /RI ' . $info['obj'] . ' 0 R';
                }
            }

            if (isset($annot['opt']['mk']['ix'])) {
                $info = $this->image->getImageDataByKey($this->image->getKey($annot['opt']['mk']['ix']));
                if (! empty($info['obj'])) {
                    $out .= ' /IX ' . $info['obj'] . ' 0 R';
                }
            }

            if (! empty($annot['opt']['mk']['if']) && is_array($annot['opt']['mk']['if'])) {
                $out .= ' /IF <<';
                $if_sw = ['A', 'B', 'S', 'N'];
                if (isset($annot['opt']['mk']['if']['sw']) && in_array($annot['opt']['mk']['if']['sw'], $if_sw)) {
                    $out .= ' /SW /' . $annot['opt']['mk']['if']['sw'];
                }

                $if_s = ['A', 'P'];
                if (isset($annot['opt']['mk']['if']['s']) && in_array($annot['opt']['mk']['if']['s'], $if_s)) {
                    $out .= ' /S /' . $annot['opt']['mk']['if']['s'];
                }

                if (
                    isset($annot['opt']['mk']['if']['a'])
                    && (is_array($annot['opt']['mk']['if']['a']))
                    && (isset($annot['opt']['mk']['if']['a']) && $annot['opt']['mk']['if']['a'] !== [])
                ) {
                    $out .= sprintf(
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
                && ($annot['opt']['mk']['tp'] >= 0)
                && ($annot['opt']['mk']['tp'] <= 6)
            ) {
                $out .= ' /TP ' . (int) $annot['opt']['mk']['tp'];
            }

            $out .= '>>';
        }

        // --- Entries for field dictionaries ---
        if (isset($this->radiobuttonGroups[$annot['txt']])) {
            $out .= ' /Parent ' . $this->radiobuttonGroups[$annot['txt']] . ' 0 R';
        }

        if (isset($annot['opt']['t']) && is_string($annot['opt']['t'])) {
            $out .= ' /T ' . $this->encrypt->escapeDataString($annot['opt']['t'], $oid);
        }

        if (isset($annot['opt']['tu']) && is_string($annot['opt']['tu'])) {
            $out .= ' /TU ' . $this->encrypt->escapeDataString($annot['opt']['tu'], $oid);
        }

        if (isset($annot['opt']['tm']) && is_string($annot['opt']['tm'])) {
            $out .= ' /TM ' . $this->encrypt->escapeDataString($annot['opt']['tm'], $oid);
        }

        if (isset($annot['opt']['ff'])) {
            if (is_array($annot['opt']['ff'])) {
                // array of bit settings
                $flag = 0;
                foreach ($annot['opt']['ff'] as $val) {
                    $flag += 1 << ($val - 1);
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
            if (is_array($annot['opt']['v'])) {
                foreach ($annot['opt']['v'] as $optval) {
                    if (is_float($optval)) {
                        $optval = sprintf('%F', $optval);
                    }

                    $out .= ' ' . $optval;
                }
            } else {
                $out .= ' ' . $this->getOutTextString($annot['opt']['v'], $oid, true);
            }
        }

        if (isset($annot['opt']['dv'])) {
            $out .= ' /DV';
            if (is_array($annot['opt']['dv'])) {
                foreach ($annot['opt']['dv'] as $optval) {
                    if (is_float($optval)) {
                        $optval = sprintf('%F', $optval);
                    }

                    $out .= ' ' . $optval;
                }
            } else {
                $out .= ' ' . $this->getOutTextString($annot['opt']['dv'], $oid, true);
            }
        }

        if (isset($annot['opt']['rv'])) {
            $out .= ' /RV';
            if (is_array($annot['opt']['rv'])) {
                foreach ($annot['opt']['rv'] as $optval) {
                    if (is_float($optval)) {
                        $optval = sprintf('%F', $optval);
                    }

                    $out .= ' ' . $optval;
                }
            } else {
                $out .= ' ' . $this->getOutTextString($annot['opt']['rv'], $oid, true);
            }
        }

        if (! empty($annot['opt']['a'])) {
            $out .= ' /A << ' . $annot['opt']['a'] . ' >>';
        }

        if (! empty($annot['opt']['aa'])) {
            $out .= ' /AA << ' . $annot['opt']['aa'] . ' >>';
        }

        if (! empty($annot['opt']['da'])) {
            $out .= ' /DA (' . $annot['opt']['da'] . ')';
        }

        if (isset($annot['opt']['q']) && ($annot['opt']['q'] >= 0) && ($annot['opt']['q'] <= 2)) {
            $out .= ' /Q ' . (int) $annot['opt']['q'];
        }

        if (! empty($annot['opt']['opt']) && is_array($annot['opt']['opt'])) {
            $out .= ' /Opt [';
            foreach ($annot['opt']['opt'] as $copt) {
                if (is_array($copt)) {
                    $out .= ' [' . $this->getOutTextString($copt[0], $oid, true)
                        . ' ' . $this->getOutTextString($copt[1], $oid, true) . ']';
                } else {
                    $out .= ' ' . $this->getOutTextString($copt, $oid, true);
                }
            }

            $out .= ']';
        }

        if (isset($annot['opt']['ti'])) {
            $out .= ' /TI ' . (int) $annot['opt']['ti'];
        }

        if (! empty($annot['opt']['i']) && is_array($annot['opt']['i'])) {
            $out .= ' /I [';
            foreach ($annot['opt']['i'] as $copt) {
                $out .= (int) $copt . ' ';
            }

            $out .= ']';
        }

        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.screen.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeScreen(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.printermark.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypePrintermark(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.trapnet.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeTrapnet(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.watermark.
     *
     * @param array $annot Array containing page annotations.
     */
    protected function getOutAnnotationOptSubtypeWatermark(array $annot): string
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.3d.
     *
     * @param array $annot Array containing page annotations.
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
        if (($this->pdfa > 0) || (empty($this->javascript) && empty($this->jsobjects))) {
            return '';
        }

        if (strpos($this->javascript, 'this.addField') > 0) {
            if (! $this->userrights['enabled']) {
                // $this->setUserRights();
            }

            // The following two lines are used to avoid form fields duplication after saving.
            // The addField method only works when releasing user rights (UR3).
            $pattern = "ftcpdfdocsaved=this.addField('%s','%s',%d,[%F,%F,%F,%F]);";
            $jsa = sprintf($pattern, 'tcpdfdocsaved', 'text', 0, 0, 1, 0, 1);
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
            if ($val['onload']) {
                // additional Javascript object
                $oid = ++$this->pon;
                $out .= $oid . ' 0 obj' . "\n"
                . '<< '
                . '/S /JavaScript /JS '
                . $this->getOutTextString($val['js'], $oid, true)
                . ' >>' . "\n"
                . 'endobj' . "\n";
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
        array_multisort($outline_p, SORT_NUMERIC, SORT_ASC, $outline_k, SORT_NUMERIC, SORT_ASC, $this->outlines);
    }

    /**
     * Process the bookmarks to get the previous and next one.
     *
     * @return int first bookmark object ID
     */
    protected function processPrevNextBookmarks(): int
    {
        $numbookmarks = count($this->outlines);
        $this->sortBookmarks();
        $lru = [];
        $level = 0;
        foreach ($this->outlines as $i => $o) {
            if ($o['l'] > 0) {
                $parent = $lru[($o['l'] - 1)];
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

            if (($o['l'] <= $level) && ($i > 0)) {
                // set prev and next pointers
                $prev = $lru[$o['l']];
                $this->outlines[$prev]['next'] = $i;
                $this->outlines[$i]['prev'] = $prev;
            }

            $lru[$o['l']] = $i;
            $level = $o['l'];
        }

        return $lru[0];
    }

    /**
     * Reverse function for htmlentities.
     *
     * @param string $text_to_convert Text to convert.
     *
     * @return string converted text string
     */
    protected function unhtmlentities(string $text_to_convert): string
    {
        return html_entity_decode($text_to_convert, ENT_QUOTES, $this->encoding);
    }

    /**
     * Returns the PDF Bookmarks entry.
     */
    protected function getOutBookmarks(): string
    {
        if ($this->outlines === []) {
            return '';
        }

        $numbookmarks = is_countable($this->outlines) ? count($this->outlines) : 0;
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
            $title = preg_replace($search, $replace, $outline['t']);
            if ($title === null) {
                $title = '';
            }
            $title = strip_tags($title);

            $title = preg_replace("/^\s+|\s+$/u", '', $title);
            if ($title === null) {
                $title = '';
            }

            $oid = ++$this->pon;
            $out .= $oid . ' 0 obj' . "\n"
                . '<<'
                . ' /Title ' . $this->getOutTextString($title, $oid, true)
                . ' /Parent ' . ($first_oid + $outline['parent']) . ' 0 R';
            if (isset($outline['prev'])) {
                $out .= ' /Prev ' . ($first_oid + $outline['prev']) . ' 0 R';
            }

            if (isset($outline['next'])) {
                $out .= ' /Next ' . ($first_oid + $outline['next']) . ' 0 R';
            }

            if (isset($outline['first'])) {
                $out .= ' /First ' . ($first_oid + $outline['first']) . ' 0 R';
            }

            if (isset($outline['last'])) {
                $out .= ' /Last ' . ($first_oid + $outline['last']) . ' 0 R';
            }

            if (! empty($outline['u'])) {
                // link
                if (is_string($outline['u'])) {
                    switch ($outline['u'][0]) {
                        case '#':
                            // internal destination
                            $out .= ' /Dest /' . $this->encrypt->encodeNameObject(substr($outline['u'], 1));
                            break;
                        case '%':
                            // embedded PDF file
                            $filename = basename(substr($outline['u'], 1));
                            $out .= ' /A << /S /GoToE /D [0 /Fit] /NewWindow true /T << /R /C /P '
                                . ($outline['p'] - 1)
                                . ' /A ' . $this->embeddedfiles[$filename]['a'] . ' >>'
                                . ' >>';
                            break;
                        case '*':
                            // embedded generic file
                            $filename = basename(substr($outline['u'], 1));
                            $jsa = 'var D=event.target.doc;var MyData=D.dataObjects;'
                            . 'for (var i in MyData) if (MyData[i].path=="'
                            . $filename . '")'
                            . ' D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
                            $out .= ' /A <</S /JavaScript /JS '
                            . $this->getOutTextString($jsa, $oid, true) . '>>';
                            break;
                        default:
                            // external URI link
                            $out .= ' /A << /S /URI /URI '
                                . $this->encrypt->escapeDataString($this->unhtmlentities($outline['u']), $oid)
                                . ' >>';
                            break;
                    }
                } elseif (isset($this->links[$outline['u']])) {
                    // internal link ID
                    $l = $this->links[$outline['u']];
                    $page = $this->page->getPage($l['p']);
                    $y = ($page['height'] - ($l['y'] * $this->kunit));
                    $out .= sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $page['n'], $y);
                }
            } else {
                // link to a page
                $page = $this->page->getPage($outline['p']);
                $x = ($outline['x'] * $this->kunit);
                $y = ($page['height'] - ($outline['y'] * $this->kunit));
                $out .= ' ' . sprintf('/Dest [%u 0 R /XYZ %F %F null]', $page['n'], $x, $y);
            }

            // set font style
            $style = 0;
            if (! empty($outline['s'])) {
                if (str_contains($outline['s'], 'B')) {
                    $style |= 2; // bold
                }

                if (str_contains($outline['s'], 'I')) {
                    $style |= 1; // oblique
                }
            }

            $out .= sprintf(' /F %d', $style);
            // set bookmark color
            if (! empty($outline['c']) && is_array($outline['c'])) {
                $out .= ' /C [' . static::getColorStringFromArray($outline['c']) . ']';
            } else {
                $out .= ' /C [0.0 0.0 0.0]'; // black
            }

            $out .= ' /Count 0 >>' . "\n"
                . 'endobj';
        }

        //Outline root
        $this->outlinerootoid = ++$this->pon;
        return $out . ($this->outlinerootoid . ' 0 obj' . "\n"
            . '<<'
            . ' /Type /Outlines'
            . ' /First ' . $first_oid . ' 0 R'
            . ' /Last ' . ($first_oid + $root_oid) . ' 0 R'
            . ' >>' . "\n"
            . 'endobj');
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
            $signame = $esa['name'] . sprintf(' [%03d]', ($key + 1));
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
                . "\n" . 'endobj';
        }

        return $out;
    }

    /**
     * Sign the document.
     *
     * @param string $pdfdoc string containing the PDF document
     */
    protected function signDocument(string $pdfdoc): string
    {
        $out = '';
        if (! $this->sign) {
            return $out;
        }

        // remove last newline
        $pdfdoc = substr($pdfdoc, 0, -1);
        // remove filler space
        $byterange_strlen = strlen($this::BYTERANGE);
        // define the ByteRange
        $byte_range = [];
        $byte_range[0] = 0;
        $byte_range[1] = strpos($pdfdoc, $this::BYTERANGE) + $byterange_strlen + 10;
        $byte_range[2] = $byte_range[1] + $this::SIGMAXLEN + 2;
        $byte_range[3] = strlen($pdfdoc) - $byte_range[2];
        $pdfdoc = substr($pdfdoc, 0, $byte_range[1]) . substr($pdfdoc, $byte_range[2]);
        // replace the ByteRange
        $byterange = sprintf('/ByteRange[0 %u %u %u]', $byte_range[1], $byte_range[2], $byte_range[3]);
        $byterange .= str_repeat(' ', ($byterange_strlen - strlen($byterange)));
        $pdfdoc = str_replace($this::BYTERANGE, $byterange, $pdfdoc);
        // write the document to a temporary folder
        $tempdoc = $this->cache->getNewFileName('doc', $this->fileid);
        if ($tempdoc === false) {
            throw new PdfException('Unable to create temporary document file for signature');
        }

        $f = $this->file->fopenLocal($tempdoc, 'wb');
        $pdfdoc_length = strlen($pdfdoc);
        fwrite($f, $pdfdoc, $pdfdoc_length);
        fclose($f);
        // get digital signature via openssl library
        $tempsign = $this->cache->getNewFileName('sig', $this->fileid);
        if ($tempsign === false) {
            throw new PdfException('Unable to create temporary signature file');
        }

        if (empty($this->signature['extracerts'])) {
            openssl_pkcs7_sign(
                $tempdoc,
                $tempsign,
                $this->signature['signcert'],
                [$this->signature['privkey'], $this->signature['password']],
                [],
                PKCS7_BINARY | PKCS7_DETACHED
            );
        } else {
            openssl_pkcs7_sign(
                $tempdoc,
                $tempsign,
                $this->signature['signcert'],
                [$this->signature['privkey'], $this->signature['password']],
                [],
                PKCS7_BINARY | PKCS7_DETACHED,
                $this->signature['extracerts']
            );
        }

        // read signature
        $signature = $this->file->getFileData($tempsign);
        if ($signature === false) {
            throw new PdfException('Unable to read signature file');
        }
        // extract signature
        $signature = substr($signature, $pdfdoc_length);
        $signature = substr($signature, (strpos($signature, "%%EOF\n\n------") + 13));

        $tmparr = explode("\n\n", $signature);
        $signature = $tmparr[1];
        // decode signature
        $signature = base64_decode(trim($signature));
        if ($signature === false) {
            throw new PdfException('Unable to decode signature');
        }
        // add TSA timestamp to signature
        $signature = $this->applySignatureTimestamp($signature);
        // convert signature to hex
        $signature = unpack('H*', $signature);
        if ($signature === false) {
            throw new PdfException('Unable to unpack signature');
        }
        $signature = current($signature);
        $signature = str_pad($signature, $this::SIGMAXLEN, '0');
        // Add signature to the document
        $out = substr($pdfdoc, 0, $byte_range[1]) . '<' . $signature . '>' . substr($pdfdoc, $byte_range[1]);
        return $out;
    }

    /**
     * -- NOT YET IMPLEMENTED --
     * Add TSA timestamp to the signature.
     *
     * @param string $signature Digital signature as binary string
     */
    protected function applySignatureTimestamp(string $signature): string
    {
        return $signature;
    }

    /**
     * Returns the PDF signarure entry.
     */
    protected function getOutSignature(): string
    {
        if ((! $this->sign) || empty($this->signature['cert_type'])) {
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
            . 'endobj';
        $out .= $oid . ' 0 obj' . "\n";
        $out .= '<< /Type /Sig /Filter /Adobe.PPKLite /SubFilter /adbe.pkcs7.detached '
            . $this::BYTERANGE
            . ' /Contents<' . str_repeat('0', $this::SIGMAXLEN) . '>';
        if (empty($this->signature['approval']) || ($this->signature['approval'] != 'A')) {
            $out .= ' /Reference [ << /Type /SigRef';
            if ($this->signature['cert_type'] > 0) {
                $out .= $this->getOutSignatureDocMDP();
            } else {
                $out .= $this->getOutSignatureUserRights();
            }

            // optional digest data (values must be calculated and replaced later)
            //$out .= ' /Data ********** 0 R'
            //    .' /DigestMethod/MD5'
            //    .' /DigestLocation[********** 34]'
            //    .' /DigestValue<********************************>';
            $out .= ' >> ]'; // end of reference
        }

        $out .= $this->getOutSignatureInfo($oid);
        return $out . (' /M '
            . $this->getOutDateTimeString($this->docmodtime, $oid)
            . ' >>' . "\n"
            . 'endobj');
    }

    /**
     * Returns the PDF signarure entry.
     */
    protected function getOutSignatureDocMDP(): string
    {
        return ' /TransformMethod /DocMDP /TransformParams << /Type /TransformParams /P '
            . $this->signature['cert_type']
            . ' /V /1.2 >>';
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
     * Get the PDF output string for Font resources dictionary.
     */
    protected function getOutFontDic(): string
    {
        $fonts = $this->font->getFonts();
        if ($fonts === []) {
            return '';
        }

        $out = ' /Font <<';
        foreach ($fonts as $font) {
            $out .= ' /F' . $font['i'] . ' ' . $font['n'] . ' 0 R';
        }

        return $out . ' >>';
    }

    /**
     * Get the PDF output string for XObject resources dictionary.
     */
    protected function getXObjectDic(): string
    {
        $out = ' /XObject <<';
        foreach ($this->xobject as $id => $oid) {
            $out .= ' /' . $id . ' ' . $oid['n'] . ' 0 R';
        }

        $out .= $this->image->getXobjectDict();
        return $out . ' >>';
    }

    /**
     * Get the PDF output string for Layer resources dictionary.
     */
    protected function getLayerDic(): string
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
            echo $rawpdf;
            return;
        }

        if (headers_sent()) {
            throw new PdfException(
                'The PDF file cannot be sent because some data has already been output to the browser.'
            );
        }

        header('Content-Type: application/pdf');
        header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        header('Pragma: public');
        header('Expires: Sat, 01 Jan 2000 01:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: inline; filename="' . $this->encpdffilename . '"; filename*=UTF-8\'\''
        . $this->encpdffilename);
        if (empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            // the content length may vary if the server is using compression
            header('Content-Length: ' . strlen($rawpdf));
        }

        echo $rawpdf;
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
        if (ob_get_contents()) {
            throw new PdfException(
                'The PDF file cannot be sent, some data has already been output to the browser.'
            );
        }

        if (headers_sent()) {
            throw new PdfException(
                'The PDF file cannot be sent because some data has already been output to the browser.'
            );
        }

        header('Content-Description: File Transfer');
        header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        header('Pragma: public');
        header('Expires: Sat, 01 Jan 2000 01:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        // force download dialog
        header('Content-Type: application/pdf');
        if (! str_contains(PHP_SAPI, 'cgi')) {
            header('Content-Type: application/force-download', false);
            header('Content-Type: application/octet-stream', false);
            header('Content-Type: application/download', false);
        }

        // use the Content-Disposition header to supply a recommended filename
        header('Content-Disposition: attachment; filename="' . $this->encpdffilename . '";'
        . " filename*=UTF-8''" . $this->encpdffilename);
        header('Content-Transfer-Encoding: binary');
        if (empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            // the content length may vary if the server is using compression
            header('Content-Length: ' . strlen($rawpdf));
        }

        echo $rawpdf;
    }

    /**
     * Save the PDF document to a local file.
     *
     * @param string $path    Path to the output file.
     * @param string $rawpdf  Raw PDF data string from getOutPDFString().
     */
    public function savePDF(
        string $path = '',
        string $rawpdf = ''
    ): void {
        $filepath = implode('/', [realpath($path), $this->pdffilename]);
        $fhd = $this->file->fopenLocal($filepath, 'wb');
        if (! $fhd) {
            throw new PdfException('Unable to create output file: ' . $filepath);
        }

        fwrite($fhd, $rawpdf, strlen($rawpdf));
        fclose($fhd);
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
        return 'Content-Type: application/pdf;
 name="' . $this->encpdffilename . '"' . "\r\n"
        . 'Content-Transfer-Encoding: base64' . "\r\n"
        . 'Content-Disposition: attachment;' . "\r\n"
        . ' filename="' . $this->encpdffilename . '"' . "\r\n\r\n"
        . chunk_split(base64_encode($rawpdf), 76, "\r\n");
    }
}
