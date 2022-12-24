<?php
/**
 * Output.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2022 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf;

use \Com\Tecnick\Pdf\Font\Output as OutFont;

/**
 * Com\Tecnick\Pdf\Output
 *
 * Output PDF data
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2022 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * @SuppressWarnings(PHPMD)
 */
abstract class Output
{
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
     * Returns the RAW PDF string.
     *
     * @return string
     */
    public function getOutPDFString()
    {
        $out = $this->getOutPDFHeader()
            .$this->getOutPDFBody();
        $startxref = strlen($out);
        $offset = $this->getPDFObjectOffsets($out);
        $out .= $this->getOutPDFXref($offset)
            .$this->getOutPDFTrailer()
            .'startxref'."\n"
            .$startxref."\n"
            .'%%EOF'."\n";
        $out .= $this->signDocument($out);
        return $out;
    }

    /**
     * Returns the PDF document header.
     *
     * @return string
     */
    protected function getOutPDFHeader()
    {
        return '%PDF-'.$this->pdfver."\n"
            ."%\xE2\xE3\xCF\xD3\n";
    }

    /**
     * Returns the raw PDF Body section.
     *
     * @return string
     */
    protected function getOutPDFBody()
    {
        $out = $this->page->getPdfPages($this->pon);
        $this->objid['pages'] = $this->page->getRootObjID();
        $out .= $this->graph->getOutExtGState($this->pon);
        $this->pon = $this->graph->getObjectNumber();
        $out .= $this->getOutOCG();
        $outfont = new OutFont(
            $this->font->getFonts(),
            $this->pon,
            $this->encrypt
        );
        $out .= $outfont->getFontsBlock();
        $this->pon = $outfont->getObjectNumber();
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
        $out .= $this->getOutCatalog();
        return $out;
    }

