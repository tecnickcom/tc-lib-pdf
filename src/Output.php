<?php
/**
 * Output.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2019 Nicola Asuni - Tecnick.com LTD
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
 * @copyright   2002-2019 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * @SuppressWarnings(PHPMD)
 */
abstract class Output
{
    /**
     * Array containing the ID of some named PDF objects
     *
     * @var array
     */
    protected $objid = array();
    
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
     * Returns the RAW PDF string
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
        $out = $this->signDocument($out);
        return $out;
    }

    /**
     * Returns the PDF document header
     *
     * @return string
     */
    protected function getOutPDFHeader()
    {
        return '%PDF-'.$this->pdfver."\n"
            ."%\xE2\xE3\xCF\xD3\n";
    }

    /**
     * Returns the raw PDF Body section
     *
     * @return string
     */
    protected function getOutPDFBody()
    {
        $out = $this->page->getPdfPages($this->pon);
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
        $this->pon = $outfont->getObjectNumber();
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
     * Returns the ordered offset array for each object
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
     * Returns the PDF XREF section
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
     * Returns the PDF Trailer section
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
     * Returns the PDF object to include a standard sRGB_IEC61966-2.1 blackscaled ICC colour profile
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
     * Get OutputIntents for sRGB IEC61966-2.1 if required
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
            .' /OutputCondition '.$this->getOutTextString('sRGB IEC61966-2.1', $oid)
            .' /OutputConditionIdentifier '.$this->getOutTextString('sRGB IEC61966-2.1', $oid)
            .' /RegistryName '.$this->getOutTextString('http://www.color.org', $oid)
            .' /Info '.$this->getOutTextString('sRGB IEC61966-2.1', $oid)
            .' /DestOutputProfile '.$this->objid['srgbicc'].' 0 R'
            .' >>]';
        return $out;
    }

    /**
     * Get OutputIntents for PDF-X if required
     *
     * @return string
     */
    protected function getOutputIntentsPdfX()
    {
        $oid = $this->objid['catalog'];
        $out = ' /OutputIntents [<<'
            .' /Type /OutputIntent'
            .' /S /GTS_PDFX'
            .' /OutputConditionIdentifier '.$this->getOutTextString('OFCOM_PO_P1_F60_95', $oid)
            .' /RegistryName '.$this->getOutTextString('http://www.color.org', $oid)
            .' /Info '.$this->getOutTextString('OFCOM_PO_P1_F60_95', $oid)
            .' >>]';
        return $out;
    }

    /**
     * Set OutputIntents
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
     * Get the PDF layers
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
            .' /Name '.$this->getOutTextString('Layers', $oid)
            .' /Creator '.$this->getOutTextString($this->creator, $oid)
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
     * Returns the PDF Catalog entry
     *
     * @return string
     */
    protected function getOutCatalog()
    {
        // @TODO
        $oid = ++$this->pon;
        $this->objid['catalog'] = $oid;
        $out = $oid.' 0 obj'."\n"
            .'<<'
            .' /Type /Catalog'
            .' /Version /'.$this->pdfver
            //.' /Extensions <<>>'
            .' /Pages 1 0 R'
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
            $out .= ' /Lang '.$this->getOutTextString($this->l['a_meta_language'], $oid);
        }

        //$out .= ' /SpiderInfo <<>>';
        $out .= $this->getOutputIntents();
        //$out .= ' /PieceInfo <<>>';
        $out .= $this->getPDFLayers();

        /*
        // AcroForm
        if (!empty($this->form_obj_id)
            OR ($this->sign AND isset($this->signature['cert_type']))
            OR !empty($this->empty_signature_appearance)) {
            $out .= ' /AcroForm <<';
            $objrefs = '';
            if ($this->sign AND isset($this->signature['cert_type'])) {
                // set reference for signature object
                $objrefs .= $this->sig_obj_id.' 0 R';
            }
            if (!empty($this->empty_signature_appearance)) {
                foreach ($this->empty_signature_appearance as $esa) {
                    // set reference for empty signature objects
                    $objrefs .= ' '.$esa['objid'].' 0 R';
                }
            }
            if (!empty($this->form_obj_id)) {
                foreach($this->form_obj_id as $objid) {
                    $objrefs .= ' '.$objid.' 0 R';
                }
            }
            $out .= ' /Fields ['.$objrefs.']';
            // It's better to turn off this value and set the appearance stream for
            // each annotation (/AP) to avoid conflicts with signature fields.
            if (empty($this->signature['approval']) OR ($this->signature['approval'] != 'A')) {
                $out .= ' /NeedAppearances false';
            }
            if ($this->sign AND isset($this->signature['cert_type'])) {
                if ($this->signature['cert_type'] > 0) {
                    $out .= ' /SigFlags 3';
                } else {
                    $out .= ' /SigFlags 1';
                }
            }
            //$out .= ' /CO ';
            if (isset($this->annotation_fonts) AND !empty($this->annotation_fonts)) {
                $out .= ' /DR <<';
                $out .= ' /Font <<';
                foreach ($this->annotation_fonts as $fontkey => $fontid) {
                    $out .= ' /F'.$fontid.' '.$this->font_obj_ids[$fontkey].' 0 R';
                }
                $out .= ' >> >>';
            }
            $font = $this->getFontBuffer('helvetica');
            $out .= ' /DA (/F'.$font['i'].' 0 Tf 0 g)';
            $out .= ' /Q '.(($this->rtl)?'2':'0');
            //$out .= ' /XFA ';
            $out .= ' >>';
            // signatures
            if ($this->sign AND isset($this->signature['cert_type'])
                AND (empty($this->signature['approval']) OR ($this->signature['approval'] != 'A'))) {
                if ($this->signature['cert_type'] > 0) {
                    $out .= ' /Perms << /DocMDP '.($this->sig_obj_id + 1).' 0 R >>';
                } else {
                    $out .= ' /Perms << /UR3 '.($this->sig_obj_id + 1).' 0 R >>';
                }
            }
        }
        */

        //$out .= ' /Legal <<>>';
        //$out .= ' /Requirements []';
        //$out .= ' /Collection <<>>';
        //$out .= ' /NeedsRendering true';

        $out .= ' >>'."\n"
            .'endobj'."\n";
        return $out;
    }

    /**
     * Returns the PDF OCG entry
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
                .' /Name '.$this->getOutTextString($layer['name'], $oid)
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
     * Returns the PDF XObjects entry
     *
     * @return string
     */
    protected function getOutXObjects()
    {
        // @TODO
        return '';
    }

    /**
     * Returns the PDF Resources Dictionary entry
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
     * Returns the PDF Destinations entry
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
            .'endobj';
        return $out;
    }

    /**
     * Returns the PDF Embedded Files entry
     *
     * @return string
     */
    protected function getOutEmbeddedFiles()
    {
        if (($this->pdfa == 1 ) || ($this->pdfa == 2)) {
            // embedded files are not allowed in PDF/A mode version 1 and 2
            return;
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
                .'endobj';
            // embedded file object
            $filter = '';
            if ($this->pdfa == 3) {
                $filter = ' /Subtype /text#2Fxml';
            } else {
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
                .'endobj';
            return $out;
        }
    }

    /**
     * Returns the PDF Annotations entry
     *
     * @return string
     */
    protected function getOutAnnotations()
    {
        // @TODO
        return '';
    }

    /**
     * Returns the PDF Javascript entry
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
            // The addField method only works when releasing user rights (UR3)
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
            .$this->getOutTextString($this->javascript, $oid)
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
                .$this->getOutTextString($val['js'], $oid)
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
     * Sort bookmarks by page and original position
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
     * Returns the PDF Bookmarks entry
     *
     * @return string
     */
    protected function getOutBookmarks()
    {
        $numbookmarks = count($this->outlines);
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
                .' /Title '.$this->getOutTextString($title, $oid)
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
            if (isset($o['u']) and !empty($o['u'])) {
                // link
                if (is_string($o['u'])) {
                    if ($o['u'][0] == '#') {
                        // internal destination
                        $out .= ' /Dest /'.$this->encrypt->encodeNameObject(substr($o['u'], 1));
                    } elseif ($o['u'][0] == '%') {
                        // embedded PDF file
                        $filename = basename(substr($o['u'], 1));
                        $out .= ' /A <<'
                            .' /S /GoToE /D [0 /Fit] /NewWindow true /T'
                            .' << /R /C /P '.($o['p'] - 1).' /A '.$this->embeddedfiles[$filename]['a'].' >>'
                            .' >>';
                    } elseif ($o['u'][0] == '*') {
                        // embedded generic file
                        $filename = basename(substr($o['u'], 1));
                        $jsa = 'var D=event.target.doc;'
                        .'var MyData=D.dataObjects;'
                        .'for (var i in MyData)'
                        .' if (MyData[i].path=="'.$filename.'")'
                        .' D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
                        $out .= ' /A <</S /JavaScript /JS '.$this->getOutTextString($jsa, $oid).'>>';
                    } else {
                        // external URI link
                        $out .= ' /A << /S /URI /URI '
                            .$this->encrypt->escapeDataString($this->unhtmlentities($o['u']), $oid)
                            .' >>';
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
            if (!empty($color)) {
                $out .= ' /C ['.$this->color->getPdfRgbComponents($color).']';
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
     * Returns the PDF Signature Fields entry
     *
     * @return string
     */
    protected function getOutSignatureFields()
    {
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
                .' /T '.$this->getOutTextString($signame, $esa['objid'])
                .' /Ff 0'
                .' >>'
                ."\n".'endobj';
        }
        return $out;
    }

    /**
     * Sign the document
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
        $byte_range[2] = $byte_range[1] + $this->signature_max_length + 2;
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
        $signature = str_pad($signature, $this->signature_max_length, '0');
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
     * Returns the PDF signarure entry
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
            .' /T '.$this->getOutTextString($this->signature['appearance']['name'], $soid)
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
     * Returns the PDF signarure entry
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
     * Returns the PDF signarure entry
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
     * Returns the PDF signarure info section
     *
     * @return string
     */
    protected function getOutSignatureInfo()
    {
        $out = '';
        if (!empty($this->signature['info']['Name'])) {
            $out .= ' /Name '.$this->getOutTextString($this->signature['info']['Name'], $oid);
        }
        if (!empty($this->signature['info']['Location'])) {
            $out .= ' /Location '.$this->getOutTextString($this->signature['info']['Location'], $oid);
        }
        if (!empty($this->signature['info']['Reason'])) {
            $out .= ' /Reason '.$this->getOutTextString($this->signature['info']['Reason'], $oid);
        }
        if (!empty($this->signature['info']['ContactInfo'])) {
            $out .= ' /ContactInfo '.$this->getOutTextString($this->signature['info']['ContactInfo'], $oid);
        }
        return $out;
    }

    /**
     * Get the PDF output string for Font resources dictionary
     *
     * return string
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
     * Get the PDF output string for XObject resources dictionary
     *
     * return string
     */
    protected function getXObjectDic()
    {
        if (empty($this->xobject)) {
            return '';
        }
        $out = ' /XObject <<';
        foreach ($this->xobject as $id => $oid) {
            $out .= ' /'.$id.' '.$oid['n'].' 0 R';
        }
        $out .= ' >>';
        return $out;
    }

    /**
     * Get the PDF output string for Layer resources dictionary
     *
     * return string
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
     * Returns 'ON' if $val is true, 'OFF' otherwise
     *
     * return string
     */
    protected function getOnOff($val)
    {
        if (bool($val)) {
            return 'ON';
        }
        return 'OFF';
    }
}
