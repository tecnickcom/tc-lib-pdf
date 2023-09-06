<?php
/**
 * MetaInfo.php
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

/**
 * Com\Tecnick\Pdf\MetaInfo
 *
 * Meta Informaton PDF class
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2019 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
abstract class MetaInfo extends \Com\Tecnick\Pdf\Output
{
    /**
     * TCPDF version.
     *
     * @var string
     */
    protected $version = '8.0.26';

    /**
     * Time is seconds since EPOCH when the document was created.
     *
     * @var int
     */
    protected $doctime = 0;

    /**
     *  Time is seconds since EPOCH when the document was modified.
     *
     * @var int
     */
    protected $docmodtime = 0;

    /**
     * The name of the application that generates the PDF.
     *
     * If the document was converted to PDF from another format,
     * the name of the conforming product that created the original document from which it was converted.
     *
     * @var string
     */
    protected $creator = 'TCPDF';

    /**
     * The name of the person who created the document.
     *
     * @var string
     */
    protected $author = 'TCPDF';

    /**
     * Subject of the document.
     *
     * @var string
     */
    protected $subject = '-';

    /**
     * Title of the document.
     *
     * @var string
     */
    protected $title = 'PDF Document';

    /**
     * Space-separated list of keywords associated with the document.
     *
     * @var string
     */
    protected $keywords = 'TCPDF';

    /**
     * Additional XMP data to be appended just before the end of "x:xmpmeta" tag.
     *
     * @var string
     */
    protected $custom_xmp = '';

    /**
     * Additional XMP RDF data to be appended just before the end of "rdf:RDF" tag.
     *
     * @var string
     */
    protected $custom_xmp_rdf = '';

    /**
     * Set this to TRUE to add the default sRGB ICC color profile
     *
     * @var bool
     */
    protected $sRGB = false;

    /**
     * Viewer preferences dictionary controlling the way the document is to be presented on the screen or in print.
     * (Section 8.1 of PDF reference, "Viewer Preferences").
     *
     * @var array
     */
    protected $viewerpref = array();

    /**
     * Boolean flag to set the default document language direction.
     *    False = LTR = Left-To-Right.
     *    True = RTL = Right-To-Left.
     *
     * @val bool
     */
    protected $rtl = false;

    /**
     * Valid document zoom modes
     *
     * @var array
     */
    protected static $valid_zoom = array(
        'fullpage',
        'fullwidth',
        'real',
        'default'
    );

    /**
     * Return the program version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set a field value only if it is not empty.
     *
     * @param string $field Field name
     * @param srting $value Value to set
     */
    private function setNonEmptyFieldValue($field, $value)
    {
        if (!empty($value)) {
            $this->$field = $value;
        }
        return $this;
    }

    /**
     * Defines the creator of the document.
     * This is typically the name of the application that generates the PDF.
     *
     * @param string $creator The name of the creator.
     */
    public function setCreator($creator)
    {
        return $this->setNonEmptyFieldValue('creator', $creator);
    }

    /**
     * Defines the author of the document.
     *
     * @param string $author The name of the author.
     */
    public function setAuthor($author)
    {
        return $this->setNonEmptyFieldValue('author', $author);
    }

    /**
     * Defines the subject of the document.
     *
     * @param string $subject The subject.
     */
    public function setSubject($subject)
    {
        return $this->setNonEmptyFieldValue('subject', $subject);
    }

    /**
     * Defines the title of the document.
     *
     * @param string $title The title.
     */
    public function setTitle($title)
    {
        return $this->setNonEmptyFieldValue('title', $title);
    }

    /**
     * Associates keywords with the document, generally in the form 'keyword1 keyword2 ...'.
     *
     * @param string $keywords Space-separated list of keywords.
     */
    public function setKeywords($keywords)
    {
        return $this->setNonEmptyFieldValue('keywords', $keywords);
    }

    /**
     * Set the PDF version (check PDF reference for valid values).
     *
     * @param string $version PDF document version.
     *
     * @throw PdfException in case of error.
     */
    public function setPDFVersion($version = '1.7')
    {
        if ($this->pdfa == 1) { // PDF/A 1 mode
            $this->pdfver = '1.4';
            return $this;
        }
        $isvalid = preg_match('/^[1-9]+[.][0-9]+$/', $version);
        if (empty($isvalid)) {
            throw new PdfException('Invalid PDF version format');
        }
        $this->pdfver = $version;
        return $this;
    }

    /**
     * Set the sRGB mode
     *
     * @param bool $enabled Set to true to add the default sRGB ICC color profile
     */
    public function setSRGB($enabled)
    {
        $this->srgb = (bool) $enabled;
        return $this;
    }

    /**
     * Format a text string for output.
     *
     * @param string $str String to escape.
     * @param int    $oid Current PDF object number.
     * @param bool   $bom If true set the Byte Order Mark (BOM).
     *
     * @return string escaped string.
     */
    protected function getOutTextString($str, $oid, $bom = false)
    {
        if ($this->isunicode) {
            $str = $this->uniconv->toUTF16BE($str);
            if ($bom) {
                $str = "\xFE\xFF".$str; // Byte Order Mark (BOM)
            }
        }
        return $this->encrypt->escapeDataString($str, $oid);
    }

    /**
     * Returns a formatted date for meta information
     *
     * @param int $time Time in seconds.
     *
     * @return string date-time string.
     */
    protected function getFormattedDate($time)
    {
        return substr_replace(date('YmdHisO', intval($time)), '\'', (0 - 2), 0).'\'';
    }

    /**
     * Returns a formatted date for XMP meta information
     *
     * @param int $time Time in seconds.
     *
     * @return string date-time string.
     */
    protected function getXMPFormattedDate($time)
    {
        return date('Y-m-dTH:i:sP', intval($time));
    }

    /**
     * Returns the producer string
     *
     * @return string
     */
    protected function getProducer()
    {
        return "\x54\x43\x50\x44\x46\x20"
        .$this->version
        ."\x20\x28\x68\x74\x74\x70\x73\x3a\x2f\x2f\x74\x63\x70\x64\x66\x2e\x6f\x72\x67\x29";
    }
    
    /**
     * Returns a formatted date for meta information
     *
     * @param int    $time Time in seconds.
     * @param int    $oid  Current PDF object number.
     *
     * @return string escaped date-time string.
     */
    protected function getOutDateTimeString($time, $oid)
    {
        if (empty($time)) {
            $time = $this->doctime;
        }
        return $this->encrypt->escapeDataString('D:'.$this->getFormattedDate($time), $oid);
    }

    /**
     * Get the PDF output string for the Document Information Dictionary.
     * (ref. Chapter 14.3.3 Document Information Dictionary of PDF32000_2008.pdf)
     *
     * @return string
     */
    protected function getOutMetaInfo()
    {
        $oid = ++$this->pon;
        $this->objid['info'] = $oid;
        $out = $oid.' 0 obj'."\n"
        .'<<'
        .' /Creator '.$this->getOutTextString($this->creator, $oid, true)
        .' /Author '.$this->getOutTextString($this->author, $oid, true)
        .' /Subject '.$this->getOutTextString($this->subject, $oid, true)
        .' /Title '.$this->getOutTextString($this->title, $oid, true)
        .' /Keywords '.$this->getOutTextString($this->keywords, $oid, true)
        .' /Producer '.$this->getOutTextString($this->getProducer(), $oid, true)
        .' /CreationDate '.$this->getOutDateTimeString($this->doctime, $oid)
        .' /ModDate '.$this->getOutDateTimeString($this->docmodtime, $oid)
        .' /Trapped /False'
        .' >>'."\n"
        .'endobj'."\n";
        return $out;
    }

    /**
     * Escape some special characters (&lt; &gt; &amp;) for XML output.
     *
     * @param string $str Input string to escape.
     *
     * @return string
     */
    protected function getEscapedXML($str)
    {
        return strtr($str, array("\0" => '', '&' => '&amp;', '<' => '&lt;', '>' => '&gt;'));
    }

    /**
     * Set additional XMP data to be appended just before the end of "x:xmpmeta" tag.
     *
     * IMPORTANT:
     * This data is added as-is without controls, so you have to validate your data before using this method.
     *
     * @param string $xmp Custom XMP data.
     */
    public function setExtraXMP($xmp)
    {
        return $this->setNonEmptyFieldValue('custom_xmp', $xmp);
    }

    /**
     * Set additional XMP data to be appended just before the end of "rdf:RDF" tag.
     *
     * IMPORTANT:
     * This data is added as-is without controls, so you have to validate your data before using this method.
     *
     * @param string $xmp Custom XMP data.
     */
    public function setExtraXMPRDF($xmp)
    {
        return $this->setNonEmptyFieldValue('custom_xmp_rdf', $xmp);
    }

    /**
     * Get the PDF output string for the XMP data object
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getOutXMP()
    {
        $uuid = 'uuid:'.substr($this->fileid, 0, 8)
        .'-'.substr($this->fileid, 8, 4)
        .'-'.substr($this->fileid, 12, 4)
        .'-'.substr($this->fileid, 16, 4)
        .'-'.substr($this->fileid, 20, 12);
        
        // @codingStandardsIgnoreStart
        $xmp = '<?xpacket begin="'.$this->uniconv->chr(0xfeff).'" id="W5M0MpCehiHzreSzNTczkc9d"?>'."\n"
        .'<x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 4.2.1-c043 52.372728, 2009/01/18-15:08:04">'."\n"
        ."\t".'<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">'."\n"
        ."\t\t".'<rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n"
        ."\t\t\t".'<dc:format>application/pdf</dc:format>'."\n"
        ."\t\t\t".'<dc:title>'."\n"
        ."\t\t\t\t".'<rdf:Alt>'."\n"
        ."\t\t\t\t\t".'<rdf:li xml:lang="x-default">'.$this->getEscapedXML($this->title).'</rdf:li>'."\n"
        ."\t\t\t\t".'</rdf:Alt>'."\n"
        ."\t\t\t".'</dc:title>'."\n"
        ."\t\t\t".'<dc:creator>'."\n"
        ."\t\t\t\t".'<rdf:Seq>'."\n"
        ."\t\t\t\t\t".'<rdf:li>'.$this->getEscapedXML($this->author).'</rdf:li>'."\n"
        ."\t\t\t\t".'</rdf:Seq>'."\n"
        ."\t\t\t".'</dc:creator>'."\n"
        ."\t\t\t".'<dc:description>'."\n"
        ."\t\t\t\t".'<rdf:Alt>'."\n"
        ."\t\t\t\t\t".'<rdf:li xml:lang="x-default">'.$this->getEscapedXML($this->subject).'</rdf:li>'."\n"
        ."\t\t\t\t".'</rdf:Alt>'."\n"
        ."\t\t\t".'</dc:description>'."\n"
        ."\t\t\t".'<dc:subject>'."\n"
        ."\t\t\t\t".'<rdf:Bag>'."\n"
        ."\t\t\t\t\t".'<rdf:li>'.$this->getEscapedXML($this->keywords).'</rdf:li>'."\n"
        ."\t\t\t\t".'</rdf:Bag>'."\n"
        ."\t\t\t".'</dc:subject>'."\n"
        ."\t\t".'</rdf:Description>'."\n"
        ."\t\t".'<rdf:Description rdf:about="" xmlns:xmp="http://ns.adobe.com/xap/1.0/">'."\n"
        ."\t\t\t".'<xmp:CreateDate>'.$this->getXMPFormattedDate($this->doctime).'</xmp:CreateDate>'."\n"
        ."\t\t\t".'<xmp:CreatorTool>'.$this->getEscapedXML($this->creator).'</xmp:CreatorTool>'."\n"
        ."\t\t\t".'<xmp:ModifyDate>'.$this->getXMPFormattedDate($this->docmodtime).'</xmp:ModifyDate>'."\n"
        ."\t\t\t".'<xmp:MetadataDate>'.$this->getXMPFormattedDate($this->doctime).'</xmp:MetadataDate>'."\n"
        ."\t\t".'</rdf:Description>'."\n"
        ."\t\t".'<rdf:Description rdf:about="" xmlns:pdf="http://ns.adobe.com/pdf/1.3/">'."\n"
        ."\t\t\t".'<pdf:Keywords>'.$this->getEscapedXML($this->keywords).'</pdf:Keywords>'."\n"
        ."\t\t\t".'<pdf:Producer>'.$this->getEscapedXML($this->getProducer()).'</pdf:Producer>'."\n"
        ."\t\t".'</rdf:Description>'."\n"
        ."\t\t".'<rdf:Description rdf:about="" xmlns:xmpMM="http://ns.adobe.com/xap/1.0/mm/">'."\n"
        ."\t\t\t".'<xmpMM:DocumentID>'.$uuid.'</xmpMM:DocumentID>'."\n"
        ."\t\t\t".'<xmpMM:InstanceID>'.$uuid.'</xmpMM:InstanceID>'."\n"
        ."\t\t".'</rdf:Description>'."\n";
        
        if ($this->pdfa) {
            $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdfaid="http://www.aiim.org/pdfa/ns/id/">'."\n"
            ."\t\t\t".'<pdfaid:part>'.$this->pdfa.'</pdfaid:part>'."\n"
            ."\t\t\t".'<pdfaid:conformance>B</pdfaid:conformance>'."\n"
            ."\t\t".'</rdf:Description>'."\n";
        }

        // XMP extension schemas
        $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdfaExtension="http://www.aiim.org/pdfa/ns/extension/" xmlns:pdfaSchema="http://www.aiim.org/pdfa/ns/schema#" xmlns:pdfaProperty="http://www.aiim.org/pdfa/ns/property#">'."\n"
        ."\t\t\t".'<pdfaExtension:schemas>'."\n"
        ."\t\t\t\t".'<rdf:Bag>'."\n"
        ."\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n"
        ."\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://ns.adobe.com/pdf/1.3/</pdfaSchema:namespaceURI>'."\n"
        ."\t\t\t\t\t\t".'<pdfaSchema:prefix>pdf</pdfaSchema:prefix>'."\n"
        ."\t\t\t\t\t\t".'<pdfaSchema:schema>Adobe PDF Schema</pdfaSchema:schema>'."\n"
        ."\t\t\t\t\t".'</rdf:li>'."\n"
        ."\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n"
        ."\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://ns.adobe.com/xap/1.0/mm/</pdfaSchema:namespaceURI>'."\n"
        ."\t\t\t\t\t\t".'<pdfaSchema:prefix>xmpMM</pdfaSchema:prefix>'."\n"
        ."\t\t\t\t\t\t".'<pdfaSchema:schema>XMP Media Management Schema</pdfaSchema:schema>'."\n"
        ."\t\t\t\t\t\t".'<pdfaSchema:property>'."\n"
        ."\t\t\t\t\t\t\t".'<rdf:Seq>'."\n"
        ."\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>UUID based identifier for specific incarnation of a document</pdfaProperty:description>'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>InstanceID</pdfaProperty:name>'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>URI</pdfaProperty:valueType>'."\n"
        ."\t\t\t\t\t\t\t\t".'</rdf:li>'."\n"
        ."\t\t\t\t\t\t\t".'</rdf:Seq>'."\n"
        ."\t\t\t\t\t\t".'</pdfaSchema:property>'."\n"
        ."\t\t\t\t\t".'</rdf:li>'."\n"
        ."\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n"
        ."\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://www.aiim.org/pdfa/ns/id/</pdfaSchema:namespaceURI>'."\n"
        ."\t\t\t\t\t\t".'<pdfaSchema:prefix>pdfaid</pdfaSchema:prefix>'."\n"
        ."\t\t\t\t\t\t".'<pdfaSchema:schema>PDF/A ID Schema</pdfaSchema:schema>'."\n"
        ."\t\t\t\t\t\t".'<pdfaSchema:property>'."\n"
        ."\t\t\t\t\t\t\t".'<rdf:Seq>'."\n"
        ."\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Part of PDF/A standard</pdfaProperty:description>'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>part</pdfaProperty:name>'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Integer</pdfaProperty:valueType>'."\n"
        ."\t\t\t\t\t\t\t\t".'</rdf:li>'."\n"
        ."\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Amendment of PDF/A standard</pdfaProperty:description>'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>amd</pdfaProperty:name>'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Text</pdfaProperty:valueType>'."\n"
        ."\t\t\t\t\t\t\t\t".'</rdf:li>'."\n"
        ."\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Conformance level of PDF/A standard</pdfaProperty:description>'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>conformance</pdfaProperty:name>'."\n"
        ."\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Text</pdfaProperty:valueType>'."\n"
        ."\t\t\t\t\t\t\t\t".'</rdf:li>'."\n"
        ."\t\t\t\t\t\t\t".'</rdf:Seq>'."\n"
        ."\t\t\t\t\t\t".'</pdfaSchema:property>'."\n"
        ."\t\t\t\t\t".'</rdf:li>'."\n"
        ."\t\t\t\t".'</rdf:Bag>'."\n"
        ."\t\t\t".'</pdfaExtension:schemas>'."\n"
        ."\t\t".'</rdf:Description>'."\n"
        .$this->custom_xmp_rdf."\n"
        ."\t".'</rdf:RDF>'."\n"
        .$this->custom_xmp."\n"
        .'</x:xmpmeta>'."\n"
        .'<?xpacket end="w"?>';
        // @codingStandardsIgnoreEnd

        $oid = ++$this->pon;
        $this->objid['xmp'] = $oid;
        $out = $oid.' 0 obj'."\n"
        .'<<'
        .' /Type /Metadata'
        .' /Subtype /XML'
        .' /Length '.strlen($xmp)
        .' >> stream'."\n"
        .$xmp."\n"
        .'endstream'."\n"
        .'endobj'."\n";

        return $out;
    }

    /**
     * Set the default document language direction.
     *
     * @param bool $enabled False = LTR = Left-To-Right; True = RTL = Right-To-Left.
     */
    public function setRTL($enabled)
    {
        $this->rtl = (bool) $enabled;
        return $this;
    }

    /**
     * Set the viewer preferences dictionary
     * controlling the way the document is to be presented on the screen or in print.
     *
     * @param array $pref Array of options (see Section 12.2 of PDF reference, "Viewer Preferences").
     */
    public function setViewerPreferences($pref)
    {
        $this->viewerpref = $pref;
        return $this;
    }

    /**
     * Sanitize the page box name and return the default 'CropBox' in case of error.
     *
     * @param string $name Entry name.
     *
     * @return string
     */
    protected function getPageBoxName($name)
    {
        $box = 'CropBox';
        if (isset($this->viewerpref[$name])) {
            $val = $this->viewerpref[$name];
            if (isset($this->page->$box[$val])) {
                $box = $this->page->$box[$val];
            }
        }
        return ' /'.$name.' /'.$box;
    }

    /**
     * Sanitize the page box name and return the default 'CropBox' in case of error.
     *
     * @return string
     */
    protected function getPagePrintScaling()
    {
        $mode = 'AppDefault';
        if (isset($this->viewerpref['PrintScaling'])) {
            $name = strtolower($this->viewerpref['PrintScaling']);
            $valid = array(
                'none'       => 'None',
                'appdefault' => 'AppDefault',
            );
            if (isset($valid[$name])) {
                $mode = $valid[$name];
            }
        }
        return ' /PrintScaling /'.$mode;
    }

    /**
     * Returns the Duplex mode for the Viewer Preferences
     *
     * @return string
     */
    protected function getDuplexMode()
    {
        $mode = 'none';
        if (isset($this->viewerpref['Duplex'])) {
            $name = strtolower($this->viewerpref['Duplex']);
            $valid = array(
                'simplex'             => 'Simplex',
                'duplexflipshortedge' => 'DuplexFlipShortEdge',
                'duplexfliplongedge'  => 'DuplexFlipLongEdge',
            );
            if (isset($valid[$name])) {
                $mode = $valid[$name];
            }
        }
        return ' /Duplex /'.$mode;
    }

    /**
     * Returns the Viewer Preference boolean entry.
     *
     * @param string $name Entry name.
     *
     * @return string
     */
    protected function getBooleanMode($name)
    {
        if (isset($this->viewerpref[$name])) {
            return ' /'.$name.' '.var_export((bool)$this->viewerpref[$name], true);
        }
        return '';
    }

    /**
     * Returns the PDF viewer preferences for the catalog section
     *
     * @return string
     */
    protected function getOutViewerPref()
    {
        $vpr = $this->viewerpref;
        $out = ' /ViewerPreferences <<';
        if ($this->rtl) {
            $out .= ' /Direction /R2L';
        } else {
            $out .= ' /Direction /L2R';
        }
        $out .= $this->getBooleanMode('HideToolbar');
        $out .= $this->getBooleanMode('HideMenubar');
        $out .= $this->getBooleanMode('HideWindowUI');
        $out .= $this->getBooleanMode('FitWindow');
        $out .= $this->getBooleanMode('CenterWindow');
        $out .= $this->getBooleanMode('DisplayDocTitle');
        if (isset($vpr['NonFullScreenPageMode'])) {
            $out .= ' /NonFullScreenPageMode /'.$this->page->getDisplay($vpr['NonFullScreenPageMode']);
        }
        $out .= $this->getPageBoxName('ViewArea');
        $out .= $this->getPageBoxName('ViewClip');
        $out .= $this->getPageBoxName('PrintArea');
        $out .= $this->getPageBoxName('PrintClip');
        $out .= $this->getPagePrintScaling();
        $out .= $this->getDuplexMode();
        $out .= $this->getBooleanMode('PickTrayByPDFSize');
        if (isset($vpr['PrintPageRange'])) {
            $PrintPageRangeNum = '';
            foreach ($vpr['PrintPageRange'] as $pnum) {
                $PrintPageRangeNum .= ' '.($pnum - 1).'';
            }
            $out .= ' /PrintPageRange ['.$PrintPageRangeNum.' ]';
        }
        if (isset($vpr['NumCopies'])) {
            $out .= ' /NumCopies '.intval($vpr['NumCopies']);
        }
        $out .= ' >>';
        return $out;
    }
}