    /**
     * Returns the ordered offset array for each object.
     *
     * @param string $data Raw PDF data
     *
     * @return array
     */
    protected function getPDFObjectOffsets($data)
    {
        preg_match_all('/(([0-9]+)[\s][0-9]+[\s]obj[\n])/i', $data, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
        $offset = array();
        foreach ($matches as $item) {
            $offset[($item[2][0])] = $item[2][1];
        }
        ksort($offset);
        return $offset;
    }

    /**
     * Returns the PDF XREF section.
     *
     * @param array $offset Ordered offset array for each PDF object
     *
     * @return string
     */
    protected function getOutPDFXref($offset)
    {
        $out = 'xref'."\n"
            .'0 '.($this->pon + 1)."\n"
            .'0000000000 65535 f '."\n";
        $freegen = ($this->pon + 2);
        end($offset);
        $lastobj = key($offset);
        for ($idx = 1; $idx <= $lastobj; ++$idx) {
            if (isset($offset[$idx])) {
                $out .= sprintf('%010d 00000 n '."\n", $offset[$idx]);
            } else {
                $out .= sprintf('0000000000 %05d f '."\n", $freegen);
                ++$freegen;
            }
        }
        return $out;
    }

    /**
     * Returns the PDF Trailer section.
     *
     * @param array $offset Ordered offset array for each PDF object
     *
     * @return string
     */
    protected function getOutPDFTrailer()
    {
        $out = 'trailer'."\n"
            .'<<'
            .' /Size '.($this->pon + 1)
            .' /Root '.$this->objid['catalog'].' 0 R'
            .' /Info '.$this->objid['info'].' 0 R';
        $enc = $this->encrypt->getEncryptionData();
        if (!empty($enc['objid'])) {
            $out .= ' /Encrypt '.$enc['objid'].' 0 R';
        }
        $out .= ' /ID [ <'.$this->fileid.'> <'.$this->fileid.'> ]'
            .' >>'."\n";
        return $out;
    }

    /**
     * Returns the PDF object to include a standard sRGB_IEC61966-2.1 blackscaled ICC colour profile.
     *
     * @return string
     */
    protected function getOutICC()
    {
        if (!$this->pdfa && !$this->sRGB) {
            return '';
        }
        
        $oid = ++$this->pon;
        $this->objid['srgbicc'] = $oid;
        $out = $oid.' 0 obj'."\n";
        $icc = file_get_contents(dirname(__FILE__).'/include/sRGB.icc.z');
        $icc = $this->encrypt->encryptString($icc, $oid);
        $out .= '<<'
            .' /N 3'
            .' /Filter /FlateDecode'
            .' /Length '.strlen($icc)
            .' >>'
            .' stream'."\n"
            .$icc."\n"
            .'endstream'."\n"
            .'endobj'."\n";
        return $out;
    }

    /**
     * Get OutputIntents for sRGB IEC61966-2.1 if required.
     *
     * @return string
     */
    protected function getOutputIntentsSrgb()
    {
        if (empty($this->objid['srgbicc'])) {
            return '';
        }
        $oid = $this->objid['catalog'];
        $out = ' /OutputIntents [<<'
            .' /Type /OutputIntent'
            .' /S /GTS_PDFA1'
            .' /OutputCondition '.$this->getOutTextString('sRGB IEC61966-2.1', $oid, true)
            .' /OutputConditionIdentifier '.$this->getOutTextString('sRGB IEC61966-2.1', $oid, true)
            .' /RegistryName '.$this->getOutTextString('http://www.color.org', $oid, true)
            .' /Info '.$this->getOutTextString('sRGB IEC61966-2.1', $oid, true)
            .' /DestOutputProfile '.$this->objid['srgbicc'].' 0 R'
            .' >>]';
        return $out;
    }

    /**
     * Get OutputIntents for PDF-X if required.
     *
     * @return string
     */
    protected function getOutputIntentsPdfX()
    {
        $oid = $this->objid['catalog'];
        $out = ' /OutputIntents [<<'
            .' /Type /OutputIntent'
            .' /S /GTS_PDFX'
            .' /OutputConditionIdentifier '.$this->getOutTextString('OFCOM_PO_P1_F60_95', $oid, true)
            .' /RegistryName '.$this->getOutTextString('http://www.color.org', $oid, true)
            .' /Info '.$this->getOutTextString('OFCOM_PO_P1_F60_95', $oid, true)
            .' >>]';
        return $out;
    }

    /**
     * Set OutputIntents.
     *
     * @return string
     */
    protected function getOutputIntents()
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
     *
     * @return string
     */
    protected function getPDFLayers()
    {
        if (empty($this->pdflayer) || empty($this->objid['catalog'])) {
            return '';
        }
        $oid = $this->objid['catalog'];
        $lyrobjs = '';
        $lyrobjs_off = '';
        $lyrobjs_lock = '';
        foreach ($this->pdflayer as $layer) {
            $layer_obj_ref = ' '.$layer['objid'].' 0 R';
            $lyrobjs .= $layer_obj_ref;
            if ($layer['view'] === false) {
                $lyrobjs_off .= $layer_obj_ref;
            }
            if ($layer['lock']) {
                $lyrobjs_lock .= $layer_obj_ref;
            }
        }
        $out = ' /OCProperties << /OCGs ['.$lyrobjs.' ]'
            .' /D <<'
            .' /Name '.$this->getOutTextString('Layers', $oid, true)
            .' /Creator '.$this->getOutTextString($this->creator, $oid, true)
            .' /BaseState /ON'
            .' /OFF ['.$lyrobjs_off.']'
            .' /Locked ['.$lyrobjs_lock.']'
            .' /Intent /View'
            .' /AS ['
            .' << /Event /Print /OCGs ['.$lyrobjs.'] /Category [/Print] >>'
            .' << /Event /View /OCGs ['.$lyrobjs.'] /Category [/View] >>'
            .' ]'
            .' /Order ['.$lyrobjs.']'
            .' /ListMode /AllPages'
            //.' /RBGroups ['..']'
            //.' /Locked ['..']'
            .' >>'
            .' >>';
        return $out;
    }

    /**
     * Returns the PDF Catalog entry.
     *
     * @return string
     */
    protected function getOutCatalog()
    {
        $oid = ++$this->pon;
        $this->objid['catalog'] = $oid;
        $out = $oid.' 0 obj'."\n"
            .'<<'
            .' /Type /Catalog'
            .' /Version /'.$this->pdfver
            //.' /Extensions <<>>'
            .' /Pages '.$this->objid['pages'].' 0 R'
            //.' /PageLabels ' //...
            .' /Names <<';
        if (!$this->pdfa && !empty($this->objid['javascript'])) {
            $out .= ' /JavaScript '.$this->objid['javascript'];
        }
        if (!empty($this->efnames)) {
            $out .= ' /EmbeddedFiles << /Names [';
            foreach ($this->efnames as $fn => $fref) {
                $out .= ' '.$this->getOutTextString($fn, $oid).' '.$fref;
            }
            $out .= ' ] >>';
        }
        $out .= ' >>';

        if (!empty($this->objid['dests'])) {
            $out .= ' /Dests '.($this->objid['dests']).' 0 R';
        }

        $out .= $this->getOutViewerPref();

        if (!empty($this->display['layout'])) {
            $out .= ' /PageLayout /'.$this->display['layout'];
        }
        if (!empty($this->display['mode'])) {
            $out .= ' /PageMode /'.$this->display['mode'];
        }
        if (!empty($this->outlines)) {
            $out .= ' /Outlines '.$this->OutlineRoot.' 0 R';
            $out .= ' /PageMode /UseOutlines';
        }

        //$out .= ' /Threads []';

        $firstpage = $this->page->getPage(0);
        $fpo = $firstpage['n'];
        if ($this->display['zoom'] == 'fullpage') {
            $out .= ' /OpenAction ['.$fpo.' 0 R /Fit]';
        } elseif ($this->display['zoom'] == 'fullwidth') {
            $out .= ' /OpenAction ['.$fpo.' 0 R /FitH null]';
        } elseif ($this->display['zoom'] == 'real') {
            $out .= ' /OpenAction ['.$fpo.' 0 R /XYZ null null 1]';
        } elseif (!is_string($this->display['zoom'])) {
            $out .= sprintf(' /OpenAction ['.$fpo.' 0 R /XYZ null null %F]', ($this->display['zoom'] / 100));
        }

        //$out .= ' /AA <<>>';
        //$out .= ' /URI <<>>';
        $out .= ' /Metadata '.$this->objid['xmp'].' 0 R';
        //$out .= ' /StructTreeRoot <<>>';
        //$out .= ' /MarkInfo <<>>';

        if (!empty($this->l['a_meta_language'])) {
            $out .= ' /Lang '.$this->getOutTextString($this->l['a_meta_language'], $oid, true);
        }

        //$out .= ' /SpiderInfo <<>>';
        $out .= $this->getOutputIntents();
        //$out .= ' /PieceInfo <<>>';
        $out .= $this->getPDFLayers();

        // AcroForm
        if (!empty($this->objid['form'])
            || ($this->sign && isset($this->signature['cert_type']))
            || !empty($this->signature['appearance']['empty'])) {
            $out .= ' /AcroForm <<';
            $objrefs = '';
            if ($this->sign && isset($this->signature['cert_type'])) {
                // set reference for signature object
                $objrefs .= $this->objid['signature'].' 0 R';
            }
            if (!empty($this->signature['appearance']['empty'])) {
                foreach ($this->signature['appearance']['empty'] as $esa) {
                    // set reference for empty signature objects
                    $objrefs .= ' '.$esa['objid'].' 0 R';
                }
            }
            if (!empty($this->objid['form'])) {
                foreach ($this->objid['form'] as $objid) {
                    $objrefs .= ' '.$objid.' 0 R';
                }
            }
            $out .= ' /Fields ['.$objrefs.']';
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

            if (!empty($this->annotation_fonts)) {
                $out .= ' /DR << /Font <<';
                foreach ($this->annotation_fonts as $fontkey => $fontid) {
                    $out .= ' /F'.$fontid.' '.$this->font->getFont($fontkey)['n'].' 0 R';
                }
                $out .= ' >> >>';
            }

            $font = $this->font->getFont('helvetica');
            $out .= ' /DA (/F'.$font['i'].' 0 Tf 0 g)';
            $out .= ' /Q '.(($this->rtl)?'2':'0');
            //$out .= ' /XFA ';
            $out .= ' >>';


            // signatures
            if ($this->sign && isset($this->signature['cert_type'])
                && (empty($this->signature['approval']) || ($this->signature['approval'] != 'A'))) {
                $out .= ' /Perms << ';
                if ($this->signature['cert_type'] > 0) {
                    $out .= '/DocMDP ';
                } else {
                    $out .= '/UR3 ';
                }
                $out .= ($this->objid['signature'] + 1).' 0 R >>';
            }
        }

        //$out .= ' /Legal <<>>';
        //$out .= ' /Requirements []';
        //$out .= ' /Collection <<>>';
        //$out .= ' /NeedsRendering true';

        $out .= ' >>'."\n"
            .'endobj'."\n";
        return $out;
    }

    /**
     * Returns the PDF OCG entry.
     *
     * @return string
     */
    protected function getOutOCG()
    {
        if (empty($this->pdflayer)) {
            return '';
        }
        $out = '';
        foreach ($this->pdflayer as $key => $layer) {
            $oid = ++$this->pon;
            $this->pdflayer[$key]['objid'] = $oid;
            $out .= $oid.' 0 obj'."\n";
            $out .= '<< '
                .' /Type /OCG'
                .' /Name '.$this->getOutTextString($layer['name'], $oid, true)
                .' /Usage <<';
            if (isset($layer['print']) && ($layer['print'] !== null)) {
                $out .= ' /Print <</PrintState /'.$this->getOnOff($layer['print']).'>>';
            }
            $out .= ' /View <</ViewState /'.$this->getOnOff($layer['view']).'>>'
                .' >>'
                .' >>'."\n"
                .'endobj'."\n";
        }
        return $out;
    }

    /**
     * Returns the PDF Annotation code for Apearance Stream XObjects entry.
     *
     * @param int $width annotation width
     * @param int $height annotation height
     * @param string $stream appearance stream
     *
     * @return int
     */
    protected function getOutAPXObjects($width = 0, $height = 0, $stream = '')
    {
        $stream = trim($stream);
        $oid = ++$this->pon;
        $out = $oid.' 0 obj'."\n";
        $this->xobjects['AX'.$oid] = array('n' => $oid);
        $out .= '<<'
            .' /Type /XObject'
            .' /Subtype /Form'
            .' /FormType 1';
        if ($this->compress) {
            $stream = gzcompress($stream);
            $out .= ' /Filter /FlateDecode';
        }
        $stream = $this->_getrawstream($stream);
        $rect = sprintf('%F %F', $width, $height);
        $out .= ' /BBox [0 0 '.$rect.']'
            .' /Matrix [1 0 0 1 0 0]'
            .' /Resources 2 0 R'
            .' /Length '.strlen($stream)
            .' >>'
            .' stream'."\n"
            .$stream."\n"
            .'endstream'."\n"
            .'endobj'."\n";
        return $out;
    }

    /**
     * Returns the PDF XObjects entry.
     *
     * @return string
     */
    protected function getOutXObjects()
    {
        $out = '';
        foreach ($this->xobject as $key => $data) {
            if (empty($data['outdata'])) {
                continue;
            }
            $out .= ' '.$data['n'].' 0 R'."\n"
                .'<<'
                .' /Type /XObject'
                .' /Subtype /Form'
                .' /FormType 1';
            $stream = trim($data['outdata']);
            if ($this->compress) {
                $stream = gzcompress($stream);
                $out .= ' /Filter /FlateDecode';
            }
            $out .= sprintf(
                ' /BBox [%F %F %F %F]',
                ($data['x'] * $this->kunit),
                (-$data['y'] * $this->kunit),
                (($data['w'] + $data['x']) * $this->kunit),
                (($data['h'] - $data['y']) * $this->kunit)
            );
            $out .= ' /Matrix [1 0 0 1 0 0]'
                .' /Resources <<'
                .' /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]';
            if (!empty($data['fonts'])) {
                $fonts = $data['fonts']->getFonts();
                $out = ' /Font <<';
                foreach ($fonts as $font) {
                    $out .= ' /F'.$font['i'].' '.$font['n'].' 0 R';
                }
                $out .= ' >>';
            }
            if (!empty($data['extgstates'])) {
                $out .= $data['extgstates']->getOutExtGStateResources();
            }
            if (!empty($data['gradients'])) {
                $out .= $data['gradients']->getOutGradientResources();
            }
            if (!empty($data['spot_colors'])) {
                $out .= $data['spot_colors']->getPdfSpotResources();
            }
            // images or nested xobjects
            if (!empty($data['images']) || !empty($data['xobjects'])) {
                $out .= ' /XObject <<';
                foreach ($data['images'] as $imgid) {
                    $out .= ' /I'.$imgid.' '.$this->xobject['I'.$imgid]['n'].' 0 R';
                }
                foreach ($data['xobjects'] as $sub_id => $sub_objid) {
                    $out .= ' /'.$sub_id.' '.$sub_objid['n'].' 0 R';
                }
                $out .= ' >>';
            }
            $out .= ' >>';
            if (!empty($data['group'])) {
                // set transparency group
                $out .= ' /Group << /Type /Group /S /Transparency';
                if (is_array($data['group'])) {
                    if (!empty($data['group']['CS'])) {
                        $out .= ' /CS /'.$data['group']['CS'];
                    }
                    if (isset($data['group']['I'])) {
                        $out .= ' /I /'.($data['group']['I']===true?'true':'false');
                    }
                    if (isset($data['group']['K'])) {
                        $out .= ' /K /'.($data['group']['K']===true?'true':'false');
                    }
                }
                $out .= ' >>';
            }
            $stream = $this->encrypt->encryptString($stream, $data['n']);
            $out .= ' /Length '.strlen($stream)
                .' >>'
                .' stream'."\n"
                .$stream."\n"
                .'endstream'."\n"
                .'endobj'."\n";
        }
        return $out;
    }

    /**
     * Returns the PDF Resources Dictionary entry.
     *
     * @return string
     */
    protected function getOutResourcesDict()
    {
        $this->objid['resdic'] = $this->page->getResourceDictObjID();
        $out = $this->objid['resdic'].' 0 obj'."\n"
            .'<<'
            .' /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]'
            .$this->getOutFontDic()
            .$this->getXObjectDic()
            .$this->getLayerDic()
            .$this->graph->getOutExtGStateResources()
            .$this->graph->getOutGradientResources()
            .$this->color->getPdfSpotResources()
            .' >>'."\n"
            .'endobj'."\n";
        return $out;
    }

    /**
     * Returns the PDF Destinations entry.
     *
     * @return string
     */
    protected function getOutDestinations()
    {
        if (empty($this->dests)) {
            return '';
        }
        $oid = ++$this->pon;
        $this->objid['dests'] = $oid;
        $out .= $oid.' 0 obj'."\n"
            .'<< ';
        foreach ($this->dests as $name => $dst) {
            $page = $this->page->getPage($dst['p']);
            $poid = $page['n'];
            $pgx = ($dst['x'] * $this->page->getKUnit());
            $pgy = ($page['pheight'] - ($dst['y'] * $this->page->getKUnit()));
            $out .= ' /'.$name.' '.sprintf('[%u 0 R /XYZ %F %F null]', $poid, $pgx, $pgy);
        }
        $out .= ' >>'."\n"
            .'endobj'."\n";
        return $out;
    }

    /**
     * Returns the PDF Embedded Files entry.
     *
     * @return string
     */
    protected function getOutEmbeddedFiles()
    {
        if (($this->pdfa == 1 ) || ($this->pdfa == 2)) {
            // embedded files are not allowed in PDF/A mode version 1 and 2
            return '';
        }
        reset($this->embeddedfiles);
        foreach ($this->embeddedfiles as $name => $data) {
            try {
                $content = $this->file->fileGetContents($data['file']);
            } catch (Exception $e) {
                continue; // silently skip the file
            }
            $rawsize = strlen($content);
            if ($rawsize <= 0) {
                continue; // silently skip the file
            }
            // update name tree
            $oid = $data['f'];
            $this->efnames[$name] = $oid.' 0 R';
            // embedded file specification object
            $out = $oid.' 0 obj'."\n"
                .'<<'
                .' /Type /Filespec /F '.$this->getOutTextString($name, $oid)
                .' /UF '.$this->getOutTextString($name, $oid)
                .' /AFRelationship /Source'
                .' /EF <</F '.$data['n'].' 0 R>>'
                .' >>'."\n"
                .'endobj'."\n";
            // embedded file object
            $filter = '';
            if ($this->pdfa == 3) {
                $filter = ' /Subtype /text#2Fxml';
            } elseif ($this->compress) {
                $content = gzcompress($content);
                $filter = ' /Filter /FlateDecode';
            }
            $stream = $this->encrypt->encryptString($content, $data['n']);
            $out .= "\n"
                .$data['n'].' 0 obj'."\n"
                .'<<'
                .' /Type /EmbeddedFile'
                .$filter
                .' /Length '.strlen($stream)
                .' /Params <</Size '.$rawsize.'>>'
                .' >>'
                .' stream'."\n"
                .$stream."\n"
                .'endstream'."\n"
                .'endobj'."\n";
            return $out;
        }
    }

    /**
     * Convert a color array into a string representation for annotations.
     * The number of array elements determines the colour space in which the colour shall be defined:
     *     0 No colour; transparent
     *     1 DeviceGray
     *     3 DeviceRGB
     *     4 DeviceCMYK
     *
     * @param array $colors Array of colors.
     *
     * @return string
     */
    protected static function getColorStringFromArray($colors)
    {
        $col = array_values($colors);
        $out = '[';
        switch (count($c)) {
            case 4: // CMYK
                $out .= sprintf(
                    '%F %F %F %F',
                    (max(0, min(100, floatval($col[0]))) / 100),
                    (max(0, min(100, floatval($col[1]))) / 100),
                    (max(0, min(100, floatval($col[2]))) / 100),
                    (max(0, min(100, floatval($col[3]))) / 100)
                );
                break;
            case 3: // RGB
                $out .= sprintf(
                    '%F %F %F',
                    (max(0, min(255, floatval($col[0]))) / 255),
                    (max(0, min(255, floatval($col[1]))) / 255),
                    (max(0, min(255, floatval($col[2]))) / 255)
                );
                break;
            case 1: // grayscale
                $out .= sprintf(
                    '%F',
                    (max(0, min(255, floatval($col[0]))) / 255)
                );
                break;
        }
        $out .= ']';
        return $out;
    }

    /**
     * Returns the PDF Annotations entry.
     *
     * @return string
     */
    protected function getOutAnnotations()
    {
        $out = '';
        $pages = $this->page->getPages();
        foreach ($pages as $num => $page) {
            foreach ($page['annotrefs'] as $key => $oid) {
                $annot = $this->pageAnnots[$oid];
                $annot['opt'] = array_change_key_case($annot['opt'], CASE_LOWER);
                $out .= $this->getAnnotationRadiobuttonGroups($annot);
                $orx = $annot['x'] * $this->kunit;
                $ory = $page['height'] - (($annot['y'] + $annot['h']) * $this->kunit);
                $width = $annot['w'] * $this->kunit;
                $height = $annot['h'] * $this->kunit;
                $rect = sprintf('%F %F %F %F', $orx, $ory, $orx+$width, $ory+$height);
                $out .= $oid.' 0 R'."\n"
                    .'<<'
                    .' /Type /Annot'
                    .' /Subtype /'.$annot['opt']['subtype']
                    .' /Rect ['.$rect.']';
                $ft = array('Btn', 'Tx', 'Ch', 'Sig');
                $formfield = (!empty($annot['opt']['ft']) && in_array($annot['opt']['ft'], $ft));
                if ($formfield) {
                    $out .= ' /FT /'.$annot['opt']['ft'];
                }
                if ($annot['opt']['subtype'] !== 'Link') {
                    $out .= ' /Contents '.$this->getOutTextString($annot['txt'], $oid, true);
                }
                $out .= ' /P '.$page['n'].' 0 R'
                    .' /NM '.$this->encrypt->escapeDataString(sprintf('%04u-%04u', $n, $key), $oid)
                    .' /M '.$this->getOutDateTimeString($this->docmodtime, $oid)
                    .$this->getOutAnnotationFlags($annot)
                    .$this->getAnnotationAppearanceStream($annot, $width, $height)
                    .$this->getAnnotationBorder($annot);
                if (!empty($annot['opt']['c']) && is_array($annot['opt']['c'])) {
                    $out .= ' /C '.$this->getColorStringFromArray($annot['opt']['c']);
                }
                //$out .= ' /StructParent ';
                //$out .= ' /OC ';
                $out .= $this->getOutAnnotationMarkups($annot, $oid)
                    .$this->getOutAnnotationOptSubtype($annot, $pagenum, $oid, $key)
                    .' >>'."\n"
                    .'endobj'."\n";
                if ($formfield && !isset($this->radiobuttonGroups[$n][$annot['txt']])) {
                    $this->objid['form'][] = $oid;
                }
            }
        }
        return $out;
    }

    /**
     * Returns the Annotation code for Radio buttons.
     *
     * @params array $annot   Array containing page annotations.
     *
     * @return string
     */
    protected function getAnnotationRadiobuttonGroups($annot)
    {
        $out = '';
        if (empty($this->radiobuttonGroups[$n][$annot['txt']])
            || !is_array($this->radiobuttonGroups[$n][$annot['txt']])) {
            return $out;
        }
        $oid = $this->radiobuttonGroups[$n][$annot['txt']]['n'];
        $out = $oid.' 0 obj'."\n"
            .'<<'
            .' /Type /Annot'
            .' /Subtype /Widget'
            .' /Rect [0 0 0 0]';
        if ($this->radiobuttonGroups[$n][$annot['txt']]['#readonly#']) {
            // read only
            $out .= ' /F 68 /Ff 49153';
        } else {
            $out .= ' /F 4 /Ff 49152'; // default print for PDF/A
        }
        $out .= ' /T '.$this->encrypt->escapeDataString($annot['txt'], $oid);
        if (!empty($annot['opt']['tu']) && is_string($annot['opt']['tu'])) {
            $out .= ' /TU '.$this->encrypt->escapeDataString($annot['opt']['tu'], $oid);
        }
        $out .= ' /FT /Btn /Kids [';
        $defval = '';
        foreach ($this->radiobuttonGroups[$n][$annot['txt']] as $key => $data) {
            if (isset($data['kid'])) {
                $out .= ' '.$data['kid'].' 0 R';
                if ($data['def'] !== 'Off') {
                    $defval = $data['def'];
                }
            }
        }
        $out .= ' ]';
        if (!empty($defval)) {
            $out .= ' /V /'.$defval;
        }
        $out .= ' >>'."\n"
            .'endobj'."\n";
        $this->objid['form'][] = $oid;
        // store object id to be used on Parent entry of Kids
        $this->radiobuttonGroups[$n][$annot['txt']] = $oid;
        return $out;
    }

    /**
     * Returns the Annotation code for Appearance Stream.
     *
     * @params array $annot  Array containing page annotations.
     * @param int $width     Annotation width.
     * @param int $height    Annotation height.
     *
     * @return string
     */
    protected function getAnnotationAppearanceStream($annot, $width = 0, $height = 0)
    {
        $out = '';
        if (!empty($annot['opt']['as']) && is_string($annot['opt']['as'])) {
            $out .= ' /AS /'.$annot['opt']['as'];
        }
        if (empty($annot['opt']['ap'])) {
            return $out;
        }
        $out .= ' /AP <<';
        if (!is_array($annot['opt']['ap'])) {
            $out .= $annot['opt']['ap'];
        } else {
            foreach ($annot['opt']['ap'] as $mode => $def) {
                // $mode can be: n = normal; r = rollover; d = down;
                $out .= ' /'.strtoupper($mode);
                if (is_array($def)) {
                    $out .= ' <<';
                    foreach ($def as $apstate => $stream) {
                        // reference to XObject that define the appearance for this mode-state
                        $apsobjid = $this->getOutAPXObjects($width, $height, $stream);
                        $out .= ' /'.$apstate.' '.$apsobjid.' 0 R';
                    }
                    $out .= ' >>';
                } else {
                    // reference to XObject that define the appearance for this mode
                    $apsobjid = $this->getOutAPXObjects($width, $height, $def);
                    $out .= ' '.$apsobjid.' 0 R';
                }
            }
        }
        $out .= ' >>';
        return $out;
    }

    /**
     * Returns the Annotation code for Borders.
     *
     * @params array $annot  Array containing page annotations.
     *
     * @return string
     */
    protected function getAnnotationBorder($annot)
    {
        $out = '';
        if (!empty($annot['opt']['bs']) && (is_array($annot['opt']['bs']))) {
            $out .= ' /BS <<'
                .' /Type /Border';
            if (isset($annot['opt']['bs']['w'])) {
                $out .= ' /W '.intval($annot['opt']['bs']['w']);
            }
            $bstyles = array('S', 'D', 'B', 'I', 'U');
            if (!empty($annot['opt']['bs']['s']) && in_array($annot['opt']['bs']['s'], $bstyles)) {
                $out .= ' /S /'.$annot['opt']['bs']['s'];
            }
            if (isset($annot['opt']['bs']['d']) && (is_array($annot['opt']['bs']['d']))) {
                $out .= ' /D [';
                foreach ($annot['opt']['bs']['d'] as $cord) {
                    $out .= ' '.intval($cord);
                }
                $out .= ']';
            }
            $out .= ' >>';
        } else {
            $out .= ' /Border [';
            if (isset($annot['opt']['border']) && (count($annot['opt']['border']) >= 3)) {
                $out .= intval($annot['opt']['border'][0])
                    .' '.intval($annot['opt']['border'][1])
                    .' '.intval($annot['opt']['border'][2]);
                if (isset($annot['opt']['border'][3]) && is_array($annot['opt']['border'][3])) {
                    $out .= ' [';
                    foreach ($annot['opt']['border'][3] as $dash) {
                        $out .= ' '.intval($dash);
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
            $bstyles = array('S', 'C');
            if (!empty($annot['opt']['be']['s']) && in_array($annot['opt']['be']['s'], $bstyles)) {
                $out .= ' /S /'.$annot['opt']['bs']['s'];
            } else {
                $out .= ' /S /S';
            }
            if (isset($annot['opt']['be']['i'])
                && ($annot['opt']['be']['i'] >= 0)
                && ($annot['opt']['be']['i'] <= 2)) {
                $out .= ' /I '.sprintf(' %F', $annot['opt']['be']['i']);
            }
            $out .= '>>';
        }
        return $out;
    }

    /**
     * Returns the Annotation code for Makups.
     *
     * @params array $annot Array containing page annotations.
     * @params int   $oid   Annotation Object ID.
     *
     * @return string
     */
    protected function getOutAnnotationMarkups($annot, $oid)
    {
        $out = '';
        $markups = array(
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
            'sound'
        );
        if (empty($annot['opt']['subtype']) || !in_array(strtolower($annot['opt']['subtype']), $markups)) {
            return $out;
        }
        if (!empty($annot['opt']['t']) && is_string($annot['opt']['t'])) {
            $out .= ' /T '.$this->getOutTextString($annot['opt']['t'], $oid, true);
        }
        //$out .= ' /Popup ';
        if (isset($annot['opt']['ca'])) {
            $out .= ' /CA '.sprintf('%F', floatval($annot['opt']['ca']));
        }
        if (isset($annot['opt']['rc'])) {
            $out .= ' /RC '.$this->getOutTextString($annot['opt']['rc'], $oid, true);
        }
        $out .= ' /CreationDate '.$this->getOutDateTimeString($this->doctime, $oid);
        //$out .= ' /IRT ';
        if (isset($annot['opt']['subj'])) {
            $out .= ' /Subj '.$this->getOutTextString($annot['opt']['subj'], $oid, true);
        }
        //$out .= ' /RT ';
        //$out .= ' /IT ';
        //$out .= ' /ExData ';
        return $out;
    }

    /**
     * Returns the Annotation code for Flags.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationFlags($annot)
    {
        $fval = 4;
        if (isset($annot['opt']['f'])) {
            $fval = $this->getAnnotationFlagsCode($annot['opt']['f']);
        }
        if ($this->pdfa > 0) {
            // force print flag for PDF/A mode
            $fval |= 4;
        }
        return' /F '.intval($fval);
    }

    /**
     * Returns the Annotation Flags code.
     *
     * @params array|int $flags Annotation flags.
     *
     * @return int
     */
    protected function getAnnotationFlagsCode($flags)
    {
        if (!is_array($flags)) {
            return intval($flags);
        }
        $fval = 0;
        foreach ($flags as $f) {
            switch (strtolower($f)) {
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
     * @params array $annot   Array containing page annotations.
     * @params int   $pagenum Page number.
     * @params int   $oid     Annotation Object ID.
     * @params int   $key     Annotation index in the current page.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtype($annot, $pagenum, $oid, $key)
    {
        switch (strtolower($annot['opt']['subtype'])) {
            case 'text':
                return $this->getOutAnnotationOptSubtypeText($annot);
            case 'link':
                return $this->getOutAnnotationOptSubtypeLink($annot, $pagenum, $oid);
            case 'freetext':
                return $this->getOutAnnotationOptSubtypeFreetext($annot);
            case 'line':
                return $this->getOutAnnotationOptSubtypeLine($annot);
            case 'square':
                return $this->getOutAnnotationOptSubtypeSquare($annot);
            case 'circle':
                return $this->getOutAnnotationOptSubtypeCircle($annot);
            case 'polygon':
                return $this->getOutAnnotationOptSubtypePolygon($annot);
            case 'polyline':
                return $this->getOutAnnotationOptSubtypePolyline($annot);
            case 'highlight':
                return $this->getOutAnnotationOptSubtypeHighlight($annot);
            case 'underline':
                return $this->getOutAnnotationOptSubtypeUnderline($annot);
            case 'squiggly':
                return $this->getOutAnnotationOptSubtypeSquiggly($annot);
            case 'strikeout':
                return $this->getOutAnnotationOptSubtypeStrikeout($annot);
            case 'stamp':
                return $this->getOutAnnotationOptSubtypeStamp($annot);
            case 'caret':
                return $this->getOutAnnotationOptSubtypeCaret($annot);
            case 'ink':
                return $this->getOutAnnotationOptSubtypeInk($annot);
            case 'popup':
                return $this->getOutAnnotationOptSubtypePopup($annot);
            case 'fileattachment':
                return $this->getOutAnnotationOptSubtypeFileattachment($annot, $key);
            case 'sound':
                return $this->getOutAnnotationOptSubtypeSound($annot);
            case 'movie':
                return $this->getOutAnnotationOptSubtypeMovie($annot);
            case 'widget':
                return $this->getOutAnnotationOptSubtypeWidget($annot, $oid);
            case 'screen':
                return $this->getOutAnnotationOptSubtypeScreen($annot);
            case 'printermark':
                return $this->getOutAnnotationOptSubtypePrintermark($annot);
            case 'trapnet':
                return $this->getOutAnnotationOptSubtypeTrapnet($annot);
            case 'watermark':
                return $this->getOutAnnotationOptSubtypeWatermark($annot);
            case '3d':
                return $this->getOutAnnotationOptSubtype3D($annot);
        }
        return '';
    }


    /**
     * Returns the output code associated with the annotation opt.subtype.text.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeText($annot)
    {
        $out = '';
        if (isset($annot['opt']['open'])) {
            $out .= ' /Open '. (strtolower($annot['opt']['open']) == 'true' ? 'true' : 'false');
        }
        $iconsapp = array('Comment', 'Help', 'Insert', 'Key', 'NewParagraph', 'Note', 'Paragraph');
        if (isset($annot['opt']['name']) && in_array($annot['opt']['name'], $iconsapp)) {
            $out .= ' /Name /'.$annot['opt']['name'];
        } else {
            $out .= ' /Name /Note';
        }
        $hasStateModel = isset($annot['opt']['statemodel']);
        $hasState = isset($annot['opt']['state']);
        $statemodels = array('Marked', 'Review');
        if (!$hasStateModel && !$hasState) {
            return $out;
        }
        if ($hasStateModel && in_array($annot['opt']['statemodel'], $statemodels)) {
            $out .= ' /StateModel /'.$annot['opt']['statemodel'];
        } else {
            $annot['opt']['statemodel'] = 'Marked';
            $out .= ' /StateModel /'.$annot['opt']['statemodel'];
        }
        if ($annot['opt']['statemodel'] == 'Marked') {
            $states = array('Accepted', 'Unmarked');
        } else {
            $states = array('Accepted', 'Rejected', 'Cancelled', 'Completed', 'None');
        }
        if ($hasState && in_array($annot['opt']['state'], $states)) {
            $out .= ' /State /'.$annot['opt']['state'];
        } else {
            if ($annot['opt']['statemodel'] == 'Marked') {
                $out .= ' /State /Unmarked';
            } else {
                $out .= ' /State /None';
            }
        }
        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.link.
     *
     * @params array $annot   Array containing page annotations.
     * @params int   $pagenum Page number.
     * @params int   $oid     Annotation Object ID.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeLink($annot, $pagenum, $oid)
    {
        $out = '';
        if (!empty($annot['txt']) && is_string($annot['txt'])) {
            switch ($annot['txt'][0]) {
                case '#': // internal destination
                    $out .= ' /A << /S /GoTo /D /'.$this->encrypt->encodeNameObject(substr($annot['txt'], 1)).'>>';
                    break;
                case '%': // embedded PDF file
                    $filename = basename(substr($annot['txt'], 1));
                    $out .= ' /A <<'
                        .' /S /GoToE'
                        .' /D [0 /Fit]'
                        .' /NewWindow true'
                        .' /T <<'
                        .' /R /C /P '.($pagenum - 1)
                        .' /A '.$this->embeddedfiles[$filename]['a']
                        .' >>'
                        .' >>';
                    break;
                case '*': // embedded generic file
                    $filename = basename(substr($annot['txt'], 1));
                    $jsa = 'var D=event.target.doc;'
                        .'var MyData=D.dataObjects;'
                        .'for (var i in MyData)'
                        .' if (MyData[i].path=="'.$filename.'")'
                        .' D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
                    $out .= ' /A << /S /JavaScript /JS '.$this->getOutTextString($jsa, $oid, true).' >>';
                    break;
                default:
                    $parsedUrl = parse_url($annot['txt']);
                    if (empty($parsedUrl['scheme'])
                        && (!empty($parsedUrl['path'])
                        && strtolower(substr($parsedUrl['path'], -4)) == '.pdf')) {
                        // relative link to a PDF file
                        $dest = '[0 /Fit]'; // default page 0
                        if (!empty($parsedUrl['fragment'])) {
                            // check for named destination
                            $tmp = explode('=', $parsedUrl['fragment']);
                            $dest = '('.((count($tmp) == 2) ? $tmp[1] : $tmp[0]).')';
                        }
                        $out .= ' /A <<'
                            .' /S /GoToR'
                            .' /D '.$dest
                            .' /F '.$this->encrypt->escapeDataString($this->unhtmlentities($parsedUrl['path']), $oid)
                            .' /NewWindow true'
                            .' >>';
                    } else {
                        // external URI link
                        $out .= ' /A <<'
                            .' /S /URI'
                            .' /URI '.$this->encrypt->escapeDataString($this->unhtmlentities($annot['txt']), $oid)
                            .' >>';
                    }
                    break;
            }
        } elseif (!empty($this->links[$annot['txt']])) {
            // internal link ID
            $l = $this->links[$annot['txt']];
            $page = $this->page->getPage($l['p']);
            $y = ($page['height'] - ($l['y'] * $this->kunit));
            $out .= sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $page['n'], $y);
        }
        $hmodes = array('N', 'I', 'O', 'P');
        if (!empty($annot['opt']['h']) && in_array($annot['opt']['h'], $hmodes)) {
            $out .= ' /H /'.$annot['opt']['h'];
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
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeFreetext($annot)
    {
        $out = '';
        if (!empty($annot['opt']['da'])) {
            $out .= ' /DA ('.$annot['opt']['da'].')';
        }
        if (isset($annot['opt']['q']) && ($annot['opt']['q'] >= 0) && ($annot['opt']['q'] <= 2)) {
            $out .= ' /Q '.intval($annot['opt']['q']);
        }
        if (isset($annot['opt']['rc'])) {
            $out .= ' /RC '.$this->getOutTextString($annot['opt']['rc'], $annot_obj_id, true);
        }
        if (isset($annot['opt']['ds'])) {
            $out .= ' /DS '.$this->getOutTextString($annot['opt']['ds'], $annot_obj_id, true);
        }
        if (isset($annot['opt']['cl']) && is_array($annot['opt']['cl'])) {
            $out .= ' /CL [';
            foreach ($annot['opt']['cl'] as $cl) {
                $out .= sprintf('%F ', $cl * $this->kunit);
            }
            $out .= ']';
        }
        $tfit = array('FreeText', 'FreeTextCallout', 'FreeTextTypeWriter');
        if (isset($annot['opt']['it']) && in_array($annot['opt']['it'], $tfit)) {
            $out .= ' /IT /'.$annot['opt']['it'];
        }
        if (isset($annot['opt']['rd']) && is_array($annot['opt']['rd'])) {
            $l = $annot['opt']['rd'][0] * $this->kunit;
            $r = $annot['opt']['rd'][1] * $this->kunit;
            $t = $annot['opt']['rd'][2] * $this->kunit;
            $b = $annot['opt']['rd'][3] * $this->kunit;
            $out .= ' /RD ['.sprintf('%F %F %F %F', $l, $r, $t, $b).']';
        }
        $lineendings = array(
            'Square',
            'Circle',
            'Diamond',
            'OpenArrow',
            'ClosedArrow',
            'None',
            'Butt',
            'ROpenArrow',
            'RClosedArrow',
            'Slash'
        );
        if (isset($annot['opt']['le']) && in_array($annot['opt']['le'], $lineendings)) {
            $out .= ' /LE /'.$annot['opt']['le'];
        }
        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.line.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeLine($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.square.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeSquare($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.circle.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeCircle($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.polygon.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypePolygon($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.polyline.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypePolyline($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.highlight.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeHighlight($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeUnderline($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.squiggly.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeSquiggly($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.strikeout.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeStrikeout($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.stamp.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeStamp($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.caret.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeCaret($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.ink.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeInk($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.popup.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypePopup($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.fileattachment.
     *
     * @params array $annot Array containing page annotations.
     * @params int   $key   Annotation index in the current page.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeFileattachment($annot, $key)
    {
        $out = '';
        if (($this->pdfa == 1 ) || ($this->pdfa == 2) || !isset($annot['opt']['fs'])) {
            // embedded files are not allowed in PDF/A mode version 1 and 2
            return $out;
        }
        $filename = basename($annot['opt']['fs']);
        if (isset($this->embeddedfiles[$filename]['f'])) {
            $out .= ' /FS '.$this->embeddedfiles[$filename]['f'].' 0 R';
            $iconsapp = array('Graph', 'Paperclip', 'PushPin', 'Tag');
            if (isset($annot['opt']['name']) && in_array($annot['opt']['name'], $iconsapp)) {
                $out .= ' /Name /'.$annot['opt']['name'];
            } else {
                $out .= ' /Name /PushPin';
            }
            // index (zero-based) of the annotation in the Annots array of this page
            $this->embeddedfiles[$filename]['a'] = $key;
        }
        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.sound.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeSound($annot)
    {
        $out = '';
        if (empty($annot['opt']['fs'])) {
            return $out;
        }
        $filename = basename($annot['opt']['fs']);
        if (isset($this->embeddedfiles[$filename]['f'])) {
            // ... TO BE COMPLETED ...
            // /R /C /B /E /CO /CP
            $out .= ' /Sound '.$this->embeddedfiles[$filename]['f'].' 0 R';
            $iconsapp = array('Speaker', 'Mic');
            if (isset($annot['opt']['name']) && in_array($annot['opt']['name'], $iconsapp)) {
                $out .= ' /Name /'.$annot['opt']['name'];
            } else {
                $out .= ' /Name /Speaker';
            }
        }
        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.movie.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeMovie($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.widget.
     *
     * @params array $annot Array containing page annotations.
     * @params int   $oid   Annotation Object ID.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeWidget($annot, $oid)
    {
        $out = '';
        $hmode = array('N', 'I', 'O', 'P', 'T');
        if (!empty($annot['opt']['h']) && in_array($annot['opt']['h'], $hmode)) {
            $out .= ' /H /'.$annot['opt']['h'];
        }
        if (!empty($annot['opt']['mk']) && is_array($annot['opt']['mk'])) {
            $out .= ' /MK <<';
            if (isset($annot['opt']['mk']['r'])) {
                $out .= ' /R '.$annot['opt']['mk']['r'];
            }
            if (isset($annot['opt']['mk']['bc']) && (is_array($annot['opt']['mk']['bc']))) {
                $out .= ' /BC '.$this->getColorStringFromArray($annot['opt']['mk']['bc']);
            }
            if (isset($annot['opt']['mk']['bg']) && (is_array($annot['opt']['mk']['bg']))) {
                $out .= ' /BG '.$this->getColorStringFromArray($annot['opt']['mk']['bg']);
            }
            if (isset($annot['opt']['mk']['ca'])) {
                $out .= ' /CA '.$annot['opt']['mk']['ca'];
            }
            if (isset($annot['opt']['mk']['rc'])) {
                $out .= ' /RC '.$annot['opt']['mk']['rc'];
            }
            if (isset($annot['opt']['mk']['ac'])) {
                $out .= ' /AC '.$annot['opt']['mk']['ac'];
            }
            if (isset($annot['opt']['mk']['i'])) {
                $info = $this->getImageBuffer($annot['opt']['mk']['i']);
                if ($info !== false) {
                    $out .= ' /I '.$info['n'].' 0 R';
                }
            }
            if (isset($annot['opt']['mk']['ri'])) {
                $info = $this->getImageBuffer($annot['opt']['mk']['ri']);
                if ($info !== false) {
                    $out .= ' /RI '.$info['n'].' 0 R';
                }
            }
            if (isset($annot['opt']['mk']['ix'])) {
                $info = $this->getImageBuffer($annot['opt']['mk']['ix']);
                if ($info !== false) {
                    $out .= ' /IX '.$info['n'].' 0 R';
                }
            }
            if (!empty($annot['opt']['mk']['if']) && is_array($annot['opt']['mk']['if'])) {
                $out .= ' /IF <<';
                $if_sw = array('A', 'B', 'S', 'N');
                if (isset($annot['opt']['mk']['if']['sw']) && in_array($annot['opt']['mk']['if']['sw'], $if_sw)) {
                    $out .= ' /SW /'.$annot['opt']['mk']['if']['sw'];
                }
                $if_s = array('A', 'P');
                if (isset($annot['opt']['mk']['if']['s']) && in_array($annot['opt']['mk']['if']['s'], $if_s)) {
                    $out .= ' /S /'.$annot['opt']['mk']['if']['s'];
                }
                if (isset($annot['opt']['mk']['if']['a'])
                    && (is_array($annot['opt']['mk']['if']['a']))
                    && !empty($annot['opt']['mk']['if']['a'])) {
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
            if (isset($annot['opt']['mk']['tp'])
                && ($annot['opt']['mk']['tp'] >= 0)
                && ($annot['opt']['mk']['tp'] <= 6)) {
                $out .= ' /TP '.intval($annot['opt']['mk']['tp']);
            }
            $out .= '>>';
        }
        // --- Entries for field dictionaries ---
        if (isset($this->radiobuttonGroups[$n][$annot['txt']])) {
            // set parent
            $out .= ' /Parent '.$this->radiobuttonGroups[$n][$annot['txt']].' 0 R';
        }
        if (isset($annot['opt']['t']) && is_string($annot['opt']['t'])) {
            $out .= ' /T '.$this->encrypt->escapeDataString($annot['opt']['t'], $oid);
        }
        if (isset($annot['opt']['tu']) && is_string($annot['opt']['tu'])) {
            $out .= ' /TU '.$this->encrypt->escapeDataString($annot['opt']['tu'], $oid);
        }
        if (isset($annot['opt']['tm']) && is_string($annot['opt']['tm'])) {
            $out .= ' /TM '.$this->encrypt->escapeDataString($annot['opt']['tm'], $oid);
        }
        if (isset($annot['opt']['ff'])) {
            if (is_array($annot['opt']['ff'])) {
                // array of bit settings
                $flag = 0;
                foreach ($annot['opt']['ff'] as $val) {
                    $flag += 1 << ($val - 1);
                }
            } else {
                $flag = intval($annot['opt']['ff']);
            }
            $out .= ' /Ff '.$flag;
        }
        if (isset($annot['opt']['maxlen'])) {
            $out .= ' /MaxLen '.intval($annot['opt']['maxlen']);
        }
        if (isset($annot['opt']['v'])) {
            $out .= ' /V';
            if (is_array($annot['opt']['v'])) {
                foreach ($annot['opt']['v'] as $optval) {
                    if (is_float($optval)) {
                        $optval = sprintf('%F', $optval);
                    }
                    $out .= ' '.$optval;
                }
            } else {
                $out .= ' '.$this->getOutTextString($annot['opt']['v'], $oid, true);
            }
        }
        if (isset($annot['opt']['dv'])) {
            $out .= ' /DV';
            if (is_array($annot['opt']['dv'])) {
                foreach ($annot['opt']['dv'] as $optval) {
                    if (is_float($optval)) {
                        $optval = sprintf('%F', $optval);
                    }
                    $out .= ' '.$optval;
                }
            } else {
                $out .= ' '.$this->getOutTextString($annot['opt']['dv'], $oid, true);
            }
        }
        if (isset($annot['opt']['rv'])) {
            $out .= ' /RV';
            if (is_array($annot['opt']['rv'])) {
                foreach ($annot['opt']['rv'] as $optval) {
                    if (is_float($optval)) {
                        $optval = sprintf('%F', $optval);
                    }
                    $out .= ' '.$optval;
                }
            } else {
                $out .= ' '.$this->getOutTextString($annot['opt']['rv'], $oid, true);
            }
        }
        if (!empty($annot['opt']['a'])) {
            $out .= ' /A << '.$annot['opt']['a'].' >>';
        }
        if (!empty($annot['opt']['aa'])) {
            $out .= ' /AA << '.$annot['opt']['aa'].' >>';
        }
        if (!empty($annot['opt']['da'])) {
            $out .= ' /DA ('.$annot['opt']['da'].')';
        }
        if (isset($annot['opt']['q']) && ($annot['opt']['q'] >= 0) && ($annot['opt']['q'] <= 2)) {
            $out .= ' /Q '.intval($annot['opt']['q']);
        }
        if (!empty($annot['opt']['opt']) && is_array($annot['opt']['opt'])) {
            $out .= ' /Opt [';
            foreach ($annot['opt']['opt'] as $copt) {
                if (is_array($copt)) {
                    $out .= ' ['.$this->getOutTextString($copt[0], $oid, true)
                        .' '.$this->getOutTextString($copt[1], $oid, true).']';
                } else {
                    $out .= ' '.$this->getOutTextString($copt, $oid, true);
                }
            }
            $out .= ']';
        }
        if (isset($annot['opt']['ti'])) {
            $out .= ' /TI '.intval($annot['opt']['ti']);
        }
        if (!empty($annot['opt']['i']) && is_array($annot['opt']['i'])) {
            $out .= ' /I [';
            foreach ($annot['opt']['i'] as $copt) {
                $out .= intval($copt).' ';
            }
            $out .= ']';
        }
        return $out;
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.screen.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeScreen($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.printermark.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypePrintermark($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.trapnet.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeTrapnet($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.watermark.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtypeWatermark($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the output code associated with the annotation opt.subtype.3d.
     *
     * @params array $annot Array containing page annotations.
     *
     * @return string
     */
    protected function getOutAnnotationOptSubtype3D($annot)
    {
        // @TODO
        return '';
    }

    /**
     * Returns the PDF Javascript entry.
     *
     * @return string
     */
    protected function getOutJavascript()
    {
        if (($this->pdfa > 0) || (empty($this->javascript) && empty($this->jsobjects))) {
            return;
        }
        if (strpos($this->javascript, 'this.addField') > 0) {
            if (!$this->userrights['enabled']) {
                // $this->setUserRights();
            }
            // The following two lines are used to avoid form fields duplication after saving.
            // The addField method only works when releasing user rights (UR3).
            $pattern = "ftcpdfdocsaved=this.addField('%s','%s',%d,[%F,%F,%F,%F]);";
            $jsa = sprintf($pattern, 'tcpdfdocsaved', 'text', 0, 0, 1, 0, 1);
            $jsb = "getField('tcpdfdocsaved').value='saved';";
            $this->javascript = $jsa."\n".$this->javascript."\n".$jsb;
        }
        $out = '';
        // name tree for javascript
        $njs = '<< /Names [';
        if (!empty($this->javascript)) {
            // default Javascript object
            $oid = ++$this->pon;
            $out .= $oid.' 0 obj'."\n"
            .'<<'
            .' /S /JavaScript /JS '
            .$this->getOutTextString($this->javascript, $oid, true)
            .' >>'."\n"
            .'endobj'."\n";
            $njs .= ' (EmbeddedJS) '.$oid.' 0 R';
        }
        foreach ($this->jsobjects as $key => $val) {
            if ($val['onload']) {
                // additional Javascript object
                $oid = ++$this->pon;
                $out .= $oid.' 0 obj'."\n"
                .'<< '
                .'/S /JavaScript /JS '
                .$this->getOutTextString($val['js'], $oid, true)
                .' >>'."\n"
                .'endobj'."\n";
                $njs .= ' (JS'.$key.') '.$oid.' 0 R';
            }
        }
        $njs .= ' ] >>';
        $this->jstree = $njs;
        return $out;
    }

    /**
     * Sort bookmarks by page and original position.
     */
    protected function sortBookmarks()
    {
        $outline_p = array();
        $outline_y = array();
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
    protected function processPrevNextBookmarks()
    {
        $this->sortBookmarks();
        $lru = array();
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
    protected function unhtmlentities($text_to_convert)
    {
        return html_entity_decode($text_to_convert, ENT_QUOTES, $this->encoding);
    }

    /**
     * Returns the PDF Bookmarks entry.
     *
     * @return string
     */
    protected function getOutBookmarks()
    {
        if (empty($this->outlines)) {
            return '';
        }
        $numbookmarks = is_countable($this->outlines)?count($this->outlines):0;
        if ($numbookmarks <= 0) {
            return;
        }
        $root_oid = $this->processPrevNextBookmarks();
        $first_oid = $this->pon + 1;
        $nltags = '/<br[\s]?\/>|<\/(blockquote|dd|dl|div|dt|h1|h2|h3|h4|h5|h6|hr|li|ol|p|pre|ul|tcpdf|table|tr|td)>/si';
        foreach ($this->outlines as $i => $o) {
            // covert HTML title to string
            $title = preg_replace($nltags, "\n", $o['t']);
            $title = preg_replace("/[\r]+/si", '', $title);
            $title = preg_replace("/[\n]+/si", "\n", $title);
            $title = strip_tags($title);
            $title = preg_replace("/^\s+|\s+$/u", '', $title);
            $oid = ++$this->pon;
            $out .= $oid.' 0 obj'."\n"
                .'<<'
                .' /Title '.$this->getOutTextString($title, $oid, true)
                .' /Parent '.($first_oid + $o['parent']).' 0 R';
            if (isset($o['prev'])) {
                $out .= ' /Prev '.($first_oid + $o['prev']).' 0 R';
            }
            if (isset($o['next'])) {
                $out .= ' /Next '.($first_oid + $o['next']).' 0 R';
            }
            if (isset($o['first'])) {
                $out .= ' /First '.($first_oid + $o['first']).' 0 R';
            }
            if (isset($o['last'])) {
                $out .= ' /Last '.($first_oid + $o['last']).' 0 R';
            }
            if (!empty($o['u'])) {
                // link
                if (is_string($o['u'])) {
                    switch ($o['u'][0]) {
                        case '#':
                            // internal destination
                            $out .= ' /Dest /'.$this->encrypt->encodeNameObject(substr($o['u'], 1));
                            break;
                        case '%':
                            // embedded PDF file
                            $filename = basename(substr($o['u'], 1));
                            $out .= ' /A <<'
                                .' /S /GoToE /D [0 /Fit] /NewWindow true /T'
                                .' << /R /C /P '.($o['p'] - 1).' /A '.$this->embeddedfiles[$filename]['a'].' >>'
                                .' >>';
                            break;
                        case '*':
                            // embedded generic file
                            $filename = basename(substr($o['u'], 1));
                            $jsa = 'var D=event.target.doc;'
                            .'var MyData=D.dataObjects;'
                            .'for (var i in MyData)'
                            .' if (MyData[i].path=="'.$filename.'")'
                            .' D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
                            $out .= ' /A <</S /JavaScript /JS '.$this->getOutTextString($jsa, $oid, true).'>>';
                            break;
                        default:
                            // external URI link
                            $out .= ' /A << /S /URI /URI '
                                .$this->encrypt->escapeDataString($this->unhtmlentities($o['u']), $oid)
                                .' >>';
                            break;
                    }
                } elseif (isset($this->links[$o['u']])) {
                    // internal link ID
                    $l = $this->links[$o['u']];
                    $page = $this->page->getPage($l['p']);
                    $y = ($page['height'] - ($l['y'] * $this->k));
                    $out .= sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $page['n'], $y);
                }
            } else {
                // link to a page
                $page = $this->page->getPage($o['p']);
                $x = ($o['x'] * $this->k);
                $y = ($page['height'] - ($o['y'] * $this->k));
                $out .= ' '.sprintf('/Dest [%u 0 R /XYZ %F %F null]', $page['n'], $x, $y);
            }
            // set font style
            $style = 0;
            if (!empty($o['s'])) {
                if (strpos($o['s'], 'B') !== false) {
                    $style |= 2; // bold
                }
                if (strpos($o['s'], 'I') !== false) {
                    $style |= 1; // oblique
                }
            }
            $out .= sprintf(' /F %d', $style);
            // set bookmark color
            if (!empty($o['c']) && is_array($o['c'])) {
                $out .= ' /C ['.$this->getColorStringFromArray($o['c']).']';
            } else {
                $out .= ' /C [0.0 0.0 0.0]'; // black
            }
            $out .= ' /Count 0' // normally closed item
                .' >>'."\n"
                .'endobj';
        }
        //Outline root
        $this->OutlineRoot = ++$this->pon;
        $out .= $this->OutlineRoot.' 0 obj'."\n"
            .'<<'
            .' /Type /Outlines'
            .' /First '.$first_oid.' 0 R'
            .' /Last '.($first_oid + $root_oid).' 0 R'
            .' >>'."\n"
            .'endobj';
        return $out;
    }

    /**
     * Returns the PDF Signature Fields entry.
     *
     * @return string
     */
    protected function getOutSignatureFields()
    {
        if (empty($this->signature)) {
            return '';
        }
        foreach ($this->signature['appearance']['empty'] as $key => $esa) {
            $page = $this->page->getPage($esa['page']);
            $signame = $esa['name'].sprintf(' [%03d]', ($key + 1));
            $out .= $esa['objid'].' 0 obj'."\n"
                .'<<'
                .' /Type /Annot'
                .' /Subtype /Widget'
                .' /Rect ['.$esa['rect'].']'
                .' /P '.$page['n'].' 0 R' // link to signature appearance page
                .' /F 4'
                .' /FT /Sig'
                .' /T '.$this->getOutTextString($signame, $esa['objid'], true)
                .' /Ff 0'
                .' >>'
                ."\n".'endobj';
        }
        return $out;
    }

    /**
     * Sign the document.
     *
     * @param string $pdfdoc string containing the PDF document
     *
     * @return string
     */
    protected function signDocument($pdfdoc)
    {
        $out = '';
        if (!$this->sign) {
            return $out;
        }
        // remove last newline
        $pdfdoc = substr($pdfdoc, 0, -1);
        // remove filler space
        $byterange_strlen = strlen($this->byterange);
        // define the ByteRange
        $byte_range = array();
        $byte_range[0] = 0;
        $byte_range[1] = strpos($pdfdoc, $this->byterange) + $byterange_strlen + 10;
        $byte_range[2] = $byte_range[1] + $this->sigmaxlen + 2;
        $byte_range[3] = strlen($pdfdoc) - $byte_range[2];
        $pdfdoc = substr($pdfdoc, 0, $byte_range[1]).substr($pdfdoc, $byte_range[2]);
        // replace the ByteRange
        $byterange = sprintf('/ByteRange[0 %u %u %u]', $byte_range[1], $byte_range[2], $byte_range[3]);
        $byterange .= str_repeat(' ', ($byterange_strlen - strlen($byterange)));
        $pdfdoc = str_replace($this->byterange, $byterange, $pdfdoc);
        // write the document to a temporary folder
        $tempdoc = $this->cache->getNewFileName('doc', $this->fileid);
        $f = $this->file->fopenLocal($tempdoc, 'wb');
        $pdfdoc_length = strlen($pdfdoc);
        fwrite($f, $pdfdoc, $pdfdoc_length);
        fclose($f);
        // get digital signature via openssl library
        $tempsign = $this->cache->getNewFileName('sig', $this->fileid);
        if (empty($this->signature['extracerts'])) {
            openssl_pkcs7_sign(
                $tempdoc,
                $tempsign,
                $this->signature['signcert'],
                array($this->signature['privkey'],
                $this->signature['password']),
                array(),
                PKCS7_BINARY | PKCS7_DETACHED
            );
        } else {
            openssl_pkcs7_sign(
                $tempdoc,
                $tempsign,
                $this->signature['signcert'],
                array($this->signature['privkey'],
                $this->signature['password']),
                array(),
                PKCS7_BINARY | PKCS7_DETACHED,
                $this->signature['extracerts']
            );
        }
        // read signature
        $signature = $this->file->getFileData($tempsign);
        // extract signature
        $signature = substr($signature, $pdfdoc_length);
        $signature = substr($signature, (strpos($signature, "%%EOF\n\n------") + 13));
        $tmparr = explode("\n\n", $signature);
        $signature = $tmparr[1];
        // decode signature
        $signature = base64_decode(trim($signature));
        // add TSA timestamp to signature
        $signature = $this->applySignatureTimestamp($signature);
        // convert signature to hex
        $signature = current(unpack('H*', $signature));
        $signature = str_pad($signature, $this->sigmaxlen, '0');
        // Add signature to the document
        $out = substr($pdfdoc, 0, $byte_range[1]).'<'.$signature.'>'.substr($pdfdoc, $byte_range[1]);
        return $out;
    }

    /**
     * -- NOT YET IMPLEMENTED --
     * Add TSA timestamp to the signature.
     *
     * @param string $signature Digital signature as binary string
     *
     * @return string
     */
    protected function applySignatureTimestamp($signature)
    {
        return $signature;
    }

    /**
     * Returns the PDF signarure entry.
     *
     * @return string
     */
    protected function getOutSignature()
    {
        if ((!$this->sign) || empty($this->signature['cert_type'])) {
            return;
        }
        // widget annotation for signature
        $soid = $this->objid['signature'];
        $oid = $soid + 1;
        $page = $this->page->getPage($this->signature['appearance']['page']);
        $out = $soid.' 0 obj'."\n"
            .'<<'
            .' /Type /Annot'
            .' /Subtype /Widget'
            .' /Rect ['.$this->signature['appearance']['rect'].']'
            .' /P '.$page['n'].' 0 R' // link to signature appearance page
            .' /F 4'
            .' /FT /Sig'
            .' /T '.$this->getOutTextString($this->signature['appearance']['name'], $soid, true)
            .' /Ff 0'
            .' /V '.$oid.' 0 R'
            .' >>'."\n"
            .'endobj';
        $out .= $oid.' 0 obj'."\n";
        $out .= '<<'
            .' /Type /Sig'
            .' /Filter /Adobe.PPKLite'
            .' /SubFilter /adbe.pkcs7.detached '
            .$this->byterange
            .' /Contents<'.str_repeat('0', $this->sigmaxlen).'>';
        if (empty($this->signature['approval']) || ($this->signature['approval'] != 'A')) {
            $out .= ' /Reference [' // array of signature reference dictionaries
                .' << /Type /SigRef';
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
            $out .= ' >>'
                .' ]'; // end of reference
        }
        $out .= $this->getOutSignatureInfo();
        $out .= ' /M '
            .$this->getOutDateTimeString($this->docmodtime, $oid)
            .' >>'."\n"
            .'endobj';
        return $out;
    }

    /**
     * Returns the PDF signarure entry.
     *
     * @return string
     */
    protected function getOutSignatureDocMDP()
    {
        $out .= ' /TransformMethod /DocMDP'
            .' /TransformParams'
            .' <<'
            .' /Type /TransformParams'
            .' /P '.$this->signature['cert_type']
            .' /V /1.2'
            .' >>';
        return $out;
    }

    /**
     * Returns the PDF signarure entry.
     *
     * @return string
     */
    protected function getOutSignatureUserRights()
    {
        $out = ' /TransformMethod /UR3'
            .' /TransformParams'
            .' <<'
            .' /Type /TransformParams'
            .' /V /2.2';
        if (!empty($this->userrights['document'])) {
            $out .= ' /Document['.$this->userrights['document'].']';
        }
        if (!empty($this->userrights['form'])) {
            $out .= ' /Form['.$this->userrights['form'].']';
        }
        if (!empty($this->userrights['signature'])) {
            $out .= ' /Signature['.$this->userrights['signature'].']';
        }
        if (!empty($this->userrights['annots'])) {
            $out .= ' /Annots['.$this->userrights['annots'].']';
        }
        if (!empty($this->userrights['ef'])) {
            $out .= ' /EF['.$this->userrights['ef'].']';
        }
        if (!empty($this->userrights['formex'])) {
            $out .= ' /FormEX['.$this->userrights['formex'].']';
        }
        $out .= ' >>';
        return $out;
    }

    /**
     * Returns the PDF signarure info section.
     *
     * @return string
     */
    protected function getOutSignatureInfo()
    {
        $out = '';
        if (!empty($this->signature['info']['Name'])) {
            $out .= ' /Name '.$this->getOutTextString($this->signature['info']['Name'], $oid, true);
        }
        if (!empty($this->signature['info']['Location'])) {
            $out .= ' /Location '.$this->getOutTextString($this->signature['info']['Location'], $oid, true);
        }
        if (!empty($this->signature['info']['Reason'])) {
            $out .= ' /Reason '.$this->getOutTextString($this->signature['info']['Reason'], $oid, true);
        }
        if (!empty($this->signature['info']['ContactInfo'])) {
            $out .= ' /ContactInfo '.$this->getOutTextString($this->signature['info']['ContactInfo'], $oid, true);
        }
        return $out;
    }

    /**
     * Get the PDF output string for Font resources dictionary.
     *
     * @return string
     */
    protected function getOutFontDic()
    {
        $fonts = $this->font->getFonts();
        if (empty($fonts)) {
            return '';
        }
        $out = ' /Font <<';
        foreach ($fonts as $font) {
            $out .= ' /F'.$font['i'].' '.$font['n'].' 0 R';
        }
        $out .= ' >>';
        return $out;
    }

    /**
     * Get the PDF output string for XObject resources dictionary.
     *
     * @return string
     */
    protected function getXObjectDic()
    {
        $out = ' /XObject <<';
        foreach ($this->xobject as $id => $oid) {
            $out .= ' /'.$id.' '.$oid['n'].' 0 R';
        }
        $out .= $this->image->getXobjectDict();
        $out .= ' >>';
        return $out;
    }

    /**
     * Get the PDF output string for Layer resources dictionary.
     *
     * @return string
     */
    protected function getLayerDic()
    {
        if (empty($this->pdflayer)) {
            return '';
        }
        $out = ' /Properties <<';
        foreach ($this->pdflayer as $layer) {
            $out .= ' /'.$layer['layer'].' '.$layer['objid'].' 0 R';
        }
        $out .= ' >>';
        return $out;
    }

    /**
     * Returns 'ON' if $val is true, 'OFF' otherwise.
     *
     * @param mixed $val Item to parse for boolean value.
     *
     * @return string
     */
    protected function getOnOff($val)
    {
        if (bool($val)) {
            return 'ON';
        }
        return 'OFF';
    }
}
