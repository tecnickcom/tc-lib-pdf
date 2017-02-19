<?php
/**
 * Output.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2017 Nicola Asuni - Tecnick.com LTD
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
 * @copyright   2002-2017 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
abstract class Output
{
    /**
     * Array containing the ID of some PDF objects
     *
     * @var array
     */
    protected $objid = array();

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
        // @TODO: sign the document ...
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
        $out .= $this->getOutOutput();
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
            .'/N 3'
            .' /Filter /FlateDecode'
            .' /Length '.strlen($icc)
            .'>>'
            .' stream'."\n"
            .$icc."\n"
            .'endstream'."\n"
            .'endobj'."\n";
        return $out;
    }

    /**
     * Set OutputIntent to sRGB IEC61966-2.1 if required
     *
     * @return string
     */
    protected function getOutputIntents()
    {
        if (empty($this->objid['srgbicc']) || empty($this->objid['catalog'])) {
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
        return '';
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
        $oid = ++$this->pon;
        $this->objid['resdic'] = $oid;
        $out .= $oid.' 0 obj'."\n"
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
        // @TODO
        return '';
    }

    /**
     * Returns the PDF Embedded Files entry
     *
     * @return string
     */
    protected function getOutEmbeddedFiles()
    {
        // @TODO
        return '';
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
        // @TODO
        return '';
    }

    /**
     * Returns the PDF Bookmarks entry
     *
     * @return string
     */
    protected function getOutBookmarks()
    {
        // @TODO
        return '';
    }

    /**
     * Returns the PDF Signature Fields entry
     *
     * @return string
     */
    protected function getOutSignatureFields()
    {
        // @TODO
        return '';
    }

    /**
     * Returns the PDF signarure entry
     *
     * @return string
     */
    protected function getOutSignature()
    {
        // @TODO
        return '';
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
